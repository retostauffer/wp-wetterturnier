<h1>Wetterturnier API Manager</h1>

<help>
   Description missing at the moment.
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
if ( ! empty($_REQUEST['submit']) && ! empty($_REQUEST['APIKEY']) ) {
   $paramconfig = array();
   // Searching for:

   // Create data array
   $data = array();
   foreach ( array("APIKEY","APITYPE","APICONFIG","name","description") as $key ) {
      if ( in_array($key,array_keys($_REQUEST)) ) { $data[$key] = $_REQUEST[$key]; }
   }
   // Adding flag for public/nonpublic
   $data["ISPUBLIC"] = (in_array("ISPUBLIC",array_keys($_REQUEST)) ) ? true : false;
   // Adding until date if set
   if ( in_array("useuntil",array_keys($_REQUEST)) && 
        in_array("until",   array_keys($_REQUEST)) ) {
      $data["until"] = (int)strtotime($_REQUEST["until"]) / 86400;
   }

   //// Update database
   $update = $wpdb->insert(sprintf('%swetterturnier_api',$wpdb->prefix),$data);

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

}


// - If action is set
$showtable = true;
if ( ! empty($_GET['action']) ) {

    // Now depending on action we have to do some stuff
    if ( $_GET['action'] === "edit" && ! empty($_GET['ID']) ) {
         print "SHOW EDIT FORM HERE";
        $showtable = false;
    }

   // Delete entry
   if ( $_GET["action"] === "delete" && ! empty($_GET["ID"]) ) {
      $check = $wpdb->delete(sprintf("%swetterturnier_api",$wpdb->prefix),
         array("ID"=>(int)$_GET["ID"]));
      if ( $check ) { ?>
         <div class="wetterturnier-info ok">
         Entry properly deleted from database.
         </div>
      <?php } else { ?>
         <div class="wetterturnier-info error">
         Problem deleting the entry.
         </div>
      <?php }
   }
}



// Show table?
if ( $showtable ) {

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

        
    include_action_file('api_list.php');
}
?>
