<div class="col-lg-4 mb-4">
    <?php
        $item = $item ?? (object) [];
        $categoryPath = trim((string) ($item->category_path ?? ''), '/');
        $masterSlug = trim((string) ($item->master_slug ?? ''), '/');
        $variantSlug = trim((string) ($item->variant_slug ?? ''), '/');

        if ($categoryPath !== '' && $masterSlug !== '' && $variantSlug !== '') {
            $productUrl = base_url('termekek/' . $categoryPath . '/' . $masterSlug . '/' . $variantSlug);
        } else {
            $productUrl = base_url('termekek');
        }
    ?>
    <div class="product">
        <div class="main-data">
            <div class="btn-hover">
                <figure>
                    <?php if (isset($item->image) && $item->image): ?>
                        <img src="<?php echo base_url('imgs/products/'.$item->image); ?>" alt="<?php echo $item->name; ?>">
                    <?php else: ?>
                        <img src="https://placehold.co/355x290" alt="Product Image">
                    <?php endif; ?>                    
                </figure>
                <a href="<?php echo $productUrl; ?>" class="theme-btn">Termékinfó <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="data">
                <?php
                    $hasDiscount = !empty($item->has_discount_variant);
                ?>
                <?php if ($hasDiscount): ?>
                    <div class="product-badge-discount"><span><i>Akció</i></span></div>
                <?php endif; ?>
                <h3><a href="<?php echo $productUrl; ?>"><?php echo $item->name; ?></a></h3>
                <div class="sku">
                    <span>SKU: <?php echo $item->sku; ?></span>
                </div>
            </div>
        </div>
    </div>
</div>