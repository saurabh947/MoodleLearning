<?php
require_once('../../config.php');
//require_once('formdesign.php');
require_once('mod_form.php'); //include simplehtml_form.php
//require_login($course, true);
echo $OUTPUT->header();
//global $DB;

$mform = new mod_testform_mod_form();
 
	if ($mform->is_cancelled()) {
	}
	else if ($fromform = $mform->get_data()) {
		
/*  		print_object($fromform);	
		$record = new stdClass();		
		$record->id='';
		$record->name= $fromform->name;
		$record->description= $fromform->desc;  */
//	    $CFG=insert_record('testform_details', $record);
   		$mform->display();
	}
	else {
		$mform->display();
	}
echo $OUTPUT->footer();
//print_footer($course);
?>