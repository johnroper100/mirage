<?php

session_start();

define('BASEPATH', dirname($_SERVER['PHP_SELF']));

if (!file_exists(".htaccess")) {
    $myfile = fopen(".htaccess", "w") or die("Unable to open file!");
    $txt = "DirectoryIndex index.php\n";
    fwrite($myfile, $txt);
    // Enable apache rewrite engine
    $txt = "RewriteEngine on\n";
    fwrite($myfile, $txt);
    // Set the rewrite base
    $txt = "RewriteBase " . BASEPATH . "\n";
    fwrite($myfile, $txt);
    // Deliver the folder or file directly if it exists on the server
    $txt = "RewriteRule ^database/?$ - [F,L]\n";
    fwrite($myfile, $txt);
    $txt = "RewriteRule ^dashboard/?$ - [F,L]\n";
    fwrite($myfile, $txt);
    $txt = "RewriteCond %{REQUEST_FILENAME} !-f\n";
    fwrite($myfile, $txt);
    $txt = "RewriteCond %{REQUEST_FILENAME} !-d\n";
    fwrite($myfile, $txt);
    // Push every request to index.php
    $txt = "RewriteRule ^(.*)$ index.php [QSA]\n";
    fwrite($myfile, $txt);
    fclose($myfile);
}

use Steampixel\Route;
use SleekDB\Store;

include 'simplePHPRouter/src/Steampixel/Route.php';
require_once 'SleekDB/src/Store.php';

$sleekDBConfiguration = [
    "timeout" => false
];

$databaseDirectory = __DIR__ . "/database";
$pageStore = new Store("pages", $databaseDirectory, $sleekDBConfiguration);
$userStore = new Store("users", $databaseDirectory, $sleekDBConfiguration);
$mediaStore = new Store("mediaItems", $databaseDirectory, $sleekDBConfiguration);

function generateField($field)
{
    if ($field['type'] != 'list') {
        return $field['value'];
    } else {
        $itemList = [];
        foreach ($field['value'] as $subFields) {
            $newItem = [];
            foreach ($subFields as $subField) {
                $newItem[$subField['id']] = generateField($subField);
            }
            array_push($itemList, $newItem);
        }
        return $itemList;
    }
};

function generatePage($json, $currentTheme)
{
    $data = json_decode($json, true);
    $page = [];
    $page["content"] = [];
    $page["userID"] = $_SESSION['id'];
    $page["edited"] = time();
    $page["theme"] = $currentTheme;

    foreach ($data["template"]["sections"] as $section) {
        foreach ($section["fields"] as $field) {
            if (isset($field['value'])) {
                $page["content"][$field['id']] = generateField($field);
            }
        }
    }

    $page["templateName"] = $data["templateName"];
    $page["title"] = $data["title"];
    $page["path"] = $data["path"];
    $page["collection"] = $data["collection"];
    $page["collectionSubpath"] = $data["collectionSubpath"] ?? "";
    $page["published"] = $data["published"];
    return $page;
};

function getErrorPage($errorCode)
{
    global $activeTheme;
    http_response_code($errorCode);
    $errorMessage = "we will look into the issue and get it fixed as soon as possible, maybe try reloading the page";
    if ($errorCode == 404) {
        $errorMessage = "you've lost your way, you may have attempted to get to a page that doesn't exist";
    }
    if (file_exists("./themes/" .  $activeTheme . "/error.php")) {
        include "./themes/" .  $activeTheme . "/error.php";
    } else {
        include "./dashboard/error.php";
    }
};

function getPages($collection, $numEntries)
{
    global $pageStore;
    $pages = $pageStore->findBy(["collection", "=", $collection], ["edited" => "desc"], $numEntries);
    return $pages;
};

function get_words($sentence, $count = 10) {
    preg_match("/(?:\w+(?:\W+|$)){0,$count}/", $sentence, $matches);
    return $matches[0];
};

if (!file_exists("config.php")) {
    Route::add('/setup', function () {
        global $userStore;

        $user = [
            'name' => $_POST["name"],
            'email' => $_POST["email"],
            'password' => password_hash($_POST["password"], PASSWORD_DEFAULT),
            'accountType' => 0
        ];

        $user = $userStore->insert($user);

        $myfile = fopen("config.php", "w") or die("Unable to open file!");
        $txt = "<?php\n\n";
        fwrite($myfile, $txt);
        $txt = "\$siteTitle = \"" . $_POST["siteTitle"] . "\";\n";
        fwrite($myfile, $txt);
        $txt = "\$activeTheme = \"business\";\n\n";
        fwrite($myfile, $txt);
        $txt = "?>";
        fwrite($myfile, $txt);
        fclose($myfile);

        header('Location: ' . BASEPATH . '/login');
    }, 'POST');

    Route::add('(.*)', function ($who) {
        include "./dashboard/setup.php";
    });
} else {
    require_once 'config.php';

    Route::add('/admin', function () {
        if (isset($_SESSION['loggedin'])) {
            include "./dashboard/admin.php";
        } else {
            header('Location: ' . BASEPATH . '/login');
        }
    });

    Route::add('/login', function () {
        if (isset($_SESSION['loggedin'])) {
            header('Location: ' . BASEPATH . '/admin');
        } else {
            include "./dashboard/login.php";
        }
    });

    Route::add('/login', function () {
        global $userStore;

        $user = $userStore->findOneBy(["email", "=", $_POST["email"]]);

        if ($user != null && password_verify($_POST["password"], $user['password'])) {
            session_regenerate_id();
            $_SESSION['loggedin'] = true;
            $_SESSION['name'] = $user['name'];
            $_SESSION['id'] = $user['_id'];
            $_SESSION['userType'] = $user['accountType'];

            header('Location: ' . BASEPATH . '/admin');
        } else {
            header('Location: ' . BASEPATH . '/login');
        }
    }, 'POST');

    Route::add('/logout', function () {
        if (isset($_SESSION['loggedin'])) {
            session_destroy();
        }
        header('Location: ' . BASEPATH . '/login');
    });

    Route::add('/api/themes', function () {
        if (isset($_SESSION['loggedin']) || !file_exists("config.php")) {
            $themes = array();
            foreach (new DirectoryIterator('./themes') as $fileInfo) {
                if($fileInfo->isDir() && !$fileInfo->isDot()) {
                    $configFile = $fileInfo->getPathname() . "/config.json";
                    if (file_exists($configFile)) {
                        global $activeTheme;
                        $themeItem = json_decode(file_get_contents($configFile));
                        if (file_exists("config.php") && $fileInfo->getFilename() == $activeTheme) {
                            $themeItem->active = true;
                        }
                        $themes[] = $themeItem;
                    }
                }
            }
            echo json_encode($themes);
        } else {
            getErrorPage(404);
        }
    });

    Route::add('/api/themes/active', function () {
        global $activeTheme;
        if (isset($_SESSION['loggedin'])) {
            echo file_get_contents("./themes/" .  $activeTheme . "/config.json");
        } else {
            getErrorPage(404);
        }
    });

    Route::add('/api/templates/(.*)', function ($who) {
        global $activeTheme;
        if (isset($_SESSION['loggedin'])) {
            echo file_get_contents("./themes/" . $activeTheme . "/template_defs/" . $who . ".json");
        } else {
            getErrorPage(404);
        }
    });

    Route::add('/api/counts', function () {
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
            getErrorPage(404);
        }
    });

    Route::add('/api/users', function () {
        if (isset($_SESSION['loggedin'])) {
            global $userStore;
            echo json_encode($userStore->createQueryBuilder()->select(['name', 'email', 'accountType'])->getQuery()->fetch());
        } else {
            getErrorPage(404);
        }
    });

    Route::add('/api/users/active', function () {
        if (isset($_SESSION['loggedin'])) {
            global $userStore;
            echo json_encode($userStore->createQueryBuilder()->select(['name', 'email', 'accountType'])->where( [ "_id", "=", $_SESSION["id"] ] )->getQuery()->fetch()[0]);
        } else {
            getErrorPage(404);
        }
    });

    Route::add('/api/users', function () {
        if (isset($_SESSION['loggedin']) && $_SESSION['userType'] == 0) {
            global $userStore;
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $user = [
                'name' => $data["name"],
                'email' => $data["email"],
                'password' => password_hash($data["password"], PASSWORD_DEFAULT),
                'accountType' => $data["accountType"]
            ];

            $user = $userStore->insert($user);
        } else {
            getErrorPage(404);
        }
    }, 'POST');

    Route::add('/api/users/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin']) && ($_SESSION['userType'] == 0 || $_SESSION['id'] == $who)) {
            global $userStore;
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $user = [
                'name' => $data["name"],
                'email' => $data["email"],
                'accountType' => $data["accountType"]
            ];

            if ($data["password"]) {
                $user['password'] = password_hash($data["password"], PASSWORD_DEFAULT);
            }

            $user = $userStore->updateById($who, $user);
        } else {
            getErrorPage(404);
        }
    }, 'PUT');

    Route::add('/api/users/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin']) && $_SESSION['userType'] == 0) {
            global $userStore;
            if ($userStore->count() > 1) {
                $userStore->deleteById($who);
            }
        } else {
            getErrorPage(404);
        }
    }, 'DELETE');

    Route::add('/api/collections/(.*)/pages', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            global $pageStore;
            $allPages = $pageStore->findBy(["collection", "=", $who]);
            $myJSON = json_encode($allPages);
            echo $myJSON;
        } else {
            getErrorPage(404);
        }
    });

    Route::add('/api/pages/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            global $pageStore;
            $selectedPage = $pageStore->findById($who);
            $myJSON = json_encode($selectedPage);
            echo $myJSON;
        } else {
            getErrorPage(404);
        }
    });

    Route::add('/api/pages/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            global $pageStore;
            global $activeTheme;

            $json = file_get_contents('php://input');
            $page = $pageStore->updateById($who, generatePage($json, $activeTheme));
            $myJSON = json_encode($page);
            echo $myJSON;
        } else {
            getErrorPage(404);
        }
    }, 'PUT');

    Route::add('/api/pages/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            global $pageStore;
            $pageStore->deleteById($who);
        } else {
            getErrorPage(404);
        }
    }, 'DELETE');

    Route::add('/api/pages', function () {
        if (isset($_SESSION['loggedin'])) {
            global $pageStore;
            global $activeTheme;

            $json = file_get_contents('php://input');
            $page = $pageStore->insert(generatePage($json, $activeTheme));
            $myJSON = json_encode($page);
            echo $myJSON;
        } else {
            getErrorPage(404);
        }
    }, 'POST');

    Route::add('/api/media', function () {
        if (isset($_SESSION['loggedin'])) {
            global $mediaStore;
            $allMedia = $mediaStore->findAll();
            $myJSON = json_encode($allMedia);
            echo $myJSON;
        } else {
            getErrorPage(404);
        }
    });

    Route::add('/api/media', function () {
        if (isset($_SESSION['loggedin'])) {
            global $mediaStore;

            if (!file_exists('./uploads')) {
                mkdir('./uploads');
            }

            $count = count($_FILES['uploadMediaFiles']['name']);
            for ($i = 0; $i < $count; $i++) {
                if (!move_uploaded_file($_FILES['uploadMediaFiles']['tmp_name'][$i], "./uploads/" . $_FILES['uploadMediaFiles']['name'][$i])) {
                    getErrorPage(500);
                } else {
                    $page = [];
                    $page['file'] = $_FILES['uploadMediaFiles']['name'][$i];
                    $page['extension'] = pathinfo($page['file'], PATHINFO_EXTENSION);
                    $page = $mediaStore->insert($page);
                }
            }
        } else {
            getErrorPage(404);
        }
    }, 'POST');

    Route::add('/api/media/richtext', function () {
        if (isset($_SESSION['loggedin'])) {
            global $mediaStore;

            if (!file_exists('./uploads')) {
                mkdir('./uploads');
            }

            if (!move_uploaded_file($_FILES['fileToUpload']['tmp_name'], "./uploads/" . $_FILES['fileToUpload']['name'])) {
                $response = [];
                $response['success'] = false;
                echo json_encode($response);
            } else {
                $page = [];
                $page['file'] = $_FILES['fileToUpload']['name'];
                $page['extension'] = pathinfo($page['file'], PATHINFO_EXTENSION);
                $page = $mediaStore->insert($page);
                $response = [];
                $response['success'] = true;
                $response['file'] = BASEPATH . '/uploads/' . $page['file'];
                echo json_encode($response);
            }
        } else {
            $response = [];
            $response['success'] = false;
            echo json_encode($response);
        }
    }, 'POST');

    Route::add('/api/media/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            global $mediaStore;
            $selectedMedia = $mediaStore->findById($who);
            $mediaStore->deleteById($who);
            if (!unlink("./uploads/" . $selectedMedia['file'])) {
                getErrorPage(500);
            }
        } else {
            
        }
    }, 'DELETE');

    Route::add('/(.*)/(.*)', function ($who1, $who2) {
        global $pageStore;
        global $siteTitle;
        global $activeTheme;
        $page = $pageStore->findOneBy([["collectionSubpath", "=", $who1], "AND", ["path", "=", $who2]]);
        if ($page == null || ($page["published"] == false && !isset($_SESSION['loggedin']))) {
            getErrorPage(404);
        } else {
            include './themes/' .  $activeTheme . '/' . $page["templateName"] . ".php";
        }
    });

    Route::add('/(.*)', function ($who) {
        global $pageStore;
        global $siteTitle;
        global $activeTheme;
        $page = $pageStore->findOneBy([["collectionSubpath", "=", ""], "AND", ["path", "=", $who]]);
        if ($page == null || ($page["published"] == false && !isset($_SESSION['loggedin']))) {
            getErrorPage(404);
        } else {
            include './themes/' .  $activeTheme . '/' . $page["templateName"] . ".php";
        }
    });
};


Route::run(BASEPATH);

?>