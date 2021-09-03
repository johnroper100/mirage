<?php include 'header.php'; ?>
<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h3 class="text-center">MIRAGE SETUP</h3>
        </div>
    </div>
    <div class="row justify-content-center mt-4">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card">
                <div class="card-body">
                    <form action="<?php echo BASEPATH ?>/setup" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Site Title:</label>
                            <input required type="text" class="form-control" placeholder="My New Website" name="siteTitle" id="siteTitle">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Your Name:</label>
                            <input required type="text" class="form-control" placeholder="John Doe" name="name" id="name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address:</label>
                            <input required type="email" class="form-control" placeholder="johndoe@mail.com" name="email" id="email">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password:</label>
                            <input required type="password" class="form-control" placeholder="myspecialpassword" name="password" id="password">
                        </div>
                        <button class="btn btn-success w-100" type="submit">Start Your Website!</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>