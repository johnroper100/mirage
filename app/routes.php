<?php

# Run setup if config.php does not yet exist
$configPath = MIRAGE_ROOT . DIRECTORY_SEPARATOR . 'config.php';
if (!file_exists($configPath)) {
    require __DIR__ . '/routes/setup.php';
} else {
# if config.php exists, run the rest of the application

    require_once $configPath;

    $siteSettings = getCurrentSiteSettings();
    $siteTitle = $siteSettings['siteTitle'];
    $footerText = $siteSettings['footerText'];
    $copyrightText = $siteSettings['copyrightText'];
    $googleAnalyticsTrackingCode = $siteSettings['googleAnalyticsTrackingCode'];

    define('THEMEPATH', BASEPATH . "/theme");

    require __DIR__ . '/routes/admin.php';
    require __DIR__ . '/routes/users.php';
    require __DIR__ . '/routes/pages.php';
    require __DIR__ . '/routes/menus.php';
    require __DIR__ . '/routes/media.php';
    require __DIR__ . '/routes/forms.php';
    require __DIR__ . '/routes/public.php';
}

\Steampixel\Route::run(BASEPATH);
