<?php


$WPbooted = false;
$dbug_mode = false;
$action = '';


if ( isset( $_GET['state'] ) )
{

	$state = urldecode( $_GET['state'] );		//the state hash sent back from linkedin, it must match the saved one.
	$paths = explode( '--SULI--',  $state ); 	//paths should contain [0] - an md5 hash, [1] - wp install path, and [2] - blog id.	
	
	$wp_bootstrap = $paths[1] . 'wp-blog-header.php';
	$ok = include( $wp_bootstrap );
	if ( $ok === 1 )
	{
		$WPbooted = true;
	}
		
	
	if ( $WPbooted )
	{
		//include('setup.php');
		//include('class-core.php');
		//include('class-linkedin.php');
		//$liLITE = new liLITE_main();
		
		
		//try switch to correct blog
		if ( function_exists('switch_to_blog') )
		{
			$blogID = $paths[2];
			switch_to_blog( $blogID );
		}
		
		//grab the plugin options
		$pluginSettings = get_option( 'liLITE_settings' );
				
		//check the state hash against the stored version
		$stateHashOK = false;
		if ( $state === $pluginSettings['saved_state'] )
		{
			$stateHashOK = true;
		}
		
		//if all ok then try to get a token 
		if ( $stateHashOK && isset( $_GET['code'] ) )
		{			
			//include the plugin definitions
			include_once( $pluginSettings['file_include_path'] . '/setup.php' );
			
			//build the request url
			$tokenURL = 'https://www.linkedin.com/uas/oauth2/accessToken?';
			$qryStr = http_build_query( 
				array (
					'grant_type' => 'authorization_code',
					'code' => $_GET['code'],
					'redirect_uri' => $liLITE->receiverURL,
					'client_id' => $liLITE->APIkey,
					'client_secret' => $liLITE->APIsecret
				)
			);
			
			//tell streams to make a POST request
			$context = stream_context_create(
				array(
					'http' => array(
						'method' => 'POST'
					)
				)
			);
			
			//retrieve access token information
			$response = file_get_contents( $tokenURL . $qryStr, false, $context );
			$body = json_decode( $response );
			
			if ( isset( $body->access_token ) ) 
			{
				$pluginSettings['access_token'] = $body->access_token;
				$pluginSettings['length_secs'] = $body->expires_in;
				$pluginSettings['uts_received'] = time();
				update_option( LIL_SETTINGS_OPTION_NAME, $pluginSettings );
				
				$action = 'authSuccess';
			}
			elseif ( isset( $body->error ) )
			{
				//the linkedin api returned error (message is in $body->error_description)
				$action = 'authFail';
			}
			else
			{
				//unknown error
				$action = 'authFailUnknown';
			}
			
			
		} //end if stateHashOK
		
		
		if ( $dbug_mode )
		{
			echo "<pre>settings (post token request):\n";
			print_r($pluginSettings);
			echo '</pre>';
		}
		
	} //end if WPbooted

}  



if ( $stateHashOK ) //we passed the first check, so redirect back to the admin page with an action (triggers success/fail message).
{
	if ( ! $dbug_mode )
	{
		header( 'Location: ' . $pluginSettings['settings_page_url'] . '&action=' . $action );
	}
}
else //didn't get past security hash exchange
{
	
	if ( $WPbooted ) //can at least send them back to admin and trigger a message
	{
		header( 'Location: ' . $pluginSettings['settings_page_url'] . '&action=authFailUnknown' );
	}
	else
	{
		//just show an error message and a link to home page
		echo '<br /><br /><p style="text-align:center; font-size:110%;">Sorry, something went wrong :(<br /><a href="/">Home</a></p>';
	}
	
	
}

?>