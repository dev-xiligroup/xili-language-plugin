# xili-language-plugin
multilingual plugin for WordPress since 2009
The first using custom taxonomy introduced in WP 2.3 (here named language).

The readme file is now one of the files of plugin opened for GitHub contributors to improve this content in english.
When displayed, in [WordPress repository](https://wordpress.org/plugins/xili-language/), the text is divided in tabs.

The other files are available for comments or fixes. Only the version in [WordPress xili-language repository](https://wordpress.org/plugins/xili-language/) is for use.

## Latest notes 
### 20190430 - Customizer, in admin side, to work well, needs two settings : 
1. the language in general settings must be “english” core wp language
2. you can change admin side language in top right menu of dashboard screen.
( if not pomo script gives fatal error and customizer shows blank screen )

## Master Branch

Since 2019-05-10, master branch contains dev version 2.23.x +. Contains latest dev with rewritten sources code with [WP Coding Standards](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards).
 
**REQUIRES PHP 7.1+ !**

**pre-tested with WP 5.2**

Since 2016-12-14, master branch contains dev version 2.22.1+ with some futures new features and fixes for WP 4.6 to now (April 2018) WP 4.9.5.

## before-rw branch

2019-05-08 - This branch contains the source code before rewriting in Master Branch with WP Coding Standards and Php Code Sniffer (< 2.23)

## Gutenberg - need latest version

Since Gutenberg 2.7 (April 18th 2018), the (private) taxonomy language metabox on right side of post edit remains hidden as expected. The big metabox (translations) from xili-language is now well displayed.

## Taxonomy language

Language taxonomy settings are saved in term metas ( need WP 4.4 ) and a new "language" object (*) is created to contain all language features. This new “language” description allows now to use (option) several languages per post around a main language.

...

(*) thanks to JetPack to maintain a locale.php file with a huge list of languages of the world.

## Preliminary infos:

1. This readme.txt follow the rules of developers described [here](https://wordpress.org/plugins/about/)
1. Do not forgot to [validate](https://wordpress.org/plugins/about/validator/) the text before commit.

Thanks for your contribution.

M.
