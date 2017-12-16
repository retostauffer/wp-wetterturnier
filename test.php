<?php


$x = file_get_contents("/var/www/html/referrerdata/mos/mos.json","r");

print_r( json_decode($x) );
?>
