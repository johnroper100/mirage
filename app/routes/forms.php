<?php

    /* Forms */

    \Steampixel\Route::add('/api/form', function () {
        if (canManageForms()) {
            global $formStore;
            $allSubmissions = $formStore->findAll($orderBy = ["created" => "desc"]);
            $myJSON = json_encode($allSubmissions);
            echo $myJSON;
        } else {
            getErrorPage(401);
        }
    });

    \Steampixel\Route::add('/form/(.*)', function ($formID) {
        global $formStore, $userStore;
        $themeConfig = json_decode(file_get_contents(MIRAGE_ROOT . '/theme/config.json'), true);
        $forms = isset($themeConfig["forms"]) && is_array($themeConfig["forms"]) ? $themeConfig["forms"] : [];
        foreach ($forms as $form) {
            if ($form["id"] == $formID) {
                $submittedFields = getSubmittedFormFieldValues($form);
                $rejectionReason = null;

                if (hasExceededFormAttemptLimit($formID, getClientIpAddress())) {
                    $rejectionReason = 'attempt_rate_limit';
                } elseif (!isSameOriginFormRequest()) {
                    $rejectionReason = 'cross_origin';
                } elseif (isSpamSubmission($formID)) {
                    $rejectionReason = 'spam_protection';
                } else {
                    $rejectionReason = validateSubmittedFormFields($form, $submittedFields);
                }

                $fingerprint = buildFormSubmissionFingerprint($formID, $submittedFields);
                if ($rejectionReason === null) {
                    $recentMatchingSubmission = findRecentFormSubmissionByFingerprint($formID, $fingerprint);
                    if (
                        $recentMatchingSubmission != null
                        && isset($recentMatchingSubmission['created'])
                        && (time() - (int) $recentMatchingSubmission['created']) < 86400
                    ) {
                        $rejectionReason = 'duplicate_submission';
                    }
                }

                if ($rejectionReason !== null) {
                    recordFormAttempt($formID, 'rejected', $rejectionReason, $submittedFields);
                    unset($_SESSION['formSpamProtection'][$formID]);
                    header('Location: ' . appendQueryParam(getFormReferer(), 'error', '1'));
                    return;
                } else {
                    $submission = [];
                    $submission["form"] = $formID;
                    $submission["formName"] = $form["name"];
                    $submission["fields"] = [];
                    $submission["fingerprint"] = $fingerprint;
                    $submission["created"] = time();
                    $submission["ipAddress"] = getClientIpAddress();
                    $submission["userAgent"] = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : '';
                    foreach ($form["fields"] as $field) {
                        $fieldID = (string) ($field["id"] ?? '');
                        $fieldType = strtolower((string) ($field["type"] ?? 'text'));
                        $fieldValue = isset($submittedFields[$fieldID]) ? $submittedFields[$fieldID] : '';
                        if ($fieldType === 'email') {
                            $fieldValue = normalizeEmailAddress($fieldValue);
                        }

                        $submission["fields"][] = [
                            "id" => $fieldID,
                            "name" => $field["name"],
                            "type" => $field["type"],
                            "value" => test_input($fieldValue)
                        ];
                    }
                    $submission = $formStore->insert($submission);
                    recordFormAttempt($formID, 'accepted', 'accepted', $submittedFields);


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

    \Steampixel\Route::add('/api/form/([0-9]*)', function ($who) {
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

