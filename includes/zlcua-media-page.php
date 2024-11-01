<?php
/**
 * Media Library view of all avatars in use.
 *
 * @package Zipline Custom User Avatars
 */
/**
 * @uses  object $zlcua_admin
 * @uses  _zlcua_get_list_table()
 * @uses  add_query_arg()
 * @uses  check_admin_referer()
 * @uses  current_action()
 * @uses  current_user_can()
 * @uses  display()
 * @uses  esc_url()
 * @uses  find_posts_div()
 * @uses  get_pagenum()
 * @uses  get_search_query
 * @uses  number_format_i18n()
 * @uses  prepare_items()
 * @uses  remove_query_arg()
 * @uses  search_box()
 * @uses  views()
 * @uses  wp_delete_attachment()
 * @uses  wp_die()
 * @uses  wp_enqueue_script()
 * @uses  wp_get_referer()
 * @uses  wp_redirect()
 * @uses  wp_unslash()
 */
/** WordPress Administration Bootstrap */
require_once( ABSPATH . 'wp-admin/admin.php' );
if ( ! current_user_can( 'upload_files' ) )
    wp_die( __( 'You do not have permission to upload files.', 'zl-custom-user-avatars' ) );
global $zlcua_admin;
$wp_list_table = $zlcua_admin->_zlcua_get_list_table( 'ZL_Custom_User_Avatars_List_Table' );
$pagenum       = $wp_list_table->get_pagenum();
// Handle bulk actions
$doaction = $wp_list_table->current_action();
if ( $doaction ) {
    check_admin_referer( 'bulk-media' );
    if ( isset( $_REQUEST['media'] ) ) {
        $args = array (
            'media' => array (
                'filter' => FILTER_VALIDATE_INT,
                'flags'  => FILTER_REQUIRE_ARRAY,
            )
        );

        $post_ids = filter_input_array( INPUT_GET, $args );
    } elseif ( isset( $_REQUEST['ids'] ) ) {
        $args = array (
            'ids' => array (
                'filter' => FILTER_VALIDATE_INT,
                'flags'  => FILTER_REQUIRE_ARRAY,
            )
        );

        $post_ids = explode( ',', filter_input_array( INPUT_GET, $args ) );
    }
    $location = esc_url( add_query_arg( array ( 'page' => 'zl-custom-user-avatars-library' ), 'admin.php' ) );
    if ( $referer = wp_get_referer() ) {
        if ( false !== strpos( $referer, 'admin.php' ) ) {
            $location = remove_query_arg( array ( 'trashed', 'untrashed', 'deleted', 'message', 'ids', 'posted' ), $referer );
        }
    }
    switch ( $doaction ) {
        case 'delete':
            if ( ! isset( $post_ids ) ) {
                break;
            }
            foreach ( (array) $post_ids as $post_id_del ) {
                if ( ! current_user_can( 'delete_post', $post_id_del ) ) {
                    wp_die( __( 'You are not allowed to delete this post.', 'zl-custom-user-avatars' ) );
                }
                if ( ! wp_delete_attachment( $post_id_del ) ) {
                    wp_die( __( 'Error in deleting.', 'zl-custom-user-avatars' ) );
                }
            }
            $location = esc_url_raw( add_query_arg( 'deleted', count( $post_ids ), $location ) );
            break;
    }
    wp_redirect( $location );
    exit;
} elseif ( ! empty( $_GET['_wp_http_referer'] ) ) {
    wp_redirect( remove_query_arg( array ( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
    exit;
}
$wp_list_table->prepare_items();
wp_enqueue_script( 'wp-ajax-response' );
wp_enqueue_script( 'jquery-ui-draggable' );
wp_enqueue_script( 'media' );
?>
<div class="wrap">
    <h2>
        <?php
        _e( 'Avatars', 'zl-custom-user-avatars' );
        if ( ! empty( $_REQUEST['s'] ) ) {
            printf( '<span class="subtitle">' . esc_html__( 'Search results for &#8220;%s&#8221;', 'zl-custom-user-avatars' ) . '</span>', get_search_query() );
        }
        ?>
    </h2>
    <?php
    $message = "";
    if ( ! empty( $_GET['deleted'] ) && $deleted = absint( $_GET['deleted'] ) ) {
        $message                = sprintf( _n( 'Media attachment permanently deleted.', '%d media attachments permanently deleted.', $deleted ), number_format_i18n( filter_input( INPUT_GET, 'deleted', FILTER_VALIDATE_INT ) ) );
        $_SERVER['REQUEST_URI'] = remove_query_arg( array ( 'deleted' ), $_SERVER['REQUEST_URI'] );
    }
    if ( ! empty( $message ) ) : ?>
        <div id="message" class="updated"><p><?php
                echo $message; ?></p></div>
    <?php
    endif; ?>
    <?php
    $wp_list_table->views(); ?>
    <form id="posts-filter" action="" method="get">
        <?php
        $wp_list_table->search_box( esc_html__( 'Search', 'zl-custom-user-avatars' ), 'media' ); ?>
        <?php
        $wp_list_table->display(); ?>
        <div id="ajax-response"></div>
        <?php
        find_posts_div(); ?>
        <br class="clear"/>
    </form>
</div>
