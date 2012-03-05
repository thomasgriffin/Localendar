jQuery(document).ready(function($) {
    
	/**
	 * Show and hide specific elements based on user selection.
	 */
	if ( $('.localendar-type-link').is(':checked') )
		$('.localendar-link-text').show();
	else
		$('.localendar-link-text').hide();
		
	if ( 'mb' == $('.localendar-styles option[selected]').val() )
		$('.localendar-hide-events').show();
	else
		$('.localendar-hide-events').hide();

	if ( $('.localendar-type-mini').is(':checked') ) {
		$('.select-style').hide();
		$('.localendar-styles').hide();
		$('.localendar-hide-events').hide();
	} else {
		$('.select-style').show();
		$('.localendar-styles').show();
		$('.localendar-hide-events').show();
	}
	
	$(document).on('ajaxComplete', function() {    		
		if ( $('.localendar-type-link').is(':checked') )
			$('.localendar-link-text').show();
		else
			$('.localendar-link-text').hide();
			
		if ( 'mb' == $('.localendar-styles option[selected]').val() )
			$('.localendar-hide-events').show();
		else
			$('.localendar-hide-events').hide();

		if ( $('.localendar-type-mini').is(':checked') ) {
			$('.select-style').hide();
			$('.localendar-styles').hide();
			$('.localendar-hide-events').hide();
		} else if ( $(this).is(':checked') && 'mb' == $('.localendar-styles option[selected]').val() ) {
			$('.select-style').show();
			$('.localendar-styles').show();
			$('.localendar-hide-events').show();
		} else {
			$('.select-style').show();
			$('.localendar-styles').show();
		}
	});
	
	$(document).on('change.localendarLink', '.localendar-types input[type="radio"]', function() {		
		if ( $(this).is(':checked') && 'link' == $(this).val() && 'mb' == $('.localendar-styles option[selected]').val() ) {
			$('.localendar-link-text').fadeIn();
			$('.select-style').fadeIn();
			$('.localendar-styles').fadeIn();
			$('.localendar-hide-events').fadeIn();
		} else if ( $(this).is(':checked') && 'link' == $(this).val() && 'mb' !== $('.localendar-styles option[selected]').val() ) {
			$('.localendar-link-text').fadeIn();
			$('.select-style').fadeIn();
			$('.localendar-styles').fadeIn();
		} else if ( $(this).is(':checked') && 'mini' == $(this).val() ) {
			$('.localendar-link-text').fadeOut();
			$('.select-style').fadeOut();
			$('.localendar-styles').fadeOut();
			$('.localendar-hide-events').fadeOut();
		} else if ( $(this).is(':checked') && 'mb' == $('.localendar-styles option[selected]').val() ) {
			$('.localendar-link-text').fadeOut();
			$('.select-style').fadeIn();
			$('.localendar-styles').fadeIn();
			$('.localendar-hide-events').fadeIn();
		} else {
			$('.localendar-link-text').fadeOut();
			$('.select-style').fadeIn();
			$('.localendar-styles').fadeIn();
		}
	});

	$(document).on('change.localendarStyle', '.localendar-styles', function() {
		if ( 'mb' == $(this).val() )
			$('.localendar-hide-events').fadeIn();
		else
			$('.localendar-hide-events').fadeOut();
	});

});