<?php
// ------------------------------------------------------------------
/// @page admin/templates/stations_edit.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief This page shows the form to change the station details.
///   Furthermore, it also controls the update/edit on the database.
// ------------------------------------------------------------------


// Change content of a group entry
global $wpdb;
global $WTadmin;


// ------------------------------------------------------------------
// - Loading necessary data to display the edit form
// ------------------------------------------------------------------
$stnObj = new wetterturnier_stationObject( $_GET['station'] );

// ------------------------------------------------------------------
if ( $stnObj->get('cityID') > 0 ) {
   $cityObj = new wetterturnier_cityObject( $stnObj->get('cityID') );
} else { $cityObj = NULL; }

$REQ_URL = explode("?",$_SERVER['REQUEST_URI'],2);
$CURRENT = sprintf("https://%s%s?page=%s",$_SERVER['HTTP_HOST'],$REQ_URL[0],$_REQUEST['page']);
#$CURRENT = 'https://' . $_SERVER['HTTP_HOST'] . $REQ_URL[0]
#          .'?page='.$_REQUEST['page']; 
$CURRENT = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>


<div class="wrap">

   <h2>Edit Station Entry</h2>

   <div class='wetterturnier-info'> 
   Here you can change some of the station properties.
   the "<?php _e('Parameter observed','wpwt'); ?>" list 
   defines which parameters are not observed at the station.
   For example: Innsbruck University will never ever offer
   observed "current weather" Wv/Wn. If you check the 
   checkbox I can deliver this information to the user.
   </div>

   <form method="post" action="<?php print $CURRENT; ?>">

      <input type='hidden' name='what' value='edit'>

      <fd><?php _e('Station name','wpwt'); ?>:</fd>
      <input type='text' name='name' value='<?php print $stnObj->get("name"); ?>' /><br>

      <fd><?php _e('Station WMO number','wpwt'); ?>:</fd>
      <input type='text' name='wmo' value='<?php print $stnObj->get('wmo'); ?>' size='6' maxlength='6' /><br>

      <fd><?php _e('Currently connected to','wpwt'); ?>:</fd>
      <?php $cityname = (is_null($cityObj) ? _e("Not attached to a city","wpwt") : $cityObj->get('name')); ?>
      <input type='text' name='sort' value='<?php print $cityname; ?>' disabled /><br>

      <fd><?php _e('Parameter observed','wpwt'); ?>:</fd>
      <div style='display: block;'>
      <?php print $stnObj->showParamCheckboxes(); ?>
      </div><br>
     
      <fd>&nbsp;</fd>
      <input type='hidden' name='station' value='<?php print $stnObj->get('ID'); ?>'>

      <?php @submit_button(); ?>
   </form>

</div>
