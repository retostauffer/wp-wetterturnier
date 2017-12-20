<?php
// ------------------------------------------------------------------
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief Wetterturnier betclass to read/write/manipulate the bets
///   placed by our users. $WTbetclass will be set up in 
///   @file wp-wetterturnier.php and used in different scripts.
///
/// @details This is ...
// ------------------------------------------------------------------
class wetterturnier_betclass
{
   // --------------------------------------------------------------
   /// Helper function which prints a string of the form
   /// "AUTOSUBMIT ERRORCODE X" and dies. This is used by the
   /// user/autosubmit/autosubmit.php script to return an error
   /// code value which will be parsed and evaluated by the autosubmit
   /// python package to identify whether there were erros during 
   /// submission or not.
   /// @param $code. integer, error code as numeric value.
   /// @return no return. Print's a string containing the error code
   ///   $code and dies.
   // --------------------------------------------------------------
   function error( $code ) {
      printf("\nAUTOSUBMIT ERRORCODE %d\n",$code);
      die($code);
   }
   
   
   // ---------------------------------------------------------------
   /// @details For those automated systems using the old submission string we have
   /// to parse the data and bring them into the new and more general form.
   /// If the $data submitted by the user contain `aa' or `bb' then we have
   /// to call the @ref convert_old_post_data method, else the method
   /// @ref parse_post_data will be used.
   ///
   /// @params $data. stdClass object containing the submitted data.
   /// @return stdClass object with the parsed data.
   /// @see convert_old_post_data
   /// @see parse_post_data
   // ---------------------------------------------------------------
   function parse_parameters( $data ) {
   
      $msg = "Parsing POST parameters";
      printf("%s\n",strtoupper($msg));
      printf("%s\n",str_repeat("=",strlen($msg)));
   
      // Convert array to stdClass
      $data = (object) $data;
   
      // Checking keys. If contains 'aa' (user) and 'bb' (password)
      // this is a call to the old parser. Convert first. 
      if ( property_exists($data,'aa') && property_exists($data,'bb') ) {
         $data = $this->convert_old_post_data( $data );
      }
   
      // Parsing data with new config parser
      $res = $this->parse_post_data( $data );
   
      // Return result
      return( $res );
   
   }
   
   
   // ---------------------------------------------------------------
   /// @details Parsing bets/submitted forecast data which were
   /// submitted in the old-style format as used before the new
   /// wetterturnier software. The different parameters are coded
   /// using `aa' for the username, or `ee' for wind direction day 1
   /// which is (i) not very general and (ii) hard to interpret.
   /// This method converts the input names (e.g., `aa' or `bb') to
   /// what will be expected by @ref parse_post data in the new
   /// wetterturnier software. 
   ///
   /// @param $data. stdClass object containing the submitted
   ///   parameter/value forecast pairs in the old format.
   /// @return stdClass object with the renamed input arguments. This
   ///   object can be put into @ref parse_post_data to check and
   ///   process the user input data.
   /// @see parse_post_data
   // -------------------------------------------------------------------
   function convert_old_post_data( $data ) {
   
      printf("%s\n","Got old post format. Convert first.");
   
      // Lookup table to translate the 'old-school' inputs
      // into the new format.
      $lookup = array( "stadt" => "city",     //i
                       "aa"    => "user",     //reto
                       "bb"    => "password", //wetter
                       "cc"    => "N_1",      //8
                       "dd"    => "N_2",      //6
                       "ww"    => "Sd_1",     //4
                       "xx"    => "Sd_2",     //17
                       "ee"    => "dd_1",     //250
                       "ff"    => "dd_2",     //260
                       "gg"    => "ff_1",     //21
                       "hh"    => "ff_2",     //20
                       "yy"    => "fx_1",     //44
                       "zz"    => "fx_2",     //37
                       "ii"    => "Wv_1",     //6
                       "jj"    => "Wv_2",     //8
                       "kk"    => "Wn_1",     //6
                       "ll"    => "Wn_2",     //8
                       "mm"    => "PPP_1",    //998.9
                       "nn"    => "PPP_2",    //1003.7
                       "oo"    => "TTm_1",    //13.6
                       "pp"    => "TTm_2",    //5.6
                       "qq"    => "TTn_1",    //6.2
                       "rr"    => "TTn_2",    //2.3
                       "ss"    => "TTd_1",    //9.8
                       "tt"    => "TTd_2",    //-0.7
                       "uu"    => "RR_1",     //4.6
                       "vv"    => "RR_2" );   //2.9

      // Rewrite station to hash (old-school format
      // was the i/b/w/z/l, new is either hash or
      // full name.
      $cityhash = array("i" => "IBK",
                        "b" => "BER",
                        "w" => "VIE",
                        "z" => "ZUR",
                        "l" => "LEI");
   
   
      // Rewrite object to new format.
      $res = new stdClass();
      foreach ( $data as $key => $val ) {
         // Key not in lookup: use old key.
         if ( ! in_array($key,array_keys($lookup)) ) {
            $res->$key          = $val;
         // Translate with lookup
         } else {
            $res->$lookup[$key] = $val;
         }
      }

      // Rewrite city (old style was to identify the city
      // by one single character) to city hash as used in
      // the database. Reason: more flexible. Think of 
      // having a second city starting with "i" like "Innsbruck":
      // a single "i" as identifier is not unique enough.
      // If array_search returns Fals (boolean): not found.
      if ( is_bool( array_search(strtolower($res->city),array_keys($cityhash),true) ) ) {
         printf("ERROR: cannot find city hash for city identified by \"%s\". Stop.",$res->city);
         error(12);
      }
      $res->city = $cityhash[strtolower($res->city)];
   
      return( $res );
   
   }
   
   // ---------------------------------------------------------------
   /// @details Translates active parameters into readable parameter strings
   ///
   /// @params $paramconfig, json string containing the parameter
   ///   configuration.
   /// @return returns an array containing the readable parameter strings.
   // ---------------------------------------------------------------
   function get_paramconfig_string( $cityObj ) {
      global $WTuser;
      $res = array();
      foreach ( $cityObj->get('paramconfig') as $paramID ) {
         array_push( $res,
            strtoupper($WTuser->get_param_by_ID($paramID)->paramName) );
      }
      return( $res );
   }
   
   // ---------------------------------------------------------------
   /// @details Parsing post object 
   /// Using function for frontend and autosubmit. If $autosubmit is true,
   /// there will be the autosubmit behaviour. Else the frontend behaviour.
   ///
   /// @param $data stdClass object containing the submitted (and
   /// pre-processed) user data.
   /// @param $autosubmit. Boolean, default is `true`. If `true`
   ///   some more output will be generated and returned when using
   ///   the python package (to be able to trace back errors or see
   ///   whether it was working or not).
   // ---------------------------------------------------------------
   function parse_post_data( $data, $autosubmit=true ) {

      global $WTuser;
      $ndays = (int)$WTuser->options->wetterturnier_betdays;
   
      if ( $autosubmit ) { printf("%s\n","Parsing parameters."); }

      // Loading proper city object
      if ( $autosubmit ) {
         $cityObj = new wetterturnier_cityObject( $data->city );
      } elseif ( property_exists($data,"cityID") ) {
         $cityObj = new wetterturnier_cityObject( $data->cityID );
      } else {
         $cityObj = new wetterturnier_cityObject( );
      }
      $cityObj->paramconfig_string = $this->get_paramconfig_string( $cityObj );
   
      // Convert city name to city ID when data coming from autosubmit.
      if ( $autosubmit ) {
         printf("Found city %s (%s,ID %d) for input %s\n",
                        $cityObj->get('name'),$cityObj->get('hash'),$cityObj->get('ID'),$data->city);
      }
   
      // Create new stdCalss to store the data.
      // Note that each parameter:value pair has parameter name of
      // type <NAME>_<DAY> where <NAME> specifies the parameter,
      // <DAY> the forecast day. If the tournament is on a Friday
      // <DAY>=1 is Saturday, <DAY>=2 is Sunday.
      // [NOTE] if syntax is wrong, day is bigger than allowed
      // $ndays, or paramter name not found in database the
      // value will be ignored!
      $res = new stdClass();
      $res->cityObj    = $cityObj;   # store city object
      $res->ignored    = array(); # to store ignore cases
      $res->checknotes = array(); # to store value check messages
   
      // There is an option which defines for how many days forecasts
      // have to be submitted. Store them as betdays.
      $res->betdays = get_option("wetterturnier_betdays");
      
      // Define necessary keys: if not exist: stop.
      if ( $autosubmit ) {
         $keys_needed = array("user","password");
         foreach ( $keys_needed as $rec ) {
            if ( ! property_exists($data,$rec) ) {
               printf("ERROR: input \"%s\" is missing in the data. Cannot process. Stop.",$rec);
               error(14);
            }
            $res->$rec = $data->$rec;
         }
      }
   
      // Looping trough parameters. The ones defined in $no_param
      // will be ignored (as they are already processed and no parameters)
      $no_params = array("user","password","city","submit","cityID");
      foreach ( $data as $key => $val ) {

         // Skip those from no_param
         if ( is_numeric(array_search($key,$no_params)) ) { continue; }
         // Value which are not numeric: skip 
         $val = str_replace(",",".",$val);
         // Splitting parameter
         $tmp = explode("_",$key);


         // ------------------------------------------------------------
         // A few ignore cases first
         // ------------------------------------------------------------
         // If length is not equal to 2: add to ignore!
         if ( count($tmp) != 2 ) {
            $res->ignored[$key] = "Ignored because format was wrong. Has to be <NAME>_<DAY>.";
         // If second part is not numeric: ignore
         } else if ( ! is_numeric($tmp[1]) ) {
            $res->ignored[$key] = "Ignored because format was wrong. Second part (<NAME>_<DAY>) "
                                 ."has to be an integer, got non-numeric value!"; 
         // ------------------------------------------------------------
         // Else check value. If value is invalid: add to inored.
         // if valid but has to be corrected: correct. Else add. 
         // ------------------------------------------------------------
         // Else adding to correct prop
         } else {
            // Easy access to parameter name and forecast day
            $param = strtoupper( (string)$tmp[0] );
            $day   = (int)$tmp[1];
            // Correct property name
            $prop  = sprintf("day_%d",(int)$day);
            if ( ! property_exists($res,$prop) ) { $res->$prop = new stdClass(); }
            if ( ! is_numeric($val) ) {
               $res->$prop->$param = NULL; 
            } else {
               $res->$prop->$param = (float)$val;
            }
         }
      }
   
      return( $res );
   }
   
   
   // ---------------------------------------------------------------
   /// @details Returns summary of the received data.
   ///
   /// @param $data. stdClass object containing the required user input
   ///   data (forecast/bet) and city information.
   /// @param $stoponerror. boolean, default is `true` (as used in the
   ///   autosubmit procedure) whenever someone submits data via an
   ///   automated script.
   ///   If `true` the script will give some output
   ///   and stops if an error occurs (missing or droped data). This is
   ///   needed as we only store auto-submit forecasts where ALL
   ///   necessary parameters are set correctly. `stoponerror=false` is
   ///   used by the frontend bet form. In this case we won't exit the
   ///   script, but give the user some info and store what we get. This
   ///   allows the user to save parts of his bet.
   /// @return array containing the data and a boolean value `check`
   ///   which is `true` if everything is o.k., and `false` otherwise.
   // ---------------------------------------------------------------
   function check_received_data( $data, $stoponerror = true ) {

      global $WTuser;
      $ndays = (int)$WTuser->options->wetterturnier_betdays;

      // check. Flag which will be returned at the end of this function. 
      // If true at the end, we have all data needed and can mark the
      // bet als "propper" and save the submission time. This means that
      // the bet is fully probided and will be scored later on. If not,
      // there is at least one parameter missing.
      $check = true;
   
      // Checking number of received days.
      for ( $day=1; $day<=$data->betdays; $day++ ) {
         if ( ! property_exists($data,sprintf("day_%d",$day)) ) {
            $check = false;
            if ( $stoponerror ) {
               printf('[!] While checking parameters: The wetterturnier '
                            .'expects to get data for the next %d days. Day '
                            .'"%d" seems not to be delivered! Stop!',
                             $data->betdays,$day);
               $this->error(15);
            } else { continue; }
         }
   
         // Checking all parameters which have to be sent to the
         // wetterturnier are delivered by the user. if not, the script
         // will stop and not send the data to the database.
         $hash     = sprintf("day_%d",$day);
         $haystack = $data->$hash;
         foreach ( $data->cityObj->paramconfig_string as $param ) {
            $param    = strtoupper($param);
            // If parameter is not defined for this day: stop
            if ( ! property_exists($data->$hash,$param) ) { 
               if ( $stoponerror ) {
                  printf("[!] Parameter \"%s\" missing for forecast day \"%d\". "
                               ."If not all parameters are sent we cannot store your "
                               ."forecast/bet!",$param,$day);
                  $this->error(15);
               } else { $check = false; }
            } else if ( is_null($data->$hash->$param) ) { 
               if ( $stoponerror ) {
                  printf("[!] Parameter \"%s\" missing for forecast day \"%d\". "
                               ."If not all parameters are sent we cannot store your "
                               ."forecast/bet!",$param,$day);
                  $this->error(15);
               } else { $check = false; }
            }
         }
   
      }
   
      // Checks if there were parameters which were additional but
      // not requested by the wetterturnier or the current city.
      // If there are, add ignore message and drop the parameter. 
      foreach ( $data as $key=>$arr ) {
         if ( ! preg_match("/^day_[1-9]{1,}/",$key) ) { continue; }
         list($param,$day) = explode("_",$key); 
         if ( (int)$day > $data->betdays ) {
            unset($data->$key); 
            $data->ignored[$key] = sprintf("Forecasts for day %s ignored. They were not "
                   ."requested. Everything fine. This is just a note that you are sending "
                   ."more data than necessary.",$day);
         }
      }
   
      // Checking for additional parameters
      for ( $day=1; $day <= $data->betdays; $day++ ) {
         $hash = sprintf("day_%d",$day);
         if ( ! property_exists($data,$hash) & ! $stoponerror ) { continue; }
         $haystack = array($data->$hash);
         $haystack = $haystack[0];
         foreach ( $haystack as $param => $value ) {
            $param = strtoupper($param);
            if ( ! in_array($param,$data->cityObj->paramconfig_string) ) {
               unset($data->$hash->$param);
               $data->ignored[sprintf("%s %s",$hash,$param)] = sprintf("Forecast parameter "
                      ."%s for day %d ignored. This was not requested for this city. "
                      ." Everything fine. This is just a note that you are sending "
                      ."more data than necessary.",$param,$day);
            }
         }
      }
   
      return( array($data, $check) );
   
   }
   
   
   // ---------------------------------------------------------------
   /// @details Check if received values were valid. Corrects some of the
   ///   data or drops them if values were in a wrong format or something. 
   ///
   /// @param $data. stdClass object containing the submitted data/bet.
   /// @return Returns $data with (possibly) corrected or dropped values.
   // ---------------------------------------------------------------
   function check_correct_values( $data ) {
   
      global $WTuser;
   
      // Looping over requested forecast days
      for($day=1;$day<=$data->betdays;$day++) {
         $hash = sprintf("day_%d",$day);
         if ( ! property_exists($data,$hash) ) { continue; }
         // Looping over all parameters
         foreach ( $data->$hash as $param=>$val ) {
            // Checking/correcting the value
            $cval  = $this->check_bet_value($param, (is_null($val) ? NULL : $val*10.) );
            // If there were corrections or rejections: add to $data->checknotes
            $key = sprintf("%s_%s",$hash,$param);
            if ( property_exists($cval,'error') ) { 
               $data->checknotes[$key] = sprintf("Value check ERROR:   %s",$cval->error);
               $val = $cval->value; 
               //unset($data->$hash->$param); continue;
            } else if ( property_exists($cval,'warning') ) { 
               $data->checknotes[$key] = sprintf("Value check WARNING: %s",$cval->warning);
               $val = $cval->value / 10.;
            }
            if ( is_null($val) ) {
               $data->$hash->$param = $val;
            } else {
               $data->$hash->$param = $val*10.;
            }
         }
      }
      return( $data );
   }


   // ---------------------------------------------------------------
   /// @details Checking bet values before I insert them into the databae.
   ///  Note that this will be used by user/view/bet.php but also
   ///  by the user/autosubmit/autosubmit.php script. 
   ///
   /// @param $param. String, parameter shortname (e.g., RR).
   /// @param $value. Float, the forecasted value for $param.
   /// @return Returns a stdClass object containing parameter
   ///   information, (possibly corrected) value and a warning
   ///   or error message if warnings or errors occured.
   // --------------------------------------------------------------
   function check_bet_value( $param, $value ) {

       global $WTuser;
       $res = new stdClass();
       $res->param = $param; $res->value = $value;

       $pconfig = $WTuser->get_param_by_name( $param );
       // If value is NULL simply return
       if ( is_null($value) ) { return($res); }
       // If parameter is out of range: return array(false,NULL);
       if ( $value < $pconfig->valmin || $value > $pconfig->valmax ) {
           $res->value = NULL;
           $res->error = sprintf("Value was outside its limits for parameter \"%s\". "
                    ."Defined range is %.1f to %.1f. Your submitted value was \"%.1f\". "
                    ."Set to NULL!",$param,$pconfig->valmin/10.,$pconfig->valmax/10.,$value/10.);
           return( $res );
       }

       // If parameter is 'fx' and value is between 0 and 250: correct
       // to 0! 250 is 25 knots. All below should be reduced to 0.
       if ( strcmp("fx",$param) === 0 && $value > 0 && $value < 250 ) {
           $res->value = 0;
           $res->warning = sprintf("Corrected wind gust bet. fx is not allowed "
               ."to be between 0 and 25.0. Should either be 0, or 25-Inf. Your "
               ."value was corrected from %.1f to %.1f",$value/10.,$res->value/10);
       }

       // If parameter is precipitation (RR) and value is between -30 and 0
       // (which means precip -3.0 and 0) setting value to -30.
       if ( strcmp("RR",$param) === 0 && $value > -30 && $value < 0 ) {
           $res->value = -30;
           $res->warning = sprintf("Corrected precipitation bet. RR is not allowed "
               ."to be between -3.0 and 0.0. Should either be -3.0, or 0-Inf. Your "
               ."value was corrected from %.1f to %.1f",$value/10.,$res->value/10.);
       }

       // Else return this object. 
       return( $res );
   }

   
   // --------------------------------------------------------------
   /// @details Shows a summary of the parsed (and possibly corrected)
   ///   data submitted by the user. The function also shows whether
   ///   some submitted values have been ignored (e.g., due to wrong
   ///   parameter specification or data which won't be processed).
   ///
   /// @param $data. stdClass object containing the submitted and
   ///   possibly corrected bet/forecast data and the city information.
   // --------------------------------------------------------------
   function show_parsed_data( $data ) {
   
      // ---------------------------------------------------------------
      // Main title 
      // ---------------------------------------------------------------
      print "\n"; $msg = "Show information parsed by the autosubmit script";
      printf("%s\n",strtoupper($msg));
      printf("%s\n",str_repeat("=",strlen($msg)));
   
      // ---------------------------------------------------------------
      // Show city information first 
      // ---------------------------------------------------------------
      $msg = "City information";
      print "\n";
      printf("  %s\n",strtoupper($msg));
      printf("  %s\n",str_repeat("-",strlen($msg)));

      printf("  %-20s %s\n","City name:",$data->cityObj->get('name'));
      printf("  %-20s %s\n","City hash:",$data->cityObj->get('hash'));
      printf("  %-20s %d\n","City ID:",  $data->cityObj->get('ID')  );
   
      // ---------------------------------------------------------------
      // Ignored parameters 
      // ---------------------------------------------------------------
      print "\n"; $msg = "Ignored parameters";
      printf("  %s\n",strtoupper($msg));
      printf("  %s\n",str_repeat("-",strlen($msg)));
   
      if ( count($data->ignored) == 0 ) {
         printf("  No parameter were ignored, everything ok.\n");
      } else {
         foreach ( $data->ignored as $key => $val ) {
            printf("  Ignored: %s: %s\n",$key,$val);
         }
      }
   
   
      // ---------------------------------------------------------------
      // Messages from the value check routine 
      // ---------------------------------------------------------------
      print "\n"; $msg = "Output from value check routine";
      printf("  %s\n",strtoupper($msg));
      printf("  %s\n",str_repeat("-",strlen($msg)));
   
      if ( count($data->checknotes) == 0 ) {
         printf("  No notifications found, everything ok.\n");
      } else {
         foreach ( $data->checknotes as $key => $val ) {
            printf("  %s: %s\n",$key,$val);
         }
      }
   
      // ---------------------------------------------------------------
      // Show parameters (on properties like "day_X")
      // ---------------------------------------------------------------
      print "\n"; $msg = "Loaded parameters";
      printf("  %s\n",strtoupper($msg));
      printf("  %s\n",str_repeat("-",strlen($msg)));
   
      // Find "day" entries first:
      $all_days  = array();
      $all_param = array();
      foreach ( $data as $key => $params ) {
         // Searching for properties like "day_X"
         if ( ! preg_match("/^day_[1-9]/",$key) ) { continue; }
         // Extracting day
         $tmp = explode("_",$key); $day = (int)$tmp[1];
         $all_days[$day] = $day;
         foreach ( $params as $param => $value ) {
            array_push($all_param,$param);
         }
      }
      sort($all_days);
      $all_param = array_unique($all_param);
   
      // Now show that stuff
      if ( count($all_param) > 0 ) {
         // Show header
         printf("  %-20s ","");
         foreach ( $all_days as $day ) {
            printf("%8s ",sprintf("Day %d",$day));
         }
         print "\n";
         // Show parameter
         foreach ( $all_param as $param ) {
            sprintf("  %-20s ",sprintf("- Parameter %s:",$param));
            foreach ( $all_days as $day ) {
               $prop = sprintf("day_%d",$day);
               if ( property_exists($data->$prop,$param) ) {
                  printf("%8.1f ",(float)$data->$prop->$param/10.);
               } else {
                  printf("%8s ","- - -");
               }
            } 
            print "\n";
         }
      }
   
   }
   
   
   // --------------------------------------------------------------
   /// @details Write data to database. Prepares arrays (key/val) to feed the
   ///   insertonduplicate function in the wetterturnier plugin.
   ///   This function is also used when administrators change the
   ///   forecasts of one of our users. In this case only changed values
   ///   will be updated and marked in the database. 
   ///
   /// @param $user. stdClass object containing the user information such
   ///   as the ID of the user. Required to update the correct row in the
   ///   database.
   /// @param $next. stdClass object containing the next tournament date
   ///   which is required to update the corresponding row in the database.
   /// @param $data. The forecast data and city information.
   /// @param $checkflag. Boolean. If true all needed variables are here.
   ///   In this case we can write the time into the betstat table.
   /// @param $verbose. Boolean, default `true`.
   /// @param $adminuser. Default `NULL`. If not `NULL` this indicates
   ///   that an administrator currently changes the data/forecast.
   // --------------------------------------------------------------
   function write_to_database( $user, $next, $data, $checkflag, $verbose = true, $adminuser=NULL ) {
   
      global $WTuser;
      global $wpdb;

      // If $adminuser is set, the admin changes some values. In this case we
      // would like to update only the parameters, which have been changed, and
      // not all (on duplicate). Therefore loading the corresponding data from
      // the database first.
      if ( ! is_null($adminuser) ) {
         $existing = $wpdb->get_results(sprintf("SELECT * FROM %swetterturnier_bets "
                  ."WHERE userID=%d AND cityID=%d AND tdate=%d",
                  $wpdb->prefix,$user->data->ID,$data->cityObj->get('ID'),$next->tdate));
      }

      // Helper method which will be used below, checking if a certain value
      // has been changed by the admin or not.
      function set_placedby_if_changed($tmp,$existing,$adminID) {
         // If not "existing", the admin added a new value. Return adminID 
         $placedby = (int)$adminID;
         foreach ( $existing as $rec ) {
            // Parameter and betdate match?
            if ( $rec->paramID == $tmp['paramID'] && $rec->betdate == $tmp['betdate'] ) {
               // Value changed: admin changed an existing value, return
               // the userID of the admin who is manipulating the data.
               if ( (int)$rec->value != (int)$tmp['value'] ) { $placedby = $adminID; }
               // Else return the userID currently in the database.
               else { $placedby = $rec->placedby; }
               // End loop
               break;
            }
         }
         return($placedby);
      }

      // Prepares $data for database
      $data4db = array();
      foreach ( $data as $key => $params ) {
         // Searching for properties like "day_X"
         if ( ! preg_match("/^day_[1-9]/",$key) ) { continue; }
         $tmp = explode("_",$key); $day = (int)$tmp[1];
   
         // If betdate entry does not exist (from $next): continue
         $prop = sprintf("betdate_day%d",$day);
         if ( ! property_exists($next,$prop) ) { continue; }
         $betdate = $next->$prop;
         $tdate   = $next->tdate;
   
         // Processing the data
         foreach ( $params as $param => $value ) {
            // Loading parameter ID
            $paramID = $WTuser->get_param_ID( $param );
            if ( is_bool($paramID) ) {
               printf("  ERROR: Cannot find parameter ID for parameter %s in database. Skip.",
                              $param);
               continue;
            }
            $tmp = array("userID"  => $user->data->ID,
                         "cityID"  => $data->cityObj->get('ID'),
                         "paramID" => $paramID,
                         "tdate"   => $tdate,
                         "betdate"        => $betdate,
                         "value"          => $value,
                         "placedby"       => 0 );
            // Admin mode: check if the admin really changed this value.
            if ( ! is_null($adminuser) ) {
               $tmp['placedby'] = set_placedby_if_changed($tmp,$existing,$adminuser->data->ID);
            }
            
            array_push($data4db,$tmp);
            unset($tmp);
         }
      }
   
      // No data to write to database?
      if ( count($data4db) == 0 ) {
         printf("No data to write to database! All rejected nor not submitted?\n");
         $this-> error(9);
      } 
    
      // Write to database and create some user output 
      $user_login = $user->data->user_login;
      if ( $verbose ) {
         printf("           %-10s   %-14s   %-14s    %-6s  %8s\n",
                     "Username","Tournament","Valid for","Param","Value"); 
      }
      foreach ( $data4db as $rec ) {
         $param = $WTuser->get_param_by_ID($rec['paramID'])->paramName;
         $td = sprintf("%s %s",$WTuser->date_format($rec['tdate'],  "%a"),
                               $WTuser->date_format($rec['tdate']));
         $bd = sprintf("%s %s",$WTuser->date_format($rec['betdate'],"%a"),
                               $WTuser->date_format($rec['betdate']));
         //$td = date("D Y-m-d",$rec['tdate']*86400);
         //$bd = date("D Y-m-d",$rec['betdate']*86400       );
         if ( $verbose ) {
            printf("  - Write: %10s   %14s   %14s:   %-5s  %8.1f\n",
               $user_login,$td,$bd,$param,$rec['value']/10.);
         }
         // If value is not empty: write to database. Else drop from database!
         if ( ! is_null($rec['value']) ) {
            $dbcheck = $WTuser->insertonduplicate(sprintf("%swetterturnier_bets",$wpdb->prefix),$rec);
         } else {
            $where = array("userID"  => $rec['userID'],
                           "cityID"  => $rec['cityID'],
                           "paramID" => $rec['paramID'],
                           "tdate"   => $rec['tdate'],
                           "betdate" => $rec['betdate']);
            $wpdb->delete(sprintf("%swetterturnier_bets",$wpdb->prefix),$where);
         }
      }
      // Update betstat table (used to store the points later in the tournament)
      // If and only if $checkflag is TRUE (all days, all parameters for the city set
      // as expected). If there is something missing: remove the betstat entry. This
      // also means that the user bet will be ignored!
      if ( $checkflag ) {
         $data = array("userID" => $user->data->ID,
                       "cityID" => $data->cityObj->get('ID'),
                       "tdate"  => $next->tdate,
                       "submitted" => date("Y-m-d H:i"));
         if ( $verbose ) { print "  - Update betstat table\n"; }
         $dbcheck = $WTuser->insertonduplicate(sprintf("%swetterturnier_betstat",$wpdb->prefix),$data);
      } else {
         $where = array("userID" => $user->data->ID,
                        "cityID" => $data->cityObj->get('ID'),
                        "tdate"  => $next->tdate);
         $wpdb->delete(sprintf("%swetterturnier_betstat",$wpdb->prefix),$where);
      }
   
      ///return($data4db);
   }


   // --------------------------------------------------------------
   /// @details The function which produces the output
   ///   Using the same method for the admin (changing bets)
   ///   and the user (submit bet). If inputs $cityID and $userID are
   ///   empty, the user form will be shown. If both are given, the
   ///   form will be shown on the admin side with the bets for a selected
   ///   user (userID), and city (cityID).
   ///  There are two helper functions print_bet_form and print_obs_form
   ///  for displaying the data while this thin also adds some jQuery
   ///  code and does some stuff.
   ///
   /// @param $cityID. Default `NULL`. If `NULL` the current city
   ///   @ref WP_wetterturnier_generalclass::get_current_city will be used.
   /// @param $targetID. Default `NULL`. If `NULL` the ID of the active
   ///   user will be used. $targetID is only used for administrators
   ///   which might possibly change forecasted values of other users.
   /// @param $isstation. Boolean, default is `false`. If `false` the
   ///   bet-form for players (forecasts) will be shown. If set to `true`
   ///   the form to manipulate station observations will be shown. 
   /// @see print_bet_form
   /// @see print_obs_form
   // ------------------------------------------------------------------
   function print_form( $cityID=NULL, $targetID=NULL, $isstation=false, $tdate=NULL ) {

      global $WTuser;
   
      // User mode:
      if ( is_null($cityID) && is_null($targetID) ) {
         $tournament = $WTuser->next_tournament();
         $admin_mode = false;
         $userID     = get_current_user_id();
      } else {
         // It's actually not next, but latest when using in admin mode!
         $tdate = (is_null($tdate) ? (int)date("%s")/86400 : (int)$tdate );
         $tournament = $WTuser->latest_tournament( $tdate );
         $admin_mode = true;
         if ( ! $isstation ) { $userID = $targetID; }
         if ( ! is_admin() ) { die("You, as a non-admin, started using the bet form "
            ."in admin mode. There is something wrong. Really, really wrong."); }
      }
   
      // If next friday is a tournament weekend, show form
      if ( ! $tournament->closed  ) { 

         // Creating a new object: store all necessary infos onto it (needed later
         // for print_bet_form or print_obs_form)
         $obj = new stdClass();
         // Defualt view of the form fields (up-down = portrait, or left-right = landscape)
         // Loading user option. If empty, use portrait.
         $obj->defaultview = get_user_option("wt_betform_orientation");
         if ( is_bool($obj->defaultview) ) { $obj->defaultview = "portrait"; }
         // Loading number of forecast days as specified in the settings
         $obj->betdays           = get_option("wetterturnier_betdays");

         // ------------------------------------------------------------
         // Loading city information (needed for both, bets and obs)
         // ------------------------------------------------------------
         if ( is_null( $cityID ) ) {
            // Load pre-fetched city information of the current city
            $cityObj = $WTuser->get_current_cityObj();
         } else {
            $cityObj = new wetterturnier_cityObject( $cityID );
         }
   
         // Looping over parameters. If not necessary for this city
         // (then ID's not in $city->paramconfig) the parameter will
         // be skipped out of the $parameter object.
         $obj->parameter = $WTuser->get_param_data();
         foreach ( $obj->parameter as $key=>$val ) {
            if ( ! in_array($val->paramID,$cityObj->get('paramconfig')) ) {
               unset($obj->parameter->$key);
            }
         }
   
   
         // ------------------------------------------------------------
         // Frontend user information
         // Only used for the user when submitting a bet, not in admin
         // mode. Only working for "bets", not for "obs"!
         // ------------------------------------------------------------
         if ( ! $admin_mode && ! $isstation ) {

            //$city = $WTuser->get_current_city();
            $is_submitted = $WTuser->check_bet_is_submitted($userID,$cityObj,$tournament->tdate);

            if ( $is_submitted )         { $div_class = 'ok'; }
            else                         { $div_class = 'warning'; }

            // Info closing time
            $ct = strptime(sprintf("%s %04d UTC",$WTuser->date_format($tournament->tdate,"%Y-%m-%d"),
                           (int)$WTuser->options->wetterturnier_bet_closingtime),"%Y-%m-%d %H%M %Z");
            $ct = (int)mktime($ct['tm_hour'], $ct['tm_min'], $ct['tm_sec'], $ct['tm_mon']+1,
                              $ct['tm_mday'], $ct['tm_year']+1900);

            // Info for which weekend the form is valid
            #echo "<div class='wetterturnier-info ".$div_class."'>"
            #   .__("Please be sure that this is correct before inserting/sending the data.","wpwt")."<br>\n" 
            #   .__("Form to submit your bet for","wpwt").": "
            #   .$tournament->weekday.", ".$tournament->readable.".<br>\n";
            printf("<div class='wetterturnier-info %s'>%s %s: %s, %s", $div_class,
               __("Please be sure that this is correct before inserting/sending the data.","wpwt"),
               __("Form to submit your bet for","wpwt"), $tournament->weekday, $tournament->readable);

            // submitted or not?
            if ( $is_submitted ) {
               printf("<b>%s</b>",__("YOUR BET HAS BEEN SUBMITTED, EVERYTHING FINE.","wpwt"));
            } else {
               printf("<b>%s</b><br>\n%s %s",
                    __("YOUR BET HAS NOT BEEN SUBMITTED YET.","wpwt"),
                    __("Please fill in all values in the propper format.","wpwt"),
                    __("Afterwards submit your bet by clicking the submit button.","wpwt"));
            }
            echo "</div>";

            // Generate output
            $form_closes = sprintf("<span class='big'>%s %s, %s %s</span><br>",
                  __("Form closes","wpwt"),$WTuser->date_format($tournament->tdate,"%A"),
                  $WTuser->date_format($ct/86400),
                  $WTuser->datetime_format($ct,"%H:%M %Z"));
            printf("<div class='wetterturnier-info warning'>%s <span class='big'>%s</span> %s, "
                  ."<span class='big'>%s</span>.<br><span class='big' id='live-closingstring'></span> "
                  ."<span class='big' id='live-closingtime'></span>"
                  ."</div>",
                  __("Form closes","wpwt"),$WTuser->date_format($tournament->tdate,"%A"),
                  $WTuser->date_format($ct/86400),
                  $WTuser->datetime_format($ct,"%H:%M %Z"));

            printf("<div class='wetterturnier-info ok large'>%s <span class='big'>%s</span></div>",
                   __("Your active city is","wpwt"),$cityObj->get('name'));
         }
   
   
         // ------------------------------------------------------------
         // Mini jQuery function to change landscape/portrait
         // ------------------------------------------------------------
         ?>
   
         <script type="text/javascript">
         jQuery(document).ready(function() {
            $ = jQuery
            $(document).on("click","span.orientation",function() {
               setorientation( $(this) )
            });
            $(document).keypress(function(event){
               if ( event.which == 120 ) { setorientation( $("span.orientation") ) }
            });
            function setorientation( input ) {
               var currentview = $(input).attr("orientation");
               if ( currentview == "portrait" )
               { var newview = "landscape"; } else { var newview = "portrait"; }
               $(input).attr("orientation",newview);
               $("#wetterturnier-bet-form").removeClass(currentview);
               $("#wetterturnier-bet-form").addClass(newview);
            }
            // Function to show closing time on user frontend.
            <?php // Only if not admin and not station 
            if ( ! $admin_mode && ! $isstation ) { ?>
               closingstring( <?php print $ct; ?> );
               function show_closingstring( timestamp ) {
                  var now  = parseInt( $.now() / 1000 )
                  var diff = timestamp - now
                  // Default is: Form closed
                  var val = "<?php _e("Form closed! Submissions won't be stored anymore.","wpwt"); ?>"
                  if ( diff > (2*86400) ) {
                     var d = parseInt( diff / 86400 )
                     val = "<?php _e("Form closes in more than","wpwt"); ?> "+d+" <?php _e("days","wpwt"); ?>. <?php _e("No hurry!","wpwt"); ?>"
                  } else if ( diff > 86400 ) {
                     val = "<?php _e("More than one day left to submit the bet.","wpwt"); ?>"
                  } else if ( diff > (2*3600) ) {
                     var d = parseInt( diff / 3600 )
                     val = "<?php _e("Form closes in more than","wpwt"); ?> "+d+" <?php _e("hours","wpwt"); ?>. <?php _e("Keep cool!","wpwt"); ?>"
                  } else if ( diff > 3600 ) {
                     val = "<?php _e("Still a bit more than one hour until the form closes.","wpwt"); ?>"
                  } else if ( diff > (10*60) ) {
                     var d = parseInt( diff / 60 )
                     val = "<?php _e("The form closes in about","wpwt"); ?> "+d+" <?php _e("minutes","wpwt"); ?>."
                  } else if ( diff > (2*60) ) {
                     var d = parseInt( diff / 60 )
                     val = "<?php _e("The form closes in a bit more than","wpwt"); ?> "+d+" <?php _e("minutes","wpwt"); ?>! <?php _e("Time to hurry up a bit, my friend!","wpwt"); ?>!"
                  } else if ( diff > 60 ) {
                     val = "<?php _e("Only a bit more than one minute left to submit your bet!","wpwt"); ?>"
                  } else if ( diff > 0 ) {
                     val = "<?php _e("Form closes in","wpwt"); ?> "+diff+" <?php _e("seconds","wpwt"); ?>! <?php _e("Hurry, hurry!","wpwt"); ?>"
                  }
                  // User output
                  $("#live-closingstring").html( val )
               }
               // Sever time
               function getServerTime() {
                  var x = $.ajax({async: false}).getResponseHeader( 'Date' );
                  return x
               }
               function show_servertime() {
                  $("#live-closingtime").html( getServerTime() )
               }
               function closingstring( timestamp ) {
                  show_closingstring(timestamp)
                  show_servertime();
                  var intv = self.setInterval( function() { show_closingtime(timestamp) }, 1000 ) 
                  var intv2 = self.setInterval( function() { show_servertime() }, 100 ) 
               }
               closingstring( timestamp );
               //function closingtime( timestamp ) {
               //   $("#live-closingtime").html( getServerTime() )
               //   var intv2 = self.setInterval( function() { g
               //}
            <?php } ?>
         });
         </script>

         <style>
            #wetterturnier-bet-form button {
                margin-left: 165px;
                margin-top: 5px;
                width: 280px;
            }
            /* view "current" with the form where to add/store the
             * forecasted values. There are two types, a portrait and
             * a landscape version */
            #wetterturnier-bet-form.portrait div {
               float: left;
               width: 100px;
            }
            #wetterturnier-bet-form div ul li input:disabled {
               background-color: gray;
            }
            #wetterturnier-bet-form.landscape div {
               float: none;
               height: 40px;
               width: 100%;
               clear: both;
            }
            #wetterturnier-bet-form.portrait #wt-betform-parameter-wrapper {
               width: 30%;
               min-width: 230px;
            }
            #wetterturnier-bet-form #wt-betform-parameter-wrapper > div { width: 100% }
            .wt-betform title {
               display: block;
               line-height: 2em;
               font-weight: bold;
               width: 100%;
            }
            #wetterturnier-bet-form.landscape div title {
               float: left;
               width: 10%;
               overflow: hidden;
            }
            #wetterturnier-bet-form div ul {
               list-style: none;
               padding: 0px; margin: 0px;
            }
            #wetterturnier-bet-form div ul li {
               height: 2em;
               line-height: 2em;
               margin: 2px;
               width: 100%;
               overflow: hidden;
            }
            #wetterturnier-bet-form.landscape div ul li {
               float: left;
               width: 70px;
               padding: 0;
            }
            #wetterturnier-bet-form.landscape div ul li long  { display: none;   }
            #wetterturnier-bet-form.landscape div ul li short { display: inline; }
            #wetterturnier-bet-form.portrait  div ul li long   { display: inline; }
            #wetterturnier-bet-form.portrait  div ul li short  { display: none;   }
            #wetterturnier-bet-form           div ul li input {
               max-width: 90%;
               border: 1px solid blue;
               text-align: right;
               padding: .2em;
               margin: 0px;
            }

         </style>
         <!--
         <button name="orientation" value="<?php print $obj->defaultview; ?>">
            <?php _e("Switch orientation","wpwt"); ?>
         </button>
         -->

         <?php 

         // Show station edit form 
         if ( $isstation ) {
            // targetID in this case is the statioin number!!
            $this->print_obs_form( $obj, $tournament, $targetID );
         // Else print the bet form 
         } else {
            $this->print_bet_form( $obj, $tournament, $userID, $cityObj );
         }

      }
   } // End of print_form()


   // ---------------------------------------------------------------
   /// @details Helper function. Will be called by @ref print_form.
   ///   Shows the bet form for bets. There is a second function using mainly the
   ///   same framework, but showing observatinos (to modify observations in the
   ///   admin interface; called @ref print_obs_form )
   ///
   /// @param $obj. stdClass object containing the required data.
   /// @param $tournament. stdClass object containing the tournament
   ///   information (like date and so on).
   /// @param $userID. Integer, ID of the user.
   /// @param $cityObj. Object of class @ref wetterturnier_cityObject. 
   /// @see print_form
   /// @see print_obs_form
   // ---------------------------------------------------------------
   function print_bet_form( $obj, $tournament, $userID, $cityObj ) {

      // Getting propper current page url
      global $WTuser;
      $curURL = $WTuser->curPageURL();

      // ------------------------------------------------------------
      // Looping over all necessary forecast days
      // ------------------------------------------------------------
      print "<h3 style=\"display: inline;\">BET FORM</h3>\n";
      printf("<span class=\"orientation\" orientation=\"%s\">[%s]</span><br>",
            $obj->defaultview,__("Switch orientation","wpwt"));
      printf("<form action='%s' method='post' class='%s' id='wetterturnier-bet-form'>\n",
            $curURL,$obj->defaultview);
   
      // Adding parameter description
      printf("<div id=\"wt-betform-parameter-wrapper\" class=\"wt-betform %s\">\n",$obj->defaultview);
      printf("<title>%s</title>\n",__("Parameter","wpwt"));
      print "<ul>\n";

      // Adding one list element per parameter
      foreach ( $obj->parameter as $param ) {
         printf("  <li>%s%s</li>\n",
                sprintf("<long>%s</long>",  $param->thename),
                sprintf("<short>%s</short>",$param->paramName));
      }
      print "</ul>\n"
           ."</div>\n";
   
      // Adding data ul's
      for ( $day=1; $day <= $obj->betdays; $day++ ) {
         $data = $WTuser->get_user_bets_from_db($cityObj,$userID,$tournament,$day);
         $day_string = $WTuser->date_format( (int)$tournament->tdate + $day, "%A" ); 
         printf("<div id=\"wt-betform-%d-wrapper\" class=\"wt-betform %s\">\n",$day,$obj->defaultview);
         printf("<title>%s</title>\n",$day_string);
         print "<ul>\n";
         // Adding one list element per parameter
         foreach ( $obj->parameter as $param ) {
            $input_name  = sprintf("%s_%d",$param->paramName,$day);
            if ( property_exists($data,$input_name) ) { $input_value = $WTuser->number_format($data->$input_name,$param->decimals); }
            else                                      { $input_value = "";                  }
            printf("   <li><input type=\"text\" name=\"%s\" value=\"%s\"maxlengt=\"6\"></input></li>",
                         $input_name,$input_value); 
         }
         print "</ul>\n" // End ul element
              ."</div>\n"; // End wrapper
   
      } // End looping over all days

      // Adding cityID if set. Needed for admin-interface
      if ( $cityObj ) {
         printf("<input type='hidden' name='cityID' value='%d'></input>",$cityObj->get('ID'));
      }

      print "  <button type=\"submit\" id=\"save-submit\" name=\"submit\" value=\"save\">\n"
           .__("Validate & Save","wpwt")."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n"
           .__("for","wpwt").": ".$cityObj->get('name')."</button><br>\n"
           ."</form>";
   
   } // End of print_bet_form()


   // ---------------------------------------------------------------
   /// @details Helper function. Will be called by @ref print_form.
   ///   Shows the observation edit form. There is a second function using mainly the
   ///   same framework, but showing user bets (to modify bets in the
   ///   admin interface; called @ref print_bet_form)
   ///
   /// @param $obj. stdClass object containing the required data.
   /// @param $tournament. stdClass object containing the tournament
   ///   information (like date and so on).
   /// @param $station. Integer, WMO station number.
   /// @see print_form
   /// @see print_obs_form
   // ---------------------------------------------------------------
   function print_obs_form( $obj, $tournament, $station ) {

      // Getting propper current page url
      global $WTuser;
      $curURL = $WTuser->curPageURL();
      $station = $WTuser->get_station_by_wmo( $station );
      if ( strlen($station->nullconfig) > 0 ) {
         $station->nullconfig = json_decode($station->nullconfig);
      } else {
         $station->nullconfig = array();
      }

      // ------------------------------------------------------------
      // Looping over all necessary forecast days
      // ------------------------------------------------------------
      print "<h3 style=\"display: inline-block;\">OBS FORM</h3>\n";
      printf("<span class=\"orientation\" orientation=\"%s\">[%s]</span><br>",
            $obj->defaultview,__("Switch orientation","wpwt"));
      printf("<form action='%s' method='post' class='%s' id='wetterturnier-bet-form'>\n",
            $curURL,$obj->defaultview);
   
      // Adding parameter description
      printf("<div id=\"wt-betform-parameter-wrapper\" class=\"wt-betform %s\">\n",$obj->defaultview);
      printf("<title>%s</title>\n",__("Parameter","wpwt"));
      print "<ul>\n";
      // Adding one list element per parameter
      foreach ( $obj->parameter as $param ) {
         printf("  <li>%s%s</li>\n",
                   sprintf("<long>%s</long>",  $param->thename),
                   sprintf("<short>%s</short>",$param->paramName));
      }
      print "</ul>\n"
           ."</div>\n";

      // Adding data ul's
      for ( $day=1; $day <= $obj->betdays; $day++ ) {
         $data = $WTuser->get_obs_from_db($station->wmo,$tournament,$day);
         $day_string = $WTuser->date_format( (int)$tournament->tdate + $day, "%A" ); 
         printf("<div id=\"wt-betform-%d-wrapper\" class=\"wt-betform %s\">\n",$day,$obj->defaultview);
         printf("<title>%s</title>\n",$day_string);
         print "<ul class=\"wt-betform\">\n";
         // Adding one list element per parameter
         foreach ( $obj->parameter as $param ) {
            if ( in_array($param->paramID,$station->nullconfig) )
            { $disabled = true; } else { $disabled = false; }
            $input_name  = sprintf("%s_%d",$param->paramName,$day);
            if ( property_exists($data,$input_name) ) {
               $input_value = (is_null($data->$input_name) ? "-xxx-" : $data->$input_name);
               $nullclass   = (is_null($data->$input_name) ? "setnull" : "");
            } else {
               $input_value = "";
               $nullclass   = "";
            }
            printf("   <li><input type=\"text\" name=\"%s\" value=\"%s\" "
                        ."maxlengt=\"6\" class=\"%s\" %s></input></li>",
                        $input_name,$input_value,$nullclass,($disabled ? " disabled" : "")); 
         }
         print "</ul>\n" // End ul element
              ."</div>\n"; // End wrapper
   
      } // End looping over all days
      print "  <input type=\"hidden\" name=\"tdate\" value=\"".$tournament->tdate."\"></input>\n";
      print "  <button type=\"submit\" id=\"save-submit\" name=\"submit\" value=\"save\">\n"
           .__("Save Observations","wpwt").": [".$station->wmo."] ".$station->name."</button><br>\n"
           ."</form>";
   
   } // End of print_bet_form()


   // ----------------------------------------------------------------
   /// @details Update or insert bet in the database.
   /// 
   /// @param next. stdClass tournament date object. Default `NULL` if a user inserts
   ///   a bet on the frontend. If an admin maniplates the bets of
   ///   a user, this should be used (to define for which date the
   ///   changes should be stored).
   /// @param user. Wordpress user object. Same as for next: is `NULL` if
   ///   a user inserts a bet on the frontend (current user will be
   ///   used). If an admin changes bets for a specific user, this
   ///   has to be the user which the modifications should affect.
   // ----------------------------------------------------------------
   function update_bet_database($next=NULL,$user=NULL) {
   
      function error($msg) {
         printf("<div class=\"wetterturnier-info error\">%s</div>",$msg);
      }
   
      // If 'submit' not equal to 'save' this is some unexpected input.
      $input = (object)$_REQUEST; 
      if ( ! property_exists($input,"submit") ) {
         error(__("Unexpected input to the method which has to store the forecast "
                 ."value. This is a problem in update_bet_database, missing submit. "
                 ."Please inform one of our administrators!","wpwt"));
         return;
      }
      // If submit property was not 'save': stop.
      if ( strcmp($input->submit,"save") != 0 ) {
         error(__("Unexpected input to the method which has to store the forecast "
                 ."value. This is a problem in update_bet_database, submit value was wrong. "
                 ."Please inform one of our administrators!","wpwt"));
         return;
      }
       
      // Save current page url, load tournament  
      global $WTuser;
      $curURL = $WTuser->curPageURL();
      // Used for admin mode. If input tournament date class is set,
      // use the one we got. For user (frontend): save the bets for the
      // following (next) tournament.
      if ( is_null($next) ) {
         $next   = $WTuser->next_tournament(0,true);
      }
      // Used for admin mode. If an input user is set (admin is currently changing
      // the bets of a certain user): take this, and not current user. Current user
      // will be used if a user inserts a bet on the frontend. 
      // If input $user specifies a certain user, we have to load the current user
      // (which is then the admin manipulating the data).
      if ( is_null($user) ) {
         $user      = wp_get_current_user();
         $adminuser = NULL; // Default
      } else {
         $adminuser = wp_get_current_user();
      }
      /// - If form is closed: post message and stop this process.
      if ( ! $adminuser && $next->closed ) {
         error(__("Sorry, tournament CLOSED. We cannot store your changes anymore.","wpwt")
             ."</div>");
         return;
      }

      // Checking data
      $data = (object)$_POST;
      $data = $this->parse_post_data( $data, false );

      list($data,$checkflag) = $this->check_received_data( $data, false );
      $data = $this->check_correct_values( $data );
      $this->write_to_database( $user, $next, $data, $checkflag, false, $adminuser );
   
      if ( (count($data->ignored) + count($data->checknotes)) > 0 ) {
         echo "<div class=\"wetterturnier-info error\">\n";
         foreach ( $data->ignored as $key=>$msg ) {
            echo "<span>".$msg."</span>\n";
         }
         foreach ( $data->checknotes as $key=>$msg ) {
            echo "<span>".$msg."</span>\n";
         }
         echo "</div>\n";
      }
   
   }

}

?>
