<?php

/* ------------------------------------------------------------------------ *
 * HORIZON SETUP
 * ------------------------------------------------------------------------ */
function sailthru_initialize_setup_options() {

	// If the section options don't exist, create them.
	if ( false == get_option( 'sailthru_setup_options' ) ) {
		add_option( 'sailthru_setup_options' );
	} // end if

	// get the options
	$setup = get_option( 'sailthru_setup_options' );


	add_settings_section(
		'sailthru_setup_section',   // ID used to identify this section and with which to register options
		__( 'Sailthru API Setup', 'sailthru-for-wordpress' ),    // Title to be displayed on the administration page
		'sailthru_setup_callback',   // Callback used to render the description of the section
		'sailthru_setup_options'   // Page on which to add this section of options
	);


	add_settings_field(
		'sailthru_form_name',     // ID used to identify the field throughout the theme
		__( 'Sailthru field name', 'sailthru-for-wordpress' ),     // The label to the left of the option interface element
		'sailthru_html_text_input_callback', // The name of the function responsible for rendering the option interface
		'sailthru_forms_options',   // The page on which this option will be displayed
		'sailthru_setup_section',   // The name of the section to which this field belongs
		array(        // The array of arguments to pass to the callback. In this case, just a description.
			'sailthru_setup_options',
			'sailthru_form_name',
			'',
			'sailthru_form_name'
		)
	);


	add_settings_field(
		'sailthru_api_key',     // ID used to identify the field throughout the theme
		__( 'Sailthru API Key', 'sailthru-for-wordpress' ),     // The label to the left of the option interface element
		'sailthru_html_text_input_callback', // The name of the function responsible for rendering the option interface
		'sailthru_setup_options',   // The page on which this option will be displayed
		'sailthru_setup_section',   // The name of the section to which this field belongs
		array(        // The array of arguments to pass to the callback. In this case, just a description.
			'sailthru_setup_options',
			'sailthru_api_key',
			'',
			'sailthru_api_key'
		)
	);

	add_settings_field(
		'sailthru_api_secret',
		__( 'Sailthru API Secret', 'sailthru-for-wordpress' ),
		'sailthru_html_text_input_callback',
		'sailthru_setup_options',
		'sailthru_setup_section',
		array(
			'sailthru_setup_options',
			'sailthru_api_secret',
			'',
			'sailthru_api_secret'
		)
	);

	add_settings_field(
		'sailthru_js_type',
		'Sailthru JavaScript Version',
		'sailthru_js_type_callback',
		'sailthru_setup_options',
		'sailthru_setup_section',
		array(
			'sailthru_setup_options',
			'sailthru_js_type',
			'',
			'sailthru_js_type'
		)
	);


	if ( isset( $setup['sailthru_js_type'] ) &&   $setup['sailthru_js_type'] != 'personalize_js' ) {


		add_settings_field(
			'sailthru_horizon_domain',
			'Sailthru Horizon Domain',
			'sailthru_html_text_input_callback',
			'sailthru_setup_options',
			'sailthru_setup_section',
			array(
				'sailthru_setup_options',
				'sailthru_horizon_domain',
				'',
				'sailthru_horizon_domain'
			)
		);


		add_settings_field(
			'sailthru_horizon_load_type',
			'Horizon Loading',
			'sailthru_horizon_loadtype_callback',
			'sailthru_setup_options',
			'sailthru_setup_section',
			array(
				'sailthru_setup_options',
				'sailthru_horizon_load_type',
				'',
				'sailthru_horizon_load_type'
			)
		);
	}

	// Add customer Id if personalize JS is being used.

	if ( isset( $setup['sailthru_js_type'] ) &&   $setup['sailthru_js_type'] == 'personalize_js' ) {

		add_settings_field(
			'sailthru_customer_id',
			'Customer Id',
			'sailthru_customer_id_callback',
			'sailthru_setup_options',
			'sailthru_setup_section',
			array(
				'sailthru_setup_options',
				'sailthru_customer_id',
				'',
				'sailthru_customer_id'
			)
		);

	}


	/*
	 * Sailthru options for overriding emails
	 * Add a new field for selecting the email template to use,
	 * but don't do this until we have an API key & secret to use.
	 */


	if ( isset( $setup['sailthru_api_key'] ) && ! empty ( $setup['sailthru_api_key'] ) &&
		isset( $setup['sailthru_api_secret'] ) && ! empty ( $setup['sailthru_api_secret'] ) ) {


		add_settings_section(
			'sailthru_email_setup_section',   // ID used to identify this section and with which to register options
			__( 'Email Settings', 'sailthru-for-wordpress' ),    // Title to be displayed on the administration page
			'sailthru_email_section_callback',   // Callback used to render the description of the section
			'sailthru_setup_options'   // Page on which to add this section of options
		);



		add_settings_field(
			'sailthru_setup_email_template', // ID used to identify the field throughout the theme
			__( 'Default Template', 'sailthru-for-wordpress' ),  // The label to the left of the option interface element
			'sailthru_setup_email_template_callback',
			'sailthru_setup_options',
			'sailthru_email_setup_section',
			array(
				'sailthru_setup_options',
				'sailthru_setup_email_template',
				'',
				'sailthru_setup_email_template',
				'When selected WordPress emails will be sent via Sailthru. Configure your template in Sailthru with {subject} as the subject line and include {body} in the editor.'

			)
		);

		add_settings_field(
			'sailthru_setup_new_user_override_template', // ID used to identify the field throughout the theme
			__( 'New User Registration', 'sailthru-for-wordpress' ),  // The label to the left of the option interface element
			'sailthru_setup_email_template_callback',
			'sailthru_setup_options',
			'sailthru_email_setup_section',
			array(
				'sailthru_setup_options',
				'sailthru_setup_new_user_override_template',
				'',
				'sailthru_setup_new_user_override_template',
				'Select this template to for new user accounts in your WordPress site. Newsletter signups can be configured using the List Signup Widget.'
			)
		);

	}


	// Finally, we register the fields with WordPress
	register_setting(
		'sailthru_setup_options',
		'sailthru_setup_options',
		'sailthru_setup_handler'
	);

} // end sailthru_initialize_setup_options
add_action( 'admin_init', 'sailthru_initialize_setup_options' );



/* ------------------------------------------------------------------------ *
 * Section Callbacks
 * ------------------------------------------------------------------------ */

/**
 * Provides a simple description for each setup page respectively.
 */

function sailthru_setup_callback() {
	echo '<p>Add your Sailthru API key , secret key and your Horizon domain. This can be found on the <a href="https://my.sailthru.com/settings_api">settings page</a> of the Sailthru dashboard.</p><p>Not sure what these are? Contact <a href="mailto:support@sailthru.com">support@sailthru.com</a> ';
} // end sailthru_setup_callback




/* ------------------------------------------------------------------------ *
 * Field Callbacks - Helpers to render form elements specific to this section
 * ------------------------------------------------------------------------ */
/**
 * Creates a checkbox for the Horizon JS output type
 *
 */
function sailthru_horizon_loadtype_callback() {

	$options = get_option( 'sailthru_setup_options' );
	$load_type = isset( $options['sailthru_horizon_load_type'] ) ? $options['sailthru_horizon_load_type'] : '';
	echo '<input type="checkbox" id="checkbox_example" name="sailthru_setup_options[sailthru_horizon_load_type]" value="1"' . checked( 1, esc_attr( $load_type ), false ) . '/>';
	echo '<small>Use synchronous loading for Horizon</small>';

}


/**
 * Creates a checkbox for the Sailthru JS output type
 *
 */
function sailthru_js_type_callback() {

	$options = get_option( 'sailthru_setup_options' );
	$js_type = isset( $options['sailthru_js_type'] ) ? $options['sailthru_js_type'] : '';

	$html_options = array( 'horizon_js' => 'Horizon JavaScript', 'personalize_js' => 'Sailthru Script Tag' );

	echo '<select id="sailthru_js_type" name="sailthru_setup_options[sailthru_js_type]">';
	foreach ( $html_options as $key => $val ) {

		if ( $key == $js_type ) {
			$selected = ' selected';
		} else {
			$selected = '';
		}
		echo '<option value="'.$key.'"'.$selected.'>'.$val.'</option>';
	}
	echo '</select>';


}

/**
 * Creates field for the customer Id
 *
 */
function sailthru_customer_id_callback() {

	$options = get_option( 'sailthru_setup_options' );
	$customer_id = isset( $options['sailthru_customer_id'] ) ? $options['sailthru_customer_id'] : '';
	echo '<input type="text" id="sailthru_customer_id" name="sailthru_setup_options[sailthru_customer_id]" value="' . esc_attr( $customer_id ) . '"/>';


}

/**
 * Creates a checkbox to use stored tags
 *
 */
function sailthru_personalize_stored_tags_callback() {

	$options = get_option( 'sailthru_setup_options' );
	$ignore_stored_tags = isset( $options['sailthru_ignore_personalize_stored_tags'] ) ? $options['sailthru_ignore_personalize_stored_tags'] : '';
	echo '<input type="checkbox" id="sailthru_ignore_personalize_stored_tags" name="sailthru_setup_options[sailthru_ignore_personalize_stored_tags]" value="1"' . checked( 1, esc_attr( $ignore_stored_tags ), true ) . '/>';

}


/**
 * Creates a section header for the emails
 *
 */
function sailthru_email_section_callback() {

	echo "<p>You can use Sailthru to deliver your WordPress emails.</p>";

}


/**
 * Creates a default template if there are none yet.
 *
 */
function sailthru_setup_email_template_callback( $args ) {

	$sailthru   = get_option( 'sailthru_setup_options' );
	if ( isset( $sailthru['sailthru_api_key'] ) && isset( $sailthru['sailthru_api_secret'] ) ) {
		$api_key    = $sailthru['sailthru_api_key'];
		$api_secret = $sailthru['sailthru_api_secret'];

		$client = new WP_Sailthru_Client( $api_key, $api_secret );
		try {
			if ( $client ) {
				$res = $client->getTemplates();
			}
		}
		catch ( Sailthru_Client_Exception $e ) {
			//silently fail
			return;
		}


		if ( isset( $res['error'] ) ) {

			$tpl =  array();

		} else {

			$tpl = $res['templates'];
		}

		// if there are no templates available create a basic one
		// since multiple settings use this callback, we do this
		// only if we're in setup mode:
		if ( isset( $arg[1] ) ) {
			$has_default_template = 'sailthru_setup_email_template';
		} else {
			$has_default_template = false;
		}
		if ( $has_default_template ) {

			if ( isset( $tpl ) || $tpl != '' ) {

				$name = get_bloginfo( 'name' );
				$email = get_bloginfo( 'admin_email' );
				try {

					if ( $sailthru_client ) {

						$template = 'default-template';
						$options = array(
							'from_name' => $name,
							'from_email' => $email,
							'content_html' => '{body}',
							'subject' => '{subject}' );
						$response = $client->saveTemplate( $template, $options );

					}
				} catch ( Sailthru_Client_Exception $e ) {
					//silently fail
					return;
				}

			}
		}

	}

	// Render the Drop Downs
	if ( isset( $tpl ) ) {
		$html = sailthru_create_dropdown( $args, $tpl );
	} else {
		$html = sailthru_create_dropdown( $args, array() );
		$html .= "Sailthru Api Key and Secret must be saved first";
	}

	echo $html;

}


/* ------------------------------------------------------------------------ *
 * Setting Callbacks
 * ------------------------------------------------------------------------ */




/**
 * Sanitize the text inputs, and don't let the horizon
 * domain get saved with either http:// https:// or www
 */
function sailthru_setup_handler( $input ) {


	$output = array();

	// api key
	if ( isset( $input['sailthru_api_key'] ) ) {
		$output['sailthru_api_key'] = filter_var( $input['sailthru_api_key'], FILTER_SANITIZE_STRING );
	} else {
		$output['sailthru_api_key'] = false;
	}

	// api secret
	if ( isset( $input['sailthru_api_secret'] ) ) {
		$output['sailthru_api_secret'] = filter_var( $input['sailthru_api_secret'], FILTER_SANITIZE_STRING );
	} else {
		$output['sailthru_api_secret'] = false;
	}


	if ( empty( $input['sailthru_customer_id'] ) && ( $input['sailthru_js_type'] == 'personalize_js' ) ) {
		add_settings_error( 'sailthru-notices', 'sailthru-horizon-domain-fail', __( 'Please enter your Sailthru Customer Id' ), 'error' );
	} else {
		// customer id
		if ( isset( $input['sailthru_customer_id'] ) ) {
			$output['sailthru_customer_id'] = filter_var( $input['sailthru_customer_id'], FILTER_SANITIZE_STRING );
		} else {
			$output['sailthru_customer_id'] = false;
		}
	}

	// javascript type
	if ( isset( $input['sailthru_js_type'] ) && ( $input['sailthru_js_type'] == 'personalize_js' ) ) {
		$output['sailthru_js_type'] = filter_var( $input['sailthru_js_type'], FILTER_SANITIZE_STRING );
	} else {
		$output['sailthru_js_type'] = false;
	}

	// Personalize JS - use stored tags
	if ( isset( $input['sailthru_ignore_personalize_stored_tags'] ) ) {
		$output['sailthru_ignore_personalize_stored_tags'] = 1;
	} else {
		$output['sailthru_ignore_personalize_stored_tags'] = false;
	}


	// horizon load type
	if ( isset( $input['sailthru_horizon_load_type'] ) ) {
		$output['sailthru_horizon_load_type'] = 1;
	} else {
		$output['sailthru_horizon_load_type'] = false;
	}


	// horizon domain
	if ( isset( $input['sailthru_horizon_domain'] ) ) {
		$output['sailthru_horizon_domain'] = filter_var( $input['sailthru_horizon_domain'], FILTER_SANITIZE_STRING );
	} else {
		$output['sailthru_horizon_domain'] = '';
	}


	// set errors
	if ( empty( $output['sailthru_api_secret'] ) ) {
		add_settings_error( 'sailthru-notices', 'sailthru-api-secret-fail', __( 'Sailthru will not function without an API secret.' ), 'error' );
	}

	if ( empty( $output['sailthru_api_key'] ) ) {
		add_settings_error( 'sailthru-notices', 'sailthru-api-key-fail', __( 'Sailthru will not function without an API key.' ), 'error' );
	}

	if ( empty( $output['sailthru_horizon_domain'] ) && ( $input['sailthru_js_type'] == 'horizon_js' ) ) {
		add_settings_error( 'sailthru-notices', 'sailthru-horizon-domain-fail', __( 'Please enter your Horizon domain.' ), 'error' );
	} else {

		$output['sailthru_horizon_domain'] = str_ireplace( 'http://', '', $output['sailthru_horizon_domain'] );
		$output['sailthru_horizon_domain'] = str_ireplace( 'https://', '', $output['sailthru_horizon_domain'] );
		$output['sailthru_horizon_domain'] = str_ireplace( 'www.', '', $output['sailthru_horizon_domain'] );

		// remove trailing
		if ( substr( $output['sailthru_horizon_domain'], -1 ) == '/' ) {
			$output['sailthru_horizon_domain'] = substr( $output['sailthru_horizon_domain'], 0, -1 );
		}
	}


	/*
	 * Of course we want to vaildate this field,
	 * but don't do this until we have an API key & secret to use.
	 */
	$setup = get_option( 'sailthru_setup_options' );

	if ( isset( $setup['sailthru_api_key'] ) && ! empty( $setup['sailthru_api_key'] ) &&
		isset( $setup['sailthru_api_secret'] ) && ! empty( $setup['sailthru_api_secret'] ) ) {

		// creates an email template if one does not already exist
		sailthru_create_wordpress_template();

		// sitewide email template
		if ( isset( $input['sailthru_setup_email_template'] ) ) {
			$output['sailthru_setup_email_template'] = trim( $input['sailthru_setup_email_template'] );
		} else {
			$output['sailthru_setup_email_template'] = false;
		}
	
		if ( isset( $input['sailthru_setup_new_user_override_template'] ) ) {
			$output['sailthru_setup_new_user_override_template'] = trim( $input['sailthru_setup_new_user_override_template'] );
		} else {
			$output['sailthru_setup_new_user_override_template'] = false;
		}

	}


	return $output;

}
// end sailthru_setup_handler
