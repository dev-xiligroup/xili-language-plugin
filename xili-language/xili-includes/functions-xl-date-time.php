<?php
/**
 * XL class date time functions
 *
 * @package Xili-Language
 * @subpackage core
 * @since 2.23
 */

/**
 * Return the current date or a date formatted with strftime.
 *
 * @since 0.9.7.1
 * @updated 1.6.0 - timezone offset - http://core.trac.wordpress.org/ticket/11672
 * can be used in theme for multilingual date
 * @since  2.22 uses object
 * @param format and time (if no time = current date-time)
 * @return the formatted date.
 */
function the_xili_local_time( $format = '%B %d, %Y', $time = null ) {
	global $xili_language;
	if ( null == $time ) {
		$time = current_time( 'timestamp' ); //to get the Unix timestamp with a timezone offset -
	}
	$curslug = $xili_language->curlang;
	$curlang = ( 5 == strlen( $curslug ) ) ? substr( $curslug, 0, 3 ) . strtoupper( substr( $curslug, -2 ) ) : $curslug;
	setlocale( LC_TIME, $curlang ); /* work if server is ready */

	//$charset = ( $xili_language->xili_settings['lang_features'][$curslug]['charset'] != '' ) ? $xili_language->xili_settings['lang_features'][$curslug]['charset'] : "" ; // 1.8.9.1
	$cur_language = $xili_language->cur_language;
	$charset = $cur_language->charset;

	if ( '' != $charset ) {
		return htmlentities( strftime( __( $format, the_theme_domain() ), $time ), ENT_COMPAT, $charset ); /* ,'UTF-8' entities for some server */
	} else {
		return htmlentities( strftime( __( $format, the_theme_domain() ), $time ), ENT_COMPAT );
	}
}

/**
 * Return the current date or a date formatted with strftime according get_option php date format.
 *
 * @since 1.6.0, 1.8.9.1, 2.2.2
 * @since  2.22 uses object
 * can be used in theme for multilingual date
 * @param format and time (if no time = current date-time)
 * @return the formatted date.
 */
function the_xili_wp_local_time( $wp_format = 'F j, Y', $time = null ) {
	global $xili_language;
	if ( null == $time ) {
		$time = current_time( 'timestamp' ); //to get the Unix timestamp with a timezone offset -
	}
	$curslug = $xili_language->curlang;
	$cur_language = $xili_language->cur_language;
	$charset = $cur_language->charset;
	//if ( $xili_language->xili_settings['lang_features'][$curslug]['charset'] == 'no_locale' ) { // need to be inside charset input
	if ( 'no_locale' == $charset ) {
		$date_formatted = date ( __( $wp_format, the_theme_domain(), $time ) );
		if ( function_exists( 'xili_translate_date' ) ) {
			return xili_translate_date( $curslug, $date_formatted );
		} else {
			return $date_formatted;
		}
	} else {
		$curlang = ( 5 == strlen( $curslug ) ) ? substr( $curslug, 0, 3 ) . strtoupper( substr( $curslug, -2 ) ) : $curslug;
		setlocale( LC_TIME, $curlang ); /* work if server is ready */
		$format = xiliml_php2loc_time_format_translator( __( $wp_format, the_theme_domain() ) ); /* translated by theme mo*/

		//$charset = ( $xili_language->xili_settings['lang_features'][$curslug]['charset'] != '' ) ? $xili_language->xili_settings['lang_features'][$curslug]['charset'] : "" ; // 1.8.9.1
		if ( '' != $charset ) {
			return htmlentities( strftime( $format, $time ), ENT_COMPAT, $charset ); /* ,'UTF-8' entities for some server - ja char */
		} else {
			return htmlentities( strftime( $format, $time ), ENT_COMPAT );
		}
	}
}

/**
 * Return translated format from php time to loc time used in strftime.
 *
 * @since 1.6.0
 * @updated 1.8.1 - add T -> %z, e -> %Z - 1.8.7 T -> %Z (stephen)
 * @ 1.8.9.1 - add n -> %m (japanese)
 * (was formerly in xilidev-libraries)
 * can be used in theme for multilingual date
 * @param phpformat
 * @return locale format.
 */
function xiliml_php2loc_time_format_translator( $phpformat = 'm/d/Y H:i' ) {
	/* order left to right to avoid over replacing DON'T MODIFY */
	$phpformchar = array( 'A', 'a', 'D', 'l', 'g', 'd', 'e', 'j', 'z', 'T', 'N', 'w', 'W', 'M', 'F', 'h', 'M', 'm', 'y', 'Y', 'H', 'G', 'i', 'S', 's', 'O', 'n' );
	/* doc here: http://fr2.php.net/manual/en/function.date.php */
	$locformchar = array( '%p', '%P', '%a', '%A', '%l', '%d', '%Z', '%e', '%j', '%Z', '%U', '%w', '%W', '%b', '%B', '%I', '%h', '%m', '%y', '%Y', '%H', '%l', '%M', '', '%S', '%z', '%m' );
	/* doc here: http://fr.php.net/manual/en/function.strftime.php */

	if ( '' == $phpformat ) {
		$phpformat = 'm/d/Y H:i';
	}
	// use to detect escape char that illustrate date or hour... \h or \m
	$ars = explode( '\\', $phpformat );
	$i = 0;
	if ( $ars[0] == $phpformat ) {
		$locform = str_replace( $phpformchar, $locformchar, $phpformat );
	} else {
		$locform = '';
		foreach ( $ars as $a ) {
			if ( '' != $a ) {
				$locform = $locform . ( ( 0 == $i ) ? str_replace( $phpformchar, $locformchar, $a ) : substr( $a, 0, 1 ) . str_replace( $phpformchar, $locformchar, substr( $a, 1 ) ) );
			}
			$i++;
		}
	}
	return $locform;
}

/**
 * in twentyten theme: display the time of current post when mouse is on date
 * - adapted for twentytwelve
 * @since  2.22 uses object
 */
function xiliml_get_the_translated_time( $thetime, $format = '' ) {
	global $xili_language;
	if ( 'db_locale' == $xili_language->xili_settings['wp_locale'] ) {
		$theformat = ( '' == $format ) ? get_option( 'time_format' ) : $format;
		return the_xili_wp_local_time( $theformat, strtotime( xiliml_get_the_time( 'm/d/Y H:i' ) ) ); // old method locale
	} else {
		//return $thetime; // new mode wp_locale ;
		$curslug = $xili_language->curlang;
		$cur_language = $xili_language->cur_language; // KH or HU
		if ( 'no_locale' == $cur_language->charset ) {
			if ( function_exists( 'xili_translate_date' ) ) {
				return xili_translate_date( $curslug, $thetime );
			} else {
				return $thetime;
			}
		} else {
			return $thetime;
		}
	}
}

/**
 * Clone w/o filter
 */
function xiliml_get_the_time( $d = '', $post = null ) {
	$post = get_post( $post );

	if ( '' == $d ) {
		$the_time = get_post_time( get_option( 'time_format' ), false, $post, true );
	} else {
		$the_time = get_post_time( $d, false, $post, true );
	}
	return $the_time; /* without filter */
}

/**
 * in twentyten theme: display the date of current post - adapted for twentytwelve
 * @since  2.22 uses object
 */
function xiliml_get_translated_date( $thedate, $format = '' ) {
	global $xili_language;
	$theformat = ( '' == $format ) ? get_option( 'date_format' ) : $format;
	if ( 'db_locale' == $xili_language->xili_settings['wp_locale'] ) {
		return the_xili_wp_local_time( $theformat, strtotime( xiliml_get_the_date( 'm/d/Y H:i' ) ) );
	} else {
		$cur_language = $xili_language->cur_language;
		if ( 'no_locale' == $cur_language->charset ) {
			if ( function_exists( 'xili_translate_date' ) ) {
				return xili_translate_date( $curslug, $thedate );
			} else {
				return $thedate;
			}
		} else {
			return $thedate;
		}
	}
}

if ( ! is_admin() ) {
	add_filter( 'get_the_time', 'xiliml_get_the_translated_time', 10, 3 );
	add_filter( 'get_the_date', 'xiliml_get_translated_date', 10, 2 );
}

/**
 * Clone w/o filter
 */
function xiliml_get_the_date( $d = '' ) {
	global $post;
	$the_date = '';

	if ( '' == $d ) {
		$the_date .= mysql2date( get_option( 'date_format' ), $post->post_date );
	} else {
		$the_date .= mysql2date( $d, $post->post_date );
	}

	return $the_date; /* without filter */
}

/**
 * filter for template tag: get_comment_date()
 */
function xiliml3_comment_date( $comment_time, $format = '' ) {
	$theformat = ( '' == $format ) ? get_option( 'date_format' ) : $format;
	return the_xili_wp_local_time( $theformat, strtotime( get_comment_time( 'm/d/Y H:i' ) ) );
	/* impossible to use get_comment_date as it is itself filtered*/
}
if ( ! is_admin() ) {
	add_filter( 'get_comment_date', 'xiliml3_comment_date', 10, 2 );
}

