<h1>Wetterturnier Webcams</h1>

<help>
   Allows to define webcams. Each webcam has to be mapped to a city.
</help>

<?php
// The citiesview handles different
// things all for the group manipulating.
// Actions are transported by the _GET "action" argument.
// If set, try to call the correct page.
// If no action is set (default), view grop list.

global $wpdb;
global $WTadmin;


function include_action_file( $filename ) {
    include(sprintf("%s/../templates/%s", dirname(__FILE__),$filename));
}

// - If action is set
if ( ! empty($_GET['action']) ) {

    // Now depending on action we have to do some stuff
    if ( $_GET['action'] == "edit" && ! empty($_GET['cam']) ) {
        include_action_file('webcams_edit.php');
    // Delete an entry
    } else if ( $_GET['action'] == 'delete' & ! empty( $_GET['cam'] ) ) {

        $wpdb->delete( sprintf("%swetterturnier_webcams",$wpdb->prefix),
                        array('ID'=>$_GET['cam']) );

        echo "<div id='message' class='updated fade'><p><strong>"
            .__("Webcam removed.","wpwt")
                ."</strong></p></div>";

        // Show the table now
        include_action_file('webcams_list.php');
    }


// - Show grup_list else 
} else {

    // Update a group
    if ( ! empty($_POST['what']) && $_POST['what'] == 'edit' ) {

        // getting url, source, description 
        $uri    = stripslashes(esc_html((string)$_REQUEST['uri'] ));
        $source = stripslashes(esc_html((string)$_REQUEST['source']));
        $desc   = stripslashes(esc_html((string)$_REQUEST['desc']));

        $WTadmin->insertonduplicate( sprintf('%swetterturnier_webcams',$wpdb->prefix),
                    array('ID'=>$_POST['id'],'uri'=>$uri,'source'=>$source,'desc'=>$desc) );

    
    // Add a new webcam 
    } else if ( ! empty($_REQUEST['uri']) & ! empty($_REQUEST['city']) ) {
    
        // getting url, source, description 
        $uri    = stripslashes(esc_html((string)$_REQUEST['uri'] ));
        $source = stripslashes(esc_html((string)$_REQUEST['source']));
        $desc   = stripslashes(esc_html((string)$_REQUEST['desc']));
        $cityID = stripslashes(esc_html((string)$_REQUEST['city']));


        // upsert
        $wpdb->insert( sprintf('%swetterturnier_webcams',$wpdb->prefix),
                    array('cityID'=>$cityID,'uri'=>$uri,'source'=>$source,'desc'=>$desc) );

    // Forgot to choose city
    } else if ( ! empty($_REQUEST['uri']) & empty($_REQUEST['city']) ) {

        echo "<div id='message' class='error fade'><p><strong>"
            .__("Webcam not added. Seems that you forgot to select a city.","wpwt")
                ."</strong></p></div>";
    
    }

    // Show the table now
    include_action_file('webcams_list.php');

}

?>
