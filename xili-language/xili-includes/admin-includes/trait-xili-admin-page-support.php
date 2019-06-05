<?php
namespace Xili_Admin;

/**
 * @package Xili-Language
 * @subpackage admin
 * functions for support interface
 * @since  2.23 traits files
 */

trait Xili_Admin_Page_Support {


	/**
	 * Support and info
	 * @since 2.4.1
	 */
	public function on_load_page_support() {
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'wp-lists' );
			wp_enqueue_script( 'postbox' );

			add_meta_box( 'xili-language-sidebox-info', __( 'Info', 'xili-language' ), array( &$this, 'on_sidebox_info_content' ), $this->thehook3, 'side', 'core' );

			$this->insert_news_pointer( 'languages_support' ); // news pointer 2.6.2
	}

	/**
	 * Support page
	 *
	 * @since 2.4.1
	 */
	public function languages_support() {
		global $wp_version;
		$msg = 0;
		$themessages = array( 'ok' );
		$emessage = '';
		$action = '';
		if ( isset( $_POST['sendmail'] ) ) {
			$action = 'sendmail';
		}
		switch ( $action ) {

			case 'sendmail': // 1.8.5
				check_admin_referer( 'xili-plugin-sendmail' );

				$this->xili_settings['url'] = ( isset( $_POST['urlenable'] ) ) ? $_POST['urlenable'] : '';
				$this->xili_settings['theme'] = ( isset( $_POST['themeenable'] ) ) ? $_POST['themeenable'] : '';
				$this->xili_settings['wplang'] = ( isset( $_POST['wplangenable'] ) ) ? $_POST['wplangenable'] : '';
				$this->xili_settings['version-wp'] = ( isset( $_POST['versionenable'] ) ) ? $_POST['versionenable'] : '';
				$this->xili_settings['permalink_structure'] = ( isset( $_POST['permalink_structure'] ) ) ? $_POST['permalink_structure'] : '';
				$this->xili_settings['xiliplug'] = ( isset( $_POST['xiliplugenable'] ) ) ? $_POST['xiliplugenable'] : '';
				$this->xili_settings['webmestre-level'] = $_POST['webmestre']; // 2.8.4
				update_option( 'xili_language_settings', $this->xili_settings );
				$contextual_arr = array();
				if ( 'enable' == $this->xili_settings['url'] ) {
					$contextual_arr[] = 'url=[ ' . get_bloginfo( 'url' ) . ' ]';
				}
				if ( isset( $_POST['onlocalhost'] ) ) {
					$contextual_arr[] = 'url=local';
				}
				if ( 'enable' == $this->xili_settings['theme'] ) {
					$contextual_arr[] = 'theme=[ ' . get_option( 'stylesheet' ) . ' ]';
				}
				if ( 'enable' == $this->xili_settings['wplang'] ) {
					$contextual_arr[] = 'WPLANG=[ ' . $this->get_wplang() . ' ]';
				}
				if ( isset( $_POST['xililanguageslist'] ) ) {
					$contextual_arr[] = 'Languages List=[ ' . implode( ',', $this->langs_slug_name_array ) . ' ]';
				}
				if ( 'enable' == $this->xili_settings['version-wp'] ) {
					$contextual_arr[] = 'WP version=[ ' . $wp_version . ' ]';
				}
				if ( 'enable' == $this->xili_settings['permalink_structure'] ) {
					$contextual_arr[] = 'Permalinks=[ ' . get_option( 'permalink_structure' ) . ' ]';
					if ( isset( $xl_permalinks_rules ) ) {
						$contextual_arr[] = 'XL lang perma';
					}
				}
				if ( 'enable' == $this->xili_settings['xiliplug'] ) {
					$contextual_arr[] = 'xiliplugins=[ ' . $this->check_other_xili_plugins() . ' ]';
				}

				$contextual_arr[] = $this->xili_settings['webmestre-level']; // 1.9.1

				$headers = 'From: xili-language plugin page <' . get_bloginfo( 'admin_email' ) . '>' . "\r\n";
				if ( '' != $_POST['ccmail'] ) {
					$headers .= 'Cc: <' . $_POST['ccmail'] . '>' . "\r\n";
					$headers .= 'Reply-To: <' . $_POST['ccmail'] . '>' . "\r\n";
				}
				$headers .= '\\';
				$message = 'Message sent by: ' . get_bloginfo( 'admin_email' ) . "\n\n";
				$message .= 'Subject: ' . $_POST['subject'] . "\n\n";
				$message .= 'Topic: ' . $_POST['thema'] . "\n\n";
				$message .= 'Content: ' . $_POST['mailcontent'] . "\n\n";
				$message .= 'Checked contextual infos: ' . implode( ', ', $contextual_arr ) . "\n\n";
				$message .= "This message was sent by webmaster in xili-language plugin settings page.\n\n";
				$message .= "\n\n";
				if ( preg_match( '/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,6}$/i', $_POST['ccmail'] ) && preg_match( '/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,6}$/i', get_bloginfo( 'admin_email' ) ) ) {
					$result = wp_mail( 'contact@xiligroup.com', $_POST['thema'] . ' from xili-language v.' . XILILANGUAGE_VER . ' plugin settings page.', $message, $headers );
					$message = __( 'Email sent.', 'xili-language' );
					$msg = 1;
					$sent = ( $result ) ? __( 'WP Mail OK', 'xili-language' ) : __( 'Issue in wp_mail or smtp config', 'xili-language' );
					/* translators: */
					$emessage = sprintf( esc_html__( 'Thanks for your email. A copy was sent to %1$s (%2$s)', 'xili-language' ), $_POST['ccmail'], $sent );
				} else {
					$msg = 2;
					/* translators: */
					$emessage = sprintf( esc_html__( 'Issue in your email. NOT sent to Cc: %1$s or the return address %2$s is not good !', 'xili-language' ), $_POST['ccmail'], get_bloginfo( 'admin_email' ) );
				}
				break;
		}
		$themessages[1] = __( 'Email sent.', 'xili-language' );
		$themessages[2] = __( 'Email not sent. Please verify email field', 'xili-language' );

		add_meta_box( 'xili-language-box-mail', __( 'Mail & Support', 'xili-language' ), array( &$this, 'on_box_mail_content' ), $this->thehook3, 'normal', 'low' );

		$data = array(
			'action' => $action,
			'emessage' => $emessage,
		);

		?>
		<div id="xili-language-support" class="wrap columns-2 minwidth">

			<h2><?php esc_html_e( 'Languages', 'xili-language' ); ?></h2>
			<h3 class="nav-tab-wrapper">
			<?php $this->set_tabs_line(); ?>
			</h3>

			<?php if ( 0 != $msg ) { ?>
			<div id="message" class="updated fade"><p><?php echo $themessages[ $msg ]; ?></p></div>
			<?php } ?>
			<form name="support" id="support" method="post" action="options-general.php?page=language_support">
				<?php wp_nonce_field( 'xili-language-support' ); ?>
				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
				<p class="width23 boldtext">
				<?php printf( __( "For support, before sending an email with the form below, don't forget to visit the readme as %1\$shere%2\$s and the links listed in contextual help tab (on top left).", 'xili-language' ), '<a href="' . $this->repositorylink . '" target="_blank">', '</a>' ); ?>
				</p>
				<?php $this->setting_form_content( $this->thehook3, $data ); ?>
			</form>
		</div>
		<?php
		$this->setting_form_js( $this->thehook3 );
	}


	public function check_other_xili_plugins() {
		$list = array();
		//if ( class_exists( 'xili_language' ) ) $list[] = 'xili-language' ;
		if ( class_exists( 'xili_tidy_tags' ) ) {
			$list[] = 'xili-tidy-tags';
		}
		if ( class_exists( 'xili_dictionary' ) ) {
			$list[] = 'xili-dictionary';
		}
		if ( class_exists( 'xilithemeselector' ) ) {
			$list[] = 'xilitheme-select';
		}
		if ( function_exists( 'insert_a_floom' ) ) {
			$list[] = 'xili-floom-slideshow';
		}
		if ( class_exists( 'xili_postinpost' ) ) {
			$list[] = 'xili-postinpost';
		}
		return implode( ', ', $list );
	}

	public function on_box_mail_content( $data ) {
		extract( $data );
		global $wp_version;
		$theme = ( isset( $this->xili_settings['theme'] ) ) ? $this->xili_settings['theme'] : '';
		$wplang = ( isset( $this->xili_settings['wplang'] ) ) ? $this->xili_settings['wplang'] : '';
		$xiliplug = ( isset( $this->xili_settings['xiliplug'] ) ) ? $this->xili_settings['xiliplug'] : '';
		if ( '' != $emessage ) {
			?>
			<h4><?php esc_html_e( 'Note:', 'xili-language' ); ?></h4>
			<p><strong><?php echo $emessage; ?></strong></p>
		<?php } ?>
		<fieldset class="mailto"><legend><?php esc_html_e( 'Mail to dev.xiligroup', 'xili-language' ); ?></legend><p class="textright">
		<label for="ccmail"><?php esc_html_e( 'Cc: (Reply to:)', 'xili-language' ); ?>
		<input class="widefat width23" id="ccmail" name="ccmail" type="text" value="<?php bloginfo( 'admin_email' ); ?>" /></label><br /><br /></p><p class="textleft">
		<?php if ( false === strpos( get_bloginfo( 'url' ), 'local' ) ) { ?>
			<label for="urlenable">
				<input type="checkbox" id="urlenable" name="urlenable" value="enable" <?php checked( ( isset( $this->xili_settings['url'] ) && 'enable' == $this->xili_settings['url'] ), true, true ); ?> />&nbsp;<?php bloginfo( 'url' ); ?>
			</label><br />
		<?php } else { ?>
			<input type="hidden" name="onlocalhost" id="onlocalhost" value="localhost" />
		<?php } ?>
		<br /><em><?php esc_html_e( 'When checking and giving detailled infos, support will be better !', 'xili-language' ); ?></em><br />
		<label for="themeenable">
			<input type="checkbox" id="themeenable" name="themeenable" value="enable" <?php checked( $theme, 'enable', true ); ?> />&nbsp;<?php echo 'Theme name= ' . get_option( 'stylesheet' ); ?>
		</label><br />
		<?php
		if ( '' != $this->get_wplang() ) {
			?>
			<label for="wplangenable">
				<input type="checkbox" id="wplangenable" name="wplangenable" value="enable" <?php checked( $wplang, 'enable', true ); ?> />&nbsp;<?php echo 'WPLANG= ' . $this->get_wplang(); ?>
			</label><br />
		<?php
		}
		$xililanguageslist = implode( ', ', $this->langs_slug_name_array );

		?>
		<label for="xililanguageslist">
			<input type="checkbox" id="xililanguageslist" name="xililanguageslist" value="enable" />&nbsp;<?php echo 'Languages list= ' . $xililanguageslist; ?>
		</label><br />
		<label for="versionenable">
			<input type="checkbox" id="versionenable" name="versionenable" value="enable" <?php checked( isset( $this->xili_settings['version-wp'] ) && 'enable' == $this->xili_settings['version-wp'], true, true ); ?> />&nbsp;<?php echo 'WP version: ' . $wp_version; ?>
		</label><br />
		<?php
		if ( get_option( 'permalink_structure' ) ) {
		// 2.10.0 -
		?>
		<label for="versionenable">
			<input type="checkbox" id="permalink_structure" name="permalink_structure" value="enable" <?php checked( isset( $this->xili_settings['permalink_structure'] ) && 'enable' == $this->xili_settings['permalink_structure'], true, true ); ?> />&nbsp;<?php echo 'Permalink structure: <small>' . get_option( 'permalink_structure' ) . '</small>'; ?>
		</label><br />
		<?php } ?>
		<br />
		<?php
		$list = $this->check_other_xili_plugins();
		if ( '' != $list ) {
			?>
		<label for="xiliplugenable">
			<input type="checkbox" id="xiliplugenable" name="xiliplugenable" value="enable" <?php checked( $xiliplug, 'enable', true ); ?> />&nbsp;<?php printf( __( 'Other xili plugins = %s', 'xili-language' ), $list ); ?>
		</label><br /><br />
		<?php } ?>
		</p><p class="textright">
		<label for="webmestre"><?php esc_html_e( 'Type of webmaster:', 'xili-language' ); ?>
		<select name="webmestre" id="webmestre" class="width23">
			<?php
			if ( ! isset( $this->xili_settings['webmestre-level'] ) ) {
				$this->xili_settings['webmestre-level'] = '?';
			}
			?>
			<option value="?" <?php selected( $this->xili_settings['webmestre-level'], '?' ); ?>><?php esc_html_e( 'Define your experience as webmaster…', 'xili-language' ); ?></option>
			<option value="newbie" <?php selected( $this->xili_settings['webmestre-level'], 'newbie' ); ?>><?php esc_html_e( 'Newbie in WP', 'xili-language' ); ?></option>
			<option value="wp-php" <?php selected( $this->xili_settings['webmestre-level'], 'wp-php' ); ?>><?php esc_html_e( 'Good knowledge in WP and few in php', 'xili-language' ); ?></option>
			<option value="wp-php-dev" <?php selected( $this->xili_settings['webmestre-level'], 'wp-php-dev' ); ?>><?php esc_html_e( 'Good knowledge in WP, CMS and good in php', 'xili-language' ); ?></option>
			<option value="wp-plugin-theme" <?php selected( $this->xili_settings['webmestre-level'], 'wp-plugin-theme' ); ?>><?php esc_html_e( 'WP theme and /or plugin developper', 'xili-language' ); ?></option>
		</select></label><br /><br />
		<label for="subject"><?php esc_html_e( 'Subject:', 'xili-language' ); ?>
		<input class="widefat width23" id="subject" name="subject" type="text" value="" /></label>
		<select name="thema" id="thema" class="width23">
			<option value="" ><?php esc_html_e( 'Choose topic...', 'xili-language' ); ?></option>
			<option value="Message" ><?php esc_html_e( 'Message', 'xili-language' ); ?></option>
			<option value="Question" ><?php esc_html_e( 'Question', 'xili-language' ); ?></option>
			<option value="Encouragement" ><?php esc_html_e( 'Encouragement', 'xili-language' ); ?></option>
			<option value="Support need" ><?php esc_html_e( 'Support need', 'xili-language' ); ?></option>
		</select>
		<textarea class="widefat width45" rows="5" cols="20" id="mailcontent" name="mailcontent"><?php esc_html_e( 'Your message here…', 'xili-language' ); ?></textarea>
		</p></fieldset>
		<p>
		<?php esc_html_e( 'Before send the mail, be accurate, check the infos to inform support and complete textarea. A copy (Cc:) is sent to webmaster email (modify it if needed).', 'xili-language' ); ?>
		</p>
		<?php wp_nonce_field( 'xili-plugin-sendmail' ); ?>
		<div class='submit'>
		<input id='sendmail' name='sendmail' type='submit' tabindex='6' value="<?php esc_html_e( 'Send email', 'xili-language' ); ?>" /></div>

		<div class="clearb1">&nbsp;</div><br/>
		<?php
	}

	public function print_styles_options_language_support() {

		echo "<!---- xl options css 4 ----->\n";
		echo '<style type="text/css" media="screen">' . "\n";
		echo ".red-alert {color:red;}\n";
		echo ".minwidth {min-width:1000px !important;}\n";
		echo ".textleft {text-align:left;}\n";
		echo ".textright {text-align:right;}\n";
		echo ".fullwidth { width:97%; }\n";
		echo ".width23 { width:70% !important; }\n";
		echo ".width45 { width:80% !important; }\n";
		echo ".boldtext {font-size:1.15em;}\n";
		echo ".mailto {margin:2px; padding:12px 100px 12px 30px; border:1px solid #ccc; }\n";
		echo "</style>\n";

		if ( $this->exists_style_ext && 'on' == $this->xili_settings['external_xl_style'] ) {
			wp_enqueue_style( 'xili_language_stylesheet' );
		}
	}


}
