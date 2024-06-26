<?php
define("BASE_DE_DATOS", "revistas");
define("NOMBRE_HOST", "localhost");
define("USUARIO", "root");
define("CONTRASENA", "");
class ConexionBD
{
    /**
     * Única instncia de la clase
     */
    private static $db = null;
    /**
     * Instancia de PDO
     */
    private static $pdo;
    final private function __construct()
    {
        try {
            // Crear nueva conexión PDO
            self::obtenerBD();
        } catch (PDOException $e) {
            // Manejo de excepciones
        }
    }
    /**
     * Retorna en la única instancia de la clase
     * @return ConexionBD|null
     */
    public static function obtenerInstancia()
    {
        if (self::$db === null) {
            self::$db = new self();
        }
        return self::$db;
    }

    /**
     * Crear una nueva conexión PDO basada
     * en las constantes de conexión
     * @return PDO Objeto PDO
     */
    public function obtenerBD()
    {
        if (self::$pdo == null) {
            self::$pdo = new PDO(
                'mysql:dbname=' . BASE_DE_DATOS .
                ';host=' . NOMBRE_HOST . ";",
                USUARIO,
                CONTRASENA,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
            );
            // Habilitar excepciones
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$pdo;
    }
    /**
     * Evita la clonación del objeto
     */
    final protected function __clone()
    {
    }
    function _destructor()
    {
        self::$pdo = null;
    }
}