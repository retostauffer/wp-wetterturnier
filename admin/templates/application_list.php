<?php
// ------------------------------------------------------------------
/// @page admin/templates/application_list.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief How the group-application requests admin page will be displayed.
// ------------------------------------------------------------------


global $wpdb;

// If not allready available, load wordpress class-wp-list table first
if(!class_exists('WP_List_Table')){
   require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
// Import personal list class
require_once( sprintf("%s/../classes/application_list.php",dirname(__FILE__)) );

// actionlink needed to send a few of the forms on this page
$actionlink =  sprintf('?page=%s',$_REQUEST['page']);
?>

<div class="wrap">


   <h2>Open applications</h2>
   <?php
   // Success message after changing something
   if ( ! empty($_GET['m']) && $_GET['m'] == 1 ) {
       echo "<div id='message' class='updated fade'><p><strong>"
           .__("Successfully changed the group entry","wpwt")
           ."</strong></p></div>";
   } 

   // Pick search string
   $search = (empty($_REQUEST['s']) ? false : $_REQUEST['s']);

   // ---------------------------------------------------------------
   // Prepare two tables. The first (if action='wtapply') shows the table
   // with the applications to get into a group; these are the ones with
   // a active=9 flag in the database.
   // The second table (with action='wtremove') shows the table with the
   // requests to get removed from a group.
   // ---------------------------------------------------------------
   foreach ( array('wtapply','wtremove') as $action ) {
      if ( $action === 'wtapply' ) { ?>
         <h2>Users who want to join a group</h2>
         This table contains the applications of the users to join
         one of our wetterturnier groups. You can either approve them
         (from then on they will be a member of this group) or reject
         (do not put them into the group). In both cases no message will
         be sent to the user, so please inform them via e-mail or something.
      <?php } else { ?>
         <h2>Users who want to leave a group</h2>
         This table contains the 'please remove me from the group' requests.
         The same as for the applications you have two options: either you
         approve the request (user will no longer be in the group from now on)
         or to reject (user stays in the group). Again, no message will be sent
         to the user. Please inform them via e-mail or something.
      <?php }

      // Create and show table
      $wp_list_table = new Wetterturnier_Application_List_Table( $search, $action );
      $wp_list_table->prepare_items();
      // Show search form
      echo "  <form action=\"" . $actionlink ."\" method=\"post\">\n";
      echo "  <input type=\"hidden\" name=\"page\" value=\"".$_REQUEST['page']."\" />\n";
      $wp_list_table->search_box('search', 'user_login');
      echo "  </form>\n\n";
      $wp_list_table->display();
   }
   ?>

</div>
