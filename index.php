<?php

session_start();

define('MIRAGE_VERSION', "1.0.1");

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

function getMenuItems($menuID)
{
    global $menuStore;
    $menuItems = $menuStore->findBy(["menuID", "=", $menuID], ["order" => "asc"]);
    return $menuItems;
};

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
                    $menuItem["link"] = $page["path"];
                    if ($page["collectionSubpath"] != "") {
                        $menuItem["link"] = $page["collectionSubpath"] . "/" . $menuItem["link"];
                    }
                    $menuItem = $menuStore->updateById($menuItem["_id"], ["link" => $menuItem["link"]]);
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
                    $menuStore->deleteById($menuItem["_id"]); // TODO: this may cause issues once menus can be nested
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
            global $menuStore;
            $allMenuItems = $menuStore->findAll(["order" => "asc"]);
            $myJSON = json_encode($allMenuItems);
            echo $myJSON;
        } else {
            getErrorPage(401);
        }
    });

    Route::add('/api/menus', function () {
        if (isset($_SESSION['loggedin'])) {
            global $menuStore;
            global $pageStore;

            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $menuItems = $menuStore->createQueryBuilder()->getQuery()->delete();
            foreach ($data as &$menuItem) {
                if ($menuItem["type"] == 0) {
                    $menuItem["link"] = $pageStore->findById($menuItem["page"])["path"];
                    if ($pageStore->findById($menuItem["page"])["collectionSubpath"] != "") {
                        $menuItem["link"] = $pageStore->findById($menuItem["page"])["collectionSubpath"] . "/" . $menuItem["link"];
                    }
                }
            }
            $menuItems = $menuStore->insertMany($data);
            $allMenuItems = $menuStore->findAll(["order" => "asc"]);
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

            if (!file_exists('./uploads')) {
                mkdir('./uploads');
            }

            $count = count($_FILES['uploadMediaFiles']['name']);
            for ($i = 0; $i < $count; $i++) {
                if (!move_uploaded_file($_FILES['uploadMediaFiles']['tmp_name'][$i], "./uploads/" . $_FILES['uploadMediaFiles']['name'][$i])) {
                    getErrorPage(500);
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
                }
            }
        } else {
            getErrorPage(401);
        }
    }, 'POST');

    Route::add('/api/media/richtext', function () {
        if (isset($_SESSION['loggedin'])) {
            global $mediaStore;

            if (!file_exists('./uploads')) {
                mkdir('./uploads');
            }

            if (!move_uploaded_file($_FILES['fileToUpload']['tmp_name'], "./uploads/" . $_FILES['fileToUpload']['name'])) {
                $response = [];
                $response['success'] = false;
                echo json_encode($response);
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
                $response = [];
                $response['success'] = true;
                $response['file'] = BASEPATH . '/uploads/' . $page['file'];
                echo json_encode($response);
            }
        } else {
            $response = [];
            $response['success'] = false;
            echo json_encode($response);
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
                if (test_input($_POST["math"]) != "5") {
                    $error = true;

                    header("Location: {$_SERVER["HTTP_REFERER"]}?error=1");
                } else {
                    $submission = [];
                    $submission["form"] = $formID;
                    $submission["formName"] = $form["name"];
                    $submission["fields"] = [];
                    $submission["created"] = time();
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

                    header("Location: {$_SERVER["HTTP_REFERER"]}?success=1");
                }
            }
        };
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
                include $filename;
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
                include $filename;
            } else {
                getErrorPage(404);
            }
        }
    });
};


Route::run(BASEPATH);

?>