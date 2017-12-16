<?php

global $wpdb;
global $WTuser;

// Access only for logged in users
if ( $WTuser->access_denied() ) { return; }

// Including xml2json jQuery script
$WTuser->include_js_script("jquery.xml2json");
$WTuser->include_js_script("wetterturnier.meteograms");

// Reading latest run. ASCII file containing a time stamp
// like YYYY-mm-dd HH:MM.
$imagedir = "/referrerdata/meteograms";
$xmlfile  = sprintf("%s/user/xmlfiles/meteograms.xml",plugins_url("wp-wetterturnier"));
$runhour  = "00";

?>
<script type='text/javascript'>
jQuery(document).on('ready',function() {
   (function($) {

      // Define the image here. Image names are different form the
      // Wetterturnier city names, sorry.
      $.meteogram = new Object()
      $.meteogram.xmlfile        = "<?php print $xmlfile; ?>"
      $.meteogram.imagedir       = "<?php print $imagedir; ?>"
      $.meteogram.runhour        = "<?php print $runhour; ?>"
      $.meteogram.lastrundefault = "00" // Taken if lastrun file could not be found/parsed
      $.meteogram.model          = "icon" // default type

      $("#wt-meteogram-container").wtmeteograms();

   })(jQuery);
});
</script>

<style type='text/css'>
#wt-meteogram-navigation {
}
#wt-meteogram-navigation h1 {
   border-bottom: 3px solid #000;
   color: #2b2b2b;
   font-size: 14px;
   font-weight: 900;
   margin: 0 0 18px;
   padding-top: 7px;
   text-transform: uppercase;
   margin-bottom: 5px;
}
#wt-meteogram-navigation div.models,
#wt-meteogram-navigation div.hours,
#wt-meteogram-navigation div.cities {
   display: block;
   width: 200px;
   margin-right: 10px;
   float: left;
   padding: 0; margin: 0;
}
#wt-meteogram-navigation div.cities { width: 350px; }

#wt-meteogram-navigation div ul {
   list-style: none;
   margin: 0;
   padding: 0 0 20px 0;
}
#wt-meteogram-navigation div ul li {
   border-radius: 2px;
   border: none;
   margin: 3px; padding: 0px 10px;
   background-color: #ccc;
}
#wt-meteogram-navigation div ul li.selected {
   cursor: pointer;
   background-color: #FF6600;
   color: white;
   font-weight: bold;
}
#wt-meteogram-navigation div ul li:hover {
   background-color: #41a62a;
}
#wt-meteogram-navigation div ul li.selected:hover {
   color: black;
   background-color: #41a62a;
}


*/
#wt-meteogram-image {
   clear: both;
   margin-top: 50px;
}
div#wt-meteogram-raw {
   display: block;
   width: 100%;
}
div#wt-meteogram-raw div.data {
   text-align: left;
   font-family: courier;
   font-size: 10px;
}
</style>


<div id='wt-meteogram-container'><div>
















