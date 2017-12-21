<?php
// ------------------------------------------------------------------
/// @page admin/templates/groups_edit.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief This page shows the form to change the group details.
///   Furthermore, it also controls the update/edit on the database.
// ------------------------------------------------------------------


// Change content of a group entry
global $wpdb, $WTadmin;
$group = $wpdb->get_row(sprintf('SELECT * FROM %swetterturnier_groups WHERE groupID = %s',$wpdb->prefix,$_GET['group']));

// actionlink needed to send a few of the forms on this page
$actionlink =  sprintf("%s?page=%s",$WTadmin->curPageURL( true ),$_REQUEST["page"]);
?>


<div class="wrap">

    <h2>Edit Group Entry</h2>

    <?php
    // If group is inactive, STOP
    if ( (int)$group->active == 0 ) {
        _e('Editing inactive groups is not allowed! Are you doing nasty things?');
    } else { ?>

    <form method="post" action="<?php print $actionlink; ?>">

        <fd><?php _e('Group name','wpwt'); ?>:</fd>
        <input type='text' name='groupName' value='<?php print $group->groupName; ?>' /><br>

        <fd><?php _e('Group Description','wpwt'); ?>:</fd>
        <input type='text' name='groupDesc' value='<?php print $group->groupDesc; ?>' /><br>

        <fd><?php _e('Active flag','wpwt'); ?>:</fd>
        <input type='radio' name='active' value='0' <?php if ( $group->active == 0 ) { print 'checked'; } ?>> Inactive
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input type='radio' name='active' value='1' <?php if ( $group->active == 1 ) { print 'checked'; } ?>> Active<br>
        <fd>&nbsp;</fd>
        <span style='color: red;'>WARNING: IF YOU CHANGE TO INACTIVE IT IS PERMANENT!</span>

        <input type='hidden' name='groupID' value='<?php print $group->groupID; ?>'>

        <?php @submit_button(); ?>
    </form>

    <?php } ?>

</div>
