<?php

include('NFL5.php');

// outputs a list of all teams playing this week, and the confidence level for each matchup
$nfl = new NFL5;
$week = $argv[1];

$nfl->buildSchedule($week);
$nfl->prime();
$nfl->applyStrategy(STRATEGY_SAME_CONFERENCE);
$nfl->applyStrategy(STRATEGY_WEEKS);
$nfl->applyStrategy(STRATEGY_MATCHUPS);

$teams = $nfl->getTeamLookupTable();
$schedule = $nfl->getSchedule();

$output = array();
foreach ($schedule[$week] as $k => $s) {
    $line = array();
    $line[] = sprintf("%s, %s %s (%d) at %s %s (%d)",
            date('n/j/Y, g:i a', strtotime($s['kickoff'])),
            $teams[$s['ateamid']]['city'], $teams[$s['ateamid']]['mascot'], $teams[$s['ateamid']]['rank'],
            $teams[$s['hteamid']]['city'], $teams[$s['hteamid']]['mascot'], $teams[$s['hteamid']]['rank']);

    $winid = $s['hscore'] > $s['ascore'] ? $s['hteamid'] : $s['ateamid'];
    $wscore = $s['hscore'] > $s['ascore'] ? $s['hscore'] : $s['ascore'];
    $lscore = $s['hscore'] > $s['ascore'] ? $s['ascore'] : $s['hscore'];

    $line[] = sprintf("Projection: %s (%d to %d) [Confidence: %01.2f]", $teams[$winid]['mascot'], round($wscore), round($lscore), round($s['c'], 2));

    $output[strtotime($s['kickoff']) + $k] = implode("\n", $line);
}

ksort($output);
fwrite(STDOUT, implode("\n\n", $output)."\n");