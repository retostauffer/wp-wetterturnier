Databases and Tables
====================

We do have two databases in use to provide all functionalities.
**One database** is the database on which `wordpress <https://wordpress.org>`_
is running. In this documentation this database will be called
`wpwt` for :ref:`WordPress WetterTurnier"<mysql-database-wpwt>`.
The data tables for the wetterturnier plugin are stored using
the wordpress database prefix (classically ``wp_`` but depends on your installation).

**The second database** is the database where all the live observations
are coming in. Depending on the station we are retrieving the observations
on an hourly temporal resolution, many parameters, and even more stations,
from which only a very specific small subset is used for the
tournament itself. To keep the data structure clean the live observations
are stored in a second database, from now on called ``obs`` database
for :ref:`OBServation <mysql-database-obs>` database. 
There is a python script in the `wetterturnier backend <https://github.com/retostauffer/wetterturnier-backend>`_ which loads the required observational subset from the ``obs`` database
and stores them in the ``wpwt`` database for operational use (compute the points).

.. note:: Please note that, as we have two separate databases, you need to
    grant SELECT access to the wordpress database user to the ``obs`` database
    to be able to use all wordpress wetterturnier plugin functions. This is
    no requirement, if you do not have such a data access you simply have to
    disable all features using this (mainly :doc:`thewidgets` and some data views).


The Observation Database (`obs`)
--------------------------------

.. _mysql-database-obs:

.. todo:: Short information about the table structure and where to find the
        details (cross-ref style) as this is all done by the BUFR-decoder
        script/package.


The Wordpress Database (`wpwt`)
-------------------------------

.. _mysql-database-wpwt:

.. todo:: A description would be nice ;)

.. _mysql-table-webcams:

The `*wetterturnier_webcams` table contains a list of webcams
displayed by the :ref:`webcam widget <widgets-webcams>` if there are any.

.. todo:: Provide detailed table description here and what to find
        where and why and who fills in/deletes the data sets.

















