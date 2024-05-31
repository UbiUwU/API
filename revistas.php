<?php
//Kevin Jesus Yam Sanchez
//Angel Alberto Castellanos Sulub
require_once "ConexionBD.php";
require_once "ExceptionApi.php";
require_once "usuarios.php";

class revistas
{
    // Datos de la tabla "usuario"
    const NOMBRE_TABLA = "revistas";
    const REVISTAID = "RevistaID";
    const TITULO = "Título";
    const DESCRIPCION = "Descripción";
    const EDITORIALID = "EditorialID";
    const FECHAPUBLICACION = "FechaPublicación";
    const PRECIO = "Precio";
    const CATEGORIAID = "CategoriaID";
    const PROVEEDORID = "ProveedorID";
    const STOCK = "Stock";
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
    const MENSAJE_FALLA_DELETE = "Error al intentar eliminar la revista";
    const MENSAJE_FALLA_PUT = "Error al intentar modificar la revista";
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
                    // Obtener los libros en el rango especificado
                    return self::obtenerRevistasRango($inicio, $fin);
                } else {
                    // Si el inicio es mayor que el fin, devolver un mensaje de error
                    return self::ESTADO_CREACION_FALLIDA;
                }
            } else {
                // Si no hay exactamente dos parámetros, intentar obtener un libro por su ID
                $RevistaID = $peticion[0];
                return self::obtenerRevista($RevistaID);
            }
        } else {
            // Si no hay parámetros en la solicitud, devolver todos los libros
            return self::obtenerRevistas();
        }
    }


    private static function obtenerRevistasRango($inicio, $fin)
    {
        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

            // Consulta SQL para obtener los libros en el rango especificado
            $comando = "SELECT * FROM " . self::NOMBRE_TABLA . " WHERE " . self::REVISTAID . " BETWEEN ? AND ?";

            $sentencia = $pdo->prepare($comando);
            $sentencia->bindParam(1, $inicio, PDO::PARAM_INT);
            $sentencia->bindParam(2, $fin, PDO::PARAM_INT);
            $sentencia->execute();

            $revistas = $sentencia->fetchAll(PDO::FETCH_ASSOC);

            return $revistas;
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    private static function obtenerRevistas()
    {
        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

            $comando = "SELECT * FROM " . self::NOMBRE_TABLA;

            $sentencia = $pdo->prepare($comando);
            $sentencia->execute();

            $revistas = $sentencia->fetchAll(PDO::FETCH_ASSOC);

            return $revistas;
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    private static function obtenerRevista($RevistaID)
    {
        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

            $comando = "SELECT * FROM " . self::NOMBRE_TABLA . " WHERE " . self::REVISTAID . " = ?";

            $sentencia = $pdo->prepare($comando);
            $sentencia->bindParam(1, $RevistaID);
            $sentencia->execute();

            $revista = $sentencia->fetch(PDO::FETCH_ASSOC);

            if (!$revista) {
                throw new ExcepcionApi(self::ESTADO_NO_ENCONTRADO, "El libro con ID $RevistaID no existe", 404);
            }

            return $revista;
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
            $datosRevista = json_decode($cuerpo);
            return self::crear($datosRevista);
        } else {
            throw new ExcepcionApi(self::ESTADO_URL_INCORRECTA, "Url mal formada", 400);
        }
    }

    public static function crear($datosRevista)
    {
        $Título = $datosRevista->Título;
        $Descripción = $datosRevista->Descripción;
        $EditorialID = $datosRevista->EditorialID;
        $FechaPublicación = $datosRevista->FechaPublicación;
        $Precio = $datosRevista->Precio;
        $CategoriaID = $datosRevista->CategoriaID;
        $ProveedorID = $datosRevista->ProveedorID;
        $Stock = $datosRevista->Stock;

        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
            // Sentencia INSERT
            $comando = "INSERT INTO " . self::NOMBRE_TABLA . " ( " .
                self::TITULO . "," .
                self::DESCRIPCION . "," .
                self::EDITORIALID . "," .
                self::FECHAPUBLICACION . "," .
                self::PRECIO . "," .
                self::CATEGORIAID . "," .
                self::PROVEEDORID . "," .
                self::STOCK . ")" .
                " VALUES(?,?,?,?,?,?,?,?)";

            $sentencia = $pdo->prepare($comando);

            $sentencia->bindParam(1, $Título);
            $sentencia->bindParam(2, $Descripción);
            $sentencia->bindParam(3, $EditorialID);
            $sentencia->bindParam(4, $FechaPublicación);
            $sentencia->bindParam(5, $Precio);
            $sentencia->bindParam(6, $CategoriaID);
            $sentencia->bindParam(7, $ProveedorID);
            $sentencia->bindParam(8, $Stock);

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
                    // Eliminar los libros en el rango especificado
                    return self::eliminarRevistasRango($inicio, $fin);
                } else {
                    // Si el inicio es mayor que el fin, devolver un mensaje de error
                    throw new ExcepcionApi(self::ESTADO_ERROR, "El parámetro de inicio debe ser menor o igual al parámetro de fin", 400);
                }
            } else {
                // Si no hay exactamente dos parámetros, intentar eliminar un libro por su ID
                $RevistaID = $peticion[0];
                return self::eliminarRevista($RevistaID);
            }
        } else {
            throw new ExcepcionApi(self::ESTADO_URL_INCORRECTA, "URL mal formada", 400);
        }
    }

    private static function eliminarRevistasRango($inicio, $fin)
    {
        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

            // Sentencia DELETE para eliminar los botones en el rango especificado
            $comando = "DELETE FROM " . self::NOMBRE_TABLA . " WHERE " . self::REVISTAID . " BETWEEN ? AND ?";

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


    public static function eliminarRevista($RevistaID)
    {
        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

            // Sentencia DELETE
            $comando = "DELETE FROM " . self::NOMBRE_TABLA . " WHERE " . self::REVISTAID . " = ?";

            $sentencia = $pdo->prepare($comando);
            $sentencia->bindParam(1, $RevistaID);
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
            $RevistaID = $peticion[0];
            $cuerpo = file_get_contents('php://input');
            $datosRevista = json_decode($cuerpo);
            return self::modificarRevista($RevistaID, $datosRevista);
        } else {
            throw new ExcepcionApi(self::ESTADO_URL_INCORRECTA, "URL mal formada", 400);
        }
    }

    public static function modificarRevista($RevistaID, $datosRevista)
    {
        $Título = $datosRevista->Título;
        $Descripción = $datosRevista->Descripción;
        $EditorialID = $datosRevista->EditorialID;
        $FechaPublicación = $datosRevista->FechaPublicación;
        $Precio = $datosRevista->Precio;
        $CategoriaID = $datosRevista->CategoriaID;
        $ProveedorID = $datosRevista->ProveedorID;
        $Stock = $datosRevista->Stock;

        try {
            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

            // Sentencia UPDATE
            $comando = "UPDATE " . self::NOMBRE_TABLA . " SET " .
                self::TITULO . "= ?," .
                self::DESCRIPCION . "= ?," .
                self::EDITORIALID . "= ?," .
                self::FECHAPUBLICACION . "= ?," .
                self::PRECIO . "= ?," .
                self::CATEGORIAID . "= ?," .
                self::PROVEEDORID . "= ?," .
                self::STOCK . "= ?" .
                " WHERE " . self::REVISTAID . " = ?";

            $sentencia = $pdo->prepare($comando);
            $sentencia->bindParam(1, $Título);
            $sentencia->bindParam(2, $Descripción);
            $sentencia->bindParam(3, $EditorialID);
            $sentencia->bindParam(4, $FechaPublicación);
            $sentencia->bindParam(5, $Precio);
            $sentencia->bindParam(6, $CategoriaID);
            $sentencia->bindParam(7, $ProveedorID);
            $sentencia->bindParam(8, $Stock);
            $sentencia->bindParam(9, $RevistaID);

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