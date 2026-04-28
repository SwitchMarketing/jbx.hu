<main>
    <section class="gap general-terms">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h2>Általános Szerződési Feltételek</h2>
                    <p>Hatályos: 2026.04.28.-tól</p>

                    <h4>1. A szolgáltató adatai</h4>
                    <p><strong>Név:</strong> <?php echo config('Config\\AppConfig')->companyFullName ?></p>
                    <p><strong>Székhely:</strong> <?php echo config('Config\\AppConfig')->companyAddress ?></p>
                    <p><strong>Email:</strong> <a href="mailto:<?php echo config('Config\\AppConfig')->siteEmail ?>"><?php echo config('Config\\AppConfig')->siteEmail ?></a></p>

                    <h4>2. A megrendelés folyamata</h4>
                    <p>A weboldalon leadott megrendelés ajánlatkérésként és megrendelési szándékként kerül feldolgozásra. A megrendelés véglegesítése minden esetben egyedi visszaigazolás után történik.</p>
                    <p>A megrendelés beküldésével a vásárló kijelenti, hogy az általa megadott adatok valósak, és jogosult azok kezelésére.</p>

                    <h4>3. Ár és teljesítés</h4>
                    <p>A weboldalon megjelenő árak tájékoztató jellegűek. Az aktuális ár, elérhetőség és szállítási feltétel a visszaigazolásban kerül pontosításra.</p>
                    <p>A teljesítési határidő a termék típusától és elérhetőségétől függően változhat.</p>

                    <h4>4. Felelősség és jogi nyilatkozat</h4>
                    <p>A megrendelés leadásával a vásárló elfogadja jelen ÁSZF rendelkezéseit, valamint tudomásul veszi az adatkezelési tájékoztatóban foglaltakat.</p>

                    <h4>5. Kapcsolódó jogi dokumentumok</h4>
                    <ul>
                        <li><a href="<?php echo base_url('adatkezeles') ?>">Adatkezelési tájékoztató</a></li>
                        <li><a href="<?php echo base_url('sutikezeles') ?>">Sütikezelési tájékoztató</a></li>
                        <li><a href="<?php echo base_url('impresszum') ?>">Impresszum</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</main>
