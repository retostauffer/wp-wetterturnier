<?php
/**
 * Class which calculates (and caches) the rankings for different views,
 * e.g., weekend rankings, yearly rankings, alpine ranking, and so far
 * and so on.
 *
 * @file rankingclass.php
 * @author Reto Stauffer
 * @date November 3, 2018
 * @brief New more efficient ranking table calculation.
 */


/** Class which calculates (and caches) the rankings for different views,
 * e.g., weekend rankings, yearly rankings, alpine ranking, and so far
 * and so on.
 *
 * @param deadman (str)
 *   login name of the user which
 *   provides the points for players not having participated. On 
 *   wetterturnier this user is known as "Sleepy" (default).
 * @param max_points (int):
 *   maximum number of points per weekend.
 *   Used to compute the 'relative points' gained of the players
 *   given the ranking settings. Default is `200` as on wetterturnier.de.
 * @param cache (bool)
 *   whether file cache is active or not. If `true` (default) the rankings
 *   will be stored (serialized) in the `cache` folder and will be re-used
 *   if another user makes the same request. Files will be kept for 10 minutes.
 *   Can be disabled if set to `false`. Note: requires write access to the
 *   `cache` folder within the wp-wetterturnier plugin directory!
 */
class wetterturnier_rankingObject {

    /// Will contain a copy of the global $wpdb instance. Used as
    /// class-internal reference for database requests.
    private $wpdb;
    /// Attribute to store the cityObject.
    private $cityObj = null;
    /// Maximum number of points per weekend.
    private $max_points = 200;

    # Whether or not the trend (increase/decrease in rank)
    # should be calculated. Is controlled by set_tdates().
    private $calc_trend = true;

    # Used to generate the name of the cache files if $cache is enabled.
    private $cachehash = "UNNAMED";

    # WTuser object
    private $WTuser;

    # Used to store the ranking from prepare_ranking
    private $ranking = null;

    # Whether file caching should be used or not
    private $cache = true;

    # Name of the deadman.
    private $deadman;

    # Used to store some words to be able to provide multilingual
    # output. Using php language files to store some words into the
    # resulting JSON array to display the tables via jQuery.
    private $dict;

    function __construct($deadman = "Sleepy", $max_points = 200, $cache = true) {

       global $wpdb; $this->wpdb = $wpdb;
       global $WTuser;

       # Check if access is granted
       $this->WTuser     = $WTuser;
       $this->deadman    = $deadman;
       $this->max_points = $max_points;
       $this->cache      = $cache;

       $this->dict = new stdClass();
       $this->dict->older      = __("Older", "wpwt");
       $this->dict->newer      = __("Newer", "wpwt");
       $this->dict->place      = __("place", "wpwt");
       $this->dict->th         = array(__("st","wpwt"),__("nd","wpwt"),__("rd","wpwt"),__("th","wpwt"));
       $this->dict->trophy     = array( 'first', 'second', 'third', 'none' );
       $this->dict->p          = __("points", "wpwt");
       $this->dict->points     = __("Points", "wpwt");
       $this->dict->points_d1  = __("Sat","wpwt");
       $this->dict->points_d2  = __("Sun","wpwt");
       $this->dict->trend      = "+/-";
       $this->dict->played     = __("Part", "wpwt");
       $this->dict->difference = __("Diff", "wpwt");
       $this->dict->rank       = __("Rank", "wpwt");
       $this->dict->user       = __("User", "wpwt");
       $this->dict->max_points = __("The maximum score (total) for the ranking is","wpwt");
       $this->dict->statusbar    = __("Statusbar","wpwt");

       //only for etertnal ranking
       $this->dict->points_max   = __("Max","wpwt");
       $this->dict->points_mean  = __("Mean","wpwt");
       $this->dict->sd_ind       = __("PSD","wpwt");
       $this->dict->won_weekends = __("Wins (%)","wpwt");
       $this->dict->won_seasons  = __("Seasons (x/N)","wpwt");

    }


    /* Set city/cities. Rankings can be computed for one specific city or
     * for multiple cities (overall ranking for users participating in 
     * all cities, e.g., the 5-city-ranking).
     *
     * The input argument `$cityObj` can either be a single object of the
     * class :php:class:`wetterturnier_cityObject` or an array containing
     * multiple :php:class:`wetterturnier_cityObject` objects.
     * This method has no return value, it simply stores the object internally
     * which is then used later on when preparing the data/ranks.
     *
     * @param $cityObj (:php:class:`wetterturnier_cityObject` or an :obj:`array` of :php:class:`wetterturnier_cityObject`)
     *    defines for which city/cities the ranking should be calculated.
     *
     * See also :php:meth:`set_tdates`.
     */
    public function set_cities( $cityObj ) { $this->cityObj = $cityObj; }

    /* Simply setting a type or name. Only used to define the cache file name.
     * Default is "UNNAMED".
     *
     * @param name (str)
     *    name or hash used to create the cache files.
     */
    public function set_cachehash( $name ) { $this->cachehash = str_replace(" ", "_", (string)$name); }


    /* Store the date ranges for which the request should be made.
     *
     * @param from(int or stdClass)
     *   either a single integer
     *   for the first date (days since 1970-01-01) for the current rank,
     *   or an object. If it is an object we assume it contains four elements
     *   specifying 'from', 'to', 'from_prev', and 'to_prev'. Else all four
     *   iputs have to be given!
     * @param to (Null or int)
     *   if input $from is an object this argument
     *   is simply ignored. Else should contain an integer with the last day
     *   (days since 1970-01-01) of the period for the current rank.
     * @param from_prev (Null or int)
     *   if input $from is an object this argument
     *   is simply ignored. Else should contain an integer with the first day
     *   (days since 1970-01-01) of the period for the previouse rank. Used to
     *   compute the trend.
     * @param to_prev (Null or int)
     *   if input $from is an object this argument
     *   is simply ignored. Else should contain an integer with the last day
     *   (days since 1970-01-01) of the period for the previouse rank. Used to
     *   compute the trend.
     *
     * See also :php:meth:`set_tdates`.
     */
    public function set_tdates($from, $to = Null, $from_prev = Null, $to_prev = Null, $calc_trend = false) {

        if ( ! is_object($from) ) {
            $this->tdates = (object) array("from"      => $from,      "to"      => $to,
                                           "from_prev" => $from_prev, "to_prev" => $to_prev);
        } else { $this->tdates = $from; }

        if ( is_null($this->tdates->from_prev) | is_null($this->tdates->to_prev) ) {
            $this->tdates->min = min($this->tdates->from, $this->tdates->to);
            $this->tdates->max = max($this->tdates->from, $this->tdates->to);
            $this->calc_trend = false;
        } else {
            $this->tdates->min = min($this->tdates->from,      $this->tdates->to,
                                     $this->tdates->from_prev, $this->tdates->to_prev);
            $this->tdates->max = max($this->tdates->from, $this->tdates->to,
                                     $this->tdates->from_prev, $this->tdates->to_prev);
            $this->calc_trend = $calc_trend;
        }

        // If 'max' > 'latest':
        if ( property_exists($this->tdates, "latest") ) {
            if ( $this->tdates->max > $this->tdates->latest ) {
                $this->tdates->max = $this->tdates->latest;
            }
        }
    }


    /* Returns the data in a structured way. The 'data' are the points for each
     * specific city/tournament_date/user where the 'user' nesting level is not
     * requred (and therefore not returned) if input `$deadman = true`.
     *
     * @pararm deadman (bool)
     *   if `false` (default) all points will
     *   be returned. Else (if `true`) only the deadman points will
     *   be returned. Uses the 'deadman' argument from this object.
     *   If the deadman user cannot be found: return null such that players
     *   which have not participated simply get 0 points.
     * Returns:
     * A stdClass object of the following form, here one example
     * with for only one tournament date (tdate).
     * If input argument $deadman is `true`, only the deadman user will be loaded.
     * >>> stdClass Object
     * >>>  (
     * >>>    [data] => stdClass Object
     * >>>      (
     * >>>        [ambot_lang] => stdClass Object
     * >>>          (
     * >>>            [tdate_17830] => 158.60000610351562
     * >>>          )
     * >>>        [DWD-MOS-Mix] => stdClass Object
     * >>>          (
     * >>>            [tdate_17830] => 152.8000030517578
     * >>>          )
     * >>>        [..next user..] ...
     * >>>  
     * >>>      )
     * >>>    [users] => Array
     * >>>      (
     * >>>        [0] => ambot_lang
     * >>>        [1] => DWD-MOS-Mix
     * >>>        [2] => ..next user..
     * >>>      )
     * >>>    [tdates] => Array
     * >>>        (
     * >>>            [0] => 17830
     * >>>        )
     * >>>  )
     */
    private function _get_data_object( $deadman = false, $d1d2 = false, $type = NULL ) {

        if ($type === "eternal") {

            # for PHP < 7.3
            if ( ! function_exists('is_countable') ) {
                function is_countable($var) {
                    return ( is_array($var) || $var instanceof Countable );
                }
            }

            # if more than one city was given we look for the eternal all-city ranking (cityID = 0)
            $cityID = is_countable( $this->cityObj ) ? 0 : $this->cityObj->get("ID");

            # get ranking from userstats
            $sql = sprintf("SELECT u.ID, u.user_login, s.points_adj AS points, s.ranks_weekend AS ranks_weekend,\n" .
            "s.sd_ind AS sd_ind, s.max AS points_max, s.mean AS points_mean, s.part AS played\n" .
            "FROM %susers AS u RIGHT OUTER JOIN %swetterturnier_userstats AS s ON u.ID = s.userID\n".
            "WHERE cityID = %d AND ID IS NOT NULL AND s.part >= 25 GROUP BY u.ID",
            $this->wpdb->prefix, $this->wpdb->prefix, $cityID);

        } else {

            # If $deadman is set to true: only fetch deadman data
            if ( $deadman ) {
                $deadman = get_user_by( "login", $this->deadman );
                if ( ! $deadman ) {
                    return Null;
                }
            }
            $where_user = (! $deadman ) ? "" : sprintf(" AND userID = %d ",$deadman->ID);

            # Where city
            if ( is_array($this->cityObj) ) {
                $tmp = array();
                foreach ( $this->cityObj as $rec ) { array_push($tmp, sprintf("%d",$rec->get("ID"))); }
                $where_city = sprintf("b.cityID IN (%s)", join(",",$tmp));
                unset($tmp);
            } else {
                $where_city = sprintf("b.cityID = %d",(int)$this->cityObj->get("ID"));
            }

            # Where tdate
            if ( $this->tdates->min == $this->tdates->max ) {
                $where_tdate = sprintf("b.tdate = %d",$this->tdates->max);
            } else {
                $where_tdate = sprintf("b.tdate between %d and %d", $this->tdates->min, $this->tdates->max);
            }

            # Just no need to load user_login for a known user!
            $usercol = ($deadman) ? "" : "u.ID, u.user_login, ";

            # Create SQL command
            $sql = array();
            array_push($sql, sprintf("SELECT b.tdate, %s", $usercol));
            array_push($sql, " SUM(b.points) AS points,");
            #carefull, sleepy has no points for d1/d2
            if ( $d1d2 ) {
               array_push($sql, " SUM(b.points_d1) AS points_d1,");
               array_push($sql, " SUM(b.points_d2) AS points_d2,");
            }
            array_push($sql, " COUNT(*) AS played");
            array_push($sql, sprintf("FROM %susers AS u RIGHT OUTER JOIN", $this->wpdb->prefix));
            array_push($sql, sprintf("%swetterturnier_betstat AS b", $this->wpdb->prefix));
            array_push($sql, "ON u.ID=b.userID WHERE");
            array_push($sql, sprintf("%s AND %s %s", $where_city, $where_tdate, $where_user));
            array_push($sql, "GROUP BY u.ID, b.tdate");
            $sql = join("\n", $sql);
            #print($sql);
            #die(0);

            # If calculating the ranking for multiple
            # cities we have to capsule the statement above: 
            if ( count($this->cityObj) > 1 ) {
                $sql = sprintf("SELECT * FROM (\n%s\n) AS X WHERE X.played = %d",
                               $sql, count($this->cityObj));
            }
        }

        #printf("\n%s\n", join("\n",$sql));
        $dbres = $this->wpdb->get_results($sql);

        # If deadman is requested: create one stdClass object containing
        # the points for each tournament date, no need to add an extra
        # nesting level containing the username.
        if ( $deadman ) {
            $res = new stdClass();
            foreach ( $dbres as $rec ) {
                # Append tourmanet date to city
                $thash = sprintf("tdate_%d",$rec->tdate);
                $res->$thash = $rec->points;
            }
        } else {
            $res = (object)array("data"=>new stdClass(), "users"=>array(), "tdates"=>array());
            foreach ( $dbres as $rec ) {
                
                # Append user names and tournament dates
                if ( ! in_array($rec->user_login, $res->users) ) {
                    array_push($res->users, $rec->user_login);
                }

                # User hash
                $uhash = $rec->user_login;
                if ( ! property_exists($res->data, $uhash) ) { $res->data->$uhash = new stdClass(); }
                
                $res->data->$uhash->userID = $rec->ID;

                if ( $type === "eternal" ) {
                    $ranks = $rec->ranks_weekend;
                    $res->data->$uhash->won_weekends = explode( ",", $ranks )[0];
                    $res->data->$uhash->points       = $rec->points;
                    $res->data->$uhash->played       = $rec->played;
                    $res->data->$uhash->sd_ind       = $rec->sd_ind;
                    $res->data->$uhash->points_max   = $rec->points_max;
                    $res->data->$uhash->points_mean  = $rec->points_mean;
                    //$res->data->$uhash->won_seasons  = $rec->won_seasons;
                    //$res->data->$uhash->played_seasons = $rec->played_seasons;
                } else {
                    if ( ! in_array($rec->tdate, $res->tdates ) ) {
                        array_push($res->tdates, $rec->tdate );
                    }
                    # Append tournament date to city
                    $thash = sprintf("tdate_%d", $rec->tdate); 
                    if ( ! isset($res->data->$uhash->$thash) ) {
                        $res->data->$uhash->$thash = new stdClass();
                    }
                    $res->data->$uhash->$thash->points = $rec->points;

                    if ($d1d2) {
                        $res->data->$uhash->$thash->points_d1   = $rec->points_d1;
                        $res->data->$uhash->$thash->points_d2   = $rec->points_d2;
                    }                
                }
            }
        }

        $sql=sprintf("SELECT COUNT(*) AS c FROM %swetterturnier_dates WHERE tdate BETWEEN %d AND %d AND status = 1",
            $this->wpdb->prefix, $this->tdates->from, $this->tdates->to);
        $res->total = $this->wpdb->get_row($sql)->c;

        return $res;
    }

    /* Helper method, returns the tournament date before the first one requested
     * for the ranking. If the first one for the ranking is the first, so that 
     * there is no previous, a :obj:`null` will be returned.
     */
    private function _get_previous_tournament_date( $sort = "DESC" ) {
        # Find tdate before $tdate->min for ranking
        $sql = sprintf("SELECT distinct(tdate) from %swetterturnier_betstat "
            ." WHERE tdate < %d ORDER BY tdate DESC LIMIT 1;",
            $this->wpdb->prefix, $this->tdates->min);
        $res = $this->wpdb->get_row($sql);
        if ( $this->wpdb->num_rows == 0 ) {
            return null;
        }
        return $res->tdate;
    }

    /* Helper method, returns the tournament date after the last tournament date
     * requested. This is used to provide the link on the front page to navigate
     * trough the ranking pages. 
     * If the last tournament is the current one (newest) a :obj:`null` will be returned.
     */
    private function _get_later_tournament_date( ) {
        # Find tdate before $tdate->min for ranking
        $sql = sprintf("SELECT distinct(tdate) from %swetterturnier_betstat "
            ." WHERE tdate > %d ORDER BY tdate ASC LIMIT 1;",$this->wpdb->prefix,$this->tdates->max);
        $res = $this->wpdb->get_row($sql);
        if ( $this->wpdb->num_rows == 0 ) {
            return null;
        }
        return $res->tdate;
    }


    /* Creates the admin link to modify the current user/station.
     *
     * @param type (str)
     *   user type (e.g., user or mitteltip)
     *   Obj (...): Either a wordpress user Object or a wetterturnier
     *   :class:`wetterturnier_stationObject` for the observations.
     *
     * @return Returns a string with a html element to place the edit button or
     *   an empty string if no editbuttion is needed (or not allowed as the user
     *   is no admin).
     */
    private function _get_edit_button( $type, $Obj ) {

       // If no admin: return
       // if ( ! current_user_can('manage_options') ) { return(""); }
       if ( ! isset($user->allcaps["wetterturnier_admin"]) ) { return(""); }
       // If mitteltip or sleepy: return
       else if ( $type === "mitteltip" || $type === "sleepy" )
         { return(""); }
       // If this is an observation entry (use $type = "obs")
       else if ( $type === "obs" ) {
           return( sprintf("<span class='button small edit edit-obs' url='%s' "
                          ."station='%d' cityID='%s' tdate='%d'></span>",
                          admin_url(), (int)$identifier,
                          $this->cityObj->get('ID'), $this->tdates->max));
       } else {
           return( sprintf("<span class='button small edit edit-bet' url='%s' "
                          ."userID='%d' cityID='%s' tdate='%d'></span>",
                          admin_url(), $Obj->ID, $this->cityObj->get('ID'), $this->tdates->max));
       }

    }


    /* Creates a button to view the bets of a user or group in detail
     * (featherlight window)
     */
    private function _get_detail_button( $userObj ) {
        if ( $userObj->display_name === "Sleepy" ) {
           return( sprintf("<span class=\"button sleepy\"></span>") ); }
        else {
        return sprintf("<span class=\"button small detail\" userid=\"%d\" "
                      ."cityid=\"%d\" tdate=\"%d\"></span>",
                      $userObj->ID, $this->cityObj->get("ID"), $this->tdates->max);
                      }
    }


    /** Returns the file name for the cache file (only used if cache is set
     * to ``true``, see initialization arguments of this class).
     *
     * @return Returns the absolute path to the cache file.
     */
    private function _get_cache_file_name() {
        # Where city
        if ( is_array($this->cityObj) ) {
            $tmp = array();
            foreach ( $this->cityObj as $rec ) { array_push($tmp,sprintf("%d",$rec->get("ID"))); }
            $city_hash  = join(":", $tmp);
        } else {
            $city_hash  = sprintf("%d", (int)$this->cityObj->get("ID"));
        }

        # Where tdate
        $tdate_hash = sprintf("%s-%s_%d-%d",
             (is_null($this->tdates->from_prev)) ? "NULL" : sprintf("%d", $this->tdates->from_prev),
             (is_null($this->tdates->from_prev)) ? "NULL" : sprintf("%d", $this->tdates->to_prev),
             $this->tdates->from, $this->tdates->to);

        # Return cache file
        return(sprintf("%scache/wt-ranking_%s_%s_%s.json", plugin_dir_path(__FILE__),
                       $this->cachehash, $tdate_hash, $city_hash));
    }

     /* Assign rank to each value of the array $in. 
     * Pretty cool function I wrote, I think :).
     *
     * Args:
     *   in (array): Array containing as set of numeric values.
     *
     * Returns:
     *   Returns an array of the same length with ranks. Highest
     *   values of $in get rank 1, lower values get higher ranks.
     *   The same values are attributed to the same ranks.
     *   Ranks are re-used. Some ranks may not appear if some
     *   elements in $in do have the same value!
     */
    private function _array_rank( $in ) {
        # Keep input array "x" and replace values with rank.
        # This preserves the order. Working on a copy called $x
        # to set the ranks.
        $x = $in; arsort($x);
        # Initival values
        $rank       = 0;
        $hiddenrank = 0;
        $hold = null;
        foreach ( $x as $key=>$val ) {
            # Always increase hidden rank
            $hiddenrank += 1;
            # If current value is lower than previous:
            # set new hold, and set rank to hiddenrank.
            if ( is_null($hold) || $val < $hold ) {
                $rank = $hiddenrank;
                $hold = $val;
            }
            # Set rank $rank for $in[$key]
            $in[$key] = $rank;
        }
        return $in;
    }


    /* Prepares a ranking object based on the class attributes 'tdate' and
     * 'cityObj' (also allowed for time periods and multiple cities at the
     * same time). This function loads the data from the database and creates
     * a structured object containing the user points and ranks.
     * Given a valid deadman username the points will be filled with the
     * points of deadman (known as Sleepy on wetterturnier.de), if not
     * 0 points will be given for not participating at all.
     *
     * Note: if cache is set to true (see __construct method) the result
     * will be stored in a file as a serialized object. If the file exists
     * and is not older than 600 seconds the data will be loaded from the
     * file rather than re-calculating the rankings based on the database.
     * This can be used to reduce the server load, especially as most
     * users do check the very same rankings over and over again.
     * Requires a folder "wp-wetterturnier/cache" with rw permissions for
     * the webserver user. Files are _not_ deleted via php, if you use
     * the caching take care of removing the files in the cache folder
     * every now and then to avoid filling the disc for nothing.
     *
     * @return No return! Stores the ranking object on the parent object itself.
     *  There are different ouptut methods to display/return the data.
     *
     * .. todo:: Explain caching.
     */
    public function prepare_ranking( $type = NULL, $d1d2=false ) {

        if ( is_null($this->tdates) || is_null($this->cityObj) ) {
            //echo "Sorry, cannot prepare ranking, tdate or cityObject not set!";
            return null;
        }

        ///if ( is_numeric($this->tdates->max) ) {
        ///    ob_start();
        ///    $closed = $this->WTuser->check_view_is_closed($this->tdates->max);
        ///    ob_end_clean();
        ///    if ( $closed ) { die("No access! Go away, please! :)"); }
        ///}

        # If caching is enabled: check if we can load the
        # data from disc to avoid re-calculating the ranking.
        if ( $this->cache ) {
            $cache_file  = $this->_get_cache_file_name();
            if ( file_exists($cache_file) ) {
                # If newer than 15 minutes: load file
                if ( time() - filemtime($cache_file) <= 900 ) {
                    $this->ranking = unserialize(file_get_contents($cache_file));
                    return(false);
                }
            }
        }

        # Loading deadman points. Whenever a player did not participate he/she
        # will get these points. May return "0" if the deadman is not defined.
        $deadman = ($type !== "eternal") ? $this->_get_data_object($deadman=true) : NULL;

        # Loading user data
        $userdata = $this->_get_data_object($deadman=false, $d1d2=$d1d2, $type=$type);

        $ranking = (object)array("pre"=>new stdClass(), "now"=>new stdClass());

        # Number of played tournaments so far
        $ntournaments = 0;
        $latest = $this->WTuser->latest_tournament(floor(time() / 86400))->tdate;
        foreach ( $userdata->tdates as $tdate ) {
            if ( $tdate >= $this->tdates->from && $tdate <= $latest ) { $ntournaments++; }
        }

        # Prepare data
        foreach ( $userdata->data as $user=>$data ) {

            # Append user to $ranking object if not yet existing
            if ( ! property_exists($ranking->pre, $user) ) {
                if ( $this->calc_trend ) {
                    $ranking->pre->$user = (object)array("played"=>0,"points"=>0);
                    if ($d1d2) { $ranking->pre->$user->points_d1 = NULL; $ranking->pre->$user->points_d2 = NULL; }
                }
                $ranking->now->$user = (object)array("played"=>0,"points"=>0);
                if ($d1d2) { $ranking->now->$user->points_d1 = NULL; $ranking->now->$user->points_d2 = NULL; }
            }

            # Looping over the tournament dates
            foreach ( $userdata->tdates as $tdate ) {

                # Skip if in the future
                if ( $tdate > $latest ) { continue; }

                # Create hash for the object names
                $thash = sprintf("tdate_%d",$tdate);

                # Default: 0 points and not participated (default)
                $points = 0; $played = 0;
                if ($d1d2) { $points_d1 = 0; $points_d2 = 0; }

                # If user got points: use user points 
                if ( property_exists($data, $thash) ) {
                    $points = $data->$thash->points;
                    $played = 1;
                    if ($d1d2) {
                        if (is_null($points_d1)) { $data->$thash->points_d1 = NULL; $data->$thash->points_d2 = NULL;
                        } else {
                            $points_d1 = $data->$thash->points_d1;
                            $points_d2 = $data->$thash->points_d2;
                        }
                    }
                # Else check if deadman exists and has points for this
                # specific tournament date ($thash).
                } else if ( $deadman and  property_exists($deadman, $thash) ) {
                    $points = $deadman->$thash;
                }
                # Adding points
                # We have to check whether the points fall in the
                # previous time period (from_prev, to_prev) or/and
                # into the current time period (from, to).
                # These points are used later on to calculate the
                # trends (+/- ranks gained).
                # 
                # Visual help
                #
                #   from_prev          to_prev
                #      +-----------------+
                #           +-----------------+
                #         from                to
                #   -------------------------------> tdate axis
                #
                if ( $this->calc_trend ) {
                    if ( $tdate >= $this->tdates->from_prev &&
                         $tdate <= $this->tdates->to_prev ) {
                        $ranking->pre->$user->points += $points;
                        $ranking->pre->$user->played += $played;
                        if ($d1d2) {
                            if ( ! is_null($points_d1)) {
                                $ranking->pre->$user->points_d1 += $points_d1;
                                $ranking->pre->$user->points_d2 += $points_d2;
                            } 
                        }
                    }
                }
                if ( $tdate >= $this->tdates->from &&
                     $tdate <= $this->tdates->to ) {
                    $ranking->now->$user->points += $points;
                    $ranking->now->$user->played += $played;
                    if ($d1d2) {
                        if ( ! is_null($points_d1)) {
                            $ranking->now->$user->points_d1 += $points_d1;
                            $ranking->now->$user->points_d2 += $points_d2;
                        }
                    }
                }
            }

            // for eternal ranking we already have participation count and so on in db
            if ( $type === "eternal" ) {
                $ranking->now->$user->points       = $data->points;
                $ranking->now->$user->played       = $data->played;
                $ranking->now->$user->won_weekends = (int)$data->won_weekends;
                $ranking->now->$user->sd_ind       = $data->sd_ind;
                $ranking->now->$user->points_max   = $data->points_max;
                $ranking->now->$user->points_mean  = $data->points_mean;
                //$ranking->now->$user->won_seasons  = $data->won_seasons;
            }

            #drop players who not participated at all in the ranking, workaround for strange bug
            #TODO: investigate on this quirk, if clause should not be neccesary (actually)!
            if ( $ranking->now->$user->played == 0 ) { unset($ranking->now->$user); }
        }
        
        # If tdates->from == tdates->to (only one weekend)
        # we drop the players which have _not_ participated
        # on this specific weekend. Else they would show up
        # getting the sleepy-points.
        if ( $this->tdates->from == $this->tdates->to ) {
            foreach ( $ranking->now as $username=>$info ) {
                # If the user has not participated the current
                # weekend: kill from $ranking object.
                if ( $info->played == 0 ) {
                    unset($ranking->now->$username);
                    unset($ranking->pre->$username);
                }
            }
        }

        # Extracting points to get rank
        $rank = (object)array("now"=>array());

        // Current rank
        foreach ( $ranking->now as $rec ) {
            array_push( $rank->now, round($rec->points,2) );
        }
        $rank->now = $this->_array_rank( $rank->now );

        # Previous rank (to calculate the trend), if requested
        if ( $this->calc_trend ) {
            $rank->pre = array();
            foreach ( $ranking->pre as $rec ) {
                array_push( $rank->pre, round($rec->points,2) );
            }
            $rank->pre = $this->_array_rank( $rank->pre );
        }

        # Array of the same order as $rank containing usernames
        $users = array();
        foreach ( $ranking->now as $user=>$x ) { array_push($users, $user); }

        # Looping in rank order
        $order = $rank->now; asort($order);


        $final         = new stdClass();
        $points_winner = NULL;

        $total_tournaments = $userdata->total;
        if ($type !== "eternal") {
            $max_points = $this->max_points * $total_tournaments * count( $this->cityObj );
        }
 
        foreach ( $order as $idx=>$trash ) {

            # Current user in loop (winner first)
            $user = $users[$idx];

            # Setting winner points, used to compute differences.
            if ( is_null($points_winner) ) { $points_winner = $ranking->now->$user->points; }
            
            if ($type === "eternal") { $max_points = round( $points_winner, 1 ); }

            # Appending data
            $final->$user = new stdClass();
            $final->$user->rank_now    = $rank->now[$idx];
            $final->$user->points_now  = $this->WTuser->number_format($ranking->now->$user->points,1);
            $final->$user->played_now  = $ranking->now->$user->played;
            $final->$user->points_relative = round($ranking->now->$user->points, 1) / $max_points;
            $final->$user->points_diff = $this->WTuser->number_format($points_winner
                                                 - $ranking->now->$user->points,1);

            if ( $d1d2 ) {
                if (is_null($ranking->now->$user->points_d1)) {
                    $final->$user->points_d1 = "-";
                    $final->$user->points_d2 = "-";
                } else {
                    $final->$user->points_d1 = $this->WTuser->number_format(
                        $ranking->now->$user->points_d1, 1);
                    $final->$user->points_d2 = $this->WTuser->number_format(
                        $ranking->now->$user->points_d2, 1);
                } 
            } else if ( $type === "eternal" ) {
                $final->$user->sd_ind       = $this->WTuser->number_format(
                    $ranking->now->$user->sd_ind, 1);
                $final->$user->points_max   = $this->WTuser->number_format(
                    $ranking->now->$user->points_max, 1);
                $final->$user->points_mean  = $this->WTuser->number_format(
                    $ranking->now->$user->points_mean, 1);
                $final->$user->won_weekends = $this->WTuser->number_format(
                    round(100*($ranking->now->$user->won_weekends / $final->$user->played_now),1),1);
                //$final->$user->won_seasons = $this->WTuser->number_format($ranking->now->$user->won_seasons);
            }

            if ( $this->calc_trend ) {
                $final->$user->rank_pre    = $rank->pre[$idx];
                $final->$user->trend = $rank->pre[$idx] - $rank->now[$idx];
            }


            # Replace username with "user display name"
            # and add userclass (for display) using the
            # method :meth:`generalclass.get_user_display_class_and_name`.
            $userObj = get_user_by( "login", $user );
            $final->$user->userID       = $userObj->ID;
            $tmp = $this->WTuser->get_user_display_class_and_name($userObj->ID, $userObj);
            $final->$user->display_name = $tmp->display_name;
            $final->$user->userclass    = $tmp->userclass;

            // Create edit button for administrators

            if ( ! is_array($this->cityObj) && $ntournaments == 1 ) {
                $final->$user->edit_button   = $this->_get_edit_button( $tmp->userclass, $userObj );
                $final->$user->detail_button = $this->_get_detail_button( $userObj );
            }

            # Loading additional information only for leaderboard
            if ($type === "leading") {
                $final->$user->avatar = get_wp_user_avatar($userObj->ID, 96);
                $final->$user->avatar_link = sprintf(bbp_get_user_profile_url($userObj->ID));
            }

            // Getting profile link
            $final->$user->profile_link = $this->WTuser->get_user_profile_link( $tmp );
        }

        unset($ranking);
        unset($rank);

        $this->ranking = new stdClass();
        $this->ranking->meta               = new stdClass();
        $this->ranking->meta->has_trends   = $this->calc_trend;
        $this->ranking->meta->ntournaments = $ntournaments;
        $this->ranking->meta->total_tournaments = $total_tournaments;
        $this->ranking->meta->max_points   = $max_points;
        $this->ranking->meta->older        = $this->tdates->older;
        $this->ranking->meta->newer        = $this->tdates->newer;
        $this->ranking->meta->from         = $this->WTuser->date_format($this->tdates->from);
        $this->ranking->meta->to           = $this->WTuser->date_format($this->tdates->to);

        // If only one city:
        if ( ! is_array($this->cityObj) ) {
            $this->ranking->meta->city = $this->cityObj->get("name");
        } else {
            $names = array(); foreach ( $this->cityObj as $rec ) { array_push($names, $rec->get("name")); }
            $this->ranking->meta->city = join(" ", $names); 
        }

        // The data
        $this->ranking->data               = $final;

        if ( is_plugin_active("polylang") ) {
            $this->ranking->meta->lang = pll_get_current_language();
        }

        # Write data to cache file
        if ( $this->cache ) {
            if ( is_writable(dirname($cache_file)) ) {
                file_put_contents($cache_file, serialize($this->ranking), LOCK_EX);
            }
        }
    }

    /**
     * Prints the content of $this->ranking (or $this->ranking->data),
     * just a development helper function!
     *
     * @param $data (bool)
     *   if ``false`` the whole object will be printed, if set to ``true``
     *   only the ``data`` is printed.
     */
    public function print_r($data = false) {
        print_r(($data) ? $this->ranking->data : $this->ranking );
        die("Exit in development method \"print_r\"");
    }

    /**
     * Public function which simply returns the internal object.
     *
     * @return Returns the structured object containing the data.
     */
    public function return_obj() {
        return($this->ranking);
    }

    /**
     * Returns the ranking prepared by prepare_ranking as JSON string.
     * This is what will be returned for the ajax requests.
     *
     * @return Returns JSON string containing data and meta information
     *   which is used by the :file:`js/wetterturnier.ranking.js` function
     *  ``show_ranking(..)`` to display the ranking table on the frontend.
     */
    public function return_json( ) {
        if ( is_null($this->ranking) ) {
            return json_encode(array("error"=>"Data not prepared, prepare_ranking not called?"));
        } else {
            $this->ranking->dict = $this->dict;
            return json_encode($this->ranking);
        }
    }

}
?>
