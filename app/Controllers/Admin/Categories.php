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
