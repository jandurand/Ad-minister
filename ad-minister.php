<?php
/*
Plugin Name: Ad-minister Plus
Version: 0.6
Plugin URI: http://labs.dagensskiva.com/plugins/ad-minister/
Author URI: http://labs.dagensskiva.com/
Description:  A management system for temporary static content (such as ads) on your WordPress website. Ad-minister->All Banners to administer.
Author: Henrik Melin, Kal Ström, Jan Durand

	USAGE:

	See the Help tab in Ad-minister->Help.

	LICENCE:

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
       
*/

require_once ( 'ad-minister-functions.php' );

// Theme action
add_action('ad-minister', 'administer_template_action');

// XML Export
add_action('rss2_head', 'administer_export');

// Enable translation
add_action('init', 'administer_translate'); 

// Add administration menu
function administer_menu() {
	$page_title = 'Ad-minister';
	$menu_title = 'Ad-minister';
	$capability = 'manage_options';
	$menu_slug = 'ad-minister';
	$function = 'administer_main';
	$icon_url = plugins_url( 'images/money_icon.png', __FILE__ );
	$position = '';
	add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url );
	
	add_submenu_page( $menu_slug, 'Ad-minister - All Banners', 'All Banners', $capability, 'ad-minister' );
	add_submenu_page( $menu_slug, 'Ad-minister - New Banner', 'New Banner', $capability, 'ad-minister-banner', 'administer_page_banner' );
	add_submenu_page( $menu_slug, 'Ad-minister - Positions/Widgets', 'Positions', $capability, 'ad-minister-positions', 'administer_page_positions' );
	add_submenu_page( $menu_slug, 'Ad-minister - Settings', 'Settings', $capability, 'ad-minister-settings', 'administer_page_settings' );
	add_submenu_page( $menu_slug, 'Ad-minister - Help', 'Help', $capability, 'ad-minister-help', 'administer_page_help' );
}
add_action( 'admin_menu', 'administer_menu' );

// Ajax functions
add_action('wp_ajax_administer_save_content', 'administer_save_content');
add_action('wp_ajax_administer_delete_content', 'administer_delete_content');
add_action('wp_ajax_administer_save_position', 'administer_save_position');
add_action('wp_ajax_administer_delete_position', 'administer_delete_position');

// Handle theme widgets
if (get_option('administer_make_widgets') == 'true') {
	add_action('sidebar_admin_page', 'administer_popuplate_widget_controls');
	add_action( 'widgets_init', 'administer_load_widgets' );
}

// Display Ad-minister widget on dashboard
if (get_option('administer_dashboard_show') == 'true') {
	add_action('wp_dashboard_setup', 'administer_register_widgets');
}
	
// Count the number of impressions the content makes
if (get_option('administer_statistics') == 'true' && !is_admin()) {
	add_action('init', 'administer_init_stats');
	add_action('shutdown', 'administer_save_stats');
}
add_action('init', 'administer_do_redirect', 11);

add_action('administer_stats', 'administer_template_stats');

function administer_enqueue_scripts ( $hook ) {
	global $wpdb;
	global $pagenow;
	
	// Auto install
	if ( ! ( get_option( 'administer_post_id' ) && administer_ok_to_go() ) ) {
		$_POST = array();
		
		// Does it exist already?
		$sql = "select count(*) from $wpdb->posts where post_type='administer'";
		$nbr = $wpdb->get_var( $sql ) + 1;

		// Create a new one		
		$_POST['post_title'] = 'Ad-minister Data Holder ' . $nbr;
		$_POST['post_type'] = 'administer';
		$_POST['content'] = 'This post holds your Ad-minister data';
		$id = wp_write_post();
		update_option( 'administer_post_id', $id );
	}

	// Enqueue Ad-minster styles (includes styles for Dashboard widget)
	$script = 'css/ad-minister.css';
	$version = filemtime( plugin_dir_path( __FILE__ ) . $script );
	wp_enqueue_style( 'ad-minister', plugins_url( $script, __FILE__ ), null, $version );	
	wp_enqueue_style( 'ad-minister' );
	
	$page = isset( $_GET['page'] ) ? $_GET['page'] : '';
	if ( strpos( $page, 'ad-minister' ) !== 0 ) return false;
	
	// If we're not installed, go to the settings page for the setup.
	if ( ! administer_ok_to_go() && $page != 'ad-minister-help' ) $page = 'ad-minister-settings';	
	
	// Cannot show 'Banners' if there aren't any	
	if ( $page == 'ad-minister' ) { 
		$content = administer_get_content(); 
		if ( ! is_array( $content ) || empty( $content ) ) $page = 'ad-minister-banner';
	}
	
	// Cannot create a new banner if there are no positions
	if ( $page == 'ad-minister-banner' ) {
		$positions = get_post_meta(get_option('administer_post_id'), 'administer_positions', true);
		if ( ! is_array( $positions ) || empty( $positions ) ) $page = 'ad-minister-positions';
	}
	
	$_GET['page'] = $page;

	// Enqueue common functions javascript
	$script = 'js/ad-minister.min.js'; 
	$version = filemtime( plugin_dir_path( __FILE__ ) . $script );
	wp_enqueue_script( 'ad-minister', plugins_url( $script, __FILE__ ), array( 'jquery' ), $version );
	
	// Enqueue Flash Players
	$script_url = plugins_url( 'script/flowplayer/flowplayer-3.2.12.min.js', __FILE__ );
	wp_enqueue_script( 'flowplayer', $script_url );
	wp_enqueue_script( 'swfobject' );
	
	if ( $page == 'ad-minister-banner' ) {
		wp_enqueue_script('page');
		wp_enqueue_script('editor');
		wp_enqueue_script('thickbox');
		wp_enqueue_style('thickbox');
		wp_enqueue_script('media-upload');
		wp_enqueue_script('controls');
		wp_enqueue_script('jquery-ui-datepicker');
		
		// Enqueue style sheet for date picker fields
		wp_enqueue_style( 'ui-lightness', plugins_url( 'css/ui-lightness/jquery-ui-1.8.24.custom.css', __FILE__ ) );
		
		// Enqueue jquery multiselect plugin
		wp_enqueue_script( 'jquery-multiselect', plugins_url('js/jquery.multiselect.js', __FILE__), array( 'jquery', 'jquery-ui-widget' ) );
		wp_enqueue_style( 'jquery-multiselect', plugins_url('css/jquery.multiselect.css', __FILE__) );
		wp_enqueue_script( 'jquery-multiselect-filter', plugins_url('js/jquery.multiselect.filter.js', __FILE__), array( 'jquery-multiselect' ) );
		wp_enqueue_style( 'jquery-multiselect-filter', plugins_url('css/jquery.multiselect.filter.css', __FILE__) );
				
		// Enqueue script to use media uploader and provide form validation
		wp_enqueue_media();
	}
}
add_action( 'admin_enqueue_scripts', 'administer_enqueue_scripts', 20 ); 

function administer_wp_enqueue_scripts() {	
	$script = 'js/ad-minister-functions.min.js';
	$version = filemtime( plugin_dir_path( __FILE__ ) . $script );
	wp_register_script( 'administer-functions', plugins_url( $script, __FILE__ ), array( 'jquery' ), $version );
	wp_enqueue_script( 'administer-functions' );
}
add_action('wp_enqueue_scripts', 'administer_wp_enqueue_scripts');

function administer_wp_enqueue_styles() {	
	$script = 'css/ad-minister.css';
	$version = filemtime( plugin_dir_path( __FILE__ ) . $script );
	wp_enqueue_style( 'ad-minister', plugins_url( $script, __FILE__ ), null, $version );	
	wp_enqueue_style( 'ad-minister' );	
}
add_action('wp_enqueue_scripts', 'administer_wp_enqueue_styles');

function administer_wp_head() {
    // Hide administer-lazy-load content if javascript is unsupported
	echo '<noscript><style> .administer-lazy-load { display: none; } </style></noscript>';
}
add_action( 'wp_head', 'administer_wp_head' );

function administer_start_session() {
	if ( is_admin() ) return;
	
	if ( function_exists( 'session_status' ) ) {
		if ( session_status() == PHP_SESSION_NONE ) {
			session_start();
		}
	}
	else if ( session_id() == '' ) {
		session_start();
	}
	
	// Define $_SESSION['administer_key'] used to help in deciding
	// the first (or only) ad to be displayed in a given ad position.
	// Set max key to avoid performance hits from modulus operation on large numbers.
	$max_key = 30;
	if ( isset( $_SESSION['administer_key'] ) )
		$_SESSION['administer_key'] = ( $_SESSION['administer_key'] + 1 ) % $max_key;
	else
		$_SESSION['administer_key'] = rand( 0, $max_key - 1 );
}
administer_start_session();
//add_action('init', 'administer_session', 1);

function administer_register_type() {
	$args = array(
		'public' => false
	);
	register_post_type( 'administer', $args );
}
add_action( 'init', 'administer_register_type', 1 );