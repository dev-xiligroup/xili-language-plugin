<?php
/**
 * Widget classes and attached functions
 * @package xili-language
 */

/*
* since 1.8.8 only the 3 widget classes here
* PREVIOUS PLUGIN DESC.
* the name of plugin was xili-language widget now it is a part of xili-language plugin.
* Add optional widgets to display list of languages in the sidebar or recent #comments and recents posts selected according current language. xili-language plugin must be activated!
* Author is dev.xiligroup.com - MS
* Author URI is http://dev.xiligroup.com
* License is GPLv2
*/

# 150306 - 2.16.3 - add xili_Widget_Categories class (with counter and walker) (must be registered in theme functions.php)
# 150228 - 2.16.2 - rewrite selected(), checked()

# 140602 - 2.13.2 - language file merged in main of plugin
# 140428 - 2.12.0 - is_preview added for customizer 3.9
# 140201 - 2.10.0 - maintenance
# 131203 - 2.9.22 - updated query in recent posts, recent comments (filter)
# 130518 - 2.8.9 - fixes constructor
# 130502 - 2.8.8 - titles of widgets
# 130317 - 2.8.6 - Type language list translatable
# 111210 - 2.4.0 - clean notices
# 111016 - 2.2.3 - clean recent posts
# 110521 - 2.1.0 - see main file
# 110410 - 2.0.0 - source cleaning
# 110306 - 1.9.1 - fixes in recent posts - only post-type display - input added to add list of type (post,video,…)
# 101104 - 1.8.4 - widget languages list with display condition
# 101101 - 1.8.3 - languages list and recent comments rewritten as extended class of WP_Widget
#
# 101026 - 1.8.1 - fixes : add a missing ending tag in options list in widget xili-language list
# 100713 - 1.7.0 - add a querytag to be compatible with new mechanism (join+where) of xili-language
# 100602 - 1.6.0 - add list of options in widget - hook possible if hook in languages_list
# 100416 - change theme_domain constant for multisite (WP3)
# 100219 - add new widget recent posts if WP >= 2.8.0
# 090606 - xili-language list widget is now multiple and more features

/*  thanks to http://blog.zen-dreams.com/ tutorial

	Copyright 2009-10  dev.xiligroup.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * Recent_Posts widget class
 * rewritten from default WP widget to suppress wp_reset_query and add sub-selection by language (current or forced)
 * @since 1.4.0
 * @updated 2.9.22 - new query
 * @updated 2.12 - is_preview added
 */
class xili_Widget_Recent_Posts extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'xili_widget_recent_entries', 'description' => __( "The most recent posts on your blog by xili-language",'xili-language').' © v. '.XILILANGUAGE_VER );
		parent::__construct('xili-recent-posts', '[©xili] ' . __('List of recent posts','xili-language'), $widget_ops);
		$this->alt_option_name = 'xili_widget_recent_entries';

		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
	}

	function widget($args, $instance) {
		if  ( method_exists($this,'is_preview') ) { // 3.9

			$cache = array();
			if ( ! $this->is_preview() ) {
				$cache = wp_cache_get( 'xili_widget_recent_posts', 'widget' );
			}
		} else {
			$cache = wp_cache_get('xili_widget_recent_posts', 'widget');
		}

		if ( !is_array($cache) )
			$cache = array();

		if ( isset($cache[$args['widget_id']]) ) {
			echo $cache[$args['widget_id']];
			return;
		}

		ob_start();
		extract($args);

		$title = apply_filters('widget_title', empty($instance['title']) ? __('Recent Posts') : $instance['title']);
		if ( !$number = (int) $instance['number'] )
			$number = 10;
		else if ( $number < 1 )
			$number = 1;
		else if ( $number > 15 )
			$number = 15;
		$show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;
		$the_lang =	$instance['the_lang'];

		if ( !isset ( $instance['post_type'] ) || $instance['post_type'] == '' ) {
			$post_type_arr = array('post');
		} else {
			$post_type_arr = explode (',', $instance['post_type'] );
		}

		if ( class_exists('xili_language') ) {
			$tmp_query = ( isset( $wp_query->query_vars[QUETAG]) ) ? $wp_query->query_vars[QUETAG] : "" ;

			if ( $the_lang == '' ) {
				// new filter 'xili_widget_posts_args' with two params: array and args 2.9.22
				$thequery = apply_filters( 'xili_widget_posts_args', array( 'posts_per_page' => $number,
					'post_type' => $post_type_arr , 'no_found_rows' => true,
					'post_status' => 'publish', 'ignore_sticky_posts' => true ), $args );
			} else {
				$lang =  ($the_lang == '*')	? the_curlang() : $the_lang ;
			 	$thequery = apply_filters( 'xili_widget_posts_args', array ( 'posts_per_page' => $number,
			 		'post_type' => $post_type_arr , 'no_found_rows' => true,
			 	 	'post_status' => 'publish',  'ignore_sticky_posts' => true,
			 	 	'tax_query'   => array(
						array(
							'field'    => 'slug',
							'taxonomy' => TAXONAME,
							'terms'    => $lang,
						),
					),
			 	 ), $args );
			}

			$r = new WP_Query($thequery);

		} else {
			$thequery = apply_filters( 'widget_posts_args', array( 'posts_per_page' => $number, 'no_found_rows' => true, 'post_status' => 'publish', 'ignore_sticky_posts' => true ) ) ;
			$r = new WP_Query($thequery);
		}

		if ($r->have_posts()) :
?>
		<?php echo $before_widget; ?>
		<?php if ( $title ) echo $before_title . $title . $after_title; ?>
		<ul>
		<?php  while ($r->have_posts()) : $r->the_post(); ?>
			<li><a href="<?php the_permalink() ?>" title="<?php echo esc_attr(get_the_title() ? get_the_title() : get_the_ID()); ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?> </a>
			<?php if ( $show_date ) : ?>
				<span class="post-date"><?php echo get_the_date(); ?></span>
			<?php endif; ?>

			</li>
		<?php endwhile; ?>
		</ul>
		<?php echo $after_widget; ?>
<?php
		wp_reset_postdata();
		endif;

		if  ( method_exists($this,'is_preview') ) { // 3.9
			if ( ! $this->is_preview() ) {
				$cache[ $args['widget_id'] ] = ob_get_flush();
				wp_cache_set( 'xili_widget_recent_posts', $cache, 'widget' );
			} else {
				ob_flush();
			}
		} else {
			$cache[$args['widget_id']] = ob_get_flush();
			wp_cache_set('xili_widget_recent_posts', $cache, 'widget');
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['the_lang'] = strtolower($new_instance['the_lang']);
		$instance['number'] = (int) $new_instance['number'];
		$instance['show_date'] = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
		$instance['post_type'] = $new_instance['post_type'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['xili_widget_recent_entries']) )
			delete_option('xili_widget_recent_entries');

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('xili_widget_recent_posts', 'widget');
	}

	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$the_lang = isset($instance['the_lang']) ? strtolower($instance['the_lang']) : '';
		if ( !isset($instance['number']) || !$number = (int) $instance['number'] )
			$number = 5;
		$show_date = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;
		$post_type = isset($instance['post_type']) ? esc_attr($instance['post_type']) : 'post';
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<?php if (class_exists('xili_language')) { global $xili_language; ?>
		<p>
			<label for="<?php echo $this->get_field_id('the_lang'); ?>"><?php _e('Language:','xili-language'); ?></label>
			<select name="<?php echo $this->get_field_name('the_lang'); ?>" id="<?php echo $this->get_field_id('the_lang'); ?>" class="widefat">
				<option value=""<?php selected( $the_lang, '' ); ?>><?php _e('All languages','xili-language'); ?></option>
				<option value="*"<?php selected( $the_lang, '*' ); ?>><?php _e('Current language','xili-language'); ?></option>
				<?php $listlanguages = get_terms_of_groups_lite ($xili_language->langs_group_id,TAXOLANGSGROUP,TAXONAME,'ASC');
					foreach ($listlanguages as $language) { ?>
					<option value="<?php echo $language->slug ?>"<?php selected( $the_lang, $language->slug ); ?>><?php _e($language->description,'xili-language'); ?></option>

					<?php } /* end */
				?>
			</select>
		</p>
		<?php } ?>
		<p>
			<label for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Post type(s):','xili-language'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>" type="text" value="<?php echo $post_type; ?>" /><br />
		</p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:','xili-language'); ?></label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /><br />
		<small><?php _e('(at most 15)', 'xili-language'); ?></small></p>
		<p><input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date?','xili-language' ); ?></label></p>
		<p><small>© xili-language v. <?php echo XILILANGUAGE_VER; ?></small></p>
<?php
	}
}

/***** new class since 1.8.3 *****/

/**
 * Recent_Comments widget class
 *
 * @since 1.8.3
 * @updated 2.9.22
 */
class xili_WP_Widget_Recent_Comments extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'xili_widget_recent_comments', 'description' => __( 'The most recent comments by xili-language','xili-language' ).' © v. '.XILILANGUAGE_VER );
		parent::__construct('xili-recent-comments', '[©xili] ' . __('Recent Comments list','xili-language'), $widget_ops);
		$this->alt_option_name = 'xili_widget_recent_comments';

		if ( is_active_widget(false, false, $this->id_base) )
			add_action( 'wp_head', array(&$this, 'recent_comments_style') );

		add_action( 'comment_post', array(&$this, 'flush_widget_cache') );
		add_action( 'edit_comment', array($this, 'flush_widget_cache') );
		add_action( 'transition_comment_status', array(&$this, 'flush_widget_cache') );
	}

	function recent_comments_style() {
		if ( ! current_theme_supports( 'widgets' ) // Temp hack #14876
			|| ! apply_filters( 'show_recent_comments_widget_style', true, $this->id_base ) )
			return;
		?>
	<style type="text/css">.recentcomments a{display:inline !important;padding:0 !important;margin:0 !important;}</style>
<?php
	}

	function flush_widget_cache() {
		wp_cache_delete('xili_widget_recent_comments', 'widget');
	}

	function widget( $args, $instance ) {
		global $comments, $comment;

		if  ( method_exists($this,'is_preview') ) { // 3.9

			$cache = array();
			if ( ! $this->is_preview() ) {
				$cache = wp_cache_get( 'xili_widget_recent_comments', 'widget' );
			}
		} else {
			$cache = wp_cache_get('xili_widget_recent_comments', 'widget');
		}

		if ( ! is_array( $cache ) )
			$cache = array();

		if ( isset( $cache[$args['widget_id']] ) ) {
			echo $cache[$args['widget_id']];
			return;
		}

 		extract($args, EXTR_SKIP);
 		$output = '';
 		$title = apply_filters('widget_title', empty($instance['title']) ? __('Recent Comments') : $instance['title']);

		if ( ! $number = (int) $instance['number'] )
 			$number = 5;
 		else if ( $number < 1 )
 			$number = 1;
		/* if xili-language plugin is activated */
		$lang = ( isset( $instance['the_lang'] ) ) ? $instance['the_lang'] : '*' ; // update from previous release
		if ( class_exists ('xili_language' ) && '' != $lang ) {
			global $xili_language;

			add_filter ( 'comments_clauses' , array($xili_language, 'xili_language_comments_clauses'), 10, 2); // line #3705 in xl
			// new filter 'xili_widget_comments_args' with two params: array and args 2.9.22
			$comments = get_comments( apply_filters( 'xili_widget_comments_args', array( 'number' => $number, 'status' => 'approve', 'post_status' => 'publish', QUETAG => $lang ), $args ) );
			remove_filter ( 'comments_clauses' , array($xili_language, 'xili_language_comments_clauses'), 10, 2) ;

		} else {
			$comments = get_comments( apply_filters( 'widget_comments_args', array( 'number' => $number, 'status' => 'approve', 'post_status' => 'publish' ) ) );

		}
		$output .= $before_widget;
		if ( $title )
			$output .= $before_title . $title . $after_title;

		$output .= '<ul id="recentcomments">';
		if ( $comments ) {
			foreach ( (array) $comments as $comment) {
				$output .=  '<li class="recentcomments">' . /* translators: comments widget: 1: comment author, 2: post link */ sprintf(_x('%1$s on %2$s', the_theme_domain()), get_comment_author_link(), '<a href="' . esc_url( get_comment_link($comment->comment_ID) ) . '">' . get_the_title($comment->comment_post_ID) . '</a>') . '</li>';
			}
 		}
		$output .= '</ul>';
		$output .= $after_widget;

		echo $output;

		if  ( method_exists($this,'is_preview') ) { // 3.9
			if ( ! $this->is_preview() ) {
				$cache[ $args['widget_id'] ] = $output;
				wp_cache_set( 'xili_widget_recent_comments', $cache, 'widget' );
			}
		} else {
			$cache[$args['widget_id']] = $output;
			wp_cache_set('xili_widget_recent_comments', $cache, 'widget');
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['the_lang'] = strtolower($new_instance['the_lang']);
		$instance['number'] = (int) $new_instance['number'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['xili_widget_recent_comments']) )
			delete_option('xili_widget_recent_comments');

		return $instance;
	}

	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$the_lang = isset($instance['the_lang']) ? strtolower($instance['the_lang']) : '*';
		$number = isset($instance['number']) ? absint($instance['number']) : 5;

?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<?php if (class_exists('xili_language')) { global $xili_language; ?>
		<p>
			<label for="<?php echo $this->get_field_id('the_lang'); ?>"><?php _e('Language:','xili-language'); ?></label>
			<select name="<?php echo $this->get_field_name('the_lang'); ?>" id="<?php echo $this->get_field_id('the_lang'); ?>" class="widefat">
				<option value=""<?php selected( $the_lang, '' ); ?>><?php _e('All languages','xili-language'); ?></option>
				<option value="*"<?php selected( $the_lang, '*' ); ?>><?php _e('Current language','xili-language'); ?></option>
				<?php $listlanguages = get_terms_of_groups_lite ($xili_language->langs_group_id,TAXOLANGSGROUP,TAXONAME,'ASC');
					foreach ($listlanguages as $language) { ?>
					<option value="<?php echo $language->slug ?>"<?php selected( $the_lang, $language->slug ); ?>><?php _e($language->description,'xili-language'); ?></option>

					<?php } /* end */
				?>
			</select>
		</p>
		<?php } ?>
		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of comments to show:'); ?></label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
		<p><small>© xili-language v. <?php echo XILILANGUAGE_VER; ?></small></p>
<?php
	}

}

/**
 * xili-language list widget class
 *
 * @since 1.8.3
 * rewritten
 */
class xili_language_Widgets extends WP_Widget {

	function __construct() {

		$widget_ops = array('classname' => 'xili-language_Widgets',
			'description' => __( "List of available languages by xili-language plugin", 'xili-language' ).' © v. '.XILILANGUAGE_VER );
		parent::__construct('xili_language_widgets',
			'[©xili] ' . __("List of languages", 'xili-language'), $widget_ops );
		$this->alt_option_name = 'xili_language_widgets_options';
	}

	function widget( $args, $instance ) {

		extract($args, EXTR_SKIP);
		$thecondition = trim( $instance['thecondition'],'!' ) ;

		if ( '' != $instance['thecondition'] && function_exists( $thecondition ) ) {
			$not = ( $thecondition == $instance['thecondition'] ) ? false : true ;
			$arr_params = ('' != $instance['theparams']) ? array(explode( ',', $instance['theparams'] )) : array();
 			$condition_ok = ($not) ? !call_user_func_array ( $thecondition, $arr_params ) : call_user_func_array ( $thecondition, $arr_params );
		} else {
 			$condition_ok = true;
 		}

 		if ( $condition_ok ) {
 			$output = '';
	 		$output .= $before_widget;
	 		$title = apply_filters( 'widget_title', $instance['title'] );
			if ( $title )
				$output .= $before_title . $title . $after_title;

			if ( function_exists( 'xili_language_list' ) ) {
				//$hidden = ( $instance['hidden'] == 'hidden' ) ? true : false ;
				$flagstyle = isset($instance['flagstyle']) ? $instance['flagstyle'] : '' ; // update
				$output .= $instance['beforelist'];
				// $output .= xili_language_list( $instance['beforeline'], $instance['afterline'], $instance['theoption'], false, $hidden );
				$output .= xili_language_list( array(
					'before' => $instance['beforeline'],
					'after' => $instance['afterline'],
					'option' => $instance['theoption'],
					'echo' => false,
					'hidden' => $instance['hidden'],
					'flagstyle' => $flagstyle,
					) );
				$output .= $instance['afterlist'];
			}
			$output .= $after_widget;
			echo $output;
 		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags($new_instance['title']);
		$instance['beforelist'] = stripslashes($new_instance['beforelist']);
		$instance['beforeline'] = stripslashes($new_instance['beforeline']);
		$instance['afterline'] = stripslashes($new_instance['afterline']);
		$instance['afterlist'] = stripslashes($new_instance['afterlist']);
		$instance['theoption'] = strip_tags(stripslashes($new_instance['theoption']));
		$instance['thecondition'] = strip_tags(stripslashes($new_instance['thecondition'])); // 1.8.4
		$instance['theparams'] = strip_tags(stripslashes($new_instance['theparams']));
		$instance['hidden'] = isset($new_instance['hidden']) ? $new_instance['hidden'] : '' ;  // 2.4.0 checkbox
		$instance['flagstyle'] = $new_instance['flagstyle']; // 2.20.3
		return $instance;
	}

	function form( $instance ) {
		global $xili_language;
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$beforelist = isset($instance['beforelist']) ? htmlentities(stripslashes($instance['beforelist'])) : "<ul class='xililanguagelist'>";
		$beforeline =  isset($instance['beforeline']) ? htmlentities(stripslashes($instance['beforeline'])) : '<li>';
		$afterline =  isset($instance['afterline']) ? htmlentities(stripslashes($instance['afterline'])): '</li>';
		$afterlist =  isset($instance['afterlist']) ? htmlentities(stripslashes($instance['afterlist'])) : '</ul>';
		$theoption =  isset($instance['theoption']) ? stripslashes($instance['theoption']) : '' ;
 		$thecondition =  isset($instance['thecondition']) ? stripslashes($instance['thecondition']) : '' ;
 		$theparams =  isset($instance['theparams']) ? stripslashes($instance['theparams']) : '' ;
 		$hidden = isset($instance['hidden']) ? $instance['hidden'] : '' ; // 1.8.9.1
 		$flagstyle = isset($instance['flagstyle']) ? $instance['flagstyle'] : '' ; // 2.20.3

	?>
	<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
	<?php

	if ( class_exists('xili_language') ) {
		if ( $xili_language->this_has_external_filter('xili_language_list')) // one external action
			$xili_language->langs_list_options = array();
		if ( has_filter('xili_language_list_options') ) {	// is list of options described
			do_action('xili_language_list_options', $theoption); // update the list of external action
		}
	}
	if ( class_exists('xili_language') && isset($xili_language->langs_list_options) && $xili_language->langs_list_options != array()) {
		echo '<br /><label for="'.$this->get_field_id('theoption').'">'.__('Type','xili-language').':';
		echo '<select name="'.$this->get_field_name('theoption').'" id="'.$this->get_field_id('theoption').'">';
		foreach ($xili_language->langs_list_options as $typeoption) {
			if ( empty($typeoption[0]) || false === strpos( $typeoption[0], 'navmenu' ) ) { // 2.0.1
				echo '<option value="'.$typeoption[0].'" '. selected( $theoption, $typeoption[0], false ) . ' >' . $typeoption[1] .'</option>';
			}
		}
		echo '</select></label>';
	} else {
			echo '<br /><label for="'.$this->get_field_id('theoption').'">'.__('Type','xili-language').': <input id="'.$this->get_field_id('theoption').'" name="'.$this->get_field_name('theoption').'" type="text" value="'.$theoption.'" /></label>';
	}

	?>
	<br /><label for="<?php echo $this->get_field_id('hidden'); ?>"><?php _e('Do not display hidden languages:','xili-language'); ?>&nbsp;<input id="<?php echo $this->get_field_id('hidden'); ?>" name="<?php echo $this->get_field_name('hidden'); ?>" type="checkbox" value="hidden" <?php checked( $hidden, 'hidden' ); ?> /></label>
	<hr />
	<label for="<?php echo $this->get_field_id('flagstyle'); ?>"><?php _e('Display style:', 'xili-language'); ?>
	<select name="<?php echo $this->get_field_name('flagstyle'); ?>" id="<?php echo $this->get_field_id('flagstyle'); ?>">
		<option value="" <?php selected( $flagstyle, '' ); ?> ><?php _e('only text', 'xili-language'); ?></option>
		<option value="flagstyle" <?php selected( $flagstyle, 'flagstyle'); ?> ><?php _e('with flag', 'xili-language'); ?></option>
		<option value="flagstyletext" <?php selected( $flagstyle, 'flagstyletext'); ?> ><?php _e('with flag and text', 'xili-language'); ?></option>
	</select></label>
	<?php
	echo '<br /><small>';
	if ( current_theme_supports( 'custom_xili_flag' ) ) {
		_e( 'The current theme supports flags, see Media and Appearance xili flag options submenu.', 'xili-language' );
	} else {
		printf( __( 'The current theme needs to support xili_flags (%s).', 'xili-language' ), 'custom_xili_flag');
	}
	echo '</small>';
	?>
	<br /><br />
	<fieldset style="margin:2px; padding:3px; border:1px solid #ccc;"><legend><?php _e('HTML tags of list','xili-language'); ?></legend>
	<label title="<?php esc_attr_e('if flag style, do not erase xililanguagelist class','xili-language'); ?>" for="<?php echo $this->get_field_id('beforelist'); ?>"><?php _e('before list','xili-language'); ?></label>:
	<input class="widefat" id="<?php echo $this->get_field_id('beforelist'); ?>" name="<?php echo $this->get_field_name('beforelist'); ?>" type="text" value="<?php echo $beforelist; ?>" />

	<label for="<?php echo $this->get_field_id('beforeline'); ?>"><?php _e('before line','xili-language'); ?></label>:
	<input class="widefat" id="<?php echo $this->get_field_id('beforeline'); ?>" name="<?php echo $this->get_field_name('beforeline'); ?>" type="text" value="<?php echo $beforeline; ?>" />

	<label for="<?php echo $this->get_field_id('afterline'); ?>"><?php _e('after line','xili-language'); ?></label>:
	<input class="widefat" id="<?php echo $this->get_field_id('afterline'); ?>" name="<?php echo $this->get_field_name('afterline'); ?>" type="text" value="<?php echo $afterline; ?>" />

	<label for="<?php echo $this->get_field_id('afterlist'); ?>"><?php _e('after list','xili-language'); ?></label>:
	<input class="widefat" id="<?php echo $this->get_field_id('afterlist'); ?>" name="<?php echo $this->get_field_name('afterlist'); ?>" type="text" value="<?php echo $afterlist; ?>" /></fieldset>
	<fieldset style="margin:2px; padding:3px; border:1px solid #ccc;" >
	<label for="<?php echo $this->get_field_id('thecondition'); ?>"><?php _e('Condition','xili-language'); ?></label>:
	<input class="widefat" id="<?php echo $this->get_field_id('thecondition'); ?>" name="<?php echo $this->get_field_name('thecondition'); ?>" type="text" value="<?php echo $thecondition; ?>" />
	( <input id="<?php echo $this->get_field_id('theparams'); ?>" name="<?php echo $this->get_field_name('theparams'); ?>" type="text" value="<?php echo $theparams; ?>" /> )
	</fieldset>
	<p><small>© xili-language v. <?php echo XILILANGUAGE_VER; ?></small></p>
	<?php
	}
}

/**
 * Insert style in <head>
 * not in dropdown
 *
 * @since 2.20.3
 *
 */
function xili_language_list_widget_style () {
	if ( is_active_widget( false, false, 'xili_language_widgets') ) {
		//
		$insert_style = array();
		$styletext = array();
		if ( $widgets_xll = get_option( 'widget_xili_language_widgets' ) ) {
			foreach ( $widgets_xll as $key => $one_xll ) {
				if ( isset( $one_xll['flagstyle'] ) && in_array( $one_xll['flagstyle'],  array ('flagstyle', 'flagstyletext' ) ) ) {
					$insert_style[] = $key;
					$styletext[$key] = ( $one_xll['flagstyle'] == 'flagstyletext' ) ? true : false ;
				}
			}
		}
		if ( $insert_style != array() ) {
			//
			$style_lines = '<!--- Xili-language widgets css -->' . "\n";
			$style_lines .= '<style type="text/css">';

			$style_lines .= '.widget.xili-language_Widgets {margin-bottom:10px}'. "\n"; // depends theme
			$style_lines .= '.xililanguagelist {list-style: none; margin:0}'. "\n";
			$style_lines .= '.xililanguagelist li a {display:block;}'. "\n";
			$style_lines .= '</style>';

			/**
			 * Filter whether widgets need commun style css/js in head.
			 *
			 * @since 2.20.3
			 *
			 * @param int       ID of the active widget
			 */
			echo apply_filters ( 'xili_language_widgets_head', $style_lines );
			foreach ( $insert_style as $key ) {
				/**
 				 * Filter action whether a widget needs flag css.
				 *
				 * @since 2.20.3
				 *
				 * @param int       ID of the active widget
				 */
				do_action ( 'xili_language_widgets_list_head', $key, $styletext[$key] ); // see example below
			}
		}
	}
}
add_action( 'wp_head', 'xili_language_list_widget_style', 13 ); // after menu style


/**
 * Insert style in <head> for one instanciation
 *
 *
 * @since 2.20.3
 *
 */
function xili_language_widgets_head_test ( $key,  $styletext ) {
	$widget_uid = 'xili_language_widgets-' . $key;
	$name = is_active_widget( false, $widget_uid, 'xili_language_widgets');
	if ( $name ) {
		printf('<!--- Xili-language list widget ID %s in %s -->', $key, $name ); // only active and visible in sidebar with $name
		$style_lines = '<style type="text/css">';
		if ( $styletext ) {
			$style_lines .= '#' . $widget_uid . ' .xililanguagelist li[class*="lang-"] a {text-indent:30px;}'. "\n";
			$style_lines .= '#' . $widget_uid . ' .xililanguagelist li[class*="lang-"] a {width:100%; height:100%; background-position: left 1px; }'. "\n"; // cancel image size
			$style_lines .= '#' . $widget_uid . ' .xililanguagelist li[class*="lang-"] a:hover {background-position: left 2px !important; }'. "\n";
			$style_lines .= '#' . $widget_uid . ' .xililanguagelist li {display:list-item;}'. "\n";
		} else {
			$style_lines .= '#' . $widget_uid . ' .xililanguagelist li[class*="lang-"] a {text-indent:-9999px;}'. "\n";
			$style_lines .= '#' . $widget_uid . ' .xililanguagelist li[class*="lang-"] a:hover {background-position: center 2px !important;}' ."\n";
			$style_lines .= '#' . $widget_uid . ' .xililanguagelist li {display:inline-block;}'. "\n";
		}
		$style_lines .= '</style>';
		echo $style_lines;
	}
}
add_action( 'xili_language_widgets_list_head', 'xili_language_widgets_head_test', 10, 2 );

/**
 * Categories widget class with counter according current language
 * not in dropdown
 *
 * @since 2.8.0
 * @since XL 2.16.3
 */
class xili_Widget_Categories extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'classname' => 'widget_categories', 'description' => __( "A multilingual list or dropdown of categories.", 'xili-language' ) );
		parent::__construct('xl_categories', '[©xili] ' . __('Categories', 'xili-language'), $widget_ops);
	}

	public function widget( $args, $instance ) {

		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Categories', 'xili-language' ) : $instance['title'], $instance, $this->id_base );

		$c = ! empty( $instance['count'] ) ? '1' : '0';
		$h = ! empty( $instance['hierarchical'] ) ? '1' : '0';
		$d = ! empty( $instance['dropdown'] ) ? '1' : '0';

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$cat_args = array('orderby' => 'name', 'show_count' => $c, 'hierarchical' => $h);

		if ( $d ) {
			$cat_args['show_option_none'] = __('Select Category', 'xili-language');

			/**
			 * Filter the arguments for the Categories widget drop-down.
			 *
			 * @since 2.8.0
			 *
			 * @see wp_dropdown_categories()
			 *
			 * @param array $cat_args An array of Categories widget drop-down arguments.
			 */
			wp_dropdown_categories( apply_filters( 'widget_categories_dropdown_args', $cat_args ) );
?>

<script type='text/javascript'>
/* <![CDATA[ */
	var dropdown = document.getElementById("cat");
	function onCatChange() {
		if ( dropdown.options[dropdown.selectedIndex].value > 0 ) {
			location.href = "<?php echo home_url(); ?>/?cat="+dropdown.options[dropdown.selectedIndex].value;
		}
	}
	dropdown.onchange = onCatChange;
/* ]]> */
</script>

<?php
		} else {
?>
		<ul>
<?php
		$cat_args['title_li'] = '';
		$cat_args['walker'] = new XL_tax_walker; // multilingual taxonomy customized walker
		/**
		 * Filter the arguments for the Categories widget.
		 *
		 * @since 2.8.0
		 *
		 * @param array $cat_args An array of Categories widget options.
		 */
		wp_list_categories( apply_filters( 'widget_categories_args', $cat_args ) );
?>
		</ul>
<?php
		}

		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = !empty($new_instance['count']) ? 1 : 0;
		$instance['hierarchical'] = !empty($new_instance['hierarchical']) ? 1 : 0;
		$instance['dropdown'] = !empty($new_instance['dropdown']) ? 1 : 0;

		return $instance;
	}

	public function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = esc_attr( $instance['title'] );
		$count = isset($instance['count']) ? (bool) $instance['count'] :false;
		$hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
		$dropdown = isset( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>"<?php checked( $dropdown ); ?> />
		<label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e( 'Display as dropdown', 'xili-language' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $count ); ?> />
		<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Show post counts in this language', 'xili-language' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php echo $this->get_field_name('hierarchical'); ?>"<?php checked( $hierarchical ); ?> />
		<label for="<?php echo $this->get_field_id('hierarchical'); ?>"><?php _e( 'Show hierarchy', 'xili-language' ); ?></label></p>
<?php
	}

}

/**
 * XL_tax_walker including counter according current languages
 * here, only count is modified
 *
 * @since XL 2.16.3
 *
 */
class XL_tax_walker extends Walker_Category {

	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		/** This filter is documented in wp-includes/category-template.php */
		$cat_name = apply_filters(
			'list_cats',
			esc_attr( $category->name ),
			$category
		);

		$link = '<a href="' . esc_url( get_term_link( $category ) ) . '" ';
		if ( $args['use_desc_for_title'] && ! empty( $category->description ) ) {
			/**
			 * Filter the category description for display.
			 *
			 * @since 1.2.0
			 *
			 * @param string $description Category description.
			 * @param object $category    Category object.
			 */
			$link .= 'title="' . esc_attr( strip_tags( apply_filters( 'category_description', $category->description, $category ) ) ) . '"';
		}

		$link .= '>';
		$link .= $cat_name . '</a>';

		if ( ! empty( $args['feed_image'] ) || ! empty( $args['feed'] ) ) {
			$link .= ' ';

			if ( empty( $args['feed_image'] ) ) {
				$link .= '(';
			}

			$link .= '<a href="' . esc_url( get_term_feed_link( $category->term_id, $category->taxonomy, $args['feed_type'] ) ) . '"';

			if ( empty( $args['feed'] ) ) {
				$alt = ' alt="' . sprintf(__( 'Feed for all posts filed under %s' ), $cat_name ) . '"';
			} else {
				$alt = ' alt="' . $args['feed'] . '"';
				$name = $args['feed'];
				$link .= empty( $args['title'] ) ? '' : $args['title'];
			}

			$link .= '>';

			if ( empty( $args['feed_image'] ) ) {
				$link .= $name;
			} else {
				$link .= "<img src='" . $args['feed_image'] . "'$alt" . ' />';
			}
			$link .= '</a>';

			if ( empty( $args['feed_image'] ) ) {
				$link .= ')';
			}
		}

		if ( ! empty( $args['show_count'] ) ) {
			$xili_count = xili_cached_taxonomy_count ( $args['taxonomy'], $category->slug, $category->term_id );
			$link .= ' (' . number_format_i18n( $xili_count ) . ')';
		}
		if ( 'list' == $args['style'] ) {
			$output .= "\t<li";
			$class = 'cat-item cat-item-' . $category->term_id;
			if ( ! empty( $args['current_category'] ) ) {
				$_current_category = get_term( $args['current_category'], $category->taxonomy );
				if ( $category->term_id == $args['current_category'] ) {
					$class .=  ' current-cat';
				} elseif ( $category->term_id == $_current_category->parent ) {
					$class .=  ' current-cat-parent';
				}
			}
			$output .=  ' class="' . $class . '"';
			$output .= ">$link\n";
		} else {
			$output .= "\t$link<br />\n";
		}
	}

}

/**
 * uses and cache (via transient) count of posts linked with a term of a taxonomy in current language
 *
 * @since XL 2.16.3
 *
 */
function xili_cached_taxonomy_count ( $taxonomy, $slug, $term_id ) {
	global $xili_language;
	$curlang = xili_curlang();
	$transient_name = 'xili_count_' . $taxonomy . '_' . $term_id . '_' . $curlang ;
	if ( false === ( $xili_count = get_transient( $transient_name ) ) ) {
    // It wasn't there, so regenerate the data and save the transient
     	$xili_count = $xili_language->count_posts_in_taxonomy_and_lang ( $taxonomy, $slug, $curlang ); // post by default here
     	set_transient( $transient_name, $xili_count, 12 * HOUR_IN_SECONDS );
	}
	return $xili_count;
}

/**
 * reset cache (via transient) count of posts linked with a term of a taxonomy in current language
 * from: do_action( 'set_object_terms', $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids );
 * @since XL 2.16.3
 *
 */
function xili_reset_transient_count ( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
  if ( $taxonomy != 'category' ) return;
  $languages = xili_get_listlanguages();
  foreach ($languages as $curlang) {
  	foreach ($terms as $term_id ){
  		$transient_name = 'xili_count_' . $taxonomy . '_' . $term_id . '_' . $curlang->slug ;
  		delete_transient( $transient_name );
  	}
  }

}
add_action( 'set_object_terms', 'xili_reset_transient_count', 10, 6);


?>