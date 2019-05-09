<?php
/**
 * XL class permalinks functions
 *
 * @package Xili-Language
 * @subpackage core
 * @since 2.23
 */
/**
 * called by add_action( 'plugins_loaded', 'permalink_init', 1);
 *
 */
function xili_permalink_init() {
	if ( get_option( 'permalink_structure' ) ) {

		require_once XILILANGUAGE_PLUGIN_DIR . '/xili-includes/xili-permalinks-class.php';

		add_action( 'init', 'xl_permalinks_theme', 1 );
	}
}

/**
 * Add rules and add_permastruct of lang
 * Incorporate rules with previous settings in 201x-xili bundled themes
 *
 */
function xl_permalinks_theme() {

	$xili_language_settings = get_option( 'xili_language_settings' );
	if ( function_exists( 'xl_permalinks_init' ) && ! isset( $xili_language_settings['lang_permalink'] ) ) {
		xl_permalinks_init();
	} else if ( function_exists( 'xl_permalinks_init' ) && isset( $xili_language_settings['lang_permalink'] ) && 'perma_ok' !== $xili_language_settings['lang_permalink'] ) {
		remove_filter( 'alias_rule', 'xili_language_trans_slug_qv' );  // now managed by plugin and not 201x-xili themes
		// this function is in themes/theme-x/functions-xili/multilingual-permalinks.php required if option perma_ok
		// back compatibility 2.20
	} else {
		global $xl_permalinks_rules;

		$ok = ( isset( $xili_language_settings['lang_permalink'] ) && 'perma_ok' == $xili_language_settings['lang_permalink'] ) ? true : false;
		if ( $ok && get_option( 'permalink_structure' ) && class_exists( 'XL_Permalinks_Rules' ) ) {

			// back compatibility 2.20
			if ( ! has_filter( 'alias_rule', 'xili_language_trans_slug_qv' ) ) {
				add_filter( 'alias_rule', 'xili_language_trans_slug_qv' ); // alias insertion
			}

			$xl_permalinks_rules = new XL_Permalinks_Rules();

			add_permastruct( 'language', '%lang%', true, 1 );
			add_permastruct( 'language', '%lang%', array( 'with_front' => false ) );
		}
	}
}
