// This is the code to evaluate the user inputs
// When using a judgingform
jQuery(document).on('ready',function() {
   (function($) {
      $.fn.judgingform = function( ajaxurl ) {
         $(this).data("ajaxurl",ajaxurl)
         $(this).on("click","input[type='button']",function() {
            var form = $(this).closest("form")
            eval_judgingform( form ) 
         })
      }

      // Evaluating the user inputs, and calls ajax
      function eval_judgingform( form ) {

         var param = $(form).find("input[name='parameter']").val().trim().replace(" ","")
         // Loading values (string first)
         var obs1     = $(form).find("input[name='observed_1']").val().trim().replace(",",".")
         var obs2     = $(form).find("input[name='observed_2']").val().trim().replace(",",".")
         var forecast = $(form).find("input[name='forecast']").val().trim().replace(",",".")
         // If form has extra fields
         var need_extra = false
         if ( $(form).find("input[name='extra_1']").length > 0 ) {
            var extra1 = $(form).find("input[name='extra_1']").val().trim().replace(",",".")
            var extra2 = $(form).find("input[name='extra_2']").val().trim().replace(",",".")
            var need_extra = true
         } else { var extra1 = undefined; var extra2 = undefined; }

         // Checking values
         if ( obs1.length > 0 && isNaN( parseFloat(obs1) ) ) {
            alert("Obs1: Inserted value is no integer/float number.")
         } else if ( obs2.length > 0 & isNaN( parseFloat(obs2) ) ) {
            alert("Obs2: Inserted value is no integer/float number.")
         } else if ( forecast.length > 0 & isNaN( parseFloat(forecast) ) ) {
            alert("Forecast: Inserted value is no integer/float number.")

         // One missing?
         } else if ( ! obs1 | ! obs2 | ! forecast ) {
            alert("At least one required input missing [obs1/obs2/forecast].") 

         // Else we can compute the points a user would get
         } else {
            // Loading ajax url from container
            var ajaxurl = $(form).data("ajaxurl")

            // The data for the request. Depends on if extra values will be needed or not
            if ( ! need_extra ) {
               var args = {action:'judging_ajax',param:param,obs1:obs1,obs2:obs2,forecast:forecast}
            } else {
               var args = {action:'judging_ajax',param:param,obs1:obs1,obs2:obs2,forecast:forecast,
                           extra1:extra1,extra2:extra2}
            }

            // Caalling ajax now
            $.ajax({
                url: ajaxurl, dataType: 'json', type: 'post', async: false, data: args,
                success: function(results) {
                   $(form).find("input[name='points']").val( results.points ) 
                   if ( results.points == 'empty' ) { console.log( results.cmd ); }
                },
                error: function(e) {
                   $(form).find("input[name='points']").val( "Error ... ups" ) 
                } 
            });

            //console.log(results.cmd)
            //console.log(obs1+' '+obs2+' '+extra1+' '+extra2+' '+forecast)
         }
      }
   })(jQuery);
});

