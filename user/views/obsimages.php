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
div.wt-obsimage {
   display: none;
} 
</style>

<script type='text/javascript'>
jQuery(document).on('ready',function() {
   (function($) {

      // Highlight first one on start
      $("div#"+$("div.wt-obsimage").get(0).id).show()

      // Show different images
      $(document).on("click","input.button.wt-obsimages",function() {
         var info = $(this).attr('info');
         $("div.wt-obsimage").hide();
         $("div#wt-obsimage-"+info).show();
      });

      // Change image on the map
      $('select#obs_europe_select').change(function() {
         var val = $(this).attr('value')
         var img = '/referrerdata/obsimages/'+val+'_europe.png'
         console.log( img )
         $('#obs_europe').attr('src',img)
      });

   })(jQuery);
});
</script>


<?php
// Create buttons to switch between the stations
echo "<div id=\"wt-obsimage-nav\">\n";
foreach( $cityObj->stations() as $stnObj ) {
   printf("<input class=\"button wt-obsimages\" type=\"submit\" info=\"%d\" "
               ."value=\"Station %d\">\n",$stnObj->get('wmo'),$stnObj->get('wmo'));
}
// Append this extra 'map' button
printf("<input class=\"button wt-obsimages\" type=\"submit\" info=\"map\" value=\"Map\">\n");
echo "<div id=\"wt-obsimage-nav\">\n";


// Create image elements. There is some css/jQuery code to
// navigate trough these elements.
foreach( $cityObj->stations() as $stnObj ) {
   printf("<div class=\"wt-obsimage\" id=\"wt-obsimage-%d\">\n",$stnObj->get('wmo'));
   printf("<h1>%s [%d]</h1>\n",$stnObj->get('name'),$stnObj->get('wmo'));
   printf("<img src='/referrerdata/obsimages/synop_obs_%d.svg?%d'"
               ." width='99%%'></img>\n",$stnObj->get('wmo'),$stnObj->get('wmo'),$now);
   printf("</div>\n");
} ?>

<div class="wt-obsimage" id="wt-obsimage-map">
   <select id='obs_europe_select'>
      <option value='0000'>00:00 UTC</option>
      <option value='0100'>01:00 UTC</option>
      <option value='0200'>02:00 UTC</option>
      <option value='0300'>03:00 UTC</option>
      <option value='0400'>04:00 UTC</option>
      <option value='0500'>05:00 UTC</option>
      <option value='0600'>06:00 UTC</option>
      <option value='0700'>07:00 UTC</option>
      <option value='0800'>08:00 UTC</option>
      <option value='0900'>09:00 UTC</option>
      <option value='1000'>10:00 UTC</option>
      <option value='1100'>11:00 UTC</option>
      <option value='1200'>12:00 UTC</option>
      <option value='1300'>13:00 UTC</option>
      <option value='1400'>14:00 UTC</option>
      <option value='1500'>15:00 UTC</option>
      <option value='1600'>16:00 UTC</option>
      <option value='1700'>17:00 UTC</option>
      <option value='1800'>18:00 UTC</option>
      <option value='1900'>19:00 UTC</option>
      <option value='2000'>20:00 UTC</option>
      <option value='2100'>21:00 UTC</option>
      <option value='2200'>22:00 UTC</option>
      <option value='2300'>23:00 UTC</option>
   </select>

   <br>
   <img id='obs_europe' src='/referrerdata/obsimages/0000_europe.png'></img>
</div>


