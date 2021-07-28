<?php

require_once '../SleekDB/src/Store.php';

use SleekDB\Store;

$databaseDirectory = "../database";
$pageStore = new Store("pages", $databaseDirectory);

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
$page["type"] = "page";

$page = $pageStore->insert($page);

?>