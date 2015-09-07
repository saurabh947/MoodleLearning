<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->dirroot.'/course/moodleform_mod.php');
class editcategory_form extends moodleform {

    // form definition
    function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;
        $category = $this->_customdata['category'];
        $editoroptions = $this->_customdata['editoroptions'];

        // get list of categories to use as parents, with site as the first one
        $options = array();
        if (has_capability('moodle/category:manage', get_system_context()) || $category->parent == 0) {
            $options[0] = get_string('top');
        }
        $parents = array();
        if ($category->id) {
            // Editing an existing category.
/*
//			$sql= 'SELECT * FROM {course_categories} c, {vrsspl_course_subject_info} b WHERE c.id= ? AND b.categoryid= ? AND c.batch= ?';

			$ifbatch = $DB->get_record_sql('SELECT * FROM {course_categories} WHERE id= ? AND batch= ?', array($category->id, 1), IGNORE_MISSING);
//			$ifbatch = get_record_sql('SELECT * ');

			if($ifbatch)
			{
				echo "Batch has already been started, you cannot edit settings now.";
				$mform->disabledIf('mform','ifbatch','neq','0');
			}
			else
			{

*/
            make_categories_list($options, $parents, 'moodle/category:manage', $category->id);
            if (empty($options[$category->parent])) {
                $options[$category->parent] = $DB->get_field('course_categories', 'name', array('id'=>$category->parent));
            }
		
            $strsubmit = get_string('savechanges');

//		}


		} else {
            // Making a new category
            make_categories_list($options, $parents, 'moodle/category:manage');
            $strsubmit = get_string('createcategory');
        }

        $mform->addElement('select', 'parent', get_string('parentcategory'), $options);
        $mform->addElement('text', 'name', get_string('categoryname'), array('size'=>'30'));
        $mform->addRule('name', get_string('required'), 'required', null);
        $mform->addElement('text', 'idnumber', get_string('idnumbercoursecategory'),'maxlength="100"  size="10"');
        $mform->addHelpButton('idnumber', 'idnumbercoursecategory');
        $mform->addElement('editor', 'description_editor', get_string('description'), null, $editoroptions);
        $mform->setType('description_editor', PARAM_RAW);
        if (!empty($CFG->allowcategorythemes)) {
            $themes = array(''=>get_string('forceno'));
            $allthemes = get_list_of_themes();
            foreach ($allthemes as $key=>$theme) {
                if (empty($theme->hidefromselector)) {
                    $themes[$key] = get_string('pluginname', 'theme_'.$theme->name);
                }
            }
            $mform->addElement('select', 'theme', get_string('forcetheme'), $themes);
        }
		
		$meta=array();
        $meta[0] = get_string('no');
        $meta[1] = get_string('yes');
		$mform->addElement('select', 'batch', 'Course Batch', $meta);
		
		$mform->addElement('date_selector', 'batchstart', 'Start Date');
		$mform->disabledIf('batchstart', 'batch', 'eq', 0);
		
		$mform->addElement('date_selector', 'batchend', 'End Date');
		$mform->disabledIf('batchend', 'batch', 'eq', 0);
		
		$mform->addElement('static', 'label1', 'No Of Subject', '<div id="coursecount" style="margin-bottom:-15px;"></div>');		
		$mform->addElement('static', 'label1', 'Subject Selection', '<div id="basic-modal" style="margin-bottom:-15px;"><a href="#" class="basic">View All</a></div>');
		
		
		
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $category->id);

        //$this->add_action_buttons(true, $strsubmit);
		
		//normally you use add_action_buttons instead of this code
$buttonarray=array();
$buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
$buttonarray[] = &$mform->createElement('button', 'startbatch', 'Start Batch', '<div id="basic-modal-button"><a href="#" class="basic"></div>');
$mform->disabledIf('startbatch', 'batch', 'eq', 0);
$buttonarray[] = &$mform->createElement('cancel');
//		$mform->disabledIf('submitbutton', 'batch', 'eq', 1);

$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
$mform->closeHeaderBefore('buttonar');
$mform->addElement('static', 'label1', '', '<div id="errormessage" style="color:#AA0000; font-size:14px;"></div>');
//$mform->addElement('html', '<div>ramesh</div>');
    }

    function validation($mform, $files) {
        global $DB;
        $errors = array();

		if($mform['batch'])
		{
		if ($mform['batchend'] <= $mform['batchstart']){
                $errors['batchend'] = 'You must provide End Date greater than Start Date!';
            }
		}
/*		if($mform['startbatch'])
		{
				
		} */

/*      if (!empty($data['idnumber'])) {
            if ($existing = $DB->get_record('course_categories', array('idnumber' => $data['idnumber']))) {
                if (!$data['id'] || $existing->id != $data['id']) {
                    $errors['idnumber']= get_string('idnumbertaken');
                }
            }
        }
		if (!empty($data['name'])) {
			if ($existing = $DB->get_record('course_categories', array('name' => $data['name']))) {
            //if ($existing = $DB->get_records_sql('SELECT * FROM mdl_course_categories WHERE parent = ? AND name = ?',array($data['parent'], $data['name']))) {
                if (!$data['name'] || $existing->name != $data['name'] ) {
                    $errors['name']= 'Batch name should be unique!';
                }
            }
        }  */

        return $errors;
    }
}

