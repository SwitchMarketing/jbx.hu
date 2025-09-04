
<div class="blog-post">
    <div class="blog-image">
        <figure>
            <a href="<?php echo base_url('blog/' . $post->slug); ?>">
                <img src="<?php echo $post->image ?? '' ?>" alt="<?php echo $post->title; ?>">
            </a>
        </figure>
    </div>
    <div class="blog-data">
        <span class="blog-date"><?php echo $post->published_at; ?></span>
        <h2>
            <a href="<?php echo base_url('blog/' . $post->slug); ?>"><?php echo $post->title; ?></a>
        </h2>
        <p class="mt-2"><?php echo $post->excerpt; ?></p>
    </div>
</div>
