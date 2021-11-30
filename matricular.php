<?php

class matricular {

  /**
  * Método responsável de verificar se o Curso existe no Moodle, em caso de positivo, o segundo passo e verificar se existe o cadastro do Aluno no Moodle, caso seja inesxistente
  * o Aluno será automaticamente cadastrado e inscrito no Curso.
  *
  * @param  array  $json_str - Informações do Aluno e Curso
  * @return array  Informações do Aluno e do Curso no Moodle.
  */

   public function registerUserCourseDRM($json_str,$url,$wstoken) {

    //Recupera o ID do curso
    $course_id = json_decode($this->getCourse($json_str['shortname'], $url, $wstoken), true);

    if($course_id['dados']){

          //Recupera o ID do Aluno já Cadastrado
          $user_id = json_decode($this->getUser($json_str['username'], $url, $wstoken), true);

          if (!$user_id['dados']){

              //Recupera o ID do Novo Aluno Cadastrado
              $user_id_new = json_decode($this->createUser($json_str, $url, $wstoken), true);

              if ($user_id_new['Status'] == "true") {
                  $user_id = json_decode($this->getUser($json_str['username'], $url, $wstoken), true);
              }else{
                  return json_encode(["result" => $user_id_new['result']]);
              }
          }
          //Inscreve o Aluno no Curso
          if ($user_id['dados']['id']){
              $user_course = json_decode($this->registerUserCourse($user_id['dados']['id'], $course_id['dados']['id'], $url, $wstoken), true);
              if ($user_course['Status'] == "true"){
                   return json_encode(["result" => "Usuário ". $user_id['dados']['username']. " cadastrado no curso " . $course_id['dados']['shortname'] . " com sucesso!"]);

              }else{
                  json_encode($user_course);
              }
          }
    }else{
      return json_encode($course_id);
    }
  }


/**
* Método responsável de busca do Curso no Moodle através do campo chave (short_name)
*
* @param string $short_name - Nome breve do Curso.
* @return array Informações do Curso no Moodle.
*/

  function getCourse($short_name = null, $url, $wstoken){

            $param=array();
            $param['wstoken']=$wstoken;
            $param['wsfunction']="core_course_get_courses_by_field";

            $param['field'] = 'shortname';
            $param['value'] = $short_name;

            $paramjson = json_encode($param);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $paramjson);

            $headers = apache_request_headers();
            $token = $this->getToken($headers['Authorization']);
            $authorization = "Authorization: Bearer ".$token;

            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $result = curl_exec($ch);
            curl_close($ch);

            $resp = json_decode($result, true);

            if (!$resp['courses'][0]['id']){
                 return json_encode(["result" => "Curso não existe"]);
            }
              else{
                 return json_encode(["result" => "Curso existe", "dados" => $resp['courses'][0]]);
            }

  }

  /**
  * Método responsável de busca o Aluno no Moodle através do campo chave (user_name)
  *
  * @param  string $user_name - Login do Aluno  no Moodle.
  * @return array Informações do Aluno no Moodle.
  */

   function getUser($user_name = null, $url, $wstoken){

     $param=array();
     $param['wstoken']= $wstoken;
     $param['wsfunction']="core_user_get_users";

     $param['criteria'][0]['key']= 'username';
     $param['criteria'][0]['value']= $user_name;

     $paramjson = json_encode($param);

     $ch = curl_init();
     curl_setopt($ch, CURLOPT_URL, $url);
     curl_setopt($ch, CURLOPT_POST, 0);
     curl_setopt($ch, CURLOPT_POSTFIELDS, $paramjson);
     curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
     $result = curl_exec($ch);
     $resp = json_decode($result, true);

     if (!$resp['users'][0]['id']){
        return json_encode(["result" => "Usuário não existe"]);
     }
     else{
        return json_encode(["result" => "Usuário existe", "dados" => $resp['users'][0]]);
     }
   }

   /**
   * Método responsável de busca o Token no Cabeçalho da solicitação
   *
   * @param string $user_name - Login no Moodle.
   * @return array Informações do Usuário no Moodle.
   */

   function getToken($headers = null) {
     $token = null;
     if (!empty($headers)) {
           if (preg_match('/Bearer\s(\S+)/', $headers, $token)) {
               return $token[1];
           }
       }
       return null;
   }

   /**
   * Método responsável de Inscrever o Aluno no Curso do Moodle
   *
   * @param string $user_id   - Identificação (id) do Aluno no Moodle.
   * @param string $course_id - Identificação (id) do Curso no Moodle
   * @return array Informações do Usuário no Moodle.
   */

   function registerUserCourse($user_id, $course_id, $url, $wstoken){

       $param=array();
       $param['wstoken']= $wstoken;
       $param['wsfunction']="enrol_manual_enrol_users";

       $param['enrolments'][0]['roleid']= 5; //Perfil aluno
       $param['enrolments'][0]['userid']= $user_id;
       $param['enrolments'][0]['courseid']= $course_id;
       $param['enrolments'][0]['timestart']= 1579631944; //data de início de inscrição em formato numérico
       $param['enrolments'][0]['timeend']= 1584815944; //data de fim de inscrição em formato numérico
       $param['enrolments'][0]['suspend']= 0;

       $paramjson = json_encode($param);
       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, $url);
       curl_setopt($ch, CURLOPT_POST, 0);
       curl_setopt($ch, CURLOPT_POSTFIELDS, $paramjson);
       curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       $result = curl_exec($ch);
       $resp =json_decode($result);

       if ($resp['debuginfo'] != "") {
          return json_encode(["result" => "error: ". $resp['debuginfo'], "Status" => "false"]);
       }
         return json_encode(["result" => "Usuário cadastrado no Curso", "Status" => "true"]);

   }

   /**
   * Método responsável de cadatstar o Aluno no Moodle
   *
   * @param  array $data - Informações do Aluno.
   * @return array Informações do Usuário no Moodle.
   */

   function createUser($data, $url, $wstoken){

     if($data != ""){

       $functionname = 'core_user_create_users';
       $token = $wstoken;
       $url = $url . '/webservice/rest/server.php?wstoken=' . $token . '&wsfunction='.$functionname."&moodlewsrestformat=json";

       unset($data['shortname']);
       $moodledata['users'][] = $data;

       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, $url);
       curl_setopt($ch, CURLOPT_POST, true);
       curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($moodledata));
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        $resp = json_decode($result, true);

        if ($resp['debuginfo'] != "") {
           return json_encode(["result" => "error: ". $resp['debuginfo'], "Status" => "false"]);
        }
         return json_encode(["result" => "Usuário Cadastrado com Sucesso", "Status" => "true"]);
     }
   }
}
