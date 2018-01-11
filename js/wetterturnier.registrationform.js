jQuery(document).on("ready",function(){
   (function($) {

      var col1 = "#FF6600"
      var col2 = "#CCCCCC"

      // Functionality on the buttons to show the user details
      if ( $("[name='registerform']").length ) {
         $("input#wp-submit").prop("disabled",true).css("background-color",col2);

         $("input[name='wt_accept']").live("click",function() {
            if ( $(this).is(":checked") ) {
               $("input#wp-submit").prop("disabled",false).css("background-color",col1);
               // Replace div with form
               var fdiv = $(this).closest("[name='registerform']");
               if ( fdiv.is("div") ) {
                  var repl = "<form name='registerform' id='registerform' action='"+
                       fdiv.attr("action")+"' method='post' novalidate='novalidate'>" +
                       fdiv.html() + "</form>";
                  $(fdiv).replaceWith(repl);
                  $("form[name='registerform']").children("input").prop("checked",true)
               }
            } else {
               $("input#wp-submit").prop("disabled",true ).css("background-color",col2);
            }
         });
      }
      


   })(jQuery);
});

