<?php
//moodleform is defined in formslib.php
require_once("../config.php");
require_once("../lib/formslib.php");
 
class simplehtml_form extends moodleform {
    //Add elements to form
    function definition() {
        global $CFG;
 
        $mform =& $this->_form; // Don't forget the underscore! 
			
			$mform->addElement('header','general', 'Batch Create');
			
			
           
		
			$mform->addElement('text', 'batchname', 'Batch Name', 'maxlength="100" size="25" ');
			$mform->setType('batchname', PARAM_TEXT	);   
			 $mform->addRule('batchname', "Please enter batchname", 'required', null, 'server');
			 $mform->setHelpButton('batchname', array('admission', get_string('batchname')), true);
			
		
        
		// $this->add_action_buttons();
		 $buttonarray=array();
		$buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
		$buttonarray[] =& $mform->createElement('submit', 'cancel', get_string('cancel'));
		$buttonarray[] = &$mform->createElement('reset', 'resetbutton', 'Reset');
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
		$mform->closeHeaderBefore('buttonar');
       
    }
    //Custom validation should be added here
   
}