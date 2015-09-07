<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of testimonial
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage testimonial
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// (Replace testimonial with the name of your module and remove this line)

require_once('../../config.php');
require_once('formdesign.php');
global $DB;
global $USER;

/*$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$t  = optional_param('t', 0, PARAM_INT);  // testimonial instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('testimonial', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $testimonial  = $DB->get_record('testimonial', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($t) {
    $testimonial  = $DB->get_record('testimonial', array('id' => $t), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $testimonial->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('testimonial', $testimonial->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

add_to_log($course->id, 'testimonial', 'view', "view.php?id={$cm->id}", $testimonial->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/testimonial/view.php', array('id' => $USER->id));
$PAGE->set_title(format_string($testimonial->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);   */

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('testimonial-'.$somevar);

// Output starts here

$PAGE->set_pagelayout('admin');
$mform = new simplehtml_form();
echo $OUTPUT->header();
//echo $USER->id;

if ($mform->is_cancelled()) {
		$PAGE->redirect('');
	}
	else if ($fromform = $mform->get_data()) {
		
//		print_object($fromform);
		$record = new stdClass();
//		$record->id ='';
		$record->userid = $USER->id;
		$record->name = $fromform->name;
		$record->type = $fromform->type;
//		$content = $mform->get_file_content('userphoto');
		$record->feedback = $fromform->feedback;
		
//		$mform->display(); 
//		print_object($record);

//		$url = new moodle_url('/mod/testimonial/view.php', array('user'=>$USER->id));
//		$PAGE->set_url($url);

//		print_object($record);
		$DB->insert_record('testimonial_feed', $record, false, false);

		echo "Your record has been successfully inserted.";
	}
	else {

		$mform->display();
		// Finish the page
		echo $OUTPUT->footer();
	}
