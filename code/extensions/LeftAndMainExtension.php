<?php

class CleanLeftAndMainDecorator extends LeftAndMainDecorator {
    function init()
    {
        CMSMenu::remove_menu_item('Help');
        return parent::init();
    }
}
