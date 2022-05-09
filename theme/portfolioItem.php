<?php include 'header.php'; ?>
<div class="probootstrap-section">
    <div class="container">
        <div class="row probootstrap-gutter16">
            <div class="col-12">
                <div class="img-bg" style="background-image: url(<?php echo BASEPATH; ?>/uploads/<?php echo $page["content"]["featuredImage"]; ?>);"></div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-md-offset-3 text-center">
                <h2><?php echo $page["title"]; ?></h2>
                <p><?php echo $page["content"]["headerSubtitle"]; ?></p>
            </div>
            <div class="col-md-12">
                <?php echo $page["content"]["pageContent"]; ?>
            </div>
        </div>
        <div class="row">
            <?php foreach ($page["content"]["additionalImages"] as $imageItem) { ?>
                <div class="col-md-6">
                    <p><a href="<?php echo BASEPATH; ?>/uploads/<?php echo $imageItem["image"]; ?>" class="image-popup"><img src="<?php echo BASEPATH; ?>/uploads/<?php echo $imageItem["image"]; ?>" class="img-responsive"></a></p>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>