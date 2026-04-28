<?php

namespace App\Controllers;

use App\Helpers\CategoryHelper;
use App\Libraries\BuildPage;
use App\Models\ProductVariantModel;

class ShopCategories extends BaseController
{
    /**
     * Manual override map for top-level category images.
     * Keys are category slugs, values are filenames from /public/imgs/products.
     */
    private const CATEGORY_IMAGE_MAP = [
        'gepbiztonsagi-kerites' => 'W340-220120.jpg',
        'kabeltalca-rendszer' => '1112.jpg',
        'utkozesvedelem' => 'AG-CP4-250IN.jpg',
        'raktarbiztonsag' => '520-150220.jpg'
    ];

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
            ->orderBy('order', 'ASC')
            ->get()
            ->getResult();

        $activeVariantStates = [
            ProductVariantModel::STATE_INSTOCK,
            ProductVariantModel::STATE_BACKORDER,
            ProductVariantModel::STATE_INQUIRE,
        ];

        // Load first product image for each category
        foreach ($mainCategories as $cat) {
            $mappedImage = self::CATEGORY_IMAGE_MAP[$cat->slug] ?? null;
            if ($mappedImage !== null && $mappedImage !== '') {
                $cat->image = $mappedImage;
                continue;
            }

            $descendantIds = CategoryHelper::getDescendantIds((int) $cat->unas_id);

            // Pick the first available image from an active product variant in this category
            $image = $db->table('product_masters')
                ->select('images.filename')
                ->join('product_variants', 'product_variants.master_id = product_masters.id')
                ->join('images', 'images.product_id = product_variants.unas_id', 'inner')
                ->whereIn('product_masters.category_id', $descendantIds)
                ->where('product_masters.state', 'active')
                ->whereIn('product_variants.state', $activeVariantStates)
                ->orderBy('product_masters.id', 'ASC')
                ->orderBy('images.id', 'ASC')
                ->get()
                ->getFirstRow();

            $cat->image = $image ? $image->filename : null;
        }

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
