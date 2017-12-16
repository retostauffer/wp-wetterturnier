<?php
global $wpdb;
global $WTadmin;

// There are two ways to use this script. If you have
// a direct link the script automatically leads you to
// the propper selection of city, station, and tournament date.
// But you can also "reset" the options to start from the
// beginning. Here I am creating this button.

$reset_url = sprintf('%s?page=%s',$WTadmin->curPageURL(true),$_GET['page']);
$reset_link = sprintf("<a href=\"%s\" target\"_self\" style=\"color: red;\">%s</a>",
                  $reset_url,"[RESET SELECTION]");
?>


<h1>Editing Obervations <?php print $reset_link; ?></h1>


<?php
// Initialize $_REQUEST so that it is never empty.
if ( empty($_REQUEST) ) { $_REQUEST = array(); } 


// ------------------------------------------------------------------
// THIS IS THE UPDATE PART! IF paramID, bdate, tdate, userID and
// cityID, and value are set in the POST variable -> update entry first.
// ------------------------------------------------------------------
if ( ! empty($_POST) ) {
   if ( ! isset($_POST['station']) || ! isset($_POST['submit']) ) {
      echo "<div id='message' class='error fade'>"
          ."I was told to update some observation but at least one "
          ."of the important definitions is missing! Stop.</div>";
      return;
   }

   // ---------------------------------------------------------------
   // $_POST['submit'] is 'update' we have to update some things.
   // ---------------------------------------------------------------
   if ( strcmp($_POST['submit'],'update') == 0 ) {

      // ------------------------------------------------------------
      // - Generate 'ERROR' message array
      // ------------------------------------------------------------
      $errormsg = array();
      $count_updated = 0;

      // ------------------------------------------------------------
      // - Loop over all $_POST keys to extract the
      //   obsval_<betdate>_<paramID> = value
      //   elements.
      // ------------------------------------------------------------
      foreach ( $_POST as $key => $val ) {

         if ( strcmp(substr($key,0,6),'obsval') != 0 ) { continue; }
         $tmp = explode("_",$key);
         $betdate = (int)$tmp[1]; $paramID = (int)$tmp[2];

         // - Load value first - check if something has changed.
         $sql = sprintf("SELECT value FROM %swetterturnier_obs "
               ." WHERE station = %d AND paramID = %d "
               ." AND betdate = %d",
               $wpdb->prefix,(int)$_POST['station'],
               $paramID,$betdate);
         $old = $wpdb->get_row( $sql ); 

         // ------------------------------------------------------------
         // - If old is equal to new: do not update
         //   If value length (as string) is zero: do not update
         //   If there is no old value, do update
         // -------------------------------------------------------------
         $do_update = false;
         if ( strlen( (string)$val ) == 0 ) { 
            $param_info = $WTadmin->get_param_by_id($paramID);
            array_push($errormsg,sprintf("%s, %s was empty!",
               strftime('%Y-%m-%d',$betdate*86400),$param_info->EN));
            continue; 
         } else if ( ! $old ) {
            $do_update = true;
         } else if ( $old->value != (int)round((float)$val*10) ) {
            $do_update = true;
         }

         // -------------------------------------------------------------
         // - Create update statement
         //   It is a insert on duplicate key update (upsert) statement.
         // -------------------------------------------------------------
         if ( $do_update ) {
            $update_table = sprintf('%swetterturnier_obs',$wpdb->prefix);
            $update_data  = array('station'       => (int)$_POST['station'],
                                  'paramID'       => $paramID,
                                  'betdate'       => $betdate,
                                  'value'         => (int)round((float)$val*10),
                                  'placedby'        => (int)get_current_user_id());
            $WTadmin->insertonduplicate($update_table,$update_data);

            // - Counter
            $count_updated = $count_updated+1;

            $param_info = $WTadmin->get_param_by_id($paramID);
            array_push($errormsg,sprintf("Changed: %s, %s to <b>%s</b>",
               strftime('%Y-%m-%d',$betdate*86400),$param_info->EN,number_format($val,1)));
         }
      }

      // - End-user message
      if ( $count_updated > 0 ) {
         echo "<div class=\"wetterturnier-info ok\">"
             ."Please note that the points "
             ."are not computed live while updating! There is a "
             ."python script in the backend computing the points. "
             ."The script should run a few times an hour so that "
             ."the corrected points for all users should be "
             ."online in a few minutes or so."
             ."</div>";
      }
      if ( count($errormsg) == 0 ) { array_push($errormsg,"Nothing changed."); }
      echo "<div class=\"wetterturnier-info warning\">"
          ."Some information:<br>"
          .join("<br>\n",$errormsg)
          ."</div>";

   // ---------------------------------------------------------------
   // - If the submit value was scary, stop.
   // ---------------------------------------------------------------
   } else {
      echo "<div class=\"wetterturnier-info erro\">"
          ."Got fancy submit value! Stop! There is somthing"
          ."uncool going on</div>";   
      die();
   }

}


// ------------------------------------------------------------------
// - Only allow to modify the last tournament. Makes no sense to
//   change even older values (at least this is what I hope).
// ------------------------------------------------------------------
$today = floor((int)date('U')/86400);
$last  = $WTadmin->older_tournament($today);
$last_string = strftime('%Y-%m-%d',$last->tdate*86400);
printf("<h2>Tournament: <b>%s</b> [%d]</h2>",$last_string,(int)$last->tdate);


// ------------------------------------------------------------------
// - If city is not set yet - show city selection!
//   Else selecting city. If wrong, print error, stop.
// ------------------------------------------------------------------
if ( ! isset($_REQUEST['city']) ) {
   echo "<fd>Select a city:</fd>";
   function wpwt_change_values_citylink($ID,$name) {
      global $WTadmin;
      printf("<a href='%s&city=%d' target='_self'>%s</a>&nbsp;&nbsp;",$WTadmin->curPageURL(),$ID,$name);
   } 
   foreach ( $$WTadmin->get_all_cityObj( false ) as $cityObj ) {
      wpwt_change_values_citylink($cityObj->get('ID'),$cityObj->get('name'));
   }
   return;
}


// ------------------------------------------------------------------
$cityObj = new wetterturnier_cityObject( (int)$_REQUEST['city'] );
if ( ! $cityObj->get('ID') ) {
   echo "<div id='message' class='error fade'>"
       ."Sorry, cannot find selected city in the database. "
       ."Something is going wrong! Stop.</div>";
   return;
}
printf("<h2>For city: <b>%s [%d]</b></h2>",$cityObj->get('name'),$cityObj->get('ID'));


// ------------------------------------------------------------------
// - Selecting the user
// ------------------------------------------------------------------
if ( ! isset($_REQUEST['wmo']) ) {
   echo "<fd>Select station</fd>";
   function wpwt_change_values_stationlink($wmo,$name) {
      global $WTadmin;
      print sprintf("<a href='%s&wmo=%d' target='_self'>%s</a>&nbsp;&nbsp;",$WTadmin->curPageURL(),$wmo,$name);
   } 
   // Loop trough all stations for this city and show edit link
   foreach ( $cityObj->stations() as $stnObj ) {
      wpwt_change_values_stationlink($stnObj->get('wmo'), $stnObj->get('name'));
   }
   return;
}

// ------------------------------------------------------------------
// Else go ahead
// ------------------------------------------------------------------
$stnObj = new wetterturnier_stationObject( (int)$_REQUEST['wmo'], 'wmo' );
if ( ! $stnObj->get('ID') ) {
   echo "<div id='message' class='error fade'>"
       ."Sorry, cannot find selected station (by wmo station number) "
       ." in the database. Something is going wrong! Stop.</div>";
   return;
}
printf("<h2>For station: <b>%s [%d]</b></h2>",$stnObj->get('name'),$stnObj->get('wmo'));


// ------------------------------------------------------------------
// - Loading the observations for a given station 
// ------------------------------------------------------------------
$obs1 = $WTadmin->get_station_obs_from_db($stnObj,$last,1,true);
$obs2 = $WTadmin->get_station_obs_from_db($stnObj,$last,2,true);
$obs = (object)array_merge((array)$obs1,(array)$obs2);


// ------------------------------------------------------------------
// - Show one form for each of the defined parameters, skip these
//   which are defined in the nullconfig of the station. Why so
//   complicated? We would like to update only the values the admin
//   changes. Not all values of a user.
// ------------------------------------------------------------------
$parameter  = $WTadmin->get_param_data(); 
$stnObj->show();

print 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxX';
die();
$nullconfig = json_decode($station->nullconfig);

// ------------------------------------------------------------------
// - Helper function to find value for a given day and parameter
// ------------------------------------------------------------------
function getthevalue($bets,$param,$day) {
   if ( ! $bets ) { return(false); }
   // - Check if entry exists
   $hash = sprintf('day%d_%s',$day,$param->paramName);
   //if ( isset($bets->$hash) ) { die(print_r($bets->$hash)); } 
   if ( isset($bets->$hash) ) { return( $bets->$hash ); }
   return(false);
}

// - Show frontend
?>
<form method="post" action="<?php print $WTadmin->curPageURL(); ?>">
<table>
   <tr>
      <th>Parameter</th>
      <th style>Saturday</th>
      <th style>Sunday</th>
   <tr>
   <?php
   // ---------------------------------------------------------------
   // Looping over the parameters which are defined for the current
   // city. NOTE: additionally counting empty fields. If at least
   // one field is empty you cannot "submit" the data! All parameters
   // have to be set to submit.
   // ---------------------------------------------------------------
   $empty_values = 0;

   // Looping now
   foreach($parameter as $param) {

      // - If parameter is in the nullconfig, skip.
      if ( ! is_null($nullconfig) ) {
         if ( is_numeric(array_search($param->paramID,$nullconfig)) ) { continue; }
      }

      // - Show param name
      echo "   <tr>\n"
          ."      <td>".$param->thename."</td>\n";
      // - Show small form
      for($day=1;$day<=2;$day++) {
         $res = getthevalue($obs,$param,$day);
         // - If $res is boolean, the return value was false
         //   which means that the user has not set any bet for this
         //   parameter. Prepare "output". 
         if ( is_bool($res) ) { 
            $msg = 'n/a';
            $value = false;
            $empty_values++;
         // - Else checking status and prepare the message
         //   for the admin.
         } else {
            if ( $res->placedby == 0 ) {
               $msg = sprintf('%s',(string)$res->placed);
            } else {
               $msg = sprintf("%s&nbsp;<span style=\"color: red;\">%s</span>\n",
                        (string)$res->placed,$WTadmin->get_user_by_ID($res->placedby)->user_login);
            }
            $value = number_format($res->value,1,".","");
         }
         
         // Create the "value" for the input field. User, City, and tournament date
         // are stored as hidden fields. But we need the additional information
         // about the "day" and "parameter ID". Therefore we create the
         // string here:
         $combined_value = sprintf('obsval_%d_%d',(int)$last->tdate+(int)$day,(int)$param->paramID);
         ?>
         <td>
               <input type="text" style="width: 60px; text-align: right;" name="<?php print $combined_value; ?>" value="<?php print $value; ?>" />
               <span style="font-size: 9px;">
                  <?php print $msg; ?>       
               </span>

               <!-- All the necessary hidden elements -->
               <input type='hidden' name='bdate' value='<?php print (int)$last->tdate+(int)$day; ?>'> 
               <input type='hidden' name='paramID' value='<?php print (int)$param->paramID; ?>'> 
            </form>
         <?php
      }
      echo "   </tr>\n";
   }
   ?>
</table>
<!-- general ids and shit -->
<input type='hidden' name='station' value='<?php print (int)$station->wmo; ?>'> 
<fd>Store changes, update database:</fd><input type='submit' name='submit' value='update' />
</form>

<?php
// ------------------------------------------------------------------
// - User messages with the option to "submit" the tip if all
//   values are (i) set and (ii) not allready submitted (only changed).
// ------------------------------------------------------------------


if ( $empty_values > 0 ) { ?>
   <div class='wetterturnier-info error'>
   WARNING: there are <?php print $empty_values; ?> empty values.
   Note that this is just a warning.
   </div>
<?php } ?>

