<?php
// ------------------------------------------------------------------
/// @file uninstall.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief Script which will be called when the plugin will be
///        uninstalled from the wordpress installation.
///
/// @details At the moment there is no uninstall procedure. 
/// @todo Should ask the use wheter he is sure to uninstall the
///   plugin. If 'yes': drop data from database and delete the
///   wordpress plugin options.
// ------------------------------------------------------------------
?>
<h1>Does nothing at the moment!</h1>
<?php
   die("NOT TESTED");

   global $wpdb;

   $sql     = sprintf("select option_name from %soptions where option_name like \"wetterturnier_%\"",
                      $wpdb->prefix);
   // Fetch all option name wetterturnier_*
   $options = $wpdb->get_results( $sql );
   foreach ( $options as $rec ) {
      delete_option( $rec->option_name );
   }
?>
