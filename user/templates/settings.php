<div class="wrap">
    <h2>WP Wetterturnier Plugin</h2>
    <form method="post" action="options.php"> 
        <?php @settings_fields('wp_wetterturnier-group'); ?>
        <?php @do_settings_fields('wp_wetterturnier-group'); ?>

        <h2>Checking Tournament Calendar</h2>
        <?php
        if ( !class_exists('WpSimpleBookingCalendar') ) {
            echo '<span style="color: red;">';
            echo 'Booking Calendar NOT found!';
            echo '</span>';
        } else {
            echo '<span style="color: green;">';
            echo 'Booking Calendar found!';
            echo '</span>';

            // Next friday
            $fri = date('Y-m-d', strtotime('next Friday'));
            $friYear  = (int)date('Y', strtotime('next Friday'));
            $friMonth = (int)date('m', strtotime('next Friday'));
            $friDay   = (int)date('d', strtotime('next Friday'));

            // Getting entry from the WpSimpleBookingCalendar
            $test = new WpSimpleBookingCalendar_Model();
            $out = $test->getCalendar();
            $out = json_decode( $out['calendarJson'] );
            $status = (!empty($out->{'year'.$friYear}->{'month'.$friMonth}->{'day'.$friDay}) ? $out->{'year'.$friYear}->{'month'.$friMonth}->{'day'.$friDay} : 'free');

            // Message
            echo ' Next Friday is '.$fri.'.<br>';
            switch ( $status ) {
                case 'free':
                    echo '<span style="color: red;">NOTHING SET IN '
                        .'TURNAMENT CALENDAR!!!</span>';
                    break;
                case 'changeover':
                    echo 'Next weekend is a turnament weekend.';
                    break;
                case 'booked':
                    echo 'Next weekend is <span style="color: red;">NO '
                        .'turnament weekend.</span>';
                    break;
                default:
                    echo 'Undefined status ['.$status.']';
            }
        }
        ?>

        <?php do_settings_sections('wp_wetterturnier'); ?>

        <?php @submit_button(); ?>
    </form>
</div>
