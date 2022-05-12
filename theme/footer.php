<footer class="probootstrap-footer" role="contentinfo">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <p class="probootstrap-copyright">&copy; <?php echo date("Y"); ?> <?php echo $siteTitle; ?> - All Rights Reserved.</p>
                <ul class="probootstrap-main-nav" style="float: right;">
                    <?php
                        $menuItems = getMenuItems('header');
                        foreach ($menuItems as $menuItem) {
                    ?>
                    <li><a style="color: #5068A9;" href="<?php echo BASEPATH . '/' . $menuItem['link']; ?>" <?php if ($menuItem['type'] == 1) { ?>target="_blank"<?php } ?>><?php echo $menuItem['name']; ?></a></li>
                    <?php }; ?>
                </ul>
            </div>
        </div>
    </div>
</footer>

<div class="gototop js-top">
    <a href="#" class="js-gotop"><i class="icon-chevron-thin-up"></i></a>
</div>


<script src="<?php echo THEMEPATH; ?>/js/scripts.min.js"></script>
<script src="<?php echo THEMEPATH; ?>/js/main.min.js"></script>
<script src="<?php echo THEMEPATH; ?>/js/custom.js"></script>

</body>

</html>