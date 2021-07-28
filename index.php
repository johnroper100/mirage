<?php

use Steampixel\Route;
use SleekDB\Store;

include 'simplePHPRouter/src/Steampixel/Route.php';
require_once './SleekDB/src/Store.php';
require './mustache/src/Mustache/Autoloader.php';

define('BASEPATH', '/mirage');

Mustache_Autoloader::register();
$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/templates/mirage'),
));

$databaseDirectory = __DIR__ . "/database";
$pageStore = new Store("pages", $databaseDirectory);

Route::add('/', function() {
    echo 'Welcome :-)';
});

Route::add('/admin', function() {
    echo 'admin';
});

Route::add('/(.*)', function($who) {
    global $pageStore, $m;
    $page = $pageStore->findOneBy(["path", "=", $who]);
    if ($page == null) {

    } else {
        echo $m->render($page["templateName"], $page);
    }
});

Route::run(BASEPATH);

?>