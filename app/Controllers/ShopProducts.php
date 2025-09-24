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

		// csak a parent típusú termékeket listázzuk alapértelmezetten
		// vagy ahol a types mező NULL
		$model->groupStart()
             ->where("json_extract(types, '$.Type') IS NULL")
             ->orWhere("json_extract(types, '$.Type') =", 'parent')
             ->groupEnd();   

		// a termékek lekérése
		if ($categoryId) {
			// a kategórának vannak al-kategóriái, így a kategória összes termékét lekérjük
			$descendantIds = \App\Helpers\CategoryHelper::getDescendantIds($categoryId);
			$model->whereIn('category_id', $descendantIds);	
		} 

		// a termékek rendezése név alapján
		$sorters = [
			(object) [
				'property' => 'name',
				'direction' => 'ASC'
			]
		];
		$model->setSorters($sorters);

		$result = $model->findAll($itemsPerPage, $start);		
		
		// a lapozó
		$pager = service('pager');

		$shop = (object) [
            'items' => $result->data,
            'total' => $result->total,
			'links' => $pager->makeLinks($page, $limit, $result->total, 'shop')
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

		// a termék típusa - szülő vagy gyermek
		// a types mező JSON formátumú, dekódoljuk
		$type = json_decode($product->types, true)['Type'] ?? null;

		// ha gyermek termék, akkor betöltjük a szülő terméket is
		// ha szülő termék, akkor az a termék maga
		$parent = null;
		if ($type === 'child') {

			$parentSku = json_decode($product->types, true)['Parent'];
			
			// Betöltjük a parent terméket
			$parent = $model
				->where('sku', $parentSku)
				->where("json_extract(types, '$.Type') =", 'parent')
				->first();

		} else {
			$parent = $product;
		}		
		
		// termék variációk
		$options = $model->getOptions($parent->sku);

		// a termék kategóriája
		$category = (new \App\Models\CategoryModel())->find($product->category_id);

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

		if($product->params) {
		
			$product_params = json_decode($product->params, true) ?? [];
		//// --- A jelölés logikája --
			foreach ($product_params as $param) {

				if(!is_array($param)) {
					continue;
				}

				$id    = $param['Id'];
				$value = trim($param['Value']);

				if (isset($options[$id])) {
					foreach ($options[$id]['values'] as &$optValue) {
						if (trim($optValue['value']) === $value) {
							$optValue['active'] = true;
						}
					}
					unset($optValue); // mindig bontsuk a referenciát!
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
				'product' => $product,
				'options' => $options				
            ]
        ];

		/*
		echo '<pre>';
		print_r($data);
		echo '</pre>';
		*/
		
		BuildPage::render('shop-product', $data);

    }
	
		
	/**
	 * productVariation
	 *
	 * @return void
	 */
	public function productVariation()
	{	

		if(!$this->request->isAJAX()) {
			return $this->response->setStatusCode(400)->setJSON(['error' => 'Érvénytelen kérés.']);
		}
		
		$sku = $this->request->getPost('sku');
		$options = json_decode($this->request->getPost('params'), true) ?? []; // tömb
		$slug = $this->request->getPost('slug');
		
		if (!$sku || !$options) {
			return $this->response->setStatusCode(400)->setJSON(['error' => 'Hiányzó paraméterek.']);
		}

		// a termék az sku alapján, kell a kategória azonosításhoz
		$model = model(ProductModel::class);
		$product = $model->where('sku', $sku)->first();
		$category_id = $product->category_id ?? null;

		// a kategória
		if (!$category_id) {
			return $this->response->setStatusCode(404)->setJSON(['error' => 'A termék kategóriája nem található.']);
		}
		$category = (new \App\Models\CategoryModel())->find($category_id);

		// az összes termék lekérése a kategóriából
		$products = $model->where('category_id', $category_id)->findAll(0);
		
		// a megfelelő termék keresése a paraméterek alapján
		// minden paraméternek egyeznie kell
		$result = null;
		foreach ($products->data as $prod) {
			
			if ($prod->sku === $sku) {
				continue; // a kiinduló terméket kihagyjuk
			}
			if (!$prod->params) {
				continue; // ha nincs paraméter, akkor kihagyjuk
			}
			$prodParams = json_decode($prod->params, true);
			$match = true;
			foreach ($options as $key => $value) {
				
				$found = false;

				foreach ($prodParams as $param) {			
					if(!is_array($param)) {
						continue;
					}
					if ($param['Id'] == $value['optionId'] && trim($param['Value']) === trim($value['optionValue'])) {
						$found = true;
						break;
					}
				}
				if (!$found) {
					$match = false;
					break;
				}
				
			}
			if ($match) {
				$result = $prod;
				break;
			}	
			
		}

		// ha van kategória ID, akkor a kategória trail lekérése
		$path = ['termekek'];
		$trail = \App\Helpers\BreadcrumbsHelper::getCategoryTrail($product->category_id);
		if(!empty($trail)) {
			foreach($trail as $cat) {
				$path[] = $cat->slug;
			}
		}
		$path[] = ($result->slug ?? $slug);
		
		$response = [
			'success' => true,
			'url' => base_url(implode('/', $path))
		];

		return $this->response->setJSON($response);		
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
