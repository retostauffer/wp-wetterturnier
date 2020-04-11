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

      error_reporting(0);
      global $WTuser;
      $sleepyID = $WTuser->get_user_ID("Sleepy");

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
         $usercount = count($users);
      } else {
         print json_encode(array("error"=>"[ERROR] Input userID not valid (wrong format/pattern).")); die();
      }

      // Create sql command
      $sql  = "SELECT * FROM\n";
      $sql .= "(SELECT p1.tdate*86400 AS timestamp,\n";
      $sql .= "ROUND(p1.points, 1) AS player1";
      
      if ($usercount > 1) {
          for ( $i=2; $i <= $usercount; $i++ ) { 
             if ( ! $args->sleepy ) {
                $sql .= sprintf(",\nROUND(p%d.points, 1) AS player%d", $i, $i);
             } else {
                // Fill missing (not played) weekends with sleepy points
                $sql .= sprintf(",\nROUND(CASE WHEN p%d.points IS NULL THEN p1.points "
                   ."ELSE p%d.points END, 1) AS player%d", $i, $i, $i);
             }
          }
      }

      array_push($sql,join("\n",$playerdata));
      $sql .= "\nFROM\n";
      $sql .= sprintf("   (SELECT tdate, %s AS points FROM %swetterturnier_betstat\n",$args->column,
              $this->wpdb->prefix);
      $sql .= sprintf("   WHERE userID = %d AND cityID = %d ) AS p1\n", $users[0], $args->cityID);

      if ($usercount > 1) {
         for ( $i=2; $i <= $usercount; $i++ ) {
            $sql .= "LEFT OUTER JOIN\n";
            $sql .= sprintf("   (SELECT tdate, userID, %s AS points FROM %swetterturnier_betstat\n",
                    $args->column, $this->wpdb->prefix);
            $sql .= sprintf("   WHERE userID = %d AND cityID = %d ) AS p%d\n", $users[$i-1], 
                    $args->cityID, $i);
            $sql .= sprintf("   ON p1.tdate = p%d.tdate\n", $i);
          }
      }

      $sql .= ") AS tmp\n"; // End of ( ) AS tmp table

      $where = array();
      // Kill empty lines
      if ( ! $args->sleepy ) {
         for ( $i = 1; $i <= $usercount; $i++ ) {
             array_push($where, "player".$i." IS NOT NULL");
         }
      }
      array_push($where, "timestamp < " . $this->tdatemax*86400);

      $sql .= "WHERE ".join(" AND ", $where);

      // Order time series
      $sql .= "\nORDER BY timestamp";   
      
      //print "\n------------------------------\n";
      //print join("\n",$sql);
      //print "\n------------------------------\n";
      //die();

      // Save results
      $result = new stdClass();
      $result->sql = $sql;

      /* Wetterturnier ORANGE:     #ff6600  */
      /* Wetterturnier DARKBLUE:   #0c3772  */
      /* Wetterturnier GREEN:      #0e9311  */
      /* Wetterturnier LIGHTGREEN  #41c343  */
      /* Wetterturnier LIGHTBLUE:  #6592cf  */
      /* Wetterturnier GRAY:       #b3b3b3  */
      /* Wetterturnier VERLY LIGHT BLUEISH: #eef0f2 */

      $sleepy_color  = "#cccccc";
      $player_colors = array("#ff6600","#0c3772","#0e9311","red","darkviolet");

      $result->line_colors = array_fill(0, $usercount, "");

      //$result->line_colors = array("#ff6600","#E16A86","#9C9500","#00AD81","#4195E2");
      $ii = 0;
      for ($i=0; $i < $usercount; $i++) {
        if ($users[$i] == $sleepyID) {
            $result->line_colors[$i] = $sleepy_color;
            $ii++;
        } else {
            $result->line_colors[$i] = $player_colors[$i - $ii]; 
        }
      }
      //$result->line_colors = array("#cccccc","#E16A86","#9C9500","#00AD81","#4195E2");
      $result->ylabel      = __("Points","wpwt");
      $result->xlabel      = __("Date","wpwt");
      $result->title       = __("Full weekend points","wpwt");

      // Append readable usernames
      $result->user_login  = array();
      for ( $i = 0; $i < $usercount; $i++ ) {
         $tmp = $WTuser->get_user_by_ID( (int)$users[$i] );
         array_push($result->user_login,$tmp->user_login);
      }

      // Create proper data arrays
      $result->data       = array();
      $tmp = $this->wpdb->get_results( $sql );
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

   public $str = <<<SQL
      SELECT * FROM
      (SELECT p1.tdate*86400 AS timestamp, 
      ROUND(p1.points, 1) AS player1, ROUND(p2.points, 1) AS player2
      FROM
         (SELECT tdate, points FROM wp_wetterturnier_bets
         WHERE userID = 1461 AND cityID = 4 AND paramID IN(1,2) ) AS p1
         LEFT OUTER JOIN
         (SELECT tdate, userID, points FROM wp_wetterturnier_bets
         WHERE userID = 954 AND cityID = 4 AND paramID IN(1,2) ) AS p2
         ON p1.tdate = p2.tdate
      ) AS tmp
      WHERE player1 IS NOT NULL AND player2 IS NOT NULL AND timestamp < 1583712000
      ORDER BY timestamp
SQL;

   public function timeseries_parameter_points_ajax() {

      global $WTuser;
      $sleepyID = $WTuser->get_user_ID("Sleepy");

      $args = (object)$_POST;

      //for points_d1/d2 we have to use the betdate column instead of tdate
      //d1: bdate = tdate+1
      //d2: bdate = tdate+2

      // Parsing user inputs. Creates an array of integers if the
      // format matches /^(\d+)(,\d+)*$/. Else error message and exit.
      if ( empty($args->userID) ) {
         print json_encode(array("error"=>"[ERROR] Input userID not set!")); die();
      } else if ( preg_match("/^(\d+)(,\d+)*$/",$args->userID) ) {
         $users = explode(",",$args->userID); array_walk($users,'intval');
         $usercount = count($users);
      } else { 
         print json_encode(array("error"=>"[ERROR] Input userID not valid (wrong format/pattern).")); die();
      }

      if ( empty($args->cityID) ) {
         print json_encode(array("error"=>"[ERROR] Input cityID not set!")); die();
      } else { $cityID = $args->cityID; }

      if ( empty($args->params) ) {
         print json_encode(array("error"=>"[ERROR] Input params not set!")); die();
      } else { $params = $args->params; }

      $sql  = "SELECT * FROM\n";
         $sql .= "SELECT(p1.tdate*86400 AS timestamp\n";
         $sql .= "ROUND(p1.pouints, 1) AS player1";
         if ($usercount > 1) {
            for ($i=2; $i <= $usercount; $i++) {
               $sql .= sprintf(", ROUND(p%d.points, 1) AS player%d", $i, $i);
            }
         }

      $sql .= "FROM\n";
      $sql .= "   (SELECT tdate, points FROM wp_wetterturnier_bets\n";
      $sql .= sprintf("   WHERE userID = %d AND cityID = %d AND paramID IN(%s) ) AS p1\n", $users[0],
              $cityID, $params);;

      if ($usercount > 1) {
         $sql .= "LEFT OUTER JOIN\n";
         for ($i=2; $i <= $usercount; $i++) {
            $sql .= "   (SELECT tdate, userID, points FROM wp_wetterturnier_bets\n";
            $sql .= sprintf("WHERE userID = %d AND cityID = %d AND paramID IN(%s) ) AS p%d\n",
                           $users[$i-1], $cityID, $params);
            $sql .= sprintf("ON p1.tdate = p%d.tdate\n", $i);
         }
      }

      $sql .= ") AS tmp\n"; // End of ( ) AS tmp table

      $where = array();
      // Kill empty lines
      for ( $i = 1; $i <= $usercount; $i++ ) {
         array_push($where, "player".$i." IS NOT NULL");
      }
      array_push($where, "timestamp < " . $this->tdatemax*86400);

      $sql .= "WHERE ".join(" AND ", $where);

      // Order time series
      $sql .= "\nORDER BY timestamp";

      //calculate mean of points for each player by timestamp
      //
   }

   // ---------------------------------------------------------------
   /// @details 
   ///   Takes arguments from $_POST. The following arguments can be set:
   ///   cityID integer.
   // ---------------------------------------------------------------
   public function participants_counts_ajax() {

      //error_reporting(0); 
      global $WTuser;
      $sleepyID = $WTuser->get_user_ID("Sleepy");

      $args = (object)$_POST;

      // Automaten, WARNING do not check time when active in group!
      $automat_id = $WTuser->get_group_ID( "Automaten" );
      $tmp = $this->wpdb->get_results(sprintf("SELECT userID FROM %swetterturnier_groupusers WHERE groupID = %d", $this->wpdb->prefix, $automat_id));

      $automat = array();
      foreach ( $tmp as $rec ) { array_push($automat, $rec->userID); }

      // Referenztips, WARNING do not check time when active in group!
      $referenz_id = $WTuser->get_group_ID( "Referenztipps" );
      $tmp = $this->wpdb->get_results(sprintf("SELECT userID FROM %swetterturnier_groupusers WHERE groupID = %d", $this->wpdb->prefix, $referenz_id));

      $referenz = array();
      foreach ( $tmp as $rec ) { array_push($referenz, $rec->userID); }

      // Create strings for the sql query
      $id_automat  = sprintf("(%s)", join(",", $automat));
      $id_referenz = sprintf("(%s)", join(",", $referenz));
      $id_nonhuman = sprintf("(%s)", join(",", array_merge($referenz, $automat)));

      // Create SQL command
      $sql  = "SELECT timestamp, SUM(referenz) AS referenz, SUM(gruppe) AS gruppe,\n";
      $sql .= "SUM(automat) AS automat, SUM(human) AS human\n";
      $sql .= "FROM (\n";
      $sql .= "   SELECT betstat.tdate*86400 AS timestamp,\n";
      $sql .= "   CASE WHEN user.ID IN ".$id_automat. " THEN 1 ELSE 0 END AS automat,\n";
      $sql .= "   CASE WHEN user.ID IN ".$id_referenz." THEN 1 ELSE 0 END AS referenz,\n";
      $sql .= "   CASE WHEN user.ID IN ".$id_nonhuman." THEN 0 ELSE\n";
      $sql .= "      CASE WHEN user.user_login LIKE 'GRP_%' THEN 1 ELSE 0 END\n";
      $sql .= "   END AS gruppe,\n";
      $sql .= "   CASE WHEN user.ID IN ".$id_nonhuman." THEN 0 ELSE\n";
      $sql .= "      CASE WHEN user.user_login LIKE 'GRP_%' THEN 0 ELSE 1 END\n";
      $sql .= "   END AS human\n";
      $sql .= "   FROM ".$this->wpdb->prefix."wetterturnier_betstat AS betstat\n";
      $sql .= "   LEFT JOIN ".$this->wpdb->users." AS user\n";
      $sql .= "   ON betstat.userID = user.ID\n";
      $sql .= "   WHERE betstat.cityID = ".$args->cityID." AND NOT user.ID = ".$sleepyID;
      $sql.="\n) AS tmp\n";
      $sql .= "GROUP BY timestamp ORDER BY timestamp ASC";
/*    
      //this would be even nicer to read but doesnt work somehow???
      $sql = <<<SQL
         SELECT timestamp, SUM(referenz) AS referenz, SUM(gruppe) AS gruppe
         SUM(automat) AS automat, SUM(human) AS human
         FROM (
            SELECT betstat.tdate*86400 AS timestamp
            CASE WHEN user.ID IN $id_automat  THEN 1 ELSE 0 END AS automat
            CASE WHEN user.ID IN $id_referenz THEN 1 ELSE 0 END AS referenz
            CASE WHEN user.ID IN $id_nonhuman THEN 0 ELSE
               CASE WHEN user.user_login LIKE 'GRP_%' THEN 1 ELSE 0 END
            END AS gruppe
            CASE WHEN user.ID IN $id_nonhuman THEN 0 ELSE
               CASE WHEN user.user_login LIKE 'GRP_%' THEN 0 ELSE 1 END
            END AS human
            FROM {$this->wpdb->prefix}wetterturnier_betstat AS betstat
            LEFT JOIN $this->wpdb->users AS user
            ON betstat.userID = user.ID
            WHERE betstat.cityID = $args->cityID AND NOT user.ID = $sleepyID
         ) AS tmp
         GROUP BY timestamp ORDER BY timestamp ASC
SQL;
*/
      // Save results
      $result = new stdClass();
      $result->sql = $sql;
      
      $result->line_colors = array("#cccccc","#E16A86","#9C9500","#00AD81");
      $result->ylabel      = __("Particioners","wpwt");
      $result->xlabel      = __("Date","wpwt");
      $result->title       = __("Number of participants","wpwt");

      $result->names = array(__("Reference methods","wpwt"),__("Groups","wpwt"),__("Automated forecasts","wpwt"),__("Human players","wpwt"));

      // Create proper data arrays
      $result->data       = array();
      $tmp = $this->wpdb->get_results( $sql );
      // No data?
      if ( $this->wpdb->num_rows == 0 ) { $result->num_rows = $this->wpdb->num_rows; }
      foreach( $tmp as $rec ) {
         $tmp = array();
         foreach ( $rec as $key=>$val ) {
             array_push($tmp, (float)$val);
         }
         array_push($result->data,$tmp);
         unset($tmp);
      }
      echo json_encode($result,true);
      die();
   }
}
