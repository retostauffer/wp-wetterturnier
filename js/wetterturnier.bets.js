jQuery(window).load(function(){
   (function($) {

   // Adds the functionality that rows can be highlighted
   $("table.wttable-show > tbody > tr > td").on("click",function() {
      var userID = $(this).closest("tr").attr("userid");
      var elem = $("table.wttable-show > tbody > tr[userid='"+userID+"']").first()
      if ( elem.hasClass('highlighted') ) {
         $("table.wttable-show > tbody > tr[userid='"+userID+"']").removeClass('highlighted')
      } else {
         $("table.wttable-show > tbody > tr[userid='"+userID+"']").addClass('highlighted')
      }
   });

   // Functionality on the buttons to show the user details
   $("table.wttable-show > tbody > tr > td > span.button.detail").click(function(){

      // --------------------
      // Ajaxing the calculation miniscript
      var userID = parseInt($(this).attr("userID"));
      var cityID = parseInt($(this).attr("cityID"));
      var tdate  = parseInt($(this).attr("tdate"));
      var foo = $("<div>Error loading the data!</div>"); 
      // Loading requested information
      $.ajax({
         cache: false,
         url: $.ajaxurl, dataType: 'json', type: 'post', async: false,
         data: {cache:false,action:'wttable_show_details_ajax',userID:userID,cityID:cityID,tdate:tdate},
         success: function(results) {
            foo = $("<div></div>").html(results[0]) 
         },
         error: function(e) {
            $error = e; console.log('errorlog'); console.log(e);
            console.log(e.responseText);
         }
      });
      // --------------------
      // Show featherlight lightbox now
      $.featherlight(foo,{afterClose:function() { $(this).remove() }});
      
      // Allow user to sort the tables
      $(".wttable-show").tablesorter({sortList: [], stringTo: "bottom",
          sortReset: true, sortRestart: true, sortInitialOrder: "desc"});
      $(".wttable-show th").css('cursor', 'pointer'); 

   });      

   // Functionality on the buttons to forward admins to 'edit user bets' page.
   $("table.wttable-show > tbody > tr > td > span.button.edit-bet").click(function(){

      var userID  = parseInt($(this).attr("userID"));
      var cityID  = parseInt($(this).attr("cityID"));
      var tdate   = parseInt($(this).attr("tdate"));
      var url     = $(this).attr("url");
      var url     = url + "admin.php?page=wp_wetterturnier_admin_bets&action=edit&cityID=" +
                    cityID + "&userID=" + userID + "&tdate=" + tdate
      window.location.replace(  url )
   });

   // Functionality on the buttons to forward admins to 'edit observations' page.
   $("table.wttable-show > tbody > tr > td > span.button.edit-obs").on("click",function(){

      var station = parseInt($(this).attr("station"));
      var cityID  = parseInt($(this).attr("cityID"));
      var tdate   = parseInt($(this).attr("tdate"));
      var url     = $(this).attr("url");
      var url     = url + "admin.php?page=wp_wetterturnier_admin_obs&action=edit&cityID=" +
                    cityID + "&station=" + station + "&tdate=" + tdate
      window.location.replace(  url )
   });

   // Functionality to show/hide certain types of players
   // on the bet tables.
   $(".colorlegend-wrapper input[type='submit'].settings-button").on("click",function() {
      if ( $(this).hasClass("inactive") ) {
         $(this).removeClass("inactive")
         $("table.wttable-show-bets tbody tr."+$(this).attr("name")).show()
         $("table.wttable-show      tbody tr."+$(this).attr("name")).show()
      } else {
         $(this).addClass("inactive")
         $("table.wttable-show-bets tbody tr."+$(this).attr("name")).hide()
         $("table.wttable-show      tbody tr."+$(this).attr("name")).hide()
      }
   });

   })(jQuery);

});

jQuery(document).ready(function(){
      // Functionality on the buttons to show the user details
      $("table.wttable-show > tbody > tr > td > span.button.detail").click(function(){
         window.console&&console.log('CLICK!');
         // --------------------
         // Ajaxing the calculation miniscript
         var userID = parseInt($(this).attr("userID"));
         var cityID = parseInt($(this).attr("cityID"));
         var tdate  = parseInt($(this).attr("tdate"));
         var foo = $("<div>Error loading the data!</div>");
         // Loading requested information
         $.ajax({
            cache: false,
            //cache: true,
            url: $.ajaxurl, dataType: 'json', type: 'post', async: false,
            data: {cache:false,action:'wttable_show_details_ajax',userID:userID,cityID:cityID,tdate:tdate},
            success: function(results) {
               foo = $("<div></div>").html(results[0])
            },
            error: function(e) {
               $error = e; console.log('errorlog'); console.log(e);
               console.log(e.responseText);
            }
         });
         // --------------------
         // Show featherlight lightbox now
         $.featherlight(foo,{afterClose:function() { $(this).remove() }});

         // Allow user to sort the tables
         $(".wttable-show").tablesorter({sortList: [], stringTo: "bottom",
             sortReset: true, sortRestart: true, sortInitialOrder: "desc"});
         $(".wttable-show th").css('cursor', 'pointer');

      });
});
