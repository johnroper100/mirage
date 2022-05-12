<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $siteTitle; ?> - <?php echo $page["title"]; ?></title>
    <meta name="description" content="Free Bootstrap Theme by uicookies.com">
    <meta name="keywords"
        content="free website templates, free bootstrap themes, free template, free bootstrap, free website template">

    <link href="https://fonts.googleapis.com/css?family=Abel" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo THEMEPATH; ?>/css/styles-merged.css">
    <link rel="stylesheet" href="<?php echo THEMEPATH; ?>/css/style.min.css">
    <link rel="stylesheet" href="<?php echo THEMEPATH; ?>/css/custom.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>

    <!--[if lt IE 9]>
      <script src="<?php echo THEMEPATH; ?>/js/vendor/html5shiv.min.js"></script>
      <script src="<?php echo THEMEPATH; ?>/js/vendor/respond.min.js"></script>
    <![endif]-->
</head>

<body>

    <!-- START: header -->

    <div class="probootstrap-loader"></div>

    <?php include("nav.php"); ?>