

// Initialize demo table
jQuery(document).on('ready',function() {

   // Copy jQuery to $
   $ = jQuery

   $.fn.wtsoundingsmorten = function( xmlfile, callback = null ) {

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
      function timestep_forward() {

         // Check which time list element is the selected element:
         var total   = $("#wt-sounding-timeline ul li").length
         var current = $("#wt-sounding-timeline ul li.selected").index() + 1
         // Change selection
         if ( (current+1) < total ) {
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
      function timestep_backwards() {

         // Check which time list element is the selected element:
         var total   = $("#wt-sounding-timeline ul li").length
         var current = $("#wt-sounding-timeline ul li.selected").index() + 1
         if ( current > 2 ) { 
            $("#wt-sounding-timeline ul li.selected").removeClass('selected')
            $("#wt-sounding-timeline ul li:nth-child("+(current-1)+")").addClass('selected')
         // Else jumping to the last entry
         } else {
            $("#wt-sounding-timeline ul li.selected").removeClass('selected')
            $("#wt-sounding-timeline ul li:nth-child("+(total-1)+")").addClass('selected')
         }
         showImage()

      }

      // Change time step function.
      function change_time(to) {
          if      ( to == "+" ) { timestep_forward(); }
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

         // Add functionality that each click onto the image itself
         // will act like a "go forward in time by one time step"
         $("#wt-sounding-container").on("click","img",function() { timestep_forward(); });

         // Adding keyboard navigation functionality
         $("body").on("keydown",function(e){
            if      ( e.keyCode == 39 ) { timestep_forward(); }
            else if ( e.keyCode == 37 ) { timestep_backwards(); }
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
            }
         });

         // Appending models
         showSounding(data, undefined)

      }

      // Shows model selection
      function showSounding(data, selected) {

         // Append available stations to navigation
         var target = $("#wt-sounding-navigation").find(".stations").first();
         $(target).empty().append("<h1>Station</h1><ul></ul>")
         $.each(data.stations.station, function(key, val) {
            // Default: take first one if input unset
            if ( selected == undefined ) { selected = val.imgname; }
            $(target).find("ul")
               .append("<li station='"+val.imgname+"'>" + val.name + "</li>")
            // If match:
            if ( selected == val.imgname ) { $(target).find("ul li").last().addClass("selected") } 
         });

         // Appending interactive functionality
         $("#wt-sounding-navigation .stations ul").on("click","li",function() {
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
         var time    = $("#wt-sounding-timeline               ul li.selected").attr("time")
         var image = "https://userpage.fu-berlin.de/mammatus95/cosmo/00/soundings/"
                   + "stuve_"+station+"_"+time+".png"
         $("#wt-sounding-image").attr("src",image).error( function() {
            $(this).attr("src","/referrerdata/soundings_missing_image.png");
         });

      }

   }

});
