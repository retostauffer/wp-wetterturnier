<?php
// ------------------------------------------------------------------
/// @file settings.php
/// @author Reto Stauffer
/// @date 16 June 2017
/// @brief Plugin settings for the wp-wetterturnier wordpress plugin.
///
/// @details Settings page which is called by wordpress when the
/// plugin is active. Initializes the plugin and setup other things
/// like e.g., adding the custom navigation elements in the wordpress
/// admin interface.
// ------------------------------------------------------------------
if(!class_exists('WP_Wetterturnier_Settings'))
{
    class WP_Wetterturnier_Settings
    {
        /**
         * Construct the plugin object
         */
        public function __construct()
        {
            // register actions
            add_action('admin_init', array(&$this, 'admin_init'));
            add_action('admin_menu', array(&$this, 'add_menu'));
        } // END public function __construct

        /**
         * hook into WP's admin_init action hook
         */
        public function admin_init()
        {
            // register your plugin's settings
            register_setting('wp_wetterturnier-group', 'setting_a');
            register_setting('wp_wetterturnier-group', 'setting_b');

            // add your settings section
            add_settings_section(
                'wp_wetterturnier-section', 
                'WP Wetterturnier Settings', 
                array(&$this, 'settings_section_wp_wetterturnier'), 
                'wp_wetterturnier'
            );
            
            // add your setting's fields
            add_settings_field(
                'wp_wetterturnier-setting_a', 
                'Setting A', 
                array(&$this, 'settings_field_input_text'), 
                'wp_wetterturnier', 
                'wp_wetterturnier-section',
                array(
                    'field' => 'setting_a'
                )
            );
            add_settings_field(
                'wp_wetterturnier-setting_b', 
                'Setting B', 
                array(&$this, 'settings_field_input_text'), 
                'wp_wetterturnier', 
                'wp_wetterturnier-section',
                array(
                    'field' => 'setting_b'
                )
            );

            // add your settings section
            add_settings_section(
                'wp_wetterturnier-betdays', 
                'WP Wetterturnier Number of bet days',
                array(&$this, 'settings_section_wp_wetterturnier'), 
                'wp_wetterturnier'
            );
            // Possibly do additional admin_init tasks
        } // END public static function activate
        
        public function settings_section_wp_wetterturnier()
        {
            // Think of this as help text for the section.
            echo 'These settings do things for the WP Wetterturnier.';
        }
        
        /**
         * This function provides text inputs for settings fields
         */
        public function settings_field_input_text($args)
        {
            // Get the field name from the $args array
            $field = $args['field'];
            // Get the value of this setting
            $value = get_option($field);
            // echo a proper input type="text"
            printf('<input type="text" name="%s" id="%s" value="%s" />', $field, $field, $value);
        } // END public function settings_field_input_text($args)
        
        /**
         * add a menu
         */     
        public function add_menu()
        {
            // Add a page to manage this plugin's settings
            add_options_page(
                'WP Wetterturnier Settings', 
                'WP Wetterturnier', 
                'manage_options', 
                'wp_wetterturnier', 
                array(&$this, 'plugin_settings_page')
            );
        } // END public function add_menu()
    
        /**
         * Menu Callback
         */     
        public function plugin_settings_page()
        {
            if(!current_user_can('manage_options'))
            {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            // Render the settings template
            include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
        } // END public function plugin_settings_page()
    } // END class WP_Wetterturnier_Settings
} // END if(!class_exists('WP_Wetterturnier_Settings'))
?>
