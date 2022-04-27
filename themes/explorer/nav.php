<header role="banner" class="probootstrap-header">
    <div class="container">
        <a href="index.html" class="probootstrap-logo"><?php echo $siteTitle; ?></a>
        
        <a href="#" class="probootstrap-burger-menu visible-xs" ><i>Menu</i></a>
        <div class="mobile-menu-overlay"></div>

        <nav role="navigation" class="probootstrap-nav hidden-xs">
          <ul class="probootstrap-main-nav">
            <?php
                $menuItems = getMenuItems('header');
                foreach ($menuItems as $menuItem) {
            ?>
            <li <?php if ($menuItem['type'] == 0 && $menuItem['page'] == $page['_id']) { ?>class="active"<?php }; ?>><a href="<?php echo $menuItem['link']; ?>" <?php if ($menuItem['type'] == 1) { ?>target="_blank"<?php } ?>><?php echo $menuItem['name']; ?></a></li>
            <?php }; ?>
          </ul>
          <!--<div class="extra-text visible-xs"> 
            <a href="#" class="probootstrap-burger-menu"><i>Menu</i></a>
            <h5>Address</h5>
            <p>198 West 21th Street, Suite 721 New York NY 10016</p>
            <h5>Connect</h5>
            <ul class="social-buttons">
              <li><a href="#"><i class="icon-twitter"></i></a></li>
              <li><a href="#"><i class="icon-facebook2"></i></a></li>
              <li><a href="#"><i class="icon-instagram2"></i></a></li>
            </ul>
          </div>-->
        </nav>
    </div>
  </header>
  <!-- END: header -->