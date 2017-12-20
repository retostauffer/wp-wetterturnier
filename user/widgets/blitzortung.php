<?php
// ------------------------------------------------------------------
// - NAME:        widgets/blitzortung.php 
// - AUTHOR:      Reto Stauffer
// - DATE:        2014-12-30
// ------------------------------------------------------------------
// - DESCRIPTION: Shows blitzortung minimap if there is an image. 
// ------------------------------------------------------------------

class WP_wetterturnier_widget_blitzortung extends WP_Widget
{

    // --------------------------------------------------------------
    // Constructor method: construct the plugin
    // --------------------------------------------------------------
    function __construct() {

        global $WTuser;

        // Widget  options
        $widget_ops = array('classname'=>'wtwidget_blitzortung',
                            'description'=>__('Wetterturnier Blitzortung') );
        // those are completely default at the moment TODO remove or use
        $control_ops = array('width'=>300, 'height'=>300, 'id_base'=>'wp_wetterturnier_blitzortung' );
        $this->WP_Widget('wtwidget_blitzortung', __('Wetterturnier Blitzortung'),"widget",
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
        echo "  <div id=\"wtwidget_blitzortung\" class=\"ll-skin-nigran\"></div>\n";

        // Check if textarea is set
        if( $textarea ) { echo '<p class="wp_widget_plugin_textarea">'.$textarea.'</p>'; }

        $this->show_blitzortung();

        echo "</div>";
        echo $after_widget;
    }

    // --------------------------------------------------------------
    // Show the blitzortung 
    // --------------------------------------------------------------
    function show_blitzortung() {

        global $wpdb;
        global $WTuser;

        // Rudimentary solution - fixed path
        $the_image  = sprintf("/var/www/html/Rimages/blitzortung/blitzortung_%s.png",
                              $WTuser->get_current_cityObj()->get('hash'));
        $http_image = sprintf("/Rimages/blitzortung/blitzortung_%s.png",
                              $WTuser->get_current_cityObj()->get('hash'));
        // Does the file exist?
        if ( file_exists($the_image) ) {
            echo "<img src=\"".$http_image."\"></img>";
        } else {
           # - Reading lastrun file if existing
           function lastrun_fn() {
              if ( ! file_exists("/var/www/html/Rimages/blitzortung/lastrun") ) { return( False ); }
              $lastrun_file = "/var/www/html/Rimages/blitzortung/lastrun";
              $fid = @fopen($lastrun_file, "r");
              if ( ! $fid ) { return(__('Information about last run not available!','wpwt')); }
              $res = new stdClass();
              $res->content = fread($fid,filesize($lastrun_file));
              $res->string = __("Last run: ","wpwt")." ".$res->content;
              $res->timestamp = strtotime($res->content);
              $res->age       = (int)date("U") - $res->timestamp;
              return( $res ); 
           }
           $lastrun = lastrun_fn();
           // Age of the lastrun
           if ( ! $lastrun ) {
               echo "<div style=\"width: 100; text-align: center\">"
                   .__("Cannot display lightning data, \"lastrun\" file not found!","wpwt")
                   ."<br>\n</div>\n";
           } else if ( $lastrun->age > 12*3600 ) {
               echo "<div style=\"width: 100; text-align: center\">"
                   .__("Seems that we have lost the lightning data stream!","wpwt")
                   ."<br>\n".$lastrun->string."</div>\n";
           } else {
               echo "<div style=\"width: 100; text-align: center\"><gray>"
                   .__("Currently no lightning activity in the region.","wpwt")
                   ."<br>\n".$lastrun->string."</gray></div>\n";
           }
        }
        
    }

}

// Add widget to wordpress
add_action('widgets_init', function() { register_widget("WP_wetterturnier_widget_blitzortung"); });

?>
