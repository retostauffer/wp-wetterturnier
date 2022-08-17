<?php
global $wpdb;
global $WTuser;

$cityObj = $WTuser->get_current_cityObj();

$c = strtolower( substr($cityObj->get("name"), 0, 1) );

// ------------------------------------------------------------------
// Getting "date" information if nothing is given
// ------------------------------------------------------------------

//check whether new moses file maching tdate exist, if not present show latest file
$tdate = $WTuser->current_tournament(0,false,0,true)->tdate - 7;
$date = gmdate( "ymd", $tdate*86400 );
//$date = "yymmdd";
if ( empty( glob("mosescoefs/moses".$date.".?pw") ) ) {
   $filelist = glob("mosescoefs/moses*.?pw");
   rsort($filelist);
   $mosesfile = substr( $filelist[0], 0, -3 );
   $date = substr( $mosesfile, -7, -1 );
} else {
   $mosesfile = "mosescoefs/moses".$date.".";
}

function file_get_contents_utf8($fn) {
	$content = file_get_contents($fn);
	return mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
}

$date = date_create_from_format( "ymd", $date )->format("d.m.Y");

echo nl2br(__("Date: ","wpwt").$date."\r\n\r\n");
echo nl2br( file_get_contents_utf8( $mosesfile.$c."pw" ) ); // get the contents, and echo it out.
?>
