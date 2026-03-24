<?php

$isHttpsRequest = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttpsRequest,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
} else {
    session_set_cookie_params(0, '/; samesite=Lax', '', $isHttpsRequest, true);
}

session_start();

if (!isset($_SESSION['sessionStartedAt'])) {
    session_regenerate_id(true);
    $_SESSION['sessionStartedAt'] = time();
}

define('MIRAGE_ROOT', dirname(__DIR__));
define('MIRAGE_VERSION', "1.3.0");

# Define the site root (used in the backend and frontend)
define('ORIGBASEPATH', dirname($_SERVER['PHP_SELF']));
if (ORIGBASEPATH == "/") {
    define('BASEPATH', "");
} else {
    define('BASEPATH', ORIGBASEPATH);
}

# Generate .htaccess to block database from view and enable url rewrite
$htaccessPath = MIRAGE_ROOT . DIRECTORY_SEPARATOR . '.htaccess';
if (!file_exists($htaccessPath)) {
    $myfile = fopen($htaccessPath, "w") or die("Unable to open file!");
    $txt = "Options -Indexes\n";
    fwrite($myfile, $txt);
    $txt = "DirectoryIndex index.php\n";
    fwrite($myfile, $txt);
    // Enable apache rewrite engine
    $txt = "RewriteEngine on\n";
    fwrite($myfile, $txt);
    // Set the rewrite base
    $txt = "RewriteBase " . ORIGBASEPATH . "\n";
    fwrite($myfile, $txt);
    // Deliver the folder or file directly if it exists on the server
    $txt = "RewriteRule ^database/?$ - [F,L]\n";
    fwrite($myfile, $txt);
    $txt = "RewriteRule ^dashboard/?$ - [F,L]\n";
    fwrite($myfile, $txt);
    $txt = "RewriteRule ^uploads/.*\\.(?:php[0-9]?|phtml|phar|cgi|pl|asp|aspx|jsp|sh|bat|cmd)$ - [F,NC,L]\n";
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

use SleekDB\Store;

include MIRAGE_ROOT . '/simplePHPRouter/src/Steampixel/Route.php';
require_once MIRAGE_ROOT . '/SleekDB/src/Store.php';
include MIRAGE_ROOT . '/php-image-resize/lib/ImageResize.php';

$sleekDBConfiguration = [
    "timeout" => false
];

$databaseDirectory = MIRAGE_ROOT . "/database";
$pageStore = new Store("pages", $databaseDirectory, $sleekDBConfiguration);
$userStore = new Store("users", $databaseDirectory, $sleekDBConfiguration);
$mediaStore = new Store("mediaItems", $databaseDirectory, $sleekDBConfiguration);
$menuStore = new Store("menuItems", $databaseDirectory, $sleekDBConfiguration);
$formStore = new Store("formSubmissions", $databaseDirectory, $sleekDBConfiguration);
$formAttemptStore = new Store("formAttempts", $databaseDirectory, $sleekDBConfiguration);
$analyticsEventStore = new Store("analyticsEvents", $databaseDirectory, $sleekDBConfiguration);

