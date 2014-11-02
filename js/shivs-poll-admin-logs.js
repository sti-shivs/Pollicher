jQuery(document).ready(function(jQuery) {
		var shivsPollLogStartDateTextBox = jQuery('#shivs-poll-logs-start-date-input');
		var shivsPollLogEndDateTextBox = jQuery('#shivs-poll-logs-end-date-input');
		
		shivsPollLogStartDateTextBox.datepicker({ 			
			dateFormat: 'yy-mm-dd'
		});
		shivsPollLogEndDateTextBox.datepicker({ 
			dateFormat: 'yy-mm-dd'
		}); 
});