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
	public $termmetas = array ( 'text_direction' => 'rtl',
								'native_name' => '',
								'visibility' => 1,
								'charset' => '' ,
								'front_back_side' => 'both',
								'flag' => '',
								'alias' => ''
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

		$_term =  get_term( $term_id, TAXONAME );

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
			if ( $meta = get_term_meta( $lang_term_obj->term_id, $term_meta_key, true ) ) {
			// to conserve default value if termmeta not set
				$lang_term_obj->termmetas[$term_meta_key] = $meta ;
			}
		}
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
		foreach ( get_object_vars( $term ) as $key => $value ) {
			$this->$key = $value;
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

}

?>