<?php

session_start();

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
require_once './SleekDB/src/Store.php';
require_once './config.php';

define('BASEPATH', dirname($_SERVER[PHP_SELF]));

$databaseDirectory = __DIR__ . "/database";
$pageStore = new Store("pages", $databaseDirectory);
$userStore = new Store("users", $databaseDirectory);

function generatePage($json) {
    $data = json_decode($json, true);
    $page = [];
    $page["content"] = [];

    foreach ($data["template"]["sections"] as $section) {
        foreach ($section["fields"] as $field) {
            $page["content"][$field['id']] = $field['value'];
        }
    }

    $page["templateName"] = $data["templateName"];
    $page["title"] = $data["title"];
    $page["path"] = $data["path"];
    $page["collection"] = $data["collection"];
    $page["private"] = $data["private"];
    return $page;
}

Route::add('/admin', function () {
    if (isset($_SESSION['loggedin'])) {
        include "admin.php";
    } else {
        header('Location: ' . BASEPATH . '/login');
    }
});

Route::add('/login', function () {
    if (isset($_SESSION['loggedin'])) {
        header('Location: ' . BASEPATH . '/admin');
    } else {
        include "login.php";
    }
});

Route::add('/login', function () {
    global $userStore;

    session_regenerate_id();
    $_SESSION['loggedin'] = true;
    $_SESSION['name'] = "John Roper";
    $_SESSION['id'] = 12345;

    header('Location: ' . BASEPATH . '/admin');
}, 'POST');

Route::add('/logout', function () {
    if (isset($_SESSION['loggedin'])) {
        session_destroy();
    }
    header('Location: ' . BASEPATH . '/login');
});

Route::add('/api/theme', function () {
    if (isset($_SESSION['loggedin'])) {
        echo file_get_contents("./themes/mirage/config.json");
    } else {
        header('HTTP/1.0 404 Not Found');
    }
});

Route::add('/api/template/(.*)', function ($who) {
    if (isset($_SESSION['loggedin'])) {
        echo file_get_contents("./themes/mirage/template_defs/" . $who . ".json");
    } else {
        header('HTTP/1.0 404 Not Found');
    }
});

Route::add('/api/page', function () {
    if (isset($_SESSION['loggedin'])) {
        global $pageStore;
        $allPages = $pageStore->findAll();
        $myJSON = json_encode($allPages);
        echo $myJSON;
    } else {
        header('HTTP/1.0 404 Not Found');
    }
});

Route::add('/api/page/collection/(.*)', function ($who) {
    if (isset($_SESSION['loggedin'])) {
        global $pageStore;
        $allPages = $pageStore->findBy(["collection", "=", $who]);
        $myJSON = json_encode($allPages);
        echo $myJSON;
    } else {
        header('HTTP/1.0 404 Not Found');
    }
});

Route::add('/api/page/([0-9]*)', function ($who) {
    if (isset($_SESSION['loggedin'])) {
        global $pageStore;
        $selectedPage = $pageStore->findById($who);
        $myJSON = json_encode($selectedPage);
        echo $myJSON;
    } else {
        header('HTTP/1.0 404 Not Found');
    }
});

Route::add('/api/page/([0-9]*)', function ($who) {
    if (isset($_SESSION['loggedin'])) {
        global $pageStore;

        $json = file_get_contents('php://input');
        $page = $pageStore->updateById($who, generatePage($json));
        $myJSON = json_encode($page);
        echo $myJSON;
    } else {
        header('HTTP/1.0 404 Not Found');
    }
}, 'POST');

Route::add('/api/page/([0-9]*)', function ($who) {
    if (isset($_SESSION['loggedin'])) {
        global $pageStore;
        $pageStore->deleteById($who);
    } else {
        header('HTTP/1.0 404 Not Found');
    }
}, 'DELETE');

Route::add('/api/page/generate', function () {
    if (isset($_SESSION['loggedin'])) {
        global $pageStore;

        $json = file_get_contents('php://input');
        $page = $pageStore->insert(generatePage($json));
        $myJSON = json_encode($page);
        echo $myJSON;
    } else {
        header('HTTP/1.0 404 Not Found');
    }
}, 'POST');

Route::add('(.*)', function ($who) {
    global $pageStore;
    global $siteTitle;
    $page = $pageStore->findOneBy(["path", "=", $who]);
    if ($page == null || ($page["private"] == true && !isset($_SESSION['loggedin']))) {
        header('HTTP/1.0 404 Not Found');
    } else {
        include './themes/mirage/' . $page["templateName"] . ".php";
    }
});


Route::run(BASEPATH);

?>