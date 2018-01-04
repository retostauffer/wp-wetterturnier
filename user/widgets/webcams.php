<?php
// ------------------------------------------------------------------
// - NAME:        widgets/widgets.php 
// - AUTHOR:      Reto Stauffer
// - DATE:        2018-01-03
// ------------------------------------------------------------------
/// @details Widget to display the webcams.
class WP_wetterturnier_widget_webcams extends WP_Widget
{

   // --------------------------------------------------------------
   // Constructor method: construct the plugin
   // --------------------------------------------------------------
   function __construct() {

      global $WTuser;

      // Widget  options
      $widget_ops = array('classname'=>'wtwidget_webcams',
                          'description'=>__('Wetterturnier Webcams') );
      // those are completely default at the moment TODO remove or use
      $control_ops = array('width'=>300, 'height'=>300, 'id_base'=>'wp_wetterturnier_webcams' );
      $this->WP_Widget('wtwidget_webcams', __('Wetterturnier Webcams'),"widget",
                       $widget_ops, $control_ops );

   }


   // --------------------------------------------------------------
   // widget admin form creation
   // --------------------------------------------------------------
   function form($instance) {  
      // Check values
      if( $instance) {
           $title    = esc_attr($instance['title']);
           $textarea = esc_textarea($instance['textarea']);
      } else {
           $title    = '';
           $textarea = '';
      }
      ?>
      
      <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', 'wp_widget_plugin'); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
      </p>
      
      <p>
      <label for="<?php echo $this->get_field_id('textarea'); ?>"><?php _e('Textarea:', 'wp_widget_plugin'); ?></label>
      <textarea class="widefat" id="<?php echo $this->get_field_id('textarea'); ?>" name="<?php echo $this->get_field_name('textarea'); ?>"><?php echo $textarea; ?></textarea>
      </p>

      <?php
   }

   // --------------------------------------------------------------
   // widget update
   // --------------------------------------------------------------
   function update($new_instance, $old_instance) {
      $instance             = $old_instance;
      $instance['title']    = strip_tags($new_instance['title']);
      $instance['textarea'] = strip_tags($new_instance['textarea']);
      return $instance;
   }


   // --------------------------------------------------------------
   // widget display
   // --------------------------------------------------------------
   function widget( $args, $instance ) {
      extract( $args, EXTR_SKIP );

      // these are the widget options
      $title     = $instance['title'];
      $textarea  = $instance['textarea'];

      echo $before_widget;
      // Display the widget
      echo '<div class="widget-text wp_widget_plugin_box">';

      // Check if title is set
      if ( $title ) { echo $before_title . $title . $after_title; }
      echo "  <div id=\"wtwidget_webcams\"></div>\n";

      // Check if textarea is set
      if( $textarea ) { echo '<p class="wp_widget_plugin_textarea">'.$textarea.'</p>'; }

      $this->show_webcams();

      echo "</div>";
      echo $after_widget;
   }

   // --------------------------------------------------------------
   // Show the blitzortung 
   // --------------------------------------------------------------
   function show_webcams() {

      global $wpdb;
      global $WTuser;

      // Loading all webcams for current city
      $cityID  = $WTuser->get_current_cityObj();
      $webcams = $wpdb->get_results( sprintf("SELECT ID FROM %swetterturnier_webcams "
                    ." WHERE cityID = %d;", $wpdb->prefix, (int)$cityID->get("ID")) );
      if ( count($webcams) == 0 ) {
         print __("No webcams defined for","wpwt")." ".$cityID->get("name").".";
         return;
      }

      // Load webcam objects
      $objects = array();
      foreach ( $webcams as $rec ) {
         $webcamObj = new wetterturnier_webcamObject( (int)$rec->ID );
         $webcamObj->display_webcam();
      }

   }

}

// Add widget to wordpress
add_action('widgets_init', function() { register_widget("WP_wetterturnier_widget_webcams"); });

?>
