<?php
/**
 * Admin page to change plugin options.
 *
 * @package Zipline Custom User Avatars
 */
/**
 * @uses  bool $show_avatars
 * @uses  string $upload_size_limit_with_units
 * @uses  object $zlcua_admin
 * @uses  bool $zlcua_allow_upload
 * @uses  bool $zlcua_disable_gravatar
 * @uses  bool $zlcua_edit_avatar
 * @uses  bool $zlcua_resize_crop
 * @uses  int int $zlcua_resize_h
 * @uses  bool $zlcua_resize_upload
 * @uses  int $zlcua_resize_w
 * @uses  object $zlcua_subscriber
 * @uses  bool $zlcua_tinymce
 * @uses  int $zlcua_upload_size_limit
 * @uses  string $zlcua_upload_size_limit_with_units
 * @uses  admin_url()
 * @uses  apply_filters()
 * @uses  checked()
 * @uses  do_action()
 * @uses  do_settings_fields()
 * @uses  get_option()
 * @uses  settings_fields()
 * @uses  submit_button()
 * @uses  zlcua_add_default_avatar()
 */
global $show_avatars, $upload_size_limit_with_units, $zlcua_admin, $zlcua_allow_upload, $zlcua_disable_gravatar, $zlcua_edit_avatar, $zlcua_resize_crop, $zlcua_resize_h, $zlcua_resize_upload, $zlcua_resize_w, $zlcua_subscriber, $zlcua_tinymce, $zlcua_upload_size_limit, $zlcua_upload_size_limit_with_units;
$updated = false;
if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] === 'true' ) {
    $updated = true;
}
$hide_size               = (bool) $zlcua_allow_upload != 1 ? ' style="display:none;"' : "";
$hide_resize             = (bool) $zlcua_resize_upload != 1 ? ' style="display:none;"' : "";
$zlcua_options_page_title = __( 'Zipline Custom User Avatars', 'zl-custom-user-avatars' );

/**
 * Deprecated filter wpua_options_page_title
 */
$zlcua_options_page_title = apply_filters_deprecated( 'wpua_options_page_title', array( $zlcua_options_page_title ), '1.0.0', 'zlcua_options_page_title' );

/**
 * Filter admin page title
 *
 * @param string $zlcua_options_page_title
 */
$zlcua_options_page_title = apply_filters( 'zlcua_options_page_title', $zlcua_options_page_title );
?>
<div class="wrap">
    <h2><?php
        echo $zlcua_options_page_title; ?></h2>
    <table>
        <tr valign="top">
            <td align="top">
                <form method="post" action="<?php
                echo admin_url( 'options.php' ); ?>">
                    <?php
                    settings_fields( 'zlcua-settings-group' ); ?>
                    <?php
                    do_settings_fields( 'zlcua-settings-group', "" ); ?>
                    <table class="form-table">
                        <?php
                        // Format settings in table rows
                        $zlcua_before_settings = array ();
                        /**
                         * Deprecated filter wpua_before_settings
                         */
                        $zlcua_before_settings = apply_filters_deprecated( 'wpua_before_settings', array( $zlcua_before_settings ), '1.0.0', 'zlcua_before_settings' );

                        /**
                         * Filter settings at beginning of table
                         *
                         * @param array $zlcua_before_settings
                         */
                        $zlcua_before_settings = apply_filters( 'zlcua_before_settings', $zlcua_before_settings );
                        echo implode( "", $zlcua_before_settings );
                        ?>
                        <tr valign="top">
                            <th scope="row"><?php
                                _e( 'Settings' ); ?></th>
                            <td>
                                <?php
                                // Format settings in fieldsets
                                $zlcua_settings             = array ();
                                $zlcua_settings['tinymce']  = '<fieldset>
              <label for="zl_custom_user_avatars_tinymce">
                <input name="zl_custom_user_avatars_tinymce" type="checkbox" id="zl_custom_user_avatars_tinymce" value="1" ' . checked( $zlcua_tinymce, 1, 0 ) . ' />'
                                                             . esc_html__( 'Add avatar button to Visual Editor', 'zl-custom-user-avatars' ) . '
              </label>
            </fieldset>';
                                $zlcua_settings['upload']   = '<fieldset>
              <label for="zl_custom_user_avatars_allow_upload">
                <input name="zl_custom_user_avatars_allow_upload" type="checkbox" id="zl_custom_user_avatars_allow_upload" value="1" ' . checked( $zlcua_allow_upload, 1, 0 ) . ' />'
                                                             . esc_html__( 'Allow Contributors & Subscribers to upload avatars', 'zl-custom-user-avatars' ) . '
              </label>
            </fieldset>';
                                $zlcua_settings['gravatar'] = '<fieldset>
              <label for="zl_custom_user_avatars_disable_gravatar">
                <input name="zl_custom_user_avatars_disable_gravatar" type="checkbox" id="zl_custom_user_avatars_disable_gravatar" value="1" ' . checked( $zlcua_disable_gravatar, 1, 0 ) . ' />'
                                                             . esc_html__( 'Disable Gravatar and use only local avatars', 'zl-custom-user-avatars' ) . '
              </label>
            </fieldset>';
                                /**
                                 * Deprecated filter wpua_settings
                                 */
                                $zlcua_settings = apply_filters_deprecated( 'wpua_settings', array( $zlcua_settings ), '1.0.0', 'zlcua_settings' );

                                /**
                                 * Filter main settings
                                 *
                                 * @param array $zlcua_settings
                                 */
                                $zlcua_settings = apply_filters( 'zlcua_settings', $zlcua_settings );
                                echo implode( "", $zlcua_settings );
                                ?>
                            </td>
                        </tr>
                    </table>
                    <?php
                    // Format settings in table
                    $zlcua_subscriber_settings                        = array ();
                    $zlcua_subscriber_settings['subscriber-settings'] = '<div id="zlcua-contributors-subscribers"' . $hide_size . '>
        <table class="form-table">
          <tr valign="top">
            <th scope="row">
              <label for="zl_custom_user_avatars_upload_size_limit">'
                                                                       . esc_html__( 'Upload Size Limit', 'zl-custom-user-avatars' ) . ' ' . __( '(only for Contributors & Subscribers)', 'zl-custom-user-avatars' ) . '
              </label>
            </th>
            <td>
              <fieldset>
                <legend class="screen-reader-text"><span>' . esc_html__( 'Upload Size Limit', 'zl-custom-user-avatars' ) . ' ' . esc_html__( '(only for Contributors & Subscribers)', 'zl-custom-user-avatars' ) . '</span></legend>
                <input name="zl_custom_user_avatars_upload_size_limit" type="text" id="zl_custom_user_avatars_upload_size_limit" value="' . esc_attr( $zlcua_upload_size_limit ) . '" class="regular-text" />
                <span id="zlcua-readable-size">' . $zlcua_upload_size_limit_with_units . '</span>
                <span id="zlcua-readable-size-error">' . sprintf( __( '%s exceeds the maximum upload size for this site.', 'zl-custom-user-avatars' ), "" ) . '</span>
                <div id="zlcua-slider"></div>
                <span class="description">' . sprintf( __( 'Maximum upload file size: %d%s.', 'zl-custom-user-avatars' ), esc_html( wp_max_upload_size() ), esc_html( ' bytes (' . $upload_size_limit_with_units . ')' ) ) . '</span>
              </fieldset>
              <fieldset>
                <label for="zl_custom_user_avatars_edit_avatar">
                  <input name="zl_custom_user_avatars_edit_avatar" type="checkbox" id="zl_custom_user_avatars_edit_avatar" value="1" ' . checked( $zlcua_edit_avatar, 1, 0 ) . ' />'
                                                                       . __( 'Allow users to edit avatars', 'zl-custom-user-avatars' ) . '
                </label>
              </fieldset>
              <fieldset>
                <label for="zl_custom_user_avatars_resize_upload">
                  <input name="zl_custom_user_avatars_resize_upload" type="checkbox" id="zl_custom_user_avatars_resize_upload" value="1" ' . checked( $zlcua_resize_upload, 1, 0 ) . ' />'
                                                                       . __( 'Resize avatars on upload', 'zl-custom-user-avatars' ) . '
                </label>
              </fieldset>
              <fieldset id="zlcua-resize-sizes"' . $hide_resize . '>
                <label for="zl_custom_user_avatars_resize_w">' . __( 'Width', 'zl-custom-user-avatars' ) . '</label>
                <input name="zl_custom_user_avatars_resize_w" type="number" step="1" min="0" id="zl_custom_user_avatars_resize_w" value="' . get_option( 'zl_custom_user_avatars_resize_w' ) . '" class="small-text" />
                <label for="zl_custom_user_avatars_resize_h">' . __( 'Height', 'zl-custom-user-avatars' ) . '</label>
                <input name="zl_custom_user_avatars_resize_h" type="number" step="1" min="0" id="zl_custom_user_avatars_resize_h" value="' . get_option( 'zl_custom_user_avatars_resize_h' ) . '" class="small-text" />
                <br />
                <input name="zl_custom_user_avatars_resize_crop" type="checkbox" id="zl_custom_user_avatars_resize_crop" value="1" ' . checked( '1', $zlcua_resize_crop, 0 ) . ' />
                <label for="zl_custom_user_avatars_resize_crop">' . __( 'Crop avatars to exact dimensions', 'zl-custom-user-avatars' ) . '</label>
              </fieldset>
            </td>
          </tr>
        </table>
      </div>';
                    /**
                     * Deprecated filter wpua_subscriber_settings
                     */
                    $zlcua_subscriber_settings = apply_filters_deprecated( 'wpua_subscriber_settings', array( $zlcua_subscriber_settings ), '1.0.0', 'zlcua_subscriber_settings' );

                    /**
                     * Filter Subscriber settings
                     *
                     * @param array $zlcua_subscriber_settings
                     */
                    $zlcua_subscriber_settings = apply_filters( 'zlcua_subscriber_settings', $zlcua_subscriber_settings );
                    echo implode( "", $zlcua_subscriber_settings );
                    ?>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php
                                esc_html_e( 'Avatar Display', 'zl-custom-user-avatars' ); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php
                                            esc_html_e( 'Avatar Display', 'zl-custom-user-avatars' ); ?></span></legend>
                                    <label for="show_avatars">
                                        <input type="checkbox" id="show_avatars" name="show_avatars" value="1" <?php
                                        checked( $show_avatars, 1 ); ?> />
                                        <?php
                                        esc_html_e( 'Show Avatars', 'zl-custom-user-avatars' ); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr valign="top" id="avatar-rating" <?php
                        echo ( (bool) $zlcua_disable_gravatar == 1 ) ? 'style="display:none"' : '' ?>>
                            <th scope="row"><?php
                                esc_html_e( 'Maximum Rating', 'zl-custom-user-avatars' ); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php
                                            esc_html_e( 'Maximum Rating', 'zl-custom-user-avatars' ); ?></span></legend>
                                    <?php
                                    $ratings = array (
                                        'G'  => __( 'G &#8212; Suitable for all audiences', 'zl-custom-user-avatars' ),
                                        'PG' => __( 'PG &#8212; Possibly offensive, usually for audiences 13 and above', 'zl-custom-user-avatars' ),
                                        'R'  => __( 'R &#8212; Intended for adult audiences above 17', 'zl-custom-user-avatars' ),
                                        'X'  => __( 'X &#8212; Even more mature than above', 'zl-custom-user-avatars' )
                                    );
                                    foreach ( $ratings as $key => $rating ) :
                                        $selected = ( get_option( 'avatar_rating' ) == $key ) ? 'checked="checked"' : "";
                                        echo "\n\t<label><input type='radio' name='avatar_rating' value='" . esc_attr( $key ) . "' $selected/> $rating</label><br />";
                                    endforeach;
                                    ?>
                                </fieldset>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php
                                esc_html_e( 'Default Avatar', 'zl-custom-user-avatars' ) ?></th>
                            <td class="defaultavatarpicker">
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php
                                            esc_html_e( 'Default Avatar', 'zl-custom-user-avatars' ); ?></span></legend>
                                    <?php
                                    esc_html_e( 'For users without a custom avatar of their own, you can either display a generic logo or a generated one based on their e-mail address.', 'zl-custom-user-avatars' ); ?>
                                    <br/>
                                    <?php
                                    echo $zlcua_admin->zlcua_add_default_avatar(); ?>
                                </fieldset>
                            </td>
                        </tr>
                    </table>
                    <?php
                    submit_button(); ?>
                </form>

            </td>
        </tr>
    </table>
</div>
