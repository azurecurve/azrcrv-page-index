=== Page Index ===
Contributors: azurecurve
Tags: page, pages, index, archive
Plugin URI: https://development.azurecurve.co.uk/classicpress-plugins/page-index/
Donate link: https://development.azurecurve.co.uk/support-development/
Requires at least: 1.0.0
Tested up to: 1.0.0
Requires PHP: 5.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Shortcode which displays a simple tile based page index showing the child pages of the loaded page or of the supplied pageid or slug. This plugin is multisite compatible.

== Description ==
Shortcode which displays a simple tile based page index showing the child pages of the loaded page or of the supplied pageid or slug. This plugin is multisite compatible.

To use simply place the [page-index] shortcode on a page or in a post. Tiled page index based on child pages of the page the shortcode is used on.

If a different page index is required, or the shortcode is used in a post use one of the following parameters:
* pageid
* slug
e.g. [page-index pageid='32'] or [page-index slug='mythology/celtic-fairy-tales']

If both parameters are supplied, then pageid will take precedence and slug will be ignored.

== Installation ==
To install the Page Index plugin:
* Download the plugin from <a href='https://github.com/azurecurve/azrcrv-multisite-favicon/'>GitHub</a>.
* Upload the entire zip file using the Plugins upload function in your ClassicPress admin panel.
* Activate the plugin.
* Configure relevant settings via the configuration page in the admin control panel (azurecurve menu).
* Add page-index shortcode to pages.

== Changelog ==
Changes and feature additions for the Page Index plugin:
= 1.0.0 =
* First version for ClassicPress forked from azurecurve Page Index WordPress Plugin.

== Frequently Asked Questions ==
= Can I translate this plugin? =
* Yes, the .pot fie is in the plugin's languages folder and can also be downloaded from the plugin page on https://development.azurecurve.co.uk/; if you do translate this plugin please sent the .po and .mo files to translations@azurecurve.co.uk for inclusion in the next version (full credit will be given).
= Is this plugin compatible with both WordPress and ClassicPress? =
* This plugin is developed for ClassicPress, but will likely work on WordPress.