<section class="gap blog-style-one blog-style-three">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <?php if (!empty($posts) && is_array($posts)) :
                    foreach ($posts as $post) :
                        echo view('blog/list-item', ['post' => $post]);
                    endforeach;
                endif; ?>
            </div>
            <div class="col-lg-4">
                <aside class="sidebar">
                    <div class="box recent-posts">
                        <h3>Legfrissebb bejegyzések</h3>
                        <?php if(!empty($latestPosts) && is_array($latestPosts)) : ?>
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
    <div class="container">
        <div class="row">
            <?php echo $links; ?>
        </div>
    </div>
</section>