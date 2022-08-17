<?php
// ------------------------------------------------------------------
/// @page admin/templates/station_list.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief How the stations admin page will be displayed.
// ------------------------------------------------------------------


global $wpdb;
global $WTadmin;

// If not allready available, load wordpress class-wp-list table first
if(!class_exists('WP_List_Table')){
   require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
// Import personal list class
require_once( sprintf("%s/../classes/cities_list.php",dirname(__FILE__)) );
require_once( sprintf("%s/../classes/stations_list.php",dirname(__FILE__)) );

// Actionlink needed to send a few of the forms on this page
$actionlink =  sprintf('?page=%s',$_REQUEST['page']);
?>

<div class="wrap">

    <h2>Add a new Station for the cities</h2>
    Note that - if you are adding a new station - registered stations
    can be used. Before you add a city please register your stations here.
    Info: you cann change the corresponding stations for a city until
    24 hours after adding the city to the database. But not afterwards!
    Reason: the station selection directly affects the computation of the
    points for all the bets!! If you chante the relationship this will
    affect the whole archive!!! Dont try this. Neither here nor in the
    database or somewhere!!!

    <br><br>
    <form action="<?php print $actionlink; ?>" method=\"post\">
        <input type="hidden" name="page" value="<?php print $_REQUEST['page']; ?>" />
        <fd>WMO number (for obs)::</fd>
        <input type='text' name='wmo'><br> 
        <fd>New Station Name:</fd>
        <input type='text' name='name'><br> 
        <fd>&nbsp;</fd>
        <input type='submit' name='submit' class='button' value='Add the station'>
    </form>

    <?php
    // Prepare and show stations table
    $wp_stations_table = new Wetterturnier_Stations_List_Table();
    $wp_stations_table->prepare_items();
    $wp_stations_table->display();
    ?>

</div>
