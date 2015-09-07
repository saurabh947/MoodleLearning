<?php

/**
 * Configure block page.
 *
 * @author Toni Mas
 * @version
 * @package email list
 * @license The source code packaged with this file is Free Software, Copyright (C) 2010 by
 *          <toni.mas at uib dot es>.
 *          It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
 *          You can get copies of the licenses here:
 *                         http://www.affero.org/oagpl.html
 *          AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
 **/

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
	require_once($CFG->dirroot.'/blocks/email_list/email/lib.php');

	$settings->add(new admin_setting_heading('email_list_normal_header', 'eMail GENERAL settings', 'You can enable who user\'s settings'));

    $options = array('0' => get_string('no'), '1' => get_string('yes'));

    $settings->add(new admin_setting_configselect('email_enable_ssl', 'email_enable_ssl', get_string('enable_ssl', 'block_email_list'),
    					$CFG->email_enable_ssl, $options));

    $settings->add(new admin_setting_configselect('email_trackbymail', 'email_trackbymail', get_string('configtrackbymail', 'block_email_list'),
    					$CFG->email_trackbymail, $options));

    $settings->add(new admin_setting_configselect('email_marriedfolders2courses', 'email_marriedfolders2courses', get_string('configmarriedfolders2courses', 'block_email_list'),
    					$CFG->email_marriedfolders2courses, $options));

    $settings->add(new admin_setting_configselect('email_add_admins', 'email_add_admins', get_string('add_admins', 'block_email_list'),
    					$CFG->email_add_admins, $options));

    $settings->add(new admin_setting_configselect('email_enable_ajax', 'email_enable_ajax', get_string('enable_ajax', 'block_email_list'),
    					$CFG->email_enable_ajax, $options));

    $settings->add(new admin_setting_configselect('email_display_course_fullname', 'email_display_course_fullname', get_string('display_course_fullname', 'block_email_list'),
    					$CFG->email_display_course_fullname, $options));


    $settings->add(new admin_setting_heading('email_list_block_header', 'BLOCK email_list', ''));

    $options[0] = get_string('all');
    $options[5] = 5;
    $options[10] = 10;
    $options[15] = 15;
    $options[20] = 20;
    $options[25] = 25;
    $options[30] = 30;
    $options[35] = 35;
    $options[40] = 40;
    $options[45] = 45;
    $options[50] = 50;


    $settings->add(new admin_setting_configselect('email_max_number_courses', 'email_max_number_courses', get_string('configmaxnumbercourses', 'block_email_list'),
    					$CFG->email_max_number_courses, $options));

}