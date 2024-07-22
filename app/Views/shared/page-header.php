  <!-- Page Header -->
  <section class="page-header">
    <div class="parallax" style="background-image: url(<?php echo img_src('pattern-3.png') ?>);"></div>
    <div class="container">
      <div class="row">
        <div class="banner-details">
          <h2><?php echo $title ?? '' ?></h2>
          <p><?php echo $caption ?? '' ?></p>
        </div>
      </div>
    </div>
    <div class="breadcrums">
      <div class="container">
        <div class="row">
          <ul>
            <li>
              <a href="<?php echo base_url() ?>">
                <i class="fa-solid fa-house"></i>
                <p>Főoldal</p>
              </a>
            </li>
            <li class="current">
              <p><?php echo $title ?? '' ?></p>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </section>
  <!-- ./Page Header -->