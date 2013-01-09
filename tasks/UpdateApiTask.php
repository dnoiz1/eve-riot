<?php
/*
 DEPRECATED - Eve Update Task

class UpdateApiTask extends ScheduledTask
{
    function process() {
        printf("[-] %s\n[-] Starting API Updates\n", Date('c'));
        $members = Member::get('Member');
        foreach($members as $m) {
            printf("%s (%s)\n", $m->FirstName, $m->NickName());
            foreach($m->Characters() as $c) {
                $s = (strtolower($c['name']) == strtolower($m->NickName())) ? '-' : ' ';
                printf("  %s %s\n", $s, $c['name']);
            }
            $m->UpdateGroupsFromAPI();
        }
        printf("[-] %s\n[-] Finished API Updates\n", Date('c'));
    }
}
*/
