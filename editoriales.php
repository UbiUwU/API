<?php
//Kevin Jesus Yam Sanchez
//Angel Alberto Castellanos Sulub
require_once "ConexionBD.php";
require_once "ExceptionApi.php";
require_once "usuarios.php";

class editoriales
{
    // Datos de la tabla "usuario"
    const NOMBRE_TABLA = "editoriales";
    const EDITORIALID = "EditorialID";
    const NOMBREEDITORIAL = "NombreEditorial";
    const DIRECCION = "Dirección";
    const CIUDAD = "Ciudad";
    const ESTADO = "Estado";
    const PAIS = "País";
    const TELEFONO = "Teléfono";
    const CORREOCONTACTO = "CorreoContacto";
    const NOMBRE_TABLO = "clientes";
    const CLIENTEID = "ClienteID";
    const TOKEN = "token";
    const ESTADO_CREACION_EXITOSA = "Creación con éxito";
    const ESTADO_CREACION_FALLIDA = "Creación fallida";
    const URL_FALLIDO = "URL Falliado";    
    const MENSAJE_EXITO_GET = "Obtención exitosa";
    const MENSAJE_EXITO_POST = "Creación exitosa";
    const MENSAJE_EXITO_DELETE = "Eliminación exitosa";
    const MENSAJE_EXITO_PUT = "Modificación exitosa";
    const MENSAJE_FALLA_POST = "Creación fallida";
    const MENSAJE_FALLA_DELETE = "Error al intentar eliminar la editorial";
    const MENSAJE_FALLA_PUT = "Error al intentar modificar la editorial";
    const ESTADO_ERROR_BD = -1;

    public static function get($peticion)
    {
        $ClienteID = self::autorizar();

        if ($ClienteID == null) {
            throw new ExcepcionApi(self::ESTADO_CLAVE_NO_AUTORIZADA, "Clave API no autorizada");
        }
        // Si hay parámetros en la solicitud
        if (!empty($peticion)) {
            // Si hay dos parámetros en la solicitud
            if (count($peticion) == 2) {
                // Obtener los valores de inicio y fin
                $inicio = intval($peticion[0]);
                $fin = intval($peticion[1]);

                // Verificar si el inicio es menor o igual al fin
                if ($inicio <= $fin) {
                    // Obtener los botones en el rango especificado
                    return self::obtenerEditorialesRango($inicio, $fin);
                } else {
                    // Si el inicio es mayor que el fin, devolver un mensaje de error
                    return self::ESTADO_CREACION_FALLIDA;
                }
            } else {
                // Si no hay exactamente dos parámetros, intentar obtener un botón por su ID
                $EditorialID = $peticion[0];
                return self::obtenerEditorial($EditorialID);
            }
        } else {
            // Si no hay parámetros en la solicitud, devolver todos los botones
            return self::obtenerEditoriales();
        }
    }


    private static function obtenerEditorialesRango($inicio, $fin)
    {
        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

            // Consulta SQL para obtener los botones en el rango especificado
            $comando = "SELECT * FROM " . self::NOMBRE_TABLA . " WHERE " . self::EDITORIALID . " BETWEEN ? AND ?";

            $sentencia = $pdo->prepare($comando);
            $sentencia->bindParam(1, $inicio, PDO::PARAM_INT);
            $sentencia->bindParam(2, $fin, PDO::PARAM_INT);
            $sentencia->execute();

            $editoriales = $sentencia->fetchAll(PDO::FETCH_ASSOC);

            return $editoriales;
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    private static function obtenerEditoriales()
    {
        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

            $comando = "SELECT * FROM " . self::NOMBRE_TABLA;

            $sentencia = $pdo->prepare($comando);
            $sentencia->execute();

            $editoriales = $sentencia->fetchAll(PDO::FETCH_ASSOC);

            return $editoriales;
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    private static function obtenerEditorial($EditorialID)
    {
        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

            $comando = "SELECT * FROM " . self::NOMBRE_TABLA . " WHERE " . self::EDITORIALID . " = ?";

            $sentencia = $pdo->prepare($comando);
            $sentencia->bindParam(1, $EditorialID);
            $sentencia->execute();

            $editorial = $sentencia->fetch(PDO::FETCH_ASSOC);

            if (!$editorial) {
                throw new ExcepcionApi(self::ESTADO_NO_ENCONTRADO, "El botón con ID $EditorialID no existe", 404);
            }

            return $editorial;
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    public static function post($peticion)
    {
        $ClienteID = self::autorizar();

        if ($ClienteID == null) {
            throw new ExcepcionApi(self::ESTADO_CLAVE_NO_AUTORIZADA, "Clave API no autorizada");
        }
        //Procesar post
        //this->crear($peticion);
        if ($peticion[0] == 'crear') {
            $cuerpo = file_get_contents('php://input');
            $datosEditorial = json_decode($cuerpo);
            return self::crear($datosEditorial);
        } else {
            throw new ExcepcionApi(self::ESTADO_URL_INCORRECTA, "Url mal formada", 400);
        }
    }

    public static function crear($datosEditorial)
    {
        $NombreEditorial = $datosEditorial->NombreEditorial;
        $Dirección = $datosEditorial->Dirección;
        $Ciudad = $datosEditorial->Ciudad;
        $Estado = $datosEditorial->Estado;
        $País = $datosEditorial->País;
        $Teléfono = $datosEditorial->Teléfono;
        $CorreoContacto = $datosEditorial->CorreoContacto;

        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
            // Sentencia INSERT
            $comando = "INSERT INTO " . self::NOMBRE_TABLA . " ( " .
                self::NOMBREEDITORIAL . "," .
                self::DIRECCION . "," .
                self::CIUDAD . "," .
                self::ESTADO . "," .
                self::PAIS . "," .
                self::TELEFONO . "," .
                self::CORREOCONTACTO . ")" .
                " VALUES(?,?,?,?,?,?,?)";

            $sentencia = $pdo->prepare($comando);

            $sentencia->bindParam(1, $NombreEditorial);
            $sentencia->bindParam(2, $Dirección);
            $sentencia->bindParam(3, $Ciudad);
            $sentencia->bindParam(4, $Estado);
            $sentencia->bindParam(5, $País);
            $sentencia->bindParam(6, $Teléfono);
            $sentencia->bindParam(7, $CorreoContacto);

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

    public static function delete($peticion)
    {
        $ClienteID = self::autorizar();

        if ($ClienteID == null) {
            throw new ExcepcionApi(self::ESTADO_CLAVE_NO_AUTORIZADA, "Clave API no autorizada");
        }
        // Si hay parámetros en la solicitud
        if (!empty($peticion)) {
            // Si hay dos parámetros en la solicitud
            if (count($peticion) == 2) {
                // Obtener los valores de inicio y fin
                $inicio = intval($peticion[0]);
                $fin = intval($peticion[1]);

                // Verificar si el inicio es menor o igual al fin
                if ($inicio <= $fin) {
                    // Eliminar los botones en el rango especificado
                    return self::eliminarEditorialesRango($inicio, $fin);
                } else {
                    // Si el inicio es mayor que el fin, devolver un mensaje de error
                    throw new ExcepcionApi(self::ESTADO_ERROR, "El parámetro de inicio debe ser menor o igual al parámetro de fin", 400);
                }
            } else {
                // Si no hay exactamente dos parámetros, intentar eliminar un botón por su ID
                $EditorialID = $peticion[0];
                return self::eliminarEditorial($EditorialID);
            }
        } else {
            throw new ExcepcionApi(self::ESTADO_URL_INCORRECTA, "URL mal formada", 400);
        }
    }

    private static function eliminarEditorialesRango($inicio, $fin)
    {
        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

            // Sentencia DELETE para eliminar los botones en el rango especificado
            $comando = "DELETE FROM " . self::NOMBRE_TABLA . " WHERE " . self::EDITORIALID . " BETWEEN ? AND ?";

            $sentencia = $pdo->prepare($comando);
            $sentencia->bindParam(1, $inicio, PDO::PARAM_INT);
            $sentencia->bindParam(2, $fin, PDO::PARAM_INT);
            $resultado = $sentencia->execute();


            if ($resultado) {
                return self::MENSAJE_EXITO_DELETE;
            } else {
                return self::MENSAJE_FALLA_DELETE;
            }
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }


    public static function eliminarEditorial($EditorialID)
    {
        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

            // Sentencia DELETE
            $comando = "DELETE FROM " . self::NOMBRE_TABLA . " WHERE " . self::EDITORIALID . " = ?";

            $sentencia = $pdo->prepare($comando);
            $sentencia->bindParam(1, $EditorialID);
            $resultado = $sentencia->execute();

            if ($resultado) {
                return self::MENSAJE_EXITO_DELETE;
            } else {
                return self::MENSAJE_FALLA_DELETE;
            }
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }
    public static function put($peticion)
    {
        $ClienteID = self::autorizar();

        if ($ClienteID == null) {
            throw new ExcepcionApi(self::ESTADO_CLAVE_NO_AUTORIZADA, "Clave API no autorizada");
        }
        if (!empty($peticion)) {
            $EditorialID = $peticion[0];
            $cuerpo = file_get_contents('php://input');
            $datosEditorial = json_decode($cuerpo);
            return self::modificarEditorial($EditorialID, $datosEditorial);
        } else {
            throw new ExcepcionApi(self::ESTADO_URL_INCORRECTA, "URL mal formada", 400);
        }
    }

    public static function modificarEditorial($EditorialID, $datosEditorial)
    {
        $NombreEditorial = $datosEditorial->NombreEditorial;
        $Dirección = $datosEditorial->Dirección;
        $Ciudad = $datosEditorial->Ciudad;
        $Estado = $datosEditorial->Estado;
        $País = $datosEditorial->País;
        $Teléfono = $datosEditorial->Teléfono;
        $CorreoContacto = $datosEditorial->CorreoContacto;

        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

            // Sentencia UPDATE
            $comando = "UPDATE " . self::NOMBRE_TABLA . " SET " .
                self::NOMBREEDITORIAL . "= ?," .
                self::DIRECCION . "= ?," .
                self::CIUDAD . "= ?," .
                self::ESTADO . "= ?," .
                self::PAIS . "= ?," .
                self::TELEFONO . "= ?," .
                self::CORREOCONTACTO . "= ?" .
                " WHERE " . self::EDITORIALID . " = ?";

            $sentencia = $pdo->prepare($comando);
            $sentencia->bindParam(1, $NombreEditorial);
            $sentencia->bindParam(2, $Dirección);
            $sentencia->bindParam(3, $Ciudad);
            $sentencia->bindParam(4, $Estado);
            $sentencia->bindParam(5, $País);
            $sentencia->bindParam(6, $Teléfono);
            $sentencia->bindParam(7, $CorreoContacto);
            $sentencia->bindParam(8, $EditorialID);

            $resultado = $sentencia->execute();

            if ($resultado) {
                return self::MENSAJE_EXITO_PUT;
            } else {
                return self::MENSAJE_FALLA_PUT;
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
        $comando = "SELECT COUNT(" . self::CLIENTEID . ") FROM " . self::NOMBRE_TABLO . " WHERE " . self::TOKEN . " = ?";
        $sentencia = $pdo->prepare($comando);
        $sentencia->bindParam(1, $claveApi);
        $sentencia->execute();

        return $sentencia->fetchColumn(0) > 0;
    }

    private static function obtenerIdUsuario($claveApi) {
        $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
        $comando = "SELECT " . self::CLIENTEID . " FROM " . self::NOMBRE_TABLO . " WHERE " . self::TOKEN . " = ?";
        $sentencia = $pdo->prepare($comando);
        $sentencia->bindParam(1, $claveApi);

        if ($sentencia->execute()) {
            $resultado = $sentencia->fetch();
            return $resultado[self::CLIENTEID];
        } else {
            return null;
        }
    }

}