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

define('MIRAGE_VERSION', "1.1.9");

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

function normalizeEmailAddress($email)
{
    return strtolower(trim((string) $email));
}

function isValidEmailAddress($email)
{
    return filter_var((string) $email, FILTER_VALIDATE_EMAIL) !== false;
}

function sendCommonSecurityHeaders()
{
    if (!headers_sent()) {
        header_remove('X-Powered-By');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: same-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    }
}

sendCommonSecurityHeaders();

function getNormalizedRequestHost()
{
    $candidates = [
        $_SERVER['HTTP_HOST'] ?? '',
        $_SERVER['SERVER_NAME'] ?? ''
    ];

    foreach ($candidates as $candidate) {
        $candidate = trim((string) $candidate);
        if ($candidate === '') {
            continue;
        }

        if (preg_match('/\A(?:[a-z0-9-]+\.)*[a-z0-9-]+(?::\d{1,5})?\z/i', $candidate) === 1) {
            return $candidate;
        }
    }

    return 'localhost';
}

function isHttpsRequest()
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }

    if (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) {
        return true;
    }

    return false;
}

function getCsrfToken()
{
    if (empty($_SESSION['csrfToken']) || !is_string($_SESSION['csrfToken'])) {
        $_SESSION['csrfToken'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrfToken'];
}

function getCsrfTokenFieldHtml()
{
    return '<input type="hidden" name="_mirage_csrf" value="' . htmlspecialchars(getCsrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

function getRequestCsrfToken()
{
    if (isset($_SERVER['HTTP_X_MIRAGE_CSRF'])) {
        return trim((string) $_SERVER['HTTP_X_MIRAGE_CSRF']);
    }

    if (isset($_POST['_mirage_csrf'])) {
        return trim((string) $_POST['_mirage_csrf']);
    }

    return '';
}

function isValidCsrfToken($token)
{
    return is_string($token) && $token !== '' && hash_equals(getCsrfToken(), $token);
}

function requireCsrfToken($expectsJson = false)
{
    if (isValidCsrfToken(getRequestCsrfToken())) {
        return true;
    }

    if ($expectsJson) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Security token mismatch. Refresh the page and try again.'
        ], 403);
    } else {
        getErrorPage(403);
    }

    return false;
}

function isLoggedIn()
{
    return !empty($_SESSION['loggedin']) && isset($_SESSION['id']);
}

function getCurrentUserId()
{
    return isset($_SESSION['id']) ? (string) $_SESSION['id'] : null;
}

function getCurrentAccountType()
{
    return isset($_SESSION['accountType']) ? (int) $_SESSION['accountType'] : null;
}

function isAdministrator()
{
    return getCurrentAccountType() === 0;
}

function isEditor()
{
    return getCurrentAccountType() === 1;
}

function isAuthor()
{
    return getCurrentAccountType() === 2;
}

function canManageMenus()
{
    return isLoggedIn() && !isAuthor();
}

function canManageForms()
{
    return isLoggedIn() && !isAuthor();
}

function canEditPage($page)
{
    if (!isLoggedIn() || !is_array($page)) {
        return false;
    }

    if (!isAuthor()) {
        return true;
    }

    $currentUserId = getCurrentUserId();
    return $currentUserId !== null
        && (
            (string) ($page['createdUser'] ?? '') === $currentUserId
            || (string) ($page['editedUser'] ?? '') === $currentUserId
        );
}

function canEditMediaItem($mediaItem)
{
    if (!isLoggedIn() || !is_array($mediaItem)) {
        return false;
    }

    if (!isAuthor()) {
        return true;
    }

    $currentUserId = getCurrentUserId();
    return $currentUserId !== null
        && (
            (string) ($mediaItem['createdUser'] ?? '') === $currentUserId
            || (string) ($mediaItem['editedUser'] ?? '') === $currentUserId
        );
}

function canManageUserRecord($targetUser)
{
    if (!isLoggedIn() || !is_array($targetUser)) {
        return false;
    }

    $targetAccountType = isset($targetUser['accountType']) ? (int) $targetUser['accountType'] : 2;
    $targetUserId = isset($targetUser['_id']) ? (string) $targetUser['_id'] : '';
    $currentUserId = getCurrentUserId();

    if (isAdministrator()) {
        return true;
    }

    if (!isEditor() && $currentUserId !== $targetUserId) {
        return false;
    }

    if (isEditor()) {
        return $targetAccountType !== 0 || $currentUserId === $targetUserId;
    }

    return $currentUserId === $targetUserId;
}

function clampEditableAccountType($requestedAccountType, $existingUser = null)
{
    $requestedAccountType = (int) $requestedAccountType;
    if (!in_array($requestedAccountType, [0, 1, 2], true)) {
        $requestedAccountType = 2;
    }

    if (isAdministrator()) {
        return $requestedAccountType;
    }

    if ($existingUser !== null && isset($existingUser['accountType']) && (string) ($existingUser['_id'] ?? '') === getCurrentUserId()) {
        return (int) $existingUser['accountType'];
    }

    if (isEditor()) {
        return max($requestedAccountType, 1);
    }

    if ($existingUser !== null && isset($existingUser['accountType'])) {
        return (int) $existingUser['accountType'];
    }

    return 2;
}

function isSafeRelativePathValue($value)
{
    return is_string($value)
        && strpos($value, "\0") === false
        && strpos($value, '..') === false
        && strpos($value, '\\') === false;
}

function getThemeConfiguration()
{
    static $themeConfiguration = null;

    if ($themeConfiguration === null) {
        $themeConfiguration = [];
        if (file_exists('./theme/config.json')) {
            $decoded = json_decode(file_get_contents('./theme/config.json'), true);
            if (is_array($decoded)) {
                $themeConfiguration = $decoded;
            }
        }
    }

    return $themeConfiguration;
}

function getTemplateConfigById($templateID)
{
    if (!is_string($templateID) || preg_match('/\A[a-zA-Z0-9_-]+\z/', $templateID) !== 1) {
        return null;
    }

    $templates = getThemeConfiguration()['templates'] ?? [];
    foreach ($templates as $template) {
        if (($template['id'] ?? null) === $templateID) {
            return $template;
        }
    }

    return null;
}

function getCollectionConfigById($collectionID)
{
    $collections = getThemeConfiguration()['collections'] ?? [];
    foreach ($collections as $collection) {
        if (($collection['id'] ?? null) === $collectionID) {
            return $collection;
        }
    }

    return null;
}

function normalizeCollectionSortMode($sortMode)
{
    $sortMode = strtolower(trim((string) $sortMode));
    if (!in_array($sortMode, ['newest', 'oldest', 'custom'], true)) {
        return 'newest';
    }

    return $sortMode;
}

function getCollectionSortMode($collectionID, $sortMode = null)
{
    if (is_array($sortMode)) {
        return $sortMode;
    }

    if ($sortMode !== null) {
        return normalizeCollectionSortMode($sortMode);
    }

    $collection = getCollectionConfigById($collectionID);
    return normalizeCollectionSortMode($collection['sort'] ?? 'newest');
}

function isValidTemplateForCollection($templateID, $collectionID)
{
    $template = getTemplateConfigById($templateID);
    $collection = getCollectionConfigById($collectionID);

    if ($template === null || $collection === null) {
        return false;
    }

    $allowedTemplates = $collection['allowed_templates'] ?? [];
    return in_array($templateID, $allowedTemplates, true);
}

function getTemplateDefinitionPath($templateID)
{
    $template = getTemplateConfigById($templateID);
    if ($template === null) {
        return null;
    }

    $templateFile = trim((string) ($template['file'] ?? ''));
    if (preg_match('/\A[a-zA-Z0-9_-]+\.json\z/', $templateFile) !== 1) {
        return null;
    }

    $path = './theme/template_defs/' . $templateFile;
    if (!file_exists($path)) {
        return null;
    }

    return $path;
}

function getThemeTemplatePhpPath($templateID)
{
    if (getTemplateConfigById($templateID) === null) {
        return null;
    }

    $path = './theme/' . $templateID . '.php';
    if (!file_exists($path)) {
        return null;
    }

    return $path;
}

function getUploadedFileMimeType($temporaryFile)
{
    if (!is_string($temporaryFile) || $temporaryFile === '' || !file_exists($temporaryFile)) {
        return '';
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $mimeType = finfo_file($finfo, $temporaryFile);
            finfo_close($finfo);
            if (is_string($mimeType)) {
                return strtolower($mimeType);
            }
        }
    }

    if (function_exists('mime_content_type')) {
        $mimeType = mime_content_type($temporaryFile);
        if (is_string($mimeType)) {
            return strtolower($mimeType);
        }
    }

    return '';
}

function getAllowedUploadTypes()
{
    return [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'webp' => ['image/webp'],
        'pdf' => ['application/pdf'],
        'txt' => ['text/plain'],
        'csv' => ['text/csv', 'text/plain', 'application/vnd.ms-excel'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'xls' => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
        'ppt' => ['application/vnd.ms-powerpoint'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip'],
        'zip' => ['application/zip', 'application/x-zip-compressed']
    ];
}

function isAllowedUploadedFile($temporaryFile, $originalFilename, $imageOnly = false)
{
    $extension = strtolower((string) pathinfo((string) $originalFilename, PATHINFO_EXTENSION));
    $allowedTypes = getAllowedUploadTypes();

    if ($imageOnly) {
        $allowedTypes = array_intersect_key($allowedTypes, array_flip(['jpg', 'jpeg', 'png', 'gif', 'webp']));
    }

    if (!isset($allowedTypes[$extension])) {
        return false;
    }

    $mimeType = getUploadedFileMimeType($temporaryFile);
    if ($mimeType === '') {
        return false;
    }

    return in_array($mimeType, $allowedTypes[$extension], true);
}

function getStoredUploadFilename($originalFilename)
{
    $extension = strtolower((string) pathinfo((string) $originalFilename, PATHINFO_EXTENSION));
    $baseName = (string) pathinfo((string) $originalFilename, PATHINFO_FILENAME);
    $baseName = preg_replace('/[^A-Za-z0-9_-]+/', '-', $baseName);
    $baseName = trim((string) $baseName, '-_');

    if ($baseName === '') {
        $baseName = 'file';
    }

    return $baseName . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
}

function ensureUploadsDirectoryExists()
{
    $uploadsDirectory = __DIR__ . '/uploads';
    if (!is_dir($uploadsDirectory)) {
        mkdir($uploadsDirectory, 0755, true);
    }

    return $uploadsDirectory;
}

function getUploadStoragePath($storedFilename)
{
    $storedFilename = trim((string) $storedFilename);
    if ($storedFilename === '' || $storedFilename !== basename($storedFilename)) {
        return null;
    }

    return ensureUploadsDirectoryExists() . '/' . $storedFilename;
}

function moveUploadedFileToStorage($temporaryFile, $originalFilename)
{
    $storedFilename = getStoredUploadFilename($originalFilename);
    $storagePath = getUploadStoragePath($storedFilename);
    if ($storagePath === null) {
        return null;
    }

    if (!move_uploaded_file($temporaryFile, $storagePath)) {
        return null;
    }

    return $storedFilename;
}

function deleteStoredUploadFile($storedFilename)
{
    $storagePath = getUploadStoragePath($storedFilename);
    if ($storagePath === null || !file_exists($storagePath)) {
        return true;
    }

    return unlink($storagePath);
}

function getFullBasepathRaw()
{
    $host = getNormalizedRequestHost();
    $scheme = isHttpsRequest() ? "https://" : "http://";

    return $scheme . $host . BASEPATH;
}

function getFullBasepath() {
    return htmlspecialchars(getFullBasepathRaw(), ENT_QUOTES, 'UTF-8');
}

function getPublicPagePath($page)
{
    if (!is_array($page) || !empty($page['isPathless']) || empty($page['isPublished'])) {
        return null;
    }

    $templateName = trim((string) ($page['templateName'] ?? ''));
    if ($templateName === '' || getThemeTemplatePhpPath($templateName) === null) {
        return null;
    }

    $pathParts = [];
    $collectionSubpath = trim((string) ($page['collectionSubpath'] ?? ''), '/');
    $path = trim((string) ($page['path'] ?? ''), '/');

    if ($collectionSubpath !== '') {
        $pathParts[] = $collectionSubpath;
    }

    if ($path !== '') {
        $pathParts[] = $path;
    }

    if (count($pathParts) === 0) {
        return '/';
    }

    $encodedSegments = array_map('rawurlencode', explode('/', implode('/', $pathParts)));

    return '/' . implode('/', $encodedSegments);
}

function getPublicPageUrl($page)
{
    $path = getPublicPagePath($page);
    if ($path === null) {
        return null;
    }

    return rtrim(getFullBasepathRaw(), '/') . $path;
}

function getPageLastModifiedW3C($page)
{
    if (!is_array($page)) {
        return null;
    }

    $edited = isset($page['edited']) ? (int) $page['edited'] : 0;
    $created = isset($page['created']) ? (int) $page['created'] : 0;
    $timestamp = max($edited, $created);

    if ($timestamp <= 0) {
        return null;
    }

    return gmdate('Y-m-d\TH:i:s\Z', $timestamp);
}

function getSitemapEntries()
{
    global $pageStore;

    $pages = $pageStore->findAll(["edited" => "desc"]);
    $entries = [];
    $seenUrls = [];

    foreach ($pages as $page) {
        $url = getPublicPageUrl($page);
        if ($url === null || isset($seenUrls[$url])) {
            continue;
        }

        $entries[] = [
            'loc' => $url,
            'lastmod' => getPageLastModifiedW3C($page)
        ];
        $seenUrls[$url] = true;
    }

    return $entries;
}

function escapeXml($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

function outputSitemapXml()
{
    header('Content-Type: application/xml; charset=UTF-8');

    $entries = getSitemapEntries();
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

    foreach ($entries as $entry) {
        echo "  <url>\n";
        echo "    <loc>" . escapeXml($entry['loc']) . "</loc>\n";
        if (!empty($entry['lastmod'])) {
            echo "    <lastmod>" . escapeXml($entry['lastmod']) . "</lastmod>\n";
        }
        echo "  </url>\n";
    }

    echo "</urlset>";
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
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($response);
}

function getDefaultSiteSettings()
{
    return [
        'siteTitle' => '',
        'footerText' => '',
        'copyrightText' => '{{year}} {{siteTitle}} - All Rights Reserved.'
    ];
}

function normalizeSiteSettings($settings)
{
    $defaults = getDefaultSiteSettings();
    $settings = is_array($settings) ? $settings : [];

    return [
        'siteTitle' => array_key_exists('siteTitle', $settings) ? trim((string) $settings['siteTitle']) : $defaults['siteTitle'],
        'footerText' => array_key_exists('footerText', $settings) ? trim((string) $settings['footerText']) : $defaults['footerText'],
        'copyrightText' => array_key_exists('copyrightText', $settings) ? trim((string) $settings['copyrightText']) : $defaults['copyrightText']
    ];
}

function getCurrentSiteSettings()
{
    global $siteTitle;
    global $footerText;
    global $copyrightText;

    $settings = [];
    if (isset($siteTitle)) {
        $settings['siteTitle'] = $siteTitle;
    }

    if (isset($footerText)) {
        $settings['footerText'] = $footerText;
    }

    if (isset($copyrightText)) {
        $settings['copyrightText'] = $copyrightText;
    }

    return normalizeSiteSettings($settings);
}

function writeSiteConfigFile($settings)
{
    $settings = normalizeSiteSettings($settings);

    $configContents = "<?php\n\n";
    $configContents .= '$siteTitle = ' . var_export($settings['siteTitle'], true) . ";\n";
    $configContents .= '$footerText = ' . var_export($settings['footerText'], true) . ";\n";
    $configContents .= '$copyrightText = ' . var_export($settings['copyrightText'], true) . ";\n";

    return file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'config.php', $configContents, LOCK_EX) !== false;
}

function renderSiteTextTemplate($text, $context = [])
{
    $context = is_array($context) ? $context : [];
    $siteTitleValue = array_key_exists('siteTitle', $context) ? (string) $context['siteTitle'] : '';

    $renderedText = preg_replace_callback('/\{\{\s*(year|siteTitle|site_title)\s*\}\}/i', function ($matches) use ($siteTitleValue) {
        $token = strtolower(str_replace('_', '', (string) ($matches[1] ?? '')));

        if ($token === 'year') {
            return date('Y');
        }

        if ($token === 'sitetitle') {
            return $siteTitleValue;
        }

        return $matches[0];
    }, (string) $text);

    return $renderedText !== null ? $renderedText : (string) $text;
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

function getInvalidUploadTypeMessage($imageOnly = false)
{
    if ($imageOnly) {
        return 'Only JPG, PNG, GIF, and WebP images can be uploaded here.';
    }

    return 'That file type is not allowed. Use a common image or document format.';
}

function normalizeOptionalMediaReference($value)
{
    if ($value === null) {
        return null;
    }

    if (is_string($value)) {
        $value = trim($value);
        if ($value === '' || strtolower($value) === 'null' || strtolower($value) === 'undefined') {
            return null;
        }

        return ctype_digit($value) ? (int) $value : $value;
    }

    if (is_int($value)) {
        return $value;
    }

    if (is_float($value) && floor($value) === $value) {
        return (int) $value;
    }

    return is_scalar($value) ? $value : null;
}

# Generate page field
function generateField($field)
{
    if ($field['type'] != 'list') {
        if (($field['type'] ?? '') === 'richtext') {
            return (string) ($field['value'] ?? '');
        }

        if (($field['type'] ?? '') === 'media') {
            return normalizeOptionalMediaReference($field['value'] ?? null);
        }

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

function sanitizeStoredPathSegment($value)
{
    $value = trim((string) $value, '/');
    if (!isSafeRelativePathValue($value)) {
        return null;
    }

    return $value;
}

# Function used for generating a page document from input information and page config
function generatePage($json, $isNewPage = false, $existingPage = null)
{
    $data = json_decode($json, true);
    if (!is_array($data) || !isset($data['template']) || !is_array($data['template'])) {
        return null;
    }

    $templateName = trim((string) ($data["templateName"] ?? ''));
    $collectionID = trim((string) ($data["collection"] ?? ''));
    if (!isValidTemplateForCollection($templateName, $collectionID) || getThemeTemplatePhpPath($templateName) === null) {
        return null;
    }

    $path = sanitizeStoredPathSegment($data["path"] ?? '');
    $collectionSubpath = sanitizeStoredPathSegment($data["collectionSubpath"] ?? '');
    if ($path === null || $collectionSubpath === null) {
        return null;
    }

    $page = [];
    $page["content"] = [];
    $page["editedUser"] = getCurrentUserId(); // could be null if user has been deleted
    $page["edited"] = time();
    if ($isNewPage) {
        $page["createdUser"] = $page["editedUser"]; // could be null if user has been deleted
        $page["created"] = $page["edited"];
    }

    $sections = $data["template"]["sections"] ?? [];
    foreach ($sections as $section) {
        if (!isset($section["fields"]) || !is_array($section["fields"])) {
            continue;
        }

        foreach ($section["fields"] as $field) {
            if (isset($field['value'])) {
                $page["content"][$field['id']] = generateField($field);
            }
        }
    }

    $page["templateName"] = $templateName;
    $page["title"] = (string) ($data["title"] ?? '');
    $page["featuredImage"] = normalizeOptionalMediaReference($data["featuredImage"] ?? null);
    $page["description"] = (string) ($data["description"] ?? '');
    $page["path"] = $path;
    $page["isPathless"] = !empty($data["isPathless"]);
    $page["collection"] = $collectionID;
    $page["collectionSubpath"] = $collectionSubpath ?? "";
    $page["isPublished"] = isAuthor() ? (bool) ($existingPage["isPublished"] ?? false) : !empty($data["isPublished"]);
    if ($isNewPage) {
        $page["order"] = getNextCollectionPageOrder($collectionID);
    } else if (is_array($existingPage) && isset($existingPage["order"]) && is_numeric($existingPage["order"])) {
        $page["order"] = (int) $existingPage["order"];
    }

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
    } else if ($errorCode == 403) {
        $errorMessage = "the request could not be verified, refresh the page and try again";
    }
    if (file_exists("./theme/error.php")) {
        include "./theme/error.php";
    } else {
        include "./dashboard/error.php";
    }
};

function getPageCreatedTimestamp($page)
{
    return isset($page['created']) ? (int) $page['created'] : 0;
}

function getPageOrderValue($page)
{
    return isset($page['order']) && is_numeric($page['order']) ? (int) $page['order'] : null;
}

function comparePagesByCollectionSort($leftPage, $rightPage, $sortMode)
{
    $leftId = isset($leftPage['_id']) ? (int) $leftPage['_id'] : 0;
    $rightId = isset($rightPage['_id']) ? (int) $rightPage['_id'] : 0;

    if ($sortMode === 'oldest') {
        $leftCreated = getPageCreatedTimestamp($leftPage);
        $rightCreated = getPageCreatedTimestamp($rightPage);
        if ($leftCreated === $rightCreated) {
            return $leftId <=> $rightId;
        }

        return $leftCreated <=> $rightCreated;
    }

    if ($sortMode === 'custom') {
        $leftOrder = getPageOrderValue($leftPage);
        $rightOrder = getPageOrderValue($rightPage);
        $leftHasOrder = $leftOrder !== null;
        $rightHasOrder = $rightOrder !== null;

        if ($leftHasOrder && $rightHasOrder && $leftOrder !== $rightOrder) {
            return $leftOrder <=> $rightOrder;
        }

        if ($leftHasOrder !== $rightHasOrder) {
            return $leftHasOrder ? -1 : 1;
        }
    }

    $leftCreated = getPageCreatedTimestamp($leftPage);
    $rightCreated = getPageCreatedTimestamp($rightPage);
    if ($leftCreated === $rightCreated) {
        return $rightId <=> $leftId;
    }

    return $rightCreated <=> $leftCreated;
}

function sortPagesByCollectionSort($pages, $sortMode)
{
    $sortMode = normalizeCollectionSortMode($sortMode);
    if ($sortMode === 'custom') {
        $explicitPages = [];
        $missingOrderPages = [];

        foreach ($pages as $page) {
            $pageOrder = getPageOrderValue($page);
            if ($pageOrder === null || $pageOrder < 0) {
                $missingOrderPages[] = $page;
                continue;
            }

            $page['_mirageEffectiveOrder'] = $pageOrder;
            $explicitPages[] = $page;
        }

        usort($explicitPages, function ($leftPage, $rightPage) {
            $leftOrder = (int) ($leftPage['_mirageEffectiveOrder'] ?? 0);
            $rightOrder = (int) ($rightPage['_mirageEffectiveOrder'] ?? 0);
            if ($leftOrder === $rightOrder) {
                return comparePagesByCollectionSort($leftPage, $rightPage, 'newest');
            }

            return $leftOrder <=> $rightOrder;
        });

        usort($missingOrderPages, function ($leftPage, $rightPage) {
            return comparePagesByCollectionSort($leftPage, $rightPage, 'newest');
        });

        $occupiedOrders = [];
        foreach ($explicitPages as $page) {
            $occupiedOrders[(int) $page['_mirageEffectiveOrder']] = true;
        }

        $nextFallbackOrder = 0;
        foreach ($missingOrderPages as $index => $page) {
            while (isset($occupiedOrders[$nextFallbackOrder])) {
                $nextFallbackOrder++;
            }

            $missingOrderPages[$index]['_mirageEffectiveOrder'] = $nextFallbackOrder;
            $occupiedOrders[$nextFallbackOrder] = true;
            $nextFallbackOrder++;
        }

        $pages = array_merge($explicitPages, $missingOrderPages);
        usort($pages, function ($leftPage, $rightPage) {
            $leftOrder = (int) ($leftPage['_mirageEffectiveOrder'] ?? 0);
            $rightOrder = (int) ($rightPage['_mirageEffectiveOrder'] ?? 0);
            if ($leftOrder === $rightOrder) {
                return comparePagesByCollectionSort($leftPage, $rightPage, 'newest');
            }

            return $leftOrder <=> $rightOrder;
        });

        foreach ($pages as $index => $page) {
            unset($pages[$index]['_mirageEffectiveOrder']);
        }

        return $pages;
    }

    usort($pages, function ($leftPage, $rightPage) use ($sortMode) {
        return comparePagesByCollectionSort($leftPage, $rightPage, $sortMode);
    });

    return $pages;
}

function getNextCollectionPageOrder($collectionID)
{
    global $pageStore;

    $pages = $pageStore->findBy(["collection", "=", $collectionID]);
    $highestOrder = -1;
    foreach ($pages as $page) {
        $pageOrder = getPageOrderValue($page);
        if ($pageOrder !== null && $pageOrder > $highestOrder) {
            $highestOrder = $pageOrder;
        }
    }

    return max(count($pages), $highestOrder + 1);
}

function syncCollectionPageOrders($collectionID, $orderedPageIDs = null)
{
    global $pageStore;

    $pages = $pageStore->findBy(["collection", "=", $collectionID]);
    if (count($pages) === 0) {
        return [];
    }

    $pagesById = [];
    foreach ($pages as $page) {
        $pagesById[(string) ($page['_id'] ?? '')] = $page;
    }

    $orderedPages = [];
    if (is_array($orderedPageIDs)) {
        foreach ($orderedPageIDs as $pageID) {
            $pageKey = (string) $pageID;
            if (!isset($pagesById[$pageKey])) {
                continue;
            }

            $orderedPages[] = $pagesById[$pageKey];
            unset($pagesById[$pageKey]);
        }
    }

    if (count($pagesById) > 0) {
        $remainingPages = sortPagesByCollectionSort(array_values($pagesById), 'custom');
        $orderedPages = array_merge($orderedPages, $remainingPages);
    }

    foreach ($orderedPages as $index => $page) {
        if (getPageOrderValue($page) !== $index) {
            $pageStore->updateById($page['_id'], [
                'order' => $index
            ]);
        }
        $orderedPages[$index]['order'] = $index;
    }

    return $orderedPages;
}

function getPages($collection, $numEntries, $sort = null)
{
    global $pageStore;

    $conditions = isset($_SESSION['loggedin'])
        ? ["collection", "=", $collection]
        : [["collection", "=", $collection], ["isPublished", "=", true]];

    if (is_array($sort) && count($sort) > 0) {
        if ($numEntries > 0) {
            return $pageStore->findBy($conditions, $sort, $numEntries);
        }

        return $pageStore->findBy($conditions, $sort);
    }

    $pages = $pageStore->findBy($conditions);
    $pages = sortPagesByCollectionSort($pages, getCollectionSortMode($collection, $sort));

    if ($numEntries > 0) {
        return array_slice($pages, 0, $numEntries);
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

function getSafeInternalRedirectUrl($url)
{
    $url = trim((string) $url);
    if ($url === '') {
        return BASEPATH . '/';
    }

    $parsedUrl = parse_url($url);
    if ($parsedUrl === false) {
        return BASEPATH . '/';
    }

    if (!isset($parsedUrl['host'])) {
        $relativePath = '/' . ltrim($url, '/');
        if (BASEPATH !== '' && strpos($relativePath, BASEPATH . '/') !== 0 && $relativePath !== BASEPATH) {
            return BASEPATH . '/';
        }

        return $relativePath;
    }

    $requestHost = preg_replace('/:\d{1,5}\z/', '', getNormalizedRequestHost());
    $redirectHost = preg_replace('/:\d{1,5}\z/', '', (string) $parsedUrl['host']);
    if (strcasecmp($requestHost, $redirectHost) !== 0) {
        return BASEPATH . '/';
    }

    $path = $parsedUrl['path'] ?? '/';
    if (!isSafeRelativePathValue($path)) {
        return BASEPATH . '/';
    }

    if (BASEPATH !== '' && strpos($path, BASEPATH . '/') !== 0 && $path !== BASEPATH) {
        return BASEPATH . '/';
    }

    $safeUrl = $path;
    if (!empty($parsedUrl['query'])) {
        $safeUrl .= '?' . $parsedUrl['query'];
    }
    if (!empty($parsedUrl['fragment'])) {
        $safeUrl .= '#' . $parsedUrl['fragment'];
    }

    return $safeUrl;
}

function getFormReferer()
{
    if (!isset($_SERVER["HTTP_REFERER"]) || $_SERVER["HTTP_REFERER"] == "") {
        return BASEPATH . '/';
    }

    return getSafeInternalRedirectUrl($_SERVER["HTTP_REFERER"]);
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
    if (!isset($data['page']) || !is_array($data['page'])) {
        $data['page'] = [];
    }

    $siteSettings = getCurrentSiteSettings();
    if (!array_key_exists('siteTitle', $data)) {
        $data['siteTitle'] = $siteSettings['siteTitle'];
    }

    if (!array_key_exists('footerText', $data)) {
        $data['footerText'] = $siteSettings['footerText'];
    }

    if (!array_key_exists('copyrightText', $data)) {
        $data['copyrightText'] = $siteSettings['copyrightText'];
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

        if (!requireCsrfToken()) {
            return;
        }

        $emailAddress = normalizeEmailAddress($_POST["email"] ?? '');
        if (!isValidEmailAddress($emailAddress)) {
            getErrorPage(400);
            return;
        }

        $user = [
            'name' => test_input($_POST["name"]),
            'email' => $emailAddress,
            'bio' => "",
            'notifySubmissions' => 1,
            'password' => password_hash($_POST["password"], PASSWORD_DEFAULT),
            'accountType' => 0
        ];

        $user = $userStore->insert($user);

        $siteSettings = normalizeSiteSettings([
            'siteTitle' => $_POST["siteTitle"] ?? ''
        ]);

        if ($siteSettings['siteTitle'] === '') {
            getErrorPage(400);
            return;
        }

        if (!writeSiteConfigFile($siteSettings)) {
            getErrorPage(500);
            return;
        }

        header('Location: ' . BASEPATH . '/login');
    }, 'POST');

    Route::add('(.*)', function ($who) {
        include "./dashboard/setup.php";
    });
} else {
# if config.php exists, run the rest of the application

    require_once 'config.php';

    $siteSettings = getCurrentSiteSettings();
    $siteTitle = $siteSettings['siteTitle'];
    $footerText = $siteSettings['footerText'];
    $copyrightText = $siteSettings['copyrightText'];

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

        if (!requireCsrfToken()) {
            return;
        }

        $user = $userStore->findOneBy(["email", "=", normalizeEmailAddress($_POST["email"] ?? '')]);

        if ($user != null && password_verify($_POST["password"], $user['password'])) {
            session_regenerate_id(true);
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
        if (!requireCsrfToken()) {
            return;
        }

        if (isset($_SESSION['loggedin'])) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                setcookie(session_name(), '', time() - 42000, '/');
            }
            session_destroy();
        }
        header('Location: ' . BASEPATH . '/login');
    }, 'POST');

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
            $templatePath = getTemplateDefinitionPath($who);
            if ($templatePath === null) {
                getErrorPage(404);
                return;
            }

            echo file_get_contents($templatePath);
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

    Route::add('/api/settings', function () {
        if (!isLoggedIn()) {
            sendJsonResponse([
                'success' => false,
                'message' => 'You must be logged in to view site settings.'
            ], 401);
            return;
        }

        if (!isAdministrator()) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Only administrators can view site settings.'
            ], 403);
            return;
        }

        sendJsonResponse(getCurrentSiteSettings());
    });

    Route::add('/api/settings', function () {
        if (!isLoggedIn()) {
            sendJsonResponse([
                'success' => false,
                'message' => 'You must be logged in to update site settings.'
            ], 401);
            return;
        }

        if (!isAdministrator()) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Only administrators can update site settings.'
            ], 403);
            return;
        }

        if (!requireCsrfToken(true)) {
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Invalid settings payload.'
            ], 400);
            return;
        }

        $siteSettings = normalizeSiteSettings($data);
        if ($siteSettings['siteTitle'] === '') {
            sendJsonResponse([
                'success' => false,
                'message' => 'Site title is required.'
            ], 400);
            return;
        }

        if (!writeSiteConfigFile($siteSettings)) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Site settings could not be saved.'
            ], 500);
            return;
        }

        global $siteTitle;
        global $footerText;
        global $copyrightText;

        $siteTitle = $siteSettings['siteTitle'];
        $footerText = $siteSettings['footerText'];
        $copyrightText = $siteSettings['copyrightText'];

        sendJsonResponse($siteSettings);
    }, 'PUT');

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

    Route::add('/api/users/([0-9]*)', function ($who) {
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

    Route::add('/api/users/([0-9]*)', function ($who) {
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

    /* Pages */

    Route::add('/api/collections/(.*)/pages', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            $allPages = getPages($who, 0);
            $myJSON = json_encode($allPages);
            echo $myJSON;
        } else {
            getErrorPage(401);
        }
    });

    Route::add('/api/collections/(.*)/order', function ($who) {
        if (!isset($_SESSION['loggedin'])) {
            getErrorPage(401);
            return;
        }

        global $pageStore;

        if (!requireCsrfToken(true)) {
            return;
        }

        if (getCollectionConfigById($who) === null) {
            sendJsonResponse([
                'success' => false,
                'message' => 'The requested collection does not exist.'
            ], 404);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        $orderedPageIDs = $payload['pageIDs'] ?? null;
        if (!is_array($orderedPageIDs)) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Invalid page order payload.'
            ], 400);
            return;
        }

        $orderedPageIDs = array_values(array_map('strval', $orderedPageIDs));
        if (count($orderedPageIDs) !== count(array_unique($orderedPageIDs))) {
            sendJsonResponse([
                'success' => false,
                'message' => 'The page order payload contains duplicate pages.'
            ], 400);
            return;
        }

        $collectionPages = $pageStore->findBy(["collection", "=", $who]);
        if (count($orderedPageIDs) !== count($collectionPages)) {
            sendJsonResponse([
                'success' => false,
                'message' => 'The page order payload does not match the collection.'
            ], 400);
            return;
        }

        $pagesById = [];
        foreach ($collectionPages as $page) {
            $pageID = (string) ($page['_id'] ?? '');
            $pagesById[$pageID] = $page;

            if (!canEditPage($page)) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'You do not have permission to reorder this collection.'
                ], 403);
                return;
            }
        }

        foreach ($orderedPageIDs as $pageID) {
            if (!isset($pagesById[$pageID])) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'The page order payload does not match the collection.'
                ], 400);
                return;
            }
        }

        sendJsonResponse(syncCollectionPageOrders($who, $orderedPageIDs));
    }, 'PUT');

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

            if (!requireCsrfToken(true)) {
                return;
            }

            $json = file_get_contents('php://input');
            $page = generatePage($json, true);
            if ($page === null) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'The page request is invalid.'
                ], 400);
                return;
            }

            $savedPage = $pageStore->insert($page);
            sendJsonResponse($savedPage);
        } else {
            getErrorPage(401);
        }
    }, 'POST');

    Route::add('/api/pages/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            global $pageStore;
            global $menuStore;

            if (!requireCsrfToken(true)) {
                return;
            }

            $existingPage = $pageStore->findById($who);
            if ($existingPage == null || !canEditPage($existingPage)) {
                getErrorPage(401);
                return;
            }

            $json = file_get_contents('php://input');
            $updatedPageData = generatePage($json, false, $existingPage);
            if ($updatedPageData === null) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'The page request is invalid.'
                ], 400);
                return;
            }

            $page = $pageStore->updateById($who, $updatedPageData);
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

            if (!requireCsrfToken(true)) {
                return;
            }

            $existingPage = $pageStore->findById($who);
            if ($existingPage == null || !canEditPage($existingPage)) {
                getErrorPage(401);
                return;
            }

            $allMenuItems = $menuStore->findAll();
            foreach ($allMenuItems as &$menuItem) {
                if ($menuItem["type"] == 0 && $menuItem["page"] == $who) {
                    $normalizedMenuItem = normalizeMenuItem($menuItem);
                    reparentChildMenuItems($normalizedMenuItem["itemID"], $normalizedMenuItem["parentItemID"]);
                    $menuStore->deleteById($menuItem["_id"]);
                }
            }
            $deletedPageCollection = $existingPage['collection'] ?? null;
            $pageStore->deleteById($who);
            if (is_string($deletedPageCollection) && $deletedPageCollection !== '') {
                syncCollectionPageOrders($deletedPageCollection);
            }
            sendJsonResponse([
                'success' => true
            ]);
        } else {
            getErrorPage(401);
        }
    }, 'DELETE');

    /* Menus */

    Route::add('/api/menus', function () {
        if (canManageMenus()) {
            $allMenuItems = getAllMenuItems();
            $myJSON = json_encode($allMenuItems);
            echo $myJSON;
        } else {
            getErrorPage(401);
        }
    });

    Route::add('/api/menus', function () {
        if (canManageMenus()) {
            global $menuStore;

            if (!requireCsrfToken(true)) {
                return;
            }

            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            if (!is_array($data)) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Invalid menu payload.'
                ], 400);
                return;
            }

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

            if (!requireCsrfToken(true)) {
                return;
            }

            if (requestExceededPostMaxSize()) {
                sendJsonResponse([
                    'success' => false,
                    'message' => getUploadTooLargeMessage(),
                ], 413);
                return;
            }

            ensureUploadsDirectoryExists();

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

                $temporaryFile = $_FILES['uploadMediaFiles']['tmp_name'][$i] ?? '';
                $originalFilename = $_FILES['uploadMediaFiles']['name'][$i] ?? '';
                if (!isAllowedUploadedFile($temporaryFile, $originalFilename)) {
                    sendJsonResponse([
                        'success' => false,
                        'message' => getInvalidUploadTypeMessage(),
                    ], 400);
                    return;
                }

                $storedFilename = moveUploadedFileToStorage($temporaryFile, $originalFilename);
                if ($storedFilename === null) {
                    sendJsonResponse([
                        'success' => false,
                        'message' => 'The server could not save the uploaded file. Please try again.',
                    ], 500);
                    return;
                } else {
                    $page = [];
                    $page['file'] = $storedFilename;
                    $page['fileSmall'] = $page['file'];
                    $page["caption"] = "";
                    $page["altText"] = "";
                    $page['extension'] = pathinfo($page['file'], PATHINFO_EXTENSION);

                    $page["editedUser"] = getCurrentUserId();
                    $page["edited"] = time();
                    $page["createdUser"] = $page["editedUser"];
                    $page["created"] = $page["edited"];

                    if (in_array(strtolower($page['extension']), ['png', 'jpg', 'gif', 'jpeg', 'webp'], true)) {
                        $page['type'] = "image";
                    } else {
                        $page['type'] = "file";
                    }

                    if ($page["type"] == "image") {
                        try {
                            $image = new ImageResize(getUploadStoragePath($page['file']));
                            $image->resizeToWidth(500);
                            $page['fileSmall'] = "min_" . $page['file'];
                            $image->save(getUploadStoragePath($page['fileSmall']));
                        } catch (\Throwable $exception) {
                            deleteStoredUploadFile($page['file']);
                            sendJsonResponse([
                                'success' => false,
                                'message' => getInvalidUploadTypeMessage(true),
                            ], 400);
                            return;
                        }
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

            if (!requireCsrfToken(true)) {
                return;
            }

            if (requestExceededPostMaxSize()) {
                sendJsonResponse([
                    'success' => false,
                    'message' => getUploadTooLargeMessage(),
                ], 413);
                return;
            }

            ensureUploadsDirectoryExists();

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

            $temporaryFile = $_FILES['fileToUpload']['tmp_name'] ?? '';
            $originalFilename = $_FILES['fileToUpload']['name'] ?? '';
            if (!isAllowedUploadedFile($temporaryFile, $originalFilename, true)) {
                sendJsonResponse([
                    'success' => false,
                    'message' => getInvalidUploadTypeMessage(true),
                ], 400);
                return;
            }

            $storedFilename = moveUploadedFileToStorage($temporaryFile, $originalFilename);
            if ($storedFilename === null) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'The server could not save the uploaded file. Please try again.',
                ], 500);
                return;
            } else {
                $page = [];
                $page['file'] = $storedFilename;
                $page['fileSmall'] = "min_" . $page['file'];
                $page["caption"] = "";
                $page["altText"] = "";
                $page['extension'] = pathinfo($page['file'], PATHINFO_EXTENSION);
                $page['type'] = "image";

                $page["editedUser"] = getCurrentUserId();
                $page["edited"] = time();
                $page["createdUser"] = $page["editedUser"];
                $page["created"] = $page["edited"];

                try {
                    $image = new ImageResize(getUploadStoragePath($page['file']));
                    $image->resizeToWidth(500);
                    $image->save(getUploadStoragePath($page['fileSmall']));
                } catch (\Throwable $exception) {
                    deleteStoredUploadFile($page['file']);
                    sendJsonResponse([
                        'success' => false,
                        'message' => getInvalidUploadTypeMessage(true),
                    ], 400);
                    return;
                }

                $page = $mediaStore->insert($page);
                sendJsonResponse([
                    'success' => true,
                    'file' => BASEPATH . '/uploads/' . rawurlencode($page['file']),
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

            if (!requireCsrfToken(true)) {
                return;
            }

            $existingMedia = $mediaStore->findById($who);
            if ($existingMedia == null || !canEditMediaItem($existingMedia)) {
                getErrorPage(401);
                return;
            }

            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $mediaItem = [
                'caption' => (string) ($data["caption"] ?? ''),
                'altText' => (string) ($data["altText"] ?? ''),
                'editedUser' => getCurrentUserId(),
                'edited' => time()
            ];

            $mediaStore->updateById($who, $mediaItem);
            sendJsonResponse([
                'success' => true
            ]);
        } else {
            getErrorPage(401);
        }
    }, 'PUT');

    Route::add('/api/media/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            global $mediaStore;

            if (!requireCsrfToken(true)) {
                return;
            }

            $selectedMedia = $mediaStore->findById($who);
            if ($selectedMedia == null || !canEditMediaItem($selectedMedia)) {
                getErrorPage(401);
                return;
            }

            $mediaStore->deleteById($who);
            if (!deleteStoredUploadFile($selectedMedia['file'])) {
                getErrorPage(500);
                return;
            }
            if ($selectedMedia['type'] == "image" && $selectedMedia['fileSmall'] !== $selectedMedia['file']) {
                if (!deleteStoredUploadFile($selectedMedia['fileSmall'])) {
                    getErrorPage(500);
                    return;
                }
            }

            sendJsonResponse([
                'success' => true
            ]);
        }
    }, 'DELETE');

    /* Forms */

    Route::add('/api/form', function () {
        if (canManageForms()) {
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
                            "value" => test_input($_POST[$field["id"]] ?? '')
                        ];
                    }
                    $submission = $formStore->insert($submission);


                    $subject = $form["name"] . " Form Submission From Your Website";
                    $txt = "There is a new " . $form["name"] . " form submission on your website. <a href='" . rtrim(getFullBasepathRaw(), '/') . "/admin'>Log into to the dashboard to view it.</a>";
                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

                    $allUsers = $userStore->findAll();
                    foreach ($allUsers as $user) {
                        if ($user["notifySubmissions"] == 1 && isValidEmailAddress($user["email"] ?? '')) {
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
        if (canManageForms()) {
            global $formStore;

            if (!requireCsrfToken(true)) {
                return;
            }

            $formStore->deleteById($who);
            sendJsonResponse([
                'success' => true
            ]);
        } else {
            getErrorPage(401);
        }
    }, 'DELETE');

    Route::add('/sitemap.xml', function () {
        outputSitemapXml();
    });

    /* Page Display */

    # Return pages under a collection subpath - currently only supports one collection subpath
    Route::add('/(.*)/(.*)', function ($who1, $who2) {
        global $pageStore;
        global $siteTitle;

        $page = $pageStore->findOneBy([["collectionSubpath", "=", $who1], "AND", ["path", "=", $who2]]);
        if ($page == null || $page["isPathless"] == true || ($page["isPublished"] == false && !isset($_SESSION['loggedin']))) {
            getErrorPage(404);
        } else {
            $filename = getThemeTemplatePhpPath((string) ($page["templateName"] ?? ''));
            if ($filename !== null) {
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
            $filename = getThemeTemplatePhpPath((string) ($page["templateName"] ?? ''));
            if ($filename !== null) {
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
