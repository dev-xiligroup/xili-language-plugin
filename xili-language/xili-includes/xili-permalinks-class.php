<?php
/**
 * Added in xili-language plugin
 *
 * @since 2.9.30 - 2013-12-08
 * @package xili-language
 *
 * @updated 2.10.0 - 2014-02-02
 * @updated 2.10.1 - 2014-02-26
 * @updated 2.11.0 - 2014-03-10 - alias refreshing
 * @updated 2.11.1 - 2014-03-17 - fixes notice - improved linl (date, postformat)
 * @updated 2.13.2 - 2014-06-02 - fixes shortlink
 * @updated 2.13.3 - 2014-06-09 - fixes preg_match - functions optimized
 * @updated 2.14.0 - 2014-06-12 - fixes notices
 *
 * @updated 2.16.4 - 2015-03-15 - fixes View term links in admin side
 * @updated 2.16.6 - 2015-04-12 - adapted for JSON REST API (in dev)
 * @updated 2.20.3 - 2015-09-24 - tag links from multilingual group like trademark
 *
 */

/**
 * called from xl_permalinks_init (in theme functions.php)
 *
 */
class XL_Permalinks_rules {

	var $options = array ('rewrite' => true, 'force_lang' => 1 );
	protected $rewrite_rules = array();
	protected $always_rewrite = array('date', 'root', 'comments', 'search', 'author', 'post_format', 'language' );
	protected $always_insert = array( 'type', 'date', 'author' );
	var $language_slugs_list;
	var $language_xili_settings = array();
	protected $taxoname = 'language';
	protected $reqtag = 'lang';
	protected $rew_reqtag = '%lang%';

	function __construct( ) {

		$this->language_xili_settings = get_option('xili_language_settings');

		$this->taxoname = $this->language_xili_settings ['taxonomy'];
		$this->reqtag = $this->language_xili_settings ['reqtag'];
		$this->rew_reqtag = '%' . $this->reqtag . '%' ;

		if (isset ( $this->language_xili_settings['shortqv_slug_array'] )) {
			$this->language_slugs_list_being_updated();
		}

		add_filter ( 'redirect_canonical', array(&$this, 'redirect_canonical'), 10, 2 );

		add_action ( 'pre_option_rewrite_rules', array(&$this,'prepare_rewrite_rules'), 10) ;

		// to build links
		// to insert %lang% just after root

		add_filter ( 'home_url', array(&$this, 'insert_lang_tag_root'), 10, 4 ); // insert tag language when creating permalinks
		add_filter ( 'pre_post_link', array(&$this, 'insert_lang_tag_4post'), 10, 3 );

		// filters to replace %lang%
		add_filter ( 'post_link', array(&$this, 'insert_lang_4post'), 10, 3 );
		add_filter ( 'get_shortlink', array(&$this, 'insert_lang_shortlink'), 10, 4 ); // 2.13.2
		add_filter ( 'post_type_link', array(&$this, 'insert_lang_4post_type'), 10, 3 );
		add_filter ( '_get_page_link', array(&$this, 'insert_lang_4page'), 10, 2 );

		add_filter ( 'year_link', array(&$this, 'insert_lang_4year'), 10, 2 );
		add_filter ( 'month_link', array(&$this, 'insert_lang_4month'), 10, 3 );
		add_filter ( 'day_link', array(&$this, 'insert_lang_4day'), 10, 4 );

		add_filter ( 'author_link', array(&$this, 'insert_cur_lang'), 10, 1 );

		add_filter ( 'term_link', array(&$this, 'insert_lang_taxonomy'), 10, 3 );

		if ( is_admin() ) {
			add_filter ( 'term_link', array(&$this, 'create_default_link'), 11, 3 ); // 2.16.4
		}

		add_filter ( 'search_feed_link', array(&$this, 'insert_cur_lang'), 10, 2 );
		add_filter ( 'post_type_archive_feed_link', array(&$this, 'insert_cur_lang'), 10, 2 );

		// filter for paging links
		add_filter ( 'get_pagenum_link', array(&$this, 'insert_lang_pagenum_link'), 10, 2 );

	}

	/**
	 * Special admin side for taxonomy - no %lang% inserted - w/o permalink (to avoid language)
	 *
	 * @since 2.16.4
	 *
	 */
	function create_default_link ( $termlink, $term, $taxonomy ) {
		if ( !is_object($term) ) {
			if ( is_int($term) ) {
				$term = get_term($term, $taxonomy);
			} else {
				$term = get_term_by('slug', $term, $taxonomy);
			}
		}

		if ( !is_object($term) )
			$term = new WP_Error('invalid_term', __('Empty Term'));

		if ( is_wp_error( $term ) )
			return $term;

		$taxonomy = $term->taxonomy;
		$slug = $term->slug;
		$t = get_taxonomy($taxonomy);

		if ( 'category' == $taxonomy )
			$termlink = '?cat=' . $term->term_id;
		elseif ( $t->query_var )
			$termlink = "?$t->query_var=$slug";
		else
			$termlink = "?taxonomy=$taxonomy&term=$slug";

		remove_filter ( 'home_url', array(&$this, 'insert_lang_tag_root'));
		$termlink = home_url($termlink);
		add_filter ( 'home_url', array(&$this, 'insert_lang_tag_root'), 10, 4 );

		return 	$termlink;
	}

	/**
	 * language_slugs_list evenif being updated by first tab of settings
	 *
	 * @since 2.11.0
	 *
	 */
	function language_slugs_list_being_updated() {
		if ( isset($_POST['language_settings_action']) ) { // form form first xl settings tab
			$array_alias = array();
			$list_language_alias = $_POST['list_language_alias'];
			foreach ($list_language_alias as $slug => $alias ) {
				$array_alias[$alias] = $slug;
			}

			// edit or add
			if ( !in_array ( $_POST['language_settings_action'], array( 'delete','deleting' ) ) ) {
				$cur_slug = $_POST['language_nicename'];
				$cur_alias = ( $_POST['language_alias'] == '' ) ? $cur_slug : $_POST['language_alias'] ;
				$array_alias[$cur_alias] = $cur_slug;
			}

			$language_qvs = array_keys ( $array_alias );

		} else {
			// values are here saved in options
			$language_qvs = array_keys ( $this->language_xili_settings['shortqv_slug_array'] );
		}
		$language_qvs_all = $language_qvs;
		$language_qvs_all[] = $this->language_xili_settings['lang_undefined']; //2.2.3 for undefined

		foreach ( $language_qvs as $slug ) {
			$language_qvs_all[] = $slug . $this->language_xili_settings['lang_undefined']; //2.2.3 for undefined (§) + lang
		}

		$this->language_slugs_list = implode ("|", $language_qvs_all );
	}

	function redirect_canonical ( $redirect_url, $requested_url ) {

		if ( is_front_page() ) {
			return $requested_url; // to avoid relaunch when page as front... and a second query
		} else {
			return $redirect_url;
		}
	}

	function prepare_rewrite_rules ( $pre ) {
		global $xili_language;
		foreach ($this->rewrite_rules as $type)
			remove_filter($type . '_rewrite_rules', array(&$this, 'type_rewrite_rules'));

		$enabled_post_types = array_keys ( $xili_language->authorized_custom_post_type ( true ) );

		// add taxonomies linked to enabled posts
		$authorized_custom_taxonomies = $xili_language -> authorized_custom_taxonomies ( $enabled_post_types ) ;

		$types = array_merge( $this->always_rewrite, $enabled_post_types , array_merge ( array('category'), $authorized_custom_taxonomies ) );

		if ( class_exists ( 'xili_tidy_tags' ) )
			$types = array_merge( array ('post_tag'), $types );

		$types = array_merge( $this->always_rewrite, $types );

		$this->rewrite_rules = $types;

		foreach ($this->rewrite_rules as $type)
				add_filter($type . '_rewrite_rules', array(&$this, 'type_rewrite_rules'));

		add_filter('rewrite_rules_array', array(&$this, 'type_rewrite_rules') ); // needed for post type archives

		return $pre;
	}

	function type_rewrite_rules ( $rules ) {

		$filter = str_replace('_rewrite_rules', '', current_filter());

		// suppress the rules created by WordPress for our taxonomy
		if ( $filter == $this->taxoname )
			return array();

		global $wp_rewrite;
		$newrules = array();

		if (!empty( $this->language_slugs_list )) {
			$slug = $wp_rewrite->root . ($this->options['rewrite'] ? '' : '/') . '('. $this->language_slugs_list .')/';
		}
		// for custom post type archives
		$cpts = array_merge ( array_keys ( $this->language_xili_settings['multilingual_custom_post'] ), array ( 'category' ) );
		$cpts = $cpts ? '#post_type=('.implode('|', $cpts).')#' : '';

		// don't need the lang parameter for post types and post_tag taxonomy
		$cpts_wo_lang = array_merge ( array_keys ( $this->language_xili_settings['multilingual_custom_post'] ), array ( 'post_tag', 'page', 'post' ) );

		foreach ($rules as $key => $rule) {
			// don't need the lang parameter for post types and post_tag taxonomy

			if ( $this->options['force_lang'] && in_array($filter, $cpts_wo_lang )) {
				if ( isset($slug) ) {
					$newrules[$slug.str_replace($wp_rewrite->root, '', $key)] = str_replace(
						array('[8]', '[7]', '[6]', '[5]', '[4]', '[3]', '[2]', '[1]'),
						array('[9]', '[8]', '[7]', '[6]', '[5]', '[4]', '[3]', '[2]'),
						$rule
					); // hopefully it is sufficient! Yes Fréderic !

				}
			}

			// rewrite rules filtered by language
			elseif (in_array($filter, $this->always_rewrite) || ($cpts && preg_match($cpts, $rule) && !strpos($rule, 'name=')) || ($filter != 'rewrite_rules_array' && $this->options['force_lang'])) {
				if (isset($slug)) {
						$newrules[$slug.str_replace($wp_rewrite->root, '', $key)] = str_replace(
						array('[8]', '[7]', '[6]', '[5]', '[4]', '[3]', '[2]', '[1]', '?'),
						array('[9]', '[8]', '[7]', '[6]', '[5]', '[4]', '[3]', '[2]', '?' . $this->reqtag . '=$matches[1]&'),
						$rule
					); // should be enough!

				}
				unset($rules[$key]); // now useless
			}
		}

		// the home rewrite rule
		if ($filter == 'root' && isset($slug))
			$newrules[$slug.'?$'] = $wp_rewrite->index.'?' . $this->reqtag . '=$matches[1]';

		return $newrules + $rules;

	}

	/**
	 * insert lang rewrite tag in $url of post, CPT, Category and Taxonomy links
	 *
	 * @updated 1.1.2 - short link of post
	 * @updated 2.16.6 - add filter for json link
	 */
	function insert_lang_tag_root ( $url, $path, $orig_scheme, $blog_id ) {
		// isset( $wp_query->query_vars['json_route'])
		if ( true === apply_filters('xili_json_dont_insert_lang_tag_root', false, $url, $path, $orig_scheme, $blog_id ) ) return $url; // for JSON REST API 2.16.5
		if ( false !== strpos( $url, $this->rew_reqtag ) ) return $url; // to avoid twice insertion

		global $xili_language;

		$enabled_custom_posts = $enabled_custom_post_types = array(); // fixes with only enabled 2.10.1

		foreach ( $xili_language->xili_settings['multilingual_custom_post'] as $post_type => $values ) {
			if ( $values['multilingual'] == 'enable') {
				$enabled_custom_posts[] = '\/' . $post_type . '\/' ;
				$enabled_custom_post_types[] = $post_type;
			}
		}

		if ( $enabled_custom_posts ) {
			$pattern = '/('.implode ('|', $enabled_custom_posts ).')/';
		} else {
			$pattern = '';
		}

		$tax_base = array();
		$category_base_option = get_option('category_base');
		$tax_base[] = ($category_base_option) ? $category_base_option : 'category'; // à centraliser si class - ajouter "date"

		if ( class_exists ( 'xili_tidy_tags' ) ) { // now gives lang of tags
			$tag_base_option = get_option('tag_base');
			$tax_base[] = ($tag_base_option) ? $tag_base_option : 'tag';
		}

		$authorized_custom_taxonomies = $xili_language -> authorized_custom_taxonomies ( $enabled_custom_post_types ) ;
		$tax_base = array_merge ( $tax_base, $this->always_insert, $authorized_custom_taxonomies );

		$tax_base_slash = array();
		foreach ( $tax_base as $base ) $tax_base_slash[] = '\/' . $base . '\/';
		$pattern_tax = '/(' . implode ( '|', $tax_base_slash ) .')/'; // add / around



		if ( class_exists ( 'bbpress' ) ) {

			if ( $path == bbp_get_root_slug().'/' ) {
				$replace = $xili_language->lang_slug_qv_trans ( $xili_language->default_slug ) ;
				$url = str_replace( $path, $replace . '/' . $path, $url ) ;
				return $url ;
			} else if ( false !== strpos( $path, bbp_get_root_slug() ) && preg_match ( $pattern, $path ) ) {
				$url = str_replace( $path, $this->rew_reqtag . '/' . $path, $url ) ;
				return $url ;
			}

		}

		if ( $pattern && preg_match ( $pattern, $path ) ) {

			$path = ( substr ( $path, 0, 1) == '/' ) ? $path : '/' . $path ;
			$url = str_replace( $path, '/' . $this->rew_reqtag . $path, $url ) ;

		} else if ( preg_match ( $pattern_tax, $path ) ) {
			$url = str_replace( $path, '/' . $this->rew_reqtag . $path, $url ) ;

		} else if ( '' != $path && '/' != substr( $path, 0, 1 ) && false === strpos( $path, $this->rew_reqtag ) ) {
			$url = str_replace( $path, $this->rew_reqtag . '/' . $path, $url ) ;

		}

		return $url;
	}

	/**
	 * insert lang_tag (req_tag) in permastruct of post links
	 *
	 *
	 */
	function insert_lang_tag_4post ( $permalink, $post, $leavename ) {
		if ( false !== strpos( $permalink, '?p=' ) ) return $permalink;
		if ( false !== strpos( $permalink, $this->rew_reqtag ) ) return $permalink; // to avoid twice insertion

		return $this->rew_reqtag . $permalink;
	}

	/**
	 * Function series to replace reqtag by lang alias
	 */
	function insert_lang_shortlink ( $shortlink, $id, $context, $allow_slugs ) {
		global $xili_language;
		if ( $id == 0 ) {
			$post = get_post( $id ); // get current post
			if (!$post) return $shortlink;
			$post_id = $post->ID;
		} else {
			$post_id = $id;
		}

		$post_lang_slug = $xili_language->get_post_language ( $post_id );
		if ( $post_lang_slug != "" ) {
			$shortlink = str_replace( $this->rew_reqtag, $xili_language->lang_slug_qv_trans ( $post_lang_slug ) , $shortlink ) ;
		} else {
			$shortlink = str_replace( '/' . $this->rew_reqtag, '' , $shortlink ) ;
		}
		return $shortlink;
	}

	function insert_lang_4post ( $permalink, $post, $leavename ) {

		global $xili_language;

		$post_lang_slug = $xili_language->get_post_language ( $post->ID );

		if ( $post_lang_slug != "" ) {
			$permalink = str_replace( $this->rew_reqtag, $xili_language->lang_slug_qv_trans ( $post_lang_slug ) , $permalink ) ;
		} else {
			$permalink = str_replace( '/' . $this->rew_reqtag, '' , $permalink ) ;
		}

		return $permalink;
	}
	// added for post_type
	function insert_lang_4post_type ( $permalink, $post, $leavename ) {

		global $xili_language;
		// get authorized post_types
		$post_type = get_post_type( $post->ID );

		$custompoststype = $xili_language->xili_settings['multilingual_custom_post'] ;
		$custompoststype_keys = array_keys( $custompoststype );

		if (in_array($post_type, $custompoststype_keys ) && $custompoststype [$post_type]['multilingual'] == 'enable') {

			$post_lang_slug = $xili_language->get_post_language ( $post->ID );

			if ( $post_lang_slug != "" ) {
				$permalink = str_replace( $this->rew_reqtag, $xili_language->lang_slug_qv_trans ( $post_lang_slug ) , $permalink ) ;
			} else {
				$permalink = str_replace( '/' . $this->rew_reqtag, '' , $permalink ) ;
			}
		}

		return $permalink;
	}
	// page
	function insert_lang_4page ( $permalink, $post_id ) {

		global $xili_language;

		$post_lang_slug = $xili_language->get_post_language ( $post_id );
		if ( $post_lang_slug != "" ) {
			$permalink = str_replace( $this->rew_reqtag, $xili_language->lang_slug_qv_trans ( $post_lang_slug ) , $permalink ) ;
		} else {
			$permalink = str_replace( '/' . $this->rew_reqtag, '' , $permalink ) ;
		}

		return $permalink;
	}

	function insert_lang_4year ( $permalink, $year ) {

		return $this->insert_cur_lang ( $permalink );
	}
	function insert_lang_4month ( $permalink, $year, $month ) {

		return $this->insert_cur_lang ( $permalink );
	}
	function insert_lang_4day ( $permalink, $year, $month, $day ) {

		return $this->insert_cur_lang ( $permalink );
	}
	// used by date, author components
	function insert_cur_lang ( $permalink ) {
		global $xili_language;
		if ( is_admin() ) return $permalink ; // in menus builder - not resolve %lang%

		$the_lang = ( $xili_language->doing_list_language === false ) ? $xili_language->lang_slug_qv_trans ( the_curlang() ) : $xili_language->lang_slug_qv_trans ( $xili_language->doing_list_language ); // by default

		if ( $the_lang == '' ) $the_lang = xili_language_trans_slug_qv ( $xili_language->default_slug );

		$permalink = str_replace( $this->rew_reqtag, $xili_language->lang_slug_qv_trans ( $the_lang ) , $permalink ) ;
		return $permalink;
	}

	function insert_lang_taxonomy ( $termlink, $term, $taxonomy ) {
		global $xili_language, $xili_tidy_tags, $post;

		if ( is_admin() ) return $termlink ; // in menus builder - not resolve %lang%
		$do_replace = false ;

		$the_lang = ( $xili_language->doing_list_language === false ) ? $xili_language->lang_slug_qv_trans ( the_curlang() ) : $xili_language->lang_slug_qv_trans ( $xili_language->doing_list_language ); // by default

		if ( $the_lang == '' ) $the_lang = xili_language_trans_slug_qv ( $xili_language->default_slug );

		if ( in_array ( $taxonomy , array ( 'category' ) ) ) {

			if ( ! $post = get_post( $post ) ) {
				$termlink = str_replace( $this->rew_reqtag, $the_lang , $termlink); // 404 - link in menu
				return $termlink;
			}

			$the_post_lang = $xili_language->get_post_language ( $post->ID );

			if ( $the_post_lang )
				$termlink = str_replace( $this->rew_reqtag, xili_language_trans_slug_qv ( $the_post_lang ) , $termlink);
			else
				$termlink = str_replace( $this->rew_reqtag, xili_language_trans_slug_qv ( $xili_language->default_slug ) . LANG_UNDEF , $termlink);

			return $termlink;

		} else if ( in_array ( $taxonomy , array ( 'language' , 'post_format' ) ) ) {

			$do_replace = true;

		} else if ( 'post_tag' == $taxonomy ) {
			// lang of the post_tag if xili-tidy-tags exists...
			if ( class_exists ( 'xili_tidy_tags' ) ) {

				// search lang of tag
				$tag_langs = $xili_tidy_tags->return_lang_of_tag ( $term->term_id );

				if ( $tag_langs ) {
					$the_tag_lang = xili_language_trans_slug_qv ( $tag_langs[0]->slug );
					$termlink = str_replace( $this->rew_reqtag, $the_tag_lang, $termlink);
					return $termlink;
				} else { // 2.20.3
					// for tags grouped in multilingual group like trademark
					$the_tag_lang = xili_language_trans_slug_qv ( $xili_language->get_default_slug() );
					$termlink = str_replace( $this->rew_reqtag, $the_tag_lang, $termlink);
					return $termlink;
				}

			} else {
				$do_replace = true;
			}
		}

		if ( !$do_replace ) {

			$enabled_post_type = array_keys ( $xili_language->authorized_custom_post_type ( true ) );
			// taxonomies linked to enabled posts
			$authorized_custom_taxonomies = $xili_language -> authorized_custom_taxonomies ( $enabled_post_type ) ;

			if ( in_array ( $taxonomy , $authorized_custom_taxonomies ) ) {	// test if linked to authorized CPT

				$do_replace = true;
			}
		}

		if ( $do_replace ) $termlink = str_replace( $this->rew_reqtag, $the_lang, $termlink );
		return $termlink ;
	}

	// insert curlang if paging link on home
	function insert_lang_pagenum_link ( $link ) {
		global $xili_language, $wp_rewrite;
		$the_lang = $xili_language->lang_slug_qv_trans ( the_curlang() ); // by default
		$home = trailingslashit( get_bloginfo( 'url' ) );
		//if ( $the_lang == '' ) $the_lang = xili_language_trans_slug_qv ( $xili_language->default_slug );
		if ( $the_lang != '' && false === strpos ( $link, '/'.$the_lang ) ) {

			return str_replace( $home.$wp_rewrite->root, $home.$wp_rewrite->root.$the_lang.'/', $link );
		}
		return $link;
	}


} // end class
?>