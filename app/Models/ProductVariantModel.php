<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductVariantModel extends Model
{
    protected $table            = 'product_variants';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'master_id',
        'unas_id',
        'sku',
        'price',
        'stock',
        'state'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getWithAttributes($masterId)
    {
        $db = \Config\Database::connect();
        $variants = $this->where('master_id', $masterId)->findAll();

        foreach ($variants as &$v) {
            $sql = "SELECT a.name, vav.value 
                    FROM variant_attribute_values vav
                    JOIN attributes a ON vav.attribute_id = a.id
                    WHERE vav.variant_id = ?";
            $v->attributes = $db->query($sql, [$v->id])->getResult();
        }

        return $variants;
    }
}
