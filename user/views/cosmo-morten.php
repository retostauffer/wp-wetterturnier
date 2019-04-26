<?php

global $WTuser;

// Access only for logged in users
if ( $WTuser->access_denied() ) { return; }

// Including the forecast map jquery plus required xml2json script here
$WTuser->include_js_script("jquery.xml2json");
$WTuser->include_js_script("wetterturnier.cosmo-morten");

// Location of the xml file containing the 'available cosmos'
$xml_file = sprintf("%s/user/xmlfiles/cosmo-morten.xml",
                    plugins_url("wp-wetterturnier"));
?>

<style>
/* Styling the product navigation (including 
 * model, region, and product selection */
#wt-cosmo-navigation h1 {
   border-bottom: 3px solid #000;
   color: #2b2b2b;
   font-size: 14px;
   font-weight: 900;
   margin: 0 0 18px;
   padding-top: 7px;
   text-transform: uppercase;
   margin-bottom: 5px;
}
#wt-cosmo-navigation { float: none; min-height: 220px; clear: both; }
#wt-cosmo-navigation .stations {
   display: block;
   top: 0px;
   width: 200px;
   margin-right: 10px;
   position: relative;
   float: left;
}
#wt-cosmo-navigation ul li {
   border-radius: 2px;
   border: none;
   margin: 3px; padding: 0px 10px;
   background-color: #ccc;
}
#wt-cosmo-navigation ul li.selected {
   cursor: pointer;
   background-color: #FF6600;
   color: white;
   font-weight: bold;
}
#wt-cosmo-navigation ul li:hover {
   background-color: #41a62a;
}
#wt-cosmo-navigation ul li.selected:hover {
   color: black;
   background-color: #41a62a;
}

#wt-cosmo-navigation div {
   padding: 0; margin: 0;
}
#wt-cosmo-navigation ul {
   list-style: none;
   margin: 0; padding: 0;
}
/* Styling the time line navigation */
#wt-cosmo-timeline {
   clear: both;
   position: relative;
   margin: 20px 0 0 0;
   padding: 10px 0px;
   min-height: 30px;
}
#wt-cosmo-timeline ul {
   list-style: none;
   padding: 0px; margin: 0px;
}
#wt-cosmo-timeline ul li {
   border-radius: 2px;
   border: none;
   padding: 0; margin: 0;
   float: left;
   min-width: 40px;
   text-align: center;
   background-color: #ccc;
}
#wt-cosmo-timeline ul li[time="-"],
#wt-cosmo-timeline ul li[time="+"] {
   background-color: #FF6600;
   color: black;
   cursor: pointer;
}
#wt-cosmo-timeline ul li.selected {
   background-color: #FF6600;
   font-weight: bold;
   color: white;
}
#wt-cosmo-timeline   ul li:hover {
   background-color: #41a62a;
}
</style>

<script>
jQuery(document).on('ready',function() {
   $ = jQuery
   function setImageWidth() {
	  var width = parseInt( $("#wt-cosmo-container").width() );
      var width = (width > 1200) ? 1200 : width;
      $( "#wt-map-image" ).width( width );
   }
   $("#wt-cosmo-container").wtcosmomorten("<?php print $xml_file; ?>",setImageWidth);
   $(window).resize(function(){ setImageWidth(); });
});
</script>

<!-- Container will be filled by jQuery function later -->
<div id="wt-cosmo-container"></div>
