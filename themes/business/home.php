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
                                <a class="btn btn-primary btn-lg px-4 me-sm-3" href="#features">Get Started</a>
                                <a class="btn btn-outline-light btn-lg px-4" href="<?php echo $page["content"]["buttonTwoLink"]; ?>">Learn More</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-5 col-xxl-6 d-none d-xl-block text-center"><img class="img-fluid rounded-3 my-5" src="<?php echo BASEPATH; ?>/uploads/<?php echo $page["content"]["headerImage"]; ?>" alt="..." /></div>
                </div>
            </div>
        </header>
        <!-- Features section-->
        <section class="py-5" id="features">
            <div class="container px-5 my-5">
                <div class="row gx-5">
                    <div class="col-lg-4 mb-5 mb-lg-0">
                        <h2 class="fw-bolder mb-0"><?php echo $page["content"]["featuresTitle"]; ?></h2>
                    </div>
                    <div class="col-lg-8">
                        <div class="row gx-5 row-cols-1 row-cols-md-2">
                            <?php if (array_key_exists('featuresItems', $page["content"])) {
                                foreach ($page["content"]["featuresItems"] as $key => $featuresItem) { ?>
                                    <div class="col <?php if ($key != array_key_last($page["content"]["featuresItems"])) { ?>mb-5<?php } ?> h-100">
                                        <div class="feature bg-primary bg-gradient text-white rounded-3 mb-3"><i class="bi <?php echo $featuresItem['icon']; ?>"></i></div>
                                        <h2 class="h5"><?php echo $featuresItem['text']; ?></h2>
                                        <p class="mb-0"><?php echo $featuresItem['details']; ?></p>
                                    </div>
                            <?php }
                            }; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Testimonial section-->
        <div class="py-5 bg-light">
            <div class="container px-5 my-5">
                <div class="row gx-5 justify-content-center">
                    <div class="col-lg-10 col-xl-7">
                        <div class="text-center">
                            <div class="fs-4 mb-4 fst-italic">"<?php echo $page["content"]["testimonialQuote"]; ?>"</div>
                            <div class="d-flex align-items-center justify-content-center">
                                <img width="40" height="40" class="rounded-circle me-3" style="object-fit: cover;" src="<?php echo BASEPATH; ?>/uploads/<?php echo $page["content"]["testimonialImage"]; ?>" alt="..." />
                                <div class="fw-bold">
                                    <?php echo $page["content"]["testimonialPerson"]; ?>
                                    <span class="fw-bold text-primary mx-1">/</span>
                                    <?php echo $page["content"]["testimonialPosition"]; ?>, <?php echo $page["content"]["testimonialCompany"]; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php include 'footer.php'; ?>