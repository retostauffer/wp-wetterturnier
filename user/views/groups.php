<!-- Small tablesorter functionality -->

<script>
   jQuery(document).on('ready',function($) {
      (function($) {
         // Allows user to sort the tables
          $(".wttable-groups").tablesorter({sortList: [[0,0]]});
          $(".wttable-groups th").css('cursor', 'pointer');

         // Show inactive groupmembers
         $('.groups-show-inactive').click( function() {
            // Getting group ID
            var groupID = $(this).attr('groupID');
            $('#wttable-group-'+groupID).find('td').show();
         });
      })(jQuery);

   });
</script>
<?php


$groupsObj = new wetterturnier_groupsObject();
$groupsObj->show_frontend_tables();

?>
