<?php

require_once('../../config.php');
require_once('formdesign.php');
global $DB;
global $USER;
$PAGE->set_pagelayout('admin');
$mform = new simplehtml_form();
 
if ($mform->is_cancelled()) {

	}
	else if ($fromform = $mform->get_data()) {
		
		$record = new stdClass();
		$record->id='';
		$record->name= $fromform->name;
		$record->text= $fromform->text['text'];
		$record->format= $fromform->text['format'];

//		print_object($record);
		$DB->insert_record('form', $record, true, false);
//		$mform->display(); 
		echo "Changes have been made.";
		echo $OUTPUT->header();
		
	}
	else {
		echo $OUTPUT->header();
		$result = $_SESSION['admission'];
		print_object($result);
//		print_object($USER->id);
//		$mform->set_data(array ($result=>name,$result=>text) );
		$mform->set_data($result);
//		$mform-> getElement ('text') -> SetValue (array ('text' => $result[text]));
		$mform->display();
		echo $OUTPUT->footer();
		
	}
?>