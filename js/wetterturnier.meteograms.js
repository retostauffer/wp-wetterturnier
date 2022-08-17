

// Initialize demo table
jQuery(document).on('ready',function() {
   (function($) {

   // Function initializing the meteogram viewer
   $.fn.wtmeteograms = function( ) {
      var container = $(this)

      // Loading xml file with meteogram specifications first.
      // Will return a jQuery object (needs xml2json.js, included
      // by default by the wp-wetterturnier plugin).
      console.log( $.meteogram.xmlfile );
      $.ajax({
         type: "GET",
         url: $.meteogram.xmlfile,
         dataType: "text",
         success: function (xml) {
            $.meteogram.data = $.xml2json( xml )

            // Loading lastrun (need hour to display latest meteograms by default)
            $.each( $.meteogram.data, function( key, values ) {
               var lastrun = ""
               $.ajax({type:"GET",url:$.meteogram.imagedir+"/"+values.lastrunfile,
                  dataType:"text", async: false, cache: false,
                  success: function(lastrun) {
                     $.meteogram.data[key].lastrun = lastrun.substring(11,13)
                  }, error: function() {
                     $.meteogram.data[key].lastrun = $.meteogram.lastrundefault 
                  }
               });
            });

            // Setting necessary elements into the target element 
            $(container).empty()
            // Adding the necessary elements
            $("<div id=\"wt-meteogram-navigation\"></div>").appendTo(container)
            $("<div style=\"clear:both;\"></div>").appendTo(container)
            $("#wt-meteogram-navigation").append("<div class=\"models\"></div>");
            $("#wt-meteogram-navigation div.models").append("<h1>Model</h1>").append("<ul class=\"models\"></ul>");

            $("#wt-meteogram-navigation").append("<div class=\"hours\"></div>")
            $("#wt-meteogram-navigation div.hours").append("<h1>Initial time</h1>").append("<ul class=\"hours\"></ul>");

            $("#wt-meteogram-navigation").append("<div class=\"cities\"></div>")
            $("#wt-meteogram-navigation div.cities").append("<h1>Location</h1>").append("<ul class=\"cities\"></ul>");

            $("<div id=\"wt-meteogram-image\"><img></img></div>").appendTo(container)
            
            // Show different models now
            showModels()
         },
         error: function() {
            alert("Problems reading the xml file.");
         }
      });


      // ------------------------------------------------------------
      // Shows defined meteogram models (types)
      // ------------------------------------------------------------
      function showModels() {
         var target = $("#wt-meteogram-navigation").find("ul.models") 
         $(target).empty()
         // Subetting data
         var data = $.meteogram.data
         var model = undefined
         // Appending all models as li elements
         $.each(data,function(key,values) {
            $(target).append("<li model=\""+key+"\">"+values.name+"</li>")
            // Set first selected
            if ( model == undefined ) { model = key; }
            if ( model == key ) { $(target).find("li").last().addClass("selected"); }
         });

         // Appending functionality
         $("#wt-meteogram-navigation ul.models").on("click","li",function() {
            $("#wt-meteogram-navigation ul.models li").removeClass("selected")
            $(this).addClass("selected")
            showHours()
         });

         // After models are set: show hours
         showHours()
      }
      // ------------------------------------------------------------
      // Shows defined meteogram models (types)
      // ------------------------------------------------------------
      function showHours() {
         var target = $("#wt-meteogram-navigation").find("ul.hours") 
         $(target).empty()
         // Loading selected model
         var model = $("#wt-meteogram-navigation ul.models li.selected").attr("model")
         // Subetting data
         var data = $.meteogram.data[model]
         var hour = data.lastrun
         // Appending all models as li elements
         $.each(data.init,function(key,val) {
            $(target).append("<li hour=\""+key+"\">"+val+" UTC</li>")
            // Set selected 
            if ( hour == val ) { $(target).find("li").last().addClass("selected"); }
         });

         // Appending functionality
         $("#wt-meteogram-navigation ul.hours").on("click","li",function() {
            $("#wt-meteogram-navigation ul.hours li").removeClass("selected")
            $(this).addClass("selected")
            showCities()
         });

         // After hours are set: show cities 
         showCities()
      }

      // ------------------------------------------------------------
      // Shows defined cities
      // ------------------------------------------------------------
      function showCities() {
         var target = $("#wt-meteogram-navigation").find("ul.cities") 
         $(target).empty()
         // Loading selected model
         var model  = $("#wt-meteogram-navigation ul.models li.selected").attr("model")
         // Subetting data
         var data = $.meteogram.data[model]
         // Shw first (set to default)
         var city = undefined
         // Appending all models as li elements
         $.each(data.meteogram,function(key,values) {
            $(target).append("<li file=\""+values.file+"\" model=\""+key+"\">"+values.name+"</li>")
            // Set selected 
            if ( city == undefined ) { city = key; }
            if ( city == key ) { $(target).find("li").last().addClass("selected"); }
         });

         // Appending functionality
         $("#wt-meteogram-navigation ul.cities").on("click","li",function() {
            $("#wt-meteogram-navigation ul.cities li").removeClass("selected")
            $(this).addClass("selected")
            showImage()
         });

         // Show image
         showImage()
      }
      // ------------------------------------------------------------
      // Display image
      // ------------------------------------------------------------
      function showImage() {
         var model  = $("#wt-meteogram-navigation ul.models li.selected").attr("model")
         var hourID = parseInt($("#wt-meteogram-navigation ul.hours li.selected").attr("hour"))
         var hour   = $.meteogram.data[model].init[hourID]
         var file   = $("#wt-meteogram-navigation ul.cities li.selected")
                            .attr("file").replace("HH",hour)
         // Create timestamp: half hour. Means that images will be cached by the
         // browser for half an hour longest.
         var ts = new Date(); ts = parseInt(ts.getTime()/1000/1800)
         var image = $.meteogram.imagedir+"/"+file+"?"+ts
         $("#wt-meteogram-image img").attr("src",image)
      } 

   }

   })(jQuery);
});
