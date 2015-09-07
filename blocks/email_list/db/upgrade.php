<?php

// This file keeps track of upgrades to
// the email
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_block_email_list_upgrade($oldversion=0) {

    global $CFG, $THEME, $DB;

    $dbman = $DB->get_manager(); /// loads ddl manager and xmldb classes

    $result = true;

    // If is set upgrade_blocks_savepoint function
    $existfunction = false;
    if (!function_exists('upgrade_blocks_savepoint') ) {
        $existfunction = true;
    }

/// And upgrade begins here. For each one, you'll need one
/// block of code similar to the next one. Please, delete
/// this comment lines once this file start handling proper
/// upgrade code.

	if ($result && $oldversion < 2007062205) {
		$fields = array(
						'mod/email:viewmail',
						'mod/email:addmail',
						'mod/email:reply',
						'mod/email:replyall',
						'mod/email:forward',
						'mod/email:addsubfolder',
						'mod/email:updatesubfolder',
						'mod/email:removesubfolder'	);

		/// Remove no more used fields
        $table = new xmldb_table('capabilities');

        foreach ($fields as $name) {

            $field = new xmldb_field($name);
            $result = $result && $dbman->drop_field($table, $field);
        }

        // Active cron block of email_list
        if ( $result ) {
        	if ( $email_list = $DB->get_record('block', array('name' => 'email_list')) ) {
        		$email_list->cron = 1;
        		update_record('block',$email_list);
        	}
        }

        if ( $existfunction ) {
			/// Block savepoint reached
			upgrade_blocks_savepoint($result, 2007062205, 'email_list');
        }

	}

	// force
	$result = true;

	if ($result && $oldversion < 2007072003) {
		// Add marriedfolder2courses flag on email_preferences
		$table = new xmldb_table('email_preference');

		$field = new xmldb_field('marriedfolders2courses');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);

        $result = $result && $dbman->add_field($table, $field);


        // Add course ID on email_folder
        $table = new xmldb_table('email_folder');

		$field = new xmldb_field('course');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);

        $result = $result && $dbman->add_field($table, $field);

		// Add index
        $key = new xmldb_key('course');
        $key->set_attributes(XMLDB_KEY_FOREIGN, array('course'), 'course', array('id'));

        $result = $result && $dbman->add_key($table, $key);

        if ( $existfunction ) {
			/// Block savepoint reached
			upgrade_blocks_savepoint($result, 2007072003, 'email_list');
        }

	}

	if ($result && $oldversion < 2008061400 ) {

		// Add reply and forwarded info field on email_mail.
		$table = new xmldb_table('email_send');

		$field = new xmldb_field('answered');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL);

        $result = $result && $dbman->add_field($table, $field);

        if ( $existfunction ) {
			/// Block savepoint reached
			upgrade_blocks_savepoint($result, 2008061400, 'email_list');
        }
	}

	// Solve old problems
	if ($result && $oldversion < 2008061600 ) {
		$table = new xmldb_table('email_preference');
		$field = new xmldb_field('marriedfolders2courses');

		if ( !$dbman->field_exists($table, $field) ) {
			$field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);

        	$result = $result && $dbman->add_field($table, $field);
		}

		$table = new xmldb_table('email_folder');

		$field = new xmldb_field('course');

		if ( !$dbman->field_exists($table, $field) ) {
	        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);

	        $result = $result && $dbman->add_field($table, $field);

			// Add index
	        $key = new xmldb_key('course');
	        $key->set_attributes(XMLDB_KEY_FOREIGN, array('course'), 'course', array('id'));

	        $result = $result && $dbman->add_key($table, $key);
		}

		if ( $existfunction ) {
			/// Block savepoint reached
			upgrade_blocks_savepoint($result, 2008061600, 'email_list');
		}

	}

	// Add new index
	if ( $result and $oldversion < 2008081602 ) {
		// Add combine key on foldermail
        $table = new xmldb_table('email_foldermail');
        $index = new xmldb_index('folderid-mailid');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('folderid', 'mailid'));

        if (!$dbman->index_exists($table, $index)) {
        /// Launch add index
            $result = $result && $dbman->add_index($table, $index);
        }

        if ( $existfunction ) {
			/// Block savepoint reached
			upgrade_blocks_savepoint($result, 2008081602, 'email_list');
        }

	}

	// Upgrading to Moodle 2.0
	if ( $result and $oldversion < 2009040200 ) {

		// Portable SQL staments to Oracle, MySQL and PostgreSQL NOT APPLYCABLE to MSSQL
		if ( $CFG->dbname != 'mssql' ) {
			// Moodle 1.9 or prior
			if ($CFG->version < '2009011541') {
				// Filter
				$result = $DB->execute_sql("ALTER TABLE {email_filter} RENAME TO {block_email_list_filter}");

				// Folder
				$result = $DB->execute_sql("ALTER TABLE {email_folder} RENAME TO {block_email_list_folder}") && $result;

				// Foldermail
				$result = $DB->execute_sql("ALTER TABLE {email_foldermail} RENAME TO {block_email_list_foldermail}") && $result;

				// Mail
				$result = $DB->execute_sql("ALTER TABLE {email_mail} RENAME TO {block_email_list_mail}") && $result;

				// Preference
				$result = $DB->execute_sql("ALTER TABLE {email_preference} RENAME TO {block_email_list_preference}") && $result;

				// Send
				$result = $DB->execute_sql("ALTER TABLE {email_send} RENAME TO {block_email_list_send}") && $result;

				// Subfolder
				$result = $DB->execute_sql("ALTER TABLE {email_subfolder} RENAME TO {block_email_list_subfolder}") && $result;
			} else {
				// Filter
				$DB->execute("ALTER TABLE {email_filter} RENAME TO {block_email_list_filter}");

				// Folder
				$DB->execute("ALTER TABLE {email_folder} RENAME TO {block_email_list_folder}");

				// Foldermail
				$DB->execute("ALTER TABLE {email_foldermail} RENAME TO {block_email_list_foldermail}");

				// Mail
				$DB->execute("ALTER TABLE {email_mail} RENAME TO {block_email_list_mail}");

				// Preference
				$DB->execute("ALTER TABLE {email_preference} RENAME TO {block_email_list_preference}");

				// Send
				$DB->execute("ALTER TABLE {email_send} RENAME TO {block_email_list_send}");

				// Subfolder
				$DB->execute("ALTER TABLE {email_subfolder} RENAME TO {block_email_list_subfolder}");
			}
		}

		// Change module name to Standard eMail name.
		if ( $logs = $DB->get_records('log_display', array('module' => 'email')) ) {
			foreach ( $logs as $log ) {
				$DB->set_field('log_display', array('module' => 'block_email_list'), array('id' => $log->id));
			}
		}

		// Only compatible with 1.9 or prior versions
		if ( $existfunction ) {
			/// Block savepoint reached
			upgrade_blocks_savepoint($result, 2009040200, 'block_email_list');
		}
	}

	// Add field respondedid into block_email_list_mail table because, if one mail has replied, know this parent.
	if ( $result and $oldversion < 2010021400 ) {
		$table = new xmldb_table('block_email_list_mail');
		$field = new xmldb_field('respondedid');

		if ( !$dbman->field_exists($table, $field) ) {
			$field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL);

        	$result = $result && $dbman->add_field($table, $field);
		}

		// Only compatible with 1.9 or prior versions
		if ( $existfunction ) {
			/// Block savepoint reached
			upgrade_blocks_savepoint($result, 2010021400, 'block_email_list');
		}
	}

    return $result;
}

?>