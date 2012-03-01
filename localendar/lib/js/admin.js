jQuery(document).ready(function($) {

	/**
	 * Show and hide specific elements based on user selection
	 */
	if ( $('.localendar-type-link').is(':checked') )
		$('.localendar-link-text').show();
	else
		$('.localendar-link-text').hide();
		
	if ( 'mb' == $('.localendar-styles option[selected]').val() )
		$('.localendar-hide-events').show();
	else
		$('.localendar-hide-events').hide();
		
	$(document).on('ajaxComplete', function() {
		if ( $('.localendar-type-link').is(':checked') )
			$('.localendar-link-text').show();
		else
			$('.localendar-link-text').hide();
			
		if ( 'mb' == $('.localendar-styles option[selected]').val() )
			$('.localendar-hide-events').show();
		else
			$('.localendar-hide-events').hide();
	});
	
	$(document).on('change.localendarLink', '.localendar-types input[type="radio"]', function() {		
		if ( $(this).is(':checked') && 'link' == $(this).val() )
			$('.localendar-link-text').fadeIn();
		else
			$('.localendar-link-text').fadeOut();
	});

	$(document).on('change.localendarStyle', '.localendar-styles', function() {
		if ( 'mb' == $(this).val() )
			$('.localendar-hide-events').fadeIn();
		else
			$('.localendar-hide-events').fadeOut();
	});

});