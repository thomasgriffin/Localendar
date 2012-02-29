<?php
/*
Plugin Name: Localendar for WordPress
Plugin URI: http://www.localendar.com
Description: The official Localendar plugin for WordPress.
Author: Thomas Griffin
Author URI: http://thomasgriffinmedia.com/
Version: 1.0.0
License: GNU General Public License v2.0 or later
License URI: http://www.opensource.org/licenses/gpl-license.php
*/

/*  
	Copyright 2012  Thomas Griffin  (email : thomas@thomasgriffinmedia.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! class_exists( 'TGM_Localendar' ) ) {
	/**
 	 * Localendar class for WordPress.
 	 *
 	 * @since 1.0.0
 	 *
 	 * @package TGM-Localendar
 	 * @author Thomas Griffin <thomas@thomasgriffinmedia.com>
 	 */
	class TGM_Localendar {
	
		/**
		 * Holds a copy of the object for easy reference.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		static $instance;
	
		/**
		 * Constructor. Hooks all interactions into correct areas to start
		 * the class.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			
			self::$instance = $this;
			
			/** Run a hook before the class is loaded and pass the object */
			do_action_ref_array( 'tgmlo_init', array( $this ) );

			add_action( 'widgets_init', array( $this, 'widget' ) );
			add_action( 'init', array( $this, 'init' ) );
		
		}
		
		/**
		 * Instantiates the Localendar widget.
		 *
		 * @since 1.0.0
		 */
		public function widget() {
		
			register_widget( 'TGM_Localendar_Widget' );
		
		}
		
		/**
		 * Loads the plugin upgrader, registers the post type and
		 * loads all the actions and filters for the class.
		 *
		 * @since 1.0.0
		 */
		public function init() {
	
			/** Load hooks and filters */
			add_action( 'admin_enqueue_scripts', array ( $this, 'assets' ) );
			add_filter( 'media_buttons_context', array( $this, 'tinymce' ) );
			add_action( 'admin_footer', array( $this, 'admin_footer' ) );
			
			/** Create shortcode to output calendar data and allow shortcode to be used in widgets */
			add_shortcode( 'localendar', array( $this, 'shortcode' ) );
			add_filter( 'widget_text', 'do_shortcode' );
			
			/** Load the plugin textdomain for internationalizing strings */
			load_plugin_textdomain( 'localendar', false, plugin_dir_path( __FILE__ ) . '/lib/languages/' );
		
		}
		
		/**
		 * Loads assets for the Localendar plugin.
		 *
		 * @since 1.0.0
		 *
		 * @global object $current_screen The current screen object
		 */
		public function assets() {
		
			global $current_screen;
			
			wp_register_script( 'localendar-admin', plugins_url( 'lib/js/admin.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
			
			if ( 'widgets' == $current_screen->id )
				wp_enqueue_script( 'localendar-admin' );
		
		}
		
		/**
		 * Adds a custom calendar insert button beside the media uploader button.
		 *
		 * @since 1.0.0
		 *
		 * @param array $columns The default columns provided by WP_List_Table
		 */
		public function tinymce( $context ) {
		
			global $pagenow;
			$output = '';
			
			/** Only run in post/page creation and edit screens */
			if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) {
				$img 	= '<img src="' . plugins_url( 'lib/css/images/title-icon.png', __FILE__ ) . '" width="16px" height="16px" alt="Add Localendar" />';
				$output = '<a href="#TB_inline?width=640&inlineId=choose-localendar-slider" class="thickbox" title="' . __( 'Add Localendar', 'localendar' ) . '">' . $img . '</a>';
			}
			
			return $context . $output;
		
		}
		
		/**
		 * Outputs the jQuery and HTML necessary to insert a calendar when the user 
		 * uses the button added to the media buttons above TinyMCE. 
		 *
		 * @since 1.0.0
		 *
		 * @global string $pagenow The current page slug
		 */
		public function admin_footer() {
		
			global $pagenow;
			
			/** Only run in post/page creation and edit screens */
			if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) {
				/** Get all published sliders */
				$sliders = get_posts( array( 'post_type' => 'localendar', 'posts_per_page' => -1, 'post_status' => 'publish' ) );
				
				?>
				<script type="text/javascript">
					function insertSlider() {
						var id = jQuery('#select-localendar-slider').val();
						
						/** Return early if no slider is selected */
						if ( '' == id ) {
							alert('<?php _e( 'Please select a slider.', 'localendar' ); ?>');
							return;
						}
						
						/** Send the shortcode to the editor */
						window.send_to_editor('[localendar id="' + id + '"]');
					}		
				</script>
				
				<div id="choose-localendar-slider" style="display: none;">
					<div class="wrap" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
						<div id="icon-localendar" class="icon32" style="background: url(<?php echo plugins_url( 'lib/css/images/title-icon.png', __FILE__ ); ?>) no-repeat scroll 0 50%; width: 16px;"><br></div>
						<h2><?php _e( 'Choose Your Slider', 'localendar' ); ?></h2>
						<?php do_action( 'tgmsp_before_slider_insertion', $sliders ); ?>
						<p style="font-weight: bold; padding-bottom: 10px;"><?php _e( 'Select a slider below from the list of available sliders and then click \'Insert\' to place the slider into the editor.', 'localendar' ); ?></p>
						<select id="select-localendar-slider" style="clear: both; display: block; margin-bottom: 1em;">
						<?php
							foreach ( $sliders as $slider )
								echo '<option value="' . absint( $slider->ID ) . '">' . esc_attr( $slider->post_title ) . '</option>';
						?>
						</select>
						
						<input type="button" id="localendar-insert-slider" class="button-primary" value="<?php esc_attr_e( 'Insert Slider', 'localendar' ); ?>" onclick="insertSlider();" />
						<a id="localendar-cancel-slider" class="button-secondary" onclick="tb_remove();" title="<?php esc_attr_e( 'Cancel Slider Insertion', 'localendar' ); ?>"><?php _e( 'Cancel Slider Insertion', 'localendar' ); ?></a>
						<?php do_action( 'tgmsp_after_slider_insertion', $sliders ); ?>
					</div>
				</div>
				<?php
			}
		
		}
		
		/**
		 * Outputs calendar data in a shortcode called 'localendar'.
		 *
		 * @since 1.0.0
		 * 
		 * @param array $atts Array of shortcode attributes
		 * @return string $calendar Concatenated string of calendar data
		 */
		public function shortcode( $atts ) {
			
			/** Extract shortcode atts */
			extract( shortcode_atts( array(
				'username' 	=> '',
				'type' 		=> '',
				'include' 	=> '',
				'dynamic' 	=> '',
				'style' 	=> ''
			), $atts ) );
			
			/** Make sure jQuery is loaded */
			wp_enqueue_script( 'jquery' );
			
			if ( ! $username )
				return __( 'You must enter a valid username to display a calendar.', 'localendar' );
				
			$include 	= isset( $include ) ? '&include=' . $include : '';
			$dynamic 	= isset( $dynamic ) ? '&dynamic=' . $dynamic : '';
			$style 		= isset( $style ) 	? '&style=' . $style : '';
			
			if ( 'iframe' == $type )
				$calendar = '<script type="text/javascript" src="http://www.localendar.com/public/' . esc_attr( $username ) . '/' . $include . $dynamic . $style . '></script>';
			else
				$calendar = '<script type="text/javascript" src="http://www.localendar.com/public/' . esc_attr( $username ) . '/' . $include . $dynamic . $style . '></script>';
			
			return apply_filters( 'tgmlo_calendar_shortcode', $calendar );
			
		}
		
	}
}

/** Instantiate the class */
$tgm_localendar = new TGM_Localendar();

if ( ! class_exists( 'TGM_Localendar_Widget' ) ) {
	/**
 	 * Localendar widget class for WordPress.
 	 *
 	 * @since 1.0.0
 	 *
 	 * @package TGM-Localendar
 	 * @author Thomas Griffin <thomas@thomasgriffinmedia.com>
 	 */
 	 class TGM_Localendar_Widget extends WP_Widget {
 	 
 	 	/**
		 * Constructor. Sets up and creates the widget with appropriate settings.
		 *
		 * @since 1.0.0
		 */
 	 	public function __construct() {
 	 	
 	 		$widget_ops = array(
 	 			'classname' 	=> 'localendar',
 	 			'description' 	=> __( 'Use this widget to place a Localendar in your sidebar.', 'localendar' )
 	 		);
 	 		
 	 		$control_ops = array(
 	 			'id_base' 	=> 'localendar',
 	 			'height' 	=> 350,
 	 			'width' 	=> 350
 	 		);
 	 		
 	 		$this->WP_Widget( 'localendar', __( 'Localendar', 'localendar' ), $widget_ops, $control_ops );
 	 	
 	 	}
 	 	
 	 	/**
		 * Outputs the widget within the sidebar.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args The default widget arguments
		 * @param array $instance The input settings for the current widget instance
		 */
 	 	public function widget( $args, $instance ) {
 	 	
 	 		/** Take arguments array and turn keys into variables */
 	 		extract( $args );
 	 		
 	 		$title = apply_filters( 'widget_title', $instance['title'] );
 	 		echo '<pre>' . print_r( $args, true ) . '</pre>';
 	 		
 	 		do_action( 'tgmlo_widget_before_output', $args, $instance );
 	 		
 	 		echo $before_widget;
 	 		
 	 		do_action( 'tgmlo_widget_before_title', $args, $instance );
 	 		
 	 		/** If a title exists, output it */
 	 		if ( $title )
 	 			echo $before_title . esc_attr( $title ) . $after_title;
 	 		
 	 		do_action( 'tgmlo_widget_before_calendar', $args, $instance );
 	 		
 	 		$calendar = '';
 	 			
 	 		/** Build the calendar */
 	 		switch ( $instance['type'] ) {
 	 			case 'link' :
 	 				switch ( $instance['style'] ) {
 	 					case 'mb' :
 	 						$calendar = '<a class="localendar" href="http://www.localendar.com/public/' . esc_attr( $instance['username'] ) . '" target="_blank">' . esc_attr( $instance['link_text'] ) . '</a>';
 	 						break 1;
 	 				}
 	 		}
 	 		
 	 		echo apply_filters( 'tgmlo_calendar_output', $calendar, $args, $instance );
 	 			
 	 		do_action( 'tgmlo_widget_after_calendar', $args, $instance );
 	 			
 	 		echo $after_widget;
 	 		
 	 		do_action( 'tgmlo_widget_after_output', $args, $instance );
 	 	
 	 	}
 	 	
 	 	/**
		 * Sanitizes and updates the widget.
		 *
		 * @since 1.0.0
		 *
		 * @param array $new_instance The new input settings for the current widget instance
		 * @param array $old_instance The old input settings for the current widget instance
		 */
 	 	public function update( $new_instance, $old_instance ) {
 	 	
 	 		/** Set $instance to the old instance in case no new settings have been updated for a particular field */
 	 		$instance = $old_instance;
 	 		
 	 		/** Sanitize inputs */
 	 		$instance['title'] 		= strip_tags( $new_instance['title'] );
 	 		$instance['username'] 	= strip_tags( $new_instance['username'] );
 	 		$instance['type'] 		= esc_attr( $new_instance['type'] );
 	 		$instance['style'] 		= esc_attr( $new_instance['style'] );
 	 		$instance['link_text'] 	= strip_tags( $new_instance['link_text'] );
 	 		
 	 		do_action( 'tgmlo_widget_update', $new_instance, $instance );
 	 		
 	 		return $instance;
 	 	
 	 	}
 	 	
 	 	/**
		 * Outputs the form where the user can specify settings.
		 *
		 * @since 1.0.0
		 *
		 * @param array $instance The input settings for the current widget instance
		 */
 	 	public function form( $instance ) {
 	 	
 	 		/** Set defaults */
 	 		$defaults = array(
 	 			'title' 	=> '',
 	 			'username' 	=> '',
 	 			'type' 		=> 'link',
 	 			'style' 	=> 'mb'
 	 		);
 	 		wp_parse_args( (array) $instance, $defaults );
 	 		$types 	= array( 'link', 'full', 'static', 'iframe', 'mini' );
 	 		$styles = array( 'mb', 'mb2', 'ml', 'wb', 'wl', 'dv', 'th' );
 	 		
 	 		?>
 	 		<!-- Output styling inline to avoid having to make an unnecessary CSS call -->
 	 		<style type="text/css">.localendar-form .localendar-types input[type="radio"] { vertical-align: middle; } .localendar-form .localendar-types label { margin-left: 5px; vertical-align: middle; }</style>
 	 		<div class="localendar-form">
 	 		<?php do_action( 'tgmlo_widget_before_form', $instance ); ?>
 	 		<p>
 	 			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title', 'localendar' ); ?></label>
 	 			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" style="width: 100%;" />
 	 		</p>
 	 		<?php do_action( 'tgmlo_widget_middle_form', $instance ); ?>
 	 		<p>
 	 			<label for="<?php echo $this->get_field_id( 'username' ); ?>"><?php _e( 'Localendar Username', 'localendar' ); ?></label>
 	 			<input id="<?php echo $this->get_field_id( 'username' ); ?>" name="<?php echo $this->get_field_name( 'username' ); ?>" type="text" value="<?php echo esc_attr( $instance['username'] ); ?>" style="width: 100%;" />
 	 		</p>
 	 		<p><strong><?php _e( 'Step 1: How do you want to include your calendar?', 'localendar' ); ?></strong></p>
 	 		<p class="localendar-types">
			<?php 
				foreach ( $types as $type ) {
					$checked = ( $type == $instance['type'] ) ? 'checked="checked"' : '';
					echo '<input id="' . $this->get_field_id( 'type' ) . '" class="localendar-type-' . $type . '" type="radio" name="' . $this->get_field_name( 'type' ) . '" value="' . $type . '"' . $checked . ' />';

					switch ( $type ) {
						case 'link' :
							echo '<label for="' . $this->get_field_id( 'type' ) . '">' . __( 'a link to a <strong>full-page</strong> view of my calendar', 'localendar' ) . '</label><br />';
							break 1;
						case 'full' :
							echo '<label for="' . $this->get_field_id( 'type' ) . '">' . __( 'a <strong>fully-interactive</strong> embedded calendar', 'localendar' ) . '</label><br />';
							break 1;
						case 'static' :
							echo '<label for="' . $this->get_field_id( 'type' ) . '">' . __( 'a <strong>static</strong> (non-interactive) embedded calendar', 'localendar' ) . '</label><br />';
							break 1;
						case 'iframe' :
							echo '<label for="' . $this->get_field_id( 'type' ) . '">' . __( 'my calendar <strong>in an &#60;iframe&#62;</strong>', 'localendar' ) . '</label><br />';
							break 1;
						case 'mini' :
							echo '<label for="' . $this->get_field_id( 'type' ) . '">' . __( 'an <strong>interactive mini-calendar</strong> with pop-up event balloons', 'localendar' ) . '</label><br />';
							break 1;
					}
					
				} 
			?>
 	 		</p>
 	 		<p class="localendar-link-text" style="display: none;">
 	 			<label for="<?php echo $this->get_field_id( 'link_text' ); ?>"><?php _e( 'Link Text', 'localendar' ); ?></label>
 	 			<input id="<?php echo $this->get_field_id( 'link_text' ); ?>" name="<?php echo $this->get_field_name( 'link_text' ); ?>" type="text" value="<?php echo esc_attr( $instance['link_text'] ); ?>" style="width: 100%;" />
 	 		</p>
 	 		<p><strong><?php _e( 'Step 2: Select the style for your calendar.', 'localendar' ); ?></strong></p>
 	 		<p class="styles">
 	 			<select id="<?php echo $this->get_field_id( 'style' ); ?>" name="<?php echo $this->get_field_name( 'style' ); ?>">
				<?php
					foreach ( $styles as $style ) {
						switch ( $style ) {
							case 'mb' :
								echo '<option value="' . esc_attr( $style ) . '"' . selected( esc_attr( $style ), $instance['style'], false ) . '>' . __( 'Month Block-View', 'localendar' ) . '</option>';
								break 1;
							case 'mb2' :
								echo '<option value="' . esc_attr( $style ) . '"' . selected( esc_attr( $style ), $instance['style'], false ) . '>' . __( 'Month Block-View (Style #2)', 'localendar' ) . '</option>';
								break 1;
							case 'ml' :
								echo '<option value="' . esc_attr( $style ) . '"' . selected( esc_attr( $style ), $instance['style'], false ) . '>' . __( 'Month List-View', 'localendar' ) . '</option>';
								break 1;
							case 'wb' :
								echo '<option value="' . esc_attr( $style ) . '"' . selected( esc_attr( $style ), $instance['style'], false ) . '>' . __( 'Week Block-View', 'localendar' ) . '</option>';
								break 1;
							case 'wl' :
								echo '<option value="' . esc_attr( $style ) . '"' . selected( esc_attr( $style ), $instance['style'], false ) . '>' . __( 'Week List-View', 'localendar' ) . '</option>';
								break 1;
							case 'dv' :
								echo '<option value="' . esc_attr( $style ) . '"' . selected( esc_attr( $style ), $instance['style'], false ) . '>' . __( 'Day View', 'localendar' ) . '</option>';
								break 1;
							case 'th' :
								echo '<option value="' . esc_attr( $style ) . '"' . selected( esc_attr( $style ), $instance['style'], false ) . '>' . __( 'Today + "Happening Soon"', 'localendar' ) . '</option>';
								break 1;
						}
					}
				?>
				</select>
 	 		</p>
 	 		</div>
 	 		<?php do_action( 'tgmlo_widget_after_form', $instance ); ?>
 	 		<?php
 	 	
 	 	}
 	 
 	 }
}