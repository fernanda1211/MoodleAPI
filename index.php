<?php
/**
 * API - Moodle
 * @author Fernanda Duarte
 */
 header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization");
 header("Content-Type: application/json; charset=UTF-8");
 header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");
 header("Access-Control-Max-Age: 86400");
 header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

 $method               = $_SERVER["REQUEST_METHOD"];

 $caminho = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

 //$url                  = str_replace(' ', '', $caminho);
 $json_data            = file_get_contents('php://input');
 $json_str             = json_decode($json_data, true);
 // parse_str(file_get_contents('php://input'), $data);

 require_once("matricular.php");
 $matricularController = new matricular();


 $url2 = sprintf(
     "%s://%s%s",
     isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
     $_SERVER['SERVER_NAME'],
     $_SERVER['REQUEST_URI']
   );

 print_r($url2);


 die();

 require_once("env.php");
 $url = getenv('url'); //Endpoint
 $wstoken = getenv('wstoken'); //token de acesso ao webservice

 switch($method) {
   case 'POST':
      if($json_str != null){
        echo $matricularController->registerUserCourseDRM($json_str,$url);
        //header('Location:http://10.0.100.58/moodle/_extra/logar.php');
      }else{
        echo json_encode(["result" => "Parametros Inválido"]);
      }
     break;
   default:
       echo json_encode(["result" => "Inválido Request"]);
   break;
   }
