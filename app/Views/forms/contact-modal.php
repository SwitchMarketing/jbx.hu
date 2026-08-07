<form id="contact_form_modal" action="<?php echo base_url('kapcsolat') ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="<?php echo csrf_token() ?>" value="<?php echo csrf_hash() ?>">        
    <div class="row mb-2">
        <div class="form-group col-md-12">
            <p class="mb-0">A <strong>*</strong> jelölt mezők kitöltése kötelező.</p>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-12">
            <label for="contactModalName">Teljes név *</label>
            <input type="text" name="name" class="form-control" id="contactModalName"  placeholder="Teljes név" required>
        </div>
        <div class="form-group col-md-12 mt-3">
            <label for="contactModalCompanyName">Cégnév *</label>
            <input type="text" name="company_name" class="form-control" id="contactModalCompanyName"  placeholder="Cégnév" required>
        </div>
        <div class="form-group col-md-12 mt-3">
            <label for="contactModalCompanyAddress">Székhely *</label>
            <input type="text" name="company_address" class="form-control" id="contactModalCompanyAddress"  placeholder="Székhely" required>
        </div>
        <div class="form-group col-md-12 mt-3">
            <label for="contactModalTaxNumber">Adószám *</label>
            <input type="text" name="tax_number" class="form-control" id="contactModalTaxNumber"  placeholder="Adószám" required>
        </div>
        <div class="form-group col-lg-6 mt-3">
            <label for="contactModalEmail">Email cím *</label>
            <input type="email" name="email" class="form-control" id="contactModalEmail"  placeholder="Email cím">
        </div>
        <div class="form-group col-lg-6 mt-3">
            <label for="contactModalPhone">Telefonszám *</label>
            <input type="tel" name="phone_number" class="form-control" id="contactModalPhone"  placeholder="Telefonszám">
        </div>
        <div class="form-group col-md-12 mt-2">
            <small>Email cím vagy telefonszám megadása kötelező.</small>
        </div>
    </div>
    <?php if( isset($products) ): ?>
    <div class="row checkk g-2">
        <p>Termékcsalád *</p>
        <?php foreach($products as $p): ?>
            <div class="form-group col-md-6">
                <div class="custom-control custom-radio">
                    <input type="checkbox" name="products[]" value="<?php echo $p->value ?>" id="productModal<?php echo $p->id ?>" class="custom-control-input" <?php echo ($p->selected) ? 'checked' : '' ?>>
                    <label class="custom-control-label" for="productModal<?php echo $p->id ?>"><?php echo $p->label ?></label>
                </div>
            </div>
        <?php endforeach; ?>            
    </div>
    <?php endif; ?>
    <!-- Fájl(ok) -->                 
    <div class="row g-0">       
        <div class="dropzone" data-title="Terv, alaprajz feltöltés"></div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <textarea id="contactModalMessage" name="message" class="form-control" placeholder="Megjegyzések"></textarea>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="custom-control custom-checkbox custom-checkbox-privacy">
                <input type="checkbox" class="custom-control-input" id="privacyModal" name="privacy" value="1" required>
                <label class="custom-control-label" for="privacyModal">Hozzájárulok a fenti személyes adataim kezeléséhez, kapcsolatfelvétel és árajánlat küldés céljából. Az <a href="<?php echo base_url('adatkezeles') ?>" target="_blank">adatkezelési tájékoztató</a> ide vonatkozó rendelkezéseit megértettem, annak tartalmát ismerem és elfogadom. *</label>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-12 text-center">
            <div class="messages"></div>
            <button type="button" class="theme-btn submit">Elküldöm <i class="fa-solid fa-angles-right"></i></button>
        </div>
    </div>
</form>