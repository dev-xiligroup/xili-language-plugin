<?php
namespace Xili_Admin;

/**
 * @package Xili-Language
 * @subpackage admin
 * functions for frontend settings page
 * @since  2.23 traits files
 */

trait Xili_Admin_Page_Authoring_Rules_Settings {

	/**
	 * Settings by experts and info
	 * @since 2.4.1
	 */
	public function on_load_page_author_rules() {
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'wp-lists' );
			wp_enqueue_script( 'postbox' );

			add_meta_box( 'xili-language-sidebox-theme', __( 'Current theme infos', 'xili-language' ), array( &$this, 'on_sidebox_5_theme_info' ), $this->thehook6, 'side', 'high' );

			add_meta_box( 'xili-language-sidebox-info', __( 'Info', 'xili-language' ), array( &$this, 'on_sidebox_info_content' ), $this->thehook6, 'side', 'core' );

			$this->insert_news_pointer( 'page_author_rules' ); // news pointer 2.12.1
	}

	/**
	 * Authoring rules page
	 *
	 * @since 2.12.0
	 */
	public function author_rules() {

		$themessages = array( 'ok' );
		$emessage = '';
		$action = '';

		$data = array(
			'action' => $action,
			'emessage' => $emessage,
		);
		add_meta_box( 'xili-language-authoring-settings', __( 'Authoring and data settings', 'xili-language' ), array( &$this, 'on_box_authoring_settings' ), $this->thehook6, 'normal', 'low' );
		add_meta_box( 'xili-language-author-rules', __( 'Authoring rules', 'xili-language' ), array( &$this, 'on_box_author_rules' ), $this->thehook6, 'normal', 'low' );

		add_settings_section( 'option_section_1', __( 'Propagation options', 'xili-language' ), array( $this, 'display_one_section' ), $this->settings_author_rules . '_group' );

		foreach ( $this->propagate_options_labels as $one_key => $one_option ) {

			if ( $this->propagate_options_default[ $one_key ]['hidden'] ) {

				$field_args = array(
					'option_name'   => $this->settings_author_rules,
					'title'         => $one_option['name'],
					'type'          => 'hidden',
					'id'            => $one_key,
					'name'          => $one_key,
					'desc'          => $one_option['description'],
					'std'           => '1',
					'class'         => 'css_class propagate',
				);

			} else {
				$field_args = array(
					'option_name'   => $this->settings_author_rules,
					'title'         => $one_option['name'],
					'type'          => 'checkbox',
					'id'            => $one_key,
					'name'          => $one_key,
					'desc'          => $one_option['description'],
					'std'           => '1',
					'label_for'     => $one_key,
					'class'         => 'css_class propagate',
				);
			}

			add_settings_field( $field_args['id'], $field_args['title'], array( $this, 'display_one_setting' ), $this->settings_author_rules . '_group', 'option_section_1', $field_args );

		}
		// Authoring settings (grouped from previous other tabs 2 and 3)
		add_settings_section( 'option_section_settings_1', __( 'Authoring settings', 'xili-language' ), array( $this, 'display_one_section' ), $this->settings_authoring_settings . '_group' );

		$authoring_language_options = array(
			'no' => __( 'No default language', 'xili-language' ),
			'authorbrowser' => __( 'Browser language', 'xili-language' ),
			'authordashboard' => __( 'Dashboard language', 'xili-language' ),
		);

		// authorbrowseroption
		$field_args = array(
			'option_name' => $this->settings_authoring_settings,
			'title' => __( 'Default language of a new created post', 'xili-language' ),
			'type' => 'select',
			'id' => 'authorbrowseroption',
			'name' => 'authorbrowseroption',
			'desc' => __( 'When creating a very new post, choose the default language assigned to this post.', 'xili-language' ),
			'std' => 'no',
			'label_for' => 'authorbrowseroption',
			'class' => 'css_class settings',
			'option_values' => $authoring_language_options,
		);

		add_settings_field( $field_args['id'], $field_args['title'], array( $this, 'display_one_setting' ), $this->settings_authoring_settings . '_group', 'option_section_settings_1', $field_args );

		// creation_redirect
		$field_args = array(
			'option_name' => $this->settings_authoring_settings,
			'title' => __( 'Redirect to created post', 'xili-language' ),
			'type' => 'checkbox',
			'id' => 'creation_redirect',
			'name' => 'creation_redirect',
			'desc' => __( 'After creating a linked post in other language, the Edit post is automatically displayed.', 'xili-language' ),
			'std' => 'redirect',
			'label_for' => 'creation_redirect',
			'class' => 'css_class settings',
		);
		add_settings_field( $field_args['id'], $field_args['title'], array( $this, 'display_one_setting' ), $this->settings_authoring_settings . '_group', 'option_section_settings_1', $field_args );

		// external_xl_style
		if ( ! $this->exists_style_ext ) {
			$style_state = __( 'There is no style for dashboard', 'xili-language' ) . ' (' . $this->style_message . ' ) ';
		} else {
			$style_state = $this->style_message;
		}
		$field_args = array(
			'option_name' => $this->settings_authoring_settings,
			'title' => __( 'Activate xl-style.css', 'xili-language' ),
			'type' => 'checkbox',
			'id' => 'external_xl_style',
			'name' => 'external_xl_style',
			/* translators: */
			'desc' => sprintf( __( 'Dashboard style: %s', 'xili-language' ), $style_state ),
			'std' => 'on',
			'label_for' => 'external_xl_style',
			'class' => 'css_class settings',
		);
		add_settings_field( $field_args['id'], $field_args['title'], array( $this, 'display_one_setting' ), $this->settings_authoring_settings . '_group', 'option_section_settings_1', $field_args );

		// custom post type
		$types = get_post_types( array( 'show_ui' => 1 ) );
		if ( count( $types ) > 2 ) {
			$thecheck = array();
			$thecustoms = $this->get_custom_desc();
			if ( count( $thecustoms ) > 0 ) {
				foreach ( $thecustoms as $type => $thecustom ) {
					$thecheck[] = $type;
				}
				$clabel = implode( ', ', $thecheck );

				add_settings_section( 'option_section_settings_2', __( 'Custom post authoring multilingual rules', 'xili-language' ), array( $this, 'display_one_section' ), $this->settings_authoring_settings . '_group' );

				$customs_options = $this->xili_settings['multilingual_custom_post'];
				foreach ( $thecustoms as $type => $thecustom ) {
					//$customs_enable = ( isset($customs_options[$type]) ) ? $customs_options[$type]['multilingual'] : '';
					$field_args = array(
						'option_name' => $this->settings_authoring_settings,
						'title' => $thecustom['name'],
						'type' => 'checkbox',
						'id' => 'cpt_' . $type,
						'name' => 'cpt_' . $type,
						/* translators: */
						'desc' => sprintf( __( 'Custom post type named: %s', 'xili-language' ), $thecustom ['name'] ),
						'std' => 'enable',
						'label_for' => 'cpt_' . $type,
						'class' => 'css_class settings',
					);
					add_settings_field( $field_args['id'], $field_args['title'], array( $this, 'display_one_setting' ), $this->settings_authoring_settings . '_group', 'option_section_settings_2', $field_args );
				}
			}
		}

		// bookmarks sub selection in links widget

		add_settings_section( 'option_section_settings_3', __( 'Sub-selection in bookmarks widget', 'xili-language' ), array( $this, 'display_one_section' ), $this->settings_authoring_settings . '_group' );

		// xili_language_link_cat_all
		$field_args = array(
			'option_name' => $this->settings_authoring_settings,
			'title' => __( 'All Links', 'xili-language' ),
			'type' => 'checkbox',
			'id' => 'link_cat_all',
			'name' => 'link_cat_all',
			'desc' => __( 'If checked, all bookmarks will be subselected according current language.', 'xili-language' ),
			'std' => '1',
			'label_for' => 'link_cat_all',
			'class' => 'css_class settings',
		);
		add_settings_field( $field_args['id'], $field_args['title'], array( $this, 'display_one_setting' ), $this->settings_authoring_settings . '_group', 'option_section_settings_3', $field_args );

		$link_cats = get_terms( 'link_category' );

		if ( $link_cats ) {
			foreach ( $link_cats as $link_cat ) {

				$field_args = array(
					'option_name' => $this->settings_authoring_settings,
					'title' => $link_cat->name,
					'type' => 'checkbox',
					'id' => 'link_cat_' . $link_cat->term_id,
					'name' => 'link_cat_' . $link_cat->term_id,
					/* translators: */
					'desc' => sprintf( __( 'If checked, %s bookmark will be subselected according current language.', 'xili-language' ), $link_cat->name ),
					'std' => '1',
					'label_for' => 'link_cat_' . $link_cat->term_id,
					'class' => 'css_class settings',
				);
				add_settings_field( $field_args['id'], $field_args['title'], array( $this, 'display_one_setting' ), $this->settings_authoring_settings . '_group', 'option_section_settings_3', $field_args );

			}
		}

		//
		add_settings_section( 'option_section_settings_5', __( 'Option to define widget visibility', 'xili-language' ), array( $this, 'display_one_section' ), $this->settings_authoring_settings . '_group' );
		if ( current_theme_supports( 'widgets' ) ) {
			$field_args = array(
				'option_name' => $this->settings_authoring_settings,
				'title' => __( 'Option Enabled', 'xili-language' ),
				'type' => 'checkbox',
				'id' => 'widget_visibility',
				'name' => 'widget_visibility',
				'desc' => __( 'If checked, each widget settings form includes rules to define visibility in front-end.', 'xili-language' ),
				'std' => '1',
				'label_for' => 'widget_visibility',
				'class' => 'css_class settings',
			);
			add_settings_field( $field_args['id'], $field_args['title'], array( $this, 'display_one_setting' ), $this->settings_authoring_settings . '_group', 'option_section_settings_5', $field_args );
		}
		//
		add_settings_section( 'option_section_settings_4', __( 'Other settings (Widgets)', 'xili-language' ), array( $this, 'display_one_section' ), $this->settings_authoring_settings . '_group' );
		if ( current_theme_supports( 'widgets' ) ) {
			$field_args = array(
				'option_name' => $this->settings_authoring_settings,
				'title' => __( 'Enable widgets', 'xili-language' ),
				'type' => 'checkbox',
				'id' => 'widget',
				'name' => 'widget',
				'desc' => __( 'If checked, selected xili-language widgets below will be available.', 'xili-language' ),
				'std' => 'enable',
				'label_for' => 'widget',
				'class' => 'css_class settings',
			);

			add_settings_field( $field_args['id'], $field_args['title'], array( $this, 'display_one_setting' ), $this->settings_authoring_settings . '_group', 'option_section_settings_4', $field_args );
			$widgets = array_keys( $GLOBALS['wp_widget_factory']->widgets ); // 2.17.1
			foreach ( $this->xili_settings['specific_widget'] as $key => $value ) {

				$setbytheme = ( '' == $value['value'] && in_array( $key, $widgets ) ) ? '<small>(*)</small>' : '';

				$field_args = array(
					'option_name' => $this->settings_authoring_settings,
					/* translators: */
					'title' => sprintf( __( 'Widget: %s', 'xili-language' ), xl__( $value['name'], 'xili-language' ) ),
					'type' => 'checkbox',
					'id' => 'specific_widget_' . $key,
					'name' => 'specific_widget_' . $key,
					/* translators: */
					'desc' => sprintf( __( 'Checked, this widget of class %1$s is available. %2$s', 'xili-language' ), $key, $setbytheme ),
					'std' => 'enabled',
					'label_for' => 'specific_widget_' . $key,
					'class' => 'css_class settings widget',
				);
				add_settings_field( $field_args['id'], $field_args['title'], array( $this, 'display_one_setting' ), $this->settings_authoring_settings . '_group', 'option_section_settings_4', $field_args );
			}
		}

		if ( file_exists( WP_PLUGIN_DIR . $this->xilidev_folder ) ) {
			$field_args = array(
				'option_name' => $this->settings_authoring_settings,
				'title' => __( 'Enable gold functions', 'xili-language' ),
				'type' => 'checkbox',
				'id' => 'functions_enable',
				'name' => 'functions_enable',
				'desc' => __( 'If checked, available gold functions are activated.', 'xili-language' ),
				'std' => 'enable',
				'label_for' => 'functions_enable',
				'class' => 'css_class settings',
			);
			add_settings_field( $field_args['id'], $field_args['title'], array( $this, 'display_one_setting' ), $this->settings_authoring_settings . '_group', 'option_section_settings_4', $field_args );
		}

		?>
		<div id="xili-language-author-rules" class="wrap columns-2 minwidth">

			<h2><?php esc_html_e( 'Authoring rules', 'xili-language' ); ?></h2>
			<h3 class="nav-tab-wrapper">
			<?php $this->set_tabs_line(); ?>
			</h3>

			<p class="width23 boldtext">
			<?php printf( esc_html__( 'This settings screen contains new miscellaneous features.', 'xili-language' ), '<a href="' . $this->repositorylink . '" target="_blank">', '</a>' ); ?>
			</p>

			<?php $this->setting_form_content( $this->thehook6, $data ); ?>
		</div>
		<?php
		$this->setting_form_js( $this->thehook6 );
	}


	public function on_box_authoring_settings() {
		?>
		<div class="list-settings authoring_settings">
		<form name="authoring_settings" id="authoring_settings" method="post" enctype="multipart/form-data" action="options.php">
			<?php
			wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
			wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
			settings_fields( $this->settings_authoring_settings . '_group' ); // nonce, action (plugin.php)
			do_settings_sections( $this->settings_authoring_settings . '_group' );
			?>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php /* translators: */ printf( esc_html__( '%1$s of %2$s', 'xili-language' ), esc_html__( 'Save Changes' ), esc_html__( ' Authoring and data settings', 'xili-language' ) ); ?>" />
			</p>
			<div class="clearb1">&nbsp;</div>
		</form>
		</div>
		<?php
	}

	public function on_box_author_rules() {
		?>
		<div class="list-settings authoring-rules">
		<form name="author_rules" id="author_rules" method="post" enctype="multipart/form-data" action="options.php">
			<?php
			wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
			wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
			settings_fields( $this->settings_author_rules . '_group' ); // nonce, action (plugin.php)
			do_settings_sections( $this->settings_author_rules . '_group' );
			?>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php /* translators: */ printf( esc_html__( '%1$s of %2$s', 'xili-language' ), esc_html__( 'Save Changes' ), esc_html__( 'Authoring rules', 'xili-language' ) ); ?>" />
			</p>
			<div class="clearb1">&nbsp;</div>
		</form>
		</div>
		<?php

	}

	public function get_authoring_settings_options() {
		// not used yet
		//return get_option( $this->settings_authoring_settings, $this->get_default_authoring_settings_options() );
		$default_array = $this->get_default_authoring_settings_options();
		$values_array = array(
			'authorbrowseroption' => $this->xili_settings['authorbrowseroption'],
			'creation_redirect' => $this->xili_settings['creation_redirect'],
			'external_xl_style' => $this->xili_settings['external_xl_style'],
			'widget' => $this->xili_settings['widget'],
			'functions_enable' => $this->xili_settings['functions_enable'],
		);
		return ( array_merge( $default_array, $values_array ) );
	}

	public function get_default_authoring_settings_options() {
		return array(
			'authorbrowseroption' => 'no',
			'creation_redirect' => 'redirect',
			'external_xl_style' => 'on',
			'widget' => 'enable',
			'functions_enable' => '',
		);
	}

	public function get_theme_author_rules_options() {
		return get_option( $this->settings_author_rules, $this->get_default_theme_author_rules_options() );
	}

	public function get_default_theme_author_rules_options() {
		$propagate_option_default = array();

		if ( array() != $this->propagate_options_default ) {
			foreach ( $this->propagate_options_default as $key => $one_options ) {
				$propagate_option_default[ $key ] = $one_options['default'];
			}
		}
		//$arr = array_merge ( array( 'authoring_options_admin' => '' ), $propagate_option_default );
		return $propagate_option_default;
	}

	public function settings_authoring_settings_validate( $input ) {

		if ( isset( $input['authorbrowseroption'] ) ) {
			$this->xili_settings['authorbrowseroption'] = $input['authorbrowseroption'];
		}
		$this->xili_settings['creation_redirect'] = ( isset( $input['creation_redirect'] ) ) ? $input['creation_redirect'] : ''; // because checkbox
		$this->xili_settings['external_xl_style'] = ( isset( $input['external_xl_style'] ) ) ? $input['external_xl_style'] : 'off'; // because checkbox
		$this->xili_settings['widget'] = ( isset( $input['widget'] ) ) ? $input['widget'] : ''; // because checkbox
		$this->xili_settings['widget_visibility'] = ( isset( $input['widget_visibility'] ) ) ? $input['widget_visibility'] : ''; // because checkbox
		$specifics = array();
		foreach ( $this->xili_settings['specific_widget'] as $key => $value ) {
			$specifics[ $key ]['name'] = $value['name'];
			$specifics[ $key ]['value'] = ( isset( $input[ 'specific_widget_' . $key ] ) ) ? $input[ 'specific_widget_' . $key ] : '';
		}
		$this->xili_settings['specific_widget'] = $specifics; // 2.16.4
		$this->xili_settings['functions_enable'] = ( isset( $input['functions_enable'] ) ) ? $input['functions_enable'] : ''; // because checkbox
		$thecustoms = $this->get_custom_desc();
		if ( count( $thecustoms ) > 0 ) {
			foreach ( array_keys( $thecustoms ) as $cpt ) {
				if ( isset( $input[ 'cpt_' . $cpt ] ) ) { // because checkbox
					$this->xili_settings['multilingual_custom_post'][ $cpt ] = $thecustoms[ $cpt ]; // fixed 2.13.2 for new CPT
					$this->xili_settings['multilingual_custom_post'][ $cpt ]['multilingual'] = $input[ 'cpt_' . $cpt ];
				} else {
					$this->xili_settings['multilingual_custom_post'][ $cpt ]['multilingual'] = '';
				}
			}
		} else {
			$this->xili_settings['multilingual_custom_post'] = array();
		}

		$this->xili_settings['link_categories_settings']['all'] = ( isset( $input['link_cat_all'] ) ) ? $input['link_cat_all'] : ''; // because checkbox
		$link_cats = get_terms( 'link_category' );

		if ( $link_cats ) {
			foreach ( $link_cats as $link_cat ) {
				$this->xili_settings['link_categories_settings']['category'][ $link_cat->term_id ] = ( isset( $input[ 'link_cat_' . $link_cat->term_id ] ) ) ? $input[ 'link_cat_' . $link_cat->term_id ] : ''; // because checkbox
			}
		}
		update_option( 'xili_language_settings', $this->xili_settings ); // based on original settings
		return $input; // redundant if no filter
	}

	public function author_rules_validate_settings( $input ) {
		foreach ( $input as $id => $v ) {
			$newinput[ $id ] = trim( $v );
		}
		if ( ! isset( $input['authoring_options_admin'] ) ) {
			$newinput['authoring_options_admin'] = '';
		}
		$keys = array_keys( $this->propagate_options_default );
		foreach ( $keys as $key ) {
			if ( ! isset( $input[ $key ] ) ) {
				$newinput[ $key ] = '';
			}
		}

		return $newinput;
	}

	public function display_one_section( $section ) {
		switch ( $section['id'] ) {
			case 'option_section_1':
				echo '<p class="section">' . esc_html__( 'When authors of post, page and custom post want to create a translation, it is possible to define what feature of original post can be copied to the post of target language (format, parent, comment or ping status,...). Some features are not ajustable (to be, it will be need premium services). For developer only: filters are available.', 'xili-language' ) . '</p>';
				break;

			case 'option_section_settings_1':
				echo '<p class="section">' . esc_html__( 'This settings screen contains new miscellaneous features to define or help authoring.', 'xili-language' ) . '</p>';
				break;

			case 'option_section_settings_2':
				$thecustoms = $this->get_custom_desc();
				$thecheck = array();
				foreach ( $thecustoms as $type => $thecustom ) {
					$thecheck[] = $type;
				}
				$clabel = implode( ', ', $thecheck );
				/* translators: */
				$text = ( 1 == count( $thecustoms ) ) ? sprintf( __( 'One custom post (%s) is available.', 'xili-language' ), $clabel ) : sprintf( __( 'More than one custom post (%s) are available.', 'xili-language' ), $clabel );
				echo '<p class="section">' . $text . '</p>';
				echo '<p class="section">' . __( 'Check to define as multilingual (a translation box will appear in edit page).', 'xili-language' ) . '</p>';
				break;

			case 'option_section_settings_3':
				echo '<p class="section">' . __( 'Check the bookmark\'s categories where to enable multilanguage features.', 'xili-language' ) . '</p>';
				break;
			case 'option_section_settings_4':
				echo '<p class="section">' . __( 'Define here the widget(s) visible in Appearance. If visibility is set inside theme source code, a * is visible.', 'xili-language' ) . '</p>';
				break;
			case 'option_section_settings_5': // 2.20.3
				echo '<p class="section">' . __( 'Set here if an option to set <em>visibility rules according language</em> will be inserted in each widget form of Appearance/Widgets settings page (and Customize page).', 'xili-language' );
				if ( class_exists( 'jetpack' ) ) {
					$modules_array = get_option( 'jetpack_active_modules', true );
					if ( $modules_array && in_array( 'widget-visibility', $modules_array ) ) {
						echo '<br />' . __( 'The module - Widget visibility - of Jetpack is active. Language rules can overwrite Jetpack visibility rules!', 'xili-language' );
					}
				}
				echo '</p>';
				break; // admin.php?page=jetpack_modules

			case 'option_front_section_1':
				echo '<p class="section">' . __( 'Here select language of the home webpage', 'xili-language' ) . '</p>';
				echo '<p class="section"><em>' . sprintf( __( 'As set in <a href="%1$s">%2$s</a>, the home webpage is', 'xili-language' ), 'options-reading.php', __( 'Reading' ) ) . '&nbsp;';
				if ( $this->show_page_on_front ) {
					printf( __( 'a static <a href="%1$s">page</a>.', 'xili-language' ), 'edit.php?post_type=page' );
					$page_for_posts = get_option( 'page_for_posts' );
					if ( ! empty( $page_for_posts ) ) {
						echo '&nbsp;' . __( 'Another page is set to display the latest posts (in default theme).', 'xili-language' );
					}
				} else {
					esc_html_e( 'set as to display the latest posts (in default theme).', 'xili-language' );
				}
				echo '</em></p>';
				break;

			case 'option_front_section_2':
				echo '<p class="section">' . esc_html__( 'Here select language of the theme items when a category is displayed without language sub-selection', 'xili-language' ) . '</p>';
				break;

			case 'xili_flag_section_1':
				echo '<p class="section">' . esc_html__( 'Here define if flags style in language selector (switcher) navigation menu', 'xili-language' ) . '</p>';
				break;

			case 'xili_flag_section_2':
				echo '<p class="section">' . esc_html__( 'Settings flags style for language selector (switcher) menu', 'xili-language' ) . '</p>';
				break;

		}
	}

	/**
	 * one line in section
	 *
	 * @updated 2.12.2 (notices)
	 */
	public function display_one_setting( $args ) {
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
					$options[ 'specific_widget_' . $key ] = $value['value'];
				}
				// CPT
				if ( false !== strpos( $id, 'cpt_' ) ) {
					$cpt = str_replace( 'cpt_', '', $id );
					$options[ $id ] = ( isset( $this->xili_settings['multilingual_custom_post'][ $cpt ]['multilingual'] ) ) ? $this->xili_settings['multilingual_custom_post'][ $cpt ]['multilingual'] : '';
				}
				// Bookmarks
				if ( false !== strpos( $id, 'link_cat_' ) ) {
					$link_cat_id = str_replace( 'link_cat_', '', $id );
					if ( 'all' == $link_cat_id ) {
						$options[ $id ] = ( isset( $this->xili_settings['link_categories_settings']['all'] ) ) ? $this->xili_settings['link_categories_settings']['all'] : '';
					} else {
						$options[ $id ] = ( isset( $this->xili_settings['link_categories_settings']['category'][ $link_cat_id ] ) ) ? $this->xili_settings['link_categories_settings']['category'][ $link_cat_id ] : '';
					}
				}

				break;

			case $this->settings_frontend:
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

			case $this->flag_settings_name:
				$options = $this->get_xili_flag_options();
				break;
		}

		switch ( $type ) {

			case 'message':
				echo ( '' != $desc ) ? "<span class='description'>$desc</span>" : '...';
				break;

			case 'text':
				$set = ( isset( $options[ $id ] ) ) ? $options[ $id ] : $std;
				$set = stripslashes( $set );
				$set = esc_attr( $set );
				$size_attr = ( isset( $size ) ) ? "size='$size'" : '';
				echo "<input $size_attr class='regular-text$class' type='text' id='$id' name='" . $option_name . "[$id]' value='$set' />";
				echo ( '' != $desc ) ? "<br /><span class='description'>$desc</span>" : '';
				break;

			case 'hidden':
				$set = ( isset( $options[ $id ] ) ) ? $options[ $id ] : ( ( isset( $this->propagate_options_default[ $id ]['default'] ) ) ? $this->propagate_options_default[ $id ]['default'] : false );

				$val = ( $set ) ? '1' : '';
				echo "<input type='hidden' id='$id' name='" . $option_name . "[$id]' value='$val' />";

				echo ( '' != $desc && $set ) ? "<span class='description'>$desc</span>" : "<span class='description'>" . __( 'No propagation', 'xili-language' ) . '</span>';
				break;

			case 'checkbox':
				// take default if not previous saved
				switch ( $option_name ) {
					case $this->settings_author_rules: // multiple names (based on current theme)
						$set = ( isset( $options[ $id ] ) ) ? $options[ $id ] : ( ( isset( $this->propagate_options_default[ $id ]['default'] ) ) ? $this->propagate_options_default[ $id ]['default'] : false );
						break;
					default:
						$set = ( isset( $options[ $id ] ) ) ? $options[ $id ] : false;
						break;
				}

				$checked = checked( $set, $std, false );
				echo "<input $checked class='$class' type='checkbox' id='$id' name='" . $option_name . "[$id]' value='$std' />";
				echo ( '' != $desc ) ? "<br /><span class='description'>$desc</span>" : '';
				break;

			case 'select':
				$set = ( isset( $options[ $id ] ) ) ? $options[ $id ] : false;

				echo "<select id='$id' name='" . $option_name . "[$id]' />";

				foreach ( $option_values as $value => $content ) {
					echo "<option value='$value' " . selected ( $set, $value , false ) . ">$content</option>";
				}
				echo '</select>';
				echo ( '' != $desc ) ? "<br /><span class='description'>$desc</span>" : '';
				break;
		}
	}

}
