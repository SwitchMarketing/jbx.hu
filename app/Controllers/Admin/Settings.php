<?php

namespace App\Controllers\Admin;

use Exception;

class Settings extends BaseResourceController
{
    protected $modelName = '\\App\\Models\\SettingModel';

    public function index()
    {
        try {
            $rows = $this->model->orderBy('setting_key', 'ASC')->findAll();
            $this->setData($rows);
            $this->setTotal(count($rows));
            $this->setSuccess(true);
            $this->setMessage('OK');
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }

    public function show($id = null)
    {
        try {
            if (!is_object($row = $this->model->find($id))) {
                throw new Exception('Nincs ilyen rekord');
            }

            $this->setData($row);
            $this->setSuccess(true);
            $this->setMessage('OK');
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }

    public function create()
    {
        try {
            $data = $this->request->getRawInput();
            $key = trim((string) ($data['setting_key'] ?? ''));
            if ($key === '') {
                throw new Exception('A kulcs kotelezo');
            }

            if ($this->model->where('setting_key', $key)->first()) {
                throw new Exception('Mar letezik ilyen kulcs');
            }

            $insert = [
                'setting_key' => $key,
                'setting_value' => isset($data['setting_value']) ? (string) $data['setting_value'] : '',
                'data_type' => isset($data['data_type']) ? (string) $data['data_type'] : 'string',
                'description' => isset($data['description']) ? (string) $data['description'] : null,
            ];

            if (!$this->model->insert($insert)) {
                throw new Exception(implode(' ', $this->model->errors()));
            }

            $this->setData(['id' => $this->model->getInsertID()]);
            $this->setSuccess(true);
            $this->setMessage('Beallitas letrehozva');
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }

    public function update($id = null)
    {
        try {
            $id = (int) $id;
            if ($id < 1) {
                throw new Exception('Hianyzik az azonosito');
            }

            $data = $this->request->getRawInput();
            $update = [];
            if (isset($data['setting_value'])) {
                $update['setting_value'] = (string) $data['setting_value'];
            }
            if (isset($data['description'])) {
                $update['description'] = (string) $data['description'];
            }
            if (isset($data['data_type'])) {
                $update['data_type'] = (string) $data['data_type'];
            }

            if (empty($update)) {
                throw new Exception('Nincs modositando adat');
            }

            if (!$this->model->update($id, $update)) {
                throw new Exception(implode(' ', $this->model->errors()));
            }

            $this->setSuccess(true);
            $this->setMessage('Beallitas frissitve');
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }

    public function delete($id = null)
    {
        try {
            $id = (int) $id;
            if ($id < 1) {
                throw new Exception('Hianyzik az azonosito');
            }

            if (!$this->model->delete($id)) {
                throw new Exception('Torlesi hiba');
            }

            $this->setSuccess(true);
            $this->setMessage('Beallitas torolve');
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }
}
