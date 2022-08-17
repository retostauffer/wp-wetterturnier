<?php
   /**
    * The 'core function' of this view: loads the defined @ref wetterturnier_webcamObjects
    * via current city (@ref wetterturnier_cityObject) and displays them if there
    * are any. If there are no images a short string will be shown.
    */
   function show_webcams() {
      global $wpdb;
      global $WTuser;

      // Access only for logged in users
      if ( $WTuser->access_denied() ) { return; }

      // Loading all webcams for current city
      $cities = $WTuser->get_all_cityObj();
      foreach ( $cities as $city ) {
         print( "<h2>".$city->get("name")."</h2>" );
         $webcams = $wpdb->get_results( sprintf("SELECT ID FROM %swetterturnier_webcams "
                    ." WHERE cityID = %d;", $wpdb->prefix, (int)$city->get("ID")) );
         if ( count($webcams) == 0 ) {
            print __("No webcams defined for","wpwt")." ".$city->get("name").".";
         }
         
         // Load webcam objects
         $objects = array();
         foreach ( $webcams as $rec ) {
            $webcamObj = new wetterturnier_webcamObject( (int)$rec->ID );
            $webcamObj->display_webcam(1200);
         }
      }
   }

//show all webcams
show_webcams();

?>
