<?php
//Kevin Jesus Yam Sanchez
//Angel Alberto Castellanos Sulub
require_once "ConexionBD.php";
require_once "ExceptionApi.php";

class usuarios{
    // Datos de la tabla "usuario"
    const NOMBRE_TABLA = "usuario";
    const ID_USUARIO = "usuario_id";
    const NOMBRE = "nombre";
    const CORREO = "correo";
    const PASSWORD = "password";
    const TOKEN = "token";
    const ESTADO_CREACION_EXITOSA  = "Creación con éxito";
    const ESTADO_CREACION_FALLIDA = "Creación fallida";
    const ESTADO_UPDATE_EXITOSA = "Modificacion exitosa";
    const ESTADO_UPDATE_FALLIDA = "Mofidicacion fallida";
    const ESTADO_DELETE_EXITOSA = "Eliminacion exitosa";
    const ESTADO_DELETE_FALLIDA = "Eliminacion fallida";
    const ESTADO_ERROR_BD = -1;
    const ESTADO_CLAVE_NO_AUTORIZADA = 410;
    const ESTADO_AUSENCIA_CLAVE_API = 411;

    public static function get($peticion)
    {   
        $idUsuario = usuarios::autorizar();
        
        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
            // Preparar la sentencia SQL
            $sentencia = $pdo->prepare("SELECT * FROM " . self::NOMBRE_TABLA . " WHERE " . self::ID_USUARIO . " = ?");

            $sentencia->bindParam(1, $idUsuario);
            
            if ($sentencia->execute()) {
                // Recuperar los detalles del usuario
                $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
                return $resultado;
            } else {
                throw new ExcepcionApi(self::ESTADO_ERROR_BD, "Se ha producido un error al recuperar los detalles del usuario.");
            }
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }  

    public static function post($peticion){
        //Procesar post
        //this->crear($peticion);
        if ($peticion[0] == 'crear') {
            $cuerpo = file_get_contents('php://input');
            $datosBoton = json_decode($cuerpo);
            return self::crear($datosBoton);
        }else {
            throw new ExcepcionApi(self::ESTADO_URL_INCORRECTA, "Url mal formada", 400);
        }
    }

    public static function put($peticion){
        //Procesar put
        if ($peticion[0] == 'actualizar') {
            $cuerpo = file_get_contents('php://input');
            $datosBoton = json_decode($cuerpo);
            return self::actualizar($datosBoton); // Aquí debes llamar a actualizar, no a crear
        }else {
            throw new ExcepcionApi(self::ESTADO_URL_INCORRECTA, "Url mal formada", 400);
        }
    }

    public static function delete($peticion){
        //Procesar put
        if ($peticion[0] == 'eliminar') {
            $cuerpo = file_get_contents('php://input');
            $datosBoton = json_decode($cuerpo);
            return self::eliminar($datosBoton);
        }else {
            throw new ExcepcionApi(self::ESTADO_URL_INCORRECTA, "Url mal formada", 400);
        }
    }

    public static function crear($datosBoton)
    {
        $nombre = $datosBoton->nombre;
        $correo = $datosBoton->correo;
        $password = password_hash($datosBoton->password, PASSWORD_BCRYPT, ['cost' => 4]);
        $token = $datosBoton->token;

        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
            // Sentencia INSERT
            $comando = "INSERT INTO " . self::NOMBRE_TABLA . " ( " .
                self::NOMBRE . "," .
                self::CORREO . "," .
                self::PASSWORD . "," .
                self::TOKEN . ")" .
                " VALUES(?,?,?,?)";

            $sentencia = $pdo->prepare($comando);

            $sentencia->bindParam(1, $nombre);
            $sentencia->bindParam(2, $correo);
            $sentencia->bindParam(3, $password);
            $sentencia->bindParam(4, $token);
            

            $resultado = $sentencia->execute();

            if ($resultado) {
                return self::ESTADO_CREACION_EXITOSA;
            } else {
                return self::ESTADO_CREACION_FALLIDA;
            }
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }

    } 

    public static function actualizar($datosBoton){

        $id = $datosBoton->usuario_id;
        $nombre = $datosBoton->nombre;
        $correo = $datosBoton->correo;
        $password = password_hash($datosBoton->password, PASSWORD_BCRYPT, ['cost' => 4]);
    
        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
            // Preparar la sentencia SQL
            $sentencia = $pdo->prepare("UPDATE ". self::NOMBRE_TABLA . " SET " . self::NOMBRE . " = ?, " . self::CORREO . " = ?, " . 
            self::PASSWORD . " = ? WHERE " . self::ID_USUARIO . " = ?");

            $sentencia->bindParam(1, $nombre);
            $sentencia->bindParam(2, $correo);
            $sentencia->bindParam(3, $password);
            $sentencia->bindParam(4, $id);
            
            $resultado = $sentencia->execute();
    
            if ($resultado) {
                return self::ESTADO_UPDATE_EXITOSA;
            } else {
                return self::ESTADO_UPDATE_FALLIDA;
            }
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    public static function eliminar($datosBoton){

        $id = $datosBoton->usuario_id;
    
        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
            // Preparar la sentencia SQL
            $sentencia = $pdo->prepare("DELETE FROM " . self::NOMBRE_TABLA . " WHERE " . self::ID_USUARIO . " = ?");
            
            $sentencia->bindParam(1, $id);

            $resultado = $sentencia->execute();
    
            if ($resultado) {
                return self::ESTADO_DELETE_EXITOSA;
            } else {
                return self::ESTADO_DELETE_FALLIDA;
            }
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    public static function autorizar(){
        $cabeceras = apache_request_headers();

        if (isset($cabeceras["Authorization"])) {

            $claveApi = $cabeceras["Authorization"];

            if (usuarios::validarClaveApi($claveApi)) {
                return usuarios::obtenerIdUsuario($claveApi);
            } else {
                throw new ExcepcionApi(
                    self::ESTADO_CLAVE_NO_AUTORIZADA, "Clave de API no autorizada", 401);
            }

        } else {
            throw new ExcepcionApi(
                self::ESTADO_AUSENCIA_CLAVE_API,
                utf8_encode("Se requiere Clave del API para autenticación"));
        }
    }

    private static function validarClaveApi($claveApi){
        $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
        $comando = "SELECT COUNT(" . self::ID_USUARIO . ")" .
            " FROM " . self::NOMBRE_TABLA .
            " WHERE " . self::TOKEN . "=?";
    
        $sentencia = $pdo->prepare($comando);
        $sentencia->bindParam(1, $claveApi);
        $sentencia->execute();
    
        return $sentencia->fetchColumn(0) > 0;
    }
    
    private static function obtenerIdUsuario($claveApi){
        $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
        $comando = "SELECT " . self::ID_USUARIO .
            " FROM " . self::NOMBRE_TABLA .
            " WHERE " . self::TOKEN . "=?";
    
        $sentencia = $pdo->prepare($comando);
        $sentencia->bindParam(1, $claveApi);
    
        if ($sentencia->execute()) {
            $resultado = $sentencia->fetch();
            return $resultado[self::ID_USUARIO];
        } else
            return null;
    }
    
}