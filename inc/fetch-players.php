<?php
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

                            // Skip players with empty names or IDs (but allow ID "0")
                            if ((empty($player_id) && $player_id !== "0") || empty($player_name)) {
                                continue;
                            }

                            // Create a new player entry for each player (no deduplication by ID)
                            $all_players[] = array(
                                'player_id' => $player_id,
                                'name' => $player_name,
                                'team_id' => intval($team_id),
                                'team_name' => $team_name,
                                'teams' => array($team_id => $team_name), // Track all teams (for future multi-team support)
                                'games_played' => 0,
                                'goals' => 0,
                                'blue_cards' => 0,
                                'yellow_cards' => 0,
                                'red_cards' => 0,
                                'injuries' => 0, // We'll implement this when injury tracking is added
                            );
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
            error_log("Match $match_id: is_completed = '$is_completed'");
            if ($is_completed !== '1') {
                error_log("Match $match_id: Skipping incomplete match for games played calculation");
                continue; // Skip this match if it's not completed
            }
            
            $score_data_json = get_post_meta($match_id, 'score_data', true);
            $team1_id = intval(get_post_meta($match_id, '_team1_id', true));
            $team2_id = intval(get_post_meta($match_id, '_team2_id', true));
            
            error_log("Match $match_id: team1_id = $team1_id, team2_id = $team2_id, is_completed = $is_completed");
            error_log("Match $match_id: score_data_json = $score_data_json");
            
            if (!empty($score_data_json)) {
                $score_data = json_decode($score_data_json, true);
                error_log("Match $match_id: score_data_json length: " . strlen($score_data_json));
                if (is_array($score_data)) {
                    // Debug: Log the score data
                    error_log("Match $match_id: Processing score_data: " . $score_data_json);
                    
                    // Initialize participating players arrays
                    $participating_team1 = array();
                    $participating_team2 = array();
                    
                    // Process team1 and team2 data
                    foreach (['team1', 'team2'] as $team_key) {
                        if (isset($score_data[$team_key])) {
                            $team_data = $score_data[$team_key];
                            
                            // Process goals
                            if (isset($team_data['goals']) && is_array($team_data['goals'])) {
                                foreach ($team_data['goals'] as $goal) {
                                    if (is_array($goal) && isset($goal['playerId'])) {
                                        $player_id = strval($goal['playerId']);
                                        // Find player by player_id and team_id
                                        foreach ($all_players as &$player) {
                                            if ($player['player_id'] === $player_id && $player['team_id'] === ${$team_key . '_id'}) {
                                                $player['goals']++;
                                                if ($team_key == 'team1') {
                                                    $participating_team1[] = $player_id;
                                                } else {
                                                    $participating_team2[] = $player_id;
                                                }
                                                break;
                                            }
                                        }
                                    } else if (!is_null($goal) && $goal !== '' && $goal !== false) {
                                        // Handle case where goal is just a player ID
                                        $player_id = strval($goal);
                                        // Find player by player_id and team_id
                                        foreach ($all_players as &$player) {
                                            if ($player['player_id'] === $player_id && $player['team_id'] === ${$team_key . '_id'}) {
                                                $player['goals']++;
                                                if ($team_key == 'team1') {
                                                    $participating_team1[] = $player_id;
                                                } else {
                                                    $participating_team2[] = $player_id;
                                                }
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                            
                            // Process cards
                            if (isset($team_data['cards']) && is_array($team_data['cards'])) {
                                foreach ($team_data['cards'] as $card) {
                                    if (is_array($card) && isset($card['playerId'])) {
                                        $player_id = strval($card['playerId']);
                                        // Find player by player_id and team_id
                                        foreach ($all_players as &$player) {
                                            if ($player['player_id'] === $player_id && $player['team_id'] === ${$team_key . '_id'}) {
                                                if (isset($card['blue']) && $card['blue'] === true) {
                                                    $player['blue_cards']++;
                                                }
                                                if (isset($card['yellow']) && $card['yellow'] === true) {
                                                    $player['yellow_cards']++;
                                                }
                                                if (isset($card['red']) && $card['red'] === true) {
                                                    $player['red_cards']++;
                                                }
                                                if ($team_key == 'team1') {
                                                    $participating_team1[] = $player_id;
                                                } else {
                                                    $participating_team2[] = $player_id;
                                                }
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                            
                            // Add attendance/lineup players
                            if (isset($team_data['attendance']) && is_array($team_data['attendance'])) {
                                error_log("Match $match_id: Found attendance data for $team_key: " . json_encode($team_data['attendance']));
                                foreach ($team_data['attendance'] as $attendance_item) {
                                    // Handle both formats: objects with id/name or just IDs
                                    if (is_array($attendance_item) && isset($attendance_item['id'])) {
                                        $player_id = strval($attendance_item['id']);
                                        error_log("Match $match_id: Processing attendance object for $team_key, player_id: $player_id");
                                    } else {
                                        $player_id = strval($attendance_item);
                                        error_log("Match $match_id: Processing attendance ID for $team_key, player_id: $player_id");
                                    }
                                    
                                    if ($team_key == 'team1') {
                                        $participating_team1[] = $player_id;
                                    } else {
                                        $participating_team2[] = $player_id;
                                    }
                                }
                            } elseif (isset($team_data['lineup']) && is_array($team_data['lineup'])) {
                                error_log("Match $match_id: Found lineup data for $team_key: " . json_encode($team_data['lineup']));
                                foreach ($team_data['lineup'] as $lineup_item) {
                                    // Handle both formats: objects with id/name or just IDs
                                    if (is_array($lineup_item) && isset($lineup_item['id'])) {
                                        $player_id = strval($lineup_item['id']);
                                        error_log("Match $match_id: Processing lineup object for $team_key, player_id: $player_id");
                                    } else {
                                        $player_id = strval($lineup_item);
                                        error_log("Match $match_id: Processing lineup ID for $team_key, player_id: $player_id");
                                    }
                                    
                                    if ($team_key == 'team1') {
                                        $participating_team1[] = $player_id;
                                    } else {
                                        $participating_team2[] = $player_id;
                                    }
                                }
                            }
                        }
                    }
                    
                    // Make participating lists unique and increment games_played
                    $participating_team1 = array_unique($participating_team1);
                    $participating_team2 = array_unique($participating_team2);
                    
                    error_log("Match $match_id: Final participating_team1: " . json_encode($participating_team1));
                    error_log("Match $match_id: Final participating_team2: " . json_encode($participating_team2));
                    
                    foreach ($participating_team1 as $player_id) {
                        foreach ($all_players as &$player) {
                            if ($player['player_id'] === strval($player_id) && $player['team_id'] === $team1_id) {
                                $player['games_played']++;
                                error_log("Match $match_id: Incremented games_played for team1 player $player_id");
                                break;
                            }
                        }
                    }
                    
                    foreach ($participating_team2 as $player_id) {
                        foreach ($all_players as &$player) {
                            if ($player['player_id'] === strval($player_id) && $player['team_id'] === $team2_id) {
                                $player['games_played']++;
                                error_log("Match $match_id: Incremented games_played for team2 player $player_id");
                                break;
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

    // Convert associative array to indexed array for response (already indexed, so just use as is)
    $players_array = $all_players;

    wp_send_json_success(array(
        'players' => $players_array,
        'stats' => $stats,
    ));
}
