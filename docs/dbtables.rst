Database Tables
===============

We do have two databases in use to provide all functionalities.
**One database** is the database on which `wordpress <https://wordpress.org>`_
is running. In this documentation this database will be called
`wpwt` for :ref:`WordPress WetterTurnier"<database-wpwt>`.
The data tables for the wetterturnier plugin are stored using
the wordpress database prefix (classically ``wp_`` but depends on your installation).

**The second database** is the database where all the live observations
are coming in. Depending on the station we are retrieving the observations
on an hourly temporal resolution, many parameters, and even more stations,
from which only a very specific small subset is used for the
tournament itself. To keep the data structure clean the live observations
are stored in a second database, from now on called ``obs`` database
for :ref:`OBServation <database-obs>` database.
There is a python script in the `wetterturnier backend <https://github.com/retostauffer/wetterturnier-bac
kend>`_ which loads the required observational subset from the ``obs`` database
and stores them in the ``wpwt`` database for operational use (compute the points).

.. note:: Please note that, as we have two separate databases, you need to
    grant SELECT access to the wordpress database user to the ``obs`` database
    to be able to use all wordpress wetterturnier plugin functions. This is
    no requirement, if you do not have such a data access you simply have to
    disable all features using this (mainly :doc:`thewidgets` and some data views).



* :ref:`Wordpress Databse <database-wpwt>` contains the Wetterturnier Plugin Database tables:
    * Table :ref:`wp_users <table-wp_users>`: Text ...
    * Table :ref:`wp_wetterturnier_api <table-wp_wetterturnier_api>`: Text ...
    * Table :ref:`wp_wetterturnier_bets <table-wp_wetterturnier_bets>`: Text ...
    * Table :ref:`wp_wetterturnier_betstat <table-wp_wetterturnier_betstat>`: Text ...
    * Table :ref:`wp_wetterturnier_cities <table-wp_wetterturnier_cities>`: Text ...
    * Table :ref:`wp_wetterturnier_dates <table-wp_wetterturnier_dates>`: Text ...
    * Table :ref:`wp_wetterturnier_groups <table-wp_wetterturnier_groups>`: Text ...
    * Table :ref:`wp_wetterturnier_groupusers <table-wp_wetterturnier_groupusers>`: Text ...
    * Table :ref:`wp_wetterturnier_obs <table-wp_wetterturnier_obs>`: Text ...
    * Table :ref:`wp_wetterturnier_param <table-wp_wetterturnier_param>`: Text ...
    * Table :ref:`wp_wetterturnier_rerunrequest <table-wp_wetterturnier_rerunrequest>`: Text ...
    * Table :ref:`wp_wetterturnier_stationparams <table-wp_wetterturnier_stationparams>`: Text ...
    * Table :ref:`wp_wetterturnier_stations <table-wp_wetterturnier_stations>`: Text ...
    * Table :ref:`wp_wetterturnier_webcams <table-wp_wetterturnier_webcams>`: Text ...

* :ref:`Observation Database <database-obs>` contains the following important tables:
    * Table :ref:`archive <table-archive>`: Text ...
    * Table :ref:`live <table-live>`: Text ...

Relationship
============

Not yet having a visual representation of the database structure but trying
to explain the relationship in some words. All user data are linked to the
:ref:`wordpress users table <table-wp_users>` usint the user ``ID``. The wetterturnier
structure can be summarized as follows:

* Several :ref:`cities <table-wp_wetterturnier_cities>` can be specified for which
  forecasts can be submitted. Each :ref:`cities <table-wp_wetterturnier_cities>` has its
  own unique *city ID*.
* To each city one or more :ref:`weather stations <table-wp_wetterturnier_stations>`
  can be linked to identified by a unique *station ID*.
* A set of :ref:`parameters to forecast <table-wp_wetterturnier_param>` can be specified
  which have to be forecasted. Note that for each individual city or station the parameters
  can be disabled/enabled (e.g., when no observations for this parameter are available).
  As cities and stations parameters have their unique *parameter ID*.
* :ref:`Tournament dates <table-wp_wetterturnier_dates>` are specified via *tournament dates*
  (``tdate``'s) using *days since 1970-01-01*. Tournaments can therefore be held on a daily
  basis (but not sub-daily, not two rounds per day).
* :ref:`User forecasts <table-wp_wetterturnier_bets>` are made for a specific
  :ref:`city <table-wp_wetterturnier_cities>`, 
  :ref:`day <table-wp_wetterturnier_dates>`, and
  :ref:`parameter <table-wp_wetterturnier_param>` using the 
  :ref:`wordpress user ID <table-wp_users>` to link the forecasts to the users.
* Forecasts are summarized and stored in the :ref:`betstat table <table-wp_wetterturnier_betstat>`
  where the sum of the points will be added by the
  `wetterturnier backend judging <https://github.com/retostauffer/wetterturnier-backend>`_.




Wordpress Database 
===================

.. _database-wpwt:

wp_users
------------------------------------------------

.. _table-wp_users:

.. include:: dbtables/wp_users.rsx

wp_wetterturnier_api
------------------------------------------------

.. _table-wp_wetterturnier_api:

.. include:: dbtables/wp_wetterturnier_api.rsx

wp_wetterturnier_bets
------------------------------------------------

.. _table-wp_wetterturnier_bets:

.. include:: dbtables/wp_wetterturnier_bets.rsx

wp_wetterturnier_betstat
------------------------------------------------

.. _table-wp_wetterturnier_betstat:

.. include:: dbtables/wp_wetterturnier_betstat.rsx

wp_wetterturnier_cities
------------------------------------------------

.. _table-wp_wetterturnier_cities:

.. include:: dbtables/wp_wetterturnier_cities.rsx

wp_wetterturnier_dates
------------------------------------------------

.. _table-wp_wetterturnier_dates:

.. include:: dbtables/wp_wetterturnier_dates.rsx

wp_wetterturnier_groups
------------------------------------------------

.. _table-wp_wetterturnier_groups:

.. include:: dbtables/wp_wetterturnier_groups.rsx

wp_wetterturnier_groupusers
------------------------------------------------

.. _table-wp_wetterturnier_groupusers:

.. include:: dbtables/wp_wetterturnier_groupusers.rsx

wp_wetterturnier_obs
------------------------------------------------

.. _table-wp_wetterturnier_obs:

.. include:: dbtables/wp_wetterturnier_obs.rsx

wp_wetterturnier_param
------------------------------------------------

.. _table-wp_wetterturnier_param:

.. include:: dbtables/wp_wetterturnier_param.rsx

wp_wetterturnier_rerunrequest
------------------------------------------------

.. _table-wp_wetterturnier_rerunrequest:

.. include:: dbtables/wp_wetterturnier_rerunrequest.rsx

wp_wetterturnier_stationparams
------------------------------------------------

.. _table-wp_wetterturnier_stationparams:

.. include:: dbtables/wp_wetterturnier_stationparams.rsx

wp_wetterturnier_stations
------------------------------------------------

.. _table-wp_wetterturnier_stations:

.. include:: dbtables/wp_wetterturnier_stations.rsx

wp_wetterturnier_webcams
------------------------------------------------


The `*wetterturnier_webcams` table contains a list of webcams
displayed by the :ref:`webcam widget <widgets-webcams>` if there are any.

.. todo:: Provide detailed table description here and what to find
        where and why and who fills in/deletes the data sets.

.. _table-wp_wetterturnier_webcams:

.. include:: dbtables/wp_wetterturnier_webcams.rsx

Observation Database
================================

.. _database-obs:

archive
------------------------------------------------

.. _table-archive:

.. include:: dbtables/archive.rsx

live
------------------------------------------------

.. _table-live:

.. include:: dbtables/live.rsx

