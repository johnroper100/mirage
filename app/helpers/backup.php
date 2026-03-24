<?php

function buildFullBackupDownloadFilename($extension = 'zip')
{
    $normalizedHost = strtolower(preg_replace('/[^a-z0-9.-]+/i', '-', getNormalizedRequestHost()));
    $normalizedHost = trim((string) $normalizedHost, '-.');
    if ($normalizedHost === '') {
        $normalizedHost = 'site';
    }

    $extension = ltrim(strtolower(trim((string) $extension)), '.');
    if ($extension === '') {
        $extension = 'zip';
    }

    return 'mirage-backup-' . $normalizedHost . '-' . date('Ymd-His') . '.' . $extension;
}

function getFullBackupTemporaryDirectory()
{
    $candidates = [
        sys_get_temp_dir(),
        MIRAGE_ROOT
    ];

    foreach ($candidates as $candidate) {
        $candidate = is_string($candidate) ? trim($candidate) : '';
        if ($candidate === '') {
            continue;
        }

        if (is_dir($candidate) && is_writable($candidate)) {
            return $candidate;
        }
    }

    return null;
}

function createTemporaryFullBackupArchivePath($extension = 'zip')
{
    $temporaryDirectory = getFullBackupTemporaryDirectory();
    if ($temporaryDirectory === null) {
        return null;
    }

    $temporaryBasePath = tempnam($temporaryDirectory, 'mirage-backup-');
    if ($temporaryBasePath === false) {
        return null;
    }

    if (file_exists($temporaryBasePath)) {
        @unlink($temporaryBasePath);
    }

    $extension = ltrim(strtolower(trim((string) $extension)), '.');
    if ($extension === '') {
        $extension = 'zip';
    }

    return $temporaryBasePath . '.' . $extension;
}

function addDirectoryToZipArchive($zipArchive, $sourceDirectory, $archiveDirectory)
{
    if (!($zipArchive instanceof ZipArchive) || !is_dir($sourceDirectory)) {
        return false;
    }

    $resolvedSourceDirectory = realpath($sourceDirectory);
    if ($resolvedSourceDirectory === false) {
        return false;
    }

    $archiveDirectory = trim(str_replace('\\', '/', (string) $archiveDirectory), '/');
    if ($archiveDirectory !== '' && !$zipArchive->addEmptyDir($archiveDirectory)) {
        return false;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($resolvedSourceDirectory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        if ($item->isLink()) {
            continue;
        }

        $itemPath = $item->getPathname();
        $relativePath = substr($itemPath, strlen($resolvedSourceDirectory) + 1);
        $relativePath = str_replace('\\', '/', (string) $relativePath);
        $archivePath = $archiveDirectory !== ''
            ? $archiveDirectory . '/' . $relativePath
            : $relativePath;

        if ($item->isDir()) {
            if (!$zipArchive->addEmptyDir($archivePath)) {
                return false;
            }
            continue;
        }

        if (!$zipArchive->addFile($itemPath, $archivePath)) {
            return false;
        }
    }

    return true;
}

function addDirectoryToPharArchive($pharArchive, $sourceDirectory, $archiveDirectory)
{
    if (!($pharArchive instanceof PharData) || !is_dir($sourceDirectory)) {
        return false;
    }

    $resolvedSourceDirectory = realpath($sourceDirectory);
    if ($resolvedSourceDirectory === false) {
        return false;
    }

    $archiveDirectory = trim(str_replace('\\', '/', (string) $archiveDirectory), '/');

    try {
        if ($archiveDirectory !== '') {
            $pharArchive->addEmptyDir($archiveDirectory);
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($resolvedSourceDirectory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isLink()) {
                continue;
            }

            $itemPath = $item->getPathname();
            $relativePath = substr($itemPath, strlen($resolvedSourceDirectory) + 1);
            $relativePath = str_replace('\\', '/', (string) $relativePath);
            $archivePath = $archiveDirectory !== ''
                ? $archiveDirectory . '/' . $relativePath
                : $relativePath;

            if ($item->isDir()) {
                $pharArchive->addEmptyDir($archivePath);
                continue;
            }

            $pharArchive->addFile($itemPath, $archivePath);
        }
    } catch (Throwable $exception) {
        return false;
    }

    return true;
}

function createFullBackupZipArchive($configPath, $databasePath, $uploadsPath)
{
    $archivePath = createTemporaryFullBackupArchivePath('zip');
    if ($archivePath === null) {
        return [
            'success' => false,
            'message' => 'The server could not create a temporary backup file.'
        ];
    }

    $zipArchive = new ZipArchive();
    $openResult = $zipArchive->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    if ($openResult !== true) {
        @unlink($archivePath);

        return [
            'success' => false,
            'message' => 'The server could not prepare the backup archive.'
        ];
    }

    $backupBuilt = $zipArchive->addFile($configPath, 'config.php')
        && addDirectoryToZipArchive($zipArchive, $databasePath, 'database')
        && addDirectoryToZipArchive($zipArchive, $uploadsPath, 'uploads');

    if (!$backupBuilt) {
        $zipArchive->close();
        @unlink($archivePath);

        return [
            'success' => false,
            'message' => 'The server could not package the full backup.'
        ];
    }

    if (!$zipArchive->close() || !is_file($archivePath)) {
        @unlink($archivePath);

        return [
            'success' => false,
            'message' => 'The backup archive could not be finalized.'
        ];
    }

    return [
        'success' => true,
        'archivePath' => $archivePath,
        'downloadName' => buildFullBackupDownloadFilename('zip'),
        'contentType' => 'application/zip'
    ];
}

function createFullBackupTarArchive($configPath, $databasePath, $uploadsPath)
{
    $archivePath = createTemporaryFullBackupArchivePath('tar');
    if ($archivePath === null) {
        return [
            'success' => false,
            'message' => 'The server could not create a temporary backup file.'
        ];
    }

    try {
        $pharArchive = new PharData($archivePath);
        $pharArchive->addFile($configPath, 'config.php');
        $backupBuilt = addDirectoryToPharArchive($pharArchive, $databasePath, 'database')
            && addDirectoryToPharArchive($pharArchive, $uploadsPath, 'uploads');
    } catch (Throwable $exception) {
        $backupBuilt = false;
    }

    if (!$backupBuilt || !is_file($archivePath)) {
        @unlink($archivePath);

        return [
            'success' => false,
            'message' => 'The server could not package the full backup.'
        ];
    }

    return [
        'success' => true,
        'archivePath' => $archivePath,
        'downloadName' => buildFullBackupDownloadFilename('tar'),
        'contentType' => 'application/x-tar'
    ];
}

function createFullBackupArchive()
{
    $configPath = MIRAGE_ROOT . DIRECTORY_SEPARATOR . 'config.php';
    $databasePath = MIRAGE_ROOT . DIRECTORY_SEPARATOR . 'database';
    $uploadsPath = MIRAGE_ROOT . DIRECTORY_SEPARATOR . 'uploads';

    if (!is_file($configPath)) {
        return [
            'success' => false,
            'message' => 'The site settings file could not be found.'
        ];
    }

    if (!is_dir($databasePath) || !is_dir($uploadsPath)) {
        return [
            'success' => false,
            'message' => 'The database or uploads directory is missing.'
        ];
    }

    if (class_exists('ZipArchive')) {
        return createFullBackupZipArchive($configPath, $databasePath, $uploadsPath);
    }

    if (class_exists('PharData')) {
        return createFullBackupTarArchive($configPath, $databasePath, $uploadsPath);
    }

    return [
        'success' => false,
        'message' => 'Full backups require the PHP ZipArchive or PharData extension on this server.'
    ];
}

function deleteGeneratedFullBackupArchive($backupArchive)
{
    $archivePath = is_array($backupArchive) ? (string) ($backupArchive['archivePath'] ?? '') : '';
    if ($archivePath !== '' && is_file($archivePath)) {
        @unlink($archivePath);
    }
}

function streamFullBackupArchive($backupArchive)
{
    $archivePath = is_array($backupArchive) ? (string) ($backupArchive['archivePath'] ?? '') : '';
    $downloadName = is_array($backupArchive) ? (string) ($backupArchive['downloadName'] ?? 'mirage-backup.zip') : 'mirage-backup.zip';
    $contentType = is_array($backupArchive) ? (string) ($backupArchive['contentType'] ?? 'application/octet-stream') : 'application/octet-stream';

    if ($archivePath === '' || !is_file($archivePath)) {
        return false;
    }

    register_shutdown_function('deleteGeneratedFullBackupArchive', $backupArchive);

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }

    @set_time_limit(0);
    ignore_user_abort(true);

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    $archiveSize = filesize($archivePath);
    $handle = fopen($archivePath, 'rb');
    if ($handle === false) {
        return false;
    }

    header('Content-Description: File Transfer');
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: attachment; filename="' . $downloadName . '"; filename*=UTF-8\'\'' . rawurlencode($downloadName));
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    if ($archiveSize !== false) {
        header('Content-Length: ' . (string) $archiveSize);
    }

    while (!feof($handle)) {
        $chunk = fread($handle, 1024 * 1024);
        if ($chunk === false) {
            break;
        }

        echo $chunk;
        flush();

        if (connection_status() !== CONNECTION_NORMAL) {
            break;
        }
    }

    fclose($handle);
    return true;
}
