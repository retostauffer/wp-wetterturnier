

// Initialize demo table
jQuery(document).on('ready',function() {

   // Copy jQuery to $
   $ = jQuery

   $.fn.wtcosmomorten = function( xmlfile, callback = null ) {

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
         var total   = $("#wt-cosmo-timeline ul li").length
         var current = $("#wt-cosmo-timeline ul li.selected").index() + 1
         // Change selection
         if ( (current+1) < total ) {
            $("#wt-cosmo-timeline ul li.selected").removeClass('selected')
            $("#wt-cosmo-timeline ul li:nth-child("+(current+1)+")").addClass("selected")
         // Else jumping back to the first one!
         } else {
            $("#wt-cosmo-timeline ul li.selected").removeClass('selected')
            $("#wt-cosmo-timeline ul li:nth-child(2)").addClass('selected')
         }
         showImage()

      }

      // Jumping one time step backwards. If active is the firstone,
      // jumping to the last timestep.
      function timestep_backwards() {

         // Check which time list element is the selected element:
         var total   = $("#wt-cosmo-timeline ul li").length
         var current = $("#wt-cosmo-timeline ul li.selected").index() + 1
         if ( current > 2 ) { 
            $("#wt-cosmo-timeline ul li.selected").removeClass('selected')
            $("#wt-cosmo-timeline ul li:nth-child("+(current-1)+")").addClass('selected')
         // Else jumping to the last entry
         } else {
            $("#wt-cosmo-timeline ul li.selected").removeClass('selected')
            $("#wt-cosmo-timeline ul li:nth-child("+(total-1)+")").addClass('selected')
         }
         showImage()

      }

      // Change time step function.
      function change_time(to) {
          if      ( to == "+" ) { timestep_forward(); }
          else if ( to == "-" ) { timestep_backwards(); }
          else {
              $("#wt-cosmo-timeline ul li.selected").removeClass("selected")
              $("#wt-cosmo-timeline ul li[time='"+to+"']").addClass("selected")
              showImage()
          }
      }

      function showdata(target, data) {

         // Clear target object
         $(this).empty()

         // Append necessary blocks for the forecast data navigation
         // consisting of: model, region, product, times, image
         $("<img id='wt-cosmo-image'></img>").appendTo( target )
         $("<div id='wt-cosmo-timeline'></div>").appendTo( target )
         $("<div id='wt-cosmo-navigation'></div>").appendTo( target )
         $("#wt-cosmo-navigation").append("<div class='stations'>Stations</div>")
         $("#wt-cosmo-navigation").append("<div class='clear'></div>")

         // Add functionality that each click onto the image itself
         // will act like a "go forward in time by one time step"
         $("#wt-cosmo-container").on("click","img",function() { timestep_forward(); });

         // Adding keyboard navigation functionality
         $("body").on("keydown",function(e){
            if      ( e.keyCode == 39 ) { timestep_forward(); }
            else if ( e.keyCode == 37 ) { timestep_backwards(); }
            else if ( e.keyCode == 38 ) {
                e.preventDefault()
                var current = $("#wt-cosmo-navigation .stations ul li.selected").index()+1
                // Remove current selection
                $("#wt-cosmo-navigation .stations ul li.selected").removeClass("selected")
                // Add new selection
                if ( current == 1 ) {
                   var las = $("#wt-cosmo-navigation .stations ul li").length
                   $("#wt-cosmo-navigation .stations ul li").last().addClass("selected")
                } else {
                   $("#wt-cosmo-navigation .stations ul li:nth-child("+(current-1)+")").addClass("selected")
                }
                showImage(); // Update image
            } else if ( e.keyCode == 40 ) {
                e.preventDefault()
                var current = $("#wt-cosmo-navigation .stations ul li.selected").index()+1
                // Remove current selection
                $("#wt-cosmo-navigation .stations ul li.selected").removeClass("selected")
                // Add new selection
                if ( current >= $("#wt-cosmo-navigation .stations ul li").length ) {
                   $("#wt-cosmo-navigation .stations ul li").first().addClass("selected")
                } else {
                   $("#wt-cosmo-navigation .stations ul li:nth-child("+(current+1)+")").addClass("selected")
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
         var target = $("#wt-cosmo-navigation").find(".stations").first();
         $(target).empty().append("<h1>Charts</h1><ul></ul>")
         $.each(data.stations.station, function(key, val) {
            // Default: take first one if input unset
            if ( selected == undefined ) { selected = val.imgname; }
            $(target).find("ul")
               .append("<li station='"+val.imgname+"'>" + val.name + "</li>")
            // If match:
            if ( selected == val.imgname ) { $(target).find("ul li").last().addClass("selected") } 
         });
         // Appending interactive functionality
         $("#wt-cosmo-navigation .stations ul").on("click","li",function() {
            $(this).parent("ul").find("li").removeClass('selected')
            $(this).addClass('selected')
            showImage() // Update image
         });         


         // Append available times to navigation
         var target = $("#wt-cosmo-timeline")
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
         $("#wt-cosmo-timeline ul").on("click","li",function() {
             change_time($(this).attr("time"))
         });         

         showImage()

      }

      // Shows the image
      function showImage( ) {

         // Load key of current selected model
         var station = $("#wt-cosmo-navigation .stations   ul li.selected").attr("station")
         var time    = $("#wt-cosmo-timeline               ul li.selected").attr("time")
         var image = "https://userpage.fu-berlin.de/mammatus95/cosmo/00/charts/"
                   + "wxdbz_D2_"+time+".png"
         $("#wt-cosmo-image").attr("src",image).error( function() {
            $(this).attr("src","/referrerdata/soundings_missing_image.png");
         });

      }

   }

});
