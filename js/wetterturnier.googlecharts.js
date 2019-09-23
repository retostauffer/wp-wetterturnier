 
// ------------------------------------------------------------------
/// @file wetterturnier.googlecharts.js
/// @author Reto Stauffer
/// @date 28 June 2017
/// @brief Creates a jQuery function .googlechart which is used to
///   create/show different graphical stats.
// ------------------------------------------------------------------
jQuery(document).on('ready',function() {
   (function($) {

   // ---------------------------------------------------------------
   /// @details Main function or object which provides several
   ///   graphical statistics/charts.
   // ---------------------------------------------------------------
   $.fn.googlechart = function( ajaxurl, targetId, inputs ) {

      var target = document.getElementById(targetId)

      // ------------------------------------------------------------
      // Call the function depending on input.call
      // ------------------------------------------------------------
      // Hide all options first
      $("#chart-options").children("div").hide()
      $("#chart-options").children("ul").hide()
      if ( inputs.call === undefined | inputs.call === "init" ) {
         // Show plot type option
         $("#chart-options").children("div#plot-type").show()
         $(target).html("<div class='googlechart-noplot'>No method selected, cannot draw plot!</div>")
         return(false)
         alert("Error: inputs.call missing when calling googlechart function.")
      } else if ( ! eval("$.isFunction("+inputs.call+")") ) {
         $(target).html("Sorry, method does not exist. Problem of the jQuery object.")
         alert("Error: inputs.call "+inputs.call+" googlechart: function not defined.")
      } else {
         eval(inputs.call+"(ajaxurl,target,inputs)")
         append_share_url( inputs )
      }

      // ------------------------------------------------------------
      // Append sharing link
      // ------------------------------------------------------------
      function append_share_url( inputs ) {
         console.log( window.location.protocol )
         var url = window.location.protocol+"//"+window.location.hostname + window.location.pathname
         // Append opts
         var first = true;
         $.each( inputs, function(key,val) {
            if ( first ) { sep = "?" } else { sep = "&" }
            first = false // setting first to false
            url = url + sep + key + "=" + val 
         });
         $("#chart-share-url").html("<a href='"+url+"' target='_blank'>"+url+"</a>")
      }

      // ------------------------------------------------------------
      // Returns points (weekend sum points) for one or multiple users.
      // ------------------------------------------------------------
      function timeseries_user_points( ajaxurl, target, inputs ) {

         // Object to store the wetterturnier data
         var wtdata;
         // Input arguments to ajax call
         var args = { action:"timeseries_user_points_ajax", userID:inputs.userID, cityID:inputs.cityID,
                      sleepy: inputs.sleepy, column: inputs.column } 

         // Enable options for this plot type
         $.each( ["#plot-type","#user-search","#selected-users","#cityID","#sleepy","#pointselector"], function(k,v) {
            $("#chart-options").children(v).show()
         });

         // Calling ajax now
         $.ajax({
             url: ajaxurl, dataType: 'json', cache: false, type: 'post', async: false, data: args,
             success: function( data ) {
               wtdata = data
               // Convert to date
               for ( var i=0; i < wtdata.data.length; i++ ) {
                  x = new Date(wtdata.data[i][0] * 1000);
                  wtdata.data[i][0] = new Date(x.getUTCFullYear(),x.getUTCMonth(),x.getUTCDate())
               }
               // Development thing: show sql command in console if appended to return object
               if ( wtdata.sql   !== undefined ) {
                  console.log( wtdata.sql )
               }
               // If there was an error reported by the php script: exit
               if ( wtdata.error !== undefined ) {
                  alert( wtdata.error )
                  wtdata = false
               // If there are no data: exit
               } else if ( wtdata.num_rows === 0 ) {
                  alert("Sorry, no data for this request");
                  wtdata = false
               }

             },
             error: function(xhr, status, error) {
               console.log( xhr.responseText )
               console.log( error )
               var err = eval("(" + xhr.responseText + ")");
               alert( "ajax error: not able to load data." );
             }
         });

         if ( ! wtdata ) { $(target).empty(); return }

         google.charts.load('current', {packages: ['corechart', 'line']});
         google.charts.setOnLoadCallback(show_data);

         function show_data( ) {
            var data = new google.visualization.DataTable();
            data.addColumn('date', 'tdate');
            $.each(wtdata.user_login,function(key,val) { data.addColumn('number', val ); });
            data.addRows( wtdata.data );
         
            // Set chart
            var chart = new google.visualization.LineChart( target )

            // Draw chart
            var options = {
              title: wtdata.title,
              colors: wtdata.line_colors,
              width: 1500, height: 500,
              hAxis: { title: wtdata.xlabel },
              vAxis: { title: wtdata.ylabel },
              //vAxis: {minValue: 0},
              explorer: { 
                actions: ['dragToZoom', 'rightClickToReset'],
                axis: 'horizontal',
                keepInBounds: true,
                maxZoomIn: 4.0},
            };
            chart.draw(data, options);
         } // End of function 'show_data'

      } // End of timeseries_user_points


      // ------------------------------------------------------------
      // Returns points (weekend sum points) for one or multiple users.
      // ------------------------------------------------------------
      function participants_counts( ajaxurl, target, inputs ) {

         // Object to store the wetterturnier data
         var wtdata;
         // Input arguments to ajax call
         var args = { action:"participants_counts_ajax", cityID:inputs.cityID }

         $.each( ["#plot-type"], function(k,v) {
            $("#chart-options").children(v).show()
            console.log(' ---------- ' + v )
         });

         // Calling ajax now
         $.ajax({
             url: ajaxurl, dataType: 'json', cache: false, type: 'post', async: false, data: args,
             success: function( data ) {
               wtdata = data
               // Convert to date
               for ( var i=0; i < wtdata.data.length; i++ ) {
                  x = new Date(wtdata.data[i][0] * 1000);
                  wtdata.data[i][0] = new Date(x.getUTCFullYear(),x.getUTCMonth(),x.getUTCDate())
               }
               // Development thing: show sql command in console if appended to return object
               if ( wtdata.sql   !== undefined ) {
                  console.log( wtdata.sql )
               }
               // If there was an error reported by the php script: exit
               if ( wtdata.error !== undefined ) {
                  alert( wtdata.error )
                  wtdata = false
               // If there are no data: exit
               } else if ( wtdata.num_rows === 0 ) {
                  alert("Sorry, no data for this request");
                  wtdata = false
               }

             },
             error: function(xhr, status, error) {
               console.log( xhr.responseText )
               console.log( error )
               var err = eval("(" + xhr.responseText + ")");
               alert( "ajax error: not able to load data." );
             }
         });

         if ( ! wtdata ) { $(target).empty(); return }

         google.charts.load('current', {packages: ['corechart', 'line']});
         google.charts.setOnLoadCallback(show_data);

         function show_data( ) {
            var data = new google.visualization.DataTable();
            data.addColumn('date', 'tdate');
            $.each(wtdata.names,function(key,val) { data.addColumn('number', val ); });
            data.addRows( wtdata.data );
         
            // Set chart
            var chart = new google.visualization.LineChart( target )

            // Draw chart
            var options = {
              title: wtdata.title,
              colors: wtdata.line_colors,
              width: 1500, height: 500,
              hAxis: { title: wtdata.xlabel },
              vAxis: { title: wtdata.ylabel },
              //vAxis: {minValue: 0},
              explorer: { 
                actions: ['dragToZoom', 'rightClickToReset'],
                axis: 'horizontal',
                keepInBounds: true,
                maxZoomIn: 4.0},
            };
            chart.draw(data, options);
         } // End of function 'show_data'

      } // End of timeseries_user_points

   } // end of $.fn.googlechart

   })(jQuery);
});
