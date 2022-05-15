<?php include 'header.php'; ?>
<div class="probootstrap-section">
    <div class="container">
        <?php if ($page["content"]["featuredImage"] != "" ) { ?>
        <div class="row probootstrap-gutter16">
            <div class="col-12">
                <div class="img-bg" style="background-image: url(<?php echo BASEPATH; ?>/uploads/<?php echo $page["content"]["featuredImage"]; ?>);"></div>
            </div>
        </div>
        <?php }; ?>
        <div class="row">
            <div class="col-md-6 col-md-offset-3 text-center">
                <h2><?php echo $page["title"]; ?></h2>
                <p>Published on <?php echo date("F jS, Y", $page["created"]); ?></p>
            </div>
            <div class="col-md-12">
                <?php echo $page["content"]["pageContent"]; ?>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>