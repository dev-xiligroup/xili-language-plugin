<?php
namespace Xili_Admin;

/**
 * @package Xili-Language
 * @subpackage admin
 * functions for language files settings page
 * @since  2.23 traits files
 */

trait Xili_Admin_Page_Language_Files_Settings {

	/**
	 * Settings by experts and info
	 * @since 2.4.1
	 */
	public function on_load_page_files() {
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'wp-lists' );
			wp_enqueue_script( 'postbox' );

			add_meta_box( 'xili-language-sidebox-theme', __( 'Current theme infos', 'xili-language' ), array( &$this, 'on_sidebox_4_theme_info' ), $this->thehook5, 'side', 'high' );

			add_meta_box( 'xili-language-sidebox-info', __( 'Info', 'xili-language' ), array( &$this, 'on_sidebox_info_content' ), $this->thehook5, 'side', 'core' );

	}

		/**
	 * Files page - to download automattic mo files
	 *
	 * @since 2.8.8
	 */
	public function languages_files() {
		global $wp_version;
		$msg = 0;
		$themessages = array( 'ok' );
		$action = '';
		$emessage = '';

		$upgrade = false;
		if ( isset( $_POST['downloadmo'] ) ) {
			$action = 'downloadmo';
		} elseif ( isset( $_POST['mo_merging'] ) ) {
			$action = 'mo_merging';
		} elseif ( isset( $_POST['checkdownloadmo'] ) ) {
			$action = 'checkdownloadmo';
		}

		$themessages = array(
			'',
			__( 'mo files updated', 'xili-language' ),
			__( 'mo files unreachable', 'xili-language' ),
		);

		add_meta_box( 'xili-language-files', __( 'Languages System Files', 'xili-language' ), array( &$this, 'on_box_files_content' ), $this->thehook5, 'normal', 'low' );

		?>
		<div id="xili-language-files" class="wrap columns-2 minwidth">

			<h2><?php esc_html_e( 'Languages', 'xili-language' ); ?></h2>
			<h3 class="nav-tab-wrapper">
				<?php $this->set_tabs_line(); ?>
			</h3>
			<?php
			if ( 'downloadmo' == $action ) {
				check_admin_referer( 'xili-language-files' );
				$listlanguages = $this->get_listlanguages();
				$a = 0;

				foreach ( $listlanguages as $language ) {

					if ( 'en_US' != $language->name ) {

						if ( isset( $_POST[ 'downloadtheme_' . $language->name ] ) ) {

							if ( 'Choose' != $_POST[ 'downloadtheme_' . $language->name ] ) {

								$s = explode( '_', $_POST[ 'downloadtheme_' . $language->name ] );

								$theme = $s[1];

							} else {
								$theme = '';
							}
						} else {
							$theme = '';
						}

						if ( isset( $_POST[ 'download_' . $language->name ] ) && false !== ( strpos( $_POST[ 'download_' . $language->name ], 'Auto' ) ) ) {

							$version = str_replace( 'Auto_', '', $_POST[ 'download_' . $language->name ] );

							// download_mo_from_automattic( $locale = 'en_US', $upgrade = false, $theme_name = "" )
							$a = $this->download_mo_from_automattic( $language->name, $version, $theme, 1, $this->embedded_themes );

						} elseif ( isset( $_POST[ 'download_' . $language->name ] ) && false !== ( strpos( $_POST[ 'download_' . $language->name ], 'GlotPress' ) ) ) {

							$a = xl_download_mo_from_translate_wordpress( $language->name, $wp_version, $theme, 1, $this->embedded_themes );

						} elseif ( isset( $_POST[ 'downloadtheme_' . $language->name ] ) && 'Choose' !== $_POST[ 'downloadtheme_' . $language->name ] ) {

							if ( 'Auto' == $s[0] ) {

								$automattic_root = 'http://svn.automattic.com/wordpress-i18n/';
								$url_base = $automattic_root . "{$language->name}/branches/"; // replace /tags/ 2014-02-01
								$versions_to_check = $this->versions_to_check( $url_base ); // to recover version

								$version = $this->find_if_version_exists( $language->name, $versions_to_check, $url_base );

								$a = $this->download_mo_from_automattic( $language->name, $version, $theme, 2, $this->embedded_themes );

							} else {

								$a = xl_download_mo_from_translate_wordpress( $language->name, $wp_version, $theme, 2, $this->embedded_themes );
							}
						}
					}
				}
				$msg = ( $a ) ? 1 : 2;
			}

			if ( 'checkdownloadmo' == $action ) {
				check_admin_referer( 'xili-language-files' );
				$upgrade = true;
			}

			if ( 'mo_merging' == $action ) {
				check_admin_referer( 'xili-language-files' );
				$this->xili_settings['mo_parent_child_merging'] = $_POST['mo_parent_child_merging']; // 2.12 - select
				update_option( 'xili_language_settings', $this->xili_settings );
			}

			?>
			<?php if ( 0 != $msg ) { ?>
			<div id="message" class="updated fade"><p><?php echo $themessages[ $msg ]; ?></p></div>
			<?php } ?>
			<form name="files" id="files" method="post" action="options-general.php?page=language_files">
				<?php wp_nonce_field( 'xili-language-files' ); ?>
				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
				<p class="width23 boldtext">
				<?php
				/* translators: */
				printf( __( 'This tab is added to help aware webmaster to find WP core MO files from %1$s Automattic SVN server %2$s or %3$s GlotPress server%2$s. <br />Be aware that files are not always available for the current WP version (%4$s).<br /> So, the rules for Automattic SVN are set to find the most recent version equal or below the current (exp. 3.5.x or 3.4.x for 3.6). Only check the wished files if language is used in dashboard or default theme (2011, 2012,… ).', 'xili-language' ), '<a href="http://svn.automattic.com/wordpress-i18n/" target="_blank">', '</a>', '<a href="http://translate.wordpress.org/projects/wp" target="_blank">', $wp_version );
				echo '<br />' . esc_html__( 'In GlotPress, if nothing found in known versions, the /dev/ subfolder will be explored.', 'xili-language' );
				echo '<br /><strong>' . esc_html__( 'Be aware that choosen files will be downloaded in Core or Theme languages sub-folder. Verify folder rights !', 'xili-language' ) . '</strong>';

				?>
				</p>
				<?php
				$data = array(
					'action' => $action,
					'emessage' => $emessage,
					'upgrade' => $upgrade,
				);

				$this->setting_form_content( $this->thehook5, $data );
				?>
			</form>
		</div>
		<?php
		$this->setting_form_js( $this->thehook5 );
	}

	public function on_box_files_content( $data ) {
		extract( $data );
		$how = $this->states_of_mofiles( $upgrade );
		?>
		<div class="submit"><input id='downloadmo' name='downloadmo' type='submit' value="<?php esc_html_e( 'Download mo', 'xili-language' ); ?>" /></div>
		<div class="submit"><input id='checkdownloadmo' name='checkdownloadmo' type='submit' value="<?php esc_html_e( 'Check again before downloading mo', 'xili-language' ); ?>" /></div>

				<?php
				if ( 0 == $how ) {
					echo '<p><strong>' . esc_html__( 'All seems to be in WP languages folder', 'xili-language' ) . '</strong></p>';
				}
				?>
				<div class="clearb1">&nbsp;</div><br/>
		<?php
	}

	// display states of files locally and at automattic svn server

	public function states_of_mofiles( $show_upgrade = false ) {
		global $wp_version;
		$wp_version_details = explode( '.', $wp_version );

		$wp_version_root = $wp_version_details[0];

		if ( count( $wp_version_details ) > 2 ) {
			$wp_version_root_2 = $wp_version_details[0] . '.' . $wp_version_details[1];
		}

		$available_languages_installed = get_available_languages();
		//$available_languages_installed[] = $this->get_default_locale();
		$available_languages_installed = array_unique( $available_languages_installed );
		$available_languages_installed = array_filter( $available_languages_installed );

		$listlanguages = $this->get_listlanguages();

		$automattic_root = 'http://svn.automattic.com/wordpress-i18n/';

		$i = 0;
		if ( is_child_theme() ) {
			$theme_name = get_option( 'stylesheet' );
			$parent_theme_name = get_option( 'template' );

		} else {
			$theme_name = get_option( 'template' );
			$parent_theme_name = get_option( 'template' );

		}
		foreach ( $listlanguages as $language ) {

			echo '<div class="langbox" style="overflow:hidden;">';
			echo '<h4>';
			echo $language->description . ' (' . $language->name . ') ';
			echo '</h4>';

			$installed = in_array( $language->name, $available_languages_installed );

			if ( $installed ) {
				echo '<p>' . esc_html__( 'Installed in WP languages folder', 'xili-language' ) . '</p>';
			}

			$show = ( ( $installed && $show_upgrade ) || ( ! $installed ) ) ? true : false;

			if ( $show ) {

				// GlotPress
				$glot = false;
				if ( 'en_US' != $language->name ) {

					if ( $ver = $this->check_versions_in_glotpress( $language->name, $wp_version ) ) {
						if ( 'dev' == $ver ) { //2.8.8k
							echo '<p><em>' . esc_html__( 'Development Version available on GlotPress WordPress.org server', 'xili-language' ) . '</em></p>';
						} else {
							/* translators: */
							echo '<p><em>' . sprintf( esc_html__( 'Version %s ready to be downloaded on GlotPress WordPress.org server', 'xili-language' ), $wp_version ) . '</em></p>';
						}
						$glot = true;
					} else {
						$glot = false;
						echo '<p>' . esc_html__( 'Not available from GlotPress WordPress.org server', 'xili-language' ) . '</p>';
					}
				}
				// Automattic
				$url_base = $automattic_root . "{$language->name}/branches/"; // replaces /tags/ 2014-02-01
				$versions_to_check = $this->versions_to_check( $url_base );
				//$version/messages/{$language->name}.mo

				if ( 'en_US' == $language->name ) {
					echo '<p>' . esc_html__( 'Root language of WordPress', 'xili-language' ) . '</p>';
					$auto = false;
				} elseif ( $version = $this->find_if_version_exists( $language->name, $versions_to_check, $url_base ) ) {
					/* translators: */
					echo '<p><em>' . sprintf( esc_html__( 'Version %s ready to be downloaded from Automattic SVN server', 'xili-language' ), $version ) . '</em></p>';

					$auto = true;
					$i++;
				} else {
					$auto = false;
					echo '<p>' . esc_html__( 'Not available from Automattic SVN server', 'xili-language' ) . '</p>';
				}

				if ( $glot || $auto ) {

					echo esc_html__( 'Server to download', 'xili-language' );
					echo ' : <select id="download_' . $language->name . '" name="download_' . $language->name . '" >';
					echo '<option value="Choose" >' . esc_html__( 'Choose server...', 'xili-language' ) . '</option>';
					if ( $auto ) {
						echo '<option value="Auto_' . $version . '">' . esc_html__( 'Try from Automattic', 'xili-language' ) . '</option>';
					}
					if ( $glot ) {
						echo '<option value="GlotPress_' . $version . '">' . esc_html__( 'Try from GlotPress', 'xili-language' ) . '</option>';
					}

					echo '</select>';
				}
			}
			if ( 'en_US' != $language->name && in_array( $parent_theme_name, $this->embedded_themes ) ) {
				echo '<fieldset class="themebox"><legend>';
				/* translators: */
				echo sprintf( esc_html__( 'Theme\'s files %s', 'xili-language' ), ( ( is_child_theme() ) ? $theme_name . ' (' . $parent_theme_name . ') ': $theme_name ) );
				echo '</legend>';

				$mofile = get_template_directory() . '/languages/' . $language->name . '.mo';
				if ( file_exists( $mofile ) ) {
					if ( is_child_theme() ) {
						/* translators: */
						echo '<p>' . sprintf( esc_html__( 'Installed in parent theme\'s (%s) languages folder', 'xili-language' ), $parent_theme_name ) . '</p>';
					} else {
						echo '<p>' . esc_html__( 'Installed in theme\'s languages folder', 'xili-language' ) . '</p>';
					}
				} else {
					if ( is_child_theme() ) {
						/* translators: */
						echo '<p>' . sprintf( esc_html__( 'Not installed in parent theme\'s (%s) languages folder', 'xili-language' ), $parent_theme_name ) . '</p>';
					} else {
						echo '<p>' . esc_html__( 'Not installed in theme\'s languages folder', 'xili-language' ) . '</p>';
					}

					esc_html_e( 'Server to download theme file', 'xili-language' );
					echo ' : <select id="downloadtheme_' . $language->name . '" name="downloadtheme_' . $language->name . '" >';
					echo '<option value="Choose" >' . esc_html__( 'Choose server...', 'xili-language' ) . '</option>';
					echo '<option value="Auto_' . $parent_theme_name . '" >' . esc_html__( 'Try from Automattic', 'xili-language' ) . '</option>';
					echo '<option value="GlotPress_' . $parent_theme_name . '" >' . esc_html__( 'Try from GlotPress', 'xili-language' ) . '</option>';
					echo '</select>';

					$i++;
				}
				echo '</fieldset>';

			}
			echo '</div>';

		}
		return $i;
	}

	public function find_if_version_exists( $language, $versions_to_check, $url_base ) {
		if ( array() != $versions_to_check ) {
			foreach ( $versions_to_check as $version ) {
				$url = $url_base . "$version/messages/$language.mo";

				if ( $this->url_exists( $url ) ) {
					return $version;
				}
			}
		} else {
			return false;
		}
	}

	public function versions_to_check( $url_base, $upgrade = false ) {
		// define versions to check
		global $wp_version;

		$wp_version_details = explode( '.', $wp_version );

		if ( $this->url_exists( $url_base ) ) {
			// get all the versions available in the subdirectory
			$resp = wp_remote_get( $url_base );
			if ( is_wp_error( $resp ) || 200 != $resp['response']['code'] ) {
				return false;
			}

			preg_match_all( '#>([0-9\.]+)\/#', $resp['body'], $matches );
			if ( empty( $matches[1] ) ) {
				return false;
			}

			rsort( $matches[1] ); // sort from newest to oldest

			$versions = $matches[1];

			foreach ( $versions as $key => $version ) {

				$version_details = explode( '.', $version );

				if ( $version_details[0] != $wp_version_details[0] ) {
					unset( $versions[ $key ] );
					// will not try to download a too recent mofile
				} elseif ( version_compare( $version, $wp_version, '>' ) ) {
					unset( $versions[ $key ] );
					// no big diff
				} elseif ( abs( (int) $version_details[1] - (int) ( $wp_version_details[1] ) ) > 2 ) { // 3.6 and 3.5.x
					unset( $versions[ $key ] );
					// will not download an older version if we are upgrading
				} elseif ( $upgrade && version_compare( $version, $wp_version, '<=' ) ) {
					unset( $versions[ $key ] );
				}
			}
			return $versions;
		} else {
			return false;
		}
	}

	public function set_author_rules_register_setting() {
		$name = ( is_child_theme() ) ? get_option( 'stylesheet' ) : get_option( 'template' );
		$this->settings_author_rules = 'xiliml_' . $name . '_author_rules'; //'xiliml_author_rules'
		register_setting( $this->settings_author_rules . '_group', $this->settings_author_rules, array( $this, 'author_rules_validate_settings' ) );
		register_setting( $this->settings_authoring_settings . '_group', $this->settings_authoring_settings, array( $this, 'settings_authoring_settings_validate' ) );
	}



}
