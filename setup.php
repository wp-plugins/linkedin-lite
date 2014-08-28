<?php

/**
*	The number of days to keep the profile cache alive. See the linkedin api 
*	terms of use for current guidelines. Default is 1 day. 
*/
//$testcache = 30 / (60*60*24); //30 secs
define( 'SULI_CACHE_TIMEOUT', 1 ); //1 day


/**
*	WP options name for network settings. 
*/
define( 'LIL_NETWORK_OPTION_NAME', 'LiLITE_networkSettings' );


/**
*	WP options name for blog settings. 
*/
define( 'LIL_SETTINGS_OPTION_NAME', 'liLITE_settings' );


/**
*	WP options name for blog cache. 
*/
define( 'LIL_CACHE_OPTION_NAME', 'liLITE_cache' );


/**
*	WP blog admin menu slug.
*/
define( 'LIL_WPADMIN_MENU_SLUG', 'linkedin-lite' );


/**
*	Server path to the wp install. 
*/
$wp_install_url = network_site_url(); //falls back to site_url() if not multisite.
$path = explode( $_SERVER['HTTP_HOST'], $wp_install_url, 2 );
$WP_Install_Path = '/' . trim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . trim( $path[1], '/' ) . '/'; //trim everything then build right.
define( 'LIL_WP_INSTALL_PATH', $WP_Install_Path );		


/**
 *	Adds the Admin menu item(s).
 */
function SULI_addMenuPage () 
{		
	// Create main menu item
	$page_title="LinkedIn LITE Settings";
	$menu_title="LinkedIn LITE";
	$capability="manage_options"; //'manage_options' for administrators.
	$menu_slug = LIL_WPADMIN_MENU_SLUG;
	$function="SULI_adminPage";
	$iconURL= plugins_url() . '/linkedin-lite/images/li_icon.png';	
	
	//add the page and get a slug/id
	$adminPage = add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $iconURL );
	
	//use slug to reference the settings page for adding scripts
	add_action( 'admin_head-'. $adminPage, 'SULI_addToHeader' );
	
	global $liLITE;
	if ( $liLITE->isNetwork === false )
	{
		//add sub-pages
		//repeat of settings page (required)
		add_submenu_page( LIL_WPADMIN_MENU_SLUG, 'Settings | LinkedIn LITE', 'Settings', 'manage_options', LIL_WPADMIN_MENU_SLUG, 'SULI_adminPage' );
		//help page
		add_submenu_page( LIL_WPADMIN_MENU_SLUG, 'Help | LinkedIn LITE', 'Help', 'manage_options', LIL_WPADMIN_MENU_SLUG .'-help', 'SULI_helpPage' );
			
	}
	
	//hook into the links on the 'Plugins' list page next to the 'deactivate' link.
	add_filter( 'plugin_action_links', 'SULI_addSettingsLink', 10, 2 );
}


/**
*	Registers a new  
*	Network Admin page.
*/
function SULI_addNetworkAdminPage()
{
	// Create submenu item under settings
	$page_title = "LinkedIn LITE Network Settings";
	$menu_title = "LinkedIn LITE";
	$capability = "manage_network_options"; //'manage_options' for administrators.
	$menu_slug = 'linkedin-lite-network-settings';
	$function = "SULI_drawNetworkAdminPage";
	
	$page = add_submenu_page( 'settings.php', $page_title, $menu_title, $capability, $menu_slug, $function );
	add_action( 'admin_head-'. $page, 'SULI_addToHeader' );
}

  
/**
 *	Adds scripts to the 
 *	frontend.
 */
function SULI_addToHeader ()
{
	$pluginFolder = plugins_url( '', __FILE__ );
	wp_enqueue_style( 'linkedin_lite_css', $pluginFolder . '/css/admin.css' );
	wp_enqueue_script( 'jquery' );
}


/**
 *	Adds a settings link to the plugin listing
 *	on the 'Plugins' admin page.  
 */
function SULI_addSettingsLink ( $links, $file )
{ 
	if( $file == 'linkedin-lite/linkedin-lite.php' )
	{
		$settings_link = '<a href="admin.php?page=' . LIL_WPADMIN_MENU_SLUG . '">' . __('Settings') . '</a>';
		array_unshift( $links, $settings_link );
	}
	
	return $links;
}


/**
 *	Blog admin help page (hidden if using network)
 */
function SULI_helpPage ()
{
	global $liLITE;
	?>
	
	<div class="wrap">	
		
		<h2>Help | LinkedIn LITE &nbsp;	<span style="font-size:8px;">v <?php echo $liLITE->pluginSettings['plugin_version']; ?></span></h2>
		<p></p>
		<br />
		
		<?php
		echo LILdraw::helpInfo();
		
		?>
	</div>
	
	<?php

}

?>