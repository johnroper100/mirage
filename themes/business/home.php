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
        <!-- Blog preview section-->
        <section class="py-5">
            <div class="container px-5 my-5">
                <div class="row gx-5 justify-content-center">
                    <div class="col-lg-8 col-xl-6">
                        <div class="text-center">
                            <h2 class="fw-bolder">From our blog</h2>
                            <p class="lead fw-normal text-muted mb-5">Lorem ipsum, dolor sit amet consectetur adipisicing elit. Eaque fugit ratione dicta mollitia. Officiis ad.</p>
                        </div>
                    </div>
                </div>
                <div class="row gx-5">
                    <?php
                    $blogPosts = getPages('blog_posts', 3);
                    foreach ($blogPosts as $blogItem) {
                    ?>
                    <div class="col-lg-4 mb-5">
                        <div class="card h-100 shadow border-0">
                            <img class="card-img-top" src="<?php echo BASEPATH; ?>/uploads/<?php echo $blogItem["content"]["featuredImage"]; ?>" alt="..." />
                            <div class="card-body p-4">
                                <div class="badge bg-primary bg-gradient rounded-pill mb-2">News</div>
                                <a class="text-decoration-none link-dark stretched-link" href="#!"><h5 class="card-title mb-3"><?php echo $blogItem["title"]; ?></h5></a>
                                <div class="card-text mb-0"><?php echo $blogItem["content"]["postContent"]; ?></div>
                            </div>
                            <div class="card-footer p-4 pt-0 bg-transparent border-top-0">
                                <div class="d-flex align-items-end justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <img class="rounded-circle me-3" src="https://dummyimage.com/40x40/ced4da/6c757d" alt="..." />
                                        <div class="small">
                                            <div class="fw-bold">Kelly Rowan</div>
                                            <div class="text-muted">March 12, 2021 &middot; 6 min read</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <!-- Call to action-->
                <aside class="bg-primary bg-gradient rounded-3 p-4 p-sm-5 mt-5">
                    <div class="d-flex align-items-center justify-content-between flex-column flex-xl-row text-center text-xl-start">
                        <div class="mb-4 mb-xl-0">
                            <div class="fs-3 fw-bold text-white">New products, delivered to you.</div>
                            <div class="text-white-50">Sign up for our newsletter for the latest updates.</div>
                        </div>
                        <div class="ms-xl-4">
                            <div class="input-group mb-2">
                                <input class="form-control" type="text" placeholder="Email address..." aria-label="Email address..." aria-describedby="button-newsletter" />
                                <button class="btn btn-outline-light" id="button-newsletter" type="button">Sign up</button>
                            </div>
                            <div class="small text-white-50">We care about privacy, and will never share your data.</div>
                        </div>
                    </div>
                </aside>
            </div>
        </section>
    </main>
<?php include 'footer.php'; ?>