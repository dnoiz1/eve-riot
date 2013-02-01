<?php
class HomePage extends Page {}
class HomePage_controller extends Page_controller
{
    public function index()
    {
        Requirements::ThemedCss('home');
        return $this;
    }
}
