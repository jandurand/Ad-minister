<?php

/*
**    administer_main ( )
**
**    Main Ad-minster Admin
*/
function administer_main( $page ) {
	if ( empty( $page ) ) 
		$page = 'ad-minister-content';
	else if ( strpos( $page, 'ad-minister' ) === false ) 
		$page = 'ad-minister-' . $page;
	
	// Check that our statistics are set up
	$stats = administer_get_stats();
	$content = administer_get_content();
	$positions = get_post_meta(get_option('administer_post_id'), 'administer_positions', true);
	
	// Load the relevant page
	include("{$page}.php");
}

function administer_page_content() {
	administer_main( 'content' );
}
function administer_page_banner() {
	administer_main( 'banner' );
}
function administer_page_positions() {
	administer_main( 'positions' );
}
function administer_page_settings() {
	administer_main( 'settings' );
}
function administer_page_help() {
	administer_main( 'help' );
}

function administer_position_template ($position = array(), $nbr = 0) { echo administer_get_position_template($position, $nbr); }
function administer_get_position_template ($position = array(), $nbr = 0) { 
	$key  = $position['position']; // p2m_meta('position_key_' . $nbr);
	$desc = $position['description']; //p2m_meta('position_desc_' . $nbr);
			
	// Set up css formatting
	$class =  ($nbr % 2) ? '' : 'alternate';
	$html = '
			<tr class="%class%">
				<td>' . $key . '</td>
				<td>' . $desc . '</td>
				<td>' . htmlentities($position['before']) . ' ' . htmlentities($position['after']) . '</td>
				<td>
					<a href="%url_edit%">' . __('Edit', 'ad-minister') . '</a> |
					<a href="%url_remove%">' . __('Remove', 'ad-minister') . '</a>
				</td>
			</tr>
			';

	// Inject template values
	$url = get_option('siteurl') . '/' . PLUGINDIR . '/' . dirname(plugin_basename (__FILE__));
	$html = str_replace('%url_edit%', administer_get_page_url( 'positions' ) . '&key=' . urlencode($key) . '&action=edit', $html);
	$html = str_replace('%url_remove%', administer_get_page_url( 'positions' ) . '&key=' . urlencode($key) . '&action=delete', $html);
	$html = str_replace('%class%', $class, $html);

	return $html;
}

/*
**   administer_position_select ()
**
**   Generate the select list for the defined positions
*/
function administer_position_select ($nbr = '', $value = '') {

	if ($value == '') $value = '-';

	$html = '<select name="position" id ="ad_position_edit_' . $nbr . '">';

	$got_selected = false;
	
	$positions = get_post_meta(get_option('administer_post_id'), 'administer_positions', true);	
	if (!is_array($positions)) $positions = array();
	$position_keys = array_keys($positions); 
	sort($position_keys);
	
	foreach ($position_keys as $key) {
		// $position_key = p2m_meta('ad_position_' . $nbr);
		if ($key == $value) {
			$selected = ' selected="selected"';
			$got_selected = true;
		} else $selected = '';
		$description = ($positions[$key]['description']) ? ' (' . $positions[$key]['description'] . ')' : '';
		$html .= '<option value="' . $positions[$key]['position'] . '"' . $selected .'>' . $positions[$key]['position'] . $description . '</option>';
	}

	// If nothing got selected, then churn out a blank value for orphans.
	if ($value == '-') $html .= '<option value="-" selected="selected">(' . __('None', 'ad-minister') . ')</option>';
	if (!$value || $value != '-') $html .= '<option value="-">(' . __('None', 'ad-minister') . ')</option>';

	$html .= '</select>';
	return $html;
}

/*
**   p2m_nbr_to_save()
**
**   Finds the highest number from zero that is not currently
**   some content.
*/
function administer_nbr_to_save($what = 'content') {

	$content = get_post_meta(get_option('administer_post_id'), 'administer_content', true);

	if (!is_array($content)) return 0;
	if (empty($content)) return 0;

	// Store the ids in a separate array
	$ids = array_keys($content);
	sort($ids);

	// Get the smallest unpopulated id
	for ($i = 0; $i < $ids[count($ids) - 1] + 2; $i++)
			if ($i != $ids[$i]) return strval($i);
}

/*
**   administer_ok_to_go()
**
**   Checks if the supplied post/page ID exists.
*/
function administer_ok_to_go() {
	$the_page = get_page(get_option('administer_post_id'));
	$ok_to_go = ($the_page->post_title) ? true : false;
	return $ok_to_go;
}

/*
**   administer_content_age()
**
**  Calculates the age of a content. Returns assoc. array with 'start' and 'end' ages, i.e.
**	negative numbers for events in the future, positive for events passed, just like at
**	shuttle launches.
*/
function administer_content_age($schedule) {
	if (!$schedule) return array(array('start' => '0', 'end' => '0'));

	$age = array();
	$now = (int) current_time( 'timestamp' ); // Get wordpress local current time
	
	// Separate schedule into different periods
	$periods = explode(',', $schedule);
	
	// Content may have multiple schedule periods
	foreach ( $periods as $period ) {
		
		// Makes dates inclusive by default
		$start_time = "00.00.00";	// same as 00:00:00
		$end_time = "23.59.59";		// same as 23:59:59
		
		// Separate period into start date and end date
		$dates = explode( ':', ltrim( rtrim( $period ) ) );
		if ( count( $dates ) == 2 ) {
			list( $start_date, $end_date ) = $dates;
		
			// Check if time specified with start date
			$start_date = explode( ' ', ltrim( rtrim( $start_date ) ) );
			if ( count( $start_date ) == 2 ) { 
				$start_time = $start_date[1];
			}
			$start_date = $start_date[0];
			
			// Check if time specified with end date
			$end_date = explode( ' ', ltrim( rtrim( $end_date ) ) );
			if ( count( $end_date ) == 2 ) {
				$end_time = $end_date[1];
			}
			$end_date = $end_date[0];
			
			$start_str = "$start_date $start_time";
			$end_str = "$end_date $end_time";
			$start = strtotime( $start_str );
			$end   = strtotime( $end_str );
		}
		else if ( count( $dates ) == 1 ) {
			// Assume only end date entered
			$end_date = $dates[0];
			
			// Check if time specified with end date
			$end_date = explode( ' ', ltrim( rtrim( $end_date ) ) );
			if ( count( $end_date ) == 2 ) {
				$end_time = $end_date[1];
			}
			$end_date = $end_date[0];
			
			$end_str = "$end_date $end_time";
			$start = $now;
			$end = strtotime( $end_str );
		}
		
		$day_in_secs = 86400; // 24 hours is 86400 seconds
		$age[] = array( 
		'start' => ( $start - $now ) / $day_in_secs, 
		'end' => ( $end - $now ) / $day_in_secs 
		);

	}

	return $age;
}


/*
**  administer_dashboard_widget ()
**
**  Stick something on the dashboard if something is due to expire or become active, or if
**	it's running out of clicks or impressions.
*/
function administer_dashboard_widget () {

	$stats = administer_get_stats();
	
	// If there is no content, then skip this
	if (!($content = get_post_meta(get_option('administer_post_id'), 'administer_content', true))) 
		$content = array(); 

	$url = administer_get_page_url();
	$period = get_option('administer_dashboard_period');

	$li_ends = '';
	$li_starts = '';
	$li_impressions = '';
	$li_clicks = '';

	foreach ($content as $con) {
	
		// Format impressions
		$impressions = ($stats[$con['id']]['i']) ? $stats[$con['id']]['i'] : 0;
		$impressions_p = ($con['impressions']) ? 100 * $impressions / $con['impressions'] : 0;
		if ($impressions_p > 100 - get_option('administer_dashboard_percentage') && $impressions_p < 100) {
			$li_impressions .= '<li><a href="' . $url . '&cshow=' . urlencode($con['position']) . '">' . $con['title'] . '</a>';
			$li_impressions .= ' ' . __('has', 'ad-minister') . ' ' . round(100 - $impressions_p, 1) . __('% of impressions left', 'ad-minister') . '.</li>';
		}
		
		// Format clicks
		$clicks = ($stats[$con['id']]['c']) ? $stats[$con['id']]['c'] : 0;
		$clicks_p = ($con['clicks']) ? 100 * $clicks / $con['clicks'] : 0;
		if ($clicks_p > 100 - get_option('administer_dashboard_percentage') && $clicks_p < 100) {
			$li_clicks .= '<li><a href="' . $url . '&cshow=' . urlencode($con['position']) . '">' . $con['title'] . '</a>';
			$li_clicks .= ' ' . __('has', 'ad-minister') . ' ' . round(100 - $clicks_p, 1) . __('% of clicks left', 'ad-minister') . '.</li>';
		}
		
		// Format start/end times
		$ages = administer_content_age($con['scheduele']);
		foreach ($ages as $age) {
			if ( $age['start'] && $age['start'] >= 0 && $age['start'] < $period ) {
				$link_url .= $url . '&tab=upload&action=edit&id=' . $con['id'];
				$link_style = 'style="color: #00AA00;"';
				$li_starts .= '<li><a ' . $link_style . ' href="' . $link_url . '">' . $con['title'] . '</a>';
				$li_starts .= ' ' . __('starts in', 'ad-minister') . ' ' . sprintf( "%.1f", $age['start'] ) . ' ' . __('days', 'ad-minister') . '.</li>';
				break;
			}
			else if ( $age['end'] && $age['end'] >= 0 && $age['end'] < $period ) {
				$link_url = $url . '&tab=upload&action=edit&id=' . $con['id']; 
				$link_style = 'style="color: #AA0000;"';
				$li_ends .= '<li><a ' . $link_style . ' href="' . $link_url . '">' . $con['title'] . '</a>';
				$li_ends .= ' ' . __('expires in', 'ad-minister') . ' ' . sprintf( "%.1f", $age['end'] ) . ' days.</li>';
				break;
			}		
		}
	}

	// Display dashboard widget 
	if ($li_starts || $li_ends) {
		echo '<p><ul>';
		if ($li_impressions) echo $li_impressions;
		if ($li_clicks) echo $li_clicks;
		if ($li_ends) echo $li_ends;
		if ($li_starts) echo $li_starts;
		echo '</ul></p>';
	}
	else {
		echo '<p>No upcoming events.</p>';
	}
		
}

// Displays Ad-minister dashboard widget options
function administer_dashboard_widget_control() {    
    $widget_id = 'ad-minister-dashboard-widget'; // This must be the same ID we set in wp_add_dashboard_widget
    $form_id = 'ad-minister-dashboard-widget-control'; // Set this to whatever you want
    
    // Checks whether there are already dashboard widget options in the database
    if ( !$widget_options = get_option( 'dashboard_widget_options' ) )
      $widget_options = array(); // If not, we create a new array
    
    // Check whether we have information for this form
    if ( !isset($widget_options[$widget_id]) )
      $widget_options[$widget_id] = array(); // If not, we create a new array
    
    // Check whether our form was just submitted
    if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST[$form_id] ) ) {
      $event_period = absint( $_POST[$form_id]['event_period'] );
      
	  update_option( 'administer_dashboard_period', $event_period );
      //$widget_options[$widget_id]['event_period'] = $event_period;
      //update_option( 'dashboard_widget_options', $widget_options ); // Update our dashboard widget options so we can access later
    }
    
    //$event_period = isset( $widget_options[$widget_id]['event_period'] ) ? (int) $widget_options[$widget_id]['event_period'] : '';
    $event_period = get_option( 'administer_dashboard_period', '' );
	
    // Create our form fields
    echo '<p><label for="ad-minister-dashboard-widget-event-period">' . __('Number of days to check for upcoming events: ') . '</label>';
    echo '<input id="ad-minister-dashboard-widget-event-period" name="' . $form_id . '[event_period]" type="text" value="' . $event_period . '" size="3" /></p>';
}

function administer_register_widgets(){
     wp_add_dashboard_widget( 'ad-minister-dashboard-widget', 'Ad-minister Events', 'administer_dashboard_widget', 'administer_dashboard_widget_control' );
}

/**
***		administer_translate ( )
**/
function administer_translate(){
    // Load a language
	load_plugin_textdomain('p2m-ad-manager', PLUGINDIR . '/' . dirname(plugin_basename (__FILE__)) );
}

/**
***   administer_export ( )
***   
***   Enable one-click xml export of the post that contains the administer data.
**/
function administer_export () {
	global $post_ids;
	if ($_GET['administer'])
		$post_ids = array(get_option('administer_post_id'));
}

/**
***  administer_load_widgets (  )
***
***   Create the widgets...
**/
function administer_load_widgets() {
	register_widget( 'AdministerWidget' );
}

/*
** AdministerWidget Widget Class
*/
class AdministerWidget extends WP_Widget {

    function AdministerWidget() {
        //Constructor
        parent::WP_Widget(false, $name = 'Ad-minister', array('description' => 'Widget For Ad-minister Plugin.'));
    }

    function widget($args, $instance) {
        // outputs the content of the widget
        extract( $args );
        $position = $instance['position'];
        
        echo $before_widget;
		administer_display_position( $position );
		echo $after_widget;
    }
	
	// Updates the widget
    function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['position'] = $new_instance['position'];
		return $instance;
    }
	
	// Widget form in backend
    function form($instance) {
		global $wpdb; 
	
        $position = esc_attr( $instance['position'] );
		if ( !$position ) $position = 'None';
		$title = strip_tags( $instance['title'] );
       
		// Get the existing widget positions and build a simple select dropdown for the user.
		$positions = get_post_meta( get_option('administer_post_id'), 'administer_positions', true );
        ksort( $positions );
		$pos_options = array();
        $pos_options[] = '<option value="None">None</option>';
		if ( is_array( $positions ) and !empty( $positions ) ) {
			foreach ($positions as $pos) {
				if ( $pos['type'] == 'widget' ) {
					$selected = $position === $pos['position'] ? ' selected="selected"' : '';
					$pos_options[] = '<option value="' . $pos['position'] .'"' . $selected . '>' . $pos['position'] . '</option>';
				}
			}
		}
		?>
        
		<p>
			<label for="<?php echo $this->get_field_id( 'position' ); ?>"><?php _e( 'Select Position:' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'position' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'position' ); ?>">
				<?php echo implode( '', $pos_options ); ?>
			</select>
			<input type="hidden" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $position ?>" />
		</p>
            
		<?php 
	}
}

/**
***   administer_widget_control (  )
***
**/
function administer_widget_control() { }

/**
***   administer_popuplate_widget_controls (  )
***
**/
function administer_popuplate_widget_controls () { }

/**
***   administer_template_action ( )
***
***   The template action. Add action if it doesn't exist and
***   display the content of the position.
**/
function administer_template_action ($args) {

	if (!($post_id = get_option('administer_post_id'))) return 0;

	// It's OK only to pass the name of the position to be shown...
	$position = (!is_array($args)) ? $args : '';	
	
	$defaults = array('position' => $position, 'description' => '', 'before' => '', 'after' => '', 'type' => 'template');
	$args = wp_parse_args($args, $defaults);

	// Ignore empty calls
	if (!$args['position']) return '';

	$positions = get_post_meta($post_id, 'administer_positions', true);

	if (!is_array($positions)) {
			$positions = array();
			$edit_position = true;
	} else {
		if (array_key_exists($args['position'], $positions)) {
		 	$diff = array_diff($positions[$args['position']], $args);
		 	if (!empty($diff)) $edit_position = true;
			else $edit_position = false;
		}
		else $edit_position = true;
	}

	// If anything has changed, then update our database
	if ($edit_position) {
	
		$positions[$args['position']] = $args; 
		
		// Save to a Custom Field
		if (!add_post_meta($post_id, 'administer_positions', $positions, true)) 
				update_post_meta($post_id, 'administer_positions', $positions);		
	}

	administer_display_position($args['position']);
}

/*
**   administer_is_visible ( )
**
**   Determine wether or not content is visible.
*/
function administer_is_visible($ad) {

	// Is the option to show the content ticked.
	if ($ad['show'] == 'false') return false; 

	// Is the content schedueled to show?
	$valid = false;
	$ages = administer_content_age($ad['scheduele']);

	// Has the scheduele expired, or hasn't it started?
	foreach ($ages as $age) {
	
		// No scheduele, so content always valid
		if (!$age['start'] && !$age['end']) $valid = true;

		// Check that we're in the validity period
		if ($age['start'] <= 0 && $age['end'] > 0) $valid = true;
	}

	// Have we reached maximum impressions or clicks?
	if (get_option('administer_statistics') == 'true') {

		//$stats = get_post_meta(get_option('administer_post_id'), 'administer_stats', true);		
		//if (!is_array($stats)) $stats = array();
		$stats = administer_get_stats();
		
		if ($ad['impressions'])
			if ($stats[$ad['id']]['i'] >= $ad['impressions']) $valid = false;

		if ($ad['clicks'])
			if ($stats[$ad['id']]['c'] >= $ad['clicks']) $valid = false;
	}
	
	return $valid;
}

/*
**   administer_build_code ( )
**
**   Build advertisement code from stored information.
*/
function administer_build_code( $ad ) {
	if ( !( $ad['ad_mode'] == 'mode_basic' and $ad['ad_media_url'] ) ) return '';
	$media_url = $ad['ad_media_url'];
	list( $width, $height ) = explode( 'x', $ad['ad_size'] );
	$link_url = $ad['ad_link_url'];
	$audio_url = $ad['ad_audio_url'];
	$ad_hint = $ad['hint'];
	$ext = strtolower( pathinfo( $media_url, PATHINFO_EXTENSION ) );
	switch ( $ext ) {
		case 'jpg':
		case 'jpeg':
		case 'gif':
		case 'bmp':
		case 'png':
		case 'tif':
		case 'tiff':
			$code = "<img title='$hint' src='$media_url' width='$width' height='$height' />";
			break;
		case 'swf':
			$code = "[flashad src='$media_url' width='$width' height='$height']";
			break;
		default:
			$code = '';
	}
	if ( $link_url ) $code = "<a title='$hint' href='%tracker%$link_url' target='_blank'>$code</a>";
	if ( $audio_url ) $code .= "[esplayer url='$audio_url' width='$width' height='27']";
	return $code;
}

// Returns a random value from an array with a weighted bias
function array_rand_weighted( array $values, array $weights ) {
	if ( count( $values ) == 1 ) return array_shift( array_values( $values ) );
	$len_diff = count( $values ) - count( $weights );
	if ( $len_diff ) {
		if ( $len_diff > 0 ) 
			$weights = array_merge( $weights, array_fill( count( $weights ), $len_diff, 1 ) );
		else 
			array_splice( $weights, count( $values ) );
	}
	$n = rand( 1, array_sum( $weights ) );
	$weight_sum = 0;
	foreach ( $values as $key => $value ) {
		$weight_sum += $weight[$key] ? $weight[$key] : 1;
		if ( $n <= $weight_sum ) return $value;
	}
}

// Returns the parsed, expanded code for the given advertisement id
function administer_get_ad_code( $ad_id ) {
	if ( !( $post_id = get_option('administer_post_id') ) ) return '';
	$content = administer_get_content();
	if ( !is_array( $content ) || empty( $content ) ) return '';
	$ad = $content[$ad_id];
	
	// Get advertisement code
	if ( !( $ad['ad_mode'] ) ) {
		$ad['ad_mode'] = 'mode_advanced';
		$content[$ad_id] = $ad;
		administer_update_content( $content );
	}
	$code = ( $ad['ad_mode'] == 'mode_basic' ) ? administer_build_code( $ad ) : $ad['code'];
	
	// Strip html slashes and expand shortcodes
	$code = do_shortcode( stripslashes( $code ) );
	
	return $code;
}

/*
**   administer_display_position ( )
**
**   Show a position, randomize weighted content and log.
*/
function administer_display_position( $pos ) {

	// Display the content
	if ( !( $post_id = get_option('administer_post_id') ) ) return false;
	$content = get_post_meta( $post_id, 'administer_content', true );
	if ( !is_array( $content ) || empty( $content ) ) return false;
	
	$ad_ids = array();
	$ad_weights = array();
	foreach ( $content as $ad_id => $ad ) {
		if ( !( $ad['position'] == $pos and administer_is_visible( $ad ) ) ) continue;
		// Consider ad for display if its in this position and visible	
		$ad_ids[] = $ad_id;
		$ad_weights[] = $ad['weight'] ? $ad['weight'] : 1;
	}
	
	// Ensure that we have at least 1 ad to display
	if ( empty( $ad_ids ) ) return false;

	// Randomly select an ad taking weight into consideration
	$ad = $content[array_rand_weighted( $ad_ids, $ad_weights )];
	
	// Get advertisement code
	$code = administer_get_ad_code( $ad['id'] );
	
	// Replace click tracker placeholder
	if ( !( false === strpos( $code, '%tracker%' ) ) ) {
		if ( get_option( 'administer_statistics' ) == 'true' ) {
			$code = str_replace( '%tracker%', administer_tracker_url( $ad['id'] ), $code );
		} else { 
			$code = str_replace( '%tracker%', '', $code );
		}
	}	

	// Make amersands validate
	$code = preg_replace( '/&([^#])(?![a-zA-Z1-4]{1,8};)/', '&amp;$1', $code );	
	
	// Get content wrapper
	$positions = get_post_meta($post_id, 'administer_positions', true);
	$wrapper_before = $positions[$pos]['before'];	
	$wrapper_after = $positions[$pos]['after'];
	
	// Add id attribute to wrapper container
	$wrapper_id = " id='ad-{$ad['id']}'";
	$wrapper_start = '<div';
	if ( substr( $wrapper_before, 0, strlen( $wrapper_start ) ) === $wrapper_start ) {
		$wrapper_before = str_replace( $wrapper_start, $wrapper_start . $wrapper_id, $wrapper_before );
	}
		
	// Display the content code with optional wrapping.
	if ( $ad['wrap'] != 'false' ) { 
		echo $wrapper_before . $code . $wrapper_after;
	} 
	else {
		echo $code;
	}
	
	// Save the pageview
	if ( get_option('administer_statistics') == 'true' ) {
		administer_register_impression( $ad['id'] );

		global $current_user;
		get_currentuserinfo();
		$roles = array( 'administrator', 'editor', 'author', 'contributor' );
		foreach ( $roles as $role ) {
			if ( user_can( $current_user->ID, $role ) ) return true;
		}

		// Register click/view events through Google Analytics
		?>
		
		<script type="text/javascript">
		$('<?php echo "#ad-{$ad['id']}"; ?>').ready(function() {
			_gaq = _gaq || [];
			_gaq.push(['_trackEvent', 'Advertisements', 'View', '<?php echo $ad['title']; ?>']);
		});
		$('<?php echo "#ad-{$ad['id']} a"; ?>').click(function() {
			_gaq = _gaq || [];
			_gaq.push(['_trackEvent', 'Advertisements', 'Click', '<?php echo $ad['title']; ?>']);
		});
		</script>
		
		<?php				
	}
	return true;
}

/*
**	administer_register_impression ( )
**
**	Note that an impression was made.
*/
function administer_register_impression($id) {
	global $administer_stats;
	global $current_user;
	get_currentuserinfo();
	$roles = array( 'administrator', 'editor', 'author', 'contributor' );
	foreach ( $roles as $role ) {
		if ( user_can( $current_user->ID, $role ) ) return;
	}
	
	if (!isset($administer_stats[$id]['i'])) $administer_stats[$id]['i'] = 0;
	$administer_stats[$id]['i']++;
}

/*
**	administer_init_impressions ( )
**
**	Set up global stat variable.
*/
function administer_init_impressions() {
	global $administer_stats;
	$administer_stats = administer_get_stats();
}

/*
**	administer_register_click ( )
**
**	Note that an ad was clicked.
*/
function administer_register_click( $id ) {
	if ( is_admin() ) return;
	global $administer_stats;
	global $current_user;
	get_currentuserinfo();
 	$roles = array( 'administrator', 'editor', 'author', 'contributor' );
	foreach ( $roles as $role ) {
		if ( user_can( $current_user->ID, $role ) ) return;
	}
	
	if ( !isset( $administer_stats[$id]['c']) ) $administer_stats[$id]['c'] = 0;
	$administer_stats[$id]['c']++;
}

/*
**   administer_do_redirect ( )
**
**   Register clicks.
*/
function administer_do_redirect() {
 	global $administer_stats;
 	
	if ($qs = $_SERVER['REQUEST_URI']) {
		$pos = strpos($qs, 'administer_redirect');
		if ( !( false === $pos ) ) { 
			$link = substr( $qs, $pos );

			// Extract the ID and get the link
			$pattern = '/administer_redirect_(\d+?)\=/';
			preg_match( $pattern, $link, $matches );
			$id = $matches[1];
			$link = str_replace( 'administer_redirect_' . $id . '=', '', $link );
			if ( !( startsWith( $link, 'http://' ) || startsWith( $link, 'https://' ) ) ) {
				$link = "http://{$link}";
			}
			// Save click!
			if ( get_option( 'administer_statistics') == 'true' ) { 
				administer_register_click( $id );
				administer_update_stats( $administer_stats, __FILE__, __FUNCTION__, __LINE__ );
				//update_post_meta( get_option( 'administer_post_id'), 'administer_stats', $administer_stats );
			}

			// Redirect
			header( "HTTP/1.1 302 Temporary Redirect" );
			header( "Location:" . $link );			
			// I'm outta here!
			exit(1);
		}
	} 
}

/*
**  administer_save_impressions ( )
**
**  Save the clicks and impressions to db. I think there is an issue regarding the 
**	effectivness of storing this data in a Custom Field. In the future a separate db might be more
**	appropriate.
*/
function administer_save_impressions () {
	global $administer_stats;
	// Save to a Custom Field
	if (!is_admin()) {
		administer_update_stats( $administer_stats );
	}
}

/*
**  administer_save_stats ( )
**
**  Save the clicks and impressions to db.
*/
function administer_save_stats ( $stats, $filename = __LINE__, $function = __FUNCTION__, $line = __LINE__ ) {
	// Save to a Custom Field
	if ( !is_admin() ) { 
		if ( empty( $stats ) ) {
			administer_log_stats_reset( $filename, $function, $line );
		}
		delete_post_meta(get_option('administer_post_id'), 'administer_stats');
		update_post_meta(get_option('administer_post_id'), 'administer_stats', $stats);
	}
}

/*
**  administer_f ( )
**
**  Formatting wrapper function
*/
function administer_f($text) {
	return wptexturize(stripslashes($text));
}

/*
**  administer_sort_link ( )
**
**  Generate the link for the statistics table headers
*/
function administer_sort_link($link, $field, $sort, $order) {
	//$link = get_option('siteurl') . '/wp-admin/edit.php?page=' . dirname(plugin_basename (__FILE__)) . '';
	$link .= '&tab=statistics';
	if ($field != $sort) return false;
	$symbol = ($order == 'up') ? '&darr;' : '&uarr;';
	$linkorder = ($order == 'up') ? 'down' : 'up';
	$alt = ($order == 'up') ? __('Sort up', 'ad-minister') : __('Sort down', 'ad-minister');
	echo '<a class="sort" title="' . $alt. '" href="' . $link . ' &sort=' . $sort . '&order=' . $linkorder . '">' . $symbol . '</a>';
}

/*
**  administer_tracker_url ( )
**
**  Generate the tracker link
*/
function administer_tracker_url ($id) {
	return get_option('siteurl') . '/?administer_redirect_' . $id . '=';
}

/*
**  administer_stats ( )
**
**  Generate the statistics table, both for template use and in the admin.
*/
function administer_stats ($options = array()) {

	if (empty($options)) {
		$ids = array();
		$columns = array('selected', 'id', 'title', 'position', 'visible', 'time', 'impressions', 'clicks');
	} else {
		$ids = $options['ids'];
		$columns = $options['columns'];
	}
	
	// Check Bulk actions
	if ( $_POST['bulk_actions'] == 'delete' ) {
		$selected_ads = $_POST['selected_ads'] ? $_POST['selected_ads'] : array();
		$ad_count = count( $selected_ads );
		
		// Delete selected ad content
		foreach ( $selected_ads as $ad_id ) {
			administer_delete_ad( $ad_id );
		}
			
		// Notify 
		echo '<div id="message" class="updated fade"><p><strong>' . __( $ad_count . ( $ad_count == 1 ? ' Ad ' : ' Ads ' ) . 'Deleted.', 'ad-minister') . '</strong></p></div>';
	}
	
	$contents = administer_get_content();
	$positions = get_post_meta(get_option('administer_post_id'), 'administer_positions', true);
	$stats = administer_get_stats();
	
	if (is_admin()) 
		$link = administer_get_page_url();
	else 
		$link = get_page_link() . '?administer=view';
	
	$link = administer_get_page_url( "create" );	
	$table = array();
	foreach ( array_keys( $contents ) as $i ) {

		$ad = $contents[$i];

		if ( !empty( $ids ) && !in_array( $ad['id'], $ids ) ) continue;
	
		$table['title'][$i] = administer_f($ad['title']);
		$table['title_link'][$i] = $link . '&action=edit&id=' . $ad['id'];
		$table['position'][$i] = ($pos = administer_f($ad['position'])) ? $pos : '-';

		// Check visibility
		$is_visible = administer_is_visible($ad);

		// Set orphaned content as invisible
		if ($table['position'][$i] == '-') $is_visible = false;

		// Get the time left based on schedule, if present
		$ages = administer_content_age($ad['scheduele']);
		$time = '-';
		$time_left = -1;
		foreach ($ages as $age) {
			if ( $age['start'] == $age['end'] ) continue;
			
			if ( $age['start'] > 0 ) {
				$time_left = $age['start'];
				$time = __('Starts in', 'ad-minister') . ' ' . sprintf( '%.1f', $time_left ) . ' ' . __('days', 'ad-minister');
			}
			else {
				$time_left = $age['end'];
				$time = __('Ends in', 'ad-minister') . ' ' . sprintf( '%.1f', $time_left ) . ' ' . __('days', 'ad-minister');
			}
			
			if ( $age['end'] >= 0 ) break;

			$time = 'Ended';
		}
	
		// Calculate and format the fractional weight, given as a percentage
		$total_weight = 0;
		foreach ($contents as $content)
			if ($ad['position'] == $content['position']) 
				if (administer_is_visible($content))
					$total_weight += ($content['weight']) ? $content['weight'] : 1;		
		$weight = ($ad['weight']) ? $ad['weight'] : 1;
		$weight = (administer_is_visible($ad)) ? 100*$weight/$total_weight : '';
		$table['weight'][$i] = ($weight > 0 && $weight < 100) ? '(' . round($weight, 1) . '%)' : '';
		
		// Don't show percentages for orphans
		if ($table['position'][$i] == '-') $table['weight'][$i] = '';

		// Format impressions
		$impressions = ($stats[$ad['id']]['i']) ? $stats[$ad['id']]['i'] : '0';
		$impressions = ($ad['impressions']) ? $impressions . ' of ' . $ad['impressions'] : $impressions;

		// Format clicks
		$clicks = ($stats[$ad['id']]['c']) ? $stats[$ad['id']]['c'] : '0';
		$clicks = ($ad['clicks']) ? $clicks . ' of ' . $ad['clicks'] : $clicks;

		$table['clicks'][$i]      = $clicks;
		$table['impressions'][$i] = $impressions;
		$table['time'][$i]        = $time;
		$table['visible'][$i]     = ($is_visible) ? __('Yes', 'ad-minister') : __('No', 'ad-minister');
		$table['id'][$i] = $ad['id'];
		$table['row-class'][$i]   = ($is_visible) ? 'ad-visible' : 'ad-invisible';
		
		if ( $time_left > 0 ) {
			if ( $is_visible ) {
				$expiring_period = (float) get_option( 'administer_dashboard_period', 7 );
				if ( $time_left <= $expiring_period ) {
					$table['row-class'][$i] .= ' ad-expiring';
				}
				if ( $time_left <= 2 ) {
					$table['row-class'][$i] .= ' ad-almost-expired';
				}
			}
			else {
				$table['row-class'][$i] .= ' ad-in-transit';
			}
		}
	}

	// Do the sorting, only save sort column if we're in the admin
	$saved_sort = (is_admin()) ? get_option('administer_sort_key') : '';
	if (!($sort = $_GET['sort'])) $sort = ($saved_sort) ? $saved_sort : 'position';
	if ($sort != $saved_sort && is_admin()) update_option('administer_sort_key', $sort);
	$order = $_GET['order'];
	$arr = $table[$sort];
	if (!is_array($arr)) {
		echo '<p><strong>' . __('No data available', 'ad-minister') . '.</strong></p>';
		return 0;
	}
	natcasesort($arr);

	$arr_keys = array_keys($arr);
	if ($order == 'down') $arr_keys = array_reverse($arr_keys);
	$link = administer_get_page_url(); 
	?>
	<form id="form_bulk" name="form_bulk" method="POST" action="<?php echo $link; ?>">
		<div style="margin-bottom:4px;">
			<select id="bulk_actions" name="bulk_actions">
				<option value="">Bulk Actions</option>
				<option value="delete">Delete</option>
			</select>
			<input class="button" type="submit" id="apply_button" name="apply_button" value="Apply" />
		</div>
		<table class="widefat">
		<thead>
			<tr>
				<?php if (in_array('selected', $columns)) : ?>
					<th><input class='staddt_selected' type="checkbox" id="select_all" name="select_all" /></th>
				<?php endif; ?>
				<?php if (in_array('id', $columns)) : ?>
					<th><a class="sort" href="<?php echo $link; ?>&sort=id&order=up"><?php _e('ID', 'ad-minister'); ?></a> <?php administer_sort_link($link, 'id', $sort, $order); ?></th>
				<?php endif; ?>
				<?php if (in_array('title', $columns)) : ?>
					<th><a class="sort" href="<?php echo $link; ?>&sort=title&order=up"><?php _e('Content title', 'ad-minister'); ?></a> <?php administer_sort_link($link, 'title', $sort, $order); ?></th>
				<?php endif; ?>
				<?php if (in_array('position', $columns)) : ?>
					<th><a class="sort" href="<?php echo $link; ?>&sort=position&order=up"><?php _e('Position', 'ad-minister'); ?></a> <?php administer_sort_link($link, 'position', $sort, $order); ?></th>
				<?php endif; ?>
				<?php if (in_array('visible', $columns)) : ?>
					<th><a class="sort" href="<?php echo $link; ?>&sort=visible&order=up"><?php _e('Visible', 'ad-minister'); ?></a> <?php administer_sort_link($link, 'visible', $sort, $order); ?></th>	
				<?php endif; ?>
				<?php if (in_array('time', $columns)) : ?>
					<th><a class="sort" href="<?php echo $link; ?>&sort=time&order=up"><?php _e('Time left', 'ad-minister'); ?></a> <?php administer_sort_link($link, 'time', $sort, $order); ?></th>
				<?php endif; ?>
				<?php if (in_array('impressions', $columns)) : ?>
					<th><a class="sort" href="<?php echo $link; ?>&sort=impressions&order=up"><?php _e('Impressions', 'ad-minister'); ?></a> <?php administer_sort_link($link, 'impressions', $sort, $order); ?></th>
				<?php endif; ?>
				<?php if (in_array('clicks', $columns)) : ?>
					<th><a class="sort" href="<?php echo $link; ?>&sort=clicks&order=up"><?php _e('Clicks', 'ad-minister'); ?></a> <?php administer_sort_link($link, 'clicks', $sort, $order); ?></th>
				<?php endif; ?>
			</tr>
		</thead>

		<?php 
		$rownbr = 0;
		foreach ( $arr_keys as $i ) {
			$class = ( $rownbr++ % 2 ) ? $table['row-class'][$i] : $table['row-class'][$i] . ' alternate'; 
		?>
			<tr class="<?php echo $class; ?>">
				<?php if (in_array('selected', ($columns))) : ?>
					<td class='staddt_selected'><input style="margin-left: 8px;" type="checkbox" name="selected_ads[]" value="<?php echo $table['id'][$i]; ?>" /></td>
				<?php endif; ?>

				<?php if (in_array('id', ($columns))) : ?>
					<td class='staddt_id'><strong><?php echo $table['id'][$i]; ?></strong></td>
				<?php endif; ?>
				<?php if (in_array('title', ($columns))) : ?>
					<td class='stat_title'>
						<?php if (is_admin()) : ?><a href="<?php echo $table['title_link'][$i]; ?>"><?php endif; ?><?php echo $table['title'][$i]; ?><?php if (is_admin()) : ?></a><?php endif; ?>	
					</td>
				<?php endif; ?>
				<?php if (in_array('position', ($columns))) : ?>
					<td class='stat_position'><?php echo $table['position'][$i]; ?> <?php echo $table['weight'][$i]; ?></td>
				<?php endif; ?>
				<?php if (in_array('visible', ($columns))) : ?>
					<td class='stat_visible'><?php echo $table['visible'][$i]; ?></td>
				<?php endif; ?>
				<?php if (in_array('time', ($columns))) : ?>
					<td class='stat_time'><?php echo $table['time'][$i]; ?></td>
				<?php endif; ?>
				<?php if (in_array('impressions', ($columns))) : ?>
					<td class='stat_impressions'><?php echo $table['impressions'][$i]; ?></td>
				<?php endif; ?>
				<?php if (in_array('clicks', ($columns))) : ?>
					<td class='stat_clicks'><?php echo $table['clicks'][$i]; ?></td>
				<?php endif; ?>
			</tr>
		<?php
		} 
		?>
		</table>
	</form>
	<?php
}

/*
**	administer_template_stats
**
*/
function administer_template_stats ($options = array()) {
	administer_stats($options);
}

/*
** administer_log_stats_reset
**
** Logs whenever the tracking statistics (impressions and clicks) of the ad-minister plugin are reset.
*/ 
function administer_log_stats_reset( $filename, $function, $line ) {
	$message = "Statistics reset in filename '{$filename}', by function '{$function}', on line {$line}\n";
	$timestamp = date( 'm/d/Y H:i:s' );
	$log_file = dirname( __FILE__ ) . '/ad-minister.log';
	//error_log( '[' . $timestamp . '] INFO: ' . $message . PHP_EOL, 3, $log_file );
	$fh = fopen( $log_file, 'ab' );
	fwrite( $fh, $message );
	fclose( $fh );
}

function administer_get_stats() {
	$stats = get_post_meta( get_option('administer_post_id'), 'administer_stats', true );
	return is_array( $stats ) ? $stats : array();
}

function administer_update_stats( $stats ) {
	if ( function_exists( 'array_replace' ) ) {
		$old_stats = administer_get_stats();
		$stats = array_replace( $old_stats, $stats );	
	}
	administer_save_stats( $stats );
}

function administer_default_editor_to_html ($type) {
	global $page_hook;
	if (strpos($page_hook, 'ad-minister'))
		$type = 'html';
	return $type;
}
add_filter('wp_default_editor', 'administer_default_editor_to_html');

/**
 * Add SWF width and height fields to media uploader
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */
function administer_attachment_fields_to_edit( $form_fields, $post ) {
	if ( !( $_GET['ad-minister']  ) ) return $form_fields; 
	
	$attachment = get_post($post->ID); // fetching attachment by $id passed through
	$mime_type = $attachment->post_mime_type; //getting the mime-type
	
	if ( $mime_type == "application/x-shockwave-flash" ) {	
		$dimensions = get_post_meta( $post->ID, 'ad-minister-flash-ad-dimensions', true );
		if ( empty( $dimensions ) ) $dimensions = '306x300'; // default dimensions
		$dim_options = array( '306x140', '306x300', '642x140' );
		$html = "";
		for ( $i = 0; $i < count( $dim_options ); ++$i ) {
			$html .= "<input type='radio' name='attachments[{$post->ID}][ad-minister-flash-ad-dimensions]' value='{$dim_options[$i]}' "; 
			if ( $dim_options[$i] == $dimensions ) {
				$html .= "checked ";
			}
			$dim_text = explode( 'x', $dim_options[$i] );
			$html .= "/> {$dim_text[0]} x {$dim_text[1]} pixels<br />";
		}
		$form_fields['ad-minister-flash-ad-dimensions'] = array(
			'label' => 'Dimensions',
			'input' => 'html',
			'html' => $html,
			'helps' => 'The width and height of the flash advertisement',
		);
	}
	
	return $form_fields;
}
add_filter( 'attachment_fields_to_edit', 'administer_attachment_fields_to_edit', 20, 2 );

/**
 * Save values of Flash Ad height and width in media uploader
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */
function administer_attachment_fields_to_save( $post, $attachment ) {	
	if( isset( $attachment['ad-minister-flash-ad-dimensions'] ) )
		update_post_meta( $post['ID'], 'ad-minister-flash-ad-dimensions', $attachment['ad-minister-flash-ad-dimensions'] );
	return $post;
}
add_filter( 'attachment_fields_to_save', 'administer_attachment_fields_to_save', 20, 2 );


// Create shortcode [flashad]
function flashad_func( $atts ) {
	extract( shortcode_atts( array(
		'src' => '',
		'width' => get_post_meta( $post->ID, 'ad-minister-flash-ad-width', true ),
		'height' => get_post_meta( $post->ID, 'ad-minister-flash-ad-height', true )
	), $atts ) );

	$html = '<!-- [flashad: invalid shortcode attributes specified] -->';
	if ( !empty( $src ) ) {
		$extension = strrchr( $src, '.' ); 
		if ( $extension == '.swf' ) {
			$html = "<object width='$width' height='$height' classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0'><param name='quality' value='high' /><param name='src' value='$src' /><param name='pluginspage' value='http://www.macromedia.com/go/getflashplayer' /><param name='wmode' value='transparent' /><embed width='$width' height='$height' type='application/x-shockwave-flash' src='$src' quality='high' pluginspage='http://www.macromedia.com/go/getflashplayer' wmode='transparent' /></object>";
		}
	}
	return $html;
}
add_shortcode( 'flashad', 'flashad_func' );

// Customize media uploader html for Ad-minister
function administer_send_to_editor($html, $id, $attachment_info) {	
	$attachment = get_post($id); //fetching attachment by $id passed through
	$mime_type = $attachment->post_mime_type; //getting the mime-type
	$src = wp_get_attachment_url( $id );
	switch ( $mime_type ) {
		// Flash shockwave
		case 'application/x-shockwave-flash':
			$html = do_shortcode( "[flashad src='$src' width='306' height='300']" );
			break;
	}
	return $html;
}
add_filter( 'media_send_to_editor', 'administer_send_to_editor', 20, 3 );

// Deletes the specified advertisement from Ad-minister
function administer_delete_ad( $id ) {
	
	// Delete ad content
	$content = administer_get_content();
	unset( $content[$id] );
	administer_update_content( $content );
	
	// Delete ad statistics
	$stats = administer_get_stats();
	unset( $stats[$id] ); 
	administer_update_stats( $stats );	
}

// Returns an array containg all Ad-minister ad content
function administer_get_content() {
	$content = get_post_meta( get_option( 'administer_post_id' ), 'administer_content', true );
	return is_array( $content ) ? $content : array();
}

// Updates the Ad-minister ad content with the given content
function administer_update_content( $content ) {
	update_post_meta( get_option( 'administer_post_id' ), 'administer_content', $content );
}

function administer_get_page_url( $page = '' ) {
	if ( empty( $page ) )
		$page = 'ad-minister';
	else if ( strpos( $page, 'ad-minister-') === false )
		$page = 'ad-minister-' . $page;
	return get_option('siteurl') . "/wp-admin/admin.php?page=$page";
}

function administer_show_ad( $ad_id ) {
	$content = administer_get_content();
	$content[$ad_id]['show'] = 'true';
	administer_update_content( $content ); 
}

function administer_hide_ad( $ad_id ) {
	$content = administer_get_content();
	$content[$ad_id]['show'] = 'false';
	administer_update_content( $content ); 
}

function administer_replace_thickbox_text($translated_text, $text, $domain) { 
    if ('Insert into Post' == $text) { 
        $referer = strpos( wp_get_referer(), 'ad-minister' ); 
        if ( $referer !== FALSE ) { 
            return __('Select', 'ad-minister' );  
        }  
    }  
    return $translated_text;  
}
function administer_media_upload_setup() {  
    global $pagenow;  
    if ( 'media-upload.php' == $pagenow || 'async-upload.php' == $pagenow ) {  
        // Now we'll replace the 'Insert into Post Button' inside Thickbox  
        add_filter( 'gettext', 'administer_replace_thickbox_text'  , 1, 3 ); 
    } 
} 
add_action( 'admin_init', 'administer_media_upload_setup' );
?>