<main>
    <?php echo view('shared/page-header', ['title' => 'Kosár']); ?>
    <div class="sections">

        <!-- Cart Start -->
        <section class="gap cart">
            <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="cart-box">
                        <ul class="cart-table head">
                            <li>
                                <div class="c-c">
                                <div class="c-data">
                                    <span>Megnevezés</span>
                                </div>
                                <div class="c-price">
                                    <span>Egységár</span>
                                </div>
                                <div class="c-quality">
                                    <span>Mennyiség</span>
                                </div>
                                <div class="c-total">
                                    <span>Részösszeg</span>
                                </div>
                                </div>
                            </li>
                        </ul>
                        <ul class="cart-table">
                            <?php for($i = 1; $i <= 4; $i++): ?>
                            <li>
                                <div class="c-c">
                                <div class="c-data">
                                    <a class="cr-svg d-flex-all" href="javascript:void(0)">
                                    <img src="imgs/cross.svg" alt="Cross Svg">
                                    </a>
                                    <img src="https://placehold.co/80x80" alt="Product One">
                                    <h2><a href="javascript:void(0)">Fosroc Galvafroid – 400ml</a></h2>
                                </div>
                                <div class="c-price">
                                    <span class="orgnl">$ 400.00</span>
                                    <del class="sale">$ 500.00</del>
                                </div>
                                <div class="c-quality">
                                    <input type="number" name="number" value="1">
                                </div>
                                <div class="c-total">
                                    <span>$ 400.00</span>
                                </div>
                                </div>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row cart-total">
                <div class="col-lg-6"></div>
                <div class="col-lg-6">
                    <div class="cart-total-box">
                        <div class="parallax" style="background-image: url(imgs/pattren-4.png);"></div>
                        <div class="final">
                        <h4>Összesítés</h4>
                        <ul>
                            <li>
                                <span>Nettó:</span>
                                <span><?php echo format_price('358', 'HUF') ?></span>
                            </li>
                            <li>
                                <span>ÁFA:</span>
                                <span><?php echo format_price('158', 'HUF') ?></span>
                            </li>
                        </ul>
                        </div>
                        <div class="total">
                        <ul>
                            <li>
                                <span>Összesen:</span>
                                <span><?php echo format_price('3555558', 'HUF') ?></span>
                            </li>
                        </ul>
                        </div>
                    </div>
                    <div class="update-cart d-flex-all justify-content-end">
                        <a href="#" class="theme-btn">Tovább a pénztárhoz <i class="fa-solid fa-angles-right"></i></a>
                    </div>                
                </div>
            </div>
            </div>
        </section>
        <!-- Cart End -->

    </div>
</main>