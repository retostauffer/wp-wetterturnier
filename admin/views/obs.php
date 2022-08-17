<?php
// ------------------------------------------------------------------
/// @file admin/views/obs.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief Backend page to display the latest observations of the
///   wetterturnier.
/// @details This view creates the wetterturnier admin backend view
///   to see and/or motify the latest observations used in the
///   wetterturnier wordpress plugin.
// ------------------------------------------------------------------

global $wpdb;
global $WTadmin;

// There are two ways to use this script. If you have
// a direct link the script automatically leads you to
// the propper selection of city, user, and tournament date.
// But you can also "reset" the options to start from the
// beginning. Here I am creating this button.

$reset_url  = sprintf('%s?page=%s',$WTadmin->curPageURL(true),$_GET['page']);
$reset_link = sprintf("<a href=\"%s\" target\"_self\" style=\"color: red;\">%s</a>",
                  $reset_url,"[RESET SELECTION]");

// Small helper function
function include_action_file( $filename ) {
    include(sprintf("%s/../templates/%s", dirname(__FILE__),$filename));
}

// ------------------------------------------------------------------
// Loading current tournament
// ------------------------------------------------------------------
$current = $WTadmin->latest_tournament( (int)date("%s")/86400 );


// ------------------------------------------------------------------
// First of all, the admin user has to specify a city for which
// the obs should be shown. If not set (cityID), then a list of
// all active cities will be shown first.
// ------------------------------------------------------------------
if ( empty($_REQUEST['cityID']) ) {
   $cityID = (int)$wpdb->get_row(sprintf("SELECT ID FROM %swetterturnier_cities ORDER BY ID ASC",$wpdb->prefix))->ID;
} else { $cityID = (int)$_REQUEST['cityID']; }

// ------------------------------------------------------------------
// Display city selection box
// ------------------------------------------------------------------
echo "<br><br>\n"
    ."<div style=\"padding: 5px; background-color: #CBCAD1;\">\n"
    ."  <form method=\"get\" action=\"".$WTadmin->curPageURL(true)."\">\n"
    ."    <label style=\"margin-right: 15px;\">Choose a city:</label>\n"
    ."    <select name=\"cityID\">\n";
    // Show options
    foreach ( $WTadmin->get_all_cityObj( false ) as $cityObj ) {
       printf("      <option value=\"%d\"%s>%s</option>\n",
          $cityObj->get('ID'),($cityObj->get('ID')==$cityID ? " selected" : ""),$cityObj->get('name'));
    }
echo "    </select>\n"
    ."    <input type=\"submit\" value=\"Choose City\"></input>\n"
    ."    <input type=\"hidden\" name=\"page\" value=\"".$_GET['page']."\"></input>\n"
    ."  </form>\n"
    ."</div>\n";
?>
<h1>Managing observations</h1>

<help>
   The <b>observations</b> of the last tournament can be changed here.
   Please note that the system stores who changed values, this information
   will also be visible to our users.

   Like for the <b>bets</b> the observations can only be changed for the ongoing/last
   tournament as they will directly affect the points and ranking. There is a cronjob
   running in the background, computing the user points every few minutes. If you change
   observations, this cron-job should re-compute the points within a few minutes, but
   not live.
</help>

<?php

// ------------------------------------------------------------------
// Include talbe file.
// ------------------------------------------------------------------
include_action_file("obs_list.php");

?>

