<?php if (!empty($options)): ?>
    <?php $optionMatrixJson = json_encode($optionMatrix ?? [], JSON_UNESCAPED_UNICODE); ?>
    
    <div class="option-pills-container mb-3" data-master-slug="<?php echo esc($masterSlug ?? ''); ?>" data-option-matrix="<?php echo esc($optionMatrixJson ?: '[]', 'attr'); ?>">
        <?php foreach($options as $k => $option): ?>
            <?php
                $sortedValues = is_array($option['values'] ?? null) ? $option['values'] : [];
                usort($sortedValues, static function($a, $b) {
                    $valueA = (string) ($a['value'] ?? '');
                    $valueB = (string) ($b['value'] ?? '');
                    return strnatcasecmp($valueA, $valueB);
                });
            ?>

            <div class="option-pills-group" data-option-id="<?php echo esc($k); ?>">
                <div class="option-pills-title"><?php echo esc($option['name']); ?>:</div>
                <div class="option-pills-wrap">
                    <div class="option-pills">
                        <?php foreach($sortedValues as $val): ?>
                            <button type="button" class="option-pill <?php echo (isset($val['active']) && $val['active']) ? 'active' : ''; ?>" 
                                data-option-id="<?php echo esc($k); ?>"
                                data-option-value="<?php echo esc($val['value']); ?>" 
                                onclick="App.productOption(this)">
                                <?php echo esc($val['value']); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>

        <div class="option-pills-legend" aria-label="Opció színmagyarázat">
            <span class="option-pills-legend-item">
                <span class="option-pills-legend-swatch is-default" aria-hidden="true"></span>
                Elérhető opció
            </span>
            <span class="option-pills-legend-item">
                <span class="option-pills-legend-swatch is-active" aria-hidden="true"></span>
                Kiválasztott opció
            </span>
            <span class="option-pills-legend-item">
                <span class="option-pills-legend-swatch is-partial" aria-hidden="true"></span>
                Részben kompatibilis opció
            </span>
            <span class="option-pills-legend-item">
                <span class="option-pills-legend-swatch is-unavailable" aria-hidden="true"></span>
                Nem elérhető opció
            </span>
        </div>
    </div>
    
<?php endif; ?>