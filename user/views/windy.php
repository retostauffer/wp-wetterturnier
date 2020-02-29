<?php
global $wpdb;
global $WTuser;
$args = (object)$args;

// ------------------------------------------------------------------
// If no city input is set: using current city as default
// In this case we can load the pre-fetched city (active or current city)
// ------------------------------------------------------------------
if ( ! $args->city )
	{ $cityID = $this->get_current_cityObj()->get("ID"); }
else { $cityID = $args->city; }

switch ( $cityID ) {

	case 1: //BER
		$lat="52.518611";
		$lon="13.408333";
		break;
	case 2: //VIE
		$lat="48.20849";
		$lon="16.37208";
		break;
	case 3: //ZUR
		$lat="47.37174";
		$lon="8.54226";
		break;
	case 4: //IBK
		$lat="47.265";
		$lon="11.395";
		break;
	case 5: //LEI
		$lat="51.222";
		$lon="12.5023";
		break;
        case 6: //FRA
                $lat="50.033056";
                $lon="8.570556";
                break;
        case 7: //CLB
                $lat="50.865917";
                $lon="7.142744";
                break;
	case 8: //HAN
                $lat="52.37052";
                $lon="9.73322";
                break;
	case 9: //HMB
                $lat="53.57532";
                $lon="10.01534";
                break;
	case 10://STU
                $lat="48.78232";
                $lon="9.17702";
                break;
	case 11://MUN
                $lat="48.13743";
                $lon="11.57549";
                break;
	default:
		$lat="";
		$lon="";
	break;

}

// TODO: get the lat, lon of the city from the database
// Problem here: we only have it for the stations, not for each city in general. Maybe calculate mean of all locations?

$w=$args->width;
$h=$args->heigth;
$z=$args->zoom;
$l=$args->level;
$o=$args->overlay;
$men=$args->menu;
$mes=$args->message;
$mar=$args->marker;
$cal=$args->calendar;
$pre=$args->pressure;
$typ=$args->type;
$loc=$args->location;
$det=$args->detail;
$cit=$args->city;

if ( isset($args->lat) && isset($args->lon) ) {
	$lat=$args->lat;
	$lon=$args->lon;
}

echo "<iframe width=$w height=$h src='https://embed.windy.com/embed2.html?lat=$lat&lon=$lon&zoom=$z&level=$l&overlay=$o&menu=$men&message=$mes&marker=$mar&calendar=$cal&pressure=$pre&type=$typ&location=$loc&detail=$det&detailLat=$lat&detailLon=$lon&metricWind=kt&metricTemp=%C2%B0C&radarRange=-1' frameborder=0></iframe>";

?>
