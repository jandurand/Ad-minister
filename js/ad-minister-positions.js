jQuery(document).ready(function() {
	jQuery('#select_all_template').click(function() {
		var checked = jQuery(this).is(':checked');
		jQuery('#template-positions input[type=checkbox][name="selected_template_positions[]"]').each(function() {
			if (checked) {
				jQuery(this).attr('checked', checked);			
			}
			else {
				jQuery(this).removeAttr('checked');
			}
		});
	});
});