<?php
$benefits = [
    [
        'title' => 'Higiénikus kialakítás',
        'text'  => 'A vízszintes, port és szennyeződést gyűjtő felületek hiánya megkönnyíti a tisztítást.'
    ],
    [
        'title' => 'Merevebb panelek',
        'text'  => 'A kerítéselemeken kialakított további hajlítások növelik a panelek szilárdságát és a dolgozók biztonságát.'
    ],
    [
        'title' => 'Helyszíni méretre igazítás',
        'text'  => 'A kerítéselemek szélessége 20 mm-es lépésekben állítható.'
    ],
    [
        'title' => 'Állítható konzolok',
        'text'  => 'Egyszerű szerszámokkal, a szerelés helyszínén is a szükséges szögbe hajlíthatók.'
    ],
    [
        'title' => 'Higiénikus csatlakozások',
        'text'  => 'A kötések peremes csavarokkal és jól felismerhető, kék színű, nyomon követhető távtartókkal vannak kialakítva.'
    ],
    [
        'title' => 'Megemelt oszlopok lehetősége',
        'text'  => 'Az opcionális menetes szárak megemelik az oszlopokat a padlótól. Ez megkönnyíti az alatta történő takarítást, és segít kiegyenlíteni a lejtős padlót nedves környezetben.'
    ]
];
?>
  <!-- Comp-Line -->
  <section class="gap service-style-two ntf-comp-line">
    <div class="heading">
      <figure>
        <img src="<?php echo img_src('logo-axelent.svg') ?>" alt="AXELENT Safety Design" loading="lazy">
      </figure>
      <span>Comp-Line gépvédő kerítés</span>
      <h2>Kialakítás és előnyök</h2>
    </div>
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <h4 class="mb-5 text-center fw-normal">Moduláris panelekből felépíthető rendszer a gyártóberendezések körül.</h4>
        </div>
      </div>
      <div class="row g-0">
        <?php foreach($benefits as $i => $benefit): ?>
        <div class="col-lg-4 col-md-6 col-sm-12">
          <div class="service-two-box">
            <h3><?php echo $benefit['title'] ?></h3>
            <p><?php echo $benefit['text'] ?></p>
            <div class="service-two-icon ntf-number"><?php echo sprintf('%02d', $i + 1) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <!-- ./Comp-Line -->
