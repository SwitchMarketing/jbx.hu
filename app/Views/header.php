<!DOCTYPE html>
<html lang="<?= $locale ?>">
<head>
<base href="<?=$base?>">
<title><?=$title?></title>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="<?=$desc?>">
<meta name="language" content="<?= $locale ?>">
<?php echo csrf_meta().PHP_EOL ?>
<meta property="og:url" content="<?=$og_url?>">
<meta property="og:title" content="<?=$og_title?>">
<meta property="og:description" content="<?=$og_desc?>">
<meta property="og:image" content="<?=$og_img?>">
<meta property="og:type" content="website">
<link rel="canonical" href="<?=$og_url?>">
<?php echo view('inc/verify') ?>
<meta name="msapplication-TileColor" content="#da532c">
<meta name="theme-color" content="#ffffff">
<link rel="apple-touch-icon" sizes="180x180" href="/imgs/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/imgs/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/imgs/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
<link rel="mask-icon" href="/imgs/safari-pinned-tab.svg" color="#5bbad5">
<!-- CSS -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800;900&Poppins:wght@100;400;900&display=swap" rel="stylesheet">
<?php echo $css ?>
<?php echo view('inc/gtm-head') ?>
<?php echo view('inc/fbpixel-head') ?>
</head>
<body class="<?=$section,' ',$devicetype?>">
<?php echo view('inc/gtm-body') ?>
<?php echo view('inc/fbpixel-body') ?>
<!-- Loader Start -->
<div class="preloader" id="preloader"> 
    <figure>
      <img src="<?php echo img_src('jbx-logo-no-text.svg')?> " alt="<?php echo $title ?>"> 
    </figure>
  </div>
<!-- Loader End -->
<?php echo $this->include('menu') ?>