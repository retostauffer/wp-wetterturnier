<?php

global $wpdb;
global $WTuser;

// Access only for logged in users
if ( $WTuser->access_denied() ) { return; }

// Including xml2json jQuery script
$WTuser->include_js_script("jquery.xml2json");
$WTuser->include_js_script("wetterturnier.mosforecasts");

// Reading latest run. ASCII file containing a time stamp
// like YYYY-mm-dd HH:MM.
$xmlfile  = sprintf("%s/user/xmlfiles/mosforecasts.xml",plugins_url("wp-wetterturnier"));

?>
<script type='text/javascript'>
jQuery(document).on('ready',function() {
   (function($) {

      // Define the image here. Image names are different form the
      // Wetterturnier city names, sorry.
      $("#wt-mosforecasts-container")
         .wtmosforecasts( "/referrerdata/mos/mos.json" );

   })(jQuery);
});
</script>

<style type='text/css'>
#wt-mosforecasts-navigation {
   display: block;
}
#wt-mosdata-navigation desc {
   display: inline-block;
   width: 300px;
   font-weight: bold;
}
#wt-mosdata-navigation select {
   display: inline-block;
   width: 300px;
   font-weight: bold;
}
#wt-mosdata-data table {
   margin-top: 20px;
}
#wt-mosdata-data table tr th {
   text-align: center;
}
#wt-mosdata-data table tr:hover,
#wt-mosdata-data table tr:hover td {
   background-color: #d4e0ec;
}

#wt-mosdata-data table tr:hover td.shaded {
   background-color: #c2d5e7;
}
#wt-mosdata-data table tr th.mosname,
#wt-mosdata-data table tr td.column-right {
   border-right: 3px solid black;
}
#wt-mosdata-data table tr th.mosname:last-child,
#wt-mosdata-data table tr td.column-right:last-child {
   border-right: none;
}

#wt-mosdata-data table td.param {
   font-weight: bold;
   text-align: left;
}
#wt-mosdata-data table td.data {
   text-align: right
}
#wt-mosdata-data .shaded {
   background-color: #eef0f2;
}

</style>


<div id='wt-mosforecasts-container'><div>

