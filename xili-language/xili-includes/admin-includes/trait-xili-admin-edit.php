<?php
namespace Xili_Admin;

/**
 * @package class-xili-language-admin
 * functions modifying edit interface
 */
trait Xili_Admin_Edit {

	/**************** List of Posts (edit.php) *******************/

	/**
	 * display languages column name in Posts/Pages list
	 *
	 * @updated 1.8.9
	 *
	 * complete get_column_headers (screen.php) filter
	 *
	 * @since 2.9.10
	 */
	public function xili_manage_column_name( $cols ) {
		global $post_type;
		if ( defined( 'XDMSG' ) ) {
			$cpts = array( XDMSG );
		}
		$cpts[] = 'page';
		$cpts[] = 'post';
		$cpts[] = 'attachment'; // 2.8.4.1

		$custompoststype = $this->xili_settings['multilingual_custom_post'];
		if ( array() != $custompoststype ) {
			foreach ( $custompoststype as $key => $customtype ) {
				if ( 'enable' == $customtype['multilingual'] ) {
					$cpts[] = $key;
				}
			}
		}

		//$post_type = $_GET['post_type'];//get_post_type( $post ); 2.22.1

		if ( in_array( $post_type, $cpts ) ) {

			$ends = apply_filters( 'xiliml_manage_column_name', array( 'comments', 'date', 'rel', 'visible' ), $cols, $post_type ); // 2.8.1
			$end = array();
			foreach ( $cols as $k => $v ) {
				if ( in_array( $k, $ends ) ) {
					$end[ $k ] = $v;
					unset( $cols[ $k ] );
				}
			}
			$cols[ TAXONAME ] = __( 'Language', 'xili-language' );
			$cols = array_merge( $cols, $end );
		}

		return $cols;
	}

	public function xili_manage_column( $name, $id ) {
		global $wp_query; // 2.8.1
		global $post_type; // 2.22.1
		if ( TAXONAME != $name ) {
			return;
		}
		$output = '';
		$terms = wp_get_object_terms( $id, TAXONAME ); // get languages
		if ( $terms ) {
			if ( metadata_exists( 'post', $id, '_multiple_lang' ) && $lang_array = get_post_meta( $id, '_multiple_lang', true ) ) {
				// sorted terms
				$sorted_languages = array();
				foreach ( $terms as $term ) {
					if ( $term->slug == $lang_array[0] ) {
						array_unshift( $sorted_languages, $term );
					} else {
						$sorted_languages[] = $term;
					}
				}
			} else {
				$sorted_languages = $terms;
			}
			$first = true;
			foreach ( $sorted_languages as $term ) {
				if ( $first ) {
					$first = false;
				} else {
					$output .= ', ';
				}

				if ( current_user_can( 'activate_plugins' ) ) {
					$output .= '<span class="curlang lang-' . $term->slug . '"><a href="options-general.php?page=language_page" title="'
					/* translators: */
					. sprintf( esc_attr__( 'Post in %s. Link to see list of languages…', 'xili-language' ), $term->description ) . '" >'; /* see more precise link ?*/
					$output .= $term->name . '</a></span>';
				} else {
					/* translators: */
					$output .= '<span title="' . esc_attr( sprintf( __( 'Post in %s.','xili-language'), $term->description ) )
					. '" class="curlang lang-' . $term->slug . '">' . $term->name . '</span>';
				}

				$output .= '<input type="hidden" id="' . QUETAG . '-' . $id . '" value="' . $term->slug . '" >'; // for Quick-Edit - 1.8.9
			}
		}
		$xdmsg = ( defined( 'XDMSG' ) ) ? XDMSG : '';

		//$post_type = ( isset( $wp_query->query_vars['post_type' ] ) ) ? $wp_query->query_vars['post_type' ] : '' ;

		if ( $post_type != $xdmsg ) { // no for XDMSG
			$output .= '<br />';

			$result = $this->translated_in( $id );

			$output .= apply_filters( 'xiliml_language_translated_in_column', $this->display_translated_in_result( $result ), $result, $post_type );
		}
		echo '<div id="' . TAXONAME . '-' . $id . '">' . $output . '</div>'; // called by do_action() class wp_posts_list_table 2.9.10
	}

	/**
	*
	*/

	public function display_translated_in_result( $result ) {
		$output = '';
		if ( '' == $result ) {
			$output .= __( 'not yet translated', 'xili-language' );
		} else {
			$output .= __( 'translated in:', 'xili-language' );
			$output .= '&nbsp;<span class="translated-in">' . $result . '</span>';
		}
		return $output;
	}

	/******************************* TAXONOMIES ****************************/

	public function xili_manage_tax_column_name( $cols ) {

		$ends = array( 'posts' );
		$end = array();
		foreach ( $cols as $k => $v ) {
			if ( in_array( $k, $ends ) ) {
				$end[ $k ] = $v;
				unset( $cols[ $k ] );
			}
		}
		$cols[ TAXONAME ] = __( 'Language', 'xili-language' );
		$cols = array_merge( $cols, $end );

		$this->local_theme_mos = $this->get_localmos_from_theme();

		return $cols;
	}

	public function xili_manage_tax_column( $content, $name, $id ) {
		if ( TAXONAME == $name || 'description' == $name ) {
			global $taxonomy;
			$tax = get_term( (int) $id, $taxonomy );
			$a = '<div class="taxinmoslist" >';

			$text = ( TAXONAME == $name ) ? $tax->name : $tax->description;

			$the_translation_results = $this->is_msg_saved_in_localmos( $text, 'msgid', '', 'array' );

			if ( $the_translation_results ) {
				foreach ( $the_translation_results as $lang_slug => $translation ) {
					$a .= '<span class="lang-' . $lang_slug . '" >' . $translation['lang_name'] . '</span>&nbsp;:&nbsp;';
					$a .= $translation['msg_id_str']['msgstr'] . '<br />';
				}
			} else {
				$a .= __( 'need mo file', 'xili-language' ) . ' ';
			}
			$a .= '</div>';

			return $content . $a; // 2.8.1 - to have more than one filter for this column ! #21222 comments...

		} else {
			return $content; // to have more than one added column 2.8.1
		}
	}

	public function xili_manage_tax_action( $actions, $tag ) {
		return $actions;
	}

	public function show_translation_msgstr( $tag, $taxonomy ) {
		if ( ! class_exists( 'xili_dictionary' ) ) {
		?>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="description"><?php esc_html_e( 'Translated in', 'xili-language' ); ?></label></th>
			<td>
			<?php
			echo '<fieldset class="taxinmos" ><legend>' . esc_html__( 'Name' ) . '</legend>';
			$a = $this->is_msg_saved_in_localmos( $tag->name, 'msgid', '', 'single' );
			echo $a[0];

			echo '</fieldset><br /><fieldset class="taxinmos" ><legend>' . __( 'Description' ) . '</legend>';
			$a = $this->is_msg_saved_in_localmos( $tag->description, 'msgid', '', 'single' );
			echo $a[0];

			echo '</fieldset>';

			?>
			<p><em><?php esc_html_e( 'This list above gathers the translations of name and description saved in current local-xx_XX.mo files of the current theme.', 'xili-language' ); ?></em></p>
			</td>
		</tr>

		<?php
		}
	}

	/**
	 * Bulk actions
	 * Thanks to wpengineer.com
	 * @since 2.22.4 [<description>]
	 */
	public function register_my_bulk_post_actions( $bulk_actions ) {
		$bulk_actions['bulk_remove_multiple_languages_action'] = __( 'Remove secondary languages', 'xili-language' );
		//$bulk_actions['my_other_bulk_action'] = __( 'My Other Bulk Action', 'xili-language');
		return $bulk_actions;
	}

	public function bulk_action_handlers( $redirect_to, $action_name, $post_ids ) {

		if ( 'bulk_remove_multiple_languages_action' === $action_name ) {
			foreach ( $post_ids as $post_id ) {

				if ( metadata_exists( 'post', $post_id, '_multiple_lang' ) && $lang_array = get_post_meta( $post_id, '_multiple_lang', true ) ) {
					foreach ( $lang_array as $key => $onelang ) {
						if ( $key > 0 ) {
							wp_remove_object_terms( $post_id, $onelang, TAXONAME );
						}
					}
					$new_array = array( $lang_array[0] ); // only the main
					update_post_meta( $post_id, '_multiple_lang', $new_array );
				}
			}
			$redirect_to = add_query_arg( 'bulk_posts_processed', count( $post_ids ), $redirect_to );
			return $redirect_to;

		} elseif ( 'my_other_bulk_action' === $action_name ) {
			foreach ( $post_ids as $post_id ) {
				// $post = get_post($post_id);
				// process $post wp_update_post($post);
			}
			$redirect_to = add_query_arg( 'other_bulk_posts_precessed', count( $post_ids ), $redirect_to );
			return $redirect_to;

			$dummy = 1;
		} else {
			return $redirect_to;
		}
	}

	public function my_bulk_action_admin_notice() {
		if ( ! empty( $_REQUEST['bulk_posts_processed'] ) ) {
			global $post_type;
			$post_type_object = get_post_type_object( $post_type );

			$posts_count = intval( $_REQUEST['bulk_posts_processed'] );
			echo '<div class="notice notice-success is-dismissible">';
			printf(
				' ' . _n( 'Processed %1$s %2$s.', 'Processed %1$s %3$s.', $posts_count, 'xili-language' )
				. '<br />' . __( 'Secondary languages deleted.', 'xili-language' ),
				$posts_count,
				$post_type_object->labels->singular_name,
				$post_type_object->label
			);
			echo '</div>';
		}
	}

	/**
	 * Insert translation for taxonomies columns in edit.php - only dashboard yet
	 *
	 * filter from sanitize_term_field  at end {$taxonomy}_{$field} name here
	 *
	 * @since 2.13.3
	 *
	 */
	public function translated_taxonomy_name( $value, $term_id, $context ) {
		if ( 'display' == $context ) {

			$locale = $this->admin_side_locale();
			$this->add_local_text_domain_file( $locale ); // called here - 2.20.3

			$theme_domain = the_theme_domain();
			$translated = xl__( $value, $theme_domain );

			$tvalue = ( 'en_US' != $locale && $translated != $value ) ? $value . ' (' . $translated . ')' : $value;
		} else {
			$tvalue = $value;
		}
		return $tvalue;
	}

	/**
	 * Insert popup in quickedit at end (filter quick_edit_custom_box - template.php)
	 *
	 * @since 1.8.9
	 *
	 * hook with only two params - no $post_id - populated by.. || $type != 'post' || $type != 'page'
	 *
	 */
	public function languages_custom_box( $col, $type ) {
		if ( 'edit-tags' == $type ) {
			return;
		}
		if ( TAXONAME != $col ) {
			return;
		}

		$listlanguages = $this->get_listlanguages();
		$margintop = ( 'page' == $type ) ? 'toppage' : 'toppost';
		?>

		<fieldset class="inline-edit-language langquickedit <?php echo $margintop; ?>" ><legend><em><?php esc_html_e( 'Language', 'xili-language' ); ?></em></legend>

			<select name="xlpop" id="xlpop">
				<option value=""> <?php esc_html_e( 'undefined', 'xili-language' ); ?> </option>
				<?php
				foreach ( $listlanguages as $language ) {
					echo '<option value="' . $language->slug . '">' . xl__( $language->description, 'xili-language' ) . '</option>';
					// no preset values now (see below)
				}
				?>
			</select>
		</fieldset>


	<?php
	}

	/**
	 * script used in quick and bulk edit
	 *
	 * @since 2.9.10
	 *
	 */
	public function quick_edit_add_script() {
		$suffix = defined( 'WP_DEBUG' ) && WP_DEBUG ? '.dev' : '.min';
		wp_enqueue_script( 'xl-admin-edit', plugin_dir_url( XILILANGUAGE_PLUGIN_FILE ) . 'js/xl_quick_edit' . $suffix . '.js', array( 'jquery', 'inline-edit-post' ), '', true );
	}

	/**
	 * Insert popup in BULK quickedit at end (filter bulk_edit_custom_box - template.php)
	 *
	 * @since 1.8.9.3
	 *
	 * hook with only two params - no $post_id - populated by.. || $type != 'post' || $type != 'page'
	 *
	 */
	public function hidden_languages_custom_box( $col, $type ) {
		if ( TAXONAME != $col ) {
			return;
		}
		$listlanguages = $this->get_listlanguages();
		$margintop = ( 'page' == $type ) ? '-40px' : '0';
		?>

		<label class="alignright">
			<span class="title"><?php esc_html_e( 'Language', 'xili-language' ); ?></span>
			<select name="xlpop" id="xlpop">
			<option value="-1"> <?php esc_html_e( '&mdash; No Change &mdash;' ); ?> </option>
			<option value="*"> <?php esc_html_e( 'undefined', 'xili-language' ); ?> </option>
			<?php
			foreach ( $listlanguages as $language ) {
				echo '<option value="' . $language->slug . '">' . xl__( $language->description, 'xili-language' ) . '</option>';
				// no preset values now (see below)
			}
			?>
			</select>
		</label>
		<?php
	}

	/**
	 * style for posts (and taxonomies) list
	 *
	 *
	 */
	public function print_styles_posts_list() {

		if ( 'upload' == get_current_screen()->base ) {
			$this->insert_news_pointer( 'media_language' ); // 2.6.3
		}
		$insert_flags = ( 'on' == $this->xili_settings['external_xl_style'] );
		echo "<!---- xl css --->\n";
		echo '<style type="text/css" media="screen">' . "\n";
		echo ".langquickedit { background: #E4EAF8; padding:0 5px 4px !important; border:1px solid #ccc; width:140px !important; float:right !important;}\n";
		echo ".toppost { margin: 0 20px 2px 7px; } \n";
		echo ".toppage { margin: -40px 20px 2px 7px; } \n";
		echo "span.curlang a { display:inline-block; font-size:80%; height:18px; width:20px; } \n";
		echo "span.translated-in a { display:inline-block; text-indent:-9999px; width:25px; border:0px solid red;} \n";
		if ( $insert_flags ) {
			echo 'div.taxinmos span[class|="lang"] { display:inline-block; text-indent:-9999px; width:20px; border:0px solid red; }' . "\n";
			echo 'fieldset.taxinmos span[class|="lang"] { display:inline-block; text-indent:-9999px; width:20px; border:0px solid red; }' . "\n";
		}
		$listlanguages = $this->get_listlanguages();

		if ( $this->style_folder == get_stylesheet_directory_uri() ) {
			$folder_url = $this->style_folder . '/images/flags/';
		} else {
			$folder_url = $this->style_folder . '/xili-css/flags/';
		}

		foreach ( $listlanguages as $language ) {
			$ok = false;
			$flag_id = $this->get_flag_series( $language->slug, 'admin' );
			if ( 0 != $flag_id ) {
				$flag_uri = wp_get_attachment_url( $flag_id );
				$ok = true;
			} else {
				$flag_uri = $folder_url . $language->slug . '.png';
				$ok = file_exists( $this->style_flag_folder_path . $language->slug . '.png' );
			}

			if ( $insert_flags && $ok ) {

				echo 'span.lang-' . $language->slug . ' { background: url(' . $flag_uri . ") no-repeat 0% center } \n";
				echo 'span.translated-in a.lang-' . $language->slug . ' { background: transparent url(' . $flag_uri . ") no-repeat 50% center ; } \n";
				echo 'span.curlang.lang-' . $language->slug . " a { color:#f5f5f5; text-indent:-9999px ;}\n";

				if ( class_exists( 'xili_tidy_tags' ) ) {
					echo "div#xtt-edit-tag span.curlang.lang-" . $language->slug . " { margin-left:5px; color:#f5f5f5; display:inline-block; height:18px; width:25px; text-indent:-9999px ; }\n";
				}

			} else {
				echo 'span.curlang.lang-' . $language->slug . " a { font-size:100%; text-align: left; }\n";
			}
		}
		echo "</style>\n";

		if ( 'on' == $this->exists_style_ext && $this->xili_settings['external_xl_style'] ) {
			wp_enqueue_style( 'xili_language_stylesheet' );
		}
	}

	/**
	 * language saved via ajax for bulk_edit
	 *
	 * @since 2.9.10
	 *
	 */
	public function save_bulk_edit_language() {
		// get our variables
		$post_ids = ( isset( $_POST['post_ids'] ) && ! empty( $_POST['post_ids'] ) ) ? sanitize_text_field( wp_unslash( $_POST['post_ids'] ) ) : array();
		$assigned_lang = ( isset( $_POST['assigned_lang'] ) && ! empty( $_POST['assigned_lang'] ) ) ? $_POST['assigned_lang'] : null;
		// if everything is in order
		if ( ! empty( $post_ids ) && is_array( $post_ids ) && ! empty( $assigned_lang ) ) {

			$listlanguages = $this->get_listlanguages();

			foreach ( $post_ids as $post_id ) {

				$previous_lang = $this->get_post_language( $post_id );

				if ( '-1' != $assigned_lang && '*' != $assigned_lang ) {

					if ( $assigned_lang != $previous_lang ) {

						foreach ( $listlanguages as $language ) {

							$target_id = get_post_meta( $post_id, QUETAG . '-' . $language->slug, true );
							if ( '' != $target_id ) {
								if ( '' != $previous_lang ) {
									delete_post_meta( $target_id, QUETAG . '-' . $previous_lang );
								}
								update_post_meta( $target_id, QUETAG . '-' . $assigned_lang, $post_id );
							}
						}
					}

					wp_set_object_terms( $post_id, $assigned_lang, TAXONAME );
				} elseif ( '*' == $assigned_lang ) { // undefined

					foreach ( $listlanguages as $language ) {
							//delete_post_meta( $post_id, QUETAG.'-'.$language->slug ); // erase translated because undefined - not yet
						$target_id = get_post_meta( $post_id, QUETAG . '-' . $language->slug, true );
						if ( '' != $target_id ) {
							delete_post_meta( $target_id, QUETAG . '-' . $previous_lang );
							delete_post_meta( $post_id, QUETAG . '-' . $language->slug ); // 2.22.4 - break the link
						}
					}

					wp_delete_object_term_relationships( $post_id, TAXONAME );
				}
			}
		}
		die();
	}

	/**
	 * Add Languages selector in edit.php edit after Category Selector (hook: restrict_manage_posts)
	 * top of table
	 * @since 1.8.9
	 * @updated 2.13.2 - restricted if not authorized - adapted message for XD
	 *
	 */
	public function restrict_manage_languages_posts() {
		global $post_type; // 2.22.1

		if ( defined( 'XDMSG' ) ) {
			$cpts = array( XDMSG );
		}
		$cpts[] = 'page';
		$cpts[] = 'post';
		$cpts[] = 'attachment'; // 2.8.4.1

		$custompoststype = $this->xili_settings['multilingual_custom_post'];
		if ( array() != $custompoststype ) {
			foreach ( $custompoststype as $key => $customtype ) {
				if ( 'enable' == $customtype['multilingual'] ) {
					$cpts[] = $key;
				}
			}
		}

		//$post_type = get_post_type();

		if ( in_array( $post_type, $cpts ) ) {

			$listlanguages = $this->get_listlanguages();

			$without = ( defined( 'XDMSG' ) && XDMSG == $post_type ) ? __( 'Only msgid', 'xili-language' ) : __( 'Without language', 'xili-language' );
			$view_all = ( defined( 'XDMSG' ) && XDMSG == $post_type ) ? __( 'All msg', 'xili-language' ) : __( 'View all languages', 'xili-language' );
			/* translators: */
			$cpt_name = ( defined( 'XDMSG' ) && XDMSG == $post_type ) ? __( 'msgstr in %s', 'xili-language' ) : '%s';
			?>
			<select name="<?php echo QUETAG; ?>" id="<?php echo QUETAG; ?>" class='postform'>
				<option value=""> <?php echo $view_all; ?> </option>

				<option value="<?php echo LANG_UNDEF; ?>" <?php selected( ( isset( $_GET[ QUETAG ] ) && LANG_UNDEF == $_GET[ QUETAG ] ), true, true ); ?> > <?php echo $without; ?> </option>

				<?php
				foreach ( $listlanguages as $language ) {
					//$selected = ( isset( $_GET[QUETAG] ) && $language->slug == $_GET[QUETAG] ) ? "selected=selected" : "" ;
					$selected = selected( ( isset( $_GET[ QUETAG ] ) && $language->slug == $_GET[ QUETAG ] ), true, false );
					echo '<option value="' . $language->slug . '" ' . $selected . ' >' . sprintf( $cpt_name, xl__( $language->description, 'xili-language' ) ) . '</option>';
				}
				?>
				</select>
			<?php
		}

	}

}
