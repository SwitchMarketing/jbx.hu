<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class NormalizeData extends BaseCommand
{
    protected array $variantSlugCache = [];

    protected $group       = 'Custom';
    protected $name        = 'db:normalize-products';
    protected $description = 'Migrates data from the flat products table to normalized tables.';

    public function run(array $params)
    {
        $db = \Config\Database::connect('shop');
        
        // Clear new tables first to allow re-runs
        $db->table('variant_attribute_values')->truncate();
        $db->query("SET FOREIGN_KEY_CHECKS = 0");
        $db->table('attributes')->truncate();
        $db->table('product_variants')->truncate();
        $db->table('product_masters')->truncate();
        $db->query("SET FOREIGN_KEY_CHECKS = 1");

        $oldProducts = $db->table('products')->get()->getResult();

        $masters = []; // key by parent_sku or own sku
        $attributeMap = [];

        CLI::write('🔄 Starting normalization...', 'yellow');

        // First pass: identify and create masters
        foreach ($oldProducts as $p) {
            $types = json_decode($p->types);
            $isParent = (isset($types->Type) && $types->Type === 'parent');
            $isChild = (isset($types->Type) && $types->Type === 'child');
            
            // If it's a parent, it's definitely a master container
            // If it's standalone (not parent, not child), it's also a master
            if ($isParent || !$isChild) {
                $masterData = [
                    'category_id' => $p->category_id,
                    'name'        => $p->name,
                    'slug'        => $p->slug,
                    'unit'        => $p->unit,
                    'description' => $p->description,
                    'state'       => $this->mapMasterState($p->state),
                    'created_at'  => $p->created_at,
                    'updated_at'  => $p->updated_at,
                ];
                $db->table('product_masters')->insert($masterData);
                $masters[$p->sku] = $db->insertID();
            }
        }

        // Second pass: create variants and attributes
        foreach ($oldProducts as $p) {
            $types = json_decode($p->types);
            $isParent = (isset($types->Type) && $types->Type === 'parent');
            $isChild = (isset($types->Type) && $types->Type === 'child');
            $parentSku = $isChild ? $types->Parent : null;

            // In our new model, EVERY actual purchasable SKU is a variant.
            // UNAS "parents" are often just containers, but sometimes they are also the base product.
            // If it's a child, we link it to the master of its parent.
            // If it's standalone, it's a variant of its own master.
            
            if ($isParent) continue; // Skip pure container parents for variants

            $masterId = $isChild ? ($masters[$parentSku] ?? null) : ($masters[$p->sku] ?? null);
            
            if (!$masterId) {
                // Should not happen if data is consistent, but as fallback:
                $masterData = [
                    'category_id' => $p->category_id,
                    'name'        => $p->name,
                    'slug'        => $p->slug,
                    'unit'        => $p->unit,
                    'description' => $p->description,
                    'state'       => $this->mapMasterState($p->state),
                    'created_at'  => $p->created_at,
                    'updated_at'  => $p->updated_at,
                ];
                $db->table('product_masters')->insert($masterData);
                $masterId = $db->insertID();
                $masters[$p->sku] = $masterId;
            }

            $prices = json_decode($p->prices);
            $stock = json_decode($p->stock);
            
            // Extract Price (handle nested Price object)
            $priceVal = 0;
            if (isset($prices->Price)) {
                if (is_object($prices->Price)) {
                    $priceVal = (float)($prices->Price->Net ?? $prices->Price->Gross ?? 0);
                } else {
                    $priceVal = (float)$prices->Price;
                }
            }

            $variantData = [
                'master_id'  => $masterId,
                'unas_id'    => $p->product_id,
                'sku'        => $p->sku,
                'name'       => $p->name,
                'slug'       => $this->buildVariantSlug($db, (int)$masterId, (string)($p->name ?: $p->sku)),
                'price'      => $priceVal,
                'stock'      => (float)($stock->Value ?? 0),
                'state'      => $p->state,
                'created_at' => $p->created_at,
                'updated_at' => $p->updated_at,
            ];
            $db->table('product_variants')->insert($variantData);
            $variantId = $db->insertID();

            // Handle Attributes (Params)
            if ($p->params) {
                $paramsArr = json_decode($p->params);
                if (is_array($paramsArr)) {
                    foreach ($paramsArr as $param) {
                        $attrName = $param->Name;
                        if (!isset($attributeMap[$attrName])) {
                            $db->table('attributes')->insert(['name' => $attrName]);
                            $attributeMap[$attrName] = $db->insertID();
                        }
                        
                        $db->table('variant_attribute_values')->insert([
                            'variant_id'   => $variantId,
                            'attribute_id' => $attributeMap[$attrName],
                            'value'        => $param->Value
                        ]);
                    }
                }
            }
        }

        CLI::write('✅ Data normalization completed.', 'green');
    }

    protected function mapMasterState(?string $legacy): string
    {
        switch ($legacy) {
            case 'live':     return 'instock';
            case 'draft':    return 'inactive';
            case 'instock':
            case 'backorder':
            case 'inquire':
            case 'inactive': return $legacy;
            default:         return 'instock';
        }
    }

    protected function buildVariantSlug($db, int $masterId, string $source): string
    {
        $base = $this->slugify($source);
        if ($base === '') {
            $base = 'variant';
        }

        $slug = $base;
        $i = 2;

        while (
            isset($this->variantSlugCache[$masterId . ':' . $slug]) ||
            $db->table('product_variants')->where('master_id', $masterId)->where('slug', $slug)->countAllResults() > 0
        ) {
            $slug = $base . '-' . $i;
            $i++;
        }

        $this->variantSlugCache[$masterId . ':' . $slug] = true;

        return $slug;
    }

    protected function slugify(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';

        return trim($value, '-');
    }
}
