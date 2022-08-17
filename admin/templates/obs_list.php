<?php
// ------------------------------------------------------------------
/// @page admin/templates/obs_list.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief How the observations admin page will be displayed.
// ------------------------------------------------------------------


global $wpdb, $WTadmin;

// If not allready available, load wordpress class-wp-list table first
if(!class_exists('WP_List_Table')){
   require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
// Import personal list class
require_once( sprintf("%s/../classes/obs_list.php",dirname(__FILE__)) );
require_once( sprintf("%s/../../betclass.php",dirname(__FILE__)) );
$WTbetclass = new wetterturnier_betclass();

// Check if the admin user is already on his/her way to edit
// some values. If $_REQUEST['action'] is empty - or not 'edit', setting
// $edit to false. Else true.
if ( empty($_REQUEST['action']) )            { $edit = false; }
else if ( ! $_REQUEST['action'] === 'edit' ) { $edit = false; }
else                                         { $edit = true;  }


if ( empty($_REQUEST['cityID']) ) {
   //$cityID = (int)$wpdb->get_row(sprintf("SELECT ID FROM %swetterturnier_cities ORDER BY ID ASC",$wpdb->prefix))->ID;
   ?>
   <h1>Please select a city first</h1>

   <div class="error" style="max-width: 800px;">
   There is a select-box on top of this page where you can select a city. Please
   select your city first.<br>
   We do not offer a default city to avoid you from changing the obs from a 
   certain station for the wrong city.
   </div>
   <?php
   die();
} else { $cityID = (int)$_REQUEST['cityID']; }
$city = $WTadmin->get_city_info($cityID);

// ------------------------------------------------------------------
// Tournament date. By default the current_tournament will be selected,
// however, the admin can select another date. For simplicity the
// latest 10 tournaments will be offerd only.
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
   However, if you have to change observations from an earlier tournament
   just change the data here. This was a quick implementation of a date picker,
   a more advanced could be implemented as well, it will be sufficient in 99%
   of all cases. If you wanna change something very old you can also go to the
   archive and use the green 'edit' button there (works).
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

<div class="wrap">

   <h1>Please check your settings!</h1>
   <div class='notice'>
   <h3>Tournament date: <span class="wt-admin-highlight">
      <?php
      printf("%s, %s",$WTadmin->date_format($selected_tdate,"l"),$WTadmin->date_format($selected_tdate));
      ?>
   </span></h3>
   <h3>Selected city: <span class="wt-admin-highlight"><?php print $city->name; ?></span></h3>
   <?php if ( ! empty($_REQUEST['station']) ) {
      $station = $WTadmin->get_station_by_wmo( (int)$_REQUEST['station'] );
      ?>
      <h3>Selected station: <span class="wt-admin-highlight">
         <?php printf("%s [%s]",$station->name,$station->wmo); ?></span>
      </h3>
   <?php } ?>
   </div>

   <?php if ( ! $edit ) { ?>

      <h1>Add <b>new</b> observations (or edit them)</h1>
      If there are no observations at all for a given station, then you wont
      be able to find a entry in the table below! In this case just select
      the station from the list here, and press the button. You can also do
      this instead of clicking onto the edit button below:
      <?php print $WTadmin->curPageURL(true); ?>
      <form id='add-obs' method="get" action="<?php print $WTadmin->curPageURL(true); ?>" autocomplete="off">
         <select style='float: left;' name="station">
            <?php
            $stations = $WTadmin->get_station_data_for_city( (int)$_REQUEST['cityID'] );
            foreach ( $stations as $station ) {
               printf("<option value=\"%d\">[%d] %s</option>",$station->wmo,
                        $station->wmo,$station->name);
            } ?>
         </select>
         <input type="submit" name="" value="Add Observations"></input>
         <input type="hidden" name="cityID" value="<?php print $city->ID; ?>"></input>
         <input type="hidden" name="page" value="<?php print $_REQUEST['page']; ?>"></input>
         <input type="hidden" name="action" value="edit"></input>
      </form>

   <?php } ?>


<?php
// Setting default to avoid error messages
if ( empty($_REQUEST['action']) ) { $_REQUEST['action'] = NULL; }
// If "action=edit" we have to show the edit form here.
if ( $edit ) { 

   // Exit because one of the required edit-inputs is missing
   if ( empty($_REQUEST['cityID']) || empty($_REQUEST['station']) ) {
      echo "<div id='message' class='error'>"
          .__("You tried to edit a observation, but either the city ID or the station ID is missing. "
             ."Seems that there is either a problem with the code, or you tried to manipulate "
             ."the URL. Dont do the latter one please. If it is a problem with the code, please "
             ."inform your administrator.","wpwt")
          ."</div>";
   // Exit because one of the required inputs is non-integer
   } else if ( ! filter_var($_REQUEST['cityID'], FILTER_VALIDATE_INT) ||
               ! filter_var($_REQUEST['station'], FILTER_VALIDATE_INT) ) {
      echo "<div id='message' class='error'>"
          .__("You tried to edit a station, but either the city ID or the station ID contain "
             ."non-integer value. Seems that there is a problem or you tried to manipulate "
             ."the URL. Dont do the latter one please. If it is a problem with the code, please "
             ."inform your administrator.","wpwt")
          ."</div>";
   // If input $_REQUEST['save'] is not set or not equal to 1 (which means that
   // the admin-user submitted changes) we will just show the form. Else save changes 
   // and show the form with the new values.
   } else {

      $cityID  = (int)$_REQUEST['cityID'];
      $station = (int)$_REQUEST['station'];

      if ( ! empty($_POST) ) {

         global $WTuser;
         global $wpdb;
         $data = $_POST;

         // Loading all existing values. Reason: only "overwrite" those changed
         // by the administrator in the change-obs-form. 
         $tdate     = (int)$data['tdate'];
         $bdate_min = (int)$data['tdate'] + 1;
         $bdate_max = (int)$data['tdate'] + get_option("wetterturnier_betdays");
         $existing = $wpdb->get_results(sprintf("SELECT * FROM %swetterturnier_obs "
                  ."WHERE station=%d AND betdate BETWEEN %d AND %d", 
                  $wpdb->prefix,$station,$bdate_min,$bdate_max));

         // Helper method which will be used below, checking if a certain value
         // has been changed by the admin or not.
         function obs_changed($tmp,$existing) {
            // If not "existing", the admin added a new value. Return adminID 
            $changed = NULL; // Null will be returned if NOT FOUND
            foreach ( $existing as $rec ) {
               // Parameter and betdate match?
               if ( $rec->paramID == $tmp['paramID'] && $rec->betdate == $tmp['betdate'] ) {
                  // One is NULL the other not?
                  if      (   is_null($rec->value) & ! is_null($tmp['value']) ) { $changed = true; }
                  else if ( ! is_null($rec->value) &   is_null($tmp['value']) ) { $changed = true; }
                  // Value changed: admin changed an existing value, return
                  // the userID of the admin who is manipulating the data.
                  else if ( (int)$rec->value !== (int)$tmp['value'] ) { $changed = true;  }
                  else { $changed = false; }
                  // End loop
                  break;
               }
            }
            return($changed);
         }

         $admin_userID = get_current_user_id();

         foreach ( $data as $key => $value ) {

            // Searching for properties like "day_X"
            if ( ! preg_match("/^[a-zA-Z]{1,}_[1-9]/",$key) ) { continue; }
            list($param,$day) = explode("_",$key); $day = (int)$day;
            // Compute "betday" (tdate + day)
            $betdate = $tdate + $day;
            // Loading full parameter object
            $param = $WTuser->get_param_by_name($param);

            $tmp =     array("station"   => (int)$_GET['station'],
                             "paramID"   => (int)$param->paramID,
                             "betdate"   => (int)$betdate );

            // if value empty: delete it
            if ( is_null($value) || strlen($value) == 0 )     {

                $wpdb->delete($wpdb->prefix . "wetterturnier_obs", $tmp); 
                continue;
            }

            // Replace fucking "," with "."
            $value = str_replace(",",".",$value);

            // append the data to temp array
            $tmp["value"]    = (is_numeric($value) ? $value*10. : NULL);
            $tmp["placedby"] = $admin_userID;

            // Admin mode: check if the admin really changed this value.
            $check = obs_changed($tmp,$existing);
            if ( $check === false ) { continue; }

            $WTadmin->insertonduplicate(sprintf("%swetterturnier_obs",$wpdb->prefix),$tmp,
                      array("value","placedby"),False);
            unset($tmp);
            // Save a rerun flag into the database such that we can re-run the
            // computation of the required tournaments as the observations changed.
            $rerun = array('userID'=>get_current_user_id(),'cityID'=>$city->ID,
                           'tdate'=>$selected_tdate);
            $wpdb->insert(sprintf("%swetterturnier_rerunrequest",$wpdb->prefix),$rerun);
         }

      }
      ?>
      <br><h3>Change stored observation values</h3>
      <?php $WTbetclass->print_form( $cityID, $station, true, $selected_tdate ); die(); ?>
      <br>

      <form action="<?php print $WTadmin->curPageURL(true); ?>" method="get">
         <input type="hidden" name="cityID" value="<?php print $cityID; ?>"></input>
         <input type="hidden" name="page"   value="<?php print $_REQUEST["page"]; ?>"></input>
         <input type="submit" value="<?php _e("Back to bet list","wpwt"); ?>"></input>
      </form>

   <?php }

// Else show the overview with or without the modification message.
} else {?>
   
   <h1>Existing observations to modify</h1>
   <?php
   // Success message after changing something
   if ( ! empty($_GET['m']) && $_GET['m'] == 1 ) {
       echo "<div id='message' class='updated fade'><p><strong>"
           .__("Successfully changed the observation entry","wpwt")
           ."</strong></p></div>";
   } 

   // prepare data/items with search string, if set
   if ( ! empty($_REQUEST['s']) ) { $search = $_REQUEST['s']; }
   else                           { $search = false; }

   $wp_list_table = new Wetterturnier_Obs_List_Table( $city,$selected_tdate,$search );
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
