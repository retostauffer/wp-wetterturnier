<?php
// ------------------------------------------------------------------
/// @page admin/templates/param_edit.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief This page shows the form to change the parameter details.
///   Furthermore, it also controls the update/edit on the database.
// ------------------------------------------------------------------


// Change content of a group entry
global $wpdb;
$param = $wpdb->get_row(sprintf('SELECT * FROM %swetterturnier_param WHERE paramID = %s',$wpdb->prefix,$_GET['param']));


$REQ_URL = explode("?",$_SERVER['REQUEST_URI'],2);
$CURRENT = sprintf("https://%s%s?page=%s",$_SERVER['HTTP_HOST'],$REQ_URL[0],$_REQUEST['page']);
?>


<div class="wrap">

    <h2>Edit Parameter Entry</h2>

    <form method="post" action="<?php print $CURRENT; ?>">

        <input type='hidden' name='what' value='edit' />

        <fd><?php _e('Param name [fix]','wpwt'); ?>:</fd>
        <input type='text' name='paramName' value='<?php print $param->paramName; ?>' disabled /><br>

        <fd>English description:</fd>
        <input type='text' name='EN' value='<?php print $param->EN; ?>' /><br>

        <fd>German description:</fd>
        <input type='text' name='DE' value='<?php print $param->DE; ?>' /><br>

        <fd>English help:</fd>
        <textarea name='helpEN' cols="50" rows="3"><?php print esc_html($param->helpEN); ?></textarea><br>

        <fd>German help:</fd>
        <textarea name='helpDE' cols="50" rows="3"><?php print esc_html($param->helpDE); ?></textarea><br>

        <fd><?php _e('Validation format','wpwt'); ?>:</fd>
        <select name='valformat'>
            <option value=''>empty</option>
            <option value='number' <?php if ( $param->valformat == 'number' ) { print "selected"; } ?>>Float (number)</option>
            <option value='digits' <?php if ( $param->valformat == 'digits' ) { print "selected"; } ?>>Integer (digits)</option>
        <select><br>

        <fd><?php _e('Validation length','wpwt'); ?>:</fd>
        <input type='text' name='vallength' value='<?php print $param->vallength; ?>' /><br>

        <fd><?php _e('Minimum value','wpwt'); ?>:</fd>
        <input type='text' name='valmin' value='<?php print $param->valmin/10.; ?>' /><br>

        <fd><?php _e('Maximum value','wpwt'); ?>:</fd>
        <input type='text' name='valmax' value='<?php print $param->valmax/10.; ?>' /><br>

        <fd><?php _e('Nr. of decimals','wpwt'); ?>:</fd>
        <input type='text' name='decimals' value='<?php print $param->decimals; ?>' /><br>

        <fd><?php _e('Validation length','wpwt'); ?>:</fd>
        <input type='text' name='unit' value='<?php print $param->unit; ?>' /><br>

        <input type='hidden' name='param' value='<?php print $param->paramID; ?>'>

        <?php @submit_button(); ?>
    </form>

</div>
