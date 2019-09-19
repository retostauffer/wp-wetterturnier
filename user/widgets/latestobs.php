<?php
/**
 * This is the widget displaying the latest observations for all stations
 * attached to a specific city (the active city). Note that this plugin
 * uses the @ref wetterturnier_latestobsClass which itself makes use of the
 * 'obs' database (proper read-permissions have to be set).
 * If there are no data at all or the latest observations are too old they
 * will not be displayed.
 *
 * @file latestobs.php
 * @author Reto Stauffer
 * @date Somewhen back in 2015
 */
class WP_wetterturnier_widget_latestobs extends WP_Widget
{

    /**
     * Setting up the widget name and the control options
     */
    function __construct() {

        global $WTuser;

        // Widget  options
        $widget_ops = array('classname'=>'wtwidget_latestobs',
                            'description'=>__('Wetterturnier latest obs') );
        // those are completely default at the moment TODO remove or use
        $control_ops = array('width'=>300, 'height'=>300, 'id_base'=>'wp_wetterturnier_latestobs' );
        parent::__construct('wtwidget_latestobs', __('Wetterturnier latest obs'),"widget",
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
        } else { $title = ''; $textarea = ''; }
        ?>
        
        <p>
        <label for="<?php echo $this->get_field_id('title'); ?>">
            <?php _e('Widget Title', 'wp_widget_plugin'); ?>
        </label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        
        <p>
        <label for="<?php echo $this->get_field_id('textarea'); ?>">
            <?php _e('Textarea:', 'wp_widget_plugin'); ?>
        </label>
        <textarea class="widefat" id="<?php echo $this->get_field_id('textarea'); ?>" name="<?php echo $this->get_field_name('textarea'); ?>">
            <?php echo $textarea; ?>
        </textarea>
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
        echo "  <div id=\"wtwidget_latestobs\" class=\"ll-skin-nigran\"></div>\n";

        // Show data first (before the description)
        $this->show_latestobs();

        // Check if textarea is set
        if( $textarea ) { echo '<br><p class="wp_widget_plugin_textarea">'.$textarea.'</p>'; }

        echo "</div>";
        echo $after_widget;
    }

    /**
     * The 'core function' of this widget: loads latest observations from database.
     * if the data are too old (older than $nhours) they won't be displayed, e.g.,
     * if the data stream breaks down.
     */
    function show_latestobs() {

        global $wpdb;
        global $WTuser;

        // Table name
        $tablename = "obs.live";

        // Check if we can reach the table or not.
        $query = $wpdb->get_row(sprintf("SELECT 1 FROM %s LIMIT 1",$tablename));
        if ( ! $query ) {
            $msg = sprintf(__("Cannot reach table <b>%s</b>! Sorry.","wpwt"),$tablename);
            printf("<div class=\"wetterturnier-info error\">%s</div>",$msg);
            $reachable = false;
        } else {
            $reachable = true;
        }

        // Looping over all active cites
        if ( $reachable ) {
            // Maximum age of the observations: 12 hours here
            $nhours = 12;
            $maxage = (int)date('U') - $nhours*3600;
            ?>

            <style>
            .wpwt-synopsymbol {
               float:left; border: 1px solid black;
               margin: 2px 2px 10px 0px;
               padding: 2px; text-align: center;
               max-width: 80px;
               max-height: 120px;
               width: auto;
               height: auto;
            }
            .wpwt-synopsymbol img {
               max-width: 80px;
               max-height: 80px;
               width: auto;
               height: auto;
            }
            div.wpwt-synopsymbol:hover { border-color: #6592cf; }
            </style>
            <script>
            jQuery(document).on('ready',function() {
              (function($) {
                 $.each( $('div.wpwt-synopsymbol').find('img'), function() {
                    $(this).error(function() {
                       $(this).unbind('error')
                         .attr('src','/referrerdata/SynopSymbols/missing.png');
                    });
                 });
              })(jQuery);
            });
            </script>

            <?php
            // Show latest synop symbols
            foreach ( $WTuser->get_current_cityObj()->stations() as $stnObj ) {
               if ( $stnObj->get("active") != 1 ) { continue; }
	       $image = sprintf("/referrerdata/SynopSymbols/synop_current_%d.png",$stnObj->get('wmo'));
               printf("<div class='wpwt-synopsymbol'>%s<br><img src=\"%s\"></img></div>\n",
                      $stnObj->get('wmo'), $image);
            } 
            echo "<div style='clear:both;'></div>\n";

            // $show_data is just a helper function called within the loop below.
            function show_data( $time, $value, $title, $unit ) {
                printf("<b><darkblue>%s</darkblue></b>&nbsp;&nbsp;"
                      ."%s:&nbsp;&nbsp;<b><darkblue>%s</darkblue></b>&nbsp;[%s]<br>\n",
                      $time,$title,$value,$unit);
            }

            // Looping over all stations, print value/date/title
            // for the frontend
            foreach ( $WTuser->get_current_cityObj()->stations() as $stnObj ) {
                if ( $stnObj->get("active") != 1 ) { continue; }    
		printf("<h3>%s [%d]</h3>",$stnObj->get('name'),$stnObj->get('wmo'));
                $count = 0;

                // Loading data from database
                $latestobsObj = new wetterturnier_latestobsObject( $stnObj, $maxage, Null, 1 );

                // No data in the object (no data loaded from database)
                if ( ! $latestobsObj->has_data() ) {
                    printf(__("Sorry, no valid observations available for station %s within ","wpwt"),
                              $stnObj->get("wmo"));
                    printf(__("the last %d hours","wpwt"),$nhours);
                    continue;
                }

                $time = sprintf("%04d",$latestobsObj->get_value("stdmin"));
                $time = sprintf("%02d:%02d",substr($time,0,2),substr($time,2,4));

                // Dry air temperature
                $value = $latestobsObj->get_value( "t", Null, "%.1f" );
                if ( $value ) { show_data($time,$value,__("Temperature","wpwt"),"C"); }

                // Dew point temperature 
                $value = $latestobsObj->get_value( "td", Null, "%.1f" );
                if ( $value ) { show_data($time,$value,__("Dew point temp","wpwt"),"C"); }

                // Cloud cover 
                $value = $latestobsObj->get_value( "cc", Null, "%d" );
                if ( $value ) { show_data($time,$value,__("Cloud cover","wpwt"),"%"); }

                // Wind direction 
                $value = $latestobsObj->get_value( "ff", Null, "%.1f" );
                if ( $value ) { show_data($time,$value,__("Wind speed","wpwt"),"m/s"); }

                // Wind speed 
                $value = $latestobsObj->get_value( "dd", Null, "%d" );
                if ( $value ) { show_data($time,$value,__("Wind direction","wpwt"),"deg"); }

                // Pressure 
                $value = $latestobsObj->get_value( "pmsl", Null );
                if ( $value ) { show_data($time,sprintf("%.2f",$value/100.),__("Pressure [msl]","wpwt"),"hPa"); }

            }
        }
        
    }

}

// Add widget to wordpress
add_action('widgets_init', function() { register_widget("WP_wetterturnier_widget_latestobs"); });

?>
