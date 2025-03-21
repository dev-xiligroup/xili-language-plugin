<?php
/**
 * Class xili_language
 *
 * @since  20190430 in single file
 *
 */

class Xili_Language {

	use Xili_Main\Xili_Language_Flags;
	use Xili_Main\Xili_Language_ShortCodes;
	use Xili_Main\Xili_Language_Menus;
	use Xili_Main\Xili_Language_Taxinomies;
	use Xili_Main\Xili_Language_Comments;
	use Xili_Main\Xili_Language_Themes;
	use Xili_Main\Xili_Language_Links;
	use Xili_Main\Xili_Language_Head;
	use Xili_Main\Xili_Language_Widgets;


	/**
	 * Holds the singleton instance of this class
	 * @since 2.21.1
	 * @var xili_language
	 */
	private static $instance = false;

	public $xili_settings; /* saved in options */

	public $langs_group_id = 0; /* group ID and Term Taxo ID */
	public $langs_group_tt_id = 0;

	public $default_lang; /* language of config.php*/
	public $default_slug; /* slug of language of config.php since 1.5.3 wpmu*/
	public $curlang;
	public $cur_language; /* 2.22 - object of class xili_language_term */

	private $ready_to_join_filter = false; /* to avoid sql error if no join - posts_where is called before posts_join filter 2.12 */

	public $thetextdomain = ''; /* since 1.5.2 - used if multiple */
	public $langstate; /* undefined or not */

	public $default_dir = ''; /* undefined or not in WP config '' or rtl or ltr */
	public $curlang_dir = ''; /* undefined or not according array */
	// var $rtllanglist = 'ar-dv-fa-ha-he-ps-ur-uz-yi'; /* default-list - can be set after class instantiation - 2.8.7 improved since JetPack */

	public $get_archives_called = array(); /* if != '' - insert lang in link */
	public $idx = array(); /* used to identify filter or action set from this class - since 0.9.9.6 */
	public $theme_locale = false; /* to control locale hook */
	public $ossep = '/'; /* for recursive file search in xamp */

	public $current_lang_query_tag = ''; /* since 1.3.0 */
	public $temp_lang_query_tag = '';

	public $domaindetectmsg = ''; // msg used if pb with load_theme_textdomain

	public $langs_list_options = array(); // now set in init 2.8.6

	public $comment_form_labels = array( // since 1.6.0 for comment_form - updated 2.16.0, 2.22.3 (comment-template)
		'name' => 'Name', // field: author
		'email' => 'Email',
		'website' => 'Website', // field: url
		'comment' => 'Comment', // _x noun
		'youmustbe' => 'You must be <a href="%s">logged in</a> to post a comment.', // field: must_log_in
		'loggedinas' => '<a href="%1$s" aria-label="%2$s">Logged in as %3$s</a>. <a href="%4$s">Log out?</a>',
		'loggedinas_edit' => 'Logged in as %s. Edit your profile.', // field:logged_in_as
		'emailnotpublished' => 'Your email address will not be published.', // field: comment_notes_before
		'requiredmarked' => 'Required fields are marked %s',
		// 'youmayuse' => 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes: %s',
		'leavereply' => 'Leave a Reply', // field: title_reply
		'replyto' => 'Leave a Reply to %s', // field: title_reply_to
		'cancelreply' => 'Cancel reply', // field: cancel_reply_link
		'postcomment' => 'Post Comment', // field: label_submit
	);

	public $sticky_keep_original = false; // since 1.6.1 see translate_sticky_posts_ID function
	public $xl_recent_posts = false;
	public $ltd = false; // load_textdomain detected 1.8.5
	public $ltd_parent = false; // load_textdomain detected in parent if merging 2.9
	public $arraydomains = array(); // for theme domain tests
	public $show = false;

	public $langs_ids_array; // array slug => id
	public $langs_slug_name_array; // array slug => name // 2.4.2
	public $langs_slug_fullname_array; // array slug => full name // 2.6.3

	public $langs_slug_shortqv_array = array(); // array slug => short query var // 2.8.2
	public $langs_shortqv_slug_array = array(); // array short query var => slug // 2.8.2
	public $alias_mode = false; // temp

	public $show_page_on_front = false;

	/* for permalink with lang at root */
	public $lang_perma = false; // if true new permalink for root and categories /en_us/category/.... if special action available… 2.1.1
	public $lpr = ''; // 2.3.2
	public $show_page_on_front_array = array(); // array of lang=>page_id if show_on_front == page

	public $undefchar = '.'; // 2.2.3 - used to detect undefined
	private $sublang = ''; // 2.2.3 - used to detect - or fr_fr- of like private - used to search undefined
	public $doing_list_language = false; // 2.11.1 set to loop lang slug when doing list languages

	// 2.5 2.15
	// other in class admin
	public $translation_state = '_xl_translation_state'; // set to initial + coming lang when post created from dashboard metabox (to update post slug)

	// 2.6 - class admin in separate file
	public $file_file = ''; // see in construct below
	public $file_basename = '';
	public $plugin_basename = '';
	public $plugin_url = '';
	public $plugin_path = ''; // The path to this plugin - see construct

	public $xilidev_folder = '/xilidev-libraries'; //must be in plugins

	public $page_for_posts_array = array();
	public $page_for_posts_name_array = array(); // used if is_permalink
	public $page_for_posts_name_to_id_array = array(); // used if is_permalink
	public $page_for_posts_original = false; // 2.8.4
	public $is_permalink = false;

	// since 2.8.8 - insertion

	public $insertion_point_box_title = 'Languages list Insertion point';
	public $insertion_point_dummy_link = '#insertlist';
	// since 2.9.10 - insertion pages sub-selection
	public $insertion_point_box_title_page = 'Pages list Insertion point';
	public $insertion_point_dummy_link_page = '#insertpagelist';
	// since 2.9.20 - insertion menu selection
	public $insertion_point_box_title_menu = 'Menus list Insertion point';
	public $insertion_point_dummy_link_menu = '#insertmenu';

	// since 2.12 - propagate when create

	public $propagate_options_default_ref = array(
		'post_format' => array(
			'default' => '1',
			'data' => 'attribute',
			'hidden' => '',
		),
		'page_template' => array(
			'default' => '1',
			'data' => 'meta',
			'hidden' => '',
		),
		'comment_status' => array(
			'default' => '',
			'data' => 'post',
			'hidden' => '',
		),
		'ping_status' => array(
			'default' => '',
			'data' => 'post',
			'hidden' => '',
		),
		'post_parent' => array(
			'default' => '',
			'data' => 'post',
			'hidden' => '1',
		),
		'menu_order' => array(
			'default' => '',
			'data' => 'post',
			'hidden' => '1',
		),
		'thumbnail_id' => array(
			'default' => '1',
			'data' => 'meta',
			'hidden' => '',
		),
	);

	public $propagate_options_default; // will be updated by filter in xl-class-admin
	public $propagate_options_labels = array();

	public $menu_slug_sep = '--'; // use in menus insertion point class - 2.12.2

	public $unusable_domains = array( 'xili-dictionary', 'xili-language', 'xili_language_errors', 'bbpress' ); // bbPress by add-on

	public $authorized_taxonomies = array(); // used by link_template...

	public $flag_settings_name; // to get and save (in admin) current theme flag option
	// names same in widgets
	public $xili_widgets = array(
		'Xili_Widget_Recent_Posts' => array(
			'value' => 'enabled',
			'name' => 'List of recent posts',
		),
		'Xili_Language_Widgets' => array(
			'value' => 'enabled',
			'name' => 'List of languages',
		),
		'Xili_WP_Widget_Recent_Comments' => array(
			'value' => 'enabled',
			'name' => 'Recent Comments list',
		),
		'Xili_Widget_Categories' => array(
			'value' => '',
			'name' => 'Categories',
		),
	);

	public $theme_mod_to_be_filtered = array(); // array of theme_mod to be filtered - 2.18.2

	public $update_new_term_metas = false; // to update term metas since 2.22

	public $multiple_lang = false;

	public function __construct( $locale_method = false, $show = false, $class_admin = false ) {

		unset( $GLOBALS['l10n']['xili-language'] ); // to bypass _load_textdomain_just_in_time and refresh live locale changing 20171208

		if ( self::$instance && ! is_admin() ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( '*** WARNING: xili_language class cannot be constructed twice time !!! Use global $xili_language ! ***' );
			}
			return;
		}
		self::$instance = true;
		// 2.6 - class admin in separate file
		$this->file_file = __FILE__; // see in construct below
		$this->file_basename = basename( __FILE__ );
		$this->plugin_basename = plugin_basename( __FILE__ );
		$this->plugin_url = XILILANGUAGE_PLUGIN_URL; //plugins_url('', __FILE__) ;
		$this->plugin_path = XILILANGUAGE_PLUGIN_DIR; //plugin_dir_path(__FILE__) ; // with / at end
		$this->ossep = strtoupper( substr( PHP_OS, 0, 3 ) == 'WIN' ) ? '\\' : '/';

		$this->locale_method = $locale_method; /* added for compatibility with cache plugin from johan */
		$this->show = $show;
		$this->class_admin = $class_admin;

		$this->flag_settings_name = get_option( 'stylesheet' ) . '-xili-flag-options'; // if xilitheme-select, will be changed

		/* activated when first activation of plug */
		register_activation_hook( __FILE__, array( &$this, 'xili_language_activate' ) );

		register_deactivation_hook( __FILE__, array( &$this, 'remove_capabilities' ) ); //2.8.8

		if ( ! $class_admin ) {
			$this->only_parent_construct(); // 2.8.4.3
		}

	}

	private function only_parent_construct() {
		global $wp_version;
		/**
		 * get current settings
		 */
		$this->xili_settings = get_option( 'xili_language_settings', false );

		if ( false === $this->xili_settings ) {
			//1.9.1
			$this->xili_settings = $this->initial_settings();
			update_option( 'xili_language_settings', $this->xili_settings );
			set_transient( '_xl_activation_redirect', 1, 30 ); // 2.20
		} else {
			$current_xl_version = $this->xili_settings['version'];
			if ( $this->xili_settings['version'] === '1.9' ) {
				/* 2.4.0 */
				$this->xili_settings['wp_locale'] = 'wp_locale'; //wp_locale new mode
				$this->xili_settings['version'] = '2.0';
			}
			if ( $this->xili_settings['version'] === '2.0' ) {
				/* 2.4.0 */
				$this->xili_settings['available_langs'] = array(); // default array
				$this->xili_settings['version'] = '2.1';
			}
			if ( $this->xili_settings['version'] === '2.1' ) {
				/* 2.6.0 */
				$this->xili_settings['external_xl_style'] = 'on';
				$this->xili_settings['creation_redirect'] = 'redirect'; // default - after initial creation
				$this->xili_settings['version'] = '2.2';
			}
			if ( $this->xili_settings['version'] === '2.2' ) {
				/* 2.8.3 */
				$this->xili_settings['nav_menu_separator'] = '|';
				$this->xili_settings['version'] = '2.3';
			}
			if ( $this->xili_settings['version'] === '2.3' ) {
				/* 2.8.4 */
				$this->xili_settings['pforp_select'] = 'select'; // no_select,
				$this->xili_settings['version'] = '2.4';
			}
			if ( $this->xili_settings['version'] === '2.4') { /* 2.8.4.3 */
				if ( isset( $this->xili_settings['domains']['all'] ) ) {
					unset( $this->xili_settings['domains']['all'] ); // no_all for plugins
				}
				// move all value to new array
				$this->xili_settings['page_in_nav_menu_array'] = '';
				if ( isset( $this->xili_settings['navmenu_check_optionp'] ) ) {
					$key = $this->xili_settings['navmenu_check_optionp'];
					$enable = ( isset( $this->xili_settings['navmenu_check_optionp'] ) && 'enable' == $this->xili_settings['page_in_nav_menu'] ) ? 'enable' : '';

					$thenewvalue = array(
						'enable' => $enable,
						'args' => $this->xili_settings['args_page_in_nav_menu'],
					);
					if ( '' != $key ) {
						$this->xili_settings['array_navmenu_check_option_page'][ $key ] = $thenewvalue;
					}

					$this->xili_settings['page_in_nav_menu_array'] = $enable;
					unset( $this->xili_settings['navmenu_check_optionp'] );
					unset( $this->xili_settings['args_page_in_nav_menu'] );
					unset( $this->xili_settings['page_in_nav_menu'] );
				}

				$this->xili_settings['version'] = '2.5';
			}
			if ( $this->xili_settings['version'] === '2.5' ) {
				/* 2.8.4.4 */
				$this->xili_settings['list_pages_check_option'] = 'fixe';
				$this->xili_settings['version'] = '2.6';
			}
			// 'Posts selected in %s' 2.8.7
			if ( $this->xili_settings['version'] === '2.6' ) {
				$this->xili_settings['list_link_title'] = array(
					'post_selected' => 'Posts selected in %s',
					'current_post' => 'Current post in %s',
					'latest_posts' => 'Latest posts in %s', // used in xili-language list,
					'view_all_posts' => 'View all posts in %s',
				); //	the_category (and XD);
				$this->xili_settings['version'] = '2.7';
			}
			if ( $this->xili_settings['version'] === '2.7' ) {
				$this->xili_settings['mo_parent_child_merging'] = false;
				$this->xili_settings['parent_langs_folder'] = '';
				$this->xili_settings['version'] = '2.8';
			}
			if ( $this->xili_settings['version'] === '2.8' ) {
				$this->xili_settings['version'] = '2.9';
			}
			if ( $this->xili_settings['version'] === '2.9' ) {
				$this->xili_settings['version'] = '2.11';
				$this->xili_settings['enable_fc_theme_class'] = 'enable'; //

				$theme_name = get_option( 'current_theme' ); // full name for theme switcher...
				$this->xili_settings['theme_alias_cache'] = array();
				foreach ( $this->xili_settings['lang_features'] as $slug => $values ) {
					if ( isset( $this->xili_settings['lang_features'][ $slug ]['alias'] ) ) {
						$this->xili_settings['theme_alias_cache'][ $theme_name ][ $slug ] = $this->xili_settings['lang_features'][ $slug ]['alias'];
					}
				}
			}
			// 2.12.1
			if ( $this->xili_settings['version'] === '2.11' ) {
				$this->xili_settings['version'] = '2.12';
				if ( true == $this->xili_settings['mo_parent_child_merging'] ) {
					$this->xili_settings['mo_parent_child_merging'] = 'parent-priority';
				}
				//update_option('xili_language_settings', $this->xili_settings);
			}
			// 2.15.1
			if ( $this->xili_settings['version'] === '2.12' ) {
				$this->xili_settings['version'] = '2.15';
				$this->xili_settings['langs_group_id'] = 0;
				$this->xili_settings['langs_group_tt_id'] = 0;
			}
			// 2.15.1
			if ( $this->xili_settings['version'] === '2.15') {
				$this->xili_settings['version'] = '2.16';
				$this->xili_settings['specific_widget'] = $this->xili_widgets; // 2.16.4
			}
			// 2.20
			if ( version_compare( $this->xili_settings['version'], '2.20', '<' ) ) {
				$this->xili_settings['version'] = '2.20';
				$lang_perma_state = 'updated';
				$this->xili_settings['lang_permalink'] = $lang_perma_state; // 2.20
			}
			// 2.21
			if ( version_compare( $this->xili_settings['version'], '2.21', '<' ) ) {
				$this->xili_settings['version'] = '2.21';
				if ( empty( $this->xili_settings['show_page_on_front_array'] ) ) {
					// fixed enfold theme - thanks to Stephanie DE
					$this->xili_settings['show_page_on_front_array'] = array(); // 2.21
				}
			}
			// lastest version
			$latest_xl_version = XILILANGUAGE_VER;

			if ( version_compare( $this->xili_settings['version'], $latest_xl_version, '<' ) ) {
				if ( ! isset( $this->xili_settings['language_objects_list'] ) ) {
					$this->xili_settings['language_objects_list'] = array();
				}
				if ( ! isset( $this->xili_settings['multiple_lang'] ) ) {
					$this->xili_settings['multiple_lang'] = '';
				}
				$this->xili_settings['specific_widget'] = $this->xili_widgets; // class renaming
				$this->xili_settings['version'] = $latest_xl_version;
				$this->update_new_term_metas = true;
				set_transient( '_xl_activation_redirect', 2, 30 ); // 2.21 - 2 - 2 = updated
			}
			if ( $this->xili_settings['version'] !== $current_xl_version ) {
				update_option( 'xili_language_settings', $this->xili_settings );
			}
			// redundant !
			if ( $latest_xl_version != $this->xili_settings['version'] ) {
				// repair or restart from new
				$this->xili_settings = $this->initial_settings(); // 2.22
				update_option( 'xili_language_settings', $this->xili_settings );
				set_transient( '_xl_activation_redirect', 1, 30 ); // 2.20
			}
			xili_xl_error_log( '# ' . __LINE__ . ' ************* only_parent_construct = ' . $this->xili_settings['version'] );
		}
		// 2.20.3
		// test pll was previously installed but not deleted
		if ( ( $settings = get_option( 'polylang', false ) ) && ( empty( $this->xili_settings['pll_cleaned'] ) || 4 >= $this->xili_settings['pll_cleaned'] ) ) {
			if ( version_compare( $settings['version'], XILILANGUAGE_PLL_TESTED, '>=' ) ) {
				include_once $this->plugin_path . 'xili-includes/pll-functions.php';
			}
		}

		if ( ! defined( 'TAXONAME' ) ) {
			define( 'TAXONAME', $this->xili_settings['taxonomy'] );
		}
		if ( ! defined( 'QUETAG' ) ) {
			define( 'QUETAG', $this->xili_settings['reqtag'] ); // 'lang'
		}
		if ( ! defined( 'TAXOLANGSGROUP' ) ) {
			define( 'TAXOLANGSGROUP', $this->xili_settings['taxolangsgroup'] );
		}
		if ( ! defined( 'LANG_UNDEF' ) ) {
			define( 'LANG_UNDEF', $this->xili_settings['lang_undefined'] ); //2.2.3
		}

		/* detect research about permalinks */

		$this->show_page_on_front = ( 'page' == $this->get_option_wo_xili( 'show_on_front' ) );

		$this->is_permalink = ( '' == get_option( 'permalink_structure' ) ) ? false : true; // 2.8.4

		/* language of WP */
		if ( ! class_exists( 'GP_Locales' ) ) {
			require_once XILILANGUAGE_PLUGIN_DIR . 'xili-includes/locales.php'; // thanks hnygard 20141212
		}

		/**
		 * do_action - wp-settings.php - #337
		 */
		add_action( 'after_setup_theme', array( &$this, 'bundled_themes_support_flag' ), 12 ); // bundled themes
		// used if config.xml set an array of theme_mod values - 2.18.2
		add_action( 'after_setup_theme', array( &$this, 'theme_mod_create_filters' ), 13 ); // if array theme_mod_to_be_filtered set before
		// if permalink
		if ( 'updated' == $this->xili_settings['lang_permalink'] ) {
			add_action( 'after_setup_theme', array( &$this, 'update_lang_permalink' ), 13 );
		}

		/**
		 * do_action - wp-settings.php - #353
		 */
		add_action( 'init', array( &$this, 'init_and_register_language_post_taxonomy' ), 9 );
		add_action( 'init', array( &$this, 'init_vars' ), 9 ); //2.8.4 level 9 - these previous lines - to be before XD 130122

		add_action( 'init', array( &$this, 'init_plugin_textdomain' ), 10 ); // 2.20  changed to init
		add_action( 'init', array( &$this, 'init_theme_textdomain' ), 10 ); // 2.20  changed to 10
		add_action( 'init', array( &$this, 'init_translatable_vars' ), 10 ); // 2.20.3

		add_action( 'init', array( &$this, 'add_link_taxonomy' ), 13 ); // 1.8.5

		/* special to detect theme changing since 1.1.9 */
		add_action( 'switch_theme', array( &$this, 'theme_switched' ) );

		/**
		 * query filters
		 */
		add_filter( 'posts_where', array( &$this, 'posts_where_lang' ), 10, 2 );
		add_filter( 'posts_join', array( &$this, 'posts_join_tax_lang' ), 10, 2 ); // 2.16.4
		add_filter( 'posts_distinct', array( &$this, 'posts_distinct_lang' ), 10, 2 );

		add_filter( 'posts_search', array( &$this, 'posts_search_filter' ), 10, 2 ); //2.2.3
		add_action( 'pre_get_posts', array( &$this, 'modify_querytag' ) );
		add_action( 'pre_get_posts', array( &$this, 'multiple_lang_query' ) );
		add_filter( 'query_vars', array( &$this, 'multiple_lang_query_vars' ) );
		add_action( 'template_redirect', array( &$this, 'multiple_lang_query_redirect' ) );

		add_action( 'wp', array( &$this, 'xili_test_lang_perma' ), 1 ); // only front-end
		add_action( 'wp', array( &$this, 'xiliml_language_wp' ) ); /// since 2.2.3 - wp_loaded - wp before
		/* 'wp' = where theme's language is defined just after query */
		if ( 'wp_locale' == $this->xili_settings['wp_locale'] ) {
			add_action( 'wp', array( &$this, 'xili_locale_setup' ), 15 ); // since 2.4
		}
		//
		if ( $this->locale_method ) {
			add_filter( 'locale', array( &$this, 'xiliml_setlocale' ), 10 );
		}
		/* to be compatible with l10n cache from Johan since 1.1.9 */

		add_filter( 'widget_title', array( &$this, 'one_text' ), 9 ); /* added 0.9.8.1 - 9 to avoid quotation filter 2.8.8 */
		add_filter( 'widget_text', array( &$this, 'one_text' ) );

		add_filter( 'list_cats', array( &$this, 'xiliml_cat_language' ), 10, 2 ); /* mode 2 : content = name */
		add_filter( 'link_category', array( &$this, 'one_text' ) ); // 1.6.0 for wp_list_bookmarks (forgotten)
		//add_filter( 'category_link', array(&$this,'xiliml_link_append_lang'), 10, 2 );
		add_filter( 'term_link', array( &$this, 'xiliml_term_link_append_lang' ), 10, 3 ); // both category and post_tag - 2.13.3 ( category_link / tag_link filter are obsolete)

		add_filter( 'category_description', array( &$this, 'xiliml_link_translate_desc' ) );
		add_filter( 'single_cat_title', array( &$this, 'xiliml_single_cat_title_translate' ) ); /* 1.4.1 wp_title() */
		add_filter( 'get_the_archive_description', array( &$this, 'get_the_archive_description' ) ); // 2.17.1

		//add_filter( 'tag_link', array(&$this,'xiliml_taglink_append_lang' )); // see above - only used if lang_perma

		add_filter( 'bloginfo', array( &$this, 'xiliml_bloginfo' ), 10, 2 ); /* since 1.6.0 - description - date - time */

		/* filters for archives since 0.9.9.4 */
		add_filter( 'getarchives_join', array( &$this, 'xiliml_getarchives_join' ), 10, 2 );
		add_filter( 'getarchives_where', array( &$this, 'xiliml_getarchives_where' ), 10, 2 );
		add_filter( 'get_archives_link', array( &$this, 'xiliml_get_archives_link' ) );
		add_filter( 'widget_archives_args', array( &$this, 'xiliml_widget_archives_args' ) ); // since 2.16.3
		add_filter( 'widget_archives_dropdown_args', array( &$this, 'xiliml_widget_archives_args' ) );

		/* option modified */
		add_filter( 'option_sticky_posts', array( &$this, 'translate_sticky_posts_id' ) ); /* 1.6.1 */

		add_filter( 'option_page_on_front', array( &$this, 'translate_page_on_front_id' ) ); /* 1.7.0 */

		/* bookmarks and widget_links 1.8.5 #2500 */
		add_filter( 'widget_links_args', array( &$this, 'widget_links_args_and_lang' ), 10, 1 ); // in class WP_Widget_Links (default-widgets.php)
		add_filter( 'get_bookmarks', array( &$this, 'the_get_bookmarks_lang' ), 10, 2 ); // only active if 'lang' in wp_list_bookmarks()

		add_action( 'wp_head', array( &$this, 'head_insert_language_metas' ), 11 ); // © and options present in functions.php

		add_filter( 'plugin_locale', array( &$this, 'get_plugin_domain_array' ), 10, 2 );

		if ( ! is_admin() ) {

			add_filter( 'option_page_for_posts', array( &$this, 'translate_page_for_posts_id' ) ); /* 2.8.4 */

			add_filter( 'the_category', array( &$this, 'xl_get_the_category_list' ), 10, 2 ); /* 1.7.0 */

			add_filter( 'gettext', array( &$this, 'change_plugin_domain' ), 10, 3 ); /* 1.8.7 */
			add_filter( 'gettext_with_context', array( &$this, 'change_plugin_domain_with_context' ), 10, 4 ); /* 1.8.8 */
			add_filter( 'ngettext', array( &$this, 'change_plugin_domain_plural' ), 10, 5 );
			add_filter( 'ngettext_with_context', array( &$this, 'change_plugin_domain_plural_with_context' ), 10, 6 );

			add_action( 'xiliml_add_frontend_mofiles', array( &$this, 'load_plugin_domain_for_curlang' ), 10, 2 );

			// for wp nav menu
			add_filter( 'the_title', array( &$this, 'wp_nav_title_text' ), 9, 2 ); // 9 to be before RIGHT SINGLE QUOTATION MARK filter 2.8.8 - thanks to PouletFou

			if ( '' != $this->xili_settings['in_nav_menu'] ) {
				add_filter( 'wp_nav_menu_items', 'xili_nav_lang_list', 10, 2 );
			}

			add_filter( 'wp_nav_menu_objects', array( &$this, 'insert_language_objects_in_nav_menu' ), 10, 2 ); //wp-includes/nav-menu-template.php

			if ( '' != $this->xili_settings['page_in_nav_menu_array'] ) {
				// new 2.8.4.3 to avoid if ''
				add_filter( 'wp_nav_menu_items', 'xili_nav_page_list', 9, 2 ); // before lang's links - 1.7.1
			}
			if ( '' != $this->xili_settings['home_item_nav_menu'] ) {
				add_filter( 'walker_nav_menu_start_el', 'xili_nav_page_home_item', 10, 4 ); // add lang if - 1.8.9.2
			}
			if ( version_compare( $wp_version, '3.4.9', '>' ) ) {
				// new behaviour - add dummy if lang nav or page nav (
				if ( '' != $this->xili_settings['in_nav_menu'] || '' != $this->xili_settings['page_in_nav_menu_array'] ) {
					add_filter( 'wp_nav_menu_args', 'xili_nav_menu_args', 10, 1 ); // filter in nav-menu-template.php 2.8.3
				}
			}

			add_filter( 'language_attributes', array( &$this, 'head_language_attributes' ) );
			add_action( 'wp_head', array( &$this, 'head_insert_hreflang_link' ), 10 ); // since 2.5

			add_filter( 'option_date_format', array( &$this, 'translate_date_format' ) ); /* 1.7.0 */

			add_filter( 'category_feed_link', array( &$this, 'category_feed_link' ) ); // 2.8.1
		}

		// since 1.5.5
		add_filter( 'comment_form_default_fields', array( &$this, 'xili_comment_form_default_fields' ) );
		add_filter( 'comment_form_defaults', array( &$this, 'xili_comment_form_defaults' ) );

		// since 2.4.0 for rtl.css
		add_filter( 'locale_stylesheet_uri', array( &$this, 'change_locale_stylesheet_uri' ), 10, 2 );

		// since 1.8.8 - activate xl widget series
		if ( 'enable' == $this->xili_settings['widget'] ) {
			add_action( 'widgets_init', array( &$this, 'add_new_widgets' ) );
		}

		add_filter( 'widget_display_callback', array( &$this, 'widget_display_callback' ), 10, 2 ); // 2.20.3

		/* new actions for xili-language theme's templates tags */

		add_action( 'xili_language_list', array( &$this, 'xili_language_list' ), 10, 5 ); /* add third param 0.9.7.4 - 4th 1.6.0*/
		add_action( 'xili_post_language', array( &$this, 'xili_post_language' ), 10, 2 );

		add_action( 'xiliml_the_other_posts', array( &$this, 'the_other_posts' ), 10, 4 ); /* add a param 1.1 */
		add_action( 'xiliml_the_category', array( &$this, 'the_category' ), 10, 3 );
		add_filter( 'xiliml_langinsearchform', array( &$this, 'xiliml_langinsearchform' ), 10, 3 ); // 1.8.2 action to filter

		// verify theme and set ltd for both parent and admin child
		add_filter( 'override_load_textdomain', array( &$this, 'xiliml_override_load' ), 10, 3 ); // since 1.5.0
		add_filter( 'theme_locale', array( &$this, 'xiliml_theme_locale' ), 10, 2 );  // two times if is_admin()

		// propagation when creation
		add_action( 'xl_propagate_post_attributes', array( &$this, 'propagate_categories' ), 10, 2 ); // 2.8.8

		// to translate inside content according current post language - 2.12.0
		add_shortcode( 'xili18n', array( &$this, 'xili18n_shortcode' ) );
		// to display part of content according current language - 2.13.3
		add_shortcode( 'xili-show-if', array( &$this, 'xili_content_if_shortcode' ) );
		// insert link to linked post in other language - 2.18.2
		add_shortcode( 'linked-post-in', array( &$this, 'build_linked_posts_shortcode' ) );

		// to return URI of flag assigned to a language
		add_shortcode( 'xili-flag', array( &$this, 'xili_multilingual_flag' ) );
		// since 2.22
		add_shortcode( 'xili-multiple-lang-selector', array( &$this, 'multiple_lang_selector' ) );

		add_action( 'wp_head', array( &$this, 'insert_xili_flag_css_in_header' ), 12 ); // 2.15 after bundled old version

		add_action( 'xili_language_widgets_head', array( &$this, 'xili_language_widgets_head_style' ) ); // 2.20.3 - global css
	}

	/**
	 * first activation or empty settings
	 */
	private function initial_settings() {
		return array(
			'taxonomy'                 => 'language',
			'version'                  => XILILANGUAGE_VER,
			'reqtag'                   => 'lang', // query_var
			'browseroption'            => '',
			'authorbrowseroption'      => '',
			'taxolangsgroup'           => 'languages_group',
			'functions_enable'         => '',
			'langs_folder'             => '',
			'theme_domain'             => '',
			'homelang'                 => '',
			'langs_list_status'        => '',
			'in_nav_menu'              => '',
			'page_in_nav_menu'         => '',
			'page_in_nav_menu_array'   => '',
			'args_page_in_nav_menu'    => '',
			'multilingual_custom_post' => array(),
			'langs_in_root_theme'      => '',
			'domains'                  => array(
				'default' => 'disable',
				'bbpress' => 'disable',
			), // no default domain to theme domain 1.8.7 - no all 2.8.4.3
			'widget'                   => 'enable',
			'delete_settings'          => '', //1.8.8 for uninstall
			'allcategories_lang'       => 'browser', // 1.8.9.1
			'lang_features'            => array(),
			'home_item_nav_menu'       => '', // 1.8.9.2
			'lang_undefined'           => $this->undefchar, //2.2.3
			'lang_neither_browser'     => '', // 2.3.1
			'wp_locale'                => 'wp_locale', // 2.7.1 new mode as default - 2.4.0 = old mode based on db strftime
			'available_langs'          => array(),
			'creation_redirect'        => 'redirect', // 2.6 to redirect to new post after creation
			'external_xl_style'        => 'on', // activate external xl-style.css - on by default :2.6.3
			'nav_menu_separator'       => '|', // 2.8.3
			'pforp_select'             => 'select', // 2.8.4
			'shortqv_slug_array'       => array(), // special perma
			'list_pages_check_option'  => 'fixe', // 2.8.5
			'list_link_title'          => array(
				'post_selected' => 'Posts selected in %s',
				'current_post'   => 'Current post in %s',
				'latest_posts'   => 'Latest posts in %s', // used in xili-language list (and XD)
				'view_all_posts' => 'View all posts in %s',
			), // used in the_category
			'mo_parent_child_merging'  => 'parent-priority',  // 2.21.1 to parent-priority
			'parent_langs_folder'      => '',
			'enable_fc_theme_class'    => 'enable', // 2.11.1 - priority to theme Featured Content Class and not jetpack
			'theme_alias_cache'        => array(),
			'langs_group_id'           => 0,
			'langs_group_tt_id'        => 0, // 2.15.1
			'languages_list'           => array(), // 2.15.2
			'specific_widget'          => $this->xili_widgets, // 2.16.4
			'lang_permalink'           => '', // 2.20
			'widget_visibility'        => '',
			'show_page_on_front_array' => array(), // 2.20.3 - thanks to Stephanie DE
			'link_categories_settings' => array(), // avoid notice
			'language_objects_list'    => array(), // 2.22
			'multiple_lang'            => '',
		);
	}

	// updated only 2.20 - after_theme_setup
	public function update_lang_permalink() {
		$lang_perma_state = '';
		if ( function_exists( 'get_theme_xili_options' ) ) { // in theme-multilingual-classes.php ( required 201x-xili functions.php )
			$xili_theme_options = get_theme_xili_options();
			if ( isset( $xili_theme_options['perma_ok'] ) ) {
				if ( $xili_theme_options['perma_ok'] ) {
					$lang_perma_state = 'perma_ok';
				}
			}
		}
		$this->xili_settings['lang_permalink'] = $lang_perma_state; // 2.20
		update_option( 'xili_language_settings', $this->xili_settings );
	}

	/* first activation of plugin */
	private function xili_language_activate() {
		$this->xili_settings = get_option( 'xili_language_settings', false );
		if ( false === $this->xili_settings ) {
			$this->xili_settings = $this->initial_settings();
			update_option( 'xili_language_settings', $this->xili_settings );
		}

	}

	// when admin_init (to be refreshed after temporary desactivated)
	public function init_roles() {

		global $wp_roles;

		$wp_roles->add_cap( 'administrator', 'xili_language_set' );
		$wp_roles->add_cap( 'administrator', 'xili_language_menu' );
		$wp_roles->add_cap( 'administrator', 'xili_language_clone_tax' );

		$wp_roles->add_cap( 'editor', 'xili_language_menu' );
		$wp_roles->add_cap( 'editor', 'xili_language_clone_tax' ); // able to clone taxonomy ('category') during propagate or create post

	}

	// when desactivating - 2.8.8
	public function remove_capabilities() {

		global $wp_roles;

		$wp_roles->remove_cap( 'administrator', 'xili_language_set' );
		$wp_roles->remove_cap( 'administrator', 'xili_language_menu' );
		$wp_roles->remove_cap( 'administrator', 'xili_language_clone_tax' );

		$wp_roles->remove_cap( 'editor', 'xili_language_menu' );
		$wp_roles->remove_cap( 'editor', 'xili_language_clone_tax' );

	}

	// simplified
	public function get_wplang() {
		return get_option( 'WPLANG', '' );
	}

	// used by customize preview features - 2.8.7
	public function get_xili_language_options() {
		return get_option( 'xili_language_settings', $this->initial_settings() );
	}

	public function add_action( $action, $function = '', $priority = 10, $accepted_args = 1 ) {
		add_action( $action, array( &$this, '' == $function ? $action : $function ), $priority, $accepted_args );
		$this->idx[ $action ] = _wp_filter_build_unique_id( $action, array( &$this, '' == $function ? $action : $function ), $priority ); /* unique id of this filter from object */
	}

	public function add_filter( $filter, $function = '', $priority = 10, $accepted_args = 1 ) {
		add_filter( $filter, array( &$this, '' == $function ? $filter : $function ), $priority, $accepted_args );
		$this->idx[ $filter ] = _wp_filter_build_unique_id( $filter, array( &$this, '' == $function ? $filter : $function ), $priority ); /* unique id of this filter from object fixed 1.0.1 */
	}

	/**
	 * More than one filter for the function.
	 *
	 * @since 0.9.7
	 * @update renamed from this_has_filter
	 * @param $the_function (string).
	 * @return true if more than one.
	 */
	public function this_has_external_filter( $the_function ) {
		global $wp_filter;
		if ( ! isset( $wp_filter[ $the_function ] ) ) {
			return false; // avoid php warning 2.3.0
		}
		$has = $wp_filter[ $the_function ];

		if ( ! is_array( $has ) ) {
			return false; // avoid php warning 2.1.0
		}
		$keys = array_keys( $has );

		if ( count( $has[ $keys[0] ] ) >= 2 ) { /*one from class others from functions.php or elsewhere*/
			return true;
		} else {
			return false;
		}
	}

	/**
	 * for wpmu
	 * register functions must be called by init
	 * filter action init
	 * @since 1.5.1
	 *
	 */
	public function init_and_register_language_post_taxonomy() {

		$post_type_array = array_keys( $this->authorized_custom_post_type( true ) ); // to be fully registered for xml export // 2.12.1 - 2.13.2 b only fully authorized

		if ( class_exists( 'xili_dictionary' ) ) {
			$post_type_array[] = 'xdmsg'; // XD active
		}

		if ( 'wp_locale' == $this->xili_settings['wp_locale'] ) {
			xiliml_declare_xl_wp_locale();
		}

		add_filter( 'query_vars', array( &$this, 'keywords_addqueryvar' ) ); // now in taxonomy decl. // 2.1.1

		register_taxonomy(
			TAXONAME,
			$post_type_array,
			array(
				'labels' => array(
					'name' => __( 'Languages', 'xili-language' ),
					'singular_name' => __( 'Language', 'xili-language' ),
				),
				'hierarchical' => false,
				//'query_var' => QUETAG, // 2.22.9 - must be set via filter 'query_vars' with permalinks mode
				'label' => false,
				'rewrite' => false,
				'update_count_callback' => array( &$this, '_update_post_lang_count' ),
				'show_ui' => false,
				'_builtin' => false,
				'show_in_nav_menus' => false,
				'show_in_rest' => true, // 2.19.2 - tested with REST API beta3
			)
		);

		register_taxonomy(
			TAXOLANGSGROUP,
			'term',
			array(
				'hierarchical' => false,
				'update_count_callback' => '',
				'show_ui' => false,
				'label' => false,
				'rewrite' => false,
				'_builtin' => false,
				'show_in_nav_menus' => false,
			)
		);

		$this->authorized_taxonomies = $this->authorized_custom_taxonomies( $post_type_array );
		// 2.22
		// update metas
		if ( $this->update_new_term_metas ) {
			foreach ( $this->xili_settings['languages_list'] as $one_language ) {

				$lang_test = Xili_Language_Term::upgrade_instance( $one_language->term_id );

			}
			$this->xili_settings['meta_update'] = true;
		} else {

			$list_language_objects = $this->get_list_language_objects( true ); // TO DO force : $true
		}
	}

	/**
	 * enable the new query tag associated with new taxonomy
	 */
	public function keywords_addqueryvar( $vars ) {
		if ( ! in_array( QUETAG, $vars ) ) {
			$vars[] = QUETAG;
		}
		return $vars;
	}

	/**
	 * since 2.6
	 *
	 */
	public function init_vars() {

		/* default values */
		$wplang = $this->get_wplang();
		if ( '' != $wplang && ( in_array( strlen( $wplang ), array( 2, 3, 5, 6 ) ) ) ) {
			$this->default_lang = $wplang;
		} else {
			$this->default_lang = 'en_US';
		}
		// cache_domain added to avoid annoying caches 2.8.4
		$cache_suffix = ( $this->class_admin ) ? '_ad' : '';
		$thegroup = get_terms(
			TAXOLANGSGROUP,
			array(
				'hide_empty' => false,
				'slug' => 'the-langs-group',
				'cache_domain' => 'core1' . $cache_suffix,
			)
		);

		if ( empty( $thegroup ) || empty( $thegroup[0]->count ) ) {
			/* update langs group 0.9.8 and if from start 2.3.1 */
			$args = array(
				'alias_of' => '',
				'description' => 'the group of languages',
				'parent' => 0,
				'slug' => 'the-langs-group',
			);
			wp_insert_term( 'the-langs-group', TAXOLANGSGROUP, $args ); /* create and link to existing langs */
			$listlanguages = get_terms(
				TAXONAME,
				array(
					'hide_empty' => false,
					'get' => 'all',
					'cache_domain' => 'core1' . $cache_suffix,
				)
			);

			if ( array() == $listlanguages ) {
				/*create two default lines with the default language (as in config)*/
				$listlanguages = $this->create_default_languages_list( $cache_suffix );
			}
			foreach ( $listlanguages as $language ) {
				wp_set_object_terms( $language->term_id, 'the-langs-group', TAXOLANGSGROUP );
			}
			$thegroup = get_terms(
				TAXOLANGSGROUP,
				array(
					'hide_empty' => false,
					'slug' => 'the-langs-group',
					'get' => 'all',
					'cache_domain' => 'core2' . $cache_suffix,
				)
			);
		} else {

			$cleaned = apply_filters( 'clean_previous_languages_list', false ); // 2.20.3
			if ( ! $cleaned ) {
				// if created by XD - update
				$listlanguages = get_terms(
					TAXONAME,
					array(
						'hide_empty' => false,
						'get' => 'all',
						'cache_domain' => 'core2' . $cache_suffix,
					)
				);
				$i = 0;
				foreach ( $listlanguages as $language ) {
					if ( ! isset( $this->xili_settings['lang_features'][ $language->slug ] ) ) {
						$this->xili_settings['lang_features'][ $language->slug ] = array(
							'charset' => '',
							'hidden' => '',
						);
						$i++;
					}
				}
				if ( $i > 0 ) {
					update_option( 'xili_language_settings', $this->xili_settings );
				}
			}
		}

		$this->langs_group_id = $thegroup[0]->term_id;
		$this->langs_group_tt_id = $thegroup[0]->term_taxonomy_id;
		if ( $this->langs_group_id > 0 ) {
			$this->xili_settings['langs_group_id'] = $this->langs_group_id;
			$this->xili_settings['langs_group_tt_id'] = $this->langs_group_tt_id;
			update_option( 'xili_language_settings', $this->xili_settings ); // 2.15.1
		}

		if ( is_child_theme() ) { // 1.8.1 - 1.8.5
			if ( 'root' == $this->xili_settings['langs_in_root_theme'] ) {
				$this->get_template_directory = get_template_directory();
			} else {
				$this->get_template_directory = get_stylesheet_directory();
			}
			$this->get_parent_theme_directory = get_template_directory();

		} else {
			$this->get_template_directory = get_template_directory();
			$this->get_parent_theme_directory = '';
		}

		$this->get_lang_slug_ids(); // default array of languages slug=>id and slug=>name // 2.4.2

		if ( $this->show_page_on_front ) {
			$this->get_show_page_on_front_array();
		}
		/* here because taxonomy is registered : since 1.5.3 */

		$this->default_slug = $this->get_default_slug(); /*no constant for wpmu */
		if ( ! is_multisite() && ! defined( 'DEFAULTSLUG' ) ) {
			define( 'DEFAULTSLUG', $this->default_slug ); /* for backward compatibility */
		}

		// if ( $dir = get_bloginfo('text_direction') ) /* if present in blog options @since 0.9.9 */
		if ( is_rtl() ) {
			$this->default_dir = 'rtl'; // $dir; 2.16.6
		}

		// 1.8.4
		if ( array() == $this->xili_settings['available_langs'] ) {

			$this->xili_settings['available_langs'] = $this->get_lang_ids();
			update_option( 'xili_language_settings', $this->xili_settings );
		}
		/* multiple language option - 2.22 */
		$this->multiple_lang = ( isset( $this->xili_settings['multiple_lang'] ) && 'multiple_lang' == $this->xili_settings['multiple_lang'] ) ? true : false;
	}

	public function create_default_languages_list( $cache_suffix ) {

		$term = 'en_US';
		$locale_1 = GP_Locales::by_field( 'wp_locale', $term );
		$desc = ( $locale_1 ) ? $locale_1->english_name : 'english';
		$args = array(
			'alias_of' => '',
			'description' => $desc,
			'parent' => 0,
			'slug' => 'en_us',
		);

		$term_data = $this->safe_lang_term_creation( $term, $args );
		if ( ! is_wp_error( $term_data ) ) {
			wp_set_object_terms( $term_data['term_id'], 'the-langs-group', TAXOLANGSGROUP );
			//$this->xili_settings['lang_features']['en_us'] = array('charset'=>"",'hidden'=>"");
			$lang_object = xili_language_term::complete_instance( $term_data['term_id'] );
		} else {
			$inserted = $this->safe_insert_in_language_group( $term_data, 0 );
		}

		$term = $this->default_lang;

		$locale_2 = GP_Locales::by_field( 'wp_locale', $term );

		$desc = ( $locale_2 ) ? $locale_2->english_name : $this->default_lang;
		$desc_array = explode( ' (', $desc );
		$desc = $desc_array[0];
		$slug = strtolower( $this->default_lang ); // 2.3.1

		$wp_lang = $this->get_wplang();
		if ( '' == $wp_lang || 'en_US' == $this->default_lang || '' == $this->default_lang ) {
			$term = 'fr_FR';
			$locale_f = GP_Locales::by_field( 'wp_locale', $term );
			$desc = ( $locale_f ) ? $locale_f->english_name : 'french';
			$desc_array = explode( ' (', $desc );
			$desc = $desc_array[0];
			$slug = 'fr_fr';
		}
		$args = array(
			'alias_of' => '',
			'description' => $desc,
			'parent' => 0,
			'slug' => $slug,
		);

		$term_data = $this->safe_lang_term_creation( $term, $args );
		if ( ! is_wp_error( $term_data ) ) {
			wp_set_object_terms( $term_data['term_id'], 'the-langs-group', TAXOLANGSGROUP );
			//$this->xili_settings['lang_features'][$slug] = array('charset'=>"",'hidden'=>"");
			$lang_object = xili_language_term::complete_instance( $term_data['term_id'] );
		} else {
			$inserted = $this->safe_insert_in_language_group( $term_data, 0 );
		}

		update_option( 'xili_language_settings', $this->xili_settings );

		return get_terms(
			TAXONAME,
			array(
				'hide_empty' => false,
				'get' => 'all',
				'cache_domain' => 'core2' . $cache_suffix,
			)
		);
	}

	/**
	 * init localisable vars
	 * fired after load_plugin_text_domain
	 *
	 * @since 2.20.3
	 *
	 */
	public function init_translatable_vars() {
		// type of languages list see options in xili_language_list or navmenu - third param = title for option
		$this->langs_list_options = array(
			array( '', __( 'Nav to home', 'xili-language' ), esc_attr__( 'Links redirect to home.', 'xili-language' ) ),
			array( 'typeone', __( 'Nav to home (w/o cur lang.)', 'xili-language' ), esc_attr__( 'Current language is not inserted and links redirect to home.', 'xili-language' ) ),
			array( 'typeonenew', __( 'Nav to Singular (w/o cur lang.)', 'xili-language' ), esc_attr__( 'Current language is not inserted and links redirect to post or page if exists in other languages.', 'xili-language' ) ),
			array( 'navmenu', __( 'Nav Menu', 'xili-language' ), esc_attr__( 'List of all languages are inserted and links redirect to home.', 'xili-language' ) ),
			array( 'navmenu-a', __( 'Nav Menu (w/o current lang.)', 'xili-language' ), esc_attr__( 'Current language is not inserted and links redirect to home.', 'xili-language' ) ),
			array( 'navmenu-1', __( 'Nav Menu Singular', 'xili-language' ), esc_attr__( 'List of all languages are inserted and links redirect to post or page if exists in other languages.', 'xili-language' ) ),
			array( 'navmenu-1a', __( 'Nav Menu Singular (w/o current lang.)', 'xili-language' ), esc_attr__( 'Current language is not inserted and links redirect to post or page if exists in other languages.', 'xili-language' ) ),
			array( 'navmenu-1ao', __( 'Nav Menu Singular (w/o curr. lang and if exists)', 'xili-language' ), esc_attr__( 'Current language is not inserted and links appears and redirect to post or page if exists in other languages.', 'xili-language' ) ),
		);
	}
	/**
	 * Safe language term creation
	 *
	 * @since 2.4.1
	 */
	public function safe_lang_term_creation( $term, $args ) {
		global $wpdb;
		// test if exists with other slug or name
		if ( $term_id = term_exists( $term ) ) {
			// return id (no taxonomy)
			$existing_term = $wpdb->get_row( $wpdb->prepare( "SELECT name, slug FROM $wpdb->terms WHERE term_id = %d", $term_id ), ARRAY_A );
			if ( $existing_term['slug'] != $args['slug'] ) {
				// same name but not same slug
				$res = wp_insert_term( $term . 'xl', TAXONAME, $args ); // temp insert with temp other name but same slug
				$args['name'] = $term;
				$res = wp_update_term( $res['term_id'], TAXONAME, $args ); // to recover same name
			} else {
				return new WP_Error( 'term_exists', __( 'A term with the name provided already exists.' ), $term_id );
			}
		} else {
			$res = wp_insert_term( $term, TAXONAME, $args );
		}
		if ( is_wp_error( $res ) ) {
			return $res;
		} else {
			$theids = $res;
		}
		return $theids;
	}

	/**
	 * Safe language term insertion in group following error due to existing term
	 *
	 * @since 2.12.2
	 */
	public function safe_insert_in_language_group( $term_data, $language_order = 0 ) {

		if ( $id_exists = $term_data->error_data['term_exists'] ) {
			// id of existing term
			$term_id = term_exists( $id_exists, TAXOLANGSGROUP );
			if ( is_array( $term_id ) ) { // yet in group because array
					return false;
			} else {
				wp_set_object_terms( $id_exists, 'the-langs-group', TAXOLANGSGROUP );
				if ( $language_order ) {
					update_term_order( $id_exists, $this->langs_group_tt_id, $language_order );
				}
				return true;
			}
		}
		return false;
	}

	/**
	 * Get list language Objects - designed and used to avoid query by using settings
	 *
	 * @since 1.6.0
	 * @param $force to avoid buffer
	 * @return array of objects
	 */
	public function get_listlanguages( $force = false ) {
		if ( true === $force || in_array( $this->xili_settings['langs_list_status'], array( 'added', 'edited', 'deleted' ) ) ) {
			if ( $this->xili_settings['langs_group_id'] > 0 ) { // 2.15.1
				$listlanguages = get_terms_of_groups_lite( $this->xili_settings['langs_group_id'], TAXOLANGSGROUP, TAXONAME, 'ASC' );
				if ( $listlanguages ) {
					$prev_listlanguages = $this->xili_settings['languages_list'];
					$this->xili_settings['languages_list'] = $listlanguages;
					$do = ( 'set' != $this->xili_settings['langs_list_status'] ) ? true : $prev_listlanguages != $listlanguages; // 2.15.1
					$this->xili_settings['langs_list_status'] = 'set';
					if ( is_admin() && $do && false !== $this->class_admin ) {
						update_option( 'xili_language_settings', $this->xili_settings );
					}
				}
				return $listlanguages;
			} else {
				return array();
			}
		} else {
			return $this->xili_settings['languages_list'];
		}
	}

	/**
	 * Get list of language objects
	 *
	 * @since  2.22 [<description>]
	 *
	 * @return array of objects indexed with slug
	 */
	public function get_list_language_objects( $force = false ) {

		if ( ! isset( $this->xili_settings['language_objects_list'] ) || array() == $this->xili_settings['language_objects_list'] ) {
			$force = true;
		}

		if ( true === $force || in_array( $this->xili_settings['langs_list_status'], array( 'added', 'edited', 'deleted' ) ) ) {
			if ( $this->xili_settings['langs_group_id'] > 0 ) { // 2.15.1
				$listlanguages = get_terms_of_groups_lite( $this->xili_settings['langs_group_id'], TAXOLANGSGROUP, TAXONAME, 'ASC' );
				if ( $listlanguages ) {
					$language_objects = array();
					foreach ( $listlanguages as $language ) {
						$language_term = xili_language_term::get_instance( $language->term_id );
						if ( $language_term && ! is_wp_error( $language_term ) ) {
							$one_language = $language_term->language_data; // metas in object
							$language_objects[ $language->slug ] = $one_language; // indexed by slug
						}
					}
					$prev_listlanguages = $this->xili_settings['language_objects_list'];
					$this->xili_settings['language_objects_list'] = $language_objects;
					$do = ( 'set' != $this->xili_settings['langs_list_status'] ) ? true : $prev_listlanguages != $language_objects;
					$this->xili_settings['langs_list_status'] = 'set';
					if ( is_admin() && $do && false !== $this->class_admin ) {
						update_option( 'xili_language_settings', $this->xili_settings );
					}
					return $language_objects;
				}
			} else {
				return array();
			}
		} else {
			return $this->xili_settings['language_objects_list'];
		}
	}

	/**
	 * Get list language IDs
	 *
	 * @since 1.8.4
	 */
	public function get_lang_ids() {

		$lang_ids = array();
		$listlanguages = get_terms_of_groups_lite( $this->langs_group_id, TAXOLANGSGROUP, TAXONAME, 'ASC' );
		foreach ( $listlanguages as $lang ) {
			$lang_ids[] = $lang->term_id;
		}
		return $lang_ids;
	}

	/**
	 * Get list languages slug_IDs
	 *
	 * @since 2.1.1
	 * @since 2.22 with new object
	 */
	public function get_lang_slug_ids( $flush = 'null' ) {
		$this->alias_mode = ( has_filter( 'alias_rule', 'xili_language_trans_slug_qv' ) ) ? true : false;

		$lang_slugs = array();
		$lang_names = array();
		$lang_full_names = array();
		$langs_shortqv_slug = array();
		$langs_slug_shortqv = array();

		$do = false;
		$listlanguages = $this->get_list_language_objects( true ); // 2.22
		foreach ( $listlanguages as $key => $lang_term ) {
			//$key = $lang_term->slug;
			$lang_slugs[ $key ] = $lang_term->term_id;
			$lang_names[ $key ] = $lang_term->iso_name;
			$lang_full_names[ $key ] = $lang_term->english_name;
			// new approach
			$short = ( '' != $lang_term->alias ) ? $lang_term->alias : $key;
			$langs_slug_shortqv[ $key ] = $short;
			$langs_shortqv_slug[ $short ] = $key;

			/*
			if ( $this->alias_mode ) {
				$short = ( isset ( $this->xili_settings['lang_features'][$key]['alias'] ) ) ? $this->xili_settings['lang_features'][$key]['alias'] : $key ;
				$langs_slug_shortqv[$key] = $short;

				if ( '' != $short ) {
					$langs_shortqv_slug[$short] = $key;
				}

			}
			*/
		}

		$this->langs_ids_array = $lang_slugs;
		$this->langs_slug_name_array = $lang_names;
		$this->langs_slug_fullname_array = $lang_full_names;

		$this->xili_settings['langs_ids_array'] = $lang_slugs;

		if ( $this->alias_mode ) {
			// 2.8.2
			$this->langs_slug_shortqv_array = $langs_slug_shortqv;
			$this->langs_shortqv_slug_array = $langs_shortqv_slug;
			$do = ( $this->xili_settings['shortqv_slug_array'] != $langs_shortqv_slug ) ? true : false;
			$this->xili_settings['shortqv_slug_array'] = $langs_shortqv_slug;  // used by permalinks

		} else {
			$this->langs_slug_shortqv_array = array();
			$this->langs_shortqv_slug_array = array();
		}

		if ( is_admin() && $do && ! $this->class_admin ) {
			update_option( 'xili_language_settings', $this->xili_settings );
		}
		// 2.9.21 - 22
		if ( 'edited' == $flush && $this->is_permalink ) {
			flush_rewrite_rules( false ); // false = no .htaccess
		}
	}

	/**
	 * lang query var translator
	 *
	 * 2.8.2
	 *
	 */
	public function lang_qv_slug_trans( $lang_qv ) {
		if ( count( $langs = explode( ',', $lang_qv ) ) > 1 ) {
			$lang_qv = $langs[0]; // if multilanguage query: keep first 2.8.8
		}

		$lang_qv = wp_kses( $lang_qv, array() ); // fixes security xss - 2.8.6
		$lang_qv = preg_replace( '/[^a-z0-9_\-]/', '', $lang_qv );
		if ( isset( $this->langs_shortqv_slug_array[ $lang_qv ] ) ) {
			return $this->langs_shortqv_slug_array[ $lang_qv ];
		} else {
			return $lang_qv;
		}
	}

	/**
	 * lang slug translator
	 *
	 * 2.8.2
	 *
	 */
	public function lang_slug_qv_trans( $lang_slug ) {
		return apply_filters( 'alias_rule', $lang_slug );
	}

	/**
	 * recover alias settings of previous used theme if exists
	 *
	 * 2.11.1
	 *
	 */
	public function recover_alias_settings_of_previous_used_theme( $theme ) {
		if ( isset( $this->xili_settings['theme_alias_cache'][ $theme ] ) ) {
			foreach ( $this->xili_settings['theme_alias_cache'][ $theme ] as $slug => $value ) {
				$this->xili_settings['lang_features'][ $slug ]['alias'] = $value;
			}
		}
	}

	/**
	 * get show pages on front
	 *
	 * @since 2.1.1
	 *
	 *
	 * update $this->show_page_on_front_array array
	 */
	public function get_show_page_on_front_array() {

		$front_pages_array = array();
		$front_pages_for_posts_array = array();
		$front_pages_for_posts_name_array = array(); // 2.8.4.2

		$languages = $this->get_listlanguages( is_admin() );
		$front_page_id = $this->get_option_wo_xili( 'page_on_front' );
		$this->page_for_posts_original = true;
		$front_page_for_posts_id = get_option( 'page_for_posts' );
		$this->page_for_posts_original = false;
		foreach ( $languages as $lang ) {
			$key = $lang->slug;
			$id = get_post_meta( $front_page_id, QUETAG . '-' . $key, true );
			$page_id = ( '' != $id ) ? $id : $front_page_id;
			$front_pages_array[ $key ] = $page_id;
			// page_for_posts
			$id = get_post_meta( $front_page_for_posts_id, QUETAG . '-' . $key, true );
			$page_id = ( '' != $id ) ? $id : 0;
			if ( $page_id > 0 ) {
				$front_pages_for_posts_array[ $key ] = $page_id;
			}
			if ( $this->is_permalink && $page_id > 0 ) {

				$pagecontent = get_page( $page_id );
				$front_pages_for_posts_name_array[ $key ] = $pagecontent->post_name;
				$this->page_for_posts_name_to_id_array[ $pagecontent->post_name ] = $page_id;
			}
		}

		$this->show_page_on_front_array = $front_pages_array;
		$this->page_for_posts_array = $front_pages_for_posts_array;
		if ( $this->is_permalink ) {
			$this->page_for_posts_name_array = $front_pages_for_posts_name_array;
		}

		// debug temp
		$do = ( ! isset( $this->xili_settings['show_page_on_front_array'] ) || $this->xili_settings['show_page_on_front_array'] != $front_pages_array ) ? true : false;

		$this->xili_settings['show_page_on_front_array'] = $front_pages_array;

		if ( is_admin() && $do ) {
			update_option( 'xili_language_settings', $this->xili_settings );
		}
	}

	/**
	 * @since 0.9
	 * @since 2.22 now detect first language
	 *
	 */
	public function get_post_language( $post_id, $result = 'slug' ) {
		$ress = wp_get_object_terms( $post_id, TAXONAME );
		$postlang = '';

		if ( ! is_wp_error( $ress ) && isset( $ress[0] ) ) { // 2.18.2 (error if membership plugin )
			if ( 1 == count( $ress ) ) {
				$obj_term = $ress[0]; // today only one language per post
			} else { // since 2.22 - multiple lang

				if ( metadata_exists( 'post', $post_id, '_multiple_lang' ) && $lang_array = get_post_meta( $post_id, '_multiple_lang', true ) ) {
					$primary_slug = $lang_array[0];
					$obj_term = '';
					foreach ( $ress as $one_term ) {
						if ( $one_term->slug == $primary_slug ) {
							$obj_term = $one_term;
							break 1; // toward switch
						}
					}
					if ( ! $obj_term ) {
						return ''; // error in loop
					}
				} else {
					return ''; // undefined
				}
			}
			switch ( $result ) {
				case 'iso':
					$postlang = ( '' != $obj_term->name ) ? $obj_term->name : '';
					break;
				case 'name':
					$postlang = ( '' != $obj_term->description ) ? $obj_term->description : '';
					break;
				default: // slug
					$postlang = ( '' != $obj_term->slug ) ? $obj_term->slug : '';
			}
		} else {
			$postlang = '';
		}
		return $postlang;
	}

	/**
	 * Create a linked copy of current post in target language
	 *
	 * @since 2.5
	 *
	 */
	public function create_initial_translation( $target_lang, $from_post_title = '', $frompostlang = '', $from_post_id ) {
		global $user_ID;
		/* translators: */
		$post_title_prefix = sprintf( __( 'Please translate in %s:', 'xili-language' ), $target_lang );
		$target_post_title = ( '' == $from_post_title ) ? $post_title_prefix . ' ' . $from_post_id : $post_title_prefix . ' ' . $from_post_title;

		$post_type = get_post_type( $from_post_id );

		$params = array(
			'post_status' => 'draft',
			'post_type' => $post_type,
			'post_author' => $user_ID,
			'ping_status' => get_option( 'default_ping_status' ),
			'post_parent' => 0,
			'menu_order' => 0,
			'to_ping' => '',
			'pinged' => '',
			'post_password' => '',
			'guid' => '',
			'post_content_filtered' => '',
			'post_excerpt' => '',
			'import_id' => 0,
			'post_content' => $target_post_title,
			'post_title' => $target_post_title,
		);

		$post_id = wp_insert_post( $params );

		if ( 0 != $post_id ) {
			// taxonomy
			wp_set_object_terms( $post_id, $target_lang, TAXONAME );
			// metas
			// from
			update_post_meta( $from_post_id, QUETAG . '-' . $target_lang, $post_id );

			// this with other target of from
			$listlanguages = $this->get_listlanguages();
			foreach ( $listlanguages as $language ) {
				if ( $language->slug != $target_lang && $language->slug != $frompostlang ) {
					$id = get_post_meta( $from_post_id, QUETAG . '-' . $language->slug, true );
					if ( '' != $id ) {
						update_post_meta( $post_id, QUETAG . '-' . $language->slug, $id );
					}
				}
			}
			// this
			update_post_meta( $post_id, QUETAG . '-' . $frompostlang, $from_post_id );
			update_post_meta( $post_id, $this->translation_state, 'initial ' . $frompostlang ); // to update further slug - post_name

			do_action( 'xl_propagate_post_attributes', $from_post_id, $post_id ); // to personalize features - see class theme-multilingual-classes

			return $post_id;
		}
	}

	/**
	 * propagate categories from a reference post to another (loop is in class admin)
	 * if cat is not in target, cat will be unassigned in target if mode is 'erase'
	 *
	 * @since 2.6
	 *
	 */
	public function propagate_categories( $from_post_id, $post_id, $mode = '' ) {
		// categories of from_post
		// get
		$categories = get_the_category( $from_post_id );
		if ( ! empty( $categories ) ) {
			if ( is_object_in_taxonomy( get_post_type( $from_post_id ), 'category' ) ) {
				// set
				$the_cats = array();
				foreach ( $categories as $category ) {
					$the_cats[] = $category->slug; // wp_set_object_terms don't like loop
				}
				// prepare target (erase all)
				if ( 'erase' == $mode ) {
					wp_delete_object_term_relationships( $post_id, 'category' );
				}
				wp_set_object_terms( $post_id, $the_cats, 'category' );
			}
		}
	}

	// propagates added with 2.12

	/**
	 * propagate post_formats
	 *
	 * @since 2.12
	 *
	 * by default copy from original to created post - customization with filter xiliml_propagate_post_format
	 *
	 */
	public function propagate_post_format( $from_post_id, $post_id ) {
		if ( $format = get_post_format( $from_post_id ) ) {
			set_post_format( (int) $post_id, apply_filters( 'xiliml_propagate_post_format', $format ) );
		}
	}

	/**
	 * propagate page_template
	 *
	 * @since 2.12
	 *
	 * by default copy from original to created post - customization with filter xiliml_propagate_page_template
	 *
	 */
	public function propagate_page_template( $from_post_id, $post_id ) {
		if ( 'page' == get_post_type( $from_post_id ) ) {
			// post_type_supports( 'page', 'page-attributes' );
			$template = get_post_meta( $from_post_id, '_wp_page_template', true );
			update_post_meta( $post_id, '_wp_page_template', apply_filters( 'xiliml_propagate_page_template', $template ) );
		}
	}

	/**
	 * propagate comment_status ping_status menu_order post_parent in oneshot
	 *
	 * @since 2.12
	 *
	 * by default copy from original to created post - customization with filter xiliml_propagate_post_columns (need key of column)
	 *
	 */
	public function propagate_post_columns( $from_post_id, $post_id ) {

		// list columns to update
		$options = $this->get_theme_author_rules_options(); // in admin extension

		$from_post = get_post( $from_post_id, ARRAY_A );
		$from_lang = get_cur_language( $from_post_id );

		$to_post = array( 'ID' => $post_id );
		$to_lang = get_cur_language( $post_id );

		if ( array() != $this->propagate_options_default ) { // in admin extension
			$i = 0;
			foreach ( $this->propagate_options_default as $key => $one_propagate ) {
				if ( 'post' == $one_propagate['data'] && isset( $options[ $key ] ) && '' != $options[ $key ] ) { // 2.17.1

					if ( 'post_parent' == $key ) {
						$parent_key = $from_post[ $key ];

						$translated_parent_key = xl_get_linked_post_in( $parent_key, $to_lang ); // return ID
						$to_post[ $key ] = $translated_parent_key;

					} else {
						$to_post[ $key ] = apply_filters( 'xiliml_propagate_post_columns', $from_post[ $key ], $key, $from_lang, $to_lang ); // ready also for excerpt...
					}

					$i++;
				}
			}

			if ( $i > 0 ) {
				wp_update_post( $to_post );
			}
		}
	}

	/**
	 * featured image
	 *
	 * @since 2.12
	 */
	public function propagate_thumbnail_id( $from_post_id, $post_id ) {
		$thumbnail_id = get_post_meta( $from_post_id, '_thumbnail_id', true );

		if ( $thumbnail_id ) {
			$to_lang = get_cur_language( $post_id );
			$translated_value = xl_get_linked_post_in( $thumbnail_id, $to_lang );
			$value = ( 0 != $translated_value ) ? $translated_value : $thumbnail_id; // a translation exist ( title / alt / ...)
			update_post_meta( $post_id, '_thumbnail_id', $value );
		}
	}

	/**
	 * return post object
	 *
	 * @since 2.5
	 *
	 */
	public function temp_get_post( $post_id ) {
		global $wpdb;
		$res = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM %1s WHERE ID = %2d LIMIT 1', $wpdb->posts, $post_id ) );
		if ( $res && ! is_wp_error( $res ) ) {
			return $res;
		} else {
			return false;
		}
	}

	/**
	 * return saved list of post_type and custom_post_type
	 *
	 * @since 2.5
	 *
	 * @updated 2.13.2b
	 *
	 */
	public function authorized_custom_post_type( $fully = false ) {

		$custompoststype = $this->xili_settings['multilingual_custom_post'];
		$custom = get_post_type_object( 'post' );
		$clabels = $custom->labels;
		$custompoststype['post'] = array(
			'name' => $custom->label,
			'singular_name' => $clabels->singular_name,
			'multilingual' => 'enable',
		);
		$custom = get_post_type_object( 'page' );
		$clabels = $custom->labels;
		$custompoststype['page'] = array(
			'name' => $custom->label,
			'singular_name' => $clabels->singular_name,
			'multilingual' => 'enable',
		);
		$custom = get_post_type_object( 'wp_block' ); // since 5.0
		$clabels = $custom->labels;
		$custompoststype['wp_block'] = array(
			'name' => $custom->label,
			'singular_name' => $clabels->singular_name,
			'multilingual' => '',
		);
		if ( $fully ) {
			$custompoststype_enabled = array();
			foreach ( $custompoststype as $post_type => $one ) {
				if ( post_type_exists( $post_type ) && 'enable' == $one['multilingual'] ) {
					$custompoststype_enabled[ $post_type ] = $one;
				}
			}
			return $custompoststype_enabled;
		} else {
			return $custompoststype;
		}
	}

	/**
	 * return array of authorized taxonomies (for link template of language list)
	 *
	 * @since 2.13.3
	 *
	 */
	public function authorized_custom_taxonomies( $post_type_array, $public = true ) {
		$authorized_custom_taxonomies = array(); // to merge
		if ( is_array( $post_type_array ) && array() != $post_type_array ) {
			foreach ( $post_type_array as $post_type ) {
				$taxonomies = get_object_taxonomies( $post_type, 'objects' );
				if ( $public ) {
					$public_cpt_taxonomies = array();
					foreach ( $taxonomies as $taxonomy => $one_t ) {
						if ( $one_t->public ) {
							$public_cpt_taxonomies[] = $taxonomy;
						}
					}
					$authorized_custom_taxonomies = array_merge( $authorized_custom_taxonomies, $public_cpt_taxonomies );
				} else {
					$authorized_custom_taxonomies = array_merge( $authorized_custom_taxonomies, array_keys( $taxonomies ) ); // public and none public
				}
			}
			$authorized_custom_taxonomies = array_diff( $authorized_custom_taxonomies, array( 'category', 'post_tag', 'post_format', TAXONAME ) ); // exclude buit-in
		}
		return $authorized_custom_taxonomies;
	}

	/**
	 * Will update term count based on posts AND pages.
	 * called from register taxonomy etc...
	 * @access private
	 * @since 0.9.8.1
	 * @since 2.22 [limited to authorized taxonomies]
	 * @uses $wpdb
	 *
	 * @param array $terms List of Term taxonomy IDs
	 */
	public function _update_post_lang_count( $terms ) {
		global $wpdb;
		$authorized_taxonomies = array_keys( $this->authorized_custom_post_type( true ) ); // not those of XD !
		$implo = implode( "', '", $authorized_taxonomies );
		foreach ( (array) $terms as $term ) {
			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->term_relationships}, {$wpdb->posts} WHERE $wpdb->posts.ID = $wpdb->term_relationships.object_id AND post_status = 'publish' AND post_type IN ( '%1s' ) AND term_taxonomy_id = '%2s' ", $implo, $term ) );
			$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
		}
	}

	/**
	 * Return language dir
	 *
	 * @since 0.9.9
	 * @since 2.22 with GP_Locales
	 * @param slug of lang
	 */
	public function get_dir_of_cur_language( $lang_slug ) {
		// $rtlarray = explode ('-',$this->rtllanglist);
		// $dir = ( in_array(substr(strtolower($lang_slug), 0, 2 ),$rtlarray) ) ? 'rtl' : 'ltr';
		// return $dir;
		// notice if changing theme 170310
		if ( $lang_slug ) {
			$iso_name = $this->langs_slug_name_array[ $lang_slug ];
			$locale = GP_Locales::by_field( 'wp_locale', $iso_name );
		} else {
			$locale = '';
		}
		return ( $locale ) ? $locale->text_direction : 'ltr';

	}

	/**
	 * Insert rtl.css if exists (default filter of wp_head) - see theme.php (Thanks Sam R.)
	 *
	 * @since 2.4.0
	 */
	public function change_locale_stylesheet_uri( $stylesheet_uri, $stylesheet_dir_uri ) {
		// $rtlarray = explode ('-', $this->rtllanglist);
		// $dir = ( in_array( substr( strtolower( $this->curlang ), 0, 2 ), $rtlarray ) ) ? 'rtl' : 'ltr';
		$text_direction = $this->get_dir_of_cur_language( $this->curlang );
		$dircss = get_stylesheet_directory();

		if ( '' == $stylesheet_uri || false !== strpos( $stylesheet_uri, 'rtl.css' ) || false !== strpos( $stylesheet_uri, 'ltr.css' ) ) {
			if ( 'rtl' == $text_direction ) {

				if ( file_exists( "$dircss/{$text_direction}.css" ) ) {
					return $stylesheet_dir_uri . "/{$text_direction}.css";
				} else {
					return '';
				}
			}
		}
		return $stylesheet_uri; // non filtered value
	}

	/**
	 * Return language of post.
	 *
	 * @since 0.9.0
	 * @updated 0.9.7.6, 0.9.9
	 *
	 * @param $post_id.
	 * @return slug of language of post or false if var langstate is false.
	 */
	public function get_cur_language( $post_id ) {
		$ress = wp_get_object_terms( $post_id, TAXONAME );
		if ( $ress ) {
			if ( is_a( $ress, 'WP_Error' ) ) {
				echo 'Language table not created ! see plug-in admin';
				$this->langstate = false;
			} else {
				$obj_term = $ress[0];
				$this->langstate = true;
				$postlang = $obj_term->slug;
				$postlangdir = $this->get_dir_of_cur_language( $postlang );
				return array(
					QUETAG => $postlang,
					'direction' => $postlangdir,
				);
			}
		} else {
			$this->langstate = false; /* can be used in language attributes for header */
			return false;  /* undefined state */
		}
	}

	public function get_default_slug() {
		$listlanguages = get_terms( TAXONAME, array( 'hide_empty' => false ) );
		$default_slug = 'en_us';
		foreach ( $listlanguages as $language ) {
			if ( $language->name == $this->default_lang ) {
				return $language->slug;
			}
		}
		return $default_slug;
	}

	/**
	 * Query join filter used when querytag is used or home
	 *
	 * @updated 1.7.0 modify page on front and home query
	 * @updated 1.8.4 * to select posts with undefined lang
	 * @updated 2.2.3 LANG_UNDEF = . and no * xili_xl_
	 * @updated 2.12 multiple post_type - fired by where query filter and $ready_to_join_filter (if where needs join)
	 *
	 */
	public function posts_join_tax_lang( $join, $query_object = null ) {
		// new version 2.16.4
		global $wpdb;
		$insert_join = $this->ready_to_join_filter; // value from where filter fired before
		if ( $insert_join ) {
			// ready_to_join_filter set in where filter 2.12 (before optimization - avoid sql error)
			$join .= " LEFT JOIN $wpdb->term_relationships as xtr ON ($wpdb->posts.ID = xtr.object_id) LEFT JOIN $wpdb->term_taxonomy as xtt ON (xtr.term_taxonomy_id = xtt.term_taxonomy_id) ";
		}
		xili_xl_error_log( '# ' . __LINE__ . ' ****** END JOIN *** *** ' . $join );
		return $join;
	}

	/**
	 * to detect undefined query and unset language tax query
	 * @since 2.2.3 - LANG_UNDEF
	 *
	 */
	public function posts_search_filter( $search, $the_query ) {
		$this->sublang = '';

		if ( isset( $the_query->query_vars[ QUETAG ] ) && false !== strpos( $the_query->query_vars[ QUETAG ], LANG_UNDEF ) ) {

			if ( isset( $the_query->tax_query->queries ) && array() != $the_query->tax_query->queries ) {
				$new_queries = array();
				foreach ( $the_query->tax_query->queries as $query ) {
					if ( TAXONAME != $query['taxonomy'] ) {
						$new_queries[] = $query;
					}
				}
				$the_query->tax_query->queries = $new_queries;
			}
			$this->sublang = $the_query->query_vars[ QUETAG ]; // to adapt in where filter below
			unset( $the_query->query_vars[ QUETAG ] );
			unset( $the_query->tax_query->relation );
			if ( isset( $the_query->tax_query->queries ) && array() == $the_query->tax_query->queries ) {
				$the_query->is_tax = false;
			}
		}
		return $search;
	}

	/**
	 * Insert distinct in query if multiple lang queries (lang=fr_fr,en_us)
	 *
	 * @since 2.22
	 */
	public function posts_distinct_lang( $distinct, $query_object = null ) {
		//error_log ( '-> ' . $distinct );
		if ( isset( $query_object->query_vars[ QUETAG ] ) && false !== strpos( $query_object->query_vars[ QUETAG ], ',' ) && ! $distinct ) {
			$distinct = 'DISTINCT';
		}
		return $distinct;
	}

	/**
	 * insert multiple language from search query form
	 *
	 * @since 2.22
	 */
	public function multiple_lang_query( $q ) {
		//return $q; // temporary disabled = redirection now
		if ( $q->is_main_query()
		  // … sur une page de recherche…
		  // && is_search()
		  // … multi-critères
		  && $q->get( 'mlang' ) ) {
			$tax_queries = $q->get( 'tax_query', array() );
			$tax_queries[] = array(
				'taxonomy' => TAXONAME,
				'terms'    => array_filter( (array) $q->get( 'mlang' ) ),
				'field'    => 'slug',
			);
			if ( isset( $tax_queries[1] ) ) {
				$tax_queries['relation'] = 'AND'; // if original tax_queries is not empty
			}
			$q->set( 'tax_query', $tax_queries ); //error_log ( 'q lang ' . serialize ( (array) $q->get( 'mlang' ) ) );
		}
		return $q;
	}


	/**
	 * add vars of checkboxes search query form mlang
	 *
	 * @since 2.22
	 */
	public function multiple_lang_query_vars( $vars ) {
		$vars[] = 'mlang';
		return $vars;
	}

	/**
	 * build redirection of checkboxes search query form with mlang array
	 *
	 * @since 2.22
	 */
	public function multiple_lang_query_redirect( $vars ) {
		if ( isset( $_GET['mlang'] ) ) {
			$lang_slugs = get_query_var( 'mlang', false );
			if ( is_array( $lang_slugs ) ) {
				$search_url = add_query_arg( array( 'lang' => implode( ',', $lang_slugs ) ), false );
				$search_url = remove_query_arg( 'mlang', $search_url );
				wp_redirect( $search_url );
				exit();
			}
		}
	}

	/**
	 * Modify the query including lang or home
	 *
	 * @since 0.9.0
	 * @updated 0.9.4 (OR added) lang=xx_xx,yy_yy,..
	 * @updated 1.7.0 modify page on front and home query
	 * @updated 2.2.3 LANG_UNDEF
	 * @updated 2.12 multiple post_type
	 *
	 * @param object $where.
	 * @return $where.
	 */
	public function posts_where_lang( $where, $query_object = null ) {
		global $wpdb, $wp_query;
		$reqtags = array();
		$thereqtags = array();
		$need_join = false; // 2.12

		if ( '' != $this->sublang ) { // see above - add undefined post

			$lang = str_replace( LANG_UNDEF, '', $this->sublang ); //$query_object->query_vars[QUETAG] ) ;
			if ( '' == $lang ) {
				$lang_string = implode( ', ', $this->xili_settings['available_langs'] );
			} else {
				$id = ( isset( $this->langs_ids_array[ $lang ] ) ) ? $this->langs_ids_array[ $lang ] : 0;
				if ( $id > 0 ) {
					$remain = array_diff( $this->xili_settings['available_langs'], array( $id ) );
					$lang_string = implode( ', ', $remain );
				} else {
					$lang_string = implode( ', ', $this->xili_settings['available_langs'] );
				}
			}
			$need_join = true;
			$where .= " AND $wpdb->posts.ID NOT IN ( SELECT xtr.object_id FROM $wpdb->term_relationships AS xtr INNER JOIN $wpdb->term_taxonomy AS xtt ON xtr.term_taxonomy_id = xtt.term_taxonomy_id WHERE xtt.taxonomy = '" . TAXONAME . "' AND xtt.term_id IN ( $lang_string ) )";

		} elseif ( isset( $query_object->query_vars[ QUETAG ] ) && '' != $query_object->query_vars[ QUETAG ] ) {

			$do_it = false;
			if ( ( isset( $query_object->query_vars['caller_get_posts'] ) && $query_object->query_vars['caller_get_posts'] ) || ( isset( $query_object->query_vars['ignore_sticky_posts'] ) && $query_object->query_vars['ignore_sticky_posts'] ) ) {
				$do_it = false;
			} else {

				if ( $this->lang_perma && ! is_admin() ) {
					if ( $query_object->is_page && isset( $query_object->query_vars[ QUETAG ] ) ) {
						$dummy = false;
					} elseif ( ! ( $query_object->is_home && $this->show_page_on_front ) ) {
						$do_it = true;
					}

					if ( $query_object->is_tax && 'category' == $query_object->query_vars['taxonomy'] ) {
						$do_it = true;
					}
					if ( TAXONAME == $query_object->is_tax && $query_object->query_vars['taxonomy'] && '' == $query_object->query_vars['category_name'] ) {
						$do_it = false;
						if ( ! $query_object->is_page ) {
							$where .= " AND $wpdb->posts.post_type = 'post'";
						}
					}
				} else {
					if ( ! ( $query_object->is_home && $this->show_page_on_front ) ) {
						$do_it = true; // all but not home
					}
				}
			}

			if ( $do_it ) { // insertion of selection

				if ( false !== strpos( $query_object->query_vars[ QUETAG ], ',' ) ) {
					$langs = preg_split( '/[,\s]+/', $query_object->query_vars[ QUETAG ] );

					xili_xl_error_log( '# ' . __LINE__ . "multiple = " . serialize( $langs ) );

					foreach ( (array) $langs as $lang ) {
						$lang = sanitize_term_field( 'slug', $lang, 0, 'post_tag', 'db' );
						$reqtags[] = $lang;
					}
					foreach ( $reqtags as $reqtag ) {
						if ( isset( $this->langs_ids_array[ $this->lang_qv_slug_trans( $reqtag ) ] ) ) {
							$thereqtagids[] = $this->langs_ids_array[ $this->lang_qv_slug_trans( $reqtag ) ]; //  alias
						}
						//$thereqtagids[] = $this->langs_ids_array[$reqtag];
					}
					$wherereqtags = implode( ', ', $thereqtagids );
					$need_join = true;
					$where .= " AND xtt.taxonomy = '" . TAXONAME . "' ";
					$where .= " AND xtt.term_id IN ( $wherereqtags )"; // 2.22
				} else { /* only one lang */
					$query_object->query_vars[ QUETAG ] = sanitize_term_field( 'slug', $query_object->query_vars[ QUETAG ], 0, 'post_tag', 'db' );
					$reqtag = $query_object->query_vars[ QUETAG ];
					if ( isset( $this->langs_ids_array[ $this->lang_qv_slug_trans( $reqtag ) ] ) ) { // 2.19.3
						$need_join = true;
						$wherereqtag = $this->langs_ids_array[ $this->lang_qv_slug_trans( $reqtag ) ];
						if ( isset( $wp_query->query_vars['rest_route'] ) ) {
							$wp_query->query_vars[ QUETAG ] = $reqtag; // json 2.22.8 - class WP_REST_Request 4.4 -
						}
						$where .= " AND xtt.taxonomy = '" . TAXONAME . "' ";
						$where .= " AND xtt.term_id = $wherereqtag ";
					} else {
						$need_join = false;
					}
				}
			} else { // is_home and page

				if ( $query_object->is_main_query() && $query_object->is_home && $this->show_page_on_front ) { // 2.16.4 + test main
					$query_object->is_home = false; // renew the values because the query contains lang=
					$query_object->is_page = true;
					$query_object->is_singular = true;
					$query_object->query = array();
					$query_object->query_vars['page_id'] = get_option( 'page_on_front' ); // new filtered value

					$where = str_replace( "'post'", "'page'", $where ); // post_type =
					$where .= " AND 3=3 AND {$wpdb->posts}.ID = " . $query_object->query_vars['page_id'];
				}

				if ( $this->lang_perma && $this->show_page_on_front ) { // 2.1.1

					if ( '' != $query_object->query_vars[ QUETAG ] && isset( $query_object->query_vars['taxonomy'] ) && TAXONAME == $query_object->query_vars['taxonomy'] ) {

						$query_object->is_page = true;
						$query_object->is_tax = false;
						$query_object->is_archive = false;
						$query_object->is_singular = true;

						$query_object->query = array();
						$pid = $this->get_option_wo_xili( 'page_on_front' );

						$lang = ( isset( $query_object->query_vars[ QUETAG ] ) ) ? $this->lang_qv_slug_trans( $query_object->query_vars[ QUETAG ] ) : 'en_us';

						$id = get_post_meta( $pid, QUETAG . '-' . $lang, true );
						$pagid = ( '' != $id ) ? $id : $pid;
						$query_object->query_vars['page_id'] = $pagid;

						unset( $query_object->query_vars['taxonomy'] );

						$where = str_replace( "'post'", "'page'", $where ); // post_type =
						$where = "AND {$pagid} = {$pagid} AND {$wpdb->posts}.ID = " . $query_object->query_vars['page_id'] . " AND {$wpdb->posts}.post_type = 'page'";

						$query_object->query_vars['page_id'] = get_option( 'page_on_front' );

						unset( $wp_query->queried_object );
						$wp_query->queried_object_id = $query_object->query_vars['page_id'];
						$wp_query->queried_object->ID = $query_object->query_vars['page_id'];

					}
				}
			}
		} else { // no query tag

			if ( ( isset( $query_object->query_vars['caller_get_posts'] ) && $query_object->query_vars['caller_get_posts'] ) || ( isset( $query_object->query_vars['ignore_sticky_posts'] ) && $query_object->query_vars['ignore_sticky_posts'] ) ) {
				// nothing - caller_get_posts deprecated => ignore_sticky_posts
				$dummy = false;
			} else {
				if ( ( $query_object->is_main_query() && $query_object->is_home && ! $this->show_page_on_front && 'modify' == $this->xili_settings['homelang'] ) || ( $query_object->is_home && $query_object->is_posts_page && 'no_select' != $this->xili_settings['pforp_select'] ) ) {
					// force change if loop - home or page_for_posts
					if ( $query_object->is_posts_page ) { // 2.8.4

						if ( $this->is_permalink ) { // 2.8.4.1

							// $pagenametolang = array_flip ( $this->page_for_posts_name_array ) ;
							// now test values before becoming index - 2.15.4
							$pagenametolang = array();
							foreach ( $this->page_for_posts_name_array as $key => $value ) {
								if ( $value && is_string( $value ) ) {
									$pagenametolang[ $value ] = $key;
								}
							}
							if ( isset( $query_object->query_vars['pagename'] ) && isset( $pagenametolang[ $query_object->query_vars['pagename'] ] ) ) {
								$curlang = $pagenametolang[ $query_object->query_vars['pagename'] ];
							} else {
								$curlang = $this->choice_of_browsing_language();
							}
						} else {

							$pageidtolang = array_flip( $this->page_for_posts_array );

							if ( isset( $query_object->query_vars['page_id'] ) && isset( $pageidtolang[ $query_object->query_vars['page_id'] ] ) ) {
								$curlang = $pageidtolang[ $query_object->query_vars['page_id'] ];

							} else {

								$curlang = $this->choice_of_browsing_language();

							}
						}
					} else {
						$curlang = $this->choice_of_browsing_language();
					}

					// 2.12 - // thanks to muh if multiple post_types
					if ( empty( $query_object->query_vars['post_type'] ) ) { // string or array

						$insertwhere = true;
					} elseif ( is_string( $query_object->query_vars['post_type'] ) && in_array( $query_object->query_vars['post_type'], array_keys( $this->authorized_custom_post_type( true ) ) ) ) {

						$insertwhere = true;
					} elseif ( is_array( $query_object->query_vars['post_type'] ) && ! count( array_diff( $query_object->query_vars['post_type'], array_keys( $this->authorized_custom_post_type( true ) ) ) ) ) {
						$insertwhere = true;
					} else {
						$insertwhere = false;
					}

					if ( $insertwhere ) {

						if ( '' != $curlang ) {
							$query_object->query_vars[ QUETAG ] = $curlang; // in query.php posts_join filter is called after posts_where filter - for home page without lang_quetag
							$need_join = true;
							$wherereqtag = $this->langs_ids_array[ $curlang ];
							$where .= " AND xtt.taxonomy = '" . TAXONAME . "' "; //"
							$where .= " AND xtt.term_id = $wherereqtag ";
						}
					}
				}
			}
		}
		xili_xl_error_log( '# ' . __LINE__ . ' ****** END WHERE *** *** ' . $where );
		$this->ready_to_join_filter = $need_join; // 2.12 - join filter after where filter
		return $where;
	}

	/******** template theme live modifications ********/

	public function set_cur_xili_language_term( $cur_slug ) {
		$term_id = $this->langs_ids_array[ $cur_slug ];
		$t_language = xili_language_term::get_instance( $term_id );
		if ( $t_language && ! is_wp_error( $t_language ) ) {
			return $t_language->language_data;
		} else {
			return false;
		}
	}


	/**
	 * wp action for theme at end of query
	 *
	 * @since 0.9.0
	 * @updated 1.1.9, 1.4.2a
	 * call by wp hook
	 *
	 */
	public function xiliml_language_wp() {
		if ( ! is_admin() ) {
			$this->curlang = $this->get_curlang_action_wp(); // see hooks in that function
			// 2.22
			// if changing theme 170310
			if ( $this->curlang ) {
				$this->cur_language = $this->set_cur_xili_language_term( $this->curlang );
				$this->curlang_dir = $this->cur_language->text_direction;

				// $this->curlang_dir = $this->get_dir_of_cur_language( $this->curlang ); /* general dir of the theme */
				if ( $this->locale_method ) {
					$this->xiliml_load_theme_textdomain( $this->thetextdomain ); /* new method for cache compatibility - tests */
				} else {
					$this->set_mofile( $this->curlang );
				}
			}
		}
		xili_xl_error_log( '# ' . __LINE__ . ' ****** END WP *** *** ' . $this->curlang );
	}

	// to fixe event in WP 3.4 - 2.7.1
	public function xili_test_lang_perma() {
		global $xl_permalinks_rules;
		$this->lang_perma = ( class_exists( 'XL_Permalinks_Rules' ) && method_exists( $xl_permalinks_rules, 'insert_lang_tag_root' ) ); // 2.9.23 - must have instanciation in theme
		$this->lpr = '-'; // 2.16.6
	}

	/**
	 * wp action to switch wp_locale class only on front-end
	 *
	 * @since 2.4.0
	 *
	 * call by wp hook after theme cur_lang set
	 *
	 */
	public function xili_locale_setup() {
		if ( ! is_admin() ) {
			unset( $GLOBALS['wp_locale'] );
			global $wp_locale;
			$wp_locale = new Xl_WP_Locale();
		}
	}

	/**
	 * only called in front-end
	 *
	 * @since 2.4.0
	 *
	 */
	public function translate_date_format( $format ) {
		if ( 'wp_locale' == $this->xili_settings['wp_locale'] ) {
			return xl__( $format, $this->thetextdomain );
		} else {
			return $format;
		}
	}

	/**
	 * fixes feed link if not permalinks because called after category filter
	 *
	 * @since 2.8.1
	 *
	 */
	public function category_feed_link( $link, $feed = '' ) {
		$permalink_structure = get_option( 'permalink_structure' );

		if ( '' != $permalink_structure ) {
			if ( get_default_feed() == $feed ) {
				$feed_link = 'feed';
			} else {
				$feed_link = "feed/$feed";
			}
			// clean current link
			$root_link = str_replace( user_trailingslashit( $feed_link, 'feed' ), '', $link );
			// get part after ?lang=
			$parts = explode( '?' . QUETAG . '=', $root_link );
			if ( isset( $parts[1] ) ) {
				// recreate current link
				$link = trailingslashit( $parts[0] ) . user_trailingslashit( $feed_link, 'feed' ) . '?' . QUETAG . '=' . str_replace( '/', '', $parts[1] );
			}
		}
		return $link;

	}

	/**
	 * 'theme_locale' filter to detect theme and don't load theme_domain in functions.php
	 *
	 * @since 1.5.0
	 *
	 * call by 'theme_locale' filter
	 */
	public function xiliml_theme_locale( $locale, $domain ) {
		$this->xili_settings['theme_domain'] = $domain;
		$this->thetextdomain = $domain;

		return 'wx_YZ'; // dummy local
	}

	/**
	 * 'override_load_textdomain' filter to avoid dummy load and update langs_folder
	 *
	 * @since 1.5.0
	 * @updated 1.8.1 - 1.8.5
	 * @updated 2.8.3 (WP 3.5) limited to current theme
	 * @updated 2.12
	 *
	 */
	public function xiliml_override_load( $falseval, $domain, $mofile ) {

		if ( false !== strpos( $mofile, get_stylesheet_directory() ) ) { // limited to current theme - 2.8.3
			if ( $this->show ) {
				if ( ! in_array( $domain, $this->arraydomains ) ) {
					$this->arraydomains[] = $domain;
				}
			}
			if ( false === strpos( $mofile, 'wx_YZ.mo' ) ) {
				return false;
			} else { // dummy locale to detect languages folder
				if ( str_replace( get_stylesheet_directory(), '', $mofile ) == $mofile ) { // no effect
					$this->get_template_directory = get_template_directory();
					$this->xili_settings['langs_in_root_theme'] = 'root';
				} else {
					$this->get_template_directory = get_stylesheet_directory(); // a load is in child
					$this->xili_settings['langs_in_root_theme'] = '';

				}
				$this->ltd = true;

				$langs_folder = str_replace( '/wx_YZ.mo', '', str_replace( $this->get_template_directory, '', $mofile ) );
				// in wp3 the detection is only done here (end user side by theme domain) so updated is mandatory for xili-dico
				//error_log ('langs_folder' . $langs_folder ) ;
				if ( $this->xili_settings['langs_folder'] != $langs_folder ) {
					$this->xili_settings['langs_folder'] = $langs_folder;
					update_option( 'xili_language_settings', $this->xili_settings );
				}
				// to restore theme mo if theme datas and terms in admin ui….
				if ( is_admin() ) {
					//error_log( '-is ad-- ' . $mofile);
					return load_textdomain( $domain, str_replace( 'wx_YZ', get_locale(), $mofile ) );
					//return load_theme_textdomain( $domain, WP_LANG_DIR . '/themes/' . $domain. '-' . get_locale() . '.mo' );
				}

				return true; // to avoid dummy load

			}
			// impossible to use is_child_theme()
		} elseif ( ( get_template_directory() != get_stylesheet_directory() ) && false !== strpos( $mofile, get_template_directory() ) ) {

			if ( false === strpos( $mofile, 'wx_YZ.mo' ) ) {//
				return false;
			} else {
				$parent_langs_folder = str_replace( '/wx_YZ.mo', '', str_replace( get_template_directory(), '', $mofile ) );
				$this->ltd_parent = true;
			}

			if ( $this->xili_settings['parent_langs_folder'] != $parent_langs_folder ) {
				$this->xili_settings['parent_langs_folder'] = $parent_langs_folder;
				update_option( 'xili_language_settings', $this->xili_settings );
			}
			// to restore theme mo if theme datas and terms in admin ui….
			if ( is_admin() ) {
				//error_log( '- is ad-child - ' . $mofile . ' - ' . get_locale() );
				//return load_theme_textdomain( $domain, WP_LANG_DIR . '/themes/' . $domain. '-' . get_locale() . '.mo' );
				return load_textdomain( $domain, str_replace( 'wx_YZ', get_locale(), $mofile ) );
			}

			return true;

		} else {
			return false;
		}
	}

	/**
	 * plugin domain catalog ( hook plugin_locale )
	 */
	public function get_plugin_domain_array( $locale, $domain ) {

		if ( in_array( $domain, $this->unusable_domains ) ) {
			unset( $this->xili_settings['domains'][ $domain ] );
		} elseif ( ! isset( $this->xili_settings['domains'][ $domain ] ) && ! in_array( $domain, $this->unusable_domains ) ) {
			$this->xili_settings['domains'][ $domain ] = 'disable';
			if ( is_admin() ) {
				update_option( 'xili_language_settings', $this->xili_settings );
			}
		}

		return $locale;
	}

	/**
	 *
	 * Introduced only in visitors side (not in admin) to change domain of plugin or other
	 * gettext filter from function xl__() in wp-includes/I10n.php
	 *
	 * @since 1.8.7 - 1.8.8 - 2.8.9 &get
	 */
	public function change_plugin_domain( $translation, $text, $domain ) {

		$domain_t = $this->switching_domain( $domain );

		$translations = get_translations_for_domain( $domain_t );

		return $translations->translate( $text );
	}

	public function change_plugin_domain_with_context( $translation, $text, $context, $domain ) {

		$domain = $this->switching_domain( $domain );

		$translations = get_translations_for_domain( $domain );
		return $translations->translate( $text, $context );
	}
	public function change_plugin_domain_plural( $translation, $single, $plural, $number, $domain ) {

		$domain = $this->switching_domain( $domain );

		$translations = get_translations_for_domain( $domain );
		$translation = $translations->translate_plural( $single, $plural, $number );
		return $translation;
	}
	public function change_plugin_domain_plural_with_context( $translation, $single, $plural, $number, $context, $domain ) {

		$domain = $this->switching_domain( $domain );

		$translations = get_translations_for_domain( $domain );
		$translation = $translations->translate_plural( $single, $plural, $number, $context );
		return $translation;
	}

	/**
	 * domain switching
	 */
	public function switching_domain( $domain ) {
		$ok = 0;
		if ( $domain != $this->thetextdomain ) {
			if ( in_array( $domain, array_keys( $this->xili_settings['domains'] ) ) ) {
				if ( 'enable' == $this->xili_settings['domains'][ $domain ] ) {
					$ok = 1;
				} elseif ( 'renamed' == $this->xili_settings['domains'][ $domain ] ) {
					$ok = 2;
				} elseif ( 'filter' == $this->xili_settings['domains'][ $domain ] ) {
					// recommanded for WooCommerce
					$ok = 3; // filter mode
				}
			} //else {
				//if ( $this->xili_settings['domains']['all'] == 'enable' ) $ok = true ;
			//}
			if ( 1 == $ok ) {
				$domain = $this->thetextdomain;
			} elseif ( 2 == $ok ) {
				$domain = 'xilird_' . $domain; // xili renamed domain
			}
		}

		return $domain;
	}

	/**
	 * locale hook when load_theme_textdomain is present in functions.php
	 *
	 * @since 1.1.9
	 *
	 * call by locale hook if locale_method filter
	 */
	public function xiliml_setlocale( $locale ) {
		if ( true == $this->theme_locale ) {
			$listlanguages = get_terms(
				TAXONAME,
				array(
					'hide_empty' => false,
					'slug' => $this->curlang,
				)
			);
			return $listlanguages[0]->name;
		} else {
			return $locale;
		}
	}

	/**
	 * locale hook when load_theme_textdomain is present in functions.php
	 *
	 * @since 1.1.9
	 *
	 * call by locale hook
	 */
	public function xiliml_load_theme_textdomain( $domain ) {
		$this->theme_locale = true;
		$langfolder = $this->xili_settings['langs_folder'];
		//$langfolder = '/'.str_replace("/","",$langfolder); /* please no lang folder in sub-subfolder */
		$langfolder = ( '/' == $langfolder ) ? '' : $langfolder;
		load_theme_textdomain( $domain, $this->get_template_directory . $langfolder );
		$this->theme_locale = false;
	}

	/**
	 * select .mo file
	 * @since 0.9.0
	 * @updated 0.9.7.1 - 1.1.9 - 1.5.2 wpmu - 1.8.9.1 (domain select) - 2.8.3 (WP 3.5)
	 * @updated 2.13.1 - thanks to Edouard
	 * @updated 2.23.13 - thanks to Guy R. (review priority rules) - since 4.6.0 The core function now tries to load the .mo file from the languages directory first.
	 * call by function xiliml_language_wp()
	 * @param $curlang .
	 */
	public function set_mofile( $curlang ) {
		global $wp_version;

		if ( '' != $this->thetextdomain ) {
			$themetextdomain = $this->thetextdomain;
		} else {
			$themetextdomain = 'ttd-not-defined';
		}
		$langfolder = $this->xili_settings['langs_folder'];
		//$langfolder = ( '' == $langfolder ) ? '' : $langfolder;

		$filename = '';
		if ( '' != $curlang ) {
			if ( isset( $this->langs_slug_name_array[ $curlang ] ) ) {
				$filename = $this->langs_slug_name_array[ $curlang ]; // 2.4.2
			}
		}

		if ( '' != $filename ) {
			$filename .= '.mo';
			$theme_local_mofile = str_replace( '//', '/', $this->get_template_directory . $langfolder . '/local-' . $filename );

			$lang_dir_local_mofile = WP_LANG_DIR . "/themes/{$themetextdomain}-local-{$filename}";
			$lang_dir_mofile = WP_LANG_DIR . "/themes/{$themetextdomain}-{$filename}";
			$lang_dir_parent_mofile = WP_LANG_DIR . "/themes/{$themetextdomain}-{$filename}";

			$mofile = $this->get_template_directory . $langfolder . "/$filename"; // only child or parent subfolder
			$parent_mofile = ( is_child_theme() ) ? $this->get_parent_theme_directory . $this->xili_settings['parent_langs_folder'] . "/$filename" : '';

			if ( is_multisite() ) {
				/* completes theme's language with db structure languages (cats, desc,…) */
				if ( ( $uploads = wp_upload_dir() ) && false === $uploads['error'] ) {
					$wpmu_curdir = $uploads['basedir'] . '/languages';
					load_textdomain( $themetextdomain, $wpmu_curdir . '/local-' . $this->langs_slug_name_array[ $curlang ] . '.mo' ); // here to be the last value (created by each instance)
					load_textdomain( $themetextdomain, $wpmu_curdir . "/$filename" );
				}
			}
			// child theme and priority
			// @since 2.23.13 - rewritten (Thanks to Guy R.)
			// in xl - first search in theme folder because child and parent share the same theme-domain
			// when testing, priority can be defined
			if ( is_child_theme() ) {
				if ( 'child-priority' == $this->xili_settings['mo_parent_child_merging'] ) {
					// unload_textdomain( $themetextdomain );
					// LOCAL
					if ( ! load_textdomain( $themetextdomain, $theme_local_mofile ) ) {
						load_textdomain( $themetextdomain, $lang_dir_local_mofile );
					}
					// child
					if ( ! load_textdomain( $themetextdomain, $mofile ) ) {
						load_textdomain( $themetextdomain, $lang_dir_mofile );
					}
					// parent
					if ( ! load_textdomain( $themetextdomain, $parent_mofile ) ) {
						// now same rules for parent file if not in parent theme dir // 2.16.0
						load_textdomain( $themetextdomain, $lang_dir_parent_mofile );
					}
				} elseif ( 'parent-priority' == $this->xili_settings['mo_parent_child_merging'] ) {
					// unload_textdomain( $themetextdomain );
					// local
					if ( ! load_textdomain( $themetextdomain, $theme_local_mofile ) ) {
						// here to be the last value
						load_textdomain( $themetextdomain, $lang_dir_local_mofile );
					}
					// parent
					if ( ! load_textdomain( $themetextdomain, $parent_mofile ) ) {
						// now same rules for parent file if not in parent theme dir // 2.16.0
						load_textdomain( $themetextdomain, $lang_dir_parent_mofile );
					}
					// child
					if ( ! load_textdomain( $themetextdomain, $mofile ) ) {
						load_textdomain( $themetextdomain, $lang_dir_mofile );
					}
				} else {
					// child in child langs subfolder
					if ( ! load_textdomain( $themetextdomain, $mofile ) ) {
						if ( ! load_textdomain( $themetextdomain, $lang_dir_mofile ) ) {
							xili_xl_error_log( '# ' . __LINE__ . ' - this file is not found : ' . $mofile );
						}
					}
					// and no priority for local
					if ( ! load_textdomain( $themetextdomain, $theme_local_mofile ) ) {
						// here to be the last value
						load_textdomain( $themetextdomain, $lang_dir_local_mofile );
					}
				}
			} else {
				// theme w/o child
				// local has no priority
				// 2.12.1 - now able to search in WP_LANG_DIR/themes/

				if ( ! load_textdomain( $themetextdomain, $theme_local_mofile ) ) {
					// here to be the last value
					load_textdomain( $themetextdomain, $lang_dir_local_mofile );
				}

				if ( ! load_textdomain( $themetextdomain, $mofile ) ) {
					if ( ! load_textdomain( $themetextdomain, $lang_dir_mofile ) ) {
						xili_xl_error_log( '# ' . __LINE__ . ' - this file is not found : ' . $mofile );
					}
				}
			}

			// 2.15.2
			do_action( 'load_plugin_domain_for_curlang_bbpress', $themetextdomain, $this->langs_slug_name_array[ $curlang ] ); // to add bbpress good mo - need specific
			do_action( 'xiliml_add_frontend_mofiles', $themetextdomain, $this->langs_slug_name_array[ $curlang ] );

		}
	}

	/**
	 * load additional .mo file according specific domains
	 *
	 * @since 2.14.0
	 *
	 */
	public function load_plugin_domain_for_curlang( $themetextdomain, $iso_curlang ) {
		$detected_domains = array_keys( $this->xili_settings['domains'] );
		foreach ( $detected_domains as $plugin_domain ) {
			if ( 'renamed' == $this->xili_settings['domains'][ $plugin_domain ] ) {

				$mofile = $plugin_domain . '-' . $iso_curlang . '.mo';

				unload_textdomain( $plugin_domain );

				if ( isset( $this->xili_settings['domain_paths'][ $plugin_domain ] ) ) {
					$path = WP_PLUGIN_DIR . $this->xili_settings['plugin_paths'][ $plugin_domain ] . trim( $this->xili_settings['domain_paths'][ $plugin_domain ], '/' );
					if ( load_textdomain( 'xilird_' . $plugin_domain, $path . '/' . $mofile ) ) {
						return;
					}
				}

				$path = WP_PLUGIN_DIR . $this->xili_settings['plugin_paths'][ $plugin_domain ] . 'languages/';
				if ( load_textdomain( 'xilird_' . $plugin_domain, $path . '/' . $mofile ) ) {
					return;
				}

				load_textdomain( 'xilird_' . $plugin_domain, WP_LANG_DIR . '/plugins/' . $mofile );

			} elseif ( 'filter' == $this->xili_settings['domains'][ $plugin_domain ] ) {
				do_action( 'load_plugin_domain_for_curlang_' . str_replace( '-', '_', $plugin_domain ), $plugin_domain, $iso_curlang );
			}
		}
	}

	/**
	 * default rules - set curlang for wp action
	 *
	 * @since 1.7.0 - new mechanism for front-page
	 * @updated 1.8.9.1 - better category case
	 * @updated 2.2.3 - fixes rare frontpage infinite loop
	 * replace xiliml_cur_lang_head (0.9.7 -> 1.6.1)
	 * @return $curlang
	 */
	public function get_curlang_action_wp() {

		if ( has_filter( 'xiliml_curlang_action_wp' ) ) {
			return apply_filters( 'xiliml_curlang_action_wp', '' ); /* '' warning on some server need one arg by default*/
		}
		/* default */
		global $post, $wp_query;

		if ( have_posts() ) {
			$showpage = get_option( 'show_on_front' );
			$condition = false;
			if ( 'page' == $showpage ) {
				if ( ! in_array( $wp_query->query_vars['page_id'], $this->xili_settings['show_page_on_front_array'] ) ) {
					$condition = true;
				}
			} else {
				if ( ! is_home() ) {
					$condition = true;
				}
			}

			if ( $condition ) { /* every pages !is_front_page() */
				$curlangdir = $this->get_cur_language( $post->ID );
				$curlang = $curlangdir[ QUETAG ]; /* the first post give the current lang*/
				if ( false == $curlangdir ) {
					$curlang = $this->choice_of_browsing_language(); //$this->default_slug; /* no more constant - wpmu - can be changed if use hook */
				}
				if ( is_page() ) {
					if ( isset( $wp_query->query_vars[ QUETAG ] ) ) {
						$curlang = $this->lang_qv_slug_trans( $wp_query->query_vars[ QUETA ] );
					}
				} elseif ( is_search() ) {
					if ( isset( $wp_query->query_vars[ QUETAG ] ) ) {
						$curlang = $this->lang_qv_slug_trans( $wp_query->query_vars[ QUETAG ] );
					}
				} elseif ( is_category() ) {
					if ( $this->lang_perma ) {
						if ( isset( $wp_query->query_vars[ QUETAG ] ) ) {
							$curlang = str_replace( LANG_UNDEF, '', $this->lang_qv_slug_trans( $wp_query->query_vars[ QUETAG ] ) );
						} else {
							$curlang = $this->choice_of_categories_all_languages( $curlang );
						}
					} else {
						if ( isset( $wp_query->query_vars[ QUETAG ] ) ) {
							$curlang = $this->lang_qv_slug_trans( $wp_query->query_vars[ QUETAG ] );
						} else {
							$curlang = $this->choice_of_categories_all_languages( $curlang ); //$this->choice_of_browsing_language(); // again as defined 1.8.9.1
						}
					}
				}
			} else {
				/* front page - switch between lang (and post/page) is done in query posts_where_lang filter see above */

				if ( isset( $wp_query->query_vars[ QUETAG ] ) && '' != $wp_query->query_vars[ QUETAG ] ) {
					$this->langstate = true; // 2.8.0 b
					$curlang = $this->lang_qv_slug_trans( $wp_query->query_vars[ QUETAG ] );  // home series type

				} else {

					if ( 'page' == $showpage ) {
						//$this->show_page_on_front ) {
						$page_front = get_option( 'page_on_front' );  // filtered only if GET known
						// 2.9.21
						if ( in_array( $wp_query->query_vars['page_id'], $this->xili_settings['show_page_on_front_array'] ) ) {
							$curlang = get_cur_language( $wp_query->query_vars['page_id'] );
						} else {
							$curlang = get_cur_language( $page_front ); // redund !
						}
					} else { // home.php - 1.3.2

						$curlang = $this->choice_of_browsing_language();

					}
				}
			}
		} else { /*no posts for instance in category + lang */
			if ( $this->lang_perma ) {
				if ( isset( $wp_query->query_vars[ QUETAG ] ) ) {
					$curlang = $this->lang_qv_slug_trans( $wp_query->query_vars[ QUETAG ] );
				} else {
					$curlang = $this->choice_of_browsing_language();
				}
			} else {
				if ( isset( $wp_query->query_vars[ QUETAG ] ) ) {
					$curlang = $this->lang_qv_slug_trans( $wp_query->query_vars[ QUETAG ] );
				} else {
					$curlang = $this->choice_of_browsing_language(); //$this->choice_of_browsing_language(); // again as defined 1.8.9.1
				}
			}
		}

		xili_xl_error_log( '# ' . __LINE__ . ' - end get_curlang_action_wp = ' . $curlang );

		return str_replace( LANG_UNDEF, '', $curlang ); // 2.3 to return main part
	}


	/**
	 * used in head_insert_hreflang_link and in xili_language_list
	 *
	 * @since 2.5
	 *
	 * @updated 2.11.1
	 * @updated 2.11.2
	 * @updated 2.16.4
	 * @updated w/o ? at end - 2.19.3
	 */
	public function current_url( $lang_perma ) {
		// to create your own rules to build current url of language switcher in your own context
		if ( has_filter( 'xiliml_link_current_url' ) ) {
			return apply_filters( 'xiliml_link_current_url', $lang_perma );
		}

		global $xl_permalinks_rules, $wp_query;
		$format = get_post_format(); // take first current post format
		if ( $lang_perma ) {
			if ( is_category() ) {
				if ( version_compare( XILILANGUAGE_VER, '2.9.22', '<=' ) ) {
					remove_filter( 'term_link', 'insert_lang_4cat' );
				} else {
					remove_filter( 'term_link', array( $xl_permalinks_rules, 'insert_lang_taxonomy' ), 10 );
				}
				$catcur = xiliml_get_category_link();
				if ( version_compare( XILILANGUAGE_VER, '2.9.22', '<=' ) ) {
					add_filter( 'term_link', 'insert_lang_4cat', 10, 3 );
				} else {
					add_filter( 'term_link', array( $xl_permalinks_rules, 'insert_lang_taxonomy' ), 10, 3 );
				}
				$currenturl = $catcur;

			} elseif ( $this->authorized_taxonomies && is_tax( $this->authorized_taxonomies ) ) {
				$termlink = get_bloginfo( 'url' ) . '/%lang%/'; // 2.16.4

				foreach ( $this->authorized_taxonomies as $taxonomy_tested ) {
					if ( is_tax( $taxonomy_tested ) ) {

						$t = get_taxonomy( $taxonomy_tested );
						$slug_var = ( $t->query_var ) ? $t->query_var : $taxonomy_tested;
						global $wp_query;
						$term_id = $wp_query->query_vars[ $slug_var ];

						$termlink = get_term_link( $term_id, $taxonomy_tested );
						continue;
					}
				}
				$currenturl = $termlink;

			} elseif ( is_archive() ) {
				$post_type = get_post_type();
				if ( is_post_type_archive( $post_type ) && isset( $wp_query->query_vars['post_type'] ) ) {
					$currenturl = ( get_post_type_archive_link( $post_type ) ) ? get_post_type_archive_link( $post_type ) : get_bloginfo( 'url' ) . '/%lang%/';
				} elseif ( is_date() ) {
					global $wp_query;
					$date_array = $wp_query->query;
					if ( isset( $date_array['day'] ) ) {
						$result = get_day_link( $date_array['year'], $date_array['monthnum'], $date_array['day'] );
					} elseif ( isset( $date_array['monthnum'] ) ) {
						$result = get_month_link( $date_array['year'], $date_array['monthnum'] );
					} elseif ( isset( $date_array['year'] ) ) {
						$result = get_year_link( $date_array['year'] );
					}
					$currenturl = $result;
				} elseif ( has_post_format( $format ) && isset( $wp_query->query_vars['post_format'] ) ) {
					$currenturl = get_post_format_link( $format );
				} else {
					$currenturl = get_bloginfo( 'url' ) . '/%lang%/';
				}
			} elseif ( has_post_format( $format ) && isset( $wp_query->query_vars['post_format'] ) ) { // in initial query
				$currenturl = get_post_format_link( $format );

			} else {
				$currenturl = get_bloginfo( 'url' ) . '/%lang%/';
			}
		} else {

			if ( is_category() ) {
				$catcur = xiliml_get_category_link(); // w/o default filter

				$currenturl = $catcur;

			} elseif ( $this->authorized_taxonomies && is_tax( $this->authorized_taxonomies ) ) {
					$termlink = trailingslashit( get_bloginfo( 'url' ) ); // 2.16.4
				foreach ( $this->authorized_taxonomies as $taxonomy_tested ) {
					if ( is_tax( $taxonomy_tested ) ) {

						$t = get_taxonomy( $taxonomy_tested );
						$slug_var = ( $t->query_var ) ? $t->query_var : $taxonomy_tested;
						global $wp_query;
						$term_id = $wp_query->query_vars[ $slug_var ];

						$termlink = get_term_link( $term_id, $taxonomy_tested );
						continue;
					}
				}
				$currenturl = $termlink;

			} elseif ( is_archive() ) {

				$post_type = get_post_type();
				if ( is_post_type_archive( $post_type ) && isset( $wp_query->query_vars['post_type'] ) ) {
					$currenturl = ( get_post_type_archive_link( $post_type ) ) ? get_post_type_archive_link( $post_type ) : get_bloginfo( 'url' );
				} elseif ( is_date() ) {
					global $wp_query;
					$result = '';
					$date_array = $wp_query->query;
					if ( isset( $date_array['day'] ) ) {
						$result = get_day_link( $date_array['year'], $date_array['monthnum'], $date_array['day'] );
					} elseif ( isset( $date_array['monthnum'] ) ) {
						$result = get_month_link( $date_array['year'], $date_array['monthnum'] );
					} elseif ( isset( $date_array['year'] ) ) {
						$result = get_year_link( $date_array['year'] );
					}
					if ( $result ) { // thanks to Vladimir N.
						$currenturl = $result;
					} else {
						if ( isset( $date_array['m'] ) ) {
							$currenturl = add_query_arg(
								array(
									'm' => $date_array['m'],
								),
								trailingslashit( get_bloginfo( 'url' ) )
							);
						} else {
							$currenturl = trailingslashit( get_bloginfo( 'url' ) );
						}
					}
				} elseif ( has_post_format( $format ) && isset( $wp_query->query_vars['post_format'] ) ) {
					$currenturl = get_post_format_link( $format );
				} else {
					$currenturl = trailingslashit( get_bloginfo( 'url' ) );
				}
			} elseif ( has_post_format( $format ) && isset( $wp_query->query_vars['post_format'] ) ) { // in initial query
				$currenturl = get_post_format_link( $format );

			} else {
				$currenturl = trailingslashit( get_bloginfo( 'url' ) );
			}
		}
		return $currenturl;
	}

	/**
	 * return count of posts in taxonomy and language
	 *
	 * called by head_insert_hreflang_link
	 * available for specific behaviour - why not in custom plugin for xili_language_list or...
	 * @params taxonomy (category), current slug, current lang
	 * @since 2.5
	 */
	public function count_posts_in_taxonomy_and_lang( $taxonomy, $slug = '', $language = '', $post_type = 'post' ) {

		$args = array(
			'post_type' => $post_type,
			'tax_query' => array(
				'relation' => 'AND',
				array(
					'taxonomy' => $taxonomy,
					'field' => 'slug',
					'terms' => array( $slug ),
				),
				array( // language
					'taxonomy' => TAXONAME,
					'field' => 'slug',
					'terms' => array( $language ),
				),
			),
		);
		$query = new WP_Query( $args );

		return $query->found_posts;
	}

	/**
	 * Prepare filter for selected theme_mod...
	 *
	 * @since 2.18.2
	 *
	 */
	public function theme_mod_create_filters() {
		$curtheme = get_option( 'stylesheet' ); // both child or not
		if ( ! is_admin() && $this->theme_mod_to_be_filtered ) {
			foreach ( $this->theme_mod_to_be_filtered[ $curtheme ] as $config_name ) {
				$filtername = 'theme_mod_' . $config_name;
				add_filter( $filtername, array( &$this, 'one_text' ) );
			}
		}
	}

	/**
	 * callable by theme to populate theme_mod filter array
	 *
	 * @see example in latest version of twentyfifteen-xili child theme
	 * @since 2.18.2
	 *
	 */
	public function set_theme_mod_to_be_filtered( $theme_mod_index ) {
		if ( $theme_mod_index && is_string( $theme_mod_index ) ) {
			$curtheme = get_option( 'stylesheet' );
			$this->theme_mod_to_be_filtered[ $curtheme ][] = $theme_mod_index;
		}
	}

	/**
	 * Translate texts of widgets or other simple text...
	 *
	 * @updated 1.6.0 - Old name widget_texts
	 * @since 0.9.8.1
	 * @ return
	 */
	public function one_text( $value ) {
		if ( '' != $value ) {
			return xl__( $value, $this->thetextdomain );
		} else {
			return $value;
		}
	}

	/**
	 * Translate title of wp nav menu
	 *
	 * @since 1.6.0
	 * @ return
	 */
	public function wp_nav_title_text( $value = '', $itemid = 0 ) {
		if ( '' != $value ) {
			return xl__( $value, $this->thetextdomain );
		} else {
			return $value;
		}
	}

	/**
	 * insert other language of wp_list_categories
	 *
	 * @since 0.9.0
	 * @updated 0.9.8.4 - 1.4.1 = no original term in ()
	 * @updated 2.16.2
	 * can be hooked by filter add_filter('xiliml_cat_language','yourfunction',2,3) in functions.php
	 * call by do_filter list_cats
	 * @param $content, $category
	 */
	public function xiliml_cat_language( $content, $category = null ) {
		if ( has_filter( 'xiliml_cat_language' ) ) {
			return apply_filters( 'xiliml_cat_language', $content, $category, $this->curlang );
		}
		$new_cat_name = ( ! is_admin() && $category ) ? xl__( $category->name, $this->thetextdomain ) : $content;  /*to detect admin UI*/
		return $new_cat_name;
	}

	/**
	 * add the language key in category links of current pages - obsolete - backwards compatibility
	 * @need cleaning
	 * @since 0.9.0
	 * update 0.9.7 1.5.1
	 * can be hooked by filter add_filter('xiliml_link_append_lang','yourfunction',10,3) in functions.php
	 * call by do_filter
	 * @param $content,
	 */
	public function xiliml_link_append_lang( $termlink, $category_id = 0 ) {
		if ( has_filter( 'xiliml_link_append_lang' ) ) {
			return apply_filters( 'xiliml_link_append_lang', $link, $category_id, $this->curlang );
		}
		/*default*/

		/*
		if ($this->curlang) {
			if ( !$this->lang_perma ){ // 2.1.1
				$permalink = get_option( 'permalink_structure' );
				$sep = ('' == $permalink) ? "&amp;".QUETAG."=" : "?".QUETAG."=";
				$language_qv = $this->lang_slug_qv_trans ( $this->curlang );
				$link .= $sep. $language_qv ;
			}
		}
		return $link;
		*/
		return $this->xiliml_term_link_append_lang( $termlink, $category_id, 'category' ); // see below
	}

	/**
	 * add the language key in term links of current pages
	 * @need cleaning
	 * @since 2.13.3
	 *
	 * can be hooked by filter add_filter('xiliml_term_link_append_lang','yourfunction',10 , 4) in functions.php
	 *
	 * @param $termlink, $term, $taxonomy
	 */
	public function xiliml_term_link_append_lang( $termlink, $term, $taxonomy ) {
		if ( has_filter( 'xiliml_term_link_append_lang' ) ) {
			return apply_filters( 'xiliml_term_link_append_lang', $termlink, $term, $taxonomy, $this->curlang );
		}
		/*default*/

		if ( $this->curlang && in_array( $taxonomy, array( 'category' ) ) ) { // backwards compat
			if ( ! $this->lang_perma ) { // 2.1.1
				$permalink = get_option( 'permalink_structure' );
				$sep = ( '' == $permalink ) ? '&amp;' . QUETAG . '=' : '?' . QUETAG . '=';
				$language_qv = $this->lang_slug_qv_trans( $this->curlang );
				$termlink .= $sep . $language_qv;
			}
		}

		return $termlink;
	}

	/**
	 * Setup global post data.
	 *
	 * @since 1.6.0
	 * can be hooked by filter add_filter('xiliml_bloginfo','yourfunction',10,3) in functions.php
	 *
	 * @param $output, $show.
	 * @return translated $output.
	 */
	public function xiliml_bloginfo( $output, $show ) {
		if ( has_filter( 'xiliml_bloginfo' ) ) {
			return apply_filters( 'xiliml_bloginfo', $output, $show, $this->curlang );
		}
		$info_enabled = array( 'name', 'blogname', 'description' );
		if ( in_array( $show, $info_enabled ) ) {
			return xl__( $output, $this->thetextdomain );
		} else {
			return $output;
		}
	}

	/**
	 * filters for wp_get_archives
	 *
	 * @since 0.9.2
	 * @params $join or $where and template params
	 *
	 */
	public function xiliml_getarchives_join( $join, $r ) {
		global $wpdb;
		if ( has_filter( 'xiliml_getarchives_join' ) ) {
			return apply_filters( 'xiliml_getarchives_join', $join, $r, $this->curlang );
		}
		extract( $r, EXTR_SKIP );
		$this->get_archives_called = $r;
		if ( isset( $lang ) ) {
			if ( '' == $lang ) { /* used for link */
				$this->get_archives_called[ QUETAG ] = $this->curlang;
			} else {
				$this->get_archives_called[ QUETAG ] = $lang;
			}
			$join = " INNER JOIN $wpdb->term_relationships as tr ON ($wpdb->posts.ID = tr.object_id) INNER JOIN $wpdb->term_taxonomy as tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) ";

		}
		return $join;
	}

	public function xiliml_getarchives_where( $where, $r ) {
		global $wpdb;
		if ( has_filter( 'xiliml_getarchives_where' ) ) {
			return apply_filters( 'xiliml_getarchives_where', $where, $r, $this->curlang );
		}
		extract( $r, EXTR_SKIP );
		if ( isset( $lang ) ) {
			if ( '' == $lang ) {
				$curlang = $this->curlang;
			} else {
				$curlang = $lang;
			}
			$reqtag = term_exists( $curlang, TAXONAME );
			if ( '' != $reqtag ) {
				$wherereqtag = $reqtag['term_id'];
			} else {
				$wherereqtag = 0;
			}
			$where .= " AND tt.taxonomy = '" . TAXONAME . "' ";
			$where .= " AND tt.term_id = $wherereqtag ";
		}
		return $where;
	}

	/**
	 * here basic translation - to improve depending theme features : use hook 'xiliml_get_archives_link'
	 *
	 */
	public function xiliml_get_archives_link( $link_html ) {
		if ( has_filter( 'xiliml_get_archives_link' ) ) {
			return apply_filters( 'xiliml_get_archives_link', $link_html, $this->get_archives_called, $this->curlang );
		}
		extract( $this->get_archives_called, EXTR_SKIP );
		if ( isset( $lang ) && '' != $lang ) {
			$permalink = get_option( 'permalink_structure' );
			$sep = ( '' == $permalink ) ? '&amp;' . QUETAG . '=' : '?' . QUETAG . '=';
			if ( 'option' != $format && 'link' != $format && 'postbypost' != $type && 'alpha' != $type ) {
				/* text extract */
				$i = preg_match_all( "/'>(.*)<\/a>/Ui", $link_html, $matches, PREG_PATTERN_ORDER );
				$line = $matches[1][0];
				/* link extract - no title by default 2.16.3*/
				$i = preg_match_all( "/href='(.*)'>/Ui", $link_html, $matches, PREG_PATTERN_ORDER );
				if ( '' == $type || 'monthly' == $type ) {
					if ( '' == $permalink ) {
						$archivedate = str_replace( get_bloginfo( 'url' ) . '/?', '', $matches[1][0] );
						$r = wp_parse_args( $archivedate, array() );
						extract( $r, EXTR_SKIP );
						$month = substr( $m, -2 );
						$year = substr( $m, 0, 4 );
					} else {
						/* Due to prevents post ID and date permalinks from overlapping using /date/ v 1.1.9
						 * no / at end for "numeric" permalink giving /archives/date/2009/04
						 */
						$thelink = $matches[1][0];
						$i = preg_match_all( '/\/([0-9]{4})\/([0-9]{2})/Ui', $thelink, $results, PREG_PATTERN_ORDER );
						if ( $i ) {
							$month = $results[2][0];
							$year = $results[1][0];
						}
					}
					$time = strtotime( $month . '/1/' . $year );
					$line2print = the_xili_local_time( '%B %Y', $time ); /* use server local*/
					if ( $line2print ) {
						str_replace( $line, $line2print, $link_html ); //1.6.3.1
					}
				}
				if ( ! $this->lang_perma ) {
					$link_html = str_replace( "'>", $sep . $lang . "'>", $link_html );
				}
			} elseif ( 'link' == $format ) {
				//  2.16.3
				if ( ! $this->lang_perma ) {
					$link_html = str_replace( "' />", $sep . $lang . "' />", $link_html );
				}
			} else { // option and custom
				if ( ! $this->lang_perma ) {
					$link_html = str_replace( "'>", $sep . $lang . "'>", $link_html );
				}
			}
		}
		return $link_html;
	}


	/**
	 * translate description of archive (since WP 4.1)
	 *
	 * @since 2.17.1
	 *
	 */
	public function get_the_archive_description( $description ) {
		if ( ! $description ) {
			return '';
		}
		preg_match( '/<p>(.*)<\/p>/', $description, $match ); // ever return description with <p> with default filter wpautop for display description
		return '<p>' . xl__( $match[1], $this->thetextdomain ) . '</p>';
	}

	/**
	 * Return the list of preferred languages for displaying pages (see in firefox prefs)
	 * thanks to php.net comments HTTP_ACCEPT_LANGUAGE
	 * @since 0.9.7.5
	 * @updated 2.7.1
	 * @return array (non sorted)
	 */
	public function the_preferred_languages() {
		$preferred_languages = array();
		if ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) && preg_match_all( '#([^;,]+)(;[^,0-9]*([0-9\.]+)[^,]*)?#i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				if ( isset( $match[3] ) ) {
					$preferred_languages[ $match[1] ] = floatval( $match[3] );
				} else {
					$preferred_languages[ $match[1] ] = 1.0;
				}
			}
			return $preferred_languages;
		} else {
			return false;
		}
	}
	/**
	 * Return the lang defined by admin UI if no browser
	 *
	 * @since 1.0
	 *
	 */
	public function choice_of_home_selected_lang() {
		if ( 'browser' == $this->xili_settings['browseroption'] ) {
			return choice_of_browsing_language();
		} elseif ( '' != $this->xili_settings['browseroption'] ) {
			/* slug of the lang*/
			return $this->xili_settings['browseroption'];
		} else {
			return strtolower( $this->default_lang );
		}
	}

	/**
	 * Return the list of preferred languages for displaying pages (see in firefox prefs)
	 * thanks to php.net comments HTTP_ACCEPT_LANGUAGE
	 * @since 0.9.7.5
	 * @updated 0.9.9.4
	 * @updated 2.3.1 - lang_neither_browser
	 * @updated 2.8.7 - strtolower
	 * @updated 2.11.3 - wp_error
	 * @return default target language
	 */
	public function choice_of_browsing_language( $test = '' ) {
		if ( has_filter( 'choice_of_browsing_language' ) ) {
			return apply_filters( 'choice_of_browsing_language', '' ); // '' 2.3.1
		}
		if ( 'browser' != $this->xili_settings['browseroption'] ) {
			return $this->choice_of_home_selected_lang(); /* in settings UI - after filter to hook w/o UI */
		}
		$listofprefs = $this->the_preferred_languages();
		$default_lang = ( '' != $this->xili_settings['lang_neither_browser'] ) ? $this->xili_settings['lang_neither_browser'] : $this->default_lang; //2.3.1
		if ( is_array( $listofprefs ) ) {
			arsort( $listofprefs, SORT_NUMERIC );
			$listlanguages = get_terms( TAXONAME, array( 'hide_empty' => false ) );
			if ( ! is_wp_error( $listlanguages ) ) {
				// redundant to call in translate_page_on_front_ID filter 2.11.3
				$sitelanguage = $this->match_languages( $listofprefs, $listlanguages );
				if ( $sitelanguage ) {
					return $sitelanguage->slug;
				}
			}
			return strtolower( $default_lang );
		} else {
			return strtolower( $default_lang );
		}
	}

	/**
	 * @updated 2.9.21
	 * in priority list in firefox five chars must be before 2 chars - it-ch and it
	 */
	public function match_languages( $listofprefs, $listlanguages ) {

		foreach ( $listofprefs as $browserlanguage => $priority ) {
					/* match root languages to give similar in site - first : five chars langs*/
			foreach ( $listlanguages as $sitelanguage ) {
				// strtolower for IE - thanks to z8po // 2.8.7
						// equal
				if ( str_replace( '-', '_', strtolower( $browserlanguage ) ) == $sitelanguage->slug ) {
					return $sitelanguage;
						// only first two chars
				}
				if ( strtolower( $browserlanguage ) == substr( $sitelanguage->slug, 0, 2 ) ) {
					return $sitelanguage;
				}
			}
		}
	}

	/**
	 * Choice of language when is_category and all languages
	 *
	 * @since 1.8.9.1
	 * called by get_curlang_action_wp
	 *
	 */
	public function choice_of_categories_all_languages( $curlang ) {
		$choice = $this->xili_settings['allcategories_lang'];
		if ( 'browser' == $choice ) {
			return $this->choice_of_browsing_language();
		} elseif ( 'firstpost' == $choice ) {
			return $curlang;
		} elseif ( '' == $choice ) {
			if ( function_exists( 'xl_choice_of_categories_all_languages' ) ) {
				return xl_choice_of_categories_all_languages();
			} else {
				return ''; // return without mo
			}
		}
		return $choice;
	}

	/**
	 * to encapsulate future method
	 *
	 * @since 1.8.9.1
	 * @param post_ID
	 * @param lang slug
	 */
	public function linked_post_in( $fromid, $lang_slug ) {
		return get_post_meta( $fromid, QUETAG . '-' . $lang_slug, true ); // will be soon changed
	}

	/**
	 * if possible, translate the array of ID of sticky posts
	 *
	 * @since 1.6.1
	 * called by hook option_sticky_posts
	 * @param array of sticky post ID
	 *
	 * @updated 2.8.1
	 * @updated 2.16.4
	 */
	public function translate_sticky_posts_id( $original_array ) {
		global $wp_query;
		// thanks to pjgunst 20150401 - if get_option is called before $wp_query set (rare ?)
		if ( ! is_admin() && isset( $wp_query ) && $wp_query->is_main_query() && is_home() ) { // because impossible to register the value in admin UI -
			// and because tracs http://core.trac.wordpress.org/ticket/14115
			if ( array() != $original_array ) {
				$translated_array = array();
				if ( isset( $wp_query->query_vars[ QUETAG ] ) ) { //if (isset($_GET[QUETAG])) { // $_GET not usable by lang perma mode 2.8.1

					$curlang = $this->lang_qv_slug_trans( $wp_query->query_vars[ QUETAG ] ); // compatible with lang perma mode 2.16.4 alias

				} else {
					$curlang = $this->choice_of_browsing_language(); // rule defined in admin UI
				}
				foreach ( $original_array as $id ) {
					$langpost = $this->get_cur_language( $id );
					$post_lang = $langpost[ QUETAG ];
					if ( $post_lang != $curlang ) { // only if necessary
						$trans_id = $this->linked_post_in( $id, $curlang ); // get_post_meta($id, 'lang-'.$curlang, true);
						if ( '' != $trans_id ) {
							$translated_array[] = $trans_id;
						} else {
							if ( true === $this->sticky_keep_original ) {
								$translated_array[] = $id;
							}
						}
					} else {
						$translated_array[] = $id;
					}
				}
				return $translated_array;
			} else {
				return $original_array;
			}
		} else {
			return $original_array;
		}
	}

	/**
	 * cancel default filtering when page on front
	 *
	 *
	 * @updated 2.6.3
	 * @param string option
	 */
	public function get_option_wo_xili( $option ) {
		if ( 'page_on_front' == $option && has_filter( 'option_' . $option, array( &$this, 'translate_page_on_front_id' ) ) ) { // 2.6.3
			remove_filter( 'option_' . $option, array( &$this, 'translate_page_on_front_id' ) );
			$value = get_option( $option );
			add_filter( 'option_' . $option, array( &$this, 'translate_page_on_front_id' ) );
		} else {
			$value = get_option( $option );
		}
		return $value;
	}

	/**
	 * if possible, translate the ID of front_page post
	 *
	 * @since 1.7.0
	 * called by hook option_page_on_front
	 *
	 */
	public function translate_page_on_front_id( $original_id ) {
		if ( ! taxonomy_exists( TAXONAME ) ) {
			return $original_id; // for very initial call
		}
		$this->xili_test_lang_perma(); // not detected in WP hook
		global $wp_query;
		if ( $this->lang_perma ) {
			if ( ! is_admin() && $this->show_page_on_front ) {
				if ( isset( $wp_query->query_vars[ QUETAG ] ) && in_array( $this->lang_qv_slug_trans( $wp_query->query_vars[ QUETAG ] ), array_keys( $this->show_page_on_front_array ) ) && '' != $wp_query->query_vars['page_id'] ) {
					$curlang = $this->lang_qv_slug_trans( $wp_query->query_vars[ QUETAG ] );

				} elseif ( isset( $wp_query->query_vars[ QUETAG ] ) && in_array( $this->lang_qv_slug_trans( $wp_query->query_vars[ QUETAG ] ), array_keys( $this->show_page_on_front_array ) ) ) {

					$curlang = $this->lang_qv_slug_trans( $wp_query->query_vars[ QUETAG ] ); // to verify

				} else {
					$curlang = $this->choice_of_browsing_language(); // rule defined in admin UI
				}
				$trans_id = $this->linked_post_in( $original_id, $curlang );

				if ( '' != $trans_id ) {
					return $trans_id;
				} else {
					return $original_id;
				}
			} else {
				return $original_id;
			}
		} else {

			if ( ! is_admin() && $this->show_page_on_front ) {
				if ( isset( $wp_query->query_vars[ QUETAG ] ) ) {
					// $this_curlang is not yet set
					$curlang = $this->lang_qv_slug_trans( $wp_query->query_vars[ QUETAG ] );
				} else {
					$curlang = $this->choice_of_browsing_language( '2760' ); // rule defined in admin UI
				}

				$trans_id = $this->linked_post_in( $original_id, $curlang );

				if ( '' != $trans_id ) {
					return $trans_id;
				} else {
					return $original_id;
				}
			} else {
				return $original_id;
			}
		}
	}

	/**
	 * blog page id (page for posts container)
	 * @since 2.8.4
	 *
	 */
	public function translate_page_for_posts_id( $original_id ) {
		if ( empty( $original_id ) ) {
			return $original_id; // 2.8.4.1
		}
		global $wp_query;

		if ( $this->is_permalink ) {
			if ( isset( $wp_query->query_vars['pagename'] ) && in_array( $wp_query->query_vars['pagename'], $this->page_for_posts_name_array ) ) {

				$wp_query->is_page = false;
				$wp_query->is_home = true;
				$wp_query->is_posts_page = true;

				return $this->page_for_posts_name_to_id_array[ $wp_query->query_vars['pagename'] ];

			} else {

				return $original_id;
			}
		} else { // no permalinks
			if ( isset( $wp_query->query_vars['page_id'] ) && in_array( $wp_query->query_vars['page_id'], $this->page_for_posts_array ) ) {

				$wp_query->is_page = false;
				$wp_query->is_home = true;
				$wp_query->is_posts_page = true;

				return $wp_query->query_vars['page_id'];

			} else {

				return $original_id;

			}
		}
	}

	/**
	 * List custom post types $type != 'attachment' &&
	 *
	 * @since 1.8.0
	 *
	 */
	public function get_custom_desc() {
		$types = get_post_types( array( 'show_ui' => 1 ) );
		if ( count( $types ) > 2 ) {
			$thecheck = array();
			$thecustoms = array();

			foreach ( $types as $type ) {
				$true_xd = ( defined( 'XDMSG' ) ) ? ( XDMSG != $type ) : true; // exclude xdmsg of xili-dictionary
				if ( ! in_array( $type, array( 'attachment', 'post', 'page', 'wp_block' ) ) && true == $true_xd ) { // temporary WP 3.5 attachement UI
					$custom = get_post_type_object( $type );
					$clabels = $custom->labels;
					$thecustoms[ $type ] = array(
						'name' => $custom->label,
						'singular_name' => $clabels->singular_name,
						'multilingual' => '',
					);
				}
			}
			return $thecustoms;
		}
	}

	/**
	 * unassign a language from a series of objects (post or link)
	 * @since 1.8.8
	 *
	 *
	 */
	public function multilingual_links_erase( $lang_term_id ) {
		$languages = $this->get_listlanguages();

		foreach ( $languages as $language ) {
			if ( $language->term_id == $lang_term_id ) {
				$lang_slug = $language->slug;
				continue;
			}
		}
		foreach ( $languages as $language ) {
			// for other languages as this - delete postmeta linked to post of erased posts
			if ( $language->term_id != $lang_term_id ) {
				$post_ids = get_objects_in_term( array( $language->term_id ), array( TAXONAME ) );
				foreach ( $post_ids as $post_id ) {
						delete_post_meta( $post_id, QUETAG . '-' . $lang_slug );
				}
			}
		}
		// posts
		$post_ids = get_objects_in_term( array( $lang_term_id ), array( TAXONAME ) );
		foreach ( $post_ids as $post_id ) {
			// delete relationships posts
			wp_delete_object_term_relationships( $post_id, TAXONAME );
		}
		// links of blogroll
		$links = get_objects_in_term( array( $lang_term_id ), array( 'link_' . TAXONAME ) );
		foreach ( $links as $link ) {
			wp_delete_object_term_relationships( $link, 'link_' . TAXONAME );
		}
	}

	/**
	 * Set language plugin
	 *
	 *
	 */
	public function init_plugin_textdomain() {
		load_plugin_textdomain( 'xili-language', false, 'xili-language/languages' );
	}

	// also include automatic search of domain and lang subfolder in current theme
	public function init_theme_textdomain() {
		/* in wp3 multisite - don't use constant - for backward compatibility keep it in mono*/
		if ( '' != $this->xili_settings['theme_domain'] ) {
			if ( ! is_multisite() && ! defined( 'THEME_TEXTDOMAIN' ) ) {
				define( 'THEME_TEXTDOMAIN', $this->thetextdomain ); // for backward compatibility;
			}
			if ( is_admin() ) {
				$do = ( $this->xili_settings['theme_domain'] != $this->thetextdomain ) ? true : false;
				if ( $do ) {
					$this->xili_settings['theme_domain'] = $this->thetextdomain;
					update_option( 'xili_language_settings', $this->xili_settings );
				}
			}
		} else {
			$this->domaindetectmsg = __( 'no load_theme_textdomain in functions.php', 'xili-language' );
		}
	}

	// deprecated
	public function searchpath( $path, $filename ) {
		$this->xili_settings['langs_folder'] = str_replace( $this->get_template_directory, '', $path );
	}

	/**
	 * Reset values when theme was changed... updated by previous function
	 * @since 1.1.9
	 */
	public function theme_switched( $theme ) {
		$this->xili_settings['langs_folder'] = '';
		$this->xili_settings['theme_domain'] = ''; /* to force future search in new theme */

		$this->recover_alias_settings_of_previous_used_theme( $theme ); // 2.11.1

		update_option( 'xili_language_settings', $this->xili_settings );
	}

	/**
	 * link of linked post
	 *
	 * @since 2.1.0
	 *
	 * @updated 2.15.4
	 */
	public function link_of_linked_post( $fromid, $lang_slug ) {
		$targetpost = $this->linked_post_in( $fromid, $lang_slug );
		if ( $targetpost && 'publish' == get_post_status( $targetpost ) ) { // 2.15.4
			return get_permalink( $targetpost );
		} else {

			$language_qv = $this->lang_slug_qv_trans( $lang_slug );
			$link = ( $this->lang_perma ) ? str_replace( '%lang%', $language_qv, get_bloginfo( 'url' ) . '/%lang%/' ) :
			add_query_arg(
				array(
					QUETAG => $language_qv,
				),
				get_bloginfo( 'url' )
			);
			return $link;
		}
	}

	/**
	 * For widget - the list of options above
	 * @since 1.6.0
	 * obsolete
	 */
	public function xili_language_list_options() {
		$this->langs_list_options = array( array( '', 'default' ), array( 'typeone', 'Type n°1' ), array( 'typeonenew', 'Type for single' ) );
	}


	/**
	 * Enable to add functions and filters that are not in theme's functions.php
	 * These filters are common even if you change default theme...
	 * Place your functions.php in folder plugins/xilidev-libraries/
	 * if you have a filter in this file, avoid to have similar one in functions.php of the theme !!!
	 *
	 */
	public function insert_gold_functions() {
		$gold_path = WP_PLUGIN_DIR . $this->xilidev_folder; /* since 1.0 to add xili-libraries */
		if ( '' != $this->xili_settings['functions_enable'] && file_exists( $gold_path . '/functions.php' ) ) {
			include_once $gold_path . '/functions.php';
		}
	}


} /* **************** end of xili-language class ******************* */
