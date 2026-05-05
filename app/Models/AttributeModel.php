<?php

namespace App\Models;

use CodeIgniter\Model;

class AttributeModel extends Model
{
    protected $DBGroup          = 'shop';
    protected $table            = 'attributes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'position'];

    // Dates
    protected $useTimestamps = false;
}
