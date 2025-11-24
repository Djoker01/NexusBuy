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
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #4cc9f0;
            --success: #4bb543;
            --warning: #ffc107;
            --danger: #e63946;
            --light: #f8f9fa;
            --dark: #212529;
            --gradient-primary: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            --gradient-accent: linear-gradient(135deg, #4cc9f0 0%, #4895ef 100%);
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 8px 24px rgba(0, 0, 0, 0.12);
            --border-radius: 12px;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--gradient-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-box {
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo img {
            max-width: 100px;
            height: auto;
            border-radius: 50%;
            box-shadow: var(--shadow);
            border: 4px solid white;
            margin-bottom: 1rem;
        }

        .login-logo a {
            font-size: 2rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: block;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .login-logo a:hover {
            color: var(--accent);
        }

        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-hover);
            overflow: hidden;
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-body {
            padding: 2.5rem;
        }

        .login-card-body {
            background: white;
        }

        .login-box-msg {
            text-align: center;
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 2rem;
            font-weight: 500;
        }

        .input-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: var(--transition);
            height: 50px;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }

        .input-group-text {
            background: white;
            border: 2px solid #e9ecef;
            border-left: none;
            border-radius: 0 8px 8px 0;
            transition: var(--transition);
        }

        .input-group:focus-within .input-group-text {
            border-color: var(--primary);
        }

        .password-toggle {
            cursor: pointer;
            color: #6c757d;
            transition: var(--transition);
        }

        .password-toggle:hover {
            color: var(--primary) !important;
        }

        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            font-size: 1rem;
            font-weight: 600;
            transition: var(--transition);
            height: 50px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }

        .btn-block {
            width: 100%;
        }

        .links-section {
            margin-top: 2rem;
            text-align: center;
        }

        .links-section a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            display: block;
            margin-bottom: 0.5rem;
        }

        .links-section a:hover {
            color: var(--secondary);
            transform: translateX(5px);
        }

        .links-section a i {
            margin-right: 8px;
        }

        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-box {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Responsive */
        @media (max-width: 576px) {
            body {
                padding: 15px;
            }
            
            .card-body {
                padding: 2rem 1.5rem;
            }
            
            .login-logo a {
                font-size: 1.6rem;
            }
            
            .login-logo img {
                max-width: 80px;
            }
        }

        /* Efectos de carga */
        .loading {
            position: relative;
            overflow: hidden;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { left: -100%; }
            100% { left: 100%; }
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