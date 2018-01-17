Getting Started
===============


Wordpress Wetterturnier Plugin
-------------------------------

.. image:: images/github-logo.png
   :width: 498px
   :height: 151px
   :scale: 50 %
   :alt: Screenshot Frontend
   :align: center

The repository/documentation you are currently looking at is the
one for the fronend.

.. todo:: Here I would like to tell a bit about how to set up
        the frontend and which plugins we are currently using
        side by side with the ``wp-wetterturnier`` plugin 
        to the the full functionality. However, at the moment
        the setup routine for the wordpress plugin is not in 
        its final and tested stage, so this is something for 
        later.

Wetterturnier Backend
---------------------

.. image:: images/github-logo.png
   :width: 498px
   :height: 151px
   :scale: 50 %
   :alt: Screenshot Frontend
   :align: center

.. note:: The whole system consists of several modules. All modules
        are open source. One, the most important to get the system
        running, is the `Wetterturnier Backend <https://github.com/retostauffer/wetterturnier-backend>`_
        which does the judging (computation of the points), computation
        of the refernce players, mean group bets, and such things.


**Compute reference and mean group bets:**
As reference Wetterturnier uses different methods. While some are
pretty specific one of them is the persistency (observations from
two days before the day where the first forecasts have to be delivered)
called (this user is called **Persistenz**). Another is **Sleepy** to judge
users not having participated (as a punishment). Furthermore mean group
bets are computed if two or more players from a specific group have submitted
forecasts for a certain city.
This is all done on the :ref:`wpwt or wordpress database <mysql-database-wpwt>`.

**Prepare required observations:**
The `Wetterturnier Backend https://github.com/retostauffer/wetterturnier-backend`
has one script which fetches the observations from the
:ref:`obs or observation database <mysql-database-obs>` and prepares the
subset we need for the Wetterturnier based on the settings/config of this
wordpress Wetterturnier plugin.

**Judging:**
Once the required observations are prepared
and "moved" to the :ref:`wpwt or wordpress database <mysql-database-wpwt>`
the judingclass can do it's job. 
The judging method loads the prepared observations and the user and group
forecasts from the database and computes the points they get. This (fetching
the data and save the points) is all performed on the
:ref:`wpwt or wordpress database <mysql-database-wpwt>`.


Wetterturnier BUFRdecoder
-------------------------

.. note:: A third module is the BUFR decoder. We are getting our data in the
        WMO94 BUFR format. This, of course, might differ strongly between
        you and us. However, the repository is there and it might be worth
        to take a look to see how the database structure is set up if you
        have to or want to adjust our code or set something up which is
        very similar to not have to adjust the code too much.
        Or we are able to make the code more flexible where needed to be
        able to run it on different machines with a slightly different
        setup (e.g., some of the column names of the observation database
        tables are very German and could be defined via config files or so).
        Just let us know and we can discuss that.

.. todo:: Move repository from bitbucket -> github, add link, and add few
        more details here.
