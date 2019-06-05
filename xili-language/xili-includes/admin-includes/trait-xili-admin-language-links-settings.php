<?php
namespace Xili_Admin;

/**
 * @package Xili-Language
 * @subpackage admin
 * functions for links settings interface
 * @since  2.23 traits files
 */

trait Xili_Admin_Language_Links_Settings {

	/******************************* LINKS ****************************/

	/**
	 * @updated 1.8.0
	 */
	public function add_custom_box_in_link() {

		add_action( 'add_meta_boxes_link', array( &$this, 'new_box' ) );
	}



	/**
	 * Box, action and function to set language in edit-link-form
	 * @ since 1.8.5
	 */
	public function new_box() {
		add_meta_box( 'linklanguagediv', __( "Link's language", 'xili-language' ), array( &$this, 'link_language_meta_box' ), 'link', 'side', 'core' );
	}

	public function link_language_meta_box( $link ) {

		if ( isset( $link->link_id ) ) {
			$ress = wp_get_object_terms( $link->link_id, 'link_' . TAXONAME );
		} else {
			$ress = false;
		}
		$curlangname = '';
		if ( $ress ) {
			$obj_term = $ress[0];
			if ( '' != $obj_term->name ) :
				$curlangname = $obj_term->name;
			endif;
		}

		echo '<h4>' . esc_html__( 'Check the language for this link', 'xili-language' ) . '</h4><div style="line-height:1.7em;">';
		// built the check series with saved check if edit
		$listlanguages = get_terms_of_groups_lite( $this->langs_group_id, TAXOLANGSGROUP, TAXONAME, 'ASC' );
		$l = 2;
		foreach ( $listlanguages as $language ) {
			if ( 0 == $l % 3 && 3 != $l ) {
				echo '<br />';
			}
			?>

			<label class="check-lang selectit" for="xili_language_check_<?php echo $language->slug; ?>"><input id="xili_language_check_<?php echo $language->slug; ?>" name="xili_language_set" type="radio" value="<?php echo $language->slug; ?>" <?php checked( $curlangname, $language->name, true ); ?> /> <?php esc_html_e($language->description, 'xili-language' ); ?></label>

			<?php
		} /*link to top of sidebar*/
		?>
			<br /><label class="check-lang selectit" for="xili_language_check" ><input id="xili_language_check_ever" name="xili_language_set" type="radio" value="ev_er" <?php checked( $curlangname, "ev_er", true ); ?> /> <?php _e('Ever', 'xili-language' ); ?></label>
			<label class="check-lang selectit" for="xili_language_check" ><input id="xili_language_check" name="xili_language_set" type="radio" value="" <?php checked( $curlangname, '', true ); ?> /> <?php esc_html_e( 'undefined', 'xili-language' ); ?></label><br /></div>
			<br /><small>© xili-language <?php echo XILILANGUAGE_VER; ?></small>
		<?php

	}

	public function print_styles_link_edit() {
		echo "<!---- xl options css links ----->\n";
		echo '<style type="text/css" media="screen">' . "\n";
			echo ".check-lang { border:solid 1px grey; margin:1px 0px; padding:3px 4px; width:45%; display:inline-block; }\n";
		echo "</style>\n";

		if ( 'on ' == $this->exists_style_ext && $this->xili_settings['external_xl_style'] ) {
			wp_enqueue_style( 'xili_language_stylesheet' );
		}
	}

	/**
	 * Action and filter to add language column in link-manager page
	 * @ since 1.8.5
	 */


	public function xili_manage_link_column_name( $cols ) {
		$ends = array( 'rel', 'visible', 'rating' ); // insert language before rel
		$end = array();
		foreach ( $cols as $k => $v ) {
			if ( in_array( $k, $ends ) ) {
				$end[ $k ] = $v;
				unset( $cols[ $k ] );
			}
		}
		$cols[ TAXONAME ] = __( 'Language', 'xili-language' );
		$cols = array_merge( $cols, $end );
		return $cols;
	}

	public function manage_link_lang_column( $column_name, $link_id ) {

		if ( TAXONAME != $column_name ) {
			return;
		}
		$ress = wp_get_object_terms( $link_id, 'link_' . TAXONAME );
		if ( $ress ) {
			$obj_term = $ress[0];
			echo $obj_term->name;
		}
	}

	/**
	 * To edit language when submit in edit-link-form
	 * @ since 1.8.5
	 */
	public function edit_link_set_lang( $link_id ) {
		// create relationships with link_language taxonomy
		$sellang = $_POST['xili_language_set'];
		// test if exist in link taxinomy or create it
		$linklang = term_exists( $sellang, 'link_' . TAXONAME );
		if ( ! $linklang ) {
			$lang = term_exists( $sellang, TAXONAME );
			$lang_term = get_term( $lang['term_id'], TAXONAME );
			if ( ! is_wp_error( $lang_term ) ) {
				wp_insert_term(
					$lang_term->name,
					'link_' . TAXONAME,
					array(
						'alias_of' => '',
						'description' => $lang_term->description,
						'parent' => 0,
						'slug' => $lang_term->slug,
					)
				);
			}
		}

		if ( '' != $sellang ) {
			wp_set_object_terms( $link_id, $sellang, 'link_' . TAXONAME );
		} else {
			wp_delete_object_term_relationships( $link_id, 'link_' . TAXONAME );
		}
	}

}
