<?php

function injectMirageFrontendAssets($html, $page = null)
{
    $html = injectMirageHeadIntegrations($html, $page);

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

    if (!array_key_exists('googleAnalyticsTrackingCode', $data)) {
        $data['googleAnalyticsTrackingCode'] = $siteSettings['googleAnalyticsTrackingCode'];
    }

    if (!array_key_exists('mirageMetaTag', $data)) {
        $data['mirageMetaTag'] = renderMirageMetaTag();
    }

    recordAnalyticsPageView($data['page']);

    extract($data, EXTR_SKIP);

    ob_start();
    include $filename;
    $output = ob_get_clean();

    $output = injectSpamProtectionIntoForms($output);
    echo injectMirageFrontendAssets($output, $data['page']);
}

function canRenderPublicPage($page)
{
    return is_array($page)
        && empty($page['isPathless'])
        && (!empty($page['isPublished']) || isLoggedIn());
}

function renderProtectedPagePrompt($page, $errorMessage = '', $statusCode = 200)
{
    if (!is_array($page)) {
        getErrorPage(404);
        return;
    }

    $pagePath = getMetaPagePath($page);
    if ($pagePath === null) {
        getErrorPage(404);
        return;
    }

    $pageForResponse = preparePageForResponse($page);
    $siteSettings = getCurrentSiteSettings();
    $siteTitle = trim((string) ($siteSettings['siteTitle'] ?? ''));
    $pageTitle = trim((string) ($pageForResponse['title'] ?? ''));
    $documentTitle = $pageTitle !== '' ? $pageTitle : 'Protected Page';
    if ($siteTitle !== '' && strcasecmp($documentTitle, $siteTitle) !== 0) {
        $documentTitle .= ' | ' . $siteTitle;
    }

    $description = trim((string) ($pageForResponse['description'] ?? ''));
    $promptTitle = $pageTitle !== '' ? $pageTitle : 'Protected Page';
    $actionUrl = BASEPATH . $pagePath;
    $siteTitleLabel = $siteTitle !== '' ? $siteTitle : 'Mirage';
    $errorMessage = trim((string) $errorMessage);
    $socialMetaTags = buildSocialMetaTagsHtml($pageForResponse, $siteSettings);

    http_response_code($statusCode);
    include MIRAGE_ROOT . '/dashboard/pagePassword.php';
}

function handleProtectedPageAccess($page)
{
    if (!canRenderPublicPage($page) || !isPagePasswordProtected($page)) {
        getErrorPage(404);
        return;
    }

    if (isPageUnlockedForCurrentVisitor($page)) {
        $pagePath = getMetaPagePath($page);
        if ($pagePath === null) {
            getErrorPage(404);
            return;
        }

        header('Location: ' . BASEPATH . $pagePath);
        return;
    }

    if (!requireCsrfToken()) {
        return;
    }

    $submittedPassword = isset($_POST['pagePassword']) ? (string) $_POST['pagePassword'] : '';
    $passwordHash = getPagePasswordHash($page);
    if ($submittedPassword !== '' && $passwordHash !== '' && password_verify($submittedPassword, $passwordHash)) {
        unlockPageForCurrentVisitor($page);

        $pagePath = getMetaPagePath($page);
        if ($pagePath === null) {
            getErrorPage(404);
            return;
        }

        header('Location: ' . BASEPATH . $pagePath);
        return;
    }

    renderProtectedPagePrompt($page, 'Incorrect password. Please try again.', 401);
}

function outputPublicPage($page, $siteTitle)
{
    if (!canRenderPublicPage($page)) {
        getErrorPage(404);
        return;
    }

    if (isPagePasswordProtected($page) && !isPageUnlockedForCurrentVisitor($page)) {
        renderProtectedPagePrompt($page);
        return;
    }

    $filename = getThemeTemplatePhpPath((string) ($page["templateName"] ?? ''));
    if ($filename === null) {
        getErrorPage(404);
        return;
    }

    includeThemeFile($filename, [
        'page' => preparePageForResponse($page),
        'siteTitle' => $siteTitle
    ]);
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
    if (
        $storedProtection == null
        || !isset($storedProtection['token'])
        || !isset($storedProtection['generatedAt'])
        || !isset($storedProtection['honeypotName'])
        || $submittedToken === ''
        || !hash_equals($storedProtection['token'], $submittedToken)
    ) {
        return true;
    }

    $honeypotName = (string) $storedProtection['honeypotName'];
    if ($honeypotName === '' || !array_key_exists($honeypotName, $_POST)) {
        return true;
    }

    $honeypotValue = normalizePostedFormValue($_POST[$honeypotName] ?? '');
    if ($honeypotValue !== '') {
        return true;
    }

    $secondsSinceRendered = time() - $storedProtection['generatedAt'];
    if ($secondsSinceRendered < 3 || $secondsSinceRendered > 7200) {
        return true;
    }

    $recentSubmission = getRecentFormSubmission($formID, getClientIpAddress());
    if ($recentSubmission != null && isset($recentSubmission['created']) && (time() - (int) $recentSubmission['created']) < 300) {
        return true;
    }

    return false;
}

