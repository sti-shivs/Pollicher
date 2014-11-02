jQuery(document).ready(function(jQuery) {
	jQuery( "#shivs-poll-add-answer-button" ).click( function () {
		shivs_poll_add_table_answer( jQuery( "#shivs-poll-answer-table" ), shivs_poll_count_number_of_answer_without_other ( "#shivs-poll-answer-table" ) + 1 );
		return false;
	});
	jQuery( "#shivs-poll-add-customfield-button" ).click( function () {
		shivs_poll_add_table_customfield( jQuery( "#shivs-poll-customfields-table" ), shivs_poll_count_number_of_customfields ( "#shivs-poll-customfields-table" ) + 1 );
		return false;
	});

    jQuery( "#shivs-poll-allow-other-answers-yes" ).click( function () {
		jQuery( '#shivs-poll-other-answers-label-div' ).show();
		jQuery( '#shivs-poll-other-answers-to-results-div' ).show();
		jQuery( '#shivs-poll-display-other-answers-values-div' ).show();
		jQuery( '#shivs-poll-is-default-other-answers-values-div' ).show();
	});
	jQuery( "#shivs-poll-allow-other-answers-no" ).click( function () {
		jQuery( '#shivs-poll-other-answers-label-div' ).hide();
        jQuery( '#shivs-poll-other-answers-to-results-div' ).hide();
		jQuery( '#shivs-poll-display-other-answers-values-div' ).hide();
		jQuery( '#shivs-poll-is-default-other-answers-values-div' ).hide();
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
	
	jQuery( "#shivs-poll-schedule-reset-poll-stats-no" ).click( function () {
		jQuery( '.shivs-poll-schedule-reset-poll-stats-options-div' ).hide();
	});
	jQuery( "#shivs-poll-schedule-reset-poll-stats-yes" ).click( function () {
		jQuery( '.shivs-poll-schedule-reset-poll-stats-options-div' ).show();
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

	jQuery( "#shivs-poll-view-total-answers-no" ).click( function () {
		jQuery( '#shivs-poll-view-total-answers-div' ).hide();
	});
	jQuery( "#shivs-poll-view-total-answers-yes" ).click( function () {
		jQuery( '#shivs-poll-view-total-answers-div' ).show();
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

	jQuery( "#shivs-poll-answers-advanced-options-button" ).click( function () {
		jQuery( '#shivs-poll-answers-advanced-options-div' ).toggle( 'medium' );
		return false;
	});

	jQuery( "#shivs-poll-customfield-advanced-options-button" ).click( function () {
		jQuery( '#shivs-poll-custom-fields-advanced-options-div' ).toggle( 'medium' );
		return false;
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
	
	var shivsPollResetPollDateTextBox	= jQuery('#shivs-poll-schedule-reset-poll-stats-date').datetimepicker({
		showSecond: false,
		showMinute: false,
		showHour: true,
		timeFormat: 'hh:00:00',
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

	jQuery('#shivs-poll-edit-add-new-form-submit').click( function () {
		savePoll();
	});                                         
	jQuery('#shivs-poll-edit-add-new-form-submit1').click( function () {
		savePoll();
	});

	function savePoll() {
        var x = {
            'action' : shivs_poll_add_new_config.ajax.action
        };
        var toSend = jQuery.param(x) + "&" + jQuery('#shivs-poll-edit-add-new-form' ).serialize();
		jQuery.ajax({
			type: 'POST',
			url: shivs_poll_add_new_config.ajax.url,
			data: toSend,
			cache: false,
			beforeSend: function() {
				jQuery('html, body').animate({scrollTop: '0px'}, 800);
				jQuery('#message').html('<p>' + shivs_poll_add_new_config.ajax.beforeSendMessage + '</p>');
				jQuery("#message").removeClass();
				jQuery('#message').addClass('updated');
				jQuery('#message').show();
			},
			error: function() {
				jQuery('html, body').animate({scrollTop: '0px'}, 800);
				jQuery('#message').html('<p>' + shivs_poll_add_new_config.ajax.errorMessage + '</p>');
				jQuery("#message").removeClass();
				jQuery('#message').addClass('error');
				jQuery('#message').show();
			},
			success:
			function( data ){
				jQuery('html, body').animate({scrollTop: '0px'}, 800);
				jQuery('#message').html('<p>' + data + '</p>');
				jQuery("#message").removeClass();
				jQuery('#message').addClass('updated');
				jQuery('#message').show();
			}
		});
	}

});
function shivs_poll_add_table_answer( table, ans_no ) {
	var answer_id = shivs_poll_add_new_config.default_number_of_answers;
	var bar_border_style_solid_checked	= '';
	if ( 'solid' == shivs_poll_add_new_config.poll_bar_default_options.border)
		bar_border_style_solid_checked	= 'selected="selected"';

	var bar_border_style_dashed_checked	= '';
	if ( 'dashed' == shivs_poll_add_new_config.poll_bar_default_options.border)
		bar_border_style_dashed_checked	= 'selected="selected"';

	var bar_border_style_dotted_checked	= '';
	if ( 'dotted' == shivs_poll_add_new_config.poll_bar_default_options.border)
		bar_border_style_dotted_checked	= 'selected="selected"';

	var jQuerytr = '<tr class="shivs_poll_tr_answer" id="shivs_poll_tr_answer' + answer_id + '"><th scope="row"><label class="shivs_poll_answer_label" for="shivs-poll-answer' + answer_id + '">' + shivs_poll_add_new_config.text_answer + ' ' + ans_no + '</label></th><td><input type="text" value="" id="shivs-poll-answer'+ answer_id +'" name="shivs_poll_answer[answer'+ answer_id +']" /></td><td align="right"><input type="button" value="' + shivs_poll_add_new_config.text_customize_answer + '" onclick="shivs_poll_toogle_customize_answer(\'#shivs-poll-answer-table\', ' + answer_id + ' ); return false;" class="button" /> <input onclick="shivs_poll_remove_answer( \'#shivs-poll-answer-table\', ' + answer_id + ' ); return false;" type="button" value="' + shivs_poll_add_new_config.text_remove_answer + '" class="button" /></td></tr>';
	jQuerytr += '<tr class="shivs_poll_tr_customize_answer" id="shivs_poll_tr_customize_answer' + answer_id +'" style="display:none;">' +
	'<td colspan="3">' +
	
	'<table cellspacing="0" width="100%"><tbody>' +
	'<tr>' +
	'<th>' + shivs_poll_add_new_config.text_is_default_answer +
	'</th>' +
	'<td>' +
	'<input checked="checked" id="shivs-poll-is-default-answer-no-' + answer_id + '" ';
	jQuerytr += ' type="radio" name="shivs_poll_answer_options[answer' + answer_id + '][is_default_answer]" value="no" /> <label for="shivs-poll-is-default-answer-no-' + answer_id + '">' + shivs_poll_add_new_config.text_poll_bar_style.use_template_bar_no_label + '</label>&nbsp;|&nbsp;' +
	'<input id="shivs-poll-is-default-answer-yes-' + answer_id + '" ';
	jQuerytr += 'type="radio" name="shivs_poll_answer_options[answer' + answer_id + '][is_default_answer]" value="yes" /> <label for="shivs-poll-is-default-answer-yes-' + answer_id + '">' + shivs_poll_add_new_config.text_poll_bar_style.use_template_bar_yes_label + '</label>' +
	'</td>' +
	'</tr>' +
	'</tbody>' +
	'</table>' +
	
	'<table cellspacing="0" width="100%"><tbody>' +
	'<tr>' +
	'<th>' + shivs_poll_add_new_config.text_poll_bar_style.use_template_bar_label +
	'</th>' +
	'<td>' +
	'<input onclick="jQuery(\'#shivs-poll-answer-use-template-bar-table-' + answer_id + '\').show();" id="shivs-poll-answer-use-template-bar-no-' + answer_id + '" ';
	if ( 'no' == shivs_poll_add_new_config.poll_bar_default_options.use_template_bar )
		jQuerytr += 'checked="checked"';
	jQuerytr += ' type="radio" name="shivs_poll_answer_options[answer' + answer_id + '][use_template_bar]" value="no" /> <label for="shivs-poll-answer-use-template-bar-no-' + answer_id + '">' + shivs_poll_add_new_config.text_poll_bar_style.use_template_bar_no_label + '</label>&nbsp;|&nbsp;' +
	'<input onclick="jQuery(\'#shivs-poll-answer-use-template-bar-table-' + answer_id + '\').hide();" id="shivs-poll-answer-use-template-bar-yes-' + answer_id + '" ';
	if ( 'yes' == shivs_poll_add_new_config.poll_bar_default_options.use_template_bar )
		jQuerytr += 'checked="checked"';
	jQuerytr += 'type="radio" name="shivs_poll_answer_options[answer' + answer_id + '][use_template_bar]" value="yes" /> <label for="shivs-poll-answer-use-template-bar-yes-' + answer_id + '">' + shivs_poll_add_new_config.text_poll_bar_style.use_template_bar_yes_label + '</label>' +
	'</td>' +
	'</tr>' +
	'</tbody>' +
	'</table>' +
	'<table cellspacing="0" width="100%" id="shivs-poll-answer-use-template-bar-table-' + answer_id + '" style="';
	if ( 'yes' == shivs_poll_add_new_config.poll_bar_default_options.use_template_bar )
		jQuerytr += 'display:none';
	jQuerytr += '">' +
	'<tbody>' +
	'<tr>' +
	'<th>' +
	'<label>' + shivs_poll_add_new_config.text_poll_bar_style.poll_bar_style_label + '</label>' +
	'</th>' +
	'<td>' +
	'<table cellspacing="0" style="margin-left:0px;" style="width:100%"><tbody>' +
	'<tr>' +
	'<th>' +
	'<label for="shivs-poll-answer-option-bar-background-answer' + answer_id + '">' + shivs_poll_add_new_config.text_poll_bar_style.poll_bar_style_background_label + '</label>' +
	'</th>' +
	'<td>' +
	'#<input id="shivs-poll-answer-option-bar-background-answer' + answer_id + '" value="' + shivs_poll_add_new_config.poll_bar_default_options.background_color + '" onblur="shivs_poll_update_bar_style(\'#shivs-poll-bar-preview' + answer_id + '\', \'background-color\', \'#\'+this.value)" type="text" name="shivs_poll_answer_options[answer' + answer_id + '][bar_background]" />' +
	'</td>' +
	'</tr>' +
	'<tr>' +
	'<th>' +
	'<label for="shivs-poll-answer-option-bar-height-answer' + answer_id + '">' + shivs_poll_add_new_config.text_poll_bar_style.poll_bar_style_height_label + '</label>' +
	'</th>' +
	'<td>' +
	'<input id="shivs-poll-answer-option-bar-height-answer' + answer_id + '" value="' + shivs_poll_add_new_config.poll_bar_default_options.height + '" onblur="shivs_poll_update_bar_style(\'#shivs-poll-bar-preview' + answer_id + '\', \'height\', this.value+\'px\')" type="text" name="shivs_poll_answer_options[answer' + answer_id + '][bar_height]" /> px' +
	'</td>' +
	'</tr>' +
	'<tr>' +
	'<th>' +
	'<label for="shivs-poll-answer-option-bar-border-color-answer' + answer_id + '">' + shivs_poll_add_new_config.text_poll_bar_style.poll_bar_style_border_color_label + '</label>' +
	'</th>' +
	'<td>' +
	'#<input id="shivs-poll-answer-option-bar-border-color-answer' + answer_id + '" value="' + shivs_poll_add_new_config.poll_bar_default_options.border_color + '" onblur="shivs_poll_update_bar_style(\'#shivs-poll-bar-preview' + answer_id + '\', \'border-color\', \'#\'+this.value)" type="text" name="shivs_poll_answer_options[answer' + answer_id + '][bar_border_color]" />' +
	'</td>' +
	'</tr>' +
	'<tr>' +
	'<th>' +
	'<label for="shivs-poll-answer-option-bar-border-width-answer' + answer_id + '">' + shivs_poll_add_new_config.text_poll_bar_style.poll_bar_style_border_width_label + '</label>' +
	'</th>' +
	'<td>' +
	'<input id="shivs-poll-answer-option-bar-border-width-answer' + answer_id + '" value="' + shivs_poll_add_new_config.poll_bar_default_options.border_width + '" onblur="shivs_poll_update_bar_style(\'#shivs-poll-bar-preview' + answer_id + '\', \'border-width\', this.value+\'px\')" type="text" name="shivs_poll_answer_options[answer' + answer_id + '][bar_border_width]" /> px' +
	'</td>' +
	'</tr>' +
	'<tr>' +
	'<th>' +
	'<label for="shivs-poll-answer-option-bar-border-style-answer' + answer_id + '">' + shivs_poll_add_new_config.text_poll_bar_style.poll_bar_style_border_style_label + '</label>' +
	'</th>' +
	'<td>' +
	'<select id="shivs-poll-answer-option-bar-border-style-answer' + answer_id + '" onchange="shivs_poll_update_bar_style(\'#shivs-poll-bar-preview' + answer_id + '\', \'border-style\', this.value)" name="shivs_poll_answer_options[answer' + answer_id + '][bar_border_style]">' +
	'<option ' + bar_border_style_solid_checked + ' value="solid">Solid</option>' +
	'<option ' + bar_border_style_dashed_checked + ' value="dashed">Dashed</option>' +
	'<option ' + bar_border_style_dotted_checked + ' value="dotted">Dotted</option>' +
	'</select>' +
	'</td>' +
	'</tr>' +
	'</tbody></table>' +
	'</td>' +
	'</tr>' +
	'<tr>' +
	'<th>' +
	'<label>' + shivs_poll_add_new_config.text_poll_bar_style.poll_bar_preview_label + '</label>' +
	'</th>' +
	'<td>' +
	'<div id="shivs-poll-bar-preview' + answer_id + '" style="width: 100px; height: ' + shivs_poll_add_new_config.poll_bar_default_options.height + 'px; background-color: #' + shivs_poll_add_new_config.poll_bar_default_options.background_color +'; border-style: '+ shivs_poll_add_new_config.poll_bar_default_options.border + '; border-width: ' + shivs_poll_add_new_config.poll_bar_default_options.border_width + 'px; border-color: #' + shivs_poll_add_new_config.poll_bar_default_options.border_color + ';"></div>' +
	'</td>' +
	'</tr>' +
	'</tbody>' +
	'</table>' +
	'</td>' +
	'</tr>';
	if ( 1 == ans_no ) {
		jQuery( table ).children( 'tbody' ).html( jQuerytr );
	}
	else {
		jQuery( table ).children( 'tbody' ).children( 'tr:last' ).after( jQuerytr );
	}
	jQuery( '#shivs_poll_tr_answer' + shivs_poll_add_new_config.default_number_of_answers ).hide().fadeIn( 'medium' );
	shivs_poll_add_new_config.default_number_of_answers++;
	shivs_poll_reorder_answer( table );
};

function shivs_poll_add_table_customfield( table, custfield_no ) {
	var jQuerytr = '<tr class="shivs_poll_tr_customfields" id="shivs_poll_tr_customfield' + shivs_poll_add_new_config.default_number_of_customfields + '"><th scope="row"><label class="shivs_poll_customfield_label" for="shivs_poll_customfield' + shivs_poll_add_new_config.default_number_of_customfields + '">' + shivs_poll_add_new_config.text_customfield + ' ' + custfield_no + '</label></th><td><input type="text" value="" id="shivs-poll-customfield' + shivs_poll_add_new_config.default_number_of_customfields + '" name="shivs_poll_customfield[customfield' + shivs_poll_add_new_config.default_number_of_customfields +']" /> <input value="yes" id="shivs-poll-customfield-required-' + shivs_poll_add_new_config.default_number_of_customfields + '" type="checkbox" name="shivs_poll_customfield_required[customfield' + shivs_poll_add_new_config.default_number_of_customfields +']" /> <label for="shivs-poll-customfield-required-' + shivs_poll_add_new_config.default_number_of_customfields + '">' + shivs_poll_add_new_config.text_requiered_customfield + '</label></td><td align="right"><input onclick="shivs_poll_remove_customfield( \'#shivs-poll-customfields-table\', ' + shivs_poll_add_new_config.default_number_of_customfields + ' ); return false;" type="button" value="' + shivs_poll_add_new_config.text_remove_customfield + '" class="button" /></td></tr>';
	if ( 1 == custfield_no ) {
		jQuery( table ).children( 'tbody' ).html( jQuerytr );
	}
	else {
		jQuery( table ).children( 'tbody' ).children( 'tr:last' ).after( jQuerytr );
	}
	jQuery( '#shivs_poll_tr_customfield' + shivs_poll_add_new_config.default_number_of_customfields ).hide().fadeIn( 'medium' );
	shivs_poll_add_new_config.default_number_of_customfields++;
	shivs_poll_reorder_customfields( table );
};

function shivs_poll_count_number_of_answer ( table ) {
	var jQuerycount = 0;
	jQuerycount = jQuery( table ).find( "tbody .shivs_poll_tr_answer" ).length;
	if ( jQuery( '#shivs-poll-allow-other-answers-yes' ).attr('checked') == 'checked' )
		jQuerycount = jQuerycount + 1;
	return jQuerycount;
}

function shivs_poll_count_number_of_answer_without_other ( table ) {
	var jQuerycount = 0;
	jQuerycount = jQuery( table ).find( "tbody .shivs_poll_tr_answer" ).length;
	return jQuerycount;
}

function shivs_poll_count_number_of_customfields ( table ) {
	var jQuerycount = jQuery( table ).find( "tbody .shivs_poll_tr_customfields" ).length;
	return jQuerycount;
}

function shivs_poll_reorder_answer( table ) {
	jQuerytr = jQuery( table ).find( "tbody .shivs_poll_tr_answer" );
	jQuerytr.each( function ( index, value ) {
		jQuery( this ).find(".shivs_poll_answer_label").html( shivs_poll_add_new_config.text_answer + ' ' + parseInt(index + 1) ) ;
	});
	return false;
}

function shivs_poll_reorder_customfields( table ) {
	jQuerytr = jQuery( table ).find( "tbody .shivs_poll_tr_customfields" );
	jQuerytr.each( function ( index, value ) {
		jQuery( this ).find(".shivs_poll_customfield_label").html( shivs_poll_add_new_config.text_customfield + ' ' + parseInt(index + 1) ) ;
	});
	return false;
}

function shivs_poll_remove_answer( table, answer_id ) {
	if ( shivs_poll_count_number_of_answer ( table ) >= 2 ) {
		jQuery( '#shivs_poll_tr_answer' + answer_id ).fadeOut( 'medium', function () {
			jQuery( this ).remove();
			shivs_poll_reorder_answer( table );
			return false;
		});
		jQuery( '#shivs_poll_tr_customize_answer' + answer_id ).fadeOut( 'medium', function () {
			jQuery( this ).remove();
			return false;
		});
	}
	return false;
}

function shivs_poll_remove_customfield( table, customfield_id ) {
	jQuery( '#shivs_poll_tr_customfield' + customfield_id ).fadeOut( 'medium', function () {
		jQuery( this ).remove();
		shivs_poll_reorder_customfields( table );
		return false;
	});
	return false;
}

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

function shivs_poll_toogle_customize_answer( table, answer_id ) {
	jQuery( '#shivs_poll_tr_customize_answer' + answer_id ).toggle( 'medium' );
	return false;
}

function shivs_poll_show_change_votes_number_answer( answer_id ) {
	jQuery.fn.modalBox({
		directCall: {
			source : shivs_poll_add_new_config.ajax.url + '?action=shivs_poll_show_change_votes_number_answer&answer_id=' + answer_id
		},
		disablingTheOverlayClickToClose : true
	});
	return false;
}

function shivs_poll_show_change_total_number_poll( poll_id, type ) {
	jQuery.fn.modalBox({
		directCall: {
			source : shivs_poll_add_new_config.ajax.url + '?action=shivs_poll_show_change_total_number_poll&poll_id=' + poll_id + '&type=' + type
		},
		disablingTheOverlayClickToClose : true
	});
	return false;
}

function shivs_poll_show_change_poll_author( poll_id ) {
	jQuery.fn.modalBox({
		directCall: {
			source : shivs_poll_add_new_config.ajax.url + '?action=shivs_poll_show_change_poll_author&poll_id=' + poll_id
		},
		disablingTheOverlayClickToClose : true
	});
	return false;
}

function shivs_poll_do_change_votes_number_answer( answer_id ) {
	jQuery.ajax({
		type: 'POST',
		url: shivs_poll_add_new_config.ajax.url,
		data: 'action=shivs_poll_do_change_votes_number_answer'+'&'+jQuery( "#shivs-poll-change-answer-no-votes-form" ).serialize(),
		cache: false,
		beforeSend: function() {
			jQuery('#shivs-poll-change-no-votes-error').html('<p>' + shivs_poll_add_new_config.ajax.beforeSendMessage + '</p>');
		},
		error: function() {
			jQuery('#shivs-poll-change-no-votes-error').html('<p>' + shivs_poll_add_new_config.ajax.errorMessage + '</p>');
		},
		success:
		function( data ){
			data = shivs_poll_extractApiResponse( data );
			jQuery('#shivs-poll-change-no-votes-error').html('<p>' + data + '</p>');
			if ( ! jQuery('#shivs-poll-update-answers-with-logs').prop('checked' ) ) {
				jQuery('#shivs-poll-change-no-votes-button-' + answer_id).val( shivs_poll_add_new_config.text_change_votes_number_answer + ' (' + jQuery('#shivs-poll-answer-no-votes' ).val() + ')' );
				if ( jQuery('#shivs-poll-change-to-all-poll-answers').prop('checked') )
					jQuery('.shivs-poll-change-no-votes-buttons').val( shivs_poll_add_new_config.text_change_votes_number_answer + ' (' + jQuery('#shivs-poll-answer-no-votes' ).val() + ')' );
			}
			if ( jQuery('#shivs-poll-update-answers-with-logs').prop('checked') )
				setTimeout('location.reload();', 100 );
		}
	});
}

function shivs_poll_do_change_poll_author( poll_id ) {
	jQuery.ajax({
		type: 'POST',
		url: shivs_poll_add_new_config.ajax.url,
		data: 'action=shivs_poll_do_change_poll_author'+'&'+jQuery( "#shivs-poll-change-poll-author-form" ).serialize(),
		cache: false,
		beforeSend: function() {
			jQuery('#shivs-poll-change-poll-author-error').html('<p>' + shivs_poll_add_new_config.ajax.beforeSendMessage + '</p>');
		},
		error: function() {
			jQuery('#shivs-poll-change-poll-author-error').html('<p>' + shivs_poll_add_new_config.ajax.errorMessage + '</p>');
		},
		success:
		function( data ){
			data = shivs_poll_extractApiResponse( data );
			jQuery('#shivs-poll-change-poll-author-error').html('<p>' + data + '</p>');
			jQuery('#shivs-poll-change-poll-author-container-' + poll_id).html( '<b>' + jQuery('#shivs-poll-author-select option[value='+jQuery('#shivs-poll-author-select').val()+']' ).text() + '</b>' );
		}
	});
}

function shivs_poll_do_change_total_number_poll( poll_id, type ) {
	jQuery.ajax({
		type: 'POST',
		url: shivs_poll_add_new_config.ajax.url,
		data: 'action=shivs_poll_do_change_total_number_poll'+'&'+jQuery( "#shivs-poll-change-poll-total-no-form" ).serialize(),
		cache: false,
		beforeSend: function() {
			jQuery('#shivs-poll-change-total-no-error').html('<p>' + shivs_poll_add_new_config.ajax.beforeSendMessage + '</p>');
		},
		error: function() {
			jQuery('#shivs-poll-change-total-no-error').html('<p>' + shivs_poll_add_new_config.ajax.errorMessage + '</p>');
		},
		success:
		function( data ){
			data = shivs_poll_extractApiResponse( data );
			jQuery('#shivs-poll-change-total-no-error').html('<p>' + data + '</p>');
			if ( ! jQuery('#shivs-poll-update-poll-with-logs').prop('checked') && ! jQuery('#shivs-poll-update-poll-with-answers').prop('checked' ) ) {
				if ( 'votes' == type )
					jQuery('#shivs-poll-change-no-votes-poll-container-' + poll_id ).html( '<b>' +jQuery('#shivs-poll-total-votes' ).val() + '</b>' );
				if ('answers' == type )
					jQuery('#shivs-poll-change-no-answers-poll-container-' + poll_id ).html( '<b>' +jQuery('#shivs-poll-total-answers' ).val() + '</b>' );
			}
			if ( jQuery('#shivs-poll-update-poll-with-logs').prop('checked') )
				setTimeout('location.reload();', 100 );
			if ( jQuery('#shivs-poll-update-poll-with-answers').prop('checked') )
				setTimeout('location.reload();', 100 );
		}
	});
}

function shivs_poll_extractApiResponse( str ) {
	var patt	= /\[response\](.*)\[\/response\]/m;
	resp 		= str.match( patt )
	return resp[1];
}

function shivs_poll_return_template_preview( template_id, destination, location) {
	dest = jQuery(destination);
	if( '' == template_id )	{
		dest.html('');
	}
	else {
		var t_data = {
			action : 'shivs_poll_preview_template',
			template_id: template_id,
			loc: location
		}
		jQuery.ajax({
			type: 'POST',
			url: shivs_poll_add_new_config.ajax.url,
			data: t_data,
			beforeSend: function() {
				dest.html('<p>' + shivs_poll_add_new_config.ajax.beforeSendMessage + '</p>');
			},
			error: function() {
				dest.html('<p>' + shivs_poll_add_new_config.ajax.errorMessage + '</p>');
			},
			success: function( data ) {
				dest.html(data);
			}
		});
	}
}