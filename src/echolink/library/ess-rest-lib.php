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

defined('MOODLE_INTERNAL') || die();

include_once($CFG->dirroot."/mod/echolink/library/oauth-lib/OAuthStore.php");
include_once($CFG->dirroot."/mod/echolink/library/oauth-lib/OAuthRequester.php");

function callRestService($essURL, $essConsumerKey, $essConsumerSecret, $path, $data, $method, $params) {
    try {
        $options = array('consumer_key' => $essConsumerKey, 'consumer_secret' => $essConsumerSecret);
        OAuthStore::instance("2Leg", $options);
        $curl_options = array(CURLOPT_SSL_VERIFYPEER => false, CURLOPT_HTTPHEADER => Array("Content-Type: application/xml"));
        $request = new OAuthRequester($essURL . $path, $method, $params, $data);
        $result = $request->doRequest(0, $curl_options);
        $data = $result['body'];
    } catch (Exception $e) {
        error_log('Echo exception: ' . $e->getMessage());
        return '';
    }

    return $data;
}// end of callRestService function
?>
