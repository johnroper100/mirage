<?php

if (!file_exists(".htaccess")) {
    $myfile = fopen(".htaccess", "w") or die("Unable to open file!");
    $txt = "DirectoryIndex index.php\n";
    fwrite($myfile, $txt);
    // Enable apache rewrite engine
    $txt = "RewriteEngine on\n";
    fwrite($myfile, $txt);
    // Set the rewrite base
    $txt = "RewriteBase " . dirname($_SERVER[PHP_SELF]) . "\n";
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
require './mustache/src/Mustache/Autoloader.php';

define('BASEPATH', dirname($_SERVER[PHP_SELF]));

Mustache_Autoloader::register();
$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/themes/mirage'),
));

$databaseDirectory = __DIR__ . "/database";
$pageStore = new Store("pages", $databaseDirectory);
$userStore = new Store("users", $databaseDirectory);

Route::add('/admin', function () {
    if (isset($_SESSION['loggedin'])) {
        include "admin.php";
    } else {
        header('Location: '.dirname($_SERVER[PHP_SELF]).'/login');
    }
});

Route::add('/login', function () {
    include "login.php";
});

Route::add('/api/theme', function () {
    echo file_get_contents("./themes/mirage/config.json");
});

Route::add('/api/template/(.*)', function ($who) {
    echo file_get_contents("./themes/mirage/template_defs/" . $who . ".json");
});

Route::add('/api/page', function () {
    global $pageStore;
    $allPages = $pageStore->findAll();
    $myJSON = json_encode($allPages);
    echo $myJSON;
});

Route::add('/api/page/collection/(.*)', function ($who) {
    global $pageStore;
    $allPages = $pageStore->findBy(["collection", "=", $who]);
    $myJSON = json_encode($allPages);
    echo $myJSON;
});

Route::add('/api/page/([0-9]*)', function ($who) {
    global $pageStore;
    $selectedPage = $pageStore->findById($who);
    $myJSON = json_encode($selectedPage);
    echo $myJSON;
});

Route::add('/api/page/([0-9]*)', function ($who) {
    global $pageStore;

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $page = [];

    foreach ($data["template"]["sections"] as $section) {
        foreach ($section["fields"] as $field) {
            $page[$field['id']] = $field['value'];
        }
    }

    $page["templateName"] = $data["templateName"];
    $page["title"] = $data["title"];
    $page["path"] = $data["path"];
    $page["collection"] = $data["collection"];
    $page["draft"] = $data["draft"];

    $page = $pageStore->updateById($who, $page);
    $myJSON = json_encode($page);
    echo $myJSON;
}, 'POST');

Route::add('/api/page/([0-9]*)', function ($who) {
    global $pageStore;
    $pageStore->deleteById($who);
}, 'DELETE');

Route::add('/api/page/generate', function () {
    global $pageStore;

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $page = [];

    foreach ($data["template"]["sections"] as $section) {
        foreach ($section["fields"] as $field) {
            $page[$field['id']] = $field['value'];
        }
    }

    $page["templateName"] = $data["templateName"];
    $page["title"] = $data["title"];
    $page["path"] = $data["path"];
    $page["collection"] = $data["collection"];
    $page["draft"] = $data["draft"];

    $page = $pageStore->insert($page);
    $myJSON = json_encode($page);
    echo $myJSON;
}, 'POST');

Route::add('(.*)', function ($who) {
    global $pageStore, $m;
    $page = $pageStore->findOneBy(["path", "=", $who]);
    if ($page == null || ($page["draft"] == true && !isset($_SESSION['loggedin']))) {
        header('HTTP/1.0 404 Not Found');
    } else {
        $page["basepath"] = dirname($_SERVER[PHP_SELF]);
        echo $m->render($page["templateName"], $page);
    }
});


Route::run(BASEPATH);

?>