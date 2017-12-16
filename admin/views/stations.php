<h1>Wetterturnier Station Management</h1>

<help>
   Each city needs at least one <b>station</b>. Observations are
   bounded to stations, rather than cities. If more than only one
   station is set, the judging (points the user get) is typically
   based on all of these - and if a user tip lies between the range
   of the observed values, maximum points will be assigned for this
   value. That was at least how the original Berliner wetterturnier
   was designed.

   Each station consists of a <b>name</b>, a <b>wmo station number</b>,
   and a list of <b>parameters not observed</b>. The name can be anything,
   however, the station number is crucial. Observations are directly mapped
   to this station number. Parameters which are not observed at all (e.g.,
   total cloud cover, as there is no observer and no instrument) can be labeled
   here as well.
</help>

<?php
// The citiesview handles different
// things all for the group manipulating.
// Actions are transported by the _GET "action" argument.
// If set, try to call the correct page.
// If no action is set (default), view grop list.

global $wpdb;
global $WTadmin;

function include_action_file( $filename ) {
    include(sprintf("%s/../templates/%s", dirname(__FILE__),$filename));
}

// ------------------------------------------------------------------
// - If submit button was pressed, then update the station first.
// ------------------------------------------------------------------
if ( ! empty($_REQUEST['submit']) ) {
   $nullconfig = array();
   // Searching for:
   $check = 'config_';

   // Array with key/value which should be updated in the
   // database. The 'key' here is the database column name.
   $to_update = array('nullconfig'=>NULL);

   // Extract the ID of all elements named config_[0-9}{1,}
   // to append them to the nullconfig array (evaluating the
   // checkboxes from the edit submission form).
   // Furthermore: keep some station settings. These are the ones
   // the user is allowed to change.
   foreach ( $_REQUEST as $key=>$value ) {
      // Extract nullconfig elements
      if ( preg_match("/^config_([0-9]{1,})$/",$key,$paramID) ) {
         array_push($nullconfig,(int)$paramID[1]);
         continue;
      }
      // Allowed to change this?
      if ( in_array($key,array("name","wmo"),true) ) {
         $to_update[$key] = $value;
      }
   }

   // If nullconfig is necessary return string,
   // else boolean false.
   if ( count($nullconfig) > 0 ) { $to_update['nullconfig'] = json_encode($nullconfig); }

   // Update database
   $update = $wpdb->update(sprintf('%swetterturnier_stations',$wpdb->prefix),
                           ///array('nullconfig'=>$nullconfig),
                           $to_update,
                           array('ID'=>$_REQUEST['station']));

   // Message for end user
   ////if ( ! $update ) { PHP END TAG MISSING HERE
   ////   <div class='wetterturnier-info error'>
   ////   Problems while updating the database! Why? :)
   ////   </div>
   ////<?php } else { PHP END TAG MISSING HERE
   ////   <div class='wetterturnier-info ok'>
   ////   Station successfully update in the database.
   ////   </div>
   ////<?php }

}


// ------------------------------------------------------------------
// - If action is set
//   we have to show the city edit form, not the city list.
// ------------------------------------------------------------------
if ( ! empty($_REQUEST['action']) ) {

    // Now depending on action we have to do some stuff
    if ( $_GET['action'] == "edit" && ! empty($_GET['station']) ) {
        include_action_file('stations_edit.php');
    }
// ------------------------------------------------------------------
// - Show city_list else 
// ------------------------------------------------------------------
} else {

    if ( ! empty($_REQUEST['wmo']) ) {

        // First checking if the entered wmo station is an integer number
        if ( $_REQUEST['wmo'] != (string)(int)$_REQUEST['wmo'] ) {
                echo "<div id='message' class='error fade'><p><strong>"
                    .__("Entered WMO number was no integer. Stop.","wpwt")
                    ."</strong></p></div>";
        } else {
            // checking if the wmo station is allready in use
            $res = $wpdb->get_row(sprintf("SELECT ID FROM %swetterturnier_stations "
                        ." WHERE wmo = %s",$wpdb->prefix,$_REQUEST['wmo']));
            if ( count($res) > 0 ) {
                echo "<div id='message' class='error fade'><p><strong>"
                    .__("WMO number allready defined. Stop.","wpwt")
                    ."</strong></p></div>";
            } else {
                $wpdb->query(sprintf("INSERT INTO %swetterturnier_stations "
                        ."(`wmo`,`cityID`,`name`) VALUES ('%s', 0, '%s')",
                        $wpdb->prefix,$_REQUEST['wmo'],$_REQUEST['name']));
            }
        }
    }

        
    include_action_file('stations_list.php');
}
?>
