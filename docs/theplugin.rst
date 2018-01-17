The Plugin
==========

For those not familiar with hot to write plugins for wordpress:
there is `a nice documentation/tutorial in the wordpress codes <https://codex.wordpress.org/Writing_a_Plugin>`_. A plugin adds additional functionality to the core system
which allows to do more or less everything with wordpress. For most
applications something suitable exists among the
`more tha 50,000 plugins <https://wordpress.org/plugins>`_ currently available.

Plugin Setup (Install/Uninstall)
---------------------------------

.. note:: This file also contains the install/uninstall procedures
    which are not yet fully implemented. MySQL table schemata 
    included but not finally tested, might be out of date.



Loading Plugin
---------------

Whenever the plugin is activated in wordpress
`wp-wetterturnier.php <https://github.com/retostauffer/wp-wetterturnier/blob/master/wp-wetterturnier.php>`__
will be loaded. This file is the "key file" containing
the plugin description and plugin initialization.
When loaded (on each wordpress request):

* Include required class files (:ref:`api`).
* Include all user widgets from `user/widgets/*.php`.
* If user is admin: include :ref:`adminclass.php <api-adminclass>`,

    .. php:class:: wetterturnier_adminclass

* else :ref:`adminclass.php <api-userclass>` is loaded.

    .. php:class:: wetterturnier_userclass

