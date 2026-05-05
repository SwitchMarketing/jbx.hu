<?php

namespace App\Controllers\Admin;

use Exception;
use App\Models\ProductVariantModel;
use App\Models\CategoryTreeModel;
use App\Models\ImageModel;
use App\Models\ProductMasterDefaultAttributeModel;
use App\Helpers\BreadcrumbsHelper;

class Products extends BaseResourceController
{
    protected $modelName = '\App\Models\ProductMasterModel';

    /**
     * index
     *
     * @return ResponseInterface
     */
    public function index()
    {
        try {
            $limit  = $this->request->getVar('limit') ?? 25;
            $offset = $this->request->getVar('start') ?? 0;
            $filter = $this->request->getVar('filter');
            $sort   = $this->request->getVar('sort');

            if ($filter) {
                $this->model->setFilters(json_decode($filter));
            }

            if ($sort) {
                $this->model->setSorters(json_decode($sort));
            }

            $result = $this->model->findAll($limit, $offset);

            // Enrich with full category path names (breadcrumb-style) in a single extra query.
            if (!empty($result->data)) {
                $catMap = [];
                foreach ((new CategoryTreeModel())->findAll() as $c) {
                    $catMap[$c->unas_id] = $c;
                }
                foreach ($result->data as $row) {
                    $row->category_path_names = '';
                    if (!empty($row->category_id) && isset($catMap[$row->category_id])) {
                        $cat = $catMap[$row->category_id];
                        $ids = array_filter(explode('/', $cat->pathIds ?? ''));
                        $names = [];
                        foreach ($ids as $id) {
                            if (isset($catMap[$id])) $names[] = $catMap[$id]->name;
                        }
                        $row->category_path_names = implode(' › ', $names);
                    }
                }
            }

            $this->setData($result->data);
            $this->setTotal($result->total);
            $this->setSuccess(true);
            $this->setMessage('OK');
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }

    /**
     * show
     *
     * @return ResponseInterface
     */
    public function show($id = null)
    {
        try {
            if (!is_object($product = $this->model->findWithCategory($id))) {
                throw new Exception('Nincs ilyen rekord!');
            }

            // Include variants
            $variantModel = new ProductVariantModel();
            $product->variants = $variantModel->getWithAttributes($id);

            $db = \Config\Database::connect('shop');
            $product->default_attributes = $db->table('product_master_default_attributes pmda')
                ->select('pmda.attribute_id, a.name, pmda.default_value as value')
                ->join('attributes a', 'a.id = pmda.attribute_id', 'left')
                ->where('pmda.master_id', (int) $id)
                ->orderBy('a.name', 'ASC')
                ->get()
                ->getResult();

            $product->images = (new ImageModel())
                ->where('master_id', (int) $id)
                ->orderBy('position', 'ASC')
                ->orderBy('id', 'ASC')
                ->findAll();

            // Full category path (breadcrumb-style): "Root > Parent > Child"
            $product->category_path_names = '';
            if (!empty($product->category_id)) {
                $trail = BreadcrumbsHelper::getCategoryTrail($product->category_id);
                if (!empty($trail)) {
                    $product->category_path_names = implode(' › ', array_map(fn($c) => $c->name, $trail));
                }
            }

            $this->setData($product);
            $this->setSuccess(true);
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }

    /**
     * create — POST /admin/products
     * Creates a new product master. Body must include name, slug, category_id.
     *
     * @return ResponseInterface
     */
    public function create()
    {
        try {
            $data = $this->request->getRawInput();

            $name       = isset($data['name']) ? trim((string) $data['name']) : '';
            $slug       = isset($data['slug']) ? trim((string) $data['slug']) : '';
            $categoryId = isset($data['category_id']) ? (int) $data['category_id'] : 0;

            if ($name === '') {
                throw new Exception('Hiányzó kötelező mező: név');
            }
            if ($slug === '') {
                throw new Exception('Hiányzó kötelező mező: slug');
            }
            if ($categoryId <= 0) {
                throw new Exception('Hiányzó kötelező mező: kategória');
            }

            if ($this->model->where('slug', $slug)->first()) {
                throw new Exception('A slug már foglalt: "' . $slug . '".');
            }

            $childCount = (new \App\Models\CategoryModel())
                ->where('parent_id', $categoryId)
                ->countAllResults();
            if ($childCount > 0) {
                throw new Exception('A termék csak levél (alkategória nélküli) kategóriába helyezhető.');
            }

            $state = $data['state'] ?? \App\Models\ProductMasterModel::STATE_INACTIVE;
            if (!in_array($state, \App\Models\ProductMasterModel::STATES, true)) {
                throw new Exception('Érvénytelen master állapot: ' . $state);
            }

            $insertData = [
                'category_id' => $categoryId,
                'name'        => $name,
                'slug'        => $slug,
                'unit'        => isset($data['unit']) ? trim((string) $data['unit']) : null,
                'state'       => $state,
            ];

            if ($this->model->insert($insertData)) {
                $this->setData(['id' => $this->model->getInsertID()]);
                $this->setSuccess(true);
                $this->setMessage('Termék létrehozva');
            } else {
                throw new Exception(implode(' ', $this->model->errors()));
            }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }

    /**
     * update
     *
     * @return ResponseInterface
     */
    public function update($id = null)
    {
        try {
            $data = $this->request->getRawInput();

            if (!empty($data['slug'])) {
                $existing = $this->model
                    ->where('slug', $data['slug'])
                    ->where('id !=', $id)
                    ->first();
                if ($existing) {
                    throw new Exception('A slug már foglalt: "' . $data['slug'] . '".');
                }
            }

            if (!empty($data['category_id'])) {
                $childCount = (new \App\Models\CategoryModel())
                    ->where('parent_id', $data['category_id'])
                    ->countAllResults();
                if ($childCount > 0) {
                    throw new Exception('A termék csak levél (alkategória nélküli) kategóriába helyezhető.');
                }
            }

            if (isset($data['state']) && !in_array($data['state'], \App\Models\ProductMasterModel::STATES, true)) {
                throw new Exception('Érvénytelen master állapot: ' . $data['state']);
            }

            if ($this->model->update($id, $data)) {
                $this->setSuccess(true);
                $this->setMessage('Sikeres frissítés');
            } else {
                throw new Exception(implode(' ', $this->model->errors()));
            }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }

    /**
     * saveDefaultAttributes
     *
     * @param int|null $id Product master ID
     * @return ResponseInterface
     */
    public function saveDefaultAttributes($id = null)
    {
        try {
            $raw = $this->request->getRawInput()['attributes'] ?? $this->request->getPost('attributes');
            if (is_string($raw)) {
                $attributes = json_decode($raw, true) ?? [];
            } else {
                $attributes = is_array($raw) ? $raw : [];
            }

            $id = (int) $id;
            if ($id < 1) {
                throw new Exception('Hiányzó termék azonosító');
            }

            $db = \Config\Database::connect('shop');
            $db->transStart();

            $defaultModel = new ProductMasterDefaultAttributeModel();
            $defaultModel->where('master_id', $id)->delete();

            foreach ($attributes as $row) {
                $attributeId = isset($row['attribute_id']) ? (int) $row['attribute_id'] : 0;
                if ($attributeId < 1) {
                    continue;
                }

                $defaultModel->insert([
                    'master_id' => $id,
                    'attribute_id' => $attributeId,
                    'default_value' => isset($row['value']) ? trim((string) $row['value']) : '',
                ]);
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new Exception('Alapertelmezett jellemzok mentese sikertelen');
            }

            $this->setSuccess(true);
            $this->setMessage('Alapertelmezett jellemzok mentve');
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }
}
