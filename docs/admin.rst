Admin Interface
===============

This doc page explains the tools and options provided via
the `<wordpress <https://wordpress.org>`_ admin page when the 
plugin is active.

Settings
---------------------

.. _admin-settings:

The settings section allows you to control some of the basic wetterturnier
plugin settings. Please note that changes will immediately affect the
tournament. Settings you can change in here:

[Closing time]
    Closing time: the time when the bet-form will be closed.
[Tournament offset]
    actually the bet-form closes N minutes after the
    official closing time, however, can also be set to zero. Was a requirement from
    the admins while the tool was developed.
[Bet-form opening]
    how many days before the official tournament-date the
    bet-form should be opened. Only the next tournament will be shown, always. If
    you have scheduled one tournament a day, and the opening starts 2 days before,
    the bet-form will only be available for the next one, not for two, or even more
    tournaments in the future.
[Date format]
    how dates should be displayed on the frontend (date only).
[Datetime format]
    how date and time should be displayed on the frontend.
[Float number format]
    there are two options. One character can be
    specified which is used as the thousand separator (e.g., 1.035, english
    format), the other one specifies the decimal separator. 
[Terms links]
    Depending on the language we have to show our users the
    english or german version of the "Terms and Conditions" or  "Nutzungsbedingungen".
[Number of bet-days]
    currently two (e.g., on a friday-tournament bets have
    to be placed for Saturday, and Sunday). Could also be changed. Note: large
    parts of the code are ready to handle more than two days. However, some core
    methods are not. The database design only allows two forecast bet days at the
    moment. Would require quite a bit of programming to change this (Reto, June
    2017).
[Others]
    see settings page.

Scheduler
---------------------

.. _admin-scheduler:

The scheduler is the tournament planner. A tournament could take place on every
day of the year. By simply clicking the dates you can specify if there will be
an official tournament, or not. The calendar has tree different states.
Default: no tournament (blueish). Then there are two additional states, where
one specifies "that there will be a tournament" (green), and one that "there is
no official tournament" (red). While the default just does not announce
anything, the explicit "no tournament this day" will be highlighted on the
frontend.

Deactivate Users
---------------------

.. _admin-deactivate-users:

.. todo:: Description missing.

Groups
---------------------

.. _admin-groups:

Helps you managing your groups. A group can consist of several human or
automated players. The mean bet of all active group members will take place in
the tournament as well. Please note that users can join, but also leave groups.
The system will store these infos. New groups can be created, existing groups
can be set to inactive. WARNING: as soon as a group was set inactive, it can't
be reactivated again!

Group Members
---------------------

.. _admin-group-members:

While you are able to change the group settings in the group menu entry, you
can change the members/users in a group in here. It allows you to add users to
a group (set them as active group members), or remove them from a group (will
set them inactive). As long as a user is active in a group, his/her bet will be
included while computing the mean group bet for a certain tournament. Note that
you - as an administrator - are able to add random people to random groups.
However, there is a small application form where users can endorse themselves
to get a new member of an existing group. An admin has to approve/reject these
endorsements (see group application).

Users count as active group members when they have been activated on the day of
the tournament or have not been deactivated before the end of the day of the
tournament.  An example: let's assume that there was a tournament on the 24th
of June 2017 (2017-06-23). If you add a user to a group till "2017-06-23
23:59:59" it will be included in the tournament (even if the tournament starts
at 16 UTC!. Users which have been members at 00 UTC of the day of the
tournament will be included as well. If you disable a user in a certain group
on the day of the tournament (later or equal to "2017-06-23 00:00:00") it will
be included in the mean bet. In SQL this is (see python backend code,
database.get_bet_data() function): [...] AND gu.since <= '2017-06-24 00:00:00'
AND (gu.until IS NULL OR gu.until >= '2017-06-23 00:00:00')

Group Application
---------------------

.. _admin-group-applications:

For managament purposes, we allow registered (and logged in) users to endorse
themselves for a specific group. However, these endorsements need to be
approved by an administrator. Here you can find all active user-requests to get
part of a group. You are allowed to reject, or accept these endorsments. It
might be nice to inform the user on your decision.


Cities
---------------------

.. _admin-cities:

The wetterturnier plugin allows you to remove, and add cities. If you do so,
please ensure that you also add some stations to the city as a city without
stations wont get any observations. Note: each station can only be mapped to
one specific city. Stations can have specific parameter specifications - this
allows to specify cities with more, or less parameters (e.g., if you have one
city where a certain parameter wont't be observed at all). When you delete a
city, the city wont be removed from the system. The city will only be hidden
for the user (set to inactive). The data corresponding to the city won't be
lost therefore, and you are able to "switch a city off" for a certain time - if
needed. Disabled parameters: If parameters are disabled they will not be
included in the tournament (the bet-form won't show them as soon as they are
disabled).


Stations
---------------------

.. _admin-stations:

Each city needs at least one station. Observations are bounded to stations,
rather than cities. If more than only one station is set, the judging (points
the user get) is typically based on all of these - and if a user tip lies
between the range of the observed values, maximum points will be assigned for
this value. That was at least how the original Berliner wetterturnier was
designed. Each station consists of a name, a wmo station number, and a list of
parameters not observed. The name can be anything, however, the station number
is crucial. Observations are directly mapped to this station number. Parameters
which are not observed at all (e.g., total cloud cover, as there is no observer
and no instrument) can be labeled here as well. Inactive Parameters: Inactive
parameters will get inactive next midnight. Parameters can be activated and
deactivated for specific time periods, the system keeps track of it.

Parameter
---------------------

.. _admin-parameter:

These are the parameters the use have to specify. Plese note that parameters
can be set active/inactive on city level (see cities) You are allowed to add
new parameters here. But please note that each new parameter requires some
changes on (i) how the observations are prepared, and (ii) how the bets will be
judged. The interface furthermore allows to set a specific data range. E.g.,
for temperature, the allowed range lies between -50/+50 (degrees Celsius). If a
user tries to submit something outside, the "betclass" object will reject these
and inform the user. Parameters cant be deleted. If you don't need a parameter
again, change the cities-settings (and uncheck the parameter there).

Webcams
---------------------

.. _admin-webcams:

Allows to define webcams. Each webcam has to be mapped to a city.

.. note:: Please keep in mind that embedding webcams most often require
    permission from the maintainer/owner!

Bets
---------------------

.. _admin-bets:

You are allowed to change user bets. Please note that the system will store the
information who changed the values. This will be visible to the users as well
(transparency). All submitted bets will be shown in the list below, does not
matter if they were valid (all parameters are ok), or invalid (at least one
parameter missing). If the user is in the list, you are able to just edit the
bet and save the data to the database.  If a user was not able to access the
internet, and hasn't submitted ANY value, then he/she won't show up in the list
of bets below. The form on top (add new bet) can be used to insert a bet for a
specific user/city.  NOTE: this is only allowed for the current tournament, not
for older ones. The simple reason: there is a cronjob running every few minutes
computing the points - but only for the ongoing tournament. Changing old bets
would therefore have no effect on the points and obscure the data (points/bets
wont match anymore). If you change something here, the cronjob should compute
the new points within the next few minutes (not live!!).


Observations
---------------------

.. _admin-observations:

The observations of the last tournament can be changed here. Please note that
the system stores who changed values, this information will also be visible to
our users. Like for the bets the observations can only be changed for the
ongoing/last tournament as they will directly affect the points and ranking.
There is a cronjob running in the background, computing the user points every
few minutes. If you change observations, this cron-job should re-compute the
points within a few minutes, but not live.

API
---------------------

.. _admin-API:

.. todo:: Experimenta, not yet documented.

Rerun Requests
---------------------

.. _admin-rerun-requests:

.. todo:: Documentation missing.



