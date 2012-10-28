jQuery(document).ready(function() {
	var apply_button = jQuery('#apply_button');
	var bulk_actions = jQuery('#bulk_actions');
	var select_all = jQuery('#select_all');
	
	apply_button.click(function(e) {
		if (bulk_actions.val() == '') {	
			e.preventDefault();
		}
		if ((bulk_actions.val() == 'delete') && !confirm('Are you sure you want to delete this content?')) {
			e.preventDefault();
		}
	});
	
	select_all.click(function() {
		var checked = select_all.is(':checked');
		jQuery('input[type=checkbox]').each(function() {
			if (checked) {
				jQuery(this).attr('checked', checked);			
			}
			else {
				jQuery(this).removeAttr('checked');
			}
		});
	});
});