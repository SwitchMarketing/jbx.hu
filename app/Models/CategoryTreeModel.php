<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryTreeModel extends Model
{
    protected $DBGroup          = 'shop';
    protected $table            = 'category_tree';
    protected $primaryKey       = 'unas_id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'unas_id',
        'name',
        'slug',
        'parent_id',
        'order',
        'depth',
        'path'
    ];

    // Dates
    protected $useTimestamps = false;
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
     * updateTree
     *
     * @return mixed
     */
    public function updateTree()
    {

        // Clear the existing tree
        $this->db->table($this->table)->truncate();
        
        $sql = "WITH RECURSIVE category_tree_cte(unas_id, name, parent_id, [order], slug, depth, path) AS (
                            SELECT 
                                unas_id,
                                name,
                                parent_id,
                                [order],
                                slug,
                                0 AS depth,
                                slug AS path
                            FROM categories
                            WHERE parent_id = 0

                            UNION ALL

                            SELECT 
                                c.unas_id,
                                c.name,
                                c.parent_id,
                                c.[order],
                                c.slug,
                                ct.depth + 1,
                                ct.path || '/' || c.slug AS path
                            FROM categories c
                            JOIN category_tree_cte ct ON c.parent_id = ct.unas_id
                        )
                        INSERT INTO category_tree (unas_id, name, slug, parent_id, [order], depth, path)
                        SELECT unas_id, name, slug, parent_id, [order], depth, path
                        FROM category_tree_cte;";

        return $this->db->query($sql);

    }
}
