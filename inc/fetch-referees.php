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
        // Get first and last name
        $first_name = get_user_meta($user->ID, 'first_name', true);
        $last_name = get_user_meta($user->ID, 'last_name', true);
        
        // Use first + last name if both are available, otherwise fallback to display name or username
        if (!empty($first_name) && !empty($last_name)) {
            $name = trim($first_name . ' ' . $last_name);
        } else {
            $name = $user->display_name ? $user->display_name : $user->user_login;
        }
        
        $data[] = array(
            'id' => $user->ID,
            'name' => $name
        );
    }
    wp_send_json_success( $data );
}
