<?php
// -------------------------------------------------------------------
// - NAME:        export.php
// - AUTHOR:      Reto Stauffer
// - DATE:        2015-08-02
// -------------------------------------------------------------------
// - DESCRIPTION:
// -------------------------------------------------------------------
// - EDITORIAL:   2015-08-02, RS: Created file on thinkreto.
// -------------------------------------------------------------------
// - L@ST MODIFIED: 2014-09-13 15:12 on thinkreto
// -------------------------------------------------------------------

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ------------------------------------------------------------------
// Header which manages the 'send to browser'
// ------------------------------------------------------------------
header("Content-Description: File Transfer");
header("Content-type: text/plain; charset=utf-8");
header("Content-Disposition: attachment; filename=wetterturnier_export.txt");
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

print("Currently disabled");
die();

// ------------------------------------------------------------------
// Title. Note that there is no line break after the second
// print. Problem: the require_once somehow creates a linebreak
// and I was not able to remove it. Therefore this is the
// 'hack' to avoid blank lines in the export file.
// ------------------------------------------------------------------
printf("# %s\n",str_repeat("-",68));
printf("# %s","Wetterturnier.de: data export");

// ------------------------------------------------------------------
// Include necessary elements
// ------------------------------------------------------------------
require_once("../../../../../wp-load.php");
require_once("export-functions.php");

print "# Please note that some of the data we are using for the wetterturnier\n";
print "# are under special constraints (e.g., WMO additional)\n";
print "# as we are not allowed to re-distribute these data only data\n";
print "# labeled as WMO essential wil be exported.\n";
print "# TODO Reto: add some header description\n";
printf("# %s\n",str_repeat("-",68));


// ------------------------------------------------------------------
// If on development mode: 
// ------------------------------------------------------------------
if ( ! empty($_REQUEST['APIKEY']) ) {

   $QUERY = read_APICONFIG( $_REQUEST["APIKEY"] );
   // If we cannot find this request key
   if ( ! $QUERY ) {
      printf("# %s\n",__("Sorry, APIKEY or the corresponding config is invalid. Stop.","wpwt"));
      die(9);
   }
   foreach ( $QUERY as $key=>$val ) { $_REQUEST[$key] = $val; }
   $APICALL = true;

// Else set APICALL to false.
} else { $APICALL = false; }


// ------------------------------------------------------------------
// There are a few cases why to kick out the user :)
// ------------------------------------------------------------------
// If user is not logged in - kick out
###file_get_contents
if ( ! is_user_logged_in() & empty($_REQUEST['APIKEY']) ) {
   printf("# %s\n",__("You are not logged in! We are very sorry, but "
     ."we only allow data downloads for logged in users.","wpwt"));
   die(9);
} else if ( empty($_REQUEST['action']) ) {
   printf("# %s\n",__("Sorry, could not understand your request.","wpwt"));
}

// Save action
$input = wt_export_inputcheck( $_REQUEST );
if ( ! $input->action || ! $input->statnr ) {
   printf("# %s\n",__("No 'action' or 'statnr' input received. Cannot "
     ."do the data export without them.","wpwt"));
   die(9);
}

// Base classes
global $WTuser;
global $wpdb;

// ------------------------------------------------------------------
// Define which cols should be loaded. There is one smaller subset
// and one called "everything"
// ------------------------------------------------------------------
if ( $APICALL & ! empty($_REQUEST["columns"]) ) {
   $cols = $_REQUEST["columns"];
} else if ( $input->preset ) {
   $cols = array("datum","stdmin","datumsec","t","td","cc","sun","sunday","dd","ff","ffx","ffx1","ffx3","ffx6",
               "ww","w1","w2","psta","pmsl","tmin12","tmax12","rrr3","rrr6","rrr12");
} else {
   $cols = array("*");
}

// ------------------------------------------------------------------
// Getting station description. 
// Prints the description into the header as well.
// ------------------------------------------------------------------
$desc = wt_export_station_desc( $input, True );
printf("# %s\n",str_repeat("-",68));

// ------------------------------------------------------------------
// Getting parameter description object. Also necessary to do the
// descaling of the different values. 
// Prints the description into the header as well.
// ------------------------------------------------------------------
$desc = wt_export_param_desc( $input, $cols, True );
printf("# %s\n",str_repeat("-",68));


// ------------------------------------------------------------------
// Returning data
// The only difference between obslive and obsarchive is the
// dataase. Once the database is obs.live, once obs.archive.
// The rest is absolutely the same! 
// ------------------------------------------------------------------
if ( strcmp($input->action,"obslive")    === 0 || 
     strcmp($input->action,"obsarchive") === 0 ) {
   // Define database table
   $dbtable = str_replace("obs","obs.",$input->action);

   // Station and NA value
   $bgn     = (int)sprintf("%4d0101",$input->year);
   $end     = (int)sprintf("%4d1231",$input->year);
   // Create SQL statement
   $sql = array();
   array_push($sql,sprintf("SELECT %s FROM %s",join(",",$cols),$dbtable));
   array_push($sql,sprintf("WHERE statnr=%d",$input->statnr));
   array_push($sql,sprintf("AND datum BETWEEN %d AND %d",$bgn,$end)); 
   array_push($sql,sprintf("AND stint = 'essential'"));
   array_push($sql,sprintf("ORDER BY datumsec ASC"));

   // ---------------------------------------------------------------
   // Loading the data
   // ---------------------------------------------------------------
   $data = $wpdb->get_results( join(" ",$sql) );
   // Define format strings
   $fmthead = "%s".$input->delimiter." ";
   if ( $input->raw ) {
      $fmtdata = "%5d".$input->delimiter." ";
      $missing = sprintf("%5s%s ",$input->NAvalue,$input->delimiter);
   } else {
      $fmtdata = "%8.1f".$input->delimiter." ";
      $missing = sprintf("%8s%s ",$input->NAvalue,$input->delimiter);
   }

   // ---------------------------------------------------------------
   // Show data
   // ---------------------------------------------------------------
   $first = true;
   foreach ( $data as $rec ) {
      // Header
      if ( $first )  {
         foreach ( $rec as $key=>$val ) { printf($fmthead,$key); }
         print "\n";
         $first = false;
      }
      foreach ( $rec as $key=>$val ) {
         # ------------ descaling values -------------
         if ( is_numeric($val) && ! $input->raw ) { 
            if ( property_exists($desc,$key) ) { 
               if ( $desc->$key->factor == 0 ) {
                  $val = $val - $desc->$key->offset;
               } else {
                  $val = ( $val / $desc->$key->factor ) - $desc->$key->offset;
               }
            }
         }
         # ------------ descaling values -------------

         # - Show value
         print (is_numeric($val) ? sprintf($fmtdata,($val)) : $missing );
      }
      print "\n";
   }
}
?> 
