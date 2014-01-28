<?php
/*****************************************************************************
 *
 *                       _          _____  __    ___  
 *              ___  ___| |__   ___|___ / / /_  / _ \ 
 *             / _ \/ __| '_ \ / _ \ |_ \| '_ \| | | |
 *            (  __/ (__| | | | (_) |__) | (_) | |_| |
 *             \___|\___|_| |_|\___/____/ \___/ \___/.com
 *
 *****************************************************************************
 *  C O P Y R I G H T   A N D   C O N F I D E N T I A L I T Y   N O T I C E
 *****************************************************************************
 *
 *      Copyright 2008 Echo360, Inc.  All rights reserved.
 *      This software contains valuable confidential and proprietary
 *      information of Echo360, Inc. and is subject to applicable
 *      licensing agreements.  Unauthorized reproduction, transmission or
 *      distribution of this file and its contents is a violation of
 *      applicable laws.
 ****************************************************************************/
/**
 *
 * @package    mod
 * @subpackage echolink
 * @copyright  (c) 2013, Echo360 Inc.  www.echo360.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->dirroot.'/mod/echolink/locallib.php');

$course_uuid = optional_param('course_uuid', '', PARAM_TEXT);
$defaultFilter = optional_param('defaultFilter', '', PARAM_TEXT);
$moodle_user = optional_param('moodle_user', '', PARAM_TEXT);
$filter_option = optional_param('filter_option', '', PARAM_TEXT);
if($filter_option == "show_my_ess_courses") {
    echo echolink_ess_get_rest_person_courses($moodle_user, $course_uuid, $defaultFilter);
} else if($filter_option == "show_all_ess_courses") {
    echo echolink_ess_get_rest_courses($course_uuid, $defaultFilter);
}
?>
