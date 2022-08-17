<h1>Wetterturnier Parameter Management</h1>

<help>
   These are the <b>parameters</b> the use have to specify. Plese note
   that parameters can be set active/inactive on city level (see <b>cities</b>)
   You are allowed to add new parameters here. But please note that each new 
   parameter requires some changes on (i) how the observations are prepared, and
   (ii) how the bets will be judged.
   The interface furthermore allows to set a specific data range. E.g., for 
   temperature, the allowed range lies between -50/+50 (degrees Celsius). If
   a user tries to submit something outside, the "betclass" object will reject
   these and inform the user.
   Parameters cant be deleted. If you don't need a parameter again, change
   the cities-settings (and uncheck the parameter there).
</help>

<?php
// The citiesview handles different
// things all for the group manipulating.
// Actions are transported by the _GET "action" argument.
// If set, try to call the correct page.
// If no action is set (default), view grop list.

global $wpdb;
function include_action_file( $filename ) {
    include(sprintf("%s/../templates/%s", dirname(__FILE__),$filename));
}

// - If action is set
if ( ! empty($_GET['action']) ) {

    // Now depending on action we have to do some stuff
    if ( $_GET['action'] == "edit" && ! empty($_GET['param']) ) {
        include_action_file('param_edit.php');
    }

// - Show grup_list else 
} else {


    // Update a parameter
    if ( ! empty($_POST['what']) && $_POST['what'] == 'edit' ) {

        // Loading old entry first
        $old = $wpdb->get_row(sprintf('SELECT * FROM %swetterturnier_param WHERE paramID = %s',
                              $wpdb->prefix,$_POST['param']));
        // Creat update array
        $update = array('EN'=>esc_html(stripslashes($_POST['EN'])),
                        'DE'=>esc_html(stripslashes($_POST['DE'])),
                        'helpEN'  => esc_html(stripslashes($_POST['helpEN'])),
                        'helpDE'  => esc_html(stripslashes($_POST['helpDE'])),
                        'helpEN'  => esc_html(stripslashes($_POST['helpEN'])),
                        'valmin'  => (int)( round($_POST['valmin'] * 10. ) ),
                        'valmax'  => (int)( round($_POST['valmax'] * 10. ) ),
                        'decimals'=> (int)( $_POST['decimals'] )
                       );
        // unit set?
        if ( strlen($_POST['unit']) > 0 ) {
            $update["unit"] = esc_html(stripslashes($_POST['unit']));
        } else {
            $update["unit"] = "";
        }
        if ( ! empty($_POST['valformat']) && strlen($_POST['valformat']) > 0 ) {
            $update['valformat'] = $_POST['valformat'];
        }
        if ( ! empty($_POST['vallength']) ) { $update['vallength'] = $_POST['vallength']; }

        // Do update?
        $update_flag = $wpdb->update($wpdb->prefix.'wetterturnier_param',$update,
                                     array('paramID'=>$_POST['param']));
        if ( ! $update_flag ) { echo 'Problems while updating.'; }


    // Add a new parameter
    } else if ( ! empty($_REQUEST['paramName']) & ! empty($_REQUEST['EN']) & ! empty($_REQUEST['DE']) ) {

        $paramName = stripslashes(esc_html((string)$_REQUEST['paramName']));
        $EN        = stripslashes(esc_html((string)$_REQUEST['EN']));
        $DE        = stripslashes(esc_html((string)$_REQUEST['DE']));
        $helpEN    = stripslashes(esc_html((string)$_REQUEST['helpEN']));
        $helpDE    = stripslashes(esc_html((string)$_REQUEST['helpDE']));
        $valmin    = (int)( floor($_REQUEST['valmin'] * 10.) );
        $valmax    = (int)( floor($_REQUEST['valmax'] * 10.) );

        // Check if group allready exists
        if ( count($wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'wetterturnier_param '
                                     .' WHERE paraName = "'.$paramName.'"')) > 0 ) {

            echo "<div id='message' class='error fade'><p><strong>"
                .__("Parameter with this parameter name existing! Cannot add again!","wpwt")
                ."</strong></p></div>";
        } else {
            // If one of the ID's is negative (-9) nothing was choosen.
            if ( strlen($paramName) <= 0 | strlen($EN) <= 0 | strlen($DE) <= 0 )  {
                $_REQUEST['added'] = false;
                echo "<div id='message' class='error fade'><p><strong>"
                    .__("Parameter not added. Parameter name, english or german description was empty.","wpwt")
                    ."</strong></p></div>";
            }  else {
                $insert_flag = $wpdb->insert($wpdb->prefix.'wetterturnier_param',
                                             array('paramName'=>$paramName,'EN'=>$EN,'DE'=>$DE,
                                                   'helpEN'=>$helpEN,'helpDE'=>$helpDE),
                                             array('%s','%s'));
                echo "<div id='message' class='updated fade'><p><strong>"
                    .__("Parameter added. Online right now!","wpwt")
                    ."</strong></p></div>";
            }
            if ( ! $insert_flag ) { echo 'Problems while inserting.'; }
        }
    }

        
    include_action_file('param_list.php');
}
?>
