<?php

class EvePage extends DataExtension
{
    private static $db = array(
        "CanViewStanding"      => 'Boolean',
        "CanViewMinStanding"   => 'Int'
    );

    public function canView($member = null)
    {
        $member = Member::CurrentUser();

        if($this->owner->CanViewStanding && $member) {
            return ($member->Standing() >= $this->owner->CanViewMinStanding);
        } elseif($this->owner->CanViewStanding) {
            return false;
        }
    }

    public function updateSettingsFields(FieldList $fields)
    {
        //$tab = $fields->findOrMakeTab('Root.Settings');
        $fields->addFieldsToTab('Root.Settings', FieldList::create(
            CheckBoxField::create('CanViewStanding', 'Use Viewer Minimum Standing'),
            //really should create a standing field with colors and niceness
            DropDownField::create('CanViewMinStanding', 'Viewer Min Required Standing', array('-10','-5','-2.5','0','2.5','5','10'))
        ));
        return $fields;
    }
}

class EvePage_controller extends Extension
{
    public function onAfterInit()
    {
        Requirements::JavaScript('eacc/thirdparty/jquery.countdown.min.js');
        Requirements::CSS('eacc/thirdparty/scrollbars/jquery.scrollbars.min.css');
        Requirements::javascript('eacc/thirdparty/scrollbars/jquery.scrollbars.min.js');

        Requirements::CustomScript(<<<JS
            $(function(){
                $("*").scrollbars();
                $('.countdown').each(function(k,v){
                    ts = parseInt($(this).text());
                    $(this).countdown({
                        until: new Date(ts * 1000),
                        compact: true,
                        description: ''
                    });
                });
            });
JS
        );
    }

    public function NextOpTimer()
    {
        $m = Member::CurrentUser();
        if(!$m) return false;

        if($m->Standing() >= 5 && $this->owner->current_stage() == 'Live') {
            return EveTimerPage::get_one('EveTimerPage', "`SiteTree_Live`.`UrlSegment` = 'op-timers'")->NextTimer();
        }
    }
}
