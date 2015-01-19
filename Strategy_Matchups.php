<?php

// A strategy for rating games based on historical matchups.

// The reason is, certain teams have histories that cannot be determined
// through straight power rankings.

class Strategy_Matchups implements Strategy {
    
    private $nfl;
    private $matchups;
    private $db;
    private $num_years = 1;
    
    public function __construct(NFL5 $nfl) {
        $this->nfl = $nfl;
        $this->db = $nfl->getDB();
        $this->buildMatchups();
    }
    
    public function apply($schedule) {
        
        foreach ($schedule as $week => $games) {
            foreach ($games as $idx => $g) {
                if ($g['hscore'] > $g['ascore']) {
                    $wid = $g['hteamid'];
                    $lid = $g['ateamid'];
                } else {
                    $wid = $g['ateamid'];
                    $lid = $g['hteamid'];
                }
                
                if (isset($this->matchups[$wid][$lid])) {
                    // the historical average, weighted by the number of times that they have played (less times played reduces confidence)
                    $historical_distance = array_sum($this->matchups[$wid][$lid]) / count($this->matchups[$wid][$lid]);
                    $match_weight = count($this->matchups[$wid][$lid]) / ($this->num_years * 2);
                    
                    $schedule[$week][$idx]['c'] += ($historical_distance * $match_weight);
                } else {
                    // if there are no previous games, reduce confidence by a quarter
                    $schedule[$week][$idx]['c'] *= .75;
                }
            }
        }
        
        return $schedule;
    }
    
    private function buildMatchups() {

        $this->matchups = array();
        $years = array();

        $stmt = $this->db->query("SELECT * FROM schedule WHERE hscore IS NOT NULL AND ascore IS NOT NULL");

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $s) {
            $season = $this->getSeasonFromKickoff($s['kickoff']);
            $years[$season] = true;
            $this->matchups[$s['hteamid']][$s['ateamid']][] = ($s['hscore'] - $s['ascore']) * (1 / (THIS_YEAR + 1 - $season));
            $this->matchups[$s['ateamid']][$s['hteamid']][] = ($s['ascore'] - $s['hscore']) * (1 / (THIS_YEAR + 1 - $season));
        }
        
        $this->num_years = count($years);
    }
    
    private function getSeasonFromKickoff($kickoff_time) {
        // let's let the cuttoff be june
        list($y,$m) = explode('-',date('Y-m', strtotime($kickoff_time)));
        return ($m < 6) ? $y - 1 : (int)$y;
    }

}