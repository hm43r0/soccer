<?php
// Security helper for admin-only actions
function your_plugin_require_admin_and_nonce() {
    if ( ! check_ajax_referer( 'your_plugin_ajax_nonce', 'nonce', false ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'your-awesome-admin-dashboard' ) ) );
        return false;
    }
    if ( ! current_user_can( 'administrator' ) ) {
        wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'your-awesome-admin-dashboard' ) ) );
        return false;
    }
    return true;
}

// Lightweight list for select dropdowns (kept for backward compatibility)
add_action('wp_ajax_your_plugin_fetch_referees', 'your_plugin_fetch_referees_handler');
add_action('wp_ajax_nopriv_your_plugin_fetch_referees', 'your_plugin_fetch_referees_handler');
function your_plugin_fetch_referees_handler() {
    if ( ! check_ajax_referer( 'your_plugin_ajax_nonce', 'nonce', false ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'your-awesome-admin-dashboard' ) ) );
        return;
    }
    $referees = get_users( array( 'role' => 'referee' ) );
    $data = array();
    foreach ( $referees as $user ) {
        $first_name = get_user_meta($user->ID, 'first_name', true);
        $last_name = get_user_meta($user->ID, 'last_name', true);
        $name = (!empty($first_name) && !empty($last_name))
            ? trim($first_name . ' ' . $last_name)
            : ($user->display_name ? $user->display_name : $user->user_login);
        $data[] = array(
            'id' => $user->ID,
            'name' => $name,
        );
    }
    wp_send_json_success( $data );
}

// Full referee list for admin management (includes email, username, etc.)
add_action('wp_ajax_your_plugin_fetch_referees_full', 'your_plugin_fetch_referees_full_handler');
function your_plugin_fetch_referees_full_handler() {
    if ( ! your_plugin_require_admin_and_nonce() ) return;

    $referees = get_users( array( 'role' => 'referee' ) );
    $data = array();
    foreach ( $referees as $user ) {
        $first_name = get_user_meta($user->ID, 'first_name', true);
        $last_name = get_user_meta($user->ID, 'last_name', true);
        $name = (!empty($first_name) && !empty($last_name))
            ? trim($first_name . ' ' . $last_name)
            : ($user->display_name ? $user->display_name : $user->user_login);
        $data[] = array(
            'id' => $user->ID,
            'user_login' => $user->user_login,
            'user_email' => $user->user_email,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'name'       => $name,
        );
    }
    wp_send_json_success( $data );
}

// Referee stats for admin dashboard cards
add_action('wp_ajax_your_plugin_fetch_referee_stats', 'your_plugin_fetch_referee_stats_handler');
function your_plugin_fetch_referee_stats_handler() {
    if ( ! your_plugin_require_admin_and_nonce() ) return;

    // Total referees
    $counts = count_users();
    $total_referees = 0;
    if ( isset($counts['avail_roles']) && isset($counts['avail_roles']['referee']) ) {
        $total_referees = intval($counts['avail_roles']['referee']);
    } else {
        $total_referees = count( get_users( array( 'role' => 'referee', 'fields' => 'ID' ) ) );
    }

    // Matches with a referee assigned
    $matches_total_q = new WP_Query(array(
        'post_type'      => defined('YOUR_MATCH_CPT_SLUG') ? YOUR_MATCH_CPT_SLUG : 'match_management',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'meta_query'     => array(
            array(
                'key'     => '_referee_id',
                'compare' => 'EXISTS',
            )
        ),
        'no_found_rows'  => true,
    ));
    $matches_total = intval($matches_total_q->post_count);

    // Completed matches (is_completed = '1')
    $completed_q = new WP_Query(array(
        'post_type'      => defined('YOUR_MATCH_CPT_SLUG') ? YOUR_MATCH_CPT_SLUG : 'match_management',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'meta_query'     => array(
            array('key' => '_referee_id', 'compare' => 'EXISTS'),
            array('key' => 'is_completed', 'value' => '1', 'compare' => '=')
        ),
        'no_found_rows'  => true,
    ));
    $completed_matches = intval($completed_q->post_count);

    // Upcoming matches (date >= today and not completed)
    $today = date('Y-m-d');
    $upcoming_q = new WP_Query(array(
        'post_type'      => defined('YOUR_MATCH_CPT_SLUG') ? YOUR_MATCH_CPT_SLUG : 'match_management',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'meta_query'     => array(
            'relation' => 'AND',
            array('key' => '_referee_id', 'compare' => 'EXISTS'),
            array('key' => 'is_completed', 'value' => '1', 'compare' => '!='),
            array('key' => '_match_date', 'value' => $today, 'compare' => '>=', 'type' => 'CHAR'),
        ),
        'no_found_rows'  => true,
    ));
    $upcoming_matches = intval($upcoming_q->post_count);

    wp_send_json_success(array(
        'total_referees'     => $total_referees,
        'matches_assigned'   => $matches_total,
        'upcoming_matches'   => $upcoming_matches,
        'completed_matches'  => $completed_matches,
    ));
}

// Create a referee user
add_action('wp_ajax_your_plugin_create_referee', 'your_plugin_create_referee_handler');
add_action('wp_ajax_nopriv_your_plugin_create_referee', 'your_plugin_unauthorized_access_handler');
function your_plugin_create_referee_handler() {
    if ( ! your_plugin_require_admin_and_nonce() ) return;

    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name  = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
    $user_email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : '';
    $user_login = isset($_POST['user_login']) ? sanitize_user($_POST['user_login']) : '';
    $user_pass  = isset($_POST['user_pass']) ? sanitize_text_field($_POST['user_pass']) : '';

    if ( empty($user_email) ) {
        wp_send_json_error(array('message' => __('Email is required.', 'your-awesome-admin-dashboard')));
        return;
    }
    if ( email_exists($user_email) ) {
        wp_send_json_error(array('message' => __('Email already in use.', 'your-awesome-admin-dashboard')));
        return;
    }
    if ( empty($user_login) ) {
        // Generate from first/last or email prefix
        $base = $first_name || $last_name ? sanitize_user(strtolower($first_name . '.' . $last_name)) : sanitize_user(current(explode('@', $user_email)));
        if ( empty($base) ) { $base = 'referee'; }
        $candidate = $base;
        $i = 1;
        while ( username_exists($candidate) ) {
            $candidate = $base . $i;
            $i++;
        }
        $user_login = $candidate;
    } else {
        if ( username_exists($user_login) ) {
            wp_send_json_error(array('message' => __('Username already exists.', 'your-awesome-admin-dashboard')));
            return;
        }
    }
    if ( empty($user_pass) ) {
        $user_pass = wp_generate_password(12, true);
    }

    $user_id = wp_insert_user(array(
        'user_login' => $user_login,
        'user_email' => $user_email,
        'user_pass'  => $user_pass,
        'first_name' => $first_name,
        'last_name'  => $last_name,
        'role'       => 'referee',
    ));

    if ( is_wp_error($user_id) ) {
        wp_send_json_error(array('message' => $user_id->get_error_message()));
        return;
    }

    $user = get_userdata($user_id);
    wp_send_json_success(array(
        'id' => $user_id,
        'user_login' => $user->user_login,
        'user_email' => $user->user_email,
        'first_name' => get_user_meta($user_id, 'first_name', true),
        'last_name'  => get_user_meta($user_id, 'last_name', true),
    ));
}

// Update a referee user
add_action('wp_ajax_your_plugin_update_referee', 'your_plugin_update_referee_handler');
add_action('wp_ajax_nopriv_your_plugin_update_referee', 'your_plugin_unauthorized_access_handler');
function your_plugin_update_referee_handler() {
    if ( ! your_plugin_require_admin_and_nonce() ) return;

    $user_id    = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name  = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
    $user_email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : '';
    $user_pass  = isset($_POST['user_pass']) ? sanitize_text_field($_POST['user_pass']) : '';

    if ( ! $user_id ) {
        wp_send_json_error(array('message' => __('Invalid user ID.', 'your-awesome-admin-dashboard')));
        return;
    }

    $userdata = array(
        'ID'         => $user_id,
        'first_name' => $first_name,
        'last_name'  => $last_name,
    );
    if ( ! empty($user_email) ) {
        $existing = email_exists($user_email);
        if ( $existing && intval($existing) !== $user_id ) {
            wp_send_json_error(array('message' => __('Email already in use by another user.', 'your-awesome-admin-dashboard')));
            return;
        }
        $userdata['user_email'] = $user_email;
    }
    if ( ! empty($user_pass) ) {
        $userdata['user_pass'] = $user_pass;
    }

    $result = wp_update_user($userdata);
    if ( is_wp_error($result) ) {
        wp_send_json_error(array('message' => $result->get_error_message()));
        return;
    }

    // Ensure the role remains referee
    $user = get_userdata($user_id);
    if ( $user && ! in_array('referee', (array) $user->roles, true) ) {
        $user->set_role('referee');
    }

    wp_send_json_success(array('message' => __('Referee updated.', 'your-awesome-admin-dashboard')));
}

// Delete a referee user
add_action('wp_ajax_your_plugin_delete_referee', 'your_plugin_delete_referee_handler');
add_action('wp_ajax_nopriv_your_plugin_delete_referee', 'your_plugin_unauthorized_access_handler');
function your_plugin_delete_referee_handler() {
    if ( ! your_plugin_require_admin_and_nonce() ) return;

    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    if ( ! $user_id ) {
        wp_send_json_error(array('message' => __('Invalid user ID.', 'your-awesome-admin-dashboard')));
        return;
    }
    if ( get_current_user_id() === $user_id ) {
        wp_send_json_error(array('message' => __('You cannot delete your own account.', 'your-awesome-admin-dashboard')));
        return;
    }
    require_once ABSPATH . 'wp-admin/includes/user.php';
    $deleted = wp_delete_user($user_id);
    if ( ! $deleted ) {
        wp_send_json_error(array('message' => __('Failed to delete user.', 'your-awesome-admin-dashboard')));
        return;
    }
    wp_send_json_success(array('message' => __('Referee deleted.', 'your-awesome-admin-dashboard')));
}

function your_plugin_unauthorized_access_handler() {
    wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'your-awesome-admin-dashboard' ) ) );
}
