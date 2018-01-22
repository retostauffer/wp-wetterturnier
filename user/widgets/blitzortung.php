<?php
/*
 * This is the blitzortung plugin for wp-wetterturnier.
 * A small script which reads a tiny text file to get the information
 * about the last data update and whether there are strokes in the
 * surrounding or not. If there is lightning activity a small image
 * will be shown.
 * The data source is indirectly blitzortung.org, the data are delivered
 * as a small sqlite3 file from the ACINN, the Atmospheric and Cryospheric
 * Institute Innsbruck in case of Wetterturnier.de.
 *
 * @file blitzortung.php
 * @author Reto Stauffer
 * @date Somewhen back in 2015.
 */
class WP_wetterturnier_widget_blitzortung extends WP_Widget
{

    /**
     * Setting up the widget name and the control options
     */
    function __construct() {

        global $WTuser;

        // Widget  options
        $widget_ops = array('classname'=>'wtwidget_blitzortung',
                            'description'=>__('Wetterturnier Blitzortung') );
        // those are completely default at the moment TODO remove or use
        $control_ops = array('width'=>300, 'height'=>300, 'id_base'=>'wp_wetterturnier_blitzortung' );
        parent::__construct('wtwidget_blitzortung', __('Wetterturnier Blitzortung'),"widget",
                         $widget_ops, $control_ops );

    }


    /**
     * Creates the admin-widget box (drag-and-drop widget with attributes/settings)
     *
     * @param array $instance The widget options
     */
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

    /**
     * Processing widget options on save
     *
     * @param array $new_instance The new options
     *
     * @param array $old_instance The previous options
     *
     * @return array $instance (updated)
     */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        // Fields
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['textarea'] = strip_tags($new_instance['textarea']);
        return $instance;
    }


    /**
     * Outputs the content of the widget
     *
     * @param array $args
     *
     * @param array $instance
     */
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


    /**
     * The 'core function' of this widget: reading a file called 'lastrun'
	 * located in /var/www/html/Rimages/blitzortung/ to see whether there
	 * are new data (output will be ignored if incoming data stream would break down)
     * Displays a small image and a time stamp if there is lightning activity in the
	 * area. Data from ACINN, see class header description.
     */
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
