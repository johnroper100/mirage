<header role="banner" class="probootstrap-header">
    <div class="container">
        <a href="<?php echo ORIGBASEPATH; ?>" class="probootstrap-logo"><?php echo $siteTitle; ?></a>
        
        <a href="#" class="probootstrap-burger-menu visible-xs" ><i>Menu</i></a>
        <div class="mobile-menu-overlay"></div>

        <nav role="navigation" class="probootstrap-nav hidden-xs">
          <ul class="probootstrap-main-nav">
            <?php
                $menuItems = getMenuItems('header');
                foreach ($menuItems as $menuItem) {
            ?>
            <li <?php if ($menuItem['type'] == 0 && $menuItem['page'] == $page['_id']) { ?>class="active"<?php }; ?>><a href="<?php echo BASEPATH . '/' . $menuItem['link']; ?>" <?php if ($menuItem['type'] == 1) { ?>target="_blank"<?php } ?>><?php echo $menuItem['name']; ?></a></li>
            <?php }; ?>
          </ul>
        </nav>
    </div>
  </header>
  <!-- END: header -->