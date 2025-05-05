<main>
    <?php echo view('shared/page-header', ['title' => 'Pénztár']); ?>
    <div class="sections">

        <!-- Cart Start -->
        <section class="gap checkout detail-page">
            <form>
                <div class="container">
                    <div class="row">
                        <div class="col-lg-7 col-md-12">
                            <div class="billing w-100">
                                <h3>Számlázási cím</h3>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="name" placeholder="Név">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="email" name="email" placeholder="Email cím">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="tel" name="phone" placeholder="Telefonszám">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="company" placeholder="Cégnév">
                                    </div>
                                </div>
                                <div class="row dist">
                                    <div class="col-md-6">
                                        <input type="number" name="zip" placeholder="IRSZ" maxlength="4">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <select id="inputState-2" class="form-control">
                                            <option selected>Település</option>                                                    
                                        </select>
                                    </div>                                            
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="address" placeholder="Cím">
                                    </div>
                                </div>
                                <div class="row checkk g-0">
                                    <div class="form-group col-md-12">
                                        <div class="custom-control custom-radio">
                                            <input type="checkbox" id="diffDeliveryAddress" name="diffDeliveryAddress" class="custom-control-input" onchange="App.toggleDeliveryAddr(this)">
                                            <label class="custom-control-label" for="diffDeliveryAddress">Más szállítási címet adok meg</label>
                                        </div>
                                    </div>                                            
                                </div>
                                <div id="deliveryAddr" class="pb-3 d-none">
                                    <h3 class="mt-0">Szállítási cím</h3>
                                    <div class="row dist">
                                        <div class="col-md-6">
                                            <input type="number" name="zip" placeholder="IRSZ" maxlength="4">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <select id="inputState-2" class="form-control">
                                                <option selected>Település</option>                                                    
                                            </select>
                                        </div>                                            
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" name="address" placeholder="Cím">
                                        </div>
                                    </div>                                                                        
                                </div>
                                <div class="row">
                                    <div class="order-note">
                                        <h3>Megjegyzés</h3>
                                        <textarea placeholder="Megjegyzés"></textarea>
                                    </div>
                                </div>                                        
                            </div>                                                                
                        </div>
                        <div class="col-lg-5 col-md-12">
                            <div class="cart-t-payment-m">
                                <div class="cart-total-box w-100">
                                    <div class="parallax" style="background-image: url(/imgs/pattren-4.png);"></div>
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
                                    <div class="update-cart d-flex-all justify-content-start mt-4">
                                        <a href="#" class="theme-btn">Megrendelés <i class="fa-solid fa-angles-right"></i></a>
                                    </div>
                                </div>                                                                    
                            </div>                                
                        </div>
                    </div> 
                </div>
            </form>
        </section>
        <!-- Cart End -->

    </div>
</main>