<?php

// Output as text/plain
header('HTTP/1.1 200 OK');
header('Content-Type: text/plain; charset=utf-8');

require_once("../../../wp-config.php");


global $wpdb;
global $WTuser;

$args = (object)$_REQUEST;
if ( ! property_exists($args,"date") | ! property_exists($args,"city") ) {
	die("Input arguments missing, stop.");
}

$allcityObj = $WTuser->get_all_cityObj();
$cityObj = NULL; # Default, not yet found
foreach ( $allcityObj as $rec ) {
	if ( strcmp($args->city,strtolower(substr($rec->get("hash"),0,1))) == 0 ) {
		$cityObj = $rec; break;
	}
}

// City not found?
if ( is_null($cityObj) ) {
	die("Sorry, city could not have been found.");
}

if ( ! preg_match("/^[0-9]{6}$/",$args->date) ) {
	die("Sorry, could not understande date input.");
}

# Convert date if possible
try {
   $tdate = DateTime::createFromFormat('ymd', $args->date)->format("U");
   $tdate = (int)floor($tdate / 86400);
} catch ( Expection $e ) {
   die("Problems converting the input date to oldoutputObject. Stop.");
}


// Check if tdate was a tournament
$res = $wpdb->get_row(sprintf("SELECT status FROM %swetterturnier_dates WHERE tdate = %d",
							 $wpdb->prefix,$tdate));
if ( $wpdb->num_rows == 0 ) { exit("Sorry, no official tournament date."); }
if ( ! $res->status == 1 )  { exit("Sorry, no tournament date."); }

// Create new object
$obj = new wetterturnier_oldoutputObject( $cityObj, $tdate );
// Show archive table
$obj->show();

?>
