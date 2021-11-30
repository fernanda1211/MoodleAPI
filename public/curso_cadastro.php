<?php
require_once("../classes/moodleAPI.php");
use moodleAPI;
// Verificação de login
if ($CFG->forcelogin) {
    require_login();
}
echo $OUTPUT->header();

echo $OUTPUT->box_start('generalbox');
echo $OUTPUT->heading('Cursos/Disciplinas');
echo $OUTPUT->box_end();

echo $OUTPUT->box_start('generalbox');
global $DB;
$cursos = $DB->get_records('course');

$table->head = array('Lastname', 'Firstname', 'ID Number');

$table = new html_table();
    foreach ($cursos as $curso) {
      $row = array ();
      $row[] = html_writer::checkbox('newattempt', 'on', false, '', array('id' => $curso->id));
      $row[] = $curso->id;
      $row[] = $curso->fullname;
      $table->data[] = $row;

}

if (!empty($table)) {
    echo html_writer::start_tag('div', array('class'=>'no-overflow'));
    echo html_writer::table($table);
    echo html_writer::end_tag('div');
    echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);
}

echo $OUTPUT->single_button(new moodle_url('view.php', array('id'=>$CourseModuleID, 'view'=>1)), 'Vincular', 'GET', array('target'=>'_blank', 'a'=>'b'));

echo $OUTPUT->box_end();
echo $OUTPUT->footer();

/*

//$table = new html_table();
//$table->head = array('Lastname', 'Firstname', 'ID Number');
//$table->data[] = array('teste', 'teste2', 'teste3');
//$table->data[] = array('teste', 'teste2', 'teste3');

// Cabeçalho
//$heading = $site->fullname;
//$PAGE->set_heading($heading);
echo $OUTPUT->header();

// Rodapé
*/
//echo $OUTPUT->footer();
