<?php
/**
 * Public user functions.
 *
 * @package Zipline Custom User Avatars
 */
/**
 * Returns true if user has zl_custom_user_avatars
 *
 * @param int|string $id_or_email
 * @param bool       $has_zlcua
 * @param object     $user
 * @param int        $user_id
 *
 * @uses  object $zlcua_functions
 * @return object has_zl_custom_user_avatars()
 */
function has_zl_custom_user_avatar( $id_or_email = "", $has_zlcua = "", $user = "", $user_id = "" ) {
    global $zlcua_functions;
    return $zlcua_functions->has_zl_custom_user_avatar( $id_or_email, $has_zlcua, $user, $user_id );
}

/**
 * Find ZLCUA, show get_avatar if empty
 *
 *
 * @param int|string $size
 * @param string     $align
 * @param string     $alt
 * @param array      $class
 *
 * @param int|string $id_or_email
 *
 * @uses  object $zlcua_functions
 * @return object get_zl_custom_user_avatars()
 */
function get_zl_custom_user_avatar( $id_or_email = "", $size = "", $align = "", $alt = "", $class = [] ) {
    global $zlcua_functions;
    return $zlcua_functions->get_zl_custom_user_avatar( $id_or_email, $size, $align, $alt, $class );
}

/**
 * Return just the image src
 *
 *
 * @param int|string $id_or_email
 * @param int|string $size
 * @param string     $align
 *
 * @uses  object $zlcua_functions
 * @return object get_zl_custom_user_avatar_src()
 */
function get_zl_custom_user_avatar_src( $id_or_email = "", $size = "", $align = "" ) {
    global $zlcua_functions;
    return $zlcua_functions->get_zl_custom_user_avatar_src( $id_or_email, $size, $align );
}

/**
 * Before wrapper for profile
 *
 * @uses  do_action()
 */
function zlcua_before_avatar() {
    /**
     * Deprecated action wpua_before_avatar
     */
    do_action_deprecated( 'wpua_before_avatar', array(), '1.0.0', 'zlcua_before_avatar' );

    do_action( 'zlcua_before_avatar' );
}

/**
 * After wrapper for profile
 *
 * @uses  do_action()
 */
function zlcua_after_avatar() {
    /**
     * Deprecated action wpua_after_avatar
     */
    do_action_deprecated( 'wpua_after_avatar', array(), '1.0.0', 'zlcua_after_avatar' );

    do_action( 'zlcua_after_avatar' );
}

/**
 * Before avatar container
 *
 * @uses  apply_filters()
 * @uses  bbp_is_edit()
 * @uses  zlcua_has_shortcode()
 */
function zlcua_do_before_avatar() {
    $zlcua_profile_title = '<h3>' . __( 'Profile Picture', 'zl-custom-user-avatars' ) . '</h3>';
    /**
     * Deprecated filter wpua_profile_title
     */
    $zlcua_profile_title = apply_filters_deprecated( 'wpua_profile_title', array( $zlcua_profile_title ), '1.0.0', 'zlcua_profile_title' );

    /**
     * Filter profile title
     *
     * @param string $zlcua_profile_title
     */
    $zlcua_profile_title = apply_filters( 'zlcua_profile_title', $zlcua_profile_title );
    ?>
    <?php
    if ( class_exists( 'bbPress' ) && bbp_is_edit() ) : // Add to bbPress profile with same style ?>
        <h2 class="entry-title"><?php
            _e( 'Profile Picture', 'zl-custom-user-avatars' ); ?></h2>
        <fieldset class="bbp-form">
        <legend><?php
            _e( 'Image', 'zl-custom-user-avatars' ); ?></legend>
    <?php
    elseif ( class_exists( 'WPUF_Main' ) && wpuf_has_shortcode( 'wpuf_editprofile' ) ) : // Add to WP User Frontend profile with same style ?>
        <fieldset>
        <legend><?php
            _e( 'Profile Picture', 'zl-custom-user-avatars' ) ?></legend>
        <table class="wpuf-table">
        <tr>
        <th><label for="zl_custom_user_avatar"><?php
                _e( 'Image', 'zl-custom-user-avatars' ); ?></label></th>
        <td>
    <?php
    else : // Add to profile without table ?>
        <div class="zlcua-edit-container">
        <?php
        echo $zlcua_profile_title; ?>
    <?php
    endif; ?>
    <?php
}

add_action( 'zlcua_before_avatar', 'zlcua_do_before_avatar' );
/**
 * After avatar container
 *
 * @uses  bbp_is_edit()
 * @uses  wpuf_has_shortcode()
 */
function zlcua_do_after_avatar() {
    ?>
    <?php
    if ( class_exists( 'bbPress' ) && bbp_is_edit() ) : // Add to bbPress profile with same style ?>
        </fieldset>
    <?php
    elseif ( class_exists( 'WPUF_Main' ) && wpuf_has_shortcode( 'wpuf_editprofile' ) ) : // Add to WP User Frontend profile with same style ?>
        </td>
        </tr>
        </table>
        </fieldset>
    <?php
    else : // Add to profile without table ?>
        </div>
    <?php
    endif; ?>
    <?php
}

add_action( 'zlcua_after_avatar', 'zlcua_do_after_avatar' );
/**
 * Before wrapper for profile in admin section
 *
 * @uses  do_action()
 */
function zlcua_before_avatar_admin() {
    /**
     * Deprecated action wpua_before_avatar_admin
     */
    do_action_deprecated( 'wpua_before_avatar_admin', array(), '1.0.0', 'zlcua_before_avatar_admin' );

    do_action( 'zlcua_before_avatar_admin' );
}

/**
 * After wrapper for profile in admin section
 *
 * @uses  do_action()
 */
function zlcua_after_avatar_admin() {
    /**
     * Deprecated action wpua_after_avatar_admin
     */
    do_action_deprecated( 'wpua_after_avatar_admin', array(), '1.0.0', 'zlcua_after_avatar_admin' );

    do_action( 'zlcua_after_avatar_admin' );
}

/**
 * Before avatar container in admin section
 */
function zlcua_do_before_avatar_admin() {
    ?>
    <table class="form-table">
    <tr>
    <th><label for="zl_custom_user_avatar"><?php
            _e( 'Profile Picture', 'zl-custom-user-avatars' ); ?></label></th>
    <td>
    <?php
}

add_action( 'zlcua_before_avatar_admin', 'zlcua_do_before_avatar_admin' );
/**
 * After avatar container in admin section
 */
function zlcua_do_after_avatar_admin() {
    ?>
    </td>
    </tr>
    </table>
    <?php
}

add_action( 'zlcua_after_avatar_admin', 'zlcua_do_after_avatar_admin' );
/**
 * Register widget
 *
 * @since 1.9.4
 * @uses  register_widget()
 */
function zlcua_widgets_init() {
    register_widget( 'ZL_Custom_User_Avatars_Profile_Widget' );
}

add_action( 'widgets_init', 'zlcua_widgets_init' );
