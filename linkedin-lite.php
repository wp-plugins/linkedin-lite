<?php
/*
Plugin Name: LinkedIn LITE
Plugin URI: http://www.cite.soton.ac.uk
Description: Display your LinkedIn profile on your blog
Version: 1.0
Author: Alex Furr, Simon Ward
Author URI: http://www.cite.soton.ac.uk
License: GPL
*/


$LLpath = dirname(__FILE__);
include_once( $LLpath . '/setup.php');
include_once( $LLpath . '/class-core.php');
include_once( $LLpath . '/class-linkedin.php');
include_once( $LLpath . '/class-draw.php');
include_once( $LLpath . '/widget.php');


if ( class_exists( "liLITE_main" ) )
{
	$liLITE = new liLITE_main();
	
	if ( isset( $liLITE ) )
	{
		$liLITE->fileIncludePath = $LLpath;
		
		if ( is_network_admin() )
		{
			include_once( $LLpath . '/network-admin.php');
			add_action('network_admin_menu', 'SULI_addNetworkAdminPage');
		}
		else if ( is_admin() )
		{
			include_once( $LLpath . '/admin.php');
			add_action('admin_menu', 'SULI_addMenuPage');
		}
		else
		{
			add_action( 'wp_head', array( &$liLITE, 'frontend_header_handler' ) );
			add_shortcode( 'linkedin-profile', array( &$liLITE, 'shortcode_handler' ) ); 
			add_shortcode( 'linkedin-profile-widget', array( &$liLITE, 'shortcode_handler_widget' ) );
		}
		
		function LIL_profileWidgetInit()
		{
			register_widget( 'LILwidget_Profile' );
		}
		add_action( 'widgets_init', 'LIL_profileWidgetInit' );
	}
} 
?>