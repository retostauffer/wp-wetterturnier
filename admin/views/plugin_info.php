<?php
// ------------------------------------------------------------------
// A small function extracting all <help></help> blocks from php files
// ------------------------------------------------------------------
function get_help_entries( $file ) {
   // Getting absolute path of the file
   global $WTadmin;
   $path = parse_url( $WTadmin->plugins_url() ); $path = $path["path"];
   $abspath = realpath(sprintf("%s/%s",get_home_path(),$path));
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

The wetterturnier-plugin is fully implemented into wordpress. It allows all registered admins to 
change the settings, correct bets, correct observations, or add new stations, and parameters.
The usage should be straight forward, however, here you can find an overview over some of the
most important features. All these tools can be accessed by just clicking onto the corresponding
menu entry on the very right hand side.

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


<h1>Shortcodes</h1>

A lot of things in the wetterturnier-plugin are handled via wordpress shortcodes.
Shortcodes are certain strings encapsulated in square brackets which will be replaced
by a certain content. Reason: the wetterturnier plugin supports multi-language support,
and therefore some descriptions are set as page or post content, while others will
be automatically generated. With the shortcodes you can easily compose a e.g., german,
and an english description for a page content, and show some dynamically generated 
content below both (e.g., a ranking table). This is an overview over the shortcodes
available and theyr options.

<h2>Ranking tables</h2>
<div class="shortcode-description">
   <code>[wetterturnier_ranking (options)]</code><br><br>
   This shortcode shows the different ranking tables. As we are offering
   a set of different view styles, and ranking types, there are some extra
   arguments for this shortcode. The following options are available:
   <ul style="list-style: square inside;">
      <li><b>type:</b> string. Default is "weekend". Others can be defined as
         "season", "total", "cities", "yearly".</li>
      <li><b>limit:</b> default false. If set to a positive integer,
         only the best N entries will be shown.</li>
      <li><b>city:</b> default false. If false, the current city will be used.
         Can be set to any defined city ID (integer) to display ranking for a certain city.</li>
      <li><b>cities:</b> default 1,2,3. Comma separated integer list of defined city ID's.
         Is only affecting ranking tables of type="cities".</li>
      <li><b>slim:</b> default false. Can be set to true to hide some of the
         columns.</li>
      <li><b>weeks:</b> default 15. Can be set to any positive integer. Defines
         the number of tournaments to be included for type="total". Only affects
         this type.</li>
      <li><b>hidebuttons:</b> default false. Can be set to true. If true, the navigation
         wont be shown.</li>
   </ul>
   Some examples:<br>
   <code>[wetterturnier_ranking type="weekend" hidebuttons=true]</code><br>
   <code>[wetterturnier_ranking type="season" city=3 limit=10 slim=true ]</code><br>
   <code>[wetterturnier_ranking type="cities" cities=1,3,4 slim=true ]</code><br>
</div>


<h2>Judging forms (testing the rules)</h2>
<div class="shortcode-description">
   <code>[wetterturnier_judgingform (options)]</code><br><br>
   The user can test the operational judgingclass (the class computing
   the points for a given set of observations/bets). Shows a set of html
   forms for the parameters. Via ajax the wetterturnier plugin will call
   the python backend judgingclass, returning the points. A small set of
   options can be set:
   <ul style="list-style: square inside;">
      <li><b>extra:</b> default false. If set to true, an "extra obs" field will be
         shown. This is needed for some parameters where the computation of the
         points is based on a second parameter.</li>
      <li><b>parameter:</b> default false. String, parameter name, like e.g., "TTm", "TTn".</li>
   </ul>
   Some examples:<br>
   <code>[wetterturnier_judgingform parameter="TTm"]</code><br>
   <code>[wetterturnier_judgingform parameter="TTn"]</code><br>
   <code>[wetterturnier_judgingform parameter="dd" extra=true]</code><br>
</div>

<h2>Links to user bbp profile</h2>
<div class="shortcode-description">
   <code>[wetterturnier_profilelink user="..."]</code><br><br>
   user="..." is required. String, specifying the username.
   This command will create a link to the user profile page which is
   actually an extended version of the bbpress profile page (with some
   extra wetterturnier statistics and profile infos).
   <ul style="list-style: square inside;">
      <li><b>user:</b> required, string. Username.</li>
   </ul>
   Some examples:<br>
   <code>[wetterturnier_profilelink user="ww75"]</code><br>
   <code>[wetterturnier_profilelink user="reto"]</code><br>
</div>

<h2>Different views (without options)</h2>
<div class="shortcode-description">
   <code>[wetterturnier_register]</code> Registration form.<br>
   <code>[wetterturnier_synopsymbols]</code> Synop symbol table.<br>
   <code>[wetterturnier_mapsforecasts]</code> Forecast-maps navigation.<br>
   <code>[wetterturnier_archive]</code> The archive (bets and points)<br>
   <code>[wetterturnier_current]</code> Current tournament overview.<br>
   <code>[wetterturnier_groups]</code> A set of group-tables containing names and members.<br>
   <code>[wetterturnier_applygroup]</code> Form where users can apply for a membership for a group.<br>
   <code>[wetterturnier_bet]</code> The bet form.<br>
   <code>[wetterturnier_exportobslive]</code> Small form for exporting latest observations in the live obs table.<br>
   <code>[wetterturnier_exportobsarchive]</code> Small form for exporting archived observations (obs archive table).<br>
   <code>[wetterturnier_statplayer]</code> Small interface to create R-statistics-plots (devel).<br>
   <code>[wetterturnier_obstable]</code> Table containing the latest observations.<br>
   <code>[wetterturnier_meteogram]</code> Meteogram navigation.<br>
   <code>[wc]...[/wc]</code> Shows "..." in code-styling.<br>
   <code>[wetterturnier_stationinfo]</code> Shows a list of all cities and their corresponding stations (used in rules/Spielregeln).<br>
   <code>[wetterturnier_stationparamdisabled]</code> Shows a list of the stations in use have disabled parameters, parameters which are currently excluded from the tournament. Used in rules/Spielregeln.<br>
</div>




























