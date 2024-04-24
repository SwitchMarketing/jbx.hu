<?php if (! empty($errors)) : ?>
	<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<ul>
		<?php foreach ($errors as $error) : ?>
			<li><?php echo  $error ?></li>
		<?php endforeach ?>		
		</ul>
		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Bezár"></button>
	</div>
<?php endif ?>