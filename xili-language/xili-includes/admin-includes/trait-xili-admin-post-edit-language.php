<?php
namespace Xili_Admin;

/**
 * @package Xili-Language
 * @subpackage admin
 * functions of post edit interface
 * @since  2.23 traits files
 */

trait Xili_Admin_Post_Edit_Language {

	/**
	 * Add Translations Dashboard in post edit screen
	 *
	 * @since 2.5
	 *
	 */
	public function add_custom_box_in_post_edit() {

		$custompoststype = $this->authorized_custom_post_type();

		foreach ( $custompoststype as $key => $customtype ) {
			if ( 'enable' == $customtype['multilingual'] ) {
				$plural_name = ( isset( $customtype['name'] ) ) ? $customtype['name'] : $key;
				$singular_name = ( isset( $customtype['singular_name'] ) ) ? $customtype['singular_name'] : $key;
				/* translators: */
				add_meta_box( 'post_state', sprintf( __( '%1$s of this %2$s', 'xili-language' ), __( 'Translations', 'xili-language' ), $singular_name ), array( &$this, 'post_state_box' ), $key, 'normal', 'high' );
			}
		}
	}

	/**
	 * Display content and parts of translations dashboard metabox
	 *
	 * @since 2.5
	 *
	 */
	public function post_state_box() {
		global $post_id;
		?>
		<div id="msg-states">
			<?php $curlang = $this->post_translation_display( $post_id ); ?>
		</div>
		<div id="msg-states-comments">
			<?php $this->post_status_addons( $post_id, $curlang ); ?>
			<p class="docinfos" ><?php /* translators: */ printf( __( 'This list gathers together the titles and infos about (now and future) linked posts by language. For more info, visit the <a href="%s">wiki</a> website.', 'xili-language' ), $this->wikilink ); ?></p>
			<p class="xlversion">©xili-language v. <?php echo XILILANGUAGE_VER; ?></p>
		</div>
		<div class="clearb1">&nbsp;</div>
	<?php
	}

	/**
	 * Display main part and list of translation dashboard metabox
	 *
	 * @since 2.5
	 *
	 */
	public function post_translation_display( $post_id ) {
		global $post;
		$postlang = '';
		$test = ( 'auto-draft' == $post->post_status ) ? false : true;
		if ( true === $test ) {
			$postlang = $this->get_post_language( $post_id );
		} else {
			$postlang = ''; /* new post */
		}

		$listlanguages = get_terms_of_groups_lite( $this->langs_group_id, TAXOLANGSGROUP, TAXONAME, 'ASC' );

		if ( 'authorbrowser' == $this->xili_settings['authorbrowseroption'] ) { // setting = select language of author's browser
			$listofprefs = $this->the_preferred_languages();
			if ( is_array( $listofprefs ) ) {
				arsort( $listofprefs, SORT_NUMERIC );
				$sitelanguage = $this->match_languages( $listofprefs, $listlanguages );
				if ( $sitelanguage ) {
					$defaultlanguage = $sitelanguage->slug;
				} else {
					$defaultlanguage = '';
				}
				$mention = __( 'Your browser language preset by default for this new post...', 'xili-language' );
			} else {
				$defaultlanguage = ''; /* undefined */
			}
		} elseif ( 'authordashboard' == $this->xili_settings['authorbrowseroption'] ) {
			$current_dash_lang = strtolower( $this->admin_side_locale() );
			if ( isset( $this->langs_slug_name_array[ $current_dash_lang ] ) ) {
				$defaultlanguage = $current_dash_lang;
				$mention = __( 'Your dashboard language preset by default for this new post...', 'xili-language' );
			} else {
				$defaultlanguage = ''; /* undefined */
			}
		} else {
			$defaultlanguage = ''; /* undefined */
			$mention = '';
		}
		$this->authorbrowserlanguage = $defaultlanguage; // for right box

		if ( isset( $_GET['xlaction'] ) && isset( $_GET['xllang'] ) ) {
			// create new translation
			$targetlang = $_GET['xllang'];
			if ( 'transcreate' == $_GET['xlaction'] ) {
				$translated_post_id = $this->create_initial_translation( $targetlang, $post->post_title, $postlang, $post_id );
			}
			if ( $translated_post_id > 0 && 'redirect' == $this->xili_settings['creation_redirect'] ) {
				$url_redir = admin_url() . 'post.php?post=' . $translated_post_id . '&action=edit';

				?>
	<script type="text/javascript">
	<!--
	window.location= <?php echo "'" . $url_redir . "'"; ?>;
	//-->
	</script>
	<?php
			}
		} //elseif ( isset($_GET['xlaction'] ) && $_GET['xlaction'] == 'refresh' ) {
		if ( '' != $postlang ) {
			// refresh only if defined
			foreach ( $listlanguages as $language ) {
				if ( $language->slug != $postlang ) {
					$otherpost = $this->linked_post_in( $post_id, $language->slug );
					if ( $otherpost ) {
						$linepost = $this->temp_get_post( $otherpost );
						if ( $linepost && $otherpost != $post_id ) {
							// search metas of target
							$metacurlang = $this->get_cur_language( $linepost->ID ); // array
							foreach ( $listlanguages as $metalanguage ) {
								if ( $metalanguage->slug != $postlang && $metalanguage->slug != $metacurlang[ QUETAG ] ) {
									$id = get_post_meta( $linepost->ID, QUETAG . '-' . $metalanguage->slug, true );
									$locid = get_post_meta( $post_id, QUETAG . '-' . $metalanguage->slug, true ); // do not erase
									if ( '' != $id && '' == $locid && $id != $post_id ) {
										update_post_meta( $post_id, QUETAG . '-' . $metalanguage->slug, $id );
									}
								}
								if ( $metalanguage->slug == $postlang ) {
									update_post_meta( $linepost->ID, QUETAG . '-' . $metalanguage->slug, $post_id );
								}
							}
						} else {
							delete_post_meta( $post_id, QUETAG . '-' . $language->slug );
						}
					}
				}
			} // for
		}
		if ( isset( $_GET['xlaction'] ) && 'propataxo' == $_GET['xlaction'] ) {
			$this->propagate_categories_to_linked( $post_id, $postlang );
		}

		$post_type = $post->post_type;

		$post_type_object = get_post_type_object( $post_type );

		$i = 0;
		// table of languages - asc sorted
		?>
		<table id="postslist" class="widefat">
		<thead>
		<tr>
		<?php echo '<th class="language" title="' . __( 'Select the main language', 'xili-language' ) . '" >' . __( 'Language', 'xili-language' ); ?></th>
		<?php
		if ( $this->multiple_lang ) { // 2.22
			echo '<th class="language" title="' . __( 'Check other language if necessary...', 'xili-language' ) . '" >' . __( 'Other', 'xili-language' ) . '</th>';
		}
		?>
		<th class="postid"><?php esc_html_e( 'ID', 'xili-language' ); ?></th><th class="title"><?php esc_html_e( 'Title', 'xili-language' ); ?></th><th class="status" ><?php esc_html_e( 'Status' ); ?></th><th class="action" ><?php esc_html_e( 'Edit' ); ?></th></tr>
		</thead>
		<tbody id='the-linked' class='postsbody'>
		<?php
		foreach ( $listlanguages as $language ) {
			$otherpost = $this->linked_post_in( $post_id, $language->slug );

			$checkpostlang = ( '' != $postlang ) ? $postlang : $defaultlanguage; // according author language

			$checked = checked( $checkpostlang, $language->slug, false );

			$creation_edit = ( 'redirect' == $this->xili_settings['creation_redirect'] ) ? __( 'Create and edit', 'xili-language' ) : __( 'Create', 'xili-language' );

			$tr_class = ' class="lang-' . $language->slug . '" ';

			$language_name = '<span class="lang-iso"><abbr class="abbr_name" title="' . $language->description . '">' . $language->name . '</abbr></span>';

			$checkline = '<label title="' . $language->description . '" class="checklang" for="xili_language_check_' . $language->slug . '" class="selectit"></label>
			<input class="main-language" type="radio" id="xili_language_check_' . $language->slug . '" title="' . $language->description . '" name="xili_language_set" value="' . $language->slug . '"  ' . $checked . ' />
			&nbsp;&nbsp;' . $language_name;

			$hiddeninput = '<input class="inputid" id="xili_language_' . QUETAG . '-' . $language->slug . '" name="xili_language_' . QUETAG . '-' . $language->slug . '" value="" />
			<input type="hidden" name="xili_language_rec_' . QUETAG . '-' . $language->slug . '" value=""/>';

			if ( $otherpost && $language->slug != $postlang ) {
				$linepost = $this->temp_get_post( $otherpost );
				$display_link = sprintf( '<a href="%s" title="%s" target="_blank" >' . $otherpost . '</a>', get_permalink( $otherpost ), esc_attr__( 'Display this post', 'xili-language' ) ); // 2.18.2

				if ( $linepost ) {

					if ( 'trash' == $linepost->post_status ) {

						$edit = __( 'uneditable', 'xili-language' );
					} else {
						$edit = sprintf( ' <a href="%s" title="link to:%d">%s</a> ', 'post.php?post=' . $otherpost . '&action=edit', $otherpost, __( 'Edit' ) );
					}

					echo '<tr' . $tr_class . '><th title="' . $language->description . '" >&nbsp;' . $language_name . '</th>';
					if ( $this->multiple_lang ) {
						// 2.22
						echo $this->multiple_checkbox( $post_id, $language->slug );
					}
					echo '<td>' . $display_link . '</td><td>' . $linepost->post_title . '</td><td>';

					switch ( $linepost->post_status ) {
						case 'private':
							esc_html_e( 'Privately Published' );
							break;
						case 'publish':
							esc_html_e( 'Published' );
							break;
						case 'future':
							esc_html_e( 'Scheduled' );
							break;
						case 'pending':
							esc_html_e( 'Pending Review' );
							break;
						case 'trash':
							echo esc_html_x( 'Trash', 'post' );
							break;
						case 'draft':
						case 'auto-draft':
							esc_html_e( 'Draft' );
							break;
					}

					echo '</td><td>'
					. $edit
					. '</td></tr>';

				} else {
					// delete post_meta - not target post
					delete_post_meta( $post_id, QUETAG . '-' . $language->slug );
					$search = '<a class="hide-if-no-js" onclick="findPosts.open( \'lang[]\',\'' . $language->slug . '\' );return false;" href="#the-list" title="' . esc_attr__( 'Search linked post', 'xili-language' ) . '"> ' . __( 'Search', 'xili-language' ) . '</a>';

					echo '<tr' . $tr_class . '><th>' . $checkline . '</th>';
					if ( $this->multiple_lang ) { // 2.22
						echo $this->multiple_checkbox( $post_id, $language->slug );
					}
					echo '<td>' . $hiddeninput . ' </td><td>' . __( 'not yet translated', 'xili-language' )

						. '&nbsp;&nbsp;' .
						sprintf(
							/* translators: */
							'<a href="%1$s" title="%2$s">' . $creation_edit . '</a>',
							'post.php?post=' . $post_id . '&action=edit&xlaction=transcreate&xllang=' . $language->slug,
							sprintf(
								/* translators: name of language */
								esc_attr__( 'For create a linked draft translation in %s', 'xili-language' ),
								$language->name
							)
						)
						. '&nbsp;|&nbsp;' .  $search
						. '</td><td>&nbsp;</td><td>' . $search
						. '&nbsp;'
						. '</td></tr>';

				}
			} elseif ( $language->slug == $postlang ) {

				echo '<tr class="editing lang-' . $language->slug . '" ><th>' . $checkline . '</th>';
				if ( $this->multiple_lang ) { // 2.22
					echo $this->multiple_checkbox( $post_id, $language->slug, $postlang );
				}

				echo '<td>' . $post_id . '</td><td>'
				. $post->post_title
				. '</td><td>';
				switch ( $post->post_status ) {
					case 'private':
						esc_html_e( 'Privately Published' );
						break;
					case 'publish':
						esc_html_e( 'Published' );
						break;
					case 'future':
						esc_html_e( 'Scheduled' );
						break;
					case 'pending':
						esc_html_e( 'Pending Review' );
						break;
					case 'trash':
						esc_html_e( 'Trash' );
						break;
					case 'draft':
					case 'auto-draft':
						esc_html_e( 'Draft' );
						break;
				}

				echo '</td><td>&nbsp;</td></tr>';

			} else { // no linked post

				if ( in_array( $post->post_status, array( 'draft', 'pending', 'future', 'publish', 'private' ) ) && '' != $postlang ) {
					// Block editor in WP 5.0+
					if ( $this->is_block_editor_active( true ) ) {
						$search = ''; // findPosts dont work !
					} else {
						$search = '&nbsp;|&nbsp;<a class="hide-if-no-js" onclick="findPosts.open( \'lang[]\',\'' . $language->slug . '\' );return false;" href="#the-list" title="' . esc_attr__( 'Search linked post', 'xili-language' ) . '"> ' . esc_html__( 'Search' ) . '</a>';
					}

					echo '<tr' . $tr_class . '><th>' . $checkline . '</th>';
					if ( $this->multiple_lang ) {
						// 2.22
						echo $this->multiple_checkbox( $post_id, $language->slug );
					}
					echo '<td>' . $hiddeninput . '</td><td>'
					/* translators: */
					. sprintf( esc_html__( 'not yet translated in %s', 'xili-language' ), $language->description )
					/* translators: */
					. '&nbsp;&nbsp;' . sprintf( '<a href="%s" title="%s">' . $creation_edit . '</a>', 'post.php?post=' . $post_id . '&action=edit&xlaction=transcreate&xllang=' . $language->slug, sprintf( esc_html__( 'For create a linked draft translation in %s', 'xili-language' ), $language->name ) ) . $search
					. '</td><td>&nbsp;</td><td>'
					. '&nbsp;'
					. '</td></tr>';

				} else {

					if ( '' != $defaultlanguage && $defaultlanguage == $language->slug ) {
						// if post-new.php and pre-checked for author's brother
						$the_message = $mention;
						$the_class = ' class="editing lang-' . $defaultlanguage . '"';

					} else {
						/* translators: */
						$the_message = sprintf( esc_html__( 'select language %s !', 'xili-language' ), $language->description );
						$the_class = $tr_class;
					}

					echo '<tr' . $the_class . '><th>' . $checkline . '</th>';
					if ( $this->multiple_lang ) { // 2.22
						echo $this->multiple_checkbox( $post_id, $language->slug );
					}
					echo '<td>&nbsp;</td><td>'
						. '<p class="message" ><––––– ' . $the_message . '</p>'
						. '</td><td>&nbsp;</td><td>'
						. '&nbsp'
						. '</td></tr>';
				}
			}
		}
		?>
		</tbody>
		</table>
		<?php
			if ( $this->is_block_editor_active( true ) ) {
				$this->post_submit_permalink_option();
			}
		?>
		<div id="ajax-response"></div>
			<?php
			// ajax form
			$this->xili_find_posts_div( '', $post_type, $post_type_object->label );
			?>
		<?php
		return $postlang;
	}

	/**
	 * test if bloc editor is active
	 *
	 * @since 2.23
	 *
	 * @param  $test_post_type true if need to test custom post type
	 */
	public function is_block_editor_active( $test_post_type = false ) {
		$screen = get_current_screen();
		if ( empty( $screen ) ) {
			return false;
		}
		$post_type_valid = false;
		$custompoststype_keys = array_keys( $this->authorized_custom_post_type() );
		if ( $test_post_type && ! empty( $screen->post_type ) && in_array( $screen->post_type, $custompoststype_keys ) ) {
			$post_type_valid = true;
		}
		// Block editor in WP 5.0+
		if ( $post_type_valid && method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor() ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * display or not multiple language checkbox
	 *
	 * @since 2.22
	 */
	public function multiple_checkbox( $post_id, $lang_slug, $postlang = '' ) {
		$cell_content = '<td>';
		// test if undefined
		//
		$state = 0;
		$readonly = '';
		if ( $lang_slug == $postlang ) {
			$state = 1; // force
			$readonly = 'disabled ';
		}
		// get (saved) linked language taxonomies
		$terms = wp_get_object_terms( $post_id, TAXONAME, array( 'fields' => 'ids' ) );
		if ( $terms && in_array( $this->langs_ids_array[ $lang_slug ], $terms ) ) {
			$state = 1;
		}

		$checked = checked( $state, 1, false );
		$title = ( $checked ) ? '' : sprintf( 'title="%s"', __( 'check to add a secondary language', 'xili-language' ) );
		// create box
		$cell_content .= '<input class="other-language" ' . $readonly . $title . ' ' . $checked . ' type="checkbox" name="multi_lang_' . $lang_slug . '" value="' . $lang_slug . '">';

		$cell_content .= '</td>';
		return $cell_content;
	}


	/**
	 * Display right part of translations dashboard
	 *
	 * @since 2.5
	 *
	 */
	public function post_status_addons( $post_id, $curlang ) {
		$notundefinedlang = ( '' != $curlang ) ? $curlang : $this->authorbrowserlanguage; // set in left box
		$un_id = ( '' == $curlang ) ? '&nbsp;(' . $post_id . ')' : '';
		$refresh = sprintf( '<a href="%1$s" title="%2$s">%3$s</a> ', 'post.php?post=' . $post_id . '&action=edit&xlaction=refresh', esc_attr__( 'Refresh links series', 'xili-language' ), esc_html__( 'Refresh links', 'xili-language' ) );
		?>
		<p><?php echo $refresh; ?>
		<?php
		if ( '' != $curlang && current_user_can( 'xili_language_clone_tax' ) && is_object_in_taxonomy( get_post_type( $post_id ), 'category' ) ) {
			//2.6.3
			printf( '&nbsp|&nbsp;<a href="%1$s" title="%2$s">%3$s</a> ', 'post.php?post=' . $post_id . '&action=edit&xlaction=propataxo', esc_attr__( 'Propagate categories', 'xili-language' ), esc_html__( 'Propagate categories', 'xili-language' ) );
		}
		?>
		</p>
		<label for="xili_language_check" class="selectit"><?php esc_html_e( 'set post to:', 'xili-language' ); ?>&nbsp;<input class="main-language" id="xili_language_check" name="xili_language_set" type="radio" value="undefined" <?php checked( $notundefinedlang, '', true ); ?> />&nbsp;<?php esc_html_e( 'undefined', 'xili-language' ); ?><?php echo $un_id; ?></label>
		<?php
	}

	/**
	 * Add a checkbox to renew permalink (slug) with title - only appear if post created from another language
	 *
	 * @since 2.15
	 *
	 */
	public function post_submit_permalink_option() {
		global $post;
		if ( $post ) {
			$translation_state = get_post_meta( $post->ID, $this->translation_state, true );
			if ( '' != $translation_state && false !== strpos( $translation_state, 'initial' ) ) {
				$perma = ( get_option( 'permalink_structure' ) ) ? __( '(permalink)', 'xili-language' ) : '';
				?>
				<p><label for="xl_permalink_option" class="selectit"><input class="renew-perma" name="xl_permalink_option" type="checkbox" id="xl_permalink_option" value="slug" /> <?php /* translators: */ printf( esc_html__( 'Renew slug %s with title', 'xili-language' ), $perma ); ?></label></p>
				<?php
			}
		}
	}

	/**
	 * inspired by find_posts_div from wp-admin/includes/template.php
	 *
	 * @since 2.3.1 to restrict to type of post
	 *
	 * @param unknown_type $found_action -
	 */
	public function xili_find_posts_div( $found_action = '', $post_type, $post_label ) {
		/* translators: */
		?>
		<div id="find-posts" class="find-box" style="display:none;">

			<div id="find-posts-head" class="find-box-head"><?php /* translators: */ printf( esc_html__( 'Find %s', 'xili-language' ), $post_label ); ?>
				<div id="find-posts-close"></div>
			</div>
			<div class="find-box-inside">
				<div class="find-box-search">
					<?php if ( $found_action ) { ?>
						<input type="hidden" name="found_action" value="<?php echo esc_attr( $found_action ); ?>" />
					<?php } ?>

					<input type="hidden" name="affected" id="affected" value="" />
					<?php wp_nonce_field( 'find-post-types', '_ajax_nonce', false ); ?>
					<label class="screen-reader-text" for="find-posts-input"><?php esc_html_e( 'Search' ); ?></label>
					<input type="text" id="find-posts-input" name="ps" value="" />
					<input type="button" id="find-posts-search" value="<?php esc_attr_e( 'Search' ); ?>" class="button" />
					<div class="clear"></div>
					<?php /* checks replaced by hidden - see js findposts*/ ?>
					<input type="hidden" name="find-posts-what" id="find-posts-what" value="<?php echo esc_attr( $post_type ); ?>" />

				</div>
				<div id="find-posts-response"></div>
			</div>
			<div class="find-box-buttons">
				<?php submit_button( __( 'Select' ), 'button-primary alignright', 'find-posts-submit', false ); ?>
			</div>
		</div>
	<?php
	}

	/**
	 * scripts for findposts only in post-new and post
	 * @since 2.2.2
	 */
	public function find_post_script() {
		global $post;
		if ( 'attachment' != get_post_type( $post->ID ) ) {
			wp_enqueue_script( 'wp-ajax-response' );
			wp_enqueue_script( 'jquery-ui-draggable' );
			$suffix = defined( 'WP_DEBUG' ) && WP_DEBUG ? '.dev' : '.min'; // 2.8.8
			wp_enqueue_script( 'xili-find-post', plugin_dir_url( XILILANGUAGE_PLUGIN_FILE ) . 'js/xili-findposts' . $suffix . '.js', '' , XILILANGUAGE_VER );
		}
	}


	public function wp_ajax_find_post_types() {
		//global $wp_version ;

		check_ajax_referer( 'find-post-types' );

		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		unset( $post_types['attachment'] );

		$s = wp_unslash( $_POST['ps'] );
		$searchand = $search = '';
		$args = array(
			'post_type' => array( $_POST['post_type'] ), //array_keys( $post_types ),
			'post_status' => 'any',
			'posts_per_page' => 50,
		);
		if ( '' !== $s ) {
			$args['s'] = $s;
		}

		$posts = get_posts( $args );

		if ( ! $posts ) {
			wp_die( __( 'No items found.' ) );
		}

		$html = '<table class="widefat" cellspacing="0"><thead><tr><th class="found-radio"><br /></th><th>' . __( 'Title' ) . '</th><th class="no-break">' . __( 'Type' ) . '</th><th class="no-break">' . __( 'Date' ) . '</th><th class="no-break">' . __( 'Status' ) . '</th></tr></thead><tbody>';
		foreach ( $posts as $post ) {
			$title = trim( $post->post_title ) ? $post->post_title : __( '(no title)' );

			switch ( $post->post_status ) {
				case 'publish':
				case 'private':
					$stat = __( 'Published' );
					break;
				case 'future':
					$stat = __( 'Scheduled' );
					break;
				case 'pending':
					$stat = __( 'Pending Review' );
					break;
				case 'draft':
					$stat = __( 'Draft' );
					break;
			}

			if ( '0000-00-00 00:00:00' == $post->post_date ) {
				$time = '';
			} else {
				/* translators: date format in table columns, see http://php.net/date */
				$time = mysql2date( __( 'Y/m/d' ), $post->post_date );
			}

			$html .= '<tr class="found-posts"><td class="found-radio"><input type="radio" id="found-' . $post->ID . '" name="found_post_id" value="' . esc_attr( $post->ID ) . '"></td>';
			$html .= '<td><label for="found-' . $post->ID . '">' . esc_html( $title ) . '</label></td><td class="no-break">' . esc_html( $post_types[ $post->post_type ]->labels->singular_name ) . '</td><td class="no-break">' . esc_html( $time ) . '</td><td class="no-break">' . esc_html( $stat ) . ' </td></tr>' . "\n\n";
		}

		$html .= '</tbody></table>';
		wp_send_json_success( $html );
	}



}
