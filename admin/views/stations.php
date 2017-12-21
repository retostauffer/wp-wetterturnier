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

   <b>Inactive Parameters:</b> Inactive parameters will get inactive
   next midnight. Parameters can be activated and deactivated for specific
   time periods, the system keeps track of it.
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

   // Loading station object first
   $stnObj = new wetterturnier_stationObject( (int)$_REQUEST["station"] );

   // Extracting all "active or activated" paramID's to update
   // the database if required.
   $check = 'config_';
   $active_params = array();
   foreach ( $_REQUEST as $key=>$value ) {
      // Extract parameter elements
      if ( preg_match("/^config_([0-9]{1,})$/",$key,$paramID) ) {
         $paramID = (int)$paramID[1];
         array_push($active_params,$paramID);
         // Check if param is active, else we have to activate it
         foreach ( $stnObj->getParams() as $paramObj ) {
            if ( $paramObj->get("paramID") == $paramID &
                 ! $paramObj->isParameterActive( $stnObj->get("ID") ) ) {
               // Update database (insert new row)
               $tmp = array("stationID"=>$stnObj->get("ID"),
                                       "paramID"=>$paramObj->get("paramID"),
                                       "since"=>date("Y-m-d H:i:s"));
               $wpdb->insert(sprintf("%swetterturnier_stationparams",$wpdb->prefix),$tmp);
               if ( ! $wpdb->last_query ) { die("Problems updating the database!"); }
            }
         }
      // Other parameters which are allowed to change
      } else if ( in_array($key,array("name","wmo"),true) ) {
         $to_update[$key] = $value;
      }
   }

   // Now check if we have to disable parameters
   foreach ( $stnObj->getParams() as $paramObj ) {
      // If parameter is set as 'active' in the database but was not
      // checked anymore (not in $active_params) we have to deactivate this one.
      if ( $paramObj->isParameterActive( $stnObj->get("ID") ) &
           ! in_array((int)$paramObj->get("paramID"), $active_params) ) {
         // Update database
         $wpdb->update( sprintf("%swetterturnier_stationparams",$wpdb->prefix),
               array( "until" => date("Y-m-d H:i:s") ),
               array( "stationID" => $stnObj->get("ID"), "paramID" => $paramObj->get("paramID") ) );
         if ( ! $wpdb->last_query ) { die("Problems updating the database, sorry!"); }
      }
   }

   // Update database
   $update = $wpdb->update(sprintf('%swetterturnier_stations',$wpdb->prefix),
                           $to_update, array('ID'=>$_REQUEST['station']));


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
