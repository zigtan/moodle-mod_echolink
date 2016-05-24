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
 * Private echolink module utility functions
 *
 * @package    mod
 * @subpackage echolink
 * @copyright  (c) 2013, Echo360 Inc.  www.echo360.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/echolink/lib.php");
require_once("$CFG->dirroot/mod/echolink/library/ess-rest-lib.php");
require_once("$CFG->dirroot/mod/echolink/library/ess-seamless-lib.php");

$config = get_config('echolink');
define('ESS_API_PATH', "ess/scheduleapi/v1/");
define('ESS_URL', $config->ESS_URL . ESS_API_PATH);
define('ESS_CONSUMER_KEY', $config->ESS_Consumer_Key);
define('ESS_CONSUMER_SECRET', $config->ESS_Consumer_Secret);


//-------------------------------------------------------------
function echolink_ess_get_rest_courses($moodleCourse = null, $defaultFilter = null) {

        // Retrieve ESS Course XML
        $courseXML = callRestService(ESS_URL, ESS_CONSUMER_KEY, ESS_CONSUMER_SECRET, "courses", "", "GET", array());

        if($courseXML != null || $courseXML != '') {
                $courseJSON = convertXMLtoJSON($courseXML, true, true);

                $courseFilterHTML = "<div id='headerDiv'>" .
                                      "<div id='headerData'><h3 style='text-align:left;'>Echo360 Courses | Sections | Presentations</h3></div>" .
                                      "<div style='float: right;'>Show By: <select name='SHOW_BY_FILTER' id='show_by_filter'><option value='show_all_ess_courses'>All Available Echo360 Courses</option><option value='show_my_ess_courses'>My Echo360 Courses</option></select></div>" .
                                    "</div>";

                if($courseJSON['total-results'] == 0) {
                        return "<div id='echoLinkFormDiv'>" .
                                  $courseFilterHTML .
                                 "<div id='courseDiv'>" .
                                   "<div id='coursesData' style='margin-left: 20px; padding-bottom: 10px;'>" .
                                     "<div class='courseRecord'>No EchoSystem Courses are currently available.</div>" .
                                   "</div>" .
                                 "</div>" .
                               "</div>";
                } else if($courseJSON['total-results'] == 1) {
                        $id = $courseJSON['course']['id'];
                        $name = $courseJSON['course']['name'] . " (" . $courseJSON['course']['identifier'] . ")";

                        return "<div id='echoLinkFormDiv'>" .
                                 $courseFilterHTML .
                                 "<div id='courseDiv'>" .
                                   "<div id='coursesData' style='margin-left: 20px; padding-bottom: 10px;'>" .
                                     "<div class='courseRecord' id='$id'>" .
                                       "<a href='#' class='ess_course_link' id='$id' target='_parent'><label id='$id' style='font-weight: bold;'> + </label>$name</a>" .
                                       "<div class='sectionDiv' id='$id'></div>" .
                                     "</div>" .
                                   "</div>" .
                                 "</div>" .
                               "</div>";
                } else {
                        $courseData = array();
                        foreach($courseJSON['course'] as $course) {
                                $courseData[$course['id']] = $course['name'] . " (" . $course['identifier'] . ")";
                        }
                        asort($courseData);

                        $courseHTML = "<div id='echoLinkFormDiv'>" .
                                        $courseFilterHTML .
                                        "<div id='courseDiv'>" .
                                          "<div id='coursesData' style='margin-left: 20px; padding-bottom: 10px;'>";
                        foreach($courseData as $id => $name) {
                                $courseHTML .= "<div class='courseRecord' id='$id'>" .
                                                  "<a href='#' class='ess_course_link' id='$id' target='_parent'><label id='$id' style='font-weight: bold;'> + </label>&nbsp;$name</a>" .
                                                  "<div class='sectionDiv' id='$id'></div>" .
                                               "</div>";
                        }
                        $courseHTML .=    "</div>" .
                                        "</div>" .
                                      "</div>";
                        return $courseHTML;
                }
        } else {
                return "<div style='text-align: center;padding:10px;color:red;font-weight:bold;'>Communication error with the configured EchoSystem Server.<br />Please contact your EchoSystem Administrators for assistance.</div>";
        }
}// end of echolink_ess_get_rest_courses function


function echolink_ess_get_rest_person_courses($moodlePerson, $moodleCourse = null, $defaultFilter = null) {

        if($moodlePerson != null && $moodlePerson != '') {
                // Retrieve ESS Person XML by searching by their Moodle UserID (should be an Institution Staff-ID value and is used for the ESS Username).
                $personXML = callRestService(ESS_URL, ESS_CONSUMER_KEY, ESS_CONSUMER_SECRET, "people?filter=$moodlePerson", "", "GET", array());

                if($personXML != null && $personXML != '') {
                        $personJSON = convertXMLtoJSON($personXML, true, true);

                        if($personJSON['total-results'] == 0) {			// If Person record(s) are not found, return warning message no ESS User found for current Moodle User
				if($defaultFilter == '0') {
			                $courseFilterHTML = "<div id='headerDiv'>" .
                        			              "<div id='headerData'><h3 style='text-align:left;'>Echo360 Courses | Sections | Presentations</h3></div>" .
			                                      "<div style='float: right;'>Show By: <select name='SHOW_BY_FILTER' id='show_by_filter'><option value='show_all_ess_courses'>All Available Echo360 Courses</option><option value='show_my_ess_courses'>My Echo360 Courses</option></select></div>" .
			                                    "</div>";

		                        return "<div id='echoLinkFormDiv'>" .
		                                  $courseFilterHTML .
		                                 "<div id='courseDiv'>" .
						   "<div id='coursesData' style='margin-left: 20px; padding-bottom: 10px;'><div class='courseRecord'>No EchoSystem Courses are currently available for current Moodle User '$moodlePerson'.</div></div>" .
		                                 "</div>" .
		                               "</div>";
				} else {
                                	return "<div style='text-align: center;padding:10px;color:red;font-weight:bold;'>Moodle User '$moodlePerson' is not found in the configured EchoSystem Server.<br />Please contact your EchoSystem Administrators for assistance.</div>";
				}
                        } else if($personJSON['total-results'] >= 1) {          // Else Person record(s) are found
				$personUUID = "";

				if($personJSON['total-results'] == 1) {
	                                $personUUID = $personJSON['person']['id'];
				} else {
					foreach($personJSON['person'] as $person) {
						if($person['user-name'] == "$moodlePerson") {
							$personUUID = $person['id'];
							break;
						}
					}
				}

				if($personUUID == "") {
					if($defaultFilter == '0') {
			                	$courseFilterHTML = "<div id='headerDiv'>" .
                        				              "<div id='headerData'><h3 style='text-align:left;'>Echo360 Courses | Sections | Presentations</h3></div>" .
			                                	      "<div style='float: right;'>Show By: <select name='SHOW_BY_FILTER' id='show_by_filter'><option value='show_all_ess_courses'>All Available Echo360 Courses</option><option value='show_my_ess_courses'>My Echo360 Courses</option></select></div>" .
				                                    "</div>";

						return "<div id='echoLinkFormDiv'>" .
							  $courseFilterHTML .
			                                 "<div id='courseDiv'>" .
							   "<div id='coursesData' style='margin-left: 20px; padding-bottom: 10px;'><div class='courseRecord'>No EchoSystem Courses are currently available for current Moodle User '$moodlePerson'.</div></div>" .
			                                 "</div>" .
						       "</div>";
					} else {
						return "<div style='text-align: center;padding:10px;color:red;font-weight:bold;'>Moodle User '$moodlePerson' was not found in the configured EchoSystem Server.<br />Please contact your EchoSystem Administrators for assistance. ?</div>";
					}
				} else {
	                                $sectionRoleXML = callRestService(ESS_URL, ESS_CONSUMER_KEY, ESS_CONSUMER_SECRET, "presenters/$personUUID/sections", "", "GET", array());
        	                        $sectionRoleJSON = convertXMLtoJSON($sectionRoleXML, true, true);

					$courseFilterHTML = "";

					if($defaultFilter == '0') {
        	                        	$courseFilterHTML = "<div id='headerDiv'>" .
                	                                              "<div id='headerData'><h3 style='text-align:left;'>Echo360 Courses | Sections | Presentations</h3></div>" .
                        	                	              "<div style='float: right;'>Show By: <select name='SHOW_BY_FILTER' id='show_by_filter'><option value='show_all_ess_courses'>All Available Echo360 Courses</option><option value='show_my_ess_courses' selected>My Echo360 Courses</option></select></div>" .
		                                                    "</div>";
					}

                	                if($sectionRoleJSON['total-results'] == 0) {
                        	                return "<div id='echoLinkFormDiv'>" .
                                	                  $courseFilterHTML .
                                        	         "<div id='courseDiv'>" .
                                                	   "<div id='coursesData' style='margin-left: 20px; padding-bottom: 10px;'><div class='courseRecord'>No EchoSystem Courses are currently available for current Moodle User '$moodlePerson'.</div></div>" .
	                                                 "</div>" .
        	                                       "</div>";
                	                } else if($sectionRoleJSON['total-results'] == 1) {
                        	                if($sectionRoleJSON['section-role']['course-id'] == "") {
                                	                return "<div style='text-align: center;padding:10px;color:red;font-weight:bold;'>Unable to retrieve EchoSystem Courses for current Moodle User '$moodlePerson' due to EchoSystem API limitations.<br />Please upgrade to EchoSystem 5.3 or higher.</div>";
	                                        } else {
	                                                $id = $sectionRoleJSON['section-role']['course-id'];
        	                                        $name = $sectionRoleJSON['section-role']['course-name'] . " (" . $sectionRoleJSON['section-role']['course-identifier'] . ")";

                	                                return "<div id='echoLinkFormDiv'>" .
	                                                          $courseFilterHTML .
	                                                 	  "<div id='courseDiv'>" .
		                                                    "<div id='coursesData' style='margin-left: 20px; padding-bottom: 10px;'>" .
				                                      "<div class='courseRecord' id='$id'>" .
			        	                                "<a href='#' class='ess_course_link' id='$id' target='_parent'><label id='$id' style='font-weight: bold;'> + </label>$name</a>" .
				                                        "<div class='sectionDiv' id='$id'></div>" .
				                                      "</div>" .
								    "</div>" .
	                	                                  "</div>" .
                                	                       "</div>";
	                                        }
					} else {
						$sectionRoleData = array();
						foreach($sectionRoleJSON['section-role'] as $sectionRole) {
							$sectionRoleData[$sectionRole['course-id']] = $sectionRole['course-name'] . " (" . $sectionRole['course-identifier'] . ")";
						}
						asort($sectionRoleData);

						$courseHTML = "<div id='echoLinkFormDiv'>" .
								$courseFilterHTML .
								"<div id='courseDiv'>" .
								   "<div id='coursesData' style='margin-left: 20px; padding-bottom: 10px;'>";

						foreach($sectionRoleData as $id => $name) {
							$courseHTML .= "<div class='courseRecord' id='$id'>" .
										"<a href='#' class='ess_course_link' id='$id' target='_parent'><label id='$id' style='font-weight: bold;'> + </label>$name</a>" .
										"<div class='sectionDiv' id='$id'></div>" .
								       "</div>";
                                                }

						$courseHTML .=    "</div>" .
								"</div>" .
							       "</div>";
						return $courseHTML;
					}
				}
			}
		} else {
                	return "<div style='text-align: center;padding:10px;color:red;font-weight:bold;'>Communication error with the configured EchoSystem Server.<br />Please contact your EchoSystem Administrators for assistance.</div>";
		}
	}
}// end of echolink_ess_get_rest_person_courses function

function echolink_ess_get_rest_course_sections($essCourse) {

        // Retrieve ESS Section XML
        $sectionXML = callRestService(ESS_URL, ESS_CONSUMER_KEY, ESS_CONSUMER_SECRET, "courses/$essCourse/sections", "", "GET", array());

        if($sectionXML != null || $sectionXML != '') {
                $sectionJSON = convertXMLtoJSON($sectionXML, true, true);

                if($sectionJSON['total-results'] == 0) {
                        return "<div id='sectionData' style='margin-left: 20px; padding-bottom:10px;'><div class='sectionRecord'>No EchoSystem Sections are currently available for this Course.</div></div>";
                } else if($sectionJSON['total-results'] == 1) {
                        $id = $sectionJSON['section']['id'];
                        $name = $sectionJSON['section']['name'];

                        return "<div id='sectionData' style='margin-left: 20px; padding-bottom:10px;'>" .
                                  "<div class='sectionRecord' id='$id'>" .
                                    "<a href='#' class='ess_section_link' id='$id' target='_parent'><label id='$id' style='font-weight: bold'> + </label> $name</a>" .
                                    "<div class='presentationDiv' id='$id'></div>" .
                                  "</div>" .
                               "</div>";
                } else {
                        $sectionData = array();
                        foreach($sectionJSON['section'] as $section) {
                                $sectionData[$section['id']] = $section['name'];
                        }
                        asort($sectionData);

                        $courseSectionHTML = "<div id='sectionData' style='margin-left: 20px; padding-bottom:10px;'>";
                        foreach($sectionData as $id => $name) {
                                $courseSectionHTML .= "<div class='sectionRecord' id='$id'>" .
                                                        "<a href='#' class='ess_section_link' id='$id' target='_parent'><label id='$id' style='font-weight: bold'> + </label> $name</a>" .
                                                        "<div class='presentationDiv' id='$id'></div>" .
                                                      "</div>";
                        }
                        $courseSectionHTML .= "</div>";

                        return $courseSectionHTML;
                }
        }
}// end of echolink_ess_get_rest_course_sections function


function echolink_ess_get_rest_section($essSection) {

        // Retrieve ESS Section XML
        $sectionXML = callRestService(ESS_URL, ESS_CONSUMER_KEY, ESS_CONSUMER_SECRET, "sections/$essSection", "", "GET", array());

        if($sectionXML != null || $sectionXML != '') {
                $sectionJSON = convertXMLtoJSON($sectionXML, true, true);

                $ecpLink = "";
                foreach($sectionJSON['link'] as $link) {
                        if($link['@attributes']['title'] == "course-portal") {
                                $ecpLink = $link['@attributes']['href'];
                                break;
                        }
                }

                return "<div id='ecpLinkData' style='margin-left: 20px; padding-top: 5px;'>" .
                          "<div class='sectionRecordECPLink' id=''>&#8226; <i><a href='#' class='ess_presentation_link' id='$ecpLink'>EchoCenter Page Link</a></i></div>" .
                       "</div>";
        } else {
                return "<div style='text-align: center;padding:10px;color:red;font-weight:bold;'>Communication error with the configured EchoSystem Server.<br />Please contact your EchoSystem Administrators for assistance.</div>";
        }
}// end of echolink_ess_get_rest_section function


function echolink_ess_get_rest_section_presentations($essSection) {

        // Retrieve ESS Section Presentation XML
        $sectionPresentationXML = callRestService(ESS_URL, ESS_CONSUMER_KEY, ESS_CONSUMER_SECRET, "sections/$essSection/presentations", "", "GET", array());

        if($sectionPresentationXML != null || $sectionPresentationXML != '') {
                $sectionPresentationJSON = convertXMLtoJSON($sectionPresentationXML, true, true);

                if($sectionPresentationJSON['total-results'] == 0) {
                        return "<div id='presentationLinkData' style='margin-left: 40px; padding-bottom: 5px;'><div class='presentationRecord'>&#8226; No Echo360 Presentations are currently available.</div></div>";
                } else if($sectionPresentationJSON['total-results'] == 1) {
			if($sectionPresentationJSON['presentation']['status'] == "presentation-status-available") {
				$id = $sectionPresentationJSON['presentation']['id'];
				$time = date("Y-m-d H:i", strtotime($sectionPresentationJSON['presentation']['start-time']));
				$title = $sectionPresentationJSON['presentation']['title'];

	                        return "<div id='presentationLinkData' style='margin-left: 40px; padding-bottom: 5px;'>" .
	                                   "<div class='presentationRecord'>&#8226; <span style='color: gray;'>$time</span> - <i><a href='#' class='ess_presentation_link' id='$id'>$title</a></i>";
        	                       "</div>";
			} else {
                        	return "<div id='presentationLinkData' style='margin-left: 40px; padding-bottom: 5px;'><div class='presentationRecord'>&#8226; No Echo360 Presentations are currently available.</div></div>";
			}
                } else {
                        $presentationData = array();
                        foreach($sectionPresentationJSON['presentation'] as $presentation) {
                                if($presentation['status'] == "presentation-status-available") {
                                        $presentationData[$presentation['id']] = date("Y-m-d H:i", strtotime($presentation['start-time'])) . " ||--|| " . $presentation['title'];
                                }
                        }
                        arsort($presentationData);

                        $presentationHTML = "<div id='presentationLinkData' style='margin-left: 40px; padding-bottom: 5px;'>";
                        foreach($presentationData as $id => $title) {
                                $_title = explode(" ||--|| ", $title);
                                $presentationHTML .= "<div class='presentationRecord'>&#8226; <span style='color: gray;'>" . $_title[0] . "</span> - <i><a href='#' class='ess_presentation_link' id='$id'>" . $_title[1] . "</a></i>";
                        }
                        $presentationHTML .= "</div>";

                        return $presentationHTML;
                }
        } else {
                // Do nothing - error message would have already been displayed
        }
}// end of echolink_ess_get_rest_section_presentations function


function echolink_ess_get_rest_presentation($essPresentation) {

        // Retrieve ESS Section XML
        $presentationXML = callRestService(ESS_URL, ESS_CONSUMER_KEY, ESS_CONSUMER_SECRET, "presentations/$essPresentation", "", "GET", array());

	return convertXMLtoJSON($presentationXML, true, false);
}// end of echolink_ess_get_rest_presentation function
//-------------------------------------------------------------


//-------------------------------------------------------------
function convertXMLtoJSON($xml, $encode, $decode) {
	$xml = str_replace('<?xml version="1.0"?>', '', $xml);
	$xml = str_replace(array("\n", "\r", "\t"), '', $xml);
	$xml = trim(str_replace('"', "'", $xml));

	if($encode == false && $decode == false) {
		return simplexml_load_string($xml);
	} else if($encode == true && $decode == false) {
		return json_encode(simplexml_load_string($xml));
	} else if($encode == true && $decode == true) {
		return json_decode(json_encode(simplexml_load_string($xml)), true);
	}
}// end of convertXMLtoJSON function
//-------------------------------------------------------------


//-------------------------------------------------------------
function echolink_ess_oauth_seamless_login($echolink) {
        global $USER;

        $config = get_config('echolink');
        $essURL = $config->ESS_URL;
        $essConsumerKey = $config->ESS_Consumer_Key;
        $essConsumerSecret = $config->ESS_Consumer_Secret;
	$realm = "";

	$isInstructor = true;

	$essSSOLogin = new EchoSystemSeamlessLogin($essURL, $essConsumerKey, $essConsumerSecret, $realm);
	$ssoResponse = $essSSOLogin->generate_sso_url($echolink, $USER, $isInstructor, true);

        if($ssoResponse['success'] == true) {
            // we want to test for a 404
            $curl = $essSSOLogin->get_curl_with_defaults();
            $headers = $essSSOLogin->get_headers($curl, $ssoResponse['url'], 1);

            if (!strstr($headers[0]['http'], "302")) {
                $error_message = 'unexpected_response';
                $e = explode(" ", $headers[0]['http'], 3);
                $error_detail = $e[2];
            } else if (strstr($headers[1]['http'], "404")) {
                $error_message = 'not_found_response';
                $e = explode(" ", $headers[1]['http'], 3);
                $error_detail = $e[2];
            } else if (strstr($headers[1]['http'], "403")) {
                $error_message = 'forbidden_response';
                $e = explode(" ", $headers[1]['http'], 3);
                $error_detail = $e[2];
            } else if (!strstr($headers[1]['http'], "200")) {
                $error_message = 'unexpected_response';
                $e = explode(" ", $headers[1]['http'], 3);
                $error_detail = $e[2];
            }
            curl_close($curl);

	    if ($error_message == "") {
	        // All good - but we already used the request - need to sign again (generate a new nonce)
	        $ssoResponse = $essSSOLogin->generate_sso_url($echolink, $USER, $isInstructor, true);
	        header("Location:" . $ssoResponse['url']);
	    } else {
	        print_error($error_message, 'mod_echolink', '', $error_detail);
	    }
	}

	return;
}// end of echolink_ess_oauth_seamless_login function

//-------------------------------------------------------------



//-------------------------------------------------------------

/**
 * This methods does weak echolink validation, we are looking for major problems only,
 * no strict RFE validation.
 *
 * @param $echolink
 * @return bool true is seems valid, false if definitely not valid URL
 */
function echolink_appears_valid_echolink($echolink) {
    if (preg_match('/^(\/|https?:|ftp:)/i', $echolink)) {
        // note: this is not exact validation, we look for severely malformed URLs only
        return (bool)preg_match('/^[a-z]+:\/\/([^:@\s]+:[^@\s]+@)?[a-z0-9_\.\-]+(:[0-9]+)?(\/[^#]*)?(#.*)?$/i', $echolink);
    } else {
        return (bool)preg_match('/^[a-z]+:\/\/...*$/i', $echolink);
    }
}

/**
 * Fix common URL problems that we want teachers to see fixed
 * the next time they edit the resource.
 *
 * This function does not include any XSS protection.
 *
 * @param string $echolink
 * @return string
 */
function echolink_fix_submitted_echolink($echolink) {
    // note: empty echolinks are prevented in form validation
    $echolink = trim($echolink);

    // remove encoded entities - we want the raw URI here
    $echolink = html_entity_decode($echolink, ENT_QUOTES, 'UTF-8');

    if (!preg_match('|^[a-z]+:|i', $echolink) and !preg_match('|^/|', $echolink)) {
        // invalid URI, try to fix it by making it normal URL,
        // please note relative echolinks are not allowed, /xx/yy links are ok
        $echolink = 'http://'.$echolink;
    }

    return $echolink;
}

/**
 * Return full echolink with all extra parameters
 *
 * This function does not include any XSS protection.
 *
 * @param string $echolink
 * @param object $cm
 * @param object $course
 * @param object $config
 * @return string echolink with & encoded as &amp;
 */
function echolink_get_full_echolink($echolink, $cm, $course, $config=null) {

    $parameters = empty($echolink->parameters) ? array() : unserialize($echolink->parameters);

    // make sure there are no encoded entities, it is ok to do this twice
    $fullecholink = html_entity_decode($echolink->externalecholink, ENT_QUOTES, 'UTF-8');

    if (preg_match('/^(\/|https?:|ftp:)/i', $fullecholink) or preg_match('|^/|', $fullecholink)) {
        // encode extra chars in URLs - this does not make it always valid, but it helps with some UTF-8 problems
        $allowed = "a-zA-Z0-9".preg_quote(';/?:@=&$_.+!*(),-#%', '/');
        $fullecholink = preg_replace_callback("/[^$allowed]/", 'echolink_filter_callback', $fullecholink);
    } else {
        // encode special chars only
        $fullecholink = str_replace('"', '%22', $fullecholink);
        $fullecholink = str_replace('\'', '%27', $fullecholink);
        $fullecholink = str_replace(' ', '%20', $fullecholink);
        $fullecholink = str_replace('<', '%3C', $fullecholink);
        $fullecholink = str_replace('>', '%3E', $fullecholink);
    }

    // add variable echolink parameters
    if (!empty($parameters)) {
        if (!$config) {
            $config = get_config('echolink');
        }
        $paramvalues = echolink_get_variable_values($echolink, $cm, $course, $config);

        foreach ($parameters as $parse=>$parameter) {
            if (isset($paramvalues[$parameter])) {
                $parameters[$parse] = rawecholinkencode($parse).'='.rawecholinkencode($paramvalues[$parameter]);
            } else {
                unset($parameters[$parse]);
            }
        }

        if (!empty($parameters)) {
            if (stripos($fullecholink, 'teamspeak://') === 0) {
                $fullecholink = $fullecholink.'?'.implode('?', $parameters);
            } else {
                $join = (strpos($fullecholink, '?') === false) ? '?' : '&';
                $fullecholink = $fullecholink.$join.implode('&', $parameters);
            }
        }
    }

    // encode all & to &amp; entity
    $fullecholink = str_replace('&', '&amp;', $fullecholink);

    return $fullecholink;
}

/**
 * Unicode encoding helper callback
 * @internal
 * @param array $matches
 * @return string
 */
function echolink_filter_callback($matches) {
    return rawecholinkencode($matches[0]);
}

/**
 * Print echolink header.
 * @param object $echolink
 * @param object $cm
 * @param object $course
 * @return void
 */
function echolink_print_header($echolink, $cm, $course) {
    global $PAGE, $OUTPUT;

    $PAGE->set_title($course->shortname.': '.$echolink->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($echolink);
    echo $OUTPUT->header();
}

/**
 * Print echolink heading.
 * @param object $echolink
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function echolink_print_heading($echolink, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($echolink->displayoptions) ? array() : unserialize($echolink->displayoptions);

    if ($ignoresettings or !empty($options['printheading'])) {
        echo $OUTPUT->heading(format_string($echolink->name), 2, 'main', 'echolinkheading');
    }
}

/**
 * Print echolink introduction.
 * @param object $echolink
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function echolink_print_intro($echolink, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($echolink->displayoptions) ? array() : unserialize($echolink->displayoptions);
    if ($ignoresettings or !empty($options['printintro'])) {
        if (trim(strip_tags($echolink->intro))) {
            echo $OUTPUT->box_start('mod_introbox', 'echolinkintro');
            echo format_module_intro('echolink', $echolink, $cm->id);
            echo $OUTPUT->box_end();
        }
    }
}

/**
 * Display echolink frames.
 * @param object $echolink
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function echolink_display_frame($echolink, $cm, $course) {
    global $PAGE, $OUTPUT, $CFG;

    $frame = optional_param('frameset', 'main', PARAM_ALPHA);

    if ($frame === 'top') {
        $PAGE->set_pagelayout('frametop');
        echolink_print_header($echolink, $cm, $course);
        echolink_print_heading($echolink, $cm, $course);
        echolink_print_intro($echolink, $cm, $course);
        echo $OUTPUT->footer();
        die;

    } else {
        $config = get_config('echolink');
        $context = context_module::instance($cm->id);
        $exteecholink = echolink_get_full_echolink($echolink, $cm, $course, $config);
        $navecholink = "$CFG->wwwroot/mod/echolink/view.php?id=$cm->id&amp;frameset=top";
        $coursecontext = context_course::instance($course->id);
        $courseshortname = format_string($course->shortname, true, array('context' => $coursecontext));
        $title = strip_tags($courseshortname.': '.format_string($echolink->name));
        $framesize = $config->framesize;
        $modulename = s(get_string('modulename','echolink'));
        $contentframetitle = format_string($echolink->name);
        $dir = get_string('thisdirection', 'langconfig');

        $extframe = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html dir="$dir">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>$title</title>
  </head>
  <frameset rows="$framesize,*">
    <frame src="$navecholink" title="$modulename"/>
    <frame src="$exteecholink" title="$contentframetitle"/>
  </frameset>
</html>
EOF;

        @header('Content-Type: text/html; charset=utf-8');
        echo $extframe;
        die;
    }
}

/**
 * Print echolink info and link.
 * @param object $echolink
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function echolink_print_workaround($echolink, $cm, $course) {
    global $OUTPUT;

    echolink_print_header($echolink, $cm, $course);
    echolink_print_heading($echolink, $cm, $course, true);
    echolink_print_intro($echolink, $cm, $course, true);

    $fullecholink = echolink_get_full_echolink($echolink, $cm, $course);

    $display = echolink_get_final_display_type($echolink);
    if ($display == RESOURCELIB_DISPLAY_POPUP) {
        $jsfullecholink = addslashes_js($fullecholink);
        $options = empty($echolink->displayoptions) ? array() : unserialize($echolink->displayoptions);
        $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $extra = "onclick=\"window.open('$jsfullecholink', '', '$wh'); return false;\"";

    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $extra = "onclick=\"this.target='_blank';\"";

    } else {
        $extra = '';
    }

    echo '<div class="echolinkworkaround">';
    print_string('clicktoopen', 'echolink', "<a href=\"$fullecholink\" $extra>$fullecholink</a>");
    echo '</div>';

    echo $OUTPUT->footer();
    die;
}

/**
 * Display embedded echolink file.
 * @param object $echolink
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function echolink_display_embed($echolink, $cm, $course) {
    global $CFG, $PAGE, $OUTPUT;

    $mimetype = resourcelib_guess_url_mimetype($echolink->externalecholink);
    $fullecholink  = echolink_get_full_echolink($echolink, $cm, $course);
    $title    = $echolink->name;

    $link = html_writer::tag('a', $fullecholink, array('href'=>str_replace('&amp;', '&', $fullecholink)));
    $clicktoopen = get_string('clicktoopen', 'echolink', $link);
    $moodleecholink = new moodle_echolink($fullecholink);

    $extension = resourcelib_get_extension($echolink->externalecholink);

    $mediarenderer = $PAGE->get_renderer('core', 'media');
    $embedoptions = array(
        core_media::OPTION_TRUSTED => true,
        core_media::OPTION_BLOCK => true
    );

    if (in_array($mimetype, array('image/gif','image/jpeg','image/png'))) {  // It's an image
        $code = resourcelib_embed_image($fullecholink, $title);

    } else if ($mediarenderer->can_embed_echolink($moodleecholink, $embedoptions)) {
        // Media (audio/video) file.
        $code = $mediarenderer->embed_echolink($moodleecholink, $title, 0, 0, $embedoptions);

    } else {
        // anything else - just try object tag enlarged as much as possible
        $code = resourcelib_embed_general($fullecholink, $title, $clicktoopen, $mimetype);
    }

    echolink_print_header($echolink, $cm, $course);
    echolink_print_heading($echolink, $cm, $course);

    echo $code;

    echolink_print_intro($echolink, $cm, $course);

    echo $OUTPUT->footer();
    die;
}

/**
 * Decide the best display format.
 * @param object $echolink
 * @return int display type constant
 */
function echolink_get_final_display_type($echolink) {
    global $CFG;

    if ($echolink->display != RESOURCELIB_DISPLAY_AUTO) {
        return $echolink->display;
    }

    // detect links to local moodle pages
    if (strpos($echolink->externalecholink, $CFG->wwwroot) === 0) {
        if (strpos($echolink->externalecholink, 'file.php') === false and strpos($echolink->externalecholink, '.php') !== false ) {
            // most probably our moodle page with navigation
            return RESOURCELIB_DISPLAY_OPEN;
        }
    }

    static $download = array('application/zip', 'application/x-tar', 'application/g-zip',     // binary formats
                             'application/pdf', 'text/html');  // these are known to cause trouble for external links, sorry
    static $embed    = array('image/gif', 'image/jpeg', 'image/png', 'image/svg+xml',         // images
                             'application/x-shockwave-flash', 'video/x-flv', 'video/x-ms-wm', // video formats
                             'video/quicktime', 'video/mpeg', 'video/mp4',
                             'audio/mp3', 'audio/x-realaudio-plugin', 'x-realaudio-plugin',   // audio formats,
                            );

    $mimetype = resourcelib_guess_url_mimetype($echolink->externalecholink);

    if (in_array($mimetype, $download)) {
        return RESOURCELIB_DISPLAY_DOWNLOAD;
    }
    if (in_array($mimetype, $embed)) {
        return RESOURCELIB_DISPLAY_EMBED;
    }

    // let the browser deal with it somehow
    return RESOURCELIB_DISPLAY_OPEN;
}

/**
 * Get the parameters that may be appended to URL
 * @param object $config echolink module config options
 * @return array array describing opt groups
 */
function echolink_get_variable_options($config) {
    global $CFG;

    $options = array();
    $options[''] = array('' => get_string('chooseavariable', 'echolink'));

    $options[get_string('course')] = array(
        'courseid'        => 'id',
        'coursefullname'  => get_string('fullnamecourse'),
        'courseshortname' => get_string('shortnamecourse'),
        'courseidnumber'  => get_string('idnumbercourse'),
        'coursesummary'   => get_string('summary'),
        'courseformat'    => get_string('format'),
    );

    $options[get_string('modulename', 'echolink')] = array(
        'echolinkinstance'     => 'id',
        'echolinkcmid'         => 'cmid',
        'echolinkname'         => get_string('name'),
        'echolinkidnumber'     => get_string('idnumbermod'),
    );

    $options[get_string('miscellaneous')] = array(
        'sitename'        => get_string('fullsitename'),
        'serverecholink'       => get_string('serverecholink', 'echolink'),
        'currenttime'     => get_string('time'),
        'lang'            => get_string('language'),
    );
    if (!empty($config->secretphrase)) {
        $options[get_string('miscellaneous')]['encryptedcode'] = get_string('encryptedcode');
    }

    $options[get_string('user')] = array(
        'userid'          => 'id',
        'userusername'    => get_string('username'),
        'useridnumber'    => get_string('idnumber'),
        'userfirstname'   => get_string('firstname'),
        'userlastname'    => get_string('lastname'),
        'userfullname'    => get_string('fullnameuser'),
        'useremail'       => get_string('email'),
        'usericq'         => get_string('icqnumber'),
        'userphone1'      => get_string('phone').' 1',
        'userphone2'      => get_string('phone2').' 2',
        'userinstitution' => get_string('institution'),
        'userdepartment'  => get_string('department'),
        'useraddress'     => get_string('address'),
        'usercity'        => get_string('city'),
        'usertimezone'    => get_string('timezone'),
        'userecholink'         => get_string('webpage'),
    );

    if ($config->rolesinparams) {
        $roles = role_fix_names(get_all_roles());
        $roleoptions = array();
        foreach ($roles as $role) {
            $roleoptions['course'.$role->shortname] = get_string('yourwordforx', '', $role->localname);
        }
        $options[get_string('roles')] = $roleoptions;
    }

    return $options;
}

/**
 * Get the parameter values that may be appended to URL
 * @param object $echolink module instance
 * @param object $cm
 * @param object $course
 * @param object $config module config options
 * @return array of parameter values
 */
function echolink_get_variable_values($echolink, $cm, $course, $config) {
    global $USER, $CFG;

    $site = get_site();

    $coursecontext = context_course::instance($course->id);

    $values = array (
        'courseid'        => $course->id,
        'coursefullname'  => format_string($course->fullname),
        'courseshortname' => format_string($course->shortname, true, array('context' => $coursecontext)),
        'courseidnumber'  => $course->idnumber,
        'coursesummary'   => $course->summary,
        'courseformat'    => $course->format,
        'lang'            => current_language(),
        'sitename'        => format_string($site->fullname),
        'serverecholink'       => $CFG->wwwroot,
        'currenttime'     => time(),
        'echolinkinstance'     => $echolink->id,
        'echolinkcmid'         => $cm->id,
        'echolinkname'         => format_string($echolink->name),
        'echolinkidnumber'     => $cm->idnumber,
    );

    if (isloggedin()) {
        $values['userid']          = $USER->id;
        $values['userusername']    = $USER->username;
        $values['useridnumber']    = $USER->idnumber;
        $values['userfirstname']   = $USER->firstname;
        $values['userlastname']    = $USER->lastname;
        $values['userfullname']    = fullname($USER);
        $values['useremail']       = $USER->email;
        $values['usericq']         = $USER->icq;
        $values['userphone1']      = $USER->phone1;
        $values['userphone2']      = $USER->phone2;
        $values['userinstitution'] = $USER->institution;
        $values['userdepartment']  = $USER->department;
        $values['useraddress']     = $USER->address;
        $values['usercity']        = $USER->city;
        $values['usertimezone']    = get_user_timezone_offset();
        $values['userecholink']    = $USER->echolink;
    }

    // weak imitation of Single-Sign-On, for backwards compatibility only
    // NOTE: login hack is not included in 2.0 any more, new contrib auth plugin
    //       needs to be createed if somebody needs the old functionality!
    if (!empty($config->secretphrase)) {
        $values['encryptedcode'] = echolink_get_encrypted_parameter($echolink, $config);
    }

    //hmm, this is pretty fragile and slow, why do we need it here??
    if ($config->rolesinparams) {
        $coursecontext = context_course::instance($course->id);
        $roles = role_fix_names(get_all_roles($coursecontext), $coursecontext, ROLENAME_ALIAS);
        foreach ($roles as $role) {
            $values['course'.$role->shortname] = $role->localname;
        }
    }

    return $values;
}

/**
 * BC internal function
 * @param object $echolink
 * @param object $config
 * @return string
 */
function echolink_get_encrypted_parameter($echolink, $config) {
    global $CFG;

    if (file_exists("$CFG->dirroot/local/externserverfile.php")) {
        require_once("$CFG->dirroot/local/externserverfile.php");
        if (function_exists('extern_server_file')) {
            return extern_server_file($echolink, $config);
        }
    }
    return md5(getremoteaddr().$config->secretphrase);
}

/**
 * Optimised mimetype detection from general URL
 * @param $fullecholink
 * @param int $size of the icon.
 * @return string|null mimetype or null when the filetype is not relevant.
 */
function echolink_guess_icon($fullecholink, $size = null) {
    global $CFG;
    require_once("$CFG->libdir/filelib.php");

    if (substr_count($fullecholink, '/') < 3 or substr($fullecholink, -1) === '/') {
        // Most probably default directory - index.php, index.html, etc. Return null because
        // we want to use the default module icon instead of the HTML file icon.
        return null;
    }

    $icon = file_extension_icon($fullecholink, $size);
    $htmlicon = file_extension_icon('.htm', $size);
    $unknownicon = file_extension_icon('', $size);

    // We do not want to return those icon types, the module icon is more appropriate.
    if ($icon === $unknownicon || $icon === $htmlicon) {
        return null;
    }

    return $icon;
}
//-------------------------------------------------------------


//-------------------------------------------------------------
// Functions to perform XSL transformations from XML to HTML

function echolink_transform_xml_to_html($xsl, $xml) {
        $xslDoc = new DOMDocument();
        $xslDoc->load($xsl);

        // Allocate a new XSLT processor
        $xslt = new XSLTProcessor();
        $xslt->importStylesheet($xslDoc);

        return $xslt->transformToXML(new SimpleXMLElement($xml));
}// end of echolink_transform_xml_to_html function

//-------------------------------------------------------------
