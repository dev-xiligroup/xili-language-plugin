<?php
namespace Xili_Admin;

/**
 * @package Xili-Language
 * @subpackage admin
 * functions for help interface
 * @since  2.23 traits files
 */

trait Xili_Admin_Pomo {

	/**
	 * Import MO file in class PO
	 *
	 *
	 * @since 1.0.2 - only WP >= 2.8.4
	 * @updated 1.0.5 - for wpmu
	 * @param lang
	 * @param $mofile since 1.0.5
	 */
	public function pomo_import_mo( $lang = '', $mofile = '', $local = false ) {
		$mo = new \MO();

		if ( '' == $mofile && true == $local ) {
			$mofile = $this->get_template_directory . $this->xili_settings['langs_folder'] . '/local-' . $lang . '.mo';
		} elseif ( '' == $mofile ) {
			$mofile = $this->get_template_directory . $this->xili_settings['langs_folder'] . '/' . $lang . '.mo';
		}

		if ( file_exists( $mofile ) ) {
			if ( ! $mo->import_from_file( $mofile ) ) {
				return false;
			} else {

				return $mo;
			}
		} else {
			return false;
		}
	}

	/**
	 * Function to manage mo files downloaded from automattic
	 *
	 *
	 */
	public function download_mo_from_automattic( $locale = 'en_US', $version, $theme_name = '', $upgrade = 0, $embedded_themes ) {

		$mofile = WP_LANG_DIR . "/$locale.mo";

		// does core file exists in current installation ?
		if ( ( file_exists( $mofile ) && 0 == $upgrade ) || 'en_US' == $locale ) {
			return true;
		}

		// does language directory exists in current installation ?
		if ( ! is_dir( WP_LANG_DIR ) ) {
			if ( ! @mkdir( WP_LANG_DIR ) ) {
				return false;
			}
		}

		// will first look in tags/ (most languages) then in branches/ (only Greek ?)
		$automattic_locale_root = 'http://svn.automattic.com/wordpress-i18n/' . $locale;
		$automattic_locale_root = $automattic_locale_root . '/branches/'; // replaces /tags/ 2014-02-01

		$args = array(
			'timeout' => 30,
			'stream' => true,
		);

		if ( 2 != $upgrade ) {
			// only core files
			// try to download the file

			$resp = wp_remote_get(
				$automattic_locale_root . "$version/messages/$locale.mo",
				$args + array(
					'filename' => $mofile,
				)
			);
			if ( is_wp_error( $resp ) || 200 != $resp['response']['code'] ) {
				//continue; - forum 2015-12-04 - 16-01-29
				$dummy = 1;
			} else {
				// try to download ms and continents-cities files if exist (will not return false if failed)
				// with new files introduced in WP 3.4
				foreach ( array( 'ms', 'continents-cities', 'admin', 'admin-network' ) as $file ) {
					$url = $automattic_locale_root . "$version/messages/$file-$locale.mo";
					if ( $this->url_exists( $url ) ) {
						wp_remote_get(
							$url,
							$args + array(
								'filename' => WP_LANG_DIR . "/$file-$locale.mo",
							)
						);
					}
				}
			}
		}

				// try to download theme files if exist (will not return false if failed)
				// FIXME not updated when the theme is updated outside a core update
		if ( in_array( $theme_name, $embedded_themes ) ) {
			$url = $automattic_locale_root . "$version/messages/$theme_name/$locale.mo";
			if ( $this->url_exists( $url ) ) {
				wp_remote_get(
					$url,
					$args + array(
						'filename' => get_theme_root() . "/$theme_name/languages/$locale.mo",
					)
				);
			}
		}
		return true;

	}

	// GlotPress version sub-folder

	public function glotpress_version_folder( $version ) {
		$version_parts = explode( '.', $version );
		$version_to_search = $version_parts[0] . '.' . $version_parts[1] . '.x';
		$list = wp_remote_get( 'http://translate.wordpress.org/api/projects/wp/' );
		if ( is_wp_error( $list ) || 200 !== wp_remote_retrieve_response_code( $list ) ) {
			return '';
		}
		$list = json_decode( wp_remote_retrieve_body( $list ) );
		if ( is_null( $list ) ) {
			return '';
		}

		$filtered = wp_list_filter( $list->sub_projects, array( 'name' => $version_to_search ) ); // search in list  of sub_projects 2014-02-01

		$filtereds = array_shift( $filtered );
		if ( empty( $filtereds ) ) {
			return '';
		}

		return $filtereds->slug;
	}

	//TO DO - uses wp_download_language_pack
	public function check_versions_in_glotpress( $locale, $version = 'dev' ) {

		$version_folder = $this->glotpress_version_folder( $version );
		if ( '' == $version_folder ) {
			$version_folder = 'dev';
		}
		// Get the list of available translation from Translate WordPress. This is expected to be JSON.
		$translations = wp_remote_get( sprintf( 'http://translate.wordpress.org/api/projects/wp/%1$s', $version_folder ) ); // 2.8.8k

		if ( is_wp_error( $translations ) || 200 !== wp_remote_retrieve_response_code( $translations ) ) {
			// again with forcing 'dev'
			$translations = wp_remote_get( sprintf( 'http://translate.wordpress.org/api/projects/wp/%1$s', 'dev' ) ); // rules changed in glot
			if ( is_wp_error( $translations ) || 200 !== wp_remote_retrieve_response_code( $translations ) ) {
				return false;
			}
		}

		$translations = json_decode( wp_remote_retrieve_body( $translations ) );
		if ( is_null( $translations ) ) {
			return false;
		}

		$filtered = wp_list_filter(
			$translations->translation_sets,
			array(
				'locale' => substr( $locale, 0, 2 ),
			)
		); // 2.9.10 (no more wp_locale)
		// See if the requested $locale has an available translation
		$translations = array_shift( $filtered ); // param variable

		if ( empty( $translations ) ) {
			return false;
		}

		return $translations->locale;
	}

	/**
	 * Download from translation.wordpress.org
	 *
	 */
	public function download_mo_from_translate_wordpress( $locale = 'en_US', $version, $theme_name = '', $upgrade = 0, $embedded_themes ) {

		$locale_subfolder = $this->check_versions_in_glotpress( $locale, $version );
		// return subfolder at WP (en, en-ca, fr, zn-tw…)
		if ( false === $locale_subfolder ) {
			return false;
		}

		$version_folder = $this->glotpress_version_folder( $version );

		if ( '' == $version_folder ) {
			$version_folder = 'dev'; // 2.12.2
		}
		$mofile = WP_LANG_DIR . "/$locale.mo";

		// does file exists in current installation ?
		if ( ( file_exists( $mofile ) && 0 == $upgrade ) || 'en_US' == $locale ) {
			return true;
		}

		// does language directory exists in current installation ?
		if ( ! is_dir( WP_LANG_DIR ) ) {
			if ( ! @mkdir( WP_LANG_DIR ) ) {
				return false;
			}
		}

		// will first look in tags/ (most languages) then in branches/ (only Greek ?)
		$translate_wordpress_root = 'http://translate.wordpress.org/projects/wp/' . $version_folder . '/';

		$suffix = 'mo'; // tested with po

		// 'http://translate.wordpress.org/projects/wp/3.5.x/admin/fr/default/export-translations'
		// GET ( sent by verified jquery in above url)

		$sub_folder_array = array(
			'default' => '%lang%/default',
			'admin' => 'admin/%lang%/default',
			'admin-network' => 'admin/network/%lang%/default',
			'continents-cities' => 'cc/%lang%/default',
		);
		if ( 2 != $upgrade ) {
			// only theme files
			foreach ( $sub_folder_array as $prename => $one_subfolder ) {

				$url = $translate_wordpress_root . str_replace( '%lang%', $locale_subfolder, $one_subfolder ) . '/export-translations?format=' . $suffix;

				$fileprename = ( 'default' != $prename ) ? $prename . '-' : '';
				$request = wp_remote_get(
					$url,
					array(
						'filename' => WP_LANG_DIR . '/' . $fileprename . $locale . '.' . $suffix,
						'timeout' => 15,
						'stream' => true,
						'body' => array(),
					)
				);

				if ( 200 != wp_remote_retrieve_response_code( $request ) ) {
					unlink( WP_LANG_DIR . '/' . $fileprename . $locale . '.' . $suffix );
					// see /wp-includes/file.php
				}
			}
		}

		if ( in_array( $theme_name, $embedded_themes ) ) {

			// thanks for format - markoheijnen - http://buddypress.trac.wordpress.org/raw-attachment/ticket/4857/translatecode-003.php, http://buddypress.trac.wordpress.org/attachment/ticket/4857/translatecode-003.php

			// temp patch for twentythirteen 20130503 - fixed 20130504 via polyglots blog
			//$theme_subfolder = ( $theme_name == 'twentythirteen' ) ? 'twenty-thirteen' : $theme_name ;

			$url = $translate_wordpress_root . $theme_name . '/' . $locale_subfolder . '/default/export-translations?format=' . $suffix;
			$request = wp_remote_get(
				$url,
				array(
					'filename' => get_theme_root() . "/$theme_name/languages/$locale." . $suffix,
					'timeout' => 15,
					'stream' => true,
					'body' => array(),
				)
			);

			if ( 200 != wp_remote_retrieve_response_code( $request ) ) {
				@unlink( get_theme_root() . "/$theme_name/languages/$locale." . $suffix );
				// see /wp-includes/file.php
			}
		}
		return true;
	}

	public function url_exists( $url ) {
		//if (!$fp = curl_init($url)) return false;
		//return true;
		$file = $url;
		$file_headers = @get_headers( $file );
		if ( 'HTTP/1.1 404 Not Found' == $file_headers[0] ) {
			return false;
		} else {
			return true;
		}
	}

	/******************************* MO TOOLS FOR TAXONOMIES AND LOCAL VALUES ****************************/

	/**
	 * test if line is in entries
	 * @since 2.6.0
	 */
	public function is_msg_in_entries( $msg, $type, $entries, $context ) {
		foreach ( $entries as $entry ) {
			$diff = 1;
			switch ( $type ) {
				case 'msgid':
					$diff = strcmp( $msg, $entry->singular );
					if ( '' != $context ) {
						if ( null != $entry->context ) {
							$diff += strcmp( $context, $entry->context );
						}
					}
					break;
				case 'msgid_plural':
					$diff = strcmp( $msg, $entry->plural );
					break;
				case 'msgstr':
					if ( isset( $entry->translations[0] ) ) {
						$diff = strcmp( $msg, $entry->translations[0] );
					}
					break;
				default:
					if ( false !== strpos( $type, 'msgstr_' ) ) {
						$indice = (int) substr( $type, -1 );
						if ( isset( $entry->translations[ $indice ] ) ) {
							$diff = strcmp( $msg, $entry->translations[ $indice ] );
						}
					}
			}

			//if ( $diff != 0) { echo $msg.' i= '.strlen($msg); echo $entry->singular.') e= '.strlen($entry->singular); }
			if ( 0 == $diff ) {
				return true;
			}
		}
		return false;
	}

	public function get_msg_in_entries( $msg, $type, $entries, $context ) {
		foreach ( $entries as $entry ) {
			$diff = 1;
			switch ( $type ) {
				case 'msgid':
					$diff = strcmp( $msg, $entry->singular );
					if ( '' != $context ) {
						if ( null != $entry->context ) {
							$diff += strcmp( $context, $entry->context );
						}
					}
					break;
				case 'msgid_plural':
					$diff = strcmp( $msg, $entry->plural );
					break;
				case 'msgstr':
					if ( isset( $entry->translations[0] ) ) {
						$diff = strcmp( $msg, $entry->translations[0] );
					}
					break;
				default:
					if ( false !== strpos( $type, 'msgstr_' ) ) {
						$indice = (int) substr( $type, -1 );
						if ( isset( $entry->translations[ $indice ] ) ) {
							$diff = strcmp( $msg, $entry->translations[ $indice ] );
						}
					}
			}

			//if ( $diff != 0) { echo $msg.' i= '.strlen($msg); echo $entry->singular.') e= '.strlen($entry->singular); }
			if ( 0 == $diff ) {
				if ( isset( $entry->translations[0] ) ) {
					return array(
						'msgid' => $entry->singular,
						'msgstr' => $entry->translations[0],
					);
				} else {
					return array();
				}
			}
		}
		return array();
	}


	/**
	 * Detect if cpt are saved in theme's languages folder
	 * @since 2.0
	 *
	 */
	public function is_msg_saved_in_localmos( $msg, $type, $context = '', $mode = 'list' ) {
		$thelist = array();
		$thelistsite = $the_translation_results = array();
		$outputsite = '';
		$output = '';

		$listlanguages = $this->get_listlanguages();

		foreach ( $listlanguages as $reflanguage ) {
			if ( isset( $this->local_theme_mos[ $reflanguage->slug ] ) ) {
				if ( 'list' == $mode && $this->is_msg_in_entries( $msg, $type, $this->local_theme_mos[ $reflanguage->slug ], $context ) ) {
					$thelist[] = '<span class="lang-' . $reflanguage->slug . '" >' . $reflanguage->name . '</span>';

				} elseif ( 'single' == $mode || 'array' == $mode ) {
					$res = $this->get_msg_in_entries( $msg, $type, $this->local_theme_mos[ $reflanguage->slug ], $context );
					if ( array() != $res ) {
						$thelist[ $reflanguage->name ] = $res;
						if ( 'array' == $mode ) {
							$the_translation_results[ $reflanguage->slug ] = array(
								'lang_name' => $reflanguage->name,
								'msg_id_str' => $res,
							);
						}
					}
				}
			}

			if ( is_multisite() ) {
				if ( isset( $this->local_site_mos[ $reflanguage->slug ] ) ) {
					if ( $this->is_msg_in_entries( $msg, $type, $this->local_site_mos[ $reflanguage->slug ], $context ) ) {
						$thelistsite[] = '<span class="lang-' . $reflanguage->slug . '" >' . $reflanguage->name . '</span>';
					}
				}
			}
		}

		if ( 'list' == $mode ) {

			$output = ( array() == $thelist ) ? '<br /><small><span style="color:black" title="' . esc_attr__( "No translations saved in theme's .mo files", 'xili-dictionary' ) . '">**</span></small>' : '<br /><small><span style="color:green" title="' . __( "Original with translations saved in theme's files: ", 'xili-dictionary' ) . '" >' . implode( ' ', $thelist ) . '</small></small>';

			if ( is_multisite() ) {

				$outputsite = ( array() == $thelistsite ) ? '<br /><small><span style="color:black" title="' . esc_attr__( "No translations saved in site's .mo files", 'xili-dictionary' ) . '">**</span></small>' : '<br /><small><span style="color:green" title="' . __( "Original with translations saved in site's files: ", 'xili-dictionary' ) . '" >' . implode( ', ', $thelistsite ) . '</small></small>';

			}
		} elseif ( 'single' == $mode ) {

			if ( array() == $thelist ) {

				$output = __( 'Not yet translated in any language', 'xili-language' ) . '<br />';
			} else {
				$output = '';
				foreach ( $thelist as $key => $msg ) {

					$output .= '<span class="lang-' . strtolower( $key ) . '" >' . $key . '</span> : ' . $msg['msgstr'] . '<br />';
				}
			}
		} elseif ( 'array' == $mode ) {
			return $the_translation_results;
		}

		return array( $output, $outputsite );

	}

	/**
	 * create an array of local mos content of theme
	 *
	 * @since 2.6.0
	 */
	public function get_localmos_from_theme() {
		$local_theme_mos = array();

		$listlanguages = $this->get_listlanguages();

		if ( is_multisite() ) {
			if ( ( $uploads = wp_upload_dir() ) && false === $uploads['error'] ) {
				$folder = $uploads['basedir'] . '/languages';
			}
		}

		foreach ( $listlanguages as $reflanguage ) {
			if ( is_multisite() ) {
				$folder_file = $folder . '/local-' . $reflanguage->name . '.mo';
			} else {
				$folder_file = '';
			}

			$res = $this->pomo_import_mo( $reflanguage->name, $folder_file, true ); // local only
			if ( false !== $res ) {
				$local_theme_mos[ $reflanguage->slug ] = $res->entries;
			}
		}

		return $local_theme_mos;
	}

}
