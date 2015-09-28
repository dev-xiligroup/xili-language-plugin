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
	$i = 0;
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
		$expert_page = '<a href="' . admin_url('options-general.php?page=language_expert') . '" >' . __( 'Settings for experts', 'xili-language' ) . '</a>';
		$message =  sprintf ( __( '%1$d multilingual groups and %2$d  posts were updated ! Go to page: %3$s to continue importation process.', 'xili-language' ), $g, $i, $expert_page );

	} else {
		$xili_language->xili_settings['pll_cleaned'] = 99;
		$message = __( 'Errors when importing Polylang!', 'xili-language');
	}
	// get_pll_CPTs
	// here because CPTs are registered
	$pll_settings = get_option( 'polylang');
	$pll_post_types = $pll_settings['post_types'];
	$customposttypes = $xili_language->xili_settings['multilingual_custom_post'] ;
	if ( $pll_post_types ) {
		foreach ( $pll_post_types as $one_type ) {
			$custom = get_post_type_object ($one_type);
			if ( $custom ) { // is registered
				$clabels = $custom->labels;
				$customposttypes[$one_type] = array( 'name' => $custom->label , 'singular_name' => $clabels->singular_name , 'multilingual' => 'enable');
			}
		}
		$xili_language->xili_settings['multilingual_custom_post'] = $customposttypes ;
	}
	// Frontend settings
	if ( $pll_settings['browser'] ) {
		$xili_language->xili_settings['browseroption'] = 'browser';
		$xili_language->xili_settings['homelang'] = 'modify';
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

/**
 * Copy category and post_tag group
 *
 * to be used by XD and XTT
 *
 * @since 2.20.3
 */
function pll_copy_taxonomies_datas() {
	if ( ! current_user_can('activate_plugins') )
	wp_die( __( 'You do not have sufficient permissions to manage plugins for this site.', 'xili-language') );
	$results = array( __('no imported categories', 'xili-language') );
	$term_ids = array();
	register_taxonomy( 'term_translations', null , array('label' => false, 'public' => false, 'query_var' => false, 'rewrite' => false));
	foreach ( get_terms( 'term_translations', array('hide_empty'=>false)) as $term ) {
		$term_id = (int) $term->term_id;
		$pll_links = unserialize( $term->description );
		if ( ! empty( $pll_links ) ) {

			// which default taxonomy
			$term_id = (int)reset ($pll_links);
			if ( term_exists ( $term_id, 'category' ) ) {
				$term_ids['category'][] = array ( 'pll_links' => $pll_links );
			}
			if ( term_exists ( $term_id, 'post_tag' ) ) {
				$term_ids['post_tag'][] = array ( 'pll_links' => $pll_links );
			}
		}
	}
	if ( !empty( $term_ids['category'] ) ) {
		update_option( 'xili_language_pll_term_category_groups', $term_ids['category'] );
		$results[0] = __('category fields imported for xili-dictionary', 'xili-language');
	}
	if ( !empty( $term_ids['post_tag'] ) ) {
		update_option( 'xili_language_pll_term_post_tag_groups', $term_ids['post_tag'] );

		if ( class_exists('xili_tidy_tags') ) {
			global $xili_tidy_tags;
			$qu = 0;
			// create groups - wp_update_term
			foreach ( $term_ids['post_tag'] as $pll_links_group) {
				if ( count( $pll_links_group['pll_links'] ) > 1 ) {
					$c = 0;
					foreach ( $pll_links_group['pll_links'] as $key_pll => $linked_term_id) {
						if ( $c ) {
							$alias = get_term_by( 'id', (int)$alias_of, 'post_tag' );
							if ( $alias ) {
								$a = wp_update_term( (int)$linked_term_id, 'post_tag' , array( 'alias_of' => $alias->slug ) );
							}
						} else {
							$c++;
							$alias_of = $linked_term_id;
						}
					}
				}
			}
			$pll_languages = get_option( 'xili_language_pll_languages', false ); // pll=>xl_slug
			// add in xtt group
			foreach ( $term_ids['post_tag'] as $pll_links_group) {
				foreach ( $pll_links_group['pll_links'] as $key_pll => $linked_term_id) {
					$target_lang = $pll_languages[$key_pll];
					$res = term_exists ( $target_lang, $xili_tidy_tags->tidy_taxonomy ); // xtt group in this lang
					wp_set_object_terms((int) $linked_term_id, (int) $res ['term_id'], $xili_tidy_tags->tidy_taxonomy, false );
					$qu++;
				}
			}
			$results[] = sprintf(__('%s post tags assigned to language for xili-tidy-tags', 'xili-language'), $qu ) ;
		}
	}
	return implode (', ', $results );
}

/**
 * Clean polylang db records - customized functions from original uninstall file
 * Do not delete 'language' taxonomy !
 *
 * @since 2.20.3
 */
function pll_clean_db_records() {
	global $wpdb;

	// to secure direct call
	if ( ! current_user_can('activate_plugins') )
	wp_die( __( 'You do not have sufficient permissions to manage plugins for this site.', 'xili-language' ) );

	//nounce

	// need to register the taxonomies
	$pll_taxonomies = array( 'term_language', 'post_translations', 'term_translations' ); // w/o language used by XL
	foreach ($pll_taxonomies as $taxonomy)
		register_taxonomy($taxonomy, null , array('label' => false, 'public' => false, 'query_var' => false, 'rewrite' => false));

	$languages = get_terms('language', array('hide_empty'=>false)); //registered by XL

	$pll_xl_aliases = get_option( 'xili_language_pll_languages', false ); // to be used in future to recover taxonomies
	$xl_slug_pll_slug = array_flip ( $pll_xl_aliases ) ;

	// delete users options
	foreach (get_users(array('fields' => 'ID')) as $user_id) {
		delete_user_meta($user_id, 'user_lang');
		delete_user_meta($user_id, 'pll_filter_content');
		foreach ($languages as $lang) {
			$xl_slug = $lang->slug;
			delete_user_meta($user_id, 'description_'. $xl_slug_pll_slug[$xl_slug]);
		}
	}

	// delete menu language switchers
	$ids = get_posts(array(
		'post_type'   => 'nav_menu_item',
		'numberposts' => -1,
		'nopaging'    => true,
		'fields'      => 'ids',
		'meta_key'    => '_pll_menu_item'
	));

	foreach ($ids as $id)
		wp_delete_post($id, true);

	// delete the strings translations (<1.2)
	// FIXME: to remove when support for v1.1.6 will be dropped
	foreach ($languages as $lang)
		delete_option('polylang_mo'.$lang->term_id);

	// delete the strings translations 1.2+
	register_post_type('polylang_mo', array('rewrite' => false, 'query_var' => false));
	$ids = get_posts(array(
		'post_type'   => 'polylang_mo',
		'numberposts' => -1,
		'nopaging'    => true,
		'fields'      => 'ids',
	));
	foreach ($ids as $id)
		wp_delete_post($id, true);

	// delete all what is related to languages and translations
	foreach (get_terms($pll_taxonomies, array('hide_empty'=>false)) as $term) {
		$term_ids[] = (int) $term->term_id;
		$tt_ids[] = (int) $term->term_taxonomy_id;
	}

	if (!empty($term_ids)) {
		$term_ids = array_unique($term_ids);
		$wpdb->query("DELETE FROM $wpdb->terms WHERE term_id IN (" . implode(',', $term_ids) . ")");
		$wpdb->query("DELETE FROM $wpdb->term_taxonomy WHERE term_id IN (" . implode(',', $term_ids) . ")");
	}

	if (!empty($tt_ids)) {
		$tt_ids = array_unique($tt_ids);
		$wpdb->query("DELETE FROM $wpdb->term_relationships WHERE term_taxonomy_id IN (" . implode(',', $tt_ids) . ")");
	}

	// delete options
	delete_option('polylang');
	delete_option('widget_polylang'); // automatically created by WP
	delete_option('polylang_wpml_strings'); // strings registered with icl_register_string

	//delete transients
	delete_transient('pll_languages_list');
	delete_transient('pll_upgrade_1_4');

}

/**
 * Display form in expert tab
 *
 *
 * @since 2.20.3
 */
function pll_list_forms_action() {
	global $xili_language;
	if ( !empty($xili_language->xili_settings['pll_cleaned']) && $xili_language->xili_settings['pll_cleaned'] >= 2 ) {
		?>
		<p><?php _e('This multilingual website was formerly driven by Polylang.','xili-language'); ?></p>
		<?php
		$step = $xili_language->xili_settings['pll_cleaned'];
		switch ( $step ) {
			case 2: // 2 = achieved

				$label = __( 'Launch taxonomy importation.','xili-language' );
				if ( get_terms ( 'post_tag', array ( 'hide_empty' => false ) ) ) {
					if ( !class_exists( 'xili_tidy_tags' ) ) {
						$label .= '<br />(<em><strong>' . __( 'Be aware that xili-tidy-tags plugin is not active. Multilingual importation will be incomplete !', 'xili-language' ) . '</strong></em> )';
					} else {
						// test and create post_tag groups
						if ( version_compare( XILITIDYTAGS_VER, '1.11.1', '>' ) ) {
							global $xili_tidy_tags_admin ;
							$xili_tidy_tags_admin->xili_langs_import_terms();
						} else {
							$label .= '<br />(<em><strong>' . __( 'Be aware that xili-tidy-tags plugin version is not recent. Update it or goto xili-tidy-tags settings page to create language group !', 'xili-language' ) . '</strong></em> )';
						}
					}
				}
				$submit_id = 'fire_taxo_step';
				break;

			case 3:

				$label = __('Launch db cleaning.','xili-language');
				$submit_id = 'fire_clean_db_step';
				break;

			default:
				# code...
				break;
		}
		if ( $step < 4) {
			echo sprintf( '<fieldset id="pll-box" class="box"><legend>%s</legend>', __("Polylang importation last actions",'xili-language') ) ;
			wp_nonce_field( $submit_id , 'pll_cleaned' );
			echo sprintf( '<label for="%s" class="selectit">&nbsp;', 'pll_action' );
			echo sprintf( '<input name="%1$s" id="%1$s" type="checkbox" value="enable">', 'pll_action' );
			echo sprintf( '&nbsp;%s</label>',$label);
			echo sprintf( '<div class="submit"><input id="%1$s" name="%1$s" type="submit" value="%2$s" /></div>', $submit_id, __('Start','xili-language'));
			echo '</fieldset>';
		} else {
			$plugin_dir = str_replace ( 'xili-language/xili-includes', 'polylang', plugin_dir_path( __FILE__ ) );
			$plugin_dir = trailingslashit( $plugin_dir );

			if ( file_exists ( $plugin_dir . "polylang.php" ) ) {
				echo '<em>' . __('Now the importation process is achieved, you can delete the Polylang plugin files.','xili-language') . '</em>';
			} else {
				echo '<em>' . __('Now the importation process is achieved, Polylang plugin is deleted.','xili-language') . '</em>';
				$xili_language->xili_settings['pll_cleaned'] = 5 ;
				update_option( 'xili_language_settings', $xili_language->xili_settings );
			}


		}
	}
}
add_action ('import_list_forms_action', 'pll_list_forms_action');

/**
 * fire actions from expert tab
 *
 *
 * @since 2.20.3
 * @param ($_POST array )
 */
function pll_list_of_actions( $message, $post_array ) {
	if ( ! current_user_can('activate_plugins') )
	wp_die( __( 'You do not have sufficient permissions to manage plugins for this site.', 'xili-language') );

	global $xili_language;
	$actions = array ( 'fire_taxo_step', 'fire_clean_db_step' );
	$action = '';
	foreach ( $actions as $one ) {
		if ( isset ( $post_array[$one])) {
			$action = $one;
			continue ;
		}
	}
	if ( !$action ) return;
	check_admin_referer( $action, 'pll_cleaned' );
	if ( isset($post_array['pll_action']) ) {
		switch ( $action ) {
			case 'fire_taxo_step': // 2 = achieved
				$step = 3;
				$res = pll_copy_taxonomies_datas();
				$message = sprintf( __( 'Importation of taxonomies multilingual features done. ( %s )', 'xili-language' ), $res );
				break;

			case 'fire_clean_db_step':
				$step = 4;
				pll_clean_db_records();
				$message = __('Polylang datas are cleaned from database.', 'xili-language');
				break;

			default:
				$step = 2;
			break;
		}
		$xili_language->xili_settings['pll_cleaned'] = $step;
		update_option( 'xili_language_settings', $xili_language->xili_settings );
	}
	return $message;
}
add_filter ( 'import_list_of_actions', 'pll_list_of_actions', 10, 2 );
?>