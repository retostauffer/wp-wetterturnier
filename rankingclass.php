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
 */
class wetterturnier_rankingObject {

    /// Will contain a copy of the global $wpdb instance. Used as
    /// class-internal reference for database requests.
    private $wpdb;
	/// Attribute to store the tournament date.
	private $tdate = null;
	/// Attribute to store the cityObject.
	private $cityObj = null;
    private $WTuser;

    # Name of the deadman.
    private $deadman;

    /**
     * Object constructor.
     *
     * No input arguments. Cities/tdates are set via set_cities and set_tdates.
     */
    function __construct( $deadman = "Sleepy" ) {

       global $wpdb; $this->wpdb = $wpdb;

       # Check if access is granted
       global $WTuser;
       $this->WTuser = $WTuser;
       $this->deadman = $deadman;
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

        printf("\n%s\n", join("\n",$sql));

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

    function prepare_data() {
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
        foreach ( $userdata->cityIDs as $cityID ) {
            $chash = sprintf("city_%d",$cityID);
            foreach ( $userdata->tdates as $tdate ) {
                $thash = sprintf("city_%d",$cityID);

                die("looping over users here");
                # Append points
                if        ( $rec->tdate == $tdate->min ) {
                    $ranking->pre->$user += $rec->points;
                } else if ( $rec->tdate == $tdate->max ) {
                    $ranking->now->$user += $rec->points;
                } else {
                    $ranking->pre->$user += $rec->points;
                    $ranking->now->$user += $rec->points;
                }
            }
        }

        print_r($ranking);

    }

    /**
     * Helper function. Returns the output format (as string!), someting
     * like '%.3f ' or '  %8d '.
     *
     * @param string $paramName Name of the pararameter. Used to return
     * the correct format from a lookup-procedure.
     *
	 * @return Returns a string of type '%Xs' for a string of length X
	 *where X depends on the input $paramName.
	 */
	private function _get_param_format_( $paramName ) {
        # Note that there is no space after Wn, looks horrible, but is as it is.
		if ( in_array( $paramName, array("name") ) )
		{ $fmt = "%-25s"; }	
		else if ( in_array( $paramName, array( "TTm","TTn","TTd","RR") ) )
		{ $fmt = " %5s"; }	
		else if ( in_array( $paramName, array( "N" ) ) )
		{ $fmt = " %1s"; }	
		else if ( in_array( $paramName, array( "Wn" ) ) )
		{ $fmt = "%2s"; }
		else if ( in_array( $paramName, array( "ff","fx","Wv" ) ) )
		{ $fmt = " %2s"; }	
		else if ( in_array( $paramName, array( "Sd" ) ) )
		{ $fmt = " %3s"; }	
		else if ( in_array( $paramName, array( "dd" ) ) )
		{ $fmt = " %4s"; }	
		else if ( in_array( $paramName, array( "PPP" ) ) )
		{ $fmt = " %7s"; }	
		else { $fmt = " %10s"; }
		return( $fmt );
	}

    /**
     * Helper function to cut a string to a specific length if it is longer than $len.
     *
	 * @param $str. String uf unknown length.
     *
	 * @param $len. Integer, length to which the string should be cut if longer than $len.
     *
	 * @return Returns string cut to length $len.
	 */
	private function _str_cut_( $str, $len ) {
		if ( strlen($str) <= $len ) { return( $str ); }
		return( substr( $str, 0, $len ) );
	}

   /**
    * Heper function to always show float point numbers
    * in the same format, with a ".".
    *
    * @param $value. Float value.
    *
    * @param $decimals. Integer, deault 1, number of digits after comma.
    *
    * @return Returns string using number_format with fixed format internally.
    */
   private function show_number( $value, $decimals = 1 ) {
      return number_format( $value, $decimals, ".", "" );
   }


	/**
	 * This is the main function which proces the output.
	 * No extra inputs needed, all we need was already processed
	 * in the class __construct method.
     * Note that I've removed two Umlaute (non utf-8 characters). They break
     * my sphinx! And I am quite sure no one searches for non utf-8 to get
     * the data position.
     */
	public function show() {

		global $WTuser;

		// ------------------------------------------------------------------
		// File header dingsda
		// ------------------------------------------------------------------
        printf("Innsbrucker Wetterprognoseturnier %s\n\n\n"
               ."Eingetroffene Werte und abgegebene Prognosen:\n\n",
                date("d.m.Y",$this->tdate*86400));

		// Show stations and their values for day 1
		for ( $day=1; $day <= $this->days; $day++ ) {

			// ---------------------------------------------------------------
			// SHOW STATION OBSERVATIONS
			// ---------------------------------------------------------------

			// Show header
			printf("%s:\n",($day==1) ? "Samstag" : "Sonntag" );
			printf( $this->_get_param_format_("name"), "Name");
			foreach ( $this->cityObj->getParams() as $paramObj ) {
				printf( $this->_get_param_format_($paramObj->get("paramName")),
						  $paramObj->get("paramName") );
			}; print "\n";
			printf("%s\n",str_repeat("_",80));

			// Show observations
			$obs = $WTuser->get_obs_values($this->cityObj->get("ID"),$this->tdate+1,false);
			foreach ( $this->cityObj->stations() as $stnObj ) {

				// Station name
				printf( $this->_get_param_format_("name"), $this->_str_cut_($stnObj->get("name"),25) );

				// Getting data from $obs stdClass object
				$hash = sprintf("wmo_%d",(int)$stnObj->get("wmo"));
				if ( ! property_exists($obs->data,$hash) ) { continue; }
				$data = $obs->data->$hash;

				foreach ( $stnObj->getParams() as $paramObj ) {
					if ( $paramObj->isParameterActive( (int)$this->cityObj->get("ID") ) ) {
						$hash = sprintf("pid_%d",(int)$paramObj->get("paramID"));
						if ( ! property_exists($data,$hash) ) {
							$val = "n";
						} else {
							$val = $this->show_number( $data->$hash->value, (int)$paramObj->get("decimals") );
						}
					} else {	$val = "n"; }
					printf( $this->_get_param_format_($paramObj->get("paramName")), $val );
				}; print "\n";
			}; print "\n"; # End of observations, line break

			// ---------------------------------------------------------------
			// SHOW BETS
			// ---------------------------------------------------------------
			$bets = $WTuser->get_bet_values( (int)$this->cityObj->get("ID"),
							$this->tdate, $day, false );

			foreach ( $bets->data as $rec ) {
				printf( $this->_get_param_format_("name"),
               $this->_str_cut_( preg_replace("/^GRP_/","",$rec->user_login),25 ) );
				foreach ( $stnObj->getParams() as $paramObj ) {
					if ( $paramObj->isParameterActive( (int)$this->cityObj->get("ID") ) ) {
						$hash = sprintf("pid_%d",(int)$paramObj->get("paramID"));
						if ( ! property_exists($rec,$hash) ) {
							$val = "n";
						} else {
							$val = $this->show_number( $rec->$hash->value, (int)$paramObj->get("decimals") );
						}
					} else {	$val = "n"; }
					printf( $this->_get_param_format_($paramObj->get("paramName")), $val );
				}; print "\n"; 
			}

		}; print "\n";

		// ----------------------------------------------------------------
		// ----------------------------------------------------------------
		printf("Wertung der Prognose vom %s:\n",date("d.m.Y",$this->tdate*86400));

		printf("%2s. %-25s %6s %5s %5s\n","Pl","Name","Punkte","Tag1","Tag2");
		printf("%s\n",str_repeat("_",49));

		$data = $WTuser->get_ranking_data( $this->cityObj, (int)$this->tdate );
		$rank = 0;
		$keep_points = NULL;
		$stats = array( "N" => 0, "points" => 0.0, "Sleepy" => NULL );
		foreach ( $data->data as $rec ) {
			if ( is_null($keep_points) ) {
				$rank +=1; $keep_points = round($rec->points,2);
			} else if ( round($rec->points,2) < $keep_points ) {
				$rank +=1; $keep_points = round($rec->points,2);
			}
			// Show output
			printf("%2d. %-25s %5s (%5s/%5s)\n", $rank,
				$this->_str_cut_( preg_replace("/^GRP_/","",$rec->user_login), 25),
				$this->show_number($rec->points,1),
            $this->show_number($rec->points_d1,1),
            $this->show_number($rec->points_d2));

			// Stats
			if ( strcmp($rec->user_login,"Sleepy") === 0 ) {
				$stats["Sleepy"] = $rec->points;
			} else {
				$stats["N"] += 1; $stats["points"] += $rec->points;
			}
		}; print "\n";

		// Mean points
		
		if ( $stats["N"] > 0 ) {
      	printf("Die durchschnittliche Punktzahl betragt:    %5s Punkte.\n"
      	      ."Wertung fur nicht teilnehmende Mitspieler:  %5s Punkte.\n\n",
      	      $this->show_number($stats["points"]/$stats["N"]),
               $this->show_number($stats["Sleepy"]));
		}

	}

}



?>
