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

// Title of the mosescoefs table
      $title = $cityObj->get('name').": ".__("These are the moses coefs for the weeked around","wpwt")
                 .sprintf(" %s.",$WTuser->date_format($args->tdate));
      // For the overview: smaller title
      $short_title = sprintf("Top %d %s (%s)",$args->limit,$cityObj->get('name'),
                              $WTuser->date_format($args->tdate));
      // Appending link to $short_title
      // Bit freaky. Translation needs to be the permalink to the
      // corresponding language!
      $short_title = sprintf("<a href='%s?wetterturnier_city=%s' target='_self'>%s</a>",
                     __("/ranking/weekend-rankings/","wpwt"),$cityObj->get("hash"),
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

$current = $WTuser->current_tournament(0,false,0,true);

$params = $WTuser->get_param_data();

foreach ( $params as $param ) {
 
      // First day shows header, the rest doesn't
      ?>
         <div class="wt-twocolumn wrapper">
            <div class="wt-twocolumn column-left" style="width: 65%;">
               <?php
               printf("<h3>%s: <b>%s, %s</b><h3>\n",__('Tournament','wpwt'),
                      $current->weekday,$current->readable);
               ?>
            </div>
            <div style="clear: both;" class="wt-twocolumn footer"></div>
         </div>
   <?php
   $WTuser->mosescoefs_show( $current->tdate );
}
   
      // Note: for observations ('obs') there are no points so please
      // always use $points=false on archive_show.
