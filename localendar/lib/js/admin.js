jQuery(document).ready(function($){
 	var select_val = $('.localendar-styles option[selected]').val();
 	 	
 	/** Show and hide specific elements based on user selection */
	if ( $('.localendar-type-link').is(':checked') )
		$('.localendar-link-text').show();
	else
		$('.localendar-link-text').hide();
		
	if ( $('.localendar-type-full').is(':checked') || $('.localendar-type-static').is(':checked') ) {
		$('.localendar-link-text').hide();
		$('.localendar-iframe-style').hide();
	}
						
	if ( $('.localendar-type-iframe').is(':checked') )
		$('.localendar-iframe-style').show();
	else
		$('.localendar-iframe-style').hide();
	
	if ( $('.localendar-type-mini').is(':checked') ) {
		$('.localendar-iframe-style').hide();
		$('.localendar-link-text').hide();
		$('.select-style').hide();
		$('.localendar-styles').hide();
		$('.localendar-hide-events').hide();
	}

	if ( 'mb' == select_val )
		$('.localendar-hide-events').show();
	else
		$('.localendar-hide-events').hide();
		
	$(document).on('ajaxStop', function() {
		/** Show and hide specific elements based on user selection */
		if ( $('.localendar-type-link').is(':checked') )
			$('.localendar-link-text').show();
		else
			$('.localendar-link-text').hide();
		
		if ( $('.localendar-type-full').is(':checked') || $('.localendar-type-static').is(':checked') ) {
			$('.localendar-link-text').hide();
			$('.localendar-iframe-style').hide();
		}
						
		if ( $('.localendar-type-iframe').is(':checked') )
			$('.localendar-iframe-style').show();
		else
			$('.localendar-iframe-style').hide();
	
		if ( $('.localendar-type-mini').is(':checked') ) {
			$('.localendar-iframe-style').hide();
			$('.localendar-link-text').hide();
			$('.select-style').hide();
			$('.localendar-styles').hide();
			$('.localendar-hide-events').hide();
		}

		if ( 'mb' == select_val )
			$('.localendar-hide-events').show();
		else
			$('.localendar-hide-events').hide();
	});
					
	$(document).on('change.localendarLink', '.localendar-types input[type="radio"]', function() {
		/** Do conditional logic for the link selection */	
		if ( 'link' == $(this).val() && 'mb' == select_val ) {
			$('.localendar-iframe-style').hide();
			$('.localendar-link-text').show();
			$('.select-style').show();
			$('.localendar-styles').show();
			$('.localendar-hide-events').show();
		} else if ( 'link' == $(this).val() && 'mb' !== select_val ) {
			$('.localendar-hide-events').hide();
			$('.localendar-iframe-style').hide();
			$('.localendar-link-text').show();
			$('.select-style').show();
			$('.localendar-styles').show();
		}
						
		/** Do conditional logic for the full and static selections */
		if ( 'full' == $(this).val() && 'mb' == select_val || 'static' == $(this).val() && 'mb' == select_val ) {
			$('.localendar-iframe-style').hide();
			$('.localendar-link-text').hide();
			$('.select-style').show();
			$('.localendar-styles').show();
			$('.localendar-hide-events').show();
		} else if ( 'full' == $(this).val() && 'mb' !== select_val || 'static' == $(this).val() && 'mb' !== select_val ) {
			$('.localendar-hide-events').hide();
			$('.localendar-iframe-style').hide();
			$('.localendar-link-text').hide();
			$('.select-style').show();
			$('.localendar-styles').show();
		}
						
		/** Do conditional logic for the iframe selection */
		if ( 'iframe' == $(this).val() && 'mb' == select_val ) {
			$('.localendar-iframe-style').show();
			$('.localendar-link-text').hide();
			$('.select-style').show();
			$('.localendar-styles').show();
			$('.localendar-hide-events').show();
		} else if ( 'iframe' == $(this).val() && 'mb' !== select_val ) {
			$('.localendar-iframe-style').show();
			$('.localendar-hide-events').hide();
			$('.localendar-link-text').hide();
			$('.select-style').show();
			$('.localendar-styles').show();
		}
						
		/** Do conditional logic for the mini selection */
		if ( 'mini' == $(this).val() ) {
			$('.localendar-iframe-style').hide();
			$('.localendar-link-text').hide();
			$('.select-style').hide();
			$('.localendar-styles').hide();
			$('.localendar-hide-events').hide();
		}
	});

	$(document).on('change.localendarStyle', '.localendar-styles', function() {
		if ( 'mb' == $(this).val() )
			$('.localendar-hide-events').show();
		else
			$('.localendar-hide-events').hide();
	});
});