Data Handling Classes
=====================

Unfortunately some parts of the source code are a bit unorganized as the
project grew over several years and, alongside the project, the php programming
skills got better and better.

City Handler
------------

.. phpautoclass:: wetterturnier_cityObject
    :filename: ../classes.php
    :members:

Station Handler
---------------

.. phpautoclass:: wetterturnier_stationObject
    :filename: ../classes.php
    :members:

Parameter Handler
-----------------

.. phpautoclass:: wetterturnier_paramObject
    :filename: ../classes.php
    :members:

Webcam Handler
--------------

.. phpautoclass:: wetterturnier_webcamObject
    :filename: ../classes.php
    :members:

Group Handler
-------------

.. phpautoclass:: wetterturnier_groupsObject
    :filename: ../classes.php
    :members:

Latest Observations
-------------------

.. phpautoclass:: wetterturnier_latestobsObject
    :filename: ../classes.php
    :members:

Ranking Class
-------------

A php class which computes and returns the data for all kinds
of ranking tables (e.g., weekend ranking, season ranking, rankings
for multiple cities, ...).

This class is only used by ``ranking_ajax``, the ajax call handling
ranking requests and should not be used/loaded in-line.

.. note::
    **How the integration in wordpress is done:**
    The :php:class:`wetterturnier_userclass` registers an action called
    `ranking_ajax` which makes use of the :php:class:`wetterturnier_rankingObject`.

    How it works:

    * :file:`js/wetterturnier.rankingtable_init.js` is looking for html objects
      via ``div.wt-ranking-container`` selector. Thise ``div``'s have an ``args``
      attribute containing the necessary parameters to create the ranking (a
      JSON array which defines for which city and which date the ranking should
      be computed).
    * For each ``div.wt-ranking-container`` object the jQuery function
      ``show_ranking(...)`` is called (:file:`js/wetterturnier.rankingtabe.js`)
      which makes performs the data request. ``show_ranking(...)`` triggers the
      ``ranking_ajax`` function via ajax request.
    * ``ranking_ajax`` creates a new instance of the :php:class:`wetterturnier_rankingObject`
      class, performs the calculation of the ranks (or loads the cached file if 
      caching is enabled) and returns a JSON object to ``show_ranking(...)`` which
      is then used to create the html output on the website.

What the ranking will display is controlled via a set of arguments.
Most important is the definition of the city/cities, and a set of
parameters to control the date periods. For some cases we also want to
have a trend, thus two different time periods are needed. If set, the
rank for the `previous` period is calculated, the rank for the `current`
period is calculated, and the difference between these two is the trend.

This is controlled via ``from``, ``to``, ``from_prev``, and ``to_prev``.
Each of these elements is an integer tournament date (days since 1970-01-01).

* ``from`` and ``to`` define the period for which the current rank should
  be computed. For one specific weekend both arguments are the same.
* ``from_prev`` and ``to_prev`` define the previous period. If one of them
  is ``Null`` the previous round is not defined and no rank for this period
  is calculated (and thus, no tend is shown). If set these two arguments
  also specify the beginning and end of the period (tournament dates,
  days since 1970-01-01). For the weekend ranking both arguments are
  the same, and both contain the tournament date for the tournament just
  one weekend before ``from`` and ``to``. For the yearly ranking table
  ``from`` is typically identical to ``from_prev``, and ``to_prev``
  defines the one tournament one week before ``to``.


.. phpautoclass:: wetterturnier_rankingObject
    :filename: ../rankingclass.php
    :members:

