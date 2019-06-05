<?php
namespace Xili_Main;

/**
 * @package  xili-language
 * @subpackage main class
 * functions for menus in front
 */
trait Xili_Language_Menus {

	/**
	 * to add links in current menu - manual insertion in dashboard (obsolete soon)
	 *
	 * used in languages_expert tab
	 *
	 */
	public function add_list_of_language_links_in_wp_menu( $location ) {
		$defaultarray = array(
			'menu-item-type' => 'custom',
			'menu-item-title' => '',
			'menu-item-url' => '',
			'menu-item-description' => '',
			'menu-item-status' => 'publish',
			'menu-item-aria-current' => '',
		);
		$url = get_bloginfo( 'url' );
		$listlanguages = get_terms_of_groups_lite( $this->langs_group_id, TAXOLANGSGROUP, TAXONAME, 'ASC' );
		$langdesc_array = array();
		foreach ( $listlanguages as $language ) {
			$langdesc_array[] = $language->description;
		}
		/* detect menu inside location */
		$menu_locations = get_nav_menu_locations();
		$menuid = $menu_locations[ $location ];
		$menuitem = wp_get_nav_menu_object( $menuid );
		$items = get_objects_in_term( $menuitem->term_id, 'nav_menu' );
		$nothere = true;
		if ( ! empty( $items ) ) {
			$founditems = wp_get_nav_menu_items( $menuid ); //try to see if a previous insert was done
			foreach ( $founditems as $item ) {
				if ( '|' == $item->title || in_array( $item->title, $langdesc_array ) ) {
					$nothere = false;
					break;
				}
			}
		}
		if ( true == $nothere ) {
			/* add separator */
				$defaultarray['menu-item-title'] = '|';
				$defaultarray['menu-item-url'] = $url . '/#';
				wp_update_nav_menu_item( $menuid, 0, $defaultarray );
			foreach ( $listlanguages as $language ) {
				$defaultarray['menu-item-title'] = $language->description;
				$defaultarray['menu-item-url'] = $url . '/?' . QUETAG . '=' . $language->slug;
				//$defaultarray['menu-item-aria-current'] = '';
				wp_update_nav_menu_item( $menuid, 0, $defaultarray );
			}
			return __( 'language items added', 'xili-language' );
		} else {
			return __( 'seems to be set', 'xili-language' );
		}
	}

	/**
	 * insert languages list objects in nav menu at insertion point (filter wp_nav_menu_objects)
	 *
	 * @since 2.8.8
	 * @updated 2.9.11 (page) , 2.9.20 (menu)
	 * @updated 2.10.1 - singular if exists
	 * @updated 2.11.2 - better class assignation (ancestor) - thanks to Bastian
	 * @updated 2.12.2 - compatible
	 *
	 */
	public function insert_language_objects_in_nav_menu( $sorted_menu_items, $args ) {
		global $post, $wp_query;
		// detect insertion point menu object and menu type

		$new_sorted_menu_items = array();

		foreach ( $sorted_menu_items as $key => $menu_object ) {

			if ( $menu_object->url == $this->insertion_point_dummy_link_menu ) { // #insertmenu

				$queried_object = $wp_query->get_queried_object();
				$queried_object_id = (int) $wp_query->queried_object_id;

				if ( false !== strpos( $menu_object->attr_title, 'menu-wo-' ) ) {
					$langkey = explode( '-', str_replace( 'menu-wo-', '', $menu_object->attr_title ) ); // approach < 2.14.2
				} else {
					$langkey_ids = explode( '-', str_replace( 'menu-wlid-', '', $menu_object->attr_title ) );
					$langkey = array();
					$id_slug = array_flip( $this->langs_ids_array );
					foreach ( $langkey_ids as $lang_id ) {
						$langkey[] = $id_slug[ $lang_id ];
					}
				}

				$menu_id_list = '';
				$menu_slug_list = '';

				foreach ( $menu_object->classes as $one_class ) {
					if ( false !== strpos( $one_class, 'xlmenulist-' ) ) {
						$menu_id_list = str_replace( 'xlmenulist-', '', $one_class );
						continue;
					} elseif ( false !== strpos( $one_class, 'xlmenuslug' ) ) { // to be compatible with export xml
						$menu_slug_list = str_replace( 'xlmenuslug' . $this->menu_slug_sep, '', $one_class ); // -- seems better than _
						continue;
					}
				}
				if ( $menu_id_list ) {
					$menu_ids = explode( '-', $menu_id_list ); // here saved as term_id (container of menu items) (<2.12.2)

				} elseif ( $menu_slug_list ) {
					$menu_slugs = explode( $this->menu_slug_sep, $menu_slug_list );
					foreach ( $menu_slugs as $one_slug ) {
						$term_data = term_exists( $one_slug, 'nav_menu' );
						$menu_ids[] = ( is_array( $term_data ) ) ? $term_data['term_id'] : 0;
					}
				}

				$menu_list = ( count( $langkey ) == count( $menu_ids ) ) ? array_combine( $langkey, $menu_ids ) : array();  // pb in count

				$curlang = the_curlang();

				if ( isset( $menu_list [ $curlang ] ) ) {

					$menu_structure_exists = ( term_exists( (int) $menu_list[ $curlang ], 'nav_menu' ) ) ? true : false;

				} else {
					$menu_structure_exists = false;
				}

				if ( $curlang && $menu_structure_exists ) {

					$menu_items = wp_get_nav_menu_items( $menu_list[ $curlang ] ); // need term_id of structure
					_wp_menu_item_classes_by_context( $menu_items ); // added 2.11.2
					if ( $menu_items ) {
						// added in 1.8 to sort inserted menu content and to insert class 'menu-item-has-children' as in nav-menu-template.php
						$sorted_menu_items = $menu_items_with_children = array();
						foreach ( (array) $menu_items as $menu_item ) {
							$sorted_menu_items[ $menu_item->menu_order ] = $menu_item;
							if ( $menu_item->menu_item_parent ) {
								$menu_items_with_children[ $menu_item->menu_item_parent ] = true;
							}
						}

						// Add the menu-item-has-children class where applicable
						if ( $menu_items_with_children ) {
							foreach ( $sorted_menu_items as &$menu_item ) {
								if ( isset( $menu_items_with_children[ $menu_item->ID ] ) ) {
									$menu_item->classes[] = 'menu-item-has-children';
								}
							}
						}

						unset( $menu_items, $menu_item );

						foreach ( $sorted_menu_items as $new_menu_item ) {
							// not recursive : impossible to decode insertion point inside menu
							if ( ! in_array( $new_menu_item->url, array( $this->insertion_point_dummy_link_menu, $this->insertion_point_dummy_link_page, $this->insertion_point_dummy_link ) ) ) {

								$new_classes = array( 'insertion-point' );

								if ( 0 == $new_menu_item->menu_item_parent ) {
									$new_menu_item->menu_item_parent = $menu_object->menu_item_parent; // heritate from insertion point
								}

								$new_menu_item->classes = array_merge( $menu_object->classes, $new_classes, $new_menu_item->classes ); // fixed 2.11.2

								$new_sorted_menu_items[] = $new_menu_item;

							}
						}
					}
				}
			} elseif ( $menu_object->url == $this->insertion_point_dummy_link_page ) {
				// #insertpagelist
				$classes = $menu_object->classes;

				$i = 0;

				$defaults = array(
					'sort_order' => 'ASC',
					'sort_column' => 'menu_order',
					'hierarchical' => 1,
					QUETAG => $this->curlang,
				);

				$r = wp_parse_args( $menu_object->attr_title, $defaults );
				extract( $r, EXTR_SKIP );

				$pagelist = get_pages( $r );

				foreach ( $pagelist as $onepage ) {

					$class = ( is_page( $onepage->ID ) ) ? ' current-menu-item' : '';
					$i++;

					$new_lang_menu_item = (object) array();
					$id = $menu_object->ID * 1000 + $i;
					$new_lang_menu_item->ID = $id;
					$new_lang_menu_item->url = get_permalink( $onepage->ID ); // $onepage->guid;
					$new_lang_menu_item->title = $onepage->post_title;
					$new_lang_menu_item->attr_title = apply_filters( 'xl_nav_menu_page_attr_title', '...', $onepage->ID );
					$new_lang_menu_item->description = apply_filters( 'xl_nav_menu_page_description', '', $onepage->ID ); // for twentyfifteen 2.15.4
					$new_lang_menu_item->menu_item_parent = $menu_object->menu_item_parent;
					$new_lang_menu_item->db_id = $menu_object->db_id;
					$new_lang_menu_item->target = $menu_object->target;
					$new_lang_menu_item->xfn = $menu_object->xfn; // 2.23
					$new_lang_menu_item->current = $menu_object->current; // 2.23

					$new_lang_menu_item->classes = array_merge( $menu_object->classes, explode( ' ', $class ) );

					$new_sorted_menu_items[] = $new_lang_menu_item;

				}
			} elseif ( $menu_object->url == $this->insertion_point_dummy_link ) { // language

				$classes = $menu_object->classes;

				$keys = array();

				foreach ( $this->langs_list_options as $one ) {
					$keys[] = $one[0];
				}

				$type_array = array_values( array_intersect( $keys, $menu_object->classes ) );

				$type = $type_array[0];

				$hidden = true; // hidden here as defined in list - only available language are listed

				// create array of language menu objects
				$listlanguages = $this->get_list_language_objects();
				$new_menu_objects = array();
				$i = 0;
				foreach ( $listlanguages as $slug => $language ) {
					$link = false;
					$display = ( $hidden && ( 0 == $language->visibility ) ) ? false : true;

					if ( $display && ( ! ( in_array( $type, array( 'navmenu-a', 'navmenu-1a', 'navmenu-1ao' ) ) && the_curlang() == $language->slug ) ) ) {
						$i++;
						if ( the_curlang() != $language->slug ) {
							$class = 'lang-' . $language->slug;
						} else {
							$class = 'lang-' . $language->slug . ' current-lang current-menu-item';
						}
						$language_qv = $this->lang_slug_qv_trans( $language->slug );

						if ( in_array( $type, array( 'navmenu-1', 'navmenu-1a', 'navmenu-1ao' ) ) ) {
							$this->doing_list_language = $language->slug; // for date filter if lang_perma
							$currenturl = $this->current_url( $this->lang_perma );

							if ( is_singular() && ! is_front_page() ) {
								if ( in_array( $type, array( 'navmenu-1a', 'navmenu-1' ) ) ) {
									// 2.13.3
									$link = $this->link_of_linked_post( $post->ID, $language->slug );
								} else {
									$targetpost = $this->linked_post_in( $post->ID, $language->slug );
									if ( $targetpost ) {
										$link = get_permalink( $targetpost );
									}
								}
								$title = sprintf( xl__( $this->xili_settings['list_link_title']['current_post'], the_theme_domain() ), xl__( $language->english_name, $this->thetextdomain ) );
							} elseif ( $wp_query->is_posts_page ) { // 2.8.4
								$link = $this->link_of_linked_post( get_option( 'page_for_posts' ), $language->slug );
								$title = sprintf( xl__( $this->xili_settings['list_link_title']['latest_posts'], the_theme_domain() ), xl_x( $language->english_name, 'linktitle', $this->thetextdomain ) );

							} elseif ( function_exists( 'xili_tidy_tag_in_other_lang' ) && ( is_tag() || $this->is_tax_improved() ) ) { // 2.9.1

								$q = '';
								if ( ! is_tag() && $this->is_tax_improved() ) {
									$queried_object = $wp_query->get_queried_object();
									$q = '&tidy_post_tag=' . $queried_object->taxonomy;
								}

								if ( $link = xili_tidy_tag_in_other_lang( 'format=term_link&' . QUETAG . '=' . $language->iso_name . $q ) ) {
									$title = xili_tidy_tag_in_other_lang( 'format=term_name&' . QUETAG . '=' . $language->iso_name . $q );
								} else {
									$link = ( $this->lang_perma ) ? str_replace( '%lang%', $language_qv, $currenturl ) :
										add_query_arg(
											array(
												QUETAG => $language_qv,
											),
											$currenturl
										);

									$title = sprintf( xl__( $this->xili_settings['list_link_title']['post_selected'], the_theme_domain() ), xl_x( $language->english_name, 'linktitle', $this->thetextdomain ) );
								}
							} else {
								$link = ( $this->lang_perma ) ? str_replace( '%lang%', $language_qv, $currenturl ) :
									add_query_arg(
										array(
											QUETAG => $language_qv,
										),
										$currenturl
									);
								$link = apply_filters( 'xiliml_language_list_menu_link', $link, $type, $language->slug, $language_qv ); // 2.19.3
								$title = sprintf( xl__( $this->xili_settings['list_link_title']['post_selected'], the_theme_domain() ), xl_x( $language->english_name, 'linktitle', $this->thetextdomain ) );
							}
							$this->doing_list_language = false;

						} else { // 'navmenu', 'navmenu-a'

							$link = ( $this->lang_perma ) ? str_replace( '%lang%', $language_qv, get_bloginfo( 'url' ) . '/%lang%/' ) :
							add_query_arg(
								array(
									QUETAG => $language_qv,
								),
								get_bloginfo( 'url' )
							);
							$title = esc_attr( sprintf( xl__( $this->xili_settings['list_link_title']['post_selected'], $this->thetextdomain ), xl_x( $language->english_name, 'linktitle', $this->thetextdomain ) ) );
						}

						// only required values...
						if ( $link ) {
							$new_lang_menu_item = (object) array();
							$id = $menu_object->ID * 100 + $i;
							$new_lang_menu_item->ID = $id;
							$new_lang_menu_item->url = $link;
							$new_lang_menu_item->title = xl__( $language->english_name, $this->thetextdomain );
							$new_lang_menu_item->attr_title = $title;
							$new_lang_menu_item->description = apply_filters( 'xl_nav_menu_lang_description', '', $language->slug ); // for twentyfifteen 2.15.4
							$new_lang_menu_item->menu_item_parent = $menu_object->menu_item_parent;
							$new_lang_menu_item->db_id = $menu_object->db_id;
							$new_lang_menu_item->target = $menu_object->target;
							$new_lang_menu_item->xfn = $menu_object->xfn; // 2.23
							$new_lang_menu_item->current = $menu_object->current; // 2.23
							$new_lang_menu_item->classes = array_merge( $menu_object->classes, explode( ' ', $class ) );

							$new_sorted_menu_items[] = $new_lang_menu_item;
						}
					} // language
				} // display
			} else { // no dummy insertion

				$new_sorted_menu_items[] = $menu_object;
			}
		} // foreach menu

		return $new_sorted_menu_items;
	}

	/**
	 * test if insertion point for languages list inside a location of navigation menu
	 * used by admin and customize theme
	 *
	 * return 0 if no insertion
	 *
	 * @since 2.8.8
	 * @updated 2.9.11
	 */
	public function has_languages_list_menu( $menu_location ) {
		// soon obsolete - conserved for old class
		return $this->has_insertion_point_list_menu( $menu_location, $this->insertion_point_dummy_link );
	}

	public function has_insertion_point_list_menu( $menu_location, $insertion_point_dummy_link ) {
		$locations_all = get_nav_menu_locations();
		$point = 0;
		if ( has_nav_menu( $menu_location ) && isset( $locations_all[ $menu_location ] ) ) {
			$menu = wp_get_nav_menu_object( $locations_all[ $menu_location ] );
			$menu_items = wp_get_nav_menu_items( $menu->term_id );

			foreach ( $menu_items as $one_item ) {
				if ( $one_item->url == $insertion_point_dummy_link ) {
					$point++;
				}
			}
		}
		return $point;
	}

}
