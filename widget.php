<?php
/*
*	Profile Widget
*	---
*	Adds the widget-version profile, and any user content after it.
*
*/
if ( class_exists("WP_Widget") ) { 
	
	class LILwidget_Profile extends WP_Widget {
	
	
		/*	
		*	Constructor. 
		*	(required by api) 
		*/
		//function LILwidget_Profile() {
		function __construct () {
		
			$widget_ops = array( 
				'classname' => 'lil_widget_profile', 	
				'description' => __('Add your LinkedIn profile to your sidebar.', 'lil_widget_profile')
			);
			
			$control_ops = array( 
				'id_base' => 'lil-widget-profile',
				'width' => 300 
			);
			
			//$this->WP_Widget( 'lil-widget-profile', __('LinkedIn Lite', 'lil_widget_profile'), $widget_ops, $control_ops );
			parent::__construct( 'lil-widget-profile', __('LinkedIn Lite', 'lil_widget_profile'), $widget_ops, $control_ops );
		}
	
	
		/*	
		*	Outputs the widget.
		*	(required by api)
		*	@args		- passed theme vars provided by WP.
		*	@instance	- the widget's saved settings.
		*/
		function widget( $args, $instance ) {
			
			//check if theres any filter settings and return if this page should be filtered out.
			$isFiltered = liLITE_main::page_filter( $instance['restrict_list'], $instance['restrict_mode'] );
			if ( $isFiltered )
			{
				return;
			}
			
			//process the widget content.
			$input = '[linkedin-profile-widget]<br />' . $instance['arb_text'] . '<br />&nbsp;';
			$output = do_shortcode( $input );
			
			extract( $args ); // supplied WP theme vars (before_widget, before_title etc).
			
			echo $before_widget;
			if ( $instance['title'] ) 
			{ 
				echo $before_title . $instance['title'] . $after_title; 
			}
			echo $output;
			echo $after_widget;
			
			return;
		}
   
   
		/*	
		*	Updates the widget settings.
		*	(required by api)
		*/
		function update( $new_instance, $old_instance ) {
			
			$instance = $old_instance;
			
			$instance['title'] = $new_instance['title']; 					//standard widget title location
			$instance['arb_text'] = $new_instance['arb_text'];				//any text and/or other shortcode to add after the profile
			$instance['restrict_list'] = $new_instance['restrict_list'];	//page filter list (string eg: 5, 20, archive, post)
			$instance['restrict_mode'] = $new_instance['restrict_mode'];	//page filter mode, whether to include or exclude what's on the list.
			
			return $instance;
		}

		
		/*	
		*	Creates default settings and writes the widget panel.
		*	(required by api)
		*/
		function form( $instance ) {
			
			$defaultvalues = array(
				'title' => '',
				'arb_text' => '',
				'restrict_list' => '',
				'restrict_mode' => 'exclude'
			);
			$instance = wp_parse_args( (array) $instance, $defaultvalues );
			?>
				
				<!-- Widget Heading -->
				<h3 style="text-align:right; font-size: 14px; margin-bottom:0px;"><a href="admin.php?page=linkedin-lite">&raquo; Profile Settings</a></h3>
				<p style="font-size:11px;">Widget Heading: <input class="widefat" type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" /></p>
							
				
				<!-- Arbitrary text/shortcodes -->
				
				
				
				<p>Any text to appear above the profile:</p>
				<p style="margin:8px 0 10px 0;"><textarea class="widefat" style="font-size:11px;" rows="8" cols="85" id="<?php echo $this->get_field_id( 'arb_text' ); ?>" name="<?php echo $this->get_field_name( 'arb_text' ); ?>"><?php echo $instance['arb_text']; ?></textarea></p>
				
				
				<!-- Page Filter -->
				<p style="font-size: 11px; margin:0px 0px 10px 0px;">
					Include <input type="radio" id="<?php echo $this->get_field_id( 'restrict_mode' ); ?>" name="<?php echo $this->get_field_name( 'restrict_mode' ); ?>" value="include" <?php if ($instance['restrict_mode'] == "include") { _e('checked="checked"', "LIL_widgetProfile"); }?> />
					or <input type="radio" id="<?php echo $this->get_field_id( 'restrict_mode' ); ?>" name="<?php echo $this->get_field_name( 'restrict_mode' ); ?>" value="exclude" <?php if ($instance['restrict_mode'] == "exclude") { _e('checked="checked"', "LIL_widgetProfile"); }?> />
					Exclude pages &nbsp;<input class="widefat" style="font-size:11px; width:200px;" type="text" id="<?php echo $this->get_field_id( 'restrict_list' ); ?>" name="<?php echo $this->get_field_name( 'restrict_list' ); ?>" value="<?php echo $instance['restrict_list']; ?>" /></p>
				
				<p class="description" style="font-size:11px; color:#999999; margin-top:4px; margin-bottom:1px;">A comma separated list that can contain post ID's eg. <code>547</code>, and the following <code>index</code>, <code>archive</code>, <code>post</code>, <code>search</code>.</p> 
				
				
			<?php	
		}
		
	} //close class
	
}
?>