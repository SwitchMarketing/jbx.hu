<?php
$doors = [
    [
        'title'  => 'Tolóajtó',
        'text'   => 'Nagy gördülőfelülete tartós és stabil működést biztosít. Egyszerű, masszív zárszerkezete hatékony zárást tesz lehetővé. Különféle biztonsági kapcsolókhoz illeszkedő konzolokkal szerelhető.',
        'widths' => ['1000', '1260', '1500']
    ],
    [
        'title'  => 'Egyszárnyú ajtó',
        'text'   => 'Balos vagy jobbos kivitelben választható. Egyszerű húzófogantyúval és bepattanó zárral készül; többféle biztonsági kapcsolóhoz illeszkedő konzollal egészíthető ki.',
        'widths' => ['800', '1000', '1260', '1500']
    ],
    [
        'title'  => 'Multi-Lock Pro ajtó',
        'text'   => 'Háromféle zárási lehetőség közül választható: mágneszár, kulcsos zár vagy bepattanó zárkészlet. Profilvázas ajtó, amely biztonsági kapcsolókhoz illeszkedő konzolokkal is felszerelhető.',
        'widths' => ['800', '1000', '1260', '1500']
    ],
    [
        'title'  => 'Kétszárnyú ajtó',
        'text'   => 'Szélesebb nyílást biztosít, az egyszerű hozzáférést egyetlen tolóretesz segíti. Biztonsági kapcsolókhoz illeszkedő konzolokkal szerelhető.',
        'widths' => []
    ]
];
?>
  <!-- Doors -->
  <section class="gap ntf-doors">
    <div class="heading">
      <span>Ajtóval szerelt kerítés</span>
      <h2 class="mb-3">Ajtótípusok</h2>
    </div>
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <h4 class="mb-5 text-center fw-normal">Védett elhatárolás, amely hozzáférést biztosít a gépekhez.</h4>
        </div>
      </div>
      <div class="row g-4">
        <?php foreach($doors as $door): ?>
        <div class="col-xl-3 col-md-6">
          <div class="ntf-door">
            <h3><?php echo $door['title'] ?></h3>
            <p><?php echo $door['text'] ?></p>
            <div class="ntf-door--widths">
              <span class="label">Elérhető szélességek</span>
              <?php if(!empty($door['widths'])): ?>
              <ul>
                <?php foreach($door['widths'] as $width): ?>
                <li><?php echo $width ?> mm</li>
                <?php endforeach; ?>
              </ul>
              <?php else: ?>
              <ul>
                <li>Egyedi igény szerint</li>
              </ul>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <!-- ./Doors -->
