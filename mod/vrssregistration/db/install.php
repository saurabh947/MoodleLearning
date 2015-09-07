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
 * This file replaces the legacy STATEMENTS section in db/install.xml,
 * lib.php/modulename_install() post installation hook and partially defaults.php
 *
 * @package    mod
 * @subpackage vrssregistration
 * @copyright  2011 Your Name <your@email.adress>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Post installation procedure
 *
 * @see upgrade_plugins_modules()
 */
function xmldb_vrssregistration_install() {
	    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

	//Puja -- insert default values
		//Qualification  	
		$record = new stdClass();		
		$record->qualification         = 'M.E./M.Tech';
		$record->description = '';
		$DB->insert_record('vrssqualification', $record, false);
		
		$record->qualification         = 'B.E./B.Tech';
		$record->description = '';		
		$DB->insert_record('vrssqualification', $record, false);		

		//colleges
		$record = new stdClass();		
		$record->qualification = 'MSU';
		$record->description = '';
		$DB->insert_record('vrsscollege', $record, false);
		
		$record = new stdClass();		
		$record->college_name         = 'G.H.Patel';
		$record->description = '';
		$record->is_other=0;
		$DB->insert_record('vrsscollege', $record, false);
		
		$record = new stdClass();		
		$record->college_name         = 'Parul Institute';
		$record->description = '';
		$record->is_other=0;
		$DB->insert_record('vrsscollege', $record, false);
		
		$record = new stdClass();		
		$record->college_name         = 'SVIT,Vasad';
		$record->description = '';
		$record->is_other=0;
		$DB->insert_record('vrsscollege', $record, false);

		$record = new stdClass();		
		$record->college_name         = 'Nirma';
		$record->description = '';
		$record->is_other=0;
		$DB->insert_record('vrsscollege', $record, false);
	
		//Branch
		$record = new stdClass();		
		$record->branch         = 'Computers';
		$record->description = '';
		$DB->insert_record('vrssbranch', $record, false);

		$record = new stdClass();		
		$record->branch         = 'Information Technology';
		$record->description = '';
		$DB->insert_record('vrssbranch', $record, false);

		$record = new stdClass();		
		$record->branch         = 'Electronics';
		$record->description = '';
		$DB->insert_record('vrssbranch', $record, false);


		$record = new stdClass();		
		$record->qualification_id        = 1;
		$record->branch_id = 1;
		$DB->insert_record('vrssqualificationbranch', $record, false);

		$record = new stdClass();		
		$record->qualification_id        = 1;
		$record->branch_id = 2;
		$DB->insert_record('vrssqualificationbranch', $record, false);
		$record = new stdClass();		
		$record->qualification_id        = 1;
		$record->branch_id = 3;
		$DB->insert_record('vrssqualificationbranch', $record, false);

		$record = new stdClass();		
		$record->qualification_id        = 2;
		$record->branch_id = 1;
		$DB->insert_record('vrssqualificationbranch', $record, false);
		
		$record = new stdClass();		
		$record->qualification_id        = 2;
		$record->branch_id = 2;
		$DB->insert_record('vrssqualificationbranch', $record, false);
		
		$record = new stdClass();		
		$record->qualification_id        = 2;
		$record->branch_id = 3;
		$DB->insert_record('vrssqualificationbranch', $record, false);
				$record = new stdClass();		
		$record->qualification_id        = 3;
		$record->branch_id = 1;
		$DB->insert_record('vrssqualificationbranch', $record, false);
				$record = new stdClass();		
		$record->qualification_id        = 3;
		$record->branch_id = 2;
		$DB->insert_record('vrssqualificationbranch', $record, false);
				$record = new stdClass();		
		$record->qualification_id        = 3;
		$record->branch_id = 3;
		$DB->insert_record('vrssqualificationbranch', $record, false);
								
		//courses
		$record = new stdClass();		
		$record->course_name        = '1 year IDP';
		$record->course_url = '';
		$DB->insert_record('vrsscourse', $record, false);

		$record = new stdClass();		
		$record->course_name        = 'Embedded';
		$record->course_url = '';
		$DB->insert_record('vrsscourse', $record, false);
				$record = new stdClass();		
		$record->course_name        = 'VLSI';
		$record->course_url = '';
		$DB->insert_record('vrsscourse', $record, false);
				
		$record = new stdClass();		
		$record->course_name        = 'M.E./M.Tech Project Training';
		$record->course_url = '';
		$DB->insert_record('vrsscourse', $record, false);
		
		$record->course_name         = 'M.Tech (VLSI & Embedded System)';
		$record->course_url = '';		
		$DB->insert_record('vrsscourse', $record, false);
				
		$record = new stdClass();		
		$record->course_name        = 'Software';
		$record->course_url = '';
		$DB->insert_record('vrsscourse', $record, false);	  
  								
}

/**
 * Post installation recovery procedure
 *
 * @see upgrade_plugins_modules()
 */
function xmldb_vrssregistration_install_recovery() {
}
