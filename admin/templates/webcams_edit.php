<?php
// ------------------------------------------------------------------
/// @page admin/templates/webcams_edit.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief This page shows the form to change the webcam details.
///   Furthermore, it also controls the update/edit on the database.
// ------------------------------------------------------------------


// Change content of a group entry
global $wpdb;
global $WTadmin;

// Update if needed
if ( ! empty($_REQUEST["action"]) ) {
   if ( is_numeric($_REQUEST["cam"]) & ! empty($_REQUEST["update"]) ) {
      $data = array( "uri"    => (string)$_REQUEST["uri"],
                     "desc"   => (string)$_REQUEST["desc"],
                     "source" => (string)$_REQUEST["source"] );
      $wpdb->update( sprintf("%swetterturnier_webcams",$wpdb->prefix),
                     $data, array( "ID" => (int)$_REQUEST["cam"] ) );
   }
}

$webcamObj = new wetterturnier_webcamObject( (int)$_REQUEST["cam"] );
$cityObj   = new wetterturnier_cityObject( (int)$webcamObj->get("cityID") );

$CURRENT = $WTadmin->curPageURL();
?>

<style>
form.webcamedit input[type='text'] {
   min-width: 400px;
   width: 50%;
}
</style>

<div class="wrap">

    <h2>Edit Parameter Entry</h2>

    <form class="webcamedit" method="post" action="<?php print $CURRENT; ?>">

        <fd>Linked to:</fd>
        <input type='text' name='cityID' value='<?php print $cityObj->get("name"); ?>' disabled /><br>

        <fd>Image URI:</fd>
        <input type='text' name='uri' value='<?php print $webcamObj->get("uri"); ?>' /><br>

        <fd>Source:</fd>
        <input type='text' name='source' value='<?php print $webcamObj->get("source"); ?>' /><br>

        <fd>Description:</fd>
        <input type='text' name='desc' value='<?php print $webcamObj->get("desc"); ?>' /><br>

        <input type="hidden" name="update" value="true" />
        <?php @submit_button(); ?>
    </form>

</div>


