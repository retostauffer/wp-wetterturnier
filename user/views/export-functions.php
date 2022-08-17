<?php
// -------------------------------------------------------------------
// - NAME:        export-functions.php
// - AUTHOR:      Reto Stauffer
// - DATE:        2015-08-02
// -------------------------------------------------------------------
// - DESCRIPTION: Used by export.php
// -------------------------------------------------------------------
// - EDITORIAL:   2015-08-02, RS: Created file on thinkreto.
// -------------------------------------------------------------------
// - L@ST MODIFIED: 2014-09-13 15:12 on thinkreto
// -------------------------------------------------------------------


// -------------------------------------------------------------------
/// @details There is a speical file called export-queries.dat which is read
///   when an input user query is set. Rather than taking additional
///   input arguments the settings from this file will be used.
///   This function file reads the config .dat file.
/// @param $file. String, specifies the file which has to be read.
/// @return ...
// -------------------------------------------------------------------
function read_APICONFIG( $APIKEY, $file = "export-APICONFIG.dat" ) {

   $content = file_get_contents($file);
   $lines   = explode("\n",$content);

   // Looping trough file
   foreach ( $lines as $line ) {
      // Empty line
      if ( strlen($line) === 0 ) { continue; }
      // Comment line
      if ( substr(trim($line),0,1) === "#" ) { continue; }
      // Checking format, extract information
      //                      | hash       |         | stat |         | table|         | NAvalue       |         |del|         |   param    |
      $chk = preg_match("/\s+?([a-zA-Z0-9]+)\s+?@\s+?([0-9]*)\s+?@\s+?([a-z]*)\s+?@\s+?([0-9a-zA-Z-\.]+)\s+?@\s+?(\S+)\s+?@\s+?([a-zA-Z0-9_,]+)\s*?$/",$line,$mtch);
      if ( ! $chk ) { continue; }


      // Now we have the following array elements
      // [0]   original string (full line)
      // [1]   hash
      // [2]   station number
      // [3]   action
      // [4]   NAvalue
      // [5]   delimiter
      // [6]   parameter
      // Check if this is the hash we are looking for.
      // If we find a match: return key/value pairs of the
      // important variables.
      if ( $mtch[1] == $APIKEY ) { 
         $res = new stdClass();
         $res->year      = (int)date("Y");
         $res->statnr    = (int)$mtch[2];
         $res->action    = $mtch[3];
         $res->NAvalue   = $mtch[4];
         $res->delimiter = $mtch[5];
         if ( $mtch[6] !== "all" ) {
            // Split columns and convert into array
            $res->columns = explode(",",$mtch[6]);
         }
         return( $res );
      }
   }

   // Not found, return false
   return( false );

}



// -------------------------------------------------------------------
// Manipulating $_REQUEST variables, create stdClass and return.
// If 'errors' is set, the export.php script will stop.
// -------------------------------------------------------------------
function wt_export_inputcheck( $args ) {

   // Resulting class
   $res = new stdClass();
   // Adding all $args to $res
   foreach ( $args as $key => $val ) { $res->$key = $val; }

   // Setting some defaults if ont set 
   if ( ! property_exists($res,"action")    ) { $res->action    = False; }
   if ( ! property_exists($res,"statnr")    ) { $res->statnr    = False; }
   if ( ! property_exists($res,"NAvalue")   ) { $res->NAvalue   = "NA"; }
   if ( ! property_exists($res,"delimiter") ) { $res->delimiter = ""; }
   if ( ! property_exists($res,"raw") )       { $res->raw       = False; }
   else                                       { $res->raw       = True; }
   if ( ! property_exists($res,"preset") )    { $res->preset    = False; }
   else                                       { $res->preset    = True; }
   
   // If NAvalue is 'blank' this means we have to set a blank
   if ( strcmp($res->NAvalue,"blank") === 0 ) { $res->NAvalue = ""; }

   // Identify delimiter and replace REQUEST value
   if ( strcmp($res->delimiter,"semikolon") == 0 ) {
      $res->delimiter = ";";
   } else if ( strcmp($res->delimiter,"blank") == 0 ) {
      $res->delimiter = " ";
   }

   // Return resulting object
   return( $res );
}


// ------------------------------------------------------------------
// Loading statioin description from database
// ------------------------------------------------------------------
function wt_export_station_desc( $input, $show = True ) {

   global $wpdb;

   // Check if database table is reachable 
   if ( ! $wpdb->get_results("SHOW columns FROM obs.stations") ) {
      printf("# %s\n",__("Cannot reach database obs.stations. Ignore station description.","wpwt"));
      return(false); 
   }

   // Create sql and load row
   $sql = sprintf("SELECT * FROM obs.stations WHERE statnr = %d",$input->statnr);
   $res = $wpdb->get_row( $sql ); 

   // Show?
   if ( $show && ! $res ) {
      print "# STATION INFO: Problems loading station information. Sorry.\n";
   } else if ( $show ) {
      print "# Station information:\n";

      printf("# %-20s %s\n",     "Station name:",       $res->name);
      printf("# %-20s %10d\n",   "WMO station number:", $res->statnr);
      printf("# %-20s %10.5f\n", "Longitude:",          $res->lon);
      printf("# %-20s %10.5f\n", "Latitude:",           $res->lat);
      printf("# %-20s %10d\n",   "Station height:",     $res->hoehe);
      printf("# %-20s %10d\n",   "Barometer height:",   $res->hbaro);
   }
   
   return( $res );
}

// ------------------------------------------------------------------
// Loading parameter description from database
// ------------------------------------------------------------------
function wt_export_param_desc( $input, $cols, $show = False ) {

   global $wpdb;

   // Check if database table is reachable 
   if ( ! $wpdb->get_results("SHOW columns FROM obs.bufrdesc") ) {
      printf("# %s\n",__("Cannot reach database obs.bufrdesc, exit.","wpwt"));
      return(false); 
   }

   // Else loading parameters
   if ( count($cols) == 1 && $cols[0] == "*" ) {
      $sql = sprintf("SELECT * FROM obs.bufrdesc");
   } else {
      $strcols = array();
      foreach ( $cols as $rec ) { array_push($strcols,sprintf("'%s'",$rec)); }
      $sql = sprintf("SELECT * FROM obs.bufrdesc WHERE param in (%s)",join(",",$strcols));
   }
   $tmp = $wpdb->get_results( $sql );
   $param = new stdClass();
   foreach ( $tmp as $rec ) { $hash = $rec->param; $param->$hash = $rec; }

   // ---------------------------------------------------------------
   // If show = True: print parameter description. 
   // If raw = True: show offset/factor (else we are scalilng
   // the data before we show them and therefore offset and scaling
   // aren't necessary anymore).
   // ---------------------------------------------------------------
   if ( $show ) {

      printf("# Parameter description:\n");
      if ( count($param) == 0 ) {
         printf("# [!] No parameter description available. Problems with 'bufrdesc' table?\n");
      } else {
         foreach ( $param as $rec ) {
            printf("# %-10s %s\n",$rec->param,$rec->desc);
            printf("# %-10s [Unit: %s; Period: %d seconds; BUFR ID: %d]\n",
                          "",$rec->unit,$rec->period,$rec->bufrid);
            if ( $input->raw ) {
               printf("# %-10s Offset: %.3f; Factor: %.3f\n",
                             "",$rec->offset,$rec->factor);
            }
         }
      }
      if ( $input->raw ) {
         print "# NOTE: you have choosen RAW export. To save space on disc\n"
              ."#       the data are stored 'scaled' in the database. To get\n"
              ."#       original values (with units shown above) the data have\n"
              ."#       to be descaled. Therefore just do the following:\n"
              ."#        descaled_value = (value / factor) - offset\n";
      }

   }

   return( $param );

}



































