jQuery(document).ready(function(jQuery) {
	jQuery( "#shivs-poll-allow-other-answers-yes" ).click( function () {
		jQuery( '#shivs-poll-other-answers-label-div' ).show();
        jQuery( '#shivs-poll-other-answers-to-results-div' ).show();
		jQuery( '#shivs-poll-display-other-answers-values-div' ).show();
	});
	jQuery( "#shivs-poll-allow-other-answers-no" ).click( function () {
		jQuery( '#shivs-poll-other-answers-label-div' ).hide();
        jQuery( '#shivs-poll-other-answers-to-results-div' ).hide();
		jQuery( '#shivs-poll-display-other-answers-values-div' ).hide();
	});
	
	jQuery( "#shivs-poll-display-answers-vertical" ).click( function () {
		jQuery( '#shivs-poll-display-answers-tabulated-div' ).hide();
	});
	jQuery( "#shivs-poll-display-answers-orizontal" ).click( function () {
		jQuery( '#shivs-poll-display-answers-tabulated-div' ).hide();
	});
	jQuery( "#shivs-poll-display-answers-tabulated" ).click( function () {
		jQuery( '#shivs-poll-display-answers-tabulated-div' ).show();
	});
	
	jQuery( "#shivs-poll-display-results-vertical" ).click( function () {
		jQuery( '#shivs-poll-display-results-tabulated-div' ).hide();
	});
	jQuery( "#shivs-poll-display-results-orizontal" ).click( function () {
		jQuery( '#shivs-poll-display-results-tabulated-div' ).hide();
	});
	jQuery( "#shivs-poll-display-results-tabulated" ).click( function () {
		jQuery( '#shivs-poll-display-results-tabulated-div' ).show();
	});
	
	jQuery( "#shivs-poll-allow-multiple-answers-yes" ).click( function () {
		jQuery( '#shivs-poll-allow-multiple-answers-div' ).show();
		jQuery( '#shivs-poll-allow-multiple-answers-div1' ).show();
	});
	jQuery( "#shivs-poll-allow-multiple-answers-no" ).click( function () {
		jQuery( '#shivs-poll-allow-multiple-answers-div' ).hide();
		jQuery( '#shivs-poll-allow-multiple-answers-div1' ).hide();
	});
	jQuery( ".shivs-poll-view-results-hide-custom" ).click( function () {
		jQuery( '#shivs-poll-display-view-results-div' ).hide();
	});
	jQuery( ".shivs-poll-view-results-show-custom" ).click( function () {
		jQuery( '#shivs-poll-display-view-results-div' ).show();
	});
	jQuery( ".shivs-poll-blocking-voters-hide-interval" ).click( function () {
		jQuery( '#shivs-poll-blocking-voters-interval-div' ).hide();
	});
	jQuery( ".shivs-poll-blocking-voters-show-interval" ).click( function () {
		jQuery( '#shivs-poll-blocking-voters-interval-div' ).show();
	});
	
	jQuery( "#shivs-poll-limit-number-of-votes-per-user-no" ).click( function () {
		jQuery( '#shivs-poll-number-of-votes-per-user-div' ).hide();
	});
	jQuery( "#shivs-poll-limit-number-of-votes-per-user-yes" ).click( function () {
		jQuery( '#shivs-poll-number-of-votes-per-user-div' ).show();
	});
	
	jQuery( "#shivs-poll-view-results-link-no" ).click( function () {
		jQuery( '#shivs-poll-view-results-link-div' ).hide();
	});
	jQuery( "#shivs-poll-view-results-link-yes" ).click( function () {
		jQuery( '#shivs-poll-view-results-link-div' ).show();
	});

	jQuery( "#shivs-poll-view-back-to-vote-link-no" ).click( function () {
		jQuery( '#shivs-poll-view-back-to-vote-link-div' ).hide();
	});
	jQuery( "#shivs-poll-view-back-to-vote-link-yes" ).click( function () {
		jQuery( '#shivs-poll-view-back-to-vote-link-div' ).show();
	});

	jQuery( "#shivs-poll-view-total-votes-no" ).click( function () {
		jQuery( '#shivs-poll-view-total-votes-div' ).hide();
	});
	jQuery( "#shivs-poll-view-total-votes-yes" ).click( function () {
		jQuery( '#shivs-poll-view-total-votes-div' ).show();
	});

	jQuery( "#shivs-poll-view-total-voters-no" ).click( function () {
		jQuery( '#shivs-poll-view-total-voters-div' ).hide();
	});
	jQuery( "#shivs-poll-view-total-voters-yes" ).click( function () {
		jQuery( '#shivs-poll-view-total-voters-div' ).show();
	});

	jQuery( "#shivs-poll-use-default-loading-image-no" ).click( function () {
		jQuery( '#shivs-poll-use-default-loading-image-div' ).show();
	});
	jQuery( "#shivs-poll-use-default-loading-image-yes" ).click( function () {
		jQuery( '#shivs-poll-use-default-loading-image-div' ).hide();
	});
	
	jQuery( "#shivs-poll-redirect-after-vote-yes" ).click( function () {
		jQuery( '#shivs-poll-redirect-after-vote-url-div' ).show();
	});
	jQuery( "#shivs-poll-redirect-after-vote-no" ).click( function () {
		jQuery( '#shivs-poll-redirect-after-vote-url-div' ).hide();
	});

	jQuery( "#shivs-poll-view-poll-archive-link-no" ).click( function () {
		jQuery( '#shivs-poll-view-poll-archive-link-div' ).hide();
	});
	jQuery( "#shivs-poll-view-poll-archive-link-yes" ).click( function () {
		jQuery( '#shivs-poll-view-poll-archive-link-div' ).show();
	});
	
	jQuery( "#shivs-poll-share-after-vote-no" ).click( function () {
		jQuery( '#shivs-poll-share-after-vote-name-tr' ).hide();
		jQuery( '#shivs-poll-share-after-vote-caption-tr' ).hide();
		jQuery( '#shivs-poll-share-after-vote-description-tr' ).hide();
		jQuery( '#shivs-poll-share-after-vote-picture-tr' ).hide();
	});
	jQuery( "#shivs-poll-share-after-vote-yes" ).click( function () {
		jQuery( '#shivs-poll-share-after-vote-name-tr' ).show();
		jQuery( '#shivs-poll-share-after-vote-caption-tr' ).show();
		jQuery( '#shivs-poll-share-after-vote-description-tr' ).show();
		jQuery( '#shivs-poll-share-after-vote-picture-tr' ).show();
	});

	jQuery( "#shivs-poll-show-in-archive-no" ).click( function () {
		jQuery( '#shivs-poll-show-in-archive-div' ).hide();
	});
	jQuery( "#shivs-poll-show-in-archive-yes" ).click( function () {
		jQuery( '#shivs-poll-show-in-archive-div' ).show();
	});
	
	jQuery( "#shivs-poll-send-email-notifications-no" ).click( function () {
		jQuery( '.shivs-poll-email-notifications-div' ).hide();
	});
	jQuery( "#shivs-poll-send-email-notifications-yes" ).click( function () {
		jQuery( '.shivs-poll-email-notifications-div' ).show();
	});

	jQuery( "#shivs-poll-use-template-bar-no" ).click( function () {
		jQuery( '.shivs-poll-custom-result-bar-table' ).show();
	});
	jQuery( "#shivs-poll-use-template-bar-yes" ).click( function () {
		jQuery( '.shivs-poll-custom-result-bar-table' ).hide();
	});

	jQuery( "#shivs-poll-vote-permisions-quest-only" ).click( function () {
		jQuery( '.shivs-poll-vote-as-div' ).hide(); 
	});
	jQuery( "#shivs-poll-vote-permisions-registered-only" ).click( function () {
		jQuery( '.shivs-poll-vote-as-div' ).show();
		if ( true == jQuery( '#shivs-poll-vote-permisions-facebook-yes' ).is(':checked') )
			jQuery( '#shivs-poll-vote-permisions-facebook-div' ).show();	
		if ( true == jQuery( '#shivs-poll-vote-permisions-facebook-no' ).is(':checked') )
			jQuery( '#shivs-poll-vote-permisions-facebook-div' ).hide();

		if ( true == jQuery( '#shivs-poll-vote-permisions-wordpress-yes' ).is(':checked') )
			jQuery( '#shivs-poll-vote-permisions-wordpress-div' ).show();	
		if ( true == jQuery( '#shivs-poll-vote-permisions-wordpress-no' ).is(':checked') )
			jQuery( '#shivs-poll-vote-permisions-wordpress-div' ).hide();

		if ( true == jQuery( '#shivs-poll-vote-permisions-anonymous-yes' ).is(':checked') )
			jQuery( '#shivs-poll-vote-permisions-anonymous-div' ).show();	
		if ( true == jQuery( '#shivs-poll-vote-permisions-anonymous-no' ).is(':checked') )
			jQuery( '#shivs-poll-vote-permisions-anonymous-div' ).hide();
	});
	jQuery( "#shivs-poll-vote-permisions-guest-registered" ).click( function () {
		jQuery( '.shivs-poll-vote-as-div' ).show();
		if ( true == jQuery( '#shivs-poll-vote-permisions-facebook-yes' ).is(':checked') )
			jQuery( '#shivs-poll-vote-permisions-facebook-div' ).show();	
		if ( true == jQuery( '#shivs-poll-vote-permisions-facebook-no' ).is(':checked') )
			jQuery( '#shivs-poll-vote-permisions-facebook-div' ).hide();

		if ( true == jQuery( '#shivs-poll-vote-permisions-wordpress-yes' ).is(':checked') )
			jQuery( '#shivs-poll-vote-permisions-wordpress-div' ).show();	
		if ( true == jQuery( '#shivs-poll-vote-permisions-wordpress-no' ).is(':checked') )
			jQuery( '#shivs-poll-vote-permisions-wordpress-div' ).hide();

		if ( true == jQuery( '#shivs-poll-vote-permisions-anonymous-yes' ).is(':checked') )
			jQuery( '#shivs-poll-vote-permisions-anonymous-div' ).show();	
		if ( true == jQuery( '#shivs-poll-vote-permisions-anonymous-no' ).is(':checked') )
			jQuery( '#shivs-poll-vote-permisions-anonymous-div' ).hide();
	});

	jQuery( "#shivs-poll-vote-permisions-facebook-yes" ).click( function () {
		jQuery( '#shivs-poll-vote-permisions-facebook-div' ).show();
	});
	jQuery( "#shivs-poll-vote-permisions-facebook-no" ).click( function () {
		jQuery( '#shivs-poll-vote-permisions-facebook-div' ).hide();
	});

	jQuery( "#shivs-poll-vote-permisions-wordpress-yes" ).click( function () {
		jQuery( '#shivs-poll-vote-permisions-wordpress-div' ).show();
	});
	jQuery( "#shivs-poll-vote-permisions-wordpress-no" ).click( function () {
		jQuery( '#shivs-poll-vote-permisions-wordpress-div' ).hide();
	});

	jQuery( "#shivs-poll-vote-permisions-anonymous-yes" ).click( function () {
		jQuery( '#shivs-poll-vote-permisions-anonymous-div' ).show();
	});
	jQuery( "#shivs-poll-vote-permisions-anonymous-no" ).click( function () {
		jQuery( '#shivs-poll-vote-permisions-anonymous-div' ).hide();
	});

	jQuery( "#shivs-poll-never-expire" ).click( function () {
		if ( true == jQuery( this ).is(':checked') ) {
			jQuery( "#shivs-poll-end-date-input" ).attr("disabled", "disabled");
			jQuery( "#shivs-poll-end-date-input" ).hide();
		}
		else {
			jQuery( "#shivs-poll-end-date-input" ).removeAttr("disabled", "disabled");
			jQuery( "#shivs-poll-end-date-input" ).show();
		}
	});
	jQuery ( "#message").hide();

	var shivsPollStartDateTextBox = jQuery('#shivs-poll-start-date-input');
	var shivsPollEndDateTextBox = jQuery('#shivs-poll-end-date-input');
	var shivsPollViewResultStartDateTextBox = jQuery('#shivs-poll-view-results-start-date').datetimepicker({
		showSecond: true,
		timeFormat: 'hh:mm:ss',
		dateFormat: 'yy-mm-dd'}
	);

	shivsPollStartDateTextBox.datetimepicker({
		showSecond: true,
		timeFormat: 'hh:mm:ss',
		dateFormat: 'yy-mm-dd'
	});
	shivsPollEndDateTextBox.datetimepicker({
		showSecond: true,
		timeFormat: 'hh:mm:ss',
		dateFormat: 'yy-mm-dd'
	});

});

function shivs_poll_update_bar_style( obj, property, value ) {
	if(
		'background-color' == property ||
		'height' == property ||
		'border-color' == property ||
		'border-width' == property ||
		'border-style' == property ) {
		if( jQuery( obj ).length > 0 )
		{
			if( '' != value )
				jQuery( obj ).css( property , value );
		}
	}
}