<?php
// ------------------------------------------------------------------
/// @page admin/views/bets.php
/// @author Reto Stauffer
/// @date 16 June 2017
// ------------------------------------------------------------------

global $wpdb;
global $WTadmin;

// There are two ways to use this script. If you have
// a direct link the script automatically leads you to
// the propper selection of city, user, and tournament date.
// But you can also "reset" the options to start from the
// beginning. Here I am creating this button.

$reset_url = sprintf('%s?page=%s',$WTadmin->curPageURL(true),$_GET['page']);
$reset_link = sprintf("<a href=\"%s\" target\"_self\" style=\"color: red;\">%s</a>",
                  $reset_url,"[RESET SELECTION]");

error_reporting(E_ALL);
ini_set('display_errors', 1);

function include_action_file( $filename ) {
    include(sprintf("%s/../templates/%s", dirname(__FILE__),$filename));
}

// ------------------------------------------------------------------
// Loading current tournament
// ------------------------------------------------------------------
$current = $WTadmin->latest_tournament( (int)date("%s")/86400 );


// ------------------------------------------------------------------
// First of all, the admin user has to specify a city for which
// the bets should be shown. If not set (cityID), then a list of
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
<h1>Managing user bets</h1>

<div class='wrap'>
   <help>
      You are allowed to change user bets. Please note that the system will
      store the information who changed the values. This will be visible to the
      users as well (transparency). All submitted bets will be shown in the list below,
      does not matter if they were valid (all parameters are ok), or invalid (at least one
      parameter missing). If the user is in the list, you are able to just edit
      the bet and save the data to the database. <br>
   
      If a user was not able to access the internet, and hasn't submitted ANY value,
      then he/she won't show up in the list of bets below. The form on top (add new bet)
      can be used to insert a bet for a specific user/city.<br>
      
      NOTE: this is only allowed for the current tournament, not for older ones. The simple
      reason: there is a cronjob running every few minutes computing the points - but only
      for the ongoing tournament. Changing old bets would therefore have no effect on the
      points and obscure the data (points/bets wont match anymore).
      If you change something here, the cronjob should compute the new points within the
      next few minutes (not live!!).
   </help>
</div>

<?php

// ------------------------------------------------------------------
// Include talbe file.
// ------------------------------------------------------------------
include_action_file("bets_list.php");

?>

