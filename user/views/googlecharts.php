<?php
// ------------------------------------------------------------------
/// @file googlecharts.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief Interface to create some plots.
// ------------------------------------------------------------------


global $WTuser;

// The jQuery code for sending applications. 
///$ndays = (int)$WTuser->options->wetterturnier_betdays;
///$chartHandler = new wetterturnier_chartHandler( 'test in googleclass', $ndays );
// Including the jquery code for this application
$WTuser->include_js_script("wetterturnier.usersearch");
$WTuser->include_js_script("wetterturnier.googlecharts");
//$WTuser->include_js_script("jquery-ui.min");

?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script>

   jQuery(document).on("ready",function() {
      (function($) {

         // Prevent enter button on this page (not that the user submits
         // the <form> below)
         $(window).keydown(function(event){
           if(event.keyCode == 13) { event.preventDefault(); return false; }
         });

         // Initialize user search
         var adminurl = <?php printf("'%s'\n",admin_url('admin-ajax.php')); ?>
         var opts  = {target:"#chart-div",
                      call: "timeseries_user_points",
                      cityID:<?php printf("'%d'",$WTuser->get_current_cityObj()->get("ID")); ?>}
         var opts = {addul:"#selected-users",ulmax:4}
         $('#chart-options div.user-search').usersearch(adminurl, opts);
         function get_current_plottype() {
            return( $("#chart-options").find("[name='opt-plottype']").val() );
         }

         // Execute whenever usersearch changes the userID to trigger the
         // regeneration of the googlechart. 
         $("#selected-users").live("change",function() {
            refresh_chart( get_current_plottype() )
         });
         $("#selected-users").on("click","li.selected-user",function() {
            $(this).remove()
            refresh_chart("timeseries_user_points")
         });
         // Execute as soon as observed elements change
         $("#chart-options").on("change",".observe",function() {
            refresh_chart( get_current_plottype() )
         });
         function refresh_chart(call) {

            // Show/hide placeholder
            if ( $("#selected-users").find(".selected-user").length === 0 ) {
               $("#selected-users li.placeholder").show()
            } else { $("#selected-users li.placeholder").hide()  }

            // Getting sleepy option
            var opt_column    = $("#chart-options").find("[name='opt-column']:checked").val()
            if ( opt_column !== "points" ) {
               $("#chart-options").find("[name='opt-sleepy'][value='0']").attr("checked",true)
            }
            var opt_sleepy   = $("#chart-options").find("[name='opt-sleepy']:checked").val()
            var opt_cityID    = $("#chart-options").find("[name='opt-cityID']").val()

            // Read selected users
            var lis = $("#selected-users").find("li.selected-user")
            //if ( lis.length === 0 ) { return }
            var uid = ""
            $.each( lis, function(key,val) {
               if ( uid.length == 0 ) { uid = $(val).attr("userid") }
               else { uid = uid + "," + $(val).attr("userid") }
            });

            // Argument options for googlechart function call
            if ( call === "timeseries_user_points" ) {
               var opts = {call: call, userID: uid, cityID: opt_cityID, sleepy: opt_sleepy,
                           column: opt_column }
            } else if ( call === "participants_counts" ) {
               var opts = {call: call, cityID: opt_cityID}
            } else if ( call === "init" ) {
               var opts = {call: call}
            } else {
               alert("Undefind procedure creating the opts object for \""+call+"\"!");
            }

            $.fn.googlechart(adminurl,"chart-div",opts);
         }

         // If there are post-args: create plot
         <?php
         $get_opts = array();
         if ( count($_GET) > 0 ) {
            foreach ( $_GET as $key=>$val ) { $get_opts[$key] = $val; } ?>
            var getopts = <?php print json_encode($get_opts); ?>;
         <?php } ?>

         // Initialize
         if ( typeof(getopts) !== "undefined" ) {
            console.log( adminurl )
            console.log( getopts )
            $.fn.googlechart(adminurl,"chart-div",getopts);
         } else {
            refresh_chart( "init" )
         }

      })(jQuery);
   });
</script>


<style>
#chart-options div {
   min-height: 30px;
}
#selected-users {
   display: block;
   list-style: none;
   margin: 10px 0px;
   padding: 0;
   float: inline;
}
#selected-users:before {
   content: "Selected users:";
   font-weight: bold;
   clear: both;
   padding-right: 10px;
}
#selected-users li {
   display: inline;
   margin-right: 10px;
   border-radius: 2px;
}
#selected-users li.selected-user:before {
   content: "X";
   display: inline-block;
   height: 15px;
   width: 15px;
   color: black;
   cursor: pointer;
}
#selected-users li.selected-user {
   padding: 3px 15px 3px 8px;
   background-color: #ff6600;
   color: white;
   font-weight: bold;
}
#selected-users li.placeholder {
   padding: 3px 15px 3px 15px;
   background-color: #eef0f2;
   border: .5px solid black;
}
.googlechart-noplot {
   display: block;
   width: 500px;
   min-width: 300px;
   text-align: center;
   vertical-align: middle;
   color: #ffcc00;
   padding: 50px;
   border: 2px solid gray;
   margin: 20px 0;
}
#chart-share-url:before {
   content: "Share Plot:";
   padding-right: 5px;
   font-weight: bold;
}
#chart-share-url {
   border: 1px solid black;
   border-radius: 5px;
   background-color: #eef0f2;
   padding: 2px 5px;
   margin: 10px 0;
}
</style>

<div id='chart-options' autocomplete="off">
   <!-- place where usersearch stores the result -->
   <div id="user-search" class='user-search'></div><br>
   <ul id="selected-users">
      <li class="placeholder"><?php _e("No user selected","wpwt"); ?></li>
   </ul>
   <div id="plot-type">
      <b>Select plot type:</b>&nbsp;
      <select name="opt-plottype" class="observe">
         <option value="" selected>Select a plot type first</option>
         <option value="timeseries_user_points">Timeseries Points</option>
         <!--<option value="timeseries_user_points">Timeseries Parameter Points</option>-->
         <option value="participants_counts">Participants counts</option>
      </select>
   </div>
   <div id="paramID">
      <b>Select a parameter:</b>&nbsp;
      <select name="opt-paramID" class="observe">
      <?php
      $selected = "selected";
      foreach ( $WTuser->get_param_data() as $rec ) {
         printf("  <option value='%d' %s>%s</s>",$rec->paramID,
               $selected,$rec->paramName); $selected = "";
      }
      ?>
      </select>
   </div>
   <div id="cityID">
      <b>Select a city:</b>&nbsp;
      <select name="opt-cityID" class="observe">
      <?php
      $curCityObj = $WTuser->get_current_cityObj();
      foreach ( $WTuser->get_all_cityObj() as $cityObj ) {
         printf("  <option value='%d' %s>%s</s>",$cityObj->get("ID"),
               ($cityObj->get("ID") === $curCityObj->get("ID") ? "selected" : ""),
               $cityObj->get("name"));
      }
      ?>
      </select>
   </div>
   <div id="sleepy">
      <b>Expand with Sleepy:</b>&nbsp;
      <input class="observe" type="radio" name="opt-sleepy" value="0"> No
      <input class="observe" type="radio" name="opt-sleepy" value="1" checked> Yes
   </div>
   <div id="pointselector">
      <b>Show points for Saturday/Sunday/Total:</b>&nbsp;
      <input class="observe" type="radio" name="opt-column" value="points_d1"> Saturday
      <input class="observe" type="radio" name="opt-column" value="points_d2"> Sunday
      <input class="observe" type="radio" name="opt-column" value="points" checked> Total
   </div>
</div>

<div id='chart-div'></div>
<div id='chart-share-url'></div>

