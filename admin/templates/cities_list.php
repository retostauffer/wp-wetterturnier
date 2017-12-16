<?php

global $wpdb;
global $WTadmin;

// If not allready available, load wordpress class-wp-list table first
if(!class_exists('WP_List_Table')){
   require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
// Import personal list class
require_once( sprintf("%s/../classes/cities_list.php",dirname(__FILE__)) );
require_once( sprintf("%s/../classes/stations_list.php",dirname(__FILE__)) );

// actionlink needed to send a few of the forms on this page
$actionlink =  sprintf('?page=%s',$_REQUEST['page']);

?>

<div class="wrap">


    <h2>Cities list</h2>
    <h3>You can add new cities</h3>
    You do have the ability to add new cities for the Wetterturnier.
    Please note that some computation is done by some scripts not
    directly associated with the wordpress. Adding a city does not
    mean that there are observation data aferwords etc.!

    <?php //@settings_fields('wp_wetterturnier-group'); ?>
    <?php //@do_settings_fields('wp_wetterturnier-group'); ?>

    <h2>Add a new city</h2>
    <form action="<?php print $actionlink; ?>" method=\"post\">
        <input type="hidden" name="page" value="<?php print $_REQUEST['page']; ?>" />
        <fd>New City Name:</fd>
        <input type='text' name='name'><br> 
        <fd>New City hash:</fd>
        <input type='text' name='hash' size='3' maxlength='3'><br> 
        <fd>Include station:</fd>
        <?php $WTadmin->show_station_select(False); ?>
        <br>
        <fd>&nbsp;</fd>
        <input type='submit' name='submit' class='button' value='Add City'>
    </form>

    <h2>Overview</h2>

    <?php
    // Success message after changing something
    if ( ! empty($_GET['m']) && $_GET['m'] == 1 ) {
        echo "<div id='message' class='updated fade'><p><strong>"
            .__("Successfully changed the city entry","wpwt")
            ."</strong></p></div>";
    } 

    // Prepare table
    $wp_cities_table = new Wetterturnier_Cities_List_Table();
    $wp_cities_table->prepare_items();
    $wp_cities_table->display();

    ?>

</div>
