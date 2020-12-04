<?php
/**
 * ------------------------------------------------------------------------------
 * Plugin Name: Page Index
 * Description: Displays Index of Pages using page-index Shortcode; uses the Parent Page field to determine content of index or one of supplied pageid or slug parameters.
 * Version: 1.5.3
 * Author: azurecurve
 * Author URI: https://development.azurecurve.co.uk/classicpress-plugins/
 * Plugin URI: https://development.azurecurve.co.uk/classicpress-plugins/page-index/
 * Text Domain: page-index
 * Domain Path: /languages
 * ------------------------------------------------------------------------------
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.html.
 * ------------------------------------------------------------------------------
 */

// Prevent direct access.
if (!defined('ABSPATH')){
	die();
}

// include plugin menu
require_once(dirname( __FILE__).'/pluginmenu/menu.php');
add_action('admin_init', 'azrcrv_create_plugin_menu_pi');

// include update client
require_once(dirname(__FILE__).'/libraries/updateclient/UpdateClient.class.php');

/**
 * Setup registration activation hook, actions, filters and shortcodes.
 *
 * @since 1.0.0
 *
 */

// add actions
add_action('admin_menu', 'azrcrv_pi_create_admin_menu');
add_action('admin_enqueue_scripts', 'azrcrv_pi_load_admin_style');
add_action('admin_post_azrcrv_pi_save_options', 'azrcrv_pi_save_options');
add_action('plugins_loaded', 'azrcrv_pi_load_languages');


// add filters
add_filter('plugin_action_links', 'azrcrv_pi_add_plugin_action_link', 10, 2);
add_filter('the_posts', 'azrcrv_pi_check_for_shortcode', 10, 2);
add_filter('codepotent_update_manager_image_path', 'azrcrv_pi_custom_image_path');
add_filter('codepotent_update_manager_image_url', 'azrcrv_pi_custom_image_url');

// add shortcodes
add_shortcode('page-index', 'azrcrv_pi_display_page_index');
add_shortcode('PAGE-INDEX', 'azrcrv_pi_display_page_index');

/**
 * Load language files.
 *
 * @since 1.0.0
 *
 */
function azrcrv_pi_load_languages() {
    $plugin_rel_path = basename(dirname(__FILE__)).'/languages';
    load_plugin_textdomain('page-index', false, $plugin_rel_path);
}

/**
 * Check if shortcode on current page and then load css and jqeury.
 *
 * @since 1.0.0
 *
 */
function azrcrv_pi_check_for_shortcode($posts){
    if (empty($posts)){
        return $posts;
	}
	
	
	// array of shortcodes to search for
	$shortcodes = array(
						'page-index','PAGE-INDEX'
						);
	
    // loop through posts
    $found = false;
    foreach ($posts as $post){
		// loop through shortcodes
		foreach ($shortcodes as $shortcode){
			// check the post content for the shortcode
			if (has_shortcode($post->post_content, $shortcode)){
				$found = true;
				// break loop as shortcode found in page content
				break 2;
			}
		}
	}
 
    if ($found){
		// as shortcode found call functions to load css and jquery
        azrcrv_pi_load_css();
    }
    return $posts;
}

/**
 * Load CSS.
 *
 * @since 1.0.0
 *
 */
function azrcrv_pi_load_css(){
	wp_enqueue_style('azrcrv-pi', plugins_url('assets/css/style.css', __FILE__), '', '1.0.0');
}

/**
 * Custom plugin image path.
 *
 * @since 1.3.0
 *
 */
function azrcrv_pi_custom_image_path($path){
    if (strpos($path, 'azrcrv-page-index') !== false){
        $path = plugin_dir_path(__FILE__).'assets/pluginimages';
    }
    return $path;
}

/**
 * Custom plugin image url.
 *
 * @since 1.3.0
 *
 */
function azrcrv_pi_custom_image_url($url){
    if (strpos($url, 'azrcrv-page-index') !== false){
        $url = plugin_dir_url(__FILE__).'assets/pluginimages';
    }
    return $url;
}

/**
 * Get options including defaults.
 *
 * @since 1.4.0
 *
 */
function azrcrv_pi_get_option($option_name){
 
	$defaults = array(
						"color" => "",
						"background" => "",
						'timeline-integration' => 0,
						'timeline-signifier' => '*',
						'flags-integration' => 0,
						'flag-width' => 16,
						'flag-position' => 'after',
						'icons-integration' => 0,
						'icon-position' => 'after',
						'icon-visited' => '',
					);

	$options = get_option($option_name, $defaults);

	$options = wp_parse_args($options, $defaults);

	return $options;

}

/**
 * Add Page Index action link on plugins page.
 *
 * @since 1.0.0
 *
 */
function azrcrv_pi_add_plugin_action_link($links, $file){
	static $this_plugin;

	if (!$this_plugin){
		$this_plugin = plugin_basename(__FILE__);
	}

	if ($file == $this_plugin){
		$settings_link = '<a href="'.admin_url('admin.php?page=azrcrv-pi').'"><img src="'.plugins_url('/pluginmenu/images/Favicon-16x16.png', __FILE__).'" style="padding-top: 2px; margin-right: -5px; height: 16px; width: 16px;" alt="azurecurve" />'.esc_html__('Settings' ,'page-index').'</a>';
		array_unshift($links, $settings_link);
	}

	return $links;
}

/**
 * Add to menu.
 *
 * @since 1.0.0
 *
 */
function azrcrv_pi_create_admin_menu(){
	//global $admin_page_hooks;
	
	add_submenu_page("azrcrv-plugin-menu"
						,esc_html__("Page Index Settings", "page-index")
						,esc_html__("Page Index", "page-index")
						,'manage_options'
						,'azrcrv-pi'
						,'azrcrv_pi_display_options');
}

/**
 * Load css and jquery for page index admin page.
 *
 * @since 1.5.0
 *
 */
function azrcrv_pi_load_admin_style(){
    wp_register_style('page-index-css', plugins_url('assets/css/admin.css', __FILE__), false, '1.0.0');
    wp_enqueue_style( 'page-index-css' );
	
	wp_enqueue_script("page-index-admin-js", plugins_url('assets/jquery/jquery.js', __FILE__), array('jquery', 'jquery-ui-core', 'jquery-ui-tabs'));
}

/**
 * Display Settings page.
 *
 * @since 1.0.0
 *
 */
function azrcrv_pi_display_options(){
	if (!current_user_can('manage_options')){
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'page-index'));
    }
	
	// Retrieve plugin configuration options from database
	$options = azrcrv_pi_get_option('azrcrv-pi');
	?>
	<div id="azrcrv-pi-general" class="wrap">
		<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
		
		<?php if(isset($_GET['settings-updated'])){ ?>
			<div class="notice notice-success is-dismissible">
				<p><strong><?php esc_html_e('Settings have been saved.', 'page-index'); ?></strong></p>
			</div>
		<?php } ?>
		
		<form method="post" action="admin-post.php">
			<input type="hidden" name="action" value="azrcrv_pi_save_options" />
			<input name="page_options" type="hidden" value="azrcrv-pi" />
			
			<!-- Adding security through hidden referrer field -->
			<?php wp_nonce_field('azrcrv-pi', 'azrcrv-pi-nonce'); ?>
			
			<h2 class="nav-tab-wrapper nav-tab-wrapper-azrcrv-pi">
				<a class="nav-tab nav-tab-active" data-item=".tabs-1" href="#tabs-1"><?php _e('Default Settings', 'page-index') ?></a>
				<a class="nav-tab" data-item=".tabs-2" href="#tabs-2"><?php _e('Integration', 'page-index') ?></a>
				<input type="submit" style="float: left; margin: 6px; margin-bottom: 3px; float: right;  " value="<?php _e('Save Settings', 'page-index'); ?>" class="button-primary" id="submit" name="submit" />
			</h2>
			
			<div>
				<div class="azrcrv_pi_tabs tabs-1">
					
					<label for="explanation">
						<p>
							<?php printf(esc_html__('%s and %s custom fields can be applied to a page to change the color of a specific pages appearance in the page index.', 'page-index'), 'azrcrv-pi-color', 'azrcrv-pi-background'); ?>
						</p>
						<p>
							<?php esc_html_e('If the options are blank then the defaults in the plugin\'s CSS will be used.', 'page-index'); ?>
						</p>
					</label>
					
					<table class="form-table">
						
						<tr><th scope="row"><label for="color"><?php esc_html_e('Color', 'page-index'); ?></label></th><td>
							<input type="text" name="color" value="<?php echo esc_html(stripslashes($options['color'])); ?>" class="large-text" />
							<p class="description"><?php esc_html_e('Set default color (e.g. #FFF)', 'page-index'); ?></p>
						</td></tr>
						
						<tr><th scope="row"><label for="background"><?php esc_html_e('Background Color', 'page-index'); ?></label></th><td>
							<input type="text" name="background" value="<?php echo esc_html(stripslashes($options['background'])); ?>" class="large-text" />
							<p class="description"><?php esc_html_e('Set default background color (e.g. #000)', 'page-index'); ?></p>
						</td></tr>
						
						<tr><th scope="row"><label for="width"><?php esc_html_e('Width', 'page-index'); ?></label></th><td>
							<input type="text" name="width" value="<?php echo esc_html(stripslashes($options['width'])); ?>" class="large-text" />
							<p class="description"><?php esc_html_e('Set default width (e.g. 48.4%)', 'page-index'); ?></p>
						</td></tr>
						
						<tr><th scope="row"><label for="height"><?php esc_html_e('Height', 'page-index'); ?></label></th><td>
							<input type="text" name="height" value="<?php echo esc_html(stripslashes($options['height'])); ?>" class="large-text" />
							<p class="description"><?php esc_html_e('Set default height (e.g. 100px)', 'page-index'); ?></p>
						</td></tr>
						
						<tr><th scope="row"><label for="lineheight"><?php esc_html_e('Line Height', 'page-index'); ?></label></th><td>
							<input type="text" name="lineheight" value="<?php echo esc_html(stripslashes($options['lineheight'])); ?>" class="large-text" />
							<p class="description"><?php esc_html_e('Set default line height (e.g. 100px)', 'page-index'); ?></p>
						</td></tr>
						
						<tr><th scope="row"><label for="margin"><?php esc_html_e('Margin', 'page-index'); ?></label></th><td>
							<input type="text" name="margin" value="<?php echo esc_html(stripslashes($options['margin'])); ?>" class="large-text" />
							<p class="description"><?php esc_html_e('Set default margin (e.g. 4px)', 'page-index'); ?></p>
						</td></tr>
						
						<tr><th scope="row"><label for="padding"><?php esc_html_e('Padding', 'page-index'); ?></label></th><td>
							<input type="text" name="padding" value="<?php echo esc_html(stripslashes($options['padding'])); ?>" class="large-text" />
							<p class="description"><?php esc_html_e('Set default padding (e.g. 3px 2px 3px 2px)', 'page-index'); ?></p>
						</td></tr>
						
						<tr><th scope="row"><label for="textalign"><?php esc_html_e('Text Align', 'page-index'); ?></label></th><td>
							<input type="text" name="textalign" value="<?php echo esc_html(stripslashes($options['textalign'])); ?>" class="large-text" />
							<p class="description"><?php esc_html_e('Set default textalign (e.g. center or left)', 'page-index'); ?></p>
						</td></tr>
						
						<tr><th scope="row"><label for="fontweight"><?php esc_html_e('Font-Weight', 'page-index'); ?></label></th><td>
							<input type="text" name="fontweight" value="<?php echo esc_html(stripslashes($options['fontweight'])); ?>" class="large-text" />
							<p class="description"><?php esc_html_e('Set default fontweight (e.g. 700)', 'page-index'); ?></p>
						</td></tr>
					
					</table>
				</div>
				
				<div class="azrcrv_pi_tabs invisible tabs-2">
				
					<label for="explanation">
						<p>
							<?php printf(esc_html__('When integration with %s from %s has been enabled, add a custom field called %s containing the required flag id to a page.', 'page-index'),'<a href="https://development.azurecurve.co.uk/classicpress-plugins/flags/">Flags</a>', '<a href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve</a>', '<strong>Flag</strong>'); ?>
						</p>
						<p>
							<?php printf(esc_html__('When integration with %s from %s has been enabled, add a custom field called %s containing the required icon id to a page.', 'page-index'),'<a href="https://development.azurecurve.co.uk/classicpress-plugins/icons/">Icons</a>', '<a href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve</a>', '<strong>Icon</strong>'); ?>
						</p>
					</label>
					
					<table class="form-table">
						
						<tr>
							<th scope="row">
								<label for="flags-integration">
									<?php printf(esc_html__('Integrate with %s from %s', 'page-index'), '<a href="https://development.azurecurve.co.uk/classicpress-plugins/flags/">Flags</a>', '<a href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve</a>'); ?>
								</label>
							</th>
							<td>
								<?php
									if (azrcrv_pi_is_plugin_active('azrcrv-flags/azrcrv-flags.php')){ ?>
										<label for="flags-integration"><input name="flags-integration" type="checkbox" id="flags-integration" value="1" <?php checked('1', $options['flags-integration']); ?> /><?php printf(esc_html__('Enable integration with %s from %s?', 'page-index'), 'Flags', 'azurecurve'); ?></label>
									<?php }else{
										printf(esc_html__('%s from %s not installed/activated.', 'page-index'), 'Flags', 'azurecurve');
									}
									?>
							</td>
						</tr>
						
						<tr><th scope="row"><?php esc_html_e('Flag width?', 'flags'); ?></th><td>
							<fieldset><legend class="screen-reader-text"><span><?php esc_html_e('Flag width', 'flags'); ?></span></legend>
								<label for="flag-width"><input type="number" name="flag-width" class="small-text" value="<?php echo $options['flag-width']; ?>" />px</label>
							</fieldset>
						</td></tr>
						
						<tr><th scope="row"><label for="flag-position"><?php esc_html_e('Flag Position', 'page-index'); ?></label></th><td>
							<?php esc_html_e('Place flag', 'page-index'); ?> <select name="flag-position">
								<option value="before" <?php if($options['flag-position'] == 'before'){ echo ' selected="selected"'; } ?>><?php esc_html_e('Before', 'page-index'); ?></option>
								 <option value="after" <?php if($options['flag-position'] == 'after'){ echo ' selected="selected"'; } ?>><?php esc_html_e('After', 'page-index'); ?></option>
							</select>
						</td></tr>
						
					</table>
					
					<table class="form-table">
						
						<tr>
							<th scope="row">
								<label for="icons-integration">
									<?php printf(esc_html__('Integrate with %s from %s', 'page-index'), '<a href="https://development.azurecurve.co.uk/classicpress-plugins/icons/">Icons</a>', '<a href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve</a>'); ?>
								</label>
							</th>
							<td>
								<?php
									if (azrcrv_pi_is_plugin_active('azrcrv-icons/azrcrv-icons.php')){ ?>
										<label for="icons-integration"><input name="icons-integration" type="checkbox" id="icons-integration" value="1" <?php checked('1', $options['icons-integration']); ?> /><?php printf(esc_html__('Enable integration with %s from %s?', 'page-index'), 'Icons', 'azurecurve'); ?></label>
									<?php }else{
										printf(esc_html__('%s from %s not installed/activated.', 'page-index'), 'Icons', 'azurecurve');
									}
									?>
							</td>
						</tr>
						
						<tr><th scope="row"><label for="icon-position"><?php esc_html_e('Icon Position', 'page-index'); ?></label></th><td>
							<?php esc_html_e('Place flag', 'page-index'); ?> <select name="icon-position">
								<option value="before" <?php if($options['icon-position'] == 'before'){ echo ' selected="selected"'; } ?>><?php esc_html_e('Before', 'page-index'); ?></option>
								<option value="after" <?php if($options['icon-position'] == 'after'){ echo ' selected="selected"'; } ?>><?php esc_html_e('After', 'page-index'); ?></option>
							</select>
						</td></tr>
					
					</table>
					
					<table class="form-table">
					
						<tr>
							<th scope="row">
								<label for="timeline-integration"><?php printf(__('Integrate with %s from %s', 'page-index'), '<a href="https://development.azurecurve.co.uk/classicpress-plugins/timelines/">Timelines</a>', '<a href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve</a>'); ?></label></th>
							<td>
								<?php
									if (azrcrv_pi_is_plugin_active('azrcrv-timelines/azrcrv-timelines.php')){ ?>
										<label for="timeline-integration"><input name="timeline-integration" type="checkbox" id="timeline-integration" value="1" <?php checked('1', $options['timeline-integration']); ?> /><?php printf(__('Enable integration with %s from %s?', 'page-index'), 'Timelines', 'azurecurve'); ?></label>
									<?php }else{
										echo printf(__('%s from %s not installed/activated.', 'page-index'), 'Timelines', 'azurecurve');
									}
									?>
							</td>
						</tr>
						
						<?php
							if (azrcrv_pi_is_plugin_active('azrcrv-timelines/azrcrv-timelines.php')){ ?>
								<tr>
									<th scope="row">
										<label for="timeline-signifier">
											<?php esc_html_e('Timeline Signifier', 'page-index'); ?>
										</label>
									</th>
									<td>
										<input name="timeline-signifier" type="text" id="timeline-signifier" value="<?php echo stripslashes($options['timeline-signifier']); ?>" class="small-text" />
										<?php										
										if (azrcrv_pi_is_plugin_active('azrcrv-icons/azrcrv-icons.php') AND $options['icons-integration'] == 1){ ?>
										or <select name="icon-visited">
												<option value="" <?php if($options['icon-visited'] == ''){ echo ' selected="selected"'; } ?>>&nbsp;</option>
												<?php						
												$icons = azrcrv_i_get_icons();
													
												foreach ($icons as $icon_id => $icon){
													echo '<option value="'.esc_html($icon_id).'" ';
													if($options['icon-visited'] == esc_html($icon_id)){ echo ' selected="selected"'; }
													echo '>'.esc_html($icon_id).'</option>';
												}
											echo '</select>';
										}
										?>
										<p class="description"><?php esc_html_e('Symbol displayed next to page index entries which have a timeline entry.', 'page-index'); ?></p>
									</td>
								</td></tr>
							<?php }
						?>
					</table>
				</div>
			</div>
			
			<input type="submit" value="<?php _e('Save Settings', 'page-index'); ?>" class="button-primary"/>
		</form>
	</div>
	
	<div>
		<p>
			<label for="additional-plugins">
				<?php printf(esc_html__('This plugin integrates with the following plugins from %s:', 'flags'), '<a href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve</a>'); ?>
			</label>
			<ul class='azrcrv-plugin-index'>
				<li>
					<?php
					if (azrcrv_f_is_plugin_active('azrcrv-flags/azrcrv-flags.php')){
						echo '<a href="admin.php?page=azrcrv-f" class="azrcrv-plugin-index">Flags</a>';
					}else{
						echo '<a href="https://development.azurecurve.co.uk/classicpress-plugins/flags/" class="azrcrv-plugin-index">Flags</a>';
					}
					?>
				</li>
				<li>
					<?php
					if (azrcrv_f_is_plugin_active('azrcrv-icons/azrcrv-icons.php')){
						echo '<a href="admin.php?page=azrcrv-i" class="azrcrv-plugin-index">Icons</a>';
					}else{
						echo '<a href="https://development.azurecurve.co.uk/classicpress-plugins/icons" class="azrcrv-plugin-index">Icons</a>';
					}
					?>
				</li>
				<li>
					<?php
					if (azrcrv_f_is_plugin_active('azrcrv-timelines/azrcrv-timelines.php')){
						echo '<a href="admin.php?page=azrcrv-t" class="azrcrv-plugin-index">Timelines</a>';
					}else{
						echo '<a href="https://development.azurecurve.co.uk/classicpress-plugins/timelines/" class="azrcrv-plugin-index">Timelines</a>';
					}
					?>
				</li>
			</ul>
		</p>
	</div>
	<?php
}

/**
 * Check if function active (included due to standard function failing due to order of load).
 *
 * @since 1.5.0
 *
 */
function azrcrv_pi_is_plugin_active($plugin){
    return in_array($plugin, (array) get_option('active_plugins', array()));
}

/**
 * Save settings.
 *
 * @since 1.0.0
 *
 */
function azrcrv_pi_save_options(){
	// Check that user has proper security level
	if (!current_user_can('manage_options')){
		wp_die(esc_html__('You do not have permissions to perform this action', 'page-index'));
	}
	// Check that nonce field created in configuration form is present
	// Check that nonce field created in configuration form is present
	if (! empty($_POST) && check_admin_referer('azrcrv-pi', 'azrcrv-pi-nonce')){
	
		// Retrieve original plugin options array
		$options = get_option('azrcrv-pi');
		
		$option_name = 'color';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'background';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'width';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'height';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'lineheight';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'margin';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'textalign';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'padding';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'fontweight';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'timeline-integration';
		if (isset($_POST[$option_name])){
			$options[$option_name] = 1;
		}else{
			$options[$option_name] = 0;
		}
		
		$option_name = 'timeline-signifier';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'flags-integration';
		if (isset($_POST[$option_name])){
			$options[$option_name] = 1;
		}else{
			$options[$option_name] = 0;
		}
		
		$option_name = 'flag-position';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'flag-width';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field(intval($_POST[$option_name]));
		}
		
		$option_name = 'icons-integration';
		if (isset($_POST[$option_name])){
			$options[$option_name] = 1;
		}else{
			$options[$option_name] = 0;
		}
		
		$option_name = 'icon-position';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field($_POST[$option_name]);
		}
		
		$option_name = 'icon-visited';
		if (isset($_POST[$option_name])){
			$options[$option_name] = sanitize_text_field($_POST[$option_name]);
		}
		
		// Store updated options array to database
		update_option('azrcrv-pi', $options);
		
		// Redirect the page to the configuration form that was processed
		wp_redirect(add_query_arg('page', 'azrcrv-pi&settings-updated', admin_url('admin.php')));
		exit;
	}
}

/**
 * Display page index in shortcode.
 *
 * @since 1.0.0
 *
 */
function azrcrv_pi_display_page_index($atts, $content = null){
	
	$options = azrcrv_pi_get_option('azrcrv-pi');
	
	if (!$options['color']){ $color = ''; }else{ $color = $options['color']; }
	if (!$options['background']){ $background = ''; }else{ $background = $options['background']; }
	if (!$options['width']){ $width = ''; }else{ $width = $options['width']; }
	if (!$options['height']){ $height = ''; }else{ $height = $options['height']; }
	if (!$options['lineheight']){ $lineheight = ''; }else{ $lineheight = $options['lineheight']; }
	if (!$options['margin']){ $margin = ''; }else{ $margin = $options['margin']; }
	if (!$options['textalign']){ $textalign = ''; }else{ $textalign = $options['textalign']; }
	if (!$options['padding']){ $padding = ''; }else{ $padding = $options['padding']; }
	if (!$options['fontweight']){ $fontweight = ''; }else{ $fontweight = $options['fontweight']; }
	
	$args = shortcode_atts(array(
		'pageid' => ''
		,'slug' => ''
		,'color' => $color
		,'background' => $background
		,'width' => $width
		,'height' => $height
		,'lineheight' => $lineheight
		,'margin' => $margin
		,'textalign' => $textalign
		,'padding' => $padding
		,'fontweight' => $fontweight
	), $atts);
	$pageid = $args['pageid'];
	$slug = $args['slug'];
	$color = $args['color'];
	$background = $args['background'];
	$width = $args['width'];
	$height = $args['height'];
	$lineheight = $args['lineheight'];
	$margin = $args['margin'];
	$textalign = $args['textalign'];
	$padding = $args['padding'];
	$fontweight = $args['fontweight'];
	
	if (strlen($color) > 0){ $color = "color: $color;"; }
	if (strlen($background) > 0){ $background = "background: $background;"; }
	if (strlen($width) > 0){ $width = "width: $width;"; }
	if (strlen($height) > 0){ $height = "height: $height;"; }
	if (strlen($lineheight) > 0){ $lineheight = "line-height: $lineheight;"; }
	if (strlen($margin) > 0){ $margin = "margin: $margin;"; }
	if (strlen($textalign) > 0){ $textalign = "text-align: $textalign;"; }
	if (strlen($padding) > 0){ $padding = "padding: $padding;"; }
	if (strlen($fontweight) > 0){ $fontweight = "font-weight: $fontweight;"; }
	
	$pageid = intval($pageid);
	$slug = sanitize_text_field($slug);
	
	global $wpdb;
	
	/*if (is_ssl()){
			$protocol = 'https';
	}else{
			$protocol = 'http';
	}
	$page_url = $protocol."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	if (substr($page_url, -1) == "/"){
		$page_url = substr($page_url, 0, -1);
	}*/
	
	if (strlen($pageid) > 0 AND $pageid != 0){
		$pageid = $pageid;
	}elseif (strlen($slug) > 0){
		$page = get_page_by_path($slug);
		$pageid = $page->ID;
	}else{
		$pageid = get_the_ID();
	}
	$page_url = get_permalink($pageid);

	$sql = $wpdb->prepare("SELECT ID, post_title, post_name FROM ".$wpdb->prefix."posts WHERE post_status = 'publish' AND post_type = 'page' AND post_parent=%s ORDER BY menu_order, post_title ASC", $pageid);
	
	$output = '';
	$myrows = $wpdb->get_results($sql);
	foreach ($myrows as $myrow){
		$overridecolor = '';

		$page_color = get_post_meta($myrow->ID, 'azrcrv-pi-color', true);
		if (strlen($page_color) == 0){
			$page_color = get_post_meta($myrow->ID, 'azc_pi_color', true);
		}
		if (strlen($page_color) > 0){
			$overridecolor = "color: $page_color;";
		}else{
			$overridecolor = $color;
		}

		$overridebackground = '';
		$page_background = get_post_meta($myrow->ID, 'azrcrv-pi-background', true);
		if (strlen($page_background) == 0){
			$page_background = get_post_meta($myrow->ID, 'azc_pi_background', true);
		}
		if (strlen($page_background) > 0){
			$overridebackground = "background: $page_background;";
		}else{
			$overridebackground = $background;
		}
		
		$flag = '';
		$flag_before = '';
		$flag_after = '';
		if (azrcrv_pi_is_plugin_active('azrcrv-flags/azrcrv-flags.php') AND $options['flags-integration'] == 1){
			$flag = get_post_meta($myrow->ID, 'Flag', true);
			if ($flag != ''){
				$flag = azrcrv_f_flag(array( 'id' => $flag, 'width' => $options['flag-width'].'px'));
				if ($options['flag-position'] == 'before'){
					$flag_before = $flag.'&nbsp;';
				}else{
					$flag_after = '&nbsp;'.$flag;
				}
			}
		}
		
		$icon = '';
		$icon_before = '';
		$icon_after = '';
		if (azrcrv_pi_is_plugin_active('azrcrv-icons/azrcrv-icons.php') AND $options['icons-integration'] == 1){
			$icon = get_post_meta($myrow->ID, 'Icon', true);
			if ($icon != ''){
				$icon = azrcrv_i_icon(array( $icon));
				if ($options['icon-position'] == 'before'){
					$icon_before = $icon.'&nbsp;';
				}else{
					$icon_after = '&nbsp;'.$icon;
				}
			}
		}
		
		$timeline_signifier = '';
		
		if (azrcrv_pi_is_plugin_active('azrcrv-timelines/azrcrv-timelines.php') AND $options['timeline-integration'] == 1){
			
			if (azrcrv_pi_is_plugin_active('azrcrv-icons/azrcrv-icons.php') AND $options['icons-integration'] == 1){
				if ($options['icon-visited'] != ''){
					$timeline_signifier_to_use = azrcrv_i_icon(array($options['icon-visited']));
				}
			}
			if (strlen($timeline_signifier_to_use) == 0){
				$timeline_signifier_to_use = $options['timeline-signifier'];
			}
			
			
			$sql = "SELECT COUNT(pm.meta_value) FROM ".$wpdb->prefix."posts as p INNER JOIN ".$wpdb->prefix."postmeta AS pm ON pm.post_id = p.ID WHERE p.post_status = 'publish' AND p.post_type = 'timeline-entry' AND pm.meta_key = 'timelines_metafields' AND pm.meta_value LIKE '%s'";
			
			$timeline_exists = $wpdb->get_var(
									$wpdb->prepare(
										$sql,
										'%'.$page_url.$myrow->post_name.'%'
									)
								);
			
			if ($timeline_exists >= 1){
				$timeline_signifier = '&nbsp;'.$timeline_signifier_to_use;
			}else{
				$timeline_signifier = '';
			}
		}
		
		$output .= "<a href='$page_url$myrow->post_name/' class='azrcrv-pi' style='$overridecolor $overridebackground $width $height $lineheight $margin $textalign $padding $fontweight'>$flag_before$icon_before$myrow->post_title$flag_after$icon_after$timeline_signifier</a>";
	}
	
	return "<span class='azrcrv-pi'>".$output."</span>";
}

?>