<?php

/**
 * During delete files (uninstall process): delete all taxonomies elements and relationships
 *
 * @since 1.8.8
 * @updated for multisite configuation
 *
 * ONLY WORKS IF WEBMASTER has checked "delete DB datas" in xili-language settings BEFORE deactivate and fires delete in plugins list.
 *
 */


if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	echo 'Impossible to erase xili-language plugin!';
	exit();
}

/*
 * Class to manage xili-language uninstallation
 * the goal is to remove only checked related data off db in mono or multisite
 *
 * @since 2.17
 */
class xili_language_uninstall {

	function __construct() {
		global $wpdb;

		// check if it is a multisite uninstall - if so, run the uninstall function for each blog id
		if (is_multisite()) {
			foreach ($wpdb->get_col("SELECT blog_id FROM $wpdb->blogs") as $blog_id) {
				switch_to_blog($blog_id);
				$this->uninstall($blog_id);
			}
			restore_current_blog($blog_id);
		}
		else {
			$this->uninstall();
		}
	}

	/*
	 * removes All plugin datas before deleting plugin files if option set
	 *
	 * @since 2.17
	 */
	function uninstall( $blog_id = 1 ) {
		// check if delete_settings is set
		global $wpdb;
		$xl_settings = get_option('xili_language_settings');

		if ( isset( $xl_settings['delete_settings'] ) && $xl_settings['delete_settings'] == 'delete' ) { // only if instance in multisite is checked

			$this->delete_taxonomies ($xl_settings); // see below

			/* since 2.12 for author_rules */

			$meta_key_list = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s AND option_name LIKE %s ", '%author_rules', 'xiliml_%' ));

			if ( $meta_key_list ) {
				foreach ( $meta_key_list as $one_meta_key ) {
					delete_option($one_meta_key);
				}
			}
			// transient for category counting - 2.16.4
			$meta_key_list = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s ", 'xili_count_category_%' ));

			if ( $meta_key_list ) {
				foreach ( $meta_key_list as $one_meta_key ) {
					delete_option($one_meta_key);
				}
			}

			/* since 2.12 for author_rules */
			delete_option('xiliml_authoring_settings'); // future uses
			delete_option('xiliml_frontend_settings');

			// transient
			delete_transient( 'get_flag_series' );
			delete_transient( 'admin_get_flag_series' ); // 2.16.4
			delete_transient( '_xl_activation_redirect' ); // 2.20

			// Options
			delete_option( 'xili_language_widgets_options' );
			delete_option( 'xili_widget_recent_comments' );
			delete_option( 'xili_widget_recent_entries' );
			delete_option( 'xl-bbp-addon-activated' ); // since 2.18
			delete_option( 'xili_language_pll_languages' ); // since 2.20.3

			// main settings now !
			delete_option( 'xili_language_settings' );

			if ( defined ('WP_DEBUG') && WP_DEBUG == true )
				error_log( sprintf('xili-language datas of db %s deleted' , $blog_id ) );

		} else {
			if ( defined ('WP_DEBUG') && WP_DEBUG == true )
				error_log( sprintf('xili-language datas of db %s not deleted' , $blog_id ) );

		}
	}
	/*
 	 * removes taxonomies and post_meta
 	 *
 	 * @since 1.8.8
 	*/
	function delete_taxonomies ($xl_settings) {

		// temporary register taxonomies (plugin is deactivated)

		register_taxonomy( $xl_settings['taxonomy'], 'post', array('hierarchical' => false, 'label' => false, 'rewrite' => false, 'show_ui' => false, '_builtin' => false ));
		register_taxonomy( $xl_settings['taxolangsgroup'], 'term', array('hierarchical' => false, 'update_count_callback' => '', 'show_ui' => false, 'label'=>false, 'rewrite' => false, '_builtin' => false ));
		register_taxonomy( 'link_'.$xl_settings['taxonomy'], 'link', array('hierarchical' => false, 'label' => false, 'rewrite' => false, 'show_ui' => false, '_builtin' => false ));


		// list of languages

		$languages = get_terms($xl_settings['taxonomy'], array('hide_empty' => false));
		//update_option ( 'xili_language_settings_bk', $xl_settings );


		// array postmeta lang-xx_xx
		foreach ($languages as $language ) {
			$postmeta_suffixes[] = $language->slug ;
		}
		foreach ($languages as $language ) {

			$term_id = $language->term_id;

			$post_IDs = get_objects_in_term( array( $term_id ), array( $xl_settings['taxonomy'] ) );

			foreach ( $post_IDs as $post_ID ) {
				// delete postmeta lang-xx_xx
				foreach ( $postmeta_suffixes as $postmeta_suffix ) {
					if ( $language->slug != $postmeta_suffix ) delete_post_meta( $post_ID, $xl_settings['reqtag'].'-'.$postmeta_suffix ) ;
				}
				// delete relationships posts
				wp_delete_object_term_relationships( $post_ID, $xl_settings['taxonomy'] );
			}

			wp_delete_object_term_relationships( $term_id, $xl_settings['taxolangsgroup'] );

			// link_language links
			$links = get_objects_in_term( array( $term_id ), array( 'link_'.$xl_settings['taxonomy'] ) );

			foreach ( $links as $link ) {
				wp_delete_object_term_relationships( $link, 'link_'.$xl_settings['taxonomy'] );
			}

			// delete terms
			$linklang = term_exists($language->slug,'link_'.$xl_settings['taxonomy']);
				if ( $linklang ) wp_delete_term( $term_id, 'link_'.$xl_settings['taxonomy'] );
			wp_delete_term( $term_id, $xl_settings['taxonomy'] );

		}
		$term_group = term_exists( 'ev_er', 'link_'.$xl_settings['taxonomy'] ); /* special ever language for links */
		// link_language links
		$links = get_objects_in_term( array( $term_group['term_id'] ), array( 'link_'.$xl_settings['taxonomy'] ) );

		foreach ( $links as $link ) {
			wp_delete_object_term_relationships( $link_id, 'link_'.$xl_settings['taxonomy'] );
		}
		wp_delete_term( $term_group['term_id'], 'link_'.$xl_settings['taxonomy'] );

		// delete taxonomie groups ['taxolangsgroup'] - when count = 0
		$term_group = term_exists( 'the-langs-group', $xl_settings['taxolangsgroup'] );
		wp_delete_term( $term_group['term_id'], $xl_settings['taxolangsgroup'] );

	}
}

new xili_language_uninstall();
?>