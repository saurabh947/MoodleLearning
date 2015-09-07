<?php

require_once("../../config.php");
require_once("../../lib/formslib.php");
 
global $USER;

class simplehtml_form extends moodleform {

    function definition() {

		$maxbytes = 8;
    	global $CFG; 
	    $mform =& $this->_form;
		$mform->addElement('header','general', 'Please Enter Your Feedback');

//		$mform->addElement('text', 'id', 'ID', 'maxlength="3"');	
		$mform->addElement('text', 'name', 'Name', 'maxlength="20"');
		$mform->setType('name', PARAM_TEXT	);
		$mform->addRule('name', "Please enter name", 'required', null, 'client');
//		$mform->setHelpButton('name', array('admission', get_string('batchname')), true);

		$mform->addElement('text', 'type', 'Designation', 'maxlength="30"');
		$mform->setType('type', PARAM_TEXT	);
		$mform->addRule('type', "Please select your current status", 'required', null, 'client');

//		$mform->addElement('file', 'userphoto', 'Upload Photo');
//		$mform->addElement('filepicker', 'userphoto', 'Upload Photo', null,array('maxbytes' => $maxbytes, 'accepted_types' => '*'));

		$mform->addElement('textarea', 'feedback', "Feedback", 'wrap="virtual" rows="10" cols="40"');
		$mform->addRule('feedback', "Please enter your feedback", 'required', null, 'client');
					
		$buttonarray=array();
		$buttonarray[] =& $mform->createElement('submit', 'submitbutton', 'Submit Entry');
		$buttonarray[] =& $mform->createElement('submit', 'cancel', 'Cancel');
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
		$mform->closeHeaderBefore('buttonar');   
		
//		$mform->addElement('header','general', 'Additional Actions:');
//		$mform->addElement('text', 'name1', 'Enter Name', 'maxlength="20"');
//		$mform->addElement('button', 'delete', 'Delete a Record');
//		$mform->addElement('button', 'update', 'Update a Record');

	}
}