<?php

require_once "Botones.php";
require_once "VistaJson.php";
require_once "VistaXML.php";

$Jsonboton = '{
    "material": "lino",
    "color": "blanco",
    "ojales": "6"
}';

$objboton = json_decode($Jsonboton);
$boton = new Botones();
//$boton-> crear($objboton);
$respuesta = $boton->crear($objboton);

/*
$respuestaJSon = new VistaJson();
$respuestaJSon->imprimir($respuesta);

*/
/*
{
 "estado": 400
 "datos": "creacion"

}
*/
$respuestaJSon = new VistaJson();
/*
$respuestaJSon = new VistaJson();
$arreglo = [ "estado"=>$respuestaJSon->estado,
            "datoa"=>$respuesta];
$respuestaJSon->imprimir($arreglo);

$respuestaXML = new VistaXML();
$arreglo = [
    "estado" => $respuestaXML->estado,
    "datoS" => $respuesta
];
*/
$formato ="";

if(isset($_GET["formato"])){
    $formato = $_GET["formato"];
}


$formato = $_GET["formato"];
if ($formato ="" || $formato == "JSON") {
    $objeto = new stdClass();
    $objboton->$estado = $respuestaJSon->estado;
    $objboton->$datos = $respuesta;
    $respuestaJSon->imprimir($objeto);
} else {


}