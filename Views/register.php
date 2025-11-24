<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrarse | NexusBuy</title>

    <!-- Google Font: Source Sans Pro -->
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../Util/css/Librerias/font awesome/all.min.css">
    <link rel="stylesheet" href="../Util/css/Librerias/fonts Poppins/fonts_Poppins.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../Util/Css/adminlte.min.css">
    <link rel="stylesheet" href="../Util/Css/toastr.min.css">
    <link rel="stylesheet" href="../Util/Css/sweetalert2.min.css">
</head>
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

        .register-container {
            width: 100%;
            max-width: 900px;
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

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            display: block;
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

        .custom-control-input:checked ~ .custom-control-label::before {
            border-color: var(--primary);
            background-color: var(--primary);
        }

        .custom-control-label {
            cursor: pointer;
        }

        .custom-control-label a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .custom-control-label a:hover {
            text-decoration: underline;
        }

        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            border-radius: 8px;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: var(--transition);
            width: 100%;
            margin-top: 1rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }

        /* Modal Terms */
        .modal-content {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-hover);
        }

        .card-success {
            border: none;
        }

        .card-header {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 1.5rem;
        }

        .card-title {
            margin: 0;
            font-weight: 600;
        }

        .close {
            color: white;
            opacity: 0.8;
            transition: var(--transition);
        }

        .close:hover {
            opacity: 1;
            color: white;
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

        .register-container {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Responsive */
        @media (max-width: 768px) {
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

        @media (max-width: 576px) {
            body {
                padding: 10px;
            }
            
            .card-body {
                padding: 1.5rem 1rem;
            }
        }

        /* Password strength indicator */
        .password-strength {
            height: 4px;
            margin-top: 5px;
            border-radius: 2px;
            transition: var(--transition);
        }

        .strength-weak {
            background: var(--danger);
            width: 25%;
        }

        .strength-medium {
            background: var(--warning);
            width: 50%;
        }

        .strength-strong {
            background: var(--success);
            width: 100%;
        }
    </style>
<body>
    <!-- Modal Términos y Condiciones -->
    <div class="modal fade" id="terminos" tabindex="-1" aria-labelledby="terminosLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="card card-success">
                    <div class="card-header">
                        <h5 class="card-title">Términos y Condiciones</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="card-body">
                        <h6>Bienvenido a NexusBuy</h6>
                        <p>Al registrarte en nuestra plataforma, aceptas los siguientes términos y condiciones:</p>
                        
                        <h6>1. Uso de la Plataforma</h6>
                        <p>NexusBuy es una plataforma de comercio electrónico que conecta compradores y vendedores. Te comprometes a usar el servicio de manera legal y ética.</p>
                        
                        <h6>2. Cuenta de Usuario</h6>
                        <p>Eres responsable de mantener la confidencialidad de tu cuenta y contraseña. Toda actividad realizada bajo tu cuenta es de tu responsabilidad.</p>
                        
                        <h6>3. Privacidad</h6>
                        <p>Respetamos tu privacidad y protegemos tus datos personales según nuestra Política de Privacidad.</p>
                        
                        <h6>4. Compras y Pagos</h6>
                        <p>Las transacciones realizadas a través de NexusBuy están sujetas a disponibilidad y confirmación de pago.</p>
                        
                        <h6>5. Modificaciones</h6>
                        <p>Nos reservamos el derecho de modificar estos términos en cualquier momento. Las modificaciones serán efectivas al ser publicadas.</p>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-secondary btn-block" data-dismiss="modal">Entendido</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="register-container">
        <div class="login-logo">
            <img src="../Util/Img/png/logo.png" class="profile-user-img img-fluid img-circle" alt="NexusBuy Logo">
            <a href="../index.php">NexusBuy</a>
        </div>
        
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Crear una cuenta nueva</p>

                <form id="form-register">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username">Usuario</label>
                                <input type="text" name="username" class="form-control" id="username" placeholder="Ingrese su nombre de usuario" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="pass">Contraseña</label>
                                <input type="password" name="pass" class="form-control" id="pass" placeholder="Cree una contraseña segura" required>
                                <div class="password-strength strength-weak" id="password-strength"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="nombres">Nombres</label>
                                <input type="text" name="nombres" class="form-control" id="nombres" placeholder="Ingrese sus nombres" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="dni">DNI</label>
                                <input type="text" name="dni" class="form-control" id="dni" placeholder="Ingrese su carnet de identidad" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" id="telefono" placeholder="Ingrese su número de teléfono" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" class="form-control" id="email" placeholder="Ingrese su correo electrónico" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="pass_repeat">Repetir Contraseña</label>
                                <input type="password" name="pass_repeat" class="form-control" id="pass_repeat" placeholder="Confirme su contraseña" required>
                                <small id="password-match" class="form-text text-danger" style="display: none;">Las contraseñas no coinciden</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="apellidos">Apellidos</label>
                                <input type="text" name="apellidos" class="form-control" id="apellidos" placeholder="Ingrese sus apellidos" required>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="terms" class="custom-control-input" id="terms" required>
                                    <label class="custom-control-label" for="terms">
                                        Acepto los <a href="#" data-toggle="modal" data-target="#terminos">términos y condiciones</a> del servicio.
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus mr-2"></i>Crear mi cuenta
                    </button>
                    
                    <div class="text-center mt-3">
                        <a href="login.php" class="text-primary">
                            <i class="fas fa-sign-in-alt mr-1"></i>¿Ya tienes cuenta? Inicia sesión aquí
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="../Util/Js/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../Util/Js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../Util/Js/adminlte.min.js"></script>
    <script src="../Util/Js/jquery.validate.min.js"></script>
    <script src="../Util/Js/toastr.min.js"></script>
    <script src="../Util/Js/additional-methods.min.js"></script>
    <script src="../Util/Js/sweetalert2.min.js"></script>
    <script src="register.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password strength indicator
            const passwordInput = document.getElementById('pass');
            const strengthIndicator = document.getElementById('password-strength');
            
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                if (password.length >= 8) strength++;
                if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
                if (password.match(/\d/)) strength++;
                if (password.match(/[^a-zA-Z\d]/)) strength++;
                
                strengthIndicator.className = 'password-strength';
                
                if (password.length === 0) {
                    strengthIndicator.style.width = '0%';
                } else if (strength <= 2) {
                    strengthIndicator.classList.add('strength-weak');
                } else if (strength === 3) {
                    strengthIndicator.classList.add('strength-medium');
                } else {
                    strengthIndicator.classList.add('strength-strong');
                }
            });
            
            // Password confirmation check
            const confirmPassword = document.getElementById('pass_repeat');
            const passwordMatch = document.getElementById('password-match');
            
            confirmPassword.addEventListener('input', function() {
                if (this.value !== passwordInput.value && this.value.length > 0) {
                    passwordMatch.style.display = 'block';
                } else {
                    passwordMatch.style.display = 'none';
                }
            });
            
            // Form submission
            const form = document.getElementById('form-register');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Check if passwords match
                if (passwordInput.value !== confirmPassword.value) {
                    toastr.error('Las contraseñas no coinciden', 'Error de validación');
                    return;
                }
                
                // Check if terms are accepted
                if (!document.getElementById('terms').checked) {
                    toastr.error('Debe aceptar los términos y condiciones', 'Error de validación');
                    return;
                }
                
                const submitButton = this.querySelector('button[type="submit"]');
                const originalText = submitButton.innerHTML;
                
                // Show loading state
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creando cuenta...';
                submitButton.disabled = true;
                
                
            });
        });
    </script>
</body>
</html>
