<?php
// ------------------------------------------------------------------
// - NAME:        widgets/latestobs.php 
// - AUTHOR:      Reto Stauffer
// - DATE:        2014-12-30
// ------------------------------------------------------------------
// - DESCRIPTION: Shows latestobs minimap if there is an image. 
// ------------------------------------------------------------------

class WP_wetterturnier_widget_latestobs extends WP_Widget
{

    // --------------------------------------------------------------
    // Constructor method: construct the plugin
    // --------------------------------------------------------------
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


    // --------------------------------------------------------------
    // widget admin form creation
    // --------------------------------------------------------------
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
        echo "  <div id=\"wtwidget_latestobs\" class=\"ll-skin-nigran\"></div>\n";

        // Show data first (before the description)
        $this->show_latestobs();

        // Check if textarea is set
        if( $textarea ) { echo '<br><p class="wp_widget_plugin_textarea">'.$textarea.'</p>'; }

        echo "</div>";
        echo $after_widget;
    }

    // --------------------------------------------------------------
    // Show the latestobs 
    // --------------------------------------------------------------
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

            <style type="text/css">
            div.wpwt-synopsymbol {
               float:left; border: 1px solid black; margin: 2px 2px 10px 0px; padding: 2px; text-align: center;
               max-width: 80px;
            }
            div.wpwt-synopsymbol:hover { border-color: #6592cf; }
            </style>
            <script type="text/javascript">
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
               $image = sprintf("/referrerdata/SynopSymbols/synop_current_%d.png",$stnObj->get('wmo'));
               printf("<div class='wpwt-synopsymbol'>%s<br><img src=\"%s\"></img></div>\n",
                      $stnObj->get('wmo'), $image);
            } 
            echo "<div style='clear:both;' />\n";

            // $show_data is just a helper function called within the loop below.
            function show_data( $data, $title, $unit ) {
                printf("<b><darkblue>%s</darkblue></b>&nbsp;&nbsp;"
                      ."%s:&nbsp;&nbsp;<b><darkblue>%s</darkblue></b>&nbsp;[%s]<br>\n",
                      $data->stdmin,$title,$data->value,$unit);
            }

            // Looping over all stations, print value/date/title
            // for the frontend
            foreach ( $WTuser->get_current_cityObj()->stations() as $stnObj ) {
                printf("<h3>%s [%d]</h3>",$stnObj->get('name'),$stnObj->get('wmo'));
                $count = 0;

                // Dry air temperature
                $data = $WTuser->get_raw_obs_values($tablename,$maxage,$stnObj,'t');
                if ( $data ) { $count++; show_data($data,__("Temperature","wpwt"),"C"); }

                // Dew point temperature 
                $data = $WTuser->get_raw_obs_values($tablename,$maxage,$stnObj,'td');
                if ( $data ) { $count++; show_data($data,__("Dew point temp","wpwt"),"C"); }

                // Cloud cover 
                $data = $WTuser->get_raw_obs_values($tablename,$maxage,$stnObj,'cc',1.);
                if ( $data ) {
                    if ( $data->value > 0 ) {
                       //$data->value = (int)round((float)$data->value/100*80);
                       $data->value = (int)floor((float)$data->value/100*8) + 1;
                       if ( $data->value == 9 ) { $data->value = 8; }
                    }
                    $count++;
                    show_data($data,__("Cloud cover","wpwt"),"octa");
                }

                // Wind direction 
                $data = $WTuser->get_raw_obs_values($tablename,$maxage,$stnObj,'ff');
                if ( $data ) { $count++. show_data($data,__("Wind speed","wpwt"),"m/s"); }

                // Wind speed 
                $data = $WTuser->get_raw_obs_values($tablename,$maxage,$stnObj,'dd',1.);
                if ( $data ) { $count++; show_data($data,__("Wind direction","wpwt"),"deg"); }

                // Pressure 
                $data = $WTuser->get_raw_obs_values($tablename,$maxage,$stnObj,'pmsl',100);
                if ( $data ) { $count++; show_data($data,__("Pressure [msl]","wpwt"),"hPa"); }

                // Nothing shown?
                if ( $count == 0 ) {
                    printf( "%s %s",
                            sprintf(__("Sorry, no valid observations available for station %s within ","wpwt"),$stnObj->get("wmo")),
                            sprintf(__("the last %d hours","wpwt"),$nhours) );
                }
            }
        }
        
    }

}

// Add widget to wordpress
add_action('widgets_init', function() { register_widget("WP_wetterturnier_widget_latestobs"); });

?>
