<?php
/*!
 * Admin settings page 
*/

// DEVELOPMENT: delete and re-set all options. This should be done
// by the plugin installer at the end. But for the development this
// is nicer

global $WTadmin;

//delete_option("wetterturnier_bet_closingoffset");
//add_option(   "wetterturnier_bet_closingoffset", "5","","yes");

// ------------------------------------------------------------------
// If $_POST was not empty we have to update the settings 
// ------------------------------------------------------------------
if ( ! empty($_POST) ) {
    foreach ( $_POST as $key=>$val ) {
        if ( strcmp($key,'submit') == 0 ) { continue; }
        // Update wordpress option. If it fails: try to create
        if ( ! update_option($key,$val) ) {
           add_option($key,$val,"","yes");
        }
    }
    // Re-initialize options with new values
    $WTadmin->options = $WTadmin->init_options();
}

// ------------------------------------------------------------------
// Some helper-functions to create the forms and submit buttons. 
// ------------------------------------------------------------------
function show_input($title,$name,$options,$disabled=false) {
    echo "<fd>".str_replace("\"","\\\"",$title)."</fd>\n";
    $disabled = $disabled ? " disabled" : "";
    echo "<input type=\"text\" name=\"".$name."\" value=\"".$options->$name."\" ".$disabled."/>\n";
    echo "<br>\n";
}
function start_form() {
    global $WTadmin;
    echo "<form method=\"POST\" action=\"".$WTadmin->curPageURL()."\">\n";
}
function end_form()   {
    echo "    <input type=\"submit\" name=\"submit\" value=\"Save changes\">\n";
    echo "</form>\n";
}

?>
<h1>Wetterturnier Settings</h1>

<help>
   The <b>settings</b> section allows you to control some of the
   basic wetterturnier plugin settings. Please note that changes will
   immediately affect the tournament. Settings you can change in here:
   <ul>
      <li><b>Closing time:</b> the time when the bet-form will be closed.</li>
      <li><b>Tournament offset:</b> actually the bet-form closes <b>N</b> minutes
         after the official closing time, however, can also be set to zero. Was
         a requirement from the admins while the tool was developed.</li>
      <li><b>Bet-form opening:</b> how many days before the official tournament-date
         the bet-form should be opened. Only the next tournament will be shown, always.
         If you have scheduled one tournament a day, and the opening starts 2 days before,
         the bet-form will only be available for the next one, not for two, or even more
         tournaments in the future.</li>
      <li><b>Date format:</b> how dates should be displayed on the frontend (date only).</li>
      <li><b>Datetime format:</b> how date and time should be displayed on the frontend.</li>
      <li><b>Float number format:</b> there are two options. One character can be specified
         which is used as the thousand separator (e.g., 1.035, english format), the other
         one specifies the decimal separator.</li>
      <li><b>Terms links:</b>: Depending on the language we have to show our users the
         english or german version of the "Terms and Conditions" or "Nutzungsbedingungen".</li>
      <li><b>Number of bet-days:</b> currently two (e.g., on a friday-tournament bets
         have to be placed for Saturday, and Sunday). <strike>Could also be changed.</strike>
         <i>Note: large parts of the code are ready to handle more than two days. However,
         some core methods are not. The database design only allows two forecast bet 
         days at the moment. Would require quite a bit of programming to change this
         (Reto, June 2017).</i></li>
      <li><b>Others:</b> see settings page.</li>
   </ul>
</help>


<div id='wetterturnier-admin-settings'>

    <h2>Tournamet settings</h2>
    <div class='wpwt-admin-info'>
    Note that there is an official closing time given by "HHMM" UTC. This
    is the time shown on the front end where the counter is based on and stuff.
    But, we are no bad guys and give the users a little bit more time. The
    offset defines when the form closes. After that time (closing time + offset)
    the bet form will be locked - you cannot insert any data or change anything.
    </div>
    <?php
    start_form();
    show_input('Tournament, official closing time, UTC, format: HHMM:',
               'wetterturnier_bet_closingtime',$WTadmin->options);
    
    show_input('Tournament offset:',
               'wetterturnier_bet_closingoffset',$WTadmin->options);

    show_input('Bet-Form opens n-days before tournament:',
               'wetterturnier_bet_open_days',$WTadmin->options);

    // English date (date only) and datetime (date and time) format 
    show_input('English version date format (see php date doc):',
               'wetterturnier_date_format_en',$WTadmin->options);
    show_input('English version datetime format (see php date doc):',
               'wetterturnier_datetime_format_en',$WTadmin->options);

    // English floating point number format
    show_input('English decimal number decimal point character:',
               'wetterturnier_floatdsep_format_en',$WTadmin->options);
    show_input('English decimal number thousand separator character:',
               'wetterturnier_floattsep_format_en',$WTadmin->options);

    // German date (date only) and datetime (date and time) format 
    show_input('German version date format (see php date doc):',
               'wetterturnier_date_format_de',$WTadmin->options);
    show_input('German version datetime format (see php date doc):',
               'wetterturnier_datetime_format_de',$WTadmin->options);

    // German floating point number format
    show_input('German decimal number decimal point character:',
               'wetterturnier_floatdsep_format_de',$WTadmin->options);
    show_input('German decimal number thousand separator character:',
               'wetterturnier_floattsep_format_de',$WTadmin->options);

    // Links to terms and conditions page of the Wetterturnier
    show_input('Link to Terms and Conditions (english):',
               'wetterturnier_terms_link_en',$WTadmin->options);
    show_input('Link to Nutzungsbedingungen (german):',
               'wetterturnier_terms_link_de',$WTadmin->options);

    show_input('Number of bet days (forecast days):',
               'wetterturnier_betdays',$WTadmin->options,true);

    end_form(); 
    ?>


    <h2>General settings of the plugin</h2>
    <div class='wpwt-admin-info'>
    Those are a few general settings for the wetterturnier plugin.
    Normally you dont have to change them.
    </div>
    <?php
    start_form();
    show_input('CSS selector where the city-menu should be placed',
               'wetterturnier_cities_menu_css',$WTadmin->options);
    show_input('Name of stylesheet dependency (loads own after this)',
               'wetterturnier_style_deps',$WTadmin->options);
    end_form(); 
    ?>

</form>
