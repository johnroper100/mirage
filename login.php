<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    <script defer src="all.min.js"></script>
    <title>Mirage Login</title>
    <style>
        * {
            border-radius: 0 !important;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f9fa;
        }

        .bg-dark {
            background-color: #212529 !important;
        }

        .bg-secondary {
            background-color: #343a40 !important;
        }

        .bg-light {
            background-color: #f1f3f5 !important;
        }

        .btn-dark {
            color: #f8f9fa;
            background-color: #212529;
            border-color: #212529;
        }

        .btn-success {
            color: #f8f9fa;
            background-color: #2f9e44;
            border-color: #2f9e44;
        }

        .btn-primary {
            color: #f8f9fa;
            background-color: #1971c2;
            border-color: #1971c2;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h3 class="text-center">MIRAGE LOGIN</h3>
            </div>
        </div>
        <div class="row justify-content-center mt-4">
            <div class="col-12 col-md-5">
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
                            <button class="btn btn-success" type="submit">Log In</button> or <a href="#">forgot your password?</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>

</html>