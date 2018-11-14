<?php
# -------------------------------------------------------------------
# - NAME:        judgingform.php
# - AUTHOR:      Reto Stauffer
# - DATE:        2015-12-23
# -------------------------------------------------------------------
# - DESCRIPTION: Shows small html form where users can test the
#                judging routine and see how many points they would
#                get given a certain forecast/observed range.
# -------------------------------------------------------------------
# - EDITORIAL:   2015-12-23, RS: Created file on thinkreto.
# -------------------------------------------------------------------
# - L@ST MODIFIED: 2018-11-14 19:31 on marvin
# -------------------------------------------------------------------


if ( ! $args['parameter'] ) {
   echo "Sorry, cannot show form - parameter config wrong.";
} else {
   // Show the form
   ?>
   <script>
   jQuery(document).on('ready',function() {
   (function($) {
      var ajaxurl = "<?php print admin_url('admin-ajax.php'); ?>"
      $("form#judging_form_<?php print $args['parameter']; ?>").judgingform( ajaxurl );
   })(jQuery);
   });
   </script>
   
   <form id='judging_form_<?php print $args['parameter']; ?>' class='judging_form'>
      <input type='hidden' name='parameter' value='<?php print $args['parameter']; ?>'>

      <formdesc><?php _e("Observed"); ?>:</formdesc>
      <input class='judging_form_1th' type='text' name='observed_1' maxlength='7'></input>
      <input class='judging_form_2nd' type='text' name='observed_2' maxlength='7'></input>
      <br>

      <?php if ( $args['extra'] ) { ?>
      <formdesc><?php _e("Extra-Observed?","wpwt"); ?>:</formdesc>
      <input class='judging_form_1th' type='text' name='extra_1' maxlength='7'></input>
      <input class='judging_form_2nd' type='text' name='extra_2' maxlength='7'></input>
      <br>
      <?php } ?>

      <formdesc><?php _e("My forecast","wpwt"); ?>:</formdesc>
      <input class='judging_form_solo' type='text' name='forecast' maxlength='7'></input>
      <br>

      <formdesc><?php _e("Points you'll get","wpwt"); ?>:</formdesc>
      <input class='judging_form_solo' type='text' name='points' disabled></input>
      <br>

      <formdesc>&nbsp;</formdesc>
      <input type='button' name='foo' value='<?php _e("Compute points","wpwt"); ?>' />
   </form>


<?php } ?>
