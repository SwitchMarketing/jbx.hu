<?php

namespace App\Models;

use App\Models\BaseModel;

class ProductMasterModel extends BaseModel
{
    protected $table            = 'product_masters';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'category_id',
        'name',
        'slug',
        'unit',
        'description',
        'state'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function initialize()
    {
        $this->extraFields = [
            'category_name' => 'categories.name',
            'category_slug' => 'categories.slug',
        ];
        $this->_setDefaultFields();
    }

    protected function _setSelect($builder): \CodeIgniter\Database\BaseBuilder
    {
        $columns = $this->displayFields ?? $this->table.'.*';

        $columns = implode(',', array_map(
            function ($v, $k) {
                return $v.' AS '.$k;
            },
            $columns,
            array_keys($columns)
        ));

        $builder->select($columns, false)
            ->join('category_tree as categories', $this->table.'.category_id = categories.unas_id', 'left');

        return $builder;
    }
}
