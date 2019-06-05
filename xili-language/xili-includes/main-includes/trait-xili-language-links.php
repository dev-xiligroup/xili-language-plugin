<?php
namespace Xili_Main;

/**
 * @package  xili-language
 * @subpackage main class
 * functions for links in both
 */
trait Xili_Language_Links {

	/**
	 * ***** Functions to improve bookmarks language filtering *****
	 *
	 */

	/**
	 * Filter to widget_links parameter
	 * @ since 1.8.5
	 */
	public function widget_links_args_and_lang( $widget_args ) {

		$cur_lang = $this->curlang;
		// rules depending category and settings in xili-language
		$cat_settings = $this->xili_settings['link_categories_settings']; //array ( 'all'=> true, 'category' => array ( '2' => false , '811' => true ) );

		$sub_select = false;
		if ( $widget_args['category'] && isset( $cat_settings['category'] ) ) {
			$sub_select = $cat_settings['category'][ $widget_args['category'] ];
		} else {
			$sub_select = $cat_settings['all'];
			//if ( $sub_select ) $widget_args['categorize'] = 0;
		}

		if ( $sub_select ) {
			$catname = '';
			$linklang = term_exists( $cur_lang, 'link_' . TAXONAME );
			$linklang_ever = term_exists( 'ev_er', 'link_' . TAXONAME ); // the dummy lang - shown ever with selected language
			if ( $cur_lang && $linklang ) {

				if ( $widget_args['category'] ) {
					$cat = get_term( $widget_args['category'], 'link_category' );
					$catname = apply_filters( 'link_category', $cat->name );
				}
				$the_link_ids = array();

				$the_link_ids = get_objects_in_term( array( $linklang['term_id'], $linklang_ever['term_id'] ), 'link_' . TAXONAME ); // lang + ever

				if ( $widget_args['category'] ) {
					$the_link_ids_cat = get_objects_in_term( array( $widget_args['category'] ), 'link_category' );
					$the_link_ids_all = array_intersect( $the_link_ids_cat, $the_link_ids );
					$widget_args['categorize'] = 0; // no sub list in one cat asked
				} else {
					$the_link_ids_all = $the_link_ids;
				}

				$widget_args['include'] = implode( ',', $the_link_ids_all );
				$widget_args['category'] = ''; // because implode of intersect $widget_args['include']; //
				$widget_args['title_li'] = $catname;

			}
		}

		return $widget_args;
	}

	/**
	 * only active if 'lang' in template tag wp_list_bookmarks()
	 *
	 * as :	wp_list_bookmarks( array( 'lang'=>the_curlang() ) ) to display only in current language
	 *
	 * don't interfere with widget_links filter
	 *
	 * @ since 1.8.5
	 */
	public function the_get_bookmarks_lang( $links_list, $args ) {
		if ( isset( $args[ QUETAG ] ) ) {
			// get links in selected lang
			$linklang = term_exists( $args[ QUETAG ], 'link_' . TAXONAME );
			$linklang_ever = term_exists( 'ev_er', 'link_' . TAXONAME ); // the dummy lang - shown ever with selected language
			//global $the_link_ids;
			$this->the_link_ids = get_objects_in_term( array( $linklang['term_id'], $linklang_ever['term_id'] ), 'link_' . TAXONAME );

			return array_filter( $links_list, array( &$this, '_filtering_links' ) );

		}
		return $links_list;
	}

	public function _filtering_links( $link ) {
		//global $the_link_ids;
		if ( in_array( $link->link_id, $this->the_link_ids ) ) {
			return true;
		}
	}

	/**
	 * Register link language taxonomy
	 * @ since 1.8.5
	 */
	public function add_link_taxonomy() {
		register_taxonomy(
			'link_' . TAXONAME,
			'link',
			array(
				'hierarchical' => false,
				'label' => false,
				'rewrite' => false,
				'update_count_callback' => array( &$this, '_update_link_lang_count' ),
				'show_ui' => false,
				'_builtin' => false,
				'show_in_nav_menus' => false,
			)
		);
	}
	// count update
	public function _update_link_lang_count( $terms ) {
		//
		global $wpdb;
		foreach ( (array) $terms as $term ) {
			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->links WHERE $wpdb->links.link_id = $wpdb->term_relationships.object_id AND term_taxonomy_id = %d", $term ) );
			$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
		}
	}


	/** end of language for bookmarks * @ since 1.8.5 **/


}
