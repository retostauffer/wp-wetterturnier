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
	/// Attribute to store the tournament date.
	private $tdate = null;
	/// Attribute to store the cityObject.
	private $cityObj = null;
    /// Maximum number of points per weekend.
    private $points_max = 200;

    private $WTuser;

    # Used to store the ranking from prepare_ranking
    private $ranking = null;

    # Name of the deadman.
    private $deadman;

    /**
     * Object constructor.
     */
    function __construct( $deadman = "Sleepy", $points_max = 200 ) {

       global $wpdb; $this->wpdb = $wpdb;

       # Check if access is granted
       global $WTuser;
       $this->WTuser     = $WTuser;
       $this->deadman    = $deadman;
       $this->points_max = $points_max;
       echo "initialized\n";

    }


    /* Setting cityObj argument within this class.
     *
     * Args:
     *    tdate (:obj:`array` or :class:`wetterturnier_cityObj`): Either a
     *      :class:`wetterturnier_cityObject` object or an array containing one or several
     *      of these objects. Whether to load ranking for one or several cities.
     */
    function set_cities( $cityObj ) {
        echo "Setting city\n";
        $this->cityObj = $cityObj;
    }


    /* Setting tdate argument within this class.
     *
     * Args:
     *    tdate (:obj:`array` or :obj:`int`): Either a single numeric (prepare ranking
     *      for one single tournament date) or an array (for a time period, uses min/max).
     */
    function set_tdate( $tdate ) {
        echo "Setting tdate\n";
        $this->tdate = $this->_get_tdate_object_( $tdate );
    }


    /* Prepare tdate argument within this class.
     *
     * Args:
     *    tdate (:obj:`array` or :obj:`int`): Either a single numeric (prepare ranking
     *      for one single tournament date) or an array (for a time period, uses min/max).
     */
    private function _get_tdate_object_( $tdate ) {
        if ( is_numeric($tdate) ) {
            $tdate = (object)array("min"=>$tdate,"max"=>$tdate);
        } else {
            $tdate = (object)array("min"=>min($tdate),"max"=>max($tdate));
        }
        return $tdate;
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
     *      Stuff.
     *
     * .. todo:: Explain return.
     */
    private function _get_data_object_( $deadman = false ) {

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
        if ( $this->tdate->previous ) {
            $where_tdate = sprintf("b.tdate between %d and %d",$this->tdate->previous,$this->tdate->max);
        } else if ( ! $tdate->min == $tdate->max ) {
            $where_tdate = sprintf("b.tdate between %d and %d",$this->tdate->min,$this->tdate->max);
        } else {
            $where_tdate = sprintf("b.tdate = %d",$this->tdate->max);
        }

        # Just no need to load user_login for a known user!
        $usercol = ($deadman) ? "" : "u.user_login, ";

        # Create SQL command
        $sql = array();
        array_push($sql,"SELECT b.cityID, b.tdate, ".$usercol." SUM(b.points) AS points");
        array_push($sql,sprintf("FROM %susers AS u RIGHT OUTER JOIN",$this->wpdb->prefix));
        array_push($sql,sprintf("%swetterturnier_betstat AS b",$this->wpdb->prefix));
        array_push($sql,"ON u.ID=b.userID WHERE");
        array_push($sql,sprintf("%s AND %s %s",$where_city,$where_tdate,$where_user));
        array_push($sql,"GROUP BY u.ID, b.tdate;");

        # printf("\n%s\n", join("\n",$sql));
        $dbres = $this->wpdb->get_results( join( "\n", $sql ) );

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
    private function _get_previous_tournament_date_( ) {
        # Find tdate before $tdate->min for ranking
        $sql = sprintf("SELECT distinct(tdate) from %swetterturnier_betstat "
            ." WHERE tdate < %d ORDER BY tdate DESC LIMIT 1;",$this->wpdb->prefix,$this->tdate->min);
        $res = $this->wpdb->get_row($sql);
        if ( $this->wpdb->num_rows == 0 ) {
            return null;
        }
        return $res->tdate;
    }

    /* Prepares a ranking object based on the class attributes 'tdate' and
     * 'cityObj' (also allowed for time periods and multiple cities at the
     * same time). This function loads the data from the database and creates
     * a structured object containing the user points and ranks.
     * Given a valid deadman username the points will be filled with the
     * points of deadman (known as Sleepy on wetterturnier.de), if not
     * 0 points will be given for not participating at all.
     *
     * Returns:
     *  No return! Stores the ranking object on the parent object itself.
     *  There are different ouptut methods to display/return the data.
     */
    function prepare_ranking() {
        echo "Prepare now\n";

        if ( is_null($this->tdate) || is_null($this->cityObj) ) {
            echo "Sorry, cannot prepare ranking, tdate or cityObject not set!";
            return null;
        }

        if ( is_numeric($this->tdate) ) {
            ob_start();
            $closed = $this->WTuser->check_view_is_closed( $this->tdate );
            ob_end_clean();
            if ( $closed ) { die("No access! Go away, please! :)"); }
        }


        $tdate  = $this->tdate;
        $cityID = $this->cityObj->get("ID");
        $prefix = $this->wpdb->prefix;

        # Loading previous tournament date, needed to get the position changes
        # from the last to the current tournament.
        $this->tdate->previous = $this->_get_previous_tournament_date_();

        # Loading deadman points. Whenever a player did not participate he/she
        # will get these points. May return "0" if the deadman is not defined.
        $deadman = $this->_get_data_object_( true );

        # Loading user data
        $userdata = $this->_get_data_object_( false );

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
                    if ( $tdate == $this->tdate->previous ) {
                        $ranking->pre->$user->points += $points;
                        $ranking->pre->$user->played += $played;
                    } else if ( $tdate == $this->tdate->max ) {
                        $ranking->now->$user->points += $points;
                        $ranking->now->$user->played += $played;
                    } else {
                        $ranking->pre->$user->points += $points;
                        $ranking->now->$user->points += $points;
                        $ranking->pre->$user->played += $played;
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
        $ntournaments = count($userdata->tdates);
        $points_max = $this->points_max * $ntournaments;
        $final = new stdClass();
        foreach ( $order as $idx=>$trash ) {
            $user = $users[$idx];
            $final->$user = new stdClass();
            $final->$user->rank_pre  = $rank->pre[$idx];
            $final->$user->rank_now  = $rank->now[$idx];
            $final->$user->points_pre = $ranking->pre->$user->points;
            $final->$user->points_now = $ranking->now->$user->points;
            $final->$user->played_pre = $ranking->pre->$user->played;
            $final->$user->played_now = $ranking->now->$user->played;
            $final->$user->points_relative = $ranking->now->$user->points / $points_max;
            $final->$user->trend = $rank->now[$idx] - $rank->pre[$idx];
            $final->$ntournaments = $ntournaments;
        }

        unset($ranking);
        unset($rank);

        print_r($final);
        $this->ranking = $final;
    }

    /**
     * Returns the ranking prepared by prepare_ranking as json array.
     */
    function return_json( ) {
        if ( is_null($this->ranking) ) {
            return json_encode(array("error"=>"Data not prepared, prepare_ranking not called?"));
        } else {
            return json_encode( $this->ranking );
        }
    }

}



?>
