<?php

/**
 * Settings only for subscribers and contributors.
 *
 * @package Zipline Custom User Avatars
 */
class ZL_Custom_User_Avatars_Subscriber {
    /**
     * Constructor
     *
     * @uses  object $zl_custom_user_avatars
     * @uses  bool $zlcua_allow_upload
     * @uses  add_action()
     * @uses  current_user_can()
     * @uses  zlcua_is_author_or_above()
     */
    public function __construct() {
        global $zlcua_allow_upload;
        if ( (bool) $zlcua_allow_upload == 1 ) {
            add_action( 'user_edit_form_tag', array ( $this, 'zlcua_add_edit_form_multipart_encoding' ) );
        }
        add_action( 'admin_init', array ( $this, 'zlcua_subscriber_capability' ) );
    }

    /**
     * Allow multipart data in form
     */
    public function zlcua_add_edit_form_multipart_encoding() {
        echo ' enctype="multipart/form-data"';
    }

    /**
     * Give subscribers edit_posts capability
     *
     * @uses  int $blog_id
     * @uses  object $wpdb
     * @uses  bool $zlcua_allow_upload
     * @uses  bool $zlcua_edit_avatar
     * @uses  get_blog_prefix()
     * @uses  get_option()
     * @uses  update_option()
     */
    public function zlcua_subscriber_capability() {
        global $blog_id, $wpdb, $zlcua_allow_upload, $zlcua_edit_avatar;
        $wp_user_roles = $wpdb->get_blog_prefix( $blog_id ) . 'user_roles';
        $user_roles    = get_option( $wp_user_roles );
        if ( isset( $user_roles['subscriber']['capabilities']['edit_posts'] ) ) {
            unset( $user_roles['subscriber']['capabilities']['edit_posts'] );
        }
        update_option( $wp_user_roles, $user_roles );
    }
}

/**
 * Initialize
 */
function zlcua_subscriber_init() {
    global $zlcua_subscriber;
    $zlcua_subscriber = new ZL_Custom_User_Avatars_Subscriber();
}

add_action( 'init', 'zlcua_subscriber_init' );
