<?php

namespace App\Controllers\Admin;

use Exception;

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
            $data = $this->request->getPost();

            // unas_id is the primary key but not auto_increment in the schema
            // (legacy UNAS-sourced). Generate next available id for new categories.
            if (empty($data['unas_id'])) {
                $max = $this->model->selectMax('unas_id')->first();
                $data['unas_id'] = ((int) ($max->unas_id ?? 0)) + 1;
            }

            if ($this->model->insert($data)) {
                $this->rebuildTree();
                $this->setData(['unas_id' => $data['unas_id']]);
                $this->setSuccess(true);
                $this->setMessage('Sikeres mentés');
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
            
            if ($this->model->update($id, $data)) {
                $this->rebuildTree();
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
     * delete
     *
     * @return ResponseInterface
     */
    public function delete($id = null)
    {
        try {
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
}
