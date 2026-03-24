<?php

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
        || !isset($spamProtection[$formID]['honeypotName'])
        || (time() - (int) $spamProtection[$formID]['generatedAt']) > 7200
    ) {
        $spamProtection[$formID] = [
            'token' => bin2hex(random_bytes(16)),
            'generatedAt' => time(),
            'honeypotName' => '_mirage_hp_' . bin2hex(random_bytes(6))
        ];
    }

    $_SESSION['formSpamProtection'] = $spamProtection;

    return $spamProtection[$formID];
}

function normalizePostedFormValue($value)
{
    if (is_array($value) || is_object($value)) {
        return '';
    }

    return trim((string) $value);
}

function isFormFieldRequired($field)
{
    return !array_key_exists('required', $field) || !empty($field['required']);
}

function getSubmittedFormFieldValues($form)
{
    $submittedFields = [];
    $fields = isset($form['fields']) && is_array($form['fields']) ? $form['fields'] : [];

    foreach ($fields as $field) {
        $fieldID = trim((string) ($field['id'] ?? ''));
        if ($fieldID === '') {
            continue;
        }

        $submittedFields[$fieldID] = normalizePostedFormValue($_POST[$fieldID] ?? '');
    }

    return $submittedFields;
}

function countUrlsInText($text)
{
    $text = (string) $text;
    if ($text === '') {
        return 0;
    }

    $matches = [];
    preg_match_all('~(?:https?://|www\.)\S+~i', $text, $matches);
    return count($matches[0]);
}

function countUrlsInSubmittedFields($submittedFields)
{
    $linkCount = 0;
    foreach ($submittedFields as $fieldValue) {
        $linkCount += countUrlsInText((string) $fieldValue);
    }

    return $linkCount;
}

function validateSubmittedFormFields($form, $submittedFields)
{
    $fields = isset($form['fields']) && is_array($form['fields']) ? $form['fields'] : [];
    foreach ($fields as $field) {
        $fieldID = trim((string) ($field['id'] ?? ''));
        if ($fieldID === '') {
            continue;
        }

        $fieldType = strtolower(trim((string) ($field['type'] ?? 'text')));
        $fieldValue = (string) ($submittedFields[$fieldID] ?? '');

        if (isFormFieldRequired($field) && $fieldValue === '') {
            return 'missing_required_field';
        }

        if ($fieldType === 'email' && $fieldValue !== '' && !isValidEmailAddress(normalizeEmailAddress($fieldValue))) {
            return 'invalid_email';
        }

        if (strlen($fieldValue) > 4000) {
            return 'field_too_long';
        }
    }

    if (countUrlsInSubmittedFields($submittedFields) > 2) {
        return 'too_many_links';
    }

    return null;
}

function normalizeFingerprintValue($value)
{
    $value = strtolower(trim((string) $value));
    return preg_replace('/\s+/', ' ', $value);
}

function buildFormSubmissionFingerprint($formID, $submittedFields)
{
    ksort($submittedFields);

    $segments = [(string) $formID];
    foreach ($submittedFields as $fieldID => $fieldValue) {
        $segments[] = (string) $fieldID . '=' . normalizeFingerprintValue($fieldValue);
    }

    return hash('sha256', implode('|', $segments));
}

function findRecentFormSubmissionByFingerprint($formID, $fingerprint)
{
    global $formStore;

    $submissions = $formStore->findBy([
        ["form", "=", $formID],
        ["fingerprint", "=", $fingerprint]
    ], ["created" => "desc"], 1);

    if (!is_array($submissions) || count($submissions) === 0) {
        return null;
    }

    return $submissions[0];
}

function getRecentFormAttempts($formID, $ipAddress, $limit = 25)
{
    global $formAttemptStore;

    try {
        $attempts = $formAttemptStore->findBy([
            ["form", "=", $formID],
            ["ipAddress", "=", $ipAddress]
        ], ["created" => "desc"], $limit);
    } catch (\Throwable $exception) {
        return [];
    }

    return is_array($attempts) ? $attempts : [];
}

function hasExceededFormAttemptLimit($formID, $ipAddress)
{
    $attempts = getRecentFormAttempts($formID, $ipAddress);
    if (count($attempts) === 0) {
        return false;
    }

    $now = time();
    $attemptsInQuarterHour = 0;
    $attemptsInDay = 0;

    foreach ($attempts as $attempt) {
        $createdAt = (int) ($attempt['created'] ?? 0);
        if ($createdAt >= ($now - 900)) {
            $attemptsInQuarterHour++;
        }

        if ($createdAt >= ($now - 86400)) {
            $attemptsInDay++;
        }
    }

    return $attemptsInQuarterHour >= 5 || $attemptsInDay >= 12;
}

function recordFormAttempt($formID, $outcome, $reason, $submittedFields = [])
{
    global $formAttemptStore;

    $submittedFields = is_array($submittedFields) ? $submittedFields : [];
    $emailAddress = isset($submittedFields['email']) ? normalizeEmailAddress($submittedFields['email']) : '';

    try {
        $formAttemptStore->insert([
            'form' => (string) $formID,
            'outcome' => substr((string) $outcome, 0, 32),
            'reason' => substr((string) $reason, 0, 64),
            'email' => substr($emailAddress, 0, 254),
            'fingerprint' => buildFormSubmissionFingerprint($formID, $submittedFields),
            'created' => time(),
            'ipAddress' => getClientIpAddress(),
            'userAgent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr((string) $_SERVER['HTTP_USER_AGENT'], 0, 500) : ''
        ]);
    } catch (\Throwable $exception) {
        return;
    }
}

function isSameOriginFormRequest()
{
    $expectedHost = strtolower(getNormalizedRequestHost());
    $candidateUrls = [];

    if (!empty($_SERVER['HTTP_ORIGIN'])) {
        $candidateUrls[] = (string) $_SERVER['HTTP_ORIGIN'];
    }

    if (!empty($_SERVER['HTTP_REFERER'])) {
        $candidateUrls[] = (string) $_SERVER['HTTP_REFERER'];
    }

    if (count($candidateUrls) === 0) {
        return true;
    }

    foreach ($candidateUrls as $candidateUrl) {
        $parsedUrl = parse_url($candidateUrl);
        if (!is_array($parsedUrl) || empty($parsedUrl['host'])) {
            return false;
        }

        $candidateHost = strtolower($parsedUrl['host'] . (isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : ''));
        if (!hash_equals($expectedHost, $candidateHost)) {
            return false;
        }
    }

    return true;
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
            . '<input type="text" name="' . htmlspecialchars($protection['honeypotName'], ENT_QUOTES, 'UTF-8') . '" tabindex="-1" autocomplete="new-password">'
            . '</label>'
            . '</div>'
            . '<input type="hidden" name="_mirage_form_token" value="' . htmlspecialchars($protection['token'], ENT_QUOTES, 'UTF-8') . '">';

        return $matches[0] . $injectedFields;
    }, $html);
}

