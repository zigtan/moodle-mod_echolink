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
 * List of echolinks in course
 *
 * @package    mod
 * @subpackage echolink
 * @copyright  (c) 2013, Echo360 Inc.  www.echo360.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

$id = required_param('id', PARAM_INT); // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_course_login($course, true);
$PAGE->set_pagelayout('incourse');

add_to_log($course->id, 'echolink', 'view all', "index.php?id=$course->id", '');

$strecholink       = get_string('modulename', 'echolink');
$strecholinks      = get_string('modulenameplural', 'echolink');
$strsectionname  = get_string('sectionname', 'format_'.$course->format);
$strname         = get_string('name');
$strintro        = get_string('moduleintro');
$strlastmodified = get_string('lastmodified');

$PAGE->set_echolink('/mod/echolink/index.php', array('id' => $course->id));
$PAGE->set_title($course->shortname.': '.$strecholinks);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strecholinks);
echo $OUTPUT->header();

if (!$echolinks = get_all_instances_in_course('echolink', $course)) {
    notice(get_string('thereareno', 'moodle', $strecholinks), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}

$usesections = course_format_uses_sections($course->format);
if ($usesections) {
    $modinfo = get_fast_modinfo($course->id);
    $sections = $modinfo->get_section_info_all();
}

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections) {
    $table->head  = array ($strsectionname, $strname, $strintro);
    $table->align = array ('center', 'left', 'left');
} else {
    $table->head  = array ($strlastmodified, $strname, $strintro);
    $table->align = array ('left', 'left', 'left');
}

$modinfo = get_fast_modinfo($course);
$currentsection = '';
foreach ($echolinks as $echolink) {
    $cm = $modinfo->cms[$echolink->coursemodule];
    if ($usesections) {
        $printsection = '';
        if ($echolink->section !== $currentsection) {
            if ($echolink->section) {
                $printsection = get_section_name($course, $sections[$echolink->section]);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $echolink->section;
        }
    } else {
        $printsection = '<span class="smallinfo">'.userdate($echolink->timemodified)."</span>";
    }

    $extra = empty($cm->extra) ? '' : $cm->extra;
    $icon = '';
    if (!empty($cm->icon)) {
        // each echolink has an icon in 2.0
        $icon = '<img src="'.$OUTPUT->pix_echolink($cm->icon).'" class="activityicon" alt="'.get_string('modulename', $cm->modname).'" /> ';
    }

    $class = $echolink->visible ? '' : 'class="dimmed"'; // hidden modules are dimmed
    $table->data[] = array (
        $printsection,
        "<a $class $extra href=\"view.php?id=$cm->id\">".$icon.format_string($echolink->name)."</a>",
        format_module_intro('echolink', $echolink, $cm->id));
}

echo html_writer::table($table);

echo $OUTPUT->footer();
