<?php include 'header.php'; ?>
<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h3 class="text-center">MIRAGE LOGIN</h3>
        </div>
    </div>
    <div class="row justify-content-center mt-4">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card">
                <div class="card-body">
                    <form action="<?php echo BASEPATH ?>/login" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email Address:</label>
                            <input type="email" class="form-control" placeholder="johndoe@mail.com" name="email" id="email">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password:</label>
                            <input type="password" class="form-control" placeholder="myspecialpassword" name="password" id="password">
                        </div>
                        <button class="btn btn-success" type="submit">Log In</button> <!--or <a href="#" class="text-decoration-none">forgot your password?--></a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-12 text-center">
            <small class="text-secondary">View Mirage on <i class="fa-brands fa-github me-1 ms-1"></i><a href="https://github.com/johnroper100/mirage" target="_blank" class="text-secondary">GitHub</a></small>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>