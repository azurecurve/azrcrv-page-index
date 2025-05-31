<?php
/**
 * ------------------------------------------------------------------------------
 * Plugin Name:		Page Index
 * Description:		Displays Index of Pages using page-index Shortcode; uses the Parent Page field to determine content of index or one of supplied pageid or slug parameters.
 * Version:			1.6.5
 * Requires CP:		1.0
 * Requires PHP:	7.4
 * Author:			azurecurve
 * Author URI:		https://development.azurecurve.co.uk/classicpress-plugins/
 * Plugin URI:		https://development.azurecurve.co.uk/classicpress-plugins/page-index/
 * Donate link:		https://development.azurecurve.co.uk/support-development/
 * Text Domain:		page-index
 * Domain Path:		/languages
 * License:			GPLv2 or later
 * License URI:		http://www.gnu.org/licenses/gpl-2.0.html
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
add_action('admin_enqueue_scripts', 'azrcrv_pi_load_admin_jquery');
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
						"width" => "",
						"height" => "",
						"lineheight" => "",
						"margin" => "",
						"background" => "",
						"padding" => "",
						"textalign" => "",
						"fontweight" => "",
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
		$settings_link = '<a href="'.admin_url('admin.php?page=azrcrv-pi').'"><img src="'.plugins_url('/pluginmenu/images/logo.svg', __FILE__).'" style="padding-top: 2px; margin-right: -5px; height: 16px; width: 16px;" alt="azurecurve" />'.esc_html__('Settings' ,'page-index').'</a>';
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
	
	global $pagenow;
	
	if ($pagenow == 'admin.php' AND $_GET['page'] == 'azrcrv-pi'){
		wp_register_style('azrcrv-pi-admin-css', plugins_url('assets/css/admin.css', __FILE__), false, '1.0.0');
		wp_enqueue_style('azrcrv-pi-admin-css');
		
		wp_register_style('azrcrv-pi-admin-css-jquery-ui', plugins_url('libraries/jquery-ui/jquery-ui.css', __FILE__), false, '1.0.0');
		wp_enqueue_style('azrcrv-pi-admin-css-jquery-ui');
		
		wp_register_style('azrcrv-pi-admin-css-jquery-ui-structure', plugins_url('libraries/jquery-ui/jquery-ui.structure.css', __FILE__), false, '1.0.0');
		wp_enqueue_style('azrcrv-pi-admin-css-jquery-ui-structure');
	}
}

/**
 * Load media uploaded.
 *
 * @since 1.6.0
 *
 */
function azrcrv_pi_load_admin_jquery(){
	
	global $pagenow;
	
	if ($pagenow == 'admin.php' AND $_GET['page'] == 'azrcrv-pi'){
		wp_enqueue_script('azrcrv-pi-admin-jquery', plugins_url('assets/jquery/admin.js', __FILE__), array('jquery'));
		
		wp_enqueue_script('azrcrv-pi-admin-jquery-ui', plugins_url('libraries/jquery-ui/jquery-ui.js', __FILE__), array('jquery'));
		wp_enqueue_script('azrcrv-pi-admin-jquery-ui-external', plugins_url('libraries/jquery-ui/external/jquery/jquery.js', __FILE__), array('jquery'));
	}
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

	/*
		Tab 1 = General Settings
	*/
	$tab_1_label = 'Defaults';
	$tab_1 = "
				<table class='form-table'>";
	
	// instructions
	$instructions_th = '<p>'.sprintf(esc_html__('%s and %s custom fields can be applied to a page to change the color of a specific pages appearance in the page index.', 'page-index'), 'azrcrv-pi-color', 'azrcrv-pi-background').'</p>';
	$instructions_th .= '<p>'.esc_html__('If the options are blank then the defaults in the plugin\'s CSS will be used.', 'page-index').'</p>';
	$tab_1 .= "
					<tr>
						<th scope='row' colspan=2>
							$instructions_th
						</th>
					</tr>";
	// color
	$color_th = esc_html__('Color', 'page-index');
	$color_name = 'color';
	$color_value = esc_html__(stripslashes($options[$color_name]));
	$color_input = "<input type='text' name='$color_name' value='$color_value' class='regular-text' />";
	$color_description_text = sprintf(esc_html__('Set default color (e.g. %1$s#FFF%2$s or %1$sblack%2$s).', 'page-index'), '<strong>', '</strong>');
	$color_description = "<p class='description'>$color_description_text</p>";
	$color_td = $color_input.$color_description;
	$tab_1 .= "
					<tr>
						<th scope='row'>
							$color_th
						</th>
						
						<td>
							$color_td
						</td>
					</tr>";
	// background
	$background_th = esc_html__('Background Color', 'page-index');
	$background_name = 'background';
	$background_value = esc_html__(stripslashes($options[$background_name]));
	$background_input = "<input type='text' name='$background_name' value='$background_value' class='regular-text' />";
	$background_description_text = sprintf(esc_html__('Set default background color (e.g. %1$s#000%2$s or %1$swhite%2$s).', 'page-index'), '<strong>', '</strong>');
	$background_description = "<p class='description'>$background_description_text</p>";
	$background_td = $background_input.$background_description;
	$tab_1 .= "
					<tr>
						<th scope='row'>
							$background_th
						</th>
						
						<td>
							$background_td
						</td>
					</tr>";
	// width
	$width_th = esc_html__('Width', 'page-index');
	$width_name = 'width';
	$width_value = esc_html__(stripslashes($options[$width_name]));
	$width_input = "<input type='text' name='$width_name' value='$width_value' class='regular-text' />";
	$width_description_text = sprintf(esc_html__('Set default width (e.g. %1$s#48&#37;%2$s or %1$s300px%2$s).', 'page-index'), '<strong>', '</strong>');
	$width_description = "<p class='description'>$width_description_text</p>";
	$width_td = $width_input.$width_description;
	$tab_1 .= "
					<tr>
						<th scope='row'>
							$width_th
						</th>
						
						<td>
							$width_td
						</td>
					</tr>";
	// height
	$height_th = esc_html__('Height', 'page-index');
	$height_name = 'height';
	$height_value = esc_html__(stripslashes($options[$height_name]));
	$height_input = "<input type='text' name='$height_name' value='$height_value' class='regular-text' />";
	$height_description_text = sprintf(esc_html__('Set default height (e.g. %1$s100px%2$s).', 'page-index'), '<strong>', '</strong>');
	$height_description = "<p class='description'>$height_description_text</p>";
	$height_td = $height_input.$height_description;
	$tab_1 .= "
					<tr>
						<th scope='row'>
							$height_th
						</th>
						
						<td>
							$height_td
						</td>
					</tr>";
	// lineheight
	$lineheight_th = esc_html__('Line Height', 'page-index');
	$lineheight_name = 'lineheight';
	$lineheight_value = esc_html__(stripslashes($options[$lineheight_name]));
	$lineheight_input = "<input type='text' name='$lineheight_name' value='$lineheight_value' class='regular-text' />";
	$lineheight_description_text = sprintf(esc_html__('Set default line height (e.g. %1$s100px%2$s).', 'page-index'), '<strong>', '</strong>');
	$lineheight_description = "<p class='description'>$lineheight_description_text</p>";
	$lineheight_td = $lineheight_input.$lineheight_description;
	$tab_1 .= "
					<tr>
						<th scope='row'>
							$lineheight_th
						</th>
						
						<td>
							$lineheight_td
						</td>
					</tr>";
	// margin
	$margin_th = esc_html__('Margin', 'page-index');
	$margin_name = 'margin';
	$margin_value = esc_html__(stripslashes($options[$margin_name]));
	$margin_input = "<input type='text' name='$margin_name' value='$margin_value' class='regular-text' />";
	$margin_description_text = sprintf(esc_html__('Set default margin (e.g. %1$s6px%2$s).', 'page-index'), '<strong>', '</strong>');
	$margin_description = "<p class='description'>$margin_description_text</p>";
	$margin_td = $margin_input.$margin_description;
	$tab_1 .= "
					<tr>
						<th scope='row'>
							$margin_th
						</th>
						
						<td>
							$margin_td
						</td>
					</tr>";
	// padding
	$padding_th = esc_html__('Padding', 'page-index');
	$padding_name = 'padding';
	$padding_value = esc_html__(stripslashes($options[$padding_name]));
	$padding_input = "<input type='text' name='$padding_name' value='$padding_value' class='regular-text' />";
	$padding_description_text = sprintf(esc_html__('Set default padding (e.g. %1$s3px 2px 3px 2px%2$s).', 'page-index'), '<strong>', '</strong>');
	$padding_description = "<p class='description'>$padding_description_text</p>";
	$padding_td = $padding_input.$padding_description;
	$tab_1 .= "
					<tr>
						<th scope='row'>
							$padding_th
						</th>
						
						<td>
							$padding_td
						</td>
					</tr>";
	// textalign
	$textalign_th = esc_html__('Text Align', 'page-index');
	$textalign_name = 'textalign';
	$textalign_value = esc_html__(stripslashes($options[$textalign_name]));
	$textalign_input = "<input type='text' name='$textalign_name' value='$textalign_value' class='regular-text' />";
	$textalign_description_text = sprintf(esc_html__('Set default text align (e.g. %1$scenter%2$s or %1$sleft%2$s).', 'page-index'), '<strong>', '</strong>');
	$textalign_description = "<p class='description'>$textalign_description_text</p>";
	$textalign_td = $textalign_input.$textalign_description;
	$tab_1 .= "
					<tr>
						<th scope='row'>
							$textalign_th
						</th>
						
						<td>
							$textalign_td
						</td>
					</tr>";
	// fontweight
	$fontweight_th = esc_html__('Font-Weight', 'page-index');
	$fontweight_name = 'fontweight';
	$fontweight_value = esc_html__(stripslashes($options[$fontweight_name]));
	$fontweight_input = "<input type='text' name='$fontweight_name' value='$fontweight_value' class='regular-text' />";
	$fontweight_description_text = sprintf(esc_html__('Set default font-weight (e.g. %1$s900%2$s).', 'page-index'), '<strong>', '</strong>');
	$fontweight_description = "<p class='description'>$fontweight_description_text</p>";
	$fontweight_td = $fontweight_input.$fontweight_description;
	$tab_1 .= "
					<tr>
						<th scope='row'>
							$fontweight_th
						</th>
						
						<td>
							$fontweight_td
						</td>
					</tr>";
	$tab_1 .= "
				</table>";
	/*
		Tab 2 = Integration Settings
	*/
	$tab_2_label = 'Integration';
	$tab_2 = "
				<table class='form-table'>";
	// integrate with flags
	$flags_integration_th = sprintf(esc_html__('Integrate with %s?', 'page-index'), '<a class="azrcrv" href="https://development.azurecurve.co.uk/classicpress-plugins/flags/">Flags</a>');
	$flags_integration_name = 'flags-integration';
	$flags_integration_checked = ($options[$flags_integration_name] == 1) ? 'checked' : '';
	if (azrcrv_pi_is_plugin_active('azrcrv-flags/azrcrv-flags.php')){
		$flags_integration_prompt = sprintf(esc_html__('Enable integration with %s from %s?', 'page-index'), '<a class="azrcrv" href="admin.php?page=azrcrv-f">Flags</a>', '<a class="azrcrv" href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve</a>');
	}else{
		$flags_integration_prompt = sprintf(esc_html__('%s from %s not installed/activated.', 'page-index'), '<a class="azrcrv" href="https://development.azurecurve.co.uk/classicpress-plugins/flags/">Flags</a>', '<a class="azrcrv" href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve</a>');
	}
	$flags_integration_description_text = sprintf(esc_html__('Add a custom field called %s containing the required flag id to a page.', 'page-index'), '<strong>Flag</strong>');
	$flags_integration_description = "<p class='description'>$flags_integration_description_text</p>";
	$flags_integration_td = "<input name='$flags_integration_name' type='checkbox' id='$flags_integration_name' value='1' $flags_integration_checked />";
	$flags_integration_td .= "<label for='$flags_integration_name'>$flags_integration_prompt</label>";
	$flags_integration_td .= $flags_integration_description;
	$tab_2 .= "
					<tr>
						<th scope='row'>
							$flags_integration_th
						</th>
						
						<td>
							$flags_integration_td
						</td>
					</tr>";
	// flag width
	$flag_width_th = esc_html__('Flag Width', 'page-index');
	$flag_width_name = 'flag-width';
	$flag_width_value = esc_html__(stripslashes($options[$flag_width_name]));
	$flag_width_input = "<input type='number' minimum=16 step=1 name='$flag_width_name' value='$flag_width_value' class='small-text' /> px";
	$flag_width_description_text = sprintf(esc_html__('Minimum flag width is %dpx.', 'page-index'), 16);
	$flag_width_description = "<p class='description'>$width_description_text</p>";
	$flag_width_td = $flag_width_input.$flag_width_description;
	$tab_2 .= "
					<tr>
						<th scope='row'>
							$flag_width_th
						</th>
						
						<td>
							$flag_width_td
						</td>
					</tr>";
	// flag position
	$flag_postition_th = esc_html__('Flag Position', 'page-index');
	$flag_postition_name = 'flag-position';
	$flag_postition_1_value = esc_html__("Before", "page-index");
	$flag_postition_1_checked = ($options[$flag_postition_name] == 'before') ? 'selected' : '';
	$flag_postition_1_option = "<option value='before' $flag_postition_1_checked>$flag_postition_1_value</option>";
	$flag_postition_2_value = esc_html__("After", "page-index");
	$flag_postition_2_checked = ($options[$flag_postition_name] == 'after') ? 'selected' : '';
	$flag_postition_2_option = "<option value='after' $flag_postition_2_checked>$flag_postition_2_value</option>";
	$flag_postition_td = "<select name='$flag_postition_name'>$flag_postition_1_option$flag_postition_2_option</select>";
	$tab_2 .= "
					<tr>
						<th scope='row'>
							$flag_postition_th
						</th>
						
						<td>
							$flag_postition_td
						</td>
					</tr>";
	// integrate with icons
	$icons_integration_th = sprintf(esc_html__('Integrate with %s?', 'page-index'), '<a class="azrcrv" href="https://development.azurecurve.co.uk/classicpress-plugins/icons/">Icons</a>');
	$icons_integration_name = 'icons-integration';
	$icons_integration_checked = ($options[$icons_integration_name] == 1) ? 'checked' : '';
	if (azrcrv_pi_is_plugin_active('azrcrv-icons/azrcrv-icons.php')){
		$icons_integration_prompt = sprintf(esc_html__('Enable integration with %s from %s?', 'page-index'), '<a class="azrcrv" href="admin.php?page=azrcrv-i">Icons</a>', '<a class="azrcrv" href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve</a>');
	}else{
		$icons_integration_prompt = sprintf(esc_html__('%s from %s not installed/activated.', 'page-index'), '<a class="azrcrv" href="https://development.azurecurve.co.uk/classicpress-plugins/icons/">Icons</a>', '<a class="azrcrv" href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve</a>');
	}
	$icons_integration_description_text = sprintf(esc_html__('Add a custom field called %s containing the required icon id to a page.', 'page-index'), '<strong>Icon</strong>');
	$icons_integration_description = "<p class='description'>$icons_integration_description_text</p>";
	$icons_integration_td = "<input name='$icons_integration_name' type='checkbox' id='$icons_integration_name' value='1' $icons_integration_checked />";
	$icons_integration_td .= "<label for='$icons_integration_name'>$icons_integration_prompt</label>";
	$icons_integration_td .= $icons_integration_description;
	$tab_2 .= "
					<tr>
						<th scope='row'>
							$icons_integration_th
						</th>
						
						<td>
							$icons_integration_td
						</td>
					</tr>";
	// icon position
	$icon_postition_th = esc_html__('Icon Position', 'page-index');
	$icon_postition_name = 'icon-position';
	$icon_postition_1_value = esc_html__("Before", "page-index");
	$icon_postition_1_checked = ($options[$icon_postition_name] == 'before') ? 'selected' : '';
	$icon_postition_1_option = "<option value='before' $icon_postition_1_checked>$icon_postition_1_value</option>";
	$icon_postition_2_value = esc_html__("After", "page-index");
	$icon_postition_2_checked = ($options[$icon_postition_name] == 'after') ? 'selected' : '';
	$icon_postition_2_option = "<option value='after' $icon_postition_2_checked>$icon_postition_2_value</option>";
	$icon_postition_td = "<select name='$icon_postition_name'>$icon_postition_1_option$icon_postition_2_option</select>";
	$tab_2 .= "
					<tr>
						<th scope='row'>
							$icon_postition_th
						</th>
						
						<td>
							$icon_postition_td
						</td>
					</tr>";
	// integrate with timelines
	$timelines_integration_th = sprintf(esc_html__('Integrate with %s?', 'page-index'), '<a class="azrcrv" href="https://development.azurecurve.co.uk/classicpress-plugins/timelines/">Timelines</a>');
	$timelines_integration_name = 'timeline-integration';
	$timelines_integration_checked = ($options[$timelines_integration_name] == 1) ? 'checked' : '';
	if (azrcrv_pi_is_plugin_active('azrcrv-timelines/azrcrv-timelines.php')){
		$timelines_integration_prompt = sprintf(esc_html__('Enable integration with %s from %s?', 'page-index'), '<a class="azrcrv" href="admin.php?page=azrcrv-t">Timelines</a>', '<a class="azrcrv" href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve</a>');
	}else{
		$timelines_integration_prompt = sprintf(esc_html__('%s from %s not installed/activated.', 'page-index'), '<a class="azrcrv" href="https://development.azurecurve.co.uk/classicpress-plugins/timelines/">Timelines</a>', '<a class="azrcrv" href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve</a>');
	}
	$timelines_integration_td = "<input name='$timelines_integration_name' type='checkbox' id='$timelines_integration_name' value='1' $timelines_integration_checked />";
	$timelines_integration_td .= "<label for='$timelines_integration_name'>$timelines_integration_prompt</label>";
	$tab_2 .= "
					<tr>
						<th scope='row'>
							$timelines_integration_th
						</th>
						
						<td>
							$timelines_integration_td
						</td>
					</tr>";
	// timeline signifier
	if (azrcrv_pi_is_plugin_active('azrcrv-timelines/azrcrv-timelines.php')){
		$timeline_signifier_th = esc_html__('Timeline Signifier', 'page-index');
		$timeline_signifier_name = 'timeline-signifier';
		$timeline_signifier_value = esc_html__(stripslashes($options[$timeline_signifier_name]));
		$timeline_signifier_input = "<input type='text' name='$timeline_signifier_name' value='$timeline_signifier_value' class='small-text' /> px";
		
		$icon_visited_td = '';
		
		if (azrcrv_pi_is_plugin_active('azrcrv-icons/azrcrv-icons.php') AND $options['icons-integration'] == 1){
			$icon_visited_name = 'icon-visited';
			
			$icon_visited_value = "";
			$icon_visited_checked = ($options[$icon_visited_name] == $icon_visited_value) ? 'selected' : '';
			$icon_visited_option = "<option value='' $icon_visited_checked>$icon_visited_value</option>";
			// get available icons
			$icons = azrcrv_i_get_icons();
			$icon_visited_options = '';
			foreach ($icons as $icon_id => $icon){
				$icon_value = esc_html__($icon_id, "page-index");
				$icon_checked = ($options[$icon_visited_name] == esc_html($icon_id)) ? 'selected' : '';
				$icon_option = "<option value='$icon_value' $icon_checked>$icon_value</option>";
				
				$icon_visited_options .= $icon_option;
			}
			
			$icon_visited_td = " or <select name='$icon_visited_name'>$icon_visited_option$icon_visited_options</select>";
		}
		
		$timeline_signifier_description_text = esc_html__('Symbol displayed next to page index entries which have a timeline entry.', 'page-index');
		$timeline_signifier_description = "<p class='description'>$timeline_signifier_description_text</p>";
		$timeline_signifier_td = $timeline_signifier_input.$icon_visited_td.$timeline_signifier_description;
		$tab_2 .= "
					<tr>
						<th scope='row'>
							$timeline_signifier_th
						</th>
						
						<td>
							$timeline_signifier_td
						</td>
					</tr>";
				
	}
	$tab_2 .= "
				</table>";
	?>
	
	<div id="azrcrv-pi-general" class="wrap">
		<h1>
			<?php
				echo '<a href="https://development.azurecurve.co.uk/classicpress-plugins/"><img src="'.plugins_url('/pluginmenu/images/logo.svg', __FILE__).'" style="padding-right: 6px; height: 20px; width: 20px;" alt="azurecurve" /></a>';
				esc_html_e(get_admin_page_title());
			?>
		</h1>
		
		<?php
		if(isset($_GET['settings-updated'])){
			echo '<div class="notice notice-success is-dismissible"><p><strong>'.esc_html__('Settings have been saved.', 'page-index').'</strong></p></div>';
		}
		?>
		
		<form method="post" action="admin-post.php">
			<input type="hidden" name="action" value="azrcrv_pi_save_options" />
			<input name="page_options" type="hidden" value="azrcrv-pi" />
			
			<!-- Adding security through hidden referrer field -->
			<?php wp_nonce_field('azrcrv-pi', 'azrcrv-pi-nonce'); ?>
		
			<div id="tabs" class="ui-tabs">
				<ul class="ui-tabs-nav ui-widget-header" role="tablist">
					<li class="ui-state-default ui-state-active" aria-controls="tab-panel-1" aria-labelledby="tab-1" aria-selected="true" aria-expanded="true" role="tab">
						<a id="tab-1" class="ui-tabs-anchor" href="#tab-panel-1"><?php echo $tab_1_label; ?></a>
					</li>
					<li class="ui-state-default" aria-controls="tab-panel-2" aria-labelledby="tab-2" aria-selected="false" aria-expanded="false" role="tab">
						<a id="tab-2" class="ui-tabs-anchor" href="#tab-panel-2"><?php echo $tab_2_label; ?></a>
					</li>
				</ul>
				<div id="tab-panel-1" class="ui-tabs-scroll" role="tabpanel" aria-hidden="false">
					<?php echo $tab_1; ?>
				</div>
				<div id="tab-panel-2" class="ui-tabs-scroll ui-tabs-hidden" role="tabpanel" aria-hidden="true">
					<?php echo $tab_2; ?>
				</div>
			</div>
			
			<input type="submit" value="<?php esc_html_e('Save Settings', 'page-index'); ?>" class="button-primary"/>
		</form>
	</div>
	
	<div>
		<p>
			<label for="additional-plugins">
				<?php printf(esc_html__('This plugin integrates with the following plugins from %s:', 'page-index'), '<a href="https://development.azurecurve.co.uk/classicpress-plugins/">azurecurve</a>'); ?>
			</label>
			<ul class='azrcrv-plugin-index'>
				<li>
					<?php
					if (azrcrv_pi_is_plugin_active('azrcrv-flags/azrcrv-flags.php')){
						echo '<a href="admin.php?page=azrcrv-f" class="azrcrv-plugin-index">Flags</a>';
					}else{
						echo '<a href="https://development.azurecurve.co.uk/classicpress-plugins/flags/" class="azrcrv-plugin-index">Flags</a>';
					}
					?>
				</li>
				<li>
					<?php
					if (azrcrv_pi_is_plugin_active('azrcrv-icons/azrcrv-icons.php')){
						echo '<a href="admin.php?page=azrcrv-i" class="azrcrv-plugin-index">Icons</a>';
					}else{
						echo '<a href="https://development.azurecurve.co.uk/classicpress-plugins/icons" class="azrcrv-plugin-index">Icons</a>';
					}
					?>
				</li>
				<li>
					<?php
					if (azrcrv_pi_is_plugin_active('azrcrv-timelines/azrcrv-timelines.php')){
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