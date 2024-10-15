<?php if(isset($pdfs) && !empty($pdfs)): ?>
<div class="downloads">
    <div class="container">
        <div>
            <figure>
                <img src="<?php echo img_src('pdf.svg')?>" alt="Letöltések">
            </figure>
        </div>
        <div>
            <h3 class="mb-4 mb-lg-2">Letöltések</h3>
            <ul>
                <?php foreach($pdfs as $pdf): ?>
                <li><a href="<?php echo $pdf['url'] ?>" target="_blank"><i class="fas fa-download"></i> <?php echo $pdf['label'] ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<?php endif; ?>