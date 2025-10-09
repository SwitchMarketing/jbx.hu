Megrendelés<br><br>

<p>Név: <br><?php echo $name ?></p>
<p>Email: <br><?php echo $email ?></p>
<p>Telefon: <br><?php echo $phone ?></p>
<p>Cégnév: <br><?php echo $company ?? '' ?></p>
<p>Számlázási cím: <br><?php echo $billing_zip ?? '' ?> <?php echo $billing_state ?? '' ?>, <?php echo $billing_address ?? '' ?></p>
<?php if(isset($diffDeliveryAddress)): ?>
<p>Szállítási cím: <br><?php echo $delivery_zip ?? '' ?> <?php echo $delivery_state ?? '' ?>, <?php echo $delivery_address ?? '' ?></p>
<?php endif; ?>
<p>Megjegyzés: <br><?php echo nl2br($comments) ?></p>

<h3>Megrendelt termékek:</h3>
<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif;">
    <thead>
        <tr>
            <th align="left">Megnevezés</th>
            <th align="center">SKU</th>
            <th align="right">Egységár</th>
            <th align="center">Mennyiség</th>
            <th align="right">Részösszeg</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($products as $p): ?>
        <tr>
            <td><?php echo $p->name ?></td>
            <td align="center"><?php echo $p->sku ?></td>
            <td align="right"><?php echo $p->price ? format_price((int)$p->price) : '-' ?></td>
            <td align="center"><?php echo $p->qty ?></td>
            <td align="right"><?php echo ($p->price && $p->qty) ? format_price($p->price * $p->qty) : $p->status ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>