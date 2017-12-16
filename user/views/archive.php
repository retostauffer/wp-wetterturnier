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
# - L@ST MODIFIED: 2017-07-15 10:52 on thinkreto
# -------------------------------------------------------------------
global $WTuser;

?>
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
                   <input class="button" type="submit" value="<< <?php _e("older"); ?>">
               </form>
               <?php } ?>
               <form style='float: left;' method="post" action="<?php print $WTuser->curPageURL(); ?>">
                   <input class="button" type="submit" name="values" value="<?php _e("Show Values","wpwt"); ?>">
                   <input class="button" type="submit" name="points" value="<?php _e("Show Points","wpwt"); ?>">
               </form>
               <?php if ( is_object($newer) ) { ?>
               <form style='float: left; padding-left: 3px;' method='post' action='<?php echo $aurl.'?tdate='.$newer->tdate; ?>'>
                   <input class="button" type="submit" value="<?php _e("newer"); ?> >>">
               </form>
               <?php } ?>
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
