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
	Copyright 2012	 Thomas Griffin	 (email : thomas@thomasgriffinmedia.com)

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

			register_activation_hook( __FILE__, array( $this, 'activation' ) );
			add_action( 'widgets_init', array( $this, 'widget' ) );
			add_action( 'init', array( $this, 'init' ) );

		}

		/**
		 * Deactivate the plugin if the user is using less than WP 3.2.
		 *
		 * @since 1.0.0
		 */
		public function activation() {

			global $wp_version;

			if ( version_compare( $wp_version, '3.2', '<' ) ) {
				deactivate_plugins( plugin_basename( __FILE__ ) );
				wp_die( sprintf( __( 'Your current version of WordPress (<strong>%s</strong>) is not supported by this plugin. You must be using WordPress 3.2 or greater to use this plugin. <a href="%s">Return to the plugins page</a>', 'localendar' ), $wp_version, admin_url( 'plugins.php' ) ) );
			}

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
			add_action( 'admin_enqueue_scripts', array ( $this, 'admin_scripts' ) );
			add_filter( 'media_buttons_context', array( $this, 'tinymce' ) );
			add_action( 'admin_footer', array( $this, 'admin_footer' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'jquery' ) );

			/** Create shortcode to output calendar data and allow shortcode to be used in widgets */
			add_shortcode( 'localendar', array( $this, 'shortcode' ) );
			add_filter( 'widget_text', 'do_shortcode' );

			/** Load the plugin textdomain for internationalizing strings */
			load_plugin_textdomain( 'localendar', false, plugin_dir_path( __FILE__ ) . '/lib/languages/' );

		}

		/**
		 * Loads admin scripts only on widgets page or post edit screens.
		 *
		 * @since 1.0.0
		 *
		 * @global object $current_screen The current screen object
		 * @global string $pagenow The current page slug
		 */
		public function admin_scripts() {

			global $current_screen, $pagenow;

			wp_register_script( 'localendar-admin', plugins_url( 'lib/js/admin.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );

			if ( 'widgets' == $current_screen->id || in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) )
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
				$img 	= '<img src="' . plugins_url( 'lib/images/icon.png', __FILE__ ) . '" width="13px" height="15px" alt="' . esc_attr__( 'Add Localendar', 'localendar' ) . '" />';
				$output = '<a href="#TB_inline?width=640&inlineId=choose-localendar" class="thickbox" title="' . esc_attr__( 'Add Localendar', 'localendar' ) . '">' . $img . '</a>';
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
				?>
				<script type="text/javascript">
					function insertCalendar() {
						var user	= jQuery('#localendar-user').val() ? 'username="' + jQuery('#localendar-user').val() + '"' : '';
						var type	= jQuery('.localendar-types input:checked').val() ? 'type="' + jQuery('.localendar-types input:checked').val() + '"' : '';
						var style	= jQuery('#localendar-styles').val() ? 'style="' + jQuery('#localendar-styles').val() + '"' : '';
						var hide	= jQuery('#localendar-events').is(':checked') ? 'hide_events="true"' : 'hide_events="false"';
						var text	= jQuery('#localendar-link-text').val() ? 'link_text="' + jQuery('#localendar-link-text').val() + '"' : '';
						var width 	= jQuery('#localendar-iframe-width').val() ? 'iframe_width="' + jQuery('#localendar-iframe-width').val() + '"' : '';
						var height 	= jQuery('#localendar-iframe-height').val() ? 'iframe_height="' + jQuery('#localendar-iframe-height').val() + '"' : '';
						var query 	= jQuery('#localendar-query').val() ? 'query="' + jQuery('#localendar-query').val() + '"' : '';
						//console.log(user, type, style, hide, text, width, height, query);

						/** Return early if no username, type or style */
						if ( '' == user ) {
							alert('<?php _e( 'Please enter your Localendar username.', 'localendar' ); ?>');
							return;
						}

						if ( '' == type ) {
							alert('<?php _e( 'Please select the type of calendar you want to insert.', 'localendar' ); ?>');
							return;
						}

						if ( '' == style ) {
							alert('<?php _e( 'Please select a valid style for your calendar.', 'localendar' ); ?>');
							return;
						}

						/** Send shortcode to editor with correct attributes */
						switch (type) {
							case 'type="link"' :
								window.send_to_editor('[localendar ' + user + ' ' + type + ' ' + style + ' ' + hide + ' ' + text + ' ' + query + ']');
								break;
							case 'type="full"' :
								window.send_to_editor('[localendar ' + user + ' ' + type + ' ' + style + ' ' + hide + ' ' + query + ']');
								break;
							case 'type="static"' :
								window.send_to_editor('[localendar ' + user + ' ' + type + ' ' + style + ' ' + hide + ' ' + query + ']');
								break;
							case 'type="iframe"' :
								window.send_to_editor('[localendar ' + user + ' ' + type + ' ' + style + ' ' + hide + ' ' + width + ' ' + height + ' ' + query + ']');
								break;
							case 'type="mini"' :
								window.send_to_editor('[localendar ' + user + ' ' + type + ' ' + style + ' ' + hide + ' ' + query + ']');
								break;
						}

						/** Clear out all previous entries except username */
						jQuery('.localendar-types input:checked').prop('checked', false);
						jQuery('#localendar-styles').prop('selected', false);
						jQuery('#localendar-events').prop('checked', false);
						jQuery('#localendar-link-text').val('');
						jQuery('#localendar-iframe-width').val('');
						jQuery('#localendar-iframe-height').val('');
						jQuery('#localendar-query').val('');
					}
				</script>

				<div id="choose-localendar" style="display: none;">
					<div class="wrap" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
						<div id="icon-localendar" class="icon32" style="background: url(<?php echo plugins_url( 'lib/images/icon.png', __FILE__ ); ?>) no-repeat scroll 0 50%; width: 13px;"><br></div>
						<h2><?php _e( 'Choose Your Calendar', 'localendar' ); ?></h2>
						<?php
							$types 	= array( 'link', 'full', 'static', 'iframe', 'mini' );
 	 						$styles = array( 'mb', 'mb2', 'ml', 'wb', 'wl', 'dv', 'th' );
 	 					?>
 	 					<style type="text/css">.localendar-form .localendar-types input[type="radio"] { vertical-align: middle; } .localendar-form .localendar-types label { margin-left: 5px; vertical-align: middle; }</style>
 	 					<div class="localendar-form">
 	 						<p>
 	 							<label for="localendar-user"><?php _e( 'Localendar Username', 'localendar' ); ?></label>
 	 							<input id="localendar-user" name="localendar-user" type="text" value="" />
 	 						</p>
 	 						<p><strong><?php _e( 'Step 1: How do you want to include your calendar?', 'localendar' ); ?></strong></p>
 	 						<p class="localendar-types">
							<?php
								foreach ( $types as $type ) {
									echo '<input id="localendar-type-' . $type . '" type="radio" name="localendar-type" value="' . $type . '" />';

									switch ( $type ) {
										case 'link' :
											echo '<label for="localendar-type-link">' . __( 'a link to a <strong>full-page</strong> view of my calendar', 'localendar' ) . '</label><br />';
											break 1;
										case 'full' :
											echo '<label for="localendar-type-full">' . __( 'a <strong>fully-interactive</strong> embedded calendar', 'localendar' ) . '</label><br />';
											break 1;
										case 'static' :
											echo '<label for="localendar-type-static">' . __( 'a <strong>static</strong> (non-interactive) embedded calendar', 'localendar' ) . '</label><br />';
											break 1;
										case 'iframe' :
											echo '<label for="localendar-type-iframe">' . __( 'my calendar <strong>in an &#60;iframe&#62;</strong>', 'localendar' ) . '</label><br />';
											break 1;
										case 'mini' :
											echo '<label for="localendar-type-mini">' . __( 'an <strong>interactive mini-calendar</strong> with pop-up event balloons', 'localendar' ) . '</label><br />';
											break 1;
									}
								}
							?>
 	 						</p>
 	 						<p class="localendar-link-text">
 	 							<label for="localendar-link-text"><?php _e( 'Link Text', 'localendar' ); ?></label>
 	 							<input id="localendar-link-text" name="localendar-link-text" type="text" value="" />
 	 						</p>
 	 						<p class="select-style"><strong><?php _e( 'Step 2: Select the style for your calendar.', 'localendar' ); ?></strong></p>
 	 						<p class="styles">
 	 							<select id="localendar-styles" class="localendar-styles" name="localendar-styles">
 	 							<option value="" disabled="disabled"><?php _e( 'Select Your Style', 'localendar' ); ?></option>
								<?php
									foreach ( $styles as $style ) {
										switch ( $style ) {
											case 'mb' :
												echo '<option value="' . esc_attr( $style ) . '">' . __( 'Month Block-View', 'localendar' ) . '</option>';
												break 1;
											case 'mb2' :
												echo '<option value="' . esc_attr( $style ) . '">' . __( 'Month Block-View (Style #2)', 'localendar' ) . '</option>';
												break 1;
											case 'ml' :
												echo '<option value="' . esc_attr( $style ) . '">' . __( 'Month List-View', 'localendar' ) . '</option>';
												break 1;
											case 'wb' :
												echo '<option value="' . esc_attr( $style ) . '">' . __( 'Week Block-View', 'localendar' ) . '</option>';
												break 1;
											case 'wl' :
												echo '<option value="' . esc_attr( $style ) . '">' . __( 'Week List-View', 'localendar' ) . '</option>';
												break 1;
											case 'dv' :
												echo '<option value="' . esc_attr( $style ) . '">' . __( 'Day View', 'localendar' ) . '</option>';
												break 1;
											case 'th' :
												echo '<option value="' . esc_attr( $style ) . '">' . __( 'Today + "Happening Soon"', 'localendar' ) . '</option>';
												break 1;
										}
									}
								?>
								</select>
 	 						</p>
 	 						<p class="localendar-hide-events">
 	 							<input id="localendar-events" name="localendar-events" type="checkbox" value="" />
 	 							<label for="localendar-events"><?php _e( 'Hide events that occur in the previous/next month when applicable?', 'localendar' ); ?></label>
 	 						</p>
 	 						<p class="step-3"><strong><?php _e( 'Step 3: Additional Customizations', 'localendar' ); ?></strong></p>
 	 						<p class="localendar-iframe-style">
 	 							<label for="localendar-iframe-width"><?php _e( 'Iframe Width?', 'localendar' ); ?></label><br />
 	 							<input id="localendar-iframe-width" name="localendar-iframe-width" type="text" value="" />
 	 							<span class="description"><?php _e( 'Used to determine width of an iframe (if iframe type is selected).', 'localendar' ); ?></span><br /><br />
 	 							<label for="localendar-iframe-height"><?php _e( 'Iframe Height?', 'localendar' ); ?></label><br />
 	 							<input id="localendar-iframe-height" name="localendar-iframe-height" type="text" value="" />
 	 							<span class="description"><?php _e( 'Used to determine height of an iframe (if iframe type is selected).', 'localendar' ); ?></span>
 	 						</p>
 	 						<p class="localendar-query-strings">
 	 							<?php _e( 'Localendar also accepts a number of other query string parameters. If there are extra parameters that you would like appended to your URL, you can place them in the text field below. <a href="http://localendar.com/docs/display/lc/Your+Calendar%27s+URL+and+Parameters" target="_blank">Learn more about formatting your calendar URL</a>.', 'localendar' ); ?><br /><br />
 	 							<label for="localendar-query"><?php _e( 'Extra Query Args', 'localendar' ); ?></label><br />
 	 							<input id="localendar-query" name="localendar-query" type="text" value="" />
 	 						</p>
 	 						<input type="button" id="localendar-insert" class="button-primary" value="<?php _e( 'Insert Calendar', 'localendar' ); ?>" onclick="insertCalendar();" />
							<a id="localendar-cancel" class="button-secondary" onclick="tb_remove();" title="<?php esc_attr_e( 'Cancel Calendar Insertion', 'localendar' ); ?>"><?php _e( 'Cancel Calendar Insertion', 'localendar' ); ?></a>
 	 					</div>
					</div>
				</div>
				<?php
			}

		}

		/**
		 * Makes sure that jQuery is loaded so the widget can function properly.
		 *
		 * @since 1.0.0
		 */
		public function jquery() {

			/** Make sure jQuery is loaded */
			wp_enqueue_script( 'jquery' );

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
				'username' 		=> '',
				'type' 			=> '',
				'style' 		=> '',
				'hide_events' 	=> '',
				'link_text' 	=> '',
				'iframe_width'	=> '',
				'iframe_height'	=> '',
				'query'			=> ''
			), $atts ) );

			if ( ! $username )
				return __( 'You must enter a valid username to display a calendar.', 'localendar' );

			/** Build the calendar */
			$calendar = TGM_Localendar_Widget::build_calendar( $username, $type, $style, $hide_events, $link_text, $iframe_width, $iframe_height, $query );

			return apply_filters( 'tgmlo_calendar_shortcode', $calendar );

		}

	}
}

/** Instantiate the class */
$tgm_localendar = new TGM_Localendar;

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
 	 			'width' 	=> 400
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

 	 		do_action( 'tgmlo_widget_before_output', $args, $instance );

 	 		echo $before_widget;

 	 		do_action( 'tgmlo_widget_before_title', $args, $instance );

 	 		/** If a title exists, output it */
 	 		if ( $title )
 	 			echo $before_title . esc_attr( $title ) . $after_title;

 	 		do_action( 'tgmlo_widget_before_calendar', $args, $instance );

 	 		$calendar = $this->build_calendar( $instance['username'], $instance['type'], $instance['style'], $instance['hide_events'], $instance['link_text'], $instance['width'], $instance['height'], $instance['query'] );

 	 		echo sprintf( '<div class="localendar-output">%s</div>', apply_filters( 'tgmlo_calendar_output', $calendar, $args, $instance ) );

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
 	 		$instance['title'] 			= strip_tags( $new_instance['title'] );
 	 		$instance['username'] 		= strip_tags( $new_instance['username'] );
 	 		$instance['type'] 			= esc_attr( $new_instance['type'] );
 	 		$instance['style'] 			= esc_attr( $new_instance['style'] );
 	 		$instance['link_text'] 		= strip_tags( $new_instance['link_text'] );
 	 		$instance['hide_events'] 	= isset( $new_instance['hide_events'] ) ? 'true' : 'false';
 	 		$instance['width'] 			= intval( $new_instance['width'] ) ? $new_instance['width'] : '';
 	 		$instance['height'] 		= intval( $new_instance['height'] ) ? $new_instance['height'] : '';
 	 		$instance['query']			= esc_attr( $new_instance['query'] );

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
 	 			'title' 		=> '',
 	 			'username' 		=> '',
 	 			'type' 			=> 'link',
 	 			'style' 		=> 'mb',
 	 			'link_text' 	=> '',
 	 			'hide_events' 	=> 0,
 	 			'width' 		=> '',
 	 			'height' 		=> '',
 	 			'query' 		=> ''
 	 		);
 	 		$defaults 	= wp_parse_args( (array) $instance, $defaults );
 	 		$types 		= array( 'link', 'full', 'static', 'iframe', 'mini' );
 	 		$styles 	= array( 'mb', 'mb2', 'ml', 'wb', 'wl', 'dv', 'th' );

 	 		?>
 	 		<style type="text/css">.localendar-form .localendar-types input[type="radio"] { vertical-align: middle; } .localendar-form .localendar-types label { margin-left: 5px; vertical-align: middle; }</style>
 	 		<div id="localendar-instance-<?php echo $this->number; ?>" class="localendar-form">
 	 		<?php do_action( 'tgmlo_widget_before_form', $instance ); ?>
 	 		<p>
 	 			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title', 'localendar' ); ?></label>
 	 			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $defaults['title'] ); ?>" style="width: 100%;" />
 	 		</p>
 	 		<?php do_action( 'tgmlo_widget_middle_form', $instance ); ?>
 	 		<p>
 	 			<label for="<?php echo $this->get_field_id( 'username' ); ?>"><?php _e( 'Localendar Username', 'localendar' ); ?></label>
 	 			<input id="<?php echo $this->get_field_id( 'username' ); ?>" name="<?php echo $this->get_field_name( 'username' ); ?>" type="text" value="<?php echo esc_attr( $defaults['username'] ); ?>" style="width: 100%;" />
 	 		</p>
 	 		<p><strong><?php _e( 'Step 1: How do you want to include your calendar?', 'localendar' ); ?></strong></p>
 	 		<p class="localendar-types">
			<?php
				foreach ( $types as $type ) {
					$checked = ( $type == $defaults['type'] ) ? 'checked="checked"' : '';
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
 	 		<p class="localendar-link-text">
 	 			<label for="<?php echo $this->get_field_id( 'link_text' ); ?>"><?php _e( 'Link Text', 'localendar' ); ?></label>
 	 			<input id="<?php echo $this->get_field_id( 'link_text' ); ?>" name="<?php echo $this->get_field_name( 'link_text' ); ?>" type="text" value="<?php echo esc_attr( $defaults['link_text'] ); ?>" style="width: 100%;" />
 	 			<span class="description"><?php _e( 'Only applied when the link radio option is selected.', 'localendar' ); ?></span>
 	 		</p>
 	 		<p class="select-style"><strong><?php _e( 'Step 2: Select the style for your calendar.', 'localendar' ); ?></strong></p>
 	 		<p class="styles">
 	 			<select id="<?php echo $this->get_field_id( 'style' ); ?>" class="localendar-styles" name="<?php echo $this->get_field_name( 'style' ); ?>">
				<?php
					foreach ( $styles as $style ) {
						switch ( $style ) {
							case 'mb' :
								echo '<option value="' . esc_attr( $style ) . '"' . selected( esc_attr( $style ), $defaults['style'], false ) . '>' . __( 'Month Block-View', 'localendar' ) . '</option>';
								break 1;
							case 'mb2' :
								echo '<option value="' . esc_attr( $style ) . '"' . selected( esc_attr( $style ), $defaults['style'], false ) . '>' . __( 'Month Block-View (Style #2)', 'localendar' ) . '</option>';
								break 1;
							case 'ml' :
								echo '<option value="' . esc_attr( $style ) . '"' . selected( esc_attr( $style ), $defaults['style'], false ) . '>' . __( 'Month List-View', 'localendar' ) . '</option>';
								break 1;
							case 'wb' :
								echo '<option value="' . esc_attr( $style ) . '"' . selected( esc_attr( $style ), $defaults['style'], false ) . '>' . __( 'Week Block-View', 'localendar' ) . '</option>';
								break 1;
							case 'wl' :
								echo '<option value="' . esc_attr( $style ) . '"' . selected( esc_attr( $style ), $defaults['style'], false ) . '>' . __( 'Week List-View', 'localendar' ) . '</option>';
								break 1;
							case 'dv' :
								echo '<option value="' . esc_attr( $style ) . '"' . selected( esc_attr( $style ), $defaults['style'], false ) . '>' . __( 'Day View', 'localendar' ) . '</option>';
								break 1;
							case 'th' :
								echo '<option value="' . esc_attr( $style ) . '"' . selected( esc_attr( $style ), $defaults['style'], false ) . '>' . __( 'Today + "Happening Soon"', 'localendar' ) . '</option>';
								break 1;
						}
					}
				?>
				</select>
 	 		</p>
 	 		<p class="localendar-hide-events">
 	 			<input id="<?php echo $this->get_field_id( 'hide_events' ); ?>" name="<?php echo $this->get_field_name( 'hide_events' ); ?>" type="checkbox" value="<?php echo esc_attr( $defaults['hide_events'] ); ?>" <?php checked( $defaults['hide_events'], 'true' ); ?> />
 	 			<label for="<?php echo $this->get_field_id( 'hide_events' ); ?>"><?php _e( 'Hide events that occur in the previous/next month when applicable?', 'localendar' ); ?></label><br />
 	 			<span class="description"><?php _e( 'This field is applied only when the "Month Block-View" style is selected.', 'localendar' ); ?></span>
 	 		</p>
 	 		<p class="step-3"><strong><?php _e( 'Step 3: Additional Customizations', 'localendar' ); ?></strong></p>
 	 		<p class="localendar-iframe-style">
 	 			<label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Iframe Width?', 'localendar' ); ?></label><br />
 	 			<input id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" type="text" value="<?php echo esc_attr( $defaults['width'] ); ?>" style="width: 100%;" />
 	 			<span class="description"><?php _e( 'Used to determine width of an iframe (if iframe type is selected).', 'localendar' ); ?></span><br /><br />
 	 			<label for="<?php echo $this->get_field_id( 'height' ); ?>"><?php _e( 'Iframe Height?', 'localendar' ); ?></label><br />
 	 			<input id="<?php echo $this->get_field_id( 'height' ); ?>" name="<?php echo $this->get_field_name( 'height' ); ?>" type="text" value="<?php echo esc_attr( $defaults['height'] ); ?>" style="width: 100%;" />
 	 			<span class="description"><?php _e( 'Used to determine height of an iframe (if iframe type is selected).', 'localendar' ); ?></span>
 	 		</p>
 	 		<p class="localendar-query-strings">
 	 			<?php _e( 'Localendar also accepts a number of other query string parameters. If there are extra parameters that you would like appended to your URL, you can place them in the text field below. <a href="http://localendar.com/docs/display/lc/Your+Calendar%27s+URL+and+Parameters" target="_blank">Learn more about formatting your calendar URL</a>.', 'localendar' ); ?><br /><br />
 	 			<label for="<?php echo $this->get_field_id( 'query' ); ?>"><?php _e( 'Extra Query Args', 'localendar' ); ?></label><br />
 	 			<input id="<?php echo $this->get_field_id( 'query' ); ?>" name="<?php echo $this->get_field_name( 'query' ); ?>" type="text" value="<?php echo esc_attr( $defaults['query'] ); ?>" style="width: 100%;" />
 	 		</p>
 	 		</div>
 	 		<?php do_action( 'tgmlo_widget_after_form', $instance );

 	 	}

 	 	/**
		 * Helper function for parsing user input into a usable calendar.
		 *
		 * @since 1.0.0
		 *
		 * @param string $username The localendar username
		 * @param string $type The type of calendar to use
		 * @param string $style The styling of the calendar selection
		 * @param bool $hide_events Flag to set event hiding
		 * @param string $link_text Link text if user chooses to display a linked calendar
		 * @param int $width Width of calendar (iframe only)
		 * @param int $height Height of calendar (iframe only)
		 * @return string $calendar The built calendar
		 */
 	 	public function build_calendar( $username, $type, $style, $hide_events = 'false', $link_text = '', $width = '', $height = '', $query = '' ) {

 	 		$calendar 	= '';
 	 		$width 		= isset( $width ) ? $width : 700;
 	 		$height 	= isset( $height ) ? $height : 600;

 	 		/** Build the calendar */
 	 		switch ( $type ) {
 	 			case 'link' :
 	 				switch ( $style ) {
 	 					case 'mb' :
 	 						if ( 'true' == $hide_events )
 	 							$calendar = '<a class="localendar" href="http://www.localendar.com/public/' . esc_attr( $username ) . '?current_only=Y' . $query . '" target="_blank">' . esc_attr( $link_text ) . '</a>';
 	 						else
 	 							$calendar = '<a class="localendar" href="http://www.localendar.com/public/' . esc_attr( $username ) . '' . $query . '" target="_blank">' . esc_attr( $link_text ) . '</a>';
 	 						break 1;
 	 					case 'mb2' :
 	 						$calendar = '<a class="localendar" href="http://www.localendar.com/public/' . esc_attr( $username ) . '?style=M4' . $query . '" target="_blank">' . esc_attr( $link_text ) . '</a>';
 	 						break 1;
 	 					case 'ml' :
 	 						$calendar = '<a class="localendar" href="http://www.localendar.com/public/' . esc_attr( $username ) . '?style=M1' . $query . '" target="_blank">' . esc_attr( $link_text ) . '</a>';
 	 						break 1;
 	 					case 'wb' :
 	 						$calendar = '<a class="localendar" href="http://www.localendar.com/public/' . esc_attr( $username ) . '?style=W0' . $query . '" target="_blank">' . esc_attr( $link_text ) . '</a>';
 	 						break 1;
 	 					case 'wl' :
 	 						$calendar = '<a class="localendar" href="http://www.localendar.com/public/' . esc_attr( $username ) . '?style=W1' . $query . '" target="_blank">' . esc_attr( $link_text ) . '</a>';
 	 						break 1;
 	 					case 'dv' :
 	 						$calendar = '<a class="localendar" href="http://www.localendar.com/public/' . esc_attr( $username ) . '?style=D0' . $query . '" target="_blank">' . esc_attr( $link_text ) . '</a>';
 	 						break 1;
 	 					case 'th' :
 	 						$calendar = '<a class="localendar" href="http://www.localendar.com/public/' . esc_attr( $username ) . '?style=D2' . $query . '" target="_blank">' . esc_attr( $link_text ) . '</a>';
 	 						break 1;
 	 				}
 	 				break;
 	 			case 'full' :
 	 				switch ( $style ) {
 	 					case 'mb' :
 	 						if ( 'true' == $hide_events )
 	 							$calendar = '<script type="text/javascript" src="http://www.localendar.com/public/' . esc_attr( $username ) . '?include=Y&dynamic=Y&style=M0&current_only=Y' . $query . '"></script>';
 	 						else
 	 							$calendar = '<script type="text/javascript" src="http://www.localendar.com/public/' . esc_attr( $username ) . '?include=Y&dynamic=Y&style=M0' . $query . '"></script>';
 	 						break 1;
 	 					case 'mb2' :
 	 						$calendar = '<script type="text/javascript" src="http://www.localendar.com/public/' . esc_attr( $username ) . '?include=Y&dynamic=Y&style=M4' . $query . '"></script>';
 	 						break 1;
 	 					case 'ml' :
 	 						$calendar = '<script type="text/javascript" src="http://www.localendar.com/public/' . esc_attr( $username ) . '?include=Y&dynamic=Y&style=M1' . $query . '"></script>';
 	 						break 1;
 	 					case 'wb' :
 	 						$calendar = '<script type="text/javascript" src="http://www.localendar.com/public/' . esc_attr( $username ) . '?include=Y&dynamic=Y&style=W0' . $query . '"></script>';
 	 						break 1;
 	 					case 'wl' :
 	 						$calendar = '<script type="text/javascript" src="http://www.localendar.com/public/' . esc_attr( $username ) . '?include=Y&dynamic=Y&style=W1' . $query . '"></script>';
 	 						break 1;
 	 					case 'dv' :
 	 						$calendar = '<script type="text/javascript" src="http://www.localendar.com/public/' . esc_attr( $username ) . '?include=Y&dynamic=Y&style=D0' . $query . '"></script>';
 	 						break 1;
 	 					case 'th' :
 	 						$calendar = '<script type="text/javascript" src="http://www.localendar.com/public/' . esc_attr( $username ) . '?include=Y&dynamic=Y&style=D2' . $query . '"></script>';
 	 						break 1;
 	 				}
 	 				break;
 	 			case 'static' :
 	 				switch ( $style ) {
 	 					case 'mb' :
 	 						if ( 'true' == $hide_events )
 	 							$calendar = '<script type="text/javascript" src="http://www.localendar.com/public/' . esc_attr( $username ) . '?include=Y&style=M0&current_only=Y' . $query . '"></script>';
 	 						else
 	 							$calendar = '<script type="text/javascript" src="http://www.localendar.com/public/' . esc_attr( $username ) . '?include=Y&style=M0' . $query . '"></script>';
 	 						break 1;
 	 					case 'mb2' :
 	 						$calendar = '<script type="text/javascript" src="http://www.localendar.com/public/' . esc_attr( $username ) . '?include=Y&style=M4' . $query . '"></script>';
 	 						break 1;
 	 					case 'ml' :
 	 						$calendar = '<script type="text/javascript" src="http://www.localendar.com/public/' . esc_attr( $username ) . '?include=Y&style=M1' . $query . '"></script>';
 	 						break 1;
 	 					case 'wb' :
 	 						$calendar = '<script type="text/javascript" src="http://www.localendar.com/public/' . esc_attr( $username ) . '?include=Y&style=W0' . $query . '"></script>';
 	 						break 1;
 	 					case 'wl' :
 	 						$calendar = '<script type="text/javascript" src="http://www.localendar.com/public/' . esc_attr( $username ) . '?include=Y&style=W1' . $query . '"></script>';
 	 						break 1;
 	 					case 'dv' :
 	 						$calendar = '<script type="text/javascript" src="http://www.localendar.com/public/' . esc_attr( $username ) . '?include=Y&style=D0' . $query . '"></script>';
 	 						break 1;
 	 					case 'th' :
 	 						$calendar = '<script type="text/javascript" src="http://www.localendar.com/public/' . esc_attr( $username ) . '?include=Y&style=D2' . $query . '"></script>';
 	 						break 1;
 	 				}
 	 				break;
 	 			case 'iframe' :
 	 				switch ( $style ) {
 	 					case 'mb' :
 	 						if ( 'true' == $hide_events )
 	 							$calendar = '<iframe src="http://www.localendar.com/public/' . esc_attr( $username ) . '?current_only=Y' . $query . '" height=' . $height . ' width=' . $width . ' marginheight=0 marginwidth=0 scrolling=no frameborder=0 border=0></iframe>';
 	 						else
 	 							$calendar = '<iframe src="http://www.localendar.com/public/' . esc_attr( $username ) . '' . $query . '" height=' . $height . ' width=' . $width . ' marginheight=0 marginwidth=0 scrolling=no frameborder=0 border=0></iframe>';
 	 						break 1;
 	 					case 'mb2' :
 	 						$calendar = '<iframe src="http://www.localendar.com/public/' . esc_attr( $username ) . '?style=M4' . $query . '" height=' . $height . ' width=' . $width . ' marginheight=0 marginwidth=0 scrolling=no frameborder=0 border=0></iframe>';
 	 						break 1;
 	 					case 'ml' :
 	 						$calendar = '<iframe src="http://www.localendar.com/public/' . esc_attr( $username ) . '?style=M1' . $query . '" height=' . $height . ' width=' . $width . ' marginheight=0 marginwidth=0 scrolling=no frameborder=0 border=0></iframe>';
 	 						break 1;
 	 					case 'wb' :
 	 						$calendar = '<iframe src="http://www.localendar.com/public/' . esc_attr( $username ) . '?style=W0' . $query . '" height=' . $height . ' width=' . $width . ' marginheight=0 marginwidth=0 scrolling=no frameborder=0 border=0></iframe>';
 	 						break 1;
 	 					case 'wl' :
 	 						$calendar = '<iframe src="http://www.localendar.com/public/' . esc_attr( $username ) . '?style=W1' . $query . '" height=' . $height . ' width=' . $width . ' marginheight=0 marginwidth=0 scrolling=no frameborder=0 border=0></iframe>';
 	 						break 1;
 	 					case 'dv' :
 	 						$calendar = '<iframe src="http://www.localendar.com/public/' . esc_attr( $username ) . '?style=D0' . $query . '" height=' . $height . ' width=' . $width . ' marginheight=0 marginwidth=0 scrolling=no frameborder=0 border=0></iframe>';
 	 						break 1;
 	 					case 'th' :
 	 						$calendar = '<iframe src="http://www.localendar.com/public/' . esc_attr( $username ) . '?style=D2' . $query . '" height=' . $height . ' width=' . $width . ' marginheight=0 marginwidth=0 scrolling=no frameborder=0 border=0></iframe>';
 	 						break 1;
 	 				}
 	 				break;
 	 			case 'mini' :
 	 				$calendar = '<style type="text/css">#elsiemini table{width:auto;height:auto;}.localendar-close{position:absolute;top:4px;right:4px;border:none;margin:2px;}.localendar-mini td,th{width:30px;height:30px;text-align:center;vertical-align:middle;font-family:arial;font-size:16px;}.localendar-label,.localendar-time,.localendar-title{font-family:arial;font-size:12px;}.localendar-label{font-weight:bold;font-size:14px;}.localendar-hasEvents{background-color:orange;cursor:pointer;}.localendar-monthName{}.localendar-balloon{text-align:left;}</style>';
 	 				$calendar .= '<script>var lcPopupColor="rgba(230, 230, 230, .9)";var lcPopupOutline="#333333";var lcPopupCornerRadius=20;</script>';
 	 				$calendar .= '<script type="text/javascript" src="http://www.localendar.com/js/PublishedIncludeMini.js"></script>';
					$calendar .= '<script type="text/javascript" src="http://www.localendar.com/public/' . esc_attr( $username ) . '?include=Y&style=M5' . $query . '"></script>';
 	 				break;
 	 		}

 	 		return $calendar;

 	 	}

 	 }
}