<?php

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

function isMediaItemAllowedForAccepts($mediaItem, $accepts)
{
    if (!is_array($mediaItem)) {
        return false;
    }

    $normalizedAccepts = normalizeMediaFieldAccepts($accepts);
    $mediaType = strtolower(trim((string) ($mediaItem['type'] ?? '')));

    if (!in_array($mediaType, ['image', 'file'], true)) {
        return false;
    }

    return $normalizedAccepts === 'both' || $mediaType === $normalizedAccepts;
}

function isValidFieldValue($field)
{
    if (!is_array($field)) {
        return false;
    }

    $fieldType = (string) ($field['type'] ?? '');
    if ($fieldType === 'media') {
        $mediaId = normalizeOptionalMediaReference($field['value'] ?? null);
        if ($mediaId === null) {
            return true;
        }

        return isMediaItemAllowedForAccepts(
            getMedia($mediaId),
            normalizeMediaFieldAccepts($field['accepts'] ?? '', $field['subtype'] ?? '')
        );
    }

    if ($fieldType === 'list') {
        $fieldValue = $field['value'] ?? [];
        if (!is_array($fieldValue)) {
            return false;
        }

        foreach ($fieldValue as $subFields) {
            if (!is_array($subFields)) {
                return false;
            }

            foreach ($subFields as $subField) {
                if (!isValidFieldValue($subField)) {
                    return false;
                }
            }
        }
    }

    return true;
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

    $templateDefinition = loadTemplateDefinition($templateName);
    if ($templateDefinition === null) {
        return null;
    }

    $path = sanitizeStoredPathSegment($data["path"] ?? '');
    $collectionSubpath = sanitizeStoredPathSegment($data["collectionSubpath"] ?? '');
    if ($path === null || $collectionSubpath === null) {
        return null;
    }

    $shouldPasswordProtectPage = !empty($data["isPasswordProtected"]);
    $requestedPagePassword = isset($data["pagePassword"]) ? (string) $data["pagePassword"] : '';

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
                if (!isValidFieldValue($field)) {
                    return null;
                }
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
    if ($shouldPasswordProtectPage) {
        if ($requestedPagePassword !== '') {
            $page["passwordHash"] = password_hash($requestedPagePassword, PASSWORD_DEFAULT);
        } else if (!$isNewPage && isPagePasswordProtected($existingPage)) {
            $page["passwordHash"] = getPagePasswordHash($existingPage);
        } else {
            return null;
        }
    } else {
        $page["passwordHash"] = '';
    }
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
    $themeErrorPath = MIRAGE_ROOT . '/theme/error.php';
    if (file_exists($themeErrorPath)) {
        include $themeErrorPath;
    } else {
        include MIRAGE_ROOT . '/dashboard/error.php';
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
        $pages = null;
        if ($numEntries > 0) {
            $pages = $pageStore->findBy($conditions, $sort, $numEntries);
        } else {
            $pages = $pageStore->findBy($conditions, $sort);
        }

        return preparePagesForResponse($pages);
    }

    $pages = $pageStore->findBy($conditions);
    $pages = sortPagesByCollectionSort($pages, getCollectionSortMode($collection, $sort));

    if ($numEntries > 0) {
        $pages = array_slice($pages, 0, $numEntries);
    }

    return preparePagesForResponse($pages);
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
    $mediaID = normalizeOptionalMediaReference($mediaID);
    if ($mediaID === null) {
        return null;
    }

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

