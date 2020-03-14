

// Initialize demo table
jQuery(document).on('ready',function() {

   // Copy jQuery to $
   $ = jQuery

   $.fn.wtsoundingsicon = function( xmlfile, callback = null ) {

      var target = $(this)

      // Loading required xml file
      $.ajax({
         type: "GET",
         url: xmlfile, 
         dataType: "text",
         success: function (xml) {
            data = $.xml2json( xml )
            // Show data
            showdata(target, data)
         },
         error: function() {
            alert("Problems reading the xml file.");
         },
         complete: function() {
            if ( $.isFunction(callback) ) { callback(); } 
         } 
      });

      // Jumping one time step forwards. If active is the last one,
      // jumping to the first one.
      function timestep_forwards(steps=1) {
         // Check which time list element is the selected element:
         var total   = $("#wt-sounding-timeline ul li").length
         var current = $("#wt-sounding-timeline ul li.selected").index() + steps
         // Change selection
         if ( (current+1) < (total) ) {
            $("#wt-sounding-timeline ul li.selected").removeClass('selected')
            $("#wt-sounding-timeline ul li:nth-child("+(current+1)+")").addClass("selected")
         // Else jumping back to the first one!
         } else {
            $("#wt-sounding-timeline ul li.selected").removeClass('selected')
            $("#wt-sounding-timeline ul li:nth-child(2)").addClass('selected')
         }
         showImage()

      }

      // Jumping one time step backwards. If active is the firstone,
      // jumping to the last timestep.
      function timestep_backwards(steps=1) {
         // Check which time list element is the selected element:
         var total   = $("#wt-sounding-timeline ul li").length
         var current = $("#wt-sounding-timeline ul li.selected").index() + 1
         if ( current > (steps+1) ) { 
            $("#wt-sounding-timeline ul li.selected").removeClass('selected')
            $("#wt-sounding-timeline ul li:nth-child("+(current-steps)+")").addClass('selected')
         // Else jumping to the last entry
         } else {
            $("#wt-sounding-timeline ul li.selected").removeClass('selected')
            $("#wt-sounding-timeline ul li:nth-child("+(total-1)+")").addClass('selected')
         }
         showImage()

      }

      //jumping to a certain position (e.g. first/last)
      //last step can be triggered by step=0
      function timestep(step) {
         var total   = $("#wt-sounding-timeline ul li").length
         if ( step > 0 ) {
            $("#wt-sounding-timeline ul li.selected").removeClass('selected')
            $("#wt-sounding-timeline ul li:nth-child("+(step+1)+")").addClass('selected')
         // Else jumping to the last entry
         } else {
            $("#wt-sounding-timeline ul li.selected").removeClass('selected')
            $("#wt-sounding-timeline ul li:nth-child("+(total-1)+")").addClass('selected')
         }
         showImage()

      }


      // Change time step function.
      function change_time(to) {
          if      ( to == "+" ) { timestep_forwards(); }
          else if ( to == "-" ) { timestep_backwards(); }
          else {
              $("#wt-sounding-timeline ul li.selected").removeClass("selected")
              $("#wt-sounding-timeline ul li[time='"+to+"']").addClass("selected")
              showImage()
          }
      }

      function showdata(target, data) {

         // Clear target object
         $(this).empty()

         // Append necessary blocks for the forecast data navigation
         // consisting of: model, region, product, times, image
         $("<img id='wt-sounding-image'></img>").appendTo( target )
         $("<div id='wt-sounding-timeline'></div>").appendTo( target )
         $("<div id='wt-sounding-navigation'></div>").appendTo( target )
         $("#wt-sounding-navigation").append("<div class='stations'>Stations</div>")
         //$("#wt-sounding-navigation").append("<div class='clear'></div>")
         $("#wt-sounding-navigation").append("<div class='types'>Types</div>")

         // Add functionality that each click onto the image itself
         // will act like a "go forward in time by one time step"
         $("#wt-sounding-container").on("click","img",function() { timestep_forwards(); });

         //var type_keyCodes = [83, 84, 72]
         //var station_keyCodes = [66, 87, 90, 73, 76]

         // Adding keyboard navigation functionality
         $("body").on("keydown",function(e){
            if      ( e.keyCode == 39 ) { timestep_forwards(); }
            else if ( e.keyCode == 34 ) { e.preventDefault(); timestep_forwards(steps=2); }
            else if ( e.keyCode == 37 ) { timestep_backwards(); }
            else if ( e.keyCode == 33 ) { e.preventDefault(); timestep_backwards(steps=2); }
            else if ( e.keyCode == 35 ) { e.preventDefault(); timestep(0); } //end -> last step 
            else if ( e.keyCode == 36 ) { e.preventDefault(); timestep(1); } //pos1 -> first step
            else if ( e.keyCode == 38 ) {
                e.preventDefault()
                var current = $("#wt-sounding-navigation .stations ul li.selected").index()+1
                // Remove current selection
                $("#wt-sounding-navigation .stations ul li.selected").removeClass("selected")
                // Add new selection
                if ( current == 1 ) {
                   var las = $("#wt-sounding-navigation .stations ul li").length
                   $("#wt-sounding-navigation .stations ul li").last().addClass("selected")
                } else {
                   $("#wt-sounding-navigation .stations ul li:nth-child("+(current-1)+")").addClass("selected")
                }
                showImage(); // Update image
            } else if ( e.keyCode == 40 ) {
                e.preventDefault()
                var current = $("#wt-sounding-navigation .stations ul li.selected").index()+1
                // Remove current selection
                $("#wt-sounding-navigation .stations ul li.selected").removeClass("selected")
                // Add new selection
                if ( current >= $("#wt-sounding-navigation .stations ul li").length ) {
                   $("#wt-sounding-navigation .stations ul li").first().addClass("selected")
                } else {
                   $("#wt-sounding-navigation .stations ul li:nth-child("+(current+1)+")").addClass("selected")
                }
                showImage(); // Update image

            //TODO group type and station keyCode to make it slimmer. maybe define keys in xml
            //else if ( type_keyCodes.includes( e.keyCode ) 

            } else if ( e.keyCode == 83 ) { //s
                e.preventDefault()
                // Remove current selection
                $("#wt-sounding-navigation .types ul li.selected").removeClass("selected")
                // Add new selection
                $("#wt-sounding-navigation .types ul li:nth-child(2)").addClass("selected")
                $("#wt-sounding-navigation .types ul li.selected").attr("type", "stuve")
                showImage(); // Update image
            } else if ( e.keyCode == 84 ) { //t
                e.preventDefault()
                // Remove current selection
                $("#wt-sounding-navigation .types ul li.selected").removeClass("selected")
                // Add new selection
                $("#wt-sounding-navigation .types ul li:nth-child(1)").addClass("selected")
                $("#wt-sounding-navigation .types ul li.selected").attr("type", "skewT")
                showImage(); // Update image
            } else if ( e.keyCode == 72 ) { //h
                e.preventDefault()
                // Remove current selection
                $("#wt-sounding-navigation .types ul li.selected").removeClass("selected")
                // Add new selection
                $("#wt-sounding-navigation .types ul li:nth-child(3)").addClass("selected")
                $("#wt-sounding-navigation .types ul li.selected").attr("type", "hodo")
                showImage(); // Update image
            } else if ( e.keyCode == 66 ) { //b
                e.preventDefault()
                // Remove current selection
                $("#wt-sounding-navigation .stations ul li.selected").removeClass("selected")
                // Add new selection
                $("#wt-sounding-navigation .stations ul li:nth-child(1)").addClass("selected")
                $("#wt-sounding-navigation .types ul li.selected").attr("station", "berlin")
                showImage(); // Update image
            } else if ( e.keyCode == 87 ) { //w
                e.preventDefault()
                // Remove current selection
                $("#wt-sounding-navigation .stations ul li.selected").removeClass("selected")
                // Add new selection
                $("#wt-sounding-navigation .stations ul li:nth-child(2)").addClass("selected")
                $("#wt-sounding-navigation .types ul li.selected").attr("station", "wien")
                showImage(); // Update image
            } else if ( e.keyCode == 90 ) { //z
                e.preventDefault()
                // Remove current selection
                $("#wt-sounding-navigation .stations ul li.selected").removeClass("selected")
                // Add new selection
                $("#wt-sounding-navigation .stations ul li:nth-child(3)").addClass("selected")
                $("#wt-sounding-navigation .types ul li.selected").attr("station", "zurich")
                showImage(); // Update image
            } else if ( e.keyCode == 73 ) { //i
                e.preventDefault()
                // Remove current selection
                $("#wt-sounding-navigation .stations ul li.selected").removeClass("selected")
                // Add new selection
                $("#wt-sounding-navigation .stations ul li:nth-child(4)").addClass("selected")
                $("#wt-sounding-navigation .types ul li.selected").attr("station", "innsbruck")
                showImage(); // Update image
            } else if ( e.keyCode == 76 ) { //l
                e.preventDefault()
                // Remove current selection
                $("#wt-sounding-navigation .stations ul li.selected").removeClass("selected")
                // Add new selection
                $("#wt-sounding-navigation .stations ul li:nth-child(5)").addClass("selected")
                $("#wt-sounding-navigation .types ul li.selected").attr("station", "leipzig")
                showImage(); // Update image
            }
         });

         // Appending models
         showSounding(data, undefined)

      }

      // Shows model selection
      function showSounding(data, selected) {

         // Append available stations to navigation
         var target = $("#wt-sounding-navigation").find(".stations").first();
         $(target).empty().append("<h1>City</h1><ul></ul>")
         $.each(data.stations.station, function(key, val) {
            // Default: take first one if input unset
            if ( selected == undefined ) { selected = val.imgname; }
            $(target).find("ul")
               .append("<li station='"+val.imgname+"'>" + val.name + "</li>")
            // If match:
            //alert(val.imgname)
            if ( selected == val.imgname ) { $(target).find("ul li").last().addClass("selected") } 
         });
         // Appending interactive functionality
         $("#wt-sounding-navigation .stations ul").on("click","li",function() {
            $(this).parent("ul").find("li").removeClass('selected')
            $(this).addClass('selected')
            showImage() // Update image
         });         

         
         selected_type = undefined
         // Append available types to navigation
         var target = $("#wt-sounding-navigation").find(".types").first();
         $(target).empty().append("<h1>Type</h1><ul></ul>")
         $.each(data.types.type, function(key, val) {
            // Default: take first one if input unset
            if ( selected_type == undefined ) { selected_type = val.imgname; }
            $(target).find("ul")
               .append("<li type='"+val.imgname+"'>" + val.name + "</li>")
            // If match:
            //alert(val.imgname)
            if ( selected_type == val.imgname ) { $(target).find("ul li").last().addClass("selected") }
         });
         // Appending interactive functionality
         $("#wt-sounding-navigation .types ul").on("click","li",function() {
            $(this).parent("ul").find("li").removeClass('selected')
            $(this).addClass('selected')
            showImage() // Update image
         });


         // Append available times to navigation
         var target = $("#wt-sounding-timeline")
         $(target).empty().append("<ul></ul>")
         $(target).find("ul").append("<li time='-'>-</li>");
         $.each(data.times.time, function(key, val) {
            // Default: take first one if input unset
            $(target).find("ul").append("<li time='"+val+"'>"+val+"</li>");
            // If match:
            if ( selected == val.imgname ) { $(target).find("ul li").last().addClass("selected") } 
         });
         $(target).find("ul").append("<li time='+'>+</li>");
         // Default: first time step (second li element) selected.
         $(target).find("ul").find("li:nth-child(2)").addClass("selected")

         // Appending interactive functionality
         $("#wt-sounding-timeline ul").on("click","li",function() {
             change_time($(this).attr("time"))
         });         

         showImage()

      }

      // Shows the image
      function showImage( ) {

         // Load key of current selected model
         var station = $("#wt-sounding-navigation .stations   ul li.selected").attr("station")
         var type    = $("#wt-sounding-navigation .types      ul li.selected").attr("type")
         var time    = $("#wt-sounding-timeline               ul li.selected").attr("time")
         var image = "https://userpage.fu-berlin.de/mammatus95/icon/00/karten/"
                   + type + "_" + station + "_" + time + ".png"
         $("#wt-sounding-image").attr("src",image).error( function() {
            $(this).attr("src","/referrerdata/soundings_missing_image.png");
         });

      }

   }

});
