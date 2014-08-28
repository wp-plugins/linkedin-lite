<?php
/**
*	--- NETWORK ADMIN SETTINGS PAGE --------------------------------------------
*
*	These settings are used if network admin wants to run a multisite network on 
*	a single API key/secret pair.
*/

function SULI_drawNetworkAdminPage()
{
	if ( !current_user_can( 'manage_network_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}	
	
	$message = false;
	
	if ( isset($_POST['updateSettings']) )
	{
		$ApiKey = ( isset($_POST['ApiKey']) ) ? $_POST['ApiKey'] : '';
		$ApiSecret = ( isset($_POST['ApiSecret']) ) ? $_POST['ApiSecret'] : '';
		$ApiRedirectUrl = ( isset($_POST['ApiRedirectUrl']) ) ? $_POST['ApiRedirectUrl'] : '';
		$useNetworkKeys = ( isset($_POST['useNetworkKeys']) ) ? $_POST['useNetworkKeys'] : '';
	
		$settings = array(
			'ApiKey' => $ApiKey,
			'ApiSecret' => $ApiSecret,
			'ApiRedirectUrl' => $ApiRedirectUrl,
			'useNetworkKeys' => $useNetworkKeys
		);
		
		update_option( LIL_NETWORK_OPTION_NAME, $settings );
		$message = 'Settings Updated';
	}
	else if ( isset($_POST['deleteSettings']) )
	{
		delete_option( LIL_NETWORK_OPTION_NAME );
		$message = 'Settings record deleted.';
	}
	?>
	
	
	<div class="wrap">

		<?php
		if ( $message !== false )
		{
			echo '<div class="updated">' . $message . '</div>';
		}
		?>
	
		<h2>LinkedIn Lite Network Settings</h2>
		<br />
		
		<?php
		$settings = get_option( LIL_NETWORK_OPTION_NAME );
		$isActive = false;
		$activeKeysMessage = '';
		
		//£addClass = ' class="feint"';
		if ( $settings['useNetworkKeys'] == 'true' )
		{
			if ( $settings['ApiKey'] != '' && $settings['ApiSecret'] != '' )
			{
				$activeKeysMessage = 'Network Keys Are Active';
				$isActive = true;
				//$addClass = '';
			}
		}
		?>
		
		
		<style>
			.feint { opacity:0.3; alpha:opacity(50); }
		</style>
		
		<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		
			<div class="mp3j-tabbuttons-wrap">
				<div class="mp3j-tabbutton" id="mp3j_tabbutton_0">API Settings</div>
				<div class="mp3j-tabbutton" id="mp3j_tabbutton_1">Redirection</div>
				<div class="mp3j-tabbutton" id="mp3j_tabbutton_2">Help</div>
				<br class="clearB" />
			</div>
			<div class="mp3j-tabs-wrap">
				
				<!-- tab 0 -->
				<div class="mp3j-tab" id="mp3j_tab_0">
					<p style="font-size:16px;">API Key and Secret &nbsp; <span style="color:#0b0; font-size:12px; font-weight:700;"><?php echo$activeKeysMessage; ?></span></p>
			
					<p>Use these settings if you want to run a multisite network on a single API key/secret pair.</p>
					<br />
					<p><input type="checkbox" name="useNetworkKeys" id="useNetworkKeys" value="true" <?php if ( $settings !== false && $settings['useNetworkKeys'] === 'true' ) { echo 'CHECKED'; } ?>/>
						<label for="useNetworkKeys">Use network-wide API key/secret.</label>
						<br /><span class="description" style="margin-left:22px; color:#aaa;">Ticking this will activate the keys entered below for all blogs on this multisite network 
							(Blog admins will not see any API options in their settings).</span></p>
					
					<table style="margin-left:26px;" id="keysTable">
						<tr>
							<td><p><label for="ApiKey">API Key </label></p></td>
							<td><input type="text" name="ApiKey" id="ApiKey" value="<?php echo ( $settings !== false ? $settings['ApiKey'] : '' ); ?>" /></td>
						</tr>
						<tr>
							<td><p><label for="ApiSecret">API Secret </label></p></td>
							<td><input type="text" name="ApiSecret" id="ApiSecret" value="<?php echo ( $settings !== false ? $settings['ApiSecret'] : '' ); ?>" /></td>
						</tr>
					</table>
				</div>
				
				
				<!-- tab 1 -->
				<div class="mp3j-tab" id="mp3j_tab_1">
					<p style="font-size:16px;">API Redirection (Optional - for advanced users)</p>
					<p>Use this field if you are an advanced user or theme editor and want to specify your own custom receiver file.<br/>
                    <b>Leave this field blank to use the plugin supplied one (recommended) .</b></p><br/>
					<label for="">Receiver URL:</label> <input type="text" name="ApiRedirectUrl" id="ApiRedirectUrl" value="<?php echo ( $settings !== false ? $settings['ApiRedirectUrl'] : '' ); ?>" style="width:480px;" />
					<p><i>NOTE: If you change this setting you will also need to update your LinkedIn Developer API settings.</i></p>
				</div>
				
				
				<!-- tab 2 -->
				<div class="mp3j-tab" id="mp3j_tab_2">
					<?php echo LILdraw::helpInfo(); ?>
				</div>
			
			</div>
			<br /><br />
			
			<?php $buttontext = ( $settings !== false ) ? 'Update' : 'Save'; ?>
			<input type="submit" name="updateSettings" id="updateSettings" value="<?php echo $buttontext; ?> All" class="button-primary" />
		
			<?php
			if ( $settings !== false ) 
			{ 
			?>	
				&nbsp;&nbsp;<input type="submit" name="deleteSettings" id="deleteSettings" value="Delete All Settings" class="button-primary" />
			<?php 
			} 
			?>
			
		</form>
	</div>
	
	
	<!-- Tabs JS -->
	<script type="text/javascript">
		
		var SULI_ADMIN = {
	
			openTab: 0,
			
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
				jQuery('#mp3j_tabbutton_' + this.openTab).addClass('active-tab');
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