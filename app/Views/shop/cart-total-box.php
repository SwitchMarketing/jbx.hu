<div class="cart-total-box w-100 mb-4">
<div class="parallax" style="background-image: url(/imgs/pattren-4.png);"></div>
<div class="final">
    <h4>Összesítés</h4>
    <ul>
        <li>
            <span>Nettó:</span>
            <span><?php echo format_price($cartNetTotal) ?></span>
        </li>
        <li>
            <span>ÁFA (<?php echo number_format((float)($vatRatePercent ?? 0), 0, '', ' ') ?>%):</span>
            <span><?php echo format_price($cartVat) ?></span>
        </li>
    </ul>
</div>
<div class="total">
    <ul>
        <li>
            <span>Összesen:</span>
            <span><?php echo format_price($cartTotal) ?></span>
        </li>
    </ul>
</div>
</div>