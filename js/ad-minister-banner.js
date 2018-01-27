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
	var ad_position = jQuery('select#ad_position').multiselect({
		minWidth: 700,
		selectedText: '# positions selected',
		noneSelectedText: 'Select banner positions',
      	selectedList: 1,
		position: {
			my: 'left bottom',
			at: 'left top',
			collision: 'flip'
		}
	}).multiselectfilter({
		width: '150',
		placeholder: "Enter position",
		autoReset: true
	});
	var ad_position_info = jQuery('#ad_position_info');
	var sched_add_button = jQuery('#sched_add_button');
	var	ad_mode = jQuery('#ad_mode');
	var ad_mode_tabs = jQuery('ul#mode');
	var ad_modes_synced = jQuery('#ad_modes_synced');
	var ad_html = jQuery('textarea#content');
	var ad_preview = jQuery('#ad-preview');
	
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
		}
		
		ad_title.removeClass("error");
		ad_title_info.text("");
		ad_title_info.removeClass("error");
		ad_title_info.hide();
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
		}	
		
		ad_media_url.removeClass("error");
		ad_media_url_info.text("");
		ad_media_url_info.removeClass("error");
		ad_media_url_info.hide();
		return true;
	}
	ad_media_url.change(validate_ad_media_url);

	// Validates 'Position' field
	function validate_ad_position() {
		if (ad_position.val() == null) {
      		ad_position.focus();
			ad_position.addClass("error"); // adding css error class to the control
			ad_position_info.text("Please select a banner position"); 
			ad_position_info.addClass("error"); //add error class to info span
			ad_position_info.show();
			return false;
		} 	
		
		ad_position.removeClass("error");
		ad_position_info.text("");
		ad_position_info.removeClass("error");
		ad_position_info.hide();
		return true;
	}
	ad_position.change(validate_ad_position);
	
	// Add date picker functionality to 'From' and 'To' date selection fields for 'Schedule' field
	jQuery(function() {
		jQuery("#ad_schedule_df").datepicker({ dateFormat: "yy-mm-dd", changeMonth: true, changeYear: true });
		jQuery("#ad_schedule_dt").datepicker({ dateFormat: "yy-mm-dd", changeMonth: true, changeYear: true });
	});
	
	// 'Schedule' field 'Add Schedule' button onClick event handler
	sched_add_button.click(function() {
		var validated = true, new_schedule = '';
		validated = validate_ad_schedule();
		if (!validated) return false;
		if (ad_schedule.val() != '') new_schedule += ',';
		new_schedule += ad_schedule_df.val() + ':' + ad_schedule_dt.val();
		ad_schedule.val(ad_schedule.val() + new_schedule);
		ad_schedule_df.val('');
		ad_schedule_dt.val('');
	});

	// Validates the provided schedule
	function validate_ad_schedule() {
		if (!(validate_ad_schedule_df() && validate_ad_schedule_dt())) {
			return false;
		}
		
		if (ad_schedule_df.val() > ad_schedule_dt.val()) {
			ad_schedule_df.addClass("error"); // adding css error class to the control
			ad_schedule_df_info.text("Start date > End date"); 
			ad_schedule_df_info.addClass("error"); //add error class to info span
			ad_schedule_df_info.show();
			return false;	
		}
			
		ad_schedule_df.removeClass("error");
		ad_schedule_df_info.text("");
		ad_schedule_df_info.removeClass("error");
		ad_schedule_df_info.hide();				
		return true;
	}

	// Validates 'Schedule From' field
	function validate_ad_schedule_df() {
		if (ad_schedule_df.val() == "") {
			ad_schedule_df.addClass("error"); // adding css error class to the control
			ad_schedule_df_info.text("Please select a date"); 
			ad_schedule_df_info.addClass("error"); //add error class to info span
			ad_schedule_df_info.show();
			return false;	
		}
		
		ad_schedule_df.removeClass("error");
		ad_schedule_df_info.text("");
		ad_schedule_df_info.removeClass("error");
		ad_schedule_df_info.hide();
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
		}
			
		ad_schedule_dt.removeClass("error");
		ad_schedule_dt_info.text("");
		ad_schedule_dt_info.removeClass("error");
		ad_schedule_dt_info.hide();
		return true;
	}
	ad_schedule_dt.change(validate_ad_schedule_dt);
	
	// Validate fields on form submission
	jQuery('#save').click(function(e) {
		var validated = true;
		
		// Long-circuit evaluation
		validated = validate_ad_position() ? validated: false;
		if (ad_mode.val() == 'mode_basic') {
			validated = validate_ad_media_url() ? validated : false;
		}
		validated = validate_ad_title() ? validated: false;
			
		if (validated) {
			var action_url = jQuery('form#post').attr('action');
			jQuery('form#post').attr('action', action_url + '&action=edit&id=' + jQuery('input#id').val()); 
		}
		else {
			e.preventDefault();
		}
	});
	
	jQuery('#delete').click(function(e) {
		if (!confirm('Are you sure you want to delete this content?')) {
			e.preventDefault();
		}
	});
	 
	function select_media(options) {
    if (typeof(options) === 'undefined') return false;
		if ((typeof(options) !== 'object') && (typeof(options) !== 'function')) return false;
		
		if (typeof(options) === 'function') {
			options = {
				select: options,
				title: 'Select Media',
				button_text: 'Select',
				media_type: '' // Show All
			}
		} 
		if (typeof(options.title) === 'undefined') options.title = 'Select Media';
		if (typeof(options.button_text) === 'undefined') options.button_text = 'Select';
		if (typeof(options.media_type) === 'undefined') options.media_type = '';
		
		var custom_uploader = wp.media({
        frame: 'select',
				title: options.title,
        button: { text: options.button_text },
				library: { type: options.media_type },
        multiple: false  // Set this to true to allow multiple files to be selected
    })
    .on('select', function() {
        var attachment = custom_uploader.state().get('selection').first().toJSON();
        options.select(attachment.url);
    })
    .open();		
	}

	jQuery('#ad_media_button').click(function() {
 	 	select_media({
			select: function (url) {
				ad_media_url.val(url);
				return url;
			},
			title: 'Select Banner Image/Animation'
		});
		
	  return false;       
	});
	
	jQuery('#ad_link_button').click(function() {
		select_media({
			select: function (url) {
				ad_link_url.val(url);
				return url;
			},
			title: 'Select Banner Attachment'
		});
			
		return false;
	});
	
	jQuery('#ad_audio_button').click(function() {
		select_media({
			select: function (url) {
				ad_audio_url.val(url);
				return url;
			},
			title: 'Select Banner Audio', 
			media_type: 'audio'
		});

		return false;
	});

	// Preview button onClick event handler
	jQuery('#preview-button').click(function() {
		return preview_ad_content();
	});
		
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
				
			// Take certain actions depending on the mode selected
			/*if (ad_modes_synced.attr('checked')) {
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
			}*/
				
	    // Prevent the anchor's default click action
	    e.preventDefault();
		});
	});
	
	if (!Array.prototype.map) {
	  Array.prototype.map = function(func /*, thisp*/) {
	    var len = this.length;
	    if (typeof func != "function")
	      throw new TypeError();

	    var res = new Array(len);
	    var thisp = arguments[1];
	    for (var i = 0; i < len; i++) {
	      if (i in this)
	        res[i] = func.call(thisp, this[i], i, this);
	    }

	    return res;
	  };
	}

	if (!Array.prototype.closest) {
		Array.prototype.closest = function(n) {
			n = +n;
			if (typeof n != "number")
	    		throw new TypeError();

			var diff = Number.MAX_VALUE;
	    	for (var i = 0; i < this.length; ++i) {
	      		if (i in this) {
	        		diff = (Math.abs(+this[i] - n) < Math.abs(diff)) ? +this[i] - n : diff;
				}
			}
	
	    	return n + diff;
	  	};
	}
	
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
	
	// Gets the media src from the given html
	function get_media_src_url(html) {
		var pattern, value;
		
		// Try searching for an image
		pattern = /<img[^>]+src=['"]([^\s'">]*)['"]/i;
		value = html.match(pattern);
		if (value != null) return value[1];
			
		// Try searching for a shockwave flash animation
		pattern = /<object[^>]+<param\s+name=['"]src['"]\s+value=['"]([^\s'">]*)['"]/i;
		value = html.match(pattern);
		if (value != null) return value[1];
			
		pattern = /<embed[^>]+src=['"]([^\s'">]*)['"]/i;
		value = html.match(pattern);
		if (value != null) return value[1];
				
		pattern = /[\[<][^\]>]+src=['"]([^\s'"\]>]*)['"]/i;
		value = html.match(pattern);
		if (value != null) return value[1];
		
		pattern = /[\[<][^\]>]+file=['"]([^\s'"\]>]*)['"]/i;
		value = html.match(pattern);
		if (value != null) return value[1];
		
		pattern = /[\[<][^\]>]+url=['"]([^\s'"\]>]*)['"]/i;
		value = html.match(pattern);
		if (value != null) return value[1];

		pattern = /url:\s['"]([^\s'"\,]*)['"]/i;
		value = html.match(pattern);
		if (value != null) return value[1];		

		// Try searching for an anchor tag
		pattern = /<a[^>]+href=['"]([^\s'">]*)['"]/i;
		value = html.match(pattern);
		if (value != null) return value[1];
 
		return html;
	}
	
	function get_media_title(html) {
		// Get title if present
		pattern = /[\[<][^\]>]+title=['"]([^\s'"\]>]*)['"]/i;
		value = html.match(pattern);
		if (value != null) {
			return value[1];
		}
		return '';
	}
	
	function get_media_width(html) {
		var pattern, width, widths = [306, 474, 642, 978];
		pattern = /[\[<][^\]>]+width=['"]([\d]+)['"]/i;
		width = html.match(pattern);
		//width = (width != null) ? getClosestNumber(width[1], widths) : '306';
		//return (width != null) ? widths.closest(width[1]) + '' : '306';
		return (width != null) ? width[1] + '' : '';
	}
	
	function get_media_height(html) {
		var pattern, height, heights = [60, 100, 140, 250, 270, 300, 560];
		pattern = /[\[<][^\]>]+height=['"]([\d]+)['"]/i;
		height = html.match(pattern);
		//height = (height != null) ? getClosestNumber(height[1], heights) : '300';
		//return (height != null) ? heights.closest(height[1]) + '' : '300';
		return (height != null) ? height[1] + '' : '';
	}
	
	function get_img_dim_str(imgPath) {
	 	if (jQuery.trim(imgPath) == '') return ''; 
		
		var imgHeight;
	  var imgWidth;

	  function findHHandWW() {
	    imgHeight = this.height;
			imgWidth = this.width;
			return true;
	  }

    var myImage = new Image();
    myImage.name = imgPath;
    myImage.onload = findHHandWW;
    myImage.src = imgPath;
	  
		return imgWidth + 'x' + imgHeight;
	}
	
	// Fill in basic mode controls from advanced mode html
	function complete_basic_from_advanced() {
		if (jQuery.trim(ad_html.val()) == '') return false;
		var html = ad_html.val(), value, width, height, pattern;
		
		/* Get Media URL first */
		value = get_media_src_url(html);
		if (value != '') ad_media_url.val(value);
		
		/* Get Media Dimensions */
		width = get_media_width(html);
		height = get_media_height(html);
		var dims = ['306x60', '306x140', '306x250', '306x300', '474x270', '474x560', '642x100', '642x140', '978x100'];
		value = (dims.indexOf(width + 'x' + height) == -1) ? '' : width + 'x' + height;
		ad_size.val(value);
		
		value = get_media_title(html);	
		if (value != '') ad_hint.val(value);
		
		/* Get Link URL */
		pattern = /<a[^>]+href=['"]([^\s'"]*)['"]/i;
		value = html.match(pattern);
		if (value != null) {
			ad_link_url.val(value[1].replace('%tracker%', ''));
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
		administer_build_code(function (html) {
			ad_html.val(html);
		});
	}
	
	function administer_build_code(onSuccess) {
		var data = { 
			action: 'administer_build_code',
			ad_mode : 'mode_basic',
			ad_media_url : ad_media_url.val(),
			ad_size : ad_size.val(),
			ad_link_url : ad_link_url.val(),
			ad_audio_url : ad_audio_url.val(),
			ad_hint : ad_hint.val()
		};
		jQuery.post(
      ajaxurl,
			data,
      function(response) {
        onSuccess(response);  
				return response;
      }
    );	
	}
	
	// Generates and returns the html ad from the basic mode field values
	function get_html_from_basic() {
		var	html = '', ext, width, height;
		width = ad_size.val().split('x');
		//if (width = 'Actual') width = get_img_dim_str(ad_media_url.val()).split('x'); 
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
				/*html = '<object width="{1}" height="{2}" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"';
				html += 'codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0">';
				html += '<param name="quality" value="high" /><param name="src"';
				html += 'value="{0}" /><param name="pluginspage" value="http://www.macromedia.com/go/getflashplayer" />';
				html += '<param name="wmode" value="transparent" /><embed width="{1}" height="{2}" type="application/x-shockwave-flash"';
				html += 'src="{0}" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer"';
				html += 'wmode="transparent" /></object>';*/
				html = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="{1}" height="{2}"><param name="movie" value="{0}" /><param name="wmode" value="transparent" /><!--[if !IE]>--><object type="application/x-shockwave-flash" data="{0}" width="{1}" height="{2}"><param name="wmode" value="transparent" /><!--<![endif]--><p>Flash Content Unavailable</p><!--[if !IE]>--></object><!--<![endif]--></object>';			
				html = html.format(ad_media_url.val(), width, height);
				break;
			case 'flv':
				html = '<div id="flvplayer{0}" style="width:{1}px;height:{2}px"></div><script language="JavaScript">flowplayer("flvplayer{0}", "/flowplayer/flowplayer-3.2.16.swf", { clip: { url: "{3}", autoPlay: true, autoBuffering: true, linkUrl: "{4}", linkWindow: "_blank" }, plugins: { controls: null }, buffering: false, onFinish: function() { this.stop(); this.play(); }, onBeforePause: function() { return false; } });</script>";';
				html = html.format('1', width, height, ad_media_url.val(), ad_link_url.val());
			default:
				html = '';
		}
		
		// Add anchor tags
		if ( ad_link_url.val() != '' ) {
			if (ext === 'swf') {
				html += "<a class='flash-banner-link' href='%tracker%{0}' target='_blank' title='{1}'></a>".format(ad_link_url.val().replace('%tracker%', ''), ad_hint.val());
			}
			else {
				html = "<a href='%tracker%{0}' target='_blank' title='{1}'>{2}</a>".format(ad_link_url.val().replace('%tracker%', ''), ad_hint.val(), html);
			}
		}
		
		// Add audio
		if ( ad_audio_url.val() != '' ) 
			html += '<div style="display:inline;position:relative;border:solid 0px #f00;" id="esplayer_1_tmpspan"><canvas id="esplayer_1" style="cursor:pointer;width:312.75px; height:33.75px;" width="312.75px" height="33.75px"></canvas></div><input type="hidden" id="esplayervar1" value="simple|esplayer_1|{0}||{1}px|{2}px|-0px|-999||-999|-999|0|false|false|false||100|||">'.format(ad_audio_url.val(), width, 27);

		return html;
	}

	function show_preview(html) {
		var width, height, dims;

		if (html == '') {
			ad_preview.html('No content to preview.');
			return false;
		}

		width = get_media_width(html);
		width = (width != '') ? "width={0}".format(width + 10) : '';
		height = get_media_height(html);
		height = (height != '') ? "height={0}".format(height + 10) : '';
		dims = ( width != '' ? '&' . width : '' ) + ( height != '' ? '&' + height : '' );  
		
		ad_preview.html(html);
		tb_show('Preview', '#TB_inline?inlineId=ad-preview&modal=false' + dims, null);
		return true;	
	}
	
	// Generates a preview of the current ad content
	function preview_ad_content() {
		if ((ad_mode.val() === 'mode_basic') && validate_ad_media_url()) {
			administer_build_code(show_preview);
		}
		else {
			show_preview(ad_html.val());
		}

		return true;
	}
});