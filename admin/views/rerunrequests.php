<?php
// ------------------------------------------------------------------
/// @file admin/views/rerunrequests.php
/// @author Reto Stauffer
/// @date 23 June 2017
/// @brief Backend page to display the rerun requests and when (if)
///   they have been executed by the wetteturnier backend python scripts. 
/// @details If an admin changes an observation or a bet of a  user
///   we have to re-run the computation of the mean bets and the 
///   computation of the points. For the current tournament this
///   is no problem as the cronjob on the server does that every
///   X minutes. However, if observations or bets are changed
///   from a previous tournament (which might sometimes be necessary)
///   we have to tell the backend code to do so.
///   When bets or observations are changed they log into the
///   wetterturnier_rerunrequest database table. This page shows
///   the current state (whether there are open rerun requests
///   which did not work or when they have been running.
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

?>
<h1>Wetterturnier Rerun Requests</h1>

<help>
The whole scoring for the wetterturnier is not made inside wordpress
but done by the server in certain intervals using the backend python
package.<br><br>
Every few minutes (currently 15) the cronjob triggers a script called
Chain.py which does the computation of the mean or group bets and the
scoring. This job would not notice when you change some old observations
or bets (e.g., the ones from the weekend before). This won't happen
often, however, it is possible. Therefore wordpress requests a rerun
to re-compute the scores for an older tournament.<br><br>
Whenever you change old observations or bets you will see a log in the
table below. Wordpress asks the backend whether it could rerun the
computation for a certain city and weekend.
<br><br>
This is also a cron-job using Rerun.py started once an hour. Therefore
<b>it can take some time</b> until the points have been recomputed.
As soon as the Rerun.py script did his job you will se a date/time
on the most right in the table below.
</help>

<?php

// ------------------------------------------------------------------
// Loading current tournament
// ------------------------------------------------------------------
$current = $WTadmin->current_tournament;

// ------------------------------------------------------------------
// Loading the latest rerunrequests from the database
// ------------------------------------------------------------------
if(!class_exists('WP_List_Table')){
   require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
require_once( sprintf("%s/../classes/rerunrequest_list.php",dirname(__FILE__)) );
?>
<style>
   td.not-done {
      background-color: #fccdb5;
   }
</style>
<?php
$list_table = new Wetterturnier_Rerunrequest_List_Table(100);
$list_table->prepare_items();
$list_table->display();



















