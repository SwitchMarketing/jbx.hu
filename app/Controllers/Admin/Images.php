<?php

namespace App\Controllers\Admin;

use Exception;
use App\Models\ProductVariantModel;

class Images extends BaseResourceController
{
    protected $modelName = '\\App\\Models\\ImageModel';

    public function index()
    {
        try {
            $masterId = (int) ($this->request->getGet('master_id') ?? 0);
            if ($masterId < 1) {
                throw new Exception('Hiányzó master_id');
            }

            $rows = $this->model
                ->where('master_id', $masterId)
                ->orderBy('position', 'ASC')
                ->orderBy('id', 'ASC')
                ->findAll();

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

    public function create()
    {
        try {
            $data = $this->request->getRawInput();

            $masterId = (int) ($data['master_id'] ?? 0);
            $filename = trim((string) ($data['filename'] ?? ''));

            if ($masterId < 1) {
                throw new Exception('Hiányzó master_id');
            }
            if ($filename === '') {
                throw new Exception('A kep eleresi utja kotelezo');
            }

            $maxPosition = $this->model->selectMax('position')->where('master_id', $masterId)->first();
            $nextPosition = isset($maxPosition->position) ? ((int) $maxPosition->position + 1) : 1;

            $insert = [
                'master_id' => $masterId,
                'variant_id' => isset($data['variant_id']) && $data['variant_id'] !== '' ? (int) $data['variant_id'] : null,
                'filename' => $filename,
                'alt' => isset($data['alt']) ? trim((string) $data['alt']) : null,
                'position' => isset($data['position']) ? (int) $data['position'] : $nextPosition,
            ];

            if (!$this->model->insert($insert)) {
                throw new Exception(implode(' ', $this->model->errors()));
            }

            $this->setData(['id' => $this->model->getInsertID()]);
            $this->setSuccess(true);
            $this->setMessage('Kep mentve');
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
                throw new Exception('Hiányzó azonosító');
            }

            $data = $this->request->getRawInput();
            $update = [];

            if (isset($data['filename'])) {
                $filename = trim((string) $data['filename']);
                if ($filename === '') {
                    throw new Exception('A kep eleresi utja kotelezo');
                }
                $update['filename'] = $filename;
            }
            if (isset($data['alt'])) {
                $update['alt'] = trim((string) $data['alt']);
            }
            if (isset($data['position'])) {
                $update['position'] = max(0, (int) $data['position']);
            }
            if (isset($data['variant_id'])) {
                $update['variant_id'] = $data['variant_id'] === '' ? null : (int) $data['variant_id'];
            }

            if (empty($update)) {
                throw new Exception('Nincs modositando adat');
            }

            if (!$this->model->update($id, $update)) {
                throw new Exception(implode(' ', $this->model->errors()));
            }

            $this->setSuccess(true);
            $this->setMessage('Kep frissitve');
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
                throw new Exception('Hiányzó azonosító');
            }

            if (!$this->model->delete($id)) {
                throw new Exception('Torlesi hiba');
            }

            $this->setSuccess(true);
            $this->setMessage('Kep torolve');
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }

    public function reorder()
    {
        try {
            $rows = $this->request->getRawInput()['rows'] ?? $this->request->getPost('rows');
            if (is_string($rows)) {
                $rows = json_decode($rows, true);
            }

            if (empty($rows) || !is_array($rows)) {
                throw new Exception('Hiányzó sor-rendezési adatok');
            }

            foreach ($rows as $row) {
                $id = isset($row['id']) ? (int) $row['id'] : 0;
                if ($id < 1) {
                    continue;
                }

                $position = isset($row['position']) ? (int) $row['position'] : 0;
                $this->model->update($id, ['position' => max(0, $position)]);
            }

            $this->setSuccess(true);
            $this->setMessage('Kepsorrend mentve');
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }

    /**
     * upload
     *
     * Multipart upload endpoint for product images.
     * Fields: image(file), master_id(required), variant_id(optional), alt(optional), position(optional)
     */
    public function upload()
    {
        try {
            $masterId = (int) ($this->request->getPost('master_id') ?? 0);
            if ($masterId < 1) {
                throw new Exception('Hiányzó master_id');
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

            $variantId = $this->request->getPost('variant_id');
            $variantId = ($variantId === null || $variantId === '') ? null : (int) $variantId;

            $legacyProductId = null;
            if ($variantId) {
                $variant = (new ProductVariantModel())->find($variantId);
                if ($variant && !empty($variant->unas_id)) {
                    $legacyProductId = (string) $variant->unas_id;
                }
            }

            $maxPosition = $this->model->selectMax('position')->where('master_id', $masterId)->first();
            $nextPosition = isset($maxPosition->position) ? ((int) $maxPosition->position + 1) : 1;

            $insert = [
                'master_id' => $masterId,
                'variant_id' => $variantId,
                'product_id' => $legacyProductId,
                'filename' => $storedName,
                'alt' => trim((string) ($this->request->getPost('alt') ?? '')) ?: null,
                'position' => (int) ($this->request->getPost('position') ?? $nextPosition),
            ];

            if (!$this->model->insert($insert)) {
                $errors = $this->model->errors();
                throw new Exception('Adatbázis hiba: ' . (is_array($errors) ? implode('; ', $errors) : $errors));
            }

            $this->setData([
                'id' => $this->model->getInsertID(),
                'filename' => $storedName,
            ]);
            $this->setSuccess(true);
            $this->setMessage('Kép feltöltve');
        } catch (Exception $e) {
            $msg = $e->getMessage();
            if (empty($msg)) {
                $msg = 'Ismeretlen hiba történt a feltöltés közben';
            }
            $this->setMessage($msg);
            \Config\Services::logger()->error('Image upload error: ' . $msg);
        } finally {
            return $this->setResponse();
        }
    }
}
