<?php

// A strategy for teams in the same conference.
// If teams are in the same conference, reduce confidence by half

// the reason is teams in the same conference play games more competitively because
// the games count more toward making the playoffs

class Strategy_Conf implements Strategy {

    private $nfl;

    public function __construct(NFL5 $nfl) {
        $this->nfl = $nfl;
    }

    public function apply($schedule) {

        $teams = $this->nfl->getTeamLookupTable();

        foreach ($schedule as $week => $games) {
            foreach ($games as $idx => $g) {
                if ($teams[$g['hteamid']]['divisionid'] == $teams[$g['ateamid']]['divisionid']) {
                    $schedule[$week][$idx]['c'] *= .5;
                }
            }
        }

        return $schedule;
    }
}