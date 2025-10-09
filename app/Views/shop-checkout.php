<main>
    <?php echo view('shared/page-header', ['title' => 'Megrendelés']); ?>
    <div class="sections">

        <!-- Cart Start -->
        <section class="gap checkout detail-page">
            <form>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-7 col-md-12">
                            <div class="billing w-100">
                                <h3>Számlázási cím</h3>
                                <?php echo view('forms/order'); ?>                           
                            </div>                                                                
                        </div>
                        <?php if($cartTotal): ?>
                            <div class="col-lg-5 col-md-12">
                                <div class="cart-t-payment-m">
                                   <?php echo view('shop/cart-total-box', [
                                        'cartTotal'    => $cartTotal,
                                        'cartNetTotal' => $cartNetTotal,
                                        'cartVat'      => $cartVat
                                   ]); ?>                                                                     
                                </div>                                
                            </div>
                        <?php endif; ?>                        
                    </div> 
                </div>
            </form>
        </section>
        <!-- Cart End -->

    </div>
</main>