<?php

    /* Pages */

    \Steampixel\Route::add('/api/collections/(.*)/pages', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            $allPages = getPages($who, 0);
            sendJsonResponse($allPages);
        } else {
            getErrorPage(401);
        }
    });

    \Steampixel\Route::add('/api/collections/(.*)/order', function ($who) {
        if (!isset($_SESSION['loggedin'])) {
            getErrorPage(401);
            return;
        }

        global $pageStore;

        if (!requireCsrfToken(true)) {
            return;
        }

        if (getCollectionConfigById($who) === null) {
            sendJsonResponse([
                'success' => false,
                'message' => 'The requested collection does not exist.'
            ], 404);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        $orderedPageIDs = $payload['pageIDs'] ?? null;
        if (!is_array($orderedPageIDs)) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Invalid page order payload.'
            ], 400);
            return;
        }

        $orderedPageIDs = array_values(array_map('strval', $orderedPageIDs));
        if (count($orderedPageIDs) !== count(array_unique($orderedPageIDs))) {
            sendJsonResponse([
                'success' => false,
                'message' => 'The page order payload contains duplicate pages.'
            ], 400);
            return;
        }

        $collectionPages = $pageStore->findBy(["collection", "=", $who]);
        if (count($orderedPageIDs) !== count($collectionPages)) {
            sendJsonResponse([
                'success' => false,
                'message' => 'The page order payload does not match the collection.'
            ], 400);
            return;
        }

        $pagesById = [];
        foreach ($collectionPages as $page) {
            $pageID = (string) ($page['_id'] ?? '');
            $pagesById[$pageID] = $page;

            if (!canEditPage($page)) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'You do not have permission to reorder this collection.'
                ], 403);
                return;
            }
        }

        foreach ($orderedPageIDs as $pageID) {
            if (!isset($pagesById[$pageID])) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'The page order payload does not match the collection.'
                ], 400);
                return;
            }
        }

        sendJsonResponse(syncCollectionPageOrders($who, $orderedPageIDs));
    }, 'PUT');

    \Steampixel\Route::add('/api/pages', function () {
        if (isset($_SESSION['loggedin'])) {
            global $pageStore;
            $allPages = $pageStore->findAll();
            sendJsonResponse(preparePagesForResponse($allPages));
        } else {
            getErrorPage(401);
        }
    });

    \Steampixel\Route::add('/api/pages/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            global $pageStore;
            $selectedPage = $pageStore->findById($who);
            sendJsonResponse(preparePageForResponse($selectedPage));
        } else {
            getErrorPage(401);
        }
    });

    \Steampixel\Route::add('/api/pages', function () {
        if (isset($_SESSION['loggedin'])) {
            global $pageStore;

            if (!requireCsrfToken(true)) {
                return;
            }

            $json = file_get_contents('php://input');
            $page = generatePage($json, true);
            if ($page === null) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'The page request is invalid.'
                ], 400);
                return;
            }

            $savedPage = $pageStore->insert($page);
            sendJsonResponse(preparePageForResponse($savedPage));
        } else {
            getErrorPage(401);
        }
    }, 'POST');

    \Steampixel\Route::add('/api/pages/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            global $pageStore;
            global $menuStore;

            if (!requireCsrfToken(true)) {
                return;
            }

            $existingPage = $pageStore->findById($who);
            if ($existingPage == null || !canEditPage($existingPage)) {
                getErrorPage(401);
                return;
            }

            $json = file_get_contents('php://input');
            $updatedPageData = generatePage($json, false, $existingPage);
            if ($updatedPageData === null) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'The page request is invalid.'
                ], 400);
                return;
            }

            $page = $pageStore->updateById($who, $updatedPageData);

            $allMenuItems = $menuStore->findAll();
            foreach ($allMenuItems as &$menuItem) {
                if ($menuItem["type"] == 0 && $menuItem["page"] == $who) {
                    $normalizedMenuItem = normalizeMenuItem($menuItem);
                    $menuItem["link"] = resolveMenuItemLink($menuItem);
                    $menuItem = $menuStore->updateById($menuItem["_id"], [
                        "link" => $menuItem["link"],
                        "itemID" => $normalizedMenuItem["itemID"],
                        "parentItemID" => $normalizedMenuItem["parentItemID"]
                    ]);
                }
            }

            sendJsonResponse(preparePageForResponse($page));
        } else {
            getErrorPage(401);
        }
    }, 'PUT');

    \Steampixel\Route::add('/api/pages/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            global $pageStore;
            global $menuStore;

            if (!requireCsrfToken(true)) {
                return;
            }

            $existingPage = $pageStore->findById($who);
            if ($existingPage == null || !canEditPage($existingPage)) {
                getErrorPage(401);
                return;
            }

            $allMenuItems = $menuStore->findAll();
            foreach ($allMenuItems as &$menuItem) {
                if ($menuItem["type"] == 0 && $menuItem["page"] == $who) {
                    $normalizedMenuItem = normalizeMenuItem($menuItem);
                    reparentChildMenuItems($normalizedMenuItem["itemID"], $normalizedMenuItem["parentItemID"]);
                    $menuStore->deleteById($menuItem["_id"]);
                }
            }
            $deletedPageCollection = $existingPage['collection'] ?? null;
            $pageStore->deleteById($who);
            if (is_string($deletedPageCollection) && $deletedPageCollection !== '') {
                syncCollectionPageOrders($deletedPageCollection);
            }
            sendJsonResponse([
                'success' => true
            ]);
        } else {
            getErrorPage(401);
        }
    }, 'DELETE');

