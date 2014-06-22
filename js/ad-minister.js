jQuery(document).ready(function() {
	// Tests if a given Date() instance is valid
	function isValidDate(d) {
  		if ( Object.prototype.toString.call(d) !== "[object Date]" )
   			return false;
  		return !isNaN(d.getTime());
	}
	
	String.prototype.format = function() {
	  var args = arguments;
	  return this.replace(/{(\d+)}/g, function(match, number) { 
	    return typeof args[number] != 'undefined'
	      ? args[number]
	      : match
	    ;
	  });
	};
	
	// Positions Page
	jQuery('input[type=checkbox]#rotate').change(function() {
		var checked = jQuery(this).attr('checked');
        if (checked) {
			if (!jQuery('input#rotate_time').val()) {
				jQuery('input#rotate_time').val(7);
			}
			jQuery('tr#positions_edit_rotate_time').show();
		}
		else {
			jQuery('tr#positions_edit_rotate_time').hide();
		}
	});	
});