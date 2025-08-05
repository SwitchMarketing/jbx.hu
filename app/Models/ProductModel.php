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
        'prices'
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
    protected $afterFind      = [];
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
            'image'         => 'images.filename'
        ];
        $this->_setDefaultFields();
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
                ->join('categories', $this->table.'.category_id = categories.unas_id')
                ->join('(
                    SELECT product_id, filename FROM images GROUP BY product_id
                ) AS images', $this->table.'.product_id = images.product_id', 'left');

         return $builder;
    } 
}
