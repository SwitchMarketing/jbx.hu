<?php

namespace App\Controllers\Admin;

use Exception;
use Throwable;

class Categories extends BaseResourceController
{
    protected $modelName = '\App\Models\CategoryModel';

    /**
     * index
     *
     * @return ResponseInterface
     */
    public function index()
    {
        try {
            // We use CategoryTreeModel for the list to have the path and depth
            $treeModel = new \App\Models\CategoryTreeModel();
            $categories = $treeModel->orderBy('path', 'asc')->findAll();
            
            $this->setData($categories);
            $this->setTotal(count($categories));
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
            if (!is_object($category = $this->model->find($id))) {
                throw new Exception('Nincs ilyen rekord!');
            }

            $this->setData($category);
            $this->setSuccess(true);
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }

    /**
     * create
     *
     * @return ResponseInterface
     */
    public function create()
    {
        try {
            $data = $this->normalizeCategoryData($this->request->getPost());
            $db = \Config\Database::connect('shop');

            if ($data['name'] === '') {
                throw new Exception('A kategória neve kötelező.');
            }
            if ($data['slug'] === '') {
                throw new Exception('A slug megadása kötelező.');
            }

            $parentId = isset($data['parent_id']) ? (int) $data['parent_id'] : 0;
            if ($parentId > 0) {
                $parent = $this->model->find($parentId);
                if (!is_object($parent)) {
                    throw new Exception('A megadott szülő kategória nem létezik.');
                }

                $parentProductCount = (new \App\Models\ProductMasterModel())
                    ->where('category_id', $parentId)
                    ->countAllResults();
                if ($parentProductCount > 0) {
                    throw new Exception('Ehhez a kategóriához már termékek tartoznak, ezért nem lehet alkategóriát létrehozni alá. Előbb helyezd át a termékeket levél kategóriába.');
                }
            }

            // unas_id is the primary key but not auto_increment in the schema
            // (legacy UNAS-sourced). Generate next available id for new categories.
            if (empty($data['unas_id'])) {
                $data['unas_id'] = $this->getNextCategoryId($db);
            }

            $slugExists = $this->model->where('slug', $data['slug'])->first();
            if ($slugExists) {
                throw new Exception('A slug már foglalt: "' . $data['slug'] . '".');
            }

            $inserted = false;
            for ($attempt = 0; $attempt < 3; $attempt++) {
                try {
                    $inserted = (bool) $this->model->insert($data);
                    if ($inserted) {
                        break;
                    }

                    throw new Exception($this->getModelErrorMessage('A kategória mentése sikertelen.'));
                } catch (Throwable $e) {
                    $message = $e->getMessage() ?? '';
                    if (stripos($message, 'Duplicate entry') !== false && stripos($message, 'PRIMARY') !== false) {
                        $data['unas_id'] = $this->getNextCategoryId($db);
                        continue;
                    }

                    throw $e;
                }
            }

            if (!$inserted) {
                throw new Exception('A kategória mentése sikertelen: nem sikerült egyedi azonosítót foglalni.');
            }

            $this->rebuildTree();
            $this->setData(['unas_id' => $data['unas_id']]);
            $this->setSuccess(true);
            $this->setMessage('Sikeres mentés');
        } catch (Exception $e) {
            \Config\Services::logger()->error('Category create failed: ' . $e->getMessage());
            $this->setMessage($e->getMessage() !== '' ? $e->getMessage() : 'A kategória mentése sikertelen.');
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
            $data = $this->normalizeCategoryData($this->request->getRawInput());

            if (!is_object($current = $this->model->find($id))) {
                throw new Exception('Nincs ilyen rekord!');
            }

            if ($data['name'] === '') {
                throw new Exception('A kategória neve kötelező.');
            }
            if ($data['slug'] === '') {
                throw new Exception('A slug megadása kötelező.');
            }

            $slugExists = $this->model
                ->where('slug', $data['slug'])
                ->where('unas_id !=', $id)
                ->first();
            if ($slugExists) {
                throw new Exception('A slug már foglalt: "' . $data['slug'] . '".');
            }

            $parentId = array_key_exists('parent_id', $data)
                ? (int) $data['parent_id']
                : (int) $current->parent_id;

            if ($parentId === (int) $id) {
                throw new Exception('A kategória nem lehet önmaga szülője.');
            }

            if ($parentId > 0) {
                $parent = $this->model->find($parentId);
                if (!is_object($parent)) {
                    throw new Exception('A megadott szülő kategória nem létezik.');
                }

                $parentProductCount = (new \App\Models\ProductMasterModel())
                    ->where('category_id', $parentId)
                    ->countAllResults();
                if ($parentProductCount > 0) {
                    throw new Exception('Ehhez a kategóriához már termékek tartoznak, ezért nem lehet alkategóriát mozgatni/létrehozni alá. Előbb helyezd át a termékeket levél kategóriába.');
                }
            }

            $changedData = [];
            foreach (['name', 'slug', 'parent_id', 'order'] as $field) {
                if (!array_key_exists($field, $data)) {
                    continue;
                }

                $currentValue = $current->{$field} ?? null;
                if ((string) $currentValue !== (string) $data[$field]) {
                    $changedData[$field] = $data[$field];
                }
            }

            if (empty($changedData)) {
                $this->setSuccess(true);
                $this->setMessage('Nincs módosítás');
                return $this->setResponse();
            }
            
            if ($this->model->update($id, $changedData)) {
                $this->rebuildTree();
                $this->setSuccess(true);
                $this->setMessage('Sikeres frissítés');
            } else {
                throw new Exception($this->getModelErrorMessage('A kategória frissítése sikertelen.'));
            }
        } catch (Exception $e) {
            \Config\Services::logger()->error('Category update failed: ' . $e->getMessage());
            $this->setMessage($e->getMessage() !== '' ? $e->getMessage() : 'A kategória frissítése sikertelen.');
        } finally {
            return $this->setResponse();
        }
    }

    /**
     * delete
     *
     * @return ResponseInterface
     */
    public function delete($id = null)
    {
        try {
            $id = (int) $id;
            if ($id < 1) {
                throw new Exception('Hiányzó kategória azonosító');
            }

            $category = $this->model->find($id);
            if (!is_object($category)) {
                throw new Exception('Nincs ilyen rekord!');
            }

            $childCount = $this->model->where('parent_id', $id)->countAllResults();
            if ($childCount > 0) {
                throw new Exception('Nem törölhető: a kategóriának vannak alkategóriái.');
            }

            $productCount = (new \App\Models\ProductMasterModel())
                ->where('category_id', $id)
                ->countAllResults();
            if ($productCount > 0) {
                throw new Exception('Nem törölhető: a kategóriához termékek tartoznak.');
            }

            $oldSlug = trim((string) ($category->slug ?? ''));
            if ($oldSlug !== '') {
                $archivedSlug = $this->buildDeletedSlug($oldSlug, $id);
                $this->model->update($id, ['slug' => $archivedSlug]);
            }

            if ($this->model->delete($id)) {
                $this->rebuildTree();
                $this->setSuccess(true);
                $this->setMessage('Sikeres törlés');
            } else {
                throw new Exception('Törlési hiba');
            }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }

    /**
     * uploadImage
     *
     * Multipart upload endpoint for category image.
     * Field: image(file)
     *
     * @param int|null $id Category unas_id
     * @return ResponseInterface
     */
    public function uploadImage($id = null)
    {
        try {
            $id = (int) $id;
            if ($id < 1) {
                throw new Exception('Hiányzó kategória azonosító');
            }

            if (!is_object($this->model->find($id))) {
                throw new Exception('Nincs ilyen kategória');
            }

            $file = $this->request->getFile('image');
            if (!$file) {
                throw new Exception('Nincs fájl a kérésben');
            }
            if (!$file->isValid()) {
                throw new Exception('Érvénytelen fájl: ' . $file->getErrorString());
            }

            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
            $mimeType = $file->getMimeType();
            if (!in_array($mimeType, $allowedMimes, true)) {
                throw new Exception('Nem támogatott fájltípus: ' . $mimeType);
            }

            $targetDir = FCPATH . 'imgs/products';
            if (!is_dir($targetDir)) {
                if (!@mkdir($targetDir, 0775, true)) {
                    throw new Exception('A célmappa nem hozható létre: ' . $targetDir);
                }
            }
            if (!is_writable($targetDir)) {
                throw new Exception('A célmappa nem írható: ' . $targetDir);
            }

            $storedName = $file->getRandomName();
            if (!$file->move($targetDir, $storedName)) {
                throw new Exception('Fájl mozgatás sikertelen. Elérési út: ' . $targetDir . '/' . $storedName);
            }

            if (!file_exists($targetDir . '/' . $storedName)) {
                throw new Exception('Fájl feltöltés sikertelen, a fájl nem létezik a célon.');
            }

            if (!$this->model->update($id, ['image' => $storedName])) {
                $errors = $this->model->errors();
                throw new Exception('Adatbázis hiba: ' . (is_array($errors) ? implode('; ', $errors) : $errors));
            }

            $this->rebuildTree();
            $this->setData(['image' => $storedName]);
            $this->setSuccess(true);
            $this->setMessage('Kategória kép feltöltve');
        } catch (Exception $e) {
            $msg = $e->getMessage();
            if (empty($msg)) {
                $msg = 'Ismeretlen hiba történt a feltöltés közben';
            }
            $this->setMessage($msg);
            \Config\Services::logger()->error('Category image upload error: ' . $msg);
        } finally {
            return $this->setResponse();
        }
    }

    /**
     * rebuildTree
     *
     * @return void
     */
    protected function rebuildTree()
    {
        $treeModel = new \App\Models\CategoryTreeModel();
        $treeModel->updateTree();

        // Clear cache
        @unlink(WRITEPATH . 'cache/category_routes.php');

        // Regenerate routes
        \App\Helpers\CategoryRouteCache::generate();
    }

    protected function buildDeletedSlug(string $slug, int $id): string
    {
        $suffix = '--deleted-' . $id . '-' . date('YmdHis');
        $maxBaseLen = 255 - strlen($suffix);
        $base = substr($slug, 0, max(1, $maxBaseLen));
        return $base . $suffix;
    }

    protected function normalizeCategoryData(array $data): array
    {
        if (array_key_exists('name', $data)) {
            $data['name'] = trim((string) $data['name']);
        }
        if (array_key_exists('slug', $data)) {
            $data['slug'] = trim((string) $data['slug']);
        }
        if (array_key_exists('parent_id', $data)) {
            $data['parent_id'] = (int) $data['parent_id'];
        }
        if (array_key_exists('order', $data)) {
            $data['order'] = max(0, (int) $data['order']);
        }

        return $data;
    }

    protected function getModelErrorMessage(string $fallback): string
    {
        $errors = $this->model->errors();
        if (is_array($errors)) {
            $errors = array_filter(array_map('trim', $errors));
            if (!empty($errors)) {
                return implode(' ', $errors);
            }
        } elseif (is_string($errors) && trim($errors) !== '') {
            return trim($errors);
        }

        $dbError = $this->model->db->error();
        if (!empty($dbError['message'])) {
            return trim((string) $dbError['message']);
        }

        return $fallback;
    }

    protected function getNextCategoryId($db): int
    {
        $row = $db->table('categories')
            ->selectMax('unas_id', 'max_id')
            ->get()
            ->getRow();

        return ((int) ($row->max_id ?? 0)) + 1;
    }
}
