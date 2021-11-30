<?php
require_once("../../../config.php");
require_once("util.php");
/**
* Classe - Moodle
*/
trait moodleAPI {
  use util;
  /**
  * Método responsável de Buscar a Relação de Disciplinas relacionadas ao Curso de um Cliente Externo
  *
  * @param  int $short_name - Identificação do Curso do Moodle (Identificador único entre as Plataformas Moodle e o Cliente).
  * @return array Identificação das Disciplinas no Moodle.
  */
  function buscarCursoExterno($short_name)
  {
    try {
      global $DB;
      $curso_interno = $DB->get_recordset('ucb_externo_interno', array('cod_externo'=>$short_name));
      if ($curso_interno->valid()) {
        foreach ($curso_interno as $course) {
          $course_int[] = $course->cod_interno;
        }
        $curso_interno->close();
        return json_encode([
          "status" => true,
          "mensagem" => "Curso " .$short_name. " encontrado no Moodle",
          "resultado" => $course_int,
        ]);
      }
      else{
        $curso_interno->close();
        return json_encode([
          "status" => false,
          "mensagem" => "O Curso " .$short_name. " nao tem vinculo com o Moodle",
          "resultado" => null
        ]);
      }
    } catch(Exception $e) {
      echo 'Exceção',  $e->getMessage(), "\n";
    }
  }
  function registraAutenticacao($user_id)
  {
    try {
      global $DB;
      $chave = $this->setToken();
      $validade = date('Y-m-d H:i:s', strtotime('+1 day', time()));
      $obj = (object)array('userid' => $user_id, 'chave' => $chave, 'validade' => $validade);
      $acesso = $DB->insert_record('ucb_externo_acesso', $obj);
      return json_encode([
        "status" => true,
        "mensagem" => "Token de autenticação do aluno no Moodle",
        "resultado" => $chave
      ]);
    } catch(Exception $e) {
      echo 'Exceção',  $e->getMessage(), "\n";
    }
  }

  function consultaAutenticacao($token)
  {
    try {
      global $DB;
      $ucb_externo_acesso = $DB->get_recordset('ucb_externo_acesso', array('chave'=>$token));
      if ($ucb_externo_acesso->valid()) {
        foreach ($ucb_externo_acesso as $value) {
          $userid = $value->userid;
          $validade = strtotime($value->validade);
        }
        //Apaga o token
        $DB->delete_records('ucb_externo_acesso', array('chave'=>$token));
        // Veifica validade token
        if (strtotime(date("Y-m-d")) > $validade){
          return json_encode([
            "status" => false,
            "mensagem" => 'Aluno não autenticado!',
            "resultado" => null
          ]);
        }
        else{
          $this->redirecionar($userid);
        }
        $ucb_externo_acesso->close();
      }else{
        $ucb_externo_acesso->close();
        return json_encode([
          "status" => false,
          "mensagem" => 'Aluno não autenticado!',
          "resultado" => null
        ]);
      }
    } catch(Exception $e) {
      echo 'Exceção',  $e->getMessage(), "\n";
    }
  }
  /**
  * Método responsável de Autenticar(logar) o Aluno no Moodle
  *
  * @param  int $user_id - Identificação do Alunono Moodle.
  */
  function logar($user_id)
  {
    try {
      global $DB, $CFG;
      $user = $DB->get_record('user', array('id'=>$user_id));
      $r=complete_user_login($user);
      if($r){
        return json_encode([
          "status" => true,
          "mensagem" => "Usuário logado!",
          "resultado" => $this->getToken(),
          "url" => $CFG->httpswwwroot . '/index.php'
        ]);
      }
      return json_encode([
        "status" => false,
        "mensagem" => 'Usuário inválido!',
        "resultado" => null
      ]);
      //redirect($CFG->httpswwwroot.'/index.php');
    } catch(Exception $e) {
      return json_encode([
        "status" => false,
        "mensagem" => $e->getMessage(),
        "resultado" => null
      ]);
    }
  }
  /**
  * Método responsável de Autenticar(logar) o Aluno no Moodle
  *
  * @param  int $user_id - Identificação do Alunono Moodle.
  */
  function redirecionar($user_id)
  {
    try {
      global $DB, $CFG;
      $user = $DB->get_record('user', array('id'=>$user_id));
      $r=complete_user_login($user);
      redirect($CFG->httpswwwroot.'/index.php');
    } catch(Exception $e) {
      return json_encode([
        "status" => false,
        "mensagem" => $e->getMessage(),
        "resultado" => null
      ]);
    }
  }
  /**
  * Método responsável de busca o Aluno no Moodle através do campo chave (user_name)
  *
  * @param  string $user_name - Login do Aluno  no Moodle.
  * @return array Informações do Aluno no Moodle.
  */
  function buscarUsuario($login){
    try {
      $headers = apache_request_headers();
      $wstoken = $this->getToken($headers['Authorization']);
      $param=array();
      $param['wstoken']= $wstoken;
      $param['wsfunction']="core_user_get_users";
      $param['criteria'][0]['key']= 'username';
      $param['criteria'][0]['value']= $login;
      $paramjson = json_encode($param);
      $result = $this->curl($paramjson,$wstoken);
      $resp = json_decode($result, true);
      if (!$resp['users'][0]['id']){
        return json_encode([
          "status" => false,
          "mensagem" => "Usuário " .$login. " não encontrado no Moodle",
          "resultado" => null
        ]);
      }
      else{
        return json_encode([
          "status" => true,
          "mensagem" => "Usuário " .$login. " encontrado no Moodle",
          "resultado" => $resp['users'][0]
        ]);
      }
    }catch(Exception $e) {
      echo 'Exceção',  $e->getMessage(), "\n";
    }
  }
  /**
  * Método responsável de Criar o Aluno no Moodle
  *
  * @param  array $data - Dados de Cadastro do Aluno.
  * @return array Informações do Aluno no Cadastrado no Moodle.
  */
  function criarUsuario($data = null){
    try{
      global $DB;
      $existEmail = $DB->record_exists('user', array('email'=>$data['email']));
      if($existEmail)
        return json_encode([
          "status" => "false",
          "mensagem" => "E-mail já existe!",
          "resultado" => null
        ]);
      $newuser=  new stdClass();
      $newuser->id='';
      $newuser->auth='manual';
      $newuser->confirmed=1;
      $newuser->mnethostid=1;
      $newuser->username=$data['username'];
      $newuser->password=hash_internal_user_password($data['password']);
      $newuser->firstname=$data['firstname'];
      $newuser->lastname=$data['lastname'];
      $newuser->email=$data['email'];
      $newuser->timecreated=time();
      $newuser->id = $DB->insert_record('user', $newuser);
      return json_encode([
        "status" => true,
        "mensagem" => "Cadastro efetuado com sucesso!",
        "resultado" => $newuser->id
      ]);
    }catch(Exception $e) {
      return json_encode([
        "status" => false,
        "mensagem" => $e->getMessage(),
        "resultado" => null
      ]);
      //echo 'Exceção',  $e->getMessage(), "\n";
    }
  }
  /**
  * Método responsável de inserir o Aluno na Disciplina no Moodle
  *
  * @param  array $usuario_id - Identificação do Aluno.
  * @param  array $curso_id - Disciplinas a inserir o Aluno.
  * @return array Informações do Aluno no Cadastrado no Moodle.
  */
  function inserirUsuarioCurso($usuario_id, $curso_id) {
    try {

      $cursos_inscrito = json_decode($this->consultaUsuarioCurso($usuario_id),true);
      $novo_curso = $this-> ary_diff($cursos_inscrito['resultado'], $curso_id);

      if (!empty($novo_curso)){
        $headers = apache_request_headers();
        $wstoken = $this->getToken($headers['Authorization']);
        $param=array();
        $param['wstoken']= $wstoken;
        $param['wsfunction']="enrol_manual_enrol_users";
        $enrol=array();
        foreach ($curso_id as $i => $curso) {
          $enrol['roleid'] = 5;
          $enrol['userid'] = $usuario_id;
          $enrol['courseid'] = intval($curso);
          $enrol['timestart'] = time();
          $param['enrolments'][$i] = $enrol;
        }
        $paramjson = json_encode($param);
        $result = $this->curl($paramjson,$wstoken);
        $resp = json_decode($result, true);
        if ($resp['debuginfo'] != "") {
          return json_encode([
            "status" => false,
            "mensagem" => "Erro ao cadastrar o aluno no curso!",
            "resultado" => $resp['debuginfo']
          ]);
        }
     }
      return json_encode([
        "status" => true,
        "mensagem" => "Aluno inscrito no curso com sucesso!",
        "resultado" => $usuario_id
      ]);
    }catch(Exception $e) {
      echo 'Exceção',  $e->getMessage(), "\n";
    }
  }

function consultaUsuarioCurso($usuario_id) {
    try {
      $headers = apache_request_headers();
      $wstoken = $this->getToken($headers['Authorization']);
      $param=array();
      $param['wstoken'] = $wstoken;
      $param['wsfunction'] = "core_enrol_get_users_courses";
      $param['userid'] = $usuario_id;
      $paramjson = json_encode($param);
      $result = $this->curl($paramjson,$wstoken);
      $resposta = json_decode($result, true);
        if ($resposta['debuginfo'] != "") {
        return json_encode([
          "status" => false,
          "mensagem" => "Erro ao cadastrar o aluno no curso!",
          "resultado" => $resposta['debuginfo']
        ]);
      }
      foreach($resposta as $resp) {
          $cursos[] = $resp['id'];
      }
      return json_encode([
        "status" => true,
        "mensagem" => "Aluno inscrito no curso com sucesso!",
        "resultado" => $cursos
      ]);
    }catch(Exception $e) {
      echo 'Exceção',  $e->getMessage(), "\n";
    }
  }
}
