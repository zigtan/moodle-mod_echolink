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
 * EchoLink module upgrade related helper functions
 *
 * @package    mod
 * @subpackage echolink
 * @copyright  (c) 2013, Echo360 Inc.  www.echo360.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Migrate echolink module data from 1.9 resource_old table to new echolink table
 * @return void
 */
function echolink_20_migrate() {
    global $CFG, $DB;

    require_once("$CFG->libdir/filelib.php");
    require_once("$CFG->libdir/resourcelib.php");
    require_once("$CFG->dirroot/course/lib.php");

    if (!file_exists("$CFG->dirroot/mod/resource/db/upgradelib.php")) {
        // bad luck, somebody deleted resource module
        return;
    }

    require_once("$CFG->dirroot/mod/resource/db/upgradelib.php");

    // create resource_old table and copy resource table there if needed
    if (!resource_20_prepare_migration()) {
        // no modules or fresh install
        return;
    }

    $candidates = $DB->get_recordset('resource_old', array('type'=>'file', 'migrated'=>0));
    if (!$candidates->valid()) {
        $candidates->close(); // Not going to iterate (but exit), close rs
        return;
    }

    foreach ($candidates as $candidate) {
        $path = $candidate->reference;
        $siteid = get_site()->id;

        if (strpos($path, 'LOCALPATH') === 0) {
            // ignore not maintained local files - sorry
            continue;
        } else if (!strpos($path, '://')) {
            // not EchoLink
            continue;
        } else if (preg_match("|$CFG->wwwroot/file.php(\?file=)?/$siteid(/[^\s'\"&\?#]+)|", $path, $matches)) {
            // handled by resource module
            continue;
        } else if (preg_match("|$CFG->wwwroot/file.php(\?file=)?/$candidate->course(/[^\s'\"&\?#]+)|", $path, $matches)) {
            // handled by resource module
            continue;
        }

        upgrade_set_timeout();

        if ($CFG->texteditors !== 'textarea') {
            $intro       = text_to_html($candidate->intro, false, false, true);
            $introformat = FORMAT_HTML;
        } else {
            $intro       = $candidate->intro;
            $introformat = FORMAT_MOODLE;
        }

        $echolink = new stdClass();
        $echolink->course       = $candidate->course;
        $echolink->name         = $candidate->name;
        $echolink->intro        = $intro;
        $echolink->introformat  = $introformat;
        $echolink->externalecholink  = $path;
        $echolink->timemodified = time();

        $options    = array('printheading'=>0, 'printintro'=>1);
        $parameters = array();
        if ($candidate->options == 'frame') {
            $echolink->display = RESOURCELIB_DISPLAY_FRAME;

        } else if ($candidate->options == 'objectframe') {
            $echolink->display = RESOURCELIB_DISPLAY_EMBED;

        } else if ($candidate->popup) {
            $echolink->display = RESOURCELIB_DISPLAY_POPUP;
            if ($candidate->popup) {
                $rawoptions = explode(',', $candidate->popup);
                foreach ($rawoptions as $rawoption) {
                    list($name, $value) = explode('=', trim($rawoption), 2);
                    if ($value > 0 and ($name == 'width' or $name == 'height')) {
                        $options['popup'.$name] = $value;
                        continue;
                    }
                }
            }

        } else {
            $echolink->display = RESOURCELIB_DISPLAY_AUTO;
        }
        $echolink->displayoptions = serialize($options);

        if ($candidate->alltext) {
            $rawoptions = explode(',', $candidate->alltext);
            foreach ($rawoptions as $rawoption) {
                list($variable, $parameter) = explode('=', trim($rawoption), 2);
                $parameters[$parameter] = $variable;
            }
        }

        $echolink->parameters = serialize($parameters);

        if (!$echolink = resource_migrate_to_module('echolink', $candidate, $echolink)) {
            continue;
        }
    }

    $candidates->close();

    // clear all course modinfo caches
    rebuild_course_cache(0, true);
}
