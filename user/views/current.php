<?php
// ------------------------------------------------------------------
// Decide what to do
// ------------------------------------------------------------------
global $WTuser;


// ------------------------------------------------------------------
// It is possible to forward a json-like array to this function.
// Here decoding the json object, save into $inputs used below.
// ------------------------------------------------------------------
// There is an option called 'daybyday'. If not existing assume to
// be false. If false, then show all betdays in one single table.
// Therefore looping over "array(false)" which calls the functions
// below using $showday=false.
// If set to true, then creating an "array(1,...,$betdays)" and
// each forecast/bet day will be shown separately.
$inputs = (object)$args;
if ( ! property_exists($inputs,"daybyday") ) { $inputs->daybyday = false; }
if ( ! $inputs->daybyday ) {
   $showdays = array(false);
} else {
   $betdays = $WTuser->init_options()->wetterturnier_betdays;
   $showdays = array();
   for ($day=1;$day<=$betdays;$day++) { array_push($showdays,$day); }
} ?>

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
// --------------------------------------------------------------yy
// Only show tables if allowed.
// Current values ("for the current weekend") are
// locked until all bets have to been placed.
// --------------------------------------------------------------yy
$show_locked_info = true;
foreach ( $showdays as $showday ) {

   $current = $WTuser->current_tournament(0,false,0,true);
   $tdate   = $current->tdate;
   $cityID  = $WTuser->get_current_city_id();
   if ( $WTuser->check_allowed_to_display_betdata($current->tdate,$show_locked_info) )
   {
      // First day shows header, the rest doesn't
      if ( $showday == 1 || is_bool($showday) ) { ?>
         <div class="wt-twocolumn wrapper">
            <div class="wt-twocolumn column-left" style="width: 65%;">
               <?php
               printf("<h3>%s: <b>%s, %s</b><h3>\n",__('Current tournament','wpwt'),
                      $current->weekday,$current->readable);
               ?>
               <form method="post" action="<?php print $WTuser->curPageURL(); ?>">
                  <input class="button" type="submit" name="values" value="<?php _e("Show Values","wpwt"); ?>" />
                  <input class="button" type="submit" name="points" value="<?php _e("Show Points","wpwt"); ?>" />
               </form>

               <b><?php _e("Preliminary Statistics:","wpwt"); ?></b><br>
               <table style="min-width: 100px; width: 200px;">

                  <tr>
                     <td>
                        <?php print _e("Measure","wpwt"); ?>
                     </td>
                     <td>
                        <?php print _e("Points","wpwt"); ?>
                  </tr>

                  <tr>
                     <td>
                        <desc><?php _e("Mean:","wpwt"); ?></desc>
                     </td>
                     <td>
                        <?php print $WTuser->get_average_points($cityID, $tdate); ?>
                     </td>
                  </tr>

                  <tr>
                     <td>
                        <desc><?php _e("Median:","wpwt"); ?></desc>
                     </td>
                     <td>
                        <?php print $WTuser->get_average_points($cityID, $tdate, "median"); ?>
                    </td>
                  </tr>


                  <tr>
                     <td>
                        <desc><?php _e("Max:","wpwt"); ?></desc>
                     </td>
                     <td>
                        <?php print $WTuser->get_average_points($cityID, $tdate, "max"); ?>
                    </td>
                  </tr>

                  <tr>
                     <td>
                        <desc><?php _e("Min:","wpwt"); ?></desc>
                     </td>
                     <td>
                        <?php print $WTuser->get_average_points($cityID, $tdate, "min"); ?>
                     </td>
                  </tr>

                 <tr>
                     <td>
                        <desc><?php _e("Range:","wpwt"); ?></desc>
                     </td>
                     <td>
                        <?php print $WTuser->get_average_points($cityID, $tdate, "spread"); ?>
                     </td>
                  </tr>

                  <tr>
                     <td>
                        <desc><?php _e("Standard deviation:","wpwt"); ?></desc>
                     </td>
                     <td>
                        <?php print $WTuser->get_average_points($cityID, $tdate, "sd"); ?>
                     </td>
                  </tr>


                  <tr>
                     <td>
                        <desc><?php _e("Sleepy:","wpwt"); ?></desc>
                     </td>
                     <td>
                        <?php print $WTuser->get_sleepy_points($cityID, $tdate); ?>
                     </td>
                  </tr>

                  <tr>
                     <td>
                        <desc><?php _e("Participants:","wpwt"); ?></desc>
                     </td>
                     <td>
                        <?php print $WTuser->get_average_points($cityID, $tdate, "part"); ?>
                     </td>
                  </tr>

               </table>

            </div>
            <div class="wt-twocolumn column-right colorlegend-wrapper" style="width: 33%;">
               <?php $WTuser->archive_show_colorlegend(); ?>
            </div>
            <div style="clear: both;" class="wt-twocolumn footer"></div>
         </div>
      <?php }
   
      // Note: for observations ('obs') there are no points so please
      // always use $points=false on archive_show.
      if ( array_key_exists('points',$_REQUEST) )  {  $points  = true;  } else { $points = false; }
      // Using the same method for obs and bets.
      $WTuser->archive_show( 'obs',  $current->tdate, false,   $showday );
      $WTuser->archive_show( 'bets', $current->tdate, $points, $showday );
   } else { $show_locked_info = false; } // Set flag to false

}

?>
<script>
    jQuery(document).on('ready',function($) {
      (function($) {
         // Allows user to sort the tables
         $(".wttable-show").tablesorter({sortList: [[0,0]],
             sortInitialOrder: "desc", stringTo: "bottom",
             sortReset: true, sortRestart: true}});
         $(".wttable-show th").css('cursor', 'pointer'); 
      })(jQuery);
    });
</script>
