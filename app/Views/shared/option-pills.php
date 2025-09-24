<?php if (!empty($options)): ?>
    
    <div class="option-pills-container mb-3">
        <?php foreach($options as $k => $option): ?>

            <div class="option-pills-group" data-option-id="<?php echo esc($k); ?>">
                <div class="option-pills-title"><?php echo esc($option['name']); ?>:</div>
                <div class="option-pills">
                    <?php foreach($option['values'] as $val): ?>
                        <button type="button" class="option-pill <?php echo (isset($val['active']) && $val['active']) ? 'active' : ''; ?>" 
                            data-sku="<?php echo esc($product->sku ?? ''); ?>"
                            data-option-id="<?php echo esc($k); ?>"
                            data-option-value="<?php echo esc($val['value']); ?>" 
                            data-slug="<?php echo esc($val['slug']); ?>" onclick="App.productOption(this)">
                            <?php echo esc($val['value']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php endforeach; ?>
    </div>
    
<?php endif; ?>