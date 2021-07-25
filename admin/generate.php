<?php

require_once './SleekDB/src/Store.php';
require './mustache/src/Mustache/Autoloader.php';

use SleekDB\Store;

Mustache_Autoloader::register();
$m = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__) . '/templates/mirage'),
));

$databaseDirectory = __DIR__ . "/database";
$pageStore = new Store("pages", $databaseDirectory);

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$page = [];

foreach ($data["sections"] as $section) {
    foreach ($section["fields"] as $field) {
        $page[$field['id']] = $field['value'];
    }
}

$myfile = fopen("../test.html", "w") or die("Unable to open file!");
fwrite($myfile, $m->render($data["template"], $page));
fclose($myfile);

?>