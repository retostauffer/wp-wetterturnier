<?php
// ------------------------------------------------------------------
// A small function extracting all <help></help> blocks from php files
// ------------------------------------------------------------------
function get_help_entries( $file ) {
   // Getting absolute path of the file
   global $WTadmin;
   $path = parse_url( $WTadmin->plugins_url() ); $path = $path["path"];
   $abspath = realpath(sprintf("%s/%s",get_home_path(),$path));
   //print_r($abspath);
   // Loading file content
   $content = file_get_contents( sprintf("%s/admin/views/%s",$abspath,$file) );
   // Extracting content of all <help></help> blocks.
   preg_match_all("/<help>(.*)<\/help>/",str_replace("\n","",$content),$help);
   return( $help );
}
// ------------------------------------------------------------------
// Loading help entries via get_help_entries and displays them.
// ------------------------------------------------------------------
function show_help_entries( $file ) {
   $help = get_help_entries( $file );
   if ( count($help[0]) == 0 ) {
      printf("<help>No help-entries found in file %s</help>",$file);
   } else {
      foreach ( $help[0] as $rec ) {
         if ( $help ) { printf("%s",$rec); }
      }
   }
}
?>

<h1>Wetterturnier Plugin Info</h1>

<help>
    The wetterturnier-plugin is fully implemented into wordpress. It allows all registered admins to 
    change the settings, correct bets, correct observations, or add new stations, and parameters.
    The usage should be straight forward, however, here you can find an overview over some of the
    most important features. All these tools can be accessed by just clicking onto the corresponding
    menu entry on the very right hand side.

    Information about the different settings can be found in the documentation
    of the Wordpress Wetterturnier Plugin.
    <ul>
        <li>
            <a href="https://github.com/retostauffer/wp-wetterturnier" target="_blank">
                Github repository
            </a>
        </li>
        <li>
            <a href="http://wetterturnier-wordpress-plugin.readthedocs.io/en/latest/" target="_blank">
                Documentation on readthedocs
            </a>
        </li>
    </ul>
</help>


<h2>Settings</h2>
<?php show_help_entries("settings.php"); ?> 

<h2>Scheduler</h2>
<?php show_help_entries("scheduler.php"); ?> 

<h2>Groups</h2>
<?php show_help_entries("groups.php"); ?> 

<h2>Group memberships</h2>
<?php show_help_entries("groupusers.php"); ?> 

<h2>User requests to get member of a group</h2>
<?php show_help_entries("application.php"); ?> 

<h2>Cities</h2>
<?php show_help_entries("cities.php"); ?> 

<h2>Stations</h2>
<?php show_help_entries("stations.php"); ?> 

<h2>Parameter</h2>
<?php show_help_entries("param.php"); ?> 

<h2>Webcams</h2>
<?php show_help_entries("webcams.php"); ?> 

<h2>User bets</h2>
<?php show_help_entries("bets.php"); ?> 

<h2>Observations</h2>
<?php show_help_entries("obs.php"); ?> 























