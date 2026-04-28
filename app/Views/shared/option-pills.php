<?php if (!empty($options)): ?>
    <?php $optionMatrixJson = json_encode($optionMatrix ?? [], JSON_UNESCAPED_UNICODE); ?>
    
    <div class="option-pills-container d-flex flex-column" data-master-slug="<?php echo esc($masterSlug ?? ''); ?>" data-option-matrix="<?php echo esc($optionMatrixJson ?: '[]', 'attr'); ?>">
        <?php foreach($options as $k => $option): ?>
            <div class="option-pills-group" data-option-id="<?php echo esc($k); ?>">
                <div class="option-pills-title"><?php echo esc($option['name']); ?>:</div>
                <select class="option-select" data-option-id="<?php echo esc($k); ?>" onchange="App.productOptionSelect(this)">
                    <?php foreach($option['values'] as $val): ?>
                        <option value="<?php echo esc($val['value']); ?>" <?php echo (isset($val['active']) && $val['active']) ? 'selected' : ''; ?>>
                            <?php echo esc($val['value']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        <?php endforeach; ?>
    </div>
    
<?php endif; ?>