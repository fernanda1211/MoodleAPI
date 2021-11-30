<?php
/**
* API - DRM/Moodle
* @author Fernanda Duarte
*/
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 86400"); // cache 1 dia

/**
 * Configuração
 */
require_once("../classes/drm.php");
$drm = new drm();
$headers  = apache_request_headers();
$wstoken  = $headers['Authorization'];
$stHeader = false;
$method   = $_SERVER["REQUEST_METHOD"];
$params   = json_decode(file_get_contents('php://input'),true);
$methods = ['POST','GET']; // 'POST' e/ou 'GET'
//$methods = ['GET']; // 'POST' e/ou 'GET'

if(isset($_POST) && $_POST){
    $params = $_POST;
}elseif(isset($_GET) && $_GET){
    $params = $_GET;
}

/**
 * Configuração
 */
if(!in_array($method, $methods)){
  echo json_encode([
    "status" => false,
    "mensagem" => "Método Inválido!",
    "resultado" => null
  ]);
  die();
}

if($st_header && empty($wstoken)){
  echo json_encode([
    "status" => false,
    "mensagem" => "Token de acesso não foi informado!",
    "resultado" => null
  ]);
  die();
}

/**
 * Ação
 */
echo $drm->autenticaUsuario($params);
