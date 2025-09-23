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
		foreach ($children as $child) {
			$slug = $child->slug;
			foreach (json_decode($child->params, true) as $p) {
                if(!is_array($p) || !isset($p['Name']) || !isset($p['Value'])) {
                    continue;
                }
				$n = $p['Name'];
				$v = trim($p['Value']);
				// minden elérhető értékhez hozzárendeljük a slugját
				$options[$n][$v] = $slug;
			}
		}

		// opcionálisan: duplikátum szűrés
		foreach ($options as &$group) {
			$group = array_unique($group);
		}

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
