<?php

include('NFL5.php');

// populates the schedule table with scores from this season or previous seasons
$tidy = new tidy;

$nfl = new NFL5;
$db = $nfl->getDB();

$lookup = $nfl->getTeamLookupTable();

$year = $argv[1];
$url = "http://www.pro-football-reference.com/years/$year/games.htm";

// local cache
$schedule = dirname(__FILE__)."/schedule$year.html";
if ($year == THIS_YEAR && file_exists($schedule)) { // always delete this year's schedule
    unlink($schedule);
}

if (!file_exists($schedule)) {
    file_put_contents($schedule, file_get_contents($url));
}
$dom = new DOMDocument;
@$dom->loadHTML($tidy->repairString(file_get_contents($schedule)));
$xpath = new DOMXPath($dom);

if ($year == THIS_YEAR) {
    // if it's this year, delete all records so far for this year
    $db->exec(sprintf("DELETE FROM schedule WHERE `kickoff` > '%d-09-01' AND `kickoff` < '%d-03-01';", THIS_YEAR, THIS_YEAR + 1));

    // add games that are scheduled but have not happened

    /* @var $nodes DOMNodeList */
    $nodes = @$xpath->query("//table[@id='games_left']/tbody/tr");
    if ($nodes->length > 0) {
        /* @var $node DOMElement */
        foreach ($nodes as $node) {

            // don't process header rows
            if ($node->hasAttribute('class') && strpos($node->getAttribute('class'), 'thead') !== false) continue;

            /* @var $children DOMNodeList */
            $children = $node->childNodes;

            // date has to be converted into 24 hour time
            $date = preg_replace('/[\r\n]+/', '', $children->item(4)->nodeValue);
            $time = $children->item(12)->nodeValue;

            $sql = 'INSERT INTO `schedule` (kickoff, hteamid, ateamid) VALUES (?, ?, ?);';
            $stmt = $db->prepare($sql);
            $stmt->execute(array(
                date('Y-m-d H:i:s', strtotime("$date $time")), // this actually works
                $lookup[$nfl->getMascot($children->item(10)->nodeValue)],
                $lookup[$nfl->getMascot($children->item(6)->nodeValue)],
            ));
        }
    }
}

/* @var $nodes DOMNodeList */
$nodes = @$xpath->query("//table[@id='games']/tbody/tr");
if ($nodes->length > 0) {
    /* @var $node DOMElement */
    foreach ($nodes as $node) {

        // don't process header rows
        if ($node->hasAttribute('class') && strpos($node->getAttribute('class'), 'thead') !== false) continue;

        /* @var $children DOMNodeList */
        $children = $node->childNodes;

        // these are going to have a number of child nodes
        $date = $children->item(4)->getAttribute('csk');

        if (strpos($date, 'zz') !== false) {
            $date = date('Y-m-d', strtotime($children->item(4)->nodeValue . ' ' . ($year + 1)));
        }

        $winner     = $children->item(8)->nodeValue;
        $at         = $children->item(10)->nodeValue;
        $loser      = $children->item(12)->nodeValue;
        $wscore     = $children->item(14)->nodeValue;
        $lscore     = $children->item(16)->nodeValue;

        $wmascot = $nfl->getMascot($winner);
        $lmascot = $nfl->getMascot($loser);

        if (trim($at)) {
            $hteamid = $lookup[$lmascot];
            $ateamid = $lookup[$wmascot];
            $hscore = $lscore;
            $ascore = $wscore;
        } else {
            $hteamid = $lookup[$wmascot];
            $ateamid = $lookup[$lmascot];
            $hscore = $wscore;
            $ascore = $lscore;
        }

        $sql = 'INSERT INTO `schedule` (kickoff, hteamid, ateamid, hscore, ascore) VALUES (?, ?, ?, ?, ?);';
        $stmt = $db->prepare($sql);
        $stmt->execute(array(
            trim($date),
            $hteamid,
            $ateamid,
            $hscore,
            $ascore,
        ));
    }
}