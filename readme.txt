=== Image Rights ===
Contributors: wsmeu, cleuenberg
Donate link: 
Tags: image rights, photo credits, copyrights, media library, custom fields, meta fields, images, photos, copyright, dsgvo
Requires at least: 
Tested up to: 6.3.1
Stable tag: 1.2
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Adds additional fields for setting image credits in the media library.

== Description ==

This plugin adds new meta fields for images within the WordPress media library. You can display all your images in one place with corresponding copyrights (photographer and platform) via a simple shortcode. Especially the German law forces website owners to display all the copyright holders of graphics and photos used on a website.

Features:

* adds new meta fields for media library items
* edit copyright holder / photographer name
* set an optional stock photo platform
* shortcode `[photo_credits]` displays all images with credits set
* optional image overlay with copyright information

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/image-rights` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Edit copyright information in your media library items.
3. Use the shortcode `[photo_credits]` in order to display a simple table with all your copyrighted images.
4. Optionally change table layout by addressing CSS class `table.photo-credits-table` in your theme's stylesheet.
5. Activate an optional copyright overlay for images under Settings > Media > Image Rights Options

== Screenshots ==

1. Editing copyright information within the media library.
2. Display all copyrighted images in the front-end.

== Changelog ==

= 1.2 =
* Added admin option to make overlays in frontend optional
* Updated German language file
* Small bugfixes

= 1.1 =
* Added layers on images with credits in frontend.

= 1.0 =
* Initial release for WordPress plugin directory.