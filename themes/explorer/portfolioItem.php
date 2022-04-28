<?php include 'header.php'; ?>
<div class="probootstrap-section">
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-md-offset-3 mb40 text-center">
                <h2><?php echo $page["title"]; ?></h2>
                <p><?php echo $page["content"]["headerSubtitle"]; ?></p>
            </div>
            <div class="col-md-12">
            <?php echo $page["content"]["pageContent"]; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <p><a href="img/img_1.jpg" class="image-popup"><img src="img/img_1.jpg" class="img-responsive"
                            alt="Free HTML5 Bootstrap Template by uicookies.com"></a></p>
                <p><a href="img/img_3.jpg" class="image-popup"><img src="img/img_3.jpg" class="img-responsive"
                            alt="Free HTML5 Bootstrap Template by uicookies.com"></a></p>
                <p><a href="img/img_7.jpg" class="image-popup"><img src="img/img_7.jpg" class="img-responsive"
                            alt="Free HTML5 Bootstrap Template by uicookies.com"></a></p>
            </div>
            <div class="col-md-6">
                <p><a href="img/img_2.jpg" class="image-popup"><img src="img/img_2.jpg" class="img-responsive"
                            alt="Free HTML5 Bootstrap Template by uicookies.com"></a></p>
                <p><a href="img/img_4.jpg" class="image-popup"><img src="img/img_4.jpg" class="img-responsive"
                            alt="Free HTML5 Bootstrap Template by uicookies.com"></a></p>
                <p><a href="img/img_6.jpg" class="image-popup"><img src="img/img_6.jpg" class="img-responsive"
                            alt="Free HTML5 Bootstrap Template by uicookies.com"></a></p>
            </div>
        </div>

    </div>
</div>
<?php include 'footer.php'; ?>