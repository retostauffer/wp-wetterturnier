<?php

global $WTuser, $wpdb;
$cityObj  = $WTuser->get_current_cityObj();

error_reporting(E_ALL);
ini_set("display_errors", 1);

// Including jQuery scripts to display the synop symbols here
$WTuser->include_js_script("wetterturnier.synopsymbols");

// Loading all available stations for current city
$res = $wpdb->get_results(sprintf("SELECT wmo FROM %swetterturnier_stations "
//               ."WHERE cityID = %d AND active = 1 ORDER BY wmo ASC", 
               ."WHERE active = 1 ORDER BY wmo ASC",
               $wpdb->prefix, $cityObj->get("ID")));
$stations = array();
foreach ( $res as $rec ) { array_push($stations,$rec->wmo); }
?>

<style>
div.wt-synopsymbol {
   float: left;
   text-align: center;
   color: gray;
   border: 1px solid black;
   margin: 2px 2px 10px 0px;
   padding: 2px;
}
div.wt-synopsymbol:hover {
   border-color: #FF6600;
   color: #FF6600;
}
input[name='wt-synopsymbol-show'] {
   margin-right: 10px;
   margin-bottom: 20px;
}
div.wt-synsymbol-date {
   height: 110px;
}
div.wt-synsymbol-date h3 {
   display: block;
   height: 110px;
   width: 100px;
   text-align: center;
   padding: 0; margin: 0;
   float: left; 
   font-size: 1em;
   -webkit-transform: rotate(270deg);
   -moz-transform: rotate(270deg);
   -o-transform: rotate(270deg);
}
/* default size of an image */
div.wt-synopsymbol img {
   width: 80px; height: 80px;
}
</style>

<script>
jQuery(document).on('ready',function() {
   $ = jQuery
   $("#wt-synsymb-container").wtsynopsymbols(<?php printf("[%s]",join(",",$stations)); ?>,{"show":6});
});
</script>

<!-- Container will be filled by jQuery function later -->
<div id="wt-synsymb-container"></div>
