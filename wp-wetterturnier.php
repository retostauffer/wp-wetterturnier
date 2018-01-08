<?php
/*
Plugin Name: WP Wetterturnier 
Text Domain: wtlang
Domain Path: /languages
Plugin URI: http://www.wetterturnire.de
Description: The Wetterturnier bet plugin 
Version: 0.1
Author: Reto Stauffer
Author URI: http://www.wetterturnier.de
License: GPL2
*/
/*
Copyright 2017  Reto Stauffer  (email : reto.stauffer@uibk.ac.at)
*/




// ------------------------------------------------------------------
/// @file wt-wetterturnier.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief Wordpress plugin specification script. This script
///   initializes the wordpress plugin and is used by wordpress
///   to load and construct all required classes.
// ------------------------------------------------------------------
if(!class_exists('WP_wetterturnier'))
{
   // WP_wetterturnier extends the generalclass
   // containing some methods I am using in the
   // userclass and the adminclass.
   require_once('generalclass.php');
   // Class checking the bets from the users. Used by
   // autosubmit and the frontend
   require_once('betclass.php');

   // ---------------------------------------------------------------
   /// @brief Contains the main wordpress plugin structure initialization
   ///   methods.
   /// @details Initializes the plugin and sets e.g., plugin path,
   ///   specifies the plugin textdomain which is used by the polylang
   ///   plugin used for multilingual support and so far and so on.
   ///   The class also contains the @ref activate and @ref deactivate
   ///   methods which are used by wordpress within the plugin
   ///   management system.
   // ---------------------------------------------------------------
   class WP_wetterturnier extends wetterturnier_generalclass
   {

      // ----------------------------------------------------------
      /// @details Construct the plugin object
      // ----------------------------------------------------------
      public function __construct()
      {
          // Do I really need this? :)
          $plugin = plugin_basename(__FILE__);

          // Loading language file
          load_plugin_textdomain('wpwt', false, 
              dirname(plugin_basename(__FILE__)) . '/languages/'); 

          // Initialize Settings
          require_once(sprintf("%s/settings.php", dirname(__FILE__)));
          $WP_wetterturnier_Settings = new WP_wetterturnier_Settings();

          // Load language
          load_plugin_textdomain('wtlang');

          $role = get_role( 'administrator' );
          // This only works, because it accesses the class instance.
          // would allow the author to edit others' posts for current theme only
          $role->add_cap( 'wetterturnier_admin' ); 


      } // END public function __construct


      // ----------------------------------------------------------
      /// @details Function which is used to activate the plugin.
      ///   when activating the plugin some options are set and
      ///   databases will be created if not existing.
      ///   This method also makes use of the demo database sql files
      ///   in the `demodb` folder which are used to create the
      ///   required tables when activating the plugin the first
      ///   time.
      ///
      /// @todo Reto develop and test activate/deactivate/uninstall
      ///   procedures for the plugin. Not yet done properly.
      // ----------------------------------------------------------
      public static function activate()
      {
          global $wpdb;
      
          // Adding the css tag where we would like to place
          // the cities menu. Default is twentyfourteen-child and
          // the nav-menu menue (kill all wordpress entries and replace
          // by the defined cities). Can be changed somewhen in the
          // plugin admin panel (hopefully). Or change it here.
          delete_option('wetterturnier_cities_menu_css');
          add_option(   'wetterturnier_cities_menu_css', 'div.wetterturnier-cities-menu', '', 'yes');

          delete_option("wetterturnier_bet_closingtime");
          add_option(   "wetterturnier_bet_closingtime", "1500","","yes");
          
          delete_option("wetterturnier_bet_closingoffset");
          add_option(   "wetterturnier_bet_closingoffset", "5","","yes");

          // Add new role
          //remove_role('wetterturnier_admin');
          $capabilities = array('read'=>true);
          $result = add_role( 'wetterturnier_admin', __('Wetterturnier Admin' ), $capabilities );

          // THE PLUGIN INSERTS SOME
          // SAMPLE DATA IF THE TABLES DO NOT EXIST
          // Create wetterturnier data table if not existing!
          $table = $wpdb->prefix . "wetterturnier_bets";
          if($wpdb->get_var("SHOW TABLES LIKE '".$table."'") != $table) {
              $sql = file_get_contents(sprintf('%s/demodb/bets.sql',dirname(__FILE__)));
              $sql = explode(";",str_replace("%table%",$table,$sql));
              foreach ( $sql as $cmd ) {
                  $wpdb->query($cmd);
              }
          }
          // Create wetterturnier groups table 
          $table = $wpdb->prefix . "wetterturnier_groups";
          if($wpdb->get_var("SHOW TABLES LIKE '".$table."'") != $table) {
              $sql = file_get_contents(sprintf('%s/demodb/groups.sql',dirname(__FILE__)));
              $sql = explode(";",str_replace("%table%",$table,$sql));
              foreach ( $sql as $cmd ) {
                  $wpdb->query($cmd);
              }
          }
          // Create wetterturnier groupusers table (which user in which group) 
          $table = $wpdb->prefix . "wetterturnier_groupusers";
          if($wpdb->get_var("SHOW TABLES LIKE '".$table."'") != $table) {
              $sql = file_get_contents(sprintf('%s/demodb/groupusers.sql',dirname(__FILE__)));
              $sql = explode(";",str_replace("%table%",$table,$sql));
              foreach ( $sql as $cmd ) {
                  $wpdb->query($cmd);
              }
          }
          // Create wetterturnier parameter table
          $table = $wpdb->prefix . "wetterturnier_param";
          if($wpdb->get_var("SHOW TABLES LIKE '".$table."'") != $table) {
              $sql = file_get_contents(sprintf('%s/demodb/parameter.sql',dirname(__FILE__)));
              $sql = explode(";",str_replace("%table%",$table,$sql));
              foreach ( $sql as $cmd ) {
                  $wpdb->query($cmd);
              }
          }
          // Create cities table 
          $table = $wpdb->prefix . "wetterturnier_cities";
          if($wpdb->get_var("SHOW TABLES LIKE '".$table."'") != $table) {
              $sql = file_get_contents(sprintf('%s/demodb/cities.sql',dirname(__FILE__)));
              $sql = explode(";",str_replace("%table%",$table,$sql));
              foreach ( $sql as $cmd ) {
                  $wpdb->query($cmd);
              }
          }
          // Create obs table 
          $table = $wpdb->prefix . "wetterturnier_obs";
          if($wpdb->get_var("SHOW TABLES LIKE '".$table."'") != $table) {
              $sql = file_get_contents(sprintf('%s/demodb/obs.sql',dirname(__FILE__)));
              $sql = explode(";",str_replace("%table%",$table,$sql));
              foreach ( $sql as $cmd ) {
                  $wpdb->query($cmd);
              }
          }
          // Create stations table 
          $table = $wpdb->prefix . "wetterturnier_stations";
          if($wpdb->get_var("SHOW TABLES LIKE '".$table."'") != $table) {
              $sql = file_get_contents(sprintf('%s/demodb/stations.sql',dirname(__FILE__)));
              $sql = explode(";",str_replace("%table%",$table,$sql));
              foreach ( $sql as $cmd ) {
                  $wpdb->query($cmd);
              }
          }

          // Getting user list content
          $demo_users = explode(';',file_get_contents(sprintf('%s/demodb/demo.users.list',dirname(__FILE__))));
          foreach ( $demo_users as $demo_user ) {
              $demo_user = explode(':',$demo_user); 
              if ( count($demo_user) == 2 ) {
                  $user  = trim($demo_user[0]);
                  $group = trim($demo_user[1]);
              } else {
                  $user  = trim($demo_user[0]);
                  $group = 'NONE';
              }
              if ( strlen($user) == 0 ) { continue; }
              if ( ! is_callable(wp_create_user) ) { die('cannot call'); }
              $uid = wp_create_user($user,'',$user."@nothing.org");
              if ( strcmp($group,'NONE') != 0 ) { #AND is_integer($uid) ) {
                  $tmp = $wpdb->get_row('SELECT * FROM wp_wetterturnier_groups WHERE groupName = \''.$group.'\'');
                  if ( is_object($tmp) ) {
                      $wpdb->query(sprintf('INSERT INTO wp_wetterturnier_groupusers (`userID`,`groupID`) VALUES (%d, %d)',
                               $uid,$tmp->groupID));
                  }
              }
          }

          // Create new pages, use activate_pages.php
          require_once(sprintf('%s/activate_pages.php',dirname(__FILE__)));
      } // END public static function activate


      // ----------------------------------------------------------
      /// @details Procedure called by wordpress when the plugin
      ///   is deactivated in the plugin management system of
      ///   wordpress. 
      ///   Deletes some wetterturnier-related options from the
      ///   wordpress options database, ...
      ///
      /// @todo Reto check activate/deactivate procedure.
      // ----------------------------------------------------------
      public static function deactivate()
      {
          global $wpdb;
   
          // Remove some options stored while activating the plugin
          delete_option('wetterturnier_cities_menu_css');

          // Getting user list content
          $demo_users = explode(';',file_get_contents(sprintf('%s/demodb/demo.users.list',dirname(__FILE__))));
          foreach ( $demo_users as $demo_user ) {
              $demo_user = explode(':',$demo_user); 
              if ( count($demo_user) == 2 ) {
                  $user  = trim($demo_user[0]);
                  $group = trim($demo_user[1]);
              } else {
                  $user  = trim($demo_user[0]);
                  $group = 'NONE';
              }
              if ( strlen($user) == 0 ) { continue; }
              if ( ! is_callable(wp_create_user) ) { die('cannot call'); }
              $uid = $wpdb->get_row("SELECT ID FROM wp_users WHERE user_login = '".$user."'");
              if ( $uid->ID > 2 ) {
                  wp_delete_user($uid->ID); 
              }
          }

      } // END public static function deactivate

   } // END class WP_wetterturnier

} // END if(!class_exists('WP_wetterturnier'))


// ------------------------------------------------------------------
/// @details Initializin the plugin if WP_Wetterturnier class exists
///   (wordpress was able to read the class file). Registers some
///   wordpress hooks like e.g., the activation, deactivation and
///   uninstall hook (used by the wordpress plugin manager).
///   Impors/reads the widget files from user/widgets such that
///   you can use them via the wordpress admin interface.
///   Last but not least: imports the adminclass (if admin) or
///   userclass and betclass (for all visitors) which contain
///   the core methods for the whole wetterturnier plugin.
// ------------------------------------------------------------------
if( class_exists('WP_wetterturnier') )
{

    error_reporting(E_ALL);
    ini_set('display_errors', 1);


    // Installation and uninstallation hooks
    register_activation_hook(__FILE__,   array('WP_wetterturnier', 'activate'));
    register_deactivation_hook(__FILE__, array('WP_wetterturnier', 'deactivate'));
    register_uninstall_hook(__FILE__,    array('WP_wetterturnier', 'uninstall'));

    // Custom helper class instance
    // Has to be called BEFORE loading the widgets.
    require_once(sprintf("%s/classes.php",   dirname(__FILE__)));
    require_once(sprintf("%s/chartclass.php",   dirname(__FILE__)));
    require_once(sprintf("%s/oldoutputclass.php",   dirname(__FILE__)));

    // INCLUDE WIDGETS ----------------------------------------------
    // include the widgets for this plugin 
    // INCLUDE WIDGETS ----------------------------------------------
    require_once(sprintf("%s/user/widgets/tournaments.php", dirname(__FILE__)));
    require_once(sprintf("%s/user/widgets/blitzortung.php", dirname(__FILE__)));
    require_once(sprintf("%s/user/widgets/webcams.php", dirname(__FILE__)));
    require_once(sprintf("%s/user/widgets/leading.php",     dirname(__FILE__)));
    require_once(sprintf("%s/user/widgets/latestobs.php",   dirname(__FILE__)));
    require_once(sprintf("%s/user/widgets/bbpmessages.php", dirname(__FILE__)));

    // instantiate the plugin class
    $wp_wetterturnier = new WP_wetterturnier();

    if ( is_admin() ) {
        require_once(sprintf("%s/admin/adminclass.php", dirname(__FILE__)));
        $WTadmin = new wetterturnier_adminclass();
        require_once(sprintf("%s/user/userclass.php", dirname(__FILE__)));
        $WTuser     = new wetterturnier_userclass();
        require_once(sprintf("%s/betclass.php", dirname(__FILE__)));
        $WTbetclass = new wetterturnier_betclass();
    } else {
        require_once(sprintf("%s/admin/adminclass.php", dirname(__FILE__)));
        require_once(sprintf("%s/user/userclass.php", dirname(__FILE__)));
        $WTuser     = new wetterturnier_userclass();
        require_once(sprintf("%s/betclass.php", dirname(__FILE__)));
        $WTbetclass = new wetterturnier_betclass();
    }

}
?>

