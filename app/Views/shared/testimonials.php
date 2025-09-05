<section class="gap client-review-style-one">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="head-review">
                    <span>Ügyfél</span>
                    <h3>Visszajelzések</h3>
                </div>
                <div class="client-review-slider owl-carousel">
                    <?php $testimonials = client_testimonials(); foreach($testimonials as $testimonial): ?>
                    <div class="slider-data">
                        <p><?php echo $testimonial['testimonial']; ?></p>
                        <div class="bio d-flex-all justify-content-start w-100">
                            <div class="icon d-flex-all">
                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="26" height="26" viewBox="0 0 26 26">
                                    <defs>
                                        <clipPath id="clip-Inverted">
                                            <rect width="26" height="26" />
                                        </clipPath>
                                    </defs>
                                    <g id="Inverted_" data-name="Inverted commas flaky" clip-path="url(#clip-Inverted)">
                                        <path id="Path_3444" data-name="Path 3" d="M.032,24.036V14.478l-.032,0V8.991C.4.4,9.086,0,9.086,0V5.961c-3.535,0-3.555,3.03-3.555,3.03v4.045h5.5v11ZM0,8.991Z" transform="translate(14 0.964)" />
                                        <path id="Path_weee4" data-name="Path 4" d="M.032,24.036V14.478l-.032,0V8.991C.4.4,9.086,0,9.086,0V5.961c-3.535,0-3.555,3.03-3.555,3.03v4.045h5.5v11ZM0,8.991Z" transform="translate(0.969 0.964)" />
                                    </g>
                                </svg>
                            </div>
                            <div class="details w-100">
                                <h3><?php echo $testimonial['name']; ?></h3>
                                <p><?php echo $testimonial['position']; ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <figure>
                    <img src="<?php echo img_src('testimonials.png') ?>" alt="Ügyfél visszajelzések" class="img-fluid" loading="lazy">
                </figure>
            </div>
        </div>
    </div>
</section>