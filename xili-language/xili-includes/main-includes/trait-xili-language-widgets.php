<?php
namespace Xili_Main;

/**
 * @package  xili-language
 * @subpackage main class
 * functions for widgets
 */
trait Xili_Language_Widgets {

	/**
	 * in archives default widget - create sub-selection if isset curlang - see default-widget.php
	 *
	 * @since 2.16.3
	 *
	 */
	public function xiliml_widget_archives_args( $args ) {
		if ( $this->curlang ) {
			$args[ QUETAG ] = $this->curlang;
		}
		return $args;
	}

	/**
	 * now active in same file as class xili_language
	 * Widgets registration after classes rewritten
	 *
	 * @since 1.8.8
	 * @since 2.16.4 - now Widget_categories and more precise enabling
	 */
	public function add_new_widgets() {
		foreach ( $this->xili_settings['specific_widget'] as $key => $value ) {
			if ( 'enabled' == $value['value'] ) {
				register_widget( $key );
			}
		}
	}

	/*
	 * visibility of the widget according to the rule and the current language
	 * don't display according rules (show, hidden and current language)
	 *
	 * @since 2.20.3
	 *
	 * @param array $instance widget settings
	 * @param object $widget WP_Widget object
	 * @return bool|array false if we hide the widget, unmodified $instance otherwise
	 */
	public function widget_display_callback( $instance, $widget ) {
		if ( empty( $this->xili_settings['widget_visibility'] ) ) {
			return $instance;
		}

		if ( ! empty( $instance['xl_show'] ) ) {
			if ( 'show' == $instance['xl_show'] ) {
				$false_rule = false;
				$instance_rule = $instance;
			} else {
				$false_rule = $instance;
				$instance_rule = false;
			}
		} else {
			$false_rule = false;
			$instance_rule = $instance;
		}
		return ! empty( $instance['xl_lang'] ) && $instance['xl_lang'] != $this->curlang ? $false_rule : $instance_rule;
	}

}
