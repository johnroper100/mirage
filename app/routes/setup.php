<?php

    \Steampixel\Route::add('/setup', function () {
        global $userStore;

        if (!requireCsrfToken()) {
            return;
        }

        $emailAddress = normalizeEmailAddress($_POST["email"] ?? '');
        if (!isValidEmailAddress($emailAddress)) {
            getErrorPage(400);
            return;
        }

        $user = [
            'name' => test_input($_POST["name"]),
            'email' => $emailAddress,
            'bio' => "",
            'notifySubmissions' => 1,
            'password' => password_hash($_POST["password"], PASSWORD_DEFAULT),
            'accountType' => 0
        ];

        $user = $userStore->insert($user);

        $siteSettings = normalizeSiteSettings([
            'siteTitle' => $_POST["siteTitle"] ?? ''
        ]);

        if ($siteSettings['siteTitle'] === '') {
            getErrorPage(400);
            return;
        }

        if (!writeSiteConfigFile($siteSettings)) {
            getErrorPage(500);
            return;
        }

        header('Location: ' . BASEPATH . '/login');
    }, 'POST');

    \Steampixel\Route::add('(.*)', function ($who) {
        include MIRAGE_ROOT . '/dashboard/setup.php';
    });
