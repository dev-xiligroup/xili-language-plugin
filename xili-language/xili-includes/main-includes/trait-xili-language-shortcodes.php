<?php
namespace Xili_Main;

/**
 * @package  xili-language
 * @subpackage main class
 * functions for shortcodes in front
 */
trait Xili_Language_Shortcodes {

	/**
	 * SHORTCODE: insert translated msgid content according current language
	 *
	 * [xili18n msgid='yes']
	 * [xili18n msgid='yes' ctxt='front']
	 * [xili18n msgid='yes' ctxt='front' textdomain='default'] - core wp language file
	 * return with only em strong br
	 * return '' if issues in textdomain or msgid
	 *
	 * @since 2.12.0
	 */
	public function xili18n_shortcode( $atts, $content = null ) {
		extract(
			shortcode_atts(
				array(
					'msgid' => '',
					'textdomain' => $this->thetextdomain, // by default theme textdomain
					'ctxt' => '', // context to adapt translation
				),
				$atts
			)
		);
		if ( $msgid && $textdomain ) {
			if ( $ctxt ) {
				$string = xl_x( $msgid, $ctxt, $textdomain );

			} else {
				$string = xl__( $msgid, $textdomain );
			}
			$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
			$string = strip_tags( $string, '<em><strong><br>' );
			return $string;
		} else {
			return '';
		}
	}

	/**
	 * SHORTCODE: display only content if current language
	 *
	 * [xili-show-if lang=fr_FR ]contenu de la page boutique multilingue[/xili-show-if]
	 * [xili-show-if lang=en_us ]content of multilingual eshop[/xili-show-if]
	 *
	 * use in lang slug or ISO
	 * return '' if issues in lang
	 *
	 * @since 2.13.3
	 */
	public function xili_content_if_shortcode( $atts, $content = null ) {
		extract(
			shortcode_atts(
				array(
					'lang' => '',
				),
				$atts
			)
		);
		if ( 'every' == $lang ) {
			return $content;
		}
		if ( '' == $lang ) {
			return '';
		}
		$slug = $this->curlang;
		if ( $slug ) {
			$language = get_term_by( 'slug', $slug, TAXONAME, ARRAY_A );
			if ( $language['slug'] == $lang || $language['name'] == $lang ) {
				return $content;
			}
		}
		return '';
	}

	/**
	 * Shortcode inside post content
	 * [linked-post-in lang="fr_fr"]Voir cet article[/linked-post-in]
	 *
	 */
	public function build_linked_posts_shortcode( $atts, $content = null ) {
		global $post;
		extract(
			shortcode_atts(
				array(
					'lang' => '',
					'title' => '',
					'context' => 'linktitle', // for adapt translation
				),
				$atts
			)
		);
		$otherpost = 0;
		$language = xiliml_get_language( $lang ); /* test if lang is available */

		if ( false != $language ) {
			$otherpost = $this->linked_post_in( $post->ID, $language->slug );
		}

		if ( $otherpost ) {
			if ( '' == $title ) {
				$obj_lang = xiliml_get_lang_object_of_post( $otherpost );
				if ( false !== $obj_lang ) {
					$description = $obj_lang->description;
					if ( $context ) {
						$text_title = xl_x( 'A similar post in %s', $context, $this->thetextdomain );
						$language_name = xl_x( $description, $context, $this->thetextdomain );
					} else {
						$text_title = xl__( 'A similar post in %s', $this->thetextdomain );
						$language_name = xl__( $description, $this->thetextdomain );
					}
					$title = esc_attr( sprintf( $text_title, $language_name ) );
				} else {
					$title = esc_attr( __( 'Error with target post #', 'xili-language' ) ) . $otherpost;
				}
			}
			$output = '<a href="' . get_permalink( $otherpost ) . '" title="' . $title . '">' . $content . '</a>';
			/* this link above can be enriched by image or flag inside $content */
		} else {
			$output = '<a href="#" title="' . esc_attr__( 'Error: other post not present !!!', 'xili-language' ) . '">' . $content . '</a>';
		}
		return $output;
	}

	/**
	 * SHORTCODE: display multiple languages selector form
	 *
	 * insertable in search form via theme functions.php
	 *
	 *
	 * @since 2.22
	 *
	 */
	public function multiple_lang_selector( $atts, $content = null ) {
		global $wp_query;
		$atts = shortcode_atts(
			array(
				'option' => 'list', // 'form' == insert button
				'before-list' => '<p>', // ul
				'after-list' => '</p>', // /ul
				'before-line' => '', // li
				'after-line' => '<br />', // ul
				'button' => esc_attr_x( 'Search', 'submit button' ), // see search form
				'button-class' => 'submit', // search-submit = vertical in 2016
			),
			$atts
		);

		$listlanguages = $this->get_list_language_objects();
		// form
		// checkbox
		// button
		$form = '';
		if ( 'form' == $atts['option'] ) {
			$form .= '<form role="search" method="get" class="search-form" action="' . esc_url( home_url( '/' ) ) . '">';
		}
		$form .= $atts['before-list'];
		foreach ( $listlanguages as $slug => $language ) {
			if ( isset( $wp_query->query_vars[ QUETAG ] ) ) {
				$lang_list = explode( ',', $wp_query->query_vars[ QUETAG ] );
				$true = in_array( $this->lang_slug_qv_trans( $slug ), $lang_list );
				$checked = checked( $true, true, false );
			} else {
				$checked = '';
			}
			$form .= $atts['before-line'] . '<input name="mlang[]" type="checkbox" value="' . $this->lang_slug_qv_trans( $slug ) . '" ' . $checked . '/>' . $language->english_name . $atts['after-line'];
		}
		$form .= $atts['after-list']; //

		if ( 'form' == $atts['option'] ) {
			$form .= '<input type="submit" class="' . $atts['button-class'] . '" value="' . xl_esc_attr_x( $atts['button'], 'submit button' ) . '" />';
			$form .= '</form>';
		}

		return $form;
	}


	/**
	 * SHORTCODE: return URI of flag in a language
	 *
	 * <img src="[xili-flag lang=es_es]" width="16" height="12" class="alignnone" />
	 *
	 * use in lang slug
	 * return '' if issues in lang or flag
	 *
	 * @since 2.15
	 * @updated 2.16.4
	 */
	public function xili_multilingual_flag( $atts, $content = null ) {
		extract(
			shortcode_atts(
				array(
					'lang' => '',
					'src' => 0,
				),
				$atts
			)
		);
		if ( '' == $lang ) {
			return '';
		}
		// search attachement

		$post_id = $this->get_flag_series( $lang );
		// return URI
		if ( $post_id ) {
			switch ( $src ) {
				case 1:
					return wp_get_attachment_image_src( $post_id ); // array ( url, width, height)
				case 2:
					$desc = wp_get_attachment_image_src( $post_id ); // array ( url, width, height)
					return ' src="' . $desc[0] . '" width="' . $desc[1] . '" height="' . $desc[2] . '" ';
				default:
					return wp_get_attachment_url( $post_id );
			}
		} else {
			// search if default value available in theme
			global $_wp_theme_features;
			if ( isset( $_wp_theme_features['custom_xili_flag'][0][ $lang ] ) ) {
				$url = sprintf( $_wp_theme_features['custom_xili_flag'][0][ $lang ]['path'], get_template_directory_uri(), get_stylesheet_directory_uri() );
				$width = $_wp_theme_features['custom_xili_flag'][0][ $lang ]['width'];
				$height = $_wp_theme_features['custom_xili_flag'][0][ $lang ]['height'];
				switch ( $src ) {
					case 1:
						return array( $url, $width, $height );
					case 2:
						return ' src="' . $url . '" width="' . $width . '" height="' . $height . '" ';
					default:
						return $url;
				}
			} else {  // not predeclared by theme author
				// search if available in themes/current_theme/images/flags/ - 2.16.4
				// and /current_theme/assets/images/flags/ (2017)
				$path_root = get_stylesheet_directory();
				$path = '%2$s%3$s' . $lang . '.png';
				$folder = '';
				if ( file_exists( sprintf( $path, '', $path_root, '/images/flags/' ) ) ) {
					$folder = '/images/flags/';
				} elseif ( file_exists( sprintf( $path, '', $path_root, '/assets/images/flags/' ) ) ) {
					$folder = '/assets/images/flags/';
				}

				if ( $folder ) {
					$url = get_stylesheet_directory_uri() . $folder . $lang . '.png'; // only in current (child or not)
					$width = 16;
					$height = 11; // as default flag shipped in plugin (this case is patch - webmaster must declare or upload)
					switch ( $src ) {
						case 1:
							return array( $url, $width, $height );
						case 2:
							return ' src="' . $url . '" width="' . $width . '" height="' . $height . '" ';
						default:
							return $url;
					}
				} else {
					return '';
				}
			}
		}
	}

}
