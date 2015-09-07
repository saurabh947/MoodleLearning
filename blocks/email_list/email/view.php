<?php  // $Id: view.php,v 1.16 2010/08/31 11:32:58 tmas Exp $
/**
 * This file used for print email.
 *
 * @author Toni Mas
 * @version 1.4
 * @package email_list
 * @license The source code packaged with this file is Free Software, Copyright (C) 2009 by
 *          <toni.mas at uib dot es>.
 *          It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
 *          You can get copies of the licenses here:
 * 		                   http://www.affero.org/oagpl.html
 *          AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
 **/

    require_once( "../../../config.php" );
    require_once($CFG->dirroot.'/blocks/email_list/email/lib.php');

    // For apply ajax and javascript functions.
    require_once($CFG->libdir. '/ajax/ajaxlib.php');
    require_once($CFG->dirroot.'/blocks/email_list/email/email.class.php');

    //require_js('treemenu.js');
    //require_js('email.js');

	// SSL encription
	if ( $CFG->email_enable_ssl ) {
    	httpsrequired();
	}

    $mailid 	= required_param('id', PARAM_INT); 			// email ID
    $courseid	= optional_param('course', SITEID, PARAM_INT); 				// Course ID
    $action 	= optional_param('action', '', PARAM_ALPHANUM); 	// Action to execute
    $folderid	= optional_param('folderid', 0, PARAM_INT); 		// folder ID

	$mails 		= optional_param('mails', '', PARAM_ALPHANUM); 	// Next and previous mails
	$selectedusers = optional_param('selectedusers', '', PARAM_ALPHANUM); // User who send mail

	// If defined course to view
    if (! $course = $DB->get_record('course', array('id' => $courseid)) ) {
    	print_error('invalidcourseid', 'block_email_list');
    }

    if ($course->id == SITEID) {
        $coursecontext = get_context_instance(CONTEXT_SYSTEM);   // SYSTEM context
    } else {
        $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);   // Course context
    }

    // eMail
    $email = new eMail();
    $email->set_email($mailid);

    require_login($course->id, false); // No autologin guest

    if ( !email_is_enabled_email_list( $course->id ) ) {
        print_error('blocknotavailable', 'block_email_list');
    }

    // Add log for one course
    add_to_log($course->id, 'email_list', 'view mail', 'view.php?id='.$mailid, 'View mail '.$email->subject, 0, $USER->id);


/// Print the page header

	$preferencesbutton = email_get_preferences_button($courseid);

	$stremail  = get_string('name', 'block_email_list');
	// Add subject on information page
	$stremail .= ' :: '.$email->subject;

	$PAGE->requires->js('/blocks/email_list/email/treemenu.js');
	$PAGE->requires->js('/blocks/email_list/email/email.js');
	$PAGE->requires->css('/blocks/email_list/email/treemenu.css');
	$PAGE->requires->css('/blocks/email_list/email/tree.css');
	$PAGE->requires->css('/blocks/email_list/email/email.css');
	$PAGE->set_cacheable(true);
	$PAGE->set_focuscontrol('');
	$PAGE->set_button($preferencesbutton);

    if ( function_exists( 'build_navigation') ) {
    	// Prepare navlinks
    	$navlinks = array();
    	$navlinks[] = array('name' => get_string('nameplural', 'block_email_list'), 'link' => 'index.php?id='.$course->id, 'type' => 'misc');
    	$navlinks[] = array('name' => get_string('name', 'block_email_list'), 'link' => null, 'type' => 'misc');

		// Build navigation
		$navigation = build_navigation($navlinks);

		$PAGE->set_heading("$course->fullname"); // Required
		$PAGE->set_title("$course->shortname: $stremail");
		echo $OUTPUT->header($navigation);

    } else {
    	$navigation = '';
		if ( isset($course) ) {
	    	if ($course->category) {
	    	    $navigation = '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'">'.$course->shortname.'</a> ->';
	    	}
		}

		$stremails = get_string('nameplural', 'block_email_list');

		$PAGE->set_heading("$course->fullname"); // Required
		$PAGE->set_title("$course->shortname: $stremail");
		echo $OUTPUT->header("$navigation <a href=index.php?id=$course->id>$stremails</a> -> $stremail");

    }

	// Options
	$options = new stdClass();
	$options->id = $mailid;
	$options->mailid = $mailid;
	$options->course = $courseid;
	$options->folderid = $folderid;

	/// Prepare url's to sending
	$baseurl = email_build_url($options);


/// Print the main part of the page

	// Print principal table. This have 2 columns . . .  and possibility to add right column.
	echo '<table id="layout-table">
  			<tr>';


	// Print "blocks" of this account
	echo '<td style="width: 180px;" id="left-column">';

	// HACK for print folder links correct
	$options->id = $courseid;
	email_printblocks($USER->id, $courseid);
	$options->id = $mailid;

	// Close left column
	echo '</td>';

	// Print principal column
	echo '<td id="middle-column">';

	// Get actual folder, for show
	if (! $folder = email_get_folder($folderid) ) {
		// Default, is inbox
		$folder->name = get_string('inbox', 'block_email_list');
	}

	// Print middle table
	print_heading_block(get_string('mailbox', 'block_email_list'). ': '. $folder->name);

	echo '<div>&#160;</div>';

	unset($options->id);
	// Print tabs options
	email_print_tabs_options($courseid, $folderid, $action);

	// Print action in case . . .
	// Get user, for show this fields
	if (! $user = $DB->get_record('user', array('id' => $USER->id)) ) {
		echo $OUTPUT->notification('Fail reading user', '');
	}

	// Prepare next and previous mail
	if ( $mails ) {
		$urlnextmail  = '';
		$next = email_get_nextprevmail($mailid, $mails, true);
		if ( $next ) {
			$action = (PHP_VERSION < 5) ? $options : clone($options);	// Thanks Ann
			$action->id = $next;
			$urlnextmail  = email_build_url($action);
			$urlnextmail .= '&amp;mails='. $mails;
			$urlnextmail .=  '&amp;action='.EMAIL_VIEWMAIL;
		}

		$urlpreviousmail  = '';
		$prev = email_get_nextprevmail($mailid, $mails, false);
		if ( $prev ) {
			$action = (PHP_VERSION < 5) ? $options : clone($options);	// Thanks Ann
			$action->id = $prev;
			$urlpreviousmail  = email_build_url($action);
			$urlpreviousmail .= '&amp;mails='. $mails;
			$urlpreviousmail .= '&amp;action='.EMAIL_VIEWMAIL;
		}
	}

	$email->display($courseid, $folderid, $urlpreviousmail, $urlnextmail, $baseurl, $user, has_capability('moodle/site:viewfullnames', $coursecontext));

	// Close principal column
	echo '</td>';

	// Close table
	echo '</tr> </table>';

/// Finish the page
    if ( isset( $course ) ) {
    	echo $OUTPUT->footer($course);
    } else {
    	echo $OUTPUT->footer($SITE);
    }

?>
