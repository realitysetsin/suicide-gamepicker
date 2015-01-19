<?php

require('Strategy.php');

// you should only need to set these once per season
define('THIS_YEAR', 2014);
define('NUM_WEEKS', 17);

// various strategies
define('STRATEGY_SAME_CONFERENCE', 'Strategy_Conf');
define('STRATEGY_WEEKS', 'Strategy_Weeks');
define('STRATEGY_MATCHUPS', 'Strategy_Matchups');

define('POWER_RANKINGS_URL', 'http://www.masseyratings.com/ratejson.php?s=262648');

class NFL5 {

    private $db;
    private $lookup;
    private $schedule;

    public function __construct() {
        try {
            $this->db = new PDO("mysql:host=localhost;dbname=nfl", 'nfl', '!nfl1');
        } catch (PDOException $e) {
            die(var_dump($e));
            throw $e;
        }
        $this->createTeamLookupTable();
    }

    public function &getDB() {
        return $this->db;
    }

    public function getTeamLookupTable() {
        return $this->lookup;
    }

    private function createTeamLookupTable() {
        $stmt = $this->db->query("SELECT * FROM team");
        $this->lookup = array();

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $t) {
            $this->lookup[$t['id']]       = $t;
            $this->lookup[$t['city']]     = $t['id'];
            $this->lookup[$t['mascot']]   = $t['id'];
            $this->lookup[$t['alt_city']] = $t['id'];
        }
    }

    public function applyStrategy($strategy) {
        require($strategy . '.php');
        $class = new $strategy($this);
        $this->schedule = $class->apply($this->schedule);
    }

    public function getPicks() {
        $stmt = $this->db->query("SELECT teamid FROM picks");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getSchedule() {
        return $this->schedule;
    }

    public function buildSchedule($week = null) {

        $this->schedule = array();

        if (!is_null($week)) {
            $weeks = array($week);
        } else {
            $weeks = range(1, NUM_WEEKS);
        }

        $stmt = $this->db->prepare("SELECT * FROM schedule WHERE kickoff >= ? AND kickoff <= ?");

        foreach ($weeks as $w) {
            $range = $this->getDatesForWeek($w);
            $stmt->execute(array($range['start'], $range['end']));

            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $s) {
                $this->schedule[$w][] = $s;
            }
        }
    }

    public function prime() {
        // get the json data from Massey
        $ranking_file = dirname(__FILE__) . '/rankings.json';
        if (!file_exists($ranking_file) || filemtime($ranking_file) < strtotime('-1 day')) {
            file_put_contents($ranking_file, file_get_contents(POWER_RANKINGS_URL));
        }

        $data = json_decode(file_get_contents($ranking_file), true);

        foreach ($data['DI'] as $d) {
            $id = $this->lookup[$d[0][0]]; // get id by team
            $this->lookup[$id]['rank']    = $d[5];
            $this->lookup[$id]['rating']  = $d[6];
            $this->lookup[$id]['power']   = $d[8];
            $this->lookup[$id]['offense'] = $d[10];
            $this->lookup[$id]['defense'] = $d[12];
            $this->lookup[$id]['hfa']     = $d[13];
        }

        foreach ($this->schedule as $week => $games) {
            foreach ($games as $idx => $g) {
                $combined_hfa = $this->lookup[$g['hteamid']]['hfa'] + $this->lookup[$g['ateamid']]['hfa'];
                $this->schedule[$week][$idx]['hscore'] = $this->lookup[$g['hteamid']]['offense'] - $this->lookup[$g['ateamid']]['defense'] + ($combined_hfa / 4);
                $this->schedule[$week][$idx]['ascore'] = $this->lookup[$g['ateamid']]['offense'] - $this->lookup[$g['hteamid']]['defense'] - ($combined_hfa / 4);
                $this->schedule[$week][$idx]['c'] = abs($this->schedule[$week][$idx]['hscore'] - $this->schedule[$week][$idx]['ascore']);
            }
        }
    }

    private function getDatesForWeek($week = 1, $year = THIS_YEAR) {

        // get the first kickoff for the year after june
        $stmt = $this->db->prepare("SELECT MIN(kickoff) FROM schedule WHERE kickoff > ?");
        $stmt->execute(array(sprintf('%d-06-01', $year)));
        $res = $stmt->fetch(PDO::FETCH_NUM);
        $first_kickoff = array_pop($res);

        // get the tuesday before this
        $dow = date('N', strtotime($first_kickoff));
        $season_start = new DateTime($first_kickoff);
        $season_start->modify(sprintf('-%d day', ($dow + 5) % 7)); // because we're not using 5.3

        $season_start->modify(sprintf('+%d week', $week - 1));
        $week_start = $season_start->format('Y-m-d');

        $season_start->modify('+1 week');
        $week_end = $season_start->format('Y-m-d');

        return array(
            0 => $week_start,
            1 => $week_end,
            'start' => $week_start,
            'end' => $week_end,
        );
    }

    // takes a full string like 'Green Bay Packers' and returns just the mascot part
    public function getMascot($str) {
        $tokens = preg_split('/\W+/', $str);
        return array_pop($tokens);
    }

}