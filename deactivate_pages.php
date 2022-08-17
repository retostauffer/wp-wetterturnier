<?php
global $wpdb;

/**
 * Deactivate pages when the wp-wetterturnier plugin is getting
 * deactivated.
 *
 * @todo Needs a check, not yet fully tested!
 */
function wp_wetterturnier_remove_the_page($lang) {

    $the_page_title = get_option( "wetterturnier_page_title_".$lang );
    $the_page_name =  get_option( "wetterturnier_page_name_".$lang );
    
    //  the id of our page...
    $the_page_id = get_option( $the_page_name ); #'wtpage_overview_'.$lang );
    if( $the_page_id ) {
    
        wp_delete_post( $the_page_id ); // this will trash, not delete
    
    }
    delete_option("wetterturnier_page_title_".$lang);
    delete_option("wetterturnier_page_name_".$lang);
    delete_option("wetterturnier_page_id_".$lang);

}

// If the polylang plugin is active we have to remove the pages
// for EACH of the languages!
if ( add_action('is_plugin_active','polylang') ) {
    foreach ( pll_languages_list() as $lang ) {
        wp_wetterturnier_remove_the_page($lang);
    }
} else {
    wp_wetterturnier_remove_the_page('');
}

?>
