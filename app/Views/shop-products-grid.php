<main>
<?php echo view('shared/page-header', ['class' => 'product-grid']); ?>
<div class="sections">

  <section class="gap shop-style-one">
    <div class="container shop-container">
      <aside class="sidebar shop-sidebar">
        <div class="shop-categories">
          <h3 class="shop-title d-none d-xl-block">Termék kategóriák</h3>
          <a href="#offcanvasCategories" class="shop-title d-xl-none" data-bs-toggle="offcanvas" role="button" aria-controls="offcanvasCategories"><i class="fas fa-th-list"></i>Termék kategóriák</a>
          <div class="shop-tree d-none d-xl-block">
            <?php if(isset($tree) && !empty($tree)): ?>
              <?php echo $tree; ?>
            <?php endif; ?>
          </div>
        </div>
      </aside>
      <div class="shop-content">
        <?php if (!empty($showCategoryCards)): ?>
          <div class="row g-4 justify-content-center shop-categories-grid">
            <?php if (isset($categories) && is_array($categories) && count($categories) > 0): ?>
              <?php foreach ($categories as $index => $category): ?>
                <?php $categoryUrl = base_url('termekek/' . ($category->path ?? $category->slug)); ?>
                <?php $imageFile = !empty($category->image) ? $category->image : (!empty($category->default_image) ? $category->default_image : null); ?>
                <div class="col-lg-4 col-md-6">
                  <article class="category-card" style="--card-delay: <?php echo ((int) $index) * 80; ?>ms;">
                    <a class="category-card__media" href="<?php echo $categoryUrl; ?>" aria-label="<?php echo esc($category->name); ?> kategória megnyitása">
                      <?php if (!empty($imageFile)): ?>
                        <?php $imageSrc = preg_match('#^https?://#i', (string) $imageFile) ? $imageFile : base_url('imgs/products/' . ltrim((string) $imageFile, '/')); ?>
                        <img src="<?php echo $imageSrc; ?>" alt="<?php echo esc($category->name); ?>" loading="lazy">
                      <?php else: ?>
                        <div class="category-card__placeholder" aria-hidden="true">
                          <span><?php echo esc(strtoupper(mb_substr((string) $category->name, 0, 1))); ?></span>
                        </div>
                      <?php endif; ?>
                    </a>
                    <div class="category-card__content">
                      <h3><a href="<?php echo $categoryUrl; ?>"><?php echo esc($category->name); ?></a></h3>
                      <a href="<?php echo $categoryUrl; ?>" class="theme-btn category-card__cta">Termékek <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                  </article>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-lg-8">
                <div class="categories-empty-state text-center">
                  <h3>Jelenleg nincsenek alkategóriák</h3>
                  <p>Kérjük, látogasson vissza később, vagy válasszon másik kategóriát.</p>
                </div>
              </div>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div class="row justify-content-center">     
            <?php if(isset($shop) && is_array($shop->items) && count($shop->items) > 0): 
                  foreach($shop->items as $item):
                    echo view('shop/product-card', ['item' => $item]);
                  endforeach;
            endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>   
    <?php if (empty($showCategoryCards)): ?>
      <div class="container" >
        <div class="row">
          <?php if (isset($shop)): ?>
            <?php echo $shop->links; ?>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </section>
</div>
<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasCategories" aria-labelledby="offcanvasCategoriesLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasCategoriesLabel">Termék kategóriák</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Bezár"></button>
  </div>
  <div class="offcanvas-body">
    <div class="shop-categories">
      <?php if(isset($tree) && !empty($tree)): ?>
        <?php echo $tree; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
</main>