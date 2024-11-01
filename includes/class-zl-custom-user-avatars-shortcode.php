<?php

/**
 * Defines shortcodes.
 *
 * @package Zipline Custom User Avatars
 */
class ZL_Custom_User_Avatars_Shortcode {
    /**
     * Constructor
     *
     * @uses  object $zl_custom_user_avatars
     * @uses  add_action()
     * @uses  add_shortcode()
     */
    public function __construct() {
        global $zl_custom_user_avatars;
        add_shortcode( 'avatar', array ( $this, 'zlcua_shortcode' ) );
        add_shortcode( 'avatar_upload', array ( $this, 'zlcua_edit_shortcode' ) );
        // Add avatar and scripts to avatar_upload
        add_action( 'zlcua_show_profile', array ( $zl_custom_user_avatars, 'zlcua_action_show_user_profile' ) );
        add_action( 'zlcua_show_profile', array ( $zl_custom_user_avatars, 'zlcua_media_upload_scripts' ) );
        add_action( 'zlcua_update', array ( $zl_custom_user_avatars, 'zlcua_action_process_option_update' ) );
        // Add error messages to avatar_upload
        add_action( 'zlcua_update_errors', array ( $zl_custom_user_avatars, 'zlcua_upload_errors' ), 10, 3 );
    }

    /**
     * Display shortcode
     *
     * @param array  $atts
     * @param string $content
     *
     * @uses  array $_wp_additional_image_sizes
     * @uses  array $all_sizes
     * @uses  int $blog_id
     * @uses  object $post
     * @uses  object $wpdb
     * @uses  do_shortcode()
     * @uses  get_attachment_link()
     * @uses  get_blog_prefix()
     * @uses  get_option()
     * @uses  get_user_by()
     * @uses  get_query_var()
     * @uses  get_the_author_meta()
     * @uses  get_user_meta()
     * @uses  get_zl_custom_user_avatars_src()
     * @uses  get_zl_custom_user_avatars()
     * @uses  image_add_caption()
     * @uses  is_author()
     * @uses  shortcode_atts()
     * @return string
     */
    public function zlcua_shortcode( $atts, $content = null ) {
        global $all_sizes, $blog_id, $post, $wpdb;
        // Set shortcode attributes
        extract( shortcode_atts( array ( 'user' => "", 'size' => '96', 'align' => "", 'link' => "", 'target' => "" ), $atts ) );
        // Find user by ID, login, slug, or e-mail address
        if ( ! empty( $user ) ) {
            if ( $user == 'current' ) {
                $user = wp_get_current_user();
            } else {
                $user = is_numeric( $user ) ? get_user_by( 'id', $user ) : get_user_by( 'login', $user );
                $user = empty( $user ) ? get_user_by( 'slug', $user ) : $user;
                $user = empty( $user ) ? get_user_by( 'email', $user ) : $user;
            }
        } else {
            // Find author's name if id_or_email is empty
            $author_name = get_query_var( 'author_name' );
            if ( is_author() ) {
                // On author page, get user by page slug
                $user = get_user_by( 'slug', $author_name );
            } else {
                // On post, get user by author meta
                $user_id = get_the_author_meta( 'ID' );
                $user    = get_user_by( 'id', $user_id );
            }
        }
        // Numeric sizes leave as-is
        $get_size = $size;
        // Check for custom image sizes if there are captions
        if ( ! empty( $content ) ) {
            if ( in_array( $size, $all_sizes ) ) {
                if ( in_array( $size, array ( 'original', 'large', 'medium', 'thumbnail' ) ) ) {
                    $get_size = ( $size == 'original' ) ? get_option( 'large_size_w' ) : get_option( $size . '_size_w' );
                } else {
                    $get_size = $_wp_additional_image_sizes[$size]['width'];
                }
            }
        }
        // Get user ID
        $id_or_email = ! empty( $user ) ? $user->ID : 'unknown@gravatar.com';
        // Check if link is set
        if ( ! empty( $link ) ) {
            // CSS class is same as link type, except for URL
            $link_class = $link;
            if ( $link == 'file' ) {
                // Get image src
                $link = get_zl_custom_user_avatars_src( $id_or_email, 'original' );
            } elseif ( $link == 'attachment' ) {
                // Get attachment URL
                $link = get_attachment_link( get_the_author_meta( $wpdb->get_blog_prefix( $blog_id ) . 'user_avatar', $id_or_email ) );
            } else {
                // URL
                $link_class = 'custom';
            }
            // Open in new window
            $target_link = ! empty( $target ) ? ' target="' . $target . '"' : "";
            // Wrap the avatar inside the link
            $html = '<a href="' . $link . '" class="zl-custom-user-avatars-link zl-custom-user-avatars-' . $link_class . '"' . $target_link . '>' . get_zl_custom_user_avatars( $id_or_email, $get_size, $align ) . '</a>';
        } else {
            $html = get_zl_custom_user_avatars( $id_or_email, $get_size, $align );
        }
        // Check if caption is set
        if ( ! empty( $content ) ) {
            // Get attachment ID
            $zlcua = get_user_meta( $id_or_email, $wpdb->get_blog_prefix( $blog_id ) . 'user_avatar', true );
            // Clean up caption
            $content = trim( $content );
            $content = preg_replace( '/\r|\n/', "", $content );
            $content = preg_replace( '/<\/p><p>/', "", $content, 1 );
            $content = preg_replace( '/<\/p><p>$/', "", $content );
            $content = str_replace( '</p><p>', "<br /><br />", $content );
            $avatar  = do_shortcode( image_add_caption( $html, $zlcua, $content, $title = "", $align, $link, $get_size, $alt = "" ) );
        } else {
            $avatar = $html;
        }
        return $avatar;
    }

    /**
     * Update user
     *
     * @param bool $user_id
     *
     * @uses  add_query_arg()
     * @uses  apply_filters()
     * @uses  do_action_ref_array()
     * @uses  wp_get_referer()
     * @uses  wp_redirect()
     * @uses  wp_safe_redirect()
     */
    private function zlcua_edit_user( $user_id = 0 ) {
        $update = $user_id ? true : false;
        $user   = new stdClass;
        $errors = new WP_Error();
        do_action_ref_array( 'zlcua_update_errors', array ( &$errors, $update, &$user ) );
        if ( $errors->get_error_codes() ) {
            // Return with errors
            return $errors;
        }
        if ( $update ) {
            // Redirect with updated variable
            $redirect_url = esc_url_raw( add_query_arg( array ( 'updated' => '1' ), wp_get_referer() ) );
            /**
             * Deprecated filter wpua_edit_user_redirect_url
             */
            $redirect_url = apply_filters_deprecated( 'wpua_edit_user_redirect_url', array( $redirect_url ), '1.0.0', 'zlcua_edit_user_redirect_url' );

            /**
             * Filter redirect URL
             *
             * @param string $redirect_url
             */
            $redirect_url = apply_filters( 'zlcua_edit_user_redirect_url', $redirect_url );

            /**
             * Deprecated filter wpua_edit_user_safe_redirect
             */
            $safe_redirect = apply_filters_deprecated( 'wpua_edit_user_safe_redirect', array( true ), '1.0.0', 'zlcua_edit_user_safe_redirect' );

            /**
             * Filter wp_safe_redirect or wp_redirect
             *
             * @param bool $safe_redirect
             */
            $safe_redirect = apply_filters( 'zlcua_edit_user_safe_redirect', true );
            $safe_redirect ? wp_safe_redirect( $redirect_url ) : wp_redirect( $redirect_url );
            exit;
        }
    }

    /**
     * Edit shortcode
     *
     * @param array $atts
     *
     * @uses  $zl_custom_user_avatars
     * @uses  $zlcua_allow_upload
     * @uses  current_user_can()
     * @uses  do_action()
     * @uses  get_error_messages()
     * @uses  get_user_by()
     * @uses  is_user_logged_in()
     * @uses  is_wp_error()
     * @uses  shortcode_atts()
     * @uses  zlcua_edit_form()
     * @uses  zlcua_edit_user()
     * @uses  zlcua_is_author_or_above()
     * @return string
     */
    public function zlcua_edit_shortcode( $atts ) {
        global $current_user, $errors, $zl_custom_user_avatars, $zlcua_allow_upload;
        // Shortcode only works for users with permission
        if ( $zl_custom_user_avatars->zlcua_is_author_or_above() || ( (bool) $zlcua_allow_upload == 1 && is_user_logged_in() ) ) {
            extract( shortcode_atts( array ( 'user' => "" ), $atts ) );
            // Default user is current user
            $valid_user = $current_user;
            // Find user by ID, login, slug, or e-mail address
            if ( ! empty( $user ) ) {
                $get_user = is_numeric( $user ) ? get_user_by( 'id', $user ) : get_user_by( 'login', $user );
                $get_user = empty( $get_user ) ? get_user_by( 'slug', $user ) : $get_user;
                $get_user = empty( $get_user ) ? get_user_by( 'email', $user ) : $get_user;
                // Check if current user can edit this user
                $valid_user = current_user_can( 'edit_user', $get_user->ID ) ? $get_user : null;
            }
            // Show form only for valid user
            if ( $valid_user ) {
                // Save
                if ( isset( $_POST['submit'] ) && $_POST['submit'] && $_POST['zlcua_action'] === 'update' ) {
                    do_action( 'zlcua_update', $valid_user->ID );
                    // Check for errors
                    $errors = $this->zlcua_edit_user( $valid_user->ID );
                }
                // Errors
                if ( isset( $errors ) && is_wp_error( $errors ) ) {
                    echo '<div class="error"><p>' . implode( "</p>\n<p>", $errors->get_error_messages() ) . '</p></div>';
                } elseif ( isset( $_GET['updated'] ) && $_GET['updated'] === '1' ) {
                    echo '<div class="updated"><p><strong>' . esc_html__( 'Profile updated.', 'zl-custom-user-avatars' ) . '</strong></p></div>';
                }
                // Edit form
                return $this->zlcua_edit_form( $valid_user );
            }
        }
    }

    /**
     * Edit form
     *
     * @param object $user
     *
     * @uses  do_action()
     * @uses  submit_button()
     * @uses  wp_nonce_field()
     */
    private function zlcua_edit_form( $user ) {
        ob_start();
        ?>
        <form id="zlcua-edit-<?php
        echo $user->ID; ?>" class="zlcua-edit" action="" method="post" enctype="multipart/form-data">
            <?php
            do_action( 'zlcua_show_profile', $user ); ?>
            <input type="hidden" name="zlcua_action" value="update"/>
            <input type="hidden" name="user_id" id="user_id" value="<?php
            echo esc_attr( $user->ID ); ?>"/>
            <?php
            wp_nonce_field( 'update-user_' . $user->ID ); ?>
            <?php
            submit_button( esc_html__( 'Update Profile', 'zl-custom-user-avatars' ) ); ?>
        </form>
        <?php
        return ob_get_clean();
    }
}

/**
 * Initialize
 */
function zlcua_shortcode_init() {
    global $zlcua_shortcode;
    $zlcua_shortcode = new ZL_Custom_User_Avatars_Shortcode();
    // Clean output
    ob_start();
}

add_action( 'init', 'zlcua_shortcode_init' );