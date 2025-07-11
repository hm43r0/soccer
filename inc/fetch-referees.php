<?php
// AJAX handler to fetch all users with the 'referee' role
add_action('wp_ajax_your_plugin_fetch_referees', 'your_plugin_fetch_referees_handler');
add_action('wp_ajax_nopriv_your_plugin_fetch_referees', 'your_plugin_fetch_referees_handler');

function your_plugin_fetch_referees_handler() {
    // Security check (optional, but recommended)
    if ( ! check_ajax_referer( 'your_plugin_ajax_nonce', 'nonce', false ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'your-awesome-admin-dashboard' ) ) );
        return;
    }
    $referees = get_users( array( 'role' => 'referee' ) );
    $data = array();
    foreach ( $referees as $user ) {
        $data[] = array(
            'id' => $user->ID,
            'name' => $user->display_name ? $user->display_name : $user->user_login
        );
    }
    wp_send_json_success( $data );
}
