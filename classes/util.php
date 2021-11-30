<?php

/**
* Classe função do Moodle
*/
trait util
{
  function curl($param = null, $token)
  {
    try {
      $url = "http://10.0.100.58/moodle/webservice/restjson/server.php";
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_POST, 0);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $param);
      $headers = apache_request_headers();
      $token = $this->getToken($headers['Authorization']);
      $authorization = "Authorization: Bearer ".$token;
      curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization));
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      $response = curl_exec($curl);
      curl_close($curl);
      return $response;
    }catch(Exception $e) {
      echo 'Exceção',  $e->getMessage(), "\n";
    }

  }

  function getToken($headers) {
    try {
      $token = null;
      if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $token)) {
          return $token[1];
        }
      }
      return $token;
    }catch(Exception $e) {
      echo 'Exceção',  $e->getMessage(), "\n";
    }
  }

  function setToken() {
    try {

      $token_acesso = sha1(uniqid(mt_rand()+time(),true));
      return $token_acesso;
    }catch(Exception $e) {
      echo 'Exceção',  $e->getMessage(), "\n";
    }
  }

  function ary_diff($ary_1,$ary_2) {
    $diff = array();

    foreach ($ary_1 as $v1) {
      $flag = 0;
      foreach ($ary_2 as $v2) {
        $flag |= ($v1 == $v2);
        if ($flag) break;
      }
      if (!$flag) array_push($diff, $v1);
    }

    foreach ($ary_2 as $v2) {
      $flag = 0;
      foreach ($ary_1 as $v1) {
        $flag |= ($v1 == $v2);
        if ($flag) break;
      }
      if (!$flag && !in_array( $v2, $diff )) array_push($diff,$v2);
    }
    return $diff;
  }


}
