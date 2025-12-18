<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión | NexusBuy</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../Util/css/Librerias/font awesome/all.min.css">
    <link rel="stylesheet" href="../Util/css/Librerias/fonts Poppins/fonts_Poppins.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../Util/Css/adminlte.min.css">
    <link rel="stylesheet" href="../Util/Css/toastr.min.css">
    <link rel="stylesheet" href="../Util/Css/nexusbuy.css">
    <style>
       

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--gradient-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        
    </style>
</head>
<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo">
            <img src="../Util/Img/png/logo.png" class="profile-user-img img-fluid img-circle" alt="NexusBuy Logo">
            <a href="../index.php">NexusBuy</a>
        </div>
        <!-- /.login-logo -->
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Inicie sesión en su cuenta</p>

                <form id="form-login">
                    <div class="input-group mb-3">
                        <input id="user" type="text" class="form-control" placeholder="Usuario" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input id="pass" type="password" class="form-control" placeholder="Contraseña" required>
                        <div class="input-group-append">
                            <div class="input-group-text password-toggle" id="toggle-password">
                                <span class="fas fa-eye"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-sign-in-alt mr-2"></i>Iniciar sesión
                            </button>
                        </div>
                    </div>
                </form>

                
                <div class="row">
                    <div class="col-12">
                        <p class="mb-1 text-center">
                            <a href="javascript:void(0)" id="btn-recuperar-contra" class="text-primary">
                                <i class="fas fa-key mr-1"></i>¿Olvidó su contraseña?
                            </a>
                        </p>
                        <p class="mb-0 text-center">
                            <a href="register.php" class="text-center">
                                <i class="fas fa-user-plus mr-1"></i>¿No tiene cuenta? Regístrese aquí
                            </a>
                        </p>
                    </div>
                </div>
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
    <script src="../Util/Js/toastr.min.js"></script>
    <script src="login.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const togglePassword = document.getElementById('toggle-password');
            const passwordInput = document.getElementById('pass');
            
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('span').classList.toggle('fa-eye');
                this.querySelector('span').classList.toggle('fa-eye-slash');
            });

            // Form submission with loading state
            const form = document.getElementById('form-login');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitButton = this.querySelector('button[type="submit"]');
                const originalText = submitButton.innerHTML;
                
                // Show loading state
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Iniciando sesión...';
                submitButton.classList.add('loading');
                submitButton.disabled = true;
                
                
            });

            // Password recovery
            document.getElementById('btn-recuperar-contra').addEventListener('click', function() {
                toastr.info('Función de recuperación de contraseña próximamente', 'En desarrollo');
            });
        });
    </script>
</body>

</html>