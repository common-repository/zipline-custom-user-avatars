<?php
/**
 * Global variables used in plugin.
 *
 * @package Zipline Custom User Avatars
 */
/**
 * @uses  get_intermediate_image_sizes()
 * @uses  get_option()
 * @uses  wp_max_upload_size()
 */
// Define global variables
global $avatar_default,
       $show_avatars,
       $zlcua_allow_upload,
       $zlcua_avatar_default,
       $zlcua_disable_gravatar,
       $zlcua_edit_avatar,
       $zlcua_resize_crop,
       $zlcua_resize_h,
       $zlcua_resize_upload,
       $zlcua_resize_w,
       $zlcua_tinymce,
       $mustache_original,
       $mustache_medium,
       $mustache_thumbnail,
       $mustache_avatar,
       $mustache_admin,
       $zlcua_default_avatar_updated,
       $zlcua_users_updated,
       $zlcua_media_updated,
       $upload_size_limit,
       $upload_size_limit_with_units,
       $zlcua_user_upload_size_limit,
       $zlcua_upload_size_limit,
       $zlcua_upload_size_limit_with_units,
       $all_sizes,
       $zlcua_hash_gravatar;
// Store if hash has gravatar
$zlcua_hash_gravatar = get_option( 'zlcua_hash_gravatar' );
if ( $zlcua_hash_gravatar != false )
    $zlcua_hash_gravatar = maybe_unserialize( $zlcua_hash_gravatar );
// Default avatar name
$avatar_default = get_option( 'avatar_default' );
// Attachment ID of default avatar
$zlcua_avatar_default = get_option( 'avatar_default_zl_custom_user_avatars' );
// Booleans
$show_avatars           = get_option( 'show_avatars' );
$zlcua_allow_upload     = get_option( 'zl_custom_user_avatars_allow_upload' );
$zlcua_disable_gravatar = get_option( 'zl_custom_user_avatars_disable_gravatar' );
$zlcua_edit_avatar      = get_option( 'zl_custom_user_avatars_edit_avatar' );
$zlcua_resize_crop      = get_option( 'zl_custom_user_avatars_resize_crop' );
$zlcua_resize_upload    = get_option( 'zl_custom_user_avatars_resize_upload' );
$zlcua_tinymce          = get_option( 'zl_custom_user_avatars_tinymce' );
// Resize dimensions
$zlcua_resize_h = get_option( 'zl_custom_user_avatars_resize_h' );
$zlcua_resize_w = get_option( 'zl_custom_user_avatars_resize_w' );
// Default avatar 512x512
$mustache_original = ZLCUA_URL . 'images/zlcua.png';
// Default avatar 300x300
$mustache_medium = ZLCUA_URL . 'images/zlcua-300x300.png';
// Default avatar 150x150
$mustache_thumbnail = ZLCUA_URL . 'images/zlcua-150x150.png';
// Default avatar 96x96
$mustache_avatar = ZLCUA_URL . 'images/zlcua-96x96.png';
// Default avatar 32x32
$mustache_admin = ZLCUA_URL . 'images/zlcua-32x32.png';
// Check for updates
$zlcua_default_avatar_updated = get_option( 'zl_custom_user_avatars_default_avatar_updated' );
$zlcua_users_updated          = get_option( 'zl_custom_user_avatars_users_updated' );
$zlcua_media_updated          = get_option( 'zl_custom_user_avatars_media_updated' );
// Server upload size limit
$upload_size_limit = wp_max_upload_size();
// Convert to KB
if ( $upload_size_limit > 1024 ) {
    $upload_size_limit /= 1024;
}
$upload_size_limit_with_units = (int) $upload_size_limit . 'KB';
// User upload size limit
$zlcua_user_upload_size_limit = get_option( 'zl_custom_user_avatars_upload_size_limit' );
if ( $zlcua_user_upload_size_limit == 0 || $zlcua_user_upload_size_limit > wp_max_upload_size() ) {
    $zlcua_user_upload_size_limit = wp_max_upload_size();
}
// Value in bytes
$zlcua_upload_size_limit = $zlcua_user_upload_size_limit;
// Convert to KB
if ( $zlcua_user_upload_size_limit > 1024 ) {
    $zlcua_user_upload_size_limit /= 1024;
}
$zlcua_upload_size_limit_with_units = (int) $zlcua_user_upload_size_limit . 'KB';
// Check for custom image sizes
$all_sizes = array_merge( get_intermediate_image_sizes(), array ( 'original' ) );
