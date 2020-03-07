<?php

global $wpdb;
global $WTuser;

$cityObj = $WTuser->get_current_cityObj();
$city    = $cityObj->get('name');

// Access only for logged in users
if ( $WTuser->access_denied() ) { return; }

$betdays = $WTuser->init_options()->wetterturnier_betdays;
$showdays = array();
for ($day=1;$day<=$betdays;$day++) { array_push($showdays,$day);}

?>
<script>
   // Initialize demo table
   jQuery(document).on('ready',function($) {
       (function($) {
           // Also activates the tooltipster used for the
           // "entry modified by" tooltips bets/obs
           $('.data.changed-status').tooltipster({
             delay: 100, contentAsHTML: true, position: 'bottom'
           });
       })(jQuery);
   });
</script>
<?php

$show_locked_info = true;
$showday = 1;
foreach ( $showdays as $showday ) {
   //get current tournament object
   $current = $WTuser->current_tournament(0,false,0,true);
   // First day shows header, the rest doesn't
   if ( $showday == 1 || is_bool($showday) ) { ?>
      <div class="wt-twocolumn wrapper">
         <div class="wt-twocolumn column-left" style="width: 65%;">
            <?php
            printf("<h3>%s: <b>%s, %s</b><h3>\n",__('Current tournament','wpwt'),
                   $current->weekday,$current->readable);
            //TODO: only show buttons if tournament is finished?>
            <form method="post" action="<?php print $WTuser->curPageURL(); ?>">
               <input class="button" type="submit" name="values" value="<?php _e("Show Values","wpwt"); ?>" />
               <input class="button" type="submit" name="points" value="<?php _e("Show Points","wpwt"); ?>" />
            </form>
         </div>
         <div class="wt-twocolumn column-right colorlegend-wrapper" style="width: 33%;">
            <?php $WTuser->archive_show_colorlegend(TRUE); ?>
         </div>
         <div style="clear: both;" class="wt-twocolumn footer"></div>
      </div>
   <?php }
   // run selection (does not work yet)
   /**
   foreach( array("21z","3z","9z") as $i ) {
      if ( array_key_exists($i, $_REQUEST) )  {  $run  = $i;  } else { $run = "9z"; }
   }
   */
   //echo $run;
   if ( array_key_exists('points',$_REQUEST) )  {  $points  = true;  } else { $points = false; }
   // Using a special method of archive_show() to only show MOS forecasts
   $WTuser->archive_show( 'mos', $current->tdate, $points, $showday );
}
?>
<script>
    jQuery(document).on('ready',function($) {
      (function($) {
         // Allows user to sort the tables
         $(".wttable-show").tablesorter({sortList: [[0,0]]});
         $(".wttable-show th").css('cursor', 'pointer');      
      })(jQuery);
    });
</script>
