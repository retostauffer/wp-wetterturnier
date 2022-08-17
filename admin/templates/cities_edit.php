<?php
// ------------------------------------------------------------------
/// @page admin/templates/cities_edit.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief This page shows the form to change the city details.
///   Furthermore, it also controls the update/edit on the database.
// ------------------------------------------------------------------


// Change content of a group entry
global $wpdb;
global $WTadmin;

$cityObj = new wetterturnier_cityObject( $_GET['city'] );

$REQ_URL = explode("?",$_SERVER['REQUEST_URI'],2);
$CURRENT = sprintf("http://%s%s?page=%s",$_SERVER['HTTP_HOST'],$REQ_URL[0],$_REQUEST['page']);
?>


<div class="wrap">

    <h2>Edit City Entry</h2>

    <form method="post" action="<?php print $CURRENT; ?>">

        <input type='hidden' name='what' value='edit'>

        <fd><?php _e('City name','wpwt'); ?>:</fd>
        <input type='text' name='name' value='<?php print $cityObj->get('name'); ?>' /><br>

        <fd><?php _e('City hash','wpwt'); ?>:</fd>
        <input type='text' name='hash' value='<?php print $cityObj->get('hash'); ?>' size='3' maxlength='3' /><br>

        <fd><?php _e('Sort','wpwt'); ?>:</fd>
        <input type='text' name='sort' value='<?php print $cityObj->get('sort'); ?>' size='3' maxlength='3' /><br>

        <?php
        // Show station selection. There are two possible stations
        // per city. This script takes the two entries (if avalable)
        // from the database and shows a selection. Warning:
        // changing the station also changes the computation of the
        // scores. You should not do that, just in the beginning
        // when adding a new station!!!.
        // Closing this part when a city is older than 24h
        $since = strtotime( $cityObj->get('since') );
        $now   = gmdate('U');

            // Currently choosen stations
            $stations = $wpdb->get_results(sprintf("SELECT * FROM %swetterturnier_stations "
                          ." WHERE cityID = %d",$wpdb->prefix,$cityObj->get('ID')));
            if ( count($stations) >= 1 ) { $sel1 = $stations[0]->ID; } else { $sel1 = 0; }
            if ( count($stations) >= 2 ) { $sel2 = $stations[1]->ID; } else { $sel2 = 0; }

            echo "<fd>Stations matched to the city AND stations not attached to any city at the moment. Can be added, if wished.:</fd>\n";
            $WTadmin->show_station_select($cityObj->get('ID'));
            echo "<br>\n";

        //}
        ?>

        <fd><?php _e('Active flag','wpwt'); ?>:</fd>
        <input type='radio' name='active' value='0' <?php if ( $cityObj->get('active') == 0 ) { print 'checked'; } ?>> Inactive
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input type='radio' name='active' value='1' <?php if ( $cityObj->get('active') == 1 ) { print 'checked'; } ?>> Active<br>

       <fd><?php _e('Parameter for city','wpwt'); ?>:</fd>
       <div style='display: block;'>
       <?php
       // Create checkboxes
       $html = array();
       foreach ( $cityObj->getParams() as $paramObj ) {
          array_push( $html, sprintf("<input type=\"checkbox\" name=\"config_%d\"%s> %s",
             $paramObj->get("paramID"),
             ( in_array($paramObj->get("paramID"),$cityObj->get("paramconfig")) ) ? " checked" : "",
             $paramObj->get("paramName")) );
       } 
       print implode(", ",$html);
       ?>
       </div><br>


       <fd>&nbsp;</fd>
       <input type='hidden' name='city' value='<?php print $cityObj->get('ID'); ?>'>

       <?php @submit_button(); ?>
    </form>

</div>
