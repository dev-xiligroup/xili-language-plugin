<?php
namespace Xili_Admin;

/**
 * @package Xili-Language
 * @subpackage admin
 * functions for menus builder interface
 * @since  2.23 traits files
 */

trait Xili_Admin_Menus_Builder {

	/**
	 * Navigation menu: new ways to insert language list via menu builder screen
	 *
	 * @since 2.8.8
	 *
	 */
	public function add_language_nav_menu_meta_boxes() {
		add_meta_box(
			'insert-xl-list',
			/* translators: */
			sprintf( __( '%s Languages list', 'xili-language' ), '[©xili]' ),
			array( $this, 'language_nav_menu_link' ),
			'nav-menus',
			'side',
			'high'
		);
	}

	// called by above filter
	public function language_nav_menu_link() {
			// div id in submit below
		global $_nav_menu_placeholder, $nav_menu_selected_id;
		$_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? $_nav_menu_placeholder - 1 : -1;
			?>
		<div id="posttype-xllist" class="posttypediv">
			<div id="tabs-panel-xllist" class="tabs-panel tabs-panel-active">
				<ul id ="xllist-checklist" class="categorychecklist form-no-clear">

					<?php
					foreach ( $this->langs_list_options as $typeoption ) {
						if ( false !== strpos( $typeoption[0], 'navmenu' ) ) {
							// list according available types of menu
					?>
					<li>
						<label title="<?php echo $typeoption[2]; ?>" class="menu-item-title">
							<input type="radio" class="menu-item-checkbox" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" value="-1">&nbsp;<?php _e( $typeoption[1], 'xili-language' ); ?>
						</label>
						<input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="custom">
						<input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="<?php echo $this->insertion_point_box_title; ?>">
						<input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" value="<?php echo $this->insertion_point_dummy_link; ?>">
						<input type="hidden" class="menu-item-attr-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-attr-title]" value="<?php echo $typeoption[2]; ?>">
						<input type="hidden" class="menu-item-classes" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-classes]" value="xl-list <?php echo $typeoption[0]; ?>">
						<input type="hidden" class="menu-item-aria-current" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-aria-current]" value= "*" >
					</li>
					<?php
						}
					}
					?>
				</ul>
			</div>
			<p class='description'><?php esc_html_e( 'Check to decide what type of languages menu. Only an insertion point will be placed inside the menu. The content of the language list will be automatically made according navigation rules and contexts.', 'xili-language' ); ?></p>
			<p class="button-controls">
				<span class="list-controls">

				</span>
				<span class="add-to-menu">
					<input type="submit" <?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-post-type-menu-item" id="submit-posttype-xllist">
					<span class="spinner"></span>
				</span>
			</p>
		</div>
		<?php
	}



	// prepares javascript to modify the languages list menu item
	// modified for block - 2019-05-24
	public function admin_enqueue_menu_script() {
		$screen = get_current_screen();
		$suffix = defined( 'WP_DEBUG' ) && WP_DEBUG ? '.dev' : '.min';
		if ( $this->is_block_editor_active( true ) ) {
				wp_enqueue_script( 'xl-block-editor', plugin_dir_url( XILILANGUAGE_PLUGIN_FILE ) . 'js/xili-block-editor' . $suffix . '.js', array( 'wp-api-fetch' ), XILILANGUAGE_VER );
		} elseif ( 'nav-menus' == $screen->base ) {

			wp_enqueue_script( 'xllist_nav_menu', plugin_dir_url( XILILANGUAGE_PLUGIN_FILE ) . 'js/nav-menu' . $suffix . '.js', array( 'jquery' ), XILILANGUAGE_VER );

			$data = array();
			$data['strings'][0] = $this->insertion_point_box_title;
			$data['strings'][1] = esc_js( __( 'The languages list will be inserted here and :', 'xili-language' ) );
			$data['strings'][2] = esc_js( __( ' (Hidden input items below will be used for live menu generating.)', 'xili-language' ) );

			$data['strings'][3] = $this->insertion_point_box_title_page;
			$data['strings'][4] = esc_js( __( 'The list of a sub-selection of pages will be inserted here according current language of webpage.', 'xili-language' ) );
			$data['strings'][5] = esc_js( __( 'This is an experimental feature.', 'xili-language' ) );

			$data['strings'][6] = $this->insertion_point_box_title_menu;
			$data['strings'][7] = esc_js( __( 'One menu from this list of menus will be inserted here according current language of displayed webpage.', 'xili-language' ) );
			$data['strings'][8] = esc_js( __( 'This is an experimental powerful feature for dynamic menus.', 'xili-language' ) );
			$data['strings'][9] = $this->menu_slug_sep; // 2.12.2
			foreach ( $this->langs_ids_array as $slug => $id ) {
				$data['strings'][10][ $id ] = $slug;
			}
			// send all these data to javascript
			wp_localize_script( 'xllist_nav_menu', 'xili_data', $data );
		}

	}

	/**
	 * Navigation menu: new ways to insert pages sub-selection via menu builder screen
	 *
	 * @since 2.9.10
	 *
	 */
	public function add_sub_select_page_nav_menu_meta_boxes() {
		add_meta_box(
			'insert-xlspage-list',
			sprintf( __( '%s Pages selection', 'xili-language' ), '[©xili]' ),
			array( $this, 'sub_select_page_nav_menu_link' ),
			'nav-menus',
			'side',
			'high'
		);
	}
	// called by above filter
	public function sub_select_page_nav_menu_link() {
			// div id in submit below
		global $_nav_menu_placeholder, $nav_menu_selected_id;
		$_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? $_nav_menu_placeholder - 1 : -1;
			?>
		<div id="posttype-xlsplist" class="posttypediv">
				<div id="tabs-panel-xlsplist" class="tabs-panel tabs-panel-active">
					<ul id ="xllist-checklist" class="categorychecklist form-no-clear">

						<li>
							<label title="" class="menu-item-title">
								<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" value="-1">&nbsp;
							</label>
							<input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="custom">
							<input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="<?php echo $this->insertion_point_box_title_page; ?>">
							<input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" value="<?php echo $this->insertion_point_dummy_link_page; ?>" >
							<input type="text" class="menu-item-attr-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-attr-title]" value="include=">
							<input type="hidden" class="menu-item-classes" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-classes]" value="xlsplist">
						</li>


					</ul>
				</div>
				<p class='description'><?php esc_html_e( 'Check and add to menu an insertion point of sub-selection of pages (during displaying menu, a sub-selection will be done according current language. Args is like in function wp_list_pages. Example: <code>include=11,15</code>', 'xili-language' ); ?></p>
				<p class="button-controls">
					<span class="list-controls">

					</span>
					<span class="add-to-menu">
						<input type="submit" <?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-post-type-menu-item" id="submit-posttype-xlsplist">
						<span class="spinner"></span>
					</span>
				</p>
			</div>
		<?php
	}

	/**
	 * Navigation menu: new ways to insert another menu from menu builder and set language selection
	 *
	 * @since 2.9.20
	 *
	 */
	public function add_sub_select_nav_menu_meta_boxes() {
		add_meta_box(
			'insert-xlmenus-list',
			/* translators: */
			sprintf( __( '%s Menus selection', 'xili-language' ), '[©xili]' ),
			array( $this, 'sub_select_nav_menus' ),
			'nav-menus',
			'side',
			'high'
		);
	}

	// called by above filter - now saves slugs
	public function sub_select_nav_menus() {
		global $_nav_menu_placeholder, $nav_menu_selected_id;
		$_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? $_nav_menu_placeholder - 1 : -1;
		?>
		<div id="posttype-xlmenulist" class="posttypediv">
			<div id="tabs-panel-xlmenulist" class="tabs-panel tabs-panel-active tabs-panel-menu">
				<ul id ="xlmenulist-checklist" class="categorychecklist form-no-clear">

					<?php

					// load menus without location
					$locations = get_registered_nav_menus();
					$menu_locations = get_nav_menu_locations();
					$nav_menus = wp_get_nav_menus(
						array(
							'orderby' => 'name',
						)
					);
					// reduce to those without location
					$nav_menus_wo = array();
					$already_located = array(); // to avoid multiple items 2.9.22
					foreach ( $locations as $_location => $_name ) {
						if ( isset( $menu_locations[ $_location ] ) && $menu_locations[ $_location ] > 0 ) {
							$already_located[] = $menu_locations[ $_location ];
						}
					}
					foreach ( $nav_menus as $menu ) {
						if ( array() != $already_located && ! in_array( $menu->term_id, $already_located ) ) {
							$nav_menus_wo[] = $menu;
						}
					}

					if ( $nav_menus_wo ) { // now saves slug - lang index as term_id
						echo '<li>';
						$listlanguages = $this->get_listlanguages();
						foreach ( $listlanguages as $language ) {
							echo '<span class="lang-menu-name" > ' . xl_esc_html__( $language->description, 'xili-language' ) . '</span>';
							?>
							&nbsp;<select name="menu" id="menu-wlid-<?php echo $language->term_id; ?>" class="menu-wo">
								<option value="0" ><?php esc_attr_e( 'Select a menu...', 'xili-language' ); ?></option>
								<?php foreach ( $nav_menus_wo as $_nav_menu ) { ?>
										<option value = "<?php echo esc_attr( $_nav_menu->slug ); ?>" >
											<?php echo wp_html_excerpt( $_nav_menu->name, 40, '&hellip;' ); ?>
										</option>
								<?php } ?>
							</select><br />
						<?php } ?>

						</li><li>
							<label title="<?php echo $language->name; ?>" class="menu-item-title menu-item-title-ok">
								<input type="checkbox" class="menu-item-checkbox menu-check-ok" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" value="-1">&nbsp;&nbsp;<?php _e( 'Check before adding to menu' ); ?>
							</label>
							<input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="custom">
							<input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="<?php echo $this->insertion_point_box_title_menu; ?>">
							<input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" value="<?php echo $this->insertion_point_dummy_link_menu; ?>">
							<input type="hidden" class="menu-item-attr-title menu-list-index" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-attr-title]" value="">
							<input type="hidden" class="menu-item-classes menu-list-menu" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-classes]" value="">
						</li>
					<?php } else { ?>
						<li>
							<label class="menu-item-title">
								<?php esc_html_e( 'No menu is available. We must create them without assigning a theme location', 'xili-language' ); ?>
							</label>
						</li>
					<?php } ?>

				</ul>
			</div>
			<p class='description'><?php esc_html_e( 'Select to assign a language to a menu container without location. Only an insertion point will be placed inside the Menu Structure. The content of the insertion will be automatically made according navigation language rules and contexts. After selection, check and click “Add to Menu” - button below.', 'xili-language' ); ?></p>
			<p class="button-controls">
				<span class="list-controls">

				</span>
				<?php if ( $nav_menus_wo ) { ?>
				<span class="add-to-menu">
					<input type="submit" <?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-post-type-menu-item" id="submit-posttype-xlmenulist">
					<span class="spinner"></span>
				</span>
				<?php } ?>
			</p>
		</div>
			<script type="text/javascript">
	//<![CDATA[
			jQuery(document).ready( function($) {
				$(".lang-menu-name").css ({display:'inline-block', width:'55px' });
				$(".lang-menu-name").css ( "font-size", "12px");
				$(".tabs-panel-menu").css ({background:'#f5f5f5'});
				$(".menu-item-title-ok").css ({ display:'inline-block', margin:'7px 0 0'});
				$(".menu-check-ok").change(function() {
					if ( $(this).attr('checked') ) {
						var langindex = new Array();
						var menuvalue = new Array();
						var message = '<?php echo esc_js( __( 'Assign at least one menu to a language !!!', 'xili-language' ) ); ?>';
						var total = '';
						$('.menu-wo').each(function() {

							langindex.push( $(this).attr('id') );
							menuvalue.push( $(this).find('option:selected').val() );
							total = total + $(this).find('option:selected').val();
						});
						$('.menu-list-index').val( langindex.join('-') ) ;
						$('.menu-list-menu').val( 'xlmenuslug<?php echo $this->menu_slug_sep; ?>' + menuvalue.join('<?php echo $this->menu_slug_sep; ?>') ) ;

						if ( total == '' ) {
							$(this).attr('checked', false );
							alert ( message ) ;
						}
					}
				});
			});
			//]]>
		</script>
		<?php

	}

	/**
	*
	* called by /js/nav-menu.xxx.js when displaying results in nav menu settings
	* @since 2.9.20
	*
	* @updated 2.12.2 - 2.23.14 20250320 sanitize
	*/
	public function ajax_get_menu_infos() {

		$menu_slug = ( isset( $_POST['menu_slug'] ) && ! empty( $_POST['menu_slug'] ) ) ? sanitize_text_field($_POST['menu_slug']) : 'no-xl-menu';

		$term_data = term_exists( $menu_slug, 'nav_menu' );
		$menu_id = ( is_array( $term_data ) ) ? $term_data['term_id'] : 0;
		$menu_obj = wp_get_nav_menu_object( (int) $menu_id );
		if ( $menu_obj ) {
			echo '<strong>' . wp_html_excerpt( $menu_obj->name, 40, '&hellip;' ) . '</strong>';
		} else {
			/* translators: */
			echo '<strong>' . sprintf( __( 'Warning: Unavailable menu with slug %s', 'xili-language' ), $menu_slug ) . '</strong>';
		}

		die();
	}

}
