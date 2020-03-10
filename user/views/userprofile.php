<!-- First remove GRP_ in title for wetterturnier groups -->
<script>
$( document ).ready( function() {
    var entry_title = document.getElementsByClassName("entry-title")[0];
    entry_title.innerHTML = entry_title.innerHTML.replace("GRP_", "");
    var page_title = document.getElementsByTagName("title")[0];
    console.log(page_title);
    document.title = page_title.innerHTML.replace("GRP_", "");
});
</script>

<?php
// Get displayed user id first
$userID = bbp_get_displayed_user_id();
$user   = get_userdata( $userID );
$first_name = get_user_meta($userID,"first_name");
$last_name  = get_user_meta($userID,"last_name");
$user->real_name  = sprintf("%s %s",$first_name[0],$last_name[0]);


// ------------------------------------------------------------------
// Helper function to display the table rows
// ------------------------------------------------------------------
function show_row($key,$value) {
   echo"   <tr>\n"
      ."      <td class='key'>".$key.":</td>\n"
      ."      <td>".$value."</td>\n"
      ."   </tr>\n";
}

// ------------------------------------------------------------------
// Creating city statistics
// ------------------------------------------------------------------
function get_city_stats( $cityID, $userID ) {

   global $WTuser, $wpdb;
   // Do not show results for today - number of bet days - 1 (until Tuesday in wetterturnier)
   // To show only fully finished tournaments in this 'stats'
   
   $tdatebefore    = (int)$WTuser->options->wetterturnier_betdays;
   $tdatebefore    = (int)(time()/86400) - $tdatebefore - 1;
 
   $sql  = "SELECT min(tdate) AS min, max(tdate) AS max, count(tdate) AS count";
   $sql .= sprintf(" FROM %swetterturnier_betstat",$wpdb->prefix);
   $sql .= sprintf(" WHERE userID = %d AND cityID = %d",$userID,$cityID);
   $sql .= sprintf(" AND tdate < %d",$tdatebefore);
   $res = $wpdb->get_row($sql);
   if ( ! $res ) { return(__("No information available","wpwt")); }
   // Converting date
   $first = $WTuser->date_format($res->min);
   $last  = $WTuser->date_format($res->max);

   // Checking if on first three ranks
   /*   
   $sql = array();
   array_push($sql,sprintf("SELECT rank, count(rank) AS count"));
   array_push($sql,sprintf("FROM %swetterturnier_betstat",$wpdb->prefix));
   array_push($sql,sprintf("WHERE cityID = %d AND userID = %d AND rank <= 3",$cityID,$userID));
   array_push($sql,sprintf(" AND tdate < %d",$tdatebefore));
   array_push($sql,sprintf("GROUP BY rank ORDER BY rank"));
   //$rank_res = $wpdb->get_results(join("\n",$sql));
   */
   $sql = "SELECT ranks_weekend AS medals FROM ".$wpdb->prefix."wetterturnier_userstats\n".
          "WHERE userID = ".$userID." AND cityID = ".$cityID;
   $rankhistory = new stdClass();
   $rankhistory->rank_1 = 0;
   $rankhistory->rank_2 = 0;
   $rankhistory->rank_3 = 0;
   /*
   foreach ( $wpdb->get_results(join("\n",$sql)) as $rec ) {
      $tmp = sprintf("rank_%d",$rec->rank);
      $rankhistory->$tmp = $rec->count;
   }
    */
   $ranks = $wpdb->get_row($sql)->medals;
   if ( isset($ranks) ) {
      $ranks = explode(",", $ranks);
      $i=1;
      foreach ( $ranks as $rank ) {
         $tmp = sprintf("rank_%d", $i);
         $rankhistory->$tmp = $rank;
         $i++;
      }
   }

   $tmp=array();
   $measures=array("mean AS pavg", "median AS pmed", "max AS pmax", "min AS pmin", "sd AS pstd");
   foreach ($measures as $measure) {
      $sql  = sprintf("SELECT %s FROM `wp_wetterturnier_userstats` WHERE userID=%d AND cityID=%d", $measure, $userID, $cityID, $tdatebefore);
      array_push($tmp, $wpdb->get_row($sql));
   }
   if (!isset($tmp[0])) {
      $pvag = $pmed = $pmax = $pmin = $pstd = NULL; 
   } else {
      $pavg = $tmp[0]->pavg;
      $pmed = $tmp[1]->pmed;
      $pmax = $tmp[2]->pmax;
      $pmin = $tmp[3]->pmin;
      $pstd = $tmp[4]->pstd;
   }

   // Return string
   if ( $res->count > 0 ) {
      $return = array();
      array_push($return, sprintf("<span class='rankhistory first'>%d</span>",$rankhistory->rank_1));
      array_push($return, sprintf("<span class='rankhistory second'>%d</span>",$rankhistory->rank_2));
      array_push($return, sprintf("<span class='rankhistory third'>%d</span>",$rankhistory->rank_3));
      array_push($return, "<br>");
      array_push($return, sprintf("<b>%d</b> %s %s %s %s %s",
                          $res->count,__("participations","wpwt"),__("between","wpwt"),
                          $first, __("and","wpwt"), $last) );

      if ($pavg !== NULL) {
         array_push($return, sprintf("<table style=\"width:300px;\"><tr><td>".__("Average points","wpwt").":</td><td><b>%s</b></td><tr>", number_format($pavg,1) ) );
         array_push($return, sprintf("<tr><td>".__("Median points","wpwt").":</td><td><b>%s</b></td><tr>", number_format($pmed,1) ) );
         array_push($return, sprintf( "<tr><td>".__("Max points","wpwt").":</td><td><b>%s</b></td><tr>", number_format($pmax,1) ) );
         array_push($return, sprintf( "<tr><td>".__("Min points").":</td><td><b>%s</b></td><tr>", number_format($pmin,1) ) );
         array_push($return, sprintf( "<tr><td>".__("Standard deviation","wpwt").":</td><td><b>%s</b></td><tr></table>", number_format($pstd,1) ) );
      }
      return( join("\n", $return) );

   } else {
      return( __("<span style='color: gray;'>Never participated</span>","wpwt") );
   }
}
?>

<style>
div#bbp-user-profile div.bbp-user-section { display: none; }
table#wt-profile-table, table#wt-profile-table tr, table#wt-profile-table tr td {
   border: none;
}
table#wt-profile-table tr td.key {
   font-weight: bold;
   font-size: 0.8em;
   text-transform: uppercase;
   width: 200px;
}
</style>

<table id='wt-profile-table'>

<?php
// Globalize class
global $WTuser;

// Some user infos
show_row(__("Username","wpwt"),         str_replace('GRP_', '', $user->display_name));

if (strpos($user->real_name==="", 0)) {
    show_row(__("Name","wpwt"), $user->real_name);
} 
show_row(__("Registered since","wpwt"), date(__("d.m.Y","wpwt"), strtotime($user->user_registered)));

// Loading user's current language
$user_lang = $WTuser->get_user_language("slug");

// Try to load user bio/description based on user language
$bio = get_user_meta($userID, sprintf("description_%s", $user_lang), true);

if ( strlen($bio) == 0 ) {
   $bio = get_user_meta($userID, "description",true);
}
if ( strlen($bio) > 0 ) {
    show_row(sprintf("%s",__("Biography","wpwt")), sprintf("<b>%s</b>", $bio)); }

// User roles
$roles = array();
foreach ( $user->wp_capabilities as $key=>$val ) {
    if ( $val ) { array_push($roles, $key); }
}
show_row(__("capabilities","wpwt"), join(", ", $roles));

// show website/url if exists
$user_url = get_user_by( "id", $userID )->user_url;
if ( !empty($user_url) ) {
    show_row( "Website", "<a href='".$user_url."'>".$user_url."</a>" );
}

//type
$usr = $WTuser->get_user_by_ID( $userID );
$userclass = $WTuser->get_user_display_class_and_name($userID, $usr);
show_row(__("Type","wpwt"), $userclass->text);

//group memberships/members
if ( $userclass->userclass === "mitteltip" ) {

    if ( in_array($usr->user_login, array( "GRP_MOS", "GRP_MOS-Max", "GRP_MOS-Min", "GRP_MOS-Random" ) ) ) {
        $group_name = "Automaten";
    } else {
        $group_name = str_replace( "GRP_", "", $usr->user_login );
    }
    
    $members = $WTuser->get_users_in_group( $group_name, NULL, $active=true );
    
    if ($members) {
        $member_names = array();
        foreach ( $members as $m ) {
            $member = $WTuser->get_user_by_ID($m)->user_login;
            if ( ! in_array($member, $member_names) ) {
                array_push($member_names, $member);
            }
        }
        natcasesort($member_names);
        $member_profiles = array();
        foreach ( $member_names as $m ) {
                $usr = $WTuser->get_user_by_username( $m );
                array_push($member_profiles, sprintf("<a href=\"%s\">" . 
                $usr->display_name . "</a>", bbp_get_user_profile_url($usr->ID) ));
        }
        $member_profiles = join(", ", $member_profiles);
    } else {
        $member_profiles = __("No active members","wpwt");
    }
    show_row(__("Active members","wpwt"), $member_profiles );

    $members_inactive = $WTuser->get_users_in_group( $group_name, NULL, $active=false );

    if ($members_inactive) {
        $member_names = array();
        foreach ( $members_inactive as $m ) {
            $member = $WTuser->get_user_by_ID($m)->user_login;
            if ( ! in_array($member, $member_names) ) {
                array_push($member_names, $member);
            }
        }
        natcasesort($member_names);
        $member_profiles = array();
        foreach ( $member_names as $m ) {
                $usr = $WTuser->get_user_by_username( $m );
                array_push($member_profiles, sprintf("<a href=\"%s\">" .
                $usr->display_name . "</a>", bbp_get_user_profile_url($usr->ID) ));
        }
        $member_profiles = join(", ", $member_profiles);
    } else {
        $member_profiles = __("No inactive members","wpwt");
    }
    show_row(__("Inactive members","wpwt"), $member_profiles );


    //check whether group is active or not
    global $wpdb;
    $sql = sprintf("SELECT active AS a FROM wp_wetterturnier_groups WHERE groupName LIKE '%s'",
                    $group_name);
    $active = $wpdb->get_row($sql)->a;
    $status = ($active) ? __("active","wpwt") : __("inactive","wpwt");
    show_row(__("Group status","wpwt"), $status);

//show a user's group memberships
} else {

    $groups = $WTuser->get_groups_for_user( $userID, $active=1 );
    if (!empty($groups)) {
        $group_names = array();
        foreach ( $groups as $g ) {
            $group_userID = $WTuser->get_user_ID( $g->groupName, "group" );
            $group_url = sprintf("<a href=\"%s\">".$g->groupName."</a>",
                bbp_get_user_profile_url($group_userID) );
            if (! in_array($group_url, $group_names)) {
            array_push( $group_names, $group_url );
            }
        }
        show_row(__("Current group memberships","wpwt"), join(", ", $group_names));
    }

    $groups_past = $WTuser->get_groups_for_user( $userID, $active=0 );
    if (!empty($groups_past)) {
        $group_names = array();
        foreach ( $groups_past as $g ) {
            $group_userID = $WTuser->get_user_ID( $g->groupName, "group" );
            $group_url = sprintf("<a href=\"%s\">".$g->groupName."</a>",
                bbp_get_user_profile_url($group_userID) );
            if (! in_array($group_url, $group_names)) {
                array_push( $group_names, $group_url );
            }
        }
        show_row(__("Past group memberships","wpwt"), join(", ", $group_names));
    }
}


// Show statistics for each city
$cities = $WTuser->get_all_cityObj();
foreach ( $cities as $cityObj ) {
    show_row(sprintf("%s", $cityObj->get('name')),
        get_city_stats($cityObj->get('ID'), $userID));
}
?>
</table>
