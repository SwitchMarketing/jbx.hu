<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductVariantModel extends Model
{
    public const STATE_INSTOCK   = 'instock';
    public const STATE_BACKORDER = 'backorder';
    public const STATE_INQUIRE   = 'inquire';
    public const STATE_INACTIVE  = 'inactive';
    public const STATES          = [
        self::STATE_INSTOCK,
        self::STATE_BACKORDER,
        self::STATE_INQUIRE,
        self::STATE_INACTIVE,
    ];

    protected $DBGroup          = 'shop';
    protected $table            = 'product_variants';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'master_id',
        'unas_id',
        'sku',
        'name',
        'slug',
        'price',
        'stock',
        'position',
        'state'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getWithAttributes($masterId)
    {
        $db = \Config\Database::connect('shop');
        $variants = $this->where('master_id', $masterId)
            ->orderBy('position', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        foreach ($variants as &$v) {
            $sql = "SELECT vav.attribute_id, a.name, a.position, vav.value
                    FROM variant_attribute_values vav
                    JOIN attributes a ON vav.attribute_id = a.id
                WHERE vav.variant_id = ?
                ORDER BY a.position ASC, a.name ASC, a.id ASC";
            $v->attributes = $db->query($sql, [$v->id])->getResult();
        }
        unset($v);

        return $variants;
    }
}
