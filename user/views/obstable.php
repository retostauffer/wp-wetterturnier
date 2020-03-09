<?php
// ------------------------------------------------------------------
/// @file user/views/obstable.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief Frontent page to display the latest observations in table
///   form.
/// @details Based on the station definition or the wetterturnier
///   this page displays the latest observations in a table format.
///   This view was mainly used during development to see whether
///   we got the required observations or whether there is someting
///   wrong with the backend and/or observations are missing.
///   The file contains some css/jQuer functions as well.
// ------------------------------------------------------------------

global $WTuser;

// Access only for logged in users
if ( $WTuser->access_denied() ) { return; }

/// Loading active city, see @ref wetterturnier_generalclass::get_current_cityObj
$cityObj = $WTuser->get_current_cityObj();

// Including the needed jquery script
$WTuser->include_js_script("wetterturnier.obstable");

// Get custom table styling
$wttable_style = get_user_option("wt_wttable_style");
$wttable_style = (is_bool($wttable_style) ? "" : $wttable_style);
?>

<script>
jQuery(document).on('ready',function() {
   (function($){

      // Function to refresh the data table
      function loadDataTable( title, statnr, days ) {
         ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
         var statnr = $("input.active.obs-table-station").attr("statnr")
         var title  = $("input.active.obs-table-station").val()
         var days   = $("input.active.obs-table-days").attr("days")
console.log( statnr+'  '+title+'  '+days )
         var style = "<?php print($wttable_style); ?>"
         $('#obs-table').show_obstable({ajaxurl:ajaxurl,style:style,title:title,statnr:statnr,days:days});

      }

      // Initialize the data
      $("input[type='button'].obs-table-station").first().addClass("active")
      $("input[type='button'].obs-table-days").first().addClass("active")
      loadDataTable( )

      // Adding func. to select station/days
      $(document).on("click","input[type='button'].obs-table-station",function() {
         $("input[type='button'].obs-table-station").removeClass("active")
         $(this).addClass("active")
         loadDataTable( ) 
      })
      $(document).on("click","input[type='button'].obs-table-days",function() {
         $("input[type='button'].obs-table-days").removeClass("active")
         $(this).addClass("active")
         loadDataTable( ) 
      })

      // Makes lines highlightable
      $(document).on("click","table.wttable-obs tr",function($) {
         $ = jQuery
         var trclass = $(this).attr('class')
         var classname = "highlighted";
         if ( $(this).hasClass( classname ) ) {
            $("table.wttable-obs tr[class='"+trclass+"']").removeClass( classname )
            $("table.wttable-obs tr[class='"+trclass+"']").addClass( classname )
         }
      });


      // add parser through the tablesorter addParser method
  $.tablesorter.addParser({
    // set a unique id
    id: 'data',
    is: function(s, table, cell, $cell) {
      // return false so this parser is not auto detected
      return false;
    },
    format: function(s, table, cell, cellIndex) {
      var $cell = $(cell);
      // I could have used $(cell).data(), then we get back an object which contains both
      // data-lastname & data-date; but I wanted to make this demo a bit more straight-forward
      // and easier to understand.

      // returns lastname data-attribute, or cell text (s) if it doesn't exist
      return $cell.attr('class') || s;
      },
      // flag for filter widget (true = ALWAYS search parsed values; false = search cell text)
      parsed: false,
      // set type, either numeric or text
      type: 'text'
   });

      // Allows user to sort the tables
      $(".wttable-obs").tablesorter({sortList: [[1,0]],
            textExtractrion: "basic", sortInitialOrder: "desc",
            stringTo: "bottom", debug: "true"});
      //$(".wttable-obs th").css('cursor', 'pointer');
      var resort = true;
      $(".wttable-obs").trigger("updateAll", [resort]);

   })(jQuery);
});
</script>

<style>
table.wttable-obs            { width: auto; }
table.wttable-obs tr td.null { color: #B2B2B2; }
table.wttable-obs tr td      { white-space: nowrap; }
input[type='button'].obs-table-station  { margin-right: 10px; }
input[type='button'].obs-table-days     { margin-right: 10px; }
input[type='button'].active             { background-color: #41a62a;     }
div#obs-table                           { margin-top: 20px; }
table.wttable-obs tr td          { background-color: transparent !important; }
table.wttable-obs tr:nth-child(odd) { background-color: #eef0f2; } 
table.wttable-obs tr.highlighted { background-color: #ffe4a8; }
table.wttable-obs tr.highlighted:nth-child(odd) { background-color: #ffd270; }
.wttable-obs th, .wttable-obs td { max-width: 100px; }
#wttable-obs-nav {
    margin-bottom: 1em; 
}
#wttable-obs-nav > ul {
    list-style: none;
    position: relative;
}
#wttable-obs-nav > ul > li {
    float: left; min-width: 100px;
}
#wttable-obs-nav > .preset > h3 {
   font-size: 1em; float: left;
   padding-right: 1em; line-height: 1.5em;
}
#wttable-obs-nav > .preset > ul {
   list-style: none;
   position: relative;
}
#wttable-obs-nav > .preset > ul li {
   float: left; padding: 0 1em 0 0;
   cursor: pointer;
}
#wttable-obs-nav > .preset > ul li:hover {
    color: #ff6600;
}
</style>

<?php
foreach( $cityObj->stations() as $stnObj ) {
        if ( $stnObj->get("active") != 1 ) { continue; }     
	printf("<input type=\"button\" class=\"obs-table-station\" statnr=\"%d\" value=\"[%d] %s\"></input>",
           $stnObj->get('wmo'),$stnObj->get('wmo'),$stnObj->get('name'));
}
for ( $i=2; $i<=9; $i++ ) {
   printf("<input type=\"button\" class=\"obs-table-days\" days=\"%d\" value=\"%d d\"></input>",
            $i,$i);
}
?>
<div id='obs-table'></div>
