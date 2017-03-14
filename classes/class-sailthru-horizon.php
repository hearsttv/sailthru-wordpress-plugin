<?php

class Sailthru_Horizon {

	protected $admin_views = array();

	 // Represents the nonce value used to save the post media
	 private $nonce = 'wp_sailthru_nonce';


	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/

	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() {



		// Load plugin text domain
		add_action( 'init', array( $this, 'sailthru_init' ) );

		// Register admin styles and scripts
		// Documentation says: admin_print_styles should not be used to enqueue styles or scripts on the admin pages. Use admin_enqueue_scripts instead.
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );

		// Register Horizon Javascripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_scripts' ) );

		// Register the menu
		add_action( 'admin_menu', array( $this, 'sailthru_menu') );

		// Register the Horizon meta tags
	    add_action( 'wp_head', array( $this, 'sailthru_horizon_meta_tags' ) );


	 	// Setup the meta box hooks
	 	add_action( 'add_meta_boxes', array( $this, 'sailthru_post_metabox' ) );
	 	add_action( 'save_post', array( $this, 'save_custom_meta_data' ) );


	 	// Check for updates and make changes as needed
	 	add_action( 'plugins_loaded', array( $this, 'sailthru_update_check' ) );


	} // end constructor

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin
	 * 			uses "Network Activate" action, false if WPMU is
	 * 			disabled or plugin is activated on an individual blog
	 */
	public static function activate( $network_wide ) {

		if ( ! current_user_can( 'activate_plugins' ) )
            return;

        // signal that it's ok to override Wordpress's built-in email functions
		if( false == get_option( 'sailthru_override_wp_mail' ) ) {
			add_option( 'sailthru_override_wp_mail', 1 );
		} // end if
	

	} // end activate

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin
	 * 			uses "Network Activate" action, false if WPMU is
	 * 			disabled or plugin is activated on an individual blog
	 */
	public static function deactivate( $network_wide ) {

		if ( ! current_user_can( 'activate_plugins' ) )
            return;

		// stop overriding Wordpress's built in email functions
		if( false != get_option( 'sailthru_override_wp_mail' ) ) {
			delete_option( 'sailthru_override_wp_mail' );
		}

		// we don't know if the API keys, etc, will still be
		// good, so kill the flag that said we knew.
		if( false != get_option( 'sailthru_setup_complete') ) {
			delete_option( 'sailthru_setup_complete' );
		}

		// remove all setup information including API key info
		if( false != get_option( 'sailthru_setup_options') ) {
			delete_option( 'sailthru_setup_options' );
		}

		// remove concierge settings
		if( false != get_option( 'sailthru_concierge_options') ) {
			delete_option( 'sailthru_concierge_options' );
		}

		// remove scout options
		if( false != get_option( 'sailthru_scout_options') ) {
			delete_option( 'sailthru_scout_options' );
		}

		// remove custom fields options
		if( false != get_option( 'sailthru_forms_options') ) {
			delete_option( 'sailthru_forms_options' );
		}

			if( false != get_option('sailthru_customfields_order_widget') ) {
				delete_option('sailthru_customfields_order_widget');
			}

			if( false != get_option('sailthru_customfields_order') ) {
				delete_option('sailthru_customfields_order');
			}

			if( false != get_option('sailthru_forms_key') ) {
				delete_option('sailthru_forms_key');
			}


		// remove integrations options
		if( false != get_option('sailthru_integrations_options') ) {
			delete_option( 'sailthru_integrations_options' );
		}


		//remove plugin version number
		if( false != get_option('sailthru_plugin_version') ) {
			delete_option( 'sailthru_plugin_version' );
		}



	} // end deactivate

	/**
	 * Fired when the plugin is uninstalled.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin
	 * 			uses "Network Activate" action, false if WPMU is
	 * 			disabled or plugin is activated on an individual blog
	 */
	public static function uninstall( $network_wide ) {
		// nothing to see here.
	} // end uninstall


	/**
	 * Fired after plugins are loaded.
	 * Checks versions to see if changes
	 * need to be made to the database
	 */
	public static function sailthru_update_check() {

		$sailthru_plugin_version = get_option( 'sailthru_plugin_version' );
		
		// changes to <3.0.5
		// delete custom subscribe widget fields from the database,
		// don't just hide them.
		if ( $sailthru_plugin_version <= '3.0.5' || $sailthru_plugin_version ==  false) {

			$customfields  = get_option( 'sailthru_forms_options' );
			$key           = get_option( 'sailthru_forms_key' );	

			$updatedcustomfields = array();		

			if ( isset($customfields) && !empty($customfields)) {

				for ( $i = 0; $i <= $key; $i++ ) {
					if( isset($customfields[$i]['sailthru_customfield_name']) 
							and !empty($customfields[$i]['sailthru_customfield_name']) ) {

						// save non-empty custom fields to the database
						$updatedcustomfields[$i] = $customfields[$i];

					} else {

						// don't save empty custom fields to the database
						// we're pruning them.
					}

				}

				update_option( 'sailthru_forms_options', $updatedcustomfields );
				
			} // end if $customfields isset

		}

	}




	/**
 	* Loads the plugin text domain for translation
 	*/
	public function sailthru_init() {


		$domain = 'sailthru-for-wordpress-locale';
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
        	load_textdomain( $domain, SAILTHRU_PLUGIN_PATH . $domain . '-' . $locale . '.mo' );
        	load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

        	// Add a thumbnail size for concierge
        	add_theme_support( 'post-thumbnails' );
        	add_image_size( 'concierge-thumb', 50, 50 );


	} // end plugin_textdomain



	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */
	public function register_admin_scripts( $hook ) {

		/* loads the admin js and css for editing/creating posts to make sure this
			is only loaded in context rather than everywhere in the admin screens
		*/

		$screens = array('post-new.php', 'post.php', 'edit.php');

		if (in_array($hook, $screens)) {

			if (isset($_GET['action'])) {
				if ($_GET['action'] == 'edit') {
					// datepicker for the meta box on post pages
					wp_enqueue_script('jquery-ui-datepicker', array('jquery'));
					wp_enqueue_style( 'jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
					// our own magic
					wp_enqueue_script( 'sailthru-for-wordpress-admin-script', SAILTHRU_PLUGIN_URL . 'js/admin.js' , array('jquery') );
				}
			}
		}

		// abandon if we're not on our own pages.
		if( !stristr( $hook, 'sailthru' ) &&
				!stristr( $hook, 'horizon' ) &&
					!stristr( $hook, 'scout' ) &&
						!stristr( $hook, 'concierge' ) &&
							!stristr( $hook, 'settings_configuration_page' ) ) {
			return;
		}

		// loads the admin js and css for the configuration pages
		wp_enqueue_script( 'sailthru-for-wordpress-admin-script', SAILTHRU_PLUGIN_URL . 'js/admin.js' , array('jquery') );
		wp_enqueue_style( 'sailthru-for-wordpress-admin-styles', SAILTHRU_PLUGIN_URL . 'css/admin.css'  );

	} // end register_admin_scripts



	/**
	 * Registers and enqueues Horizon for every page, but only if setup has been completed.
	 */
	public function register_plugin_scripts() {

		// Check first, otherwise js could throw errors
		if( get_option('sailthru_setup_complete') ) {


			// we're not going to pass the enitre set of options
			// through to be seen in the html source so just stick it in this var
			$options = get_option('sailthru_setup_options');


			// check what JS version is being used. 

			if ( isset ( $options['sailthru_js_type'] ) &&  $options['sailthru_js_type'] == 'personalize_js')  {
				
				// Load Personalize JS

				$customer_id = $options['sailthru_customer_id'];

				// and then grab only what we need and put it in this var
				$params = array();
				$params['sailthru_customer_id'] = $customer_id;

				wp_enqueue_script( 'personalize_js', '//ak.sail-horizon.com/onsite/personalize.v0.0.4.min.js', array( 'jquery' ) );
				add_action('wp_footer', array( $this, 'sailthru_client_personalize' ), 10);

			} else {

				$horizon_domain = $options['sailthru_horizon_domain'];

				// and then grab only what we need and put it in this var
				$params = array();
				$params['sailthru_horizon_domain'] = $horizon_domain;

				add_action('wp_footer', array( $this, 'sailthru_client_horizon' ), 10);

				// A handy trick to update the parameters in js files
				wp_localize_script( 'sailthru-horizon-params', 'Horizon', $params );

				// Horizon parameters.
				wp_enqueue_script( 'sailthru-horizon-params' );
			}			

		} // end if sailthru setup is complete

	} // end register_plugin_scripts




		/*-------------------------------------------
	 * Create the Horizon Script for the <strike>page footer</strike>  page body.
	 *------------------------------------------*/
	 function sailthru_client_personalize() {

	 	$options = get_option('sailthru_setup_options');
	 	$customer_id = isset( $options['sailthru_customer_id'] ) ? $options['sailthru_customer_id'] : '';

	 	// if the content API has been disabled by a filter set stored tags to false
	 	if( false === apply_filters( 'sailthru_content_api_enable', true ) ) {
			$stored_tags = 'false';
		} else {
			if ( !$options['sailthru_ignore_personalize_stored_tags'] || !isset( $options['sailthru_ignore_personalize_stored_tags'] ) ) {
	 			$stored_tags = 'true';
	 		} else {
	 			// default setting
	 			$stored_tags = 'false';
	 		}
		}

	 	$customer_id = isset( $options['sailthru_customer_id'] ) && ($options['sailthru_customer_id']) ? $options['sailthru_customer_id'] : '';

		$js = "<script type=\"text/javascript\">\n";
        $js .= "var customerId = '".esc_attr( $customer_id ) ."'\n";
        $js .= "var SPM = Sailthru.SPM;\n";
        $js .= "SPM.setup(customerId, {\n";
        $js .= "autoTrackPageviews: true,\n";
		$js .= "useStoredTags: ". esc_attr( $stored_tags ) ."\n";
        $js .= "});\n";
 		$js .= "</script>\n";

		if ( !is_404() &&  !is_preview() ) {
			echo $js;
		}

	 } // end sailthru_client_horizon



	/*-------------------------------------------
	 * Create the Horizon Script for the <strike>page footer</strike>  page body.
	 *------------------------------------------*/
	 function sailthru_client_horizon() {

	 	// get the client's horizon domain
	 	$options = get_option('sailthru_setup_options');

	 	$concierge = get_option('sailthru_concierge_options');
	 		$concierge_from = isset($concierge['sailthru_concierge_from']) ? $concierge['sailthru_concierge_from'] : 'bottom';

	 		// threshold
	 		if( !isset($concierge['sailthru_concierge_threshold']) ) {
	 			$concierge_threshold = 'threshold: 500,';
	 		} else {
	 			$concierge_threshold =  strlen($concierge['sailthru_concierge_threshold']) ? "threshhold: ".intval( $concierge['sailthru_concierge_threshold'] ) .",": 'threshold: 500,';
	 		}

	 		// delay
	 		$concierge_delay = isset($concierge['sailthru_concierge_delay']) ? $concierge['sailthru_concierge_delay'] : '500';

	 		// offset
	 		$concierge_offset = isset($concierge['sailthru_concierge_offsetBottom']) ? $concierge['sailthru_concierge_offsetBottom'] : '20';

	 		// cssPath
	 		if( !isset($concierge['sailthru_concierge_cssPath']) ) {
	 			$concierge_css = 'https://ak.sail-horizon.com/horizon/recommendation.css';
	 		} else {
				$concierge_css = strlen($concierge['sailthru_concierge_cssPath']) > 0 ? $concierge['sailthru_concierge_cssPath']  :  'https://ak.sail-horizon.com/horizon/recommendation.css';
	 		}

	 		// filter
	 		if( !isset($concierge['sailthru_concierge_filter']) ) {
	 			$concierge_filter = '';
	 		} else {
	 			//remove whitespace around the commas
	 			$tags_filtered = preg_replace("/\s*([\,])\s*/", "$1", $concierge['sailthru_concierge_filter']);
	 			$concierge_filter = strlen($concierge['sailthru_concierge_filter']) >  0 ? "filter: {tags: '". esc_js($tags_filtered) ."'}" : '';
	 		}


	 	// check if concierge is on
	 	if ( isset($concierge['sailthru_concierge_is_on']) && $concierge['sailthru_concierge_is_on'] == 1 ) {
	 		$horizon_params = "domain: '".$options['sailthru_horizon_domain']."',concierge: {
	 			from: '". esc_js( $concierge_from ) ."',
	 			". esc_js( $concierge_threshold ) ."
	 			delay: ". esc_js( $concierge_delay ) .",
	 			offsetBottom: ". esc_js( $concierge_offset ) .",
	 			cssPath: '". esc_js( $concierge_css ) ."',
	 			$concierge_filter
	 		}";

		} else {
			$horizon_params =   "domain:'" . esc_js( $options['sailthru_horizon_domain'] ) ."'";
		}

		if ($options['sailthru_horizon_load_type'] == '1') {
			$horizon_js =  "<!-- Sailthru Horizon Sync -->\n";
			$horizon_js .= "<script type=\"text/javascript\" src=\"//ak.sail-horizon.com/horizon/v1.js\"></script>\n";
			$horizon_js .= "<script type=\"text/javascript\">\n";
			$horizon_js .= "jQuery(function() { \n";
			$horizon_js .= "  if (window.Sailthru) {\n";
			$horizon_js .= "           Sailthru.setup({\n";
			$horizon_js .= "              ". $horizon_params ."\n";
			$horizon_js .= "         });\n";
			$horizon_js .= "  }\n";
			$horizon_js .= "});\n";
			$horizon_js .= " </script>\n";
		} else {
			$horizon_js  = "<!-- Sailthru Horizon  Async-->\n";
			$horizon_js .= "<script type=\"text/javascript\">\n";
			$horizon_js .= "(function() {\n";
			$horizon_js .= "     function loadHorizon() {\n";
			$horizon_js .= "           var s = document.createElement('script');\n";
			$horizon_js .= "           s.type = 'text/javascript';\n";
			$horizon_js .= "          s.async = true;\n";
			$horizon_js .= "          s.src = location.protocol + '//ak.sail-horizon.com/horizon/v1.js';\n";
			$horizon_js .= "         var x = document.getElementsByTagName('script')[0];\n";
			$horizon_js .= "         x.parentNode.insertBefore(s, x);\n";
			$horizon_js .= "      }\n";
			$horizon_js .= "     loadHorizon();\n";
			$horizon_js .= "      var oldOnLoad = window.onload;\n";
			$horizon_js .= "      window.onload = function() {\n";
			$horizon_js .= "          if (typeof oldOnLoad === 'function') {\n";
			$horizon_js .= "            oldOnLoad();\n";
			$horizon_js .= "         }\n";
			$horizon_js .= "           Sailthru.setup({\n";
			$horizon_js .= "              ". $horizon_params ."\n";
			$horizon_js .= "         });\n";
			$horizon_js .= "     };\n";
			$horizon_js .= "  })();\n";
			$horizon_js .= " </script>\n";
		}

		if ( !is_404() &&  !is_preview() ) {
			echo $horizon_js;
		}

	 } // end sailthru_client_horizon



	/*--------------------------------------------*
	 * Core Functions
	 *---------------------------------------------*/
	/**
	 * Add a top-level Sailthru menu and its submenus.
	 */
	function sailthru_menu() {

		$sailthru_menu = add_menu_page(
			'Sailthru',											// The value used to populate the browser's title bar when the menu page is active
			__( 'Sailthru', 'sailthru-for-wordpress' ),			// The text of the menu in the administrator's sidebar
			'manage_options',									// What roles are able to access the menu
			'sailthru_configuration_page',						// The ID used to bind submenu items to this menu
			array( &$this, 'load_sailthru_admin_display'),		// The callback function used to render this menu
			SAILTHRU_PLUGIN_URL . 'img/sailthru-menu-icon.png' 	// The icon to represent the menu item
		);
		$this->admin_views[$sailthru_menu] = 'sailthru_configuration_page';

			$redundant_menu = add_submenu_page(
				'sailthru_configuration_page',
				__( 'Welcome', 'sailthru-for-wordpress' ),
				__( 'Welcome', 'sailthru-for-wordpress' ),
				'manage_options',
				'sailthru_configuration_page',
				array( &$this, 'load_sailthru_admin_display')
			);
			$this->admin_views[$redundant_menu] = 'sailthru_configuration_page';


			$settings_menu = add_submenu_page(
				'sailthru_configuration_page',
				__( 'Settings', 'sailthru-for-wordpress' ),
				__( 'Settings', 'sailthru-for-wordpress' ),
				'manage_options',
				'settings_configuration_page',
				array( &$this, 'load_sailthru_admin_display')
			);
			$this->admin_views[$settings_menu] = 'settings_configuration_page';




			$concierge_menu = add_submenu_page(
				'sailthru_configuration_page',							// The ID of the top-level menu page to which this submenu item belongs
				__( 'Concierge Options', 'sailthru-for-wordpress' ),	// The value used to populate the browser's title bar when the menu page is active
				__( 'Concierge Options', 'sailthru-for-wordpress' ),	// The label of this submenu item displayed in the menu
				'manage_options',										// What roles are able to access this submenu item
				'concierge_configuration_page',							// The ID used to represent this submenu item
				array( &$this, 'load_sailthru_admin_display')			// The callback function used to render the options for this submenu item
			);
			$this->admin_views[$concierge_menu] = 'concierge_configuration_page';



			$scout_menu = add_submenu_page(
				'sailthru_configuration_page',
				__( 'Scout Options', 'sailthru-for-wordpress' ),
				__( 'Scout Options', 'sailthru-for-wordpress' ),
				'manage_options',
				'scout_configuration_page',
				array( &$this, 'load_sailthru_admin_display')
			);
			$this->admin_views[$scout_menu] = 'scout_configuration_page';

			$scout_menu = add_submenu_page(
				'sailthru_configuration_page',
				__( 'Subscribe Widget Fields', 'sailthru-for-wordpress' ),
				__( 'Subscribe Widget Fields', 'sailthru-for-wordpress' ),
				'manage_options',
				'custom_fields_configuration_page',
				array( &$this, 'load_sailthru_admin_display')
			);
			$this->admin_views[$scout_menu] = 'customforms_configuration_page';

			$forms_menu = add_submenu_page(
				'customforms_configuration_page',
				__( 'Custom Forms', 'sailthru-for-wordpress' ),
				__( 'Custom Forms', 'sailthru-for-wordpress' ),
				'manage_options',
				'customforms_configuration_page',
				array( &$this, 'load_sailthru_admin_display')
			);
			$this->admin_views[$forms_menu] = 'customforms_configuration_page';

	} // end sailthru_menu

	/**
	 * Renders a simple page to display for the theme menu defined above.
	 */
	function load_sailthru_admin_display( ) {

		$active_tab = empty($this->views[current_filter()]) ? '' : $this->views[current_filter()] ;
		// display html
		include( SAILTHRU_PLUGIN_PATH . 'views/admin.php' );

	} // end sailthru_admin_display

	/**
 	 * Renders Horizon specific meta tags in the <head></head>
	 */
	function sailthru_horizon_meta_tags() {

		// only do this on pages and posts
		if( ! is_single() ) {
			return;
		}

		// filter to disable all output
		if( false === apply_filters( 'sailthru_horizon_meta_tags_enable', true ) )
			return;

		global $post;

    	$post_object = get_post();

    	$horizon_tags = array();

    		// date
    		$post_date = get_the_date( 'Y-m-d H:i:s' );
    			$horizon_tags['sailthru.date'] =  esc_attr($post_date);

    		// title
    		$post_title = get_the_title();
    			$horizon_tags['sailthru.title'] = esc_attr($post_title);

    		// tags in the order of priority
    		// first sailthru tags
    		$post_tags = get_post_meta( $post_object->ID, 'sailthru_meta_tags', true);

	    		// wordpress tags
	    		if( empty( $post_tags ) ) {
		    		$post_tags = get_the_tags();
		    		if( $post_tags ) {
						$post_tags = esc_attr(implode( ', ', wp_list_pluck( $post_tags, 'name' )) );
		    		}
	    		}

	    			// wordpress categories
	    			if( empty( $post_tags ) ) {
	    				$post_categories = get_the_category( $post_object->ID );
	    				foreach( $post_categories as $post_category ) {
	    					$post_tags .= $post_category->name . ', ';
	    				}
	    				$post_tags = substr($post_tags, 0, -2);
	    			}

			if ( ! empty( $post_tags ) ) {
				$horizon_tags['sailthru.tags'] = $post_tags;
			}


    		// author << works on display name. best option?
    		$post_author = get_the_author();
    		if( ! empty( $post_author ) )
    			$horizon_tags['sailthru.author'] = $post_author;

    		// description
    		$post_description = get_the_excerpt();
    		if( empty( $post_description ) ) {
				$excerpt_length = 250;
				// get the entire post and then strip it down to just sentences.
				$text = $post_object->post_content;
				$text = apply_filters( 'the_content', $text );
				$text = str_replace( ']]>', ']]>', $text );
				$text = strip_shortcodes( $text );
				$text = strip_tags( $text );
				$text = substr( $text, 0, $excerpt_length );
				$post_description = $this->reverse_strrchr( $text, '.', 1 );
    		}
    		$horizon_tags['sailthru.description'] = esc_html($post_description);

    		// image & thumbnail
			if( has_post_thumbnail( $post_object->ID ) ) {
				$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
				$thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'concierge-thumb' );

	    		$post_image = $image[0];
	    			$horizon_tags['sailthru.image.full'] = esc_attr($post_image);
	    		$post_thumbnail = $thumb[0];
	    			$horizon_tags['sailthru.image.thumb'] = $post_thumbnail;
			}

			// expiration date
			$post_expiration = get_post_meta( $post_object->ID, 'sailthru_post_expiration', true );

			if( ! empty( $post_expiration ) )
				$horizon_tags['sailthru.expire_date'] = esc_attr($post_expiration);

			$horizon_tags = apply_filters( 'sailthru_horizon_meta_tags', $horizon_tags, $post_object );

			$tag_output = "\n\n<!-- BEGIN Sailthru Horizon Meta Information -->\n";
			foreach ( (array) $horizon_tags as $tag_name => $tag_content ) {
				if ( empty( $tag_content ) )
					continue; // Don't ever output empty tags
				$meta_tag = sprintf( '<meta name="%s" content="%s" />', esc_attr( $tag_name ), esc_attr( $tag_content ) );
				$tag_output .= apply_filters( 'sailthru_horizon_meta_tags_output', $meta_tag );
				$tag_output .= "\n";
			}
			$tag_output .= "<!-- END Sailthru Horizon Meta Information -->\n\n";

    	echo $tag_output;

	} // sailthru_horizon_meta_tags


	/*-------------------------------------------
	 * Utility Functions
	 *------------------------------------------*/

	/*
	 * Returns the portion of haystack which goes until the last occurrence of needle
	 * Credit: http://www.wprecipes.com/wordpress-improved-the_excerpt-function
	 */
	function reverse_strrchr($haystack, $needle, $trail) {
	    return strrpos($haystack, $needle) ? substr($haystack, 0, strrpos($haystack, $needle) + $trail) : false;
	}


	/*--------------------------------------------*
	 * Hooks
	 *--------------------------------------------*/

	/**
	 * Introduces the meta box for expiring content,
	 * and a meta box for Sailthru tags.
	 */
	public function sailthru_post_metabox() {

		add_meta_box(
			'sailthru-post-data',
			__( 'Sailthru Post Data', 'sailthru' ),
			array( $this, 'post_metabox_display' ),
			'post',
			'side',
			'high'
		);

	} // sailthru_post_metabox

	/**
	 * Adds the input box for the post meta data.
	 *
	 * @param		object	$post	The post to which this information is going to be saved.
	 */
	public function post_metabox_display( $post ) {


		$sailthru_post_expiration = get_post_meta( $post->ID, 'sailthru_post_expiration', true);
		$sailthru_meta_tags = get_post_meta( $post->ID, 'sailthru_meta_tags', true);

		wp_nonce_field( plugin_basename( __FILE__ ), $this->nonce );

		// post expiration
		$html  = '<p><strong>Sailthru Post Expiration</strong></p>';
		$html .= '<input id="sailthru_post_expiration" type="text" placeholder="YYYY-MM-DD" name="sailthru_post_expiration" value="' . esc_attr($sailthru_post_expiration) . '" size="25" class="datepicker" />';
		$html .= '<p class="description">';
		$html .= 'Flash sales, events and some news stories should not be recommended after a certain date and time. Use this Sailthru-specific meta tag to prevent Horizon from suggesting the content at the given point in time. <a href="http://docs.sailthru.com/documentation/products/horizon-data-collection/horizon-meta-tags" target="_blank">More information can be found here</a>.';
		$html .= '</p><!-- /.description -->';


		// post meta tags
		$html .= '<p>&nbsp;</p>';
		$html .= '<p><strong>Sailthru Meta Tags</strong></p>';
		$html .= '<input id="sailthru_meta_tags" type="text" name="sailthru_meta_tags" value="' . esc_attr($sailthru_meta_tags) . '" size="25"  />';
		$html .= '<p class="description">';
		$html .= 'Tags are used to measure user interests and later to send them content customized to their tastes.';
		$html .= '</p><!-- /.description -->';
		$html .= '<p class="howto">Separate tags with commas</p>';



		echo $html;

	} // end post_media

	/**
	 * Determines whether or not the current user has the ability to save meta data associated with this post.
	 *
	 * @param		int		$post_id	The ID of the post being save
	 * @param		bool				Whether or not the user has the ability to save this post.
	 */
	public function save_custom_meta_data( $post_id ) {

		// First, make sure the user can save the post
		if( $this->user_can_save( $post_id, $this->nonce ) ) {

			// Did the user set an expiry date, or are they clearing an old one?
			if( ! empty( $_POST['sailthru_post_expiration'] ) && isset( $_POST['sailthru_post_expiration'] )
					|| get_post_meta($post_id, 'sailthru_post_expiration', TRUE) ) {

				$expiry_time = strtotime( $_POST['sailthru_post_expiration'] );
				if ( $expiry_time ) {
					$expiry_date = date( 'Y-m-d', $expiry_time );

					// Save the date. hehe.
					update_post_meta( $post_id, 'sailthru_post_expiration', esc_attr( $expiry_date) );
				}

			} // end if

			// Did the user set some meta tags, or are they clearing out old tags?
			if( ! empty( $_POST['sailthru_meta_tags'] ) && isset( $_POST['sailthru_meta_tags'] )
					|| get_post_meta($post_id, 'sailthru_meta_tags', TRUE) ) {

				//remove trailing comma
				$meta_tags = rtrim( $_POST['sailthru_meta_tags'], ',');
				update_post_meta( $post_id, 'sailthru_meta_tags', esc_attr( $meta_tags ) );

			}

		} // end if

	} // end save_custom_meta_data

	/*--------------------------------------------*
	 * Helper Functions
	 *--------------------------------------------*/

	/**
	 * FROM: https://github.com/tommcfarlin/WordPress-Upload-Meta-Box
	 * Determines whether or not the current user has the ability to save meta data associated with this post.
	 *
	 * @param		int		$post_id	The ID of the post being save
	 * @param		bool				Whether or not the user has the ability to save this post.
	 */
	function user_can_save( $post_id, $nonce ) {

	    $is_autosave = wp_is_post_autosave( $post_id );
	    $is_revision = wp_is_post_revision( $post_id );
	    $is_valid_nonce = ( isset( $_POST[ $nonce ] ) && wp_verify_nonce( $_POST[ $nonce ], plugin_basename( __FILE__ ) ) );

	    // Return true if the user is able to save; otherwise, false.
	    return ! ( $is_autosave || $is_revision ) && $is_valid_nonce;

	} // end user_can_save


} // end class
