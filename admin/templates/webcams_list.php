<?php
// ------------------------------------------------------------------
/// @page admin/templates/webcams_list.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief How the webcam admin page will be displayed.
// ------------------------------------------------------------------


global $wpdb;
global $WTadmin;

// If not allready available, load wordpress class-wp-list table first
if(!class_exists('WP_List_Table')){
   require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
// Import personal list class
require_once( sprintf("%s/../classes/webcams_list.php",dirname(__FILE__)) );

// actionlink needed to send a few of the forms on this page
$actionlink =  sprintf('?page=%s',$_REQUEST['page']);
?>

<div class="wrap">


    <h2>Webcams list</h2>
    <h3>Add or remove webcams</h3>
    <div class='wpwt-admin-info'>
    You do have the ability to add new cities for the Wetterturnier.
    Please note that some computation is done by some scripts not
    directly associated with the wordpress. Adding a city does not
    mean that there are observation data aferwords etc.!
    </div>

    <h2>Add a new webcam</h2>
    <form action="<?php print $actionlink; ?>" method="post">
        <input type="hidden" name="page" value="<?php print $_REQUEST['page']; ?>" />
        <fd>City:</fd>
        <select name='city'>
            <option value='0'>Select City</option>
            <?php
            // getting cities
            foreach ( $WTadmin->get_all_cityObj( false ) as $cityObj ) {
               printf("    <option value=\"%d\">%s</option>\n",
                      $cityObj->get('ID'), $cityObj->get('name'));
            }
            ?>
        </select><br> 

        <fd>Webcam uri (to png/jpg):</fd>
        <input type='text' name='uri' /><br> 

        <fd>Source desc:</fd>
        <input type='text' name='source' /><br> 

        <fd>Description:</fd>
        <textarea name='desc'></textarea><br> 

        <fd>&nbsp;</fd>
        <input type='submit' name='submit' class='button' value='Add Webcam'>
    </form>

    <h2>Overview</h2>

    <?php
    // Prepare table
    $wp_webcam_table = new Wetterturnier_Webcams_List_Table();
    $wp_webcam_table->prepare_items();
    $wp_webcam_table->display();
    ?>

</div>
