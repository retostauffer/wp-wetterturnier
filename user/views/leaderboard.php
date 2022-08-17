<?php
# -------------------------------------------------------------------
# - NAME:        ranking.php
# - AUTHOR:      Reto Stauffer
# - DATE:        2014-11-10
# -------------------------------------------------------------------
# - DESCRIPTION: There is a shortcode called [wetterturnier_ranking]
#                with several options to display ranking tables.
#                All will call this file. Input arguments specified
#                by the user (on shortcode) will be set on $args.
#                Depending on the input, the I/O will be prepared.
# -------------------------------------------------------------------
# - EDITORIAL:   2014-11-10, RS: Created file on thinkreto.
# -------------------------------------------------------------------
# - L@ST MODIFIED: 2017-06-11 13:05 on prognose2.met.fu-berlin.de
# -------------------------------------------------------------------



global $wpdb;
global $WTuser;
$args = (object)$args;

// Devel: show all php errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

$WTuser->show_leading((int)$args->city,(int)$args->tdate,
                      (int)$args->number,$args->style);
?>
