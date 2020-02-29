<?php
# -------------------------------------------------------------------
# - NAME:        rankarchive.php
# - AUTHOR:      Juri Hubrig
# - DATE:        2014-10-06
# -------------------------------------------------------------------
# - DESCRIPTION: Shows the tournament dates for the active citz.
#                Note: is checking if the current tournament is
#                allready unlocked and the user can see the data.
#                This should happen as soon as the bet-form has been
#                closed.
# -------------------------------------------------------------------
# - EDITORIAL:   2014-09-29, RS: Created file on thinkreto.
# -------------------------------------------------------------------
# - L@ST MODIFIED: 2018-11-14 19:25 on marvin
# -------------------------------------------------------------------
global $wpdb;
global $WTuser;
$args = (object)$args;

$type = "year"

// ------------------------------------------------------------------
// If no city input is set: using current city as default
// In this case we can load the pre-fetched city (active or current city)
// ------------------------------------------------------------------
if ( is_bool($args->city) | $args->city == 'false' ) {
   $cityObj = $WTuser->get_current_cityObj();
} else {
   $cityObj = new wetterturnier_cityObject($args->city);
}

//GET DATA
if ($type == "year") {
   $sql=""  
}

        // Print dates in a ugly way
        if ( empty($data) ) {
            echo "<div class='wetterturnier-info warning'>"
                .__('Sorry, but at the moment there are no'
                   .' tournaments in our archive for the '
                   .' city you have choosen. Please come back '
                   .' later. If you know that there should be '
                   .' archived data you can also contact one '
                   .' of our administrators. Thanks.','wpwt')
                ."</div>";
        } else {

            echo "<form style=\"float: left; padding-right: 3px;\" method=\"POST\" action=\""
                .$this->curPageURL(True)."?limit=15\">\n"
                ."  <input class=\"button\" type=\"submit\" value=\"".__('Show','wpwt')." 15\">\n"
                ."</form>\n";
            echo "<form style=\"float: left; padding-right: 3px;\" method=\"POST\" action=\""
                .$this->curPageURL(True)."?limit=50\">\n"
                ."  <input class=\"button\" type=\"submit\" value=\"".__('Show','wpwt')." 50\">\n"
                ."</form>\n";
            echo "<form style=\"float: left; padding-right: 3px;\" method=\"POST\" action=\""
                .$this->curPageURL(True)."?limit=100\">\n"
                ."  <input class=\"button\" type=\"submit\" value=\"".__('Show','wpwt')." 100\">\n"
                ."</form>\n";
            echo "<form style=\"float: none; padding-right: 3px;\"method=\"POST\" action=\""
                .$this->curPageURL(True)."?limit=all\">\n"
                ."  <input class=\"button\" type=\"submit\" value=\"".__('Show All','wpwt')."\">\n"
                ."</form>\n";
            echo "<br><br>";

            // Create a table to show the data
            $wttable_style = get_user_option("wt_wttable_style");
            $wttable_style = (is_bool($wttable_style) ? "" : $wttable_style);
            echo "<table width=\"100%\" class=\"wttable-archive ".$wttable_style."\">\n"
                ."  <tr>\n"
                ."    <th>".__($type,'wpwt')."</th>\n"
                ."    <th>".__('Players','wpwt')."</th>\n"
                ."    <th>".__('Winner','wpwt')."</th>\n"
                ."  </tr>\n";

            // Width of the points status bar
            $max_width = 300;
            foreach ( $data as $rec ) {

               // Create link to the archive page
               if ( strpos($link, '?') !== false ) {
                   $wt_link = $link.'&tdate='.$rec->tdate;
               } else {
                   $wt_link = $link.'?tdate='.$rec->tdate;
               }

               $user  = $WTuser->get_user_display_class_and_name($rec->userID, $rec);
               $user_name = $WTuser->get_user_profile_link( $user );
            }
?>
