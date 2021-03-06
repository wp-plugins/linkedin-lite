<?php
/**
*	Writes the main admin settings page.
*	---
*/

function SULI_adminPage ()
{
	global $liLITE;
	$message = false;
	$preview = false;
	$openTab = 3;
	
	
	//handle page actions.
	if ( isset( $_GET['action'] ) ) 
	{
		if ( 'revoke' === $_GET['action'] )
		{
			$liLITE->clearToken();
			$liLITE->clearCache();
			$message = '<p>Authorisation revoked.</p>';
			$openTab = 3;
		}
		else if ( 'authSuccess' === $_GET['action'] )
		{
			$message = '<p>Successfully Authorised!</p>';
			$openTab = 3;
		}
		else if ( 'authFail' === $_GET['action'] )
		{
			$message = '<p>Something went wrong, please check any settings and try again.</p>';
			$openTab = 3;
		}
		else if ( 'authFailUnknown' === $_GET['action'] )
		{
			$message = '<p>Something went wrong, please try later or contact an administrator.</p>';
			$openTab = 3;
		}
		else if ( 'deleteCache' === $_GET['action'] )
		{
			$liLITE->clearCache();
			$message = '<p>Cache deleted.</p>';
			$openTab = 2;
		}
	}
	else if ( isset( $_POST['updatePluginOptions'] ) )
	{
		$liLITE->pluginSettings['allow_cache'] = ( isset( $_POST['allow_cache'] ) ) ? 'y' : 'n';		
		update_option( LIL_SETTINGS_OPTION_NAME, $liLITE->pluginSettings );
		$message = '<p>Options updated.</p>';
		$openTab = 2;
	}
	else if ( isset( $_POST['updatePageProfileOptions'] ) )
	{
		$liLITE->pluginSettings['page_profile_fields'] = ( isset( $_POST['PagefieldCheckers'] ) ) ? $_POST['PagefieldCheckers'] : '';
		update_option( LIL_SETTINGS_OPTION_NAME, $liLITE->pluginSettings );
		$message = '<p>Page Profile updated.</p>';
		$openTab = 0;
	}
	else if ( isset( $_POST['updateWidgetProfileOptions'] ) )
	{
		$liLITE->pluginSettings['widget_profile_fields'] = ( isset( $_POST['WidgetfieldCheckers'] ) ) ? $_POST['WidgetfieldCheckers'] : '';
		update_option( LIL_SETTINGS_OPTION_NAME, $liLITE->pluginSettings );
		$message = '<p>Widget Profile updated.</p>';
		$openTab = 1;
	}
	else if ( isset( $_POST['updateBlogAPISettings'] ) )
	{
		$liLITE->pluginSettings['blogApiKey'] = ( isset( $_POST['blogApiKey'] ) ) ? $_POST['blogApiKey'] : '';
		$liLITE->pluginSettings['blogApiSecret'] = ( isset( $_POST['blogApiSecret'] ) ) ? $_POST['blogApiSecret'] : '';		
		update_option( LIL_SETTINGS_OPTION_NAME, $liLITE->pluginSettings );
		$message = '<p>API settings updated.</p>';
		
		//if API settings were entered then load with auth tab (3) open, otherwise stay on the API tab.
		$openTab = ( $liLITE->pluginSettings['blogApiKey'] !== '' && $liLITE->pluginSettings['blogApiSecret'] !== '' ) ? 3 : 4; 
	}
	
	$liLITE->liLITE_main();
	
	
	//Store the settings page url in options if it's not identical to the stored one. 
	//Can (will) be done here as the user has to visit in order to authenticate themselves.
	$settingsPageURL = $liLITE->domainRootURL . $_SERVER["REQUEST_URI"];
	$settingsPageURL = explode( '?', $settingsPageURL, 2 ); //remove any gets
	$settingsPageURL = $settingsPageURL[0] . '?page=' . LIL_WPADMIN_MENU_SLUG; //build url
	
	if ( $liLITE->pluginSettings['settings_page_url'] !== $settingsPageURL ) //save it
	{
		$liLITE->pluginSettings['settings_page_url'] = $settingsPageURL;
		update_option( LIL_SETTINGS_OPTION_NAME, $liLITE->pluginSettings );
	}
	
	
	//show feedback message
	if ( $message !== false )
	{
		echo '<div class="updated" style="max-width:518px;">' . $message . '</div>';
	}
	
	
	//try fetch profile AFTER any actions!
	$profile = $liLITE->tryGetUserProfile(); 
	?>

	<div class="wrap">	
		
		<h2>LinkedIn LITE &nbsp;	<span style="font-size:8px;">v <?php echo $liLITE->pluginSettings['plugin_version']; ?></span></h2>
		<p>This plugin lets you display your LinkedIn profile publicly on your blog.</p>
		<br />
	
		<form method="post" action="<?php echo $liLITE->pluginSettings['settings_page_url']; ?>">
			

		<!-- TAB Headers ..................... -->	

			<div class="mp3j-tabbuttons-wrap">
			<?php
			if ( $liLITE->isNetwork === false ) //display key/secret input fields (eg. for single installs).
			{
			?>
				<div class="mp3j-tabbutton" id="mp3j_tabbutton_4">API Settings</div>
			<?php
			}
			?>
				<div class="mp3j-tabbutton" id="mp3j_tabbutton_3">Authentication</div>
				<div class="mp3j-tabbutton" id="mp3j_tabbutton_0">Page Profile</div>
				<div class="mp3j-tabbutton" id="mp3j_tabbutton_1">Widget Profile</div>                
				<div class="mp3j-tabbutton" id="mp3j_tabbutton_2">Options</div>
				
				<br class="clearB" />
			</div>
			<div class="mp3j-tabs-wrap">
			
		
			<!-- TAB 0 Page Profile .......................... -->
				<div class="mp3j-tab" id="mp3j_tab_0">
                	
					<p style="font-size:16px;">Page Profile</p>
					
					<span class="description">Tick the parts of your profile that you want to show.
                    You may have to adjust your privacy settings within LinkedIn in order for some of them to display.</span>
					<br /><br />
								
					<?php
					LILdraw::adminProfileTab( 'Page', $profile, $liLITE->pluginSettings );
					
					echo '<br /><br /><input type="submit" name="updatePageProfileOptions" class="button-primary" value="Update Page Profile" />';
					
					?>				
					
				</div> <!-- close -->
 
 
			<!-- TAB 1 Widget Profile .......................... -->               
				<div class="mp3j-tab" id="mp3j_tab_1">
					
					<p style="font-size:16px;">Widget Profile</p>
					
					<span class="description">As the previous page, but for the widget fields</span>
					<br /><br />
                
					<?php 
					LILdraw::adminProfileTab( 'Widget', $profile, $liLITE->pluginSettings ); 
					
					echo '<br /><br /><input type="submit" name="updateWidgetProfileOptions" class="button-primary" value="Update Widget Profile" />';
					?>
				
				</div><!-- close -->

	
			<!-- TAB 2 Options .......................... -->
				<div class="mp3j-tab" id="mp3j_tab_2">
					
					<p style="font-size:16px;">Profile Caching</p>
					
					<span class="description">When this option is ticked the plugin will attempt to use a cached copy of your profile should the API connection fail.</span>
					<br /><br /><br />
					
					<input type="checkbox" value="y" id="allow_cache" name="allow_cache" <?php if ( $liLITE->pluginSettings['allow_cache'] === 'y' ) { echo 'checked="checked" '; } ?>/>
					<label for="allow_cache">Allow caching of profile</label> 
					<br /><br />
					
					<?php
					if (  $liLITE->checkCache() !== false )
					{
						echo '<a href="' . $liLITE->pluginSettings['settings_page_url'] . '&action=deleteCache" class="button-secondary">Delete Cache</a>';
					}
					else
					{
						echo '<span class="description">Cache is empty</span>';
					}
					?>
					
					<br /><br /><br /><br />
					<input type="submit" name="updatePluginOptions" class="button-primary" value="Update Plugin Options" />
					
                </div><!-- close -->                
					
		 
			<!-- TAB 3 Authorisation .......................... -->
				<div class="mp3j-tab" id="mp3j_tab_3">
                    
					<p style="font-size:16px;">Authentication</p>
					
					<?php
					$blogID = get_current_blog_id();
					$authURL = $liLITE->makeAuthURL( $blogID );
					$liLITE->pluginSettings['saved_state'] = $authURL['state'];
					update_option( LIL_SETTINGS_OPTION_NAME, $liLITE->pluginSettings );
					
					if ( $liLITE->pluginSettings['access_token'] === '' ) 
					{ //don't have a token
					
						if ( $liLITE->blogReady === false && $liLITE->isNetwork === false )
						{
							echo 'You must complete your API setup before you can authorise your account!';
							echo '<br />See the <a href="#" onclick="SULI_ADMIN.changeTab(4);">API Settings</a> tab for help.';
							$openTab = 4;
						}
						else
						{
							if ( $liLITE->isNetwork && ! $liLITE->networkReady )
							{
								echo '<p>Sorry, the network is currently under maintenance, please try again in a while.';
							}
							else
							{
								echo '<a href="' . $authURL['url'] . '" class="button-primary fL" style="margin:5px 16px 0 0;">Authorise</a>';
								echo '<p class="description" style="margin:0 0 0 0;">The plugin needs your permission to display your LinkedIn profile, click<br />"Authorise" to grant it, you will need your LinkedIn login credentials.</p>';
							}
						}
					}
					else 
					{ //got a token, check it's valid
					
						$tokenLife = $liLITE->checkToken( $liLITE->pluginSettings, 'display' );
						
						if ( $tokenLife !== false ) //it's still valid.
						{	
							echo '<p class="tick">';
							echo '<strong>Plugin Authorised</strong> &nbsp;';
							echo '<span class="description">Valid for ' . $tokenLife . ' days</span> &nbsp; &nbsp; &nbsp;';
							$authButtonText = 'Refresh';
							$preview = true;
						}
						else //it's expired
						{
							echo '<p>';
							echo 'The current authorisation period has expired, please re-authorise to continue displaying your<br />up-to-date profile, you will need your LinkedIn login credentials:';
							$authButtonText = 'Re-Authorise';
						}
						//show re-auth and revoke links.
						echo '<a href="' . $authURL['url'] . '" class="button-primary">' . $authButtonText . '</a>';
						echo '&nbsp; <a href="' . $liLITE->pluginSettings['settings_page_url'] . '&action=revoke" class="button-primary">Revoke</a>';
						echo '</p>';
					
					
						if ( $profile !== false ) //show their profile pic and a few details.
						{
							echo '<img src="' . $profile->pictureUrl . '" class="suli-profilepic fL" />';
							echo '<p class="description" style="font-size:15px; margin:0 0 0 0;">' . $profile->formattedName . '</p>';
							echo '<p style="margin:0 0 0 0;"><strong>' . $profile->headline . '</strong></p>';
							echo '<p style="font-size:11px; margin:27px 0 0 0; color:#aaa;">LinkedIn Account ID: ' . $profile->id . '</p>';
						}
						echo '<br class="clearL" />';
					}
					?>       
						
				</div><!-- close -->
				
				
			<?php
			if ( $liLITE->isNetwork === false ) //display key/secret input fields (eg. for single installs).
			{
			?>
				<!-- TAB 4 API Setup .......................... -->
				<div class="mp3j-tab" id="mp3j_tab_4">
					
					<?php
					if ( $liLITE->blogReady === false )
					{
						echo 'You need to enter some API keys';
						
						echo '<br />the redirect url: ' . $liLITE->receiverURL;
						
					}
					?>
					
					<table style="margin-left:26px;">
						<tr>
							<td><p><label for="blogApiKey">API Key </label></p></td>
							<td><input type="text" name="blogApiKey" id="blogApiKey" value="<?php echo ( $liLITE->pluginSettings !== false ? $liLITE->pluginSettings['blogApiKey'] : '' ); ?>" /></td>
						</tr>
						<tr>
							<td><p><label for="blogApiSecret">API Secret </label></p></td>
							<td><input type="text" name="blogApiSecret" id="blogApiSecret" value="<?php echo ( $liLITE->pluginSettings !== false ? $liLITE->pluginSettings['blogApiSecret'] : '' ); ?>" /></td>
						</tr>
					</table>
					
					<input type="submit" name="updateBlogAPISettings" class="button-primary" value="Update API Settings" />
				
				</div><!-- close -->
			<?php
			}
			?>
			
			</div><!-- close .mp3j-tabs-wrap -->
			<!-- End Tabs ..................... -->										<?php						echo '<br /><br /><br /><h3>Displaying Your Profile</h3>';			echo '<p>You can display your profile in 2 ways:</p>';			echo '<h4>1. Using the shortcode <code>[linkedin-profile]</code> in a page or post.</h4>';			echo '<h4>2. Using the Widget in a sidebar area.</h4>';						?>						

		</form>
	</div><!-- close .wrap -->
	
	
	<!-- Tabs JS -->
	<script type="text/javascript">
		
		var SULI_ADMIN = {
	
			openTab: <?php echo $openTab; ?>,
			
			add_tab_listener: function ( j ) {
				var that = this;
				jQuery('#mp3j_tabbutton_' + j).click( function (e) {
					that.changeTab( j );
				});
			},
			
			changeTab: function ( j ) {
				if ( j !== this.openTab ) {
					jQuery('#mp3j_tab_' + this.openTab).hide();
					jQuery('#mp3j_tabbutton_' + this.openTab).removeClass('active-tab');
					jQuery('#mp3j_tab_' + j).show();
					jQuery('#mp3j_tabbutton_' + j).addClass('active-tab');
					this.openTab = j;
				}
			},
			
			init: function () {
				var that = this;
				jQuery( '.mp3j-tabbutton').each( function ( j ) {
					that.add_tab_listener( j );
					if ( j !== that.openTab ) {
						jQuery('#mp3j_tab_' + j ).hide();
					}
				});
				jQuery('#mp3j_tabbutton_' + this.openTab ).addClass('active-tab');
			}
		};
	
	</script> 

	<!-- On load -->
	<script> 
		jQuery(document).ready( function () {
			SULI_ADMIN.init();
		});
	</script>   
	
<?php

}
?>