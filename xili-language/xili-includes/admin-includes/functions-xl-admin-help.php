<?php
/**
 * XL Admin class help adn pointer functions
 *
 * @package Xili-Language
 * @subpackage admin
 * @since 2.23
 */

// with xili-language, it is now possible to export/import xml with language for all authorized cpts
function xl_message_export_limited() {
	echo '<div class="error"><p>' . esc_html__( 'WARNING: With xili-language, language taxonomy is now ready to be imported from XML file generated here (All content choice). <br />So, before to import in a new website, be sure that xili-language plugins trilogy is active in this target site.', 'xili-language' ) . '</p>'
	. '<p>' . esc_html__( 'Therefore, before importing, verify that custom post types are registered in this new clean install.', 'xili-language' ) . '</p></div>';
}

function xl_test( &$xili_language_admin ) {

	echo $xili_language_admin->wikilink;
}

function xl_add_help_text( $contextual_help, $screen_id, $screen, &$xili_language_admin ) {
	if ( 'nav-menus' == $screen->id ) { // 2.8.8
		$wikilink = $xili_language_admin->wikilink . '/index.php/Xili-language:_languages_list_insertion_in_nav_menu';
		$to_remember =
			'<p><em>' . esc_html__( 'To show insertion metabox, remember to check them inside Screen Options.', 'xili-language' ) . '</em></p>' .
			'<p><strong>' . esc_html__( 'Things to remember to insert Languages list:', 'xili-language' ) . '</strong></p>' .
			'<ul>' .
				'<li>' . esc_html__( 'Checking radio button, choose type of languages list to insert.', 'xili-language' ) . '</li>' .
				'<li>' . esc_html__( 'Click the button - Add to Menu -.', 'xili-language' ) . '</li>' .
				'<li>' . esc_html__( 'Drag and drop to the desired place.', 'xili-language' ) . '</li>' .
				'<li>' . esc_html__( 'Do not modify content of url and label. These infos will be used to generate the final languages list according position in website during navigation.', 'xili-language' ) . '</li>' .
			'</ul>' .
			'<p><strong>' . esc_html__( 'Things to remember to insert Pages Selection:', 'xili-language' ) . '</strong></p>' .
			'<ul>' .
				'<li>' . esc_html__( 'With prefix - include= - fill the list of page IDs where sub-selection will be done according current language', 'xili-language' ) . '</li>' .
				'<li><em>' . esc_html__( 'Args is like in function wp_list_pages, example: <em>include=11,15</em><br />Note: If args kept empty, the selection will done on all pages (avoid it).', 'xili-language' ) . '</em></li>' .
				'<li>' . esc_html__( 'Check the input field line,', 'xili-language' ) . '</li>' .
				'<li>' . esc_html__( 'Click the button - Add to Menu -.', 'xili-language' ) . '</li>' .
				'<li>' . esc_html__( 'Drag and drop to the desired place.', 'xili-language' ) . '</li>' .

			'</ul>' .
			'<p><strong>' . esc_html__( 'Things to remember to insert Menus Selection:', 'xili-language' ) . '</strong></p>' .
			'<ul>' .
			'<li>' . esc_html__( 'After creating menu structures containing items linked to a language (but not assigned to a loacation), select a menu structure for each language in Menus selection box.', 'xili-language' ) . '</li>' .

			'<li>' . esc_html__( 'Check after selecting,', 'xili-language' ) . '</li>' .
			'<li>' . esc_html__( 'Click the button - Add to Menu -.', 'xili-language' ) . '</li>' .
			'<li>' . esc_html__( 'Drag and drop to the desired place.', 'xili-language' ) . '</li>' .
			'<li><em>' . esc_html__( 'If after changing or removing menu, you see - unavailable menu - in Menu list insertion point box, you must remove this insertion point and create a new one with new menus.', 'xili-language' ) . '</em></li>' .

			'</ul>' .
			/* translators: */
			'<p>' . sprintf( esc_html__( '%1$sMost recent infos about xili-language trilogy%2$s', 'xili-language' ), '<a href="' . $xili_language_admin->fourteenlink . '" target="_blank">', '</a>' ) . '</p>' .
			/* translators: */
			'<p>' . sprintf( esc_html__( '<a href="%s" target="_blank">Xili Wiki Documentation</a>', 'xili-language' ), $wikilink ) . '</p>';

		$screen->add_help_tab(
			array(
				'id' => 'xili-language-list',
				/* translators: */
				'title' => sprintf( esc_html__( 'About %s insertion points', 'xili-language' ), '[©xili]' ),
				'content' => $to_remember,
			)
		);

	}
	if ( 'attachment' == $screen->id ) { // 2.18.1
		$more_infos =
			'<p><strong>' . esc_html__( 'About multilingual features:', 'xili-language' ) . '</strong></p>' .
			'<ul>' .
			'<li>' . esc_html__( 'With media attachment, in multilingual context, it is possible to clone an attachment with the same media. The file is not duplicated. Title, Legend, Alt text can be written in each language.', 'xili-language' ) . '</li>' .
			/* translators: */
			'<li><em>' . sprintf( esc_html__( 'Fields under the description are available to assign and clone. A side box %s contain also infos and links to go to another clone in other languages.', 'xili-language' ), '<strong>' . __( 'Multilingual informations', 'xili-language' ) . '</strong>' ) . '</em></li>' .
			'</ul>' .
			/* translators: */
			 '<p>' . sprintf( esc_html__( '%1$sXili-language Plugin Documentation in WP repository%2$s', 'xili-language' ), '<a href="' . $xili_language_admin->repositorylink . '" target="_blank">', '</a>' ) . '</p>' .
			 /* translators: */
			'<p>' . sprintf( esc_html__( '%1$sMost recent infos about xili-language trilogy%2$s', 'xili-language' ), '<a href="' . $xili_language_admin->fourteenlink . '" target="_blank">', '</a>' ) . '</p>';

			$screen->add_help_tab(
				array(
					'id'      => 'more-media-infos',
					/* translators: */
					'title'   => sprintf( esc_html__( 'About %s multilingual features', 'xili-language' ), '[©xili]' ),
					'content' => $more_infos,
				)
			);
	}
	if ( in_array( $screen->id, array( 'settings_page_language_page', 'settings_page_language_front_set', 'settings_page_language_expert', 'settings_page_language_files', 'settings_page_author_rules', 'settings_page_language_support' ) ) ) {

		$page_title['settings_page_language_page'] = esc_html__( 'Languages list', 'xili-language' );
		$page_title['settings_page_language_front_set'] = esc_html__( 'Languages front-end settings', 'xili-language' );
		$page_title['settings_page_language_expert'] = esc_html__( 'Settings for experts', 'xili-language' );
		$page_title['settings_page_author_rules'] = esc_html__( 'Settings Authoring rules', 'xili-language' );
		$page_title['settings_page_language_files'] = esc_html__( 'Managing MO files', 'xili-language' );
		$page_title['settings_page_language_support'] = esc_html__( 'xili-language support', 'xili-language' );

		$line['settings_page_language_page'] = esc_html__( 'In this page, the list of languages used by the multilingual website is set.', 'xili-language' );
		$line['settings_page_language_front_set'] = esc_html__( 'Here, you decide what happens when a visitor arrives on the website homepage with his browser commonly set according to his mother language. Xili-language offers multiple ways according your content strategy.', 'xili-language' );
		$line['settings_page_language_expert'] = esc_html__( 'This sub-page will present how to set navigation menu in multilingual context with xili-language.', 'xili-language' );
		$line['settings_page_author_rules'] = esc_html__( 'This sub-page will present how to set authoring rules when creating translation.', 'xili-language' ) . '</li>' .
		'<li>' . esc_html__( 'When authors of post, page and custom post want to create a translation, it is possible to define what feature of original post can be copied to the post of target language (format, parent, comment or ping status,...). Some features are not ajustable (to be, it will be need premium services). For developer only: filters are available.', 'xili-language' );

		// list
		$line['settings_page_language_files'] = esc_html__( 'This sub-page will help to import MO files from WordPress SVN.', 'xili-language' ) . '</li>' .
		'<li>' . esc_html__( 'Be aware that, before to be displayed, this page scans datas from servers, be patient, it takes time.', 'xili-language' ) . '</li>' .
		'<li>' . esc_html__( 'If the theme is a child theme, in the box containing infos about theme, a list of languages from the parent theme is shown.', 'xili-language' ) . '</li>' .
		/* translators: */
		'<li>' . sprintf( esc_html__( 'If the option %s is checked, available translations from child are merged with those from parent. You can choose the priority: parent or child .mo. Local-xx_YY.mo have always priority.', 'xili-language' ), '<em>' . __( 'MO merging between parent and child', 'xili-language' ) . '</em>' );
		;

		$line['settings_page_language_support'] = esc_html__( 'This form to email to dev.xiligroup.com team your observations.', 'xili-language' );

		$wiki_page['settings_page_language_page'] = '/index.php/Xili-language_settings:_the_list_of_languages,_line_by_line';
		$wiki_page['settings_page_language_front_set'] = '/index.php/Xili-language_settings:_Home_page_and_more...';
		$wiki_page['settings_page_language_expert'] = '/index.php/Xili-language:_navigation_menu';
		$wiki_page['settings_page_language_files'] = '/index.php/Xili-language:_managing_mo_files';
		$wiki_page['settings_page_author_rules'] = '/index.php/Xili-language:_managing_authoring_rules';
		$wiki_page['settings_page_language_support'] = '/index.php/Xili-language_settings:_Assistance,_support_form';

		$this_tab =
		/* translators: */
			'<p><strong>' . sprintf( esc_html__( 'About this tab %s:', 'xili-language' ), $page_title[ $screen->id ] ) . '</strong></p>' .
			'<ul>' .
				'<li>' . $line[ $screen->id ] . '</li>' .
				/* translators: */
				'<li>' . sprintf( __( '<a href="%s" target="_blank">Xili Wiki Post</a>', 'xili-language' ), $xili_language_admin->wikilink . $wiki_page[ $screen->id ] ) . '</li>' .
			'</ul>';

		$to_remember =
			'<p><strong>' . esc_html__( 'Things to remember to set xili-language:', 'xili-language' ) . '</strong></p>' .
			'<ul>' .
				'<li>' . esc_html__( 'Verify that the theme is localizable (like kubrick, fusion or twentyten or others...).', 'xili-language' ) . '</li>' .
				'<li>' . esc_html__( 'Define the list of targeted languages.', 'xili-language' ) . '</li>' .
				'<li>' . esc_html__( 'Prepare .po and .mo files for each language with poEdit or xili-dictionary plugin.', 'xili-language' ) . '</li>' .
				'<li>' . esc_html__( 'If your website contains custom post type: check those which need to be multilingual. xili-language will add automatically edit meta boxes.', 'xili-language' ) . '</li>' .
			'</ul>';

		$more_infos =
			/* translators: */
			'<p><strong>' . esc_html__( 'For more information:' ) . '</strong></p>' .
			'<p><a href="' . $xili_language_admin->devxililink . '/xili-language" target="_blank">' . esc_html__( 'Xili-language Plugin Documentation', 'xili-language' ) . '</a></p>' .
			/* translators: */
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Xili Wiki Documentation</a>', 'xili-language' ), $xili_language_admin->wikilink ) . '</p>' .
			'<p><a href="' . $xili_language_admin->forumxililink . '" target="_blank">' . esc_html__( 'Support Forums', 'xili-language' ) . '</a></p>' .
			'<p><a href="https://codex.wordpress.org/" target="_blank">' . esc_html__( 'WordPress Documentation', 'xili-language' ) . '</a></p>';

		$screen->add_help_tab(
			array(
				'id'      => 'this-tab',
				'title'   => esc_html__( 'About this tab', 'xili-language' ),
				'content' => $this_tab,
			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'to-remember',
				'title'   => esc_html__( 'Things to remember', 'xili-language' ),
				'content' => $to_remember,
			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'more-infos',
				'title'   => esc_html__( 'For more information', 'xili-language' ),
				'content' => $more_infos,
			)
		);
	}
	return $contextual_help;
}

/**
 * Create list of messages
 * @since 2.23.01
 *
 */
function xl_create_library_of_alert_messages( &$admin_messages, $wikilink = '#' ) {

	/* translators: */
	$admin_messages['alert']['default'] = sprintf( __( 'See %1$sWiki%2$s for more details', 'xili-language' ), '<a href="' . $wikilink . '">', '</a>' );
	/* translators: */
	$admin_messages['alert']['no_load_function'] = sprintf( __( 'CAUTION: no load_theme_textdomain() in functions.php - review the content of file in the current theme or choose another canonical theme. %s', 'xili-language' ), $admin_messages['alert']['default'] );
	/* translators: */
	$admin_messages['alert']['no_load_function_child'] = sprintf( __( 'CAUTION: no load_theme_textdomain() in functions.php of child theme - review the content of file in the current child theme or leave as is to use only parent theme translation file. %s', 'xili-language' ), $admin_messages['alert']['default'] );
	$admin_messages['alert']['no_domain_defined'] = __( 'Theme domain NOT defined', 'xili-language' );
	/* translators: */
	$admin_messages['alert']['menu_auto_inserted'] = sprintf( __( 'Be aware that language list is already automatically inserted (see above) and %s', 'xili-language' ), $admin_messages['alert']['default'] );

	if ( is_multisite() ) {
		/* translators: */
		$admin_messages['alert']['plugin_deinstalling'] = sprintf( __( 'CAUTION: If checked below, before deactivating xili-language plugin, ALL the xili-language datas in database will be definitively ERASED when this plugin files will be deleted !!! (only multilingual features on <strong>this</strong> website of the WP network (multisite) install). %s', 'xili-language' ), $admin_messages['alert']['default'] );
	} else {
		/* translators: */
		$admin_messages['alert']['plugin_deinstalling'] = sprintf( __( 'CAUTION: When checking below, before deactivating xili-language plugin, if delete it through plugins list, ALL the xili-language datas in database will be definitively ERASED when this plugin files will be deleted !!! (only multilingual features). %s', 'xili-language' ), $admin_messages['alert']['default'] );
	}

	$admin_messages['alert']['erasing_language'] = __( 'Erase (only) multilingual features of concerned posts when this language will be erased !', 'xili-language' );

}

/**
	 * News pointer for tabs
	 *
	 * @since 2.6.2
	 *
	 */
function xl_localize_admin_js( $case_news, $news_id, &$xili_language_admin ) {
	$about = esc_attr__( 'Docs about xili-language', 'xili-language' );

	//$pointer_Offset = '';
	$pointer_edge = '';
	$pointer_at = '';
	$pointer_my = '';
	switch ( $case_news ) {

		case 'xl_new_version':
			$pointer_text = '<h3>' . esc_js( __( 'xili-language updated', 'xili-language' ) ) . '</h3>';
			/* translators: */
			$pointer_text .= '<p>' . esc_js( sprintf( __( 'xili-language was updated to version %s', 'xili-language' ), XILILANGUAGE_VER ) ) . '</p>';
			/* translators: */
			$pointer_text .= '<p>' . esc_js( sprintf( __( 'This version %1$s is tested with %2$s. (See details in %3$s) ', 'xili-language' ), XILILANGUAGE_VER, XILILANGUAGE_WP_TESTED, '<a href="' . $xili_language_admin->repositorylink . 'changelog/" title="' . $xili_language_admin->changelog . '" >' . $xili_language_admin->changelog . '</a>' ) ) . '</p>';

			$pointer_text .= '<p>' . esc_js(
				sprintf(
					/* translators: */
					__( 'More infos about the previous versions of %1$s here %2$s and %3$s.', 'xili-language' ),
					XILILANGUAGE_VER,
					'<a href="' . $xili_language_admin->repositorylink . 'changelog/" title="' . $xili_language_admin->changelog . '" >' . $xili_language_admin->changelog . '</a>',
					esc_js( ' “<a href="index.php?page=xl-about&xl-updated=1">' . __( 'in welcome page', 'xili-language' ) . '</a>”' )
				)
			) . '</p>';

			$pointer_text .= '<p>' . esc_js( __( 'See settings submenu', 'xili-language' ) . ' “<a href="options-general.php?page=language_page">' . __( 'Languages ©xili', 'xili-language' ) . '</a>”' ) . '</p>';
			/* translators: */
			$pointer_text .= '<p>' . esc_js( sprintf( __( 'Before to question dev.xiligroup support, do not forget to visit %s documentation', 'xili-language' ), '<a href="' . $xili_language_admin->wikilink . '" title="' . $about . '" >wiki</a>' ) ) . '</p>';

			$pointer_dismiss = 'xl-new-version-' . str_replace( '.', '-', XILILANGUAGE_VER );
			$pointer_div = '#menu-settings';

			$pointer_edge = 'left'; // the arrow
			$pointer_my = 'left+5px'; // relative to the box - margin = 5px
			$pointer_at = 'right'; // relative to div where pointer is attached
			break;

		case 'languages_settings':
			$pointer_text = '<h3>' . esc_js( __( 'To define languages', 'xili-language' ) ) . '</h3>';
			$pointer_text .= '<p>' . esc_js( __( 'This screen is designed to define the list of languages assigned to this website. Use the form below to add a new language with the help of preset list (popup) or by input your own ISO code.', 'xili-language' ) ) . '</p>';
			/* translators: */
			$pointer_text .= '<p>' . esc_js( sprintf( __( 'Before to question dev.xiligroup support, do not forget to visit %s documentation', 'xili-language' ), '<a href="' . $xili_language_admin->wikilink . '" title="' . $about . '" >wiki</a>' ) ) . '</p>';

			$pointer_dismiss = 'xl-settings-news';
			$pointer_div = '#xili-language-lang-list';
			break;

		case 'frontend_settings':
			$pointer_text = '<h3>' . esc_js( __( 'To define front-page', 'xili-language' ) ) . '</h3>';
			$pointer_text .= '<p>' . esc_js( __( 'This screen contains selectors to define the behaviour of frontpage according languages and visitors browser and more...', 'xili-language' ) ) . '</p>';
			/* translators: */
			$pointer_text .= '<p>' . esc_js( sprintf( __( 'Before to question dev.xiligroup support, do not forget to visit %s documentation', 'xili-language' ), '<a href="' . $xili_language_admin->wikilink . '" title="' . $about . '" >wiki</a>' ) ) . '</p>';

			$pointer_dismiss = 'xl-frontend-newss';
			$pointer_div = '#post-body-content';

			$pointer_edge = 'left'; // the arrow
			$pointer_my = 'top'; // relative to the box - margin = 5px
			$pointer_at = 'top-40px'; // relative to div where pointer is attached
			break;

		case 'languages_theme_infos':
			$pointer_text = '<h3>' . esc_js( __( 'Infos about current theme', 'xili-language' ) ) . '</h3>';
			$pointer_text .= '<p>' . esc_js( __( 'This metabox contains infos about the theme and the joined available language files (.mo).', 'xili-language' ) ) . '</p>';
			/* translators: */
			$pointer_text .= '<p>' . esc_js( sprintf( __( 'Before to question dev.xiligroup support, do not forget to visit %s documentation', 'xili-language' ), '<a href="' . $xili_language_admin->wikilink . '" title="' . $about . '" >wiki</a>' ) ) . '</p>';

			$pointer_dismiss = 'xl-frontend-theme-news';
			$pointer_div = '#xili-language-sidebox-theme';

			$pointer_edge = 'top';
			$pointer_my = 'top';
			$pointer_at = 'top+40px';
			break;

		case 'languages_expert':
			$pointer_text = '<h3>' . esc_js( __( 'For documented webmaster', 'xili-language' ) ) . '</h3>';
			$pointer_text .= '<p>' . esc_js( __( 'This screen contains nice selectors and features to customize menus and other objects for your CMS multilingual website.', 'xili-language' ) ) . '</p>';
			/* translators: */
			$pointer_text .= '<p>' . esc_js( sprintf( __( 'Before to question dev.xiligroup support, do not forget to visit %s documentation', 'xili-language' ), '<a href="' . $xili_language_admin->wikilink . '" title="' . $about . '" >wiki</a>' ) ) . '</p>';

			$pointer_dismiss = 'xl-expert-news';
			$pointer_div = '#poststuff';

			$pointer_edge = 'top';
			$pointer_my = 'top';
			$pointer_at = 'top-10px';
			break;

		case 'languages_expert_special':
			$pointer_text = '<h3>' . esc_js( __( 'For documented webmaster', 'xili-language' ) ) . '</h3>';
			$pointer_text .= '<p>' . esc_js( __( 'This metabox contains advanced selectors and features to customize behaviours for your CMS multilingual website.', 'xili-language' ) ) . '</p>';
			/* translators: */
			$pointer_text .= '<p>' . esc_js( sprintf( __( 'Before to question dev.xiligroup support, do not forget to visit %s documentation', 'xili-language' ), '<a href="' . $xili_language_admin->wikilink . '" title="' . $about . '" >wiki</a>' ) ) . '</p>';
			$pointer_dismiss = 'xl-expert-special-news';
			$pointer_div = '#xili-language-sidebox-special';

			$pointer_edge = 'left'; // the arrow
			$pointer_my = 'top'; // relative to the box - margin = 5px
			$pointer_at = 'top-40px'; // relative to div where pointer is attached
			break;

		case 'page_author_rules':
			$pointer_text = '<h3>' . esc_js( __( 'For webmaster and editor', 'xili-language' ) ) . '</h3>';
			$pointer_text .= '<p>' . esc_js( __( 'This settings page contains advanced selectors and features to customize behaviours when author or editor works in your CMS multilingual website.', 'xili-language' ) ) . '</p>';
			/* translators: */
			$pointer_text .= '<p>' . esc_js( sprintf( __( 'Before to question dev.xiligroup support, do not forget to visit %s documentation', 'xili-language' ), '<a href="' . $xili_language_admin->wikilink . '" title="' . $about . '" >wiki</a>' ) ) . '</p>';
			$pointer_dismiss = 'xl-page-author-rules';
			$pointer_div = '#poststuff';

			$pointer_edge = 'left'; // the arrow
			$pointer_my = 'top'; // relative to the box
			$pointer_at = 'right top-80px'; // relative to div where pointer is attached
			break;

		case 'languages_support':
			$pointer_text = '<h3>' . esc_js( __( 'In direct with support', 'xili-language' ) ) . '</h3>';
			/* translators: */
			$pointer_text .= '<p>' . esc_js( sprintf( __( 'Before to question dev.xiligroup support, do not forget to check needed website infos and to visit %s documentation', 'xili-language' ), '<a href="' . $xili_language_admin->wikilink . '" title="' . $about . '" >wiki</a>' ) ) . '</p>';

			$pointer_dismiss = 'xl-support-news';
			$pointer_div = '#poststuff';

			$pointer_edge = 'left'; // the arrow
			$pointer_my = 'top';
			$pointer_at = 'top'; // relative to div where pointer is attached
			break;

		case 'media_language':
			$pointer_text = '<h3>' . esc_js( __( 'Language of media', 'xili-language' ) ) . '</h3>';
			/* translators: */
			$pointer_text .= '<p>' . esc_js( sprintf( __( 'Language concern title, caption and description of media. With clonage approach, the file is shared between version for each language. When modifying a media, new fields are available at end of form. Before to assign language to media, do not forget to visit %s documentation', 'xili-language' ), '<a href="' . $xili_language_admin->wikilink . '" title="' . $about . '" >wiki</a>' ) ) . '</p>';

			$pointer_dismiss = 'xl-media-uploads';
			$pointer_div = '#language';

			$pointer_edge = 'right';
			$pointer_my = 'right top+10px';
			$pointer_at = 'left top';
			break;

		default: // nothing
			$pointer_text = '';
	}

		// inspired from www.generalthreat.com
	// Get the list of dismissed pointers for the user
	$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
	if ( in_array( $pointer_dismiss, $dismissed ) && 'xl-new-version-' . str_replace( '.', '-', XILILANGUAGE_VER ) == $pointer_dismiss ) {
		$pointer_text = '';

	} elseif ( in_array( $pointer_dismiss, $dismissed ) ) {
		$pointer_text = '';
	}

	return array(
		'pointerText' => html_entity_decode( (string) $pointer_text, ENT_QUOTES, 'UTF-8' ),
		'pointerDismiss' => $pointer_dismiss,
		'pointerDiv' => $pointer_div,
		'pointerEdge' => ( '' == $pointer_edge ) ? 'top' : $pointer_edge,
		'pointerAt' => ( '' == $pointer_at ) ? 'left top' : $pointer_at,
		'pointerMy' => ( '' == $pointer_my ) ? 'left top' : $pointer_my,
		// 'pointerOffset' => $pointer_Offset, deprecated
		'newsID' => $news_id,
	);
}

