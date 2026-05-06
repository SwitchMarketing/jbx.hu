<?php

namespace App\Controllers;

use App\Libraries\BuildPage;

use App\Models\CategoryTreeModel;
use App\Models\ProductMasterModel;
use App\Models\ProductVariantModel;

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
		$showCategoryCards = false;
		$directChildren = [];

		if (!$categoryId) {
			$showCategoryCards = true;
			$directChildren = \App\Helpers\CategoryHelper::getDirectChildren(0);
		} elseif (\App\Helpers\CategoryHelper::hasChildren($categoryId)) {
			$showCategoryCards = true;
			$directChildren = \App\Helpers\CategoryHelper::getDirectChildren($categoryId);
		}

		// termékek lekérése
		// a lapozóhoz szükséges paraméterek
		$itemsPerPage = 12;

		$page = $this->request->getGet('page') ?? 1;
		$limit = $this->request->getGet('limit') ?? 24;

		$start = ($page * $itemsPerPage) - $itemsPerPage;

		$db = \Config\Database::connect('shop');
		$activeVariantStates = [
			ProductVariantModel::STATE_INSTOCK,
			ProductVariantModel::STATE_BACKORDER,
			ProductVariantModel::STATE_INQUIRE,
		];
		$quotedStates = implode(', ', array_map(static fn ($state) => $db->escape($state), $activeVariantStates));

		$builder = $db->table('product_masters pm');
		$builder->select([
			'pm.id AS master_id',
			'pm.name AS name',
			'pm.slug AS master_slug',
			'representative_variant.id AS variant_id',
			'representative_variant.slug AS variant_slug',
			'representative_variant.sku AS sku',
			'representative_variant.price AS price',
			'representative_variant.state AS variant_state',
			'categories.path AS category_path',
			'(SELECT i.filename FROM images i WHERE i.master_id = pm.id ORDER BY i.position ASC, i.id ASC LIMIT 1) AS image',
		]);
		$builder->join('category_tree AS categories', 'categories.unas_id = pm.category_id', 'left');
		$builder->join(
			'product_variants AS representative_variant',
			"representative_variant.id = (
				SELECT pv.id
				FROM product_variants pv
				WHERE pv.master_id = pm.id
					AND pv.state IN ({$quotedStates})
				ORDER BY pv.position ASC, pv.price ASC, pv.id ASC
				LIMIT 1
			)",
			'inner',
			false
		);
		$builder->where('pm.state', ProductMasterModel::STATE_ACTIVE);

		if ($categoryId && !$showCategoryCards) {
			// a kategórának vannak al-kategóriái, így a kategória összes termékét lekérjük
			$descendantIds = \App\Helpers\CategoryHelper::getDescendantIds($categoryId);
			$builder->whereIn('pm.category_id', $descendantIds);
		}

		$total = 0;
		$items = [];

		if (!$showCategoryCards) {
			$countBuilder = clone $builder;
			$total = $countBuilder->countAllResults();

			$items = $builder
				->orderBy('pm.name', 'ASC')
				->limit($itemsPerPage, $start)
				->get()
				->getResult();
		}
		
		// a lapozó
		$pager = service('pager');

		$shop = (object) [
			'items' => $items,
			'total' => $total,
			'links' => $showCategoryCards ? '' : $pager->makeLinks($page, $limit, $total, 'shop')
        ];

		// kategória fa lekérése
		$treeModel = new CategoryTreeModel();
        $categories = $treeModel->getAllOrdered();

		// a fategóriaképzés
		$tree = $this->buildTree($categories, null);
		if (empty($tree)) {
			$tree = $this->buildTree($categories, 0);
		}

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
				'tree' => $this->renderTree($tree, $categoryId),
				'showCategoryCards' => $showCategoryCards,
				'categories' => $directChildren
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
		$master = null;
		$variant = null;
		$activeVariantStates = [
			ProductVariantModel::STATE_INSTOCK,
			ProductVariantModel::STATE_BACKORDER,
			ProductVariantModel::STATE_INQUIRE,
		];

		if (count($params) >= 2) {
			$variantSlug = array_pop($params);
			$masterSlug = array_pop($params);

			$masterModel = model(ProductMasterModel::class);
			$variantModel = model(ProductVariantModel::class);

			$master = $masterModel
				->where('slug', $masterSlug)
				->where('state', ProductMasterModel::STATE_ACTIVE)
				->first();

			if ($master) {
				$variant = $variantModel
					->where('master_id', $master->id)
					->where('slug', $variantSlug)
					->whereIn('state', $activeVariantStates)
					->first();

				if (!$variant) {
					$variant = $variantModel
						->where('master_id', $master->id)
						->whereIn('state', $activeVariantStates)
						->orderBy('position', 'ASC')
						->orderBy('price', 'ASC')
						->first();

					if (!$variant) {
						throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
					}

					$canonicalPath = ['termekek'];
					if (!empty($master->category_id)) {
						$trail = \App\Helpers\BreadcrumbsHelper::getCategoryTrail($master->category_id);
						if (!empty($trail)) {
							foreach ($trail as $cat) {
								$canonicalPath[] = $cat->slug;
							}
						}
					}
					$canonicalPath[] = $master->slug;
					$canonicalPath[] = $variant->slug;

					return redirect()->to(base_url(implode('/', $canonicalPath)), 301);
				}
			}
		}

		// Egy-szegmenses legacy URL-eket nem szolgálunk ki többé.
		if (!$master || !$variant) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$product = (object) [
			'id'          => (int) $variant->id,
			'variant_id'  => (int) $variant->id,
			'master_id'   => (int) $master->id,
			'name'        => $variant->name ?: $master->name,
			'sku'         => $variant->sku,
			'description' => $master->description ?? '',
			'price'       => (float) ($variant->price ?? 0),
			'stock'       => (float) ($variant->stock ?? 0),
			'state'       => $variant->state ?? '',
			'images'      => $this->getVariantImages((int) $master->id, (int) $variant->id, $variant->unas_id ?? null),
			'params'      => null,
		];
		$variant->attributes = $this->getVariantAttributes((int) $variant->id);

		$siblings = model(ProductVariantModel::class)->getWithAttributes($master->id);
		$siblings = array_values(array_filter($siblings, static function ($v) use ($activeVariantStates) {
			return in_array($v->state ?? null, $activeVariantStates, true);
		}));

		$options = [];
		$optionMatrix = [];
		if (!empty($siblings)) {
			$options = $this->buildOptionsFromVariants($siblings, (int) $variant->id);
			$optionMatrix = $this->buildOptionMatrix($siblings);
		}

		if (!empty($variant->attributes) && is_array($variant->attributes)) {
			$params = [];
			foreach ($variant->attributes as $attr) {
				$params[] = (object) [
					'Name' => $attr->name,
					'Value' => $attr->value,
				];
			}
			$product->params = json_encode($params);
		}

		// breadcrumbs
		$breadcrumbs = [
			(object) [
                'title' => 'Termékek',
                'url'   => base_url('termekek')
            ]
		];
		if ($master->category_id) {
			$trail = \App\Helpers\BreadcrumbsHelper::getCategoryTrail($master->category_id);
			if (!empty($trail)) {
				foreach ($trail as $cat) {
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
				'product'     => $product,
				'options'     => $options,
				'optionMatrix'=> $optionMatrix,
				'masterSlug'  => $master->slug,
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
		
		$masterSlug = $this->request->getPost('master_slug') ?? '';
		$clickedOptionId = (string) ($this->request->getPost('clicked_option_id') ?? '');
		$clickedOptionValue = $this->normalizeOptionValue((string) ($this->request->getPost('clicked_option_value') ?? ''));
		$options    = json_decode($this->request->getPost('params'), true) ?? [];
		
		if (!$masterSlug || !$options) {
			return $this->response->setStatusCode(400)->setJSON(['error' => 'Hiányzó paraméterek.']);
		}

		$masterModel = model(ProductMasterModel::class);
		$master = $masterModel
			->where('slug', $masterSlug)
			->where('state', ProductMasterModel::STATE_ACTIVE)
			->first();

		if (!$master) {
			return $this->response->setStatusCode(404)->setJSON(['error' => 'Mester termék nem található.']);
		}

		$db = \Config\Database::connect('shop');
		$matchCount = count($options);

		$conditions = [];
		foreach ($options as $o) {
			$conditions[] = '(vav.attribute_id = ' . $db->escape((int)($o['optionId'] ?? 0))
				. ' AND vav.value = ' . $db->escape($o['optionValue'] ?? '') . ')';
		}
		$condStr = implode(' OR ', $conditions);

		$sql = "SELECT pv.id, pv.slug AS variant_slug, pv.sku
				FROM product_variants pv
				WHERE pv.master_id = ?
				AND pv.state IN (?, ?, ?)
				AND (
					SELECT COUNT(*) FROM variant_attribute_values vav
					WHERE vav.variant_id = pv.id
					AND ({$condStr})
				) = ?
				LIMIT 1";

		$foundVariant = $db->query($sql, [
			$master->id,
			ProductVariantModel::STATE_INSTOCK,
			ProductVariantModel::STATE_BACKORDER,
			ProductVariantModel::STATE_INQUIRE,
			$matchCount,
		])->getRow();

		if (!$foundVariant && $clickedOptionId !== '' && $clickedOptionValue !== '') {
			$activeStates = [
				ProductVariantModel::STATE_INSTOCK,
				ProductVariantModel::STATE_BACKORDER,
				ProductVariantModel::STATE_INQUIRE,
			];

			$selectedOptions = [];
			foreach ($options as $o) {
				$optId = (string) ($o['optionId'] ?? '');
				if ($optId === '') {
					continue;
				}
				$selectedOptions[$optId] = $this->normalizeOptionValue((string) ($o['optionValue'] ?? ''));
			}

			$candidates = model(ProductVariantModel::class)->getWithAttributes((int) $master->id);
			$best = null;
			$bestScore = -1;

			foreach ($candidates as $candidate) {
				if (!in_array($candidate->state ?? null, $activeStates, true)) {
					continue;
				}

				$attrs = [];
				foreach (($candidate->attributes ?? []) as $attr) {
					$attrs[(string) $attr->attribute_id] = $this->normalizeOptionValue((string) $attr->value);
				}

				if (($attrs[$clickedOptionId] ?? null) !== $clickedOptionValue) {
					continue;
				}

				$score = 0;
				foreach ($selectedOptions as $optId => $optValue) {
					if (($attrs[$optId] ?? null) === $optValue) {
						$score++;
					}
				}

				if ($score > $bestScore) {
					$bestScore = $score;
					$best = $candidate;
				} elseif ($score === $bestScore && $best && (float)($candidate->price ?? 0) < (float)($best->price ?? 0)) {
					$best = $candidate;
				}
			}

			if ($best) {
				$foundVariant = (object) [
					'id' => (int) $best->id,
					'variant_slug' => (string) $best->slug,
				];
			}
		}

		$path = ['termekek'];
		$trail = \App\Helpers\BreadcrumbsHelper::getCategoryTrail($master->category_id);
		if (!empty($trail)) {
			foreach ($trail as $cat) {
				$path[] = $cat->slug;
			}
		}
		$path[] = $master->slug;

		if (!$foundVariant) {
			return $this->response->setStatusCode(422)->setJSON([
				'success' => false,
				'error' => 'A kiválasztott opciókombináció nem elérhető.'
			]);
		}

		$path[] = $foundVariant->variant_slug;

		return $this->response->setJSON([
			'success'   => true,
			'variantId' => $foundVariant->id ?? null,
			'url'       => base_url(implode('/', $path)),
		]);		
	}

	/**
	 * buildOptionsFromVariants
	 *
	 * Felépíti az opció-választó tömböt a product_variants + variant_attribute_values
	 * táblák alapján. Visszatér ugyanolyan struktúrával, mint a legacy getOptions().
	 *
	 * @param  array $variants  getWithAttributes() eredménye
	 * @param  int   $currentVariantId  az éppen megjelenített variáns ID-ja
	 * @return array
	 */
	private function buildOptionsFromVariants(array $variants, int $currentVariantId): array
	{
		// Az aktuális variáns attribútum-értékei (aktív jelöléshez)
		$currentAttrs = [];
		foreach ($variants as $v) {
			if ((int)$v->id === $currentVariantId) {
				foreach ($v->attributes as $attr) {
					$attrId = (string) $attr->attribute_id;
					$currentAttrs[$attrId] = $this->normalizeOptionValue((string) $attr->value);
				}
				break;
			}
		}

		$options = [];
		$seenValues = [];
		foreach ($variants as $v) {
			foreach ($v->attributes as $attr) {
				$attrId = (string) $attr->attribute_id;
				$rawValue = (string) $attr->value;
				$normalizedValue = $this->normalizeOptionValue($rawValue);
				if ($normalizedValue === '') {
					continue;
				}

				if (!isset($options[$attrId])) {
					$options[$attrId] = [
						'id'     => $attrId,
						'name'   => $attr->name,
						'position' => isset($attr->position) ? (int) $attr->position : 0,
						'values' => [],
					];
					$seenValues[$attrId] = [];
				}

				// Csak egyszer vegyük fel az adott értéket normalizált összehasonlítással
				if (!isset($seenValues[$attrId][$normalizedValue])) {
					$seenValues[$attrId][$normalizedValue] = true;
					$displayValue = trim(preg_replace('/\s+/u', ' ', $rawValue) ?? $rawValue);
					$options[$attrId]['values'][] = [
						'value'  => $displayValue,
						'slug'   => $v->slug,
						'active' => (
							isset($currentAttrs[$attrId]) &&
							$currentAttrs[$attrId] === $normalizedValue
						),
					];
				}
			}
		}

		// Stabil sorrend: attribútum pozíció, majd név
		uasort($options, static function(array $a, array $b): int {
			$positionA = (int) ($a['position'] ?? 0);
			$positionB = (int) ($b['position'] ?? 0);

			if ($positionA === $positionB) {
				return strnatcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
			}

			return $positionA <=> $positionB;
		});

		return $options;
	}

	/**
	 * buildOptionMatrix
	 *
	 * Olyan mátrixot épít, amiből frontend oldalon eldönthető,
	 * hogy egy opcióérték kompatibilis-e a jelenlegi kiválasztással.
	 *
	 * @param  array $variants
	 * @return array
	 */
	private function buildOptionMatrix(array $variants): array
	{
		$matrix = [];
		foreach ($variants as $variant) {
			$attrs = [];
			foreach (($variant->attributes ?? []) as $attr) {
				$attrId = (string) $attr->attribute_id;
				$attrs[$attrId] = $this->normalizeOptionValue((string) $attr->value);
			}

			$matrix[] = [
				'id' => (int) $variant->id,
				'attrs' => $attrs,
			];
		}

		return $matrix;
	}

	/**
	 * getVariantAttributes
	 *
	 * @param  int $variantId
	 * @return array
	 */
	private function getVariantAttributes(int $variantId): array
	{
		if ($variantId < 1) {
			return [];
		}

		$db = \Config\Database::connect('shop');
		return $db->query(
			"SELECT vav.attribute_id, a.name, a.position, vav.value
			FROM variant_attribute_values vav
			JOIN attributes a ON a.id = vav.attribute_id
			WHERE vav.variant_id = ?
			ORDER BY a.position ASC, a.name ASC, a.id ASC",
			[$variantId]
		)->getResult();
	}

	/**
	 * normalizeOptionValue
	 *
	 * @param  string $value
	 * @return string
	 */
	private function normalizeOptionValue(string $value): string
	{
		$value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
		if ($value === '') {
			return '';
		}

		if (function_exists('mb_strtolower')) {
			return mb_strtolower($value, 'UTF-8');
		}

		return strtolower($value);
	}

	/**
	 * getVariantImages
	 *
	 * @param  int $masterId
	 * @param  int|null $variantId
	 * @param  int|string|null $unasId
	 * @return array
	 */
	private function getVariantImages(int $masterId, ?int $variantId = null, $unasId = null): array
	{
		if ($masterId < 1 && empty($unasId)) {
			return [];
		}

		$db = \Config\Database::connect('shop');
		$builder = $db->table('images')
			->select('filename')
			->groupStart();

		if ($masterId > 0) {
			$builder->where('master_id', $masterId);
		}

		if (!empty($variantId)) {
			$builder->groupStart()
				->where('variant_id', null)
				->orWhere('variant_id', $variantId)
			->groupEnd();
		}

		if (!empty($unasId)) {
			$builder->orGroupStart()
				->where('master_id', null)
				->where('product_id', (string) $unasId)
			->groupEnd();
		}

		$builder->groupEnd();

		return $builder
			->orderBy('position', 'ASC')
			->orderBy('id', 'ASC')
			->get()
			->getResult();
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
			$isRootSearch = ($parentId === null || $parentId === 0 || $parentId === '0');
			$isElementRoot = ($element->parent_id === null || $element->parent_id === 0 || $element->parent_id === '0');

			if (($isRootSearch && $isElementRoot) || (!$isRootSearch && $element->parent_id == $parentId)) {
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
		* @return string
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
			$toggleHtml = isset($cat->children) ? "<span class='tree-toggle' title='alkategóriák'></span>" : '';
            $html .= "<li$cls>$toggleHtml<a href='/termekek/" . esc($cat->path) . "'>" . esc($cat->name) . "</a>";
            if (isset($cat->children)) {
                $html .= $this->renderTree($cat->children, $activeCategory);
            }
            $html .= "</li>";
        }

        $html .= "</ul>";

        return $html;
    }

	
}
