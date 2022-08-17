<?php
// ------------------------------------------------------------------
/// @page admin/templates/param_list.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief How the parameter admin page will be displayed.
// ------------------------------------------------------------------


// If not allready available, load wordpress class-wp-list table first
if(!class_exists('WP_List_Table')){
   require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
// Import personal list class
require_once( sprintf("%s/../classes/param_list.php",dirname(__FILE__)) );

// actionlink needed to send a few of the forms on this page
$actionlink =  sprintf('?page=%s',$_REQUEST['page']);

?>

<div class="wrap">


    <h2>Parameter list</h2>
    <h3>You can add new parameters</h3>
    You can add new parameters here.
    Note that they are not automatically active for all existing
    cities. Go to wetterturnier-cities-edit, there you can see a list of
    checkboxes. If you would like to add a new parmaeter you have to
    activate them there. However, please also think about what you are
    doing. Adding a new parameter does NOT mean that the backend knows
    what to do (prepare Observations, Compute Points, compute Petrus and Moses).
    This is just an easy-to-use interface in case the wetterturnier will be
    extended!

    <?php //@settings_fields('wp_wetterturnier-group'); ?>
    <?php //@do_settings_fields('wp_wetterturnier-group'); ?>

    <h2>Add a new parameter</h2>
    <form action="<?php print $actionlink; ?>" method=\"post\">
        <input type="hidden" name="page" value="<?php print $_REQUEST['page']; ?>" />
        <fd>New Param Name:</fd>
        <input type='text' name='paramName' /><br> 
        <fd>English description:</fd>
        <input type='text' name='EN' /><br> 
        <fd>German description:</fd>
        <input type='text' name='DE' /><br> 
        <fd>English help:</fd>
        <textarea name='helpEN'></textarea><br> 
        <fd>German help:</fd>
        <textarea name='helpDE'></textarea><br> 
        <fd>Minimum value:</fd>
        <input type='text' name='valmin' value='-9999' /><br> 
        <fd>Maximum value:</fd>
        <input type='text' name='valmax' value='9999' /><br> 
        <fd>Decimals:</fd>
        <input type='text' name='decimals' value='1' maxlength="3"/><br> 
        <fd>Unit:</fd>
        <input type='text' name='valmin' maxlength="10" value='' /><br> 
        <fd>&nbsp;</fd>
        <input type='submit' name='submit' class='button' value='Add Parameter'>
    </form>

   <!--
    <h2>Validator java script file</h2>

    The wetterturnier plugin is using jquery.validate to validate the
    values users are entering on the "place bet" page. Therefore a little
    bit of jquery code is necessary which depends on the parameters defined 
    here. The wetterturnier plugin will automatically generate this file
    every time you are changing something on the parameters. However, 
    you can also re-generate the file here. Please note that the webserver
    needs write-permissions on the directory called "dynamic" within the
    wetterturnier plugin on your server. If not, please set correct permissions!<br>

    <form method="post" action="?wp_wetterturnier_admin_param_regenerate_js">
        <input type="submit" name="regenerate" value="Re-generate JS file"></input>
    </form>
      -->

    <h2>Overview</h2>

    <div id="message">
    <b>Column description:</b>
    Format (number/digits), Maxlen, and Range are for the jQuery
    input checker which is used on the "bet form" which is checking
    the user inputs and e.g., rejects a "N=34.3" or similar inputs.<br>
    Decimals, and Unit are used to display the data for the end user.
    If digits are set wrong, the frontend just rounds the data which
    does not directly include that they are wrong in the database! Be
    aware what you are doing here. If unit is empty: no unit will be shown.
    Else the unit will be added in the tables.
    </div>

    <?php
    // Success message after changing something
    if ( ! empty($_GET['m']) && $_GET['m'] == 1 ) {
        echo "<div id='message' class='updated fade'><p><strong>"
            .__("Successfully changed parameter entry","wpwt")
            ."</strong></p></div>";
    } 

    // Prepare table
    $wp_list_table = new Wetterturnier_Param_List_Table();
    $wp_list_table->prepare_items();
    $wp_list_table->display();
    ?>
</div>
