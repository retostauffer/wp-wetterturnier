<?php
// ------------------------------------------------------------------
// - NAME:        widgets/bbpmessages.php 
// - AUTHOR:      Reto Stauffer
// - DATE:        2014-12-30
// ------------------------------------------------------------------
// - DESCRIPTION: Shows bbpmessages minimap if there is an image. 
// ------------------------------------------------------------------

class WP_wetterturnier_widget_bbpmessages extends WP_Widget
{

    // --------------------------------------------------------------
    // Constructor method: construct the plugin
    // --------------------------------------------------------------
    function __construct() {

        global $WTuser;


        // Widget  options
        $widget_ops = array('classname'=>'wtwidget_bbpmessages',
                            'description'=>__('Wetterturnier bbPM messages') );
        // those are completely default at the moment TODO remove or use
        $control_ops = array('width'=>300, 'height'=>300, 'id_base'=>'wp_wetterturnier_bbpmessages' );
        $this->WP_Widget('wtwidget_bbpmessages', __('Wetterturnier bbPM messages'),"widget",
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

        // If the user is not logged in: hide widget
        if ( ! is_user_logged_in() ) { return false; }

        extract( $args, EXTR_SKIP );

        // these are the widget options
        $title = $instance['title']; #apply_filters('widget_title', $instance['title']);
        $textarea  = $instance['textarea'];

        echo $before_widget;
        // Display the widget
        echo '<div class="widget-text wp_widget_plugin_box">';

        // Check if title is set
        if ( $title ) { echo $before_title . $title . $after_title; }
        echo "  <div id=\"wtwidget_bbpmessages\" class=\"ll-skin-nigran\"></div>\n";

        // Check if textarea is set
        if( $textarea ) { echo '<p class="wp_widget_plugin_textarea">'.$textarea.'</p>'; }

        $this->show_bbpmessages();

        echo "</div>";
        echo $after_widget;
    }

    // --------------------------------------------------------------
    // Show the bbpmessages 
    // --------------------------------------------------------------
    function show_bbpmessages() {

         function is_plugin_active( $plugin ) {
             return in_array( $plugin, (array) get_option( 'active_plugins', array() ) );
         }
         if ( ! is_plugin_active('bbp-messages/index.php') ) { ?>
            <div class="wetterturnier-info error">
               This plugin relies on bbp-messages. The
               bbp-messages plugin is either not installed or not active
               on this system.
            </div>
         <?php } else {
            // Show message count and link to message platform
            $msg_count = (int)do_shortcode("[bbpm-unread-count]");
            $msg_link  = do_shortcode("[bbpm-messages-link]");

            // No new messages
            if ( $msg_count === 0 ) {
               print "<div class='message-info'>\n";
               _e("No unread messages in your inbox.","wpwt");
               print "</div>\n";
               printf("<a href='%s' target='_self'>%s</a>",$msg_link,__("Open Messenger","wpwt"));
            // New messages
            } else {
               ?>
               <style type="text/css">
               .widget_wtwidget_bbpmessages div.wt-messages-count {
                  display: block;
                  text-align: center;
                  width: 33%;
                  float: left;
                  margin: 10px;
                  width: 73px;
                  height: 73px;
                  background-color: #FF6600;
                  font-size: 4em;
                  color: white;
                  border-radius: 5px;
               }
               .widget_wtwidget_bbpmessages div.wt-messages-info {
                  padding: 9px 0 9px 105px;
               }
               .widget_wtwidget_bbpmessages div.wt-messages-info .color {
                  color: black;
               }
               .widget_wtwidget_bbpmessages div.wt-messages-info .big {
                  font-size: 1.5em;
               }
               .widget_wtwidget_bbpmessages bar {
                  display: block;
                  padding: none;
                  width: 100%;
                  height: 3px;
                  background-color: #6592cf;
                  margin: 2px 0;
               }
               </style>
               <div>
                  <div class="wt-messages-count">
                     <?php print $msg_count; ?> 
                  </div>
                  <div class="wt-messages-info">
                     <info><?php _e("You have","wpwt"); ?></info>
                     <br>
                     <bar></bar>
                     <info class="color big">
                       <?php ($msg_count == 1 ? _e("Unread message","wpwt") : _e("Unread messages","wpwt")); ?></info>
                     <br>
                     <bar></bar>
                     <info class="small">
                     <?php printf("<a href='%s' target='_self'>%s</b>",$msg_link,__("Open Messenger","wpwt")); ?>
                     </info><br>
                  </div>
               </div>
            <?php }
         }

    }

}

// Add widget to wordpress
add_action('widgets_init', function() { register_widget("WP_wetterturnier_widget_bbpmessages"); });

?>
