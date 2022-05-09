<?php include 'header.php'; ?>
<section class="probootstrap-section">
    <div class="container">
        <?php if (array_key_exists('featuredImage', $page["content"])) { ?>
        <div class="row">
            <div class="col-md-12">
                <p><img src="<?php echo BASEPATH; ?>/uploads/<?php echo $page["content"]["featuredImage"]; ?>" class="img-responsive"></p>
            </div>
        </div>
        <?php }; ?>
        <div class="row">
            <?php if (array_key_exists('secondaryImage', $page["content"])) { ?>
            <div class="col-12 col-md-5">
                <img src="<?php echo BASEPATH; ?>/uploads/<?php echo $page["content"]["secondaryImage"]; ?>" class="img-responsive">
            </div>
            <?php }; ?>
            <div class="col-5 col-md-7">
                <h2 style="margin-top: 0.5rem;"><?php echo $page["title"]; ?></h2>
                <?php echo $page["content"]["pageContent"]; ?>
            </div>
        </div>
    </div>
</section>
<!-- END section -->
<?php include 'footer.php'; ?>