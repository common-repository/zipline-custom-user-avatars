<?php

/**
 * Core user functions.
 *
 * @package Zipline Custom User Avatars
 */
class ZL_Custom_User_Avatars_Functions {
    /**
     * Constructor
     *
     * @uses  add_filter()
     * @uses  register_activation_hook()
     * @uses  register_deactivation_hook()
     */
    public function __construct() {
        add_filter( 'get_avatar', array ( $this, 'zlcua_get_avatar_filter' ), 10, 6 );
        add_filter( 'get_avatar_url', array ( $this, 'zlcua_get_avatar_url' ), 10, 3 );
        // Filter to display Zipline Custom User Avatars at Buddypress
        add_filter( 'bp_core_fetch_avatar', array ( $this, 'zlcua_bp_core_fetch_avatar_filter' ), 10, 5 );
        // Filter to display Zipline Custom User Avatars by URL at Buddypress
        add_filter( 'bp_core_fetch_avatar_url', array ( $this, 'zlcua_bp_core_fetch_avatar_url_filter' ), 10, 5 );
    }

    function zlcua_get_avatar_url( $url, $id_or_email, $args ) {
        global $zlcua_disable_gravatar;
        $user_id = null;
        if ( is_object( $id_or_email ) ) {
            if ( ! empty( $id_or_email->comment_author_email ) ) {
                $user_id = $id_or_email->user_id;
            }
        } else {
            if ( is_email( $id_or_email ) ) {
                $user = get_user_by( 'email', $id_or_email );
                if ( $user ) {
                    $user_id = $user->ID;
                }
            } else {
                $user_id = $id_or_email;
            }
        }
        // First checking custom avatar.
        if ( $this->has_zl_custom_user_avatars( $user_id ) ) {
            $url = $this->get_zl_custom_user_avatars_src( $user_id );
        } else if ( $zlcua_disable_gravatar ) {
            $url = $this->zlcua_get_default_avatar_url( $url, $id_or_email, $args );
        } else {
            $has_valid_url = $this->zlcua_has_gravatar( $id_or_email );
            if ( ! $has_valid_url ) {
                $url = $this->zlcua_get_default_avatar_url( $url, $id_or_email, $args );
            }
        }
        /**
         * Deprecated filter wpua_get_avatar_filter_url
         */
        $url = apply_filters_deprecated( 'wpua_get_avatar_filter_url', array( $url, $id_or_email ), '1.0.0', 'zlcua_get_avatar_filter_url' );

        /**
         * Filter get_avatar_url filter
         *
         * @param string     $url
         * @param int|string $id_or_email
         * @param array      $args
         */
        return apply_filters( 'zlcua_get_avatar_filter_url', $url, $id_or_email );
    }

    function zlcua_get_default_avatar_url( $url, $id_or_email, $args ) {
        global $avatar_default, $mustache_admin, $mustache_avatar, $mustache_medium, $mustache_original, $mustache_thumbnail, $post, $zlcua_avatar_default, $zlcua_disable_gravatar, $zlcua_functions;
        $default_image_details = array ();
        $size                  = ! empty( $args['size'] ) ? $args['size'] : 96;
        // Show custom Default Avatar
        if ( ! empty( $zlcua_avatar_default ) && $zlcua_functions->zlcua_attachment_is_image( $zlcua_avatar_default ) ) {
            // Get image
            $zlcua_avatar_default_image = $zlcua_functions->zlcua_get_attachment_image_src( $zlcua_avatar_default, array ( $size, $size ) );
            // Image src
            $url = $zlcua_avatar_default_image[0];
            // Add dimensions if numeric size
        } else {
            // Get mustache image based on numeric size comparison
            if ( $size > get_option( 'medium_size_w' ) ) {
                $url = $mustache_original;
            } elseif ( $size <= get_option( 'medium_size_w' ) && $size > get_option( 'thumbnail_size_w' ) ) {
                $url = $mustache_medium;
            } elseif ( $size <= get_option( 'thumbnail_size_w' ) && $size > 96 ) {
                $url = $mustache_thumbnail;
            } elseif ( $size <= 96 && $size > 32 ) {
                $url = $mustache_avatar;
            } elseif ( $size <= 32 ) {
                $url = $mustache_admin;
            }
            // Add dimensions if numeric size
        }
        return $url;
    }

    /**
     * Returns Zipline Custom User Avatars or Gravatar-hosted image if user doesn't have Buddypress-uploaded image
     *
     * @param string $avatar
     * @param array  $params
     * @param int    $item_id
     * @param string $avatar_dir
     * @param string $css_id
     * @param int    $html_width
     * @param int    $html_height
     * @param string $avatar_folder_url
     * @param string $avatar_folder_dir
     *
     * @uses object $zlcua_functions
     * @uses zlcua_get_avatar_filter()
     */
    public function zlcua_bp_core_fetch_avatar_filter( $gravatar, $params, $item_id = '', $avatar_dir = '', $css_id = '', $html_width = '', $html_height = '', $avatar_folder_url = '', $avatar_folder_dir = '' ) {
        global $zlcua_functions;
        if ( strpos( $gravatar, 'gravatar.com', 0 ) > -1 ) {
            $avatar = $zlcua_functions->zlcua_get_avatar_filter( $gravatar, ( $params['object'] == 'user' ) ? $params['item_id'] : '', ( $params['object'] == 'user' ) ? ( ( $params['type'] == 'thumb' ) ? 50 : 150 ) : 50, '', '' );
            return $avatar;
        } else
            return $gravatar;
    }

    /**
     * Returns WP user default local avatar URL or Gravatar-hosted image URL if user doesn't have Buddypress-uploaded
     * image
     *
     * @param string $avatar
     * @param array  $params
     *
     * @uses object $zlcua_functions
     * @uses zlcua_get_avatar_filter()
     */
    public function zlcua_bp_core_fetch_avatar_url_filter( $gravatar, $params ) {
        global $zlcua_functions;
        if ( strpos( $gravatar, 'gravatar.com', 0 ) > -1 ) {
            $avatar = $this->zlcua_get_avatar_url( $gravatar, $params['email'], $params );
            return $avatar;
        } else
            return $gravatar;
    }

    /**
     * Returns true if user has Gravatar-hosted image
     *
     * @param int|string $id_or_email
     * @param bool       $has_gravatar
     * @param int|string $user
     * @param string     $email
     *
     * @uses  get_user_by()
     * @uses  is_wp_error()
     * @uses  wp_cache_get()
     * @uses  wp_cache_set()
     * @uses  wp_remote_head()
     * @return bool $has_gravatar
     */
    public function zlcua_has_gravatar( $id_or_email = "", $has_gravatar = 0, $user = "", $email = "" ) {
        global $zlcua_hash_gravatar, $avatar_default, $mustache_admin, $mustache_avatar, $mustache_medium, $mustache_original, $mustache_thumbnail, $post, $zlcua_avatar_default, $zlcua_disable_gravatar, $zlcua_functions;
        // User has ZLCUA
        //Decide if check gravatar required or not.
        if ( trim( $avatar_default ) != 'zl_custom_user_avatars' )
            return true;
        if ( ! is_object( $id_or_email ) && ! empty( $id_or_email ) ) {
            // Find user by ID or e-mail address
            $user = is_numeric( $id_or_email ) ? get_user_by( 'id', $id_or_email ) : get_user_by( 'email', $id_or_email );
            // Get registered user e-mail address
            $email = ! empty( $user ) ? $user->user_email : "";
        }
        if ( $email == "" ) {
            if ( ! is_numeric( $id_or_email ) and ! is_object( $id_or_email ) )
                $email = $id_or_email;
            elseif ( ! is_numeric( $id_or_email ) and is_object( $id_or_email ) )
                $email = $id_or_email->comment_author_email;
        }
        if ( $email != "" ) {
            $hash = md5( strtolower( trim( $email ) ) );
            //check if gravatar exists for hashtag using options
            if ( is_array( $zlcua_hash_gravatar ) ) {
                if ( array_key_exists( $hash, $zlcua_hash_gravatar ) and is_array( $zlcua_hash_gravatar[$hash] ) and array_key_exists( date( 'm-d-Y' ), $zlcua_hash_gravatar[$hash] ) ) {
                    return (bool) $zlcua_hash_gravatar[$hash][date( 'm-d-Y' )];
                }
            }
            //end
            if ( isset( $_SERVER['HTTPS'] ) && ( 'on' == $_SERVER['HTTPS'] || 1 == $_SERVER['HTTPS'] ) || isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' == $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
                $http = 'https';
            } else {
                $http = 'http';
            }
            $gravatar = $http . '://www.gravatar.com/avatar/' . $hash . '?d=404';
            $data     = wp_cache_get( $hash );
            if ( false === $data ) {
                $response = wp_remote_head( $gravatar );
                $data     = is_wp_error( $response ) ? 'not200' : $response['response']['code'];
                wp_cache_set( $hash, $data, $group = "", $expire = 60 * 5 );
                //here set if hashtag has avatar
                $has_gravatar = ( $data == '200' ) ? true : false;
                if ( $zlcua_hash_gravatar == false ) {
                    $zlcua_hash_gravatar                         = [];
                    $zlcua_hash_gravatar[$hash][date( 'm-d-Y' )] = (bool) $has_gravatar;
                    add_option( 'zlcua_hash_gravatar', serialize( $zlcua_hash_gravatar ), '', false );
                } else {
                    if ( is_array( $zlcua_hash_gravatar ) && ! empty( $zlcua_hash_gravatar ) ) {
                        if ( array_key_exists( $hash, $zlcua_hash_gravatar ) ) {
                            unset( $zlcua_hash_gravatar[$hash] );
                            $zlcua_hash_gravatar[$hash][date( 'm-d-Y' )] = (bool) $has_gravatar;
                            update_option( 'zlcua_hash_gravatar', serialize( $zlcua_hash_gravatar ), false );
                        } else {
                            $zlcua_hash_gravatar[$hash][date( 'm-d-Y' )] = (bool) $has_gravatar;
                            update_option( 'zlcua_hash_gravatar', serialize( $zlcua_hash_gravatar ), false );
                        }
                    }
                }
                //end
            }
            $has_gravatar = ( $data == '200' ) ? true : false;
        } else
            $has_gravatar = false;
        // Check if Gravatar image returns 200 (OK) or 404 (Not Found)
        return (bool) $has_gravatar;
    }

    /**
     * Check if local image
     *
     * @param int $attachment_id
     *
     * @uses  apply_filters()
     * @uses  wp_attachment_is_image()
     * @return bool
     */
    public function zlcua_attachment_is_image( $attachment_id ) {
        $is_image = wp_attachment_is_image( $attachment_id );
        /**
         * Deprecated filter wpua_attachment_is_image
         */
        $is_image = apply_filters_deprecated( 'wpua_attachment_is_image', array( $is_image, $attachment_id ), '1.0.0', 'zlcua_attachment_is_image' );

        /**
         * Filter local image check
         *
         * @param bool $is_image
         * @param int  $attachment_id
         */
        $is_image = apply_filters( 'zlcua_attachment_is_image', $is_image, $attachment_id );
        return (bool) $is_image;
    }

    /**
     * Get local image tag
     *
     * @param int        $attachment_id
     * @param int|string $size
     * @param bool       $icon
     * @param string     $attr
     *
     * @uses  apply_filters()
     * @uses  wp_get_attachment_image()
     * @return string
     */
    public function zlcua_get_attachment_image( $attachment_id, $size = 'thumbnail', $icon = 0, $attr = '' ) {
        $image = wp_get_attachment_image( $attachment_id, $size, $icon, $attr );
        /**
         * Deprecated filter wpua_get_attachment_image
         */
        $image = apply_filters_deprecated( 'wpua_get_attachment_image', array( $image, $attachment_id, $size, $icon, $attr ), '1.0.0', 'zlcua_get_attachment_image' );

        /**
         * Filter local image tag
         *
         * @param string     $image
         * @param int        $attachment_id
         * @param int|string $size
         * @param bool       $icon
         * @param string     $attr
         */
        return apply_filters( 'zlcua_get_attachment_image', $image, $attachment_id, $size, $icon, $attr );
    }

    /**
     * Get local image src
     *
     * @param int        $attachment_id
     * @param int|string $size
     * @param bool       $icon
     *
     * @uses  apply_filters()
     * @uses  wp_get_attachment_image_src()
     * @return array
     */
    public function zlcua_get_attachment_image_src( $attachment_id, $size = 'thumbnail', $icon = 0 ) {
        $image_src_array = wp_get_attachment_image_src( $attachment_id, $size, $icon );
        /**
         * Deprecated filter wpua_get_attachment_image_src
         */
        $image_src_array = apply_filters_deprecated( 'wpua_get_attachment_image_src', array( $image_src_array, $attachment_id, $size, $icon ), '1.0.0', 'zlcua_get_attachment_image_src' );

        /**
         * Filter local image src
         *
         * @param array      $image_src_array
         * @param int        $attachment_id
         * @param int|string $size
         * @param bool       $icon
         */
        return apply_filters( 'zlcua_get_attachment_image_src', $image_src_array, $attachment_id, $size, $icon );
    }

    /**
     * Returns true if user has zl_custom_user_avatars
     *
     * @param int|string $id_or_email
     * @param bool       $has_zlcua
     * @param object     $user
     * @param int        $user_id
     *
     * @uses  int $blog_id
     * @uses  object $wpdb
     * @uses  int $zlcua_avatar_default
     * @uses  object $zlcua_functions
     * @uses  get_user_by()
     * @uses  get_user_meta()
     * @uses  get_blog_prefix()
     * @uses  zlcua_attachment_is_image()
     * @return bool
     */
    public function has_zl_custom_user_avatars( $id_or_email = "", $has_zlcua = 0, $user = "", $user_id = "" ) {
        global $blog_id, $wpdb, $zlcua_avatar_default, $zlcua_functions, $avatar_default;
        if ( ! is_object( $id_or_email ) && ! empty( $id_or_email ) ) {
            // Find user by ID or e-mail address
            $user = is_numeric( $id_or_email ) ? get_user_by( 'id', $id_or_email ) : get_user_by( 'email', $id_or_email );
            // Get registered user ID
            $user_id = ! empty( $user ) ? $user->ID : "";
        }
        $zlcua = get_user_meta( $user_id, $wpdb->get_blog_prefix( $blog_id ) . 'user_avatar', true );
        // Check if avatar is same as default avatar or on excluded list
        $has_zlcua = ! empty( $zlcua ) && ( $avatar_default != 'zl_custom_user_avatars' or $zlcua != $zlcua_avatar_default ) && $zlcua_functions->zlcua_attachment_is_image( $zlcua ) ? true : false;
        return (bool) $has_zlcua;
    }

    /**
     * Retrieve default image url set by admin.
     */
    public function zlcua_default_image( $size ) {
        global $avatar_default, $mustache_admin, $mustache_avatar, $mustache_medium, $mustache_original, $mustache_thumbnail, $post, $zlcua_avatar_default, $zlcua_disable_gravatar, $zlcua_functions;
        $default_image_details = array ();
        // Show custom Default Avatar
        if ( ! empty( $zlcua_avatar_default ) && $zlcua_functions->zlcua_attachment_is_image( $zlcua_avatar_default ) ) {
            // Get image
            $zlcua_avatar_default_image = $zlcua_functions->zlcua_get_attachment_image_src( $zlcua_avatar_default, array ( $size, $size ) );
            // Image src
            $default = $zlcua_avatar_default_image[0];
            // Add dimensions if numeric size
            $default_image_details['dimensions'] = ' width="' . $zlcua_avatar_default_image[1] . '" height="' . $zlcua_avatar_default_image[2] . '"';
        } else {
            // Get mustache image based on numeric size comparison
            if ( $size > get_option( 'medium_size_w' ) ) {
                $default = $mustache_original;
            } elseif ( $size <= get_option( 'medium_size_w' ) && $size > get_option( 'thumbnail_size_w' ) ) {
                $default = $mustache_medium;
            } elseif ( $size <= get_option( 'thumbnail_size_w' ) && $size > 96 ) {
                $default = $mustache_thumbnail;
            } elseif ( $size <= 96 && $size > 32 ) {
                $default = $mustache_avatar;
            } elseif ( $size <= 32 ) {
                $default = $mustache_admin;
            }
            // Add dimensions if numeric size
            $default_image_details['dimensions'] = ' width="' . $size . '" height="' . $size . '"';
        }
        // Construct the img tag
        $default_image_details['size'] = $size;
        $default_image_details['src']  = $default;
        return $default_image_details;
    }

    /**
     * Replace get_avatar only in get_zl_custom_user_avatars
     *
     * @param string     $avatar
     * @param int|string $id_or_email
     * @param int|string $size
     * @param string     $default
     * @param string     $alt
     * @param array      $args
     *
     * @uses  string $avatar_default
     * @uses  string $mustache_admin
     * @uses  string $mustache_avatar
     * @uses  string $mustache_medium
     * @uses  string $mustache_original
     * @uses  string $mustache_thumbnail
     * @uses  object $post
     * @uses  int $zlcua_avatar_default
     * @uses  bool $zlcua_disable_gravatar
     * @uses  object $zlcua_functions
     * @uses  apply_filters()
     * @uses  get_zl_custom_user_avatars()
     * @uses  has_zl_custom_user_avatars()
     * @uses  zlcua_has_gravatar()
     * @uses  zlcua_attachment_is_image()
     * @uses  zlcua_get_attachment_image_src()
     * @uses  get_option()
     * @return string $avatar
     */
    public function zlcua_get_avatar_filter( $avatar, $id_or_email = "", $size = "", $default = "", $alt = "", $args = [] ) {
        global $avatar_default, $mustache_admin, $mustache_avatar, $mustache_medium, $mustache_original, $mustache_thumbnail, $post, $zlcua_avatar_default, $zlcua_disable_gravatar, $zlcua_functions;
        // User has ZLCUA
        if ( $alt == '' ) {
            /**
             * Deprecated filter wpua_default_alt_tag
             */
            $alt = apply_filters_deprecated( 'wpua_default_alt_tag', array( __( "Avatar", 'zl-custom-user-avatars' ) ), '1.0.0', 'zlcua_default_alt_tag' );

            $alt = apply_filters( 'zlcua_default_alt_tag', $alt );
        }
        $alt   = esc_attr( $alt );
        $size  = esc_attr( $size );
        $class = [];
        if ( isset( $args['class'] ) ) {
            if ( is_array( $args['class'] ) ) {
                $class = array_merge( $class, $args['class'] );
            } else {
                $class[] = $args['class'];
            }
        }
        $avatar = str_replace( 'gravatar_default', '', $avatar );
        if ( is_object( $id_or_email ) ) {
            if ( ! empty( $id_or_email->comment_author_email ) ) {
                $avatar = $this->get_zl_custom_user_avatars( $id_or_email, $size, $default, $alt, $class );
            } else {
                $avatar = $this->get_zl_custom_user_avatars( 'unknown@gravatar.com', $size, $default, $alt, $class );
            }
        } else {
            if ( $this->has_zl_custom_user_avatars( $id_or_email ) ) {
                $avatar = $this->get_zl_custom_user_avatars( $id_or_email, $size, $default, $alt, $class );
                // User has Gravatar and Gravatar is not disabled
            } elseif ( (bool) $zlcua_disable_gravatar != 1 && $zlcua_functions->zlcua_has_gravatar( $id_or_email ) ) {
                // find our src
                if ( ! empty( $avatar ) ) {
                    $output                        = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $avatar, $matches, PREG_SET_ORDER );
                    $zlcua_avatar_image_src         = ! empty( $matches ) ? $matches [0] [1] : "";
                    $default_image_details         = $this->zlcua_default_image( $size );
                    $zlcua_default_avatar_image_src = $default_image_details['src'];
                    $zlcua_final_avatar_image_src   = str_replace( 'd=zl_custom_user_avatars', 'd=' . urlencode( $zlcua_default_avatar_image_src ), $zlcua_avatar_image_src );
                }
                $class_string = ! empty( $class ) ? ' ' . esc_attr( implode( ' ', $class ) ) : '';
                $avatar       = '<img src="' . $zlcua_final_avatar_image_src . '"' . $default_image_details['dimensions'] . ' alt="' . $alt . '" class="avatar avatar-' . $size . ' zl-custom-user-avatars zl-custom-user-avatars-' . $size . ' photo avatar-default' . $class_string . '" />';
                // User doesn't have ZLCUA or Gravatar and Default Avatar is zl_custom_user_avatars, show custom Default Avatar
            } elseif ( $avatar_default == 'zl_custom_user_avatars' ) {
                $default_image_details = $this->zlcua_default_image( $size );
                $class_string          = ! empty( $class ) ? ' ' . esc_attr( implode( ' ', $class ) ) : '';
                $avatar                = '<img src="' . $default_image_details['src'] . '"' . $default_image_details['dimensions'] . ' alt="' . $alt . '" class="avatar avatar-' . $size . ' zl-custom-user-avatars zl-custom-user-avatars-' . $size . ' photo avatar-default' . $class_string . '" />';
                return $avatar;
            }
        }
        /**
         * Deprecated filter wpua_get_attachment_image_src
         */
        $avatar = apply_filters_deprecated( 'wpua_get_avatar_filter', array( $avatar, $id_or_email, $size, $default, $alt ), '1.0.0', 'zlcua_get_avatar_filter' );

        /**
         * Filter get_avatar filter
         *
         * @param string     $avatar
         * @param int|string $id_or_email
         * @param int|string $size
         * @param string     $default
         * @param string     $alt
         */
        return apply_filters( 'zlcua_get_avatar_filter', $avatar, $id_or_email, $size, $default, $alt );
    }

    /**
     * Get original avatar, for when user removes zl_custom_user_avatars
     *
     * @param int|string $id_or_email
     * @param int|string $size
     * @param string     $default
     * @param string     $alt
     *
     * @uses  string $avatar_default
     * @uses  string $mustache_avatar
     * @uses  int $zlcua_avatar_default
     * @uses  bool $zlcua_disable_gravatar
     * @uses  object $zlcua_functions
     * @uses  zlcua_attachment_is_image()
     * @uses  zlcua_get_attachment_image_src()
     * @uses  zlcua_has_gravatar()
     * @uses  add_filter()
     * @uses  apply_filters()
     * @uses  get_avatar()
     * @uses  remove_filter()
     * @return string $default
     */
    public function zlcua_get_avatar_original( $id_or_email = "", $size = "", $default = "", $alt = "" ) {
        global $avatar_default, $mustache_avatar, $zlcua_avatar_default, $zlcua_disable_gravatar, $zlcua_functions;
        // Remove get_avatar filter
        remove_filter( 'get_avatar', array ( $zlcua_functions, 'zlcua_get_avatar_filter' ) );
        if ( (bool) $zlcua_disable_gravatar != 1 ) {
            // User doesn't have Gravatar and Default Avatar is zl_custom_user_avatars, show custom Default Avatar
            if ( ! $zlcua_functions->zlcua_has_gravatar( $id_or_email ) && $avatar_default == 'zl_custom_user_avatars' ) {
                // Show custom Default Avatar
                if ( ! empty( $zlcua_avatar_default ) && $zlcua_functions->zlcua_attachment_is_image( $zlcua_avatar_default ) ) {
                    // $zlcua_avatar_default_image = $zlcua_functions->zlcua_get_attachment_image_src($zlcua_avatar_default, array($size,$size));
                    $size_numeric_w_x_h        = array ( get_option( $size . '_size_w' ), get_option( $size . '_size_h' ) );
                    $zlcua_avatar_default_image = $zlcua_functions->zlcua_get_attachment_image_src( $zlcua_avatar_default, $size_numeric_w_x_h );
                    $default                   = $zlcua_avatar_default_image[0];
                } else {
                    $default = $mustache_avatar;
                }
            } else {
                // Get image from Gravatar, whether it's the user's image or default image
                $zlcua_image = get_avatar( $id_or_email, $size );
                // Takes the img tag, extracts the src
                $output  = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $zlcua_image, $matches, PREG_SET_ORDER );
                $default = ! empty( $matches ) ? $matches [0] [1] : "";
            }
        } else {
            if ( ! empty( $zlcua_avatar_default ) && $zlcua_functions->zlcua_attachment_is_image( $zlcua_avatar_default ) ) {
//      $zlcua_avatar_default_image = $zlcua_functions->zlcua_get_attachment_image_src($zlcua_avatar_default, array($size,$size));
                $size_numeric_w_x_h        = array ( get_option( $size . '_size_w' ), get_option( $size . '_size_h' ) );
                $zlcua_avatar_default_image = $zlcua_functions->zlcua_get_attachment_image_src( $zlcua_avatar_default, $size_numeric_w_x_h );
                $default                   = $zlcua_avatar_default_image[0];
            } else {
                $default = $mustache_avatar;
            }
        }
        // Enable get_avatar filter
        add_filter( 'get_avatar', array ( $zlcua_functions, 'zlcua_get_avatar_filter' ), 10, 5 );
        /**
         * Deprecated filter wpua_get_avatar_original
         */
        $default = apply_filters_deprecated( 'wpua_get_avatar_original', array( $default ), '1.0.0', 'zlcua_get_avatar_original' );

        /**
         * Filter original avatar src
         *
         * @param string $default
         */
        return apply_filters( 'zlcua_get_avatar_original', $default );
    }

    /**
     * Find ZLCUA, show get_avatar if empty
     *
     * @param int|string $id_or_email
     * @param int|string $size
     * @param string     $align
     * @param string     $alt
     * @param array      $class
     *
     * @uses  array $_wp_additional_image_sizes
     * @uses  array $all_sizes
     * @uses  string $avatar_default
     * @uses  int $blog_id
     * @uses  object $post
     * @uses  object $wpdb
     * @uses  int $zlcua_avatar_default
     * @uses  object $zlcua_functions
     * @uses  apply_filters()
     * @uses  get_the_author_meta()
     * @uses  get_blog_prefix()
     * @uses  get_user_by()
     * @uses  get_query_var()
     * @uses  is_author()
     * @uses  zlcua_attachment_is_image()
     * @uses  zlcua_get_attachment_image_src()
     * @uses  get_option()
     * @uses  get_avatar()
     * @return string $avatar
     */
    public function get_zl_custom_user_avatars( $id_or_email = "", $size = '96', $align = "", $alt = "", $class = [] ) {
        global $all_sizes, $avatar_default, $blog_id, $post, $wpdb, $zlcua_avatar_default, $zlcua_functions, $_wp_additional_image_sizes;
        $email = 'unknown@gravatar.com';
        // Checks if comment
        if ( $alt === '' ) {
            $alt = __( "Avatar", 'zl-custom-user-avatars' );
            /**
             * Deprecated filter wpua_default_alt_tag
             */
            $alt = apply_filters_deprecated( 'wpua_default_alt_tag', array( $alt ), '1.0.0', 'zlcua_default_alt_tag' );

            $alt = apply_filters( 'zlcua_default_alt_tag', $alt );
        }
        if ( is_object( $id_or_email ) ) {
            // Checks if comment author is registered user by user ID
            if ( $id_or_email->user_id != 0 ) {
                $email = $id_or_email->user_id;
                // Checks that comment author isn't anonymous
            } elseif ( ! empty( $id_or_email->comment_author_email ) ) {
                // Checks if comment author is registered user by e-mail address
                $user = get_user_by( 'email', $id_or_email->comment_author_email );
                // Get registered user info from profile, otherwise e-mail address should be value
                $email = ! empty( $user ) ? $user->ID : $id_or_email->comment_author_email;
            }
            $alt = $id_or_email->comment_author;
        } else {
            if ( ! empty( $id_or_email ) ) {
                // Find user by ID or e-mail address
                $user = is_numeric( $id_or_email ) ? get_user_by( 'id', $id_or_email ) : get_user_by( 'email', $id_or_email );
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
            // Set user's ID and name
            if ( ! empty( $user ) ) {
                $email = $user->ID;
                $alt   = $user->display_name;
            }
        }
        $alt   = esc_attr( $alt );
        $size  = esc_attr( $size );
        $class = esc_attr( implode( ' ', $class ) );
        // Checks if user has ZLCUA
        $zlcua_meta = get_the_author_meta( $wpdb->get_blog_prefix( $blog_id ) . 'user_avatar', $email );
        // Add alignment class
        $alignclass = ! empty( $align ) && ( $align == 'left' || $align == 'right' || $align == 'center' ) ? ' align' . $align : ' alignnone';
        // User has ZLCUA, check if on excluded list and bypass get_avatar
        if ( ! empty( $zlcua_meta ) && $zlcua_functions->zlcua_attachment_is_image( $zlcua_meta ) ) {
            // Numeric size use size array
            $get_size = is_numeric( $size ) ? array ( $size, $size ) : $size;
            // Get image src
            $zlcua_image = $zlcua_functions->zlcua_get_attachment_image_src( $zlcua_meta, $get_size );
            // Add dimensions to img only if numeric size was specified
            $dimensions   = is_numeric( $size ) ? ' width="' . $zlcua_image[1] . '" height="' . $zlcua_image[2] . '"' : "";
            $class_string = ! empty( $class ) ? ' ' . $class : '';
            // Construct the img tag
            $avatar = '<img src="' . $zlcua_image[0] . '"' . $dimensions . ' alt="' . $alt . '" class="avatar avatar-' . $size . ' zl-custom-user-avatars zl-custom-user-avatars-' . $size . $alignclass . ' photo' . $class_string . '" />';
        } else {
            // Check for custom image sizes
            if ( in_array( $size, $all_sizes ) ) {
                if ( in_array( $size, array ( 'original', 'large', 'medium', 'thumbnail' ) ) ) {
                    $get_size = ( $size == 'original' ) ? get_option( 'large_size_w' ) : get_option( $size . '_size_w' );
                } else {
                    $get_size = $_wp_additional_image_sizes[$size]['width'];
                }
            } else {
                // Numeric sizes leave as-is
                $get_size = $size;
            }
            // User with no ZLCUA uses get_avatar
            $avatar = get_avatar( $email, $get_size, $default = "", $alt = "", [ 'class' => $class ] );
            // Remove width and height for non-numeric sizes
            if ( in_array( $size, array ( 'original', 'large', 'medium', 'thumbnail' ) ) ) {
                $avatar = preg_replace( '/(width|height)=\"\d*\"\s/', "", $avatar );
                $avatar = preg_replace( "/(width|height)=\'\d*\'\s/", "", $avatar );
            }
            $replace      = array ( 'zl-custom-user-avatars ', 'zl-custom-user-avatars-' . $get_size . ' ', 'zl-custom-user-avatars-' . $size . ' ', 'avatar-' . $get_size, ' photo' );
            $replacements = array ( "", "", "", 'avatar-' . $size, 'zl-custom-user-avatars zl-custom-user-avatars-' . $size . $alignclass . ' photo' );
            $avatar       = str_replace( $replace, $replacements, $avatar );
        }
        /**
         * Deprecated filter get_wp_user_avatar
         */
        $avatar = apply_filters_deprecated( 'get_wp_user_avatar', array( $avatar, $id_or_email, $size, $align, $alt ), '1.0.0', 'get_zl_custom_user_avatars' );

        /**
         * Filter get_zl_custom_user_avatars
         *
         * @param string     $avatar
         * @param int|string $id_or_email
         * @param int|string $size
         * @param string     $align
         * @param string     $alt
         */
        return apply_filters( 'get_zl_custom_user_avatars', $avatar, $id_or_email, $size, $align, $alt );
    }

    /**
     * Return just the image src
     *
     * @param int|string $id_or_email
     * @param int|string $size
     * @param string     $align
     *
     * @uses  get_zl_custom_user_avatars()
     * @return string
     */
    public function get_zl_custom_user_avatars_src( $id_or_email = "", $size = "", $align = "" ) {
        $zlcua_image_src = "";
        // Gets the avatar img tag
        $zlcua_image = $this->get_zl_custom_user_avatars( $id_or_email, $size, $align );
        // Takes the img tag, extracts the src
        if ( ! empty( $zlcua_image ) ) {
            $output         = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $zlcua_image, $matches, PREG_SET_ORDER );
            $zlcua_image_src = ! empty( $matches ) ? $matches [0] [1] : "";
        }
        return $zlcua_image_src;
    }
}

/**
 * Initialize
 */
function zlcua_functions_init() {
    global $zlcua_functions;
    $zlcua_functions = new ZL_Custom_User_Avatars_Functions();
}

add_action( 'plugins_loaded', 'zlcua_functions_init' );
