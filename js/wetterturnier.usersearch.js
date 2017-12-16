
// ------------------------------------------------------------------
// You can append init_user_search to any <div> object.
// ------------------------------------------------------------------
jQuery.fn.usersearch = function(ajaxurl,inputs) {
   // Shortcut for jQuery
   $ = jQuery

   // Store "this" to an explicit variable name
   var base_elem = this

   // ---------------------------------------------------------------
   // Loading the id where we have to set-up the 
   // user-search box and stuff.
   // ---------------------------------------------------------------
   if ( this.length == 0 ) { alert('Problems loading element id for user_search plugin.'); return }
   // Append class
   $(this).addClass('wetterturnier-user-search');
   $(this).empty()
   $("<input class=\"user-search\" name=\"user-search\" automplete=\"off\"></input>").appendTo(this);
   $("<input type=\"hidden\" name=\"user-search-id\" value=\"\" />").appendTo(this)
   $("<input type=\"hidden\" name=\"user-search-name\" value=\"\" />").appendTo(this)
   $("<ul></ul>").appendTo(this);

   // Loading the data via ajax. Note that the  
   // handling of the ajaxurl is somehow special because
   // this is a wordpress plugin!
   wpwt_get_user_data = function() {
   
      console.log( ajaxurl )
      // Ajaxing the calculation miniscript
      var data = false;
      $.ajax({
         url: ajaxurl, dataType: 'json', type: 'post', async: false,
         data: {action:'usersearch_ajax'},
         success: function(results) { 
            data = results;
         },
         error: function(xhr, status, error) {
            //console.log( xhr.responseText )
            //console.log( error )
            //var err = eval("(" + xhr.responseText + ")");
            alert( "ajax error: not able to load data." );
         }
      });
      return( data )
   }
  
   // ---------------------------------------------------------------
   // Appending the data from the ajax call to the element itself
   // ---------------------------------------------------------------
   this.data( wpwt_get_user_data() );

   // ---------------------------------------------------------------
   // This is the keyup-feature. Whenever the user changes something
   // we have to re-load the data.
   // ---------------------------------------------------------------
   this.keyup(function() {
      // Define where the <ul> element is and clearcontent
      // if there is any.
      var thisul = $(this).find('ul')
      thisul.empty();
      // Loading data from parent object
      var data = $(this).data()
      // Search string
      var search = $(this).find('input[name="user-search"]').attr('value').trim().toLowerCase();
      if ( search.length > 0 ) {
         $.each( data, function(i) {
            if ( search.length > 0 ) {
               if ( data[i]['user_login'].toLowerCase().indexOf(search) < 0 ) { return(true); }
            }
            $("<li class=\"user-search-selectable\" userID=\""+data[i]['ID']+"\">"+data[i]['user_login']+"</li>").appendTo( thisul );
         });
         var num = thisul.find("li").length
         if      ( num == 1 ) {
            var username = thisul.find("li").first().html()
            var userID   = thisul.find("li").first().attr("userid")
console.log( " ----------- " + username )
console.log( " ----------- " + userID )
            $(this).find("input[name='user-search']").attr( "value",  username )
            $(this).find("input[name='user-search']").attr( "userid", userID )
            thisul.hide()
            // and clear the field.
            $(this).find('input[name="user-search"]').empty()

            // If inputs.addul is set: append the match to the ul 
            if ( inputs.ulmax !== undefined ) {
               var count = $(inputs.addul).find(".selected-user").length
               if ( count >= inputs.ulmax )
               { alert("Not allowed to add more than " + inputs.ulmax + " users at a time."); return }
               $(inputs.addul).append("<li class='selected-user' userid='"+userID+"'>"+username+"</li>")
               $(inputs.addul).trigger("change")
            }
         }
         else if ( num  > 1 ) { thisul.show() }
         else                 { thisul.hide(); }
      }
   });


   // ---------------------------------------------------------------
   // If a <li> gets clicked
   // ---------------------------------------------------------------
   $(this).find('ul li').live('click',function() {
      var userID   = $(this).attr('userID')
      var username = $(this).html()
      $(base_elem).find('ul').hide();
      // Add to ul if defined on inputs
      if ( inputs.addul !== undefined ) {
         if ( inputs.ulmax !== undefined ) {
            var count = $(inputs.addul).find(".selected-user").length
            if ( count >= inputs.ulmax )
            { alert("Not allowed to add more than " + inputs.ulmax + " users at a time."); return }
         }
         $(inputs.addul).append("<li class='selected-user' userid='"+userID+"'>"+username+"</li>")
         $(inputs.addul).trigger("change")
      }
      $(base_elem).find('input[name="user-search"]').attr('value',username);
      $(base_elem).find('input[name="user-search"]').attr('userid',userID );
      $(base_elem).closest('form').find('input[name="userID"]').attr('value',userID)
   
      // I "input" contains a function on "fun" call
      // the function! At the moment only functions are
      // allowed where the input is only the base element
      // and the ajaxurl (need them for some R-calls)
      if ( typeof( inputs ) != "undefined" ) {
         console.log( inputs )
         if ( inputs.fun == 'form.submit' ) {
            $(this).closest('form').submit()
         } else if ( typeof( inputs.fun ) == 'function' ) {
            base_elem.find('ul').hide()
            inputs.userID = userID
            inputs.fun(ajaxurl,inputs);
            //$.when( base_elem.find('ul').hide() ).then(
            // inputs.fun(base_elem,ajaxurl)
            //)
         }
      }
   });


}

