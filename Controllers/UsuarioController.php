<?php
include_once '../Models/Usuario.php';
include_once '../Models/Conexion.php';
include_once '../Models/Historial.php';
include_once '../Models/Orden.php';
include_once '../Models/Reseña.php';
include_once '../Models/UsuarioMunicipio.php';
include_once '../Util/Config/config.php';
$usuario = new Usuario();
$historial = new Historial();
$orden = new Orden();
$reseña = new Reseña();
$usuario_municipio = new UsuarioMunicipio();
session_start();

if ($_POST['funcion'] == 'login') {
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    $usuario->verificar_usuario($user);

    if (!empty($usuario->objetos)) {
        $pass_bd = $usuario->objetos[0]->password_hash;
        if (password_verify($pass, $pass_bd)) {
            $_SESSION['id'] = $usuario->objetos[0]->id;
            $_SESSION['user'] = $usuario->objetos[0]->username;
            $_SESSION['tipo_usuario'] = $usuario->objetos[0]->id_tipo_usuario;
            $_SESSION['avatar'] = $usuario->objetos[0]->avatar;

            // Actualizar último login
            $usuario->actualizar_ultimo_login($usuario->objetos[0]->id);
        }
        echo $_SESSION['tipo_usuario'];
    } else {
        echo 'error';
    }
}

if ($_POST['funcion'] == 'verificar_sesion') {
    if (!empty($_SESSION['id'])) {
        $json[] = array(
            'id' => $_SESSION['id'],
            'user' => $_SESSION['user'],
            'tipo_usuario' => $_SESSION['tipo_usuario'],
            'avatar' => $_SESSION['avatar']
        );
        $jsonstring = json_encode($json[0]);
        echo $jsonstring;
    } else {
        echo '';
    }
}

if ($_POST['funcion'] == 'verificar_usuario') {
    $username = $_POST['value'];
    $usuario->verificar_usuario($username);
    if (!empty($usuario->objetos)) {
        echo 'success';
    } else {
        echo 'error';
    }
}

if ($_POST['funcion'] == 'verificar_email') {
    $email = $_POST['value'];
    $usuario->verificar_email($email);
    if (!empty($usuario->objetos)) {
        echo 'success';
    } else {
        echo 'error';
    }
}

if ($_POST['funcion'] == 'verificar_dni') {
    $dni = $_POST['value'];
    $usuario->verificar_dni($dni);
    if (!empty($usuario->objetos)) {
        echo 'success';
    } else {
        echo 'error';
    }
}

if ($_POST['funcion'] == 'registrar_usuario') {
    $username = $_POST['username'];
    $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT);
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $dni = $_POST['dni'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];

    $usuario->registrar_usuario($username,$email, $pass, $nombres, $apellidos, $dni, $telefono);
    echo 'success';
}

if ($_POST['funcion'] == 'obtener_datos') {
    $usuario->obtener_datos($_SESSION['id']);
    foreach ($usuario->objetos as $objeto) {
        $json[] = array(
            'username' => $objeto->username,
            'nombres' => $objeto->nombres,
            'apellidos' => $objeto->apellidos,
            'dni' => $objeto->dni,
            'email' => $objeto->email,
            'telefono' => $objeto->telefono,
            'avatar' => $objeto->avatar,
            'tipo_usuario' => $objeto->tipo_usuario,
            'fecha_nacimiento' => $objeto->fecha_nacimiento,
            'genero' => $objeto->genero,
            'ultimo_login' => $objeto->ultimo_login
        );
    }
    $jsonstring = json_encode($json[0]);
    echo $jsonstring;
}

if ($_POST['funcion'] == 'editar_datos') {
    $id_usuario = $_SESSION['id'];
    $nombres = $_POST['nombres_mod'];
    $apellidos = $_POST['apellidos_mod'];
    $dni = $_POST['dni_mod'];
    $email = $_POST['email_mod'];
    $telefono = $_POST['telefono_mod'];
    $fecha_nacimiento = $_POST['fecha_nacimiento_mod'] ?? null;
    $genero = $_POST['genero_mod'] ?? null;
    $avatar = $_FILES['avatar_mod']['name'] ?? '';

    $usuario->obtener_datos($id_usuario);
    $datos_cambiados = 'Ha realizado los siguientes cambios: ';

    if ($nombres != $usuario->objetos[0]->nombres || $apellidos != $usuario->objetos[0]->apellidos || $dni != $usuario->objetos[0]->dni || $email != $usuario->objetos[0]->email || $telefono != $usuario->objetos[0]->telefono || $avatar != '' || $fecha_nacimiento != $usuario->objetos[0]->fecha_nacimiento || $genero != $usuario->objetos[0]->genero) {

        if ($nombres != $usuario->objetos[0]->nombres) {
            $datos_cambiados .= 'su nombre cambio de: ' . $usuario->objetos[0]->nombres . ' a ' . $nombres . ', ';
        }
        if ($apellidos != $usuario->objetos[0]->apellidos) {
            $datos_cambiados .= 'su apellido cambio de: ' . $usuario->objetos[0]->apellidos . ' a ' . $apellidos . ', ';
        }
        if ($dni != $usuario->objetos[0]->dni) {
            $datos_cambiados .= 'su DNI cambio de: ' . $usuario->objetos[0]->dni . ' a ' . $dni . ', ';
        }
        if ($email != $usuario->objetos[0]->email) {
            $datos_cambiados .= 'su email cambio de: ' . $usuario->objetos[0]->email . ' a ' . $email . ', ';
        }
        if ($telefono != $usuario->objetos[0]->telefono) {
            $datos_cambiados .= 'su telefono cambio de: ' . $usuario->objetos[0]->telefono . ' a ' . $telefono . ', ';
        }
        if ($fecha_nacimiento != $usuario->objetos[0]->fecha_nacimiento) {
            $datos_cambiados .= 'su fecha de nacimiento fue actualizada, ';
        }
        if ($genero != $usuario->objetos[0]->genero) {
            $datos_cambiados .= 'su género fue actualizado, ';
        }

        if ($avatar != '') {
            $datos_cambiados .= 'su avatar fue cambiado. ';
            $nombre = uniqid() . '-' . $avatar;
            $ruta = '../Util/Img/Users/' . $nombre;
            move_uploaded_file($_FILES['avatar_mod']['tmp_name'], $ruta);
            $usuario->obtener_datos($id_usuario);
            foreach ($usuario->objetos as $objeto) {
                $avatar_actual = $objeto->avatar;
                if ($avatar_actual != 'default_avatar.png') {
                    unlink('../Util/Img/Users/' . $avatar_actual);
                }
            }
            $_SESSION['avatar'] = $nombre;
        } else {
            $nombre = '';
        }

        $usuario->editar_datos($id_usuario, $nombres, $apellidos, $dni, $email, $telefono, $nombre, $fecha_nacimiento, $genero);
        $descripcion = 'Ha editado sus datos personales, ' . $datos_cambiados;
        $historial->crear_historial($descripcion, 1, 1, $id_usuario);
        echo 'success';
    } else {
        echo 'danger';
    }
}

if ($_POST['funcion'] == 'cambiar_contra') {
    $id_usuario = $_SESSION['id'];
    $user = $_SESSION['user'];
    $pass_old = $_POST['pass_old'];
    $pass_new = $_POST['pass_new'];

    $usuario->verificar_usuario($user);
    if (!empty($usuario->objetos)) {
        if (password_verify($pass_old, $usuario->objetos[0]->password_hash)) {
            $pass_new_hash = password_hash($pass_new, PASSWORD_DEFAULT);
            $usuario->cambiar_contra($id_usuario, $pass_new_hash);
            $descripcion = 'Ha cambiado su password';
            $historial->crear_historial($descripcion, 1, 1, $id_usuario);
            echo 'success';
        } else {
            echo 'error';
        }
    } else {
        echo 'error';
    }
}

if ($_POST['funcion'] == 'recuperar_contra') {
    $email = $_POST['email'];

    $usuario->verificar_email($email);
    if (!empty($usuario->objetos)) {
        $token = bin2hex(random_bytes(32));
        $fecha_expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $usuario->actualizar_token_recuperacion($email, $token, $fecha_expiracion);

        // Aquí deberías enviar el email con el token
        // $enlace_recuperacion = "https://tudominio.com/recuperar_contra.php?token=" . $token;
        // enviar_email_recuperacion($email, $enlace_recuperacion);

        echo 'success';
    } else {
        echo 'error';
    }
}

if ($_POST['funcion'] == 'resetear_contra') {
    $token = $_POST['token'];
    $nueva_pass = $_POST['nueva_pass'];

    $usuario->verificar_token_recuperacion($token);
    if (!empty($usuario->objetos)) {
        $nueva_pass_hash = password_hash($nueva_pass, PASSWORD_DEFAULT);
        $usuario->actualizar_password_por_token($token, $nueva_pass_hash);
        echo 'success';
    } else {
        echo 'error';
    }
}

if ($_POST['funcion'] == 'obtener_actividad_usuario') {
    if (empty($_SESSION['id'])) {
        echo json_encode(['error' => 'no_sesion']);
        exit();
    }

    $id_usuario = $_SESSION['id'];
    $filtro_tipo = $_POST['filtro_tipo'] ?? '';
    $filtro_periodo = intval($_POST['filtro_periodo'] ?? 30);

    try {
        $actividades = [];

        // 1. Obtener historial del sistema
        $historial->llenar_historial($id_usuario);
        foreach ($historial->objetos as $item) {
            $actividades[] = [
                'tipo' => 'sistema',
                'subtipo' => $item->tipo_historial,
                'fecha' => $item->fecha,
                'descripcion' => $item->descripcion,
                'icono' => $item->th_icono,
                'modulo' => $item->modulo,
                'modulo_icono' => $item->m_icono
            ];
        }

        // 2. Obtener pedidos del usuario
        $sql_pedidos = "SELECT * FROM orden 
                       WHERE id_usuario = :id_usuario 
                       ORDER BY fecha_creacion DESC 
                       LIMIT 50";
        $query = $orden->acceso->prepare($sql_pedidos);
        $query->execute([':id_usuario' => $id_usuario]);
        $pedidos = $query->fetchAll();

        foreach ($pedidos as $pedido) {
            $actividades[] = [
                'tipo' => 'compra',
                'subtipo' => 'pedido',
                'fecha' => $pedido->fecha_creacion,
                'descripcion' => "Realizó un pedido #{$pedido->numero_orden} por $ {$pedido->total}",
                'icono' => 'fas fa-shopping-cart',
                'detalles' => [
                    'numero_orden' => $pedido->numero_orden,
                    'total' => $pedido->total,
                    'estado' => $pedido->estado
                ]
            ];
        }

        // 3. Obtener reseñas del usuario
        $sql_resenas = "SELECT r.*, p.nombre as producto_nombre 
                       FROM resena r
                       JOIN producto_tienda pt ON r.id_producto_tienda = pt.id
                       JOIN producto p ON pt.id_producto = p.id
                       WHERE r.id_usuario = :id_usuario 
                       AND r.estado = 'aprobada'
                       ORDER BY r.fecha_creacion DESC 
                       LIMIT 50";
        $query = $reseña->acceso->prepare($sql_resenas);
        $query->execute([':id_usuario' => $id_usuario]);
        $resenas = $query->fetchAll();

        foreach ($resenas as $resena) {
            $actividades[] = [
                'tipo' => 'reseña',
                'subtipo' => 'reseña_producto',
                'fecha' => $resena->fecha_creacion,
                'descripcion' => "Reseñó el producto '{$resena->producto_nombre}' con {$resena->calificacion} estrellas",
                'icono' => 'fas fa-star',
                'detalles' => [
                    'producto' => $resena->producto_nombre,
                    'calificacion' => $resena->calificacion,
                    'comentario' => $resena->comentario
                ]
            ];
        }

        // 4. Obtener direcciones del usuario
        $usuario_municipio->llenar_direcciones($id_usuario);
        foreach ($usuario_municipio->objetos as $direccion) {
            $actividades[] = [
                'tipo' => 'direccion',
                'subtipo' => 'direccion_agregada',
                'fecha' => $direccion->fecha_creacion,
                'descripcion' => "Agregó la dirección: {$direccion->direccion}, {$direccion->municipio}",
                'icono' => 'fas fa-map-marker-alt',
                'detalles' => [
                    'direccion' => $direccion->direccion,
                    'municipio' => $direccion->municipio,
                    'provincia' => $direccion->provincia
                ]
            ];
        }

        // Ordenar actividades por fecha (más reciente primero)
        usort($actividades, function ($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });

        // Aplicar filtros
        if ($filtro_tipo && $filtro_tipo != '') {
            $actividades = array_filter($actividades, function ($actividad) use ($filtro_tipo) {
                return $actividad['tipo'] == $filtro_tipo;
            });
        }

        if ($filtro_periodo > 0) {
            $fecha_limite = date('Y-m-d H:i:s', strtotime("-$filtro_periodo days"));
            $actividades = array_filter($actividades, function ($actividad) use ($fecha_limite) {
                return $actividad['fecha'] >= $fecha_limite;
            });
        }

        // Limitar a 50 actividades
        $actividades = array_slice($actividades, 0, 50);

        echo json_encode([
            'success' => true,
            'actividades' => $actividades,
            'estadisticas' => [
                'total_pedidos' => count($pedidos),
                'total_resenas' => count($resenas),
                'total_actualizaciones' => count(array_filter($actividades, function ($a) {
                    return $a['tipo'] == 'sistema' && strpos($a['descripcion'], 'editó') !== false;
                })),
                'total_direcciones' => count($usuario_municipio->objetos)
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al cargar la actividad: ' . $e->getMessage()
        ]);
    }
}
