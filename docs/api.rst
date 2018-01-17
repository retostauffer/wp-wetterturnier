
API
===

misc classes
------------

.. note:: tbc

generalclass
-------------

.. _api-generalclass:

This is **the main wordpress plugin class** and contains a range of
important/necessary methods for the data handling, website content,
and so far and so on. This class will be extended by both, the 
:php:class:`adminclass` and :php:class:`userclass` shown later.

.. phpautoclass:: wetterturnier_generalclass
    :filename: ../generalclass.php
    :members:
    :undoc-members:

adminclass
----------

.. _api-adminclass:

The adminclass builds on :php:class:`generalclass` (extends the
:php:class:`generalclass`) and provides a set of admin-specific
functions and attributes.
These are only for the wordpress admin-interface.

.. phpautoclass:: wetterturnier_adminclass
    :filename: ../admin/adminclass.php
    :members:
    :undoc-members:

userclass
---------

.. _api-userclass:

The userclass builds on :php:class:`generalclass` (extends the
:php:class:`generalclass`) and provides a set of frontend-specific
methods. This class is used for visitors (not logged in users),
members (logged in users) and administrators as long as they browse
on the frontend.

.. phpautoclass:: wetterturnier_userclass
    :filename: ../user/userclass.php
    :members:
    :undoc-members:

cityObject
-----------

.. _api-cityObject:

Handling the different cities defined in the wetterturnier.

.. phpautoclass:: wetterturnier_cityObject
    :filename: ../classes.php
    :members:
    :undoc-members:

stationObject
-------------

.. _api-stationObject:

Handling single weather stations.

.. phpautoclass:: wetterturnier_stationObject
    :filename: ../classes.php
    :members:
    :undoc-members:

paramObject
-----------

.. _api-paramObject:

Handling the different parameters (e.g., minimum temperature, or
pressure at 12 UTC).

.. phpautoclass:: wetterturnier_paramObject
    :filename: ../classes.php
    :members:
    :undoc-members:

webcamObject
-------------

.. _api-webcamObject:

Small object to handle the webcams for the different cities
(webcam image implementation).

.. phpautoclass:: wetterturnier_webcamObject
    :filename: ../classes.php
    :members:
    :undoc-members:

groupsObject
------------

.. _api-groupsObject:

Handling of groups. A group consists of several members/users
and is used to generate the `Mitteltipps` (mean group bets).

.. phpautoclass:: wetterturnier_groupsObject
    :filename: ../classes.php
    :members:
    :undoc-members:

latestobsObject
---------------

Class to read observation data from the `obs` database table.
Please note that database is hardcoded in the php code (database
table called `obs`). The wordpress mysql user requires read
permissions to be able to get these data. The objects can be returned
as JSON arrays and are used for some wetterturnier jQuery plugins
(observation tables and plots).

To grant the correct privileges to the wordpress user simply login
to your database and give the correct user the following permissions:

```
GRANT PRIVILEGES SELECT ON obs.* TO 'wpwt'@'localhost';
FLUSH PRIVILEGES;
```

.. phpautoclass:: wetterturnier_latestobsObject
    :filename: ../classes.php
    :members:
    :undoc-members:


oldoutputformat
----------------

.. _api-oldoutputObject:

.. note:: This was a `quick fix` which not yet works (Jan 2018)
    we have the method Moses which relies of the forecasts of the
    latest few weeks. After moving our server the files are no more
    available in the same format. This class (in combination with
    the file `oldarchive.php <https://github.com/retostauffer/wp-wetterturnier/blob/master/oldarchive.php>`_ 
    is mimiking the old ASCII format for the upper part of the data.
    Either Moses needs more, or he never downloads he new files
    as he cannot find the file listing on which he relies?


Called and only called from
`oldarchive.php <https://github.com/retostauffer/wp-wetterturnier/blob/master/oldarchive.php>`_.
The server uses a `.htaccess` rewrite rule provide this mimiked
files under the same old url. The rewrite condition is as follows:

.. code-block:: bash

    RewriteBase /
    RewriteRule ^archiv/wert_([a-z])/wert([0-9]{6})\.txt$ wp-content/plugins/wp-wetterturnier/oldarchive.php?city=$1&date=$2 [L,NC]

.. phpautoclass:: wetterturnier_oldoutputObject
    :filename: ../oldoutputclass.php
    :members:
    :undoc-members:









