<?php
/**
 * ------------------------------------------------------------------------------
 * Plugin Name: Page Index
 * Description: Displays Index of Pages using page-index Shortcode; uses the Parent Page field to determine content of index or one of supplied pageid or slug parameters.
 * Version: 1.2.2
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
register_activation_hook(__FILE__, 'azrcrv_create_plugin_menu_pi');

// include update client
require_once(dirname(__FILE__).'/libraries/updateclient/UpdateClient.class.php');

/**
 * Setup registration activation hook, actions, filters and shortcodes.
 *
 * @since 1.0.0
 *
 */
// add actions
register_activation_hook(__FILE__, 'azrcrv_pi_set_default_options');

// add actions
add_action('admin_menu', 'azrcrv_pi_create_admin_menu');
add_action('admin_post_azrcrv_pi_save_options', 'azrcrv_pi_save_options');
add_action('wp_enqueue_scripts', 'azrcrv_pi_load_css');
//add_action('the_posts', 'azrcrv_pi_check_for_shortcode');
add_action('plugins_loaded', 'azrcrv_pi_load_languages');


// add filters
add_filter('plugin_action_links', 'azrcrv_pi_add_plugin_action_link', 10, 2);

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
 * Set default options for plugin.
 *
 * @since 1.0.0
 *
 */
function azrcrv_pi_set_default_options($networkwide){
	
	$new_options = array(
				"color" => ""
				,"background" => ""
			);
	
	// set defaults for multi-site
	if (function_exists('is_multisite') && is_multisite()){
		// check if it is a network activation - if so, run the activation function for each blog id
		if ($networkwide){
			global $wpdb;

			$blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
			$original_blog_id = get_current_blog_id();

			foreach ($blog_ids as $blog_id){
				switch_to_blog($blog_id);

				if (get_option('azrcrv-pi') === false){
					if (get_option('azc_pi') === false){
						add_option('azrcrv-pi', $new_options);
					}else{
						add_option('azrcrv-pi', get_option('azc_pi'));
					}
				}
			}

			switch_to_blog($original_blog_id);
		}else{
			if (get_option('azrcrv-pi') === false){
				if (get_option('azc_pi') === false){
					add_option('azrcrv-pi', $new_options);
				}else{
					add_option('azrcrv-pi', get_option('azc_pi'));
				}
			}
		}
		if (get_site_option('azrcrv-pi') === false){
				if (get_option('azc_pi') === false){
					add_option('azrcrv-pi', $new_options);
				}else{
					add_option('azrcrv-pi', get_option('azc_pi'));
				}
		}
	}
	//set defaults for single site
	else{
		if (get_option('azrcrv-pi') === false){
				if (get_option('azc_pi') === false){
					add_option('azrcrv-pi', $new_options);
				}else{
					add_option('azrcrv-pi', get_option('azc_pi'));
				}
		}
	}
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
		$settings_link = '<a href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page=azrcrv-pi"><img src="'.plugins_url('/pluginmenu/images/Favicon-16x16.png', __FILE__).'" style="padding-top: 2px; margin-right: -5px; height: 16px; width: 16px;" alt="azurecurve" />'.esc_html__('Settings' ,'page-index').'</a>';
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
	$options = get_option('azrcrv-pi');
	?>
	<div id="azrcrv-pi-general" class="wrap">
		<fieldset>
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
				<table class="form-table">
				
				<tr><td colspan=2>
					<p><?php esc_html_e('If the options are blank then the defaults in the plugin\'s CSS will be used.', 'page-index'); ?></p>
				</td></tr>
				
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
				
				<input type="submit" value="Submit" class="button-primary"/>
			</form>
		</fieldset>
		<?php esc_html_e('azrcrv-pi-color (or azc_pi_color) and azrcrv-pi-background (or azi_pi_background) custom fields can be applied to a page to change the color of that pages appearance in the page index.', 'page-index'); ?>
	</div>
	<?php
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
	check_admin_referer('azrcrv-pi', 'azrcrv-pi-nonce');
	settings_fields('azrcrv-pi');
	
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
	
	// Store updated options array to database
	update_option('azrcrv-pi', $options);
	
	// Redirect the page to the configuration form that was processed
	wp_redirect(add_query_arg('page', 'azrcrv-pi&settings-updated', admin_url('admin.php')));
	exit;
}

/**
 * Display page index in shortcode.
 *
 * @since 1.0.0
 *
 */
function azrcrv_pi_display_page_index($atts, $content = null){
	
	$options = get_option('azrcrv-pi');
	
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
	
	if (is_ssl()){
			$protocol = 'https';
	}else{
			$protocol = 'http';
	}
	$page_url = $protocol."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	if (substr($page_url, -1) == "/"){
		$page_url = substr($page_url, 0, -1);
	}
	
	if (strlen($postid) > 0){
		$pageid = $postid;
	}elseif (strlen($slug) > 0){
		$page = get_page_by_path($slug);
		$pageid = $page->ID;
	}else{
		$pageid = get_the_ID();
	}

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
		$output .= "<a href='".$page_url."/".$myrow->post_name."/' class='azrcrv-pi' style='$overridecolor $overridebackground $width $height $lineheight $margin $textalign $padding $fontweight'>".$myrow->post_title."</a>";
	}
	
	return "<span class='azrcrv-pi'>".$output."</span>";
}

?>