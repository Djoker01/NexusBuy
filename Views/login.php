<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | NexusBuy</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../Util/Css/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../Util/Css/adminlte.min.css">
    <link rel="stylesheet" href="../Util/Css/toastr.min.css">
</head>
<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo">
            <img src="../Util/Img/logo.png" class="profile-user-img img-fluid img-circle">
            <a href="../index.php"><b>NexusBuy</b> - Store</a>
        </div>
        <!-- /.login-logo -->
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Inicie sesión</p>

                <form id="form-login">
                    <div class="input-group mb-3">
                        <input id="user" type="text" class="form-control" placeholder="User" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input id="pass" type="password" class="form-control" placeholder="Password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="social-auth-links text-center mb-3">
                        <button type="submit" href="#" class="btn btn-block btn-primary">
                            Iniciar sesión
                        </button>
                        <div class="social-auth-links text-center mb-3">
                            <p>- OR -</p>
                            <button type="button" class="btn btn-block btn-primary" id="btn-login-facebook">
                                <i class="fab fa-facebook mr-2"></i> Inicia sesión usando Facebook
                            </button>
                            <button type="button" class="btn btn-block btn-danger" id="btn-login-google">
                                <i class="fab fa-google mr-2"></i> Inicia sesión usando Google
                            </button>
                        </div>
                    </div>
                </form>


                <!-- /.social-auth-links -->

                <p class="mb-1">
                    <a href="">He olvidado mi contraseña</a>
                </p>
                <p class="mb-0">
                    <a href="register.php" class="text-center">Registrarse</a>
                </p>
            </div>
            <!-- /.login-card-body -->
        </div>
    </div>
    <!-- /.login-box -->

    <!-- jQuery -->
    <script src="../Util/Js/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../Util/Js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../Util/Js/adminlte.min.js"></script>
    <script src="login.js"></script>
    <script src="../Util/Js/toastr.min.js"></script>
</body>

</html>