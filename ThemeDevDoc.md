# Mirage Theme Developer Documentation

This document describes the current theme API in Mirage as implemented in `index.php`, `dashboard/admin.php`, and the files under `theme/`.

It covers:

- the variables, constants, and helper functions that templates can rely on
- the structure of `theme/config.json`
- the structure of `theme/template_defs/*.json`
- the runtime details that matter when building or maintaining a theme

Anything not documented here should be treated as internal implementation detail, even if it currently exists in the global PHP namespace.

## Theme Layout

A Mirage theme currently uses this structure:

```text
theme/
  config.json
  {template-id}.php
  template_defs/
    {template-def}.json
  header.php
  footer.php
  nav.php
  css/
  js/
  img/
```

The important pieces are:

- `theme/config.json`
  Declares templates, collections, menus, and forms.
- `theme/{template-id}.php`
  The PHP file used to render a page for a template entry in `config.json`.
- `theme/template_defs/{file}.json`
  Defines the editable fields shown in the admin UI for a template.

For a template to work, both of these must exist:

- a `templates[].id` entry in `theme/config.json`
- a matching `theme/{id}.php` file

If a template also needs editable content fields in the admin UI, `templates[].file` must point to a matching JSON file inside `theme/template_defs/`.

## Template Runtime

Mirage renders a page template through `includeThemeFile()`. That function extracts a small set of variables into template scope, renders the file, then post-processes the HTML for form protection, Mirage head integrations, and menu assets.

Included partials such as `header.php`, `footer.php`, and `nav.php` share the same PHP scope as the main template file.

## Variables Available In Templates

These variables are the current documented template variables.

### `$page`

The current page record as stored in the `pages` database.

Common keys:

- `_id`
  Numeric page ID.
- `title`
  Page title from the page options UI.
- `description`
  Page description from the page options UI.
- `templateName`
  The template ID from `theme/config.json`.
- `content`
  Associative array of values generated from the template definition JSON.
- `featuredImage`
  Optional page-level featured image selected in the page options UI.
- `path`
  The page slug.
- `isPathless`
  Boolean. If true, the page is not publicly routable.
- `collection`
  Collection ID from `theme/config.json`.
- `collectionSubpath`
  Optional collection subpath from the collection config.
- `isPublished`
  Boolean publish state.
- `created`
  Unix timestamp.
- `edited`
  Unix timestamp.
- `createdUser`
  User ID of the creator, if available.
- `editedUser`
  User ID of the last editor, if available.
- `order`
  Integer order value used when the collection sort mode is `custom`.

Important: template field values live in `$page['content']`, not at the top level of `$page`.

Example:

```php
<h1><?php echo htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
<?php echo $page['content']['pageContent'] ?? ''; ?>
```

### `$siteTitle`

The current site title from `config.php` / the site settings UI.

### `$footerText`

The current footer text from site settings.

### `$copyrightText`

The current copyright text from site settings.

These two values can contain `{{year}}` and `{{siteTitle}}` tokens. Mirage does not automatically expand those tokens in every template. If you want token replacement outside the stock footer, call `renderSiteTextTemplate()`.

### `$googleAnalyticsTrackingCode`

The normalized Google Analytics tracking code from site settings when one is configured.

### `$mirageMetaTag`

The Mirage head placeholder meta tag. Place this inside `<head>` if you want Mirage to inject native integrations such as Google Analytics for the site owner.

## Constants Available In Templates

These path-related constants are safe to use in themes:

- `BASEPATH`
  The install path relative to the web root. Empty string when Mirage is installed at the root.
- `ORIGBASEPATH`
  The raw original base path from `dirname($_SERVER['PHP_SELF'])`.
- `THEMEPATH`
  Equal to `BASEPATH . '/theme'`.

Common usage:

```php
<link rel="stylesheet" href="<?php echo THEMEPATH; ?>/css/custom.css">
<img src="<?php echo BASEPATH; ?>/uploads/<?php echo rawurlencode($media['file']); ?>">
```

## Helper Functions Available In Templates

These are the current documented theme helpers.

### `getPages($collection, $numEntries, $sort = null)`

Returns pages from a collection.

Parameters:

- `$collection`
  Collection ID from `theme/config.json`.
- `$numEntries`
  Maximum number of pages to return. Use `0` for all pages.
- `$sort`
  Optional sort mode.

Supported `$sort` values:

- `null`
  Uses the collection's configured sort mode.
- `'newest'`
  Newest first by creation date.
- `'oldest'`
  Oldest first by creation date.
- `'custom'`
  Uses the stored `order` field.
- an associative array such as `['created' => 'desc']`
  Passed directly to the database query as a raw sort instruction.

Behavior:

- front-end visitors only receive published pages
- logged-in users also receive unpublished pages
- when `sort` is omitted, the collection's `sort` value is used, defaulting to `newest`

Returns:

- an array of page records shaped like `$page`

Example:

```php
<?php foreach (getPages('newsItems', 3, 'newest') as $newsItem) { ?>
    <h2><?php echo htmlspecialchars($newsItem['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
<?php } ?>
```

### `getMenuItems($menuID)`

Returns a flat, normalized array of menu items for a configured menu ID.

Each item contains:

- `menuID`
- `itemID`
- `parentItemID`
- `name`
- `type`
  `0` for internal page links, `1` for external links
- `page`
  Page ID for internal links
- `link`
  Stored link value
- `order`
- `_id`
  Present on stored items

This helper is useful if you want to render your own navigation markup from the flat list.

### `getMenuTree($menuID)`

Returns the same menu items as a nested tree.

Each returned item adds:

- `children`
  Nested child menu items

Use this if you want to render custom nested navigation without rebuilding the parent/child structure yourself.

### `getMenuItemUrl($menuItem)`

Returns the final URL for a menu item.

Behavior:

- internal menu items return a URL under `BASEPATH`
- external menu items return the stored external URL
- empty internal links resolve to `BASEPATH . '/'`

This is mainly useful when custom-rendering a menu returned by `getMenuItems()` or `getMenuTree()`.

### `renderMenu($menuID, $options = [])`

Returns fully rendered menu HTML for a configured menu.

Supported options:

- `currentPageID`
  Marks the current item and active parent chain.
- `listClass`
- `listAttributes`
- `submenuClass`
- `submenuAttributes`
- `itemClass`
- `activeItemClass`
- `hasChildrenItemClass`
- `linkClass`
- `linkAttributes`
- `buttonClass`
- `buttonAttributes`
- `submenuToggleLabel`

Notes:

- the output is a nested `<ul>` structure
- external links automatically get `target="_blank"` and `rel="noopener noreferrer"` unless you override them
- if `renderMenu()` output is present on the page, Mirage injects `/assets/css/mirage-menu.css` and `/assets/js/mirage-menu.js` automatically

Example:

```php
echo renderMenu('header', [
    'currentPageID' => $page['_id'] ?? null,
    'listClass' => 'site-nav',
    'activeItemClass' => 'is-active'
]);
```

### `getMedia($mediaID)`

Returns a single media item by ID.

Common keys:

- `_id`
- `file`
  Original stored filename under `uploads/`
- `fileSmall`
  Small preview filename, usually for images
- `caption`
- `extension`
- `type`
  `image` or `file`
- `created`
- `edited`
- `createdUser`
- `editedUser`

Example:

```php
<?php $image = getMedia($page['content']['featuredImage'] ?? null); ?>
<?php if ($image) { ?>
    <img src="<?php echo BASEPATH; ?>/uploads/<?php echo rawurlencode($image['file']); ?>">
<?php } ?>
```

### `getUsers($numEntries)`

Returns an array of user records.

Selected fields:

- `name`
- `email`
- `accountType`
- `notifySubmissions`
- `bio`

Use `0` to return all users.

### `getUser($userID)`

Returns one user record using the same selected fields as `getUsers()`.

Pass a valid user ID. The current implementation assumes the user exists.

### `getFirstParagraph($string)`

Returns the substring from the first `<p>` to the first `</p>`.

This is a simple helper used for excerpts. It assumes the string contains a paragraph tag.

### `renderSiteTextTemplate($text, $context = [])`

Expands the site text tokens currently supported by Mirage:

- `{{year}}`
- `{{siteTitle}}`
- `{{site_title}}`

Supported context keys:

- `siteTitle`

Example:

```php
echo htmlspecialchars(
    renderSiteTextTemplate($copyrightText, ['siteTitle' => $siteTitle]),
    ENT_QUOTES,
    'UTF-8'
);
```

### `renderMirageMetaTag()`

Returns the Mirage head placeholder meta tag for native integrations.

Typical usage:

```php
<?php echo renderMirageMetaTag(); ?>
```

You can also echo the prebuilt `$mirageMetaTag` template variable instead.

## Template Content Values

Template field values are saved into `$page['content']` using the field `id` from the template definition JSON.

Examples:

- a `text` field with `id: "headerTitle"` becomes `$page['content']['headerTitle']`
- a `media` field stores the selected media item ID
- a `page` field stores the selected page ID
- a `list` field stores an array of nested item arrays keyed by nested field IDs

Example list value:

```php
$page['content']['associateImages'] = [
    [
        'image' => 12,
        'link' => 'https://example.com'
    ],
    [
        'image' => 14,
        'link' => 'https://example.org'
    ]
];
```

## `theme/config.json`

Mirage loads `theme/config.json` through `getThemeConfiguration()`.

Top-level structure:

```json
{
  "name": "Theme Name",
  "version": "1.0",
  "author": "Author Name",
  "templates": [],
  "collections": [],
  "menus": [],
  "forms": []
}
```

### Informational Keys

These keys are informational and are not currently used by the runtime for routing or rendering:

- `name`
- `version`
- `author`

### `templates`

Array of template definitions.

Each template object supports:

- `name`
  Display name shown in the admin UI.
- `id`
  Template ID. This must match:
  - the PHP template filename `theme/{id}.php`
  - the values used in `collections[].allowed_templates`
- `file`
  JSON filename inside `theme/template_defs/`

Rules enforced by the runtime:

- `id` must match `[A-Za-z0-9_-]+`
- `file` must match `[A-Za-z0-9_-]+.json`
- `theme/template_defs/{file}` must exist for the editor API to load it
- `theme/{id}.php` must exist for page rendering to work

Example:

```json
{
  "name": "News Item",
  "id": "newsItem",
  "file": "newsItem.json"
}
```

### `collections`

Array of page collections shown in the admin sidebar.

Each collection object supports:

- `name`
  Display name.
- `id`
  Collection ID used by page records and `getPages()`.
- `icon`
  Font Awesome class name used in the admin sidebar.
- `allowed_templates`
  Array of template IDs that can be used for this collection.
- `subpath`
  Optional public URL prefix for pages in this collection.
- `sort`
  Optional collection sort mode.

Current supported `sort` values:

- `newest`
- `oldest`
- `custom`

Default:

- if `sort` is missing or invalid, Mirage uses `newest`

Important notes:

- `subpath` should be a single URL segment
- the current front-end routing only supports one collection subpath segment
- collections with `sort: "custom"` use and maintain the page `order` field

Example:

```json
{
  "name": "Portfolio Items",
  "id": "portfolioItems",
  "icon": "fa-images",
  "subpath": "portfolio",
  "sort": "custom",
  "allowed_templates": ["portfolioItem"]
}
```

### `menus`

Array of menu slots.

Each menu object supports:

- `name`
  Display name in the admin UI.
- `id`
  Menu ID used by `getMenuItems()`, `getMenuTree()`, and `renderMenu()`

Important:

- `theme/config.json` defines the menu containers only
- the actual menu items are stored in the database and managed through the admin UI

Example:

```json
{
  "name": "Header",
  "id": "header"
}
```

### `forms`

Array of form definitions for the built-in `/form/{id}` handler.

Each form object supports:

- `name`
  Human-readable form name used in stored submissions and notification text.
- `id`
  Form ID used in the front-end form action URL.
- `recipient`
  Legacy field. Present in sample themes, but the current runtime does not use it for email delivery.
- `fields`
  Array of field definitions stored with each submission.

Each `fields[]` entry supports:

- `name`
  Human-readable label stored in the submission.
- `id`
  Form input name Mirage reads from `$_POST`.
- `type`
  Stored alongside the submission for display in the dashboard

Important runtime details:

- Mirage does not generate the front-end HTML form from `forms[]`
- you must build the `<form>` markup yourself inside the theme
- the form action must point to `<?php echo BASEPATH; ?>/form/{id}`
- the input names in your HTML must match `forms[].fields[].id`
- Mirage automatically injects anti-spam hidden fields into forms whose `action` matches `/form/{id}`
- the current built-in handler also requires a user-facing field named `math` whose submitted value must be `5`
- email notifications are sent to dashboard users with `notifySubmissions == 1`, not to `forms[].recipient`

Minimal example:

```json
{
  "name": "Contact",
  "id": "contact",
  "recipient": "legacy@example.com",
  "fields": [
    { "name": "First Name", "id": "firstName", "type": "text" },
    { "name": "Email", "id": "email", "type": "email" },
    { "name": "Message", "id": "message", "type": "text" }
  ]
}
```

Corresponding template snippet:

```php
<form action="<?php echo BASEPATH; ?>/form/contact" method="post">
    <input type="text" name="firstName">
    <input type="email" name="email">
    <textarea name="message"></textarea>
    <input type="text" name="math">
    <button type="submit">Send</button>
</form>
```

## `theme/template_defs/*.json`

Each template entry in `theme/config.json` points to a template definition JSON file. This file defines the fields shown in the page editor.

Top-level structure:

```json
{
  "name": "Template Name",
  "isPathless": false,
  "sections": []
}
```

Top-level keys:

- `name`
  Template name shown in the editor payload.
- `isPathless`
  Boolean. New pages based on this template start with the pathless flag from this value.
- `sections`
  Array of editor sections.

### Section Structure

Each section supports:

- `name`
  Display label in the editor.
- `id`
  Internal identifier.
- `fields`
  Array of field definitions.

### Supported Field Types

Mirage's current editor supports these field types:

- `text`
- `link`
- `select`
- `page`
- `textarea`
- `richtext`
- `media`
- `list`

Common field keys:

- `name`
  Field label shown in the editor.
- `id`
  Field key used when the value is stored into `$page['content']`.
- `type`
  Field type.
- `value`
  Optional default value.
- `placeholder`
  Optional placeholder text for supported input types.

Type-specific keys:

### `text`, `link`, `textarea`, `richtext`

Optional:

- `value`
- `placeholder`

Example:

```json
{
  "name": "Subtitle",
  "type": "text",
  "id": "headerSubtitle",
  "value": "Default subtitle"
}
```

### `select`

Additional key:

- `options`
  Array of objects with:
  - `name`
  - `value`

Example:

```json
{
  "name": "Theme Variant",
  "type": "select",
  "id": "variant",
  "options": [
    { "name": "Light", "value": "light" },
    { "name": "Dark", "value": "dark" }
  ]
}
```

### `page`

Additional key:

- `collection`
  The collection ID used to populate the page picker

Stored value:

- selected page ID

### `media`

Additional key:

- `accepts`
  `image`, `file`, or `both`

Legacy compatibility:

- `subtype`
  Older themes can still use `image` or `file`. Mirage will treat this as the matching `accepts` value.

Stored value:

- selected media item ID

### `list`

Additional key:

- `fields`
  Array of nested field definitions used for each list item

Stored value:

- array of nested item arrays

Example:

```json
{
  "name": "Gallery",
  "type": "list",
  "id": "galleryItems",
  "fields": [
    {
      "name": "Image",
      "type": "media",
      "accepts": "image",
      "id": "image"
    },
    {
      "name": "Caption",
      "type": "text",
      "id": "caption"
    }
  ]
}
```

## Important Behavior Differences From Older Theme Docs

The older documentation no longer matched the current runtime in a few important ways:

- `getPages()` now supports collection sort modes from config, including `custom`
- Mirage now has documented menu helpers beyond `getMenuItems()`, especially `renderMenu()`
- template scope includes `$footerText` and `$copyrightText`
- template scope also includes `$googleAnalyticsTrackingCode` and `$mirageMetaTag`
- `theme/config.json` collections can declare `sort` and `subpath`
- forms are protected by automatic hidden-field injection, but still require a manual `math` field in the current implementation
- `forms[].recipient` is currently legacy data and is not used to choose notification recipients
- page option fields such as top-level `featuredImage` are separate from template content fields stored in `$page['content']`
- native head integrations such as Google Analytics can be inserted through the Mirage meta tag instead of hardcoded snippets

## Recommended Theme Conventions

These are not enforced by Mirage, but they will keep themes easier to maintain:

- escape plain text with `htmlspecialchars()`
- treat `richtext` fields as already-authored HTML and render them intentionally
- use `BASEPATH` and `THEMEPATH` for all internal URLs
- avoid giving a template content field the same semantic meaning as a top-level page option unless you truly want both
- keep collection `subpath` values to one segment
- prefer `renderMenu()` unless you specifically need custom markup

## Quick Reference

### Most-used template helpers

- `getPages()`
- `renderMenu()`
- `getMedia()`
- `renderSiteTextTemplate()`
- `renderMirageMetaTag()`

### Most-used template variables

- `$page`
- `$siteTitle`
- `$footerText`
- `$copyrightText`
- `$mirageMetaTag`

### Files that must stay in sync

- `theme/config.json`
- `theme/{template-id}.php`
- `theme/template_defs/{template-def}.json`
