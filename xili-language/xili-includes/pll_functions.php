<?php
// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin file, not much I can do when called directly.';
	exit;
}
/**
 * Clean terms from Polylang if previous install.
 *
 * @since 2.20.3
 */
function clean_pll_languages_list( $result = false ) {
	global $xili_language;

	$listlanguages = get_terms(TAXONAME, array('hide_empty' => false, 'get' => 'all', 'cache_domain' => 'core2_ppl' ));
	// test pll was not previously imported
	if ( empty($xili_language->xili_settings['pll_cleaned']) ) {
		$pll_languages = array();
		foreach( $listlanguages as $language ) {
			// search iso = locale in description
			$pll_description = unserialize( $language->description );
			if (!empty( $pll_description['locale'] )) {
				$xl_name = $pll_description['locale'];
				$xl_description = $language->name;
				$xl_alias = $language->slug;
				$slug = strtolower($xl_name);
				// update with xl value
				$term_data = wp_update_term( (int)$language->term_id,
					TAXONAME,
					array( 'name' => $xl_name,
						'slug' => $slug,
						'description' => $xl_description,
						) );
				if ( ! is_wp_error( $term_data ) ) {
					wp_set_object_terms( $term_data['term_id'], 'the-langs-group', TAXOLANGSGROUP);
					$xili_language->xili_settings['lang_features'][$slug] = array('charset'=>"",'hidden'=>"", 'alias'=>$xl_alias);
					$pll_languages[$xl_alias] = $slug;
				} else {
					$inserted = $this->safe_insert_in_language_group ( $term_data, 0 );
				}
			}
		}
		update_option( 'xili_language_pll_languages', $pll_languages );
		$xili_language->xili_settings['pll_cleaned'] = 1;
		update_option( 'xili_language_settings', $xili_language->xili_settings );
		rename_pll_uninstall_file();
		return true ;
	}

	return false ;
}
add_filter ( 'clean_previous_languages_list', 'clean_pll_languages_list' ); // called when creating new lists of languages (install)

/**
 * Rename uninstall polylang files to avoid unwanted erasing process
 *
 * @since 2.20.3
 */
function rename_pll_uninstall_file () {

	$plugin_dir = str_replace ( 'xili-language/xili-includes', 'polylang', plugin_dir_path( __FILE__ ) );

	$plugin_dir = trailingslashit( $plugin_dir );

	rename ( $plugin_dir . "uninstall.php", $plugin_dir . "uninstall_XL_desactived.php");

}

/**
 * Recreate aliases between translations from Polylang if previous install.
 *
 * @since 2.20.3
 */
function xl_import_previous_pll_aliases ( $result ) {

	global $xili_language;
	if ( ( $pll_xl_aliases = get_option( 'xili_language_pll_languages', false ) ) && $xili_language->xili_settings['pll_cleaned'] = 2 ) {

		foreach ( $pll_xl_aliases as $pll_alias => $slug ) {
			$xili_language->xili_settings['lang_features'][$slug]['alias'] = $pll_alias ;
		}
		$xili_language->xili_settings['pll_cleaned'] = 3;
		update_option( 'xili_language_settings', $xili_language->xili_settings );
		delete_option( 'xili_language_pll_languages' );
		return true;
	}
	return false;
}
//add_filter ('xl_import_previous_aliases', 'xl_import_previous_pll_aliases' ); = redundant

/**
 * Recreate links between translations from Polylang if previous install.
 *
 * @since 2.20.3
 */
function recreate_links_from_pll( $messages = array() ) {
	global $xili_language;

	//update_option( 'xili_language_pll_languages', array('en'=>'en_us','fr'=>'fr_fr','de'=>'de_de') );

	register_taxonomy( 'post_translations', null , array('label' => false, 'public' => false, 'query_var' => false, 'rewrite' => false));
	// search group of posts in taxonomy 'post_translations'
	$pll_post_groups = get_terms( 'post_translations', array('hide_empty' => false, 'get' => 'all') );
	$array_groups = array();
	if ( !is_wp_error( $pll_post_groups ) && $pll_post_groups ) {
		foreach( $pll_post_groups as $pll_one_post_group ) {
			if ($pll_one_post_group->description)
				$array_groups[] = unserialize ( $pll_one_post_group->description );
		}
		$pll_languages = get_option( 'xili_language_pll_languages', false );

		if ( $pll_languages && $array_groups ) {
			$g = count( $array_groups );
			foreach( $array_groups as $pll_one_group ) {
				foreach( $pll_one_group as $pll_key => $post_ID ) {
					$one_group_wo = $pll_one_group;
					unset( $one_group_wo [$pll_key]); // done by taxo
					$post_lang = $xili_language->get_post_language( $post_ID, 'slug');
					$i++;
					foreach( $one_group_wo as $key => $the_ID ) {
						$xl_lang = $pll_languages[$key];
						//error_log( $post_lang . ' update_post_meta ID= ' . $post_ID . ' lang-'. $xl_lang .' = ' . $the_ID );
						update_post_meta( $post_ID, QUETAG.'-' . $xl_lang, $the_ID );
					}
				}
			}
		}

		$xili_language->xili_settings['pll_cleaned'] = 2;

		$message =  sprintf (__( '%d multilingual groups and %d  posts were updated !', 'xili-language'), $g, $i );

	} else {
		$xili_language->xili_settings['pll_cleaned'] = 99;
		$message = __( 'Errors when importing Polylang!', 'xili-language');
	}
	update_option( 'xili_language_settings', $xili_language->xili_settings );
	return $message;
}
add_filter ( 'recreate_links_from_previous', 'recreate_links_from_pll' ); // called when after creating new list when webmaster starts

/**
 * Messages after step of pll import process in xl settings first tab (list of languages).
 *
 * @since 2.20.3
 */
function pll_list_messages( $messages, $count = 0 ) {
	global $xili_language;
	$message = $click = "";

	if ( !empty( $xili_language->xili_settings['pll_cleaned'] ) ) {
		if ( $xili_language->xili_settings['pll_cleaned'] < 2 )
		 	$message = sprintf(__('A list of %s languages from a previous Polylang install has just been updated !', 'xili-language'), $count );
		if ( $xili_language->xili_settings['pll_cleaned'] == 1 ) {
			$message .= "<br/>" . __('Links between translated posts and pages need to be refreshed!', 'xili-language');
			$link = wp_nonce_url( "?action=refreshlinks&amp;page=language_page", "refresh_pll_links" );
			$click = ' <a href="' . $link . '"> '. __('Start', 'xili-language') . '</a>';
		}
	}
	return array ('message' => $message, 'click' => $click );
}
add_filter ( 'previous_install_list_messages', 'pll_list_messages', 10, 2 ); // messages in xl-class-admin
?>