<?php
// ------------------------------------------------------------------
/// @page admin/templates/bets_list.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief How the bets or forecast admin page will be displayed.
// ------------------------------------------------------------------

global $wpdb, $WTadmin;

// If not allready available, load wordpress class-wp-list table first
if(!class_exists('WP_List_Table')){
   require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
// Import personal list class
require_once( sprintf("%s/../classes/bets_list.php",dirname(__FILE__)) );

// Check if the admin user is already on his/her way to edit
// some values. If $_REQUEST['action'] is empty - or not 'edit', setting
// $edit to false. Else true.
if ( empty($_REQUEST['action']) )            { $edit = false; }
else if ( ! $_REQUEST['action'] === 'edit' ) { $edit = false; }
else                                         { $edit = true;  }


// Load city and tournament info from $_SESSION
// (set there in views/edit-bets.php)
if ( empty($_REQUEST['cityID']) ) {
   // Let the user select a city first
   ?>
   <h1>Please select a city first</h1>
   
   <div class="error" style="max-width: 800px;">
   There is a select-box on top of this page where you can select a city. Please
   select your city first.<br>
   We do not offer a default city to avoid you from changing the bets from a 
   certain player for the wrong city.
   </div>
   <?php
   die();
} else { $cityID = (int)$_REQUEST['cityID']; }
$city = $WTadmin->get_city_info($cityID);

// ------------------------------------------------------------------
// Loading current tournament
// ------------------------------------------------------------------
$current = $WTadmin->latest_tournament( (int)date("%s")/86400 );
$selected_tdate = (empty($_REQUEST['tdate'])) ? $current->tdate : (int)$_REQUEST['tdate'];

# Loading past tournaments from database older than current
$past_sql = sprintf("SELECT tdate FROM %swetterturnier_dates WHERE status = 1 "
                   ."AND tdate <= %d ORDER BY tdate DESC LIMIT 10",
                   $wpdb->prefix,$current->tdate);
$past_tournaments = $wpdb->get_results($past_sql);
if ( $past_tournaments ) {
   ?>
   <h1>Tournament date</h1>

   <help>
   The default tournament date is always the one from the current tournament.
   However, if you have to change bets from an earlier tournament
   just change the data here (only last 10 are shown).
   <br>
   <form id='change-date' method="get" action="<?php print $WTadmin->curPageURL(true); ?>" autocomplete="off">
   <span><b>Change tournament date:</b></span>
   <select name="tdate">
   <?php
   foreach( $past_tournaments as $rec ) {
      $selected = ((int)$rec->tdate == $selected_tdate) ? " selected" : "";
      printf("  <option value='%d' %s>%s</option>\n",$rec->tdate,
            $selected,$WTadmin->date_format($rec->tdate));
   } ?>
   </select>
   <input type="hidden" name="cityID" value="<?php print $city->ID; ?>"></input>
   <input type="hidden" name="page"   value="<?php print $_REQUEST["page"]; ?>"></input>
   <input type="submit" name="submit" />
   </form>
   </help>
<?php }


// actionlink needed to send a few of the forms on this page
$actionlink =  sprintf('?page=%s',$_REQUEST['page']);
?>

<!-- Bit of css styling -->
<style type='text/css'>
.striped > tbody > .row-valid-false              { background-color: #F5BBBB; }
.striped > tbody>:nth-child(odd).row-valid-false { background-color: #F4CDCD; }
.striped > tbody > tr:hover                      { background-color: #66ccff; }
.striped > tbody > tr.row-valid-false:hover      { background-color: #ff9966; }
#col_userID    { width: 80px; }
#col_userlogin { width: 250px; }
</style>

<div class="wrap">

   <h1>Please check your settings!</h1>
   <div class='notice'>
   <h3>Tournament date: <span class="wt-admin-highlight">
      <?php
      printf("%s, %s",$WTadmin->date_format($selected_tdate,"%A"),$WTadmin->date_format($selected_tdate));
      ?>
   </span></h3>
   <h3>Selected city: <span class="wt-admin-highlight"><?php print $city->name; ?></span></h3>
   <?php if ( ! empty($_REQUEST['userID']) ) {
      $user = $WTadmin->get_user_by_ID( (int)$_REQUEST['userID'] );
      ?>
      <h3>Selected user: <span class="wt-admin-highlight">
         <?php printf("%s [%s]",$user->display_name,$user->user_login); ?></span>
      </h3>
   <?php } ?>
   </div>

   <?php if ( ! $edit ) { ?> 

      <h1>Add a <b>new</b> bet / modify bet</h1>
      If a user was not able to submit his bet, not even one single value,
      then he/she won't be listed below. In this case you can select the user here
      and add a completely new bet. If the user already submitted some values you can
      either select him/her from the list below, or just use this small form to find
      and edit the submitted values. 
      The form helps you searching for valid users:<br>
      <?php $WTadmin->include_js_script("wetterturnier.usersearch"); ?>
      <script type="text/javascript">
         jQuery(document).on('ready',function() {
            (function($) {
               var ajaxurl = <?php printf("'%s'\n",admin_url('admin-ajax.php')); ?>
               $("#bet-user-search").usersearch(ajaxurl,{});
               $("form#add-bet input[type='button']").on("click",function() {
                  var userID = $(this).closest("form").find("input[name='user-search']").attr('userid')
                  var cityID = $(this).closest("form").find("input[name='cityID']").val()
                  var tdate  = $(this).closest("form").find("input[name='tdate']").val()
                  var url = "<?php printf("%s?page=%s",$WTadmin->curPageURL(true),$_REQUEST['page']); ?>"
                  url += "&action=edit&userID="+userID+"&cityID="+cityID+"&tdate="+tdate
                  window.location.replace( url )
               });
            })(jQuery);
         });
      </script>
      <form id='add-bet' method="get" action="" autocomplete="off">
         <div id='bet-user-search' style="float: left;"></div>
         <input type="button" name="Add bet" value="Add Bet"></input>
         <input type="hidden" name="userID" value=""></input>
         <input type="hidden" name="tdate" value="<?php print $selected_tdate; ?>"></input>
         <input type="hidden" name="cityID" value="<?php print $city->ID; ?>"></input>
      </form>

   <?php } ?>


<?php
// Setting default - just to avoid error messages
if ( empty($_REQUEST['action']) ) { $_REQUEST['action'] = NULL; }
// If "action=edit" we have to show the edit form here.
if ( $edit ) {

   // Exit because one of the required edit-inputs is missing
   if ( empty($_REQUEST['cityID']) || empty($_REQUEST['userID']) ) {
      echo "<div id='message' class='error'>"
          .__("You tried to edit a bet, but either the city ID or the user ID is missing. "
             ."Seems that there is either a problem with the code, or you tried to manipulate "
             ."the URL. Dont do the latter one please. If it is a problem with the code, please "
             ."inform your administrator.","wpwt")
          ."</div>";
   // Exit because one of the required inputs is non-integer
   } else if ( ! filter_var($_REQUEST['cityID'], FILTER_VALIDATE_INT) ||
               ! filter_var($_REQUEST['userID'], FILTER_VALIDATE_INT) ) {
      echo "<div id='message' class='error'>"
          .__("You tried to edit a bet, but either the city ID or the user ID contain "
             ."non-integer value. Seems that there is a problem or you tried to manipulate "
             ."the URL. Dont do the latter one please. If it is a problem with the code, please "
             ."inform your administrator.","wpwt")
          ."</div>";
   // If input $_REQUEST['save'] is not set or not equal to 1 (which means that
   // the admin-user submitted changes) we will just show the form. Else save changes 
   // and show the form with the new values.
   } else {

      global $WTbetclass;

      // Save cityID and userID onto simple-access variables
      $cityID = (int)$_REQUEST['cityID'];
      $userID = (int)$_REQUEST['userID'];
      $tdate  = (int)$_REQUEST['tdate'];
      $city   = $WTadmin->get_city_info( $cityID );

      // - If not empty _POST, update database
      if ( ! empty($_POST) ) {
         $WTbetclass->update_bet_database($WTadmin->next_tournament(0,false,$tdate),
                      get_user_by('id',$userID));
         // If not current date append to rerun database table which 
         // is triggering the re-computation of the points.
         $rerun = array('userID'=>get_current_user_id(),'cityID'=>$cityID,
                        'tdate'=>$selected_tdate);
         if ( $tdate != $current->tdate ) {
            $wpdb->insert(sprintf("%swetterturnier_rerunrequest",$wpdb->prefix),$rerun);
         }
      }
      ?>
      <h1>Change user-bets</h1>
      <?php $WTbetclass->print_form( $cityID, $userID, false, $selected_tdate ); ?>
      <br>

      <form action="<?php print $WTadmin->curPageURL(true); ?>" method="get">
         <input type="hidden" name="cityID" value="<?php print $cityID; ?>"></input>
         <input type="hidden" name="page"   value="<?php print $_REQUEST["page"]; ?>"></input>
         <input type="submit" value="<?php _e("Back to bet list","wpwt"); ?>"></input>
      </form>
   <?php }

// Else show the overview with or without the modification message.
} else {?>
   
   <br><h1>Edit existing values</h1>
   <?php
   // Success message after changing something
   if ( ! empty($_GET['m']) && $_GET['m'] == 1 ) {
       echo "<div id='message' class='updated fade'><p><strong>"
           .__("Successfully changed the bet entry","wpwt")
           ."</strong></p></div>";
   } 

   // prepare data/items with search string, if set
   if ( ! empty($_REQUEST['s']) ) { $search = $_REQUEST['s']; }
   else                           { $search = false; }

   // Initialize new wordpress admin table object 
   $wp_list_table = new Wetterturnier_Bets_List_Table( $city, $selected_tdate, $search );
   $wp_list_table->prepare_items();

   // Show search form
   echo "  <form action=\"" . $actionlink ."\" method=\"post\">\n";
   echo "  <input type=\"hidden\" name=\"cityID\" value=\"".$cityID."\" />\n";
   echo "  <input type=\"hidden\" name=\"page\" value=\"".$_REQUEST['page']."\" />\n";
   $wp_list_table->search_box('search', 'user_login');
   echo "  </form>\n\n";

   // Display table
   $wp_list_table->display();

} ?>

</div>
