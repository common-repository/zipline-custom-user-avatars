<?php

/**
 * Defines all of administrative, activation, and deactivation settings.
 *
 * @package Zipline Custom User Avatars
 */
class ZL_Custom_User_Avatars_Admin {
    /**
     * Constructor
     *
     * @uses  bool $show_avatars
     * @uses  add_action()
     * @uses  add_filter()
     * @uses  load_plugin_textdomain()
     * @uses  register_activation_hook()
     * @uses  register_deactivation_hook()
     */
    public function __construct() {
        global $show_avatars;
        // Initialize default settings
        register_activation_hook( ZLCUA_DIR . 'zl-custom-user-avatars.php', array ( $this, 'zlcua_options' ) );
        // Settings saved to wp_options
        add_action( 'admin_init', array ( $this, 'zlcua_options' ) );
        // Remove subscribers edit_posts capability
        // Translations
        load_plugin_textdomain( 'zl-custom-user-avatars', "", ZLCUA_FOLDER . '/lang' );
        // Admin menu settings
        add_action( 'admin_menu', array ( $this, 'zlcua_admin' ) );
        add_action( 'admin_init', array ( $this, 'zlcua_register_settings' ) );
        // Default avatar
        add_filter( 'default_avatar_select', array ( $this, 'zlcua_add_default_avatar' ), 10 );
        if ( function_exists( 'add_allowed_options' ) ) {
            add_filter( 'allowed_options', array ( $this, 'zlcua_whitelist_options' ), 10 );
        } else {
            add_filter( 'whitelist_options', array ( $this, 'zlcua_whitelist_options' ), 10 );
        }
        // Additional plugin info
        add_filter( 'plugin_action_links', array ( $this, 'zlcua_action_links' ), 10, 2 );
        add_filter( 'plugin_row_meta', array ( $this, 'zlcua_row_meta' ), 10, 2 );
        // Hide column in Users table if default avatars are enabled
        if ( (bool) $show_avatars == 0 ) {
            add_filter( 'manage_users_columns', array ( $this, 'zlcua_add_column' ), 10, 1 );
            add_filter( 'manage_users_custom_column', array ( $this, 'zlcua_show_column' ), 10, 3 );
        }
        // Media states
        add_filter( 'display_media_states', array ( $this, 'zlcua_add_media_state' ), 10, 1 );
    }

    /**
     * Settings saved to wp_options
     *
     * @uses  add_option()
     */
    public function zlcua_options() {
        /**
         * See if WPUA has options already and load them to set defaults.
         */
        $avatar_default_zl_custom_user_avatars_default    = get_option( 'avatar_default_wp_user_avatar', "" );
        $zl_custom_user_avatars_allow_upload_default      = get_option( 'wp_user_avatar_allow_upload', '0' );
        $zl_custom_user_avatars_disable_gravatar_default  = get_option( 'wp_user_avatar_disable_gravatar', '0' );
        $zl_custom_user_avatars_edit_avatar_default       = get_option( 'wp_user_avatar_edit_avatar', '1' );
        $zl_custom_user_avatars_resize_crop_default       = get_option( 'wp_user_avatar_resize_crop', '0' );
        $zl_custom_user_avatars_resize_h_default          = get_option( 'wp_user_avatar_resize_h', '96' );
        $zl_custom_user_avatars_resize_upload_default     = get_option( 'wp_user_avatar_resize_upload', '0' );
        $zl_custom_user_avatars_resize_w_default          = get_option( 'wp_user_avatar_resize_w', '96' );
        $zl_custom_user_avatars_tinymce_default           = get_option( 'wp_user_avatar_tinymce', '1' );
        $zl_custom_user_avatars_upload_size_limit_default = get_option( 'wp_user_avatar_upload_size_limit', '0' );

        add_option( 'avatar_default_zl_custom_user_avatars', $avatar_default_zl_custom_user_avatars_default );
        add_option( 'zl_custom_user_avatars_allow_upload', $zl_custom_user_avatars_allow_upload_default );
        add_option( 'zl_custom_user_avatars_disable_gravatar', $zl_custom_user_avatars_disable_gravatar_default );
        add_option( 'zl_custom_user_avatars_edit_avatar', $zl_custom_user_avatars_edit_avatar_default );
        add_option( 'zl_custom_user_avatars_resize_crop', $zl_custom_user_avatars_resize_crop_default );
        add_option( 'zl_custom_user_avatars_resize_h', $zl_custom_user_avatars_resize_h_default );
        add_option( 'zl_custom_user_avatars_resize_upload', $zl_custom_user_avatars_resize_upload_default );
        add_option( 'zl_custom_user_avatars_resize_w', $zl_custom_user_avatars_resize_w_default );
        add_option( 'zl_custom_user_avatars_tinymce', $zl_custom_user_avatars_tinymce_default );
        add_option( 'zl_custom_user_avatars_upload_size_limit', $zl_custom_user_avatars_upload_size_limit_default );
        if ( wp_next_scheduled( 'zlcua_has_gravatar_cron_hook' ) ) {
            $cron     = get_option( 'cron' );
            $new_cron = '';
            foreach ( $cron as $key => $value ) {
                if ( is_array( $value ) ) {
                    if ( array_key_exists( 'zlcua_has_gravatar_cron_hook', $value ) )
                        unset( $cron[$key] );
                }
            }
            update_option( 'cron', $cron );
        }
    }

    /**
     * On deactivation
     *
     * @uses  int $blog_id
     * @uses  object $wpdb
     * @uses  get_blog_prefix()
     * @uses  get_option()
     * @uses  update_option()
     */
    public function zlcua_deactivate() {
        global $blog_id, $wpdb;
        $wp_user_roles = $wpdb->get_blog_prefix( $blog_id ) . 'user_roles';
        // Get user roles and capabilities
        $user_roles = get_option( $wp_user_roles );
        // Remove subscribers edit_posts capability
        unset( $user_roles['subscriber']['capabilities']['edit_posts'] );
        update_option( $wp_user_roles, $user_roles );
        // Reset all default avatars to Mystery Man
        update_option( 'avatar_default', 'mystery' );
    }

    /**
     * Add options page and settings
     *
     * @uses  add_menu_page()
     * @uses  add_submenu_page()
     */
    public function zlcua_admin() {
        add_menu_page( __( 'Zipline Custom User Avatars', 'zl-custom-user-avatars' ), __( 'Avatars', 'zl-custom-user-avatars' ), 'manage_options', 'zl-custom-user-avatars', array ( $this, 'zlcua_options_page' ), ZLCUA_URL . 'images/zlcua-icon.png' );
        add_submenu_page( 'zl-custom-user-avatars', __( 'Settings', 'zl-custom-user-avatars' ), __( 'Settings', 'zl-custom-user-avatars' ), 'manage_options', 'zl-custom-user-avatars', array ( $this, 'zlcua_options_page' ) );
        $hook = add_submenu_page( 'zl-custom-user-avatars', __( 'Library', 'zl-custom-user-avatars' ), __( 'Library', 'zl-custom-user-avatars' ), 'manage_options', 'zl-custom-user-avatars-library', array ( $this, 'zlcua_media_page' ) );
        add_action( "load-$hook", array ( $this, 'zlcua_media_screen_option' ) );
        add_filter( 'set-screen-option', array ( $this, 'zlcua_set_media_screen_option' ), 10, 3 );
    }

    /**
     * Checks if current page is settings page
     *
     * @uses  string $pagenow
     * @return bool
     */
    public function zlcua_is_menu_page() {
        global $pagenow;
        $is_menu_page = ( $pagenow === 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] === 'zl-custom-user-avatars' ) ? true : false;
        return (bool) $is_menu_page;
    }

    /**
     * Media page
     */
    public function zlcua_media_page() {
        require_once( ZLCUA_INC . 'zlcua-media-page.php' );
    }

    /**
     * Avatars per page
     *
     * @uses  add_screen_option()
     */
    public function zlcua_media_screen_option() {
        $option = 'per_page';
        $args   = array (
            'label'   => __( 'Avatars', 'zl-custom-user-avatars' ),
            'default' => 10,
            'option'  => 'upload_per_page'
        );
        add_screen_option( $option, $args );
    }

    /**
     * Save per page setting
     *
     * @param int    $status
     * @param string $option
     * @param int    $value
     *
     * @return int $status
     */
    public function zlcua_set_media_screen_option( $status, $option, $value ) {
        $status = ( $option == 'upload_per_page' ) ? $value : $status;
        return $status;
    }

    /**
     * Options page
     */
    public function zlcua_options_page() {
        require_once( ZLCUA_INC . 'zlcua-options-page.php' );
    }

    /**
     * Whitelist settings
     *
     * @uses  apply_filters()
     * @uses  register_setting()
     * @return array
     */
    public function zlcua_register_settings() {
        $settings   = array ();
        $settings[] = register_setting( 'zlcua-settings-group', 'avatar_rating' );
        $settings[] = register_setting( 'zlcua-settings-group', 'avatar_default' );
        $settings[] = register_setting( 'zlcua-settings-group', 'avatar_default_zl_custom_user_avatars' );
        $settings[] = register_setting( 'zlcua-settings-group', 'show_avatars', 'intval' );
        $settings[] = register_setting( 'zlcua-settings-group', 'zl_custom_user_avatars_tinymce', 'intval' );
        $settings[] = register_setting( 'zlcua-settings-group', 'zl_custom_user_avatars_allow_upload', 'intval' );
        $settings[] = register_setting( 'zlcua-settings-group', 'zl_custom_user_avatars_disable_gravatar', 'intval' );
        $settings[] = register_setting( 'zlcua-settings-group', 'zl_custom_user_avatars_edit_avatar', 'intval' );
        $settings[] = register_setting( 'zlcua-settings-group', 'zl_custom_user_avatars_resize_crop', 'intval' );
        $settings[] = register_setting( 'zlcua-settings-group', 'zl_custom_user_avatars_resize_h', 'intval' );
        $settings[] = register_setting( 'zlcua-settings-group', 'zl_custom_user_avatars_resize_upload', 'intval' );
        $settings[] = register_setting( 'zlcua-settings-group', 'zl_custom_user_avatars_resize_w', 'intval' );
        $settings[] = register_setting( 'zlcua-settings-group', 'zl_custom_user_avatars_upload_size_limit', 'intval' );
        /**
         * Deprecated filter wpua_register_settings
         */
        $settings = apply_filters_deprecated( 'wpua_register_settings', array ( $settings ), '1.0.0', 'zlcua_register_settings' );

        /**
         * Filter admin whitelist settings
         *
         * @param array $settings
         */
        return apply_filters( 'zlcua_register_settings', $settings );
    }

    /**
     * Add default avatar
     *
     * @uses  string $avatar_default
     * @uses  string $mustache_admin
     * @uses  string $mustache_medium
     * @uses  int $zlcua_avatar_default
     * @uses  bool $zlcua_disable_gravatar
     * @uses  object $zlcua_functions
     * @uses  get_avatar()
     * @uses  remove_filter()
     * @uses  zlcua_attachment_is_image()
     * @uses  zlcua_get_attachment_image_src()
     * @return string
     */
    public function zlcua_add_default_avatar() {
        global $avatar_default, $mustache_admin, $mustache_medium, $zlcua_avatar_default, $zlcua_disable_gravatar, $zlcua_functions;
        // Remove get_avatar filter
        remove_filter( 'get_avatar', array ( $zlcua_functions, 'zlcua_get_avatar_filter' ) );
        // Set avatar_list variable
        $avatar_list = "";
        // Set avatar defaults
        $avatar_defaults = array (
            'mystery'          => __( 'Mystery Man', 'zl-custom-user-avatars' ),
            'blank'            => __( 'Blank', 'zl-custom-user-avatars' ),
            'gravatar_default' => __( 'Gravatar Logo', 'zl-custom-user-avatars' ),
            'identicon'        => __( 'Identicon (Generated)', 'zl-custom-user-avatars' ),
            'wavatar'          => __( 'Wavatar (Generated)', 'zl-custom-user-avatars' ),
            'monsterid'        => __( 'MonsterID (Generated)', 'zl-custom-user-avatars' ),
            'retro'            => __( 'Retro (Generated)', 'zl-custom-user-avatars' )
        );
        $avatar_defaults = apply_filters( 'avatar_defaults', $avatar_defaults );
        // No Default Avatar, set to Mystery Man
        if ( empty( $avatar_default ) ) {
            $avatar_default = 'mystery';
        }
        // Take avatar_defaults and get examples for unknown@gravatar.com
        foreach ( $avatar_defaults as $default_key => $default_name ) {
            $avatar      = get_avatar( 'unknown@gravatar.com', 32, $default_key );
            $selected    = ( $avatar_default == $default_key ) ? 'checked="checked" ' : "";
            $avatar_list .= "\n\t<label><input type='radio' name='avatar_default' id='avatar_{$default_key}' value='" . esc_attr( $default_key ) . "' {$selected}/> ";
            $avatar_list .= preg_replace( "/src='(.+?)'/", "src='\$1&amp;forcedefault=1'", $avatar );
            $avatar_list .= ' ' . $default_name . '</label>';
            $avatar_list .= '<br />';
        }
        // Show remove link if custom Default Avatar is set
        if ( ! empty( $zlcua_avatar_default ) && $zlcua_functions->zlcua_attachment_is_image( $zlcua_avatar_default ) ) {
            $avatar_thumb_src = $zlcua_functions->zlcua_get_attachment_image_src( $zlcua_avatar_default, array ( 32, 32 ) );
            $avatar_thumb     = $avatar_thumb_src[0];
            $hide_remove      = "";
        } else {
            $avatar_thumb = $mustache_admin;
            $hide_remove  = ' class="zlcua-hide"';
        }
        // Default Avatar is zl_custom_user_avatars, check the radio button next to it
        $selected_avatar = ( (bool) $zlcua_disable_gravatar == 1 || $avatar_default == 'zl_custom_user_avatars' ) ? ' checked="checked" ' : "";
        // Wrap ZLCUA in div
        $avatar_thumb_img = '<div id="zlcua-preview"><img src="' . $avatar_thumb . '" width="32" /></div>';
        // Add ZLCUA to list
        $zlcua_list = "\n\t<label><input type='radio' name='avatar_default' id='zl_custom_user_avatars_radio' value='zl_custom_user_avatars'$selected_avatar /> ";
        $zlcua_list .= preg_replace( "/src='(.+?)'/", "src='\$1'", $avatar_thumb_img );
        $zlcua_list .= ' ' . __( 'Zipline Custom User Avatars', 'zl-custom-user-avatars' ) . '</label>';
        $zlcua_list .= '<p id="zlcua-edit"><button type="button" class="button" id="zlcua-add" name="zlcua-add" data-avatar_default="true" data-title="' . __( 'Choose Image' ) . ': ' . __( 'Default Avatar' ) . '">' . __( 'Choose Image', 'zl-custom-user-avatars' ) . '</button>';
        $zlcua_list .= '<span id="zlcua-remove-button"' . $hide_remove . '><a href="#" id="zlcua-remove">' . __( 'Remove', 'zl-custom-user-avatars' ) . '</a></span><span id="zlcua-undo-button"><a href="#" id="zlcua-undo">' . __( 'Undo', 'zl-custom-user-avatars' ) . '</a></span></p>';
        $zlcua_list .= '<input type="hidden" id="zl-custom-user-avatars" name="avatar_default_zl_custom_user_avatars" value="' . $zlcua_avatar_default . '">';
        $zlcua_list .= '<div id="zlcua-modal"></div>';
        if ( (bool) $zlcua_disable_gravatar != 1 ) {
            return $zlcua_list . '<div id="wp-avatars">' . $avatar_list . '</div>';
        } else {
            return $zlcua_list . '<div id="wp-avatars" style="display:none;">' . $avatar_list . '</div>';
        }
    }

    /**
     * Add default avatar_default to whitelist
     *
     * @param array $options
     *
     * @return array $options
     */
    public function zlcua_whitelist_options( $options ) {
        $options['discussion'][] = 'avatar_default_zl_custom_user_avatars';
        return $options;
    }

    /**
     * Add actions links on plugin page
     *
     * @param array  $links
     * @param string $file
     *
     * @return array $links
     */
    public function zlcua_action_links( $links, $file ) {
        if ( basename( dirname( $file ) ) == 'zl-custom-user-avatars' ) {
            $links[] = '<a href="' . esc_url( add_query_arg( array ( 'page' => 'zl-custom-user-avatars' ), admin_url( 'admin.php' ) ) ) . '">' . __( 'Settings', 'zl-custom-user-avatars' ) . '</a>';
        }
        return $links;
    }

    /**
     * Add row meta on plugin page
     *
     * @param array  $links
     * @param string $file
     *
     * @return array $links
     */
    public function zlcua_row_meta( $links, $file ) {
        if ( basename( dirname( $file ) ) == 'zl-custom-user-avatars' ) {
            $links[] = '<a href="http://wordpress.org/support/plugin/zl-custom-user-avatars" target="_blank">' . __( 'Support Forums', 'zl-custom-user-avatars' ) . '</a>';
        }
        return $links;
    }

    /**
     * Add column to Users table
     *
     * @param array $columns
     *
     * @return array
     */
    public function zlcua_add_column( $columns ) {
        return $columns + array ( 'zl-custom-user-avatars' => __( 'Profile Picture', 'zl-custom-user-avatars' ) );
    }

    /**
     * Show thumbnail in Users table
     *
     * @param string $value
     * @param string $column_name
     * @param int    $user_id
     *
     * @uses  int $blog_id
     * @uses  object $wpdb
     * @uses  object $zlcua_functions
     * @uses  get_blog_prefix()
     * @uses  get_user_meta()
     * @uses  zlcua_get_attachment_image()
     * @return string $value
     */
    public function zlcua_show_column( $value, $column_name, $user_id ) {
        global $blog_id, $wpdb, $zlcua_functions;
        $zlcua       = get_user_meta( $user_id, $wpdb->get_blog_prefix( $blog_id ) . 'user_avatar', true );
        $zlcua_image = $zlcua_functions->zlcua_get_attachment_image( $zlcua, array ( 32, 32 ) );
        if ( $column_name == 'zl-custom-user-avatars' ) {
            $value = $zlcua_image;
        }
        return $value;
    }

    /**
     * Get list table
     *
     * @param string $class
     * @param array  $args
     *
     * @return object
     */
    public function _zlcua_get_list_table( $class, $args = array () ) {
        require_once( ZLCUA_INC . 'class-zl-custom-user-avatars-list-table.php' );
        $args['screen'] = 'zl-custom-user-avatars';
        return new $class( $args );
    }

    /**
     * Add media states
     *
     * @param array $states
     *
     * @uses  object $post
     * @uses  int $zlcua_avatar_default
     * @uses  apply_filters()
     * @uses  get_post_custom_values()
     * @return array
     */
    public function zlcua_add_media_state( $states ) {
        global $post, $zlcua_avatar_default;
        $is_zlcua = isset( $post->ID ) ? get_post_custom_values( '_wp_attachment_zl_custom_user_avatars', $post->ID ) : '';
        if ( ! empty( $is_zlcua ) ) {
            $states[] = __( 'Profile Picture', 'zl-custom-user-avatars' );
        }
        if ( ! empty( $zlcua_avatar_default ) && isset( $post->ID ) && ( $zlcua_avatar_default == $post->ID ) ) {
            $states[] = __( 'Default Avatar', 'zl-custom-user-avatars' );
        }
        /**
         * Deprecated filter wpua_add_media_state
         */
        $states = apply_filters_deprecated( 'wpua_add_media_state', array ( $states ), '1.0.0', 'zlcua_add_media_state' );

        /**
         * Filter media states
         *
         * @param array $states
         */
        return apply_filters( 'zlcua_add_media_state', $states );
    }
}

/**
 * Initialize
 */
function zlcua_admin_init() {
    global $zlcua_admin;
    $zlcua_admin = new ZL_Custom_User_Avatars_Admin();
}

add_action( 'init', 'zlcua_admin_init' );
