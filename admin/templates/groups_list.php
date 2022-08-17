<?php
// ------------------------------------------------------------------
/// @page admin/templates/groups_list.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief How the groups admin page will be displayed.
// ------------------------------------------------------------------


// If not allready available, load wordpress class-wp-list table first
if(!class_exists('WP_List_Table')){
   require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
// Import personal list class
require_once( sprintf("%s/../classes/groups_list.php",dirname(__FILE__)) );

// actionlink needed to send a few of the forms on this page
$actionlink =  sprintf('?page=%s',$_REQUEST['page']);

?>

<div class="wrap">


    <h2>Group list</h2>
    <h3>A few important words befor you start managing the groups</h3>
    A few important things you have to know about
    wetterturnier groups before you start manipulating them.
    (i) groups can be modified (name, description) as long as they
    are active, (ii) groups can be deactivated but (iii) groups can
    never be activated again!
    <h3>Group inactive, but I need the group again</h3>
    If it was an accident, the database admin can probably help you.
    If it was no accident: forget it. Reason: the wetterturnier
    script is using those groups to calculate mean tips and stuff.
    To know which mean tip was based on which group, we have to
    keep them in the database. Please create a new group with a new
    name (e.g., for lectures name them WAV2014 or WAV2015).

    <?php //@settings_fields('wp_wetterturnier-group'); ?>
    <?php //@do_settings_fields('wp_wetterturnier-group'); ?>

    <h2>Add a new group</h2>
    <form action="<?php print $actionlink; ?>" method=\"post\">
        <input type="hidden" name="page" value="<?php print $_REQUEST['page']; ?>" />
        <fd>New Group Name:</fd>
        <input type='text' name='name'><br> 
        <fd>Description:</fd>
        <input type='text' name='desc'><br> 
        <fd>&nbsp;</fd>
        <input type='submit' name='submit' class='button' value='Add Group'>
    </form>

    <h2>Overview</h2>

    <?php
    // Success message after changing something
    if ( ! empty($_GET['m']) && $_GET['m'] == 1 ) {
        echo "<div id='message' class='updated fade'><p><strong>"
            .__("Successfully changed the group entry","wpwt")
            ."</strong></p></div>";
    } 

    // Prepare table
    $wp_list_table = new Wetterturnier_Groups_List_Table();
    $wp_list_table->prepare_items();
    $wp_list_table->display();
    ?>
</div>
