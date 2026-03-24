<?php

    /* Administation */

    \Steampixel\Route::add('/admin', function () {
        if (isset($_SESSION['loggedin'])) {
            include MIRAGE_ROOT . '/dashboard/admin.php';
        } else {
            header('Location: ' . BASEPATH . '/login');
        }
    });

    \Steampixel\Route::add('/login', function () {
        if (isset($_SESSION['loggedin'])) {
            header('Location: ' . BASEPATH . '/admin');
        } else {
            include MIRAGE_ROOT . '/dashboard/login.php';
        }
    });

    \Steampixel\Route::add('/login', function () {
        global $userStore;

        if (!requireCsrfToken()) {
            return;
        }

        $user = $userStore->findOneBy(["email", "=", normalizeEmailAddress($_POST["email"] ?? '')]);

        if ($user != null && password_verify($_POST["password"], $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['loggedin'] = true;
            $_SESSION['name'] = $user['name'];
            $_SESSION['id'] = $user['_id'];
            $_SESSION['accountType'] = $user['accountType'];

            header('Location: ' . BASEPATH . '/admin');
        } else {
            header('Location: ' . BASEPATH . '/login');
        }
    }, 'POST');

    \Steampixel\Route::add('/logout', function () {
        if (!requireCsrfToken()) {
            return;
        }

        if (isset($_SESSION['loggedin'])) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                setcookie(session_name(), '', time() - 42000, '/');
            }
            session_destroy();
        }
        header('Location: ' . BASEPATH . '/login');
    }, 'POST');

    # Routes marked API are used by the backend to get data

    /* Theme and Admin UI */

    \Steampixel\Route::add('/api/theme', function () {
        if (isset($_SESSION['loggedin'])) {
            echo file_get_contents(MIRAGE_ROOT . '/theme/config.json');
        } else {
            getErrorPage(401);
        }
    });

    \Steampixel\Route::add('/api/templates/(.*)', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            $templateDefinition = loadTemplateDefinition($who);
            if ($templateDefinition === null) {
                getErrorPage(404);
                return;
            }

            sendJsonResponse($templateDefinition);
        } else {
            getErrorPage(401);
        }
    });

    \Steampixel\Route::add('/api/counts', function () {
        if (isset($_SESSION['loggedin'])) {
            global $pageStore;
            global $userStore;
            global $mediaStore;
            echo json_encode([
                "pages" => $pageStore->count(),
                "users" => $userStore->count(),
                "media" => $mediaStore->count()
            ]);
        } else {
            getErrorPage(401);
        }
    });

    \Steampixel\Route::add('/api/analytics/summary', function () {
        if (!isLoggedIn()) {
            sendJsonResponse([
                'success' => false,
                'message' => 'You must be logged in to view analytics.'
            ], 401);
            return;
        }

        sendJsonResponse(getAnalyticsSummary());
    });

    \Steampixel\Route::add('/api/settings', function () {
        if (!isLoggedIn()) {
            sendJsonResponse([
                'success' => false,
                'message' => 'You must be logged in to view site settings.'
            ], 401);
            return;
        }

        if (!isAdministrator()) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Only administrators can view site settings.'
            ], 403);
            return;
        }

        sendJsonResponse(getCurrentSiteSettings());
    });

    \Steampixel\Route::add('/api/settings', function () {
        if (!isLoggedIn()) {
            sendJsonResponse([
                'success' => false,
                'message' => 'You must be logged in to update site settings.'
            ], 401);
            return;
        }

        if (!isAdministrator()) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Only administrators can update site settings.'
            ], 403);
            return;
        }

        if (!requireCsrfToken(true)) {
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Invalid settings payload.'
            ], 400);
            return;
        }

        $siteSettings = normalizeSiteSettings($data);
        $rawGoogleAnalyticsTrackingCode = trim((string) ($data['googleAnalyticsTrackingCode'] ?? ''));
        if ($siteSettings['siteTitle'] === '') {
            sendJsonResponse([
                'success' => false,
                'message' => 'Site title is required.'
            ], 400);
            return;
        }

        if ($rawGoogleAnalyticsTrackingCode !== '' && $siteSettings['googleAnalyticsTrackingCode'] === '') {
            sendJsonResponse([
                'success' => false,
                'message' => 'Paste a Google Analytics measurement ID or full Google tag snippet.'
            ], 400);
            return;
        }

        if (!writeSiteConfigFile($siteSettings)) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Site settings could not be saved.'
            ], 500);
            return;
        }

        global $siteTitle;
        global $siteDescription;
        global $socialImage;
        global $socialLocale;
        global $twitterSite;
        global $twitterCreator;
        global $facebookAppId;
        global $footerText;
        global $copyrightText;
        global $googleAnalyticsTrackingCode;

        $siteTitle = $siteSettings['siteTitle'];
        $siteDescription = $siteSettings['siteDescription'];
        $socialImage = $siteSettings['socialImage'];
        $socialLocale = $siteSettings['socialLocale'];
        $twitterSite = $siteSettings['twitterSite'];
        $twitterCreator = $siteSettings['twitterCreator'];
        $facebookAppId = $siteSettings['facebookAppId'];
        $footerText = $siteSettings['footerText'];
        $copyrightText = $siteSettings['copyrightText'];
        $googleAnalyticsTrackingCode = $siteSettings['googleAnalyticsTrackingCode'];

        sendJsonResponse($siteSettings);
    }, 'PUT');

    \Steampixel\Route::add('/api/backups/full', function () {
        if (!isLoggedIn()) {
            sendJsonResponse([
                'success' => false,
                'message' => 'You must be logged in to download a full backup.'
            ], 401);
            return;
        }

        if (!isAdministrator()) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Only administrators can download a full backup.'
            ], 403);
            return;
        }

        if (!requireCsrfToken(true)) {
            return;
        }

        $backupArchive = createFullBackupArchive();
        if (($backupArchive['success'] ?? false) !== true) {
            sendJsonResponse([
                'success' => false,
                'message' => $backupArchive['message'] ?? 'The full backup could not be generated.'
            ], 500);
            return;
        }

        if (!streamFullBackupArchive($backupArchive)) {
            deleteGeneratedFullBackupArchive($backupArchive);
            sendJsonResponse([
                'success' => false,
                'message' => 'The full backup could not be downloaded.'
            ], 500);
        }
    }, 'POST');

