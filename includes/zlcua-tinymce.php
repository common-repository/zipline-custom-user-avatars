<?php
/**
 * TinyMCE button for Visual Editor.
 *
 * @package Zipline Custom User Avatars
 */
/**
 * Add TinyMCE button
 *
 * @uses  add_filter()
 * @uses  get_user_option()
 */
function zlcua_add_buttons() {
    // Add only in Rich Editor mode
    if ( get_user_option( 'rich_editing' ) == 'true' ) {
        add_filter( 'mce_external_plugins', 'zlcua_add_tinymce_plugin' );
        add_filter( 'mce_buttons', 'zlcua_register_button' );
    }
}

add_action( 'init', 'zlcua_add_buttons' );
/**
 * Register TinyMCE button
 *
 * @param array $buttons
 *
 * @return array
 */
function zlcua_register_button( $buttons ) {
    array_push( $buttons, 'separator', 'zlCustomUserAvatars' );
    return $buttons;
}

/**
 * Load TinyMCE plugin
 *
 * @param array $plugin_array
 *
 * @return array
 */
function zlcua_add_tinymce_plugin( $plugins ) {
    $plugins['zlCustomUserAvatars'] = ZLCUA_INC_URL . 'tinymce/editor_plugin.js';
    return $plugins;
}

/**
 * Call TinyMCE window content via admin-ajax
 *
 */
function zlcua_ajax_tinymce() {
    include_once( ZLCUA_INC . 'tinymce/window.php' );
    die();
}

add_action( 'wp_ajax_zl_custom_user_avatars_tinymce', 'zlcua_ajax_tinymce' );
