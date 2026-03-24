<?php

    \Steampixel\Route::add('/sitemap.xml', function () {
        outputSitemapXml();
    });

    /* Page Display */

    \Steampixel\Route::add('/(.*)/(.*)', function ($who1, $who2) {
        global $pageStore;

        $page = $pageStore->findOneBy([["collectionSubpath", "=", $who1], "AND", ["path", "=", $who2]]);
        handleProtectedPageAccess($page);
    }, 'POST');

    \Steampixel\Route::add('/(.*)', function ($who) {
        global $pageStore;

        $page = $pageStore->findOneBy([["collectionSubpath", "=", ""], "AND", ["path", "=", $who]]);
        handleProtectedPageAccess($page);
    }, 'POST');

    # Return pages under a collection subpath - currently only supports one collection subpath
    \Steampixel\Route::add('/(.*)/(.*)', function ($who1, $who2) {
        global $pageStore;
        global $siteTitle;

        $page = $pageStore->findOneBy([["collectionSubpath", "=", $who1], "AND", ["path", "=", $who2]]);
        outputPublicPage($page, $siteTitle);
    });

    # Return pages not under a collection subpath
    \Steampixel\Route::add('/(.*)', function ($who) {
        global $pageStore;
        global $siteTitle;

        $page = $pageStore->findOneBy([["collectionSubpath", "=", ""], "AND", ["path", "=", $who]]);
        outputPublicPage($page, $siteTitle);
    });
