<?php
# -------------------------------------------------------------------
# - NAME:        archive.php
# - AUTHOR:      Reto Stauffer
# - DATE:        2014-09-29
# -------------------------------------------------------------------
# - DESCRIPTION: Shows the tournament dates for the active citz.
#                Note: is checking if the current tournament is
#                allready unlocked and the user can see the data.
#                This should happen as soon as the bet-form has been
#                closed.
# -------------------------------------------------------------------
# - EDITORIAL:   2014-09-29, RS: Created file on thinkreto.
# -------------------------------------------------------------------
# - L@ST MODIFIED: 2018-11-14 19:25 on marvin
# -------------------------------------------------------------------
global $WTuser;

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

if ( empty( $_GET['tdate'] ) ) {
    $WTuser->archive_show_bet_data();
} else {
    // --------------------------------------------------------------yy
    // Only show tables if allowed.
    // Current values ("for the current weekend") are
    // locked until all bets have to been placed.
    // --------------------------------------------------------------yy
    if ( $WTuser->check_allowed_to_display_betdata($_GET['tdate']) )
    {
        $older = $WTuser->older_tournament($_GET['tdate']);
        $newer = $WTuser->newer_tournament($_GET['tdate']);
        $aurl = explode('?', $_SERVER['REQUEST_URI'], 2);
        $aurl = 'http://'.$_SERVER['HTTP_HOST'].$aurl[0];
        $cityID = $WTuser->get_current_cityObj()->get('ID');
        $tdate  = $_GET['tdate'];

        ?>

         <div class="wt-twocolumn wrapper">
            <div class="wt-twocolumn column-left" style="width: 65%;">
               <?php
               printf("%s: <b>%s, %s</b>\n",__('Current tournament','wpwt'),
                    $WTuser->date_format($_GET['tdate'],'%A'),
                    $WTuser->date_format($_GET['tdate']));
               ?>
               <br style="clear: both;">
               <?php if ( is_object($older) ) { ?>
               <form style='float: left; padding-right: 3px;' method='post' action='<?php echo $aurl.'?tdate='.$older->tdate; ?>'>
                   <input class="button" type="submit" value="<< <?php _e("older","wpwt"); ?>">
               </form>
               <?php } ?>
               <form style='float: left;' method="post" action="<?php print $WTuser->curPageURL(); ?>">
                   <input class="button" type="submit" name="values" value="<?php _e("Values","wpwt"); ?>">
                   <input class="button" type="submit" name="points" value="<?php _e("Points","wpwt"); ?>">
               </form>
               <?php if ( is_object($newer) ) { ?>
               <form style='float: left; padding-left: 3px;' method='post' action='<?php echo $aurl.'?tdate='.$newer->tdate; ?>'>
                   <input class="button" type="submit" value="<?php _e("newer","wpwt"); ?> >>">
               </form>
               <?php } ?>
                
               <br><br>
               <b><?php _e("Statistics","wpwt"); ?></b><br>
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
                        <?php print $WTuser->get_average_points($cityID, $tdate, "mean", False); ?>
                     </td>
                  </tr>

                  <tr>
                     <td>
                        <desc><?php _e("Median:","wpwt"); ?></desc>
                     </td>
                     <td>
                        <?php print $WTuser->get_average_points($cityID, $tdate, "median", False); ?>
                    </td>
                  </tr>


                  <tr>
                     <td>
                        <desc><?php _e("Max:","wpwt"); ?></desc>
                     </td>
                     <td>
                        <?php print $WTuser->get_average_points($cityID, $tdate, "max", False); ?>
                    </td>
                  </tr>

                  <tr>
                     <td>
                        <desc><?php _e("Min:","wpwt"); ?></desc>
                     </td>
                     <td>
                        <?php print $WTuser->get_average_points($cityID, $tdate, "min", False); ?>
                     </td>
                  </tr>

                 <tr>
                     <td>
                        <desc><?php _e("Range:","wpwt"); ?></desc>
                     </td>
                     <td>
                        <?php print $WTuser->get_average_points($cityID, $tdate, "spread", False); ?>
                     </td>
                  </tr>

                  <tr>
                     <td>
                        <desc><?php _e("Standard deviation:","wpwt"); ?></desc>
                     </td>
                     <td>
                        <?php print $WTuser->get_average_points($cityID, $tdate, "sd", False); ?>
                     </td>
                  </tr>


                  <tr>
                     <td>
                        <desc><?php _e("Sleepy ranking:","wpwt"); ?></desc>
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
                        <?php print $WTuser->get_average_points($cityID, $tdate, "part", False); ?>
                     </td>
                  </tr>

               </table>

           </div>
           <div class="wt-twocolumn column-right colorlegend-wrapper" style="width: 33%;">
               <?php $WTuser->archive_show_colorlegend(); ?>
           </div>
           <div style="clear: both;" class="wt-twocolumn footer"></div>
         </div>

        <!-- <button id='wttable-button-automaten'>Automaten only</button> -->
        <?php
        $points = false;
        if ( array_key_exists('points',$_REQUEST) ) {  $points = true;  }
        // Using the same method for obs and bets.
        // For $showday==1: Saturday
        $WTuser->archive_show( 'obs',  $_GET['tdate'], false,   1);
        $WTuser->archive_show( 'bets', $_GET['tdate'], $points, 1);
        // Using the same method for obs and bets.
        // For $showday==2: Sunday
        $WTuser->archive_show( 'obs',  $_GET['tdate'], false,   2);
        $WTuser->archive_show( 'bets', $_GET['tdate'], $points, 2);

         // Include the weekend ranking as well
        
        $call = sprintf("[wetterturnier_ranking type='weekend' hidebuttons=true tdate=%d city=%d]",
                        $_GET["tdate"],$WTuser->get_current_cityObj()->get('ID'));
               
        print do_shortcode( $call );

    }
}

?>
