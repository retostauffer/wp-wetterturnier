<?php

// Output as text/plain
header('Content-Type: text/plain');

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

// Create new object
$obj = new wetterturnier_oldoutputObject( $cityObj, $args->date );
// Show archive table
$obj->show();

?>
