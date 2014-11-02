jQuery(document).ready(function(jQuery) {
		var shivsPollCustomFieldStartDateTextBox = jQuery('#shivs-poll-custom-field-start-date-input');
		var shivsPollCustomFieldEndDateTextBox = jQuery('#shivs-poll-custom-field-end-date-input');
		
		shivsPollCustomFieldStartDateTextBox.datepicker({ 			
			dateFormat: 'yy-mm-dd'
		});
		shivsPollCustomFieldEndDateTextBox.datepicker({ 
			dateFormat: 'yy-mm-dd'
		}); 
});