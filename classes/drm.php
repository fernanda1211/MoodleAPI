<?php
require_once("moodleAPI.php");
class drm {
  use moodleAPI;
  /**
  * Método responsável de verificar se o Curso DRM tem relação com Moodle, em caso de positivo, o segundo passo e verificar se existe o cadastro do Aluno no Moodle, caso seja inesxistente
  * o Aluno será automaticamente cadastrado e inscrito no Curso.
  *
  * @param  array  $json_str - Informações do Aluno e Curso
  * @return array  Informações do Aluno e do Curso no Moodle.
  */
  public function registaUsuarioCurso($json_str) {
    try {
      //Verifica se o curso DRM existe e recupera as ID das disciplinas
      $curso_interno = $this->buscarCursoExterno($json_str['curso']);
      $curso = json_decode($curso_interno, true);

      if ($curso['status'] == true) {
        //Recupera os dados do Aluno
        $user = json_decode($this->buscarUsuario($json_str['username']), true);
        if (!$user['resultado']['id']){
          //Recupera o ID do novo Aluno
          $user_id_new = json_decode($this->criarUsuario($json_str), true);
        //  if ($user_id_new['status'] == "false"){
          //  return json_encode([
          //    "status" => false,
          //    "mensagem" => $user_id_new['mensagem'],
          //    "resultado" => null
        //    ]);
        //  }
          $user_id = $user_id_new['resultado'];
        }else{
          $user_id = $user['resultado']['id'];
        }
        //Inseri o Aluno no Curso
        $user_course = $this->inserirUsuarioCurso($user_id, $curso['resultado']);
        $user_course_dados = json_decode($user_course, true);
        if ($user_course_dados['status'] == true) {
          //Registra Autenticação do Aluno no Moodle
          $user_acesso = $this->registraAutenticacao($user_course_dados['resultado']);
          return $user_acesso;
        }else{
          return $user_course;
        }
      }else{
        return $curso_interno;
      }
    }catch(Exception $e) {
      echo 'Exceção',  $e->getMessage(), "\n";
    }
  }

  public function autenticaUsuario($json_str){
    $user_acesso = $this->consultaAutenticacao($json_str['token']);
    return $user_acesso;

  }





}
