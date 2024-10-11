<main>
<?php echo view('shared/page-header', ['title' => 'Bemutatkozó']); ?>
<?php 
echo view('about/about');
echo view('about/counter');
echo view('about/security');
echo $this->include('shared/exclusive');
echo $this->include('shared/products');
echo $this->include('shared/how-we-work');
echo $this->include('shared/contact-form');
?>
</main>