<?php

require_once("../config.php");
$user = $DB->get_record('user', array('id'=>1351)); //mude o parâmetro do id do  usuário. 2 por padrão é id do admin

complete_user_login($user);
update_login_count();

  redirect($CFG->wwwroot.'/index.php');


  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

  $headers = array(
     "Accept: application/json",
     "Authorization: Bearer {token}",
  );
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  //for debug only!
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);


  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

  $resp = curl_exec($curl);
  curl_close($curl);
  var_dump($resp);



//$r = complete_user_login($user);
//redirect($CFG->wwwroot.'/index.php');

/*
require('../config.php');

$username      = "fernanda";
$signature     = optional_param('id', '', PARAM_TEXT);

$PAGE->https_required();
$user = $DB->get_record('user', array('username'=>$username, 'deleted'=>0, 'suspended'=>0));

add_to_log(SITEID, 'user', 'login', "view.php?id=$USER->id&course=".SITEID,$user->id, 0, $user->id);

complete_user_login($user);
update_login_count();

redirect($CFG->wwwroot.'/index.php');

/*


$username      = optional_param('username', 0, PARAM_INT);
$signature     = optional_param('id', '', PARAM_TEXT);

$PAGE->https_required();
  $user = $DB->get_record('user', array('username'=>$username, 'deleted'=>0, 'suspended'=>0));

    add_to_log(SITEID, 'user', 'login', "view.php?id=$USER->id&course=".SITEID,$user->id, 0, $user->id);

    complete_user_login($user);
    update_login_count();

    redirect($CFG->wwwroot.'/index.php');
