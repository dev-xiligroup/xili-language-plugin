<?php
namespace Xili_Main;

/**
 * @package  xili-language
 * @subpackage main class
 * functions for Head part in html
 */
trait Xili_Language_Head {

	/**
	 * modify language_attributes() output
	 *
	 * @since 0.9.7.6
	 *
	 * The - language_attributes() - template tag is use in header of theme file in html tag
	 *
	 * @param $output
	 */
	public function head_language_attributes( $output ) {
		/* hook head_language_attributes */

		if ( has_filter( 'head_language_attributes' ) ) {
			return apply_filters( 'head_language_attributes', $output );
		}
		$attributes = array();
		$output = '';

		if ( is_rtl() ) {
			/*use hook for future use - 2.16.6 */
			$attributes[] = 'dir="rtl"';
		}

		if ( true == $this->langstate ) {

			$lang = ( isset( $this->langs_slug_name_array[ $this->curlang ] ) ) ? str_replace( '_', '-', $this->langs_slug_name_array[ $this->curlang ] ) : ''; // 2.8.6

		} else {
			//use hook if you decide to display limited list of languages for use by instance in frontpage
			$listlang = array();

			$listlanguages = $this->get_listlanguages();
			if ( $listlanguages ) {
				foreach ( $listlanguages as $language ) {
					$listlang[] = str_replace( '_', '-', $language->name );
				}
				$lang = $listlang[0]; // implode(', ',$listlang); // not w3c compatible
			}
		}
		if ( 'text/html' == get_option( 'html_type' ) ) {
			$attributes[] = "lang=\"$lang\"";
		}
		// to use both - use the hook - head_language_attributes
		if ( 'text/html' != get_option( 'html_type' ) ) {
			$attributes[] = "xml:lang=\"$lang\"";
		}

		$output = implode( ' ', $attributes );

		return $output;
	}

	/**
	 * modify insert language metas in head (via wp_head)
	 *
	 * @since 0.9.7.6
	 * @updated 1.1.8
	 * @must be defined in functions.php according general theme design (wp_head)
	 *
	 */
	public function head_insert_language_metas() {
		$curlang = $this->curlang;
		$undefined = $this->langstate;
		echo '<!-- multilingual website powered with xili-language v. ' . XILILANGUAGE_VER . ' ' . $this->lpr . " WP plugin of dev.xiligroup.com -->\n";
		if ( has_filter( 'head_insert_language_metas' ) ) {
			return apply_filters( 'head_insert_language_metas', $curlang, $undefined );
		}
	}

	/**
	 * insert hreflang link in head (via wp_head)
	 *
	 * @since 2.5
	 * as commented in Google rel="alternate"
	 *
	 * to change rules or be compatible with cpt and taxonomy use head_insert_hreflang_link filter
	 *
	 */
	public function head_insert_hreflang_link() {
		if ( has_filter( 'head_insert_hreflang_link' ) ) {
			return apply_filters( 'head_insert_hreflang_link', $this->curlang );
		}
		global $post, $cat;
		if ( is_front_page() || is_category() ) {
			$listlanguages = $this->get_listlanguages();
			$currenturl = $this->current_url( $this->lang_perma );
			foreach ( $listlanguages as $language ) {
				if ( $language->slug != $this->curlang ) {
					if ( is_category() ) {
						$category = get_category( $cat ); // test targets count
						if ( 0 < $this->count_posts_in_taxonomy_and_lang( 'category', $category->slug, $language->slug ) ) {
							$do_it = true; //
						} else {
							$do_it = false;
						}
					} else {
						$do_it = true;
					}
					if ( $do_it ) {
						$lang = str_replace( '_', '-', $language->name );
						$hreflang = ( $this->lang_perma ) ? str_replace( '%lang%', $language->slug, $currenturl ) :
							add_query_arg(
								array(
									QUETAG => $language->slug,
								),
								$currenturl
							);
						printf( '<link rel="alternate" hreflang="%s" href="%s" />' . "\n", $lang, $hreflang );
					}
				}
			}
			if ( is_front_page() ) {
				printf( '<link rel="alternate" hreflang="%s" href="%s" />' . "\n", 'x-default', trailingslashit( get_bloginfo( 'url' ) ) );
			}
		} elseif ( is_singular() ) {
			$listlanguages = $this->get_listlanguages();
			foreach ( $listlanguages as $language ) {
				$targetpost = $this->linked_post_in( $post->ID, $language->slug );
				if ( $language->slug != $this->curlang && ! empty( $targetpost ) ) {
					$hreflang = $this->link_of_linked_post( $post->ID, $language->slug );
					$lang = str_replace( '_', '-', $language->name );
					printf( '<link rel="alternate" hreflang="%s" href="%s" />' . "\n", $lang, $hreflang );
				}
			}
		}
	}

}
