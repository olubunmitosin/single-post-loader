### Single Post Loader

Dynamically loads the next post without pagination on single post page on infinite scroll.

#### Description

Rather than having your users download a new page each time they click to view previous or next posts, speed up your website by dynamically loading those posts on the same page using ajax. With minimal editing to your theme files. Works automatically on your single post page, any taxonomy or custom post type page, any archive page, and search pages without special configuration.

Single Post Loader has a light footprint that won't slow your website down. Buttons are lightly styled that won't override or interfere with your theme styles. The required javascript is tiny at only 766 bytes. Animated loading icons are rendered with SVG so your users don't have to download images. Finally advanced theme developers can optionally turn off plugin styles and javascript to bundle it with theme files, reduce HTTP requests that increase page load time, and completely customize the look and feel of the loading buttons.

#### Installation

Installing "Single Post Loader" can be done either by searching for "Single Post Loader" via the "Plugins > Add New" screen in your WordPress dashboard, or by using the following steps:

1. Download the plugin via WordPress.org
2. Upload the ZIP file through the 'Plugins > Add New > Upload' screen in your WordPress dashboard
3. Activate the plugin through the 'Plugins' menu in WordPress

Once plugin is activated, you need to update plugin settings under 'Settings > Single Post Loader'. On post page where want to use the loader, 
e.g on Bimber theme [/includes/front/post-template.php] you need to replace the following block of code in your template file like so.

``
function bimber_render_pagination_single( $args ) {
	wp_link_pages();
}
``

with

``
function bimber_render_pagination_single( $args ) {
	wp_link_pages();
	?>
    <div id="loader-container" style="display: block !important; width: 100%; height: 30px; text-align: center;"><span id="loader" class=""><?php echo show_loader_icon(); ?></span> </div>
    <?php
}
``
