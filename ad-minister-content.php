<div class="wrap">
	<h2>Banners <a href="<?php echo administer_get_page_url( "banner" ); ?>" class="add-new-h2">Add New</a></h2>
	
	<p><?php _e('Here the content in Ad-minister is listed. Green rows indicate that the content is visible, and red that it is not. Click on the headers to sort the table according to the values of that column. To reverse the order click the arrow. To edit content click on the title.', 'ad-minister'); ?></p>
	<div id="ads">
		<?php
		$ids = array();
		$columns = array('selected', 'id', 'title', 'position', 'visible', 'time', 'impressions', 'clicks');
		
		// Check Bulk actions
		if ( isset( $_POST['bulk_actions'] ) ) {
			$selected_ads = $_POST['selected_ads'] ? $_POST['selected_ads'] : array();
			$ad_count = count( $selected_ads );
					
			switch ( $_POST['bulk_actions'] ) {
				case 'delete':	
					// Delete selected ad content
					foreach ( $selected_ads as $ad_id ) {
						administer_delete_ad( $ad_id );
					}
					// Notify
					echo '<div id="message" class="updated fade"><p><strong>' . __( $ad_count . ( $ad_count == 1 ? ' Ad ' : ' Ads ' ) . 'Deleted.', 'ad-minister') . '</strong></p></div>';
					break;
				
				case 'show':
					// Make all selected ads visible
					foreach ( $selected_ads as $ad_id ) {
						administer_show_ad( $ad_id );
					}
					// Notify 
					echo '<div id="message" class="updated fade"><p><strong>' . __( $ad_count . ( $ad_count == 1 ? ' Ad was made visible.' : ' Ads were made visible.' ), 'ad-minister') . '</strong></p></div>';
					break;
					
				case 'hide':
					// Hide all selected ads
					foreach ( $selected_ads as $ad_id ) {
						administer_hide_ad( $ad_id );
					}
					// Notify 
					echo '<div id="message" class="updated fade"><p><strong>' . __( $ad_count . ( $ad_count == 1 ? ' Ad was hidden.' : ' Ads were hidden.' ), 'ad-minister') . '</strong></p></div>';
					break;
			}
		}
		
		$contents = administer_get_content();
		$positions = get_post_meta(get_option('administer_post_id'), 'administer_positions', true);
		$stats = administer_get_stats();
		
		$link = administer_get_page_url( "banner" );	
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
					<option value="show">Show</option>
					<option value="hide">Hide</option>
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
	</div>
</div>