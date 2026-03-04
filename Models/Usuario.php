<?php
include_once 'Conexion.php';
class Usuario
{
    var $objetos;
    var $acceso;
    public function __construct()
    {
        $db = new Conexion();
        $this->acceso = $db->pdo;
    }

    function verificar_usuario($user)
    {
        $sql = "SELECT * FROM usuario
                    WHERE username =:username";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':username' => $user));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    function registrar_usuario($username,$email, $pass, $nombres, $apellidos, $dni, $telefono)
    {
        $sql = "INSERT INTO usuario(username, email, password_hash, nombres, apellidos, dni, telefono, id_tipo_usuario) 
                VALUES(:username, :email, :password_hash, :nombres, :apellidos, :dni, :telefono, :id_tipo_usuario)";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => $pass,
            ':nombres' => $nombres,
            ':apellidos' => $apellidos,
            ':dni' => $dni,
            ':telefono' => $telefono,
            ':id_tipo_usuario' => 2
        ));
        return $this->acceso->lastInsertId();
    }

    function obtener_datos($user)
    {
        $sql = "SELECT u.*, tu.nombre as tipo_usuario, tu.nivel_permisos
                FROM usuario u
                JOIN tipo_usuario tu ON u.id_tipo_usuario = tu.id
                WHERE u.id = :user";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':user' => $user));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    function obtener_datos_por_username($username)
    {
        $sql = "SELECT u.*, tu.nombre as tipo_usuario, tu.nivel_permisos
                FROM usuario u
                JOIN tipo_usuario tu ON u.id_tipo_usuario = tu.id
                WHERE u.username = :username";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':username' => $username));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    function editar_datos($id_usuario, $nombres, $apellidos, $dni, $email, $telefono, $avatar = null, $fecha_nacimiento = null, $genero = null)
    {
        if ($avatar != '') {
            $sql = "UPDATE usuario SET nombres = :nombres,
                                    apellidos = :apellidos,
                                    dni = :dni,
                                    email = :email,
                                    telefono = :telefono,
                                    fecha_nacimiento = :fecha_nacimiento,
                                    genero = :genero,
                                    avatar = :avatar,
                                    fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id = :id_usuario";
            $query = $this->acceso->prepare($sql);
            $variables = array(
                ':id_usuario' => $id_usuario,
                ':nombres' => $nombres,
                ':apellidos' => $apellidos,
                ':dni' => $dni,
                ':email' => $email,
                ':telefono' => $telefono,
                ':fecha_nacimiento' => $fecha_nacimiento,
                ':genero' => $genero,
                ':avatar' => $avatar
            );
            $query->execute($variables);
        } else {
            $sql = "UPDATE usuario SET nombres = :nombres,
                                    apellidos = :apellidos,
                                    dni = :dni,
                                    email = :email,
                                    telefono = :telefono,
                                    fecha_nacimiento = :fecha_nacimiento,
                                    genero = :genero,
                                    fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id = :id_usuario";
            $query = $this->acceso->prepare($sql);
            $variables = array(
                ':id_usuario' => $id_usuario,
                ':nombres' => $nombres,
                ':apellidos' => $apellidos,
                ':dni' => $dni,
                ':email' => $email,
                ':telefono' => $telefono,
                ':fecha_nacimiento' => $fecha_nacimiento,
                ':genero' => $genero
            );
            $query->execute($variables);
        }

        return $query->rowCount();
    }

    function cambiar_contra($id_usuario, $pass_new)
    {
        $sql = "UPDATE usuario SET password_hash = :password_hash,
                                  fecha_actualizacion = CURRENT_TIMESTAMP                                  
                WHERE id = :id_usuario";
        $query = $this->acceso->prepare($sql);
        $variables = array(
            ':id_usuario' => $id_usuario,
            ':password_hash' => $pass_new
        );
        $query->execute($variables);
        return $query->rowCount();
    }

    function actualizar_ultimo_login($id_usuario)
    {
        $sql = "UPDATE usuario SET ultimo_login = CURRENT_TIMESTAMP
                WHERE id = :id_usuario";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':id_usuario' => $id_usuario));
        return $query->rowCount();
    }

    function verificar_email($email)
    {
        $sql = "SELECT * FROM usuario
                WHERE email = :email
                AND estado = 'activo'";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':email' => $email));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    function actualizar_token_recuperacion($email, $token, $fecha_expiracion)
    {
        $sql = "UPDATE usuario SET token_recuperacion = :token,
                                  fecha_expiracion_token = :fecha_expiracion
                WHERE email = :email";
        $query = $this->acceso->prepare($sql);
        $variables = array(
            ':email' => $email,
            ':token' => $token,
            ':fecha_expiracion' => $fecha_expiracion
        );
        $query->execute($variables);
        return $query->rowCount();
    }

    function verificar_token_recuperacion($token)
    {
        $sql = "SELECT * FROM usuario
                WHERE token_recuperacion = :token
                AND fecha_expiracion_token > NOW()";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':token' => $token));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }

    function actualizar_password_por_token($token, $nueva_password)
    {
        $sql = "UPDATE usuario SET password_hash = :password_hash,
                                  token_recuperacion = NULL,
                                  fecha_expiracion_token = NULL,
                                  fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE token_recuperacion = :token";
        $query = $this->acceso->prepare($sql);
        $variables = array(
            ':token' => $token,
            ':password_hash' => $nueva_password
        );
        $query->execute($variables);
        return $query->rowCount();
    }

    function verificar_dni($dni)
    {
        $sql = "SELECT * FROM usuario
                WHERE dni = :dni
                AND estado = 'activo'";
        $query = $this->acceso->prepare($sql);
        $query->execute(array(':dni' => $dni));
        $this->objetos = $query->fetchAll();
        return $this->objetos;
    }
}
