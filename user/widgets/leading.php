<?php
/**
 * This is the leading widget for wp-wetterturnier. The leading
 * widget shows the best players of the tournament given the active city.
 *
 * @file leading.php
 * @author Reto Stauffer
 * @date Somewhen back in 2015
 */
class WP_wetterturnier_widget_leading extends WP_Widget
{

    /**
     * Setting up the widget name and the control options
     */
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
        echo "  <div id=\"wtwidget_leading\" class=\"ll-skin-nigran\"></div>\n";

        // Check if textarea is set
        if( $textarea ) { echo '<p class="wp_widget_plugin_textarea">'.$textarea.'</p>'; }

        $this->show_leading();

        echo "</div>";
        echo $after_widget;
    }

    /**
     * The function which creates the frontend output. It basically only
     * creates a html div element with a specific class. The jQuery plugin
     * js/wetterturnier.rankingtable.js is looking for these elements and
     * fills them with data.
     */
    function show_leading() {

        global $WTuser;

        // Check if there are any valid rankings at the moment
        $current = $WTuser->current_tournament;
        $scored  = $WTuser->scored_players_per_town( $current->tdate );

        // No results for the current one? Well, take the one before!
        if ( ! $scored ) {
           $current = $WTuser->older_tournament( $current->tdate );
        }

        $args = array(
            "type"    => "weekend",
            "tdate"   => "17830",
            "limit"   => 3,
            "city"    => $WTuser->get_current_city_id(),
            "tdates"  => array("from"      => $current->tdate,
                               "to"        => $current->tdate,
                               "from_prev" => Null, "to_prev" => Null,
                               "older"     => Null, "newer" => Null,
                               "userinfo"  => true));
        printf("<div class=\"wt-leaderboard\" args=\"%s\"></div>", 
               htmlspecialchars(json_encode($args)));
    }

}

// Add widget to wordpress
add_action('widgets_init', function() { register_widget("WP_wetterturnier_widget_leading"); });

?>
