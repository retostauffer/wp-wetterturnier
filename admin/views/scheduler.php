<?php global $wpdb, $WTadmin; ?>

<h1>Tournament Scheduler</h1>

<help>
   The <b>scheduler</b> is the tournament planner. A tournament could take
   place on every day of the year. By simply clicking the dates you can specify
   if there will be an official tournament, or not. 

   The calendar has tree different states. Default: no tournament (blueish). Then there
   are two additional states, where one specifies "that there will be a tournament" (green),
   and one that "there is no official tournament" (red). While the default just does not
   announce anything, the explicit "no tournament this day" will be highlighted
   on the frontend.
</help>

<h2>How this works</h2>
<div class="wpwt-admin-info">
    The scheduler or tournament date manager is necessary to define
    the tournaments in the future. For the past, the wetterturnier plugin
    takes the days it finds in the <i>bets/tips</i> database.<br>
     -->What you can do here: you <b>can define on which days a tournament
    takes place</b>. It is always the <b>day where the users have to
    place the bets</b>. For example: for a weekend tournament select
    <b>Friday</b>, not Saturday or Sunday. The two following days are
    then the days where to set the bets.<br>
    Actually you could also specify a Monday to open a tournament for Tuesday/Wednesday.
    <br>
</div>
<h2>Pick your dates</h2>
<div class="wpwt-admin-info">
    Simply click to set. If they are kind of <span style='color: blue;'>blueish</span>
    there is nothing defined. If you click once, the color turns into
    <span style='color: green;'>green</span> indicating a day where
    <b>a tournament will take place</b>. If you click a second time, the color
    turns into <span style='color: red;'>red</span> indicating that
    <b>this is one of the rare weekends where explicitly no tournament
    takes place</b>. By clicking again you can de-set this day (turns back
    into <span style='color: blue;'>blueish</span>).
</div>

<div id='wetterturnier_tournaments' class='ll-skin-nigran'></div>
