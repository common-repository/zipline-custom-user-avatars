<?php

/**
 * Defines all profile and upload settings.
 *
 * @package Zipline Custom User Avatars
 */
class ZL_Custom_User_Avatars {
    /**
     * Constructor
     *
     * @uses  string $pagenow
     * @uses  bool $show_avatars
     * @uses  object $zlcua_admin
     * @uses  bool $zlcua_allow_upload
     * @uses  add_action()
     * @uses  add_filterI]()
     * @uses  is_admin()
     * @uses  is_user_logged_in()
     * @uses  zlcua_is_author_or_above()
     * @uses  zlcua_is_menu_page()
     */
    public function __construct() {
        global $pagenow, $show_avatars, $zlcua_admin, $zlcua_allow_upload;
        // Add ZLCUA to profile for users with permission
        if ( $this->zlcua_is_author_or_above() || ( (bool) $zlcua_allow_upload == 1 && is_user_logged_in() ) ) {
            // Profile functions and scripts
            add_action( 'show_user_profile', function ( $user ) {
                if ( is_admin() ) return;
                $this->zlcua_action_show_user_profile( $user );
            } );
            add_action( 'edit_user_profile', function ( $user ) {
                if ( is_admin() ) return;
                $this->zlcua_action_show_user_profile( $user );
            } );
            add_action( 'personal_options_update', array ( $this, 'zlcua_action_process_option_update' ) );
            add_action( 'edit_user_profile_update', array ( $this, 'zlcua_action_process_option_update' ) );
            add_action( 'user_new_form', array ( $this, 'zlcua_action_show_user_profile' ) );
            add_action( 'user_register', array ( $this, 'zlcua_action_process_option_update' ) );
            // see https://stackoverflow.com/questions/37779680/missing-argument-2-for-a-custom-function
            add_filter( 'user_profile_picture_description', function ( $description = '', $profileuser = null ) {
                ob_start();
                echo '<style>.user-profile-picture > td > .avatar {display: none;}</style>';
                self::zlcua_core_show_user_profile( $profileuser );
                return ob_get_clean();
            }, 9999999999999999999, 2 );
            // Admin scripts
            $pages = array ( 'profile.php', 'options-discussion.php', 'user-edit.php', 'user-new.php' );
            if ( in_array( $pagenow, $pages ) || $zlcua_admin->zlcua_is_menu_page() ) {
                add_action( 'admin_enqueue_scripts', array ( $this, 'zlcua_media_upload_scripts' ) );
            }
            // Front pages
            if ( ! is_admin() ) {
                add_action( 'show_user_profile', array ( 'zl_custom_user_avatars', 'zlcua_media_upload_scripts' ) );
                add_action( 'edit_user_profile', array ( 'zl_custom_user_avatars', 'zlcua_media_upload_scripts' ) );
            }
            if ( ! $this->zlcua_is_author_or_above() ) {
                // Upload errors
                add_action( 'user_profile_update_errors', array ( $this, 'zlcua_upload_errors' ), 10, 3 );
                // Prefilter upload size
                add_filter( 'wp_handle_upload_prefilter', array ( $this, 'zlcua_handle_upload_prefilter' ) );
            }
        }
        add_filter( 'media_view_settings', array ( $this, 'zlcua_media_view_settings' ), 10, 1 );
    }

    /**
     * Avatars have no parent posts
     *
     * @param array $settings
     *
     * @uses  bool $zlcua_is_profile
     * @uses  is_admin()
     * array $settings
     * @uses  object $post
     * @return array
     */
    public function zlcua_media_view_settings( $settings ) {
        global $post, $zlcua_is_profile;
        // Get post ID so not to interfere with media uploads
        $post_id = is_object( $post ) ? $post->ID : 0;
        // Don't use post ID on front pages if there's a ZLCUA uploader
        $settings['post']['id'] = ( ! is_admin() && $zlcua_is_profile == 1 ) ? 0 : $post_id;
        return $settings;
    }

    /**
     * Media Uploader
     *
     * @param object $user
     *
     * @uses  object $current_user
     * @uses  string $mustache_admin
     * @uses  string $pagenow
     * @uses  object $post
     * @uses  bool $show_avatars
     * @uses  object $zl_custom_user_avatars
     * @uses  object $zlcua_admin
     * @uses  object $zlcua_functions
     * @uses  bool $zlcua_is_profile
     * @uses  int $zlcua_upload_size_limit
     * @uses  get_user_by()
     * @uses  wp_enqueue_script()
     * @uses  wp_enqueue_media()
     * @uses  wp_enqueue_style()
     * @uses  wp_localize_script()
     * @uses  wp_max_upload_size()
     * @uses  zlcua_get_avatar_original()
     * @uses  zlcua_is_author_or_above()
     * @uses  zlcua_is_menu_page()
     */
    public static function zlcua_media_upload_scripts( $user = "" ) {
        global $current_user, $mustache_admin, $pagenow, $post, $show_avatars, $zl_custom_user_avatars, $zlcua_admin, $zlcua_functions, $zlcua_is_profile, $zlcua_upload_size_limit;
        // This is a profile page
        $zlcua_is_profile = 1;
        $user_id          = filter_input( INPUT_GET, 'user_id', FILTER_VALIDATE_INT );
        $user             = ( $pagenow == 'user-edit.php' && isset( $user_id ) ) ? get_user_by( 'id', $user_id ) : $current_user;
        wp_enqueue_style( 'zl-custom-user-avatars', ZLCUA_URL . 'css/zl-custom-user-avatars.css', "", ZLCUA_VERSION );
        wp_enqueue_script( 'jquery' );
        if ( $zl_custom_user_avatars->zlcua_is_author_or_above() ) {
            wp_enqueue_script( 'admin-bar' );
            wp_enqueue_media( array ( 'post' => $post ) );
            wp_enqueue_script( 'zl-custom-user-avatars', ZLCUA_URL . 'js/zl-custom-user-avatars.js', array ( 'jquery', 'media-editor' ), ZLCUA_VERSION, true );
        } else {
            wp_enqueue_script( 'zl-custom-user-avatars', ZLCUA_URL . 'js/zl-custom-user-avatars-user.js', array ( 'jquery' ), ZLCUA_VERSION, true );
        }
        // Admin scripts
        if ( $pagenow == 'options-discussion.php' || $zlcua_admin->zlcua_is_menu_page() ) {
            // Size limit slider
            wp_enqueue_script( 'jquery-ui-slider' );
            wp_enqueue_style( 'zl-custom-user-avatars-jqueryui', ZLCUA_URL . 'css/jquery.ui.slider.css', "", null );
            // Default avatar
            wp_localize_script( 'zl-custom-user-avatars', 'zlcua_custom', array ( 'avatar_thumb' => $mustache_admin ) );
            // Settings control
            wp_enqueue_script( 'zl-custom-user-avatars-admin', ZLCUA_URL . 'js/zl-custom-user-avatars-admin.js', array ( 'zl-custom-user-avatars' ), ZLCUA_VERSION, true );
            wp_localize_script( 'zl-custom-user-avatars-admin', 'zlcua_admin', array ( 'upload_size_limit' => $zlcua_upload_size_limit, 'max_upload_size' => wp_max_upload_size() ) );
        } else {
            // Original user avatar
            $avatar_medium_src = (bool) $show_avatars == 1 ? $zlcua_functions->zlcua_get_avatar_original( $user->user_email, 'medium' ) : includes_url() . 'images/blank.gif';
            wp_localize_script( 'zl-custom-user-avatars', 'zlcua_custom', array ( 'avatar_thumb' => $avatar_medium_src ) );
        }
    }

    public static function zlcua_core_show_user_profile( $user ) {
        global $blog_id, $current_user, $show_avatars, $wpdb, $zl_custom_user_avatars, $zlcua_edit_avatar, $zlcua_functions, $zlcua_upload_size_limit_with_units;
        $has_zl_custom_user_avatars = $zlcua_functions->has_zl_custom_user_avatars( @$user->ID );
        // Get ZLCUA attachment ID
        $zlcua = get_user_meta( @$user->ID, $wpdb->get_blog_prefix( $blog_id ) . 'user_avatar', true );
        // Show remove button if ZLCUA is set
        $hide_remove = ! $has_zl_custom_user_avatars ? 'zlcua-hide' : "";
        // Hide image tags if show avatars is off
        $hide_images = ! $has_zl_custom_user_avatars && (bool) $show_avatars == 0 ? 'zlcua-no-avatars' : "";
        // If avatars are enabled, get original avatar image or show blank
        $avatar_medium_src = (bool) $show_avatars == 1 ? $zlcua_functions->zlcua_get_avatar_original( @$user->user_email, 'medium' ) : includes_url() . 'images/blank.gif';
        // Check if user has zl_custom_user_avatars, if not show image from above
        $avatar_medium = $has_zl_custom_user_avatars ? $zlcua_functions->get_zl_custom_user_avatars_src( $user->ID, 'medium' ) : $avatar_medium_src;
        // Check if user has zl_custom_user_avatars, if not show image from above
        $avatar_thumbnail     = $has_zl_custom_user_avatars ? $zlcua_functions->get_zl_custom_user_avatars_src( $user->ID, 96 ) : $avatar_medium_src;
        $edit_attachment_link = esc_url( add_query_arg( array ( 'post' => $zlcua, 'action' => 'edit' ), admin_url( 'post.php' ) ) );
        // Chck if admin page
        ?>
        <input type="hidden" name="zl-custom-user-avatars" id="<?php
        echo ( $user == 'add-new-user' ) ? 'zl-custom-user-avatars' : 'zl-custom-user-avatars-existing' ?>" value="<?php
        echo $zlcua; ?>"/>
        <?php
        if ( $zl_custom_user_avatars->zlcua_is_author_or_above() ) : // Button to launch Media Uploader
            ?>
            <p id="<?php
            echo ( $user == 'add-new-user' ) ? 'zlcua-add-button' : 'zlcua-add-button-existing' ?>">
                <button type="button" class="button" id="<?php
                echo ( $user == 'add-new-user' ) ? 'zlcua-add' : 'zlcua-add-existing' ?>" name="<?php
                echo ( $user == 'add-new-user' ) ? 'zlcua-add' : 'zlcua-add-existing' ?>" data-title="<?php
                _e( 'Choose Image', 'zl-custom-user-avatars' ); ?>: <?php
                echo( ! empty( $user->display_name ) ? $user->display_name : '' ); ?>"><?php
                    _e( 'Choose Image', 'zl-custom-user-avatars' ); ?></button>
            </p>
        <?php
        elseif ( ! $zl_custom_user_avatars->zlcua_is_author_or_above() ) : // Upload button
            ?>
            <p id="<?php
            echo ( $user == 'add-new-user' ) ? 'zlcua-upload-button' : 'zlcua-upload-button-existing' ?>">
                <input name="zlcua-file" id="<?php
                echo ( $user == 'add-new-user' ) ? 'zlcua-file' : 'zlcua-file-existing' ?>" type="file"/>
                <button type="submit" class="button" id="<?php
                echo ( $user == 'add-new-user' ) ? 'zlcua-upload' : 'zlcua-upload-existing' ?>" name="submit"
                        value="<?php
                        _e( 'Upload', 'zl-custom-user-avatars' ); ?>"><?php
                    _e( 'Upload', 'zl-custom-user-avatars' ); ?></button>
            </p>
            <p id="<?php
            echo ( $user == 'add-new-user' ) ? 'zlcua-upload-messages' : 'zlcua-upload-messages-existing' ?>">
                <span id="<?php
                echo ( $user == 'add-new-user' ) ? 'zlcua-max-upload' : 'zlcua-max-upload-existing' ?>"
                      class="description"><?php
                    printf( __( 'Maximum upload file size: %d%s.', 'zl-custom-user-avatars' ), esc_html( $zlcua_upload_size_limit_with_units ), esc_html( 'KB' ) ); ?></span>
                <span id="<?php
                echo ( $user == 'add-new-user' ) ? 'zlcua-allowed-files' : 'zlcua-allowed-files-existing' ?>"
                      class="description"><?php
                    _e( 'Allowed Files', 'zl-custom-user-avatars' ); ?>: <?php
                    _e( '<code>jpg jpeg png gif</code>', 'zl-custom-user-avatars' ); ?></span>
            </p>
        <?php
        endif; ?>
        <div id="<?php
        echo ( $user == 'add-new-user' ) ? 'zlcua-images' : 'zlcua-images-existing' ?>" class="<?php
        echo $hide_images; ?>">
            <p id="<?php
            echo ( $user == 'add-new-user' ) ? 'zlcua-preview' : 'zlcua-preview-existing' ?>">
                <img src="<?php
                echo $avatar_medium; ?>" alt=""/>
                <span class="description"><?php
                    _e( 'Original Size', 'zl-custom-user-avatars' ); ?></span>
            </p>
            <p id="<?php
            echo ( $user == 'add-new-user' ) ? 'zlcua-thumbnail' : 'zlcua-thumbnail-existing' ?>">
                <img src="<?php
                echo $avatar_thumbnail; ?>" alt=""/>
                <span class="description"><?php
                    _e( 'Thumbnail', 'zl-custom-user-avatars' ); ?></span>
            </p>
            <p id="<?php
            echo ( $user == 'add-new-user' ) ? 'zlcua-remove-button' : 'zlcua-remove-button-existing' ?>" class="<?php
            echo $hide_remove; ?>">
                <button type="button" class="button" id="<?php
                echo ( $user == 'add-new-user' ) ? 'zlcua-remove' : 'zlcua-remove-existing' ?>"
                        name="zlcua-remove"><?php
                    _e( 'Remove Image', 'zl-custom-user-avatars' ); ?></button>
            </p>
            <p id="<?php
            echo ( $user == 'add-new-user' ) ? 'zlcua-undo-button' : 'zlcua-undo-button-existing' ?>">
                <button type="button" class="button" id="<?php
                echo ( $user == 'add-new-user' ) ? 'zlcua-undo' : 'zlcua-undo-existing' ?>" name="zlcua-undo"><?php
                    _e( 'Undo', 'zl-custom-user-avatars' ); ?></button>
            </p>
        </div>
        <?php
    }

    /**
     * Add to edit user profile
     *
     * @param object $user
     *
     * @uses  int $blog_id
     * @uses  object $current_user
     * @uses  bool $show_avatars
     * @uses  object $wpdb
     * @uses  object $zl_custom_user_avatars
     * @uses  bool $zlcua_edit_avatar
     * @uses  object $zlcua_functions
     * @uses  string $zlcua_upload_size_limit_with_units
     * @uses  add_query_arg()
     * @uses  admin_url()
     * @uses  do_action()
     * @uses  get_blog_prefix()
     * @uses  get_user_meta()
     * @uses  get_zl_custom_user_avatars_src()
     * @uses  has_zl_custom_user_avatars()
     * @uses  is_admin()
     * @uses  zlcua_author()
     * @uses  zlcua_get_avatar_original()
     * @uses  zlcua_is_author_or_above()
     */
    public static function zlcua_action_show_user_profile( $user ) {
        $is_admin = is_admin() ? '_admin' : "";
        /**
         * Deprecated action wpua_before_avatar
         */
        do_action_deprecated( 'wpua_before_avatar' . $is_admin, array (), '1.0.0', 'zlcua_before_avatar' . $is_admin );

        do_action( 'zlcua_before_avatar' . $is_admin );
        self::zlcua_core_show_user_profile( $user );
        /**
         * Deprecated action wpua_after_avatar
         */
        do_action_deprecated( 'wpua_after_avatar' . $is_admin, array (), '1.0.0', 'zlcua_after_avatar' . $is_admin );

        do_action( 'zlcua_after_avatar' . $is_admin ); ?>
        <?php
    }

    /**
     * Add upload error messages
     *
     * @param array  $errors
     * @param bool   $update
     * @param object $user
     *
     * @uses  int $zlcua_upload_size_limit
     * @uses  add()
     * @uses  wp_upload_dir()
     */
    public static function zlcua_upload_errors( $errors, $update, $user ) {
        global $zlcua_upload_size_limit;
        if ( $update && ! empty( $_FILES['zlcua-file'] ) ) {
            $size       = sanitize_text_field( $_FILES['zlcua-file']['size'] );
            $type       = sanitize_mime_type( $_FILES['zlcua-file']['type'] );
            $upload_dir = wp_upload_dir();
            // Allow only JPG, GIF, PNG
            if ( ! empty( $type ) && ! preg_match( '/(jpe?g|gif|png)$/i', $type ) ) {
                $errors->add( 'zlcua_file_type', __( 'This file is not an image. Please try another.', 'zl-custom-user-avatars' ) );
            }
            // Upload size limit
            if ( ! empty( $size ) && $size > $zlcua_upload_size_limit ) {
                $errors->add( 'zlcua_file_size', __( 'Memory exceeded. Please try another smaller file.', 'zl-custom-user-avatars' ) );
            }
            // Check if directory is writeable
            if ( ! is_writeable( $upload_dir['path'] ) ) {
                $errors->add( 'zlcua_file_directory', sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?', 'zl-custom-user-avatars' ), $upload_dir['path'] ) );
            }
        }
    }

    /**
     * Set upload size limit
     *
     * @param object $file
     *
     * @uses  int $zlcua_upload_size_limit
     * @uses  add_action()
     * @return object $file
     */
    public function zlcua_handle_upload_prefilter( $file ) {
        global $zlcua_upload_size_limit;
        $size = $file['size'];
        if ( ! empty( $size ) && $size > $zlcua_upload_size_limit ) {
            /**
             * Error handling that only appears on front pages
             */
            function zlcua_file_size_error( $errors, $update, $user ) {
                $errors->add( 'zlcua_file_size', __( 'Memory exceeded. Please try another smaller file.', 'zl-custom-user-avatars' ) );
            }

            add_action( 'user_profile_update_errors', 'zlcua_file_size_error', 10, 3 );
            return;
        }
        return $file;
    }

    /**
     * Update user meta
     *
     * @param int $user_id
     *
     * @uses  int $blog_id
     * @uses  object $post
     * @uses  object $wpdb
     * @uses  object $zl_custom_user_avatars
     * @uses  bool $zlcua_resize_crop
     * @uses  int $zlcua_resize_h
     * @uses  bool $zlcua_resize_upload
     * @uses  int $zlcua_resize_w
     * @uses  add_post_meta()
     * @uses  delete_metadata()
     * @uses  get_blog_prefix()
     * @uses  is_wp_error()
     * @uses  update_post_meta()
     * @uses  update_user_meta()
     * @uses  wp_delete_attachment()
     * @uses  wp_generate_attachment_metadata()
     * @uses  wp_get_image_editor()
     * @uses  wp_handle_upload()
     * @uses  wp_insert_attachment()
     * @uses  WP_Query()
     * @uses  wp_read_image_metadata()
     * @uses  wp_reset_query()
     * @uses  wp_update_attachment_metadata()
     * @uses  wp_upload_dir()
     * @uses  zlcua_is_author_or_above()
     * @uses  object $zlcua_admin
     * @uses  zlcua_has_gravatar()
     */
    public static function zlcua_action_process_option_update( $user_id ) {
        global $blog_id, $post, $wpdb, $zl_custom_user_avatars, $zlcua_resize_crop, $zlcua_resize_h, $zlcua_resize_upload, $zlcua_resize_w, $zlcua_admin;
        // Check if user has publish_posts capability
        if ( $zl_custom_user_avatars->zlcua_is_author_or_above() ) {
            $avatar = filter_input( INPUT_POST, 'zl-custom-user-avatars', FILTER_VALIDATE_INT );
            $zlcua_id = isset( $avatar ) ? strip_tags( $avatar ) : "";
            // Remove old attachment postmeta
            delete_metadata( 'post', null, '_wp_attachment_zl_custom_user_avatars', $user_id, true );
            // Create new attachment postmeta
            add_post_meta( $zlcua_id, '_wp_attachment_zl_custom_user_avatars', $user_id );
            // Update usermeta
            update_user_meta( $user_id, $wpdb->get_blog_prefix( $blog_id ) . 'user_avatar', $zlcua_id );
        } else {
            // Remove attachment info if avatar is blank
            if ( isset( $_POST['zl-custom-user-avatars'] ) && empty( $_POST['zl-custom-user-avatars'] ) ) {
                // Delete other uploads by user
                $q                = array (
                    'author'         => $user_id,
                    'post_type'      => 'attachment',
                    'post_status'    => 'inherit',
                    'posts_per_page' => '-1',
                    'meta_query'     => array (
                        array (
                            'key'     => '_wp_attachment_zl_custom_user_avatars',
                            'value'   => "",
                            'compare' => '!='
                        )
                    )
                );
                $avatars_wp_query = new WP_Query( $q );
                while ( $avatars_wp_query->have_posts() ) : $avatars_wp_query->the_post();
                    wp_delete_attachment( $post->ID );
                endwhile;
                wp_reset_query();
                // Remove attachment postmeta
                delete_metadata( 'post', null, '_wp_attachment_zl_custom_user_avatars', $user_id, true );
                // Remove usermeta
                update_user_meta( $user_id, $wpdb->get_blog_prefix( $blog_id ) . 'user_avatar', "" );
            }
            // Create attachment from upload
            if ( isset( $_POST['submit'] ) && $_POST['submit'] && ! empty( $_FILES['zlcua-file'] ) ) {
                $name       = sanitize_file_name( $_FILES['zlcua-file']['name'] );
                $file       = wp_handle_upload( $_FILES['zlcua-file'], array ( 'test_form' => false ) );
                $type       = sanitize_mime_type( $_FILES['zlcua-file']['type'] );
                $upload_dir = wp_upload_dir();
                if ( is_writeable( $upload_dir['path'] ) ) {
                    if ( ! empty( $type ) && preg_match( '/(jpe?g|gif|png)$/i', $type ) ) {
                        // Resize uploaded image
                        if ( (bool) $zlcua_resize_upload == 1 ) {
                            // Original image
                            $uploaded_image = wp_get_image_editor( $file['file'] );
                            // Check for errors
                            if ( ! is_wp_error( $uploaded_image ) ) {
                                // Resize image
                                $uploaded_image->resize( $zlcua_resize_w, $zlcua_resize_h, $zlcua_resize_crop );
                                // Save image
                                $resized_image = $uploaded_image->save( $file['file'] );
                            }
                        }
                        // Break out file info
                        $name_parts = pathinfo( $name );
                        $name       = trim( substr( $name, 0, -( 1 + strlen( $name_parts['extension'] ) ) ) );
                        $url        = $file['url'];
                        $file       = $file['file'];
                        $title      = $name;
                        // Use image exif/iptc data for title if possible
                        if ( $image_meta = @wp_read_image_metadata( $file ) ) {
                            if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) ) {
                                $title = $image_meta['title'];
                            }
                        }
                        // Construct the attachment array
                        $attachment = array (
                            'guid'           => $url,
                            'post_mime_type' => $type,
                            'post_title'     => $title,
                            'post_content'   => ""
                        );
                        // This should never be set as it would then overwrite an existing attachment
                        if ( isset( $attachment['ID'] ) ) {
                            unset( $attachment['ID'] );
                        }
                        // Save the attachment metadata
                        $attachment_id = wp_insert_attachment( $attachment, $file );
                        if ( ! is_wp_error( $attachment_id ) ) {
                            // Delete other uploads by user
                            $q                = array (
                                'author'         => $user_id,
                                'post_type'      => 'attachment',
                                'post_status'    => 'inherit',
                                'posts_per_page' => '-1',
                                'meta_query'     => array (
                                    array (
                                        'key'     => '_wp_attachment_zl_custom_user_avatars',
                                        'value'   => "",
                                        'compare' => '!='
                                    )
                                )
                            );
                            $avatars_wp_query = new WP_Query( $q );
                            while ( $avatars_wp_query->have_posts() ) : $avatars_wp_query->the_post();
                                wp_delete_attachment( $post->ID );
                            endwhile;
                            wp_reset_query();
                            wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $file ) );
                            // Remove old attachment postmeta
                            delete_metadata( 'post', null, '_wp_attachment_zl_custom_user_avatars', $user_id, true );
                            // Create new attachment postmeta
                            update_post_meta( $attachment_id, '_wp_attachment_zl_custom_user_avatars', $user_id );
                            // Update usermeta
                            update_user_meta( $user_id, $wpdb->get_blog_prefix( $blog_id ) . 'user_avatar', $attachment_id );
                        }
                    }
                }
            }
        }
    }

    /**
     * Check attachment is owned by user
     *
     * @param int  $attachment_id
     * @param int  $user_id
     * @param bool $zlcua_author
     *
     * @uses  get_post()
     * @return bool
     */
    private function zlcua_author( $attachment_id, $user_id, $zlcua_author = 0 ) {
        $attachment = get_post( $attachment_id );
        if ( ! empty( $attachment ) && $attachment->post_author == $user_id ) {
            $zlcua_author = true;
        }
        return (bool) $zlcua_author;
    }

    /**
     * Check if current user has at least Author privileges
     *
     * @uses  current_user_can()
     * @uses  apply_filters()
     * @return bool
     */
    public function zlcua_is_author_or_above() {
        $is_author_or_above = ( current_user_can( 'edit_published_posts' ) && current_user_can( 'upload_files' ) && current_user_can( 'publish_posts' ) && current_user_can( 'delete_published_posts' ) ) ? true : false;
        /**
         * Deprecated filter wpua_is_author_or_above
         */
        $is_author_or_above = apply_filters_deprecated( 'wpua_is_author_or_above', array ( $is_author_or_above ), '1.0.0', 'zlcua_is_author_or_above' );

        /**
         * Filter Author privilege check
         *
         * @param bool $is_author_or_above
         */
        return (bool) apply_filters( 'zlcua_is_author_or_above', $is_author_or_above );
    }
}

/**
 * Initialize WP_User_Avatar
 */
function zlcua_init() {
    global $zl_custom_user_avatars;
    $zl_custom_user_avatars = new ZL_Custom_User_Avatars();
}

add_action( 'init', 'zlcua_init' );
