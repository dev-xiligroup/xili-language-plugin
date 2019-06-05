<?php
namespace Xili_Admin;

/**
 * @package class-xili-language-admin
 * functions modifying edit interface
 */
trait Xili_Admin_Page_Languages_Settings {


	/**
	 * Manage list of languages
	 * @since 0.9.0
	 */
	public function on_load_page() {
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'wp-lists' );
			wp_enqueue_script( 'postbox' );
			add_meta_box( 'xili-language-sidebox-theme', __( 'Current theme infos', 'xili-language' ), array( &$this, 'on_sidebox_4_theme_info' ), $this->thehook, 'side', 'high' );
			//add_meta_box('xili-language-sidebox-msg', __('Message','xili-language'), array(&$this,'on_sidebox_msg_content'), $this->thehook , 'side', 'core');
			add_meta_box( 'xili-language-sidebox-info', __( 'Info', 'xili-language' ), array( &$this, 'on_sidebox_info_content' ), $this->thehook, 'side', 'core' );

			add_meta_box( 'xili-language-sidebox-uninstall', __( 'Uninstall Options', 'xili-language' ), array( &$this, 'on_sidebox_uninstall_content' ), $this->thehook, 'side', 'low' );

			$this->insert_news_pointer( 'languages_settings' ); // news pointer 2.6.2

	}

	/**
	 * to display the languages settings admin UI
	 *
	 * @since 0.9.0
	 * @updated 0.9.6 - only for WP 2.7.X - do new meta boxes and JS
	 *
	 */
	public function languages_settings() {

		$formtitle = __( 'Add a language', 'xili-language' ); /* translated in form */
		$submit_text = __( 'Add &raquo;', 'xili-language' );
		$cancel_text = __( 'Cancel' );
		$action = '';
		$actiontype = '';
		$language = (object) array(
			'name' => '',
			'slug' => '',
			'description' => '',
			'term_order' => '',
			); //2.2.3

		$msg = 0; /* 1.7.1 */
		if ( isset( $_POST['reset'] ) ) {
			$action = $_POST['reset'];
		} elseif ( isset( $_POST['updateoptions'] ) ) {
			$action = 'updateoptions';
		} elseif ( isset( $_POST['updateundefined'] ) ) {
			$action = 'updateundefined';
		} elseif ( isset( $_POST['menuadditems'] ) ) {
			$action = 'menuadditems';
		} elseif ( isset( $_POST['sendmail'] ) ) { //1.8.5
			$action = 'sendmail';
		} elseif ( isset( $_POST['uninstalloption'] ) ) { //1.8.8
			$action = 'uninstalloption';
		} elseif ( isset( $_POST['action'] ) ) {
			$action = $_POST['action'];
		}

		if ( isset( $_GET['action'] ) ) {
			$action = $_GET['action'];
		}
		if ( isset( $_GET['term_id'] ) ) {
			$term_id = $_GET['term_id'];
		}

		$theme_name = get_option( 'current_theme' ); // full name

		switch ( $action ) {

			case 'uninstalloption': // 1.8.8 see Uninstall Options metabox in sidebar
				$this->xili_settings['delete_settings'] = $_POST['delete_settings'];
				update_option( 'xili_language_settings', $this->xili_settings );
				break;

			case 'add':
				check_admin_referer( 'xili-language-settings' );
				$term = $_POST['language_name'];
				if ( '' != $term ) {
					$slug = $_POST['language_nicename'];
					$args = array(
						'alias_of' => '',
						'description' => $_POST['language_description'],
						'parent' => 0,
						'slug' => $slug,
					);

					$term_data = $this->safe_lang_term_creation( $term, $args );
					$doit = false;
					if ( ! is_wp_error( $term_data ) ) {

						wp_set_object_terms( $term_data['term_id'], 'the-langs-group', TAXOLANGSGROUP );
						update_term_order( $term_data['term_id'], $this->langs_group_tt_id, $_POST['language_order'] );
						$doit = true;

					} else {
						// error need insertion in group if existing term is ok
						$doit = $this->safe_insert_in_language_group( $term_data, $_POST['language_order'] );
					}

					if ( $doit ) {

						$this->xili_settings['langs_list_status'] = 'added'; // 1.6.0
						$lang_ids = $this->get_lang_ids();
						//$this->available_langs = $lang_ids ;
						$this->xili_settings['available_langs'] = $lang_ids;
						$this->xili_settings['lang_features'][ $slug ]['hidden'] = ( isset( $_POST['language_hidden'] ) ) ? $_POST['language_hidden'] : '';
						$this->xili_settings['lang_features'][ $slug ]['charset'] = ( isset( $_POST['language_charset'] ) ) ? $_POST['language_charset'] : '';
						$this->xili_settings['lang_features'][ $slug ]['alias'] = ( isset( $_POST['language_alias'] ) ) ? $_POST['language_alias'] : ''; // 2.8.2
						$this->xili_settings['theme_alias_cache'][ $theme_name ][ $slug ] = ( isset( $_POST['language_alias'] ) ) ? $_POST['language_alias'] : '';
						update_option( 'xili_language_settings', $this->xili_settings );

						$this->get_lang_slug_ids( 'edited' ); // flush - 2.9.21
						$actiontype = 'add';

						$msg = 5;

						$this->update_xili_language_term_metas_from_form( $term_data['term_id'] ); // since 2.22

					} else {
						$msg = 10;
					}
				} else {
						$msg = 10;
				}
				break;

			case 'edit':
				// check id
				if ( isset( $_GET['term_id'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'edit-' . $_GET['term_id'] ) ) {
					$actiontype = 'edited';
					$language = get_term_and_order( $term_id, $this->langs_group_tt_id, TAXONAME );
					$submit_text = __( 'Update &raquo;', 'xili-language' );
					$formtitle = __( 'Edit language', 'xili-language' );

					$msg = 3;

				} else {
					wp_die( __( 'Security check', 'xili-language' ) );
				}
				break;

			case 'edited':
				check_admin_referer( 'xili-language-settings' );
				$actiontype = 'add';
				$term_id = $_POST['language_term_id'];
				$term = $_POST['language_name']; // 2.4
				$slug = $_POST['language_nicename'];
				$args = array(
					'name' => $term,
					'alias_of' => '',
					'description' => $_POST['language_description'],
					'parent' => 0,
					'slug' => $slug,
				);
				$theids = wp_update_term( $term_id, TAXONAME, $args );
				if ( ! is_wp_error( $theids ) ) {
					wp_set_object_terms( $theids['term_id'], 'the-langs-group', TAXOLANGSGROUP );
					update_term_order( $theids['term_id'], $this->langs_group_tt_id, $_POST['language_order'] );
					$this->xili_settings['langs_list_status'] = 'edited'; // 1.6.0
					$this->xili_settings['lang_features'][ $slug ]['hidden'] = ( isset( $_POST['language_hidden'] ) ) ? $_POST['language_hidden'] : '';
					$this->xili_settings['lang_features'][ $slug ]['charset'] = $_POST['language_charset'];

					$this->xili_settings['lang_features'][ $slug ]['alias'] = ( isset( $_POST['language_alias'] ) ) ? $_POST['language_alias'] : ''; // 2.8.2
					$this->xili_settings['theme_alias_cache'][ $theme_name ][ $slug ] = ( isset( $_POST['language_alias'] ) ) ? $_POST['language_alias'] : '';

					update_option( 'xili_language_settings', $this->xili_settings );

					$this->get_lang_slug_ids( 'edited' );

					$this->update_xili_language_term_metas_from_form( $term_id );

					$msg = 4;
				} else {
					$msg = 8;

				}
				break;

			case 'delete':
				// check id
				if ( isset( $_GET['term_id'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'delete-' . $_GET['term_id'] ) ) {

					$actiontype = 'deleting';
					$submit_text = __( 'Delete &raquo;', 'xili-language' );
					$formtitle = __( 'Delete language ?', 'xili-language' );
					$language = get_term_and_order( $term_id, $this->langs_group_tt_id, TAXONAME );

					$msg = 1;

				} else {
					wp_die( __( 'Security check', 'xili-language' ) );
				}
				break;
			case 'deleting':
				check_admin_referer( 'xili-language-settings' );
				$actiontype = 'add';
				$term_id = $_POST['language_term_id'];
				$slug = $_POST['language_nicename'];
				if ( isset( $_POST['multilingual_links_erase'] ) && 'erase' == $_POST['multilingual_links_erase'] ) {
					$this->multilingual_links_erase( $term_id ); // as in uninstall.php - 1.8.8
				}

				wp_delete_object_term_relationships( $term_id, TAXOLANGSGROUP ); // degrouping
				wp_delete_term( $term_id, TAXONAME );

				$this->xili_settings['langs_list_status'] = 'deleted'; // 1.6.0
				$lang_ids = $this->get_lang_ids();
				//$this->available_langs = $lang_ids ;
				$this->xili_settings['available_langs'] = $lang_ids;
				unset( $this->xili_settings['lang_features'][ $slug ] );
				update_option( 'xili_language_settings', $this->xili_settings );

				$msg = 2;
				break;

			case 'refreshlinks':
				// refresh from PLL
				check_admin_referer( 'refresh_pll_links' );
				$themessages[11] = apply_filters( 'recreate_links_from_previous', array() ); // in pll-functions.php file
				$msg = 11;
				$actiontype = 'add';
				break;

			case 'reset':
				$actiontype = 'add';
				break;

			default:
				$actiontype = 'add';
		}
		/* register the main boxes always available */
		add_meta_box( 'xili-language-lang-list', __( 'List of languages', 'xili-language' ), array( &$this, 'on_box_lang_list_content' ), $this->thehook, 'normal', 'high' );
		add_meta_box( 'xili-language-lang-form', __( 'Language', 'xili-language' ), array( &$this, 'on_box_lang_form_content' ), $this->thehook, 'normal', 'high' );

		$themessages[1] = __( 'A language to delete.', 'xili-language' );
		$themessages[2] = __( 'A language was deleted.', 'xili-language' );
		$themessages[3] = __( 'Language to update.', 'xili-language' );
		$themessages[4] = __( 'A language was updated.', 'xili-language' );
		$themessages[5] = __( 'A new language was added.', 'xili-language' );
		$themessages[8] = __( 'Error when updating.', 'xili-language' );
		$themessages[10] = __( 'Error when adding.', 'xili-language' );

		/* form datas in array for do_meta_boxes() */
		$language_features = ( isset( $this->xili_settings['lang_features'][ $language->slug ] ) && '' != $language->slug ) ? $this->xili_settings['lang_features'][ $language->slug ] : array(
			'charset' => '',
			'hidden' => '',
		);

		$data = array(
			'action' => $action,
			'formtitle' => $formtitle,
			'language' => $language,
			'submit_text' => $submit_text,
			'cancel_text' => $cancel_text,
			'language_features' => $language_features,
		);
		?>

		<div id="xili-language-settings" class="wrap columns-2 minwidth" >

			<h2><?php esc_html_e( 'Languages', 'xili-language' ); ?></h2>
			<h3 class="nav-tab-wrapper">
			<?php $this->set_tabs_line(); ?>
			</h3>

			<?php if ( 0 != $msg ) { ?>
			<div id="message" class="updated fade"><p><?php echo $themessages[ $msg ]; ?></p></div>
			<?php } ?>
			<form name="add" id="add" method="post" action="options-general.php?page=language_page">
				<input type="hidden" name="action" value="<?php echo $actiontype; ?>" />
				<?php wp_nonce_field( 'xili-language-settings' ); ?>
				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php
				wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
				$this->setting_form_content( $this->thehook, $data );
				?>
		</form>
		</div>
		<?php
		$this->setting_form_js( $this->thehook );
	}

	/**
	 * for each three forms of settings side-info-column
	 * @since 2.4.1
	 * @updated 2.5, 2.9.12
	 */
	public function setting_form_content( $the_hook, $data ) {

		$poststuff_class = '';
		$postbody_class = 'class="metabox-holder columns-2"';
		$postleft_id = 'id="postbox-container-2"';
		$postright_id = 'postbox-container-1';
		$postleft_class = 'class="postbox-container"';
		$postright_class = 'postbox-container';

		?>
		<div id="poststuff" <?php echo $poststuff_class; ?>>
			<div id="post-body" <?php echo $postbody_class; ?> >

				<div id="<?php echo $postright_id; ?>" class="<?php echo $postright_class; ?>">
					<?php do_meta_boxes( $the_hook, 'side', $data ); ?>
				</div>

				<div id="post-body-content">

					<div <?php echo $postleft_id; ?> <?php echo $postleft_class; ?> style="min-width:360px">
						<?php do_meta_boxes( $the_hook, 'normal', $data ); ?>
					</div>

					<h4><a href="<?php echo $this->repositorylink; ?>" title="xili-language page and docs" target="_blank" style="text-decoration:none" >
							<img style="vertical-align:bottom; margin-right:10px" src="<?php echo plugins_url( 'images/xililang-logo-32.png', XILILANGUAGE_PLUGIN_FILE ); ?>" alt="xili-language logo"/>
						</a>&nbsp;&nbsp;&nbsp;©&nbsp;
						<a href="<?php echo $this->devxililink; ?>" target="_blank" title="<?php esc_attr_e( 'Author' ); ?>" >xiligroup.com</a>™ - msc 2007-2019 - v. <?php echo XILILANGUAGE_VER; ?>
					</h4>

				</div>
			</div>
			<br class="clear" />
		</div>
		<?php
	}

	/**
	 * add js at end of each three forms of settings
	 * @since 2.4.1
	 */
	public function setting_form_js( $the_hook ) {
		?>
	<script type="text/javascript">
	//<![CDATA[
			jQuery(document).ready( function($) {
				// close postboxes that should be closed
				$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				// postboxes setup
				postboxes.add_postbox_toggles('<?php echo $the_hook; ?>');

			<?php if ( $the_hook == $this->thehook4 ) { ?>
				$('#show-manual-box').change(function() {

						$('#manual-menu-box').toggle();

				});
				$('#show-menus-boxes').change(function() {

						$('#old-menus-boxes').toggle();

				});

			<?php } ?>
			<?php if ( $the_hook == $this->thehook ) { ?>
				// for examples list
			$('#language_name_list').change(function() {
				var x = $(this).val();
				$('#language_name').val(x);
				var x = $(this).val();
				x = x.toLowerCase();
				$('#language_nicename').val(x);
				var v = $('#language_name_list option:selected').text();
				v1 = v.substring(0,v.indexOf("/",0));
				v2 = v1.substring(0,v1.indexOf(" (",0));
				if ('' != v2 ) {
					v = v2;
				} else {
					v = v1;
				}
				$('#language_description').val(v);
			});
			<?php } ?>
			});
			//]]>
		</script>
	<?php
	}

	/**
	 * main setting window
	 * the list
	 * @since 1.0
	 * @since 2.22 with title and new object
	 */
	public function on_box_lang_list_content( $data ) {
		?>
			<table class="widefat" style="clear:none;">
				<thead>
					<tr>
						<th scope="col" class="head-id" ><?php esc_html_e( 'ID' ); ?></th>
						<?php
						echo '<th scope="col" title="' . esc_html__( 'Language Iso Name is currently also wp_locale', 'xili-language' ) . '">' . esc_html__( 'ISO Name','xili-language') . '</th>';
						if ( $this->alias_mode ) {
							echo '<th scope="col" title="' . esc_html__( 'Short slug of language used in permalink','xili-language') . '">' . esc_html__('Alias', 'xili-language' ) . '</th>';
						}
						?>
						<th scope="col" title="english name"><?php esc_html_e( 'Full name', 'xili-language' ) ?></th>
						<?php echo '<th scope="col" title="' . esc_html__( 'Name in native language', 'xili-language' ) . '">' . esc_html__( 'Native', 'xili-language' ) . '</th>'; ?>
						<th scope="col"><?php esc_html_e( 'Slug', 'xili-language' ); ?></th>
						<?php
						echo '<th scope="col" title="' . esc_html__( 'Order of languages in switcher', 'xili-language' ) . '">' . esc_html__( 'Order', 'xili-language' ) . '</th>';
						echo '<th scope="col" title="' . esc_html__( 'Visibility in switcher','xili-language' ) . '">' . esc_html__( 'Vis.', 'xili-language' ) . '</th>';
						echo '<th scope="col" title="' . esc_html__( 'Language is available in dashboard side', 'xili-language' ) . '">' . esc_html__( 'Dashb.', 'xili-language') . '</th>';
						?>
						<th scope="col" class="head-count" ><?php esc_html_e( 'Posts' ); ?></th>
						<th scope="col" class="head-action" ><?php esc_html_e( 'Action' ); ?></th>
					</tr>
				</thead>
				<tbody id="the-list">
					<?php
					//$this->available_languages_row(); /* the lines #2260 */
					$this->table_available_language_rows();
					?>
				</tbody>
			</table>
	<?php
	}

	/**
	 * private function - the rows of available language getting from object xili_language_term
	 *
	 * @since 2.22 built with data from xili_language_term
	 *
	 *
	 */
	public function table_available_language_rows() {

		$default = 0;
		/*list of languages*/
		$listlanguages = get_terms_of_groups_lite( $this->langs_group_id, TAXOLANGSGROUP, TAXONAME, 'ASC' );
		if ( empty( $listlanguages ) ) {
			$cleaned = apply_filters( 'clean_previous_languages_list', false ); // 2.20.3
			if ( $cleaned ) {
				$listlanguages = get_terms_of_groups_lite( $this->langs_group_id, TAXOLANGSGROUP, TAXONAME, 'ASC' );
			}
		}
		if ( empty( $listlanguages ) ) {
			$listlanguages = $this->create_default_languages_list( '_add' ); // 2.20.3
			$default = 1;
		}

		if ( ! empty( $default ) || ! empty( $cleaned ) || ! empty( $this->xili_settings['pll_cleaned'] ) ) {
			$click = '';
			$message = '';

			$messages = apply_filters( 'previous_install_list_messages', array(), count( $listlanguages ) );
			$message = $messages['message'];
			$click = $messages['click'];

			if ( $default ) {
				$message = sprintf( __( 'A new list of %s languages by default has just been created !', 'xili-language' ), count( $listlanguages ) );
			}

			if ( $message ) {
				$line = '<tr>'
				. '<th scope="row" class="lang-id" ><img src="' . includes_url( 'images/smilies/icon_exclaim.gif' ) . '" alt="Caution" />&nbsp;&nbsp;' . __( 'CAUTION', 'xili-language' ) . '</th>'
				. '<td class="col-center" colspan="5" ><strong class="red-alert">' . $message . $click . '</strong></td>'
				. '<td class="col-center" colspan="5" >' . __( 'Complete and modify the list according your multilingual need...', 'xili-language' ) . '</td>'
				. '</tr>';
				echo $line;
				$line = '<tr>'
				. '<th scope="row" class="lang-id" ></th>'
				. '<td class="col-center" colspan="10" ><hr/></td>'
				. '</tr>';
				echo $line;
			}
		}
		if ( 1 == count( $listlanguages ) ) {

			$line = '<tr>'
			. '<th scope="row" class="lang-id" ><img src="' . includes_url( 'images/smilies/icon_exclaim.gif' ) . '" alt="Caution" />&nbsp;&nbsp;' . __( 'CAUTION', 'xili-language' ) . '</th>'
			. '<td class="col-center" colspan="5" ><strong class="red-alert">' . __( 'Only one language remains in the list !', 'xili-language' ) . '</strong></td>'
			. '<td class="col-center" colspan="5" >' . __( 'If you don’t need it, add another language required before deletion !', 'xili-language' ) . '</td>'
			. '</tr>';
			echo $line;
			$line = '<tr>'
			. '<th scope="row" class="lang-id" ></th>'
			. '<td class="col-center" colspan="10" ><hr/></td>'
			. '</tr>';
			echo $line;
		}
		$trclass = '';
		foreach ( $listlanguages as $language ) {
			$trclass = ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ' alternate' == $trclass ) ? '' : ' alternate';
			$this->one_admin_language_row( $language, $trclass );
		}
	}

	/**
	 * form to create or edit one language (under the list)
	 */
	public function on_box_lang_form_content( $data ) {
		extract( $data );
		if ( $this->alias_mode ) {
			// used for flush alias refreshing in permalink-class - 2.11
			?>
			<input type="hidden" id="language_settings_action" name="language_settings_action" value="<?php echo $action; ?>" />
		<?php } ?>
		<h2 id="addlang" <?php if ( 'delete' == $action ) { echo 'class="red-alert"'; } ?>><?php echo $formtitle; ?></h2>
		<?php if ( 'edit' == $action || 'delete' == $action ) : ?>
			<input type="hidden" name="language_term_id" value="<?php echo $language->term_id; ?>" />

		<?php endif; ?>
		<?php
		if ( 'delete' == $action ) :
			$disdel = 'disabled="disabled"';
			?>
			<input type="hidden" name="language_nicename" value="<?php echo $language->slug; ?>" />
			<?php
		else :
			$disdel = '';
		endif;
		?>
		<?php wp_nonce_field( 'display-gp-locale-nonce', 'display-gp-locale-nonce', false ); ?>

		<table class="editform" width="100%" cellspacing="2" cellpadding="5">
			<tr>
				<th width="33%" scope="row" valign="top" align="right"><label for="language_name_list"><?php esc_html_e( 'Examples', 'xili-language' ); ?></label>:&nbsp;</th>
				<td width="67%">
					<select name="language_name_list" id="language_name_list">
						<?php $this->example_langs_list( $language->name, $action ); ?>
					</select>&nbsp;
						<small>
						<a href="http://www.gnu.org/software/hello/manual/gettext/Usual-Language-Codes.html#Usual-Language-Codes" target="_blank"><?php esc_html_e( 'ISO Language-Codes', 'xili-language' ); ?></a>
						</small>&nbsp;_&nbsp;
						<small>
							<a href="http://www.gnu.org/software/hello/manual/gettext/Country-Codes.html#Country-Codes" target="_blank"><?php esc_html_e( 'ISO Country-Codes', 'xili-language' ); ?></a>
						</small>
						<br />
						<div id="gplocale-info"></div>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="middle" align="right">
					<label for="language_name"><?php esc_html_e( 'ISO Name', 'xili-language' ); ?></label>:&nbsp;
				</th>
				<td >
					<input name="language_name" id="language_name" type="text" value="<?php echo esc_attr( $language->name ); ?>" size="10" <?php echo $disdel; ?> />  <small>(<?php printf( __( "two, three or five (six) chars like 'ja', 'bal' or 'zh_TW' (haw_US), see %s docs", 'xili-language' ), '<a href="' . $this->glotpresslink . '" target="_blank" >WP Polyglots Page</a>' ); ?>)</small>
				</td>
			</tr>

			<?php
			if ( $this->alias_mode ) {
				// 2.8.2
				if ( '' != $language->slug ) {
					$alias_val = ( $this->lang_slug_qv_trans( $language->slug ) == $language->slug ) ? '' : $this->lang_slug_qv_trans( $language->slug );
					if ( '' == $alias_val ) {
						$alias_val = substr( $language->slug, 0, 2 );
					}
				} else {
					$alias_val = '';
				}
			?>

			<tr>
				<th scope="row" valign="middle" align="right">
					<label for="language_alias"><?php esc_html_e( 'Alias', 'xili-language' ); ?></label>:&nbsp;
				</th>
				<td>
					<input name="language_alias" id="language_alias" size="20" type="text" value="<?php echo esc_attr( $alias_val ); ?>" <?php echo $disdel; ?> />  <small>(<?php esc_html_e( 'as visible in query or permalink on front-end.', 'xili-language' ); ?>,…)</small>
					<?php
					// used for flush alias refreshing in permalink-class - 2.11
					$listlanguages = get_terms_of_groups_lite( $this->langs_group_id, TAXOLANGSGROUP, TAXONAME, 'ASC' );
					foreach ( $listlanguages as $one_language ) {
						$one_alias_val = $this->lang_slug_qv_trans( $one_language->slug );
						if ( $one_language->slug == $language->slug ) {
							echo '<input name="prev_language_alias" id="prev_language_alias" type="hidden" value="' . $one_alias_val . '" />';
						} else {
							echo '<input name="list_language_alias[' . $one_language->slug . ']" id="list_language_alias[' . $one_language->slug . ']" type="hidden" value="' . $one_alias_val . '" />';
						}
					}
					?>
				</td>

			</tr>

			<?php } ?>

			<tr>
				<th scope="row" valign="middle" align="right">
					<label for="language_description"><?php esc_html_e( 'Full name', 'xili-language' ); ?></label>:&nbsp;
				</th>
				<td><input name="language_description" id="language_description" size="20" type="text" value="<?php echo esc_attr( $language->description ); ?>" <?php echo $disdel; ?> />  <small>(<?php esc_html_e( 'as visible in list or menu: english, chinese', 'xili-language' ); ?>,…)</small>
				</td>

			</tr>
			<tr>
				<th scope="row" valign="middle" align="right">
					<label for="language_nicename"><?php esc_html_e( 'Language slug', 'xili-language' ); ?></label>:&nbsp;
				</th>
				<td>
					<input name="language_nicename" id="language_nicename" type="text" value="<?php echo esc_attr( $language->slug ); ?>" size="10" <?php echo $disdel; ?> />
				<?php
				if ( '' != $language->slug ) {
					$cur_locale = \GP_Locales::by_field( 'wp_locale', $language->name );
					if ( $cur_locale ) {
						$native = $cur_locale->native_name;
					} else {
						$cur_locale = \GP_Locales::by_slug( $language->slug );
						$native = ( $cur_locale ) ? $cur_locale->native_name . ' *' : '';
					}
					if ( $native ) {
						printf( '&nbsp;&nbsp;&nbsp;&nbsp;<strong>' . __( 'Native Name: %s', 'xili-language' ) . '</strong>' , $native );
					}
				}
				?>
				</td>
			</tr>

			<tr>
				<th scope="row" valign="middle" align="right">
					<label for="language_order"><?php esc_html_e( 'Order', 'xili-language' ); ?></label>:&nbsp;
				</th>
				<td>
					<input name="language_order" id="language_order" size="3" type="text" value="<?php echo esc_attr( $language->term_order ); ?>" <?php echo $disdel; ?> />&nbsp;&nbsp;&nbsp;<small>
					<label for="language_hidden"><?php esc_html_e( 'hidden', 'xili-language' ); ?>&nbsp;<input name="language_hidden" id="language_hidden" type="checkbox" value="hidden" <?php echo $disdel; ?> <?php checked( $language_features['hidden'], 'hidden', true ); ?> /></label>&nbsp;&nbsp;
					<label for="language_charset"><?php esc_html_e( 'Server Entities Charset:', 'xili-language' ); ?>&nbsp;<input name="language_charset" id="language_charset" type="text" value="<?php echo $language_features['charset']; ?>" size="25" <?php echo $disdel; ?> /></label></small>
				</td>
			</tr>
			<?php if ( 'delete' == $action ) : ?>
			<tr>
				<th scope="row" valign="top" align="right">
					<label for="multilingual_links_erase"><span class="red-alert" ><?php echo $this->admin_messages['alert']['erasing_language']; ?></span></label>&nbsp;:&nbsp;
				</th>
				<td>
					<input name="multilingual_links_erase" id="multilingual_links_erase" type="checkbox" value="erase" />
				</td>

			</tr>
			<?php endif; ?>
			<tr>
			<th><p class="submit"><input type="submit" name="reset" value="<?php echo $cancel_text; ?>" /></p></th>
			<td>
			<p class="submit"><input type="submit" name="submit" value="<?php echo $submit_text; ?>" /></p>
			</td>
			</tr>
		</table>
	<?php
	}

	/**
	 * add styles in options
	 *
	 * @since 2.6
	 *
	 */
	public function print_styles_options_language_page() {
		// first tab

		echo "<!---- xl options css 1 ----->\n";
		echo '<style type="text/css" media="screen">' . "\n";
		echo ".red-alert {color:red;}\n";
		echo ".minwidth {min-width:1000px !important ;}\n";
		echo "th.head-id { color:red ; width:60px; }\n";
		echo "th.head-count { text-align: center !important; width: 60px; }\n";
		echo "th.head-action { text-align: center !important; width: 140px; }\n";
		echo ".col-center { text-align: center; }\n";
		echo "th.lang-id { font-size:70% !important; }\n";
		echo "span.lang-flag { display:inline-block; height: 18px; }\n";
		echo ".box { margin:2px; padding:12px 6px; border:1px solid #ccc; } \n";
		echo ".themeinfo {margin:2px 2px 5px; padding:12px 6px; border:1px solid #ccc;} \n";

		//if ( $this->exists_style_ext && ( $this->style_folder == $this->plugin_url) ) {
		if ( get_stylesheet_directory_uri() == $this->style_folder ) {
			$folder_url = $this->style_folder . '/images/flags/';
		} else {
			$folder_url = $this->style_folder . '/xili-css/flags/';
		}
		$listlanguages = $this->get_listlanguages( true ); // 2.22
		foreach ( $listlanguages as $language ) {
			$ok = false;
			$flag_id = $this->get_flag_series( $language->slug, 'admin' );
			if ( 0 != $flag_id ) {
				$flag_uri = wp_get_attachment_url( $flag_id );
				$ok = true;
			} else {
				//$flag_uri = $folder_url . $language->slug .'.png';
				$ok = file_exists( $this->style_flag_folder_path . $language->slug . '.png' );
				$flag_uri = $folder_url . $language->slug . '.png';
			}

			if ( $ok && 'on' == $this->xili_settings['external_xl_style'] ) {
				echo 'tr.lang-' . $language->slug . ' th { background: transparent url(' . $flag_uri . ') no-repeat 60% center; }' . "\n";
			}
		}
		//}
		echo "</style>\n";
		if ( $this->exists_style_ext && 'on' == $this->xili_settings['external_xl_style'] ) {
			wp_enqueue_style( 'xili_language_stylesheet' );
		}
	}

}
