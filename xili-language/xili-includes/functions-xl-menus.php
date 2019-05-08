<?php
// MENUS add-ons for xili-language

/**
 * insert a dummy items to precede lang list insertion since WP 3.5
 * @since 2.8.3
 */
function xili_nav_menu_args( $args ) {
	global $xili_language;
	if ( isset( $xili_language->xili_settings['navmenu_check_options'] ) ) {
		$navmenu_check_options = $xili_language->xili_settings['navmenu_check_options'];

		$args = (object) $args;

		$menu = wp_get_nav_menu_object( $args->menu );

		if ( ! $menu && $args->theme_location && ( $locations = get_nav_menu_locations() ) && isset( $locations[ $args->theme_location ] ) ) {
			$menu = wp_get_nav_menu_object( $locations[ $args->theme_location ] );
		}

		if ( isset( $navmenu_check_options[ $args->theme_location ] ) && 'enable' == $navmenu_check_options[ $args->theme_location ]['navenable'] ) {

			if ( $menu && ! is_wp_error( $menu ) ) {

				$menu_items = wp_get_nav_menu_items( $menu->term_id, array( 'update_post_term_cache' => false ) );

				if ( empty( $menu_items ) ) {

					$menu_id = wp_update_nav_menu_item(
						$menu->term_id,
						0,
						array(
							'menu-item-title' => 'xili-language un-visible dummy-menu-item',
							'menu-item-url' => '#',
						)
					);
					$menu_id = wp_update_nav_menu_item(
						$menu->term_id,
						$menu_id,
						array(
							'menu-item-title' => 'xili-language un-visible dummy-menu-item',
							'menu-item-url' => '#dummy-link',
							'menu-item-attr-title' => __( 'delete if you add manually another menu item', 'xili-language' ),
						)
					); // wp-includes/nav-menu.php

				}
			}
		}

		// search another virtual locations created by child themes (2010, 2011, 2012 and others (responsive,…))
		$language_slugs_list = array_keys( $xili_language->xili_settings['langs_ids_array'] );
		foreach ( $language_slugs_list as $slug ) {
			$default = 'en_us';
			if ( $slug != $default ) {

				if ( isset( $locations[ $args->theme_location . '_' . $slug ] ) && isset( $navmenu_check_options[ $args->theme_location . '_' . $slug ] ) && 'enable' == $navmenu_check_options[ $args->theme_location . '_' . $slug ]['navenable'] ) {
					$menu = wp_get_nav_menu_object( $locations[ $args->theme_location . '_' . $slug ] );

					if ( $menu && ! is_wp_error( $menu ) ) {

						$menu_items = wp_get_nav_menu_items( $menu->term_id, array( 'update_post_term_cache' => false ) );

						if ( empty( $menu_items ) ) {

							$menu_id = wp_update_nav_menu_item(
								$menu->term_id,
								0,
								array(
									'menu-item-title' => 'xili-language un-visible dummy-menu-item',
									'menu-item-url'   => '#',
								)
							);
							$menu_id = wp_update_nav_menu_item(
								$menu->term_id,
								$menu_id,
								array(
									'menu-item-title'      => 'xili-language un-visible dummy-menu-item',
									'menu-item-url'        => '#dummy-link',
									'menu-item-attr-title' => __( 'delete if you add manually another menu item', 'xili-language' ),
								)
							);

						}
					}
				}
			}
		}

		$args = (array) $args;
	}
	return $args;
}


/**
 * Insert automatically some languages items at end in menu
 * @since 1.6.0
 * @updated 1.7.1 - add optionally wp_page_list result
 * @updated 1.8.1 - choose good menu location
 * @updated 1.8.9 - add filter (example: add_filter ('xili_nav_lang_list', 'my_xili_nav_lang_list', 10, 3);)
 * and class for separator ( example: li.menu-separator a {display:none;})
 *
 * @updated 2.1.0 - for multiple navmenu locations - CAUTION new filter: xili_nav_lang_lists (with s at end)
 * @updated 2.8.3 - for empty items (hack args) wp 3.5
 * @updated 2.8.7 - customize preview
 */
function xili_nav_lang_list( $items, $args ) {
	global $xili_language;

	$preview_options = $xili_language->get_xili_language_options(); // to obtain previewable params

	$li_separator = ( '' != $preview_options['nav_menu_separator'] ) ? '<li class="menu-item menu-separator" ><a>' . $preview_options['nav_menu_separator'] . '</a></li>' : ''; // 2.8.3

	if ( 0 != strpos( $items, '>xili-language un-visible dummy-menu-item<' ) ) {

		$items = preg_replace( '/<li(.*)href="#dummy-link(.*)<\/li>/i', '', $items );
		$li_separator = '';
	}

	if ( 'enable' == $preview_options['in_nav_menu'] ) {

		if ( isset( $preview_options['navmenu_check_options'] ) ) {
			$navmenu_check_options = $preview_options['navmenu_check_options'];

			if ( has_filter( 'xili_nav_lang_lists' ) ) {
				return apply_filters( 'xili_nav_lang_lists', $items, $args, $navmenu_check_options );
			}

			if ( isset( $navmenu_check_options[ $args->theme_location ] ) && 'enable' == $navmenu_check_options[ $args->theme_location ]['navenable'] ) {

				$navmenu = ( '' != $navmenu_check_options[ $args->theme_location ]['navtype'] ) ? $navmenu_check_options[ $args->theme_location ]['navtype'] : 'navmenu-1';

				$end = xili_language_list( '<li>', '</li>', $navmenu, false, true ); // don't display hidden languages

				return $items . $li_separator . $end; // class for display none...

			} else {
				return $items;
			}
		} else { // if settings not updated since updated by admin user
			$navmenu_check_option = $xili_language->xili_settings['navmenu_check_option'];
			if ( has_filter( 'xili_nav_lang_list' ) ) {
				return apply_filters( 'xili_nav_lang_list', $items, $args, $navmenu_check_option );
			}
			if ( $args->theme_location == $navmenu_check_option ) {

				$end = xili_language_list( '<li>', '</li>', 'navmenu', false, true ); // don't display hidden languages 1.8.9.1

				return $items . $li_separator . $end; // class for display none... 1.8.9 no ID for instantiations

			} else {
				return $items;
			}
		}
	}
}

/**
 * Insert automatically some pages items at end in menu
 * @since 1.7.1 - add optionally wp_page_list result
 * @updated 1.8.1 - choose good menu location
 * @updated 1.8.9 - add filter (example: add_filter ('xili_nav_page_list', 'my_xili_nav_page_list', 10, 3);)
 * @updated 2.8.3 - for empty items (hack args) wp 3.5
 * @updated 2.8.4.3 - multi location - new filter - xili_nav_page_list_array - two params
 *
 */
function xili_nav_page_list( $items, $args ) {
	global $xili_language;

	if ( array() != isset( $xili_language->xili_settings['array_navmenu_check_option_page'] ) && $xili_language->xili_settings['array_navmenu_check_option_page'] ) {
		$array_navmenu_check_option_page = $xili_language->xili_settings['array_navmenu_check_option_page'];

		if ( has_filter( 'xili_nav_page_list_array' ) ) {
			return apply_filters( 'xili_nav_page_list_array', $items, $args, $array_navmenu_check_option_page );
		}

		$location_keys = array_keys( $array_navmenu_check_option_page );

		if ( in_array( $args->theme_location, $location_keys ) && 'enable' == $array_navmenu_check_option_page[ $args->theme_location ]['enable'] ) {
			if ( 0 != strpos( $items, '>xili-language un-visible dummy-menu-item<' ) ) {
				if ( '' == $xili_language->xili_settings['in_nav_menu'] ) { // no language list in the menus - need to be erased here
					$items = preg_replace( '/<li(.*)href="#dummy-link(.*)<\/li>/i', '', $items );
				}
			}

			$pagelist = '';
			$pagelist_args = $array_navmenu_check_option_page[ $args->theme_location ]['args'] . '&';
			// sub-selection
			add_filter( 'page_link', 'xili_nav_page_link_insertion_fixe', 10, 3 ); // 2.8.5
			$pagelist = wp_list_pages( $pagelist_args . 'title_li=&echo=0&' . QUETAG . '=' . $xili_language->curlang );
			remove_filter( 'page_link', 'xili_nav_page_link_insertion_fixe' );

			return $items . $pagelist;
		} else {
			return $items;
		}
	} else {
		return $items;
	}
}

// apply_filters( 'page_link', $link, $post->ID, $sample );

/**
 * fixes filter for front-page array
 * @since 2.8.5
 *
 */
function xili_nav_page_link_insertion_fixe( $link, $post_id, $sample ) {
	global $xili_language;
	//$front_page_id = $xili_language->get_option_wo_xili ('page_on_front');
	$list_pages_check_option = $xili_language->xili_settings['list_pages_check_option'];
	if ( $xili_language->show_page_on_front && 'fixe' == $list_pages_check_option && in_array( $post_id, $xili_language->show_page_on_front_array ) ) {
		$post_id = (int) $post_id; // issue with 3.4.2
		$post = get_post( $post_id );
		$link = _get_page_link( $post, false, $sample );
	}
	return $link;
}

/**
 * modify automatically home page item in nav menu - exemple for twentyten child menu
 * @since 1.8.9.2
 *
 * filter ( example: add_filter ('xili_nav_page_home_item', 'my_xili_nav_page_home_item', 10, 5);)
 *
 */
function xili_nav_page_home_item( $item_output, $item, $depth, $args ) {
	global $xili_language;
	$homemenu_check_option = $xili_language->xili_settings['home_item_nav_menu'];
	if ( has_filter( 'xili_nav_page_home_item' ) ) {
		return apply_filters( 'xili_nav_page_home_item', $item_output, $item, $depth, $args, $homemenu_check_option ); // fixed 2.8
	}

	if ( get_option( 'siteurl' ) . '/' == $item->url ) { // page or list
		$curlang = $xili_language->curlang;

		$link = ( $xili_language->lang_perma ) ? str_replace( '%lang%', $xili_language->lang_slug_qv_trans( $curlang ), get_option( 'siteurl' ) . '/%lang%/' ) :
		add_query_arg(
			array(
				QUETAG => $curlang,
			),
			get_option( 'siteurl' )
		);

		$attributes  = ! empty( $item->attr_title ) ? ' title="' . esc_attr__( $item->attr_title, $xili_language->thetextdomain ) . '"' : ''; //
		$attributes .= ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
		$attributes .= ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : '';
		$attributes .= ! empty( $item->url ) ? ' href="' . esc_attr( $link ) . '"' : ''; // 2.9.22
		$attributes .= ! empty( $item->current ) ? 'page' : '';

		$item_output = $args->before;
		$item_output .= '<a' . $attributes . '>';
		$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
		$item_output .= '</a>';
		$item_output .= $args->after;
		return $item_output;
	} else {
		return $item_output;
	}
}


/*
 * sub selection of pages for wp_list_pages()
 * @ since 090504 - exemple of new function add here or addable in functions.php
 * © xiligroup.dev
 *
 * only called if xili-language plugin is active and query tag 'lang' is in wp_list_pages template tag
 *
 * example 1 : wp_list_pages('title_li=&lang='.the_curlang() ); will display only pages of current lang
 *
 * example 2 : wp_list_pages('title_li=&setlang=0&lang='.the_curlang() ); will display pages of current lang AND pages with lang undefined (polyglot pages).
 * example 3 : wp_list_pages('title_li=&echo=0&include=2,10&lang='); will display pages of current lang (new since 2.2.2) useful with xili-widget plugin
 *
 */
function ex_pages_by_lang( $pages, $r ) {
	if ( isset( $r[ QUETAG ] ) && ! empty( $pages ) && function_exists( 'get_cur_post_lang_dir' ) ) {
		$keepundefined = null;
		if ( isset( $r['setlang'] ) ) {
			if ( 0 == $r['setlang'] || 'false' == $r['setlang'] ) {
				$keepundefined = false;
			}
			if ( 1 == $r['setlang'] || 'true' == $r['setlang'] ) {
				$keepundefined = true;
			}
		}
		$resultingpages = array();
		if ( '' == $r[ QUETAG ] ) {
			$r[ QUETAG ] = the_curlang(); // when param is here but empty = cur lang of page - 2.2.2
		}
		foreach ( $pages as $page ) {
			$post_lang_dir = get_cur_post_lang_dir( $page->ID );
			if ( $post_lang_dir === $keepundefined ) {
					$resultingpages[] = $page;
			} elseif ( $post_lang_dir[ QUETAG ] == $r[ QUETAG ] ) {
					$resultingpages[] = $page;
			}
		}
		return $resultingpages;
	} else {
		return $pages;
	}
}
add_filter( 'get_pages', 'ex_pages_by_lang', 10, 2 );
