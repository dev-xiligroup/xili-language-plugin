<?php
namespace Xili_Admin;

/**
 * @package Xili-Language
 * @subpackage admin
 * functions for commun sideboxes
 * @since  2.23 traits files
 */

trait Xili_Admin_Page_Sideboxes {

	/***************** Side settings metaboxes *************/

	/**
	 * info box
	 */
	public function on_sidebox_info_content() {
		?>

		<p><?php esc_html_e( 'This plugin was developed with the taxonomies, terms tables and WP specifications.', 'xili-language' ); ?><br /><?php esc_html_e( 'xili-language create a new taxonomy used for language of posts and pages and custom post types. For settings (basic or expert), 5 tabs were available.', 'xili-language' ); ?><br /><br /><?php esc_html_e( 'To attach a language to a post, a box gathering infos in available in new and edit post admin side pages. Also, selectors are in Quick Edit bulk mode of Posts list.', 'xili-language' ); ?></p>
		<?php
	}

	/**
	 * Special box
	 *
	 * @since 2.4.1
	 *
	 */
	public function on_sidebox_for_specials( $data ) {

		if ( get_option( 'permalink_structure' ) ) {
			// back compat
			if ( 'perma_ok' == $this->xili_settings['lang_permalink'] ) {
				$lang_perma_state = 'perma_ok';
			} else {
				$lang_perma_state = '';
			}

			?>

			<fieldset class="box"><legend><?php esc_html_e( 'Permalinks rules', 'xili-language' ); ?></legend>

			<label for="lang_permalink" class="selectit"><input id="lang_permalink" name="lang_permalink" type="checkbox" <?php checked( $lang_perma_state, 'perma_ok' ); ?> value="perma_ok" />
			&nbsp;<?php esc_html_e( 'Permalinks for languages', 'xili-language' ); ?></label>

			<p><small><em><?php esc_html_e( 'If checked, xili-language incorporates language (or alias) at the begining of permalinks... (premium services for donators, see docs)', 'xili-language' ); ?></em><br/>
			<?php
			/* translators: */
			printf( esc_html__( 'URI will be like %s, xx is slug/alias of current language.', 'xili-language' ), '<em>' . get_option( 'home' ) . '<strong>/xx</strong>' . get_option( 'permalink_structure' ) . '</em>' );
			?>
			</small><p>
			<?php // to force permalinks flush  ?>
			<label for="force_permalinks_flush" class="selectit"><input id="force_permalinks_flush" name="force_permalinks_flush" type="checkbox" value="enable" /> <?php esc_html_e( 'force permalinks flush', 'xili-language' ); ?></label>
			</fieldset>
			<div class='submit'>
				<input id='updatespecials' name='updatespecials' type='submit' tabindex='6' value="<?php esc_html_e( 'Update', 'xili-language' ); ?>" />
			</div>
			<div class='clearb1'>&nbsp;</div>

		<?php
		} // end permalink

		if ( 'multiple_lang' == $this->xili_settings['multiple_lang'] ) {
			$multiple_lang = 'multiple_lang';
		} else {
			$multiple_lang = '';
		}
		?>

		<fieldset class="box"><legend><?php esc_html_e( 'Multiple languages in post (new feature)', 'xili-language' ); ?></legend>
			<label for="multiple_lang" class="selectit"><input id="multiple_lang" name="multiple_lang" type="checkbox" <?php checked( $multiple_lang, 'multiple_lang' ); ?> value="multiple_lang" />
			&nbsp;<?php esc_html_e( 'Enable multiple languages in post', 'xili-language' ); ?></label>

			<p><small><em><?php esc_html_e( 'If checked, xili-language allows to define multiple languages inside a post.', 'xili-language' ); ?></em></small></p>
			</fieldset>
			<div class='submit'>
				<input id='updatespecials' name='updatespecials' type='submit' tabindex='6' value="<?php esc_html_e( 'Update', 'xili-language' ); ?>" />
			</div>
			<div class='clearb1'>&nbsp;</div>


		<fieldset class="box"><legend><?php esc_html_e( 'Translation domains settings', 'xili-language' ); ?></legend><p>
			<?php esc_html_e( 'For experts in multilingual CMS: Choose the rule to modify domains switching.', 'xili-language' ); ?><br />
			<em><?php /* translators: */ printf( __( 'Some plugins are well built to be translation ready. On front-end side, xili-language is able to switch the text_domain of the good plugin file or to the local theme_domain. So, if terms (and translations) are available in these .mo files, these terms are displayed in the right language. Rule for plugins without front-end text don’t need to be changed. Others need modification of php source via a customized filter (add_action).<br />More infos in sources and perhaps soon in <a href="%s">wiki</a>.', 'xili-language' ), $this->wikilink ); ?>
			<br /><?php esc_html_e( 'Sometime languages sub-folder (Domain Path) containing .mo files is not well defined in plugin source header. So, after looking inside plugin folder, insert the good one between / !', 'xili-language' ); ?><br />
			</em><br /><br />
			<table class="widefat trans-domain"><thead>
			<?php
			echo '<tr><th class="p-name">' . __( 'Name', 'xili-language' ) . '</th><th class="p-rule" >' . __( 'Rule', 'xili-language' ) . '</th><th class="p-domain">' . __( 'Plugin Domain', 'xili-language' ) . '</th><th style="p-path">' . __( 'Domain Path', 'xili-language' ) . '</th></tr>';
			echo '</thead><tbody>';
			$active_plugin_by_domain = array();
			foreach ( wp_get_active_and_valid_plugins() as $plugin_file ) {
				$plugin = get_plugin_data( $plugin_file, false, false );
				if ( 'Text Domain' != $plugin['TextDomain'] && '' != $plugin['TextDomain'] ) {
					$active_plugin_by_domain[ $plugin['TextDomain'] ]['plugin-data'] = $plugin;
					$active_plugin_by_domain[ $plugin['TextDomain'] ]['plugin-path'] = str_replace( WP_PLUGIN_DIR, '', plugin_dir_path( $plugin_file ) ); // sub-folder with /nameofplugin/
				}
			}
			foreach ( $this->xili_settings['domains'] as $domain => $state ) {
				if ( 'default' == $domain || ( ! in_array( $domain, $this->unusable_domains ) && isset( $active_plugin_by_domain[ $domain ] ) ) ) {
					$domaininlist = ( 'default' == $domain ) ? __( 'Switch default domain of WP', 'xili-language' ) : $active_plugin_by_domain[ $domain ]['plugin-data']['Name'];
				?>
				<tr><th>
				<label for="xili_language_domains_<?php echo $domain; ?>" class="selectit"><?php echo $domaininlist; ?>&nbsp;&nbsp;</label></th><td>
					<select id="xili_language_domains_<?php echo $domain; ?>" name="xili_language_domains_<?php echo $domain; ?>" >
						<option value="" <?php selected( $state, '' ); ?> /><?php esc_html_e( 'no modification', 'xili-language' ); ?></option>
						<option value="enable" <?php selected( $state, 'enable' ); ?> /> <?php esc_html_e( 'translation in local', 'xili-language' ); ?></option>
						<?php if ( 'default' != $domain ) { ?>
						<option value="renamed" <?php selected( $state, 'renamed' ); ?> /> <?php esc_html_e( 'translation in plugin', 'xili-language' ); ?></option>
						<option value="filter" <?php selected( $state, 'filter' ); ?> /> <?php esc_html_e( 'custom translation', 'xili-language' ); ?></option>
						<?php } ?>
					</select>
				</td>
				<td>&nbsp;&nbsp;
					<?php
					echo $domain;
					if ( 'filter' == $state ) {
						$has_filter = has_filter( 'load_plugin_domain_for_curlang_' . str_replace( '-', '_', $domain ) );
						if ( ! $has_filter ) {
							echo '<br /><em><small>' . sprintf( __( 'Customization requires filter tag:%s', 'xili-language' ), " 'load_plugin_domain_for_curlang_" . str_replace( '-', '_', $domain ) . "'" ) . '</small></em>';
						}
					}
					?>
				</td>
					<?php
					if ( 'default' != $domain ) {
						$value = ( '' == $active_plugin_by_domain[ $domain ]['plugin-data']['DomainPath'] ) ? '/' : $active_plugin_by_domain[ $domain ]['plugin-data']['DomainPath'];
						?>
						<td>&nbsp;&nbsp;<input id="xili_language_domain_path_<?php echo $domain; ?>" name="xili_language_domain_path_<?php echo $domain; ?>" type="text" value="<?php echo $value; ?>" />
							<input id="xili_language_plugin_path_<?php echo $domain; ?>" name="xili_language_plugin_path_<?php echo $domain; ?>" type="hidden" value="<?php echo $active_plugin_by_domain[ $domain ]['plugin-path']; ?>" />
						</td>
						<?php
					}
					?>
				</tr>
				<?php
				}
			}
			echo '</tbody></table>';
			if ( $this->show ) {
				print_r( $this->arraydomains );
			}
			?>
			</p></fieldset>

			<fieldset class="box" ><legend><?php esc_html_e( 'Locale (date) translation', 'xili-language' ); ?></legend><p>
				<?php esc_html_e( 'Since v2.4, new way for locale (wp_locale) translation.', 'xili-language' ); ?><br /><br />
				<label for="xili_language_wp_locale"><?php esc_html_e( 'Mode wp_locale', 'xili-language' ); ?> <input id="xili_language_wp_locale" name="xili_language_wp_locale" type="checkbox" value="wp_locale" <?php checked( $this->xili_settings['wp_locale'], 'wp_locale', true ); ?> /></label></p>
			</fieldset>

			<div class='submit'>
			<input id='updatespecials' name='updatespecials' type='submit' tabindex='6' value="<?php esc_html_e( 'Update', 'xili-language' ); ?>" /></div>

			<div class="clearb1">&nbsp;</div>
		<?php

	}

	/**
	 * Theme's information box
	 *
	 * @since 2.4.1
	 *
	 */
	public function on_sidebox_4_theme_info( $data ) {
		$template_directory = $this->get_template_directory;
		$current_theme_obj = wp_get_theme();
		if ( is_child_theme() ) { // 1.8.1 and WP 3.0
			$parent_theme_obj = wp_get_theme( get_option( 'template' ) );
			$theme_name = $current_theme_obj->get( 'Name' ) . ' </strong>' . __( 'child of', 'xili-language' ) . ' <strong>' . $parent_theme_obj->get( 'Name' ); //replace slug of theme
		} else {
			$theme_name = $current_theme_obj->get( 'Name' ); // get_option("current_theme"); // name of theme
		}
		?>
		<fieldset class="themeinfo"><legend><?php esc_html_e( 'Theme type and domain:', 'xili-language' ); ?></legend>
			<strong><?php echo ' - ' . $theme_name . ' -'; ?></strong>
			<?php
			if ( '' != $this->parent->thetextdomain ) {
				echo '<br />' . __( 'theme_domain:', 'xili-language' ) . ' <em>' . $this->parent->thetextdomain . '</em><br />' . __( 'as function like:', 'xili-language' ) . '<em> _e(\'-->\',\'' . $this->parent->thetextdomain . '\');</em>';
			} else {
				echo '<span class="red-alert">' . $this->admin_messages['alert']['no_domain_defined'] . '</span>';
				if ( '' != $this->domaindetectmsg ) {
					echo '<br /><span class="red-alert">' . $this->domaindetectmsg . ' ' . $this->admin_messages['alert']['default'] . '</span>';
				}
			}
			?>
			<br />

		</fieldset>
		<fieldset class="box"><legend><?php echo __( 'Language files:', 'xili-language' ); ?></legend>
		<p><?php echo __( 'Languages sub-folder:', 'xili-language' ) . ' ' . $this->xili_settings['langs_folder']; ?><br />
		<?php
		esc_html_e( 'Available MO files:', 'xili-language' );
		echo '<br />';
		if ( file_exists( $template_directory ) ) {
			 // when theme was unavailable
			$this->find_files( $template_directory, '/(\w\w_\w\w|\w\w).mo$/', array( &$this, 'available_mo_files' ) );
		}

		if ( file_exists( WP_LANG_DIR . '/themes' ) ) {
			// when languages/themes was unavailable
			echo '<br /><em>'; esc_html_e( 'Available MO files in WP_LANG_DIR/themes:', 'xili-language' );
			echo '</em><br />';
			$this->find_files( WP_LANG_DIR . '/themes', '/(' . $this->thetextdomain . ')-local-(\w\w_\w\w|\w\w).mo$/', array( &$this, 'available_mo_files' ), true );
		}

		if ( false === $this->parent->ltd ) {
			if ( is_child_theme() ) {
				echo '<br /><span class="red-alert">' . $this->admin_messages['alert']['no_load_function_child'] . '</span>';
			} else {
				if ( false === $this->parent->ltd_parent ) {
					echo '<br /><span class="red-alert">' . $this->admin_messages['alert']['no_load_function'] . '</span>';
				}
			}
		}
			?>
		</p><br />
		</fieldset>
		<?php
		$screen = get_current_screen(); // to limit unwanted side effects (form)

		if ( 'settings_page_language_files' == $screen->id && is_child_theme() ) {

			if ( $this->xili_settings['mo_parent_child_merging'] ) {
			?>
				<fieldset class="box"><legend><?php echo __( 'Language files in parent theme:', 'xili-language' ); ?></legend>
					<p>
					<?php echo __( 'Languages sub-folder:', 'xili-language' ) . ' ' . $this->xili_settings['parent_langs_folder']; ?><br />
					<?php
					esc_html_e( 'Available MO files:', 'xili-language' );
					echo '<br />';
					$template_directory = $this->get_parent_theme_directory;
					if ( file_exists( $template_directory ) ) { // when theme was unavailable
						$this->find_files( $template_directory, '/^(\w\w_\w\w|\w\w).mo$/', array( &$this, 'available_mo_files' ) );
					}
					if ( file_exists( WP_LANG_DIR . '/themes' ) ) { // when languages/themes was unavailable
						echo '<br /><em>' . __( 'in WP_LANG_DIR/themes:', 'xili-language' ) . '</em>' . '<br />';
						$this->find_files( WP_LANG_DIR . '/themes', '/(' . $this->parent->thetextdomain . ')-(\w\w_\w\w|\w\w).mo$/', array( &$this, 'available_mo_files' ), true );
					}
					if ( false === $this->parent->ltd_parent ) {
						echo '<br /><span class="red-alert">' . $this->admin_messages['alert']['no_load_function'] . '</span>';
					}
					?>
					</p><br />
				</fieldset>

			<?php
			}
			echo '<br />' . __( 'MO merging between parent and child', 'xili-language' ) . ':&nbsp;';
			// update 2.12.1
			if ( true === $this->xili_settings['mo_parent_child_merging'] ) {
				$this->xili_settings['mo_parent_child_merging'] = 'parent-priority';
			}
			echo '<select id="mo_parent_child_merging" name="mo_parent_child_merging" style="width:80%;" >';
			echo '<option value="" ' . selected( $this->xili_settings['mo_parent_child_merging'], '', false ) . ' >' . __( 'parent mo not used', 'xili-language' ) . '</option>';
			echo '<option value="parent-priority" ' . selected( $this->xili_settings['mo_parent_child_merging'], 'parent-priority', false ) . ' >' . __( 'with priority of parent mo', 'xili-language' ) . '</option>';
			echo '<option value="child-priority" ' . selected( $this->xili_settings['mo_parent_child_merging'], 'child-priority', false ) . ' >' . __( 'with priority of child mo', 'xili-language' ) . '</option>';
			echo '</select>';
			?>
				<div class='submit'>
				<input id='mo_merging' name='mo_merging' type='submit' value="<?php esc_html_e( 'Update', 'xili-language' ); ?>" />
				</div>
			<?php
		}
	}

	/**
	 * Theme's information box
	 *
	 * @since 2.12
	 *
	 */
	public function on_sidebox_5_theme_info( $data ) {

		$the_theme = wp_get_theme();
		?>
		<fieldset class="themeinfo"><legend><?php esc_html_e( 'Header infos', 'xili-language' ); ?></legend>
		<?php
		echo '<p>Name: ' . $the_theme->get( 'Name' ) . '</p>'; // fix get value
		echo '<p>Author: ' . $the_theme->get( 'Author' ) . '</p>';
		echo '<p>Version: ' . $the_theme->get( 'Version' ) . '</p>';
		if ( is_child_theme() ) {
			echo '<p>Template: ' . $the_theme->get( 'Template' ) . '</p>';
		};
		if ( $textdomain = $the_theme->get( 'TextDomain' ) ) {
			echo '<p>Text Domain: ' . $textdomain . '</p>';
			$path = get_stylesheet_directory();
			if ( $domainpath = $the_theme->get( 'DomainPath' ) ) {
				echo '<p>Domain Path: ' . $domainpath . '</p>';
				$path .= $domainpath;
			} else {
				echo '<p><em>' . __( 'The Domain Path is not specified in Theme Header of style.css ! - /languages - will be used by default.', 'xili-language') . '</em></p>';
				$path .= '/languages';
			}
			$folder = file_exists( $path );
			if ( ! $folder ) {
				echo '<p><em>' . __( 'The languages folder (Domain Path) does not exist inside theme folder.', 'xili-language' ) . '</em></p>';
			}

		} else {
			echo '<p><em>' . __( 'The Text Domain is not specified in Theme Header of style.css !', 'xili-language' ) . '</em></p>';
		}
		?>
		</fieldset>
		<?php
	}


	/**
	 * If checked, functions in uninstall.php will be fired when deleting the plugin via plugins list.
	 *
	 * @since 1.8.8
	 */
	public function on_sidebox_uninstall_content( $data ) {
		extract( $data );
		$delete = ( is_multisite() ) ? 'delete_this' : 'delete';
	?>
	<p class="red-alert"><?php echo $this->admin_messages['alert']['plugin_deinstalling']; ?></p>
	<label for="delete_settings">
			<input type="checkbox" id="delete_settings" name="delete_settings" value="<?php echo $delete; ?>" <?php checked( in_array( $this->xili_settings['delete_settings'], array( 'delete_this', 'delete' ) ), true ); ?> />&nbsp;<?php esc_html_e( 'Delete DB plugin‘s datas', 'xili-language' ); ?>
	</label>
	<div class='submit'>
		<input id='uninstalloption' name='uninstalloption' type='submit' tabindex='6' value="<?php esc_html_e( 'Update', 'xili-language' ); ?>" /></div>
	<?php
	}

}
