<h1>Deactivate Users</h1>

<help>
In combination with the wordpress "Disable Users" plugin you can deactivate
some users. <b>Users will not be and should never be deleted</b>! 
This would lead to non-matched user bets. 
<br>
What you can do is to disable a user. A user who is disabled <b>cannot
login anymore</b> until he is activated again.
</help>

<?php
if ( ! is_plugin_active('disable-users/init.php') ) { ?>
<div class="wetterturnier-info error">
   This wetterturnier plugin features depends on the
   "Disable Users" plugin. The "Disable Users" plugin has
   to be installed and activated!<br>
   Details can be found
   <a href="https://de.wordpress.org/plugins/disable-users/" target="_new">
      on the plugins page</a>.
</div>
<?php die(); }

// ------------------------------------------------------------------
/// @details Small helper function to activate/deactivate a certain user.
///   When deactivating the function checks whether the user has a 
///   wordpress user-meta information 'ja_disable_user'. If so then
///   the value will be updated and set to 1 (disabled). If not, the
///   meta/key pair will be created and set to 1 (disabled).
///   When activating a user we simply delete this user-meta attribute.
/// @param $userID. Integer, user ID.
/// @param $deactivate. Boolean. If 'true' the user will be deactivated,
///   else activated.
// ------------------------------------------------------------------
function activate_deactivate_user_by_id( $userID, $deactivate ) {
   // Deactivate user
   if ( $deactivate ) {
      if ( ! get_user_meta($userID, "ja_disable_user") ) {
         $check = add_user_meta( $userID, "ja_disable_user", 1, false );
      } else {
         $check = update_user_meta( $userID, "ja_disable_user", 1, false );
      }
   // Else activate user
   } else {
      $check = delete_user_meta( $userID, "ja_disable_user", 1 );
   }
   return $check;
}

// ------------------------------------------------------------------
// If 'action' is present: trigger action!
// ------------------------------------------------------------------
// If 'bulkaction' is not set: set to "-1" (no bulk action to trigger)
if ( empty($_REQUEST['bulkaction']) ) { $_REQUEST['bulkaction'] = "-1"; }
// If bulkaction is not equal to "-1": trigger the bulk action
if ( $_REQUEST["bulkaction"] !== "-1" ) {

   // Searching for all elements named 'user_[0-9]+'. Extract
   // user ID and trigger the wordpress action.
   foreach ( $_REQUEST as $key=>$val ) {
      $to_do = $_REQUEST["bulkaction"] === "deactivate";
      if ( preg_match("/^user_([0-9]+)$/",$key,$matches) ) {
         //printf(" --- %s : %s --- %s<br>\n",$key,$val,$matches[1]);
         activate_deactivate_user_by_id( (int)$matches[1], $to_do );
      }
   }
// If these are single-action requests
} else if ( !empty($_REQUEST["action"]) & !empty($_REQUEST["userID"]) ) {
   // Checking inputs
   if ( !is_numeric($_REQUEST["userID"]) ) {
     echo "<div id='message' class='error fade'><p><strong>"
         ."Input <userID> was not numeric! I cannot modify the "
         ."user as I do not know the correct userID. Stop!" 
         ."</strong></p></div>";
   }
   // Set player to inactive
   $userID = (int)$_REQUEST["userID"];
   activate_deactivate_user_by_id( (int)$userID, $_REQUEST["action"] === "deactivate" );
}




// ------------------------------------------------------------------
// Functionality of this page
// ------------------------------------------------------------------
function include_action_file( $filename ) {
    include(sprintf("%s/../templates/%s", dirname(__FILE__),$filename));
}

// ------------------------------------------------------------------
// Loading the latest rerunrequests from the database
// ------------------------------------------------------------------
if(!class_exists('WP_List_Table')){
   require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
require_once( sprintf("%s/../classes/deactivateusers_list.php",dirname(__FILE__)) );

// actionlink needed to send a few of the forms on this page
$actionlink =  sprintf('?page=%s',$_REQUEST['page']);

// doshow action
if ( empty($_REQUEST['doshow']) ) { $_REQUEST['doshow'] = 'all'; } # default

// prepare data/items with search string if set
if ( empty($_REQUEST['s']) ) { $_REQUEST['s'] = NULL; }
#if ( ! empty($_REQUEST['s']) ) { $search = $_REQUEST['s']; }
#else                           { $search = NULL; }

echo "<help>\n";
printf("<h2>%s</h2>",__("Search/Filter Options","wpwt"));
_e("Search options: you can either search for username or for the number of days since the "
  ."user last time participated in the tournament. If the search string matches '< XXX' or "
  ." '> XXX' (where 'XXX' is the number of days, e.g., '> 100') the table below will be filtered "
  ." and only shows the users which have not played within the last XXX days ('> XXX') or "
  ." which have played within the last XXX days ('< XXX').","wpwt");
echo "</help>\n";

// Generate table data
$list_table = new Wetterturnier_DeactivateUsers_List_Table(50,$_REQUEST['s'],$_REQUEST['doshow']);
$list_table->prepare_items();

// Show table
global $WTadmin;
$link = sprintf("%s?page=%s",$WTadmin->curPageURL(true),$_REQUEST["page"]);
?>
<h2 class="screen-reader-text">Filter users list</h2>
<?php
   $call      = ($_REQUEST["doshow"] === "all"         ? "class='current'" : "");
   $cactive   = ($_REQUEST["doshow"] === "active"      ? "class='current'" : "");
   $cinactive = ($_REQUEST["doshow"] === "deactivated" ? "class='current'" : "");
?>
<ul class="subsubsub">
   <li class="all">
      <a href="<?php printf("%s&doshow=all",$link); ?>" <?php print $call; ?>>Show All </a>
      <span class="count"><?php printf("(%d)",$list_table->count_total); ?></span> |
   </li>
   <li class="active">
      <a href="<?php printf("%s&doshow=active",$link); ?>" <?php print $cactive; ?>>Show Active </a>
      <span class="count"><?php printf("(%d)",$list_table->count_active); ?></span> |
   </li>
   <li class="inactive">
      <a href="<?php printf("%s&doshow=deactivated",$link); ?>" <?php print $cinactive; ?>>Show Deactivated </a>
      <span class="count"><?php printf("(%d)",$list_table->count_inactive); ?></span>
   </li>
</ul>

<form action="<?php print $actionlink; ?>" method="get">
   <input type="hidden" name="page" value="<?php print $_REQUEST['page']; ?>" />
   <input type="hidden" name="doshow" value="<?php print $_REQUEST['doshow']; ?>" />
   <?php $list_table->search_box('search', 'user_login'); ?>
   <?php $list_table->display(); ?>
</form>
