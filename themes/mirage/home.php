<?php include 'header.php'; ?>

<body class="d-flex flex-column h-100">
    <main class="flex-shrink-0">
        <?php include 'nav.php'; ?>
        <!-- Header-->
        <header class="bg-dark py-5">
            <div class="container px-5">
                <div class="row gx-5 align-items-center justify-content-center">
                    <div class="col-lg-8 col-xl-7 col-xxl-6">
                        <div class="my-5 text-center text-xl-start">
                            <h1 class="display-5 fw-bolder text-white mb-2"><?php echo $page["content"]["headerTitle"]; ?></h1>
                            <p class="lead fw-normal text-white-50 mb-4"><?php echo $page["content"]["headerSubtitle"]; ?></p>
                            <div class="d-grid gap-3 d-sm-flex justify-content-sm-center justify-content-xl-start">
                                <a class="btn btn-primary btn-lg px-4 me-sm-3" href="<?php echo $page["content"]["buttonOneLink"]; ?>"><?php echo $page["content"]["buttonOneText"]; ?></a>
                                <a class="btn btn-outline-light btn-lg px-4" href="<?php echo $page["content"]["buttonTwoLink"]; ?>"><?php echo $page["content"]["buttonTwoText"]; ?></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-5 col-xxl-6 d-none d-xl-block text-center"><img class="img-fluid rounded-3 my-5" src="<?php echo BASEPATH; ?>/uploads/<?php echo $page["content"]["headerImage"]; ?>" alt="..." /></div>
                </div>
            </div>
        </header>
    </main>
<?php include 'footer.php'; ?>