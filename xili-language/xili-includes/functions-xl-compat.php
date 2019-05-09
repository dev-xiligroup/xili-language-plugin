<?php
/**
 * XL class compat functions
 *
 * @package Xili-Language
 * @subpackage core
 * @since 2.23
 */

/****************** Customization for bbPress *****************/

/**
 * Detect older plugin xili-xl-bbp-addon.php until 1.7.1
 * avoid red message
 * @since 2.18
 *
 */
function xili_xl_old_bbp_addon_remove() {
	if ( is_admin() ) {
		if ( is_plugin_active( 'xili-language/xili-xl-bbp-addon.php' ) ) {
			deactivate_plugins( 'xili-language/xili-xl-bbp-addon.php' );
		}
	}
}
add_action( 'admin_init', 'xili_xl_old_bbp_addon_remove' );

// new bbPress api since 2.18
// replace test plugin xili-xl-bbp-addon
if ( class_exists( 'bbPress' ) && $subfolder = get_option( 'xl-bbp-addon-activated-folder', '/' ) ) { // true by default
	$plugin_path = dirname( __FILE__ );
	if ( file_exists( $plugin_path . $subfolder . 'xili-xl-bbp-addon.php' ) ) {
		require_once( $plugin_path . $subfolder . 'xili-xl-bbp-addon.php' );
		add_action( 'plugins_loaded', 'xili_xl_bbp_lang_init', 9 ); // 9 = to be registered before bbPress instantiate
		add_action( 'plugins_loaded', 'xili_xl_bbp_addon_init', 17 ); // after xili-tidy-tags
		add_action( 'plugins_loaded', 'xili_tidy_tags_start_topic', 18 ); // after xili-tidy-tags
	} else {
		add_action( 'admin_notices', 'xili_xl_bbp_error' );
		return;
	}
}

/**
 *
 * Error if no bbPress addon file
 *
 */
function xili_xl_bbp_error() {
		global $wp_version;
		load_plugin_textdomain( 'xili_language_errors', false, 'xili-language/languages' );
		echo '<div id="message" class="error fade"><p>';
		echo '<strong>' . esc_html__( 'Installation of both xili-language AND bbPress need addon file in good place.', 'xili_language_errors' ) . '</strong>';
		echo '<br />';
		echo '</p></div>';
}





/****************** Customization for JetPack *****************/

// special jetpack to live change admin side language - 2.8.9 (was before tested with bbPress addon)
function xili_jetpack_lang_init() {
	if ( is_admin() ) {
		add_filter( 'plugin_locale', 'xili_jetpack_lang_reload', 10, 2 );
	}
}

function xili_jetpack_lang_reload( $locale = 'en_US', $domain = 'default' ) {
	global $xili_language;
	if ( 'xili-language' == $domain && class_exists( 'jetpack' ) ) { // because plugin jetpack domain not filterable
		$locale = get_user_option( 'user_locale' );
		if ( empty( $locale ) ) {
			$wplang = $xili_language->get_wplang();
			$locale = ( '' != $wplang ) ? $wplang : 'en_US';

			if ( is_multisite() ) {
				$ms_locale = get_option( 'WPLANG' );
				if ( defined( 'WP_INSTALLING' ) || ( false === $ms_locale ) ) {
					$ms_locale = get_site_option( 'WPLANG' );
				}
				if ( false != $ms_locale ) {
					$locale = $ms_locale;
				}
			}
		}
		unload_textdomain( 'jetpack' ); // by Jetpack_Sync::sync_options( __FILE__, 'widget_twitter' ) during plugin loading 2.3

		load_textdomain( 'jetpack', WP_PLUGIN_DIR . '/jetpack/languages/jetpack-' . $locale . '.mo' );
	}
	return $locale;
}

/**
 * @since 2.10.1 - Needed to recover 2014-xili customized multilingual featured content
 */
function xili_jetpack_disable_featured() {
	global $xili_language;

	if ( 'enable' == $xili_language->xili_settings['enable_fc_theme_class'] && ! class_exists( 'Featured_Content' ) && 'plugins.php' !== $GLOBALS['pagenow'] ) {
		if ( file_exists( get_stylesheet_directory() . '/inc/featured-content.php' ) ) {
			require get_stylesheet_directory() . '/inc/featured-content.php'; // this one will disable others
		}
	}
}

if ( class_exists( 'jetpack' ) ) { // inited by init filter but without modules (priority 100 in jetpack)
	// 2.11.1
	add_action( 'plugins_loaded', 'xili_jetpack_disable_featured', 17 ); // after XL and XTT
	// must be commented if jetpack changes when load_plugin_textdomain (as # http://plugins.trac.wordpress.org/ticket/1764)
	// disabled with jetpack 5.0
	// add_action( 'plugins_loaded', 'xili_jetpack_lang_init', 99 ); // 99 = to be registered before jetpack instantiate - plugins_loaded = 100
}
