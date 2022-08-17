Shortcodes
===========

The Plugin provides a range of shortcodes. Shortcodes are strings which
will be replaced by wordpress by the output of a specific function, e.g.,
``[wetterturnier_ranking type="weekend"]`` displays the weekend ranking.

These shortcodes can be inserted in the posts and pages to arrange, rarrange,
and adjust the look and view of your page and to provide the contentd whereever
needed. A list of shortcodes:


Ranking tables
----------------

This shortcode shows the different ranking tables. As we are offering a set of
different view styles, and ranking types, there are some extra arguments for
this shortcode. The following options are available:

Options:

[type]
    string. Default is "weekend". Others can be defined as "season",
    "total", "cities", "yearly".
[limit]
    default false. If set to a positive integer, only the best N
    entries will be shown.
[city]
    default false. If false, the current city will be used. Can be set
    to any defined city ID (integer) to display ranking for a certain city.
[cities]
    default 1,2,3. Comma separated integer list of defined city ID's.
    Is only affecting ranking tables of type="cities".
[slim]
    default false. Can be set to true to hide some of the columns.
[weeks]
    default 15. Can be set to any positive integer. Defines the number
    of tournaments to be included for type="total". Only affects this type.
[hidebuttons]
    default false. Can be set to true. If true, the navigation
    wont be shown.

Examples:

* ``[wetterturnier_ranking type="weekend" hidebuttons=true]``
* ``[wetterturnier_ranking type="season" city=3 limit=10 slim=true ]``
* ``[wetterturnier_ranking type="cities" cities=1,3,4 slim=true ]``

Judging forms (testing the rules)
-----------------------------------

The user can test the operational judgingclass (the class computing the points
for a given set of observations/bets). Shows a set of html forms for the
parameters. Via ajax the wetterturnier plugin will call the python backend
judgingclass, returning the points. A small set of options can be set: extra:
default false. If set to true, an "extra obs" field will be shown. This is
needed for some parameters where the computation of the points is based on a
second parameter.

Options:

[parameter]
    Parameter Option: default false. String, parameter name, like e.g.,
    ``"TTm"``, ``"TTn"``.

Examples:

* ``[wetterturnier_judgingform <options>]``
* ``[wetterturnier_judgingform parameter="TTm"]``
* ``[wetterturnier_judgingform parameter="TTn"]``
* ``[wetterturnier_judgingform parameter="dd" extra=true]``

Links to user bbp profile
--------------------------

Display the user profile link to the bbpress profile page.  ``user="..."`` is
required. String, specifying the username. This command will create a link to
the user profile page which is actually an extended version of the bbpress
profile page (with some extra wetterturnier statistics and profile infos).

Options:

[user]
    String, username (``user_login`` from :ref:`table-wp_users`).

Examples:

* ``[wetterturnier_profilelink user="ww75"]`` Display user profile link for user ``ww75``.
* ``[wetterturnier_profilelink user="reto"]`` Display user profile link for user ``reto``.

Different views (without options)
----------------------------------

* ``[wetterturnier_register]`` Registration form.
* ``[wetterturnier_synopsymbols]`` Synop symbol table.
* ``[wetterturnier_mapsforecasts]`` Forecast-maps navigation.
* ``[wetterturnier_archive]`` The archive (bets and points)
* ``[wetterturnier_current]`` Current tournament overview.
* ``[wetterturnier_groups]`` A set of group-tables containing names and members.
* ``[wetterturnier_applygroup]`` Form where users can apply for a membership for a group.
* ``[wetterturnier_bet]`` The bet form.
* ``[wetterturnier_exportobslive]`` Small form for exporting latest observations in the live obs table.
* ``[wetterturnier_exportobsarchive]`` Small form for exporting archived observations (obs archive table).
* ``[wetterturnier_statplayer]`` Small interface to create R-statistics-plots (devel).
* ``[wetterturnier_obstable]`` Table containing the latest observations.
* ``[wetterturnier_meteogram]`` Meteogram navigation.
* ``[wc]...[/wc]`` Shows "..." in code-styling.
* ``[wetterturnier_stationinfo]`` Shows a list of all cities and their corresponding stations (used in rules/Spielregeln).
* ``[wetterturnier_stationparamdisabled]`` Shows a list of the stations in use have disabled parameters, parameters which are currently excluded from the tournament. Used in rules/Spielregeln.
