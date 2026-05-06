<main>
    <?php echo view('shared/page-header', [
        'class'       => 'product-grid',
        'breadcrumbs' => [
            (object) ['title' => 'Kosár',       'url' => base_url('kosar')],
            (object) ['title' => 'Megrendelés', 'url' => base_url('megrendeles')],
        ],
    ]); ?>
    <div class="sections">

        <!-- Cart Start -->
        <section class="checkout detail-page contact-form-2 order-checkout-section pt-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="data">
                            <div class="est-form billing w-100">
                                <h3>Számlázási adatok</h3>
                                <?php echo view('forms/order'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 offset-lg-1">
                        <div class="order-summary-side">
                            <?php if($cartTotal): ?>
                                <?php echo view('shop/cart-total-box', [
                                    'cartTotal'    => $cartTotal,
                                    'cartNetTotal' => $cartNetTotal,
                                    'cartVat'      => $cartVat,
                                    'vatRatePercent' => $vatRatePercent ?? null
                                ]); ?>
                            <?php endif; ?>
                                <div class="order-trust-box mt-0">
                                    <span class="order-trust-box__eyebrow">Biztonságos megrendelés</span>
                                    <h3>Mi történik a beküldés után?</h3>
                                    <p>Rövid időn belül visszaigazoljuk a rendelést, és egyeztetjük a szállítás részleteit.</p>

                                    <ul class="order-trust-box__list">
                                        <li>
                                            <i class="fa-solid fa-clock"></i>
                                            <span>Válaszidő: jellemzően 1 munkanapon belül</span>
                                        </li>
                                        <li>
                                            <i class="fa-solid fa-shield-halved"></i>
                                            <span>Tételes visszaigazolás az árakról és a készletről</span>
                                        </li>
                                        <li>
                                            <i class="fa-solid fa-user-check"></i>
                                            <span>Valódi szakértői kapcsolattartás, automatizmusok nélkül</span>
                                        </li>
                                    </ul>

                                    <div class="order-trust-box__contact">
                                        <a href="tel:<?php echo default_phone_number(true) ?>"><i class="fa-solid fa-phone"></i> <?php echo default_phone_number() ?></a>
                                        <a href="mailto:<?php echo config('Config\\AppConfig')->siteEmail ?>"><i class="fa-solid fa-envelope"></i> <?php echo config('Config\\AppConfig')->siteEmail ?></a>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Cart End -->

    </div>
</main>