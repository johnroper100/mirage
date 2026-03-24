<?php

function getMirageMetaTagMarkup()
{
    return '<meta name="mirage-head" content="integrations">';
}

function renderMirageMetaTag()
{
    return getMirageMetaTagMarkup();
}

function buildHeadMetaTag($attributeName, $attributeValue, $content)
{
    $attributeName = trim((string) $attributeName);
    $attributeValue = trim((string) $attributeValue);
    $content = trim((string) $content);

    if ($attributeName === '' || $attributeValue === '' || $content === '') {
        return '';
    }

    return '<meta ' . $attributeName . '="' . htmlspecialchars($attributeValue, ENT_QUOTES, 'UTF-8') . '" content="' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . '">';
}

function buildHeadLinkTag($rel, $href)
{
    $rel = trim((string) $rel);
    $href = trim((string) $href);

    if ($rel === '' || $href === '') {
        return '';
    }

    return '<link rel="' . htmlspecialchars($rel, ENT_QUOTES, 'UTF-8') . '" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">';
}

function getTextLength($value)
{
    $value = (string) $value;
    if (function_exists('mb_strlen')) {
        return (int) mb_strlen($value, 'UTF-8');
    }

    return strlen($value);
}

function getTextSlice($value, $start, $length = null)
{
    $value = (string) $value;
    if (function_exists('mb_substr')) {
        return $length === null
            ? (string) mb_substr($value, $start, null, 'UTF-8')
            : (string) mb_substr($value, $start, $length, 'UTF-8');
    }

    return $length === null ? substr($value, $start) : substr($value, $start, $length);
}

function getLastTextSpacePosition($value)
{
    $value = (string) $value;
    if (function_exists('mb_strrpos')) {
        return mb_strrpos($value, ' ', 0, 'UTF-8');
    }

    return strrpos($value, ' ');
}

function collapseWhitespace($value)
{
    $collapsed = preg_replace('/\s+/u', ' ', (string) $value);
    return trim($collapsed !== null ? $collapsed : (string) $value);
}

function truncateMetaText($text, $maxLength)
{
    $text = trim((string) $text);
    if ($maxLength <= 0 || getTextLength($text) <= $maxLength) {
        return $text;
    }

    if ($maxLength <= 3) {
        return getTextSlice($text, 0, $maxLength);
    }

    $truncated = getTextSlice($text, 0, $maxLength - 3);
    $lastSpace = getLastTextSpacePosition($truncated);
    if ($lastSpace !== false && $lastSpace >= (int) floor(($maxLength - 3) * 0.6)) {
        $truncated = getTextSlice($truncated, 0, $lastSpace);
    }

    return rtrim($truncated, " \t\n\r\0\x0B,.;:-") . '...';
}

function sanitizeMetaText($value, $maxLength = 0)
{
    if (is_array($value) || is_object($value)) {
        return '';
    }

    $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = collapseWhitespace($text);
    if ($maxLength > 0) {
        $text = truncateMetaText($text, $maxLength);
    }

    return $text;
}

function toAbsoluteSiteUrl($url)
{
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }

    if (preg_match('/\A[a-z][a-z0-9+.-]*:/i', $url) === 1) {
        return $url;
    }

    if (strpos($url, '//') === 0) {
        return (isHttpsRequest() ? 'https:' : 'http:') . $url;
    }

    if ($url[0] === '/') {
        return getSiteOriginRaw() . $url;
    }

    return rtrim(getFullBasepathRaw(), '/') . '/' . ltrim($url, '/');
}

function getCurrentRequestUrl()
{
    $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');
    $requestPath = $requestUri !== '' ? parse_url($requestUri, PHP_URL_PATH) : null;

    if (!is_string($requestPath) || $requestPath === '') {
        $requestPath = BASEPATH !== '' ? BASEPATH . '/' : '/';
    }

    return getSiteOriginRaw() . $requestPath;
}

function getPageContentFieldValue($page, $fieldName)
{
    if (!is_array($page) || !isset($page['content']) || !is_array($page['content']) || !array_key_exists($fieldName, $page['content'])) {
        return null;
    }

    return $page['content'][$fieldName];
}

function collectContentTextCandidates($value, &$candidates)
{
    if (is_array($value)) {
        foreach ($value as $childValue) {
            collectContentTextCandidates($childValue, $candidates);
        }
        return;
    }

    if (!is_scalar($value) || is_bool($value)) {
        return;
    }

    $text = sanitizeMetaText($value);
    if ($text === '' || preg_match('/\A\d+\z/', $text) === 1 || getTextLength($text) < 25) {
        return;
    }

    $candidates[] = $text;
}

function findFirstMeaningfulContentText($content)
{
    $candidates = [];
    collectContentTextCandidates($content, $candidates);
    if (count($candidates) === 0) {
        return '';
    }

    usort($candidates, function ($left, $right) {
        $leftLength = getTextLength($left);
        $rightLength = getTextLength($right);
        if ($rightLength === $leftLength) {
            return 0;
        }

        return $rightLength > $leftLength ? 1 : -1;
    });

    return truncateMetaText($candidates[0], 220);
}

function extractFirstMediaIdFromHtml($html)
{
    if (!is_string($html) || $html === '') {
        return null;
    }

    if (preg_match('/\bdata-media-id\s*=\s*["\']?(\d+)/i', $html, $matches) !== 1) {
        return null;
    }

    return (int) $matches[1];
}

function extractFirstImageUrlFromHtml($html)
{
    if (!is_string($html) || $html === '') {
        return '';
    }

    if (preg_match('/<img\b[^>]*\bsrc\s*=\s*["\']([^"\']+)["\']/i', $html, $matches) !== 1) {
        return '';
    }

    return trim((string) $matches[1]);
}

function collectContentImageCandidates($value, &$candidates, $keyName = '')
{
    if (is_array($value)) {
        foreach ($value as $key => $childValue) {
            $nextKeyName = is_string($key) ? $key : $keyName;
            collectContentImageCandidates($childValue, $candidates, $nextKeyName);
        }
        return;
    }

    if (
        is_scalar($value)
        && is_string($keyName)
        && $keyName !== ''
        && preg_match('/image|thumbnail|photo|cover|hero/i', $keyName) === 1
    ) {
        $candidates[] = $value;
    }

    if (!is_string($value) || $value === '') {
        return;
    }

    $mediaId = extractFirstMediaIdFromHtml($value);
    if ($mediaId !== null) {
        $candidates[] = $mediaId;
    }

    $imageUrl = extractFirstImageUrlFromHtml($value);
    if ($imageUrl !== '') {
        $candidates[] = $imageUrl;
    }
}

function resolveSocialImageDataFromReference($reference, $fallbackAlt = '')
{
    $reference = normalizeOptionalMediaReference($reference);
    if ($reference === null) {
        return null;
    }

    $fallbackAlt = sanitizeMetaText($fallbackAlt, 420);

    if (is_int($reference) || (is_string($reference) && ctype_digit($reference))) {
        $mediaItem = prepareMediaItemForResponse(getMedia((int) $reference));
        if (!is_array($mediaItem) || ($mediaItem['type'] ?? '') !== 'image' || empty($mediaItem['fileUrl'])) {
            return null;
        }

        $altText = trim((string) ($mediaItem['altText'] ?? ''));
        if ($altText === '') {
            $altText = trim((string) ($mediaItem['caption'] ?? ''));
        }
        if ($altText === '') {
            $altText = $fallbackAlt;
        }

        return [
            'url' => toAbsoluteSiteUrl((string) $mediaItem['fileUrl']),
            'alt' => sanitizeMetaText($altText, 420),
            'mimeType' => trim((string) ($mediaItem['mimeType'] ?? '')),
            'width' => isset($mediaItem['width']) ? (int) $mediaItem['width'] : null,
            'height' => isset($mediaItem['height']) ? (int) $mediaItem['height'] : null
        ];
    }

    if (is_string($reference)) {
        $reference = trim($reference);
        if ($reference === '' || (strpos($reference, '/') === false && strpos($reference, '.') === false && stripos($reference, 'http') !== 0)) {
            return null;
        }

        return [
            'url' => toAbsoluteSiteUrl($reference),
            'alt' => $fallbackAlt,
            'mimeType' => '',
            'width' => null,
            'height' => null
        ];
    }

    return null;
}

function getDefaultThemeSocialImageData($fallbackAlt = '')
{
    $themePreviewPath = MIRAGE_ROOT . '/theme/img/webpreview.png';
    if (!is_file($themePreviewPath)) {
        return null;
    }

    $imageSize = @getimagesize($themePreviewPath);

    return [
        'url' => toAbsoluteSiteUrl(BASEPATH . '/theme/img/webpreview.png'),
        'alt' => sanitizeMetaText($fallbackAlt, 420),
        'mimeType' => getUploadedFileMimeType($themePreviewPath),
        'width' => is_array($imageSize) && isset($imageSize[0]) ? (int) $imageSize[0] : null,
        'height' => is_array($imageSize) && isset($imageSize[1]) ? (int) $imageSize[1] : null
    ];
}

function isHomepagePage($page)
{
    return is_array($page)
        && trim((string) ($page['collectionSubpath'] ?? ''), '/') === ''
        && trim((string) ($page['path'] ?? ''), '/') === '';
}

function getPageSocialTitle($page, $siteSettings)
{
    $siteTitle = trim((string) ($siteSettings['siteTitle'] ?? ''));
    $pageTitle = is_array($page) ? trim((string) ($page['title'] ?? '')) : '';

    if ($pageTitle === '' || $pageTitle === $siteTitle || isHomepagePage($page) || strcasecmp($pageTitle, 'Home') === 0) {
        return $siteTitle !== '' ? $siteTitle : $pageTitle;
    }

    return $siteTitle !== '' ? $pageTitle . ' | ' . $siteTitle : $pageTitle;
}

function getPageSocialDescription($page, $siteSettings)
{
    $siteDescription = trim((string) ($siteSettings['siteDescription'] ?? ''));
    $candidates = [];

    if (is_array($page)) {
        $candidates[] = $page['description'] ?? '';
        $candidates[] = getPageContentFieldValue($page, 'description');
        if (isHomepagePage($page)) {
            $candidates[] = $siteDescription;
        }
        $candidates[] = getPageContentFieldValue($page, 'aboutBio');
        $candidates[] = getPageContentFieldValue($page, 'headerSubtitle');
        $candidates[] = getPageContentFieldValue($page, 'pageContent');
        $candidates[] = findFirstMeaningfulContentText($page['content'] ?? []);
    }

    $candidates[] = $siteDescription;

    foreach ($candidates as $candidate) {
        $description = sanitizeMetaText($candidate, 220);
        if ($description !== '') {
            return $description;
        }
    }

    return '';
}

function getPageSocialUrl($page)
{
    if (is_array($page)) {
        $pageUrl = getMetaPageUrl($page);
        if (is_string($pageUrl) && $pageUrl !== '') {
            return $pageUrl;
        }
    }

    return getCurrentRequestUrl();
}

function getPageSocialType($page)
{
    if (!is_array($page) || isHomepagePage($page)) {
        return 'website';
    }

    return 'article';
}

function getPageSocialImageData($page, $siteSettings, $fallbackAlt = '')
{
    $candidates = [];

    if (is_array($page)) {
        $candidates[] = $page['featuredImage'] ?? null;
        $candidates[] = getPageContentFieldValue($page, 'featuredImage');
        collectContentImageCandidates($page['content'] ?? [], $candidates);
    }

    $candidates[] = $siteSettings['socialImage'] ?? null;

    foreach ($candidates as $candidate) {
        $imageData = resolveSocialImageDataFromReference($candidate, $fallbackAlt);
        if ($imageData !== null) {
            return $imageData;
        }
    }

    return getDefaultThemeSocialImageData($fallbackAlt);
}

function buildSocialMetaTagsHtml($page = null, $siteSettings = null)
{
    $siteSettings = is_array($siteSettings) ? $siteSettings : getCurrentSiteSettings();
    $siteTitle = trim((string) ($siteSettings['siteTitle'] ?? ''));
    $locale = trim((string) ($siteSettings['socialLocale'] ?? ''));
    $twitterSite = trim((string) ($siteSettings['twitterSite'] ?? ''));
    $twitterCreator = trim((string) ($siteSettings['twitterCreator'] ?? ''));
    $facebookAppId = trim((string) ($siteSettings['facebookAppId'] ?? ''));

    if ($twitterCreator === '' && $twitterSite !== '') {
        $twitterCreator = $twitterSite;
    }

    $title = getPageSocialTitle($page, $siteSettings);
    if ($title === '') {
        $title = $siteTitle;
    }

    $description = getPageSocialDescription($page, $siteSettings);
    $url = getPageSocialUrl($page);
    $type = getPageSocialType($page);
    $imageData = getPageSocialImageData($page, $siteSettings, $title !== '' ? $title : $siteTitle);

    $tags = [];
    if ($description !== '') {
        $tags[] = buildHeadMetaTag('name', 'description', $description);
    }
    if ($url !== '') {
        $tags[] = buildHeadLinkTag('canonical', $url);
        $tags[] = buildHeadMetaTag('property', 'og:url', $url);
    }
    if ($siteTitle !== '') {
        $tags[] = buildHeadMetaTag('property', 'og:site_name', $siteTitle);
    }
    if ($locale !== '') {
        $tags[] = buildHeadMetaTag('property', 'og:locale', $locale);
    }
    if ($title !== '') {
        $tags[] = buildHeadMetaTag('property', 'og:title', $title);
        $tags[] = buildHeadMetaTag('name', 'twitter:title', $title);
    }

    $tags[] = buildHeadMetaTag('property', 'og:type', $type);

    if ($description !== '') {
        $tags[] = buildHeadMetaTag('property', 'og:description', $description);
        $tags[] = buildHeadMetaTag('name', 'twitter:description', $description);
    }

    if ($facebookAppId !== '') {
        $tags[] = buildHeadMetaTag('property', 'fb:app_id', $facebookAppId);
    }

    if ($imageData !== null && !empty($imageData['url'])) {
        $imageUrl = trim((string) $imageData['url']);
        $imageAlt = trim((string) ($imageData['alt'] ?? ''));
        $imageMimeType = trim((string) ($imageData['mimeType'] ?? ''));
        $imageWidth = isset($imageData['width']) ? (int) $imageData['width'] : null;
        $imageHeight = isset($imageData['height']) ? (int) $imageData['height'] : null;

        $tags[] = buildHeadMetaTag('property', 'og:image', $imageUrl);
        $tags[] = buildHeadMetaTag('property', 'og:image:url', $imageUrl);
        if (strpos($imageUrl, 'https://') === 0) {
            $tags[] = buildHeadMetaTag('property', 'og:image:secure_url', $imageUrl);
        }
        if ($imageMimeType !== '') {
            $tags[] = buildHeadMetaTag('property', 'og:image:type', $imageMimeType);
        }
        if ($imageWidth !== null && $imageWidth > 0) {
            $tags[] = buildHeadMetaTag('property', 'og:image:width', (string) $imageWidth);
        }
        if ($imageHeight !== null && $imageHeight > 0) {
            $tags[] = buildHeadMetaTag('property', 'og:image:height', (string) $imageHeight);
        }
        if ($imageAlt !== '') {
            $tags[] = buildHeadMetaTag('property', 'og:image:alt', $imageAlt);
            $tags[] = buildHeadMetaTag('name', 'twitter:image:alt', $imageAlt);
        }

        $tags[] = buildHeadMetaTag('name', 'twitter:image', $imageUrl);
    }

    $tags[] = buildHeadMetaTag('name', 'twitter:card', $imageData !== null ? 'summary_large_image' : 'summary');

    if ($twitterSite !== '') {
        $tags[] = buildHeadMetaTag('name', 'twitter:site', $twitterSite);
    }

    if ($twitterCreator !== '') {
        $tags[] = buildHeadMetaTag('name', 'twitter:creator', $twitterCreator);
    }

    if (is_array($page)) {
        $created = isset($page['created']) ? (int) $page['created'] : 0;
        $edited = max(isset($page['edited']) ? (int) $page['edited'] : 0, $created);

        if ($edited > 0) {
            $tags[] = buildHeadMetaTag('property', 'og:updated_time', gmdate(DATE_W3C, $edited));
        }

        if ($type === 'article') {
            if ($created > 0) {
                $tags[] = buildHeadMetaTag('property', 'article:published_time', gmdate(DATE_W3C, $created));
            }
            if ($edited > 0) {
                $tags[] = buildHeadMetaTag('property', 'article:modified_time', gmdate(DATE_W3C, $edited));
            }
        }
    }

    return implode("\n", array_filter($tags));
}

function htmlContainsGoogleAnalyticsSnippet($html)
{
    return stripos($html, 'googletagmanager.com/gtag/js') !== false
        || stripos($html, 'google-analytics.com/analytics.js') !== false
        || stripos($html, 'GoogleAnalyticsObject') !== false;
}

function buildGoogleAnalyticsHeadHtml($trackingCode)
{
    $trackingCode = normalizeGoogleAnalyticsTrackingCode($trackingCode);
    if ($trackingCode === '') {
        return '';
    }

    $encodedTrackingCode = json_encode($trackingCode, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    $escapedTrackingCode = htmlspecialchars($trackingCode, ENT_QUOTES, 'UTF-8');

    return '<script async src="https://www.googletagmanager.com/gtag/js?id=' . $escapedTrackingCode . '"></script>' . "\n"
        . '<script>'
        . 'window.dataLayer=window.dataLayer||[];'
        . 'function gtag(){dataLayer.push(arguments);}'
        . 'gtag(\'js\', new Date());'
        . 'gtag(\'config\', ' . $encodedTrackingCode . ');'
        . '</script>';
}

function buildMirageHeadIntegrationsHtml($page = null)
{
    $siteSettings = getCurrentSiteSettings();
    $snippets = [];

    $socialMetaTags = buildSocialMetaTagsHtml($page, $siteSettings);
    if ($socialMetaTags !== '') {
        $snippets[] = $socialMetaTags;
    }

    if ($siteSettings['googleAnalyticsTrackingCode'] !== '') {
        $googleAnalyticsSnippet = buildGoogleAnalyticsHeadHtml($siteSettings['googleAnalyticsTrackingCode']);
        if ($googleAnalyticsSnippet !== '') {
            $snippets[] = $googleAnalyticsSnippet;
        }
    }

    return implode("\n", $snippets);
}

function injectMirageHeadIntegrations($html, $page = null)
{
    $headIntegrations = buildMirageHeadIntegrationsHtml($page);
    $placeholderPattern = '/<meta\b(?=[^>]*\bname\s*=\s*(["\'])mirage-head\1)(?=[^>]*\bcontent\s*=\s*(["\'])integrations\2)[^>]*\/?>/i';
    $placeholderCount = 0;

    $html = preg_replace($placeholderPattern, $headIntegrations, $html, 1, $placeholderCount);
    if ($placeholderCount > 0) {
        return $html;
    }

    if ($headIntegrations !== '' && !htmlContainsGoogleAnalyticsSnippet($html)) {
        if (stripos($html, '</head>') !== false) {
            $html = preg_replace('/<\/head>/i', $headIntegrations . "\n</head>", $html, 1);
        } else {
            $html = $headIntegrations . "\n" . $html;
        }
    }

    return $html;
}

function isLikelyAnalyticsBotRequest()
{
    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? trim((string) $_SERVER['HTTP_USER_AGENT']) : '';
    if ($userAgent === '') {
        return true;
    }

    return preg_match('/bot|spider|crawl|slurp|preview|facebookexternalhit|whatsapp|discordbot|telegrambot|linkedinbot|pingdom|uptimerobot/i', $userAgent) === 1;
}

function getAnalyticsVisitorId()
{
    $sessionId = session_id();
    if (!is_string($sessionId) || $sessionId === '') {
        return null;
    }

    return substr(hash('sha256', $sessionId), 0, 40);
}

function cleanupAnalyticsEvents()
{
    global $analyticsEventStore;

    $cutoff = time() - (7 * 86400);

    do {
        $expiredEvents = $analyticsEventStore->findBy([
            ['created', '<', $cutoff]
        ], ['created' => 'asc'], 250);

        foreach ($expiredEvents as $expiredEvent) {
            if (isset($expiredEvent['_id'])) {
                $analyticsEventStore->deleteById($expiredEvent['_id']);
            }
        }
    } while (count($expiredEvents) === 250);
}

function maybeCleanupAnalyticsEvents()
{
    $lastCleanup = isset($_SESSION['mirageAnalyticsCleanupAt']) ? (int) $_SESSION['mirageAnalyticsCleanupAt'] : 0;
    if ($lastCleanup > 0 && (time() - $lastCleanup) < 3600) {
        return;
    }

    cleanupAnalyticsEvents();
    $_SESSION['mirageAnalyticsCleanupAt'] = time();
}

function shouldRecordAnalyticsPageView($page)
{
    if (!is_array($page) || isLoggedIn()) {
        return false;
    }

    $requestMethod = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if ($requestMethod !== 'GET') {
        return false;
    }

    if (isLikelyAnalyticsBotRequest()) {
        return false;
    }

    if (($page['isPublished'] ?? true) === false) {
        return false;
    }

    return getPublicPagePath($page) !== null;
}

function recordAnalyticsPageView($page)
{
    if (!shouldRecordAnalyticsPageView($page)) {
        return;
    }

    $visitorId = getAnalyticsVisitorId();
    if ($visitorId === null) {
        return;
    }

    global $analyticsEventStore;

    maybeCleanupAnalyticsEvents();

    $path = getPublicPagePath($page);
    if ($path === null) {
        return;
    }

    $recentEvents = $analyticsEventStore->findBy([
        ['visitorId', '=', $visitorId],
        'AND',
        ['path', '=', $path]
    ], ['created' => 'desc'], 1);

    if (!empty($recentEvents) && isset($recentEvents[0]['created']) && (time() - (int) $recentEvents[0]['created']) < 10) {
        return;
    }

    $siteSettings = getCurrentSiteSettings();
    $title = trim((string) ($page['title'] ?? ''));
    if ($title === '') {
        $title = (string) ($siteSettings['siteTitle'] ?? '');
    }

    $analyticsEventStore->insert([
        'visitorId' => $visitorId,
        'path' => $path,
        'title' => $title,
        'pageId' => isset($page['_id']) ? (string) $page['_id'] : '',
        'collection' => isset($page['collection']) ? (string) $page['collection'] : '',
        'templateName' => isset($page['templateName']) ? (string) $page['templateName'] : '',
        'created' => time()
    ]);
}

function getAnalyticsSummary()
{
    global $analyticsEventStore;

    maybeCleanupAnalyticsEvents();

    $now = time();
    $lastFiveMinutes = $analyticsEventStore->findBy([
        ['created', '>=', $now - 300]
    ], ['created' => 'desc']);
    $lastThirtyMinutes = $analyticsEventStore->findBy([
        ['created', '>=', $now - 1800]
    ], ['created' => 'desc']);
    $lastDay = $analyticsEventStore->findBy([
        ['created', '>=', $now - 86400]
    ], ['created' => 'desc']);
    $today = $analyticsEventStore->findBy([
        ['created', '>=', strtotime('today')]
    ], ['created' => 'desc']);

    $activeVisitors = [];
    foreach ($lastFiveMinutes as $event) {
        $visitorId = (string) ($event['visitorId'] ?? '');
        if ($visitorId !== '') {
            $activeVisitors[$visitorId] = true;
        }
    }

    $topPages = [];
    foreach ($lastDay as $event) {
        $path = trim((string) ($event['path'] ?? ''));
        if ($path === '') {
            continue;
        }

        if (!isset($topPages[$path])) {
            $topPages[$path] = [
                'path' => $path,
                'title' => trim((string) ($event['title'] ?? '')),
                'views' => 0
            ];
        }

        $topPages[$path]['views'] += 1;
        if ($topPages[$path]['title'] === '' && !empty($event['title'])) {
            $topPages[$path]['title'] = trim((string) $event['title']);
        }
    }

    uasort($topPages, function ($left, $right) {
        if ($right['views'] !== $left['views']) {
            return $right['views'] - $left['views'];
        }

        return strcmp((string) $left['path'], (string) $right['path']);
    });

    $recentViews = [];
    foreach (array_slice($lastThirtyMinutes, 0, 8) as $event) {
        $recentViews[] = [
            'path' => (string) ($event['path'] ?? ''),
            'title' => (string) ($event['title'] ?? ''),
            'created' => (int) ($event['created'] ?? 0)
        ];
    }

    $siteSettings = getCurrentSiteSettings();

    return [
        'trackingConfigured' => $siteSettings['googleAnalyticsTrackingCode'] !== '',
        'trackingCode' => $siteSettings['googleAnalyticsTrackingCode'],
        'activeVisitors' => count($activeVisitors),
        'pageViewsLast30Minutes' => count($lastThirtyMinutes),
        'pageViewsToday' => count($today),
        'topPages' => array_values(array_slice($topPages, 0, 5)),
        'recentViews' => $recentViews,
        'lastUpdated' => $now
    ];
}

