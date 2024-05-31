<?php
//Kevin Jesus Yam Sanchez
//Angel Alberto Castellanos Sulub
require_once "ConexionBD.php";
require_once "ExceptionApi.php";


class detallespedidos
{
    // Datos de la tabla "usuario"
    const NOMBRE_TABLA = "detallespedidos";
    const DETALLEPEDIDOID = "DetallePedidoID";
    const PEIDIDOID = "PedidoID";
    const REVISTAID = "RevistaID";
    const CANTIDAD = "Cantidad";
    const PRECIOUNITARIO = "PrecioUnitario";
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
    const MENSAJE_FALLA_DELETE = "Error al intentar eliminar los detalles";
    const MENSAJE_FALLA_PUT = "Error al intentar modificar los detalles";
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
                    return self::obtenerDetallesPedidosRango($inicio, $fin);
                } else {
                    // Si el inicio es mayor que el fin, devolver un mensaje de error
                    return self::ESTADO_CREACION_FALLIDA;
                }
            } else {
                // Si no hay exactamente dos parámetros, intentar obtener un botón por su ID
                $DetallePedidoID = $peticion[0];
                return self::obtenerDetallesPedido($DetallePedidoID);
            }
        } else {
            // Si no hay parámetros en la solicitud, devolver todos los botones
            return self::obtenerDetallesPedidos();
        }
    }


    private static function obtenerDetallesPedidosRango($inicio, $fin)
    {
        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

            // Consulta SQL para obtener los botones en el rango especificado
            $comando = "SELECT * FROM " . self::NOMBRE_TABLA . " WHERE " . self::DETALLEPEDIDOID . " BETWEEN ? AND ?";

            $sentencia = $pdo->prepare($comando);
            $sentencia->bindParam(1, $inicio, PDO::PARAM_INT);
            $sentencia->bindParam(2, $fin, PDO::PARAM_INT);
            $sentencia->execute();

            $detalles = $sentencia->fetchAll(PDO::FETCH_ASSOC);

            return $detalles;
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    private static function obtenerDetallesPedidos()
    {
        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

            $comando = "SELECT * FROM " . self::NOMBRE_TABLA;

            $sentencia = $pdo->prepare($comando);
            $sentencia->execute();

            $detalles = $sentencia->fetchAll(PDO::FETCH_ASSOC);

            return $detalles;
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    private static function obtenerDetallesPedido($DetallePedidoID)
    {
        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

            $comando = "SELECT * FROM " . self::NOMBRE_TABLA . " WHERE " . self::DETALLEPEDIDOID . " = ?";

            $sentencia = $pdo->prepare($comando);
            $sentencia->bindParam(1, $DetallePedidoID);
            $sentencia->execute();

            $detalles = $sentencia->fetch(PDO::FETCH_ASSOC);

            if (!$detalles) {
                throw new ExcepcionApi(self::ESTADO_NO_ENCONTRADO, "El botón con ID $DetallePedidoID no existe", 404);
            }

            return $detalles;
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
            $datosDetallesPedido = json_decode($cuerpo);
            return self::crear($datosDetallesPedido);
        } else {
            throw new ExcepcionApi(self::ESTADO_URL_INCORRECTA, "Url mal formada", 400);
        }
    }

    public static function crear($datosDetallesPedido)
    {
        $PedidoID = $datosDetallesPedido->PedidoID;
        $RevistaID = $datosDetallesPedido->RevistaID;
        $Cantidad = $datosDetallesPedido->Cantidad;
        $PrecioUnitario = $datosDetallesPedido->PrecioUnitario;


        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
            // Sentencia INSERT
            $comando = "INSERT INTO " . self::NOMBRE_TABLA . " ( " .
                self::PEIDIDOID . "," .
                self::REVISTAID . "," .
                self::CANTIDAD . "," .
                self::PRECIOUNITARIO . ")" .
                " VALUES(?,?,?,?)";

            $sentencia = $pdo->prepare($comando);

            $sentencia->bindParam(1, $PedidoID);
            $sentencia->bindParam(2, $RevistaID);
            $sentencia->bindParam(3, $Cantidad);
            $sentencia->bindParam(4, $PrecioUnitario);

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
                    return self::eliminarDetallesPedidoRango($inicio, $fin);
                } else {
                    // Si el inicio es mayor que el fin, devolver un mensaje de error
                    throw new ExcepcionApi(self::ESTADO_ERROR, "El parámetro de inicio debe ser menor o igual al parámetro de fin", 400);
                }
            } else {
                // Si no hay exactamente dos parámetros, intentar eliminar un botón por su ID
                $DetallePedidoID = $peticion[0];
                return self::eliminarDetallesPedido($DetallePedidoID);
            }
        } else {
            throw new ExcepcionApi(self::ESTADO_URL_INCORRECTA, "URL mal formada", 400);
        }
    }

    private static function eliminarDetallesPedidoRango($inicio, $fin)
    {
        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

            // Sentencia DELETE para eliminar los botones en el rango especificado
            $comando = "DELETE FROM " . self::NOMBRE_TABLA . " WHERE " . self::DETALLEPEDIDOID . " BETWEEN ? AND ?";

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


    public static function eliminarDetallesPedido($DetallePedidoID)
    {
        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

            // Sentencia DELETE
            $comando = "DELETE FROM " . self::NOMBRE_TABLA . " WHERE " . self::DETALLEPEDIDOID . " = ?";

            $sentencia = $pdo->prepare($comando);
            $sentencia->bindParam(1, $DetallePedidoID);
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
            $DetallePedidoID = $peticion[0];
            $cuerpo = file_get_contents('php://input');
            $datosDetallesPedido = json_decode($cuerpo);
            return self::modificarDetallesPedido($DetallePedidoID, $datosDetallesPedido);
        } else {
            throw new ExcepcionApi(self::ESTADO_URL_INCORRECTA, "URL mal formada", 400);
        }
    }

    public static function modificarDetallesPedido($DetallePedidoID, $datosDetallesPedido)
    {
        $PedidoID = $datosDetallesPedido->PedidoID;
        $RevistaID = $datosDetallesPedido->RevistaID;
        $Cantidad = $datosDetallesPedido->Cantidad;
        $PrecioUnitario = $datosDetallesPedido->PrecioUnitario;

        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

            // Sentencia UPDATE
            $comando = "UPDATE " . self::NOMBRE_TABLA . " SET " .
                self::PEIDIDOID . "= ?," .
                self::REVISTAID . "= ?," .
                self::CANTIDAD . "= ?," .
                self::PRECIOUNITARIO . "= ?" .
                " WHERE " . self::DETALLEPEDIDOID . " = ?";

            $sentencia = $pdo->prepare($comando);
            $sentencia->bindParam(1, $PedidoID);
            $sentencia->bindParam(2, $RevistaID);
            $sentencia->bindParam(3, $Cantidad);
            $sentencia->bindParam(4, $PrecioUnitario);
            $sentencia->bindParam(5, $DetallePedidoID);

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