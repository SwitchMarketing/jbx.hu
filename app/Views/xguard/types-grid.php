  <!-- Types -->
  <section class="gap core-values">
      <div class="heading">
          <figure>
              <img src="<?php echo img_src('logo-axelent.svg') ?>" alt="AXELENT Safety Design" loading="lazy">
          </figure>
          <span>X-Guard - Hálós Panelek</span>
          <h2>Biztonsági kerítés típusok</h2>
      </div>
      <?php if(isset($types)): ?>
      <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="types-grid">

                    <div class="types-grid--col types-grid--legend">
                        <div class="types-grid--row title">&nbsp;</div>
                        <?php foreach($types->legend['rows'] as $row): ?>
                            <div class="types-grid--row">
                                <?php echo $row ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="types-grid--col types-grid--type">
                        <div class="types-grid--row image mb-3"><img src="<?php echo $types->lite['img'] ?>" alt="<?php echo $types->lite['title'] ?>" class="img-fluid"></div>
                        <div class="types-grid--row title"><?php echo $types->lite['title'] ?></div>
                        <div class="types-grid--row description my-4"><?php echo $types->lite['description'] ?></div>
                        <?php foreach($types->lite['rows'] as $row): ?>
                            <div class="types-grid--row">
                                <span class="label"><?php echo $row['label'] ?></span>
                                <span><?php echo $row['value'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="types-grid--col types-grid--type">
                        <div class="types-grid--row image mt-3"><img src="<?php echo $types->classic['img'] ?>" alt="<?php echo $types->classic['title'] ?>" class="img-fluid"></div>
                        <div class="types-grid--row title"><?php echo $types->classic['title'] ?></div>
                        <div class="types-grid--row description my-4"><?php echo $types->classic['description'] ?></div>
                        <?php foreach($types->classic['rows'] as $row): ?>
                            <div class="types-grid--row">
                                <span class="label"><?php echo $row['label'] ?></span>
                                <span><?php echo $row['value'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="types-grid--col types-grid--type">
                        <div class="types-grid--row image mt-3 mb-3"><img src="<?php echo $types->premium['img'] ?>" alt="<?php echo $types->premium['title'] ?>" class="img-fluid"></div>
                        <div class="types-grid--row title"><?php echo $types->premium['title'] ?></div>
                        <div class="types-grid--row description my-4"><?php echo $types->premium['description'] ?></div>
                        <?php foreach($types->premium['rows'] as $row): ?>
                            <div class="types-grid--row">
                                <span class="label"><?php echo $row['label'] ?></span>
                                <span><?php echo $row['value'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>
        </div>
      </div>
      <?php endif; ?>
  </section>
  <!-- ./Types -->