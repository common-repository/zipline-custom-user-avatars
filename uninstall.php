<?php
/**
 * Remove user metadata and options on plugin delete.
 *
 * @package Zipline Custom User Avatars
 */
/**
 * @uses  int $blog_id
 * @uses  object $wpdb
 * @uses  delete_option()
 * @uses  delete_post_meta_by_key()
 * @uses  delete_user_meta()
 * @uses  get_users()
 * @uses  get_blog_prefix()
 * @uses  is_multisite()
 * @uses  switch_to_blog()
 * @uses  update_option()
 * @uses  wp_get_sites()
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die( 'You are not allowed to call this page directly.' );
}
global $blog_id, $wpdb;
$users = get_users();
// Remove settings for all sites in multisite
if ( is_multisite() ) {
    $blogs = wp_get_sites();
    foreach ( $users as $user ) {
        foreach ( $blogs as $blog ) {
            delete_user_meta( $user->ID, $wpdb->get_blog_prefix( $blog->blog_id ) . 'user_avatar' );
        }
    }
    foreach ( $blogs as $blog ) {
        switch_to_blog( $blog->blog_id );
        delete_option( 'avatar_default_zl_custom_user_avatars' );
        delete_option( 'zl_custom_user_avatars_allow_upload' );
        delete_option( 'zl_custom_user_avatars_disable_gravatar' );
        delete_option( 'zl_custom_user_avatars_edit_avatar' );
        delete_option( 'zl_custom_user_avatars_load_scripts' );
        delete_option( 'zl_custom_user_avatars_resize_crop' );
        delete_option( 'zl_custom_user_avatars_resize_h' );
        delete_option( 'zl_custom_user_avatars_resize_upload' );
        delete_option( 'zl_custom_user_avatars_resize_w' );
        delete_option( 'zl_custom_user_avatars_tinymce' );
        delete_option( 'zl_custom_user_avatars_upload_size_limit' );
        delete_option( 'zl_custom_user_avatars_default_avatar_updated' );
        delete_option( 'zl_custom_user_avatars_media_updated' );
        delete_option( 'zl_custom_user_avatars_users_updated' );
        delete_option( 'zlcua_has_gravatar' );
    }
} else {
    foreach ( $users as $user ) {
        delete_user_meta( $user->ID, $wpdb->get_blog_prefix( $blog_id ) . 'user_avatar' );
    }
    delete_option( 'avatar_default_zl_custom_user_avatars' );
    delete_option( 'zl_custom_user_avatars_allow_upload' );
    delete_option( 'zl_custom_user_avatars_disable_gravatar' );
    delete_option( 'zl_custom_user_avatars_edit_avatar' );
    delete_option( 'zl_custom_user_avatars_load_scripts' );
    delete_option( 'zl_custom_user_avatars_resize_crop' );
    delete_option( 'zl_custom_user_avatars_resize_h' );
    delete_option( 'zl_custom_user_avatars_resize_upload' );
    delete_option( 'zl_custom_user_avatars_resize_w' );
    delete_option( 'zl_custom_user_avatars_tinymce' );
    delete_option( 'zl_custom_user_avatars_upload_size_limit' );
    delete_option( 'zl_custom_user_avatars_default_avatar_updated' );
    delete_option( 'zl_custom_user_avatars_media_updated' );
    delete_option( 'zl_custom_user_avatars_users_updated' );
    delete_option( 'zlcua_has_gravatar' );
}
// Delete post meta
delete_post_meta_by_key( '_wp_attachment_zl_custom_user_avatars' );
// Reset all default avatars to Mystery Man
update_option( 'avatar_default', 'mystery' );
