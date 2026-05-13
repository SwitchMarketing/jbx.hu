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
		$searchTerm = trim((string) ($this->request->getGet('q') ?? ''));
		$sort = $this->normalizeProductSort((string) ($this->request->getGet('sort') ?? 'name_asc'));
		$hasActiveSearch = $searchTerm !== '';

		$db = \Config\Database::connect('shop');
		$activeVariantStates = [
			ProductVariantModel::STATE_INSTOCK,
			ProductVariantModel::STATE_BACKORDER,
			ProductVariantModel::STATE_INQUIRE,
		];

		$showCategoryCards = false;
		$directChildren = [];

		$treeModel = new CategoryTreeModel();
        $allCategories = $treeModel->getAllOrdered();
		$visibleCategoryIds = $this->resolveVisibleCategoryIds($allCategories, $activeVariantStates);
		$categories = $this->filterVisibleCategories($allCategories, $visibleCategoryIds);

		if (!$categoryId && !$hasActiveSearch) {
			$showCategoryCards = true;
			$directChildren = $this->getDirectChildren($categories, 0);
		} elseif (!$hasActiveSearch && $this->hasChildren($categories, $categoryId)) {
			$showCategoryCards = true;
			$directChildren = $this->getDirectChildren($categories, $categoryId);
		}

		// termékek lekérése
		// a lapozóhoz szükséges paraméterek
		$itemsPerPage = 12;

		$page = max(1, (int) ($this->request->getGet('page') ?? 1));
		$limit = $itemsPerPage;

		$start = ($page * $itemsPerPage) - $itemsPerPage;

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
			'representative_variant.discount_price AS discount_price',
			'representative_variant.state AS variant_state',
			'categories.path AS category_path',
			'(SELECT i.filename FROM images i WHERE i.master_id = pm.id ORDER BY i.position ASC, i.id ASC LIMIT 1) AS image',
			'(SELECT COUNT(*) FROM product_variants pv2 WHERE pv2.master_id = pm.id AND pv2.discount_price IS NOT NULL AND pv2.discount_price > 0 AND pv2.discount_price < pv2.price) AS has_discount_variant',
		]);
		$builder->join('category_tree AS categories', 'categories.unas_id = pm.category_id', 'left');
		$builder->join(
			'product_variants AS representative_variant',
			"representative_variant.id = (
				SELECT pv.id
				FROM product_variants pv
				WHERE pv.master_id = pm.id
					AND pv.state IN ({$quotedStates})
				ORDER BY
					CASE WHEN pv.discount_price IS NOT NULL AND pv.discount_price > 0 AND pv.discount_price < pv.price THEN 0 ELSE 1 END ASC,
					CASE WHEN pv.price > 0 THEN 0 ELSE 1 END ASC,
					CASE
						WHEN pv.discount_price IS NOT NULL AND pv.discount_price > 0 AND pv.discount_price < pv.price
						THEN pv.discount_price
						ELSE pv.price
					END ASC,
					pv.id ASC
				LIMIT 1
			)",
			'inner',
			false
		);
		$builder->where('pm.state', ProductMasterModel::STATE_ACTIVE);

		if ($categoryId && !$showCategoryCards && !$hasActiveSearch) {
			// a kategórának vannak al-kategóriái, így a kategória összes termékét lekérjük
			$descendantIds = \App\Helpers\CategoryHelper::getDescendantIds($categoryId);
			$builder->whereIn('pm.category_id', $descendantIds);
		}

		if ($searchTerm !== '') {
			// split search term into individual words and match ALL words
			$searchWords = array_filter(preg_split('/\s+/', $searchTerm), static fn ($word) => $word !== '');
			foreach ($searchWords as $word) {
				$builder->groupStart()
					->like('pm.name', $word)
					->orLike('pm.slug', $word)
					->orLike('pm.description', $word)
					->orLike('representative_variant.sku', $word)
				->groupEnd();
			}
		}

		$total = 0;
		$items = [];

		if (!$showCategoryCards) {
			$countBuilder = clone $builder;
			$total = $countBuilder->countAllResults();

			$this->applyProductListingSort($builder, $sort);

			$items = $builder
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
				'categories' => $directChildren,
				'searchTerm' => $searchTerm,
				'sort' => $sort
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
						->orderBy('CASE WHEN discount_price IS NOT NULL AND discount_price > 0 AND discount_price < price THEN discount_price ELSE price END', 'ASC', false)
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
			'discount_price' => isset($variant->discount_price) ? (float) $variant->discount_price : null,
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

		$initialVariantPayload = $this->buildVariantPayload($master, $variant, $product, current_url());

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
				'og_img'   => product_cover_image($product),
				'og_url'   => current_url(),
				'section' => 'shop'		
			],
			'body'	=> [
                'breadcrumbs' => $breadcrumbs,
				'product'     => $product,
				'options'     => $options,
				'optionMatrix'=> $optionMatrix,
				'masterSlug'  => $master->slug,
				'initialVariantPayload' => $initialVariantPayload,
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
	 * applyProductListingSort
	 */
	private function applyProductListingSort($builder, string $sort): void
	{
		$effectivePriceExpr = 'CASE WHEN representative_variant.discount_price IS NOT NULL AND representative_variant.discount_price > 0 AND representative_variant.discount_price < representative_variant.price THEN representative_variant.discount_price ELSE representative_variant.price END';

		switch ($sort) {
			case 'name_desc':
				$builder->orderBy('pm.name', 'DESC');
				break;
			case 'price_asc':
				$builder->orderBy($effectivePriceExpr, 'ASC', false)
					->orderBy('pm.name', 'ASC');
				break;
			case 'price_desc':
				$builder->orderBy($effectivePriceExpr, 'DESC', false)
					->orderBy('pm.name', 'ASC');
				break;
			case 'name_asc':
			default:
				$builder->orderBy('pm.name', 'ASC');
				break;
		}
	}

	/**
	 * normalizeProductSort
	 */
	private function normalizeProductSort(string $sort): string
	{
		$allowed = ['name_asc', 'name_desc', 'price_asc', 'price_desc'];

		if (!in_array($sort, $allowed, true)) {
			return 'name_asc';
		}

		return $sort;
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
				} elseif (
					$score === $bestScore
					&& $best
					&& shop_effective_net_price_eur($candidate->price ?? 0, $candidate->discount_price ?? null)
					< shop_effective_net_price_eur($best->price ?? 0, $best->discount_price ?? null)
				) {
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
		$variantModel = model(ProductVariantModel::class);
		$variant = $variantModel
			->where('master_id', $master->id)
			->where('id', (int) ($foundVariant->id ?? 0))
			->whereIn('state', [
				ProductVariantModel::STATE_INSTOCK,
				ProductVariantModel::STATE_BACKORDER,
				ProductVariantModel::STATE_INQUIRE,
			])
			->first();

		if (!$variant) {
			return $this->response->setStatusCode(404)->setJSON([
				'success' => false,
				'error' => 'A kiválasztott variáns nem található.'
			]);
		}

		$product = (object) [
			'id'          => (int) $variant->id,
			'variant_id'  => (int) $variant->id,
			'master_id'   => (int) $master->id,
			'name'        => $variant->name ?: $master->name,
			'sku'         => $variant->sku,
			'description' => $master->description ?? '',
			'price'       => (float) ($variant->price ?? 0),
			'discount_price' => isset($variant->discount_price) ? (float) $variant->discount_price : null,
			'stock'       => (float) ($variant->stock ?? 0),
			'state'       => $variant->state ?? '',
			'images'      => $this->getVariantImages((int) $master->id, (int) $variant->id, $variant->unas_id ?? null),
			'params'      => null,
		];

		$variantAttributes = $this->getVariantAttributes((int) $variant->id);
		if (!empty($variantAttributes)) {
			$params = [];
			foreach ($variantAttributes as $attr) {
				$params[] = (object) [
					'Name' => $attr->name,
					'Value' => $attr->value,
				];
			}
			$product->params = json_encode($params);
		}

		$variantUrl = base_url(implode('/', $path));
		$payload = $this->buildVariantPayload($master, $variant, $product, $variantUrl);

		return $this->response->setJSON([
			'success'   => true,
			'variantId' => $foundVariant->id ?? null,
			'url'       => $variantUrl,
			'payload'   => $payload,
		]);		
	}

	/**
	 * buildVariantPayload
	 *
	 * @param  object $master
	 * @param  object $variant
	 * @param  object $product
	 * @param  string $url
	 * @return array
	 */
	private function buildVariantPayload(object $master, object $variant, object $product, string $url): array
	{
		$selectedOptions = [];
		$attrs = $this->getVariantAttributes((int) $variant->id);
		foreach ($attrs as $attr) {
			$selectedOptions[(string) $attr->attribute_id] = $this->normalizeOptionValue((string) ($attr->value ?? ''));
		}

		return [
			'name' => (string) ($product->name ?? ''),
			'sku' => (string) ($product->sku ?? ''),
			'state' => (string) ($product->state ?? ''),
			'title' => page_title((string) ($product->name ?? 'Termék')),
			'url' => $url,
			'priceHtml' => product_price($product),
			'addToCartHtml' => add_to_cart_button($product),
			'coverImageUrl' => product_cover_image($product),
			'galleryHtml' => $this->renderGalleryItemsHtml($product->images ?? [], (string) ($product->name ?? '')),
			'featureRowsHtml' => $this->renderFeatureRowsHtml($product),
			'selectedOptions' => $selectedOptions,
			'masterSlug' => (string) ($master->slug ?? ''),
			'variantSlug' => (string) ($variant->slug ?? ''),
		];
	}

	/**
	 * renderGalleryItemsHtml
	 *
	 * @param  array  $images
	 * @param  string $productName
	 * @return string
	 */
	private function renderGalleryItemsHtml(array $images, string $productName): string
	{
		if (count($images) < 2) {
			return '';
		}

		$html = '';
		$index = 0;
		foreach ($images as $image) {
			$filename = $image->filename ?? null;
			if (!$filename) {
				continue;
			}

			$activeClass = $index === 0 ? ' nav-active' : '';
			$html .= '<li class="li-pd-imgs' . $activeClass . '">';
			$html .= '<a href="JavaScript:void(0)">';
			$html .= '<img src="' . esc(product_image($filename), 'attr') . '" alt="' . esc($productName, 'attr') . '" class="img-fluid">';
			$html .= '</a>';
			$html .= '</li>';
			$index++;
		}

		return $html;
	}

	/**
	 * renderFeatureRowsHtml
	 *
	 * @param  object $product
	 * @return string
	 */
	private function renderFeatureRowsHtml(object $product): string
	{
		$rows = '';

		if (!empty($product->sku)) {
			$rows .= '<tr><td>SKU</td><td>' . esc((string) $product->sku) . '</td></tr>';
		}

		$params = !empty($product->params) ? json_decode((string) $product->params) : null;
		if (is_object($params)) {
			$rows .= '<tr><td>' . esc((string) ($params->Name ?? '')) . '</td><td>' . esc((string) ($params->Value ?? '')) . '</td></tr>';
		} elseif (is_array($params)) {
			foreach ($params as $param) {
				$rows .= '<tr><td>' . esc((string) ($param->Name ?? '')) . '</td><td>' . esc((string) ($param->Value ?? '')) . '</td></tr>';
			}
		}

		return $rows;
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

	private function resolveVisibleCategoryIds(array $categories, array $activeVariantStates): array
	{
		if (empty($categories)) {
			return [];
		}

		$db = \Config\Database::connect('shop');
		$quotedStates = implode(', ', array_map(static fn ($state) => $db->escape($state), $activeVariantStates));
		$categoryIds = $db->table('product_masters')
			->select('category_id')
			->distinct()
			->where('state', ProductMasterModel::STATE_ACTIVE)
			->whereIn('category_id', array_map(static fn ($cat) => (int) $cat->unas_id, $categories))
			->where("EXISTS (SELECT 1 FROM product_variants pv WHERE pv.master_id = product_masters.id AND pv.state IN ({$quotedStates}))", null, false)
			->get()
			->getResultArray();

		if (empty($categoryIds)) {
			return [];
		}

		$parentById = [];
		foreach ($categories as $category) {
			$parentById[(int) $category->unas_id] = (int) ($category->parent_id ?? 0);
		}

		$visible = [];
		foreach ($categoryIds as $row) {
			$current = (int) ($row['category_id'] ?? 0);
			while ($current > 0 && !isset($visible[$current])) {
				$visible[$current] = true;
				$current = $parentById[$current] ?? 0;
			}
		}

		return $visible;
	}

	private function filterVisibleCategories(array $categories, array $visibleCategoryIds): array
	{
		if (empty($visibleCategoryIds)) {
			return [];
		}

		return array_values(array_filter($categories, static function ($category) use ($visibleCategoryIds) {
			return isset($visibleCategoryIds[(int) $category->unas_id]);
		}));
	}

	private function hasChildren(array $categories, $parentId): bool
	{
		$parentId = (int) $parentId;
		foreach ($categories as $category) {
			if ((int) ($category->parent_id ?? 0) === $parentId) {
				return true;
			}
		}

		return false;
	}

	private function getDirectChildren(array $categories, $parentId): array
	{
		$parentId = (int) $parentId;
		$children = array_values(array_filter($categories, static function ($category) use ($parentId) {
			return (int) ($category->parent_id ?? 0) === $parentId;
		}));

		usort($children, static function ($a, $b) {
			$orderA = (int) ($a->order ?? 0);
			$orderB = (int) ($b->order ?? 0);
			if ($orderA !== $orderB) {
				return $orderA <=> $orderB;
			}

			return strcmp((string) ($a->name ?? ''), (string) ($b->name ?? ''));
		});

		return $children;
	}

	
}
