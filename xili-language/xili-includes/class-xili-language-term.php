<?php
/**
 * @package xili-language
 * @subpackage Taxonomy
 */

/**
 * Class used for managing language taxonomy term object.
 *
 * This class is used to incorporate description and features formerly stored in Options
 * and now since WP 4.4 in termmeta new table.
 * Uses new WP_term final class to create xili_language_term object
 * .
 *
 * @since 2.21.2
 */
class xili_language_term {

	/**
	 * Term ID.
	 *
	 * @since 4.4.0
	 * @access public
	 * @var int
	 */
	public $term_id;

	/**
	 * The term's name. - iso name - wp_locale (jetpack)
	 *
	 * @since 4.4.0
	 * @access public
	 * @var string
	 */
	public $name = '';

	/**
	 * The term's slug. - used in query var
	 *
	 * @since 4.4.0
	 * @access public
	 * @var string
	 */
	public $slug = '';

	/**
	 * The term's term_group.
	 *
	 * @since 4.4.0
	 * @access public
	 * @var string
	 */
	public $term_group = '';

	/**
	 * Term Taxonomy ID.
	 *
	 * @since 4.4.0
	 * @access public
	 * @var int
	 */
	public $term_taxonomy_id = 0;

	/**
	 * The term's taxonomy name. TAXONAME
	 *
	 * @since 4.4.0
	 * @access public
	 * @var string
	 */
	public $taxonomy = '';

	/**
	 * The term's description. - english name
	 *
	 * @since 4.4.0
	 * @access public
	 * @var string
	 */
	public $description = '';

	/**
	 * ID of a term's parent term.
	 *
	 * @since 4.4.0
	 * @access public
	 * @var int
	 */
	public $parent = 0;

	/**
	 * Cached object count for this term.
	 *
	 * @since 4.4.0
	 * @access public
	 * @var int
	 */
	public $count = 0;

	/**
	 * Stores the term object's sanitization level.
	 *
	 * Does not correspond to a database field.
	 *
	 * @since 4.4.0
	 * @access public
	 * @var string
	 */
	public $filter = 'raw';

	/**
	 * Group Term Taxonomy ID. used in xili-language since 0.9.8
	 *
	 * @since 2.21.2
	 * @access public
	 * @var int
	 */
	public $group_term_taxonomy_id = 0;

	/**
	 * Group The term's taxonomy name. used in xili-language since 0.9.8 TAXONAME
	 *
	 * @since 2.21.2
	 * @access public
	 * @var string
	 */
	public $group_taxonomy = '';

	/**
	 * Term Order. used in xili-language since 0.9.8
	 *
	 * @since 2.21.2
	 * @access public
	 * @var int
	 */
	public $term_order = 0;

	/**
	 * termmeta list
	 *
	 * @since 2.21.2
	 * @access public
	 * @var strings array
	 */
	public $termmetas = array ( 'text_direction' => 'ltr',
								'native_name' => '',
								'visibility' => 1,
								'charset' => '' ,
								'front_back_side' => 'both', // where is the language used
								'flag' => '', // URI
								'admin_flag' => '', // URI
								'alias' => '',
								/**
								 * some infos from wp translation install ( 108 )
								 */
								'wp_glot_version' => '', // WP version - translated
								'wp_glot_updated' => '', // latest date
								'wp_glot_package' => '', // URI zip (core)
								'wp_glot_formal' => '' // '' or 'formal' or 'informal'
								);

	/**
	 * Get instance.
	 *
	 * @since 2.21.2
	 * @access public
	 *
	 * @param $language id
	 * @return mixed Type corresponding to `$output` on success or null on failure. When `$output` is `OBJECT`,
 	 *               a WP_Term instance is returned. If taxonomy does not exist then WP_Error will be returned.
	 */
	public static function get_instance( $term_id ) {
		global $wpdb;
		if ( empty( $term_id ) ) {
			return new WP_Error( 'invalid_term', __( 'Empty Term' ) );
		}

		$_term =  get_term( $term_id, TAXONAME ); //error_log('message '. $term_id);

		if ( is_wp_error( $_term ) ) {
			return $_term;
		} elseif ( ! $_term ) {
			return null;
		}
		$lang_term_obj = new xili_language_term( $_term ); // fill obj term values

		$lang_term_obj->term_order = $wpdb->get_var( "SELECT term_order FROM $wpdb->term_relationships WHERE object_id = $lang_term_obj->term_id AND term_taxonomy_id = $lang_term_obj->group_term_taxonomy_id " );

		// fill meta keys with term meta values
		$meta_keys = array_keys( $lang_term_obj->termmetas );

		foreach ( $meta_keys as $term_meta_key) {
			// to conserve default value if termmeta not set
			if ( metadata_exists( 'term', $lang_term_obj->term_id, $term_meta_key ) )
				$lang_term_obj->termmetas[$term_meta_key] = get_term_meta( $lang_term_obj->term_id, $term_meta_key, true ) ;
		}
		//error_log ( '----------------- ' . serialize($lang_term_obj));
		return $lang_term_obj;
	}

	/**
	 * Constructor.
	 *
	 * @since 4.4.0
	 * @access public
	 *
	 * @param WP_Term|object $term Term object.
	 */
	public function __construct( $term ) {
		global $xili_language;

		$term_vars = get_object_vars( $term );
		$term_vars['termmetas'] = $this->termmetas; // 2.22.8
		foreach ( $term_vars as $key => $value ) {
			$this->$key = $value;
			// define sanitize callback functions for meta list
			//
			if ( $key == 'termmetas') {
				foreach ( $this->$key as $meta_key => $default ) {
					$args = array(
						'type'              => 'string', //TODO
						'description'       => ' = ' . $meta_key, //TODO
						'single'            => false,
						'sanitize_callback' => array( &$this, 'meta_callback_'.$meta_key),
						'auth_callback'     => null,
						'show_in_rest'      => true,
					);
					register_meta ( 'term', $meta_key, $args );
				}
			}
		}
		$this->taxonomy = TAXONAME;
		$this->group_taxonomy = TAXOLANGSGROUP;
		$this->group_term_taxonomy_id = $xili_language->xili_settings['langs_group_tt_id']; // because grouped via taxonomy languages_group


	}

	/**
	 * Converts an object to array.
	 *
	 * @since 4.4.0
	 * @access public
	 *
	 * @return array Object as array.
	 */
	public function to_array() {
		return get_object_vars( $this );
	}

	/**
	 * Getter.
	 * example 1: error_log ( serialize ( $lang_test = xili_language_term::get_instance( $language->term_id )));
     * example 1: error_log ( serialize ( $lang_test->language_data )); // magic method
	 *
	 * @since 4.4.0
	 * @access public
	 *
	 * @return mixed
	 */
	public function __get( $key ) {

		switch ( $key ) {
			case 'data' :
				$data = new stdClass();
				$columns = array( 'term_id', 'name', 'slug', 'term_group', 'term_taxonomy_id', 'taxonomy', 'description', 'parent', 'count' );
				foreach ( $columns as $column ) {
					$data->{$column} = isset( $this->{$column} ) ? $this->{$column} : null;
				}

				return sanitize_term( $data, $data->taxonomy, 'raw' );
				break;

			case 'language_data' :
				$data = new stdClass();
				$columns = array( 'term_id'=>'term_id',
							'name'=>'iso_name',
							'slug'=>'slug',
							'term_group'=>'term_group',
							'term_taxonomy_id'=>'term_taxonomy_id',
							'taxonomy'=>'taxonomy',
							'group_taxonomy'=>'group_taxonomy',
							'group_term_taxonomy_id'=>'group_term_taxonomy_id',
							'term_order'=>'term_order',
							'description'=>'english_name',
							'parent'=>'parent',
							'count'=>'count' );
				foreach ( $columns as $column_key => $lang_column ) {
					$data->{$lang_column} = isset( $this->{$column_key} ) ? $this->{$column_key} : null;
				}
				// fill meta keys with term meta values
				$meta_keys = array_keys( $this->termmetas );
				foreach ( $meta_keys as $term_meta_key) {
					$data->{$term_meta_key} = isset( $this->termmetas[$term_meta_key] ) ? $this->termmetas[$term_meta_key] : null ;
				}
				return $data;
				break;
		}
	}

	/**
	 * to control value in term meta - see __construct
	 * 'text_direction' => 'ltr',
		'native_name' => '',
		'visibility' => 1,
		'charset' => '' ,
		'front_back_side' => 'both',
		'flag' => '',
		'admin_flag' => '',
		'alias' => ''
		'wp_glot_version' => '', // WP version - translated
		'wp_glot_updated' => '', // latest date
		'wp_glot_package' => '', // URI zip (core)
		'wp_glot_formal' => '' // '' or 'formal' or 'informal'
		);
	 */
	public function meta_callback_text_direction( $text_direction = 'ltr' ){
		return ( in_array( $text_direction, array('ltr', 'rtl') ) ) ? $text_direction : 'ltr' ;
	}

	public function meta_callback_native_name( $native_name = '' ){
		return sanitize_text_field ( $native_name ) ;
	}

	public function meta_callback_visibility( $visibility = 1 ){
		return $visibility ;
	}

	public function meta_callback_charset( $charset = '' ){
		return $charset ;
	}

	public function meta_callback_front_back_side( $front_back_side = 'both' ){
		return ( in_array( $front_back_side, array( 'front', 'back', 'both') ) ) ? $front_back_side : 'both' ;
	}

	public function meta_callback_flag( $flag = '' ){ //TODO check URI
		return $flag ;
	}

	public function meta_callback_admin_flag( $flag = '' ){ //TODO check URI
		return $flag ;
	}

	public function meta_callback_alias( $alias = '' ){
		return $alias ;
	}

	public function meta_callback_wp_glot_version( $wp_glot_version = '' ){
		return $wp_glot_version ;
	}

	public function meta_callback_wp_glot_updated( $wp_glot_updated = '' ){
		return $wp_glot_updated ;
	}

	public function meta_callback_wp_glot_package( $wp_glot_package = '' ){
		// TODO test available
		return $wp_glot_package ;
	}

	public function meta_callback_wp_glot_formal( $wp_glot_formal = '' ){
		return ( in_array( $wp_glot_formal, array('', 'formal', 'informal') ) ) ? $wp_glot_formal : null ;
	}




	/**
	 * Populate term metas with values saved in former versions in xili-language_settings.
	 * example 1: error_log ( serialize ( $lang_test = xili_language_term::upgrade_instance( $language->term_id )));
     *
	 *
	 * @since 2.21.2
	 * @access public
	 *
	 * @return mixed object as language_data
	 */
	public static function upgrade_instance( $term_id ) {

		$a_language = xili_language_term::get_instance( $term_id ) ;

		if ( $a_language && !is_wp_error( $a_language ) ) {
			$xili_settings = get_option( 'xili_language_settings', false );
			if ( $xili_settings && !isset( $xili_settings['meta_update'] ) ) {

				$one_language = $a_language->language_data; // metas in object

				// array('charset'=>"",'hidden'=>"");
				$one_language->visibility = 1 - (int) $xili_settings['lang_features'][$one_language->slug]['hidden'];
				$one_language->charset = $xili_settings['lang_features'][$one_language->slug]['charset'];
				$one_language->alias = ( isset ( $xili_settings['lang_features'][$one_language->slug]['alias'] ) ) ? $xili_settings['lang_features'][$one_language->slug]['alias'] : $one_language->slug ;

				// values from GP_locale (by ISO)

				$locale = GP_Locales::by_field( 'wp_locale', $one_language->iso_name );

				$one_language->text_direction = ( $locale ) ? $locale->text_direction : 'ltr'; // rtl changed
				$one_language->native_name = ( $locale ) ? $locale->native_name : '' ;

				// add alias by default
				$slugs = explode('_', $one_language->slug) ;
				$one_language->alias = ( $locale ) ? ( ( $locale->lang_code_iso_639_1 ) ? $locale->lang_code_iso_639_1 : $locale->lang_code_iso_639_2 )  : $slugs[0] ;

				// UX info

				// 'front_back_side' => 'both',
				if ( $one_language->visibility ) {
					if ( in_array ($one_language->iso_name, get_available_languages() ) ) {
						$one_language->front_back_side = 'both';
					} else {
						$one_language->front_back_side = 'front';
					}

				} else {
					if ( in_array ($one_language->iso_name, get_available_languages() ) ) {
						$one_language->front_back_side = 'back';
					} else {
						$one_language->front_back_side = 'na'; // not available - must be improved
					}
				}

				// 'flag' => ''
				//  analyze if exists
				$url = do_shortcode( "[xili-flag lang={$one_language->slug}]" ) ;
				$one_language->flag = $url; // '' if not exists

				// update termmetas
				// fill meta keys with term meta values
				$meta_keys = array_keys( $a_language->termmetas );

				foreach ( $meta_keys as $term_meta_key) {
					update_term_meta( $term_id, $term_meta_key, $one_language->{$term_meta_key} );
				}
				return $one_language;
			}

		} else {
			return false;
		}
	}

	/**
	 * Populate term metas with default values. (w/o wp_glot metas)
     *
	 *
	 * @since 2.22
	 * @access public
	 *
	 * @return mixed object as language_data
	 */
	public static function complete_instance( $term_id ) {

		$a_language = xili_language_term::get_instance( $term_id ) ;

		if ( $a_language && !is_wp_error( $a_language ) ) {

				// metas in object
				$one_language = $a_language->language_data;
				// values from GP_locale (by ISO)

				$locale = GP_Locales::by_field( 'wp_locale', $one_language->iso_name );

				$one_language->text_direction = ( $locale ) ? $locale->text_direction : 'ltr'; // rtl changed
				$one_language->native_name = ( $locale ) ? $locale->native_name : '' ;
				// add alias by default
				$slugs = explode('_', $one_language->slug) ;
				$one_language->alias = ( $locale ) ? ( ( $locale->lang_code_iso_639_1 ) ? $locale->lang_code_iso_639_1 : $locale->lang_code_iso_639_2 )  : $slugs[0] ;
				// UX info

				// 'front_back_side' => 'both',
				if ( $one_language->visibility ) {
					if ( in_array ($one_language->iso_name, get_available_languages() ) ) {
						$one_language->front_back_side = 'both';
					} else {
						$one_language->front_back_side = 'front';
					}

				} else {
					if ( in_array ($one_language->iso_name, get_available_languages() ) ) {
						$one_language->front_back_side = 'back';
					} else {
						$one_language->front_back_side = 'na'; // not available - must be improved
					}
				}

				// 'flag' => ''
				//  analyze if exists
				$url = do_shortcode( "[xili-flag lang={$one_language->slug}]" ) ;
				$one_language->flag = $url; // '' if not exists

				// update termmetas
				// fill meta keys with term meta values
				$meta_keys = array_keys( $a_language->termmetas );

				foreach ( $meta_keys as $term_meta_key) {
					update_term_meta( $term_id, $term_meta_key, $one_language->{$term_meta_key} );
				}
				return $one_language;

		} else {
			return false;
		}
	}

}

/**
 * Updates WP glot metas of collection ( during admin )
 * @since  2.22.8 to integrate installing wp glot datas (from translation install)
 *
 * @param   $languages term_id array
 * @param   $available_translations values get from WP
 * @param   $update if true update directly term meta
 *
 * @return  array term_id => wp_glot array
 */
function xili_update_wp_glot_metas( $languages, $available_translations = array(), $update = false ) {
	// get install list
	// check if exists
	// updates
	if ( $available_translations == array() ) {
		require_once ABSPATH . 'wp-admin/includes/translation-install.php';
		$available_translations = wp_get_available_translations();
	}
	$updated_objects = array();
	foreach ( $languages as $term_id ) {
		$a_language = xili_language_term::get_instance( $term_id ) ;

		if ( $a_language && !is_wp_error( $a_language ) ) {

			$one_language = $a_language->language_data; // metas in object
			$wp_locale = $one_language->iso_name;
			$wp_locale_full = '';
			if ( isset ( $available_translations[$wp_locale] ) ) {
				$wp_locale_full = $wp_locale ;
				$wp_glot_formal = '';
			} else if ( isset ( $available_translations[$wp_locale.'-formal'] ) ){ // WP stores zip with suffix
				$wp_locale_full = $wp_locale.'-formal';
				$wp_glot_formal = 'formal';
			} else if ( isset ( $available_translations[$wp_locale.'-informal'] ) ){
				$wp_locale_full = $wp_locale.'-informal';
				$wp_glot_formal = 'informal';
			}
			if ( $wp_locale_full ) {
				$wp_glot_language = $available_translations[$wp_locale_full];

				$one_language->wp_glot_version = $wp_glot_language['version'];
				if ( $update ) update_term_meta( $term_id, 'wp_glot_version', $wp_glot_language['version'] );

				$one_language->wp_glot_updated = $wp_glot_language['updated'];
				if ( $update ) update_term_meta( $term_id, 'wp_glot_updated', $wp_glot_language['updated'] );

				$one_language->wp_glot_package = $wp_glot_language['package'];
				if ( $update ) update_term_meta( $term_id, 'wp_glot_package', $wp_glot_language['package'] );

				$one_language->wp_glot_formal = $wp_glot_formal;
				if ( $update ) update_term_meta( $term_id, 'wp_glot_formal', $wp_glot_formal );

				$updated_objects[$term_id] = array(
					'wp_glot_version' => $wp_glot_language['version'],
					'wp_glot_updated' => $wp_glot_language['updated'],
					'wp_glot_package' => $wp_glot_language['package'],
					'wp_glot_formal' => $wp_glot_formal
					);
			}
			//error_log ( '--- ' . serialize ( $one_language ) );
		}
	}
	return $updated_objects;
}

?>