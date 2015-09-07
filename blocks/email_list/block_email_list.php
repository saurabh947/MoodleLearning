<?php

require_once($CFG->dirroot .'/blocks/email_list/email/lib.php');

/**
 * This block shows information about user email's
 *
 * @author Sergio Sama
 * @author Toni Mas
 * @version 1.1
 * @package email_list
 **/
class block_email_list extends block_list {

	function init() {
		$this->title = get_string('pluginname', 'block_email_list');
	}

	function get_content() {
		global $USER, $CFG, $COURSE, $DB, $OUTPUT;

		// Get course id
		if ( ! empty($COURSE) ) {
            $this->courseid = $COURSE->id;
        }

		// If block have content, skip.
		if ($this->content !== NULL) {
			return $this->content;
		}

		$this->content = new stdClass;
		$this->content->items = array();
		$this->content->icons = array();

		// Security.
		$wwwroot = email_httpsrequired();

		$emailicon = '<img src="'.$wwwroot.'/blocks/email_list/email/images/sobre.png" height="11" width="15" alt="'.get_string("course").'" />';
		$pixpath = "$CFG->wwwroot/pix";
		$composeicon = '<img src="'.$pixpath.'/i/edit.gif" alt="" />';

		$spacer = array('height'=>1, 'width'=> 4);

		// Only show all course in principal course, others, show it
		if ( $this->page->course->id == SITEID ) {
			//Get the courses of the user
			$mycourses = enrol_get_my_courses();
			$this->content->footer = '<br />'.$emailicon.$OUTPUT->spacer($spacer).'<a href="'.$wwwroot.'/blocks/email_list/email/">'.get_string('view_all', 'block_email_list').'</a>';
		} else {

			if (! empty($CFG->mymoodleredirect) and $COURSE->id == 1 ) {
				//Get the courses of the user
				$mycourses = get_my_courses($USER->id);
				$this->content->footer = '<br />'.$emailicon.$OUTPUT->spacer($spacer).'<a href="'.$wwwroot.'/blocks/email_list/email/">'.get_string('view_all', 'block_email_list').'</a>';
			} else {
				// Get this course
				$course = $DB->get_record('course',array('id' => $this->page->course->id));
				$mycourses[] = $course;
				$this->content->footer = '<br />'.$emailicon.$OUTPUT->spacer($spacer).'<a href="'.$wwwroot.'/blocks/email_list/email/index.php?id='.$course->id.'">'.get_string('view_inbox', 'block_email_list').'</a>';
				$this->content->footer .= '<br />'.$composeicon.$OUTPUT->spacer($spacer).'<a href="'.$wwwroot.'/blocks/email_list/email/sendmail.php?course='.$course->id.'&amp;folderid=0&amp;filterid=0&amp;folderoldid=0&amp;action=newmail">'.get_string('compose', 'block_email_list').'</a>';
			}
		}

		// Count my courses
		$countmycourses = count($mycourses);

		//Configure item and icon for this account
		$icon = '<img src="'.$wwwroot.'/blocks/email_list/email/images/openicon.gif" height="16" width="16" alt="'.get_string("course").'" />';

		$number = 0;
		foreach( $mycourses as $mycourse ) {

			++$number; // increment for first course

			if ( $number > $CFG->email_max_number_courses && !empty($CFG->email_max_number_courses) ) {
				continue;
			}
			//Get the number of unread mails
			$numberunreadmails = email_count_unreaded_mails($USER->id, $mycourse->id);
			$coursename = $CFG->email_display_course_fullname ? $mycourse->fullname : $mycourse->shortname;

			// Only show if has unreaded mails
			if ( $numberunreadmails > 0 ) {

				$unreadmails = '<b>('.$numberunreadmails.')</b>';
				$this->content->items[] = '<a href="'.$wwwroot.'/blocks/email_list/email/index.php?id='.$mycourse->id.'">'.$coursename.' '. $unreadmails.'</a>';
				$this->content->icons[] = $icon;
			}
		}

		if ( count( $this->content->items ) == 0 ) {
			$this->content->items[] = '<div align="center">'.get_string('emptymailbox', 'block_email_list').'</div>';
		}

		return $this->content;
	}

	function applicable_formats() {
        return array('all' => true, 'mod' => false, 'tag' => false);
    }

	function has_config() {
        return true;
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
    function cron() {

		global $CFG, $DB;

		// If no isset trackbymail, return cron.
		if ( !isset($CFG->email_trackbymail) ) {
			return true;
		}

		// If NOT enabled
		if ( $CFG->email_trackbymail == 0 ) {
			return true;
		}

		// Get actualtime
		$now = time();

		// Get record for mail list
		if ( $block = $DB->get_record('block', array('name' => 'email_list')) ) {

			if ( $now > $block->lastcron ) {

				$unreadmails = new stdClass();

				// Get users who have unread mails
				$from = "{user} u,
						 {block_email_list_send} s,
						 {block_email_list_mail} m";

				$params = array('timecreated' => $block->lastcron, 'readed' => 0, 'sended' => 1);
				$where = " WHERE u.id = s.userid
								AND s.mailid = m.id
								AND m.timecreated > :timecreated
								AND s.readed = :readed
								AND s.sended = :sended";

				// If exist any users
				if ( $users = $DB->get_records_sql('SELECT u.* FROM '.$from.$where, $params) ) {

					// For each user ... get this unread mails, and send alert mail.
					foreach ( $users as $user ) {

						$mails = new stdClass();

						// Preferences! Can send mail?
						// Case:
						// 		1.- Site allow send trackbymail
						//			1.1.- User doesn't define this settings -> Send mail
						//			1.2.- User allow trackbymail -> Send mail
						//			1.3.- User denied trackbymail -> Don't send mail

						// User can definied this preferences?
						if ( $preferences = $DB->get_record('block_email_list_preference', array('userid' => $user->id)) ) {
							if ( $preferences->trackbymail == 0 ) {
								continue;
							}
						}


						// Get this unread mails
						if ( $mails = email_get_unread_mails($user->id) ) {

							$bodyhtml = '<head>';
							foreach ($CFG->stylesheets as $stylesheet) {
						        $bodyhtml .= '<link rel="stylesheet" type="text/css" href="'.$stylesheet.'" />'."\n";
						    }

						    $bodyhtml .= '</head>';
	    					$bodyhtml .= "\n<body id=\"email\">\n\n";


							$bodyhtml .= '<div class="content">'.get_string('listmails', 'block_email_list').": </div>\n\n";
							$body = get_string('listmails', 'block_email_list')  .": \n\n";

							$bodyhtml .= '<table border="0" cellpadding="3" cellspacing="0">';
							$bodyhtml .= '<th class="header">'.get_string('course').'</th>';
							$bodyhtml .= '<th class="header">'.get_string('subject','block_email_list').'</th>';
							$bodyhtml .= '<th class="header">'.get_string('from','block_email_list').'</th>';
							$bodyhtml .= '<th class="header">'.get_string('date','block_email_list').'</th>';

							// Prepare messagetext
							foreach ( $mails as $mail ) {

								// Get folder
								$folder = email_get_root_folder($mail->userid, EMAIL_SENDBOX);
								if ( ! email_isfolder_type($folder, EMAIL_SENDBOX) ) {
									continue;
								}

								if ( isset($mail->mailid) ) {
									$message = $DB->get_record('block_email_list_mail', array('id' => $mail->mailid));
									$mailcourse = $DB->get_record('course', array('id' => $mail->course));

									$body .= "---------------------------------------------------------------------\n";
									$body .= get_string('course').": $mailcourse->fullname \n";
									$body .= get_string('subject','block_email_list').": $message->subject \n";
									$body .= get_string('from', 'block_email_list').": ".fullname(email_get_user($message->id));
									$body .= " - ".userdate($message->timecreated)."\n";
									$body .= "---------------------------------------------------------------------\n\n";


									$bodyhtml .= '<tr  class="r0">';
									$bodyhtml .= '<td class="cell c0">'.$mailcourse->fullname .'</td>';
									$bodyhtml .= '<td class="cell c0">'.$message->subject .'</td>';
									$bodyhtml .= '<td class="cell c0">'.fullname(email_get_user($message->id)).'</td>';
									$bodyhtml .= '<td class="cell c0">'.userdate($message->timecreated).'</td>';
									$bodyhtml .= '</tr>';
								}
							}

							$bodyhtml .= '</table>';
							$bodyhtml .= '</body>';

							$body .= "\n\n\n\n";

							email_to_user($user, get_string('emailalert', 'block_email_list'),
											get_string('emailalert', 'block_email_list').': '.get_string('newmails', 'block_email_list'),
											$body, $bodyhtml);
						}
					}
				}

			}

    		return true;
		} else {
			mtrace('FATAL ERROR: I couldn\'t read eMail list block');
			return false;
		}
    }

    function backuprestore_instancedata_used() {
        return true;
    }

    /**
     * Backup emails
     *
     * @return boolean
     **/
    function instance_backup($bf, $preferences) {

        global $CFG;

        $status = true;

        if ($preferences->backup_users == 0 or $preferences->backup_users == 1) {

            require_once("$CFG->dirroot/blocks/email_list/email/backuplib.php");

            //are there any emails to backup?
            $courseid = $this->instance->pageid;

            $status = email_backup_instance($bf, $preferences, $courseid);

        }
        return $status;
    }

    /**
     * Restore routine
     *
     * @return boolean
     **/
    function instance_restore($restore, $data) {

        $status = true;

        if ($restore->users != 0 and $restore->users != 1) {
            return $status;
        }

        global $CFG;

        require_once("$CFG->dirroot/blocks/email_list/email/restorelib.php");

        $status = email_restore_instance($data, $restore);

        return $status;
    }

    /**
     * Before delete, drop all log records
     */
    function before_delete() {
		global $DB;

    	// Delete all email records to log_display
    	$DB->delete_records('log_display', array('module' => 'email_list'));


    }

    /**
     * When instance course has deleted, deleting all email_list course content.
     */
    function instance_delete() {

		/* When delete instance. Drop:
		 * 		- Folders associated at this course.
		 * 		- Mails of this course and all attachments.
		 * 		- Colaterals effects:
		 * 			# Drop all mail-folder references. (email_foldermail)
		 * 			# Drop all sendbox/inbox (email_send)
		 * 			# Drop all subfolders associated at this course.
		 * 			# Drop all filters.
		 */

		global $CFG, $DB;

		// Get course id
		if ( ! empty($this->instance) ) {
        	$courseid = $this->instance->pageid;
        } else {
        	return true; // Skip
        }

        // Do not process SITEID
        if ( $courseid === SITEID ) {
        	return true; // Skip
        }

        // Get all email references.
        if ( $mailids = $DB->get_records('block_email_list_mail', array('course' => $courseid)) ) {
        	foreach( $mailids as $mailid ) {
        		$DB->delete_records('block_email_list_foldermail', array('mailid' => $mailid));
        	}
        }

        // Drop all mails
        $DB->delete_records('block_email_list_mail', array('course' => $courseid));

    	if ( $folders = $DB->get_records('block_email_list_folder', array('course' => $courseid)) ) {
			foreach ( $folders as $folder ) {
    			// If user have defined who has associated folders-course, then delete all folders to this course.
				if ( email_have_asociated_folders($folder->userid) ) {
					if ( ! email_removefolder($folder->id) ) {
						print_error('faildroprecordsfolder', 'block_email_list');
					}
				}
			}
		}

		return true;

    }
}
?>
