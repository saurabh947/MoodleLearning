<?php

require_once("../../config.php");
require_once("../../lib/formslib.php");
 
global $USER;
class simplehtml_form extends moodleform {

    function definition() {

    	global $CFG; 
	    $mform =& $this->_form;
		$mform->addElement('header','general', 'Enter Details');

//		$mform->addElement('text', 'id', 'ID', 'maxlength="3"');	
		$mform->addElement('text', 'name', 'Name', 'maxlength="20"');
		$mform->setType('name', PARAM_TEXT	);
		$mform->addRule('name', "Please enter name", 'required', null, 'client');
//		$mform->setHelpButton('name', array('admission', get_string('batchname')), true);

		$forum_id = optional_param('forum', 0, PARAM_INT); // id of forum (from URL)
		$cm = get_coursemodule_from_instance('forum', $forum_id, $course->id);
		$context = get_context_instance(CONTEXT_MODULE, $cm->id);
		$context = context_system::instance();			
//		$this->add_intro_editor();
		$mform->addElement('editor', 'text', 'Description', null, array('context' => $context) );
		$mform->setType('text', PARAM_RAW);
		
/*		$buttonarray=array();
		$buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
		$buttonarray[] =& $mform->createElement('submit', 'cancel', get_string('cancel'));
		$buttonarray[] = &$mform->createElement('reset', 'resetbutton', 'Reset');
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
		$mform->closeHeaderBefore('buttonar');   */
		$this->add_action_buttons();
		
//		$mform->addElement('header','general', 'Additional Actions:');
//		$mform->addElement('text', 'name1', 'Enter Name', 'maxlength="20"');
//		$mform->addElement('button', 'delete', 'Delete a Record');
//		$mform->addElement('button', 'update', 'Update a Record');

	}
}