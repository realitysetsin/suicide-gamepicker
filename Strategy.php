<?php

interface Strategy {
    public function __construct(NFL5 $nfl);
    public function apply($schedule);
}
