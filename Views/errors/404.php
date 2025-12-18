<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página No Encontrada - NexusBuy</title>
    <link href="../../Util/Css/bootstrap.min.css" rel="stylesheet">
    <style>
        .error-container {
            min-height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <!-- <?php include '../Layauts/header_general.php'; ?> -->
    
    <div class="container error-container">
        <div class="text-center">
            <h1 class="display-1 text-muted">404</h1>
            <h2 class="mb-3">Página No Encontrada</h2>
            <p class="mb-4">La página que buscas no existe o ha sido movida.</p>
            <a href="index.php?page=inicio" class="btn btn-primary">
                <i class="fas fa-home"></i> Volver al Inicio
            </a>
        </div>
    </div>
    
    <!-- <?php include '../Layauts/footer_general.php'; ?> -->
</body>
</html>