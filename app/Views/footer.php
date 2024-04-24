<!-- Footer -->
<footer class="footer-style-one">
<div class="footer-p-2">
    <div class="container">
    <div class="row">
        <div class="col-lg-7 col-md-6 col-sm-12">
            <div class="footer-col">
                <img src="<?php echo img_src('jbx-logo-w.svg') ?>" alt="JBX" class="img-fluid logo mb-4" loading="lazy">
                <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Amet nobis hic iste officia exercitationem earum nam atque fugiat quam, minus vel accusantium eum maxime nemo eius quo, saepe dignissimos. Ullam?</p>
            </div>
        </div>
        <div class="col-lg-5 col-md-6 col-sm-12">
            <div class="footer-col">
                <h3>Kapcsolat</h3>
                <ul>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="79" height="94" viewBox="0 0 79 94"> <defs> <clipPath id="clip-location_B"> <rect width="79" height="94"/> </clipPath> </defs> <g id="location_B" data-name="location B" clip-path="url(#clip-location_B)"> <path id="Path_121" data-name="Path 1" d="M962.855,575.375a3,3,0,0,1-2.1-.861l-26.263-25.826c-11.03-11.993-13.791-27.653-7.492-42a38.334,38.334,0,0,1,34.959-23.117l1.346.009c15.262,0,27.868,8.452,33.722,22.609,6.152,14.878,3.046,31.554-7.912,42.485-.528.555-24.064,25.75-24.064,25.75a3,3,0,0,1-2.129.951Zm-.9-85.8A31.924,31.924,0,0,0,932.49,509.1c-5.313,12.1-2.954,25.342,6.31,35.419l23.963,23.559c15.027-16.085,20.179-21.585,22.274-23.488l-.164-.165c9.233-9.209,11.825-23.318,6.605-35.944a29.677,29.677,0,0,0-28.177-18.9Z" transform="translate(-922.725 -482.15)"/> <path id="Path_23" data-name="Path 2" d="M15,6a9,9,0,1,0,9,9,9.01,9.01,0,0,0-9-9m0-6A15,15,0,1,1,0,15,15,15,0,0,1,15,0Z" transform="translate(25 26)"/> </g> </svg>
                    <p><?php echo config( 'Config\\AppConfig' )->companyAddress ?></p>
                </li>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="40" height="62" viewBox="0 0 40 62"> <defs> <clipPath id="dasdasdasd"> <rect width="40" height="62"/> </clipPath> </defs> <g id="Mobsdfsdfsdfsdfile" clip-path="url(#dasdasdasd)"> <path id="Path_331" data-name="Path 1" d="M10,6a4,4,0,0,0-4,4V50a4,4,0,0,0,4,4H28a4,4,0,0,0,4-4V10a4,4,0,0,0-4-4H10m0-6H28A10,10,0,0,1,38,10V50A10,10,0,0,1,28,60H10A10,10,0,0,1,0,50V10A10,10,0,0,1,10,0Z" transform="translate(1 1)"/> <path id="Path_2" data-name="Path 2" d="M2.5,0h7a2.5,2.5,0,0,1,0,5h-7a2.5,2.5,0,0,1,0-5Z" transform="translate(14 48)"/> </g> </svg>
                    <p><a href="tel:<?php echo default_phone_number(true) ?>"><?php echo default_phone_number() ?></a></p>
                </li>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="102" height="93" viewBox="0 0 102 93"> <defs> <clipPath id="clip-Email_B"> <rect width="102" height="93"/> </clipPath> </defs> <g id="Email_B" data-name="Email B" clip-path="url(#clip-Email_B)"> <path id="Path_1444" data-name="Path 1" d="M969.85,550.4,927.766,528.2l2.8-5.307,39.229,20.7,37.712-20.677,2.885,5.261Z" transform="translate(-918 -492)"/> <path id="Path_24" data-name="Path 2" d="M969.562,494.385l48.391,25.361,0,1.818c-.023,17.272-.043,42.814-.012,47.124l.012.024v.709c0,5.426-1.516,9.425-4.508,11.885a10.4,10.4,0,0,1-6.575,2.344l-75.5-.016c-3.557.071-5.965-.931-7.717-2.752-2.4-2.5-3.517-6.391-3.317-11.577l.065-1.194c.116-5.315.029-29.954-.067-46.535l-.011-1.842Zm42.386,28.988-42.411-22.227-43.2,22.238c.189,32.939.239,42.8-.143,46.148l.13.005c-.168,4.351.8,6.309,1.645,7.185a3.342,3.342,0,0,0,2.458.984l76.043-.071a4.65,4.65,0,0,0,3.16-.963c1.517-1.248,2.319-3.754,2.319-7.25h.09C1011.893,566.689,1011.9,557.566,1011.947,523.373Z" transform="translate(-918 -492)"/> </g> </svg>
                    <p><a href="mailto:<?php echo config( 'Config\\AppConfig' )->siteEmail ?>"><?php echo config( 'Config\\AppConfig' )->siteEmail ?></a></p>
                </li>
                </ul>
            </div>
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
            <a href="javascript:void(0)">Facebook</a>
            <a href="javascript:void(0)">Twitter</a>
            <a href="javascript:void(0)">Linkedin</a>
        </div>
        </div>
    </div>
    </div>
</div>
</footer>
<!-- ./Footer -->
<button id="scrollTop" class="scrollTopStick">
    <i class="fa-solid fa-arrow-up"></i>
</button>
<?php echo $this->include('modals'); ?>
<?php echo $js ?>
</body>
</html>