<?php

// This file is part of the Echo360 Moodle Plugin - http://moodle.org/
//
// The Echo360 Moodle Plugin is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// The Echo360 Moodle Plugin is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with the Echo360 Moodle Plugin.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file provides a wrapper for the EchoSystem seamless login api
 *
 * @package    mod
 * @subpackage echolink
 * @copyright  (c) 2013, Echo360 Inc.  www.echo360.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
include_once($CFG->dirroot."/mod/echolink/library/ess-oauth-lib.php");

/**
 * This class is a wrapper around the EchoSystem Seamless login API
 *
 * @copyright 2011 Echo360 Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later 
 */
class EchoSystemSeamlessLogin {

    /**
     * Default ESS Trusted System global variables with example values -- the values for these variables are overridden by the constructor
     */
    private $baseURL = "";
    private $consumerKey = "";
    private $consumerSecret = "";
    private $realm = "";	// Optional realm setting
    private $sessionkey = "";

    /**
     * Save the baseURL, consumer key, consumer secret and realm.
     *
     * If the base url does not end in '/' add one.
     *
     * @param string - baseURL
     * @param string - consumer key
     * @param string - consumer secret
     * @param string - realm
     */
    function __construct($baseURL, $consumerKey, $consumerSecret, $realm) {
        if ($baseURL != null) {
            $this->baseURL = $baseURL;
            if (!$this->baseURL[strlen($this->baseURL) -1] === '/') {
                $this->baseURL .= '/';
            }
        }
        if ($consumerKey != null) {
            $this->consumerKey = $consumerKey;
        }
        if ($consumerSecret != null) {
            $this->consumerSecret = trim($consumerSecret);
        }
        if ($realm != null) {
            $this->realm = $realm;
        }
    }// end of constructor


    /**
     * Sign a request
     *
     * Returned is an array with multiple values
     * the response['success'] is a boolean to indicate a failure
     * the response['message'] is a description of any failures
     * the response['url'] is the signed url
     *
     * @param string - url to request
     * @param array - the parameters
     * @param string - the http method
     * @return array
     */
    private function sign_oauth_request($url, $params, $method) {
        $response = array('success' => false,
                          'url' => '',
                          'message' => '');
        try {
            $consumer = new echo360_oauth_consumer($this->consumerKey, $this->consumerSecret, NULL);

            // empty token for 2 legged oauth
            $oauthrequest = echo360_oauth_request::from_consumer_and_token($consumer, new echo360_oauth_token('', ''), $method, $url, $params);

            $oauthrequest->sign_request(new echo360_oauth_signature_method_hmacsha1(), $consumer, NULL);

            $url = $oauthrequest->to_url();

            $response['success'] = true;
            $response['message'] = 'success';
            $response['url'] = $url;
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = print_r($e);
            $response['url'] = '';
        }
        return $response;
    }// end of sign_oauth_request function


    /**
     * Returns a curl handle set with the standard set of options required to talk to EchoSystem
     *
     * @return curl
     */
    public function get_curl_with_defaults() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_VERBOSE, 1); 
        return $ch;
    }// end of get_curl_with_defaults function


    public function get_headers($curl, $url, $redirects=1) {
        $headers = array();
        $cookie = '';
        while ($redirects >= 0) {
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            if ($cookie != '') {
                $cookieheaders = array("Cookie: $cookie");
                curl_setopt($curl, CURLOPT_HTTPHEADER, $cookieheaders);
            }

            $result = curl_exec($curl);
            $error = curl_error($curl);
            if ($error !== '') {
                $headers['error'] = $error;
                return $headers;
            }

            // add new entry to headers array
            $headers[] = array();
            $headers[count($headers) - 1]['http'] = strtok($result, "\r\n");

            $header = strtok("\r\n");
            while ($header !== false) {
                $split = explode(": ", $header, 2);
                if (count($split) > 1) {
                    $headers[count($headers) - 1][$split[0]] = $split[1];
                    // get the next url
                    if ($split[0] === "Location") {
                        $url = $split[1];
                    }
                    // get the cookies
                    if ($split[0] === "Set-Cookie") {
                        $cookie = strtok($split[1], ";");
                    }
                }
                $header = strtok("\r\n");
            }

            $redirects -= 1;
        }
        return $headers;
    }// end of get_headers function

    
    /**
     * Generate a SSO URL for this course.
     * The response is the same as sign_oauth_request above.
     *
     * @param string - Echo360 Link
     * @param string - Username
     * @param boolean - Is an instructor
     * @param boolean - Show a heading with branding for the course (false for iframe)
     * @return array
     */
    public function generate_sso_url($essURL, $userObject, $isInstructor, $showHeading) {

        // This is the Echo360 Link for seamless login access
	$essURL = $essURL . '?showheading=' . ($show_heading?"true":"false");
        $essURL .= "&firstname=" . trim($userObject->firstname);
        $essURL .= "&lastname=" . trim($userObject->lastname);
        $essURL .= "&email=" . trim($userObject->email);
        $essURL .= "&instructor=" . ($isInstructor?'true':'false');

        $apiurl = $this->baseURL . 'ess/personapi/v1/' . urlencode($userObject->username) . '/session';
        $apiparams = array('redirecturl' => $essURL);

        // Generate OAuth URL for seamless login
        $ssoResponse = $this->sign_oauth_request($apiurl, $apiparams, 'GET');
        
        return $ssoResponse;
    }// end of generate_sso_url function

}// end of EchoSystemSeamlessLogin class

?>
