<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $DBGroup          = 'shop';
    protected $table            = 'settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'setting_key',
        'setting_value',
        'data_type',
        'description',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getValue(string $key, $default = null)
    {
        $row = $this->where('setting_key', $key)->first();
        if (!$row) {
            return $default;
        }

        return $this->castValue($row->setting_value, $row->data_type);
    }

    protected function castValue($value, string $dataType)
    {
        if ($value === null) {
            return null;
        }

        switch (strtolower($dataType)) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'float':
            case 'double':
                return (float) $value;
            case 'bool':
            case 'boolean':
                return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
            case 'json':
                return json_decode((string) $value, true);
            case 'string':
            default:
                return (string) $value;
        }
    }
}
