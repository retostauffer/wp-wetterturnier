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
# - L@ST MODIFIED: 2018-11-02 13:01 on marvin
# -------------------------------------------------------------------

global $wpdb;
global $WTuser;
$args = (object)$args;

// ------------------------------------------------------------------
// If no city input is set: using current city as default
// In this case we can load the pre-fetched city (active or current city)
// ------------------------------------------------------------------
if ( is_bool($args->city) | $args->city == 'false' ) {
   $cityObj = $WTuser->get_current_cityObj();
} else {
   $cityObj = new wetterturnier_cityObject($args->city);
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

// Checking input argument tdate
if ( ! is_null($args->tdate) )        { $tdate = (int)$args->tdate; }
else if ( empty($_REQUEST['tdate']) ) {
   $args->tdate = (int)$this->current_tournament(0,false,0,true)->tdate;
} else { $args->tdate = (int)$_REQUEST['tdate']; }


// ------------------------------------------------------------------
// Depending on the type of ranking which should be shown to the end
// user, we have to prepare a few things.
// ------------------------------------------------------------------
$tdates = (object) array("from"      => Null, "to"      => Null,
                         "from_prev" => Null, "to_prev" => Null,
                         "older"     => Null, "newer"   => Null);
// Latest possible tournament
$tdates->latest = $WTuser->latest_tournament(floor(time() / 86400.))->tdate;
if ( ! $WTuser->scored_players_per_town( $args->tdate ) ) {
    $current = $WTuser->older_tournament( $args->tdate );
    $args->tdate = $current->tdate;
    $tdates->latest = $current->tdate;
}


$short_title = "This should be the <i>short title</i>, but seems to be missing.";
// to prevent errors, show no title if we mess something up later or anything unforeseen happens (not with our forecasts)
$title = NULL;

switch ( $args->type ) {

   // ---------------------------------------------------------------
   // Weekend ranking
   // ---------------------------------------------------------------
   case "weekend":
   case "leading":
      // Title of the ranking table
      $title = $cityObj->get('name').": ".__("This is the weekend ranking for the weekend around","wpwt")
                 .sprintf(" %s.",$WTuser->date_format($args->tdate));
      // For the overview: smaller title
      $short_title = sprintf("%s (%s)", $cityObj->get('name'),
                              $WTuser->date_format($args->tdate));
      // Appending link to $short_title
      // Bit freaky. Translation needs to be the permalink to the
      // corresponding language!
      $short_title = sprintf("<a href='%s?wetterturnier_city=%s' target='_self'>%s</a>",
                     __("/rankings/weekend-rankings/","wpwt"),$cityObj->get("hash"),
                     $short_title);

      // Navigation items 
      $tdates->older = $WTuser->older_tournament($args->tdate)->tdate;
      $tdates->newer = $WTuser->newer_tournament($args->tdate)->tdate;

      // Next tournament is in the future?
      if ( $tdates->newer > $tdates->latest ) {
          $tdates->newer = Null;
      }

      // Define the two time periods for the ranking.
      // integers, days since 1970-01-01.
      // Current rank based on bets "from - to", the previous
      // rank is based on "from_prev - to_prev".
      $tdates->from      = $args->tdate;
      $tdates->to        = $args->tdate;
      $tdates->from_prev = $tdates->older;
      $tdates->to_prev   = $tdates->older;

      break;



   // ---------------------------------------------------------------
   // Cities ranking
   // ---------------------------------------------------------------
   case "cities":
      // City-ranking is for more than one city. Create $city_array first.
      $tmp = explode(",", $args->cities);
      $cityObj = array();
      foreach ( $tmp as $elem ) {
         if (is_numeric($elem)) {
            array_push($cityObj, new wetterturnier_cityObject((int)$elem));
         }
      }
      if ( count($cityObj) == 0 ) {
          printf("<div class=\"wetterturnier-info error\">%s</div>",
              __("Sorry, no proper city definition for","wpwt")
              ." wetterturnier_ranking type cities"); return;
      }
      // Define title
      $names = array(); foreach ( $cityObj as $rec ) { array_push($names, $rec->get("name")); }
      $title = sprintf("%d-%s %s %s %s",count($cityObj),__("City-ranking for the cities ","wpwt"),
                  join(" ".__(" and ","wpwt")." ",
                  array(join(", ",array_slice($names,0,-1)), end($names))),
                  __("for the weekend around","wpwt"),$WTuser->date_format($args->tdate));

      $short_title = sprintf( __("%d cities ranking (%s)","wpwt"), count($cityObj), $WTuser->date_format($args->tdate) );
      $link = sprintf( __("/rankings/%d-city-ranking/","wpwt"), count($cityObj));
      $short_title = sprintf("<a href='%s' target='_self'>%s</a>", $link, $short_title);

      // Navigation items 
      $tdates->older = $WTuser->older_tournament($args->tdate)->tdate;
      $tdates->newer = $WTuser->newer_tournament($args->tdate)->tdate;

      // Next tournament is in the future?
      if ( $tdates->newer > $tdates->latest ) {
          $tdates->newer = Null;
      }

      // Define the two time periods for the ranking.
      // integers, days since 1970-01-01.
      // Current rank based on bets "from - to", the previous
      // rank is based on "from_prev - to_prev".
      $tdates->from      = $args->tdate;
      $tdates->to        = $args->tdate;
      $tdates->from_prev = $tdates->older;
      $tdates->to_prev   = $tdates->older;

      break;

   // TODO: make it slimmer and remove repeating code, perhaps only case "season" is need, but then you have to changed userclass.php and many other stuff as well, plus the whole documentation

   // ---------------------------------------------------------------
   // Season ranking for single cities or a set of cities (e.g.,
   // the three and five city rankings)
   // ---------------------------------------------------------------
   case "season":
      // Compute begin and end tournament date for the season
      $month = (int)$WTuser->date_format($args->tdate, "%m");
      $year  = (int)$WTuser->date_format($args->tdate, "%Y");
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

      // Navigation items 
      $tdates->older    = $WTuser->older_tournament($dates[0])->tdate;
      $tdates->newer    = $WTuser->newer_tournament($dates[1])->tdate;

      // Define the two time periods for the ranking.
      // integers, days since 1970-01-01.
      // Current rank based on bets "from - to", the previous
      // rank is based on "from_prev - to_prev".
      $tdates->from      = $dates[0];
      $tdates->to        = $dates[1];
      $tdates->from_prev = $dates[0];
      $tdates->to_prev   = $WTuser->older_tournament(min($tdates->latest,$dates[1]))->tdate;

      // Hide trend if season lies in the past
      if ( $tdates->to < $WTuser->newer_tournament($tdates->latest)->tdate ) {
          $tdates->from_prev = $tdates->to_prev = Null;
      }

      // If the next is in the future
      if ( $tdates->to > $tdates->latest ) { $tdates->newer = Null; }

      // Title
      $title = sprintf("%s %s %s, %s %s %s %s",
               $season,__("season ranking for","wpwt"),$cityObj->get('name'),
               __("tournaments from","wpwt"),
               $WTuser->date_format($tdates->from),__("to","wpwt"),
               $WTuser->date_format($tdates->to));
      break;


   // ---------------------------------------------------------------
   // Specific settings for "seasoncities"
   // ---------------------------------------------------------------
   case "seasoncities":
      // Compute begin and end tournament date for the season
      $month = (int)$WTuser->date_format($args->tdate, "%m");
      $year  = (int)$WTuser->date_format($args->tdate, "%Y");
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

      // Navigation items 
      $tdates->older    = $WTuser->older_tournament($dates[0])->tdate;
      $tdates->newer    = $WTuser->newer_tournament($dates[1])->tdate;

      // Define the two time periods for the ranking.
      // integers, days since 1970-01-01.
      // Current rank based on bets "from - to", the previous
      // rank is based on "from_prev - to_prev".
      $tdates->from      = $dates[0];
      $tdates->to        = $dates[1];
      $tdates->from_prev = $dates[0];
      $tdates->to_prev   = $WTuser->older_tournament(min($tdates->latest,$dates[1]))->tdate;

      // Hide trend if season has lies in the past
      if ( $tdates->to < $WTuser->newer_tournament($tdates->latest)->tdate ) {
          $tdates->from_prev = $tdates->to_prev = Null;
      }

      // If the next is in the future
      if ( $tdates->to > $tdates->latest ) { $tdates->newer = Null; }

      // City-ranking is for more than one city. Create $city_array first.
      $tmp = explode(",", $args->cities);
      $cityObj = array();
      foreach ( $tmp as $elem ) {
         if (is_numeric($elem)) {
            array_push($cityObj, new wetterturnier_cityObject((int)$elem));
         }
      }
      if ( count($cityObj) == 0 ) {
          printf("<div class=\"wetterturnier-info error\">%s</div>",
              __("Sorry, no proper city definition for","wpwt")
              ." wetterturnier_ranking type cities"); return;
      }

      // Generate the title
      $names = array(); foreach ( $cityObj as $rec ) { array_push($names, $rec->get("name")); }
      $title = sprintf("%s %s %s %d %s<br>\n%s,<br>\n%s %s %s %s", $season,
               __("season ranking for","wpwt"), __("the", "wpwt"),
               count($cityObj), __("cities", "wpwt"),
               join(" ".__(" and ","wpwt")." ",
                 array(join(", ",array_slice($names,0,-1)), end($names))),
               __("tournaments from","wpwt"),
               $WTuser->date_format($tdates->from),__("to","wpwt"),
               $WTuser->date_format($tdates->to));

      break;

   // yearly ranking for multiple cities
   case "yearlycities":
      // Compute begin and end tournament date for the season
      $year = (int)$WTuser->date_format($args->tdate,"%Y");
      $dates = array( round(strtotime(sprintf("%04d-12-31",$year - 1)) / 86400),
                      round(strtotime(sprintf("%04d-01-01",$year)) / 86400),
                      round(strtotime(sprintf("%04d-12-31",$year)) / 86400),
                      round(strtotime(sprintf("%04d-12-31",$year + 1)) / 86400) );

      // Time periods for data aggregation
      $tdates->from      = $dates[1];
      $tdates->to        = $dates[2];

      $tdates->from_prev = $dates[1];
      $tdates->to_prev   = $WTuser->older_tournament($dates[2])->tdate;
      // If the year has past ...
      if ( $tdates->latest > $tdates->to_prev ) {
         $tdates->from_prev = $tdates->to_prev = Null;
      }

      // Navigation items 
      $tdates->older = $dates[0];
      $tdates->newer = $dates[3];

      // Hide 'newer' button if this is the current year.
      if ( (int)date("Y") === $year ) { $tdates->newer = Null; }
      // print_r($tdates->newer);

      // City-ranking is for more than one city. Create $city_array first.
      $tmp = explode(",", $args->cities);
      $cityObj = array();
      foreach ( $tmp as $elem ) {
         if (is_numeric($elem)) {
            array_push($cityObj, new wetterturnier_cityObject((int)$elem));
         }
      }
      if ( count($cityObj) == 0 ) {
          printf("<div class=\"wetterturnier-info error\">%s</div>",
              __("Sorry, no proper city definition for","wpwt")
              ." wetterturnier_ranking type cities"); return;
      }

      // Generate the title
      $names = array(); foreach ( $cityObj as $rec ) { array_push($names, $rec->get("name")); }
      $title = sprintf("%s %s %s %d %s<br>\n%s,<br>\n%s %s %s %s", $year,
               __("yearly ranking for","wpwt"), __("the", "wpwt"),
               count($cityObj), __("cities", "wpwt"),
               join(" ".__(" and ","wpwt")." ",
                 array(join(", ",array_slice($names,0,-1)), end($names))),
               __("tournaments from","wpwt"),
               $WTuser->date_format($tdates->from),__("to","wpwt"),
               $WTuser->date_format($tdates->to));

      break;


   // ---------------------------------------------------------------
   // Yearly ranking
   // ---------------------------------------------------------------
   case "yearly":

      // Compute begin and end tournament date for the season
      $year = (int)$WTuser->date_format($args->tdate,"%Y");
      $dates = array( round(strtotime(sprintf("%04d-12-31",$year - 1)) / 86400),
                      round(strtotime(sprintf("%04d-01-01",$year)) / 86400),
                      round(strtotime(sprintf("%04d-12-31",$year)) / 86400),
                      round(strtotime(sprintf("%04d-12-31",$year + 1)) / 86400) );

      // Time periods for data aggregation
      $tdates->from      = $dates[1];
      $tdates->to        = $dates[2];

      $tdates->from_prev = $dates[1];
      $tdates->to_prev   = $WTuser->older_tournament($dates[2])->tdate;
      // If the year has past ...
      if ( $tdates->latest > $tdates->to_prev ) {
         $tdates->from_prev = $tdates->to_prev = Null;
      }

      // Navigation items 
      $tdates->older = $dates[0];
      $tdates->newer = $dates[3];

      // Hide 'newer' button if this is the current year.
      if ( (int)date("Y") === $year ) { $tdates->newer = Null; }
      // print_r($tdates->newer);

      // Generate the title, using meta-info from the $ranking object
      $title = sprintf("%s %s %s %04d",
               __("Ranking for","wpwt"),$cityObj->get('name'),
               __("for","wpwt"),$year);

      break;

   // ---------------------------------------------------------------
   case "total":
   case "eternal":
      // Need the last $args->weeks tournament weekends for this ranking
      // type. 
      $sql = array();
      array_push($sql,sprintf("SELECT tdate FROM %swetterturnier_betstat", $wpdb->prefix));
      array_push($sql,sprintf("WHERE cityID = %d AND tdate <= %d", $cityObj->get('ID'), $args->tdate));
      if ($args->weeks) {
          
         array_push($sql,sprintf("GROUP BY tdate DESC LIMIT %d", $args->weeks));
         $ranking = (string)$args->weeks . __(" weeks ranking for","wpwt");
   
      } else {
          array_push($sql,sprintf("GROUP BY tdate DESC"));
          
          if ($args->type === "eternal") {
              $ranking = __("Eternal ranking for","wpwt");
          } else {
              $ranking = __("Alltime ranking for","wpwt");
          } 
      }

      $dates = $wpdb->get_results(join(" ",$sql));
      $dates = array(end($dates)->tdate,$args->tdate);

      $tdates->from      = $dates[0];
      $tdates->to        = $dates[1];
      $tdates->from_prev = $dates[0];
      $tdates->to_prev   = $WTuser->older_tournament($dates[1])->tdate;

      if ($args->weeks) {
        # For navigation   
        $tdates->older     = $tdates->to_prev;
        $tdates->newer     = $WTuser->newer_tournament($dates[1])->tdate;
        if ( $tdates->newer > $tdates->latest ) { $tdates->newer = Null; }
      }

      // Loading the data set
      //$ranking = $WTuser->get_ranking_data($cityObj,$dates,$args->limit);
      // Generate the title, using meta-info from the $ranking object
      $title = sprintf("%s %s %s %s %s",
               __("$ranking","wpwt"),$cityObj->get('name'),
               $WTuser->date_format($tdates->from),__("to","wpwt"),
               $WTuser->date_format($tdates->to));

      break;


   // ---------------------------------------------------------------
   // Else there was a problem with the shortcode specification
   // ---------------------------------------------------------------
   default:
      printf("<div class=\"wetterturnier-info error\">%s</div>",
          __("Sorry, cannot understand input 'type' for","wpwt")
            ." wetterturnier_ranking shortcode.");
      return;

}


// URL for navigation
$hrefurl = $WTuser->curPageURL(true);


// Append date range to $arg's object
$args->tdates = $tdates;

if ( ! $args->hidebuttons & $args->header ) { ?>
   <div class="wt-twocolumn wrapper">
      <div class="wt-twocolumn column-left" style="width: 65%;">
         <?php
         // Show title
         if ( $args->header ) {
             printf("<h3>%s</h3><br>\n", $title);
         } else if ( is_numeric($args->limit) ) {
             printf("<h3 class=\"wt-table-title\">%s</h3>\n",$short_title);
         }
         ?>

         <div style="min-height: 30px;">
            <?php if ( isset($tdates->older) ) { ?>
                <form style="float: left; padding-right: 3px;" method="post" action="<?php printf("%s?tdate=%d", $hrefurl, $tdates->older); ?>">
                <input class="button" type="submit" value="<< <?php _e("older","wpwt"); ?>" />
                </form>
            <?php } ?>
            <?php if ( isset($tdates->newer) ) { ?>
                <form style="float: left; padding-left: 3px;" method="post" action="<?php printf("%s?tdate=%d", $hrefurl, $tdates->newer); ?>">
                <input class="button" type="submit" value="<?php _e("newer","wpwt"); ?> >>" />
                </form>
            <?php } else if ( $args->type === "eternal" ) { ?>
            <input class="button" type="submit" id="scroll-down" value="<?php _e(">> Explain columns <<","wpwt"); ?>" onClick="document.getElementById('wt-ranking-container-end').scrollIntoView();" />
            <?php } ?>
         </div>
      </div>
      <?php if ( $args->legend === true ) { ?>
         <div class="wt-twocolumn column-right colorlegend-wrapper" style="width: 33%;">
            <?php $WTuser->archive_show_colorlegend(); ?>
         </div>
      <?php } ?>
      <div style="clear: both;" class="wt-twocolumn footer"></div>
   </div>
   <br>
<?php } else {
   // Show title
   if ( $args->header )
   { printf("<h3>%s</h3><br>\n",$title); 
      if ( $args->legend === true ) { ?>
         <div class="wt-twocolumn column-right colorlegend-wrapper" style="width: 33%;">
            <?php $WTuser->archive_show_colorlegend(); ?>
         </div>
      <?php }
   } else if ( is_numeric($args->limit) ) {
       printf("<h3 class=\"wt-table-title\">%s</h3>\n", $short_title);
   }
}

# Random container ID
$containerID = $WTuser->random_string(10, "wt-ranking-container");
?>
<!--
<b>Args:&nbsp;</b><?php print htmlspecialchars(json_encode($args)); ?><br><br>
-->
<div id="<?php print $WTuser->random_string(10, "wt-ranking-container"); ?>"
     class="wt-ranking-container"
     args="<?php print htmlspecialchars(json_encode($args)); ?>">
<?php _e("Loading data ...", "wpwt"); ?>
</div>
<?php if ( $args->type === "eternal" ) { ?>
<div id="wt-ranking-container-end">
<input class="button" type="submit" id="scroll-up" value="<?php _e("^ scroll up ^","wpwt"); ?>" onClick="document.getElementById('page').scrollIntoView();" />
</div>
<?php } ?>
<?php
// Print dates in a ugly way
$today = (int)(time()/86400);
?>

   <?php
   ///// TODO the maximum number of points should not be static here!
   ///// but they seem to be deliverd by the ranking object?
   ///if ( $args->header ) {
   ///   printf("%s <b>%s</b>.",__("The maximum score (total) for the ranking is","wpwt"),
   ///                 $this->number_format(200));
   ///}

   // Get custom table styling
   $wttable_style = get_user_option("wt_wttable_style");
   $wttable_style = (is_bool($wttable_style) ? "" : $wttable_style);
?>
