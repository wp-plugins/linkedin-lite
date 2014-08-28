<?php
/**
*	Class liLITE_main
*	---
*
*/
class liLITE_main extends liLITE_core
{
	
	var $PluginFolder 		= '';
	var $blogReady			= false;

	
/**
*	Constructor
*/
	function liLITE_main ()
	{
		//run parent constructor
		parent::liLITE_core(); 
		
		//probably useful
		$this->PluginFolder = plugins_url( '', __FILE__ );
		
		//checks the stored options compatibility with this version, updates if needed.
		$this->getSettings();
		
		$this->blogReady = false;
		if ( $this->isNetwork === false )
		{
			$this->APIkey = $this->pluginSettings['blogApiKey'];
			$this->APIsecret = $this->pluginSettings['blogApiSecret'];
			
			if ( $this->pluginSettings['blogApiKey'] != '' && $this->pluginSettings['blogApiSecret'] != '' ) //good to go!
			{
				$this->blogReady = true;
			}
		}
	}
	


/**
*	WP Handlers
*/	
	
	/* Shortcode [linkedin-profile] */
	function shortcode_handler ( $atts, $content = null ) 
	{	
		$profileHTML = '';
		$profile = $this->tryGetUserProfile();
		
		if ( $this->usingCache )
		{
			$profileHTML .= '<div class="SULI-cacheNotice">Currently only a cached version of this profile is available.</div>';
		}
		$profileHTML .= LILdraw::publicProfile( 'page', $profile, $this->pluginSettings );
		return $profileHTML;
	}
	
	/* Shortcode [linkedin-profile-widget] */
	function shortcode_handler_widget ( $atts, $content = null )
	{	
		$profileHTML = '';
		$profile = $this->tryGetUserProfile();
		
		if ( $this->usingCache )
		{
			$profileHTML .= '<div class="SULI-cacheNoticeWidget">Currently only a cached version of this profile is available.</div>';
		}
		$profileHTML .= LILdraw::publicProfile( 'widget', $profile, $this->pluginSettings );
		return $profileHTML;
	}
	
	
	function frontend_header_handler ()
	{
		$pluginFolder = plugins_url( '', __FILE__ );
		wp_enqueue_style( 'linkedin_lite_frontend_css', $pluginFolder . '/css/frontend.css' );
	
	}
	
	
/**
*
*/	
	function clearToken ()
	{
		$this->pluginSettings['access_token'] = '';
		$this->pluginSettings['length_secs'] = '';
		$this->pluginSettings['uts_received'] = '';
		$this->pluginSettings['saved_state'] = '';
		$this->pluginSettings['li_primary_email'] = '';
		
		update_option( LIL_SETTINGS_OPTION_NAME, $this->pluginSettings );
	}
	

/**
*
*/	
	function setCache ( $data )
	{
		update_option( LIL_CACHE_OPTION_NAME, $data );
		
		$this->pluginSettings['uts_last_cached'] = time();
		update_option( LIL_SETTINGS_OPTION_NAME, $this->pluginSettings );
	}
	

/**
*
*/
	function checkCache ()
	{
		$cache = false;
		$stored = get_option( LIL_CACHE_OPTION_NAME );
		$reset = true;
				
		if ( $stored !== false && ! empty( $stored ) )
		{
			$cacheLife = SULI_CACHE_TIMEOUT * (60 * 60 * 24);
			$utsNow = time();
			$cacheAge = $utsNow - intval( $this->pluginSettings['uts_last_cached'] );
			
			if ( $cacheAge < $cacheLife ) //cache is available
			{
				$cache = $stored;
				$reset = false;
				$this->usingCache = true;
			}
		}
		
		if ( $reset )
		{
			$this->clearCache();
		}
		
		return ( $cache === false ) ? false : $cache;
	}
	

/**
*
*/	
	function clearCache ()
	{
		delete_option( LIL_CACHE_OPTION_NAME );
		$this->pluginSettings['uts_last_cached'] = '';
		update_option( LIL_SETTINGS_OPTION_NAME, $this->pluginSettings );
	}


/**
*
*/	
	function tryGetUserProfile ()
	{
		$profile = false;
		
		if ( $this->checkToken( $this->pluginSettings ) !== false )
		{
			$fieldString = $this->getAPIFieldSet();
			$profileInfo = $this->fetchProfileInfo ( 'GET', '/v1/people/~:(' . $fieldString . ')', $this->pluginSettings['access_token'] );
			
			if ( isset( $profileInfo->id ) && $profileInfo->id != '' )
			{
				$profile = $profileInfo;
				if ( $this->pluginSettings['allow_cache'] === 'y' )
				{
					$this->setCache( $profile );
				}
			}
		}
		
		if ( $profile === false )
		{
			if ( $this->pluginSettings['allow_cache'] === 'y' )
			{
				$profile = $this->checkCache();
			}
		}
		return $profile;
	}
	

/**
*
*/		
	function getSettings ()
	{
		$settings = array();

		$blogID = get_current_blog_id();
		$defaults = $this->getDefaultSettingsArray();
		$stored = get_option( LIL_SETTINGS_OPTION_NAME );
		
		if ( $stored === false ) //create defaults
		{
			update_option( LIL_SETTINGS_OPTION_NAME, $defaults );
			$settings = $defaults;
		}
		else //check they're up-to-date with current plugin
		{
			if ( $stored['plugin_version'] !== $this->pluginVersion )
			{
				foreach ( $defaults as $key => $option )
				{
					if ( array_key_exists( $key, $stored ) )
					{
						$settings[ $key ] = $stored[ $key ];
					}
					else
					{
						$settings[ $key ] = $option;
					}
				}
				
				$settings['plugin_version'] = $this->pluginVersion;
				update_option( LIL_SETTINGS_OPTION_NAME, $settings );
			}
			else
			{
				$settings = $stored;
			}
		}
		
		$this->pluginSettings = $settings;
		return $this->pluginSettings;
	}
	
	
/**
*	Checks current page against widget page-filter settings.
*	returns true if widget should be filtered out.	
*/	
	function page_filter( $list, $mode ) {
		
		$f = false;
		
		if ( !empty($list) )
		{
			$pagelist = explode( ",", $list );
			
			if ( !empty($pagelist) )
			{
				foreach ( $pagelist as $i => $id )
				{ 
					$pagelist[$i] = str_replace( " ", "", $id ); 
				}
			}
			if ( !is_singular() ) //look for 'index' or 'archive' or 'search'
			{ 
				if ( $mode == "include" )
				{
					if ( is_home() )
					{
						if ( strpos($list, "index") === false ) { $f = true; }
					}
					if ( is_archive() )
					{
						if ( strpos($list, "archive") === false ) { $f = true; }
					}
					if ( is_search() )
					{
						if ( strpos($list, "search") === false ) { $f = true; }
					}
				}
				if ( $mode == "exclude" )
				{
					if ( is_home() )
					{
						if ( strpos($list, "index") !== false ) { $f = true; }
					}
					if ( is_archive() )
					{
						if ( strpos($list, "archive") !== false ) { $f = true; }
					}
					if ( is_search() )
					{
						if ( strpos($list, "search") !== false ) { $f = true; }
					}
				}
			}
			else //check the id's against current page
			{ 
				global $post;
				$thisID = $post->ID;
				
				if ( $mode == "include" )
				{
					$f = true;
					
					foreach ( $pagelist as $i => $id )
					{
						if ( $id == $thisID ) { $f = false; }
					}
					
					if ( is_single() )
					{
						if ( strpos($list, "post") !== false )
						{
							$f = false;
						}
					}
				}
				if ( $mode == "exclude" )
				{
					foreach ( $pagelist as $i => $id )
					{
						if ( $id == $thisID ) { $f = true; }
					}
					
					if ( is_single() )
					{
						if ( strpos($list, "post") !== false )
						{
							$f = true;
						}
					}
				}
			}
		}
		
		return $f;
	}

} //end class

?>