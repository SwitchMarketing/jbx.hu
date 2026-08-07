Ajánlatkérés<br><br>

<p>Név: <br><?php echo $name ?></p>
<p>Cégnév: <br><?php echo $company_name ?? '' ?></p>
<p>Székhely: <br><?php echo $company_address ?? '' ?></p>
<p>Adószám: <br><?php echo $tax_number ?? '' ?></p>
<?php if (empty($company_name ?? '') && !empty($company ?? '')): ?>
<p>Céges adat: <br><?php echo $company ?></p>
<?php endif; ?>
<p>Email: <br><?php echo $email ?></p>
<p>Telefon: <br><?php echo $phone ?></p>
<p>Termékcsalád: <br><?php echo $products ?? '' ?></p>
<p>Megjegyzés: <br><?php echo nl2br($message) ?></p>
<p>Forrás: <br><?php echo $utm_source ?? '' ?></p>
