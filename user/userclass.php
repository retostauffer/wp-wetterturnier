<?php
/** The userclass contains all the necessary functions for the
 * frontend pages shown when visiting the wordpress page.
 */
class wetterturnier_userclass extends wetterturnier_generalclass
{

    const HOOK = 'wp-wetterturnier';
    public $cities = false; # default value

    // - Setting css directory and an array of all the css
    //   files which should be added to the header of wordpress
    public $css_dir   = 'css';
    public $css_files = array('tablesorter',
                              'style',
                              'featherlight',
                              'user.datepicker',
                              'tooltipster');

    // - The same happens to the js files.
    public $js_dir    = 'js';
    public $js_files  = array("jquery.tablesorter",
                              "jquery.tablesorter.widgets",
                              "wetterturnier.bets",
                              "wetterturnier.judgingform",
                              "wetterturnier.registrationform",
                              "wetterturnier.rankingtable",
                              "jquery.featherlight-1.5.0.min",
                              "jquery.tooltipster.min");

    // To store plugin options
    public $options = false;

    /// Attribute which will be set to true if user is logged in
    /// and admin (current_user_can('manage_options')).
    private $is_admin = false;

    /** constructor method  */
    function __construct()
    {

        // Start session
        if ( ! session_id() ) { session_start(); } 

        // Loading plugin options
        $this->options = $this->init_options();

        // Loading cities from database.
        call_user_func(array($this,'initialize_cities_menu'));
        // Everytime!
        call_user_func(array($this,'city_check'));

        //unused//add_action('pll_language_defined', array($this,'load_locale'));
        add_action('pll_language_defined', array($this,'load_float_format'));
        add_action('pll_language_defined', array($this,'load_date_format'));
        add_action('pll_language_defined', array($this,'load_datetime_format'));
        add_action('pll_language_defined', array($this,'set_locale') ); 

        // Add css files to head
        add_action('wp_enqueue_scripts',array($this,'register_css_files'));
        // Add js files to head
        add_action('wp_enqueue_scripts',array($this,'register_js_files'));

        // Adding the tooltips for the bet-parameters to the head of wordpress
        add_action('wp_head',array($this,'wetterturnier_add_tooltip_js'));

        // add datepicker script for tournament dates
        add_action('wp_ajax_nopriv_tournament_datepicker_ajax',array(&$this,'tournament_datepicker_ajax'));
        // apply for group. Only for logged in users 
        // and therefore nopriv is not needed.
        add_action('wp_ajax_applygroup_ajax',array($this,'applygroup_ajax'));

        // User-name-search
        add_action('wp_ajax_usersearch_ajax',array($this,'usersearch_ajax'));
        add_action('wp_ajax_nopriv_usersearch_ajax',array($this,'usersearch_ajax'));

        // Returns latest observations from obs.synop database 
        add_action('wp_ajax_getobservations_ajax',array($this,'getobservations_ajax'));
        add_action('wp_ajax_nopriv_getobservations_ajax',array($this,'getobservations_ajax'));

        // Calling Rscript 
        add_action('wp_ajax_wttable_show_details_ajax',array($this,'wttable_show_details_ajax'));
        add_action('wp_ajax_nopriv_wttable_show_details_ajax',array($this,'wttable_show_details_ajax'));

        // Calling Rscript 
        add_action('wp_ajax_callRscript_ajax',array($this,'callRscript_ajax'));
        add_action('wp_ajax_nopriv_callRscript_ajax',array($this,'callRscript_ajax'));

        // Calling the python judgingclass 
        add_action('wp_ajax_judging_ajax',array($this,'judging_ajax'));
        add_action('wp_ajax_nopriv_judging_ajax',array($this,'judging_ajax'));

        // Interfaces the rankingclass to return a json array containing
        // the ranking table.
        add_action('wp_ajax_ranking_ajax',array($this,'ranking_ajax'));
        add_action('wp_ajax_nopriv_ranking_ajax',array($this,'ranking_ajax'));

        // Adding the needed shortcodes
        add_shortcode( 'wetterturnier_linkcollection',   array($this,'shortcode_wetterturnier_linkcollection') );
        add_shortcode( 'wetterturnier_profilelink',      array($this,'shortcode_wetterturnier_profilelink') );
        add_shortcode( 'wetterturnier_register',         array($this,'shortcode_wetterturnier_register')    );
        add_shortcode( 'wetterturnier_current',          array($this,'shortcode_wetterturnier_current')     );
        add_shortcode( 'wetterturnier_judgingform',      array($this,'shortcode_wetterturnier_judgingform') );

        add_shortcode( 'wetterturnier_mapsforecasts',    array($this,'shortcode_wetterturnier_mapsforecasts') );
        add_shortcode( 'wetterturnier_mapsanalysis',     array($this,'shortcode_wetterturnier_mapsanalysis')  );
        add_shortcode( 'wetterturnier_soundingsmorten',  array($this,'shortcode_wetterturnier_soundingsmorten')  );
        add_shortcode( 'wetterturnier_cosmomorten',      array($this,'shortcode_wetterturnier_cosmomorten')  );
        add_shortcode( 'wetterturnier_iconepsmorten',    array($this,'shortcode_wetterturnier_iconepsmorten')  );
        add_shortcode( 'wetterturnier_synopsymbols',     array($this,'shortcode_wetterturnier_synopsymbols')  );
        add_shortcode( 'wetterturnier_archive',          array($this,'shortcode_wetterturnier_archive')       );
        add_shortcode( 'wetterturnier_bet',              array($this,'shortcode_wetterturnier_bet')           );
        add_shortcode( 'wetterturnier_applygroup',       array($this,'shortcode_wetterturnier_applygroup')    );
        add_shortcode( 'wetterturnier_groups',           array($this,'shortcode_wetterturnier_groups')        );

        add_shortcode( 'wetterturnier_ranking',          array($this,'shortcode_wetterturnier_ranking')   );
        //add_shortcode( 'wetterturnier_leaderboard',      array($this,'shortcode_wetterturnier_leaderboard')   );
        add_shortcode( 'wetterturnier_exportobsarchive', array($this,'shortcode_wetterturnier_exportobsarchive'));
        add_shortcode( 'wetterturnier_exportobslive',    array($this,'shortcode_wetterturnier_exportobslive')   );

        add_shortcode( 'wetterturnier_googlecharts',       array($this,'shortcode_wetterturnier_googlecharts'));
        add_shortcode( 'wetterturnier_obsimages',        array($this,'shortcode_wetterturnier_obsimages') );
        add_shortcode( 'wetterturnier_obstable',         array($this,'shortcode_wetterturnier_obstable')  );
        add_shortcode( 'wetterturnier_meteogram',        array($this,'shortcode_wetterturnier_meteogram') );
        add_shortcode( 'wetterturnier_meteogramdata',    array($this,'shortcode_wetterturnier_meteogramdata') );
        add_shortcode( 'wetterturnier_mosforecasts',     array($this,'shortcode_wetterturnier_mosforecasts') );
        add_shortcode( 'wetterturnier_stationinfo',      array($this,'shortcode_wetterturnier_stationinfo') );
        add_shortcode( 'wetterturnier_stationparamdisabled', array($this,'shortcode_wetterturnier_stationparamdisabled') );

// wetterturnier_moses

        add_shortcode( 'wc', array($this,'shortcode_wtcode') );

        add_action('register_form',array($this,'wetterturnier_registration_form'));

        add_action('bbp_template_after_user_profile', array($this,'show_wetterturnier_user_profile'));

        add_action( 'wp_loaded', array($this,'check_is_admin') );
        add_action( 'wp_loaded', array($this,'load_current_tournament_once') );

        // Initialize charthandler object
        $ndays = $this->options->wetterturnier_betdays;
        $chartHandler = new wetterturnier_chartHandler("init in userclass",$ndays);
        add_action('wp_ajax_nopriv_timeseries_user_points_ajax',
                  array($chartHandler,"timeseries_user_points_ajax"));
        add_action('wp_ajax_timeseries_user_points_ajax',
                  array($chartHandler,"timeseries_user_points_ajax"));

        add_action('wp_ajax_nopriv_participants_counts_ajax',
                  array($chartHandler,"participants_counts_ajax"));
        add_action('wp_ajax_participants_counts_ajax',
                  array($chartHandler,"participants_counts_ajax"));


        // Adding custom user options
        add_action( 'edit_user_profile', array($this,'wt_custom_user_options') );
        add_action( 'show_user_profile', array($this,'wt_custom_user_options') );

        // Method which saves the custom user options
        add_action( 'personal_options_update',  array($this,'wt_save_custom_user_options') );
        add_action( 'edit_user_profile_update', array($this,'wt_save_custom_user_options') );

        add_action( 'wp_head', array($this,'disable_display_name_settings') );

        // Append javascript snippet
        add_action( 'wp_head', array($this, 'wt_add_ajax_admin') );

    }

    public function wt_add_ajax_admin() { ?>
        <script>
           jQuery.ajaxurl  = "<?php print admin_url('admin-ajax.php'); ?>";
        </script>
    <?php }

    /** Helper function to create a random string.
     * @param $length int, default is 10. Length of the string to be returned.
     * @param $prefix str, string prefix.
     */
    function random_string($length = 10, $prefix = "") {
        $res = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
                      ceil($length/strlen($x)) )),1,$length);
        return (strlen($prefix) > 0) ? sprintf("%s-%s", $prefix, $res) : $res;
    }

    /** Hide display name in profiles */
    function disable_display_name_settings() {
       ?>
       <script>
          jQuery( document ).ready(function() {
             jQuery("#bbpress-forums label[for='nickname']").closest("div").css("display","none");
             jQuery("#bbpress-forums label[for='display_name']").closest("div").css("display","none");
          });
       </script>
    <?php }

    /** Adding special wetterturnier options to the user
     * profile or settings page.
     *
     * @param $user is a stdClass object containing the user Information.
     */
    function wt_custom_user_options( $user ) { ?>
        <h3>Wetterturnier Options</h3>

        <?php // Getting user options first to set the 'selected' options.
        $wt_bo = get_user_option("wt_betform_orientation",$user->ID);
        $wt_ts = get_user_option("wt_wttable_style",$user->ID);
        ?>
    
        <table class="form-table">
            <tr>
                <th><label for="wt_betform_orientation">
                <?php printf("%s:",_e("Bet-form orientation","wpwt")); ?>
                </label></th>
                <td>
                   <select id="wt_betform_orientation" name="wt_betform_orientation">
                      <option value="portrait" <?php print ((is_bool($wt_bo) | $wt_bo === "portrait") ? "selected" : ""); ?>>
                         <?php _e("portrait","wpwt"); ?>
                      </option>
                      <option value="landscape" <?php print ($wt_bo === "landscape" ? "selected" : ""); ?>>
                         <?php _e("landscape","wpwt"); ?>
                      </option>
                   </select>
                   <span class="description"><?php printf("%s.",__("Select your default orientation","wpwt")); ?></span>
                </td>
            </tr>
            <tr>
                <th><label for="wt_wttable_style">
                <?php printf("%s:",_e("Table styling","wpwt")); ?>
                </label></th>
                <td>
                   <select id="wt_wttable_style" name="wt_wttable_style">
                      <option value="default" <?php print ((is_bool($wt_ts) | $wt_ts === "default") ? "selected" : ""); ?>>
                         <?php _e("default","wpwt"); ?>
                      </option>
                      <option value="contrast" <?php print ($wt_ts === "contrast" ? "selected" : ""); ?>>
                         <?php _e("contrast","wpwt"); ?>
                      </option>
                      <option value="orangeblue" <?php print ($wt_ts === "orangeblue" ? "selected" : ""); ?>>
                         <?php _e("orange-blue","wpwt"); ?>
                      </option>
                   </select>
                   <span class="description"><?php printf("%s.",__("Select your preferred table styling","wpwt")); ?></span>
                </td>
            </tr>
        </table>
    <?php }

    /** There is a @ref method called wt_custom_user_option
     * which adds custom settings to the user profile page.
     * This method saves the options to the database.
     *
     * @param $user_id. Integer user ID of the active user.
     */
    function wt_save_custom_user_options( $user_id ) {
    
       // Securety
       if ( !current_user_can( 'edit_user', $user_id ) )
          return false;
    
       // Try to update. If update fails (entry did not exist): add
       $check = update_user_meta( $user_id, 'wt_betform_orientation', $_POST['wt_betform_orientation'] );
       if ( ! $check ) {
          add_user_meta( $user_id, 'wt_betform_orientation', $_POST['wt_betform_orientation'], true );
       }
       $check = update_user_meta( $user_id, 'wt_wttable_style', $_POST['wt_wttable_style'] );
       if ( ! $check ) {
          add_user_meta( $user_id, 'wt_wttable_style', $_POST['wt_wttable_style'], true );
       }
    }

    /** Function which will be executed as soon as wordpress is loaded.
     * Checks whether user is admin when logged in. Sets private attribute
     * $this->is_admin to true.
     */
    function check_is_admin() {
       if ( ! is_admin() ) { $this->is_admin = true; }
       ///if ( ! current_user_can('manage_options') ) { $this->is_admin = true; }
    }

    /** Wrapper function for wordpress is_admin function.
     *
     *  @return Returns boolean attribute $this->is_admin.
     */
    function is_admin() { return( $this->is_admin ); }

   /** Append terms and conditions flag during registration */
   function wetterturnier_registration_form() {
      ?>
      <input type="checkbox" name="wt_accept"></input>
      <?php
      printf("%s %s: <a href='%s' target='_blank'>%s</a>",__("They can be found here","wpwt"),
             __("Before the registration you have to read and accept our terms and conditions!","wpwt"),
             $this->get_terms_link(),__("Terms and Conditions","wpwt"));
   }

   /* ---------------------------------------------------------------
   /** Extends the bbpress profile page (see above,
    * add_action('bbp_template_after_user_profile',...)) with some
    * wetterturnier related statistics and stuff.
    */
   function show_wetterturnier_user_profile() {
      print $this->shortcode_include("views/userprofile.php");
   }


    /** Load js script snippet, manipulate, return and add to header */
    function add_cities_nav() {

        $css_pos = get_option("wetterturnier_cities_menu_css");
        $js = file_get_contents(sprintf('%s/templates/wetterturnier.menu.cities.js',dirname(__FILE__)));
        $js = str_replace('%css_pos%',$css_pos,$js);
        // PHP 5.3 hack (not working in one line)
        $url = explode('?',$_SERVER['REQUEST_URI']);
	     $url = $url[0];

        // - If $_REQUEST contains tdate: add
        if ( isset($_REQUEST['tdate']) ) {
           $tdatestr = sprintf("&tdate=%d",(int)$_REQUEST['tdate']);
        } else { $tdatestr = ""; }

        // - Adding the new menu items
        $menu       = "   <ul>";
        $menu      .= "      <li class='page_item wtnav-cities-small'><a href='/' target='_self'>".__('Home','wpwt')."</a></li>";
        $menu_big   = "      <li class='page_item wtnav-cities-big %s'><a href='%s?wetterturnier_city=%s".$tdatestr."' target='_self'>%s</a></li>";
        $menu_small = "      <li class='page_item wtnav-cities-small %s'><a href='%s?wetterturnier_city=%s".$tdatestr."' target='_self'>%s</a></li>";

        // Generating ul/li elements with HASH
        foreach ( $this->get_all_cityObj() as $cityObj ) {
            //if ( strcmp($_SESSION['wetterturnier_city'],$cityObj->get('hash') === 0 ) )
            if ( $_SESSION['wetterturnier_city'] === $cityObj->get('hash') )
            { $class = "wetterturnier-city-active"; } else { $class = ""; } 
            $menu .= sprintf($menu_small,$class,$url,$cityObj->get('hash'),$cityObj->get('hash'));
        }
        // Generating ul/li elements with NAME
        foreach ( $this->get_all_cityObj() as $cityObj ) {
            if ( $_SESSION['wetterturnier_city'] === $cityObj->get('hash') )
            { $class = "wetterturnier-city-active"; } else { $class = ""; } 
            $menu .= sprintf($menu_big,$class,$url,$cityObj->get('hash'),$cityObj->get('name'));
        }
        $menu .= "   </ul>";
        $js = str_replace('%menu%',$menu,$js);
        
        echo $js;
    }


    /** Loadaing wetterturnier.bet.tooltip.js and manipulate content.
     * Add this (js) to the head of the wordpress. 
     */
    function wetterturnier_add_tooltip_js() {

        $content = file_get_contents(sprintf("%s/templates/wetterturnier.bet.tooltip.js",
                        dirname(__FILE__)));
        // Loading parameter
        $param = $this->get_param_data();
        // Generate dynamic content part
        $content = "<script>\n".$content."\n</script>";
        foreach ( $param as $rec ) {
            if ( strlen($rec->help) === 0 ) { $rec->help = __('No help available'); }
            $dyn  = "       $('#param-".$rec->paramName."-tooltip').tooltipster({\n";
            $dyn .= "         contentAsHTML: true, position: 'right',\n"; 
            $dyn .= "         theme: 'tooltipster-wt',\n";
            $dyn .= "         content: '".__('Help','wpwt').": ".$rec->help."'\n";
            $dyn .= "       });\n";
        }
        // Insert dynamic content into the js template and
        // print it.
        $content = str_replace('%replace_with_tooltips%',$dyn,$content);
        print $content;
    }


    /** load defined cities */
    function initialize_cities_menu() {
        // Add all of them to the primary menu
        $nav_locations = get_nav_menu_locations();
        if ( ! get_option("wetterturnier_cities_menu_css") ) { return; }
        add_action('wp_head',array($this,'add_cities_nav'));
    }


    /** Load js script snippet, manipulate, return and add to header */
    function add_wetterturnier_bet_validate_js() {
        global $wpdb;

        $css_pos = get_option('wetterturnier_cities_menu_css');

        $js = file_get_contents(sprintf('%s/templates/wetterturnier.bet.validate.js',dirname(__FILE__)));
        $js = str_replace('%css_pos%',$css_pos,$js);
        // - Now add parameter data
        $params = $wpdb->get_results("SELECT paramName, valformat, valmin, valmax FROM "
                                    .$wpdb->prefix."wetterturnier_param");
        // - Append if valformat is set
        $rules = "";
        foreach ( $params as $param ) {
            // If there is a length argument
            //if ( ! empty($param->vallength) and $param->vallength > 0 ) {
            $vallen = sprintf(", range: [%d, %d]",$param->valmin/10.,$param->valmax/10.);
            //} else { $vallen = false; }
            // Now add the rule
            if ( strcmp($param->valformat,'number') == 0 ) {
                $rules .= "        day1_".$param->paramName.": { number: true ".$vallen."},\n";
                $rules .= "        day2_".$param->paramName.": { number: true ".$vallen."},\n";
            } else if ( strcmp($param->valformat,'digits') == 0 ) {
                $rules .= "        day1_".$param->paramName.": { number: true ".$vallen."},\n";
                $rules .= "        day2_".$param->paramName.": { number: true ".$vallen."},\n";
            }
        }
        $js = str_replace('%replace_with_rules%',$rules,$js);
        $js = str_replace('%replace_daystr1%',__("First day","wpwt"),$js);
        $js = str_replace('%replace_daystr2%',__("Second day","wpwt"),$js);
        // Add to header
        echo "<script>";
        echo $js;
        echo "</script>";
    }

    
    /** Check which city is choosen */
    function city_check() {

        // If $_GET argument ['wetterturnier_city'] is set,
        // take this as new session variable.
        if ( ! empty($_GET) ) {
            if ( ! empty($_GET['wetterturnier_city'] ) )
            {
                // Setting to session for non-logged-in users
                $_SESSION['wetterturnier_city'] = $_GET['wetterturnier_city'];
                // Store to options for logged-in users. The system 
                // remembers what the user was looking at.
                if ( add_action('init',array($this,'logincheck')) ) { 
                    add_option('wt_city_userid_'.(string)get_current_user_id(),
                        $_SESSION['wetterturnier_city'], '', 'yes');
                }
            }
        }

        // If there is NO entry in session class: default city
        if ( empty($_SESSION['wetterturnier_city']) ) {
            // Register this in the user session
            if ( add_action('init',array($this,'logincheck')) ) { 
                $last = get_option('wt_city_userid_'.(string)get_current_user_id());
                // oh, a new user
                if ( ! $last ) { 
                    $cities = $this->get_all_cityObj();
                    $_SESSION['wetterturnier_city'] = $cities[0]->get('hash');
                } else {
                    $_SESSION['wetterturnier_city'] = $last; 
                }
            } else {
                $cities = $this->get_all_cityObj();
                $_SESSION['wetterturnier_city'] = $cities[0]->get('hash');
            }
        }

    }

    /** workaround function, only call it after init!
     * add_action('init','logincheck'). Returns bool value.
     */
    function logincheck() {
        if ( is_user_logged_in() ) {
            return(true);
        } else {
            return(false);
        }
    }

    /** In-page registration shortcode */
    function shortcode_wetterturnier_linkcollection() {
        return($this->shortcode_include("views/linkcollection.php"));
    }

    /** In-page registration shortcode */
    function shortcode_wetterturnier_register() {
        return($this->shortcode_include("views/register.php"));
    }

    /** Wetterturnier synop symbol overview. */
    function shortcode_wetterturnier_synopsymbols() {
        return($this->shortcode_include("views/synopsymbols.php"));
    }

    /** Wetterturnier forecast and analysis map navigation */
    function shortcode_wetterturnier_mapsforecasts() {
        return($this->shortcode_include("views/maps-forecasts.php"));
    }
    function shortcode_wetterturnier_mapsanalysis() {
        return($this->shortcode_include("views/maps-analysis.php"));
    }
    function shortcode_wetterturnier_soundingsmorten() {
        return($this->shortcode_include("views/soundings-morten.php"));
    }
    function shortcode_wetterturnier_cosmomorten() {
        return($this->shortcode_include("views/cosmo-morten.php"));
    }
    function shortcode_wetterturnier_iconepsmorten() {
        return($this->shortcode_include("views/iconeps-morten.php"));
    }

    /** Wetterturnier show archive list and values. 
     * Note: ob caches the "print output" of the functions in
     * between ob_start and ob_end. We have to return the content
     * to replace the [wetterturnier xxxx] shortcodes in the pages.
     */
    function shortcode_wetterturnier_archive() {
        return($this->shortcode_include("views/archive.php"));
    }

    /** Wetterturnier show current values (bet/obs), last tournament 
     * Note: ob caches the "print output" of the functions in
     * between ob_start and ob_end. We have to return the content
     * to replace the [wetterturnier xxxx] shortcodes in the pages.
     */
    function shortcode_wetterturnier_current( $args ) {
        $args = shortcode_atts( array('daybyday'=>false), $args );
        return($this->shortcode_include("views/current.php",$args));
    }

    /** Wetterturnier groups shows the groups and its users.
     * Note: ob caches the "print output" of the functions in
     * between ob_start and ob_end. We have to return the content
     * to replace the [wetterturnier xxxx] shortcodes in the pages.
     */
    function shortcode_wetterturnier_groups() {
        return($this->shortcode_include("views/groups.php"));
    }

    /** There is a special form where users can apply theirselves
     * to get member of a group. An administrator has to check
     * the promotion and approve or delete the promotion.
     */
    function shortcode_wetterturnier_applygroup() {
        return($this->shortcode_include("views/applygroup.php"));
    }

    /** Wetterturnier groups shows the groups and its users.
     * Note: ob caches the "print output" of the functions in
     * between ob_start and ob_end. We have to return the content
     * to replace the [wetterturnier xxxx] shortcodes in the pages.
     */
    function shortcode_wetterturnier_bet() {
        return($this->shortcode_include("views/bet.php"));
    }

    /** The wetterturnier has different ranking tables like
     * daily rankings, seasonal ranking, total ranking,
     * and some more. This is a function including differnt ranking
     * types, and also offers a set of options (if the user would like
     * to change what will be shown). 
     */
    function shortcode_wetterturnier_ranking( $args ) {
        // The first array defined is the 'default settings array',
        // the second ($args) the user options.
        // type=>'weekend',     default type
        // tdate=>false,        tournament date to be displayed. If not set
        //                      the latest tournament will be shown.
        // limit=>false,        shows top X
        // city=>false,         for single-city-rankings
        // cities=>1,2,3        List of cities for multi-city-ranking.
        //                      Only used if type="cities" or
        //                      type="seasoncities".
        // slim=>false,         Hide some columns
        // weeks=>15,           for total- and cities ranking
        // header=>true,        Hide header title and stuff
        // $args are the user-args, will be combined with de defaults.
        $args = shortcode_atts( array('type'=>'weekend',
                                      'tdate'=>Null,
                                      'limit'=>false,
                                      'city'=>false,
                                      'cities'=>"1,2,3",
                                      'slim'=>false,
                                      'weeks'=>15,
                                      'header'=>true,
                                      'hidebuttons'=>false), $args );
        foreach ( array("slim", "hidebuttons", "header") as $key ) {
            $args[$key] = ( $args[$key] === "true" ) ? true : false;
        }
        if ( ! in_array($args['type'], array('weekend','total','season','seasoncities','cities','yearly')) ) {
            return(sprintf("<div class=\"wetterturnier-info error\">%s</div>",
                sprintf("Sorry, ranking of <b>type='%s'</b> unknown. Option wrong.",$args['type'])));
        }
        if ( ! $args["city"] ) { $args["city"] = $this->get_current_cityObj()->get("ID"); }
        return($this->shortcode_include("views/ranking.php", $args));
    }

    /** Referer to the dataexport */
    function shortcode_wetterturnier_exportobslive() {
        return($this->shortcode_include("views/export-obslive.php"));
    }
    function shortcode_wetterturnier_exportobsarchive() {
        return($this->shortcode_include("views/export-obsarchive.php"));
    }

    /** Statistics interfaces. There are some based on R, others are purely php based. */
    function shortcode_wetterturnier_googlecharts() {
        return($this->shortcode_include("views/googlecharts.php"));
    }

    /** Obs images */
    function shortcode_wetterturnier_obsimages() {
        return($this->shortcode_include("views/obsimages.php"));
    }

    /** Observation table */
    function shortcode_wetterturnier_obstable() {
        return($this->shortcode_include("views/obstable.php"));
    }

    /** Show GEFS Meteograms and Meteogram data
     * And the MOS forecasts */
    function shortcode_wetterturnier_meteogram() {
        return($this->shortcode_include("views/meteogram.php"));
    }
    function shortcode_wetterturnier_meteogramdata() {
        return($this->shortcode_include("views/meteogramdata.php"));
    }
    function shortcode_wetterturnier_mosforecasts() {
        return($this->shortcode_include("views/mosforecasts.php"));
    }

    // --------------------------------------------------------------
    // Show judging-form where users can try out what the points 
    // would be given a certain forecast and observations. 
    // --------------------------------------------------------------
    function shortcode_wetterturnier_judgingform( $args ) {
        // Extra will be false by default
        $args = shortcode_atts( array('extra'=>false, 'parameter'=>false), $args );
        $args['extra'] = ($args['extra'] == "true" ? true : false);
        return($this->shortcode_include("views/judgingform.php",$args));
    }

    /** Forward to bbpress profile if we can find the user name.
     * Note: ob caches the "print output" of the functions in
     * between ob_start and ob_end. We have to return the content
     * to replace the [wetterturnier xxxx] shortcodes in the pages.
     */
    function shortcode_wetterturnier_profilelink($args) {
        $args = (object)shortcode_atts( array('user'=>false), $args );
        if ( ! $args->user ) { return(''); }
        if ( function_exists('bbp_get_user_profile_url') ) {
            global $wpdb;
            $user = $wpdb->get_row(sprintf("SELECT ID FROM %s WHERE user_login = '%s'",$wpdb->users,$args->user));
            if ( count($user) > 0 ) {
                $bbprofile = bbp_get_user_profile_url($user->ID);
                return( ',&nbsp;<a href='.$bbprofile.' target=\"_self\">'.__('Profile','wpwt').'</a>' ); 
            }
        }
        return( '' ); 
    }

    /** Frontend */
    function shortcode_wtcode( $args, $content ) {
      return( "<span class='wtcode'>".$content."</span>" );
    }

    /** List of cities/stations and the corresponding stations.
     * Used in frontend (rules/spielregeln).
     */
    function shortcode_wetterturnier_stationinfo( ) {
       $res = array("<ul>");
       foreach ( $this->get_all_cityObj() as $cityObj ) {
          // Fetching stations
          $stations = array();
          foreach ( $cityObj->stations() as $stnObj ) {
             array_push($stations,sprintf(" %s (%d)",$stnObj->get("name"),$stnObj->get("wmo")));
          }
          // Append list element
          array_push($res,sprintf("<li><b>%s</b>: %s</li>",
                  $cityObj->get("name"),join(", ",$stations)));
       }
       array_push($res,"</ul>");
       return( join("\n",$res) );
    }

    /** Frontend output to show which parameters of each station are neglected!
     * Used in frontend (rules/spielregeln).
     */
    function shortcode_wetterturnier_stationparamdisabled( ) {
       $res = array("<ul>");
       foreach ( $this->get_all_cityObj() as $cityObj ) {
          // Fetching stations
          foreach ( $cityObj->stations() as $stnObj ) {
            $inactive_params = $stnObj->showInactiveParams();
            if ( is_null($inactive_params) ) { continue; }
            // Else
            array_push($res,sprintf("<li>%s, <b>%s</b> (%d): <span class='wtcode'>%s</span> %s</li>",
               $cityObj->get("name"),$stnObj->get("name"),$stnObj->get("wmo"),
               $inactive_params,__("will not be considered","wpwt")));
          }
       }
       array_push($res,"</ul>");
       return( join("\n",$res) );
    }

    /** Function used for the shortcodes - includes the file using
    * buffering. Returns the content which should replace the
    * shortcode in the pages.
    */
    function shortcode_include( $file, $args=NULL ) {
        ob_start();
        require(sprintf('%s/%s',dirname(__FILE__),$file)); 
        $content = ob_get_contents();
        ob_end_clean(); return($content);
    }

    /** Counting how many players already got points for a certain
     * tournament date for each city. The method is used for some
     * applications where the current leading players will be shown.
     * If there are no points for any of the players, it does not make
     * a lot of sense to show the "ranking of the ongoing tournament"
     * bur rather show the "ranking of the last tournament".
     */
    function scored_players_per_town( $tdate ) {

      global $wpdb;
      $sql = array();
      array_push($sql,"SELECT stat.*, c.name, c.hash FROM");
      array_push($sql,"(SELECT count(*) AS count, cityID");
      array_push($sql,sprintf("FROM %swetterturnier_betstat",$wpdb->prefix));
      array_push($sql,sprintf("WHERE tdate=%d AND NOT points IS NULL GROUP BY cityID) AS stat",$tdate));
      array_push($sql,sprintf("LEFT OUTER JOIN %swetterturnier_cities AS c",$wpdb->prefix));
      array_push($sql,"ON stat.cityID=c.ID ORDER BY c.sort ASC");

      // Loading data
      $res = $wpdb->get_results(join("\n",$sql));
      if ( ! $res  ) { return(false); }
      return($res);

    }
  



    /** Used to display the group-lists
     *
     * @details Prints the table of a certain group and its members.
     * @param $inactive. Bool. If true this indicates that there are
     * inactive users in the group.
    */
    function print_group_table($id,$group,$users,$inactive) {

        echo '<h2>'.$group->groupName.'</h2>';
        echo '<desc>'.__('Description','wpwt').': '.$group->groupDesc.'</desc>';
        if ( ! $users ) {
            echo "<br>".__('Currently no users in this group','wpwt')."<br>\n";
        } else {
            // Button to show inactive users
            if ( $inactive ) { 
               echo "<input type=\"button\" class=\"groups-show-inactive\" "
                   ."groupID=\"".$group->groupID."\" value=\"show inactive\"></input>";
            }
            // Can we show user profile link from bbpress?
            $showprofile = false;
            $profile = __('Not available','wpwt');
            if ( function_exists('bbp_get_user_profile_url') ) {
                $showprofile = true; 
            }
    
    
            // Print head
            echo "  <table class=\"tablesorter wttable-groups\" id=\"wttable-group-".$group->groupID."\">\n"
                ."    <thead>\n"
                ."      <tr>\n"
                ."        <th>".__("User name",'wpwt')."</th>\n"
                ."        <th>".__("since",'wpwt')."</th>\n"
                ."        <th>".__("until",'wpwt')."</th>\n"
                ."        <th>".__("Show user profile",'wpwt')."</th>\n"
                ."      </tr>\n"
                ."    </thead>\n"
                ."    <tbody>\n";
            // Loop over users
            foreach ( $users as $user ) {
                if ( $showprofile ) {
                    $profile = bbp_get_user_profile_url( 30 );
                    if ( is_bool($profile) ) {
                        $profile = __('Not available','wpwt');
                    } else {
                        $profile = sprintf("<a href=\"%s\" target=\"_self\">"
                                     ."%s</a>",bbp_get_user_profile_url( $user->ID ),
                                      __("Show profile","wpwt"));
                    }
                }
                if ( strftime('%s',strtotime($user->until)) < 0 ) {
                   $until = __('active','wpwt');
                   if ( $user->active == 8 ) { $until .= "&nbsp;*"; }
                   $class = '';
                } else {
                   $until = strftime('%Y-%m-%d',strtotime($user->until));
                   $class = ' class=\'inactive\'';
                }
                echo "      <tr>\n"
                    ."        <td".$class.">".$user->name."</td>\n"
                    ."        <td".$class.">".strftime('%Y-%m-%d',strtotime($user->since))."</td>\n"
                    ."        <td".$class.">".$until."</td>\n"
                    ."        <td".$class.">".$profile."</td>\n"
                    ."      </tr>\n";
            }
            echo "    </tbody>\n"
                 ."  </table>\n";
        }
        
    }


    /** Loading all tournament dates stored in the db ever for this
     * city (based on the bets, not on the obs. Should change that
     * probably).
     */
    function archive_show_bet_data() {
    
        global $wpdb;
    
        // Getting city information
        $cityObj = $this->get_current_cityObj();

        // If input $link is 'here' we are using the current
        // page url. If it is false, the user forgot to add the option
        // 'link=...' to the shortcut and we have to give him a hint.
        // Else take the input link.
        if ( ! $this->REQUEST_CHECK('wetterturnier_link') ) {
            $link = $this->curPageURL();
        } else {
            $link = $this->REQUEST_CHECK('wetterturnier_link');
        }
    
        // Adding limit
        if ( ! empty($_REQUEST['limit']) ) {
            if ( is_numeric($_REQUEST['limit']) ) {
                $limit = (int)$_REQUEST['limit'];
            } else if ( $_REQUEST['limit'] === "all" ) {
                $limit = false;
            } else {
                $limit = 10; # default
            }
        } else {
            if ( ! $this->REQUEST_CHECK('wetterturnier_num') ) {
                $limit = 10;
            } else if ( is_integer($this->REQUEST_CHECK('wetterturnier_num')) ) {
                $limit = (int)$this->REQUEST_CHECK('wetterturnier_num');
            } else {
                $limit = 10;
            }
        }
        

        global $WTuser;
        $current = $WTuser->current_tournament(0,false);

        // First of all we have to find the tournaments which
        // have to be shown on the list given the limit set.
        $date_sql = array();
        array_push($date_sql,'  SELECT tdate, COUNT(userID) AS players,');
        array_push($date_sql,'  MIN(points) AS pmin, MAX(points) AS pmax,');
        array_push($date_sql,'  AVG(points) AS pavg');
        array_push($date_sql,'  FROM %swetterturnier_betstat');
        array_push($date_sql,'  WHERE cityID = %d AND tdate <= %d');
        array_push($date_sql,'  GROUP BY tdate');
        array_push($date_sql,'  ORDER BY tdate DESC');

        $date_sql = sprintf( join("\n",$date_sql), $wpdb->prefix, $cityObj->get('ID'),
                      (int)$current->tdate - (int)$this->options->wetterturnier_betdays );
        if ( is_integer($limit) ) { $date_sql .= sprintf(" LIMIT %d",$limit); }

        ///////print "DATE SQL<br>".$date_sql."<br><br>";

        // Find maximum points for the dates we have found 
        // for the given city
        $maxp_sql = array();
        array_push($maxp_sql,'  SELECT p1.tdate AS tdate, p2.userID AS userID,');
        array_push($maxp_sql,'  p2.points AS points FROM ( ');
        array_push($maxp_sql,'    SELECT psub.tdate AS tdate, MAX(psub.points) AS points FROM (');
        array_push($maxp_sql,'      SELECT tdate, MAX(points) AS points FROM');
        array_push($maxp_sql,'      %swetterturnier_betstat');
        array_push($maxp_sql,'      WHERE cityID = %d');
        array_push($maxp_sql,'      GROUP BY userID, tdate');
        array_push($maxp_sql,'    ) AS psub GROUP BY tdate');
        array_push($maxp_sql,'  ) AS p1 LEFT OUTER JOIN (');
        array_push($maxp_sql,'    SELECT tdate, userID, MAX(points) AS points FROM');
        array_push($maxp_sql,'    %swetterturnier_betstat');
        array_push($maxp_sql,'    WHERE cityID = %d');
        array_push($maxp_sql,'    GROUP BY userID, tdate');
        array_push($maxp_sql,'  ) AS p2');
        array_push($maxp_sql,'  ON p1.points=p2.points AND p1.tdate=p2.tdate');

        $maxp_sql = sprintf( join("\n",$maxp_sql), $wpdb->prefix, $cityObj->get('ID'),
                       $wpdb->prefix, $cityObj->get('ID') );
        ///////print "MAXP SQL<br>".$maxp_sql.'<br><br>'; 

        $sql = array();
        array_push($sql,'SELECT u.user_login AS user_login, u.ID AS userID,');
        array_push($sql,'d.tdate AS tdate, d.players AS players,');
        array_push($sql,'p.points AS points, d.pmin AS pmin, d.pmax AS pmax,');
        array_push($sql,'d.pavg AS pavg FROM');
        array_push($sql,'(%s) AS d');
        array_push($sql,'LEFT OUTER JOIN');
        array_push($sql,'(%s) AS p');
        array_push($sql,'ON d.tdate = p.tdate');
        array_push($sql,'LEFT OUTER JOIN');
        array_push($sql,'( SELECT ID, user_login FROM %susers) AS u');
        array_push($sql,'ON u.ID = p.userID');

        //print "FULL SQL:<br>";
        //printf( join("<br>\n",$sql), $date_sql, $maxp_sql, $wpdb->prefix );
        //print "<br><br>";


        // Loading data from database
        $sql = sprintf( join("\n",$sql), $date_sql, $maxp_sql, $wpdb->prefix );
        $data = $wpdb->get_results(sprintf($sql,$wpdb->prefix,(int)$cityObj->get('ID'),
                          $wpdb->prefix,$wpdb->prefix,$cityObj->get('ID')));

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
                ."    <th>".__('Date','wpwt')."</th>\n"
                ."    <th>".__('Players','wpwt')."</th>\n"
                ."    <th>".__('Winner','wpwt')."</th>\n"
                ."    <th>".__('Points','wpwt')."</th>\n"
                ."    <th>".__('Stats','wpwt')."</th>\n"
                ."  </tr>\n";

            // Width of the points status bar
            $max_width = 190;
            foreach ( $data as $rec ) {
                
               // Create link to the archive page
               if ( strpos($link, '?') !== false ) {
                   $wt_link = $link.'&tdate='.$rec->tdate;
               } else {
                   $wt_link = $link.'?tdate='.$rec->tdate; 
               }

               $user  = $WTuser->get_user_display_class_and_name($rec->userID, $rec);
               if ( $user->userclass == "mitteltip" ) {
                  $user_name = $user->display_name;
               } else {
                  $user_name = $WTuser->get_user_profile_link( $user );
               }

               // Create the status bar (max is 200)
               $w1 = max(0,(int)floor((float)$rec->points / 200. * (float)$max_width)); # last number is max width 
               $w2 = max(0,(int)floor((float)$rec->pmin / 200. * (float)$max_width) - 1); # last number is max width 
               if ( $w2 < 0 ) { $w2 = 0; }
               $w3 = max(0,(int)floor((float)$rec->pavg / 200. * (float)$max_width) - 2 - $w2); # last number is max width 

               # Width to the player with the lowest points (looser width)
               $w1 = max(0,(int)floor((float)$rec->pmin / 200. * (float)$max_width));
               # Width of the bar between lowest and average -1 for border
               $w2 = max(0,(int)floor((float)$rec->pavg / 200. * (float)$max_width)) - $w1 - 1;
               # Width of the bar between average and highest -1 for border
               $w3 = max(0,(int)floor((float)$rec->points / 200. * (float)$max_width)) - $w1 - $w2 - 2;
               $sbar  = "<span class='archiv-statusbar' style='width: ".$max_width."px;'>\n"
                       ."  <span class='lower' style='margin-left: ".$w1."px; width: ".$w2."px;'></span>"
                       ."  <span class='upper' style='width: ".$w3."px;'></span>"
                       ."</span>\n";

               // Show the data
               echo "  <tr>\n";
               echo "    <td><a href=\"".$wt_link."\" target=\"_self\">"
                    .$this->date_format($rec->tdate)."</a></td>\n";
               echo "    <td>".$rec->players."</td>\n";
               echo "    <td>".$user_name."</td>\n";
               echo "    <td>".$this->number_format($rec->points,1)."/200</td>\n";
               echo "    <td>".$sbar."</td>\n";
               echo "  </tr>\n";
            
            }
            // End table
            echo "</table>\n";

        }
    }

    /** Displays the 'color legend' which shows the color 
     * mapping in the bet tables and allows the user to show/hide
     * a certain type of players (e.g., mitteltips or automaten).
     * Is a bit static, however, works.
     */
    function archive_show_colorlegend() { ?>
               <?php
               printf("<b>%s</b><br>\n",__("Color legend:","wpwt"));
               _e("Shows the color coding of the different player classes. Just click on the buttons to show/hide a certain type in the tables below!","wpwt");
               ?><br>
               <ul>
                  <li class="player">
                     <span><input class="settings-button" type="submit" name="player" value="" /></span>
                     <?php _e("Human player","wpwt"); ?>
                  </li>
                  <li class="mitteltip">
                     <span><input class="settings-button" type="submit" name="mitteltip" value="" /></span>
                     <?php _e("Group","wpwt"); ?>
                  </li>
                  <li class="automat">
                     <span><input class="settings-button" type="submit" name="automat" value="" /></span>
                     <?php _e("Automated forecast","wpwt"); ?>
                  </li>
                  <li class="referenz">
                     <span><input class="settings-button" type="submit" name="referenz" value="" /></span>
                     <?php _e("Reference method","wpwt"); ?>
                  </li>
                  <li class="sleepy">
                     <span><input class="settings-button" type="submit" name="sleepy" value="" /></span>
                     <?php _e("Sleepy","wpwt"); ?>
                  </li>
               </ul>
    <?php }

    /** Show total points for a given weekend with additional link to
     * the archive to see the single parameter points if somewone is
     * interested in that.
     */
    function archive_show_ranking_weekend() {
    
        global $wpdb;

        // Getting city information
        $cityObj = $this->get_current_cityObj();

        // If input $link is 'here' we are using the current
        // page url. If it is false, the user forgot to add the option
        // 'link=...' to the shortcut and we have to give him a hint.
        // Else take the input link.
        if ( ! $this->REQUEST_CHECK('wetterturnier_link') ) {
            $link = $this->curPageURL();
        } else {
            $link = $this->REQUEST_CHECK('wetterturnier_link');
        }
   
        // next_tournament with offset 1 gives last played
        // tournament.
        if ( empty($_REQUEST['tdate']) ) {
            $tdate = $this->next_tournament(1);
            $tdate = $tdate->tdate;
        } else {
            $tdate = $_REQUEST['tdate'];
        }
        $tdate_readable = $this->date_format($tdate);

        // Loading dates from the database
        $sql = 'SELECT usr.user_login, usr.display_name, '
              .'bet.points_d1 AS points_d1, '
              .'bet.points_d2 AS points_d2, '
              .'bet.points AS points '
              .'FROM %swetterturnier_betstat AS bet '
              .'LEFT OUTER JOIN %susers AS usr '
              .'ON usr.ID=bet.userID '
              .'WHERE bet.cityID=%d AND tdate=%d '
              .'ORDER BY points DESC';

        // Loading data from database
        $data = $wpdb->get_results(sprintf($sql,$wpdb->prefix,$wpdb->prefix,
                          $cityObj->get('ID'),(int)$tdate));

        // Print dates in a ugly way
        if ( empty($data) ) {
            echo "<div class='wetterturnier-info warning'>"
                .__('Sorry, but at the moment we cannot show '
                   .' you the weekend ranking for the choosen date. '
                   .' Possible problem: we havn\'t compute the '
                   .' points yet because of missing observations.','wpwt')
                ."</div>";    
        } else {
        
            // Navigation 
            $older = $this->older_tournament($_GET['tdate']);
            $newer = $this->newer_tournament($_GET['tdate']);
            $aurl = explode('?', $_SERVER['REQUEST_URI'], 2);
            $aurl = 'http://'.$_SERVER['HTTP_HOST'].$aurl[0];
            if ( ! in_array(strtolower($this->REQUEST_CHECK('wetterturnier_hidebuttons')),array('false')) ) { ?>
                <form style='float: left; padding-right: 3px;' method='post' action='<?php echo $aurl.'?tdate='.$older->tdate; ?>'>
                    <input class="button" type="submit" value="<< <?php _e("older"); ?>">
                </form>
                <form style='float: left; padding-left: 3px;' method='post' action='<?php echo $aurl.'?tdate='.$newer->tdate; ?>'>
                    <input class="button" type="submit" value="<?php _e("newer"); ?> >>">
                </form>
            <?php }

            // Show human readable output of the date
            echo "<h3>".__('Weekend ranking for','wpwt')." ".$tdate_readable."</h3>\n";
            $tdate_readable = $this->date_format($tdate);
    

            // Create a table to show the data
            echo "<table width=\"100%\">\n"
                ."  <tr>\n"
                ."    <th>".__('Rank','wpwt')."</th>\n"
                ."    <th>".__('Player','wpwt')."</th>\n"
                ."    <th>".__('Saturday','wpwt')."</th>\n"
                ."    <th>".__('Sunday','wpwt')."</th>\n"
                ."    <th>".__('Total','wpwt')."</th>\n"
                ."    <th>".__('Status','wpwt')."</th>\n"
                ."  </tr>\n";

            // Width of the points status bar
            $points_hold = 99999; $rank = 0; $hidden_rank = 0;
            $max_width = 190;
            foreach ( $data as $rec ) {

               // Increase Rank if necessary
               if ( $rec->points < $points_hold ) {
                   $points_hold = $rec->points; $hidden_rank++; $rank = $hidden_rank;
               } else { $hidden_rank++; }
                
               // Create the status bar (max is 200)
               // $points / 2 means $points / 200 * 100 to get percent
               $pc = $this->number_format( (float)$rec->points / 2., 1).'%';
               if ( $pc > 50. ) { $pc1 = $pc.'&nbsp;'; $pc2 = ''; }
               else             { $pc2 = $pc; $pc1 = ''; }
               $w1 = max(0,(int)floor((float)$rec->points / 200. * (float)$max_width)); # last number is max width 
               $sbar  = "<span class='archiv-statusbar' style='width: ".$max_width."px;'>\n"
                       ."  <span style='width: ".$w1."px;'>".$pc1."</span>".$pc2."\n"
                       ."</span>\n";

               // Show the data
               echo "  <tr>\n"
                   ."    <td>".$rank."</td>\n"
                   ."    <td>".$rec->login_name."</td>\n"
                   ."    <td>".$this->number_format($rec->points_d1,2)."</td>\n"
                   ."    <td>".$this->number_format($rec->points_d2,2)."</td>\n"
                   ."    <td>".$this->number_format($rec->points,2)."/200</td>\n"
                   ."    <td>".$sbar."</td>\n"
                   ."  </tr>\n";
            
            }
            // End table
            echo "</table>\n";

        }
    }


    /** Show archive and/or current data
     * If input $pionts is true, loading points from database.
     */
    function archive_show( $type, $tdate, $points = false, $showday = False ) {

        global $wpdb;
        global $WTuser;

        // Just exit if input is wrong.
        if ( $type != 'bets' & $type != 'obs' ) {
            die('WRONG INPUT ON archive_show IN archive.php');
        }

        // Getting city information
        $cityObj = $this->get_current_cityObj();

        // Only allow the admin to change values from the last
        // tournament, not somewhen. If the shown tournament is
        // older than the last one - dont show edit links.
        // Furthermore, the user has to have manage_options -
        // has to be logged in and Admin.
        $last_tournament = $WTuser->older_tournament( floor((int)gmdate('U')/86400) );
        if ( current_user_can( 'manage_options' ) && 
             $last_tournament->tdate == $tdate)      {
             $editable = true;
        } else { $editable = false; }

        // Class to store all data
        $data = new stdClass();
        $data->counter = 0; # count data values

        // ----------------------------------------------------------
        // Getting bets for the first forecast day
        // ----------------------------------------------------------
        if ( $type == 'bets' ) {
            // If $showday is false: show all bet days 
            // Number of forecast days defined by wetterturnier_betdays setting.
            $betdays = $WTuser->init_options()->wetterturnier_betdays;
            for ( $day=1; $day<=$betdays; $day++ ) {
               if ( is_numeric($showday) && $day != $showday ) { continue; }
               $hash = sprintf("day_%d",$day);
               $data->$hash = $this->get_bet_values($cityObj->get('ID'),$tdate,$day,$points);
               $data->counter += count( (array) $data->$hash->data );
            }

            $nameth = "      <th class=\"user-name filter-match\" data-placeholder=\""
                      .__("Name filter","wpwt")."\">".__("User","wpwt")."</th>\n";

            // Title of the table
            if ( is_bool($showday) ) {
               printf("<h2>%s, %s</h2>\n",$this->date_format($tdate),__("User bets","wpwt"));
            } else {
               $tmp = (int)($tdate+$showday)*86400.;
               $tmp = sprintf("%s %s",$this->date_format((int)$tdate+$showday,"%A"),
                                      $this->date_format((int)$tdate+$showday));
               echo "<h2>".sprintf(__("Day %d","wpwt"),$showday)
                    .": ".$tmp.", ".__("User bets","wpwt")."</h2>\n";
            }

        // ----------------------------------------------------------
        // If type != 'bets' we are showing the observations.
        // ----------------------------------------------------------
        } else {
            // If $showday is false: show all bet days 
            // Number of forecast days defined by wetterturnier_betdays setting.
            $betdays = $WTuser->init_options()->wetterturnier_betdays;
            for ( $day=1; $day<=$betdays; $day++ ) {
               if ( is_numeric($showday) && $day != $showday ) { continue; }
               $hash = sprintf("day_%d",$day);
               $data->$hash = $this->get_obs_values($cityObj->get('ID'),$tdate+$day);
               $data->counter += count( (array) $data->$hash->data );
            }
            $nameth = "      <th class=\"user-name\"".__("Station","wpwt")."</th>\n";

            // Title of the table
            if ( is_bool($showday) ) {
               printf("<h2>%s, %s</h2>\n",$this->date_format($tdate),__("Observation data","wpwt"));
            } else {
               printf("<h2>%s %d: %s %s, %s</h2>\n",
                    __("Day","wpwt"),$showday,
                    $this->date_format((int)$tdate+$showday,"%A"),
                    $this->date_format((int)$tdate+$showday),
                    __("Observation data","wpwt"));
            }
        }

        // If nothing is available, just show message
        if ( $data->counter == 0 ) { 

            echo "<div class='wetterturnier-info error'>\n";
            if ( $type == 'bets' ) {
               printf("%s.<br>\n%s\n",
                     __('Sorry, no bet data available','wpwt'),
                     __('There is probably something going wrong?','wpwt'));
            } else {
               printf("%s.<br>\n%s.\n",
                     __('Sorry, currently no observations available','wpwt'),
                     __('They will be displayed as soon as they are available','wpwt'));
            }
            echo "</div>\n";

        } else {

            // Custom table styling via settings page
            $wttable_style = get_user_option("wt_wttable_style");
            $wttable_style = (is_bool($wttable_style) ? "" : $wttable_style);

            // Open table output
            if ( is_bool($showday) ) { $tableid = sprintf("wttable-show-%s",$type); }
            else { $tableid = sprintf("wttable-show-%s%d",$type,$showday); }
            echo "<table id=\"".$tableid."\" class=\"wttable-show-".$type." wttable-show tablesorter ".$wttable_style."\">\n"
                ."  <thead>\n"
                ."    <tr>\n"
                .$nameth
                ."      <th class=\"param-day filter-false align-center\">".__("Day","wpwt")."</th>";
                // Adding header (parameter names)
                if ( ! $showday ) {                        $params = $data->day_1->params; }
                else { $hash = sprintf("day_%d",$showday); $params = $data->$hash->params; }
                foreach ( $params as $rec ) {
                    printf("      <th class=\"param-col param-col-%s filter-true align-right\">%s</th>",$rec->paramName,$rec->paramName);
                }
                // - If points, then show sums, too.
                if ( $points ) { 
                    echo "      <th class=\"filter-false\">".__('Sum','wpwt')."</th>";
                }
            echo "    </tr>\n"
                ."  </thead>\n"
                ."  <tbody>\n";
            // Show rows for first and second day
            foreach ( $data as $key=>$val ) {
               if ( preg_match('/^day_[0-9]{1,}/',$key) ) {
                  $day = (int)str_replace("day_","",$key);
                  $this->show_bet_data_rows($data->$key,$day,$type,$points,$editable,$tdate);
               }
            }

            // Close table
            echo " </tbody>\n"
                ."</table>\n";

        }

    }

    /** Show archive and/or current data */
    function show_bet_data_rows($data,$number,$type,$points=false,$editable=false,$tdate) {

        // Do I have to display observations?
        if ( strcmp($data->what,'obs') === 0 ) {
            $obs = True;
        } else {
            $obs = False;
        }

        // City is always current city. This is how the
        // $data were generated. We can easily take the
        // current city here.
        $cityID  = $this->get_current_city_id();
        $cityObj = $this->get_current_cityObj();

        // Convert $bet->betdate into "SAT/SUN" whatever
        $day = strftime('%a',$data->betdate * 86400 );


        // Create all the rows
        foreach ( $data->data as $rec ) {

            // There is a user called the Sleepy  which contains
            // the points for non-players. There are only
            // points, no bets. Therefore just show 'n' instead
            // of values.
            // Decide if this is the sleepy player
            // WARNING: has to be done before get_user_display_class_and_name
            // will be called (or at least before $rec->username will be
            // overwritten).
            if ( strcmp( strtolower($rec->user_login),'sleepy' ) == 0 ) {
                $sleepy = True;
            } else { $sleepy = False; }

            // Reset sumpoints (sum of points for the player)
            $sumpoints = 0.0;

            // Load proper user name and user class or category.
            // If  it is a group tip: ignore profile. Else show
            // username alongside with the profile link. 
            if ( $obs ) {
               $rec->userclass = "obs";
               $user_detail = $rec->user_login;
            } else {
               $user = $this->get_user_display_class_and_name($rec->userID, $rec);
               $rec->userclass = $user->userclass;
               if ( strcmp($user->userclass,"mitteltip") !== 0 ) {
                  $user_detail = $this->get_user_profile_link( $rec );
               } else {
                  $user_detail = $user->display_name;
               }
            }

            // Create edit button for administrators
            $edit_button = $this->create_edit_button( $rec->userclass, $cityObj,
                           ($obs ? (int)$rec->wmo : (int)$rec->userID), $tdate );

            // Show row
            printf("    <tr class='day-%d %s' userid='%d'>\n"
                  ."      <td class='username %s'>%s%s</td>\n"
                  ."      <td class='day'>%s</td>\n",$number,$rec->userclass,$rec->userID,
                  $rec->userclass,$edit_button,$user_detail,$day);

            // Adding values
            foreach ( $data->params as $param ) {

                $phash = sprintf("pid_%d",$param->paramID);

                // If observation, check if this parameter
                // was active for parameter $param->paramID and $tdate. If not, display "n".
                if ( $obs ) {
                   $stationID = $this->get_station_by_wmo( (int)$rec->wmo)->ID;
                   $paramObj  = new wetterturnier_paramObject( $param->paramID, "ID", $tdate );
                   $active    = $paramObj->isParameterActive( $stationID );
                } else { $active = false; } // Default

                // If observation and not active: show the "n" now
                if ( $obs && ! $active ) {
                    echo "      <td class='data' style='color: #BDBDBD;'>n</td>\n";
                // Normal procedure
                } else if ( property_exists($rec,$phash) ) {

                    // Now define how to display the data
                    if ( property_exists($rec->$phash,"placedby") ) {

                        // Here we have to distinguish between "NULL" and "set".
                        // If set to "null" an admin has set the value to "NULL".
                        // In this case we show an "x"
                        if ( is_null($rec->$phash->value) ) {
                           $showval = "X";
                        } else {
                           $showval = $this->number_format((float)$rec->$phash->value,
                                 ($points ? 1 : $param->decimals));
                        }
                        // Generate "modified" message
                        $user  = $this->get_user_by_ID((int)$rec->$phash->placedby)->user_login;
                        if ( ! $user ) { $user = "UNKNOWN"; }
                        $title = __("Modified by","wpwt").": <b>".$user."</b><br>".$rec->$phash->modified;
                        // Show cell with tooltip message
                        printf("      <td class=\"data changed-status\" title=\"%s\">%s</td>\n",
                                 $title, $showval);
                    } else {
                        // Sleepy  should never be shown - but just in case :) 
                        if ( $sleepy ) {
                           printf("      <td class=\"data\">----</td>\n");
                        // Show cell "normal", unmodified
                        } else {
                           printf("      <td class=\"data\">%s</td>\n",
                              $this->number_format((float)$rec->$phash->value,
                              ($points ? 1 : $param->decimals)));
                        }
                    }
                // This is the case if the phash does not exists which
                // mean that the object has no entry for the current
                // parameter (no value/no observation). Then show
                // a '---' instead of a 'n' or a value.
                } else {
                    // Show "empy" cell
                    echo "      <td class='data'>----</td>\n";
                }
                // Sum points for this player
                if ( isset($rec->$phash) ) {
                   if ( is_numeric((float)$rec->$phash->value) ) {
                      if ( $rec->$phash->value > -999 ) { $sumpoints = $sumpoints + (float)$rec->$phash->value; }
                   }
                }
            }
            // - If "points=true" we have summed up the points
            //   and show them in the last column of the table.
            if ( $points ) {
               echo "      <td class='data'>".$this->number_format($sumpoints,1)."</td>\n";
            }
            echo "   </tr>\n";
        } 
    }

   /** Function to add the 'edit' button to observations
    * and bets if an admin is logged in.
    */
   function create_edit_button( $type, $cityObj, $identifier, $tdate ) {
      // If no admin: return
      if ( ! current_user_can('manage_options') ) { return(""); }
      // If mitteltip: return
      if ( $type === "mitteltip" ) { return(""); }
      // If this is an observation entry (use $type = "obs")
      if ( $type === "obs" ) {
         return(sprintf("<span class='button small edit edit-obs' url='%s' station='%d' cityID='%s' tdate='%d'>"
                                ."</span>",admin_url(),(int)$identifier,$cityObj->get('ID'),$tdate));
      } else {
         return(sprintf("<span class='button small edit edit-bet' url='%s' userID='%d' cityID='%s' tdate='%d'>"
                                ."</span>",admin_url(),(int)$identifier,$cityObj->get('ID'),$tdate));
      }
   }

   /** Loading average points from database */
   public function get_average_points( $cityID=False, $tdate=False ) {
      
      global $wpdb;

      // Take current cityID if missing input
      if ( ! $cityID ) {
         $cityID  = $this->get_current_city_id();
      }

      // Take current tournament if missing input
      if ( ! $tdate ) {
         $current = $this->current_tournament(0,false,0,true);
         $tdate   = $current->tdate;
      } 
      $sleepy = $this->get_user_by_username('Sleepy');
      // Generate SQL statement
      $sql = sprintf("SELECT AVG(points) AS points FROM %swetterturnier_betstat WHERE tdate=%d "
                    ."AND cityID=%d AND userID != %d",$wpdb->prefix,
                     $tdate,$cityID,$sleepy->ID);
      $res = $wpdb->get_row( $sql );
      return $this->number_format($res->points,1);
   }

   /** Loading current sleepy points */
   public function get_sleepy_points( $cityID=False, $tdate=False ) {
      
      global $wpdb;

      // Take current cityID if missing input
      if ( ! $cityID ) {
         $cityID  = $this->get_current_city_id();
      }

      // Take current tournament if missing input
      if ( ! $tdate ) {
         $current = $this->current_tournament(0,false,0,true);
         $tdate   = $current->tdate;
      } 
      $sleepy = $this->get_user_by_username('Sleepy');
      // Generate SQL statement
      $sql = sprintf("SELECT points FROM %swetterturnier_betstat WHERE tdate=%d "
                    ."AND cityID=%d AND userID = %d",$wpdb->prefix,
                     $tdate,$cityID,$sleepy->ID);
      $res = $wpdb->get_row( $sql );
      if ( ! $res ) { return( "N/A" ); } else {
         return $this->number_format($res->points,1);
      }
   }

   /** There is an ajax function call to save users which try to apply for
    * a group membership.
    * Returns json array. If user is allready an active member of this group,
    * return value 'got' is 'ismember'.
    */
   public function applygroup_ajax() {

       global $wpdb;

       // Single date
       if ( ! empty($_POST['what']) & ! empty($_POST['uID']) & !empty($_POST['gID']) & !empty($_POST['text']) ) {

          // If a user would like to apply for a group
          if ( strcmp($_POST['what'],'wtapply') === 0 ) {

              // Check if user is an active member of the group to create
              // message for him.
              $sql  = 'SELECT count(*) AS N from %swetterturnier_groupusers WHERE ';
              $sql .= 'userID = %d AND groupID = %d AND active = 1 ';
              $sql .= 'AND until = \'0000-00-00 00:00:00\'';
              $check = $wpdb->get_row(sprintf($sql,$wpdb->prefix,(int)$_POST['uID'],
                                          (int)$_POST['gID']));
              if ( $check->N > 0 ) { print json_encode(array('got'=>'ismember')); die(); }

              // Store into database
              $table = sprintf('%swetterturnier_groupusers',$wpdb->prefix);
              $data  = array('groupID'=>(int)$_POST['gID'],
                             'userID'=>(int)$_POST['uID'],
                             'application'=>htmlspecialchars($_POST['text']),
                             'active'=>9);
              $this->insertonduplicate($table, $data);

              print json_encode(array('got'=>'ok'));

           // Else the user will get out of an active group
           } else {

              // Add text to the application field. Active users with non-empty
              // Application fields will be marked as "will get out of the group"
              // in the admin interface.
              $table = sprintf('%swetterturnier_groupusers',$wpdb->prefix);
              $where = array('ID'=>(int)$_POST['gID'],
                             'userID'=>(int)$_POST['uID'],
                             'until'=>NULL);
              $data  = array('application'=>htmlspecialchars($_POST['text']),'active'=>8);
              $check = $wpdb->update($table,$data,$where);

              print json_encode(array('got'=>'ok'));
           }

       } else {
          die(-1);
       }

       die(); # important
   }

   /** There is an ajax function call to save users which try to apply for
    * a group membership.
    * Returns json array. If user is allready an active member of this group,
    * return value 'got' is 'ismember'.
    */
   public function ranking_ajax() {

       global $wpdb;
       if ( empty($_REQUEST["city"]) || empty($_REQUEST["tdates"]) ) {
           print json_encode(array("error"=>"Error by ajax interface function: wrong inputs."));
           die(0);
       }
       // Extractig tdates and convert to object. Should contain
       // from, to, from_prev, to_prev.
       $tdates = (object)$_REQUEST["tdates"];
       foreach ( array("from", "to", "from_prev", "to_prev") as $required ) {
           if ( ! property_exists($tdates, $required) ) {
               print json_encode(array("error"=>"Error by ajax interface function: "
                       ." Missing argument \"" . $required . "\"."));
               die(0);
           } else {
               // Can be Null. If we get an empty string that is what
               // JSON delivers us as Null.
               if ( strlen($tdates->$required) === 0 ) {
                  $tdates->$required = Null;
               // Else check if numeric. If not, stop.
               } else if ( ! is_numeric($tdates->$required) ) {
                  print json_encode(array("error"=>"Error by ajax interface function: "
                      ." Wrong format for argument " . $required . "=\"".$tdates->$required."\"."
                      ." (is of type \"".gettype($tdates->$required)."\")."));
                  die(0);
               } else { $tdates->$required = (int)$tdates->$required; }
           }
       }

       # Parsing cities
       if ( in_array($_REQUEST["type"], array("cities", "seasoncities")) ) {
          $cityObj = array();
          foreach ( explode(",", $_REQUEST["cities"]) as $cityID ) {
             array_push($cityObj, new wetterturnier_cityObject( (int)$cityID ));
          }
       } else {
          if ( is_numeric($_REQUEST["city"]) ) {
             $cityObj = new wetterturnier_cityObject( (int)$_REQUEST["city"] );
          } else {
             $cityObj = array();
             foreach ( explode( ":", $_REQUEST["city"] ) as $cityID ) {
                 array_push( $cityObj, new wetterturnier_cityObject( (int)$cityID ) );
             }
          }
       }

       # Loading ranking
       $rankingObj = new wetterturnier_rankingObject();
       $rankingObj->set_cities($cityObj);
       $rankingObj->set_tdates($tdates);
       $rankingObj->set_cachehash($_REQUEST["type"]);
       $rankingObj->prepare_ranking();
       print $rankingObj->return_json();
       die(0);
   }


   /** This is a small ajax script I am using to call an Rscript
    * on the prognose server. Used for different R-Calls
    * WARNING: only integer values as arguments allowed.
    */
public function judging_ajax() {

      global $wpdb;

      // Taking input arguments
      $obs1     = (float)$_REQUEST['obs1'];
      $obs2     = (float)$_REQUEST['obs2'];
      $forecast = (float)$_REQUEST['forecast'];
      $param    = str_replace(" ","",$_REQUEST['param']);

      // Printing output
      $cmd_base = "cd /home/wetterturnier/wetterturnier-backend && venv/bin/python TestPoints.py --quiet";

      // Special observations? The extra obs
      if ( empty($_REQUEST['extra1']) && empty($_REQUEST['extra2']) ) {
         
	 // Printing output
	 $cmd = sprintf("%s -p %s -o %s,%s -v %s",
		        $cmd_base, $param,
			number_format($obs1,     1, ".", ""),
			number_format($obs2,     1, ".", ""),
			number_format($forecast, 1, ".", ""));
	} else {
         $extra1 = (float)$_REQUEST['extra1'];
         $extra2 = (float)$_REQUEST['extra2'];
         //$cmd = sprintf("%s -p %s -o %.1f,%.1f -v %.1f -s %.1f,%.1f",
         $cmd = sprintf("%s -p %s -o %s,%s -v %s -s %s,%s",
                        $cmd_base, $param,
			number_format($obs1,     1, ".", ""),
			number_format($obs2,     1, ".", ""),
			number_format($forecast, 1, ".", ""),
			number_format($extra1,   1, ".", ""),
			number_format($extra2,   1, ".", ""));
}
 // Calling the py script
      $result = exec($cmd);
      // Expect that the LAST word will be the points
      preg_match_all("/points\s{1,}([-]{0,1}[0-9]{1,}[.]{1}[0-9]{0,})?/",$result,$matches);
      // Setting points value
      if ( empty($matches[1][0]) ) {
         $points = "empty"; 
      } else {
         $points = array_pop($matches[1]);
      }
      print json_encode( array("cmd"=>$cmd,"points"=>$result) );
      die();
}


   /** Used for the ranking-frontend: display details of a certain
    * user. Dynamically loaded via an ajax call.
    */
   public function wttable_show_details_ajax() {
      global $wpdb;
      global $WTuser;

      $args = (object)$_POST;

      // Create SQL statement
      $sql = array();
      array_push($sql,"SELECT usr.user_login, usr.display_name, stat.*");
      array_push($sql,sprintf("FROM %swetterturnier_betstat AS stat",$wpdb->prefix));
      array_push($sql,sprintf("LEFT OUTER JOIN %susers AS usr",$wpdb->prefix));
      array_push($sql,"ON usr.ID = stat.userID");
      array_push($sql,sprintf("WHERE stat.tdate = %d",$args->tdate));
      array_push($sql,sprintf("AND stat.cityID = %d",$args->cityID));
      array_push($sql,sprintf("AND stat.userID = %d",$args->userID));
      // Getting data
      $res = $wpdb->get_row(join("\n",$sql));

      // Getting maximum rank
      $sql = array();
      array_push($sql,"SELECT max(rank) AS maxrank FROM");
      array_push($sql,sprintf("%swetterturnier_betstat",$wpdb->prefix));
      array_push($sql,sprintf("WHERE cityID = %d AND tdate = %d",$args->cityID,$args->tdate));
      $maxrank = $wpdb->get_row(join("\n",$sql));

      // ------------------------------------------------------------
      // Create the output which will be returned (html)
      // ------------------------------------------------------------
      $return = array("<div id=\"betdetails\" class=\"entry-content\">\n");

   
      // Adding points and ranking
      array_push($return,sprintf("Name: %s (%s)<br>",
                         $res->display_name,$res->user_login));
      array_push($return,sprintf("Submitted: %s<br>",
                         (is_null($res->submitted)) ? "---" : $res->submitted));
      array_push($return,sprintf("%s&nbsp;%s: %s<br>",
                         __("Points","wpwt"),__("Saturday","wpwt"),
                         $this->number_format($res->points_d1,1)));
      array_push($return,sprintf("%s&nbsp;%s: %s<br>",
                         __("Points","wpwt"),__("Sunday","wpwt"),
                         $this->number_format($res->points_d2,1)));
      array_push($return,sprintf("%s: %s<br>",
                         __("Points total","wpwt"),$this->number_format($res->points,1)));
      array_push($return,sprintf("%s: %d/%d<br><br>",
                         __("Rank","wpwt"),$res->rank,$maxrank->maxrank));


      // Adding observations
      $ndays    = (int)$this->options->wetterturnier_betdays;
      $params   = $this->get_param_names();
      $stations = $this->get_station_data_for_city( (int)$args->cityID );
      $uhash    = sprintf("uid_%d",(int)$args->userID); // property name userID

      // Looping over all forecast bet days
      for ( $day=1; $day<=$ndays; $day++ ) {
         // Load observations, forecasts, and points
         $obs    = $this->get_obs_values((int)$args->cityID,(int)$args->tdate+$day);
         //print_r( $obs ) ;
         $data   = $this->get_bet_values($args->cityID,$args->tdate,$day,false,$args->userID);
         $data   = $data->data->$uhash;
         $points = $this->get_bet_values($args->cityID,$args->tdate,$day,true, $args->userID);
         $points = $points->data->$uhash;

         // Human readable day
         $tdate_readable = $this->date_format((int)$args->tdate+$day,"%A");
         $tdate_yyyymmdd = $this->date_format((int)$args->tdate+$day);

         // Start content: setting up a table for this forecast/bet day
         array_push( $return, sprintf("<b>%s</b>, %s\n",__($tdate_readable,"wpwt"),$tdate_yyyymmdd) );
         array_push( $return, sprintf("<table class=\"wttable-show small\">\n"
                     ."  <tbody>\n  <tr>\n    <th>%s</th>\n    <th>%s</th>\n",
                     __("Parameter","wpwt"),__("Forecast","wpwt")) );
         foreach ( $stations as $rec ) {
            array_push( $return, sprintf("    <th>%s</th>\n",$rec->name) );
         }
         array_push( $return, sprintf("    <th>%s</th>\n    <th>%s</th>\n  </tr>\n\n",
                     __("Deviation","wpwt"),__("Points","wpwt")) );

         foreach ( $params as $rec ) {

            // Parameter hash to access object properties
            $phash = sprintf("pid_%d",$rec->paramID);

            // Adding parameter name
            array_push( $return, sprintf("  <tr>\n    <td>%s</td>\n", $rec->paramName) );

            // Adding user forecast
            $user_forecast  = $data->$phash->value;
            array_push( $return, sprintf("    <td>%s</td>\n",
               $this->number_format($user_forecast,$obs->params->$phash->decimals)) );

            // Adding observations
            $user_deviation = array();
            foreach ( $stations as $stn ) {
               $stnhash = sprintf("wmo_%d",$stn->wmo);
               // Station has no data yet: property does not exist (add ---)
               if ( ! property_exists($obs->data,$stnhash) ) {
                  $value = "---";
               // This parameter has no observation yet (add ---)
               } else if ( ! property_exists($obs->data->$stnhash,$phash) ) {
                  $value = "---";
               } else {
                  $obs_value = $obs->data->$stnhash->$phash->value;
                  // If value on $obs->data->stnhash->phash is not null: add value, else ---
                  if ( ! is_null($obs_value) ) {
                     // Compute absolute deviation between observation and forecast
                     array_push($user_deviation,abs($obs_value - $user_forecast));
                     $value = $this->number_format($obs_value,$obs->params->$phash->decimals);
                  } else { $value = "---"; }
               }
               array_push( $return, sprintf("    <td>%s</td>\n",$value) );
            }

            // Adding absolute deviation between user forecast and observation
            if ( count($user_deviation) == 0 ) {
               array_push( $return, "    <td>---</td>\n" );
            } else {
               array_push( $return, sprintf("    <td>%s</td>\n",
                     $this->number_format(min($user_deviation),1)) );
            }

            // Adding points
            if ( ! is_null($points->$phash->value) ) {
               $value = $this->number_format($points->$phash->value,1);
            } else { $value = "---"; }
            array_push( $return, sprintf("    <td>%s</td>\n",$value) );

            // Close row
            array_push( $return, "  </tr>" );
         }

         // Close table
         array_push( $return, "</table>\n");
      }

      // Close <div id='betdetails>
      array_push( $return, "</div>\n" );

      print json_encode( (array)join("\n",$return) );

      die();
   }


   /** This is a small ajax script I am using to call an Rscript
    * on the prognose server. Used for different R-Calls
    * WARNING: only integer values as arguments allowed.
    */
   public function callRscript_ajax() {

      global $wpdb;

      $Rdir = '/var/www/Rscripts';

      // Basic R Command
      // Needs additonal arguments and the R-Script-Name.
      $rcmd = "Rscript --vanilla %s %s";

      // Prepare return array. 
      // Elements will be changed during the run.
      $result = array('stat'=>-9,'message'=>'Unknown error!','rcmd'=>'empty');

      // Request empty? Error.
      if ( empty($_REQUEST) ) {
         $result['stat']    = -1;
         $result['message'] = 'REQUEST missing'; 
      // Rscript missing? Error.
      } else if ( empty($_REQUEST['Rscript']) ) {
         $result['stat']    = -1;
         $result['message'] = 'Rscript missing'; 
      // If there are blans in the R-Script (hacking?)
      } else if ( substr_count($_REQUEST['Rscript'], ' ') > 0 ) {
         $result['stat']    = -1;
         $result['message'] = 'Rscript contained blanks'; 
      } else if ( strcmp($_REQUEST['Rscript'],htmlspecialchars($_REQUEST['Rscript'])) != 0 ) {
         $result['stat']    = -1;
         $result['message'] = 'Rscript contained special chars';
      // Else evaluating the $_REQUEST variables
      } else {
         $args = array(); 
         $Rscript = sprintf('%s/%s',$Rdir,(string)htmlspecialchars($_REQUEST['Rscript']));
         foreach ( array_keys($_REQUEST) as $key ) {
            if ( $key == 'action' || $key == 'Rscript' ) { continue; }
            array_push($args,sprintf('%s=%d',$key,(int)$_REQUEST[$key]));
         }
         if ( count($args) == 0 ) { $args = ""; }
         else { $args = sprintf("%s",join(' ',$args)); }
         $rcmd = sprintf($rcmd,$Rscript,$args);

         $result['rcmd'] = $rcmd;

         // Calling the script now
         $retval = exec($rcmd,$output,$status);
         $result['stat']    = $status;
         $result['return']  = $retval;
         if ( $status == 0 ) {
            $result['message'] = 'Success';
         } else {
            $result['message'] = sprintf('The Rscript %s returned an error',$Rscript);
         }
         $result['message'] = $retval;
      }

      // Printing output
      print json_encode($result);

      die(0);

   }


    /** Some pages are restricted for logged in users only. Instead
     * of using wordpress 'private' pages (which will result in a
     * 404: page not found if the user is not logged in) we are using
     * this small method. If a user is not logged in, an access
     * denied message will be shown. Return value 'true' in this case.
     * else the return value will be 'false', and nothing will be shown.
     */
    function access_denied() {
       if ( is_user_logged_in() ) { return(False); }
       // Else the user is not logged in, show message and return True
       printf("<h1>%s</h1>\n",__("Access denied","wpwt"));
       printf("<div class=\"wetterturnier-info error\">%s</div>",
             __("The access for some pages is restricted to registered and "
               ."logged in users only. You are not logged in at the moment. "
               ."To see the content of this specific page, please login first. "
               ."Thank you four your understanding.","wpwt") ); 

       // Show login form
       printf("<h1 class='entry-title'>%s</h1>",__("Login form","wpwt"));
       printf("<div id=\"login-restricted\">\n");
       _e("Due to the European general data protection regulation (GDPR) we kindly "
          ."ask you to accept our privacy policy before you login. "
          ."As soon as you agree (click the button above) the login form will "
          ."be shown.", "wpwt");
       wp_login_form(array(
           "remember"    => true,
           "form_id"     => "restricted-login",
           "id_username" => "restricted-user_login",
           "id_password" => "restricted-user_pass",
           "id_remember" => "restricted-rememberme",
           "id_submit"   => "restricted-wp-submit"
       ));
       printf("</div>\n");

       return(True);
    }

    /** Shows the leading players of the current or ongoing
     * tournament.
     *
     * @param $city. If `NULL` (default) the current or active city
     * will be used. Can also be a string (city hash) or numeric
     * value (city ID). Details see
     *
     * @ref wetterturnier_generalclass::get_current_cityObj
     *
     * @param $tdate. Numeric representation of the tournament date.
     * If `NULL` the current tournament will be used.
     *
     * @param $number. Positive integer. Number of players which
     * should be shown. Default is `3`.
     *
     * @param $style. Default `NULL`. Can be set to 'wide' for a 
     * landscape rather than a portrait representation.
     */
    function show_leading($city=NULL,$tdate=NULL,$number=3,$style=NULL) {

      global $wpdb;

      if ( is_null($city) ) {
         $cityObj = $this->get_current_cityObj();
      } else {
         $cityObj = new wetterturnier_cityObject( $city );
      }


      // Check if there are any valid rankings at the moment
      $current = $this->current_tournament;
      $scored  = $this->scored_players_per_town( $current->tdate );
      // No results for the current one? Well, take the one before!
      if ( ! $scored ) {
         $current = $this->older_tournament( $current->tdate );
      }
      $ranking = $this->get_ranking_data($cityObj,(int)$current->tdate);

      //// If the length of $res is equal to zero (or $res equals false)
      //// we cannot show leading users.
      //if ( $ranking->dataLength < 1 ) { 
      //   print '<div class="wetterturnier-info warning">'
      //        .__('Sorry currently no information about the leading users.'
      //           .'Possible reason: no observations (and therefore no points) at all.'
      //           .'However, good luck in the ongoing tournament!','wpwt')
      //           ."</div>\n";
      //   return;
      //}

      // ------------------------------------------------------------
      // There are (two) kinds of output styling. One is compact, showing only 
      // the avatar of the leader and a table with the first $number players 
      // while the second (if $style=NULL or undefined) shows all $number
      // leading players with avatar.
      // ------------------------------------------------------------
      $counter = 0; $rank = 1;
      if ( strcmp($style,'compact')===0 ) { ?>

         <!-- Compact leader view -->
         <table class="wt-leading content-list hentry">
            <tr>
               <td width="106px">
                  <div class="alignleft wt-leading-avatar wt-leading-avatar-compact" style="width: <?php print $width; ?>%;">
                     <?php print get_wp_user_avatar( $ranking->data[0]->userID, 96); ?>
                  </div>
               </td>
               <td>
                  <table class="wt-leading">
                     <thead>
                        <tr>
                           <th class="wt-leading-position">#</th>
                           <th class="wt-leading-user"><?php _e("User","wpwt"); ?></th>
                           <th class="wt-leading-points"><?php _e("Points","wpwt"); ?></th>
                        </tr>
                     </thead>
                     <tbody>
                     <?php
                     foreach ( $ranking->data as $rec ) {
                        // Increase ranking
                        if ( $counter > 1 ) {
                           $p1 = round($ranking->data[$counter-1]->points,2);
                           $p2 = round($ranking->data[$counter]->points,  2);
                           if ( $p1 > $p2 ) { $rank = $rank + 1; }
                        }
                        $rec->rank = $rank;
                        // Display name/login_name
                        if ( strlen($rec->display_name) > 0 ) {
                           $rec->user_login = $rec->display_name;
                        }
                        // Replace GRP_ hash
                        $rec->user_login = preg_replace("/GRP_/","Gruppe: ",$rec->user_login);
                        ?>
                        <tr>
                           <td class="wt-leading-position"><?php print $rec->rank; ?></td>
                           <td class="wt-leading-user"><darkblue><?php print $rec->user_login; ?></darkblue></td>
                           <td class="wt-leading-points">
                              <?php print $this->number_format($rec->points,2); ?>
                           </td>
                        </tr>
                        <?php
                        // Stop after $number of outputs. AND AT LEAST the
                        // first person on the second rank was shown.
                        $counter = $counter+1;
                        if ( $rank > 1 && $counter >= $number ) { break; }
                     } ?>
                     </tbody>
                  </table>
               </td>
            </tr>
         </table>

      <?php } else { // Here the non-compact view starts

         // Wide format: show title
         if ( strcmp($style,'wide') === 0 ) {
            printf("<h1 class='entry-title'>%s %s</h1>\n",
                  __("Leaderboard","wpwt"),$cityObj->get('name'));
         } ?>

         <!-- Standard leader view -->
         <div>
            <?php
            // Number of bet days
            $ndays = (int)$this->options->wetterturnier_betdays;

            // Width of the avatars
            $width = floor( 100 / $number );
            // Show avatars
            $counter = 0; $rank = 1; $hold_points = NULL;
            foreach ( $ranking->data as $rec ) {

               // Increase rank if required
               if ( ! $hold_points )                   { $hold_points = $rec->points; }
               else if ( $hold_points > $rec->points ) { $hold_points = $rec->points; $rank = $rank+1; }

               if ( strlen($rec->display_name) > 0 ) { $rec->user_login = $rec->display_name; }
               $rec->user_login = preg_replace("/GRP_/","Gruppe: ",$rec->user_login);
               // Rank string
               if ( $rank == 2 ) {
                  $rec->rank_string = sprintf("%d%s %s",$rank,__("nd","wpwt"),__("place","wpwt"));
               } else {
                  $rec->rank_string = sprintf("%d%s %s",$rank,__("th","wpwt"),__("place","wpwt"));
               }

               // Different classes for the widget and the front page (style=wide)
               if ( strcmp($style,"wide")===0 ) { ?>
               <div class="wt-leaderboard wide">
               <?php } else { ?>
               <div class="wt-leaderboard">
               <?php } ?>

                  <?php
                  // If bbpress is installed: load profile link and avatar.
                  // Else show name only.
                  if ( function_exists("bbp_get_user_profile_url") ) { ?>
                  <div class="wt-leaderboard-avatar" style="width: <?php print $width; ?>%;">
                     <a href="<?php print bbp_get_user_profile_url($rec->userID); ?>" target="_self">
                     <?php print get_wp_user_avatar( $rec->userID, 96); ?>
                     </a>
                  </div>
                  <?php } else { ?>
                  <div class="wt-leaderboard-avatar" style="width: <?php print $width; ?>%;">
                     BBP Not Active
                  </div>
                  <?php } ?>
                  <div class="wt-leaderboard-info">
                     <?php
                     printf("<info>%s</info><br>\n",$rec->rank_string);
                     print  "<bar></bar>";
                     printf("<info class='color'>%s</info><br>",$rec->user_login);
                     printf("<info class='color big'>%s</info>&nbsp;",$this->number_format($rec->points,2));
                     printf("<info class='color'>%s</info><br>",__("points","wpwt"));

                     // If we are not in the wide mode: append city and date
                     if ( strcmp($style,"wide")!==0 ) {
                        print  "<bar></bar>";
                        printf("<info class='small'>%s&nbsp;%s</info><br>\n",
                               $cityObj->get('name'),$current->readable);
                     // Else (wide format) we append the daily points
                     } else {
                        $daily = array();
                        for ( $d=1; $d<=$ndays; $d++ ) {
                           $hash = sprintf("points_d%d",$d);
                           if ( is_null($rec->$hash) ) {
                              array_push($daily,"---");
                           } else {
                              array_push($daily,$this->number_format($rec->$hash,2));
                           }
                        }
                        printf("<info class='small'>%s</info><br>\n",join(" / ",$daily));
                     }
                     ?>
                  </div>
               </div>
               <?php
               // Increase counter and break if $number entries are shown.
               $counter = $counter+1;
               if ( $counter >= $number ) { break; }
            } ?>
         </div>

      <?php } # end non-compact default view

   }

   /** Loading available stations (and their dates) from the obs
    * databases. Either 'obs.live' or 'obs.archive'.
    */
   public function obsdb_get_avaliable_stations( $table = 'archive' ) {

      global $wpdb;

      // Create sql statement
      $sql = array();
      array_push($sql,"SELECT o.statnr, min(o.datumsec) AS min,");
      array_push($sql,"max(o.datumsec) AS max, s.name");
      array_push($sql,sprintf("FROM obs.%s AS o",$table));
      array_push($sql,"LEFT JOIN obs.stations AS s");
      array_push($sql,"ON o.statnr=s.statnr WHERE stint='essential'");
      array_push($sql,"GROUP BY o.statnr");
      $sql = join("\n",$sql);

      // Just return the data
      $data =  $wpdb->get_results($sql);
      for ( $i=0; $i<count($data); $i++ ) {
         $data[$i]->from = $this->date_format($data[$i]->min/86400);
         $data[$i]->to   = $this->date_format($data[$i]->max/86400);
      }
      return( $data );

   }


} // End of class!


?>
