<?php

require_once('../../config.php');
require_once('formdesign1.php');
require_login();
global $DB;
global $USER;
$PAGE->set_pagelayout('admin');
$toform = new simplehtml_form();
 
if ($toform->is_cancelled()) {
	}

	else if ($fromform = $toform->get_data()) {
		
//		print_object($fromform);
		$record = new stdClass();
		$result = new stdClass();
		$record->name= $fromform->name;
//		print_object($record);
		$result = $DB->get_record('form', array ('name' => $record->name), $fields='*', $strictness=IGNORE_MISSING);
//		$result = $DB->get_record_sql('SELECT * FROM {form}', array('name' => $record->name));
//		print_object($result);
//		$toform->set_data('$mform');
//		print_object($mform);
		$_SESSION['admission'] = $result;
		redirect('fill_form.php?id='.$USER->id);
	}
	else {
		echo $OUTPUT->header();
		$toform->display();
		echo $OUTPUT->footer();
	
	}
?>