<?php

namespace App\Models;

use App\Models\BaseModel;
use CodeIgniter\Database\SQLite3\Builder;

class ProductModel extends BaseModel
{
    protected $DBGroup          = 'shop';
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'product_id',
        'sku',
        'category_id',        
        'name',
        'slug',
        'unit',
        'description',
        'params',
        'types',
        'prices',
        'inquire',
        'state'
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = ['getProductImages'];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * initialize
     *
     * @return void
     */
    public function initialize()
    {        
        $this->extraFields = [
            'category_name' => 'categories.name',
            'category_slug' => 'categories.slug',
            'category_path' => 'categories.path',
            'image'         => 'images.filename'
        ];
        $this->_setDefaultFields();        
                  
    }

    
    /**
     * getChildren
     *
     * @param  mixed $parentSku
     * @return object
     */
    public function getChildren($parentSku)
    {
        return $this->where("json_extract(types, '$.Parent') =", $parentSku)
                    ->where("json_extract(types, '$.Type') =", 'child')
                    ->findAll(0);
    }

    
    /**
     * getOptions
     *
     * @param  mixed $parentSku
     * @param  mixed $filters
     * @return array
     */
    public function getOptions($parentSku, $filters = [])
    {

        // kell a szülő termék is
        $parent = $this->where('sku', $parentSku)
                       ->where("json_extract(types, '$.Type') =", 'parent')
                       ->first();

        $builder = $this->db->table('products');
        
        unset($filters['parent']);

		$builder->select('slug, params')
			->where("json_extract(types, '$.Type') =", 'child')
			->where("json_extract(types, '$.Parent') =", $parentSku);

        // szűrők alkalmazása
        if (!empty($filters)) {
            foreach ($filters as $name => $value) {
                $builder->where("EXISTS (
                    SELECT 1 FROM json_each(params)
                    WHERE json_extract(json_each.value, '$.Name') = ".$this->db->escape($name)."
                    AND trim(json_extract(json_each.value, '$.Value')) = ".$this->db->escape(trim($value))."
                )");
            }
        }
		
		$children = $builder->get()->getResult();

		// echo $this->db->getLastQuery()->getQuery();
		$options = [];

        // a szülő termék paraméterei
        if(!empty($parent->params)) {
            foreach (json_decode($parent->params, true) as $p) {
                if(!is_array($p) || !isset($p['Name']) || !isset($p['Value'])) {
                    continue;
                }
                $id = $p['Id'] ?? null;
                $n = $p['Name'];
                $v = trim($p['Value']);
                if(!array_key_exists($id, $options)) {
                    $options[$id] = [
                        'id' => $id,
                        'name' => $n,
                        'values' => []
                    ];
                }
                // ha még nincs ilyen érték, akkor hozzáadjuk
                $found = false;
                foreach ($options[$id]['values'] as $val) {
                    if ($val['value'] === $v) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $options[$id]['values'][] = [
                        'value' => $v,
                        'slug'  => $parent->slug
                    ];
                }
            }
        }

		foreach ($children as $child) {
			$slug = $child->slug;
            if(empty($child->params)) {
                continue;
            }
			foreach (json_decode($child->params, true) as $p) {
                if(!is_array($p) || !isset($p['Name']) || !isset($p['Value'])) {
                    continue;
                }
                $id = $p['Id'] ?? null;
                
				$n = $p['Name'];
				$v = trim($p['Value']);
				
                if(array_key_exists($id, $options)) {
                 
                    // ha még nincs ilyen érték, akkor hozzáadjuk
                    $found = false;
                    foreach ($options[$id]['values'] as $val) {
                        if ($val['value'] === $v) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $options[$id]['values'][] = [
                            'value' => $v,
                            'slug'  => $slug
                        ];
                    }
                    
                    
                } else {
                    $options[$id] = [
                        'id' => $id,
                        'name' => $n,
                        'values' => [
                            [
                                'value' => $v,
                                'slug'  => $slug
                            ]
                        ]
                    ];                    
                }                
                
                // minden elérhető értékhez hozzárendeljük a slugját
				// $options[$n][$v] = $slug;
			}
            // az options tömb kulcsai legyenek rendezettek                
		}

        // az options tömb rendzése
        // --- 1) Rendezés name szerint (ABC) ---
        uasort($options, function($a, $b) {
            return strcasecmp($a['name'], $b['name']); // kis/nagybetű érzéketlen
        });

        // --- 2) Values rendezése számszerint, ha lehet ---
        foreach ($options as &$item) {
            usort($item['values'], function($a, $b) {
                // kinyerjük az elején lévő számot, ha van
                preg_match('/^\d+/', $a['value'], $ma);
                preg_match('/^\d+/', $b['value'], $mb);
                $numA = $ma[0] ?? null;
                $numB = $mb[0] ?? null;

                if ($numA !== null && $numB !== null) {
                    return (int)$numA <=> (int)$numB; // szám szerinti összehasonlítás
                }
                return strcasecmp($a['value'], $b['value']); // ha nincs szám, szöveg szerint
            });
        }
        unset($item); // referencia megszüntetése        

		return $options;
    }   
   


    /**
     * _setSelect
     *
     * @param  mixed $builder
     * @return Builder
     */
    protected function _setSelect($builder):Builder
    {
         //columns
         $columns = $this->displayFields ?? $this->table.'.*';

         $columns = implode(',', array_map(
             function ($v, $k) {
                return $v.' AS '.$k;
             },
             $columns,
             array_keys($columns)
         ));       
                  
         $builder->select($columns, false)
                ->join('category_tree as categories', $this->table.'.category_id = categories.unas_id')
                ->join('(
                    SELECT product_id, filename FROM images GROUP BY product_id
                ) AS images', $this->table.'.product_id = images.product_id', 'left');

         return $builder;
    } 

        
    /**
     * getProductImages
     *
     * @param  mixed $data
     * @return array
     */
    protected function getProductImages(array $data): array
    {
        if (isset($data['data']) && is_object($data['data'])) {
            $images = new \App\Models\ImageModel();
            $data['data']->images = $images->where('product_id', $data['data']->product_id)->findAll();            
        }

        return $data;
    }
}
