<?php
function administer_position_template ($position = array(), $nbr = 0) {
	$key  = $position['position'] ?: '';
	$type = $position['type'] ?: '';
	$desc = $position['description'] ?: '';
	$class = $position['class'] ?: '';
	$rotate = $position['rotate'] ?: '';
	$rotate_time = $position['rotate_time'] ?: '';
	$rotating = ( ( $rotate == 'true' ) && $rotate_time ) ? 'Yes (' . $rotate_time . 's)' : 'No'; 
	$google_adsense_id = esc_html( $position['google_adsense_id'] ?: '' );
	$google_adsense_active = $position['google_adsense_active'] ?: 'false';
	if ( $google_adsense_id && ( $google_adsense_active != 'true' ) ) {
		$google_adsense_id = '<span style="color: red">' . $google_adsense_id . ' (Inactive)</span>';
	}

	// Set up css formatting
	$class =  ($nbr % 2) ? '' : 'alternate';
	$html = '<tr class="%class%">';
	$html .= ( $position['type'] == 'template' ) ? "<td class='staddt_selected'><input style='margin-left: 8px;' type='checkbox' name='selected_template_positions[]' value='" . esc_attr( $key ) . "' /></td>" : '';
	$html .= '<td style="white-space: nowrap;">' . esc_html( $key ) . '</td>';
	$html .= '<td>' . esc_html( $desc ) . '</td>';
	//$html .= '<td>' . esc_html($position['before']) . ' ' . esc_html($position['after']) . '</td>';
	$html .= '<td>' . esc_html( $class ) . '</td>';
	$html .= '<td>' . esc_html( $rotating ) . '</td>';
	$html .= '<td>' . $google_adsense_id . '</td>';
	$html .= '<td><a href="%url_edit%">' . __('Edit', 'ad-minister') . '</a> | <a href="%url_remove%">' . __('Remove', 'ad-minister') . '</a></td>';
	$html .= '</tr>';

	// Inject template values
	$html = str_replace('%url_edit%', administer_get_page_url( 'positions' ) . '&key=' . urlencode( $key ) . '&action=edit', $html);
	$html = str_replace('%url_remove%', administer_get_page_url( 'positions' ) . '&key=' . urlencode( $key ) . '&action=delete', $html);
	$html = str_replace('%class%', $class, $html);

	echo $html;	
}
?>	

<div class="wrap">
	<h2>Banner Positions</h2>

	<?php
	$positions = administer_get_positions();
	$rotate_time_default = administer_get_default_rotate_time();
	
	/*
	**   SAVE THE POSITION
	*/
	if ( isset( $_POST['save'] ) ) {			
		if ( $name = administer_get_post_var( 'position' ) ) {
			$ok = true;
			if ( administer_get_post_var( 'edit_position' ) != $name ) {
				if (array_key_exists($name, $positions)) {
					echo '<div id="message" class="updated fade"><p><strong>' . __('That position name already exists!') . '</strong></p></div>';
					$ok = false;
				}
			} 
			if ($ok) {
				$positions[$name]['position'] = stripslashes($name); 
				$positions[$name]['description'] = stripslashes( administer_get_post_var( 'description' ) );
				/*$positions[$name]['before'] = stripslashes($_POST['before']);
				$positions[$name]['after'] = stripslashes($_POST['after']);*/
				$positions[$name]['class'] = stripslashes( administer_get_post_var( 'class' ) );
				$positions[$name]['rotate'] = stripslashes( administer_get_post_var( 'rotate' ) ) ? 'true' : 'false';
				$positions[$name]['rotate_time'] = ( administer_get_post_var( 'rotate_time' ) ) ? stripslashes( administer_get_post_var( 'rotate_time' ) ) : $rotate_time_default;
				$positions[$name]['google_adsense_id'] = stripslashes( trim( administer_get_post_var( 'google_adsense_id' ) ) );
				$positions[$name]['google_adsense_active'] = ( administer_get_post_var( 'google_adsense_active' ) ) ? 'true' : 'false';
				if ( ! $positions[$name]['type'] ) $positions[$name]['type'] = 'widget';
				administer_update_positions( $positions );
				$_GET['key'] = $positions[$name]['position'];
				echo '<div id="message" class="updated fade"><p><strong>' . __('Position saved.') . '</strong></p></div>';

				do_action( 'administer_edit_position', $positions[$name] );
			} 
		}
	}
	
	if ( administer_get_post_var( 'action' ) == 'confirm_delete' ) {
		if ( $key = administer_get_post_var( 'key' ) ) {
			if (array_key_exists($key, $positions)) {

				// Remove the position
				unset($positions[$key]);
				administer_update_positions( $positions );

				// Orphan content if required
				$content = administer_get_content();
				foreach ($content as $con) {
					if ($con['position'] == $key) {
						$content[$con['id']]['position'] = '';
					}
				}
				administer_update_content( $content ); 
				
				echo '<div id="message" class="updated fade"><p><strong>' . __('Position deleted.') . '</strong></p></div>';
			} else echo '<div id="message" class="updated fade"><p><strong>' . __('Error! Cannot delete a position that does not exist.') . '</strong></p></div>';
		} 
		else {
			echo '<div id="message" class="updated fade"><p><strong>' . __('Error! Position key missing!') . '</strong></p></div>';
		}
	}

	if (empty($positions))
		echo '<div id="message" class="updated fade"><p><strong>' . __('Before you can add content you need to define some positions. These positions will be where your content appears.') . '</strong></p></div>';			

	if ( isset( $_POST['clear_all'] ) || isset( $_POST['clear_selected'] ) ) {
		if ( is_array( $positions ) ) {
			$selected_template_positions = isset( $_POST['selected_template_positions'] ) ? $_POST['selected_template_positions'] : array();	
			foreach ( $positions as $position ) {
				if ( $position['type'] != 'template' ) continue;
				if ( isset( $_POST['clear_selected'] ) && !in_array( $position['position'], $selected_template_positions ) ) continue;
				
				unset($positions[$position['position']]);
			}
			administer_update_positions( $positions );
		}
	}	

	$positions_t = array();
	$positions_w = array();
	if (is_array($positions)) {
		$positions_modified = false;
		foreach ($positions as $key => $position) {
			if ($position['position'] !== $key) {
				$position['position'] = $key;
				$positions[$key] = $position;
				$positions_modified = true;
			}
			
			if (!isset($position['rotate'])) {
				$position['rotate'] = 'true';
				$position['rotate_time'] = $rotate_time_default;
				$positions[$key] = $position;
				$positions_modified = true;
			}
			
			if (empty($position['google_adsense_id'])) {
				$google_adsense_id = administer_google_adsense_ad_slot_id( $position['position'] );
				if ($google_adsense_id) {
					$position['google_adsense_id'] = $google_adsense_id;
					if (empty($position['google_adense_active'])) {
						$position['google_adsense_active'] = 'true';
					}
					$positions[$key] = $position;
					$positions_modified = true;
				}
			}

			if (empty($position['type'])) {
				$position['type'] = 'widget';
				$positions[$key] = $position;
				$positions_modified = true;
			}
			
			if ($position['type'] == 'widget') 
				$positions_w[$position['position']] = $position;
			else if ($position['type'] == 'template') 
				$positions_t[$position['position']] = $position;
		}
		if ( $positions_modified )
			administer_update_positions( $positions );
	}
	ksort($positions_t);
	ksort($positions_w);
	?>

	<p><?php if ( administer_get_query_var( 'action' ) != 'delete' ) _e('Positions are just that, \'positions\' at which you want content to appear. A position can be defined within a template, such as in a header for banner-ads, or a position can be a widget, which can be dragged onto a sidebar. Each position may have an optional description and html code which wraps the content within a position. For example, this may be <em>&lt;div class=&quot;ads&quot;&gt;</em> before, and <em>&lt;/div&gt;</em> after.', 'ad-minister'); ?></p>

	<?php 
	if ( administer_get_query_var( 'action' ) == 'edit' ) { 
		$position = ( $key = $_GET['key'] ) ? $positions[$key] : array();
		$checked_rotate = ( $position['rotate'] == 'true' ) ? 'checked="checked"' : '';
		$checked_google_adsense_active = ( $position['google_adsense_active'] == 'true' ) ? 'checked="checked"' : '';
	?>

		<h3><?php _e('Create/edit position', 'ad-minister'); ?></h3>
	    <form method="POST" action="<?php administer_get_page_url( "positions" ); ?>">

			<?php 
			if ($position['type'] == 'template') {
				echo '<p><strong>' . __('Warning!', 'ad-minister') . '</strong> ';
				_e('You are editing a template position. If the description and wrapper code is also set in the template, then these values will reset when the template is reloaded.', 'ad-minister');
				echo '</p>';
			}
			?>
			<table class="form-table" id="position_edit">
				<tr id="position_edit_name">
					<th scope="row" valign="top"><?php _e('Name'); ?></th>
					<?php $type = ($position['position']) ? 'hidden' : 'text'; ?>
					<td><?php if ($type == 'hidden') echo $position['position']; ?><input type="<?php echo $type; ?>" name="position" size="100" value="<?php echo format_to_edit($position['position']); ?>"></td>	
				</tr>
				<tr id="position_edit_desc">
					<th scope="row" valign="top"><?php _e('Description'); ?></th>
					<td><input type="text" name="description" size="100" value="<?php echo format_to_edit($position['description']); ?>"></td>	
				</tr>
				<!--<tr id="positions_edit_before">
					<th scope="row" valign="top"><?php _e('Code before'); ?></th>
					<td><input type="text" name="before" size="100" value="<?php echo format_to_edit($position['before']); ?>"></td>	
				</tr>
				<tr id="positions_edit_after">
					<th scope="row" valign="top"><?php _e('Code after'); ?></th>
					<td><input type="text" name="after" size="100" value="<?php echo format_to_edit($position['after']); ?>"></td>	
				</tr>-->
				<tr id="positions_edit_class">
					<th scope="row" valign="top"><?php _e('Classes'); ?></th>
					<td><input type="text" name="class" size="100" value="<?php echo format_to_edit($position['class']); ?>"> <span class="info">(<?php _e('Separate classes with spaces', 'ad-minister'); ?>)</span></td>	
				</tr>
				<tr id="positions_edit_rotate">
					<th scope="row" valign="top"><?php _e('Rotate'); ?></th>
					<td><input type="checkbox" id="rotate" name="rotate" value="true" <?php echo $checked_rotate; ?> /> <span class="info">(<?php _e('Rotate ads without reloading?', 'ad-minister'); ?>)</span></td>	
				</tr>
				<?php $style = $checked_rotate ? '' : 'style="display: none;"'; ?>
				<tr id="positions_edit_rotate_time" <?php echo $style; ?> >
					<?php $rotate_time = isset( $position['rotate_time'] ) ? $position['rotate_time'] : $rotate_time_default; ?>
					<th scope="row" valign="top"><?php _e('Rotation Delay'); ?></th>
					<td><input type="number" style="width: 55px;" id="rotate_time" name="rotate_time" min="1" value="<?php echo format_to_edit( $rotate_time ); ?>"> <span class="info">(<?php _e('Time between ad rotations in seconds', 'ad-minister'); ?>)</span></td>	
				</tr>
				<tr id="positions_edit_google_adense_id">
					<th scope="row" valign="top"><?php _e('Google Adsense ID'); ?></th>
					<td>
						<input type="text" name="google_adsense_id" id="google_adsense_id" size="15" value="<?php echo format_to_edit($position['google_adsense_id']); ?>" /> 
						<input type="checkbox" stye="margin-left: 1.5rem" name="google_adsense_active" id="google_adsense_active" value="true" <?php echo $checked_google_adsense_active; ?> /> 
						<span class="info">(<?php _e('Allow Google Adsense ads?', 'ad-minister'); ?>)</span>
					</td>	
				</tr>
			</table>

			<p><input type="submit" class="button-primary" name="save" value="<?php _e('Save position'); ?>" /></p>
			<input type="hidden" name="edit_position" value="<?php echo format_to_edit($position['position']); ?>" />
		</form>
		
		<p><a href="<?php echo administer_get_page_url( 'positions' ); ?>" title="Go back to Positions page">Back to Positions</a></p>
		
	<?php 
	} 
	else if ( administer_get_query_var( 'action' ) == 'delete' ) {
		if ( $key = $_GET['key'] ) {
			$nbr = 0;
			$content = administer_get_content();
			foreach ($content as $con) {
				if ($con['position'] == $key) $nbr++;
			}			
	?>
			<div class="narrow">
				<p><?php _e('You are about to delete position', 'ad-minister'); ?>: <strong><?php echo esc_html( $key ); ?></strong></p>

				<?php if ($nbr) printf(__('There are %s ads currently attached to this position. Deleting this position will make those ads orphans.', 'ad-minister'), $nbr); ?>

				<p><?php _e('Are you sure you want to do this?', 'ad-minister'); ?></p>

				<?php 
					if ($position['type'] == 'template') {
						echo '<p><strong>' . __('Warning!', 'ad-minister') . '</strong> ';
						_e('You are about to delete a template position. If the positions is still declared within the template, then this position will re-appear when the template is re-loaded.', 'ad-minister');
						echo '</p>';
					}
				?>

				<form action='<?php echo administer_get_page_url( "positions" ); ?>' method='POST'>
					<table width="100%">
						<tr>
							<td><input type='button' class="button" value='<?php _e('No', 'ad-minister'); ?>' onclick="self.location='<?php echo administer_get_page_url( 'positions' ); ?>';" /></td>
							<td class="textright"><input type='submit' class="button" value='<?php _e('Yes', 'ad-minister'); ?>' /></td>
						</tr>
					</table>
					<?php echo wp_nonce_field(); ?>
					<input type='hidden' name='action' value='confirm_delete' />
					<input type='hidden' name='key' value='<?php echo esc_html( $key ); ?>' />
				</form>

				<table class="form-table" cellpadding="5">
					<tr class="alt">
						<th scope="row"><?php _e('Position', 'ad-minister'); ?></th>
						<td><?php echo esc_html( $key ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Description', 'ad-minister'); ?></th>
						<td><?php echo $positions[$key]['description']; ?></td>
					</tr>
					<!--<tr>
						<th scope="row"><?php _e('Wrapper', 'ad-minister'); ?></th>
						<td><?php echo esc_html($positions[$key]['before']); ?> <?php echo esc_html($positions[$key]['after']); ?></td>
					</tr>-->
					<tr>
						<th scope="row"><?php _e('Classes', 'ad-minister'); ?></th>
						<td><?php echo $positions[$key]['class']; ?></td>
					</tr>
				</table>
			</div>
	<?php
		}
	} 
	else {
	?>
		<form action='<?php echo administer_get_page_url( "positions" ); ?>' method='POST'>
			<div id="template-positions">
				<h3>Template Positions</h3>

				<p><?php _e('These are the positions defined within the theme that you are using.', 'ad-minister'); ?></p>

				<p>
					<input type='submit' class='button' name='clear_selected' value='<?php _e('Clear Selected Template Positions'); ?>' />
					<input type='submit' class='button' style='background-color: maroon; color: white;' name='clear_all' value='<?php _e('Clear All Template Positions'); ?>' />
				</p>
				<!--<p><a class="button" href="<?php echo administer_get_page_url( "positions&action=clear_t"); ?>"><?php _e('Reset Template Positions'); ?></a></p>-->

				<table class="widefat">
					<thead>
						<tr>
							<th><input class='staddt_selected' type="checkbox" id="select_all_template" name="select_all_template" /></th>
							<th class="positionKey" scope="col" style=""><?php _e('Position Name', 'ad-minister'); ?></th>
							<th class="templatePositionsDescription" scope="col"><?php _e('Description', 'ad-minister'); ?></th>
							<!--<th class="templateFunctions" scope="col" colspan="1"><?php _e('Wrapper', 'ad-minister'); ?></th>-->
							<th class="templateClasses" scope="col" colspan="1"><?php _e('Classes', 'ad-minister'); ?></th>
							<th class="templateRotating" scope="col"><?php _e('Rotating', 'ad-minister'); ?></th>
							<th class="templateGoogleAdsenseID" scope="col"><?php _e('Google Adsense ID', 'ad-minister'); ?></th>
							<th class="templatePositionsActions" scope="col"><?php _e('Actions', 'ad-minister'); ?></th>
						</tr>
					</thead>
					<tbody id="positions_body">
					<?php
					$nbr = 0;
					if ( !empty( $positions_t ) ) {
						foreach ( $positions_t as $position ) { 							
							administer_position_template($position, $nbr++);
						}
					} else { 
						$url = 'http://labs.dagensskiva.com/plugins/ad-minister/';
						$string = __('There are currently no template positions defined. %a%See the documentation%b% on how to do that', 'ad-minister');
						$string = str_replace('%a%', '<a href="' . $url . '">', $string);
						$string = str_replace('%b%', '</a>', $string);
						?>
						<tr class="alternate"><td colspan="4"><?php echo $string;  ?>.</td></tr>
				
				<?php } ?>
					</tbody>
				</table>
				
				<h3>Widget Positions</h3>
				
				<p><?php _e('Below are the Ad-minister widgets available to be <a href="widgets.php">placed on your blog</a> (On the widget page the positions below start with \'Ad: \').', 'ad-minister'); ?></p>

				<div id="widget-positions">
					<table class="widefat">
						<thead>
							<tr>
								<th class="positionKey" scope="col" style=""><?php _e('Widget Name', 'ad-minister'); ?></th>
								<th class="templatePositionsDescription" scope="col"><?php _e('Description', 'ad-minister'); ?></th>
								<!--<th class="templateFunctions" scope="col" colspan="1"><?php _e('Wrapper', 'ad-minister'); ?></th>-->
								<th class="templateClasses" scope="col" colspan="1"><?php _e('Classes', 'ad-minister'); ?></th>
								<th class="templateRotating" scope="col"><?php _e('Rotating', 'ad-minister'); ?></th>
								<th class="templateGoogleAdsenseID" scope="col"><?php _e('Google Adsense ID', 'ad-minister'); ?></th>
								<th class="templatePositionsActions" scope="col"><?php _e('Actions', 'ad-minister'); ?></th>
							</tr>
						</thead>
						<tbody id="widget_positions_body">

						<?php
						$nbr = 0;
						if (!empty($positions_w)) {
							foreach ($positions_w as $position) {
								administer_position_template($position, $nbr++);
							}
						} else echo '<tr class="alternate"><td colspan="4">' . __('There are currently no widget positions', 'ad-minister') . '.</td></tr>';
						?>
						
						</tbody>
					</table>	
				</div>
				<p><a class="button" href="<?php echo administer_get_page_url( "positions&action=edit" ); ?>">Add new widget position</a></p>
		</form>
	<?php
	}
	?>
</div>