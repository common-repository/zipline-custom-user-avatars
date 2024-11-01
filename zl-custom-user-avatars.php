<?php
/*
Plugin Name: Zipline Custom User Avatars
Plugin URI: http://wordpress.org/plugins/zl-custom-user-avatars/
Description: Use any image from your WordPress Media Library as a custom user avatar. Add your own Default Avatar.
Author: Zipline
Author URI: https://wearezipline.com/
Version: 1.0.0
Text Domain: zl-custom-user-avatars
Domain Path: /lang/
*/

if ( ! defined( 'ABSPATH' ) ) {
    die( 'You are not allowed to call this page directly.' );
}

/**
 * Plugin setup.
 */
class ZL_Custom_User_Avatars_Setup {
    /**
     * Constructor
     */
    public function __construct() {
        $this->_define_constants();
        $this->_load_wp_includes();
        $this->_load_zlcua();
    }

    /**
     * Define paths
     *
     * @since 1.9.2
     */
    private function _define_constants() {
        define( 'ZLCUA_VERSION', '1.0.0' );
        define( 'ZLCUA_FOLDER', basename( dirname( __FILE__ ) ) );
        define( 'ZLCUA_DIR', plugin_dir_path( __FILE__ ) );
        define( 'ZLCUA_INC', ZLCUA_DIR . 'includes' . '/' );
        define( 'ZLCUA_URL', plugin_dir_url( ZLCUA_FOLDER ) . ZLCUA_FOLDER . '/' );
        define( 'ZLCUA_INC_URL', ZLCUA_URL . 'includes' . '/' );
    }

    /**
     * WordPress includes used in plugin
     *
     * @uses  is_admin()
     */
    private function _load_wp_includes() {
        if ( ! is_admin() ) {
            // wp_handle_upload
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            // wp_generate_attachment_metadata
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            // image_add_caption
            require_once( ABSPATH . 'wp-admin/includes/media.php' );
            // submit_button
            require_once( ABSPATH . 'wp-admin/includes/template.php' );
        }
        // add_screen_option
        require_once( ABSPATH . 'wp-admin/includes/screen.php' );
    }

    /**
     * Load Zipline Custom User Avatars
     *
     * @since 1.9.2
     * @uses  bool $zlcua_tinymce
     * @uses  is_admin()
     */
    private function _load_zlcua() {
        global $zlcua_tinymce;
        require_once( ZLCUA_INC . 'zlcua-globals.php' );
        require_once( ZLCUA_INC . 'zlcua-functions.php' );
        require_once( ZLCUA_INC . 'class-zl-custom-user-avatars-admin.php' );
        require_once( ZLCUA_INC . 'class-zl-custom-user-avatars.php' );
        require_once( ZLCUA_INC . 'class-zl-custom-user-avatars-functions.php' );
        require_once( ZLCUA_INC . 'class-zl-custom-user-avatars-shortcode.php' );
        require_once( ZLCUA_INC . 'class-zl-custom-user-avatars-subscriber.php' );
        require_once( ZLCUA_INC . 'class-zl-custom-user-avatars-update.php' );
        require_once( ZLCUA_INC . 'class-zl-custom-user-avatars-widget.php' );

        // Load TinyMCE only if enabled
        if ( (bool) $zlcua_tinymce === 1 ) {
            require_once( ZLCUA_INC . 'zlcua-tinymce.php' );
        }

    }
}

/**
 * Initialize
 */
new ZL_Custom_User_Avatars_Setup();
