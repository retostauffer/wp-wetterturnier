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

<script type="text/javascript">
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
foreach ( $showdays as $showday ) {

   $current = $WTuser->current_tournament(0,false,0,true);
   if ( $WTuser->check_allowed_to_display_betdata($current->tdate) )
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

               <b><?php _e("Current point status:","wpwt"); ?></b><br>
               <table style="min-width: 200px; width: 400px;">
                  <tr>
                     <td>
                        <desc><?php _e("Average points this weekend:","wpwt"); ?></desc>
                     </td>
                     <td>
                        <?php print $WTuser->get_average_points(); ?>
                        &nbsp;&nbsp;<?php _e("Points","wpwt"); ?>
                     </td>
                  </tr>
                  <tr>
                     <td>
                        <desc><?php _e("Judging for people without attendance:","wpwt"); ?></desc>
                     </td>
                     <td>
                        <?php print $WTuser->get_deadman_points(); ?>
                        &nbsp;&nbsp;<?php _e("Points","wpwt"); ?>
                     </td>
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
   }

}

?>
