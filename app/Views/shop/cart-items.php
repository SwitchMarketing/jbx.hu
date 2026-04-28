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
                        <div class="c-price text-end">
                            <span>Egységár</span>
                        </div>
                        <div class="c-quality">
                            <span>Mennyiség</span>
                        </div>
                        <div class="c-total text-end">
                            <span>Részösszeg</span>
                        </div>
                        </div>
                    </li>
                </ul>
                <ul class="cart-table">
                    <?php foreach($cartItems as $p): ?>
                    <li>
                        <div class="c-c">
                        <div class="c-data">
                            <a class="cr-svg d-flex-all" href="javascript:void(0)" data-line-id="<?php echo (int)$p->id; ?>" data-sku="<?php echo $p->sku; ?>" title="Törlés" onclick="App.removeFromCart(this);">
                                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                            </a>
                            <?php if (isset($p->image) && $p->image): ?>
                                <img src="<?php echo base_url('imgs/products/'.$p->image); ?>" width="80" alt="<?php echo $p->name; ?>">
                            <?php else: ?>
                                <img src="https://placehold.co/80x80" alt="<?php echo $p->name ?>">
                            <?php endif; ?>                            
                            <h2><a href="javascript:void(0)"><?php echo $p->name ?></a></h2>
                        </div>
                        <div class="c-price text-end">
                            <?php if((float)$p->price > 0): ?>
                            <span class="orgnl"><?php echo format_price((float)$p->price) ?></span>                            
                            <?php else: ?>
                            <span class="orgnl">-</span>
                            <?php endif; ?>
                        </div>
                        <div class="c-quality">
                            <div class="qty-control">
                                <button type="button" class="qty-btn qty-minus" aria-label="Mennyiség csökkentése" onclick="App.changeQty(this, -1)">-</button>
                                <input
                                    type="number"
                                    name="number"
                                    value="<?php echo (int)$p->qty ?>"
                                    id="qty-<?php echo $p->sku; ?>"
                                    min="1"
                                    class="text-center qty-input"
                                    data-line-id="<?php echo (int)$p->id; ?>"
                                    onchange="App.updateCartQty(this)"
                                >
                                <button type="button" class="qty-btn qty-plus" aria-label="Mennyiség növelése" onclick="App.changeQty(this, 1)">+</button>
                            </div>
                        </div>
                        <div class="c-total text-end">
                            <?php $lineTotal = $p->price * $p->qty; ?>
                            <?php if($lineTotal > 0): ?>
                                <span><?php echo format_price($lineTotal) ?></span>
                            <?php else: ?>
                                <span>Ajánlatkérés</span>
                            <?php endif; ?>
                        </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="row cart-total justify-content-end">
        <div class="col-lg-6">
            <?php if($cartTotal > 0): echo view('shop/cart-total-box', [
                'cartTotal'    => $cartTotal,
                'cartNetTotal' => $cartNetTotal,
                'cartVat'      => $cartVat
            ]); ?>
            <?php endif; ?>
            <div class="update-cart d-flex-all justify-content-end">
                <a href="<?php echo base_url('megrendeles') ?>" class="theme-btn">Tovább a rendeléshez <i class="fa-solid fa-angles-right"></i></a>
            </div>                
        </div>
    </div>
    </div>
</section>
<!-- Cart End -->