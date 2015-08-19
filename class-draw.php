<?php

class LILdraw
{

	function adminProfileTab ( $baseID, $profileData, $settings, $showEmptyFields = true )
	{
		$APIfields = liLITE_core::getAPIFieldSet( 'array' );
		
		$tickedFields = ( $baseID === 'Widget' ) ? $settings['widget_profile_fields'] : $settings['page_profile_fields'];
		
		echo '<div class="adminProfileWrapper">';
		echo '<table>';
		echo '<tr>';
		
		//make table header, with a tick-all checker. 
		echo '<td style="width:30px; vertical-align:top; border-bottom:solid 1px #ccc;">';
		echo '<input type="checkbox" id="' . $baseID . 'tickAll"><br /><br />';
		echo '</td>';
		
		echo '<td style="width:170px; vertical-align:top; border-bottom:solid 1px #ccc;">';
		echo '<label for="' . $baseID . 'tickAll"><b>Select / Deselect All</b></label>';
		echo '</td>';
		
		echo '<td style="vertical-align:top;">';
		echo '</td>';
		
		echo '</tr>';					
		
		
		//make the profile field rows, each has a checker and a profile part.
		foreach ( $APIfields as $field => $names )
		{
			$fieldID = $names['camel'];
			$fieldName = $names['nice']; //display label
			$fieldValue = self::getProfileItem( $fieldID, $profileData, $fieldName ); //the profile part
			
			if( $fieldID <> "id" ) //don't show the id field.
			{
				if ( $fieldValue !== '' || $showEmptyFields === true )
				{
					echo '<tr>';
					$lightCss = '';
					$checked = ( isset( $tickedFields[ $fieldID ] ) ) ? 'checked="checked" ' : '';
					$displayCSS = ( isset( $tickedFields[ $fieldID ] ) ) ? '' : 'display:none; ';
					
					if($fieldValue=="")
					{
						$checked="DISABLED";
						$lightCss = "greyOut";
					}
					
					echo '<td style="width:30px; vertical-align:top;">';
					echo '<input type="checkbox" value="y" name="' . $baseID . 'fieldCheckers[' . $fieldID . ']" title="checkerDiv_' . $baseID . $fieldID . '" id="' . $baseID . $fieldID . '" class="' . $baseID . 'field-checker" ' . $checked . '/>';
					echo '</td>';
					
					echo '<td style="width:150px; vertical-align:top;">';
					echo '<label for="' . $baseID . $fieldID . '" style="font-weight:500;" class="' . $lightCss . '">' . $fieldName . '</label>';
					echo '</td>';
					
					echo '<td style="vertical-align:top;">';
					echo '<div id="checkerDiv_' . $baseID . $fieldID . '" style="' . $displayCSS . '" class="' . $baseID . 'field-checker-div">' . $fieldValue . '</div>';
					echo '</td>';
					
					echo '</tr>';
					
				}	
			}
		}
		
		echo '</table>';
		echo '</div>';
		?>
		
		<script> 
			jQuery( document ).ready( function () {
				
				//toggles the partner div upon checker change
				jQuery('.<?php echo $baseID; ?>field-checker').on( 'change', function ( e ) {
					
					var checked = jQuery( this ).is( ':checked' );
					var id = jQuery( this ).attr('id');
					jQuery( '#checkerDiv_' + id ).toggle();

				});
				
				//select/deselect all, only changes enabled checkers.
				jQuery('#<?php echo $baseID; ?>tickAll').on( 'change', function ( e ) {

					jQuery('.<?php echo $baseID; ?>field-checker').each( function () {
					
						var checked = jQuery( '#<?php echo $baseID; ?>tickAll' ).is( ':checked' );
						var disabled = jQuery( this ).prop( 'disabled' );
						
						if ( ! disabled ) {
							jQuery( this ).prop( 'checked', checked );						
							var divID = jQuery( this ).attr( 'title' );
							
							if ( checked ) {
								jQuery( '#' + divID ).show();
							} else {
								jQuery( '#' + divID ).hide();
							}
						}
					});	
				
				});
			
			});
		</script>

	<?php
	}
	
	
	function publicProfile ( $type, $profileData, $settings )
	{
		$output = '';
		
		if ( $profileData === false || empty( $profileData ) )
		{
			return;
		}
		
		//grab the fieldset, and the user's ticked array.
		$APIfields = liLITE_core::getAPIFieldSet( 'array' );
		$checkedFields = $settings[ $type . '_profile_fields' ];
		
		if ( is_array( $checkedFields ) )
		{
			$output .= '<div class="' . $type . 'PublicProfileWrapper">'; //wrapper class for styling on front end
			foreach ( $APIfields as $field => $names )
			{
				$fieldID = $names['camel'];
				$fieldName = $names['nice'];	
				
				if( $fieldID <> "id" ) //don't show the id field.
				{
					if ( isset($checkedFields[ $fieldID ]) )
					{
						$output .= self::getProfileItem( $fieldID, $profileData, $fieldName, true );
					}
				}
			}
			$output .= '</div>';
		}
		
		return $output;
	}
	
	
/**
*	
*/		
	function getProfileItem ( $item, $profile, $fieldName, $addLabels = false )
	{
		$part = '';
				
		if ( isset( $profile->$item ) && gettype( $profile->$item ) === 'string'  ) //top level single values
		{
			switch ($item) //prep certain fields
			{
				case "pictureUrl":
					$element='<img src="' . $profile->$item . '" />';
					break;
					
				case "emailAddress":
					$element = '<a href="mailto'.$profile->$item.'">'.$profile->$item.'</a>';
					break;
					
				case "publicProfileUrl":
					$displayURL = str_replace( array('http://', 'https://', 'www.'), '', $profile->$item );
					$element = '<a href="' . $profile->$item . '">' . $displayURL . '</a>'; 
					break;
					
				default:
					$element = htmlspecialchars( $profile->$item );
			}
			
			//wrap the item
			$part = ( $profile->$item != '' ) ? '<div class="SULI-profile-part SULI-' . $item . '">' . $element . '</div>' : '';
			
			//add label if required
			if ( $addLabels === true )
			{
				switch ($item) //only want labels on some items
				{
					case "summary":
						$part = '<h3>' . $fieldName . '</h3>' . $part;
						break;
					
					case "publicProfileUrl":
						$part = '<h3>' . $fieldName . '</h3>' . $part;
						break;
						
					case "interests":
						$part = '<h3>' . $fieldName . '</h3>' . $part;
						break;
				}			
			}
			
		}
		else if ( isset( $profile->$item ) && gettype( $profile->$item ) === 'object' ) //top level objects
		{
			$part = self::buildProfileItem( $item, $profile, $fieldName, $addLabels );
		}
		
		return $part;
	}
	

/**
*
*	
*/	
	function buildProfileItem ( $item, $profile, $fieldName, $addLabels = false )
	{
		$string = '';
		$part = ''; 
		
		if ( 'skills' === $item )
		{
			$array = $profile->$item->values;
			
			if ( is_array( $array ) )
			{
				if ( $addLabels === true )
				{
					$string.='<h3>'.$fieldName.'</h3>';
				}
				$count = count( $array );
				$j = 1;
				$element = '';				
				foreach ( $array as $i => $object )
				{
					$element .= '<span>' . htmlspecialchars( $object->skill->name ) . '</span> ';
					$j++;
				}
			}
			
			$part = ( $element != '' ) ? $string . '<div class="SULI-profile-part SULI-' . $item . '">' . $element . '</div>' : '';
			
		}
		else if ( 'educations' === $item )
		{
			$array = ( isset($profile->$item->values) ) ? $profile->$item->values : false;
			
			if ( is_array( $array ) )
			{
				if ( $addLabels === true )
				{
					$string.='<h3>'.$fieldName.'</h3>';
				}
				foreach ( $array as $i => $education )
				{
					$data = false;
					$element = '';
					
					if ( isset($education->schoolName) )
					{
						$element .= '<h4>' . htmlspecialchars( $education->schoolName ) . '</h4>';
						$data = true;
					}
					
					if ( isset($education->degree) || isset($education->fieldOfStudy) )
					{
						$element .= '<p>';
						$element .= isset($education->degree) ? htmlspecialchars( $education->degree ) : '';
						$element .= ( isset($education->degree) && isset($education->fieldOfStudy) ) ? ', ' : '';
						$element .= isset($education->fieldOfStudy) ? htmlspecialchars( $education->fieldOfStudy ) : '';
						$element .= '.</p>';
						$data = true;
					}
					
					if ( isset($education->startDate->year) || isset($education->endDate->year) )
					{
						$element .= '<p class="date">';
						$element .= isset($education->startDate->year) ? $education->startDate->year : '';
						$element .= ( isset($education->startDate->year) && isset($education->endDate->year) ) ? ' - ' : '';
						$element .= isset($education->endDate->year) ? $education->endDate->year : '';
						$element .= '</p>';
						$data = true;
					}
					
					if ( isset($education->activities) )
					{
						$element .= '<p>' . htmlspecialchars( $education->activities ) . '</p>';
						$data = true;
					}
					
					if ( isset($education->notes) )
					{
						$element .= '<p>' . htmlspecialchars( $education->notes ) . '</p>';
						$data = true;
					}
					
					$string .= ( $data ) ? '<div class="SULI-profile-part SULI-' . $item . '">' . $element . '</div>' : '';			
				}
			}
			
			$part = $string;
		
		}
		else if ( 'positions' === $item )
		{
			
			$array = $profile->$item->values;
			
			if ( is_array( $array ) )
			{
				if ( $addLabels === true )
				{
					$string.='<h3>'.$fieldName.'</h3>';
				}
				
				foreach ( $array as $i => $position )
				{
					$data = false;
					$element = '';
					
					if ( isset($position->title) )
					{
						$element .= '<h4>';
						$element .= htmlspecialchars( $position->title );
						$element .= '</h4>';
						$data = true;
					}
					
					if ( isset($position->company->name) )
					{
						$element .= '<p>';
						$element .= htmlspecialchars( $position->company->name );
						$element .= '</p>';
						$data = true;
					}
					
					if ( isset($position->startDate->year) || isset($position->endDate->year) )
					{
						$element .= '<p class="date">';
						$element .= ( isset($position->startDate->month) ) ? self::numberToMonthName( $position->startDate->month ) : '';
						$element .= ( isset($position->startDate->month) && isset($position->startDate->year) ) ? ' ' : '';
						$element .= ( isset($position->startDate->year) ) ? $position->startDate->year : '';
						
						$element .= ( isset($position->startDate->year) && (isset($position->endDate->year) || $position->isCurrent == '1') ) ? ' - ' : '';
						
						if ( $position->isCurrent == '1' )
						{
							$element .= '(Present)';
						}
						else
						{
							$element .= ( isset($position->endDate->month) ) ? self::numberToMonthName( $position->endDate->month ) : '';
							$element .= ( isset($position->endDate->month) && isset($position->endDate->year) ) ? ' ' : '';
							$element .= ( isset($position->endDate->year) ) ? $position->endDate->year : '';
						}
						$element .= '</p>';
						$data = true;
					}
					
					if ( isset($position->summary) )
					{
						$element .= '<p>';
						$element .= htmlspecialchars( $position->summary );
						$element .= '</p>';
						$data = true;
					}
					
					$string .= ( $data ) ? '<div class="SULI-profile-part SULI-' . $item . '">' . $element . '</div>' : '';
				}
			}
			
			$part = $string;
		
		}
		else if ( 'location' === $item )
		{
			$part = ( isset($profile->location->name) ) ? '<div class="SULI-profile-part SULI-' . $item . '">' . htmlspecialchars( $profile->location->name ) . '</div>' : '';
		}
		else if ( 'certifications' === $item )
		{
			$array = ( isset($profile->$item->values) ) ? $profile->$item->values : false;
			
			if ( is_array( $array ) )
			{
				if ( $addLabels === true )
				{
					$string.='<h3>'.$fieldName.'</h3>';
				}
				foreach ( $array as $i => $certification )
				{
					$data = false;
					$element = '';
					
					if ( isset($certification->name ) )
					{
						$element .= '<h4>' . htmlspecialchars( $certification->name ) . '</h4>';
						$data = true;
					}
					
					$string .= ( $data ) ? '<div class="SULI-profile-part SULI-' . $item . '">' . $element . '</div>' : '';
				}
			}
			
			$part = $string;
		}
		else if ( 'courses' === $item )
		{
			$array = ( isset($profile->$item->values) ) ? $profile->$item->values : false;
			
			if ( is_array( $array ) )
			{
				if ( $addLabels === true )
				{
					$string.='<h3>'.$fieldName.'</h3>';
				}
				foreach ( $array as $i => $course )
				{
					$data = false;
					$element = '';
					
					if ( isset($course->name) )
					{
						$element .= '<h4>' . htmlspecialchars( $course->name ) . '</h4>';
						$element .= ( isset($course->number) ) ? '<p>(Course No. ' . htmlspecialchars( $course->number ) . ')</p>' : '';
						$data = true;
					}
					
					$string .= ( $data ) ? '<div class="SULI-profile-part SULI-' . $item . '">' . $element . '</div>' : '';
				}
			}
			
			$part = $string;
		}
		else if ( 'honorsAwards' === $item )
		{
			$array = ( isset($profile->$item->values) ) ? $profile->$item->values : false;
			
			if ( is_array( $array ) )
			{
				if ( $addLabels === true )
				{
					$string.='<h3>'.$fieldName.'</h3>';
				}
				foreach ( $array as $i => $honor )
				{
					$data = false;
					$element = '';
					
					if ( isset($honor->name) )
					{
						$element .= '<h4>' . htmlspecialchars( $honor->name ) . '</h4>';
						$element .= ( isset($honor->issuer) ) ? '<p>(Issued By ' . htmlspecialchars( $honor->issuer ) . ')</p>' : '';
						$data = true;
					}
					
					$string .= ( $data ) ? '<div class="SULI-profile-part SULI-' . $item . '">' . $element . '</div>' : '';
				}
			}
			
			$part = $string;
		}
		else if ( 'languages' === $item )
		{
			$array = ( isset($profile->$item->values) ) ? $profile->$item->values : false;
			
			if ( is_array( $array ) )
			{
				if ( $addLabels === true )
				{
					$string.='<h3>'.$fieldName.'</h3>';
				}
				
				$count = count( $array );
				$j = 1;
				$element = '';
				foreach ( $array as $i => $lang )
				{	
					if ( isset($lang->language->name) )
					{
						$element .= '<span>' . htmlspecialchars( $lang->language->name ) . '</span>' . ( $j != $count ? ', ' : '.' );
					}	
					$j++;
				}
			}
			$part = ( $element !== '' ) ? $string . '<div class="SULI-profile-part SULI-' . $item . '">' . $element . '</div>' : '';
		}
		else if ( 'volunteer' === $item )
		{
			$array = ( isset($profile->$item->volunteerExperiences->values) ) ? $profile->$item->volunteerExperiences->values : false;
			
			if ( is_array( $array ) )
			{
				if ( $addLabels === true )
				{
					$string.='<h3>'.$fieldName.'</h3>';
				}
				foreach ( $array as $i => $vol )
				{
					$data = false;
					$element = '';
					
					if ( isset($vol->organization->name) )
					{
						$element .= '<h4>' . htmlspecialchars( $vol->organization->name ) . '</h4>';
						$element .= ( isset($vol->role) ) ? '<p>Role: ' . htmlspecialchars( $vol->role ) . '</p>' : '';
						$data = true;
					}
					
					$string .= ( $data ) ? '<div class="SULI-profile-part SULI-' . $item . '">' . $element . '</div>' : '';
				}
			}
			
			$part = $string;
		}
		else if ( 'patents' === $item )
		{
			$array = ( isset($profile->$item->values) ) ? $profile->$item->values : false;
			
			if ( is_array( $array ) )
			{
				if ( $addLabels === true )
				{
					$string.='<h3>'.$fieldName.'</h3>';
				}
				foreach ( $array as $i => $patent )
				{
					$data = false;
					$element = '';
					
					if ( isset($patent->title ) )
					{
						$element .= '<h4>' . htmlspecialchars( $patent->title ) . '</h4>';
						$data = true;
					}
					
					$string .= ( $data ) ? '<div class="SULI-profile-part SULI-' . $item . '">' . $element . '</div>' : '';
				}
			}
			
			$part = $string;
		}
		else if ( 'publications' === $item )
		{
			$array = ( isset($profile->$item->values) ) ? $profile->$item->values : false;
			
			if ( is_array( $array ) )
			{
				if ( $addLabels === true )
				{
					$string.='<h3>'.$fieldName.'</h3>';
				}
				foreach ( $array as $i => $pub )
				{
					$data = false;
					$element = '';
					
					if ( isset($pub->title ) )
					{
						$element .= '<h4>' . htmlspecialchars( $pub->title ) . '</h4>';
						
						if ( isset( $pub->date->year ) )
						{
							$month = ( isset($pub->date->month) ) ? $pub->date->month : 1;
							$day = ( isset($pub->date->day) ) ? $pub->date->day : 1;
							$format = ( isset($pub->date->month) ) ? "F Y" : "Y";
							$format = ( isset($pub->date->day) ) ? "jS " . $format : $format;
							$displayDate = date($format, mktime(0, 0, 0, $month, $day, $pub->date->year) );
							$element .= '<p>Published ' . $displayDate . '</p>';
						}
						$data = true;
					}
					
					$string .= ( $data ) ? '<div class="SULI-profile-part SULI-' . $item . '">' . $element . '</div>' : '';
				}
			}
			
			$part = $string;
		}
		
		
		return $part;
	}

	
	
	function helpInfo ()
	{
		
		global $liLITE;
		$url = $liLITE->PluginFolder;
		
		$html = '';
		
		$html .= 'Setup this plugin in 3 simple steps<br/>';
		$html .= '<ol>';
		$html .= '<li><a href="#apiKeys">Get your API keys from LinkedIn</a></li>';
		$html .= '<li><a href="#profileAuthorise">Authorise your profile</a></li>';
		$html .= '<li><a href="#displayProfile">Display your profile on a page or post</a></li>';
		$html .= '</ol>';
		$html .='<a name="apiKeys"></a><br/><br/><h3>Getting Your API Keys</h3>';
		$html .= '<h4 style="font-size:15px;">1. Go to <a href="https://developer.linkedin.com/">https://developer.linkedin.com/</a>, choose \'API Keys\' from 
			the support menu (shown below) and log in with your usual LinkedIn credentials.</h4>';
		$html .= '<img src="' .$url. '/images/help-1.jpg" />';

		$html .= '<br /><br /><h4 style="font-size:15px;">2. Click \'Add New Application\'.</h4>';
		$html .= '<img src="' .$url. '/images/help-2.jpg" />';

		$html .= '<br /><br /><h4 style="font-size:15px;">3. Complete the form and submit it, see the image and explaination below:</h4>';
		$html .= '<p>The important parts of the form are the bits marked \'A\' and \'B\' in the image, explained as follows:</p>';
		$html .= '<p><strong>A - Permissions - Make sure you tick the <code>r_fullprofile</code> and <code>r_emailaddress</code> boxes.</strong></p>';
		$html .= '<p><strong>B - oAuth2 redirection - Enter the following url in this field: <code>' .$liLITE->receiverURL. '</code></strong></p>';
		$html .= '<p><br />Additionally you will need to complete the fields marked \'*\', these are all descriptive or un-important but need to be entered for the form to submit.</p>';
		$html .= '<img src="' .$url. '/images/help-3.jpg" />';
		
		$html .= '<br /><br /><h4 style="font-size:15px;">4. Copy and paste the API Key, and the Secret Key in to the respective fields on the plugin API tab, then save settings.</h4>';
		
		
		$html .= '<br /><a name="profileAuthorise"></a><br/><h3>Authorising your LinkedIn Account</h3>';
		$html .= '<p>Once you have your API keys entered, you can then use the \'Authorisation\' tab to connect the plugin to your LinkedIn account.<p></p>Click the \'Authorise\' button and you\'ll 
			be redirected to LinkedIn, enter your credentials and click \'Allow Access\'. On success you\'ll be redirected back to the plugin where you\'ll see your account summary, and 
			the number of days left before you need to re-authorise.</p>';
		
		
		$html .= '<br /><a name="displayProfile"></a><br/><h3>Displaying Your Profile</h3>';
		$html .= '<p>You can display your profile in 2 ways:</p>';
		$html .= '<h4>1. Using the shortcode <code>[linkedin-profile]</code> in a page or post.</h4>';
		$html .= '<h4>2. Using the Widget in a sidebar area.</h4>';
		
		return $html;
	
	
	}
	
	
	function numberToMonthName ( $number )
	{
		$monthName = '';
		$number = intval( $number );
		
		if ( $number <= 12 )
		{
			$dateObj = DateTime::createFromFormat( '!m', $number );
			$monthName = $dateObj->format('F');
		}
		
		return $monthName;
	}

}//end class

?>