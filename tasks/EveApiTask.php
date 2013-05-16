<?php
class QueueEveApiJobTask extends BuildTask
{
    protected $description = 'Queue the Security Group Membership and Character Cache Job';

    public function run($request) {
        $job = new EveApiJob();
        singleton('QueuedJobService')->queueJob($job);
    }
}


class EveApiJob extends AbstractQueuedJob
{
	public function __construct()
    {
        // nginx caching is now 12H, fine to run this often
        $this->repeat = 1200;
	}

    private $members = array();
    private $debug = false;

	public function getTitle()
    {
		return "Scheduled Job to update Security Group Membership and Character Cache from Eve API";
	}

    public function getJobType()
    {
        return QueuedJob::LARGE;
    }

    public function setup()
    {
       $members = Member::get('Member');
       foreach($members as $m) {
            $this->members[] = $m->ID;
       }
       $this->totalSteps = count($this->members);
    }

	public function process()
    {
        $memberID = array_shift($this->members);
        $m = Member::get()->byID($memberID);

        if($m) {
            if($this->debug) printf("processing: %s\n", $m->FirstName);

            if($old = EveMemberCharacterCache::get('EveMemberCharacterCache', sprintf("MemberID = %d", $m->ID))) {
                foreach($old as $o) {
                    $o->delete();
                }
            }
            try {
                if($apis = $m->ApiKeys()) {
                    foreach($apis as $a) {
                        if(!$a->isValid()) {
                            foreach($a->APIErrors() as $e) {
                                if($e->Reason == 'Key has expired. Contact key owner for access renewal.'
                                || $e->Reason == 'Authentication failure.') {
                                    $a->delete();
                                }
                            }
                            continue;
                        }
                        foreach($a->Characters() as $c) {
                            //incase they have multiple apis for the same account.. people is tards
                            if(!EveMemberCharacterCache::get_one('EveMemberCharacterCache', sprintf("MemberID = %d AND CharacterID = %d", $m->ID, $c['characterID']))) {
                                $cache = new EveMemberCharacterCache();
                                $cache->CharacterName = $c['name'];
                                $cache->CharacterID   = $c['characterID'];
                                $cache->CorporationName = $c['corporationName'];
                                $cache->CorporationID   = $c['corporationID'];
                                $cache->MemberID      = $m->ID;
                                $cache->EveApiID      = $a->ID;

                                $cache->write();
                            }
                            if($this->debug) printf(" - %s\n", $c['name']);

                            if(strtolower($m->Nickname) == strtolower($c['name']) || $m->CharacterID == $c['characterID'] || $m->CharacterID == 0) {
                                $m->CharacterID = $c['characterID'];
                                $m->FirstName   = $c['name'];
                                $m->write();
                            }
                        }
                    }
                }
                // this seems kinda dumb, might move it into ^
                $m->UpdateGroupsFromAPI();
            } catch(Exception $e) {
                printf("%s\n", $e->Message);
            }
        }

		$this->currentStep++;

        if(!count($this->members)) {

    		if($this->repeat) {
    	    	$job = new EveApiJob();
    			if(!$this->debug) singleton('QueuedJobService')->queueJob($job, date('Y-m-d H:i:s', time() + $this->repeat));
        	}

    		$this->isComplete = true;
        }
	}
}
