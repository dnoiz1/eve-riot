<?php

class EvePage extends DataExtension
{
    function __construct()
    {
        parent::__construct();
        Requirements::JavaScript('eacc/thirdparty/jquery.countdown.min.js');
        Requirements::CustomScript(<<<JS
            $(function(){
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

    function NextOpTimer()
    {
        $m = Member::CurrentUser();
        if(!$m) return false;

        if($m->Standing() >= 5 && $this->owner->current_stage() == 'Live') {
            return EveTimerPage::get_one('EveTimerPage', "`SiteTree_Live`.`UrlSegment` = 'op-timers'")->NextTimer();
        }
    }
}
