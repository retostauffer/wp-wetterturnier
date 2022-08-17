<?php
/** The adminclass contains all the functions needed to offer the
 * wordpress admin backend. Or at leas it is planned to put
 * all the functions into this file :).
 */
class wetterturnier_adminclass extends wetterturnier_generalclass
{

   const HOOK = 'wp-wetterturnier';

   // - Setting css directory and an array of all the css
   //   files which should be added to the header of wordpress
   public $css_dir   = 'css';
   public $css_files = array('admin','admin.datepicker');
   public $js_files  = array(); ###'wetterturnier.functions');
   //public $js_files  = array('wetterturnier.adminfunctions');
   public $options = false;

   /** constructor method, loading admin menu items */
   function __construct()
   {
      // registering menu 
      add_action('admin_menu', array($this,'add_admin_menu'));

      // registering necessary css style sheets and js scripts
      add_action('admin_enqueue_scripts',array($this,'register_css_files'));
      add_action('admin_enqueue_scripts',array($this,'register_js_files'));

      // add datepicker script for tournament dates
      add_action('admin_head',array($this,'tournament_datepicker_admin'));
      add_action('wp_ajax_tournament_datepicker_ajax',array($this,'tournament_datepicker_ajax'));
      add_action('wp_ajax_usersearch_ajax',array($this,'usersearch_ajax'));
      add_action('wp_ajax_nopriv_usersearch_ajax',array($this,'usersearch_ajax'));

      // Number and date format. As we do not use language in the admin
      // backend: simply call these functions (will return the defaults
      // as polylang will be loaded later).
      add_action('admin_head',array($this,'load_date_format'));
      add_action('admin_head',array($this,'load_datetime_format'));
      add_action('admin_head',array($this,'load_float_format'));

      // Loading current tournament once
      add_action( 'wp_loaded', array($this,'load_current_tournament_once') );

      // Adding some capabilities
      add_action( 'admin_init', array($this,'add_admin_cap') );

      // Required (wordpress internals) to use datepicker fields
      // in the backend.
      add_action( 'admin_enqueue_scripts', array($this,'enqueue_date_picker') );

      add_action("admin_head",array($this,"disable_display_name_settings"));
      // Loading plugin options
      $this->options = $this->init_options();

   }

   /** Enables the datepicker ui core plugin for the admin
    * interface (to set up forms with date fields and a visual
    * date selector).
    */
   function enqueue_date_picker() {
      wp_enqueue_script(
         'field-date-js', 
         'Field_Date.js', 
         array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'),
         time(),
         true
      );
      wp_enqueue_style( 'jquery-ui-datepicker' );
   } 

   /** hide display name in profiles */
   function disable_display_name_settings() {
      if ( ! current_user_can('manage_options') ) { ?>
      <script>
         jQuery( document ).ready(function() {
            //jQuery("#nickname").prop('disabled', 'disabled');
            //jQuery("#display_name").prop('disabled', 'disabled');
            jQuery("tr.user-nickname-wrap").css('display','none');
            jQuery("tr.user-nickname-wrap").css('display','none');
            jQuery("tr.user-display-name-wrap").css('display','none');
         });
      </script>
      <?php }
   }


   /** Adding capabilities to the administrator role */
   function add_admin_cap() {
      $role = get_role( 'administrator' );
      $role->add_cap( 'wetterturnier_admin' );
   }

   /** If an admin changes either a bet or observation we have to
    * recompute the points if the tournament date is not the current
    * tournament date. If it is the cronjob will do it anyway.
    */
   function set_rerun_request( ) {
      print "Setting rerun request now";
   }

    /** Datepicker for the admin interface */
    function tournament_datepicker_admin() {
        ?>

        <script>
        jQuery(document).on('ready',function() {
            (function($) {
                var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

                // Loop over all the tournament dates and set
                // status classes on datepicker. Note that I am doing
                // this for ALL tournament dates in the db, does not
                // matter what is on screen. Probably not the best way.
                var datepicker_get_dates = function() {
                    // Ajaxing the calculation miniscript
                    var all_dates = false;
                    $.ajax({
                        url: ajaxurl, dataType: 'json', type: 'post', async: false,
                        data: {action:'tournament_datepicker_ajax'},
                        success: function(results) { all_dates = results; }, 
                    });
                    return all_dates;
                }
                var datepicker_set_dates = function(d,all_dates,enabled) {
                    var mydate = $.datepicker.formatDate('yy-mm-dd',d);
                    var arr = [enabled,""];
                    $.each( all_dates, function(key,val) {
                        if ( key == mydate ) { arr = [enabled,"status-"+val]; }
                    });
                    return arr;
                }


                // Loading dates from database
                var all_dates = datepicker_get_dates();
                var dateToday = new Date();
                var dateTomorrow = new Date( dateToday.getTime() + 86400*1000 )

                // Initialize datepicker
                $('#wetterturnier_tournaments').datepicker({
                    firstDay: 1,
                    minDate: dateTomorrow,
                    dateFormat : 'yy-mm-dd', numberOfMonths: 4, showButtonPanel: true, async: false,
                    beforeShowDay: function(d) { return datepicker_set_dates(d,all_dates,true); },
                    onSelect: function(dateText,inst) {
                        var mon  = inst['selectedMonth']; var day  = inst['selectedDay']; var year = inst['selectedYear'];
                        // Ajaxing the calculation miniscript
                        $.ajax({
                            url: ajaxurl, 
                            dataType: 'html',
                            type: 'post',
                            firstDay: 1,
                            showButtonPanel:  false,
                            data: {action:'tournament_datepicker_ajax',date:dateText},
                            success: function(results) {
                                // This was/is to avoid having warning
                                // messages instead of status if the wordpress
                                // debug mode is on. Take only last character.
                                results = results.substr(-1);
                                //$('#wtwidget-result').attr('value',results);
                                var elem = $('td').filter('[data-year="'+year+'"]')
                                                .filter('[data-month="'+mon+'"]')
                                                .find('a')
                                                .filter(function() {
                                                    return parseInt($(this).text()) == parseInt(day);
                                                }).closest('td');
                                $(elem).removeClass('status-1')
                                       .removeClass('status-2')
                                $(elem).addClass('status-' + results);
                                // Reload from database
                                all_dates = datepicker_get_dates();
                            }
                        });
                    }
                });

            })(jQuery);
        });
        </script>

        <?php
    }

   /** adding the admin menus for the Wordpress Wetterturnier plugin */
   public function add_admin_menu() {
      // Adding admin menu pages. Level moderation, position 30 in menu.
      add_menu_page( 'Wetterturnier', 'Wetterturnier', 'wetterturnier_admin', 'wp_wetterturnier',
                       array($this,'admin_show_plugin_info'),'',30);
      //add_menu_page( 'Wetterturnier', 'Wetterturnier', '3', 'wp_wetterturnier','','',30);
      add_submenu_page('wp_wetterturnier',__('Settings','wpwt'),__('Settings','wpwt'),'manage_options',
                       'wp_wetterturnier_admin_settings',array($this,'admin_show_settings'));
      add_submenu_page('wp_wetterturnier',__('Scheduler','wpwt'),__('Scheduler','wpwt'),'manage_options',
                       'wp_wetterturnier_admin_scheduler',array($this,'admin_show_scheduler'));
      add_submenu_page('wp_wetterturnier',__('Deactivate Users','wpwt'),__('Deactivate Users','wpwt'),'wetterturnier_admin',
                       'wp_wetterturnier_admin_deactivateusers',array($this,'admin_show_deactivateusers'));
      add_submenu_page('wp_wetterturnier',__('Groups','wpwt'),__('Groups','wpwt'),'wetterturnier_admin',
                       'wp_wetterturnier_admin_groups',array($this,'admin_show_groups'));
      add_submenu_page('wp_wetterturnier',__('Group Members','wpwt'),__('Group Members','wpwt'),'wetterturnier_admin',
                       'wp_wetterturnier_admin_groupusers',array($this,'admin_show_groupusers'));
      add_submenu_page('wp_wetterturnier',__('Group Application','wpwt'),__('Group Application','wpwt'),'wetterturnier_admin',
                       'wp_wetterturnier_admin_application',array($this,'admin_show_application'));
      add_submenu_page('wp_wetterturnier',__('Cities','wpwt'),__('Cities','wpwt'),'manage_options',
                       'wp_wetterturnier_admin_cities',array($this,'admin_show_cities'));
      add_submenu_page('wp_wetterturnier',__('Stations','wpwt'),__('Stations','wpwt'),'wetterturnier_admin',
                       'wp_wetterturnier_admin_stations',array($this,'admin_show_stations'));
      add_submenu_page('wp_wetterturnier',__('Parameter','wpwt'),__('Parameter','wpwt'),'manage_options',
                       'wp_wetterturnier_admin_param',array($this,'admin_show_param'));
      add_submenu_page('wp_wetterturnier',__('Webcams','wpwt'),__('Webcams','wpwt'),'manage_options',
                       'wp_wetterturnier_admin_webcam',array($this,'admin_show_webcams'));
      add_submenu_page('wp_wetterturnier',__('Bets','wpwt'),__('Bets','wpwt'),'wetterturnier_admin',
                       'wp_wetterturnier_admin_bets',array($this,'admin_show_bets'));
      add_submenu_page('wp_wetterturnier',__('Observations','wpwt'),__('Observations','wpwt'),'wetterturnier_admin',
                       'wp_wetterturnier_admin_obs',array($this,'admin_show_obs'));
      add_submenu_page('wp_wetterturnier','API','API','wetterturnier_admin',
                       'wp_wetterturnier_admin_api',array($this,'admin_show_api'));
      add_submenu_page('wp_wetterturnier',__('Rerun Reqeuests','wpwt'),__('Rerun Requests','wpwt'),'wetterturnier_admin',
                       'wp_wetterturnier_admin_rerunrequests',array($this,'admin_show_rerunrequests'));

   }

   function admin_show_plugin_info()
   { require_once(sprintf("%s/views/plugin_info.php", dirname(__FILE__))); }
   function admin_show_settings()
   { require_once(sprintf("%s/views/settings.php", dirname(__FILE__))); }
   function admin_show_scheduler()
   { require_once(sprintf("%s/views/scheduler.php", dirname(__FILE__))); }
   function admin_show_deactivateusers()
   { require_once(sprintf("%s/views/deactivateusers.php", dirname(__FILE__))); }
   function admin_show_groups()
   { require_once(sprintf("%s/views/groups.php", dirname(__FILE__))); }
   function admin_show_groupusers()
   { require_once(sprintf("%s/views/groupusers.php", dirname(__FILE__))); }
   function admin_show_application()
   { require_once(sprintf("%s/views/application.php", dirname(__FILE__))); }
   function admin_show_cities()
   { require_once(sprintf("%s/views/cities.php", dirname(__FILE__))); }
   function admin_show_stations()
   { require_once(sprintf("%s/views/stations.php", dirname(__FILE__))); }
   function admin_show_param()
   { require_once(sprintf("%s/views/param.php", dirname(__FILE__))); }
   function admin_show_webcams()
   { require_once(sprintf("%s/views/webcams.php", dirname(__FILE__))); }
   function admin_show_bets()
   { require_once(sprintf("%s/views/bets.php", dirname(__FILE__))); }
   function admin_show_obs()
   { require_once(sprintf("%s/views/obs.php", dirname(__FILE__))); }
   function admin_show_api()
   { require_once(sprintf("%s/views/api.php", dirname(__FILE__))); }
   function admin_show_rerunrequests()
   { require_once(sprintf("%s/views/rerunrequests.php", dirname(__FILE__))); }

   /** Add station select
    * $sel is optional. If set it has to be a station ID means
    * that this station is currently selected and should be
    * shown as the selected option for this select box.
    */
   function show_station_select($cityID,$number=false,$sel=false) {

      global $wpdb;

      if ( is_bool($number) ) {

         // Getting unused stations frmo the database 
         if ( is_bool($cityID) ) { $extra = "";                                 }
         else                    { $extra = sprintf("OR cityID = %d",$cityID); }
         $stations = $wpdb->get_results(sprintf("SELECT ID, cityID, wmo, name FROM "
                 ." %swetterturnier_stations WHERE cityID = 0 %s ORDER BY wmo",$wpdb->prefix,$extra));
         // If there are no stations: return info
         if ( count($stations) == 0 ) {
            $res = "Sorry, currently no unused stations in the database. You can add them later if you like.";
         // Else create checkboxes (in a span)
         } else {
            $res = "<br>\n";
            foreach ( $stations as $rec ) {
               if ( $rec->cityID == $cityID && $rec->cityID != 0 ) { $on = "checked"; $off = "";        }
               else                                                { $on = "";        $off = "checked"; }
               $res .= sprintf("<input type=\"radio\" name=\"wmo_station-%d\" value=\"0\" %s> No &nbsp;&nbsp;",$rec->ID,$off);
               $res .= sprintf("<input type=\"radio\" name=\"wmo_station-%d\" value=\"1\" %s> Yes&nbsp;&nbsp;",$rec->ID,$on);
               $res .= sprintf("[%d] %s\n",$rec->wmo,$rec->name);
               if ( $rec->cityID == 0 ) {
                  $res .= " (Not used in any city at the moment)";
               }
               $res .= "<br>\n";
            }
         }

      // WARNING: THIS IS THE OLD MODE. I WONT USE IT ANYMORE BUT
      // AT THE MOMENT IT IS INCLUDED SOMEWHERE SO I HAVE TO TAKE
      // CARE NOT TO KILL THE WHOLE WETTERTURNIER. BUT AS SOON
      // AS I DONT USE INPUT $number AGAIN WE CAN DELETE THIS ONE.
      } else { 

         global $wpdb;

         // Getting ALL stations from the database
         $stations = $wpdb->get_results(sprintf("SELECT ID, wmo, name FROM "
                 ." %swetterturnier_stations WHERE cityID >= 0 ORDER BY wmo",$wpdb->prefix));

         // Getting used stations for this city 
         $used = $wpdb->get_results(sprintf("SELECT ID FROM "
                 ."%swetterturnier_stations WHERE cityID = %d ORDER BY ID",
                 $wpdb->prefix,$cityID));

         // Which one should we select?
         if ( count($used) == 0 )                    { $used = false; }
         else if ( $number == 1 )                    { $used = $used[0]->ID; }
         else if ( $number == 2 & count($used) > 1 ) { $used = $used[1]->ID; }
         else                                        { $used = false; }

         // Draw the select box
         $res  = "<select name=\"wmo_station_".$number."\">\n";
         $res .= "  <option value=\"0\">".__("Nothing selected")."</option>\n";
         foreach ( $stations as $rec ) {
            // Default: item not selected
            $selected = '';

            // If not set $sel and station in use -> skip
            $used = $this->station_is_in_use($rec->wmo);
            // If sel is set, in use but not current station (not sel) skip
            if ( !isset($sel) & $used ) { continue; }
            else if ( $used & $rec->ID != $sel ) { continue; }
            // If $sel is set and this is the selected station, do not continue
            // and set the selected string for the output option below.
            //if ( (! $sel & $this->station_is_in_use($rec->wmo)) | 
            //     (! $sel & ! $this->ID === $sel ) ) { print "\nkick: ".$rec->wmo; continue; }
            if ( $sel === $rec->ID ) { $selected = ' selected'; }
            else                     { $selected = ''; }
            $res .= "  <option value=\"".$rec->ID."\"".$selected.">[".$rec->wmo."] ".$rec->name."</option>\n";
         }
         $res .= "</select>\n";

      }

      print( $res );

   }

   /** Returns true or fals if a wmo station is in use of a city
    * or not.
    */
   function station_is_in_use( $wmo ) {

      global $wpdb;
      $sql = sprintf('SELECT * FROM %swetterturnier_stations WHERE '
                    .'wmo = %d',$wpdb->prefix,$wmo);
      $rec = $wpdb->get_row( $sql );
      if ( $rec->cityID > 0 ) { return(true); }
      return(false);

   }

}











