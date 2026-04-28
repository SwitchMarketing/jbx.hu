<!-- Categories Start -->
<main>
<?php echo view('shared/page-header', ['class' => 'product-grid', 'title' => 'Termék Kategóriák', 'caption' => 'Válassza ki az Önnek szükséges kategóriát']); ?>
<div class="sections">
    <section class="gap shop-style-one">
        <div class="container shop-container d-flex justify-content-center">
            <div class="shop-content w-100">
                <div class="row justify-content-center">
                    <?php if (isset($categories) && is_array($categories) && count($categories) > 0): ?>
                        <?php foreach($categories as $category): ?>
                        <div class="col-lg-4 mb-4">
                            <div class="product">
                                <div class="main-data">
                                    <div class="btn-hover">
                                        <figure>
                                            <?php if ($category->image): ?>
                                                <img src="<?php echo base_url('imgs/products/' . $category->image); ?>" alt="<?php echo $category->name; ?>" loading="lazy">
                                            <?php else: ?>
                                                <img src="https://placehold.co/355x290" alt="<?php echo $category->name; ?>">
                                            <?php endif; ?>
                                        </figure>
                                        <a href="<?php echo base_url('termekek/' . $category->slug); ?>" class="theme-btn">Termékek <i class="fa-solid fa-arrow-right"></i></a>
                                    </div>
                                    <div class="data">
                                        <h3><a href="<?php echo base_url('termekek/' . $category->slug); ?>"><?php echo $category->name; ?></a></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-lg-12">
                            <p class="text-center">Jelenleg nincsenek kategóriák.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>
</main>
<!-- Categories End -->
