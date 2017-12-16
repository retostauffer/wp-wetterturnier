<?php

global $WTuser;

// Access only for logged in users
if ( $WTuser->access_denied() ) { return; }

// Including the forecast map jquery plus required xml2json script here
$WTuser->include_js_script("jquery.xml2json");
$WTuser->include_js_script("wetterturnier.maps-analysis");

// Location of the xml file containing the 'available products'
$xml_file = sprintf("%s/user/xmlfiles/maps_analysis.xml",
                    plugins_url("wp-wetterturnier"));
?>

<style type="text/css">
/* Styling the product navigation (including 
 * model, region, and product selection */
#wt-maps-navigation h1 {
   border-bottom: 3px solid #000;
   color: #2b2b2b;
   font-size: 14px;
   font-weight: 900;
   margin: 0 0 18px;
   padding-top: 7px;
   text-transform: uppercase;
   margin-bottom: 5px;
}
#wt-maps-navigation .models,
#wt-maps-navigation .regions,
#wt-maps-navigation .products {
   display: block;
   top: 0px;
   width: 200px;
   margin-right: 10px;
   position: relative;
   float: left;
}
#wt-maps-navigation ul li {
   border-radius: 2px;
   border: none;
   margin: 3px; padding: 0px 10px;
   background-color: #ccc;
}
#wt-maps-navigation ul li.selected {
   cursor: pointer;
   background-color: #FF6600;
   color: white;
   font-weight: bold;
}
#wt-maps-navigation ul li:hover {
   background-color: #41a62a;
}
#wt-maps-navigation ul li.selected:hover {
   color: black;
   background-color: #41a62a;
}

#wt-maps-navigation div {
   padding: 0; margin: 0;
}
#wt-maps-navigation ul {
   list-style: none;
   margin: 0; padding: 0;
}
/* Styling the time line navigation */
#wt-maps-timeline {
   clear: both;
   position: relative;
   margin: 20px 0 0 0;
   padding: 10px 0px;
   min-height: 30px;
}
#wt-maps-timeline ul {
   list-style: none;
   padding: 0px; margin: 0px;
}
#wt-maps-timeline ul li {
   border-radius: 2px;
   border: none;
   padding: 0; margin: 0;
   float: left;
   min-width: 40px;
   text-align: center;
   background-color: #ccc;
}
#wt-maps-timeline ul li[time="-"],
#wt-maps-timeline ul li[time="+"] {
   background-color: #FF6600;
   color: black;
   cursor: pointer;
}
#wt-maps-timeline ul li.selected {
   background-color: #FF6600;
   font-weight: bold;
   color: white;
}
#wt-maps-timeline   ul li:hover {
   background-color: #41a62a;
}
/* Styling image (setting maximum height and width) */
#wt-maps-image {
   max-height: 700px;
   min-height: 400px;
   max-width: 1200px;
   min-width: 650px;
   height: 100%;
   width: 100%;
}
</style>

<script type="text/javascript">
jQuery(document).on('ready',function() {
   $ = jQuery
   $("#wt-maps-container").wtmapsanalysis("<?php print $xml_file; ?>");
});
</script>

<!-- Container will be filled by jQuery function later -->
<div id="wt-maps-container"></div>
