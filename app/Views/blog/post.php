<section class="gap blog-style-one blog-style-one blog-detail">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <?php if (!empty($post) && is_object($post)) : ?>
                    <div class="blog-post">
                        <div class="blog-image">
                            <figure>
                                <a href="<?php echo base_url('blog/' . $post->slug); ?>">
                                    <img src="<?php echo $post->image ?? '' ?>" alt="<?php echo $post->title; ?>">
                                </a>
                            </figure>
                        </div>
                        <div class="blog-data">
                            <span class="blog-date"><?php echo $post->published_at ?></span>
                            <h2>
                                <a href="javascript:void(0)"><?php echo $post->title; ?></a>
                            </h2>
                        </div>
                        <div class="blog-text">
                            <?php echo $post->content; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-4">
                <aside class="sidebar">
                    <div class="box recent-posts">
                        <h3>Legfrissebb bejegyzések</h3>
                        <?php if (!empty($latestPosts) && is_array($latestPosts)) : ?>
                            <ul>
                                <?php foreach ($latestPosts as $post) : ?>
                                    <li>
                                        <p><a href="<?php echo base_url('blog/' . $post->slug); ?>"><?php echo $post->title ?></a></p>
                                        <a href="<?php echo base_url('blog/' . $post->slug); ?>" title="<?php echo $post->title; ?>">
                                            <i class="fa-solid fa-arrow-up-long"></i>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</section>