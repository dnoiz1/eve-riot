<?php

class EvePosTimerPage extends Page {}

class EvePosTimerPage_Controller extends Page_Controller {

    function Timers()
    {
        return EvePosTimer::get('EvePosTimer', 'TimerEnds > NOW()');
    }

    function PastTimers()
    {
        return EvePosTimer::get('EvePosTimer', 'TimerEnds < NOW()');
    }
}
