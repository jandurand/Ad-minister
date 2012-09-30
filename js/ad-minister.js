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
});