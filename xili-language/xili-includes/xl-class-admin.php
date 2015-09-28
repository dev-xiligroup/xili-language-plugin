<?php
/**
 * class xili_language_admin
 *
 * @since 2.6.3 - 2.7.1 - 2.8.0 - 2.8.3 - 2.8.4 - 2.8.4.1 - 2.8.4.2 - 2.8.4.3 - 2.8.5
 * 2013-03-13 (05)
 * 2013-03-17 (2.8.6)
 * 2013-04-16 (2.8.7)
 * 2013-05-03 (2.8.8)
 * 2013-05-19 (2.8.8k)
 * 2013-05-26 (2.8.9)
 * 2013-08-20 (2.9.0)
 * 2013-10-02 (2.9.1)
 * 2013-11-01 (2.9.10) inline edit and bulk edit improved - Pages insertion point
 * 2013-11-20 (2.9.20) Menu insertion point
 * 2013-11-24 (2.9.21) Optimize flush
 * 2013-12-11 (2.9.30) Option for WP 3.8 style, default menu screen options
 *
 * 2014-02-01 (2.10.0) New versioning - add twentyfourteen - improved downloads from Automattic (branches)
 * 2014-02-27 (2.10.1) Fixes nav menu css, add menu type for singular
 * 2014-03-10 (2.11.0) improves infos in form for alias refreshing in permalinks, new locales (based on jetpack)
 * 2014-03-10 (2.11.1) changes ajax/json for WP3.9
 * 2014-03-30 (2.11.2) accurate counter for CPT - more tests with 3.9
 * 2014-04-21 (2.11.3) fixes keys in title count - style improved in translations metabox - recover admin substitle of tabs
 * 2014-04-21 (2.12.0) includes new tab
 * 2014-05-16 (2.12.1) improved choice for mergin .mo if child, message for export xml
 * 2014-05-26 (2.12.2) fixes notice when importing xml. Improved GlotPress import (try /dev/)
 * 2014-05-28 (2.13.1) fixes theme customize broken ( wp_get_nav_menus filter called )
 * 2014-06-02 (2.13.2 b) fixes settings for new CPT, better selector (msgid for XD), XD again in bar admin
 * 2014-06-10 (2.14.0) plugin domains and mo file switcher improved...
 * 2014-06-15 (2.14.1) news pointer improved, css adapted, fixes findposts.js (WP 3.9 broken)
 * 2014-06-22 (2.14.2) menus insert point improved (language based on id to avoid rare issue with slug)
 * 2014-07-27 (2.15.0) UI for flags option
 * 2014-08-25 (2.15.1) params in add_theme_support ( 'custom_xili_flag', args ) - possible default flags in theme (see twentyfourteen-xili as example)
 * 2014-09-15 (2.15.2) get_WPLANG() for 4.0 new install
 * 2014-11-17 (2.15.3) ready for 4.1-beta1 new install
 * 2014-12-12 (2.15.4) ready for 4.1-RC1 new install
 * 2014-12-18 (2.16.0) better .mo files list - clean code
 * 2014-12-21 (2.16.1) fixes find_files if no wp-content/languages/themes
 *
 * 2015-02-28 - 2.16.2 - rewrite selected(), checked()
 * 2015-03-23 - 2.16.4 - enable new admin_custom_xili_flag - detect in media library before than in other places (plugin, theme) as before
 * 2015-04-18 - 2.16.6 - clean code for older version option 3.8
 * 2015-04-24 - 2.17.1 - esc_html for add_query_arg - detect pre-registered widgets - Online help updated (flags) - fixes propagation
 * 2015-06-01 - 2.18.1 - fixes, improves media editing page (cloning part)
 * 2015-06-25 - 2.18.2 - add display link in languages of post table
 * 2015-07-07 - 2.19.1 - fixes (3pepe3)
 * 2015-08-15 - 2.19.3 - fixes messages for mo file like arq.mo (3) or haw_US.mo (5) - PolyGlots teams
 * 2015-09-13 - 2.20.2 - sources (vars), doc optimized
 * 2015-09-16 - 2.20.3 - new option to add widget visibility rules according language // fixes admin side taxonomy translation
 *
 * @package xili-language
 */

class xili_language_admin extends xili_language {

	// 2.5
	var $authorbrowserlanguage = ''; // author default browser language

	var $exists_style_ext = false; // test if external style exists in theme
	var $style_folder = ''; // where is xl-style.css
	var $style_flag_folder_path = ''; // where are flags
	var $style_message = '';

	var $devxililink = 'http://dev.xiligroup.com';
	var $forumxililink = 'https://wordpress.org/support/plugin/xili-language'; //http://dev.xiligroup.com/?post_type=forum'; - 2.20.2
	var $wikilink = 'http://wiki.xiligroup.org';
	var $glotpresslink = 'https://make.wordpress.org/polyglots/teams/'; // 2.19.3
	var $fourteenlink = 'http://2014.extend.xiligroup.org';
	var $repositorylink = 'https://wordpress.org/plugins/xili-language/';

	var $parent = null;
	var $news_id = 0; //for multi pointers
	var $news_case = array();
	var $admin_messages = array(); //set in #491
	var $user_locale = 'en_US';
	var $embedded_themes = array('twentyten', 'twentyeleven', 'twentytwelve', 'twentythirteen', 'twentyfourteen', 'twentyfifteen', 'twentysixteen'); // bundled themes

	var $subpage_titles = array(); // see add_menu_settings_pages
	var $xl_tabs = array();

	var $settings_frontend = 'xiliml_frontend_settings';	// tab 2
	var $settings_author_rules = 'xiliml_author_rules'; // tab 5
	var $settings_authoring_settings = 'xiliml_authoring_settings'; // tab 5

	var $default_locale = null; // from GP_locale

	var $flag_theme_page;  // used by add_menu
	var $custom_xili_flags = array('custom_xili_flag', 'admin_custom_xili_flag' ); // 2.16.4

	var $local_textdomain_loaded = array(); // to avoid multiple loading file...

	var $changelog = '*'; // used in welcome and pointer

	/**
	 * PHP 5 Constructor
	 */
	function __construct( $xl_parent ){

		$this->parent = $xl_parent; // to keep values built in parent filters...

		// need parent constructed values (third param - tell coming from admin-class //2.6
		parent::__construct( false, false, true );

		// vars shared between parent and admin class - 2.8.4.3
		$this->xili_settings = &$this->parent->xili_settings;

		$this->langs_list_options = &$this->parent->langs_list_options; // 2.8.6

		if (!class_exists('GP_Locales'))
			require_once ( $this->plugin_path .'xili-includes/locales.php' ); // from JetPack copied

		$XL_locales = GP_Locales::instance();

		$this->examples_list = array();

		foreach ( $XL_locales->locales as $key => $one_locale ) {
			if ( isset($one_locale->wp_locale) && $one_locale->wp_locale != ''){
				$this->examples_list[$one_locale->wp_locale] = sprintf( _x( '%1$s/%2$s', 'locales', 'xili-language' ), $one_locale->english_name, $one_locale->native_name );
			} else { // a * inserted if no existing WP_locale declared...
				$this->examples_list[$key] = sprintf( _x( '%1$s/%2$s *', 'locales', 'xili-language' ), $one_locale->english_name, $one_locale->native_name );
			}
		}

		$this->default_lang = &$this->parent->default_lang;

		$this->default_locale = GP_Locales::by_field( 'wp_locale', $this->default_lang );

		$this->langs_group_id = &$this->parent->langs_group_id;
		$this->langs_group_tt_id = &$this->parent->langs_group_tt_id;

		$this->get_template_directory = &$this->parent->get_template_directory;
		$this->get_parent_theme_directory = &$this->parent->get_parent_theme_directory;

		$this->show_page_on_front = &$this->parent->show_page_on_front;

		$this->arraydomains = &$this->parent->arraydomains;

		$this->lang_perma = &$this->parent->lang_perma;
		$this->alias_mode = &$this->parent->alias_mode;

		$this->langs_ids_array = &$this->parent->langs_ids_array;
		$this->langs_slug_name_array = &$this->parent->langs_slug_name_array;
		$this->langs_slug_fullname_array = &$this->parent->langs_slug_fullname_array;

		$this->langs_slug_shortqv_array = &$this->parent->langs_slug_shortqv_array;
		$this->langs_shortqv_slug_array = &$this->parent->langs_shortqv_slug_array;

		add_action( 'admin_init', array( &$this, 'init_roles') );	// 2.8.8

		add_action( 'admin_head', array(&$this,'xd_flush_permalinks') ); // 2.11
		add_filter( 'wp_get_nav_menus', array(&$this,'_update_menus_insertion_points'), 10, 2 ); // 2.13

		// since 2.2.0
		add_action( 'admin_bar_init', array( &$this, 'admin_bar_init') ); // add button in toolbar

		// 2.8.0 dashboard language - inspired from Takayuki Miyoshi works
		add_filter( 'locale', array( &$this, 'admin_side_locale') );

		add_action( 'admin_init', array( &$this, 'switch_user_locale') );
		add_action( 'personal_options_update', array( &$this, 'update_user_dashboard_lang_option') );
		add_action( 'personal_options', array( &$this, 'select_user_dashboard_locale') );
		add_action( 'edit_user_profile_update', array( &$this, 'update_user_dashboard_lang_option') ); // 2.18

		// plugins list infos
		add_filter( 'plugin_row_meta', array( &$this, 'more_infos_in_plugin_list' ), 10, 2);	// class WP_Plugins_List_Table
		add_filter( 'plugin_action_links', array( &$this, 'more_plugin_actions' ), 10, 2);		// class WP_Plugins_List_Table
		add_action( 'after_plugin_row', array( &$this, 'more_plugin_row' ), 10, 3);				// class WP_Plugins_List_Table

		// Dashboard menu and settings pages
		add_action( 'admin_init', array( &$this, 'admin_redirects') ); // 2.20 for welcome screen
		add_action( 'admin_menu', array( &$this, 'admin_welcome'), 10 ); // 2.20
		add_action( 'admin_menu', array( &$this, 'add_menu_settings_pages'), 10 );
		add_action( 'admin_menu', array( &$this, 'admin_sub_menus_hide'), 12 ); //

		add_filter( 'admin_title', array( &$this, 'admin_recover_page_title'), 10, 2 ); // 2.11.3

		add_action( 'admin_print_styles-settings_page_language_page', array(&$this, 'print_styles_options_language_page'), 20 );
		add_action( 'admin_print_styles-settings_page_language_front_set', array(&$this, 'print_styles_options_language_tabs'), 20 );
		add_action( 'admin_print_styles-settings_page_language_expert', array(&$this, 'print_styles_options_language_tabs'), 40 );
		add_action( 'admin_print_styles-settings_page_author_rules', array(&$this, 'print_styles_options_language_tabs'), 20 );
		add_action( 'admin_print_styles-settings_page_language_files', array(&$this, 'print_styles_options_language_tabs'), 20 );
		add_action( 'admin_print_styles-settings_page_language_support', array(&$this, 'print_styles_options_language_support'), 20 );

		// Navigation menu builder
		add_action( 'admin_init', array(&$this, 'add_language_nav_menu_meta_boxes') );
		add_action( 'admin_init', array(&$this, 'add_sub_select_page_nav_menu_meta_boxes') );
		add_action( 'admin_init', array(&$this, 'add_sub_select_nav_menu_meta_boxes') );

		// Edit Post Page
		add_action( 'admin_init', array(&$this,'admin_init') ); // styles registering
		add_action( 'post_submitbox_start', array(&$this,'post_submit_permalink_option') ); // 2.15 - option to use title as permalink

		// Propagation && Authoring settings
		add_action( 'admin_init', array(&$this,'set_author_rules_register_setting') );
		add_action( 'admin_init', array(&$this,'set_propagation_actions') );

		// frontend new settings
		add_action( 'admin_init', array(&$this,'set_frontend_settings_fields') );

		add_action( 'admin_menu', array(&$this, 'add_custom_box_in_post_edit') );

		add_action( 'admin_print_scripts-post.php', array(&$this,'find_post_script') ); // 2.2.2
		add_action( 'admin_print_scripts-post-new.php', array(&$this,'find_post_script') );

		// 2.9.10
		add_action( 'wp_ajax_find_post_types', array(&$this,'wp_ajax_find_post_types') );

		add_action( 'admin_print_styles-post.php', array(&$this, 'print_styles_cpt_edit') );
		add_action( 'admin_print_styles-post-new.php', array(&$this, 'print_styles_cpt_edit') );

		//add_filter( 'is_protected_meta', array(&$this,'hide_lang_post_meta'), 10, 3 ); // 2.5
		//add_filter( 'post_meta_key_subselect', array(&$this,'hide_lang_post_meta_popup'), 10, 2); // 2.5

		/* actions for edit post page */
		add_action( 'save_post', array(&$this,'xili_language_add'), 10, 2 );
		add_action( 'save_post', array(&$this, 'fixes_post_slug'), 11, 2 ); // 2.5

		// Edit Attachment Media
		add_filter( 'attachment_fields_to_edit', array(&$this,'add_language_attachment_fields'), 10, 2 ); // 2.6.3
		add_filter( 'attachment_fields_to_save', array(&$this,'set_attachment_fields_to_save'), 10, 2 ); // 2.6.3
		add_action( 'delete_attachment', array(&$this,'if_cloned_attachment') ); // 2.6.3
		add_filter( 'wp_delete_file', array(&$this,'if_file_cloned_attachment') ); // 2.6.3
		// Flag media
		add_action( 'attachment_submitbox_misc_actions', array(&$this,'attachment_submitbox_flag_metadata' ) ); // 2.15
		add_filter( 'display_media_states', array(&$this,'add_display_media_states') );
		add_action( 'edit_attachment', array(&$this,'update_attachment_context' ) ); // 2.15
		add_action( 'add_attachment', array(&$this,'xili_reset_transient_get_flag_series') );// 2.16.4
		add_action( 'edit_attachment', array(&$this,'xili_reset_transient_get_flag_series') );
		add_action( 'delete_attachment', array(&$this,'xili_reset_transient_get_flag_series') ); // near get_flag_series
		add_action( 'admin_menu', array(&$this, 'add_custom_box_in_media_edit') ); // 2.15 custom meta box in single media edit

		add_action( 'admin_menu', array( $this, 'flag_options_theme_menu' ) ); // 2.15
		add_action( 'admin_init', array(&$this,'set_flag_register_setting' ) );

		// posts edit table
		add_filter( 'manage_edit-post_columns', array(&$this,'xili_manage_column_name')); // 2.9.10 - post quick edit single row
		add_filter( 'manage_post_posts_columns', array(&$this,'xili_manage_column_name')); // 2.8.1
		add_filter( 'manage_page_posts_columns', array(&$this,'xili_manage_column_name'));
		add_filter( 'manage_media_columns', array(&$this,'xili_manage_column_name')); // 2.6.3

		$custompoststype = $this->xili_settings['multilingual_custom_post'] ; // 2.8.1
			if ( $custompoststype != array()) {
			foreach ( $custompoststype as $key => $customtype ) {
				if ( ( !class_exists( 'bbPress') && $customtype['multilingual'] == 'enable' ) || ( class_exists( 'bbPress') && ! in_array( $key, array( bbp_get_forum_post_type(), bbp_get_topic_post_type(), bbp_get_reply_post_type() ) ) && $customtype['multilingual'] == 'enable' ) ) {
					add_filter( 'manage_'.$key.'_posts_columns', array(&$this,'xili_manage_column_name'));
				}
			}
		}

		if ( class_exists( 'bbPress' ) ) {
			add_filter( 'bbp_admin_forums_column_headers', array(&$this,'xili_manage_column_name'));
			add_filter( 'bbp_admin_topics_column_headers', array(&$this,'xili_manage_column_name'));
			add_filter( 'bbp_admin_replies_column_headers', array(&$this,'xili_manage_column_name')); //2.8.1
		}

		add_action( 'manage_posts_custom_column', array(&$this,'xili_manage_column'), 10, 2);
		add_action( 'manage_pages_custom_column', array(&$this,'xili_manage_column'), 10, 2);
		add_action( 'manage_media_custom_column', array(&$this,'xili_manage_column'), 10, 2); // 2.6.3

		add_action( 'admin_print_styles-edit.php', array(&$this, 'print_styles_posts_list'), 20 );
		add_action( 'admin_print_styles-upload.php', array(&$this, 'print_styles_posts_list'), 20 );// 2.6.3

		add_filter ( 'category_name', array(&$this, 'translated_taxonomy_name'), 10, 3 ) ; // 2.13.3

		// quick edit languages in list - 1.8.9
		add_action( 'quick_edit_custom_box', array(&$this,'languages_custom_box'), 10, 2);
		add_action( 'admin_head-edit.php', array(&$this,'quick_edit_add_script') );
		add_action( 'bulk_edit_custom_box', array(&$this,'hidden_languages_custom_box'), 10, 2); // 1.8.9.3

		add_action( 'wp_ajax_save_bulk_edit', array(&$this,'save_bulk_edit_language') ); // 2.9.10

		add_action( 'wp_ajax_get_menu_infos', array(&$this,'ajax_get_menu_infos') ); // 2.9.10


		// sub-select in admin/edit.php 1.8.9
		add_action( 'restrict_manage_posts', array(&$this,'restrict_manage_languages_posts') );

		/* categories edit-tags table */
		add_filter( 'manage_edit-category_columns', array(&$this,'xili_manage_tax_column_name'));
		add_filter( 'manage_category_custom_column', array(&$this,'xili_manage_tax_column'), 10, 3); // 2.6
		add_filter( 'category_row_actions', array(&$this,'xili_manage_tax_action'), 10, 2); // 2.6

		add_action( 'admin_print_styles-edit-tags.php', array(&$this, 'print_styles_posts_list'), 20 );
		add_action( 'category_edit_form_fields', array(&$this, 'show_translation_msgstr'), 10, 2 );

		add_action( 'category_add_form', array(&$this, 'update_xd_msgid_list') ); //do_action($taxonomy . '_add_form', $taxonomy);

		/* actions for edit link page */
		add_action( 'admin_menu', array(&$this, 'add_custom_box_in_link') );

		add_action( 'admin_enqueue_scripts', array(&$this, 'admin_enqueue_menu_script') );

		add_filter( 'manage_link-manager_columns', array(&$this,'xili_manage_link_column_name') ); // 1.8.5
		add_action( 'manage_link_custom_column', array(&$this,'manage_link_lang_column'),10,2);
		add_action( 'admin_print_styles-link.php', array(&$this, 'print_styles_link_edit'), 20 );

		// set or update term for this link taxonomy
		add_action( 'edit_link', array(&$this,'edit_link_set_lang') );
		add_action( 'add_link', array(&$this,'edit_link_set_lang') );

		// default screen options - nav menus

		add_action( 'added_user_meta' , array(&$this,'default_nav_menus_screen_options'), 10, 4 );

		// new visibility for all widgets - 2.20.3
		if ( !empty($this->xili_settings['widget_visibility']) ) {
			add_filter( 'widget_update_callback', array( &$this, 'widget_update_callback' ), 10, 4 );
			add_action( 'in_widget_form', array( &$this, 'widget_visibility_admin' ), 10, 3 );
		}

		// infos in xml export
		add_action( 'export_filters', array(&$this,'message_export_limited' ) ); // 2.12.1
		//display contextual help
		add_action( 'contextual_help', array( &$this,'add_help_text' ), 10, 3 ); /* 1.7.0 */

		xili_xl_error_log ('# ADMIN '. __LINE__ .' ************* only_construct = ' . __CLASS__ );
	}

	/**
	 * Add a checkbox to renew permalink (slug) with title - only appear if post created from another language
	 *
	 * @since 2.15
	 *
	 */
	function post_submit_permalink_option() {
		global $post;
		$translation_state = get_post_meta ( $post->ID, $this->translation_state, true );
		if ( $translation_state != '' && false !== strpos( $translation_state , "initial" ) ) {
			$perma = ( get_option('permalink_structure') ) ? __('(permalink)', 'xili-language') : '';
			?>
			<p><label for="xl_permalink_option" class="selectit"><input name="xl_permalink_option" type="checkbox" id="xl_permalink_option" value="slug" /> <?php printf(__( 'Renew slug %s with title', 'xili-language' ), $perma ) ?></label></p>
			<?php
		}
	}

	/**
	 * Define authoring propagation options when creating a translation.
	 *
	 * was in theme-multilingual-classes
	 *
	 * @since 2.12
	 *
	 */
	function set_propagation_actions () {

		$checked_options = $this->get_theme_author_rules_options();  // checked 2.18

		$this->propagate_options_labels = array (
			'post_format' => array ('name' => __('Post Format', 'xili-language' ),
			'description' => __('Copy Post Format.', 'xili-language' )
			),
			'page_template' => array ('name' => __('Page template', 'xili-language' ),
			'description' => __('Copy Page template.', 'xili-language' )
			),
			'comment_status' => array ('name' => __('Comment Status', 'xili-language' ),
			'description' => __('Copy Comment Status.', 'xili-language' )
			),
			'ping_status' => array ('name' => __('Ping Status', 'xili-language' ),
			'description' => __('Copy Ping Status.', 'xili-language' ),
			),
			'post_parent' => array ('name' => __('Post Parent', 'xili-language' ),
			'description' => __('Copy Post Parent if translated (try to find the parent of the translated post).', 'xili-language' ), 'data' => 'post'
			),
			'menu_order' => array ('name' => __('Order', 'xili-language' ),
			'description' => __('Copy Page Order', 'xili-language' ),
			),
			'thumbnail_id' => array ('name' => __('Featured image', 'xili-language' ),
			'description' => __('Linked translated post will have the same featured image, (try to find the translated media). ', 'xili-language' ),
			));


		if ( current_theme_supports( 'xiliml-authoring-rules' ) ) {
			$options = array();
			$labels = array();
			$support = get_theme_support( 'xiliml-authoring-rules' ); // values defined by theme's author

			if ( isset($support[0]) && $support[0] != array() ) {
				foreach ($support[0] as $key => $params) {
					if ( array() == array_diff(array_keys($params),array('data','default','name','description', 'hidden'))) { // four parameters mandatory
						$options[$key] = array ('default' => $params['default'], 'data' => $params['data'], 'hidden' => $params['hidden']);
						$labels[$key] = array ('name' => $params['name'], 'description' => $params['description']);
					}
				}
				$this->propagate_options_default = array_merge($this->propagate_options_default_ref, $options);
				$this->propagate_options_labels = array_merge($this->propagate_options_labels, $labels); // default texts can be modified
			}
		} else {
			$this->propagate_options_default = $this->propagate_options_default_ref;
		}

		if ( $this->propagate_options_default != array() ) {
			foreach ( $this->propagate_options_default as $key => $one_propagate ) {
				if ( $one_propagate['data'] != 'post' && isset ( $checked_options[$key] ) && $checked_options[$key] == '1' ) { // 2.18
					add_action( 'xl_propagate_post_attributes', array( &$this, 'propagate_'.$key ) , 10, 2);
				}
			}
			add_action( 'xl_propagate_post_attributes', array( &$this, 'propagate_post_columns' ) , 10, 2);
		}
	}

	// called by filter admin_head
	function xd_flush_permalinks() {
		remove_submenu_page( 'index.php', 'xl-about' ); // 2.20 - here to avoid remove page title

		$screen = get_current_screen();
		if ( $screen->base == 'settings_page_language_page' ) {
			flush_rewrite_rules();
		}
	}

	/**
 	 * Checks if we should add links to the admin bar.
 	 *
 	 * @since 2.2.0
 	 */
	function admin_bar_init() {
	// Is the user sufficiently leveled, or has the bar been disabled? !is_super_admin() ||
		if ( !is_admin_bar_showing() )
			return;
	// editor rights
		if ( current_user_can ( 'xili_language_menu' ) )
			add_action( 'admin_bar_menu', array( &$this,'xili_tool_bar_links' ), 500 );

			add_action( 'admin_bar_menu', array( &$this,'lang_admin_bar_menu' ), 500 );
	}

	/**
 	 * Checks if we should add links to the bar.
 	 *
 	 * @since 2.2
 	 * updated and renamed 2.4.2 (node)
 	 */
	function xili_tool_bar_links() {

		$link = plugins_url( 'images/xililang-logo-24.png', $this->file_file ) ;
		$alt = esc_attr__( 'Languages by ©xiligroup' ,'xili-language');
		$title = esc_attr__( 'Languages menu by ©xiligroup' ,'xili-language');
		// Add the Parent link.
		$this->add_node_if_version( array(
			'title' => sprintf("<img src=\"%s\" alt=\"%s\" title=\"%s\" />", $link, $alt, $title ),
			'href' => false,
			'id' => 'xili_links',
		));
		if ( current_user_can ( 'xili_language_set' ) )
			$this->add_node_if_version( array(
				'title' => __('Languages settings','xili-language'),
				'href' => admin_url('options-general.php?page=language_page'),
				'id' => 'xl-set',
				'parent' => 'xili_links',
				'meta' => array('title' => __('Languages settings','xili-language') )
			));

		if ( class_exists('xili_tidy_tags' ) && current_user_can ('xili_tidy_editor_set') )
			$this->add_node_if_version( array(
				'title' => sprintf(__("Tidy %s settings","xili_tidy_tags"), __('Tags') ),
				'href' => admin_url( 'admin.php?page=xili_tidy_tags_settings' ),
				'id' => 'xtt-set',
				'parent' => 'xili_links',
				'meta' => array('title' => sprintf(__("Tidy %s settings","xili_tidy_tags"), __('Tags') ) )
			));
		if ( class_exists('xili_tidy_tags' ) && current_user_can ('xili_tidy_editor_group') )
			$this->add_node_if_version( array(
				'title' => sprintf( __('%s groups','xili_tidy_tags'), __('Tags')),
				'href' => admin_url( 'admin.php?page=xili_tidy_tags_assign' ),
				'id' => 'xtt-group',
				'parent' => 'xili_links',
				'meta' => array('title' => sprintf( __('%s groups','xili_tidy_tags'), __('Tags') ) )
			));

		if ( class_exists('xili_dictionary' ) && current_user_can ('xili_dictionary_edit') ) { // fixed XD 2.7
			global $xili_dictionary;
			$link = $xili_dictionary->xd_settings_page ;

			$this->add_node_if_version( array(
				'title' => 'xili-dictionary',
				'href' => admin_url( $link ),
				'id' => 'xd-set',
				'parent' => 'xili_links',
				'meta' => array('title' => sprintf( __('Translation with %s tools','xili-language'), 'xili-dictionary' ) )
			));
		}
		$this->add_node_if_version( array(
			'title' => __('xili-language : how to','xili-language'),
			'href' => $this->fourteenlink,
			'id' => 'xilione-multi',
			'parent' => 'xili_links',
			'meta' => array('target' => '_blank')
		));
		$this->add_node_if_version( array(
			'title' => __('About ©xiligroup plugins','xili-language'),
			'href' => $this->devxililink,
			'id' => 'xili-about',
			'parent' => 'xili_links',
			'meta' => array('target' => '_blank')
		));

	}

	function add_node_if_version ( $args ) {
		global $wp_admin_bar;
		$wp_admin_bar->add_node( $args );
	}

	/**
	 * from after_setup_theme action
	 *
	 * @since 2.8.8
	 *
	 */
	function admin_user_id_locale () {
		$this->user_locale = get_user_option( 'user_locale' );
	}

	/**
	 * Admin side localization - user's dashboard - called by filter locale (get_locale())
	 *
	 * @since 2.8.0
	 *
	 */
	function admin_side_locale( $locale = 'en_US' ) {

		if ( function_exists('get_current_screen') ) {
			$screen = get_current_screen();
			if ($screen && $screen->id == 'options-general') return $this->get_default_locale(); // 2.18 for selected of popup - show get_option WPLANG value
		}
		// to avoid notice with bbPress 2.3 - brutal approach
		if ( class_exists( 'bbPress') ) remove_action( 'set_current_user', 'bbp_setup_current_user' );
		$locale = get_user_option( 'user_locale' );
		if ( class_exists( 'bbPress') ) add_action( 'set_current_user', 'bbp_setup_current_user', 10 );

		if ( empty( $locale ) )
			$locale = $this->get_default_locale();

		return $locale;
	}


	/**
	 * Admin side localization - available languages inside WP core installation
	 *
	 * @since 2.8.0
	 *
	 */
	function get_default_locale() {

		$wplang = $this->get_WPLANG();
		$locale = ( '' != $wplang  ) ? $wplang : 'en_US';

		if ( is_multisite() ) {
			if ( defined( 'WP_INSTALLING' ) || ( false === $ms_locale = get_option( 'WPLANG' ) ) )
				$ms_locale = get_site_option( 'WPLANG' );

			if ( $ms_locale !== false )
				$locale = $ms_locale;
		}

		return $locale;

	}

	/**
	 * add theme local-xx_YY.mo file to translate
	 *
	 *
	 */
	function add_local_text_domain_file ( $locale ) {
		$theme_textdomain = the_theme_domain();
		$langfolder = $this->xili_settings['langs_folder'];
		$langfolder = ($langfolder == "/") ? "" : $langfolder;
		$theme_dir = get_stylesheet_directory();
		$file = "{$theme_dir}{$langfolder}/local-{$locale}.mo";
		if ( in_array ( $file, $this->local_textdomain_loaded ) ) return ; // thanks to 3pepe3
		$this->local_textdomain_loaded[] = $file;
		if (! ( $loaded = load_textdomain( $theme_textdomain, $file ) ) ) {
			load_textdomain( $theme_textdomain, WP_LANG_DIR . "/themes/local-{$theme_textdomain}-{$locale}.mo" );
		}
	}

	// Admin Bar at top right

	function lang_admin_bar_menu( ) {

		$screen = get_current_screen();	// to limit unwanted side effects (form)
		if ( in_array ( $screen->id , array (
		'dashboard', 'users', 'profile',
		'edit-post', 'edit-page', 'link-manager', 'upload',
		'settings_page_language_page', 'settings_page_language_front_set',
		'settings_page_language_expert', 'settings_page_language_expert', 'settings_page_language_support', 'settings_page_language_files', 'settings_page_author_rules',
		'xdmsg', 'edit-xdmsg', 'xdmsg_page_dictionary_page'
		) )
		|| ( false !== strpos ( $screen->id , '_page_xili_tidy_tags_assign' ) )
		) {

			$current_locale = $this->admin_side_locale();

			$cur_locale = GP_Locales::by_field( 'wp_locale', $current_locale );
			if ( $cur_locale ) {
				$current_language = $cur_locale->native_name ;
			} else {
				$cur_locale = GP_Locales::by_slug( $current_locale );
				$current_language = ( $cur_locale ) ? $cur_locale->native_name : '' ;
			}

			if ( ! $current_language )
				$current_language = $current_locale;

			$this->add_node_if_version( array(
				'parent' => 'top-secondary',
				'id' => 'xili-user-locale',
				'title' => __('Language','xili-language').': '. $this->lang_to_show( $current_language ) ) ); // '&#10004; '

			$available_languages = $this->available_languages(
				array( 'exclude' => array( $current_locale ) ) );

			foreach ( $available_languages as $locale => $lang ) {
				$url = admin_url( 'profile.php?action=lang-switch-locale&locale=' . $locale );

				$url = esc_html(add_query_arg(
					array( 'redirect_to' => urlencode( $_SERVER['REQUEST_URI'] ) ),
					$url )); //2.17.1

				$url = wp_nonce_url( $url, 'lang-switch-locale' );

				$this->add_node_if_version( array(
					'parent' => 'xili-user-locale',
					'id' => 'xili-user-locale-' . $locale,
					'title' => $this->lang_to_show( $lang ),
					'href' => $url ) );
			}
		}
	}

	function switch_user_locale() {

		if ( empty( $_REQUEST['action'] ) || 'lang-switch-locale' != $_REQUEST['action'] )
			return;

		check_admin_referer( 'lang-switch-locale' );

		$locale = isset( $_REQUEST['locale'] ) ? $_REQUEST['locale'] : '';

		if ( ! $this->is_available_locale( $locale ) || $locale == $this->admin_side_locale() )
			return;

		update_user_option( get_current_user_id(), 'user_locale', $locale, true );

		if ( ! empty( $_REQUEST['redirect_to'] ) ) {
			wp_safe_redirect( $_REQUEST['redirect_to'] );
			exit();
		}
	}

	function is_available_locale( $locale ) {
		return ! empty( $locale ) && array_key_exists( $locale, (array) $this->available_languages() );
	}

	function available_languages( $args = '' ) {
		$defaults = array(
			'exclude' => array(),
			'orderby' => 'key',
			'order' => 'ASC' );

		$args = wp_parse_args( $args, $defaults );

		$langs = array();

		$installed_locales = get_available_languages();
		$installed_locales[] = $this->get_default_locale();
		$installed_locales[] = 'en_US';
		$installed_locales = array_unique( $installed_locales );
		$installed_locales = array_filter( $installed_locales );

		foreach ( $installed_locales as $locale ) {
			if ( in_array( $locale, (array) $args['exclude'] ) )
				continue;

			$cur_locale = GP_Locales::by_field( 'wp_locale', $locale );
			if ( $cur_locale ) {
				$lang = sprintf( _x( '%1$s/%2$s', 'locales', 'xili-language' ), $cur_locale->english_name, $cur_locale->native_name ) ;
			} else {
				$cur_locale = GP_Locales::by_slug( $locale );
				$lang = ( $cur_locale ) ? sprintf( _x( '%1$s/%2$s', 'locales', 'xili-language' ), $cur_locale->english_name, $cur_locale->native_name ) : '' ;
			}

			if ( empty( $lang ) )
				$lang = "[$locale]";

			$langs[$locale] = $lang;
		}

		if ( 'value' == $args['orderby'] ) {
			natcasesort( $langs );

			if ( 'DESC' == $args['order'] )
				$langs = array_reverse( $langs );
		} else {
			if ( 'DESC' == $args['order'] )
				krsort( $langs );
			else
				ksort( $langs );
		}

		$langs = apply_filters( 'xili_available_languages', $langs, $args );

		return $langs;
	}

	/**
	 * Adds option in user profile to set and update his dashboard language
	 *
	 * 'user_locale' saved as iso (en_US or fr_FR ….)
	 * @since 2.8.0
	 *
	 */
	function update_user_dashboard_lang_option( $user_id ) { // 2.18
		if ( ! isset( $_POST['user_locale'] ) || empty( $_POST['user_locale'] ) )
			$locale = null;
		else
			$locale = $_POST['user_locale'];

		update_user_option( $user_id , 'user_locale', $locale, true );
	}

	function select_user_dashboard_locale( $wp_user ) {
		$available_languages = $this->available_languages( 'orderby=value' );
		//$selected = $this->admin_side_locale();
		$selected = get_user_option( 'user_locale', $wp_user->ID ); // thanks to renoir@w3.org 20150510
		?>
		<tr>
			<th scope="row"><?php echo esc_html( __( 'Your dashboard language', 'xili-language' ) ); ?></th>
			<td>
				<select name="user_locale">
				<?php foreach ( $available_languages as $locale => $lang ) : ?>
					<option value="<?php echo esc_attr( $locale ); ?>" <?php selected( $locale, $selected ); ?>><?php echo esc_html( $this->lang_to_show( $lang ) ); ?></option>
				<?php endforeach; ?>
				</select>
				<p><em><?php _e('System’s default language is', 'xili-language'); echo ": " . $this->get_default_locale(); ?></em></p>
			</td>
		</tr>
		<?php
	}

	function lang_to_show ( $lang = 'english' ) {
		return ucwords( $lang ); // uppercase each word
	}

	/** end dashboard user's language functions **/

	/**
	 * Adds links to the plugin row on the plugins page.
	 * Thanks to Zappone et WP engineer.com
	 *
	 * @param mixed $links
	 * @param mixed $file
	 */
	function more_infos_in_plugin_list( $links, $file ) {
		$base = $this->plugin_basename ;
		if ( $file == $base ) {
			$links[] = '<a href="options-general.php?page=language_page">' . __('Settings') . '</a>';
			$links[] = __('Informations and Getting started:', 'xili-language') . ' <a href="'. $this->wikilink . '">' . __('Xili Wiki', 'xili-language') . '</a>';
			$links[] = '<a href="'. $this->forumxililink .'">' . __('Forum and Support', 'xili-language') . '</a>';
			$links[] = '<a href="'. $this->devxililink .'/donate/">' . __('Donate', 'xili-language') . '</a>';
		}
		return $links;
	}

	/**
	 * Adds a row to comment situation for multilingual context !
	 *
	 */
	function more_plugin_row ( $plugin_file, $plugin_data, $status ) {
		$base = $this->plugin_basename ;
		if ( $plugin_file == $base ) {
			$statusXili = array ();

			$statusXili[] = __('Congratulations for choosing xili-language to built a multilingual website. To work optimally, 2 other plugins are recommended', 'xili-language');

			$statusXili[] = $this->plugin_status ( 'xili-dictionary', 'xili-dictionary/xili-dictionary.php', $status ) ;

			$statusXili[] = $this->plugin_status ( 'xili-tidy-tags', 'xili-tidy-tags/xili-tidy-tags.php' , $status) ;

			if ( is_child_theme() ) {
				$theme_name = get_option("stylesheet").' '.__('child of','xili-language').' '.get_option("template");
			} else {
				$theme_name = get_option("template"); // same as stylesheet
			}

			$statusXili[] = sprintf ( __('For Appearance the current active theme is <em>%s</em>', 'xili-language'), $theme_name );

			if ( $this->parent->xili_settings['theme_domain'] == '' ) {
				$statusXili[] = sprintf (__('This theme <em>%s</em> seems to not contain localization function (load_theme_textdomain) to be used for a multilingual website', 'xili-language'), $theme_name );
			} else {
				$statusXili[] = sprintf (__('This theme <em>%s</em> seems to contain localization function to be used for a multilingual website', 'xili-language'), $theme_name );
			}

			$cb_col = '<img src="'.plugins_url( 'images/xililang-logo-24.png', $this->file_file ).'" alt="xili-language trilogy"/>';
			$action_col = __('More infos about', 'xili-language') . '<br />&nbsp;&nbsp;' . $plugin_data['Name'] ;
			$description_col = implode ( '. ', $statusXili ).'.';
			echo "<tr><th>$cb_col</th><td>$action_col</td><td>$description_col</td></tr>";
		}
	}

	function plugin_status ( $plugin_name, $plugin_file, $status ) {

			if ( is_plugin_active( $plugin_file ) ){
				$plug_status = __('active', 'xili-language');
			} else {
				$plugins = get_plugins();
				if ( isset( $plugins[ $plugin_file ] ) ) {
					$plug_status = __('inactive', 'xili-language');
				} else {
					$plug_status = __('not installed', 'xili-language');
				}
			}

		return sprintf ( __('Plugin %s is %s', 'xili-language'), $plugin_name, $plug_status );
	}

	/**
	 * Add action link(s) to plugins page
	 *
	 * @since 0.9.3
	 * @author MS
	 * @copyright Dion Hulse, http://dd32.id.au/wordpress-plugins/?configure-link and scripts@schloebe.de
	 */
	function more_plugin_actions( $links, $file ){
		$this_plugin = $this->plugin_basename ;
		if( $file == $this_plugin ){
			$settings_link = '<a href="options-general.php?page=language_page">' . __('Settings') . '</a>';
			$links = array_merge( array($settings_link), $links); // before other links
		}
		return $links;
	}




	/********************************** SETTINGS ADMIN UI ***********************************/

	/**
	 * Handle redirects to setup/welcome page after install and updates. (like in WooCommerce)
	 *
	 * Transient must be present, the user must have access rights, and we must ignore the network/bulk plugin updaters.
	 */
	public function admin_redirects() {

		// messages used here and pointer

		$this->changelog = __('Changelog tab of xili-language', 'xili-language');

		wp_register_style( 'xl_welcome_stylesheet', $this->plugin_url.'/xili-css/xl-welcome-style.css' );
		$transient = get_transient( '_xl_activation_redirect' );
		if ( !$transient  || is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		delete_transient( '_xl_activation_redirect' );

		if ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], array( 'xl-about' ) ) ) {
			return;
		}
		$type = ( $transient == 2 ) ? '&xl-updated=1' : '';
		// Here, the welcome page
		wp_safe_redirect( admin_url( 'index.php?page=xl-about'.$type ) );
		exit;
	}

	/**
	 * Add admin menus/screens.
	 * * @since 2.20
	 */
	function admin_welcome() {
		$welcome_page_name  = __( 'About xili-language', 'xili-language' );
		$welcome_page_title = __( 'Welcome to xili-language', 'xili-language' );

		$page = add_dashboard_page( $welcome_page_title, $welcome_page_name, 'manage_options', 'xl-about', array( &$this, 'about_screen' ) );
		add_action( 'admin_print_styles-' . $page, array( &$this, 'admin_welcome_css' ) );
	}

	/**
	 * admin_css function.
	 */
	function admin_welcome_css() {
		wp_enqueue_style( 'xl_welcome_stylesheet' );
	}

	/**
	 * welcome about screen
	 * * @since 2.20
	 */
	function about_screen() {
		?>

		<div class="wrap about-wrap">

			<h1><?php printf( __( 'Welcome to xili-language %s', 'xili-language' ), XILILANGUAGE_VER ); ?></h1>

			<div class="about-text">
				<?php
					if ( ! empty( $_GET['xl-installed'] ) ) {
						$message = __( 'Thanks, all done!', 'xili-language' );
						$type = 1;
					} elseif ( ! empty( $_GET['xl-updated'] ) ) {
						$message = __( 'Thank you for updating to the latest version!', 'xili-language' );
						$type = 2;
					} else {
						$message = __( 'Thanks for installing!', 'xili-language' );
						$type = 0;
					}

					printf( __( '%s xili-language %s is more powerful, stable and secure than ever before. We hope you enjoy using it.', 'xili-language' ), $message, XILILANGUAGE_VER );
				?>
			</div>

			<?php // ?>

			<div class="infolog">
				<div class="changelog">
					<div class="feature-section">
						<?php if ( empty( $_GET['xl-updated'] ) ) { ?>

						<div>
						 	<h4><?php _e( 'What is a multilingual website with xili-language?', 'xili-language' ); ?></h4>
							<p><?php _e( 'With this plugin, your localized website will become a (bi)multilingual website. xili-language trilogy also contains xili-dictionary and xili-tidy-tags plugins.', 'xili-language' ); ?></p>
						</div>

						<?php } ?>

						<div>
							<h4><?php _e( 'Improved Permalinks management', 'xili-language' ); ?></h4>
							<p><?php echo __( 'Now xili-language includes special optional functions provided in example themes (201x-xili child series) to insert language at beginning for the permalink. ', 'xili-language' )
							. '<br />' . __( 'Options are now in expert tab. (These functions were reserved formerly for donators and contributors).', 'xili-language' )
							. '<br />' . __( 'If using or customizing 201x-xili child-theme series: it is fully recommanded to (re)visit and verify languages list and permalink settings page (flush fired).', 'xili-language' );
							?>
							</p>
						</div>
						<div>
							<h4><?php _e( 'Fully customizable by webmasters', 'xili-language' ); ?></h4>
							<p><?php _e( 'According your content “multilingual” strategy, xili-language offers six pages in settings to adapt lot of features. (with online help)', 'xili-language' ); ?></p>
						</div>
						<div class="last-feature">
							<h4><?php _e( 'Entirely designed for developers', 'xili-language' ); ?></h4>
							<p><?php _e( 'Following the WordPress Core rules, including specific elements (tags, shortcode, functions, filters,...) xili-language is a CMS plateform add-on able to work with custom post types without adding tables in db or cookies or redirecting.', 'xili-language' ); ?></p>
							<p><?php printf( __( 'Development until this version (%1$s) are documented here %2$s and inside sources.','xili-language' ),
					XILILANGUAGE_VER,
					'<a href="'.$this->repositorylink.'changelog/" title="'.$this->changelog.'" >'.$this->changelog.'</a>') ; ?></p>
						</div>
					</div>
				</div>
			</div>
			<div class="return-to-setting">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'language_page' ), 'options-general.php' ) ) ); ?>"><?php _e( 'Go to xili-language Settings', 'xili-language' ); ?></a>
			</div>
			<div class="about-footer"><a href="<?php echo $this->repositorylink; ?>" title="xili-language page and docs" target="_blank" style="text-decoration:none" >
				<img class="about-icon" src="<?php echo plugins_url( 'images/xililang-logo-32.png', $this->file_file ) ; ?>" alt="xili-language logo"/>
				</a>&nbsp;&nbsp;&nbsp;©&nbsp;
				<a href="<?php echo $this->devxililink; ?>" target="_blank" title="<?php _e('Author'); ?>" >xiligroup.com</a>™ - msc 2007-2015
			</div>
		</div>
		<?php
	}

	/**
	 * add admin menu and associated pages of admin UI
	 *
	 * @since 0.9.0
	 * @updated 0.9.6 - only for WP 2.7.X - do registering of new meta boxes and JS __(' -','xili-language')
	 * @updated 2.4.1 - sub-pages and tab
	 * @updated 2.11.3 - label and title for tabs and pages
	 *
	 */
	function add_menu_settings_pages() {
		/* browser title and menu title - if empty no menu */
		$this->thehook = add_options_page(__('xili-language plugin','xili-language'). ' - 1', __('Languages ©xili','xili-language'), 'manage_options', 'language_page', array( &$this, 'languages_settings' ) );

		add_action('load-'.$this->thehook, array(&$this,'on_load_page'));

		$this->xl_tabs = array(
				'language_page' => array( 'label' => __( 'Languages list', 'xili-language' ), 'url' => 'options-general.php?page=language_page' ),
				'language_front_set' => array( 'label' => __( 'Languages front-end settings', 'xili-language' ), 'url' => 'options-general.php?page=language_front_set' ),
				'language_expert' => array( 'label' => __( 'Settings for experts', 'xili-language' ), 'url' => 'options-general.php?page=language_expert' ),
				'language_files' => array( 'label' => __( 'Managing language files', 'xili-language' ), 'url' => 'options-general.php?page=language_files' ),
				'author_rules' => array( 'label' => __( 'Managing Authoring rules', 'xili-language' ), 'url' => 'options-general.php?page='.'author_rules' ),
				'language_support' => array( 'label' => __( 'xili-language support', 'xili-language' ), 'url' => 'options-general.php?page=language_support' )
			);

		$this->subpage_titles = array ( 'language_front_set' => __('xili-language plugin','xili-language'). ', 2: ' . $this->xl_tabs['language_front_set']['label'],
			'language_expert' => __('xili-language plugin','xili-language'). ', 3: ' . $this->xl_tabs['language_expert']['label'],
			'language_files' => __('xili-language plugin','xili-language'). ', 4: ' . $this->xl_tabs['language_files']['label'],
			'author_rules' => __('xili-language plugin','xili-language'). ' - ' . $this->xl_tabs['author_rules']['label'],
			'language_support' => __('xili-language plugin','xili-language'). ', 5: ' . $this->xl_tabs['language_support']['label']
			);


		$hooks = array(); // to prepare highlight those in tabs
		$this->thehook2 = add_options_page($this->subpage_titles['language_front_set'], 'xl-front-end', 'manage_options', 'language_front_set', array( &$this, 'frontend_settings' ) );
		add_action('load-'.$this->thehook2, array(&$this,'on_load_page_set'));
		$hooks[] = $this->thehook2;

		$this->thehook4 = add_options_page($this->subpage_titles['language_expert'], 'xl-expert', 'manage_options', 'language_expert', array( &$this, 'languages_expert' ) );
		add_action('load-'.$this->thehook4, array(&$this,'on_load_page_expert'));
		$hooks[] = $this->thehook4;
		// since 2.8.8
		$this->thehook5 = add_options_page($this->subpage_titles['language_files'], __('Languages Files','xili-language'), 'manage_options', 'language_files', array( &$this, 'languages_files' ) );
		add_action('load-'.$this->thehook5, array(&$this,'on_load_page_files'));
		$hooks[] = $this->thehook5;

		// since 2.8.8
		$this->thehook6 = add_options_page($this->subpage_titles['author_rules'], __('Authors rules','xili-language'), 'manage_options', 'author_rules', array( &$this, 'author_rules' ) );
		add_action('load-'.$this->thehook6, array(&$this,'on_load_page_author_rules'));
		$hooks[] = $this->thehook5;

		$this->thehook3 = add_options_page($this->subpage_titles['language_support'], 'xl-support', 'manage_options', 'language_support', array( &$this, 'languages_support' ) );
		add_action('load-'.$this->thehook3, array(&$this,'on_load_page_support'));
		$hooks[] = $this->thehook3;

		// Fudge the highlighted subnav item when on a XL admin page - 2.8.2
		foreach( $hooks as $hook ) {
			add_action( "admin_head-$hook", array(&$this,'modify_menu_highlight' ));
		}

		$this->insert_news_pointer ( 'xl_new_version' ); // pointer in menu for updated version

		add_action( 'admin_print_footer_scripts', array(&$this, 'print_the_pointers_js') );

		// create library of alert messages

		$this->create_library_of_alert_messages ();
	}

	// to remove those visible in tabs - 2.8.2
	function admin_sub_menus_hide() {
		remove_submenu_page( 'options-general.php', 'language_front_set' );
		remove_submenu_page( 'options-general.php', 'language_expert' );
		remove_submenu_page( 'options-general.php', 'language_files' );
		remove_submenu_page( 'options-general.php', 'author_rules' );
		remove_submenu_page( 'options-general.php', 'language_support' );

		//
	}

	// to recover title saved in $submenu and removed when hidding above ! 2.11.3
	function admin_recover_page_title ( $admin_title, $title ) {
		global $current_screen;
		$keys = array_keys ( $this->subpage_titles );
		if ( false !== strpos( $current_screen->base, 'settings_page_' )) {
			$indice = str_replace( 'settings_page_', '', $current_screen->base );
			if (in_array( $indice, $keys )) {
				$admin_title = $this->subpage_titles[$indice] . " " . $admin_title;
			}
		}
		return $admin_title;
	}

	// 2.8.2
	function modify_menu_highlight() {
		global $plugin_page, $submenu_file;

		// This tweaks the Tools subnav menu to only show one XD menu item
		if ( in_array( $plugin_page, array( 'language_expert', 'language_support', 'language_front_set', 'language_files' ) ) )
			$submenu_file = 'language_page';
	}

	// called by each pointer
	function insert_news_pointer ( $case_news ) {
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer', false, array('jquery') );
			++$this->news_id;
			$this->news_case[$this->news_id] = $case_news;
	}
	// insert the pointers registered before
	function print_the_pointers_js ( ) {
		if ( $this->news_id != 0 ) {
			for ($i = 1; $i <= $this->news_id; $i++) {
				$this->print_pointer_js ( $i );
			}
		}
	}

	function print_pointer_js ( $indice ) {

		$args = $this->localize_admin_js( $this->news_case[$indice], $indice );
		if ( $args['pointerText'] != '' ) { // only if user don't read it before
		?>
		<script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready( function() {

		var strings<?php echo $indice; ?> = <?php echo json_encode( $args ); ?>;

	<?php /** Check that pointer support exists AND that text is not empty - inspired www.generalthreat.com */ ?>

	if(typeof(jQuery().pointer) != 'undefined' && strings<?php echo $indice; ?>.pointerText != '') {
		jQuery( strings<?php echo $indice; ?>.pointerDiv ).pointer({
			content : strings<?php echo $indice; ?>.pointerText,
			position: { edge: strings<?php echo $indice; ?>.pointerEdge,
				at: strings<?php echo $indice; ?>.pointerAt,
				my: strings<?php echo $indice; ?>.pointerMy
			},
			close : function() {
				jQuery.post( ajaxurl, {
					pointer: strings<?php echo $indice; ?>.pointerDismiss,
					action: 'dismiss-wp-pointer'
				});
			}
		}).pointer('open');
	}
});
		//]]>
		</script>
		<?php //offset: strings echo $indice;  .pointerOffset  // deprecated
		}
	}

	/**
	 * News pointer for tabs
	 *
	 * @since 2.6.2
	 *
	 */
	function localize_admin_js( $case_news, $news_id ) {
		$about = esc_attr__('Docs about xili-language', 'xili-language');

		//$pointer_Offset = '';
		$pointer_edge = '';
		$pointer_at = '';
		$pointer_my = '';
		switch ( $case_news ) {

			case 'xl_new_version' :
				$pointer_text = '<h3>' . esc_js( __( 'xili-language updated', 'xili-language') ) . '</h3>';
				$pointer_text .= '<p>' . esc_js( sprintf( __( 'xili-language was updated to version %s', 'xili-language' ) , XILILANGUAGE_VER) ). '</p>';

				$pointer_text .= '<p>' . esc_js( sprintf( __( 'This version %1$s is tested with %2$s. (See details in %3$s) ','xili-language' ) , XILILANGUAGE_VER, XILILANGUAGE_WP_TESTED, '<a href="'. $this->repositorylink .'changelog/" title="'. $this->changelog .'" >'. $this->changelog .'</a>' ) ). '</p>';

				$pointer_text .= '<p>' . esc_js( sprintf( __( 'More infos about the previous versions of %1$s here %2$s and %3$s.','xili-language' ) ,
					XILILANGUAGE_VER, '<a href="'.$this->repositorylink.'changelog/" title="'.$this->changelog.'" >'.$this->changelog.'</a>',
					esc_js( ' “<a href="index.php?page=xl-about&xl-updated=1">'. __('in welcome page','xili-language')."</a>”" )
					) ). '</p>';

				$pointer_text .= '<p>' . esc_js( __( 'See settings submenu', 'xili-language' ).' “<a href="options-general.php?page=language_page">'. __('Languages ©xili','xili-language')."</a>”" ). '</p>';
				$pointer_text .= '<p>' . esc_js( sprintf(__( 'Before to question dev.xiligroup support, do not forget to visit %s documentation', 'xili-language' ), '<a href="'. $this->wikilink .'" title="'.$about.'" >wiki</a>' ) ). '</p>';

				$pointer_dismiss = 'xl-new-version-'.str_replace('.', '-', XILILANGUAGE_VER);
				$pointer_div = '#menu-settings';

				$pointer_edge = 'left'; // the arrow
				$pointer_my = 'left+5px'; // relative to the box - margin = 5px
				$pointer_at = 'right'; // relative to div where pointer is attached
				break;

			case 'languages_settings':
				$pointer_text = '<h3>' . esc_js( __( 'To define languages', 'xili-language') ) . '</h3>';
				$pointer_text .= '<p>' . esc_js( __( 'This screen is designed to define the list of languages assigned to this website. Use the form below to add a new language with the help of preset list (popup) or by input your own ISO code.', 'xili-language' ) ). '</p>';
				$pointer_text .= '<p>' . esc_js( sprintf(__( 'Before to question dev.xiligroup support, do not forget to visit %s documentation', 'xili-language' ), '<a href="'. $this->wikilink .'" title="'.$about.'" >wiki</a>' ) ). '</p>';

				$pointer_dismiss = 'xl-settings-news';
				$pointer_div = '#xili-language-lang-list';
				break;

			case 'frontend_settings':
				$pointer_text = '<h3>' . esc_js( __( 'To define front-page', 'xili-language') ) . '</h3>';
				$pointer_text .= '<p>' . esc_js( __( 'This screen contains selectors to define the behaviour of frontpage according languages and visitors browser and more...', 'xili-language' ) ). '</p>';
				$pointer_text .= '<p>' . esc_js( sprintf(__( 'Before to question dev.xiligroup support, do not forget to visit %s documentation', 'xili-language' ), '<a href="'. $this->wikilink .'" title="'.$about.'" >wiki</a>' ) ). '</p>';

				$pointer_dismiss = 'xl-frontend-newss';
				$pointer_div = '#post-body-content';

				$pointer_edge = 'left'; // the arrow
				$pointer_my = 'top'; // relative to the box - margin = 5px
				$pointer_at = 'top-40px'; // relative to div where pointer is attached
				break;

			case 'languages_theme_infos':
				$pointer_text = '<h3>' . esc_js( __( 'Infos about current theme', 'xili-language') ) . '</h3>';
				$pointer_text .= '<p>' . esc_js( __( 'This metabox contains infos about the theme and the joined available language files (.mo).', 'xili-language' ) ). '</p>';
				$pointer_text .= '<p>' . esc_js( sprintf(__( 'Before to question dev.xiligroup support, do not forget to visit %s documentation', 'xili-language' ), '<a href="'. $this->wikilink .'" title="'.$about.'" >wiki</a>' ) ). '</p>';

				$pointer_dismiss = 'xl-frontend-theme-news';
				$pointer_div = '#xili-language-sidebox-theme';

				$pointer_edge = 'top';
				$pointer_my = 'top';
				$pointer_at = 'top+40px';
				break;

			case 'languages_expert':
				$pointer_text = '<h3>' . esc_js( __( 'For documented webmaster', 'xili-language') ) . '</h3>';
				$pointer_text .= '<p>' . esc_js( __( 'This screen contains nice selectors and features to customize menus and other objects for your CMS multilingual website.', 'xili-language' ) ). '</p>';
				$pointer_text .= '<p>' . esc_js( sprintf(__( 'Before to question dev.xiligroup support, do not forget to visit %s documentation', 'xili-language' ), '<a href="'.$this->wikilink.'" title="'.$about.'" >wiki</a>' ) ). '</p>';

				$pointer_dismiss = 'xl-expert-news';
				$pointer_div = '#poststuff';

				$pointer_edge = 'top';
				$pointer_my = 'top';
				$pointer_at = 'top-10px';
				break;

			case 'languages_expert_special':
				$pointer_text = '<h3>' . esc_js( __( 'For documented webmaster', 'xili-language') ) . '</h3>';
				$pointer_text .= '<p>' . esc_js( __( 'This metabox contains advanced selectors and features to customize behaviours for your CMS multilingual website.', 'xili-language' ) ). '</p>';
				$pointer_text .= '<p>' . esc_js( sprintf(__( 'Before to question dev.xiligroup support, do not forget to visit %s documentation', 'xili-language' ), '<a href="'.$this->wikilink.'" title="'.$about.'" >wiki</a>' ) ). '</p>';
				$pointer_dismiss = 'xl-expert-special-news';
				$pointer_div = '#xili-language-sidebox-special';

				$pointer_edge = 'left'; // the arrow
				$pointer_my = 'top'; // relative to the box - margin = 5px
				$pointer_at = 'top-40px'; // relative to div where pointer is attached
				break;

			case 'page_author_rules':

				$pointer_text = '<h3>' . esc_js( __( 'For webmaster and editor', 'xili-language') ) . '</h3>';
				$pointer_text .= '<p>' . esc_js( __( 'This settings page contains advanced selectors and features to customize behaviours when author or editor works in your CMS multilingual website.', 'xili-language' ) ). '</p>';
				$pointer_text .= '<p>' . esc_js( sprintf(__( 'Before to question dev.xiligroup support, do not forget to visit %s documentation', 'xili-language' ), '<a href="'.$this->wikilink.'" title="'.$about.'" >wiki</a>' ) ). '</p>';
				$pointer_dismiss = 'xl-page-author-rules';
				$pointer_div = '#poststuff';

				$pointer_edge = 'left'; // the arrow
				$pointer_my = 'top'; // relative to the box
				$pointer_at = 'right top-80px'; // relative to div where pointer is attached
				break;

			case 'languages_support':
				$pointer_text = '<h3>' . esc_js( __( 'In direct with support', 'xili-language') ) . '</h3>';
				$pointer_text .= '<p>' . esc_js( sprintf(__( 'Before to question dev.xiligroup support, do not forget to check needed website infos and to visit %s documentation', 'xili-language' ), '<a href="'.$this->wikilink.'" title="'.$about.'" >wiki</a>' ) ). '</p>';

				$pointer_dismiss = 'xl-support-news';
				$pointer_div = '#poststuff';

				$pointer_edge = 'left'; // the arrow
				$pointer_my = 'top';
				$pointer_at = 'top'; // relative to div where pointer is attached
				break;

			case 'media_language':
				$pointer_text = '<h3>' . esc_js( __( 'Language of media', 'xili-language') ) . '</h3>';
				$pointer_text .= '<p>' . esc_js( sprintf(__( 'Language concern title, caption and description of media. With clonage approach, the file is shared between version for each language. When modifying a media, new fields are available at end of form. Before to assign language to media, do not forget to visit %s documentation', 'xili-language' ), '<a href="'.$this->wikilink.'" title="'.$about.'" >wiki</a>' ) ). '</p>';

				$pointer_dismiss = 'xl-media-uploads';
				$pointer_div = '#language';

				$pointer_edge = 'right';
				$pointer_my = 'right top+10px';
				$pointer_at = 'left top';
				break;

			default: // nothing
				$pointer_text = '';
		}

			// inspired from www.generalthreat.com
		// Get the list of dismissed pointers for the user
		$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
		if ( in_array( $pointer_dismiss, $dismissed ) && $pointer_dismiss == 'xl-new-version-'.str_replace('.', '-', XILILANGUAGE_VER) ) {
			$pointer_text = '';

		} elseif ( in_array( $pointer_dismiss, $dismissed ) ) {
			$pointer_text = '';
		}

		return array(
			'pointerText' => html_entity_decode( (string) $pointer_text, ENT_QUOTES, 'UTF-8'),
			'pointerDismiss' => $pointer_dismiss,
			'pointerDiv' => $pointer_div,
			'pointerEdge' => ( '' == $pointer_edge ) ? 'top' : $pointer_edge ,
			'pointerAt' => ( '' == $pointer_at ) ? 'left top' : $pointer_at ,
			'pointerMy' => ( '' == $pointer_my ) ? 'left top' : $pointer_my ,
			// 'pointerOffset' => $pointer_Offset, deprecated
			'newsID' => $news_id
		);
	}

	/**
	 * Create list of messages
	 * @since 2.6.3
	 *
	 */
	function create_library_of_alert_messages() {

		$this->admin_messages['alert']['default'] = sprintf(__('See %sWiki%s for more details','xili-language'),'<a href="'.$this->wikilink.'">' ,'</a>');
		$this->admin_messages['alert']['no_load_function'] = sprintf(__('CAUTION: no load_theme_textdomain() in functions.php - review the content of file in the current theme or choose another canonical theme. %s','xili-language'), $this->admin_messages['alert']['default'] ) ;
		$this->admin_messages['alert']['no_load_function_child'] = sprintf(__('CAUTION: no load_theme_textdomain() in functions.php of child theme - review the content of file in the current child theme or leave as is to use only parent theme translation file. %s','xili-language'), $this->admin_messages['alert']['default'] ) ;
		$this->admin_messages['alert']['no_domain_defined'] = __('Theme domain NOT defined','xili-language');

		$this->admin_messages['alert']['menu_auto_inserted'] = sprintf(__('Be aware that language list is already automatically inserted (see above) and %s','xili-language'), $this->admin_messages['alert']['default'] ) ;

		if ( is_multisite() ) {
			$this->admin_messages['alert']['plugin_deinstalling'] = sprintf(__('CAUTION: If checked below, before deactivating xili-language plugin, ALL the xili-language datas in database will be definitively ERASED when this plugin files will be deleted !!! (only multilingual features on <strong>this</strong> website of the WP network (multisite) install). %s', 'xili-language'), $this->admin_messages['alert']['default'] ) ;
		} else {
			$this->admin_messages['alert']['plugin_deinstalling'] = sprintf(__('CAUTION: When checking below, before deactivating xili-language plugin, if delete it through plugins list, ALL the xili-language datas in database will be definitively ERASED when this plugin files will be deleted !!! (only multilingual features). %s', 'xili-language'), $this->admin_messages['alert']['default'] ) ;
		}

		$this->admin_messages['alert']['erasing_language'] = __('Erase (only) multilingual features of concerned posts when this language will be erased !','xili-language');

	}

	/**
	 * Manage list of languages
	 * @since 0.9.0
	 */
	function on_load_page() {
			wp_enqueue_script('common');
			wp_enqueue_script('wp-lists');
			wp_enqueue_script('postbox');
			add_meta_box('xili-language-sidebox-theme', __('Current theme infos','xili-language'), array(&$this,'on_sidebox_4_theme_info'), $this->thehook , 'side', 'high');
			//add_meta_box('xili-language-sidebox-msg', __('Message','xili-language'), array(&$this,'on_sidebox_msg_content'), $this->thehook , 'side', 'core');
			add_meta_box('xili-language-sidebox-info', __('Info','xili-language'), array(&$this,'on_sidebox_info_content'), $this->thehook , 'side', 'core');

			add_meta_box('xili-language-sidebox-uninstall', __('Uninstall Options','xili-language'), array(&$this,'on_sidebox_uninstall_content'), $this->thehook , 'side', 'low');

			$this->insert_news_pointer ( 'languages_settings' ); // news pointer 2.6.2

	}

	/**
	 * Manage settings of languages behaviour in front-end (theme)
	 * @since 2.4.1
	 */
	function on_load_page_set() {
			wp_enqueue_script('common');
			wp_enqueue_script('wp-lists');
			wp_enqueue_script('postbox');

			add_meta_box('xili-language-sidebox-theme', __('Current theme infos','xili-language'), array(&$this,'on_sidebox_4_theme_info'), $this->thehook2 , 'side', 'high');
			add_meta_box('xili-language-sidebox-info', __('Info','xili-language'), array(&$this,'on_sidebox_info_content'), $this->thehook2 , 'side', 'core');

			$this->insert_news_pointer ( 'frontend_settings' ); // news pointer 2.6.2
			$this->insert_news_pointer ( 'languages_theme_infos' );
	}

	/**
	 * Settings by experts and info
	 * @since 2.4.1
	 */
	function on_load_page_expert() {
			wp_enqueue_script('common');
			wp_enqueue_script('wp-lists');
			wp_enqueue_script('postbox');

			add_meta_box('xili-language-sidebox-theme', __('Current theme infos','xili-language'), array(&$this,'on_sidebox_4_theme_info'), $this->thehook4 , 'side', 'high');

			add_meta_box('xili-language-sidebox-info', __('Info','xili-language'), array(&$this,'on_sidebox_info_content'), $this->thehook4 , 'side', 'core');

			$this->insert_news_pointer ( 'languages_expert' ); // news pointer 2.6.2
			$this->insert_news_pointer ( 'languages_expert_special' );
	}

	/**
	 * Settings by experts and info
	 * @since 2.4.1
	 */
	function on_load_page_files() {
			wp_enqueue_script('common');
			wp_enqueue_script('wp-lists');
			wp_enqueue_script('postbox');

			add_meta_box('xili-language-sidebox-theme', __('Current theme infos','xili-language'), array(&$this,'on_sidebox_4_theme_info'), $this->thehook5 , 'side', 'high');

			add_meta_box('xili-language-sidebox-info', __('Info','xili-language'), array(&$this,'on_sidebox_info_content'), $this->thehook5 , 'side', 'core');

	}

	/**
	 * Settings by experts and info
	 * @since 2.4.1
	 */
	function on_load_page_author_rules() {
			wp_enqueue_script('common');
			wp_enqueue_script('wp-lists');
			wp_enqueue_script('postbox');

			add_meta_box('xili-language-sidebox-theme', __('Current theme infos','xili-language'), array(&$this,'on_sidebox_5_theme_info'), $this->thehook6 , 'side', 'high');

			add_meta_box('xili-language-sidebox-info', __('Info','xili-language'), array(&$this,'on_sidebox_info_content'), $this->thehook6 , 'side', 'core');

			$this->insert_news_pointer ( 'page_author_rules' ); // news pointer 2.12.1
	}


	/**
	 * Support and info
	 * @since 2.4.1
	 */
	function on_load_page_support() {
			wp_enqueue_script('common');
			wp_enqueue_script('wp-lists');
			wp_enqueue_script('postbox');

			add_meta_box('xili-language-sidebox-info', __('Info','xili-language'), array(&$this,'on_sidebox_info_content'), $this->thehook3 , 'side', 'core');

			$this->insert_news_pointer ( 'languages_support' ); // news pointer 2.6.2
	}


	/******************************** Main Settings screens *************************/

	/**
	 * to display the languages settings admin UI
	 *
	 * @since 0.9.0
	 * @updated 0.9.6 - only for WP 2.7.X - do new meta boxes and JS
	 *
	 */
	function languages_settings() {

		$formtitle = __('Add a language', 'xili-language'); /* translated in form */
		$submit_text = __('Add &raquo;','xili-language');
		$cancel_text = __('Cancel');
		$action = '';
		$actiontype = '';
		$language = (object) array ('name' => '', 'slug' => '', 'description' => '', 'term_order' => '' ); //2.2.3


		$msg = 0 ; /* 1.7.1 */
		if (isset($_POST['reset'])) {
			$action =$_POST['reset'];
		} elseif ( isset($_POST['updateoptions']) ) {
			$action ='updateoptions';
		} elseif ( isset($_POST['updateundefined'])) {
			$action ='updateundefined';
		} elseif ( isset($_POST['menuadditems'])) {
			$action ='menuadditems';
		} elseif ( isset($_POST['sendmail']) ) { //1.8.5
			$action = 'sendmail' ;
		} elseif ( isset($_POST['uninstalloption']) ) { //1.8.8
			$action = 'uninstalloption' ;
		} elseif ( isset($_POST['action'])) {
			$action=$_POST['action'];
		}

		if ( isset($_GET['action']) )
			$action=$_GET['action'];
		if ( isset($_GET['term_id']) )
			$term_id = $_GET['term_id'];


		$theme_name = get_option("current_theme"); // full name

		switch( $action ) {

			case 'uninstalloption' ; // 1.8.8 see Uninstall Options metabox in sidebar
				$this->xili_settings['delete_settings'] = $_POST['delete_settings'];
				update_option('xili_language_settings', $this->xili_settings);
				break;

			case 'add';
				check_admin_referer( 'xili-language-settings' );
				$term = $_POST['language_name'];
				if ("" != $term ) {
					$slug = $_POST['language_nicename'];
					$args = array( 'alias_of' => '', 'description' => $_POST['language_description'], 'parent' => 0, 'slug' =>$slug );

					$term_data = $this->safe_lang_term_creation ( $term, $args ); 
					$doit = false;
					if ( ! is_wp_error($term_data) ) {  

						wp_set_object_terms($term_data['term_id'], 'the-langs-group', TAXOLANGSGROUP);
						update_term_order ($term_data['term_id'],$this->langs_group_tt_id,$_POST['language_order']);
						$doit = true;

					} else {	// error need insertion in group if existing term is ok	
						$doit = $this->safe_insert_in_language_group ( $term_data, $_POST['language_order'] ) ;
					}

					if ( $doit ) {

						$this->xili_settings['langs_list_status'] = "added"; // 1.6.0
						$lang_ids = $this->get_lang_ids();
						//$this->available_langs = $lang_ids ;
						$this->xili_settings['available_langs'] = $lang_ids;
						$this->xili_settings['lang_features'][$slug]['hidden'] = ( isset($_POST['language_hidden']) ) ? $_POST['language_hidden'] : "" ;
						$this->xili_settings['lang_features'][$slug]['charset'] = ( isset($_POST['language_charset'])) ? $_POST['language_charset'] : "";
						$this->xili_settings['lang_features'][$slug]['alias'] = ( isset($_POST['language_alias'])) ? $_POST['language_alias'] : ""; // 2.8.2
						$this->xili_settings['theme_alias_cache'][$theme_name][$slug] = ( isset($_POST['language_alias'])) ? $_POST['language_alias'] : "";
						update_option('xili_language_settings', $this->xili_settings);

						$this->get_lang_slug_ids('edited'); // flush - 2.9.21
						$actiontype = "add";

						$msg = 5;

					} else {
						$msg = 10;
					}

				} else {
						$msg = 10; 
				}
				break;

			case 'edit';
				// check id
				if ( isset ($_GET['term_id']) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'edit-' . $_GET['term_id'] ) ) {
					$actiontype = "edited";
					$language = get_term_and_order ($term_id, $this->langs_group_tt_id, TAXONAME);
					$submit_text = __('Update &raquo;', 'xili-language');
					$formtitle = __('Edit language', 'xili-language');

					$msg = 3;

				} else {
					wp_die( __( 'Security check', 'xili-language' ) );
				}
				break;

			case 'edited';
				check_admin_referer( 'xili-language-settings' );
				$actiontype = "add";
				$term_id = $_POST['language_term_id'];
				$term = $_POST['language_name']; // 2.4
				$slug = $_POST['language_nicename'];
				$args = array( 'name' => $term, 'alias_of' => '', 'description' => $_POST['language_description'], 'parent' => 0, 'slug' => $slug);
				$theids = wp_update_term( $term_id, TAXONAME, $args);
				if ( !is_wp_error($theids) ) {
					wp_set_object_terms($theids['term_id'], 'the-langs-group', TAXOLANGSGROUP);
					update_term_order ($theids['term_id'],$this->langs_group_tt_id,$_POST['language_order']);
					$this->xili_settings['langs_list_status'] = "edited"; // 1.6.0
					$this->xili_settings['lang_features'][$slug]['hidden'] = ( isset ( $_POST['language_hidden'] ) ) ? $_POST['language_hidden'] : "";
					$this->xili_settings['lang_features'][$slug]['charset'] = $_POST['language_charset'];

					$this->xili_settings['lang_features'][$slug]['alias'] = ( isset($_POST['language_alias'])) ? $_POST['language_alias'] : ""; // 2.8.2
					$this->xili_settings['theme_alias_cache'][$theme_name][$slug] = ( isset($_POST['language_alias'])) ? $_POST['language_alias'] : "";

					update_option('xili_language_settings', $this->xili_settings);

					$this->get_lang_slug_ids('edited');

					$msg = 4 ;
				} else {
					$msg = 8 ;

				}
				break;

			case 'delete';
				// check id
				if ( isset ($_GET['term_id']) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'delete-' . $_GET['term_id'] ) ) {

					$actiontype = "deleting";
					$submit_text = __('Delete &raquo;','xili-language');
					$formtitle = __('Delete language ?', 'xili-language');
					$language = get_term_and_order ($term_id,$this->langs_group_tt_id,TAXONAME);

					$msg = 1;

				} else {
					wp_die( __( 'Security check', 'xili-language' ) );
				}
				break;
			case 'deleting';
				check_admin_referer( 'xili-language-settings' );
				$actiontype = "add";
				$term_id = $_POST['language_term_id'];
				$slug = $_POST['language_nicename'];
				if ( isset ( $_POST['multilingual_links_erase'] ) && $_POST['multilingual_links_erase'] == 'erase' ) {
					$this->multilingual_links_erase ( $term_id ); // as in uninstall.php - 1.8.8
				}

				wp_delete_object_term_relationships( $term_id, TAXOLANGSGROUP ); // degrouping
				wp_delete_term( $term_id, TAXONAME );

				$this->xili_settings['langs_list_status'] = "deleted"; // 1.6.0
				$lang_ids = $this->get_lang_ids();
				//$this->available_langs = $lang_ids ;
				$this->xili_settings['available_langs'] = $lang_ids;
				unset ( $this->xili_settings['lang_features'][$slug] );
				update_option('xili_language_settings', $this->xili_settings);

				$msg = 2;
				break;

			case 'refreshlinks'	; // refresh from PLL
				check_admin_referer( 'refresh_pll_links' );
				$themessages[11] = apply_filters ( 'recreate_links_from_previous', array() ); // in pll_functions.php file
				$msg = 11;
				$actiontype = "add";
				break;

			case 'reset';
				$actiontype = "add";
				break;

			default :
				$actiontype = "add";



		}
		/* register the main boxes always available */
		add_meta_box('xili-language-lang-list', __('List of languages','xili-language'), array(&$this,'on_box_lang_list_content'), $this->thehook , 'normal', 'high');
		add_meta_box('xili-language-lang-form', __('Language','xili-language'), array(&$this,'on_box_lang_form_content'), $this->thehook , 'normal', 'high');

		$themessages[1] = __('A language to delete.','xili-language');
		$themessages[2] = __('A language was deleted.','xili-language');
		$themessages[3] = __('Language to update.','xili-language');
		$themessages[4] = __('A language was updated.','xili-language');
		$themessages[5] = __('A new language was added.','xili-language');
		$themessages[8] = __('Error when updating.','xili-language');
		$themessages[10] = __('Error when adding.','xili-language');

		/* form datas in array for do_meta_boxes() */
		$language_features = ( isset( $this->xili_settings['lang_features'][$language->slug] ) && '' != $language->slug ) ? $this->xili_settings['lang_features'][$language->slug] : array('charset'=>"",'hidden'=>"");

		$data = array(
			'action'=>$action, 'formtitle'=>$formtitle, 'language'=>$language,'submit_text'=>$submit_text,'cancel_text'=>$cancel_text,
			'language_features' => $language_features
		);
		?>

		<div id="xili-language-settings" class="wrap columns-2 minwidth" >
			<?php screen_icon('options-general'); ?>
			<h2><?php _e('Languages','xili-language') ?></h2>
			<h3 class="nav-tab-wrapper">
			<?php $this->set_tabs_line() ?>
			</h3>

			<?php if (0!= $msg ) { ?>
			<div id="message" class="updated fade"><p><?php echo $themessages[$msg]; ?></p></div>
			<?php } ?>
			<form name="add" id="add" method="post" action="options-general.php?page=language_page">
				<input type="hidden" name="action" value="<?php echo $actiontype ?>" />
				<?php wp_nonce_field('xili-language-settings'); ?>
				<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );
				$this->setting_form_content( $this->thehook, $data );
				?>
		</form>
		</div>
		<?php $this->setting_form_js( $this->thehook );
	}

	// new UI for frontend - 2.12

	function set_frontend_settings_fields() {
		register_setting( $this->settings_frontend . '_group', $this->settings_frontend, array( $this,'settings_frontend_validate_settings' ) );
	}

		/**
	 * Settings page for front-end features
	 *
	 * @since 2.12.0
	 */
	function frontend_settings() {
		$themessages = array('ok');
		$emessage = "";
		$action = '';

		$data = array(
			'action'=>$action, 'emessage'=>$emessage
		);
		add_meta_box('xili-language-frontend-settings', __('Front-end behaviour settings','xili-language'), array(&$this,'on_box_frontend_settings'), $this->thehook2 , 'normal', 'low');

		add_settings_section( 'option_front_section_1', __('Frontpage (home) options', 'xili-language'), array( $this, 'display_one_section'), $this->settings_frontend .'_group');

		// browseroption
		$frontend_language_options = array(
			"" => __('Software defined','xili-language'),
			"browser" => __("Language of visitor's browser",'xili-language'),
			);
		$listlanguages = get_terms_of_groups_lite ($this->langs_group_id,TAXOLANGSGROUP,TAXONAME,'ASC');
		foreach ($listlanguages as $language) {
			$frontend_language_options[$language->slug] = translate( $language->description, 'xili-language');
		}

		$field_args = array(
			'option_name'	=> $this->settings_frontend,
			'title'			=> __('Language of the home webpage', 'xili-language'),
			'type'			=> 'select',
			'id'			=> 'browseroption',
			'name'			=> 'browseroption',
			'desc'			=> __('Here select how or what will be language of the home webpage when a visitor is coming.', 'xili-language'),
			'std'			=> 'browser',
			'label_for'		=> 'browseroption',
			'class'			=> 'css_class settings',
			'option_values' => $frontend_language_options
		);

		add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'display_one_setting'), $this->settings_frontend .'_group', 'option_front_section_1', $field_args );

		// lang_neither_browser select
		if ( $this->xili_settings['browseroption'] == 'browser' ) {

			$not_found_language_options = array (
					"" => __("Language of dashboard",'xili-language')
				);
			foreach ($listlanguages as $language) {
				$not_found_language_options[$language->slug] = translate( $language->description, 'xili-language');
			}

			$field_args = array(
				'option_name'	=> $this->settings_frontend,
				'title'			=> __("if language is not found",'xili-language'),
				'type'			=> 'select',
				'id'			=> 'lang_neither_browser',
				'name'			=> 'lang_neither_browser',
				'desc'			=> __('Here select what will be language of the home webpage when the language of the browser is not available inside website.', 'xili-language'),
				'std'			=> '',
				'label_for'		=> 'lang_neither_browser',
				'class'			=> 'css_class settings',
				'option_values' => $not_found_language_options
			);

			add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'display_one_setting'), $this->settings_frontend .'_group', 'option_front_section_1', $field_args );
		}

		// homelang checkbox
		if ( !$this->show_page_on_front ) {

			$field_args = array(
				'option_name'	=> $this->settings_frontend,
				'title'			=> __('Modify home query','xili-language'),
				'type'			=> 'checkbox',
				'id'			=> 'homelang',
				'name'			=> 'homelang',
				'desc'			=> __('If checked, latest posts will be selected according current language.', 'xili-language') ,
				'std'			=> 'modify',
				'label_for'		=> 'homelang',
				'class'			=> 'css_class settings'
			);

			add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'display_one_setting'), $this->settings_frontend .'_group', 'option_front_section_1', $field_args );
		}

		// pforp_select select
		$page_for_posts = get_option('page_for_posts');
		if ( $page_for_posts ) {
			$page_for_posts_options = array(
				'no-select' => __("No selection of latest posts",'xili-language'),
				'select' => __("Selection of latest posts",'xili-language')
				);

			$field_args = array(
				'option_name'	=> $this->settings_frontend,
				'title'			=> __("In list inside (blog)page",'xili-language'),
				'type'			=> 'select',
				'id'			=> 'pforp_select',
				'name'			=> 'pforp_select',
				'desc'			=> __('Here decide (or not) subselection of latest posts according current language.', 'xili-language'),
				'std'			=> 'select',
				'label_for'		=> 'pforp_select',
				'class'			=> 'css_class settings',
				'option_values' => $page_for_posts_options
			);

			add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'display_one_setting'), $this->settings_frontend .'_group', 'option_front_section_1', $field_args );
		}

		add_settings_section( 'option_front_section_3', __('Navigation menus options', 'xili-language'), array( $this, 'display_one_section'), $this->settings_frontend .'_group');

		$field_args = array(
				'option_name'	=> $this->settings_frontend,
				'title'			=> __('Home menu item with sub-selection by language.', 'xili-language'),
				'type'			=> 'checkbox',
				'id'			=> 'home_item_nav_menu',
				'name'			=> 'home_item_nav_menu',
				'desc'			=> __('If checked, link under home menu item with be completed by language for sub-selection.', 'xili-language') ,
				'std'			=> 'modify',
				'label_for'		=> 'home_item_nav_menu',
				'class'			=> 'css_class settings'
			);
		add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'display_one_setting'), $this->settings_frontend .'_group', 'option_front_section_3', $field_args );
// categories
		add_settings_section( 'option_front_section_2', __('Categories options', 'xili-language'), array( $this, 'display_one_section'), $this->settings_frontend .'_group');

		$categories_options = array(
				'' => __('Software defined','xili-language'),
				'browser' => __("Language of visitor's browser",'xili-language'),
				'firstpost' => __("Language of first post in loop",'xili-language')
				);

		$field_args = array(
			'option_name'	=> $this->settings_frontend,
			'title'			=> __("Theme terms if category list",'xili-language'),
			'type'			=> 'select',
			'id'			=> 'allcategories_lang',
			'name'			=> 'allcategories_lang',
			'desc'			=> __("Theme's language when categories in 'all'", 'xili-language'),
			'std'			=> 'browser',
			'label_for'		=> 'allcategories_lang',
			'class'			=> 'css_class settings',
			'option_values' => $categories_options
		);

		add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'display_one_setting'), $this->settings_frontend .'_group', 'option_front_section_2', $field_args );

		?>
		<div id="xili-language-frontend" class="wrap columns-2 minwidth">
			<?php screen_icon('options-general'); ?>
			<h2><?php _e('Front-end settings','xili-language') ?></h2>
			<h3 class="nav-tab-wrapper">
			<?php $this->set_tabs_line() ?>
			</h3>

			<p class="width23 boldtext">
			<?php printf(__("This settings screen contains miscellaneous features to define behaviour in frontend side.",'xili-language'),'<a href="' . $this->repositorylink . '" target="_blank">','</a>' ); ?>
			</p>

			<?php $this->setting_form_content( $this->thehook2, $data ); ?>
		</div>
		<?php
		$this->setting_form_js( $this->thehook2 );

	}

	function on_box_frontend_settings() {
		?>
		<div class="list-settings frontend-settings">
		<form name="frontend_settings" id="frontend_settings" method="post" enctype="multipart/form-data" action="options.php">
			<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
			wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );
			settings_fields( $this->settings_frontend . '_group' ); // nonce, action (plugin.php)
			do_settings_sections( $this->settings_frontend . '_group' );
			?>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php printf(__('%1$s of %2$s', 'xili-language') ,__('Save Changes'), __('Front-end behaviour settings', 'xili-language')); ?>" />
			</p>
			<div class="clearb1">&nbsp;</div>
		</form>
		</div>
		<?php
	}

	function get_frontend_settings_options() { // not used yet
		//return get_option( $this->settings_frontend, $this->get_default_frontend_settings_options() );
		$default_array = $this->get_default_frontend_settings_options();
		$values_array = array (
			'browseroption' => $this->xili_settings['browseroption'],
			'lang_neither_browser' => $this->xili_settings['lang_neither_browser'],
			'homelang' => $this->xili_settings['homelang'],
			'pforp_select' => $this->xili_settings['pforp_select'],
			'home_item_nav_menu' => $this->xili_settings['home_item_nav_menu'],

			'allcategories_lang' => $this->xili_settings['allcategories_lang']
			);
		return ( array_merge ( $default_array, $values_array ) );
	}

	function get_default_frontend_settings_options() {
		return array(
			'browseroption' => 'browser',
			'lang_neither_browser' => '',
			'homelang' => '',
			'pforp_select' => 'select',
			'home_item_nav_menu' => '',
			'allcategories_lang' => 'browser',
			);
	}

	function settings_frontend_validate_settings( $input ) {

		if ( isset( $input['browseroption'] ) ) $this->xili_settings['browseroption'] = $input['browseroption'] ;
		if ( isset( $input['lang_neither_browser'] ) ) $this->xili_settings['lang_neither_browser'] = $input['lang_neither_browser'] ;
		$this->xili_settings['homelang'] = ( isset( $input['homelang'] ) ) ? $input['homelang'] : "" ; // because checkbox
		if ( isset( $input['pforp_select'] ) ) $this->xili_settings['pforp_select'] = $input['pforp_select'] ;
		$this->xili_settings['home_item_nav_menu'] = ( isset( $input['home_item_nav_menu'] ) ) ? $input['home_item_nav_menu'] : "" ; // because checkbox
		if ( isset( $input['allcategories_lang'] ) ) $this->xili_settings['allcategories_lang'] = $input['allcategories_lang'] ;

		update_option('xili_language_settings', $this->xili_settings );	// based on original settings
		return $input;
	}


	/**
	 * Support page
	 *
	 * @since 2.4.1
	 */
	function languages_expert() {

		$msg = 0;
		$themessages = array('ok');
		$action = '';

		$optionmessage = '';

		if (isset($_POST['reset'])) {
			$action = $_POST['reset'];
		} elseif ( isset($_POST['menuadditems'])) {
			$action = 'menuadditems';
		} elseif ( isset($_POST['updatespecials'])) {
			$action = 'updatespecials';
		} elseif ( isset($_POST['innavenable']) || isset($_POST['pagnavenable']) ) {
			$action = 'menunavoptions';
		} elseif ( isset($_POST['jetpack_fc_enable'])) {
			$action = 'jetpack_fc_enable';
		} elseif ( isset($_POST['xl-bbp-addon-integrate'])) { // 2.18
			$action = 'xl-bbp-addon-integrate';
		}

		switch( $action ) {
			case 'menuadditems';
				check_admin_referer( 'xili-language-expert' );
				$this->xili_settings['navmenu_check_option2'] = $_POST['xili_navmenu_check_option2']; // 1.8.1

				$result = $this->add_list_of_language_links_in_wp_menu($this->xili_settings['navmenu_check_option2']);

				$msg = 1;

				break;

			case 'menunavoptions';
				check_admin_referer( 'xili-language-expert' );
				if ( current_theme_supports( 'menus' ) ) {
					$menu_locations = get_nav_menu_locations();
					$selected_menu_locations = array();
					if ( $menu_locations ) {
						$pagenablelist = '';
						foreach ($menu_locations as $menu_location => $location_id) {
							if ( isset ( $_POST['xili_navmenu_check_option_'.$menu_location] ) && $_POST['xili_navmenu_check_option_'.$menu_location] == 'enable' ) {
								$selected_menu_locations[$menu_location]['navenable'] = 'enable';
								$selected_menu_locations[$menu_location]['navtype'] = $_POST['xili_navmenu_check_optiontype_'.$menu_location]; //0.9.1
							}
							// page list in array 2.8.4.3
							$enable = ( isset ( $_POST['xili_navmenu_check_option_page_'.$menu_location] ) && $_POST['xili_navmenu_check_option_page_'.$menu_location] == 'enable' ) ? 'enable' : '' ;
							$pagenablelist .= $enable;
							$args = $_POST['xili_navmenu_page_args_'.$menu_location];
							$thenewvalue = array( 'enable'=> $enable, 'args'=> $args );
							$this->xili_settings['array_navmenu_check_option_page'][$menu_location] = $thenewvalue;
						}

						$this->xili_settings['page_in_nav_menu_array'] = $pagenablelist ;

					} else {
						$optionmessage = '<strong>'.__('Locations menu not set: go to menus settings','xili-language').'</strong> ';
					}
					$this->xili_settings['navmenu_check_options'] = $selected_menu_locations; // 2.1.0

					$this->xili_settings['in_nav_menu'] = ( isset($_POST['list_in_nav_enable'] ) ) ? $_POST['list_in_nav_enable'] : ""; // 1.6.0
					//$this->xili_settings['page_in_nav_menu'] = ( isset($_POST['page_in_nav_enable'] ) ) ? $_POST['page_in_nav_enable'] : ""; // 1.7.1
					//$this->xili_settings['args_page_in_nav_menu'] = ( isset($_POST['args_page_in_nav'] ) ) ? $_POST['args_page_in_nav'] : ""; // 1.7.1

					$this->xili_settings['nav_menu_separator'] = stripslashes($_POST['nav_menu_separator']) ;

					$this->xili_settings['navmenu_check_option'] = ( isset($_POST['xili_navmenu_check_option'] ) ) ? $_POST['xili_navmenu_check_option'] : "";
					$this->xili_settings['list_pages_check_option'] = ( isset($_POST['xili_list_pages_check_option'] ) ) ? $_POST['xili_list_pages_check_option'] : ""; // 2.8.4.4

					// new method if more than one nav-menu 2.8.4.3

					$this->xili_settings['home_item_nav_menu'] = ( isset($_POST['xili_home_item_nav_menu'] ) ) ?$_POST['xili_home_item_nav_menu'] : ""; // 1.8.9.2
				// 1.8.1
				}
				/* UPDATE OPTIONS */
				update_option('xili_language_settings', $this->xili_settings);
				/* messages */
				$optionmessage .= " - ".sprintf(__("Options are updated: Automatic Nav Menu = %s, Selection of pages in Nav Menu = %s",'xili-language'), $this->xili_settings['in_nav_menu'], $this->xili_settings['page_in_nav_menu']);

				$msg = 1;


				break;

			case 'updatespecials':
				check_admin_referer( 'xili-language-expert' );
				$special_msg = array();
				// here (and not theme options) 2.20
				$lang_permalink = ( isset($_POST['lang_permalink'] ) ) ? $_POST['lang_permalink'] : 'perma_not';
				if ( $lang_permalink != $this->xili_settings['lang_permalink']) {
					$this->xili_settings['lang_permalink'] = $lang_permalink;
					$special_msg[] = sprintf(__('Language begins permalink: %s ', 'xili-language'), $this->xili_settings['lang_permalink']);
					// 2.20.3
					if ( $this->xili_settings['lang_permalink'] != 'perma_not' ) {
						/*
						$result = apply_filters ('xl_import_previous_aliases', false );
						if ( $result ) {
							$special_msg[] = ' ' . __('Alias imported from Polylang.', 'xili-language');
						}
						*/
					}
				}

				/* force rules flush - 2.1.1 */
				if ( isset($_POST['force_permalinks_flush'] ) && $_POST['force_permalinks_flush'] == 'enable' ) {
					$this->get_lang_slug_ids('edited'); // if list need refresh - 2013-11-24
					$special_msg[] = __('permalinks flushed', 'xili-language');
				}
				/* domains switching settings 1.8.7 */
				$temp_domains_settings = $this->xili_settings['domains'] ;
				foreach ( $this->xili_settings['domains'] as $domain => $state ) {
					if ( isset( $_POST['xili_language_domains_'.$domain] ) ) {
						$this->xili_settings['domains'][$domain] = $_POST['xili_language_domains_'.$domain];
						if ( $domain != 'default') {
							$this->xili_settings['domain_paths'][$domain] = $_POST['xili_language_domain_path_'.$domain];
							$this->xili_settings['plugin_paths'][$domain] = $_POST['xili_language_plugin_path_'.$domain]; // hidden input
						}
					} else {
						unset ( $this->xili_settings['domains'][$domain] ); // for unactivated plugin
					}
				}
				if ( $temp_domains_settings != $this->xili_settings['domains'] ) {
					$special_msg[] = ' ' . __('Domains switching settings changed', 'xili-language');
				}
				$temp_wp_locale = ( isset($_POST['xili_language_wp_locale'] ) ) ? $_POST['xili_language_wp_locale'] : "db_locale";
				if ( $temp_wp_locale != $this->xili_settings['wp_locale'] ) {
					$this->xili_settings['wp_locale'] = $temp_wp_locale;
					$special_msg[] = sprintf(__('Locale changed: %s', 'xili-language'), $this->xili_settings['wp_locale']);
				}


				/* UPDATE OPTIONS */
				update_option('xili_language_settings', $this->xili_settings);
				/* messages */
				if ( $special_msg )
					$optionmessage .= " - ".sprintf(__("Options are updated ( %s )",'xili-language'), implode (' & ', $special_msg ) );
				else
					$optionmessage .= " - " . __('no change', 'xili-language');

				$msg = 1;
				break;

			case 'jetpack_fc_enable':
				check_admin_referer( 'xili-language-expert' );
				if ( isset($_POST['enable_fc_theme_class'] ) && $_POST['enable_fc_theme_class'] == 'enable' ) {
					$this->xili_settings['enable_fc_theme_class'] = 'enable';
				} else {
					$this->xili_settings['enable_fc_theme_class'] = 'disable';
				}
				update_option('xili_language_settings', $this->xili_settings);
				$optionmessage = sprintf(__("Settings for JetPack are updated to ‘%s’.",'xili-language'), $this->xili_settings['enable_fc_theme_class']);
				$msg = 1; // green
			break;

			case 'xl-bbp-addon-integrate':
				check_admin_referer( 'xili-language-expert' );
				update_option ('xl-bbp-addon-activated-folder', $_POST['xl-bbp-addon'] );
				$optionmessage = sprintf(__('Settings for bbPress are updated to %1$s. Now %2$s','xili-language'), ($_POST['xl-bbp-addon'] != '') ? $_POST['xl-bbp-addon'] : __('no integration', 'xili-language'), '<a href="" >'. __('click to refresh dashboard...','xili-language').'</a>');
				$msg = 1; // green
			break;

			default:
				# do action via filters  set in importer functions - 2.20.3
				if ( isset( $_POST ) ) {
					$optionmessage = apply_filters ( 'import_list_of_actions', '', $_POST );
					if ( $optionmessage ) $msg = 1; // green
				}
			break;
		}

		$box_expert_title = ( has_filter ('clean_previous_languages_list') ) ?
			__('Special settings and actions','xili-language') : __('Special settings (JetPack,...)','xili-language'); //2.20.3

		add_meta_box('xili-language-box-3', __('Navigation menus','xili-language'), array(&$this,'on_box_expert'), $this->thehook4 , 'normal', 'high');
		add_meta_box('xili-language-sidebox-special', __('Special','xili-language'), array(&$this,'on_sidebox_for_specials'), $this->thehook4 , 'normal', 'high');
		add_meta_box('xili-language-box-3-2', $box_expert_title , array(&$this,'on_box_plugins_expert'), $this->thehook4 , 'normal', 'high');
		$themessages[1] = $optionmessage ;
		$data = array(
			'action'=>$action, 'list_in_nav_enable' => $this->xili_settings['in_nav_menu']
			);
		?>
		<div id="xili-language-support" class="wrap columns-2 minwidth">
			<?php screen_icon('options-general'); ?>
			<h2><?php _e('Languages','xili-language') ?></h2>
			<h3 class="nav-tab-wrapper">
			<?php $this->set_tabs_line() ?>
			</h3>

			<?php if (0!= $msg ) { ?>
			<div id="message" class="updated fade"><p><?php echo $themessages[$msg]; ?></p></div>
			<?php } ?>
			<form name="expert" id="expert" method="post" action="options-general.php?page=language_expert">
				<?php wp_nonce_field('xili-language-expert'); ?>
				<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );
				$this->setting_form_content( $this->thehook4, $data );
			?>
			</form>
		</div>
		<?php $this->setting_form_js( $this->thehook4 );

	}


	/**
	 * Navigation menu: new ways to insert language list via menu builder screen
	 *
	 * @since 2.8.8
	 *
	 */
	function add_language_nav_menu_meta_boxes() {
		add_meta_box(
			'insert-xl-list',
			sprintf ( __('%s Languages list', 'xili-language'), '[©xili]'),
			array( $this, 'language_nav_menu_link'),
			'nav-menus',
			'side',
			'high'
		);
	}

	// called by above filter
	function language_nav_menu_link() {
			// div id in submit below
		global $_nav_menu_placeholder, $nav_menu_selected_id;
		$_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;
			?>
		<div id="posttype-xllist" class="posttypediv">
			<div id="tabs-panel-xllist" class="tabs-panel tabs-panel-active">
				<ul id ="xllist-checklist" class="categorychecklist form-no-clear">

					<?php
					foreach ($this->langs_list_options as $typeoption) {
						if ( false !== strpos( $typeoption[0], 'navmenu' ) ) {
					// list according available types of menu
					?>
					<li>
						<label title="<?php echo $typeoption[2]; ?>" class="menu-item-title">
							<input type="radio" class="menu-item-checkbox" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" value="-1">&nbsp;<?php _e( $typeoption[1], 'xili-language' ); ?>
						</label>
						<input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="custom">
						<input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="<?php echo $this->insertion_point_box_title; ?>">
						<input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" value="<?php echo $this->insertion_point_dummy_link; ?>">
						<input type="hidden" class="menu-item-attr-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-attr-title]" value="<?php echo $typeoption[2]; ?>">
						<input type="hidden" class="menu-item-classes" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-classes]" value="xl-list <?php echo $typeoption[0]; ?>">
					</li>
					<?php } } ?>

				</ul>
			</div>
			<p class='description'><?php _e( 'Check to decide what type of languages menu. Only an insertion point will be placed inside the menu. The content of the language list will be automatically made according navigation rules and contexts.', 'xili-language' ); ?></p>
			<p class="button-controls">
				<span class="list-controls">

				</span>
				<span class="add-to-menu">
					<input type="submit" <?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-post-type-menu-item" id="submit-posttype-xllist">
					<span class="spinner"></span>
				</span>
			</p>
		</div>
		<?php
	}

	// prepares javascript to modify the languages list menu item
	function admin_enqueue_menu_script() {
		$screen = get_current_screen();
		if ('nav-menus' != $screen->base)
			return;

		$suffix = defined( 'WP_DEBUG') && WP_DEBUG ? '.dev' : '.min';
		wp_enqueue_script( 'xllist_nav_menu', plugin_dir_url ( $this->file_file ) .'js/nav-menu'.$suffix.'.js', array('jquery'), XILILANGUAGE_VER );

		$data = array ();
		$data['strings'][0] = $this->insertion_point_box_title;
		$data['strings'][1] = esc_js( __('The languages list will be inserted here and :','xili-language') );
		$data['strings'][2] = esc_js( __(' (Hidden input items below will be used for live menu generating.)','xili-language') );

		$data['strings'][3] = $this->insertion_point_box_title_page;
		$data['strings'][4] = esc_js( __('The list of a sub-selection of pages will be inserted here according current language of webpage.','xili-language') );
		$data['strings'][5] = esc_js( __('This is an experimental feature.','xili-language') );

		$data['strings'][6] = $this->insertion_point_box_title_menu;
		$data['strings'][7] = esc_js( __('One menu from this list of menus will be inserted here according current language of displayed webpage.','xili-language') );
		$data['strings'][8] = esc_js( __('This is an experimental powerful feature for dynamic menus.','xili-language') );
		$data['strings'][9] = $this->menu_slug_sep ; // 2.12.2
		foreach ( $this->langs_ids_array as $slug => $id ) {
			$data['strings'][10][$id] = $slug;
		}
		// send all these data to javascript
		wp_localize_script( 'xllist_nav_menu', 'xili_data', $data );

	}

	/**
	 * Update before main list before menus structure html building... filter wp_get_nav_menus
	 *
	 * @since 2.12.2
	 *
	 */
	function _update_menus_insertion_points( $terms, $args) {
		if ( function_exists ('get_current_screen') ) { // thanks to giuseppecabgmail.com - customize broken 2.13.1
			$screen = get_current_screen();
			if ( ! $screen || 'nav-menus' != $screen->base) // 2.13.2 - null if no current screen
				return $terms ;

			$query = new WP_Query;
			$menu_items = $query->query(
				array(
				'meta_key' => '_menu_item_url',
				'meta_value' => '#insertmenu',
				'post_status' => 'publish',
				'post_type' => 'nav_menu_item',
				'posts_per_page' => -1,
				)
			);
			if ( $menu_items ) {
				foreach ($menu_items as $menu_item) {
					$classes = get_post_meta( $menu_item->ID, '_menu_item_classes', true );
					if ( false === strpos( serialize($classes), 'xlmenuslug')) { // update previous menus insertion points
						$to_modify = 0;
						$menu_classes = array();
						foreach ($classes as $class) {
							if ( false !== strpos( $class, 'xlmenulist-')) {
								$to_modify++;
								$menu_id_list = str_replace ( 'xlmenulist-', '', $class );
								$menu_ids = explode ( '-', $menu_id_list );
								$newclass = 'xlmenuslug';
								// search slug
								foreach ( $menu_ids as $menu_id ) {
									if ( term_exists( (int)$menu_id, 'nav_menu' ) ) {
										$nav_menu = get_term ((int)$menu_id, 'nav_menu' );
										$newclass .= $this->menu_slug_sep . $nav_menu->slug;
									}
								}
								//
								$menu_classes[] = $newclass;
							} else {
								$menu_classes[] = $class;
							}
						}
						if ( $to_modify > 0 ) update_post_meta( $menu_item->ID, '_menu_item_classes', $menu_classes );
					}
				}
			}
		}
		return $terms;
	}

	/**
	 * Navigation menu: new ways to insert pages sub-selection via menu builder screen
	 *
	 * @since 2.9.10
	 *
	 */
	function add_sub_select_page_nav_menu_meta_boxes() {
		add_meta_box(
			'insert-xlspage-list',
			sprintf ( __('%s Pages selection', 'xili-language'), '[©xili]'),
			array( $this, 'sub_select_page_nav_menu_link'),
			'nav-menus',
			'side',
			'high'
		);
	}
	// called by above filter
	function sub_select_page_nav_menu_link() {
			// div id in submit below
		global $_nav_menu_placeholder, $nav_menu_selected_id;
		$_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;
			?>
		<div id="posttype-xlsplist" class="posttypediv">
				<div id="tabs-panel-xlsplist" class="tabs-panel tabs-panel-active">
					<ul id ="xllist-checklist" class="categorychecklist form-no-clear">

						<li>
							<label title="" class="menu-item-title">
								<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" value="-1">&nbsp;
							</label>
							<input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="custom">
							<input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="<?php echo $this->insertion_point_box_title_page; ?>">
							<input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" value="<?php echo $this->insertion_point_dummy_link_page; ?>" >
							<input type="text" class="menu-item-attr-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-attr-title]" value="include=">
							<input type="hidden" class="menu-item-classes" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-classes]" value="xlsplist">
						</li>


					</ul>
				</div>
				<p class='description'><?php _e( 'Check and add to menu an insertion point of sub-selection of pages (during displaying menu, a sub-selection will be done according current language. Args is like in function wp_list_pages. Example: <code>include=11,15</code>', 'xili-language' ); ?></p>
				<p class="button-controls">
					<span class="list-controls">

					</span>
					<span class="add-to-menu">
						<input type="submit" <?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-post-type-menu-item" id="submit-posttype-xlsplist">
						<span class="spinner"></span>
					</span>
				</p>
			</div>
		<?php
	}

	/**
	 * Navigation menu: new ways to insert another menu from menu builder and set language selection
	 *
	 * @since 2.9.20
	 *
	 */
	function add_sub_select_nav_menu_meta_boxes() {
		add_meta_box(
			'insert-xlmenus-list',
			sprintf ( __('%s Menus selection', 'xili-language'), '[©xili]'),
			array( $this, 'sub_select_nav_menus'),
			'nav-menus',
			'side',
			'high'
		);
	}

	// called by above filter - now saves slugs
	function sub_select_nav_menus() {
		global $_nav_menu_placeholder, $nav_menu_selected_id;
		$_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;
		?>
		<div id="posttype-xlmenulist" class="posttypediv">
			<div id="tabs-panel-xlmenulist" class="tabs-panel tabs-panel-active tabs-panel-menu">
				<ul id ="xlmenulist-checklist" class="categorychecklist form-no-clear">

					<?php

					// load menus without location
					$locations = get_registered_nav_menus();
					$menu_locations = get_nav_menu_locations();
					$nav_menus = wp_get_nav_menus( array('orderby' => 'name') );
					// reduce to those without location
					$nav_menus_wo = array();
					$already_located = array(); // to avoid multiple items 2.9.22
					foreach ( $locations as $_location => $_name ) {
						if ( isset( $menu_locations[$_location] ) && $menu_locations[$_location] > 0 )
							$already_located[] = $menu_locations[$_location];
					}
					foreach ( $nav_menus as $menu ) {
						if ( $already_located != array() && !in_array ( $menu->term_id, $already_located ) ) {
							$nav_menus_wo[] = $menu;
						}
					}

					if ( $nav_menus_wo ) { // now saves slug - lang index as term_id
						echo '<li>';
						$listlanguages = $this->get_listlanguages();
						foreach ( $listlanguages as $language ) {
							echo '<span class="lang-menu-name" > '. __( $language->description, 'xili-language' ) . '</span>'; ?>&nbsp;
							<select name="menu" id="menu-wlid-<?php echo $language->term_id ?>" class="menu-wo">
								<option value="0" ><?php echo esc_attr( __( 'Select a menu...', 'xili-language' ) ); ?></option>
								<?php foreach( $nav_menus_wo as $_nav_menu ) {  // now save slug?>
										<option value = "<?php echo esc_attr( $_nav_menu->slug ); ?>" >
											<?php echo wp_html_excerpt( $_nav_menu->name, 40, '&hellip;' ); ?>
										</option>
								<?php } ?>
							</select><br />
						<?php } ?>

						</li><li>
							<label title="<?php echo $language->name; ?>" class="menu-item-title menu-item-title-ok">
								<input type="checkbox" class="menu-item-checkbox menu-check-ok" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" value="-1">&nbsp;&nbsp;<?php _e('Check before adding to menu'); ?>
							</label>
							<input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="custom">
							<input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="<?php echo $this->insertion_point_box_title_menu; ?>">
							<input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" value="<?php echo $this->insertion_point_dummy_link_menu; ?>">
							<input type="hidden" class="menu-item-attr-title menu-list-index" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-attr-title]" value="">
							<input type="hidden" class="menu-item-classes menu-list-menu" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-classes]" value="">
						</li>
					<?php } else { ?>
						<li>
							<label class="menu-item-title">
								<?php _e('No menu is available. We must create them without assigning a theme location', 'xili-language'); ?>
							</label>
						</li>
					<?php } ?>

				</ul>
			</div>
			<p class='description'><?php _e( 'Select to assign a language to a menu container without location. Only an insertion point will be placed inside the Menu Structure. The content of the insertion will be automatically made according navigation language rules and contexts. After selection, check and click “Add to Menu” - button below.', 'xili-language' ); ?></p>
			<p class="button-controls">
				<span class="list-controls">

				</span>
				<?php if ( $nav_menus_wo ) { ?>
				<span class="add-to-menu">
					<input type="submit" <?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-post-type-menu-item" id="submit-posttype-xlmenulist">
					<span class="spinner"></span>
				</span>
				<?php } ?>
			</p>
		</div>
			<script type="text/javascript">
	//<![CDATA[
			jQuery(document).ready( function($) {
				$(".lang-menu-name").css ({display:'inline-block', width:'55px' });
				$(".lang-menu-name").css ( "font-size", "12px");
				$(".tabs-panel-menu").css ({background:'#f5f5f5'});
				$(".menu-item-title-ok").css ({ display:'inline-block', margin:'7px 0 0'});
				$(".menu-check-ok").change(function() {
					if ( $(this).attr('checked') ) {
						var langindex = new Array();
						var menuvalue = new Array();
						var message = '<?php echo esc_js ( __( "Assign at least one menu to a language !!!", "xili-language") ) ?>';
						var total = '';
						$('.menu-wo').each(function() {

							langindex.push( $(this).attr('id') );
							menuvalue.push( $(this).find('option:selected').val() );
							total = total + $(this).find('option:selected').val();
						});
						$('.menu-list-index').val( langindex.join('-') ) ;
						$('.menu-list-menu').val( 'xlmenuslug<?php echo $this->menu_slug_sep; ?>' + menuvalue.join('<?php echo $this->menu_slug_sep; ?>') ) ;

						if ( total == '' ) {
							$(this).attr('checked', false );
							alert ( message ) ;
						}
					}
				});
			});
			//]]>
		</script>
		<?php

	}

	/**
	 * Files page - to download automattic mo files
	 *
	 * @since 2.8.8
	 */
	function languages_files() {
		global $wp_version;
		$msg = 0;
		$themessages = array('ok');
		$action = '';
		$emessage = '';

		$upgrade = false ;
		if ( isset( $_POST['downloadmo'] ) ) {
			$action = 'downloadmo' ;
		} else if ( isset( $_POST['mo_merging'] ) ) {
			$action = 'mo_merging' ;
		} else if ( isset( $_POST['checkdownloadmo'] ) ) {
			$action = 'checkdownloadmo' ;
		}

		$themessages = array ( "",
		__('mo files updated','xili-language'),
		__('mo files unreachable','xili-language') ) ;

		add_meta_box('xili-language-files', __('Languages System Files','xili-language'), array(&$this,'on_box_files_content'), $this->thehook5 , 'normal', 'low');


		?>
		<div id="xili-language-files" class="wrap columns-2 minwidth">
			<?php screen_icon('options-general'); ?>
			<h2><?php _e('Languages','xili-language') ?></h2>
			<h3 class="nav-tab-wrapper">
				<?php $this->set_tabs_line() ?>
			</h3>

			<?php if ( $action == 'downloadmo' ) {
				check_admin_referer( 'xili-language-files' );
				$listlanguages = $this->get_listlanguages();
				$a = 0;

				foreach ( $listlanguages as $language ) {

					if ( $language->name != 'en_US' ) {

						if ( isset( $_POST['downloadtheme_'.$language->name] ) ) {

							if ( 'Choose' != $_POST['downloadtheme_'.$language->name] ) {

								$s = explode ( '_', $_POST['downloadtheme_'.$language->name] );

								$theme = $s[1];

							} else {
								$theme = "";
							}

						} else {
							$theme = "";
						}

						if ( isset( $_POST['download_'.$language->name] ) && false !== ( strpos ( $_POST['download_'.$language->name], "Auto" ) ) ) {

							$version = str_replace ( 'Auto_', '' , $_POST['download_'.$language->name] ) ;

						// download_mo_from_automattic( $locale = 'en_US', $upgrade = false, $theme_name = "" )
							$a = $this->download_mo_from_automattic( $language->name, $version, $theme, 1 ) ;

						} else if ( isset( $_POST['download_'.$language->name] ) && false !== ( strpos ( $_POST['download_'.$language->name], "GlotPress" ) ) ) {

							$a = $this->download_mo_from_translate_wordpress( $language->name, $wp_version, $theme , 1 ) ;

						} else if ( isset( $_POST['downloadtheme_'.$language->name] ) && 'Choose' !== $_POST['downloadtheme_'.$language->name] ) {

							if ( $s[0] == 'Auto' ) {

								$automattic_root = 'http://svn.automattic.com/wordpress-i18n/';
								$url_base = $automattic_root."{$language->name}/branches/"; // replace /tags/ 2014-02-01
								$versions_to_check = $this->versions_to_check ( $url_base ); // to recover version

								$version = $this->find_if_version_exists( $language->name, $versions_to_check, $url_base ) ;

								$a = $this->download_mo_from_automattic( $language->name, $version, $theme, 2 ) ;

							} else {

								$a = $this->download_mo_from_translate_wordpress( $language->name, $wp_version, $theme, 2 ) ;
							}
						}
					}
				}
				$msg = ( $a ) ? 1 : 2;
			}

		if ( $action == 'checkdownloadmo' ) {
			check_admin_referer( 'xili-language-files' );
			$upgrade = true ;
		}

		if ( $action == 'mo_merging' ) {
			check_admin_referer( 'xili-language-files' );
			$this->xili_settings['mo_parent_child_merging'] = $_POST['mo_parent_child_merging'] ; // 2.12 - select
			update_option('xili_language_settings', $this->xili_settings);
		}

			?>
			<?php if (0!= $msg ) { ?>
			<div id="message" class="updated fade"><p><?php echo $themessages[$msg]; ?></p></div>
			<?php } ?>
			<form name="files" id="files" method="post" action="options-general.php?page=language_files">
				<?php wp_nonce_field('xili-language-files'); ?>
				<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
				<p class="width23 boldtext">
				<?php printf(__('This tab is added to help aware webmaster to find WP core MO files from %1$s Automattic SVN server %2$s or %3$s GlotPress server%2$s. <br />Be aware that files are not always available for the current WP version (%4$s).<br /> So, the rules for Automattic SVN are set to find the most recent version equal or below the current (exp. 3.5.x or 3.4.x for 3.6). Only check the wished files if language is used in dashboard or default theme (2011, 2012,… ).','xili-language'),'<a href="http://svn.automattic.com/wordpress-i18n/" target="_blank">','</a>', '<a href="http://translate.wordpress.org/projects/wp" target="_blank">', $wp_version );
				echo '<br />'.__('In GlotPress, if nothing found in known versions, the /dev/ subfolder will be explored.','xili-language');
				echo '<br /><strong>'.__('Be aware that choosen files will be downloaded in Core or Theme languages sub-folder. Verify folder rights !','xili-language').'</strong>';

				?></p>
				<?php

				$data = array(
			'action'=>$action, 'emessage'=>$emessage, 'upgrade' => $upgrade
		);

				$this->setting_form_content( $this->thehook5, $data );
				?>
			</form>
		</div>
		<?php $this->setting_form_js( $this->thehook5 );

	}

	function on_box_files_content ( $data ) {
		extract ( $data );
		$how = $this->states_of_mofiles( $upgrade );
		?>
		<div class="submit"><input id='downloadmo' name='downloadmo' type='submit' value="<?php _e('Download mo','xili-language') ?>" /></div>
		<div class="submit"><input id='checkdownloadmo' name='checkdownloadmo' type='submit' value="<?php _e('Check again before downloading mo','xili-language') ?>" /></div>

				<?php if ( $how == 0 )
					echo '<p><strong>'.__('All seems to be in WP languages folder','xili-language').'</strong></p>';
				?>
				<div class="clearb1">&nbsp;</div><br/>
		<?php
	}

	// display states of files locally and at automattic svn server

	function states_of_mofiles ( $show_upgrade = false ) {
		global $wp_version;
		$wp_version_details = explode ('.', $wp_version );

		$wp_version_root = $wp_version_details[0] ;

		if ( count ( $wp_version_details ) > 2 )
			$wp_version_root_2 = $wp_version_details[0] . '.' . $wp_version_details[1] ;

		$available_languages_installed = get_available_languages();
		//$available_languages_installed[] = $this->get_default_locale();
		$available_languages_installed = array_unique( $available_languages_installed );
		$available_languages_installed = array_filter( $available_languages_installed );

		$listlanguages = $this->get_listlanguages();

		$automattic_root = 'http://svn.automattic.com/wordpress-i18n/';

		$i = 0 ;
		if ( is_child_theme() ) {
			$theme_name = get_option("stylesheet");
			$parent_theme_name = get_option("template");

		} else {
			$theme_name = get_option("template");
			$parent_theme_name = get_option("template");

		}
		foreach ( $listlanguages as $language ) {

			echo '<div class="langbox" style="overflow:hidden;">';
			echo '<h4>';
			echo $language->description . ' ('.$language->name.') ';
			echo '</h4>';

			$installed = in_array( $language->name, $available_languages_installed );

			if ( $installed ) {
				echo '<p>'.__('Installed in WP languages folder','xili-language').'</p>';
			}

			$show = ( ( $installed && $show_upgrade ) || ( !$installed ) ) ? true : false ;

			if ( $show ) {

				// GlotPress
				$glot = false;
				if ( $language->name != 'en_US' ) {

					if ( $ver = $this->check_versions_in_glotpress ( $language->name, $wp_version ) ) {
						if ( $ver == 'dev' ) { //2.8.8k
							echo '<p><em>'.__('Development Version available on GlotPress WordPress.org server','xili-language').'</em></p>';
						} else {
							echo '<p><em>'. sprintf( __('Version %s ready to be downloaded on GlotPress WordPress.org server','xili-language'), $wp_version ).'</em></p>';
						}
						$glot = true;
					} else {
						$glot = false;
						echo '<p>'.__('Not available from GlotPress WordPress.org server','xili-language').'</p>';
					}
				}
				// Automattic
				$url_base = $automattic_root."{$language->name}/branches/"; // replaces /tags/ 2014-02-01
				$versions_to_check = $this->versions_to_check ( $url_base );
				//$version/messages/{$language->name}.mo

				if ( $language->name == 'en_US' ) {
					echo '<p>'.__('Root language of WordPress','xili-language').'</p>';
					$auto = false;
				} else if ( $version = $this->find_if_version_exists( $language->name, $versions_to_check, $url_base ) ) {
					echo '<p><em>'. sprintf( __('Version %s ready to be downloaded from Automattic SVN server','xili-language'), $version ).'</em></p>';

					$auto = true;
					$i++;
				} else {
					$auto = false;
					echo '<p>'.__('Not available from Automattic SVN server','xili-language').'</p>';
				}

				if ( $glot || $auto ) {

					echo __('Server to download','xili-language');
					echo ' : <select id="download_'.$language->name.'" name="download_'.$language->name.'" >';
					echo '<option value="Choose" >' . __('Choose server...','xili-language') . '</option>';
					if ( $auto )
						echo '<option value="Auto_'.$version.'">' . __( 'Try from Automattic', 'xili-language' ) . '</option>' ;
					if ( $glot )
						echo '<option value="GlotPress_'.$version.'">' . __( 'Try from GlotPress', 'xili-language' ) . '</option>' ;

					echo '</select>';
				}

			}
				if ( $language->name != 'en_US' && in_array ( $parent_theme_name, $this->embedded_themes ) ) {
					echo '<fieldset class="themebox"><legend>';
					echo sprintf( __('Theme\'s files %s','xili-language'), ( ( is_child_theme() ) ? $theme_name .' ('.$parent_theme_name.') ': $theme_name ) );
					echo '</legend>';

					$mofile = get_template_directory(). '/languages/' . $language->name.'.mo';
					if ( file_exists ( $mofile ) ) {
						if ( is_child_theme() ) {

							echo '<p>'. sprintf( __('Installed in parent theme\'s (%s) languages folder','xili-language'), $parent_theme_name ).'</p>';
						} else {
							echo '<p>'.__('Installed in theme\'s languages folder','xili-language').'</p>';
						}
					} else {
						if ( is_child_theme() ) {
							echo '<p>'. sprintf( __('Not installed in parent theme\'s (%s) languages folder','xili-language'), $parent_theme_name ).'</p>';
						} else {
							echo '<p>'.__('Not installed in theme\'s languages folder','xili-language').'</p>';
						}

					echo __('Server to download theme file','xili-language');
					echo ' : <select id="downloadtheme_'.$language->name.'" name="downloadtheme_'.$language->name.'" >';
					echo '<option value="Choose" >' . __('Choose server...','xili-language') . '</option>';
					echo '<option value="Auto_'.$parent_theme_name.'" >' . __( 'Try from Automattic', 'xili-language' ) . '</option>' ;
					echo '<option value="GlotPress_'.$parent_theme_name.'" >' . __( 'Try from GlotPress', 'xili-language' ) . '</option>' ;
					echo '</select>';

						$i++;
					}
				echo '</fieldset>';

				}
			echo '</div>';




		}
		return $i;
	}

	function find_if_version_exists ( $language, $versions_to_check, $url_base ) {
		if ( $versions_to_check != array () ) {
			foreach ( $versions_to_check as $version ) {
				$url = $url_base . "$version/messages/$language.mo";

				if ( $this->url_exists( $url ) ) return $version;
			}
		} else {
			return false;
		}
	}

	function versions_to_check ( $url_base, $upgrade = false ) {
		// define versions to check
		global $wp_version;

		$wp_version_details = explode ('.', $wp_version );

		if ( $this->url_exists( $url_base ) ) {
			// get all the versions available in the subdirectory
			$resp = wp_remote_get($url_base);
			if (is_wp_error($resp) || 200 != $resp['response']['code'])
				return false;

			preg_match_all('#>([0-9\.]+)\/#', $resp['body'], $matches);
			if (empty($matches[1]))
				return false;

			rsort($matches[1]); // sort from newest to oldest

			$versions = $matches[1];

			foreach ($versions as $key => $version) {

				$version_details = explode ('.', $version );

				if ( $version_details[0] != $wp_version_details[0] ) {
					unset($versions[$key]);
				// will not try to download a too recent mofile
				} else if ( version_compare( $version, $wp_version, '>') ) {
					unset($versions[$key]);
				// no big diff
				} else if ( abs ((int) $version_details[1] - (int)( $wp_version_details[1])) > 2 ) { // 3.6 and 3.5.x
					unset($versions[$key]);
				// will not download an older version if we are upgrading
				} else if ($upgrade && version_compare($version, $wp_version, '<=')) {
					unset($versions[$key]);
				}
			}
			return $versions;
		} else {
			return false;
		}
	}

	function check_versions_in_glotpress ( $locale, $version = 'dev' ) {

		$version_folder = $this->glotPress_version_folder ( $version ) ;
		if ( '' == $version_folder ) $version_folder = 'dev';
		// Get the list of available translation from Translate WordPress. This is expected to be JSON.
		$translations = wp_remote_get( sprintf( 'http://translate.wordpress.org/api/projects/wp/%1$s', $version_folder ) ); // 2.8.8k

		if ( is_wp_error( $translations ) || wp_remote_retrieve_response_code( $translations ) !== 200 ) {
			// again with forcing 'dev'
			$translations = wp_remote_get( sprintf( 'http://translate.wordpress.org/api/projects/wp/%1$s', 'dev' ) ); // rules changed in glot
			if ( is_wp_error( $translations ) || wp_remote_retrieve_response_code( $translations ) !== 200 ) {
				return false ;
			}
		}

		$translations = json_decode( wp_remote_retrieve_body( $translations ) );
		if ( is_null( $translations ) )
			return false ;

		$filtered = wp_list_filter( $translations->translation_sets, array( 'locale' => substr( $locale, 0, 2 ) )) ; // 2.9.10 (no more wp_locale)
		// See if the requested $locale has an available translation
		$translations = array_shift( $filtered ); // param variable

		if ( empty( $translations ) )
			return false ;

		return $translations->locale ;
	}

	function set_author_rules_register_setting () {
		$name = ( is_child_theme()) ? get_option("stylesheet") : get_option("template") ;
		$this->settings_author_rules = 'xiliml_' . $name . '_author_rules'; //'xiliml_author_rules'
		register_setting( $this->settings_author_rules . '_group', $this->settings_author_rules, array( $this,'author_rules_validate_settings' ) );
		register_setting( $this->settings_authoring_settings . '_group', $this->settings_authoring_settings, array( $this,'settings_authoring_settings_validate' ) );
	}


	/**
	 * Authoring rules page
	 *
	 * @since 2.12.0
	 */
	function author_rules() {

		$themessages = array('ok');
		$emessage = "";
		$action = '';

		$data = array(
			'action'=>$action, 'emessage'=>$emessage
		);
		add_meta_box('xili-language-authoring-settings', __('Authoring and data settings','xili-language'), array(&$this,'on_box_authoring_settings'), $this->thehook6 , 'normal', 'low');
		add_meta_box('xili-language-author-rules', __('Authoring rules','xili-language'), array(&$this,'on_box_author_rules'), $this->thehook6 , 'normal', 'low');

		add_settings_section( 'option_section_1', __('Propagation options', 'xili-language'), array( $this, 'display_one_section'), $this->settings_author_rules .'_group');

		foreach ( $this->propagate_options_labels as $one_key => $one_option ) {

			if ( $this->propagate_options_default[$one_key]['hidden']) {

				$field_args = array(
					'option_name'	=> $this->settings_author_rules,
					'title'			=> $one_option['name'],
					'type'			=> 'hidden',
					'id'			=> $one_key,
					'name'			=> $one_key,
					'desc'			=> $one_option['description'],
					'std'			=> '1',

					'class'			=> 'css_class propagate'
				);

			} else {
				$field_args = array(
					'option_name'	=> $this->settings_author_rules,
					'title'			=> $one_option['name'],
					'type'			=> 'checkbox',
					'id'			=> $one_key,
					'name'			=> $one_key,
					'desc'			=> $one_option['description'],
					'std'			=> '1',
					'label_for'		=> $one_key,
					'class'			=> 'css_class propagate'
				);
			}

			add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'display_one_setting'), $this->settings_author_rules .'_group', 'option_section_1', $field_args );


		}
		// Authoring settings (grouped from previous other tabs 2 and 3)
		add_settings_section( 'option_section_settings_1', __('Authoring settings', 'xili-language'), array( $this, 'display_one_section'), $this->settings_authoring_settings .'_group');

		$authoring_language_options = array (
			'no' => __('No default language','xili-language'),
			'authorbrowser' => __('Browser language','xili-language'),
			'authordashboard' => __('Dashboard language','xili-language')
		);

		// authorbrowseroption
		$field_args = array(
			'option_name'	=> $this->settings_authoring_settings,
			'title'			=> __('Default language of a new created post', 'xili-language'),
			'type'			=> 'select',
			'id'			=> 'authorbrowseroption',
			'name'			=> 'authorbrowseroption',
			'desc'			=> __('When creating a very new post, choose the default language assigned to this post.', 'xili-language'),
			'std'			=> 'no',
			'label_for'		=> 'authorbrowseroption',
			'class'			=> 'css_class settings',
			'option_values' => $authoring_language_options
		);

		add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'display_one_setting'), $this->settings_authoring_settings .'_group', 'option_section_settings_1', $field_args );

		// creation_redirect
		$field_args = array(
			'option_name'	=> $this->settings_authoring_settings,
			'title'			=> __('Redirect to created post', 'xili-language'),
			'type'			=> 'checkbox',
			'id'			=> 'creation_redirect',
			'name'			=> 'creation_redirect',
			'desc'			=> __("After creating a linked post in other language, the Edit post is automatically displayed.", "xili-language"),
			'std'			=> 'redirect',
			'label_for'		=> 'creation_redirect',
			'class'			=> 'css_class settings'
		);
		add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'display_one_setting'), $this->settings_authoring_settings .'_group', 'option_section_settings_1', $field_args );

		// external_xl_style
		if ( ! $this->exists_style_ext ) {
			$style_state = __( 'There is no style for dashboard','xili-language' ) .' ('.$this->style_message . ' ) ';
		} else {
			$style_state = $this->style_message ;
		}
		$field_args = array(
			'option_name'	=> $this->settings_authoring_settings,
			'title'			=> __('Activate xl-style.css','xili-language'),
			'type'			=> 'checkbox',
			'id'			=> 'external_xl_style',
			'name'			=> 'external_xl_style',
			'desc'			=> sprintf( __('Dashboard style: %s', 'xili-language'), $style_state ),
			'std'			=> 'on',
			'label_for'		=> 'external_xl_style',
			'class'			=> 'css_class settings'
		);
		add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'display_one_setting'), $this->settings_authoring_settings .'_group', 'option_section_settings_1', $field_args );

		// custom post type
		$types = get_post_types( array('show_ui'=>1) );
		if ( count( $types ) > 2 ) {
			$thecheck = array() ;
			$thecustoms = $this->get_custom_desc() ;
			if ( count($thecustoms) > 0 ) {
				foreach ( $thecustoms as $type => $thecustom) {
					$thecheck[] = $type ;
				}
				$clabel = implode(', ', $thecheck);

				add_settings_section( 'option_section_settings_2', __('Custom post authoring multilingual rules', 'xili-language'), array( $this, 'display_one_section'), $this->settings_authoring_settings .'_group');

				$customs_options = $this->xili_settings['multilingual_custom_post'];
				foreach ( $thecustoms as $type => $thecustom) {
					//$customs_enable = ( isset($customs_options[$type]) ) ? $customs_options[$type]['multilingual'] : '';
					$field_args = array(
						'option_name'	=> $this->settings_authoring_settings,
						'title'			=> $thecustom['name'],
						'type'			=> 'checkbox',
						'id'			=> 'cpt_' . $type,
						'name'			=> 'cpt_' . $type,
						'desc'			=> sprintf( __('Custom post type named: %s', 'xili-language'), $thecustom ['name'] ),
						'std'			=> 'enable',
						'label_for'		=> 'cpt_' . $type,
						'class'			=> 'css_class settings'
					);
					add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'display_one_setting'), $this->settings_authoring_settings .'_group', 'option_section_settings_2', $field_args );

				}
			}
		}

		// bookmarks sub selection in links widget

		add_settings_section( 'option_section_settings_3', __('Sub-selection in bookmarks widget', 'xili-language'), array( $this, 'display_one_section'), $this->settings_authoring_settings .'_group');

		// xili_language_link_cat_all
		$field_args = array(
			'option_name'	=> $this->settings_authoring_settings,
			'title'			=> __('All Links', 'xili-language'),
			'type'			=> 'checkbox',
			'id'			=> 'link_cat_all',
			'name'			=> 'link_cat_all',
			'desc'			=> __('If checked, all bookmarks will be subselected according current language.', 'xili-language') ,
			'std'			=> '1',
			'label_for'		=> 'link_cat_all',
			'class'			=> 'css_class settings'
		);
		add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'display_one_setting'), $this->settings_authoring_settings .'_group', 'option_section_settings_3', $field_args );

		$link_cats = get_terms( 'link_category');

		if ( $link_cats ) {
			foreach ( $link_cats as $link_cat ) {

				$field_args = array(
					'option_name'	=> $this->settings_authoring_settings,
					'title'			=> $link_cat->name,
					'type'			=> 'checkbox',
					'id'			=> 'link_cat_'.$link_cat->term_id,
					'name'			=> 'link_cat_'.$link_cat->term_id,
					'desc'			=> sprintf( __('If checked, %s bookmark will be subselected according current language.', 'xili-language'), $link_cat->name ) ,
					'std'			=> '1',
					'label_for'		=> 'link_cat_'.$link_cat->term_id,
					'class'			=> 'css_class settings'
				);
				add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'display_one_setting'), $this->settings_authoring_settings .'_group', 'option_section_settings_3', $field_args );

			}
		}

		//
		add_settings_section( 'option_section_settings_5', __('Option to define widget visibility', 'xili-language'), array( $this, 'display_one_section'), $this->settings_authoring_settings .'_group');
		if ( current_theme_supports( 'widgets' ) ) {
			$field_args = array(
				'option_name'	=> $this->settings_authoring_settings,
				'title'			=> __('Option Enabled', 'xili-language'),
				'type'			=> 'checkbox',
				'id'			=> 'widget_visibility',
				'name'			=> 'widget_visibility',
				'desc'			=> __('If checked, each widget settings form includes rules to define visibility in front-end.', 'xili-language') ,
				'std'			=> '1',
				'label_for'		=> 'widget_visibility',
				'class'			=> 'css_class settings'
			);
			add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'display_one_setting'), $this->settings_authoring_settings .'_group', 'option_section_settings_5', $field_args );
		}
		//
		add_settings_section( 'option_section_settings_4', __('Other settings (Widgets)', 'xili-language'), array( $this, 'display_one_section'), $this->settings_authoring_settings .'_group');
		if ( current_theme_supports( 'widgets' ) ) {
			$field_args = array(
				'option_name'	=> $this->settings_authoring_settings,
				'title'			=> __('Enable widgets', 'xili-language'),
				'type'			=> 'checkbox',
				'id'			=> 'widget',
				'name'			=> 'widget',
				'desc'			=> __('If checked, selected xili-language widgets below will be available.', 'xili-language') ,
				'std'			=> 'enable',
				'label_for'		=> 'widget',
				'class'			=> 'css_class settings'
			);

			add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'display_one_setting'), $this->settings_authoring_settings .'_group', 'option_section_settings_4', $field_args );
			$widgets = array_keys( $GLOBALS['wp_widget_factory']->widgets ); // 2.17.1
			foreach ( $this->xili_settings['specific_widget'] as $key => $value ) {

				$setbytheme = ( $value['value'] == '' && in_array( $key, $widgets )) ? "<small>(*)</small>" : "";

				$field_args = array(
				'option_name'	=> $this->settings_authoring_settings,
				'title'			=> sprintf( __('Widget: %s', 'xili-language'), translate ($value['name'], 'xili-language') ),
				'type'			=> 'checkbox',
				'id'			=> 'specific_widget_'. $key,
				'name'			=> 'specific_widget_'. $key,
				'desc'			=> sprintf( __('Checked, this widget of class %1$s is available. %2$s', 'xili-language'), $key, $setbytheme ),
				'std'			=> 'enabled',
				'label_for'		=> 'specific_widget_'. $key,
				'class'			=> 'css_class settings widget'
				);
				add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'display_one_setting'), $this->settings_authoring_settings .'_group', 'option_section_settings_4', $field_args );
			}
		}

		if ( file_exists( WP_PLUGIN_DIR . $this->xilidev_folder ) ) {
			$field_args = array(
				'option_name'	=> $this->settings_authoring_settings,
				'title'			=> __('Enable gold functions', 'xili-language'),
				'type'			=> 'checkbox',
				'id'			=> 'functions_enable',
				'name'			=> 'functions_enable',
				'desc'			=> __('If checked, available gold functions are activated.', 'xili-language') ,
				'std'			=> 'enable',
				'label_for'		=> 'functions_enable',
				'class'			=> 'css_class settings'
			);
			add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'display_one_setting'), $this->settings_authoring_settings .'_group', 'option_section_settings_4', $field_args );
		}


		?>
		<div id="xili-language-author-rules" class="wrap columns-2 minwidth">
			<?php screen_icon('options-general'); ?>
			<h2><?php _e('Authoring rules','xili-language') ?></h2>
			<h3 class="nav-tab-wrapper">
			<?php $this->set_tabs_line() ?>
			</h3>

			<p class="width23 boldtext">
			<?php printf(__("This settings screen contains new miscellaneous features.",'xili-language'),'<a href="'. $this->repositorylink .'" target="_blank">','</a>' ); ?>
			</p>

			<?php $this->setting_form_content( $this->thehook6, $data ); ?>
		</div>
		<?php
		$this->setting_form_js( $this->thehook6 );
	}


	function on_box_authoring_settings() {
		?>
		<div class="list-settings authoring_settings">
		<form name="authoring_settings" id="authoring_settings" method="post" enctype="multipart/form-data" action="options.php">
			<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
			wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );
			settings_fields( $this->settings_authoring_settings . '_group' ); // nonce, action (plugin.php)
			do_settings_sections( $this->settings_authoring_settings . '_group' );
			?>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php printf(__('%1$s of %2$s', 'xili-language') ,__('Save Changes'), __('Authoring and data settings', 'xili-language')); ?>" />
			</p>
			<div class="clearb1">&nbsp;</div>
		</form>
		</div>
		<?php
	}

	function on_box_author_rules() {
		?>
		<div class="list-settings authoring-rules">
		<form name="author_rules" id="author_rules" method="post" enctype="multipart/form-data" action="options.php">
			<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
			wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );
			settings_fields( $this->settings_author_rules . '_group' ); // nonce, action (plugin.php)
			do_settings_sections( $this->settings_author_rules . '_group' );
			?>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php printf(__('%1$s of %2$s', 'xili-language') ,__('Save Changes'), __('Authoring rules', 'xili-language')); ?>" />
			</p>
			<div class="clearb1">&nbsp;</div>
		</form>
		</div>
		<?php

	}

	function get_authoring_settings_options() { // not used yet
		//return get_option( $this->settings_authoring_settings, $this->get_default_authoring_settings_options() );
		$default_array = $this->get_default_authoring_settings_options();
		$values_array = array (
			'authorbrowseroption' => $this->xili_settings['authorbrowseroption'],
			'creation_redirect' => $this->xili_settings['creation_redirect'],
			'external_xl_style' => $this->xili_settings['external_xl_style'],
			'widget' => $this->xili_settings['widget'],
			'functions_enable' => $this->xili_settings['functions_enable']
			);
		return ( array_merge ( $default_array, $values_array ) );
	}

	function get_default_authoring_settings_options() {
		return array(
			'authorbrowseroption' => 'no',
			'creation_redirect' => 'redirect',
			'external_xl_style' => 'on',
			'widget' => 'enable',
			'functions_enable' => ''
			);
	}

	function get_theme_author_rules_options() {
		return get_option( $this->settings_author_rules, $this->get_default_theme_author_rules_options() );
	}

	function get_default_theme_author_rules_options() {
		$propagate_option_default = array();

		if ( $this->propagate_options_default != array() ) {
			foreach ( $this->propagate_options_default as $key => $one_options ) {
				$propagate_option_default[$key] = $one_options['default'];
			}
		}
		//$arr = array_merge ( array( 'authoring_options_admin' => '' ), $propagate_option_default );
		return $propagate_option_default;
	}

	function settings_authoring_settings_validate ( $input ){

		if ( isset( $input['authorbrowseroption'] ) ) $this->xili_settings['authorbrowseroption'] = $input['authorbrowseroption'] ;
		$this->xili_settings['creation_redirect'] = ( isset( $input['creation_redirect'] ) ) ? $input['creation_redirect'] : "" ; // because checkbox
		$this->xili_settings['external_xl_style'] = ( isset( $input['external_xl_style'] ) ) ? $input['external_xl_style'] : "off" ; // because checkbox
		$this->xili_settings['widget'] = ( isset( $input['widget'] ) ) ? $input['widget'] : "" ; // because checkbox
		$this->xili_settings['widget_visibility'] = ( isset( $input['widget_visibility'] ) ) ? $input['widget_visibility'] : "" ; // because checkbox
		$specifics = array();
		foreach ( $this->xili_settings['specific_widget'] as $key => $value ) {
			$specifics[$key]['name'] = $value['name'];
			$specifics[$key]['value'] = ( isset( $input['specific_widget_'.$key] ) ) ? $input['specific_widget_'.$key] : "" ;
		}
		$this->xili_settings['specific_widget'] = $specifics; // 2.16.4
		$this->xili_settings['functions_enable'] = ( isset( $input['functions_enable'] ) ) ? $input['functions_enable'] : "" ; // because checkbox
		$thecustoms = $this->get_custom_desc() ;
		if ( count($thecustoms) > 0 ) {
			foreach ( array_keys ( $thecustoms ) as $cpt ) {
				if ( isset( $input['cpt_'.$cpt] ) ) { // because checkbox
					$this->xili_settings['multilingual_custom_post'][$cpt] = $thecustoms[$cpt]; // fixed 2.13.2 for new CPT
					$this->xili_settings['multilingual_custom_post'][$cpt]['multilingual'] = $input['cpt_'.$cpt] ;
				} else {
					$this->xili_settings['multilingual_custom_post'][$cpt]['multilingual'] = "";
				}
			}
		} else {
			$this->xili_settings['multilingual_custom_post'] = array() ;
		}

		$this->xili_settings['link_categories_settings']['all'] = ( isset( $input['link_cat_all'] ) ) ? $input['link_cat_all'] : "" ; // because checkbox
		$link_cats = get_terms( 'link_category');

		if ( $link_cats ) {
			foreach ( $link_cats as $link_cat ) {
				$this->xili_settings['link_categories_settings']['category'][$link_cat->term_id] = ( isset( $input['link_cat_'.$link_cat->term_id] ) ) ? $input['link_cat_'.$link_cat->term_id] : "" ; // because checkbox
			}
		}
		update_option('xili_language_settings', $this->xili_settings );	// based on original settings
		return $input; // redundant if no filter
	}

	function author_rules_validate_settings( $input ) {
		foreach($input as $id => $v) {
			$newinput[$id] = trim($v);
		}
		if ( !isset ( $input['authoring_options_admin'] ) ) $newinput['authoring_options_admin'] = '';
		$keys = array_keys ( $this->propagate_options_default );
			foreach ( $keys as $key) { if ( !isset ( $input[$key] ) ) $newinput[$key] = ''; }

		return $newinput;
	}

	function display_one_section( $section ){
		switch ( $section['id'] ) {
			case 'option_section_1':
				echo '<p class="section">'. __('When authors of post, page and custom post want to create a translation, it is possible to define what feature of original post can be copied to the post of target language (format, parent, comment or ping status,...). Some features are not ajustable (to be, it will be need premium services). For developer only: filters are available.', 'xili-language') .'</p>';
				break;

			case 'option_section_settings_1':
				echo '<p class="section">'. __("This settings screen contains new miscellaneous features to define or help authoring.",'xili-language').'</p>';
				break;

			case 'option_section_settings_2':
				$thecustoms = $this->get_custom_desc() ;
				$thecheck = array();
				foreach ( $thecustoms as $type => $thecustom) {
					$thecheck[] = $type ;
				}
				$clabel = implode(', ', $thecheck);
				$text = ( count($thecustoms) == 1 ) ? sprintf(__('One custom post (%s) is available.','xili-language'), $clabel ) : sprintf(__('More than one custom post (%s) are available.','xili-language'), $clabel );
				echo '<p class="section">'. $text .'</p>';
				echo '<p class="section">'. __('Check to define as multilingual (a translation box will appear in edit page).', 'xili-language') .'</p>';
				break;

			case 'option_section_settings_3':
				echo '<p class="section">'. __("Check the bookmark's categories where to enable multilanguage features.", "xili-language") .'</p>';
				break;
			case 'option_section_settings_4':
				echo '<p class="section">'. __("Define here the widget(s) visible in Appearance. If visibility is set inside theme source code, a * is visible.", "xili-language") .'</p>';
				break;
			case 'option_section_settings_5': // 2.20.3
				echo '<p class="section">'. __("Set here if an option to set <em>visibility rules according language</em> will be inserted in each widget form of Appearance/Widgets settings page (and Customize page).", "xili-language");
				if ( class_exists ('jetpack') ) {
					$modules_array = get_option ( 'jetpack_active_modules', true );
					if ( $modules_array && in_array('widget-visibility', $modules_array ) ) echo '<br />' . __("The module - Widget visibility - of Jetpack is active. Language rules can overwrite Jetpack visibility rules!", "xili-language");
				}
				echo  '</p>';
				break;

			case 'option_front_section_1':
				echo '<p class="section">' . __('Here select language of the home webpage', 'xili-language') .'</p>';
				echo '<p class="section"><em>'.sprintf(__('As set in <a href="%1$s">%2$s</a>, the home webpage is', 'xili-language'), 'options-reading.php', __('Reading')).'&nbsp;';
				if ( $this->show_page_on_front ) {
					printf(__('a static <a href="%1$s">page</a>.', 'xili-language'), "edit.php?post_type=page") ;
					$page_for_posts = get_option('page_for_posts');
					if ( !empty ( $page_for_posts ) ) {
						echo '&nbsp;'. __('Another page is set to display the latest posts (in default theme).', 'xili-language');
					}
				} else {
					_e('set as to display the latest posts (in default theme).', 'xili-language');
				}
				echo '</em></p>';
				break;

			case 'option_front_section_2':
				echo '<p class="section">'. __("Here select language of the theme items when a category is displayed without language sub-selection", "xili-language") .'</p>';
				break;

			case 'xili_flag_section_1':
				echo '<p class="section">'. __("Here define if flags style in language selector (switcher) navigation menu", "xili-language") .'</p>';
				break;

			case 'xili_flag_section_2':
				echo '<p class="section">'. __("Settings flags style for language selector (switcher) menu", "xili-language") .'</p>';
				break;

		}
	}

	/**
     * one line in section
     *
     * @updated 2.12.2 (notices)
     */
	function display_one_setting( $args ){
		extract( $args );
		switch ( $option_name ) {
			case $this->settings_authoring_settings:
				//$options = $this->get_authoring_settings_options();
				$options['authorbrowseroption'] = $this->xili_settings['authorbrowseroption'];
				$options['creation_redirect'] = $this->xili_settings['creation_redirect'];
				$options['external_xl_style'] = $this->xili_settings['external_xl_style'];
				$options['widget'] = $this->xili_settings['widget'];
				$options['widget_visibility'] = $this->xili_settings['widget_visibility'];
				$options['functions_enable'] = $this->xili_settings['functions_enable'];
				foreach ( $this->xili_settings['specific_widget'] as $key => $value ) { // 2.16.4
					$options['specific_widget_'.$key] = $value['value'];
				}
				// CPT
				if ( false !== strpos ( $id, 'cpt_') ) {
					$cpt = str_replace ( 'cpt_' , '', $id );
					$options[$id] = ( isset($this->xili_settings['multilingual_custom_post'][$cpt]['multilingual']) ) ? $this->xili_settings['multilingual_custom_post'][$cpt]['multilingual'] : '';
				}
				// Bookmarks
				if ( false !== strpos ( $id, 'link_cat_' ) ) {
					$link_cat_id = str_replace ( 'link_cat_' , '', $id );
					if ( $link_cat_id == 'all' ) {
						$options[$id] = ( isset($this->xili_settings['link_categories_settings']['all']) ) ? $this->xili_settings['link_categories_settings']['all'] : '';
					} else {
						$options[$id] = ( isset($this->xili_settings['link_categories_settings']['category'][$link_cat_id]) ) ? $this->xili_settings['link_categories_settings']['category'][$link_cat_id] : '';
					}
				}

				break;

			case $this->settings_frontend :
				$options['browseroption'] = $this->xili_settings['browseroption'];
				$options['lang_neither_browser'] = $this->xili_settings['lang_neither_browser'];
				$options['homelang'] = $this->xili_settings['homelang'];
				$options['home_item_nav_menu'] = $this->xili_settings['home_item_nav_menu'];
				$options['pforp_select'] = $this->xili_settings['pforp_select'];
				$options['allcategories_lang'] = $this->xili_settings['allcategories_lang'];

				break;

			case $this->settings_author_rules: // multiple names (based on current theme)
				$options = $this->get_theme_author_rules_options();
				break; // fixes

			case $this->flag_settings_name :
				$options = $this->get_xili_flag_options();
				break;
		}

		switch ( $type ) {

			case 'message';
				echo ($desc != '') ? "<span class='description'>$desc</span>" : "...";
				break;

			case 'text':
				$set = ( isset ( $options[$id] ) ) ? $options[$id] : $std ;
				$set = stripslashes($set);
				$set = esc_attr( $set);
				$size_attr = (isset ($size)) ? "size='$size'" : '' ;
				echo "<input $size_attr class='regular-text$class' type='text' id='$id' name='" . $option_name . "[$id]' value='$set' />";
				echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";
				break;

			case 'hidden':
				$set = ( isset ( $options[$id] ) ) ? $options[$id] : (( isset ( $this->propagate_options_default[$id]['default'] ) ) ? $this->propagate_options_default[$id]['default'] : false );

				$val = ( $set ) ? '1' : '';
				echo "<input type='hidden' id='$id' name='" . $option_name . "[$id]' value='$val' />";

				echo ( $desc != '' && $set ) ? "<span class='description'>$desc</span>" : "<span class='description'>".__('No propagation', 'xili-language')."</span>";
				break;


			case 'checkbox':
				// take default if not previous saved
				switch ( $option_name ) {
					case $this->settings_author_rules: // multiple names (based on current theme)
						$set = ( isset ( $options[$id] ) ) ? $options[$id] : (( isset ( $this->propagate_options_default[$id]['default'] ) ) ? $this->propagate_options_default[$id]['default'] : false );
						break;
					default:
						$set = ( isset ( $options[$id] ) ) ? $options[$id] : false;
						break;
				}

				$checked = checked ( $set, $std, false );
				echo "<input $checked class='$class' type='checkbox' id='$id' name='" . $option_name . "[$id]' value='$std' />";
				echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";
				break;

			case 'select':
				$set = ( isset ( $options[$id] ) ) ? $options[$id] : false;

				echo "<select id='$id' name='" . $option_name . "[$id]' />";

				foreach ( $option_values as $value => $content ) {
					echo "<option value='$value' " . selected ( $set , $value , false) . ">$content</option>";
				}
				echo "</select>";
				echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";
				break;
		}
	}

	/**
	 * Support page
	 *
	 * @since 2.4.1
	 */
	function languages_support() {
		global $wp_version ;
		$msg = 0;
		$themessages = array('ok');
		$emessage = "";
		$action = '';
		if ( isset( $_POST['sendmail'] ) ) {
			$action = 'sendmail' ;
		}
		switch( $action ) {

			case 'sendmail'; // 1.8.5
				check_admin_referer( 'xili-plugin-sendmail' );

				$this->xili_settings['url'] = ( isset( $_POST['urlenable'] ) ) ? $_POST['urlenable'] : '' ;
				$this->xili_settings['theme'] = ( isset( $_POST['themeenable'] ) ) ? $_POST['themeenable'] : '' ;
				$this->xili_settings['wplang'] = ( isset( $_POST['wplangenable'] ) ) ? $_POST['wplangenable'] : '' ;
				$this->xili_settings['version-wp'] = ( isset( $_POST['versionenable'] ) ) ? $_POST['versionenable'] : '' ;
				$this->xili_settings['permalink_structure'] = ( isset( $_POST['permalink_structure'] ) ) ? $_POST['permalink_structure'] : '' ;
				$this->xili_settings['xiliplug'] = ( isset( $_POST['xiliplugenable'] ) ) ? $_POST['xiliplugenable'] : '' ;
				$this->xili_settings['webmestre-level'] = $_POST['webmestre']; // 2.8.4
				update_option('xili_language_settings', $this->xili_settings);
				$contextual_arr = array();
				if ( $this->xili_settings['url'] == 'enable' ) $contextual_arr[] = "url=[ ".get_bloginfo ('url')." ]" ;
				if ( isset($_POST['onlocalhost']) ) $contextual_arr[] = "url=local" ;
				if ( $this->xili_settings['theme'] == 'enable' ) $contextual_arr[] = "theme=[ ".get_option ('stylesheet')." ]" ;
				if ( $this->xili_settings['wplang'] == 'enable' ) $contextual_arr[] = "WPLANG=[ ". $this->get_WPLANG()." ]" ;
				if ( isset( $_POST['xililanguageslist'] ) ) $contextual_arr[] = "Languages List=[ ". implode(',', $this->langs_slug_name_array )." ]" ;
				if ( $this->xili_settings['version-wp'] == 'enable' ) $contextual_arr[] = "WP version=[ ".$wp_version." ]" ;
				if ( $this->xili_settings['permalink_structure'] == 'enable' ) {
					$contextual_arr[] = "Permalinks=[ ".get_option('permalink_structure')." ]" ;
					if ( isset ( $XL_Permalinks_rules ) ) $contextual_arr[] = "XL lang perma" ;
				}
				if ( $this->xili_settings['xiliplug'] == 'enable' ) $contextual_arr[] = "xiliplugins=[ ". $this->check_other_xili_plugins() ." ]" ;

				$contextual_arr[] = $this->xili_settings['webmestre-level'];	// 1.9.1

				$headers = 'From: xili-language plugin page <' . get_bloginfo ('admin_email').'>' . "\r\n" ;
				if ( '' != $_POST['ccmail'] ) {
					$headers .= 'Cc: <'.$_POST['ccmail'].'>' . "\r\n";
					$headers .= 'Reply-To: <'.$_POST['ccmail'].'>' . "\r\n";
				}
				$headers .= "\\";
				$message = "Message sent by: ".get_bloginfo ('admin_email')."\n\n" ;
				$message .= "Subject: ".$_POST['subject']."\n\n" ;
				$message .= "Topic: ".$_POST['thema']."\n\n" ;
				$message .= "Content: ".$_POST['mailcontent']."\n\n" ;
				$message .= "Checked contextual infos: ". implode ( ', ', $contextual_arr ) ."\n\n" ;
				$message .= "This message was sent by webmaster in xili-language plugin settings page.\n\n";
				$message .= "\n\n";
				if ( preg_match ( '/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,6}$/i', $_POST['ccmail'] ) && preg_match ( '/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,6}$/i', get_bloginfo ('admin_email') ) ) {
					$result = wp_mail('contact@xiligroup.com', $_POST['thema'].' from xili-language v.'.XILILANGUAGE_VER.' plugin settings page.' , $message, $headers );
					$message = __('Email sent.','xili-language');
					$msg = 1;
					$sent = ($result) ? __('WP Mail OK', 'xili-language') : __('Issue in wp_mail or smtp config', 'xili-language');
					$emessage = sprintf( __( 'Thanks for your email. A copy was sent to %s (%s)','xili-language' ), $_POST['ccmail'], $sent ) ;
				} else {
					$msg = 2;
					$emessage = sprintf( __( 'Issue in your email. NOT sent to Cc: %s or the return address %s is not good !','xili-language' ), $_POST['ccmail'], get_bloginfo ('admin_email') ) ;
				}
				break;
		}
		$themessages[1] = __('Email sent.','xili-language');
		$themessages[2] = __('Email not sent. Please verify email field','xili-language');

		add_meta_box('xili-language-box-mail', __('Mail & Support','xili-language'), array(&$this,'on_box_mail_content'), $this->thehook3 , 'normal', 'low');



		$data = array(
			'action'=>$action, 'emessage'=>$emessage
		);

		?>
		<div id="xili-language-support" class="wrap columns-2 minwidth">
			<?php screen_icon('options-general'); ?>
			<h2><?php _e('Languages','xili-language') ?></h2>
			<h3 class="nav-tab-wrapper">
			<?php $this->set_tabs_line() ?>
			</h3>

			<?php if (0!= $msg ) { ?>
			<div id="message" class="updated fade"><p><?php echo $themessages[$msg]; ?></p></div>
			<?php } ?>
			<form name="support" id="support" method="post" action="options-general.php?page=language_support">
				<?php wp_nonce_field('xili-language-support'); ?>
				<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
				<p class="width23 boldtext">
				<?php printf(__("For support, before sending an email with the form below, don't forget to visit the readme as %shere%s and the links listed in contextual help tab (on top left).",'xili-language'),'<a href="'. $this->repositorylink .'" target="_blank">','</a>' ); ?>
				</p>
				<?php $this->setting_form_content( $this->thehook3, $data );
			?>
			</form>
		</div>
		<?php $this->setting_form_js( $this->thehook3 );
	}


	function check_other_xili_plugins () {
		$list = array();
		//if ( class_exists( 'xili_language' ) ) $list[] = 'xili-language' ;
		if ( class_exists( 'xili_tidy_tags' ) ) $list[] = 'xili-tidy-tags' ;
		if ( class_exists( 'xili_dictionary' ) ) $list[] = 'xili-dictionary' ;
		if ( class_exists( 'xilithemeselector' ) ) $list[] = 'xilitheme-select' ;
		if ( function_exists( 'insert_a_floom' ) ) $list[] = 'xili-floom-slideshow' ;
		if ( class_exists( 'xili_postinpost' ) ) $list[] = 'xili-postinpost' ;
		return implode (', ',$list) ;
	}


	/**
	 * for each page : tabs line
	 * @since 2.4.1
	 */
	function set_tabs_line() {
		global $pagenow;
		$id = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : 'language_page';

		foreach ( $this->xl_tabs as $tab_id => $tab ) {
				$class = ( $tab['url'] == $pagenow.'?page='.$id ) ? ' nav-tab-active' : '';
				echo '<a href="' . $tab['url'] .'" class="nav-tab' . $class . '">' . esc_html( $tab['label'] ) . '</a>';
		}
	}

	/**
	 * for each three forms of settings side-info-column
	 * @since 2.4.1
	 * @updated 2.5, 2.9.12
	 */
	function setting_form_content( $the_hook, $data ) {

		$poststuff_class = "";
		$postbody_class = 'class="metabox-holder columns-2"';
		$postleft_id = 'id="postbox-container-2"';
		$postright_id = "postbox-container-1";
		$postleft_class = 'class="postbox-container"';
		$postright_class = "postbox-container";

		?>
		<div id="poststuff" <?php echo $poststuff_class; ?>>
			<div id="post-body" <?php echo $postbody_class; ?> >

				<div id="<?php echo $postright_id; ?>" class="<?php echo $postright_class; ?>">
					<?php do_meta_boxes($the_hook, 'side', $data); ?>
				</div>

				<div id="post-body-content">

					<div <?php echo $postleft_id; ?> <?php echo $postleft_class; ?> style="min-width:360px">
						<?php do_meta_boxes($the_hook, 'normal', $data); ?>
					</div>

					<h4><a href="<?php echo $this->repositorylink; ?>" title="xili-language page and docs" target="_blank" style="text-decoration:none" >
							<img style="vertical-align:bottom; margin-right:10px" src="<?php echo plugins_url( 'images/xililang-logo-32.png', $this->file_file ) ; ?>" alt="xili-language logo"/>
						</a>&nbsp;&nbsp;&nbsp;©&nbsp;
						<a href="<?php echo $this->devxililink; ?>" target="_blank" title="<?php esc_attr_e('Author'); ?>" >xiligroup.com</a>™ - msc 2007-2015 - v. <?php echo XILILANGUAGE_VER; ?>
					</h4>

				</div>
			</div>
			<br class="clear" />
		</div>
		<?php
	}

	/**
	 * add js at end of each three forms of settings
	 * @since 2.4.1
	 */
	function setting_form_js( $the_hook ) { ?>
	<script type="text/javascript">
	//<![CDATA[
			jQuery(document).ready( function($) {
				// close postboxes that should be closed
				$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				// postboxes setup
				postboxes.add_postbox_toggles('<?php echo $the_hook; ?>');

			<?php if ( $the_hook == $this->thehook4 ) {	/* expert */ ?>
				$('#show-manual-box').change(function() {

						$('#manual-menu-box').toggle();

				});
				$('#show-menus-boxes').change(function() {

						$('#old-menus-boxes').toggle();

				});

			<?php } ?>
			<?php if ( $the_hook == $this->thehook ) {	?>
				// for examples list
			$('#language_name_list').change(function() {
				var x = $(this).val();
				$('#language_name').val(x);
				var x = $(this).val();
				x = x.toLowerCase();
				$('#language_nicename').val(x);
				var v = $('#language_name_list option:selected').text();
				v1 = v.substring(0,v.indexOf("/",0));
				v2 = v1.substring(0,v1.indexOf(" (",0));
				if ('' != v2 ) {
					v = v2;
				} else {
					v = v1;
				}
				$('#language_description').val(v);
			});
			<?php } ?>
			});
			//]]>
		</script>
	<?php
	}



	/***************** Side settings metaboxes *************/

	/**
	 * info box
	 */
	function on_sidebox_info_content() { ?>

		<p><?php _e("This plugin was developed with the taxonomies, terms tables and WP specifications. <br /> xili-language create a new taxonomy used for language of posts and pages and custom post types. For settings (basic or expert), 5 tabs were available.<br /><br /> To attach a language to a post, a box gathering infos in available in new and edit post admin side pages. Also, selectors are in Quick Edit bulk mode of Posts list.",'xili-language') ?></p>
		<?php
	}

	/**
	 * Special box
	 *
	 * @since 2.4.1
	 *
	 */
	function on_sidebox_for_specials ( $data ) {

		if ( get_option('permalink_structure') ) {
			// back compat
			if ( $this->xili_settings['lang_permalink'] == 'perma_ok' ) {
				$lang_perma_state = 'perma_ok';
			} else {
				$lang_perma_state = '';
			}

			?>

			<fieldset class="box"><legend><?php _e('Permalinks rules', 'xili-language'); ?></legend>

			<label for="lang_permalink" class="selectit"><input id="lang_permalink" name="lang_permalink" type="checkbox" <?php checked ( $lang_perma_state, 'perma_ok'); ?> value="perma_ok" />
			&nbsp;<?php _e( 'Permalinks for languages', 'xili-language'); ?></label>

			<p><small><em><?php _e('If checked, xili-language incorporate language (or alias) at the begining of permalinks... (premium services for donators, see docs)', 'xili-language'); ?></em><br/>
			<?php printf( __( 'URI will be like %s, xx is slug/alias of current language.', 'xili-language' ), '<em>' . get_option('home') . '<strong>/xx</strong>' . get_option('permalink_structure') . '</em>' ); ?></small><p>
			<?php // to force permalinks flush  ?>
			<label for="force_permalinks_flush" class="selectit"><input id="force_permalinks_flush" name="force_permalinks_flush" type="checkbox" value="enable" /> <?php _e('force permalinks flush', 'xili-language'); ?></label>
			</fieldset>
			<div class='submit'>
				<input id='updatespecials' name='updatespecials' type='submit' tabindex='6' value="<?php _e('Update','xili-language') ?>" />
			</div>
			<div class='clearb1'>&nbsp;</div>

		<?php } ?>

		<fieldset class="box"><legend><?php _e('Translation domains settings', 'xili-language'); ?></legend><p>
			<?php _e("For experts in multilingual CMS: Choose the rule to modify domains switching.", "xili-language");  ?><br />
			<em><?php printf(__("Some plugins are well built to be translation ready. On front-end side, xili-language is able to switch the text_domain of the good plugin file or to the local theme_domain. So, if terms (and translations) are available in these .mo files, these terms are displayed in the right language. Rule for plugins without front-end text don’t need to be changed. Others need modification of php source via a customized filter (add_action).<br />More infos in sources and perhaps soon in <a href=\"%s\">wiki</a>.", "xili-language"), $this->wikilink ) ; ?>
			<br /><?php _e("Sometime languages sub-folder (Domain Path) containing .mo files is not well defined in plugin source header. So, after looking inside plugin folder, insert the good one between / !", "xili-language");  ?><br />
			</em><br /><br />
			<table class="widefat trans-domain"><thead>
			<?php
			echo '<tr><th class="p-name">' . __('Name', 'xili-language') . '</th><th class="p-rule" >' . __('Rule', 'xili-language') . '</th><th class="p-domain">' . __('Plugin Domain', 'xili-language') . '</th><th style="p-path">' . __('Domain Path', 'xili-language') . '</th></tr>';
			echo '</thead><tbody>';
			$active_plugin_by_domain = array();
			foreach ( wp_get_active_and_valid_plugins() as $plugin_file ) {
				$plugin = get_plugin_data( $plugin_file, false, false ) ;
				if ( $plugin['TextDomain'] != 'Text Domain' && $plugin['TextDomain'] != '' ) {
					$active_plugin_by_domain[$plugin['TextDomain']]['plugin-data'] = $plugin;
					$active_plugin_by_domain[$plugin['TextDomain']]['plugin-path'] = str_replace ( WP_PLUGIN_DIR , '', plugin_dir_path( $plugin_file )); // sub-folder with /nameofplugin/
				}
			}
			foreach ( $this->xili_settings['domains'] as $domain => $state ) {
				if ( $domain == 'default' || (!in_array( $domain, $this->unusable_domains) && isset ( $active_plugin_by_domain[$domain] ) ) ) {
					$domaininlist = ( $domain == 'default' ) ? __( 'Switch default domain of WP','xili-language' ) : $active_plugin_by_domain[$domain]['plugin-data']['Name'] ;
					?>
					<tr><th>
					<label for="xili_language_domains_<?php echo $domain ; ?>" class="selectit"><?php echo $domaininlist ; ?>&nbsp;&nbsp;</label></th><td>
						<select id="xili_language_domains_<?php echo $domain ; ?>" name="xili_language_domains_<?php echo $domain ; ?>" >
							<option value="" <?php selected ( $state, ''); ?> /><?php _e('no modification', 'xili-language') ; ?></option>
							<option value="enable" <?php selected ( $state, 'enable'); ?> /> <?php _e('translation in local', 'xili-language') ; ?></option>
							<?php if( $domain != 'default' ) { ?>
							<option value="renamed" <?php selected ( $state, 'renamed'); ?> /> <?php _e('translation in plugin', 'xili-language') ; ?></option>
							<option value="filter" <?php selected ( $state, 'filter'); ?> /> <?php _e('custom translation', 'xili-language') ; ?></option>
							<?php } ?>
						</select>
					</td><td>&nbsp;&nbsp;<?php echo $domain ;

					if ( $state == 'filter' ) {
						$has_filter = has_filter ('load_plugin_domain_for_curlang_' . str_replace('-', '_', $domain) );
						if ( !$has_filter ) echo '<br /><em><small>'.sprintf(__('Customization requires filter tag:%s','xili-language'), " 'load_plugin_domain_for_curlang_".str_replace('-', '_', $domain) ."'" ) . '</small></em>';
					} ?></td>
					<?php if( $domain != 'default' ) {
						$value = ('' ==  $active_plugin_by_domain[$domain]['plugin-data']['DomainPath']) ? '/' : $active_plugin_by_domain[$domain]['plugin-data']['DomainPath'];
						?>
					<td>&nbsp;&nbsp;<input id="xili_language_domain_path_<?php echo $domain ; ?>" name="xili_language_domain_path_<?php echo $domain ; ?>" type="text" value="<?php echo $value; ?>" />
						<input id="xili_language_plugin_path_<?php echo $domain ; ?>" name="xili_language_plugin_path_<?php echo $domain ; ?>" type="hidden" value="<?php echo $active_plugin_by_domain[$domain]['plugin-path']; ?>" />
					</td>
					<?php

				} ?>
					</tr>
					<?php
				}
			}
			echo '</tbody></table>';
			if ( $this->show ) print_r( $this->arraydomains ) ;
			?>
		</p></fieldset>

		<fieldset class="box" ><legend><?php _e('Locale (date) translation', 'xili-language'); ?></legend><p>
			<?php _e("Since v2.4, new way for locale (wp_locale) translation.", "xili-language"); ?><br /><br />
			<label for="xili_language_wp_locale"><?php _e('Mode wp_locale','xili-language') ?> <input id="xili_language_wp_locale" name="xili_language_wp_locale" type="checkbox" value="wp_locale" <?php checked( $this->xili_settings['wp_locale'], 'wp_locale', true); ?> /></label></p>
		</fieldset>

		<div class='submit'>
		<input id='updatespecials' name='updatespecials' type='submit' tabindex='6' value="<?php _e('Update','xili-language') ?>" /></div>

		<div class="clearb1">&nbsp;</div><?php

	}

	/**
	 * Theme's information box
	 *
	 * @since 2.4.1
	 *
	 */
	function on_sidebox_4_theme_info( $data ) {
		$template_directory = $this->get_template_directory;
		if ( is_child_theme() ) { // 1.8.1 and WP 3.0
			$theme_name = get_option("current_theme") . ' </strong>' . __('child of','xili-language') . ' <strong>'.get_option("template");
		} else {
			$theme_name = get_option("current_theme");
		}
		?>
		<fieldset class="themeinfo"><legend><?php echo __("Theme type and domain:",'xili-language'); ?></legend>
			<strong><?php echo ' - '.$theme_name.' -'; ?></strong>
			<?php
			if ("" != $this->parent->thetextdomain) {
				echo '<br />' . __('theme_domain:','xili-language').' <em>'.$this->parent->thetextdomain.'</em><br />'.__('as function like:','xili-language').'<em> _e(\'-->\',\''.$this->parent->thetextdomain.'\');</em>';
			} else {
				echo '<span class="red-alert">'.$this->admin_messages['alert']['no_domain_defined'].'</span>';
				if (''!=$this->domaindetectmsg) {
					echo '<br /><span class="red-alert">'. $this->domaindetectmsg.' '.$this->admin_messages['alert']['default'].'</span>';
				}
			} ?><br />

		</fieldset>
		<fieldset class="box"><legend><?php echo __("Language files:",'xili-language'); ?></legend>
		<p>
		<?php echo __("Languages sub-folder:",'xili-language').' '.$this->xili_settings['langs_folder']; ?><br />
		<?php _e('Available MO files:','xili-language'); echo '<br />';
		if ( file_exists( $template_directory ) ) // when theme was unavailable
			$this->find_files($template_directory, "/(\w\w_\w\w|\w\w).mo$/", array(&$this,"available_mo_files") ) ;


		if ( file_exists( WP_LANG_DIR.'/themes' ) ) { // when languages/themes was unavailable
		 	echo '<br /><em>'; _e('Available MO files in WP_LANG_DIR/themes:','xili-language'); echo '</em><br />';
			$this->find_files( WP_LANG_DIR.'/themes', "/(" . $this->thetextdomain . ")-local-(\w\w_\w\w|\w\w).mo$/", array(&$this,"available_mo_files" ) , true ) ;
		}

		if ( $this->parent->ltd === false )	{
			if ( is_child_theme() ) {
				echo '<br /><span class="red-alert">'.$this->admin_messages['alert']['no_load_function_child'].'</span>';
			} else {
				if ( $this->parent->ltd_parent === false )
					echo '<br /><span class="red-alert">'.$this->admin_messages['alert']['no_load_function'].'</span>';
			}
		}
			?>
		</p><br />
		</fieldset>


	<?php
		$screen = get_current_screen(); // to limit unwanted side effects (form)

		if ( $screen->id == 'settings_page_language_files' && is_child_theme() ) {

			if ( $this->xili_settings['mo_parent_child_merging'] ) {
			?>
				<fieldset class="box"><legend><?php echo __("Language files in parent theme:",'xili-language'); ?></legend>
					<p>
					<?php echo __("Languages sub-folder:",'xili-language').' '.$this->xili_settings['parent_langs_folder']; ?><br />
					<?php _e('Available MO files:','xili-language'); echo '<br />';
					$template_directory = $this->get_parent_theme_directory;
					if ( file_exists( $template_directory ) ) {// when theme was unavailable
						$this->find_files( $template_directory, "/^(\w\w_\w\w|\w\w).mo$/", array(&$this,"available_mo_files") ) ;
					}
					if ( file_exists( WP_LANG_DIR.'/themes' ) ) { // when languages/themes was unavailable
						echo '<br /><em>'.__('in WP_LANG_DIR/themes:', 'xili-language').'</em>'.'<br />';
						$this->find_files( WP_LANG_DIR.'/themes', "/(" . $this->parent->thetextdomain . ")-(\w\w_\w\w|\w\w).mo$/", array(&$this,"available_mo_files" ) , true ) ;
					}
					if ( $this->parent->ltd_parent === false )	{
						echo '<br /><span class="red-alert">'.$this->admin_messages['alert']['no_load_function'].'</span>';
					}
					?>
					</p><br />
				</fieldset>

			<?php
			}
			echo '<br />' . __( 'MO merging between parent and child','xili-language'). ':&nbsp;';
			// update 2.12.1
			if ( $this->xili_settings['mo_parent_child_merging'] === true ) $this->xili_settings['mo_parent_child_merging'] = "parent-priority";
			echo '<select id="mo_parent_child_merging" name="mo_parent_child_merging" style="width:80%;" >';
				echo'<option value="" ' . selected ( $this->xili_settings['mo_parent_child_merging'], "", false ).' >' . __('parent mo not used', 'xili-language') . '</option>';
				echo'<option value="parent-priority" ' . selected ( $this->xili_settings['mo_parent_child_merging'], "parent-priority" ,false ) . ' >' . __('with priority of parent mo', 'xili-language') . '</option>';
				echo'<option value="child-priority" ' . selected ( $this->xili_settings['mo_parent_child_merging'], "child-priority" ,false ) . ' >' . __('with priority of child mo', 'xili-language') . '</option>';
			echo '</select>';

			?>
			<div class='submit'>
			<input id='mo_merging' name='mo_merging' type='submit' value="<?php _e('Update','xili-language') ?>" />
			</div>
			<?php


		}

	}

	/**
	 * Theme's information box
	 *
	 * @since 2.12
	 *
	 */
	function on_sidebox_5_theme_info( $data ) {

		$the_theme = wp_get_theme();
		?>
		<fieldset class="themeinfo"><legend><?php echo __("Header infos",'xili-language'); ?></legend>
		<?php
			echo '<p>Name: '. $the_theme->Name . '</p>';
			echo '<p>Author: '. $the_theme->Author . '</p>';
			echo '<p>Version: '. $the_theme->Version . '</p>';
			if ( is_child_theme() ) {
				echo '<p>Template: '. $the_theme->Template . '</p>';
			};
		if ( $textdomain = $the_theme->get('TextDomain') ) {
			echo '<p>Text Domain: '. $textdomain . '</p>';
			$path = get_stylesheet_directory();
			if ( $domainpath = $the_theme->get('DomainPath') ) {
				echo '<p>Domain Path: '. $domainpath . '</p>';
				$path .= $domainpath;
			} else {
				echo '<p><em>'. __('The Domain Path is not specified in Theme Header of style.css ! - /languages - will be used by default.', 'xili-language') . '</em></p>';
				$path .= '/languages';
			}
			$folder = file_exists( $path );
			if ( !$folder ) {
				echo '<p><em>'. __('The languages folder (Domain Path) does not exist inside theme folder.', 'xili-language') . '</em></p>';
			}

		} else {
			echo '<p><em>'. __('The Text Domain is not specified in Theme Header of style.css !', 'xili-language') . '</em></p>';
		}

		?>
		</fieldset>
		<?php
	}



	/**
	 * Actions box
	 * menu
	 * gold options
	 */
	function on_box_expert( $data ) {
		extract($data);
		$template_directory = $this->get_template_directory;
		if ( is_child_theme() ) { // 1.8.1 and WP 3.0
			$theme_name = get_option("stylesheet") . ' ' . __('child of','xili-language') . ' ' . get_option("template");
		} else {
			$theme_name = get_option("template");
		}

		if ( current_theme_supports( 'menus' ) ) { ?>

		<p><em><?php _e('These options are still present for compatibility reasons with previous versions < 2.9.22. <strong>For new installation, it is preferable to use the insertion points and menu options.</strong>', 'xili-language'); ?></em><br /><?php printf (__("Goto <a href=\"%s\" title=\"Menu Items definition\">Appearance Menus</a> settings.","xili-language"), "nav-menus.php");?></p>

		<label for="show-menus-boxes" class="selectit"><input name="show-menus-boxes" id="show-menus-boxes" type="checkbox" value="show">&nbsp;<?php _e('Show previous menus settings (reserved for backwards compatibility)','xili-language'); ?></label>

		<div id="old-menus-boxes" class="hiddenbox">
		<fieldset class="box"><legend><?php echo __("Nav menu: Home links in each language",'xili-language'); ?></legend>
			<?php
				$menu_locations = get_registered_nav_menus(); // get_nav_menu_locations() keeps data in cache; // only if linked to a content - get_registered_nav_menus() ; // 2.8.8 with has_nav_menu()

				$selected_menu_locations = ( isset($this->xili_settings['navmenu_check_options'] ) ) ? $this->xili_settings['navmenu_check_options'] : array();
			if ( is_array( $menu_locations ) && $menu_locations != array() ) { // 2.8.6 - wp 3.6
			?>
			<fieldset class="box leftbox">
				<?php _e('Choose location(s) of nav menu(s) where languages list will be automatically inserted. For each location, choose the type of list. Experts can create their own list by using api (hook) available in plugin.','xili-language'); ?>
				<br><strong><?php printf( __("Since version 2.8.8, it is possible to insert the languages list anywhere in the navigation menu via the <a href=\"%s\" title=\"Menu Items definition\">Appearance Menus Builder</a> (drag and drop method).",'xili-language'), "nav-menus.php") ; ?></strong>
				<br><em><?php printf( __("To avoid unwanted double items in navigation menu, choose one of the 2 methods but not both ! The future version will use only menus set in <a href=\"%s\" title=\"Menu Items definition\">Appearance Menus Builder</a>.",'xili-language'), "nav-menus.php") ; ?></em>

			</fieldset>
			<fieldset class="box rightbox">
			<?php
			if ( $this->this_has_external_filter('xl_language_list') ) {	// is list of options described
				$this->langs_list_options = array();
				do_action( 'xili_language_list_options', $theoption); // update the list of external action
			}
			echo '<table style="width:98%;"><tbody>';
			foreach ( $menu_locations as $menu_location => $location_id ) {

				$locations_enable = ( isset($selected_menu_locations[$menu_location]) ) ? $selected_menu_locations[$menu_location]['navenable'] : '';

				if ( $locations_enable == 'enable' || ( !isset($this->xili_settings['navmenu_check_options'] ) && isset($this->xili_settings['navmenu_check_option']) && $this->xili_settings['navmenu_check_option'] == $menu_location ) )
						$checked = 'checked="checked"'; // ascendant compatibility ( !isset($this->xili_settings['navmenu_check_options']) &&
					else
						$checked = '';

				?>
				<tr><th style="text-align:left;"><label for="xili_navmenu_check_option_<?php echo $menu_location; ?>" class="selectit"><input id="xili_navmenu_check_option_<?php echo $menu_location; ?>" name="xili_navmenu_check_option_<?php echo $menu_location; ?>" type="checkbox" value="enable" <?php echo $checked; ?> /> <?php echo $menu_location; ?></label>&nbsp;<?php echo ( has_nav_menu ( $menu_location ) ) ? '' : '<abbr title="menu location without content" class="red-alert"> (?) </abbr>' ; ?>
				</th><td><label for="xili_navmenu_check_optiontype_<?php echo $menu_location; ?>"><?php _e('Type','xili-language' ) ?>:
				<select style="width:80%;" name="xili_navmenu_check_optiontype_<?php echo $menu_location; ?>" id="xili_navmenu_check_optiontype_<?php echo $menu_location; ?>">
				<?php
				if ( $this->langs_list_options == array() ) {
						echo '<option value="" >default</option>';
				} else {
					$subtitle = '';
					foreach ($this->langs_list_options as $typeoption) {
						if ( false !== strpos( $typeoption[0], 'navmenu' ) ) {
							$seltypeoption = ( isset( $this->xili_settings['navmenu_check_options'][$menu_location]['navtype']) ) ? $this->xili_settings['navmenu_check_options'][$menu_location]['navtype'] : "";
							if ( $seltypeoption == $typeoption[0] ) $subtitle = $typeoption[2] ; // 2.8.6
							echo '<option title="'. $typeoption[2] .'" value="'.$typeoption[0].'" '. selected($seltypeoption, $typeoption[0], false ).' >'. $typeoption[1] .'</option>';
						}
					}
				}

				?>

				</select></label>
				<?php
				$point = $this->has_insertion_point_list_menu ( $menu_location, $this->insertion_point_dummy_link );
				if ( $point != 0 ) echo '<br />&nbsp; <span class="red-alert">' . __('This menu location contains a language list insertion point !','xili-language') . '</span>'; // && $checked != ''
				if ( $subtitle != '' ) echo '<br /><span id="title_xili_navmenu_check_optiontype_'.$menu_location.'" ><em>' . $subtitle . '</em></span>';
				?>
				</td></tr>
				<?php

				// focus error
			}
			echo '</tbody></table>';
				?>


			<hr />	<br />
			<label for="nav_menu_separator" class="selectit"><?php _e('Separator before language list (<em>Character or Entity Number or Entity Name</em>)', 'xili-language'); ?> : <input id="nav_menu_separator" name="nav_menu_separator" type="text" value="<?php echo htmlentities(stripslashes($this->xili_settings['nav_menu_separator'])) ?>" /> </label><br /><br />
			<label for="list_in_nav_enable" class="selectit"><input id="list_in_nav_enable" name="list_in_nav_enable" type="checkbox" value="enable" <?php checked( $list_in_nav_enable, 'enable', true ); ?> /> <?php _e('Add language list at end of nav menus checked above', 'xili-language'); ?></label><br />


			</fieldset>
			<br />
			<fieldset class="box leftbox">
					<?php echo __("Home menu item will be translated when changing language:",'xili-language'); ?>
				</fieldset>
				<fieldset class="box rightbox">
					<label for="xili_home_item_nav_menu" class="selectit"><input id="xili_home_item_nav_menu" name="xili_home_item_nav_menu" type="checkbox" value="modify" <?php checked( $this->xili_settings['home_item_nav_menu'], 'modify', true ); ?> /> <?php _e('Menu Home item with language.', 'xili-language'); ?></label>
				</fieldset>
				<?php if ( $this->show_page_on_front ) { ?>
					<br />
					<fieldset class="box leftbox">
						<?php echo __("Keep original link of frontpage array in menu pages list:",'xili-language'); ?>
					</fieldset>
					<fieldset class="box rightbox">
						<label for="xili_list_pages_check_option" class="selectit"><input id="xili_list_pages_check_option" name="xili_list_pages_check_option" type="checkbox" value="fixe" <?php checked( $this->xili_settings['list_pages_check_option'], 'fixe', true ); ?> /> <?php _e('One home per language.', 'xili-language'); ?></label>
					</fieldset>
				<?php } ?>
				<br />
				<div class="submit"><input id='innavenable' name='innavenable' type='submit' value="<?php _e('Update','xili-language') ?>" /></div>
				<br />

				</fieldset>
				<br />

				<fieldset class="box"><legend><?php echo __("Nav menu: Automatic sub-selection of pages according current language",'xili-language'); ?></legend>
					<fieldset class="box leftbox">
					<?php _e('Choose location of nav menu where sub-selection of pages list will be automatically inserted according current displayed language:','xili-language'); ?><br /><?php _e('Args is like in function wp_list_pages, example: <em>include=11,15</em><br />Note: If args kept empty, the selection will done on all pages (avoid it).','xili-language'); ?>
				<br><strong><?php printf( __("Since version 2.9.10, it is possible to insert a list of pages sub-selected according current language anywhere in the navigation menu via the <a href=\"%s\" title=\"Menu Items definition\">Appearance Menus Builder</a> (drag and drop method).",'xili-language'), "nav-menus.php") ; ?></strong>
				<br><em><?php printf( __("To avoid unwanted double items in navigation menu, choose one of the 2 methods but not both ! The future version will use only menus set in <a href=\"%s\" title=\"Menu Items definition\">Appearance Menus Builder</a>.",'xili-language'), "nav-menus.php") ; ?></em>
				</fieldset>
					<fieldset class="box rightbox">
						<?php

						$selected_page_menu_locations = ( isset($this->xili_settings['array_navmenu_check_option_page'] ) ) ? $this->xili_settings['array_navmenu_check_option_page'] : array();
						if ( is_array( $menu_locations ) ) {
							echo '<table><tbody>';
							foreach ( $menu_locations as $menu_location => $location_id ) {
								$args= ( isset ( $selected_page_menu_locations[$menu_location]['args'] ) ) ? $selected_page_menu_locations[$menu_location]['args'] : "";
								?>
								<tr><th style="text-align:left;"><label for="xili_navmenu_check_option_page_<?php echo $menu_location; ?>" class="selectit"><input id="xili_navmenu_check_option_page_<?php echo $menu_location; ?>" name="xili_navmenu_check_option_page_<?php echo $menu_location; ?>" type="checkbox" value="enable" <?php echo checked ( ( isset ( $selected_page_menu_locations[$menu_location]['enable'] ) ) ? $selected_page_menu_locations[$menu_location]['enable'] : '' , 'enable' ) ; ?> /> <?php echo $menu_location; ?></label>&nbsp;&nbsp;<?php echo ( has_nav_menu ($menu_location) ) ? '' : '<abbr title="menu location without content" class="red-alert"> (?) </abbr>' ; ?>
				</th><td><label for="xili_navmenu_page_args_<?php echo $menu_location; ?>"><?php _e('Args','xili-language' ) ?>:
					<input id="xili_navmenu_page_args_<?php echo $menu_location; ?>" name="xili_navmenu_page_args_<?php echo $menu_location; ?>" type="text" value="<?php echo $args ?>" />
					</label>
					<?php
					$point = $this->has_insertion_point_list_menu ( $menu_location, $this->insertion_point_dummy_link_page );
				if ( $point != 0 ) echo '<br />&nbsp; <span class="red-alert">' . __('This menu location contains a page sub-list insertion point !','xili-language') . '</span>';
					?>
					</td></tr>
					<?php
							}
							echo '</tbody></table>';
						}
						?>

				</fieldset>
				<br />
				<div class="submit"><input id='pagnavenable' name='pagnavenable' type='submit' value="<?php _e('Update','xili-language') ?>" /></div>
				<?php } else {
					printf (__("This theme doesn't contain active Nav Menu. List of languages cannot be automatically added.","xili-language"));
					echo '<br />';printf (__("See <a href=\"%s\" title=\"Menu Items definition\">Appearance Menus activation</a> settings.","xili-language"), "nav-menus.php");
				} ?>

			</fieldset>
			<br />

			<label for="show-manual-box" class="selectit"><input name="show-manual-box" id="show-manual-box" type="checkbox" value="show">&nbsp;<?php _e('Show toolbox for manual insertion (reserved purposes)','xili-language'); ?></label>
			<fieldset id="manual-menu-box" class="box hiddenbox"><legend><?php echo __("Theme's nav menu items settings",'xili-language'); ?></legend>
				<p><?php
				if ( $menu_locations ) {
					$loc_count = count( $menu_locations ); ?>
					<fieldset class="box leftbox">
						<?php printf (__("This theme (%s) contains %d Nav Menu(s).",'xili-language'), $theme_name, $loc_count); ?>
						<p><?php _e('Choose nav menu where languages list will be manually inserted:','xili-language'); ?></p>
					</fieldset>
					<fieldset class="box rightbox">
					<select name="xili_navmenu_check_option2" id="xili_navmenu_check_option2" class="fullwidth">
				<?php
					foreach ($menu_locations as $menu_location => $location_id) {
				if ( isset( $this->xili_settings['navmenu_check_option2'] ) && $this->xili_settings['navmenu_check_option2'] == $menu_location )
						$checked = 'selected = "selected"';
					else
						$checked = '';

				echo '<option value="'.$menu_location.'" '.$checked.' >'.$menu_location.'</option>';
			}
				?>
			</select>
			<br />	<br />
			<?php
			echo '<br />';printf (__("See <a href=\"%s\" title=\"Menu Items definition\">Appearance Menus</a> settings.","xili-language"), "nav-menus.php");
			if($list_in_nav_enable =='enable') {
					echo '<br /><span class="red-alert">'.$this->admin_messages['alert']['menu_auto_inserted'].'</span>'; }

			?>
			</p>
			</fieldset>
			<br /><?php _e('Do you want to add list of language links at the end ?','xili-language'); ?><br />
			<div class="submit"><input id='menuadditems' name='menuadditems' type='submit' value="<?php _e('Add menu items','xili-language') ?>" /></div>

			<?php } else {
				printf (__("This theme doesn't contain active Nav Menu.","xili-language"));
				echo '<br />';printf (__("See <a href=\"%s\" title=\"Menu Items definition\">Appearance Menus</a> settings.","xili-language"), "nav-menus.php");
			} ?>
		</fieldset>
		</div> <?php // toogle old settings ?>
		<?php }


		if ( $this->xili_settings['functions_enable'] !='' && function_exists('xiliml_setlang_of_undefined_posts')) {
			?><p><?php _e("Special Gold Actions",'xili-language') ?></p><?php
			xiliml_special_UI_undefined_posts ($this->langs_group_id);
		}
	}
	/**
	 *
	 * @since 2.11.1
	 *
	 */
	function on_box_plugins_expert( $data ) {
		do_action ('import_list_forms_action'); // 2.20.3

		$checked = checked ($this->xili_settings['enable_fc_theme_class'], 'enable', false ) ;
		?>
		<p><?php _e('Define some behaviours with plugins like JetPack.','xili-language'); ?></p>
		<fieldset id="jetpack-box" class="box"><legend><?php echo __("JetPack settings",'xili-language'); ?></legend>
			<?php if ( class_exists ('jetpack')) { ?>
			<label for="enable_fc_theme_class" class="selectit">
				<input <?php echo $checked; ?> name="enable_fc_theme_class" id="enable_fc_theme_class" type="checkbox" value="enable">
				&nbsp;<?php _e('Give priority to class - Featured_Content - of current theme.','xili-language'); ?>
			</label>
			<div class="submit"><input id='jetpack_fc_enable' name='jetpack_fc_enable' type='submit' value="<?php _e('Update','xili-language') ?>" /></div>
			<?php } else {
				echo '<p>' . __('JetPack plugin is not active.','xili-language') . '</p>';
			} ?>
		</fieldset>
		<?php
		$subfolder = get_option( 'xl-bbp-addon-activated-folder', '/' );

		?>
		<p><?php _e('Choose compatibility/integration with bbPress.','xili-language'); ?></p>
		<fieldset id="bbPress-box" class="box"><legend><?php echo __("bbPress settings",'xili-language'); ?></legend>
			<?php if ( class_exists ('bbPress')) { ?>
			<label for="xl-bbp-addon" class="selectit">
				<select name="xl-bbp-addon" id="xl-bbp-addon" >
					<?php
					echo '<option value="" '.selected( $subfolder, '', false ).' >'.__('no integration', 'xili-language').'</option>';
					echo '<option value="/" '.selected( $subfolder, '/', false ).' >'.__('default integration', 'xili-language').'</option>';
					echo '<option value="/xili-includes/" '.selected( $subfolder, '/xili-includes/', false ).' >'.__('custom integration (future)', 'xili-language').'</option>';
					?>
				</select>
				&nbsp;<?php _e('Define the folder of file for bbPress integration.','xili-language'); ?>
			</label>
			<div class="submit"><input id='xl-bbp-addon-integrate' name='xl-bbp-addon-integrate' type='submit' value="<?php _e('Update','xili-language') ?>" /></div>
			<?php } else {
				echo '<p>' . __('bbPress plugin is not active.','xili-language') . '</p>';
			} ?>
		</fieldset>
		<?php
	}

	function on_box_mail_content ( $data ) {
		extract( $data );
		global $wp_version ;
		$theme = ( isset ($this->xili_settings['theme']) ) ? $this->xili_settings['theme'] : "";
		$wplang = ( isset ($this->xili_settings['wplang']) ) ? $this->xili_settings['wplang'] : "";
		$xiliplug = ( isset ($this->xili_settings['xiliplug']) ) ? $this->xili_settings['xiliplug'] : "";
		if ( '' != $emessage ) { ?>
			<h4><?php _e('Note:','xili-language') ?></h4>
			<p><strong><?php echo $emessage;?></strong></p>
		<?php } ?>
		<fieldset class="mailto"><legend><?php _e('Mail to dev.xiligroup', 'xili-language'); ?></legend><p class="textright">
		<label for="ccmail"><?php _e('Cc: (Reply to:)','xili-language'); ?>
		<input class="widefat width23" id="ccmail" name="ccmail" type="text" value="<?php bloginfo ('admin_email') ; ?>" /></label><br /><br /></p><p class="textleft">
		<?php if ( false === strpos( get_bloginfo ('url'), 'local' ) ){ ?>
			<label for="urlenable">
				<input type="checkbox" id="urlenable" name="urlenable" value="enable" <?php checked(( isset ($this->xili_settings['url']) && $this->xili_settings['url']=='enable'), true, true ); ?> />&nbsp;<?php bloginfo ('url') ; ?>
			</label><br />
		<?php } else { ?>
			<input type="hidden" name="onlocalhost" id="onlocalhost" value="localhost" />
		<?php } ?>
		<br /><em><?php _e('When checking and giving detailled infos, support will be better !', 'xili-language'); ?></em><br />
		<label for="themeenable">
			<input type="checkbox" id="themeenable" name="themeenable" value="enable" <?php checked( $theme, 'enable', true ); ?> />&nbsp;<?php echo "Theme name= ".get_option ('stylesheet') ; ?>
		</label><br />
		<?php if (''!= $this->get_WPLANG() ) {?>
		<label for="wplangenable">
			<input type="checkbox" id="wplangenable" name="wplangenable" value="enable" <?php checked( $wplang,'enable', true ) ; ?> />&nbsp;<?php echo "WPLANG= ".$this->get_WPLANG() ; ?>
		</label><br />
		<?php }
		$xililanguageslist = implode (', ', $this->langs_slug_name_array);

		?>
		<label for="xililanguageslist">
			<input type="checkbox" id="xililanguageslist" name="xililanguageslist" value="enable" />&nbsp;<?php echo "Languages list= ". $xililanguageslist ; ?>
		</label><br />
		<label for="versionenable">
			<input type="checkbox" id="versionenable" name="versionenable" value="enable" <?php if( isset ($this->xili_settings['version-wp']) ) checked( $this->xili_settings['version-wp'],'enable', true); ?> />&nbsp;<?php echo "WP version: ".$wp_version ; ?>
		</label><br />
		<?php if ( get_option('permalink_structure') ) { // 2.10.0 - ?>
		<label for="versionenable">
			<input type="checkbox" id="permalink_structure" name="permalink_structure" value="enable" <?php if( isset ($this->xili_settings['permalink_structure']) ) checked( $this->xili_settings['permalink_structure'], 'enable', true ); ?> />&nbsp;<?php echo "Permalink structure: <small>".get_option('permalink_structure') ."</small>"; ?>
		</label><br />
		<?php } ?>
		<br />
		<?php $list = $this->check_other_xili_plugins();
		if ( ''!= $list ) {?>
		<label for="xiliplugenable">
			<input type="checkbox" id="xiliplugenable" name="xiliplugenable" value="enable" <?php checked ( $xiliplug, 'enable', true ); ?> />&nbsp;<?php printf(__( 'Other xili plugins = %s', 'xili-language' ), $list ) ; ?>
		</label><br /><br />
		<?php } ?>
		</p><p class="textright">
		<label for="webmestre"><?php _e('Type of webmaster:','xili-language'); ?>
		<select name="webmestre" id="webmestre" class="width23">
			<?php if ( !isset ( $this->xili_settings['webmestre-level'] ) ) $this->xili_settings['webmestre-level'] = '?' ; ?>
			<option value="?" <?php selected( $this->xili_settings['webmestre-level'], '?' ); ?>><?php _e('Define your experience as webmaster…','xili-language'); ?></option>
			<option value="newbie" <?php selected( $this->xili_settings['webmestre-level'], "newbie" ); ?>><?php _e('Newbie in WP','xili-language'); ?></option>
			<option value="wp-php" <?php selected( $this->xili_settings['webmestre-level'], "wp-php" ); ?>><?php _e('Good knowledge in WP and few in php','xili-language'); ?></option>
			<option value="wp-php-dev" <?php selected( $this->xili_settings['webmestre-level'], "wp-php-dev" ); ?>><?php _e('Good knowledge in WP, CMS and good in php','xili-language'); ?></option>
			<option value="wp-plugin-theme" <?php selected( $this->xili_settings['webmestre-level'], "wp-plugin-theme" ); ?>><?php _e('WP theme and /or plugin developper','xili-language'); ?></option>
		</select></label><br /><br />
		<label for="subject"><?php _e('Subject:','xili-language'); ?>
		<input class="widefat width23" id="subject" name="subject" type="text" value="" /></label>
		<select name="thema" id="thema" class="width23">
			<option value="" ><?php _e('Choose topic...','xili-language'); ?></option>
			<option value="Message" ><?php _e('Message','xili-language'); ?></option>
			<option value="Question" ><?php _e('Question','xili-language'); ?></option>
			<option value="Encouragement" ><?php _e('Encouragement','xili-language'); ?></option>
			<option value="Support need" ><?php _e('Support need','xili-language'); ?></option>
		</select>
		<textarea class="widefat width45" rows="5" cols="20" id="mailcontent" name="mailcontent"><?php _e('Your message here…','xili-language'); ?></textarea>
		</p></fieldset>
		<p>
		<?php _e('Before send the mail, be accurate, check the infos to inform support and complete textarea. A copy (Cc:) is sent to webmaster email (modify it if needed).','xili-language'); ?>
		</p>
		<?php wp_nonce_field('xili-plugin-sendmail'); ?>
		<div class='submit'>
		<input id='sendmail' name='sendmail' type='submit' tabindex='6' value="<?php _e('Send email','xili-language') ?>" /></div>

		<div class="clearb1">&nbsp;</div><br/>
		<?php
	}

	/**
	 * If checked, functions in uninstall.php will be fired when deleting the plugin via plugins list.
	 *
	 * @since 1.8.8
	 */
	function on_sidebox_uninstall_content ( $data ) {
		extract( $data );
		$delete = ( is_multisite() ) ? 'delete_this' : 'delete';
	?>
	<p class="red-alert"><?php echo $this->admin_messages['alert']['plugin_deinstalling']; ?></p>
	<label for="delete_settings">
			<input type="checkbox" id="delete_settings" name="delete_settings" value="<?php echo $delete ?>" <?php if( in_array ( $this->xili_settings['delete_settings'] , array ( 'delete_this', 'delete' ) ) ) echo 'checked="checked"' ?> />&nbsp;<?php _e("Delete DB plugin's datas",'xili-language') ; ?>
	</label>
	<div class='submit'>
		<input id='uninstalloption' name='uninstalloption' type='submit' tabindex='6' value="<?php _e('Update','xili-language') ?>" /></div>
	<?php
	}

	/**
	 * main setting window
	 * the list
	 * clear:none - compat 3.3 - 3.4
	 */
	function on_box_lang_list_content( $data ) {
		extract($data); ?>
			<table class="widefat" style="clear:none;">
				<thead>
				<tr>
				<th scope="col" class="head-id" ><?php _e('ID') ?></th>
				<th scope="col"><?php _e('ISO Name','xili-language') ?></th>
				<?php if ( $this->alias_mode ) {
					echo '<th scope="col">'.__('Alias','xili-language') . '</th>';
				} ?>
				<th scope="col"><?php _e('Full name','xili-language') ?></th>
				<th scope="col"><?php _e('Native','xili-language') ?></th>
				<th scope="col"><?php _e('Slug','xili-language') ?></th>
				<th scope="col"><?php _e('Order','xili-language') ?></th>
				<th scope="col"><?php _e('Vis.','xili-language') ?></th>
				<th scope="col"><?php _e('Dashb.','xili-language') ?></th>
				<th scope="col" class="head-count" ><?php _e('Posts') ?></th>
				<th scope="col" class="head-action" ><?php _e('Action') ?></th>
				</tr>
				</thead>
				<tbody id="the-list">
					<?php $this->available_languages_row(); /* the lines #2260 */ ?>
				</tbody>
			</table>
	<?php
	}

	/**
	 * form to create or edit one language
	 */
	function on_box_lang_form_content( $data ) {
		extract($data);
		if ( $this->alias_mode ) { // used for flush alias refreshing in permalink-class - 2.11 ?>
			<input type="hidden" id="language_settings_action" name="language_settings_action" value="<?php echo $action ?>" />
		<?php } ?>
		<h2 id="addlang" <?php if ($action=='delete') echo 'class="red-alert"'; ?>><?php echo $formtitle ; ?></h2>
		<?php if ($action=='edit' || $action=='delete') :?>
			<input type="hidden" name="language_term_id" value="<?php echo $language->term_id ?>" />

		<?php endif; ?>
		<?php if ( $action=='delete') :?>

			<input type="hidden" name="language_nicename" value="<?php echo $language->slug ?>" />
		<?php endif; ?>
		<table class="editform" width="100%" cellspacing="2" cellpadding="5">
			<tr>
				<th width="33%" scope="row" valign="middle" align="right"><label for="language_name_list"><?php _e('Examples', 'xili-language') ?></label>:&nbsp;</th>
				<td width="67%"><select name="language_name_list" id="language_name_list">
					<?php $this->example_langs_list($language->name, $action); ?>
				</select>&nbsp;<small> <a href="http://www.gnu.org/software/hello/manual/gettext/Usual-Language-Codes.html#Usual-Language-Codes" target="_blank"><?php _e('ISO Language-Codes','xili-language'); ?></a></small>&nbsp;_&nbsp;<small><a href="http://www.gnu.org/software/hello/manual/gettext/Country-Codes.html#Country-Codes" target="_blank"><?php _e('ISO Country-Codes','xili-language'); ?></a></small><br />&nbsp;</td>
			</tr>
			<tr>
				<th scope="row" valign="middle" align="right"><label for="language_name"><?php _e('ISO Name', 'xili-language') ?></label>:&nbsp;</th>
				<td ><input name="language_name" id="language_name" type="text" value="<?php echo esc_attr($language->name); ?>" size="10" <?php if($action=='delete') echo 'disabled="disabled"' ?> />  <small>(<?php printf( __("two, three or five (six) chars like 'ja', 'bal' or 'zh_TW' (haw_US), see %s docs", 'xili-language'), '<a href="'.$this->glotpresslink.'" target="_blank" >WP Polyglots Page</a>'); ?>)</small></td>
			</tr>

			<?php if ( $this->alias_mode ) { // 2.8.2
				if ( $language->slug != '' ) {
					$alias_val = ( $this->lang_slug_qv_trans ( $language->slug ) == $language->slug ) ? '' : $this->lang_slug_qv_trans ( $language->slug );
					if ( '' == $alias_val )
						$alias_val = substr( $language->slug, 0, 2 );
				} else {
					$alias_val = "";
				}
			?>

			<tr>
				<th scope="row" valign="middle" align="right"><label for="language_alias"><?php _e('Alias','xili-language') ?></label>:&nbsp;</th>
				<td><input name="language_alias" id="language_alias" size="20" type="text" value="<?php echo esc_attr($alias_val) ?>" <?php if($action=='delete') echo 'disabled="disabled"' ?> />  <small>(<?php _e('as visible in query or permalink on front-end.', 'xili-language'); ?>,…)</small>
					<?php
					// used for flush alias refreshing in permalink-class - 2.11
					$listlanguages = get_terms_of_groups_lite ( $this->langs_group_id, TAXOLANGSGROUP, TAXONAME, 'ASC' );
					foreach ( $listlanguages as $one_language ) {
						$one_alias_val = $this->lang_slug_qv_trans ( $one_language->slug );
						if ( $one_language->slug == $language->slug ) {
							echo '<input name="prev_language_alias" id="prev_language_alias" type="hidden" value="'.$one_alias_val.'" />';
						} else {
							echo '<input name="list_language_alias['.$one_language->slug.']" id="list_language_alias['.$one_language->slug.']" type="hidden" value="'.$one_alias_val.'" />';
						}
					}
					?>
				</td>

			</tr>

			<?php } ?>

			<tr>
				<th scope="row" valign="middle" align="right"><label for="language_description"><?php _e('Full name','xili-language') ?></label>:&nbsp;</th>
				<td><input name="language_description" id="language_description" size="20" type="text" value="<?php echo esc_attr($language->description); ?>" <?php if($action=='delete') echo 'disabled="disabled"' ?> />  <small>(<?php _e('as visible in list or menu: english, chinese', 'xili-language'); ?>,…)</small></td>

			</tr>

			<tr>
				<th scope="row" valign="middle" align="right"><label for="language_nicename"><?php _e('Language slug','xili-language') ?></label>:&nbsp;</th>
				<td><input name="language_nicename" id="language_nicename" type="text" value="<?php echo esc_attr($language->slug); ?>" size="10" <?php if( $action=='delete' ) echo 'disabled="disabled"' ?> />
				<?php if ('' != $language->slug ) {
					$cur_locale = GP_Locales::by_field( 'wp_locale', $language->name );
					if ( $cur_locale ) {
						$native = $cur_locale->native_name ;
					} else {
						$cur_locale = GP_Locales::by_slug( $language->slug );
						$native = ( $cur_locale ) ? $cur_locale->native_name . ' *' : '' ;
					}
					if ($native )
						printf ( '&nbsp;&nbsp;&nbsp;&nbsp;<strong>' . __('Native Name: %s','xili-language') . '</strong>' , $native );
					}
					?>
				</td>
			</tr>

			<tr>
				<th scope="row" valign="middle" align="right"><label for="language_order"><?php _e('Order','xili-language') ?></label>:&nbsp;</th>
				<td><input name="language_order" id="language_order" size="3" type="text" value="<?php echo esc_attr($language->term_order); ?>" <?php if( $action=='delete' ) echo 'disabled="disabled"' ?> />&nbsp;&nbsp;&nbsp;<small>
					<label for="language_hidden"><?php _e('hidden','xili-language') ?>&nbsp;<input name="language_hidden" id="language_hidden" type="checkbox" value="hidden" <?php if($action=='delete') echo 'disabled="disabled"' ?> <?php checked( $language_features['hidden'], 'hidden', true); ?> /></label>&nbsp;&nbsp;
					<label for="language_charset"><?php _e('Server Entities Charset:','xili-language') ?>&nbsp;<input name="language_charset" id="language_charset" type="text" value="<?php echo $language_features['charset'] ?>" size="25" <?php if($action=='delete') echo 'disabled="disabled"' ?> /></label></small>

				</td>
			</tr>
			<?php if ( $action=='delete' ) :?>
			<tr>
				<th scope="row" valign="top" align="right"><label for="multilingual_links_erase"><span class="red-alert" ><?php echo $this->admin_messages['alert']['erasing_language']; ?></span></label>&nbsp;:&nbsp;</th>
				<td><input name="multilingual_links_erase" id="multilingual_links_erase" type="checkbox" value="erase" /></td>

			</tr>
			<?php endif; ?>
			<tr>
			<th><p class="submit"><input type="submit" name="reset" value="<?php echo $cancel_text ?>" /></p></th>
			<td>
			<p class="submit"><input type="submit" name="submit" value="<?php echo $submit_text ?>" /></p>
			</td>
			</tr>
		</table>
	<?php
	}

	/**
	 * private functions for admin page : the language example list
	 * @since 1.6.0
	 */
	function example_langs_list( $language_name, $state ) {

		/* reduce list according present languages in today list */
		if ($state != 'delete' && $state != 'edit') {
			$listlanguages = get_terms_of_groups_lite ($this->langs_group_id,TAXOLANGSGROUP,TAXONAME,'ASC');
			foreach ($listlanguages as $language) {
				if ( array_key_exists($language->name, $this->examples_list)) unset ($this->examples_list[$language->name]);
			}
		}
		//
		echo '<option value="">'.__('Choose…','xili-language').'</option>';
		foreach($this->examples_list AS $key=>$value) {
			// $selected = (''!=$language_name && $language_name == $key) ? 'selected=selected' : '';
			$selected = selected( ('' != $language_name && $language_name == $key), true , false );
			echo '<option value="' . $key . '" ' . $selected . '>' . $value . ' (' . $key . ')</option>';
		}
	}

	/**
	 * add styles in options
	 *
	 * @since 2.6
	 *
	 */
	function print_styles_options_language_page ( ) { // first tab

		echo "<!---- xl options css 1 ----->\n";
		echo '<style type="text/css" media="screen">'."\n";
			echo ".red-alert {color:red;}\n";
			echo ".minwidth {min-width:1000px !important ;}\n";
			echo "th.head-id { color:red ; width:60px; }\n";
			echo "th.head-count { text-align: center !important; width: 60px; }\n";
			echo "th.head-action { text-align: center !important; width: 140px; }\n";
			echo ".col-center { text-align: center; }\n";
			echo "th.lang-id { font-size:70% !important; }\n";
			echo "span.lang-flag { display:inline-block; height: 18px; }\n";
			echo ".box { margin:2px; padding:12px 6px; border:1px solid #ccc; } \n";
			echo ".themeinfo {margin:2px 2px 5px; padding:12px 6px; border:1px solid #ccc;} \n";

		//if ( $this->exists_style_ext && ( $this->style_folder == $this->plugin_url) ) {
			if ( $this->style_folder == get_stylesheet_directory_uri() ) {
				$folder_url = $this->style_folder . '/images/flags/' ;
			} else {
				$folder_url = $this->style_folder . '/xili-css/flags/' ;
			}
			$listlanguages = $this->get_listlanguages();
			foreach ($listlanguages as $language) {
				$ok = false;
				$flag_id = $this->get_flag_series ( $language->slug, 'admin' );
				if ( $flag_id != 0 ) {
				    $flag_uri = wp_get_attachment_url( $flag_id ) ;
					$ok = true;
				} else {
					//$flag_uri = $folder_url . $language->slug .'.png';
					$ok = file_exists( $this->style_flag_folder_path . $language->slug .'.png' );
					$flag_uri = $folder_url . $language->slug .'.png';
				}

				if ( $ok && $this->xili_settings['external_xl_style'] == "on" ) {
					echo 'tr.lang-'.$language->slug.' th { background: transparent url('.$flag_uri.') no-repeat 60% center; }'."\n";
				}

			}
		//}
		echo "</style>\n";
		if ( $this->exists_style_ext && $this->xili_settings['external_xl_style'] == "on" ) wp_enqueue_style( 'xili_language_stylesheet' );
	}

	function print_styles_options_language_tabs ( ) {	// the 2 others tabs

		echo "<!---- xl options css 2 to 3 ----->\n";
		echo '<style type="text/css" media="screen">'."\n";
			echo ".red-alert {color:red;}\n";
			echo ".minwidth {min-width:1000px !important ;}\n";
			echo ".fullwidth { width:97%; }\n";
			echo ".width23 { width:70% ; }\n";
			echo ".box { margin:2px; padding:6px 6px; border:1px solid #ccc; } \n";
			echo ".langbox { margin:2px 2px 6px; padding:0px 6px 6px; border-bottom:1px solid #ccc; } \n";
			echo ".langbox h4 { color:#333; } \n";
			echo ".themebox { margin:6px 2px 6px 40px; padding:2px 6px 6px; border:1px solid #ccc; } \n";
			echo ".themebox legend { font-style:italic; } \n";
			echo ".hiddenbox {display:none}\n";
			echo ".rightbox { margin:2px 5px 2px 49%; width:47%;} \n";
			echo ".leftbox {border:0px; width:44%; float:left;} \n";
			echo ".clearb1 {clear:both; height:1px;} \n";
			echo ".themeinfo {margin:2px 2px 5px; padding:12px 6px; border:1px solid #ccc;} \n";

			echo ".list-settings table.form-table {margin-left:3em; margin-bottom:20px !important; width:90%; } \n";
			echo ".list-settings p.section {margin-left:2.5em; } \n";
			echo ".list-settings table.form-table td, .list-settings table.form-table th, .list-settings span.description { font-size:12px;} \n";

			echo ".trans-domain th, .trans-domain td, .trans-domain input, .trans-domain select {font-size:11px !important;} \n"; // overhide .widefat

		echo "</style>\n";

		if ( $this->exists_style_ext && $this->xili_settings['external_xl_style'] == "on" ) wp_enqueue_style( 'xili_language_stylesheet' );
	}

	function print_styles_options_language_support ( ) {

		echo "<!---- xl options css 4 ----->\n";
		echo '<style type="text/css" media="screen">'."\n";
			echo ".red-alert {color:red;}\n";
			echo ".minwidth {min-width:1000px !important;}\n";
			echo ".textleft {text-align:left;}\n";
			echo ".textright {text-align:right;}\n";
			echo ".fullwidth { width:97%; }\n";
			echo ".width23 { width:70% !important; }\n";
			echo ".width45 { width:80% !important; }\n";
			echo ".boldtext {font-size:1.15em;}\n";
			echo ".mailto {margin:2px; padding:12px 100px 12px 30px; border:1px solid #ccc; }\n";
		echo "</style>\n";

		if ( $this->exists_style_ext && $this->xili_settings['external_xl_style'] == "on" ) wp_enqueue_style( 'xili_language_stylesheet' );
	}

	/**
	 * private functions for admin page : the language list
	 * @since 0.9.0
	 *
	 * @update 0.9.5 : two default languages if taxonomy languages is empty
	 * @update 1.8.8 : fixes slug of defaults
	 * @update 1.8.9.1 : visible = *
	 * @updated 2.6 : style
	 * @updated 2.7.1 : default full name
	 * @updated 2.11.2 : detailled counter in title
	 */
	function available_languages_row() {
		global $wpdb;
		$default = 0;
		/*list of languages*/
		$listlanguages = get_terms_of_groups_lite ( $this->langs_group_id, TAXOLANGSGROUP, TAXONAME, 'ASC' );
		if ( empty($listlanguages) ) {
			$cleaned = apply_filters( 'clean_previous_languages_list', false ); // 2.20.3
			if ( $cleaned ) {
				$listlanguages = get_terms_of_groups_lite ( $this->langs_group_id, TAXOLANGSGROUP, TAXONAME, 'ASC' );
			}
		}
		if ( empty($listlanguages) ) {
			$listlanguages = $this->create_default_languages_list ( '_add' ); // 2.20.3
			$default = 1;
		}

		if ( !empty($default) || !empty($cleaned) || !empty($this->xili_settings['pll_cleaned']) ) {
			$click = '';
			$message = '';


				$messages = apply_filters ( 'previous_install_list_messages', array(), count( $listlanguages ) );
				$message = $messages['message'];
				$click = $messages['click'];


			if ( $default ) $message = sprintf(__('A new list of %s languages by default has just been created !', 'xili-language'), count ( $listlanguages ) );

			if ( $message ) {
				$line = '<tr>'
				.'<th scope="row" class="lang-id" >'. '<img src="'. includes_url( 'images/smilies/icon_exclaim.gif') . '" alt="Caution" />' . '&nbsp;&nbsp;' . __('CAUTION', 'xili-language') .  '</th>'
				.'<td class="col-center" colspan="5" ><strong class="red-alert">'. $message . $click . '</strong></td>'
				.'<td class="col-center" colspan="5" >'. __('Complete and modify the list according your multilingual need...', 'xili-language') . '</td>'
				. '</tr>';
				echo $line;
				$line = '<tr>'
				.'<th scope="row" class="lang-id" ></th>'
				.'<td class="col-center" colspan="10" ><hr/></td>'
				. '</tr>';
				echo $line;
			}
		}
		if ( count ( $listlanguages ) == 1 ) {

			$line = '<tr>'
			.'<th scope="row" class="lang-id" >'. '<img src="'. includes_url( 'images/smilies/icon_exclaim.gif') . '" alt="Caution" />' . '&nbsp;&nbsp;' . __('CAUTION', 'xili-language') .  '</th>'
			.'<td class="col-center" colspan="5" ><strong class="red-alert">'. __('Only one language remains in the list !', 'xili-language') . '</strong></td>'
			.'<td class="col-center" colspan="5" >'. __('If you don’t need it, add another language required before deletion !', 'xili-language') . '</td>'
			. '</tr>';
			echo $line;
			$line = '<tr>'
			.'<th scope="row" class="lang-id" ></th>'
			.'<td class="col-center" colspan="10" ><hr/></td>'
			. '</tr>';
			echo $line;
		}
		$trclass = '';
		foreach ($listlanguages as $language) {

			$trclass = ((defined('DOING_AJAX') && DOING_AJAX) || ' alternate' == $trclass ) ? '' : ' alternate';
			$language->count = number_format_i18n( $language->count );
			// count for each CPT
			$counts = array();
			$title = array();
			$custompoststype = $this->authorized_custom_post_type( true ); // 2.13.2 b

			foreach ( $custompoststype as $key => $customtype ) {
				$counts[$key] = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts WHERE $wpdb->posts.ID = $wpdb->term_relationships.object_id AND post_status = 'publish' AND term_taxonomy_id = %d AND post_type = %s", $language->term_id, $key ) );
				$title[] = $customtype['name'] .' = ' . $counts[$key];
			}
			$title = implode (' | ', $title );
			$posts_count = ( $language->count > 0 ) ? '<a title= "'.$title.'" href="edit.php?lang='.$language->slug.'">'.$language->count . '</a>' : $language->count;

			/* edit link*/
			// nonce added
			$link = wp_nonce_url( "?action=edit&amp;page=language_page&amp;term_id=".$language->term_id, "edit-".$language->term_id );

			$edit = "<a href='".$link."' >".__( 'Edit' )."</a>&nbsp;|";
			/* delete link*/
			// nonce added
			$link = wp_nonce_url( "?action=delete&amp;page=language_page&amp;term_id=".$language->term_id, "delete-".$language->term_id );

			$edit .= "&nbsp;<a href='".$link."' class='delete'>".__( 'Delete' )."</a>";

			$h = ( isset ( $this->xili_settings['lang_features'][$language->slug]['hidden'] ) && $this->xili_settings['lang_features'][$language->slug]['hidden'] == 'hidden') ? "&nbsp;" : "&#10004;";
			$h .= ( isset ( $this->xili_settings['lang_features'][$language->slug]['charset'] ) && $this->xili_settings['lang_features'][$language->slug]['charset'] != '') ? "&nbsp;+" : "";

			$is_mo = ! empty( $language->name ) && array_key_exists( $language->name, (array) $this->available_languages() );

			$mo_available_for_dashboard = ( $is_mo ) ? "&#10004;" : "";

			$line = '<tr id="lang-'.$language->term_id.'" class="lang-'. $language->slug . $trclass . '" >'
			.'<th scope="row" class="lang-id" ><span class="lang-flag">'.$language->term_id.'<span></th>'
			.'<td>' . $language->name . '</td>';

			$cur_locale = GP_Locales::by_field( 'wp_locale', $language->name );
			if ( $cur_locale ) {
				$native = $cur_locale->native_name ;
			} else {
				$cur_locale = GP_Locales::by_slug( $language->slug );
				$native = ( $cur_locale ) ? $cur_locale->native_name . ' *' : '' ;
			}

			if ( $this->alias_mode ) {
				$alias_val = ( $this->lang_slug_qv_trans ( $language->slug ) == $language->slug && 2 != strlen($language->slug)) ? ' ? ' : $this->lang_slug_qv_trans ( $language->slug );

				$key_slug = array_keys ( $this->langs_slug_shortqv_array, $alias_val ) ;

				if ( count ( $key_slug ) == 1 ) {
					$line .= '<td>' . $alias_val . '</td>';
				} else {
					$line .= sprintf( '<td><span class="red-alert" title="%s">', esc_attr('the default alias needs to be defined', 'xili-language' ) ) . $alias_val . '</span></td>';
				}
			}

			$line .= '<td>' . $language->description . '</td>'
			.'<td>' . $native . '</td>'
			.'<td>' . $language->slug . '</td>'
			.'<td>' . $language->term_order . '</td>'
			.'<td class="col-center" >'. $h . '</td>'
			.'<td class="col-center" >'. $mo_available_for_dashboard . '</td>'
			.'<td class="col-center" >' . $posts_count . '</td>'
			.'<td class="col-center" >'. $edit . "</td>\n\t</tr>\n";

			echo $line;

		}

	}

	/**
	 * Recursive search of files in a path
	 * @since 1.1.9
	 * @update 1.2.1 - 1.8.5
	 *
	 */
	function find_files( $path, $pattern, $callback, $type = false ) {


		$matches = Array();
		$entries = Array();
		$dir = dir($path);

		while (false !== ($entry = $dir->read())) {
			$entries[] = $entry;
		}
		$dir->close();
		foreach ($entries as $entry) {
			$fullname = $path .$this->ossep. $entry;
			if ($entry != '.' && $entry != '..' && is_dir($fullname)) {
				$this->find_files($fullname, $pattern, $callback, $type );
			} else if (is_file($fullname) && preg_match($pattern, $entry)) {
				call_user_func( $callback, $path, $entry, $type );
			}
		}

	}

	/**
	 * display lines of files in special sidebox
	 * @since 1.1.9
	 *
	 * @updated 1.8.8, 2.16.0
	 */
	function available_mo_files( $path , $filename, $wp_lang_dir = false ) {
		$shortfilename = str_replace(".mo","",$filename );
		$alert = '<span class="red-alert">'.__('Uncommon filename','xili-language').'</span>' ;
		if ( $wp_lang_dir ) {

			$message = '<em>'.__('in WP_LANG_DIR/themes', 'xili-language').'</em>'; // not used

		} else {
			if ( !in_array ( strlen($shortfilename), array ( 2, 3, 5, 6 ) ) ) { // 2.19.3 for file like kab.mo or haw_US.mo
				if ( false === strpos( $shortfilename, 'local-' ) ) {
					$message = $alert;
				} else {
					$message = '<em>'.__("Site's values",'xili-language').'</em>';
				}

			} else if ( false === strpos( $shortfilename, '_' ) && in_array ( strlen($shortfilename) , array ( 5, 6 ) ) ) {
				$message = $alert;
			} else {
				$message = '';
			}
		}

		if ( !is_child_theme() ) {
			$theme_directory = $this->get_template_directory;
		} elseif ( is_child_theme() ) {
			if ( false === strpos ( $path, $this->get_parent_theme_directory . '/' ) ) { // / to avoid -xili
				$theme_directory = $this->get_template_directory; // show child
			} else {
				$theme_directory = $this->get_parent_theme_directory; // show parent
			}
		}
		if ( $wp_lang_dir ) {
			echo $shortfilename ."<br />";
		} else {
			echo $shortfilename. " (".$this->ossep.str_replace($this->ossep,"",str_replace( $theme_directory, '', $path )).") ".$message."<br />";
		}

	}


	/********************************** Edit Post UI ***********************************/

	/**
	 * style for new dashboard
	 * @since 2.5
	 * @updated 2.6
	 */
	function admin_init () {
				// test successively style file in theme, plugins, current plugin subfolder
		if ( file_exists ( get_stylesheet_directory().'/xili-css/xl-style.css' ) ) { // in child theme
				$this->exists_style_ext = true;
				$this->style_folder = get_stylesheet_directory_uri();
				$this->style_flag_folder_path = get_stylesheet_directory () . '/images/flags/';
				$this->style_message = __( 'xl-style.css is in sub-folder <em>xili-css</em> of current theme folder', 'xili-language' );
		} elseif ( file_exists( WP_PLUGIN_DIR . $this->xilidev_folder . '/xili-css/xl-style.css' ) ) { // in plugin xilidev-libraries
				$this->exists_style_ext = true;
				$this->style_folder = plugins_url() . $this->xilidev_folder;
				$this->style_flag_folder_path = WP_PLUGIN_DIR . $this->xilidev_folder . '/xili-css/flags/' ;
				$this->style_message = sprintf( __( 'xl-style.css is in sub-folder <em>xili-css</em> of %s folder', 'xili-language' ), $this->style_folder );
		} elseif ( file_exists ( $this->plugin_path.'xili-css/xl-style.css' ) ) { // in current plugin
				$this->exists_style_ext = true;
				$this->style_folder = $this->plugin_url ;
				$this->style_flag_folder_path = $this->plugin_path . 'xili-css/flags/' ;
				$this->style_message = __( 'xl-style.css is in sub-folder <em>xili-css</em> of xili-language plugin folder (example)', 'xili-language' );
		} else {
				$this->style_message = __( 'no xl-style.css', 'xili-language' );
		}
		// build now default style
		if ( $this->exists_style_ext && ( $this->style_folder != $this->plugin_url) ) wp_register_style( 'xili_language_stylesheet', $this->style_folder . '/xili-css/xl-style.css' );
	}



	/**
	 * Add Translations Dashboard in post edit screen
	 *
	 * @since 2.5
	 *
	 */
	function add_custom_box_in_post_edit () {

		$custompoststype = $this->authorized_custom_post_type();

		foreach ( $custompoststype as $key => $customtype ) {
			if ( $customtype['multilingual'] == 'enable' ) {
				$plural_name = ( isset( $customtype['name'] ) ) ? $customtype['name'] : $key ;
				$singular_name = ( isset( $customtype['singular_name'] ) ) ? $customtype['singular_name'] : $key ;
				add_meta_box( 'post_state', sprintf(__("%s of this %s",'xili-language'), __('Translations', 'xili-language'), $singular_name ), array(&$this,'post_state_box'), $key, 'normal', 'high' );
			}
		}
	}

	/**
	 * Display content and parts of translations dashboard metabox
	 *
	 * @since 2.5
	 *
	 */
	function post_state_box () {
		global $post_ID ;
		?>
		<div id="msg-states">
			<?php $curlang = $this->post_translation_display ( $post_ID ); ?>
		</div>
		<div id="msg-states-comments">
			<?php $this->post_status_addons ( $post_ID, $curlang ); ?>
			<p class="docinfos" ><?php printf(__( 'This list gathers together the titles and infos about (now and future) linked posts by language. For more info, visit the <a href="%s">wiki</a> website.', 'xili-language' ), $this->wikilink) ; ?></p>
			<p class="xlversion">©xili-language v. <?php echo XILILANGUAGE_VER; ?></p>
		</div>
		<div class="clearb1">&nbsp;</div>
	<?php
	}

	/**
	 * Display main part and list of translation dashboard metabox
	 *
	 * @since 2.5
	 *
	 */
	function post_translation_display ( $post_ID ) {
		global $post ;
		$postlang = '';
		$test = ($post->post_status == 'auto-draft') ? false : true ;
		if ($test === true){
			$postlang = $this->get_post_language( $post_ID );
		} else {
			$postlang = ""; /* new post */
		}

		$listlanguages = get_terms_of_groups_lite ( $this->langs_group_id, TAXOLANGSGROUP, TAXONAME, 'ASC');

		if ( $this->xili_settings['authorbrowseroption'] == 'authorbrowser' ) { // setting = select language of author's browser
			$listofprefs = $this->the_preferred_languages();
			if ( is_array( $listofprefs ) ) {
				arsort($listofprefs, SORT_NUMERIC);
				$sitelanguage = $this->match_languages ( $listofprefs, $listlanguages );
				if ( $sitelanguage ) {
					$defaultlanguage = $sitelanguage->slug;
				} else {
					$defaultlanguage = "";
				}
				$mention = __('Your browser language preset by default for this new post...', 'xili-language') ;
			} else {
				$defaultlanguage = ""; /* undefined */
			}
		} elseif ( $this->xili_settings['authorbrowseroption'] == 'authordashboard' ) {
			$current_dash_lang = strtolower( $this->admin_side_locale() );
			if ( isset( $this->langs_slug_name_array[$current_dash_lang]) ) {
				$defaultlanguage = $current_dash_lang;
				$mention = __('Your dashboard language preset by default for this new post...', 'xili-language') ;
			} else {
				$defaultlanguage = ""; /* undefined */
			}
		} else {
			$defaultlanguage = ""; /* undefined */
			$mention = "";
		}
		$this->authorbrowserlanguage = $defaultlanguage; // for right box

		if ( isset ($_GET['xlaction'] ) && isset ($_GET['xllang']) ) {
			// create new translation
			$targetlang = $_GET['xllang'];
			if ( $_GET['xlaction'] == 'transcreate' )
				$translated_post_ID = $this->create_initial_translation ( $targetlang, $post->post_title , $postlang, $post_ID );

				if ( $translated_post_ID > 0 && $this->xili_settings['creation_redirect'] == 'redirect') {
					$url_redir = admin_url().'post.php?post='.$translated_post_ID.'&action=edit';

				?>
	<script type="text/javascript">
	<!--
	window.location= <?php echo "'" . $url_redir . "'"; ?>;
	//-->
	</script>
<?php
				}
		} //elseif ( isset ($_GET['xlaction'] ) && $_GET['xlaction'] == 'refresh' ) {
		if ( $postlang != "" ) {	// refresh only if defined
			foreach ( $listlanguages as $language ) {
				if ( $language->slug != $postlang ) {
					$otherpost = $this->linked_post_in( $post_ID, $language->slug ) ;
					if ( $otherpost ) {
						$linepost = $this->temp_get_post ( $otherpost );
						if ( $linepost && $otherpost != $post_ID) {
							// search metas of target
							$metacurlang = $this->get_cur_language( $linepost->ID ) ; // array
							foreach ( $listlanguages as $metalanguage ) {
								if ( $metalanguage->slug != $postlang && $metalanguage->slug != $metacurlang[QUETAG] ) {
									$id = get_post_meta( $linepost->ID, QUETAG.'-'.$metalanguage->slug, true );
									$locid = get_post_meta( $post_ID, QUETAG.'-'.$metalanguage->slug, true ); // do not erase
									if ( $id != "" && $locid =='' && $id != $post_ID ) {
										update_post_meta( $post_ID, QUETAG.'-'.$metalanguage->slug, $id );
									}
								}
								if ( $metalanguage->slug == $postlang ) {
									update_post_meta( $linepost->ID, QUETAG.'-'.$metalanguage->slug, $post_ID );
								}
							}

						} else {
							delete_post_meta ( $post_ID, QUETAG.'-'.$language->slug );
						}
					}
				}
			} // for
		}
		if ( isset ($_GET['xlaction'] ) && $_GET['xlaction'] == 'propataxo' ) {
			$this->propagate_categories_to_linked ( $post_ID, $postlang );
		}


		$post_type = $post->post_type ;

		$post_type_object = get_post_type_object( $post_type );

		$i = 0;
		// table of languages - asc sorted
		?>
		<table id="postslist" class="widefat">
		<thead>
		<tr><th class="language" ><?php _e('Language','xili-language'); ?></th><th class="postid"><?php _e('ID', 'xili-language'); ?></th><th class="title"><?php _e('Title','xili-language'); ?></th><th class="status" ><?php _e('Status'); ?></th><th class="action" ><?php _e('Edit'); ?></th></tr>
		</thead>
		<tbody id='the-linked' class='postsbody'>
		<?php
		foreach ( $listlanguages as $language ) {
			$otherpost = $this->linked_post_in( $post_ID, $language->slug );

			$checkpostlang = ( ''!= $postlang ) ? $postlang : $defaultlanguage ; // according author language
			$checked = checked( $checkpostlang, $language->slug, false );

			$creation_edit = ( $this->xili_settings['creation_redirect'] == 'redirect' ) ? __('Create and edit', 'xili-language') : __('Create', 'xili-language');

			$tr_class = ' class="lang-'.$language->slug.'" ';

			$language_name = '<span class="lang-iso"><abbr class="abbr_name" title="'.$language->description.'">'.$language->name.'</abbr></span>';

			$checkline = '<label title="'.$language->description.'" class="checklang" for="xili_language_check_'.$language->slug.'" class="selectit"></label><input id="xili_language_check_'.$language->slug.'" title="'.$language->description.'" name="xili_language_set" type="radio" value="'.$language->slug .'"  '. $checked.' />&nbsp;&nbsp;'.$language_name ;

			$hiddeninput = '<input class="inputid" id="xili_language_'.QUETAG.'-'.$language->slug .'" name="xili_language_'.QUETAG.'-'.$language->slug.'" value="" /><input type="hidden" name="xili_language_rec_'.QUETAG.'-'.$language->slug.'" value=""/>';

			if ( $otherpost && $language->slug != $postlang ) {
				$linepost = $this->temp_get_post ( $otherpost );
				$display_link = sprintf('<a href="%s" title="%s" target="_blank" >' . $otherpost . '</a>', get_permalink($otherpost), esc_attr__('Display this post', 'xili-language') ); // 2.18.2

				if ( $linepost ) {

					if ( $linepost->post_status == 'trash' ) {

						$edit = __( 'uneditable', 'xili-language' );
					} else {
						$edit = sprintf( ' <a href="%s" title="link to:%d">%s</a> ', 'post.php?post=' . $otherpost . '&action=edit', $otherpost, __('Edit') );
					}

					echo '<tr'.$tr_class.'><th title="'.$language->description.'" >&nbsp;'.$language_name .'</th><td>'.$display_link.'</td><td>'.$linepost->post_title

					.'</td><td>';

					switch ( $linepost->post_status ) {
						case 'private':
							_e('Privately Published');
							break;
						case 'publish':
							_e('Published');
							break;
						case 'future':
							_e('Scheduled');
							break;
						case 'pending':
							_e('Pending Review');
							break;
						case 'trash':
							_ex('Trash' ,'post');
							break;
						case 'draft':
						case 'auto-draft':
							_e('Draft');
							break;
					}

					echo '</td><td>'
					. $edit
					.'</td></tr>';

				} else {
					// delete post_meta - not target post
					delete_post_meta ( $post_ID, QUETAG.'-'.$language->slug );
					$search = '<a class="hide-if-no-js" onclick="findPosts.open( \'lang[]\',\''.$language->slug.'\' );return false;" href="#the-list" title="'.esc_attr__( 'Search linked post', 'xili-language' ).'"> '.__( 'Search', 'xili-language' ).'</a>';

					echo '<tr'.$tr_class.'><th>'.$checkline.'</th><td>'. $hiddeninput.' </td><td>'.__('not yet translated', 'xili-language')
						.'&nbsp;&nbsp;'.sprintf( '<a href="%s" title="%s">'.$creation_edit.'</a>', 'post.php?post='.$post_ID.'&action=edit&xlaction=transcreate&xllang='.$language->slug, sprintf(esc_attr__('For create a linked draft translation in %s', 'xili-language'), $language->name )  ). '&nbsp;|&nbsp;'.  $search
						.'</td><td>&nbsp;</td><td>'. $search
						. '&nbsp;'
						. '</td></tr>';

				}

			} elseif ( $language->slug == $postlang) {


				echo '<tr class="editing lang-'.$language->slug.'" ><th>'.$checkline.'</th><td>'.$post_ID.'</td><td>'
				.$post->post_title
				.'</td><td>';
				switch ( $post->post_status ) {
						case 'private':
							_e('Privately Published');
							break;
							case 'publish':
								_e('Published');
								break;
							case 'future':
								_e('Scheduled');
								break;
							case 'pending':
								_e('Pending Review');
								break;
							case 'trash':
								_e('Trash');
								break;
							case 'draft':
							case 'auto-draft':
								_e('Draft');
								break;
					}

				echo '</td><td>&nbsp;</td></tr>';

			} else { // no linked post

				if ( in_array( $post->post_status, array ( 'draft', 'pending', 'future', 'publish', 'private' ) ) && $postlang != '' ) {

					$search = '<a class="hide-if-no-js" onclick="findPosts.open( \'lang[]\',\''.$language->slug.'\' );return false;" href="#the-list" title="'.esc_attr__( 'Search linked post', 'xili-language' ).'"> '.__( 'Search' ).'</a>';

					echo '<tr'.$tr_class.'><th>'.$checkline.'</th><td>' . $hiddeninput .'</td><td>'
					. sprintf(__('not yet translated in %s', 'xili-language'), $language->description )
					.'&nbsp;&nbsp;'.sprintf( '<a href="%s" title="%s">'. $creation_edit .'</a>', 'post.php?post='.$post_ID.'&action=edit&xlaction=transcreate&xllang='.$language->slug, sprintf(__('For create a linked draft translation in %s', 'xili-language'), $language->name )  ).'&nbsp;|&nbsp;'.  $search
					.'</td><td>&nbsp;</td><td>'
					. '&nbsp;'
					. '</td></tr>';

				} else {

					if ( $defaultlanguage != '' && $defaultlanguage == $language->slug ) {
						// if post-new.php and pre-checked for author's brother
						$the_message = $mention;
						$the_class = ' class="editing lang-'.$defaultlanguage.'"';

					} else {
							$the_message = sprintf(__('select language %s !', 'xili-language'), $language->description );
							$the_class = $tr_class;
					}

					echo '<tr'.$the_class.'><th>'.$checkline.'</th><td>&nbsp;</td><td>'
						. '<p class="message" ><––––– '.$the_message.'</p>'
						.'</td><td>&nbsp;</td><td>'
						.'&nbsp'
						. '</td></tr>';
				}
			}
		}
		?>
		</tbody>
		</table>
		<div id="ajax-response"></div>
				<?php
				// ajax form
					$this->xili_find_posts_div('', $post_type, $post_type_object->label);
				?>
		<?php
		return $postlang ;
	}

	/**
	 * Display right part of translations dashboard
	 *
	 * @since 2.5
	 *
	 */
	function post_status_addons ( $post_ID, $curlang ) {
		$notundefinedlang = ( $curlang != "" ) ? $curlang : $this->authorbrowserlanguage; // set in left box
		$un_id = ( $curlang == "" ) ? '&nbsp;('. $post_ID .')' : '';
		$refresh = sprintf( '<a href="%s" title="%s">%s</a> ', 'post.php?post='.$post_ID.'&action=edit&xlaction=refresh', esc_attr__('Refresh links series', 'xili-language'), __('Refresh links', 'xili-language') );
		?>
		<p><?php echo $refresh; ?>
		<?php if ( '' != $curlang && current_user_can ('xili_language_clone_tax') && is_object_in_taxonomy( get_post_type($post_ID), 'category') ) { //2.6.3
			printf( '&nbsp|&nbsp;<a href="%s" title="%s">%s</a> ', 'post.php?post='.$post_ID.'&action=edit&xlaction=propataxo', esc_attr__('Propagate categories', 'xili-language'), __('Propagate categories', 'xili-language') );
		} ?></p>
		<label for="xili_language_check" class="selectit"><?php _e( 'set post to:', 'xili-language') ?>&nbsp;<input id="xili_language_check" name="xili_language_set" type="radio" value="undefined" <?php checked( $notundefinedlang, "", true ); ?> />&nbsp;<?php _e('undefined','xili-language'); echo $un_id; ?></label>
		<?php
	}

	function propagate_categories_to_linked ( $post_ID, $curlang ) {

		$listlanguages = $this->get_listlanguages();
		foreach ( $listlanguages as $language ) {
			if ( $language->slug != $curlang ) {
				// get to post
				$otherpost = $this->linked_post_in( $post_ID, $language->slug ) ;
				if ( $otherpost ) {
					$this->propagate_categories ( $post_ID, $otherpost, 'erase' );
				}
			}
		}
	}

	/**
	 * scripts for findposts only in post-new and post
	 * @since 2.2.2
	 */
	function find_post_script () {
		global $post ;
		if ( get_post_type($post->ID) != 'attachment' ) {
			wp_enqueue_script( 'wp-ajax-response' );
			wp_enqueue_script( 'jquery-ui-draggable' );
			$suffix = defined( 'WP_DEBUG') && WP_DEBUG ? '.dev' : '.min'; // 2.8.8
			wp_enqueue_script( 'xili-find-post', plugin_dir_url ( $this->file_file ) . 'js/xili-findposts'.$suffix.'.js','' , XILILANGUAGE_VER );
		}
	}


	function wp_ajax_find_post_types() {
		//global $wp_version ;

		check_ajax_referer( 'find-post-types' );

		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		unset( $post_types['attachment'] );

		$s = wp_unslash( $_POST['ps'] );
		$searchand = $search = '';
		$args = array(
			'post_type' => array($_POST['post_type']), //array_keys( $post_types ),
			'post_status' => 'any',
			'posts_per_page' => 50,
		);
		if ( '' !== $s )
			$args['s'] = $s;

		$posts = get_posts( $args );

		if ( ! $posts )
			wp_die( __('No items found.') );


		$html = '<table class="widefat" cellspacing="0"><thead><tr><th class="found-radio"><br /></th><th>'.__('Title').'</th><th class="no-break">'.__('Type').'</th><th class="no-break">'.__('Date').'</th><th class="no-break">'.__('Status').'</th></tr></thead><tbody>';
		foreach ( $posts as $post ) {
			$title = trim( $post->post_title ) ? $post->post_title : __( '(no title)' );

			switch ( $post->post_status ) {
				case 'publish' :
				case 'private' :
					$stat = __('Published');
					break;
				case 'future' :
					$stat = __('Scheduled');
					break;
				case 'pending' :
					$stat = __('Pending Review');
					break;
				case 'draft' :
					$stat = __('Draft');
					break;
			}

			if ( '0000-00-00 00:00:00' == $post->post_date ) {
				$time = '';
			} else {
				/* translators: date format in table columns, see http://php.net/date */
				$time = mysql2date(__('Y/m/d'), $post->post_date);
			}

			$html .= '<tr class="found-posts"><td class="found-radio"><input type="radio" id="found-'.$post->ID.'" name="found_post_id" value="' . esc_attr($post->ID) . '"></td>';
			$html .= '<td><label for="found-'.$post->ID.'">' . esc_html( $title ) . '</label></td><td class="no-break">' . esc_html( $post_types[$post->post_type]->labels->singular_name ) . '</td><td class="no-break">'.esc_html( $time ) . '</td><td class="no-break">' . esc_html( $stat ). ' </td></tr>' . "\n\n";
		}

		$html .= '</tbody></table>';
		wp_send_json_success( $html );
	}




	/**
	 * add styles in edit msg screen
	 *
	 * @since 2.5
	 *
	 */
	function print_styles_cpt_edit ( ) {
		global $post;

		$custompoststype = $this->authorized_custom_post_type();
		$custompoststype_keys = array_keys ( $custompoststype );
		$type = get_post_type( $post->ID );
		if ( in_array ( $type , $custompoststype_keys ) && $custompoststype[$type]['multilingual'] == 'enable' ){
			$insert_flags = ( $this->xili_settings['external_xl_style'] == "on" );
			echo '<!---- xl css ----->'."\n";
			echo '<style type="text/css" media="screen">'."\n";
			echo '#msg-states { width:79%; float:left; overflow:hidden; }'."\n";
			echo '#msg-states-comments { width:18.5%; margin-left: 80%; border-left:0px #666 solid; padding:10px 10px 0; }'."\n";
			echo ".clearb1 {clear:both; height:1px;} \n"; // 2.8.8

			echo '.xlversion {font-size:80%; margin-top:20px; text-align:right;}';

			echo '.alert { color:red;}'."\n";
			echo '.message { font-size:80%; color:#bbb !important; font-style:italic; }'."\n";
			echo '.editing { color:#333; background:#fffbcc;}'."\n";
			echo '.abbr_name:hover {border-bottom:1px dotted grey;}'."\n";
			echo '#postslist {width: 100%; border:1px solid grey ;}'."\n";

			echo '.language {width: 80px;}'."\n";
			echo '.postid {width: 35px;}'."\n";

			echo '.status {width: 60px;}'."\n";
			echo '.action {width: 120px;}'."\n";

			echo '.inputid {width: 55px; font-size:90%}'."\n";

			$flag_uri = $this->flag_in_title_input ( $post->ID, $insert_flags ); // 2.18.1
			if ( $flag_uri ) {
				echo '#titlewrap input {background-image : url('. $flag_uri . '); background-position :98.5% center; background-repeat : no-repeat; }'."\n";
			}

			echo '.postsbody tr > th span { display:inline-block; height: 20px; }'."\n";
			$listlanguages = $this->get_listlanguages();
			if ( $this->style_folder == get_stylesheet_directory_uri() ) {
				$folder_url = $this->style_folder . '/images/flags/' ;
			} else {
				$folder_url = $this->style_folder . '/xili-css/flags/' ;
			}
			foreach ($listlanguages as $language) {
				$ok = false;
				$flag_id = $this->get_flag_series ( $language->slug, 'admin' );
				if ( $flag_id != 0 ) {
				    $flag_uri = wp_get_attachment_url( $flag_id ) ;
					$ok = true;
				} else {
					//$flag_uri = $folder_url . $language->slug .'.png';
					$ok = file_exists( $this->style_flag_folder_path . $language->slug .'.png' );
					$flag_uri = $folder_url . $language->slug .'.png';
				}

				if ( $insert_flags && $ok ) {
					echo '.postsbody tr.lang-'. $language->slug .' > th span { display:inline-block; text-indent:-9999px ; height: 20px; }'."\n";
					echo 'tr.lang-'.$language->slug.' th { background: transparent url('.$flag_uri.') no-repeat 60% center; }'."\n";
				}

			}

			echo '</style>'."\n";

			if ( $this->exists_style_ext && $insert_flags ) wp_enqueue_style( 'xili_language_stylesheet' );

		} else if ( $type == 'attachment' ) { // 2.18.1
			$insert_flags = ( $this->xili_settings['external_xl_style'] == "on" );
			echo '<!---- xl css ----->'."\n";
			echo '<style type="text/css" media="screen">'."\n";
			$flag_uri = $this->flag_in_title_input ( $post->ID, $insert_flags );
			if ( $flag_uri ) {
				echo '#titlewrap input {background-image : url('. $flag_uri . '); background-position :98.5% center; background-repeat : no-repeat; }'."\n";
				echo '#attachment_caption, #attachment_alt, #attachment_content {background-image : url('. $flag_uri . '); background-position :98.5% center; background-repeat : no-repeat; }'."\n";
			}
			echo '</style>'."\n";
		}
	}

	// used in cpt and attachment
	function flag_in_title_input ( $post_id, $insert_flags ) {
		$lang = $this->get_post_language( $post_id ) ; //slug
		if ( $this->style_folder == get_stylesheet_directory_uri() ) {
			$folder_url = $this->style_folder . '/images/flags/' ;
		} else {
			$folder_url = $this->style_folder . '/xili-css/flags/' ;
		}
		$flag_id = $this->get_flag_series ( $lang, 'admin' );
		if ( $flag_id != 0 ) {
			$flag_uri = wp_get_attachment_url( $flag_id ) ;
			$ok = true;
		} else {
			$flag_uri = $folder_url . $lang .'.png';
			$ok = file_exists( $this->style_flag_folder_path . $lang .'.png' );
		}
		if ( $lang && $insert_flags && $ok ) {
			return $flag_uri;
		} else {
			return false;
		}
	}

	/**
	 * Hide language post_meta link
	 * from apply_filters( 'is_protected_meta', $protected, $meta_key, $meta_type );
	 *
	 * @since 2.5
	 */
	function hide_lang_post_meta ( $protected, $meta_key, $meta_type ) {
		if ( $meta_type == 'post' && QUETAG.'-' == substr( $meta_key, 0, strlen(QUETAG) + 1 ) ) {
			$protected = true;
		}
		return $protected;
	}

	/**
	 * test of tracs http://core.trac.wordpress.org/ticket/18979#comment:2
	 */

	function hide_lang_post_meta_popup ( $keys, $limit = 10 ) {
		global $wpdb, $post;
		$q = "SELECT meta_key FROM $wpdb->postmeta";
		$post_type = get_post_type ( $post->ID );
		if ( ! empty( $post_type ) )
			$q .= $wpdb->prepare( " INNER JOIN $wpdb->posts ON post_id = ID WHERE post_type LIKE %s", $post_type );

		$q .= " GROUP BY meta_key HAVING ( meta_key NOT LIKE '\_%' AND meta_key NOT LIKE '" . QUETAG . "-%' ) ORDER BY meta_key LIMIT $limit";
		$keys = $wpdb->get_col( $q );
		//$keys = apply_filters( 'postmeta_form_keys', $keys, $post_type );
		if ( $keys )
			natcasesort($keys);
		return $keys;
	}

	/**
	 * set language when post or page is saved or changed
	 *
	 * @since 0.9.0
	 * @completed 0.9.7.1 to record postmeta of linked posts in other languages
	 * @updated 0.9.7.5 to delete relationship when undefined
	 * @updated 0.9.9 to avoid delete relationship when in quick_edit
	 * @updated 1.3.0 to avoid delete relationship when trashing - 1.4.1 - create post-meta xl-search-linked
	 * @updated 1.8.9.3 for bulk edit...
	 * @updated 2.5, 2.6
	 * @updated 2.9.10 bulk edit via ajax
	 *
	 * @param $post_ID
	 */
	function xili_language_add( $post_ID, $post ) {

		$posttypes = array_keys( $this->xili_settings['multilingual_custom_post'] );
		$posttypes[] = 'post';
		$posttypes[] = 'page';
		$thetype = $post->post_type;
		if ( in_array ( $thetype, $posttypes ) ) {

			$listlanguages = $this->get_listlanguages () ;
			$previous_lang = $this->get_post_language ( $post_ID ) ;

			if ( isset($_POST['_inline_edit']) ) { /* when in quick_edit (edit.php) */
				$sellang = $_POST['xlpop'];
				if ( "" != $sellang ) {

					if ( $sellang != $previous_lang ) {
						// move a language
						// clean linked targets
						foreach ($listlanguages as $language) {

							$target_id = get_post_meta( $post_ID, QUETAG.'-'.$language->slug, true );
							if ( $target_id != "" ) {
								if ( $previous_lang != '' ) delete_post_meta( $target_id, QUETAG.'-'.$previous_lang );
								update_post_meta( $target_id, QUETAG.'-'.$sellang, $post_ID );
							}
						}
						wp_delete_object_term_relationships( $post_ID, TAXONAME );
					}

					$return = wp_set_object_terms( $post_ID, $sellang, TAXONAME );

				} else { // undefined
					if ( !isset ( $_GET['action'] ) ) { // trash - edit

						// clean linked targets
						foreach ($listlanguages as $language) {
							//delete_post_meta( $post_ID, QUETAG.'-'.$language->slug ); // erase translated because undefined
							$target_id = get_post_meta( $post_ID, QUETAG.'-'.$language->slug, true );
							if ( $target_id != "" ) {
								delete_post_meta( $target_id, QUETAG.'-'.$previous_lang );
							}
						}

						wp_delete_object_term_relationships( $post_ID, TAXONAME );

					}
				}

			// bulk-edit via ajax 2.9.10
			} else if ( isset($_GET['bulk_edit']) ) {
				return;

			} else {
				// post-edit single
				$sellang = ( isset ( $_POST['xili_language_set'] )) ? $_POST['xili_language_set'] : "" ;
				if ( "" != $sellang && "undefined" != $sellang ) {
					if ( $sellang != $previous_lang && $previous_lang != '' ) {
						// move a language
						// clean linked targets
						foreach ($listlanguages as $language) {

							$target_id = get_post_meta( $post_ID, QUETAG.'-'.$language->slug, true );
							if ( $target_id != "" ) {
								delete_post_meta( $target_id, QUETAG.'-'.$previous_lang );
								update_post_meta( $target_id, QUETAG.'-'.$sellang, $post_ID );
							}
						}
						wp_delete_object_term_relationships( $post_ID, TAXONAME );
					}
					$return = wp_set_object_terms($post_ID, $sellang, TAXONAME);

				} elseif ( "undefined" == $sellang ) {

					// clean linked targets
					foreach ($listlanguages as $language) {

						$target_id = get_post_meta( $post_ID, QUETAG.'-'.$language->slug, true );
						if ( $target_id != "" ) {
							delete_post_meta( $target_id, QUETAG.'-'.$previous_lang );
						}
					}
					// now undefined
					wp_delete_object_term_relationships( $post_ID, TAXONAME );
				}

				$curlang = $this->get_cur_language( $post_ID ) ; // array


				/* the linked posts set by author in postmeta */

				foreach ($listlanguages as $language) {
					$inputid = 'xili_language_'.QUETAG.'-'.$language->slug ;
					$recinputid = 'xili_language_rec_'.QUETAG.'-'.$language->slug ;
					$linkid = ( isset ( $_POST[$inputid] ) ) ? $_POST[$inputid] : 0 ;
					$reclinkid = ( isset ( $_POST[$recinputid] ) ) ? $_POST[$recinputid] : 0 ; /* hidden previous value */
					$langslug = QUETAG.'-'.$language->slug ;

					if ( $reclinkid != $linkid ) { /* only if changed value or created since 1.3.0 */
						if ((is_numeric($linkid) && $linkid == 0) || '' == $linkid ) {
							delete_post_meta($post_ID, $langslug);
						} elseif ( is_numeric( $linkid ) && $linkid > 0 ) {
							// test if possible 2.5.1
							if ( $this->is_post_free_for_link ( $post_ID, $curlang[QUETAG], $language->slug, $linkid ) ) {
								update_post_meta( $post_ID, $langslug, $linkid);

								if ($reclinkid == "-1")	update_post_meta( $linkid, QUETAG.'-'.$sellang, $post_ID);

								// update target 2.5
								foreach ($listlanguages as $metalanguage) {
									if ( $metalanguage->slug != $language->slug && $metalanguage->slug != $curlang[QUETAG] ) {
										$id = get_post_meta( $post_ID, QUETAG.'-'.$metalanguage->slug, true );
										if ( $id != "" ) {
											update_post_meta( $linkid, QUETAG.'-'.$metalanguage->slug, $id );
										}
									}
								}
								update_post_meta( $linkid, QUETAG.'-'.$curlang[QUETAG], $post_ID ); // cur post
								$return = wp_set_object_terms( $linkid, $language->slug, TAXONAME );

							}
						}
					}
				}
			}
		}
	}

	/**
	 * add to secure manual input of linked post
	 *
	 * @since 2.5.1
	 *
	 */

	function is_post_free_for_link ( $from_post_ID, $from_lang, $target_lang, $target_ID ) {

		if ( $from_post_ID == $target_ID ) return false ; // obvious

		if ( $this->temp_get_post ( $target_ID ) ) {
			// check if target ID is not yet in another lang
			$target_slug = $this->get_post_language ( $target_ID ) ;
			if ( $target_slug == '' ) {
				return true; // undefined
			} elseif ( $target_slug == $target_lang ) {
				// check target is not yet link to other
				$id = get_post_meta( $target_ID, QUETAG.'-'.$from_lang, true );
				if ( $id != "" ) {
					return false; // yet linked
				} else {
					return true;
				}

			} else {
				return false; // yet another language
			}

		} else {
			return false; // no target
		}

	}

	/**
	 * if post created by dashboard, when first saved by author, fixes post_name for permalinks use
	 *
	 * @since 2.5
	 * @updated 2.15
	 *
	 */
	function fixes_post_slug ( $post_ID, $post ) {
		if ( defined ( 'XDMSG' ) && get_post_type( $post_ID ) == XDMSG ) return;

		$translation_state = get_post_meta( $post_ID, $this->translation_state, true );

		$checked = ( isset($_POST['xl_permalink_option']) );
		if ( false !== strpos( $translation_state , "initial" ) &&  $checked ) {
			global $wpdb;
			$where = array( 'ID' => $post_ID );
			$what = array ();
			$what['post_name'] = sanitize_title($post->post_title);

			if ( $what != array() )
				$er = $wpdb->update( $wpdb->posts, $what, $where );

			if (in_array ( $post->post_status, array( 'publish', 'private', 'future', 'pending' ) ))
				delete_post_meta( $post_ID, $this->translation_state );
		}
	}

	/**
	 * inspired by find_posts_div from wp-admin/includes/template.php
	 *
	 * @since 2.3.1 to restrict to type of post
	 *
	 * @param unknown_type $found_action -
	 */
	function xili_find_posts_div($found_action = '', $post_type, $post_label ) {

	?>
		<div id="find-posts" class="find-box" style="display:none;">
			<div id="find-posts-head" class="find-box-head"><?php printf( __( 'Find %s','xili-language' ), $post_label ) ; ?>
				<div id="find-posts-close"></div>
			</div>
			<div class="find-box-inside">
				<div class="find-box-search">
					<?php if ( $found_action ) { ?>
						<input type="hidden" name="found_action" value="<?php echo esc_attr($found_action); ?>" />
					<?php } ?>

					<input type="hidden" name="affected" id="affected" value="" />
					<?php wp_nonce_field( 'find-post-types', '_ajax_nonce', false ); ?>
					<label class="screen-reader-text" for="find-posts-input"><?php _e( 'Search' ); ?></label>
					<input type="text" id="find-posts-input" name="ps" value="" />
					<input type="button" id="find-posts-search" value="<?php esc_attr_e( 'Search' ); ?>" class="button" />
					<div class="clear"></div>
					<?php /* checks replaced by hidden - see js findposts*/ ?>
					<input type="hidden" name="find-posts-what" id="find-posts-what" value="<?php echo esc_attr($post_type); ?>" />

				</div>
				<div id="find-posts-response"></div>
			</div>
			<div class="find-box-buttons">
				<?php submit_button( __( 'Select' ), 'button-primary alignright', 'find-posts-submit', false ); ?>
			</div>
		</div>
	<?php
	}

	/**************** Attachment post language *******************/

	function add_language_attachment_fields ($form_fields, $post) {

		if ( current_theme_supports( 'custom-header') ) {
			$meta_header = get_post_meta($post->ID, '_wp_attachment_is_custom_header', true );
			if ( !empty( $meta_header ) && $meta_header == get_option('stylesheet') ) {
				$form_fields['_final'] = '<strong>'.__('This media is inside Header Image list.', 'xili-language') .'</strong><br /><br /><small>© xili-language v.'.XILILANGUAGE_VER .'</small>';
				return $form_fields ;
			}
		}

		$context = get_post_meta( $post->ID, '_wp_attachment_context', true );
		$attachment_post_language = get_cur_language( $post->ID, 'slug' );
		$listlanguages = $this->get_listlanguages () ;

		if ( ! empty( $context ) && in_array( $context, $this->custom_xili_flags ) ) {
			$form_fields['_final'] = '<strong>'.__('This media is a flag for multilingual context. See the box on the right sidebar...', 'xili-language') .'</strong><br /><br /><small>© xili-language v.'.XILILANGUAGE_VER .'</small>';
			return $form_fields ;
		}

		$attachment_id = $post->ID;

		// get list of languages for popup
		$attachment_post_language = get_cur_language( $attachment_id, 'slug' );

		$listlanguages = $this->get_listlanguages () ;
		// get_language
		if ( '' != $attachment_post_language ) { // impossible to change if already assigned
			$name = $this->langs_slug_name_array[$attachment_post_language];
			$fullname = $this->langs_slug_fullname_array[$attachment_post_language];
			$form_fields['attachment_post_language'] = array(
				'label'      => __('Language', 'xili-language'),
				'input'      => 'html',
				'html'       => "<hr /><strong>$fullname</strong> ($name)<input type='hidden' name='attachments[$attachment_id][attachment_post_language]' value='" . $attachment_post_language . "' /><br />",
				'helps'      => __('Language of the file caption and description.', 'xili-language')
			);

		} else { // selector

			$html_input = '<hr /><select name="attachments['.$attachment_id.'][attachment_post_language]" ><option value="undefined">'.__('Choose…','xili-language').'</option>';
			foreach ($listlanguages as $language) {
				$selected = selected ( (''!=$attachment_post_language && $language->slug == $attachment_post_language), true, false );
				$html_input .= '<option value="'.$language->slug.'" '.$selected.'>'.$language->description.' ('.$language->name.')</option>';
			}
			$html_input .= '</select>';

			$form_fields['attachment_post_language'] = array(
				'label'      => __('Language', 'xili-language'),
				'input'      => 'html',
				'html'       => $html_input,
				'helps'      => __('Language of the file caption and description.', 'xili-language')
			);
		}

		if ( isset ( $post->ID ) && get_current_screen() ) { // test ajax WP 3.5
			$clone = ( get_current_screen()->base == "post" && get_post_type( $post->ID ) == 'attachment' ) ? true : false ;
		} else {
			$clone = false; // not visible if called by ajax
		}

		if ( '' != $attachment_post_language && $clone ) { // only in media edit not in media-upload



			$result = $this->translated_in ( $attachment_id, 'link', 'edit_attachment' );

			$trans = $this->translated_in ( $attachment_id, 'array');
			$html_input = '<hr />';
			if ( $result == '' ) {
				$html_input .= __('not yet translated', 'xili-language') ;
				$label = __('No clone', 'xili-language');
				$helps = __('You must create a clone in other language if necessary.', 'xili-language');
			} else {
				$html_input .= __('Title, caption and description are already available in language', 'xili-language');
				$html_input .= '&nbsp;:&nbsp;<span class="translated-in">' . $result .'</span><br />';
				$label = __('Clones', 'xili-language');
				$helps = __('A clone of attachment contains the same image but not the same editable texts.', 'xili-language');
			}

			$form_fields['infos_about_clones'] = array(
					'label'      => $label,
					'input'      => 'html',
					'html'       => $html_input,
					'helps'      => $helps

				);
			$html_input = '<hr />';
			$html_input .= '<input type="hidden" id="xl_post_parent" name="xl_post_parent" value="'. $post->post_parent . '" />';

			$select_options = "";
			foreach ($listlanguages as $language) {
				if ( $language->slug != $attachment_post_language && !isset ($trans[$language->slug] )) {
					$select_options .= '<option value="'.$language->slug.'" >'.$language->description.' ('.$language->name.')</option>';
				}
			}
			if ( $select_options ) {
				$html_input .= '<br />';
				$html_input .= '<select name="attachments['.$attachment_id.'][create_clone_attachment_with_language]" ><option value="undefined">'.__('Select…','xili-language').'</option>';

				$html_input .= $select_options . '</select>';

				$form_fields['create_clone_attachment_with_language'] = array(
					'label'      => __('Create clone in language', 'xili-language'),
					'input'      => 'html',
					'html'       => $html_input,
					'helps'      => sprintf(__('Select a language, and after clicking the button %s : A clone with same file will be created to translate title, caption, alt text and description.', 'xili-language'), '<strong>'.__('Update').'</strong>' )

				);
			}

			if ( $post->post_parent > 0 ) {
				$html_input = '<hr /><strong>'.sprintf( '%s:&nbsp;',__('attached to','xili-language')).get_the_title ( $post->post_parent ).'</strong>';
				$html_input .='&nbsp;&nbsp;<a href="post.php?post='.$post->post_parent.'&action=edit" title="'.__('Edit').'" >'.__('Edit').'</a>';
				$helps = __('This titled post above has this media as attachment.', 'xili-language');
			} else {
				$html_input = '<hr /><strong>'.__( 'not attached to a post.' , 'xili-language').'</strong>';
				$helps = __('In the Media Library table, it is possible to attach a media to a post.', 'xili-language');
			}

			$form_fields['attachment-linked-post'] = array(
				'label'      => __('<small>Info: </small>This media is', 'xili-language').'&nbsp;&nbsp',
				'input'      => 'html',
				'html'       => $html_input,
				'helps'      => $helps
			);

			$form_fields['_final'] = '<small>© xili-language v.'.XILILANGUAGE_VER .'</small>';
		}

		return $form_fields ;
	}

	/**
	 * add media states if media is flag
	 *
	 * @since 2.15
	 * @since 2.16.4 - admin flag
	 */
	function add_display_media_states ( $media_states ) {
		global $post;

		$stylesheet = get_option('stylesheet');
		$meta_header = get_post_meta($post->ID, '_wp_attachment_is_custom_xili_flag', true ); // true for this current theme
		$context = get_post_meta( $post->ID, '_wp_attachment_context', true );
		if ( ! empty( $context ) && in_array ( $context , $this->custom_xili_flags) && ! empty( $meta_header ) && $meta_header == $stylesheet ) {
			$media_states[] = ( $context == 'custom_xili_flag') ? __( 'Flag', 'xili-language' ) : __( 'Admin Flag', 'xili-language' );
		}
		return $media_states;
	}

	function attachment_submitbox_flag_metadata () {
		global $post;
		$context = get_post_meta( $post->ID, '_wp_attachment_context', true );
		?>
		<div class="misc-pub-section" >
		<label for="context" class="selectit"><?php _e( 'Set as', 'xili-language' ) ?>:&nbsp;
			<select name="context" id="context">
				<option value="" <?php selected( $context,'custom_xili_flag') ?>><?php _e( 'define...', 'xili-language' ); ?></option>
				<option value="custom_xili_flag" <?php selected( $context,'custom_xili_flag') ?>><?php _e( 'Menu flag', 'xili-language' ); ?></option>
				<option value="admin_custom_xili_flag" <?php selected( $context, 'admin_custom_xili_flag') ?>><?php _e( 'Admin flag', 'xili-language' ); ?></option>
			</select>

			</label>
		</div>
		<?php // fixes 20140901
	}

	/**
	 * Add a meta box in Edit Media page (edit-form-advanced.php)
	 * @since 2.15
	 *
	 */
	function add_custom_box_in_media_edit(){
		add_meta_box( 'xili_flag_as_attachment', __( 'Multilingual informations', 'xili-language') , array(&$this,'media_multilingual_infos_box'), 'attachment', 'side', 'low' );
	}

	function media_multilingual_infos_box ( $post ) {
		$context = get_post_meta( $post->ID, '_wp_attachment_context', true );
		$attachment_post_language = get_cur_language( $post->ID, 'slug' );
		$listlanguages = $this->get_listlanguages () ;

		if ( ! empty( $context ) && in_array( $context, $this->custom_xili_flags) ) {
			if ( '' != $attachment_post_language ) {
				echo '<p>' . __('This flag is assigned to a language', 'xili-language') . '</p>';
			} else {
				echo '<p>' . __('Assign this flag to a language', 'xili-language') . '</p>';
			}

			$html_input = '<select name="attachments['.$post->ID.'][attachment_post_language]" ><option value="undefined">'.__('Choose…','xili-language').'</option>';
			foreach ($listlanguages as $language) {
				$selected = selected ( ( ''!=$attachment_post_language && $language->slug == $attachment_post_language ), true, false );
				$html_input .= '<option value="'.$language->slug.'" '.$selected.'>'.$language->description.' ('.$language->name.')</option>';
			}
			$html_input .= '</select>';
			echo  $html_input;

		} else {
			if ( '' != $attachment_post_language ) { // impossible to change if already assigned
				$name = $this->langs_slug_name_array[$attachment_post_language];
				$fullname = $this->langs_slug_fullname_array[$attachment_post_language];
				$html = '<p>' . __('This media is assigned to a language', 'xili-language') . '</p>';
				$html .= "<p><strong>$fullname</strong> ($name)<input type='hidden' name='attachments[{$post->ID}][attachment_post_language]' value='" . $attachment_post_language . "' /></p>";
			// more infos

				$result = $this->translated_in ( $post->ID, 'link', 'edit_attachment', '<br/>' );

				if ( $result == '' ) {
					$html .= __('not yet translated', 'xili-language') ;
				} else {
					$html .= __('This media has already clone(s) for translation in', 'xili-language');
					$html .= '&nbsp;:&nbsp;<br /><span class="translated-in">' . $result .'</span><br />';
				}

			} else {
				$html = '<p>' . __('This media is not assigned to a language, see column on left under description textarea...', 'xili-language') . '</p>';
			}
			 echo $html;

		}
		echo '<p><small>© xili-language v.'.XILILANGUAGE_VER .'</small></p>';
	}

	function update_attachment_context ( $post_ID ) {
		// get context
		if ( '' == $_POST ['context'] ) {
			$context = get_post_meta( $post_ID, '_wp_attachment_context', true );
			if ( ! empty( $context ) && in_array ($context, $this->custom_xili_flag) )
				delete_post_meta( $post_ID, '_wp_attachment_context' );
		} else {
			update_post_meta( $post_ID, '_wp_attachment_context', $_POST ['context'] ); // add because now select
			$stylesheet = get_option('stylesheet');
			update_post_meta($post_ID, '_wp_attachment_is_custom_xili_flag', $stylesheet );
		}
	}

	function set_flag_register_setting () {
		$name = ( is_child_theme()) ? get_option("stylesheet") : get_option("template") ;

		register_setting( $this->flag_settings_name .'_group', $this->flag_settings_name, array( $this,'flag_validate_settings' ) );
	}

	function flag_options_theme_menu () {
		if ( !current_theme_supports( 'custom_xili_flag') ) return;

		$this->flag_theme_page = add_theme_page( sprintf(__('%1$s Theme Options', 'xili-language'), get_option("current_theme") ) , __('xili flag Options', 'xili-language'), 'manage_options', $this->flag_settings_name, array( $this,'flag_options_theme_page' ) );
		add_action('load-'.$this->flag_theme_page, array( $this, 'flag_theme_options_help_page' ) );

		add_settings_section( 'xili_flag_section_1', __('Multilingual language selector navigation menu options', 'xili-language'), array( $this, 'display_one_section'), $this->flag_settings_name .'_group');
		$title_description = $this->get_xili_flag_options_description();

		$field_args = array(
			'option_name'	=> $this->flag_settings_name,
			'title'			=> $title_description['menu_with_flag']['title'],
			'type'			=> 'checkbox',
			'id'			=> 'menu_with_flag',
			'name'			=> 'menu_with_flag',
			'desc'			=> $title_description['menu_with_flag']['description'],
			'std'			=> 'with-flag',
			'label_for'		=> 'menu_with_flag',
			'class'			=> 'css_class settings'
		);
		add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'display_one_setting'), $this->flag_settings_name .'_group', 'xili_flag_section_1', $field_args );

		add_settings_section( 'xili_flag_section_2', __('Flag style options', 'xili-language'), array( $this, 'display_one_section'), $this->flag_settings_name .'_group');

		$field_args = array(
			'option_name'	=> $this->flag_settings_name,
			'title'			=> __('Available flags', 'xili-language'),
			'type'			=> 'xili',
			'id'			=> 'flags_list',
			'name'			=> 'flags_list',
			'desc'			=> sprintf( __("The list of images uploaded and assigned as flag in Media table. (%s)", "xili-language"), '<small>'.__('*: from theme subfolder', 'xili-language').'</small>' ),
			'std'			=> 'with-flag',
			'label_for'		=> 'flags_list',
			'class'			=> 'css_class settings'
		);
		add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'display_flags_list'), $this->flag_settings_name .'_group', 'xili_flag_section_2', $field_args );

		$defaults = $this->get_default_xili_flag_options();
		foreach ( $defaults as $key => $default_value ) {
			if ( false !== strpos ( $key , 'css_' )) {
				$field_args = array(
					'option_name'	=> $this->flag_settings_name,
					'title'			=> $title_description[$key]['title'],
					'type'			=> 'text',
					'id'			=> $key,
					'name'			=> $key,
					'desc'			=> $title_description[$key]['description'],
					'std'			=> $default_value,
					'label_for'		=> $key,
					'class'			=> 'css_class settings',
					'size'		=> '110',
				);
				add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'display_one_setting'), $this->flag_settings_name .'_group', 'xili_flag_section_2', $field_args );
			}
		}
		$field_args = array(
			'option_name'	=> $this->flag_settings_name,
			'title'			=> __('Reset to default values', 'xili-language'),
			'type'			=> 'checkbox',
			'id'			=> 'reset',
			'name'			=> 'reset',
			'desc'			=> __('When checking, all values will be resetted to those defined by default for bundled theme like twentyfourteen or by the author of this theme.', 'xili-language'),
			'std'			=> 'reset',
			'label_for'		=> 'reset',
			'class'			=> 'css_class settings'
		);
		add_settings_field( $field_args['id'], $field_args['title'] , array( $this, 'display_one_setting'), $this->flag_settings_name .'_group', 'xili_flag_section_2', $field_args );
	}

	function flag_options_theme_page () {
		$message = '';
		?>
		<div class="section panel">
		<h1><?php printf( __('Flag Multilingual options for %1$s theme ', 'xili-language' ), get_option("current_theme") ); ?></h1>
		<?php if ( isset($_GET['settings-updated']) ) {
			switch ( $_GET['settings-updated'] ) :
			case 'true' :
				$message = __('Flag Multilingual options updated.', 'xili-language');
				$class = 'updated';
				break;
			endswitch;
		}
		if ( $message )
			echo "<div id='message' class='$class'><p>$message</p></div>\n";
		 ?>
		<form method="post" enctype="multipart/form-data" action="options.php">
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
			<?php
				settings_fields( $this->flag_settings_name . '_group' );	// nonce, action (plugin.php)
				do_settings_sections( $this->flag_settings_name . '_group' );
			?>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
		<p><small><?php echo get_option("current_theme"); ?> by <a href="<?php echo $this->devxililink; ?>" target="_blank" >dev.xiligroup.com</a> (©2015) <?php echo '(xili-language v.' . XILILANGUAGE_VER . ')';?></small></p>

		</div>
		<?php
	}
	/**
	 * display Flags list in page xili flag options
	 *
	 * @updated 2.16.4
	 *
	 * able to detect flag even if not declared
	 *
	 * @since 2.15
	 */
	function display_flags_list ( $args ) {
		global $_wp_theme_features;
		extract ( $args ) ;

		$available = $this->get_flag_series(); // only front-end series
		$listlanguages = $this->get_listlanguages () ;
		echo '<ul>';

		foreach ( $listlanguages as $one_language ) {
			if ( isset ( $available [$one_language->slug ]) ) {
				echo '<li>' . wp_get_attachment_image( $available[$one_language->slug], 'full' ) . ' (' .  $one_language->name .', '. $one_language->description . ')</li>';
			} else {
				if ( isset ($_wp_theme_features['custom_xili_flag'][0][$one_language->slug] ) ) {
					$url = sprintf($_wp_theme_features['custom_xili_flag'][0][$one_language->slug]['path'], get_template_directory_uri(), get_stylesheet_directory_uri());
					$width = $_wp_theme_features['custom_xili_flag'][0][$one_language->slug]['width'];
					$height = $_wp_theme_features['custom_xili_flag'][0][$one_language->slug]['height'];
					echo '<li><img src="' . $url . '"> (' .  $one_language->name .', '. $one_language->description . ') <small>(*)</small></li>';
				} else {
					$path_root = get_stylesheet_directory(); // 2.16.4
					$path = '%2$s/images/flags/'.$one_language->slug.'.png';
					if ( file_exists( sprintf( $path, '', $path_root ))) {
						$url = get_stylesheet_directory_uri() . '/images/flags/' . $one_language->slug .'.png'; // only in current (child or not)
						echo '<li><img src="' . $url . '"> (' .  $one_language->name .', '. $one_language->description . ') <small>' . sprintf (__('undeclared flag (in theme support custom_xili_flag) available for %s', 'xili-language'), $one_language->name .', '. $one_language->description ) . '</small></li>';
					} else {
						echo '<li>' . sprintf (__('no flag available for %s', 'xili-language'), $one_language->name .', '. $one_language->description ) . '</li>';
					}
				}
			}
		}
		echo '</ul>';
		echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";
	}

	function flag_validate_settings ( $input ) {
		if ( $input ) {
			foreach($input as $id => $v) {
				$newinput[$id] = trim($v);
				if ( in_array ( $id , array ( 'css_li_hover', 'css_li_a', 'css_li_a_hover') ) && isset ( $input[$id] ) ) {
					if ( substr ( $newinput[$id], -1 ) != ';') $newinput[$id] = $newinput[$id] . ';';
				}
			}
		}
		if ( isset ( $input['reset'] ) ) {
			$newinput = $this->get_default_xili_flag_options ();
			if ( isset ( $input['menu_with_flag'] ) )
				$newinput['menu_with_flag'] = $input['menu_with_flag']; // recovet first value only
			return $newinput;
		}

		if ( !isset ( $input['menu_with_flag'] ) ) $newinput['menu_with_flag'] = '0';
		return $newinput;
	}

	function flag_theme_options_help_page () {
		$screen = get_current_screen();

		$help = '<p>'.__('Each parameter contains a name and a description.', 'xili-language').'</p>';
		$help .= '<p>'.__('Here, you define the use of frontend flags. Requires some knowledges in CSS.', 'xili-language').'</p>';
		$help .= '<p>'.__('If, with a customized theme, you dont see nothing in frontend menu, you must examine the html of the generated header.', 'xili-language');
		$help .= '<br />'.__('If you see the css lines with good flags, you must work with the menu ul selector which can not be the same as the default shown in xili flags option...', 'xili-language');
		$help .= '<br />'.__('Some themes dont use current selector as in bundled themes like 2014...', 'xili-language').'</p>';
		$help .= '<p>'. sprintf(__('More detailled infos in %s.', 'xili-language'), sprintf('<a href="%s">' . __('this site', 'xili-language') . '</a>', $this->fourteenlink )).'</p>';

		$screen->add_help_tab( array(
				'id'	=> $this->flag_theme_page,
				'title'	=> __('Help'),
				'content'	=>$help	));
	}

	// attachment_fields_to_save
	// call by apply_filters('attachment_fields_to_save', $post, $attachment);
	function set_attachment_fields_to_save ( $post, $attachment ) {
		global $wpdb;

		if ( isset($attachment['attachment_post_language']) ){
			if ( $attachment['attachment_post_language'] != '' && $attachment['attachment_post_language'] != 'undefined' ) {
				wp_set_object_terms($post['ID'], $attachment['attachment_post_language'], TAXONAME);
			} else {
				wp_delete_object_term_relationships( $post['ID'], TAXONAME );
			}
		}
		$clone = $post;
		unset ($clone['ID']);
		if ( isset($attachment['create_clone_attachment_with_language']) && $attachment['create_clone_attachment_with_language'] != 'undefined' ){

			$clone['post_title'] = sprintf(__('Translate in %2$s: %1$s', 'xili-language'),$clone['post_title'], $attachment['create_clone_attachment_with_language'] );
			if ( $clone['post_content'] ) $clone['post_content'] = sprintf(__('Translate: %1$s', 'xili-language'),$clone['post_content'] );
			if ( $clone['post_excerpt'] ) $clone['post_excerpt'] = sprintf(__('Translate: %1$s', 'xili-language'),$clone['post_excerpt'] );

			$parent_id = $post['xl_post_parent']; // 2.8.4.2 hidden input

			$linked_parent_id = xl_get_linked_post_in ( $parent_id, $attachment['create_clone_attachment_with_language'] );
			$clone['post_parent'] = $linked_parent_id; // 0 if unknown linked id of parent in assigned language
			$clone['guid'] = $post['attachment_url']; // 2.18.1 - the URI of media and not URI of attachment itself

			// now clones
			$cloned_attachment_id = wp_insert_post( $clone );
			// clone post_meta
			$data = get_post_meta( $post['ID'], '_wp_attachment_metadata', true );
			$data_file = get_post_meta( $post['ID'], '_wp_attached_file', true );
			$data_alt = get_post_meta( $post['ID'], '_wp_attachment_image_alt', true );
			update_post_meta( $cloned_attachment_id, '_wp_attachment_metadata', $data);
			update_post_meta( $cloned_attachment_id, '_wp_attached_file', $data_file);
			if ( '' != $data_alt ) update_post_meta( $cloned_attachment_id, '_wp_attachment_image_alt', sprintf(__('Translate: %1$s', 'xili-language'), $data_alt ));
			// set language and links of cloned of current
			update_post_meta( $cloned_attachment_id, QUETAG.'-'.$attachment['attachment_post_language'], $post['ID'] );
			wp_set_object_terms( $cloned_attachment_id, $attachment['create_clone_attachment_with_language'], TAXONAME );

			// get already linked of cloned
			$already_linked = array();
			if ( $meta_values = $wpdb->get_results( $wpdb->prepare( "SELECT meta_value, meta_key FROM $wpdb->postmeta WHERE meta_key LIKE %s AND post_id = %d", QUETAG .'-' . '%', $post['ID']) ) ) {

				foreach ( $meta_values as $key_val ) {
					update_post_meta( $key_val->meta_value, QUETAG.'-'.$attachment['create_clone_attachment_with_language'], $cloned_attachment_id );
					$slug = str_replace( QUETAG.'-', '', $key_val->meta_key );
					$already_linked[$slug] = $key_val->meta_value;
				}
			}
			// set links of current to cloned

			update_post_meta( (int) $post['ID'], QUETAG.'-'.$attachment['create_clone_attachment_with_language'], $cloned_attachment_id );
			if ( $already_linked != array() ) {
				foreach ( $already_linked as $key => $id ) {
					update_post_meta( $post['ID'], QUETAG.'-'.$key, $id );
					if ( $key != $attachment['create_clone_attachment_with_language'] ) update_post_meta( $cloned_attachment_id, QUETAG.'-'.$key, $id );
				}
			}
		}

		return $post;
	}

	// called before deleting attachment by do_action( 'delete_attachment'
	function if_cloned_attachment ( $post_ID ) {
		global $wpdb;
		if ( $post = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID = %d", $post_ID) ) ) {

			if ( 'attachment' == $post->post_type ) {
				$attachment_post_language = get_cur_language( $post_ID, 'slug' );
				// test meta lang
				$linked_list = $this->translated_in ( $post_ID, 'array');
				if ( array() != $linked_list ) {
					$this->dont_delete_file = true;
					// update meta in linked attachments
					// a:1:{s:5:"en_us";a:3:{s:7:"post_ID";s:4:"8537";s:4:"name";s:5:"en_US";s:11:"description";s:7:"english";}}
					foreach ( $linked_list as $lang_slug => $linked_array ) {
						delete_post_meta ( $linked_array['post_ID'], QUETAG.'-'.$attachment_post_language ); // 2.18.1
					}
				} else {
					$this->dont_delete_file = false;
				}
			}
		}
	}

	// called before deleting file by apply_filters( 'wp_delete_file'
	function if_file_cloned_attachment ( $file ) {
		if ( $this->dont_delete_file == true ) $file = '';
		return $file;
	}


	/**************** List of Posts (edit.php) *******************/

	/**
	 * display languages column name in Posts/Pages list
	 *
	 * @updated 1.8.9
	 *
	 * complete get_column_headers (screen.php) filter
	 *
	 * @since 2.9.10
	 */
	function xili_manage_column_name( $cols ) {

		if ( defined ('XDMSG') ) $CPTs = array( XDMSG );
		$CPTs[] = 'page';
		$CPTs[] = 'post';
		$CPTs[] = 'attachment'; // 2.8.4.1

		$custompoststype = $this->xili_settings['multilingual_custom_post'] ;
		if ( $custompoststype != array()) {
			foreach ( $custompoststype as $key => $customtype ) {
				if ( $customtype['multilingual'] == 'enable' ) {
					$CPTs[] = $key;
				}
			}
		}

		$post_type = get_post_type();

		if ( in_array ( $post_type, $CPTs) ) {

			$ends = apply_filters ( 'xiliml_manage_column_name', array( 'comments', 'date', 'rel', 'visible'), $cols, $post_type ); // 2.8.1
			$end = array();
			foreach( $cols AS $k=>$v ) {
				if ( in_array($k, $ends) ) {
					$end[$k] = $v;
					unset($cols[$k]);
				}
			}
			$cols[TAXONAME] = __('Language','xili-language');
			$cols = array_merge($cols, $end);
		}
		return $cols;
	}

	/**
	 * display languages column in Posts/Pages list
	 *
	 * @updated 1.8.9
	 */
	function xili_manage_column( $name, $id ) {
		global $wp_query; // 2.8.1
		if( $name != TAXONAME )
			return;
		$output = '';
		$terms = wp_get_object_terms( $id, TAXONAME );
		$first = true;
		foreach( $terms AS $term ) {
			if ( $first )
				$first = false;
			else
				$output .= ', ';

			if ( current_user_can ('activate_plugins') ) {
				$output .= '<span class="curlang lang-'. $term->slug .'"><a href="' . 'options-general.php?page=language_page'.'" title="'.sprintf(esc_attr__('Post in %s. Link to see list of languages…','xili-language'), $term->description ) .'" >'; /* see more precise link ?*/
				$output .= $term->name .'</a></span>';
			} else {
				$output .= '<span title="'. esc_attr( sprintf(__('Post in %s.','xili-language'), $term->description ) ) .'" class="curlang lang-'. $term->slug .'">' . $term->name . '</span>' ;
			}

			$output .= '<input type="hidden" id="'.QUETAG.'-'.$id.'" value="'.$term->slug.'" >'; // for Quick-Edit - 1.8.9
		}
		$xdmsg = ( defined ('XDMSG') ) ? XDMSG : '' ;


		$post_type = ( isset ( $wp_query->query_vars['post_type' ] ) ) ? $wp_query->query_vars['post_type' ] : '' ;

		if ( $post_type != $xdmsg ) { // no for XDMSG
			$output .= '<br />';

			$result = $this->translated_in ( $id );

			$output .= apply_filters ( 'xiliml_language_translated_in_column', $this->display_translated_in_result ( $result ), $result, $post_type );
		}
		echo '<div id="'.TAXONAME.'-' . $id . '">' .$output . '</div>'; // called by do_action() class wp_posts_list_table 2.9.10
	}

	/**
	*
	*/

	function display_translated_in_result ( $result ) {
		$output = "";
		if ( $result == '' ) {
			$output .= __('not yet translated', 'xili-language') ;
		} else {
			$output .= __('translated in:', 'xili-language') ;
			$output .= '&nbsp;<span class="translated-in">' . $result .'</span>';
		}
		return $output;
	}


	/**
	 * Return list of linked posts
	 * used in edit list
	 *
	 * @param: mode = array to customize list
	 * @since 2.5
	 *
	 */
	function translated_in ( $post_ID, $mode = 'link', $type = 'post', $separator = ' ' ) {

		$curlang = $this->get_cur_language( $post_ID ) ; // array
		$listlanguages = $this->get_listlanguages () ;
		$trans = array();
			foreach ( $listlanguages as $language ) {
				if ( $language->slug != $curlang[QUETAG] ) {
					$otherpost = $this->linked_post_in( $post_ID, $language->slug ) ;
					if ( $otherpost ) {
						$linepost = $this->temp_get_post ( $otherpost );
						if ( $linepost ) {
							switch ( $mode ) {
								case 'link' :
									$detail = false;
									$title_type = $type;
									if ( $type == 'post' || $type == 'edit_attachment') {
										$link = 'post.php?post='.$linepost->ID.'&action=edit';
										if ( $type == 'edit_attachment' ) $detail = true;
										if ( $type == 'edit_attachment' ) $title_type = 'attachment';
									} elseif ( $type == 'attachment') {
										$link = 'media.php?attachment_id='.$linepost->ID.'&action=edit'; // not used (small settings screen)
									}
									$a_content = ( $detail )  ? $language->description. ' (' . $language->name .')' : $language->name ;
									$title = sprintf ( __( 'link to edit %1$s %3$d in %2$s', 'xili-language' ), $title_type, $language->description, $linepost->ID );
									$trans[] = sprintf( '<a href="%1$s" title="%2$s" class="lang-%4$s" >%3$s</a>', $link, $title, $a_content, $language->slug ); // no localization 2.18.1
								break;
								case 'array' :
									$trans[$language->slug] = array ( 'post_ID' => $linepost->ID, 'name' => $language->name, 'description' => $language->description );
								break;

							}
						}
					}
				}
			}

		if ( $mode == 'array' ) return $trans;

		$list = implode ($separator, $trans ) ;
		return $list;
	}

	/**
	 * style for posts (and taxonomies) list
	 *
	 *
	 */
	function print_styles_posts_list () {

		if ( get_current_screen()->base == "upload" )
				$this->insert_news_pointer ( 'media_language' ); // 2.6.3

		$insert_flags = ( $this->xili_settings['external_xl_style'] == "on" );
		echo "<!---- xl css --->\n";
		echo '<style type="text/css" media="screen">'."\n";
			echo ".langquickedit { background: #E4EAF8; padding:0 5px 4px !important; border:1px solid #ccc; width:140px !important; float:right !important;}\n";
			echo ".toppost { margin: 0 20px 2px 7px; } \n";
			echo ".toppage { margin: -40px 20px 2px 7px; } \n";
			echo "span.curlang a { display:inline-block; font-size:80%; height:18px; width:60px; } \n";
			echo "span.translated-in a { display:inline-block; text-indent:-9999px; width:25px; border:0px solid red;} \n";
			if ( $insert_flags ) {
				echo 'div.taxinmos span[class|="lang"] { display:inline-block; text-indent:-9999px; width:20px; border:0px solid red; }'. "\n";
				echo 'fieldset.taxinmos span[class|="lang"] { display:inline-block; text-indent:-9999px; width:20px; border:0px solid red; }'. "\n";
			}
			$listlanguages = $this->get_listlanguages();

			if ( $this->style_folder == get_stylesheet_directory_uri() ) {
				$folder_url = $this->style_folder . '/images/flags/' ;
			} else {
				$folder_url = $this->style_folder . '/xili-css/flags/' ;
			}

			foreach ($listlanguages as $language) {
				$ok = false;
				$flag_id = $this->get_flag_series ( $language->slug, 'admin' );
				if ( $flag_id != 0 ) {
				    $flag_uri = wp_get_attachment_url( $flag_id ) ;
					$ok = true;
				} else {
					$flag_uri = $folder_url . $language->slug .'.png';
					$ok = file_exists( $this->style_flag_folder_path . $language->slug .'.png' );
				}

				if ( $insert_flags && $ok ) {

					echo "span.lang-". $language->slug ." { background: url(". $flag_uri .") no-repeat 0% center } \n";
					echo "span.translated-in a.lang-". $language->slug ." { background: transparent url(". $flag_uri .") no-repeat 50% center ; } \n";
					echo "span.curlang.lang-" . $language->slug ." a { color:#f5f5f5; text-indent:-9999px ;}\n";

					if ( class_exists( 'xili_tidy_tags' ) ) {
						echo "div#xtt-edit-tag span.curlang.lang-" . $language->slug ." { margin-left:5px; color:#f5f5f5; display:inline-block; height:18px; width:25px; text-indent:-9999px ; }\n";
					}

				} else {
					echo "span.curlang.lang-" . $language->slug ." a { font-size:100%; text-align: left; }\n";
				}

			}
		echo "</style>\n";

		if ( $this->exists_style_ext && $this->xili_settings['external_xl_style'] == "on" ) wp_enqueue_style( 'xili_language_stylesheet' );
	}

	/**
	 * Insert translation for taxonomies columns in edit.php - only dashboard yet
	 *
	 * filter from sanitize_term_field  at end {$taxonomy}_{$field} name here
	 *
	 * @since 2.13.3
	 *
	 */
	function translated_taxonomy_name ( $value, $term_id, $context ) {
		if ( $context == 'display') {

			$locale = $this->admin_side_locale();
			$this->add_local_text_domain_file ( $locale ) ; // called here - 2.20.3

			$theme_domain = the_theme_domain();
			$translated = translate( $value, $theme_domain );

			$tvalue = ( $locale != 'en_US' &&  $translated != $value ) ? $value . ' (' . $translated . ')' : $value;
		} else {
			$tvalue = $value;
		}
		return $tvalue;
	}

	/**
	 * Insert popup in quickedit at end (filter quick_edit_custom_box - template.php)
	 *
	 * @since 1.8.9
	 *
	 * hook with only two params - no $post_ID - populated by.. || $type != 'post' || $type != 'page'
	 *
	 */
	function languages_custom_box ( $col, $type ) {
		if ( 'edit-tags' == $type )
			return;
		if( $col != TAXONAME )
			return;

		$listlanguages = $this->get_listlanguages();
		$margintop = ($type == 'page' ) ? 'toppage' : 'toppost';
		?>

		<fieldset class="inline-edit-language langquickedit <?php echo $margintop; ?>" ><legend><em><?php _e('Language','xili-language') ?></em></legend>

			<select name="xlpop" id="xlpop">
			<option value=""> <?php _e('undefined','xili-language') ?> </option>
			<?php foreach ($listlanguages as $language) {
				echo '<option value="'.$language->slug.'">'.__($language->description, 'xili-language').'</option>';
			// no preset values now (see below)
			}
			?>
			</select>
		</fieldset>


	<?php
	}

	/**
	 * script used in quick and bulk edit
	 *
	 * @since 2.9.10
	 *
	 */
	function quick_edit_add_script () {
		$suffix = defined( 'WP_DEBUG') && WP_DEBUG ? '.dev' : '.min';
		wp_enqueue_script( 'xl-admin-edit', plugin_dir_url ( $this->file_file ) . 'js/xl_quick_edit'.$suffix.'.js', array( 'jquery', 'inline-edit-post' ), '', true );
	}

	/**
	 * Insert popup in BULK quickedit at end (filter bulk_edit_custom_box - template.php)
	 *
	 * @since 1.8.9.3
	 *
	 * hook with only two params - no $post_ID - populated by.. || $type != 'post' || $type != 'page'
	 *
	 */
	function hidden_languages_custom_box ( $col, $type ) {
		if( $col != TAXONAME ) {
			return;
		}
		$listlanguages = $this->get_listlanguages();
		$margintop = ($type == 'page' ) ? '-40px' : '0';
		?>

		<label class="alignright">
			<span class="title"><?php _e( 'Language','xili-language' ); ?></span>
			<select name="xlpop" id="xlpop">
			<option value="-1"> <?php _e('&mdash; No Change &mdash;') ?> </option>
			<option value="*"> <?php _e('undefined','xili-language') ?> </option>
			<?php foreach ($listlanguages as $language) {
				echo '<option value="'.$language->slug.'">'.__($language->description, 'xili-language').'</option>';
			// no preset values now (see below)
			}
			?>
			</select>
		</label>
	<?php
	}

	/**
	*
	* called by /js/nav-menu.xxx.js when displaying results in nav menu settings
	* @since 2.9.20
	*
	* @updated 2.12.2
	*/
	function ajax_get_menu_infos () {

		$menu_slug = ( isset( $_POST[ 'menu_slug' ] ) && !empty( $_POST[ 'menu_slug' ] ) ) ? $_POST[ 'menu_slug' ] : 'no-xl-menu' ;

		$term_data = term_exists( $menu_slug, 'nav_menu' );
		$menu_id = (is_array( $term_data )) ? $term_data['term_id'] : 0;
		$menu_obj = wp_get_nav_menu_object( (int) $menu_id );
		if ( $menu_obj )
			echo '<strong>' . wp_html_excerpt( $menu_obj->name, 40, '&hellip;' ) . '</strong>';
		else
			echo '<strong>' . sprintf(__('Warning: Unavailable menu with slug %s', 'xili-language'), $menu_slug ) . '</strong>';

		die();
	}

	/**
	 * language saved via ajax for bulk_edit
	 *
	 * @since 2.9.10
	 *
	 */
	function save_bulk_edit_language() {
		// get our variables
		$post_IDs = ( isset( $_POST[ 'post_ids' ] ) && !empty( $_POST[ 'post_ids' ] ) ) ? $_POST[ 'post_ids' ] : array();
		$assigned_lang = ( isset( $_POST[ 'assigned_lang' ] ) && !empty( $_POST[ 'assigned_lang' ] ) ) ? $_POST[ 'assigned_lang' ] : NULL;
		// if everything is in order
		if ( !empty( $post_IDs ) && is_array( $post_IDs ) && !empty( $assigned_lang ) ) {

			$listlanguages = $this->get_listlanguages () ;


			foreach( $post_IDs as $post_ID ) {

				$previous_lang = $this->get_post_language ( $post_ID ) ;

				if ( "-1" != $assigned_lang && "*" != $assigned_lang) {

					if ( $assigned_lang != $previous_lang ) {

						foreach ($listlanguages as $language) {

							$target_id = get_post_meta( $post_ID, QUETAG.'-'.$language->slug, true );
							if ( $target_id != "" ) {
								if ( $previous_lang != '' ) delete_post_meta( $target_id, QUETAG.'-'.$previous_lang );
								update_post_meta( $target_id, QUETAG.'-'.$assigned_lang, $post_ID );
							}
						}
					}


					wp_set_object_terms( $post_ID, $assigned_lang, TAXONAME );
				} else if ( "*" == $assigned_lang ) { // undefined

					foreach ($listlanguages as $language) {
							//delete_post_meta( $post_ID, QUETAG.'-'.$language->slug ); // erase translated because undefined - not yet
						$target_id = get_post_meta( $post_ID, QUETAG.'-'.$language->slug, true );
						if ( $target_id != "" ) {
							delete_post_meta( $target_id, QUETAG.'-'.$previous_lang );
						}
					}

					wp_delete_object_term_relationships( $post_ID, TAXONAME );
				}


			}
		}
		die();
	}

	/**
	 * Add Languages selector in edit.php edit after Category Selector (hook: restrict_manage_posts)
	 *
	 * @since 1.8.9
	 * @updated 2.13.2 - restricted if not authorized - adapted message for XD
	 *
	 */
	function restrict_manage_languages_posts () {
		if ( defined ('XDMSG') ) $CPTs = array( XDMSG );
		$CPTs[] = 'page';
		$CPTs[] = 'post';
		$CPTs[] = 'attachment'; // 2.8.4.1

		$custompoststype = $this->xili_settings['multilingual_custom_post'] ;
		if ( $custompoststype != array()) {
			foreach ( $custompoststype as $key => $customtype ) {
				if ( $customtype['multilingual'] == 'enable' ) {
					$CPTs[] = $key;
				}
			}
		}
		$post_type = get_post_type();

		if ( in_array ( $post_type, $CPTs) ) {

			$listlanguages = $this->get_listlanguages();

			$without = ( defined ('XDMSG') && $post_type == XDMSG ) ? __('Only msgid', 'xili-language') : __('Without language', 'xili-language') ;
			$view_all = ( defined ('XDMSG') && $post_type == XDMSG ) ? __('All msg', 'xili-language') : __('View all languages','xili-language') ;
			$cpt_name = ( defined ('XDMSG') && $post_type == XDMSG ) ? __('msgstr in %s', 'xili-language') : '%s' ;
			?>
			<select name="<?php echo QUETAG ?>" id="<?php echo QUETAG ?>" class='postform'>
				<option value=""> <?php echo $view_all; ?> </option>

				<option value="<?php echo LANG_UNDEF ?>" <?php selected( ( isset ( $_GET[QUETAG] ) && $_GET[QUETAG] == LANG_UNDEF ), true, true ); ?> > <?php echo $without; ?> </option>

				<?php foreach ($listlanguages as $language) {
					//$selected = ( isset ( $_GET[QUETAG] ) && $language->slug == $_GET[QUETAG] ) ? "selected=selected" : "" ;
					$selected = selected ( ( isset ( $_GET[QUETAG] ) && $language->slug == $_GET[QUETAG] ), true, false );
					echo '<option value="'.$language->slug.'" '. $selected .' >'. sprintf( $cpt_name, __($language->description, 'xili-language') ) .'</option>';
				}
				?>
				</select>
			<?php
		}

	}

/******************************* TAXONOMIES ****************************/

	function xili_manage_tax_column_name ( $cols ) {

		$ends = array('posts');
		$end = array();
		foreach( $cols AS $k=>$v ) {
			if(in_array($k, $ends)) {
				$end[$k] = $v;
				unset($cols[$k]);
			}
		}
		$cols[TAXONAME] = __('Language','xili-language');
		$cols = array_merge($cols, $end);

		$this->local_theme_mos = $this->get_localmos_from_theme() ;

		return $cols;
	}

	function xili_manage_tax_column ( $content, $name, $id ) {
		if ( $name == TAXONAME || $name == 'description' ) {
			global $taxonomy ;
			$tax = get_term((int)$id , $taxonomy ) ;
			$a = '<div class="taxinmoslist" >';

			$text = ( $name == TAXONAME ) ? $tax->name : $tax->description;

			$the_translation_results = $this->is_msg_saved_in_localmos ( $text, 'msgid', '', 'array' );

			if ( $the_translation_results ) {
				foreach ( $the_translation_results as $lang_slug => $translation ) {
					$a .= '<span class="lang-'. $lang_slug .'" >'. $translation['lang_name'] .'</span>&nbsp;:&nbsp;';
					$a .= $translation['msg_id_str']['msgstr'] . '<br />';
				}

			} else {
				$a .= __( 'need mo file', 'xili-language' )." ";
			}
			$a .= '</div>';

			return $content.$a; // 2.8.1 - to have more than one filter for this column ! #21222 comments...

		} else {
			return $content ; // to have more than one added column 2.8.1
		}
	}

	function xili_manage_tax_action ( $actions, $tag ) {
		return $actions;
	}

	function show_translation_msgstr ( $tag, $taxonomy ) {
		if ( !class_exists('xili_dictionary' ) ) {
		?>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="description"><?php _e('Translated in', 'xili-language'); ?></label></th>
			<td>
			<?php
			echo '<fieldset class="taxinmos" ><legend>'.__('Name').'</legend>';
			$a = $this->is_msg_saved_in_localmos ( $tag->name, 'msgid', '', 'single' ); echo $a[0];

			echo '</fieldset><br /><fieldset class="taxinmos" ><legend>'.__('Description').'</legend>';
			$a = $this->is_msg_saved_in_localmos ( $tag->description, 'msgid', '', 'single' ); echo $a[0];

			echo '</fieldset>';

			?>
			<p><em><?php _e( 'This list above gathers the translations of name and description saved in current local-xx_XX.mo files of the current theme.', 'xili-language'); ?></em></p>
			</td>
		</tr>

		<?php
		}
	}

	/**
	 * Update msgid list when a term is created
	 *
	 * @updated 2.8.4.2
	 *
	 */
	function update_xd_msgid_list ( $taxonomy ) {
		if ( class_exists ('xili_dictionary') ) {
			global $xili_dictionary;

			if ( isset ( $_POST['tag-name'] ) && $_POST['tag-name'] != '' ) {
				$nbterms = $xili_dictionary->xili_read_catsterms_cpt( $taxonomy, $xili_dictionary->local_tag );

				if ( $nbterms[0] + $nbterms[1] > 0 )
					echo '<p>' . sprintf( __( 'xili-dictionary: msgid list updated (n=%1s, d=%2s', 'xili-dictionary' ), $nbterms[0], $nbterms[1] ) . ')</p>';	}

		} else {
			echo '<p><strong>' . __( 'xili-dictionary plugin is not active to prepare language local .po files.', 'xili-language' ). '</strong></p>';
		}
	}


/******************************* MO TOOLS FOR TAXONOMIES AND LOCAL VALUES ****************************/

	/**
	 * test if line is in entries
	 * @since 2.6.0
	 */
	function is_msg_in_entries ( $msg, $type, $entries, $context ) {
		foreach ($entries as $entry) {
			$diff = 1;
			switch ( $type ) {
				case 'msgid' :
					$diff = strcmp( $msg , $entry->singular );
					if ( $context != "" ) {
						if ( $entry->context != null ) {
							$diff += strcmp( $context , $entry->context );
						}
					}
					break;
				case 'msgid_plural' :
					$diff = strcmp( $msg , $entry->plural );
					break;
				case 'msgstr' :
					if ( isset ( $entry->translations[0] ) )
					$diff = strcmp( $msg , $entry->translations[0] );
					break;
				default:
					if ( false !== strpos ( $type, 'msgstr_' ) ) {
						$indice = (int) substr ( $type, -1) ;
						if ( isset ( $entry->translations[$indice] ) )
							$diff = strcmp( $msg , $entry->translations[$indice] );
					}
			}

			//if ( $diff != 0) { echo $msg.' i= '.strlen($msg); echo $entry->singular.') e= '.strlen($entry->singular); }
			if ( $diff == 0) return true;
		}
	return false;
	}

	function get_msg_in_entries ( $msg, $type, $entries, $context ) {
		foreach ($entries as $entry) {
			$diff = 1;
			switch ( $type ) {
				case 'msgid' :
					$diff = strcmp( $msg , $entry->singular );
					if ( $context != "" ) {
						if ( $entry->context != null ) {
							$diff += strcmp( $context , $entry->context );
						}
					}
					break;
				case 'msgid_plural' :
					$diff = strcmp( $msg , $entry->plural );
					break;
				case 'msgstr' :
					if ( isset ( $entry->translations[0] ) )
						$diff = strcmp( $msg , $entry->translations[0] );
					break;
				default:
					if ( false !== strpos ( $type, 'msgstr_' ) ) {
						$indice = (int) substr ( $type, -1) ;
						if ( isset ( $entry->translations[$indice] ) )
							$diff = strcmp( $msg , $entry->translations[$indice] );
					}
			}

			//if ( $diff != 0) { echo $msg.' i= '.strlen($msg); echo $entry->singular.') e= '.strlen($entry->singular); }
			if ( $diff == 0) {
				if ( isset ( $entry->translations[0] ) ) {
					return array( 'msgid' => $entry->singular , 'msgstr' => $entry->translations[0] );
				} else {
					return array() ;
				}
			}
		}
	return array() ;
	}


	/**
	 * Detect if cpt are saved in theme's languages folder
	 * @since 2.0
	 *
	 */
	function is_msg_saved_in_localmos ( $msg, $type, $context = "", $mode = "list" ) {
		$thelist = array();
		$thelistsite = $the_translation_results = array();
		$outputsite = "";
		$output = "";

		$listlanguages = $this->get_listlanguages();

		foreach ($listlanguages as $reflanguage) {
			if ( isset($this->local_theme_mos[$reflanguage->slug]) ) {
				if ( $mode == "list" && $this->is_msg_in_entries ( $msg, $type, $this->local_theme_mos[$reflanguage->slug], $context ) ) {
					$thelist[] = '<span class="lang-'. $reflanguage->slug .'" >'. $reflanguage->name .'</span>';

				} else if ( $mode == "single" || $mode == 'array' ) {
					$res = $this->get_msg_in_entries ( $msg, $type, $this->local_theme_mos[$reflanguage->slug], $context ) ;
					if ( $res != array () ) {
						$thelist[$reflanguage->name] = $res ;
						if ( $mode == 'array' ) {
							$the_translation_results[$reflanguage->slug] = array( 'lang_name' => $reflanguage->name, 'msg_id_str' => $res  );
						}
					}
				}
			}

			if ( is_multisite() ) {
				if ( isset($this->local_site_mos[$reflanguage->slug]) ) {
					if ( $this->is_msg_in_entries ( $msg, $type, $this->local_site_mos[$reflanguage->slug], $context ) )
						$thelistsite[] = '<span class="lang-'. $reflanguage->slug .'" >'. $reflanguage->name .'</span>';
				}
			}

		}

		if ( $mode == "list" ) {

			$output = ( $thelist == array() ) ? '<br /><small><span style="color:black" title="'.esc_attr__("No translations saved in theme's .mo files","xili-dictionary").'">**</span></small>' : '<br /><small><span style="color:green" title="'.__("Original with translations saved in theme's files: ","xili-dictionary").'" >'. implode(' ',$thelist).'</small></small>';

			if ( is_multisite() ) {

				$outputsite = ( $thelistsite == array()) ? '<br /><small><span style="color:black" title="'.esc_attr__("No translations saved in site's .mo files","xili-dictionary").'">**</span></small>' : '<br /><small><span style="color:green" title="'.__("Original with translations saved in site's files: ","xili-dictionary").'" >'. implode(', ',$thelistsite).'</small></small>';

			}

		} else if ( $mode == "single" ) {

			if ($thelist == array()) {

				$output = __('Not yet translated in any language','xili-language') .'<br />';
			} else {
				$output = '';
				foreach ( $thelist as $key => $msg ) {

					$output .= '<span class="lang-'. strtolower ( $key ) .'" >' . $key . '</span> : ' . $msg['msgstr'] . '<br />';
				}
			}
		} else if ( $mode == 'array' ) {
			return  $the_translation_results ;
		}

		return array ( $output, $outputsite ) ;

	}

	/**
	 * create an array of local mos content of theme
	 *
	 * @since 2.6.0
	 */
	function get_localmos_from_theme() {
		$local_theme_mos = array();

			$listlanguages = $this->get_listlanguages();

			if ( is_multisite() ) {
				if ( ( $uploads = wp_upload_dir() ) && false === $uploads['error'] ) {
					$folder = $uploads['basedir']."/languages";
				}
			}

			foreach ( $listlanguages as $reflanguage ) {
				if ( is_multisite() ) {
					$folder_file = $folder . '/local-' . $reflanguage->name . '.mo';
				} else {
					$folder_file = '';
				}

				$res = $this->pomo_import_MO ( $reflanguage->name, $folder_file, true ); // local only
				if ( false !== $res ) $local_theme_mos[$reflanguage->slug] = $res->entries;
			}

		return $local_theme_mos;
	}

	/**
	 * Import MO file in class PO
	 *
	 *
	 * @since 1.0.2 - only WP >= 2.8.4
	 * @updated 1.0.5 - for wpmu
	 * @param lang
	 * @param $mofile since 1.0.5
	 */
	function pomo_import_MO ( $lang = "", $mofile = "", $local = false ) {
		$mo = new MO();

		if ( $mofile == "" && $local == true ) {
			$mofile = $this->get_template_directory.$this->xili_settings['langs_folder'] .'/'.'local-'.$lang.'.mo';
		} else if ( '' == $mofile ) {
			$mofile = $this->get_template_directory.$this->xili_settings['langs_folder'] .'/'.$lang.'.mo';
		}

		if ( file_exists($mofile) ) {
			if ( !$mo->import_from_file( $mofile ) ) {
				return false;
			} else {

				return $mo;
			}
		} else {
			return false;
		}
	}

/******************************* LINKS ****************************/

	/**
	 * @updated 1.8.0
	 */
	function add_custom_box_in_link() {

		add_action( 'add_meta_boxes_link', array( &$this,'new_box' ) );
	}



	/**
	 * Box, action and function to set language in edit-link-form
	 * @ since 1.8.5
	 */
	function new_box () {
		add_meta_box('linklanguagediv', __("Link's language","xili-language"), array(&$this,'link_language_meta_box'), 'link', 'side', 'core');
	}

	function link_language_meta_box( $link) {

		if (isset( $link->link_id )) {
			$ress = wp_get_object_terms( $link->link_id, 'link_'.TAXONAME);
		} else {
			$ress = false;
		}
		$curlangname = "";
		if ( $ress ) {
			$obj_term = $ress[0];
			if ( '' != $obj_term->name ) :
				$curlangname = $obj_term->name;
			endif;
		}

		echo '<h4>'.__('Check the language for this link','xili-language').'</h4><div style="line-height:1.7em;">';
		// built the check series with saved check if edit
		$listlanguages = get_terms_of_groups_lite ( $this->langs_group_id, TAXOLANGSGROUP, TAXONAME, 'ASC' );
		$l = 2;
		foreach ( $listlanguages as $language ) {
			if ( $l % 3 == 0 && $l != 3) { echo '<br />'; }
			?>

				<label class="check-lang selectit" for="xili_language_check_<?php echo $language->slug ; ?>"><input id="xili_language_check_<?php echo $language->slug ; ?>" name="xili_language_set" type="radio" value="<?php echo $language->slug ; ?>" <?php checked( $curlangname, $language->name, true ); ?> /> <?php _e($language->description, 'xili-language'); ?></label>

				<?php } /*link to top of sidebar*/?>
				<br /><label class="check-lang selectit" for="xili_language_check" ><input id="xili_language_check_ever" name="xili_language_set" type="radio" value="ev_er" <?php checked( $curlangname, "ev_er", true ); ?> /> <?php _e('Ever','xili-language') ?></label>
				<label class="check-lang selectit" for="xili_language_check" ><input id="xili_language_check" name="xili_language_set" type="radio" value="" <?php checked( $curlangname, "", true); ?> /> <?php _e('undefined','xili-language') ?></label><br /></div>
				<br /><small>© xili-language <?php echo XILILANGUAGE_VER; ?></small>
		<?php

	}

	function print_styles_link_edit () {
		echo "<!---- xl options css links ----->\n";
		echo '<style type="text/css" media="screen">'."\n";
			echo ".check-lang { border:solid 1px grey; margin:1px 0px; padding:3px 4px; width:45%; display:inline-block; }\n";
		echo "</style>\n";

		if ( $this->exists_style_ext && $this->xili_settings['external_xl_style'] == "on" ) wp_enqueue_style( 'xili_language_stylesheet' );
	}

	/**
	 * Action and filter to add language column in link-manager page
	 * @ since 1.8.5
	 */


	function xili_manage_link_column_name ( $cols ) {
				$ends = array('rel', 'visible', 'rating'); // insert language before rel
				$end = array();
				foreach($cols AS $k=>$v) {
					if(in_array($k, $ends)) {
						$end[$k] = $v;
						unset($cols[$k]);
					}
				}
				$cols[TAXONAME] = __('Language','xili-language');
				$cols = array_merge($cols, $end);
				return $cols;
	}

	function manage_link_lang_column ( $column_name, $link_id ) {

		if ( $column_name != TAXONAME )
					return;
		$ress = wp_get_object_terms($link_id, 'link_'.TAXONAME);
		if ( $ress ) {
			$obj_term = $ress[0];
			echo $obj_term->name ;
		}
	}

	/**
	 * To edit language when submit in edit-link-form
	 * @ since 1.8.5
	 */
	function edit_link_set_lang ( $link_id ) {
		// create relationships with link_language taxonomy
				$sellang = $_POST['xili_language_set'];
				// test if exist in link taxinomy or create it
				$linklang = term_exists($sellang,'link_'.TAXONAME);
				if ( !$linklang ) {
					$lang = term_exists($sellang,TAXONAME);
					$lang_term = get_term($lang[ 'term_id' ], TAXONAME );
					if (!is_wp_error( $lang_term ))
						wp_insert_term( $lang_term -> name, 'link_'.TAXONAME , array ( 'alias_of' => '', 'description' => $lang_term -> description, 'parent' => 0, 'slug' => $lang_term->slug ) );
				}

				if ("" != $sellang) {
					wp_set_object_terms($link_id, $sellang, 'link_'.TAXONAME);
				} else {
					wp_delete_object_term_relationships( $link_id, 'link_'.TAXONAME );
				}
	}

	/**
	 * Function to manage mo files downloaded from automattic
	 *
	 *
	 */
	function download_mo_from_automattic( $locale = 'en_US', $version , $theme_name = "", $upgrade = 0 ) {

		$mofile = WP_LANG_DIR."/$locale.mo";

		// does core file exists in current installation ?
		if ((file_exists($mofile) && $upgrade == 0 ) || $locale == 'en_US')
			return true;

		// does language directory exists in current installation ?
		if (!is_dir(WP_LANG_DIR)) {
			if (!@mkdir(WP_LANG_DIR))
				return false;
		}

		// will first look in tags/ (most languages) then in branches/ (only Greek ?)
		$automattic_locale_root = 'http://svn.automattic.com/wordpress-i18n/'.$locale;
		$automattic_locale_root = $automattic_locale_root.'/branches/' ;	// replaces /tags/ 2014-02-01


		$args = array('timeout' => 30, 'stream' => true);

		if ( $upgrade != 2 ) { // only core files
		// try to download the file

			$resp = wp_remote_get($automattic_locale_root."$version/messages/$locale.mo", $args + array('filename' => $mofile));
			if (is_wp_error($resp) || 200 != $resp['response']['code'])
				continue;

			// try to download ms and continents-cities files if exist (will not return false if failed)
			// with new files introduced in WP 3.4
			foreach (array('ms', 'continents-cities', 'admin', 'admin-network') as $file) {
				$url = $automattic_locale_root."$version/messages/$file-$locale.mo";
				if ( $this->url_exists( $url ) )
					wp_remote_get( $url, $args + array('filename' => WP_LANG_DIR."/$file-$locale.mo") );
			}
		}

				// try to download theme files if exist (will not return false if failed)
				// FIXME not updated when the theme is updated outside a core update
		if ( in_array ($theme_name, $this->embedded_themes ) ) {
			$url = $automattic_locale_root."$version/messages/$theme_name/$locale.mo";
			if ( $this->url_exists( $url ) ) {
				wp_remote_get($url, $args + array('filename' => get_theme_root()."/$theme_name/languages/$locale.mo"));
			}
		}
		return true;

	}

	// GlotPress version sub-folder

	function glotPress_version_folder ( $version ) {
		$version_parts = explode ( '.', $version );
		$version_to_search = $version_parts[0] . '.' . $version_parts[1] . '.x' ;
		$list = wp_remote_get( 'http://translate.wordpress.org/api/projects/wp/' );
		if ( is_wp_error( $list ) || wp_remote_retrieve_response_code( $list ) !== 200 ) {
			return '' ;
		}
		$list = json_decode( wp_remote_retrieve_body( $list ) );
		if ( is_null( $list ) )
			return '' ;

		$filtered = wp_list_filter( $list->sub_projects, array( 'name' => $version_to_search )) ; // search in list  of sub_projects 2014-02-01

		$filtereds = array_shift( $filtered );
		if ( empty( $filtereds ) )
			return '' ;

		return $filtereds->slug ;
	}

	/**
	 * Download from translation.wordpress.org
	 *
	 */
	function download_mo_from_translate_wordpress( $locale = 'en_US', $version , $theme_name = "", $upgrade = 0 ) {

		$locale_subfolder = $this->check_versions_in_glotpress ( $locale, $version ) ;
		// return subfolder at WP (en, en-ca, fr, zn-tw…)
		if ( $locale_subfolder === false ) return false;

		$version_folder = $this->glotPress_version_folder ( $version ) ;

		if ( '' == $version_folder ) $version_folder = 'dev'; // 2.12.2

		$mofile = WP_LANG_DIR."/$locale.mo";

		// does file exists in current installation ?
		if ((file_exists($mofile) && $upgrade == 0 ) || $locale == 'en_US')
			return true;

		// does language directory exists in current installation ?
		if (!is_dir(WP_LANG_DIR)) {
			if (!@mkdir(WP_LANG_DIR))
				return false;
		}

		// will first look in tags/ (most languages) then in branches/ (only Greek ?)
		$translate_wordpress_root = 'http://translate.wordpress.org/projects/wp/'.$version_folder .'/';

		$suffix = 'mo'; // tested with po

		// 'http://translate.wordpress.org/projects/wp/3.5.x/admin/fr/default/export-translations'
		// GET ( sent by verified jquery in above url)

		$sub_folder_array = array (
			'default' => '%lang%/default',
			'admin' => 'admin/%lang%/default',
			'admin-network' => 'admin/network/%lang%/default',
			'continents-cities' => 'cc/%lang%/default',
		);
		if ( $upgrade != 2 ) { // only theme files
			foreach ( $sub_folder_array as $prename => $one_subfolder ) {

				$url = $translate_wordpress_root . str_replace ( '%lang%', $locale_subfolder, $one_subfolder ) . '/export-translations?format='.$suffix ;

				$fileprename = ( $prename != 'default' ) ? $prename . '-' : '' ;
				$request = wp_remote_get( $url , array( 'filename' => WP_LANG_DIR."/".$fileprename.$locale.".".$suffix , 'timeout' => 15, 'stream' => true, 'body' => array() ) );

				if ( 200 != wp_remote_retrieve_response_code( $request ) ){
					unlink( WP_LANG_DIR."/".$fileprename.$locale.".".$suffix );
					// see /wp-includes/file.php
				}
			}
		}

		if ( in_array ($theme_name, $this->embedded_themes ) ) {

		// thanks for format - markoheijnen - http://buddypress.trac.wordpress.org/raw-attachment/ticket/4857/translatecode-003.php, http://buddypress.trac.wordpress.org/attachment/ticket/4857/translatecode-003.php

		// temp patch for twentythirteen 20130503 - fixed 20130504 via polyglots blog
			//$theme_subfolder = ( $theme_name == 'twentythirteen' ) ? 'twenty-thirteen' : $theme_name ;

			$url = $translate_wordpress_root . $theme_name . '/' . $locale_subfolder . '/default/export-translations?format='.$suffix ;
			$request = wp_remote_get( $url , array( 'filename' => get_theme_root()."/$theme_name/languages/$locale.".$suffix , 'timeout' => 15, 'stream' => true, 'body' => array() ) );


			if ( 200 != wp_remote_retrieve_response_code( $request ) ){
				@unlink( get_theme_root()."/$theme_name/languages/$locale.".$suffix );
				// see /wp-includes/file.php
			}

		}
		return true;
	}

	function url_exists($url) {
		//if (!$fp = curl_init($url)) return false;
		//return true;
		$file = $url;
		$file_headers = @get_headers($file);
		if( $file_headers[0] == 'HTTP/1.1 404 Not Found' ) {
			return false;
		} else {
			return true;
		}
	}

    /**
     * set new default value screen options for new admin user
     *
     * @since 2.9.30
     */
	function default_nav_menus_screen_options ( $meta_id, $object_id, $meta_key, $_meta_value ) {

		if ( $meta_key == 'metaboxhidden_nav-menus' ) {

			if ( user_can ( $object_id , 'edit_theme_options' ) ) {
				$intersect = array_intersect ( $_meta_value , array( "insert-xl-list", "insert-xlspage-list", "insert-xlmenus-list" ) );
				if ( $intersect != array() )
					update_user_option( $object_id, 'metaboxhidden_nav-menus', array_diff ( $_meta_value , array( "insert-xl-list", "insert-xlspage-list", "insert-xlmenus-list" ) ), true );
			}
		}
	}


	/*
	 * insert in the widgets forms two dropdowns to define visibility rules according current language
	 *
	 * @since 2.20.3
	 *
	 * @param object $widget
	 */
	public function widget_visibility_admin ( $widget, $return, $instance ) {

		$dropdown = '<select name="' . $widget->id.'_lang_show">';
		$selected = ( isset ( $instance['xl_show'] )) ? selected( $instance['xl_show'], 'show', false ) : '';
		$dropdown .= '<option value="show" ' . $selected . '>' . __('Show', 'xili-language') . '</option>';
		$selected = ( isset ( $instance['xl_show'] )) ? selected( $instance['xl_show'], 'hidden', false ) : '';
		$dropdown .= '<option value="hidden" ' . $selected . '>' . __('Hidden', 'xili-language') . '</option>';
		$dropdown .= '</select>';

		printf('<hr /><label for="%1$s">%2$s %3$s</label><br />',
			esc_attr( $widget->id.'_lang_rule'),
			__('Visibility of this widget:', 'xili-language'),
			$dropdown
		);

		$dropdown = '<select name="' . $widget->id.'_lang_rule">';
		$dropdown .= '<option value="0">' . __('All languages', 'xili-language') . '</option>';
		foreach ( $this->langs_slug_fullname_array as $slug => $name ) {
			$selected = ( isset ( $instance['xl_lang'] )) ? selected( $instance['xl_lang'], $slug, false ) : '';
			$dropdown .= '<option value="' . $slug . '" ' . $selected . '>' . $name . '</option>';
		}
		$dropdown .= '</select>';

		printf('<label for="%1$s">%2$s %3$s</label><br /><small>%4$s</small><hr />',
			esc_attr( $widget->id.'_lang_rule'),
			__('when:', 'xili-language'),
			$dropdown,
			'© xili-language'
		);
	}

	/*
	 * called when widget options are saved (filter: widget_update_callback)
	 * add rules (language visibility) associated to the widget
	 *
	 * @since 2.20.3
	 *
	 * @param array $instance widget options
	 * @param array $new_instance not used
	 * @param array $old_instance not used
	 * @param object $widget WP_Widget object
	 * @return array widget options
	 */
	public function widget_update_callback( $instance, $new_instance, $old_instance, $widget ) {
		if ( !empty( $_POST[$key = $widget->id.'_lang_show'] ) && in_array( $_POST[$key], array('show', 'hidden') ) )
			$instance['xl_show'] = $_POST[$key];
		else
			unset($instance['xl_show']);

		if ( !empty( $_POST[$key = $widget->id.'_lang_rule'] ) && in_array( $_POST[$key], array_keys($this->langs_slug_fullname_array) ) )
			$instance['xl_lang'] = $_POST[$key];
		else
			unset($instance['xl_lang']);

		return $instance;
	}


	// with xili-language, it is now possible to export/import xml with language for all authorized cpts
	function message_export_limited() {
		echo '<div class="error"><p>'. __ ('WARNING: With xili-language, language taxonomy is now ready to be imported from XML file generated here (All content choice). <br />So, before to import in a new website, be sure that xili-language plugins trilogy is active in this target site.', 'xili-language') . '</p>'
		.'<p>'. __ ('Therefore, before importing, verify that custom post types are registered in this new clean install.', 'xili-language') . '</p></div>';
	}

	/**
	 * Contextual help
	 *
	 * @since 1.7.0
	 * @updated 2.4.1, 2.6.2, 2.8.8
	 */
	function add_help_text( $contextual_help, $screen_id, $screen ) {

		if ( $screen->id == 'nav-menus' ) { // 2.8.8
			$wikilink = $this->wikilink . '/index.php/Xili-language:_languages_list_insertion_in_nav_menu';
			$to_remember =
				'<p><em>' . __('To show insertion metabox, remember to check them inside Screen Options.','xili-language') . '</em></p>' .
				'<p><strong>' . __('Things to remember to insert Languages list:','xili-language') . '</strong></p>' .
				'<ul>' .
					'<li>' . __('Checking radio button, choose type of languages list to insert.','xili-language') . '</li>' .
					'<li>' . __('Click the button - Add to Menu -.','xili-language') . '</li>' .
					'<li>' . __('Drag and drop to the desired place.','xili-language') . '</li>' .
					'<li>' . __('Do not modify content of url and label. These infos will be used to generate the final languages list according position in website during navigation.','xili-language') . '</li>' .
				'</ul>'.
				'<p><strong>' . __('Things to remember to insert Pages Selection:','xili-language') . '</strong></p>' .
				'<ul>' .
					'<li>' . __('With prefix - include= - fill the list of page IDs where sub-selection will be done according current language','xili-language') . '</li>' .
					'<li><em>' . __('Args is like in function wp_list_pages, example: <em>include=11,15</em><br />Note: If args kept empty, the selection will done on all pages (avoid it).','xili-language') . '</em></li>' .
					'<li>' . __('Check the input field line,','xili-language') . '</li>' .
					'<li>' . __('Click the button - Add to Menu -.','xili-language') . '</li>' .
					'<li>' . __('Drag and drop to the desired place.','xili-language') . '</li>' .

				'</ul>'.
				'<p><strong>' . __('Things to remember to insert Menus Selection:','xili-language') . '</strong></p>' .
				'<ul>' .
				'<li>' . __('After creating menu structures containing items linked to a language (but not assigned to a loacation), select a menu structure for each language in Menus selection box.','xili-language') . '</li>' .

				'<li>' . __('Check after selecting,','xili-language') . '</li>' .
				'<li>' . __('Click the button - Add to Menu -.','xili-language') . '</li>' .
				'<li>' . __('Drag and drop to the desired place.','xili-language') . '</li>' .
				'<li><em>' . __('If after changing or removing menu, you see - unavailable menu - in Menu list insertion point box, you must remove this insertion point and create a new one with new menus.','xili-language') . '</em></li>' .

				'</ul>'.
				'<p>' . sprintf(__('%sMost recent infos about xili-language trilogy%s','xili-language'), '<a href="'.$this->fourteenlink.'" target="_blank">' , '</a>' ) . '</p>'.
				'<p>' . sprintf(__('<a href="%s" target="_blank">Xili Wiki Documentation</a>','xili-language'), $wikilink ) . '</p>' ;

			$screen->add_help_tab( array(
				'id'		=> 'xili-language-list',
				'title'		=> sprintf( __('About %s insertion points', 'xili-language'), '[©xili]'),
				'content'	=> $to_remember,
		));

		}
		if ( $screen->id == 'attachment' ) { // 2.18.1
			$more_infos =
				'<p><strong>' . __('About multilingual features:', 'xili-language') . '</strong></p>' .
				'<ul>' .
				'<li>' . __('With media attachment, in multilingual context, it is possible to clone an attachment with the same media. The file is not duplicated. Title, Legend, Alt text can be written in each language.','xili-language') . '</li>' .
				'<li><em>' . sprintf(__('Fields under the description are available to assign and clone. A side box %s contain also infos and links to go to another clone in other languages.','xili-language'), '<strong>' . __( 'Multilingual informations', 'xili-language') . '</strong>' ) . '</em></li>' .
				'</ul>'.
				'<p>' . sprintf(__('%sXili-language Plugin Documentation in WP repository%s','xili-language'), '<a href="'.$this->repositorylink .'" target="_blank">', '</a>' ). '</p>' .
				'<p>' . sprintf(__('%sMost recent infos about xili-language trilogy%s','xili-language'), '<a href="'.$this->fourteenlink.'" target="_blank">' , '</a>' ) . '</p>' ;

				$screen->add_help_tab( array(
				'id'      => 'more-media-infos',
				'title'   => sprintf( __('About %s multilingual features', 'xili-language'), '[©xili]'),
				'content' => $more_infos,
			));
		}
		if ( in_array ( $screen->id , array ('settings_page_language_page', 'settings_page_language_front_set', 'settings_page_language_expert', 'settings_page_language_files', 'settings_page_author_rules', 'settings_page_language_support') ) ) {

			$page_title[ 'settings_page_language_page' ] = __( 'Languages list', 'xili-language' ) ;
			$page_title[ 'settings_page_language_front_set' ] = __( 'Languages front-end settings', 'xili-language' ) ;
			$page_title[ 'settings_page_language_expert' ] = __( 'Settings for experts', 'xili-language' ) ;
			$page_title[ 'settings_page_author_rules' ] = __( 'Settings Authoring rules', 'xili-language' ) ;
			$page_title[ 'settings_page_language_files' ] = __( 'Managing MO files', 'xili-language' ) ;
			$page_title[ 'settings_page_language_support' ] = __( 'xili-language support', 'xili-language' ) ;

			$line[ 'settings_page_language_page' ] = __('In this page, the list of languages used by the multilingual website is set.','xili-language');
			$line[ 'settings_page_language_front_set' ] = __('Here, you decide what happens when a visitor arrives on the website homepage with his browser commonly set according to his mother language. Xili-language offers multiple ways according your content strategy.','xili-language');
			$line[ 'settings_page_language_expert' ] = __('This sub-page will present how to set navigation menu in multilingual context with xili-language.','xili-language');
			$line[ 'settings_page_author_rules' ] = __('This sub-page will present how to set authoring rules when creating translation.','xili-language') . '</li>' .
			'<li>' . __('When authors of post, page and custom post want to create a translation, it is possible to define what feature of original post can be copied to the post of target language (format, parent, comment or ping status,...). Some features are not ajustable (to be, it will be need premium services). For developer only: filters are available.', 'xili-language') ;

			// list
			$line[ 'settings_page_language_files' ] = __( 'This sub-page will help to import MO files from WordPress SVN.', 'xili-language' ) . '</li>' .
			'<li>' . __('Be aware that, before to be displayed, this page scans datas from servers, be patient, it takes time.','xili-language') . '</li>' .
			'<li>' . __('If the theme is a child theme, in the box containing infos about theme, a list of languages from the parent theme is shown.','xili-language') . '</li>' .
			'<li>' . sprintf ( __('If the option %s is checked, available translations from child are merged with those from parent. You can choose the priority: parent or child .mo. Local-xx_YY.mo have always priority.','xili-language'), "<em>" . __( 'MO merging between parent and child','xili-language') . "</em>" ) ;
			;

			$line[ 'settings_page_language_support' ] = __('This form to email to dev.xiligroup.com team your observations.','xili-language');

			$wiki_page[ 'settings_page_language_page' ] = '/index.php/Xili-language_settings:_the_list_of_languages,_line_by_line';
			$wiki_page[ 'settings_page_language_front_set' ] = '/index.php/Xili-language_settings:_Home_page_and_more...';
			$wiki_page[ 'settings_page_language_expert' ] = '/index.php/Xili-language:_navigation_menu';
			$wiki_page[ 'settings_page_language_files' ] = '/index.php/Xili-language:_managing_mo_files';
			$wiki_page[ 'settings_page_author_rules' ] = '/index.php/Xili-language:_managing_authoring_rules';
			$wiki_page[ 'settings_page_language_support' ] = '/index.php/Xili-language_settings:_Assistance,_support_form';

			$this_tab =
				'<p><strong>' . sprintf( __('About this tab %s:','xili-language'), $page_title[$screen->id] ) . '</strong></p>' .
				'<ul>' .
					'<li>' . $line[$screen->id] .'</li>' .
					'<li>' . sprintf(__('<a href="%s" target="_blank">Xili Wiki Post</a>','xili-language'), $this->wikilink . $wiki_page[$screen->id] ) . '</li>' .
				'</ul>' ;

			$to_remember =
				'<p><strong>' . __('Things to remember to set xili-language:','xili-language') . '</strong></p>' .
				'<ul>' .
					'<li>' . __('Verify that the theme is localizable (like kubrick, fusion or twentyten or others...).','xili-language') . '</li>' .
					'<li>' . __('Define the list of targeted languages.','xili-language') . '</li>' .
					'<li>' . __('Prepare .po and .mo files for each language with poEdit or xili-dictionary plugin.','xili-language') . '</li>' .
					'<li>' . __('If your website contains custom post type: check those which need to be multilingual. xili-language will add automatically edit meta boxes.','xili-language') . '</li>' .
				'</ul>' ;

			$more_infos =
				'<p><strong>' . __('For more information:') . '</strong></p>' .
				'<p>' . '<a href="'. $this->devxililink .'/xili-language" target="_blank">'. __('Xili-language Plugin Documentation','xili-language') . '</a></p>' .
				'<p>' . sprintf(__('<a href="%s" target="_blank">Xili Wiki Documentation</a>','xili-language'), $this->wikilink ) . '</p>' .
				'<p>' . '<a href="'. $this->forumxililink .'" target="_blank">'. __('Support Forums','xili-language') . '</a></p>' .
				'<p>' . '<a href="https://codex.wordpress.org/" target="_blank">' . __('WordPress Documentation','xili-language') . '</a></p>' ;

			$screen->add_help_tab( array(
				'id'      => 'this-tab',
				'title'   => __('About this tab','xili-language'),
				'content' => $this_tab,
			));

			$screen->add_help_tab( array(
				'id'      => 'to-remember',
				'title'   => __('Things to remember','xili-language'),
				'content' => $to_remember,
			));

			$screen->add_help_tab( array(
				'id'      => 'more-infos',
				'title'   => __('For more information', 'xili-language'),
				'content' => $more_infos,
			));

		}
		return $contextual_help;
	}

}

?>