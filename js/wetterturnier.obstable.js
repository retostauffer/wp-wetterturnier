

// Initialize demo table
jQuery(document).on('ready',function() {

   // Copy jQuery to $
   $ = jQuery


   $.fn.show_obstable = function(input) {

      // Element where we have to store the data to
      var elem = $(this)

      if ( typeof(input) == 'undefined' ) {
         $(elem).html('Problems in function fn.show_obstable. Input missing!'); return(false); 
      } else if ( typeof(input.statnr) == 'undefined' ) {
         $(elem).html('Problems in function fn.show_obstable. Input statnr missing!'); return(false); 
      } else if ( typeof(input.ajaxurl) == 'undefined' ) {
         $(elem).html('Problems in function fn.show_obstable. Input ajaxurl missing!'); return(false); 
      }
      if ( typeof(input.days) == 'undefined' ) {
         input.days = 2
      }

      // Check if file exists. If it exists, just display.
      // If not existing, start Rscript to create the image.
      // Ajaxing the calculation miniscript
      $.ajax({
         url: ajaxurl, dataType: 'json', type: 'post', async: false,
         data: {action:'getobservations_ajax',statnr:input.statnr,days:input.days},
         success: function(results) {
            data = results 
            if ( data[0] == undefined ) { data = false; }
         },
         error: function(e) {
            //$error = e; console.log('errorlog'); console.log(e);
            $(elem).html('Problems loading observation data.<br><br>\n' + e.responseText)
            data == false
         }

      });

      // Found data, print them now.
      if ( ! data == false ) {

         // Clear element, adding table
         $(elem).empty().append("<h1>"+input.title+"</h1>")
         $(elem).append("<table class='wetterturnier-obstable "+input.style+"'><thead></thead><tbody></tbody></table>")
         var id = "#" + $(elem).attr('id')

         // Header from first entry in the data object
         $.each( data[0], function(k,v) {
            $(id+' table thead').append("<th>" + k + "</th>");
         });
         // Setting 'add tr class' true
         if ( 'datum' in data[0] && 'stdmin' in data[0] ) {
            addrowname = true
         } else {
            addrowname = false
         }
         // Appending data 
         rowname = ""
         $.each( data, function(k,rec) {
            // Create clas name
            if ( addrowname ) { 
               rowname = " row='tr-"+rec['datum'].toString()+rec['stdmin'].toString()+"'"
            }
            $(id+' table tbody').append("<tr"+rowname+"></tr>");
            $.each( rec, function(k,v) {
               if ( v == null ) { v = ''; } 
               $(id+' table tbody tr:last').append("<td>" + v + "</td>");
            });
         });


      } else {
         $(elem).empty().html("Sorry, no data available.")
      }
   }

});
