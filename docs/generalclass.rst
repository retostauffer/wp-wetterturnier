
Generalclass
============



General Info
------------


Contained in the file :download:`generalclass.php <../generalclass.php>`.

This is the `core class` of the wp-wetterturnier plugin.
This class will be extended by both, the :doc:`userclass`
and the :doc:`adminclass`. :doc:`generalclass` contains a set of
methods and functions used in both, the wordpress frontend and
backend (admin interface).


.. php:class:: wetterturnier_generalclass

    General-class which is used by the main class, the
    adminclass and the userclass. Contains a wide range of 
    functions.

    .. php:attr:: date_format = "%Y-%m-%d"

        Default format used for date formatting. This will be overruled
        given the `polylang` plugin is installed and a different date format
        is specified via admin interface. This specification will be used as
        fallback if `polylang` is not installed or cannot be loaded (or loaded
        too slowly). Will be overruled by method :php:meth:`init_options`.

    .. php:attr:: datetime_format = "%Y-%m-%d %H:%M"

        Same as :php:attr:`date_format` but for date and time representation (including
        hour and minute).

    .. php:attr:: current_cityObj = NULL

        Private attribute to store the current :php:class:`wetterturnier_cityObject` 
        object. Try to avoid to call/load the same information multiple times
        as this object is used in many methods.
        After the current :php:class:`wetterturnier_cityObject` is loaded once it is
        stored on this attribute and will be re-used if loaded.

    .. php:attr:: all_cityObj_active = NULL

        Private attribute to store a list containing all active :php:class:`wetterturnier_cityObject`.
        Stored on this attribute and will be re-used if loaded.
        Similar to attribute :php:attr:`current_cityObj` but containing all active cities.

    .. php:attr:: current_tournament = false

        Private attribute to store current tournament date.

    .. php:method:: get_current_cityObj()

        Returns current active city for the user. Uses attribute :php:attr:`current_cityObj`
        if not `NULL` or loads it and stores the result on the attribute
        :php:attr:`current_cityObj`.

        :returns: Returns object if class :php:class:`wetterturnier_cityObject`.

    .. php:method:: init_options()

        Method used to load wetterturnier-specific options from database. Overwrites
        the :php:attr:`date_format` and :php:attr:`datetime_format` attributes.

        :returns: `stdClass` object containing some options such as the custom
            wetterturnier options. 

    .. php:method:: plugins_url( $pluginname = "wp-wetterturnier" )

        :param string $pluginname: Name of an installed plugin on this wordpress instance.
            Default is `"wp-wetterturnier"` the name of this plugin.
        :returns: Returns a string with the url to the plugin. Uses wordpress
            function `plugins_url($pluginname)`.

    .. php:method:: load_current_tournament_once()

    .. php:method:: number_format( $value, $decimals = 2 )

    .. php:method:: register_css_files()

    .. php:method:: register_js_files()

    .. php:method:: include_js_script()

    .. php:method:: get_user_language( $value = "slug" )

    .. php:method:: set_locale( $locale = false )

    .. php:method:: set_float_format()

    .. php:method:: load_date_format()

    .. php:method:: load_datetime_format()

    .. php:method:: convert_tdate()

    .. php:method:: date_format()

    .. php:method:: datetime_format()

    .. php:method:: get_terms_link()

    .. php:method:: insertonduplicate()

    .. php:method:: next_tournament()

    .. php:method:: current_tournament()

    .. php:method:: latest_tournament()

    .. php:method:: older_tournament()

    .. php:method:: newer_tournament()

    .. php:method:: check_bet_is_submitted()

    .. php:method:: check_user_is_in_group()

    .. php:method:: get_groups_from_user()

    .. php:method:: get_user_by_ID()

        .. todo:: Should use wordpress get_user rather than my own function.
            maybe combine with :php:meth:`get_user_by_username` or write a custom
            get_user method which also adds/returns display class and name
            (see :php:meth:`get_user_display_class_and_name`, 
            :php:meth:`get_user_profile_link`, :php:meth:`get_avatar_url`)

    .. php:method:: get_user_by_username()

        .. todo:: See :php:meth`get_user_by_ID`.

    .. php:method:: get_user_display_class_and_name()

        .. todo:: See :php:meth`get_user_by_ID`.

    .. php:method:: get_user_profile_link()

        .. todo:: See :php:meth`get_user_by_ID`.

    .. php:method:: check_view_is_closed()

    .. php:method:: check_allowed_to_display_betdata()

    .. php:method:: get_ranking_data()

        .. todo:: Should be outsourced into the new ranking-class.

    .. php:method:: get_param_by_ID()

        .. todo:: Handle via :php:class:wetterturnier_paramClass`.

    .. php:method:: get_param_by_name()

        .. todo:: Handle via :php:class:wetterturnier_paramClass`.

    .. php:method:: get_param_ID()

        .. todo:: Handle via :php:class:wetterturnier_paramClass`.

    .. php:method:: get_param_names()

        .. todo:: Handle via :php:class:wetterturnier_paramClass`.

    .. php:method:: tournament_date_status()

    .. php:method:: tournament_get_dates()

    .. php:method:: get_param_data()

        .. todo:: Handle via :php:class:wetterturnier_paramClass`.

    .. php:method:: get_current_city()

    .. php:method:: get_current_city_id()

    .. php:method:: get_all_cityObj()

        .. todo:: Handle via :php:class:wetterturnier_cityClass`.

    .. php:method:: get_city_info()

        .. todo:: Handle via :php:class:wetterturnier_cityClass`.

    .. php:method:: get_all_stationObj()

    .. php:method:: get_station_wmo_for_city()

        .. todo:: Handle via :php:class:wetterturnier_cityClass`.

    .. php:method:: get_station_data_for_city()

        .. todo:: Handle via :php:class:wetterturnier_cityClass`.

    .. php:method:: get_station_by_wmo()

        .. todo:: Handle via :php:class:wetterturnier_stationClass`.

    .. php:method:: curPageURL()

    .. php:method:: get_user_bets_from_db()

        .. todo:: Should be outsourced into the new ranking-class.

    .. php:method:: get_bet_values()

        .. todo:: Should be outsourced into the new ranking-class.

    .. php:method:: get_station_obs_from_db()

        .. todo:: Should be implemented in the :php:class:`wetterturnier_latestobsClass`.

    .. php:method:: get_obs_from_db()

        .. todo:: Should be implemented in the :php:class:`wetterturnier_latestobsClass`.

    .. php:method:: get_obs_values()

        .. todo:: Should be implemented in the :php:class:`wetterturnier_latestobsClass`.

    .. php:method:: tournament_datepicker_widget()

        .. todo:: Think about outsourcing this datepicker widget to a mini class.

    .. php:method:: tournament_datepicker_ajax()

        .. todo:: Think about outsourcing this datepicker widget to a mini class.

    .. php:method:: usersearch_ajax()

    .. php:method:: get_avatar_url()

        .. todo:: See :php:meth`get_user_by_ID`.

    .. php:method:: REQUEST_CHECK()

    .. php:method:: getobservations_ajax()










