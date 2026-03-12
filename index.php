<?php

session_start();

define('MIRAGE_VERSION', "1.1.0");

# Define the site root (used in the backend and frontend)
define('ORIGBASEPATH', dirname($_SERVER['PHP_SELF']));
if (ORIGBASEPATH == "/") {
    define('BASEPATH', "");
} else {
    define('BASEPATH', ORIGBASEPATH);
}

# Generate .htaccess to block database from view and enable url rewrite
if (!file_exists(".htaccess")) {
    $myfile = fopen(".htaccess", "w") or die("Unable to open file!");
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
include 'php-image-resize/lib/ImageResize.php';

use \Gumlet\ImageResize;

$sleekDBConfiguration = [
    "timeout" => false
];

$databaseDirectory = __DIR__ . "/database";
$pageStore = new Store("pages", $databaseDirectory, $sleekDBConfiguration);
$userStore = new Store("users", $databaseDirectory, $sleekDBConfiguration);
$mediaStore = new Store("mediaItems", $databaseDirectory, $sleekDBConfiguration);
$menuStore = new Store("menuItems", $databaseDirectory, $sleekDBConfiguration);
$formStore = new Store("formSubmissions", $databaseDirectory, $sleekDBConfiguration);

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function getFullBasepath() {
    return htmlspecialchars((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . BASEPATH, ENT_QUOTES, 'UTF-8');
}

function parseIniSizeToBytes($size)
{
    $size = trim((string) $size);
    if ($size === '') {
        return 0;
    }

    $unit = strtolower(substr($size, -1));
    $bytes = (float) $size;

    switch ($unit) {
        case 'g':
            $bytes *= 1024;
        case 'm':
            $bytes *= 1024;
        case 'k':
            $bytes *= 1024;
    }

    return (int) round($bytes);
}

function formatBytes($bytes)
{
    $bytes = (int) $bytes;
    if ($bytes <= 0) {
        return '0 B';
    }

    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $power = (int) floor(log($bytes, 1024));
    $power = min($power, count($units) - 1);
    $value = $bytes / (1024 ** $power);
    $precision = $value >= 10 || $power === 0 ? 0 : 1;

    return rtrim(rtrim(number_format($value, $precision), '0'), '.') . ' ' . $units[$power];
}

function getPostMaxSizeBytes()
{
    return parseIniSizeToBytes(ini_get('post_max_size'));
}

function getUploadFileLimitBytes()
{
    $uploadMaxBytes = parseIniSizeToBytes(ini_get('upload_max_filesize'));
    $postMaxBytes = getPostMaxSizeBytes();

    if ($uploadMaxBytes > 0 && $postMaxBytes > 0) {
        return min($uploadMaxBytes, $postMaxBytes);
    }

    if ($uploadMaxBytes > 0) {
        return $uploadMaxBytes;
    }

    return $postMaxBytes;
}

function getUploadTooLargeMessage()
{
    $fileLimitBytes = getUploadFileLimitBytes();
    $postLimitBytes = getPostMaxSizeBytes();

    if ($fileLimitBytes > 0 && $postLimitBytes > 0 && $fileLimitBytes !== $postLimitBytes) {
        return 'Upload is too large. Each file must be ' . formatBytes($fileLimitBytes) . ' or smaller, and the total upload must stay under ' . formatBytes($postLimitBytes) . '.';
    }

    if ($fileLimitBytes > 0) {
        return 'Upload is too large. The maximum allowed size is ' . formatBytes($fileLimitBytes) . '.';
    }

    return 'Upload is too large.';
}

function requestExceededPostMaxSize()
{
    $contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
    $postMaxBytes = getPostMaxSizeBytes();

    return $postMaxBytes > 0 && $contentLength > $postMaxBytes;
}

function sendJsonResponse($response, $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($response);
}

function getUploadErrorMessage($errorCode)
{
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'File is too large to upload. The maximum allowed size is ' . formatBytes(getUploadFileLimitBytes()) . '.';
        case UPLOAD_ERR_PARTIAL:
            return 'Upload did not complete. Please try again.';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was selected.';
        case UPLOAD_ERR_NO_TMP_DIR:
        case UPLOAD_ERR_CANT_WRITE:
        case UPLOAD_ERR_EXTENSION:
            return 'The server could not save the uploaded file. Please try again.';
        default:
            return 'Upload failed. Please try again.';
    }
}

# Generate page field
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

# Function used for generating a page document from input information and page config
function generatePage($json, $isNewPage = false)
{
    $data = json_decode($json, true);
    $page = [];
    $page["content"] = [];
    $page["editedUser"] = $_SESSION['id']; // could be null if user has been deleted
    $page["edited"] = time();
    if ($isNewPage) {
        $page["createdUser"] = $page["editedUser"]; // could be null if user has been deleted
        $page["created"] = $page["edited"];
    }

    foreach ($data["template"]["sections"] as $section) {
        foreach ($section["fields"] as $field) {
            if (isset($field['value'])) {
                $page["content"][$field['id']] = generateField($field);
            }
        }
    }

    $page["templateName"] = $data["templateName"];
    $page["title"] = $data["title"];
    $page["featuredImage"] = $data["featuredImage"];
    $page["description"] = $data["description"];
    $page["path"] = $data["path"];
    $page["isPathless"] = $data["isPathless"];
    $page["collection"] = $data["collection"];
    $page["collectionSubpath"] = $data["collectionSubpath"] ?? "";
    $page["isPublished"] = $data["isPublished"];
    return $page;
};

# Return error messages
function getErrorPage($errorCode)
{
    http_response_code($errorCode);
    $errorMessage = "we will look into the issue and get it fixed as soon as possible, maybe try reloading the page";
    if ($errorCode == 404) {
        $errorMessage = "you've lost your way, you may have attempted to get to a page that doesn't exist";
    } else if ($errorCode == 401) {
        $errorMessage = "you don't have permission to access this page";
    }
    if (file_exists("./theme/error.php")) {
        include "./theme/error.php";
    } else {
        include "./dashboard/error.php";
    }
};

function getPages($collection, $numEntries, $sort = ["created" => "desc"])
{
    global $pageStore;
    if ($numEntries > 0) {
        if (isset($_SESSION['loggedin'])) {
            $pages = $pageStore->findBy(["collection", "=", $collection], $sort, $numEntries);
        } else {
            $pages = $pageStore->findBy([["collection", "=", $collection], ["isPublished", "=", true]], $sort, $numEntries);
        }
    } else {
        if (isset($_SESSION['loggedin'])) {
            $pages = $pageStore->findBy(["collection", "=", $collection], $sort);
        } else {
            $pages = $pageStore->findBy([["collection", "=", $collection], ["isPublished", "=", true]], $sort);
        }
    }
    return $pages;
};

function generateMenuItemID()
{
    return str_replace('.', '', uniqid('menu_', true));
}

function normalizeMenuItemValue($value)
{
    if ($value === '' || $value === null) {
        return null;
    }

    if (is_numeric($value)) {
        return (int) $value;
    }

    return $value;
}

function normalizeMenuItem($menuItem, $fallbackItemID = null)
{
    $itemID = $menuItem['itemID'] ?? ($menuItem['_id'] ?? $fallbackItemID ?? generateMenuItemID());
    $parentItemID = $menuItem['parentItemID'] ?? null;
    if ($parentItemID === '') {
        $parentItemID = null;
    }

    $normalized = [
        'menuID' => isset($menuItem['menuID']) ? (string) $menuItem['menuID'] : '',
        'itemID' => (string) $itemID,
        'parentItemID' => $parentItemID === null ? null : (string) $parentItemID,
        'name' => isset($menuItem['name']) ? (string) $menuItem['name'] : '',
        'type' => isset($menuItem['type']) ? (int) $menuItem['type'] : 0,
        'page' => normalizeMenuItemValue($menuItem['page'] ?? null),
        'link' => isset($menuItem['link']) ? trim((string) $menuItem['link']) : '',
        'order' => isset($menuItem['order']) ? (int) $menuItem['order'] : 0
    ];

    if (isset($menuItem['_id'])) {
        $normalized['_id'] = $menuItem['_id'];
    }

    return $normalized;
}

function menuItemCreatesCycle($itemID, $parentItemID, $itemsByID)
{
    $visited = [$itemID => true];
    $currentParentID = $parentItemID;

    while ($currentParentID !== null) {
        if (isset($visited[$currentParentID])) {
            return true;
        }

        $visited[$currentParentID] = true;

        if (!isset($itemsByID[$currentParentID])) {
            return false;
        }

        $currentParentID = $itemsByID[$currentParentID]['parentItemID'] ?? null;
    }

    return false;
}

function buildNormalizedMenuItems($menuItems)
{
    if (!is_array($menuItems)) {
        return [];
    }

    $normalizedMenuItems = [];
    foreach ($menuItems as $index => $menuItem) {
        $normalized = normalizeMenuItem($menuItem, 'legacy_' . $index);
        $normalizedMenuItems[] = $normalized;
    }

    $itemsByID = [];
    foreach ($normalizedMenuItems as $menuItem) {
        $itemsByID[$menuItem['itemID']] = $menuItem;
    }

    foreach ($normalizedMenuItems as &$menuItem) {
        $parentItemID = $menuItem['parentItemID'];
        if (
            $parentItemID !== null
            && (
                !isset($itemsByID[$parentItemID])
                || $itemsByID[$parentItemID]['menuID'] !== $menuItem['menuID']
                || menuItemCreatesCycle($menuItem['itemID'], $parentItemID, $itemsByID)
            )
        ) {
            $menuItem['parentItemID'] = null;
        }
    }
    unset($menuItem);

    usort($normalizedMenuItems, function ($a, $b) {
        $menuCompare = strcmp($a['menuID'], $b['menuID']);
        if ($menuCompare !== 0) {
            return $menuCompare;
        }

        return $a['order'] <=> $b['order'];
    });

    return $normalizedMenuItems;
}

function getAllMenuItems()
{
    global $menuStore;

    return buildNormalizedMenuItems($menuStore->findAll());
}

function getMenuItems($menuID)
{
    $allMenuItems = getAllMenuItems();

    return array_values(array_filter($allMenuItems, function ($menuItem) use ($menuID) {
        return $menuItem['menuID'] === $menuID;
    }));
};

function getMenuTreeBranch($menuItems, $parentItemID = null)
{
    $branch = [];
    foreach ($menuItems as $menuItem) {
        if (($menuItem['parentItemID'] ?? null) !== $parentItemID) {
            continue;
        }

        $menuItem['children'] = getMenuTreeBranch($menuItems, $menuItem['itemID']);
        $branch[] = $menuItem;
    }

    usort($branch, function ($a, $b) {
        return $a['order'] <=> $b['order'];
    });

    return $branch;
}

function getMenuTree($menuID)
{
    return getMenuTreeBranch(getMenuItems($menuID));
}

function resolveMenuItemLink($menuItem)
{
    global $pageStore;

    if ((int) ($menuItem['type'] ?? 0) !== 0) {
        return trim((string) ($menuItem['link'] ?? ''));
    }

    $pageID = normalizeMenuItemValue($menuItem['page'] ?? null);
    if ($pageID === null) {
        return '';
    }

    $page = $pageStore->findById($pageID);
    if ($page == null) {
        return '';
    }

    $link = $page['path'];
    if (isset($page['collectionSubpath']) && $page['collectionSubpath'] !== '') {
        $link = $page['collectionSubpath'] . '/' . $link;
    }

    return $link;
}

function prepareMenuItemsForStore($menuItems)
{
    $normalizedMenuItems = buildNormalizedMenuItems($menuItems);
    $menuIndexes = [];

    foreach ($normalizedMenuItems as &$menuItem) {
        if (!isset($menuIndexes[$menuItem['menuID']])) {
            $menuIndexes[$menuItem['menuID']] = 0;
        }

        $menuItem['order'] = $menuIndexes[$menuItem['menuID']];
        $menuItem['link'] = resolveMenuItemLink($menuItem);
        $menuIndexes[$menuItem['menuID']]++;
    }
    unset($menuItem);

    return $normalizedMenuItems;
}

function reparentChildMenuItems($itemID, $newParentItemID = null)
{
    global $menuStore;

    $childMenuItems = $menuStore->findBy(['parentItemID', '=', $itemID]);
    foreach ($childMenuItems as $childMenuItem) {
        $menuStore->updateById($childMenuItem['_id'], [
            'parentItemID' => $newParentItemID
        ]);
    }
}

function getMenuItemUrl($menuItem)
{
    $link = trim((string) ($menuItem['link'] ?? ''));
    if ((int) ($menuItem['type'] ?? 0) === 1) {
        return $link;
    }

    if ($link === '') {
        return BASEPATH . '/';
    }

    return BASEPATH . '/' . ltrim($link, '/');
}

function appendHtmlClass($attributes, $className)
{
    $className = trim($className);
    if ($className === '') {
        return $attributes;
    }

    $existingClass = isset($attributes['class']) ? trim((string) $attributes['class']) : '';
    $attributes['class'] = trim($existingClass . ' ' . $className);

    return $attributes;
}

function buildHtmlAttributes($attributes)
{
    if (!is_array($attributes) || count($attributes) === 0) {
        return '';
    }

    $attributePairs = [];
    foreach ($attributes as $name => $value) {
        if ($value === null || $value === false) {
            continue;
        }

        $escapedName = htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8');
        if ($value === true) {
            $attributePairs[] = $escapedName;
            continue;
        }

        $attributePairs[] = $escapedName . '="' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '"';
    }

    if (count($attributePairs) === 0) {
        return '';
    }

    return ' ' . implode(' ', $attributePairs);
}

function applyActiveStateToMenuItems($menuItems, $currentPageID = null)
{
    $itemsWithState = [];
    foreach ($menuItems as $menuItem) {
        $menuItem['children'] = applyActiveStateToMenuItems($menuItem['children'] ?? [], $currentPageID);
        $menuItem['isCurrent'] = $currentPageID !== null
            && (int) ($menuItem['type'] ?? 0) === 0
            && (string) ($menuItem['page'] ?? '') === (string) $currentPageID;
        $menuItem['isActive'] = $menuItem['isCurrent'];

        foreach ($menuItem['children'] as $childMenuItem) {
            if (!empty($childMenuItem['isActive'])) {
                $menuItem['isActive'] = true;
                break;
            }
        }

        $itemsWithState[] = $menuItem;
    }

    return $itemsWithState;
}

function renderMenuListHtml($menuItems, $options, $isRoot = false)
{
    if (count($menuItems) === 0) {
        return '';
    }

    $listAttributes = $isRoot ? ($options['listAttributes'] ?? []) : ($options['submenuAttributes'] ?? []);
    if ($isRoot) {
        $listAttributes = appendHtmlClass($listAttributes, 'mirage-menu');
        $listAttributes = appendHtmlClass($listAttributes, $options['listClass'] ?? '');
        $listAttributes['data-mirage-menu'] = $options['menuID'];
    } else {
        $listAttributes = appendHtmlClass($listAttributes, 'mirage-menu__submenu');
        $listAttributes = appendHtmlClass($listAttributes, $options['submenuClass'] ?? '');
    }

    $html = '<ul' . buildHtmlAttributes($listAttributes) . '>';
    foreach ($menuItems as $menuItem) {
        $itemAttributes = [
            'data-menu-item-id' => $menuItem['itemID']
        ];
        $itemAttributes = appendHtmlClass($itemAttributes, 'mirage-menu__item');
        $itemAttributes = appendHtmlClass($itemAttributes, $options['itemClass'] ?? '');
        if (!empty($menuItem['isActive'])) {
            $itemAttributes = appendHtmlClass($itemAttributes, 'mirage-menu__item--active');
            $itemAttributes = appendHtmlClass($itemAttributes, $options['activeItemClass'] ?? '');
        }
        if (!empty($menuItem['children'])) {
            $itemAttributes = appendHtmlClass($itemAttributes, 'mirage-menu__item--has-children');
            $itemAttributes = appendHtmlClass($itemAttributes, $options['hasChildrenItemClass'] ?? '');
        }

        $linkAttributes = $options['linkAttributes'] ?? [];
        $linkAttributes['href'] = getMenuItemUrl($menuItem);
        $linkAttributes = appendHtmlClass($linkAttributes, 'mirage-menu__link');
        $linkAttributes = appendHtmlClass($linkAttributes, $options['linkClass'] ?? '');
        if (!empty($menuItem['isCurrent'])) {
            $linkAttributes['aria-current'] = 'page';
        }
        if ((int) ($menuItem['type'] ?? 0) === 1) {
            if (!isset($linkAttributes['target'])) {
                $linkAttributes['target'] = '_blank';
            }
            if (!isset($linkAttributes['rel'])) {
                $linkAttributes['rel'] = 'noopener noreferrer';
            }
        }

        $html .= '<li' . buildHtmlAttributes($itemAttributes) . '>';
        $html .= '<a' . buildHtmlAttributes($linkAttributes) . '>' . htmlspecialchars($menuItem['name'], ENT_QUOTES, 'UTF-8') . '</a>';

        if (!empty($menuItem['children'])) {
            $buttonAttributes = $options['buttonAttributes'] ?? [];
            $buttonAttributes['type'] = 'button';
            $buttonAttributes['aria-expanded'] = !empty($menuItem['isActive']) ? 'true' : 'false';
            $buttonAttributes['aria-label'] = $options['submenuToggleLabel'] ?? 'Toggle submenu';
            $buttonAttributes = appendHtmlClass($buttonAttributes, 'mirage-menu__toggle');
            $buttonAttributes = appendHtmlClass($buttonAttributes, $options['buttonClass'] ?? '');

            $html .= '<button' . buildHtmlAttributes($buttonAttributes) . '><span aria-hidden="true">&#9662;</span></button>';

            $submenuAttributes = $options['submenuAttributes'] ?? [];
            if (empty($menuItem['isActive'])) {
                $submenuAttributes['hidden'] = true;
            }

            $submenuOptions = $options;
            $submenuOptions['submenuAttributes'] = $submenuAttributes;
            $html .= renderMenuListHtml($menuItem['children'], $submenuOptions, false);
        }

        $html .= '</li>';
    }
    $html .= '</ul>';

    return $html;
}

function renderMenu($menuID, $options = [])
{
    $options['menuID'] = $menuID;
    $menuTree = applyActiveStateToMenuItems(getMenuTree($menuID), $options['currentPageID'] ?? null);

    return renderMenuListHtml($menuTree, $options, true);
}

function getMedia($mediaID)
{
    global $mediaStore;
    $media = $mediaStore->findById($mediaID);

    return $media;
};

function getUsers($numEntries)
{
    global $userStore;
    $users = $userStore->createQueryBuilder()->select(['name', 'email', 'accountType', 'notifySubmissions', 'bio']);
    if ($numEntries > 0) {
        $users = $users->limit($numEntries);
    }
    $users = $users->getQuery()->fetch();

    return $users;
};

function getUser($userID)
{
    global $userStore;
    $user = $userStore->createQueryBuilder()->where([ "_id", "=", $userID ] )->select(['name', 'email', 'accountType', 'notifySubmissions', 'bio'])->limit(1)->getQuery()->fetch()[0];

    return $user;
};

function getFirstParagraph($string) {
    return substr($string, strpos($string, "<p"), strpos($string, "</p>")+4);
}

function appendQueryParam($url, $key, $value)
{
    if ($url == null || $url == "") {
        return BASEPATH . '/';
    }

    $separator = strpos($url, '?') === false ? '?' : '&';
    return $url . $separator . rawurlencode($key) . '=' . rawurlencode($value);
}

function getFormReferer()
{
    if (!isset($_SERVER["HTTP_REFERER"]) || $_SERVER["HTTP_REFERER"] == "") {
        return BASEPATH . '/';
    }

    return $_SERVER["HTTP_REFERER"];
}

function getClientIpAddress()
{
    if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== '') {
        return $_SERVER['REMOTE_ADDR'];
    }

    return 'unknown';
}

function getSpamProtectionSession()
{
    if (!isset($_SESSION['formSpamProtection']) || !is_array($_SESSION['formSpamProtection'])) {
        $_SESSION['formSpamProtection'] = [];
    }

    return $_SESSION['formSpamProtection'];
}

function buildFormSpamProtection($formID)
{
    $spamProtection = getSpamProtectionSession();

    if (
        !isset($spamProtection[$formID])
        || !isset($spamProtection[$formID]['token'])
        || !isset($spamProtection[$formID]['generatedAt'])
        || (time() - (int) $spamProtection[$formID]['generatedAt']) > 7200
    ) {
        $spamProtection[$formID] = [
            'token' => bin2hex(random_bytes(16)),
            'generatedAt' => time()
        ];
    }

    $_SESSION['formSpamProtection'] = $spamProtection;

    return $spamProtection[$formID];
}

function extractFormIdFromAction($action)
{
    $matches = [];
    if (preg_match('~(?:^|/)form/([^/?#"\']+)~', $action, $matches) !== 1) {
        return null;
    }

    return rawurldecode($matches[1]);
}

function injectSpamProtectionIntoForms($html)
{
    return preg_replace_callback('/<form\b[^>]*\baction\s*=\s*(["\'])([^"\']+)\1[^>]*>/i', function ($matches) {
        $formID = extractFormIdFromAction($matches[2]);
        if ($formID == null) {
            return $matches[0];
        }

        $protection = buildFormSpamProtection($formID);
        $injectedFields = '<div aria-hidden="true" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;">'
            . '<label>Leave this field empty'
            . '<input type="text" name="_mirage_website" tabindex="-1" autocomplete="off">'
            . '</label>'
            . '</div>'
            . '<input type="hidden" name="_mirage_form_token" value="' . htmlspecialchars($protection['token'], ENT_QUOTES, 'UTF-8') . '">';

        return $matches[0] . $injectedFields;
    }, $html);
}

function injectMirageFrontendAssets($html)
{
    if (strpos($html, 'data-mirage-menu') === false) {
        return $html;
    }

    $menuStylesheet = '<link rel="stylesheet" href="' . htmlspecialchars(BASEPATH . '/assets/css/mirage-menu.css', ENT_QUOTES, 'UTF-8') . '">';
    $menuScript = '<script src="' . htmlspecialchars(BASEPATH . '/assets/js/mirage-menu.js', ENT_QUOTES, 'UTF-8') . '"></script>';

    if (strpos($html, 'mirage-menu.css') === false) {
        if (stripos($html, '</head>') !== false) {
            $html = preg_replace('/<\/head>/i', $menuStylesheet . "\n</head>", $html, 1);
        } else {
            $html = $menuStylesheet . "\n" . $html;
        }
    }

    if (strpos($html, 'mirage-menu.js') === false) {
        if (stripos($html, '</body>') !== false) {
            $html = preg_replace('/<\/body>/i', $menuScript . "\n</body>", $html, 1);
        } else {
            $html .= "\n" . $menuScript;
        }
    }

    return $html;
}

function includeThemeFile($filename, $data = [])
{
    global $siteTitle;

    if (!isset($data['page']) || !is_array($data['page'])) {
        $data['page'] = [];
    }

    if (!array_key_exists('siteTitle', $data)) {
        $data['siteTitle'] = isset($siteTitle) ? $siteTitle : '';
    }

    extract($data, EXTR_SKIP);

    ob_start();
    include $filename;
    $output = ob_get_clean();

    $output = injectSpamProtectionIntoForms($output);
    echo injectMirageFrontendAssets($output);
}

function getRecentFormSubmission($formID, $ipAddress)
{
    global $formStore;

    $recentSubmissions = $formStore->findBy([
        ["form", "=", $formID],
        ["ipAddress", "=", $ipAddress]
    ], ["created" => "desc"], 1);

    if (!is_array($recentSubmissions) || count($recentSubmissions) === 0) {
        return null;
    }

    return $recentSubmissions[0];
}

function isSpamSubmission($formID)
{
    $spamProtection = getSpamProtectionSession();
    $storedProtection = isset($spamProtection[$formID]) ? $spamProtection[$formID] : null;
    $submittedToken = isset($_POST['_mirage_form_token']) ? trim($_POST['_mirage_form_token']) : '';
    $honeypotValue = isset($_POST['_mirage_website']) ? trim($_POST['_mirage_website']) : '';

    if ($honeypotValue !== '') {
        return true;
    }

    if ($storedProtection == null || $submittedToken === '' || !hash_equals($storedProtection['token'], $submittedToken)) {
        return true;
    }

    $secondsSinceRendered = time() - $storedProtection['generatedAt'];
    if ($secondsSinceRendered < 2 || $secondsSinceRendered > 7200) {
        return true;
    }

    $recentSubmission = getRecentFormSubmission($formID, getClientIpAddress());
    if ($recentSubmission != null && isset($recentSubmission['created']) && (time() - (int) $recentSubmission['created']) < 300) {
        return true;
    }

    return false;
}

# Run setup if config.php does not yet exist
if (!file_exists("config.php")) {
    Route::add('/setup', function () {
        global $userStore;

        $user = [
            'name' => test_input($_POST["name"]),
            'email' => test_input($_POST["email"]),
            'bio' => "",
            'notifySubmissions' => 1,
            'password' => password_hash($_POST["password"], PASSWORD_DEFAULT),
            'accountType' => 0
        ];

        $user = $userStore->insert($user);

        $myfile = fopen("config.php", "w") or die("Unable to open file!");
        $txt = "<?php\n\n";
        fwrite($myfile, $txt);
        $txt = "\$siteTitle = \"" . test_input($_POST["siteTitle"]) . "\";\n";
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
# if config.php exists, run the rest of the application

    require_once 'config.php';

    define('THEMEPATH', BASEPATH . "/theme");

    /* Administation */

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

        $user = $userStore->findOneBy(["email", "=", test_input($_POST["email"])]);

        if ($user != null && password_verify($_POST["password"], $user['password'])) {
            session_regenerate_id();
            $_SESSION['loggedin'] = true;
            $_SESSION['name'] = $user['name'];
            $_SESSION['id'] = $user['_id'];
            $_SESSION['accountType'] = $user['accountType'];

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

    # Routes marked API are used by the backend to get data

    /* Theme and Admin UI */

    Route::add('/api/theme', function () {
        if (isset($_SESSION['loggedin'])) {
            echo file_get_contents("./theme/config.json");
        } else {
            getErrorPage(401);
        }
    });

    Route::add('/api/templates/(.*)', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            echo file_get_contents("./theme/template_defs/" . $who . ".json");
        } else {
            getErrorPage(401);
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
            getErrorPage(401);
        }
    });

    /* Users */

    Route::add('/api/users', function () {
        if (isset($_SESSION['loggedin'])) {
            global $userStore;
            echo json_encode($userStore->createQueryBuilder()->select(['name', 'email', 'accountType', 'notifySubmissions', 'bio'])->getQuery()->fetch());
        } else {
            getErrorPage(401);
        }
    });

    Route::add('/api/users/active', function () {
        if (isset($_SESSION['loggedin'])) {
            global $userStore;
            echo json_encode($userStore->createQueryBuilder()->select(['name', 'email', 'accountType', 'notifySubmissions', 'bio'])->where( [ "_id", "=", $_SESSION["id"] ] )->getQuery()->fetch()[0]);
        } else {
            getErrorPage(401);
        }
    });

    Route::add('/api/users', function () {
        if (isset($_SESSION['loggedin']) && $_SESSION['accountType'] != 2) {
            global $userStore;
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $user = [
                'name' => $data["name"],
                'email' => $data["email"],
                'bio' => "",
                'notifySubmissions' => 1,
                'password' => password_hash($data["password"], PASSWORD_DEFAULT),
                'accountType' => $data["accountType"]
            ];

            $user = $userStore->insert($user);
        } else {
            getErrorPage(401);
        }
    }, 'POST');

    Route::add('/api/users/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin']) && ($_SESSION['accountType'] != 2 || $_SESSION['id'] == $who)) {
            global $userStore;
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $user = [
                'name' => $data["name"],
                'email' => $data["email"],
                'bio' => $data["bio"],
                'notifySubmissions' => $data["notifySubmissions"],
                'accountType' => $data["accountType"]
            ];

            if (isset($data["password"]) && $data["password"] != "") {
                $user['password'] = password_hash($data["password"], PASSWORD_DEFAULT);
            }

            $user = $userStore->updateById($who, $user);
        } else {
            getErrorPage(401);
        }
    }, 'PUT');

    Route::add('/api/users/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin']) && $_SESSION['accountType'] != 2) {
            global $userStore;
            global $pageStore;
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
        } else {
            getErrorPage(401);
        }
    }, 'DELETE');

    /* Pages */

    Route::add('/api/collections/(.*)/pages', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            global $pageStore;
            $allPages = $pageStore->findBy(["collection", "=", $who]);
            $myJSON = json_encode($allPages);
            echo $myJSON;
        } else {
            getErrorPage(401);
        }
    });

    Route::add('/api/pages', function () {
        if (isset($_SESSION['loggedin'])) {
            global $pageStore;
            $allPages = $pageStore->findAll();
            $myJSON = json_encode($allPages);
            echo $myJSON;
        } else {
            getErrorPage(401);
        }
    });

    Route::add('/api/pages/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            global $pageStore;
            $selectedPage = $pageStore->findById($who);
            $myJSON = json_encode($selectedPage);
            echo $myJSON;
        } else {
            getErrorPage(401);
        }
    });

    Route::add('/api/pages', function () {
        if (isset($_SESSION['loggedin'])) {
            global $pageStore;

            $json = file_get_contents('php://input');
            $page = $pageStore->insert(generatePage($json, true));
            $myJSON = json_encode($page);
            echo $myJSON;
        } else {
            getErrorPage(401);
        }
    }, 'POST');

    Route::add('/api/pages/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            global $pageStore;
            global $menuStore;

            $json = file_get_contents('php://input');
            $page = $pageStore->updateById($who, generatePage($json));
            $myJSON = json_encode($page);

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

            echo $myJSON;
        } else {
            getErrorPage(401);
        }
    }, 'PUT');

    Route::add('/api/pages/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            global $pageStore;
            global $menuStore;
            $allMenuItems = $menuStore->findAll();
            foreach ($allMenuItems as &$menuItem) {
                if ($menuItem["type"] == 0 && $menuItem["page"] == $who) {
                    $normalizedMenuItem = normalizeMenuItem($menuItem);
                    reparentChildMenuItems($normalizedMenuItem["itemID"], $normalizedMenuItem["parentItemID"]);
                    $menuStore->deleteById($menuItem["_id"]);
                }
            }
            $pageStore->deleteById($who);
        } else {
            getErrorPage(401);
        }
    }, 'DELETE');

    /* Menus */

    Route::add('/api/menus', function () {
        if (isset($_SESSION['loggedin'])) {
            $allMenuItems = getAllMenuItems();
            $myJSON = json_encode($allMenuItems);
            echo $myJSON;
        } else {
            getErrorPage(401);
        }
    });

    Route::add('/api/menus', function () {
        if (isset($_SESSION['loggedin'])) {
            global $menuStore;

            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $data = prepareMenuItemsForStore($data);

            $menuStore->createQueryBuilder()->getQuery()->delete();
            if (count($data) > 0) {
                $menuStore->insertMany($data);
            }

            $allMenuItems = getAllMenuItems();
            $myJSON = json_encode($allMenuItems);
            echo $myJSON;
        } else {
            getErrorPage(401);
        }
    }, 'POST');

    /* Media */

    Route::add('/api/media', function () {
        if (isset($_SESSION['loggedin'])) {
            global $mediaStore;
            $allMedia = $mediaStore->findAll();
            $myJSON = json_encode($allMedia);
            echo $myJSON;
        } else {
            getErrorPage(401);
        }
    });

    Route::add('/api/media', function () {
        if (isset($_SESSION['loggedin'])) {
            global $mediaStore;

            if (requestExceededPostMaxSize()) {
                sendJsonResponse([
                    'success' => false,
                    'message' => getUploadTooLargeMessage(),
                ], 413);
                return;
            }

            if (!file_exists('./uploads')) {
                mkdir('./uploads');
            }

            if (!isset($_FILES['uploadMediaFiles']) || !isset($_FILES['uploadMediaFiles']['name'])) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'No file was selected.',
                ], 400);
                return;
            }

            $count = count($_FILES['uploadMediaFiles']['name']);
            $uploadedMedia = [];
            for ($i = 0; $i < $count; $i++) {
                $uploadError = $_FILES['uploadMediaFiles']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                if ($uploadError !== UPLOAD_ERR_OK) {
                    sendJsonResponse([
                        'success' => false,
                        'message' => getUploadErrorMessage($uploadError),
                    ], $uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE ? 413 : 400);
                    return;
                }

                if (!move_uploaded_file($_FILES['uploadMediaFiles']['tmp_name'][$i], "./uploads/" . $_FILES['uploadMediaFiles']['name'][$i])) {
                    sendJsonResponse([
                        'success' => false,
                        'message' => 'The server could not save the uploaded file. Please try again.',
                    ], 500);
                    return;
                } else {
                    $page = [];
                    $page['file'] = $_FILES['uploadMediaFiles']['name'][$i];
                    $page['fileSmall'] = $page['file'];
                    $page["caption"] = "";
                    $page['extension'] = pathinfo($page['file'], PATHINFO_EXTENSION);

                    $page["editedUser"] = $_SESSION['id'];
                    $page["edited"] = time();
                    $page["createdUser"] = $page["editedUser"];
                    $page["created"] = $page["edited"];

                    if (strtolower($page['extension']) == "png" || $page['extension'] == 'jpg' || $page['extension'] == 'gif' || $page['extension'] == 'jpeg' || $page['extension'] == 'svg') {
                        $page['type'] = "image";
                    } else {
                        $page['type'] = "file";
                    }

                    if ($page["type"] == "image") {
                        $image = new ImageResize("./uploads/" . $page['file']);
                        $image->resizeToWidth(500);
                        $image->save("./uploads/min_" . $page['file']);
                        $page['fileSmall'] = "min_" . $page['file'];
                    }

                    $page = $mediaStore->insert($page);
                    $uploadedMedia[] = $page;
                }
            }

            sendJsonResponse([
                'success' => true,
                'items' => $uploadedMedia,
            ]);
        } else {
            sendJsonResponse([
                'success' => false,
                'message' => 'You do not have permission to upload files.',
            ], 401);
        }
    }, 'POST');

    Route::add('/api/media/richtext', function () {
        if (isset($_SESSION['loggedin'])) {
            global $mediaStore;

            if (requestExceededPostMaxSize()) {
                sendJsonResponse([
                    'success' => false,
                    'message' => getUploadTooLargeMessage(),
                ], 413);
                return;
            }

            if (!file_exists('./uploads')) {
                mkdir('./uploads');
            }

            if (!isset($_FILES['fileToUpload'])) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'No file was selected.',
                ], 400);
                return;
            }

            $uploadError = $_FILES['fileToUpload']['error'] ?? UPLOAD_ERR_NO_FILE;
            if ($uploadError !== UPLOAD_ERR_OK) {
                sendJsonResponse([
                    'success' => false,
                    'message' => getUploadErrorMessage($uploadError),
                ], in_array($uploadError, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) ? 413 : 400);
                return;
            }

            if (!move_uploaded_file($_FILES['fileToUpload']['tmp_name'], "./uploads/" . $_FILES['fileToUpload']['name'])) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'The server could not save the uploaded file. Please try again.',
                ], 500);
                return;
            } else {
                $page = [];
                $page['file'] = $_FILES['fileToUpload']['name'];
                $page['fileSmall'] = "min_" . $page['file'];
                $page["caption"] = "";
                $page['extension'] = pathinfo($page['file'], PATHINFO_EXTENSION);
                $page['type'] = "image";

                $page["editedUser"] = $_SESSION['id'];
                $page["edited"] = time();
                $page["createdUser"] = $page["editedUser"];
                $page["created"] = $page["edited"];

                $image = new ImageResize("./uploads/" . $page['file']);
                $image->resizeToWidth(500);
                $image->save("./uploads/min_" . $page['file']);

                $page = $mediaStore->insert($page);
                sendJsonResponse([
                    'success' => true,
                    'file' => BASEPATH . '/uploads/' . $page['file'],
                ]);
            }
        } else {
            sendJsonResponse([
                'success' => false,
                'message' => 'You do not have permission to upload files.',
            ], 401);
        }
    }, 'POST');

    Route::add('/api/media/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            global $mediaStore;
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $mediaItem = [
                'caption' => $data["caption"],
                'editedUser' => $_SESSION['id'],
                'edited' => time()
            ];

            $mediaItem = $mediaStore->updateById($who, $mediaItem);
        } else {
            getErrorPage(401);
        }
    }, 'PUT');

    Route::add('/api/media/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            global $mediaStore;
            $selectedMedia = $mediaStore->findById($who);
            $mediaStore->deleteById($who);
            if (!unlink("./uploads/" . $selectedMedia['file'])) {
                getErrorPage(500);
            }
            if ($selectedMedia['type'] == "image") {
                if (!unlink("./uploads/" . $selectedMedia['fileSmall'])) {
                    getErrorPage(500);
                }
            }
        }
    }, 'DELETE');

    /* Forms */

    Route::add('/api/form', function () {
        if (isset($_SESSION['loggedin'])) {
            global $formStore;
            $allSubmissions = $formStore->findAll($orderBy = ["created" => "desc"]);
            $myJSON = json_encode($allSubmissions);
            echo $myJSON;
        } else {
            getErrorPage(401);
        }
    });

    Route::add('/form/(.*)', function ($formID) {
        global $formStore, $userStore;
        $forms = json_decode(file_get_contents("./theme/config.json"), true)["forms"];
        foreach ($forms as $form) {
            if ($form["id"] == $formID) {
                if (isSpamSubmission($formID) || test_input($_POST["math"]) != "5") {
                    unset($_SESSION['formSpamProtection'][$formID]);
                    header('Location: ' . appendQueryParam(getFormReferer(), 'error', '1'));
                    return;
                } else {
                    $submission = [];
                    $submission["form"] = $formID;
                    $submission["formName"] = $form["name"];
                    $submission["fields"] = [];
                    $submission["created"] = time();
                    $submission["ipAddress"] = getClientIpAddress();
                    $submission["userAgent"] = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : '';
                    foreach ($form["fields"] as $field) {
                        $submission["fields"][] = [
                            "id" => $field["id"],
                            "name" => $field["name"],
                            "type" => $field["type"],
                            "value" => test_input($_POST[$field["id"]])
                        ];
                    }
                    $submission = $formStore->insert($submission);


                    $subject = $form["name"] . " Form Submission From Your Website";
                    $txt = "There is a new " . $form["name"] . " form submission on your website. <a href='" . $_SERVER['SERVER_NAME'] . '/admin' . "'>Log into to the dashboard to view it.</a>";
                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

                    $allUsers = $userStore->findAll();
                    foreach ($allUsers as $user) {
                        if ($user["notifySubmissions"] == 1) {
                            mail($user["email"], $subject, $txt, $headers);
                        }
                    };

                    unset($_SESSION['formSpamProtection'][$formID]);
                    header('Location: ' . appendQueryParam(getFormReferer(), 'success', '1'));
                    return;
                }
            }
        };

        getErrorPage(404);
    }, 'POST');

    Route::add('/api/form/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            global $formStore;
            $formStore->deleteById($who);
        } else {
            getErrorPage(401);
        }
    }, 'DELETE');

    /* Page Display */

    # Return pages under a collection subpath - currently only supports one collection subpath
    Route::add('/(.*)/(.*)', function ($who1, $who2) {
        global $pageStore;
        global $siteTitle;

        $page = $pageStore->findOneBy([["collectionSubpath", "=", $who1], "AND", ["path", "=", $who2]]);
        if ($page == null || $page["isPathless"] == true || ($page["isPublished"] == false && !isset($_SESSION['loggedin']))) {
            getErrorPage(404);
        } else {
            $filename = './theme/' . $page["templateName"] . ".php";
            if (file_exists($filename)) {
                includeThemeFile($filename, [
                    'page' => $page,
                    'siteTitle' => $siteTitle
                ]);
            } else {
                getErrorPage(404);
            }
        }
    });

    # Return pages not under a collection subpath
    Route::add('/(.*)', function ($who) {
        global $pageStore;
        global $siteTitle;

        $page = $pageStore->findOneBy([["collectionSubpath", "=", ""], "AND", ["path", "=", $who]]);
        if ($page == null || $page["isPathless"] == true || ($page["isPublished"] == false && !isset($_SESSION['loggedin']))) {
            getErrorPage(404);
        } else {
            $filename = './theme/' . $page["templateName"] . ".php";
            if (file_exists($filename)) {
                includeThemeFile($filename, [
                    'page' => $page,
                    'siteTitle' => $siteTitle
                ]);
            } else {
                getErrorPage(404);
            }
        }
    });
};


Route::run(BASEPATH);

?>
