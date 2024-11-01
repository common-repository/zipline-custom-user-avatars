<?php

/**
 * Updates for legacy settings.
 *
 * @package Zipline Custom User Avatars
 */
class ZL_Custom_User_Avatars_Update {
    /**
     * Constructor
     *
     * @since 1.8
     * @uses  bool $zlcua_default_avatar_updated
     * @uses  bool $zlcua_media_updated
     * @uses  bool $zlcua_users_updated
     * @uses  add_action()
     */
    public function __construct() {
        global $zlcua_default_avatar_updated, $zlcua_media_updated, $zlcua_users_updated;
        if ( empty( $zlcua_default_avatar_updated ) ) {
            add_action( 'admin_init', array ( $this, 'zlcua_default_avatar' ) );
        }
        if ( empty( $zlcua_users_updated ) ) {
            add_action( 'admin_init', array ( $this, 'zlcua_user_meta' ) );
        }
        if ( empty( $zlcua_media_updated ) ) {
            add_action( 'admin_init', array ( $this, 'zlcua_media_state' ) );
        }
    }

    /**
     * Update default avatar to new format
     *
     * @since 1.4
     * @uses  string $avatar_default
     * @uses  string $mustache_original
     * @uses  int $zlcua_avatar_default
     * @uses  update_option()
     * @uses  wp_get_attachment_image_src()
     */
    public function zlcua_default_avatar() {
        global $avatar_default, $mustache_original, $zlcua_avatar_default;
        // If default avatar is the old mustache URL, update it
        if ( $avatar_default == $mustache_original ) {
            update_option( 'avatar_default', 'zl_custom_user_avatars' );
        }
        // If user had an image URL as the default avatar, replace with ID instead
        if ( ! empty( $zlcua_avatar_default ) ) {
            $zlcua_avatar_default_image = wp_get_attachment_image_src( $zlcua_avatar_default, 'medium' );
            if ( $avatar_default == $zlcua_avatar_default_image[0] ) {
                update_option( 'avatar_default', 'zl_custom_user_avatars' );
            }
        }
        update_option( 'zl_custom_user_avatars_default_avatar_updated', '1' );
    }

    /**
     * Rename user meta to match database settings
     *
     * @uses  int $blog_id
     * @uses  object $wpdb
     * @uses  delete_user_meta()
     * @uses  get_blog_prefix()
     * @uses  get_user_meta()
     * @uses  get_users()
     * @uses  update_option()
     * @uses  update_user_meta()
     */
    public function zlcua_user_meta() {
        global $blog_id, $wpdb;
        $zlcua_metakey = $wpdb->get_blog_prefix( $blog_id ) . 'user_avatar';
        // If database tables start with something other than wp_
        if ( $zlcua_metakey != 'zl_custom_user_avatars' ) {
            $users = get_users();
            // Move current user metakeys to new metakeys
            foreach ( $users as $user ) {
                $zlcua = get_user_meta( $user->ID, 'zl_custom_user_avatars', true );
                if ( ! empty( $zlcua ) ) {
                    update_user_meta( $user->ID, $zlcua_metakey, $zlcua );
                    delete_user_meta( $user->ID, 'zl_custom_user_avatars' );
                }
            }
        }
        update_option( 'zl_custom_user_avatars_users_updated', '1' );
    }

    /**
     * Add media state to existing avatars
     *
     * @uses  int $blog_id
     * @uses  object $wpdb
     * @uses  add_post_meta()
     * @uses  get_blog_prefix()
     * @uses  get_results()
     * @uses  update_option()
     */
    public function zlcua_media_state() {
        global $blog_id, $wpdb;
        // Find all users with ZLCUA
        $zlcua_metakey = $wpdb->get_blog_prefix( $blog_id ) . 'user_avatar';
        $zlcuas         = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->usermeta WHERE meta_key = %s AND meta_value != %d AND meta_value != %d", $zlcua_metakey, 0, "" ) );
        foreach ( $zlcuas as $usermeta ) {
            add_post_meta( $usermeta->meta_value, '_wp_attachment_zl_custom_user_avatars', $usermeta->user_id );
        }
        update_option( 'zl_custom_user_avatars_media_updated', '1' );
    }
}

/**
 * Initialize
 */
function zlcua_update_init() {
    global $zlcua_update;
    $zlcua_update = new ZL_Custom_User_Avatars_Update();
}

add_action( 'init', 'zlcua_update_init' );
