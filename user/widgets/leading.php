<?php
// ------------------------------------------------------------------
// - NAME:        widgets/leading.php 
// - AUTHOR:      Reto Stauffer
// - DATE:        2014-12-30
// ------------------------------------------------------------------
// - DESCRIPTION: Shows leading minimap if there is an image. 
// ------------------------------------------------------------------

class WP_wetterturnier_widget_leading extends WP_Widget
{

    // --------------------------------------------------------------
    // Constructor method: construct the plugin
    // --------------------------------------------------------------
    function __construct() {

        global $WTuser;

        // Widget  options
        $widget_ops = array('classname'=>'wtwidget_leading',
                            'description'=>__('Wetterturnier Leading Users') );
        // those are completely default at the moment TODO remove or use
        $control_ops = array('width'=>300, 'height'=>300, 'id_base'=>'wp_wetterturnier_leading' );
        parent::__construct('wtwidget_leading', __('Wetterturnier Leading Users'),"widget",
                         $widget_ops, $control_ops );

    }


    // --------------------------------------------------------------
    // widget admin form creation
    // --------------------------------------------------------------
    function form($instance) {  
        // Check values
        if( $instance) {
             $title = esc_attr($instance['title']);
             $textarea = esc_textarea($instance['textarea']);
        } else {
             $title = '';
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
        $instance = $old_instance;
        // Fields
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['textarea'] = strip_tags($new_instance['textarea']);
        return $instance;
    }


    // --------------------------------------------------------------
    // widget display
    // --------------------------------------------------------------
    function widget( $args, $instance ) {


        extract( $args, EXTR_SKIP );

        // these are the widget options
        $title = $instance['title']; #apply_filters('widget_title', $instance['title']);
        $textarea  = $instance['textarea'];

        echo $before_widget;
        // Display the widget
        echo '<div class="widget-text wp_widget_plugin_box">';

        // Check if title is set
        if ( $title ) { echo $before_title . $title . $after_title; }
        echo "  <div id=\"wtwidget_leading\" class=\"ll-skin-nigran\"></div>\n";

        // Check if textarea is set
        if( $textarea ) { echo '<p class="wp_widget_plugin_textarea">'.$textarea.'</p>'; }

        $this->show_leading();

        echo "</div>";
        echo $after_widget;
    }

    // --------------------------------------------------------------
    // Show the leading 
    // --------------------------------------------------------------
    function show_leading() {

      global $WTuser;
      $WTuser->show_leading(); 

    }

}

// Add widget to wordpress
add_action('widgets_init', function() { register_widget("WP_wetterturnier_widget_leading"); });

?>
