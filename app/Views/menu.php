<!-- Menü -->
<header class="header-style-one">
    <div class="container">
        <div class="row">
            <div class="desktop-nav" id="stickyHeader">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="d-flex-all justify-content-between">
                                <div class="header-logo">
                                    <a href="<?php echo base_url() ?>">
                                        <figure>
                                            <img src="<?php echo img_src('jbx-logo-w.svg') ?>" alt="<?php echo $title ?>" loading="lazy">
                                        </figure>
                                    </a>
                                    <div class="badge">
                                        <img src="<?php echo img_src('certified_retailer_2023.png') ?>" alt="Hivatalos Forgalmazó" loading="lazy" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Hivatalos Forgalmazó">
                                    </div>
                                </div>
                                <div class="nav-bar">
                                    <ul>
                                        <li class="d-none">
                                            <a href="<?php echo base_url() ?>">Főoldal</a>
                                        </li>
                                        <li class="menu-item-has-children">
                                            <a href="javascript:void(0)">Termékek</a>
                                            <ul class="sub-menu">
                                                <li><a href="<?php echo base_url('gepbiztonsagi-kerites') ?>">Gépbiztonsági kerítés</a></li>
                                                <li><a href="<?php echo base_url('kabeltalca-megoldasok') ?>">Kábeltálca megoldások</a></li>
                                                <li><a href="<?php echo base_url('utkozesvedelem') ?>">Ütközésvédelem</a></li>
                                                <li><a href="<?php echo base_url('raktarbiztonsagi-megoldasok') ?>">Raktárbiztonsági megoldások</a></li>
                                                <li><a href="<?php echo base_url('ingatlan-megoldasok') ?>">Ingatlan megoldások</a></li>
                                            </ul>
                                        </li>
                                        <li class="menu-item-has-children">
                                            <a href="javascript:void(0)">Eszközök</a>
                                            <ul class="sub-menu">
                                                <li><a href="<?php echo base_url('#axelent-safety-design') ?>">AXELENT Safety Design</a></li>
                                                <li><a href="<?php echo base_url('#snapperworks"') ?>">SnapperWorks</a></li>
                                                <li><a href="<?php echo base_url('#axelent-xperience') ?>">Axelent Xperience</a></li>
                                            </ul>
                                        </li>
                                        <li class="menu-item-has-children">
                                            <a href="javascript:void(0)">Rólunk</a>
                                            <ul class="sub-menu">
                                                <li><a href="<?php echo base_url('#hogyan-dolgozunk') ?>">Hogyan dolgozunk</a></li>
                                                <li><a href="<?php echo base_url('bemutatkozo') ?>">Bemutatkozó</a></li>
                                            </ul>
                                        </li>
                                        <li>
                                            <a href="<?php echo base_url('kapcsolat') ?>">Kapcsolat</a>
                                        </li>
                                    </ul>

                                    <div class="extras">
                                        <div class="theme-color me-md-4">
                                            <img src="<?php echo img_src('sun.png') ?>" alt="" id="theme-icon" loading="lazy">
                                        </div>
                                        <a href="javascript:void(0)" id="mobile-menu" class="menu-start">
                                            <svg id="ham-menu" viewBox="0 0 100 100"> <path class="line line1" d="M 20,29.000046 H 80.000231 C 80.000231,29.000046 94.498839,28.817352 94.532987,66.711331 94.543142,77.980673 90.966081,81.670246 85.259173,81.668997 79.552261,81.667751 75.000211,74.999942 75.000211,74.999942 L 25.000021,25.000058" /> <path class="line line2" d="M 20,50 H 80" /> <path class="line line3" d="M 20,70.999954 H 80.000231 C 80.000231,70.999954 94.498839,71.182648 94.532987,33.288669 94.543142,22.019327 90.966081,18.329754 85.259173,18.331003 79.552261,18.332249 75.000211,25.000058 75.000211,25.000058 L 25.000021,74.999942" /> </svg>
                                        </a>
                                        <a href="tel:<?php echo default_phone_number(true) ?>" class="theme-btn btn-light"><?php echo default_phone_number() ?>
                                            <i>
                                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="40" height="62" viewBox="0 0 40 62">
                                                    <defs>
                                                        <clipPath id="saddasdasdasdasda">
                                                            <rect width="40" height="62" />
                                                        </clipPath>
                                                    </defs>
                                                    <g id="Mobisdfle" clip-path="url(#saddasdasdasdasda)">
                                                        <path id="Path_125" data-name="Path 1" d="M10,6a4,4,0,0,0-4,4V50a4,4,0,0,0,4,4H28a4,4,0,0,0,4-4V10a4,4,0,0,0-4-4H10m0-6H28A10,10,0,0,1,38,10V50A10,10,0,0,1,28,60H10A10,10,0,0,1,0,50V10A10,10,0,0,1,10,0Z" transform="translate(1 1)" />
                                                        <path id="Path_4342" data-name="Path 2" d="M2.5,0h7a2.5,2.5,0,0,1,0,5h-7a2.5,2.5,0,0,1,0-5Z" transform="translate(14 48)" />
                                                    </g>
                                                </svg>
                                            </i>
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mobile-nav" id="mobile-nav">
                <div class="res-log">
                    <a href="<?php echo base_url() ?>">
                        <img src="<?php echo img_src('jbx-logo-w.svg') ?>" alt="<?php echo $title ?>" loading="lazy">
                    </a>
                </div>
                <ul>

                    <li class="menu-item-has-children active">
                        <a href="javascript:void(0)">Termékek</a>
                        <ul class="sub-menu">
                            <li><a href="<?php echo base_url('gepbiztonsagi-kerites') ?>">Gépbiztonsági kerítés</a></li>
                            <li><a href="<?php echo base_url('kabeltalca-megoldasok') ?>">Kábeltálca megoldások</a></li>
                            <li><a href="<?php echo base_url('utkozesvedelem') ?>">Ütközésvédelem</a></li>
                            <li><a href="<?php echo base_url('raktarbiztonsagi-megoldasok') ?>">Raktárbiztonsági megoldások</a></li>
                            <li><a href="<?php echo base_url('ingatlan-megoldasok') ?>">Ingatlan megoldások</a></li>
                        </ul>
                    </li>
                    <li class="menu-item-has-children active">
                        <a href="javascript:void(0)">Eszközök</a>
                        <ul class="sub-menu">
                            <li><a href="<?php echo base_url('#axelent-safety-design') ?>">AXELENT Safety Design</a></li>
                            <li><a href="<?php echo base_url('#snapperworks"') ?>">SnapperWorks</a></li>
                            <li><a href="<?php echo base_url('#axelent-xperience') ?>">Axelent Xperience</a></li>
                        </ul>
                    </li>
                    <li class="menu-item-has-children active">
                        <a href="javascript:void(0)">Rólunk</a>
                        <ul class="sub-menu">
                            <li><a href="<?php echo base_url('#hogyan-dolgozunk') ?>">Hogyan dolgozunk</a></li>
                            <li><a href="<?php echo base_url('bemutatkozo') ?>">Bemutatkozó</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="<?php echo base_url('kapcsolat') ?>">Kapcsolat</a>
                    </li>
                </ul>
                <a href="JavaScript:void(0)" id="res-cross"></a>
            </div>            
        </div>
    </div>
</header>
<!-- ./Menü -->