<?php
/*
// debug stuff + cache building terrible script
class CharacterInfoQueryTask extends BuildTask
{
    function run($request) {
        printf("[-] %s\n[-] Starting to query cache\n", Date('c'));

        foreach(EveApi::get('EveApi') as $a) {
            foreach($a->Characters() as $c) {
                var_dump($c['characterID']);
                printf("[-] processing: %s", $c->characterName);
                $c = new EveCharacter($c['characterID'], $a);
                try{
                    $c->_characterInfo();
                } catch(Exception $e) {}
            }
        }

        printf("[-] %s\n[-] Finished\n", Date('c'));
    }
}
*/
