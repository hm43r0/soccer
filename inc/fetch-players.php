<?php
// Security helper for admin-only actions (reused from fetch-referees.php)
if (!function_exists('your_plugin_require_admin_and_nonce')) {
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
}

// AJAX Handler for fetching player statistics
add_action('wp_ajax_your_plugin_fetch_players_stats', 'your_plugin_fetch_players_stats_handler');
add_action('wp_ajax_nopriv_your_plugin_fetch_players_stats', 'your_plugin_fetch_players_stats_handler');

function your_plugin_fetch_players_stats_handler() {
    if ( ! your_plugin_require_admin_and_nonce() ) return;

    // Get all teams to extract players
    $teams_query = new WP_Query(array(
        'post_type'      => defined('YOUR_TEAM_CPT_SLUG') ? YOUR_TEAM_CPT_SLUG : 'team_management',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ));

    $all_players = array();
    $team_lookup = array(); // To map team_id to team info

    if ($teams_query->have_posts()) {
        while ($teams_query->have_posts()) {
            $teams_query->the_post();
            $team_id = get_the_ID();
            $team_name = get_the_title();
            
            // Store team info for lookup
            $team_lookup[$team_id] = array(
                'name' => $team_name,
                'team_location' => get_post_meta($team_id, 'team_location', true),
                'team_day' => get_post_meta($team_id, 'team_day', true),
                'team_league' => get_post_meta($team_id, 'team_league', true),
                'team_division' => get_post_meta($team_id, 'team_division', true),
                'team_season' => get_post_meta($team_id, 'team_season', true),
                'team_year' => get_post_meta($team_id, 'team_year', true),
            );

            // Get team players
            $players_json = get_post_meta($team_id, 'team_players', true);
            
            if (!empty($players_json)) {
                $players = json_decode($players_json, true);
                if (is_array($players)) {
                    foreach ($players as $player) {
                        if (isset($player['id']) && isset($player['name']) && !empty(trim($player['name']))) {
                            $player_id = strval($player['id']); // Ensure consistent string format
                            $player_name = trim($player['name']);

                            // Skip players with empty names or IDs
                            if (empty($player_id) || empty($player_name)) {
                                continue;
                            }

                            // Initialize player stats or update team info if player exists
                            if (!isset($all_players[$player_id])) {
                                $all_players[$player_id] = array(
                                    'player_id' => $player_id,
                                    'name' => $player_name,
                                    'team_id' => $team_id,
                                    'team_name' => $team_name,
                                    'teams' => array($team_id => $team_name), // Track all teams
                                    'games_played' => 0,
                                    'goals' => 0,
                                    'blue_cards' => 0,
                                    'yellow_cards' => 0,
                                    'red_cards' => 0,
                                    'injuries' => 0, // We'll implement this when injury tracking is added
                                );
                            } else {
                                // Player exists, add this team to their team list
                                $all_players[$player_id]['teams'][$team_id] = $team_name;
                            }
                        }
                    }
                }
            }
        }
        wp_reset_postdata();
    }

    // Now get match data to calculate player statistics
    $matches_query = new WP_Query(array(
        'post_type'      => defined('YOUR_MATCH_CPT_SLUG') ? YOUR_MATCH_CPT_SLUG : 'match_management',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ));

    if ($matches_query->have_posts()) {
        while ($matches_query->have_posts()) {
            $matches_query->the_post();
            $match_id = get_the_ID();
            
            // Only count games played for completed matches
            $is_completed = get_post_meta($match_id, 'is_completed', true);
            if ($is_completed !== '1') {
                error_log("Match $match_id: Skipping incomplete match for games played calculation");
                continue; // Skip this match if it's not completed
            }
            
            $score_data_json = get_post_meta($match_id, 'score_data', true);
            $team1_id = get_post_meta($match_id, '_team1_id', true);
            $team2_id = get_post_meta($match_id, '_team2_id', true);
            
            if (!empty($score_data_json)) {
                $score_data = json_decode($score_data_json, true);
                if (is_array($score_data)) {
                    // Process team1 and team2 data
                    foreach (['team1', 'team2'] as $team_key) {
                        if (isset($score_data[$team_key])) {
                            $team_data = $score_data[$team_key];
                            
                            // Process goals
                            if (isset($team_data['goals']) && is_array($team_data['goals'])) {
                                foreach ($team_data['goals'] as $goal) {
                                    if (is_array($goal) && isset($goal['playerId'])) {
                                        $player_id = strval($goal['playerId']);
                                        if (isset($all_players[$player_id])) {
                                            $all_players[$player_id]['goals']++;
                                        }
                                    } else if (!is_null($goal) && $goal !== '' && $goal !== false) {
                                        // Handle case where goal is just a player ID
                                        $player_id = strval($goal);
                                        if (isset($all_players[$player_id])) {
                                            $all_players[$player_id]['goals']++;
                                        }
                                    }
                                }
                            }
                            
                            // Process cards
                            if (isset($team_data['cards']) && is_array($team_data['cards'])) {
                                foreach ($team_data['cards'] as $card) {
                                    if (is_array($card) && isset($card['playerId'])) {
                                        $player_id = strval($card['playerId']);
                                        if (isset($all_players[$player_id])) {
                                            if (isset($card['blue']) && $card['blue'] === true) {
                                                $all_players[$player_id]['blue_cards']++;
                                            }
                                            if (isset($card['yellow']) && $card['yellow'] === true) {
                                                $all_players[$player_id]['yellow_cards']++;
                                            }
                                            if (isset($card['red']) && $card['red'] === true) {
                                                $all_players[$player_id]['red_cards']++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            // Count games played based on attendance data in score_data
            if (!empty($score_data_json)) {
                $score_data = json_decode($score_data_json, true);
                if (is_array($score_data)) {
                    // Check for attendance/lineup data in score_data
                    foreach (['team1', 'team2'] as $team_key) {
                        if (isset($score_data[$team_key]['attendance']) && is_array($score_data[$team_key]['attendance']) && !empty($score_data[$team_key]['attendance'])) {
                            // Use attendance data if available and not empty
                            error_log("Match $match_id: Found attendance data for $team_key: " . json_encode($score_data[$team_key]['attendance']));
                            foreach ($score_data[$team_key]['attendance'] as $player_id) {
                                $player_id = strval($player_id);
                                if (isset($all_players[$player_id])) {
                                    $all_players[$player_id]['games_played']++;
                                    error_log("Match $match_id: Counted game for player $player_id via attendance");
                                }
                            }
                        } else if (isset($score_data[$team_key]['lineup']) && is_array($score_data[$team_key]['lineup']) && !empty($score_data[$team_key]['lineup'])) {
                            // Alternative: use lineup data if available and not empty
                            error_log("Match $match_id: Found lineup data for $team_key: " . json_encode($score_data[$team_key]['lineup']));
                            foreach ($score_data[$team_key]['lineup'] as $player_id) {
                                $player_id = strval($player_id);
                                if (isset($all_players[$player_id])) {
                                    $all_players[$player_id]['games_played']++;
                                }
                            }
                        }
                    }
                }
            }
            
            // Fallback: If no attendance data exists, count based on team membership (old logic)
            $has_attendance_data = false;
            if (!empty($score_data_json)) {
                $score_data = json_decode($score_data_json, true);
                if (is_array($score_data)) {
                    foreach (['team1', 'team2'] as $team_key) {
                        if ((isset($score_data[$team_key]['attendance']) && is_array($score_data[$team_key]['attendance']) && !empty($score_data[$team_key]['attendance'])) ||
                            (isset($score_data[$team_key]['lineup']) && is_array($score_data[$team_key]['lineup']) && !empty($score_data[$team_key]['lineup']))) {
                            $has_attendance_data = true;
                            error_log("Match $match_id: Found attendance/lineup data in $team_key: " . json_encode($score_data[$team_key]['attendance'] ?? $score_data[$team_key]['lineup']));
                            break;
                        }
                    }
                }
            }
            
            if (!$has_attendance_data) {
                error_log("Match $match_id: No attendance data found, using fallback team membership logic");
                error_log("Match $match_id: Full score_data: " . $score_data_json);
                $match_players_counted = array(); // Track which players we've already counted for this match
                
                if (!empty($team1_id)) {
                    $team1_players_json = get_post_meta($team1_id, 'team_players', true);
                    if (!empty($team1_players_json)) {
                        $team1_players = json_decode($team1_players_json, true);
                        if (is_array($team1_players)) {
                            foreach ($team1_players as $player) {
                                if (isset($player['id']) && isset($all_players[$player['id']])) {
                                    $player_id = $player['id'];
                                    if (!isset($match_players_counted[$player_id])) {
                                        $all_players[$player_id]['games_played']++;
                                        $match_players_counted[$player_id] = true;
                                    }
                                }
                            }
                        }
                    }
                }
                
                if (!empty($team2_id)) {
                    $team2_players_json = get_post_meta($team2_id, 'team_players', true);
                    if (!empty($team2_players_json)) {
                        $team2_players = json_decode($team2_players_json, true);
                        if (is_array($team2_players)) {
                            foreach ($team2_players as $player) {
                                if (isset($player['id']) && isset($all_players[$player['id']])) {
                                    $player_id = $player['id'];
                                    if (!isset($match_players_counted[$player_id])) {
                                        $all_players[$player_id]['games_played']++;
                                        $match_players_counted[$player_id] = true;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        wp_reset_postdata();
    }

    // Calculate overall statistics
    $total_players = count($all_players);
    $total_goals = 0;
    $total_cards = 0;
    $active_players = 0;

    foreach ($all_players as &$player) {
        $total_goals += $player['goals'];
        $total_cards += $player['blue_cards'] + $player['yellow_cards'] + $player['red_cards'];
        if ($player['games_played'] > 0) {
            $active_players++;
        }
        
        // Convert teams array to simple format for frontend
        if (isset($player['teams']) && is_array($player['teams'])) {
            $player['team_names'] = implode(', ', array_values($player['teams']));
            $player['team_ids'] = array_keys($player['teams']);
            // Keep the first team as primary for backward compatibility
            $first_team_id = array_keys($player['teams'])[0];
            $player['team_id'] = $first_team_id;
            $player['team_name'] = $player['teams'][$first_team_id];
        }
    }

    $stats = array(
        'total_players' => $total_players, // This won't be used anymore, but keeping for compatibility
        'total_goals' => $total_goals,
        'total_cards' => $total_cards,
        'active_players' => $active_players,
    );

    // Convert associative array to indexed array for response
    $players_array = array_values($all_players);

    wp_send_json_success(array(
        'players' => $players_array,
        'stats' => $stats,
    ));
}
