<?php
// ------------------------------------------------------------------
/// @file classes.php
/// @author Reto Stauffer
/// @date 19 June 2017
/// @brief This file contains several helper classes used in the
///    wetterturnier wordpress plugin.
// ------------------------------------------------------------------


// ------------------------------------------------------------------
/// @details A class to handle city information. Loads and stores
///    information from the *_wetterturnier_cities database table.
///    Whenever possible objects of this type will be called $cityObj
///    within the php code.
/// @see wetterturnier_stationObject
// ------------------------------------------------------------------
class wetterturnier_cityObject {

   /// Will contain a copy of the global $wpdb instance. Used as
   /// class-internal reference for database requests.
   private $wpdb;
   /// Attribute to store the information from the database.
   /// Will be NULL if no information loaded (init) and replaced
   /// by a php stdClass object with the key/value pairs from the
   /// database.
   private $data = NULL;
   /// Attribute to store station objects if required. See
   /// @ref _load_stations_ function.
   private $stations = NULL;

   function __construct( $init = NULL ) {

      global $wpdb; $this->wpdb = $wpdb;

      if ( is_null( $init ) ) {
         if ( empty( $_SESSION['wetterturnier_city'] ) ) {
            die("wp_wetterturnier_cityObject ERROR: SESSION PARAMETER "
               ."wetterturnier_city MISSING. THIS SEEMS TO BE A BUG. "
               ."PLEASE CALL THE ADMIN OR 911.");
         } else {
            $init = strtoupper($_SESSION['wetterturnier_city']);
         }
      }

      // Depending on input: load city information from database
      // and store the information on this object.
      if ( is_numeric($init) ) {
         $this->data = $this->_get_city_by_ID_( (int)$init );
      } else if ( is_string($init) ) {
         $this->data = $this->_get_city_by_string_( $init, "hash" );
         // If not found by hash: try by station name
         if ( ! $this->data ) {
            $this->data = $this->_get_city_by_string_( $init, "name" );
         }
      }

      // Decode parameter config (JSON->array) and load
      // stations attached to this city.
      if ( ! is_null( $this->data ) ) {
         $this->_decode_paramconfig_();
         $this->_load_stations_();
      }

   }

   // ---------------------------------------------------------------
   /// @details There is a json array stored in the database containing
   ///   the parameter configuration (basically an array of parameter ID's).
   ///   this function converts the string into an array and stores
   ///   the result on $this->data->paramconfig.
   function _decode_paramconfig_() {
      $this->data->paramconfig = json_decode( $this->get('paramconfig') );
   }

   // ---------------------------------------------------------------
   /// @details Loading city information from database given the
   ///   input $cityHash (e.g., BER). Not case sensitive.
   /// @param $cityHash String. City which should be returned from database.
   // ---------------------------------------------------------------
   function _get_city_by_string_( $needle, $col = "hash" ) {
      $sql = sprintf("SELECT * FROM %swetterturnier_cities WHERE UCASE(%s) = \"%s\"",
                     $this->wpdb->prefix,$col,strtoupper($needle));
      return $this->wpdb->get_row( $sql );
   }

   // ---------------------------------------------------------------
   /// @details Loading city information from database given the
   ///   input $cityID (numeric ID).
   /// @param $cityHash String. City which should be returned from database.
   // ---------------------------------------------------------------
   function _get_city_by_ID_( $cityID ) {
      $sql = sprintf("SELECT * FROM %swetterturnier_cities WHERE ID = %d",
                     $this->wpdb->prefix,$cityID);
      return $this->wpdb->get_row( $sql );
   }

   // ---------------------------------------------------------------
   /// @details This is the main method to extract information from
   ///   the class. Information should be stored on $this->data which
   ///   is a private stdClass object or NULL if there was a problem
   ///   initializing this object (default). The method checks 
   ///   whether a property on $this->data exists and returns the
   ///   content. If not found, boolean `false` will be returned.
   ///
   /// @param $key. String, name of the property you would like to get,
   ///   e.g., `ID` or `name`.
   /// @return Returns `false` or the value of the element with the
   ///   corresponding key.
   /// @see wetterturnier_cityObject
   // ---------------------------------------------------------------
   function get( $key ) {
      if ( is_null($this->data) ) { print("wetterturnier_cityObject data=NULL!"); }
      if ( property_exists($this->data,$key) ) {
         return $this->data->$key;
      } else { return(false); }
   }

   // ---------------------------------------------------------------
   /// @details Helper class for development. Shows loaded key/value.
   // ---------------------------------------------------------------
   function show() {
      if ( is_null($this->data) ) {
         printf("This cityObject does not contain valid information<br>\n");
      } else {
         printf("<br>Content of cityObject->data is<br>\n");
         foreach ( $this->data as $key=>$val ) {
            print "- ".$key." (".gettype($val)."):  ".$val."<br>\n";
         }
      }
   }

   // ---------------------------------------------------------------
   /// @details Each city can have one or more stations attached to it.
   ///   these stations are used to compute the points and stuff. This
   ///   method loads the stations and stores them into the array
   ///   $this->stations. Executed everytime a @ref wetterturnier_cityObject
   ///   object is initialized.
   // ---------------------------------------------------------------
   function _load_stations_() {

      $sql = sprintf("SELECT ID FROM %swetterturnier_stations WHERE cityID = %d;",
                     $this->wpdb->prefix,$this->get('ID'));
      $res = $this->wpdb->get_results($sql);

      // Loading station information
      $this->stations = array();
      foreach ( $res as $rec ) {
         array_push($this->stations,new wetterturnier_stationObject($rec->ID));
      }
   }

   // ---------------------------------------------------------------
   /// @details Returns the station information of this cityObject.
   ///   Information loaded by @ref _load_stations_ during the 
   ///   initialization of this object.
   /// @return An array of @ref wetterturnier_stationObject objects.
   // ---------------------------------------------------------------
   function stations() {
      return( $this->stations );
   }

   // ---------------------------------------------------------------
   /// @details Return parameter object array from first station, they are
   /// the same (except the 'active' flag which is killed in here)
   /// for all stations.
   /// @return Returns an array with paramObjects.
   // ---------------------------------------------------------------
   function getParams() {
      $params = $this->stations[0]->getParams();
      for ( $i=0; $i<count($params); $i++ ) {
         $params[$i]->setActive( NULL );
      }
      return( $params );
   }

}

// ------------------------------------------------------------------
/// @details A class to handle station information. Each city (see
///   @ref wetterturnier_cityObject) can have one or more stations
///   attached to it. Should be called $stnObj whenever possible in
///   the php code!
/// @param $init. Initial value, numeric, required. Depending on
///   input parameter $by this is either the station ID or station
///   wmo identification number.
/// @param $by. String, default is 'ID'. Allowed are 'ID' or 'wmo'.
///
/// @see wetterturnier_cityObject
// ------------------------------------------------------------------
class wetterturnier_stationObject {

   /// Attribute to store the station information. Similar to 
   /// the @ref wetterturnier_cityObject class. Initial value is NULL.
   private $data = NULL;
   private $params = NULL;

   function __construct( $init, $by='ID' ) {
      global $wpdb; $this->wpdb = $wpdb;

      if ( ! is_numeric($init) ) {
         die("Wrong input to wetterturnier_stationObject. Has to be numeric.");
      }
      // Selection can be performed on station ID or wmo number (numeric).
      // Default is by ID.
      if ( $by === 'wmo' ) {
         $this->data = $this->wpdb->get_row(sprintf("SELECT * FROM %swetterturnier_stations "
                                           ." WHERE wmo = %d;", $this->wpdb->prefix, $init));
      } else {
         $this->data = $this->wpdb->get_row(sprintf("SELECT * FROM %swetterturnier_stations "
                                           ." WHERE ID = %d;", $this->wpdb->prefix, $init));
      }

      // Getting parameter config
      $params = $this->wpdb->get_results(sprintf("SELECT paramID "
         ." FROM %swetterturnier_param ORDER BY sort ASC", $wpdb->prefix));

      $this->params = array();
      foreach ( $params as $rec ) {
         array_push( $this->params, new wetterturnier_paramObject( $rec->paramID ));
      }
   }

   // ---------------------------------------------------------------
   /// @details Helper function, returns a string with all active
   ///   parameters. 
   /// @param $active. Boolean, default true for showActiveParams.
   ///   showInactiveParams (which is only a wrapper) uses $active=false
   ///   to return inactive parameters, @see showInactiveParams.
   /// @return Character string with active parameters.
   // ---------------------------------------------------------------
   function showActiveParams( $active = true ) {
      $param = array();
      foreach ( $this->getParams() as $paramObj ) {
         if ( $paramObj->isParameterActive( $this->data->ID ) === $active ) {
            array_push( $param, $paramObj->get("paramName") );
         }
      }
      return ( count($param) == 0 ) ? NULL : join(",",$param);
   }
   // ---------------------------------------------------------------
   /// @details Helper function, returns a string with all inactive
   ///   parameters. See also @see showActiveParams.
   /// @return Character string with inactive parameters.
   // ---------------------------------------------------------------
   function showInactiveParams() { return $this->showActiveParams( false ); }
      

   // ---------------------------------------------------------------
   /// @details Returns the parameter array containing paramObjects.
   /// @return Array of @see wetterturnier_paramObjects.
   // ---------------------------------------------------------------
   function getParams() { return( $this->params ); }

   // ---------------------------------------------------------------
   /// @details Helper function for the admin interface. Showas checkboxes
   ///   for the parameters.
   /// @return Html code for the checkboxes. Each parameter gets a box which
   ///   is either checked if active or not checked if inactive.
   // ---------------------------------------------------------------
   function showParamCheckboxes() {
      $html = array();
      foreach ( $this->getParams() as $paramObj ) {
         array_push( $html, sprintf("<input type=\"checkbox\" name=\"config_%d\"%s> %s",
            $paramObj->get("paramID"),
            ( $paramObj->isParameterActive( $this->data->ID ) ) ? " checked" : "",
            $paramObj->get("paramName")) );
      }
      return( join(", ",$html) );
   }

   // ---------------------------------------------------------------
   /// @details This is the main method to extract information from
   ///   the class. Information should be stored on $this->data which
   ///   is a private stdClass object or NULL if there was a problem
   ///   initializing this object (default). The method checks 
   ///   whether a property on $this->data exists and returns the
   ///   content. If not found, boolean `false` will be returned.
   ///
   /// @param $key. String, name of the property you would like to get,
   ///   e.g., `ID` or `name`.
   /// @return Returns `false` or the value of the element with the
   ///   corresponding key.
   /// @see wetterturnier_stationObject
   // ---------------------------------------------------------------
   function get( $key ) {
      if ( is_null($this->data) ) { print("wetterturnier_cityObject data=NULL!"); }
      if ( property_exists($this->data,$key) ) {
         return $this->data->$key;
      } else { return(false); }
   }

   // ---------------------------------------------------------------
   /// @details Helper class for development. Shows loaded key/value.
   // ---------------------------------------------------------------
   function show() {
      if ( is_null($this->data) ) {
         printf("This stationObject does not contain valid information<br>\n");
      } else {
         printf("<br>Content of cityObject->data is<br>\n");
         foreach ( $this->data as $key=>$val ) {
            print "- ".$key." (".gettype($val)."):  ".$val."<br>\n";
         }
         // Active parameters
         if ( is_null($this->showActiveParams()) ) {
            printf("- Active parameters: NULL<br>\n");
         } else {
            printf("- Active parameters %s<br>\n",$this->showActiveParams());
            printf("- Inactive parameters %s<br>\n",$this->showInactiveParams());
         }
      }
   }

}

// ------------------------------------------------------------------
/// @details This is a small class for group handling.
///   During initialization all groups will be loaded. The class contains
///   some methods to load users (active/inactive) among the groups
///   which is used to display e.g., the group tables.
// ------------------------------------------------------------------
class wetterturnier_groupsObject {

   private $wpdb;

   private $groups;
   private $groupIDs = array();

   // ---------------------------------------------------------------
   /// @details On initialization: load all  groups from the group table.
   // ---------------------------------------------------------------
   function __construct() {
      global $wpdb; $this->wpdb = $wpdb;

      // Loading all groups
      $sql = sprintf("SELECT * FROM %swetterturnier_groups ORDER BY groupName ASC",
                     $this->wpdb->prefix);
      $this->groups = $this->wpdb->get_results($sql);
      foreach ( $this->groups as $rec ) {
         array_push($this->groupIDs,(int)$rec->groupID);
      }
   }

   function iteritems() {
      return($this->groups);
   }

   // ---------------------------------------------------------------
   /// @details For a given groupID the members will be returned.
   /// @param $groupID. Integer, ID of the group.
   /// @return Returns boolean False if the group cannot be found.
   ///   Else a list of stdClass objects will be returned containing
   ///   the required information about the user/users in the group.
   // ---------------------------------------------------------------
   function get_members( $groupID ) {

      // Check which group matches
      $idx = array_search((int)$groupID,$this->groupIDs,true);
      if ( ! $idx ) { return(False); }

      // Searching for members in this specific group
      $sql = array();
      array_push($sql,"SELECT gu.userID, gu.since, gu.until, gu.active, usr.user_login");
      array_push($sql,sprintf("FROM %swetterturnier_groupusers AS gu",$this->wpdb->prefix));
      array_push($sql,sprintf("LEFT JOIN %s AS usr",$this->wpdb->users));
      array_push($sql,"ON gu.userID = usr.ID");
      array_push($sql,sprintf("WHERE groupID = %d",$groupID));

      //print join(" ",$sql); 
      return($this->wpdb->get_results( join("\n",$sql) ));

   }

   // ---------------------------------------------------------------
   /// @details Shows the frontend tables. No inputs, uses the object
   ///   information loaded in the __construct method.
   ///   Information on active/inactive status:
   ///   active = 0: is inactive; active = 1: is active;
   ///   active = 8: is active, but the user has an open 'remove me from group' request;
   ///   active = 9: not yet in the group but a 'add me to the group' request open.
   // ---------------------------------------------------------------
   function show_frontend_tables() {

      global $WTuser;

      function is_inactive( $active ) { return( ! in_array((int)$active,array(1,8))); }

      foreach ( $this->iteritems() as $grp ) {
         $members = $this->get_members( $grp->groupID );
         // If there are no members in this group: skip
         if ( ! $members ) { continue; }

         // Count inactive users 
         $num_inactive = 0;
         foreach ( $members as $mem ) {
            if ( is_inactive($mem->active) ) { $num_inactive++; }
         }


         // Else create new table 
         ?>
         <h2><?php print $grp->groupName; ?></h2>
         <desc><?php printf("%s: %s<br>\n",__('Description','wpwt'),$grp->groupDesc); ?></desc>
         <?php
         // If the group itself is inactive: show message
         if ( $grp->active == 0 ) {
            printf("<desc class=\"orange\"><b>%s:</b>&nbsp;%s</desc><br>\n",
                   __("Inactive","wpwt"),__("This group is inactive at the moment and will not be considered in the tournament.","wpwt"));
         }
         // Show button to show/hide inactive users.
         if ( $num_inactive ) { ?>
            <input type="button" class="groups-show-inactive" groupID="<?php print $grp->groupID; ?>"
               value="<?php _e("Show inactive","wpwt"); ?>" />
         <?php } ?>
         <table class="tablesorter wttable-groups" id="wttable-group-<?php print $grp->groupID; ?>" role="grid">
            <thead>
               <tr>
                  <th><?php _e("User name","wpwt"); ?></th>
                  <th><?php _e("since","wpwt"); ?></th>
                  <th><?php _e("until","wpwt"); ?></th>
                  <th><?php _e("Show user profile","wpwt"); ?></th>
               </tr>
            </thead>
            <tbody>
            <?php foreach ( $members as $mem ) {
               $class = sprintf("class=\"%s\"",(is_inactive($mem->active) ? "inactive" : "active"));
               ?>
               <tr>
                  <td <?php print $class; ?>>
                     <?php print (is_inactive($mem->active) ? "***" : ""); ?>
                     <?php print $mem->user_login; ?>
                  </td>
                  <td <?php print $class; ?>>
                     <?php print $WTuser->date_format( strtotime($mem->since)/86400 ); ?>
                  </td>
                  <td <?php print $class; ?>>
                     <?php print (is_null($mem->until) ? "active" : $WTuser->date_format( strtotime($mem->until)/86400 )); ?>
                  </td>
                  <td <?php print $class; ?>><?php
                     $profile = bbp_get_user_profile_url($mem->userID);
                     if ( ! $profile ) { _e("Not available","wpwt"); } else {
                        printf("<a href=\"%s\" target=\"_self\">%s</a>",$profile,__("Show profile","wpwt")); 
                     } ?></td>
               </tr>
            <?php } ?>
            </tbody>
         </table>
         <?php

      }
   } # end of method

}

// ------------------------------------------------------------------
/// @details A class to handle parameter information.
/// @param $init. Initial value, numeric, required. Depending on
///   input parameter $by this is either the parameter ID or parameter
///   shortname.
/// @param $by. String, default is 'ID'. Allowed are 'ID' or 'paramName'.
/// @param $tdate. Tournament date (days since 1970-01-01), default is NULL.
///   if NULL the current date/time is used. If given the system is checking
///   whether the parameter was active for $tdate or not. This changes
///   the outcome of the isParameterActive method of this class.
///
/// @see wetterturnier_stationObject
// ------------------------------------------------------------------
class wetterturnier_paramObject {

   /// Attribute to store the station information. Similar to 
   /// the @ref wetterturnier_cityObject class. Initial value is NULL.
   private $data = NULL;

   // ---------------------------------------------------------------
   /// @details On initialization: load all  groups from the group table.
   // ---------------------------------------------------------------
   function __construct( $init, $by = "ID", $tdate = NULL ) {
      global $wpdb; $this->wpdb = $wpdb;

      if ( ! in_array( $by, array("ID","paramName") ) ) {
         die("Wrong input to wetterturnier_paramObject. \$by has to be \"ID\" or \"pramName\".");
      }
      if ( $by == "ID" & ! is_numeric($init) ) {
         die("Wrong input to wetterturnier_paramObject. \$init has to be numeric if used with \$by=\"ID\".");
      }

      // Loading all parameters
      $sql = sprintf("SELECT * FROM %swetterturnier_param WHERE %s", $this->wpdb->prefix,
             ( $by == "ID" ) ? sprintf("paramID = %d",$init) : sprintf("paramName = '%s'",$init) );
             
      $this->data = $wpdb->get_row($sql);

      // Check for which stations the parameter is active for the current
      // time stamp anf for which it isnt.
      $until = ( is_null($tdate) ) ? date("Y-m-d H:i:s") :
               strftime("%Y-%m-%d %H:%M:%S",(int)($tdate+1)*86400);
      $since = ( is_null($tdate) ) ? date("Y-m-d H:i:s") :
               strftime("%Y-%m-%d %H:%M:%S",(int)($tdate)*86400);
      $sql = sprintf("SELECT stationID, CASE WHEN "
             ." ( since <= '%s' AND (until = 0 OR until >= '%s') ) THEN 1 ELSE 0 END AS active"
             ." FROM %swetterturnier_stationparams WHERE paramID=%s",
             $since, $until, $wpdb->prefix, $this->data->paramID);
      $this->is_active = $wpdb->get_results($sql);
   }

   // ---------------------------------------------------------------
   /// @details Checks if the parameter is active for a specific station.
   /// @param $stationID. Numeric, station identifier ID.
   /// @return Returns boolean True if parameter is active for station
   ///   $stationID and false else.
   // ---------------------------------------------------------------
   function isParameterActive( $stationID ) {
      foreach ( $this->is_active as $rec ) {
         if ( $rec->stationID == $stationID ) { return (bool)$rec->active; }
      }
      return false;
   }

   // ---------------------------------------------------------------
   /// @details Allows to overrule the 'active' flag in the object.
   // ---------------------------------------------------------------
   function setActive( $to ) {
      $this->data->active = $to;
   }

   // ---------------------------------------------------------------
   /// @details This is the main method to extract information from
   ///   the class. Information should be stored on $this->data which
   ///   is a private stdClass object or NULL if there was a problem
   ///   initializing this object (default). The method checks 
   ///   whether a property on $this->data exists and returns the
   ///   content. If not found, boolean `false` will be returned.
   ///
   /// @param $key. String, name of the property you would like to get,
   ///   e.g., `ID` or `name`.
   /// @return Returns `false` or the value of the element with the
   ///   corresponding key.
   /// @see wetterturnier_cityObject
   // ---------------------------------------------------------------
   function get( $key ) {
      if ( is_null($this->data) ) { print("wetterturnier_paramObject data=NULL!"); }
      if ( property_exists($this->data,$key) ) {
         return $this->data->$key;
      } else { return(false); }
   }

   // ---------------------------------------------------------------
   /// @details Helper class for development. Shows loaded key/value.
   // ---------------------------------------------------------------
   function show() {
      if ( is_null($this->data) ) {
         printf("This paramObject does not contain valid information<br>\n");
      } else {
         printf("<br>Content of paramObject->data is<br>\n");
         foreach ( $this->data as $key=>$val ) {
            print "- ".$key." (".gettype($val)."):  ".$val."<br>\n";
         }
      }
   }
}


// ------------------------------------------------------------------
/// @details A class to handle webcam information. Loads and stores
///    information from the *_wetterturnier_webcams database table.
///    Whenever possible objects of this type will be called $webcamObj
///    within the php code.
/// @see wetterturnier_webcamObject
// ------------------------------------------------------------------
class wetterturnier_webcamObject {

   /// Attribute to store the webcam information. Similar to 
   /// the @ref wetterturnier_webcamObject class. Initial value is NULL.
   private $data = NULL;

   function __construct( $ID ) {
      global $wpdb;
      $this->data = $wpdb->get_row(sprintf("SELECT * FROM %swetterturnier_webcams WHERE ID=%d;",
               $wpdb->prefix, $ID));
   }

   // ---------------------------------------------------------------
   /// @details This is the main method to extract information from
   ///   the class. Information should be stored on $this->data which
   ///   is a private stdClass object or NULL if there was a problem
   ///   initializing this object (default). The method checks 
   ///   whether a property on $this->data exists and returns the
   ///   content. If not found, boolean `false` will be returned.
   ///
   /// @param $key. String, name of the property you would like to get,
   ///   e.g., `ID` or `uri`.
   /// @return Returns `false` or the value of the element with the
   ///   corresponding key.
   // ---------------------------------------------------------------
   function get( $key ) {
      if ( is_null($this->data) ) { print("wetterturnier_webcamObject data=NULL!"); }
      if ( property_exists($this->data,$key) ) {
         return $this->data->$key;
      } else { return(false); }
   }

   // ---------------------------------------------------------------
   /// @details Prints html to display the webcam image.
   // ---------------------------------------------------------------
   function display_webcam() {
      print "<div class='wtwebcam'>\n"
           .sprintf("<a href=\"%s\" target=\"_new\">",$this->get("source"))
           .sprintf("   <img src=\"%s\" alt=\"%s\" /><br>\n",$this->get("uri"),$this->get("source"))
           ."   <span class=\"wtwebcam-source\">"
           .sprintf("%s</span></a>",$this->get("desc"))
           ."</span></a>\n"
           ."</div>\n";
   }

   // ---------------------------------------------------------------
   /// @details Helper class for development purposes.
   // ---------------------------------------------------------------
   function show() {
      if ( is_null($this->data) ) {
         printf("This webcamObject does not contain valid information<br>\n");
      } else {
         printf("<br>Content of webcamObject->data is<br>\n");
         foreach ( $this->data as $key=>$val ) {
            print "- ".$key." (".gettype($val)."):  ".$val."<br>\n";
         }
      }
   }
}

?>
