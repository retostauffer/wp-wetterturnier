// Simply write jQuery to $
$ = jQuery;
 
// ------------------------------------------------------------------
// Load and display the ranking table.
// ------------------------------------------------------------------
$.fn.show_ranking = function(ajaxurl, input) {

  // Element where we have to store the data to
  var elem = $(this)

  // Setting defaults
  var defaults = {hidebuttons: false}
  $.each(defaults, function(key, val) {
      if ( ! input.hasOwnProperty(key) ) { input[key] = val; }
  });

  // Check if file exists. If it exists, just display.
  // If not existing, start Rscript to create the image.
  // Ajaxing the calculation miniscript
  input["action"] = "ranking_ajax";
  test = $.ajax({
     url: ajaxurl, dataType: 'json', type: 'post', data: input,
     success: function(results, hxr, settings) {

         if ( results.error != undefined ) {
            $(elem).html("<div class=\"wetterturnier-info error\">" +
                         results.error + "</div>");
         } else {
            $(elem).html( "<div class=\"wetterturnier-info\">Data loaded, waiting for js to display it.</div>" );
            data = results;
         }
         display_ranking($(elem), results, input);
     },
     error: function(hxr, ajaxOptions, thrownError) {
        //$error = e; console.log('errorlog'); console.log(e);
        $(this).html("Problems loading ranking data.<br><br>\n" + hxr.responseText + "\n" + thrownError );
        data = false;
     }

  });
  console.log(test);


  // Show status bar (return status bar html)
  function statusbar(rel, width) {

      var relwidth = parseInt(width * rel);
      var percent = parseInt(rel*1000)/10.;
      if ( rel > 0.5 ) {
         var html = "<span class=\"ranking-statusbar\" style=\"width: 100%;\">"
                  + "  <span style=\"width: " + rel * 100 + "%;\">" + percent + "%&nbsp;</span>"
                  + "</span>";
      } else {
         var html = "<span class=\"ranking-statusbar\" style=\"width: 100%;\">"
                  + "  <span style=\"width: " + rel * 100 + "%;\"></span>&nbsp;" + percent + "%"
                  + "</span>";
      }
      return(html);
  }

  // This one was my original, currently unused.
  function colorize_trend_11cols( trend ) {
      // Creates a color id btw, 0 and 10 to address color.
      // Trend divided by 3, so every three the color changes.
      colidx = Math.min( Math.max( -5, Math.round(trend/3) ), 5 ) + 5
      var cols = ["#6695EB","#81A1E7","#98AEE3","#ADBBDF","#C1C7DA",
                  "#D4D4D4","#D7C4B9","#D7B49C","#D6A47E","#D3955B","#CF862B"];
      var sign = ( trend > 0 ) ? "+" : "";
      return "<span style=\"color: " + cols[10-colidx] + ";\">" + sign + trend + "</span>";
  }

  // Same as above, but with less colors.
  function colorize_trend( trend ) {
      // Creates a color id btw, 0 and 4 to address color.
      // Trend divided by 3, so every three the color changes.
      colidx = Math.min( Math.max( -1, Math.round(trend/10) ), 1 ) + 1
      if ( trend == 0 ) { var col = "#8D8D8D" } else if ( trend < 0 ) { var col = "#ff6616"; } else { col = "#668fcc"; }
      var sign = ( trend > 0 ) ? "+" : "";
      return "<span style=\"color: " + col + ";\">" + sign + trend + "</span>";
  }

  function display_ranking( e, data, input ) {

      // Clear content of the div
      $(e).hide().empty();

      // Short information about the maximum number of points possible
      if ( input.header ) {
          if ( input.type == "eternal" ) {
              $(e).append("<div class=\"wttable-show-points-max\">" +
              data.dict.total_tournaments + " <b>" + data.meta.total_tournaments
              + "</b>." + "</div>");
          } else {
              $(e).append("<div class=\"wttable-show-points-max\">" +
              data.dict.max_points + " <b>" + data.meta.max_points + "</b>." +
              "</div>");
          }
      }

      // Append new table
      $(e).append("<table class=\"wttable-show-ranking wttable-show small ranking-weekend default tablesorter\"></table>")
      $(e).find("table").append("<thead><tr></tr></thead><tbody></tbody>")

      var head = $(e).find("table thead tr")
      var body = $(e).find("table tbody")
      $( head ).append("<th class=\"rank\">"+data.dict.rank+"</th>");
      if ( data.meta.has_trends ) {
          $( head ).append("<th class=\"trend\">"+data.dict.trend+"</th>");
      }
      // Only show number of played games if begin/end date differ or in season/yearly rankings
      if ( data.meta.ntournaments > 1 || data.meta.total_tournaments > 1 || input.type=="eternal" ) {
          $( head ).append("<th class=\"played\">"+data.dict.played+"</th>");
      }
      $( head ).append("<th class=\"user\">"+data.dict.user+"</th>")
               .append("<th class=\"points-diff\">"+data.dict.difference+"</th>")
               .append("<th class=\"points\">"+data.dict.points+"</th>")
               if ( input.type === "eternal" ) {     
                    $(head).append("<th class=\"points\">"+data.dict.sd_ind+      "</th>")
                           .append("<th class=\"points\">"+data.dict.points_max+  "</th>")
                           .append("<th class=\"points\">"+data.dict.points_mean+ "</th>")
                           //.append("<th class=\"points\">"+data.dict.points_med+  "</th>")
                           .append("<th class=\"points\">"+data.dict.won_weekends+"</th>")
                           .append("<th class=\"points\">"+data.dict.top3+        "</th>")
                           //.append("<th class=\"points\">"+data.dict.won_seasons +"</th>")
                           //.append("<th class=\"points\">"+data.dict.played_seasons +"</th>")

               } else {
                   $(head).append("<th class=\"points-d1d2\">"+data.dict.points_d1+"</th>")
                           .append("<th class=\"points-d1d2\">"+data.dict.points_d2+"</th>")

               }
               $(head).append("<th class=\"statusbar\">"+data.dict.statusbar+"</th>");

      counter = 0;
      if ( typeof(input.limit) == undefined | typeof(input.limit) === "boolean" ) {
          input.limit = data.data.length;
      }
      $.each( data.data, function(idx, rec) {

         // If input.type === "seasoncities": colorize the guys who have
         // not played all games.
         if ( input.type === "seasoncities" && rec.played_now < data.meta.ntournaments )
         { tdclass = " partial-participation"; } else { tdclass = ""; }

         // Append new table row and select the new html element (variable tr)
         $( body ).append("<tr class=\"" + rec.userclass + tdclass + "\"></tr>")
         var tr = $( body ).find("tr").last()

         // Appending data ...
         $(tr).append("<td class=\"rank "+rec.userclass+"\">"+rec.rank_now+"</td>");
         if ( data.meta.has_trends ) {
             $( tr ).append("<td class=\"trend\">"+colorize_trend(rec.trend)+"</td>");
         }
         // Only show number of played games if begin/end date differ or for season/yearly rankings
         if (data.meta.ntournaments > 1 || data.meta.total_tournaments > 1 ) {
             if ( input.type == "eternal" ) {
                 $(tr).append("<td class=\"played\">"+rec.played_now+"</td>");
             } else {
             $(tr).append("<td class=\"played\">"+rec.played_now+"/"+data.meta.ntournaments+"</td>");
             }
         }

         $(tr).append("<td class=\"user\">" +
                      (( rec.detail_button != undefined && data.meta.ntournaments === 1 ) ? rec.detail_button : "") +
                      (( rec.edit_button != undefined ) ? rec.edit_button : "") +
                      rec.profile_link + "</td>")
                   .append("<td class=\"points-diff\">"+rec.points_diff+"</td>")
                   .append("<td class=\"points\">"+rec.points_now+"</td>")

                   if ( input.type === "eternal" ) {
                       $(tr).append("<td class=\"points\">"+rec.sd_ind+     "</td>")
                            .append("<td class=\"points\">"+rec.points_max+ "</td>")
                            .append("<td class=\"points\">"+rec.points_mean+"</td>")
                            //.append("<td class=\"points\">"+rec.points_med+"</td>")
                            .append("<td class=\"points\">"+rec.won_weekends+"</td>")
                            .append("<td class=\"points\">"+rec.top3+        "</th>")
                            //.append("<td class=\"points\">"+rec.won_seasons+ "</td>")
                            //.append("<td class=\"points\">"+rec.played_seasons+"</td>")
                   } else {
                       $(tr).append("<td class=\"points-d1d2\">"+rec.points_d1+  "</td>")
                            .append("<td class=\"points-d1d2\">"+rec.points_d2+  "</td>")
                   }
                   $(tr).append("<td class=\"statusbar\">"+statusbar(rec.points_relative, 200)+"</td>");

          // Increase loop counter
          counter++;
          if ( counter >= input.limit ) { return false; }

      });

      $(e).fadeIn("fast");
      // Allow user to sort the tables
      $(".wttable-show").tablesorter({sortList: [[0,0]],
          stringTo: "bottom", sortInitialOrder:"desc",
          sortRestart : true, sortReset : true});
      $(".wttable-show th").css('cursor', 'pointer');

  };

};


// ------------------------------------------------------------------
// Load and display the leader board.
// ------------------------------------------------------------------
$.fn.show_leaderboard = function(ajaxurl, input) {

    // Element where we have to store the data to
    var elem = $(this)

    // Setting defaults
    var defaults = {N: 3};
    $.each(defaults, function(key, val) {
        if ( ! input.hasOwnProperty(key) ) { input[key] = val; }
    });
  
    // Triggering ajax request "ranking_ajax" (registered wordpress action)
    // to load the data. On success the leaderboard will be displayed.
    input["action"] = "ranking_ajax"
    test = $.ajax({
       url: ajaxurl, dataType: 'json', type: 'post', data: input,
       success: function( results, hxr, settings ) {
  
           if ( results.error != undefined ) {
              $(elem).html("<div class=\"wetterturnier-info error\">" +
                           results.error + "</div>");
           } else {
              $(elem).html( "<div class=\"wetterturnier-info\">Data loaded, waiting for js to display it.</div>" );
              data = results;
           }
           display_leaders($(elem), results, input);
       },
       error: function( hxr, ajaxOptions, thrownError ) {
          //$error = e; console.log('errorlog'); console.log(e);
          $(this).html("Problems loading ranking data.<br><br>\n" +
              hxr.responseText + "\n" + thrownError );
          data = false
       }
  
    });
    //console.log( test );
    // Creates the frontend output
    function display_leaders( e, data, input ) {
  
        path="/wp-content/plugins/wp-wetterturnier/images/"

        // Clear content of the div
        $(e).hide().empty();
    
        counter = 1
        $.each( data.data, function(user, rec) {
        console.log(rec)
        //console.log(rec.rank_now)
   
            if (rec.rank_now <= 3) {
                i = rec.rank_now-1
                th = data.dict.th[i]
            } else {
                i = 3
                th = data.dict.th[i]
            }
            
            $(e).append("<div class=\"wt-leaderboard\">\n"
                      + "    <div class=\"wt-leaderboard-avatar\" style=\"width: 33%;\">\n"
                      + "        <a href=\"" + rec.avatar_link + "\" target=\"_self\">"
                      + rec.avatar + "</a>\n"
                      + "    </div>\n"
                      + "    <div class=\"wt-leaderboard-info\">\n"
                      + "        <info>" + rec.rank_now + th + " " +data.dict.place+ "</info><br>\n"
                      + "        <bar></bar>\n"
                      + "        <info class=\"color\" style=\"text-align:left;\">"
                      + "<span style=\"position:absolute; z-index:1;\">"+user.replace("GRP_", "") +"</span>"
                      + "<span style=\"float:right; position:absolute; right:-2px;\">"+"<img src=\""
                      + path+"trophy_"+data.dict.trophy[i]+".svg\" style=\"margin-Top:-2px\"></img></span></info><br>\n"
                      + "        <info class=\"color big\">" + rec.points_now + "</info>&nbsp;\n"
                      + "        <info class=\"color\">" + data.dict.p + "</info><br>\n"
                      + "        <bar></bar><info class=\"small\">" + data.meta.city + "&nbsp;"
                      + data.meta.to + "</info><br>\n"
                      + "    </div>"
                      + "</div>");
            //console.log(user)
    
            // Breaking .each
            if ( counter >= input.limit ) { return(false); }; counter++;
        });

        $(e).fadeIn("fast");


    };

};

 
// ------------------------------------------------------------------
// Initialization function
// ------------------------------------------------------------------
$(document).on('ready',function() {
    // Looking for wt-ranking-container divs and call the show_ranking
    // plugin on each of these to load/display ranking data.
    $("div.wt-ranking-container").each(function() {
        $(this).show_ranking(jQuery.ajaxurl, jQuery.parseJSON($(this).attr("args")));
    });
    
    // Looking for wt-ranking-container divs and call the show_ranking
    // plugin on each of these to load/display ranking data.
    $("div.wt-leaderboard").each(function() {
        $(this).show_leaderboard(jQuery.ajaxurl, jQuery.parseJSON($(this).attr("args")));
    });
});
