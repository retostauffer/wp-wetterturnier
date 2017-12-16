<?php

global $wpdb;
global $WTuser;

// ------------------------------------------------------------------
// Preloading all groups
// ------------------------------------------------------------------
$sql = "SELECT * FROM %swetterturnier_groups WHERE active > 0";
$groups = $wpdb->get_results(sprintf($sql,$wpdb->prefix));


// ------------------------------------------------------------------
// Show poromote form. 
// ------------------------------------------------------------------

// Logged in users can see the application form
if ( is_user_logged_in() ) {
   // The jQuery code for sending applications. 
   ?>
   <script type='text/javascript'>
   jQuery(document).on('ready',function() {
       (function($) {
           var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
           $("#wtapplication input[type='submit']").on("click",function() {

               // what is either wtapply or wtremove
               var what =  $(this).closest('div').attr('id');
               // for wtapply gID is the group ID. 
               // for wtremove gID is the groupuserID (unique ID from wetterturnier_groupusers)
               var gID  =  parseInt( $("#wtapplication #"+what+" select[name='gID']").val() );
               var uID  =  parseInt( $("#wtapplication input[name='uID']").val() );
               var text =  $("#wtapplication #"+what+" textarea").val();

               // Check user inputs
               if ( gID < 0 ) {
                  alert('<?php _e('Cannot send inquiry. Please select a group first!','wpwt') ?>')
               } else if ( text.length < 10 ) {
                  alert('<?php _e('Cannot send inquiry. Please write application text.','wpwt') ?>')
               } else {
                  // Ajaxing the calculation miniscript
                  $.ajax({
                      url: ajaxurl, dataType: 'json', type: 'post', async: false,
                      data: {action:'applygroup_ajax',what:what,gID:gID,uID:uID,text:text},
                      success: function(results) { 

                         if ( results.got == 'ok' ) {
                             $("#wtapplication").empty(); 
                             $('#wtapplication').append(
                                 "<div class='wetterturnier-info ok'>" +
                                 "<?php _e('Inquiry successfully sent','wpwt'); ?>" +
                                 "</div>"
                             );
                         } else if ( results.got == 'ismember' ) {
                             $('#wtapplication #'+what).append(
                                 "<br><br><div class='wetterturnier-info error'>" +
                                 "<?php _e('You are allready an active member of that group.','wpwt'); ?>" +
                                 "</div>"
                             );
                         }
                      },
                      error: function(e) {
                         $error = e; console.log('errorlog'); console.log(e); 
                         $("#wtapplication").empty(); 
                         $('#wtapplication').append( e.responseText );
                      }
                  });
               }
           });
       })(jQuery);
   });
   </script>

   <?php $user = wp_get_current_user(); ?>
   <div id='wtapplication'>
      <input type='hidden' name='uID' value='<?php print $user->ID; ?>' />
      <div id='wtapply'>
         <?php _e('I would like to apply for group:','wpwt'); ?><br>
         <select name='gID' style='width: 100%;'>
            <?php
            // Show group options
            printf('    <option value=\'%d\' selected>%s</option>\n',-9,__('Select a group ...','wpwt'));
            foreach ( $groups as $rec ) {
               printf('    <option value=\'%d\'>%s</option>\n',$rec->groupID,$rec->groupName);
            } ?>
         </select>
         <textarea name='application'><?php
            print __('Hy, my name is','wpwt');
            print ' '.$user->display_name.' '; 
            print __('and I would like to apply for the group because ...','wpwt');
         ?></textarea>
         <input class='wtsendapplication' type='submit' name='submit' value='<?php _e('Apply','wpwt'); ?>' style='min-width: 150px;' />
      </div>
      <br>
      <div id='wtremove'>
         <?php
         $ingroups = $WTuser->get_groups_for_user( $user->ID );
         if ( is_array($ingroups) ) { ?>
         <?php _e('Please remove me from group:','wpwt'); ?><br>
         <select name='gID' style='width: 100%;'><?php
            // Show group options
            printf('    <option value=\'%d\' selected>%s</option>\n',-9,__('Select a group ...','wpwt'));
            foreach ( $ingroups as $rec ) {
               printf('    <option value=\'%d\'>%s</option>\n',$rec->ID,$rec->groupName);
            }
         ?></select>
         <textarea name='application'><?php
            print __('Hy, my name is','wpwt');
            print ' '.$user->display_name.' '; 
            print __('and I wont\'t be a member of this group anymore because ...','wpwt');
         ?></textarea>
         <input class='wtsendapplication' type='submit' name='submit' value='<?php _e('Remove','wpwt'); ?>' style='min-width: 150px;' />
         <?php } ?>
      </div>

   </div>
<?php
// For non-logged in users just show a message.
} else { ?>
   <div class='wetterturnier-info warning'>
   <?php
   _e('Sorry, you are not logged in. Only logged in users can apply online for a group membership.','wpwt');
   ?>
   </div>
<?php } ?>
