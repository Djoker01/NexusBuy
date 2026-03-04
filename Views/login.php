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
        /* ESTILOS ESPECÍFICOS DEL LOGIN - No están en nexusbuy.css */
        
        /* Ajustes específicos para el contenedor de login */
        .login-box {
            max-width: 400px;
            width: 100%;
            animation: fadeIn 0.5s ease;
        }
        
        /* Estilos específicos para los inputs del login */
        .input-group .input-group-text {
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            transition: var(--transition);
            min-width: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .input-group .input-group-text.password-toggle {
            cursor: pointer;
            background-color: white;
        }
        
        .input-group .input-group-text.password-toggle:hover {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        /* Estilo específico para el botón de submit */
        #form-login button[type="submit"] {
            width: 100%;
            padding: 12px;
            font-size: 1.1rem;
            margin-top: 1rem;
            position: relative;
        }
        
        /* Estilo para el estado de carga del botón */
        #form-login button[type="submit"].loading {
            color: transparent;
        }
        
        #form-login button[type="submit"].loading::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            border: 2px solid white;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        /* Estilos específicos para los enlaces */
        .login-card-body a {
            font-weight: 500;
            transition: var(--transition);
        }
        
        .login-card-body a:hover {
            text-decoration: underline;
            color: var(--secondary);
        }
        
        /* Ajustes específicos para el logo */
        .login-logo img {
            border: 4px solid white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .login-logo a {
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        /* Estilo específico para el mensaje */
        .login-box-msg {
            font-size: 1rem;
            color: #495057;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }
        
        /* Responsive específico para login */
        @media (max-width: 576px) {
            .login-box {
                margin: 0 20px;
            }
            
            .login-card-body {
                padding: 1.5rem !important;
            }
            
            .login-logo img {
                width: 80px;
                height: 80px;
            }
            
            .login-logo a {
                font-size: 1.8rem;
            }
        }
        
        /* Animación específica para el formulario */
        @keyframes slideInLogin {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .card {
            animation: slideInLogin 0.6s ease;
        }
        
        /* Estilo para validación específica */
        .input-group:focus-within .input-group-text {
            border-color: var(--primary);
            background-color: white;
        }
        
        /* Estilo para el icono del ojo */
        .password-toggle .fa-eye-slash {
            display: none;
        }
        
        .password-toggle.active .fa-eye {
            display: none;
        }
        
        .password-toggle.active .fa-eye-slash {
            display: inline-block;
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
            // document.getElementById('btn-recuperar-contra').addEventListener('click', function() {
            //     toastr.info('Función de recuperación de contraseña próximamente', 'En desarrollo');
            // });
        });
    </script>
</body>

</html>