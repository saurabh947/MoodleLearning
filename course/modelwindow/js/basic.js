/*
 * SimpleModal Basic Modal Dialog
 * http://simplemodal.com
 *
 * Copyright (c) 2013 Eric Martin - http://ericmmartin.com
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

jQuery(function ($) {
	// Load dialog on page load
	//$('#basic-modal-content').modal();

	// Load dialog on click
	$('#basic-modal .basic').click(function (e) {
		$('#basic-modal-content').modal();
		return false;
		
	});
/*
	$("#basic-modal-button").click(function () {
		$('#basic-modal-content').modal();
		
				$("#simplemodal-container").css("width","220px").css("height","120px");
				$("#simplemodal-container").html("<img src='loading.gif' alt='loading...' /> <br /> <br /> <strong>Please wait while the batch is being created... <br /> You cannot stop the process now.</strong>");
				
		});   */
	});