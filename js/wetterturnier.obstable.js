

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

      // get the obs data with AJAX call
      $.ajax({
         url: ajaxurl, dataType: 'json', type: 'post', async: false,
         data: {action:'getobservations_ajax',statnr:input.statnr,days:input.days},
         success: function(results) {
            data = results 
         },
         error: function(e) {
            //$error = e; console.log('errorlog'); console.log(e);
            $(elem).html('Problems loading observation data.<br><br>\n' + e.responseText)
            data = false
         }

      });

      // Found data, print them now.
      if ( ! data == false ) {

         // Clear element, adding table
         $(elem).empty().append("<h1>"+input.title+"</h1>")

         // Adding Navigation checkboxes
         $(elem).append("<div id='wttable-obs-nav'>"
            +"<div class=\"preset\"></div><ul></ul><div style=\"clear: both;\"></div></div>");

         $("#wttable-obs-nav div.preset").append("<h3>Presets:</h3>")
            .append("<ul></ul><div style=\"clear: both;\" />");
         $("#wttable-obs-nav div.preset ul")
            .append("<li do=\"show\" what=\"all\">show all</li>")
            .append("<li do=\"show\" what=\"stint,datum,stdmin\">hide all</li>")
            .append("<li do=\"show\" what=\"stint,datum,stdmin,w1,w2,ww,rr24,rrr1,rrr3,rrr6,rrr12\">ww/rain</li>")
            .append("<li do=\"show\" what=\"stint,datum,stdmin,sun,sunday,cc,ccl,ccm,chl,chm\">clouds/sun</li>")
            .append("<li do=\"show\" what=\"stint,datum,stdmin,dd,ff,ffinst,ffx,ffx1,ffx3,ffx6\">wind</li>")
            .append("<li do=\"show\" what=\"stint,datum,stdmin,rh,td,tmax12,tmin12,t\">temp/hum</li>")
            .append("<li do=\"show\" what=\"stint,datum,stdmin,pch,pmsl,psta,ptend\">pressure</li>");

         $.each( $.map(data.data,function(elem,index) { return index; }), function(i,param) {
            $("#wttable-obs-nav > ul").append("<li><input type=\"checkbox\" "
                  + " class='" + param + "' checked /> " + param + "</li>");
         });

         // Adding functionality to the presets
         $("#wttable-obs-nav div.preset ul").on("click","li",function(){
             var todo = $(this).attr("do");
             var what = $(this).attr("what");
             // Show all
             if ( todo == "show" & what == "all" ) {
                 $(".wttable-obs th, .wttable-obs td").show();
                 $("#wttable-obs-nav input").prop("checked",true);

             // Hide all, show only these
             } else if ( todo == "show" ) {
                 $(".wttable-obs th, .wttable-obs td").hide();
                 $("#wttable-obs-nav input").prop("checked",false);
                 $.each( what.split(","), function(idx,param) {
                    $(".wttable-obs th[class='"+param+"']").show()
                    $(".wttable-obs td[class='"+param+"']").show()
                    $("#wttable-obs-nav input[class='"+param+"']").prop("checked",true);
                 });
             // Hide some
             } else if ( todo == "hide" ) {
                 $.each( what.split(","), function(idx,param) {
                    $(".wttable-obs th[class='"+param+"']").show()
                    $(".wttable-obs td[class='"+param+"']").show()
                    $("#wttable-obs-nav input[class='"+param+"']").prop("checked",false);
                 });
             }
         });

         // Adding the title
         $(elem).append("<table class='wttable-obs tablesorter "+input.style+"'><thead></thead><tbody></tbody></table>")
         var id = "#" + $(elem).attr('id')

         // Header from first entry in the data object
         //$.each( data.data, function(k,v) {
         $.each( $.map(data.data,function(elem,index) { return index; }), function(i,param) {
            $(id+' table thead').append("<th class='" + param + "'>" + param + "</th>");
         });

         if ( data.data == null ) {
            $(elem).append("<div style=\"color: red; padding-bottom: 50px;\">Sorry, no data available!</div>");
            return;
         }
         // Setting 'add tr class' true
         if ( 'datum' in data.data && 'stdmin' in data.data ) {
            addrowname = true
         } else {
            addrowname = false
         }
         // Appending data 
         rowname = ""
         for ( var i=0; i<data.data.datum.length; i++ ) {
            if ( addrowname ) { 
               rowname = " class='tr-"+data.data['datum'][i].toString()+data.data['stdmin'][i].toString()+"'";
            }
            $(id+' table tbody').append("<tr"+rowname+"></tr>");
            $.each( $.map(data.data,function(elem,index) { return index; }), function(pi,param) {
               $(id+' table tbody tr:last')
                    .append("<td class='" + param + "' >" + data.data[param][i] + "</td>");
            });
         }


         // Adding functionality to the checkboxes
         $("#wttable-obs-nav ul").on("click","input[type='checkbox']",function() {
             var p = $(this).attr("class");
             if ( $(this).attr("checked") == "checked" ) {
                 $(".wttable-obs td[class='"+p+"']").show();
                 $(".wttable-obs th[class='"+p+"']").show();
             } else {
                 $(".wttable-obs td[class='"+p+"']").hide();
                 $(".wttable-obs th[class='"+p+"']").hide();
             }
         });
      } else {
         $(elem).empty().html("Sorry, no data available.")
      }

   }

});
