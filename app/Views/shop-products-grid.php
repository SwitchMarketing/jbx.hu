<main>
<?php echo view('shared/page-header', ['class' => 'product-grid']); ?>
<div class="sections">

  <section class="shop-style-one pt-5">
    <div class="container shop-container">
      <aside class="sidebar shop-sidebar">
        <div class="shop-categories">
          <div class="shop-toolbar mb-4">
            <form method="get" action="<?php echo !empty($searchTerm) ? base_url('termekek') : current_url(); ?>">
              <input
                type="text"
                name="q"
                class="form-control"
                placeholder="Keresés név, cikkszám vagy leírás alapján"
                value="<?php echo esc($searchTerm ?? ''); ?>"
              >
              <select name="sort" class="form-select" aria-label="Rendezés" onchange="this.form.submit()">
                <option value="name_asc" <?php echo (($sort ?? 'name_asc') === 'name_asc') ? 'selected' : ''; ?>>Rendezés: Név szerint (A-Z)</option>
                <option value="name_desc" <?php echo (($sort ?? 'name_asc') === 'name_desc') ? 'selected' : ''; ?>>Rendezés: Név szerint (Z-A)</option>
                <option value="price_asc" <?php echo (($sort ?? 'name_asc') === 'price_asc') ? 'selected' : ''; ?>>Rendezés: Ár szerint (olcsóbb elöl)</option>
                <option value="price_desc" <?php echo (($sort ?? 'name_asc') === 'price_desc') ? 'selected' : ''; ?>>Rendezés: Ár szerint (drágább elöl)</option>
              </select>
            </form>
          </div>

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
                    <a class="category-card__media<?php echo !empty($imageFile) ? ' category-card__media--with-image' : ''; ?>" href="<?php echo $categoryUrl; ?>" aria-label="<?php echo esc($category->name); ?> kategória megnyitása">
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
                      <a href="<?php echo $categoryUrl; ?>" class="theme-btn category-card__cta">Tovább <i class="fa-solid fa-arrow-right"></i></a>
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
            else: ?>
              <div class="col-12">
                <div class="no-results-message">
                  <div class="no-results-icon">
                    <i class="fas fa-search"></i>
                  </div>
                  <h3>Nincs találat</h3>
                  <p>A megadott feltételekre nem találtunk termékeket.</p>
                </div>
              </div>
            <?php endif; ?>
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
      <div class="shop-toolbar mb-4">
        <form method="get" action="<?php echo !empty($searchTerm) ? base_url('termekek') : current_url(); ?>">
          <input
            type="text"
            name="q"
            class="form-control"
            placeholder="Keresés név, cikkszám vagy leírás alapján"
            value="<?php echo esc($searchTerm ?? ''); ?>"
          >
          <select name="sort" class="form-select" aria-label="Rendezés" onchange="this.form.submit()">
            <option value="name_asc" <?php echo (($sort ?? 'name_asc') === 'name_asc') ? 'selected' : ''; ?>>Rendezés: Név szerint (A-Z)</option>
            <option value="name_desc" <?php echo (($sort ?? 'name_asc') === 'name_desc') ? 'selected' : ''; ?>>Rendezés: Név szerint (Z-A)</option>
            <option value="price_asc" <?php echo (($sort ?? 'name_asc') === 'price_asc') ? 'selected' : ''; ?>>Rendezés: Ár szerint (olcsóbb elöl)</option>
            <option value="price_desc" <?php echo (($sort ?? 'name_asc') === 'price_desc') ? 'selected' : ''; ?>>Rendezés: Ár szerint (drágább elöl)</option>
          </select>
        </form>
      </div>

      <?php if(isset($tree) && !empty($tree)): ?>
        <?php echo $tree; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
</main>