<?php
# -------------------------------------------------------------------
# - NAME:        ranking.php
# - AUTHOR:      Reto Stauffer
# - DATE:        2014-11-10
# -------------------------------------------------------------------
# - DESCRIPTION: There is a shortcode called [wetterturnier_ranking]
#                with several options to display ranking tables.
#                All will call this file. Input arguments specified
#                by the user (on shortcode) will be set on $args.
#                Depending on the input, the I/O will be prepared.
# -------------------------------------------------------------------
# - EDITORIAL:   2014-11-10, RS: Created file on thinkreto.
# -------------------------------------------------------------------
# - L@ST MODIFIED: 2017-12-20 18:22 on thinkreto
# -------------------------------------------------------------------

global $wpdb;
global $WTuser;
$args = (object)$args;

// ------------------------------------------------------------------
// If no city input is set: using current city as default
//   In this case we can load the pre-fetched city (active or current city)
// ------------------------------------------------------------------
if ( is_bool($args->city) | $args->city == 'false' ) {
   $cityObj = $WTuser->get_current_cityObj();
} else {
   $cityObj = new wetterturnier_cityObject( $args->city );
}

// ------------------------------------------------------------------
// Loading userID for the Sleepy player (to compute points
// for players without a bet).
// ------------------------------------------------------------------
$sleepy = $WTuser->get_user_by_username('Sleepy');
if ( ! $sleepy ) { echo('Could not find userID for Sleepy! Stop! Error!'); return; }

// ------------------------------------------------------------------
// Getting "date" information if nothing is given
// ------------------------------------------------------------------
if ( ! is_null($args->tdate) )        { $tdate = (int)$args->tdate; }
else if ( empty($_REQUEST['tdate']) ) {
   //$tdate = (int)$WTuser->latest_tournament(floor(date('U')/86400))->tdate;
   $tdate = (int)$this->current_tournament(0,false,0,true)->tdate;
}
else                                  { $tdate = (int)$_REQUEST['tdate']; }

// ------------------------------------------------------------------
// Depending on the type of ranking which should be shown to the end
// user, we have to prepare a few things.
// ------------------------------------------------------------------
switch ( $args->type ) {

   // ---------------------------------------------------------------
   // Weekend ranking
   // ---------------------------------------------------------------
   case "weekend":
      // Title of the ranking table
      $title = $cityObj->get('name').": ".__("This is the weekend ranking for the weekend around","wpwt")
                 .sprintf(" %s.",$WTuser->date_format( $tdate ));
      // For the overview: smaller title
      $short_title = sprintf("Top %d %s (%s)",$args->limit,$cityObj->get('name'),
                              $WTuser->date_format( $tdate ));
      // Appending link to $short_title
      // Bit freaky. Translation needs to be the permalink to the
      // corresponding language!
      $short_title = sprintf("<a href='%s?wetterturnier_city=%s' target='_self'>%s</a>",
                     __("/ranking/weekend-rankings/","wpwt"),$cityObj->get("hash"),
                     $short_title);

      // Navigation items 
      $older = $WTuser->older_tournament((int)$tdate);
      $newer = $WTuser->newer_tournament((int)$tdate);
      // Loading the data set 
      $ranking = $WTuser->get_ranking_data($cityObj,$tdate,$args->limit);

      break;

   // ---------------------------------------------------------------
   // Cities ranking
   // ---------------------------------------------------------------
   case "cities":
      // City-ranking is for more than one city. Create $city_array first.
      $tmp = explode(",",$args->cities);
      $city_array = array(); $city_names = array();
      foreach ( $tmp as $elem ) {
         if (is_numeric($elem)) {
            array_push($city_array,new wetterturnier_cityObject((int)$elem));
            array_push($city_names,end($city_array)->get('name'));
         }
      }
      if ( count($city_array) == 0 ) {
         print __("Sorry, no proper city definition for","wpwt")
              ." wetterturnier_ranking type cities"; return;
      }
      // Define title
      $title = sprintf("%d-%s %s %s %s",count($city_array),__("City-ranking for the cities ","wpwt"),
                  join(" ".__(" and ","wpwt")." ",
                  array(join(", ",array_slice($city_names,0,-1)),end($city_names))),
                  __("for the weekend around","wpwt"),$WTuser->date_format($tdate));
      // For the overview: smaller title
      $short_title = sprintf("Top %d for %d city ranking %s (%s)",$args->limit, count($city_array),
                  join(" ".__(" and ","wpwt")." ",
                  array(join(", ",array_slice($city_names,0,-1)),end($city_names))),
                  $WTuser->date_format($tdate));
      // Appending link to $short_title
      // Bit freaky. Translation needs to be the permalink to the
      // corresponding language!
      if ( count($city_array) === 3 ) {
         $permalink = __("/ranking/3-city-ranking/","wpwt");
      } else if ( count($city_array) === 5 ) {
         $permalink = __("/ranking/5-city-ranking/","wpwt");
      } else {
         $permalink = false;
      }
      if ( ! is_bool($permalink) ) {
         $short_title = sprintf("<a href='%s' target='_self'>%s</a>",
                        $permalink, $short_title);
      }

      // Navigation items 
      $older = $WTuser->older_tournament((int)$tdate);
      $newer = $WTuser->newer_tournament((int)$tdate);

      // Loading the data set
      $ranking = $WTuser->get_ranking_data($city_array,(int)$tdate,$args->limit);
      break;

   // ---------------------------------------------------------------
   // Season ranking
   // ---------------------------------------------------------------
   case "season":
      // Compute begin and end tournament date for the season
      $month = (int)$WTuser->date_format($tdate,"%m");
      $year  = (int)$WTuser->date_format($tdate,"%Y");
      if ( in_array($month,array(1,2)) ) {
         $season = __("Winter","wpwt");
         $dates = array( round(strtotime(sprintf("%04d-12-01",$year-1))/86400),
                         round(strtotime(sprintf("%04d-03-01",$year))/86400)-1  );
      } else if ( in_array($month,array(3,4,5)) ) {
         $season = __("Spring","wpwt");
         $dates = array( round(strtotime(sprintf("%04d-03-01",$year))/86400),
                         round(strtotime(sprintf("%04d-06-01",$year))/86400)-1  );
      } else if ( in_array($month,array(6,7,8)) ) {
         $season = __("Summer","wpwt");
         $dates = array( round(strtotime(sprintf("%04d-06-01",$year))/86400),
                         round(strtotime(sprintf("%04d-09-01",$year))/86400)-1  );
      } else if ( in_array($month,array(9,10,11)) ) {
         $season = __("Fall","wpwt");
         $dates = array( round(strtotime(sprintf("%04d-09-01",$year))/86400),
                         round(strtotime(sprintf("%04d-12-01",$year))/86400)-1  );
      } else { // Month is 12
         $season = __("Winter","wpwt");
         $dates = array( round(strtotime(sprintf("%04d-12-01",$year))/86400),
                         round(strtotime(sprintf("%04d-03-01",$year+1))/86400)-1  );
      }
      // Loading the data set
      $ranking = $WTuser->get_ranking_data($cityObj,$dates,$args->limit);
      // Generate the title, using meta-info from the $ranking object
      $title = sprintf("%s %s %s %s %d %s %s %s %s",
               $season,__("season ranking for","wpwt"),$cityObj->get('name'),
               __("including","wpwt"),$ranking->tdate_count,__("tournaments from","wpwt"),
               $WTuser->date_format($ranking->tdate_first),__("to","wpwt"),
               $WTuser->date_format($ranking->tdate_last ));
      // Navigation items 
      $older = $WTuser->older_tournament($dates[0]);
      $newer = $WTuser->newer_tournament($dates[1]);

      break;

   // ---------------------------------------------------------------
   // Yearly ranking
   // ---------------------------------------------------------------
   case "yearly":
      // Compute begin and end tournament date for the season
      $year = (int)$WTuser->date_format($tdate,"%Y");
      $dates = array( round(strtotime(sprintf("%04d-01-01",$year))/86400),
                      round(strtotime(sprintf("%04d-12-31",$year))/86400) );
      // Loading the data set
      $ranking = $WTuser->get_ranking_data($cityObj,$dates,$args->limit);
      // Generate the title, using meta-info from the $ranking object
      $title = sprintf("%s %s %s %04d %s %d %s",
               __("Ranking for","wpwt"),$cityObj->get('name'),
               __("for","wpwt"),$year,
               __("including","wpwt"),$ranking->tdate_count,__("tournaments","wpwt"));
      // Navigation items 
      $older = $WTuser->older_tournament($dates[0]);
      $newer = $WTuser->newer_tournament($dates[1]);

      break;

   // ---------------------------------------------------------------
   case "total":
      // Need the last $args->weeks tournament weekends for this ranking
      // type. 
      $sql = array();
      array_push($sql,sprintf("SELECT tdate FROM %swetterturnier_betstat",$wpdb->prefix));
      array_push($sql,sprintf("WHERE cityID = %d AND tdate <= %d",$cityObj->get('ID'),$tdate));
      array_push($sql,sprintf("GROUP BY tdate DESC LIMIT %d",$args->weeks));
      $dates = $wpdb->get_results(join(" ",$sql));
      $dates = array(end($dates)->tdate,$tdate);
      // Loading the data set
      $ranking = $WTuser->get_ranking_data($cityObj,$dates,$args->limit);
      // Generate the title, using meta-info from the $ranking object
      $title = sprintf("%s %s %s %d %s %s %s %s",
               __("Total ranking for","wpwt"),$cityObj->get('name'),
               __("including","wpwt"),$ranking->tdate_count,__("tournaments from","wpwt"),
               $WTuser->date_format($ranking->tdate_first),__("to","wpwt"),
               $WTuser->date_format($ranking->tdate_last));
      // Navigation items 
      $older = $WTuser->older_tournament($tdate);
      $newer = $WTuser->newer_tournament($tdate);

      break;

   // ---------------------------------------------------------------
   // Else there was a problem with the shortcode specification
   // ---------------------------------------------------------------
   default:
      print __("Sorry, cannot understand input 'type' for","wpwt")." wetterturnier_ranking shortcode.";
      return;
}




// -------------------------------------------------------------------
// Navigation 
// If the newer tournament is newer than the current date
// kill the $newer stdObject. This hides the 'next' button. 
if ( $newer->tdate > (int)floor(gmdate('U')/86400) ) {
   $newer = false;
}
$aurl = explode('?', $_SERVER['REQUEST_URI'], 2);
$aurl = 'http://'.$_SERVER['HTTP_HOST'].$aurl[0];
if ( ! $args->hidebuttons & $args->header ) { ?>
   <div class="wt-twocolumn wrapper">
      <div class="wt-twocolumn column-left" style="width: 65%;">
         <?php
         // Show title
         if ( $args->header )
         { printf("<h3>%s</h3><br>\n",$title); }
         else if ( ($args->type === "weekend" | $args->type === "cities") & is_numeric($args->limit) )
         { printf("<h3 class=\"wt-table-title\">%s</h3>\n",$short_title); }
         ?>
         <div style="min-height: 30px;">
            <?php if ( is_object($older) ) { ?>
            <form style='float: left; padding-right: 3px;' method='post' action='<?php echo $aurl.'?tdate='.$older->tdate; ?>'>
                <input class="button" type="submit" value="<< <?php _e("older","wpwt"); ?>" />
            </form>
            <?php } ?>
            <?php if ( is_object($newer) ) { ?>
            <form style='float: left; padding-left: 3px;' method='post' action='<?php echo $aurl.'?tdate='.$newer->tdate; ?>'>
                <input class="button" type="submit" value="<?php _e("newer","wpwt"); ?> >>" />
            </form>
            <?php } ?>
         </div>
      </div>
      <div class="wt-twocolumn column-right colorlegend-wrapper" style="width: 33%;">
         <?php $WTuser->archive_show_colorlegend(); ?>
      </div>
      <div style="clear: both;" class="wt-twocolumn footer"></div>
   </div>
   <br>
<?php } else {
   // Show title
   if ( $args->header )
   { printf("<h3>%s</h3><br>\n",$title); }
   else if ( ($args->type === "weekend" | $args->type === "cities") & is_numeric($args->limit) )
   { printf("<h3 class=\"wt-table-title\">%s</h3>\n",$short_title); }
}
// -------------------------------------------------------------------

// Print dates in a ugly way
$today = (int)(time()/86400);
if ( empty($ranking->data) && ($today-$tdate) <= 1 ) {
    echo "<br><div class='wetterturnier-info ok'>"
        .__("Ranking can't be shown yet. The reason: "
           ."this is the weekend ranking of the ongoing "
           ."tournament and as we don't have any observed " 
           ."parameters, we can't compute points, or ranks, "
           ."at the moment. As soon as we can judge the "
           ."parameter you can access the live-ranking here. "
           ."Thank you for your understanding.","wpwt")
        ."</div>";    
} else if ( empty($ranking->data) ) {
    echo "<br><div class='wetterturnier-info warning'>"
        .__('Sorry, but we do have a problem computing the ' 
           .' ranking you\'ll have. If the problem exists '
           .' for a longer time period please inform '
           .' one of our administrators. Please note that '
           .' the message also shows up if this is the '
           .' ongoing tournament. Thank you.','wpwt')
        ."</div>";    
} else {

   ?>
   <script type="text/javascript">
   jQuery(document).on("ready",function(){
      (function($) {
         // Admin url for ajax requests
         $.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
      })(jQuery);
   });
   </script>

   <?php
   if ( $args->header ) {
      printf("%s <b>%s</b>.",__("The maximum score (total) for the ranking is","wpwt"),
                    $this->number_format($ranking->maxpoints,0));
   }

   // Get custom table styling
   $wttable_style = get_user_option("wt_wttable_style");
   $wttable_style = (is_bool($wttable_style) ? "" : $wttable_style);


   // Create a table to show the data
   $max_width = 200;
   $thwidth = sprintf(" style=\"width: %dpx\"",(int)($max_width+10));
   $align = " style=\"text-align: right;\"";
   echo "<table class=\"wttable-show-ranking wttable-show small ranking-".$args->type." ".$wttable_style."\" width=\"100%\">\n"
       ."  <tr>\n"
       ."    <th class=\"rank\">".__('Rank','wpwt')."</th>\n"
       ."    <th class=\"played\">T</th>\n"
       ."    <th class=\"user\">".__('Player','wpwt')."</th>\n";
   if ( ! $args->slim ) {
      echo "    <th class=\"points\">".__('Saturday','wpwt')."</th>\n"
          ."    <th class=\"points\">".__('Sunday','wpwt')."</th>\n"
          ."    <th class=\"points\">".__('Total','wpwt')."</th>\n";
   } else {
      echo "    <th class=\"points\">".__('Points','wpwt')."</th>\n";
   }
   // Extra columns if not slim
   if ( ! $args->slim ) {
       echo "    <th class=\"points difference\">".__('Difference','wpwt')."</th>\n"
           ."    <th class=\"\"".$thwidth.">".__('Status','wpwt')."</th>\n"
           ."  </tr>\n";
   }

   // Width of the points status bar
   $show_sleepy_note = False;
   $points_hold = 99999; $rank = 0; $hidden_rank = 0;
   $points_leader = $ranking->data[0]->points;
   foreach ( $ranking->data as $rec ) {

      // Increase Rank if necessary
      if ( $rec->points < $points_hold ) {
          $points_hold = $rec->points; $hidden_rank++; $rank = $hidden_rank;
      } else { $hidden_rank++; }

      // Computes the difference
      $rec->difference = $rec->points - $points_leader;
       
      // Total number of points reachable in the ranking 
      // of a weekend (200) times number of weeks and
      // number of cities in the ranking. As an example:
      // If you compute the 'total ranking' for '3 towns'
      // total number to reach is 200*15*3 or in code
      // Create the status bar
      $pc = $this->number_format( (float)$rec->points / $ranking->maxpoints * 100., 1 )."%";
      if ( $pc > 50. ) { $pc1 = $pc.'&nbsp;'; $pc2 = ''; }
      else             { $pc2 = $pc; $pc1 = ''; }
      $w1 = max(0,(int)floor((float)$rec->points / $ranking->maxpoints * (float)$max_width)); # last number is max width 
      $sbar  = "<span class='ranking-statusbar' style='width: ".$max_width."px;'>\n"
              ."  <span style='width: ".$w1."px;'>".$pc1."</span>".$pc2."\n"
              ."</span>\n";

      // Returns class for the user and also
      // manipulates the username (wp_users.display_name) if necessary.
      $rec_tmp = $WTuser->get_user_display_class_and_name($rec->userID, $rec);

      // Generate link to show user details
      if ( $args->type === "weekend" ) {
         $user_details = sprintf("<span class='button small detail' userID='%d' cityID='%s' tdate='%d'>"
                                ."</span>",(int)$rec->userID,$cityObj->get('ID'),(int)$tdate);
      } else { $user_details = ""; }

      // Show edit button if logged in
      if ( $args->type === "weekend" ) {
         $edit_button = $WTuser->create_edit_button( $rec_tmp->userclass, $cityObj, (int)$rec->userID, $tdate );
      } else { $edit_button = ""; }

      // Show profile link (if not mitteltip/Gruppe)
      if ( $rec_tmp->userclass == "mitteltip" ) {
         $user_name = $rec->display_name;
         $user_name = $WTuser->get_user_display_class_and_name( $rec->userID, $rec )->display_name;
      } else {
         $user_name = $WTuser->get_user_profile_link( $rec ); //->user_login );
      }

      // Show an asteriks if not played all tournaments to indicate
      // that Saturdau and Sunday Points do NOT SUM UP to total points
      // as sleepy is used to fillup!
      if ( $rec->played < $ranking->tdate_count ) {
         $show_sleepy_note = True;
         $sleepy_marker    = "<span class='wttable-show-sleepymarker'>*</span>";
      } else { $sleepy_marker = ""; } 
      // Show the data
      echo "  <tr class='".$rec_tmp->userclass."' userid='".$rec->userID."'>\n"
          ."    <td class=\"rank ".$rec_tmp->userclass."\">".$rank."</td>\n"
          ."    <td class=\"played\">".$rec->played."/".$ranking->tdate_count."</td>\n"
          ."    <td class=\"user\">".$edit_button.$user_details.$user_name."</td>\n";
      if ( ! $args->slim ) {
         if ( $rec->userID == 1130 ) {
            echo "    <td class=\"points\">---</td>\n"
                ."    <td class=\"points\">---</td>\n";
         } else {
            echo "    <td class=\"points\">".$sleepy_marker.$this->number_format($rec->points_d1,1)."</td>\n"
                ."    <td class=\"points\">".$sleepy_marker.$this->number_format($rec->points_d2,1)."</td>\n";
         }
      }
      echo "    <td class=\"points\">".$this->number_format($rec->points,1)."</td>\n";
      if ( ! $args->slim ) {
          echo "    <td class=\"points difference\">".$this->number_format($rec->difference,1)."</td>\n"
              ."    <td>".$sbar."</td>\n  </tr>\n";
      }
   
   }
   // End table
   echo "</table>\n";

   // Show sleepy note
   if ( $show_sleepy_note ) {
      printf("<span class='wttable-show-sleepymarker'>*</span> %s",
             __("Points marked with a blue asterisk indicate are the points the players got but they do not sum up to the total points. Reason: they have not played all tournaments. For all tournaments they did not participate they get the \"Sleepy\" points, but these points do only exist for the whole weekend but not for the individual days. Therefore the \"total points\" are comparable across all playres, the individual points only for those having the same number of participations.","wpwt"));
   }

}

?>
