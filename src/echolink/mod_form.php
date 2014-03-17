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
 * Echo360 Link configuration form
 *
 * @package    mod
 * @subpackage echolink
 * @copyright  (c) 2013, Echo360 Inc.  www.echo360.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once ($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/echolink/locallib.php');

class mod_echolink_mod_form extends moodleform_mod {
    function definition() {
	global $CFG, $PAGE, $COURSE, $USER;

        $mform = $this->_form;
        $config = get_config('echolink');

	$moodleCourse = "";

        if($config->Moodle_External_ID == "moodle_short_name_course") {
                $moodleCourse = $COURSE->shortname;    
        } else if($config->Moodle_External_ID == "moodle_full_name_course") {
                $moodleCourse = $COURSE->fullname;
        } else if($config->Moodle_External_ID == "moodle_id_number_course") {
                $moodleCourse = $COURSE->idnumber;
        } else if($config->Moodle_External_ID == "moodle_database_id_course") {
                $moodleCourse = $COURSE->id;
        }   

        $moodleUser = $USER->username;

        //-------------------------------------------------------
        if($config->Display_Label != "") {
                $mform->addElement('header', 'general', $config->Display_Label);
        } else {
                $mform->addElement('header', 'general', get_string('echolink', 'echolink'));
        }   

        $mform->addElement('text', 'name', get_string('name', 'echolink'), array('size'=>'128'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $mform->addElement('hidden', 'externalecholink', '', array('id'=>'externalecholink'));
        $mform->setType('externalecholink', PARAM_URL);
        $mform->addRule('externalecholink', null, 'required', null, 'client');
	
        $mform->addElement('hidden', 'previousecholink', '', array('id'=>'previousecholink'));

	$this->add_intro_editor($config->requiremodintro);

        //-------------------------------------------------------
        if($config->Display_Label != "") {
                $mform->addElement('header', 'echosystemcontent', $config->Display_Label . " Options");
        } else {
                $mform->addElement('header', 'echosystemcontent', get_string('echosystemcontent', 'echolink'));
        }

        $defaultFilter = $config->Display_Listing;
        if($defaultFilter == '0') {				                                                                     // Show All Courses
                $mform->addElement('html', '<div>' . echolink_ess_get_rest_courses($moodleCourse, $defaultFilter) . '</div>');
        } else if($defaultFilter == '1') {				                                                             // Show Courses by Instructor Only
                $mform->addElement('html', '<div>' . echolink_ess_get_rest_person_courses($moodleUser, $moodleCourse, $defaultFilter) . '</div>');
        }

        // Load this module's Javascript library for AJAX queries / event handlers to each of the ESS Courses displayed
        $module = array('name'=>'mod_echolink',
                        'fullpath'=>'/mod/echolink/library/echolink.js',
                       );

        $params = array(
                        array(
                                "essFilterEventURL" => $CFG->wwwroot."/mod/echolink/event-handler/echolink_request_courses.php?moodle_course=$moodleCourse&moodle_user=$moodleUser&defaultFilter=$defaultFilter",
                                "essCourseEventURL" => $CFG->wwwroot."/mod/echolink/event-handler/echolink_request_course_sections.php?course_uuid=",
                                "essSectionEventURL" => $CFG->wwwroot."/mod/echolink/event-handler/echolink_request_section_presentations.php?section_uuid=",
                                "essPresentationEventURL" => $CFG->wwwroot."/mod/echolink/event-handler/echolink_request_presentation.php?presentation_uuid=",
                        )
                  );

        $PAGE->requires->js_init_call('M.mod_echolink.init', $params, false, $module);
        $mform->setExpanded('echosystemcontent');
/**
**/
        //-------------------------------------------------------

        //-------------------------------------------------------
        $this->standard_coursemodule_elements();

        //-------------------------------------------------------
        $this->add_action_buttons();
    }

    function data_preprocessing(&$default_values) {
        if (!empty($default_values['displayoptions'])) {
            $displayoptions = unserialize($default_values['displayoptions']);
            if (isset($displayoptions['printintro'])) {
                $default_values['printintro'] = $displayoptions['printintro'];
            }
            if (isset($displayoptions['printheading'])) {
                $default_values['printheading'] = $displayoptions['printheading'];
            }
            if (!empty($displayoptions['popupwidth'])) {
                $default_values['popupwidth'] = $displayoptions['popupwidth'];
            }
            if (!empty($displayoptions['popupheight'])) {
                $default_values['popupheight'] = $displayoptions['popupheight'];
            }
        }
        if (!empty($default_values['parameters'])) {
            $parameters = unserialize($default_values['parameters']);
            $i = 0;
            foreach ($parameters as $parameter=>$variable) {
                $default_values['parameter_'.$i] = $parameter;
                $default_values['variable_'.$i]  = $variable;
                $i++;
            }
        }
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Validating Entered echolink, we are looking for obvious problems only,
        // teachers are responsible for testing if it actually works.

        // This is not a security validation!! Teachers are allowed to enter "javascript:alert(666)" for example.

        // NOTE: do not try to explain the difference between URL and URI, people would be only confused...

        if (empty($data['externalecholink'])) {
            $errors['externalecholink'] = get_string('required');

        } else {
            $echolink = trim($data['externalecholink']);
            if (empty($echolink)) {
                $errors['externalecholink'] = get_string('required');

            } else if (preg_match('|^/|', $echolink)) {
                // links relative to server root are ok - no validation necessary

            } else if (preg_match('|^[a-z]+://|i', $echolink) or preg_match('|^https?:|i', $echolink) or preg_match('|^ftp:|i', $echolink)) {
                // normal URL
                if (!echolink_appears_valid_echolink($echolink)) {
                    $errors['externalecholink'] = get_string('invalidecholink', 'echolink');
                }

            } else if (preg_match('|^[a-z]+:|i', $echolink)) {
                // general URI such as teamspeak, mailto, etc. - it may or may not work in all browsers,
                // we do not validate these at all, sorry

            } else {
                // invalid URI, we try to fix it by adding 'http://' prefix,
                // relative links are NOT allowed because we display the link on different pages!
                if (!echolink_appears_valid_echolink('http://'.$echolink)) {
                    $errors['externalecholink'] = get_string('invalidecholink', 'echolink');
                }
            }
        }
        return $errors;
    }

}
