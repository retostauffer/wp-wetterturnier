

// Initialize demo table
jQuery(document).on('ready',function() {

   // Copy jQuery to $
   $ = jQuery

   $.fn.wtsynopsymbols = function(stations,args) {

      // Looping "tmax" hours back in time now
      if ( "show" in args ) { var show = args.show; } else { var show = 6; }

      // Save stations to object
      $(this).data("stations",stations)

      // Show buttons where the user can choose to see more than
      // the default amount of images.
      var target = $(this)
      $.each( [6,12,24,48], function(key,value)  {
         $(target).append("<input type=\"button\" name=\"wt-synopsymbol-show\" "
                       +"value=\"Show "+value+"\"></input>");
      });

      // Add functionality
      $(this).on("click","input[name='wt-synopsymbol-show']",function() {
         var value = parseInt($(this).val().toUpperCase().replace("SHOW ",""))
         $(this).closest("div").find("div.wt-synsymbol-date").remove()
         showSynopSymbols( $(this).closest("div"), value )
      });

      // Show synop symbols now:
      showSynopSymbols( $(this), show )

   }


   // Converts the dates into an object with several different
   // properties/attributes/formats.
   function date2obj( x ) {
      var res = new Object() 
      res.YYYY = x.getUTCFullYear()
      res.mm   = 1+x.getUTCMonth(); if ( res.mm < 10 ) { res.mm = "0"+res.mm; } 
      res.dd   = x.getUTCDate();  if ( res.dd < 10 ) { res.dd = "0"+res.dd; } 
      res.HH   = x.getUTCHours(); if ( res.HH < 10 ) { res.HH = "0"+res.HH; }
      // Create object containing all necessary infos
      res.YYYYmmdd = res.YYYY+""+res.mm+""+res.dd
      res.proper = res.YYYY+"-"+res.mm+"-"+res.dd+"T"+res.HH+":00:00Z"
      res.show   = res.YYYY+"-"+res.mm+"-"+res.dd+"<br>"+res.HH+":00 UTC"
      return( res );
   }


   // Main function showing the images
   function showSynopSymbols( target, show ) {

      // The image shown when a png is missing on the server
      var missing = "/referrerdata/SynopSymbols/missing.png"
      // We need to know what time it is at the moment.
      var now = date2obj(new Date());
      // Stations
      var stations = $(target).data("stations")

      for ( t=0; t<show; t++ ) {
         var curDate = date2obj( new Date(new Date(now.proper) - t*3600000) )
         
         // Looping over all stations and append figure
         $(target).append("<div class='wt-synsymbol-date'></div>");
         var target = $(target).find("div").last();
         // Show date on top
         $(target).append("<h3>"+curDate.show+"</h3>");
         $.each(stations, function(key,val) {
            var image = "/referrerdata/SynopSymbols/synop_"+curDate.YYYYmmdd
                       +"_"+curDate.HH+"00_"+val+".png"
            $(target).append("<div class='wt-synopsymbol'>"+val+"<br><img></img></div>")
            $(target).find("div").last().find("img").attr("src",image).error(function() {
               $(this).unbind("error").attr("src", missing);
            })
         });
      }

   }

});
