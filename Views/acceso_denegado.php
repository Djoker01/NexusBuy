<?php
$pageTitle = "Acceso Denegado";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusBuy - <?php echo $pageTitle; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .denied-container {
            text-align: center;
            background: white;
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        .denied-icon {
            font-size: 4rem;
            color: #e63946;
            margin-bottom: 1rem;
        }
        h1 {
            color: #212529;
            margin-bottom: 1rem;
        }
        p {
            color: #6c757d;
            margin-bottom: 2rem;
        }
        .btn {
            background: #4361ee;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #3651d4;
        }
    </style>
</head>
<body>
    <div class="denied-container">
        <div class="denied-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        <h1>Acceso Denegado</h1>
        <p>No tienes permisos para acceder a esta sección.<br>Si crees que esto es un error, contacta al administrador.</p>
        <a href="/nexusbuy/index.php" class="btn">Ir al Inicio</a>
    </div>
</body>
</html>