<?php

global $WTuser;

// Access only for logged in users
if ( $WTuser->access_denied() ) { return; }

// Including the forecast map jquery plus required xml2json script here
$WTuser->include_js_script("jquery.xml2json");
$WTuser->include_js_script("wetterturnier.soundings-morten");

// Location of the xml file containing the 'available soundings'
$xml_file = sprintf("%s/user/xmlfiles/soundings-morten.xml",
                    plugins_url("wp-wetterturnier"));
?>

<style>
/* Styling the product navigation (including 
 * model, region, and product selection */
#wt-sounding-navigation h1 {
   border-bottom: 3px solid #000;
   color: #2b2b2b;
   font-size: 14px;
   font-weight: 900;
   margin: 0 0 18px;
   padding-top: 7px;
   text-transform: uppercase;
   margin-bottom: 5px;
}
#wt-sounding-navigation { float: none; min-height: 220px; clear: both; }
#wt-sounding-navigation .stations {
   display: block;
   top: 0px;
   width: 200px;
   margin-right: 10px;
   position: relative;
   float: left;
}
#wt-sounding-navigation ul li {
   border-radius: 2px;
   border: none;
   margin: 3px; padding: 0px 10px;
   background-color: #ccc;
}
#wt-sounding-navigation ul li.selected {
   cursor: pointer;
   background-color: #FF6600;
   color: white;
   font-weight: bold;
}
#wt-sounding-navigation ul li:hover {
   background-color: #41a62a;
}
#wt-sounding-navigation ul li.selected:hover {
   color: black;
   background-color: #41a62a;
}

#wt-sounding-navigation div {
   padding: 0; margin: 0;
}
#wt-sounding-navigation ul {
   list-style: none;
   margin: 0; padding: 0;
}
/* Styling the time line navigation */
#wt-sounding-timeline {
   clear: both;
   position: relative;
   margin: 20px 0 0 0;
   padding: 10px 0px;
   min-height: 30px;
}
#wt-sounding-timeline ul {
   list-style: none;
   padding: 0px; margin: 0px;
}
#wt-sounding-timeline ul li {
   border-radius: 2px;
   border: none;
   padding: 0; margin: 0;
   float: left;
   min-width: 40px;
   text-align: center;
   background-color: #ccc;
}
#wt-sounding-timeline ul li[time="-"],
#wt-sounding-timeline ul li[time="+"] {
   background-color: #FF6600;
   color: black;
   cursor: pointer;
}
#wt-sounding-timeline ul li.selected {
   background-color: #FF6600;
   font-weight: bold;
   color: white;
}
#wt-sounding-timeline   ul li:hover {
   background-color: #41a62a;
}
</style>

<script>
jQuery(document).on('ready',function() {
   $ = jQuery
   function setImageWidth() {
	  var width = parseInt( $("#wt-sounding-container").width() );
      var width = (width > 900) ? 900 : width;
      $( "#wt-map-image" ).width( width );
   }
   $("#wt-sounding-container").wtsoundingsmorten("<?php print $xml_file; ?>",setImageWidth);
   $(window).resize(function(){ setImageWidth(); });
});
</script>

<!-- Container will be filled by jQuery function later -->
<div id="wt-sounding-container"></div>
