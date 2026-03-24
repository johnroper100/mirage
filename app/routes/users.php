<?php

    /* Users */

    \Steampixel\Route::add('/api/users', function () {
        if (isset($_SESSION['loggedin'])) {
            global $userStore;
            echo json_encode($userStore->createQueryBuilder()->select(['name', 'email', 'accountType', 'notifySubmissions', 'bio'])->getQuery()->fetch());
        } else {
            getErrorPage(401);
        }
    });

    \Steampixel\Route::add('/api/users/active', function () {
        if (isset($_SESSION['loggedin'])) {
            global $userStore;
            echo json_encode($userStore->createQueryBuilder()->select(['name', 'email', 'accountType', 'notifySubmissions', 'bio'])->where( [ "_id", "=", $_SESSION["id"] ] )->getQuery()->fetch()[0]);
        } else {
            getErrorPage(401);
        }
    });

    \Steampixel\Route::add('/api/users', function () {
        if (isset($_SESSION['loggedin']) && $_SESSION['accountType'] != 2) {
            global $userStore;

            if (!requireCsrfToken(true)) {
                return;
            }

            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            if (!is_array($data) || empty($data["password"])) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Name, email, and password are required.'
                ], 400);
                return;
            }

            $user = [
                'name' => trim((string) ($data["name"] ?? '')),
                'email' => normalizeEmailAddress($data["email"] ?? ''),
                'bio' => "",
                'notifySubmissions' => 1,
                'password' => password_hash($data["password"], PASSWORD_DEFAULT),
                'accountType' => clampEditableAccountType($data["accountType"] ?? 2)
            ];

            if ($user['name'] === '' || !isValidEmailAddress($user['email'])) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'A valid name and email are required.'
                ], 400);
                return;
            }

            $userStore->insert($user);
            sendJsonResponse([
                'success' => true
            ]);
        } else {
            getErrorPage(401);
        }
    }, 'POST');

    \Steampixel\Route::add('/api/users/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin']) && ($_SESSION['accountType'] != 2 || $_SESSION['id'] == $who)) {
            global $userStore;

            if (!requireCsrfToken(true)) {
                return;
            }

            $existingUser = $userStore->findById($who);
            if ($existingUser == null || !canManageUserRecord($existingUser)) {
                getErrorPage(401);
                return;
            }

            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            if (!is_array($data)) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Invalid request body.'
                ], 400);
                return;
            }

            $user = [
                'name' => trim((string) ($data["name"] ?? '')),
                'email' => normalizeEmailAddress($data["email"] ?? ''),
                'bio' => (string) ($data["bio"] ?? ''),
                'notifySubmissions' => !empty($data["notifySubmissions"]) ? 1 : 0,
                'accountType' => clampEditableAccountType($data["accountType"] ?? ($existingUser["accountType"] ?? 2), $existingUser)
            ];

            if ($user['name'] === '' || !isValidEmailAddress($user['email'])) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'A valid name and email are required.'
                ], 400);
                return;
            }

            if (isset($data["password"]) && $data["password"] != "") {
                $user['password'] = password_hash($data["password"], PASSWORD_DEFAULT);
            }

            $updatedUser = $userStore->updateById($who, $user);
            if ((string) $who === getCurrentUserId()) {
                $_SESSION['name'] = $updatedUser['name'];
                $_SESSION['accountType'] = $updatedUser['accountType'];
            }

            sendJsonResponse([
                'success' => true
            ]);
        } else {
            getErrorPage(401);
        }
    }, 'PUT');

    \Steampixel\Route::add('/api/users/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin']) && $_SESSION['accountType'] != 2) {
            global $userStore;
            global $pageStore;

            if (!requireCsrfToken(true)) {
                return;
            }

            $existingUser = $userStore->findById($who);
            if ($existingUser == null || !canManageUserRecord($existingUser) || (string) $who === getCurrentUserId()) {
                getErrorPage(401);
                return;
            }

            if ($userStore->count() > 1) {
                $userStore->deleteById($who);

                $allPages = $pageStore->findAll();
                foreach($allPages as $key => $page){
                    if ($page["createdUser"] == $who) {
                        $page["createdUser"] = null;
                    }
                    if ($page["editedUser"] == $who) {
                        $page["editedUser"] = null;
                    }
                    $allPages[$key] = $page;
                }
                $pageStore->update($allPages);
            }
            sendJsonResponse([
                'success' => true
            ]);
        } else {
            getErrorPage(401);
        }
    }, 'DELETE');

