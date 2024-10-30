<?php
/*
	Plugin Name: LandingCube for WordPress
	Plugin URI: https://landingcube.com/
	Description: Add LandingCube pages to your WordPress site in minutes.
	Text Domain: landingcube-wp
	Domain Path: /languages
	Author: LandingCube
	Plugin URI: https://landingcube.com/
	License: GPLv2
	Version: 1.0.9
*/

// BASIC SECURITY
defined( 'ABSPATH' ) or die( 'Unauthorized Access!' );

if ( !defined('FCA_LCWP_PLUGIN_DIR') ) {
	
	//DEFINE SOME USEFUL CONSTANTS
	define( 'FCA_LCWP_PLUGIN_VER', '1.0.9' );
	define( 'FCA_LCWP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	define( 'FCA_LCWP_PLUGINS_URL', plugins_url( '', __FILE__ ) );
	define( 'FCA_LCWP_PLUGIN_FILE', __FILE__ );
	define( 'FCA_LCWP_PLUGINS_BASENAME', plugin_basename(__FILE__) );
		
	//LOAD CORE
	include_once( FCA_LCWP_PLUGIN_DIR . '/includes/functions.php' );
	include_once( FCA_LCWP_PLUGIN_DIR . '/includes/custom-post-type.php' );
	include_once( FCA_LCWP_PLUGIN_DIR . '/includes/api.php' );
	include_once( FCA_LCWP_PLUGIN_DIR . '/includes/editor/editor.php' );
	include_once( FCA_LCWP_PLUGIN_DIR . '/includes/login/login-page.php' );
	include_once( FCA_LCWP_PLUGIN_DIR . '/includes/landing/landing.php' );

	function fca_lcwp_add_plugin_action_links( $links ) {
		
		$support_url = 'https://support.landingcube.com/';
		$new_url = admin_url( 'post-new.php?post_type=landingcube_page' );

		return array_merge( array(
			'addnew' => "<a href='$new_url' >" . __('Add New', 'landingcube-wp' ) . '</a>',
			'support' => "<a target='_blank' href='$support_url' >" . __('Support', 'landingcube-wp' ) . '</a>',
		), $links );
		
	}
	add_filter( 'plugin_action_links_' . FCA_LCWP_PLUGINS_BASENAME, 'fca_lcwp_add_plugin_action_links' );

}
