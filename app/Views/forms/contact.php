<form id="contact_form" action="<?php echo base_url('kapcsolat') ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="<?php echo csrf_token() ?>" value="<?php echo csrf_hash() ?>">
    <div class="row g-0">
        <input type="text" name="name" class="form-control" id="contactName" placeholder="Név">
    </div>
    <div class="row g-0">
        <input type="email" name="email" class="form-control" id="contactEmail" placeholder="Email cím">
    </div>
    <div class="row g-0">
        <input type="tel" name="phone_number" class="form-control" id="contactPhone" placeholder="Telefonszám">
    </div>
    <?php if( isset($products) ): ?>
    <!-- Termékek -->
    <div class="row checkk g-2">
        <p>Termékcsalád</p>    
        <?php foreach($products as $p): ?>
            <div class="form-group col-md-6">
                <div class="custom-control custom-radio">
                    <input type="checkbox" name="products[]" value="<?php echo $p->value ?>" id="product<?php echo $p->id ?>" class="custom-control-input">
                    <label class="custom-control-label" for="product<?php echo $p->id ?>"><?php echo $p->label ?></label>
                </div>
            </div>
        <?php endforeach; ?>            
    </div>
    <?php endif; ?>
    <!-- Fájl(ok) -->                 
    <div class="row g-0">       
        <div class="dropzone" data-title="Terv, alaprajz feltöltés"></div>
    </div>
    <!-- Megjegyzés -->
    <div class="row g-0">
        <textarea placeholder="Megjegyzés" name="message" class="form-control"></textarea>
    </div>
    <!-- Adatvédelem -->
    <div class="row g-0">
        <div class="custom-control custom-checkbox custom-checkbox-privacy">
            <input type="checkbox" class="custom-control-input" id="privacyModal" name="privacy" value="1" required>
            <label class="custom-control-label" for="privacyModal">Hozzájárulok a fenti személyes adataim kezeléséhez, kapcsolatfelvétel és árajánlat küldés céljából. Az <a href="<?php echo base_url('adatkezeles') ?>" target="_blank">adatkezelési tájékoztató</a> ide vonatkozó rendelkezéseit megértettem, annak tartalmát ismerem és elfogadom.</label>
        </div>
    </div>
    <div class="row g-0 mt-4">
        <div class="col-lg-12">
            <div class="messages"></div>
            <button type="button" class="theme-btn submit">Elküldöm <i class="fa-solid fa-angles-right"></i></button>
        </div>        
    </div>    
</form>