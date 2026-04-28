<form action="<?php echo base_url('megrendeles') ?>" method="post" id="order_form">
    <input type="hidden" name="<?php echo csrf_token() ?>" value="<?php echo csrf_hash() ?>">
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
            <input type="tel" name="phone" placeholder="Pl. +36301234567">
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <input type="text" name="company" placeholder="Cégnév">
        </div>
    </div>
    <div class="row dist">
        <div class="col-md-6">
            <input type="text" name="billing_zip" placeholder="IRSZ" maxlength="4">
        </div>
        <div class="form-group col-md-6">
            <input type="text" name="billing_state" placeholder="Település">            
        </div>                                            
    </div>
    <div class="row">
        <div class="col-md-12">
            <input type="text" name="billing_address" placeholder="Cím">
        </div>
    </div>
    <div class="row checkk g-0">
        <div class="form-group col-md-12">
            <div class="custom-control custom-radio">
                <input type="checkbox" id="diffDeliveryAddress" name="diffDeliveryAddress" class="custom-control-input" value="on" onchange="App.toggleDeliveryAddr(this)">
                <label class="custom-control-label" for="diffDeliveryAddress">Más szállítási címet adok meg</label>
            </div>
        </div>                                            
    </div>
    <div id="deliveryAddr" class="pb-3 d-none">
        <h3 class="mt-0">Szállítási cím</h3>
        <div class="row dist">
            <div class="col-md-6">
                <input type="text" name="delivery_zip" placeholder="IRSZ" maxlength="4">
            </div>
            <div class="form-group col-md-6">
                <input type="text" name="delivery_state" placeholder="Település">
            </div>                                            
        </div>
        <div class="row">
            <div class="col-md-12">
                <input type="text" name="delivery_address" placeholder="Cím">
            </div>
        </div>                                                                        
    </div>
    <div class="row">
        <div class="order-note">
            <h3>Megjegyzés</h3>
            <textarea placeholder="Megjegyzés" name="comments"></textarea>
        </div>
    </div>
    <div class="row g-0">
        <div class="custom-control custom-checkbox custom-checkbox-privacy">
            <input type="checkbox" class="custom-control-input" id="orderPrivacy" name="privacy" value="1">
            <label class="custom-control-label" for="orderPrivacy">Elolvastam és elfogadom az <a href="<?php echo base_url('adatkezeles') ?>" target="_blank">adatkezelési tájékoztatót</a>.</label>
        </div>
    </div>
    <div class="row g-0">
        <div class="custom-control custom-checkbox custom-checkbox-privacy">
            <input type="checkbox" class="custom-control-input" id="orderTerms" name="terms" value="1">
            <label class="custom-control-label" for="orderTerms">Elolvastam és elfogadom az <a href="<?php echo base_url('altalanos-szerzodesi-feltetelek') ?>" target="_blank">Általános Szerződési Feltételeket</a>.</label>
        </div>
    </div>
    <div class="row">
        <div class="messages"></div>
        <div class="update-cart">
            <button type="button" class="theme-btn submit" onclick="App.submitOrder(this)">Megrendelés elküldése <i class="fa-solid fa-paper-plane"></i></button>
        </div>
    </div>
</form>  