  <!-- Page Header -->
  <section class="page-header shop">
    <div class="parallax" style="background-image: url(<?php echo img_src('pattern-3.png') ?>);"></div>    
    <?php if( isset($breadcrumbs) ): ?>    
    <div class="breadcrums">
      <div class="container">
        <div class="row">
          <ul>
            <li>
              <a href="<?php echo base_url() ?>">
                <i class="fa-solid fa-house"></i> Főoldal
              </a>
            </li>
            <?php foreach($breadcrumbs as $bc): ?>
            <li>
              <a href="<?php echo $bc->url ?>">
                <?php echo $bc->title ?>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </section>
  <!-- ./Page Header -->