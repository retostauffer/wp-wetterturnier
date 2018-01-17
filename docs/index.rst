Wetterturnier Wordpress Plugin
===============================

This is the documentation for the 
`wetterturnier.de <http://www.wetterturnier.de>`_
`wordpress <https://wordpress.com>`_ plugin (`wp-wetterturnier`) providing full
wordpress integration.
**Please note** that the plugin is not yet ready-to-go (installation procedure
has to be tested and fixed), :ref:`the known issues <index-known-issues>` section
has some more details.



.. include:: ../README.rst


Software Dependencies
=====================

The plugin is based on `wordpress <https://wordpress.com>`_ and has
been tested using the following software:
`Wordpress 4.9 <https://wordpress.org>`_ with
`php 7.0 <https://php.net>`_ and
`mysql 5.1.73 <https://www.mysql.com>`_.
For some very specific applications
`d3 v4 <https://d3js.org/>`_ is used.


Software Overview
=================

Important blocks
-----------------

* :doc:`theplugin`: Explanation of the Wordpress plugin structure
* :doc:`gettingstarted`: Well ... todo

.. toctree::
    :maxdepth: 1
    :hidden:

    gettingstarted.rst
    theplugin.rst
    thewidgets.rst

Known Issues
------------
.. _index-known-issues:

First I have to mention that I've tried to program the plugin as
generic as possible, but as this is a very specific application
some parts might be narrowing everything a bit down. However, with
some people willing to contribute we might lift this plugin to the
next level!

This is an incomplete list of important known open points which
might be adressed over the next months, or might not.

.. note::
    * Sphinx phpautodoc (v 1.2.1 by `Takeshi Komiya <https://pypi.python.org/pypi/tk.phpautodoc>`_
      does not yet extract ``@param``, `@return` and other settings from the docs.
    * Plugin installation procedure is prepared, but outdated and not yet tested!
      Plugin updating procedure/uninstall not yet considered.
      **This makes an installation hard or even impossible, has to be adressed a.s.a.p.**.
      If you'd like to test/use the plugin you might contact the author to ask for the
      table template at the moment.
    * The wetterturnier plugin is designed to forecast the two consecutive days. While
      parts are ready to decrease/increase this number, others are not (e.g., the
      database design is not able to do so).
    * Currently only deterministic forecasts (e.g., 10 degrees celsius for tomorrow noon)
      are possible. Would be quite a bit of effort but cool to also allow for
      probabilistic forecasts/scorings (more state of the art for meteo community)

Classes and Libraries
---------------------

* :doc:`classes`: helper classes (object/data handling classes)
* :doc:`api`: API overview

.. toctree::
    :maxdepth: 2
    :hidden:
    :caption: Documentation

    files.rst
    databases.rst
    classes.rst 
    api.rst

Full Todo List from The Whole Documentation
===========================================

Full list of all (often duplicated) todos found in the whole documentation.

.. todolist::

