<div class="wrap">
	<?php
	if ( $_GET['action'] == 'edit' ) {
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
		print_r($post);

		if ($_GET['resetimpressions'] == 'true') {
			$id = $_GET['id'];
			$stats = administer_get_stats();
			unset($stats[$id]['i']);
			administer_update_stats( $stats );
		}
		if ($_GET['resetclicks'] == 'true') {
			$id = $_GET['id'];	
			$stats = administer_get_stats();
			unset($stats[$id]['c']);
			administer_update_stats( $stats );
		}

		// Check to see that we have everything we need
		if ( $_POST['save'] ) {
						
			$content = get_post_meta(get_option('administer_post_id'), 'administer_content', true);
			if ( !is_array($content) ) $content = array();
			$index = count($content);

			// Required parameters
			$id = ( $_POST['id'] ) ? $_POST['id'] : administer_nbr_to_save();
			$content[$id]['id'] = $id;
			$content[$id]['position'] = $_POST['position']; 
			$content[$id]['code'] = $_POST['content'];
			$content[$id]['title'] = $_POST['title'];
			$content[$id]['ad_media_url'] = $_POST['ad_media_url'];
			$content[$id]['ad_size'] = $_POST['ad_size'];
			$content[$id]['ad_mode'] = $_POST['ad_mode'];
			$content[$id]['ad_modes_synced'] = !empty( $_POST['ad_modes_synced'] ) ? 'checked ' : '';
			
			// Optional parameters
			$content[$id]['ad_link_url'] = $_POST['ad_link_url'];
			$content[$id]['ad_audio_url'] = $_POST['ad_audio_url'];
			$content[$id]['ad_hint'] = $_POST['ad_hint'];	
			$content[$id]['scheduele'] = $_POST['scheduele'];
			$content[$id]['impressions'] = $_POST['impressions'];
			$content[$id]['clicks'] = $_POST['clicks'];
			$content[$id]['show'] = ($_POST['show'] == 'on') ? 'true' : 'false';
			$content[$id]['weight'] = $_POST['weight'];
			$content[$id]['wrap'] = ($_POST['wrap']) ? 'true' : 'false';

			// Build code from field information if in basic mode
			if ( ( $content[$id]['ad_mode'] == 'mode_basic' ) and ( $content[$id]['code'] == '') ) {
				$content[$id]['code'] = do_shortcode( stripslashes( administer_build_code( $content[$id] ) ) );
			}
			
			// Save to a Custom Field
			if (!add_post_meta(get_option('administer_post_id'), 'administer_content', $content, true)) 
				update_post_meta(get_option('administer_post_id'), 'administer_content', $content);
			
			$value = $content[$id];
			
			// Notify
			echo '<div id="message" class="updated fade"><p><strong>' . __('Banner Saved.', 'ad-minister') . '</strong></p></div>';
		
		} else if ( $_POST['delete'] ) {
			$id = $_POST['id'];
			
			// Delete ad content data
			$content = get_post_meta(get_option('administer_post_id'), 'administer_content', true);
			unset($content[$id]);
			update_post_meta(get_option('administer_post_id'), 'administer_content', $content);		
			
			// Delete ad statistics
			$stats = administer_get_stats();
			if (is_array($stats)) {
				unset($stats[$id]); 
				administer_update_stats( $stats );
			}
				
			// Notify 
			echo '<div id="message" class="updated fade"><p><strong>' . __('Deleted!', 'ad-minister') . '</strong></p></div>';
		}

		$content = get_post_meta(get_option('administer_post_id'), 'administer_content', true);
		if (!is_array($content) || empty($content))
			echo '<div id="message" class="updated fade"><p><strong>' . __('There is no content! Do make some.', 'ad-minister') . '</strong></p></div>';

		// Are we editing?
		if ($_GET['action'] == 'edit') {
			if (!$value) $value = $content[$_GET['id']];		
		}
		else $value = array();
		$checked_visible = ($value['show'] == 'true' || !$value['show']) ? 'checked="checked"' : ''; 
		$checked_wrap = ($value['wrap'] == 'true' || !$value['wrap']) ? 'checked="checked"' : '';
		?>

		<?php $url = administer_get_page_url( "banner" ) . "&id={$value['id']}&action=edit"; ?>
		<form id="post" name="post" method="POST" action="<?php echo $url; ?>">
			<div class="wrap">	
				<?php 
				if ($_GET['action'] == 'edit') { 
					$stats = administer_get_stats();
					$impressions = ($stats[$value['id']]['i']) ? $stats[$value['id']]['i'] : '0';
					$impressions = ($impressions == 1) ? '1 ' . __('impression', 'ad-minister') : $impressions . ' ' . __('impressions', 'ad-minister');

					$clicks = ($stats[$value['id']]['c']) ? $stats[$value['id']]['c'] : '0';
					$clicks = ($clicks == 1) ? '1 ' . __('click', 'ad-minister') : $clicks . ' ' . __('clicks', 'ad-minister');
				?>
					<h3>Banner info:</h3>
					<ul>
						<li><?php echo $impressions; ?> | <a href="<?php echo $url . '&resetimpressions=true'; ?>" onclick="return confirm('<?php _e('Are you sure you want to set the impressions to zero?', 'ad-minister'); ?>')"><?php _e('Reset', 'ad-minister'); ?></a></li>
						<li><?php echo $clicks; ?> | <a href="<?php echo $url . '&resetclicks=true'; ?>" onclick="return confirm('<?php _e('Are you sure you want to set the clicks to zero?', 'ad-minister'); ?>')"><?php _e('Reset', 'ad-minister'); ?></a></li>
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
							    <li><a id="mode_advanced" name="mode_basic" href="">Advanced</a></li>
							</ul>
							<div class="ad-modes-synced-wrapper">
								<input <?php echo isset( $value['ad_modes_synced'] ) ? $value['ad_modes_synced'] : 'checked '; ?> type="checkbox" id="ad_modes_synced" name="ad_modes_synced" value="checked" title="Keep ad content syncronized across modes">
								<span class="info">   Synchronize (Keep content in 'Basic' and 'Advanced' modes synchronized)</span>
							</div>
						</td>
					</tr>
					
					<tr class="title">
						<td>
							<label class="create" for="title"><?php _e('Title', 'ad-minister'); ?>: </label>
						</td>
						<td>
							<input class="create" name="title" id="title" type="text" value="<?php echo administer_f($value['title']); ?>"><br />
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
								<input name="ad_media_button" id="ad_media_button" type="button" title="Select/Upload Image" value="" />
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
								if ( !$value['ad_size'] ) $value['ad_size'] = '306x300';  
								$options = array( '306x140', '306x300', '474x270', '474x560', '642x140', '978x100' );
								foreach ($options as $option) {
									$dims = explode( 'x', $option );
									echo "<option value='$option' " . ( ( $option == $value['ad_size'] ) ? "selected" : "" ) . ">{$dims[0]} x {$dims[1]}</option>";
								}
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
									<label for="ad_schedule_df">From:</label><br />
									<input readonly="readonly" type="text" maxlength="10" name="ad_schedule_df" id="ad_schedule_df" title="Select a start date" value="" />
									<div class="hide-info" id="ad_schedule_df_info"></div>
								</div>
								<div id="ad-schedule-to-wrapper">
									<label for="ad_schedule_dt">To:</label><br />
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
							<label class="create" for="ad_position_edit_"><?php _e('Position', 'ad-minister'); ?></label>
						</td>
						<td>
							<?php echo administer_position_select(0, $value['position']); ?>
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
				
				<div style="padding: 10px; margin-left: 170px;">
					<input type="hidden" name="id" value="<?php echo $value['id']; ?>" />
					<?php
					// Get ad mode
					if ( !$value['ad_mode'] ) {
						$value['ad_mode'] = 'mode_basic';
						if ( $value['code'] && !$value['ad_media_url'] ) {
							$value['ad_mode'] = 'mode_advanced';
						} 
					}
					?>
					<input type="hidden" name="ad_mode" id="ad_mode" value="<?php echo $value['ad_mode']; ?>" />
					
					<!-- Save Button -->
					<input id="save" name="save" class="button-primary" type="submit" value="<?php _e('Save', 'ad-minister'); ?>">
					
					<!-- Preview Button -->
					<input id="preview-button" alt="#TB_inline?inlineId=ad-preview" title="Advertisement Preview" class="button thickbox" type="button" value="Preview" />
					<div id="ad-preview" style="display: none;"></div>
				
					<?php if ($_GET['action']) : ?>
						<!--Delete Button -->
						<input id="delete" name="delete" class="button" type="submit" value="<?php _e('Delete this', 'ad-minister'); ?>">
					<?php endif; ?>
				</div>
			</div>
		</form>
	</div>
</div>