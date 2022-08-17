jQuery(document).on("ready",function(){
   (function($) {

      var col1 = "#FF6600"
      var col2 = "#CCCCCC"

      // Functionality on the buttons to show the user details
      if ( $("[name='registerform']").length ) {
         $("input#wp-submit").prop("disabled",true).css("background-color",col2);

         $("input[name='wt_accept']").click(function() {
            if ( $(this).is(":checked") ) {
               $("input#wp-submit").prop("disabled",false).css("background-color",col1);
            } else {
               $("input#wp-submit").prop("disabled",true ).css("background-color",col2);
            }
         });
      }
      


   })(jQuery);
});

