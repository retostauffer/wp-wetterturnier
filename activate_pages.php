<?php
// Activate some pages for the wp-wetterturnier frontend

global $wpdb;


function wp_wetterturnier_add_the_page($lang) {

    if ( strlen($lang) === 0 ) {
        $the_page_title = 'Wetterturnier';
    } else {
        $the_page_title = 'Wetterturnier '.$lang;
    }
    $the_page_name = 'wtpage_overview_'.$lang;
    
    // the menu entry...
    delete_option("wtterturnier_page_title_".$lang);
    add_option(   "wtterturnier_page_title_".$lang, $the_page_title, '', 'yes');
    // the slug...
    delete_option("wetterturnier_page_name_".$lang);
    add_option(   "wetterturnier_page_name_".$lang, $the_page_name, '', 'yes');
    // the id...
    delete_option("wetterturnier_page_id_".$lang);
    add_option(   "wetterturnier_page_id_".$lang, '0', '', 'yes');
    
    $the_page = get_page_by_title( $the_page_title );
    
    if ( ! $the_page ) {
    
        // Create post object
        $_p = array();
        $_p['post_title'] = $the_page_title;
        $_p['post_content'] = "This text may be overridden by the plugin. You shouldn't edit it.";
        $_p['post_status'] = 'publish';
        $_p['post_type'] = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status'] = 'closed';
        $_p['post_category'] = array(1); // the default 'Uncatrgorised'
    
        // Insert the post into the database
        $the_page_id = wp_insert_post( $_p );
    
    }
    else {
        // the plugin may have been previously active and the page may just be trashed...
        $the_page_id = $the_page->ID;
        //make sure the page is not trashed...
        $the_page->post_status = 'publish';
        $the_page_id = wp_update_post( $the_page );
    
    }
    
    delete_option( 'wetterturnier_page_id_'.$lang );
    add_option(    'wetterturnier_page_id_'.$lang, $the_page_id );

    // add language if necessary
    if ( strlen($lang) > 0 ) {
        pll_set_post_language($the_page_id,$lang);
    }

}

// If the polylang plugin is active we have to add the pages
// for EACH of the languages!
if ( add_action('is_plugin_active','polylang') ) {
    foreach ( pll_languages_list() as $lang ) {
        wp_wetterturnier_add_the_page($lang);
    }
} else {
    wp_wetterturnier_add_the_page('');
}

?>
