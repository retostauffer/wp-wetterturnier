<?php

global $WTuser;

// ------------------------------------------------------------------
// Main part of the script. Calls update_bet_database if the user
// submits data. In both cases the bet form will be shown.
// Only allowed for logged-in users.
// ------------------------------------------------------------------
if ( ! is_user_logged_in() ) {
   printf("%s\n%s<br>\n%s %s %s %s %s. %s %s %s %s %s\n%s",
       "<div class=\"wetterturnier-info error\">",
       __("You have to be registered and logged in to place your bet.","wpwt"),
       __("To register please","wpwt"),
       "<a href=\"/wp-login.php?action=register\" target=\"_self\">",
       __("register","wpwt"),
       "</a>",
       __("a new account","wpwt"),
       __("If you already have an account just","wpwt"),
       "<a href=\"wp-login.php\" target=\"_blank\">",
       __("login","wpwt"),
       "</a>",
       __("and enyoy the gambling!","wpwt"),
       "</div>");

   // Show login form
   printf("<h1 class='entry-title'>%s</h1>",__("Login form","wpwt"));
   wp_login_form( );
} else {

   if (get_user_option("wt_betform_mos") === "above") {
      require_once( "mosforecasts.php" );
   }
   // - Loading bet-class class
   require_once( sprintf("%s/../../betclass.php",dirname(__FILE__)) );
 
   // - Initialize betclass object.
   $WTbetclass = new wetterturnier_betclass();

   // - If not empty _POST, update database
   if ( ! empty($_POST) ) {
      $WTbetclass->update_bet_database(); 
   }
   // - Looping over all groups
   $WTbetclass->print_form();

   if (get_user_option("wt_betform_mos") === "below") {
      require_once( "mosforecasts.php" );
   }

}
?>
