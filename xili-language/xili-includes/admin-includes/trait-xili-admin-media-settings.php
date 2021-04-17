<?php
namespace Xili_Admin;

/**
 * @package Xili-Language
 * @subpackage admin
 * functions for medias and flags interface
 * @since  2.23 traits files
 */

trait Xili_Admin_Media_Settings {

	/**************** Attachment post language *******************/

	public function add_language_attachment_fields( $form_fields, $post ) {

		if ( current_theme_supports( 'custom-header' ) ) {
			$meta_header = get_post_meta( $post->ID, '_wp_attachment_is_custom_header', true );
			if ( ! empty( $meta_header ) && get_option( 'stylesheet' ) == $meta_header ) {
				$form_fields['_final'] = '<strong>' . __( 'This media is inside Header Image list.', 'xili-language' ) . '</strong><br /><br /><small>© xili-language v.' . XILILANGUAGE_VER . '</small>';
				return $form_fields;
			}
		}

		$context = get_post_meta( $post->ID, '_wp_attachment_context', true );
		$attachment_post_language = get_cur_language( $post->ID, 'slug' );
		$listlanguages = $this->get_listlanguages();

		if ( ! empty( $context ) && in_array( $context, $this->custom_xili_flags ) ) {
			$form_fields['_final'] = '<strong>' . esc_html__( 'This media is a flag for multilingual context. See the box on the right sidebar...', 'xili-language' ) . '</strong><br /><br /><small>© xili-language v.' . XILILANGUAGE_VER . '</small>';
			return $form_fields;
		}

		$attachment_id = $post->ID;

		// get list of languages for popup
		$attachment_post_language = get_cur_language( $attachment_id, 'slug' );

		$listlanguages = $this->get_listlanguages();
		// get_language
		if ( '' != $attachment_post_language ) { // impossible to change if already assigned
			$name = $this->langs_slug_name_array[ $attachment_post_language ];
			$fullname = $this->langs_slug_fullname_array[ $attachment_post_language ];
			$form_fields['attachment_post_language'] = array(
				'label'      => __( 'Language', 'xili-language' ),
				'input'      => 'html',
				'html'       => "<hr /><strong>$fullname</strong> ($name)<input type='hidden' name='attachments[$attachment_id][attachment_post_language]' value='" . $attachment_post_language . "' /><br />",
				'helps'      => __( 'Language of the file caption and description.', 'xili-language' ),
			);

		} else { // selector

			$html_input = '<hr /><select name="attachments[' . $attachment_id . '][attachment_post_language]" ><option value="undefined">' . __( 'Choose…', 'xili-language' ) . '</option>';
			foreach ( $listlanguages as $language ) {
				$selected = selected( ( '' != $attachment_post_language && $language->slug == $attachment_post_language ), true, false );
				$html_input .= '<option value="' . $language->slug . '" ' . $selected . '>' . $language->description . ' (' . $language->name . ')</option>';
			}
			$html_input .= '</select>';

			$form_fields['attachment_post_language'] = array(
				'label'      => __( 'Language', 'xili-language' ),
				'input'      => 'html',
				'html'       => $html_input,
				'helps'      => __( 'Language of the file caption and description.', 'xili-language' ),
			);
		}

		if ( isset( $post->ID ) && get_current_screen() ) { // test ajax WP 3.5
			$clone = ( 'post' == get_current_screen()->base && 'attachment' == get_post_type( $post->ID ) ) ? true : false;
		} else {
			$clone = false; // not visible if called by ajax
		}

		if ( '' != $attachment_post_language && $clone ) { // only in media edit not in media-upload

			$result = $this->translated_in( $attachment_id, 'link', 'edit_attachment' );

			$trans = $this->translated_in( $attachment_id, 'array' );
			$html_input = '<hr />';
			if ( '' == $result ) {
				$html_input .= __( 'not yet translated', 'xili-language' );
				$label = __( 'No clone', 'xili-language' );
				$helps = __( 'You must create a clone in other language if necessary.', 'xili-language' );
			} else {
				$html_input .= __( 'Title, caption and description are already available in language', 'xili-language' );
				$html_input .= '&nbsp;:&nbsp;<span class="translated-in">' . $result . '</span><br />';
				$label = __( 'Clones', 'xili-language' );
				$helps = __( 'A clone of attachment contains the same image but not the same editable texts.', 'xili-language' );
			}

			$form_fields['infos_about_clones'] = array(
				'label'      => $label,
				'input'      => 'html',
				'html'       => $html_input,
				'helps'      => $helps,
			);
			$html_input = '<hr />';
			$html_input .= '<input type="hidden" id="xl_post_parent" name="xl_post_parent" value="' . $post->post_parent . '" />';

			$select_options = '';
			foreach ( $listlanguages as $language ) {
				if ( $language->slug != $attachment_post_language && ! isset( $trans[ $language->slug ] ) ) {
					$select_options .= '<option value="' . $language->slug . '" >' . $language->description . ' (' . $language->name . ')</option>';
				}
			}
			if ( $select_options ) {
				$html_input .= '<br />';
				$html_input .= '<select name="attachments[' . $attachment_id . '][create_clone_attachment_with_language]" ><option value="undefined">' . __( 'Select…', 'xili-language' ) . '</option>';

				$html_input .= $select_options . '</select>';

				$form_fields['create_clone_attachment_with_language'] = array(
					'label'      => __( 'Create clone in language', 'xili-language' ),
					'input'      => 'html',
					'html'       => $html_input,
					/* translators: */
					'helps'      => sprintf( esc_html__( 'Select a language, and after clicking the button %s : A clone with same file will be created to translate title, caption, alt text and description.', 'xili-language' ), '<strong>' . esc_html__( 'Update' ) . '</strong>' ),

				);
			}

			if ( $post->post_parent > 0 ) {
				$html_input = '<hr /><strong>' . sprintf( '%s:&nbsp;', __( 'attached to', 'xili-language' ) ) . get_the_title( $post->post_parent ) . '</strong>';
				$html_input .= '&nbsp;&nbsp;<a href="post.php?post=' . $post->post_parent . '&action=edit" title="' . __( 'Edit' ) . '" >' . __( 'Edit' ) . '</a>';
				$helps = __( 'This titled post above has this media as attachment.', 'xili-language' );
			} else {
				$html_input = '<hr /><strong>' . __( 'not attached to a post.', 'xili-language' ) . '</strong>';
				$helps = __( 'In the Media Library table, it is possible to attach a media to a post.', 'xili-language' );
			}

			$form_fields['attachment-linked-post'] = array(
				'label'      => __( '<small>Info: </small>This media is', 'xili-language' ) . '&nbsp;&nbsp',
				'input'      => 'html',
				'html'       => $html_input,
				'helps'      => $helps,
			);

			$form_fields['_final'] = '<small>© xili-language v.' . XILILANGUAGE_VER . '</small>';
		}

		return $form_fields;
	}

	/**
	 * add media states if media is flag
	 *
	 * @since 2.15
	 * @since 2.16.4 - admin flag
	 */
	public function add_display_media_states( $media_states ) {
		global $post;

		$stylesheet = get_option( 'stylesheet' );
		$meta_header = get_post_meta( $post->ID, '_wp_attachment_is_custom_xili_flag', true ); // true for this current theme
		$context = get_post_meta( $post->ID, '_wp_attachment_context', true );
		if ( ! empty( $context ) && in_array( $context, $this->custom_xili_flags ) && ! empty( $meta_header ) && $meta_header == $stylesheet ) {
			$media_states[] = ( 'custom_xili_flag' == $context ) ? __( 'Flag', 'xili-language' ) : __( 'Admin Flag', 'xili-language' );
		}
		return $media_states;
	}

	public function attachment_submitbox_flag_metadata() {
		global $post;
		$context = get_post_meta( $post->ID, '_wp_attachment_context', true );
		?>
		<div class="misc-pub-section" >
		<label for="context" class="selectit"><?php esc_html_e( 'Set as', 'xili-language' ); ?>:&nbsp;
			<select name="context" id="context">
				<option value="" <?php selected( $context, 'custom_xili_flag' ); ?>><?php esc_html_e( 'define...', 'xili-language' ); ?></option>
				<option value="custom_xili_flag" <?php selected( $context, 'custom_xili_flag' ); ?>><?php esc_html_e( 'Menu flag', 'xili-language' ); ?></option>
				<option value="admin_custom_xili_flag" <?php selected( $context, 'admin_custom_xili_flag' ); ?>><?php esc_html_e( 'Admin flag', 'xili-language' ); ?></option>
			</select>

			</label>
		</div>
		<?php
		// fixes 20140901
	}

	/**
	 * Add a meta box in Edit Media page (edit-form-advanced.php)
	 * @since 2.15
	 *
	 */
	public function add_custom_box_in_media_edit() {
		add_meta_box( 'xili_flag_as_attachment', __( 'Multilingual informations', 'xili-language' ), array( &$this, 'media_multilingual_infos_box' ), 'attachment', 'side', 'low' );
	}

	/**
	 * Function media_multilingual_infos_box [to do].
	 *
	 * @param    <type> $post The post.
	 */
	public function media_multilingual_infos_box( $post ) {
		$context = get_post_meta( $post->ID, '_wp_attachment_context', true );
		$attachment_post_language = get_cur_language( $post->ID, 'slug' );
		$listlanguages = $this->get_listlanguages();

		if ( ! empty( $context ) && in_array( $context, $this->custom_xili_flags ) ) {
			if ( '' != $attachment_post_language ) {
				echo '<p>' . esc_html__( 'This flag is assigned to a language', 'xili-language' ) . '</p>';
			} else {
				echo '<p>' . esc_html__( 'Assign this flag to a language', 'xili-language' ) . '</p>';
			}

			$html_input = '<select name="attachments[' . $post->ID . '][attachment_post_language]" ><option value="undefined">' . __( 'Choose…', 'xili-language' ) . '</option>';
			foreach ( $listlanguages as $language ) {
				$selected = selected( ( '' != $attachment_post_language && $language->slug == $attachment_post_language ), true, false );
				$html_input .= '<option value="' . $language->slug . '" ' . $selected . '>' . $language->description . ' (' . $language->name . ')</option>';
			}
			$html_input .= '</select>';
			echo  $html_input;

		} else {
			if ( '' != $attachment_post_language ) {
				// impossible to change if already assigned
				$name = $this->langs_slug_name_array[ $attachment_post_language ];
				$fullname = $this->langs_slug_fullname_array[ $attachment_post_language ];
				$html = '<p>' . __( 'This media is assigned to a language', 'xili-language' ) . '</p>';
				$html .= "<p><strong>$fullname</strong> ($name)<input type='hidden' name='attachments[{$post->ID}][attachment_post_language]' value='" . $attachment_post_language . "' /></p>";
				// more infos

				$result = $this->translated_in( $post->ID, 'link', 'edit_attachment', '<br/>' );

				if ( '' == $result ) {
					$html .= __( 'not yet translated', 'xili-language' );
				} else {
					$html .= __( 'This media has already clone(s) for translation in', 'xili-language' );
					$html .= '&nbsp;:&nbsp;<br /><span class="translated-in">' . $result . '</span><br />';
				}
			} else {
				$html = '<p>' . __( 'This media is not assigned to a language, see column on left under description textarea...', 'xili-language' ) . '</p>';
			}
			 echo $html;

		}
		echo '<p><small>© xili-language v.' . XILILANGUAGE_VER . '</small></p>';
	}

	public function update_attachment_context( $post_id ) {
		// get context
		if ( '' == $_POST['context'] ) {
			$context = get_post_meta( $post_id, '_wp_attachment_context', true );
			if ( ! empty( $context ) && in_array( $context, $this->custom_xili_flag ) ) {
				delete_post_meta( $post_id, '_wp_attachment_context' );
			}
		} else {
			update_post_meta( $post_id, '_wp_attachment_context', $_POST['context'] ); // add because now select
			$stylesheet = get_option( 'stylesheet' );
			update_post_meta( $post_id, '_wp_attachment_is_custom_xili_flag', $stylesheet );
		}
	}

	public function set_flag_register_setting() {
		$name = ( is_child_theme() ) ? get_option( 'stylesheet' ) : get_option( 'template' );

		register_setting( $this->flag_settings_name . '_group', $this->flag_settings_name, array( $this, 'flag_validate_settings' ) );
	}

	public function flag_options_theme_menu() {
		if ( ! current_theme_supports( 'custom_xili_flag' ) ) {
			return;
		}
		/* translators: */
		$this->flag_theme_page = add_theme_page( sprintf( __( '%1$s Theme Options', 'xili-language' ), get_option( 'current_theme' ) ), __( 'xili flag Options', 'xili-language' ), 'manage_options', $this->flag_settings_name, array( $this, 'flag_options_theme_page' ) );
		add_action( 'load-' . $this->flag_theme_page, array( $this, 'flag_theme_options_help_page' ) );

		add_settings_section( 'xili_flag_section_1', __( 'Multilingual language selector navigation menu options', 'xili-language' ), array( $this, 'display_one_section' ), $this->flag_settings_name . '_group' );
		$title_description = $this->get_xili_flag_options_description();

		$field_args = array(
			'option_name' => $this->flag_settings_name,
			'title' => $title_description['menu_with_flag']['title'],
			'type' => 'checkbox',
			'id' => 'menu_with_flag',
			'name' => 'menu_with_flag',
			'desc' => $title_description['menu_with_flag']['description'],
			'std' => 'with-flag',
			'label_for' => 'menu_with_flag',
			'class' => 'css_class settings',
		);
		add_settings_field( $field_args['id'], $field_args['title'], array( $this, 'display_one_setting' ), $this->flag_settings_name . '_group', 'xili_flag_section_1', $field_args );

		add_settings_section( 'xili_flag_section_2', __( 'Flag style options', 'xili-language' ), array( $this, 'display_one_section' ), $this->flag_settings_name . '_group' );

		$field_args = array(
			'option_name' => $this->flag_settings_name,
			'title' => __( 'Available flags', 'xili-language' ),
			'type' => 'xili',
			'id' => 'flags_list',
			'name' => 'flags_list',
			/* translators: */
			'desc' => sprintf( __( 'The list of images uploaded and assigned as flag in Media table. (%s)', 'xili-language' ), '<small>' . __( '*: from theme subfolder', 'xili-language' ) . '</small>' ),
			'std' => 'with-flag',
			'label_for' => 'flags_list',
			'class' => 'css_class settings',
		);
		add_settings_field( $field_args['id'], $field_args['title'], array( $this, 'display_flags_list' ), $this->flag_settings_name . '_group', 'xili_flag_section_2', $field_args );

		$defaults = $this->get_default_xili_flag_options();
		foreach ( $defaults as $key => $default_value ) {
			if ( false !== strpos( $key, 'css_' ) ) {
				$field_args = array(
					'option_name' => $this->flag_settings_name,
					'title' => $title_description[ $key ]['title'],
					'type' => 'text',
					'id' => $key,
					'name' => $key,
					'desc' => $title_description[ $key ]['description'],
					'std' => $default_value,
					'label_for' => $key,
					'class' => 'css_class settings',
					'size' => '110',
				);
				add_settings_field( $field_args['id'], $field_args['title'], array( $this, 'display_one_setting' ), $this->flag_settings_name . '_group', 'xili_flag_section_2', $field_args );
			}
		}
		$field_args = array(
			'option_name' => $this->flag_settings_name,
			'title' => __( 'Reset to default values', 'xili-language' ),
			'type' => 'checkbox',
			'id' => 'reset',
			'name' => 'reset',
			'desc' => __( 'When checking, all values will be resetted to those defined by default for bundled theme like twentyfourteen or by the author of this theme.', 'xili-language' ),
			'std' => 'reset',
			'label_for' => 'reset',
			'class' => 'css_class settings',
		);
		add_settings_field( $field_args['id'], $field_args['title'], array( $this, 'display_one_setting' ), $this->flag_settings_name . '_group', 'xili_flag_section_2', $field_args );
	}

	public function flag_options_theme_page() {
		$message = '';
		?>
		<div class="section panel">
		<h1>
		<?php
		/* translators: */
		printf( esc_html__( 'Flag Multilingual options for %1$s theme ', 'xili-language' ), get_option( 'current_theme' ) );
		?>
		</h1>
		<?php
		if ( isset( $_GET['settings-updated'] ) ) {
			switch ( $_GET['settings-updated'] ) :
				case 'true':
					$message = __( 'Flag Multilingual options updated.', 'xili-language' );
					$class = 'updated';
					break;
			endswitch;
		}
		if ( $message ) {
			echo "<div id='message' class='$class'><p>$message</p></div>\n";
		}
		?>
		<form method="post" enctype="multipart/form-data" action="options.php">
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes' ); ?>" />
			</p>
			<?php
				settings_fields( $this->flag_settings_name . '_group' ); // nonce, action (plugin.php)
				do_settings_sections( $this->flag_settings_name . '_group' );
			?>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes' ); ?>" />
			</p>
		</form>
		<p><small><?php echo get_option( 'current_theme' ); ?> by <a href="<?php echo $this->devxililink; ?>" target="_blank" >dev.xiligroup.com</a> (©2015) <?php echo '(xili-language v.' . XILILANGUAGE_VER . ')'; ?></small></p>

		</div>
		<?php
	}

	/**
	 * display Flags list in page xili flag options
	 *
	 * @updated 2.16.4
	 *
	 * able to detect flag even if not declared
	 *
	 * @since 2.15
	 */
	public function display_flags_list( $args ) {
		global $_wp_theme_features;
		extract( $args ) ;

		$available = $this->get_flag_series(); // only front-end series
		$listlanguages = $this->get_listlanguages();
		echo '<ul>';

		foreach ( $listlanguages as $one_language ) {
			if ( isset( $available [ $one_language->slug ] ) ) {
				echo '<li>' . wp_get_attachment_image( $available[ $one_language->slug ], 'full' ) . ' (' . $one_language->name . ', ' . $one_language->description . ')</li>';
			} else {
				if ( isset( $_wp_theme_features['custom_xili_flag'][0][ $one_language->slug ] ) ) {
					$url = sprintf( $_wp_theme_features['custom_xili_flag'][0][ $one_language->slug ]['path'], get_template_directory_uri(), get_stylesheet_directory_uri() );
					$width = $_wp_theme_features['custom_xili_flag'][0][ $one_language->slug ]['width'];
					$height = $_wp_theme_features['custom_xili_flag'][0][ $one_language->slug ]['height'];
					echo '<li><img src="' . $url . '"> (' .  $one_language->name . ', ' . $one_language->description . ') <small>(*)</small></li>';
				} else {
					$path_root = get_stylesheet_directory(); // 2.16.4
					$path = '%2$s/images/flags/' . $one_language->slug . '.png';
					if ( file_exists( sprintf( $path, '', $path_root ) ) ) {
						$url = get_stylesheet_directory_uri() . '/images/flags/' . $one_language->slug . '.png'; // only in current (child or not)
						/* translators: */
						echo '<li><img src="' . $url . '"> (' .  $one_language->name . ', ' . $one_language->description . ') <small>' . sprintf( __( 'undeclared flag (in theme support custom_xili_flag) available for %s', 'xili-language' ), $one_language->name . ', ' . $one_language->description ) . '</small></li>';
					} else {
						/* translators: */
						echo '<li>' . sprintf( __( 'no flag available for %s', 'xili-language' ), $one_language->name . ', ' . $one_language->description ) . '</li>';
					}
				}
			}
		}
		echo '</ul>';
		echo ( '' != $desc ) ? "<br /><span class='description'>$desc</span>" : '';
	}

	public function flag_validate_settings( $input ) {
		if ( $input ) {
			foreach ( $input as $id => $v ) {
				$newinput[ $id ] = trim( $v );
				if ( in_array( $id, array( 'css_li_hover', 'css_li_a', 'css_li_a_hover' ) ) && isset( $input[ $id ] ) ) {
					if ( substr( $newinput[ $id ], -1 ) != ';' ) {
						$newinput[ $id ] = $newinput[ $id ] . ';';
					}
				}
			}
		}
		if ( isset( $input['reset'] ) ) {
			$newinput = $this->get_default_xili_flag_options();
			if ( isset( $input['menu_with_flag'] ) ) {
				$newinput['menu_with_flag'] = $input['menu_with_flag']; // recovet first value only
			}
			return $newinput;
		}

		if ( ! isset( $input['menu_with_flag'] ) ) {
			$newinput['menu_with_flag'] = '0';
		}
		return $newinput;
	}

	public function flag_theme_options_help_page() {
		$screen = get_current_screen();

		$help = '<p>' . esc_html__( 'Each parameter contains a name and a description.', 'xili-language' ) . '</p>';
		$help .= '<p>' . esc_html__( 'Here, you define the use of frontend flags. Requires some knowledges in CSS.', 'xili-language' ) . '</p>';
		$help .= '<p>' . esc_html__( 'If, with a customized theme, you dont see nothing in frontend menu, you must examine the html of the generated header.', 'xili-language' );
		$help .= '<br />' . esc_html__( 'If you see the css lines with good flags, you must work with the menu ul selector which can not be the same as the default shown in xili flags option...', 'xili-language' );
		$help .= '<br />' . esc_html__( 'Some themes dont use current selector as in bundled themes like 2014...', 'xili-language' ) . '</p>';
		/* translators: */
		$help .= '<p>' . sprintf( __( 'More detailled infos in %s.', 'xili-language' ), sprintf( '<a href="%s">' . __( 'this site', 'xili-language' ) . '</a>', $this->fourteenlink ) ) . '</p>';

		$screen->add_help_tab(
			array(
				'id'      => $this->flag_theme_page,
				'title'   => __( 'Help' ),
				'content' => $help,
			)
		);
	}

	// attachment_fields_to_save
	// call by apply_filters('attachment_fields_to_save', $post, $attachment);
	public function set_attachment_fields_to_save( $post, $attachment ) {
		global $wpdb;

		if ( isset( $attachment['attachment_post_language'] ) ) {
			if ( '' != $attachment['attachment_post_language'] && 'undefined' != $attachment['attachment_post_language'] ) {
				wp_set_object_terms( $post['ID'], $attachment['attachment_post_language'], TAXONAME );
			} else {
				wp_delete_object_term_relationships( $post['ID'], TAXONAME );
			}
		}
		$clone = $post;
		unset( $clone['ID'] );
		if ( isset( $attachment['create_clone_attachment_with_language'] ) && 'undefined' != $attachment['create_clone_attachment_with_language'] ) {
			/* translators: */
			$clone['post_title'] = sprintf( __( 'Translate in %2$s: %1$s', 'xili-language' ), $clone['post_title'], $attachment['create_clone_attachment_with_language'] );
			if ( $clone['post_content'] ) {
				/* translators: */
				$clone['post_content'] = sprintf( __( 'Translate: %1$s', 'xili-language' ), $clone['post_content'] );
			}
			if ( $clone['post_excerpt'] ) {
				/* translators: */
				$clone['post_excerpt'] = sprintf( __( 'Translate: %1$s', 'xili-language' ), $clone['post_excerpt'] );
			}

			$parent_id = $post['xl_post_parent']; // 2.8.4.2 hidden input

			$linked_parent_id = xl_get_linked_post_in( $parent_id, $attachment['create_clone_attachment_with_language'] );
			$clone['post_parent'] = $linked_parent_id; // 0 if unknown linked id of parent in assigned language
			$clone['guid'] = $post['attachment_url']; // 2.18.1 - the URI of media and not URI of attachment itself

			// now clones
			$cloned_attachment_id = wp_insert_post( $clone );
			// clone post_meta
			$data = get_post_meta( $post['ID'], '_wp_attachment_metadata', true );
			$data_file = get_post_meta( $post['ID'], '_wp_attached_file', true );
			$data_alt = get_post_meta( $post['ID'], '_wp_attachment_image_alt', true );
			update_post_meta( $cloned_attachment_id, '_wp_attachment_metadata', $data );
			update_post_meta( $cloned_attachment_id, '_wp_attached_file', $data_file );
			if ( '' != $data_alt ) {
				/* translators: */
				update_post_meta( $cloned_attachment_id, '_wp_attachment_image_alt', sprintf( __( 'Translate: %1$s', 'xili-language' ), $data_alt ) );
			}
			// set language and links of cloned of current
			update_post_meta( $cloned_attachment_id, QUETAG . '-' . $attachment['attachment_post_language'], $post['ID'] );
			wp_set_object_terms( $cloned_attachment_id, $attachment['create_clone_attachment_with_language'], TAXONAME );

			// get already linked of cloned
			$already_linked = array();
			if ( $meta_values = $wpdb->get_results( $wpdb->prepare( "SELECT meta_value, meta_key FROM $wpdb->postmeta WHERE meta_key LIKE %s AND post_id = %d", QUETAG . '-' . '%', $post['ID'] ) ) ) {

				foreach ( $meta_values as $key_val ) {
					update_post_meta( $key_val->meta_value, QUETAG . '-' . $attachment['create_clone_attachment_with_language'], $cloned_attachment_id );
					$slug = str_replace( QUETAG . '-', '', $key_val->meta_key );
					$already_linked[ $slug ] = $key_val->meta_value;
				}
			}
			// set links of current to cloned

			update_post_meta( (int) $post['ID'], QUETAG . '-' . $attachment['create_clone_attachment_with_language'], $cloned_attachment_id );
			if ( array() != $already_linked ) {
				foreach ( $already_linked as $key => $id ) {
					update_post_meta( $post['ID'], QUETAG . '-' . $key, $id );
					if ( $key != $attachment['create_clone_attachment_with_language'] ) {
						update_post_meta( $cloned_attachment_id, QUETAG . '-' . $key, $id );
					}
				}
			}
		}

		return $post;
	}

	// called before deleting attachment by do_action( 'delete_attachment'
	public function if_cloned_attachment( $post_id ) {
		global $wpdb;
		if ( $post = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE ID = %d", $post_id ) ) ) {

			if ( 'attachment' == $post->post_type ) {
				$attachment_post_language = get_cur_language( $post_id, 'slug' );
				// test meta lang
				$linked_list = $this->translated_in( $post_id, 'array' );
				if ( array() != $linked_list ) {
					$this->dont_delete_file = true;
					// update meta in linked attachments
					// a:1:{s:5:"en_us";a:3:{s:7:"post_ID";s:4:"8537";s:4:"name";s:5:"en_US";s:11:"description";s:7:"english";}}
					foreach ( $linked_list as $lang_slug => $linked_array ) {
						delete_post_meta( $linked_array['post_ID'], QUETAG . '-' . $attachment_post_language ); // 2.18.1
					}
				} else {
					$this->dont_delete_file = false;
				}
			}
		}
	}

	// called before deleting file by apply_filters( 'wp_delete_file'
	public function if_file_cloned_attachment( $file ) {
		if ( true == $this->dont_delete_file ) {
			$file = '';
		}
		return $file;
	}


}
