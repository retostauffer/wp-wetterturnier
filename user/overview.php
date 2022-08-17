<?php

// Getting date of last friday
$last_fri = strftime('%Y-%m-%d',strtotime('last friday'));


if ( ! empty($_SESSION['wetterturnier_city']) ) {

    echo "blabla choose";

} else {

    echo "variable is set";
}
?>
