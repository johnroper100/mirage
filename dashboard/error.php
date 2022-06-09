<?php include 'header.php'; ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-6 text-center">
            <h1 class="text-danger display-1 mt-5">Code <?php echo $errorCode; ?></h1>
            <h2>Oh no, there's been an error!</h2>
            <h4 class="text-secondary"><?php echo $errorMessage; ?></h4>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>