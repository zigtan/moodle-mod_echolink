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
 * Echo360 Link module admin settings and defaults
 *
 * @package    mod
 * @subpackage echolink
 * @copyright  (c) 2013, Echo360 Inc.  www.echo360.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_AUTO,
                                                           RESOURCELIB_DISPLAY_EMBED,
                                                           RESOURCELIB_DISPLAY_FRAME,
                                                           RESOURCELIB_DISPLAY_OPEN,
                                                           RESOURCELIB_DISPLAY_NEW,
                                                           RESOURCELIB_DISPLAY_POPUP,
                                                          ));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_NEW);


    //--- EchoSystem settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configtext('echolink/Display_Label', get_string('echolinkdisplaylabel', 'echolink'), get_string('echolinkdisplaylabelexplain', 'echolink'), 'EchoLink'));
    $settings->add(new admin_setting_configtext('echolink/ESS_URL', get_string('echosystemserverurl', 'echolink'), get_string('echosystemserverurlexplain', 'echolink'), 'https://ess.myinstitution.edu:8443/'));
    $settings->add(new admin_setting_configtext('echolink/ESS_Consumer_Key', get_string('trustedsystemconsumerkey', 'echolink'), get_string('trustedsystemconsumerkeyexplain', 'echolink'), ''));
    $settings->add(new admin_setting_configtext('echolink/ESS_Consumer_Secret', get_string('trustedsystemconsumersecret', 'echolink'), get_string('trustedsystemconsumersecretexplain', 'echolink'), ''));
    $settings->add(new admin_setting_configcheckbox('echolink/Display_Listing', get_string('displaylistingoption', 'echolink'), get_string('displaylistingoptionexplain', 'echolink'), false));

    // EchoSystem Moodle External Id Field
    $defaultEchoSystemMoodleExternalIdOption = 1;
    $displayEchoSystemMoodleExternalIdOptions = array('moodle_short_name_course'=>get_string('moodle_short_name_course', 'echolink'),
                                                      'moodle_full_name_course'=>get_string('moodle_full_name_course', 'echolink'),
                                                      'moodle_id_number_course'=>get_string('moodle_id_number_course', 'echolink'),
                                                      'moodle_database_id_course'=>get_string('moodle_database_id_course', 'echolink'),
                                                );

    $settings->add(new admin_setting_configselect('echolink/Moodle_External_ID', 
                                                  get_string('echosystem_moodle_external_id', 'echolink'), 
                                                  get_string('echosystem_moodle_external_id_explain', 'echolink'),
                                                  $defaultEchoSystemMoodleExternalIdOption,
                                                  $displayEchoSystemMoodleExternalIdOptions
                                                 )   
                  );  


    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configcheckbox('echolink/requiremodintro',
        get_string('requiremodintro', 'admin'), get_string('configrequiremodintro', 'admin'), false));
    $settings->add(new admin_setting_configmultiselect('echolink/displayoptions',
        get_string('displayoptions', 'echolink'), get_string('configdisplayoptions', 'echolink'),
        $defaultdisplayoptions, $displayoptions));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('echolinkmodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox('echolink/printheading',
        get_string('printheading', 'echolink'), get_string('printheadingexplain', 'echolink'), 0));
    $settings->add(new admin_setting_configcheckbox('echolink/printintro',
        get_string('printintro', 'echolink'), get_string('printintroexplain', 'echolink'), 1));
    $settings->add(new admin_setting_configselect('echolink/display',
        get_string('displayselect', 'echolink'), get_string('displayselectexplain', 'echolink'), RESOURCELIB_DISPLAY_AUTO, $displayoptions));
    $settings->add(new admin_setting_configtext('echolink/popupwidth',
        get_string('popupwidth', 'echolink'), get_string('popupwidthexplain', 'echolink'), 1024, PARAM_INT, 7));
    $settings->add(new admin_setting_configtext('echolink/popupheight',
        get_string('popupheight', 'echolink'), get_string('popupheightexplain', 'echolink'), 768, PARAM_INT, 7));
}
