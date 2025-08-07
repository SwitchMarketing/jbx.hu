<main>
<?php echo view('shared/shop-header'); ?>
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
        <div class="row justify-content-center">     
          <?php if(isset($shop) && is_array($shop->items) && count($shop->items) > 0): 
                foreach($shop->items as $item):
                  echo view('shop/product-card', ['item' => $item]);
                endforeach;
          endif; ?>
        </div>
      </div>
    </div>   
    <div class="container" >
      <div class="row">
        <?php echo $shop->links; ?>
      </div>
    </div>
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