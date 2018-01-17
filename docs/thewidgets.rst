
Widgets
========

In `wordpress <https://wordpress.org>`_ so called `widgets <https://codex.wordpress.org/Widgets_API>`_
are small applications which come along with the plugin and can be placed/configured
via the wordpress admin interface via "Design > Widgets".
We've added several to provide the latest or most
important information on the website.


Webcam Widget
-------------

.. _widgets-webcams:

`webcams.php <https://github.com/retostauffer/wp-wetterturnier/blob/master/user/widgets/webcams.php>`_
extens the wordpress `WP_Widget` class. The widget is loading the webcams from the
:ref:`*wetterturnier_webcams <mysql-table-webcams>` database table if there are any
for the current :ref:`cityObject <api-cityObject>` using the corresponding
:ref:`webcamObject <api-webcamObject>` objects.

Each webcam is attached to one specific city via it's ``cityID`` and is
defined via three attributes: url to the webcam image (has to be a static url),
a source url, and a short description. The latter two will be used to set the
source link below the live image.

Uses:

* :ref:`wetterturnier_cityObject <api-cityObject>`: current city.
* :ref:`wetterturnier_webcamObject <api-webcamObject>`: defined webcams if there are any.

.. phpautoclass:: WP_wetterturnier_widget_webcams
    :filename: ../user/widgets/webcams.php
    :members:
    :undoc-members:

Leaderboard Widget
------------------

.. _widgets-leading:

`leading.php <https://github.com/retostauffer/wp-wetterturnier/blob/master/user/widgets/leading.php>`_
extens the wordpress ``WP_Widget`` class. The widget is loading the players with the highest
points given a certain time and active city. Uses the data from the
:ref:`*wetterturnier_betstat <mysql-table-betstat>` database table.

.. todo:: Currently uses the method  :php:meth:`userclass::show_leading`.
        Should be decoupled and rather use the new (not yet scripted)
        rankingclass and procude the output directly within
        :php:meth:`WP_wetterturnier_widget_leading::show_leading`.

Makes use of the avatar image and bbpress profile link when displaying the data.

Uses:

* :ref:`wetterturnier_cityObject <api-cityObject>`: current city.

.. phpautoclass:: WP_wetterturnier_widget_leading
    :filename: ../user/widgets/leading.php
    :members:
    :undoc-members:


Lightning Activity
------------------

.. _widgets-blitzortung:

`blitzortung.php <https://github.com/retostauffer/wp-wetterturnier/blob/master/user/widgets/blitzortung.php>`_
also extends the wordpress ``WP_Widget`` class. 
The widget does two things: see if new data are available (data-stream not dead)
and if data are available check wether there was is lightning activity in the surrounding
of the city. If so, a small image will be displayed.


.. todo:: Provide some brief information about the data set and where
        these routines are running and/or can be found on the wetterturnier
        server (cross-ref style).

Uses:

* :ref:`wetterturnier_cityObject <api-cityObject>`: current city.

.. phpautoclass:: WP_wetterturnier_widget_blitzortung
    :filename: ../user/widgets/blitzortung.php
    :members:
    :undoc-members:




Latest Observations
-------------------

.. _widgets-latestobs:

`latestobs.php <https://github.com/retostauffer/wp-wetterturnier/blob/master/user/widgets/latestobs.php>`_
also extends the wordpress ``WP_Widget`` class. 
The widget does two things: see if new data are available (data-stream not dead)
and if data are available check wether there was is lightning activity in the surrounding
of the city. If so, a small image will be displayed.


.. todo:: Provide some brief information about the data set and where
        these routines are running and/or can be found on the wetterturnier
        server (cross-ref style).

Uses:

* :ref:`wetterturnier_cityObject <api-cityObject>`: current city.
* :ref:`wetterturnier_stationObject <api-stationObject>`: stations for city.
* :ref:`wetterturnier_latestobsObject <api-latestobsObject>`: fetching latest observations from
  :ref:`obs database <mysql-database-obs>`.

.. phpautoclass:: WP_wetterturnier_widget_latestobs
    :filename: ../user/widgets/latestobs.php
    :members:
    :undoc-members:


Tournament Calendar
-------------------

.. _widgets-tournaments:

`tournaments.php <https://github.com/retostauffer/wp-wetterturnier/blob/master/user/widgets/tournaments.php>`_
also extends the wordpress ``WP_Widget`` class. 
Displays the tournament calendar with the upcoming tourmanet dates.
In addition, the bet-counts (submitted partial/full forecasts for the upcoming
tournament) are shown.


.. todo:: Check whether it would make sense to move the data request
        into the new and not yet coded rankingclass.

.. phpautoclass:: WP_wetterturnier_widget_tournaments
    :filename: ../user/widgets/tournaments.php
    :members:
    :undoc-members:


Private Messages
----------------

.. _widgets-bbpmessages:

`bbpmessages.php <https://github.com/retostauffer/wp-wetterturnier/blob/master/user/widgets/bbpmessages.php>`_
also extends the wordpress ``WP_Widget`` class. 
Requires an active installation of the ``bbpmessages`` plugin. If the plugin is
not installed or at least ont active this widget will print out a message telling
you that you might not have installed the dpeendency. Else the plugin is checking
if there are new unread privat messages in the in-box and displays either a number
or a message that there are no new messages.

**Note** the widget is only visible when the user is logged in (as only logged in users
are able to retrieve and/or send messages).

.. todo:: Check whether it would make sense to move the data request
        into the new and not yet coded rankingclass.

.. phpautoclass:: WP_wetterturnier_widget_bbpmessages
    :filename: ../user/widgets/bbpmessages.php
    :members:
    :undoc-members:










