jQuery(document).ready(function() {
	var mode, formfield;
	
	// Ad-minister 'Create Content' form controls
	var ad_title = jQuery('#title');
	var	ad_title_info = jQuery('#title_info');
	var	ad_media_url = jQuery('#ad_media_url');
	var ad_media_url_info = jQuery('#ad_media_url_info');
	var ad_size = jQuery('#ad_size');
	var	ad_link_url = jQuery('#ad_link_url');
	var ad_link_url_info = jQuery('#ad_link_url_info');
	var	ad_audio_url = jQuery('#ad_audio_url');
	var ad_audio_url_info = jQuery('#ad_audio_url_info');
	var ad_hint = jQuery('#ad_hint');
	var ad_schedule = jQuery('#scheduele');
	var ad_schedule_df = jQuery('#ad_schedule_df');
	var ad_schedule_df_info = jQuery('#ad_schedule_df_info');
	var ad_schedule_dt = jQuery('#ad_schedule_dt');
	var ad_schedule_dt_info = jQuery('#ad_schedule_dt_info');
	var ad_position = jQuery('select#ad_position_edit_0');
	var ad_position_info = jQuery('#ad_position_info');
	var sched_add_button = jQuery('#sched_add_button');
	var	ad_mode = jQuery('#ad_mode');
	var ad_mode_tabs = jQuery('ul#mode');
	var ad_modes_synced = jQuery('#ad_modes_synced');
	var ad_html = jQuery('textarea#content');
	var ad_preview = jQuery('#ad-preview');
	var preview_button = jQuery('#preview_button');
	
	// Give focus to title field
	ad_title.focus();
	
	// Validates 'Title' field
	function validate_ad_title() {
		if (ad_title.val() == "") {
      		ad_title.focus();
			ad_title.addClass("error"); // adding css error class to the control
			ad_title_info.text("Title cannot be empty"); 
			ad_title_info.addClass("error"); //add error class to info span
			ad_title_info.show();
			return false;
		} else {
			ad_title.removeClass("error");
			ad_title_info.text("");
			ad_title_info.removeClass("error");
			ad_title_info.hide();
		}
		return true;
	}
	ad_title.change(validate_ad_title);
	 
	// Validates 'Media URL' field
	function validate_ad_media_url() {
		if (ad_media_url.val() == "") {
      		ad_media_url.focus();
			ad_media_url.addClass("error"); // adding css error class to the control
			ad_media_url_info.text("Media URL cannot be empty"); 
			ad_media_url_info.addClass("error"); //add error class to info span
			ad_media_url_info.show();
			return false;
		} else {
			ad_media_url.removeClass("error");
			ad_media_url_info.text("");
			ad_media_url_info.removeClass("error");
			ad_media_url_info.hide();
		}
		return true;
	}
	ad_media_url.change(validate_ad_media_url);

	// Validates 'Position' field
	function validate_ad_position() {
		if (ad_position.val() == "-") {
      		ad_position.focus();
			ad_position.addClass("error"); // adding css error class to the control
			ad_position_info.text("Please select a banner position"); 
			ad_position_info.addClass("error"); //add error class to info span
			ad_position_info.show();
			return false;
		} else {
			ad_position.removeClass("error");
			ad_position_info.text("");
			ad_position_info.removeClass("error");
			ad_position_info.hide();
		}
		return true;
	}
	ad_position.change(validate_ad_position);
	
	// Add date picker functionality to 'From' and 'To' date selection fields for 'Schedule' field
	jQuery(function() {
		jQuery("#ad_schedule_df").datepicker({ dateFormat: "yy-mm-dd", minDate: new Date(), changeMonth: true, changeYear: true });
		jQuery("#ad_schedule_dt").datepicker({ dateFormat: "yy-mm-dd", minDate: new Date(), changeMonth: true, changeYear: true });
	});
	
	// 'Schedule' field 'Add Schedule' button onClick event handler
	sched_add_button.click(function() {
		var validated = true, new_schedule = '';
		validated = validate_ad_schedule_dt() ? validated : false;
		validated = validate_ad_schedule_df() ? validated : false;
		if (!validated) return false;
		if (ad_schedule.val() != '') new_schedule += ',';
		new_schedule += ad_schedule_df.val() + ':' + ad_schedule_dt.val();
		ad_schedule.val(ad_schedule.val() + new_schedule);
		ad_schedule_df.val('');
		ad_schedule_dt.val('');
	});

	// Validates 'Schedule From' field
	function validate_ad_schedule_df() {
		if (ad_schedule_df.val() == "") {
			ad_schedule_df.addClass("error"); // adding css error class to the control
			ad_schedule_df_info.text("Please select a date"); 
			ad_schedule_df_info.addClass("error"); //add error class to info span
			ad_schedule_df_info.show();
			return false;	
		} else {
			ad_schedule_df.removeClass("error");
			ad_schedule_df_info.text("");
			ad_schedule_df_info.removeClass("error");
			ad_schedule_df_info.hide();
		}
		return true;
	}
	ad_schedule_df.change(validate_ad_schedule_df);
	
	// Validates 'Schedule To' field
	function validate_ad_schedule_dt() {
		if (ad_schedule_dt.val() == "") {
			ad_schedule_dt.addClass("error"); // adding css error class to the control
			ad_schedule_dt_info.text("Please select a date"); 
			ad_schedule_dt_info.addClass("error"); //add error class to info span
			ad_schedule_dt_info.show();
			return false;	
		} else {
			ad_schedule_dt.removeClass("error");
			ad_schedule_dt_info.text("");
			ad_schedule_dt_info.removeClass("error");
			ad_schedule_dt_info.hide();
		}
		return true;
	}
	ad_schedule_dt.change(validate_ad_schedule_dt);
	
	// Validate fields on form submission
	jQuery('#save').click(function() {
		var validated = true;
		
		// Long-circuit evaluation
		validated = validate_ad_position() ? validated: false;
		if (ad_mode.val() == 'mode_basic') {
			validated = validate_ad_media_url() ? validated : false;
		}
		validated = validate_ad_title() ? validated: false;
		return validated;
	});
	
	jQuery('#delete').click(function(e) {
		if (!confirm('Are you sure you want to delete this content?')) {
			e.preventDefault();
		}
	});
	 
	jQuery('#ad_media_button').click(function() {
		formfield = ad_media_url;
		tb_show('', 'media-upload.php?ad-minister=true&amp;tab=library&amp;TB_iframe=true');
		return false;
	});
	
	jQuery('#ad_link_button').click(function() {
		formfield = ad_link_url;
		tb_show('', 'media-upload.php?ad-minister=true&amp;tab=library&amp;TB_iframe=true');
		return false;
	});
	
	jQuery('#ad_audio_button').click(function() {
		formfield = ad_audio_url;
		tb_show('', 'media-upload.php?ad-minister=true&amp;type=audio&amp;tab=library&amp;TB_iframe=true');
		return false;
	});

	// Preview button onClick event handler
	jQuery('#preview-button').click(function() {
		return preview_ad_content();
	});

	// Modify information returned form 'Media Uploader' dialog window
	window.original_send_to_editor = window.send_to_editor; 
	window.send_to_editor = function(html) {
		if ((document.URL.indexOf("ad-minister-create") != -1) && (ad_mode.val() == 'mode_basic')) {
			var matches, src, mime_type;
			pattern = /<!--source='([^>'"]*)'|mime-type='([^>'"]*)'-->$/i;
			matches = html.match(pattern);
			if (!(matches == null)) {
				src = matches[1];
				mime_type = matches[2];			
			}
			formfield.val(src).change().focus().select();
			tb_remove();
		}
		else {
			return window.original_send_to_editor(html);
		}
	}
	
	// Ad-minister Mode Tabs
	ad_mode_tabs.each(function(){
	    // For each set of tabs, we want to keep track of
	    // which tab is active and it's associated content
	    var $active, $content, $links = jQuery(this).find('a');

	    // If no match is found, use the first link as the initial active tab.
		$active = jQuery($links.filter('[id="' + ad_mode.val() + '"]')[0] || $links[0]);	
		$active.addClass('tabs-current');
	    $content = jQuery('.' + $active.attr('id'));
		ad_mode.val($active.attr('id'));
		
	    // Hide the remaining content
	    $links.not($active).each(function () {
	        jQuery('.' + jQuery(this).attr('id')).hide();
	    });

	    // Bind the click event handler
	    jQuery(this).on('click', 'a', function(e){
			// Make the old tab inactive
	        $active.removeClass('tabs-current');
	        $content.hide();
			
	        // Update the variables with the new link and content
	        $active = jQuery(this);
	        $content = jQuery('.' + $active.attr('id'));
			ad_mode.val($active.attr('id'));
			
	        // Make the tab active
	        $active.addClass('tabs-current');
	        $content.show();
			
			if (ad_mode.val() == 'mode_basic') {
				
			}
			
			// Take certain actions depending on the mode selected
			if (ad_modes_synced.attr('checked')) {
				switch (ad_mode.val()) {
					case 'mode_basic':
						complete_basic_from_advanced();
						break;
					case 'mode_advanced':
						//switchEditors.switchto(jQuery('a#content-html'));
						jQuery('a#content-html').click();
						complete_advanced_from_basic();	
						break;
				}
			}
			
	        // Prevent the anchor's default click action
	        e.preventDefault();
	    });
	});
	
	// Returns the number in the array closest to the given number
	function getClosestNumber(number, numbers) {
		if (!(numbers instanceof Array)) return false;
		var diff = 999999999;
		for (i = 0; i < numbers.length; ++i) {
			if (Math.abs((+numbers[i]) - (+number)) < Math.abs(diff)) {
				diff = (+numbers[i]) - (+number);
			}
		}
		return ((+number) + (+diff));
	}
	
	// Fill in basic mode controls from advanced mode html
	function complete_basic_from_advanced() {
		if (jQuery.trim(ad_html.val()) == '') return false;
		
		var html = ad_html.val(), value, width, height, pattern;
		var widths = [306, 474, 642, 978];
		var heights = [100, 140, 270, 300, 560];
		
		/* Get Media URL first */
		
		// Try searching for an image
		pattern = /<img[^>]*src=['"]([^\s'">]*)['"]/i;
		value = html.match(pattern);
		if (value != null) {
			
			// Get Media URL
			ad_media_url.val(value[1]).change();
			
			// Get image dimensions
			pattern = /<img[^>]+width=['"]([^\s'">]*)['"]/i;
			width = html.match(pattern);
			width = (width != null) ? getClosestNumber(width[1], widths) : '';
			pattern = /<img[^>]+height=['"]([^\s'">]*)['"]/i;
			height = html.match(pattern);
			height = (height != null) ? getClosestNumber(height[1], heights) : '';
			if (width != '' && height != '') {
				ad_size.val(width + 'x' + height);
			}
			
			// Get image title if present
			pattern = /<img[^>]+title=['"]([^'">]*)['"]/i;
			value = html.match(pattern);
			if (value != null) {
				ad_hint.val(value[1]);
			}
		}
		else {
			// Try searching for a shockwave flash animation
			pattern = /<object.*<param\s+name=['"]src['"]\s+value=['"]([^\s'">]*)['"]/i;
			value = html.match(pattern);
			if (value != null) {
				// Get Media URL
				ad_media_url.val(value[1]).change();
				
				// Get flash animation dimensions
				pattern = /<object[^>]+width=['"]([^\s'">]*)['"]/i;
				width = html.match(pattern);
				width = (width != null) ? getClosestNumber(width[1], widths) : '';
				pattern = /<object[^>]+height=['"]([^\s'">]*)['"]/i;
				height = html.match(pattern);
				height = (height != null) ? getClosestNumber(height[1], heights) : '';
				if (width != '' && height != '') {
					ad_size.val(width + 'x' + height);
				}
			}
			else {
				pattern = /<embed[^>]+src=['"]([^\s'">]*)['"]/i;
				value = html.match(pattern);
				if (value != null) {
					// Get Media URL
					ad_media_url.val(value[1]).change();
					
					// Get flash animation dimensions
					pattern = /<embed[^>]+width=['"]([^\s'">]*)['"]/i;
					width = html.match(pattern);
					width = (width != null) ? getClosestNumber(width[1], widths) : '';
					pattern = /<embed[^>]+height=['"]([^\s'">]*)['"]/i;
					height = html.match(pattern);
					height = (height != null) ? getClosestNumber(height[1], heights) : '';
					if (width != '' && height != '') {
						ad_size.val(width + 'x' + height);
					}
				}
				else {
					pattern = /\[flashad[^\]]+src=['"]([^\s'"\]]*)['"]/i;
					value = html.match(pattern);
					if (value != null) {
						// Get Media URL
						ad_media_url.val(value[1]).change();
						
						// Get flash animation dimensions
						pattern = /\[flashad[^\]]+width=['"]([^\s'"\]]*)['"]/i;
						value = html.match(pattern);
						width = (width != null) ? getClosestNumber(width[1], widths) : '';
						pattern = /\[flashad[^\]]+height=['"]([^\s'"\]]*)['"]/i;
						height = html.match(pattern);
						height = (height != null) ? getClosestNumber(height[1], heights) : '';
						if (width != '' && height != '') {
							ad_size.val(width + 'x' + height);
						}
					}
				}
			}
		}
		
		/* Get Link URL */
		pattern = /<a[^>]+href=['"]([^\s'"]*)['"]/i;
		value = html.match(pattern);
		if (value != null) {
			ad_link_url.val(value[1]);
		}
		
		/* Get Audio URL */
		pattern = /\[esplayer[^\]]+url=['"]([^\s'"\]]*)['"]/i;
		value = html.match(pattern);
		if (value != null) {
			ad_audio_url.val(value[1]);
		}
	}
	
	// Fill in advanced mode html editor from basic mode fields
	function complete_advanced_from_basic() {
		var html = get_html_from_basic();
		if (html !== false) {
			ad_html.val(html);
		}
	}
	
	// Generates and returns the html ad from the basic mode field values
	function get_html_from_basic() {
		var	html = '', ext, width, height;
		width = ad_size.val().split('x');
		height = jQuery.trim(width[1]);
		width = jQuery.trim(width[0]);
		if (jQuery.trim(ad_media_url.val()) == '') return false;		
		ext = ad_media_url.val().split('.').pop();
		switch (ext) {
			case 'jpg':
			case 'jpeg':
			case 'gif':
			case 'bmp':
			case 'png':
			case 'tif':
			case 'tiff':
				html = "<img src='{0}' width='{1}' height='{2}' title='{3}' />".format(ad_media_url.val(), width, height, ad_hint.val());
				break;
			case 'swf':
				html = '<object width="{1}" height="{2}" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"';
				html += 'codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0">';
				html += '<param name="quality" value="high" /><param name="src"';
				html += 'value="{0}" /><param name="pluginspage" value="http://www.macromedia.com/go/getflashplayer" />';
				html += '<param name="wmode" value="transparent" /><embed width="{1}" height="{2}" type="application/x-shockwave-flash"';
				html += 'src="{0}" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer"';
				html += 'wmode="transparent" /></object>';
				html = html.format(ad_media_url.val(), width, height);
				break;
			default:
				html = '';
		}
		
		// Add anchor tags
		if ( ad_link_url.val() != '' ) 
			html = "<a href='{0}' target='_blank' title='{1}' >{2}</a>".format(ad_link_url.val(), ad_hint.val(), html);
		
		// Add audio
		if ( ad_audio_url.val() != '' ) 
			html += '<div style="display:inline;position:relative;border:solid 0px #f00;" id="esplayer_1_tmpspan"><canvas id="esplayer_1" style="cursor:pointer;width:312.75px; height:33.75px;" width="312.75px" height="33.75px"></canvas></div><input type="hidden" id="esplayervar1" value="simple|esplayer_1|{0}||{1}px|{2}px|-0px|-999||-999|-999|0|false|false|false||100|||">'.format(ad_audio_url.val(), width, 27);

		return html;
	}
	
	// Generates a preview of the current ad content
	function preview_ad_content() {
		var	html = '', ext, width, height;
		width = ad_size.val().split('x');
		height = jQuery.trim(width[1]);
		width = jQuery.trim(width[0]);
		if (ad_mode.val() == 'mode_basic') {
			if (validate_ad_media_url()) {
				html = get_html_from_basic()
			}
		}
		else {
			html = ad_html.val();
		}
		
		if (html == '') {
			alert('There is no advertisement content to preview.');
			return false;
		}
		ad_preview.html(html);
		return true;
	}
});