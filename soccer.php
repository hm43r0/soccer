<?php

/**
 * Plugin Name: Soccer Management Dashboard
 * Description: Displays a team and match management dashboard, saving data to a Custom Post Type.
 * Version: 1.0.0.0
 * Author: Hammad Mustafa
 */

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}

// --- 1. Define Constants ---
define('YOUR_PLUGIN_SLUG', 'soccer-management-dashboard');
define('YOUR_TEAM_CPT_SLUG', 'team_management'); // Slug for the Custom Post Type
define('YOUR_MATCH_CPT_SLUG', 'match_management'); // Slug for the Match Custom Post Type

// --- 2. Enqueue Scripts and Styles ---
function your_plugin_enqueue_assets()
{
    // Enqueue the JavaScript file for the dashboard
    wp_enqueue_script(
        'your-plugin-admin-dashboard', // Handle for the script
        plugin_dir_url(__FILE__) . 'js/admin-dashboard.js', // Path to the JS file
        array('jquery'), // Dependencies (jQuery is required)
        '1.0', // Version number
        true // Load script in the footer
    );

    // Localize the script to pass necessary data (AJAX URL, nonce) to JavaScript
    wp_localize_script('your-plugin-admin-dashboard', 'your_plugin_ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'), // WordPress AJAX endpoint
        'nonce'    => wp_create_nonce('your_plugin_ajax_nonce'), // Security nonce for AJAX requests
    ));

    // Enqueue your custom stylesheet with proper scoping
    your_plugin_enqueue_scoped_styles();
}

// New function to handle scoped styling
function your_plugin_enqueue_scoped_styles()
{
    // Only enqueue on pages with our shortcodes
    global $post;
    if (
        ! is_a($post, 'WP_Post') ||
        (
            ! has_shortcode($post->post_content, 'admin_dashboard_widget') &&
            ! has_shortcode($post->post_content, 'start_match_shortcode')
        )
    ) {
        return;
    }

    // Enqueue the main plugin stylesheet
    wp_enqueue_style(
        'your-plugin-style',
        plugin_dir_url(__FILE__) . 'css/style.css',
        array(),
        '1.0'
    );

    // Add inline styles for proper scoping and theme isolation
    $scoped_styles = your_plugin_get_scoped_styles();
    wp_add_inline_style('your-plugin-style', $scoped_styles);
}

// Function to generate scoped styles that won't interfere with theme
function your_plugin_get_scoped_styles()
{
    return "
    /* Soccer Plugin Scoped Styles - Isolated from theme */
    
    /* Reset and isolate plugin elements */
    .soccer-plugin-container,
    .soccer-plugin-container * {
        box-sizing: border-box;
    }
    
    /* Force plugin background and prevent theme interference */
    body.your-plugin-dashboard-active {
        background: linear-gradient(to bottom right, #1e293b, #4c1d95, #1e293b) !important;
        background-size: 200% 200% !important;
        background-attachment: fixed !important;
        color: #e2e8f0 !important;
        min-height: 100vh !important;
    }
    
    /* Isolate plugin content from theme styles */
    .soccer-plugin-container {
        position: relative;
        z-index: 1000;
        background: transparent !important;
        color: inherit !important;
    }
    
    /* Override theme styles for plugin elements */
    .soccer-plugin-container .glassmorphism {
        background: rgba(255, 255, 255, 0.05) !important;
        backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }
    
    .soccer-plugin-container .card-hover {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    
    .soccer-plugin-container .card-hover:hover {
        transform: translateY(-4px) !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
    }
    
    /* Ensure plugin forms and inputs are properly styled */
    .soccer-plugin-container input,
    .soccer-plugin-container select,
    .soccer-plugin-container textarea,
    .soccer-plugin-container button {
        font-family: 'Inter', sans-serif !important;
        color: #e2e8f0 !important;
    }
    
    .soccer-plugin-container input[type='text'],
    .soccer-plugin-container input[type='email'],
    .soccer-plugin-container input[type='password'],
    .soccer-plugin-container input[type='number'],
    .soccer-plugin-container input[type='date'],
    .soccer-plugin-container select,
    .soccer-plugin-container textarea {
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        color: #e2e8f0 !important;
        border-radius: 8px !important;
        padding: 12px 16px !important;
        font-size: 14px !important;
        transition: all 0.3s ease !important;
    }
    
    .soccer-plugin-container input:focus,
    .soccer-plugin-container select:focus,
    .soccer-plugin-container textarea:focus {
        outline: none !important;
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
    }
    
    /* Button styles */
    .soccer-plugin-container .btn,
    .soccer-plugin-container button {
        background: linear-gradient(135deg, #6366f1, #8b5cf6) !important;
        color: white !important;
        border: none !important;
        border-radius: 8px !important;
        padding: 12px 24px !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        transition: all 0.3s ease !important;
        text-decoration: none !important;
        display: inline-block !important;
    }
    
    .soccer-plugin-container .btn:hover,
    .soccer-plugin-container button:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3) !important;
    }
    
    /* Table styles */
    .soccer-plugin-container table {
        width: 100% !important;
        border-collapse: collapse !important;
        background: rgba(255, 255, 255, 0.05) !important;
        border-radius: 12px !important;
        overflow: hidden !important;
    }
    
    .soccer-plugin-container th,
    .soccer-plugin-container td {
        padding: 16px !important;
        text-align: left !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: #e2e8f0 !important;
    }
    
    .soccer-plugin-container th {
        background: rgba(255, 255, 255, 0.1) !important;
        font-weight: 600 !important;
        color: #f1f5f9 !important;
    }
    
    /* Custom checkbox styles */
    .soccer-plugin-container .custom-checkbox {
        display: inline-block !important;
        position: relative !important;
        width: 18px !important;
        height: 18px !important;
    }
    
    .soccer-plugin-container .custom-checkbox input[type='checkbox'] {
        opacity: 0 !important;
        width: 18px !important;
        height: 18px !important;
        margin: 0 !important;
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        z-index: 2 !important;
        cursor: pointer !important;
    }
    
    .soccer-plugin-container .custom-checkbox span {
        display: block !important;
        width: 18px !important;
        height: 18px !important;
        border-radius: 4px !important;
        border: 2px solid #22c55e !important;
        background: transparent !important;
        box-sizing: border-box !important;
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        z-index: 1 !important;
        pointer-events: none !important;
    }
    
    .soccer-plugin-container .custom-checkbox input[type='checkbox']:checked + span {
        background: #22c55e !important;
        border-color: #22c55e !important;
    }
    
    .soccer-plugin-container .custom-checkbox input[type='checkbox']:checked + span::after {
        content: '' !important;
        position: absolute !important;
        left: 4px !important;
        top: 0px !important;
        width: 5px !important;
        height: 10px !important;
        border: solid #fff !important;
        border-width: 0 2px 2px 0 !important;
        transform: rotate(45deg) !important;
        pointer-events: none !important;
    }
    
    /* Blue card checkbox */
    .soccer-plugin-container .blue-checkbox span {
        border-color: #3b82f6 !important;
    }
    
    .soccer-plugin-container .blue-checkbox input[type='checkbox']:checked + span {
        background: #3b82f6 !important;
        border-color: #3b82f6 !important;
    }
    
    /* Animation classes */
    .soccer-plugin-container .animate-float {
        animation: float 6s ease-in-out infinite !important;
    }
    
    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-20px);
        }
    }
    
    .soccer-plugin-container .pulse-ring {
        animation: pulse-ring 1.5s cubic-bezier(0.215, 0.61, 0.355, 1) infinite !important;
    }
    
    @keyframes pulse-ring {
        0% {
            transform: scale(0.8);
        }
        40%, 50% {
            opacity: 0;
        }
        100% {
            transform: scale(1.2);
            opacity: 0;
        }
    }
    
    /* Sidebar animations */
    .soccer-plugin-container .sidebar-item {
        transition: all 0.3s ease !important;
        position: relative !important;
        overflow: hidden !important;
    }
    
    .soccer-plugin-container .sidebar-item::before {
        content: '' !important;
        position: absolute !important;
        top: 0 !important;
        left: -100% !important;
        width: 100% !important;
        height: 100% !important;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent) !important;
        transition: left 0.5s !important;
    }
    
    .soccer-plugin-container .sidebar-item:hover::before {
        left: 100% !important;
    }
    
    /* Ensure proper font loading */
    .soccer-plugin-container {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .soccer-plugin-container .glassmorphism {
            margin: 8px !important;
            padding: 16px !important;
        }
        
        .soccer-plugin-container input,
        .soccer-plugin-container select,
        .soccer-plugin-container textarea {
            font-size: 16px !important; /* Prevents zoom on iOS */
        }
    }
    ";
}
// Hook into the 'wp_enqueue_scripts' action for front-end use
add_action('wp_enqueue_scripts', 'your_plugin_enqueue_assets');
// Hook into 'admin_enqueue_scripts' if you also want these assets loaded in the WordPress admin area
add_action('admin_enqueue_scripts', 'your_plugin_enqueue_assets');
function your_plugin_enqueue_tailwind_play_cdn()
{
    // Check if the current page should load the dashboard (e.g., if shortcode is present)
    // You might want to remove this conditional if you truly want it on all pages,
    // but for a dashboard, it's usually specific.
    global $post;
    if (
        ! is_a($post, 'WP_Post') ||
        (
            ! has_shortcode($post->post_content, 'admin_dashboard_widget') &&
            ! has_shortcode($post->post_content, 'start_match_shortcode')
        )
    ) {
        return;
    }

    wp_enqueue_script(
        'tailwind-play-cdn', // Handle for the script
        'https://cdn.tailwindcss.com', // Tailwind CSS Play CDN URL
        array(), // No dependencies
        null, // Version (null for latest dynamic CDN)
        true // Load script in the footer (recommended for Play CDN)
    );
}
add_action('wp_enqueue_scripts', 'your_plugin_enqueue_tailwind_play_cdn');
// --- 3. Register Custom Post Type: Team Management ---
function your_plugin_register_team_cpt()
{
    $labels = array(
        'name'                  => _x('Teams', 'Post type general name', YOUR_PLUGIN_SLUG),
        'singular_name'         => _x('Team', 'Post type singular name', YOUR_PLUGIN_SLUG),
        'menu_name'             => _x('Teams', 'Admin Menu text', YOUR_PLUGIN_SLUG),
        'name_admin_bar'        => _x('Team', 'Add New on Toolbar', YOUR_PLUGIN_SLUG),
        'add_new'               => __('Add New', YOUR_PLUGIN_SLUG),
        'add_new_item'          => __('Add New Team', YOUR_PLUGIN_SLUG),
        'edit_item'             => __('Edit Team', YOUR_PLUGIN_SLUG),
        'view_item'             => __('View Team', YOUR_PLUGIN_SLUG),
        'all_items'             => __('All Teams', YOUR_PLUGIN_SLUG),
        'search_items'          => __('Search Teams', YOUR_PLUGIN_SLUG),
        'parent_item_colon'     => __('Parent Teams:', YOUR_PLUGIN_SLUG),
        'not_found'             => __('No teams found.', YOUR_PLUGIN_SLUG),
        'not_found_in_trash'    => __('No teams found in Trash.', YOUR_PLUGIN_SLUG),
        'archives'              => _x('Team archives', 'The post type archive label used in menus', YOUR_PLUGIN_SLUG),
        'insert_into_item'      => _x('Insert into team', 'Overrides the "Insert into post"/”Insert into page” phrase', YOUR_PLUGIN_SLUG),
        'uploaded_to_this_item' => _x('Uploaded to this team', 'Overrides the "Uploaded to this post" / "Uploaded to this page" phrase', YOUR_PLUGIN_SLUG),
        'filter_items_list'     => _x('Filter teams list', 'Screen reader text for the filter links heading', YOUR_PLUGIN_SLUG),
        'items_list_navigation' => _x('Teams list navigation', 'Screen reader text for the list of items', YOUR_PLUGIN_SLUG),
        'items_list'            => _x('Teams list', 'Screen reader text for the items list', YOUR_PLUGIN_SLUG),
    );
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => YOUR_TEAM_CPT_SLUG), // URL slug for the CPT
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5, // Position in admin menu
        'menu_icon'          => 'dashicons-groups', // Menu icon
        'supports'           => array('title'), // Only supports title (for team name)
        'show_in_rest'       => true, // Enable for REST API support
    );
    register_post_type(YOUR_TEAM_CPT_SLUG, $args);
}
add_action('init', 'your_plugin_register_team_cpt');


// --- Register Custom Post Type: Match Management ---
function your_plugin_register_match_cpt()
{
    $labels = array(
        'name'                  => _x('Matches', 'Post type general name', YOUR_PLUGIN_SLUG),
        'singular_name'         => _x('Match', 'Post type singular name', YOUR_PLUGIN_SLUG),
        'menu_name'             => _x('Matches', 'Admin Menu text', YOUR_PLUGIN_SLUG),
        'name_admin_bar'        => _x('Match', 'Add New on Toolbar', YOUR_PLUGIN_SLUG),
        'add_new'               => __('Add New', YOUR_PLUGIN_SLUG),
        'add_new_item'          => __('Add New Match', YOUR_PLUGIN_SLUG),
        'edit_item'             => __('Edit Match', YOUR_PLUGIN_SLUG),
        'view_item'             => __('View Match', YOUR_PLUGIN_SLUG),
        'all_items'             => __('All Matches', YOUR_PLUGIN_SLUG),
        'search_items'          => __('Search Matches', YOUR_PLUGIN_SLUG),
        'parent_item_colon'     => __('Parent Matches:', YOUR_PLUGIN_SLUG),
        'not_found'             => __('No matches found.', YOUR_PLUGIN_SLUG),
        'not_found_in_trash'    => __('No matches found in Trash.', YOUR_PLUGIN_SLUG),
        'archives'              => _x('Match archives', 'The post type archive label used in menus', YOUR_PLUGIN_SLUG),
        'insert_into_item'      => _x('Insert into match', 'Overrides the "Insert into post"/”Insert into page” phrase', YOUR_PLUGIN_SLUG),
        'uploaded_to_this_item' => _x('Uploaded to this match', 'Overrides the "Uploaded to this post" / "Uploaded to this page" phrase', YOUR_PLUGIN_SLUG),
        'filter_items_list'     => _x('Filter matches list', 'Screen reader text for the filter links heading', YOUR_PLUGIN_SLUG),
        'items_list_navigation' => _x('Matches list navigation', 'Screen reader text for the list of items', YOUR_PLUGIN_SLUG),
        'items_list'            => _x('Matches list', 'Screen reader text for the items list', YOUR_PLUGIN_SLUG),
    );
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => YOUR_MATCH_CPT_SLUG),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 6, // Position in admin menu
        'menu_icon'          => 'dashicons-calendar-alt', // Menu icon
        'supports'           => array('title'), // Only supports title (for match name)
        'show_in_rest'       => true, // Enable for REST API support
    );
    register_post_type(YOUR_MATCH_CPT_SLUG, $args);
}
add_action('init', 'your_plugin_register_match_cpt');


// --- 4. AJAX Handler for Front-end Team Creation ---
// Handles requests from logged-in users
add_action('wp_ajax_your_plugin_create_team', 'your_plugin_handle_create_team');
// Handles requests from non-logged-in users
add_action('wp_ajax_nopriv_your_plugin_create_team', 'your_plugin_handle_create_team');

function your_plugin_handle_create_team()
{
    // --- Security Check: Nonce Verification ---
    // This is critical. If the nonce fails, no further processing should occur.
    // The 'false' argument prevents wp_die() and allows us to send a JSON error response.
    if (! check_ajax_referer('your_plugin_ajax_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => 'Security check failed.'));
        return;
    }

    // --- Handle Jersey Image Upload ---
    if (!isset($_FILES['team_jersey_image']) || $_FILES['team_jersey_image']['error'] !== UPLOAD_ERR_OK) {
        $error_detail = '';
        if (!isset($_FILES['team_jersey_image'])) {
            $error_detail = 'File not received by PHP. $_FILES is: ' . print_r($_FILES, true);
        } else {
            $error_code = $_FILES['team_jersey_image']['error'];
            $error_detail = 'Upload error code: ' . $error_code . '. See https://www.php.net/manual/en/features.file-upload.errors.php';
            $error_detail .= ' | $_FILES: ' . print_r($_FILES['team_jersey_image'], true);
        }
        wp_send_json_error(array('message' => 'Team jersey image is required. Debug: ' . $error_detail));
        return;
    }

    // WordPress environment setup for file uploads
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    $uploadedfile = $_FILES['team_jersey_image'];
    $upload_overrides = array('test_form' => false);

    // Handle the upload
    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
    $attach_id = 0;

    if ($movefile && !isset($movefile['error'])) {
        // File is uploaded successfully.
        $filename = basename($movefile['url']);
        $attachment = array(
            'guid'           => $movefile['url'],
            'post_mime_type' => $movefile['type'],
            'post_title'     => preg_replace('/\.[^.]+$/', '', $filename),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        // Insert the attachment.
        $attach_id = wp_insert_attachment($attachment, $movefile['file']);

        // Generate attachment metadata
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }
        $attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
        wp_update_attachment_metadata($attach_id, $attach_data);
    } else {
        // File upload failed.
        wp_send_json_error(array('message' => 'Error uploading jersey image: ' . $movefile['error']));
        return;
    }

    // --- Data Retrieval and Sanitization ---
    // Get team name and sanitize it
    $team_name = isset($_POST['team_name']) ? sanitize_text_field($_POST['team_name']) : '';

    // Get classification fields
    $team_location = isset($_POST['team_location']) ? sanitize_text_field($_POST['team_location']) : '';
    $team_day = isset($_POST['team_day']) ? sanitize_text_field($_POST['team_day']) : '';
    $team_league = isset($_POST['team_league']) ? sanitize_text_field($_POST['team_league']) : '';
    $team_division = isset($_POST['team_division']) ? sanitize_text_field($_POST['team_division']) : '';
    $team_season = isset($_POST['team_season']) ? sanitize_text_field($_POST['team_season']) : '';
    $team_year = isset($_POST['team_year']) ? sanitize_text_field($_POST['team_year']) : '';

    // Get players data as a string. It should be a JSON string from the JS.
    $players_input_string = isset($_POST['players']) ? $_POST['players'] : '[]';

    // Debug: Log the raw players input to wp-content/debug.log
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('RAW $_POST["players"]: ' . print_r($players_input_string, true));
    }

    // --- Player Data Validation ---
    // 1. Ensure it's a string before attempting to JSON decoding.
    if (! is_string($players_input_string)) {
        // error_log( "players POST data is NOT a string." ); // For debugging
        wp_send_json_error(array('message' => __('Invalid player data format: Expected a string.', YOUR_PLUGIN_SLUG)));
        return;
    }

    // 2. Attempt to decode the JSON string.
    $players_data = json_decode($players_input_string, true);

    // If decoding failed, try decoding again (handle double-encoded JSON)
    if (json_last_error() !== JSON_ERROR_NONE) {
        $players_input_string_decoded = stripslashes($players_input_string);
        $players_data = json_decode($players_input_string_decoded, true);
    }

    // 3. Check for JSON decoding errors.
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Log the error to the frontend via JS (inject a script tag in the response)
        $error_message = sprintf('Invalid player data format. JSON decoding failed: %s', json_last_error_msg());
        $js_log = '<script>console.error(' . json_encode('Team creation error: ' . $error_message . ' | Raw: ' . $players_input_string) . ');</script>';
        wp_send_json_error(array('message' => __($error_message, YOUR_PLUGIN_SLUG) . $js_log));
        return;
    }

    // 4. Ensure the decoded data is an array.
    if (! is_array($players_data)) {
        wp_send_json_error(array('message' => __('Invalid player data format: Expected an array of players after decoding.', YOUR_PLUGIN_SLUG)));
        return;
    }

    // 5. Validate each player entry within the array.
    $valid_players = [];
    foreach ($players_data as $player) {
        // Ensure 'id' and 'name' are set, not empty after trimming whitespace.
        if (isset($player['id']) && isset($player['name']) && ! empty(trim($player['id'])) && ! empty(trim($player['name']))) {
            $valid_players[] = [
                'id'   => sanitize_text_field(trim($player['id'])),
                'name' => sanitize_text_field(trim($player['name'])),
            ];
        } else {
            // If any single player entry is invalid.
            wp_send_json_error(array('message' => __('Invalid player data found. Please ensure both Player ID and Player Name are filled and valid for all players.', YOUR_PLUGIN_SLUG)));
            return;
        }
    }

    // --- Team Name Validation ---
    if (empty($team_name)) {
        wp_send_json_error(array('message' => __('Team name is required.', YOUR_PLUGIN_SLUG)));
        return;
    }

    // --- Check for Duplicate Team Names ---
    $existing_teams = get_posts(array(
        'post_type'      => YOUR_TEAM_CPT_SLUG,
        'title'          => $team_name, // Search by title
        'numberposts'    => -1,       // Get all matching posts
        'post_status'    => 'publish',  // Only active teams
        'suppress_filters' => true      // Ignore filters that might interfere
    ));

    if (! empty($existing_teams)) {
        wp_send_json_error(array('message' => sprintf(__('A team named "%s" already exists.', YOUR_PLUGIN_SLUG), esc_html($team_name))));
        return;
    }

    // --- Create the Custom Post Type Entry ---
    $post_data = array(
        'post_title'    => $team_name,
        'post_status'   => 'publish',
        'post_type'     => YOUR_TEAM_CPT_SLUG,
        // No 'post_content' needed as we store players in meta
    );

    $post_id = wp_insert_post($post_data);

    // Check if post insertion was successful
    if (is_wp_error($post_id)) {
        if ($attach_id) {
            wp_delete_attachment($attach_id, true);
        }
        wp_send_json_error(array('message' => sprintf(__('Error creating team: %s', YOUR_PLUGIN_SLUG), $post_id->get_error_message())));
    } else {
        // --- Save Player Data and Jersey Image as Post Meta ---
        // Store the validated players array as a JSON string in post meta
        update_post_meta($post_id, 'team_players', json_encode($valid_players));
        update_post_meta($post_id, 'team_jersey_image_id', $attach_id);

        // Save classification fields as post meta
        update_post_meta($post_id, 'team_location', $team_location);
        update_post_meta($post_id, 'team_day', $team_day);
        update_post_meta($post_id, 'team_league', $team_league);
        update_post_meta($post_id, 'team_division', $team_division);
        update_post_meta($post_id, 'team_season', $team_season);
        update_post_meta($post_id, 'team_year', $team_year);


        // --- Send Success Response ---
        wp_send_json_success(array(
            'message' => __('Team created successfully!', YOUR_PLUGIN_SLUG),
            'team_id' => $post_id, // The ID of the newly created CPT post
            'team_name' => $team_name,
            'players' => $valid_players, // Send back the validated players for JS to potentially use
            'jersey_image_url' => wp_get_attachment_url($attach_id),
            'team_location' => $team_location,
            'team_day' => $team_day,
            'team_league' => $team_league,
            'team_division' => $team_division,
            'team_season' => $team_season,
            'team_year' => $team_year,
        ));
    }
}

// --- 5. Shortcode to Display the Dashboard ---
// This shortcode will render the HTML and ensure scripts are enqueued.
function your_plugin_display_dashboard_form($atts)
{
    // Only show to administrators
    if (! current_user_can('administrator')) {
        return '<p>Access denied. This dashboard is only available to administrators.</p>';
    }

    // Enqueue assets when the shortcode is processed.
    your_plugin_enqueue_assets();

    // Use output buffering to capture the content of the HTML template file.
    ob_start();
    echo '<div class="soccer-plugin-container">'; // Add scoped container
    $template_path = plugin_dir_path(__FILE__) . 'templates/admindashbaord.html';
    if (file_exists($template_path)) {
        // Include the template file. The HTML and inline JS will be rendered here.
        include $template_path;
    } else {
        // Fallback if the template file is missing.
        echo '<p>Error: Dashboard template not found at ' . esc_html($template_path) . '</p>';
    }
    echo '</div>'; // Close scoped container
    return ob_get_clean(); // Return the captured output.
}
// Register the shortcode so it can be used in WordPress content.
add_shortcode('admin_dashboard_widget', 'your_plugin_display_dashboard_form');

// --- Shortcode for Referee Match Start ---
// This shortcode will render a match start interface for referees only.
function your_plugin_display_referee_match_form($atts)
{
    // Only show to users with referee role
    if (! current_user_can('referee') && ! current_user_can('administrator')) {
        return '<p>Access denied. This interface is only available to referees.</p>';
    }

    // Enqueue assets when the shortcode is processed.
    your_plugin_enqueue_assets();

    // Use output buffering to capture the content of the HTML template file.
    ob_start();
    echo '<div class="soccer-plugin-container">'; // Add scoped container
    $template_path = plugin_dir_path(__FILE__) . 'templates/referee-match-control.html';
    if (file_exists($template_path)) {
        // Include the template file. The HTML and inline JS will be rendered here.
        include $template_path;
    } else {
        // Fallback if the template file is missing.
        echo '<p>Error: Referee dashboard template not found at ' . esc_html($template_path) . '</p>';
    }
    echo '</div>'; // Close scoped container
    return ob_get_clean(); // Return the captured output.
}

// Register the referee shortcode
add_shortcode('start_match_shortcode', 'your_plugin_display_referee_match_form');

// --- 6. Add Body Class for Styling Hooks ---
// This hook allows you to add specific CSS classes to the <body> tag,
// useful for targeting styles when the dashboard is active.
function your_plugin_add_body_class($classes)
{
    global $post;
    // Check if we are viewing a post/page that contains our shortcodes.
    if (is_a($post, 'WP_Post') && (
        has_shortcode($post->post_content, 'admin_dashboard_widget') ||
        has_shortcode($post->post_content, 'start_match_shortcode')
    )) {
        $classes[] = 'your-plugin-dashboard-active'; // Add this class to the body tag.
    }
    return $classes;
}
add_filter('body_class', 'your_plugin_add_body_class');

// --- 7. AJAX Handler for fetching teams (for match creation dropdowns) ---
add_action('wp_ajax_your_plugin_fetch_teams', 'your_plugin_fetch_teams_handler');
add_action('wp_ajax_nopriv_your_plugin_fetch_teams', 'your_plugin_fetch_teams_handler'); // If you need it for logged-out users as well

function your_plugin_fetch_teams_handler()
{
    // Security check (optional but recommended if you have sensitive data)
    if (! check_ajax_referer('your_plugin_ajax_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed.', YOUR_PLUGIN_SLUG)));
        return;
    }

    $teams_query = new WP_Query(array(
        'post_type'      => YOUR_TEAM_CPT_SLUG,
        'posts_per_page' => -1, // Get all teams
        'post_status'    => 'publish',
    ));

    $teams_data = array();
    if ($teams_query->have_posts()) {
        while ($teams_query->have_posts()) {
            $teams_query->the_post();
            $post_id = get_the_ID();
            $team_name = get_the_title();
            $players_json = get_post_meta($post_id, 'team_players', true);
            $players = json_decode($players_json, true);
            if (! is_array($players)) {
                $players = []; // Ensure it's always an array
            }
            $jersey_image_id = get_post_meta($post_id, 'team_jersey_image_id', true);
            $jersey_image_url = $jersey_image_id ? wp_get_attachment_url($jersey_image_id) : '';

            // Classification fields
            $team_location = get_post_meta($post_id, 'team_location', true);
            $team_day = get_post_meta($post_id, 'team_day', true);
            $team_league = get_post_meta($post_id, 'team_league', true);
            $team_division = get_post_meta($post_id, 'team_division', true);
            $team_season = get_post_meta($post_id, 'team_season', true);
            $team_year = get_post_meta($post_id, 'team_year', true);

            $teams_data[] = array(
                'id'      => $post_id,
                'name'    => $team_name,
                'members' => $players, // Include members in the data sent to JS
                'jersey_image_url' => $jersey_image_url,
                'team_location' => $team_location ? $team_location : '',
                'team_day' => $team_day ? $team_day : '',
                'team_league' => $team_league ? $team_league : '',
                'team_division' => $team_division ? $team_division : '',
                'team_season' => $team_season ? $team_season : '',
                'team_year' => $team_year ? $team_year : '',
            );
        }
        wp_reset_postdata();
    }

    wp_send_json_success($teams_data);
}

// --- AJAX Handler for Editing a Team ---
add_action('wp_ajax_your_plugin_update_team', 'your_plugin_handle_update_team');
add_action('wp_ajax_nopriv_your_plugin_update_team', 'your_plugin_handle_update_team');

function your_plugin_handle_update_team()
{
    if (! check_ajax_referer('your_plugin_ajax_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed. Please try again.', YOUR_PLUGIN_SLUG)));
        return;
    }
    $team_id = isset($_POST['team_id']) ? intval($_POST['team_id']) : 0;
    $team_name = isset($_POST['team_name']) ? sanitize_text_field($_POST['team_name']) : '';
    // Classification fields
    $team_location = isset($_POST['team_location']) ? sanitize_text_field($_POST['team_location']) : '';
    $team_day = isset($_POST['team_day']) ? sanitize_text_field($_POST['team_day']) : '';
    $team_league = isset($_POST['team_league']) ? sanitize_text_field($_POST['team_league']) : '';
    $team_division = isset($_POST['team_division']) ? sanitize_text_field($_POST['team_division']) : '';
    $team_season = isset($_POST['team_season']) ? sanitize_text_field($_POST['team_season']) : '';
    $team_year = isset($_POST['team_year']) ? sanitize_text_field($_POST['team_year']) : '';
    $players_input_string = isset($_POST['players']) ? $_POST['players'] : '[]';

    $attach_id = null;
    // --- Handle Jersey Image Upload (if provided) ---
    if (isset($_FILES['team_jersey_image']) && $_FILES['team_jersey_image']['error'] === UPLOAD_ERR_OK) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $uploadedfile = $_FILES['team_jersey_image'];
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            $filename = basename($movefile['url']);
            $attachment = array(
                'guid'           => $movefile['url'],
                'post_mime_type' => $movefile['type'],
                'post_title'     => preg_replace('/\.[^.]+$/', '', $filename),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );
            $attach_id = wp_insert_attachment($attachment, $movefile['file'], $team_id);

            if (!function_exists('wp_generate_attachment_metadata')) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
            }
            $attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
            wp_update_attachment_metadata($attach_id, $attach_data);

            // Delete old attachment if it exists
            $old_attach_id = get_post_meta($team_id, 'team_jersey_image_id', true);
            if ($old_attach_id && $old_attach_id != $attach_id) {
                wp_delete_attachment($old_attach_id, true);
            }
        } else {
            wp_send_json_error(array('message' => 'Error uploading new jersey image: ' . $movefile['error']));
            return;
        }
    }


    $players_data = json_decode($players_input_string, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $players_input_string_decoded = stripslashes($players_input_string);
        $players_data = json_decode($players_input_string_decoded, true);
    }
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error(array('message' => 'Invalid player data format. JSON decoding failed: ' . json_last_error_msg()));
        return;
    }
    if (! is_array($players_data)) {
        wp_send_json_error(array('message' => 'Invalid player data format: Expected an array of players after decoding.'));
        return;
    }
    $valid_players = [];
    foreach ($players_data as $player) {
        if (isset($player['id']) && isset($player['name']) && ! empty(trim($player['id'])) && ! empty(trim($player['name']))) {
            $valid_players[] = [
                'id'   => sanitize_text_field(trim($player['id'])),
                'name' => sanitize_text_field(trim($player['name'])),
            ];
        } else {
            wp_send_json_error(array('message' => 'Invalid player data found. Please ensure both Player ID and Player Name are filled and valid for all players.'));
            return;
        }
    }
    if (empty($team_name)) {
        wp_send_json_error(array('message' => 'Team name is required.'));
        return;
    }
    $post_data = array(
        'ID'           => $team_id,
        'post_title'   => $team_name,
        'post_type'    => YOUR_TEAM_CPT_SLUG,
    );
    $result = wp_update_post($post_data, true);
    if (is_wp_error($result)) {
        if ($attach_id) {
            wp_delete_attachment($attach_id, true);
        }
        wp_send_json_error(array('message' => 'Error updating team: ' . $result->get_error_message()));
    } else {
        update_post_meta($team_id, 'team_players', json_encode($valid_players));
        if ($attach_id) {
            update_post_meta($team_id, 'team_jersey_image_id', $attach_id);
        }

        // Update classification fields
        update_post_meta($team_id, 'team_location', $team_location);
        update_post_meta($team_id, 'team_day', $team_day);
        update_post_meta($team_id, 'team_league', $team_league);
        update_post_meta($team_id, 'team_division', $team_division);
        update_post_meta($team_id, 'team_season', $team_season);
        update_post_meta($team_id, 'team_year', $team_year);

        $jersey_image_id = get_post_meta($team_id, 'team_jersey_image_id', true);
        $jersey_image_url = $jersey_image_id ? wp_get_attachment_url($jersey_image_id) : '';

        wp_send_json_success(array(
            'message' => 'Team updated successfully!',
            'team_id' => $team_id,
            'team_name' => $team_name,
            'players' => $valid_players,
            'jersey_image_url' => $jersey_image_url,
            'team_location' => $team_location,
            'team_day' => $team_day,
            'team_league' => $team_league,
            'team_division' => $team_division,
            'team_season' => $team_season,
            'team_year' => $team_year,
        ));
    }
}

// --- AJAX Handler for Deleting a Team ---
add_action('wp_ajax_your_plugin_delete_team', 'your_plugin_handle_delete_team');
add_action('wp_ajax_nopriv_your_plugin_delete_team', 'your_plugin_handle_delete_team');

function your_plugin_handle_delete_team()
{
    if (! check_ajax_referer('your_plugin_ajax_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed. Please try again.', YOUR_PLUGIN_SLUG)));
        return;
    }
    $team_id = isset($_POST['team_id']) ? intval($_POST['team_id']) : 0;
    if (! $team_id) {
        wp_send_json_error(array('message' => 'Invalid team ID.'));
        return;
    }
    $result = wp_delete_post($team_id, true);
    if ($result) {
        wp_send_json_success(array('message' => 'Team deleted successfully!'));
    } else {
        wp_send_json_error(array('message' => 'Error deleting team.'));
    }
}

// --- 8. Add Custom Role: Referee ---
function your_plugin_add_referee_role()
{
    add_role(
        'referee',
        __('Referee', 'your-awesome-admin-dashboard'),
        array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
        )
    );
}
register_activation_hook(__FILE__, 'your_plugin_add_referee_role');

// Optional: Remove the role on plugin deactivation
function your_plugin_remove_referee_role()
{
    remove_role('referee');
}
register_deactivation_hook(__FILE__, 'your_plugin_remove_referee_role');

// Include referee AJAX handler
require_once plugin_dir_path(__FILE__) . 'inc/fetch-referees.php';

// --- AJAX Handler for Fetching Referee Matches ---
add_action('wp_ajax_your_plugin_fetch_referee_matches', 'your_plugin_fetch_referee_matches_handler');
add_action('wp_ajax_nopriv_your_plugin_fetch_referee_matches', 'your_plugin_fetch_referee_matches_handler');

function your_plugin_fetch_referee_matches_handler()
{
    if (! check_ajax_referer('your_plugin_ajax_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed.', YOUR_PLUGIN_SLUG)));
        return;
    }

    $current_user_id = get_current_user_id();

    // Query matches where the current user is assigned as referee (both completed and incomplete)
    $matches_query = new WP_Query(array(
        'post_type'      => YOUR_MATCH_CPT_SLUG,
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => array(
            array(
                'key'     => '_referee_id',
                'value'   => $current_user_id,
                'compare' => '='
            )
        )
    ));

    $matches_data = array();
    if ($matches_query->have_posts()) {
        while ($matches_query->have_posts()) {
            $matches_query->the_post();
            $post_id = get_the_ID();
            $team1_id = get_post_meta($post_id, '_team1_id', true);
            $team2_id = get_post_meta($post_id, '_team2_id', true);
            $match_date = get_post_meta($post_id, '_match_date', true);
            $match_location = get_post_meta($post_id, '_match_location', true);
            $match_week = get_post_meta($post_id, '_match_week', true);
            $is_completed = get_post_meta($post_id, 'is_completed', true);

            // Get team names
            $team1_name = get_the_title($team1_id);
            $team2_name = get_the_title($team2_id);

            // Get team jersey images
            $team1_jersey_id = get_post_meta($team1_id, 'team_jersey_image_id', true);
            $team1_jersey_url = $team1_jersey_id ? wp_get_attachment_url($team1_jersey_id) : '';
            $team2_jersey_id = get_post_meta($team2_id, 'team_jersey_image_id', true);
            $team2_jersey_url = $team2_jersey_id ? wp_get_attachment_url($team2_jersey_id) : '';


            $matches_data[] = array(
                'id' => $post_id,
                'team1Id' => $team1_id,
                'team2Id' => $team2_id,
                'team1Name' => $team1_name ? $team1_name : 'Team ' . $team1_id,
                'team2Name' => $team2_name ? $team2_name : 'Team ' . $team2_id,
                'team1Jersey' => $team1_jersey_url,
                'team2Jersey' => $team2_jersey_url,
                'date' => $match_date,
                'location' => $match_location,
                'week' => $match_week,
                'is_completed' => $is_completed
            );
        }
        wp_reset_postdata();
    }

    wp_send_json_success($matches_data);
}

// --- AJAX Handler for Creating a Match ---
add_action('wp_ajax_your_plugin_create_match', 'your_plugin_handle_create_match');
add_action('wp_ajax_nopriv_your_plugin_create_match', 'your_plugin_handle_create_match');

function your_plugin_handle_create_match()
{
    if (! check_ajax_referer('your_plugin_ajax_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed. Please try again.', YOUR_PLUGIN_SLUG)));
        return;
    }
    $team1_id = isset($_POST['team1_id']) ? intval($_POST['team1_id']) : 0;
    $team2_id = isset($_POST['team2_id']) ? intval($_POST['team2_id']) : 0;
    $referee_id = isset($_POST['referee_id']) ? intval($_POST['referee_id']) : 0;
    $match_date = isset($_POST['match_date']) ? sanitize_text_field($_POST['match_date']) : '';
    $match_location = isset($_POST['match_location']) ? sanitize_text_field($_POST['match_location']) : '';
    $match_week = isset($_POST['match_week']) ? sanitize_text_field($_POST['match_week']) : '';

    if (! $team1_id || ! $team2_id || $team1_id === $team2_id) {
        wp_send_json_error(array('message' => 'Please select two different teams.'));
        return;
    }
    if (! $referee_id) {
        wp_send_json_error(array('message' => 'Please select a referee.'));
        return;
    }
    if (empty($match_date)) {
        wp_send_json_error(array('message' => 'Please enter a match date.'));
        return;
    }
    // Create the match post
    $post_data = array(
        'post_title'    => 'Match: ' . $team1_id . ' vs ' . $team2_id . ' (' . $match_date . ')',
        'post_status'   => 'publish',
        'post_type'     => YOUR_MATCH_CPT_SLUG,
    );
    $post_id = wp_insert_post($post_data);
    if (is_wp_error($post_id)) {
        wp_send_json_error(array('message' => 'Error creating match: ' . $post_id->get_error_message()));
    } else {
        update_post_meta($post_id, '_team1_id', $team1_id);
        update_post_meta($post_id, '_team2_id', $team2_id);
        update_post_meta($post_id, '_referee_id', $referee_id);
        update_post_meta($post_id, '_match_date', $match_date);
        update_post_meta($post_id, '_match_location', $match_location);
        update_post_meta($post_id, '_match_week', $match_week);
        update_post_meta($post_id, 'is_completed', '0'); // Set default value
        wp_send_json_success(array('message' => 'Match created successfully!', 'match_id' => $post_id));
    }
}

// --- AJAX Handler for Fetching Matches ---
add_action('wp_ajax_your_plugin_fetch_matches', 'your_plugin_fetch_matches_handler');
add_action('wp_ajax_nopriv_your_plugin_fetch_matches', 'your_plugin_fetch_matches_handler');

function your_plugin_fetch_matches_handler()
{
    if (! check_ajax_referer('your_plugin_ajax_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed.', YOUR_PLUGIN_SLUG)));
        return;
    }
    $matches_query = new WP_Query(array(
        'post_type'      => YOUR_MATCH_CPT_SLUG,
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ));
    $matches_data = array();
    if ($matches_query->have_posts()) {
        while ($matches_query->have_posts()) {
            $matches_query->the_post();
            $post_id = get_the_ID();
            $team1_id = get_post_meta($post_id, '_team1_id', true);
            $team2_id = get_post_meta($post_id, '_team2_id', true);
            $referee_id = get_post_meta($post_id, '_referee_id', true);
            $match_date = get_post_meta($post_id, '_match_date', true);
            $match_location = get_post_meta($post_id, '_match_location', true);
            $match_week = get_post_meta($post_id, '_match_week', true);
            $is_completed = get_post_meta($post_id, 'is_completed', true);
            $score_data = get_post_meta($post_id, 'score_data', true);
            $final_score = get_post_meta($post_id, 'final_score', true);
            $caution_desc = get_post_meta($post_id, 'caution_desc', true);
            $additional_notes = get_post_meta($post_id, 'additional_notes', true);
            $first_half_fouls = get_post_meta($post_id, 'first_half_fouls', true);
            $second_half_fouls = get_post_meta($post_id, 'second_half_fouls', true);
            
            // Get referee's first and last name
            $referee_name = '';
            if ($referee_id) {
                $referee_user = get_userdata($referee_id);
                if ($referee_user) {
                    $first_name = get_user_meta($referee_id, 'first_name', true);
                    $last_name = get_user_meta($referee_id, 'last_name', true);
                    if (!empty($first_name) && !empty($last_name)) {
                        $referee_name = trim($first_name . ' ' . $last_name);
                    } else {
                        // Fallback to display name or username if first/last name not available
                        $referee_name = $referee_user->display_name ? $referee_user->display_name : $referee_user->user_login;
                    }
                }
            }
            
            $matches_data[] = array(
                'id' => $post_id,
                'team1Id' => $team1_id,
                'team2Id' => $team2_id,
                'referee' => $referee_name,
                'refereeId' => $referee_id ? intval($referee_id) : 0,
                'date' => $match_date,
                'location' => $match_location,
                'week' => $match_week,
                'is_completed' => $is_completed,
                'score_data' => $score_data,
                'final_score' => $final_score,
                'caution_desc' => $caution_desc,
                'additional_notes' => $additional_notes,
                'first_half_fouls' => $first_half_fouls,
                'second_half_fouls' => $second_half_fouls
            );
        }
        wp_reset_postdata();
    }
    wp_send_json_success($matches_data);
}

// --- AJAX Handler for Editing a Match ---
add_action('wp_ajax_your_plugin_update_match', 'your_plugin_handle_update_match');
add_action('wp_ajax_nopriv_your_plugin_update_match', 'your_plugin_handle_update_match');

function your_plugin_handle_update_match()
{
    if (! check_ajax_referer('your_plugin_ajax_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed. Please try again.', YOUR_PLUGIN_SLUG)));
        return;
    }
    $match_id = isset($_POST['match_id']) ? intval($_POST['match_id']) : 0;
    $team1_id = isset($_POST['team1_id']) ? intval($_POST['team1_id']) : 0;
    $team2_id = isset($_POST['team2_id']) ? intval($_POST['team2_id']) : 0;
    $referee_id = isset($_POST['referee_id']) ? intval($_POST['referee_id']) : 0;
    $match_date = isset($_POST['match_date']) ? sanitize_text_field($_POST['match_date']) : '';
    $match_location = isset($_POST['match_location']) ? sanitize_text_field($_POST['match_location']) : '';
    $match_week = isset($_POST['match_week']) ? sanitize_text_field($_POST['match_week']) : '';

    if (! $match_id) {
        wp_send_json_error(array('message' => 'Invalid match ID.'));
        return;
    }
    if (! $team1_id || ! $team2_id || $team1_id === $team2_id) {
        wp_send_json_error(array('message' => 'Please select two different teams.'));
        return;
    }
    if (! $referee_id) {
        wp_send_json_error(array('message' => 'Please select a referee.'));
        return;
    }
    if (empty($match_date)) {
        wp_send_json_error(array('message' => 'Please enter a match date.'));
        return;
    }
    $post_data = array(
        'ID'           => $match_id,
        'post_title'   => 'Match: ' . $team1_id . ' vs ' . $team2_id . ' (' . $match_date . ')',
        'post_type'    => YOUR_MATCH_CPT_SLUG,
    );
    $result = wp_update_post($post_data, true);
    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => 'Error updating match: ' . $result->get_error_message()));
    } else {
        update_post_meta($match_id, '_team1_id', $team1_id);
        update_post_meta($match_id, '_team2_id', $team2_id);
        update_post_meta($match_id, '_referee_id', $referee_id);
        update_post_meta($match_id, '_match_date', $match_date);
        update_post_meta($match_id, '_match_location', $match_location);
        update_post_meta($match_id, '_match_week', $match_week);
        wp_send_json_success(array('message' => 'Match updated successfully!', 'match_id' => $match_id));
    }
}

// Add this to your main plugin PHP file
add_action('wp_ajax_save_soccer_match_summary', function () {
    $match_id = intval($_POST['match_id']);
    $final_score = sanitize_text_field($_POST['final_score']);
    $caution_desc = sanitize_textarea_field($_POST['caution_desc']);
    $additional_notes = sanitize_textarea_field($_POST['additional_notes']);
    $score_data = isset($_POST['score_data']) ? wp_unslash($_POST['score_data']) : '';
    $first_half_fouls = isset($_POST['first_half_fouls']) ? sanitize_text_field($_POST['first_half_fouls']) : '';
    $second_half_fouls = isset($_POST['second_half_fouls']) ? sanitize_text_field($_POST['second_half_fouls']) : '';

    // Save as post meta or update post content as needed
    update_post_meta($match_id, 'final_score', $final_score);
    update_post_meta($match_id, 'caution_desc', $caution_desc);
    update_post_meta($match_id, 'additional_notes', $additional_notes);
    update_post_meta($match_id, 'first_half_fouls', $first_half_fouls);
    update_post_meta($match_id, 'second_half_fouls', $second_half_fouls);
    if ($score_data) {
        update_post_meta($match_id, 'score_data', $score_data);
    }
    update_post_meta($match_id, 'is_completed', '1'); // Mark as completed

    // Save referee name as post meta (if not already saved)
    $referee_id = get_post_meta($match_id, '_referee_id', true);
    if ($referee_id) {
        $user = get_userdata($referee_id);
        if ($user) {
            update_post_meta($match_id, 'referee_name', $user->display_name);
        }
    }

    wp_send_json_success();
});

// AJAX: Get existing match summary (final score, fouls, notes, score_data)
add_action('wp_ajax_your_plugin_get_match_summary', 'your_plugin_get_match_summary_handler');
add_action('wp_ajax_nopriv_your_plugin_get_match_summary', 'your_plugin_get_match_summary_handler');
function your_plugin_get_match_summary_handler()
{
    if (! check_ajax_referer('your_plugin_ajax_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed.', YOUR_PLUGIN_SLUG)));
        return;
    }

    $match_id = isset($_POST['match_id']) ? intval($_POST['match_id']) : 0;
    if (! $match_id) {
        wp_send_json_error(array('message' => 'Invalid match ID.'));
        return;
    }

    $final_score = get_post_meta($match_id, 'final_score', true);
    $caution_desc = get_post_meta($match_id, 'caution_desc', true);
    $additional_notes = get_post_meta($match_id, 'additional_notes', true);
    $first_half_fouls = get_post_meta($match_id, 'first_half_fouls', true);
    $second_half_fouls = get_post_meta($match_id, 'second_half_fouls', true);
    $score_data = get_post_meta($match_id, 'score_data', true);

    wp_send_json_success(array(
        'final_score' => $final_score ? $final_score : '',
        'caution_desc' => $caution_desc ? $caution_desc : '',
        'additional_notes' => $additional_notes ? $additional_notes : '',
        'first_half_fouls' => $first_half_fouls ? $first_half_fouls : '',
        'second_half_fouls' => $second_half_fouls ? $second_half_fouls : '',
        'score_data' => $score_data ? $score_data : ''
    ));
}

// --- AJAX Handler for Deleting a Match ---
add_action('wp_ajax_your_plugin_delete_match', 'your_plugin_handle_delete_match');
add_action('wp_ajax_nopriv_your_plugin_delete_match', 'your_plugin_handle_delete_match');

function your_plugin_handle_delete_match()
{
    if (! check_ajax_referer('your_plugin_ajax_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed. Please try again.', YOUR_PLUGIN_SLUG)));
        return;
    }
    $match_id = isset($_POST['match_id']) ? intval($_POST['match_id']) : 0;
    if (! $match_id) {
        wp_send_json_error(array('message' => 'Invalid match ID.'));
        return;
    }
    $result = wp_delete_post($match_id, true);
    if ($result) {
        wp_send_json_success(array('message' => 'Match deleted successfully!'));
    } else {
        wp_send_json_error(array('message' => 'Error deleting match.'));
    }
}

// --- AJAX Handler for Fetching Team Members ---
add_action('wp_ajax_fetch_team_members', 'your_plugin_handle_fetch_team_members');
add_action('wp_ajax_nopriv_fetch_team_members', 'your_plugin_handle_fetch_team_members');

function your_plugin_handle_fetch_team_members()
{
    if (! check_ajax_referer('your_plugin_ajax_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed. Please try again.', YOUR_PLUGIN_SLUG)));
        return;
    }

    $team1_id = isset($_POST['team1_id']) ? intval($_POST['team1_id']) : 0;
    $team2_id = isset($_POST['team2_id']) ? intval($_POST['team2_id']) : 0;

    if (!$team1_id || !$team2_id) {
        wp_send_json_error(array('message' => 'Invalid team IDs provided.'));
        return;
    }

    $team1_data = get_team_data_with_members($team1_id);
    $team2_data = get_team_data_with_members($team2_id);

    wp_send_json_success(array(
        'team1' => $team1_data,
        'team2' => $team2_data
    ));
}

function get_team_data_with_members($team_id)
{
    $team_post = get_post($team_id);
    if (!$team_post || $team_post->post_type !== YOUR_TEAM_CPT_SLUG) {
        error_log("Team not found or wrong post type for ID: $team_id");
        return array('name' => 'Unknown Team', 'members' => array(), 'jersey_image_url' => '');
    }

    $team_name = $team_post->post_title;
    error_log("Team found: $team_name (ID: $team_id)");

    // Get team jersey image
    $jersey_image_id = get_post_meta($team_id, 'team_jersey_image_id', true);
    $jersey_image_url = $jersey_image_id ? wp_get_attachment_url($jersey_image_id) : '';

    // Get team players from post meta (stored as 'team_players', not 'team_members')
    $team_players_json = get_post_meta($team_id, 'team_players', true);
    error_log("Team players JSON for team $team_id: " . $team_players_json);

    $members = array();

    if (!empty($team_players_json)) {
        $team_players = json_decode($team_players_json, true);
        error_log("Decoded team players for team $team_id: " . print_r($team_players, true));

        if (is_array($team_players)) {
            foreach ($team_players as $player) {
                if (isset($player['id']) && isset($player['name'])) {
                    $members[] = array(
                        'id' => $player['id'],
                        'name' => $player['name']
                    );
                }
            }
        }
    }

    error_log("Final members array for team $team_id: " . print_r($members, true));

    return array(
        'name' => $team_name,
        'members' => $members,
        'jersey_image_url' => $jersey_image_url
    );
}

// Enhanced theme isolation function
function your_plugin_isolate_from_theme()
{
    global $post;

    // Only apply theme isolation on pages with our shortcodes
    if (
        ! is_a($post, 'WP_Post') ||
        (
            ! has_shortcode($post->post_content, 'admin_dashboard_widget') &&
            ! has_shortcode($post->post_content, 'start_match_shortcode')
        )
    ) {
        return;
    }

    // Remove theme styles that might interfere with our plugin
    $theme_styles_to_remove = array(
        'astra-theme-css',
        'astra-woocommerce-css',
        'astra-addon-css',
        'beaver-builder-theme-css',
        'bb-theme-css',
        'bb-plugin-css'
    );

    foreach ($theme_styles_to_remove as $style_handle) {
        if (wp_style_is($style_handle, 'enqueued')) {
            wp_dequeue_style($style_handle);
            wp_deregister_style($style_handle);
        }
    }

    // Add custom CSS to ensure our plugin takes precedence
    $isolation_css = "
    /* Soccer Plugin Theme Isolation */
    body.your-plugin-dashboard-active {
        background: linear-gradient(to bottom right, #1e293b, #4c1d95, #1e293b) !important;
        background-size: 200% 200% !important;
        background-attachment: fixed !important;
        color: #e2e8f0 !important;
        min-height: 100vh !important;
    }
    
    /* Ensure plugin container is isolated */
    .soccer-plugin-container {
        position: relative !important;
        z-index: 1000 !important;
        background: transparent !important;
        color: inherit !important;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    }
    
    /* Override any remaining theme styles */
    .soccer-plugin-container * {
        box-sizing: border-box !important;
    }
    
    .soccer-plugin-container h1,
    .soccer-plugin-container h2,
    .soccer-plugin-container h3,
    .soccer-plugin-container h4,
    .soccer-plugin-container h5,
    .soccer-plugin-container h6 {
        color: #f1f5f9 !important;
        font-weight: 600 !important;
        margin: 0 !important;
        padding: 0 !important;
        font-family: 'Inter', sans-serif !important;
    }
    
    .soccer-plugin-container p {
        color: #e2e8f0 !important;
        margin: 0 !important;
        padding: 0 !important;
        font-family: 'Inter', sans-serif !important;
    }
    
    /* Ensure forms are properly styled */
    .soccer-plugin-container input,
    .soccer-plugin-container select,
    .soccer-plugin-container textarea {
        font-family: 'Inter', sans-serif !important;
        color: #e2e8f0 !important;
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        border-radius: 8px !important;
        padding: 12px 16px !important;
        font-size: 14px !important;
        transition: all 0.3s ease !important;
    }
    
    .soccer-plugin-container input:focus,
    .soccer-plugin-container select:focus,
    .soccer-plugin-container textarea:focus {
        outline: none !important;
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
    }
    
    /* Button isolation */
    .soccer-plugin-container .btn,
    .soccer-plugin-container button {
        background: linear-gradient(135deg, #6366f1, #8b5cf6) !important;
        color: white !important;
        border: none !important;
        border-radius: 8px !important;
        padding: 12px 24px !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        transition: all 0.3s ease !important;
        text-decoration: none !important;
        display: inline-block !important;
        font-family: 'Inter', sans-serif !important;
    }
    
    .soccer-plugin-container .btn:hover,
    .soccer-plugin-container button:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3) !important;
    }
    
    /* Table isolation */
    .soccer-plugin-container table {
        width: 100% !important;
        border-collapse: collapse !important;
        background: rgba(255, 255, 255, 0.05) !important;
        border-radius: 12px !important;
        overflow: hidden !important;
        margin: 0 !important;
    }
    
    .soccer-plugin-container th,
    .soccer-plugin-container td {
        padding: 16px !important;
        text-align: left !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: #e2e8f0 !important;
    }
    
    .soccer-plugin-container th {
        background: rgba(255, 255, 255, 0.1) !important;
        font-weight: 600 !important;
        color: #f1f5f9 !important;
    }
    
    /* Glassmorphism effect */
    .soccer-plugin-container .glassmorphism {
        background: rgba(255, 255, 255, 0.05) !important;
        backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 16px !important;
        padding: 24px !important;
        margin: 16px 0 !important;
    }
    
    /* Card hover effects */
    .soccer-plugin-container .card-hover {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    
    .soccer-plugin-container .card-hover:hover {
        transform: translateY(-4px) !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .soccer-plugin-container .glassmorphism {
            margin: 8px !important;
            padding: 16px !important;
        }
        
        .soccer-plugin-container input,
        .soccer-plugin-container select,
        .soccer-plugin-container textarea {
            font-size: 16px !important;
        }
    }
    ";

    wp_add_inline_style('your-plugin-style', $isolation_css);
}

// Hook the theme isolation function
add_action('wp_enqueue_scripts', 'your_plugin_isolate_from_theme', 999);
