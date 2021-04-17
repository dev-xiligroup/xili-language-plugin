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
 * 2016-07-29 - 2.21.2 - wp_get_theme
 * 2016-12-13 - 2.22.1 - fixes for 4.6 & 4.7
 *
 * 2017-04-21 - 2.22.4 - multilingual per post option - bulk edit
 *
 * 2018-05-08 - 2.23.0 - source - code rewritting
 * 2019-06-03 - 2.23.12 - source - code splitted with traits
 *
 * @package xili-language
 */

class Xili_Language_Admin extends Xili_Language {
	// traits set to organize class code
	use Xili_Admin\Xili_Admin_Edit;
	use Xili_Admin\Xili_Admin_Page_Languages_Settings;
	use Xili_Admin\Xili_Admin_Page_Frontend_Settings;
	use Xili_Admin\Xili_Admin_Page_Expert_Settings;
	use Xili_Admin\Xili_Admin_Page_Language_Files_Settings;
	use Xili_Admin\Xili_Admin_Page_Authoring_Rules_Settings;
	use Xili_Admin\Xili_Admin_Page_Support;

	use Xili_Admin\Xili_Admin_Page_Sideboxes;
	use Xili_Admin\Xili_Admin_Menus_Builder;
	use Xili_Admin\Xili_Admin_Language_Links_Settings;

	use Xili_Admin\Xili_Admin_Media_Settings;
	use Xili_Admin\Xili_Admin_Post_Edit_Language;
	use Xili_Admin\Xili_Admin_Pomo;
	use Xili_Admin\Xili_Admin_Help;

	// 2.5
	public $authorbrowserlanguage = ''; // author default browser language

	public $exists_style_ext = false; // test if external style exists in theme
	public $style_folder = ''; // where is xl-style.css
	public $style_flag_folder_path = ''; // where are flags
	public $style_message = '';

	public $devxililink = 'http://dev.xiligroup.com';
	public $forumxililink = 'https://wordpress.org/support/plugin/xili-language'; //http://dev.xiligroup.com/?post_type=forum'; - 2.20.2
	public $wikilink = 'http://wiki.xiligroup.org';
	public $glotpresslink = 'https://make.wordpress.org/polyglots/teams/'; // 2.19.3
	public $fourteenlink = 'http://2014.extend.xiligroup.org';
	public $repositorylink = 'https://wordpress.org/plugins/xili-language/';

	public $parent = null;
	public $news_id = 0; //for multi pointers
	public $news_case = array();
	public $admin_messages = array(); //set in #491
	public $user_locale = 'en_US';
	public $embedded_themes = array( 'twentyten', 'twentyeleven', 'twentytwelve', 'twentythirteen', 'twentyfourteen', 'twentyfifteen', 'twentysixteen', 'twentyseventeen', 'twentynineteen' ); // bundled themes

	public $subpage_titles = array(); // see add_menu_settings_pages
	public $xl_tabs = array();

	public $settings_frontend = 'xiliml_frontend_settings'; // tab 2
	public $settings_author_rules = 'xiliml_author_rules'; // tab 5
	public $settings_authoring_settings = 'xiliml_authoring_settings'; // tab 5

	public $default_locale = null; // from GP_locale

	public $flag_theme_page;  // used by add_menu
	public $custom_xili_flags = array( 'custom_xili_flag', 'admin_custom_xili_flag' ); // 2.16.4

	public $local_textdomain_loaded = array(); // to avoid multiple loading file...

	public $changelog = '*'; // used in welcome and pointer
	public $available_translations = array(); // from translation_install

	/**
	 * PHP 5 Constructor
	 */
	public function __construct( $xl_parent ) {

		$this->parent = $xl_parent; // to keep values built in parent filters...

		// need parent constructed values (third param - tell coming from admin-class //2.6
		parent::__construct( false, false, true );

		// vars shared between parent and admin class - 2.8.4.3
		$this->xili_settings = &$this->parent->xili_settings;

		$this->langs_list_options = &$this->parent->langs_list_options; // 2.8.6

		if ( ! class_exists( 'GP_Locales' ) ) {
			require_once XILILANGUAGE_PLUGIN_DIR . 'xili-includes/locales.php'; // from JetPack copied
		}

		$xl_locales = GP_Locales::instance();

		$this->examples_list = array();

		foreach ( $xl_locales->locales as $key => $one_locale ) {
			if ( isset( $one_locale->wp_locale ) && '' != $one_locale->wp_locale ) {
				/* translators: */
				$this->examples_list[ $one_locale->wp_locale ] = sprintf( _x( '%1$s/%2$s', 'locales', 'xili-language' ), $one_locale->english_name, $one_locale->native_name );
			} else {
				// a * inserted if no existing WP_locale declared...
				/* translators: */
				$this->examples_list[ $key ] = sprintf( _x( '%1$s/%2$s *', 'locales', 'xili-language' ), $one_locale->english_name, $one_locale->native_name );
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
		$this->multiple_lang = &$this->parent->multiple_lang; /* multiple language option - 2.22 */

		$this->langs_ids_array = &$this->parent->langs_ids_array;
		$this->langs_slug_name_array = &$this->parent->langs_slug_name_array;
		$this->langs_slug_fullname_array = &$this->parent->langs_slug_fullname_array;

		$this->langs_slug_shortqv_array = &$this->parent->langs_slug_shortqv_array;
		$this->langs_shortqv_slug_array = &$this->parent->langs_shortqv_slug_array;

		add_action( 'admin_init', array( &$this, 'init_roles' ) ); // 2.8 (in main file)
		add_action( 'admin_init', array( &$this, 'init_filters' ) ); // 2.22

		add_action( 'admin_head', array( &$this, 'xd_flush_permalinks' ) ); // 2.11
		add_filter( 'wp_get_nav_menus', array( &$this, '_update_menus_insertion_points' ), 10, 2 ); // 2.13

		// since 2.2.0
		add_action( 'admin_bar_init', array( &$this, 'admin_bar_init' ) ); // add button in toolbar

		// 2.8.0 dashboard language - inspired from Takayuki Miyoshi works
		add_filter( 'locale', array( &$this, 'admin_side_locale' ) );

		add_action( 'admin_init', array( &$this, 'switch_user_locale' ) );
		add_action( 'personal_options_update', array( &$this, 'update_user_dashboard_lang_option' ) );
		add_action( 'personal_options', array( &$this, 'select_user_dashboard_locale' ) );
		add_action( 'edit_user_profile_update', array( &$this, 'update_user_dashboard_lang_option' ) ); // 2.18

		// plugins list infos
		add_filter( 'plugin_row_meta', array( &$this, 'more_infos_in_plugin_list' ), 10, 2 ); // class WP_Plugins_List_Table
		add_filter( 'plugin_action_links', array( &$this, 'more_plugin_actions' ), 10, 2 ); // class WP_Plugins_List_Table
		add_action( 'after_plugin_row', array( &$this, 'more_plugin_row' ), 10, 3 ); // class WP_Plugins_List_Table

		// Dashboard menu and settings pages
		add_action( 'admin_init', array( &$this, 'admin_redirects' ) ); // 2.20 for welcome screen
		add_action( 'admin_menu', array( &$this, 'admin_welcome' ), 10 ); // 2.20
		add_action( 'admin_menu', array( &$this, 'add_menu_settings_pages' ), 10 );
		add_action( 'admin_menu', array( &$this, 'admin_sub_menus_hide' ), 12 ); //

		add_filter( 'admin_title', array( &$this, 'admin_recover_page_title' ), 10, 2 ); // 2.11.3

		add_action( 'admin_print_styles-settings_page_language_page', array( &$this, 'print_styles_options_language_page' ), 20 );
		add_action( 'admin_print_styles-settings_page_language_front_set', array( &$this, 'print_styles_options_language_tabs' ), 20 );
		add_action( 'admin_print_styles-settings_page_language_expert', array( &$this, 'print_styles_options_language_tabs' ), 40 );
		add_action( 'admin_print_styles-settings_page_author_rules', array( &$this, 'print_styles_options_language_tabs' ), 20 );
		add_action( 'admin_print_styles-settings_page_language_files', array( &$this, 'print_styles_options_language_tabs' ), 20 );
		add_action( 'admin_print_styles-settings_page_language_support', array( &$this, 'print_styles_options_language_support' ), 20 );

		// Navigation menu builder
		add_action( 'admin_init', array( &$this, 'add_language_nav_menu_meta_boxes' ) );
		add_action( 'admin_init', array( &$this, 'add_sub_select_page_nav_menu_meta_boxes' ) );
		add_action( 'admin_init', array( &$this, 'add_sub_select_nav_menu_meta_boxes' ) );
		add_action( 'admin_head-nav-menus.php', array( &$this, 'add_help_text' ) );

		// Edit Post Page
		add_action( 'admin_init', array( &$this, 'admin_init' ) ); // styles registering
		add_action( 'post_submitbox_start', array( &$this, 'post_submit_permalink_option' ) ); // 2.15 - option to use title as permalink

		// Propagation && Authoring settings
		add_action( 'admin_init', array( &$this, 'set_author_rules_register_setting' ) );
		add_action( 'admin_init', array( &$this, 'set_propagation_actions' ) );

		// frontend new settings
		add_action( 'admin_init', array( &$this, 'set_frontend_settings_fields' ) );

		add_action( 'admin_menu', array( &$this, 'add_custom_box_in_post_edit' ) );

		add_action( 'admin_print_scripts-post.php', array( &$this, 'find_post_script' ) ); // 2.2.2
		add_action( 'admin_print_scripts-post-new.php', array( &$this, 'find_post_script' ) );

		add_action( 'wp_ajax_find_post_types', array( &$this, 'wp_ajax_find_post_types' ) ); // 2.9.10
		add_action( 'wp_ajax_display_gp_locale', array( &$this, 'wp_ajax_display_gp_locale' ) ); // 2.22.6
		add_action( 'admin_print_scripts-settings_page_language_page', array( &$this, 'display_gp_locale' ) );

		add_action( 'admin_print_styles-post.php', array( &$this, 'print_styles_cpt_edit' ) );
		add_action( 'admin_print_styles-post-new.php', array( &$this, 'print_styles_cpt_edit' ) );

		//add_filter( 'is_protected_meta', array(&$this,'hide_lang_post_meta'), 10, 3 ); // 2.5
		//add_filter( 'post_meta_key_subselect', array(&$this,'hide_lang_post_meta_popup'), 10, 2); // 2.5

		/* actions for edit post page */
		add_action( 'save_post', array( &$this, 'xili_language_add' ), 10, 2 );
		add_action( 'save_post', array( &$this, 'fixes_post_slug' ), 11, 2 ); // 2.5

		// Edit Attachment Media.
		add_filter( 'attachment_fields_to_edit', array( &$this, 'add_language_attachment_fields' ), 10, 2 ); // 2.6.3
		add_filter( 'attachment_fields_to_save', array( &$this, 'set_attachment_fields_to_save' ), 10, 2 ); // 2.6.3
		add_action( 'delete_attachment', array( &$this, 'if_cloned_attachment' ) ); // 2.6.3
		add_filter( 'wp_delete_file', array( &$this, 'if_file_cloned_attachment' ) ); // 2.6.3
		add_action( 'admin_head-post.php', array( &$this, 'add_help_text' ) ); // post.php because attachment don't work 2021-04 !

		// Flag media.
		add_action( 'attachment_submitbox_misc_actions', array( &$this, 'attachment_submitbox_flag_metadata' ) ); // 2.15
		add_filter( 'display_media_states', array( &$this, 'add_display_media_states' ) );
		add_action( 'edit_attachment', array( &$this, 'update_attachment_context' ) ); // 2.15
		add_action( 'add_attachment', array( &$this, 'xili_reset_transient_get_flag_series' ) );// 2.16.4
		add_action( 'edit_attachment', array( &$this, 'xili_reset_transient_get_flag_series' ) );
		add_action( 'delete_attachment', array( &$this, 'xili_reset_transient_get_flag_series' ) ); // near get_flag_series
		add_action( 'admin_menu', array( &$this, 'add_custom_box_in_media_edit' ) ); // 2.15 custom meta box in single media edit

		add_action( 'admin_menu', array( $this, 'flag_options_theme_menu' ) ); // 2.15
		add_action( 'admin_init', array( &$this, 'set_flag_register_setting' ) );

		// posts edit table
		add_filter( 'manage_edit-post_columns', array( &$this, 'xili_manage_column_name' ) ); // 2.9.10 - post quick edit single row
		add_filter( 'manage_edit-page_columns', array( &$this, 'xili_manage_column_name' ) ); // 2.22.1
		add_filter( 'manage_post_posts_columns', array( &$this, 'xili_manage_column_name' ) ); // 2.8.1
		add_filter( 'manage_page_posts_columns', array( &$this, 'xili_manage_column_name' ) );
		add_filter( 'manage_media_columns', array( &$this, 'xili_manage_column_name' ) ); // 2.6.3

		$custompoststype = $this->xili_settings['multilingual_custom_post']; // 2.8.1
		if ( array() != $custompoststype ) {
			foreach ( $custompoststype as $key => $customtype ) {
				if ( ( ! class_exists( 'bbPress' ) && 'enable' == $customtype['multilingual'] ) || ( class_exists( 'bbPress' ) && ! in_array( $key, array( bbp_get_forum_post_type(), bbp_get_topic_post_type(), bbp_get_reply_post_type() ) ) && 'enable' == $customtype['multilingual'] ) ) {
					add_filter( 'manage_' . $key . '_posts_columns', array( &$this, 'xili_manage_column_name' ) );
				}
			}
		}

		if ( class_exists( 'bbPress' ) ) {
			add_filter( 'bbp_admin_forums_column_headers', array( &$this, 'xili_manage_column_name' ) );
			add_filter( 'bbp_admin_topics_column_headers', array( &$this, 'xili_manage_column_name' ) );
			add_filter( 'bbp_admin_replies_column_headers', array( &$this, 'xili_manage_column_name' ) ); //2.8.1
		}

		add_action( 'manage_posts_custom_column', array( &$this, 'xili_manage_column' ), 10, 2 );
		add_action( 'manage_pages_custom_column', array( &$this, 'xili_manage_column' ), 10, 2 );
		add_action( 'manage_media_custom_column', array( &$this, 'xili_manage_column' ), 10, 2 ); // 2.6.3

		add_action( 'admin_print_styles-edit.php', array( &$this, 'print_styles_posts_list' ), 20 );
		add_action( 'admin_print_styles-upload.php', array( &$this, 'print_styles_posts_list' ), 20 );// 2.6.3

		add_filter( 'category_name', array( &$this, 'translated_taxonomy_name' ), 10, 3 ); // 2.13.3

		// quick edit languages in list - 1.8.9
		add_action( 'quick_edit_custom_box', array( &$this, 'languages_custom_box' ), 10, 2 );
		add_action( 'admin_head-edit.php', array( &$this, 'quick_edit_add_script' ) );
		add_action( 'bulk_edit_custom_box', array( &$this, 'hidden_languages_custom_box' ), 10, 2 ); // 1.8.9.3
		add_action( 'wp_ajax_save_bulk_edit', array( &$this, 'save_bulk_edit_language' ) ); // 2.9.10
		add_action( 'wp_ajax_get_menu_infos', array( &$this, 'ajax_get_menu_infos' ) ); // 2.9.10

		// sub-select in admin/edit.php 1.8.9
		add_action( 'restrict_manage_posts', array( &$this, 'restrict_manage_languages_posts' ) );

		/* categories edit-tags table */
		add_filter( 'manage_edit-category_columns', array( &$this, 'xili_manage_tax_column_name' ) );
		add_filter( 'manage_category_custom_column', array( &$this, 'xili_manage_tax_column' ), 10, 3 ); // 2.6
		add_filter( 'category_row_actions', array( &$this, 'xili_manage_tax_action' ), 10, 2 ); // 2.6

		add_action( 'admin_print_styles-edit-tags.php', array( &$this, 'print_styles_posts_list' ), 20 );
		add_action( 'category_edit_form_fields', array( &$this, 'show_translation_msgstr' ), 10, 2 );

		add_action( 'category_add_form', array( &$this, 'update_xd_msgid_list' ) ); //do_action($taxonomy . '_add_form', $taxonomy);

		/* actions for edit link page */
		add_action( 'admin_menu', array( &$this, 'add_custom_box_in_link' ) );

		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_menu_script' ) );

		add_filter( 'manage_link-manager_columns', array( &$this, 'xili_manage_link_column_name' ) ); // 1.8.5
		add_action( 'manage_link_custom_column', array( &$this, 'manage_link_lang_column' ), 10, 2 );
		add_action( 'admin_print_styles-link.php', array( &$this, 'print_styles_link_edit' ), 20 );

		// set or update term for this link taxonomy
		add_action( 'edit_link', array( &$this, 'edit_link_set_lang' ) );
		add_action( 'add_link', array( &$this, 'edit_link_set_lang' ) );

		// default screen options - nav menus

		add_action( 'added_user_meta', array( &$this, 'default_nav_menus_screen_options' ), 10, 4 );

		// new visibility for all widgets - 2.20.3
		if ( ! empty( $this->xili_settings['widget_visibility'] ) ) {
			add_filter( 'widget_update_callback', array( &$this, 'widget_update_callback' ), 10, 4 );
			add_action( 'in_widget_form', array( &$this, 'widget_visibility_admin' ), 10, 3 );
		}

		// infos in xml export
		add_action( 'export_filters', 'message_export_limited' ); // 2.12.1
		//display contextual help

		//add_action( 'contextual_help', array( &$this, 'add_help_text' ), 10, 3 ); /* 1.7.0 */

		xili_xl_error_log( '# ADMIN ' . __LINE__ . ' ************* only_construct = ' . __CLASS__ );
	}

	/**
	 * Add filters according multiple_lang options
	 *
	 * @since 2.22.4
	 *
	 */
	public function init_filters() {

		if ( $this->multiple_lang ) {

			add_filter( 'bulk_actions-edit-post', array( &$this, 'register_my_bulk_post_actions' ) );
			add_filter( 'bulk_actions-edit-page', array( &$this, 'register_my_bulk_post_actions' ) );
			add_filter( 'handle_bulk_actions-edit-post', array( &$this, 'bulk_action_handlers' ), 10, 3 );
			add_filter( 'handle_bulk_actions-edit-page', array( &$this, 'bulk_action_handlers' ), 10, 3 );

			$custompoststype = $this->xili_settings['multilingual_custom_post']; // 2.8.1
			if ( array() != $custompoststype ) {
				foreach ( $custompoststype as $key => $customtype ) {
					if ( ( ! class_exists( 'bbPress' ) && 'enable' == $customtype['multilingual'] ) ||
							( class_exists( 'bbPress' ) && ! in_array( $key, array( bbp_get_forum_post_type(), bbp_get_topic_post_type(), bbp_get_reply_post_type() ) ) && 'enable' == $customtype['multilingual'] ) ) {
						add_filter( 'bulk_actions-edit-' . $key, array( &$this, 'register_my_bulk_post_actions' ) );
						add_filter( 'handle_bulk_actions-edit-' . $key, array( &$this, 'bulk_action_handlers' ), 10, 3 );
					}
				}
			}
			add_action( 'admin_notices', array( &$this, 'my_bulk_action_admin_notice' ) );
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
	public function set_propagation_actions() {

		$checked_options = $this->get_theme_author_rules_options();  // checked 2.18

		$this->propagate_options_labels = array(
			'post_format' => array(
				'name' => __( 'Post Format', 'xili-language' ),
				'description' => __( 'Copy Post Format.', 'xili-language' ),
			),
			'page_template' => array(
				'name' => __( 'Page template', 'xili-language' ),
				'description' => __( 'Copy Page template.', 'xili-language' ),
			),
			'comment_status' => array(
				'name' => __( 'Comment Status', 'xili-language' ),
				'description' => __( 'Copy Comment Status.', 'xili-language' ),
			),
			'ping_status' => array(
				'name' => __( 'Ping Status', 'xili-language' ),
				'description' => __( 'Copy Ping Status.', 'xili-language' ),
			),
			'post_parent' => array(
				'name' => __( 'Post Parent', 'xili-language' ),
				'description' => __( 'Copy Post Parent if translated (try to find the parent of the translated post).', 'xili-language' ),
				'data' => 'post',
			),
			'menu_order' => array(
				'name' => __( 'Order', 'xili-language' ),
				'description' => __( 'Copy Page Order', 'xili-language' ),
			),
			'thumbnail_id' => array(
				'name' => __( 'Featured image', 'xili-language' ),
				'description' => __( 'Linked translated post will have the same featured image, (try to find the translated media). ', 'xili-language' ),
			),
		);

		if ( current_theme_supports( 'xiliml-authoring-rules' ) ) {
			$options = array();
			$labels = array();
			$support = get_theme_support( 'xiliml-authoring-rules' ); // values defined by theme's author

			if ( isset( $support[0] ) && array() != $support[0] ) {
				foreach ( $support[0] as $key => $params ) {
					if ( array() == array_diff( array_keys( $params ), array( 'data', 'default', 'name', 'description', 'hidden' ) ) ) { // four parameters mandatory
						$options[ $key ] = array(
							'default' => $params['default'],
							'data' => $params['data'],
							'hidden' => $params['hidden'],
						);
						$labels[ $key ] = array(
							'name' => $params['name'],
							'description' => $params['description'],
						);
					}
				}
				$this->propagate_options_default = array_merge( $this->propagate_options_default_ref, $options );
				$this->propagate_options_labels = array_merge( $this->propagate_options_labels, $labels ); // default texts can be modified
			}
		} else {
			$this->propagate_options_default = $this->propagate_options_default_ref;
		}

		if ( array() != $this->propagate_options_default ) {
			foreach ( $this->propagate_options_default as $key => $one_propagate ) {
				if ( ( 'post' != $one_propagate['data'] && isset( $checked_options[ $key ] ) ) && '1' == $checked_options[ $key ] ) {
					// 2.18
					add_action( 'xl_propagate_post_attributes', array( &$this, 'propagate_' . $key ), 10, 2 );
				}
			}
			add_action( 'xl_propagate_post_attributes', array( &$this, 'propagate_post_columns' ), 10, 2 );
		}
	}

	// called by filter admin_head
	public function xd_flush_permalinks() {
		remove_submenu_page( 'index.php', 'xl-about' ); // 2.20 - here to avoid remove page title

		$screen = get_current_screen();
		if ( 'settings_page_language_page' == $screen->base ) {
			flush_rewrite_rules();
		}
	}

	/**
	 * Checks if we should add links to the admin bar.
	 *
	 * @since 2.2.0
	 */
	public function admin_bar_init() {
		// Is the user sufficiently leveled, or has the bar been disabled? !is_super_admin() ||
		if ( ! is_admin_bar_showing() ) {
			return;
		}
		// editor rights
		if ( current_user_can( 'xili_language_menu' ) ) {
			add_action( 'admin_bar_menu', array( &$this, 'xili_tool_bar_links' ), 500 );
		}
		add_action( 'admin_bar_menu', array( &$this, 'lang_admin_bar_menu' ), 500 );
	}

	/**
	 * Checks if we should add links to the bar.
	 *
	 * @since 2.2
	 * updated and renamed 2.4.2 (node)
	 */
	public function xili_tool_bar_links() {

		$link = plugins_url( 'images/xililang-logo-24.png', XILILANGUAGE_PLUGIN_FILE );
		$alt = esc_attr__( 'Languages by ©xiligroup', 'xili-language' );
		$title = esc_attr__( 'Languages menu by ©xiligroup', 'xili-language' );
		// Add the Parent link.
		$this->add_node_if_version(
			array(
				'title' => sprintf( '<img src="%s" alt="%s" title="%s" />', $link, $alt, $title ),
				'href' => false,
				'id' => 'xili_links',
			)
		);
		if ( current_user_can( 'xili_language_set' ) ) {
			$this->add_node_if_version(
				array(
					'title' => __( 'Languages settings', 'xili-language' ),
					'href' => admin_url( 'options-general.php?page=language_page' ),
					'id' => 'xl-set',
					'parent' => 'xili_links',
					'meta' => array(
						'title' => __( 'Languages settings', 'xili-language' ),
					),
				)
			);
		}

		if ( class_exists( 'xili_tidy_tags' ) && current_user_can( 'xili_tidy_editor_set' ) ) {
			$this->add_node_if_version(
				array(
					/* translators: */
					'title' => sprintf( __( 'Tidy %s settings', 'xili_tidy_tags' ), __( 'Tags' ) ),
					'href' => admin_url( 'admin.php?page=xili_tidy_tags_settings' ),
					'id' => 'xtt-set',
					'parent' => 'xili_links',
					'meta' => array(
						/* translators: */
						'title' => sprintf( __( 'Tidy %s settings', 'xili_tidy_tags' ), __( 'Tags' ) ),
					),
				)
			);
		}

		if ( class_exists( 'xili_tidy_tags' ) && current_user_can( 'xili_tidy_editor_group' ) ) {
			$this->add_node_if_version(
				array(
					/* translators: */
					'title' => sprintf( __( '%s groups', 'xili_tidy_tags' ), __( 'Tags' ) ),
					'href' => admin_url( 'admin.php?page=xili_tidy_tags_assign' ),
					'id' => 'xtt-group',
					'parent' => 'xili_links',
					'meta' => array(
						/* translators: */
						'title' => sprintf( __( '%s groups', 'xili_tidy_tags' ), __( 'Tags' ) ),
					),
				)
			);
		}
		if ( class_exists( 'xili_dictionary' ) && current_user_can( 'xili_dictionary_edit' ) ) {
			// fixed XD 2.7
			global $xili_dictionary;
			$link = $xili_dictionary->xd_settings_page;

			$this->add_node_if_version(
				array(
					'title' => 'xili-dictionary',
					'href' => admin_url( $link ),
					'id' => 'xd-set',
					'parent' => 'xili_links',
					'meta' => array(
						/* translators: */
						'title' => sprintf( __( 'Translation with %s tools', 'xili-language' ), 'xili-dictionary' ),
					),
				)
			);
		}
		$this->add_node_if_version(
			array(
				'title' => __( 'xili-language : how to', 'xili-language' ),
				'href' => $this->fourteenlink,
				'id' => 'xilione-multi',
				'parent' => 'xili_links',
				'meta' => array(
					'target' => '_blank',
				),
			)
		);
		$this->add_node_if_version(
			array(
				'title' => __( 'About ©xiligroup plugins', 'xili-language' ),
				'href' => $this->devxililink,
				'id' => 'xili-about',
				'parent' => 'xili_links',
				'meta' => array(
					'target' => '_blank',
				),
			)
		);

	}

	public function add_node_if_version( $args ) {
		global $wp_admin_bar;
		$wp_admin_bar->add_node( $args );
	}

	/**
	 * from after_setup_theme action
	 *
	 * @since 2.8.8
	 *
	 */
	public function admin_user_id_locale() {
		$this->user_locale = get_user_option( 'user_locale' );
	}

	/**
	 * Admin side localization - user's dashboard - called by filter locale (get_locale())
	 *
	 * @since 2.8.0
	 *
	 */
	public function admin_side_locale( $locale = 'en_US' ) {

		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ( 'options-general' == $screen && $screen->id ) {
				return $this->get_default_locale(); // 2.18 for selected of popup - show get_option WPLANG value
			}
		}
		// to avoid notice with bbPress 2.3 - brutal approach
		if ( class_exists( 'bbPress' ) ) {
			remove_action( 'set_current_user', 'bbp_setup_current_user' );
		}
		$locale = get_user_option( 'user_locale' );
		if ( class_exists( 'bbPress' ) ) {
			add_action( 'set_current_user', 'bbp_setup_current_user', 10 );
		}

		if ( empty( $locale ) ) {
			$locale = $this->get_default_locale();
		}

		return $locale;
	}


	/**
	 * Admin side localization - available languages inside WP core installation
	 *
	 * @since 2.8.0
	 *
	 */
	public function get_default_locale() {

		$wplang = $this->get_wplang();
		$locale = ( '' != $wplang ) ? $wplang : 'en_US';

		if ( is_multisite() ) {
			if ( defined( 'WP_INSTALLING' ) || ( false === $ms_locale = get_option( 'WPLANG' ) ) ) {
				$ms_locale = get_site_option( 'WPLANG' );
			}

			if ( false !== $ms_locale ) {
				$locale = $ms_locale;
			}
		}

		return $locale;

	}

	/**
	 * add theme local-xx_YY.mo file to translate
	 *
	 *
	 */
	public function add_local_text_domain_file( $locale ) {
		$theme_textdomain = the_theme_domain();
		$langfolder = $this->xili_settings['langs_folder'];
		$langfolder = ( '/' == $langfolder ) ? '' : $langfolder;
		$theme_dir = get_stylesheet_directory();
		$file = "{$theme_dir}{$langfolder}/local-{$locale}.mo";
		if ( in_array( $file, $this->local_textdomain_loaded ) ) {
			return; // thanks to 3pepe3
		}
		$this->local_textdomain_loaded[] = $file;
		if ( ! ( load_textdomain( $theme_textdomain, $file ) ) ) {
			load_textdomain( $theme_textdomain, WP_LANG_DIR . "/themes/local-{$theme_textdomain}-{$locale}.mo" );
		}
	}

	// Admin Bar at top right

	public function lang_admin_bar_menu() {

		$screen = get_current_screen(); // to limit unwanted side effects (form)
		if ( in_array(
			$screen->id,
			array(
				'dashboard',
				'users',
				'profile',
				'edit-post',
				'edit-page',
				'link-manager',
				'upload',
				'settings_page_language_page',
				'settings_page_language_front_set',
				'settings_page_language_expert',
				'settings_page_language_expert',
				'settings_page_language_support',
				'settings_page_language_files',
				'settings_page_author_rules',
				'xdmsg',
				'edit-xdmsg',
				'xdmsg_page_dictionary_page',
			)
		) || ( false !== strpos( $screen->id, '_page_xili_tidy_tags_assign' ) )
		) {

			$current_locale = $this->admin_side_locale();

			$cur_locale = GP_Locales::by_field( 'wp_locale', $current_locale );
			if ( $cur_locale ) {
				$current_language = $cur_locale->native_name;
			} else {
				$cur_locale = GP_Locales::by_slug( $current_locale );
				$current_language = ( $cur_locale ) ? $cur_locale->native_name : '';
			}

			if ( ! $current_language ) {
				$current_language = $current_locale;
			}

			$this->add_node_if_version(
				array(
					'parent' => 'top-secondary',
					'id' => 'xili-user-locale',
					'title' => __( 'Language', 'xili-language' ) . ': ' . $this->lang_to_show( $current_language ),
				)
			); // '&#10004; '

			$available_languages = $this->available_languages(
				array(
					'exclude' => array( $current_locale ),
				)
			);

			foreach ( $available_languages as $locale => $lang ) {
				$url = admin_url( 'profile.php?action=lang-switch-locale&locale=' . $locale );

				$url = esc_html(
					add_query_arg(
						array(
							'redirect_to' => urlencode( $_SERVER['REQUEST_URI'] ),
						),
						$url
					)
				); //2.17.1

				$url = wp_nonce_url( $url, 'lang-switch-locale' );

				$this->add_node_if_version(
					array(
						'parent' => 'xili-user-locale',
						'id' => 'xili-user-locale-' . $locale,
						'title' => $this->lang_to_show( $lang ),
						'href' => $url,
					)
				);
			}
		}
	}

	public function switch_user_locale() {

		if ( empty( $_REQUEST['action'] ) || 'lang-switch-locale' != $_REQUEST['action'] ) {
			return;
		}

		check_admin_referer( 'lang-switch-locale' );

		$locale = isset( $_REQUEST['locale'] ) ? $_REQUEST['locale'] : '';

		if ( ! $this->is_available_locale( $locale ) || $locale == $this->admin_side_locale() ) {
			return;
		}

		update_user_option( get_current_user_id(), 'user_locale', $locale, true );

		if ( ! empty( $_REQUEST['redirect_to'] ) ) {
			wp_safe_redirect( $_REQUEST['redirect_to'] );
			exit();
		}
	}

	public function is_available_locale( $locale ) {
		return ! empty( $locale ) && array_key_exists( $locale, (array) $this->available_languages() );
	}

	/**
	 * return list of ISO and combined name of available languages
	 */

	public function available_languages( $args = '' ) {
		$defaults = array(
			'exclude' => array(),
			'orderby' => 'key',
			'order' => 'ASC',
		);

		$args = wp_parse_args( $args, $defaults );

		$langs = array();

		$installed_locales = get_available_languages(); // Get all available languages based on the presence of *.mo files in a given directory.
		$installed_locales[] = $this->get_default_locale();
		$installed_locales[] = 'en_US';
		$installed_locales = array_unique( $installed_locales );
		$installed_locales = array_filter( $installed_locales );

		foreach ( $installed_locales as $locale ) {
			if ( in_array( $locale, (array) $args['exclude'] ) ) {
				continue;
			}

			$cur_locale = GP_Locales::by_field( 'wp_locale', $locale );
			if ( $cur_locale ) {
				/* translators: */
				$lang = sprintf( _x( '%1$s/%2$s', 'locales', 'xili-language' ), $cur_locale->english_name, $cur_locale->native_name );
			} else {
				$cur_locale = GP_Locales::by_slug( $locale );
				/* translators: */
				$lang = ( $cur_locale ) ? sprintf( _x( '%1$s/%2$s', 'locales', 'xili-language' ), $cur_locale->english_name, $cur_locale->native_name ) : '';
			}

			if ( empty( $lang ) ) {
				$lang = "[$locale]";
			}

			$langs[ $locale ] = $lang;
		}

		if ( 'value' == $args['orderby'] ) {
			natcasesort( $langs );

			if ( 'DESC' == $args['order'] ) {
				$langs = array_reverse( $langs );
			}
		} else {
			if ( 'DESC' == $args['order'] ) {
				krsort( $langs );
			} else {
				ksort( $langs );
			}
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
	public function update_user_dashboard_lang_option( $user_id ) {
		// 2.18
		if ( ! isset( $_POST['user_locale'] ) || empty( $_POST['user_locale'] ) ) {
			$locale = null;
		} else {
			$locale = $_POST['user_locale'];
		}

		update_user_option( $user_id, 'user_locale', $locale, true );
	}

	public function select_user_dashboard_locale( $wp_user ) {
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
				<p><em>
				<?php
				esc_html_e( 'System’s default language is', 'xili-language' );
				echo ': ' . $this->get_default_locale();
				?>
				</em></p>
			</td>
		</tr>
		<?php
	}

	public function lang_to_show( $lang = 'english' ) {
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
	public function more_infos_in_plugin_list( $links, $file ) {
		$base = $this->plugin_basename;
		if ( $file == $base ) {
			$links[] = '<a href="options-general.php?page=language_page">' . __( 'Settings' ) . '</a>';
			$links[] = __( 'Informations and Getting started:', 'xili-language' ) . ' <a href="' . $this->wikilink . '">' . __( 'Xili Wiki', 'xili-language' ) . '</a>';
			$links[] = '<a href="' . $this->forumxililink . '">' . __( 'Forum and Support', 'xili-language' ) . '</a>';
			$links[] = '<a href="' . $this->devxililink . '/donate/">' . __( 'Donate', 'xili-language' ) . '</a>';
		}
		return $links;
	}

	/**
	 * Adds a row to comment situation for multilingual context !
	 *
	 */
	public function more_plugin_row( $plugin_file, $plugin_data, $status ) {
		$base = $this->plugin_basename;
		if ( $plugin_file == $base ) {
			$statusxili = array();

			$statusxili[] = __( 'Congratulations for choosing xili-language to built a multilingual website. To work optimally, 2 other plugins are recommended', 'xili-language' );

			$statusxili[] = $this->plugin_status( 'xili-dictionary', 'xili-dictionary/xili-dictionary.php', $status );

			$statusxili[] = $this->plugin_status( 'xili-tidy-tags', 'xili-tidy-tags/xili-tidy-tags.php', $status );

			if ( is_child_theme() ) {
				$theme_name = get_option( 'stylesheet' ) . ' ' . __( 'child of', 'xili-language' ) . ' ' . get_option( 'template' );
			} else {
				$theme_name = get_option( 'template' ); // same as stylesheet
			}
			/* translators: */
			$statusxili[] = sprintf( __( 'For Appearance the current active theme is <em>%s</em>', 'xili-language' ), $theme_name );

			if ( '' == $this->parent->xili_settings['theme_domain'] ) {
				/* translators: */
				$statusxili[] = sprintf( __( 'This theme <em>%s</em> seems to not contain localization function (load_theme_textdomain) to be used for a multilingual website', 'xili-language' ), $theme_name );
			} else {
				/* translators: */
				$statusxili[] = sprintf( __( 'This theme <em>%s</em> seems to contain localization function to be used for a multilingual website', 'xili-language' ), $theme_name );
			}

			$cb_col = '<img src="' . plugins_url( 'images/xililang-logo-24.png', XILILANGUAGE_PLUGIN_FILE ) . '" alt="xili-language trilogy"/>';
			$action_col = __( 'More infos about', 'xili-language' ) . '<br />&nbsp;&nbsp;' . $plugin_data['Name'];
			$description_col = implode( '. ', $statusxili ) . '.';
			echo "<tr><th>$cb_col</th><td>$action_col</td><td>$description_col</td></tr>";
		}
	}

	public function plugin_status( $plugin_name, $plugin_file, $status ) {

		if ( is_plugin_active( $plugin_file ) ) {
			$plug_status = __( 'active', 'xili-language' );
		} else {
			$plugins = get_plugins();
			if ( isset( $plugins[ $plugin_file ] ) ) {
				$plug_status = __( 'inactive', 'xili-language' );
			} else {
				$plug_status = __( 'not installed', 'xili-language' );
			}
		}
		/* translators: */
		return sprintf( __( 'Plugin %1$s is %2$s', 'xili-language' ), $plugin_name, $plug_status );
	}

	/**
	 * Add action link(s) to plugins page
	 *
	 * @since 0.9.3
	 * @author MS
	 * @copyright Dion Hulse, http://dd32.id.au/wordpress-plugins/?configure-link and scripts@schloebe.de
	 */
	public function more_plugin_actions( $links, $file ) {
		$this_plugin = $this->plugin_basename;
		if ( $file == $this_plugin ) {
			$settings_link = '<a href="options-general.php?page=language_page">' . __( 'Settings' ) . '</a>';
			$links = array_merge( array( $settings_link ), $links ); // before other links
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

		$this->changelog = __( 'Changelog tab of xili-language', 'xili-language' );

		wp_register_style( 'xl_welcome_stylesheet', $this->plugin_url . '/xili-css/xl-welcome-style.css', XILILANGUAGE_VER );
		$transient = get_transient( '_xl_activation_redirect' );
		if ( ! $transient || is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		delete_transient( '_xl_activation_redirect' );

		if ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], array( 'xl-about' ) ) ) {
			return;
		}
		$type = ( 2 == $transient ) ? '&xl-updated=1' : '';
		// Here, the welcome page
		wp_safe_redirect( admin_url( 'index.php?page=xl-about' . $type ) );
		exit;
	}

	/**
	 * Add admin menus/screens.
	 * * @since 2.20
	 */
	public function admin_welcome() {
		$welcome_page_name  = __( 'About xili-language', 'xili-language' );
		$welcome_page_title = __( 'Welcome to xili-language', 'xili-language' );

		$page = add_dashboard_page( $welcome_page_title, $welcome_page_name, 'manage_options', 'xl-about', array( &$this, 'about_screen' ) );
		add_action( 'admin_print_styles-' . $page, array( &$this, 'admin_welcome_css' ) );
	}

	/**
	 * admin_css function.
	 */
	public function admin_welcome_css() {
		wp_enqueue_style( 'xl_welcome_stylesheet' );
	}

	/**
	 * welcome about screen
	 * * @since 2.20
	 */
	public function about_screen() {
		?>

		<div class="wrap about-wrap">

			<h1>
				<?php
				/* translators: */
				printf( esc_html__( 'Welcome to xili-language %s', 'xili-language' ), XILILANGUAGE_VER );
				?>
			</h1>

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
				/* translators: */
				printf( esc_html__( '%1$s xili-language %2$s is more powerful, stable and secure than ever before. We hope you enjoy using it.', 'xili-language' ), $message, XILILANGUAGE_VER );
				?>
			</div>

			<div class="infolog">
				<div class="changelog">
					<div class="feature-section">
						<?php if ( empty( $_GET['xl-updated'] ) ) { ?>

						<div>
							<h4><?php esc_html_e( 'What is a multilingual website with xili-language?', 'xili-language' ); ?></h4>
							<p><?php esc_html_e( 'With this plugin, your localized website will become a (bi)multilingual website. xili-language trilogy also contains xili-dictionary and xili-tidy-tags plugins.', 'xili-language' ); ?></p>
						</div>

						<?php } ?>

						<div>
							<h4><?php esc_html_e( 'Improved Permalinks management', 'xili-language' ); ?></h4>
							<p>
							<?php
							echo esc_html__( 'Now xili-language includes special optional functions provided in example themes (201x-xili child series) to insert language at beginning for the permalink. ', 'xili-language' )
							. '<br />' . esc_html__( 'Options are now in expert tab. (These functions were reserved formerly for donators and contributors).', 'xili-language' )
							. '<br />' . esc_html__( 'If using or customizing 201x-xili child-theme series: it is fully recommanded to (re)visit and verify languages list and permalink settings page (flush fired).', 'xili-language' );
							?>
							</p>
						</div>
						<div>
							<h4><?php esc_html_e( 'Fully customizable by webmasters', 'xili-language' ); ?></h4>
							<p><?php esc_html_e( 'According your content “multilingual” strategy, xili-language offers six pages in settings to adapt lot of features. (with online help)', 'xili-language' ); ?></p>
						</div>
						<div class="last-feature">
							<h4><?php esc_html_e( 'Entirely designed for developers', 'xili-language' ); ?></h4>
							<p><?php esc_html_e( 'Following the WordPress Core rules, including specific elements (tags, shortcode, functions, filters,...) xili-language is a CMS plateform add-on able to work with custom post types without adding tables in db or cookies or redirecting.', 'xili-language' ); ?></p>
							<p>
								<?php
								printf(
									/* translators: */
									esc_html__( 'Development until this version (%1$s) are documented here %2$s and inside sources.', 'xili-language' ),
									XILILANGUAGE_VER,
									'<a href="' . $this->repositorylink . 'changelog/" title="' . $this->changelog . '" >' . $this->changelog . '</a>'
								);
								?>
								</p>
						</div>
					</div>
				</div>
			</div>
			<div class="return-to-setting">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'language_page' ), 'options-general.php' ) ) ); ?>"><?php esc_html_e( 'Go to xili-language Settings', 'xili-language' ); ?></a>
			</div>
			<div class="about-footer"><a href="<?php echo $this->repositorylink; ?>" title="xili-language page and docs" target="_blank" style="text-decoration:none" >
				<img class="about-icon" src="<?php echo plugins_url( 'images/xililang-logo-32.png', XILILANGUAGE_PLUGIN_FILE ); ?>" alt="xili-language logo"/>
				</a>&nbsp;&nbsp;&nbsp;©&nbsp;
				<a href="<?php echo $this->devxililink; ?>" target="_blank" title="<?php esc_attr_e( 'Author' ); ?>" >xiligroup.com</a>™ - msc 2007-2017
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
	public function add_menu_settings_pages() {
		/* browser title and menu title - if empty no menu */
		$this->thehook = add_options_page( __( 'xili-language plugin', 'xili-language' ) . ' - 1', __( 'Languages ©xili', 'xili-language' ), 'manage_options', 'language_page', array( &$this, 'languages_settings' ) );

		add_action( 'load-' . $this->thehook, array( &$this, 'on_load_page' ) );


		$this->xl_tabs = array(
			'language_page' => array(
				'label' => __( 'Languages list', 'xili-language' ),
				'url' => 'options-general.php?page=language_page',
			),
			'language_front_set' => array(
				'label' => __( 'Languages front-end settings', 'xili-language' ),
				'url' => 'options-general.php?page=language_front_set',
			),
			'language_expert' => array(
				'label' => __( 'Settings for experts', 'xili-language' ),
				'url' => 'options-general.php?page=language_expert',
			),
			'language_files' => array(
				'label' => __( 'Managing language files', 'xili-language' ),
				'url' => 'options-general.php?page=language_files',
			),
			'author_rules' => array(
				'label' => __( 'Managing Authoring rules', 'xili-language' ),
				'url' => 'options-general.php?page=author_rules',
			),
			'language_support' => array(
				'label' => __( 'xili-language support', 'xili-language' ),
				'url' => 'options-general.php?page=language_support',
			),
		);

		$this->subpage_titles = array(
			'language_front_set' => __( 'xili-language plugin', 'xili-language' ) . ', 2: ' . $this->xl_tabs['language_front_set']['label'],
			'language_expert' => __( 'xili-language plugin', 'xili-language' ) . ', 3: ' . $this->xl_tabs['language_expert']['label'],
			'language_files' => __( 'xili-language plugin', 'xili-language' ) . ', 4: ' . $this->xl_tabs['language_files']['label'],
			'author_rules' => __( 'xili-language plugin', 'xili-language' ) . ' - ' . $this->xl_tabs['author_rules']['label'],
			'language_support' => __( 'xili-language plugin', 'xili-language' ) . ', 5: ' . $this->xl_tabs['language_support']['label'],
		);

		$hooks = array(); // to prepare highlight those in tabs
		$this->thehook2 = add_options_page( $this->subpage_titles['language_front_set'], 'xl-front-end', 'manage_options', 'language_front_set', array( &$this, 'frontend_settings' ) );
		add_action( 'load-' . $this->thehook2, array( &$this, 'on_load_page_set' ) );
		$hooks[] = $this->thehook2;

		$this->thehook4 = add_options_page( $this->subpage_titles['language_expert'], 'xl-expert', 'manage_options', 'language_expert', array( &$this, 'languages_expert' ) );
		add_action( 'load-' . $this->thehook4, array( &$this, 'on_load_page_expert' ) );
		$hooks[] = $this->thehook4;
		// since 2.8.8
		$this->thehook5 = add_options_page( $this->subpage_titles['language_files'], __( 'Languages Files', 'xili-language' ), 'manage_options', 'language_files', array( &$this, 'languages_files' ) );
		add_action( 'load-' . $this->thehook5, array( &$this, 'on_load_page_files' ) );
		$hooks[] = $this->thehook5;

		// since 2.8.8
		$this->thehook6 = add_options_page( $this->subpage_titles['author_rules'], __( 'Authors rules', 'xili-language' ), 'manage_options', 'author_rules', array( &$this, 'author_rules' ) );
		add_action( 'load-' . $this->thehook6, array( &$this, 'on_load_page_author_rules' ) );
		$hooks[] = $this->thehook5;

		$this->thehook3 = add_options_page( $this->subpage_titles['language_support'], 'xl-support', 'manage_options', 'language_support', array( &$this, 'languages_support' ) );
		add_action( 'load-' . $this->thehook3, array( &$this, 'on_load_page_support' ) );
		$hooks[] = $this->thehook3;

		// Fudge the highlighted subnav item when on a XL admin page - 2.8.2.
		foreach ( $hooks as $hook ) {
			add_action( "admin_head-$hook", array( &$this, 'modify_menu_highlight' ) );
			add_action( 'load-' . $hook, array( &$this, 'add_help_text' ) ); // new since 2021-04.
		}

		$this->insert_news_pointer( 'xl_new_version' ); // pointer in menu for updated version.

		add_action( 'admin_print_footer_scripts', array( &$this, 'print_the_pointers_js' ) );

		// create library of alert messages

		$this->create_library_of_alert_messages();

		// get wp library of translation install
		require_once ABSPATH . 'wp-admin/includes/translation-install.php';
		$this->available_translations = wp_get_available_translations(); // transcient if
	}

	// to remove those visible in tabs - 2.8.2
	public function admin_sub_menus_hide() {
		remove_submenu_page( 'options-general.php', 'language_front_set' );
		remove_submenu_page( 'options-general.php', 'language_expert' );
		remove_submenu_page( 'options-general.php', 'language_files' );
		remove_submenu_page( 'options-general.php', 'author_rules' );
		remove_submenu_page( 'options-general.php', 'language_support' );

		//
	}

	// to recover title saved in $submenu and removed when hidding above ! 2.11.3
	public function admin_recover_page_title( $admin_title, $title ) {
		global $current_screen;
		$keys = array_keys( $this->subpage_titles );
		if ( false !== strpos( $current_screen->base, 'settings_page_' ) ) {
			$indice = str_replace( 'settings_page_', '', $current_screen->base );
			if ( in_array( $indice, $keys ) ) {
				$admin_title = $this->subpage_titles[ $indice ] . ' ' . $admin_title;
			}
		}
		return $admin_title;
	}

	// 2.8.2
	public function modify_menu_highlight() {
		global $plugin_page, $submenu_file;

		// This tweaks the Tools subnav menu to only show one XD menu item
		if ( in_array( $plugin_page, array( 'language_expert', 'language_support', 'language_front_set', 'language_files' ) ) ) {
			$submenu_file = 'language_page';
		}
	}




	/******************************** Main Settings screens *************************/

	/**
	 * for each page : tabs line
	 * @since 2.4.1
	 */
	public function set_tabs_line() {
		global $pagenow;
		$id = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : 'language_page';

		foreach ( $this->xl_tabs as $tab_id => $tab ) {
				$class = ( $tab['url'] == $pagenow . '?page=' . $id ) ? ' nav-tab-active' : '';
				echo '<a href="' . $tab['url'] . '" class="nav-tab' . $class . '">' . esc_html( $tab['label'] ) . '</a>';
		}
	}


	/**
	 * private functions for admin page : the language example list
	 * @since 1.6.0
	 */
	public function example_langs_list( $language_name, $state ) {

		/* reduce list according present languages in today list */
		if ( 'delete' != $state && 'edit' != $state ) {
			$listlanguages = get_terms_of_groups_lite( $this->langs_group_id, TAXOLANGSGROUP, TAXONAME, 'ASC' );
			foreach ( $listlanguages as $language ) {
				if ( array_key_exists( $language->name, $this->examples_list ) ) {
					unset( $this->examples_list[ $language->name ] );
				}
			}
		}
		//
		echo '<option value="">' . __( 'Choose…', 'xili-language' ) . '</option>';
		foreach ( $this->examples_list as $key => $value ) {
			// $selected = (''!=$language_name && $language_name == $key) ? 'selected=selected' : '';
			$selected = selected( ( '' != $language_name && $language_name == $key ), true, false );
			echo '<option value="' . $key . '" ' . $selected . '>' . $value . ' (' . $key . ')</option>';
		}
	}

	/**
	 * private functions for admin page : display full language infos from example_langs_list
	 * @since 2.22.6
	 */
	public function wp_ajax_display_gp_locale() {
		check_ajax_referer( 'display-gp-locale-nonce' );
		$wp_locale_slug = $_POST['wp_locale_slug'];
		$locale_infos = GP_Locales::by_field( 'wp_locale', $wp_locale_slug );

		if ( ! $locale_infos ) {
			// split names
			$locale_names = explode( '/', $_POST['locale_names'] );
			$locale_english_name = $locale_names[0];
			$locale_infos = GP_Locales::by_field( 'english_name', $locale_english_name );
		}

		$html = '<table style="text-align:left;"><thead><th><small>' . __( 'Properties', 'xili-language' ) . '</small></th><tr></tr></thead>';
		$html .= '<tr><th>' . __( 'English Name', 'xili-language' ) . '</th><td>' . $locale_infos->english_name . '</td></tr>';
		$html .= '<tr><th>' . __( 'Native Name', 'xili-language' ) . '</th><td>' . $locale_infos->native_name . '</td></tr>';
		$html .= '<tr><th>' . __( 'WP Locale', 'xili-language' ) . '</th><td>' . $locale_infos->wp_locale . '</td></tr>';
		$html .= '<tr><th>' . __( 'Iso 639-1', 'xili-language' ) . '</th><td><span id="iso_639_1">' . $locale_infos->lang_code_iso_639_1 . '</span></td></tr>';
		$html .= '<tr><th>' . __( 'Iso 639-2', 'xili-language' ) . '</th><td><span id="iso_639_2">' . $locale_infos->lang_code_iso_639_2 . '</span></td></tr>';
		$html .= '<tr><th>' . __( 'Country code', 'xili-language' ) . '</th><td>' . $locale_infos->country_code . '</td></tr>';
		// because ajax need reload - var is not updated
		require_once ABSPATH . 'wp-admin/includes/translation-install.php';
		$available_translations = wp_get_available_translations();

		$wp_locale = $locale_infos->wp_locale;
		$wp_locale_full = '';
		if ( isset( $available_translations[ $wp_locale ] ) ) {
			$wp_locale_full = $wp_locale;
			$wp_glot_formal = '';
		} elseif ( isset( $available_translations[ $wp_locale . '-formal' ] ) ) {
			// WP stores zip with suffix
			$wp_locale_full = $wp_locale . '-formal';
			$wp_glot_formal = 'formal';
		} elseif ( isset( $available_translations[ $wp_locale . '-informal' ] ) ) {
			$wp_locale_full = $wp_locale . '-informal';
			$wp_glot_formal = 'informal';
		}
		if ( $wp_locale_full ) {
			$wp_glot_language = $available_translations[ $wp_locale_full ];
			$html .= '<tr><th><small>' . __( 'From WP translations base', 'xili-language' ) . '</small></th><td></td></tr>';
			$html .= '<tr><th>' . __( 'Version', 'xili-language' ) . '</th><td>' . $wp_glot_language['version'] . '</td></tr>';
			$html .= '<tr><th>' . __( 'Updated', 'xili-language' ) . '</th><td>' . $wp_glot_language['updated'] . '</td></tr>';
		}
		$html .= '</table>';
		wp_send_json_success( $html );
	}

	public function display_gp_locale() {
		$suffix = defined( 'WP_DEBUG' ) && WP_DEBUG ? '.dev' : '.min'; // 2.8.8
		wp_enqueue_script( 'display_gp_locale', plugin_dir_url( XILILANGUAGE_PLUGIN_FILE ) . 'js/xili-display_gp_locale' . $suffix . '.js', '', XILILANGUAGE_VER );
	}


	public function print_styles_options_language_tabs() {
		// the 2 others tabs

		echo "<!---- xl options css 2 to 3 ----->\n";
		echo '<style type="text/css" media="screen">' . "\n";
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

		if ( $this->exists_style_ext && 'on' == $this->xili_settings['external_xl_style'] ) {
			wp_enqueue_style( 'xili_language_stylesheet' );
		}
	}


	/**
	 * Recursive search of files in a path
	 * @since 1.1.9
	 * @update 1.2.1 - 1.8.5
	 *
	 */
	public function find_files( $path, $pattern, $callback, $type = false ) {

		$matches = array();
		$entries = array();
		$dir = dir( $path );

		while ( false !== ( $entry = $dir->read() ) ) {
			$entries[] = $entry;
		}
		$dir->close();
		foreach ( $entries as $entry ) {
			$fullname = $path . $this->ossep . $entry;
			if ( '.' != $entry && '..' != $entry && is_dir( $fullname ) ) {
				$this->find_files( $fullname, $pattern, $callback, $type );
			} elseif ( is_file( $fullname ) && preg_match( $pattern, $entry ) ) {
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
	public function available_mo_files( $path, $filename, $wp_lang_dir = false ) {
		$shortfilename = str_replace( '.mo', '', $filename );
		$alert = '<span class="red-alert">' . __( 'Uncommon filename', 'xili-language' ) . '</span>';
		if ( $wp_lang_dir ) {

			$message = '<em>' . __( 'in WP_LANG_DIR/themes', 'xili-language' ) . '</em>'; // not used

		} else {
			if ( ! in_array( strlen( $shortfilename ), array( 2, 3, 5, 6 ) ) ) { // 2.19.3 for file like kab.mo or haw_US.mo
				if ( false === strpos( $shortfilename, 'local-' ) ) {
					$message = $alert;
				} else {
					$message = '<em>' . __( "Site's values", 'xili-language' ) . '</em>';
				}
			} elseif ( false === strpos( $shortfilename, '_' ) && in_array( strlen( $shortfilename ), array( 5, 6 ) ) ) {
				$message = $alert;
			} else {
				$message = '';
			}
		}

		if ( ! is_child_theme() ) {
			$theme_directory = $this->get_template_directory;
		} elseif ( is_child_theme() ) {
			if ( false === strpos( $path, $this->get_parent_theme_directory . '/' ) ) { // / to avoid -xili
				$theme_directory = $this->get_template_directory; // show child
			} else {
				$theme_directory = $this->get_parent_theme_directory; // show parent
			}
		}
		if ( $wp_lang_dir ) {
			echo $shortfilename . '<br />';
		} else {
			echo $shortfilename . ' (' . $this->ossep . str_replace( $this->ossep, '', str_replace( $theme_directory, '', $path ) ) . ') ' . $message . '<br />';
		}

	}


	/********************************** Edit Post UI ***********************************/

	/**
	 * style for new dashboard
	 * @since 2.5
	 * @updated 2.6
	 */
	public function admin_init() {
				// test successively style file in theme, plugins, current plugin subfolder
		if ( file_exists( get_stylesheet_directory() . '/xili-css/xl-style.css' ) ) { // in child theme
				$this->exists_style_ext = true;
				$this->style_folder = get_stylesheet_directory_uri();
				$this->style_flag_folder_path = get_stylesheet_directory() . '/images/flags/';
				$this->style_message = __( 'xl-style.css is in sub-folder <em>xili-css</em> of current theme folder', 'xili-language' );
		} elseif ( file_exists( WP_PLUGIN_DIR . $this->xilidev_folder . '/xili-css/xl-style.css' ) ) { // in plugin xilidev-libraries
				$this->exists_style_ext = true;
				$this->style_folder = plugins_url() . $this->xilidev_folder;
				$this->style_flag_folder_path = WP_PLUGIN_DIR . $this->xilidev_folder . '/xili-css/flags/';
				/* translators: */
				$this->style_message = sprintf( __( 'xl-style.css is in sub-folder <em>xili-css</em> of %s folder', 'xili-language' ), $this->style_folder );
		} elseif ( file_exists( XILILANGUAGE_PLUGIN_DIR . 'xili-css/xl-style.css' ) ) { // in current plugin
				$this->exists_style_ext = true;
				$this->style_folder = $this->plugin_url;
				$this->style_flag_folder_path = XILILANGUAGE_PLUGIN_DIR . 'xili-css/flags/';
				$this->style_message = __( 'xl-style.css is in sub-folder <em>xili-css</em> of xili-language plugin folder (example)', 'xili-language' );
		} else {
				$this->style_message = __( 'no xl-style.css', 'xili-language' );
		}
		// build now default style
		if ( $this->exists_style_ext && ( $this->style_folder != $this->plugin_url ) ) {
			wp_register_style( 'xili_language_stylesheet', $this->style_folder . '/xili-css/xl-style.css', XILILANGUAGE_VER );
		}
	}

	/**
	 * update term metas
	 * @since  2.22 [<description>]
	 */
	public function update_xili_language_term_metas_from_form( $term_id ) {
		// update xili_language_term
		$language_term = Xili_Language_Term::get_instance( $term_id );
		if ( $language_term && ! is_wp_error( $language_term ) ) {

			$one_language = $language_term->language_data; // metas in object

			// array('charset'=>"",'hidden'=>"");
			$one_language->visibility = 1 - ( ( isset( $_POST['language_hidden'] ) ) ? 1 : 0 );
			$one_language->charset = $_POST['language_charset'];
			$one_language->alias = ( isset( $_POST['language_alias'] ) ) ? $_POST['language_alias'] : $one_language->slug;

			// values from GP_locale (by ISO)
			$locale = GP_Locales::by_field( 'wp_locale', $one_language->iso_name );
			$one_language->text_direction = ( $locale ) ? $locale->text_direction : 'ltr'; // rtl changed
			$one_language->native_name = ( $locale ) ? $locale->native_name : '';

			// UX info

			// 'front_back_side' => 'both',
			if ( $one_language->visibility ) {
				if ( in_array( $one_language->iso_name, get_available_languages() ) ) {
					$one_language->front_back_side = 'both';
				} else {
					$one_language->front_back_side = 'front';
				}
			} else {
				if ( in_array( $one_language->iso_name, get_available_languages() ) ) {
					$one_language->front_back_side = 'back';
				} else {
					$one_language->front_back_side = 'na'; // not available - must be improved
				}
			}

			// 'flag' => ''
			//  analyze if exists
			$url = do_shortcode( "[xili-flag lang={$one_language->slug}]" );
			$one_language->flag = $url; // '' if not exists

			// wp translation datas

			$array_one = xili_update_wp_glot_metas( array( $term_id ), $this->available_translations ); // in class-xili-language-term.php
			$one_language_wp_metas = $array_one[ $term_id ];
			foreach ( $one_language_wp_metas as $wp_term_meta_key => $value ) {
				$one_language->{$wp_term_meta_key} = $value;
			}

			$meta_keys = array_keys( $language_term->termmetas );

			foreach ( $meta_keys as $term_meta_key ) {
				update_term_meta( $term_id, $term_meta_key, $one_language->{$term_meta_key} );
			}
		}
	}

	public function propagate_categories_to_linked( $post_id, $curlang ) {

		$listlanguages = $this->get_listlanguages();
		foreach ( $listlanguages as $language ) {
			if ( $language->slug != $curlang ) {
				// get to post
				$otherpost = $this->linked_post_in( $post_id, $language->slug );
				if ( $otherpost ) {
					$this->propagate_categories( $post_id, $otherpost, 'erase' );
				}
			}
		}
	}


	/**
	 * add styles in edit msg screen
	 *
	 * @since 2.5
	 *
	 */
	public function print_styles_cpt_edit() {
		global $post;

		$custompoststype = $this->authorized_custom_post_type();
		$custompoststype_keys = array_keys( $custompoststype );
		$type = get_post_type( $post->ID );
		if ( in_array( $type, $custompoststype_keys ) && 'enable' == $custompoststype[ $type ]['multilingual'] ) {
			$insert_flags = ( 'on' == $this->xili_settings['external_xl_style'] );
			echo '<!---- xl css ----->' . "\n";
			echo '<style type="text/css" media="screen">' . "\n";
			echo '#msg-states { width:79%; float:left; overflow:hidden; }' . "\n";
			echo '#msg-states-comments { width:18.5%; margin-left: 80%; border-left:0px #666 solid; padding:10px 10px 0; }' . "\n";
			echo ".clearb1 {clear:both; height:1px;} \n"; // 2.8.8

			echo '.xlversion {font-size:80%; margin-top:20px; text-align:right;}';

			echo '.alert { color:red;}' . "\n";
			echo '.message { font-size:80%; color:#bbb !important; font-style:italic; }' . "\n";
			echo '.editing { color:#333; background:#fffbcc;}' . "\n";
			echo '.abbr_name:hover {border-bottom:1px dotted grey;}' . "\n";
			echo '#postslist {width: 100%; border:1px solid grey ;}' . "\n";

			echo '.language {width: 80px;}' . "\n";
			echo '.postid {width: 35px;}' . "\n";

			echo '.status {width: 60px;}' . "\n";
			echo '.action {width: 120px;}' . "\n";

			echo '.inputid {width: 55px; font-size:90%}' . "\n";

			$flag_uri = $this->flag_in_title_input( $post->ID, $insert_flags ); // 2.18.1
			if ( $flag_uri ) {
				echo '#titlewrap input {background-image : url(' . $flag_uri . '); background-position :98.5% center; background-repeat : no-repeat; }' . "\n";
			}

			echo '.postsbody tr > th span { display:inline-block; height: 20px; }' . "\n";
			$listlanguages = $this->get_listlanguages();
			if ( get_stylesheet_directory_uri() == $this->style_folder ) {
				$folder_url = $this->style_folder . '/images/flags/';
			} else {
				$folder_url = $this->style_folder . '/xili-css/flags/';
			}
			foreach ( $listlanguages as $language ) {
				$ok = false;
				$flag_id = $this->get_flag_series( $language->slug, 'admin' );
				if ( 0 != $flag_id ) {
					$flag_uri = wp_get_attachment_url( $flag_id );
					$ok = true;
				} else {
					//$flag_uri = $folder_url . $language->slug .'.png';
					$ok = file_exists( $this->style_flag_folder_path . $language->slug . '.png' );
					$flag_uri = $folder_url . $language->slug . '.png';
				}

				if ( $insert_flags && $ok ) {
					echo '.postsbody tr.lang-' . $language->slug . ' > th span { display:inline-block; text-indent:-9999px ; height: 20px; }' . "\n";
					echo 'tr.lang-' . $language->slug . ' th { background: transparent url(' . $flag_uri . ') no-repeat 60% center; }' . "\n";
				}
			}

			echo '</style>' . "\n";

			if ( $this->exists_style_ext && $insert_flags ) {
				wp_enqueue_style( 'xili_language_stylesheet' );
			}
		} elseif ( 'attachment' == $type ) { // 2.18.1
			$insert_flags = ( 'on' == $this->xili_settings['external_xl_style'] );
			echo '<!---- xl css ----->' . "\n";
			echo '<style type="text/css" media="screen">' . "\n";
			$flag_uri = $this->flag_in_title_input( $post->ID, $insert_flags );
			if ( $flag_uri ) {
				echo '#titlewrap input {background-image : url(' . $flag_uri . '); background-position :98.5% center; background-repeat : no-repeat; }' . "\n";
				echo '#attachment_caption, #attachment_alt, #attachment_content {background-image : url(' . $flag_uri . '); background-position :98.5% center; background-repeat : no-repeat; }' . "\n";
			}
			echo '</style>' . "\n";
		}
	}

	// used in cpt and attachment
	public function flag_in_title_input( $post_id, $insert_flags ) {
		$lang = $this->get_post_language( $post_id ); //slug
		if ( get_stylesheet_directory_uri() == $this->style_folder ) {
			$folder_url = $this->style_folder . '/images/flags/';
		} else {
			$folder_url = $this->style_folder . '/xili-css/flags/';
		}
		$flag_id = $this->get_flag_series( $lang, 'admin' );
		if ( 0 != $flag_id ) {
			$flag_uri = wp_get_attachment_url( $flag_id );
			$ok = true;
		} else {
			$flag_uri = $folder_url . $lang . '.png';
			$ok = file_exists( $this->style_flag_folder_path . $lang . '.png' );
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
	public function hide_lang_post_meta( $protected, $meta_key, $meta_type ) {
		if ( 'post' == $meta_type && QUETAG . '-' == substr( $meta_key, 0, strlen( QUETAG ) + 1 ) ) {
			$protected = true;
		}
		return $protected;
	}

	/**
	 * test of tracs http://core.trac.wordpress.org/ticket/18979#comment:2
	 */

	public function hide_lang_post_meta_popup( $keys, $limit = 10 ) {
		global $wpdb, $post;
		$q = "SELECT meta_key FROM $wpdb->postmeta";
		$post_type = get_post_type( $post->ID );
		if ( ! empty( $post_type ) ) {
			$q .= $wpdb->prepare( " INNER JOIN $wpdb->posts ON post_id = ID WHERE post_type LIKE %s", $post_type );
		}

		$q .= " GROUP BY meta_key HAVING ( meta_key NOT LIKE '\_%' AND meta_key NOT LIKE '" . QUETAG . "-%' ) ORDER BY meta_key LIMIT $limit";
		$keys = $wpdb->get_col( $q );
		//$keys = apply_filters( 'postmeta_form_keys', $keys, $post_type );
		if ( $keys ) {
			natcasesort( $keys );
		}
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
	 * @param $post_id
	 */
	public function xili_language_add( $post_id, $post ) {

		$posttypes = array_keys( $this->xili_settings['multilingual_custom_post'] );
		$posttypes[] = 'post';
		$posttypes[] = 'page';
		$thetype = $post->post_type;
		if ( in_array( $thetype, $posttypes ) ) {

			$listlanguages = $this->get_listlanguages();
			$previous_lang = $this->get_post_language( $post_id );

			if ( isset( $_POST['_inline_edit'] ) ) { /* when in quick_edit (edit.php) */
				$sellang = $_POST['xlpop'];
				if ( '' != $sellang ) {

					if ( $sellang != $previous_lang ) {
						// move a language
						// clean linked targets
						foreach ( $listlanguages as $language ) {

							$target_id = get_post_meta( $post_id, QUETAG . '-' . $language->slug, true );
							if ( '' != $target_id ) {
								if ( '' != $previous_lang ) {
									delete_post_meta( $target_id, QUETAG . '-' . $previous_lang );
								}
								update_post_meta( $target_id, QUETAG . '-' . $sellang, $post_id );
							}
						}
						wp_delete_object_term_relationships( $post_id, TAXONAME );

					}

					// $return = wp_set_object_terms( $post_id, $sellang, TAXONAME );
					$return = $this->multiple_languages_set( 'set', $post_id, $sellang );

				} else { // undefined
					if ( ! isset( $_GET['action'] ) ) { // trash - edit

						// clean linked targets
						foreach ( $listlanguages as $language ) {
							//delete_post_meta( $post_id, QUETAG.'-'.$language->slug ); // erase translated because undefined
							$target_id = get_post_meta( $post_id, QUETAG . '-' . $language->slug, true );
							if ( '' != $target_id ) {
								delete_post_meta( $target_id, QUETAG . '-' . $previous_lang );
							}
						}

						wp_delete_object_term_relationships( $post_id, TAXONAME );
					}
				}

				// bulk-edit via ajax 2.9.10
			} elseif ( isset( $_GET['bulk_edit'] ) ) {
				return;

			} else {
				// post-edit single
				$sellang = ( isset( $_POST['xili_language_set'] ) ) ? $_POST['xili_language_set'] : '';
				if ( '' != $sellang && 'undefined' != $sellang ) {
					if ( $sellang != $previous_lang && '' != $previous_lang ) {
						// move a language
						// clean linked targets
						foreach ( $listlanguages as $language ) {

							$target_id = get_post_meta( $post_id, QUETAG . '-' . $language->slug, true );
							if ( '' != $target_id ) {
								delete_post_meta( $target_id, QUETAG . '-' . $previous_lang );
								update_post_meta( $target_id, QUETAG . '-' . $sellang, $post_id );
							}
						}
						//wp_delete_object_term_relationships( $post_id, TAXONAME );
						$this->multiple_languages_set( 'delete', $post_id, $sellang );
					}
					//$return = wp_set_object_terms($post_id, $sellang, TAXONAME);
					$return = $this->multiple_languages_set( 'set', $post_id, $sellang );

				} elseif ( 'undefined' == $sellang ) {

					// clean linked targets
					foreach ( $listlanguages as $language ) {

						$target_id = get_post_meta( $post_id, QUETAG . '-' . $language->slug, true );
						if ( '' != $target_id ) {
							delete_post_meta( $target_id, QUETAG . '-' . $previous_lang );
							delete_post_meta( $post_id, QUETAG . '-' . $language->slug ); // 2.22
						}
					}

					// now undefined
					// wp_delete_object_term_relationships( $post_id, TAXONAME );
					$this->multiple_languages_set( 'undefine', $post_id, $previous_lang );
					delete_post_meta( $post_id, '_multiple_lang' ); // 2.22.4
				}

				$curlang = $this->get_cur_language( $post_id ); // array

				/* the linked posts set by author in postmeta */

				foreach ( $listlanguages as $language ) {
					$inputid = 'xili_language_' . QUETAG . '-' . $language->slug;
					$recinputid = 'xili_language_rec_' . QUETAG . '-' . $language->slug;
					$linkid = ( isset( $_POST[ $inputid ] ) ) ? $_POST[ $inputid ] : 0;
					$reclinkid = ( isset( $_POST[ $recinputid ] ) ) ? $_POST[ $recinputid ] : 0; /* hidden previous value */
					$langslug = QUETAG . '-' . $language->slug;

					if ( $reclinkid != $linkid ) { /* only if changed value or created since 1.3.0 */
						if ( ( is_numeric( $linkid ) && 0 == $linkid ) || '' == $linkid ) {
							delete_post_meta( $post_id, $langslug );
						} elseif ( is_numeric( $linkid ) && $linkid > 0 ) {
							// test if possible 2.5.1
							if ( $this->is_post_free_for_link( $post_id, $curlang[ QUETAG ], $language->slug, $linkid ) ) {
								update_post_meta( $post_id, $langslug, $linkid );

								if ( '-1' == $reclinkid ) {
									update_post_meta( $linkid, QUETAG . '-' . $sellang, $post_id );
								}
								// update target 2.5
								foreach ( $listlanguages as $metalanguage ) {
									if ( $metalanguage->slug != $language->slug && $metalanguage->slug != $curlang[ QUETAG ] ) {
										$id = get_post_meta( $post_id, QUETAG . '-' . $metalanguage->slug, true );
										if ( '' != $id ) {
											update_post_meta( $linkid, QUETAG . '-' . $metalanguage->slug, $id );
										}
									}
								}
								update_post_meta( $linkid, QUETAG . '-' . $curlang[ QUETAG ], $post_id ); // cur post
								$return = wp_set_object_terms( $linkid, $language->slug, TAXONAME ); // to verify 2.22

							}
						}
					}
				}
			}
		}
	}

	/**
	 * if multiple lang mode - manage multiple languages
	 * @since 2.22
	 * only called in post edit page
	 */
	public function multiple_languages_set( $mode, $post_id, $sellang = '' ) {
		if ( $this->multiple_lang ) {
			// analyze multiple lang in form
			if ( 'undefine' == $mode ) {
				if ( metadata_exists( 'post', $post_id, '_multiple_lang' ) && $lang_array = get_post_meta( $post_id, '_multiple_lang', true ) ) {
					foreach ( $lang_array as $onelang ) {
						wp_remove_object_terms( $post_id, $onelang, TAXONAME );
					}
				}
				$this->multiple_language_update_meta( $post_id, $sellang, 0, $mode );
			} elseif ( 'delete' == $mode ) {
				if ( ! isset( $_POST[ 'multi_lang_' . $sellang ] ) && ! isset( $_POST[ 'xili_language_' . QUETAG . '-' . $sellang ] ) ) {
					wp_remove_object_terms( $post_id, $sellang, TAXONAME );
					$this->multiple_language_update_meta( $post_id, $sellang, 0, 'delete' );
				}
			} elseif ( 'set' == $mode ) {
				wp_add_object_terms( $post_id, $sellang, TAXONAME );
				$this->multiple_language_update_meta( $post_id, $sellang );
				// add checked another
				$listlanguages = $this->get_listlanguages();
				$k = 0;
				foreach ( $listlanguages as $onelanguage ) {
					if ( '' != $sellang && $onelanguage->slug != $sellang ) {
						if ( isset( $_POST[ 'multi_lang_' . $onelanguage->slug ] ) ) {
							$k++;
							wp_add_object_terms( $post_id, $onelanguage->slug, TAXONAME );
							$this->multiple_language_update_meta( $post_id, $onelanguage->slug, $k );
						} else {
							wp_remove_object_terms( $post_id, $onelanguage->slug, TAXONAME );
							$this->multiple_language_update_meta( $post_id, $onelanguage->slug, 0, 'delete' );
						}
					}
				}
			}
		} else {
			if ( 'set' == $mode ) {
				return wp_set_object_terms( $post_id, $sellang, TAXONAME );
			} elseif ( 'delete' == $mode ) {
				wp_delete_object_term_relationships( $post_id, TAXONAME );
			}
		}
	}

	/**
	 * set or update multiple language metadata (array with primary as index 0)
	 *
	 * @since 2.22
	 */
	public function multiple_language_update_meta( $post_id, $lang_slug, $lang_order = 0, $mode = 'update' ) {
		$lang_array = get_post_meta( $post_id, '_multiple_lang', true );

		if ( ! $lang_array ) {
			$lang_array = array();
		}

		if ( 'update' == $mode ) {
			if ( in_array( $lang_slug, $lang_array ) ) {
				// change order if
				$key = array_search( $lang_slug, $lang_array );
				if ( $key != $lang_order ) {
					$temp = $lang_array[ $lang_order ];
					$lang_array[ $lang_order ] = $lang_slug;
					$lang_array[ $key ] = $temp;
					return update_post_meta( $post_id, '_multiple_lang', $lang_array );
				}
			} else {
				if ( 0 == $lang_order && ! $lang_array ) {
					array_unshift( $lang_array, $lang_slug );
				} else {
					$lang_array[ $lang_order ] = $lang_slug;
				}
				return update_post_meta( $post_id, '_multiple_lang', $lang_array );
			}
		} elseif ( 'delete' == $mode || 'undefine' == $mode ) {
			$key = array_search( $lang_slug, $lang_array );
			$new_lang_post = '';
			if ( 'delete' == $mode ) {
				$new_lang_post = $this->get_post_language( $post_id );
			}
			if ( '' == $new_lang_post ) {
				// ?? delete other secondary language ???
				delete_post_meta( $post_id, '_multiple_lang' );
			} elseif ( $key ) {
				array_splice( $lang_array, $key );
				return update_post_meta( $post_id, '_multiple_lang', $lang_array );
			}
		} else {
			return false;
		}
	}

	/**
	 * add to secure manual input of linked post
	 *
	 * @since 2.5.1
	 *
	 */
	public function is_post_free_for_link( $from_post_id, $from_lang, $target_lang, $target_id ) {

		if ( $from_post_id == $target_id ) {
			return false; // obvious
		}

		if ( $this->temp_get_post( $target_id ) ) {
			// check if target ID is not yet in another lang
			$target_slug = $this->get_post_language( $target_id );
			if ( '' == $target_slug ) {
				return true; // undefined
			} elseif ( $target_slug == $target_lang ) {
				// check target is not yet link to other
				$id = get_post_meta( $target_id, QUETAG . '-' . $from_lang, true );
				if ( '' != $id ) {
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
	public function fixes_post_slug( $post_id, $post ) {
		if ( defined( 'XDMSG' ) && XDMSG == get_post_type( $post_id ) ) {
			return;
		}

		$translation_state = get_post_meta( $post_id, $this->translation_state, true );

		$checked = ( isset( $_POST['xl_permalink_option'] ) );
		if ( false !== strpos( $translation_state, 'initial' ) && $checked ) {
			global $wpdb;
			$where = array( 'ID' => $post_id );
			$what = array();
			$what['post_name'] = sanitize_title( $post->post_title );

			if ( array() != $what ) {
				$er = $wpdb->update( $wpdb->posts, $what, $where );
			}

			if ( in_array( $post->post_status, array( 'publish', 'private', 'future', 'pending' ) ) ) {
				delete_post_meta( $post_id, $this->translation_state );
			}
		}
	}

	/**
	 * Return list of linked posts
	 * used in edit list
	 *
	 * @param    <type> $post_id   The post identifier.
	 * @param    string $mode      The mode.
	 * @param    string $type      The type.
	 * @param    string $separator The separator.
	 *
	 * @return   array 	list.
	 */
	public function translated_in( $post_id, $mode = 'link', $type = 'post', $separator = ' ' ) {

		$curlang = $this->get_cur_language( $post_id ); // array !
		$listlanguages = $this->get_listlanguages();
		$trans = array();
		if ( array() != $curlang ) { // 2021-04.
			foreach ( $listlanguages as $language ) {
				if ( $language->slug != $curlang[ QUETAG ] ) {
					$otherpost = $this->linked_post_in( $post_id, $language->slug );
					if ( $otherpost ) {
						$linepost = $this->temp_get_post( $otherpost );
						if ( $linepost ) {
							switch ( $mode ) {
								case 'link':
									$detail = false;
									$title_type = $type;
									if ( 'post' == $type || 'edit_attachment' == $type ) {
										$link = 'post.php?post=' . $linepost->ID . '&action=edit';
										if ( 'edit_attachment' == $type ) {
											$detail = true;
										}
										if ( 'edit_attachment' == $type ) {
											$title_type = 'attachment';
										}
									} elseif ( 'attachment' == $type ) {
										$link = 'media.php?attachment_id=' . $linepost->ID . '&action=edit'; // not used (small settings screen) !
									}
									$a_content = ( $detail ) ? $language->description . ' (' . $language->name . ')' : $language->name;
									/* translators: */
									$title = sprintf( __( 'link to edit %1$s %3$d in %2$s', 'xili-language' ), $title_type, $language->description, $linepost->ID );
									$trans[] = sprintf( '<a href="%1$s" title="%2$s" class="lang-%4$s" >%3$s</a>', $link, $title, $a_content, $language->slug ); // no localization 2.18.1 !
									break;
								case 'array':
									$trans[ $language->slug ] = array(
										'post_ID' => $linepost->ID,
										'name' => $language->name,
										'description' => $language->description,
									);
									break;

							}
						}
					}
				}
			}
		}
		if ( 'array' == $mode ) {
			return $trans;
		}

		$list = implode( $separator, $trans );
		return $list;
	}

	/**
	 * Update before main list before menus structure html building... filter wp_get_nav_menus
	 * not in trait because wp_query
	 * @since 2.12.2
	 *
	 */
	public function _update_menus_insertion_points( $terms, $args ) {
		if ( function_exists( 'get_current_screen' ) ) { // thanks to giuseppecabgmail.com - customize broken 2.13.1
			$screen = get_current_screen();
			if ( ! $screen || 'nav-menus' != $screen->base ) {
				// 2.13.2 - null if no current screen
				return $terms;
			}
			$query = new WP_Query();
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
				foreach ( $menu_items as $menu_item ) {
					$classes = get_post_meta( $menu_item->ID, '_menu_item_classes', true );
					if ( false === strpos( serialize( $classes ), 'xlmenuslug' ) ) { // update previous menus insertion points
						$to_modify = 0;
						$menu_classes = array();
						foreach ( $classes as $class ) {
							if ( false !== strpos( $class, 'xlmenulist-' ) ) {
								$to_modify++;
								$menu_id_list = str_replace( 'xlmenulist-', '', $class );
								$menu_ids = explode( '-', $menu_id_list );
								$newclass = 'xlmenuslug';
								// search slug
								foreach ( $menu_ids as $menu_id ) {
									if ( term_exists( (int) $menu_id, 'nav_menu' ) ) {
										$nav_menu = get_term( (int) $menu_id, 'nav_menu' );
										$newclass .= $this->menu_slug_sep . $nav_menu->slug;
									}
								}
								//
								$menu_classes[] = $newclass;
							} else {
								$menu_classes[] = $class;
							}
						}
						if ( $to_modify > 0 ) {
							update_post_meta( $menu_item->ID, '_menu_item_classes', $menu_classes );
						}
					}
				}
			}
		}
		return $terms;
	}

	/**
	 * private function - one row getting from object xili_language_term
	 *
	 * @since 2.22 built with data from xili_language_term
	 */
	public function one_admin_language_row( $language_term, $trclass ) {
		global $wpdb;

		$language = Xili_Language_Term::get_instance( $language_term->term_id );
		$language_data = $language->language_data; // with term metas..

		$is_mo = ! empty( $language->name ) && array_key_exists( $language->name, (array) $this->available_languages() );

		$h = ( $language_data->visibility ) ? '&#10004;' : '&nbsp;';
		$h .= ( '' != $language_data->charset ) ? '&nbsp;+' : '';

		$mo_available_for_dashboard = ( $is_mo ) ? '&#10004;' : '';

		$language->count = number_format_i18n( $language->count );
		// count for each CPT
		$counts = array();
		$title = array();
		$custompoststype = $this->authorized_custom_post_type( true ); // 2.13.2 b

		foreach ( $custompoststype as $key => $customtype ) {
			$counts[ $key ] = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts WHERE $wpdb->posts.ID = $wpdb->term_relationships.object_id AND post_status = 'publish' AND term_taxonomy_id = %d AND post_type = %s", $language->term_id, $key ) );
			$title[] = $customtype['name'] . ' = ' . $counts[ $key ];
		}
		$title = implode( ' | ', $title );
		$posts_count = ( $language->count > 0 ) ? '<a title= "' . $title . '" href="edit.php?lang=' . $language->slug . '">' . $language->count . '</a>' : $language->count;

		$line = '<tr id="lang-' . $language->term_id . '" class="lang-' . $language->slug . $trclass . '" >'
		. '<th scope="row" class="lang-id" ><span class="lang-flag">' . $language->term_id . '<span></th>'
		. '<td>' . $language_data->iso_name . '</td>';

		$link = wp_nonce_url( '?action=edit&amp;page=language_page&amp;term_id=' . $language->term_id, 'edit-' . $language->term_id );
		$edit = '<a href="' . $link . '" >' . __( 'Edit' ) . '</a>&nbsp;|';
		/* delete link*/
		$link = wp_nonce_url( '?action=delete&amp;page=language_page&amp;term_id=' . $language->term_id, 'delete-' . $language->term_id );
		$edit .= '&nbsp;<a href="' . $link . '" class="delete" >' . __( 'Delete' ) . '</a>';

		if ( $this->alias_mode ) {
			$alias_val = ( '' == $language_data->alias ) ? ' ? ' : $language_data->alias;

			$key_slug = array_keys( $this->langs_slug_shortqv_array, $alias_val );
			if ( 1 == count( $key_slug ) ) {
				$line .= '<td>' . $alias_val . '</td>';
			} else {
				$line .= sprintf( '<td><span class="red-alert" title="%s">', esc_attr( 'the default alias needs to be defined', 'xili-language' ) ) . $alias_val . '</span></td>';
			}
		}

		$line .= '<td>' . $language_data->english_name . '</td>'
			. '<td>' . $language_data->native_name . '</td>'
			. '<td>' . $language_data->slug . '</td>'
			. '<td>' . $language->term_order . '</td>'
			. '<td class="col-center" >' . $h . '</td>'
			. '<td class="col-center" >' . $mo_available_for_dashboard . '</td>'
			. '<td class="col-center" >' . $posts_count . '</td>'
			. '<td class="col-center" >' . $edit . "</td>\n\t</tr>\n";

		echo $line;

	}


	/**
	 * Update msgid list when a term is created
	 *
	 * @updated 2.8.4.2
	 *
	 */
	public function update_xd_msgid_list( $taxonomy ) {
		if ( class_exists( 'xili_dictionary' ) ) {
			global $xili_dictionary;

			if ( isset( $_POST['tag-name'] ) && '' != $_POST['tag-name'] ) {
				$nbterms = $xili_dictionary->xili_read_catsterms_cpt( $taxonomy, $xili_dictionary->local_tag );

				if ( $nbterms[0] + $nbterms[1] > 0 ) {
					/* translators: */
					echo '<p>' . sprintf( __( 'xili-dictionary: msgid list updated (n=%1$s, d=%2$s', 'xili-dictionary' ), $nbterms[0], $nbterms[1] ) . ')</p>';
				}
			}
		} else {
			echo '<p><strong>' . __( 'xili-dictionary plugin is not active to prepare language local .po files.', 'xili-language' ) . '</strong></p>';
		}
	}


	/**
	 * set new default value screen options for new admin user
	 *
	 * @since 2.9.30
	 */
	public function default_nav_menus_screen_options( $meta_id, $object_id, $meta_key, $_meta_value ) {

		if ( 'metaboxhidden_nav-menus' == $meta_key ) {

			if ( user_can( $object_id, 'edit_theme_options' ) ) {
				$intersect = array_intersect( $_meta_value, array( 'insert-xl-list', 'insert-xlspage-list', 'insert-xlmenus-list' ) );
				if ( array() != $intersect ) {
					update_user_option( $object_id, 'metaboxhidden_nav-menus', array_diff( $_meta_value, array( 'insert-xl-list', 'insert-xlspage-list', 'insert-xlmenus-list' ) ), true );
				}
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
	public function widget_visibility_admin( $widget, $return, $instance ) {

		$dropdown = '<select name="' . $widget->id . '_lang_show">';
		$selected = ( isset( $instance['xl_show'] ) ) ? selected( $instance['xl_show'], 'show', false ) : '';
		$dropdown .= '<option value="show" ' . $selected . '>' . __( 'Show', 'xili-language' ) . '</option>';
		$selected = ( isset( $instance['xl_show'] ) ) ? selected( $instance['xl_show'], 'hidden', false ) : '';
		$dropdown .= '<option value="hidden" ' . $selected . '>' . __( 'Hidden', 'xili-language' ) . '</option>';
		$dropdown .= '</select>';

		printf(
			'<hr /><label for="%1$s">%2$s %3$s</label><br />',
			esc_attr( $widget->id . '_lang_rule' ),
			esc_html__( 'Visibility of this widget:', 'xili-language' ),
			$dropdown
		);

		$dropdown = '<select name="' . $widget->id . '_lang_rule">';
		$dropdown .= '<option value="0">' . __( 'All languages', 'xili-language' ) . '</option>';
		foreach ( $this->langs_slug_fullname_array as $slug => $name ) {
			$selected = ( isset( $instance['xl_lang'] ) ) ? selected( $instance['xl_lang'], $slug, false ) : '';
			$dropdown .= '<option value="' . $slug . '" ' . $selected . '>' . $name . '</option>';
		}
		$dropdown .= '</select>';

		printf(
			'<label for="%1$s">%2$s %3$s</label><br /><small>%4$s</small><hr />',
			esc_attr( $widget->id . '_lang_rule' ),
			esc_html__( 'when:', 'xili-language' ),
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
		$key = $widget->id . '_lang_show';
		if ( ! empty( $_POST[ $key ] ) && in_array( $_POST[ $key ], array( 'show', 'hidden' ) ) ) {
			$instance['xl_show'] = $_POST[ $key ];
		} else {
			unset( $instance['xl_show'] );
		}

		$key = $widget->id . '_lang_rule';
		if ( ! empty( $_POST[ $key ] ) && in_array( $_POST[ $key ], array_keys( $this->langs_slug_fullname_array ) ) ) {
			$instance['xl_lang'] = $_POST[ $key ];
		} else {
			unset( $instance['xl_lang'] );
		}

		return $instance;
	}

}
