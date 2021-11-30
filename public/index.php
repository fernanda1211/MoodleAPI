<?php
/**
* API - DRM/Moodle
* @author Fernanda Duarte
*/
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 86400"); // cache 1 dia

$method               = $_SERVER["REQUEST_METHOD"];
$json_data            = file_get_contents('php://input');
$json_str             = json_decode($json_data, true);
$headers              = apache_request_headers();
$wstoken              = $headers['Authorization'];

if(empty($wstoken)){
  echo json_encode([
    "status" => false,
    "mensagem" => "Token de acesso não foi informado!",
    "resultado" => null
  ]);
  die();
}
require_once("../classes/drm.php");
$drm = new drm();

switch($method) {
  case 'POST':
  if($json_str != null){
    echo $drm->registaUsuarioCurso($json_str);
  }else{
    echo json_encode([
      "status" => false,
      "mensagem" => "Parametros Inválidos!",
      "resultado" => null
    ]);
  }
  break;
  default:
  echo json_encode([
    "status" => false,
    "mensagem" => "Método Inválido!",
    "resultado" => null
  ]);
  break;
}
