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
 * Echo360 Link module main user interface
 *
 * @package    mod
 * @subpackage echolink
 * @copyright  (c) 2013, Echo360 Inc.  www.echo360.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/mod/echolink/locallib.php");
require_once($CFG->libdir . '/completionlib.php');

$id       = optional_param('id', 0, PARAM_INT);        // Course module ID
$u        = optional_param('u', 0, PARAM_INT);         // Echo360 Link instance id
$redirect = optional_param('redirect', 0, PARAM_BOOL);

if ($u) {  // Two ways to specify the module
    $echolink = $DB->get_record('echolink', array('id'=>$u), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('echolink', $echolink->id, $echolink->course, false, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('echolink', $id, 0, false, MUST_EXIST);
    $echolink = $DB->get_record('echolink', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/echolink:view', $context);

add_to_log($course->id, 'echolink', 'view', 'view.php?id='.$cm->id, $echolink->id, $cm->id);


// Update 'viewed' state if required by completion system
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/echolink/view.php', array('id' => $cm->id));

// Make sure Echo360 Link exists before generating output - some older sites may contain empty echolinks
// Do not use PARAM_URL here, it is too strict and does not support general URIs!
$extecholink = trim($echolink->externalecholink);
if (empty($extecholink) or $extecholink === 'http://') {
    echolink_print_header($echolink, $cm, $course);
    echolink_print_heading($echolink, $cm, $course);
    echolink_print_intro($echolink, $cm, $course);
    notice(get_string('invalidstoredecholink', 'echolink'), new moodle_echolink('/course/view.php', array('id'=>$cm->course)));
    die;
}
unset($extecholink);

$displaytype = echolink_get_final_display_type($echolink);
if ($displaytype == RESOURCELIB_DISPLAY_OPEN) {
    // For 'open' links, we always redirect to the content - except if the user
    // just chose 'save and display' from the form then that would be confusing
    if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], 'modedit.php') === false) {
        $redirect = true;
    }
}

if ($redirect) {
    // Coming from course page or echolink index page, the redirection is needed for completion tracking and logging
    $fullecholink = echolink_get_full_echolink($echolink, $cm, $course);

    // Perform EchoSystem OAuth Seamless Authentication Request to the current Echo360 Link
    echolink_ess_oauth_seamless_login($echolink->externalecholink);
}

switch ($displaytype) {
    case RESOURCELIB_DISPLAY_EMBED:
        echolink_display_embed($echolink, $cm, $course);
        break;
    case RESOURCELIB_DISPLAY_FRAME:
        echolink_display_frame($echolink, $cm, $course);
        break;
    default:
        echolink_print_workaround($echolink, $cm, $course);
        break;
}
