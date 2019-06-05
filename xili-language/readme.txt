=== xili-language ===
Contributors: michelwppi, MS dev.xiligroup.com
Donate link: http://dev.xiligroup.com/
Tags: multilingual, taxonomy, language, .mo file, localization
Requires at least: 4.9
Tested up to: 5.2
Stable tag: 2.23.12
License: GPLv2
xili-language lets you create and manage multilingual WP site in several languages with yours or most famous localizable themes. Ready for CMS design.

== Description ==

**xili-language provides for a bilingual (or multilingual) website an automatic selection of language (.mo) in theme according to the language of current post(s) or page. Theme's behaviour can be fully personalized through settings, hooks and api. Ready for CMS developers and designers.**

* xili-language plugin provides an automatic selection of language in theme according to the language of displayed post, series of posts, page or articles. *If the post is in gaelic, the texts of the theme will be in gaelic if the author checks the post as gaelic and if the theme contains the right .mo file for this target language.*

* A very readable interface with a list of titles (and links) to help you write/edit/modify articles and their translations.

* To help authoring, current user can choose language of his dashboard.

* xili-language select *on the fly* the multilingual .mo files present in the theme's folder (no cookies, no redirections like "301").

* xili-language uses a custom taxonomy to specify language of post, page and custom post. **Full compatible with WP JSON REST API**

* xili-language plugin works on Wordpress installation in mono (standalone) or on one site belonging to a multisite (network) install.

* xili-language plugin works on Wordpress installation for WebApp with JSON REST API - see [changelog](https://wordpress.org/plugins/xili-language/#developers) because [WP JSON REST API](https://wordpress.org/plugins/json-rest-api/) 1.2.1 in under full development but yet powerful.
* As *educational plateform* in constant changing since 2009, xili-language trilogy tries to use most of the WordPress Core functions and features (Custom taxonomy, API, metabox, pointer, help, pomo libraries, ...). The options are adjustable.

= Version 2.23.12 =
* Last Updated 2019-06-05
* W A R N I N G - see [tab and chapters in changelog](https://wordpress.org/plugins/xili-language/#developers/)

> For bbPress users, xili xl-bbp-addon plugin is no more a plugin. Components are optionally (if bbPress active) included. An option is also added in Experts tab of settings.

= Prequisite =
* A project of a website with articles in different languages.
* A **localizable theme** : Every themes with **localization** (or translation-ready like twentyfourteen) can be easily used (and improved) for realtime multilingual sites.
* A tool to translate .po files of the theme and built .mo files (poEdit or better xili-dictionary - *see below* ).
* see [this page in wiki.xiligroup.org](http://wiki.xiligroup.org/index.php/Xili-language:_Getting_started,_prerequisites).
* Php 7.2.

= What to prepare before and during installation before activating =
* verify that your theme is translation-ready. Collect .po files of theme for target languages.
* if rtl languages are used, verify that theme contains rtl.css file.

= Links and documentation to read before activating =
* Check out the [screenshots](https://wordpress.org/plugins/xili-language/screenshots/) to see it in action and other tabs [here](https://wordpress.org/plugins/xili-language/other_notes/).
* [latest news inside twentyfourteen-xili example](http://2014.extend.xiligroup.org/),
* [latest bundled child theme of twentyfifteen: twentyfifteen-xili example](http://2015.extend.xiligroup.org/),
* [xili wiki](http://wiki.xiligroup.org/),
* [news and history](http://dev.xiligroup.com/xili-language/),
* [forum](http://dev.xiligroup.com/?post_type=forum) to read experience of users and obtain some support,
* For **webmaster**: Before to be moved in wiki, [table](http://dev.xiligroup.com/?p=1432) summarizes all the technical features (widgets, template tags, functions and hooks) of this powerful plugin for personalized CMS created by webmaster,
* For **german speaking webmaster** some [documentations and posts](http://2012.wpmu.xilione.com/?lang=de_de) are written by YogieAnamCara of [sensorgrafie](http://www.sensorgrafie.de)

* and finally the source code of the plugin itself if you read php!

= Themes provided as examples =
* Since WordPress 4.1, the default theme named **twentyfifteen** can be used without php coding for a multilingual site [as shown here twentyfifteen-xili](http://2015.extend.xiligroup.org).
* Other child theme examples of bundled parent themes: **twentyeleven** [twentyeleven-xili](http://2011.wpmu.xilione.com/), **twentytwelve** [twentytwelve-xili](http://2012.wpmu.xilione.com/), **twentythirteen** [twentythirteen-xili](http://2013.extend.xiligroup.org/), **twentyfourteen** [twentyfourteen-xili](http://2014.extend.xiligroup.org/), **twentyfifteen** [twentyfifteen-xili](http://2015.extend.xiligroup.org/).

= Other compatible plugins by xiligroup dev =
**TRILOGY FOR MULTILINGUAL CMS WEBSITE**
including [xili-language plugin](https://wordpress.org/plugins/xili-language/)

Please verify that you have installed the latest versions of:

* [xili-dictionary plugin](https://wordpress.org/plugins/xili-dictionary/): With xili-dictionary, it is easier to create or update online, via admin/dashboard UI, the files .mo of each language.
* [xili-tidy-tags plugin](https://wordpress.org/plugins/xili-tidy-tags/): With xili-tidy-tags, it is now possible to display sub-selection (cloud) of **tags** according language and semantic trans-language group (trademark,…).

= That this plugin does not =
*With around 8000 php lines, xili-language is not everything…*

* xili-language plugin **does not create additional tables in the database**, do not create cookies and only use 4 lines in Options table. xili-language simply makes proper use of the taxonomy tables and postmeta table offered by WordPress to define language and link items between them. Because xili-language plugin does not modify deeply the post edit UI, it is possible to use **iPhone / iPod Touch** Wordpress app to prepare and draft the post.

* xili-language plugin does not replace the author or the editor. No automatic translation. Content strategist is the master of the languages, the contents and the navigation inside the website. With xili-dictionary, webmaster can translate the theme's items and when .mo files are in place, xili-dictionary can be deactivated. For design, the creator is free to choose text or graphic. xili-language does not provide flags (or few as example in child-theme example like [twentytwelve-xili](http://2012.wpmu.xilione.com) )!

= Newbie, WP user, Developer,… =

* Dear **Newbie:** originally built for webmaster and developer, the plugin trilogy progress since 6 years to be more and more plug and play for newbies who can read and spend a little time mainly for translation.

* xili-language is also dedicated for theme's creator or webmaster with knowledges in CMS and WP and having (or not) tools to create .mo language files. Through API (hook), the plugin add automatic tools (or links or filters) for sidebar or top menus. Categories or Archives lists are translated also.
* xili-language provides also series of functions which can be *hooked* in the functions.php file of the theme that you create i.e. for a cms like multilingual website.

= Licence, donation, services, "as is", ... =
Contrary to popular belief, *GPL doesn't say that everything must be zero-cost*, just that when you receive the software (plugin or theme) that it not restrict your freedoms in how you use it. *Free open source plugin does not mean free services*

* Texts of licence: [GPLv2](http://www.gnu.org/licenses/gpl-2.0.html)
* Donation link via paypal in sidebar of [dev.xiligroup site](http://dev.xiligroup.com/)
* Services : As authors of plugin, dev.xiligroup team is able to provide services (consulting, training, support) with affordable prices for WP multilingual contexts in corporate or commercial websites.
* Plugin is shipped **as is** : see no warranty chapter in license GPLv2.

= Roadmap =

* Improved documentation for getting starts, template tags and functions - [here in news](http://2014.extend.xiligroup.org/) or [here by Vladimir](http://2014.extend.xiligroup.org/en/description-and-features/xili-language-first-installation-by-vladimir/).
* Delivery of a *premium* services kit (with powerful features and attractive fees) packaged with professional training and support.
* Updating sources *(parts are 6 years old)* with new libraries provided since WP 4.2.
* Contributions are welcome ;-)

== Installation ==

READ CAREFULLY ALL THE README FILE AND PREREQUISITES

Read this [recent page](http://2014.extend.xiligroup.org/en/description-and-features/xili-language-first-installation-by-vladimir/) and also [wiki](http://wiki.xiligroup.org) for older versions and features.

1. Upload the folder containing `xili-language.php` and language files to the `/wp-content/plugins/` directory,
2. Verify that your theme is international compatible - translatable terms like `_e('the term','mytheme')` and no text hardcoded - and contains .mo and .po files for each target language - (application poEdit and/or plugin [xili-dictionary](http://dev.xiligroup.com/xili-dictionary/) can be used)
3. Verify that a domain name is defined in your theme - see note at end list below,
4. Activate the plugin through the *'Plugins'* menu in WordPress,
5. Go to the dashboard settings tab - languages - and adapt default values if necessary by adding your languages in the catalog. You can set the order (1,2,3...) of the series. (used in `language_attributes()` template_tag).
6. Modify each post by setting (checking) the language in xili-language box (under the content box) before publishing.
7. Other settings and parts (Browser detection, widgets, shortcode, template tags) see below… and examples.

Some steps to prepare a rich installation:

= Tabs of settings =

Settings are progressively reorganized in 6 tabs:

1. **Languages list**: Where you define the list of languages needed in your multilingual website.
2. **Languages front-end settings**: Where you define some rules and behaviour when visitor arrives in the website or navigates inside.
3. **Settings for experts**: Where experts are able to set and recovers previous sets. (backwards compatibility)
4. **Managing language files**: To import .mo files for theme or dashboard (if available in GlotPress or Automattic servers).
5. **Managing Authoring rules**: To define rules and settings in the dashboard side and help authoring.
6. **xili-language support**: A form to send an email to xiligroup support.

(online help on the top right tab for each settings page)

= Additional infos =

1. Before using your own theme, to understand how xili-language works, install the example child theme of one of the bundled themes like TwentyFourteen shown in this commented [demo site](http://2014.extend.xiligroup.org).
2. Child of bundled themes include a navigation menu - [see links in FAQ](https://wordpress.org/plugins/xili-language/faq/) -. In xili-language settings, it is possible to insert automatically languages list in the menu previously set by you.
3. If you are webmaster and want to add lot of personalizations in your theme, read source and visit [latest news](http://2014.extend.xiligroup.org).

= Browser detection for visitors or authors =
To change the language of the frontpage according to the language of the visitor's browser, check the popup in right small box in settings.
To change the default language of a new post according to the language of the author's browser, check the popup in right small box in settings.

= xili-language and widgets =

Three widgets are created to enrich sidebar : list of languages, recent posts and recent comments with sub-selection according current language.

= xili-language and shortcode =

SHORTCODE to add a link to other language inside content of a post like:

`[linked-post-in lang="fr_fr"]Voir cet article[/linked-post-in]`

by default use context 'linktitle' for translation 'A similar post in %s' text of link title attribut.

SHORTCODE to display only content if current language:

`[xili-show-if lang=fr_FR ]contenu de la page boutique multilingue[/xili-show-if]`

param lang can be ISO or slug.

SHORTCODE to insert translated msgid content according current language

Easy to insert .mo item in your current content.
`[xili18n msgid='yes']`
domain by default is here theme textdomain
`[xili18n msgid='yes' ctxt='front' textdomain='default']`
above it is the WP core textdomain

SHORTCODE to insert URL of flag set with a language:
`[xili-flag lang='fr_FR']` returns url of french flag


= xili-language and template tags =

* xili-language "*translates*" template tags and permits some variations for webmasters:

The most current template tags don't need special work: `wp_list_categories()`, `wp_title()`,...

`wp_get_archives`
Possible that very special permalinks need to use hook named `xiliml_get_archives_link`. -
Sub selection of archives for `wp_get_archives()` with &lang= (see § below)

`wp_list_pages()` with optional query tag &lang=

* xili-language "*provides*" new template tags (or widgets) to solve issues from WP core or add new features for CMS:

`xili_language_list()` - outside loop (sidebar) display the languages of the site (used also by widget)

`xiliml_the_category()` - replace the_category() template tag of WP Core


improved `xiliml_the_others_posts()` function and theme tag to be used in multilingual category loop and by option (array) to return an array of linked posts in other languages (useful for CMS webmasters) (array of lang and id ) - the id is easily transformable in permalink with function `get_permalink()` when using this array.

= xili-language and specific functions =

Requires knowledges in php and WP !
After class in sources files, some functions are available - see sources for details.

* `the_curlang()` returns by default the slug of the current language of the displayed webpage (alias `xili_curlang()` since 2.11+). If param is specified, return ISO, Full Name or alias of current webpage - see sources -
* `is_xili_curlang( $lang )` tests (returns true or false) after testing language of current webpage. Param can be a language slug (as string) or a list of slugs (as an array). Example: `is_xili_curlang( 'fr_fr' )` returns true if webpage is in french - since 2.11+

* widget for recent comments that can subselect those for current language. (a function `xiliml_recent_comments()` is also available if you want to create a template tag with resulting objects array).



= Archives selection =

Archives tags is a very complex template tag in his background and not very easy source hookable. So we decided to add few features : by adding query in vars of the function, it will be possible to display a monthly list of archives for a selected language - `wp_get_archives('..your.vars..&lang=fr_fr')` - or the current the theme language - `wp_get_archives('..your.vars..&lang=')` -. The displayed list of links will be translated and link restrited to display only archives of this language.

== Frequently Asked Questions ==

= What is "multiple languages" option introduced in latest 2.22 version ?

In previous version since first release in 2009, only one language can be assigned to a post (a custom post). Now, as permitted by taxonomy 'language', it is possible to assign a secondary language. Useful in case of quotation in another language inserted in content of post. When searching, the language form uses checkbox and not radio input. Today, only xili-language offers this feature available for document management by example.

= Developer : what is new class-xili-language-term ? =

Since WP 4.4, the WP_Term object was introduced. In similar way, **xili_language_term** object contains properties and methods to manage current languages. Some language properties or features (formerly in options) are now in term_meta. Default values come from the GP_locale object (full list from JetPack) and some from previous version of xili-language (flag, visibility,...)

= What is menus insertion point in navigation menus ? =

It is the most recent way to add multilingual menus at a location in a theme. This method uses insertion point that will live choose the menu structure according current targeted language.

[see this howto page](http://2014.extend.xiligroup.org/en/865/how-to-a-multilingual-navigation-menu/)

= XL version > 2.9.10 : In appareance/menus, I do not see the boxes of insertion point (languages list, sub-selection pages or sub-selection menus) ? =

To show insertion point metaboxes, remember to check them inside Screen Options. (top right tab near Help tab)

= What about WP Network (previous WPMU) and the trilogy ? =
[xili-language](https://wordpress.org/plugins/xili-language/), [xili-tidy-tags](https://wordpress.org/plugins/xili-tidy-tags/), [xili-dictionary](https://wordpress.org/plugins/xili-dictionary/)
Since WP 3.0-alpha, if multisite is activated, the trilogy is now compatible and will include progressively some improvements dedicaded especially for WP Network context. Future specific docs will be available for registered webmasters.

= How to see post or page ID in dashbord ? =

IDs of all types of post (post, page, CPT) are listed in Translation box (second column) under the content.

= What about rtl languages ? =

If your theme follow the WP core rules (like in Twenty Twelve) with rtl.css file, RTL languages are well displayed.

= Where can I see websites using this plugin ? =
Twenty Fifteen [2015](http://2015.extend.xiligroup.org/)

Twenty Fourteen [2014](http://2014.extend.xiligroup.org/)

Twenty Thirteen [2013](http://2013.extend.xiligroup.org/)

Responsive [responsive](http://childxili.wpmu.xilione.com/)

Twenty Twelve [2012](http://2012.wpmu.xilione.com/)

Twenty Eleven [2011](http://2011.wpmu.xilione.com/)

Twenty Ten [2010](http://multilingual.wpmu.xilione.com/)

dev.xiligroup.com [here](http://dev.xiligroup.com/?p=187 "why xili-language ?") and examples child theme below:

And as you can see in [stats](https://wordpress.org/plugins/xili-language/stats/), thousand of sites use xili-language (Stats in March 2015 are not good and must be fixed due to bad versioning sorting).

= For commercial websites, is it possible to buy support ? =
Yes, use contact form [here](http://dev.xiligroup.com/?page_id=10). Multiple commercial theme were customized for clients using multilingual CMS.

= What is gold functions ?, is it possible to buy them ? =
Some gold functions (in xilidev-libraries) are explained [here](http://dev.xiligroup.com/?p=1111) and some belong to pro services for commercial websites.
Yes, use contact form [here](http://dev.xiligroup.com/?page_id=10).

= Support Forum or contact form ? =

Effectively, prefer [forum](http://dev.xiligroup.com/?post_type=forum) to obtain some support.

= Does xiligroup provide free themes ? =

Yes, example child themes of official themes like Twenty Twelve or Twenty Thirteen - see list above in previous question.

= Is poEdit mandatory to edit .po and to build .mo file ? =
[xili-dictionary](https://wordpress.org/plugins/xili-dictionary/) avoids to use poEdit to update .mo files with contents of terms of your database (categories, ...)

= What about plugin admin settings UI localization ? =

It is simple, if you have translated the settings UI of plugin in your mother language, you send us a message through the contact form that contains the link to your site where you have add the .po and .mo files. Don't forget to fill the header of the .po file with your name and email. If all is ok, the files will be added to the xili-language WP plugins repository. Because I am not able to verify the content, you remain responsible of your translation.


= What about bookmarks and sub-selection according current language of displayed loop ? =

Since version 1.8.5, xili-language has inside his class filters and actions to permit displaying sub-selection of links and bookmarks.

* case of default widget-links : in xili-language settings, only check link categories where sub-selection is wanted.
* case of template tags : `<?php wp_list_bookmarks( array( 'lang'=>the_curlang() ) ; ?>` here this new arg named *lang* is set to the current language.

Visit links list editor settings page and set for each link the language.

= What happens if frontpage is a page ? =

The page as frontpage must have its clones in each other languages. Like for posts, if the user's browser is not in the default language, xili-language will display the page in the corresponding language if set by the editor. [home page of website dev.xiligroup.com](http://dev.xiligroup.com/) uses this feature.

= How to enlarge languages list of the dashboard ? =

Since version 2.8, the current user (author) can choose language of his dashboard. To enlarge list of available languages, you must upload the xx_YY.mo files of other localized WP installs in the folder wp-content/languages/. For example, here a list for a trilingual website (english, french, german : fr_FR.mo, de_DE.mo.
See this [codex page](http://codex.wordpress.org/WordPress_in_Your_Language) *about WP in your language* to find kit containing wanted files.

Since 2.8.8, xili-language is able to help you to download admin translation files from Automattic and GlotPress site. See the 4th tab in settings. For official embedded themes ( Twentyten and others), the .mo files can also be downloaded.

== Screenshots ==

01. An example of wp-content/themes folder and his languages sub-folder containing mo and po files.
02. Source coding extract with 'international' text in 'xiliphone' theme.
03. The plugin settings UI - first tab: the languages list and edit form to add or edit.
04. The plugin settings UI - second tab: Settings of front side (front-end) and languages behaviour.
05. The plugin settings UI - third tab: Settings for navigation menus and experts.
06. The language dashboard in post writting UI under the content textarea. For more infos and how to, visit the [wiki website](http://wiki.xiligroup.org/index.php/Xili-language_v2.5#Getting_started_:_linking_posts_with_different_language).
07. List of posts with language column and infos about linked posts. For more infos and how to, visit the [wiki website](http://wiki.xiligroup.org/index.php/Xili-language_v2.5).
08. Dashboard: Posts edit list with language sub-selection, quick edit and bulk edit available.
09. Tab in settings to manage language files.
10. xili-tidy-tags: Admin Tools UI - see this compatible plugin to group tags according languages.
11. xili-language widget: Admin widgets UI - since 0.9.9.6, "multiple" languages list widget.
12. xili-language: Widget to display recent posts in choosen target language.
13. Blogroll and bookmarks taxonomies and language sub-selection.
14. Since 2.2.0, new xili-language trilogy menu in admin bar menu to group main settings for multilingual website.
15. Since 2.12.0, Authoring settings (Custom post types, bookmarks) (rules propagation of post features) when creating translated posts are ajustable through the 5th settings tab.

== Changelog ==

Also read latest news (and infos) on this [multilingual website](http://2014.extend.xiligroup.org/en/category/news/).

= version 2.23.12 =
* (2019-06-05) Big classes splitted in traits to organize functions...
= version 2.23.04 =
* (2019-05-13) Rewriting source code with WordPress Standards PHP Code Sniffer is quite achieved. Compatibility is improved with latest PHP 5.2. and latest bundled themes. Previous files are splitted. More echoing secure functions are introduced. Today, use of Classic Editor is recommanded.

* Sources in [github repository](https://github.com/dev-xiligroup/) and visible [here](http://2017.wp.xiligroup.org) or . [here](http://2019.wp.xiligroup.org)
* first tests with twentyseventeen bundled theme [2017-xili](https://github.com/dev-xiligroup/twentyseventeen-xili)
* first tests with twentynineteen bundled theme [2019-xili](https://github.com/dev-xiligroup/twentynineteen-xili)

= version 2.22.10 =
* deeper tests with 4.9
= version 2.22.3 to 2.22.7 =
* updates locales.php (Jetpack 5.0) - new language added - preview of language properties
* fixes alias creation or update in xili-language-term
* updates locales.php (Jetpack 4.9)
* finalize multiple languages per post (custom field _multiple_language) - bulk actions
* fixes notices fixes when changing theme
* comment-form updated (according wp-includes/comment-template.php)
* now undefining a post will break all links with posts

= version 2.21.3 =
* locale file updated (JetPack 4.1.1)
* links selection improved
= version 2.21.2 =
* verified with 4.5.3 and tested with 4.6-rc1
= version 2.21.1 (2016-01-24) =
* default mo behaviour (parent priority)
= version 2.21.0 (2015-09-28) =
* widget xili-language list now has 3 styles (list, with images/flags, both images+texts) - generates css only if widget(s) active and visible
* more deep tests to recover Polylang previous install with xili-tidy-tags (1.11.2) and xili-dictionary (2.12.2+) [see github](https://github.com/dev-xiligroup/) - the process is semi-automatic and needs a special preparation (backup, activation,...) Soon docs... Ask in support form.
= version 2.20.3 (2015-09-17) =
* new option to add in widgets form, visibility rules according current language.

> this new option is not set by default because in some multilingual themes, the visibility according language is set at the sidebar level (and not at each widget level).

* fixes admin side taxonomies translations
* fixes nav-menus js (selector) - WP 4.3
* improves first default languages list
* first tests to recover Polylang previous install

= main features improved in previous releases up to 2.20.2 =
See [changelog.txt](https://plugins.svn.wordpress.org/xili-language/trunk/changelog.txt) for older changelog

= main features improved in previous releases up to 1.3.1 =
* *see readme in [previous versions](https://wordpress.org/plugins/xili-language/advanced/) to read the changelog chronology*
* …
= 0.9.0 (2009-02-28) = first public release (beta)

© 2019-06-03 - MS - dev.xiligroup.com

== Upgrade Notice ==
Please read the readme.txt before upgrading.
**As usually, don't forget to backup the database before major upgrade or testing no-current version.**
Upgrading can be easily procedeed through WP admin UI or through ftp (delete previous release folder before upgrading via ftp).
Verify you install latest version of trilogy (xili-language, xili-tidy-tags, xili-dictionary).
v2.1.0 is compatible with settings of previous release BUT introduces now a way to choose multiple navmenu locations - so revisit the settings page to confirm your previous choice or sets to new navigation way including singular links.

== More infos ==

1. [Technical infos](#1.-Technical-infos)  
   1.1. [Prerequisite](#1.1.-Prerequisite)  
   1.2. [CMS](#1.2.-CMS)  
   1.3. [Documentation for developers](#1.3.-Documentation-for-developers)  
   1.4. [More infos and docs](#1.4.-More-infos-and-docs)  
2. [Flags](#2.-Flags)  
3. [Compatibility](#3.-Compatibility)  

== 1. Technical infos ==

* REMEMBER : xili-language follows the WordPress story since more than 6 years. Initially designed for webmasters with knowledge in WP, PHP,… step by step the plugin will improved to be more and more plug and play. So don't forget to visit this [latest demo and news](http://2014.extend.xiligroup.org), see this [other demo](http://2013.extend.xiligroup.org) and [Forum](http://dev.xiligroup.com/?forum=xili-language-plugin).

== 1.1. Prerequisite ==
Verify that your theme is international (translation ready) compatible (translatable terms like `_e('the term','mythemedomaine')` and no displayed texts 'hardcoded' (example in default bundled themes of WP named *twentyfourteen* or *twentyfifteen* ).

* This latest version works with WP 4.0+ in mono or multisite.

== 1.2. CMS ==

* CMS = Content Management System
* Contains features dedicated to multilingual theme's creators and webmasters. Don't forget to read documented source code.

== 1.3. Documentation for developers ==

A [table](http://dev.xiligroup.com/?p=1432) summarizes all the technical features (widgets, template tags, functions and hooks) of this powerful plugin for personalized CMS created by webmaster.

* Provides infos about **text direction** *ltr* ou *rtl* of languages (arabic, hebraic,...) of theme and of each post in loop
* unique id for category link hook [see expert's corner posts](http://dev.xiligroup.com/?p=1045)
* hooks to define header metas or language attributes in html tag.

== 1.4. More infos and docs ==

* Other posts, articles and more descriptions [here](http://dev.xiligroup.com/xili-language/ "why xili-language ?") and [here in action](http://multilingual.wpmu.xilione.com).
* Visit also [Forum](http://dev.xiligroup.com/?forum=xili-language-plugin) to obtain more support or contribute to others by publishing reports about your experience.

== 2. Flags ==
Default flags provided in bundled child themes like TwentyFourteen-xili [2014](http://2014.extend.xiligroup.org/) came from [famfamfam](http://www.famfamfam.com/lab/icons/flags/). To be compliant to the design and look, choose your own series of flags. Be aware of size and file naming.

== 3. Compatibility ==

xili-language is compatible with the plugin [xili-dictionary](http://dev.xiligroup.com/xili-dictionary/) which is able to deliver .mo files on the fly through the WP admin UI (and .po files translatable by other translators). [xili-dictionary](http://dev.xiligroup.com/xili-dictionary/) used a specific taxonomy without adding tables in WP database.

xili-language is compatible with the plugin [xili-tidy-tags](http://dev.xiligroup.com/xili-tidy-tags/ ). xili-tidy-tags lets you create multiple group of tags. That way, you can have a tag cloud for tags in English, another cloud for French tags, another for Spanish ones, and so on. You can also use the plugin for more than multilingual blogs. Basically, you can create any group of tags you want.

xili-language is full compatible with the plugin [xilitheme-select](https://wordpress.org/plugins/xilitheme-select/ "xilitheme-select") to be used with iPhone, iPod Touch or other mobiles. Also with [xili re/un-attach media](https://wordpress.org/plugins/xili-re-un-attach-media/) !

More informations about other plugins in the website [dev.xiligroup.com](http://dev.xiligroup.com/ "xiligroup plugins") or in [WP Repository](https://wordpress.org/plugins/search.php?q=xili&sort=)

*The plugin is frequently updated*. Visit [Other versions](https://wordpress.org/plugins/xili-language/developers/).
See also the [dev.xiligroup Forum](http://dev.xiligroup.com/?forum=xili-language-plugin).

* Tags from previous readme : theme, post, plugin,posts,page,category,admin, bilingual, dictionary,.po file, widget,international, i18n, l10n, WP network, multisite, blogroll, japanese, khmer, rtl, translation-ready, bbpress, jetpack, polylang

© 2008-2019 - MS - dev.xiligroup.com
