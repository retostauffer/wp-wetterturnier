<?php

//get page ID (for email plugin)
//global $post; echo $post->ID;

// Access only for logged in users
global $WTuser;
if ( $WTuser->access_denied() ) { return; }

// Get displayed user id first
$userID     = get_current_user_id();
$user_data  = get_userdata($userID);
$user_login = $user_data->user_login;
$user_mail  = $user_data->user_email;

global $wpdb;
$db = $wpdb->get_results("SELECT * FROM wp_wetterturnier_metmaps WHERE userID = " . $userID);

if (!empty($db)) {
    $db = $db[0];
    $submitted_mail = $db->user_mail;
    $mail_sent      = $db->mail_sent;
    $_POST["mail"]  = $submitted_mail;
    
    if ($mail_sent) {
        $text = __("<b>NOTE</b>: An email with your username (<b>%s</b>) and mail address (<b>%s</b>) has been sent to MetMaps support. You should receive your login data within the next 24h. Please be patient! If it takes longer than 2 days, please contact an <a href='%s/team'>admin</a>. ","wpwt");
        echo sprintf($text, $user_login, $submitted_mail, site_url() );
        print(__("Did you enter your e-mail incorrectly? Please change it and click on '<b>RESUBMIT</b>'.","wpwt")."<br><br>");
        print( __("After you received your login data via mail you can login here","wpwt") . 
            ": <b><a href='https://fi.metmaps.eu/'>fi.metmaps.eu</a></b><br><br>" );
        $submit = __("RESUBMIT","wpwt");
    } else {
        $submit = __("SUBMIT","wpwt");
    }
} else {
    $submit = __("SUBMIT","wpwt");
    if ( !isset($_POST["mail"]) ) {
        $_POST["mail"] = $user_mail;
    }
}

echo __("Enter or edit email:","wpwt");

?>

<form action="" method="post">
    <input name="mail" type="text" id="field-mail" value="<?php echo $_POST["mail"]; ?>" />
    <input name="submit" type="submit" id="submit-button" value="<?php echo $submit; ?>" />
</form>

<script>

$(window).ready(function() {
    // run on form submission
    $("form").submit(function (e) {
        e.preventDefault();
        setTimeout(function () {
            console.log("After timeout")
            console.log($('#field-mail').val())
            $.ajax({
                type: "POST",
                dataType: "html",
                url: "<?php print admin_url('admin-ajax.php'); ?>",  
                data: { action: 'metmaps_ajax', mail: $('#field-mail').val() },
                success: function(state) {
                    console.log(state)
                    alert(state)
                    location.reload()
                }
            })
        }, 100);
    })
})

</script>
