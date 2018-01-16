<?php
// ------------------------------------------------------------------
/// @file user/views/obsimages.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief Frontent page to display the latest observations from the
///   wetterturnier stations. Creates a small jQuery based navigation
///   to loop/jump trough the plots for the different stations.
/// @details Based on the station definition or the wetterturnier
///   stations several images are plotted (outside the wetterturnier
///   wordpress plugin). The plugin itself only provides a very simple
///   navigation such that the users can see the lates observations in
///   a graphical representation.
///   This is the user frontpage view containing the navigation.
// ------------------------------------------------------------------


global $WTuser;

// Access only for logged in users
if ( $WTuser->access_denied() ) { return; } 

/// Include the required jQuer/d3 plugin
$WTuser->include_js_script("wetterturnier.obsPlot");
$WTuser->include_js_script("d3.v4.min");

/// Loading active city, see @ref wetterturnier_generalclass::get_current_cityObj
$cityObj  = $WTuser->get_current_cityObj();

/// Seconds since 1970-01-01 floored to the latest 2 minutes.
/// This is used to avoid that the latest-obs-plots are
/// cached by the browser for more than 2 minutes.
$now=(int)floor(time()/120)*120;

?>

<style type="text/css">
	div.wt-obsimage-nav {
	   display: block;
	   width: 99%;
	   border: 1px solid red;
	}
    /* obsPlot element styling */
    line.x, line.y {
        stroke: gray;
        stroke-width: 1px;
        stroke-dasharray: 3,3;
    }
    path[param='dd'] {
        stroke: gray;
    }
    path[param='pmsl'] {
        stroke: black;
    }
    path[param='t'] {
        stroke: red;
    }
    path[param='rh'] {
        stroke: green;
    }
    path[param='sun'] {
        stroke: orange;
    }
    path[param='cc'] {
        stroke: gray;
    }
    path[param='td'] {
        stroke: green;
    }
    path[param='ff'] {
        stroke: red;
    }
    path[param='ffx'] {
        stroke: blue;
    }
    g.x-grid {
        stroke: gray;
        stroke-dasharray: 3,1;
        stroke-width: .5px;
    }
 
</style>

<script type='text/javascript'>
jQuery(document).on('ready',function() {
   (function($) {

      // Show different images
      $(document).on("click","input.button.wt-obsimages",function() {
         var station = $(this).attr('info');
         args.statnr = station;
         $("#wt-obsplot-container").empty().obsPlot(args);
      });
      $(document).on("click","input.button.wt-obsdays",function() {
         var days = $(this).attr('info');
         args.days = days
         $("#wt-obsplot-container").empty().obsPlot(args);
      });

      // Adding the plots
      var args =  {
          ajaxurl: '<?php echo admin_url('admin-ajax.php'); ?>',
          statnr:11120,
          width: 800, height: 250,
          setup: [
              {main:"Temperature",parameter:["t","td"],
                  ylab:"Degrees C"},
              {main:"Wind",parameter:["ff","ffx"],
                  ylab:"m/s", ylim:[0,null]},
              {main:"Wind Direction",parameter:["dd"],
                  ylab:"Degrees", ylim:[0,360]},
              {main:"Cloud Cover and rel. Humidity",parameter:["cc","rh"],
                  ylab:"Percent",ylim:[0,100]},
              {main:"Sunshine Duration",parameter:["sun"],
                  ylab:"Minutes",ylim:[0,60]},
              {main:"Mean Sea Level Pressure",parameter:["pmsl"],
                  ylab:"Hectopascal", scalingfactor: 100.},
          ]
      };
      $("#wt-obsplot-container").obsPlot(args);

   })(jQuery);
});
</script>


<div id=\"wt-obsimage-nav\">
    <?php
    // Create buttons to switch between the stations
    foreach( $cityObj->stations() as $stnObj ) {
       printf("<input class=\"button wt-obsimages\" type=\"submit\" info=\"%d\" "
                   ."value=\"Station %d\">\n",$stnObj->get('wmo'),$stnObj->get('wmo'));
    }
    foreach ( array(1,3,5) as $days ) {
       printf("<input class=\"button wt-obsdays\" type=\"submit\" info=\"%d\" "
                   ."value=\"%dd\">\n",$days,$days);
    }
    ?>

</div>

<div id="wt-obsplot-container">
</div>

