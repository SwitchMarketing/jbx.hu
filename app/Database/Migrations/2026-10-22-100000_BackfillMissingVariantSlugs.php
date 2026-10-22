<?php

namespace App\Database\Migrations;

use App\Models\ProductVariantModel;
use CodeIgniter\Database\Migration;

/**
 * Az adminban kézzel létrehozott variánsok slug nélkül kerültek be (NULL),
 * így a termékoldal URL-je nem állt össze. Pótoljuk a hiányzó slugokat.
 */
class BackfillMissingVariantSlugs extends Migration
{
    public function up()
    {
        $model = new ProductVariantModel();

        $rows = $this->db->table('product_variants')
            ->select('id, master_id, name, sku')
            ->groupStart()
                ->where('slug', null)
                ->orWhere('slug', '')
            ->groupEnd()
            ->orderBy('master_id', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();

        foreach ($rows as $row) {
            $slug = $model->generateUniqueSlug(
                (int) $row->master_id,
                (string) ($row->name ?? ''),
                (string) ($row->sku ?? ''),
                (int) $row->id
            );

            $this->db->table('product_variants')
                ->where('id', $row->id)
                ->update(['slug' => $slug]);
        }
    }

    public function down()
    {
        // adatjavítás, nem visszavonható
    }
}
