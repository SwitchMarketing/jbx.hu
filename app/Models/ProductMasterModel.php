<?php

namespace App\Models;

use App\Models\BaseModel;

class ProductMasterModel extends BaseModel
{
    public const STATE_ACTIVE   = 'active';
    public const STATE_INACTIVE = 'inactive';
    public const STATES         = [self::STATE_ACTIVE, self::STATE_INACTIVE];

    protected $table            = 'product_masters';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
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
    protected $deletedField  = 'deleted_at';

    protected $qstringColumns = [
        'product_masters.name',
        'product_masters.slug',
        'product_masters.description'
    ];

    public function initialize()
    {
        $this->extraFields = [
            'category_name' => 'categories.name',
            'category_slug' => 'categories.slug',
            'variant_count' => 'IFNULL(variants.c, 0)'
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
            ->join('category_tree as categories', $this->table.'.category_id = categories.unas_id', 'left')
            ->join('(SELECT master_id, COUNT(*) as c FROM product_variants GROUP BY master_id) AS variants', $this->table.'.id = variants.master_id', 'left');

        return $builder;
    }

    public function findWithCategory($id)
    {
        if (empty($this->displayFields)) {
            $this->_setDefaultFields();
        }
        $builder = $this->builder();
        $this->_setSelect($builder);
        $builder->where($this->table . '.id', $id);
        return $builder->get()->getRow();
    }

    /**
     * BaseModel hook: translate the virtual 'variant_state' filter into an
     * EXISTS subquery against product_variants.
     */
    protected function _applyCustomFilter($builder, $filter)
    {
        if (($filter->property ?? null) === 'variant_state' && !empty($filter->value)) {
            $builder->where(
                "EXISTS (SELECT 1 FROM product_variants pv WHERE pv.master_id = {$this->table}.id AND pv.state = " .
                $this->db->escape($filter->value) . ")",
                null,
                false
            );
            return true;
        }
        return false;
    }
}
