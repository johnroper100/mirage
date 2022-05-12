<?php include 'header.php'; ?>
<section class="probootstrap-section">
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-md-offset-3 mb80 text-center probootstrap-animate">
                <h2><?php echo $page["title"]; ?></h2>
                <p><?php echo $page["content"]["headerSubtitle"]; ?></p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 probootstrap-animate">
                <form action="<?php echo BASEPATH; ?>/form/contact" method="post" class="probootstrap-form mb60">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="firstName">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="firstName">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lastName">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="lastName">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea cols="30" rows="10" class="form-control" id="message" name="message"></textarea>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary" id="submit" name="submit" value="Send Message">
                    </div>
                </form>
            </div>
            <div class="col-md-3 col-md-push-1 probootstrap-animate">
                <h4>Contact Details</h4>
                <ul class="with-icon colored">
                    <?php if ($page["content"]["address"] != "" ) { ?>
                    <li><i class="icon-location2"></i> <span><?php echo $page["content"]["address"]; ?></span></li>
                    <?php }; ?>
                    <?php if ($page["content"]["email"] != "" ) { ?>
                    <li><i class="icon-mail"></i><span><?php echo $page["content"]["email"]; ?></span></li>
                    <?php }; ?>
                    <?php if ($page["content"]["phone"] != "" ) { ?>
                    <li><i class="icon-phone2"></i><span>+<?php echo $page["content"]["phone"]; ?></span></li>
                    <?php }; ?>
                </ul>
            </div>
        </div>
    </div>
</section>
<!-- END section -->
<?php include 'footer.php'; ?>