<?php

namespace App\Controllers;

use App\Libraries\BuildPage;

use App\Models\CategoryTreeModel;
use App\Models\ProductModel;

class ShopProducts extends BaseController
{
    /**
	 * index
	 * 
	 * termék lista
	 *
	 * @return void
	 */
	public function index($categoryId = null)
    {

		// termékek lekérése
		// a lapozóhoz szükséges paraméterek
		$itemsPerPage = 12;

		$page = $this->request->getGet('page') ?? 1;
		$limit = $this->request->getGet('limit') ?? 24;

		$start = ($page * $itemsPerPage) - $itemsPerPage;

		// a termékek lekérése
		// a termékek modelje
		$model = model(ProductModel::class);

		// a termékek lekérése
		if ($categoryId) {

			// a kategórának vannak al-kategóriái, így a kategória összes termékét lekérjük
			$descendantIds = \App\Helpers\CategoryHelper::getDescendantIds($categoryId);

			// a kategória termékeinek lekérése
			$items = $model->whereIn('category_id', $descendantIds)->findAll($limit, $start);
			// a kategória termékeinek számának lekérése
			$total = $model->whereIn('category_id', $descendantIds)->countAllResults(false);
			
		} else {
			// ha nincs kategória ID, akkor az összes terméket lekérjük
			$items = $model->findAll($limit, $start);
			// az összes termék lekérése
			$total = $model->countAllResults(false);
		}

		// a lapozó
		$pager = service('pager');

		$shop = (object) [
            'items' => $items->data,
            'total' => $items->total,
			'links' => $pager->makeLinks($page, $limit, $total, 'shop')
        ];

		// kategória fa lekérése
		$treeModel = new CategoryTreeModel();
        $categories = $treeModel->getAllOrdered();

		// a fategóriaképzés
        $tree = $this->buildTree($categories);

		// breadcrumbs
		$breadcrumbs = [
			(object) [
                'title' => 'Termékek',
                'url'   => base_url('termekek')
            ]
		];
		if ($categoryId) {
			// ha van kategória ID, akkor a kategória trail lekérése
			$trail = \App\Helpers\BreadcrumbsHelper::getCategoryTrail($categoryId);
			if(!empty($trail)) {
				// a breadcrumbs tömbbe hozzáadjuk a kategória neveket és URL-eket
				foreach($trail as $cat) {
					$breadcrumbs[] = (object) [
						'title' => $cat->name,
						'url'   => base_url('termekek/' . $cat->path)
					];
				}
			}
		}
        
		$data = [
			'header' => [
				'title'	  => page_title('Termékek'),		
				'section' => 'shop'		
			],
			'body'	=> [
                'breadcrumbs' => $breadcrumbs,
				'shop' => $shop,
				'tree' => $this->renderTree($tree, $categoryId)
            ]
        ];

		BuildPage::render('shop-products-grid', $data);

    }

	/**
	 * product
	 * 
	 * termék aloldal
	 *
	 * @return void
	 */
	public function product(...$params)
    {

		$slug = end($params);

		// termék lekérése slug alapján
		$model = model(ProductModel::class);
		$product = $model->where('slug', $slug)->first();

		// ha nincs termék, akkor 404-es hiba
		if (!$product) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		// breadcrumbs
		$breadcrumbs = [
			(object) [
                'title' => 'Termékek',
                'url'   => base_url('termekek')
            ]
		];
		if ($product->category_id) {
			// ha van kategória ID, akkor a kategória trail lekérése
			$trail = \App\Helpers\BreadcrumbsHelper::getCategoryTrail($product->category_id);
			if(!empty($trail)) {
				// a breadcrumbs tömbbe hozzáadjuk a kategória neveket és URL-eket
				foreach($trail as $cat) {
					$breadcrumbs[] = (object) [
						'title' => $cat->name,
						'url'   => base_url('termekek/' . $cat->path)
					];
				}
			}
		}

		$data = [
			'header' => [
				'title'	  => page_title($product->name),		
				'section' => 'shop'		
			],
			'body'	=> [

                'breadcrumbs' => $breadcrumbs,

				'product' => $product

            ]
        ];

		BuildPage::render('shop-product', $data);

    }
	
	/**
	 * buildTree
	 *
	 * @param  mixed $elements
	 * @param  mixed $parentId
	 * @return void
	 */
	private function buildTree($elements, $parentId = null)
    {
        $branch = [];

        foreach ($elements as $element) {
            if ($element->parent_id == $parentId) {
                $children = $this->buildTree($elements, $element->unas_id);
                if ($children) {
                    $element->children = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }
    
    /**
     * renderTree
     *
     * @param  mixed $categories
     * @return void
     */
    private function renderTree($categories, $activeCategory = null)
    {
		
		// kezdő HTML lista
        $html = "<ul>";

        foreach ($categories as $cat) {
			
			$cls = '';
			
			if (isset($cat->children)) {
				$cls .= ' has-children collapsed';
			}
			
			if ($activeCategory && $cat->unas_id == $activeCategory) {
				$cls .= ' active';
			}

			if ($cls) {
				$cls = " class='" . esc($cls) . "'";
			}

			// kategória link
            $html .= "<li$cls><a href='/termekek/" . esc($cat->path) . "'>" . esc($cat->name) . "</a>";
            if (isset($cat->children)) {
                $html .= $this->renderTree($cat->children, $activeCategory);
            }
            $html .= "</li>";
        }

        $html .= "</ul>";

        return $html;
    }

}
