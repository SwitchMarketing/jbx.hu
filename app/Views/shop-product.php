<main>
    <?php echo view('shared/page-header', ['class' => 'product-grid']); ?>
    <div class="sections">

        <!-- Product Detail Start -->
        <section class="product-detail pt-5">
            <div class="container">
                <div class="row align-items-start">
                    <div class="col-12 col-lg-6">
                        <div class="pd-gallery">
                            <?php if (!empty($product->images) && is_array($product->images) && count($product->images) > 1) : ?>
                            <div class="pd-thumb-slider" data-role="pd-thumb-slider">
                                <div class="pd-thumb-slider__viewport">
                                    <ul class="pd-imgs">
                                        <?php foreach ($product->images as $index => $image) : ?>
                                        <li class="li-pd-imgs<?php echo $index === 0 ? ' nav-active' : ''; ?>">
                                            <a href="JavaScript:void(0)">
                                                <img src="<?php echo product_image($image->filename); ?>" alt="<?php echo esc($product->name); ?>" class="img-fluid">
                                            </a>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="pd-main-img">
                                <img id="NZoomImg" data-NZoomscale="2" style="width: 100%;height: 100%;" src="<?php echo product_cover_image($product); ?>" alt="<?php echo esc($product->name); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="pd-data">
                            <h2 class="pd-product-name"><?php echo esc($product->name); ?></h2>
                            <?php if (!empty($product->sku)) : ?>
                                <p id="pdSkuMeta" class="pd-sku-meta">SKU: <?php echo esc($product->sku); ?></p>
                            <?php else : ?>
                                <p id="pdSkuMeta" class="pd-sku-meta d-none"></p>
                            <?php endif; ?>
                            <?php echo view('shared/option-pills', ['options' => $options, 'optionMatrix' => $optionMatrix ?? [], 'masterSlug' => $masterSlug ?? '', 'product' => $product]); ?>
                            <div id="pdPriceWrap"><?php echo product_price($product); ?></div>
                            <div class="pd-purchase">
                                <div class="pd-quality">
                                    <span>Mennyiség</span>
                                    <input class="pd-qty-input" data-role="pd-qty-input" type="number" name="number" id="qty-<?php echo esc($product->sku) ?>" value="1" min="1" step="1" inputmode="numeric" aria-label="Mennyiség">
                                </div>
                                <div id="pdAddToCartWrap" class="pd-add-to-cart">
                                    <?php echo add_to_cart_button($product); ?>
                                </div>
                            </div>
                            <div id="cartMessages" class="mt-4"></div>
                            <!--                                                   
                            <div class="pd-cat-tags">
                                <ul>
                                    <li>
                                        <span class="theme-bg-clr font-bold">Sku:</span>
                                        <ul class="pd-sku">
                                            <li><?php echo esc($product->sku); ?></li>
                                        </ul>
                                    </li>                                    
                                </ul>
                            </div>
                            -->
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="gap detail-page">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="pd-details">
                            <div class="more d-flex align-items-start">
                                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                    <?php if (!empty($product->description)) : ?>
                                        <button class="nav-link active" id="v-pills-home-tab" data-bs-toggle="pill" data-bs-target="#v-pills-home" type="button" role="tab" aria-controls="v-pills-home" aria-selected="true">Leírás</button>
                                    <?php endif; ?>
                                    <?php if (!empty($product->params) || !empty($product->sku)) : ?>
                                        <button class="nav-link <?php echo empty($product->description) ? 'active' : ''; ?>" id="v-pills-profile-tab" data-bs-toggle="pill" data-bs-target="#v-pills-profile" type="button" role="tab" aria-controls="v-pills-profile" aria-selected="false">Jellemzők</button>
                                    <?php endif; ?>                                                                         
                                </div>
                                <div class="tab-content" id="v-pills-tabContent">
                                    <?php if(!empty($product->description)) : ?>
                                    <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                                        <div class="des-tab">
                                            <?php echo product_description($product->description); ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($product->params) || !empty($product->sku)) : $params = !empty($product->params) ? json_decode($product->params) : null; ?>                                    
                                        <div class="tab-pane fade <?php echo empty($product->description) ? 'show active' : ''; ?>" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                                        <div class="adis-tab">
                                            <div class="tab-table">
                                                <table class="table">
                                                    <tbody id="pdFeatureTableBody">
                                                        <?php if (!empty($product->sku)) : ?>
                                                            <tr>
                                                                <td>SKU</td>
                                                                <td><?php echo esc($product->sku); ?></td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    <?php if(is_object($params)): ?>
                                                        <tr>
                                                            <td><?php echo esc($params->Name); ?></td>
                                                            <td><?php echo esc($params->Value); ?></td>
                                                        </tr>
                                                    <?php elseif(is_array($params)) : ?>                                                    
                                                        <?php foreach ($params as $param) : ?>
                                                            <tr>
                                                                <td><?php echo esc($param->Name); ?></td>
                                                                <td><?php echo esc($param->Value); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>                                                    
                                                    <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div> 
                                    <?php endif; ?>                                   
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Product Detail End -->

    </div>
</main>

<?php if (!empty($initialVariantPayload) && is_array($initialVariantPayload)) : ?>
<script>
window.JBX_INITIAL_VARIANT = <?php echo json_encode(
    array_merge(['success' => true], $initialVariantPayload),
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
); ?>;
</script>
<?php endif; ?>