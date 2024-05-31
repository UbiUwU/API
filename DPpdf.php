<?php
require_once "ConexionBD.php";
require_once "ExceptionApi.php";
require_once "fpdf.php";

class PDF extends FPDF
{

    const NOMBRE_TABLA = "detallespedidos";
    const DETALLEPEDIDOID = "DetallePedidoID";
    const NOMBRE_TABLA2 = "pedidos";
    const PEIDIDOID = "PedidoID";
    
    const FECHAPEDIDO = "FechaPedido";
    const ESTADO = "Estado";
    const MONTOTOTAL = "MontoTotal";
    const NOMBRE_TABLA3 = "revistas";
    const REVISTAID = "RevistaID";
    const TITULO = "Título";
    const DESCRIPCION = "Descripción";
    const NOMBRE_TABLA4 = "editoriales";
    const EDITORIALID = "EditorialID";
    const NOMBREEDITORIAL = "NombreEditorial";
    const FECHAPUBLICACION = "FechaPublicación";
    const PRECIO = "Precio";
    const NOMBRE_TABLA5 = "categoriasrevistas";
    const CATEGORIAID = "CategoriaID";
    const NOMBRECATEGORIA = "NombreCategoria";
    const PROVEEDORID = "ProveedorID";
    const CANTIDAD = "Cantidad";
    const PRECIOUNITARIO = "PrecioUnitario";
    const NOMBRE_TABLO = "clientes";
    const CLIENTEID = "ClienteID";
    const NOMBRE = "Nombre";
    const APELLIDO = "Apellido";
    const CORREOELECTRONICO = "CorreoElectrónico";
    const TELEFONO = "Teléfono";
    const CIUDAD = "Ciudad";
    const ESTADOCLI = "Estado";
    const TOKEN = "token";
    
    public $nombreCliente;

    function Header()
    {
        $this->SetFont('Arial','B',20);
        $this->Cell(0,10,'Reportes de Usuario'.$this->nombreCliente,0,1,'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

function autorizar() {
    if (isset($_GET["token"])) {
        $claveApi = $_GET["token"];

        if (validarClaveApi($claveApi)) {
            return obtenerIdCliente($claveApi);
        } else {
            throw new ExcepcionApi(401, "Token no autorizado", "Token no autorizado");
        }
    } else {
        throw new ExcepcionApi(400, "Se requiere Token para autenticación", "Se requiere Token para autenticación");
    }
}

function validarClaveApi($claveApi) {
    $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
    $comando = "SELECT COUNT(" . PDF::CLIENTEID . ") FROM " . PDF::NOMBRE_TABLO . " WHERE " . PDF::TOKEN . " = ?";
    $sentencia = $pdo->prepare($comando);
    $sentencia->bindParam(1, $claveApi);
    $sentencia->execute();

    return $sentencia->fetchColumn(0) > 0;
}

function obtenerIdCliente($claveApi) {
    $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
    $comando = "SELECT " . PDF::CLIENTEID . " FROM " . PDF::NOMBRE_TABLO . " WHERE " . PDF::TOKEN . " = ?";
    $sentencia = $pdo->prepare($comando);
    $sentencia->bindParam(1, $claveApi);

    if ($sentencia->execute()) {
        $resultado = $sentencia->fetch();
        return $resultado[PDF::CLIENTEID];
    } else {
        return null;
    }
}

try {
    if (isset($_GET['token'])) {

        $token = $_GET['token'];

        $ClienteID = autorizar();

        $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
        $comando = "SELECT " . PDF::NOMBRE . " FROM " . PDF::NOMBRE_TABLO . " WHERE " . PDF::CLIENTEID . " = ?";
        $sentencia = $pdo->prepare($comando);
        $sentencia->bindParam(1, $ClienteID);

        $nombreCliente = 'Usuario Desconocido'; // Valor por defecto

        if ($sentencia->execute()) {
            $resultado = $sentencia->fetch();
            
        }

        // Crear el PDF
        $pdf = new PDF();
         // Establecer el nombre de usuario directamente
        $pdf->AliasNbPages();
        
        

        // Obtener y mostrar los datos del reporte
        $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
        $sentencia = $pdo->prepare("SELECT " . PDF::PEIDIDOID . ", " . PDF::REVISTAID . ", " . PDF::CANTIDAD .  ", " . PDF::PRECIOUNITARIO ." FROM " . PDF::NOMBRE_TABLA);

        if ($sentencia->execute()) {

            while ($row = $sentencia->fetch(PDO::FETCH_ASSOC)) {

                $pdf->AddPage();

                $PedidoID = $row[PDF::PEIDIDOID];
                
                // Obtener detalles adicionales del pedido
                $sentencia2 = $pdo->prepare("SELECT " . PDF::CLIENTEID . ", ". PDF::FECHAPEDIDO . ", " . PDF::ESTADO . ", " . PDF::MONTOTOTAL . " FROM " . PDF::NOMBRE_TABLA2 . " WHERE " . PDF::PEIDIDOID . " = ?");
                $sentencia2->execute([$PedidoID]);
                $row2 = $sentencia2->fetch(PDO::FETCH_ASSOC);

                $RevistaID = $row[PDF::REVISTAID];
                $ClienteID = $row2[PDF::CLIENTEID];

                $sentenciaCliente = $pdo->prepare("SELECT " . PDF::NOMBRE . ", ". PDF::APELLIDO . ", " . PDF::CORREOELECTRONICO . ", " . PDF::TELEFONO . ", " . PDF::CIUDAD . ", " . PDF::ESTADOCLI . " FROM clientes WHERE " . PDF::CLIENTEID . " = ?");
                $sentenciaCliente->execute([$ClienteID]);
                $rowCliente = $sentenciaCliente->fetch(PDO::FETCH_ASSOC);

                $pdf->SetFont('Arial','B',15);

                $anchoTabla = 75;

                // Calcular la posición horizontal central
                $Centrar = ($pdf->GetPageWidth() - $anchoTabla) / 2;

                // Establecer la posición de la primera celda en la tabla
                $pdf->SetX($Centrar);
                
                $pdf->Cell(75, 10,'Reporte de Usuario: ' . $rowCliente[PDF::NOMBRE]. " " . $rowCliente[PDF::APELLIDO], 0, 1, 'C', false);

                $pdf->SetFont('Times','',12);

                $pdf->Ln(10);

                $sentencia3 = $pdo->prepare("SELECT " . PDF::TITULO . ", " . PDF::DESCRIPCION . ", " . PDF::EDITORIALID . ", " . PDF::FECHAPUBLICACION . ", " . PDF::PRECIO . ", " . PDF::CATEGORIAID . ", " . PDF::PROVEEDORID . " FROM " . PDF::NOMBRE_TABLA3 . " WHERE " . PDF::REVISTAID . " = ?");
                $sentencia3->execute([$RevistaID]);
                $row3 = $sentencia3->fetch(PDO::FETCH_ASSOC);

                $EditorialID = $row3[PDF::EDITORIALID];
                $CategoriaID = $row3[PDF::CATEGORIAID];
                $CategoriaID = $row3[PDF::CATEGORIAID];
                
                // Obtener detalles adicionales del pedido
                $sentencia4 = $pdo->prepare("SELECT " . PDF::NOMBREEDITORIAL . " FROM " . PDF::NOMBRE_TABLA4 . " WHERE " . PDF::EDITORIALID . " = ?");
                $sentencia4->execute([$EditorialID]);
                $row4 = $sentencia4->fetch(PDO::FETCH_ASSOC);

                // Obtener detalles adicionales del pedido
                $sentencia5 = $pdo->prepare("SELECT " . PDF::NOMBRECATEGORIA . " FROM " . PDF::NOMBRE_TABLA5 . " WHERE " . PDF::CATEGORIAID . " = ?");
                $sentencia5->execute([$CategoriaID]);
                $row5 = $sentencia5->fetch(PDO::FETCH_ASSOC);

                $anchoTabla = 75 * 2; // Suponiendo que cada celda tiene un ancho de 75 unidades y hay dos celdas por fila

                // Calcular la posición horizontal central
                $Centrar = ($pdf->GetPageWidth() - $anchoTabla) / 2;

                // Establecer la posición de la primera celda en la tabla
                //rowCliente
                $pdf->SetX($Centrar);

                $pdf->SetFillColor(211, 211, 211);
                $pdf->Cell(50, 10, 'Correo Electronico', 1, 0, 'C', true);
                $pdf->Cell(100, 10, $rowCliente[PDF::CORREOELECTRONICO], 1, 1, 'C', false);

                $pdf->SetX($Centrar);

                $pdf->SetFillColor(211, 211, 211);
                $pdf->Cell(50, 10, 'Teléfono', 1, 0, 'C', true);
                $pdf->Cell(100, 10, $rowCliente[PDF::TELEFONO], 1, 1, 'C', false);

                $pdf->SetX($Centrar);

                $pdf->SetFillColor(211, 211, 211);
                $pdf->Cell(50, 10, 'Ciudad', 1, 0, 'C', true);
                $pdf->Cell(100, 10, $rowCliente[PDF::CIUDAD], 1, 1, 'C', false);

                $pdf->SetX($Centrar);

                $pdf->SetFillColor(211, 211, 211);
                $pdf->Cell(50, 10, 'Estado habitado', 1, 0, 'C', true);
                $pdf->Cell(100, 10, $rowCliente[PDF::ESTADOCLI], 1, 1, 'C', false);

                $pdf->SetX($Centrar);

                $pdf->SetFillColor(211, 211, 211);
                $pdf->Cell(50, 10, 'Fecha del pedido', 1, 0, 'C', true);
                $pdf->Cell(100, 10, $row2[PDF::FECHAPEDIDO], 1, 1, 'C', false);

                $pdf->SetX($Centrar);

                $pdf->Cell(50, 10, 'Estado del pedido', 1, 0, 'C', true);
                $pdf->Cell(100, 10, $row2[PDF::ESTADO], 1, 1, 'C', false);

                $pdf->SetX($Centrar);

                $pdf->Cell(50, 10, 'Monto Total', 1, 0, 'C', true);
                $pdf->Cell(100, 10, $row2[PDF::MONTOTOTAL], 1, 1, 'C', false);

                $pdf->SetX($Centrar);

                $pdf->Cell(50, 10, 'Titulo de la revista ', 1, 0, 'C', true);
                $pdf->Cell(100, 10, $row3[PDF::TITULO], 1, 1, 'C', false);

                $pdf->SetX($Centrar);

                $pdf->Cell(50, 10, 'Descripción ', 1, 0, 'C', true);
                $pdf->Cell(100, 10, $row3[PDF::DESCRIPCION], 1, 1, 'C', false);

                $pdf->SetX($Centrar);

                $pdf->Cell(50, 10, 'Precio de la revista', 1, 0, 'C', true);
                $pdf->Cell(100, 10, $row3[PDF::PRECIO], 1, 1, 'C', false);

                $pdf->SetX($Centrar);

                $pdf->Cell(50, 10, 'Editorial', 1, 0, 'C', true);
                $pdf->Cell(100, 10, $row4[PDF::NOMBREEDITORIAL], 1, 1, 'C', false);

                $pdf->SetX($Centrar);

                $pdf->Cell(50, 10, 'Categoria de la revista', 1, 0, 'C', true);
                $pdf->Cell(100, 10, $row5[PDF::NOMBRECATEGORIA], 1, 1, 'C', false);

                $pdf->SetX($Centrar);

                $pdf->Cell(50, 10, 'ID Revista: ', 1, 0, 'C', true);
                $pdf->Cell(100, 10, $row[PDF::REVISTAID], 1, 1, 'C', false);

                $pdf->SetX($Centrar);

                $pdf->Cell(50, 10, 'Cantidad', 1, 0, 'C', true);
                $pdf->Cell(100, 10, $row[PDF::CANTIDAD], 1, 1, 'C', false);

                $pdf->SetX($Centrar);

                $pdf->Cell(50, 10, 'Precio Unitario', 1, 0, 'C', true);
                $pdf->Cell(100, 10, $row[PDF::PRECIOUNITARIO], 1, 1, 'C', false);

                $pdf->Ln(10);
                
            }
        } else {
            // Manejo de error en la consulta
            $pdf->Cell(0,10,'Error al obtener los datos de la base de datos.',0,1);
        }

        // Enviar encabezados para que el navegador interprete el contenido como un PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="reporte.pdf"');

        // Mostrar el PDF en el navegador
        $pdf->Output('D', 'reporte.pdf');
    } else {
        echo 'ID de reporte o token no especificados.';
    }
} catch (PDOException $e) {
    echo 'Error de conexión a la base de datos: ' . $e->getMessage();
} catch (ExcepcionApi $e) {
    echo $e->getMessage();
}
?>
