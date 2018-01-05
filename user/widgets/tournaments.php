<?php
// ------------------------------------------------------------------
// - NAME:        widgets/tournaments.php 
// - AUTHOR:      Reto Stauffer
// - DATE:        2014-07-15
// ------------------------------------------------------------------
// - DESCRIPTION: Shows datepicker on frontend with dates where
//                a tournament takes place or not. 
// ------------------------------------------------------------------

class WP_wetterturnier_widget_tournaments extends WP_Widget
{

    // --------------------------------------------------------------
    // Constructor method: construct the plugin
    // --------------------------------------------------------------
    function __construct() {

        global $WTuser;

        // Widget  options
        $widget_ops = array('classname'=>'wtwidget_tournaments',
                            'description'=>__('Wetterturnier Tournament Dates') );
        // those are completely default at the moment TODO remove or use
        $control_ops = array('width'=>300, 'height'=>350, 'id_base'=>'wp_wetterturnier_tournaments' );
        parent::__construct('wtwidget_tournaments', __('Wetterturnier Dates'),"widget",
                         $widget_ops, $control_ops );

        // Enqueue the ui datepicker
        wp_enqueue_script('jquery-ui-datepicker');

        // Add action to wordpress to make the code callable
        // Adding js code to the head of the page and 
        // "registering" the ajax call necessary for this widget.
        add_action('wp_head',array($WTuser,'tournament_datepicker_widget'));
        add_action('wp_ajax_tournament_datepicker_ajax',array($WTuser,'tournament_datepicker_ajax'));
        add_action('wp_ajax_nopriv_tournament_datepicker_ajax',array($WTuser,'tournament_datepicker_ajax'));

    }


    // --------------------------------------------------------------
    // widget admin form creation
    // --------------------------------------------------------------
    function form($instance) {  
        // Check values
        if( $instance) {
             $title = esc_attr($instance['title']);
             $textarea = esc_textarea($instance['textarea']);
             $textarea2 = esc_textarea($instance['textarea2']);
        } else {
             $title = '';
             $textarea = '';
             $textarea2 = '';
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

        <p>
        <label for="<?php echo $this->get_field_id('textarea2'); ?>"><?php _e('Textarea2:', 'wp_widget_plugin'); ?></label>
        <textarea class="widefat" id="<?php echo $this->get_field_id('textarea2'); ?>" name="<?php echo $this->get_field_name('textarea2'); ?>"><?php echo $textarea2; ?></textarea>
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
        $instance['textarea2'] = strip_tags($new_instance['textarea2']);
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
        $textarea2 = $instance['textarea2'];

        echo $before_widget;
        // Display the widget
        echo '<div class="widget-text wp_widget_plugin_box">';

        // Check if title is set
        if ( $title ) { echo $before_title . $title . $after_title; }
        echo "  <div id=\"wtwidget_tournaments\" class=\"ll-skin-nigran\"></div>\n";

        // Small legend
        echo "<span class='legend status-1'>X</span>".__('Tournament date','wpwt')."<br>";
        echo "<span class='legend status-2'>X</span>".__('No tournament','wpwt')."<br>";

        // Check if textarea is set
        if( $textarea ) { echo '<p class="wp_widget_plugin_textarea">'.$textarea.'</p>'; }

        // Loading parameter infos
        $this->show_bet_counts();

        // Check if textarea is set
        if( $textarea2 ) { echo '<br><p class="wp_widget_plugin_textarea2">'.$textarea2.'</p>'; }
        echo "</div>";
        echo $after_widget;
    }

    // --------------------------------------------------------------
    // Show submitted/open bets
    // --------------------------------------------------------------
    function show_bet_counts() {

         global $wpdb;
         global $WTuser;

         // Loading next tournament
         $next = $WTuser->current_tournament;
         if ( ! $next ) { return false; }

         $sql = array();
         array_push($sql,"SELECT cit.sort AS sort, cit.name AS name, usr.user_login, bet.userID, ");
         array_push($sql,"CASE WHEN stat.submitted IS NULL THEN 0 ELSE 1 END AS status, ");
         array_push($sql,"CASE WHEN usr.user_login LIKE '###' THEN 1 ELSE 0 END AS groups ");
         array_push($sql,"FROM %swetterturnier_bets AS bet");
         array_push($sql,"LEFT OUTER JOIN %swetterturnier_betstat AS stat");
         array_push($sql,"ON bet.tdate = stat.tdate");
         array_push($sql,"AND bet.userID = stat.userID AND bet.cityID = stat.cityID");
         array_push($sql,"LEFT OUTER JOIN wp_users AS usr");
         array_push($sql,"ON bet.userID = usr.ID");
         array_push($sql,"LEFT OUTER JOIN %swetterturnier_cities AS cit");
         array_push($sql,"ON bet.cityID = cit.ID");
         array_push($sql,"WHERE bet.tdate = %d");
         array_push($sql,"GROUP BY bet.userID, bet.cityID");
   
         ##TEST##$sql = sprintf(join("\n",$sql),$wpdb->prefix,$wpdb->prefix,$wpdb->prefix,16437);
         $sql = sprintf(join("\n",$sql),$wpdb->prefix,$wpdb->prefix,$wpdb->prefix,$next->tdate);
         
         $sql = sprintf('SELECT sort, name, COUNT(*) AS total, '
                       .'SUM(tmp.groups) AS groups, SUM(tmp.status) '
                       .'AS active FROM (%s) AS tmp GROUP BY name ORDER BY sort',$sql);
         # - Now we have to replace ### by GRP_%. We have to do this
         #   here not to get troubles with the sprintf commands above
         #   building the sql command.
         $sql = str_replace('###','GRP_%',$sql);

         $res = $wpdb->get_results($sql);

         ?>
         <style class="text/css">
         div.wt-submitted-stat info {
            font-weight: bold;
         }
         div.wt-submitted-stat info.city {
            display: block;
            float: left;
            width: 60%;
            color: black;
         }
         div.wt-submitted-stat info.count {
            display: block;
            float: left;
            width: 13%;
            text-align: right;
            color: black;
         }
         div.wt-submitted-stat info.description {
            display: block;
            width: 100%;
            text-align: right;
         }
         div.wt-submitted-stat bar {
            clear: both;
            display: block;
            height: 3px;
            margin: 2px 0;
            width: 100%;
            background-color: #6592cf;
         }
         </style>
         <?php

         // If we already have information about submitted bets:
         printf("<h1 class='widget-title'>%s</h1>\n",__("Registered bets","wpwt"));
         if ( count($res) > 0 ) {
            printf("   <info class='description'>%s %s %s\n",__("Numbers: partially saved bets, valid bets, group bets","wpwt"),
                    __("for","wpwt"),$WTuser->date_format($next->tdate));
            print "<div class='wt-submitted-stat'>\n<bar></bar>\n";
            // Looping over cities, searching for correct entries and
            // show them on widget. 
            foreach ( $res as $rec ) {
               printf("<info class='city'>%s</info>\n", $rec->name);
               printf("<info class='count'>%d</info>\n",$rec->total); 
               printf("<info class='count'>%d</info>\n",$rec->active);
               printf("<info class='count'>%d</info>\n",$rec->groups);
            }
            print "<bar></bar>\n";
            print "</div>\n";
         } else {
            echo "<div class=\"wetterturnier-info warning\">"
              .__("Currently no data available about the submitted bets for the "
                 ."upcoming tournament. Will be shown as soon as the first bets "
                 ."are submitted to the wetterturnier.","wpwt")
                 ."</div>";
         }

        
    }

}

// Add widget to wordpress
add_action('widgets_init', function() { register_widget("WP_wetterturnier_widget_tournaments"); });

?>
