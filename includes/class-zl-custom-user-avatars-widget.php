<?php

/**
 * Defines widgets.
 *
 * @package Zipline Custom User Avatars
 */
class ZL_Custom_User_Avatars_Profile_Widget extends WP_Widget {
    /**
     * Constructor
     */
    public function __construct() {
        $widget_ops = array ( 'classname' => 'widget_zl_custom_user_avatars', 'description' => __( 'Insert' ) . ' ' . __( '[avatar_upload]', 'zl-custom-user-avatars' ) . '.' );
        parent::__construct( 'zl_custom_user_avatars_profile', __( 'Zipline Custom User Avatars', 'zl-custom-user-avatars' ), $widget_ops );
    }

    /**
     * Add [avatar_upload] to widget
     *
     * @param array $args
     * @param array $instance
     *
     * @uses  object $zl_custom_user_avatars
     * @uses  bool $zlcua_allow_upload
     * @uses  object $zlcua_shortcode
     * @uses  add_filter()
     * @uses  apply_filters()
     * @uses  is_user_logged_in()
     * @uses  remove_filter()
     * @uses  zlcua_edit_shortcode()
     * @uses  zlcua_is_author_or_above()
     */
    public function widget( $args, $instance ) {
        global $zl_custom_user_avatars, $zlcua_allow_upload, $zlcua_shortcode;
        extract( $args );
        $instance = apply_filters( 'zlcua_widget_instance', $instance );
        $title    = apply_filters( 'widget_title', empty( $instance['title'] ) ? "" : $instance['title'], $instance, $this->id_base );
        $text     = apply_filters( 'widget_text', empty( $instance['text'] ) ? "" : $instance['text'], $instance );
        // Show widget only for users with permission
        if ( $zl_custom_user_avatars->zlcua_is_author_or_above() || ( (bool) $zlcua_allow_upload == 1 && is_user_logged_in() ) ) {
            echo $before_widget;
            if ( ! empty( $title ) ) {
                echo $before_title . $title . $after_title;
            }
            if ( ! empty( $text ) ) {
                echo '<div class="textwidget">';
                echo ! empty( $instance['filter'] ) ? wpautop( $text ) : $text;
                echo '</div>';
            }
            // Remove profile title
            add_filter( 'zlcua_profile_title', '__return_null' );
            // Get [avatar_upload] shortcode
            echo $zlcua_shortcode->zlcua_edit_shortcode( "" );
            remove_filter( 'zlcua_profile_title', '__return_null' );
        }
    }

    /**
     * Set title
     *
     * @param array $instance
     *
     * @uses  wp_parse_args()
     */
    public function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, array ( 'title' => "", 'text' => "" ) );
        $title    = strip_tags( $instance['title'] );
        $text     = esc_textarea( $instance['text'] );
        ?>
        <p>
            <label for="<?php
            echo $this->get_field_id( 'title' ); ?>">
                <?php
                esc_html_e( 'Title:', 'zl-custom-user-avatars' ); ?>
            </label>
            <input class="widefat" id="<?php
            echo $this->get_field_id( 'title' ); ?>" name="<?php
            echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php
            echo esc_attr( $title ); ?>"/>
        </p>
        <label for="<?php
        echo $this->get_field_id( 'filter' ); ?>"><?php
            esc_html_e( 'Description:', 'zl-custom-user-avatars' ); ?></label>
        <textarea class="widefat" rows="3" cols="20" id="<?php
        echo $this->get_field_id( 'text' ); ?>" name="<?php
        echo $this->get_field_name( 'text' ); ?>"><?php
            echo $text; ?></textarea>
        <p>
            <input id="<?php
            echo $this->get_field_id( 'filter' ); ?>" name="<?php
            echo $this->get_field_name( 'filter' ); ?>" type="checkbox" <?php
            checked( isset( $instance['filter'] ) ? $instance['filter'] : 0 ); ?> />
            <label for="<?php
            echo $this->get_field_id( 'filter' ); ?>">
                <?php
                esc_html_e( 'Automatically add paragraphs', 'zl-custom-user-avatars' ); ?>
            </label>
        </p>
        <?php
    }

    /**
     * Update widget
     *
     * @param array $new_instance
     * @param array $old_instance
     *
     * @uses  current_user_can()
     * @return array
     */
    public function update( $new_instance, $old_instance ) {
        $instance          = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        if ( current_user_can( 'unfiltered_html' ) ) {
            $instance['text'] = $new_instance['text'];
        } else {
            $instance['text'] = stripslashes( wp_filter_post_kses( addslashes( $new_instance['text'] ) ) );
        }
        $instance['filter'] = isset( $new_instance['filter'] );
        return $instance;
    }
}
