<?php
// functions-xl-admin-pomo

/**
 * Import MO file in class PO
 *
 *
 * @since 1.0.2 - only WP >= 2.8.4
 * @updated 1.0.5 - for wpmu
 * @param lang
 * @param $mofile since 1.0.5
 */
function xl_pomo_import_mo( $lang = '', $mofile = '', $local = false, &$xili_language_admin ) {
	$mo = new MO();

	if ( '' == $mofile && true == $local ) {
		$mofile = $xili_language_admin->get_template_directory . $xili_language_admin->xili_settings['langs_folder'] . '/local-' . $lang . '.mo';
	} elseif ( '' == $mofile ) {
		$mofile = $xili_language_admin->get_template_directory . $xili_language_admin->xili_settings['langs_folder'] . '/' . $lang . '.mo';
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
function xl_download_mo_from_automattic( $locale = 'en_US', $version, $theme_name = '', $upgrade = 0, $embedded_themes ) {

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
				if ( xl_url_exists( $url ) ) {
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
		if ( xl_url_exists( $url ) ) {
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

function xl_glotpress_version_folder( $version ) {
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
function xl_check_versions_in_glotpress( $locale, $version = 'dev' ) {

	$version_folder = xl_glotpress_version_folder( $version );
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
function xl_download_mo_from_translate_wordpress( $locale = 'en_US', $version, $theme_name = '', $upgrade = 0, $embedded_themes ) {

	$locale_subfolder = xl_check_versions_in_glotpress( $locale, $version );
	// return subfolder at WP (en, en-ca, fr, zn-tw…)
	if ( false === $locale_subfolder ) {
		return false;
	}

	$version_folder = xl_glotpress_version_folder( $version );

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

function xl_url_exists( $url ) {
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
