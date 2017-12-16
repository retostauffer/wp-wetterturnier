<?php

global $wpdb;
global $WTuser;

// Access only for logged in users
if ( $WTuser->access_denied() ) { return; }

// Including xml2json jQuery script
$WTuser->include_js_script("jquery.xml2json");
$WTuser->include_js_script("wetterturnier.meteogramdata");

// Reading latest run. ASCII file containing a time stamp
// like YYYY-mm-dd HH:MM.
$datadir = "/publicdata/GFS";
$xmlfile  = sprintf("%s/user/xmlfiles/meteogramdata.xml",plugins_url("wp-wetterturnier"));
$runhour  = "00";

?>
<script type='text/javascript'>
jQuery(document).on('ready',function() {
   (function($) {

      // Define the image here. Image names are different form the
      // Wetterturnier city names, sorry.
      $.meteogramdata = new Object()
      $.meteogramdata.xmlfile        = "<?php print $xmlfile; ?>"
      $.meteogramdata.datadir        = "<?php print $datadir; ?>"
      $.meteogramdata.runhour        = "<?php print $runhour; ?>"
      $.meteogramdata.lastrundefault = "00" // Taken if lastrun file could not be found/parsed
      $.meteogramdata.model          = "icon" // default type

      $("#wt-meteogramdata-container").wtmeteogramdata();

   })(jQuery);
});
</script>

<style type='text/css'>
#wt-meteogramdata-navigation {
   display: block;
}
#wt-meteogramdata-navigation h1 {
   border-bottom: 3px solid #000;
   color: #2b2b2b;
   font-size: 14px;
   font-weight: 900;
   margin: 0 0 18px;
   padding-top: 7px;
   text-transform: uppercase;
   margin-bottom: 5px;
}
#wt-meteogramdata-navigation div.models,
#wt-meteogramdata-navigation div.hours,
#wt-meteogramdata-navigation div.cities {
   display: block;
   width: 200px;
   margin-right: 10px;
   float: left;
   padding: 0; margin: 0;
}
#wt-meteogramdata-navigation div.cities { width: 350px; }

#wt-meteogramdata-navigation div ul {
   list-style: none;
   margin: 0;
   padding: 0 0 20px 0;
}
#wt-meteogramdata-navigation div ul li {
   border-radius: 2px;
   border: none;
   margin: 3px; padding: 0px 10px;
   background-color: #ccc;
}
#wt-meteogramdata-navigation div ul li.selected {
   cursor: pointer;
   background-color: #FF6600;
   color: white;
   font-weight: bold;
}
#wt-meteogramdata-navigation div ul li:hover {
   background-color: #41a62a;
}
#wt-meteogramdata-navigation div ul li.selected:hover {
   color: black;
   background-color: #41a62a;
}


#wt-meteogramdata {
   display: block;
   width: 100%;
   text-align: left;
   font-family: courier;
   font-size: 10px;
}
#wt-meteogramdata-link {
   padding: 1em 0;
}
</style>


<div id='wt-meteogramdata-container'><div>

