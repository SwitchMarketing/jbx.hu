<!-- Footer -->
<footer class="footer-style-one">    
    <div class="footer-p-2 pb-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-sm-12">
                    <div class="footer-col">
                        <img src="<?php echo img_src('jbx-logo-w.svg') ?>" alt="JBX" class="img-fluid logo mb-4" loading="lazy">
                        <p>A JBX Trade Kft. az Axelent kizárólagos magyarországi képviselete, amely azzal a céllal jött létre, hogy dedikált lokális támogatást, gyors, és gördülékeny beszerzést tudjon nyújtani - mindezt magyar nyelven.</p>
                        <div class="badge mt-4">
                            <img src="<?php echo img_src('certified_retailer_2023.png') ?>" alt="Hivatalos Forgalmazó" title="Hivatalos Forgalmazó" loading="lazy" class="img-fluid" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Hivatalos Forgalmazó">
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 col-sm-12">
                    <div class="footer-col">
                        <h3>Kapcsolat</h3>
                        <ul>
                            <li>
                                <i class="fa-solid fa-building"></i>
                                <p><?php echo config('Config\\AppConfig')->companyName ?></p>
                            </li>
                            <li>
                                <i class="fa-solid fa-location-dot"></i>
                                <p><?php echo config('Config\\AppConfig')->companyAddress ?></p>
                            </li>
                            <li>
                                <i class="fa-solid fa-mobile-screen"></i>
                                <p><a href="tel:<?php echo default_phone_number(true) ?>"><?php echo default_phone_number() ?></a></p>
                            </li>
                            <li>
                                <i class="fa-solid fa-envelope"></i>
                                <p><a href="mailto:<?php echo config('Config\\AppConfig')->siteEmail ?>"><?php echo config('Config\\AppConfig')->siteEmail ?></a></p>
                            </li>
                            <li>
                                <i class="fa-solid fa-hashtag"></i>
                                <p>Adószám: <?php echo config('Config\\AppConfig')->companyTaxId ?></p>
                            </li>
                            <li>
                                <i class="fa-solid fa-hashtag"></i>
                                <p>Cégjegyzékszám: <?php echo config('Config\\AppConfig')->companyRegNo ?></p>
                            </li>
                        </ul>
                    </div>
                    <ul class="nav legal mt-4 mb-0 flex-column flex-md-row text-center">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo base_url('adatkezeles') ?>">Adatkezelés</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo base_url('sutikezeles') ?>">Sütikezelés</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo base_url('erintettseg') ?>">Érintettség</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo base_url('impresszum') ?>">Impresszum</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-p-3 rights">
        <div class="container">
            <div class="row">
                <div class="footer-col">
                    <p>JBX Magyarország | With <i class="fa-solid fa-heart"></i> by <a href="https://switchmarketing.hu/" target="_blank"> switchmarketing.hu</a></p>
                    <div class="social-medias">
                        <a href="<?php echo config('Config\\AppConfig')->socialLinkFacebook ?>" target="_blank">Facebook</a>
                        <a href="<?php echo config('Config\\AppConfig')->socialLinkInstagram ?>" target="_blank">Instagram</a>
                        <a href="<?php echo config('Config\\AppConfig')->socialLinkLinkedIn ?>" target="_blank">LinkedIn</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- ./Footer -->
<a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#contactModal" class="theme-btn btn-bottom-cta hidden" id="btn-bottom-cta"><span>Ajánlatkérés</span> <i class="fa-solid fa-envelope"></i></a>
<?php echo $this->include('modals'); ?>
<?php echo $js ?>
</body>

</html>