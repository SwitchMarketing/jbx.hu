<main>
    <?php echo view('shared/page-header', ['title' => 'Kosár']); ?>
    <div class="sections">
        <?php if( isset($cartItems) && count($cartItems) ): 
                echo view('shop/cart-items', ['cartItems' => $cartItems]); 
                else:
                echo view('shop/cart-empty'); 
        endif; ?>
    </div>
</main>