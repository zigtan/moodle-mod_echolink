/**
 * @namespace
 */
M.mod_echolink = {};

/**
 * This function is initialized from PHP
 *
 * @param {Object} Y YUI instance
 */
M.mod_echolink.init = function(Y, args) {

	var essFilterEventURL = args['essFilterEventURL'];
	var essCourseEventURL = args['essCourseEventURL'];
	var essSectionEventURL = args['essSectionEventURL'];
	var essPresentationEventURL = args['essPresentationEventURL'];

	function addEventHandlers() {
		if(document.getElementById("show_by_filter") != null)
			Y.on('change', essFilterEventHandler, '#show_by_filter');
		Y.on('click', essCourseEventHandler, '.ess_course_link');
	}// end of addEventHandlers function


	function essFilterEventHandler(e) {
		var _e = document.getElementById(e.target.get('id'));
		var filterOption = _e.options[_e.selectedIndex].value; 
		var echolinkEventURL = essFilterEventURL + '&filter_option=' + filterOption;

		var callbackHandler = {
			success: function(o) {
					var responseText = "";

					if(o['status'] === 200) {
						responseText = o['responseText'];
					} else {
						responseText = "Error " + o['status'] + " - unable to retrieve ESS Courses";
					}

					document.getElementById('echoLinkFormDiv').innerHTML = responseText;

					var __e = document.getElementById(e.target.get('id'));
					__e.value = filterOption;				// Updating Filter Option to previously selected option

					Y.on('change', essFilterEventHandler, '#show_by_filter');
					Y.on('click', essCourseEventHandler, '.ess_course_link');
				 },
			failure: function(o) { },
		};

        	Y.use('yui2-connection', function(Y) {
			if (typeof YAHOO != "undefined") {
				YAHOO.util.Connect.asyncRequest('GET', echolinkEventURL, callbackHandler);	// Yahoo YUI AJAX call for Moodle 2.2
			} else {
				Y.YUI2.util.Connect.asyncRequest('GET', echolinkEventURL, callbackHandler);	// Yahoo YUI2 AJAX call for Moodle 2.3+
			}
		});
	}// end of essFilterEventHandler function


	function essCourseEventHandler(e) {
		e.preventDefault();

		var echolinkEventURL = essCourseEventURL + e.target.get('id');
		var callbackHandler = {
			success: function(o) {
					var responseText = "";

					if(o['status'] === 200) {
						responseText = o['responseText'];
					} else {
						responseText = "Error " + o['status'] + " - unable to retrieve ESS Sections";
					}

					document.getElementById(e.target.get('id')).getElementsByTagName('label')[0].innerHTML = ' - ';
					document.getElementById(e.target.get('id')).getElementsByClassName('sectionDiv')[0].innerHTML = responseText;

					Y.on('click', essSectionEventHandler, '.ess_section_link');
			 	 },
			failure: function(o) {
					console.log("Failure: " + o.toSource());
				 },
		};

        	Y.use('yui2-connection', function(Y) {
			var label = document.getElementById(e.target.get('id')).getElementsByTagName('label')[0].innerHTML;

			if(label === ' + ') {
				if (typeof YAHOO != "undefined") {
					YAHOO.util.Connect.asyncRequest('GET', echolinkEventURL, callbackHandler);	// Yahoo YUI AJAX call for Moodle 2.2
				} else {
					Y.YUI2.util.Connect.asyncRequest('GET', echolinkEventURL, callbackHandler);	// Yahoo YUI2 AJAX call for Moodle 2.3+
				}
			} else if(label === ' - ') {
				document.getElementById(e.target.get('id')).getElementsByTagName('label')[0].innerHTML = ' + ';
				document.getElementById(e.target.get('id')).getElementsByClassName('sectionDiv')[0].innerHTML = '';
			}
		});
	}// end of essCourseEventHandler function


	function essSectionEventHandler(e) {
		e.preventDefault();

		var echolinkEventURL = essSectionEventURL + e.target.get('id');

		var callbackHandler = {
			success: function(o) {
					var responseText = "";

					if(o['status'] === 200) {
						responseText = o['responseText'];
					} else {
						responseText = "Error " + o['status'] + " - unable to retrieve ESS Presentations";
					}

					document.getElementById(e.target.get('id')).getElementsByTagName('label')[0].innerHTML = ' - ';
					document.getElementById(e.target.get('id')).getElementsByClassName('presentationDiv')[0].innerHTML = responseText;

					Y.on('click', essECPPresentationEventHandler, '.ess_presentation_link');
				 },
			failure: function(o) {
					console.log("Failure: " + o.toSource()); 
				 },
		};

        	Y.use('yui2-connection', function(Y) {
			var label = document.getElementById(e.target.get('id')).getElementsByTagName('label')[0].innerHTML;

			if(label === ' + ') {
				if (typeof YAHOO != "undefined") {
					YAHOO.util.Connect.asyncRequest('GET', echolinkEventURL, callbackHandler);	// Yahoo YUI AJAX call for Moodle 2.2
				} else {
					Y.YUI2.util.Connect.asyncRequest('GET', echolinkEventURL, callbackHandler);	// Yahoo YUI2 AJAX call for Moodle 2.3+
				}
			} else if(label === ' - ') {
				document.getElementById(e.target.get('id')).getElementsByTagName('label')[0].innerHTML = ' + ';
				document.getElementById(e.target.get('id')).getElementsByClassName('presentationDiv')[0].innerHTML = '';	
			}
		});
	}// end of essSectionEventHandler function 


	function essECPPresentationEventHandler(e) {
		e.preventDefault();

		// Check if selected Echo360 Link is for ECP or Presentation
		if(e.target.get('id').indexOf('ess/portal/section/') != -1) {			// ESS ECP Link

			document.getElementById('id_name').value = document.getElementById(e.target.get('id')).innerHTML;		// Update Echo360 Link Name field but allow user to customise it if required
			document.getElementById('externalecholink').value = e.target.get('id');						// Update Echo360 Link URL field -- read-only, cannot be customised

			if(document.getElementById('previousecholink').value != null && document.getElementById('previousecholink').value != "") {
				document.getElementById(document.getElementById('previousecholink').value).removeAttribute("style");	// Remove highlight for previous Echo360 Link URL clicked
			}

			document.getElementById('previousecholink').value = document.getElementById('externalecholink').value;		// Store the previous Echo360 Link URL - to track the highlight changes from clicks
			document.getElementById(e.target.get('id')).style.color="red";

		} else {									// ESS Presentation ID
			var essPresentationUUID = e.target.get('id');
			var echolinkEventURL = essPresentationEventURL + e.target.get('id');

	                var callbackHandler = {
        	                success: function(o) {
	                                        var responseText = "";

                                        	if(o['status'] === 200) {
	                                                responseText = o['responseText'];
							responseText = JSON.parse(responseText);
                                	        } else {
                	                                responseText = "Error " + o['status'] + " - unable to retrieve ESS Presentations";
                        	                }

						var presentationURL = "";

						// Extract ESS Presentation Link and Description
						var presentationRichMediaURL = "";
						var presentationVodcastURL = "";
						var presentationPodcastURL = "";

						// The following logic can be improved upon with the use of JSONPath, etc..
						for(var i=0; i<responseText.link.length; i++) {
							var title = JSON.stringify(responseText.link[i]["@attributes"]['title']);

							if(JSON.stringify(responseText.link[i]["@attributes"]['title']).indexOf('podcast') != -1) {
								presentationPodcastURL = JSON.stringify(responseText.link[i]["@attributes"]['href']).replace(/\"/g, '');
							} else if(JSON.stringify(responseText.link[i]["@attributes"]['title']).indexOf('vodcast') != -1) {
								presentationVodcastURL = JSON.stringify(responseText.link[i]["@attributes"]['href']).replace(/\"/g, '');
							} else if(JSON.stringify(responseText.link[i]["@attributes"]['title']).indexOf('rich-media') != -1) {
								presentationRichMediaURL = JSON.stringify(responseText.link[i]["@attributes"]['href']).replace(/\"/g, '');
							}
						}

						if(presentationRichMediaURL != '') {			// Use Rich Media, if available
							presentationURL = presentationRichMediaURL;
						} else if(presentationVodcastURL != '') {		// Use Vodcast, if Rich Media product-group is not available  
							presentationURL = presentationVodcastURL;
						} else if(presentationPodcastURL != '') {		// Use Audio MP3, if other higher product-groups are not available
							presentationURL = presentationPodcastURL;
						}

						document.getElementById('id_name').value = document.getElementById(e.target.get('id')).innerHTML;		// Update Echo360 Link Name field but allow user to customise it if required
						document.getElementById('externalecholink').value = presentationURL;						// Update Echo360 Link URL field -- read-only, cannot be customised

						if(document.getElementById('previousecholink').value != null && document.getElementById('previousecholink').value != "") {
							document.getElementById(document.getElementById('previousecholink').value).removeAttribute("style");	// Remove highlight for previous Echo360 Link URL clicked
						}

						document.getElementById('previousecholink').value = e.target.get('id');						// Store the previous Echo360 Link URL - to track the highlight changes from clicks
						document.getElementById(e.target.get('id')).style.color="red";
                	                 },  
                        	failure: function(o) {
	                                        console.log("Failure: " + o.toSource());
                                	 }, 
	                }; 

			if (typeof YAHOO != "undefined") {
				YAHOO.util.Connect.asyncRequest('GET', echolinkEventURL, callbackHandler);	// Yahoo YUI AJAX call for Moodle 2.2
			} else {
				Y.YUI2.util.Connect.asyncRequest('GET', echolinkEventURL, callbackHandler);	// Yahoo YUI2 AJAX call for Moodle 2.3+
			}
		}

		return;
	}// end of essECPPresentationEventHandler function

	addEventHandlers();
};
