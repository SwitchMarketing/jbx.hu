<?php if (!empty($options)): ?>
    <?php $optionMatrixJson = json_encode($optionMatrix ?? [], JSON_UNESCAPED_UNICODE); ?>
    <?php
        $optionDefinitions = [];
        foreach ($options as $optionId => $optionData) {
            $optionDefinitions[(string) $optionId] = [
                'name' => (string) ($optionData['name'] ?? ''),
            ];
        }
        $optionDefinitionsJson = json_encode($optionDefinitions, JSON_UNESCAPED_UNICODE);
    ?>
    
    <div class="option-pills-container d-flex flex-column" data-master-slug="<?php echo esc($masterSlug ?? ''); ?>" data-option-matrix="<?php echo esc($optionMatrixJson ?: '[]', 'attr'); ?>" data-option-definitions="<?php echo esc($optionDefinitionsJson ?: '{}', 'attr'); ?>">
        <?php foreach($options as $k => $option): ?>
            <?php
                $sortedValues = is_array($option['values'] ?? null) ? $option['values'] : [];
                usort($sortedValues, static function($a, $b) {
                    $valueA = (string) ($a['value'] ?? '');
                    $valueB = (string) ($b['value'] ?? '');
                    return strnatcasecmp($valueA, $valueB);
                });
                $isSingleValue = count($sortedValues) <= 1;
            ?>
            <div class="option-pills-group" data-option-id="<?php echo esc($k); ?>">
                <div class="option-pills-title"><?php echo esc($option['name']); ?>:</div>
                <select class="option-select <?php echo $isSingleValue ? 'option-select--single' : ''; ?>" data-option-id="<?php echo esc($k); ?>" data-option-name="<?php echo esc($option['name']); ?>" onchange="App.productOptionSelect(this)" <?php echo $isSingleValue ? 'disabled aria-disabled="true" title="Ehhez a paraméterhez csak egy opció érhető el."' : ''; ?>>
                    <?php foreach($sortedValues as $val): ?>
                        <option value="<?php echo esc($val['value']); ?>" <?php echo (isset($val['active']) && $val['active']) ? 'selected' : ''; ?>>
                            <?php echo esc($val['value']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        <?php endforeach; ?>

        <div class="option-combinations" aria-live="polite">
            <div class="option-combinations-title">Elérhető opciók a jelenlegi választás alapján</div>
            <div class="option-combinations-list"></div>
        </div>
    </div>
    
<?php endif; ?>