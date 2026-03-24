<?php

    /* Media */

    \Steampixel\Route::add('/api/media', function () {
        if (isset($_SESSION['loggedin'])) {
            global $mediaStore;
            $allMedia = $mediaStore->findAll(["edited" => "desc"]);
            sendJsonResponse(prepareMediaItemsForResponse($allMedia));
        } else {
            sendJsonResponse([
                'success' => false,
                'message' => 'You do not have permission to view media.',
            ], 401);
        }
    });

    \Steampixel\Route::add('/api/media', function () {
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
                }

                $page = buildMediaItemRecordFromStoredUpload($storedFilename, $originalFilename);
                if ($page === null) {
                    deleteStoredUploadFile($storedFilename);
                    sendJsonResponse([
                        'success' => false,
                        'message' => getInvalidUploadTypeMessage(true),
                    ], 400);
                    return;
                }

                try {
                    $page = $mediaStore->insert($page);
                } catch (\Throwable $exception) {
                    deleteMediaStorageFiles($page);
                    sendJsonResponse([
                        'success' => false,
                        'message' => 'The media library could not save the uploaded file record. Please try again.',
                    ], 500);
                    return;
                }

                $uploadedMedia[] = prepareMediaItemForResponse($page);
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

    \Steampixel\Route::add('/api/media/richtext', function () {
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
            }

            $page = buildMediaItemRecordFromStoredUpload($storedFilename, $originalFilename);
            if ($page === null || ($page['type'] ?? '') !== 'image') {
                deleteStoredUploadFile($storedFilename);
                sendJsonResponse([
                    'success' => false,
                    'message' => getInvalidUploadTypeMessage(true),
                ], 400);
                return;
            }

            try {
                $page = $mediaStore->insert($page);
            } catch (\Throwable $exception) {
                deleteMediaStorageFiles($page);
                sendJsonResponse([
                    'success' => false,
                    'message' => 'The media library could not save the uploaded file record. Please try again.',
                ], 500);
                return;
            }

            $page = prepareMediaItemForResponse($page);
            sendJsonResponse([
                'success' => true,
                'file' => $page['fileUrl'],
            ]);
        } else {
            sendJsonResponse([
                'success' => false,
                'message' => 'You do not have permission to upload files.',
            ], 401);
        }
    }, 'POST');

    \Steampixel\Route::add('/api/media/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            global $mediaStore;

            if (!requireCsrfToken(true)) {
                return;
            }

            $existingMedia = $mediaStore->findById($who);
            if ($existingMedia == null || !canEditMediaItem($existingMedia)) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'You do not have permission to edit this media item.',
                ], 401);
                return;
            }

            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $mediaItem = [
                'caption' => trim((string) ($data["caption"] ?? '')),
                'altText' => trim((string) ($data["altText"] ?? '')),
                'editedUser' => getCurrentUserId(),
                'edited' => time()
            ];

            $mediaStore->updateById($who, $mediaItem);
            sendJsonResponse([
                'success' => true,
                'item' => prepareMediaItemForResponse($mediaStore->findById($who)),
            ]);
        } else {
            sendJsonResponse([
                'success' => false,
                'message' => 'You do not have permission to edit media.',
            ], 401);
        }
    }, 'PUT');

    \Steampixel\Route::add('/api/media/([0-9]*)', function ($who) {
        if (isset($_SESSION['loggedin'])) {
            global $mediaStore;

            if (!requireCsrfToken(true)) {
                return;
            }

            $selectedMedia = $mediaStore->findById($who);
            if ($selectedMedia == null || !canEditMediaItem($selectedMedia)) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'You do not have permission to delete this media item.',
                ], 401);
                return;
            }

            if (!deleteMediaStorageFiles($selectedMedia)) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'The media files could not be deleted from storage.',
                ], 500);
                return;
            }

            $mediaStore->deleteById($who);

            sendJsonResponse([
                'success' => true
            ]);
        } else {
            sendJsonResponse([
                'success' => false,
                'message' => 'You do not have permission to delete media.',
            ], 401);
        }
    }, 'DELETE');

