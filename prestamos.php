<?php
/* 
    Tarea: API Restfull - Agregar operaciones CRUD de usuarios
    Integrantes: Angel Alexis Nolasco Acosta, Julio Manuel Guzman Zarrabal
    Grupo: 18U
    Maestro: Esquivel Pat Agustin
    http://localhost:8080/apibiblioteca/index.php?PATH_INFO=usuarios/
*/
require_once "ConexionBD.php";
require_once "ExceptionApi.php";

class prestamos {
    // Datos de la tabla "prestamo"
    const NOMBRE_TABLA = "prestamo";
    const ID_PRESTAMO = "id_prestamo";
    const ID_LIBRO = "id_libro";
    const FECHA_PRESTAMO = "fecha_prestamo";
    const FECHA_DEVOLUCION = "fecha_devolucion";
    const NOMBRE_TABLAU = "usuario";
    const ID_USUARIO = "id_usuario";
    const TOKEN = "token";
    const ESTADO_CREACION_EXITOSA  = "Creación con éxito";
    const ESTADO_CREACION_FALLIDA = "Creación fallida";
    const ESTADO_UPDATE_EXITOSA = "Modificación exitosa";
    const ESTADO_UPDATE_FALLIDA = "Modificación fallida";
    const ESTADO_DELETE_EXITOSA = "Eliminación exitosa";
    const ESTADO_DELETE_FALLIDA = "Eliminación fallida";
    const ESTADO_ERROR_BD = -1;
    const ESTADO_CLAVE_NO_AUTORIZADA = 410;
    const ESTADO_AUSENCIA_CLAVE_API = 411;
    const ESTADO_URL_INCORRECTA = 412;

    public static function get($peticion) {
        $idUsuario = self::autorizar();

        if ($idUsuario == null) {
            throw new ExcepcionApi(self::ESTADO_CLAVE_NO_AUTORIZADA, "Clave API no autorizada");
        }

        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
            if (empty($peticion)) {
                $sentencia = $pdo->prepare("SELECT * FROM " . self::NOMBRE_TABLA . " WHERE " . self::ID_USUARIO . " = ?");
                $sentencia->bindParam(1, $idUsuario);

                if ($sentencia->execute()) {
                    $resultado = $sentencia->fetchAll(PDO::FETCH_ASSOC);
                    if ($resultado) {
                        return $resultado;
                    } else {
                        throw new ExcepcionApi(self::ESTADO_ERROR_BD, "No se encontraron préstamos.");
                    }
                } else {
                    throw new ExcepcionApi(self::ESTADO_ERROR_BD, "Se ha producido un error al recuperar los préstamos.");
                }
            } else {
                $idPrestamo = $peticion[0]; // Asumiendo que el ID del préstamo es el primer elemento de $peticion
                $sentencia = $pdo->prepare("SELECT * FROM " . self::NOMBRE_TABLA . " WHERE " . self::ID_PRESTAMO . " = ? AND " . self::ID_USUARIO . " = ?");
                $sentencia->bindParam(1, $idPrestamo);
                $sentencia->bindParam(2, $idUsuario);
                
                if ($sentencia->execute()) {
                    $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
                    if ($resultado) {
                        return $resultado;
                    } else {
                        throw new ExcepcionApi(self::ESTADO_ERROR_BD, "No se encontró el préstamo.");
                    }
                } else {
                    throw new ExcepcionApi(self::ESTADO_ERROR_BD, "Se ha producido un error al recuperar los detalles del préstamo.");
                }
            }
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }


    public static function autorizar() {
        $cabeceras = apache_request_headers();

        if (isset($cabeceras["Authorization"])) {
            $claveApi = $cabeceras["Authorization"];

            if (self::validarClaveApi($claveApi)) {
                return self::obtenerIdUsuario($claveApi);
            } else {
                throw new ExcepcionApi(self::ESTADO_CLAVE_NO_AUTORIZADA, "Token no autorizado", 401);
            }
        } else {
            throw new ExcepcionApi(self::ESTADO_AUSENCIA_CLAVE_API, "Se requiere Token para autenticación", 400);
        }
    }

    private static function validarClaveApi($claveApi) {
        $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
        $comando = "SELECT COUNT(" . self::ID_USUARIO . ") FROM " . self::NOMBRE_TABLAU . " WHERE " . self::TOKEN . " = ?";
        $sentencia = $pdo->prepare($comando);
        $sentencia->bindParam(1, $claveApi);
        $sentencia->execute();

        return $sentencia->fetchColumn(0) > 0;
    }

    private static function obtenerIdUsuario($claveApi) {
        $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
        $comando = "SELECT " . self::ID_USUARIO . " FROM " . self::NOMBRE_TABLAU . " WHERE " . self::TOKEN . " = ?";
        $sentencia = $pdo->prepare($comando);
        $sentencia->bindParam(1, $claveApi);

        if ($sentencia->execute()) {
            $resultado = $sentencia->fetch();
            return $resultado[self::ID_USUARIO];
        } else {
            return null;
        }
    }
}
?>
