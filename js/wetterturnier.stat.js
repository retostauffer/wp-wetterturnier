

// Initialize demo table
jQuery(document).on('ready',function() {

      // Copy jQuery to $
      $ = jQuery

      // ------------------------------------------------------------
      // A wetterturnier R-Script function. The function is calling
      // stat.player from the R-wetterturnier package on the server
      // to generate the player dependent time series analysis.
      // ------------------------------------------------------------
      $.wetterturnier_callRscript_playerstat = function(e,ajaxurl) {

         // Fetching userID and user name from the inputs
         // of the form so that we know what we have to display.
         var userID  = parseInt($(e).closest('form').find('input[name="userID"]').attr('value'));
         var user    = $(e).closest('form').find('input[name="user-search"]').attr('value');
         var Rscript = $(e).closest('form').find('input[name="Rscript"]').attr('value');
         var theimg  = $(e).closest('form').find('img').first();
         var themsg  = $(e).closest('form').find('.wetterturnier-info').first();
         var image   = '/Rimages/playerstat_'+userID+'.svg'
         var loader  = $(e).closest('form').find('.ajax-loader-bar');

         // Check if file exists. If it exists, just display.
         // If not existing, start Rscript to create the image.
         // Ajaxing the calculation miniscript
         $.ajax({
            url: ajaxurl, type: 'HEAD', async: false, url:image,
            success: function() {
               // Setting image
               theimg.attr('src',image).fadeIn();
            },
            error: function() {
               // ------------------------------------------------------
               // Seems that image is not existing. Create new one.
               // ------------------------------------------------------
               // Show ajax loading button - if there is one.
               if ( user.length > 0 ) {

                  // Show loader
                  loader.show();
                  theimg.hide();

                  // Ajaxing the calculation miniscript
                  $.ajax({
                     url: ajaxurl, dataType: 'json', type: 'post', async: false,
                     data: {action:'callRscript_ajax',Rscript:Rscript,userID:userID},
                     success: function(results) { 
                        // Setting either the image (if status = 0),
                        // else setting missing image.
                        $('#wtapplication').empty();
                        if ( results.stat == 0 ) {
                           theimg.attr('src',image);
                           theimg.fadeIn();
                        } else {
                           theimg.attr('src','/Rimages/missing.png');
                           themsg.text( results.message ).fadeIn();
                           $('#wtapplication').html( results.message+"\n<br>"+results.rcmd
                             +"\n<br>Status "+results.stat )
                        }
                     },
                     error: function(e) {
                        $error = e; console.log('errorlog'); console.log(e); 
                        console.log(e.responseText);
                     }
                  });

                  loader.hide();

               }
            }
         });

      }

});
