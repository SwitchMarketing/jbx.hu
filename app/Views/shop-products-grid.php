<main>
<?php echo view('shared/page-header', ['title' => 'Termékeink']); ?>
<div class="sections">

<section class="gap shop-style-one">
    <div class="container">
      <div class="row align-items-center justify-content-center">     
        <?php if(isset($shop) && is_array($shop->items) && count($shop->items) > 0): ?>
        <?php foreach($shop->items as $item): ?>
        <?php echo view('shop/product-card', ['item' => $item]); ?>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
    <div class="container" >
      <div class="row">
        <?php echo $shop->links; ?>
      </div>
    </div>
  </section>

</div>
</main>