

// Initialize demo table
jQuery(document).on('ready',function() {
   (function($) {

   // Function initializing the mos viewer 
   $.fn.wtmosforecasts = function( file ) {
      var container = $(this)

      // Loading json file with the mos data. 
      //console.log( file )
      $.ajax({
         type: "GET",
         url: file,
         dataType: "json",
         success: function (data) {

            // Append data to 'container'
            $(container).data(data)

            // Append navigation container
            $(container).append("<div id=\"wt-mosdata-navigation\"></div>");
            showTimestamps( "#wt-mosdata-navigation", data.timestamps )
            showLocations ( "#wt-mosdata-navigation", data.locations  )
            $(container).append("<div id=\"wt-mosdata-data\"></div>");

            showData( container, "#wt-mosdata-data" )
         },
         error: function() {
            alert("Problems reading the JSON file.");
            return;
         }
      });
   }

   // Creates init dropdown menu
   function showTimestamps( target, timestamps ) {
      // Helper function to add leading zeros
      function twodigits( x ) {
         if (x < 10) { return "0"+x } else { return x }
      }
      $(target).append("<desc>MOS rundate/runtime:</desc>")
               .append("<select id=\"wt-mosdata-init\"></select><br>");
      $.each( timestamps, function(key,value)  {
         var date = new Date( value * 1000. )
         var YYYY = date.getUTCFullYear()
         var mm   = twodigits( (date.getUTCMonth() + 1) )
         var dd   = twodigits( date.getUTCDate() )
         var HH   = twodigits( date.getUTCHours() )
         var MM   = twodigits( date.getUTCMinutes() )
         var readable = YYYY+"-"+mm+"-"+dd+" "+HH+":"+MM+" UTC"
         $("#wt-mosdata-init").append("<option value='"+value+"'>"+readable+"</option>");
      });
      $("#wt-mosdata-init").val( $("#wt-mosdata-init option:last").val() )

      // Append on-change func.
      $(target).on("change","#wt-mosdata-init",function() {
         showData( "#wt-mosforecasts-container", "#wt-mosdata-data" )
      });
   }

   // Creates location dropdown menu
   function showLocations( target, locations ) {
      $(target).append("<desc>Forecast location:</desc>")
               .append("<select id=\"wt-mosdata-location\"></select><br>");
      $.each( locations, function(key,value)  {
         $("#wt-mosdata-location").append("<option value='"+value+"'>"+value.toUpperCase()+"</option>");
      });
      $("#wt-mosdata-location").val( $("#wt-mosdata-location option:first").val() )

      // Append on-change func.
      $(target).on("change","#wt-mosdata-location",function() {
         showData( "#wt-mosforecasts-container", "#wt-mosdata-data" )
      });
   }

   // Show data based on selection
   function showData( container, target ) {
      // Loading selected values
      var sel_timestamp = $("#wt-mosdata-init").val()
      var sel_location  = $("#wt-mosdata-location").val()
      console.log(sel_timestamp)
      console.log(sel_location)

      // Subsetting data (given timestamp
      var parameters = $(container).data().parameters
      var data = $(container).data()

      data = data["data_"+sel_timestamp][sel_location]
      console.log( data )
      console.log( parameters )

      // Create table: Models in columns, parameters in rows
      $(target).empty().append("<table><thead><tr><th></th></tr></thead><tbody></tbody></table>")
      $.each( data, function(key,val) {
         $(target+" > table > thead > tr").append( "<th colspan=\"2\">"+key+"</th>")
      });
      $.each( parameters, function(k,param) {
         $(target+" > table > tbody").append("<tr class=\"param\"><td>"+param+"</td></tr>")
         $.each( data, function(key,val) {
            // Colorizing columns if required
            $(target+" > table > tbody > tr:last")
                  .append("<td class=\"data shaded\">"+val[param][0]+"</td>")
                  .append("<td class=\"data\">"+val[param][1]+"</td>")
         });
      });

      // Show links to raw flies below
      $(target).append("<div id=\"wt-mosforecasts-rawfiles\"><b>Raw files used:</b><ul></ul></div>")
      console.log( $(container).data().rawfiles )
      $.each( $(container).data().rawfiles, function(k,rawfile) {
         $("div#wt-mosforecasts-rawfiles > ul")
            .append("<li><a href=\""+rawfile+"\" target=\"_new\">"+rawfile+"</a></li>");
      });
   }

   })(jQuery);
});









