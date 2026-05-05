<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductMasterDefaultAttributeModel extends Model
{
    protected $DBGroup          = 'shop';
    protected $table            = 'product_master_default_attributes';
    protected $primaryKey       = 'master_id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'object';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'master_id',
        'attribute_id',
        'default_value',
    ];

    protected $useTimestamps = false;
}
