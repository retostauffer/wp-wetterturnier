<?php
/**
 * A helper class returning the ranking/points/bets from the last weekend.
 * This file (only the upper part at the moment) looks exactely like the
 * ASCII files before 2018. Some more details see class description.
 *
 * @file oldoutputclass.php
 * @author Reto Stauffer
 * @date January 9 2018
 * @brief Mimiking the old archive text files.
 */


/**
 * A helper class returning the ranking/points/bets from the last weekend.
 * This file (only the upper part at the moment) looks exactely like the
 * ASCII files before 2018 and can be accessed under the same URL's
 * using .htaccess redirects. The idea was that Moses comes back to life,
 * however, the last two weeks this didn't happen.
 * This class is used solely by oldarchive.php in the same directory.
 * 
 * Args:
 *    deadman (:obj:`str`): string, use login name of the user which
 *      provides the points for players not having participated. On 
 *      wetterturnier this user is known as "Sleepy" (default).
 *    points_max (:obj:`int`): Maximum number of points per weekend.
 *      Used to compute the 'relative points' gained of the players
 *      given the ranking settings. Default is 200 as on wetterturnier.de.
 */
class wetterturnier_rankingObject {

    /// Will contain a copy of the global $wpdb instance. Used as
    /// class-internal reference for database requests.
    private $wpdb;
    /// Attribute to store the cityObject.
    private $cityObj = null;
    /// Maximum number of points per weekend.
    private $points_max = 200;

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

    /**
     * Object constructor.
     */
    function __construct( $deadman = "Sleepy", $points_max = 200, $cache = true ) {

       global $wpdb; $this->wpdb = $wpdb;
       global $WTuser;
       ///global $polylang;
       ///$lang = pll_current_language();

       # Check if access is granted
       $this->WTuser     = $WTuser;
       $this->deadman    = $deadman;
       $this->points_max = $points_max;
       $this->cache      = $cache;

       $this->dict = new stdClass();
       $this->dict->previous     = __("Older", "wpwt");
       $this->dict->later        = __("Newer", "wpwt");
       $this->dict->points       = __("Points", "wpwt");
       $this->dict->trend        = __("+/-", "wpwt");
       $this->dict->played       = __("N", "wpwt");
       $this->dict->difference   = __("Diff", "wpwt");
       $this->dict->rank         = __("Rank", "wpwt");
       $this->dict->user         = __("User", "wpwt");
       $this->dict->points_max   = __("The maximum score (total) for the ranking is", "wpwt");

    }


    /* Setting cityObj argument within this class.
     *
     * Args:
     *      cityObj (:obj:`cityObj` or an :obj:`array`): request for only one
     *          specific city or multiple cities (then an array is needed).
     */
    function set_cities( $cityObj ) {
        $this->cityObj = $cityObj;
    }


    /* Store the date ranges for which the request should be made.
     *
     * Args:
     *    from (:obj:`int` or :obj:`stdClass`): Either a single integer
     *      for the first date (days since 1970-01-01) for the current rank,
     *      or an object. If it is an object we assume it contains four elements
     *      specifying 'from', 'to', 'from_prev', and 'to_prev'. Else all four
     *      iputs have to be given!
     *    to (:obj:`Null` or :obj:`int`): If input $from is an object this argument
     *      is simply ignored. Else should contain an integer with the last day
     *      (days since 1970-01-01) of the period for the current rank.
     *    from_prev (:obj:`Null` or :obj:`int`): If input $from is an object this argument
     *      is simply ignored. Else should contain an integer with the first day
     *      (days since 1970-01-01) of the period for the previouse rank. Used to
     *      compute the trend.
     *    to_prev (:obj:`Null` or :obj:`int`): If input $from is an object this argument
     *      is simply ignored. Else should contain an integer with the last day
     *      (days since 1970-01-01) of the period for the previouse rank. Used to
     *      compute the trend.
     */
    function set_tdates($from, $to = Null, $from_prev = Null, $to_prev = Null) {
        if ( ! is_object($from) ) {
            $this->tdates = (object) array("from"=>$from, "to"=>$to,
                                          "from_prev"=>$from_prev, "to_prev"=>$to_prev);
        } else { $this->tdates = $from; }

        $this->tdates->min = min($this->tdates->from,      $this->tdates->to,
                                 $this->tdates->from_prev, $this->tdates->to_prev);
        $this->tdates->max = max($this->tdates->from, $this->tdates->to,
                                 $this->tdates->from_prev, $this->tdates->to_prev);
    }


    /* Returns the data in a structured way. The 'data' are the points for each
     * specific city/tournament_date/user where the 'user' nesting level is not
     * requred (and therefore not returned) if input $deadman is set to true.
     *
     * Args:
     *      deadman (:obj:`bool`): If false (default) all points will
     *          be returned. Else (if true) only the deadman points will
     *          be returned. Uses the 'deadman' argument from this object.
     *          If the deadman user cannot be found: return null such that players
     *          which have not participated simply get 0 points.
     * Returns:
     * A stdClass object of the following form, here one example
     * with for only one city (cityID = 4) and only one tournament date (tdate).
     * If input argument $deadman is true, only the deadman user will be loaded.
     * >>> stdClass Object
     * >>>  (
     * >>>    [data] => stdClass Object
     * >>>      (
     * >>>        [ambot_lang] => stdClass Object
     * >>>          (
     * >>>            [city_4] => stdClass Object
     * >>>              (
     * >>>                [tdate_17830] => 158.60000610351562
     * >>>              )
     * >>>          )
     * >>>        [DWD-MOS-Mix] => stdClass Object
     * >>>          (
     * >>>            [city_4] => stdClass Object
     * >>>              (
     * >>>                [tdate_17830] => 152.8000030517578
     * >>>              )
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
     * >>>    [cityIDs] => Array
     * >>>        (
     * >>>            [0] => 4
     * >>>        )
     * >>>    [tdates] => Array
     * >>>        (
     * >>>            [0] => 17830
     * >>>        )
     * >>>  )
     */
    private function _get_data_object( $deadman = false ) {

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
            foreach ( $this->cityObj as $rec ) { array_push($tmp,sprintf("%d",$rec->get("ID"))); }
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
        array_push($sql, "SELECT b.cityID, b.tdate, " . $usercol . " SUM(b.points) AS points");
        array_push($sql, sprintf("FROM %susers AS u RIGHT OUTER JOIN", $this->wpdb->prefix));
        array_push($sql, sprintf("%swetterturnier_betstat AS b", $this->wpdb->prefix));
        array_push($sql, "ON u.ID=b.userID WHERE");
        array_push($sql, sprintf("%s AND %s %s", $where_city, $where_tdate, $where_user));
        array_push($sql, "GROUP BY u.ID, b.tdate;");

        #printf("\n%s\n", join("\n",$sql));
        $dbres = $this->wpdb->get_results(join( "\n", $sql));

        # If deadman is requested: create one stdClass object containing
        # the points for each tournament date, no need to add an extra
        # nesting level containing the username.
        if ( $deadman ) {
            $res = new stdClass();
            foreach ( $dbres as $rec ) {
                # City has, append if not yet defined.
                $chash = sprintf("city_%d",$rec->cityID);
                if ( ! property_exists($res,$chash) ) { $res->$chash = new stdClass(); }
                # Append tourmanet date to city
                $thash = sprintf("tdate_%d",$rec->tdate);
                $res->$chash->$thash = $rec->points;
            }
        } else {
            $res = (object)array("data"=>new stdClass(),
                    "users"=>array(),"cityIDs"=>array(), "tdates"=>array());
            foreach ( $dbres as $rec ) {
                # Append cityID's and tdates
                if ( ! in_array($rec->user_login, $res->users) ) { array_push($res->users,$rec->user_login); }
                if ( ! in_array($rec->cityID,$res->cityIDs) ) { array_push($res->cityIDs,$rec->cityID); }
                if ( ! in_array($rec->tdate, $res->tdates ) ) { array_push($res->tdates, $rec->tdate ); }
                # User hash
                $uhash = $rec->user_login;
                if ( ! property_exists($res->data,$uhash) ) { $res->data->$uhash = new stdClass(); }
                # City has, append if not yet defined.
                $chash = sprintf("city_%d",$rec->cityID);
                if ( ! property_exists($res->data->$uhash,$chash) ) { $res->data->$uhash->$chash = new stdClass(); }
                # Append tourmanet date to city
                $thash = sprintf("tdate_%d",$rec->tdate);
                $res->data->$uhash->$chash->$thash = $rec->points;
            }
        }

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
     * Args:
     *      type (:obj:`str`): String with user type (e.g., user or mitteltip)
     *      Obj (...): Either a wordpress user Object or a wetterturnier
     *          :class:`wetterturnier_stationObject` for the observations.
     * Returns:
     *      str: Returns a string with a html element to place the edit button or
     *      an empty string if no editbuttion is needed (or not allowed as the user
     *      is no admin).
     */
    private function _get_edit_button( $type, $Obj ) {

       // If no admin: return
       if ( ! current_user_can('manage_options') ) { return(""); }
       // If mitteltip: return
       if ( $type === "mitteltip" ) { return(""); }
       // If this is an observation entry (use $type = "obs")
       if ( $type === "obs" ) {
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


    /* Creates the admin link to modify the current user/station.
     *
     * Args:
     *      Obj (...): Either a wordpress user Object.
     * Returns:
     *      str: Returns a string with a html element to place the edit button or
     *      an empty string if no editbuttion is needed (or not allowed as the user
     *      is no admin).
     */
    private function _get_detail_button( $userObj ) {
        return sprintf("<span class='button small detail' userid='%d' "
                      ."cityid='%d' tdate='%d'></span>",
                      $userObj->ID, $this->cityObj->get("ID"), $this->tdates->max);
    }


    /** Returns the file name for the cache file (only used if cache is set
     * to true, see __construct method).
     *
     * Returns the absolute path to the cache file.
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
        if ( $this->tdates->previous ) {
            $tdate_hash  = sprintf("%d-%d", $this->tdates->previous, $this->tdates->max);
        } else if ( ! $tdate->min == $tdate->max ) {
            $tdate_hash  = sprintf("%d-%d", $this->tdates->min, $this->tdates->max);
        } else {
            $tdate_hash  = sprintf("%d", $this->tdates->max);
        }

        # Return cache file
        return(sprintf("%scache/wt-ranking_%s_%s.json", plugin_dir_path(__FILE__),
                       $tdate_hash, $city_hash));
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
     * Returns:
     *  No return! Stores the ranking object on the parent object itself.
     *  There are different ouptut methods to display/return the data.
     *
     * .. todo:: Explain caching.
     */
    function prepare_ranking() {

        if ( is_null($this->tdates) || is_null($this->cityObj) ) {
            //echo "Sorry, cannot prepare ranking, tdate or cityObject not set!";
            return null;
        }

        if ( is_numeric($this->tdates->max) ) {
            ob_start();
            $closed = $this->WTuser->check_view_is_closed($this->tdates->max);
            ob_end_clean();
            if ( $closed ) { die("No access! Go away, please! :)"); }
        }

        $cityID = $this->cityObj->get("ID");
        $prefix = $this->wpdb->prefix;

        # Loading previous tournament date, needed to get the position changes
        # from the last to the current tournament.
        $this->tdates->previous = $this->_get_previous_tournament_date();
        $this->tdates->later    = $this->_get_later_tournament_date();

        // If caching is enabled: check if we can load the
        // data from disc to ont re-calculate the ranking again.
        if ( $this->cache ) {
            $cache_file  = $this->_get_cache_file_name();
            if ( file_exists($cache_file) ) {
                # If newer than 10 minutes: load file
                if ( time() - filemtime($cache_file) <= 600 ) {
                    $this->ranking = unserialize(file_get_contents($cache_file));
                    return(false);
                }
            }
        }

        # Loading deadman points. Whenever a player did not participate he/she
        # will get these points. May return "0" if the deadman is not defined.
        $deadman = $this->_get_data_object(true);

        # Loading user data
        $userdata = $this->_get_data_object(false);

        $ranking = (object)array("pre"=>new stdClass(), "now"=>new stdClass());

        # Looping over cities
        foreach ( $userdata->data as $user=>$data ) {

            # Append user to $ranking object if not yet existing
            if ( ! property_exists($ranking->pre,$user) ) {
                $ranking->pre->$user = (object)array("played"=>0,"points"=>0);
                $ranking->now->$user = (object)array("played"=>0,"points"=>0);
            }
            foreach ( $userdata->cityIDs as $cityID ) {
                $chash = sprintf("city_%d",$cityID);
                foreach ( $userdata->tdates as $tdate ) {
                    $thash = sprintf("tdate_%d",$tdate);

                    # Default: 0 points
                    $points = 0;
                    # And not participated (default)
                    $played = 0;

                    # If user got points: use user points 
                    if ( property_exists($data->$chash,$thash) ) {
                        $points = $data->$chash->$thash;
                        $played = 1;
                    # Else check if deadman exists and has points for this
                    # specific city ($chash) and tournament date ($thash).
                    } else if ( $deadman ) {
                        if ( property_exists($deadman,$chash) ) {
                            if ( property_exists($deadman->$chash,$thash) ) {
                                $points = $deadman->$chash->$thash;
                            }
                        }
                    }

                    # Adding points
                    if ( ($tdate >= $this->tdates->from_prev) && ($tdate <= $this->tdates->to_prev) ) {
                        $ranking->pre->$user->points += $points;
                        $ranking->pre->$user->played += $played;
                    }
                    if ( ($tdate >= $this->tdates->from) && ($tdate <= $this->tdates->to) ) {
                        $ranking->now->$user->points += $points;
                        $ranking->now->$user->played += $played;
                    }

                }
            }
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
        function array_rank( $in ) {
            # Keep input array "x" and replace values with rank.
            # This preserves the order. Working on a copy called $x
            # to set the ranks.
            $x = $in; arsort($x); 
            # Initival values
            $rank       = 0;
            $hiddenrank = 0;
            $hold = null;
            foreach ( $x as $key=>$val ) {
                # Always increade hidden rank
                $hiddenrank += 1;
                # If current value is lower than previous:
                # set new hold, and set rank to hiddenrank.
                if ( is_null($hold) || $val < $hold ) {
                    $rank = $hiddenrank; $hold = $val;
                }
                # Set rank $rank for $in[$key]
                $in[$key] = $rank;
            }
            return $in;
        }

        # Extracting points to get rank
        $rank = (object)array("pre"=>array(),"now"=>array());
        foreach ( $ranking->pre as $rec ) {
            array_push( $rank->pre, round($rec->points,2) );
        }
        $rank->pre = array_rank( $rank->pre );

        foreach ( $ranking->now as $rec ) {
            array_push( $rank->now, round($rec->points,2) );
        }
        $rank->now = array_rank( $rank->now );

        # Array of the same order as $rank containing usernames
        $users = array();
        foreach ( $ranking->pre as $user=>$x ) { array_push($users,$user); }

        # Looping in rank order
        $order = $rank->now; asort($order);


        # Create final object, adding ranks and tendencies.
        if ( $this->tdates->previous ) {
            $ntournaments = count($userdata->tdates) - 1;
        } else {
            $ntournaments = count($userdata->tdates);
        }
        $points_max = $this->points_max * $ntournaments;
        $final = new stdClass();
        $points_winner = NULL;
        foreach ( $order as $idx=>$trash ) {

            # Current user in loop (winner first)
            $user = $users[$idx];

            # Setting winner points, used to compute differences.
            if ( is_null($points_winner) ) { $points_winner = $ranking->now->$user->points; }

            # Appending data
            $final->$user = new stdClass();
            $final->$user->rank_pre    = $rank->pre[$idx];
            $final->$user->rank_now    = $rank->now[$idx];
            #$final->$user->points_pre = $ranking->pre->$user->points;
            $final->$user->points_now  = $this->WTuser->number_format($ranking->now->$user->points,1);
            $final->$user->points_diff = $this->WTuser->number_format($points_winner - $ranking->now->$user->points,1);
            #$final->$user->played_pre = $ranking->pre->$user->played;
            $final->$user->played_now  = $ranking->now->$user->played;
            $final->$user->points_relative = $ranking->now->$user->points / $points_max;
            $final->$user->trend = $rank->pre[$idx] - $rank->now[$idx];

            # Replace username with "user display name"
            # and add userclass (for display) using the
            # method :meth:`generalclass.get_user_display_class_and_name`.
            $userObj = get_user_by( "login", $user );
            $final->$user->userID       = $userObj->ID;
            $tmp = $this->WTuser->get_user_display_class_and_name($userObj->ID,
                                $userObj);
            $final->$user->display_name = $tmp->display_name;
            $final->$user->userclass    = $tmp->userclass;

            // Create edit button for administrators

            if ( ! is_array($this->cityObj) && $ntournaments == 1 ) {
                $final->$user->edit_button = $this->_get_edit_button( $tmp->userclass, $userObj );
            }
            $final->$user->detail_button = $this->_get_detail_button( $userObj );

            // Getting profile link
            $final->$user->profile_link = $this->WTuser->get_user_profile_link( $tmp );
        }

        unset($ranking);
        unset($rank);

        $this->ranking = new stdClass();
        $this->ranking->meta               = new stdClass();
        $this->ranking->meta->points_max   = $points_max;
        $this->ranking->meta->ntournaments = $ntournaments;
        $this->ranking->meta->previous     = $this->tdates->previous;
        $this->ranking->meta->later        = $this->tdates->later;
        $this->ranking->data               = $final;

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
     */
    public function print_r($data = false) {
        print_r(($data) ? $this->ranking->data : $this->ranking );
        die("Exit in development method \"print_r\"");
    }

    public function return_obj() {
        return($this->ranking);
    }

    /**
     * Returns the ranking prepared by prepare_ranking as json array.
     */
    function return_json( ) {
        if ( is_null($this->ranking) ) {
            return json_encode(array("error"=>"Data not prepared, prepare_ranking not called?"));
        } else {
            $this->ranking->dict = $this->dict;
            return json_encode($this->ranking);
        }
    }

}



?>
