<?php
namespace Xili_Admin;

/**
 * @package Xili-Language
 * @subpackage admin
 * functions for frontend settings page
 * @since  2.23 traits files
 */

trait Xili_Admin_Page_Frontend_Settings {

	/**
	 * Manage settings of languages behaviour in front-end (theme)
	 * @since 2.4.1
	 */
	public function on_load_page_set() {
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'wp-lists' );
			wp_enqueue_script( 'postbox' );

			add_meta_box( 'xili-language-sidebox-theme', __( 'Current theme infos', 'xili-language' ), array( &$this, 'on_sidebox_4_theme_info' ), $this->thehook2, 'side', 'high' );
			add_meta_box( 'xili-language-sidebox-info', __( 'Info', 'xili-language' ), array( &$this, 'on_sidebox_info_content' ), $this->thehook2, 'side', 'core' );

			$this->insert_news_pointer( 'frontend_settings' ); // news pointer 2.6.2
			$this->insert_news_pointer( 'languages_theme_infos' );
	}

	// new UI for frontend - 2.12

	public function set_frontend_settings_fields() {
		register_setting( $this->settings_frontend . '_group', $this->settings_frontend, array( &$this, 'settings_frontend_validate_settings' ) );
	}

		/**
	 * Settings page for front-end features
	 *
	 * @since 2.12.0
	 */
	public function frontend_settings() {
		$themessages = array( 'ok' );
		$emessage = '';
		$action = '';

		$data = array(
			'action' => $action,
			'emessage' => $emessage,
		);
		add_meta_box( 'xili-language-frontend-settings', __( 'Front-end behaviour settings', 'xili-language' ), array( &$this, 'on_box_frontend_settings' ), $this->thehook2, 'normal', 'low' );

		add_settings_section( 'option_front_section_1', __( 'Frontpage (home) options', 'xili-language' ), array( $this, 'display_one_section' ), $this->settings_frontend . '_group' );

		// browseroption
		$frontend_language_options = array(
			'' => __( 'Software defined', 'xili-language' ),
			'browser' => __( "Language of visitor's browser", 'xili-language' ),
		);
		$listlanguages = get_terms_of_groups_lite( $this->langs_group_id, TAXOLANGSGROUP, TAXONAME, 'ASC' );
		foreach ( $listlanguages as $language ) {
			$frontend_language_options[ $language->slug ] = xl__( $language->description, 'xili-language' );
		}

		$field_args = array(
			'option_name'   => $this->settings_frontend,
			'title'         => __( 'Language of the home webpage', 'xili-language' ),
			'type'          => 'select',
			'id'            => 'browseroption',
			'name'          => 'browseroption',
			'desc'          => __( 'Here select how or what will be language of the home webpage when a visitor is coming.', 'xili-language' ),
			'std'           => 'browser',
			'label_for'     => 'browseroption',
			'class'         => 'css_class settings',
			'option_values' => $frontend_language_options,
		);

		add_settings_field( $field_args['id'], $field_args['title'], array( $this, 'display_one_setting' ), $this->settings_frontend . '_group', 'option_front_section_1', $field_args );

		// lang_neither_browser select
		if ( 'browser' == $this->xili_settings['browseroption'] ) {

			$not_found_language_options = array(
				'' => __( 'Language of dashboard', 'xili-language' ),
			);
			foreach ( $listlanguages as $language ) {
				$not_found_language_options[ $language->slug ] = xl__( $language->description, 'xili-language' );
			}

			$field_args = array(
				'option_name'   => $this->settings_frontend,
				'title'         => __( 'if language is not found', 'xili-language' ),
				'type'          => 'select',
				'id'            => 'lang_neither_browser',
				'name'          => 'lang_neither_browser',
				'desc'          => __( 'Here select what will be language of the home webpage when the language of the browser is not available inside website.', 'xili-language' ),
				'std'           => '',
				'label_for'     => 'lang_neither_browser',
				'class'         => 'css_class settings',
				'option_values' => $not_found_language_options,
			);

			add_settings_field( $field_args['id'], $field_args['title'], array( $this, 'display_one_setting' ), $this->settings_frontend . '_group', 'option_front_section_1', $field_args );
		}

		// homelang checkbox
		if ( ! $this->show_page_on_front ) {

			$field_args = array(
				'option_name'   => $this->settings_frontend,
				'title'         => __( 'Modify home query', 'xili-language' ),
				'type'          => 'checkbox',
				'id'            => 'homelang',
				'name'          => 'homelang',
				'desc'          => __( 'If checked, latest posts will be selected according current language.', 'xili-language' ),
				'std'           => 'modify',
				'label_for'     => 'homelang',
				'class'         => 'css_class settings',
			);

			add_settings_field( $field_args['id'], $field_args['title'], array( $this, 'display_one_setting' ), $this->settings_frontend . '_group', 'option_front_section_1', $field_args );
		}

		// pforp_select select
		$page_for_posts = get_option( 'page_for_posts' );
		if ( $page_for_posts ) {
			$page_for_posts_options = array(
				'no-select' => __( 'No selection of latest posts', 'xili-language' ),
				'select' => __( 'Selection of latest posts', 'xili-language' ),
			);

			$field_args = array(
				'option_name' => $this->settings_frontend,
				'title' => __( 'In list inside (blog)page', 'xili-language' ),
				'type' => 'select',
				'id' => 'pforp_select',
				'name' => 'pforp_select',
				'desc' => __( 'Here decide (or not) subselection of latest posts according current language.', 'xili-language' ),
				'std' => 'select',
				'label_for' => 'pforp_select',
				'class' => 'css_class settings',
				'option_values' => $page_for_posts_options,
			);

			add_settings_field( $field_args['id'], $field_args['title'], array( $this, 'display_one_setting' ), $this->settings_frontend . '_group', 'option_front_section_1', $field_args );
		}

		add_settings_section( 'option_front_section_3', __( 'Navigation menus options', 'xili-language' ), array( $this, 'display_one_section' ), $this->settings_frontend . '_group' );

		$field_args = array(
			'option_name'  => $this->settings_frontend,
			'title'  => __( 'Home menu item with sub-selection by language.', 'xili-language' ),
			'type' => 'checkbox',
			'id' => 'home_item_nav_menu',
			'name' => 'home_item_nav_menu',
			'desc' => __( 'If checked, link under home menu item with be completed by language for sub-selection.', 'xili-language' ),
			'std' => 'modify',
			'label_for' => 'home_item_nav_menu',
			'class' => 'css_class settings',
		);
		add_settings_field( $field_args['id'], $field_args['title'], array( $this, 'display_one_setting' ), $this->settings_frontend . '_group', 'option_front_section_3', $field_args );
		// categories
		add_settings_section( 'option_front_section_2', __( 'Categories options', 'xili-language' ), array( $this, 'display_one_section' ), $this->settings_frontend . '_group' );

		$categories_options = array(
			'' => __( 'Software defined', 'xili-language' ),
			'browser' => __( "Language of visitor's browser", 'xili-language' ),
			'firstpost' => __( 'Language of first post in loop', 'xili-language' ),
		);

		$field_args = array(
			'option_name'   => $this->settings_frontend,
			'title'         => __( 'Theme terms if category list', 'xili-language' ),
			'type'          => 'select',
			'id'            => 'allcategories_lang',
			'name'          => 'allcategories_lang',
			'desc'          => __( "Theme's language when categories in 'all'", 'xili-language' ),
			'std'           => 'browser',
			'label_for'     => 'allcategories_lang',
			'class'         => 'css_class settings',
			'option_values' => $categories_options,
		);

		add_settings_field( $field_args['id'], $field_args['title'], array( $this, 'display_one_setting' ), $this->settings_frontend . '_group', 'option_front_section_2', $field_args );

		?>
		<div id="xili-language-frontend" class="wrap columns-2 minwidth">

			<h2><?php esc_html_e( 'Front-end settings', 'xili-language' ); ?></h2>
			<h3 class="nav-tab-wrapper">
			<?php $this->set_tabs_line(); ?>
			</h3>

			<p class="width23 boldtext">
			<?php printf( __( 'This settings screen contains miscellaneous features to define behaviour in frontend side.', 'xili-language' ), '<a href="' . $this->repositorylink . '" target="_blank">', '</a>' ); ?>
			</p>

			<?php $this->setting_form_content( $this->thehook2, $data ); ?>
		</div>
		<?php
		$this->setting_form_js( $this->thehook2 );

	}

	public function on_box_frontend_settings() {
		?>
		<div class="list-settings frontend-settings">
		<form name="frontend_settings" id="frontend_settings" method="post" enctype="multipart/form-data" action="options.php">
			<?php
			wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
			wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
			settings_fields( $this->settings_frontend . '_group' ); // nonce, action (plugin.php)
			do_settings_sections( $this->settings_frontend . '_group' );
			?>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php /* translators: */ printf( __( '%1$s of %2$s', 'xili-language' ), __( 'Save Changes' ), __( 'Front-end behaviour settings', 'xili-language' ) ); ?>" />
			</p>
			<div class="clearb1">&nbsp;</div>
		</form>
		</div>
		<?php
	}

	public function get_frontend_settings_options() {
		// not used yet
		//return get_option( $this->settings_frontend, $this->get_default_frontend_settings_options() );
		$default_array = $this->get_default_frontend_settings_options();
		$values_array = array(
			'browseroption' => $this->xili_settings['browseroption'],
			'lang_neither_browser' => $this->xili_settings['lang_neither_browser'],
			'homelang' => $this->xili_settings['homelang'],
			'pforp_select' => $this->xili_settings['pforp_select'],
			'home_item_nav_menu' => $this->xili_settings['home_item_nav_menu'],

			'allcategories_lang' => $this->xili_settings['allcategories_lang'],
		);
		return ( array_merge( $default_array, $values_array ) );
	}

	public function get_default_frontend_settings_options() {
		return array(
			'browseroption' => 'browser',
			'lang_neither_browser' => '',
			'homelang' => '',
			'pforp_select' => 'select',
			'home_item_nav_menu' => '',
			'allcategories_lang' => 'browser',
		);
	}

	public function settings_frontend_validate_settings( $input ) {

		if ( isset( $input['browseroption'] ) ) {
			$this->xili_settings['browseroption'] = $input['browseroption'];
		}
		if ( isset( $input['lang_neither_browser'] ) ) {
			$this->xili_settings['lang_neither_browser'] = $input['lang_neither_browser'];
		}
		$this->xili_settings['homelang'] = ( isset( $input['homelang'] ) ) ? $input['homelang'] : ''; // because checkbox
		if ( isset( $input['pforp_select'] ) ) {
			$this->xili_settings['pforp_select'] = $input['pforp_select'];
		}
		$this->xili_settings['home_item_nav_menu'] = ( isset( $input['home_item_nav_menu'] ) ) ? $input['home_item_nav_menu'] : ''; // because checkbox
		if ( isset( $input['allcategories_lang'] ) ) {
			$this->xili_settings['allcategories_lang'] = $input['allcategories_lang'];
		}

		update_option( 'xili_language_settings', $this->xili_settings );  // based on original settings
		return $input;
	}


}
