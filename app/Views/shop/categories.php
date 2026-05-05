<!-- Categories Start -->
<main class="shop-categories-page">
<?php echo view('shared/page-header', ['title' => 'Termék Kategóriák']); ?>
<div class="sections">
    <?php $categoryCount = isset($categories) && is_array($categories) ? count($categories) : 0; ?>
    <section class="gap shop-style-one shop-categories-grid">
        <div class="container shop-container d-flex justify-content-center">
            <div class="shop-content w-100">
                <div class="row g-4 justify-content-center">
                    <?php if (isset($categories) && is_array($categories) && count($categories) > 0): ?>
                        <?php foreach ($categories as $index => $category): ?>
                        <?php $categoryUrl = base_url('termekek/' . $category->slug); ?>
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
                                <h3>Jelenleg nincsenek kategóriák</h3>
                                <p>Kérjük, látogasson vissza később, vagy vegye fel velünk a kapcsolatot egyedi ajánlatért.</p>
                                <a href="<?php echo base_url('kapcsolat'); ?>" class="theme-btn">Kapcsolat <i class="fa-solid fa-arrow-right"></i></a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>
</main>
<!-- Categories End -->
