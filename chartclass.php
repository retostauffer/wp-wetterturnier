<?php
// ------------------------------------------------------------------
/// @file chartclass.php
/// @author Reto Stauffer
/// @date 26 June 2017
/// @brief This file contains several helper classes used in the
///    wetterturnier wordpress plugin.
// ------------------------------------------------------------------


class wetterturnier_chartHandler {

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

   /// Attribute to store the integer 'tdate' for which we are allowed
   /// to show the data in the stats. This is used to avoid that values
   /// from the ongoing tournament are shown!
   private $tdatemax = NULL;
   private $ndays = NULL;
 
   /// Note: _construct should NOT produce any output (ajax crashes)
   /// @param $init. String, just used as an information from where the
   ///   function has been called. 
   /// @param $ndays. Integer, number of bet days. Is used to restrict
   ///   the data which will be returned such that the users cannot see
   ///   the bets from an ongoing tournament.
   function __construct( $init = NULL, $ndays = 3 ) {
      // Globalize database interface
      global $wpdb;   $this->wpdb = $wpdb;

      global $WTuser;
      $this->ndays =  $ndays;
      // Tdate for which fetching data is allowed.
      $this->tdatemax = (int)(floor(time()/86400)) - (int)$this->ndays - 1;

   }

   // ---------------------------------------------------------------
   /// @details Returns the points for the chart class.
   ///   Takes $_POST arguments. Required: integer $_POST['cityID'],
   ///   and a string on $_POST['userID'] which can be a single integer or a list
   ///   of comma separated user ID's.
   ///   Uses $_POST arguments. cityID: an integer; userID: one integer
   ///   or a colon separated list of several userID's; Sleepy (if missing
   ///   default will be set to 1), Sleepy takes either 0 or 1.
   ///   column (default is set to 'points' which is full weekend points),
   ///   can also be 'points_d1' for Saturday or 'points_d2' for Sunday.   
   // ---------------------------------------------------------------
   public function timeseries_user_points_ajax() {

      global $WTuser;

      $args = (object)$_POST;
      // Append default Sleepy = 1 (show Sleepy)
      if ( ! property_exists($args,"sleepy") ) { $args->sleepy = 1; }
      else                                      { $args->sleepy = (int)$args->sleepy; }

      if ( ! property_exists($args,"column") ) { $args->column = "points"; }

      // Parsing user inputs. Creates an array of integers if the
      // format matches /^(\d+)(,\d+)*$/. Else error message and exit.
      if ( empty($args->userID) ) {
         print json_encode(array("error"=>"[ERROR] Input userID not set!")); die();
      } else if ( preg_match("/^(\d+)(,\d+)*$/",$args->userID) ) {
         $users = explode(",",$args->userID); array_walk($users,'intval');
      } else {
         print json_encode(array("error"=>"[ERROR] Input userID not valid (wrong format/pattern).")); die();
      }

      // Create sql command
      $sql = array();
      array_push($sql,"SELECT * FROM");
      array_push($sql,"(SELECT dead.tdate*86400 AS timestamp, "); // Begin of ( ) AS tmp table
      array_push($sql," ROUND(dead.points,2) AS sleepy,");
      $playerdata = array();
      for ( $i=0; $i < count($users); $i++ ) { 
         if ( ! $args->sleepy ) {
            array_push($playerdata,sprintf(" ROUND(p%d.points,2) AS player%d",$i+1,$i+1));
         } else {
            // Fill missing (not played) weekends with sleepy points
            array_push($playerdata,sprintf(" ROUND(CASE WHEN p%d.points IS NULL THEN "
                       ."dead.points ELSE p%d.points END,2) AS player%d",$i+1,$i+1,$i+1));
         }
      }
      array_push($sql,join(",\n",$playerdata));
      array_push($sql,"FROM");
      array_push($sql,sprintf("  (SELECT tdate, %s AS points FROM %swetterturnier_betstat",$args->column,$this->wpdb->prefix));

      $sleepyID = $WTuser->get_user_ID("Sleepy");
      array_push($sql,sprintf("   WHERE userID = %d and cityID = %d ) AS dead",$sleepyID,$args->cityID));

      for ( $i=0; $i < count($users); $i++ ) { 
         array_push($sql,"LEFT OUTER JOIN");
         array_push($sql,sprintf("  (SELECT tdate, userID, %s AS points FROM %swetterturnier_betstat",$args->column,$this->wpdb->prefix));
         array_push($sql,sprintf("   WHERE userID = %d and cityID = %d ) AS p%d",$users[$i],$args->cityID,$i+1));
         array_push($sql,sprintf("   ON dead.tdate = p%d.tdate",$i+1));
      }
      array_push($sql,") AS tmp"); // End of ( ) AS tmp table

      // Kill empty lines
      if ( ! $args->sleepy ) {
         $wherenot = array();
         for ( $i = 0; $i < count($users); $i++ )
         { array_push($wherenot,sprintf("player%d IS NOT NULL",$i+1)); }
         array_push($sql,sprintf("WHERE %s",join(" AND ",$wherenot )));
         array_push($sql,sprintf("AND timestamp < %d",$this->tdatemax*86400));
      } else {
         array_push($sql,sprintf("WHERE timestamp < %d",$this->tdatemax*86400));
      }

      // Order time series
      array_push($sql,"ORDER BY timestamp");

      array_push($sql,sprintf(" # COMMENT: ndays = %d",$this->ndays));
      //print "\n------------------------------\n";
      //print join("\n",$sql);
      //print "\n------------------------------\n";
      //die();

      // Save results
      $result = new stdClass();
      $result->sql = join("\n",$sql);
      
      $result->line_colors = array("#cccccc","#E16A86","#9C9500","#00AD81","#4195E2");
      $result->ylabel      = __("Points","wpwt");
      $result->xlabel      = __("Date","wpwt");
      $result->title       = __("Full weekend points","wpwt");

      // Append readable usernames
      $result->user_login  = array("Sleepy");
      for ( $i = 0; $i < count($users); $i++ ) {
         $tmp = $WTuser->get_user_by_ID( (int)$users[$i] );
         array_push($result->user_login,$tmp->user_login);
      }

      // Create proper data arrays
      $result->data       = array();
      $tmp = $this->wpdb->get_results( join("\n",$sql) );
      // No data?
      if ( $this->wpdb->num_rows == 0 ) { $result->num_rows = $this->wpdb->num_rows; }
      foreach( $tmp as $rec ) {
         $tmp = array(); foreach ( $rec as $key=>$val ) { array_push($tmp,(float)$val); }
         array_push($result->data,$tmp); //array((int)$rec->tdate*86400,(int)$rec->player1));
         unset($tmp);
      }
      echo json_encode($result,true);
      die();

   }


   // ---------------------------------------------------------------
   /// @details 
   ///   Takes arguments from $_POST. The following arguments can be set:
   ///   cityID integer.
   // ---------------------------------------------------------------
   public function participants_counts_ajax() {

      global $WTuser;
      $sleepyID = $WTuser->get_user_ID("Sleepy");

      $args = (object)$_POST;
      // Automaten, WARNING do not check time when active in group!
      $automaten_id = $WTuser->get_group_ID( "Automaten" );
      $tmp = $this->wpdb->get_results(sprintf("SELECT userID FROM %swetterturnier_groupusers WHERE groupID = %d",
                  $this->wpdb->prefix, $automaten_id));
      
      $automaten = array();
      foreach ( $tmp as $rec ) { array_push($automaten,$rec->userID); }
      
      // Referenztips, WARNING do not check time when active in group!
      $referenz_id = $WTuser->get_group_ID( "Referenztipps" );
      $tmp = $this->wpdb->get_results(sprintf("SELECT userID FROM %swetterturnier_groupusers WHERE groupID = %d", $this->wpdb->prefix, $referenz_id));

      $referenz = array();
      foreach ( $tmp as $rec ) { array_push($referenz,$rec->userID); }

      // Create strings for the sql query
      $id_automaten = sprintf("(%s)",join(",",$automaten));
      $id_referenz  = sprintf("(%s)",join(",",$referenz));
      $id_nonhuman  = sprintf("(%s)",join(",",array_merge($referenz,$automaten)));

      // Create SQL command
      $sql = array();
      array_push($sql,"SELECT timestamp, SUM(referenz) AS referenz, SUM(gruppe) AS gruppe,");
      array_push($sql,"SUM(automat) AS automat, SUM(human) AS human");
      array_push($sql,"FROM (");
      array_push($sql,sprintf("   SELECT betstat.tdate*86400 AS timestamp,"));
      // Check if automatenforecast
      array_push($sql,sprintf("   CASE WHEN user.ID IN %s THEN 1 ELSE 0 END AS automat,",$id_automaten));
      // Check if referenztip
      array_push($sql,sprintf("   CASE WHEN user.ID IN %s THEN 1 ELSE 0 END AS referenz,",$id_referenz));
      // Check if group bet
      array_push($sql,sprintf("   CASE WHEN user.ID IN %s THEN 0 ELSE",$id_nonhuman));
      array_push($sql,"      CASE WHEN user.user_login LIKE 'GRP_%' THEN 1 ELSE 0 END");
      array_push($sql,"   END AS gruppe,");
      // Check if human player
      array_push($sql,sprintf("   CASE WHEN user.ID IN %s THEN 0 ELSE",$id_nonhuman));
      array_push($sql,"      CASE WHEN user.user_login LIKE 'GRP_%' THEN 0 ELSE 1 END");
      array_push($sql,"   END AS human");
      ///
      array_push($sql,sprintf("   FROM %swetterturnier_betstat AS betstat",$this->wpdb->prefix));
      array_push($sql,sprintf("   LEFT JOIN %s AS user",$this->wpdb->users));
      array_push($sql,"   ON betstat.userID = user.ID");
      array_push($sql,sprintf("   WHERE betstat.cityID = %d AND NOT user.ID = %d",$args->cityID, $sleepyID));
      array_push($sql,") AS tmp");
      array_push($sql,"GROUP BY timestamp ORDER BY timestamp ASC");

      ///print "\n------------------------------\n";
      ///print join("\n",$sql);
      ///print "\n------------------------------\n";
      ///die();

      // Save results
      $result = new stdClass();
      $result->sql = join("\n",$sql);
      
      $result->line_colors = array("#cccccc","#E16A86","#9C9500","#00AD81");
      $result->ylabel      = __("Particioners","wpwt");
      $result->xlabel      = __("Date","wpwt");
      $result->title       = __("Number of participants","wpwt");

      $result->names = array(__("Reference methods","wpwt"),__("Groups","wpwt"),__("Automated forecasts","wpwt"),__("Human players","wpwt"));

      // Create proper data arrays
      $result->data       = array();
      $tmp = $this->wpdb->get_results( join("\n",$sql) );
      // No data?
      if ( $this->wpdb->num_rows == 0 ) { $result->num_rows = $this->wpdb->num_rows; }
      foreach( $tmp as $rec ) {
         $tmp = array(); foreach ( $rec as $key=>$val ) { array_push($tmp,(float)$val); }
         array_push($result->data,$tmp);
         unset($tmp);
      }
      echo json_encode($result,true);
      die();

   }

}
