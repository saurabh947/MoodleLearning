<?php
/**
 * Library of functions and constants for email
 *
 * @author Toni Mas
 * @version 1.0.3
 * @package email_list
 * @license The source code packaged with this file is Free Software, Copyright (C) 2009 by
 *          <toni.mas at uib dot es>.
 *          It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
 *          You can get copies of the licenses here:
 * 		                   http://www.affero.org/oagpl.html
 *          AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
 **/


/// Standard definitions
/**
 * Inbox folder
 */
define('EMAIL_INBOX', 'inbox');

/**
 * Sendbox folder
 */
define('EMAIL_SENDBOX', 'sendbox');

/**
 * Trash folder
 */
define('EMAIL_TRASH', 'trash');

/**
 * Draft folder
 */
define('EMAIL_DRAFT', 'draft');


/// Standard actions

/**
 * View mail
 */
define('EMAIL_VIEWMAIL', 'view');

/**
 * Write mail
 */
define('EMAIL_WRITEMAIL', 'write');

/**
 * Reply mail
 */
define('EMAIL_REPLY', 're');

/**
 * Forward mail
 */
define('EMAIL_FORWARD', 'fw');

/**
 * Reply all mail
 */
define('EMAIL_REPLYALL', 'reall');

/**
 * Edit Draft mail
 */
define('EMAIL_EDITDRAFT', 'edraft');



/**
 * Config PARAMS
 */

/**
 * Enable track mail on user's preference.
 */
define('EMAIL_TRACKBYMAIL', 1);

/**
 * Enable married user folder to this courses on user's preference.
 */
define('EMAIL_MARRIEDFOLDERS2COURSES', 1);

/**
 * Max number of courses who it's unread mails, have display. (on block)
 *
 * Default, no limit
 */
define('EMAIL_MAX_NUMBER_COURSES', 0);


/**
 * Color for answered mails
 *
 */
define('EMAIL_ANSWERED_COLOR', '#83CC83');

/**
 * Odd table fields
 */
define('EMAIL_TABLE_FIELD_COLOR', '#B7B7B7');





 /// DEFAULT CONFIGS

// First, drop old configs .. if exist
if (isset($CFG->email_display_course_principal)) {
    unset_config('email_display_course_principal');  // Default show principal course in blocks who containg list of courses
}

if (isset($CFG->email_number_courses_display_in_blocks_course)) {
    unset_config('email_number_courses_display_in_blocks_course');  // Default show all courses
}
// Default disable old screen for select participants to send mail DEPRECATED AND DROPPED
if (isset($CFG->email_old_select_participants)) {
    unset_config('email_old_select_participants');
}

// Second, define new configs.
if (!isset($CFG->email_trackbymail)) {
    set_config('email_trackbymail', EMAIL_TRACKBYMAIL);
}
if (!isset($CFG->email_marriedfolders2courses)) {
    set_config('email_marriedfolders2courses', EMAIL_MARRIEDFOLDERS2COURSES);
}
if (!isset($CFG->email_max_number_courses)) {
    set_config('email_max_number_courses', EMAIL_MAX_NUMBER_COURSES);
}
if (!isset($CFG->email_answered_color)) {
    set_config('email_answered_color', EMAIL_ANSWERED_COLOR);
}
if (!isset($CFG->email_table_field_color)) {
    set_config('email_table_field_color', EMAIL_TABLE_FIELD_COLOR);
}

// UIB needs define this param. This define if users show Admins on select users sent mail.
if (!isset($CFG->email_add_admins)) {
    set_config('email_add_admins', 1);
}

// Temporal enable/unable Ajax use for select users to send mail... now it's UNSTABLE!!! Only for developers
if (!isset($CFG->email_enable_ajax)) {
    set_config('email_enable_ajax', 1);
}

// SSL encription (ALL plugin has protect)
if (!isset($CFG->email_enable_ssl)) {
    set_config('email_enable_ssl', 0);
}

// Fullname or Shortname shows
if (!isset($CFG->email_display_course_fullname)) {
    set_config('email_display_course_fullname', 1);
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function email_cron() {
    return true;
}

/**
 * This function get name of file, to the path pass
 *
 * @param string $path $path
 * @return string File name
 * @todo Finish documenting this function
 */
function email_strip_attachment($path) {

	$part = explode('/', $path);

	return $part[count($part)-1];
}

/**
 * This function add copy attachments.
 *
 * @uses $CFG
 * @param $mailidsrc Mail ID source for attached files
 * @param $mailiddst Mail ID destiny for attached files
 * @param $internalmailpath Only use this parametrer in migration of internalmail
 * to email!!! Warning please! PASS ABSOLUTE PATH!!!
 * @param $email Email object
 * @return string Array of all name attachments upload
 * @todo Finish documenting this function
 */

function email_copy_attachments($dirsrc, $mailiddst, $internalmailpath=NULL, $attachment=NULL) {

    global $CFG, $OUTPUT;

	// Get directory for save this attachments
	$dirsrc = $CFG->dataroot .'/'.$dirsrc;
	$dirdst = email_file_area($mailiddst) .'/'.$attachment;

	if (! $internalmailpath) {
		// Copy this attachments
		if (! copy($dirsrc, $dirdst) ) {
			debugging( "src:$dirsrc, dst: $dirdst ");
			print_error('failcopingattachments', 'block_email_list');
		}
	} else {
		if ( file_exists($internalmailpath.'/'.$attachment) ) {
			// Copy attachments but src is internalmail path.
			if (! copy($internalmailpath.'/'.$attachment, $dirdst) ) {
				echo $OUTPUT->notification('Failed when copying attachmets in migration. src: '.$internalmailpath.'/'.$attachment.' to dst:'.$dirdst, '');
			}
		}
	}

	return true;
}

/**
 * This function remove all attachments associated
 * an one mail. First delele records of database, also
 * remove files.
 *
 * @param int $mailid Mail ID
 * @return boolean Success/Fail
 * @todo Finish documenting this function
 **/
function email_delete_attachments($mailid) {

	$result = true;

    if ( $basedir = email_file_area($mailid) ) {

		// Delete all files of mail
        if ( $files = get_directory_list($basedir) ) {

			foreach ($files as $file) {

                if (! $result = unlink("$basedir/$file") ) {
            		echo $OUTPUT->notification('Existing file '.$file.' has been deleted!', '');
                }
    		}
  		}

		// Delete directory as well, if empty
       	rmdir("$basedir");
    }

    return $result;
}

/**
 * This functions return string language of root folder (default en)
 *
 * @param string $type Type
 * @return string Name
 * @todo Finish documenting this function
 */
function email_get_root_folder_name($type) {

	if ($type == EMAIL_INBOX) {
		$name = get_string('inbox', 'block_email_list');
	} else if ($type == EMAIL_SENDBOX) {
		$name = get_string('sendbox', 'block_email_list');
	} else if ($type == EMAIL_TRASH) {
		$name = get_string('trash', 'block_email_list');
	} else if ($type == EMAIL_DRAFT) {
		$name = get_string('draft', 'block_email_list');
	} else {
		// Type is not defined
		$name = '';
	}

	return $name;
}

/**
 * This function is called recursive for print all subfolders.
 *
 * @uses $CFG
 * @param array $subfolders $subfolders to explore.
 * @param int $userid User ID
 * @param int $courseid Course ID
 * @param boolean $foredit For edit folders.
 * @param boolean $admin Admin folders
 * @todo Finish documenting this function
 */
function email_print_subfolders($subfolders, $userid, $courseid, $foredit=false, $admin=false) {

	global $CFG;

	$numbermails = 0;
    $unreaded = '';

	// String for alt of img
	$strremove = get_string('removefolder', 'block_email_list');

	echo '<ul>';

	$subrow = 0;
	foreach ( $subfolders as $subfolder ) {

		unset($numbermails);
		unset($unreaded);
		// Get number of unreaded mails
		$numbermails = email_count_unreaded_mails($userid, $courseid, $subfolder->id);
		if ( $numbermails > 0 ) {
			$unreaded = '('.$numbermails.')';
		} else {
			$unreaded = '';
		}

		echo '<li class="r'. $subrow .'">';

		// If edit folder...
		if ( $foredit ) {

			echo'<a href="'.$CFG->wwwroot.'/blocks/email_list/email/folder.php?course='.$courseid.'&amp;id='.$subfolder->id.'&amp;action='.md5('edit').'">'.$subfolder->name.'</a>';
			echo '&#160;&#160;<a href="'.$CFG->wwwroot.'/blocks/email_list/email/folder.php?course='.$courseid.'&amp;id='.$subfolder->id.'&amp;action='.md5('edit').'"><img src="'.$CFG->pixpath.'/t/edit.gif" alt="'.$strremove.'" /></a>';
			echo '&#160;&#160;<a href="'.$CFG->wwwroot.'/blocks/email_list/email/folder.php?course='.$courseid.'&amp;id='.$subfolder->id.'&amp;action='.md5('remove').'"><img src="'.$CFG->pixpath.'/t/delete.gif" alt="'.$strremove.'" /></a>';

		} else {
        	echo '<a href="'.$CFG->wwwroot.'/blocks/email_list/email/index.php?id='.$courseid.'&amp;folderid='.$subfolder->id.'">'.$subfolder->name.$unreaded.'</a>';
		}

        // Now, print all subfolders it
		$subfoldersrecursive = email_get_subfolders($subfolder->id, NULL, $admin);

		// Print recursive all this subfolders
		if ( $subfoldersrecursive ) {
			email_print_subfolders($subfoldersrecursive, $userid, $courseid, $foredit, $admin);
		}

        echo '</li>';
        $subrow = $subrow ? 0:1;
	}

	echo '</ul>';
}

/**
 * This function print block for show my folders.
 * Prints all tree
 *
 * @uses $CFG
 * @param int $userid User ID
 * @param int $courseid Course ID
 * @todo Finish documenting this function
 */
function email_print_tree_myfolders($userid, $courseid) {

	global $CFG;

	$strfolders = get_string('folders', 'block_email_list');
	$stredit 	= get_string('editfolders', 'block_email_list');
	$strcourse  = get_string('course');
	$strfolderopened = s(get_string('folderopened'));
    $strfolderclosed = s(get_string('folderclosed'));

    $spancounter = 1;

	// For title blocks
	$startdivtitle	= '<div class="title">';
	$enddivtitle    = '</div>';

	print_side_block_start($startdivtitle.$strfolders.$enddivtitle);

	// Get my folders
	if ( $folders = email_get_root_folders($userid) ) {

        $numbermails = 0;
        $unreaded = '';
		$row = 0;

		echo '<ul class="c_menu">';

		// Clean trash
		$clean = '';

		// Get courses
		foreach ($folders as $folder) {

			unset($numbermails);
			unset($unreaded);
			// Get number of unreaded mails
			if ( $numbermails = email_count_unreaded_mails($userid, $courseid, $folder->id) ) {
				$unreaded = ' ('.$numbermails.')';
			} else {
				$unreaded = '';
			}

			if ( email_isfolder_type($folder, EMAIL_TRASH) ) {
				$clean .= '&#160;&#160;<a href="'.$CFG->wwwroot.'/blocks/email_list/email/folder.php?course='.$courseid.'&amp;folderid='.$folder->id.'&amp;action=cleantrash">'.get_string('cleantrash', 'block_email_list').'</a>';
			}

			// Now, print all subfolders it
			$subfolders = email_get_subfolders($folder->id, $courseid);

			// LI
			echo '<li class="r'. $row .'">';
	        echo '<a href="'.$CFG->wwwroot.'/blocks/email_list/email/index.php?id='.$courseid.'&amp;folderid='.$folder->id.'">'.$folder->name.$unreaded.'</a>';

			// If subfolders
			if ( $subfolders ) {
				email_print_subfolders( $subfolders, $userid, $courseid );
			}
			echo '</li>';
			$row = $row ? 0:1;
		}


		echo '</ul>';

		echo '<div class="footer">'.$clean.'</div>';
		// For admin folders
		echo '<div class="footer"><a href="'.$CFG->wwwroot.'/blocks/email_list/email/folder.php?course='.$courseid.'&amp;action='.md5('admin').'"><b>'.$stredit.'</b></a></div>';

		print_side_block_end();

	}

}

/**
 * This function prints blocks.
 *
 * @uses $CGF, $USER, $OUTPUT
 * @param int $userid User ID
 * @param int $courseid Course ID
 * @param boolean $printsearchblock Print search block
 * @return NULL
 * @todo Finish documenting this function
 **/
function email_printblocks($userid, $courseid, $printsearchblock=true) {

	global $CFG, $USER, $OUTPUT;

	$strcourse  = get_string('course');
	$strcourses = get_string('mailboxs','block_email_list');
	$strsearch  = get_string('search');
	$strmail    = get_string('name', 'block_email_list');

	// For title blocks
	$startdivtitle	= '<div class="title">';
	$enddivtitle    = '</div>';

	$list = array();
	$icons = array();

	if ( $printsearchblock ) {
		// Print search block
		$form = email_get_search_form($courseid);
		print_side_block_start($startdivtitle.$strsearch.$enddivtitle);
		echo $form;
		print_side_block_end();
	}

	// Print my folders
	email_print_tree_myfolders( $userid, $courseid );

	// Remove old fields
	unset($list);
	unset($icons);

	// Get my course
	$mycourses = get_my_courses($USER->id, NULL, 'id, fullname, shortname, visible', false, $CFG->email_max_number_courses); // Limit

	$list = array();
	$icons = array();

	$number = 0;
	// Get courses
	foreach( $mycourses as $mycourse ) {

		++$number; // increment for first course

		if ( $number > $CFG->email_max_number_courses && !empty($CFG->email_max_number_courses) ) {
			continue;
		}

		$context = get_context_instance(CONTEXT_COURSE, $mycourse->id);


		//Get the number of unread mails
		$numberunreadmails = email_count_unreaded_mails($USER->id, $mycourse->id);
		$unreadmails = '';

		// Only show if has unreaded mails
		if ( $numberunreadmails > 0 ) {
			$unreadmails = '<b>('.$numberunreadmails.')</b>';
			// Define default path of icon for course
			$icon = '<img src="'.$CFG->wwwroot.'/blocks/email_list/email/images/openicon.gif" height="16" width="16" alt="'.$strcourse.'" />';
		} else {
			// Define default path of icon for course
			$icon = '<img src="'.$CFG->wwwroot.'/blocks/email_list/email/icon.gif" height="16" width="16" alt="'.$strcourse.'" />';
		}

		$linkcss = $mycourse->visible ? '' : ' class="dimmed" ';
		$coursename = $CFG->email_display_course_fullname ? $mycourse->fullname : $mycourse->shortname;

		// If I'm student
		if ( has_capability('moodle/course:viewhiddencourses', $context) ) {	// Thanks Tim
			// If course visible
			if ( $mycourse->visible ) {
				$list[] = '<a href="'.$CFG->wwwroot.'/blocks/email_list/email/index.php?id='.$mycourse->id.'" '.$linkcss.'>'.$coursename.' '. $unreadmails.'</a>';
				$icons[] = $icon;
			}
		} else {
			$list[] = '<a href="'.$CFG->wwwroot.'/blocks/email_list/email/index.php?id='.$mycourse->id.'" '.$linkcss.'>'.$coursename.' '. $unreadmails.'</a>';
			$icons[] = $icon;
		}
	}

	// Print block of my courses
	print_side_block($startdivtitle.$strcourses.$enddivtitle, '', $list, $icons);

}


/**
 * This fuctions return all subfolders with one folder (one level), if it've
 *
 * @uses $USER, $COURSE
 * @param int $folderid Folder parent
 * @param int $courseid Course ID.
 * @param boolean $admin Admin folders
 * @return array Contain all subfolders
 * @todo Finish documenting this function
 **/
function email_get_subfolders($folderid, $courseid=NULL, $admin=false) {

	global $USER, $DB;

	// Get childs for this parent
	$childs = $DB->get_records('block_email_list_subfolder', array('folderparentid' => $folderid) );

	$subfolders = array();

	// If have childs
	if ( $childs ) {

		// Save child folder in array
		foreach ( $childs as $child ) {

			if ( is_null($courseid) or !email_have_asociated_folders($USER->id) ) {
				$subfolders[] = $DB->get_record('block_email_list_folder', array('id' => $child->folderchildid) );
			} else {
				if ( $folder = $DB->get_record('block_email_list_folder', array('id' => $child->folderchildid, 'course' => $courseid)) ) {
					$subfolders[] = $folder;
				} else if ( $folder = $DB->get_record('block_email_list_folder', array('id' => $child->folderchildid, 'course' => '0')) ) {
					$subfolders[] = $folder; // Add general folder's
				}
			}
		}
	} else {
		// If no childs, return false
		return false;
	}

	// Return subfolders
	return $subfolders;
}

/**
 * This fuctions return all subfolders with one folder, if it've
 *
 * @param int $folderid Folder parent
 * @return array Contain all subfolders
 * @todo Finish documenting this function
 **/
function email_get_all_subfolders($folderid) {

	global $DB;

	// Get childs for this parent
	$childs = $DB->get_records('block_email_list_subfolder', array('folderparentid' => $folderid));

	$subfolders = array();

	// If have childs
	if ( $childs ) {

		// Save child folder in array
		foreach ( $childs as $child ) {
				$subfolders[] = $DB->get_record('block_email_list_folder', array('id' => $child->folderchildid));
				if ( $morechilds = $DB->get_records('block_email_list_subfolder', array('folderparentid' =>  $child->folderchildid)) ) {
					$childs = array_merge($childs, $morechilds);
				}
		}
	} else {
		// If no childs, return false
		return $childs;
	}

	// Return subfolders
	return $subfolders;
}

/**
 * This fuctions return the root parent folder, of that folderchild
 *
 * @param int $folderid Folder ID
 * @return Object Contain root parent folder
 * @todo Finish documenting this function
 **/
function email_get_parentfolder($folderid) {

	global $DB;

	// Get parent for this child
	$parent = $DB->get_record('block_email_list_subfolder', array('folderchildid' => $folderid));

	// If has parent
	if ( $parent ) {

		$folder = email_get_folder($parent->folderparentid);

		// While not find parent root, searching...
		while ( is_null($folder->isparenttype) ) {
			// Searching ...
			$parent = $DB->get_record('block_email_list_subfolder', array('folderchildid' => $folder->id) );
			$folder = email_get_folder($parent->folderparentid);
		}

		return $folder;

	} else {
		// If no parent, return false => FATAL ERROR!
		return false;
	}
}

/**
 * This function return form for searching emails.
 *
 * @uses $CGF
 * @param int $courseid Course Id
 * @return string HTML search form
 * @todo Finish documenting this function
 **/
function email_get_search_form($courseid){

	global $CFG;


	$inputhidden = '<input type="hidden" name="courseid" value="'.$courseid.'" />';

    $form = '<form method="post" name="searchform" action="'.$CFG->wwwroot.'/blocks/email_list/email/search.php">
					<table>
						<tr>
							<td>
								<input type="text" value="'.get_string('searchtext', 'block_email_list').'" name="words" />
							</td>
						</tr>
						<tr>
							<td align="center">' .
										$inputhidden.'
								<input type="submit" name="send" value="'.get_string('search').'" />
							</td>
						</tr>
						<tr valign="top">
							<td align="center">
								<a href="'.$CFG->wwwroot.'/blocks/email_list/email/search.php?courseid='.$courseid.'&amp;action=1">'. get_string('advancedsearch','search') .'</a>
							</td>
						</tr>
					</table>
			</form>';
	return $form;
}

/**
 * This function print formated users to send mail ( This had choosed before )
 *
 * @uses $CFG
 * @param Array $users Users to print.
 * @param boolean $nosenders No users choose (error log)
 * @todo Finish documenting this function
 */
function email_print_users_to_send($users, $nosenders=false, $options=NULL) {

	global $CFG, $DB, $OUTPUT;

	$url = '';
	if ( $options ) {
		$url = email_build_url($options);
	}


	echo '<tr valign="middle">
        <td class="legendmail">
            <b>'.get_string('for', 'block_email_list'). '
                :
            </b>
        </td>
        <td class="inputmail">';

    if ( ! empty ( $users ) ) {

    	echo '<div id="to">';

    	foreach ( $users as $userid ) {
    		echo '<input type="hidden" value="'.$userid.'" name="to[]" />';
    	}

    	echo '</div>';

    	echo '<textarea id="textareato" class="textareacontacts" name="to" cols="65" rows="3" disabled="true" multiple="multiple">';

    	foreach ( $users as $userid ) {
    		echo fullname( $DB->get_record('user', array('id' => $userid)) ).', ';
    	}

    	echo '</textarea>';
    }

  	echo '</td><td class="extrabutton">';

  	$link = html_link::make('/blocks/email_list/email/participants.php?'.$url, get_string('participants', 'block_email_list').' ...');
	$link->title = get_string('participants', 'block_email_list'); // optional
	$options['height'] = 470; // optional
	$options['width'] = 520; // optional
	$link->add_action(new popup_action('click', '/blocks/email_list/email/participants.php?'.$url, 'participants', $options));
	echo $OUTPUT->link($link);

   echo '</td></tr>';
   echo '<tr valign="middle">
   			<td class="legendmail">
   				<div id="tdcc"></div>
   			</td>
   			<td><div id="fortextareacc"></div><div id="cc"></div><div id="url">'.$urltoaddcc.'<span id="urltxt">&#160;|&#160;</span>'.$urltoaddbcc.'</div></td><td><div id="buttoncc"></div></td></tr>';
   echo '<tr valign="middle"><td class="legendmail"><div id="tdbcc"></div></td><td><div id="fortextareabcc"></div><div id="bcc"></div></td><td><div id="buttonbcc"></div></td>';


}

/**
 * This function return true or false if barn contains needle.
 *
 * @param string Needle
 * @param Array Barn
 * @return boolean True or false if barn contains needle
 * @todo Finish documenting this function
 */
function email_contains($needle, $barn) {

	// If not empty ...
	if ( ! empty ( $barn ) ) {
		// search string
		foreach ( $barn as $straw ) {
			if ( $straw == $needle ) {
				return true;
			}
		}
	}

	return false;

}

/**
 * This funcion assign default line on reply or forward mail
 *
 * @param object $user User
 * @param int $date Date on write mail
 * @return string Default line
 * @todo Finish documenting this function
 */
function email_make_default_line_replyforward($user, $date, $override=false) {

	$line = get_string('on', 'block_email_list').' '. userdate($date). ', '.fullname($user, $override).' '. get_string('wrote', 'block_email_list') . ': <br />'."\n";

	return $line;
}


/**
 * This function read folder's to one mail
 *
 * @uses $CFG;
 * @param object $mail Mail who has get folder
 * @param int $userid User ID
 * @return array Folders contains mail
 * @todo Finish documenting this function
 **/
function email_get_foldermail($mailid, $userid) {

	global $CFG, $DB;

	$params = array('mailid' => $mailid, 'userid' => $userid);
	// Prepare select
	$sql = "SELECT f.id, f.name, fm.id as foldermail
                   FROM {block_email_list_folder} f
                   INNER JOIN {block_email_list_foldermail} fm ON f.id = fm.folderid
                   WHERE fm.mailid = :mailid
                   AND f.userid = :userid
                   ORDER BY f.timecreated";

	// Return value of select
	return $DB->get_records_sql($sql, $params);
}

/**
 * This function read Id to reference mail and folder
 *
 * @param int $mailid Mail ID
 * @param int $folderid Folder ID
 * @return object Contain reference
 * @todo Finish documenting this function
 **/
function email_get_reference2foldermail($mailid, $folderid) {
	global $DB;

	return $DB->get_record('block_email_list_foldermail', array('mailid' => $mailid, 'folderid' => $folderid) );

}

/**
 * This function move mail to folder indicated.
 *
 * @param int $mailid Mail ID
 * @param int $foldermailid Folder Mail ID reference
 * @param int $folderidnew Folder ID New
 * @return boolean Success/Fail
 * @todo Finish documenting this function
 **/
function email_move2folder($mailid, $foldermailid, $folderidnew) {
	global $DB;

	if ( $DB->record_exists('block_email_list_folder', array('id' => $folderidnew)) ) {

		// Folder have exist in this new folder?
		if (! $DB->get_record('block_email_list_foldermail', array('mailid' => $mailid, 'folderid' => $folderidnew)) ) {

			// Change folder reference to mail
			$DB->set_field('block_email_list_foldermail', 'folderid', $folderidnew, 'id', $foldermailid, 'mailid', $mailid);
		} else {
			$DB->delete_records('block_email_list_foldermail', 'id', $foldermailid);
		}
	} else {
		return false;
	}

	return true;
}

/**
 * This functions print form to create a new folder
 *
 * @return boolean Success/Fail
 * @todo Finish documenting this function
 **/
function email_newfolderform() {

	include_once('folder.php');

	return true;
}

/**
 * This functions created news folders
 *
 * @param object $folder Fields of new folder
 * @param int $parentfolder Parent folder
 * @return boolean Success/Fail
 * @todo Finish documenting this function
 **/
function email_newfolder($folder, $parentfolder) {
	global $DB;

	// Add actual time
	$folder->timecreated = time();

	// Make sure course field is not null			Thanks Ann.
	if ( ! isset( $folder->course) ) {
		$folder->course = 0;
	}

	// Insert record
	$folder->id = $DB->insert_record('block_email_list_folder', $folder);

	// Prepare subfolder
	$subfolder = new stdClass();
	$subfolder->folderparentid = $parentfolder;
	$subfolder->folderchildid  = $folder->id;

	// Insert record reference
	$DB->insert_record('block_email_list_subfolder', $subfolder);

	add_to_log($folder->userid, "email_list", "add subfolder", "$folder->name");

	return true;
}

/**
 * This function get folders for one user.
 *
 * @param int $userid
 * @param string $sort Sort order
 * @return object Object contain all folders
 * @todo Finish documenting this function
 **/
function email_get_folders($userid, $sort='id') {
	global $DB;

	return $DB->get_records('block_email_list_folder', array('userid' => $userid), $sort);
}

/**
 * This function get folder.
 *
 * @param int $folderid
 * @return object Object contain folder
 * @todo Finish documenting this function
 **/
function email_get_folder($folderid) {
	global $DB;

	$folder = new object();

	if ( $folder = $DB->get_record('block_email_list_folder', array('id' => $folderid)) ) {

		if ( isset($folder->isparenttype) ) {
			// Only change in parent folders
			if ( ! is_null($folder->isparenttype) ) {
				// If is parent ... return language name
				if ( ( email_isfolder_type($folder, EMAIL_INBOX) ) ) {
					$folder->name = get_string('inbox', 'block_email_list');
				}

				if ( ( email_isfolder_type($folder, EMAIL_SENDBOX) ) ) {
					$folder->name = get_string('sendbox', 'block_email_list');
				}

				if ( ( email_isfolder_type($folder, EMAIL_TRASH) ) ) {
					$folder->name = get_string('trash', 'block_email_list');
				}

				if ( ( email_isfolder_type($folder, EMAIL_DRAFT) ) ) {
					$folder->name = get_string('draft', 'block_email_list');
				}
			}
		}
	}

	return $folder;
}

/**
 * This function created, if no exist, the initial folders
 * who are Inbox, Sendbox, Trash and Draft
 *
 * @param int $userid User ID
 * @return boolean Success/Fail If Success return object which id's
 * @todo Finish documenting this function
 **/
function email_create_parents_folders($userid) {
	global $DB;

	$folders = new stdClass();
	$folder = new stdClass();

	$folder->timecreated = time();
	$folder->userid	 = $userid;
	$folder->name		 = addslashes(get_string('inbox', 'block_email_list'));
	$folder->isparenttype = EMAIL_INBOX; // Be careful if you change this field

	/// $folders is an object who contain id's of created folders

	// Insert inbox if no exist
	if ( $DB->count_records('block_email_list_folder', array('userid' => $userid, 'isparenttype' => EMAIL_INBOX)) == 0 ) {
		$folders->inboxid = $DB->insert_record('block_email_list_folder', $folder);
	}

	// Insert draft if no exist
	$folder->name		 = addslashes(get_string('draft', 'block_email_list'));
	$folder->isparenttype = EMAIL_DRAFT; // Be careful if you change this field

	if ( $DB->count_records('block_email_list_folder', array('userid' => $userid, 'isparenttype' => EMAIL_DRAFT)) == 0 ) {
		$folders->trashid = $DB->insert_record('block_email_list_folder', $folder);
	}

	// Insert sendbox if no exits
	$folder->name		 = addslashes(get_string('sendbox', 'block_email_list'));
	$folder->isparenttype = EMAIL_SENDBOX; // Be careful if you change this field

	if ( $DB->count_records('block_email_list_folder', array('userid' => $userid, 'isparenttype' => EMAIL_SENDBOX)) == 0 ) {
		$folders->sendboxid = $DB->insert_record('block_email_list_folder', $folder);
	}

	// Insert trash if no exits
	$folder->name		 = addslashes(get_string('trash', 'block_email_list'));
	$folder->isparenttype = EMAIL_TRASH; // Be careful if you change this field

	if ( $DB->count_records('block_email_list_folder', array('userid' => $userid, 'isparenttype' => EMAIL_TRASH)) == 0) {
		$folders->trashid = $DB->insert_record('block_email_list_folder', $folder);
	}

	return $folders;
}

/**
 * This function remove one folder
 *
 * @uses $CFG
 * @param int $folderid Folder ID
 * @param object $options Options
 * @return boolean Success/Fail
 * @todo Finish documenting this function
 **/
function email_removefolder($folderid, $options) {

	global $CFG, $DB, $OUTPUT;

	// Check if this folder have subfolders
	if ( $DB->get_record('block_email_list_subfolder', array('folderparentid' => $folderid)) ) {
		// This folder is parent of other/s folders. Don't remove this
		// Notify
    	redirect( $CFG->wwwroot.'/blocks/email_list/email/view.php?id='.$options->id.'&amp;action=viewmails', '<div class="notifyproblem">'.get_string('havesubfolders', 'block_email_list').'</div>' );
	}

	// Get folder
	if ($folders =  $DB->get_records('block_email_list_folder', array('id' => $folderid)) ) {

	    // For all folders . . .
	    foreach($folders as $folder) {

			// Before removing references to foldermail, move this mails to root folder parent.
			if ($foldermails = $DB->get_records('block_email_list_foldermail', array('folderid' => $folder->id)) ) {

				// Move mails
				foreach ( $foldermails as $foldermail ) {
					// Get folder
					if ( $folder = email_get_folder($foldermail->folderid) ) {

						// Get root folder parent
						if ( $parent = email_get_parentfolder($foldermail->folderid) ) {

							// Assign mails it
							email_move2folder($foldermail->mailid, $foldermail->id, $parent->id);
						} else {
							print_error('failgetparentfolder', 'block_email_list');
						}
					} else {
						print_error('failreferencemailfolder', 'block_email_list');
					}
				}

			}

			// Delete all subfolders of this
			$DB->delete_records('block_email_list_subfolder', array('folderparentid' => $folder->id));

			// Delete all subfolders of this
			$DB->delete_records('block_email_list_subfolder', array('folderchildid' => $folder->id));

			// Delete all filters of this
			$DB->delete_records('block_email_list_filter', array('folderid' => $folder->id));

			// Delete all foldermail references
			$DB->delete_records('block_email_list_foldermail', array('folderid' => $folder->id));
	    }

	    // Delete all folders
	    $DB->delete_records('block_email_list_folder', array('id' => $folderid));
	}

	add_to_log($folderid, "email_list", "remove subfolder", "$folderid");

	echo $OUTPUT->notification(get_string('removefolderok', 'block_email_list'), '');

	return true;
}

/**
 * This function admin's folders.
 *
 * @todo Finish documenting this function
 **/
function email_print_administration_folders($options) {
	global $CFG, $USER, $DB, $OUTPUT;

	echo '<form method="post" name="folderform" action="'.$CFG->wwwroot.'/blocks/email_list/email/folder.php?id='.$options->id.'&amp;action=none">
						<table align="center"><tr><td>';

	echo $OUTPUT->heading(get_string('editfolder', 'block_email_list') );

	if ( $folders = email_get_root_folders($USER->id, false) ) {

		$course  = $DB->get_record('course', array('id' => $options->course));

		echo '<ul>';

        // Has subfolders
        $hassubfolders = false;

		// Get courses
		foreach ($folders as $folder) {
			// Trash folder is not showing
			if (! email_isfolder_type($folder, EMAIL_TRASH) ) {
				echo '<li>'.$folder->name.'</li>';

				// Now, print all subfolders it
				$subfolders = email_get_subfolders($folder->id, NULL, true);

				// If subfolders
				if ( $subfolders ) {
					email_print_subfolders($subfolders, $USER->id, $options->course, true, true);
					$hassubfolders = true;
				}
			}
		}

		echo '</ul>';
	}

	echo '</td></tr></table></form>';

	return $hassubfolders;
}

/**
 * This function return subfolder if it is.
 *
 * @param int $folderid Folder ID
 * @return object/boolean Return subfolder or false if it isn't subfolder
 * @todo Finish documenting this function
 */
function email_is_subfolder($folderid) {
	global $DB;

	return $DB->get_record('block_email_list_subfolder', array('folderchildid' => $folderid));
}

function email_createfilter($folderid) {

	notice();

	return true;
}

function email_modityfilter($filterid) {
	return true;
}

function email_removefilter($filterid) {
	return true;
}

/**
 * This function prints all mails
 *
 * @uses $CFG, $COURSE, $SESSION
 * @param int $userid User ID
 * @param string $order Order by ...
 * @param object $options Options for url
 * @param boolean $search When show mails on search
 * @param array $mailssearch Mails who has search
 * @return boolean Success/Fail
 * @todo Finish documenting this function
 **/
function email_showmails($userid, $order = '', $page=0, $perpage=10, $options=NULL, $search=false, $mailssearch=NULL) {

	global $CFG, $COURSE, $SESSION, $DB;

	// SSL encription
	if ( $CFG->email_enable_ssl ) {
    	httpsrequired();
	}

	// CONTRIB-690
	if ( ! empty( $_POST['perpage'] ) and is_numeric($_POST['perpage']) ) {
		$SESSION->email_mailsperpage = $_POST['perpage'];
	} else if (!isset($SESSION->email_mailsperpage) or empty($SESSION->email_mailsperpage) ) {
		$SESSION->email_mailsperpage = 10; // Default value
	}

	require_once('tablelib.php');
	require_once('email.class.php');

	// Get actual course
	$course = $DB->get_record('course', array('id' => $COURSE->id));

    if ($course->id == SITEID) {
        $coursecontext = get_context_instance(CONTEXT_SYSTEM);   // SYSTEM context
    } else {
        $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);   // Course context
    }

	$url = '';
	// Build url part options
 	if ($options) {
 		$url = email_build_url($options);
    }

	/// Print all mails in this HTML file

	// Should use this variable so that we don't break stuff every time a variable is added or changed.
    $baseurl = $CFG->wwwroot.'/blocks/email_list/email/index.php?'.$url. '&amp;page='.$page.'&amp;perpage='.$perpage;

    // Print init form from send data
    echo '<form id="sendmail" action="'.$CFG->wwwroot.'/blocks/email_list/email/index.php?id='.$course->id.'&amp;folderid='.$options->folderid.'" method="post" target="'.$CFG->framename.'" name="sendmail">';

	if ( $course->id == SITEID ) {
		$tablecolumns = array('', 'icon', 'course', 'subject', 'writer', 'timecreated');
	} else {
		$tablecolumns = array('', 'icon', 'subject', 'writer', 'timecreated');
	}

	$folder = NULL;
	if ( isset( $options->folderid) ) {
		if ( $options->folderid != 0 ) {
			// Get folder
			$folder = email_get_folder($options->folderid);
		} else {
			// solve problem with select an x mails per page for maintein in this folder
			if ( isset($options->folderoldid) && $options->folderoldid != 0 ) {
				$options->folderid = $options->folderoldid;
				$folder = email_get_folder($options->folderid);
			}
		}
	}

	// If actual folder is inbox type, ... change tag showing.
	if ( $folder ) {
		if ( ( email_isfolder_type($folder, EMAIL_INBOX) ) ) {
			$strto = get_string('from', 'block_email_list');
		} else {
			$strto = get_string('to', 'block_email_list');
		}
	} else {
		$strto = get_string('from', 'block_email_list');
	}

	if ( $course->id == SITEID ) {
    	$tableheaders = array('', '', get_string('course'), get_string('subject', 'block_email_list'), $strto, get_string('date', 'block_email_list'));
	} else {
		$tableheaders = array('', '', get_string('subject', 'block_email_list'), $strto, get_string('date', 'block_email_list'));
	}


	$table = new email_flexible_table('list-mails-'.$userid);

    $table->define_columns($tablecolumns);
    $table->define_headers($tableheaders);
    $table->define_baseurl($baseurl);

	$table->set_attribute('align', 'center');
	$table->set_attribute('width', '100%');
	$table->set_attribute('class', 'emailtable');

	$table->set_control_variables(array(
        TABLE_VAR_SORT    => 'ssort',
        TABLE_VAR_HIDE    => 'shide',
        TABLE_VAR_SHOW    => 'sshow',
        TABLE_VAR_IFIRST  => 'sifirst',
        TABLE_VAR_ILAST   => 'silast',
        TABLE_VAR_PAGE    => 'spage'
    ));

	$table->sortable(true, 'timecreated', SORT_DESC);

	$table->setup();

	// When no search
	if (! $search) {
		// Get mails
		$mails = email_get_mails($userid, $course->id, $table->get_sql_sort(), '', '', $options);
	} else {
		$mails = $mailssearch;
	}

	// Define long page.
	$totalcount = count($mails);
	$table->pagesize($SESSION->email_mailsperpage, $totalcount);

	$table->inputs(true);

	// Now, re-getting emails, apply pagesize (limit)
	if (! $search) {
		// Get mails
		$mails = email_get_mails($userid, $course->id, $table->get_sql_sort(), $table->get_page_start(), $table->get_page_size(), $options);
	}

	if (! $mails ) {
		$mails = array();
	}


	$mailsids = email_get_ids($mails);

	// Print all rows
	foreach ($mails as $mail) {

		$attribute = array();
		$email = new eMail();
		$email->set_email($mail);

		if ( $folder ) {
			if ( email_isfolder_type($folder, EMAIL_SENDBOX) ) {
				$struser = $email->get_users_send(has_capability('moodle/site:viewfullnames', $coursecontext));
			} else if ( email_isfolder_type($folder, EMAIL_INBOX) ) {

				$struser = $email->get_fullname_writer(has_capability('moodle/site:viewfullnames', $coursecontext));
				if (! $email->is_readed($userid, $mail->course) ) {
            		$attribute = array( 'bgcolor' => $CFG->email_table_field_color);
				}

			} else if ( email_isfolder_type($folder, EMAIL_TRASH) ){

				$struser = $email->get_fullname_writer(has_capability('moodle/site:viewfullnames', $coursecontext));

				if (! $email->is_readed($userid, $mail->course) ) {
        		    $attribute = array( 'bgcolor' => $CFG->email_table_field_color);
				}
			} else if ( email_isfolder_type($folder, EMAIL_DRAFT) ) {

				$struser = $email->get_users_send(has_capability('moodle/site:viewfullnames', $coursecontext));

				if (! $email->is_readed($userid, $mail->course) ) {
        		    $attribute = array( 'bgcolor' => $CFG->email_table_field_color);
				}
			} else {

				$struser = $email->get_fullname_writer(has_capability('moodle/site:viewfullnames', $coursecontext));

				if (! $email->is_readed($userid, $mail->course) ) {
        		    $attribute = array( 'bgcolor' => $CFG->email_table_field_color);
				}
			}
		} else {
			// Format user's
			$struser = $email->get_fullname_writer(has_capability('moodle/site:viewfullnames', $coursecontext));
			if (! $email->is_readed($userid, $mail->course) ) {
        	    $attribute = array( 'bgcolor' => $CFG->email_table_field_color);
			}
		}

		if (! isset($options->folderid) ) {
			$options->folderid = 0;
		}

		if ( email_isfolder_type($folder, EMAIL_DRAFT) ) {
			$urltosent = '<a href="'.$CFG->httpswwwroot.'/blocks/email_list/email/sendmail.php?id='.$mail->id.'&amp;action='.EMAIL_EDITDRAFT.'&amp;course='.$course->id.'">'.$mail->subject.'</a>';
		} else {
			if ( $course->id == SITEID ) {
				$urltosent = '<a href="'.$CFG->httpswwwroot.'/blocks/email_list/email/view.php?id='.$mail->id.'&amp;action='.EMAIL_VIEWMAIL.'&amp;course='.$mail->course.'&amp;folderid='.$options->folderid.'&amp;mails='.$mailsids.'">'.$mail->subject.'</a>';
			} else {
				$urltosent = '<a href="'.$CFG->httpswwwroot.'/blocks/email_list/email/view.php?id='.$mail->id.'&amp;action='.EMAIL_VIEWMAIL.'&amp;course='.$course->id.'&amp;folderid='.$options->folderid.'&amp;mails='.$mailsids.'">'.$mail->subject.'</a>';
			}
		}

		$attachment = '';
		if ( $email->has_attachments() ) {
			$attachment = '<img src="'.$CFG->httpswwwroot.'/blocks/email_list/email/images/clip.gif" alt="attachment" /> ';
		}

		// Display diferent color if mail is reply or reply all
		$extraimginfo = '';
		if ( $email->is_answered($userid, $course->id) ) {
			// Color td
			unset($attribute);
			$attribute = array('bgcolor' => $CFG->email_answered_color);

			// Adding info img
			$extraimginfo = '<img src="'.$CFG->wwwroot.'/blocks/email_list/email/images/answered.gif" alt="" /> ';

		}

		$course_mail = $DB->get_record('course', array('id' => $mail->course));

        $coursename = $CFG->email_display_course_fullname ? $course_mail->fullname : $course_mail->shortname;

		if ( $course->id == SITEID ) {
			$table->add_data( array (
	                                    '<input id="mail" type="checkbox" name="mailid[]" value="'.$mail->id.'" />',
	                                    $coursename,
	                                    $attachment.$extraimginfo,
	                                    $urltosent,
	                                    $struser,
	                                    userdate($mail->timecreated) ) ,
	                          $attribute
	                        );
		} else {
			$table->add_data( array (
	                                    '<input id="mail" type="checkbox" name="mailid[]" value="'.$mail->id.'" />',
	                                    $attachment.$extraimginfo,
	                                    $urltosent,
	                                    $struser,
	                                    userdate($mail->timecreated) ) ,
	                          $attribute
	                        );
		}

		// Save previous mail
       	$previousmail = $mail->id;
	}

	$table->print_html();



	// Print select action, if have mails
	if ( $mails ) {
		email_print_select_options($options, $SESSION->email_mailsperpage);
	}

	// End form
	echo '</form>';

	return true;
}

/**
 * This functions prints tabs options
 *
 * @uses $CFG, $OUTPUT
 * @param int $courseid Course Id
 * @param int $folderid Folder Id
 * @param string $action  Actual action
 * @return boolean Success/Fail
 * @todo Finish documenting this function
 */
function email_print_tabs_options($courseid, $folderid, $action=NULL) {

 	global $CFG, $OUTPUT;

 	// SSL encription
	if ( $CFG->email_enable_ssl ) {
        httpsrequired();
		$wwwroot = str_replace('http:','https:',$CFG->wwwroot);
    } else {
        $wwwroot = $CFG->wwwroot;
    }

 	if ($courseid == SITEID) {
        $context = get_context_instance(CONTEXT_SYSTEM, SITEID);   // SYSTEM context
    } else {
        $context = get_context_instance(CONTEXT_COURSE, $courseid);   // Course context
    }

 	// Declare tab array
 	$tabrow = array();

 	// Tab for writting new email
 	if ( has_capability('block/email_list:sendmessage', $context)) {
		$tabrow[] = new email_tabobject('newmail',   $wwwroot.'/blocks/email_list/email/sendmail.php?course='.$courseid.'&amp;folderid='.$folderid,   get_string('newmail', 'block_email_list'), '<img alt="'.get_string('edit').'" width="15" height="13" src="'. $CFG->pixpath .'/i/edit.gif" />' );
 	}

 	if ( has_capability('block/email_list:createfolder', $context)) {
		$tabrow[] = new email_tabobject('newfolderform', $CFG->wwwroot.'/blocks/email_list/email/folder.php?course='.$courseid.'&amp;folderid='.$folderid, get_string('newfolderform', 'block_email_list'), '<img alt="'.get_string('edit').'" width="15" height="15" src="'. $CFG->wwwroot .'/blocks/email_list/email/images/folder_add.png" />' );
 	}

	/// FUTURE: Implement filters
	//$tabrow[] = new tabobject('newfilter', $CFG->wwwroot.'/blocks/email_list/email/view.php?'.$url .'&amp;action=\'newfilter\'', get_string('newfilter', 'email') );

	$spacer = new html_image();
	$spacer->height = 50;
	$spacer->width = 1;
	// If empty tabrow, add vspace. Only apply on Site Course.
	if (empty($tabrow) ) {
		echo $OUTPUT->spacer($spacer);
	}

	$tabrows = array($tabrow);

	// Print tabs, and if it's in case, selected this
	switch($action)
	{
		case 'newmail':
			  	print_email_tabs($tabrows, 'newmail');
			break;
	    case 'newfolderform':
			  	print_email_tabs($tabrows, 'newfolderform');
			break;
		case 'newfilter':
			  print_email_tabs($tabrows, 'filter');
			break;
	    default:
			  print_email_tabs($tabrows);
	}

	return true;
 }


/// SQL funcions



/**
 * This function get write mails from user
 *
 * @param int $userid User ID
 * @param string $order Order by ...
 * @return object Contain all write mails
 * @todo Finish documenting this function
 **/
function email_get_my_writemails($userid, $order = NULL) {
	global $DB;

	// Get my write mails
	if ($order) {
		$mails = $DB->get_records('block_email_list_mail', array('userid' => $userid), $order);
	} else {
		$mails = $DB->get_records('block_email_list_mail', array('userid' => $userid));
	}

	return $mails;
}

/**
 * This function get mails.
 *
 * @uses $CFG
 * @param int $userid User ID
 * @param int $courseid Course ID
 * @param string $sort Order by ...
 * @param string $limitfrom Limit from
 * @param string $limitnum Limit num
 * @param object $options Options from get
 * @return object Contain all send mails
 * @todo Finish documenting this function
 **/
function email_get_mails($userid, $courseid=NULL, $sort = NULL, $limitfrom = '', $limitnum = '', $options = NULL) {

	global $CFG, $DB;

	// For apply order, I've writting an sql clause
	$sql = "SELECT DISTINCT m.id, m.userid as writer, m.course, m.subject, m.timecreated, m.body
                            FROM {block_email_list_mail} m
                   LEFT JOIN {block_email_list_send} s ON m.id = s.mailid ";

	$folder = email_get_root_folder($userid, EMAIL_INBOX);
	$params = array('userid' => $userid, 'sended' => 1, 'nonsended' => 0, 'courseid' => $courseid, 'folderid' => $options->folderid, 'folderidto' => $folder->id);
	// WHERE principal clause for filter userid
	$wheresql = " WHERE s.userid = :userid
					AND s.sended = :sended";
	if ( $courseid != SITEID ) {
		// WHERE principal clause for filter courseid
		$wheresql = " WHERE s.course = :courseid
					AND s.sended = :sended";
	}

	if ( $options ) {
		if ( isset($options->folderid ) ) {
			// Filter by folder?
			if ( $options->folderid != 0 ) {

				// Get folder
				$folder = email_get_folder($options->folderid);

				if ( email_isfolder_type($folder, EMAIL_SENDBOX) ) {
					// ALERT!!!! Modify where sql, because now I've show my inbox ==> email_send.userid = myuserid
					$wheresql = " WHERE m.userid = :userid
									AND s.sended = :sended";
					if ( $courseid != SITEID) {
						// WHERE principal clause for filter courseid
						$wheresql = " WHERE m.course = :courseid
										AND s.sended = :sended";
					}
				} else if ( email_isfolder_type($folder, EMAIL_DRAFT) ) {
					// ALERT!!!! Modify where sql, because now I've show my inbox ==> email_send.userid = myuserid
					$wheresql = " WHERE m.userid = :userid
									AND s.sended = :nonsended";
					if ( $courseid != SITEID) {
						// WHERE principal clause for filter courseid
						$wheresql = " WHERE m.course = :courseid
										AND s.sended = :nonsended";
					}
				}

				$sql .= " LEFT JOIN {block_email_list_foldermail} fm ON m.id = fm.mailid ";
				$wheresql .= " AND fm.folderid = :folderid ";

			} else {
				/// If folder == 0, I've get inbox
				// Get folder
				$folder = email_get_root_folder($userid, EMAIL_INBOX);
				$myfolder = array('folderidto' => $folder->id);
				$params = array_merge($params, $myfolder);
				$sql .= " LEFT JOIN {block_email_list_foldermail} fm ON m.id = fm.mailid ";
				$wheresql .= " AND fm.folderid = :folderidto ";
			}
		} else {
			/// If folder == 0, I've get inbox
			// Get folder
			$sql .= " LEFT JOIN {block_email_list_foldermail} fm ON m.id = fm.mailid ";
			$wheresql .= " AND fm.folderid = :folderidto ";
		}
	} else {
		/// If no options, I've get inbox, per default get this folder
		// Get folder
		$sql .= " LEFT JOIN {block_email_list_foldermail} fm ON m.id = fm.mailid ";
		$wheresql .= " AND fm.folderid = :folderidto ";
	}

	if ($sort) {
		$sortsql = ' ORDER BY '.$sort;
	} else {
		$sortsql = ' ORDER BY m.timecreated';
	}

	return $DB->get_records_sql($sql.$wheresql.$sortsql, $params, $limitfrom, $limitnum);
}

/**
 * This function gets all userid unread mails. It don't read trash, draft and send folders.
 * Only used in the cron.
 *
 * @param int $userid User Id.
 * @return Array All Inbox mails.
 */
function email_get_unread_mails($userid) {
	global $CFG, $DB;

	// Get user courses (don't read hidden courses)
	if ( $mycourses = get_my_courses($userid) ) {

		// Get inbox folder
		if ( $folder = email_get_root_folder($userid, EMAIL_INBOX) ) {

			// Get all subfolders
			$subfolders = email_get_all_subfolders($folder->id);

			$myfolders =  array_merge($folder, $subfolders);

			$params = array('userid' => $userid, 'readed' => 0, 'sended' => 1);

			list($csql, $params) = $DB->get_in_or_equal($mycourses);
			list($fsql, $params) = $DB->get_in_or_equal($myfolders);

			$sql = "SELECT s.userid, s.mailid, s.course
		                            FROM {block_email_list_mail} m
		                   LEFT JOIN {block_email_list_send} s ON m.id = s.mailid
		                   LEFT JOIN {block_email_list_foldermail} fm ON m.id = fm.mailid ";


			// WHERE principal clause for filter by user and course
			$wheresql = " WHERE s.userid = :userid
						  AND s.readed = :readed
						  AND s.sended = :sended
						  $usql $csql";

			return $DB->get_records_sql( $sql.$wheresql, $params );
		}
	}


	return array();

}

/**
 * This functions return number of unreaded mails
 *
 * @uses $CFG, $DB
 * @param int $userid User ID
 * @param int $courseid Course ID
 * @param int $folderid Folder ID (Optional) When fault this param, return total number of unreaded mails
 * @return int Number of unread mails.
 * @todo Finish documenting this function
 **/
function email_count_unreaded_mails($userid, $courseid, $folderid=NULL) {

	global $CFG, $DB;

	require_once($CFG->dirroot.'/blocks/email_list/email/folder.class.php');

	$folderman = new folder();

	if (! $folderid or $folderid <= 0 ) {

		$myfolders = $folderman->get_folders($userid, EMAIL_INBOX);

		// Prepare list of folders
		foreach ( $myfolders as $myfolder ) {
			$folders[] = $myfolder->id;
		}

		list($fsql, $params2) = $DB->get_in_or_equal($folders);

		$params = array('userid' => $userid, 'courseid' => $courseid, 'readed' => 0, 'sended' => 1);

		$params = array_merge($params, $params2);

		$sql = "SELECT COUNT(m.id)
	                            FROM {block_email_list_mail} m
	                   LEFT JOIN {block_email_list_send} s ON m.id = s.mailid
	                   LEFT JOIN {block_email_list_foldermail} fm ON m.id = fm.mailid ";


		// WHERE principal clause for filter by user and course
		$wheresql = " WHERE s.userid = ?
					  AND s.course = ?
					  AND s.readed = ?
					  AND s.sended = ?
					  AND fm.folderid $fsql";

		return $DB->count_records_sql( $sql.$wheresql, $params );

	} else {

		// Get folder
		if ( ! $folder = $folderman->get_folder($folderid) ) {
			return 0;
		}

		if ( $folder->isparenttype == EMAIL_INBOX ) {
			// For apply order, I've writting an sql clause
			$sql = "SELECT count(*)
		                            FROM {block_email_list_mail} m
		                   LEFT JOIN {block_email_list_send} s ON m.id = s.mailid
		                   LEFT JOIN {block_email_list_foldermail} fm ON m.id = fm.mailid ";

			$params = array('userid' => $userid, 'course' => $courseid, 'folderid' => $folder->id, 'readed' => 0, 'sended' => 1);
			// WHERE principal clause for filter by user and course
			$wheresql = " WHERE s.userid = :userid
						  AND s.course = :course
						  AND fm.folderid = :folderid
						  AND s.readed = :readed
						  AND s.sended = :sended";

			return $DB->count_records_sql( $sql.$wheresql, $params );

		} else if ( $folder->isparenttype == EMAIL_DRAFT ) {
			// For apply order, I've writting an sql clause
			$sql = "SELECT count(*)
		                   	FROM {block_email_list_mail} m
		                   	LEFT JOIN {block_email_list_foldermail} fm ON m.id = fm.mailid ";

			$params = array('userid' => $userid, 'course' => $courseid, 'folderid' => $folder->id);
			// WHERE principal clause for filter user and course
			$wheresql = " WHERE m.userid = :userid
						  AND m.course = :course
						  AND fm.folderid = :folderid";

			return $DB->count_records_sql( $sql.$wheresql, $params );

		} else {
			return 0;
		}
	}
}

/**
 * This function return success/fail if folder corresponding with this type.
 *
 * @param object $folder Folder Object
 * @param string $type Type folder
 * @return boolean Success/Fail
 * @todo Finish documenting this function
 **/
function email_isfolder_type($folder, $type) {

	if ( isset($folder->isparenttype) && $folder->isparenttype ) {
		return ($type == $folder->isparenttype);
	} else {

		// Get first parent
		$parentfolder = email_get_parent_folder($folder);

		if ( ! isset($parentfolder->isparenttype) ) {
			return false;
		}

		// Return value
		return ( $parentfolder->isparenttype == $type );
	}

}

/**
 * This function return folder parent.
 *
 * @param object $folder Folder
 * @return object Contain parent folder
 * @todo Finish documenting this function
 **/
function email_get_parent_folder($folder) {
	global $DB;

	if (! $folder ) {
		return false;
	}

	if ( is_int($folder) ) {
		$subfolder = $DB->get_record('block_email_list_subfolder', array('folderchildid' => $folder));
	} else {
		$subfolder = $DB->get_record('block_email_list_subfolder', array('folderchildid' => $folder->id));
    }

    return $DB->get_record('block_email_list_folder', array('id' => $subfolder->folderparentid));
}

/**
 * This function return folder parent with it.
 *
 * @uses $USER, $DB
 * @param int $userid User ID
 * @param string $folder Folder
 * @return object Contain parent folder
 * @todo Finish documenting this function
 **/
function email_get_root_folder($userid, $folder) {

	global $USER, $DB;

	if ( empty($userid) ) {
		$userid = $USER->id;
	}

	email_create_parents_folders($userid);

	$rootfolder = new stdClass();

	if ( $userid > 0 and !empty($userid) ) {
		if ( $folder == EMAIL_INBOX ) {
			$rootfolder = $DB->get_record('block_email_list_folder', array('userid' => $userid, 'isparenttype' => EMAIL_INBOX));
			$rootfolder->name = get_string('inbox', 'block_email_list');
			return $rootfolder;
		}

		if ( $folder == EMAIL_SENDBOX ) {
			$rootfolder = $DB->get_record('block_email_list_folder', array('userid' => $userid, 'isparenttype' => EMAIL_SENDBOX));
			$rootfolder->name = get_string('sendbox', 'block_email_list');
			return $rootfolder;
		}

		if ( $folder == EMAIL_TRASH ) {
			$rootfolder = $DB->get_record('block_email_list_folder', array('userid' => $userid, 'isparenttype' => EMAIL_TRASH));
			$rootfolder->name = get_string('trash', 'block_email_list');
			return $rootfolder;
		}

		if ( $folder == EMAIL_DRAFT ) {
			$rootfolder = $DB->get_record('block_email_list_folder', array('userid' => $userid, 'isparenttype' => EMAIL_DRAFT));
			$rootfolder->name = get_string('draft', 'block_email_list');
			return $rootfolder;
		}
	}

	return $rootfolder;

}

/**
 * This function return my folders. it's recursive function
 *
 */
function email_my_folders($folderid, $courseid, $myfolders, $space) {

	$space .= '&#160;&#160;&#160;';

	$folders = email_get_subfolders($folderid, $courseid);
	if ( $folders ) {
		foreach ( $folders as $folder ) {
			$myfolders[$folder->id] = $space.$folder->name;
			$myfolders = email_my_folders($folder->id, $courseid, $myfolders, $space);
		}
	}

	return $myfolders;

}

/**
 * This function return my folders
 *
 * @param int $userid User ID
 * @param int $courseid Course ID
 * @param boolean $excludetrash Exclude Trash
 * @param boolean $excludedraft Exclude Draft
 * @param boolean $excludesendbox Exclude Sendbox
 * @param boolean $excludeinbox Exclude Inbox
 * @return array Contain my folders
 * @todo Finish documenting this function
 */
function email_get_my_folders($userid, $courseid, $excludetrash, $excludedraft, $excludesendbox=false, $excludeinbox=false) {

	// Save my folders in this variable
	$myfolders = array();

	// Get especific root folders
	$folders = email_get_root_folders($userid, !$excludedraft, !$excludetrash, !$excludesendbox, !$excludeinbox);

	// for every root folder
	foreach ( $folders as $folder ) {

		$myfolders[$folder->id] = $folder->name;
		$myfolders = email_my_folders($folder->id, $courseid, $myfolders, '&#160;&#160;&#160;');
	}

	return $myfolders;
}

/**
 * This function return root folders parent with it.
 *
 * @param int $userid User ID
 * @param boolean $draft Add draft folder
 * @param boolean $trash Add trash folder
 * @param boolean $sendbox Add sendbox folder
 * @param boolean $inbox Add inbox folder
 * @return array Contain all parents folders
 * @todo Finish documenting this function
 **/
function email_get_root_folders($userid, $draft=true, $trash=true, $sendbox=true, $inbox=true) {

	email_create_parents_folders($userid);

	$folders = array();

	// Include inbox folder
	if ( $inbox ) {
		$folders[] = email_get_root_folder( $userid, EMAIL_INBOX);
	}

	// Include return draft folder
	if ( $draft ) {
		$folders[] = email_get_root_folder( $userid, EMAIL_DRAFT);
	}

	// Include sendbox folder
	if( $sendbox ) {
		$folders[] = email_get_root_folder( $userid, EMAIL_SENDBOX);
	}

	if ( $trash ) {
		$folders[] = email_get_root_folder( $userid, EMAIL_TRASH);
	}

	return $folders;

}

/**
 * This function get users to sent an mail.
 *
 * @param int $mailid Mail ID
 * @param boolean $forreplyall Flag indicates if getting user's for reply all. If true return object contain formated names (Optional)
 * @param object $writer Contain user who write mail (if not null, exclude this user for returned)
 * @param string Type of mail for users
 * @return object Contain all users object send mails
 * @todo Finish documenting this function
 **/
function email_get_users_sent($mailid, $forreplyall=false, $writer=NULL, $type='') {

	global $DB;

	// Get mails with send to my
	$sends = $DB->get_records('block_email_list_send', array('mailid' => $mailid));

	$users = array();

	// Get username
	foreach ( $sends as $send ) {

		// Get user
		$user = $DB->get_record('user', array('id' => $send->userid));

		// Exclude user
		if ( $writer ) {
			if ( $user->id != $writer->id) {
				if (! $forreplyall ) {
					$users[] = fullname($user);
				} else {
					// Separe type, if it's corresponding
					if ( $type == 'to') {
						if ($send->type == 'to' ) {
							$users[] = $user->id;
						}
					} else if ( $type == 'cc' ) {
						if ($send->type == 'cc' ) {
							$users[] = $user->id;
						}
					} else if ( $type == 'bcc' ) {
						if ($send->type == 'bcc' ) {
							$users[] = $user->id;
						}
					} else {
						$users[] = $user->id;
					}
				}
			}
		} else {
			if (! $forreplyall ) {

				// Separe type, if it's corresponding
				if ( $type == 'to') {
					if ($send->type == 'to' ) {
						$users[] = fullname($user);
					}
				} else if ( $type == 'cc' ) {
					if ($send->type == 'cc' ) {
						$users[] = fullname($user);
					}
				} else if ( $type == 'bcc' ) {
					if ($send->type == 'bcc' ) {
						$users[] = fullname($user);
					}
				} else {
					$users[] = fullname($user);
				}
			} else {
				// Separe type, if it's corresponding
				if ( $type == 'to') {
					if ($send->type == 'to' ) {
						$users[] = $user->id;
					}
				} else if ( $type == 'cc' ) {
					if ($send->type == 'cc' ) {
						$users[] = $user->id;
					}
				} else if ( $type == 'bcc' ) {
					if ($send->type == 'bcc' ) {
						$users[] = $user->id;
					}
				} else {
					$users[] = $user->id;
				}
			}
		}

	}

	return $users;
}

/**
 * This function return format fullname users.
 *
 * @param array $users Fullname of user's
 * @param boolean $forreplyall If it's true, no return string error (default false)
 * @return string format fullname user's.
 * @todo Finish documenting this function
 **/
function email_format_users($users, $forreplyall=false) {

	if ($users) {

		$usersend = '';

		// Index of record
		$i = 0;
		foreach ( $users as $user ) {

			// If no first record, add semicolon
			if ( $i != 0 ) {
				$usersend .= ', '.$user;
			} else {
				// If first add name only
				$usersend .= $user;
			}

			// Increment index record
			$i++;
		}
	} else {

		if (! $forreplyall) {
			// If no users sent's, inform this act.
			$usersend = get_string('neverusers', 'block_email_list');
		} else {
			$usersend = '';
		}
	}

	// Return string format name's
	return $usersend;
}

/**
 * This functions print select form, who it's options to have mails
 *
 * @uses $CFG, $USER, $OUTPUT
 * @param object $options Options for redirect this form
 * @return boolean Success/Fail
 * @todo Finish documenting this function
 * */
function email_print_select_options($options, $perpage) {

	global $CFG, $USER, $OUTPUT;

	$baseurl = $CFG->wwwroot . '/blocks/email_list/email/index.php?' . email_build_url($options);

	echo '<br />';

	echo '<div class="content emailfloatcontent">';

	$spacer = new html_image();
	$spacer->height = 1;
	$spacer->width = 20;

	if ( $options->id != SITEID ) {
		echo '<div class="emailfloat emailleft">';
		if ( ! email_isfolder_type(email_get_folder($options->folderid), EMAIL_SENDBOX) ) {
			echo '<select name="action" onchange="this.form.submit()">
		                	<option value="" selected="selected">' . get_string('markas', 'block_email_list') .':</option>
		        			<option value="toread">' . get_string('toread','block_email_list') . '</option>
		               	<option value="tounread">' . get_string('tounread','block_email_list') . '</option>
		    		</select>';


		    echo $OUTPUT->spacer($spacer);
		}
		echo '</div>';
	    echo '<div class="emailleft">';
	    email_print_movefolder_button($options);
		// Idaho State University & MoodleRooms contrib - Thanks!
		email_print_preview_button($options->id);
		echo '</div>';
		echo '<div id="move2folder"></div>';
		echo '</div>';

	}

	// ALERT!: Now, I'm printing end start form, for choose number mail per page
	$url = '';
	// Build url part options
 	if ($options) {
 		$url = email_build_url($options);
    }


	echo '</form>';
	echo '<form id="mailsperpage" name="mailsperpage" action="'.$CFG->wwwroot.'/blocks/email_list/email/index.php?'.$url.'" method="post">';


	// Choose number mails perpage

	echo '<div id="sizepage" class="emailright">' . get_string('mailsperpage', 'block_email_list') .': ';


	// Define default separator
	$spaces = '&#160;&#160;&#160;';

	echo '<select name="perpage" onchange="javascript:this.form.submit();">';

	for($i = 5; $i < 80; $i=$i+5) {

    	if ( $perpage == $i ) {
    		echo '<option value="'.$i.'" selected="selected">' . $i . '</option>';
    	} else {
    		echo '<option value="'.$i.'">' . $i . '</option>';
    	}
	}

    echo '</select>';

	echo '</div>';

    return true;
}


/**
 * This function prints select folders combobox, for move any mails
 *
 * @uses $USER
 * @param object $options
 */
function email_print_movefolder_button($options) {

	global $CFG, $USER;

	$courseid = NULL;
	if ( $options->id == SITEID and $options->course != SITEID) {
		$courseid = $options->course;
	} else {
		$courseid = $options->id;
	}

	/// TODO: Changed this function, now cases are had:
	//						1.- Inbox folder: Only can move to subfolders inbox and trash folder.
	//						2.- Sendbox and draft folder: Only can move on this subfolders.
	//						3.- Trash folder: Can move any folder
	if ( isset($options->folderid) ) {
		// Get folder
		$folderbe = email_get_folder($options->folderid);

	} else if ( isset($options->folderoldid) ) {
		// Get folder
		$folderbe = email_get_folder($options->folderoldid);
	} else {
		// Inbox folder
		$folderbe = email_get_root_folder($USER->id, EMAIL_INBOX);
	}

	if ( email_isfolder_type($folderbe, EMAIL_SENDBOX ) ) {
		// Get my sendbox folders
		$folders = email_get_my_folders( $USER->id, $courseid, false, true, false, true );
	} else if ( email_isfolder_type($folderbe, EMAIL_DRAFT )) {
		// Get my sendbox folders
		$folders = email_get_my_folders( $USER->id, $courseid, false, true, true, true );
	} else if ( email_isfolder_type($folderbe, EMAIL_TRASH ) ) {
		// Get my folders
		$folders = email_get_my_folders( $USER->id, $courseid, false, false, false, false );
	} else {
		// Get my folders
		$folders = email_get_my_folders( $USER->id, $courseid, false, true, true, false );
	}

	if ( $folders ) {

		$choose = '';

		// Get my courses
		foreach ($folders as $key => $foldername) {

	        $choose .= '<option value="'.$key.'">'.$foldername  .'</option>';
		}
	}

	echo '<select name="folderid" onchange="addAction(this)">
					<option value="" selected="selected">' . get_string('movetofolder', 'block_email_list') . ':</option>' .
               			$choose . '
    		</select>';


	// Add 2 space
	echo '&#160;&#160;';

	// Change, now folderoldid is actual folderid
	if (! $options->folderid ) {
		if ( $inbox = email_get_root_folder($USER->id, EMAIL_INBOX) ) {
			echo '<input type="hidden" name="folderoldid" value="'.$inbox->id.'" />';
		}
	} else {
		echo '<input type="hidden" name="folderoldid" value="'.$options->folderid.'" />';
	}

	// Define action
	//echo '<input type="hidden" name="action" value="move2folder" />';
	// Add javascript for insert person/s who I've send mail

	$javascript = '<script type="text/javascript" language="JavaScript">
                <!--
                		function addAction(form) {

                			var d = document.createElement("div");
                        d.setAttribute("id", "action");
                        var act = document.createElement("input");
                        act.setAttribute("type", "hidden");
                        act.setAttribute("name", "action");
                        act.setAttribute("id", "action");
                        act.setAttribute("value", "move2folder");
                        d.appendChild(act);
                        document.getElementById("move2folder").appendChild(d);

                			document.sendmail.submit();
                		}
                	-->
                 </script>';

	echo $javascript;

	// Print sent button
	//echo '<input type="submit" value="' .get_string('move'). '" onclick="javascript:addAction(this);" />';

	//echo '</div>';
}

/**
 * This funcions build an URL for an options
 *
 * @param object $options
 * @param boolean $form If return form
 * @param boolean $arrayinput If name of hidden input is array.
 * @param string $nameinput If is arrayinput, pass name of this.
 * @return string URL or Hidden input's
 * @todo Finish documenting this function
 **/
function email_build_url($options, $form=false, $arrayinput=false, $nameinput=NULL) {

	$url = '';

	// Build url part options
 	if ($options) {
 		// Index of part url
 		$i = 0;

        foreach ($options as $name => $value) {
        	// If not first param
        	if (! $form ) {
        		if ($i != 0) {
           			$url .= '&amp;' .$name .'='. $value;
        		} else {
        			// If first param
        			$url .= $name .'='. $value;
        		}
        		// Increment index
        		$i++;
        	} else {

        		if ( $arrayinput ) {
        			$url .= '<input type="hidden" name="'.$nameinput.'[]" value="'.$value.'" /> ';
        		} else {
        			$url .= '<input type="hidden" name="'.$name.'" value="'.$value.'" /> ';
        		}
        	}
        }
    }

    return $url;
}


/**
 * This functions return id's of object
 *
 * @param object $ids Mail
 * @return string String of ids
 * @todo Finish documenting this function
 **/
function email_get_ids($ids) {
	$identities = array();

	if ( $ids ) {
		foreach ($ids as $id) {
			$identities[] = $id->id;
		}
	}

	// Character alfanumeric, becase optional_param clean anothers tags.
	$strids = implode('a', $identities);

	return $strids;
}

/**
 * This functions return next or previous mail
 *
 * @param int $mailid Mail
 * @param string $mails Id's of mails
 * @param boolean True when next, false when previous
 * @return int Next or Previous mail
 * @todo Finish documenting this function
 **/
function email_get_nextprevmail($mailid, $mails, $nextorprevious) {

	// To array
	// Character alfanumeric, becase optional_param clean anothers tags.
	$mailsids = explode('a', $mails);

	if ( $mailsids ) {
		$prev = 0;
		$next = false;
		foreach ($mailsids as $mail) {
			if ( $next ) {
				return $mail; // Return next "record"
			}
			if ( $mail == $mailid ) {
				if ($nextorprevious) {
					$next = true;
				} else {
					return $prev; // Return previous "record"
				}
			}
			$prev = $mail;
		}
	}

	return false;
}

/**
 * This function return user who writting an mail
 *
 * @param int $mailid Mail ID
 * @return object User record
 * @todo Finish documenting this function
 */
function email_get_user($mailid) {

	global $DB;

	// Get mail record
	$mail = $DB->get_record('block_email_list_mail', array('id' => $mailid));

	// Return user record
	return $DB->get_record('user', array('id' => $mail->userid));
}

/**
 * This function return, if corresponding, preferences button.
 *
 * @uses $CFG
 * @param int $courseid Course Id.
 * @return string. Preferences button if corresponding.
 * @todo Finish documenting this function.
 */
function email_get_preferences_button($courseid) {
	global $CFG, $OUTPUT;

	// Security
	if ( empty($courseid) ) {
		$courseid = SITEID;
	}

	if ( empty($CFG->email_trackbymail) and empty($CFG->email_marriedfolders2courses) ) {
		return '';
	} else {

		$form = new html_form();
		$form->url = new moodle_url($CFG->wwwroot.'/blocks/email_list/email/preferences.php', array('id' => $courseid)); // Required
		$form->button = new html_button();
		$form->button->text = get_string('preferences', 'block_email_list'); // Required
		$form->method = 'post';

		return $OUTPUT->button($form);
	}
}

/**
 * This function return if userid have aviability or not to associate folder to courses.
 *
 * @param int $userid User Id.
 * @return boolean True or false if have aviability
 */
function email_have_asociated_folders($userid) {
	global $CFG, $USER, $DB;

	if ( empty($userid) ) {
		$userid = $USER->id;
	}

	if ( $CFG->email_marriedfolders2courses ) {
		if ( $preferences = $DB->get_record('block_email_list_preference', array('userid' => $userid)) ) {
			if ($preferences->marriedfolders2courses) {
				return true;
			}
		}
	}

	return false;
}




/// eMail tabs
/// Some code to print tabs

/// A class for tabs
class email_tabobject {
    var $id;
    var $link;
    var $text;
    var $linkedwhenselected;

    /// A constructor just because I like constructors
    function email_tabobject($id, $link='', $text='', $img='', $title='', $linkedwhenselected=false) {
        $this->id   = $id;
        $this->link = $link;
        $this->text = $text;
        $this->title = $title ? $title : $text;
        $this->img	= $img;
        $this->linkedwhenselected = $linkedwhenselected;
    }
}



/**
 * Returns a string containing a nested list, suitable for formatting into tabs with CSS.
 *
 * @param array $tabrows An array of rows where each row is an array of tab objects
 * @param string $selected  The id of the selected tab (whatever row it's on)
 * @param array  $inactive  An array of ids of inactive tabs that are not selectable.
 * @param array  $activated An array of ids of other tabs that are currently activated
**/
function print_email_tabs($tabrows, $selected=NULL, $inactive=NULL, $activated=NULL, $return=false) {
    global $CFG;

/// $inactive must be an array
    if (!is_array($inactive)) {
        $inactive = array();
    }

/// $activated must be an array
    if (!is_array($activated)) {
        $activated = array();
    }

/// Convert the tab rows into a tree that's easier to process
    if (!$tree = convert_tabrows_to_tree($tabrows, $selected, $inactive, $activated)) {
        return false;
    }

/// Print out the current tree of tabs (this function is recursive)

    $output = email_convert_tree_to_html($tree);

    $output = "\n\n".'<div class="tabtree">'.$output.'</div><div class="clearer"> </div>'."\n\n";

/// We're done!

    if ($return) {
        return $output;
    }
    echo $output;
}


function email_convert_tree_to_html($tree, $row=0) {

	global $OUTPUT;

    $str = "\n".'<ul class="tabrow'.$row.'">'."\n";

    $first = true;
    $count = count($tree);

    $spacer = new html_image();
	$spacer->height = 1;
	$spacer->width = 4;

    foreach ($tree as $tab) {
        $count--;   // countdown to zero

        $liclass = '';

        if ($first && ($count == 0)) {   // Just one in the row
            $liclass = 'first last';
            $first = false;
        } else if ($first) {
            $liclass = 'first';
            $first = false;
        } else if ($count == 0) {
            $liclass = 'last';
        }

        if ((empty($tab->subtree)) && (!empty($tab->selected))) {
            $liclass .= (empty($liclass)) ? 'onerow' : ' onerow';
        }

        if ($tab->inactive || $tab->active || ($tab->selected && !$tab->linkedwhenselected)) {
            if ($tab->selected) {
                $liclass .= (empty($liclass)) ? 'here selected' : ' here selected';
            } else if ($tab->active) {
                $liclass .= (empty($liclass)) ? 'here active' : ' here active';
            }
        }

        $str .= (!empty($liclass)) ? '<li class="'.$liclass.'">' : '<li>';

        if ($tab->inactive || $tab->active || ($tab->selected && !$tab->linkedwhenselected)) {
            $str .= '<a href="#" title="'.$tab->title.'"><span>'.$tab->text.$OUTPUT->spacer($spacer).$tab->img.'</span></a>';
        } else {
            $str .= '<a href="'.$tab->link.'" title="'.$tab->title.'"><span>'.$tab->text.$OUTPUT->spacer($spacer).$tab->img.'</span></a>';
        }

        if (!empty($tab->subtree)) {
            $str .= convert_tree_to_html($tab->subtree, $row+1);
        } else if ($tab->selected) {
            $str .= '<div class="tabrow'.($row+1).' empty">&nbsp;</div>'."\n";
        }

        $str .= ' </li>'."\n";
    }
    $str .= '</ul>'."\n";

    return $str;
}

/// Compatibility moodle 1.7

if (! function_exists('convert_tree_to_html') ) {
	function convert_tree_to_html($tree, $row=0) {

	    $str = "\n".'<ul class="tabrow'.$row.'">'."\n";

	    $first = true;
	    $count = count($tree);

	    foreach ($tree as $tab) {
	        $count--;   // countdown to zero

	        $liclass = '';

	        if ($first && ($count == 0)) {   // Just one in the row
	            $liclass = 'first last';
	            $first = false;
	        } else if ($first) {
	            $liclass = 'first';
	            $first = false;
	        } else if ($count == 0) {
	            $liclass = 'last';
	        }

	        if ((empty($tab->subtree)) && (!empty($tab->selected))) {
	            $liclass .= (empty($liclass)) ? 'onerow' : ' onerow';
	        }

	        if ($tab->inactive || $tab->active || ($tab->selected && !$tab->linkedwhenselected)) {
	            if ($tab->selected) {
	                $liclass .= (empty($liclass)) ? 'here selected' : ' here selected';
	            } else if ($tab->active) {
	                $liclass .= (empty($liclass)) ? 'here active' : ' here active';
	            }
	        }

	        $str .= (!empty($liclass)) ? '<li class="'.$liclass.'">' : '<li>';

	        if ($tab->inactive || $tab->active || ($tab->selected && !$tab->linkedwhenselected)) {
	            $str .= '<a href="#" title="'.$tab->title.'"><span>'.$tab->text.'</span></a>';
	        } else {
	            $str .= '<a href="'.$tab->link.'" title="'.$tab->title.'"><span>'.$tab->text.'</span></a>';
	        }

	        if (!empty($tab->subtree)) {
	            $str .= convert_tree_to_html($tab->subtree, $row+1);
	        } else if ($tab->selected) {
	            $str .= '<div class="tabrow'.($row+1).' empty">&nbsp;</div>'."\n";
	        }

	        $str .= ' </li>'."\n";
	    }
	    $str .= '</ul>'."\n";

	    return $str;
	}
}

if (! function_exists( 'convert_tabrows_to_tree' ) ) {
	function convert_tabrows_to_tree($tabrows, $selected, $inactive, $activated) {

	/// Work backwards through the rows (bottom to top) collecting the tree as we go.

	    $tabrows = array_reverse($tabrows);

	    $subtree = array();

	    foreach ($tabrows as $row) {
	        $tree = array();

	        foreach ($row as $tab) {
	            $tab->inactive = in_array((string)$tab->id, $inactive);
	            $tab->active = in_array((string)$tab->id, $activated);
	            $tab->selected = (string)$tab->id == $selected;

	            if ($tab->active || $tab->selected) {
	                if ($subtree) {
	                    $tab->subtree = $subtree;
	                }
	            }
	            $tree[] = $tab;
	        }
	        $subtree = $tree;
	    }

	    return $subtree;
	}
}

/**
 * This function return special fullname
 * Call general fullname, but it drop all semicolon apears.
 *
 * @param object $user User
 * @return string Full name
 */
function email_fullname($user, $override=false) {

	// Drop all semicolon apears. (Js errors when select contacts)
	return str_replace(',', '', fullname($user, $override));
}

/**
 * Prints the print emails button
 *
 * Idaho State University & MoodleRooms contrib - Thanks!
 *
 * @param int $courseid Course Id
 *
 * @return void
 **/
function email_print_preview_button($courseid) {
    // Action is handled weird, put in a dummy hidden element
    // and then change its name to action when our button has
    // been clicked
    /*echo '<span id="print_preview" class="print_preview">
              <input type="submit" value="'.get_string('printemails', 'block_email_list').'" name="action" onclick="return print_multiple_emails(document.sendmail.mail);" />
              <input type="hidden" value="print" name="disabled" id="printactionid" />
          </span>';
*/
	echo '<span id="print_preview" class="print_preview">';
    email_print_to_popup_window ('button', '/blocks/email_list/email/print.php?courseid='.$courseid.'&amp;mailids=', get_string('printemails', 'block_email_list'),
                                 get_string('printemails', 'block_email_list'));
    echo '</span>';
}

/**
 * Need redefine element_to_popup_window because before get email ids for print. Modify all.
 *
 */
function email_print_to_popup_window($type=null, $url=null, $linkname=null, $title=null, $return=false) {

    if (is_null($url)) {
        debugging('You must give the url to display in the popup. URL is missing - can\'t create popup window.', DEBUG_DEVELOPER);
    }

    global $CFG;



    // add some sane default options for popup windows
    $options = 'menubar=0,location=0,scrollbars,resizable,width=500,height=400';

    $name = 'popup';

    // get some default string, using the localized version of legacy defaults
    if (is_null($linkname) || $linkname === '') {
        $linkname = get_string('clickhere');
    }
    if (!$title) {
        $title = get_string('popupwindowname');
    }

    $fullscreen = 0; // must be passed to openpopup
    $element = '';

    $jscode = ' if (document.sendmail) { var ids = get_for_print_multiple_emails(document.sendmail.mail); }';

    switch ($type) {
        case 'button' :
            $element = '<input type="button" name="'. $name .'" title="'. $title .'" value="'. $linkname .'" '.
                       "onclick=\" $jscode if(ids !='' ) { return openpopup('$url'+ids, '$name', '$options', $fullscreen); } else { alert('". addslashes(get_string('nochoosemail', 'block_email_list'))."'); } \" />\n";
            break;
        case 'link' :
            // some log url entries contain _SERVER[HTTP_REFERRER] in which case wwwroot is already there.
            if (!(strpos($url,$CFG->wwwroot) === false)) {
                $url = substr($url, strlen($CFG->wwwroot));
            }
            $element = '<a title="'. s(strip_tags($title)) .'" href="'. $CFG->wwwroot . $url .'" '.
                       "onclick=\"this.target='$name'; $jscode if (ids !=''){ return openpopup('$url'+ids, '$name', '$options', $fullscreen); } else { alert('". addslashes(get_string('nochoosemail', 'block_email_list'))."'); } \">$linkname</a>";
            break;
        default :
            print_error('undefinedelement', 'block_email_list');
            break;
    }

    if ($return) {
        return $element;
    } else {
        echo $element;
    }
}

/**
 * This function manage value for what mails show perpage.
 *
 * @uses $SESSION
 */
function email_manage_mailsperpage() {
	global $SESSION;
print_object($_POST);
	if ( ! empty( $_POST['perpage'] ) and is_int($_POST['perpage']) ) {
		$SESSION->email_mailsperpage = $_POST['perpage'];
		echo 'Change for: '.$_POST['perpage'];
	} else {
		$SESSION->email_mailsperpage = 10; // Default value
	}
}


//////////////////////////
//			AJAX		//
//////////////////////////
/**
  * This function returns an object of all users whithin current course who match
  * the search query.
  *  *Modified version of datalib.php's search_user() function
  *
  * @param object $course Current Course object
  * @param string $query Search query
  * @param boolean $dispadmins Flag to return course admins or not
  * @param boolean $displayunconfirmed Flag to specify to return unconfirmed users
  * @return object result set of all matching users
  * @todo Add option to remove active user from results
 */
function email_search_course_users($course, $query = '', $dispadmins = false, $dispunconfirmed = true) {
	global $CFG, $USER, $DB;

    $LIKE      = $DB->sql_ilike();

    $order = 'ORDER BY firstname, lastname, id';

	$params = array('deleted' => 0, 'confirmed' => 1, 'username' => 'guest');
    $select = 'u.deleted = :deleted';
    if (!$dispunconfirmed) {
        $select .= ' AND u.confirmed = :confirmed';
    }

    if (!$course or $course->id == SITEID) {
        $results = $DB->get_records_sql("SELECT u.id, u.firstname, u.lastname, u.email
                      FROM {user} u
                      WHERE $select
                          AND (u.firstname $LIKE '$query%' OR u.lastname $LIKE '$query%')
                          AND u.username != :username
                          $order");
    } else {
        if ($course->id == SITEID) {
            $context = get_context_instance(CONTEXT_SYSTEM, SITEID);
        } else {
            $context = get_context_instance(CONTEXT_COURSE, $course->id);
        }

        $contextlists = get_related_contexts_string($context);

        // Returns only group(s) members for users without the viewallgroups capability
        $groupmembers = '';
        // Separate groups
        $groupmode = groups_get_course_groupmode($course);
        if ($groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
            // Returns all groups current user is assigned to in course
            if ($groups = groups_get_all_groups($course->id, $USER->id)) {
                $groupmembers = array();
                foreach ($groups as $group) {
                    $groupmembers += groups_get_members($group->id, 'u.id');
                }
                if (!empty($groupmembers)) {
                    $groupmembers = 'AND u.id IN ('.implode(',', array_keys($groupmembers)).')';
                } else {
                    // Nobody in their groups :(
                    return false;
                }
            } else {
                // They have no group :(
                return false;
            }
        }

        // Hides course admin roles (eg: admin && course creator) if requested (default)
        if (!$dispadmins) {
            $avoidroles = array();

            if ($roles = get_roles_used_in_context($context, true)) {
                $canviewroles    = get_roles_with_capability('moodle/course:view', CAP_ALLOW, $context);
                $doanythingroles = get_roles_with_capability('moodle/site:doanything', CAP_ALLOW, $context);

                if ( ! $CFG->email_add_admins ) {
                    $adminsroles = get_roles_with_capability('moodle/legacy:admin', CAP_ALLOW, $context);
                }

                foreach ($roles as $role) {
                    if (!isset($canviewroles[$role->id])) {   // Avoid this role (eg course creator)
                        $avoidroles[] = $role->id;
                        unset($roles[$role->id]);
                        continue;
                    }
                    if (isset($doanythingroles[$role->id])) {   // Avoid this role (ie admin)
                        $avoidroles[] = $role->id;
                        unset($roles[$role->id]);
                        continue;
                    }
                    if ( ! $CFG->email_add_admins ) {
                        if (isset($adminsroles[$role->id])) {   // Avoid this role (ie admin)
                            $avoidroles[] = $role->id;
                            unset($roles[$role->id]);
                            continue;
                        }
                    }
                }
            }

            // exclude users with roles we are avoiding
            if ($avoidroles) {
                $adminroles = 'AND ra.roleid NOT IN (';
                $adminroles .= implode(',', $avoidroles);
                $adminroles .= ')';
            } else {
                $adminroles = '';
            }
        } else {
            $adminroles = '';
        }

        $results = $DB->get_records_sql("SELECT u.id, u.firstname, u.lastname, u.email
                      FROM {user} u,
                           {role_assignments} ra
                      WHERE $select AND ra.contextid $contextlists AND ra.userid = u.id
                          AND (u.firstname $LIKE '$query%' OR u.lastname $LIKE '$query%')
                          AND (u.username != :username)
                          $adminroles $groupmembers $order");
    }

	return $results;
}


/**
 * This function show if this block need update.
 *
 * @param int $version eMail List version
 * @return boolean. True if this block must update, or false if block aren't update.
 */
function email_must_update( $version ) {
	global $CFG;

	if ( $version < 2009040200 ) {
		return true;
	}
	return false;
}


/**
 * Security function. eMail List has enable for one course?
 *
 * @uses $CFG, $USER, $DB
 * @param int $course Course Id.
 * @return boolean True if eMail List block has enabled, else return false.
 */
function email_is_enabled_email_list( $courseid ) {
    global $CFG, $USER, $DB, $PAGE;

    $blockman = new block_manager($PAGE);

    return $blockman->is_block_present('email_list');

    /*if ( empty($courseid) ) {
        return false;
    }

    // Get block object
    if ($emaillist = $DB->get_record('block', array('name' => 'email_list')) ) {

		$params = array('pagetype' => PAGE_COURSE_VIEW, 'pageid' => $courseid, 'blockid' => $emaillist->id);
        // Block has enable in this course?
        $block = $DB->get_record_sql("SELECT *
                                   FROM {block_instances}
                                  WHERE pagetypepattern = :pagetype
                                    AND pageid = :pageid
                                    AND blockid = :blockid", $params);
        if (!empty($block)) {
            if ($block->visible) {
                return has_capability('moodle/block:view', get_context_instance(CONTEXT_BLOCK, $block->id));
            }
        } else if ($DB->record_exists('block_pinned', array('blockid' => $emaillist->id, 'pagetype' => 'course-view'))) {
            return has_capability('moodle/block:view', get_context_instance(CONTEXT_SYSTEM));
        } else if ($courseid == SITEID and !empty($CFG->mymoodleredirect)) {
            // Block has enable in this course?
            $params =  array('pagetype' => 'my-index', 'pageid' => $USER->id, 'blockid' => $emaillist->id );
            $block = $DB->get_record_sql("SELECT *
                                       FROM {block_instances}
                                      WHERE pagetypepattern = :pagetype
                                        AND pageid = :pageid
                                        AND blockid = :blockid", $params);
            if (!empty($block)) {
                if ($block->visible) {
                    return has_capability('moodle/block:view', get_context_instance(CONTEXT_BLOCK, $block->id));
                }
            } else if ($DB->record_exists('block_pinned', array('blockid' => $emaillist->id, 'pagetype' => 'my-index'))) {
                return has_capability('moodle/block:view', get_context_instance(CONTEXT_SYSTEM));
            }
        }
    }
    return false;*/
}




 ////////////
 // TODO: Drop function when Moodle 1.7 is unsuported version.
 ///////////
if ( !function_exists('groups_print_course_menu') ) {
	/**
	 * Print group menu selector for course level.
	 * @param object $course course object
	 * @param string $urlroot return address
	 * @param boolean $return return as string instead of printing
	 * @return mixed void or string depending on $return param
	 */
	function groups_print_course_menu($course, $urlroot, $return=false) {
	    global $CFG, $USER, $SESSION, $OUTPUT;

	    if (!$groupmode = $course->groupmode) {
	        if ($return) {
	            return '';
	        } else {
	            return;
	        }
	    }

	    $context = get_context_instance(CONTEXT_COURSE, $course->id);
	    if ($groupmode == VISIBLEGROUPS or has_capability('moodle/site:accessallgroups', $context)) {
	        $allowedgroups = groups_get_all_groups($course->id, 0, $course->defaultgroupingid);
	        // detect changes related to groups and fix active group
	        if (!empty($SESSION->activegroup[$course->id][VISIBLEGROUPS][0])) {
	            if (!array_key_exists($SESSION->activegroup[$course->id][VISIBLEGROUPS][0], $allowedgroups)) {
	                // active does not exist anymore
	                unset($SESSION->activegroup[$course->id][VISIBLEGROUPS][0]);
	            }
	        }
	        if (!empty($SESSION->activegroup[$course->id]['aag'][0])) {
	            if (!array_key_exists($SESSION->activegroup[$course->id]['aag'][0], $allowedgroups)) {
	                // active group does not exist anymore
	                unset($SESSION->activegroup[$course->id]['aag'][0]);
	            }
	        }

	    } else {
	        $allowedgroups = groups_get_all_groups($course->id, $USER->id, $course->defaultgroupingid);
	        // detect changes related to groups and fix active group
	        if (isset($SESSION->activegroup[$course->id][SEPARATEGROUPS][0])) {
	            if ($SESSION->activegroup[$course->id][SEPARATEGROUPS][0] == 0) {
	                if ($allowedgroups) {
	                    // somebody must have assigned at least one group, we can select it now - yay!
	                    unset($SESSION->activegroup[$course->id][SEPARATEGROUPS][0]);
	                }
	            } else {
	                if (!array_key_exists($SESSION->activegroup[$course->id][SEPARATEGROUPS][0], $allowedgroups)) {
	                    // active group not allowed or does not exist anymore
	                    unset($SESSION->activegroup[$course->id][SEPARATEGROUPS][0]);
	                }
	            }
	        }
	    }

	    $activegroup = groups_get_course_group($course, true);

	    $groupsmenu = array();
	    if (!$allowedgroups or $groupmode == VISIBLEGROUPS or has_capability('moodle/site:accessallgroups', $context)) {
	        $groupsmenu[0] = get_string('allparticipants');
	    }

	    if ($allowedgroups) {
	        foreach ($allowedgroups as $group) {
	            $groupsmenu[$group->id] = format_string($group->name);
	        }
	    }

	    if ($groupmode == VISIBLEGROUPS) {
	        $grouplabel = get_string('groupsvisible');
	    } else {
	        $grouplabel = get_string('groupsseparate');
	    }

	    if (count($groupsmenu) == 1) {
	        $groupname = reset($groupsmenu);
	        $output = $grouplabel.': '.$groupname;
	    } else {
	        $select = new single_select(new moodle_url($urlroot), 'group', $groupsmenu, $activegroup, null, 'selectgroup');
	        $select->label = $grouplabel;
	        $output = $OUTPUT->render($select);
	    }

	    $output = '<div class="groupselector">'.$output.'</div>';

	    if ($return) {
	        return $output;
	    } else {
	        echo $output;
	    }
	}
}

if ( !function_exists('groups_get_all_groups')) {
	/**
	 * Gets array of all groups in a specified course.
	 * @param int $courseid The id of the course.
	 * @param mixed $userid optional user id or array of ids, returns only groups of the user.
	 * @param int $groupingid optional returns only groups in the specified grouping.
	 * @return array | false Returns an array of the group objects or false if no records
	 * or an error occurred. (userid field returned if array in $userid)
	 */
	function groups_get_all_groups($courseid, $userid=0, $groupingid=0, $fields='g.*') {
	    global $CFG, $DB;

	    if (empty($userid)) {
	        $userfrom  = "";
	        $userwhere = "";
	        $params = array();

	    } else {
	        list($usql, $params) = $DB->get_in_or_equal($userid);
	        $userfrom  = ", {groups_members} gm";
	        $userwhere = "AND g.id = gm.groupid AND gm.userid $usql";
	    }

	    if (!empty($groupingid)) {
	        $groupingfrom  = ", {groupings_groups} gg";
	        $groupingwhere = "AND g.id = gg.groupid AND gg.groupingid = ?";
	        $params[] = $groupingid;
	    } else {
	        $groupingfrom  = "";
	        $groupingwhere = "";
	    }

	    array_unshift($params, $courseid);

	    return $DB->get_records_sql("SELECT $fields
	                                   FROM {groups} g $userfrom $groupingfrom
	                                  WHERE g.courseid = ? $userwhere $groupingwhere
	                               ORDER BY name ASC", $params);
	}
}

if ( !function_exists('groups_get_course_group')) {
	/**
	 * Returns group active in course, changes the group by default if 'group' page param present
	 *
	 * @global object
	 * @global object
	 * @global object
	 * @param object $course course bject
	 * @param boolean $update change active group if group param submitted
	 * @return mixed false if groups not used, int if groups used, 0 means all groups (access must be verified in SEPARATE mode)
	 */
	function groups_get_course_group($course, $update=false) {
	    global $CFG, $USER, $SESSION;

	    if (!$groupmode = $course->groupmode) {
	        // NOGROUPS used
	        return false;
	    }

	    // init activegroup array
	    if (!isset($SESSION->activegroup)) {
	        $SESSION->activegroup = array();
	    }
	    if (!array_key_exists($course->id, $SESSION->activegroup)) {
	        $SESSION->activegroup[$course->id] = array(SEPARATEGROUPS=>array(), VISIBLEGROUPS=>array(), 'aag'=>array());
	    }

	    $context = get_context_instance(CONTEXT_COURSE, $course->id);
	    if (has_capability('moodle/site:accessallgroups', $context)) {
	        $groupmode = 'aag';
	    }

	    // grouping used the first time - add first user group as default
	    if (!array_key_exists(0, $SESSION->activegroup[$course->id][$groupmode])) {
	        if ($groupmode == 'aag') {
	            $SESSION->activegroup[$course->id][$groupmode][0] = 0; // all groups by default if user has accessallgroups

	        } else if ($usergroups = groups_get_all_groups($course->id, $USER->id, $course->defaultgroupingid)) {
	            $fistgroup = reset($usergroups);
	            $SESSION->activegroup[$course->id][$groupmode][0] = $fistgroup->id;

	        } else {
	            // this happen when user not assigned into group in SEPARATEGROUPS mode or groups do not exist yet
	            // mod authors must add extra checks for this when SEPARATEGROUPS mode used (such as when posting to forum)
	            $SESSION->activegroup[$course->id][$groupmode][0] = 0;
	        }
	    }

	    // set new active group if requested
	    $changegroup = optional_param('group', -1, PARAM_INT);
	    if ($update and $changegroup != -1) {

	        if ($changegroup == 0) {
	            // do not allow changing to all groups without accessallgroups capability
	            if ($groupmode == VISIBLEGROUPS or $groupmode == 'aag') {
	                $SESSION->activegroup[$course->id][$groupmode][0] = 0;
	            }

	        } else {
	            // first make list of allowed groups
	            if ($groupmode == VISIBLEGROUPS or $groupmode == 'aag') {
	                $allowedgroups = groups_get_all_groups($course->id, 0, $course->defaultgroupingid);
	            } else {
	                $allowedgroups = groups_get_all_groups($course->id, $USER->id, $course->defaultgroupingid);
	            }

	            if ($allowedgroups and array_key_exists($changegroup, $allowedgroups)) {
	                $SESSION->activegroup[$course->id][$groupmode][0] = $changegroup;
	            }
	        }
	    }

	    return $SESSION->activegroup[$course->id][$groupmode][0];
	}
}

/**
 * This function sets the $HTTPSPAGEREQUIRED global
 * (used in some parts of moodle to change some links)
 * and calculate the proper wwwroot to be used
 *
 * Rewrited original httpsrequired by eMail security.
 * Require loginhttps.
 * @return string Protocol http or https.
 */
function email_httpsrequired() {

    global $CFG, $HTTPSPAGEREQUIRED;

    if (!empty($CFG->loginhttps) and $CFG->email_enable_ssl) {
        $HTTPSPAGEREQUIRED = true;
        $CFG->httpswwwroot = str_replace('http:', 'https:', $CFG->wwwroot);

    } else {
        $CFG->httpswwwroot = $CFG->wwwroot;
    }
    return $CFG->httpswwwroot;
}

?>