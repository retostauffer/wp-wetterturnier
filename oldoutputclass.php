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
class wetterturnier_oldoutputObject {

    /// Will contain a copy of the global $wpdb instance. Used as
    /// class-internal reference for database requests.
    private $wpdb;
	/// Attribute to store the tournament date.
	private $tdate;
	/// Attribute to store the cityObject.
	private $cityObj;
	/// Attribute to store the number of bet days.
	private $days;

    /**
     * Object constructor.
     *
     * @param object $cityObj Object of class @ref wetterturnier_cityObject for which
     * the data should be returned.
     *
     * @param integer $tdate Tournament date as days since 1970-01-01 representation.
     *
     * @param integer $days Number of days, default is 2, the current setting.
     */
    function __construct( $cityObj, $tdate, $days = 2 ) {

       global $wpdb; $this->wpdb = $wpdb;

     	# Convert to tdate, days since 1970-01-01
     	$this->tdate   = $tdate;
     	$this->cityObj = $cityObj;
     	$this->days    = $days;

       # Check if access is granted
       global $WTuser;
       ob_start();
       $closed = $WTuser->check_view_is_closed( $this->tdate );
       ob_end_clean();
       if ( $closed ) { die("No access! Go away, please! :)"); }

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
