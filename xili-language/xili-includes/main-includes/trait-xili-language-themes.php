<?php
namespace Xili_Main;

/**
 * @package  xili-language
 * @subpackage main class
 * functions for menus in front
 */
trait Xili_Language_Themes {

	//********************************************//
	// Functions for themes (hookable by add_action() in functions.php - 0.9.7
	//********************************************//

	/**
	 * List of available languages.
	 *
	 * @since 0.9.0
	 * @updated 0.9.7.4 - 0.9.8.3 - 0.9.9.6 - 1.5.5 (add class current-lang in <a>)
	 * @updated 1.6.0 - new option for nav menu hook and echoing 4th param - better permalink
	 * @updated 1.8.1 - delete 'in' prefix in list - class if LI
	 * can be hooked by add_action in functions.php
	 * with : add_action('xili_language_list','my_infunc_language_list',10,4);
	 *
	 * for multiple widgets since 0.9.9.6, 1.6.0 : incorporate options
	 * @updated 2.11.1
	 *
	 * @param $before = '<li>', $after ='</li>'.
	 * @return list of languages of site for sidebar list.
	 */
	public function xili_language_list( $before = '<li>', $after = '</li>', $option = '', $echo = true, $hidden = false ) {
		// new way to add parameters now and in future

		if ( is_array( $before ) ) {
			$default = array(
				'before' => '<li>>',
				'after' => '</li>',
				'option' => '',
				'echo' => true,
				'hidden' => false,
				'flagstyle' => false,
			);
			extract( shortcode_atts( $default, $before ) );
		}

		global $post, $wp_query;
		$lang_perma = $this->lang_perma; // since 2.1.1

		$before_class = false;
		if ( '.>' == substr( $before, -2 ) || ! empty( $flagstyle ) ) {
			// tips to add dynamic class in before - now flagstyle since 2.20.3
			$before_class = true;
			$before = str_replace( '.>', '>', $before );
		}
		$listlanguages = $this->get_list_language_objects();
		$a = ''; // 1.6.1

		if ( 'typeone' == $option ) {
			/* the rules : don't display the current lang if set and add link of category if is_category()*/

			foreach ( $listlanguages as $slug => $language ) {
				$this->doing_list_language = $language->slug;
				$currenturl = $this->current_url( $lang_perma ); // 2.5
				$language_qv = $this->lang_slug_qv_trans( $language->slug );
				$display = ( $hidden && ( 0 == $language->visibility ) ) ? false : true;
				if ( the_curlang() != $language->slug && $display ) {
					$beforee = ( $before_class && '<li>' == $before ) ? '<li class="lang-' . $language->slug . '" >' : $before;
					$class = ' class="lang-' . $language->slug . '"';

					$link = ( $lang_perma ) ? str_replace( '%lang%', $language_qv, $currenturl ) :
						add_query_arg(
							array(
								QUETAG => $language_qv,
							),
							$currenturl
						);

					if ( $flagstyle ) { // 2.20.3
						$beforee = '<li' . $class . '>';
						$a .= $beforee . '<a href="' . $link . '" title="'
						. esc_attr( sprintf( xl__( $this->xili_settings['list_link_title']['post_selected'], $this->thetextdomain ), xl_x( $language->description, 'linktitle', $this->thetextdomain ) ) ) . '" >' .
						 xl__( $language->description, $this->thetextdomain )
						. '</a>' . $after;
					} else {
						$beforee = ( $before_class && '<li>' == $before ) ? '<li class="lang-' . $language->slug . '" >' : $before;
						$a .= $beforee . '<a ' . $class . ' href="' . $link . '" title="' . esc_attr( sprintf( xl__( $this->xili_settings['list_link_title']['post_selected'], $this->thetextdomain ), xl_x( $language->description, 'linktitle', $this->thetextdomain ) ) ) . '" >' . xl__( $language->description, $this->thetextdomain ) . '</a>' . $after;
					}
				}
			}
			$this->doing_list_language = false;
		} elseif ( 'typeonenew' == $option ) { // 2.1.0
			/* the rules : don't display the current lang if set and add link of category if is_category() but display linked singular */

			foreach ( $listlanguages as $slug => $language ) {
				$this->doing_list_language = $language->slug;
				$currenturl = $this->current_url( $lang_perma ); // 2.5
				$language_qv = $this->lang_slug_qv_trans( $language->slug );
				$display = ( $hidden && ( 0 == $language->visibility ) ) ? false : true;
				if ( the_curlang() != $language->slug && $display ) {

					$class = ' class="lang-' . $language->slug . '"';

					if ( ( is_single() || is_page() ) && ! is_front_page() ) {
						$link = $this->link_of_linked_post( $post->ID, $language->slug );
						$title = sprintf( xl__( $this->xili_settings['list_link_title']['current_post'], $this->thetextdomain ), xl_x( $language->description, 'linktitle', $this->thetextdomain ) );
					} elseif ( $wp_query->is_posts_page ) { // 2.8.4
						$link = $this->link_of_linked_post( get_option( 'page_for_posts' ), $language->slug );
						$title = sprintf( xl__( $this->xili_settings['list_link_title']['latest_posts'], the_theme_domain() ), xl_x( $language->description, 'linktitle', $this->thetextdomain ) );
					} else {
						$link = ( $lang_perma ) ? str_replace( '%lang%', $language_qv, $currenturl ) :
							add_query_arg(
								array(
									QUETAG => $language_qv,
								),
								$currenturl
							);
						$title = sprintf( xl__( $this->xili_settings['list_link_title']['post_selected'], $this->thetextdomain ), xl_x( $language->description, 'linktitle', $this->thetextdomain ) );
					}

					if ( $flagstyle ) { // 2.20.3
						$beforee = '<li' . $class . '>';
						$a .= $beforee . '<a href="' . $link . '" title="'
						. esc_attr( $title ) . '" >'
						. xl__( $language->description, $this->thetextdomain )
						. '</a>' . $after;
					} else {
						$beforee = ( $before_class && '<li>' == $before ) ? '<li class="lang-' . $language->slug . '" >' : $before;
						$a .= $beforee . '<a ' . $class . ' href="' . $link . '" title="'
						. esc_attr( $title ) . '" >'
						. xl__( $language->description, $this->thetextdomain )
						. '</a>' . $after;
					}
				}
			}
			$this->doing_list_language = false;

		} elseif ( in_array( $option, array( 'navmenu', 'navmenu-a' ) ) ) {
			/* current list in nav menu 1.6.0 */
			if ( $lang_perma ) {
				$currenturl = get_bloginfo( 'url' ) . '/%lang%/';
			} else {
				$currenturl = get_bloginfo( 'url' );
			}
			foreach ( $listlanguages as $language ) {

				if ( ! ( 'navmenu-a' == $option && $language->slug == the_curlang() ) ) { // 2.8.4.3
					$language_qv = $this->lang_slug_qv_trans( $language->slug );
					$display = ( $hidden && ( 0 == $language->visibility ) ) ? false : true;
					if ( $display ) {
						if ( the_curlang() != $language->slug ) {
							$class = " class='menu-item menu-item-type-custom lang-" . $language->slug . "'";
						} else {
							$class = " class='menu-item menu-item-type-custom lang-" . $language->slug . " current-lang current-menu-item'";
						}
						$beforee = ( substr( $before, -1 ) == '>' ) ? str_replace( '>', ' ' . $class . ' >', $before ) : $before;

						$link = ( $lang_perma ) ? str_replace( '%lang%', $language_qv, $currenturl ) :
						add_query_arg(
							array(
								QUETAG => $language_qv,
							),
							$currenturl
						);

						$a .= $beforee . '<a href="' . $link . '" title="' . esc_attr( sprintf( xl__( $this->xili_settings['list_link_title']['post_selected'], $this->thetextdomain ), xl_x( $language->description, 'linktitle', $this->thetextdomain ) ) ) . '" >' . xl__( $language->description, $this->thetextdomain ) . '</a>' . $after;
					}
				}
			}
		} elseif ( in_array( $option, array( 'navmenu-1', 'navmenu-1a', 'navmenu-1ao' ) ) ) {
			// 2.1.1 and single

			foreach ( $listlanguages as $language ) {
				$link = false;
				if ( ! ( ( 'navmenu-1a' == $option || 'navmenu-1ao' == $option ) && the_curlang() != $language->slug ) ) {
					// 2.8.4.3

					$language_qv = $this->lang_slug_qv_trans( $language->slug );
					$display = ( $hidden && ( 0 == $language->visibility ) ) ? false : true;
					if ( $display ) {

						if ( the_curlang() != $language->slug ) {
							$class = " class='menu-item menu-item-type-custom lang-" . $language->slug . "'";
						} else {
							$class = " class='menu-item menu-item-type-custom lang-" . $language->slug . " current-lang current-menu-item'";
						}

						if ( ( is_singular() && ! is_front_page() ) ) {
							if ( 'navmenu-1a' == $option ) {
								$link = $this->link_of_linked_post( $post->ID, $language->slug );
							} else {
								$targetpost = $this->linked_post_in( $post->ID, $language->slug );
								if ( $targetpost ) {
									$link = get_permalink( $targetpost );
								}
							}
							$title = sprintf( xl__( $this->xili_settings['list_link_title']['current_post'], the_theme_domain() ), xl__( $language->description, $this->thetextdomain ) );
						} elseif ( $wp_query->is_posts_page ) {
							// 2.8.4
							$link = $this->link_of_linked_post( get_option( 'page_for_posts' ), $language->slug );
							$title = sprintf( xl__( $this->xili_settings['list_link_title']['latest_posts'], the_theme_domain() ), xl_x( $language->description, 'linktitle', $this->thetextdomain ) );
						} else {
							$this->doing_list_language = $language->slug;
							$currenturl = $this->current_url( $lang_perma ); // 2.5
							$link = ( $lang_perma ) ? str_replace( '%lang%', $language_qv, $currenturl ) :
								add_query_arg(
									array(
										QUETAG => $language_qv,
									),
									$currenturl
								);
							$title = sprintf( xl__( $this->xili_settings['list_link_title']['post_selected'], the_theme_domain() ), xl_x( $language->description, 'linktitle', $this->thetextdomain ) );
						}

						$beforee = ( substr( $before, -1 ) == '>' ) ? str_replace( '>', ' ' . $class . ' >', $before ) : $before;
						if ( $link ) {
							if ( $link ) {
								$a .= $beforee
								. '<a href="' . apply_filters( 'xiliml_language_list_link', $link, $option, $language->slug, $language_qv ) . '" title="' . esc_attr( $title ) . '" >'
								. xl__( $language->description, $this->thetextdomain ) . '</a>' . $after;
							}
						}
					}
				}
			}
			$this->doing_list_language = false;

		} else {
			/* current list only root */

			foreach ( $listlanguages as $language ) {
				$language_qv = $this->lang_slug_qv_trans( $language->slug );
				$display = ( $hidden && ( 0 == $language->visibility ) ) ? false : true;

				if ( $display ) {
					if ( the_curlang() != $language->slug ) {
						$class = " class='lang-" . $language->slug . "'";
					} else {
						$class = " class='lang-" . $language->slug . " current-lang'";
					}

					$link = ( $lang_perma ) ? str_replace( '%lang%', $language_qv, get_bloginfo( 'url' ) . '/%lang%/' ) :
					add_query_arg(
						array(
							QUETAG => $language_qv,
						),
						get_bloginfo( 'url' )
					);

					if ( $flagstyle ) {
						// 2.20.3
						$beforee = '<li' . $class . '>';
						$a .= $beforee . '<a href="' . $link . '" title="'
						. esc_attr( sprintf( xl__( $this->xili_settings['list_link_title']['post_selected'], $this->thetextdomain ), xl_x( $language->description, 'linktitle', $this->thetextdomain ) ) ) . '" >' .
						 xl__( $language->description, $this->thetextdomain )
						. '</a>' . $after;
					} else {
						$beforee = ( $before_class && '<li>' == $before ) ? '<li class="lang-' . $language->slug . '" >' : $before;
						$a .= $beforee . '<a ' . $class . ' href="' . $link . '" title="' . esc_attr( sprintf( xl__( $this->xili_settings['list_link_title']['post_selected'], $this->thetextdomain ), xl_x( $language->description, 'linktitle', $this->thetextdomain ) ) ) . '" >' . xl__( $language->description, $this->thetextdomain ) . '</a>' . $after;
					}
				}
			}
		}
		if ( $echo ) {
			echo $a;
		} else {
			return $a;
		}
	}

	/**
	 * language of current post used in loop
	 * @since 0.9.0
	 *
	 * @updated 2.5.1
	 *
	 * @param $before = '<span class"xili-lang">(', $after =')</span>'.
	 * @return language of post.
	 */
	public function xili_post_language( $before = '<span class="xili-lang">(', $after = ')</span>', $type = 'iso' ) {
		global $post;
		$langpost = $this->get_post_language( $post->ID, $type );

		if ( '' != $langpost ) :
			$curlangname = $langpost;
		else :
			$curlangname = xl__( 'undefined', $this->thetextdomain );
		endif;
		$a = $before . $curlangname . $after . '';
		echo $a;
	}

	/**
	 * for one post create a link list of the corresponding posts in other languages
	 *
	 * @since 0.9.0
	 * @updated 0.9.9.2 / 3 $separator replace $after, $before contains pre-text to echo a better list.
	 * @updated 1.1 - see hookable same name function outside class
	 * @updated 2.8.8 - if type == "", return html list
	 * can be hooked by add_action in functions.php
	 *
	 *
	 */
	public function the_other_posts( $post_id, $before = 'This post in', $separator = ', ', $type = 'display' ) {
		/* default here*/
		$outputarr = array();
		$output = '';

		$listlanguages = get_terms( TAXONAME, array( 'hide_empty' => false ) );
		$langpost = $this->get_cur_language( $post_id ); // to be used in multilingual loop since 1.1
		$post_lang = $langpost[ QUETAG ];
		foreach ( $listlanguages as $language ) {
			$otherpost = $this->linked_post_in( $post_id, $language->slug ); //get_post_meta($post_id, 'lang-'.$language->slug, true);

			if ( 'display' == $type || '' == $type ) {
				// 2.8.8
				if ( '' != $otherpost && $language->slug != $post_lang ) {
					$outputarr[] = "<a href='" . get_permalink( $otherpost ) . "' >" . xl_x( $language->description, 'otherposts', $this->thetextdomain ) . '</a>';
				}
			} elseif ( 'array' == $type ) {
				// here don't exclude cur lang
				if ( '' != $otherpost ) {
					$outputarr[ $language->slug ] = $otherpost;
				}
			}
		}
		if ( 'display' == $type || '' == $type ) {
			// 2.8.8
			if ( ! empty( $outputarr ) ) {
				$output = ( ( '' != $before ) ? xl__( $before, $this->thetextdomain ) . ' ' : '' ) . implode( $separator, $outputarr );
				if ( 'display' == $type ) {
					echo $output;
				} else {
					return $output;
				}
			} elseif ( '' == $type ) {
				return '';
			}
		} elseif ( 'array' == $type ) {
			if ( ! empty( $outputarr ) ) {
				$outputarr[ $post_id ] = $post_lang;
				// add a key with curid to give his lang (empty if undefined)
				return $outputarr;
			} else {
				return false;
			}
		}
	}

	/**
	 * Add list of languages in radio input - for search form.
	 *
	 * @since 0.9.7
	 * can be hooked by add_action in functions.php
	 *
	 * @updated 0.9.9.5, 1.8.2, 2.2.0 , 2.2.2, 2.8.6
	 *
	 * $before, $after each line of radio input
	 *
	 * @param $before, $after.
	 * @return echo the form.
	 */
	public function xiliml_langinsearchform( $before = '', $after = '', $echo = true ) {
			/* default here*/
		global $wp_query;
		$listlanguages = get_terms( TAXONAME, array( 'hide_empty' => false ) );
		$a = '';
		foreach ( $listlanguages as $language ) {
			if ( is_search() ) {
				if ( isset( $wp_query->query_vars[ QUETAG ] ) ) { // to rebuilt form after search query
					$checked = checked( $language->slug, $this->lang_qv_slug_trans( $wp_query->query_vars[ QUETAG ] ), false ); //2.2.2
				} else {
					$checked = '';
				}
			} else {
				$checked = checked( $language->slug, $this->curlang, false );
			}
			$a .= $before . '<input onClick="if(this.form.clear.checked) { this.form.clear.checked = false; }" type="radio" name="' . QUETAG . '" value="' . $language->slug . '" id="' . QUETAG . '-' . $language->slug . '" ' . $checked . ' />' . xl_esc_attr_x( $language->description, 'searchform', $this->thetextdomain ) . '&nbsp;' . $after;
		}
		// new javascript to uncheck radio buttons	on form named searchform form.
		$a .= $before . '<input type="radio" name="clear" onClick="for (var i=0; i < this.form.' . QUETAG . '.length ; i++) { if(this.form.' . QUETAG . '[i].checked) { this.form.' . QUETAG . '[i].checked = false; } };" />&nbsp;' . xl__( 'All', $this->thetextdomain ) . $after;
		// this to all lang query

		if ( $echo ) {
			echo $a;
		} else {
			return $a;
		}

	}

}
