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
 * Mandatory public API of echolink module
 *
 * @package    mod
 * @subpackage echolink
 * @copyright  (c) 2013, Echo360 Inc.  www.echo360.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * List of features supported in Echo360 Link module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function echolink_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

/**
 * Returns all other caps used in module
 * @return array
 */
function echolink_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function echolink_reset_userdata($data) {
    return array();
}

/**
 * List of view style log actions
 * @return array
 */
function echolink_get_view_actions() {
    return array('view', 'view all');
}

/**
 * List of update style log actions
 * @return array
 */
function echolink_get_post_actions() {
    return array('update', 'add');
}

/**
 * Add echolink instance.
 * @param object $data
 * @param object $mform
 * @return int new echolink instance id
 */
function echolink_add_instance($data, $mform) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/mod/echolink/locallib.php');

    $parameters = array();
    for ($i=0; $i < 100; $i++) {
        $parameter = "parameter_$i";
        $variable  = "variable_$i";
        if (empty($data->$parameter) or empty($data->$variable)) {
            continue;
        }
        $parameters[$data->$parameter] = $data->$variable;
    }
    $data->parameters = serialize($parameters);

    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    if (in_array($data->display, array(RESOURCELIB_DISPLAY_AUTO, RESOURCELIB_DISPLAY_EMBED, RESOURCELIB_DISPLAY_FRAME))) {
        $displayoptions['printheading'] = (int)!empty($data->printheading);
        $displayoptions['printintro']   = (int)!empty($data->printintro);
    }
    $data->displayoptions = serialize($displayoptions);

    $data->externalecholink = echolink_fix_submitted_echolink($data->externalecholink);

    $data->timemodified = time();
    $data->id = $DB->insert_record('echolink', $data);

    return $data->id;
}

/**
 * Update echolink instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function echolink_update_instance($data, $mform) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/mod/echolink/locallib.php');

    $parameters = array();
    for ($i=0; $i < 100; $i++) {
        $parameter = "parameter_$i";
        $variable  = "variable_$i";
        if (empty($data->$parameter) or empty($data->$variable)) {
            continue;
        }
        $parameters[$data->$parameter] = $data->$variable;
    }
    $data->parameters = serialize($parameters);

    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    if (in_array($data->display, array(RESOURCELIB_DISPLAY_AUTO, RESOURCELIB_DISPLAY_EMBED, RESOURCELIB_DISPLAY_FRAME))) {
        $displayoptions['printheading'] = (int)!empty($data->printheading);
        $displayoptions['printintro']   = (int)!empty($data->printintro);
    }
    $data->displayoptions = serialize($displayoptions);

    $data->externalecholink = echolink_fix_submitted_echolink($data->externalecholink);

    $data->timemodified = time();
    $data->id           = $data->instance;

    $DB->update_record('echolink', $data);

    return true;
}

/**
 * Delete echolink instance.
 * @param int $id
 * @return bool true
 */
function echolink_delete_instance($id) {
    global $DB;

    if (!$echolink = $DB->get_record('echolink', array('id'=>$id))) {
        return false;
    }

    // note: all context files are deleted automatically

    $DB->delete_records('echolink', array('id'=>$echolink->id));

    return true;
}

/**
 * Return use outline
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $echolink
 * @return object|null
 */
function echolink_user_outline($course, $user, $mod, $echolink) {
    global $DB;

    if ($logs = $DB->get_records('log', array('userid'=>$user->id, 'module'=>'echolink',
                                              'action'=>'view', 'info'=>$echolink->id), 'time ASC')) {

        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $result = new stdClass();
        $result->info = get_string('numviews', '', $numviews);
        $result->time = $lastlog->time;

        return $result;
    }
    return NULL;
}

/**
 * Return use complete
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $echolink
 */
function echolink_user_complete($course, $user, $mod, $echolink) {
    global $CFG, $DB;

    if ($logs = $DB->get_records('log', array('userid'=>$user->id, 'module'=>'echolink',
                                              'action'=>'view', 'info'=>$echolink->id), 'time ASC')) {
        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $strmostrecently = get_string('mostrecently');
        $strnumviews = get_string('numviews', '', $numviews);

        echo "$strnumviews - $strmostrecently ".userdate($lastlog->time);

    } else {
        print_string('neverseen', 'echolink');
    }
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param object $coursemodule
 * @return object info
 */
function echolink_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;
    require_once("$CFG->dirroot/mod/echolink/locallib.php");

    if (!$echolink = $DB->get_record('echolink', array('id'=>$coursemodule->instance),
            'id, name, display, displayoptions, externalecholink, parameters, intro, introformat')) {
        return NULL;
    }

    $info = new cached_cm_info();
    $info->name = $echolink->name;

    //note: there should be a way to differentiate links from normal resources
    $info->icon = echolink_guess_icon($echolink->externalecholink, 24);

    $display = echolink_get_final_display_type($echolink);

    if ($display == RESOURCELIB_DISPLAY_POPUP) {
        $fullecholink = "$CFG->wwwroot/mod/echolink/view.php?id=$coursemodule->id&amp;redirect=1";
        $options = empty($echolink->displayoptions) ? array() : unserialize($echolink->displayoptions);
        $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $info->onclick = "window.open('$fullecholink', '', '$wh'); return false;";

    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $fullecholink = "$CFG->wwwroot/mod/echolink/view.php?id=$coursemodule->id&amp;redirect=1";
        $info->onclick = "window.open('$fullecholink'); return false;";

    }

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $info->content = format_module_intro('echolink', $echolink, $coursemodule->id, false);
    }

    return $info;
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function echolink_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-echolink-*'=>get_string('page-mod-echolink-x', 'echolink'));
    return $module_pagetype;
}

/**
 * Export Echo360 Link resource contents
 *
 * @return array of file content
 */
function echolink_export_contents($cm, $baseecholink) {
    global $CFG, $DB;
    require_once("$CFG->dirroot/mod/echolink/locallib.php");
    $contents = array();
    $context = context_module::instance($cm->id);

    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
    $echolink = $DB->get_record('echolink', array('id'=>$cm->instance), '*', MUST_EXIST);

    $fullecholink = str_replace('&amp;', '&', echolink_get_full_echolink($echolink, $cm, $course));
    $isecholink = clean_param($fullecholink, PARAM_URL);
    if (empty($isecholink)) {
        return null;
    }

    $echolink = array();
    $echolink['type'] = 'echolink';
    $echolink['filename']     = $echolink->name;
    $echolink['filepath']     = null;
    $echolink['filesize']     = 0;
    $echolink['fileecholink']      = $fullecholink;
    $echolink['timecreated']  = null;
    $echolink['timemodified'] = $echolink->timemodified;
    $echolink['sortorder']    = null;
    $echolink['userid']       = null;
    $echolink['author']       = null;
    $echolink['license']      = null;
    $contents[] = $echolink;

    return $contents;
}

/**
 * Register the ability to handle drag and drop file uploads
 * @return array containing details of the files / types the mod can handle
 *
function echolink_dndupload_register() {
    return array('types' => array(
                     array('identifier' => 'echolink', 'message' => get_string('createecholink', 'echolink'))
                 ));
}
 **/

/**
 * Handle a file that has been uploaded
 * @param object $uploadinfo details of the file / content that has been uploaded
 * @return int instance id of the newly created mod
 *
function echolink_dndupload_handle($uploadinfo) {
    // Gather all the required data.
    $data = new stdClass();
    $data->course = $uploadinfo->course->id;
    $data->name = $uploadinfo->displayname;
    $data->intro = '<p>'.$uploadinfo->displayname.'</p>';
    $data->introformat = FORMAT_HTML;
    $data->externalecholink = clean_param($uploadinfo->content, PARAM_URL);
    $data->timemodified = time();

    // Set the display options to the site defaults.
    $config = get_config('echolink');
    $data->display = $config->display;
    $data->popupwidth = $config->popupwidth;
    $data->popupheight = $config->popupheight;
    $data->printheading = $config->printheading;
    $data->printintro = $config->printintro;

    return echolink_add_instance($data, null);
}
 **/
