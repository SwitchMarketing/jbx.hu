<?php
$guards = [
    [
        'title' => 'Kör alakú gépvédő burkolat',
        'text'  => 'Hengeres berendezések körül mozgó alkatrészek védelmére kialakított, egyedi megoldás.',
        'img'   => 'ntf-protect/kor-alaku-gepvedo.webp'
    ],
    [
        'title' => 'Több szögből védő burkolat',
        'text'  => 'Különböző irányokból biztosít védelmet, és a géphez vagy a környező felületekhez illeszkedik.',
        'img'   => 'ntf-protect/tobbszogu-gepvedo.webp'
    ],
    [
        'title' => 'Belső gépvédő burkolat',
        'text'  => 'Nyílások lefedésére vagy ívelt belső géprészek védelmére alkalmas.',
        'img'   => 'ntf-protect/belso-gepvedo.webp'
    ],
    [
        'title' => 'Szállítópálya-védelem hozzáférési lehetőséggel',
        'text'  => 'Beépített fogantyúi megkönnyítik a hozzáférést karbantartáskor vagy egy termék eltávolításakor.',
        'img'   => 'ntf-protect/szallitopalya-vedelem.webp'
    ]
];
?>
  <!-- In-Line -->
  <section class="gap ntf-in-line">
    <div class="container">
      <div class="row align-items-center mb-5">
        <div class="col-lg-5 order-lg-last mb-4 mb-lg-0">
          <figure class="ntf-in-line--image">
            <img src="<?php echo img_src('ntf-protect/in-line-gepvedo.webp') ?>" alt="NTF In-Line gépvédő elem" loading="lazy" width="870" height="1006">
          </figure>
        </div>
        <div class="col-lg-7">
          <div class="heading px-0">
            <span class="d-block text-start">In-Line gépvédő elem</span>
            <h2 class="text-start w-100">Egyedi védelem közvetlenül a gépen</h2>
          </div>
          <p>Egyedi kialakítású védelem, amely közvetlenül a gépre vagy meglévő védőrendszerre szerelhető.</p>
          <p>Az NTF In-Line védőelemei olyan gépekhez készülnek, amelyek pontosan illeszkedő, egyedi védelmet igényelnek. Ívelt berendezésekhez, szállítórendszerekhez és a gépek belső részeihez is tervezhetők; közvetlenül a géphez illeszkedve nyújtanak védelmet és támogatják a higiénikus működést.</p>
        </div>
      </div>
      <div class="row g-4">
        <?php foreach($guards as $guard): ?>
        <div class="col-xl-3 col-md-6">
          <div class="card h-100">
            <img src="<?php echo img_src($guard['img']) ?>" class="card-img-top" alt="<?php echo $guard['title'] ?>" loading="lazy">
            <div class="card-body p-4">
              <h5 class="card-title"><?php echo $guard['title'] ?></h5>
              <p class="card-text"><?php echo $guard['text'] ?></p>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <!-- ./In-Line -->
