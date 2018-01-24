

// Initialize demo table
jQuery(document).on('ready',function() {

   // Copy jQuery to $
   $ = jQuery

   $.fn.wtmapsforecasts = function( xmlfile, callback = null ) {

      var target = $(this)

      // Loading required xml file
      $.ajax({
         type: "GET",
         url: xmlfile, 
         dataType: "text",
         success: function (xml) {
            $.wtmapdata = $.xml2json( xml )
            // Show data
            showdata( target )
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

         var current = parseInt($("#wt-maps-timeline ul li.selected").attr("time")) 
         var model   = $("#wt-maps-navigation .models   ul li.selected").attr("model")
         var region  = $("#wt-maps-navigation .regions  ul li.selected").attr("region")
         var product = $("#wt-maps-navigation .products ul li.selected").attr("product")
         // Data subsetting
         data = $.wtmapdata[model][region][product].lt
         console.log( current )
         if ( (current+1) < data.length ) {
            $("#wt-maps-timeline ul li").removeClass('selected')
            $("#wt-maps-timeline ul li[time='"+(current+1)+"']").addClass('selected')
         // Else jumping back to the first one!
         } else {
            $("#wt-maps-timeline ul li").removeClass('selected')
            $("#wt-maps-timeline ul li[time='0']").addClass('selected')
         }
         showImage()

      }

      // Jumping one time step backwards. If active is the firstone,
      // jumping to the last timestep.
      function timestep_backwards() {

         var current = parseInt($("#wt-maps-timeline ul li.selected").attr("time")) 
         var model   = $("#wt-maps-navigation .models   ul li.selected").attr("model")
         var region  = $("#wt-maps-navigation .regions  ul li.selected").attr("region")
         var product = $("#wt-maps-navigation .products ul li.selected").attr("product")
         // Data subsetting
         data = $.wtmapdata[model][region][product].lt
         if ( current > 0 ) { 
            $("#wt-maps-timeline ul li").removeClass('selected')
            $("#wt-maps-timeline ul li[time='"+(current-1)+"']").addClass('selected')
         // Else jumping to the last entry
         } else {
            $("#wt-maps-timeline ul li").removeClass('selected')
            $("#wt-maps-timeline ul li[time='"+(data.length-1)+"']").addClass('selected')
         }
         showImage()

      }

      function showdata( target ) {

         // Clear target object
         $(this).empty()

         // Append necessary blocks for the forecast data navigation
         // consisting of: model, region, product, times, image
         $("<img id='wt-map-image'></img>").appendTo( target )
         $("<div id='wt-maps-timeline'>Times</div>").appendTo( target )
         $("<div id='wt-maps-navigation'></div>").appendTo( target )
         $("#wt-maps-navigation").append("<div class='models'>Models</div>")
         $("#wt-maps-navigation").append("<div class='regions'>Regions</div>")
         $("#wt-maps-navigation").append("<div class='products'>Products</div>")


         // Add functionality that each click onto the image itself
         // will act like a "go forward in time by one time step"
         $("#wt-maps-container").on("click","img",function() { timestep_forward(); });

         // Adding keyboard navigation functionality
         $("body").on("keydown",function(e){
            if      ( e.keyCode == 39 ) { timestep_forward(); }
            else if ( e.keyCode == 37 ) { timestep_backwards(); }
         });

         // Appending models
         showModels( )

      }

      // Shows model selection
      function showModels( selected_model ) {
         var target = $("#wt-maps-navigation").find(".models").first();
         $(target).empty().append("<h1>Model</h1><ul></ul>")
         $.each( $.wtmapdata, function( key, val ) {
            // Default: take first one if input unset
            if ( selected_model == undefined ) { selected_model = key; }
            $(target).find("ul").first() 
               .append("<li model='"+key+"'>"+$.wtmapdata[key].name+"</li>")
            // If match:
            if ( selected_model == key ) { $(target).find("ul li").last().addClass("selected") } 
         });

         // Appending interactive functionality
         $("#wt-maps-navigation .models ul").on("click","li",function() {
            $(this).parent("ul").find("li").removeClass('selected')
            $(this).addClass('selected')
            showRegions()
         });         

         // New model select set: loading regions
         showRegions( )
      }

      // Shows region selection
      function showRegions( selected_region ) {
         var target = $("#wt-maps-navigation").find(".regions").first();
         // Load key of current selected model
         var model = $("#wt-maps-navigation .models ul li.selected").attr("model")
         // Data subsetting
         subdata = $.wtmapdata[model]
         // Appending new ul element and show the regions defined for current model
         $(target).empty().append("<h1>Region</h1><ul></ul>")
         $.each( subdata, function( key, val ) {
            if ( key == "name" ) { return; }
            // Default: if no input selected_region set: take first one 
            if ( selected_region == undefined ) { selected_region = key; }
            $(target).find("ul").first()
               .append("<li region='"+key+"'>"+subdata[key].name+"</li>")
            // If match:
            if ( selected_region == key ) { $(target).find("ul li").last().addClass("selected") } 
         });

         // Appending interactive functionality
         $("#wt-maps-navigation .regions ul").on("click","li",function() {
            $(this).parent("ul").find("li").removeClass('selected')
            $(this).addClass('selected')
            showProducts()
         });         

         // New region select set: loading products
         showProducts( )
      }

      // Shows product selection
      function showProducts( selected_product ) {
         var target = $("#wt-maps-navigation").find(".products").first();
         // Load key of current selected model
         var model  = $("#wt-maps-navigation .models  ul li.selected").attr("model")
         var region = $("#wt-maps-navigation .regions ul li.selected").attr("region")
         // Data subsetting
         subdata = $.wtmapdata[model][region]
         // Appending new ul element and show products defined for model/region
         $(target).empty().append("<h1>Product</h1><ul></ul>")
         $.each( subdata, function( key, val ) {
            if ( key == "name" ) { return; }
            // Default: if no input selected_region set: take first one 
            if ( selected_product == undefined ) { selected_product = key; }
            $(target).find("ul").first()
               .append("<li product='"+key+"'>"+subdata[key].name+"</li>")
            // If match:
            if ( selected_product == key ) { $(target).find("ul li").last().addClass("selected") } 
         });

         // Appending interactive functionality
         $("#wt-maps-navigation .products ul").on("click","li",function() {
            $(this).parent("ul").find("li").removeClass('selected')
            $(this).addClass('selected')
            showTimes()
         });         

         // New region select set: loading products
         showTimes( )
      }

      // Shows product selection
      function showTimes( selected_time ) {
         var target = $("#wt-maps-timeline");
         // Load key of current selected model
         var model   = $("#wt-maps-navigation .models   ul li.selected").attr("model")
         var region  = $("#wt-maps-navigation .regions  ul li.selected").attr("region")
         var product = $("#wt-maps-navigation .products ul li.selected").attr("product")
         // Data subsetting
         data = $.wtmapdata[model][region][product].lt
         $(target).empty().append("<ul><li time=\"-\">-</li></ul>")
         // Appending new ul element and show products defined for model/region
         $.each( data, function( key, val ) {
            // Default: if no input selected_region set: take first one 
            if ( selected_time == undefined ) { selected_time = key; }
            $(target).find("ul").first()
               .append("<li time='"+key+"'>"+val+"</li>")
            // If match:
            if ( selected_time == key ) { $(target).find("ul li").last().addClass("selected") } 
         });
         $(target).append("<ul><li time=\"+\">+</li></ul>")

         // Appending interactive functionality
         $("#wt-maps-timeline ul").on("click","li",function() {
            var timeID = $(this).attr('time')
            if      ( timeID == "+" ) { timestep_forward(); }
            else if ( timeID == "-" ) { timestep_backwards(); }
            else {
               $(this).parent("ul").find("li").removeClass('selected')
               $(this).addClass('selected')
               showImage()
            }
         });         

         // Show image now
         showImage()
      }


      // Shows the image
      function showImage( ) {

         // Load key of current selected model
         var model   = $("#wt-maps-navigation .models   ul li.selected").attr("model")
         var region  = $("#wt-maps-navigation .regions  ul li.selected").attr("region")
         var product = $("#wt-maps-navigation .products ul li.selected").attr("product")
         var timeID  = $("#wt-maps-timeline             ul li.selected").attr("time")
         var time = $.wtmapdata[model][region][product].lt[ parseInt(timeID) ]

         // Generate image name
         if      ( time < 10 )  { time = "00"+time; }
         else if ( time < 100 ) { time = "0"+time;  }

         var image = "/referrerdata/ForecastProducts/"+model+"/"
                    +model+"_"+region+"_"+product+"_"+time+".gif"
         $( target ).find("#wt-map-image").attr("src",image).error( function() {
            $(this).attr("src","/referrerdata/maps_missing_image.png");
         });

      }

   }

});
