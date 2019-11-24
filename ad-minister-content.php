<div class="wrap">
	<h2>Banners <a href="<?php echo administer_get_page_url( "banner" ); ?>" class="add-new-h2">Add New</a></h2>
	
	<p><?php _e('Here the content in Ad-minister is listed. Green rows indicate that the content is visible, and red that it is not. Click on the headers to sort the table according to the values of that column. To reverse the order click the arrow. To edit content click on the title.', 'ad-minister'); ?></p>
	<div id="ads">
		<?php
		$ids = array();
		$columns = array( 'selected', 'id', 'title', 'position', 'visible', 'time', 'impressions', 'clicks' );
		
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
		$filter_count = array ( 'all' => 0, 'visible' => 0, 'hidden' => 0 );
		foreach ( array_keys( $contents ) as $i ) {

			$ad = $contents[$i];

			if ( !empty( $ids ) && !in_array( $ad['id'], $ids ) ) continue;
		
			$table['title'][$i] = administer_f( $ad['title'] );
			$table['title_link'][$i] = $link . '&action=edit&id=' . $ad['id'];
			if ( !is_array( $ad['position'] ) ) {
				$ad['position'] = ( $ad['position'] == '-' ) ? array() : array( $ad['position'] );	
				$contents[$i] = $ad;
				administer_update_content( $contents ); 	
			}
			natcasesort( $ad['position'] );
			$table['position'][$i] = $ad['position'];
			
			// Check visibility
			$is_visible = administer_is_visible( $ad );

			// Set orphaned content as invisible
			if ( ( $table['position'][$i] == '-') || empty( $table['position'][$i] ) ) $is_visible = false;
			
			// Get the time left based on schedule, if present
			$time_left = administer_get_time_left( $ad['scheduele'] );
			$time = administer_get_time_left_string( $time_left );

			// Calculate and format the fractional weight, given as a percentage
			$total_weight = 0;
			
			foreach ( $contents as $content )
				if ( ( $ad['position'] == $content['position'] ) && administer_is_visible( $content ) )
					$total_weight += ( $content['weight'] ) ? $content['weight'] : 1;		
			$weight = ( $ad['weight'] ) ? $ad['weight'] : 1;
			$weight = ( administer_is_visible( $ad ) ) ? 100 * $weight / $total_weight : '';
			$table['weight'][$i] = ( $weight > 0 && $weight < 100 ) ? '(' . round($weight, 1) . '%)' : '';
			
			// Don't show percentages for orphans
			if ( !$table['position'] || $table['position'][$i] == '-' ) $table['weight'][$i] = '';

			// Format impressions
			$impressions = isset( $stats[$ad['id']]['i'] ) ? $stats[$ad['id']]['i'] : '0';
			$impressions = ( $ad['impressions'] ) ? $impressions . ' of ' . $ad['impressions'] : $impressions;

			// Format clicks
			$clicks = isset( $stats[$ad['id']]['c'] ) ? $stats[$ad['id']]['c'] : '0';
			$clicks = ( $ad['clicks'] ) ? $clicks . ' of ' . $ad['clicks'] : $clicks;

			$table['clicks'][$i]      = $clicks;
			$table['impressions'][$i] = $impressions;
			$table['time'][$i]        = $time;
			$table['visible'][$i]     = ( $is_visible ) ? __('Yes', 'ad-minister') : __('No', 'ad-minister');
			$table['id'][$i] = $ad['id'];
			$table['row-class'][$i]   = ( $is_visible ) ? 'ad-visible' : 'ad-invisible';
			
			if ( $time_left ) {
				if ( ( $time_left > 0 ) && $is_visible ) {
					$expiring_period = (float) get_option( 'administer_dashboard_period', 30 ) * 86400;
					$almost_expired_period = 7 * 86400;
					if ( $time_left <= $almost_expired_period ) {
						$table['row-class'][$i] .= ' ad-almost-expired';
					}
					else if ( $time_left <= $expiring_period ) {
						$table['row-class'][$i] .= ' ad-expiring';
					}
				}
				else {
					$table['row-class'][$i] .= ' ad-in-transit';
				}
			}
			
			$filter_count['all'] += 1;
			if ( $is_visible )
				$filter_count['visible'] += 1;
			else
				$filter_count['hidden'] += 1;
		}

		// Do the sorting, only save sort column if we're in admin
		$saved_sort = ( is_admin() ) ? get_option( 'administer_sort_key' ) : '';
		$sort = isset( $_GET['sort'] ) ? $_GET['sort'] : '';
		if ( !$sort ) $sort = ( $saved_sort ) ? $saved_sort : 'position';
		if ( ( $sort != $saved_sort ) && is_admin() ) update_option( 'administer_sort_key', $sort );
		
		$arr = isset( $table[$sort] ) ? $table[$sort] : '';
		if ( !is_array( $arr ) ) {
			echo '<p><strong>' . __('No data available', 'ad-minister') . '.</strong></p>';
			return 0;
		}
		
		if ( $sort == 'position' ) {
			function array_cmp( $a, $b ) {
    			if ( $a === $b ) return 0;
				
				for ( $j = 0; $j < min( count( $a ), count( $b ) ); ++$j ) {
					$result = strnatcasecmp( $a[$j], $b[$j] );
					if ( $result ) return $result;
				}
				return ( $a < $b ) ? -1 : 1;
			}
			uasort( $arr, "array_cmp" );
		}
		else {
			natcasesort( $arr );
		}
		
		$order = isset( $_GET['order'] ) ? $_GET['order'] : '';		
		$link = administer_get_page_url(); 
		$filter = isset( $_GET['filter'] ) ? $_GET['filter'] : 'all';
		?>
		<ul class="filters">
			<?php
				$filter_items = array (
					array ( 'filter' => 'all', 'label' => 'All' ),
					array ( 'filter' => 'visible', 'label' => 'Visible' ),
					array ( 'filter' => 'hidden', 'label' => 'Hidden'),
				);
				$filter_item_count = count( $filter_items );
				for ( $i = 0; $i < $filter_item_count; ++$i ) {
					$filter_item = $filter_items[$i];
					$item_filter = $filter_item['filter'];
					$href = $link . ( $item_filter ? '&filter=' . $item_filter : '' );
					$current = ( $item_filter == $filter ) ? 'class="current" aria-current="page"' : '';
					$count = isset( $filter_count[$item_filter] ) ? $filter_count[$item_filter] : 0;
					$separator = ( $i == ( $filter_item_count - 1 ) ) ? '' : '|';
					?>
					<li class="<?php echo $item_filter; ?>"><a href="<?php echo $href; ?>" <?php echo $current; ?>><?php echo $filter_item['label']; ?> <span class="count">(<?php echo $count; ?>)</span></a><?php echo $separator ? ' ' . $separator : ''; ?></li>
					<?php
				}
			?>
		</ul>

		<form id="form_bulk" name="form_bulk" method="POST" action="<?php echo $link; ?>">
			<div style="margin-bottom: 4px;">
				<select style="min-width: 150px;" id="bulk_actions" name="bulk_actions">
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
						<?php if ( in_array( 'selected', $columns ) ) { ?>
							<th><input class='staddt_selected' type="checkbox" id="select_all" name="select_all" /></th>
						<?php } ?>
						
						<?php
						$cols = array(
							array( 'name' => 'id', 'caption' => 'ID' ),
							array( 'name' => 'title', 'caption' => 'Title' ),
							array( 'name' => 'position', 'caption' => 'Position' ),
							array( 'name' => 'visible', 'caption' => 'Visible' ),
							array( 'name' => 'time', 'caption' => 'Time Left' ),
							array( 'name' => 'impressions', 'caption' => 'Impressions' ),
							array( 'name' => 'clicks', 'caption' => 'Clicks' )
						);					
						foreach ( $cols as $col ) {
							if ( in_array( $col['name'], $columns ) ) {
						?>
								<th><?php administer_sort_link( $link, $col['name'], $sort, $order, __( $col['caption'], 'ad-minister' ) ); ?></th>
						<?php
							}
						} 
						?>
					</tr>
				</thead>

				<?php 
				$arr_keys = array_keys( $arr );
				if ( $order == 'down' ) $arr_keys = array_reverse( $arr_keys );
				$rownbr = 0;
				foreach ( $arr_keys as $i ) {
					$class = ( $rownbr++ % 2 ) ? $table['row-class'][$i] : $table['row-class'][$i] . ' alternate'; 
					if ( $filter ) {
						if ( $filter == 'visible' && $table['visible'][$i] != 'Yes' ) continue;
						if ( $filter == 'hidden' && $table['visible'][$i] == 'Yes' ) continue;
					}
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
							<td class='stat_position'>
								<?php 
								if ( empty( $table['position'][$i] ) ) {
									echo '-';
								}
								else if ( is_array( ($table['position'][$i]) ) ) {
									foreach ( $table['position'][$i] as $position ) {
										echo "<div>$position</div>";
									}
								}
								else 
									echo $table['position'][$i]; 
								?> 
								<?php //echo $table['weight'][$i]; ?>
							</td>
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