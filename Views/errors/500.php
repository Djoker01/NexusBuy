<?php
http_response_code(500);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error del Servidor - NexusBuy</title>
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
    <?php include '../Layauts/header_general.php'; ?>
    
    <div class="container error-container">
        <div class="text-center">
            <h1 class="display-1 text-danger">500</h1>
            <h2 class="mb-3">Error del Servidor</h2>
            <p class="mb-4">Ha ocurrido un error interno. Por favor, intenta más tarde.</p>
            <a href="index.php?page=inicio" class="btn btn-primary">
                <i class="fas fa-home"></i> Volver al Inicio
            </a>
            <?php if (ENVIRONMENT === 'development' && isset($e)): ?>
                <div class="mt-4 p-3 bg-light rounded">
                    <small class="text-muted"><?php echo $e->getMessage(); ?></small>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include '../Layauts/footer_general.php'; ?>
</body>
</html>