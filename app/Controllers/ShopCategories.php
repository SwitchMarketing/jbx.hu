<?php

namespace App\Controllers;

use App\Libraries\BuildPage;

class ShopCategories extends BaseController
{
    /**
     * List main product categories
     */
    public function index()
    {
        // Get main categories (parent_id = NULL or 0)
        $db = \Config\Database::connect('shop');
        $mainCategories = $db->table('categories')
            ->groupStart()
                ->where('parent_id IS NULL')
                ->orWhere('parent_id', 0)
            ->groupEnd()
            ->where('deleted_at IS NULL')
            ->orderBy('order', 'ASC')
            ->get()
            ->getResult();

        $args = [
            'header'       => [
                'title'    => 'Termék Kategóriák - JBX Trade',
                'desc'     => 'Fedezze fel termék kategóriáinkat: gépbiztonsági kerítések, kábeltálcák, ütközésvédelem, raktárbiztonsági és ingatlan megoldások.',
                'og_title' => 'Termék Kategóriák',
                'og_desc'  => 'Ipari gépbiztonsági megoldások és termékek a JBX Trade kínálatában.',
                'og_url'   => base_url('termekek/kategoriak'),
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
