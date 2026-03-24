<?php

    /* Menus */

    \Steampixel\Route::add('/api/menus', function () {
        if (canManageMenus()) {
            $allMenuItems = getAllMenuItems();
            $myJSON = json_encode($allMenuItems);
            echo $myJSON;
        } else {
            getErrorPage(401);
        }
    });

    \Steampixel\Route::add('/api/menus', function () {
        if (canManageMenus()) {
            global $menuStore;

            if (!requireCsrfToken(true)) {
                return;
            }

            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            if (!is_array($data)) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Invalid menu payload.'
                ], 400);
                return;
            }

            $data = prepareMenuItemsForStore($data);

            $menuStore->createQueryBuilder()->getQuery()->delete();
            if (count($data) > 0) {
                $menuStore->insertMany($data);
            }

            $allMenuItems = getAllMenuItems();
            $myJSON = json_encode($allMenuItems);
            echo $myJSON;
        } else {
            getErrorPage(401);
        }
    }, 'POST');

