<?php
//

/**
 * called when wp_locale is declared when plugin_loaded
 *
 * @since 2.4
 * @updated 2.12 - translate() used
 *
 */

function xiliml_declare_xl_wp_locale() {
	/**
	 * special class extending wp_locale only for theme locale
	 *
	 * to work needs that locale datas and translation (a copy of those in core languages) will be in theme's po,mo files
	 *
	 * @since 2.4.0
	 *
	 */
	if ( ! class_exists( 'Xl_WP_Locale' ) ) {
		class Xl_WP_Locale extends WP_locale {

			function __construct() {
				parent::__construct();
			}

			function init() {

				$theme_domain = the_theme_domain();

				// The Weekdays
				$this->weekday[0] = /* translators: weekday */ translate( 'Sunday', $theme_domain );
				$this->weekday[1] = /* translators: weekday */ translate( 'Monday', $theme_domain );
				$this->weekday[2] = /* translators: weekday */ translate( 'Tuesday', $theme_domain );
				$this->weekday[3] = /* translators: weekday */ translate( 'Wednesday', $theme_domain );
				$this->weekday[4] = /* translators: weekday */ translate( 'Thursday', $theme_domain );
				$this->weekday[5] = /* translators: weekday */ translate( 'Friday', $theme_domain );
				$this->weekday[6] = /* translators: weekday */ translate( 'Saturday', $theme_domain );

				// The first letter of each day. The _%day%_initial suffix is a hack to make
				// sure the day initials are unique.
				$this->weekday_initial[translate('Sunday', $theme_domain)]		= /* translators: one-letter abbreviation of the weekday */ translate('S_Sunday_initial', $theme_domain);
				$this->weekday_initial[translate('Monday', $theme_domain)]		= /* translators: one-letter abbreviation of the weekday */ translate('M_Monday_initial', $theme_domain);
				$this->weekday_initial[translate('Tuesday', $theme_domain)]	= /* translators: one-letter abbreviation of the weekday */ translate('T_Tuesday_initial', $theme_domain);
				$this->weekday_initial[translate('Wednesday', $theme_domain)]	= /* translators: one-letter abbreviation of the weekday */ translate('W_Wednesday_initial', $theme_domain);
				$this->weekday_initial[translate('Thursday', $theme_domain)]	= /* translators: one-letter abbreviation of the weekday */ translate('T_Thursday_initial', $theme_domain);
				$this->weekday_initial[translate('Friday', $theme_domain)]		= /* translators: one-letter abbreviation of the weekday */ translate('F_Friday_initial', $theme_domain);
				$this->weekday_initial[translate('Saturday', $theme_domain)]	= /* translators: one-letter abbreviation of the weekday */ translate('S_Saturday_initial', $theme_domain);

				foreach ($this->weekday_initial as $weekday_ => $weekday_initial_) {
					$this->weekday_initial[$weekday_] = preg_replace('/_.+_initial$/', '', $weekday_initial_);
				}

				// Abbreviations for each day.
				$this->weekday_abbrev[translate('Sunday', $theme_domain)]		= /* translators: three-letter abbreviation of the weekday */ translate('Sun', $theme_domain);
				$this->weekday_abbrev[translate('Monday', $theme_domain)]		= /* translators: three-letter abbreviation of the weekday */ translate('Mon', $theme_domain);
				$this->weekday_abbrev[translate('Tuesday', $theme_domain)]		= /* translators: three-letter abbreviation of the weekday */ translate('Tue', $theme_domain);
				$this->weekday_abbrev[translate('Wednesday', $theme_domain)]	= /* translators: three-letter abbreviation of the weekday */ translate('Wed', $theme_domain);
				$this->weekday_abbrev[translate('Thursday', $theme_domain)]	= /* translators: three-letter abbreviation of the weekday */ translate('Thu', $theme_domain);
				$this->weekday_abbrev[translate('Friday', $theme_domain)]		= /* translators: three-letter abbreviation of the weekday */ translate('Fri', $theme_domain);
				$this->weekday_abbrev[translate('Saturday', $theme_domain)]	= /* translators: three-letter abbreviation of the weekday */ translate('Sat', $theme_domain);

				// The Months
				$this->month['01'] = /* translators: month name */ translate('January', $theme_domain);
				$this->month['02'] = /* translators: month name */ translate('February', $theme_domain);
				$this->month['03'] = /* translators: month name */ translate('March', $theme_domain);
				$this->month['04'] = /* translators: month name */ translate('April', $theme_domain);
				$this->month['05'] = /* translators: month name */ translate('May', $theme_domain);
				$this->month['06'] = /* translators: month name */ translate('June', $theme_domain);
				$this->month['07'] = /* translators: month name */ translate('July', $theme_domain);
				$this->month['08'] = /* translators: month name */ translate('August', $theme_domain);
				$this->month['09'] = /* translators: month name */ translate('September', $theme_domain);
				$this->month['10'] = /* translators: month name */ translate('October', $theme_domain);
				$this->month['11'] = /* translators: month name */ translate('November', $theme_domain);
				$this->month['12'] = /* translators: month name */ translate('December', $theme_domain );

				// Abbreviations for each month. Uses the same hack as above to get around the
				// 'May' duplication.
				$this->month_abbrev[translate('January', $theme_domain)] = /* translators: three-letter abbreviation of the month */ translate('Jan_January_abbreviation', $theme_domain);
				$this->month_abbrev[translate('February', $theme_domain)] = /* translators: three-letter abbreviation of the month */ translate('Feb_February_abbreviation', $theme_domain);
				$this->month_abbrev[translate('March', $theme_domain)] = /* translators: three-letter abbreviation of the month */ translate('Mar_March_abbreviation', $theme_domain);
				$this->month_abbrev[translate('April', $theme_domain)] = /* translators: three-letter abbreviation of the month */ translate('Apr_April_abbreviation', $theme_domain);
				$this->month_abbrev[translate('May', $theme_domain)] = /* translators: three-letter abbreviation of the month */ translate('May_May_abbreviation', $theme_domain);
				$this->month_abbrev[translate('June', $theme_domain)] = /* translators: three-letter abbreviation of the month */ translate('Jun_June_abbreviation', $theme_domain);
				$this->month_abbrev[translate('July', $theme_domain)] = /* translators: three-letter abbreviation of the month */ translate('Jul_July_abbreviation', $theme_domain);
				$this->month_abbrev[translate('August', $theme_domain)] = /* translators: three-letter abbreviation of the month */ translate('Aug_August_abbreviation', $theme_domain);
				$this->month_abbrev[translate('September', $theme_domain)] = /* translators: three-letter abbreviation of the month */ translate('Sep_September_abbreviation', $theme_domain);
				$this->month_abbrev[translate('October', $theme_domain)] = /* translators: three-letter abbreviation of the month */ translate('Oct_October_abbreviation', $theme_domain);
				$this->month_abbrev[translate('November', $theme_domain)] = /* translators: three-letter abbreviation of the month */ translate('Nov_November_abbreviation', $theme_domain);
				$this->month_abbrev[translate('December',$theme_domain)] = /* translators: three-letter abbreviation of the month */ translate('Dec_December_abbreviation', $theme_domain);

				foreach ($this->month_abbrev as $month_ => $month_abbrev_) {
					$this->month_abbrev[$month_] = preg_replace('/_.+_abbreviation$/', '', $month_abbrev_);
				}

				// The Meridiems
				$this->meridiem['am'] = translate('am', $theme_domain);
				$this->meridiem['pm'] = translate('pm', $theme_domain);
				$this->meridiem['AM'] = translate('AM', $theme_domain);
				$this->meridiem['PM'] = translate('PM', $theme_domain);

				// Numbers formatting
				// See http://php.net/number_format

				/* translators: $thousands_sep argument for http://php.net/number_format, default is , */
				$trans = translate('number_format_thousands_sep', $theme_domain);
				$this->number_format['thousands_sep'] = ('number_format_thousands_sep' == $trans) ? ',' : $trans;

				/* translators: $dec_point argument for http://php.net/number_format, default is . */
				$trans = translate('number_format_decimal_point', $theme_domain);
				$this->number_format['decimal_point'] = ('number_format_decimal_point' == $trans) ? '.' : $trans;

				// test version // 2.7.1
				global $wp_version;
				if ( version_compare( $wp_version, '3.4', '<' ) ) {
					// Import global locale vars set during inclusion of $locale.php.
					foreach( (array) $this->locale_vars as $var ) {
						if ( isset( $GLOBALS[ $var ]) ){
							$this->$var = $GLOBALS[ $var ];
						}
					}
				} else {
					// Set text direction.
					if ( isset( $GLOBALS['text_direction'] ) ) {
						$this->text_direction = $GLOBALS['text_direction'];
						/* translators: 'rtl' or 'ltr'. This sets the text direction for WordPress. */
					} elseif ( 'rtl' == translate_with_gettext_context( 'ltr', 'text direction', $theme_domain ) ) {
						$this->text_direction = 'rtl';
					}
				}
			}
		}
	}
}
