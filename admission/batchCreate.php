<?php

require_once('../config.php');
require_once('formdesign.php'); //include simplehtml_form.php

$PAGE->set_pagelayout('admin');
$mform = new simplehtml_form();
 
//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
} else if ($fromform = $mform->get_data()) {
  //In this case you process validated data. $mform->get_data() returns data posted in form.
  
  		//print_object($fromform);
  		$mform->display();
   
} else {
  // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
  // or on the first display of the form.
  //Set default data (if any)
  
 echo $OUTPUT->header();
	$mform->display();
	echo $OUTPUT->footer();
 
  
 // $mform->set_data($toform);
  //displays the form
  
}



?>