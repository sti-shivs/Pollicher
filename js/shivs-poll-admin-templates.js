jQuery(document).ready(function(jQuery) {
	jQuery('#shivs-poll-edit-add-new-template-form-save, #shivs-poll-edit-add-new-template-form-save1').click( function() {
		jQuery.ajax({
			type: 'POST', 
			url: shivs_poll_add_new_template_config.ajax.url,
			data: 'action='+shivs_poll_add_new_template_config.ajax.action+'&'+jQuery( "#shivs-poll-edit-add-new-template-form" ).serialize(),
			cache: false,
			beforeSend: function() {
				jQuery('html, body').animate({scrollTop: '0px'}, 800);
				jQuery('#message').html('<p>' + shivs_poll_add_new_template_config.ajax.beforeSendMessage + '</p>');
				jQuery("#message").removeClass();
				jQuery('#message').addClass('updated');
				jQuery('#message').show();  								
			},
			error: function() {
				jQuery('html, body').animate({scrollTop: '0px'}, 800);
				jQuery('#message').html('<p>' + shivs_poll_add_new_template_config.ajax.errorMessage + '</p>');
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
	});

	jQuery('#shivs-poll-template-before-start-date-handler').click( function() {
		jQuery('#shivs-poll-template-before-start-date-div').children('.inside').toggle('medium');
	});		
	jQuery('#shivs-poll-template-after-end-date-handler').click( function() {
		jQuery('#shivs-poll-template-after-end-date-div').children('.inside').toggle('medium');
	});
	jQuery('#shivs-poll-template-css-handler').click( function() {
		jQuery('#shivs-poll-template-css-div').children('.inside').toggle('medium');
	});
	jQuery('#shivs-poll-template-js-handler').click( function() {
		jQuery('#shivs-poll-template-js-div').children('.inside').toggle('medium');
	});
});

function shivs_poll_reset_template() {
	//jQuery('#shivs-poll-edit-add-new-template-form-reset').click( function() {
	jQuery.ajax({
		type: 'POST', 
		url: shivs_poll_add_new_template_config.ajax.url,
		data: 'action='+shivs_poll_add_new_template_config.ajax.reset_action+'&'+jQuery( "#shivs-poll-edit-add-new-template-form" ).serialize(),
		cache: false,
		beforeSend: function() {
			jQuery('html, body').animate({scrollTop: '0px'}, 800);
			jQuery('#message').html('<p>' + shivs_poll_add_new_template_config.ajax.beforeSendMessage + '</p>');
			jQuery("#message").removeClass();
			jQuery('#message').addClass('updated');
			jQuery('#message').show();  								
		},
		error: function() {
			jQuery('html, body').animate({scrollTop: '0px'}, 800);
			jQuery('#message').html('<p>' + shivs_poll_add_new_template_config.ajax.errorMessage + '</p>');
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
			setTimeout('location.reload();', 2000 );
		}
	});
};

function shivs_poll_do_change_template_author( template_id ) {
	jQuery.ajax({
		type: 'POST',
		url: shivs_poll_add_new_template_config.ajax.url,
		data: 'action=shivs_poll_do_change_template_author'+'&'+jQuery( "#shivs-poll-change-template-author-form" ).serialize(),
		cache: false,
		beforeSend: function() {
			jQuery('#shivs-poll-change-template-author-error').html('<p>' + shivs_poll_add_new_template_config.ajax.beforeSendMessage + '</p>');
		},
		error: function() {
			jQuery('#shivs-poll-change-template-author-error').html('<p>' + shivs_poll_add_new_template_config.ajax.errorMessage + '</p>');
		},
		success:
		function( data ){
			data = shivs_poll_extractApiResponse( data );
			jQuery('#shivs-poll-change-template-author-error').html('<p>' + data + '</p>');
			jQuery('#shivs-poll-change-template-author-container-' + template_id).html( '<b>' + jQuery('#shivs-poll-template-author-select option[value='+jQuery('#shivs-poll-template-author-select').val()+']' ).text() + '</b>' );
		}
	});
}

function shivs_poll_show_change_template_author( template_id ) {
	jQuery.fn.modalBox({
		directCall: {
			source : shivs_poll_add_new_template_config.ajax.url + '?action=shivs_poll_show_change_template_author&template_id=' + template_id
		},
		disablingTheOverlayClickToClose : true
	});
	return false;
}

function shivs_poll_extractApiResponse( str ) {
	var patt	= /\[response\](.*)\[\/response\]/m;
	resp 		= str.match( patt )
	return resp[1];
}		