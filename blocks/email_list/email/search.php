<?php
/**
 * This page prints all search's.
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
    require_once($CFG->dirroot.'/blocks/email_list/email/lib.php');			// eMail library funcions

    // For apply ajax and javascript functions.
    require_once($CFG->libdir. '/ajax/ajaxlib.php');

    // Search lib
	include_once($CFG->libdir.'/searchlib.php');

	// Advanced search form
	include_once($CFG->dirroot.'/blocks/email_list/email/advanced_search_form.php');

    $courseid	= optional_param('courseid', SITEID, PARAM_INT); 	// Course ID
    $folderid	= optional_param('folderid', 0, PARAM_INT); 		// folder ID
	$filterid	= optional_param('filterid', 0, PARAM_INT);			// filter ID

	$page       = optional_param('page', 0, PARAM_INT);          	// which page to show
	$perpage    = optional_param('perpage', 10, PARAM_INT);  		// how many per page

	// Search words
	$search		= optional_param('words', '', PARAM_TEXT);			// Text to search
	$action		= optional_param('action', 0, PARAM_INT);		// Action


	// If defined course to view
    if (! $course = $DB->get_record('course', 'id', $courseid)) {
    	print_error('invalidcourseid', 'block_email_list');
    }

    require_login($course->id, false); // No autologin guest

    if ( !email_is_enabled_email_list( $course->id ) ) {
        print_error('blocknotavailable', 'block_email_list');
    }

    // Add log for one course
    add_to_log($courseid, 'email_list', 'search', 'view.php?id='.$courseid, 'View all mails of '.$course->shortname);


/// Print the page header

	$preferencesbutton = email_get_preferences_button($courseid);

	$stremail  = get_string('name', 'block_email_list');

	if ( $search == get_string('searchtext', 'block_email_list') or $search == '' ) {
		$strsearch = get_string('advancedsearch', 'search');
	} else {
		$strsearch = get_string('search', 'search');
	}

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
    	$navlinks[] = array('name' => $strsearch, 'link' => null, 'type' => 'misc');

		// Build navigation
		$navigation = build_navigation($navlinks);

    	$PAGE->set_heading("$course->fullname"); // Required
		$PAGE->set_title("$course->shortname: $stremail: $strsearch");
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
		$PAGE->set_title("$course->shortname: $stremail: $strsearch");
		echo $OUTPUT->header("$navigation <a href=index.php?id=$course->id>$stremails</a> -> $strsearch");
    }

	// Options for new mail and new folder
	$options = new stdClass();
	$options->id = $courseid;
	$options->folderid = $folderid;
	$options->filterid = $filterid;

/// Print the main part of the page

	// Print principal table. This have 2 columns . . .  and possibility to add right column.
	echo '<table id="layout-table">
  			<tr>';


	// Print "blocks" of this account
	echo '<td style="width: 180px;" id="left-column">';
	email_printblocks($USER->id, $courseid, ($search == get_string('searchtext', 'block_email_list') or $search == '') ? true : false);

	// Close left column
	echo '</td>';

	// Print principal column
	echo '<td id="middle-column">';

	// Print middle table
	print_heading_block($strsearch);

	echo '<div>&#160;</div>';

	// Create advanced search form
	$advancedsearch = new advanced_search_form();

	if ( ( $search == get_string('searchtext', 'block_email_list') or $search == '' ) and ( !$advancedsearch->is_submitted() ) ) {

		if ( ! $action ) {
			echo $OUTPUT->notification(get_string('emptysearch', 'block_email_list'), '');
			echo $OUTPUT->notification(get_string('wantadvancedsearch', 'block_email_list'), 'notifysuccess');
		}

		// Print advanced search form
		$advancedsearch->display();
	} else if ( $advancedsearch->is_cancelled() ) {

		// Cancelled form
		redirect($CFG->wwwroot.'/blocks/email_list/email/index.php?id='.$courseid, '', 1);

	} else if ( $data = $advancedsearch->get_data()) {

		/// Advanced Search by:
		///		- Folders
		///		- Course
		///		- From
		///		- To
		///		- Subject
		///		- Body
		///
		///		And / Or

		$select = 'SELECT m.*, u.firstname,u.lastname, m.userid as writer';

		$from	= ' FROM {user} u,
					 {block_email_list_mail} m,
					 {block_email_list_send} s,
					 {block_email_list_foldermail} fm';


		// FOLDERS
		$wherefolders = '';
		if ( ! empty( $data->folders) ) {

			if ( is_array($data->folders) ) {
				$wherefolders .= ' AND ( ';
				$i = 0;
				foreach ( $data->folders as $key => $folder ) {
					$wherefolders .= ($i > 0) ? " $data->connector fm.folderid = $key": " fm.folderid = $key "; // Select this folder
					$i++;
				}
				$wherefolders .= ' ) ';
			}
		} else {
			print_error('nosearchfolders', 'block_email_list');
		}

		$groupby = ' GROUP BY m.id, m.userid, m.course, m.subject, m.timecreated, m.body, u.firstname,u.lastname';

		// TO
		$myto = " m.userid = $USER->id AND m.userid = u.id ";
		$searchto = '';
		if ( ! empty($data->to) ) {

			$searchtext = trim($data->to);

			if ($searchtext !== '') {   // Search for a subset of remaining users
	            $LIKE      = $DB->sql_ilike();
	            $FULLNAME  = $DB->sql_fullname('usu.firstname', 'usu.lastname');

	            $searchto = " AND ($FULLNAME $LIKE '%$searchtext%') ";
			}
		}


		// FROM
		$searchfrom = '';
		if ( ! empty( $data->from ) ) {

			$searchtext = trim($data->from);

			if ($searchtext !== '') {   // Search for a subset of remaining users
	            $LIKE      = $DB->sql_ilike();
	            $FULLNAME  = $DB->sql_fullname();

	            $searchfrom = " AND ($FULLNAME $LIKE '%$searchtext%') ";
	        }
		}

		// SUBJECT
		$sqlsubject = '';
		if ( ! empty( $data->subject ) ) {

			$searchstring = str_replace( "\\\"", "\"", $data->subject);
		    $parser = new search_parser();
		    $lexer = new search_lexer($parser);

		    if ($lexer->parse($searchstring)) {
		        $parsearray = $parser->get_parsed_array();

				$sqlsubject = search_generate_text_SQL($parsearray, 'm.subject', '', 'm.userid', 'u.id',
                         'u.firstname', 'u.lastname', 'm.timecreated', '');
		    }
		}


		// BODY
		$sqlbody = '';
		if ( ! empty( $data->body ) ) {

			$searchstring = str_replace( "\\\"", "\"", $data->body);
		    $parser = new search_parser();
		    $lexer = new search_lexer($parser);

		    if ($lexer->parse($searchstring)) {
		        $parsearray = $parser->get_parsed_array();

				$sqlbody = search_generate_text_SQL($parsearray, 'm.body', '', 'm.userid', 'u.id',
                         'u.firstname', 'u.lastname', 'm.timecreated', '');

                $sqlsubjectbody = (! empty($sqlsubject) ) ? " AND ( $sqlsubject $data->connector $sqlbody ) " : ' AND '.$sqlbody;
		    }
		} else if (!empty($sqlsubject) ) {
			$sqlsubjectbody = ' AND '.$sqlsubject;
		} else {
			$sqlsubjectbody = '';
		}


		$sqlcourse = " AND s.course = m.course AND m.course = $courseid AND s.course = $courseid ";

		$sql = '';

		if ( !empty($data->to) ) {
			$sql = "SELECT  R1.*, usu.firstname,usu.lastname, R1.userid as writer FROM (";
		}

		$sql .= $select.$from. ' WHERE fm.mailid = m.id '.
					' AND m.userid = u.id '. // Allways I'm searching writer ... show Select fields
					' AND s.mailid = m.id '. // Allways searching one mail ... apply join
					$wherefolders.
					$sqlcourse.
					$sqlsubjectbody.
					$searchfrom.
					' AND ( m.userid = '.$USER->id.' OR ( s.userid = '.$USER->id.' AND s.mailid = m.id) ) '.
					$groupby;

		if ( !empty($data->to) ) {
			$sql .= " ) R1, {user} usu, {block_email_list_send} s1 " .
					"WHERE R1.id = s1.mailid AND usu.id=s1.userid AND R1.course = s1.course AND s1.type <> 'bcc' $searchto";
		}

		if (! $searchmails = $DB->get_records_sql($sql) ) {
			debugging('Empty advanced search for next SQL stament: '.$sql, DEBUG_DEVELOPER);
		}

		$advancedsearch->display();

		echo $OUTPUT->notification(get_string('searchword', 'block_email_list'), 'notifysuccess');

		// Show mails searched
		email_showmails($USER->id, '', $page, $perpage, $options, true, $searchmails );

	} else {

		// Simple search
		$select = 'SELECT m.*, u.firstname,u.lastname, m.userid as writer';

		$from	= ' FROM {user} u,
					 {block_email_list_mail} m,
					 {block_email_list_send} s,
					 {block_email_list_foldermail} fm';


		// FOLDERS
		$wherefolders = '';
		$folders = email_get_root_folders($USER->id, false);
		if ( ! empty( $folders) ) {

			$wherefolders .= ' AND ( ';
			$i = 0;
			foreach ( $folders as $folder ) {
				$wherefolders .= ($i > 0) ? " OR fm.folderid = $folder->id": " fm.folderid = $folder->id "; // Select this folder
				$i++;

				// Now, get all subfolders it
				$subfolders = email_get_subfolders($folder->id);

				// If subfolders
				if ( $subfolders ) {
					foreach ( $subfolders as $subfolder ) {
						$wherefolders .= ($i > 0) ? " OR fm.folderid = $subfolder->id": " fm.folderid = $subfolder->id "; // Select this folder
						$i++;
					}
				}
			}
			$wherefolders .= ' ) ';
		} else {
			print_error('nosearchfolders', 'block_email_list');
		}

		$groupby = ' GROUP BY m.id, m.userid, m.course, m.subject, m.timecreated, m.body, u.firstname,u.lastname';

		// TO
		$myto = " m.userid = $USER->id AND m.userid = u.id ";
		$searchto = '';
		if ( ! empty($search) ) {

			$searchtext = trim($search);

			if ($searchtext !== '') {   // Search for a subset of remaining users
	            $LIKE      = $DB->sql_ilike();
	            $FULLNAME  = $DB->sql_fullname('usu.firstname', 'usu.lastname');

	            $searchto = " AND ($FULLNAME $LIKE '%$searchtext%') ";
			}
		}


		// FROM
		$searchfrom = '';
		if ( ! empty( $search ) ) {

			$searchtext = trim($search);

			if ($searchtext !== '') {   // Search for a subset of remaining users
	            $LIKE      = $DB->sql_ilike();
	            $FULLNAME  = $DB->sql_fullname();

	            $searchfrom = " OR ($FULLNAME $LIKE '%$searchtext%') )";
	        }
		}

		// SUBJECT
		$sqlsubject = '';
		if ( ! empty( $search ) ) {

			$searchstring = str_replace( "\\\"", "\"", $search);
		    $parser = new search_parser();
		    $lexer = new search_lexer($parser);

		    if ($lexer->parse($searchstring)) {
		        $parsearray = $parser->get_parsed_array();

				$sqlsubject = search_generate_text_SQL($parsearray, 'm.subject', '', 'm.userid', 'u.id',
                         'u.firstname', 'u.lastname', 'm.timecreated', '');
		    }
		}


		// BODY
		$sqlbody = '';
		if ( ! empty( $search ) ) {

			$searchstring = str_replace( "\\\"", "\"", $search);
		    $parser = new search_parser();
		    $lexer = new search_lexer($parser);

		    if ($lexer->parse($searchstring)) {
		        $parsearray = $parser->get_parsed_array();

				$sqlbody = search_generate_text_SQL($parsearray, 'm.body', '', 'm.userid', 'u.id',
                         'u.firstname', 'u.lastname', 'm.timecreated', '');

                $sqlsubjectbody = (! empty($sqlsubject) ) ? " AND ( $sqlsubject OR $sqlbody " : ' AND '.$sqlbody;
		    }
		} else if (!empty($sqlsubject) ) {
			$sqlsubjectbody = ' AND '.$sqlsubject;
		} else {
			$sqlsubjectbody = '';
		}


		$sqlcourse = " AND s.course = m.course AND m.course = $courseid AND s.course = $courseid ";

		// README: If you can search by to, this simple search mode don't get this results, you use advanced search.
		// Only search by: Folder and ( Subject or Body or From).

		$sql = '';

		$sql .= $select.$from. ' WHERE fm.mailid = m.id '.
					' AND m.userid = u.id '. // Allways I'm searching writer ... show Select fields
					' AND s.mailid = m.id '. // Allways searching one mail ... apply join
					$wherefolders.
					$sqlcourse.
					$sqlsubjectbody.
					$searchfrom.
					' AND ( m.userid = '.$USER->id.' OR ( s.userid = '.$USER->id.' AND s.mailid = m.id) ) '.
					$groupby;

		if (! $searchmails = $DB->get_records_sql($sql) ) {
			debugging('Empty simple search for next SQL stament: '.$sql, DEBUG_DEVELOPER);
		}

		$advancedsearch->display();

		echo $OUTPUT->notification(get_string('searchword', 'block_email_list'), 'notifysuccess');

		// Show mails searched
		email_showmails($USER->id, '', $page, $perpage, $options, true, $searchmails );
	}

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
