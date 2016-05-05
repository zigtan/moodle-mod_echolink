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
 * Strings for component 'echolink', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    mod
 * @subpackage echolink
 * @copyright  (c) 2013, Echo360 Inc.  www.echo360.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['clicktoopen'] = 'Click {$a} link to open resource.';
$string['configdisplayoptions'] = 'Select all options that should be available, existing settings are not modified. Hold CTRL key to select multiple fields.';
$string['configframesize'] = 'When a web page or an uploaded file is displayed within a frame, this value is the height (in pixels) of the top frame (which contains the navigation).';
$string['configrequiremodintro'] = 'Display EchoLink description below content?';
$string['configrolesinparams'] = 'Enable if you want to include localized role names in list of available parameter variables.';
$string['configsecretphrase'] = 'This secret phrase is used to produce encrypted code value that can be sent to some servers as a parameter.  The encrypted code is produced by an md5 value of the current user IP address concatenated with your secret phrase. ie code = md5(IP.secretphrase). Please note that this is not reliable because IP address may change and is often shared by different computers.';
$string['contentheader'] = 'Content';
$string['createecholink'] = 'Create a EchoLink';
$string['displayoptions'] = 'Available display options';
$string['displayselect'] = 'Display';
$string['displayselect_help'] = 'This setting, together with the EchoLink file type and whether the browser allows embedding, determines how the EchoLink is displayed. Options may include:

* Automatic - The best display option for the EchoLink is selected automatically
* Embed - The EchoLink is displayed within the page below the navigation bar together with the EchoLink description and any blocks
* Open - Only the EchoLink is displayed in the browser window
* In pop-up - The EchoLink is displayed in a new browser window without menus or an address bar
* In frame - The EchoLink is displayed within a frame below the the navigation bar and EchoLink description
* New window - The EchoLink is displayed in a new browser window with menus and an address bar';
$string['displayselectexplain'] = 'Choose display type, unfortunately not all types are suitable for all EchoLinks.';
$string['name'] = 'Display Name';
$string['externalecholink'] = 'EchoLink';
$string['framesize'] = 'Frame height';
$string['invalidstoredecholink'] = 'Cannot display this resource, EchoLink is invalid.';
$string['chooseavariable'] = 'Choose a variable...';
$string['invalidecholink'] = 'The selected EchoLink is invalid';
$string['noselectedecholink'] = 'Select EchoLink first before renaming Display Name';
$string['nonameecholink'] = 'Enter EchoLink Display Name';
$string['modulename'] = 'EchoLink';
$string['modulename_help'] = 'The EchoLink module enables a teacher to provide a web link as a course resource. Anything that is freely available online, such as documents or images, can be linked to; the EchoLink doesn’t have to be the home page of a website. The EchoLink of a particular web page may be copied and pasted or a teacher can use the file picker and choose a link from a repository such as Flickr, YouTube or Wikimedia (depending upon which repositories are enabled for the site).

There are a number of display options for the EchoLink, such as embedded or opening in a new window and advanced options for passing information, such as a student\'s name, to the EchoLink if required.

Note that EchoLinks can also be added to any other resource or activity type through the text editor.';
$string['modulename_link'] = 'mod/echolink/view';
$string['modulenameplural'] = 'EchoLinks';
$string['neverseen'] = 'Never seen';
$string['page-mod-echolink-x'] = 'Any EchoLink module page';
$string['parameterinfo'] = '&amp;parameter=variable';
$string['parametersheader'] = 'EchoLink variables';
$string['parametersheader_help'] = 'Some internal Moodle variables may be automatically appended to the EchoLink. Type your name for the parameter into each text box(es) and then select the required matching variable.';
$string['pluginadministration'] = 'EchoLink module administration';
$string['pluginname'] = 'EchoLink';
$string['popupheight'] = 'Pop-up height (in pixels)';
$string['popupheightexplain'] = 'Specifies default height of popup windows.';
$string['popupwidth'] = 'Pop-up width (in pixels)';
$string['popupwidthexplain'] = 'Specifies default width of popup windows.';
$string['printheading'] = 'Display EchoLink name';
$string['printheadingexplain'] = 'Display EchoLink name above content? Some display types may not display EchoLink name even if enabled.';
$string['printintro'] = 'Display EchoLink description';
$string['printintroexplain'] = 'Display EchoLink description below content? Some display types may not display description even if enabled.';
$string['requiremodintro'] = 'Require activity description';
$string['rolesinparams'] = 'Include role names in parameters';
$string['serverecholink'] = 'Server EchoLink';
$string['echolink:addinstance'] = 'Add a new EchoLink resource';
$string['echolink:view'] = 'View EchoLink';

$string['echolink'] = "EchoLink";
$string['echosystemcontent'] = "EchoSystem Content";
$string['echolinkdisplaylabel'] = "Display label";
$string['echolinkdisplaylabelexplain'] = "Enter the preferred display label for Moodle Users.";
$string['echosystemserverurl'] = "EchoSystem URL";
$string['echosystemserverurlexplain'] = "Enter the Echo360 EchoSystem Server URL available for your institution. e.g. https://ess.myinstitution.edu:8443/";
$string['trustedsystemconsumerkey'] = "EchoSystem Trusted System Consumer Key";
$string['trustedsystemconsumerkeyexplain'] = "Enter the Echo360 EchoSystem Server Trusted System Consumer Key.";
$string['trustedsystemconsumersecret'] = "EchoSystem Trusted System Consumer Secret";
$string['trustedsystemconsumersecretexplain'] = "Enter the Echo360 EchoSystem Server Trusted System Consumer Secret.";
$string['displaylistingoption'] = "Show by Instructor";
$string['displaylistingoptionexplain'] = "Set the EchoSystem Course/Section/Presentation listing by current Instructor, or show all listing.";

$string['echosystem_moodle_external_id'] = "Moodle Course Field";
$string['echosystem_moodle_external_id_explain'] = "This is the field in Moodle that will be passed to EchoSystem to associate a Moodle course with an EchoSystem section.";
$string['moodle_short_name_course'] = "Course short name";
$string['moodle_full_name_course'] = "Full name";
$string['moodle_id_number_course'] = "Course ID number";
$string['moodle_database_id_course'] = "Course database ID";
