
Generalclass
============



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

        Calls :php:meth:`current_tournament` once and stores the return on
        the attribute :php:attr:`current_tournament` to be reused by other functions
        and methods.

        :returns: No return.
    

    .. php:method:: number_format( $value, $decimals = 2 )

        *Should be used whenever float numbers are printed* as this
        function takes care of the language specific time formats.
        Uses language (system) specific delimiters.

        :param numeric $value: Value which should be formatted.
        :param int $decimals: Number of decimals to be shown.
        :returns: Returns a string of type `%.fX` where `X` are
            the requested digits.

    .. php:method:: register_css_files()

        Heper function to register a set of defined css files in wordpress.
        See wordpress `wp_register_style` and `wp_enqueue_style` for more information.
        :returns: No return.

    .. php:method:: register_js_files()

        Analog to :php:meth:`register_css_files` but registering jQuery/javascript files.
        See wordpress `wp_register_script` and `wp_enqueue_script` for more information.

    .. php:method:: register_js_script( $file )

        Similar to `register_js_files` but for a specific file.

        :param str $file: Name/path to the js file to be included without js suffix.
        :return: No return.

        .. todo:: Could be combined with :php:meth:`register_js_files` with
            a `NULL` input.


    .. php:method:: include_js_script( $file )

        Include a specific js file at the location where the method is called. *Note
        that this method does not register the js file* wherefore they might not work
        in wordpress but are used when loading some custom/specific apps (e.g., the
        observation plots/tables).

        :param str $file: Name/path to the js file to be included without js suffix.
        :return: No return.

    .. php:method:: get_user_language( $value = "slug" )

        Helper function to return current active user language from the polylang
        plugin (if active). If `pll_current_language` (polylang plugin) is not callable
        the fallback is _English_.

        :param str $value: Whether to return `"slug"` or `"name"`. Slug is e.g., `en`,
            name is `en_US` (long version).
        :returns: Given input $value slug or name will be returned.

    .. php:method:: set_locale( $locale = false )

        Setting language specific locale.

        .. todo:: Check if still in use.

    .. php:method:: load_date_format()

        Loading custom float format from database. This format is used in
        :php:meth:`number_format`. Stores format on :php:attr:`float_format`.

    .. php:method:: load_date_format()

        Loading custom date format from database. This format is used in
        :php:meth:`date_format`. Stores format on :php:attr:`date_format`.

    .. php:method:: load_datetime_format()

        Loading custom datetime format from database. This format is used in
        :php:meth:`datetime_format`. Stores format on :php:attr:`datetime_format`.

    .. php:method:: convert_tdate( $tdate, $fmt = "%Y-%m-%d %H:%M:%S" )

        Convert a tournament date (*days* since 1970-01-01 00:00:00) into a string
        with the given format.

        :param int $tdate: Tournament date (days since 1970-01-01).
        :param str $fmt: Valid date/time format string.
        :returns: String for tournament date given the format specified.

    .. php:method:: date_format( $tdate, $fmt = NULL )

        Converts a tournament date (*days* since 1970-01-01 00:00:00) into a
        string with attribute format :php:attr:`date_format` if input `$fmt=NULL`
        or the user-specified format.

        :param int $tdate: Tournament date (days since 1970-01-01).
        :param str $fmt: Valid date/time format string or `NULL`.
        :returns: String for tournament date given the format.

    .. php:method:: datetime_format( $tdate, $fmt = NULL )

        Converts a timestamp (*seconds* since 1970-01-01 00:00:00) into a
        string with attribute format :php:attr:`date_format` if input `$fmt=NULL`
        or the user-specified format.

        :param int $tdate: Timestamp (days since 1970-01-01).
        :param str $fmt: Valid date/time format string or `NULL`.
        :returns: String for tournament date given the format.

        .. todo:: Rename $tdate to $timestamp to increase readability.

    .. php:method:: get_terms_link()

        Given current language (based on polylang plugin): return the link
        to the `terms and conditions` page shown below the registration form.
        The URL's are defined as options in the admin backend.

        :returns: Returns hyperref link string to the terms and conditions page.

    .. php:method:: insertonduplicate( $table, $data, $updatecol = array(), $useprepare = True )

        Helper function as not provided natively by the wordpress `wpdb` object.
        Same usage as for `wpdb->insert` but using sqls `INSERT ON DUPLICATE KEY UPDATE`
        synta.

        :param str $table: Database table name.
        :param array $data: Array with data to add to the database.
        :param array $updatecol: Names of the columns which should be updated.
        :param bool $useprepare: Whether or not to use the `$wpdb->prepare` sql method.

    .. php:method:: next_tournament( $row_offset=0, $check_access=true, $dayoffset=0, $backwards=false )

        Loading next (upcoming) tournament date from database. This is the main function
        interfaced by :php:meth:`current_tournament`, :php:meth:`latest_tournament`,
        :php:meth:`older_tournament`, and :php:meth:`newer_tournament`.
        Only `tournament weekends` (`status=1` in database) will be considered.

        To be able to serve all these methods this one has all required input arguments.
        When specified correctly they allow to look behind and ahead in different ways and
        to lock the view if necessary.

        :param int $row_offset: Offset.
        :param bool $check_access: Whether to check if the requested tournament date is
            locked (no access) or not.
        :param int $dayoffset: Offst in days.
        :param bool $backwards: Whether to look-ahead or look-behind if offsets are specified.
        :returns: Returns an object of type `stdClass` with tournament date, tournament date
            for first and second bet day and a flag whether the data/tournament is locked or
            not.

    .. php:method:: current_tournament( $row_offset=0, $check_access=true, $dayoffset=-2, $backwards=false )

        See :php:meth:`next_tournament`.

    .. php:method:: latest_tournament( $tdate )

        See :php:meth:`next_tournament`.

    .. php:method:: older_tournament( $tdate )

        See :php:meth:`next_tournament`.

    .. php:method:: newer_tournament( $tdate )

        See :php:meth:`next_tournament`.

    .. php:method:: check_bet_is_submitted( $userID, $cityObj, $tdate )

        Check if a specific user has a submitted bet for a given city and tournament
        date. Uses `wp_wetterturnier_betstat` database table.

        :param int $userID: User ID.
        :param obj $cityObj: Object of type :php:class:`wetterturnier_cityObject`.
        :param int $tdate: Tournament date, days since 1970-01-01.
        :returns: Returns an object of class `stdClass` containing information if and
            when the bet has been placed.

    .. php:method:: check_user_is_in_group( $userID, $groupName )

        Check if user is in group.

        :param int $userID: User ID.
        :param str $groupName: Name of the group.

        .. todo:: WARNING: we have to check this for a specific tdate!

    .. php:method:: get_groups_from_user()

        Check whether user is currently in 0 or more groups.

        :returns: Boolean false if the user is in 0 grops, else a `stdClass`
            object containing groupID and groupName.

        .. todo:: WARNING: we have to check this for a specific tdate!

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

    .. php:method:: get_user_profile_link( $usr )

        Returns link to `bbpress` user profile link.

        :param obj $usr: `stdClass` object containing `user_login` and `display_name`.
        .. todo:: See :php:meth`get_user_by_ID`.

    .. php:method:: check_view_is_closed( $tdate )

        Checks whether the view (e.g., bet form or bets) is currently closed
        for the tourmanet input $tdate.

        :param int $tdate: Tournament date, days since 1970-01-01.
        :returns Boolean true/false whether view is closed (no access for users)
            or not.

    .. php:method:: check_allowed_to_display_betdata( $tdate, $showinfo=true )

        Checks if user are allowed to see the bet data or not. Depends on the
        tournament closing time and date. If user is not allowed to see the
        data a message will be printed (if `$showinfo=true`) and returns false (closed),
        else a boolean true.

        :param int $tdate: Tournament date, *days* since 1970-01-01.
        :returns: Boolean true or false.

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

    .. php:method:: tournament_date_status( $tdate )

        Returns tournament date status. Each day can either have
        an integer flag in the database or being `NULL`.
        If not defined (`NULL`) a `false` will be returned. Else
        the status will be returned (0=no tournament, 1=tournament, 2=special
        day without tournament).

        :param int $tdate: Tournament date, *days* since 1970-01-01.
        :returns: False or status (integer).

    .. php:method:: tournament_get_dates()

        Returns all specified tournament dates from the database.
        :returns: Array with all tournament dates specified in the database.
            Keys/values of the array are a date string (`%Y-%m-%d`) and
            the corresponding status (integer).

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

        Ajax data function for the user-search plugin used in e.g, the statistics
        plugin or in the admin-interface.

    .. php:method:: get_avatar_url()

        .. todo:: See :php:meth`get_user_by_ID`.

    .. php:method:: REQUEST_CHECK()

    .. php:method:: getobservations_ajax()

        Ajax data function for the obstable and obsplot plugins.










