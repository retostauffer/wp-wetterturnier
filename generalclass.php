<?php
// ------------------------------------------------------------------
/// @file generalclass.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief This is the `core class` of the wetterturnier plugin.
///   This class will be extended by both, the @ref wp_wetterturnier_userclass
///   and the @ref wp_wetterturnier_adminclass and contains methods
///   used in the wordpress backend and frontend.
// ------------------------------------------------------------------

// ------------------------------------------------------------------
/// @details General-class which is used by the main class, the
///   adminclass and the userclass. Contains a wide range of 
///   functions.
// ------------------------------------------------------------------
class wetterturnier_generalclass
{

    public $date_format = "%Y-%m-%d"; // Default date format on initialization
    public $datetime_format = "%Y-%m-%d %H:%M"; // Default datetime format on init

    /// Private attribute to store the current @ref wetterturnier_cityObject
    /// object. Try to avoid to call/load the same information multiple times
    /// as this object is used in many methods.
    /// @see get_current_cityObj
    private $current_cityObj = NULL;

    /// Private attribute to store an array with all ACTIVE cities as
    /// @ref wetterturnier_cityObject. This is used to create the navigation
    /// menu on top and for some other methods. Not to load the data several
    /// times I am using the attribute all_cityObj to store the information.
    /// Once initialized other methods will just return them.
    private $all_cityObj_active = NULL;

    /// Private attribute to store an array with all ACTIVE AND INACTIVE cities
    /// as @ref wetterturnier_cityObject.
    private $all_cityObj_all = NULL;

    /// Attribute to store the 'next tournament' once.
    public $current_tournament = false;

    // -------------------------------------------------------------- 
    /// @details The current city is used in varous scripts and methods. Not to
    ///  load the same information multiple times I am using this method to 
    ///  load the current city once from the database. The information
    ///  (a @ref wetterturnier_cityObject object) will be stored on 
    ///  the private attribute current_cityObj. If the attribute is `NULL`
    ///  load the information once. If not `NULL` return the object.
    ///
    /// @return Returns a @ref wetterturnier_cityObject object with the
    ///  active city.
    // -------------------------------------------------------------- 
    function get_current_cityObj() {
        if ( is_null($this->current_cityObj) ) {
           $this->current_cityObj = new wetterturnier_cityObject();
        }
        return( $this->current_cityObj );
    }

    // --------------------------------------------------------------
    /// @details Initialize wordpress options. Loads a set of 
    ///  wetterturnier related options from the wordpress options
    ///  database table and returns a stdClass object containing
    ///  these options.
    /// @return stdClass object containing all options as specified
    ///  in the array $names inside this method.
    // --------------------------------------------------------------
    function init_options() { 
        // Initialize some wordpress wetterturnier options.
        // Will be loaded from the database whenever init_options
        // is called. Returns a standard class containing the
        // options and some additional elements.
        $options = new stdClass();
        global $wpdb;
        $res = $wpdb->get_results("SELECT option_name, option_value FROM "
                        .$wpdb->prefix."options WHERE option_name LIKE \"wetterturnier_%\"");

        // Put options into the stdClass object $options
        foreach ( $res as $rec ) {
            $name = $rec->option_name;
            $options->$name = $rec->option_value;
        }

        // ----------------------------------------------------------
        // Convert deadline for tournament into unix time stamp
        $next = $this->next_tournament(0,false);
        if ( ! $next->closed ) {
            $lock = strftime('%Y-%m-%d',$next->tdate*86400.)
                    ." ".(int)$options->wetterturnier_bet_closingtime;
            $lock = strptime($lock,'%Y-%m-%d %H%M');
            $lock = mktime($lock['tm_hour'], $lock['tm_min'], $lock['tm_sec'],
                           $lock['tm_mon']+1, $lock['tm_mday'], $lock['tm_year']+1900);
            $lock = $lock + 60.*(int)$options->wetterturnier_bet_closingoffset;
        } else {
            $lock = true;
        }
        $options->wetterturnier_bet_closing_timestamp = $lock;

        // Loading floatin point format
        $this->load_float_format();

        return( $options );
    }

    // --------------------------------------------------------------
    /// @details Quick-access to the plugins url for the wp-wetterturnier
    ///  plugin (returns the path to the plugin). Wrapper around the
    ///  wordpress-function plugins_url.
    ///
    /// @param $pluginname. String, default is `wp-wetterturnier` (name
    ///  of the plugin).
    /// @return Returns (string) the path to the plugin.
    // --------------------------------------------------------------
    function plugins_url( $pluginname = "wp-wetterturnier" ) {
        return( plugins_url($pluginname) );
    }

    // --------------------------------------------------------------
    /// @details Save 'next tournament' once. Used in various 
    ///  positions, e.g., the widgets.
    function load_current_tournament_once() {
       $this->current_tournament = $this->current_tournament(0,false,0,true);
    }

    // --------------------------------------------------------------
    /// @details An own number_format function to print well formatted
    ///  numbers (integers and floating point numbers). The
    ///  wetterturnier plugin offers some user defined options (See
    ///  wetterturnier plugin backend settings) where the thousand
    ///  separator and decimal separator can be specified. These
    ///  specifications are stored.
    ///
    /// @param $value. Numeric value which has to be formatted.
    /// @param $decimals. Integer, default is `2`. Number of decimal
    ///  values. Can also be 0.
    /// @return Returns a string containing the formatted number.
    // --------------------------------------------------------------
    function number_format($value,$decimals=2) {
        return number_format((float)$value,$decimals,$this->float_format->dsep,$this->float_format->tsep);
    }

    // --------------------------------------------------------------
    /// @details Adding css files (array) to the head of the wordpress
    ///  theme.
    // --------------------------------------------------------------
    function register_css_files() {
        if ( ! empty( $this->options->wetterturnier_style_deps ) & ! is_admin() )
        { $arr = array($this->options->wetterturnier_style_deps); }
        else
        { $arr = array(); }
        //!!! RETO TODO set to all . no dependencies. Dependency
        //still set in backend, wetterturnier plugin.
        $arr = array();
        // Now add
        foreach ( $this->css_files as $file ) {
            wp_register_style(  'wetterturnier_'.$file,
                    sprintf('%s/css/%s.css',$this->plugins_url(),$file),$arr);
            wp_enqueue_style( 'wetterturnier_'.$file);
        }
    }

    // --------------------------------------------------------------
    /// @details Adding js files (array) to the head of the wordpress.
    // --------------------------------------------------------------
    function register_js_files() {
        foreach ( $this->js_files as $file ) {
            wp_register_script(  'wetterturnier_'.$file,
                    sprintf('%s/js/%s.js',$this->plugins_url(),$file));
            wp_enqueue_script( 'wetterturnier_'.$file );
        }
    }

    // --------------------------------------------------------------
    /// @detials Adding js files to the head of the wordpress.
    ///  
    /// @param $file. Name of the javascript file without postfix!
    // --------------------------------------------------------------
    function register_js_file( $file ) {
        wp_register_script(  'wetterturnier_'.$file,
                sprintf('%s/js/%s.js',plugins_url('wp-wetterturnier'),$file));
        wp_enqueue_script( 'wetterturnier_'.$file );
    }

    // --------------------------------------------------------------
    /// @details Including a js script file outside the wordpress header.
    ///  Wont call wp_register_script, but includes the js file whereever
    ///  you need. Using this for special jquery functions only needed
    ///  for some special purposes, as e.g., the synopsymbols page. 
    ///  Benefit: the synopsymbols-jQuery code is not loaded globally
    ///  whenever a wordpress page will be opened which reduces the
    ///  response time. Drawback: only available where included. Therefore
    ///  some js scripts (e.g., used for the navigation) will be registered
    ///  in wordpress to make them available everywhere.
    ///
    /// @param $file. Name of the javascript file without postfix!
    // --------------------------------------------------------------
    function include_js_script( $name ) {
      printf("<script type='text/javascript' src='%s/js/%s.js'></script>",
             $this->plugins_url(),$name);
    }

    // ----------------------------------------------------------
    /// @details Returns current language if pll (polylang plugin)
    ///   is active. Else use `en_US` as default language.
    /// @param $value. String, default is `slug`. If set to `slug`
    ///   the language slug will be returned (e.g., `en` or `de`).
    ///   Can also be set to `name`. If set to `name` the
    ///   language name will be returned (e.g., `en_US`, `de_DE`).
    ///
    /// @todo Reto: if $vlaue is not slug or name: problem?
    // ----------------------------------------------------------
    function get_user_language( $value = 'slug' ) {
        // Getting language
        if ( is_callable("pll_current_language") ) {
            $user_language = pll_current_language( $value );
            if ( strlen((string)$user_language)==0 && $value == 'slug' ) { $user_language = 'en'; }
            else if ( strlen((string)$user_language)==0 ) { $user_language = 'en_US'; }
        } else {
            if ( $value == 'slug' ) {
                $user_language = 'en'; # default defined by wetterturnier plugin
            } else {
                $user_language = 'en_US'; # default defined by wetterturnier plugin
            }
        }
        return( $user_language );
    }

    // --------------------------------------------------------------
    /// Setting locale based on the active polylang slug
    // --------------------------------------------------------------
    function set_locale( $locale = false ) {
        $locale = $this->get_user_language( 'locale' );
        setlocale(LC_ALL,$locale);
    }

    // --------------------------------------------------------------
    /// @details Depending on the current language configuration (based
    ///   on the polylang plugin if active) the floating point number
    ///   format is specified here. Saves a stdClass object into the
    ///   parent class called `float_format`. This is mainly used by
    ///   the @ref number_format method.
    ///
    /// @see number_format
    /// @see load_date_format
    /// @see load_datetime_format
    // --------------------------------------------------------------
    function load_float_format() {
        $default = new stdClass(); $default->dsep = ","; $default->tsep = "";
        global $polylang;
        if ( function_exists("pll_current_language") ) {
           $fmt = new stdClass();
           $slug = pll_current_language("slug");
           $fmt->tsep = get_option(sprintf('wetterturnier_floattsep_format_%s',$slug));
           $fmt->dsep = get_option(sprintf('wetterturnier_floatdsep_format_%s',$slug));
           if ( strlen($fmt->dsep) == 0 ) { $fmt = $default; }
        } else { $fmt = $default; }
        $this->float_format = $fmt;
        return true;
    }

    // --------------------------------------------------------------
    /// @details The wetterturnier plugins also allows to define
    ///   language specific date formats. Based on the polylang
    ///   plugin (if active) the date format will be loaded as set
    ///   in the wetterturnier admin backend. If not found or
    ///   polylang is inactive, the default format `%Y-%m-%d` will be
    ///   used. Saves the `date_format` into the parent class and
    ///   is used to create user-friendly date format output on the
    ///   frontend. 
    ///
    /// @see load_float_format
    /// @see load_datetime_format
    // --------------------------------------------------------------
    function load_date_format() {
        $default = "%Y-%m-%d";
        global $polylang;
        if ( function_exists("pll_current_language") ) {
           $slug = pll_current_language("slug");
           $fmt = get_option(sprintf('wetterturnier_date_format_%s',$slug));
           if ( strlen($fmt) == 0 ) { $fmt = $default; }
        } else { $fmt = $default; }
        $this->date_format = $fmt;
        return true;
    }

    // --------------------------------------------------------------
    /// @details The wetterturnier plugins also allows to define
    ///   language specific datetime formats. Based on the polylang
    ///   plugin (if active) the date format will be loaded as set
    ///   in the wetterturnier admin backend. If not found or
    ///   polylang is inactive, the default format `%Y-%m-%d %H:%M:%S` will be
    ///   used. Saves the `datetime_format` into the parent class and
    ///   is used to create user-friendly datetime format output on the
    ///   frontend. 
    ///
    /// @see load_float_format
    /// @see load_date_format
    // --------------------------------------------------------------
    function load_datetime_format() {
        $default="%Y-%m-%d %H:%M:%S";
        global $polylang;
        if ( function_exists("pll_current_language") ) {
           $slug = pll_current_language("slug");
           $fmt = get_option(sprintf('wetterturnier_datetime_format_%s',$slug));
           if ( strlen($fmt) == 0 ) { $fmt = $default; }
        } else { $fmt = $default; }
        $this->datetime_format = $fmt;
        return true;
    }


    // --------------------------------------------------------------
    /// @details Converting $tdate (dates sincd  1970-01-01) to the format
    ///    specified. Can be used for specific date conversion like e.g.,
    ///    to print the day of the week or something.
    /// @param $tdate. Integer representation of the date.
    /// @param $fmt. String, format (see php date() manual). Default
    ///    Return format is `%Y-%m-%d %H:%M:%S`
    /// @return Returns string with the date in the format specified.
    // --------------------------------------------------------------
    public function convert_tdate( $tdate, $fmt = "%Y-%m-%d %H:%M:%S" ) {
        return( date( $fmt, (int)$tdate*86400 ) );
    }

    // --------------------------------------------------------------
    /// @details Small helper class to convert a tournament date
    /// into a string given a certain format.
    ///
    /// @param $tdate. Integer, days since 1970-01-01.
    /// @param $fmt. Format. Either a string (see php date manual page for
    ///   more details) or `NULL`. If `NULL` the `date_format` will
    ///   be used (@ref load_date_format).
    /// @see datetime_format
    /// @see number_format
    // --------------------------------------------------------------
    function date_format( $tdate, $fmt = NULL ) {
       if ( is_null($fmt) ) { $fmt = $this->date_format; }
       return( strftime( $fmt, (int)$tdate * 86400 ) );
    }

    // --------------------------------------------------------------
    /// @details Small helper class to convert a timestamp
    /// into a string given a certain format.
    ///
    /// @param $tdate. Integer, seconds since 1970-01-01.
    /// @param $fmt. Format. Either a string (see php date manual page for
    ///   more details) or `NULL`. If `NULL` the `datetime_format` will
    ///   be used (@ref load_datetime_format).
    /// @see datetime_format
    /// @see number_format
    // --------------------------------------------------------------
    function datetime_format( $tdate, $fmt = NULL ) {
       if ( is_null($fmt) ) { $fmt = $this->datetime_format; }
       return( strftime( $fmt, (int)$tdate ) );
    }

    // --------------------------------------------------------------
    /// @details Returns the web-link to the terms and conditions
    ///   pages which can be defined via the wetterturnier plugin
    ///   settings page in the admin backend.
    // --------------------------------------------------------------
    function get_terms_link() {
        global $polylang;
        if ( function_exists("pll_current_language") ) {
           $slug = pll_current_language("slug");
        } else { $slug = "en"; }
        $link = get_option(sprintf('wetterturnier_terms_link_%s',$slug));
        return $link;
    }

    // --------------------------------------------------------------
    /// @details Small insert or update workaround for wordpress as
    ///   a `insert on duplicate update` method is not yet implemented
    ///   in the wordpress core.
    /// @param $table. Name of the database table.
    /// @param $data. Array which consists of `key`/`value` pairs
    ///   where the `key` specifies the name of the column in the
    ///   database while `value` defines the value which should be
    ///   inserted.
    // --------------------------------------------------------------
    function insertonduplicate($table, $data, $updatecol = array(), $useprepare = True ) {
    
        global $wpdb;
    
        $formatted_fields = array();
        $onduplicate = array();
        foreach ( $data as $field=>$value ) {
            $formatted_fields[] = "%s";
            // If field name is in "updatecol": do not append
            // to ON DUPLICATE KEY UPDATE array.
            if ( ! in_array($field,$updatecol) ) {
               array_push( $onduplicate, $field );
            }
        }

        $sql = "INSERT INTO `$table` (`" . implode( '`,`', array_keys($data) )
              ."`) VALUES "
              ." ('" . implode( "','", $formatted_fields ) . "')"
              ." ON DUPLICATE KEY UPDATE ";
        for ( $i = 0; $i < count($onduplicate); $i++ ) {
            $onduplicate[$i] = sprintf("`%s` = VALUES(`%s`)",
                  $onduplicate[$i], $onduplicate[$i] );
        }
        $sql = sprintf("%s %s",$sql,implode(",",$onduplicate));

        // There is one problem with the prepare function: it does not take
        // NULL and converts it to '0'. If $useprepare is set to False we ignore
        // the wpdb->prepare and do it manually.
        if ( $useprepare ) {
           return $wpdb->query( $wpdb->prepare( $sql, $data) );
        } else {
            $dbvals = array();
            // Define the sql 'values' string once
            foreach ( array_keys($data) AS $v ) { array_push($dbvals,sprintf("`%s`",$v)); }
            // Combine
            $dbvals = sprintf("(%s)",join(",",$dbvals));
            // If $updatecol is NULL or empty: append all
            $add = ( (is_null($updatecol) | count($updatecol) === 0) ? true : false );

            // For each entry: define the data array
            #printf("VALS:   %s<br>\n",$dbvals);
            $dbdata   = array();
            $dbupdate = array();
            foreach ( $data as $key=>$elem ) {
               $type = gettype($elem);
               switch( (string)$type ) {
                  case "string":
                     $tmp = sprintf("'%s'",$elem);
                     array_push($dbdata,$tmp);
                     if ( $add | in_array($key,$updatecol) ) { $dbupdate[$key] = $tmp; }
                     break;
                  case "double":
                     $tmp = number_format($elem,2,".","");
                     array_push($dbdata,$tmp);
                     if ( $add | in_array($key,$updatecol) ) { $dbupdate[$key] = $tmp; }
                     break;
                  case "integer":
                     $tmp = number_format($elem,0,".","");
                     array_push($dbdata,$tmp);
                     if ( $add | in_array($key,$updatecol) ) { $dbupdate[$key] = $tmp; }
                     break;
                  case "NULL":
                     $tmp = "NULL";
                     array_push($dbdata,$tmp);
                     if ( $add | in_array($key,$updatecol) ) { $dbupdate[$key] = $tmp; }
                     break;
                  default:
                     die("UNKNOWN DATA TYPE IN insertonduplicate WITH \$useparepare=false");
               }
            }

            // Combine
            $dbdata = sprintf("(%s)",join(",",$dbdata));

            $sql = sprintf("INSERT INTO `%s` %s VALUES %s",
                  $table, $dbvals, $dbdata);
            $sqlupdate = array();
            foreach ( $dbupdate as $key=>$val ) {
               array_push($sqlupdate,sprintf("%s = %s",$key,$val));
            }
            $sql = $sql." ".sprintf(" ON DUPLICATE KEY UPDATE %s",join(", ",$sqlupdate));
            //printf("<br><br>\n\n%s<br>\n",$sql);
            return $wpdb->query( $sql );
        }
    }

    // --------------------------------------------------------------
    /// @details Checks which one is the next tournament date
    ///   dayoffset can be used to get 'todays tournament'
    /// @param $row_offset. Default is `0`. Can be set to (any)
    ///   if set to `1` the function won't return the next tournament
    ///   but the one after. Please have a look to input $backwards.
    ///   If `backwards=true` this $row_offset can be used to get the
    ///   previous rather than the upcoming tournament.
    /// @param $check_access. Boolean, default  is `true`. Checks
    ///   whether the visitor has access to the data of this 
    ///   tournament. This is important as we don't want the user
    ///   to see the bets/forecasts of other competitors before the
    ///   bet form is closed and the tournament has been started.
    /// @param $dayoffset. Default `0`.
    /// @param $backwards. Boolean, default `false`. If `false`
    ///   we are looking forward in time if e.g., $row_offset is
    ///   set. If `true` we are looking/searching backwards to e.g.,
    ///   get the `last tournament date` rather than the `next`.
    /// @return Returns a stdClass object containing the requrested
    ///   tournament date and some more information (e.g., wheter
    ///   the user has access to the data for the requested tournament
    ///   date or the dates of the `bet days` where the forecasts will
    ///   be placed and so far and so on.
    ///
    /// @see next_tournament
    /// @see current_tournament
    /// @see latest_tournament
    /// @see older_tournament
    /// @see newer_tournament
    // --------------------------------------------------------------
    public function next_tournament($row_offset=0,$check_access=true,$dayoffset=0,$backwards=false) { 

        //printf("<br>Calling next_tournament with row_offset = %d,  check_access = %s,   dayoffset = %d,  and backwars = %s<br>\n",
        //       $row_offset,($check_access ? 'true' : 'false'), $dayoffset, ($backwards ? 'true' : 'false'));

        global $wpdb;

        // If dayoffset is bigger than 100 we expect that
        // it is a tournament date (days since 1970) and
        // therefore take this one.
        if ( (int)$dayoffset > 100 ) {
           $today = (int)$dayoffset;
        } else {
           $today = (int)floor(gmdate('U') / 86400. ) + $dayoffset;
        }

        // Loading next tournament row
        // NOTE there are two modes. Default is backwards == false menas that the system
        // is searching for the tournaments in the future. If backwards
        // is true (actually only ued by current_tournament method) searches
        // backwards in time.
        if ( ! $backwards ) {
            $sql = sprintf("SELECT tdate FROM %swetterturnier_dates "
                          ."WHERE tdate >= %d AND status = 1 ORDER BY tdate ASC LIMIT %d",
                          $wpdb->prefix,$today,$row_offset + 1);
            $rows = $wpdb->get_results( $sql );
        } else {
            $sql = sprintf("SELECT tdate FROM %swetterturnier_dates "
                          ."WHERE tdate <= %d AND status = 1 ORDER BY tdate DESC LIMIT %d",
                          $wpdb->prefix,$today,$row_offset + 1);
            $rows = $wpdb->get_results( $sql );
        }

        // - No rows? Return "closed = true"
        //   Do the same if the db returned less rows
        //   then the requested offset!
        if ( ! $rows || count($rows) < $row_offset ) {
            $next = new stdClass();
            $next->closed = true;
            $next->debug = "Nothing found in database";
            return $next;
        }

        // Else take row with offset, if set
        $row = $rows[$row_offset];

        // Generate return object
        $next = new stdClass();
        $next->tdate    = $row->tdate;
        //$next->readable = gmdate($this->date_format,$next->tdate * 86400. );
        //$next->weekday  = gmdate('l',$next->tdate * 86400. );
        $next->readable = $this->date_format( $next->tdate );
        $next->weekday  = $this->date_format( $next->tdate, "%A" );

        // if row_offset == 0, check if open or allready closed.
        // if row_offset >= 1, the form is allways closed! Do NOT
        // call check_view_is_closed because this will
        // crash (loop inside the class)
        if ( $row_offset == 0 & $check_access ) {
            $next->closed = $this->check_view_is_closed( $row->tdate, $next );
        } else if ( $row_offset > 0 ) {
            $next->closed = true;
        } else {
            $next->closed = false;
        }

        // Adding tournament date and the two bet dates
        $next->tdate = $row->tdate;
        $next->betdate_day1   = $row->tdate + 1;
        $next->betdate_day2   = $row->tdate + 2;
        //$next->day1           = gmdate($this->date_format,($next->tdate+1) * 86400. );
        //$next->day2           = gmdate($this->date_format,($next->tdate+2) * 86400. );
        $next->day1           = $this->date_format((int)$next->tdate+1);
        $next->day2           = $this->date_format((int)$next->tdate+2);

        return $next;
    }

    // ------------------------------------------------------------------
    /// @details This method is based on @ref next_tournament and returns
    ///   the current/last turnament. This is used to show
    ///   current bets/observations.
    /// @param $row_offset. Positive integer, default `0` (no row offset)
    /// @param $check_access. Boolean, default `true`.
    /// @param $dayoffset. Integer, default `-2`. We are forecasting two
    ///   days at the moment. `$dayoffset-2` uses 'today - 2 days' to
    ///   find the current tournament.
    ///
    /// @see next_tournament
    /// @see latest_tournament
    /// @see older_tournament
    /// @see newer_tournament
    ///
    /// @todo Reto should use the 'number of bet days' variable rather
    ///   than this fixed number of -2.
    // ------------------------------------------------------------------
    public function current_tournament($row_offset=0,$check_access=true,$dayoffset=-2,$backwards=false) { 
        return $this->next_tournament($row_offset,$check_access,$dayoffset,$backwards);
    }

    // ------------------------------------------------------------------
    /// @details This method is based on @ref next_tournament and returns
    ///   the latest (last) tournament based on $tdate. Please check
    ///   the next_tournament method to see what $tdate can be (either
    ///   "day_offset" or explicit "tournament date"). 
    /// @param Please check the @ref next_tournament method to see what $tdate
    ///   can be (either "day_offset" or explicit "tournament date").
    ///
    /// @see next_tournament
    /// @see current_tournament
    /// @see older_tournament
    /// @see newer_tournament
    // --------------------------------------------------------------
    public function latest_tournament($tdate) {
        return( $this->next_tournament(0,false,$tdate,$backwards=true) );
    }

    // ------------------------------------------------------------------
    /// @details This method is based on @ref next_tournament and returns
    ///   the tournament before the one specified by input $tdate.
    /// @param Please check the @ref next_tournament method to see what $tdate
    ///   can be (either "day_offset" or explicit "tournament date").
    ///
    /// @see next_tournament
    /// @see current_tournament
    /// @see latest_tournament
    /// @see newer_tournament
    // --------------------------------------------------------------
    public function older_tournament($tdate) {
        return( $this->next_tournament(1,false,$tdate,$backwards=true) );
    }

    // --------------------------------------------------------------
    /// @details This method is based on @ref next_tournament and returns
    ///   the tournament after thie current one (so the next upcoming one)
    ///   depending on the input $tdate.
    /// @param Please check the @ref next_tournament method to see what $tdate
    ///   can be (either "day_offset" or explicit "tournament date").
    ///
    /// @see next_tournament
    /// @see current_tournament
    /// @see latest_tournament
    /// @see older_tournament
    // --------------------------------------------------------------
    public function newer_tournament($tdate) {
        return( $this->next_tournament(1,false,$tdate,$backwards=false) );
    }
    

    // ------------------------------------------------------------------
    /// @details We allow the user to enter and save the forecasts
    ///   partially. As soon as all required fields are provided (all
    ///   forecasts filled in and submitted) the wetterturnier plugin
    ///   appends a row in the betstat database table. Note that this
    ///   line will be deleted if the user decides to delete one or more
    ///   values and stores them as empty. This function checks wheter
    ///   the row in the betstat table exists or not. If it exists the
    ///   function will return `true`, else `false` (not submitted or only
    ///   partially submitted).
    /// @param $userID. Integer, numeric ID of the user.
    /// @param $cityObj. Object of class @wetterturnier_cityObject.
    /// @param $tdate. Integer, date of the tournament.
    /// @return Returns `true` if the user successfully submitted the
    ///   forecast (all values) and `false` else.
    // ------------------------------------------------------------------
    public function check_bet_is_submitted($userID,$cityObj,$tdate) {

        global $wpdb;
        $return = new stdClass();
        $return->submitted = NULL;
        $return->placed    = NULL;
        // Check if we have received full bet (all fine)
        $res = $wpdb->get_row( sprintf("SELECT submitted FROM %swetterturnier_betstat "
               ." WHERE userID = %d AND cityID = %d AND tdate = %d",
               $wpdb->prefix, $userID, $cityObj->get('ID'), $tdate));
        if ( ! $res )                                 { $return->submitted = false; }
        else if ( ! $res->submitted )                 { $return->submitted = false; }
        else if ( strtotime( $res->submitted ) < 0 )  { $return->submitted = false; }
        else                                          { $return->submitted = $res->submitted; }

        // Else check when the last submission was (bets table)
        $res = $wpdb->get_row( sprintf("SELECT max(placed) AS placed FROM %swetterturnier_bets "
                   ." WHERE userID = %d AND cityID = %d AND tdate = %d",
                   $wpdb->prefix, $userID, $cityObj->get('ID'), $tdate));
        if ( ! $res )                                 { $return->placed = false; }
        else if ( strtotime( $res->placed ) < 0 )     { $return->placed = false; }
        else                                          { $return->placed = $res->placed; }
        return( $return );

    }


    // ------------------------------------------------------------------
    /// @details Checks whether the current user is in a specific group
    ///   or not (is group member).
    /// @param $userID. Integer, numeric ID of the user.
    /// @param $groupName. Name of the group to check.
    /// @return Returns `true` if the user is a member of the group and
    ///   `false` else. 
    // ------------------------------------------------------------------
    public function check_user_is_in_group( $userID, $groupName ) {
        global $wpdb;
        $res = $wpdb->get_row(
                    sprintf("SELECT gu.active FROM %swetterturnier_groups AS g "
                           ."LEFT OUTER JOIN %swetterturnier_groupusers AS gu "
                           ."ON g.groupID = gu.groupID "
                           ."WHERE g.groupName = '%s' AND gu.userID = %d",
                            $wpdb->prefix,$wpdb->prefix,$groupName,$userID));

        if ( ! $res ) { return false; }
        if ( ! $res->active == 1 ) { return false; }
        return true;
    }

    // ------------------------------------------------------------------
    /// @details Returns names of the groups the user is a member of.
    /// @param $userID. Integer, numeric user ID.
    /// @return Returns `false` if the user is not yet a member of
    ///   at least one group and a stdClass object containing the group
    ///   ID and group names for all groups where the user is a member of.
    // ------------------------------------------------------------------
    public function get_groups_for_user( $userID ) {
        global $wpdb;
        $res = $wpdb->get_results(
                    sprintf("SELECT gu.ID, g.groupName FROM %swetterturnier_groups AS g "
                           ."LEFT OUTER JOIN %swetterturnier_groupusers AS gu "
                           ."ON g.groupID = gu.groupID "
                           ."WHERE gu.userID = %d AND gu.active = 1",
                            $wpdb->prefix,$wpdb->prefix,$userID));

        if ( ! $res ) { return false; }
        return $res; 
    }

    // ------------------------------------------------------------------
    /// @details Returns a stdClass object with all information about a
    ///   certain user specified by it's numeric ID.
    /// @param $userID. Integer, numeric user ID.
    /// @return stdClass containing the detailed user information.
    ///
    /// @see get_user_by_username
    // ------------------------------------------------------------------
    public function get_user_by_ID( $userID ) {
        global $wpdb;
        ///$res = $wpdb->get_results(sprintf('SELECT * FROM %susers '
        $res = $wpdb->get_row(sprintf('SELECT * FROM %susers '
                  .' WHERE ID = %d',$wpdb->prefix,(int)$userID));
        if ( ! $res ) { return false; }
        return $res;
    }

    // ------------------------------------------------------------------
    /// @details Returns user details based on user login name.
    ///   Not case sensitive.
    /// @param $username. String containing the user login name.
    /// @return stdClass containing the detailed user information.
    ///
    /// @see get_user_by_ID
    // ------------------------------------------------------------------
    public function get_user_by_username( $username ) {
        global $wpdb;
        $res = $wpdb->get_row(sprintf('SELECT * FROM %susers '
                  .' WHERE LOWER(user_login) = "%s"',$wpdb->prefix,
                  strtolower(trim($username))));
        if ( ! $res ) { return false; }
        return $res;
    }


    // ------------------------------------------------------------------
    /// @details Sometimes I have to add a special userclass to some elements
    ///   to display them as I want. Therefore there is a small method
    ///   returning the userclass based on the uderID and the user_login
    ///   name.
    ///   The same yields for the username. We replace some special
    ///   character strings with another string.
    ///   e.g. GRP_ will be replaced but then we add [group] to the end.
    /// @param $userID. Integer, numeric user ID.
    /// @param $user_login. String containing the user_login name.
    /// @return stdClass containing two strings. `userclass` contains
    ///   the main class (automat, referenz, mitteltip, or Sleepy), 
    ///   `username` the modified username.
    // ------------------------------------------------------------------
    public function get_user_display_class_and_name($userID,$usr) {
       $username = $usr->user_login;
       // Check if user is Automat or mix or so
       if      ( $this->check_user_is_in_group($userID, 'Automaten') ) {
          $userclass = 'automat';
       } elseif      ( $this->check_user_is_in_group($userID, 'Referenztipps') ) {
          $userclass = 'referenz';
       } else if ( substr($usr->user_login,0,4) == 'GRP_' ) {
          $userclass = 'mitteltip';
          $username  = str_replace('GRP_','',$usr->user_login).' '.__('[group]','wpwt');
       } else if ( strtolower($usr->user_login) == 'Sleepy' ) {
          $userclass = 'Sleepy';
          $username  = sprintf('%s <span></span>',$usr->user_login);
       } else { $userclass = 'player'; }
       $res = new stdClass();
       $res->userclass    = $userclass;
       $res->display_name = $username;
       $res->user_login   = $usr->user_login;
       return( $res );
    }

    // ------------------------------------------------------------------
    /// @details Returns the link to the profile page of the player.
    /// @param $username. String containing the user login name. Case sensitive!
    /// @return Returns `<a href=...>...</a>` tag with the link to the
    ///   user profile (currently to bbpress /forum/users/<username>).
    // ------------------------------------------------------------------
    public function get_user_profile_link( $usr ) {
       if ( is_null($usr->display_name) ) {
          $link = sprintf("<a href=\"/forums/users/%s/\" target=\"_self\">%s</a>",
                          $usr->user_login, $usr->user_login);
       } else {
          $link = sprintf("<a href=\"/forums/users/%s/\" target=\"_self\">%s</a>",
                          $usr->user_login, $usr->display_name);
       }
       return( $link );
    }

    // ------------------------------------------------------------------
    /// @details Function which checks if we can show the bet-form or not. 
    ///   If user is allowed to see the data, function returns true.
    ///   Else return value is false and the function places some notes.
    /// @param $tdate. Integer date value of a certain tournament.
    /// @return Prints a message and returns `true` if the view is closed
    ///   and we do not allow the user to retreive the data at the moment
    ///   (maybe locked because the tournament has not been started yet).
    ///   or `false` (not locked) if to grant access.
    ///
    /// @see check_allowed_to_display_betdata
    // ------------------------------------------------------------------
    function check_view_is_closed($tdate) {

    
        // STOP if user should not see these data!
        $today = (int)floor(gmdate('U')/86400.);
        $tdate_string =  strftime('%Y-%m-%d',$tdate*86400);
        $open_string =  strftime('%Y-%m-%d %H:%M UTC',($tdate-(int)$this->options->wetterturnier_bet_open_days)*86400);

        // Is there a running wetterturnier? 
        // The tournament is always for the next two days, give the user
        // a forward link to the tournament if there is a running
        // tournament at the moment.
        // WARNING: the check_access = false is very important not to
        // create a loopback!!!!
        $check =  (int)(floor(gmdate('U')/86400)) - $this->older_tournament(0,false)->tdate;
        if ( $check >= 0 and $check <= 2 ) {
            $info = "<div class=\"wetterturnier-info ok\">\n"
                   .__("There is an ongoing tournament right now.","wpwt")."<br>\n"
                   .__("Current values can be found here:","wpwt")."<br>\n"
                   ."<a href=\"/current\" target=\"_self\">".__("Link ... ","wpwt")."</a>"
                   ."</div>\n";
        }
        // If user is too early (bet form opens X days before, see 
        // plugin settings) return false.
        if ( $today < ( $tdate - $this->options->wetterturnier_bet_open_days) ) {
            echo "<div class=\"wetterturnier-info error\">\n";
            printf("%s.<br>\n",__("Sorry, no access to these bet form","wpwt"));
            printf("%s.<br>\n",sprintf(__("The form to submit tips always opens %d days in advance.","wpwt"),
                     $this->options->wetterturnier_bet_open_days));
            printf("%s.<br>\n",sprintf(__("Next tournament will take place on %s","wpwt"),
                     $this->date_format($tdate))); 
            printf("%s.\n",sprintf(__("The bet form opens %s","wpwt"),
                     $this->datetime_format(86400*$tdate)));
            echo "</div>\n";

            return( true ); # closed true 
        }
        // Locked again
        if ( (int)gmdate('U') > $this->options->wetterturnier_bet_closing_timestamp ) {
            $nextnext = $this->next_tournament($row_offset=1);
            echo "<div class=\"wetterturnier-info error\">\n";
            printf("%s<br>\n",__("Sorry, no access to these bet form.","wpwt"));
            printf("%s<br>\n",__("The form to submit tips for todays tournament is allready closed.","wpwt"));
            printf("%s<br>\n",sprintf("%s %s, %s.",__("The next tournament will take place on %s","wpwt"),
                     $nextnext->weekday, $nextnext->readable));
            echo "</div>\n";

            return( true ); # closed true 
        }
        return( false ); # closed = false (not closed)
    
    }


    // ------------------------------------------------------------------
    /// @details Function which checks if we can show the placed bets/archive data 
    ///   If user is allowed to see the data, function returns true.
    ///   Else return value is false and the function places some notes.
    /// @param $tdate. Integer date value of a certain tournament.
    /// @param $showinfo. Boolean, default true. If set to false the
    ///   user-messages "sorry not access" are suppressed. This is used
    ///   to not show the messages twice for two consecutive days.
    /// @return Prints a message and returns `true` if the view is closed
    ///   and we do not allow the user to retreive the data at the moment
    ///   (maybe locked because the tournament has not been started yet).
    ///   or `false` (not locked) if to grant access.
    ///
    /// @see check_view_is_closed
    // ------------------------------------------------------------------
    function check_allowed_to_display_betdata($tdate,$showinfo=true) {
    
        // STOP if user should not see these data!
        $today = floor(gmdate('U')/86400.);
        if ( $today < $tdate ) {
            echo "<div class=\"wetterturnier-info error\">\n";
            echo __("The requested tournament date is in the future.","wpwt")."<br>\n";
            echo __("Do you try to cheat, bro?","wpwt")."<br>\n";
            echo "</div>\n";
            return false;
        }
        // If it is today we have to be sure that the bet-form
        // is closed now.
        if ( $tdate == $today ) {
    
            // How many minuts until you can see the data?
            $now  = gmdate('U');
            $lock = strftime('%Y-%m-%d',$tdate*86400.)." ".(int)$this->options->wetterturnier_bet_closingtime;
            $lock = strptime($lock,'%Y-%m-%d %H%M');
            $lock = mktime($lock['tm_hour'], $lock['tm_min'], $lock['tm_sec'],
                           $lock['tm_mon']+1, $lock['tm_mday'], $lock['tm_year']+1900);
            $lock = $lock + 60.*(int)$this->options->wetterturnier_bet_closingoffset;
            $diff = ceil( abs($lock - $now) / 60. );

            // ----------------------------------------------------------
            // If lock is smaller than now show message and stop.
            // ----------------------------------------------------------
            if ( (int)$now < (int)$lock ) {
               if ( $showinfo ) {
                  echo "<div class=\"wetterturnier-info error\">\n";
                  echo __("Sorry, no access to these data.","wpwt")."<br>\n";
                  echo __("We will show the current bets as soon as the bet-form has been closed.","wpwt")."<br>\n";
                  if ( $diff < 600 ) {
                      if ( $diff <= 60 ) {
                          echo __("You can see the newest tips in ","wpwt")." ".$diff." ".__("minutes","wpwt").".<br>\n";
                      } else {
                          $diff_hour = floor( $diff / 60 );
                          $diff_min  = $diff - $diff_hour*60;
                          if ( (int)$diff_hour == 1 ) { $str_hour = sprintf(" %s ",__("hour"));   } else { $str_hour = sprintf(" %s ",__("hours","wpwt")); }
                          if ( (int)$diff_min  == 1 ) { $str_min  = sprintf(" %s ",__("minute")); } else { $str_min  = sprintf(" %s ",__("minutes","wpwt")); }
                          echo __("You can see the newest tips in ","wpwt")." ".(int)$diff_hour.$str_hour.__("and","wpwt")." "
                                  .$diff_min.$str_min.".<br>\n";
                      }
                  }
                  echo "</div>\n";

                  # - Give a hint where to find the tournament bet form
                  echo "<div class=\"wetterturnier-info ok\">\n";
                  echo __("If you would like to submitt a bet please go to the \"SUBMISSION-FORM\" in the navigation.","wpwt")."<br>\n";
                  echo "</div>\n";
               }
               return false;
            }
        }
        return true;
    
    }


    // --------------------------------------------------------------
    /// @details Creates the ultra freaky SQL query for the ranking
    ///    and returns all the data and all necessary information.
    ///    The ranking data will be stored on `data`. There are several
    ///    additional properties such as `maxpoints` (max points to reach),
    ///    `tdate_count` (the number of individual tournament dates the
    ///    ranking is based on). 
    ///    This is used in users/views/ranking.php.
    ///
    /// @param $cityObj. Object of class wetterturnier_cityObject.
    ///    Can be either a single object containing
    ///    city information, or an array of @ref wetterturnier_cityObject
    ///    objects containing more than only one city. The array option is
    ///    used to create the 3-city ranking (could also be used to do
    ///    a 5-city-ranking or something similar). 
    /// @param $tdate. Integer representation of the tournament date.
    /// @param $limit. Either boolean (`false`) to set no limit (all
    ///    entries will be shown, full ranking) or a positive integer.
    ///    If positive integer only the best N players will be loaded.
    /// @return Returns a (quite big) stdClass object containing all
    ///    required information to show the ranking.
    // --------------------------------------------------------------
    public function get_ranking_data($cityObj,$tdate,$limit=false) {

        global $wpdb;

        // Generate the city-slug with all city ID's we need. 
        // With this approach we can use this method to either
        // compute the ranking for one town OR for a combination of
        // different towns for the 3-town ranking for example.
        if      ( is_object( $cityObj ) )  { $city_array = array($cityObj); }
        else if ( is_array( $cityObj ) )   { $city_array = $cityObj;        }
        else { die('Problems in get_ranking_data. Cannot understand input <city>.'); }
       
        // Prepare city slug for the sql statement and count
        // the elements in the city_array (needed to compute
        // the total number of points reachable).
        for ( $i=0; $i < count($city_array); $i++ )
        { $city_array[$i] = sprintf('cityID=%d',$city_array[$i]->get('ID')); }
        $city_slug  = "(".join(" OR ",$city_array).")";
        $city_count = count($city_array);

        // Prepare the tdate. If tdate is empty (false) then take
        // tdate_first = tdate_last = latest tournament. If tdate
        // is a single integer, take this.
        // If tdate is an array, take min/max array.
        if      ( is_object( $tdate ) )  { $tdate_array = array((int)$tdate->tdate); }
        else if ( is_integer( $tdate ) ) { $tdate_array = array((int)$tdate); }
        else if ( is_array( $tdate ) )   { $tdate_array = $tdate; }
        else { die('Problems in get_ranking_data. Cannot understand input <tdate>.'); }
        $tdate_first = min($tdate_array);
        $tdate_last  = max($tdate_array);

        // Count number of tournaments between these two dates
        $tdsql = 'SELECT tdate FROM %swetterturnier_betstat WHERE %s '
                .'AND tdate >= %d AND tdate <= %d AND rank IS NOT NULL GROUP BY tdate';
        $tdsql = sprintf($tdsql,$wpdb->prefix,$city_slug,(int)$tdate_first,(int)$tdate_last);
        $tdate_count = count($wpdb->get_results($tdsql));

        // Loading userID for the Sleepy player (to compute points
        // for players without a bet).
        $sleepy = $this->get_user_by_username('Sleepy');
        if ( ! $sleepy ) { die('Could not find userID for Sleepy! Stop! Error!'); }

        // [1] create the sql_usrdate statement.
        //     This statement gets a full but unique
        //     list of CITY/DATE/USER. We need this
        //     to fill in the points and sleepy points later on.
        $sql_usrdate = array();
        array_push($sql_usrdate,"      SELECT dateUsr.cityID, dateUsr.userID, dateDate.tdate FROM (");
        array_push($sql_usrdate,"         SELECT cityID, userID FROM %swetterturnier_betstat");
        array_push($sql_usrdate,"         WHERE tdate >= %d AND tdate <= %d AND %s");
        array_push($sql_usrdate,"         GROUP BY cityID, userID");
        array_push($sql_usrdate,"      ) AS dateUsr CROSS JOIN (");
        array_push($sql_usrdate,"         SELECT tdate FROM %swetterturnier_betstat");
        array_push($sql_usrdate,"         WHERE tdate >= %d AND tdate <= %d AND %s");
        array_push($sql_usrdate,"         AND userID=%d GROUP BY tdate");
        array_push($sql_usrdate,"      ) AS dateDate");
        
        //print "<div style='font-weight:bold;'>[1] sql_usrdate</div>";
        //printf( join("<br>\n",$sql_usrdate),
        //        $wpdb->prefix,(int)$tdate_first,(int)$tdate_last,$city_slug,
        //        $wpdb->prefix,(int)$tdate_first,(int)$tdate_last,$city_slug,(int)$sleepy->ID);
        $sql_usrdate = sprintf( join("\n",$sql_usrdate),
                       $wpdb->prefix,(int)$tdate_first,(int)$tdate_last,$city_slug,
                       $wpdb->prefix,(int)$tdate_first,(int)$tdate_last,$city_slug,(int)$sleepy->ID);
        //sprintf("<div style='font-weight:bold;'>%d</div>",
        //      count($wpdb->get_results($sql_usrdate)));
        
        
        // [2] The second thing we need are the points of the sleepy.
        //     The sleepy is the user containing the points for all the
        //     players not having inserted a bet and therefore get the
        //     mean-std points for this weekend.
        $sql_dead = array();
        array_push($sql_dead,"      SELECT cityID, tdate, points_d1, points_d2, points");
        array_push($sql_dead,"      FROM %swetterturnier_betstat WHERE userID=%d");
        array_push($sql_dead,"      AND %s AND tdate>=%d AND tdate <=%d");
        
        //print "<div style='font-weight:bold;'>[2] sql_dead</div>";
        //printf( join("<br>\n",$sql_dead),
        //        $wpdb->prefix,(int)$sleepy->ID,$city_slug,(int)$tdate_first,(int)$tdate_last);
        $sql_dead = sprintf( join("\n",$sql_dead),
                       $wpdb->prefix,(int)$sleepy->ID,$city_slug,(int)$tdate_first,(int)$tdate_last);
        //printf("<div style='font-weight:bold;'>%d</div>",
        //      count($wpdb->get_results($sql_dead)));
        
        // [3] Now we have the sleepy and the USER/DADTE/CITY combination.
        //     We can combine now the points based on USR/DATE/CITY from
        //     the user itself and the sleepy. Note: not yet the sum
        //     over the periode tdate_first to tdate_last. There is
        //     a step [4] to get the sum of points.
        $sql_points = array();
        array_push($sql_points,"   SELECT dt.cityID, dt.userID, dt.tdate,");
        array_push($sql_points,"   CASE WHEN data.points IS NULL THEN 0 ELSE 1 END AS played,");
        array_push($sql_points,"   COALESCE(data.points_d1, dead.points_d1) AS points_d1,");
        array_push($sql_points,"   COALESCE(data.points_d2, dead.points_d2) AS points_d2,");
        array_push($sql_points,"   COALESCE(data.points, dead.points) AS points");
        array_push($sql_points,"   FROM (");
        array_push($sql_points,"      %s");
        array_push($sql_points,"   ) AS dt LEFT JOIN (");
        array_push($sql_points,"      %s");
        array_push($sql_points,"   ) AS dead ON dt.tdate=dead.tdate AND dt.cityID=dead.cityID");
        array_push($sql_points,"   LEFT OUTER JOIN %swetterturnier_betstat AS data ON dt.cityID=data.cityID");
        array_push($sql_points,"   AND dt.userID=data.userID AND dt.tdate=data.tdate");
        
        //print "<div style='font-weight:bold;'>[3] sql_points</div>";
        //printf( join("<br>\n",$sql_points),$sql_usrdate,$sql_dead,$wpdb->prefix );
        $sql_points = sprintf( join("\n",$sql_points),$sql_usrdate,$sql_dead,$wpdb->prefix );
        //printf("<div style='font-weight:bold;'>%d</div>",
        //      count($wpdb->get_results($sql_points)));
        
        // [4] No create sums of points. This then gives us the
        //     final sql statement for the ranking tables.
        $sql = array();
        array_push($sql,"SELECT usr.user_login AS user_login, usr.display_name AS display_name,");
        array_push($sql,"x.cityID AS cityID, x.userID AS userID, SUM(x.played) AS played,");
        array_push($sql,"SUM(x.points_d1) AS points_d1,");
        array_push($sql,"SUM(x.points_d2) AS points_d2,");
        array_push($sql,"SUM(x.points) AS points FROM (");
        array_push($sql,"   %s");
        array_push($sql,") AS x LEFT OUTER JOIN %susers AS usr");
        array_push($sql,"ON usr.ID = x.userID ");
        # TODO RETO: wenn ich den ruasnehmen integriert er wohl auch halbe tips
        # also den played > 0.
        # (nicht vollstaendige tips) in die rankings? Oder nicht?

        //array_push($sql,"WHERE played > 0 ");
        array_push($sql,"GROUP BY x.userID ORDER BY points DESC, points_d1 DESC, points_d2 DESC");
        if ( is_numeric($limit) ) {
            array_push($sql,sprintf("LIMIT %d",(int)$limit));
        }
        
        //print "<div style='font-weight:bold;'>[4] final sql command</div>";
        //printf( join("<br>\n",$sql),$sql_points,$wpdb->prefix );
        $sql = sprintf( join("\n",$sql),$sql_points,$wpdb->prefix );

        // If multi-city-ranking is requested (count($city_array)>1) we
        // would like to show only these who have had played for all cities!
        if ( count($city_array) > 1 ) {
            $sql = sprintf("SELECT * FROM (%s) AS tmp WHERE played=%d ORDER BY points DESC",
                           $sql,count($city_array));
        }
        #print "<br><br>".$sql."<br><br>\n";

        ##printf("<div style='font-weight:bold;'>%d</div>",
        ##      count($wpdb->get_results($sql)));

        // Last but not least store all the data into a new
        // stdClass and return the results.
        $result = new stdClass();
        $result->data = $wpdb->get_results($sql);
        $result->dataLength = count($result->data);
        $result->tdate_first = $tdate_first;
        $result->tdate_last  = $tdate_last;
        $result->city_array  = $city_array;
        $result->city_count  = $city_count;
        $result->tdate_count = $tdate_count;
        // Total number of points reachable in the ranking 
        // of a weekend (200) times number of weeks and
        // number of cities in the ranking. As an example:
        // If you compute the 'total ranking' for '3 towns'
        // total number to reach is 200*15*3 or in code
        $result->maxpoints   = 200. * (float)$city_count * (float)$tdate_count;

        return($result);

    }

    // --------------------------------------------------------------
    /// @details Returns parameter details from the database given
    ///   a parameter ID.
    ///
    /// @param $ID. Integer, numeric parameter ID.
    /// @return Returns stdClass object with all the information
    ///   in the corresponding row in the database or boolean `false`
    ///   if the parameter cannot be found.
    // --------------------------------------------------------------
    public function get_param_by_ID($ID) {
        global $wpdb;
        $res = $wpdb->get_row(sprintf("SELECT * FROM %swetterturnier_param WHERE paramID = %d",
                             $wpdb->prefix,(int)$ID));
        if ( ! $res ) { return(false); } else { return($res); }
    }
    // --------------------------------------------------------------
    /// @details Returns parameter details from the database given
    ///   a parameter name (e.g., RR, Wn, Wv, ...).
    ///
    /// @return Returns stdClass object with all the information
    ///   in the corresponding row in the database or boolean `false`
    ///   if the parameter cannot be found.
    // --------------------------------------------------------------
    public function get_param_by_name($paramName) {
        global $wpdb;
        $res = $wpdb->get_row(sprintf("SELECT * FROM %swetterturnier_param WHERE UPPER(paramName) = \"%s\"",
                             $wpdb->prefix,strtoupper((string)$paramName)));
        if ( ! $res ) { return(false); } else { return($res); }
    }
    // --------------------------------------------------------------
    /// @details Returns the numeric parameter ID given the parameter
    ///   name as specified in the database.
    ///
    /// @param $name. String, name of the parameter in the database.
    /// @return Integer parameter ID or boolean `false` if the parameter
    ///   cannot be found.
    // --------------------------------------------------------------
    public function get_param_ID($name) {
        global $wpdb;
        $res = $wpdb->get_row("SELECT paramID FROM ".$wpdb->prefix."wetterturnier_param "
                             ." WHERE paramName = \"".$name."\"");
        if ( ! $res ) { return(false); } else { return($res->paramID); }
    }

    // --------------------------------------------------------------
    /// @details Returns an array of objects containing all pairs of
    ///   `paramID` and `paramName` (numeric parameter ID and name)
    ///   as specified in the database.
    /// @return See description :).
    // --------------------------------------------------------------
    public function get_param_names() {
        global $wpdb;
        $res = $wpdb->get_results(sprintf("SELECT paramID, paramName FROM "
                              ."%swetterturnier_param ORDER BY sort ASC",$wpdb->prefix));
        if ( ! $res ) { die('PROBLEMS LOADING PARAMETERS. THIS IS A BUG. CALL THE ADMIN.'); }
        return($res);
    }

    
    // --------------------------------------------------------------
    /// @details Returns status of a scheduled wetterturnier date.
    ///
    /// @param $tdate. Integer representation of the tournament date.
    /// @return Returns boolean `false` if the date is not registered
    ///   in the database (no status; neither 'there will be a tournament'
    ///   nor 'take care, there will be no tournament'). 
    ///   Else an integer will be returned: 1 = upcoming tournament,
    ///   2 = day without a tournament (or kind of a 'no, there is 
    ///   defenitively no tournament!).
    // --------------------------------------------------------------
    public function tournament_date_status( $tdate ) {
        global $wpdb;
        $res = $wpdb->get_row(sprintf("SELECT status FROM "
                             ." %swetterturnier_dates WHERE tdate = %d",
                             $wpdb->prefix, $tdate));
        if ( ! $res ) { return false; }
        // Else return status
        return $res->status;
    }

    // --------------------------------------------------------------
    /// @details Loading all tournament dates from the database which
    ///   have been specified explicitly. Status 1 means that there
    ///   is or will be a tournament, 2 means that there will be no
    ///   tournament (even if some would expect one). All others (not
    ///   in database) are 'no tournament' as well, but not explicitly
    ///   labeled as 'no tournament'.
    ///
    /// @return Array of stdClass objects containing `tdate` (numeric
    ///   representation of the tournament date) and `status`.
    // --------------------------------------------------------------
    public function tournament_get_dates() {
        global $wpdb;
        $res = $wpdb->get_results(sprintf("SELECT tdate, status FROM "
                             ." %swetterturnier_dates", $wpdb->prefix));
        if ( ! $res ) { return false; }
        // Else return status
        $data = array();
        foreach ( $res as $item ) {
            $time = $item->tdate * 86400;
            $data[strftime('%Y-%m-%d',$time)] = (int)$item->status;
        }
        return $data;
    }


    // --------------------------------------------------------------
    /// @details Returns the parameters and the parameter information 
    ///    from the database. The description depends on the current
    ///    language.
    ///
    /// @return Array of stdClass objects containing the necessary
    ///    information including parameter ID, name, number format,
    ///    description, and unit.
    ///
    /// @todo What happens if the user uses a language which is not
    ///    defined? This is not a very general way of storing this
    ///    information ...
    // --------------------------------------------------------------
    public function get_param_data() {
        global $wpdb;
        $lang = strtoupper( $this->get_user_language() );
        $res = $wpdb->get_results("SELECT paramID, paramName, ".$lang." AS thename, "
                                 ."valformat, help".$lang." AS help, decimals, unit FROM "
                                 .$wpdb->prefix."wetterturnier_param "
                                 ."ORDER BY sort ASC");
        if ( ! $res ) { die('PROBLEMS LOADING PARAMETER DATA. THIS IS A BUG. CALL THE ADMIN.'); }
        // and unit which should be displayed.
        $params = new stdClass();
        foreach ( $res as $rec ) {
            $hash = sprintf("pid_%d",$rec->paramID);
            $params->$hash = $rec; 
            if ( strlen($params->$hash->unit) > 0 ) {
                $params->$hash->unit = sprintf("&nbsp;%s",$params->$hash->unit); 
            }
        }
        return($params);
        // return($res);
    }

    // --------------------------------------------------------------
    /// @details Returns city ID based on the city hash from the $_SESSIONS
    ///   variable (is forced to be registered when loading the
    ///   wetterturnier plugin - should exist all the time) 
    ///
    /// @param Returns object from @ref get_city_info of the current city based on the
    ///   user session.
    /// @see get_city_info
    /// @see get_current_city_id
    // --------------------------------------------------------------
    public function get_current_city() {

        $cityObj = new wetterturnier_cityObject(NULL);


        if ( empty( $_SESSION['wetterturnier_city'] ) ) {
            die("get_current_city ERROR: SESSION PARAMETER wetterturnier_city MISSING. "
               ."THIS SEEMS TO BE A BUG. PLEASE CALL THE ADMIN.");
        }
        $res = $this->get_city_info( $_SESSION['wetterturnier_city'] ); 
        if ( ! $res ) {
            die('CANNOT FIND CITY ID IN DATABASE FOR '
               .$_SESSION['wetterturnier_city'].'. BUG. CALL THE ADMIN.');
            return( false );
        } else {
            return( $res );
        }
    }

    // --------------------------------------------------------------
    /// @details Returns numeric cityID from the current city.
    ///   Please note that---in contrast to @ref get_current_city---this
    ///   function only returns the cityID.
    ///
    /// @return Integer city ID of the active city.
    /// @see get_city_info
    /// @see get_current_city
    // --------------------------------------------------------------
    public function get_current_city_id() {
        $res = $this->get_current_city();
        if ( ! $res->ID ) { return( false ); } else { return( $res->ID ); }
    }


    // --------------------------------------------------------------
    /// @details Returns an array containing @ref wetterturnier_cityObject
    ///  objects. The input controls whether only active cities should be
    ///  returned (typically used for user-frontend pages) or all (used
    ///  for admin pages).
    /// @param $activeonly. Boolean, default `true`. If set to `true` only
    ///  active cities will be considered. If `false` inactive will be returned
    ///  as well.
    /// @see get_current_cityObj
    // --------------------------------------------------------------
    public function get_all_cityObj( $activeonly = true ) {

        // Check if we already have loaded this data set
        if ( $activeonly & ! is_null($this->all_cityObj_active) ) {
           return( $this->all_cityObj_active );
        } else if ( ! $activeonly & ! is_null($this->all_cityObj_all) ) {
           return( $this->all_cityObj_all );
        }

        // Else we have to fetch the data from the database.
        global $wpdb;

        $sql = sprintf("SELECT ID FROM %swetterturnier_cities %s ORDER BY sort ASC;",
               $wpdb->prefix,($activeonly ? "WHERE active = 1" : ""));
        $res = $wpdb->get_results( $sql );
        $cities = array();
        foreach ( $res as $rec ) {
            array_push($cities,new wetterturnier_cityObject( $rec->ID ));
        }
        // Save the array to the attribute $this->all_cityObj_active or
        // $this->all_cityObj_all if needed again in another method.
        if ( $activeonly ) {  $this->all_cityObj_active = $cities; }
        else               {  $this->all_cityObj_all    = $cities; }
        return( $cities );
    }


    // --------------------------------------------------------------
    /// @details Returns city info based on the city hash from the city 
    ///   hash (e.g., IBK, BER, ...) OR the ID of the city.
    ///   If input is integer, select for ID, else for hash.
    ///
    /// @param $input. Either numeric city ID or string. If string
    ///   it has to be the city hash (e.g., IBK). Returns a stdClass
    ///   object containing the city information.
    // --------------------------------------------------------------
    public function get_city_info( $input ) {
        global $wpdb;
        if ( is_integer($input) ) {
            $res = $wpdb->get_row(sprintf("SELECT * FROM %swetterturnier_cities WHERE ID = %d",
                                  $wpdb->prefix,$input));
        } else {
            $res = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."wetterturnier_cities "
                                 ." WHERE hash = \"".$input."\"");
        }
        return( $res );
    }


    // --------------------------------------------------------------
    /// @details Loading information of all active stations. An array
    /// will be returned containing a stationObject for each station.
    /// If cityID is given the stations of a specific city will be 
    /// returned. If not given (NULL, default) all active stations
    /// will be returned.
    ///
    /// @param $cityID. Integer city ID, default is NULL.
    /// @return Returns NULL if no stations are found in the database.
    ///     Else an array of stationObjects will be returned.
    /// @see wetterturnier_stationObject
    // --------------------------------------------------------------
    public function get_all_stationObj( $cityID = NULL ) {
        global $wpdb;
        $sql = array();
        array_push($sql,sprintf("SELECT ID FROM %swetterturnier_stations ",$wpdb->prefix));
        if ( ! is_null($cityID) & is_numeric($cityID) ) {
           array_push($sql,sprintf("WHERE cityID = %d",$cityID));
        }
        // Fetching data from database
        $res = $wpdb->get_results( join(" ",$sql) );
        // No data found? Return NULL
        if ( ! $res ) { return( NULL ); }
        // Loop trough all station ID's and load the stationObject.
        // Append one object to the $stationObj array for each of the stations.
        $stationObj = array();
        foreach ( $res as $rec ) {
           array_push( $stationObj, new wetterturnier_stationObject( $rec->ID, "ID" ) );
        }
        return( $stationObj );
    }

    // --------------------------------------------------------------
    /// @details Returns the station numbers of the stations which 
    ///    are attached to this city.
    ///
    /// @param $cityID. Integer city ID.
    /// @return array of stdClass objects containing `wmo` only
    ///   (the numeric WMO station number as specified in the database).
    /// @see get_station_data_for_city
    /// @see get_station_by_wmo
    // --------------------------------------------------------------
    function get_station_wmo_for_city( $cityID ) {
        global $wpdb;
        $res = $wpdb->get_results(sprintf("SELECT wmo FROM %swetterturnier_stations "
                            ." WHERE cityID = %d",$wpdb->prefix,$cityID));
        return( $res );
    }

    // --------------------------------------------------------------
    /// @details Loading observation data for a given cityID (both stations)
    ///
    /// @param $cityID. Integer city ID.
    /// @return array of stdClass Objects containing the detailed station
    ///   information of all stations mapped to the city ($cityID).
    /// @see get_station_wmo_for_city
    /// @see get_station_by_wmo
    // --------------------------------------------------------------
    function get_station_data_for_city( $cityID ) {
        global $wpdb;
        $res = $wpdb->get_results(sprintf("SELECT * FROM %swetterturnier_stations "
                            ." WHERE cityID = %d",$wpdb->prefix,$cityID));
        return( $res );
    }

    // --------------------------------------------------------------
    /// @details Loading observation data for a given cityID (both stations)
    ///
    /// @param $mo. Integer station number (WMO identifier).
    /// @return Returns stdClass object containing the details of this
    ///   specific station.
    /// @see get_station_wmo_for_city
    /// @see get_station_data_for_city
    // --------------------------------------------------------------
    function get_station_by_wmo( $wmo ) {
        global $wpdb;
        $res = $wpdb->get_row(sprintf("SELECT * FROM %swetterturnier_stations "
                            ." WHERE wmo = %d",$wpdb->prefix,(int)$wmo));
        return( $res );
    }


    // --------------------------------------------------------------
    /// @details Getting current page url in a propper way.
    /// @param $cut. Boolean, default is `false`. If set to `true`
    ///   the POST arguments will be removed from the url.
    /// @return Returns the current URL with or without POST args.
    // --------------------------------------------------------------
    function curPageURL( $cut = False ) {
        $pageURL = 'http';
        if ( isset($_SERVER["HTTPS"]) ) { $pageURL .= "s"; }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }
        // Remove additional arguments if cut = True
        if ( $cut ) {
           $tmp = explode("?",$pageURL,2);
           $pageURL = $tmp[0];
        }
        return $pageURL;
    }


    // ------------------------------------------------------------------
    /// @details Loading allready stored data from the user from the
    ///   database.
    /// 
    /// @param $cityObj. Object of class @ref wetterturnier_cityObject.
    /// @param $userID. Numeric user ID.
    /// @param $next. Object containing the tournament date information of the
    ///    next (or upcoming) tournament.
    /// @param $day. Integer which specifies for which day you want to load the
    ///    forecast data. E.g., `$day=1` would be the 1-day-ahead forecast leading
    ///    to a `bdate` or 'betdate' of `$next->tdate + 1`. 
    /// @param $detailed. Boolean, default is `false`. If `true` additional
    ///    elements will be returned such as parameter name and stuff.
    /// @return A stdClass containing stdClassses identified by a combination
    ///    of the parameter name and the forecast day. E.g.,
    ///    `$result->RR_1` contians the information for RR day 1.
    // ------------------------------------------------------------------
    function get_user_bets_from_db($cityObj,$userID,$next,$day,$detailed=false) {
    
        // Wordpress database
        global $wpdb;
    
        // Getting forecast day
        $betdate = $next->tdate + $day;

        // If detauled=false, we return the "values only"
        if ( ! $detailed ) {
            $data = $wpdb->get_results("SELECT bets.paramID AS paramID, bets.value AS value, "
                   ." par.paramName as paramName FROM "
                   .$wpdb->prefix."wetterturnier_bets AS bets "
                   ." LEFT OUTER JOIN "
                   .$wpdb->prefix."wetterturnier_param AS par "
                   ." ON bets.paramID=par.paramID "
                   ." WHERE cityID = ".$cityObj->get('ID')
                   ." AND  userID = ".$userID
                   ." AND tdate = ".$next->tdate
                   ." AND betdate = ".$betdate);

            // Prepare object
            $result = new StdClass();
            foreach ( $data as $rec ) {
               $hash = sprintf("%s_%d",$rec->paramName,$day); 
               $result->$hash = $rec->value / 10.; 
            }

        // Else return some more data. Each entry in the object
        // is a stdclass object containing several infos.
        } else {
            $data = $wpdb->get_results("SELECT bets.paramID AS paramID, bets.value AS value, "
                   ." bets.betdate-bets.tdate AS day, "
                   ." bets.placed AS placed, bets.placedby AS placedby, "
                   ." CASE WHEN stat.ID > 0 THEN 1 ELSE 0 END AS status, "
                   //." CASE WHEN count(stat.ID) >0  THEN 1 ELSE 0 END AS status, "
                   ." par.paramName as paramName FROM "
                   .$wpdb->prefix."wetterturnier_bets AS bets "
                   ." LEFT OUTER JOIN "
                   .$wpdb->prefix."wetterturnier_param AS par "
                   ." ON bets.paramID=par.paramID "
                   ." LEFT JOIN "
                   .$wpdb->prefix."wetterturnier_betstat AS stat "
                   ." ON stat.userID=bets.userID AND stat.cityID=bets.cityID "
                   ." AND stat.tdate = bets.tdate " 
                   ." WHERE bets.cityID = ".$cityObj->get('ID')
                   ." AND bets.userID = ".$userID
                   ." AND bets.tdate = ".$next->tdate
                   ." AND bets.betdate = ".$betdate);

            // Prepare object
            $result = new StdClass();
            foreach ( $data as $rec ) {
               $hash = sprintf('%s_%d',$rec->paramName,$day);
               $result->$hash = $rec;
               if ( isset($result->$hash->value) ) {
                  $result->$hash->value = $result->$hash->value/10;
               }
            }
        }
    
        return( $result );
    }


    // --------------------------------------------------------------
    // Loading bets from the database based on:
    // cityID, tdate and betdate (integer, +1/+2)
    // If input $pionts is true, loading points from database.
    // --------------------------------------------------------------
    function get_bet_values($city,$tdate,$betday,$points,$userID = NULL) {

        global $wpdb;

        // Loading parameters
        $res = new stdClass();
        $res->city    = $city;
        $res->tdate = $tdate;
        $res->betdate = $tdate + $betday;

        // Loading parameter
        // Create nice vector with paramID=>paramName
        $res->params = $this->get_param_data();
        $res->lookup = array();
        foreach ( $res->params as $param ) {
            $res->lookup[$param->paramID] = $param->paramName;
        }

        // Loading data from database
        if ( $points ) {
            $the_column = 'b.points'; 
        } else {
            $the_column = 'b.value'; 
        }
        $sql = "SELECT u.display_name AS display_name, b.userID, "
               ."u.user_login AS user_login, u.display_name as display_name, "
               ."b.paramID, b.cityID, ".$the_column." as value, "
               ."placed, placedby FROM "
               ."%swetterturnier_bets AS b "
               ." LEFT OUTER JOIN %susers AS u ON u.ID = b.userID "
               ." LEFT OUTER JOIN %swetterturnier_betstat AS stat "
               ." ON stat.userID = b.userID AND stat.cityID = b.cityID "
               ." AND stat.tdate = b.tdate "
               ." WHERE b.cityID = %d "
               ." AND b.tdate = %d "
               ." AND b.betdate = %d ";
        if ( isset($userID) ) { $sql .= "AND u.ID = ".$userID." "; }
        $sql .= " ORDER BY u.user_nicename";

        // Getting data and initialize stdClass to store the
        // information necessary for displaying them.
        $sql       = sprintf($sql,$wpdb->prefix,$wpdb->prefix,$wpdb->prefix,
                             (int)$city,$tdate,$tdate+$betday);
        $allbets   = $wpdb->get_results( $sql );
        $res->data = new stdClass();

        foreach ( $allbets as $rec ) {

            // user and param hash
            $uhash = sprintf("uid_%d",$rec->userID);
            $phash = sprintf("pid_%d",$rec->paramID);

            // Adding user subclass
            if ( ! property_exists( $res->data,$uhash) ) {
                $res->data->$uhash         = new stdClass();
            }
            // Adding parameter subclass for value/status
            if ( ! property_exists( $res->data->$uhash,$phash ) ) {
                $res->data->$uhash->$phash = new stdClass();
            }
            // Adding username
            if ( ! property_exists( $res->data->$uhash, 'user_login' ) ) {
                $res->data->$uhash->user_login   = $rec->user_login;
                $res->data->$uhash->display_name = $rec->display_name;
                $res->data->$uhash->userID       = $rec->userID;
            }

            if ( $points ) {
               $res->data->$uhash->$phash->value = (float)$rec->value; 
            } else {
               // Loading units and decimals for number_format
               $hash = sprintf("pid_%d",$rec->paramID);
               $dec  = $res->params->$hash->decimals;
               $unit = $res->params->$hash->unit;
               // Create propper value
               $res->data->$uhash->$phash->value = (float)$rec->value/10.; 
            }
            // If placedby is bigger than 0 (if it is, it is a userID) 
            if ( ! empty($rec->placedby) && $rec->placedby > 0 )
            {
                $res->data->$uhash->$phash->placedby = $rec->placedby;
                $res->data->$uhash->$phash->modified = $rec->placed;
            } 
        }

        $res->what='bet';
        return($res);

    }

    // ------------------------------------------------------------------
    // Loading allready stored observation values 
    /// @param $stnObj. Object of class @ref wetterturnier_stationObject.
    // ------------------------------------------------------------------
    function get_station_obs_from_db($stnObj,$next,$day,$detailed=false) {
    
        // Wordpress database
        global $wpdb;
    
        // Getting day. If lower than 10 this is the "1th" or "2nD" ..
        // day. If bigger, we expect that the day is
        if ( $day == 1 ) { $betdate = $next->betdate_day1; }
        else             { $betdate = $next->betdate_day2; }

        // If detauled=false, we return the "values only"
        if ( ! $detailed ) {
            $data = $wpdb->get_results("SELECT obs.paramID AS paramID, obs.value AS value, "
                   ." par.paramName as paramName FROM "
                   .$wpdb->prefix."wetterturnier_obs AS obs "
                   ." LEFT OUTER JOIN "
                   .$wpdb->prefix."wetterturnier_param AS par "
                   ." ON obs.paramID=par.paramID "
                   ." WHERE obs.station = ".$stnObj->get('wmo')." " 
                   ." AND betdate = ".$betdate);

            // Prepare object
            $dayhash = "day".$day."_";
            $result = new StdClass();
            foreach ( $data as $rec ) {
               $name = $dayhash.$rec->paramName;
               $result->$name = $rec->value / 10.; 
            }

        // Else return some more data. Each entry in the object
        // is a stdclass object containing several infos.
        } else {
            $data = $wpdb->get_results("SELECT obs.paramID AS paramID, obs.value AS value, "
                   ." obs.placed AS placed, obs.placedby AS placedby, "
                   ." par.paramName as paramName FROM "
                   .$wpdb->prefix."wetterturnier_obs AS obs "
                   ." LEFT OUTER JOIN "
                   .$wpdb->prefix."wetterturnier_param AS par "
                   ." ON obs.paramID=par.paramID "
                   ." WHERE station = ".$stnObj->get('wmo')." " 
                   ." AND betdate = ".$betdate);

            // Prepare object
            $result = new StdClass();
            foreach ( $data as $rec ) {
               $hash = sprintf('day%d_%s',$day,$rec->paramName);
               $result->$hash = $rec;
               if ( isset($result->$hash->value) ) {
                  $result->$hash->value = $result->$hash->value/10;
               }
            }
        }
    
        return( $result );
    }


    // --------------------------------------------------------------
    // Returns the observations in the same format as 
    // get_user_bets_from_db returns the bets. Need them for the
    // edit forms when someone would like to change observation
    // values.
    // --------------------------------------------------------------
    function get_obs_from_db($station,$tournament,$day) {
    
        // Wordpress database
        global $wpdb;
    
        // Getting day. If lower than 10 this is the "1th" or "2nD" ..
        // day. If bigger, we expect that the day is
        $betdate = $tournament->tdate + $day;

        $sql = array();
        array_push($sql,"SELECT obs.paramID AS paramID,");
        array_push($sql,"obs.value AS value, par.paramName as paramName");
        array_push($sql,sprintf("FROM %swetterturnier_obs AS obs",$wpdb->prefix));
        array_push($sql,sprintf("LEFT OUTER JOIN %swetterturnier_param AS par",$wpdb->prefix));
        array_push($sql,"ON obs.paramID=par.paramID");
        array_push($sql,sprintf("WHERE obs.station = %d",$station));
        array_push($sql,sprintf("AND betdate = %d",$betdate));
   
        ###print join("\n",$sql)."<br>\n";
        $data = $wpdb->get_results( join("\n",$sql) );

        // Prepare object
        $result = new StdClass();
        foreach ( $data as $rec ) {
           $hash = sprintf("%s_%d",$rec->paramName,$day); 
           $result->$hash = (is_null($rec->value) ? NULL : $rec->value / 10.); 
        }

        return( $result );
    }

    // --------------------------------------------------------------
    // Loading observation data for a given cityID (both stations)
    // --------------------------------------------------------------
    function get_obs_values($city, $betdate, $raw = false) {

        global $wpdb;

        $stations = $this->get_station_data_for_city( $city );

        $res       = new stdClass();
        $res->data = new stdClass();
        $res->betdate = $betdate;

        // Loading parameter
        // Create nice vector with paramID=>paramName
        $res->params = $this->get_param_data();
        $res->lookup = array();
        foreach ( $res->params as $param ) {
            $res->lookup[$param->paramID] = $param->paramName;
        }

        foreach ( $stations as $station ) {
            $obs = $wpdb->get_results(sprintf("SELECT paramID, value, placed, placedby "
                                ." FROM %swetterturnier_obs "
                                ." WHERE station = %d AND betdate = %d",
                                $wpdb->prefix, $station->wmo, $betdate)); 
            foreach ( $obs as $rec ) {
                $wmohash = sprintf('wmo_%d',$station->wmo);
                $phash   = sprintf('pid_%d',$rec->paramID);

                if ( ! property_exists( $res->data, $wmohash ) ) {
                    $res->data->$wmohash = new stdClass();
                    // Need the user_login key (useless for the observations)
                    // but need this so that I can use the same method to 
                    // print the data as for the user bets.
                    $res->data->$wmohash->user_login   = $station->name;
                    $res->data->$wmohash->display_name = $station->name;
                    $res->data->$wmohash->userID       = 0;
                    $res->data->$wmohash->wmo          = $station->wmo;
                }
                if ( ! property_exists( $res->data->$wmohash, $phash ) ) {
                    $res->data->$wmohash->$phash = new stdClass();
                }
                if ( $raw ) {
                    $res->data->$wmohash->$phash->value = $rec->value;
                } else {
                    // Loading units and decimals for number_format
                    $hash = sprintf("pid_%d",$rec->paramID);
                    $dec  = $res->params->$hash->decimals;
                    $unit = $res->params->$hash->unit;
                    //$res->data->$wmohash->$phash->value = $val; 
                    $res->data->$wmohash->$phash->value = (is_null($rec->value) ? NULL : (float)$rec->value/10.);
                }
                // If placedby is bigger than 0 (if it is, it is a userID) 
                if ( ! empty($rec->placedby) && $rec->placedby > 0 )
                {
                    $res->data->$wmohash->$phash->placedby = $rec->placedby;
                    $res->data->$wmohash->$phash->modified = $rec->placed;
                } 

            }
        }
        $res->what ='obs';

        return( $res );

    }


    // --------------------------------------------------------------
    // Datepicker code for the widget
    // --------------------------------------------------------------
    public function tournament_datepicker_widget() {
        ?>

        <script type='text/javascript'>
        jQuery(document).on('ready',function() {
            (function($) {
                var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

                // Loop over all the tournament dates and set
                // status classes on datepicker. Note that I am doing
                // this for ALL tournament dates in the db, does not
                // matter what is on screen. Probably not the best way.
                var datepicker_get_dates = function() {
                    // Ajaxing the calculation miniscript
                    var all_dates = false;
                    $.ajax({
                        url: ajaxurl, dataType: 'json', type: 'post', async: false,
                        data: {action:'tournament_datepicker_ajax'},
                        success: function(results) { all_dates = results; },
                        error: function(e) { $error = e; console.log('errorlog'); console.log(e); } 
                    });
                    return all_dates;
                }
                var datepicker_set_dates = function(d,all_dates) {
                    var mydate = $.datepicker.formatDate('yy-mm-dd',d);
                    var arr = [true,""];
                    $.each( all_dates, function(key,val) {
                        if ( key == mydate ) { arr = [true,"status-"+val]; }
                    });
                    return arr;
                }


                // Loading dates from database
                var all_dates = datepicker_get_dates();

                // Initialize datepicker
                $('#wtwidget_tournaments').datepicker({
                    firstDay: 1,
                    dateFormat : 'yy-mm-dd', numberOfMonths: 1, showButtonPanel: true, async: false,
                    beforeShowDay: function(d) { return datepicker_set_dates(d,all_dates,false); },
                });

            })(jQuery);
        });
        </script>

        <?php
    }

    public function tournament_datepicker_ajax() {

        global $wpdb;

        // Single date
        if ( ! empty($_POST['date']) ) {
            // Convert string date to timestamp
            $tdate = (int)floor(strtotime($_POST['date']) / 86400);

            // Getting status
            $status = $this->tournament_date_status($tdate);

            // Check if allready in tournament database
            if ( ! $status ) {
                $wpdb->insert( sprintf('%swetterturnier_dates',$wpdb->prefix),
                    array('tdate'=>$tdate,'status'=>1) );
                print 1;
            } else if ( $status == 2 ) {
                $wpdb->delete( sprintf('%swetterturnier_dates',$wpdb->prefix),
                        array('tdate'=>$tdate));
                print 0;
            } else {
                $wpdb->update( sprintf('%swetterturnier_dates',$wpdb->prefix),
                        array('status'=>$status+1), array('tdate'=>$tdate));
                print $status+1;
            }
        } else {
            $tdates = $this->tournament_get_dates();
            print json_encode($tdates);
        }

        die(); # important
    }

    // ---------------------------------------------------------------
    // There is an ajax function call to save users which try to apply for
    // a group membership.
    // Returns json array. If user is allready an active member of this group,
    // return value 'got' is 'ismember'.
    // ---------------------------------------------------------------
    public function usersearch_ajax() {

       global $wpdb;
       if ( empty($_POST) ) {
          $like = ''; 
       } else if ( empty($_POST['search']) ) {
          $like = '';
       } else {
          $like = "WHERE LCASE(user_login) LIKE '%"
                 .strtolower(htmlspecialchars($_POST['search']))
                 ."%'";
       }

       // - Searching in the database and create corresponding ajax
       //   string containing userID:user_login
       $sql  = sprintf('SELECT ID, user_login FROM %susers',$wpdb->prefix);
       $sql  = $sql." ".$like." ORDER BY user_login ASC";
       $res   = $wpdb->get_results($sql);

       print json_encode($res);
       die(); # important

    }

    // --------------------------------------------------------------
    // Getting avatar url instead of a full avatar imgi tag.
    // --------------------------------------------------------------
    function get_avatar_url($userID){
        $get_avatar = get_wp_user_avatar($userID, 96);
        preg_match("/src=\"(.*?)\"/i", $get_avatar, $matches);
        return $matches[1];
    }


    // --------------------------------------------------------------
    // REQUEST_CHECK is checking if the requested variable is set
    // or not. In case not, we will return 'false'. 
    // --------------------------------------------------------------
    public function REQUEST_CHECK( $name ) {
        if ( empty($_REQUEST) ) {
            $val = false;;
        } else if ( empty($_REQUEST[$name]) ) {
            $val = false;
        } else {
            $val = $_REQUEST[$name];
        }
        return( $val );
    }


    // ---------------------------------------------------------------
    // Returns the latest observations for a given station. Called
    // by wordpress ajax.
    // ---------------------------------------------------------------
    public function getobservations_ajax( $statnr = Null ) {

       global $wpdb;
       if ( empty($_POST) ) {
          print json_encode(array('error'=>'Got no station number.')); die();
       } else if ( empty($_POST['statnr']) ) {
          print json_encode(array('error'=>'Got no station number.')); die();
       }
       $statnr = (int)$_POST["statnr"];
       // Create new stationObject
       $stnObj = new wetterturnier_stationObject( $statnr, "wmo" );

       if ( empty($_POST['days']) ) { $days = 1; } else { $days = (int)$_POST['days']; }
       // Timestamp to fetch data from (latestobsObject requires from/to unix time stamps)
       $from = (int)date("U") - $days*86400;

       // Create new latestobsObject which loads and prepares the data.
       $latestobsObj = new wetterturnier_latestobsObject( $stnObj, $from );
       // Return data
       print $latestobsObj->get_json();
       die();

    }


} // End of class definition

?>
