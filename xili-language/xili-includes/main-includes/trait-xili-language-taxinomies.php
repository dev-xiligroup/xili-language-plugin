<?php
namespace Xili_Main;

/**
 * @package  xili-language
 * @subpackage main class
 * functions for taxonomies (and categories) in front
 */
trait Xili_Language_Taxinomies {

	/**
	 * to cancel sub select by lang in cat 1 by default
	 *
	 * @since 0.9.2
	 * @since 0.9.7 - 1.8.4
	 * can be hooked by filter add_filter('xiliml_modify_querytag','yourfunction') in functions.php
	 *
	 *
	 */
	public function modify_querytag( $query ) {
		if ( has_filter( 'xiliml_modify_querytag' ) ) {
			apply_filters( 'xiliml_modify_querytag', '' );
		} else {

			if ( defined( 'XILI_CATS_ALL' ) && ! empty( $query->query_vars['cat'] ) ) { /* change in functions.php or use hook in cat 1 by default*/
				$excludecats = explode( ',', XILI_CATS_ALL );
				if ( array() != $excludecats && in_array( $query->query_vars['cat'], $excludecats ) ) {
					$query->query_vars[ QUETAG ] = ''; /* to cancel sub select */
				}
			}
		}
	}

	/**
	 * translate description of categories
	 *
	 * @since 0.9.0
	 * update 0.9.7 - 0.9.9.4
	 * can be hooked by filter add_filter('xiliml_link_translate_desc','yourfunction',2,4) in functions.php
	 *
	 *
	 */
	public function xiliml_link_translate_desc( $description, $category = null, $context = '' ) {
		if ( has_filter( 'xiliml_link_translate_desc' ) ) {
			return apply_filters( 'xiliml_link_translate_desc', $description, $category, $context, $this->curlang );
		}
		$translated_desc = ( $this->curlang && '' != $description ) ? xl__( $description, $this->thetextdomain ) : $description;
		return $translated_desc;
	}

	/**
	 * filters for wp_title() translation - single_cat_title -
	 * since 1.4.1
	 *
	 */
	public function xiliml_single_cat_title_translate( $cat_name ) {
		if ( has_filter( 'xiliml_single_cat_title_translate' ) ) {
			return apply_filters( 'xiliml_single_cat_title_translate', $cat_name );
		}
		$translated = ( $this->curlang && '' != $cat_name ) ? xl__( $cat_name, $this->thetextdomain ) : $cat_name;
		return $translated;
	}

	/**
	 * to improve limits of is_tax()
	 *
	 * @since 2.9.1
	 *
	 * tracs #2444 - http://bbpress.trac.wordpress.org/ticket/2444
	 *
	 */

	public function is_tax_improved() {

		if ( is_tag() ) {
			return true;
		}

		global $wp_query;
		$queried_object = $wp_query->get_queried_object();

		if ( isset( $queried_object->taxonomy ) && ! in_array( $queried_object->taxonomy, array( '', 'category', 'post_tag' ) ) ) {

			// test taxonomy topic-tag - cannot use is_tax -

			if ( class_exists( 'bbpress' ) && get_option( '_bbp_topic_tag_slug', 'topic-tag' ) == $queried_object->taxonomy ) {
				return true;
			}

			// test other taxonomy

			if ( is_tax( $queried_object->taxonomy ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * the_category() rewritten to keep new features of multilingual (and amp & pbs in link)
	 *
	 * @since 0.9.0
	 * @updated 0.9.9.4 - 2.8.9
	 * can be hooked by add_action xiliml_the_category in functions.php
	 *
	 */
	public function the_category( $post_id, $separator = ', ', $echo = true ) {
		global $wp_rewrite;
		/* default here*/
		$thelist = '';
		$the_cats_list = wp_get_object_terms( $post_id, 'category' );
		$rel = ( is_object( $wp_rewrite ) && $wp_rewrite->using_permalinks() ) ? 'rel="category tag"' : 'rel="category"'; // 2.8.9
		$i = 0;

		$view_all_posts = xl__( $this->xili_settings['list_link_title']['view_all_posts'], $this->thetextdomain );

		foreach ( $the_cats_list as $the_cat ) {
			if ( 0 < $i ) {
				$thelist .= $separator . ' ';
			}

			$desc4title = trim( esc_attr( apply_filters( 'category_description', $the_cat->description, $the_cat->term_id ) ) );

			$title = ( '' == $desc4title ) ? esc_attr( sprintf( $view_all_posts, xl__( $category->name, $this->thetextdomain ) ) ) : $desc4title;

			$the_catlink = '<a href="' . get_category_link( $the_cat->term_id ) . '" title="' . $title . '" ' . $rel . '>';
			//if ($curlang != DEFAULTSLUG) :
			$the_catlink .= xl__( $the_cat->name, $this->thetextdomain ) . '</a>';
			//else :
				//$the_catlink .= $the_cat->name.'</a>';;
			//endif;
			$thelist .= $the_catlink;
			++$i;
		}
		if ( $echo ) :
			echo $thelist;
			return true;
		else :
			return $thelist;
		endif;
	}

	/**
	 * Retrieve category list in either HTML list or custom format - as in category-template - rewritten for multilingual - filter the_category only frontend
	 *
	 * @since 1.7.0
	 *
	 * @param string $separator Optional, default is empty string. Separator for between the categories.
	 * @param string $parents Optional. How to display the parents.
	 * no third param because call by end filter
	 * @return string
	 */
	public function xl_get_the_category_list( $thelist, $separator = '', $parents = '' ) {
		global $wp_rewrite, $post;
		$categories = get_the_category( $post->ID );
		//if ( !is_object_in_taxonomy( get_post_type( $post_id ), 'category' ) )
			//return apply_filters( 'the_category', '', $separator, $parents );

		if ( empty( $categories ) ) {
			return xl__( 'Uncategorized', $this->thetextdomain ); // fixed - avoid a previous recursive filter with custom @since 1.8.0
		}
		$rel = ( is_object( $wp_rewrite ) && $wp_rewrite->using_permalinks() ) ? 'rel="category tag"' : 'rel="category"';

		$thelist = '';
		$view_all_posts = xl__( $this->xili_settings['list_link_title']['view_all_posts'], $this->thetextdomain );
		if ( '' == $separator ) {
			$thelist .= '<ul class="post-categories">';
			foreach ( $categories as $category ) {
				$thelist .= "\n\t<li>";
				switch ( strtolower( $parents ) ) {
					case 'multiple':
						if ( $category->parent ) {
							$thelist .= get_category_parents( $category->parent, true, $separator );
						}
						$thelist .= '<a href="' . get_category_link( $category->term_id ) . '" title="' . esc_attr( sprintf( $view_all_posts, xl__( $category->name, $this->thetextdomain ) ) ) . '" ' . $rel . '>' . xl__( $category->name, $this->thetextdomain ) . '</a></li>';
						break;
					case 'single':
						$thelist .= '<a href="' . get_category_link( $category->term_id ) . '" title="' . esc_attr( sprintf( $view_all_posts, xl__( $category->name, $this->thetextdomain ) ) ) . '" ' . $rel . '>';
						if ( $category->parent ) {
							$thelist .= get_category_parents( $category->parent, false, $separator );
						}
						$thelist .= xl__( $category->name, $this->thetextdomain ) . '</a></li>';
						break;
					case '':
					default:
						$thelist .= '<a href="' . get_category_link( $category->term_id ) . '" title="' . esc_attr( sprintf( $view_all_posts, xl__( $category->name, $this->thetextdomain ) ) ) . '" ' . $rel . '>' . xl__( $category->cat_name, $this->thetextdomain ) . '</a></li>';
				}
			}
			$thelist .= '</ul>';
		} else {
			$i = 0;
			foreach ( $categories as $category ) {
				if ( 0 < $i ) {
					$thelist .= $separator;
				}
				switch ( strtolower( $parents ) ) {
					case 'multiple':
						if ( $category->parent ) {
							$thelist .= get_category_parents( $category->parent, true, $separator );
						}
						$thelist .= '<a href="' . get_category_link( $category->term_id ) . '" title="' . esc_attr( sprintf( $view_all_posts, xl__( $category->name, $this->thetextdomain ) ) ) . '" ' . $rel . '>' . xl__( $category->name, $this->thetextdomain ) . '</a>';
						break;
					case 'single':
						$thelist .= '<a href="' . get_category_link( $category->term_id ) . '" title="' . esc_attr( sprintf( $view_all_posts, xl__( $category->name, $this->thetextdomain ) ) ) . '" ' . $rel . '>';
						if ( $category->parent ) {
							$thelist .= get_category_parents( $category->parent, false, $separator );
						}
						$thelist .= xl__( $category->name, $this->thetextdomain ) . '</a>';
						break;
					case '':
					default:
						$thelist .= '<a href="' . get_category_link( $category->term_id ) . '" title="' . esc_attr( sprintf( $view_all_posts, xl__( $category->name, $this->thetextdomain ) ) ) . '" ' . $rel . '>' . xl__( $category->name, $this->thetextdomain ) . '</a>';
				}
				++$i;
			}
		}
		return $thelist;
	}



}
