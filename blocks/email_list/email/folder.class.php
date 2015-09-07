<?php
/**
 * Parent class for folders.
 *
 * @author Toni Mas
 * @version 1.0.1
 * @uses $CFG
 * @package email
 * @license The source code packaged with this file is Free Software, Copyright (C) 2010 by
 *          <toni.mas at uib dot es>.
 *          It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
 *          You can get copies of the licenses here:
 * 		                   http://www.affero.org/oagpl.html
 *          AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
 **/

class folder {

    function folder() {
    }

    /**
	 * This functions created news folders
	 *
	 * @uses $DB
	 * @param object $folder Fields of new folder
	 * @param int $parentfolder Parent folder
	 * @return boolean Success/Fail
	 * @todo Finish documenting this function
	 **/
	function newfolder($folder, $parentfolder) {

		global $DB;

		// Add actual time
		$folder->timecreated = time();

		// Make sure course field is not null			Thanks Ann.
		if ( ! isset( $folder->course) ) {
			$folder->course = 0;
		}

		// Insert record
		if (! $folder->id = $DB->insert_record('block_email_list_folder', $folder)) {
			return false;
		}

		// Prepare subfolder
		$subfolder = new stdClass();
		$subfolder->folderparentid = $parentfolder;
		$subfolder->folderchildid  = $folder->id;

		// Insert record reference
		if (! $DB->insert_record('block_email_list_subfolder', $subfolder)) {
			return false;
		}

		add_to_log($folder->userid, "email_list", "add subfolder", "$folder->name");

		return true;
	}

	/**
	 * This function return an folder.
	 *
	 * @uses $DB
	 * @param int Folder Id.
	 * @return object Folder.
	 * @todo Finish documenting this function
	 */
	function get_folder( $folderid ) {
		global $DB;

		$folder = new stdClass();
		$folder = $DB->$DB->get_record('block_email_list_folder', array('id' => $folderid));
		if ( $folder->isparenttype ) {
			$folder->name = get_string($folder->isparenttype, 'block_email_list');
		}
		return $folder;
	}

	/**
	 * This function return all folders and subfolder by type, example by INBOX,
	 * SENDBOX, etc.
	 *
	 * @uses $DB
	 * @param int $userid User ID
	 * @param string $type Folder type (INBOX, SENDBOX, ...). Default EMAIL_INBOX.
	 * @return array All folders of these type.
	 * @todo Finish documenting this function
	 */
	function get_folders($userid, $type=EMAIL_INBOX) {

		global $DB;

		email_create_parents_folders($userid);

		// Get root folder.
		$rootfolder = new stdClass();
		$rootfolder = $DB->get_record('block_email_list_folder', array('userid' => $userid, 'isparenttype' => $type));
		$rootfolder->name = get_string($type, 'block_email_list');

		// Get all subfolders
		$subfolders = $this->get_all_subfolders($rootfolder->id);

		return array_merge(array($rootfolder), $subfolders);

	}

	/**
	 * This fuctions return all subfolders with one folder, if it've
	 *
	 * @uses $DB
	 * @param int $folderid Folder parent
	 * @return array Contain all subfolders
	 * @todo Finish documenting this function
	 **/
	function get_all_subfolders($folderid) {

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
		}

		// Return subfolders
		return $subfolders;
	}

	/**
	 * This function return true or false if folder exists.
	 *
	 * @uses $DB
	 *
	 * @param mixed $folder Folder.
	 * @return boolean True or false if folder exist.
	 * @todo Finish documenting this function
	 */
	function folder_exist ( $folder ) {
		global $DB;

		if ( is_int($folder) ) {
			return $DB->record_exist ('block_email_list_folder', array('id' => $folder));
		} else {
			return $DB->record_exist ('block_email_list_folder', array('id' => $folder->id));
		}
	}

}
?>