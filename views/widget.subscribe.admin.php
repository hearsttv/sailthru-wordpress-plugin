	<div id="icon-sailthru" class="icon32"></div>
	<h2><?php _e( 'Sailthru Subscribe', 'sailthru-for-wordpress' ); ?></h2>

	<?php

		$sailthru = get_option('sailthru_setup_options');
		$customfields = get_option('sailthru_forms_options');
		if( !is_array($sailthru) )
		{

			echo '<p>Please return to the <a href="' . esc_url( menu_page_url( 'sailthru_configuration_menu', false ) ) . '">Sailthru Settings screen</a> and set up your API key and secret before setting up this widget.</p>';
			return;

		}
		$api_key = $sailthru['sailthru_api_key'];
		$api_secret = $sailthru['sailthru_api_secret'];

		//$client = new Sailthru_Client( $api_key, $api_secret );
		$client = new WP_Sailthru_Client( $api_key, $api_secret);
			try {
				if ($client) {
					$res = $client->getLists();
				}
			}
			catch (Sailthru_Client_Exception $e) {
				//silently fail
				return;
			}
		
			
			$lists = $res['lists'];

	?>

        
        <div id="<?php echo $this->get_field_id('title'); ?>_div" style="display: block;">
            <p>
            	<label for="<?php echo $this->get_field_id('title'); ?>">
            		<?php _e('Widget Title:'); ?>
            		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            	</label>
            </p>
            <p>
			<?php
			//grab each custom form made by the admin and ask to show them
			foreach ($customfields as $section) {
			$section_stripped_name = str_replace(" ", '_', $section);
				if(!empty($section)){
				?>
					
			            <label for="<?php echo $this->get_field_id('show_'.$section.'_name'); ?>">
							<?php _e("Show '".$section."' field:"); ?> 
							<input id="<?php echo $this->get_field_id('show_'.$section_stripped_name.'_name'); ?>" name="<?php echo $this->get_field_name('show_'.$section.'_name'); ?>" type="checkbox" <?php echo (($instance['show_'.$section_stripped_name.'_name']) ? ' checked' : ''); ?> />
							
							<br> 
							
							Required? <input id="<?php echo $this->get_field_id('show_'.$section_stripped_name.'_required'); ?>" name="<?php echo $this->get_field_name('show_'.$section_stripped_name.'_required'); ?>" type="checkbox" <?php echo (($instance['show_'.$section_stripped_name.'_required']) ? ' checked' : ''); ?> /> <br>
							
							Field Type:
							
							<select id="<?php echo $this->get_field_id('show_'.$section.'_type'); ?>" name="<?php echo $this->get_field_name('show_'.$section.'_type'); ?>" value="<?php echo $instance['show_'.$section_stripped_name.'_type']; ?>">
								<option value="textbox" <?php selected( $instance['show_'.$section.'_type'], 'textbox' );?>>Textbox</option>
								<option value="password" <?php selected( $instance['show_'.$section.'_type'], 'password' );?>>Password</option>
								<option value="tel" <?php selected( $instance['show_'.$section.'_type'], 'tel' );?>>Telephone</option>
								<option value="date" <?php selected( $instance['show_'.$section.'_type'], 'date' );?>>Date</option>
								<option value="select" <?php selected( $instance['show_'.$section.'_type'], 'select' );?>>Select</option>
								<option value="radio" <?php selected( $instance['show_'.$section.'_type'], 'radio' );?>>Radio</option>
							</select>
													</label>
					<br>
					<?php
				}
				
	        }
            		?> 
				
					</p>
			<p>			
				<?php _e('Subscribe to list(s): '); ?>
				<?php
					foreach( $lists as $key => $list )
					{ 
						?>
						<br />
						<input type="checkbox" value="<?php echo esc_attr( $list['name'] ); ?>" name="<?php echo $this->get_field_name('sailthru_list'); ?>[<?php echo $key; ?>]" id="<?php echo esc_attr( $this->get_field_id('sailthru_list') . '-' . $key ); ?>" <?php checked($instance['sailthru_list'][$key], $list['name']); ?>  /> 
						<label for="<?php // echo esc_attr( $this->get_field_id('sailthru_list') . '-' . $key ); ?>"><?php echo esc_html( $list['name'] ); ?></label>
						<?php
						
					}
				?>
			</p>						
        </div>


