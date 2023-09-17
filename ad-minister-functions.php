<?php
/*
**    administer_main ( )
**
**    Main Ad-minster Admin
*/
function administer_main( $page ) {
	global $submenu;
	
	if ( empty( $page ) ) 
		$page = 'ad-minister-content';
	else if ( strpos( $page, 'ad-minister' ) === false ) 
		$page = 'ad-minister-' . $page;
	
	// Check that our statistics are set up
	$stats = administer_get_stats();
	$content = administer_get_content();
	$positions = administer_get_positions();

	$menu_items = isset( $submenu['ad-minister'] ) ? $submenu['ad-minister'] : array();
	if ( !empty( $menu_items ) ) {
		echo "<ul class='tabs'>";
		foreach ( $menu_items as $item ) {
			// 0 = name, 1 = capability, 2 = slug
			$menu_slug = $item[2];
			if ( in_array( $menu_slug, array( 'ad-minister-banner' ) ) ) continue;
			
			$menu_name = $item[0];
			$menu_page_url = menu_page_url( $menu_slug, false );
			
			if ( $menu_slug == 'ad-minister' ) {
				$menu_slug = 'ad-minister-content';
				$menu_name = 'Banners';
			}
			
			$class = ( $menu_slug == $page ) ? 'tabs-current' : '';
			echo "<li><a class='{$class}' href='{$menu_page_url}'>{$menu_name}</a></li>";
		}
		echo "</ul>";
	}

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
	$positions = administer_get_positions();
	$position_keys = array_keys($positions); 
	sort($position_keys);	
	foreach ($position_keys as $key) {
		$selected = ( in_array( $key, $ad_positions ) ) ? ' selected="selected"' : '';
		$description =  ( isset( $positions[$key]['description'] ) && ( $positions[$key]['description'] ) ) ? ' (' . $positions[$key]['description'] . ')' : '';
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
	if ( ! ( $content = administer_get_content() ) ) return 1;
	
	// Store the ids in a separate array
	$ids = array_keys( $content );
	sort( $ids );

	// Get the smallest unpopulated id
	$id = 1;
	for ($i = 0; $i < count($ids); ++$i) {
		if ($id != $ids[$i]) return strval($id);
		++$id;
	}
	
	return strval( $id );
}

/*
**	administer_get_post_id ( )
**
**	Returns post id of post where all ad-minister data is stored.
*/
function administer_get_post_id() {
	return get_option( 'administer_post_id' );	
}

function administer_set_post_id( $post_id ) {
	if ( $post_id ) 
		update_option( 'administer_post_id', $post_id );
}

/*
**   administer_ok_to_go ( )
**
**   Checks if the supplied post/page ID exists.
*/
function administer_ok_to_go() {
	$the_page = get_page( administer_get_post_id() );
	$ok_to_go = ( $the_page->post_title ) ? true : false;
	return $ok_to_go;
}

/*
**   administer_content_age ( )
**
**  Calculates the age of a content. Returns assoc. array with 'start' and 'end' ages, i.e.
**	negative numbers for events in the future, positive for events passed, just like at
**	shuttle launches.
*/
function administer_content_age( $schedule ) {
	$schedule = trim( $schedule, " ," );
	if ( ! $schedule ) return array( array( 'start' => '0', 'end' => '0' ) );

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
		if ( $time_left == 1 )
			$units = __( 'day', 'ad-minister' );
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

function administer_set_dashboard_widget_option( $option, $option_value ) {
	$widget_id = 'ad-minister-dashboard-widget'; // This must be the same ID we set in wp_add_dashboard_widget
    $widget_options = get_option( 'dashboard_widget_options', array() );
	$widget_options[$widget_id][$option] = $option_value; 
	update_option( 'dashboard_widget_options', $widget_options );
} 

function administer_get_dashboard_widget_option( $option, $default = false ) {
	$widget_id = 'ad-minister-dashboard-widget'; // This must be the same ID we set in wp_add_dashboard_widget
    
    // Checks whether there are already dashboard widget options in the database
    $widget_options = get_option( 'dashboard_widget_options', array() );
    
    // Check whether we have widget information for ad-minister dashboard widget
    if ( !isset( $widget_options[$widget_id] ) )
		$widget_options[$widget_id] = array(); // If not, we create a new array
    
	if ( !isset( $widget_options[$widget_id][$option] ) ) {
		$widget_options[$widget_id][$option] = $default;
	}
	
	return $widget_options[$widget_id][$option];
}

/*
**  administer_dashboard_widget ()
**
**  Stick something on the dashboard if something is due to expire or become active, or if
**	it's running out of clicks or impressions.
*/
function administer_dashboard_widget () {

	// If there is no content, then skip this
	if ( ! ( $content = administer_get_content() ) ) return;
	
	$stats = administer_get_stats();
	$url = administer_get_page_url();
	$banner_url = administer_get_page_url( 'banner' );
	$events_by_time = array();
	$li_time_left = '';
	$li_impressions = '';
	$li_clicks = '';
	$expiring_period = (float) administer_get_dashboard_widget_option( 'event_period', 30 ) * 86400;
	$almost_expired_period = 7 * 86400;
	
	foreach ( $content as $con ) {
	
		// Format impressions
		$impressions = isset($stats[$con['id']]['i']) ? $stats[$con['id']]['i'] : 0;
		$impressions_p = ($con['impressions']) ? 100 * $impressions / $con['impressions'] : 0;
		if ($impressions_p > 100 - get_option('administer_dashboard_percentage') && $impressions_p < 100) {
			$li_impressions .= '<li><a href="' . $url . '&cshow=' . urlencode($con['position']) . '">' . $con['title'] . '</a>';
			$li_impressions .= ' ' . __('has', 'ad-minister') . ' ' . round(100 - $impressions_p, 1) . __('% of impressions left', 'ad-minister') . '.</li>';
		}
		
		// Format clicks
		$clicks = isset($stats[$con['id']]['c']) ? $stats[$con['id']]['c'] : 0;
		$clicks_p = ($con['clicks']) ? 100 * $clicks / $con['clicks'] : 0;
		if ($clicks_p > 100 - get_option('administer_dashboard_percentage') && $clicks_p < 100) {
			$li_clicks .= '<li><a href="' . $url . '&cshow=' . urlencode($con['position']) . '">' . $con['title'] . '</a>';
			$li_clicks .= ' ' . __('has', 'ad-minister') . ' ' . round(100 - $clicks_p, 1) . __('% of clicks left', 'ad-minister') . '.</li>';
		}
		
		// Format start/end times
		$time_left = administer_get_time_left( $con['scheduele'] );
		if ( $time_left && ( $time_left <= $expiring_period )  ) {
			$time_left_string = administer_get_time_left_string( $time_left );
			
			if ( $time_left < 0 )
				$link_class = 'ad-starting';
			elseif ($time_left <= $almost_expired_period )
				$link_class = 'ad-almost-expired';
			else
				$link_class = 'ad-expiring';
			
			$link_url = $banner_url . '&action=edit&id=' . $con['id'];
			$li_time_left = '<li><a class="' . $link_class . '" href="' . $link_url . '">' . $con['title'] . '</a> - ' . $time_left_string . '</li>';
			$events_by_time[] = array( 'time_left' => $time_left, 'li_time_left' => $li_time_left );
		}
	}

	// Build list of upcoming expiration events
    $li_time_left = '';
	if ( $events_by_time ){
		function sort_events_by_time( $a, $b ) {
			return ( $a['time_left'] > $b['time_left'] );
		}
		usort( $events_by_time, "sort_events_by_time" );
		
		foreach ( $events_by_time as $event ) {
			$li_time_left .= $event['li_time_left'];
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
      $widget_options[$widget_id]['event_period'] = absint( $_POST[$form_id]['event_period'] );
      update_option( 'dashboard_widget_options', $widget_options ); // Update our dashboard widget options so we can access later
    }
    
    $event_period = administer_get_dashboard_widget_option( 'event_period', 30 );
	
    // Create our form fields
    echo '<p><label for="ad-minister-dashboard-widget-event-period">' . __( 'Number of days to check for upcoming events: ', 'ad-minister' ) . '</label>';
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
	load_plugin_textdomain( 'ad-minister', false, plugin_basename( dirname( __FILE__ ) ) );
}

/**
***   administer_export ( )
***   
***   Enable one-click xml export of the post that contains the administer data.
**/
function administer_export () {
	global $post_ids;
	if (isset($_GET['administer']) && $_GET['administer'])
		$post_ids = array(administer_get_post_id());
}



function administer_get_default_rotate_time() {
	return get_option( 'administer_rotate_time' ) ? get_option( 'administer_rotate_time' ) : 15;
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

	function __construct() {
		// Constructor
		parent::__construct( false, $name = 'Ad-minister', array( 'description' => 'Widget For Ad-minister Plugin.' ) );
	}

	function widget($args, $instance) {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return false;
		}
		
		// outputs the content of the widget
		$before_widget = $args['before_widget'];
		$after_widget = $args['after_widget'];
		$position = $instance['position'];

		// Create widget position if it does exist
		if ( empty( $position ) ) return false;
		
		$positions = administer_get_positions();
		
		if ( !isset( $positions[$position] ) ) {
			$positions[$position] = array (
				'position' => $position,
				'type' => 'widget',
				'rotate' => 'true',
				'rotate_time' => administer_get_default_rotate_time()
			);
			administer_update_positions( $positions );
		}
		
		$code = administer_display_position( $position ); 
		if ( $code ) {
			$code = $before_widget . $code . $after_widget;
		}

		echo $code;
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
		$position = esc_attr( $instance['position'] );
		if ( !$position ) $position = 'Select a position';
		$title = strip_tags( $instance['title'] );
       
		// Get the existing widget positions and build a simple select dropdown for the user.
		$positions = administer_get_positions();
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
			<label for="<?php echo $this->get_field_id( 'position' ); ?>"><?php _e( 'Select Position:', 'ad-minister' ); ?></label>
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
function administer_template_action ( $args ) {
	$banner = administer_get_banner( $args );
	if ( $banner )
		echo $banner;
}

function administer_get_banner( $args ) {
	if ( ! administer_get_post_id() ) return false;
	
	// It's OK only to pass the name of the position to be shown...
	$args = is_array( $args ) ? $args : array( 'position' => $args );
	
	$defaults = array( 
		'position' => '', 
		'description' => '', 
		'class' => '', 
		'before' => '', 
		'after' => '', 
		'rotate' => 'true',
		'rotate_time' => 15, 
		'type' => 'template'
	);
	$args = wp_parse_args( $args, $defaults );
	
	// Ignore empty calls
	if ( ! $args['position'] ) return;	
	
	$edit_position = false;
	$editable_fields = array( 'rotate', 'rotate_time' );
	$positions = administer_get_positions();
	if ( array_key_exists( $args['position'], $positions ) ) {
	
		if ( $positions[$args['position']]['type'] !== $args['type'] ) return;
	
		foreach ( array_keys( $args ) as $key ) {
			if ( in_array( $key, $editable_fields ) ) {
				// Keep changes made to certain fields
				$saved_value = $positions[$args['position']][$key];
				if ( ( $saved_value ) && ( $args[$key] != $saved_value ) ) {
					$args[$key] = $saved_value;
				}
				elseif ( ( ! $saved_value ) && ( $args[$key] ) ) {
					$edit_position = true;
				}
			}
			elseif ( ( ! $edit_position ) && ( $args[$key] !== $positions[$args['position']][$key] ) ) {
				$edit_position = true;
			}
		}
	}
	else $edit_position = true;
	
	// If anything has changed, then update our database
	if ( $edit_position ) {
		$positions[$args['position']] = $args; 
		
		// Save to a Custom Field
		administer_update_positions( $positions );
	}

	return administer_display_position( $args['position'] );
}

/*
**   administer_is_visible ( )
**
**   Determine whether or not content is visible.
*/
function administer_is_visible( $ad ) {

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
**   administer_get_ga_tracking_code ( )
**
**   Return Google analytics tracking code.
*/
function administer_get_ga_tracking_code( $category, $action, $opt_label ) {
	// Old Google Analytics Code
	//code = "_gaq.push([\'_trackEvent\', \'{$category}\', \'{$action}\', \'{$opt_label}\']);";
	
	// Build Google Universal Analytics Tracking Code
	$code = "if ( typeof(ga) == \'function\' ) { ga(\'send\', \'event\', \'{$category}\', \'{$action}\', \'{$opt_label}\'); }";
	
	return $code;
}

function administer_resize_image( $args ) {	
	$defaults = array(
		'src' => '',
		'width' => '',
		'height' => '',
		'crop' => false,
		'retina' => false,
		'quality' => 70,
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );	
	
	if ( ! $src )
		return '';
	
	// Use timthumb script
	if ( $width & $height ) {
		$script_url = plugins_url( 'script/timthumb/timthumb.php', __FILE__ );
		$src = $script_url . '?' . ( $quality ? 'q=' . $quality : '' ) . ( $width ? '&amp;w=' . $width : '' ) . ( $height ? '&amp;h=' . $height : '' ) . '&amp;zc=0&amp;src=' . $src;	
	}
	
	return $src;
}


/*
**   administer_build_ad_link_code ( $args )
**
**   Returns url link html from supplied arguments.
*/
function administer_build_ad_link_code( $args ) {
	$defaults = array(
		'id' => '',
		'href' => '',
		'content' => '',
		'hint' => '',
		'onload' => '',
		'onclick' => '',
		'class' => ''
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	if ( ! $href ) 
		return $content;

	$link_url_id = ( $id ? "id='administer-adlink-{$id}'" : "" );
	
	if ( $hint ) {
		$link_url_title = "title='{$hint}'";
		$link_url_alt = "alt='{$hint}'";
	}
	else {
		$link_url_title = '';
		$link_url_alt = '';
	}
	
	$onload = esc_js( $onload );
	$onclick = esc_js( $onclick );
	
	if ( $onload ) {
		$onload = "onload=\"{$onload}\"";
	}
	
	if ( $onclick ) {
		$onclick = "onclick=\"{$onclick}\"";
	}
	
	$code = "<a {$link_url_id} class='{$class}' {$link_url_title} {$link_url_alt} href='{$href}' {$onclick} {$onload} target='_blank' rel='nofollow, noindex'>{$content}</a>";
	
	return $code;
}

/*
**   administer_build_ad_img_code ( $args )
**
**   Returns image banner html from supplied arguments.
*/
function administer_build_ad_img_code( $args ) {
	$defaults = array(
		'id' => '',
		'src' => '',
		'title' => '',
		'width' => '',
		'height' => '',
		'hint' => '',
		'link_url' => '',
		'onload' => '',
		'onclick' => '',
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	if ( ! $src ) return '';
	
	$onload = esc_js( $onload );
	$onclick = esc_js( $onclick );		
	
	$img_style = "";
	if ( $width ) {
		$img_style .= "width: {$width}px; ";
	}
	if ( $height ) {
		$img_style .= "height: {$height}px; ";
	}
	$img_style = $img_style ? "style='{$img_style}'" : "";
	
	$img_width = $width ? "width='{$width}'" : "";
	$img_height = $height ? "height='{$height}'" : "";
	$img_onload = $onload ? "onload=\"{$onload}\"" : "";
	$img_hint = $hint ? "title='{$hint}'" : "";
	
	$code = "";
	if ( ( ! is_admin() ) && ( get_option( 'administer_lazy_load' ) == 'true' ) ) {	
		$code .= "<noscript class='loading-lazy'><img loading='lazy' src='{$src}' {$img_style} {$img_width} {$img_height} {$img_onload} {$img_hint} /></noscript>";
	}
	else {
		$code .= "<img src='{$src}' {$img_style} {$img_width} {$img_height} {$img_onload} {$img_hint} />";
	}
		
	$code = administer_build_ad_link_code( array(
		'id' => $id,
		'href' => $link_url,
		'content' => $code,
		'hint' => $hint,
		'onclick' => $onclick
	) );
	
	return $code;
}

/*
**   administer_build_ad_flash_swf_code ( $args )
**
**   Returns flash swf object banner html from supplied arguments.
*/
function administer_build_ad_flash_swf_code( $args ) {
	$defaults = array(
		'id' => rand(),
		'title' => '',
		'src' => '',
		'link_url' => '',
		'width' => '',
		'height' => '',
		'hint' => '',
		'onload' => '',
		'onclick' => '',
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	if ( empty( $src ) )
		return '';
	
	if ( $link_url ) 
		$src .= '?clickTAG=' . $link_url;
	
	$tag_id = 'swfobject' . $id;
	$html = "<object id='{$tag_id}' classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' width='$width' height='$height'><param name='movie' value='$src' /><param name='wmode' value='transparent' /><param name='loop' value='true' /><!--[if !IE]>--><object type='application/x-shockwave-flash' data='$src' width='$width' height='$height'><param name='wmode' value='transparent' /><param name='loop' value='true' /><!--<![endif]--><p>Flash Content Unavailable</p><!--[if !IE]>--></object><!--<![endif]--></object>";
	
	// Register SWF Object
	$express_install_path = plugins_url( 'script/swfobject/expressInstall.swf', __FILE__ );
	$html .= "<script type='text/javascript' language='javascript'>swfobject.registerObject('swfobject$id', '9', '$express_install_path');</script>";
	$html .= "<script type='text/javascript' language='javascript'>jQuery('#{$tag_id}').ready(function(){ {$onload} });</script>";
	
	$html .= administer_build_ad_link_code( array(
		'id' => $id,
		'href' => $link_url,
		'content' => '',
		'hint' => $hint,
		'class' => 'block-content-link',
		'onclick' => $onclick
	) );
	
	return $html;
}

/*
**   administer_build_ad_flash_flv_code ( $args )
**
**   Returns flash video banner html from supplied arguments.
*/
function administer_build_ad_flash_flv_code( $args ) {
	$defaults = array(
		'id' => rand(),
		'title' => '',
		'src' => '',
		'link_url' => '',
		'width' => '',
		'height' => '',
		'hint' => '',
		'onload' => '',
		'onclick' => '',
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	if ( empty( $src ) )
		return '';
		
	$onload = esc_js( $onload );
	$onclick = esc_js( $onclick );
		
	$tag_id = 'flvplayer' . $id;
	$flowplayer_path = plugins_url( 'script/flowplayer/flowplayer.swf', __FILE__ ); ;
	$html = "<div id='{$tag_id}' style='width:{$width};height:{$height}'></div><script type='text/javascript' language='JavaScript'>flowplayer('flvplayer$id', '$flowplayer_path', { clip: { url: '$src', autoPlay: true, autoBuffering: true, linkUrl: '$link_url', linkWindow: '_blank' }, plugins: { controls: null }, buffering: false, onLoad: function() { $onload }, onBeforeClick: function() { $onclick }, onFinish: function() { this.stop(); this.play(); }, onBeforePause: function() { return false; } });</script>";

	$html .= administer_build_ad_link_code( array(
		'id' => $id,
		'href' => $link_url,
		'content' => '',
		'hint' => $hint,
		'class' => 'block-content-link',
		'onclick' => $onclick
	) );
	
	return $html;
}



/*
**   administer_build_ad_mp4_code ( $args )
**
**   Returns video banner html from supplied arguments.
*/
function administer_build_ad_mp4_code( $args ) {
	$defaults = array(
		'id' => rand(),
		'title' => '',
		'src' => '',
		'link_url' => '',
		'width' => '',
		'height' => '',
		'hint' => '',
		'onload' => '',
		'onclick' => '',
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	if ( empty( $src ) )
		return '';
		
	$onload = esc_js( $onload );
	$onclick = esc_js( $onclick );
				
	$tag_id = 'mp4ad' . $id;
	$classid = 'clsid:02bf25d5-8c17-4b23-bc80-d3488abddc6b';
	$codebase = 'http://www.apple.com/qtactivex/qtplugin.cab';
	$pluginspage = 'http://www.apple.com/quicktime/download';
	$html =
	"<video id='{$tag_id}' width='{$width}' height='{$height}' autoplay loop muted playsinline preload='none' style='vertical-align: middle;'>
		<!-- MP4 must be first for iPad! -->
		<source src='{$src}' type='video/mp4' /><!-- WebKit video    -->
		<!-- fallback to Flash: -->
		<object classid='{$classid}' codebase='{$codebase}' width='{$width}' height='{$height}' type='application/x-shockwave-flash' data='{$src}'>
			<!-- Firefox uses the `data` attribute above, IE/Safari uses the param below -->
			<param name='src' value='{$src}' />
			<param name='movie' value='{$src}' />
			<param name='flashvars' value='file={$src}' />
			<param name='autoplay' value='true' />
			<param name='loop' value='true' />
			<param name='mute' value='true' />
			<param name='controls' value='false' />
			<embed src='{$src}' type='image/x-macpaint' pluginspage='{$pluginspage}' width='{$width}' height='{$height}' autoplay='true' loop='true' mute='true'></embed>
		</object>
	</video>";

	$html .= administer_build_ad_link_code( array(
		'id' => $id,
		'href' => $link_url,
		'content' => '',
		'hint' => $hint,
		'class' => 'block-content-link',
		'onclick' => $onclick
	) );
	
	return $html;
}

function administer_get_ad_dimensions( $args ) {
	$defaults = array(
		'ad_size' => '',
		'ad_media_url' => '',
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	$ad_media_url = esc_url( trim( $ad_media_url ) );

	$width = '';
	$height = '';
	if ( $ad_size ) {
		list( $width, $height ) = explode( 'x', $ad_size );
	}
	else if ( $ad_media_url ) {
		$img_path = parse_url( $ad_media_url, PHP_URL_PATH );
		if ( ( $img_path !== NULL ) && ( $img_path !== FALSE ) ) {
			$img_path = realpath( '.' . $img_path );
			if ( $img_path !== FALSE ) {
				$img_size = getimagesize( $img_path );
				if ( $img_size !== FALSE ) {
					list( $width, $height ) = $img_size; 
					$width = ( $width == 0 ) ? '' : $width;
					$height = ( $height == 0 ) ? '' : $height;
				}
			}
		}
	}

	return array( 'width' => $width, 'height' => $height );
}

/*
**   administer_build_code ( )
**
**   Build advertisement code from stored information.
*/
function administer_build_code( $args ) {
	$defaults = array(
		'id' => '',
		'title' => '',
		'ad_mode' => 'advance',
		'ad_media_url' => '',
		'code' => '',
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );

	if ( $ad_mode == 'mode_basic' )  {
		$ad_media_url = esc_url( trim( $ad_media_url ) );
	
		if ( ! $ad_media_url ) return '';
		
		$dimensions = administer_get_ad_dimensions( $args );
		$width = $dimensions['width'];
		$height = $dimensions['height'];
		
		$ad_link_url = esc_url_raw( trim( $ad_link_url ) );
		if ( $ad_link_url ) {
			if ( get_option( 'administer_statistics' ) == 'true' ) {
				$ad_link_url = '%tracker%' . urlencode( str_replace( '%tracker%', '', $ad_link_url ) );
			}
		}
		
		$ad_audio_url = esc_url( trim( $ad_audio_url ) );
		
		$ad_hint = esc_attr( trim( $ad_hint ) );
		
		$onload = '';
		$onclick = '';
		$title = esc_js( $title );
		if ( ( get_option('administer_google_analytics') == 'true' ) && ( $title ) ) {
			//$onload .= esc_js( administer_get_ga_tracking_code( 'Advertisement', 'Impression', $title ) ); // Commented out because of exceeding collection limits on Google Analytics account
			$onclick .= esc_js( administer_get_ga_tracking_code( 'Advertisement', 'Click', $title, 1, true ) );
		}
	
		$args = array (
			'id' => $id,
			'title' => $title,
			'src' => $ad_media_url,
			'link_url' => $ad_link_url,
			'width' => $width,
			'height' => $height,
			'hint' => $ad_hint,
			'onload' => $onload,
			'onclick' => $onclick,
		);	
		
		$code = '';
		
		$ext = strtolower( pathinfo( parse_url( $ad_media_url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
		switch ( $ext ) {
			case 'swf':
				$code = administer_build_ad_flash_swf_code( $args );
				break;
			
			case 'flv':
				$code = administer_build_ad_flash_flv_code( $args );
				break;
			
			case 'mp4':
				$code = administer_build_ad_mp4_code( $args );
				break;
			
			default:
				if ( get_option( 'administer_resize_image' ) == 'true' ) {
					if ( ( $ext ) && ( 'gif' != $ext ) ) {
						$args['src'] = administer_resize_image( array( 'src' => $args['src'], 'width' => $args['width'], 'height' => $args['height'] ) );	
					}
				}
				$code = administer_build_ad_img_code( $args );
		}
		
		if ( $ad_audio_url )
			$code .= "[audio src='$ad_audio_url']";
	}
	
	// Strip html slashes and expand shortcodes
	$code = do_shortcode( stripslashes( $code ) );
	
	return $code;
}

function administer_build_code_callback() {
	$ad = array(
		'ad_mode' => 'mode_basic',
		'ad_media_url' => $_POST['ad_media_url'],
		'ad_size' => $_POST['ad_size'],
		'ad_hint' => $_POST['ad_hint'],
		'ad_link_url' => $_POST['ad_link_url'],
		'ad_audio_url' => $_POST['ad_audio_url'],
		'code' => $_POST['code'],
	);
	
	echo administer_build_code( $ad );
	
	die(); // this is required to return a proper result
}
add_action( 'wp_ajax_administer_build_code', 'administer_build_code_callback' );

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

// Returns the parsed, expanded code for the given advertisement
function administer_get_ad_code( $ad ) {
	if ( $ad && !is_array( $ad ) ) {
		if ( ! administer_get_post_id() ) return;
		if ( ! ( $content = administer_get_content() ) ) return;
		
		// Get advertisement code
		$ad_id = $ad;
		$ad = $content[$ad_id];
	}
	
	$code = administer_build_code( $ad );
	
	return $code;
}

/*
**   administer_get_display_code ( )
**
**   Returns the display code for a specified ad in given position.
*/
if ( !function_exists( 'administer_get_display_code' ) ) {
	function administer_get_display_code( $args ) {
		$defaults = array(
			'ads' => array(),
			'position' => array()
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		if ( ! $ads ) return '';
			
		if ( ! $position ) return '';
		
		$max_height = 0;
		$code_blocks = array();
		foreach ( $ads as $key => $ad ) {
		
			// Get advertisement code
			$code = administer_get_ad_code( $ad );
			
			// Replace click tracker place-holder
			if ( false !== strpos( $code, '%tracker%' ) ) {
				if ( get_option( 'administer_statistics' ) == 'true' ) {
					$code = str_replace( '%tracker%', administer_tracker_url( $ad['id'] ), $code );
				} else { 
					$code = str_replace( '%tracker%', '', $code );
				}
			}	

			// Make ampersands validate
			$code = preg_replace( '/&([^#])(?![a-zA-Z1-4]{1,8};)/', '&amp;$1', $code );			
			
			if ( $code ) {
				// Always wrap content code with specified wrappers
				$ad['wrap'] = 'false';
				
				// Display the content code with optional wrapping.
				if ( 'false' !== $ad['wrap'] ) { 
					$code = $position['before'] . $code . $position['after'];
				} 
			
				// Add default ad wrapping
				$class = isset( $position['class'] ) ? $position['class'] : '';
				$class .= ( $key === 0 ) ? ' first-ad' : '';
				$default_wrapper_before = "<div id='administer-ad-{$ad['id']}' class='administer-ad'>";
				$default_wrapper_after = "</div>";
				$code = $default_wrapper_before . $code . $default_wrapper_after;
				$code_blocks[] = $code;
			}
			
			$dimensions = administer_get_ad_dimensions( $ad );
			$height = $dimensions['height'];
			if ( $height ) {
				$height = (float)$height;
				$max_height = max( $max_height, $height );
			}

		}
		
		$code = implode( '', $code_blocks );

		if ( $code ) {
			$tag_attributes = ""; 
			
			if ( ( count( $code_blocks ) > 1 ) && ( get_option( 'administer_rotate_ads' ) == 'true' ) && ( $position['rotate'] == 'true' ) && ( $position['rotate_time'] ) )  {
				$class .= " tcycle";
				$time_ms = ( $position['rotate_time'] * 1000 );
				$tag_attributes .= " data-timeout='{$time_ms}' data-fx='scroll'";
			}
			
			$class .= " administer-ad-container";
			$tag_attributes .= " class='{$class}'";
			if ( $max_height ) {
				$tag_attributes .= " style='min-height: {$max_height}px'";
			}
			$code = "<div {$tag_attributes}>" . $code . "</div>";
		}
		
		return $code;
	}
}


/*
**   administer_get_rotate_display_code ( )
**
**   Returns the display code for a slide show of ads in a given rotating position.
*/
if ( !function_exists( 'administer_get_rotate_display_code' ) ) {		
	function administer_get_rotate_display_code( $args ) {
		$defaults = array(
			'slide_content'	=> array(),	
			'time_ms'      	=> 5000
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		$code = '';
		foreach ( $slide_content as $slide ) {
			$code .= $slide;
		}
		
		if ( $code ) {
			$class = "";
			$tag_attributes = ""; 
			
			if ( count( $slide_content ) > 1 )  {
				$class .= " tcycle";
				$tag_attributes .= " data-timeout='{$time_ms}' data-fx='scroll'";
			}
			
			$class .= " administer-ad-container";
			$tag_attributes .= " class='{$class}'";
			$code = "<div {$tag_attributes}>" . $code . "</div>";
		}
		
		return $code;
	}
}

/*
**	administer_get_visible_ads ( $position )
**
**	Return array of visible ads in this position.
*/
function administer_get_visible_ads( $position ) {
	if ( ! $position ) return;
	
	if ( ! ( $positions = administer_get_positions() ) ) return;

	if ( ! ( $content = administer_get_content() ) ) return;
	
	$ads = array();
	foreach ( $content as $ad ) {
		// Ensure ad is visible
		if ( ! administer_is_visible( $ad ) ) continue;
				
		// Ensure ad is in specified position
		$ad['position'] = is_array( $ad['position'] ) ? $ad['position'] : array( $ad['position'] ); 	
		if ( ! in_array( $position, $ad['position'] ) ) continue;		

		$ad['weight'] = $ad['weight'] ? $ad['weight'] : 1;
		$ads[] = $ad;
	}

	return $ads;	
}	
		
/*
**   administer_display_position ( )
**
**   Show a position, randomize weighted content and log.
*/
function administer_display_position( $position ) {
	
	if ( ! $position ) return false;
	
	// Get visible ads in this ad position
	if ( ! ( $ads = administer_get_visible_ads( $position ) ) ) {
		return administer_google_adsense_display_position( $position );
	}

	if ( ! ( $positions = administer_get_positions() ) ) return false;	
	
	// Build weighted array of ad keys
	$ad_keys = array();
	foreach ( $ads as $key => $ad ) {
		$ad_keys = array_merge( $ad_keys, array_fill( 0, $ad['weight'], $key ) );	
	}
	sort( $ad_keys );
	
	// Randomly select an ad taking weight into consideration
	$ad_key = array_rand( $ad_keys );
	$ad = $ads[$ad_key];
	
	$rotate = isset( $positions[$position]['rotate'] ) ? $positions[$position]['rotate'] : '';
	$rotate_time = isset( $positions[$position]['rotate_time'] ) ? $positions[$position]['rotate_time'] : '';
	if ( ( get_option( 'administer_rotate_ads' ) == 'true' ) && ( $rotate == 'true' ) && ( $rotate_time ) ) {
		unset( $ads[$ad_key] );
		array_unshift( $ads, $ad );	
	}
	else {
		$ads = array( $ad );
	}
	
	$code = administer_get_display_code( array( 'ads' => $ads, 'position' => $positions[$position] ) );
	
	if ( $code ) {
		// Save the page view
		if ( get_option('administer_statistics') == 'true' ) {
			administer_register_impression( $ad['id'] );
		}
	}
	
	return $code;
}

/*
**   administer_google_adsense_display_position ( )
**
*/

const GOOGLE_ADSENSE_AD_SLOT_IDS = array (
	'Article Bottom Right' => '2702633856',
	'Article Comment Banner' => '6885421444',
	'Article Comment Banner 1' => '1594917381',
	'Article Comment Banner 2' => '6655672372',
	'Article Comment Banner 3' => '2730554011',
	'Article Comment Banner 4' => '1403345694',
	'Article Comment Banner 5' => '1827346518',
	'Article Headline Banner' => '1462360915',
	'Article Logo Banner' => '2621693052',
	//'Article Margin Banner Left' => '3535021723',
	//'Article Margin Banner Right' => '1200841736',
	'Article Menu Banner Left' => '2331308194',
	'Article Post-Copyright Banner' => '8545362756',
	'Article Side Banner 1' => '9933023084',
	'Article Side Banner 2' => '1078199600',
	'Article Side Banner 3' => '6349001291',
	'Article Side Banner 4' => '9605720029',
	'Article Side Banner 5' => '1537364600',
	'Article Side Banner 6' => '7476086998',	
	'Election Coverage Footer Banner' => '7987346591',
	'Election Coverage Headline Banner' => '1421938249',
	'Election Coverage Logo Banner' => '2618156765',
	'Election Coverage Menu Banner' => '8722715907',
	'Election Coverage Post-Content Banner' => '6291121547',
	'Home Bottom Left' => '3040908142',
	'Home Content Banner' => '4754704248',
	'Home Content Banner 1' => '9447047745',
	'Home Content Banner 2' => '5113406287',
	'Home Content Banner 3' => '8759323043',
	'Home Content Banner 4' => '8939286443',
	'Home Content Banner 5' => '8364571379',
	'Home Content Banner 6' => '4826465828',
	'Home Content Banner 7' => '8949543669',
	'Home Content Banner 8' => '8719794596',
	'Home Headline Banner' => '9662099347',
	'Home Logo Banner' => '5646832712',
	'Home Menu Banner' => '2578720201',
	'Home Side Banner' => '2441545879',
	'Home Side Banner 1' => '3517719118',
	'Home Side Banner 2' => '6306475552',
	'Home Side Banner 3' => '1786939759',
	'Home Side Banner 4' => '6272979670',
	'Home Side Banner 5' => '8058540923',
	'Home Side Banner 6' => '9418017769',
);

const GOOGLE_ADSENSE_GENERAL_POSITIONS = array (
	'Article Comment Banner',
	'Article Side Banner',
	'Home Side Banner',
);

const GOOGLE_AD_POSITION_DIMENSIONS = array (
	'Article Bottom Right' => array (
		'height' => 250,
	),
	'Logo Banner' => array (
		'height' => 140,
		'full_width_responsive' => FALSE,
	),
	'Menu Banner' => array (
		'height' => 90,
		'full_width_responsive' => FALSE,
	),
	'Side Banner' => array (
		'height' => 250,
	),
	'Headline Banner' => array (
		'height' => 90,
	),
	'Content Banner' => array (
		'height' => 90,
	),
	'Comment Banner' => array (
		'height' => 90,
		'full_width_responsive' => FALSE,
	),
);

const GOOGLE_ADSENSE_CLIENTID = 'ca-pub-6732730031512336';

function administer_google_adsense_allowed() {
	if ( is_admin() ) return FALSE;
	if ( get_option( 'administer_google_adsense' ) !== 'true' ) return FALSE;
	if ( !isset( $_SERVER['HTTP_HOST'] ) ) return FALSE;

	$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
	$host = $_SERVER['HTTP_HOST'];
	$path = $_SERVER['REQUEST_URI'];
	$page_url = "{$protocol}://{$host}{$path}";

	$exclude_urls = get_option( 'administer_google_adsense_exclude_urls' );
	if ( ! empty( $exclude_urls ) ) {
		$urls = explode( "\n", trim( str_replace( "\r", "", $exclude_urls ) ) );
		foreach ( $urls as $url ) {
			if ( strcasecmp( $page_url, $url ) === 0 ) return FALSE;
		}
	}

	return TRUE;
}

function administer_google_adsense_script() {
	if ( ! administer_google_adsense_allowed() ) return FALSE;
?>	
	<!-- Google Adsense -->
	<script data-ad-client="<?php echo GOOGLE_ADSENSE_CLIENTID; ?>" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<?php	
}

function administer_google_adsense_ad_slot_id( $position ) {
	return isset( GOOGLE_ADSENSE_AD_SLOT_IDS[$position] ) ? GOOGLE_ADSENSE_AD_SLOT_IDS[$position] : FALSE;
}

function administer_google_adsense_display_position( $position ) {
	if ( ! administer_google_adsense_allowed() ) return FALSE;
	
	$result = FALSE;

	$ad_slot_id = administer_google_adsense_ad_slot_id( $position );
	if ( $ad_slot_id === FALSE ) {
		foreach ( GOOGLE_ADSENSE_GENERAL_POSITIONS as $pos ) {
			if ( strpos( $position, $pos ) === 0 ) {
				$position = $pos;
				$ad_slot_id = administer_google_adsense_ad_slot_id( $position );
				break;
			}
		}
	}

	if ( $ad_slot_id ) {
		$width = 0;
		$height = 0;
		$full_width_responsive = TRUE;
		foreach ( GOOGLE_AD_POSITION_DIMENSIONS as $pos => $dim ) {
			if ( strpos( $position, $pos ) !== FALSE ) {
				$width = isset( $dim['width'] ) ? $dim['width'] : 0;
				$height = isset( $dim['height'] ) ? $dim['height'] : 0;
				$full_width_responsive = isset( $dim['full_width_responsive'] ) ? $dim['full_width_responsive'] : TRUE;
				break;
			}
		}

		$code = administer_get_google_adsense_code( $position, $ad_slot_id, $width, $height, $full_width_responsive );
		if ( $code ) {
			$class = isset( $positions[$position]['class'] ) ? $positions[$position]['class'] : '';
			$before = "<div class='adsense-container {$class}'>";
			$after = "</div>";
			$result = $before . $code . $after;
		}
	}

	return $result;
}

/*
**   administer_get_google_adsense_code ( )
**
*/
function administer_get_google_adsense_code( $position, $ad_slot_id, $width = 0, $height = 0, $full_width_responsive = TRUE ) {
	$result = 
		'<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
		<!-- ' . $position . ' -->
		<ins class="adsbygoogle" ';
	if ( ( $width > 0 ) || ( $height > 0 ) ) {
		$result .= 
			'style="display:inline-block' . ( $width > 0 ? ';max-width:' . $width . 'px' : '' ) . ';width:100%' . ( $height > 0 ? ';height:' . $height . 'px' : '' ) . '" ';
		if ( $full_width_responsive && (  $width == 0 ) )
			$result .= ' data-full-width-responsive="true" ';
	}
	else {
		$result .= 
			'style="display:block"
			data-full-width-responsive="true"
			data-ad-format="auto" ';
	}
	$result .= '
		data-ad-client="' . GOOGLE_ADSENSE_CLIENTID . '"
		data-ad-slot="' . $ad_slot_id . '"></ins>
		<script>
			(adsbygoogle = window.adsbygoogle || []).push({});
		</script>';
	return $result;
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
**	administer_is_admin_user ( )
**
**	@return bool True if current user is site administrator/contributor.
*/

function administer_is_admin_user() {
	$current_user = wp_get_current_user();
	$roles = array( 'administrator', 'editor', 'author', 'contributor' );
	foreach ( $roles as $role ) {
		if ( user_can( $current_user->ID, $role ) ) return true;
	}
	
	return false;
}

/*
**	administer_register_impression ( )
**
**	Note that an impression was made.
*/
function administer_register_impression($id) {
	if ( is_admin() ) return;
	if ( administer_is_admin_user() ) return;
	if ( ! isset( $id ) ) return;	
	
	global $administer_stats; 
	if ( ! isset( $administer_stats[$id]['i'] ) ) $administer_stats[$id]['i'] = 0;
	$administer_stats[$id]['i']++;
}

/*
**	administer_register_click ( )
**
**	Note that an ad was clicked.
*/
function administer_register_click( $id ) {
	if ( is_admin() ) return;
	if ( administer_is_admin_user() ) return;
	if ( ! isset( $id ) ) return;
	
	global $administer_stats; 
	if ( ! isset( $administer_stats[$id]['c']) ) $administer_stats[$id]['c'] = 0;
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
			}

			// Redirect
			header( "HTTP/1.1 302 Temporary Redirect" );
			header( "Location: " . $link );
			header( "X-Robots-Tag: noindex, nofollow" );
			// I'm outta here!
			exit(1);
		}
	} 
}

function administer_send_alert( $subject, $message ) {
	// Email log message
	$headers[] = 'From: Ad-minister <admin@dominicanewsonline.com>';
	$to = "jan.durand@gmail.com";
	@wp_mail( $to, $subject, $message, $headers );	
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
function administer_log_stats_reset() {
	ob_start(); 
	debug_print_backtrace(); 
	$trace = ob_get_contents(); 
	ob_end_clean();
	
	$timestamp = date( "Y-m-d H:i:s", time() - ( 5 * 3600 ) );
	$subject = "Ad-minister Attempted Statistics Reset";
	$message = "[$timestamp] INFO: Attempted statistics reset." . PHP_EOL . $trace;
	administer_send_alert( $subject, $message );
	
	/*
	// Write to log file
	$log_file = dirname( __FILE__ ) . '/ad-minister.log';
	//error_log( '[' . $timestamp . '] INFO: ' . $message . PHP_EOL, 3, $log_file );
	$fh = fopen( $log_file, 'ab' );
	fwrite( $fh, $message );
	fclose( $fh );
	*/
}

function administer_get_stats( $id = NULL ) {
	global $administer_stats;
	
	if ( ! isset( $administer_stats ) ) {
		$administer_stats = get_post_meta( administer_get_post_id(), 'administer_stats', true );
		if ( ! is_array( $administer_stats ) ) {
			$administer_stats = array();
		}
	}
	
	if ( isset( $id ) && ! empty( $administer_stats ) )
		return $administer_stats[$id];
	else
		return $administer_stats;
}

/*
**	administer_set_stats ( )
**
**	Sets the 'administer_stats' global variable and custom field.
*/
function administer_set_stats( $stats ) {
	global $administer_stats;	
	$administer_stats = $stats;
	
	$post_id = administer_get_post_id();
	$meta_key = 'administer_stats';
	$meta_id = update_post_meta( $post_id, $meta_key, $administer_stats );
	
	if ( $meta_id && $meta_id !== true ) {
        ob_start(); 
        debug_print_backtrace(); 
        $trace = ob_get_contents(); 
        ob_end_clean(); 		
		
		$timestamp = date( "Y-m-d H:i:s", time() - ( 5 * 3600 ) );
		$subject = "WARNING: New wp_postmeta record created for meta_key '{$meta_key}'";
		$message = "[$timestamp] WARNING: New wp_postmeta record created for post_id = '{$post_id}', meta_key = '{$meta_key}' (meta_id = '{$meta_id}')." . PHP_EOL . $trace;
		administer_send_alert( $subject, $message );
	}
}

/*
**  administer_update_stats ( )
**
**  Save the clicks and impressions to db.
*/
function administer_update_stats( $stats = NULL ) {
	if ( is_admin() ) return;
	
	if ( empty( $stats ) ) {
		administer_log_stats_reset();
		return;
	}

	administer_set_stats( $stats );
}

function administer_save_stats () {
	administer_update_stats( administer_get_stats() );	
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
	if ( !( isset( $_GET['ad-minister'] ) && $_GET['ad-minister'] ) ) return $form_fields; 
	
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
	if ( isset( $attachment['ad-minister-flash-ad-dimensions'] ) )
		update_post_meta( $post['ID'], 'ad-minister-flash-ad-dimensions', $attachment['ad-minister-flash-ad-dimensions'] );
	return $post;
}
add_filter( 'attachment_fields_to_save', 'administer_attachment_fields_to_save', 20, 2 );

// Deletes the specified advertisement from Ad-minister
function administer_delete_ad( $id ) {
	if ( ! isset( $id ) ) exit;
	
	// Delete ad content
	$content = administer_get_content();
	$ad = $content[$id];	
	unset( $content[$id] );
	administer_update_content( $content );
	
	// Delete ad statistics
	administer_reset_stats( $id );
	
	do_action( 'administer_delete_ad', $ad );
}

// Returns an array containing all Ad-minister ad content
function administer_get_content( $use_cache = TRUE ) {
	global $administer_content;

	if ( $use_cache && isset( $administer_content ) ) {
		return $administer_content;
	}

	$administer_content = get_post_meta( administer_get_post_id(), 'administer_content', true );
	if ( ! is_array( $administer_content ) ) {
		$administer_content = array();
	}
	
	return $administer_content;
}

// Returns an array containing all Ad-minister ad positions
function administer_get_positions( $use_cache = TRUE ) { 
	global $administer_positions;

	if ( $use_cache && isset( $administer_positions ) ) {
		return $administer_positions;
	}

	$administer_positions = get_post_meta( administer_get_post_id(), 'administer_positions', true );
	if ( ! is_array( $administer_positions ) ) {
		$administer_positions = array();
	}
	
	return $administer_positions;
}

function administer_update_positions( $positions ) {
	global $administer_positions;
	$administer_positions = $positions;
	
	$post_id = administer_get_post_id();
	$meta_key = 'administer_positions';
	$meta_id = update_post_meta( $post_id, $meta_key, $administer_positions );
	
	if ( $meta_id && $meta_id !== true ) {
	    ob_start(); 
        debug_print_backtrace(); 
        $trace = ob_get_contents(); 
        ob_end_clean(); 		
		
		$timestamp = date( "Y-m-d H:i:s", time() - ( 5 * 3600 ) );
		$subject = "WARNING: New wp_postmeta record created for meta_key '{$meta_key}'";
		$message = "[$timestamp] WARNING: New wp_postmeta record created for post_id = '{$post_id}', meta_key = '{$meta_key}' (meta_id = '{$meta_id}')." . PHP_EOL . $trace;
		administer_send_alert( $subject, $message );
	}	
}

// Updates the Ad-minister ad content with the given content
function administer_update_content( $content ) {
	update_post_meta( administer_get_post_id(), 'administer_content', $content );
	do_action( 'administer_update_content', $content );
}

function administer_get_page_url( $page = '' ) {
	if ( empty( $page ) )
		$page = 'ad-minister';
	else if ( strpos( $page, 'ad-minister-' ) === false )
		$page = 'ad-minister-' . $page;
	return get_admin_url() . "admin.php?page=$page";
}

function administer_reset_stats( $id = NULL ) {
	if ( is_null( $id ) ) {
		$stats = array();
		administer_set_stats( $stats );
	}
	else {
		$stats = administer_get_stats();
		unset( $stats[$id] );
		administer_update_stats( $stats );
	}	
}

function administer_reset_impressions( $id ) {
	$stats = administer_get_stats();
	unset( $stats[$id]['i'] );
	administer_set_stats( $stats );
}

function administer_reset_clicks( $id ) {
	$stats = administer_get_stats();
	unset( $stats[$id]['c'] );
	administer_set_stats( $stats );
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

function administer_get_post_var( $varname, $default = '' ) {
	return isset( $_POST[$varname] ) ? $_POST[$varname] : '';
}

function administer_get_query_var( $varname, $default = '' ) {
	return isset( $_GET[$varname] ) ? $_GET[$varname] : '';
}