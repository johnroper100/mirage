<?php

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
        $themeConfigPath = MIRAGE_ROOT . '/theme/config.json';
        if (file_exists($themeConfigPath)) {
            $decoded = json_decode(file_get_contents($themeConfigPath), true);
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

    $path = MIRAGE_ROOT . '/theme/template_defs/' . $templateFile;
    if (!file_exists($path)) {
        return null;
    }

    return $path;
}

function normalizeMediaFieldAccepts($accepts, $legacySubtype = null)
{
    $normalizedAccepts = strtolower(trim((string) $accepts));
    if ($normalizedAccepts === '') {
        $normalizedAccepts = strtolower(trim((string) $legacySubtype));
    }

    if (!in_array($normalizedAccepts, ['image', 'file', 'both'], true)) {
        return 'both';
    }

    return $normalizedAccepts;
}

function normalizeTemplateFieldDefinition($field)
{
    if (!is_array($field)) {
        return $field;
    }

    if (($field['type'] ?? '') === 'media') {
        $field['accepts'] = normalizeMediaFieldAccepts($field['accepts'] ?? '', $field['subtype'] ?? '');
        if (!isset($field['subtype']) && $field['accepts'] !== 'both') {
            $field['subtype'] = $field['accepts'];
        }
    }

    if (($field['type'] ?? '') === 'list' && isset($field['fields']) && is_array($field['fields'])) {
        foreach ($field['fields'] as $index => $subField) {
            $field['fields'][$index] = normalizeTemplateFieldDefinition($subField);
        }
    }

    return $field;
}

function normalizeTemplateDefinition($templateDefinition)
{
    if (!is_array($templateDefinition)) {
        return null;
    }

    if (isset($templateDefinition['sections']) && is_array($templateDefinition['sections'])) {
        foreach ($templateDefinition['sections'] as $sectionIndex => $section) {
            if (!is_array($section) || !isset($section['fields']) || !is_array($section['fields'])) {
                continue;
            }

            foreach ($section['fields'] as $fieldIndex => $field) {
                $templateDefinition['sections'][$sectionIndex]['fields'][$fieldIndex] = normalizeTemplateFieldDefinition($field);
            }
        }
    }

    return $templateDefinition;
}

function loadTemplateDefinition($templateID)
{
    $templatePath = getTemplateDefinitionPath($templateID);
    if ($templatePath === null || !file_exists($templatePath)) {
        return null;
    }

    $decodedTemplate = json_decode(file_get_contents($templatePath), true);
    if (!is_array($decodedTemplate)) {
        return null;
    }

    return normalizeTemplateDefinition($decodedTemplate);
}

function getThemeTemplatePhpPath($templateID)
{
    if (getTemplateConfigById($templateID) === null) {
        return null;
    }

    $path = MIRAGE_ROOT . '/theme/' . $templateID . '.php';
    if (!file_exists($path)) {
        return null;
    }

    return $path;
}

