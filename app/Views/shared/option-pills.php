<?php if (!empty($options)): ?>
    <?php $optionMatrixJson = json_encode($optionMatrix ?? [], JSON_UNESCAPED_UNICODE); ?>
    
    <div class="option-pills-container mb-3" data-master-slug="<?php echo esc($masterSlug ?? ''); ?>" data-option-matrix="<?php echo esc($optionMatrixJson ?: '[]', 'attr'); ?>">
        <?php foreach($options as $k => $option): ?>

            <div class="option-pills-group" data-option-id="<?php echo esc($k); ?>">
                <div class="option-pills-title"><?php echo esc($option['name']); ?>:</div>
                <div class="option-pills">
                    <?php foreach($option['values'] as $val): ?>
                        <button type="button" class="option-pill <?php echo (isset($val['active']) && $val['active']) ? 'active' : ''; ?>" 
                            data-option-id="<?php echo esc($k); ?>"
                            data-option-value="<?php echo esc($val['value']); ?>" 
                            onclick="App.productOption(this)">
                            <?php echo esc($val['value']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php endforeach; ?>
    </div>
    
<?php endif; ?>