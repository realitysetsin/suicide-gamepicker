<?php

// plots a path through the rest of the season, picking the optimum team to select each week while minimizing risk
include('NFL5.php');

$nfl = new NFL5;
$nfl->buildSchedule();
$nfl->prime();
$nfl->applyStrategy(STRATEGY_SAME_CONFERENCE);
$nfl->applyStrategy(STRATEGY_WEEKS);
$nfl->applyStrategy(STRATEGY_MATCHUPS);

$teams = $nfl->getTeamLookupTable();
$schedule = $nfl->getSchedule();
$picks = $nfl->getPicks();

$num_picks = count($picks);
$picked = array();
foreach ($picks as $p) {
    $picked[$p] = 100;
}

$matchups = array();
$losers = array();

foreach ($schedule as $week => $games) {
    foreach ($games as $g) {
        $winid = $g['hscore'] > $g['ascore'] ? $g['hteamid'] : $g['ateamid'];
        $losid = $g['hscore'] > $g['ascore'] ? $g['ateamid'] : $g['hteamid'];
        $matchups[$week][$winid] = $g['c'];
        $losers[$week][$winid] = $losid;
    }
    // sort the games by highest probability descending
    arsort($matchups[$week]);
}

$curr_min = 0;
$curr_path = array();

process($num_picks + 1, $picked);

fwrite(STDOUT, "\n\nMin: $curr_min\n\n");
$week = 0;
foreach ($curr_path as $team => $score) {
    fwrite(STDOUT, sprintf("Week %d: %s over %s (Confidence: %01.2f)\n", ++$week,
            $teams[$team]['mascot'], $teams[$losers[$week][$team]]['mascot'],
            $score));
}

function process($week, $picks = array()) {

    global $matchups, $curr_min, $curr_path;
    if (!isset($matchups[$week])) {
        // this is at an end node
        // picks should contain an array of how we got here
        $min = min($picks);
        if ($min > $curr_min) {
            $curr_min = $min;
            $curr_path = $picks;
        }
    } else {
        foreach ($matchups[$week] as $team => $score) {
            if (isset($picks[$team])) continue;
            if ($score < $curr_min) continue;

            $tmp_picks = $picks;
            $tmp_picks[$team] = $score;
            process($week + 1, $tmp_picks);
        }
    }
}