<main>
<?php echo view('shared/page-header', ['title' => 'Belépés / regisztráció']); ?>
<div class="sections">

<section class="gap login-register">
    <div class="container">
      <div class="row">
        <div class="col-lg-6" >
          <div class="box login">
            <h3>Bejelentkezés</h3>
            <?php echo $this->include('forms/login') ?>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="box register">
            <div class="parallax" style="background-image: url(imgs/pattren.png);"></div>
            <h3>Regisztráció</h3>
            <?php echo $this->include('forms/register') ?>
          </div>
        </div>
      </div>
    </div>
  </section>

</div>
</main>