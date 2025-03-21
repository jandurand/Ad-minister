<div class="wrap">
	<h2>Ad-minister Settings</h2>

	<?php

	// Saving our Options.
	if ( $ad_post_id = administer_get_post_var( 'administer_post_id' ) ) {
		administer_set_post_id( $ad_post_id );
		if ( preg_match( '/\d+$/', $ad_post_id ) ) {
			$the_page = get_page( $ad_post_id );
			if ( $the_page->post_author ) 
				echo '<div id="message" class="updated fade"><p><strong>' . __('Options saved.') . '</strong></p></div>';			
		} else {
			echo '<div id="message" class="updated fade"><p><strong>' . __('Error! The ID must be a number. Try again.') . '</strong></p></div>';		
		}
		
		update_option( 'administer_dashboard_period', administer_get_post_var( 'administer_dashboard_period' ) );
		update_option( 'administer_dashboard_percentage', administer_get_post_var( 'administer_dashboard_percentage' ) );
		update_option( 'administer_user_level', administer_get_post_var( 'administer_user_level' ) );
		update_option( 'administer_google_adsense_exclude_urls', trim( administer_get_post_var( 'administer_google_adsense_exclude_urls' ) ) );

		// Checkboxes...
		$names = array(
			'administer_make_widgets', 
			'administer_dashboard_show', 
			'administer_statistics', 
			'administer_google_analytics',
			'administer_google_adsense',
			'administer_rotate_ads', 
			'administer_lazy_load',
			'administer_resize_image'
		);
		foreach ( $names as $name ) {
			$value = ( administer_get_post_var( $name ) == 'on' ) ? 'true' : 'false';
			update_option( $name, $value );		
		}
	}

	// Default settings
	if (!strlen(get_option('administer_make_widgets'))) 
		update_option('administer_make_widgets', 'true');

	if (!strlen(get_option('administer_statistics'))) 
		update_option('administer_statistics', 'true');

	if (!strlen(get_option('administer_google_analytics')))
		update_option('administer_google_analytics', 'true');

	if (!strlen(get_option('administer_rotate_ads')))
		update_option('administer_rotate_ads', 'false');

	if (!strlen(get_option('administer_rotate_time')))
		update_option('administer_rotate_time', '15');	
	
	if (!strlen(get_option('administer_resize_image')))
		update_option('administer_resize_image', 'false');
		
	if (!strlen(get_option('administer_lazy_load')))
		update_option('administer_lazy_load', 'false');
		
	if (!strlen(get_option('administer_dashboard_show'))) 
		update_option('administer_dash board_show', 'true');

	if (!strlen(get_option('administer_dashboard_period')))
		update_option('administer_dashboard_period', '7');

	if (!strlen(get_option('administer_dashboard_percentage')))
		update_option('administer_dashboard_percentage', '20');

	if (!strlen(get_option('administer_user_level')))
		update_option('administer_user_level', '7');

	if (!strlen(get_option('administer_google_adsense')))
		update_option('administer_google_adsense', 'true');
		
	// Display installation messsage 
	if ( ! administer_get_post_id() )
		echo '<div id="message" class="updated fade"><p><strong>' . __('Before you get started you must specify the ID of an existing post or page that will hold the content.', 'ad-minister') . '</strong></p></div>';

	// Check that the post ID exists. 
	if ( administer_get_post_id() && ! administer_ok_to_go() )
		echo '<div id="message" class="updated fade"><p><strong>' . __('Error! The ID you supplied does not exist', 'ad-minister') . ' <a href="' . get_option('siteurl') . '/wp-admin/page-new.php">' . __('Go create a one', 'ad-minister') . '</a></strong></p></div>';			

	// See what post we're attached to
	$the_page = get_page( administer_get_post_id() );
	$title = ($the_page->post_author) ? __('The content is currently attached to post/page ID <strong>', 'ad-minister') . administer_get_post_id() . "</strong> entitled '" . $the_page->post_title . "'. | <a href='#' onclick=\" alert('" . __('Warning! Changing the ID will cause the positions and content to dissapear. Please do proceed with caution.', 'ad-minister') . "'); jQuery('#view_ad_post_id').hide(); jQuery('#edit_ad_post_id').show(); return false; \">" . __('Change', 'ad-minister') . "</a>" : '';
	?>

	<form method="post" action="<?php administer_get_page_url( "settings" ); ?>">
		<table class="form-table">
			 <tr>
			 	<th scope="row" valign="top"><?php _e('Setup', 'ad-minister'); ?></th>
			 	<td>
					<?php _e('The Ad-minister data attaches itself to a post or page with a given ID. There is little reason to change this.', 'ad-minister'); ?>
					<div id="view_ad_post_id" ><?php echo $title; ?></div>
					<div id="edit_ad_post_id" <?php if ($title) echo 'style="display: none"'; ?>><?php _e('ID of page to attach the Ad-minister data to', 'ad-minister'); ?>: <input type="text" name="administer_post_id" value="<?php echo administer_get_post_id(); ?>" style="width: 30px;" /></div>
			 	</td>
			 </tr>
			 <tr>
			 	<th scope="row" valign="top"><?php _e('Notifications', 'ad-minister'); ?></th>
			 	<td>
					<input type="checkbox" id="administer_dashbaord_show" name="administer_dashboard_show" <?php if (get_option('administer_dashboard_show') == 'true') echo ' checked="checked" '; ?> /> <label for="administer_dashbaord_show"><?php _e('Alert on the Dashboard of upcoming content expiration or activation.', 'ad-minister'); ?></label><br /><br />
					<?php _e('Number of days to check for upcoming events', 'ad-minister'); ?>: <input type="number" min="1" name="administer_dashboard_period" value="<?php echo get_option('administer_dashboard_period'); ?>" style="width: 60px;" /><br /><br />
					<?php _e('Minimum percentage of clicks/impressions left before alerting', 'ad-minister'); ?>: <input type="number" min="1" name="administer_dashboard_percentage" value="<?php echo get_option('administer_dashboard_percentage'); ?>" style="width: 60px;" />
			 	</td>
			 </tr>
			 <tr>
			 	<th scope="row" valign="top"><?php _e('Theme widgets', 'ad-minister'); ?></th>
			 	<td>
					<input type="checkbox" id="administer_make_widgets" name="administer_make_widgets" <?php if (get_option('administer_make_widgets') == 'true') echo ' checked="checked"'; ?> /> <label for="administer_make_widgets"><?php _e('Make theme widgets?', 'ad-minister'); ?></label>
			 	</td>
			 </tr>
			 <tr>
			 	<th scope="row" valign="top"><?php _e('Statistics', 'ad-minister'); ?></th>
			 	<td>
					<input type="checkbox" id="administer_statistics" name="administer_statistics" <?php if (get_option('administer_statistics') == 'true') echo ' checked="checked"'; ?> /> <label for="administer_statistics"><?php _e('Log content impressions and clicks?', 'ad-minister'); ?></label>
			 	</td>
			 </tr>
			 <tr>
			 	<th scope="row" valign="top"><?php _e('Google Analytics', 'ad-minister'); ?></th>
			 	<td>
					<input type="checkbox" id="administer_google_analytics" name="administer_google_analytics" <?php if (get_option('administer_google_analytics') == 'true') echo ' checked="checked"'; ?> /> <label for="administer_google_analytics"><?php _e('Log content clicks and impressions through Google Analytics?', 'ad-minister'); ?></label>
			 	</td>
			 </tr>
			 <tr>
			 	<th scope="row" valign="top"><?php _e('Ad Rotation', 'ad-minister'); ?></th>
			 	<td>
					<input type="checkbox" id="administer_rotate_ads" name="administer_rotate_ads" <?php if ( get_option( 'administer_rotate_ads' ) == 'true' ) echo ' checked="checked"'; ?> /> <label for="administer_rotate_ads"><?php _e('Allow multiple advertisements to rotate in a single position within a singe page load?', 'ad-minister'); ?></label><br /><br />
					<?php _e('Default rotation time', 'ad-minister'); ?>: <input type="number" style="width: 60px;" id="administer_rotate_time" name="administer_rotate_time" min="1" value="<?php echo get_option( 'administer_rotate_time' ); ?>"> (<?php _e('Time between ad rotations in seconds', 'ad-minister'); ?>)	
			 	</td>
			 </tr>
			 <tr>
			 	<th scope="row" valign="top"><?php _e('Lazy Load Content', 'ad-minister'); ?></th>
			 	<td>
					<input type="checkbox" id="administer_lazy_load" name="administer_lazy_load" <?php if ( get_option( 'administer_lazy_load' ) == 'true' ) echo ' checked="checked"'; ?> /> <label for="administer_lazy_load"><?php _e('Only load ad content when banner is in view?', 'ad-minister'); ?></label>
			 	</td>
			 </tr>
			 <tr>
			 	<th scope="row" valign="top"><?php _e('Resize Images', 'ad-minister'); ?></th>
			 	<td>
					<input type="checkbox" id="administer_resize_image" name="administer_resize_image" <?php if ( get_option( 'administer_resize_image' ) == 'true' ) echo ' checked="checked"'; ?> /> <label for="administer_resize_image"><?php _e('Resize image files on the server-side before serving?', 'ad-minister'); ?></label>
			 	</td>
			 </tr>
			 <tr>
			 	<th scope="row" valign="top"><?php _e('User access', 'ad-minister'); ?></th>
			 	<td>
			 		<?php _e('The minimum <a href="http://codex.wordpress.org/Roles_and_Capabilities">User Role</a> required to access Ad-minister:', 'ad-minister'); ?>
					<select name="administer_user_level">
						<?php 
						$user_capability = get_option('administer_user_level'); 
						$roles = array(
							"Administrator" => "manage_options",
							"Editor" => "delete_others_posts",
							"Author" => "delete_published_posts",
							"Contributor" => "delete_posts",
						);
						foreach ( $roles as $role => $capability ) {
						?>
							<option value="<?php $capability; ?>" <?php if ( $user_capability == $capability ) echo 'selected="selected"'; ?>><?php _e( $role, 'ad-minister' ); ?></option>
						<?php
						}
						?>
					</select>
			 	</td>
			 </tr>
			 <tr>
			 	<th scope="row" valign="top"><?php _e('Google Adsense', 'ad-minister'); ?></th>
			 	<td>
					<input type="checkbox" id="administer_google_adsense" name="administer_google_adsense" <?php if (get_option('administer_google_adsense') == 'true') echo ' checked="checked"'; ?> /> <label for="administer_google_adsense"><?php _e('Allow Google Adsense ads?', 'ad-minister'); ?></label><br /><br />
					
					<?php
					$option_name = 'administer_google_adsense_exclude_urls';
					$option_value = get_option( $option_name );
					?>
					<label for="<?php echo $option_name; ?>"><?php _e( 'Add page urls to exclude from Google Adense on separate lines below:', 'ad-minister' ); ?></label><br /><br />
					<textarea id="<?php echo $option_name; ?>" name="<?php echo $option_name; ?>" style="width:100%;resize:vertical" rows="10"><?php echo $option_value; ?></textarea>
			 	</td>
			 </tr>
		</table>

		<?php 
		/*
			<h4><?php _e('XML Export', 'ad-minister'); ?>:</h4>
			<blockquote>
				<a href="<?php echo get_option('siteurl') . '/wp-admin/export.php?download=true&administer=true' ?>"><?php _e('Download', 'ad-minister'); ?></a> <?php _e('Worpress XML export file for Ad-minister positions and content', 'ad-minister'); ?>.
			</blockquote>
		*/
		?>
		<p class="submit">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Update Settings', 'ad-minister') ?>" />
		</p>
	</form>
</div>