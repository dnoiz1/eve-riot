<?php

class EvePlanetaryInteraction extends Page
{
}

class EvePlanetaryInteraction_controller extends Page_controller
{
    function Menu($level = 1)
    {
        if($level == 2) return false;
        return parent::Menu($level);
    }
}
