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
function administer_position_select ( $ad_positions = array() ) {
	// Convert ad_positions to array
	if ( !is_array( $ad_positions ) ) {
		$ad_positions = ( !$ad_positions || $ad_positions == '-') ?	array() : array( $ad_positions );
	}
	$html = '<select multiple="multiple" name="position[]" id="ad_position">';
	$positions = get_post_meta(get_option('administer_post_id'), 'administer_positions', true);	
	if (!is_array($positions)) $positions = array();
	$position_keys = array_keys($positions); 
	sort($position_keys);	
	foreach ($position_keys as $key) {
		$selected = ( in_array( $key, $ad_positions ) ) ? ' selected="selected"' : '';
		$description = ($positions[$key]['description']) ? ' (' . $positions[$key]['description'] . ')' : '';
		$html .= '<option value="' . $positions[$key]['position'] . '"' . $selected .'> ' . $positions[$key]['position'] . $description . '</option>';
	}
	$html .= '</select>';
	return $html;
}

/*
**   administer_get_available_id()
**
**   Finds the highest number from one that is not currently
**   some content.
*/
function administer_get_available_id() {
	$content = get_post_meta(get_option('administer_post_id'), 'administer_content', true);

	if (!is_array($content)) return 1;
	if (empty($content)) return 1;

	// Store the ids in a separate array
	$ids = array_keys($content);
	sort($ids);

	// Get the smallest unpopulated id
	$id = 1;
	for ($i = 0; $i < count($ids); ++$i) {
		if ($id != $ids[$i]) return strval($id);
		++$id;
	}
	
	return strval($id);
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
		
		$age[] = array( 
		'start' => ( $start - $now ), 
		'end' => ( $end - $now ) 
		);

	}

	return $age;
}

function administer_get_time_left_string( $time_left ) {
	if ( $time_left === FALSE ) return '-';
	if ( $time_left == 0 ) return 'Ended';
	
	$day_in_secs = 86400; // 86400 seconds in a day
	$hour_in_secs = 3600; // 3600 seconds in an hour
	$units = __( 'days', 'ad-minister' );
	
	if ( abs( $time_left ) <= $day_in_secs ) {
		$units = __( 'hours', 'ad-minister' );
		$time_left /= $hour_in_secs;
	}
	else {
		$time_left /= $day_in_secs;
	}
	
	if ( $time_left < 0 )
		return __( 'Starts in', 'ad-minister' ) . ' ' . sprintf( '%.1f', abs( $time_left ) ) . ' ' . $units;
	else
		return __( 'Ends in', 'ad-minister' ) . ' ' . sprintf( '%.1f', $time_left ) . ' ' . $units;
}

function administer_get_time_left( $ad_schedule ) {
	if ( empty( $ad_schedule ) ) return FALSE;
	
	// Get the time left based on schedule
	$time_left = 0;
	$ages = administer_content_age( $ad_schedule );
	foreach ( $ages as $age ) {
		if ( $age['start'] == $age['end'] ) continue;
		
		if ( $age['start'] > 0 ) {
			$time_left = -( $age['start'] );
			return $time_left;
		}
		else if ( $age['end'] > 0 ) {
			$time_left = $age['end'];
			return $time_left;
		}
	}
	return $time_left;	
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
	$period = get_option('administer_dashboard_period') * 86400;

	$li_time_left = '';
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
		$time_left = administer_get_time_left( $con['scheduele'] );
		$time_left_string = administer_get_time_left_string( $time_left );
		if ( $time_left ) {
			$link_url .= $url . '&tab=upload&action=edit&id=' . $con['id'];
			$link_style = $time_left < 0 ? 'style="color: #00AA00;"' : 'style="color: #AA0000;"';
			$li_time_left .= '<li><a ' . $link_style . ' href="' . $link_url . '">' . $con['title'] . '</a> - ' . $time_left_string . '</li>';
		}
	}

	// Display dashboard widget 
	if ( $li_time_left || $li_impressions || $li_clicks ) {
		echo '<p><ul>';
		echo $li_impressions;
		echo $li_clicks;
		echo $li_time_left;
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
	
	// Does ad have a schedule or has the schedule expired
	$time_left = administer_get_time_left( $ad['scheduele'] );
	if ( ( $time_left === FALSE ) || ( $time_left > 0 ) ) $valid = true;

	// Have we reached maximum impressions or clicks?
	if (get_option('administer_statistics') == 'true') {
		if ( $ad['impressions'] )
			if ( administer_get_impressions( $ad['id'] ) >= $ad['impressions'] ) $valid = false;

		if ( $ad['clicks'] )
			if ( administer_get_clicks( $ad['id'] ) >= $ad['clicks'] ) $valid = false;
	}
	
	return $valid;
}

/*
**   administer_get_code_html ( )
**
**   Returns advertisement html code from supplied arguments.
*/
//function administer_get_code_html( $args )

/*
**   administer_build_code ( )
**
**   Build advertisement code from stored information.
*/
function administer_build_code( $ad ) {
	if ( !( $ad['ad_mode'] == 'mode_basic' and $ad['ad_media_url'] ) ) return '';
	
	$media_url = $ad['ad_media_url'];
	
	if ( !$ad['ad_size'] ) {
		list( $width, $height ) = getimagesize( $media_url );
		$width = $width == 0 ? '' : $width;
		$height = $height == 0 ? '' : $height;
	}
	else {
		list( $width, $height ) = explode( 'x', $ad['ad_size'] );
	}
	
	$link_url = trim( $ad['ad_link_url'] );
	if ( $link_url ) {
		$link_url = '%tracker%' . urlencode( str_replace( '%tracker%', '', $link_url ) );
	}
	
	$audio_url = $ad['ad_audio_url'];
	
	$ad_hint = $ad['ad_hint'];
	
	$ext = strtolower( pathinfo( $media_url, PATHINFO_EXTENSION ) );
	switch ( $ext ) {
		case 'jpg':
		case 'jpeg':
		case 'gif':
		case 'bmp':
		case 'png':
		case 'tif':
		case 'tiff':
			$code = "<img title='$ad_hint' src='$media_url' width='$width' height='$height' />";
			$code = $link_url ? "<a title='$ad_hint' href='$link_url' target='_blank'>$code</a>" : $code;
			break;
		
		case 'swf':
		case 'flv':
			$code = do_shortcode( "[flashad id={$ad['id']} src='$media_url' width='$width' height='$height']" );
			$code = !empty( $link_url ) ? $code . "<a title='$ad_hint' href='$link_url' target='_blank' class='flash-banner-link'></a>" : $code;
			break;
		
		default:
			$code = '';
	}
	
	if ( $audio_url ) $code .= "[esplayer url='$audio_url' width='$width' height='27']";
	
	return $code;
}

function administer_build_code_callback() {
	//global $wpdb; // this is how you get access to the database

	$ad = array(
		'ad_mode' => 'mode_basic',
		'ad_media_url' => $_POST['ad_media_url'],
		'ad_size' => $_POST['ad_size'],
		'ad_hint' => $_POST['ad_hint'],
		'ad_link_url' => $_POST['ad_link_url'],
		'ad_audio_url' => $_POST['ad_audio_url']
	);
	
	echo administer_build_code( $ad );
	
	die(); // this is required to return a proper result
}
add_action('wp_ajax_administer_build_code', 'administer_build_code_callback');

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
		$ad['position'] = !is_array( $ad['position'] ) ? array( $ad['position'] ) : $ad['position']; 
		if ( !( in_array( $pos, $ad['position'] ) and administer_is_visible( $ad ) ) ) continue;
		
		// Consider ad for display if its in this position and visible	
		$ad_ids[] = $ad_id;
		$ad_weights[] = $ad['weight'] ? $ad['weight'] : 1;
	}
	
	// Ensure that we have at least 1 ad to display
	if ( empty( $ad_ids ) ) return false;

	if ( isset( $_SESSION['views'] ) and isset( $_SESSION['administer_key'] ) ) {
		// Use session info if available to select ad to display
		for ( $i = 0; $i < count( $ad_ids ); ++$i )
			for ( $j = 0; $j < $ad_weights[$i]; ++$j )
				$temp_ids[] = $ad_ids[$i];
		sort( $temp_ids );
		$i = ( $_SESSION['administer_key'] + $_SESSION['views'] ) % count( $ad_ids );
		$ad = $content[$temp_ids[$i]]; 
	}
	else {
		// Randomly select an ad taking weight into consideration
		$ad = $content[array_rand_weighted( $ad_ids, $ad_weights )];
	}
	
	// Get advertisement code
	$code = administer_get_ad_code( $ad['id'] );
	
	// Replace click tracker placeholder
	if ( false !== strpos( $code, '%tracker%' ) ) {
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
	}
	
	// Register click/view events through Google Analytics
	?>
	
	<script type="text/javascript">
	jQuery('#ad-<?php echo "{$ad['id']}"; ?>').ready(function() {
		var _gaq = _gaq || [];
		_gaq.push(['_trackEvent', 'Advertisements', 'View', '<?php echo $ad['title']; ?>']);
	});
	jQuery('#ad-<?php echo "{$ad['id']} a"; ?>').click(function() {
		var _gaq = _gaq || [];
		_gaq.push(['_trackEvent', 'Advertisements', 'Click', '<?php echo $ad['title']; ?>']);
	});
	</script>
	
	<?php

	return true;
}

/*
**	administer_register_impression ( )
**
**	Note that an impression was made.
*/
function administer_register_impression($id) {
	if ( is_admin() ) return;
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
**	administer_init_stats ( )
**
**	Set up global stat variable.
*/
function administer_init_stats() {
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
	if ( $qs = $_SERVER['REQUEST_URI'] ) {
		$pos = strpos( $qs, 'administer_redirect' );
		if ( !( false === $pos ) ) { 
			$link = substr( $qs, $pos );

			// Extract the ID and get the link
			$pattern = '/administer_redirect_(\d+?)\=/';
			preg_match( $pattern, $link, $matches );
			$id = $matches[1];
			$link = urldecode( str_replace( 'administer_redirect_' . $id . '=', '', $link ) );
			if ( !( startsWith( $link, 'http://' ) || 
				    startsWith( $link, 'https://' )  || 
					startsWith( $link, 'mailto:' ) ) ) {
				$link = "http://{$link}";
			}
			
			// Save click
			if ( get_option( 'administer_statistics') == 'true' ) { 
				administer_register_click( $id );
				administer_save_stats();
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
**  administer_save_stats ( )
**
**  Save the clicks and impressions to db. I think there is an issue regarding the 
**	effectivness of storing this data in a Custom Field. In the future a separate db might be more
**	appropriate.
*/
function administer_save_stats( $stats = NULL ) {
	if ( is_admin() ) return;
	if ( !$stats ) $stats = administer_get_stats(); 
	administer_set_stats( $stats, __FILE__, __FUNCTION__, __LINE__ );
}

/*
**  administer_set_stats ( )
**
**  Save the clicks and impressions to db.
*/
function administer_set_stats ( $stats, $filename = __FILE__, $function = __FUNCTION__, $line = __LINE__ ) {
	// Save to a Custom Field
	if ( empty( $stats ) ) {
		administer_log_stats_reset( $filename, $function, $line );
	}

	global $administer_stats;	
	$administer_stats = $stats;
	update_post_meta( get_option( 'administer_post_id' ), 'administer_stats', $administer_stats );
}

/*
**  administer_f ( )
**
**  Formatting wrapper function
*/
function administer_f($text) {
	//return wptexturize(stripslashes($text));
	return stripslashes($text);
}

/*
**  administer_sort_link ( )
**
**  Generate the link for the statistics table headers
*/
function administer_sort_link($link, $field, $sort, $order, $caption = '' ) {
	//$link = get_option('siteurl') . '/wp-admin/edit.php?page=' . dirname(plugin_basename (__FILE__)) . '';
	//$link .= '&tab=statistics';
	if ( empty( $caption ) ) $caption = $field;
	
	if ($field == $sort) {
		$symbol = ($order == 'up') ? ' &uarr;' : ' &darr;';
		$linkorder = ($order == 'up') ? 'down' : 'up';
		$alt = ($order == 'up') ? __('Sort descending', 'ad-minister') : __('Sort ascending', 'ad-minister');
	}
	else {
		$symbol = '';
		$linkorder = 'up';
		$alt = __( "Sort by {$caption}", 'ad-minister');
	}
	echo "<a class='sort' title='{$alt}' href='{$link}&sort={$field}&order={$linkorder}'>{$caption}{$symbol}</a>";
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
	$timestamp = date( "Y-m-d H:i:s", time() - ( 5 * 3600 ) );
	$message = "[$timestamp] INFO: Statistics reset in filename '{$filename}', by function '{$function}', on line {$line}" . PHP_EOL;	
	$log_file = dirname( __FILE__ ) . '/ad-minister.log';
	
	// Write to log file
	//error_log( '[' . $timestamp . '] INFO: ' . $message . PHP_EOL, 3, $log_file );
	$fh = fopen( $log_file, 'ab' );
	fwrite( $fh, $message );
	fclose( $fh );
	
	// Email log message
	$headers[] = 'From: Ad-minister <duravisioninc@gmail.com>';
	$to = "jan.durand@gmail.com";
	$subject = "Ad-minister Statistics Reset";
	@wp_mail( $to, $subject, $message, $headers );
}

function administer_get_stats( $id = NULL ) {
	global $administer_stats;
	if ( !isset( $administer_stats ) || empty( $administer_stats ) ) {
		$administer_stats = get_post_meta( get_option( 'administer_post_id' ), 'administer_stats', true );
		if ( !is_array( $administer_stats ) ) {
			$administer_stats = array();
		}
	}
	if ( isset( $id ) && !empty( $administer_stats ) )
		return $administer_stats[$id];
	else
		return $administer_stats;
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
		'id' => rand(),
		'src' => '',
		'linkUrl' => '',
		'width' => '', //get_post_meta( $post->ID, 'ad-minister-flash-ad-width', true ),
		'height' => '' //get_post_meta( $post->ID, 'ad-minister-flash-ad-height', true )
	), $atts ) );
	
	$width = $width ? $width . 'px' : '100%';
	$height = $height ? $height . 'px' : '100%';
	
	$html = '<!-- [flashad: invalid shortcode attributes specified] -->';
	if ( !empty( $src ) ) {
		$ext = strtolower( pathinfo( $src, PATHINFO_EXTENSION ) );
 		switch ( $ext ) {
			case 'swf':	
				$html = "<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' width='$width' height='$height'><param name='movie' value='$src' /><param name='wmode' value='transparent' /><!--[if !IE]>--><object type='application/x-shockwave-flash' data='$src' width='$width' height='$height'><param name='wmode' value='transparent' /><!--<![endif]--><p>Flash Content Unavailable</p><!--[if !IE]>--></object><!--<![endif]--></object>";
				break;
				
			case 'flv':
				$html = "<div id='flvplayer$id' style='width:{$width};height:{$height}'></div><script language='JavaScript'>flowplayer('flvplayer$id', '/flowplayer/flowplayer-3.2.16.swf', { clip: { url: '$src', autoPlay: true, autoBuffering: true, linkUrl: '$linkUrl', linkWindow: '_blank' }, plugins: { controls: null }, buffering: false, onFinish: function() { this.stop(); this.play(); }, onBeforePause: function() { return false; } });</script>";
				break;
		}
	}
	return $html;
}
add_shortcode( 'flashad', 'flashad_func' );

// Customize media uploader html for Ad-minister
/*function administer_send_to_editor($html, $id, $attachment_info) {	
	$id = $_POST['attachment']['id'];
	$attachment = get_post( $id ); // fetching attachment by $id passed through
	$mime_type = $attachment->post_mime_type; // getting the mime-type
	$src = wp_get_attachment_url( $id ); 
	$ext = strtolower( pathinfo( $src, PATHINFO_EXTENSION ) );

	// Flash Shockwave
	if ( $mime_type = 'application/x-shockwave-flash' ) { // or in_array( $ext, array( 'swf', 'flv' ) ) ) {
		$html = do_shortcode( "[flashad src='$src' width='' height='']" );
	}
	
	return $html;
}*/
//add_filter( 'media_send_to_editor', 'administer_send_to_editor', 20, 3 );

// Deletes the specified advertisement from Ad-minister
function administer_delete_ad( $id ) {
	if ( is_null( $id ) ) exit;
	
	// Delete ad content
	$content = administer_get_content();
	unset( $content[$id] );
	administer_update_content( $content );
	
	// Delete ad statistics
	administer_reset_stats( $id );
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
	else if ( strpos( $page, 'ad-minister-' ) === false )
		$page = 'ad-minister-' . $page;
	return get_option( 'siteurl' ) . "/wp-admin/admin.php?page=$page";
}

function administer_reset_stats( $id = NULL ) {
	if ( is_null( $id ) ) {
		$stats = array();
		administer_set_stats( $stats, __FILE__, __FUNCTION__, __LINE__ );
	}
	else {
		$stats = administer_get_stats();
		unset( $stats[$id] );
		administer_set_stats( $stats, __FILE__, __FUNCTION__, __LINE__ );
	}	
}

function administer_reset_impressions( $id ) {
	$stats = administer_get_stats();
	unset( $stats[$id]['i'] );
	administer_set_stats( $stats, __FILE__, __FUNCTION__, __LINE__ );
}

function administer_reset_clicks( $id ) {
	$stats = administer_get_stats();
	unset( $stats[$id]['c'] );
	administer_set_stats( $stats, __FILE__, __FUNCTION__, __LINE__ );
}

function administer_get_impressions( $id ) {
	$stats = administer_get_stats( $id );
	return $impressions = isset( $stats['i'] ) ? $stats['i'] : 0;
}

function administer_get_clicks( $id ) {
	$stats = administer_get_stats( $id );
	return $impressions = isset( $stats['c'] ) ? $stats['c'] : 0;
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
        add_filter( 'gettext', 'administer_replace_thickbox_text', 1, 3 ); 
    } 
} 
add_action( 'admin_init', 'administer_media_upload_setup' );
?>