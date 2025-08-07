<div class="col-lg-4 mb-4">
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
                <a href="<?php echo base_url('termekek/'.$item->category_path.'/'.$item->slug) ?>" class="theme-btn">Termékinfó <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="data">
                <h3><a href="<?php echo base_url('termekek/'.$item->category_path.'/'.$item->slug) ?>"><?php echo $item->name; ?></a></h3>
                <div class="sku">
                    <span>SKU: <?php echo $item->sku; ?></span>
                </div>
                <div class="price-range">
                    <span></span>
                </div>                
            </div>
        </div>
    </div>
</div>