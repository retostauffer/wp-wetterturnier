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

      // Decode nullconfig (JSON->array)
      if ( ! is_null( $this->data ) ) {
         $this->_decode_nullconfig_();
      }
   }

   // ---------------------------------------------------------------
   /// @details There is a json array stored in the database containing
   ///   the 'nullconfig' (parameters not observed at this station). It
   ///   is an array of parameter ID's.
   ///   This function converts the string into an array and stores
   ///   the result on $this->data->nullconfig.
   // ---------------------------------------------------------------
   function _decode_nullconfig_() {
      $this->data->nullconfig = json_decode( $this->get('nullconfig') );
   }

   // ---------------------------------------------------------------
   /// @details Returns the nullconfig
   // ---------------------------------------------------------------
   function nullconfig() {
      global $WTuser;

      $nullconfig = $this->data->nullconfig;
      if ( count($nullconfig) === 0 ) { return(Null); }
      // Else check which parameters are set to null
      $isnull = array();
      foreach ( $nullconfig as $paramID ) {
         array_push($isnull,$WTuser->get_param_by_ID($paramID)->paramName);
      }
      return( $isnull );
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
         <desc><?php printf(__('Description','wpwt'),$grp->groupDesc); ?></desc>
         <?php
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
                     <?php print (is_null($mem->until) ? "active" : $WTuser->datetime_format($mem->until)); ?>
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


?>
