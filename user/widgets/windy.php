<?php
/**
 * This is the windy widget. Has a work around for the bug on the
 * mobile page, which probably occured because the shortcode in the HTML
 * widget cannot be executed with ZERO width -> OpenGL error
 * Maybe there is another way 2 prevent this sucky error?
 * workaround: display widgets below rst of page on mobile devices (actually default)
 */
class WP_wetterturnier_widget_windy extends WP_Widget
{

    /**
     * Setting up the widget name and the control options
     */
    function __construct() {

        global $WTuser;

        // Widget  options
        $widget_ops = array('classname'=>'wtwidget_windy',
                            'description'=>__('Wetterturnier Windy') );
        // those are completely default at the moment TODO remove or use
        $control_ops = array('width'=>300, 'height'=>200, 'id_base'=>'wp_wetterturnier_windy' );
        parent::__construct('wtwidget_windy', __('Wetterturnier Windy'),"widget",
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

    public function isMobileDevice() {
       return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|iphone|ipad|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
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
        $title = $instance['title'];
        // apply_filters('widget_title', $instance['title']);
        $textarea  = $instance['textarea'];

        echo $before_widget;
        // Display the widget if no mobile device. Maybe use mobile detect in the future
        // http://mobiledetect.net/
        //$width = (int)$_get['width'];
        //echo $window_width = "<script>document.write(window_width);</script>";

        if(isset($_COOKIE["window_width"])){ 
           $window_width = (int)$_COOKIE["window_width"]; 
        } else{ 
           $window_width = 0;
        } 

        //see "fully working example"
        //https://stackoverflow.com/questions/1504459/getting-the-screen-resolution-using-php        
        if ( ($window_width > 320) and !($this->isMobileDevice() or wp_is_mobile()) ) {
            echo '<div class="widget-text wp_widget_plugin_box">';

		      // Check if title is set
		      if ( $title ) { echo $before_title . $title . $after_title; }
		         echo "  <div id=\"wtwidget_windy\" class=\"ll-skin-nigran\"></div>\n";

		#global $WTUser;
		// Show data first (before the description)
		/**$WTUser->shortcode_wetterturnier_windy( $args =
					array('width'=>"300",
					      'heigth'=>"300",
					      'lat'=>NULL,
					      'lon'=>NULL,
					      'zoom'=>5,
					      'level'=>"surface",
					      'overlay'=>"radar",
					      'menu'=>NULL,
					      'message'=>true,
					      'marker'=>true,
					      'calendar'=>NULL,
					      'pressure'=>true,
					      'type'=>"map",
					      'location'=>"coordinates",
					      'detail'=>NULL,
					      'city'=>false) );
		*/
		      // Check if textarea is set
		      if( $textarea ) { echo '<br><p class="wp_widget_plugin_textarea">'.$textarea.'</p>'; }

		      $this->show_windy();
		      echo "</div>";
        }

        echo $after_widget;
    }
    
    function show_windy() {
        
        global $WTuser;
        
        $cityID = $WTuser->get_current_city_id();

        switch ( $cityID ) {

        case 1: //BER
                $lat="52.518611";
                $lon="13.408333";
                break;
        case 2: //VIE
                $lat="48.20849";
                $lon="16.37208";
                break;
        case 3: //ZUR
                $lat="47.37174";
                $lon="8.54226";
                break;
        case 4: //IBK
                $lat="47.265";
                $lon="11.395";
                break;
        case 5: //LEI
                $lat="51.222";
                $lon="12.5023";
                break;
        case 6: //FRA
                $lat="50.033056";
                $lon="8.570556";
                break;
        case 7: //CLB
                $lat="50.865917";
                $lon="7.142744";
                break;
        case 8: //HAN
                $lat="52.37052";
                $lon="9.73322";
                break;
        case 9: //HMB
                $lat="53.57532";
                $lon="10.01534";
                break;
        case 10://STU
                $lat="48.78232";
                $lon="9.17702";
                break;
        case 11://MUN
                $lat="48.13743";
                $lon="11.57549";
                break;
        default:
                $lat="";
                $lon="";
        break;
}

        echo "<iframe width=300 height=250 src='https://embed.windy.com/embed2.html?lat=$lat&lon=$lon&zoom=5&level=surface&overlay=radar&menu=&message=true&marker=&calendar=&pressure=true&type=map&location=coordinates&detail=&detailLat=$lat&detailLon=$lon&metricWind=kt&metricTemp=%C2%B0C&radarRange=-1' frameborder=0></iframe>";
        /*
        $args =
                                array('width'=>"300",
                                      'heigth'=>"300",
                                      'lat'=>NULL,
                                      'lon'=>NULL,
                                      'zoom'=>5,
                                      'level'=>"surface",
                                      'overlay'=>"radar",
                                      'menu'=>NULL,
                                      'message'=>true,
                                      'marker'=>true,
                                      'calendar'=>NULL,
                                      'pressure'=>true,
                                      'type'=>"map",
                                      'location'=>"coordinates",
                                      'detail'=>NULL,
                                      'city'=>false);
        //extract($args);
        include "/wetterturnier-www/html/wp-content/plugins/wp-wetterturnier/user/views/windy.php";

        $WTUser->shortcode_wetterturnier_windy( $args =
                                array('width'=>"300",
                                      'heigth'=>"300",
                                      'lat'=>NULL,
                                      'lon'=>NULL,
                                      'zoom'=>5,
                                      'level'=>"surface",
                                      'overlay'=>"radar",
                                      'menu'=>NULL,
                                      'message'=>true,
                                      'marker'=>true,
                                      'calendar'=>NULL,
                                      'pressure'=>true,
                                      'type'=>"map",
                                      'location'=>"coordinates",
                                      'detail'=>NULL,
                                      'city'=>false) );
*/
    }

}

// Add widget to wordpress
add_action('widgets_init', function() { register_widget("WP_wetterturnier_widget_windy"); });
