<?php

class findNonRegisteredMembersTask extends ScheduledTask
{
    function process() {
        printf("[-] %s\n[-] Starting to find non registered members\n", Date('c'));
        $toons = file('../mysite/tasks/memberlist.txt');

        $t2 = new DataObjectSet();

        foreach($toons as $k => $t) {
            $t2->push(new ArrayData(array(
                'Name' => strtolower(trim($t))
            )));
        }

        $members = Member::get('Member');
        foreach($members as $m) {
            printf("%s (%s)\n", $m->FirstName, $m->NickName());
            $nn = strtolower($m->NickName());

            if($e = $t2->find('Name', $nn)) {
                $t2->remove($e);
                printf("[-] Removing %s\n", $nn);
            }

            foreach($m->Characters() as $c) {
                $n = strtolower($c['name']);
                $s = ($n == $nn) ? '-' : ' ';
                printf("  %s %s\n", $s, $c['name']);

                if($f = $t2->find('Name', $n)) {
                    $t2->remove($f);
                    printf("[-] Removing %s\n", $n);
                }
            }
        }

        printf("\n[-] Results\n");

        foreach($t2 as $t) {
            printf("%s\n", $t->Name);
        }

        printf("[-] %s\n[-] Finished finding non registered members\n", Date('c'));
    }
}
