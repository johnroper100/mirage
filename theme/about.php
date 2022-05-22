<?php include 'header.php'; ?>
<section class="probootstrap-section">
    <div class="container">
        <?php if (array_key_exists('featuredImage', $page["content"])) { ?>
        <div class="row">
            <div class="col-md-12">
                <?php $imageDetails = getMedia($page["content"]["featuredImage"]); ?>
                <p><img src="<?php echo BASEPATH; ?>/uploads/<?php echo $imageDetails["file"]; ?>" class="img-responsive"></p>
            </div>
        </div>
        <?php }; ?>
        <div class="row">
            <?php if (array_key_exists('secondaryImage', $page["content"])) { ?>
            <div class="col-12 col-md-5">
                <?php $imageDetails = getMedia($page["content"]["secondaryImage"]); ?>
                <img src="<?php echo BASEPATH; ?>/uploads/<?php echo $imageDetails["file"]; ?>" class="img-responsive">
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