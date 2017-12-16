<?php


// ------------------------------------------------------------------
// Main part of the script. Calls update_bet_database if the user
// submits data. In both cases the bet form will be shown.
// Only allowed for logged-in users.
// ------------------------------------------------------------------
if ( ! is_user_logged_in() ) {
   echo "<div class=\"wetterturnier-info error\">"
       .__("You have to be registered and logged in to place your bet.","wpwt")
       ."</div>";

   // Show login form
   printf("<h1 class='entry-title'>%s</h1>",__("Login form","wpwt"));
   wp_login_form( );
} else {

   global $WTuser;
   global $WTbetclass;

   // - If not empty _POST, update database
   if ( ! empty($_POST) ) {
      $WTbetclass->update_bet_database(); 
   }
   // - Looping over all groups
   $WTbetclass->print_form();
}
?>
