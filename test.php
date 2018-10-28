<?php

#// Output as text/plain
require_once("../../../wp-config.php");

delete_option('wetterturnier_calendar_ndays');
add_option(   'wetterturnier_calendar_ndays', 50, '', 'yes');


global $WTuser;

print_r($WTuser->options);

die();

?>
