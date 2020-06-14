<div class="wrap">
	<?php
	$is_save = isset( $_POST['save'] );
	$is_delete = isset( $_POST['delete'] );
	
	$action = isset( $_GET['action'] ) ? $_GET['action'] : '';
	$is_edit = ( !$is_delete ) && ( $action == 'edit' ); 
	$edit_id = ( $is_edit && isset( $_GET['id'] ) ) ? $_GET['id'] : '';
	
	$resetimpressions = isset( $_GET['resetimpressions'] ) && ( $_GET['resetimpressions'] == 'true' );
	$resetclicks = isset( $_GET['resetclicks'] ) && ( $_GET['resetclicks'] == 'true' );
	
	if ( $is_edit ) {
	?>
		<h2>Edit Banner <a href="<?php echo administer_get_page_url( "banner" ); ?>" class="add-new-h2">Add New</a></h2>
	<?php
	}
	else{
	?>
		<h2>Add New Banner</h2>
	<?php
	}	
	?>
	<div>
		<?php
		// Check to see that we have everything we need
		if ( $is_save ) {
					
			$content = administer_get_content();
			
			// Required parameters
			$id = administer_get_post_var( 'id' ) ? administer_get_post_var( 'id' ) : administer_get_available_id();
			$content[$id]['id'] = $id;
			$content[$id]['position'] = administer_get_post_var( 'position' ); 
			$content[$id]['code'] = administer_get_post_var( 'content' );
			$content[$id]['title'] = administer_get_post_var( 'title' );
			$content[$id]['ad_media_url'] = administer_get_post_var( 'ad_media_url' );
			$content[$id]['ad_size'] = administer_get_post_var( 'ad_size' );
			$content[$id]['ad_mode'] = administer_get_post_var( 'ad_mode' );
			//$content[$id]['ad_modes_synced'] = !empty( $_POST['ad_modes_synced'] ) ? 'checked ' : '';
			
			// Optional parameters
			$content[$id]['ad_link_url'] = administer_get_post_var( 'ad_link_url' );
			$content[$id]['ad_audio_url'] = administer_get_post_var( 'ad_audio_url' );
			$content[$id]['ad_hint'] = administer_get_post_var( 'ad_hint' );	
			$content[$id]['scheduele'] = administer_get_post_var( 'scheduele' );
			$content[$id]['impressions'] = administer_get_post_var( 'impressions' );
			$content[$id]['clicks'] = administer_get_post_var( 'clicks' );
			$content[$id]['show'] = ( administer_get_post_var( 'show' ) == 'on' ) ? 'true' : 'false';
			$content[$id]['weight'] = administer_get_post_var( 'weight' );
			$content[$id]['wrap'] = administer_get_post_var( 'wrap' ) ? 'true' : 'false';

			// Build code from field information if in basic mode
			if ( ( $content[$id]['ad_mode'] == 'mode_basic' ) and ( $content[$id]['code'] == '') ) {
				$content[$id]['code'] = administer_build_code( $content[$id] );
			}
			
			// Save ad content
			administer_update_content( $content );
			
			do_action( 'administer_edit_ad', $content[$id] );
			
			// Notify
			echo '<div id="message" class="updated fade"><p><strong>' . __('Banner Saved.', 'ad-minister') . '</strong></p></div>';
		
		} else if ( $is_delete ) {
			// Delete ad content data
			$id = administer_get_post_var( 'id' );
			
			administer_delete_ad( $id );
			
			// Notify 
			echo '<div id="message" class="updated fade"><p><strong>' . __('Deleted!', 'ad-minister') . '</strong></p></div>';
		}

		$content = administer_get_content();
		if ( empty( $content ) )
			echo '<div id="message" class="updated fade"><p><strong>' . __('There is no content! Do make some.', 'ad-minister') . '</strong></p></div>';

		// Are we editing?
		if ( $is_edit && $edit_id ) {
			$value = $content[$edit_id];
			
			// For legacy ads
			$value['ad_mode'] = !isset( $value['ad_mode'] ) ? 'mode_advanced' : $value['ad_mode'];
			$value['ad_size'] = ( $value['ad_size'] == 'Actual' ) ? '' : $value['ad_size'];
			
			if ( $resetimpressions ) {
				administer_reset_impressions( $edit_id );
			}
			if ( $resetclicks ) {
				administer_reset_clicks( $edit_id );
			}			
		}
		else {
			// New ad
			$value = array(
				'id' => administer_get_available_id(),
			);
			administer_reset_stats( $value['id'] );
		}
		
		$value = wp_parse_args( $value, array(
			'position' => '',
			'code' => '',
			'title' => '',
			'ad_media_url' => '',
			'ad_size' => '',
			'ad_mode' => 'mode_basic',
			'ad_link_url' => '',
			'ad_audio_url' => '',
			'ad_hint' => '',
			'scheduele' => '',
			'impressions' => 0,
			'clicks' => 0,
			'show' => 'true',
			'weight' => 1,
			'wrap' => 'true',
		) );	
		
		$checked_visible = ( !isset( $value['show'] ) ) || ( !$value['show'] ) || ( $value['show'] == 'true' ) ? 'checked="checked"' : ''; 
		$checked_wrap = 'checked="checked" disabled="disabled"'; //( ( $value['wrap'] == 'true' ) || !$value['wrap'] ) ? 'checked="checked"' : '';
		?>

		<?php $url = administer_get_page_url( "banner" ); ?>
		<form id="post" name="post" method="POST" action="<?php echo $url; ?>">
			<div class="wrap">	
				<?php 
				if ( $is_edit ) { 
					$impressions = administer_get_impressions( $value['id'] );
					$impressions = ( $impressions == 1 ) ? '1 ' . __('impression', 'ad-minister') : $impressions . ' ' . __('impressions', 'ad-minister');
					$clicks = administer_get_clicks( $value['id'] );
					$clicks = ( $clicks == 1 ) ? '1 ' . __('click', 'ad-minister') : $clicks . ' ' . __('clicks', 'ad-minister');
				?>
					<h3>Banner info:</h3>
					<ul>
						<li><?php echo $impressions; ?> | <a href="<?php echo $url . "&action=edit&id={$value['id']}&resetimpressions=true"; ?>" onclick="return confirm('<?php _e('Are you sure you want to set the impressions to zero?', 'ad-minister'); ?>')"><?php _e('Reset', 'ad-minister'); ?></a></li>
						<li><?php echo $clicks; ?> | <a href="<?php echo $url . "&action=edit&id={$value['id']}&resetclicks=true"; ?>" onclick="return confirm('<?php _e('Are you sure you want to set the clicks to zero?', 'ad-minister'); ?>')"><?php _e('Reset', 'ad-minister'); ?></a></li>
					</ul>
				<?php 
				} else { 
				?>
					<p><?php _e("Fill in the fields below in 'Basic' mode to create your new banner, or use 'Advanced' mode to create your banner using html.", 'ad-minister'); ?></p>
				<?php 
				} 
				?>

				<table  class="widefat">
					<tr>
						<td colspan="2">
							<ul id="mode" class="tabs">
							    <li><a id="mode_basic" name="mode_basic" href="">Basic</a></li>
							    <li><a id="mode_advanced" name="mode_advanced" href="">Advanced</a></li>
							</ul>
							<!--<div class="ad-modes-synced-wrapper">
								<input <?php //echo isset( $value['ad_modes_synced'] ) ? $value['ad_modes_synced'] : 'checked '; ?> type="checkbox" id="ad_modes_synced" name="ad_modes_synced" value="checked" title="Keep ad content syncronized across modes">
								<span class="info">   Synchronize (Keep content in 'Basic' and 'Advanced' modes synchronized)</span>
							</div>-->
						</td>
					</tr>
					
					<tr class="title">
						<td>
							<label class="create" for="title"><?php _e('Title', 'ad-minister'); ?>: </label>
						</td>
						<td>
							<input class="create" name="title" id="title" type="text" value="<?php echo administer_f( $value['title'] ); ?>"><br />
							<div class="hide-info" id="title_info"></div>
						</td>
					</tr>			
					
					<!-- Basic Tab Sheet -->
					<tr class="mode_basic alternate">
						<td>
							<label class="create" for="ad_media_url"><?php _e('Media URL', 'ad-minister'); ?>: </label>
						</td>
						<td>
							<div class="text-button-wrapper">
								<input class="create" name="ad_media_url" id="ad_media_url" type="text" value="<?php echo administer_f($value['ad_media_url']); ?>">
								<input name="ad_media_button" id="ad_media_button" type="button" title="Select/Upload Banner" value="" />
							</div>
							<div class="hide-info" id="ad_media_url_info"></div>
							<div class="info">(<?php _e('The banner image or animation', 'ad-minister'); ?>)</div>
						</td>
					</tr>
					
					<tr class="mode_basic alternate">
						<td>
							<label class="create" for="ad_size"><?php _e('Banner Size', 'ad-minister'); ?>: </label>
						</td>
						<td>
							<select id="ad_size" name="ad_size">
								<?php
								//if ( !$value['ad_size'] ) $value['ad_size'] = '306x300';
								$options = array( '100x651', '306x60', '306x140', '306x250', '306x300', '474x270', '474x560', '642x100', '642x140', '978x100' );
								foreach ($options as $option) {
									$dims = explode( 'x', $option );
									echo "<option value='$option' " . ( ( $option == $value['ad_size'] ) ? "selected" : "" ) . ">{$dims[0]} x {$dims[1]}</option>";
								}
								echo "<option value='' " . ( !$value['ad_size'] ? "selected" : "" ) . ">Actual Size</option>";  
								?>
							</select>
							<span style="vertical-align: middle;">Pixels</span><br />
							<div class="hide-info" id="ad_size_info"></div>
							<span class="info">(<?php _e('The size of the banner image or animation', 'ad-minister'); ?>)</span><br />
						</td>
					</tr>
					
					<tr class="mode_basic alternate">
						<td>
							<label class="create" for="ad_link_url"><?php _e('Link URL', 'ad-minister'); ?>: </label>
						</td>
						<td>
							<div class="text-button-wrapper">
								<input class="create" name="ad_link_url" id="ad_link_url" type="text" value="<?php echo administer_f($value['ad_link_url']); ?>" /><br />
								<input name="ad_link_button" id="ad_link_button" type="button" title="Select/Upload File" value="" />
							</div>
							<div class="hide-info" id="ad_link_url_info"></div>
							<div class="info">(<?php _e('Optional: a website or file that the banner should link to', 'ad-minister'); ?>)</div>					
						</td>
					</tr>

					<tr class="mode_basic alternate">
						<td>
							<label class="create" for="ad_audio_url"><?php _e('Audio URL', 'ad-minister'); ?>: </label>
						</td>
						<td>
							<div class="text-button-wrapper">
								<input class="create" name="ad_audio_url" id="ad_audio_url" type="text" value="<?php echo administer_f($value['ad_audio_url']); ?>" />
								<input name="ad_audio_button" id="ad_audio_button" type="button" title="Select/Upload Audio" value="" /><br />
							</div>
							<div class="hide-info" id="ad_audio_url_info"></div>
							<span class="info">(<?php _e('Optional: an audio clip that should be included with the banner', 'ad-minister'); ?>)</span><br />
						</td>
					</tr>

					<tr class="mode_basic alternate">
						<td>
							<label class="create" for="ad_hint"><?php _e('Pop-up Hint', 'ad-minister'); ?>: </label>
						</td>
						<td>
							<input class="create" name="ad_hint" id="ad_hint" type="text" value="<?php echo administer_f($value['ad_hint']); ?>" /><br />
							<div class="hide-info" id="ad_hint_info"></div>
							<span class="info">(<?php _e('Optional: a message to be displayed when the user hovers their mouse pointer over the banner', 'ad-minister'); ?>)</span>
						</td>
					</tr>
					
					<!-- Advanced Tab Sheet -->
					<tr class="mode_advanced alternate">
						<td>
							<label class="create" for="content"><?php _e('Html code', 'ad-minister'); ?>:</label>
						</td>
						<td>
							<div id="postdiv" class="postarea">
								<?php wp_editor( stripslashes( $value['code'] ), 'content' ); ?>
							</div>
						</td>
					</tr>
					
					<tr class="alternate">
						<td>
							<label class="create" for="scheduele"><?php _e('Schedule', 'ad-minister'); ?></label>
						</td>
						<td>
							<!-- Date selection fields -->
							<div id="ad-schedule-wrapper">
								<div id="ad-schedule-from-wrapper">
									<label for="ad_schedule_df">Start:</label><br />
									<input readonly="readonly" type="text" maxlength="10" name="ad_schedule_df" id="ad_schedule_df" title="Select a start date" value="" />
									<div class="hide-info" id="ad_schedule_df_info"></div>
								</div>
								<div id="ad-schedule-to-wrapper">
									<label for="ad_schedule_dt">End:</label><br />
									<input readonly="readonly" type="text" maxlength="10" name="ad_schedule_dt" id="ad_schedule_dt" title="Select an end date" value="" />
									<div class="hide-info" id="ad_schedule_dt_info"></div>
								</div>
								<input class="button" type="button" name="sched_add_button" id="sched_add_button" value="Add Schedule" />
							</div>
							<input class="create" name="scheduele" type="text" id="scheduele" value="<?php echo $value['scheduele']; ?>" /><br />
							<span class="info">(<?php _e('Optional', 'ad-minister'); ?>)</span><br /> 
							<span class="info">E.g. 2007-12-01:2008-01-01 &nbsp;&nbsp;&nbsp; (12:00am Dec. 1st 2007 to 11:59pm Jan. 1st 2008)</span><br />
							<span class="info">E.g. 2007-12-01 14.30.00:2008-01-01 18.30.00 &nbsp;&nbsp;&nbsp; (2:30pm Dec. 1st 2007 to 6:30pm Jan. 1st 2008)</span><br />
							<span class="info">E.g. 2007-12-01:2008-01-01 18.30.00 &nbsp;&nbsp;&nbsp; (12:00am Dec. 1st 2007 to 6:30pm Jan. 1st 2008)</span>
							<div class="hide-info" id="scheduele_info"></div>
						</td>
					</tr>
					
					<tr class="alternate">
						<td>
							<label class="create" for="ad_position"><?php _e('Position', 'ad-minister'); ?></label>
						</td>
						<td>
							<?php echo administer_position_select($value['position']); ?>
							<div class="hide-info" id="ad_position_info"></div>
						</td>
					</tr>

					<tr class="mode_advanced alternate">
						<td>
							<label class="create" for="impressions"><?php _e('Impressions', 'ad-minister'); ?>: </label>
						</td>
						<td>
							<input name="impressions" type="text" id="impressions" value="<?php echo $value['impressions']; ?>" /> 
							<span class="info">(<?php _e('Optional', 'ad-minister'); ?>)</span>
						</td>
					</tr>

					<tr class="mode_advanced alternate">
						<td>
							<label class="create" for="clicks"><?php _e('Clicks', 'ad-minister'); ?></label>
						</td>
						<td>
							<input name="clicks" type="text" id="clicks"  value="<?php echo $value['clicks']; ?>"  /> 
							<span class="info">(<?php _e('Optional, see documentation on how to make this work.', 'ad-minister'); ?>)</span>
						</td>
					</tr>

					<tr class="mode_advanced alternate">
						<td>
							<label class="create" for="weight"><?php _e('Weight', 'ad-minister'); ?></label>
						</td>
						<td>
							<input name="weight" type="text" id="weight" value="<?php echo $value['weight']; ?>"  /> 
							<span class="info">(<?php _e('Optional', 'ad-minister'); ?>)</span>
						</td>
					</tr>

					<tr class="alternate">
						<td>
							<label class="create" for="show"><?php _e('Visible', 'ad-minister'); ?></label>
						</td>
						<td>
							<input name="show" type="checkbox" id="show" <?php echo $checked_visible; ?> /> <span class="info">(<?php _e('Make banner visible?', 'ad-minister'); ?>)</span>
						</td>
					</tr>

					<tr class="mode_advanced alternate">
						<td>
							<label class="create" for="wrap"><?php _e('Wrapped', 'ad-minister'); ?></label>
						</td>
						<td>
							<input name="wrap" type="checkbox" id="wrap" <?php echo $checked_wrap; ?> /> <span class="info">(<?php _e('Use wrapper?', 'ad-minister'); ?>)</span>
						</td>
					</tr>
				</table>
				
				<p>
					<!-- Save Button -->
					<input id="save" name="save" class="button-primary" type="submit" value="<?php _e('Save', 'ad-minister'); ?>">
						
					<!-- Preview Button -->
					<input id="preview-button" title="Advertisement Preview" class="button" type="button" value="Preview" />
					
					<?php if ( $is_edit ) : ?>
						<!--Delete Button -->
						<input id="delete" name="delete" class="button" type="submit" value="<?php _e('Delete this', 'ad-minister'); ?>">
					<?php endif; ?>
				</p>
				
				<p><a href="<?php echo administer_get_page_url(); ?>" title="Go back to Banners page">Back to Banners</a></p>
				
				<div style="padding: 10px; margin-left: 170px;">
					<input type="hidden" id="id" name="id" value="<?php echo $value['id']; ?>" />
					<?php
					// Get ad mode
					if ( !$value['ad_mode'] ) {
						// For legacy ads
						if ( $value['code'] && !$value['ad_media_url'] ) {
							$value['ad_mode'] = 'mode_advanced';
						}
						else {
							$value['ad_mode'] = 'mode_basic'; 
						}
					}
					?>
					<input type="hidden" name="ad_mode" id="ad_mode" value="<?php echo $value['ad_mode']; ?>" />
					
					<!-- Preview Window -->
					<div id="ad-preview" style="display: none;"></div>					
				</div>
			</div>
		</form>
	</div>
</div>