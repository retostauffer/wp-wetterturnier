<h1>Wetterturnier Group Application</h1>

<help>
   For managament purposes, we allow registered (and logged in) users to endorse
   themselves for a specific group. However, these endorsements need
   to be approved by an administrator. Here you can find all active
   user-requests to get part of a group. You are allowed to reject, or
   accept these endorsments. It might be nice to inform the user on
   your decision.
</help>

<?php
// The group view handles different
// things all for the group manipulating.
// Actions are transported by the _GET "action" argument.
// If set, try to call the correct page.
// If no action is set (default), view grop list.

global $wpdb;

function include_action_file( $filename ) {
   include(sprintf("%s/../templates/%s", dirname(__FILE__),$filename));
}


// ---------------------------------------------------------------
// Approve or reject application from a user 
// ---------------------------------------------------------------
if ( ! empty($_REQUEST['id']) & ! empty($_REQUEST['action']) ) {
   // userID and groupID
   $appID  = (int)$_REQUEST['id'];
   $action = $_REQUEST['action'];
   $active = (int)$_REQUEST['active'];
  
   // ---------------------------------------------------------
   // If one of the ID's is negative (-9) nothing was choosen.
   // NOTE: if $active == 9 the user applied to be a group member.
   //       if $active == 1 he would like to get out of the group.
   // ---------------------------------------------------------
   if ( strcmp($action,'approve') === 0 & $active == 9 ) { 
      // - Change groupuser status
      $now   = date("Y-m-d H:i:s");
      $check = $wpdb->update(sprintf('%swetterturnier_groupusers',$wpdb->prefix),
                             array('active'=>1,'since'=>$now),array('ID'=>$appID));
      if ( ! $check ) {
         echo "<div id='message' class='error fade'><p><strong>"
             .__("Problems updating entry in database.","wpwt")
             ."</strong></p></div>";
      } else {
         echo "<div id='message' class='updated fade'><p><strong>"
             .__("User successfully added to the group.","wpwt")
             ."</strong></p></div>";
      }
   // ---------------------------------------------------------
   } else if ( strcmp($action,'reject') === 0 & $active == 9 ) {
      $check = $wpdb->delete(sprintf('%swetterturnier_groupusers',$wpdb->prefix),
                             array('ID'=>$appID));
      if ( ! $check ) {
         echo "<div id='message' class='error fade'><p><strong>"
             .__("Problems deleting entry from database.","wpwt")
             ."</strong></p></div>";
      } else {
         echo "<div id='message' class='updated fade'><p><strong>"
             .__("Application rejected, user not added to the group.","wpwt")
             ."</strong></p></div>";
      }
   // ---------------------------------------------------------
   } else if ( strcmp($action,'approve') === 0 & $active == 8 ) { 
      // - Change groupuser status
      $now = date('Y-m-d H:i:s');
      $check = $wpdb->update(sprintf('%swetterturnier_groupusers',$wpdb->prefix),
                             array('active'=>0,'application'=>'','until'=>$now),
                             array('ID'=>$appID));
      if ( ! $check ) {
         echo "<div id='message' class='error fade'><p><strong>"
             .__("Problems updating entry in database.","wpwt")
             ."</strong></p></div>";
      } else {
         echo "<div id='message' class='updated fade'><p><strong>"
             .__("User successfully set to inactive for this group.","wpwt")
             ."</strong></p></div>";
      }
   // ---------------------------------------------------------
   } else if ( strcmp($action,'reject') === 0 & $active == 8 ) {
      $check = $wpdb->update(sprintf('%swetterturnier_groupusers',$wpdb->prefix),
                             array('active'=>1,'application'=>''),
                             array('ID'=>$appID));
      if ( ! $check ) {
         echo "<div id='message' class='error fade'><p><strong>"
             .__("Problems deleting entry from database.","wpwt")
             ."</strong></p></div>";
      } else {
         echo "<div id='message' class='updated fade'><p><strong>"
             .__("Application rejected, user is still active in the group.","wpwt")
             ."</strong></p></div>";
      }
   // ---------------------------------------------------------
   } else {
         echo "<div id='message' class='error fade'><p><strong>"
             .__("Undefined action.","wpwt")
             ."</strong></p></div>";
   }
}
       
// - Show table
include_action_file('application_list.php');

?>
