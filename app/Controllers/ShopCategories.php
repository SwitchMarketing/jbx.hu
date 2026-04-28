<?php

namespace App\Controllers;

use App\Libraries\BuildPage;
use App\Models\CategoryModel;
use App\Models\ProductVariantModel;
use App\Models\ImageModel;

class ShopCategories extends BaseController
{
    /**
     * List main product categories
     */
    public function index()
    {
        $categoryModel = new CategoryModel();
        
        // Get main categories (parent_id = NULL or 0)
        $db = \Config\Database::connect('shop');
        $mainCategories = $db->table('categories')
            ->groupStart()
                ->where('parent_id IS NULL')
                ->orWhere('parent_id', 0)
            ->groupEnd()
            ->orderBy('order', 'ASC')
            ->get()
            ->getResult();

        // Load first product image for each category
        foreach ($mainCategories as $cat) {
            // Get first product from this category (that is active)
            $firstProduct = $db->table('product_masters')
                ->select('product_masters.id, product_variants.unas_id')
                ->join('product_variants', 'product_variants.master_id = product_masters.id')
                ->where('product_masters.category_id', $cat->unas_id)
                ->where('product_masters.state', 'active')
                ->orderBy('product_masters.id', 'ASC')
                ->get()
                ->getFirstRow();
            
            if ($firstProduct && $firstProduct->unas_id) {
                // Get first image of that product
                $image = $db->table('images')
                    ->select('filename')
                    ->where('product_id', $firstProduct->unas_id)
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getFirstRow();
                
                $cat->image = $image ? $image->filename : null;
            } else {
                $cat->image = null;
            }
        }

        $args = [
            'header'       => [
                'title'    => 'Termék Kategóriák - JBX Trade',
                'desc'     => 'Fedezze fel termék kategóriáinkat: gépbiztonsági kerítések, kábeltálcák, ütközésvédelem, raktárbiztonsági és ingatlan megoldások.',
                'og_title' => 'Termék Kategóriák',
                'og_desc'  => 'Ipari gépbiztonsági megoldások és termékek a JBX Trade kínálatában.',
                'og_url'   => base_url('kategoriak'),
                'og_img'   => base_url('imgs/jbx-og.jpg'),
                'section'  => 'shop categories',
            ],
            'body'         => [
                'categories' => $mainCategories,
                'title'      => 'Termék Kategóriák',
                'caption'    => 'Válassza ki az Önnek szükséges kategóriát',
            ]
        ];

        return BuildPage::render('shop/categories', $args);
    }
}
