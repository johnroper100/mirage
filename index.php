<?php

use Steampixel\Route;
use SleekDB\Store;

include 'simplePHPRouter/src/Steampixel/Route.php';
require_once './SleekDB/src/Store.php';
require './mustache/src/Mustache/Autoloader.php';

define('BASEPATH', '/mirage');

Mustache_Autoloader::register();
$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/themes/mirage'),
));

$databaseDirectory = __DIR__ . "/database";
$pageStore = new Store("pages", $databaseDirectory);

Route::add('/admin', function () {
    include "admin.php";
});

Route::add('/api/theme', function () {
    echo file_get_contents("./themes/mirage/config.json");
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
    $pageStore->deleteById($who);
}, 'DELETE');

Route::add('(.*)', function ($who) {
    global $pageStore, $m;
    $page = $pageStore->findOneBy(["path", "=", $who]);
    if ($page == null) {
        if ($who == "/") {
            header("Location: admin");
        } else {
            header('HTTP/1.0 404 Not Found');
        }
    } else {
        echo $m->render($page["templateName"], $page);
    }
});


Route::run(BASEPATH);
