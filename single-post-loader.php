<?php
/*
 * Plugin Name: Single Post Loader
 * Version: 1.0.0
 * Plugin URI: http://www.idiom.co/
 * Description: Dynamically loads the next post without pagination on single post page on infinite scroll.
 * Author: Olubunmi Tosin
 * Author URI: http://www.idiom.co/
 * Requires at least: 4.0
 * Tested up to: 4.9
 *
 * Text Domain: single-post-loader
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Olubunnmi Tosin
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

require ('includes/class-single-post-loader.php');
require ('includes/class-single-post-loader-settings.php');

//Load Admin Class
require ('includes/class-single-post-loader-admin.php');


/**
 * Returns the main instance of Easy_Load_More to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Easy_Load_More
 */
function Single_Post_Loader () {
	$instance = Single_Post_Loader::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = Single_Post_Loader_Settings::instance( $instance );
	}

	return $instance;
}

Single_Post_Loader();

function show_loader_icon()
{
	return Single_Post_Loader()->build_animation();
}