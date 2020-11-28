=== Page Index ===

Description:	Shortcode which displays a simple tile based page index showing the child pages of the loaded page or of the supplied pageid or slug.
Version:		1.5.0
Tags:			page, pages, index
Author:			azurecurve
Author URI:		https://development.azurecurve.co.uk/
Plugin URI:		https://development.azurecurve.co.uk/classicpress-plugins/page-index/
Download link:	https://github.com/azurecurve/azrcrv-page-index/releases/download/v1.5.0/azrcrv-page-index.zip
Donate link:	https://development.azurecurve.co.uk/support-development/
Requires PHP:	5.6
Requires:		1.0.0
Tested:			4.9.99
Text Domain:	page-index
Domain Path:	/languages
License: 		GPLv2 or later
License URI: 	http://www.gnu.org/licenses/gpl-2.0.html

Shortcode which displays a simple tile based page index showing the child pages of the loaded page or of the supplied pageid or slug.

== Description ==

# Description

Shortcode which displays a simple tile based page index showing the child pages of the loaded page or of the supplied pageid or slug. This plugin is multisite compatible.

To use simply place the [page-index] shortcode on a page or in a post. Tiled page index based on child pages of the page the shortcode is used on.

If a different page index is required, or the shortcode is used in a post use one of the following parameters:
* pageid
* slug
e.g. [page-index pageid='32'] or [page-index slug='mythology/celtic-fairy-tales']

If both parameters are supplied, then pageid will take precedence and slug will be ignored.

Integrated with [Flags](https://development.azurecurve.co.uk/classicpress-plugins/flags/) plugin to display flag in page index; add custom **Flag** custom field to page.

Integrated with [Icons](https://development.azurecurve.co.uk/classicpress-plugins/icons/) plugin to display icon in page index; add custom **Icon** custom field to page.

This plugin is multisite compatible; each site will need settings to be configured in the admin dashboard.

== Installation ==

# Installation Instructions

 * Download the plugin from [GitHub](https://github.com/azurecurve/azrcrv-page-index/releases/latest/).
 * Upload the entire zip file using the Plugins upload function in your ClassicPress admin panel.
 * Activate the plugin.
 * Configure relevant settings via the configuration page in the admin control panel (azurecurve menu).

== Frequently Asked Questions ==

# Frequently Asked Questions

### Can I translate this plugin?
Yes, the .pot fie is in the plugins languages folder and can also be downloaded from the plugin page on https://development.azurecurve.co.uk/; if you do translate this plugin, please sent the .po and .mo files to translations@azurecurve.co.uk for inclusion in the next version (full credit will be given).

### Is this plugin compatible with both WordPress and ClassicPress?
This plugin is developed for ClassicPress, but will likely work on WordPress.

== Changelog ==

# Changelog

### [Version 1.5.0](https://github.com/azurecurve/azrcrv-page-index/releases/tag/v1.5.0)
 * Integrate with [Flags](https://development.azurecurve.co.uk/classicpress-plugins/flags/)
 * Integrate with [Icons](https://development.azurecurve.co.uk/classicpress-plugins/icons/) for icon in page index and timeline signifier.
 * Add tabs to settings page.
 * Fix bug with plugin active check.
 * Update azurecurve plugin menu.

### [Version 1.4.0](https://github.com/azurecurve/azrcrv-page-index/releases/tag/v1.4.0)
 * Fix plugin action link to use admin_url() function.
 * Rewrite option handling so defaults not stored in database on plugin initialisation.
 * Update azurecurve plugin menu.
 * Amend to only load css when shortcode on page.

### [Version 1.3.1](https://github.com/azurecurve/azrcrv-page-index/releases/tag/v1.3.1)
 * Fix bug with incorrect plugin slug declaration for plugin icon and banner.
 
### [Version 1.3.0](https://github.com/azurecurve/azrcrv-page-index/releases/tag/v1.3.0)
 * Add integration with [Timelines](https://development.azurecurve.co.uk/classicpress-plugins/timelines/) from [azurecurve](https://development.azurecurve.co.uk/classicpress-plugins/).
 * Add plugin icon and banner.
 * Update generation of page URL to use get_permalink.
 * Update CSS to use flexbox instead of line height to handle wrapping within page index tiles.

### [Version 1.2.5](https://github.com/azurecurve/azrcrv-page-index/releases/tag/v1.2.5)
 * Fix bug with pageid parameter not working.

### [Version 1.2.4](https://github.com/azurecurve/azrcrv-page-index/releases/tag/v1.2.4)
 * Fix bug with setting of default options.
 * Fix bug with plugin menu.
 * Update plugin menu css.

### [Version 1.2.3](https://github.com/azurecurve/azrcrv-page-index/releases/tag/v1.2.3)
 * Rewrite default option creation function to resolve several bugs.
 * Upgrade azurecurve plugin to store available plugins in options.
 
### [Version 1.2.2](https://github.com/azurecurve/azrcrv-page-index/releases/tag/v1.2.2)
 * Update Update Manager class to v2.0.0.
 * Update action link.
 * Update azurecurve menu icon with compressed image.
 
### [Version 1.2.1](https://github.com/azurecurve/azrcrv-page-index/releases/tag/v1.2.1)
 * Fix bug with incorrect language load text domain.

### [Version 1.2.0](https://github.com/azurecurve/azrcrv-page-index/releases/tag/v1.2.0)
 * Add integration with Update Manager for automatic updates.
 * Fix issue with display of azurecurve menu.
 * Change settings page heading.
 * Add load_plugin_textdomain to handle translations.

### [Version 1.1.0](https://github.com/azurecurve/azrcrv-page-index/releases/tag/v1.1.0)
 * Add protocol check when constructing page index.

### [Version 1.0.1](https://github.com/azurecurve/azrcrv-page-index/releases/tag/v1.0.1)
 * Update azurecurve menu for easier maintenance.
 * Move require of azurecurve menu below security check.

### [Version 1.0.0](https://github.com/azurecurve/azrcrv-page-index/releases/tag/v1.0.0)
 * Initial release for ClassicPress forked from azurecurve Page Index WordPress Plugin.

== Other Notes ==

# About azurecurve

**azurecurve** was one of the first plugin developers to start developing for Classicpress; all plugins are available from [azurecurve Development](https://development.azurecurve.co.uk/) and are integrated with the [Update Manager plugin](https://codepotent.com/classicpress/plugins/update-manager/) by [CodePotent](https://codepotent.com/) for fully integrated, no hassle, updates.

Some of the top plugins available from **azurecurve** are:
* [Add Twitter Cards](https://development.azurecurve.co.uk/classicpress-plugins/add-twitter-cards/)
* [Breadcrumbs](https://development.azurecurve.co.uk/classicpress-plugins/breadcrumbs/)
* [Series Index](https://development.azurecurve.co.uk/classicpress-plugins/series-index/)
* [To Twitter](https://development.azurecurve.co.uk/classicpress-plugins/to-twitter/)
* [Theme Switcher](https://development.azurecurve.co.uk/classicpress-plugins/theme-switcher/)
* [Toggle Show/Hide](https://development.azurecurve.co.uk/classicpress-plugins/toggle-showhide/)