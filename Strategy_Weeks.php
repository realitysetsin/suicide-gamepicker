<?php

// A strategy for games that take place close to the beginning or end of the season.
// If games take place in selected seasons, reduce confidence

// The reason is, for the beginning of the season, it is difficult to gague
// team skill accurately. And for the end of the season, teams may relax if their
// playoff slot is guaranteed

class Strategy_Weeks implements Strategy {
    
    public function __construct(NFL5 $nfl) {}
    
    public function apply($schedule) {
        
        foreach ($schedule as $week => $games) {
            if (!in_array($week, array(1,2,16,17))) continue;
            foreach ($games as $idx => $g) {
                $schedule[$week][$idx]['c'] *= .75;
            }
        }
        
        return $schedule;
    }
}