<?php
$dimensions = [
    ['label' => 'Panelszélesség',                           'value' => '1000 vagy 2000 mm; 20 mm-es lépésekben méretre szabható'],
    ['label' => 'Raktárról elérhető magasság',              'value' => '2000 vagy 2400 mm'],
    ['label' => 'Szabványos rendszer lehetséges magassága', 'value' => 'legfeljebb 3000 mm'],
    ['label' => 'Háló szélessége',                          'value' => '923 vagy 1923 mm'],
    ['label' => 'Háló magassága',                           'value' => '1870 vagy 2280 mm'],
    ['label' => 'Oszlop alatti szabad magasság',            'value' => '130 mm']
];
?>
<!-- Méretek -->
<section class="gap key-benefits ntf-dimensions">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="data">
                    <figure class="ntf-figure">
                        <img class="w-100" src="<?php echo img_src('ntf-protect/keritesmodulok-meretek.webp') ?>" alt="Különböző méretű kerítésmodulok" loading="lazy">
                    </figure>
                </div>
            </div>
            <div class="col-lg-6 mt-4 mt-lg-0">
                <div class="data">
                    <div class="heading px-0 mb-4">
                        <span class="d-block text-start">Különböző méretű kerítésmodulok</span>
                        <h2 class="text-start w-100">Egyszerű szerelés és szabványos méretek</h2>
                    </div>
                    <p class="mb-4">A kerítésmodulok a rendelkezésre álló helyhez és a szükséges védelmi magassághoz igazíthatók.</p>
                    <dl class="ntf-specs">
                        <?php foreach($dimensions as $row): ?>
                        <div>
                            <dt><?php echo $row['label'] ?></dt>
                            <dd><?php echo $row['value'] ?></dd>
                        </div>
                        <?php endforeach; ?>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- ./Méretek -->
