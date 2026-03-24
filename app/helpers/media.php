<?php

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

function getAcceptedUploadExtensions($imageOnly = false)
{
    $extensions = array_keys(getAllowedUploadTypes());

    if ($imageOnly) {
        $extensions = array_values(array_intersect($extensions, ['jpg', 'jpeg', 'png', 'gif', 'webp']));
    }

    return array_values($extensions);
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

function normalizeOriginalUploadFilename($originalFilename)
{
    $originalFilename = trim((string) $originalFilename);
    if ($originalFilename === '') {
        return '';
    }

    return basename(str_replace('\\', '/', $originalFilename));
}

function getUploadsDirectoryPath()
{
    return MIRAGE_ROOT . '/uploads';
}

function ensureUploadsDirectoryExists()
{
    $uploadsDirectory = getUploadsDirectoryPath();
    if (!is_dir($uploadsDirectory)) {
        mkdir($uploadsDirectory, 0755, true);
    }

    return $uploadsDirectory;
}

function normalizeStoredUploadFilename($storedFilename)
{
    $storedFilename = trim((string) $storedFilename);
    if ($storedFilename === '' || $storedFilename !== basename($storedFilename)) {
        return null;
    }

    return $storedFilename;
}

function getUploadStoragePath($storedFilename, $ensureExists = false)
{
    $storedFilename = normalizeStoredUploadFilename($storedFilename);
    if ($storedFilename === null) {
        return null;
    }

    $uploadsDirectory = $ensureExists ? ensureUploadsDirectoryExists() : getUploadsDirectoryPath();
    return $uploadsDirectory . '/' . $storedFilename;
}

function getStoredUploadPublicUrl($storedFilename)
{
    $storedFilename = normalizeStoredUploadFilename($storedFilename);
    if ($storedFilename === null) {
        return null;
    }

    return BASEPATH . '/uploads/' . rawurlencode($storedFilename);
}

function getStoredUploadMetadata($storedFilename)
{
    $storagePath = getUploadStoragePath($storedFilename);
    $exists = $storagePath !== null && is_file($storagePath);

    $metadata = [
        'exists' => $exists,
        'size' => 0,
        'mimeType' => '',
        'width' => null,
        'height' => null,
    ];

    if (!$exists) {
        return $metadata;
    }

    $fileSize = @filesize($storagePath);
    if ($fileSize !== false) {
        $metadata['size'] = (int) $fileSize;
    }

    $mimeType = getUploadedFileMimeType($storagePath);
    if ($mimeType !== '') {
        $metadata['mimeType'] = $mimeType;
    }

    $imageSize = @getimagesize($storagePath);
    if (is_array($imageSize)) {
        $metadata['width'] = isset($imageSize[0]) ? (int) $imageSize[0] : null;
        $metadata['height'] = isset($imageSize[1]) ? (int) $imageSize[1] : null;
    }

    return $metadata;
}

function moveUploadedFileToStorage($temporaryFile, $originalFilename)
{
    $storedFilename = getStoredUploadFilename($originalFilename);
    $storagePath = getUploadStoragePath($storedFilename, true);
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

function createImagePreviewForStoredUpload($storedFilename)
{
    $storedFilename = normalizeStoredUploadFilename($storedFilename);
    $storagePath = getUploadStoragePath($storedFilename);
    if ($storedFilename === null || $storagePath === null || !is_file($storagePath)) {
        return null;
    }

    $imageSize = @getimagesize($storagePath);
    if (!is_array($imageSize) || empty($imageSize[0]) || empty($imageSize[1])) {
        return null;
    }

    $sourceWidth = (int) $imageSize[0];
    if ($sourceWidth > 0 && $sourceWidth <= 500) {
        return $storedFilename;
    }

    $previewFilename = 'min_' . $storedFilename;
    $previewPath = getUploadStoragePath($previewFilename, true);
    if ($previewPath === null) {
        return null;
    }

    try {
        $image = new \Gumlet\ImageResize($storagePath);
        if ($sourceWidth > 500) {
            $image->resizeToWidth(500);
        }
        $image->save($previewPath);
    } catch (\Throwable $exception) {
        deleteStoredUploadFile($previewFilename);
        return null;
    }

    return $previewFilename;
}

function buildMediaItemRecordFromStoredUpload($storedFilename, $originalFilename)
{
    $storedFilename = normalizeStoredUploadFilename($storedFilename);
    if ($storedFilename === null) {
        return null;
    }

    $timestamp = time();
    $currentUserId = getCurrentUserId();
    $extension = strtolower((string) pathinfo($storedFilename, PATHINFO_EXTENSION));
    $mediaItem = [
        'file' => $storedFilename,
        'fileSmall' => $storedFilename,
        'originalName' => normalizeOriginalUploadFilename($originalFilename),
        'caption' => '',
        'altText' => '',
        'extension' => $extension,
        'editedUser' => $currentUserId,
        'edited' => $timestamp,
        'createdUser' => $currentUserId,
        'created' => $timestamp,
        'type' => in_array($extension, ['png', 'jpg', 'gif', 'jpeg', 'webp'], true) ? 'image' : 'file',
    ];

    if ($mediaItem['type'] === 'image') {
        $previewFilename = createImagePreviewForStoredUpload($storedFilename);
        if ($previewFilename === null) {
            return null;
        }

        $mediaItem['fileSmall'] = $previewFilename;
    }

    return $mediaItem;
}

function deleteMediaStorageFiles($mediaItem)
{
    if (!is_array($mediaItem)) {
        return false;
    }

    $filesToDelete = [];
    $previewFilename = normalizeStoredUploadFilename($mediaItem['fileSmall'] ?? '');
    $originalFilename = normalizeStoredUploadFilename($mediaItem['file'] ?? '');

    if (($mediaItem['type'] ?? '') === 'image' && $previewFilename !== null && $previewFilename !== $originalFilename) {
        $filesToDelete[] = $previewFilename;
    }

    if ($originalFilename !== null) {
        $filesToDelete[] = $originalFilename;
    }

    foreach ($filesToDelete as $storedFilename) {
        if (!deleteStoredUploadFile($storedFilename)) {
            return false;
        }
    }

    return true;
}

function prepareMediaItemForResponse($mediaItem)
{
    if (!is_array($mediaItem)) {
        return null;
    }

    $preparedItem = $mediaItem;
    $preparedItem['file'] = (string) ($preparedItem['file'] ?? '');
    $preparedItem['fileSmall'] = (string) ($preparedItem['fileSmall'] ?? $preparedItem['file']);
    $preparedItem['originalName'] = normalizeOriginalUploadFilename($preparedItem['originalName'] ?? '');
    $preparedItem['caption'] = trim((string) ($preparedItem['caption'] ?? ''));
    $preparedItem['altText'] = trim((string) ($preparedItem['altText'] ?? ''));
    $preparedItem['extension'] = strtolower((string) ($preparedItem['extension'] ?? pathinfo($preparedItem['file'], PATHINFO_EXTENSION)));

    if (($preparedItem['type'] ?? '') !== 'image' && ($preparedItem['type'] ?? '') !== 'file') {
        $preparedItem['type'] = in_array($preparedItem['extension'], ['png', 'jpg', 'gif', 'jpeg', 'webp'], true) ? 'image' : 'file';
    }

    $fileMetadata = getStoredUploadMetadata($preparedItem['file']);
    $previewFilename = $preparedItem['fileSmall'] !== '' ? $preparedItem['fileSmall'] : $preparedItem['file'];
    $previewMetadata = $previewFilename === $preparedItem['file']
        ? $fileMetadata
        : getStoredUploadMetadata($previewFilename);

    $storageIssues = [];
    if (!$fileMetadata['exists']) {
        $storageIssues[] = 'Original file missing';
    }

    if (
        $preparedItem['type'] === 'image'
        && $previewFilename !== $preparedItem['file']
        && !$previewMetadata['exists']
    ) {
        $storageIssues[] = 'Preview image missing';
    }

    $preparedItem['displayName'] = $preparedItem['originalName'] !== '' ? $preparedItem['originalName'] : $preparedItem['file'];
    $preparedItem['fileExists'] = $fileMetadata['exists'];
    $preparedItem['previewExists'] = $previewMetadata['exists'];
    $preparedItem['storageStatus'] = !$fileMetadata['exists']
        ? 'missing'
        : (count($storageIssues) > 0 ? 'degraded' : 'ready');
    $preparedItem['storageIssues'] = $storageIssues;
    $preparedItem['fileUrl'] = $fileMetadata['exists'] ? getStoredUploadPublicUrl($preparedItem['file']) : null;
    $preparedItem['previewUrl'] = $previewMetadata['exists']
        ? getStoredUploadPublicUrl($previewFilename)
        : ($fileMetadata['exists'] ? getStoredUploadPublicUrl($preparedItem['file']) : null);
    $preparedItem['fileSize'] = $fileMetadata['size'];
    $preparedItem['previewSize'] = $previewMetadata['size'];
    $preparedItem['mimeType'] = $fileMetadata['mimeType'] !== '' ? $fileMetadata['mimeType'] : $previewMetadata['mimeType'];
    $preparedItem['width'] = $fileMetadata['width'];
    $preparedItem['height'] = $fileMetadata['height'];
    $preparedItem['totalStorageBytes'] = $fileMetadata['size']
        + ($previewFilename !== $preparedItem['file'] ? $previewMetadata['size'] : 0);

    return $preparedItem;
}

function prepareMediaItemsForResponse($mediaItems)
{
    $preparedItems = [];

    foreach ($mediaItems as $mediaItem) {
        $preparedItem = prepareMediaItemForResponse($mediaItem);
        if ($preparedItem !== null) {
            $preparedItems[] = $preparedItem;
        }
    }

    return $preparedItems;
}

function getSiteOriginRaw()
{
    return (isHttpsRequest() ? 'https://' : 'http://') . getNormalizedRequestHost();
}

function getFullBasepathRaw()
{
    return getSiteOriginRaw() . BASEPATH;
}

function getFullBasepath() {
    return htmlspecialchars(getFullBasepathRaw(), ENT_QUOTES, 'UTF-8');
}

function getRenderablePagePath($page, $requirePublished = true)
{
    if (!is_array($page) || !empty($page['isPathless']) || ($requirePublished && empty($page['isPublished']))) {
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

function getPublicPagePath($page)
{
    return getRenderablePagePath($page, true);
}

function getMetaPagePath($page)
{
    return getRenderablePagePath($page, false);
}

function getPublicPageUrl($page)
{
    $path = getPublicPagePath($page);
    if ($path === null) {
        return null;
    }

    return rtrim(getFullBasepathRaw(), '/') . $path;
}

function getMetaPageUrl($page)
{
    $path = getMetaPagePath($page);
    if ($path === null) {
        return null;
    }

    return rtrim(getFullBasepathRaw(), '/') . $path;
}

function getPagePasswordHash($page)
{
    if (!is_array($page) || !isset($page['passwordHash']) || !is_string($page['passwordHash'])) {
        return '';
    }

    return $page['passwordHash'];
}

function isPagePasswordProtected($page)
{
    return getPagePasswordHash($page) !== '';
}

function getPageProtectionFingerprint($page)
{
    $pageId = isset($page['_id']) ? (string) $page['_id'] : '';
    $passwordHash = getPagePasswordHash($page);
    if ($pageId === '' || $passwordHash === '') {
        return '';
    }

    return hash('sha256', $pageId . '|' . $passwordHash);
}

function isPageUnlockedForCurrentVisitor($page)
{
    if (!isPagePasswordProtected($page)) {
        return true;
    }

    $pageId = isset($page['_id']) ? (string) $page['_id'] : '';
    $fingerprint = getPageProtectionFingerprint($page);
    if ($pageId === '' || $fingerprint === '') {
        return false;
    }

    $unlockedPages = $_SESSION['mirageUnlockedPages'] ?? [];
    $storedFingerprint = is_array($unlockedPages) && isset($unlockedPages[$pageId])
        ? (string) $unlockedPages[$pageId]
        : '';

    return $storedFingerprint !== '' && hash_equals($storedFingerprint, $fingerprint);
}

function unlockPageForCurrentVisitor($page)
{
    $pageId = isset($page['_id']) ? (string) $page['_id'] : '';
    $fingerprint = getPageProtectionFingerprint($page);
    if ($pageId === '' || $fingerprint === '') {
        return false;
    }

    if (!isset($_SESSION['mirageUnlockedPages']) || !is_array($_SESSION['mirageUnlockedPages'])) {
        $_SESSION['mirageUnlockedPages'] = [];
    }

    $_SESSION['mirageUnlockedPages'][$pageId] = $fingerprint;

    return true;
}

function preparePageForResponse($page)
{
    if (!is_array($page)) {
        return $page;
    }

    $preparedPage = $page;
    $preparedPage['isPasswordProtected'] = isPagePasswordProtected($page);
    unset($preparedPage['passwordHash']);

    return $preparedPage;
}

function preparePagesForResponse($pages)
{
    if (!is_array($pages)) {
        return [];
    }

    return array_map('preparePageForResponse', $pages);
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
        if (isPagePasswordProtected($page)) {
            continue;
        }

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
        'siteDescription' => '',
        'socialImage' => null,
        'socialLocale' => 'en_US',
        'twitterSite' => '',
        'twitterCreator' => '',
        'facebookAppId' => '',
        'footerText' => '',
        'copyrightText' => '{{year}} {{siteTitle}} - All Rights Reserved.',
        'googleAnalyticsTrackingCode' => ''
    ];
}

function normalizeGoogleAnalyticsTrackingCode($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    if (preg_match('/\b(G-[A-Z0-9]{4,}|UA-\d{4,10}-\d+)\b/i', $value, $matches) === 1) {
        return strtoupper($matches[1]);
    }

    return '';
}

function normalizeSocialLocale($value)
{
    $value = str_replace('-', '_', trim((string) $value));
    if ($value === '') {
        return 'en_US';
    }

    if (preg_match('/\A([a-z]{2})(?:_([a-z]{2}))?\z/i', $value, $matches) !== 1) {
        return 'en_US';
    }

    $language = strtolower((string) $matches[1]);
    $territory = isset($matches[2]) && $matches[2] !== ''
        ? strtoupper((string) $matches[2])
        : 'US';

    return $language . '_' . $territory;
}

function normalizeTwitterHandle($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    if (preg_match('#\Ahttps?://(?:www\.)?(?:twitter|x)\.com/([A-Za-z0-9_]+)#i', $value, $matches) === 1) {
        $value = (string) $matches[1];
    }

    $value = ltrim($value, '@');
    if ($value === '' || preg_match('/\A[A-Za-z0-9_]{1,50}\z/', $value) !== 1) {
        return '';
    }

    return '@' . $value;
}

function normalizeFacebookAppId($value)
{
    $digits = preg_replace('/\D+/', '', (string) $value);
    return is_string($digits) ? $digits : '';
}

function normalizeSiteSettings($settings)
{
    $defaults = getDefaultSiteSettings();
    $settings = is_array($settings) ? $settings : [];

    return [
        'siteTitle' => array_key_exists('siteTitle', $settings) ? trim((string) $settings['siteTitle']) : $defaults['siteTitle'],
        'siteDescription' => array_key_exists('siteDescription', $settings) ? trim((string) $settings['siteDescription']) : $defaults['siteDescription'],
        'socialImage' => array_key_exists('socialImage', $settings)
            ? normalizeOptionalMediaReference($settings['socialImage'])
            : $defaults['socialImage'],
        'socialLocale' => array_key_exists('socialLocale', $settings)
            ? normalizeSocialLocale($settings['socialLocale'])
            : $defaults['socialLocale'],
        'twitterSite' => array_key_exists('twitterSite', $settings)
            ? normalizeTwitterHandle($settings['twitterSite'])
            : $defaults['twitterSite'],
        'twitterCreator' => array_key_exists('twitterCreator', $settings)
            ? normalizeTwitterHandle($settings['twitterCreator'])
            : $defaults['twitterCreator'],
        'facebookAppId' => array_key_exists('facebookAppId', $settings)
            ? normalizeFacebookAppId($settings['facebookAppId'])
            : $defaults['facebookAppId'],
        'footerText' => array_key_exists('footerText', $settings) ? trim((string) $settings['footerText']) : $defaults['footerText'],
        'copyrightText' => array_key_exists('copyrightText', $settings) ? trim((string) $settings['copyrightText']) : $defaults['copyrightText'],
        'googleAnalyticsTrackingCode' => array_key_exists('googleAnalyticsTrackingCode', $settings)
            ? normalizeGoogleAnalyticsTrackingCode($settings['googleAnalyticsTrackingCode'])
            : $defaults['googleAnalyticsTrackingCode']
    ];
}

function getCurrentSiteSettings()
{
    global $siteTitle;
    global $siteDescription;
    global $socialImage;
    global $socialLocale;
    global $twitterSite;
    global $twitterCreator;
    global $facebookAppId;
    global $footerText;
    global $copyrightText;
    global $googleAnalyticsTrackingCode;

    $settings = [];
    if (isset($siteTitle)) {
        $settings['siteTitle'] = $siteTitle;
    }

    if (isset($siteDescription)) {
        $settings['siteDescription'] = $siteDescription;
    }

    if (isset($socialImage)) {
        $settings['socialImage'] = $socialImage;
    }

    if (isset($socialLocale)) {
        $settings['socialLocale'] = $socialLocale;
    }

    if (isset($twitterSite)) {
        $settings['twitterSite'] = $twitterSite;
    }

    if (isset($twitterCreator)) {
        $settings['twitterCreator'] = $twitterCreator;
    }

    if (isset($facebookAppId)) {
        $settings['facebookAppId'] = $facebookAppId;
    }

    if (isset($footerText)) {
        $settings['footerText'] = $footerText;
    }

    if (isset($copyrightText)) {
        $settings['copyrightText'] = $copyrightText;
    }

    if (isset($googleAnalyticsTrackingCode)) {
        $settings['googleAnalyticsTrackingCode'] = $googleAnalyticsTrackingCode;
    }

    return normalizeSiteSettings($settings);
}

function writeSiteConfigFile($settings)
{
    $settings = normalizeSiteSettings($settings);

    $configContents = "<?php\n\n";
    $configContents .= '$siteTitle = ' . var_export($settings['siteTitle'], true) . ";\n";
    $configContents .= '$siteDescription = ' . var_export($settings['siteDescription'], true) . ";\n";
    $configContents .= '$socialImage = ' . var_export($settings['socialImage'], true) . ";\n";
    $configContents .= '$socialLocale = ' . var_export($settings['socialLocale'], true) . ";\n";
    $configContents .= '$twitterSite = ' . var_export($settings['twitterSite'], true) . ";\n";
    $configContents .= '$twitterCreator = ' . var_export($settings['twitterCreator'], true) . ";\n";
    $configContents .= '$facebookAppId = ' . var_export($settings['facebookAppId'], true) . ";\n";
    $configContents .= '$footerText = ' . var_export($settings['footerText'], true) . ";\n";
    $configContents .= '$copyrightText = ' . var_export($settings['copyrightText'], true) . ";\n";
    $configContents .= '$googleAnalyticsTrackingCode = ' . var_export($settings['googleAnalyticsTrackingCode'], true) . ";\n";

    return file_put_contents(MIRAGE_ROOT . DIRECTORY_SEPARATOR . 'config.php', $configContents, LOCK_EX) !== false;
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

