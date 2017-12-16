<h1>Wetterturnier Group Member Management</h1>

<help>
   While you are able to change the <b>group</b> settings in the <b>group</b>
   menu entry, you can change the members/users in a group in here. 
   It allows you to add users to a group (set them as active group members),
   or remove them from a group (will set them inactive). As long as a user
   is active in a group, his/her bet will be included while computing the
   mean group bet for a certain tournament.
   Note that you - as an administrator - are able to add random people to
   random groups. However, there is a small application form where users can
   endorse themselves to get a new member of an existing group. An admin has
   to approve/reject these endorsements (see <b>group application</b>).
</help>

<help>
   <b>Users count as active group members</b> when they have been <b>activated
   on the day of the tournament</b> or have <b>not been deactivated before the
   end of the day of the tournament</b>.
   <br>
   <b>An example:</b> let's assume that there was a tournament on the
   24th of June 2017 (2017-06-23). If you <b>add a user</b> to a group
   till "2017-06-23 23:59:59" it will be included in the tournament (even
   if the tournament starts at 16 UTC!.
   Users which have been members at 00 UTC of the day of the tournament
   <b>will be included as well</b>. If you <b>disable a user</b> in a certain group
   on the day of the tournament (later or equal to "2017-06-23 00:00:00") it 
   <b>will be included in the mean bet</b>.

   In SQL this is (see python backend code, database.get_bet_data() function):<br>
   [...] AND gu.since <= '2017-06-24 00:00:00' AND (gu.until IS NULL OR gu.until >= '2017-06-23 00:00:00')
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

// - If action is set
if ( ! empty($_GET['action']) ) {

   // Now depending on action we have to do some stuff
   if ( $_GET['action'] == "edit" && ! empty($_GET['group']) ) {
      include_action_file('groupusers_edit.php');
      break;
   } else if ( $_GET['action'] == "delete" && ! empty($_GET["groupuserID"]) ) {

      if ( ! is_numeric($_GET["groupuserID"]) ) {
         die("ERROR: got wrong inputs for delete user from group action!");
      }
      // Delete entry from database
      $wpdb->delete( sprintf("%swetterturnier_groupusers",$wpdb->prefix),
                     array("ID"=>(int)$_GET["groupuserID"]) );

   }
}


// - Show grup_list 

// Adding User 
if ( ! empty($_REQUEST['user']) & ! empty($_REQUEST['group']) ) {
    // userID and groupID
    $userID  = (int)$_REQUEST['user'];
    $groupID = (int)$_REQUEST['group'];


    // If one of the ID's is negative (-9) nothing was choosen.
    if ( $userID < 0 | $groupID < 0 ) {
        $_REQUEST['added'] = false; 
        echo "<div id='message' class='error fade'><p><strong>"
            .__("Not added! Please choose user and group propperly!","wpwt")
            ."</strong></p></div>";
    } else {

        // Check if is allreay active in the group
        $check = $wpdb->get_row(sprintf('SELECT ID FROM %swetterturnier_groupusers WHERE '
                   .' active = 1 AND userID = %d AND groupID = %d',
                   $wpdb->prefix, $userID, $groupID));
        if ( count($check) == 0 ) {
            $insert_flag = $wpdb->insert($wpdb->prefix.'wetterturnier_groupusers',
                                         array('userID'=>$userID,'groupID'=>$groupID),
                                         array('%d','%d'));
            echo "<div id='message' class='updated fade'><p><strong>"
                .__("User added to selected group as a group member.","wpwt")
                ."</strong></p></div>";
        } else {
            echo "<div id='message' class='error fade'><p><strong>"
                .__("Can't add second time. User is allready an active member of the group.","wpwt")
                ."</strong></p></div>";
        }
    }
    if ( ! $insert_flag ) { echo 'Problems while inserting.'; }
} 
    
include_action_file('groupusers_list.php');

?>
