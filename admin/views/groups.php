<h1>Wetterturnier Group Management</h1>

<help>
   Helps you managing your <b>groups</b>. A group can consist of several
   human or automated players. The mean bet of all active group members will
   take place in the tournament as well. Please note that users can join, but
   also leave groups. The system will store these infos.
   New groups can be created, existing groups can be set to inactive. WARNING:
   as soon as a group was set inactive, it can't be reactivated again!
</help>

<?php
// The group view handles different
// things all for the group manipulating.
// Actions are transported by the _REQUEST "action" argument.
// If set, try to call the correct page.
// If no action is set (default), view grop list.
global $wpdb;
$request = (object)$_REQUEST;

function include_action_file( $filename ) {
    include(sprintf("%s/../templates/%s", dirname(__FILE__),$filename));
}

// - If action is set
if ( ! empty($request->action) ) {

    // Now depending on action we have to do some stuff
    if ( $request->action == "edit" && ! empty($request->group) ) {
        include_action_file('groups_edit.php');
    } else if ( $request->action == "delete" ) {
        echo("<div id='message' class='error fade'><p><strong>"
            .__("Naaa, groups cannot be deleted, as this would change the whole archive! "
               ."You can edit the group and change names, but not delete an entire "
               ."group from the system.", "wpwt")
            ."</strong></p></div>");
    }

// - Show grup_list else 
} else {

    // Update a group
    if ( property_exists($request, "groupID") ) {
        print_r($request);
        // Loading old entry first
        $old = $wpdb->get_row(sprintf('SELECT * FROM %swetterturnier_groups WHERE groupID = %s',
                              $wpdb->prefix,$request->groupID));
        // Creat update array
        $update = array('groupName'=>esc_html(stripslashes($request->groupName)),
                        'groupDesc'=>esc_html(stripslashes($request->groupDesc)));
        if ( (int)$request->active == 0 & (int)$request->active != (int)$old->active ) {
            $update['active'] = $request->active;
            $update['until']  = strftime('%Y-%m-%d %H:%M:%S',time()); 
        }
        $update_flag = $wpdb->update($wpdb->prefix.'wetterturnier_groups',$update,
                                     array('groupID'=>(int)$request->groupID));

        if ( ! $update_flag ) { echo 'Problems while updating.'; }
    } 

    // Add a new group
    if ( ! empty($request->name) & ! empty($request->desc) ) {
        // userID and groupID
        $name = (string)stripslashes(esc_html($request->name ));
        $desc = (string)stripslashes(esc_html($request->desc));

        // Do not allow special characters in group names.
        if (preg_match('/[\ \'^£$%&*()}{@#~?><>,|=+¬]/', $name))
        {
            // one or more of the 'special characters' found in $string
            echo "<div id='message' class='error fade'><p><strong>"
                .__("Group not added. Reason: special characters and blanks not allowed for group names.","wpwt")
                ."</strong></p></div>";
        // If there were no special characters ...
        } else {
           // Check if group allready exists
           $check_grp = count($wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'wetterturnier_groups '
                                        .' WHERE groupName = "'.$name.'"'));
           $check_usr = count($wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'users '
                                        .' WHERE user_login = "'.$name.'"'));
           if ( $check_grp > 0 || $check_usr > 0 ) {
               echo "<div id='message' class='error fade'><p><strong>"
                   .__("Group or user with this name is allready existing! Cannot add again!","wpwt")
                   ."</strong></p></div>";
           } else {
               // If one of the ID's is negative (-9) nothing was choosen.
               if ( strlen($name) <= 0 )  {
                   $request->added = false;
                   echo "<div id='message' class='error fade'><p><strong>"
                       .__("Group not added. Group name has to be set!","wpwt")
                       ."</strong></p></div>";
               }  else {
                   $insert_flag = $wpdb->insert($wpdb->prefix.'wetterturnier_groups',
                                                array('groupName'=>$name,'groupDesc'=>$desc),
                                                array('%s','%s'));
                   echo "<div id='message' class='updated fade'><p><strong>"
                       .__("New Group set. You can use it from now on.","wpwt")
                       ."</strong></p></div>";
               }
               if ( ! $insert_flag ) { echo 'Problems while inserting.'; }
           }
        }
    }

        
    include_action_file('groups_list.php');
}
?>
