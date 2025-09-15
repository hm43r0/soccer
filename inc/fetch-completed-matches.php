<?php
/**
 * Fetch Completed Matches AJAX Handler
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include WordPress functions if not already loaded
if (!function_exists('get_posts')) {
    require_once(ABSPATH . 'wp-load.php');
}

function your_plugin_fetch_completed_matches() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'your_plugin_ajax_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }

    // Query for completed matches
    $args = array(
        'post_type' => 'match_management',
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'is_completed',
                'value' => '1',
                'compare' => '='
            )
        ),
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
    );

    $matches_query = new WP_Query($args);
    $matches = array();
    $stats = array(
        'total_completed_matches' => 0,
        'total_goals' => 0,
        'total_cards' => 0
    );

    if ($matches_query->have_posts()) {
        while ($matches_query->have_posts()) {
            $matches_query->the_post();
            $match_id = get_the_ID();

            // Get match metadata
            $team1_id = get_post_meta($match_id, '_team1_id', true);
            $team2_id = get_post_meta($match_id, '_team2_id', true);
            $match_date = get_post_meta($match_id, '_match_date', true);
            $match_location = get_post_meta($match_id, '_match_location', true);
            $match_week = get_post_meta($match_id, '_match_week', true);
            $referee_id = get_post_meta($match_id, '_referee_id', true);
            $final_score = get_post_meta($match_id, 'final_score', true);
            $score_data = get_post_meta($match_id, 'score_data', true);
            $caution_desc = get_post_meta($match_id, 'caution_desc', true);
            $additional_notes = get_post_meta($match_id, 'additional_notes', true);
            $first_half_fouls = get_post_meta($match_id, 'first_half_fouls', true);
            $second_half_fouls = get_post_meta($match_id, 'second_half_fouls', true);

            // Get referee name
            $referee_name = '';
            if ($referee_id) {
                $referee_user = get_user_by('id', $referee_id);
                if ($referee_user) {
                    $referee_name = $referee_user->first_name . ' ' . $referee_user->last_name;
                    if (empty(trim($referee_name))) {
                        $referee_name = $referee_user->user_login;
                    }
                }
            }

            // Calculate stats from score data
            $total_goals = 0;
            $total_cards = 0;
            $total_fouls = intval($first_half_fouls) + intval($second_half_fouls);

            if (!empty($score_data)) {
                $score_data_array = json_decode($score_data, true);
                if (is_array($score_data_array)) {
                    // Count goals
                    if (isset($score_data_array['team1']['goals'])) {
                        $total_goals += count(array_filter($score_data_array['team1']['goals'], function($goal) {
                            return !empty($goal) && isset($goal['playerId']);
                        }));
                    }
                    if (isset($score_data_array['team2']['goals'])) {
                        $total_goals += count(array_filter($score_data_array['team2']['goals'], function($goal) {
                            return !empty($goal) && isset($goal['playerId']);
                        }));
                    }

                    // Count cards
                    if (isset($score_data_array['team1']['cards'])) {
                        foreach ($score_data_array['team1']['cards'] as $card) {
                            if (isset($card['blue']) && $card['blue']) $total_cards++;
                            if (isset($card['yellow']) && $card['yellow']) $total_cards++;
                            if (isset($card['red']) && $card['red']) $total_cards++;
                        }
                    }
                    if (isset($score_data_array['team2']['cards'])) {
                        foreach ($score_data_array['team2']['cards'] as $card) {
                            if (isset($card['blue']) && $card['blue']) $total_cards++;
                            if (isset($card['yellow']) && $card['yellow']) $total_cards++;
                            if (isset($card['red']) && $card['red']) $total_cards++;
                        }
                    }
                }
            }

            $match_data = array(
                'id' => $match_id,
                'team1Id' => $team1_id,
                'team2Id' => $team2_id,
                'date' => $match_date,
                'location' => $match_location,
                'week' => $match_week,
                'referee' => $referee_name,
                'refereeId' => $referee_id,
                'final_score' => $final_score,
                'total_goals' => $total_goals,
                'total_cards' => $total_cards,
                'total_fouls' => $total_fouls,
                'caution_desc' => $caution_desc,
                'additional_notes' => $additional_notes,
                'score_data' => $score_data,
                'status' => 'completed'
            );

            $matches[] = $match_data;

            // Update stats
            $stats['total_completed_matches']++;
            $stats['total_goals'] += $total_goals;
            $stats['total_cards'] += $total_cards;
        }
        wp_reset_postdata();
    }

    wp_send_json_success(array(
        'matches' => $matches,
        'stats' => $stats
    ));
}

// Hook the function
add_action('wp_ajax_your_plugin_fetch_completed_matches', 'your_plugin_fetch_completed_matches');
add_action('wp_ajax_nopriv_your_plugin_fetch_completed_matches', 'your_plugin_fetch_completed_matches');
?>