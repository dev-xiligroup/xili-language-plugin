<?php
/*
was Plugin xili-xl-bbp-addon
Now as api
*/
/*
Changelog:
2.18.0 - now as xili-language api if bbPress active and option set
2.17.0 - test with 4.2-RC1
2.16.4 - test with 4.2-beta2
2.16.0 - 20141221 - ready for twentyfifteen and 4.1 -
2.15.3 - 20140915 - tests for WP 4.1-beta2
2.15.2 - 20140915 - use get_WPLANG() for 4.0
2.14.0 - 20140606 - bbPress filter updated
2.11.3 - 20140421 - Maintenance release
2.10.0 - 20140201 - Maintenance release
2.9.30 - 20131212 - Maintenance release
2.9.21 - 20131124 - Maintenance release
2.9.11 - 20131103 - Maintenance release
2.9.1 - 20130922 - add filter to manage group
2.8.9 - 20130524 - exists function for XTT
2.8.8 - 20130420 - Filter for bbPress 2.3
2.8.6 - 20130322 - Maintenance release
2.8.5 - 20130304 - Maintenance release
2.8.4.3 - 20130222 - Add help tag in settings - tested functions included inside class
2.8.4.2 - 20130216 - Maintenance release
2.8.4.1 - 20130202 - Maintenance release
2.8.4 - 20130127 - Maintenance release
2.8.3.1 - 20130106 - Maintenance release, fixes class
2.8.3 - 20130104 - Maintenance release
2.8.1 - 20120915 - Initial release as class

*/
/**
 * @package xili-language
 */
define('XILIXLBBPADDON_VER','2.18.0');

class xili_xl_bbp_addon {

	var $plugin_name = 'xili-xl-bbp-addon'; // filename and folder
	var $plugin_folder = 'xili-language';
	var $display_plugin_name = '©xili xl-bbPress add-on'; // menu and top
	var $settings_name = 'xili-xl-bbp-addon_settings'; // The settings string name for this plugin in options table
	var $xili_settings = array();
	var $xili_settings_ver = '1.0';
	var $plugin_local = "xili_xl_bbp_addon"; // text domain
	var $settings_list = "xili_xl_bbp_addon_list"; // used by settings sections and fields in settings page
	var $url = '';
	var $urlpath = ''; // The path to this plugin - see construct

	var $debug ; //WP_DEBUG

	//Class Functions
	/**
	 * PHP 5 Constructor
	 */
	function __construct(){

		$this->debug = ( defined ('WP_DEBUG') ) ? WP_DEBUG : false ;

		load_plugin_textdomain( 'xili_xl_bbp_addon', false, $this->plugin_folder.'/languages' );

		//register_activation_hook( __FILE__, array(&$this,'get_xili_settings') ); // first activation

		$this->url = plugins_url(basename(__FILE__), __FILE__);
		$this->urlpath = plugins_url('', __FILE__);

		//Initialize the options
		$this->get_xili_settings();
		//Admin menu

		add_action( 'admin_menu', array(&$this, 'admin_menu_link') );
		add_action( 'admin_init', array(&$this, 'admin_init') );

		if ( is_admin() ) {
			add_filter ( 'xiliml_manage_column_name', array(&$this,'xiliml_manage_column_name'), 10, 3);
			add_filter ( 'xiliml_language_translated_in_column', array(&$this,'xiliml_language_translated_in_column'), 10, 3);
		}

		//Actions both side

		add_action( 'init', array(&$this,'plugin_init') );
		add_action( 'bbp_enqueue_scripts', array(&$this,'bbp_custom_css_enqueue') );

		add_filter( 'bbp_edit_topic_pre_insert', array(&$this, 'xl_bbp_edit_topic_pre_insert') );
		add_filter( 'bbp_new_topic_pre_insert', array(&$this, 'xl_bbp_edit_topic_pre_insert') );

		add_filter( 'bbp_edit_reply_pre_set_terms', array(&$this, 'xl_bbp_edit_reply_pre_set_terms'), 10, 3 );
		add_filter( 'bbp_new_reply_pre_set_terms', array(&$this, 'xl_bbp_edit_reply_pre_set_terms'), 10, 3 );


		// front-end side

		add_action ('load_plugin_domain_for_curlang_bbpress', array(&$this,'load_plugin_domain_for_curlang_bbpress'), 10, 2); // fixed 2.15.2

		if ( ! is_admin() ) {
			add_action( 'save_post', array(&$this,'bbp_save_topic_or_reply'), 10, 2 );
			add_action( 'parse_query', array(&$this,'bbpress_parse_query') ); // fixe issues in bbp 2.1

			// widgets title filter

			add_filter ( 'bbp_login_widget_title' , array(&$this,'translate_one_text') );
			add_filter ( 'bbp_view_widget_title' , array(&$this,'translate_one_text') );
			add_filter ( 'bbp_forum_widget_title' , array(&$this,'translate_one_text') );
			add_filter ( 'bbp_topic_widget_title' , array(&$this,'translate_one_text') );
			add_filter ( 'bbp_replies_widget_title' , array(&$this,'translate_one_text') );
		}

		//display contextual help
		add_action( 'contextual_help', array( &$this,'add_help_text' ), 10, 3 ); /* 2.8.4.3 */
	}

	/**
	 * PHP 4 Compatible Constructor
	 */
	function xili_xl_bbp_addon(){ $this->__construct(); }



	function plugin_init() {
		$this->get_xili_settings();
	}


	/**
	 * Retrieves the plugin options from the database.
	 * @return array
	 */
	function get_xili_settings() {
		if (!$xili_settings = get_option( $this->settings_name )) {
			$xili_settings = array(
				'reply-keep-tags' => false,
				'css-theme-folder' => false,
				'version'=> $this->xili_settings_ver	// see on top class
			);
			update_option( $this->settings_name, $xili_settings);
		}
		$this->xili_settings = $xili_settings;
	}

	/** change default style - inspired from Jared Atchison **/
	function bbp_custom_css_enqueue(){
		if ( isset( $this->xili_settings['css-theme-folder'] ) && $this->xili_settings['css-theme-folder'] ){
			// Unregister default bbPress CSS
			wp_deregister_style( 'bbp-default-bbpress' );

			// Register new CSS file in our active theme directory
			wp_enqueue_style( 'bbp-default-bbpress', get_stylesheet_directory_uri() . '/bbpress.css' );
		}
	}


	/** change bbp mo file **/
	function load_plugin_domain_for_curlang_bbpress ( $plugin_domain, $cur_iso_lang ) { // only called in front-end

		unload_textdomain( 'bbpress' );

		if ( false === load_textdomain( 'bbpress', WP_LANG_DIR . '/bbpress/bbpress-'.$cur_iso_lang.'.mo' ) ) {
			load_textdomain( 'bbpress', WP_LANG_DIR . '/plugins/bbpress-'.$cur_iso_lang.'.mo' ); // new place
		}

	}



	function bbp_save_topic_or_reply ( $post_ID, $post ) {
		global $xili_language;
	//test if topic or reply
		if ( in_array ( $post->post_type, array( bbp_get_topic_post_type(), bbp_get_reply_post_type() ) ) ) {
	// get language of parent forum
			$parent_lang = $xili_language->get_post_language ( $post->post_parent ) ;
	// set taxonomy to language
			if ( $parent_lang != '') {
				wp_set_object_terms( $post_ID, $parent_lang, TAXONAME );
			}
		}
	}

	/**
	 * Translate texts of widgets or other simple text...
	 *
	 * @ return
	 */
	function translate_one_text ( $value ){
		global $xili_language;
		if ('' != $value)
			return __($value, $xili_language->thetextdomain);
		else
			return $value;
	}

	/**
	 * fixe issue in bbPress 2.1
	 */
	function bbpress_parse_query ( $wp_query ) {
		$bbp = bbpress() ;
		if ( isset ( $wp_query->query_vars['post_type' ] ) && version_compare( $bbp->version, '2.2', '<') ) {
	// announced to be fixed in bbp 2.2 - tracs 1947 - 4216
			if ( is_array ( $wp_query->query_vars['post_type' ] ) ) {
				if ( $wp_query->query_vars['post_type' ] = array( bbp_get_topic_post_type(), bbp_get_reply_post_type() ) ) {
					//error_log (' QUERY '.serialize ( $wp_query->query_vars['post_type' ] ));
					$wp_query->is_home = false ;
				}
			}
		}
	}


	function xiliml_manage_column_name ( $ends, $cols, $post_type ) {
		if ( in_array ( $post_type, array( bbp_get_forum_post_type(), bbp_get_topic_post_type(), bbp_get_reply_post_type() ) ) ) {
		//error_log ( '----------->'.$post_type);
		$ends = array( 'author', 'comments', 'date', 'rel', 'visible');
		}
		return $ends;
	}

	function xiliml_language_translated_in_column ( $output, $result, $post_type ) {

		if ( in_array ( $post_type, array( bbp_get_forum_post_type(), bbp_get_topic_post_type(), bbp_get_reply_post_type() ) ) ) {
			$output = '';
			if ( $result == '' ) {
				$output .= '.' ;
			} else {
				$output .= __('linked in:', 'xili_xl_bbp_addon') ;
				$output .= '&nbsp;<span class="translated-in">' . $result .'</span>';
			}
		}

		return $output;
	}


	/**
	 *
	 * prepare terms of topic-tag and attach language
	 *
	 */
	function xl_bbp_edit_topic_pre_insert ( $topic_data ) {
		global $xili_tidy_tags_topic;
		// analyze terms
		extract($topic_data, EXTR_SKIP);

		if ( !empty($tax_input) ) { ///error_log ( serialize ( $tax_input ) );
			foreach ( $tax_input as $taxonomy => $tags ) {
				$taxonomy_obj = get_taxonomy($taxonomy);
				if ( is_array($tags) ) { // array = hierarchical, string = non-hierarchical.
					$tags = array_filter($tags);
				} else {
					$tags = array( $tags );
				}
				if ( current_user_can($taxonomy_obj->cap->assign_terms) ) {

					$lang_group = the_curlang() . '-' . $xili_tidy_tags_topic->xili_settings['tidylangsgroup'];
					$group = term_exists( $lang_group, $xili_tidy_tags_topic->tidy_taxonomy ) ;

					foreach ( $tags as $term ) {
					// if exists add current language
						$res = term_exists( $term, $taxonomy ) ;
						if ( is_array( $res ) ) {


							// if not assigned - (int) mandatory - true to add language or group
							wp_set_object_terms( $res['term_id'], array((int)$group['term_id']), $xili_tidy_tags_topic->tidy_taxonomy, true );
						} else {
					// if not add and assign language
							$res = wp_insert_term( $term, $taxonomy);

							if ( ! is_wp_error( $res ) ) {
								wp_set_object_terms( $res['term_id'], array((int)$group['term_id']), $xili_tidy_tags_topic->tidy_taxonomy, true );
							}
						}
					}

				}
			}
		}

		return $topic_data;
	}


	/**
	 *
	 * prepare terms of topic-tag and attach language for replies
	 *
	 */
	function xl_bbp_edit_reply_pre_set_terms ( $terms, $topic_id, $reply_id ) {
		global $xili_tidy_tags_topic;
		$taxonomy = get_option( '_bbp_topic_tag_slug', 'topic-tag' );
		$taxonomy_obj = get_taxonomy($taxonomy);

		if ( current_user_can($taxonomy_obj->cap->assign_terms) ) {

			$lang_group = the_curlang() . '-' . $xili_tidy_tags_topic->xili_settings['tidylangsgroup'];
			$group = term_exists( $lang_group, $xili_tidy_tags_topic->tidy_taxonomy ) ;

			// Explode by comma
			if ( strstr( $terms, ',' ) ) {
				$tags = explode( ',', $terms );
			} else {
				$tags = array($terms);
			}

			foreach ( $tags as $term ) {
			// if exists add current language
				$res = term_exists( $term, $taxonomy ) ;
				if ( is_array( $res ) ) {


					// if not assigned - (int) mandatory - true to add language or group
					wp_set_object_terms( $res['term_id'], array((int)$group['term_id']), $xili_tidy_tags_topic->tidy_taxonomy, true );
				} else {
			// if not add and assign language
					$res = wp_insert_term( $term, $taxonomy);

					if ( ! is_wp_error( $res ) ) {
						wp_set_object_terms( $res['term_id'], array((int)$group['term_id']), $xili_tidy_tags_topic->tidy_taxonomy, true );
					}
				}
			}
			global $xili_xl_bbp_addon;

			// don't modify the reply tags string - check option - a reply cannot reduce tags list
			if ( isset( $xili_xl_bbp_addon['main']->xili_settings['reply-keep-tags'] ) && $xili_xl_bbp_addon['main']->xili_settings['reply-keep-tags'] ){
			$topic_terms = get_the_terms( $topic_id, $taxonomy ); // those of topic

			if ( ! is_wp_error( $topic_terms ) && ! empty( $topic_terms ) ) {

				foreach ( $topic_terms as $term ) {
					if ( !in_array( $term->name, $tags ) )
						$tags[] = $term->name;
				}
				return $tags;
			}
			}

		}

		return $terms;
	}



	/**
	 * Adds the options subpanel
	 */
	function admin_menu_link() {
		add_options_page( $this->plugin_name, __($this->display_plugin_name, 'xili_xl_bbp_addon'), 'manage_options', __FILE__, array(&$this,'admin_options_page'));
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'filter_plugin_actions'), 10, 2 );
	}

	/**
	 * Admin init - section - fields
	 */
	function admin_init() {
		register_setting ($this->settings_name, $this->settings_name, array(&$this,'xili_settings_validate_options') );
		add_settings_section ( $this->settings_list.'_section_1', __('List of options ', 'xili_xl_bbp_addon') , array(&$this,'xili_settings_section_1_draw') , $this->settings_list );

		add_settings_field ( $this->settings_list.'_css-theme-folder', __('bbPress default style file in theme', 'xili_xl_bbp_addon'), array(&$this,'xili_settings_field_1_draw'), $this->settings_list, $this->settings_list.'_section_1');
		add_settings_field ( $this->settings_list.'_reply-keep-tags', __('Repliers cannot delete topic tags', 'xili_xl_bbp_addon'), array(&$this,'xili_settings_field_2_draw'), $this->settings_list, $this->settings_list.'_section_1');

	}

	function xili_settings_section_1_draw () {
		echo '<p>'. __('This plugin shipped with xili-language package is an addon to activate multilingual features to bbPress with xili-language. Some other options are possible.', 'xili_xl_bbp_addon') . '</p>';

	}

	function xili_settings_field_1_draw () {
		// not checked - not saved in settings
		$checked = ( isset ( $this->xili_settings['css-theme-folder'] ) && $this->xili_settings['css-theme-folder'] ) ? "checked='checked'" : "";

		echo "<input value = 'true' id='{$this->settings_name}[css-theme-folder]' name='{$this->settings_name}[css-theme-folder]' type='checkbox' {$checked} />";

	}

	function xili_settings_field_2_draw () {
		// not checked - not saved in settings
		$checked = ( isset ( $this->xili_settings['reply-keep-tags'] ) && $this->xili_settings['reply-keep-tags'] ) ? "checked='checked'" : "";

		echo "<input value = 'true' id='{$this->settings_name}[reply-keep-tags]' name='{$this->settings_name}[reply-keep-tags]' type='checkbox' {$checked} />";

	}

	function xili_settings_validate_options ( $input ) {

		$valid = $input;
		$valid['version'] = $this->xili_settings_ver ; // because not in input !
		return $valid;
	}

	/**
	 * Adds the Settings link to the plugin activate/deactivate page
	 */
	function filter_plugin_actions($links, $file) {
		$settings_link = '<a href="options-general.php?page=' . $this->plugin_folder .'/'. basename(__FILE__) . '">' . __('Settings') . '</a>';
		array_unshift( $links, $settings_link ); // before other links

		return $links;
	}

	/**
	 * Adds settings/options page
	 */
	function admin_options_page() { ?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php printf(__( '%s settings', 'xili_xl_bbp_addon'), $this->display_plugin_name ); ?></h2>
			<form action="options.php" method="post" >
				<?php
				do_settings_sections( $this->settings_list );
				settings_fields( $this->settings_name ); // hidden fields and referrer and nonce
				?>
				<?php submit_button( __('Save Changes'), 'secondary' ); // 'primary' = by default ?>
			</form>
			<h4><a href="http://dev.xiligroup.com/<?php echo $this->plugin_name; ?>" title="Plugin page and docs" target="_blank" style="text-decoration:none" ><img style="vertical-align:bottom; margin-right:10px" src="<?php echo plugins_url( 'images/'.$this->plugin_name.'-logo-32.png', __FILE__ ) ; ?>" alt="<?php echo $this->display_plugin_name; ?> logo"/>&nbsp;<?php echo $this->display_plugin_name; ?> </a> - © <a href="http://dev.xiligroup.com" target="_blank" title="<?php _e('Author'); ?>" >xiligroup.com</a>™ - msc 2013 - v. <?php echo XILIXLBBPADDON_VER; ?></h4>
		</div>


		<?php
	}

	/**
	 * Contextual help
	 *
	 * @since 1.2.2
	 */
	function add_help_text($contextual_help, $screen_id, $screen) {
		$more_infos =
			'<p><strong>' . __('For more information:') . '</strong></p>' .
			'<p>' . __('<a href="http://wiki.xiligroup.org" target="_blank">Xili Plugins Documentation and WIKI</a>', 'xili_xl_bbp_addon') . '</p>' .
			'<p>' . __('<a href="http://dev.xiligroup.com/" target="_blank">Xili Plugins news</a>','xili_xl_bbp_addon') . '</p>' .
			'<p>' . __('<a href="http://codex.wordpress.org/" target="_blank">WordPress Documentation</a>','xili_xl_bbp_addon') . '</p>' .
			'<p>' . __('<a href="http://dev.xiligroup.com/?post_type=forum/" target="_blank">Support Forums</a>','xili_xl_bbp_addon') . '</p>' ;

		if ( 'settings_page_xili-language/xili-xl-bbp-addon' == $screen->id ) {
			$about_infos =
				'<p>' . __('Things to remember to set xili xl-bbPress add-on:','xili_xl_bbp_addon') . '</p>' .
				'<ul>' .
				'<li>' . __('Activate this plugin only if bbPress plugin is activated.','xili_xl_bbp_addon') . '</li>' .
				'<li>' . __('Remember that a forum is assigned to one language. Consequences: topics and replies are assigned to this language.','xili_xl_bbp_addon') . '</li>' .
				'<li>' . __('If xili-tidy-tags plugin is activated, a new instance is created for Topic-Tags. So it is possible to group Topic-Tags in clouds (by language or semantic group).', 'xili_xl_bbp_addon') . '</li>' .
				'<li>' . __('This first version contains basic features. Pre-tested with bbPress 2.3beta', 'xili_xl_bbp_addon') . '</li>' .
				'<li>' . __('The options below are provided to customize style (css in theme) or change behaviour (Repliers cannot delete tags).', 'xili_xl_bbp_addon') . '</li>' .
				'</ul>' ;

			$screen->add_help_tab( array(
				'id'	=> 'about-xili-xl-bbp-addon',
				'title' => __('About xili xl-bbPress add-on','xili_xl_bbp_addon'),
				'content' => $about_infos,
			));
			$screen->add_help_tab( array(
				'id'	=> 'more-infos',
				'title' => __('For more infos','xili_xl_bbp_addon'),
				'content' => $more_infos,
			));

		}
		return $contextual_help;
	}

} //End Class


// soon obsolete...
function xili_xl_bbp_get_WPLANG () {
	global $wp_version;
	if ( version_compare($wp_version, '4.0', '<') ) {
		if ( defined('WPLANG') )
			return WPLANG;
		else
			return '';
	} else {
		return get_option( 'WPLANG', '' );
	}
}


// bbPress admin Language (user locale)
function xili_xl_bbp_lang_init ( ) {
	if ( is_admin() ) {
		//add_filter( 'bbpress_locale', 'xili_bbp_admin_side_locale', 10, 2); // obsolete in 2.2
		add_filter( 'plugin_locale', 'xili_bbp_admin_side_locale', 10, 2);
	}
}

function xili_bbp_admin_side_locale ( $locale = 'en_US', $domain = 'bbpress' ) {
	global $xili_language;
	if ( in_array ( $domain, array( 'bbpress' ) ) ) {

		if ( class_exists( 'bbPress') ) remove_action( 'set_current_user', 'bbp_setup_current_user' ); // 2.8.8
		$locale = get_user_option( 'user_locale' );
		if ( class_exists( 'bbPress') ) add_action( 'set_current_user', 'bbp_setup_current_user', 10 );

		if ( empty( $locale ) ) {
			$wp_lang = xili_xl_bbp_get_WPLANG();
			$locale = ( '' != $wp_lang ) ? $wp_lang : 'en_US';

			if ( is_multisite() ) {
				if ( defined( 'WP_INSTALLING' ) || ( false === $ms_locale = get_option( 'WPLANG' ) ) )
					$ms_locale = get_site_option( 'WPLANG' );

			if ( $ms_locale !== false )
					$locale = $ms_locale;
			}
		}
	}
	return $locale;
}


// INIT and ERROR
function xili_xl_bbp_addon_init () {
	if ( function_exists ('bbpress') )
		$bbp = bbpress() ;
	if ( class_exists ('xili_language') && version_compare( XILILANGUAGE_VER, '2.15.1', '>') && class_exists ('bbpress') && version_compare( $bbp->version, '2.5.4', '>=') ) {
		global $xili_xl_bbp_addon;
		$xili_xl_bbp_addon['main'] = new xili_xl_bbp_addon();
	} else {
		add_action( 'admin_notices', 'xili_xl_bbp_addon_need_xl' );
		return;
	}
	/* - not used yet
	if ( is_admin() ) {
		$plugin_path = dirname(__FILE__) ; //error_log( $plugin_path );
		require ( $plugin_path . '/includes/class-admin.php' );
		$xili_xl_extended['admin'] = new xili_xl_template_admin();
	}
	*/
}

function xili_tidy_tags_start_topic () { //bbp_get_topic_tag_tax_slug( - not here possible - no filtered
	global $xili_tidy_tags_topic;
	if ( class_exists ( 'xili_tidy_tags' ) && class_exists ( 'bbpress' ) ) {

		$tag_slug = get_option( '_bbp_topic_tag_slug', 'topic-tag' ) ;

		$xili_tidy_tags_topic = new xili_tidy_tags ( $tag_slug, bbp_get_topic_post_type() ); // no params by default for post_tag

		if ( version_compare( XILITIDYTAGS_VER, '1.8.6', '>' ) ){
			add_filter( 'xtt_return_lang_of_tag_'. $tag_slug, array(&$xili_tidy_tags_topic, 'return_lang_of_tag' ), 10, 2 ); // to be adapted if this another instancing
		}
		/**
		 *
		 * class admin in separated file
		 *
		 */
		if ( is_admin() && class_exists ( 'bbpress' ) ) {
			$xili_tidy_tags_topic_admin = new xili_tidy_tags_admin( $xili_tidy_tags_topic, get_option( '_bbp_topic_tag_slug', 'topic-tag' ), bbp_get_topic_post_type() );
		}
	}
}

/**
 *
 * Error if no bbPress
 *
 */
function xili_xl_bbp_addon_need_xl() {
		global $wp_version;
		load_plugin_textdomain( 'xili_language_errors', false, 'xili-language/languages' );
		echo '<div id="message" class="error fade"><p>';
		echo '<strong>'.__( 'Installation of both xili-language AND bbPress is not completed. (You need to activate plugin bbPress).', 'xili_language_errors' ) . '</strong>';
		echo '<br />';
		echo '</p></div>';
}


?>