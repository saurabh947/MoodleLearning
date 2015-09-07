<?php

require_once("../../config.php");
require_once("../../lib/formslib.php");
 
class simplehtml_form extends moodleform {

    function definition() {

    	global $CFG; 
	    $toform =& $this->_form;
		$toform->addElement('header','general', 'Enter Details');

		$toform->addElement('text', 'name', 'Name');
		$this->add_action_buttons();
	
	}
	
}