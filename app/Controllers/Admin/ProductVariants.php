<?php

namespace App\Controllers\Admin;

use Exception;
use App\Models\ProductMasterDefaultAttributeModel;
use App\Models\ProductVariantModel;

class ProductVariants extends BaseResourceController
{
    protected $modelName = '\App\Models\ProductVariantModel';

    /**
     * create — POST /admin/productvariants
     * Body must include master_id and sku.
     *
     * @return ResponseInterface
     */
    public function create()
    {
        try {
            $data = $this->request->getRawInput();

            if (empty($data['master_id'])) {
                throw new Exception('Hiányzó master_id');
            }
            if (empty($data['sku'])) {
                throw new Exception('A SKU megadása kötelező');
            }

            $state = $data['state'] ?? ProductVariantModel::STATE_INSTOCK;
            if (!in_array($state, ProductVariantModel::STATES, true)) {
                throw new Exception('Érvénytelen állapot: ' . $state);
            }

            $masterId = (int) $data['master_id'];
            $maxPosition = $this->model->selectMax('position')->where('master_id', $masterId)->first();
            $nextPosition = isset($maxPosition->position) ? ((int) $maxPosition->position + 1) : 1;

            $insertData = [
                'master_id' => $masterId,
                'sku'       => trim($data['sku']),
                'name'      => $data['name']  ?? null,
                'price'     => $data['price'] ?? 0,
                'discount_price' => $data['discount_price'] ?? null,
                'stock'     => $data['stock'] ?? 0,
                'position'  => isset($data['position']) ? (int) $data['position'] : $nextPosition,
                'state'     => $state,
            ];

            if ($this->model->insert($insertData)) {
                $newId = (int) $this->model->getInsertID();

                // Copy master-level default attributes to the newly created variant.
                $copyDefaults = !isset($data['copy_defaults']) || !in_array($data['copy_defaults'], ['0', 'false', 0, false], true);
                if ($copyDefaults) {
                    $db = \Config\Database::connect('shop');
                    $defaults = (new ProductMasterDefaultAttributeModel())
                        ->where('master_id', $masterId)
                        ->findAll();

                    foreach ($defaults as $row) {
                        $db->table('variant_attribute_values')->insert([
                            'variant_id' => $newId,
                            'attribute_id' => (int) $row->attribute_id,
                            'value' => isset($row->default_value) ? (string) $row->default_value : '',
                        ]);
                    }
                }

                $this->setData(['id' => $newId, 'position' => $insertData['position']]);
                $this->setSuccess(true);
                $this->setMessage('Variáció létrehozva');
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
     * delete — removes variant and its attribute values.
     *
     * @return ResponseInterface
     */
    public function delete($id = null)
    {
        try {
            $id = (int) $id;
            if ($id < 1) {
                throw new Exception('Hiányzó azonosító');
            }

            $variant = $this->model->find($id);
            if (!is_object($variant)) {
                throw new Exception('Nincs ilyen rekord!');
            }

            $oldSlug = trim((string) ($variant->slug ?? ''));
            if ($oldSlug !== '') {
                $this->model->update($id, ['slug' => $this->buildDeletedSlug($oldSlug, $id)]);
            }

            if ($this->model->delete($id)) {
                $this->setSuccess(true);
                $this->setMessage('Variáció törölve');
            } else {
                throw new Exception(implode(' ', $this->model->errors()));
            }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }

    protected function buildDeletedSlug(string $slug, int $id): string
    {
        $suffix = '--deleted-' . $id . '-' . date('YmdHis');
        $maxBaseLen = 255 - strlen($suffix);
        $base = substr($slug, 0, max(1, $maxBaseLen));
        return $base . $suffix;
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
            
            // For variations we update price, stock, sku, name, and state
            $updateData = [];
            if (isset($data['price'])) $updateData['price'] = $data['price'];
            if (isset($data['discount_price'])) $updateData['discount_price'] = $data['discount_price'];
            if (isset($data['stock'])) $updateData['stock'] = $data['stock'];
            if (isset($data['sku']))   $updateData['sku']   = $data['sku'];
            if (isset($data['name']))  $updateData['name']  = $data['name'];
            if (isset($data['position'])) $updateData['position'] = (int) $data['position'];
            if (isset($data['state'])) {
                if (!in_array($data['state'], ProductVariantModel::STATES, true)) {
                    throw new Exception('Érvénytelen állapot: ' . $data['state']);
                }
                $updateData['state'] = $data['state'];
            }

            if (!empty($updateData)) {
                if ($this->model->update($id, $updateData)) {
                    $this->setSuccess(true);
                    $this->setMessage('Variáció frissítve');
                } else {
                    throw new Exception(implode(' ', $this->model->errors()));
                }
            } else {
                throw new Exception('Nincs módosítandó adat');
            }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }

    /**
     * reorder - bulk reorder variant positions.
     * Expects rows: [{id: number, position: number}, ...]
     */
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

            $db = \Config\Database::connect('shop');
            $db->transStart();

            foreach ($rows as $row) {
                $id = isset($row['id']) ? (int) $row['id'] : 0;
                $position = isset($row['position']) ? (int) $row['position'] : 0;
                if ($id < 1) {
                    continue;
                }
                $this->model->update($id, ['position' => max(0, $position)]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new Exception('A variációk sorrend mentése sikertelen');
            }

            $this->setSuccess(true);
            $this->setMessage('Variáció sorrend mentve');
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }

    /**
     * parseNames — extract attributes from variant names using a template.
     *
     * Template syntax: "literal text {{AttrName}} more literal {{OtherAttr}}..."
     * Runs against every variant of the given master. When apply=true, writes
     * captured values to variant_attribute_values (creating attributes on the fly).
     *
     * @param  int $masterId
     * @return ResponseInterface
     */
    public function parseNames($masterId = null)
    {
        try {
            $data = $this->request->getPost();
            $pattern = trim($data['pattern'] ?? '');
            $apply   = !empty($data['apply']) && !in_array($data['apply'], ['false', '0'], true);

            if ($pattern === '') {
                throw new Exception('Hiányzó minta');
            }

            [$regex, $names] = $this->compileTemplate($pattern);
            if (empty($names)) {
                throw new Exception('A mintában nincs {{placeholder}}');
            }

            $variants = $this->model->where('master_id', $masterId)->findAll();

            $matches = [];
            foreach ($variants as $v) {
                $row = [
                    'variant_id' => $v->id,
                    'name'       => $v->name,
                    'matched'    => false,
                    'extracted'  => (object)[],
                ];
                if (!empty($v->name) && preg_match($regex, $v->name, $m)) {
                    $row['matched'] = true;
                    $extracted = [];
                    foreach ($names as $i => $n) {
                        $extracted[$n] = trim($m[$i + 1]);
                    }
                    $row['extracted'] = $extracted;
                }
                $matches[] = $row;
            }

            $attrModel = new \App\Models\AttributeModel();
            $attrs = [];
            foreach ($names as $n) {
                $existing = $attrModel->where('name', $n)->first();
                $attrs[$n] = [
                    'id'       => $existing->id ?? null,
                    'existing' => !empty($existing),
                ];
            }

            if ($apply) {
                $db = \Config\Database::connect('shop');

                foreach ($names as $n) {
                    if (empty($attrs[$n]['id'])) {
                        $attrModel->insert(['name' => $n]);
                        $attrs[$n]['id'] = $attrModel->getInsertID();
                        $attrs[$n]['existing'] = true;
                    }
                }

                $applied = 0;
                $skipped = 0;
                foreach ($matches as $mRow) {
                    if (!$mRow['matched']) {
                        $skipped++;
                        continue;
                    }
                    $vid = $mRow['variant_id'];
                    $db->table('variant_attribute_values')->where('variant_id', $vid)->delete();
                    foreach ($mRow['extracted'] as $n => $val) {
                        if ($val === '') continue;
                        $db->table('variant_attribute_values')->insert([
                            'variant_id'   => $vid,
                            'attribute_id' => $attrs[$n]['id'],
                            'value'        => $val,
                        ]);
                    }
                    $applied++;
                }

                $this->setData(['applied' => $applied, 'skipped' => $skipped, 'attributes' => $attrs]);
                $this->setSuccess(true);
                $this->setMessage("{$applied} variációra alkalmazva, {$skipped} kihagyva");
            } else {
                $this->setData(['matches' => $matches, 'attributes' => $attrs]);
                $this->setSuccess(true);
                $this->setMessage('OK');
            }
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }

    /**
     * Convert a {{Name}} template to a PCRE regex with capture groups in order.
     *
     * @return array{0: string, 1: string[]}  [regex, placeholderNames]
     */
    protected function compileTemplate(string $template): array
    {
        $names = [];
        $regex = '';
        $parts = preg_split('/(\{\{[^{}]+\}\})/u', $template, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($parts as $part) {
            if ($part === '') continue;
            if (preg_match('/^\{\{(.+)\}\}$/u', $part, $m)) {
                $names[] = trim($m[1]);
                $regex .= '(.+?)';
            } else {
                $regex .= preg_quote($part, '/');
            }
        }
        return ['/^' . $regex . '$/u', $names];
    }

    /**
     * saveAttributes
     *
     * @param  int $id Variant ID
     * @return ResponseInterface
     */
    public function saveAttributes($id = null)
    {
        try {
            $raw = $this->request->getPost('attributes');
            if (is_string($raw)) {
                $attributes = json_decode($raw, true) ?? [];
            } else {
                $attributes = is_array($raw) ? $raw : [];
            }

            $db = \Config\Database::connect('shop');
            $db->table('variant_attribute_values')->where('variant_id', $id)->delete();

            $inserted = 0;
            if (!empty($attributes) && is_array($attributes)) {
                foreach ($attributes as $attr) {
                    $aid = $attr['attribute_id'] ?? null;
                    $val = $attr['value'] ?? null;
                    if ($aid && $val !== null && $val !== '') {
                        $db->table('variant_attribute_values')->insert([
                            'variant_id'   => (int) $id,
                            'attribute_id' => (int) $aid,
                            'value'        => $val,
                        ]);
                        $inserted++;
                    }
                }
            }

            $this->setSuccess(true);
            $this->setMessage("Jellemzők mentve ({$inserted})");
        } catch (Exception $e) {
            $this->setMessage($e->getMessage());
        } finally {
            return $this->setResponse();
        }
    }
}
