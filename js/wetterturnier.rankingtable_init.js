

// Initialize demo table
jQuery(document).on('ready',function() {

    // Copy jQuery to $
    jQuery("div.wt-ranking-container").each(function() {
        jQuery(this).show_ranking(jQuery.ajaxurl, jQuery.parseJSON($(this).attr("args")));
    });

});
