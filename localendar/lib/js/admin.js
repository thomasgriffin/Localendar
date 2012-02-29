jQuery(document).ready(function($) {

	/**
	 * Show and hide specific elements based on user selection
	 */
	if ( $('.localendar-type-link').is(':checked') )
		$('.localendar-link-text').show();
		
	$(document).on('change.localendarLink', '.localendar-types input[type="radio"]', function() {
		if ( $(this).is(':checked') && 'link' == $(this).val() )
			$('.localendar-link-text').fadeIn();
		else
			$('.localendar-link-text').fadeOut();
	});

});