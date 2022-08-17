// Initialize demo table
jQuery(document).on('ready',function($) {
    (function($) {
        // Grouping for group tables
        $.validator.setDefaults({
          success: "valid"
        });

        // Daystrings. Will be replaced by the wetterturnier
        // plugin so that we can have multilingual output for
        // the end user
        var validate_daystr1 = '%replace_daystr1%';
        var validate_daystr2 = '%replace_daystr2%';

        // Then initialize the validation
        $("#wetterturnier-bet-form").validate({
            rules: {
                %replace_with_rules% 
            },
            errorPlacement: function(error, element) {      
                var day = element.context.name.substr(3,1);
                if ( parseInt(day) == 1 ) {
                    var daystr = validate_daystr1;
                } else {
                    var daystr = validate_daystr2;
                }
                error[0].innerHTML = daystr + ": " + error[0].innerHTML
                $(element).closest('tr').find('span.error.day'+day)
                    .html( error );
            },
        });

    })(jQuery);
});
