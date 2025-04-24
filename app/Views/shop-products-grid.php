<main>
<?php echo view('shared/page-header', ['title' => 'Termékeink']); ?>
<div class="sections">

<section class="gap shop-style-one">
    <div class="container">
      <div class="row align-items-center justify-content-between">     
        <?php for($i=1; $i<=9; $i++): echo view('shop/product-card'); endfor; ?>
      </div>
    </div>
    <div class="container" >
      <div class="row">
        <div class="builty-pagination">
          <nav aria-label="Page navigation example">
            <ul class="pagination">
              <li class="page-item"><a class="page-link" href="JavaScript:void(0)"><i class='fa-solid fa-arrow-left-long'></i></a></li>
              <li class="page-item"><a class="page-link" href="JavaScript:void(0)">01</a></li>
              <li class="page-item"><a class="page-link" href="JavaScript:void(0)">02</a></li>
              <li class="page-item"><a class="page-link" href="JavaScript:void(0)">03</a></li>
              <li class="page-item space"><a class="page-link" href="JavaScript:void(0)">..........</a></li>
              <li class="page-item"><a class="page-link" href="JavaScript:void(0)">08</a></li>
              <li class="page-item"><a class="page-link" href="JavaScript:void(0)"><i class='fa-solid fa-arrow-right-long'></i> </a></li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </section>

</div>
</main>