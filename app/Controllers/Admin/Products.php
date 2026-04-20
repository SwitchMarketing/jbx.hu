<?php

namespace App\Controllers\Admin;

use Exception;
use App\Models\ProductVariantModel;
use App\Models\CategoryTreeModel;
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
}
