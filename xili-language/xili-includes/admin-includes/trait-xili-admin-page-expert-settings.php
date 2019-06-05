<?php
namespace Xili_Admin;

/**
 * @package Xili-Language
 * @subpackage admin
 * functions for expert settings page
 * @since  2.23 traits files
 */

trait Xili_Admin_Page_Expert_Settings {

	
	/**
	 * Settings by experts and info
	 * @since 2.4.1
	 */
	public function on_load_page_expert() {
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'wp-lists' );
			wp_enqueue_script( 'postbox' );

			add_meta_box( 'xili-language-sidebox-theme', __( 'Current theme infos', 'xili-language' ), array( &$this, 'on_sidebox_4_theme_info' ), $this->thehook4, 'side', 'high' );

			add_meta_box( 'xili-language-sidebox-info', __( 'Info', 'xili-language' ), array( &$this, 'on_sidebox_info_content' ), $this->thehook4, 'side', 'core' );

			$this->insert_news_pointer( 'languages_expert' ); // news pointer 2.6.2
			$this->insert_news_pointer( 'languages_expert_special' );
	}

	/**
	 * Support page
	 *
	 * @since 2.4.1
	 */
	public function languages_expert() {

		$msg = 0;
		$themessages = array( 'ok' );
		$action = '';

		$optionmessage = '';

		if ( isset( $_POST['reset'] ) ) {
			$action = $_POST['reset'];
		} elseif ( isset( $_POST['menuadditems'] ) ) {
			$action = 'menuadditems';
		} elseif ( isset( $_POST['updatespecials'] ) ) {
			$action = 'updatespecials';
		} elseif ( isset( $_POST['innavenable'] ) || isset( $_POST['pagnavenable'] ) ) {
			$action = 'menunavoptions';
		} elseif ( isset( $_POST['jetpack_fc_enable'] ) ) {
			$action = 'jetpack_fc_enable';
		} elseif ( isset( $_POST['xl-bbp-addon-integrate'] ) ) { // 2.18
			$action = 'xl-bbp-addon-integrate';
		}

		switch ( $action ) {
			case 'menuadditems':
				check_admin_referer( 'xili-language-expert' );
				$this->xili_settings['navmenu_check_option2'] = $_POST['xili_navmenu_check_option2']; // 1.8.1

				$result = $this->add_list_of_language_links_in_wp_menu( $this->xili_settings['navmenu_check_option2'] );

				$msg = 1;

				break;

			case 'menunavoptions':
				check_admin_referer( 'xili-language-expert' );
				if ( current_theme_supports( 'menus' ) ) {
					$menu_locations = get_nav_menu_locations();
					$selected_menu_locations = array();
					if ( $menu_locations ) {
						$pagenablelist = '';
						foreach ( $menu_locations as $menu_location => $location_id ) {
							if ( isset( $_POST[ 'xili_navmenu_check_option_' . $menu_location ] ) && 'enable' == $_POST[ 'xili_navmenu_check_option_' . $menu_location ] ) {
								$selected_menu_locations[ $menu_location ]['navenable'] = 'enable';
								$selected_menu_locations[ $menu_location ]['navtype'] = $_POST[ 'xili_navmenu_check_optiontype_' . $menu_location ]; //0.9.1
							}
							// page list in array 2.8.4.3
							$enable = ( isset( $_POST[ 'xili_navmenu_check_option_page_' . $menu_location ] ) && 'enable' == $_POST[ 'xili_navmenu_check_option_page_' . $menu_location ] ) ? 'enable' : '';
							$pagenablelist .= $enable;
							$args = $_POST[ 'xili_navmenu_page_args_' . $menu_location ];
							$thenewvalue = array(
								'enable' => $enable,
								'args' => $args,
							);
							$this->xili_settings['array_navmenu_check_option_page'][ $menu_location ] = $thenewvalue;
						}

						$this->xili_settings['page_in_nav_menu_array'] = $pagenablelist;

					} else {
						$optionmessage = '<strong>' . __( 'Locations menu not set: go to menus settings', 'xili-language' ) . '</strong>';
					}
					$this->xili_settings['navmenu_check_options'] = $selected_menu_locations; // 2.1.0

					$this->xili_settings['in_nav_menu'] = ( isset( $_POST['list_in_nav_enable'] ) ) ? $_POST['list_in_nav_enable'] : ''; // 1.6.0
					//$this->xili_settings['page_in_nav_menu'] = ( isset($_POST['page_in_nav_enable'] ) ) ? $_POST['page_in_nav_enable'] : ""; // 1.7.1
					//$this->xili_settings['args_page_in_nav_menu'] = ( isset($_POST['args_page_in_nav'] ) ) ? $_POST['args_page_in_nav'] : ""; // 1.7.1

					$this->xili_settings['nav_menu_separator'] = stripslashes( $_POST['nav_menu_separator'] );

					$this->xili_settings['navmenu_check_option'] = ( isset( $_POST['xili_navmenu_check_option'] ) ) ? $_POST['xili_navmenu_check_option'] : '';
					$this->xili_settings['list_pages_check_option'] = ( isset( $_POST['xili_list_pages_check_option'] ) ) ? $_POST['xili_list_pages_check_option'] : ''; // 2.8.4.4

					// new method if more than one nav-menu 2.8.4.3

					$this->xili_settings['home_item_nav_menu'] = ( isset( $_POST['xili_home_item_nav_menu'] ) ) ? $_POST['xili_home_item_nav_menu'] : ''; // 1.8.9.2
					// 1.8.1
				}
				/* UPDATE OPTIONS */
				update_option( 'xili_language_settings', $this->xili_settings );
				/* translators: */
				$optionmessage .= ' - ' . sprintf( __( 'Options are updated: Automatic Nav Menu = %s, Selection of pages in Nav Menu = %s', 'xili-language' ), $this->xili_settings['in_nav_menu'], $this->xili_settings['page_in_nav_menu'] );

				$msg = 1;
				break;

			case 'updatespecials':
				check_admin_referer( 'xili-language-expert' );
				$special_msg = array();

				$this->xili_settings['multiple_lang'] = ( isset( $_POST['multiple_lang'] ) ) ? $_POST['multiple_lang'] : '';

				// here (and not theme options) 2.20
				$lang_permalink = ( isset( $_POST['lang_permalink'] ) ) ? $_POST['lang_permalink'] : 'perma_not';
				if ( $lang_permalink != $this->xili_settings['lang_permalink'] ) {
					$this->xili_settings['lang_permalink'] = $lang_permalink;
					/* translators: */
					$special_msg[] = sprintf( __( 'Language begins permalink: %s ', 'xili-language' ), $this->xili_settings['lang_permalink'] );
					// 2.20.3
					if ( 'perma_not' != $this->xili_settings['lang_permalink'] ) {
						/*
						$result = apply_filters ('xl_import_previous_aliases', false );
						if ( $result ) {
							$special_msg[] = ' ' . __('Alias imported from Polylang.', 'xili-language');
						}
						*/
						$dummy = 1;
					}
				}

				/* force rules flush - 2.1.1 */
				if ( isset( $_POST['force_permalinks_flush'] ) && 'enable' == $_POST['force_permalinks_flush'] ) {
					$this->get_lang_slug_ids( 'edited' ); // if list need refresh - 2013-11-24
					$special_msg[] = __( 'permalinks flushed', 'xili-language' );
				}
				/* domains switching settings 1.8.7 */
				$temp_domains_settings = $this->xili_settings['domains'];
				foreach ( $this->xili_settings['domains'] as $domain => $state ) {
					if ( isset( $_POST[ 'xili_language_domains_' . $domain ] ) ) {
						$this->xili_settings['domains'][ $domain ] = $_POST[ 'xili_language_domains_' . $domain ];
						if ( 'default' != $domain ) {
							$this->xili_settings['domain_paths'][ $domain ] = $_POST[ 'xili_language_domain_path_' . $domain ];
							$this->xili_settings['plugin_paths'][ $domain ] = $_POST[ 'xili_language_plugin_path_' . $domain ]; // hidden input
						}
					} else {
						unset( $this->xili_settings['domains'][ $domain ] ); // for unactivated plugin
					}
				}
				if ( $temp_domains_settings != $this->xili_settings['domains'] ) {
					$special_msg[] = ' ' . __( 'Domains switching settings changed', 'xili-language' );
				}
				$temp_wp_locale = ( isset( $_POST['xili_language_wp_locale'] ) ) ? $_POST['xili_language_wp_locale'] : 'db_locale';
				if ( $temp_wp_locale != $this->xili_settings['wp_locale'] ) {
					$this->xili_settings['wp_locale'] = $temp_wp_locale;
					/* translators: */
					$special_msg[] = sprintf( __( 'Locale changed: %s', 'xili-language' ), $this->xili_settings['wp_locale'] );
				}

				/* UPDATE OPTIONS */
				update_option( 'xili_language_settings', $this->xili_settings );
				/* messages */
				if ( $special_msg ) {
					/* translators: */
					$optionmessage .= ' - ' . sprintf( __( 'Options are updated ( %s )', 'xili-language' ), implode( ' & ', $special_msg ) );
				} else {
					$optionmessage .= ' - ' . __( 'no change', 'xili-language' );
				}

				$msg = 1;
				break;

			case 'jetpack_fc_enable':
				check_admin_referer( 'xili-language-expert' );
				if ( isset( $_POST['enable_fc_theme_class'] ) && 'enable' == $_POST['enable_fc_theme_class'] ) {
					$this->xili_settings['enable_fc_theme_class'] = 'enable';
				} else {
					$this->xili_settings['enable_fc_theme_class'] = 'disable';
				}
				update_option( 'xili_language_settings', $this->xili_settings );
				/* translators: */
				$optionmessage = sprintf( __( 'Settings for JetPack are updated to ‘%s’.', 'xili-language' ), $this->xili_settings['enable_fc_theme_class'] );
				$msg = 1; // green
				break;

			case 'xl-bbp-addon-integrate':
				check_admin_referer( 'xili-language-expert' );
				update_option( 'xl-bbp-addon-activated-folder', $_POST['xl-bbp-addon'] );
				/* translators: */
				$optionmessage = sprintf( __( 'Settings for bbPress are updated to %1$s. Now %2$s', 'xili-language' ), ( '' != $_POST['xl-bbp-addon'] ) ? $_POST['xl-bbp-addon'] : __( 'no integration', 'xili-language' ), '<a href="" >' . __( 'click to refresh dashboard...', 'xili-language' ) . '</a>' );
				$msg = 1; // green
				break;

			default:
				# do action via filters  set in importer functions - 2.20.3
				if ( isset( $_POST ) ) {
					$optionmessage = apply_filters( 'import_list_of_actions', '', $_POST );
					if ( $optionmessage ) {
						$msg = 1; // green
					}
				}
				break;
		}

		$box_expert_title = ( has_filter( 'clean_previous_languages_list' ) ) ?
			__( 'Special settings and actions', 'xili-language' ) : __( 'Special settings (JetPack,...)', 'xili-language' ); //2.20.3

		add_meta_box( 'xili-language-box-3', __( 'Navigation menus', 'xili-language' ), array( &$this, 'on_box_expert' ), $this->thehook4, 'normal', 'high' );
		add_meta_box( 'xili-language-sidebox-special', __( 'Special', 'xili-language' ), array( &$this, 'on_sidebox_for_specials' ), $this->thehook4, 'normal', 'high' );
		add_meta_box( 'xili-language-box-3-2', $box_expert_title, array( &$this, 'on_box_plugins_expert' ), $this->thehook4, 'normal', 'high' );
		$themessages[1] = $optionmessage;
		$data = array(
			'action' => $action,
			'list_in_nav_enable' => $this->xili_settings['in_nav_menu'],
		);
		?>
		<div id="xili-language-support" class="wrap columns-2 minwidth">

			<h2><?php esc_html_e( 'Languages', 'xili-language' ); ?></h2>
			<h3 class="nav-tab-wrapper">
			<?php $this->set_tabs_line(); ?>
			</h3>

			<?php if ( 0 != $msg ) { ?>
			<div id="message" class="updated fade"><p><?php echo $themessages[ $msg ]; ?></p></div>
			<?php } ?>
			<form name="expert" id="expert" method="post" action="options-general.php?page=language_expert">
				<?php wp_nonce_field( 'xili-language-expert' ); ?>
				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php
				wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
				$this->setting_form_content( $this->thehook4, $data );
				?>
			</form>
		</div>
		<?php
		$this->setting_form_js( $this->thehook4 );

	}

	/**
	 * Actions box
	 * menu
	 * gold options
	 */
	public function on_box_expert( $data ) {
		extract( $data );
		$template_directory = $this->get_template_directory;
		if ( is_child_theme() ) { // 1.8.1 and WP 3.0
			$theme_name = get_option( 'stylesheet' ) . ' ' . __( 'child of', 'xili-language' ) . ' ' . get_option( 'template' );
		} else {
			$theme_name = get_option( 'template' );
		}

		if ( current_theme_supports( 'menus' ) ) {
			?>

		<p><em><?php esc_html_e( 'These options are still present for compatibility reasons with previous versions < 2.9.22. <strong>For new installation, it is preferable to use the insertion points and menu options.</strong>', 'xili-language' ); ?></em><br /><?php printf( __( 'Goto <a href="%s" title="Menu Items definition">Appearance Menus</a> settings.', 'xili-language' ), 'nav-menus.php' ) ;?></p>

		<label for="show-menus-boxes" class="selectit"><input name="show-menus-boxes" id="show-menus-boxes" type="checkbox" value="show">&nbsp;<?php esc_html_e( 'Show previous menus settings (reserved for backwards compatibility)', 'xili-language' ); ?></label>

		<div id="old-menus-boxes" class="hiddenbox">
		<fieldset class="box"><legend><?php esc_html_e( 'Nav menu: Home links in each language', 'xili-language' ); ?></legend>
			<?php
				$menu_locations = get_registered_nav_menus(); // get_nav_menu_locations() keeps data in cache; // only if linked to a content - get_registered_nav_menus() ; // 2.8.8 with has_nav_menu()

				$selected_menu_locations = ( isset( $this->xili_settings['navmenu_check_options'] ) ) ? $this->xili_settings['navmenu_check_options'] : array();
			if ( is_array( $menu_locations ) && array() != $menu_locations ) { // 2.8.6 - wp 3.6
			?>
			<fieldset class="box leftbox">
				<?php esc_html_e( 'Choose location(s) of nav menu(s) where languages list will be automatically inserted. For each location, choose the type of list. Experts can create their own list by using api (hook) available in plugin.', 'xili-language' ); ?>
				<br><strong><?php /* translators: */ printf( __( 'Since version 2.8.8, it is possible to insert the languages list anywhere in the navigation menu via the <a href="%s" title="Menu Items definition">Appearance Menus Builder</a> (drag and drop method).', 'xili-language' ), 'nav-menus.php' ); ?></strong>
				<br><em><?php /* translators: */ printf( __( 'To avoid unwanted double items in navigation menu, choose one of the 2 methods but not both ! The future version will use only menus set in <a href="%s" title="Menu Items definition">Appearance Menus Builder</a>.', 'xili-language' ), 'nav-menus.php' ); ?></em>

			</fieldset>
			<fieldset class="box rightbox">
			<?php
			if ( $this->this_has_external_filter( 'xl_language_list' ) ) {
				// is list of options described
				$this->langs_list_options = array();
				do_action( 'xili_language_list_options', $theoption ); // update the list of external action
			}
			echo '<table style="width:98%;"><tbody>';
			foreach ( $menu_locations as $menu_location => $location_id ) {

				$locations_enable = ( isset( $selected_menu_locations[ $menu_location ] ) ) ? $selected_menu_locations[ $menu_location ]['navenable'] : '';

				if ( 'enable' == $locations_enable || ( ! isset( $this->xili_settings['navmenu_check_options'] ) && isset( $this->xili_settings['navmenu_check_option'] ) && $this->xili_settings['navmenu_check_option'] == $menu_location ) ) {
					$checked = 'checked="checked"'; // ascendant compatibility ( !isset($this->xili_settings['navmenu_check_options']) &&
				} else {
					$checked = '';
				}
				?>
				<tr><th style="text-align:left;"><label for="xili_navmenu_check_option_<?php echo $menu_location; ?>" class="selectit"><input id="xili_navmenu_check_option_<?php echo $menu_location; ?>" name="xili_navmenu_check_option_<?php echo $menu_location; ?>" type="checkbox" value="enable" <?php echo $checked; ?> /> <?php echo $menu_location; ?></label>&nbsp;<?php echo ( has_nav_menu( $menu_location ) ) ? '' : '<abbr title="menu location without content" class="red-alert"> (?) </abbr>'; ?>
				</th><td><label for="xili_navmenu_check_optiontype_<?php echo $menu_location; ?>"><?php esc_html_e( 'Type', 'xili-language' ); ?>:
				<select style="width:80%;" name="xili_navmenu_check_optiontype_<?php echo $menu_location; ?>" id="xili_navmenu_check_optiontype_<?php echo $menu_location; ?>">
				<?php
				if ( array() == $this->langs_list_options ) {
					echo '<option value="" >default</option>';
				} else {
					$subtitle = '';
					foreach ( $this->langs_list_options as $typeoption ) {
						if ( false !== strpos( $typeoption[0], 'navmenu' ) ) {
							$seltypeoption = ( isset( $this->xili_settings['navmenu_check_options'][ $menu_location ]['navtype'] ) ) ? $this->xili_settings['navmenu_check_options'][ $menu_location ]['navtype'] : '';
							if ( $seltypeoption == $typeoption[0] ) {
								$subtitle = $typeoption[2]; // 2.8.6
							}
							echo '<option title="' . $typeoption[2] . '" value="' . $typeoption[0] . '" ' . selected( $seltypeoption, $typeoption[0], false ) . ' >' . $typeoption[1] . '</option>';
						}
					}
				}
				?>
				</select></label>
				<?php
				$point = $this->has_insertion_point_list_menu( $menu_location, $this->insertion_point_dummy_link );
				if ( 0 != $point ) {
					echo '<br />&nbsp; <span class="red-alert">' . __( 'This menu location contains a language list insertion point !', 'xili-language' ) . '</span>'; // && $checked != ''
				}
				if ( '' != $subtitle ) {
					echo '<br /><span id="title_xili_navmenu_check_optiontype_' . $menu_location . '" ><em>' . $subtitle . '</em></span>';
				}
				?>
				</td></tr>
				<?php
			}
			echo '</tbody></table>';
			?>
			<hr />	<br />
			<label for="nav_menu_separator" class="selectit"><?php esc_html_e( 'Separator before language list (<em>Character or Entity Number or Entity Name</em>)', 'xili-language' ); ?> : <input id="nav_menu_separator" name="nav_menu_separator" type="text" value="<?php echo htmlentities( stripslashes( $this->xili_settings['nav_menu_separator'] ) ); ?>" /> </label><br /><br />
			<label for="list_in_nav_enable" class="selectit"><input id="list_in_nav_enable" name="list_in_nav_enable" type="checkbox" value="enable" <?php checked( $list_in_nav_enable, 'enable', true ); ?> /> <?php esc_html_e( 'Add language list at end of nav menus checked above', 'xili-language' ); ?></label><br />

			</fieldset>
			<br />
			<fieldset class="box leftbox">
					<?php esc_html_e( 'Home menu item will be translated when changing language:', 'xili-language' ); ?>
				</fieldset>
				<fieldset class="box rightbox">
					<label for="xili_home_item_nav_menu" class="selectit"><input id="xili_home_item_nav_menu" name="xili_home_item_nav_menu" type="checkbox" value="modify" <?php checked( $this->xili_settings['home_item_nav_menu'], 'modify', true ); ?> /> <?php esc_html_e( 'Menu Home item with language.', 'xili-language' ); ?></label>
				</fieldset>
				<?php if ( $this->show_page_on_front ) { ?>
					<br />
					<fieldset class="box leftbox">
						<?php esc_html_e( 'Keep original link of frontpage array in menu pages list:', 'xili-language' ); ?>
					</fieldset>
					<fieldset class="box rightbox">
						<label for="xili_list_pages_check_option" class="selectit"><input id="xili_list_pages_check_option" name="xili_list_pages_check_option" type="checkbox" value="fixe" <?php checked( $this->xili_settings['list_pages_check_option'], 'fixe', true ); ?> /> <?php esc_html_e( 'One home per language.', 'xili-language' ); ?></label>
					</fieldset>
				<?php } ?>
				<br />
				<div class="submit"><input id='innavenable' name='innavenable' type='submit' value="<?php esc_html_e( 'Update', 'xili-language' ); ?>" /></div>
				<br />

				</fieldset>
				<br />

				<fieldset class="box"><legend><?php esc_html_e( 'Nav menu: Automatic sub-selection of pages according current language', 'xili-language' ); ?></legend>
					<fieldset class="box leftbox">
					<?php esc_html_e( 'Choose location of nav menu where sub-selection of pages list will be automatically inserted according current displayed language:', 'xili-language' ); ?><br /><?php esc_html_e( 'Args is like in function wp_list_pages, example: <em>include=11,15</em><br />Note: If args kept empty, the selection will done on all pages (avoid it).', 'xili-language' ); ?>
				<br><strong>
					<?php
					/* translators: */
					printf( __( "Since version 2.9.10, it is possible to insert a list of pages sub-selected according current language anywhere in the navigation menu via the <a href=\"%s\" title=\"Menu Items definition\">Appearance Menus Builder</a> (drag and drop method).", 'xili-language' ), 'nav-menus.php' );
					?></strong>
				<br><em>
					<?php
					/* translators: */
					printf( __( 'To avoid unwanted double items in navigation menu, choose one of the 2 methods but not both ! The future version will use only menus set in <a href=\"%s\" title=\"Menu Items definition\">Appearance Menus Builder</a>.', 'xili-language' ), 'nav-menus.php' );
					?></em>
				</fieldset>
					<fieldset class="box rightbox">
						<?php

						$selected_page_menu_locations = ( isset( $this->xili_settings['array_navmenu_check_option_page'] ) ) ? $this->xili_settings['array_navmenu_check_option_page'] : array();
						if ( is_array( $menu_locations ) ) {
							echo '<table><tbody>';
							foreach ( $menu_locations as $menu_location => $location_id ) {
								$args = ( isset( $selected_page_menu_locations[ $menu_location ]['args'] ) ) ? $selected_page_menu_locations[ $menu_location ]['args'] : '';
								?>
								<tr>
									<th style="text-align:left;">
										<label for="xili_navmenu_check_option_page_<?php echo $menu_location; ?>" class="selectit"><input id="xili_navmenu_check_option_page_<?php echo $menu_location; ?>" name="xili_navmenu_check_option_page_<?php echo $menu_location; ?>" type="checkbox" value="enable" <?php echo checked( ( isset( $selected_page_menu_locations[ $menu_location ]['enable'] ) ) ? $selected_page_menu_locations[ $menu_location ]['enable'] : '', 'enable' ); ?> /> <?php echo $menu_location; ?></label>&nbsp;&nbsp;<?php echo ( has_nav_menu( $menu_location ) ) ? '' : '<abbr title="menu location without content" class="red-alert"> (?) </abbr>'; ?>
									</th>
									<td>
										<label for="xili_navmenu_page_args_<?php echo $menu_location; ?>"><?php esc_html_e( 'Args', 'xili-language' ); ?>:
											<input id="xili_navmenu_page_args_<?php echo $menu_location; ?>" name="xili_navmenu_page_args_<?php echo $menu_location; ?>" type="text" value="<?php echo $args; ?>" />
										</label>
									<?php
									$point = $this->has_insertion_point_list_menu( $menu_location, $this->insertion_point_dummy_link_page );
									if ( 0 != $point ) {
										echo '<br />&nbsp; <span class="red-alert">' . __( 'This menu location contains a page sub-list insertion point !', 'xili-language' ) . '</span>';
									}
									?>
									</td>
								</tr>
						<?php
							}
							echo '</tbody></table>';
						}
						?>

				</fieldset>
				<br />
				<div class="submit"><input id='pagnavenable' name='pagnavenable' type='submit' value="<?php esc_html_e( 'Update', 'xili-language' ); ?>" /></div>
				<?php
				} else {
					esc_html_e( "This theme doesn't contain active Nav Menu. List of languages cannot be automatically added.", 'xili-language' );
					echo '<br />';
					/* translators: */
					printf( __( 'See <a href="%s" title="Menu Items definition">Appearance Menus activation</a> settings.', 'xili-language' ), 'nav-menus.php' );
				}
				?>

			</fieldset>
			<br />

			<label for="show-manual-box" class="selectit"><input name="show-manual-box" id="show-manual-box" type="checkbox" value="show">&nbsp;<?php esc_html_e( 'Show toolbox for manual insertion (reserved purposes)', 'xili-language' ); ?></label>
			<fieldset id="manual-menu-box" class="box hiddenbox">
				<legend><?php esc_html_e( "Theme's nav menu items settings", 'xili-language' ); ?></legend>
				<p>
					<?php
					if ( $menu_locations ) {
						$loc_count = count( $menu_locations );
								?>
								<fieldset class="box leftbox">
									<?php
									/* translators: */
									printf( __( 'This theme (%1$s) contains %2$d Nav Menu(s).', 'xili-language' ), $theme_name, $loc_count );
									?>
									<p><?php esc_html_e( 'Choose nav menu where languages list will be manually inserted:', 'xili-language' ); ?></p>
								</fieldset>
								<fieldset class="box rightbox">
									<select name="xili_navmenu_check_option2" id="xili_navmenu_check_option2" class="fullwidth">
						<?php
						foreach ( $menu_locations as $menu_location => $location_id ) {
							if ( isset( $this->xili_settings['navmenu_check_option2'] ) && $this->xili_settings['navmenu_check_option2'] == $menu_location ) {
								$checked = 'selected = "selected"';
							} else {
								$checked = '';
							}
							echo '<option value="' . $menu_location . '" ' . $checked . ' >' . $menu_location . '</option>';
						}
						?>
									</select>
									<br />	<br />
			<?php
			echo '<br />';
			/* translators: */
			printf( __( 'See <a href="%s" title="Menu Items definition">Appearance Menus</a> settings.', 'xili-language' ), 'nav-menus.php' );
			if ( 'enable' == $list_in_nav_enable ) {
				echo '<br /><span class="red-alert">' . $this->admin_messages['alert']['menu_auto_inserted'] . '</span>';
			}

			?>
			</p>
			</fieldset>
			<br /><?php esc_html_e( 'Do you want to add list of language links at the end ?', 'xili-language' ); ?><br />
			<div class="submit"><input id='menuadditems' name='menuadditems' type='submit' value="<?php esc_html_e( 'Add menu items', 'xili-language' ); ?>" /></div>

					<?php
					} else {
						esc_html_e( "This theme doesn't contain active Nav Menu.", 'xili-language' );
						echo '<br />';
						/* translators: */
						printf( esc_html__( 'See <a href="%s" title="Menu Items definition">Appearance Menus</a> settings.', 'xili-language' ), 'nav-menus.php' );
					}
					?>
		</fieldset>
		</div>

		<?php
		}
		if ( '' != $this->xili_settings['functions_enable'] && function_exists( 'xiliml_setlang_of_undefined_posts' ) ) {
			?>
			<p><?php esc_html_e( 'Special Gold Actions', 'xili-language' ); ?></p>
			<?php
			xiliml_special_UI_undefined_posts( $this->langs_group_id );
		}
	}

	/**
	 *
	 * @since 2.11.1 // admin.php?page=jetpack_modules
	 *
	 */
	public function on_box_plugins_expert( $data ) {
		do_action( 'import_list_forms_action' ); // 2.20.3

		$checked = checked( $this->xili_settings['enable_fc_theme_class'], 'enable', false );
		?>
		<p><?php esc_html_e( 'Define some behaviours with plugins like JetPack.', 'xili-language' ); ?></p>
		<fieldset id="jetpack-box" class="box"><legend><?php printf( '<a href="admin.php?page=jetpack_modules">%s</a>', esc_html__( 'JetPack settings', 'xili-language' ) ); ?></legend>
			<?php
			if ( class_exists( 'jetpack' ) ) {
				?>
			<label for="enable_fc_theme_class" class="selectit">
				<input <?php echo $checked; ?> name="enable_fc_theme_class" id="enable_fc_theme_class" type="checkbox" value="enable">
				&nbsp;<?php esc_html_e( 'Give priority to class - Featured_Content - of current theme.', 'xili-language' ); ?>
			</label>
			<div class="submit"><input id='jetpack_fc_enable' name='jetpack_fc_enable' type='submit' value="<?php esc_html_e( 'Update', 'xili-language' ); ?>" /></div>
			<?php
			} else {
				echo '<p>' . esc_html__( 'JetPack plugin is not active.', 'xili-language' ) . '</p>';
			}
			?>
		</fieldset>
		<?php
		$subfolder = get_option( 'xl-bbp-addon-activated-folder', '/' );

		?>
		<p><?php esc_html_e( 'Choose compatibility/integration with bbPress.', 'xili-language' ); ?></p>
		<fieldset id="bbPress-box" class="box"><legend><?php esc_html_e( 'bbPress settings', 'xili-language' ); ?></legend>
			<?php
			if ( class_exists( 'bbPress' ) ) {
				?>
			<label for="xl-bbp-addon" class="selectit">
				<select name="xl-bbp-addon" id="xl-bbp-addon" >
					<?php
					echo '<option value="" ' . selected( $subfolder, '', false ) . ' >' . esc_html__( 'no integration', 'xili-language' ) . '</option>';
					echo '<option value="/" ' . selected( $subfolder, '/', false ) . ' >' . esc_html__( 'default integration', 'xili-language' ) . '</option>';
					echo '<option value="/xili-includes/" ' . selected( $subfolder, '/xili-includes/', false ) . ' >' . esc_html__( 'custom integration (future)', 'xili-language' ) . '</option>';
					?>
				</select>
				&nbsp;<?php esc_html_e( 'Define the folder of file for bbPress integration.', 'xili-language' ); ?>
			</label>
			<div class="submit"><input id='xl-bbp-addon-integrate' name='xl-bbp-addon-integrate' type='submit' value="<?php esc_html_e( 'Update', 'xili-language' ); ?>" /></div>
		<?php
			} else {
				echo '<p>' . esc_html__( 'bbPress plugin is not active.', 'xili-language' ) . '</p>';
			}
		?>
		</fieldset>
		<?php
	}


}
