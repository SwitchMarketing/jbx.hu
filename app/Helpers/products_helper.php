<?php

/**
 * products
 *
 * termék opciók az űrlapon
 * 
 * @param  int $selected
 * @return array
 */
function product_options(int $selected = 0) : array {
    return [
        (object) [
            'id' => 1,
            'value' => 'X-Guard',
            'label' => 'X-Guard | Gépbiztonsági kerítés',
            'selected' => ($selected == 1)
        ],
        (object) [
            'id' => 2,
            'value' => 'Wire Tray',
            'label' => 'Wire Tray | Rácsos kábeltálca rendszerek',
            'selected' => ($selected == 2)
        ],
        (object) [
            'id' => 3,
            'value' => 'X-Protect',
            'label' => 'X-Protect | Ütközésvédelem',
            'selected' => ($selected == 3)
        ],
        (object) [
            'id' => 4,
            'value' => 'X-Store',
            'label' => 'X-Store | Raktárbiztonság',
            'selected' => ($selected == 4)
        ],
        (object) [
            'id' => 5,
            'value' => 'Ingatlan',
            'label' => 'Ingatlan megoldások',
            'selected' => ($selected == 5)
        ]
    ];
}