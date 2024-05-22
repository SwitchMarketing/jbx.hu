<div class="modal fade popups est-popup" id="contactModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="estimated-price popup">
          <div class="est-form">
            <h3>Ajánlatkérés</h3>
            <p class="mt-3">Egyeztessünk az igényekről, vagy egy helyszíni felmérésről, de ha már kész tervek vannak, küldd el számunkra és készítünk egy pontos, naprakész ajánlatunkat.</p>
            <?php echo $this->include('forms/contact-modal') ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade popups" id="videoModal" tabindex="-1" aria-hidden="true" data-bs-keyboard="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-1">
        <div class="ratio ratio-16x9">
          <iframe src="" allow="autoplay;" allowfullscreen></iframe>
        </div>
      </div>
    </div>
  </div>
</div>