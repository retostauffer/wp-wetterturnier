<?php
// ------------------------------------------------------------------
/// @page admin/templates/groupusers_list.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief How the group members admin page will be displayed.
// ------------------------------------------------------------------


global $wpdb;

// If not allready available, load wordpress class-wp-list table first
if(!class_exists('WP_List_Table')){
   require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
// Import personal list class
require_once( sprintf("%s/../classes/groupusers_list.php",dirname(__FILE__)) );

// actionlink needed to send a few of the forms on this page
$actionlink =  sprintf('?page=%s',$_REQUEST['page']);

// If action not empty: trigger the action
if ( ! empty($_REQUEST["action"]) ) {
   if ( $_REQUEST["action"] === "setinactive" ) {

      // Get the date/time when the user has been added to the group.
      $res = $wpdb->get_row(sprintf("SELECT since FROM %swetterturnier_groupusers WHERE ID = %d",
                            $wpdb->prefix,$_REQUEST["groupuserID"]));
      // Time in seconds the user has been active till now. If this is less
      // than 86400 (which is one day) we will remove the user from the group
      // rather than set him/her inactive.
      $where = array("ID"=>$_REQUEST["groupuserID"]);
      $tmp = abs((int)strtotime($res->since) - (int)date("U"));
      if ( $tmp < 86400 ) {
         $msg = "The time between activation and setting the user to inactive "
               ."was less than 24h. Therefore the user was deleted from the group.";
         $wpdb->delete(sprintf("%swetterturnier_groupusers",$wpdb->prefix),$where);
      } else {
         $msg = "User has successfully been set to inactive.";
         $data = array("active"=>0,"until"=>date("Y-m-d H:i:s"));
         $wpdb->update(sprintf("%swetterturnier_groupusers",$wpdb->prefix),$data,$where);
      }
      // Show message
      printf("<div id='message' class='updated fade'><p><strong>%s</strong></p></div>",$msg);
   }
}
?>

<div class="wrap">


    <h2>Group Member list</h2>

    <help>
    Only active members are displayed. The concept: each of the defined
    groups can contain a number of users. A user can occur in more than
    just one group (being a member of multiple different groups).
    As soon as a user is defined (counts at the time where the tournament
    send form closes) he counts for the mean-tip of the group as long
    as the user is not inactive. Inactive users must be kept in case that
    the mean bets/points have to be re-computed for a historical tournament
    and can therefore not be deleted but set to inactive.
    <b>Please note:</b> if a user is set to inactive which was just added
    to the group within 24 hours the system imagines that you made a mistake
    and will <b>delete</b> the user rather than to set him/her inactive.
    </help>

    <h2>Add new Member to a Group</h2>
    <?php
    // Loading user names and group names
    $users  = $wpdb->get_results("SELECT ID, user_login FROM ".$wpdb->users." ORDER BY user_login ASC");
    $groups = $wpdb->get_results("SELECT groupID, groupName FROM ".$wpdb->prefix."wetterturnier_groups  ORDER BY groupName ASC");
    ?>
    <form action="<?php print $actionlink; ?>" method=\"post\">
        <input type="hidden" name="page" value="<?php print $_REQUEST['page']; ?>" />
        <fd>Chose user and group to add a new one:&nbsp;&nbsp;</fd>
        <select name='user'>
            <option value="-9">----- CHOOSE USER -----</option>
            <?php foreach ( $users as $item ) { echo "<option value=\"".$item->ID."\">".$item->user_login."</option>"; } ?>
        </select>
        <select name='group'>
            <option value="-9">----- CHOOSE GROUP -----</option>
            <?php foreach ( $groups as $item ) { echo "<option value=\"".$item->groupID."\">".$item->groupName."</option>"; } ?>
i   
        </select>
        <input type="submit" name="" class="button" value="Add">
    </form>

    <h2>Current entries</h2>

    Shows all registered group members.<br>
    The search option (top right of the table) searches for the string you
    enter in 'User Name' and 'Group Name'.

    <?php
    // Success message after changing something
    if ( ! empty($_GET['m']) && $_GET['m'] == 1 ) {
        echo "<div id='message' class='updated fade'><p><strong>"
            .__("Successfully changed the group entry","wpwt")
            ."</strong></p></div>";
    } 


    // prepare data/items with search string if set
    if ( ! empty($_REQUEST['s']) ) { $search = $_REQUEST['s']; }
    else                           { $search = NULL; }

   // Shows the talbe of the users which have applied for
   // a group
   $wp_list_table = new Wetterturnier_Groupusers_List_Table( $search );
   $wp_list_table->prepare_items();

   global $WTadmin;
   $link = sprintf("%s?page=%s",$WTadmin->curPageURL(true),$_REQUEST["page"]);

   ?>
   <form action="<?php print $actionlink; ?>" method="get">
      <input type="hidden" name="page" value="<?php print $_REQUEST['page']; ?>">
      <?php $wp_list_table->search_box('search', 'user_login'); ?>
      <?php $wp_list_table->display(); ?>
   </form>

</div>
