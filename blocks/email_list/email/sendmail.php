<?php  // $Id: sendmail.php,v 1.30 2010/08/31 11:32:58 tmas Exp $
/**
 * This is used to send mails.
 *
 * @author Toni Mas
 * @version $Id: sendmail.php,v 1.30 2010/08/31 11:32:58 tmas Exp $
 * @uses $CFG
 * @package email_list
 * @license The source code packaged with this file is Free Software, Copyright (C) 2010 by
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

    $mailid 		= optional_param('id', 0, PARAM_INT); 			// email ID
	$courseid		= optional_param('course', SITEID, PARAM_INT);		// Course ID
    $action 		= optional_param('action', '', PARAM_ALPHANUM); 	// Action to execute
    $folderid		= optional_param('folderid', 0, PARAM_INT); 		// folder ID
    $folderoldid	= optional_param('folderoldid', 0, PARAM_INT); 		// folder ID
	$filterid		= optional_param('filterid', 0, PARAM_INT);			// filter ID

	$olderrors		= optional_param('error', 0, PARAM_INT);
	$subject   		= optional_param('subject', '', PARAM_ALPHANUM); 	// Subject of mail
	$body   		= optional_param('body', '', PARAM_ALPHANUM); 		// Body of mail

	$mails 		= optional_param('mails', '', PARAM_ALPHANUM); 	// Next and previous mails
	$selectedusers = optional_param('selectedusers', '', PARAM_ALPHANUM); // User who send mail

	// SSL encription
	$wwwroot = email_httpsrequired();

    if (! $course = $DB->get_record('course', array('id' => $courseid)) ) {
        print_error('invalidcourseid', 'block_email_list');
    }

	require_login($course->id, false); // No autologin guest

    if ( !email_is_enabled_email_list( $course->id ) ) {
        print_error('blocknotavailable', 'block_email_list');
    }

	if ($course->id == SITEID) {
        $context = get_context_instance(CONTEXT_SYSTEM, SITEID);   // SYSTEM context
    } else {
        $context = get_context_instance(CONTEXT_COURSE, $course->id);   // Course context
    }

	// CONTRIB-626. Add capability for send messages. Thanks Jeff.
	if ( ! has_capability('block/email_list:sendmessage', $context)) {
		print_error('forbiddensendmessage', 'block_email_list', $CFG->wwwroot.'/blocks/email_list/email/index.php?id='.$course->id);
	}

    $preferencesbutton = email_get_preferences_button($courseid);

	$stremail  = get_string('name', 'block_email_list');

	$PAGE->requires->js('/blocks/email_list/email/treemenu.js');
	$PAGE->requires->js('/blocks/email_list/email/email.js');
	$PAGE->requires->css('/blocks/email_list/email/treemenu.css');
	$PAGE->requires->css('/blocks/email_list/email/tree.css');
	$PAGE->requires->css('/blocks/email_list/email/email.css');
	$PAGE->requires->css('/blocks/email_list/email/participants/autocomplete-skin.css');
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
		echo $OUTPUT->header("$navigation <a href=index.php?id=$course->id>$stremails</a> -> $strsearch");

    }

	// Options for new mail and new folder
	$options = new stdClass();
	$options->id = $courseid;
	$options->mailid = $mailid;
	$options->folderid = $folderid;
	$options->filterid = $filterid;
	$options->folderoldid = $folderoldid;

	// Fields of error mail (only use when created new email and it's insert fail)
	$fieldsmail = new stdClass();
	$fieldsmail->subject = $subject;
	$fieldsmail->body = $body;

	/// Print the main part of the page

	// Print principal table. This have 2 columns . . .  and possibility to add right column.
	echo '<table id="layout-table">
  			<tr>';


	// Print "blocks" of this account
	echo '<td style="width: 180px;" id="left-column">';
	email_printblocks($USER->id, $courseid);

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

	// Print tabs options
	email_print_tabs_options($courseid, $folderid, $action);

	include_once('mail_edit_form.php');

	// Solve bug
	if ( ! isset( $mail->body ) ) {
		$mail->body = '';
	}

	if ( ! isset( $mail->subject ) ) {
		$mail->subject = '';
	}

	/// first create the form
	$mailform = new mail_edit_form('sendmail.php', array('oldmail' => $DB->get_record('block_email_list_mail', array('id' => $mailid)), 'action' => $action), 'post', '', array('name' => 'sendmail'));

	if ( $mailform->is_cancelled() ) {
		// Only redirect
		redirect($CFG->wwwroot.'/blocks/email_list/email/index.php?id='.$courseid, '', '0');
	} else if ( $form = $mailform->get_data() ) {
		if ( empty($form->to) and empty($form->cc) and empty($form->bcc) ) {
			echo $OUTPUT->notification(get_string('nosenders', 'block_email_list'), '');
			$mailform->set_data($form);
			$mailform->display();
		} else 	if (! empty($form->send) or ! empty($form->draft)) {

			// Create new eMail
			$email = new eMail();

			// User send mail
			$email->set_writer($USER->id);
			$email->set_course($courseid);

			// Generic URL for send mails errors
			$baseurl =  $CFG->wwwroot.'/blocks/email_list/email/index.php?id='.$courseid.'&amp;mailid='.$form->id.'&amp;subject=\''.$form->subject.'\'&amp;body=\''.$form->body.'\'';

			// Add subject
			$email->set_subject($form->subject);

	    	// Get upload file's
	    	$email->set_attachments(isset($_FILES) ? $_FILES : NULL);

	    	// If forward or draft mail, can do old attachments
	    	$i = 0;
	    	$oldattach = 'oldattachment'.$i;
	    	if ( isset($form->$oldattach) ) {
	    		$attachments = array();
	    		while (true) {
	    			$oldattachck = $oldattach.'ck';
	    			// Only add if it is checked
	    			if ( isset($form->$oldattachck) and $form->$oldattachck ) {
	    				$attachments[] = $form->$oldattach;
 	    			}
	    			$i++;
	    			$oldattach = "oldattachment$i";
	    			if ( empty($form->$oldattach ) ) {
	    				break;
	    			}
	    		}

	    		$email->set_oldattachments($attachments);
	    	}

			// Add body
			$email->set_body($form->body);

			// Add users sent mail
			if ( isset($form->to) ) {
				$email->set_sendusersbyto($form->to);
			}
			if ( isset($form->cc) ) {
				$email->set_sendusersbycc($form->cc);
			}
			if ( isset($form->bcc) ) {
				$email->set_sendusersbybcc($form->bcc);
			}

			// Add type action and if corresponding old mail id.
			$email->set_type( $form->action );
			$email->set_oldmailid ((isset($form->oldmailid) ) ? $form->oldmailid : NULL ); // Fixme.
			$email->set_responded ((isset($form->respondedid) and $form->respondedid > 0 ) ? $form->respondedid : $form->oldmailid ); // Change oldmail to responded.
			$email->set_mailid ((isset($form->id) ) ? $form->id : NULL );

			// Add new mail, in the Inbox or corresponding folder
			if ( empty($form->draft) ) {

				if ( isset($form->action) ) {
					if ( $form->action == EMAIL_FORWARD ) {
						$form->oldmailid = NULL;	// Drop mailid on forward
					}
				}

				if (! $email->send() ) {
					echo $OUTPUT->notification('Don\'t send mail', '');
				}
			} else {
				// Save in Draft

				// Fix forward bug. Thanks Ann
				if ( isset($form->action) ) {
					if ( $form->action == EMAIL_FORWARD ) {
						$form->oldmailid = NULL; // Drop mailid on forward
					}

					// CONTRIB-702
					if ( $form->action == EMAIL_REPLY or $form->action == EMAIL_REPLYALL or $form->action == EMAIL_FORWARD) {
						unset($form->id); // If you don't unset id, save eMail update this record.
					}
				}

				if (! $email->save((isset($form->id) ) ? $form->id : NULL ) ) {
					echo $OUTPUT->notification('Don\'t save mail in my draft', '');
				}
			}

			if ( empty($form->draft) ) {
				$legend = get_string('sendok', 'block_email_list');
			} else {
				$legend = get_string('draftok', 'block_email_list');
			}

			redirect($CFG->wwwroot.'/blocks/email_list/email/index.php?id='.$courseid, $legend, '4');

		} else {
			print_error ( 'Fatal error when sending or draft mail');
		}

    } else {

// DRAFT integration

    	// Prepare mail according action

    	if ( $action == EMAIL_REPLY or $action == EMAIL_REPLYALL or $action == EMAIL_EDITDRAFT ) {
			if ( ! $mail = $DB->get_record('block_email_list_mail', array('id' => $mailid)) ) {
				print_error ('Mail not found');
			}
    	}

		$nosenders = false;
		$nosubject = false;
		$formoldattachments = false;

		if ( $action == EMAIL_FORWARD ) {
			$selectedusers = array();
		}

		if ( $action == EMAIL_REPLY ) {
			// Predefinity user send
			$user = email_get_user($mailid);
			$mail->nameto = email_fullname($user, $context).', ';
		}

		if ( $action == EMAIL_REPLYALL ) {

			$selectedusers = array();
			// Predefinity user send
			$userwriter = email_get_user($mailid);

			// First, prepare writer
			$selectedusers[] = $userwriter->id;

			// Get users sent mail, with option for reply all
			$selectedusersto = $selectedusers;

			$mail->nameto = '';
			foreach ( $selectedusersto as $userid ) {
				$mail->nameto .= email_fullname($DB->get_record('user', array('id' => $userid)), $context) .', ';
			}

			// Get users sent mail, with option for reply all
			$selecteduserscc = array_merge(email_get_users_sent($mailid, true, $userwriter, 'to'), email_get_users_sent($mailid, true, $userwriter, 'cc'));

			$mail->namecc = '';
			foreach ( $selecteduserscc as $userid ) {
				$mail->namecc .= email_fullname($DB->get_record('user', array('id' => $userid)), $context) .', ';
			}
		}

		if ( $action == EMAIL_FORWARD ) {
			$newmail = new stdClass();

			// Get mail
			if ( ! $oldmail = $DB->get_record('block_email_list_mail', array('id' => $mailid))) {
				error ('Can\'t found mail');
			}

			$newmail = (PHP_VERSION < 5) ? $oldmail : clone($oldmail);

			// Remove id
			unset($newmail->id);
			$newmail->mailid = NULL;

			// Predefinity user send
			$user = email_get_user($mailid);

		}

		if ( $action == EMAIL_EDITDRAFT ) {
			// Predefinity user send
			$userwriter = email_get_user($mailid);

			// Get users sent mail, with option for reply all
			$selectedusersto = email_get_users_sent($mailid, true, false, 'to');

			$mail->nameto = '';
			foreach ( $selectedusersto as $userid ) {
				$mail->nameto .= email_fullname($DB->get_record('user', array('id' => $userid)), $context) .', ';
			}

			// Get users sent mail, with option for reply all
			$selecteduserscc = email_get_users_sent($mailid, true, false, 'cc');

			$mail->namecc = '';
			foreach ( $selecteduserscc as $userid ) {
				$mail->namecc .= email_fullname($DB->get_record('user', array('id', $userid)), $context) .', ';
			}

			// Get users sent mail, with option for reply all
			$selectedusersbcc = email_get_users_sent($mailid, true, false, 'bcc');

			$mail->namebcc = '';
			foreach ( $selectedusersbcc as $userid ) {
				$mail->namebcc .= email_fullname($DB->get_record('user', array('id' => $userid)), $context) .', ';
			}
		}

		if ( $action == EMAIL_REPLY or $action == EMAIL_REPLYALL ) {
			// Modify subject
			$mail->subject = get_string('re', 'block_email_list').' '.$mail->subject;
		}

		if ( $action == EMAIL_FORWARD ) {
			// Modify subject
			$newmail->subject = get_string('fw', 'block_email_list').' '.$oldmail->subject;
		}

		if ( $action == EMAIL_REPLY or $action == EMAIL_REPLYALL ) {
			// Separe message in diferents lines, who add >
			$lines = explode('<br />', $mail->body);

			( isset($user) ) ? $userdef = $user : $userdef = $userwriter;

			// Insert default line for known sended mail, and date
			$body =  email_make_default_line_replyforward($userdef, $mail->timecreated, $context);
			// Intert >
			foreach($lines as $line ) {
				$body = $body. '>' .$line. '<br />'."\n";
			}
			// Assign new body
			$mail->body = $body;
		}

		if ( $action == EMAIL_FORWARD ) {
			// Separe message in diferents lines, who add >
			$lines = explode('<br />', $newmail->body);

			// Insert default line for known sended mail, and date
			$body =  email_make_default_line_replyforward($user, $newmail->timecreated, $context);
			// Intert >
			foreach($lines as $line ) {
				$body = $body. '>' .$line. '<br />'."\n";
			}
			// Assign new body
			$newmail->body = $body;

			// Add oldmail
			$newmail->oldmail = $mailid;
		}

		if ( $action == EMAIL_REPLY ) {
			// Add log
			add_to_log($mail->course, 'email_list', 'reply', '', "$mail->subject", 0, $mail->userid);
		}

		if ( $action == EMAIL_REPLYALL ) {
			add_to_log($mail->course, 'email_list', 'reply all', '', "$mail->subject", 0, $mail->userid);
		}

		if ( $action == EMAIL_FORWARD ) {
			add_to_log($newmail->course, 'email_list', 'forward', '', "$newmail->subject", 0, $newmail->userid);
		}

		if ( $action == EMAIL_REPLY or $action == EMAIL_REPLYALL ) {
			$mail->action = EMAIL_REPLY;
			$mailform->set_data($mail);
		}
		if ( $action == EMAIL_FORWARD ) {
			$mailform->set_data($newmail);
		}

		if ( $action == EMAIL_EDITDRAFT ) {
			$mailform->set_data($mail);
		}

		if ( $action == EMAIL_REPLY or $action == EMAIL_REPLYALL or $action == EMAIL_FORWARD ) {
			$mailform->focus('body');
		} else {
			$mailform->focus('subject');
		}

    	$mailform->display();

        if ( $CFG->email_enable_ajax && ajaxenabled($CFG->ajaxtestedbrowsers)) {
            require_once($CFG->dirroot.'/blocks/email_list/email/participants/yui_autocomplete.html');
        }

    	if ( $action == EMAIL_REPLY ) {
    		if ( $CFG->email_enable_ajax ) {
	    		echo '<script type="text/javascript" language="JavaScript">var input = \'<input type="hidden" value="'.$user->id.'" name="to[]">\';' .
	    				'var rand = get_random();' .
	    				'var fullname = window.document.createElement("span");' .
	    				'fullname.setAttribute("id", "id_nameto"+rand);' .
	    				'fullname.setAttribute("class", "ajax_user_selected");' .
	    				'window.document.getElementById("id_nameto").parentNode.insertBefore(fullname,window.document.getElementById("id_nameto"));' .
	    				'var htmlimg = "<img class=\"imgdropuser\" src=\"'.$wwwroot.'/blocks/email_list/email/images/cross.png\" height=\"11\" width=\"11\" alt=\"'.get_string('remove','block_email_list').'\" />";' .
	    				'fullname.innerHTML = "'.fullname($user, $context).'<a href=\"#\" onClick=\"removeAJAXContact(\'id_nameto"+rand+"\');\">"+htmlimg+input+"</a>";</script>';
    		} else {
    			echo ' <script type="text/javascript" language="JavaScript"> var contacts = window.document.createElement("span");
			        	window.document.getElementById(\'id_nameto\').parentNode.appendChild(contacts);
			        	contacts.innerHTML = \'<input type="hidden" value="'.$user->id.'" name="to[]">\';</script>';
    		}
    	}

    	if ( $action == EMAIL_REPLYALL ) {
    		echo ' <script type="text/javascript" language="JavaScript">';
		    foreach ( $selectedusersto as $selecteduser ) {
		    	if ( $CFG->email_enable_ajax ) {
			    	echo 'var input = \'<input type="hidden" value="'.$selecteduser.'" name="to[]">\';' .
	    				'var rand = get_random();' .
	    				'var fullname = window.document.createElement("span");' .
	    				'fullname.setAttribute("id", "id_nameto"+rand);' .
	    				'fullname.setAttribute("class", "ajax_user_selected");' .
	    				'window.document.getElementById("id_nameto").parentNode.insertBefore(fullname,window.document.getElementById("id_nameto"));' .
	    				'var htmlimg = "<img class=\"imgdropuser\" src=\"'.$wwwroot.'/blocks/email_list/email/images/cross.png\" height=\"11\" width=\"11\" alt=\"'.get_string('remove','block_email_list').'\" />";' .
	    				'fullname.innerHTML = "'.fullname($DB->get_record('user', array('id' => $selecteduser)), $context).'<a href=\"#\" onClick=\"removeAJAXContact(\'id_nameto"+rand+"\');\">"+htmlimg+input+"</a>";';
		    	} else {
		    		echo 'var contacts = window.document.createElement("span");
			        		window.document.getElementById(\'id_nameto\').parentNode.appendChild(contacts);
			        		contacts.innerHTML = \'<input type="hidden" value="'.$selecteduser.'" name="to[]">\';';
		    	}
		    }
		    foreach ( $selecteduserscc as $selecteduser ) {
		    	if ( $CFG->email_enable_ajax ) {
			    	echo 'var input = \'<input type="hidden" value="'.$selecteduser.'" name="cc[]">\';' .
	    				'var rand = get_random();' .
	    				'var fullname = window.document.createElement("span");' .
	    				'fullname.setAttribute("id", "id_namecc"+rand);' .
	    				'fullname.setAttribute("class", "ajax_user_selected");' .
	    				'window.document.getElementById("id_namecc").parentNode.insertBefore(fullname,window.document.getElementById("id_namecc"));' .
	    				'var htmlimg = "<img class=\"imgdropuser\" src=\"'.$wwwroot.'/blocks/email_list/email/images/cross.png\" height=\"11\" width=\"11\" alt=\"'.get_string('remove','block_email_list').'\" />";' .
	    				'fullname.innerHTML = "'.fullname($DB->get_record('user', array('id' => $selecteduser)), $context).'<a href=\"#\" onClick=\"removeAJAXContact(\'id_namecc"+rand+"\');\">"+htmlimg+input+"</a>";';
		    	} else {
		    		echo 'var contactscc = window.document.createElement("span");
			        		window.document.getElementById(\'id_namecc\').parentNode.appendChild(contactscc);
			        		contactscc.innerHTML = \'<input type="hidden" value="'.$selecteduser.'" name="cc[]">\';';
		    	}
		    }
		    echo '</script>';
    	}

    	if ( $action == EMAIL_EDITDRAFT ) {
    		echo ' <script type="text/javascript" language="JavaScript">';
		    foreach ( $selectedusersto as $selecteduser ) {
		    	if ( $CFG->email_enable_ajax ) {
		    		echo 'var input = \'<input type="hidden" value="'.$selecteduser.'" name="to[]">\';' .
	    				'var rand = get_random();' .
	    				'var fullname = window.document.createElement("span");' .
	    				'fullname.setAttribute("id", "id_nameto"+rand);' .
	    				'fullname.setAttribute("class", "ajax_user_selected");' .
	    				'window.document.getElementById("id_nameto").parentNode.insertBefore(fullname,window.document.getElementById("id_nameto"));' .
	    				'var htmlimg = "<img class=\"imgdropuser\" src=\"'.$wwwroot.'/blocks/email_list/email/images/cross.png\" height=\"11\" width=\"11\" alt=\"'.get_string('remove','block_email_list').'\" />";' .
	    				'fullname.innerHTML = "'.fullname($DB->get_record('user', array('id' => $selecteduser)), $context).'<a href=\"#\" onClick=\"removeAJAXContact(\'id_nameto"+rand+"\');\">"+htmlimg+input+"</a>";';
		    	} else {
		    		echo 'var contacts = window.document.createElement("span");
			    	    	window.document.getElementById(\'id_nameto\').parentNode.appendChild(contacts);
							contacts.innerHTML = \'<input type="hidden" value="'.$selecteduser.'" name="to[]">\';';
		    	}
		    }
		    foreach ( $selecteduserscc as $selecteduser ) {
		    	if ( $CFG->email_enable_ajax ) {
			    	echo 'var input = \'<input type="hidden" value="'.$selecteduser.'" name="cc[]">\';' .
	    				'var rand = get_random();' .
	    				'var fullname = window.document.createElement("span");' .
	    				'fullname.setAttribute("id", "id_namecc"+rand);' .
	    				'fullname.setAttribute("class", "ajax_user_selected");' .
	    				'window.document.getElementById("id_namecc").parentNode.insertBefore(fullname,window.document.getElementById("id_namecc"));' .
	    				'var htmlimg = "<img class=\"imgdropuser\" src=\"'.$wwwroot.'/blocks/email_list/email/images/cross.png\" height=\"11\" width=\"11\" alt=\"'.get_string('remove','block_email_list').'\" />";' .
	    				'fullname.innerHTML = "'.fullname($DB->get_record('user', array('id' => $selecteduser)), $context).'<a href=\"#\" onClick=\"removeAJAXContact(\'id_namecc"+rand+"\');\">"+htmlimg+input+"</a>";';
		    	} else {
					echo 'var contacts = window.document.createElement("span");
			    	    	window.document.getElementById(\'id_namecc\').parentNode.appendChild(contacts);
						 	contacts.innerHTML = \'<input type="hidden" value="'.$selecteduser.'" name="cc[]">\';';
		    	}
		    }
		    foreach ( $selectedusersbcc as $selecteduser ) {
		    	if ( $CFG->email_enable_ajax ) {
			    	echo 'var input = \'<input type="hidden" value="'.$selecteduser.'" name="bcc[]">\';' .
	    				'var rand = get_random();' .
	    				'var fullname = window.document.createElement("span");' .
	    				'fullname.setAttribute("id", "id_namebcc"+rand);' .
	    				'fullname.setAttribute("class", "ajax_user_selected");' .
	    				'window.document.getElementById("id_namebcc").parentNode.insertBefore(fullname,window.document.getElementById("id_namebcc"));' .
	    				'var htmlimg = "<img class=\"imgdropuser\" src=\"'.$wwwroot.'/blocks/email_list/email/images/cross.png\" height=\"11\" width=\"11\" alt=\"'.get_string('remove','block_email_list').'\" />";' .
	    				'fullname.innerHTML = "'.fullname($DB->get_record('user', array('id' => $selecteduser)), $context).'<a href=\"#\" onClick=\"removeAJAXContact(\'id_namebcc"+rand+"\');\">"+htmlimg+input+"</a>";';
		    	} else  {
					echo 'var contacts = window.document.createElement("span");
			   			    window.document.getElementById(\'id_namebcc\').parentNode.appendChild(contacts);
							contacts.innerHTML = \'<input type="hidden" value="'.$selecteduser.'" name="bcc[]">\';';
		    	}
		    }
		    echo '</script>';
    	}

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
