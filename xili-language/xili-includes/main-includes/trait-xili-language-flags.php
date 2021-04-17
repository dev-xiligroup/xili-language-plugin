<?php
namespace Xili_Main;

/**
 * @package class-xili-language
 * functions for flags in front
 */
trait Xili_Language_Flags {

	/**
	 * return integer/array(id) of flags
	 *
	 * @since 2.15
	 * params $lang (empty = full series), 'admin' to detect admin_custom_xili_flag
	 *
	 * @return id or array
	 */
	public function get_flag_series( $lang = '', $admin = '' ) {
		$context = ( 'admin' == $admin ) ? 'admin_' : '';
		$transient_name = $context . 'get_flag_series';
		if ( false === ( $flag_series = get_transient( $transient_name ) ) ) {
			// test if in cache 2.16.4
			$query = array(
				'post_type' => 'attachment',
				'posts_per_page' => -1,

				'meta_query' => array(
					array(
						'key' => '_wp_attachment_context',
						'value' => $context . 'custom_xili_flag', // front or admin
						'compare' => '=',
					),
				),
			);

			$flags = get_posts( $query );
			$flag_series = array();
			if ( $flags ) {
				$flags_ids = wp_list_pluck( (array) $flags, 'ID' );
				$flags_ids = array_map( 'absint', $flags_ids );

				foreach ( $flags_ids as $flag_id ) {
					$attachment_post_language = get_cur_language( $flag_id, 'slug' );
					if ( '' != $attachment_post_language ) {
						$flag_series[ $attachment_post_language ] = $flag_id;
					}
				}
			}
			set_transient( $transient_name, $flag_series, 1000 * HOUR_IN_SECONDS );
		}

		if ( $lang ) {
			if ( isset( $flag_series[ $lang ] ) ) {
				return $flag_series[ $lang ];
			} else {
				return 0;
			}
		} else {
			return $flag_series;
		}
	}

	/**
	 * Reset transient of flags when attachment(s) changed (add, edit, delete - see xl-class-admin)
	 *
	 * @since 2.16.4
	 *
	 */
	public function xili_reset_transient_get_flag_series() {
		delete_transient( 'get_flag_series' );
		delete_transient( 'admin_get_flag_series' );
	}

	/**
	 * Insert style for flag in navigation menu
	 *
	 * called by action wp_head priority 12
	 *
	 * @since 2.15
	 * results are filterable by hook insert_xili_flag_css_in_header
	 */
	public function insert_xili_flag_css_in_header() {
		if ( ! current_theme_supports( 'custom_xili_flag' ) ) {
			return; // needs add_theme_support ( 'custom_xili_flag' ) if not bundled theme
		}
		$flag_options = $this->get_xili_flag_options();
		echo '<style type="text/css">' . "\n";
		if ( 'with-flag' == $flag_options['menu_with_flag'] ) {
			sprintf( "/* flag style added by xili-language v. %s */ \n", XILILANGUAGE_VER );

			// common lines
			$css_ul_nav_menu = ( '' != $flag_options['css_ul_nav_menu'] ) ? $flag_options['css_ul_nav_menu'] . ' ' : '';
			$output = $css_ul_nav_menu . 'li[class*="lang-"]:hover { ' . $flag_options['css_li_hover'] . ' }' . "\n";
			$output .= $css_ul_nav_menu . 'li[class*="lang-"] a {' . $flag_options['css_li_a'] . '}' . "\n";
			$output .= $css_ul_nav_menu . 'li[class*="lang-"] a:hover {' . $flag_options['css_li_a_hover'] . '}' . "\n";

			// loop lines / lang
			foreach ( $this->langs_ids_array as $slug => $id ) {
				$url = do_shortcode( "[xili-flag lang={$slug}]" );
				$output .= $css_ul_nav_menu . "li.lang-{$slug} a { background-image: url('{$url}') }\n";
				$output .= $css_ul_nav_menu . "li.lang-{$slug} a:hover { background-image: url('{$url}') !important;}\n";
			}

			echo apply_filters( 'insert_xili_flag_css_in_header', $output, $flag_options, $this->langs_ids_array );

		} else {
			sprintf( "/* no flag style added by xili-language v. %s */ \n", XILILANGUAGE_VER );
		}
		echo "</style>\n";
	}

	/**
	 * called by both side
	 */
	public function get_xili_flag_options() {
		return get_option( $this->flag_settings_name, $this->get_default_xili_flag_options() );
	}

	/**
	 * default array according bundled themes
	 * @since 2.15
	 *
	 * results are filterable by hook - get_default_xili_flag_options - to be adapted in customized theme
	 */
	public function get_default_xili_flag_options() {
		$current_parent_theme = get_option( 'template' ); // for child also !
		$default = array(
			'menu_with_flag' => '0',
			'css_ul_nav_menu' => 'ul.nav-menu',
			'css_li_hover' => 'background-color:#41a62a;',
			'css_li_a' => 'text-indent:-9999px; width:10px; background:transparent no-repeat center 19px; margin:0;',
			'css_li_a_hover' => 'background: no-repeat center 20px !important;',
		);

		switch ( $current_parent_theme ) {
			case 'twentyten':
				$default['css_ul_nav_menu'] = 'ul.menu';
				$default['css_li_hover'] = 'background-color:#333;';
				$default['css_li_a'] = 'text-indent:-9999px; width:24px; background:transparent no-repeat center 16px; padding:0 !important;';
				break;
			case 'twentyeleven':
				$default['css_ul_nav_menu'] = 'ul.menu';
				$default['css_li_hover'] = 'background-color:#efefef;';
				$default['css_li_a'] = 'text-indent:-9999px; width:24px; background:transparent no-repeat center 16px; padding:0 !important;';
				break;
			case 'twentytwelve':
				$default['css_li_hover'] = 'background-color:none;';
				$default['css_li_a'] = 'text-indent:-9999px; width:24px; background:transparent no-repeat center 16px; margin:0;';
				break;
			case 'twentythirteen':
				$default['css_li_hover'] = 'background-color:#ad9065;';
				break;
			case 'twentyfifteen':
				$default['css_li_hover'] = 'background-color:#f5f5f5; background:rgba(255,255,255,0.3);'; // transparency if possible
				$default['css_li_a'] = 'text-indent:30px; width:100%; background:transparent no-repeat 0 16px; margin:0;';
				$default['css_li_a_hover'] = 'background: no-repeat 0 17px !important;';
				break;
			case 'twentysixteen':
				$default['css_ul_nav_menu'] = 'ul.primary-menu';
				$default['css_li_hover'] = 'background-color:#f5f5f5;';
				$default['css_li_a'] = 'text-indent:-9999px; width:10px; background:transparent no-repeat center 16px; margin:0;';
				$default['css_li_a_hover'] = 'background: no-repeat center 17px !important;';
				break;
			case 'twentyseventeen': // 2.22.1
				$default['css_ul_nav_menu'] = 'ul.menu';
				$default['css_li_hover'] = 'background-color:#f5f5f5;';
				$default['css_li_a'] = 'text-indent:-9999px; width:10px; background:transparent no-repeat center 20px; margin:0;';
				$default['css_li_a_hover'] = 'background: no-repeat center 21px !important;';
				break;
			case 'twentynineteen': // 2.22.12
				$default['css_ul_nav_menu'] = 'ul.main-menu';
				$default['css_li_hover'] = 'background-color:#f5f5f5;';
				$default['css_li_a'] = 'display:inline-block; text-indent:-9998px; width:30px; background:transparent no-repeat center 13px; margin:0;';
				$default['css_li_a_hover'] = 'background: no-repeat center 21px !important;';
				break;
			case 'twentytwenty': // 2.23.14
				$default['css_ul_nav_menu'] = 'ul.primary-menu';
				$default['css_li_hover'] = 'background-color:#f5f5f5;';
				$default['css_li_a'] = 'display:inline-block; text-indent:-9998px; width:30px; background:transparent no-repeat center 1px; margin:0;';
				$default['css_li_a_hover'] = 'background: no-repeat center 2px !important;';
				break;
			case 'twentytwentyone': // 2.23.14
				$default['css_ul_nav_menu'] = 'ul.menu-wrapper';
				$default['css_li_hover'] = 'background-color:#d1e4dd;';
				$default['css_li_a'] = 'display:inline-block; text-indent:-9998px; width:30px; background:transparent no-repeat center 13px; margin:0;';
				$default['css_li_a_hover'] = 'background: no-repeat center 15px !important;';
				break;
			case 'twentyfourteen':
			default:
		}
		return apply_filters( 'get_default_xili_flag_options', $default, $current_parent_theme );
	}

	// used in admin settings but here for easy update content
	public function get_xili_flag_options_description() {
		return array(
			'menu_with_flag' => array(
				'title' => __( 'Menu with flags', 'xili-language' ),
				'description' => __( 'If checked and if flag images available  for each language in Medias table, navigation menu item will display image instead language name.', 'xili-language' )
				. '</br>' . __( 'If theme contains flags and if these flags are well registered inside args of “custom_xili_flag” theme_support function, a default flag is used for the target language. Marked with *.', 'xili-language' ),
			),
			'css_ul_nav_menu' => array(
				'title' => __( 'Navigation menu selector', 'xili-language' ),
				'description' => __( 'The css navigation menu selector (default as in twentyfourteen bundled theme).', 'xili-language' ),
			),
			'css_li_hover' => array(
				'title' => __( 'li:hover selector', 'xili-language' ),
				'description' => __( 'The css navigation menu selector when mouse is hover li.', 'xili-language' ),
			),
			'css_li_a' => array(
				'title' => __( 'a selector', 'xili-language' ),
				'description' => __( 'The css navigation menu selector (a) where flag is in background.', 'xili-language' ),
			),
			'css_li_a_hover' => array(
				'title' => __( 'a:hover selector', 'xili-language' ),
				'description' => __( 'The css navigation menu (a) selector when mouse moves hover).', 'xili-language' ),
			),
		);
	}

	// when theme activated (after setup)
	// 2.15.1
	// 2.23.15 add 2020 2021.
	/**
	 * [bundled_themes_support_flag description]
	 */
	public function bundled_themes_support_flag() {
		$current_parent_theme = get_option( 'template' );
		$current_theme = get_option( 'stylesheet' );
		if ( in_array( $current_parent_theme, array( 'twentyten', 'twentyeleven', 'twentytwelve', 'twentythirteen', 'twentyfifteen', 'twentysixteen', 'twentyseventeen', 'twentynineteen', 'twentytwenty', 'twentytwentyone' ) ) ) {
			add_theme_support( 'custom_xili_flag' ); // same name as used in context of image.
		}

		if ( in_array( $current_theme, array( 'twentyfourteen-xili', 'twentyfifteen-xili', 'twentysixteen-xili', 'twentyseventeen-xili', 'twentynineteen-xili', 'twentytwenty-xili', 'twentytwentyone-xili' ) ) ) {

			remove_theme_support( 'custom_xili_flag' );
			$args = array();
			$listlanguages = $this->get_listlanguages();

			foreach ( $listlanguages as $one_language ) {
				$path_root = get_stylesheet_directory();
				$assets = ( in_array( $current_theme, array( 'twentyseventeen-xili', 'twentynineteen-xili', 'twentytwenty-xili', 'twentytwentyone-xili' ) ) ) ? '/assets' : '';
				$path = '%2$s' . $assets . '/images/flags/' . $one_language->slug . '.png';

				if ( file_exists( sprintf( $path, '', $path_root ) ) ) {
					$args[ $one_language->slug ] = array(
						'path' => $path,
						'height' => 11,
						'width' => 16,
					);
				}
			}
			// path and size - %2$s = child theme
			/*
			'de_de'	=> array(
						'path' => '%2$s/images/flags/de_de.png',
						'height'				=> 16,
						'width'					=> 11),

			*/
			add_theme_support( 'custom_xili_flag', $args );
		}
	}

	/**
	 * Insert style for flag in widget xili-language list
	 *
	 * called by action xili_language_widgets_head ( in widgets file)
	 *
	 * @since 2.20.3
	 *
	 */
	public function xili_language_widgets_head_style( $style_lines ) {

		if ( current_theme_supports( 'custom_xili_flag' ) ) {
			$style_lines .= '<!--- Xili-language widgets loop css -->' . "\n"; // iteration
			$style_lines .= '<style type="text/css">';
			// same in widget function
			//$style_lines .= '.widget.xili-language_Widgets {margin-bottom:10px}'. "\n";
			//$style_lines .= '.xililanguagelist {list-style: none; margin:0}'. "\n";
			//$style_lines .= '.xililanguagelist li {display:inline-block;}'. "\n";
			//$style_lines .= '.xililanguagelist li a {display:block;}'. "\n";

			// loop lines / lang
			$i = 0;
			$loop_style_lines = '';
			foreach ( $this->langs_ids_array as $slug => $id ) {
				if ( 0 == $i ) {
					$img_infos = $this->xili_multilingual_flag(
						array(
							'lang' => $slug,
							'src' => 1,
						)
					); // return size values.
					$i++;
				}
				$url = do_shortcode( "[xili-flag lang={$slug}]" );
				if ( ! $url ) {
					// temporary search a file in plugin itself.
					$url = $this->plugin_url . '/xili-css/flags/' . $slug . '.png';
					if ( ! file_exists( $this->plugin_path . 'xili-css/flags/' . $slug . '.png' ) ) {
						$url = $this->plugin_url . '/xili-css/flags/xx_xx.png'; // show a dummy image.
					}
				}
				$loop_style_lines .= '.xililanguagelist ' . "li.lang-{$slug} a {background-image: url('{$url}') }\n";
				$loop_style_lines .= '.xililanguagelist ' . "li.lang-{$slug} a:hover {background-image: url('{$url}') !important;}\n";
			}
			// common lines.
			$style_lines .= '.xililanguagelist li[class*="lang-"]:hover {background-color:#f5f5f5;}' . "\n";
			$style_lines .= '.xililanguagelist li[class*="lang-"] a {background:transparent no-repeat center 1px; margin:0 1px;}' . "\n";
			if ( $img_infos ) {
				$style_lines .= '.xililanguagelist li[class*="lang-"] a {width:' . ( (int) $img_infos[1] + 2 ) . 'px; height:' . ( (int) $img_infos[2] + 2 ) . 'px;}' . "\n";
			} else { // no flags detected in theme or plugin..
				$style_lines .= '.xililanguagelist li[class*="lang-"] a {width:18px; height:13px;}' . "\n";
			}
			$style_lines .= '.xililanguagelist li[class*="lang-"] a:hover {background:transparent no-repeat; }' . "\n";
			// loop lines after.
			$style_lines .= $loop_style_lines;

			$style_lines .= '</style>';
		} else {
			$style_lines .= '<!--- Xili-language - this theme do not support custom xili-flags -->' . "\n";
		}
		return $style_lines;
	}

}
