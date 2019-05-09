<?php
/**
 * XL class pomo functions
 *
 * @package Xili-Language
 * @subpackage core
 * @since 2.23
 */

/**
 * Return the current language of theme.
 *
 * @since 0.9.7
 * @updated 2.11.0
 * use by other function elsewhere
 * @param slug, iso, full name, alias, charset, hidden, count
 *
 * @return by default the slug of language (used in query).
 */

function the_curlang( $by = 'slug' ) {
	return xili_curlang( $by );
}

function xili_curlang( $by = 'slug' ) {
	global $xili_language;
	if ( empty( $by ) ) {
		$by = 'slug';
	}
	$by = strtolower( $by );

	$slug = $xili_language->curlang;
	$the_cur_language = $xili_language->cur_language;
	if ( 'iso' == $by || 'iso_name' == $by ) {
		$by = 'name';
	}
	if ( 'full name' == $by || 'english_name' == $by ) {
		$by = 'description';
	}
	if ( $slug && in_array( $by, array( 'name', 'description', 'count' ) ) ) {
		$language = get_term_by( 'slug', $slug, TAXONAME, ARRAY_A );
		return $language[ $by ];
	}
	if ( $slug && in_array( $by, array( 'alias', 'charset', 'hidden', 'visibility' ) ) ) {
		//$val = ( isset($xili_language->xili_settings['lang_features'][$slug][$by]) ) ? $xili_language->xili_settings['lang_features'][$slug][$by] : '';
		if ( 'hidden' == $by ) {
			$by = 'visibility'; // 2.22
			$val = 1 - $the_cur_language->{$by};
			$val = ( 1 == $val ) ? 'hidden' : '';
		} else {
			$val = $the_cur_language->{$by};
		}
		return $val;
	}
	if ( $slug && ( 'direction' == $by || 'text_direction' == $by ) ) {
		return $xili_language->curlang_dir;
	}

	return $slug;
}

/**
 * Test the current language of displayed page.
 *
 * @since 2.11
 * use for other function elsewhere
 * @param "" for undefined, slug of tested language alone or an array list
 * @return true or false
 */
function is_xili_curlang( $lang = false ) {
	if ( false === $lang ) {
		return false;
	}
	$lang_array = array();
	if ( ! is_array( $lang ) ) {
		$lang_array[] = $lang; // when not called by xili-postinpost
	} else {
		$lang_array = $lang;
	}
	global $xili_language;
	if ( in_array( $xili_language->curlang, $lang_array ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Return field of a language
 * <?php echo xili_get_language_field('alias', 'fr_FR'); ?>
 *
 * @since 2.12.0
 * @param field as in term of in settings - name ISO (fr_FR) or slug (fr_fr)
 * @return WP_error or field
 */
function xili_get_language_field( $field, $lang_isoorslug ) {
	global $xili_language;
	$res = term_exists( $lang_isoorslug, TAXONAME );
	if ( $res ) {
		if ( empty( $field ) ) {
			$field = 'slug';
		}
		$field = strtolower( $field );
		if ( 'iso' == $field ) {
			$field = 'name';
		}
		if ( 'full name' == $field ) {
			$field = 'description';
		}
		$language = get_term( $res['term_id'], TAXONAME, ARRAY_A, 'edit' );
		$language_obj = Xili_Language_Term::get_instance( $res['term_id'] );
		if ( $language ) {
			if ( in_array( $field, array( 'name', 'description', 'count' ) ) ) {
				return $language[ $field ];
			}
			if ( in_array( $field, array( 'alias', 'charset', 'hidden', 'visibility' ) ) ) {
				if ( 'hidden' == $field ) {
					$field = 'visibility'; // 2.22
					$val = 1 - $language_obj->termmetas[ $field ];
					$val = ( 1 == $val ) ? 'hidden' : '';
				} else {
					$val = $language_obj->termmetas[ $field ];
				}
				return $val;
			}
			if ( 'direction' == $field || 'text_direction' == $field ) {
				return $language_obj->termmetas[ $field ];
			}
		}
	}
	return new WP_Error( 'language-error', __( 'Language or taxonomy language not available', 'xili-language' ) );
}

/**
 * Return the current language and dir of theme.
 *
 * @since 0.9.9
 * use for other function elsewhere
 *
 * @return array with slug of language (used in query) and dir (ltr or rtl).
 */
function the_cur_lang_dir() {
	global $xili_language;
	return array(
		QUETAG => $xili_language->curlang,
		'direction' => $xili_language->curlang_dir,
	);
}

/**
 * Return the current group of languages
 *
 * @since 0.9.8.3
 */
function the_cur_langs_group_id() {
	global $xili_language;
	return $xili_language->langs_group_id;
}

/**
 * Return the feature of language as array or string if param is specified (language slug)
 * Used by xili_language_list hook to hidden checked language
 *
 * @since 1.8.9.1
 * @since 2.11.0
 * @since  2.22 obsolete - please use language object
 */
function xl_lang_features( $slug, $param = '' ) {
	global $xili_language;
	if ( '' == $param ) {
		$val = ( isset( $xili_language->xili_settings['lang_features'][ $slug ] ) ) ? $xili_language->xili_settings['lang_features'][ $slug ] : array();
	} else {
		$val = ( isset( $xili_language->xili_settings['lang_features'][ $slug ][ $param ] ) ) ? $xili_language->xili_settings['lang_features'][ $slug ][ $param ] : '';
	}
	return $val;
}




/**
 * Return the language of current post in loop.
 *
 * @since 0.9.7.0
 * @updated 0.9.9, 2.6
 * useful for functions in functions.php or other plugins
 *
 * @param ID of the post, $type
 * @return the slug (en_us) or iso (en_US) or name ( english )...
 */
function get_cur_language( $post_ID, $type = 'slug' ) {
	global $xili_language;
	return $xili_language->get_post_language( $post_ID, $type );
}

/**
 * Return the lang and dir of language of current post in loop.
 *
 * @since 0.9.9
 * useful for functions in functions.php or other plugins
 *
 * @param ID of the post
 * @return array two params : lang (as slug) and direction of lang (ltr or rtl).
 */
function get_cur_post_lang_dir( $post_ID ) {
	global $xili_language;
	return $xili_language->get_cur_language( $post_ID );
}

/**
 * Return languages objects in taxinomy. Useful for hooks as in functions.php of theme
 *
 * @since 1.6.0
 * @param $force to avoid buffer
 */
function xili_get_listlanguages( $force = false ) {
	global $xili_language;
	return $xili_language->get_listlanguages( $force );
}

/**
 * Return language object of a post.
 *
 * @since 1.1.8
 * useful for functions in functions.php or other plugins
 *
 * @param ID of the post
 * @return false or object with params as in current term (->description = full name of lang, ->count = number of posts in this language,...
 */
function xiliml_get_lang_object_of_post( $post_ID ) {

	$ress = wp_get_object_terms( $post_ID, TAXONAME ); /* lang of target post */
	if ( array() == $ress ) {
		return false;
	} else {
		return $ress[0];
	}
}

/**
 * Return the language of current browser.
 *
 * @since 0.9.7.6
 * @updated 0.9.9
 * useful for functions in functions.php or other plugins
 *
 * @param no
 * @return the best choice.
 */
function choice_of_browsing_language() {
	global $xili_language;
	return $xili_language->choice_of_browsing_language();
}

/**
 * Return the lang and dir of current browser.
 *
 * @since 0.9.9
 * useful for functions in functions.php or other plugins
 *
 * @param no
 * @return array of the best choice lang and his dir.
 */
function choice_of_browsing_lang_dir() {
	global $xili_language;
	$lang = $xili_language->choice_of_browsing_language();
	$dir = $xili_language->get_dir_of_cur_language( $lang );
	return array(
		QUETAG => $lang,
		'direction' => $dir,
	);
}

/**
 * Activate hooks of plugin in class.
 *
 * @since 0.9.7.4
 * can be used in functions.php for special action
 *
 * @param filter name and function
 *
 */
function add_again_filter( $filtername, $filterfunction ) {
	global $xili_language;
	add_filter( $filtername, array( $xili_language, $filterfunction ) );
}

/**
 * Replace get_category_link to bypass hook from xili_language
 *
 * @since 0.9.7.4
 * @updated 1.0.1
 * can be used in functions.php for special action needing permalink
 * @param category ID
 * @return the permalink of passed cat_id.
 */
function xiliml_get_category_link( $catid = 0 ) {
	global $xili_language;
	if ( 0 == $catid ) {
		global $wp_query;
		$catid = $wp_query->query_vars['cat'];
	}
	remove_filter( 'term_link', array( $xili_language, 'xiliml_term_link_append_lang' ) );
	$catcur = get_category_link( $catid );
	add_filter( 'term_link', array( $xili_language, 'xiliml_term_link_append_lang' ), 10, 3 );
	return $catcur;
}

/** used by xili widget - usable if you need to create your own template tag
 *
 * @since 0.9.9.4
 * @param quantity of comments
 *
 * @return comments objects...
 */
function xiliml_recent_comments( $number = 5 ) {
	global $xili_language;
	return $xili_language->xiliml_recent_comments( $number );
}

/**
 * Return full object of a language
 * @since 1.1.8
 * @param name (fr_FR) or slug (fr_fr)
 * @return false or full language object (example ->description = full as set in admin UI)
 */
function xiliml_get_language( $lang_nameorslug = '' ) {
	$language = term_exists( $lang_nameorslug, TAXONAME );
	if ( $language && ! is_wp_error( $language ) ) { // 2.19.3 - if taxonomy not declared (function called too soon)
		return get_term( $language['term_id'], TAXONAME, OBJECT, 'edit' );
	} else {
		return false;
	}
}

/**
 *
 * Template Tags for themes (with current do_action tool some are hookable functions)
 *
 */

/**
 * Template Tag insertable in search form for sub-selection of a language
 *
 * @since 0.9.7
 * @updated 1.8.2
 * can be used in theme template
 * example: if(class_exists('xili_language')) xiliml_langinsearchform()
 *
 * hook: add_action('xiliml_langinsearchform','your_xiliml_langinsearchform',10,2) to change its behaviour elsewhere
 * @param html tags
 * @return echo the list as radio-button
 */
function xiliml_langinsearchform( $before = '', $after = '', $echo = true ) {
	/* list of radio buttons for search form*/
	global $xili_language;
	if ( $xili_language->this_has_external_filter( 'xiliml_langinsearchform' ) ) {
		remove_filter( 'xiliml_langinsearchform', array( $xili_language, 'xiliml_langinsearchform' ) ); /*no default from class*/
	}
	if ( $echo ) {
		echo apply_filters( 'xiliml_langinsearchform', $before, $after, $echo );
	} else {
		return apply_filters( 'xiliml_langinsearchform', $before, $after, $echo );
	}
}

/**
 * Template Tag - replace the_category() tag of WP Core
 *
 * @since 0.9.0
 * @updated 1.4.2 - default value to post_ID
 * can be used in theme template in each post in loop
 * example: if(class_exists('xili_language')) xiliml_the_category($post->ID)
 *
 * hook: add_action('xiliml_the_category','your_xiliml_the_category',10,3) to change its behaviour elsewhere
 * @param post_id separator echo (true by default)
 * @return echo (by default) the list of cats with comma separated...
 */
function xiliml_the_category( $post_ID = 0, $separator = ', ', $echo = true ) {
	global $xili_language, $post;
	if ( 0 == $post_ID ) {
		$post_ID = $post->ID;
	}
	if ( $xili_language->this_has_external_filter( 'xiliml_the_category' ) ) {
		remove_filter( 'xiliml_the_category', array( $xili_language, 'xiliml_the_category' ) ); /*no default from class*/
	}
	do_action( 'xiliml_the_category', $post_ID, $separator, $echo );
}

/**
 * Template Tag - in loop display the link of other posts defined as in other languages
 *
 * @since 0.9.0
 * @updated 0.9.9.2, 1.1 (can return an array of lang + id)
 * @updated 1.4.2 - default value to post_ID
 * can be used in theme template in single.php under the title
 * example: if(class_exists('xili_language')) xiliml_the_other_posts($post->ID)
 *
 * hook: add_action('xiliml_the_other_posts','your_xiliml_the_other_posts',10,3) to change its behaviour elsewhere
 * @param post_id, before, separator, type (echo, array).
 * @return echo (by default) the list
 */
function xiliml_the_other_posts( $post_ID = 0, $before = 'This post in', $separator = ', ', $type = 'display' ) {
	global $xili_language, $post;
	if ( 0 == $post_ID ) {
		$post_ID = $post->ID;
	}
	if ( $xili_language->this_has_external_filter( 'xiliml_the_other_posts' ) ) {
		remove_filter( 'xiliml_the_other_posts', array( $xili_language, 'xiliml_the_other_posts' ) ); /*no default from class*/
	}
	return apply_filters( 'xiliml_the_other_posts', $post_ID, $before, $separator, $type );
}

/**
 * Template Tag - in loop display the language of the post
 *
 * @since 0.9.0
 * can be used in theme template in loop under the title
 * example: if(class_exists('xili_language')) xili_post_language()
 *
 * hook: add_action('xili_post_language','your_xili_post_language',10,2) to change its behaviour elsewhere
 * @param before after
 * @return echo (by default) the language
 */
function xili_post_language( $before = '<span class="xili-lang">(', $after = ')</span>' ) {
	do_action( 'xili_post_language', $before, $after );
}

/**
 * Template Tag - outside loop (sidebar) display the languages of the site (used also by widget)
 *
 * @since 0.9.0
 * @updated 0.9.7.4
 * @udpated 1.8.9.1
 * can be used in theme template in sidebar menu or header menu
 * example: if(class_exists('xili_language')) xili_language_list()
 * theoption param is used to define type of display according places (sidebar / header) in theme (see dev.xiligroup.com)
 *
 * hook: add_action('xili_language_list','your_xili_language_list',10,5) to change its behaviour elsewhere
 * @param before after theoption
 * @return echo the list of languages
 * @hidden don't list hidden languages
 */
function xili_language_list( $before = '<li>', $after = '</li>', $theoption = '', $echo = true, $hidden = false ) {

	global $xili_language;
	if ( $xili_language->this_has_external_filter( 'xili_language_list' ) ) {
		remove_filter( 'xili_language_list', array( $xili_language, 'xili_language_list' ) ); /*no default from class*/
	}
	return apply_filters( 'xili_language_list', $before, $after, $theoption, $echo, $hidden );
}

/**
 * function to get id, link or permalink of linked post in target lang
 * to replace get_post_meta($post->ID, 'lang-'.$language->slug, true) soon obsolete
 * @since 1.8.9.1
 *
 * @updated 2.1.0 - permalink as
 */
function xl_get_linked_post_in( $from_id, $lang, $info = 'id' ) {
	global $xili_language;

	$language = xiliml_get_language( $lang ); /* test if lang is available */

	if ( false !== $language ) {
		$otherpost = get_post_meta( $from_id, QUETAG . '-' . $language->slug, true ); // will be soon changed
		if ( 'permalinknav' == $info ) {
			return $xili_language->link_of_linked_post( $from_id, $language->slug );
		}

		if ( $otherpost ) {
			switch ( $info ) {
				case 'id':
					$output = $otherpost;
					break;
				case 'link':
					$post = get_post( $otherpost );
					if ( isset( $post->post_type ) ) {
						if ( 'post' == $post->post_type ) {
							$output = home_url( '?p=' . $otherpost );
						} elseif ( 'post' == $post->post_type ) {
							$output = home_url( '?page_id=' . $otherpost );
						}
					}
					break;
				case 'permalink':
					$output = get_permalink( $otherpost );
					break;
				case 'postname': // 2.19.3
					$post = get_post( $otherpost );
					$output = $post->post_name;
					break;
			}
		} else {
			switch ( $info ) {
				case 'id':
					$output = 0; // false
					break;
				case 'link':
					$output = '#';
					break;
				case 'permalink':
					$output = '#';
					break;
				case 'postname':
					$output = '';
					break;
			}
		}
		return $output;
	}
}


/**
 * functions to change and restore loop's query tag
 * (useful for sidebar widget - see functions table)
 * @since 1.3.0
 * @param lang to modify query_tag -
 *
 */
function xiliml_force_loop_lang( $lang_query_tag ) {
	global $xili_language, $wp_query;
	$xili_language->temp_lang_query_tag = $wp_query->query_vars[ QUETAG ];
	$wp_query->query_vars[ QUETAG ] = $lang_query_tag;
	$xili_language->current_lang_query_tag = $wp_query->query_vars[ QUETAG ];
}

function xiliml_restore_loop_lang() {
	global $xili_language, $wp_query;
	$wp_query->query_vars[ QUETAG ] = $xili_language->temp_lang_query_tag;
	$xili_language->current_lang_query_tag = $wp_query->query_vars[ QUETAG ];
}

/**
 * functions to permit lang query tag
 * (useful for WP_Query)
 * @since 1.3.2
 * example: add_action('parse_query','xiliml_add_lang_to_parsed_query');
 *   $r = new WP_Query($thequery);
 *   remove_filter('parse_query','xiliml_add_lang_to_parsed_query');
 * used by class xili_Widget_Recent_Posts
 */
function xiliml_add_lang_to_parsed_query( $theclass = array() ) {
	global $wp_query;
	$query = $theclass->query;
	if ( is_array( $query ) ) {
		$r = $query;
	} else {
		parse_str( $query, $r );
	}
	if ( array_key_exists( QUETAG, $r ) ) {
		$wp_query->query_vars[ QUETAG ] = $r[ QUETAG ];
	}
}

/* ****** functions and filter added for new default theme named twentyten and twenty-eleven (since WP 3.0) ******* */


/**
 * filter in theme multilingual-permalinks.php file
 *
 * @since 2.10.0
 * @since 2.22 object - to optimize ?
 */
function xili_language_trans_slug_qv( $lang_slug ) {
	global $xili_language;

	//if ( isset ( $_POST['language_alias'] ) ) // called in add or edit new languages
	//	$xili_language->xili_settings = get_option('xili_language_settings'); // need update !

	//$short = ( isset ( $xili_language->xili_settings['lang_features'][$lang_slug]['alias'] ) ) ? $xili_language->xili_settings['lang_features'][$lang_slug]['alias'] : $lang_slug ;
	$res = term_exists( $lang_slug, TAXONAME );
	if ( $res ) {
		$language = get_term( $res['term_id'], TAXONAME, ARRAY_A, 'edit' );
		$language_obj = Xili_Language_Term::get_instance( $res['term_id'] );
		return $language_obj->termmetas['alias'];
	}

	return $lang_slug;
}

/**
 * Translate string with or w/o gettext context, and escapes it for safe use in HTML output.
 *
 * @param string $text    Text to translate.
 * @param string $context Context information for the translators.
 * @param string $domain  Optional. Text domain. Unique identifier for retrieving translated strings.
 *                        Default 'default'.
 * @return string Translated text.
 */
function xl__( $text, $domain = 'default' ) {
	return translate( $text, $domain );
}

function xl_esc_attr__( $text, $domain = 'default' ) {
	return esc_attr( translate( $text, $domain ) );
}

function xl_esc_attr_e( $text, $domain = 'default' ) {
	echo esc_attr( translate( $text, $domain ) );
}

function xl_esc_html__( $text, $domain = 'default' ) {
	return esc_html( translate( $text, $domain ) );
}

function xl_esc_html_e( $text, $domain = 'default' ) {
	echo esc_html( translate( $text, $domain ) );
}

function xl_x( $text, $context, $domain = 'default' ) {
	return translate_with_gettext_context( $text, $context, $domain );
}

function xl_esc_attr_x( $text, $context, $domain = 'default' ) {
	return esc_attr( translate_with_gettext_context( $text, $context, $domain ) );
}

function xl_esc_html_x( $text, $context, $domain = 'default' ) {
	return esc_html( translate_with_gettext_context( $text, $context, $domain ) );
}




