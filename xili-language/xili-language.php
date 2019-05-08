<?php
/*
Plugin Name: xili-language
Plugin URI: http://dev.xiligroup.com/xili-language/
Description: This plugin modify on the fly the translation of the theme depending the language of the post or other blog elements - a way to create a real multilanguage site (cms or blog). Numerous template tags and three widgets are included. It introduce a new taxonomy - here language - to describe posts and pages. To complete with tags, use also xili-tidy-tags plugin. To include and set translation of .mo files use xili-dictionary plugin. Includes add-on for multilingual bbPress forums.
Author: dev.xiligroup.com - MS
Author URI: http://dev.xiligroup.com
Version: 2.23.01
License: GPLv2
Text Domain: xili-language
Domain Path: /languages/
*/

# updated 190430 - 2.23.01 - begin php files rewritting - new files required

# updated 190418 - 2.22.12 - tests wp5.11 - add 2019 bundled theme
# updated 171208 - 2.22.11 - test wp4.9.1 - fixes live locale changing
# updated 170822 - 2.22.10 - fixes permalinks query_tags, add flags in assets (2017), pre-test wp4.9-alpha
# updated 170622 - 2.22.8 - fixes, jetpack settings compatibility (json)
# updated 170607 - 2.22.7 - updates locales.php (Jetpack 5.0) - new language added - preview of language properties
# updated 170523 - 2.22.6 - fixes alias creation or update in xili-language-term
# updated 170504 - 2.22.5 - updates locales.php (Jetpack 4.9)
# updated 170421 - 2.22.4 - updates comment forms - finalize multiple languages per post (custom field _multiple_language) - bulk actions
# updated 170310 - 2.22.3 - fixes for 4.6 & 4.7 / notices fixes when changing theme

# updated 161213 - 2.22.1 - fixes for 4.6 & 4.7
# updated 160810 - 2.22.0 - language taxonomy settings are saved in term metas ( need WP 4.4 )
# updated 160805 - 2.21.3 - locale file updated (JetPack 4.1.1) - links selection improved
# updated 160728 - 2.21.2 - verified with 4.5.3 and tested with 4.6-rc1 - introduces new taxonomy language class (WP 4.4+)

# updated 151104 - 2.21.1 - default mo behaviour (parent) - 2016 infos
# updated 150927 - 2.21.0 - includes detection of previous PLL install - source cleaned and improved
# updated 150917 - 2.20.3 - new option to add widget visibility rules according language // fixes admin side taxonomy translation
# updated 150914 - 2.20.2 - updated language list (Jetpack 3.7) - updated commun messages (pointer)
# updated 150903 - 2.20.1 - fixes error "/theme-multilingual-classes.php on line 1014"
# updated 150820 - 2.20.0 - WP 4.3 is finally shipped - add "alternate x-default" - includes now files and functions from 201x-xili themes examples - pre-tests with twentysixteen
# updated 150818 - 2.19.3 - latest tests with WP 4.3 RC2 and WooCommerce 2.4.4 (multilingual kit)
# updated 150717 - 2.19.2 - add show in REST param
# updated 150707 - 2.19.1 - fixes admin add_local_text_domain_file (3pepe3)
# updated 150705 - 2.19.0 - Stable version
# updated 150618 - 2.18.2 - fixes, add link in post edit, add shortcode linked post, pre-tests with WP 4.3-beta1, ready to translate theme_mod values
# updated 150601 - 2.18.1 - fixes, improves media editing page (cloning part, admin side)
# updated 150508 - 2.18.0 - integration of xl-bbp-addon, fixes/adds menu-item-has-children class in menus selector, fixes propagation options

# updated 150424 - 2.17.1 - fixes after final WP 4.2 Release
# updated 150418 - 2.17.0 - is_rtl() replaces get_bloginfo - 4.2-RC1 - stickies improved with JSON REST API - deleting multisite install improved

# updated 150401 - 2.16.5 - WP JSON REST API compatibility - fixes is_main_query and option stickies -
# updated 150323 - 2.16.4 - custom_xili_flag for admin side - search in theme/images/flags - better selection of active widgets - improved permalinks class
# updated 150306 - 2.16.3 - fixes warning of archives link w/o perma - widget archives filtered if curlang - add xili_Widget_Categories class (need registering by author)
# updated 150228 - 2.16.2 - fixes warning if dropdown categories, some rewritten lines

# less than 2.16…
# see readme text for these intermediate versions for WP 2.15. or visit other previous versions (2.15).
# updated 090228 - Class and OOP - see 0.9.7 in comments of functions below - only for WP 2.7.x

# This plugin is free software; you can redistribute it and/or
# modify it under the terms of the GNU Lesser General Public
# License as published by the Free Software Foundation; either
# version 2.1 of the License, or (at your option) any later version.
#
# This plugin is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
# Lesser General Public License for more details.
#
# You should have received a copy of the GNU Lesser General Public
# License along with this plugin; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA


define( 'XILILANGUAGE_VER', '2.23.01' ); /* used in admin UI*/
define( 'XILILANGUAGE_WP_VER', '4.9' ); /* minimal version - used in error - see at end */
define( 'XILILANGUAGE_PHP_VER', '7.2.0' ); /* used in error - see at end */
define( 'XILILANGUAGE_PREV_VER', '2.15.4' );
define( 'XILILANGUAGE_WP_TESTED', '4.9 Tipton' ); /* 2.17.1 - used in version pointer infos */
define( 'XILILANGUAGE_PLL_TESTED', '1.7.9' ); /* 2.20.3 - newest PLL tested */
define( 'XILILANGUAGE_DEBUG', false ); /* used in dev step UI - xili_xl_error_log () if WP_DEBUG is true */
define( 'XILILANGUAGE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'XILILANGUAGE_PLUGIN_URL', plugins_url( '', __FILE__ ) );
define( 'XILILANGUAGE_PLUGIN_FILE', __FILE__ );

/********************* the CLASS **********************/
require_once XILILANGUAGE_PLUGIN_DIR . '/xili-includes/class-xili-language.php';

require_once XILILANGUAGE_PLUGIN_DIR . '/xili-includes/functions-xl-taxonomies.php';
require_once XILILANGUAGE_PLUGIN_DIR . '/xili-includes/functions-xl-menus.php';
require_once XILILANGUAGE_PLUGIN_DIR . '/xili-includes/functions-xl-languages.php';
require_once XILILANGUAGE_PLUGIN_DIR . '/xili-includes/functions-xl-date-time.php';
require_once XILILANGUAGE_PLUGIN_DIR . '/xili-includes/functions-xl-permalinks.php';
require_once XILILANGUAGE_PLUGIN_DIR . '/xili-includes/functions-xl-compat.php';
require_once XILILANGUAGE_PLUGIN_DIR . '/xili-includes/class-xl-wp-locale.php';

/**
 * instantiation of xili_language class
 *
 * @since 1.8.8 to verify that WP 3.0 is installed
 * @updated for 1.8.9, 2.3.1, 2.7.1 (function)
 *
 */
function xili_language_start() {
	global $wp_version, $xili_language, $xili_language_admin;
	if ( version_compare( PHP_VERSION, XILILANGUAGE_PHP_VER, '<' ) ) {
		add_action( 'admin_notices', 'xili_language_need_php5' );
		return;
	} elseif ( version_compare( $wp_version, XILILANGUAGE_WP_VER, '<' ) && XILILANGUAGE_VER > XILILANGUAGE_PREV_VER ) {
		add_action( 'admin_notices', 'xili_language_need_31' );
		return;
	} else {
		// 2.21.2
		if ( version_compare( $wp_version, 4.4, '>=' ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'xili-includes/class-xili-language-term.php';
		}

		// new sub-folder since 2.6
		require_once plugin_dir_path( __FILE__ ) . 'xili-includes/xili-language-widgets.php';
		require_once plugin_dir_path( __FILE__ ) . 'xili-includes/theme-multilingual-classes.php'; // since 2.20.0
		/**
		 * instantiation of xili_language class
		 *
		 * @since 0.9.7
		 * @updated 2.6
		 *
		 *
		 * @param locale_method (true for cache compatibility... in current tests with johan.eenfeldt@kostdoktorn.se)
		 * @param future version
		 */

		$xili_language = new Xili_Language( false, false );

		if ( is_admin() ) {
			$plugin_path = dirname( __FILE__ ); // w/o / at end
			require $plugin_path . '/xili-includes/class-xili-language-admin.php';
			$xili_language_admin = new Xili_Language_Admin( $xili_language );
		}

		/**
		 * Enable to add functions and filters that are not in theme's functions.php
		 * These filters are common even if you change default theme...
		 * Place your functions.php in folder plugins/xilidev-libraries/
		 * if you have a filter in this file, avoid to have similar one in functions.php of the theme !!!
		 *
		 * (for tests, check / uncheck 'enable gold functions' in settings UI)
		 * @since 1.0
		 * @updated 1.4.0
		 */
		$xili_language->insert_gold_functions();
	}
}



/**
 * xili-language is constructed only via plugins_loaded - not when plugin file is loaded
 *
 */
add_action( 'plugins_loaded', 'xili_permalink_init', 1 );
add_action( 'plugins_loaded', 'xili_language_start', 13 ); // before xili-dictionary (20) and xili_tidy_tags (15) - 2.7.1

/**
 * == Functions using the xili_language class ==
 */

/**
 * function to progressively replace the previous constant THEME_TEXTDOMAIN only usable in mono site
 *
 * @since 1.5.2
 */
function the_theme_domain() {
	global $xili_language;
	return $xili_language->thetextdomain;
}


/**
 * @since 2.8.3 - XILILANGUAGE_DEBUG on top
 */
function xili_xl_error_log( $content = '' ) {

	if ( defined( 'XILILANGUAGE_DEBUG' ) && XILILANGUAGE_DEBUG == true && defined( 'WP_DEBUG' ) && true == WP_DEBUG && '' != $content ) {
		error_log( 'XL' . $content );
	}
}

/**
 * errors messages
 */
function xili_language_need_php5() {
		global $wp_version;
		load_plugin_textdomain( 'xili_language_errors', false, 'xili-language/languages' );
		echo '<div id="message" class="error fade"><p>';
		echo '<strong>' . esc_html__( 'Installation of xili-language is not completed.', 'xili_language_errors' ) . '</strong>';
		echo '<br />';
		/* translators: */
		printf( esc_html__( 'This xili-language version (%1$s) need PHP Version more than %2$s; installed release is %3$s.', 'xili_language_errors' ), XILILANGUAGE_VER, XILILANGUAGE_PHP_VER, PHP_VERSION );
		echo '<br />';
		/* translators: */
		printf( esc_html__( 'Find a server with PHP Version to more %1$s or use xili-language version less or equal to %2$s', 'xili_language_errors' ), XILILANGUAGE_PHP_VER, XILILANGUAGE_PREV_VER );
		echo '</p></div>';
}

function xili_language_need_31() {
		global $wp_version;
		load_plugin_textdomain( 'xili_language_errors', false, 'xili-language/languages' );
		echo '<div id="message" class="error fade"><p>';
		echo '<strong>' . esc_html__( 'Installation of xili-language is not completed.', 'xili_language_errors' ) . '</strong>';
		echo '<br />';
		/* translators: */
		printf( esc_html__( 'This xili-language version (%1$s) need WordPress Version more than %2$s; installed release is %3$s.', 'xili_language_errors' ), XILILANGUAGE_VER, XILILANGUAGE_WP_VER, $wp_version );
		echo '<br />';
		/* translators: */
		printf( esc_html__( 'Upgrade WordPress Version to more %1$s or use xili-language version less than %2$s', 'xili_language_errors' ), XILILANGUAGE_WP_VER, XILILANGUAGE_PREV_VER );
		echo '</p></div>';
}

