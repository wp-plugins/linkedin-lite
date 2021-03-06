<?php
/**
*	Class liLITE_core
*	---
*	
*/
class liLITE_core
{
	
	/* ! UPDATE ME ON RELEASE
	// (fires an auto-compatibility routine, use it to
	// get new options in to saved settings. */
	var $pluginVersion 		= '1.0.0';    
	// -------------------------------
	
	var $domainRootURL = '';
	var $receiverURL = '';
	var $pluginSettings = array();
	var $fileIncludePath = '';
	
	var $APIkey = '';
	var $APIsecret = '';
	
	var $networkSettings = false;
	var $isNetwork = false; 		//true when network checkbox is ticked on network admin.
	var $networkReady = false; 		//true when network ticked AND both keys are present in settings.
	
	var $usingCache = false;


/**
*	Constructor function, sets up some flags and paths that will be used.
*/	
	function liLITE_core ()
	{	
		$path = explode( $_SERVER['DOCUMENT_ROOT'], dirname(__FILE__), 2 );
		$path = trim( $path[1], '/' );
		$protocol = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ) ? 'https' : 'http';
		
		$this->domainRootURL = trim( $protocol . '://' . $_SERVER['HTTP_HOST'], '/' );
		$this->receiverURL = $this->domainRootURL . '/' . $path . '/linkedin_receiver.php';
		
		$this->isNetwork = false;
		$this->networkReady = false;
		$NWsettings = $this->getNetworkSettings();
		if ( is_array($NWsettings) )
		{
			if ( $NWsettings['ApiRedirectUrl'] !== '' )
			{
				$this->receiverURL = $NWsettings['ApiRedirectUrl'];
			}
			
			if ( $NWsettings['useNetworkKeys'] === 'true' )
			{
				$this->APIkey = $NWsettings['ApiKey'];
				$this->APIsecret = $NWsettings['ApiSecret'];
				$this->isNetwork = true;			
			
				if ( $NWsettings['ApiKey'] != '' && $NWsettings['ApiSecret'] != '' )
				{
					$this->networkReady = true;
				}
			}
		}
		
		//this gets stored in the plugin options, for use by the 
		//linkedin_receiver.php file from outside of the install
		$this->fileIncludePath = dirname(__FILE__);
	}
	
	
/**
*	Returns the saved network settings (stored in root blog), or false if none found.
*/
	function getNetworkSettings ()
	{
		$currentBlog = get_current_blog_id();
		if ( function_exists('switch_to_blog') )
		{
			switch_to_blog( 1 );
		}
		$this->networkSettings = get_option( LIL_NETWORK_OPTION_NAME );
		if ( function_exists('switch_to_blog') )
		{
			switch_to_blog( $currentBlog );
		}
		return $this->networkSettings;
	}
	
	
/**
*	Returns a default set of plugin options.
*/	
	function getDefaultSettingsArray ()
	{
		$blogID = get_current_blog_id();
		$pageProfileFields = $this->tickAllProfileFields();
		
		return array(
			'access_token' 			=> '',
			'length_secs'			=> '',
			'uts_received'			=> '',
			'saved_state'			=> '',
			'li_primary_email'		=> '',
			'allow_cache'			=> 'y',
			'uts_last_cached'		=> '',
			'settings_page_url'		=> '',
			'page_profile_fields'	=> $pageProfileFields,
			'widget_profile_fields'	=> array( 'formattedName' => 'y', 'pictureUrl' => 'y', 'headline' => 'y' ),
			'blog_id'				=> $blogID,
			'blogApiKey'			=> '',
			'blogApiSecret'			=> '',
			'file_include_path'		=> $this->fileIncludePath,
			'plugin_version'		=> $this->pluginVersion
		);
	}
	

/**
*	Makes the authorisation url query string.
*/	
	function makeAuthURL ( $senderID )
	{
		//the linkedin oauth url
		$authURL = 'https://www.linkedin.com/uas/oauth2/authorization?';
		
		//make a 'state' to send over, this will be sent back by the api, along with a request-code (or error).
		//the state should be identical when returned, and it is checked before trying to exchange the request-code 
		//for an oAuth token.
		$randomstring = bin2hex( openssl_random_pseudo_bytes( 16 ) );
		$hash = hash( 'md5', $randomstring );
		$state = $hash . '--SULI--' . LIL_WP_INSTALL_PATH . '--SULI--' . $senderID; //add the bootstrapping path, and the sender id.
		
		//build the get params
		$qryStr = http_build_query( //url encoded get string
			array (
				'response_type'	=> 'code',
				'client_id' 	=> $this->APIkey,
				'scope'			=> 'r_fullprofile r_emailaddress',
				'state'			=> $state,
				'redirect_uri'	=> $this->receiverURL
			)
		);
		
		return array( 
			'url' 	=> $authURL . $qryStr, 
			'state' => $state
		);
	}
	
	
/**
*	
*/	
	function tickAllProfileFields ( $username='', $profile='', $settings='' )
	{
		//get fields array
		$APIfields = $this->getAPIFieldSet('array');
		
		$pageProfileFields = array();
		foreach ( $APIfields as $key => $names )
		{
			$pageProfileFields[ $names['camel'] ] = 'y'; 
		}
	
		return $pageProfileFields;
	}

	
/**
*
*/	
	function fetchProfileInfo ( $method, $resourceType, $accessToken )
	{
		//the linkedin API resource request url
		$url = 'https://api.linkedin.com' . $resourceType . '?';
		
		//build the API query
		$getStr = http_build_query( 
			array (
				'oauth2_access_token'	=> $accessToken,
				'format' 				=> 'json'
			)
		);
					
		// Tell streams to make a (GET, POST, PUT, or DELETE) request
		$context = stream_context_create(
			array(
				'http' => array( 'method' => $method )
			)
		);
	 
		$response = @file_get_contents( $url . $getStr, false, $context ); // !! suppressing errors
		return json_decode( $response );
	}

	
/**
*
*/
	function getAPIFieldSet ( $format = 'string' )
	{
		
		$apiFields = array(
		
			'picture-url' 				=> array( 	'camel' => 'pictureUrl', 			'nice' => 'Profile Picture' 			),
			'formatted-name' 			=> array( 	'camel' => 'formattedName', 		'nice' => 'Full Name' 					),
			'headline' 					=> array( 	'camel' => 'headline', 				'nice' => 'Job Title / Headline' 		),
			'industry'					=> array( 	'camel' => 'industry', 				'nice' => 'Industry' 					),
			'location'					=> array( 	'camel' => 'location', 				'nice' => 'Location' 					),
			'email-address' 			=> array( 	'camel' => 'emailAddress', 			'nice' => 'Primary Email Address' 		),			
			'summary' 					=> array( 	'camel' => 'summary', 				'nice' => 'Summary' 					),			
			'skills' 					=> array( 	'camel' => 'skills', 				'nice' => 'Skills' 						),
			//'specialties' 				=> array( 	'camel' => 'specialties', 			'nice' => 'Specialities' 				),
			'positions' 				=> array( 	'camel' => 'positions', 			'nice' => 'Positions' 					),
			'educations' 				=> array( 	'camel' => 'educations', 			'nice' => 'Education' 					),
			'certifications' 			=> array( 	'camel' => 'certifications', 		'nice' => 'Certifications' 				),
			'courses' 					=> array( 	'camel' => 'courses', 				'nice' => 'Courses' 					),
			'languages' 				=> array( 	'camel' => 'languages', 			'nice' => 'Languages' 					),
			'volunteer' 				=> array( 	'camel' => 'volunteer', 			'nice' => 'Volunteer Activities' 		),
			//'associations' 				=> array( 	'camel' => 'associations', 			'nice' => 'Associations' 				),
			'interests' 				=> array( 	'camel' => 'interests', 			'nice' => 'Interests' 					),
			'publications' 				=> array( 	'camel' => 'publications', 			'nice' => 'Publications' 				),
			'patents' 					=> array( 	'camel' => 'patents', 				'nice' => 'Patents' 					),
			'honors-awards'				=> array( 	'camel' => 'honorsAwards', 			'nice' => 'Honours Awards' 				),
			'public-profile-url' 		=> array( 	'camel' => 'publicProfileUrl', 		'nice' => 'Public LinkedIn Profile'		),
			'id' 						=> array( 	'camel' => 'id', 					'nice' => 'LinkedIn Account ID' 		)
		
		);
		
		$fieldCount = count( $apiFields );
		
		$fieldstring = '';
		$j = 1;
		foreach ( $apiFields as $key => $val )
		{
			$field = ( $val['camel'] === 'pictureUrl' ) ? $val['camel'] . ';secure=true' : $val['camel']; //just need to tweak the image field so it returns an https url!
			$fieldstring .= $field . ( $j != $fieldCount ? ',' : '' );
			$j++;
		}
		
		return ( $format === 'string' ) ? $fieldstring : $apiFields;
	}

	
/**
*
*
*/
	function checkToken ( $settings, $format = '' )
	{
		$valid = false;
		
		if ( $settings['access_token'] !== '' )
		{		
			$utsNow = time();
			$tokendate = intval( $settings['uts_received'] );
			$tokenAge = $utsNow - $tokendate;
				
			$tokenLife = intval( $settings['length_secs'] );
			$displayLife = round( $tokenLife*(1/86400), 1 );
			
			if ( $tokenAge < $tokenLife ) //it's still valid.
			{
				$valid = ( $format === 'display' ) ? round( ($tokenLife - $tokenAge)*(1/86400), 1 ) : $tokenLife - $tokenAge;
			}
		}
		
		return $valid;
	}

} //end class
?>