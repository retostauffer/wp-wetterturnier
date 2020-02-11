<?php

global $WTuser;

// Access only for logged in users
if ( $WTuser->access_denied() ) { return; }

global $wpdb;

$table    = "archive";
$stations = $WTuser->obsdb_get_avaliable_stations( $table );
?>


<script>
jQuery(document).ready(function($) {

   var pluginpath = "<?php print plugins_url('wp-wetterturnier'); ?>";

   $(document).on("click","input.wt-dataexport-obs<?php print $table; ?>",function() {
      var statnr    = $(this).closest("form").find("select[name='station']").val()
      var NAvalue   = $(this).closest("form").find("select[name='NAvalue']").val()
      var delimiter = $(this).closest("form").find("select[name='delimiter']").val()
      var year      = $(this).closest("form").find("input[name='year']").val()

      var url = pluginpath+"/user/views/export.php?action=obs<?php print $table; ?>"
                +"&statnr="+statnr+"&NAvalue="+NAvalue
                +"&delimiter="+delimiter+"&year="+year

      // Raw mode and Preset mode
      if ( $(this).closest("form").find("input[name='preset']").is(":checked") ) {
         url = url+"&preset"
      }
      if ( $(this).closest("form").find("input[name='raw']").is(":checked") ) {
         url = url+"&raw"
      }

      window.location=url //pluginpath+"/user/views/export.php"
   });
});
</script>

<form>
   <!-- Select station -->
   <formdesc><?php _e("Station","wpwt"); ?>:</formdesc>
   <formfield>
      <select class="wt-dataexport-obs<?php print $table; ?>" name="station">
         <?php
         foreach ( $stations as $rec ) {
            printf("  <option value=\"%d\">%s - %s&nbsp;&nbsp;|&nbsp;&nbsp;%d %s</option>\n",
                  $rec->statnr,$rec->from,$rec->to,$rec->statnr,$rec->name);
         } ?>
      </select>
   <formfield><br>

   <!-- Select delimiter -->
   <formdesc><?php _e("Request year","wpwt"); ?>:</formdesc>
   <formfield>
      <input type='text' name="year" maxlength="4" value="<?php print date("Y"); ?>"></input>
   </formfield><br>
   
   <!-- Select NA value -->
   <formdesc><?php _e("Missing value","wpwt"); ?>:</formdesc>
   <formfield>
      <select name="NAvalue">
         <option value="-9999" selected>-9999</option>
         <option value="NA">NA</option>
         <option value="blank">[blank]</option>
      </select>
      <!--
      <input type="radio" name="NAvalue" value="-999" checked /> -999<br>
      <input type="radio" name="NAvalue" value="" /> "empty"<br>
      <input type="radio" name="NAvalue" value="NA" /> NA<br>
      -->
   </formfield><br>

   <!-- Select delimiter -->
   <formdesc><?php _e("Delimiter","wpwt"); ?>:</formdesc>
   <formfield>
      <select name="delimiter">
         <option value="semikolon" selected>semikolon (leads to csv output)</option>
         <option value="blank">blank (no delimiter)</option>
      </select>
   </formfield><br>

   <!-- Option for RAW output and PRESET -->
   <formdesc><?php _e("RAW data","wpwt"); ?>:</formdesc>
   <formfield>
      <input type="checkbox" name="raw" />
      <?php _e("If checked, output contains RAW database values!","wpwt"); ?>
   </formfield><br>
   <formdesc><?php _e("Export preset","wpwt"); ?>:</formdesc>
   <formfield>
      <input type="checkbox" name="preset" checked />
      <?php _e("Checked: export (useful) preset. Else everything.","wpwt"); ?>
   </formfield><br>

   <formdesc>&nbsp;</formdesc>
   <formfield>
      <input type="button" type="submit" class="wt-dataexport-obs<?php print $table; ?>" value="Download">
   </formfield><br>
</form>

