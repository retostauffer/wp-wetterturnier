

// Initialize demo table
jQuery(document).on('ready',function() {
   (function($) {

   // Function initializing the meteogram viewer
   $.fn.wtmeteogramdata = function( ) {
      var container = $(this)

      // Loading xml file with meteogram specifications first.
      // Will return a jQuery object (needs xml2json.js, included
      // by default by the wp-wetterturnier plugin).
      //console.log( $.meteogramdata.xmlfile );
      $.ajax({
         type: "GET",
         url: $.meteogramdata.xmlfile,
         dataType: "text",
         success: function (xml) {
            $.meteogramdata.data = $.xml2json( xml )

            // Loading lastrun (need hour to display latest meteograms by default)
            $.each( $.meteogramdata.data, function( key, values ) {
               var lastrun = ""
               $.ajax({type:"GET",url:$.meteogramdata.datadir+"/"+values.lastrunfile,
                  dataType:"text", async: false, cache: false,
                  success: function(lastrun) {
                     $.meteogramdata.data[key].lastrun = lastrun.substring(11,13)
                  }, error: function() {
                     $.meteogramdata.data[key].lastrun = $.meteogram.lastrundefault 
                  }
               });
            });

            // Setting necessary elements into the target element 
            $(container).empty()
            // Adding the necessary elements
            $("<div id=\"wt-meteogramdata-navigation\"></div>").appendTo(container)
            $("<div style=\"clear:both;\"></div>").appendTo(container)
            $("#wt-meteogramdata-navigation").append("<div class=\"models\"></div>");
            $("#wt-meteogramdata-navigation div.models").append("<h1>Model</h1>").append("<ul class=\"models\"></ul>");

            $("#wt-meteogramdata-navigation").append("<div class=\"hours\"></div>")
            $("#wt-meteogramdata-navigation div.hours").append("<h1>Initial time</h1>").append("<ul class=\"hours\"></ul>");

            $("#wt-meteogramdata-navigation").append("<div class=\"cities\"></div>")
            $("#wt-meteogramdata-navigation div.cities").append("<h1>Location</h1>").append("<ul class=\"cities\"></ul>");

            $("<div id=\"wt-meteogramdata-link\"></div>").appendTo(container)
            $("<div id=\"wt-meteogramdata\"></div>").appendTo(container)
            
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
         var target = $("#wt-meteogramdata-navigation").find("ul.models") 
         $(target).empty()
         // Subetting data
         var data = $.meteogramdata.data
         var model = undefined
         // Appending all models as li elements
         $.each(data,function(key,values) {
            $(target).append("<li model=\""+key+"\">"+values.name+"</li>")
            // Set first selected
            if ( model == undefined ) { model = key; }
            if ( model == key ) { $(target).find("li").last().addClass("selected"); }
         });

         // Appending functionality
         $("#wt-meteogramdata-navigation ul.models").on("click","li",function() {
            $("#wt-meteogramdata-navigation ul.models li").removeClass("selected")
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
         var target = $("#wt-meteogramdata-navigation").find("ul.hours") 
         $(target).empty()
         // Loading selected model
         var model = $("#wt-meteogramdata-navigation ul.models li.selected").attr("model")
         // Subetting data
         var data = $.meteogramdata.data[model]
         var hour = data.lastrun
         // Appending all models as li elements
         $.each(data.init,function(key,val) {
            $(target).append("<li hour=\""+key+"\">"+val+" UTC</li>")
            // Set selected 
            if ( hour == val ) { $(target).find("li").last().addClass("selected"); }
         });

         // Appending functionality
         $("#wt-meteogramdata-navigation ul.hours").on("click","li",function() {
            $("#wt-meteogramdata-navigation ul.hours li").removeClass("selected")
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
         var target = $("#wt-meteogramdata-navigation").find("ul.cities") 
         $(target).empty()
         // Loading selected model
         var model  = $("#wt-meteogramdata-navigation ul.models li.selected").attr("model")
         // Subetting data
         var data = $.meteogramdata.data[model]
         // Shw first (set to default)
         var city = undefined
         // Appending all models as li elements
         $.each(data.meteogramdata,function(key,values) {
            $(target).append("<li file=\""+values.file+"\" model=\""+key+"\">"+values.name+"</li>")
            // Set selected 
            if ( city == undefined ) { city = key; }
            if ( city == key ) { $(target).find("li").last().addClass("selected"); }
         });

         // Appending functionality
         $("#wt-meteogramdata-navigation ul.cities").on("click","li",function() {
            $("#wt-meteogramdata-navigation ul.cities li").removeClass("selected")
            $(this).addClass("selected")
            showData()
         });

         // Show image
         showData()
      }
      // ------------------------------------------------------------
      // Display image
      // ------------------------------------------------------------
      function showData() {
         var model  = $("#wt-meteogramdata-navigation ul.models li.selected").attr("model")
         var hourID = parseInt($("#wt-meteogramdata-navigation ul.hours li.selected").attr("hour"))
         var hour   = $.meteogramdata.data[model].init[hourID]
         var file   = $("#wt-meteogramdata-navigation ul.cities li.selected")
                            .attr("file").replace("HH",hour)
         // Create timestamp: half hour. Means that data will be cached by the
         // browser for half an hour longest.
         var ts = new Date(); ts = parseInt(ts.getTime()/1000/1800)
         var datfile = $.meteogramdata.datadir+"/"+file+"?"+ts

         var link = "Link to download the file: <a href=\"" + datfile + "\" target=\"_blank\">" +
                    datfile + "</a>.";
         $('#wt-meteogramdata-link').html(link);
         $.get(datfile, function(data) {
            $('#wt-meteogramdata').html(data.replace(/\ /g,"&nbsp;").replace(/\n/g,'<br>\n'));
         });
      } 

   }

   })(jQuery);
});
