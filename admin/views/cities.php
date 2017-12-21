<h1>Wetterturnier Cities Management</h1>

<help>
   The wetterturnier plugin allows you to remove, and add cities.
   If you do so, please ensure that you also add some stations to
   the city as a city without stations wont get any observations.
   Note: each station can only be mapped to one specific city.
   Stations can have specific parameter specifications - this allows
   to specify cities with more, or less parameters (e.g., if you have
   one city where a certain parameter wont't be observed at all).

   When you delete a city, the city wont be removed from the system.
   The city will only be hidden for the user (set to inactive). The
   data corresponding to the city won't be lost therefore, and you are
   able to "switch a city off" for a certain time - if needed.

   <b>Disabled parameters:</b> If parameters are disabled they will not
   be included in the tournament (the bet-form won't show them as soon
   as they are disabled).
</help>

<?php
// The citiesview handles different
// things all for the group manipulating.
// Actions are transported by the _GET "action" argument.
// If set, try to call the correct page.
// If no action is set (default), view grop list.
global $wpdb, $WTadmin;
function include_action_file( $filename ) {
    include(sprintf("%s/../templates/%s", dirname(__FILE__),$filename));
}


// ------------------------------------------------------------------
// - If submit button was pressed, then update the city first.
// ------------------------------------------------------------------
if ( ! empty($_REQUEST['submit']) && ! empty($_REQUEST['city']) ) {
   $paramconfig = array();
   // Searching for:
   $check = 'config_';
   // Checking all request keys
   foreach ( array_keys($_REQUEST) as $k ) {
      if ( strcmp($check,substr($k,0,strlen($check))) === 0 ) {
         array_push($paramconfig,(int)str_replace($check,"",$k));
      }
   }

   // Sets $paramconfig to something. Sorry, forgot what, but looks like
   // the old 'nullconfig' array. 
   if ( count($paramconfig) == 0 ) { $paramconfig = NULL; }
   else                            { $paramconfig = json_encode($paramconfig); }

   // Update database
   $update = $wpdb->update(sprintf('%swetterturnier_cities',$wpdb->prefix),
                           array('paramconfig'=>$paramconfig),
                           array('ID'=>$_REQUEST['city']));
   // Message for end user
   if ( ! $update ) { ?>
      <div class='wetterturnier-info error'>
      Problems while updating the database or you have not changed anything. 
      </div>
   <?php } else { ?>
      <div class='wetterturnier-info ok'>
      Station successfully update in the database.
      </div>
   <?php }

   // Checking the matched stations
   foreach ( $_REQUEST as $key=>$value ) {
      if ( ! preg_match("/^wmo_station-[0-9]{1,}/",$key,$match) ) { continue; }
      $stationID = (int)str_replace("wmo_station-","",$match[0]);
      // Add the station (if not already added)
      if ( (int)$value == 1 ) {
         $wpdb->update(sprintf('%swetterturnier_stations',$wpdb->prefix),
                       array('cityID'=>$_REQUEST['city']),
                       array('ID'=>$stationID));
      // Remove station from city
      } else if ( (int)$value == 0 ) {
         $wpdb->update(sprintf('%swetterturnier_stations',$wpdb->prefix),
                       array('cityID'=>NULL),
                       array('ID'=>$stationID));
      }
   }

}


// - If action is set
if ( ! empty($_GET['action']) ) {

    // Now depending on action we have to do some stuff
    if ( $_GET['action'] == "edit" && ! empty($_GET['city']) ) {
        include_action_file('cities_edit.php');
    }

// - Show city_list else 
} else {

    // Update a group
    if ( ! empty($_POST['what']) && $_POST['what'] == 'edit' ) {

        // Loading old entry first
        $old = $wpdb->get_row(sprintf('SELECT * FROM %swetterturnier_cities WHERE ID = %s',
                              $wpdb->prefix,$_POST['city']));
        // Creat update array
        $update = array('name'=>esc_html(stripslashes($_POST['name'])),
                        'hash'=>strtoupper(esc_html(stripslashes($_POST['hash']))),
                        'sort'=>(int)$_POST['sort']);
        if ( (int)$_POST['active'] == 0 & (int)$_POST['active'] != (int)$old->active ) {
            $update['active'] = $_POST['active'];
            $update['until']  = strftime('%Y-%m-%d %H:%M:%S',time()); 
        }
        // If hash changes but is allready in use: stop
        $doupdate = true;
        if ( ! strcmp($update['hash'],$old->hash) == 0 ) {
            if ( count($wpdb->get_results(sprintf("SELECT ID FROM %swetterturnier_cities "
                    ."WHERE hash = '%s'",$wpdb->prefix,$update['hash'])) ) > 0 ) {
                echo "<div id='message' class='error fade'><p><strong>"
                    .__("City hash is allready in use. Drop changes.","wpwt")
                    ."</strong></p></div>";
                $doupdate = false;
            }
            
        }
        // Do update?
        if ( $doupdate ) {

            $wpdb->update($wpdb->prefix.'wetterturnier_cities',$update,
                                         array('ID'=>$_POST['city']));

            // --------------------------------------------------
            // Updating corresponding stations (if set) 
            if ( !empty($_REQUEST['wmo_station_2']) & !empty($_REQUEST['wmo_station_1']) ) {
                // First reset both
                $wpdb->update($wpdb->prefix."wetterturnier_stations",
                    array("cityID"=>0),array("cityID"=>$_POST['city']));
                $wpdb->update($wpdb->prefix."wetterturnier_stations",
                    array("cityID"=>$_POST['city']),
                    array("ID"=>$_REQUEST['wmo_station_1']));
                $wpdb->update($wpdb->prefix."wetterturnier_stations",
                    array("cityID"=>$_POST['city']),
                    array("ID"=>$_REQUEST['wmo_station_2']));
            }
            // --------------------------------------------------

        }

    // Add a new city 
    } else if ( ! empty($_REQUEST['name']) & ! empty($_REQUEST['hash']) ) {

        // userID and groupID
        $name = stripslashes(esc_html((string)$_REQUEST['name'] ));
        $hash = strtoupper(stripslashes(esc_html((string)$_REQUEST['hash'])));
        // Get sorting
        $sort_sql = sprintf('SELECT max(sort+1) AS sort FROM %swetterturnier_cities',$wpdb->prefix);
        $sort = $wpdb->get_row($sort_sql)->sort;

        // Check if group allready exists
        if ( count($wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'wetterturnier_cities '
                                     .' WHERE name = "'.$name.'" OR hash = "'.$hash.'"')) > 0 ) {

            echo "<div id='message' class='error fade'><p><strong>"
                .__("City with this name or hash is allready existing! Cannot add again!","wpwt")
                ."</strong></p></div>";
        } else {
            // If one of the ID's is negative (-9) nothing was choosen.
            if ( strlen($name) <= 0 | strlen($hash) <= 0)  {
                $_REQUEST['added'] = false;
                echo "<div id='message' class='error fade'><p><strong>"
                    .__("City not added. City name and hash have to be set!","wpwt")
                    ."</strong></p></div>";
            }  else {
                $check = $wpdb->insert($wpdb->prefix.'wetterturnier_cities',
                                       array('name'=>$name,'hash'=>$hash,'sort'=>$sort),
                                       array('%s','%s','%d'));
                if ( ! $check ) { die('BUG. PROBLEM INSERTING STATION. WHY?'); }

                echo "<div id='message' class='updated fade'><p><strong>"
                    .__("New City set. Shown on front page from now on.","wpwt")
                    ."</strong></p></div>";

                // --------------------------------------------------
                // Checking if the user selected corresponding stations. If so,
                // we have to update the stations database, too. Therefore
                // searching for $_REQUEST keys like wmo_station_XXX where
                // XXX is the station ID in the database.
                $cityObj = new wetterturnier_cityObject( $_REQUEST['hash'] );
                foreach ( $_REQUEST as $key => $val ) {
                    if ( preg_match("/^(wmo_station-)[0-9]{1,7}/",$key) ) {
                        // If flag is 0: unused: skip
                        if ( $val == 0 ) { continue; }
                        $stnID = explode("-",$key); $stnID = (int)$stnID[1];
                        $wpdb->update($wpdb->prefix."wetterturnier_stations",
                            array("cityID"=>$cityObj->get('ID')), array("ID"=>$stnID));
                    }
                }
                // --------------------------------------------------

            }
        }
    // Add a station
    } else if ( ! empty($_REQUEST['wmo']) ) {

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

        
    include_action_file('cities_list.php');
}
?>
