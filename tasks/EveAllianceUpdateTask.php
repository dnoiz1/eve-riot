<?php
class QueueEveAllianceUpdateJobTask extends BuildTask
{
    protected $description = 'Queue the Alliance corp memberships update job';

    public function run($request) {
        $job = new EveAllianceUpdateJob();
        singleton('QueuedJobService')->queueJob($job);
    }
}


class EveAllianceUpdateJob extends AbstractQueuedJob
{
	public function __construct()
    {
        $this->repeat = 3600 * 12;
	}

    private $alliances    = array();
    private $corporations = array();
    private $allianceID   = 0;
    public  $debug        = false;
    //public  $debug        = true;

	public function getTitle()
    {
		return "Scheduled Job to update Alliance corp memberships";
	}

    public function getJobType()
    {
        return QueuedJob::QUEUED;
    }

    public function setup()
    {
        $total = 0;

        $alliances = EveAlliance::get('EveAlliance');

        foreach($alliances as $a) {
            $total++;
            $corps = $a->MemberCorps();
            $this->alliances[$a->ID] = array_values($corps);
            $total += count($corps);
        }
        $this->totalSteps = $total;
    }

	public function process()
    {
        if(count($this->corporations) == 0) {
            //note: allianceID is EveAlliance.ID not EveAlliance.AllianceID
            $alliances = $this->alliances;
            $this->allianceID = array_shift(array_keys($alliances));
            unset($this->alliances[$this->allianceID]);
            $this->corporations = array_shift($alliances);

            $alliance = EveAlliance::get_by_id('EveAlliance', $this->allianceID);

            if($this->debug) printf(" - %s <%s>\n", $alliance->AllianceName, $alliance->Ticker);
            /* create Alliance Group here if needed */


            /* end create */

            // remove groups for corps that are no longer in alliance
            $corps = EveCorp::get('EveCorp', sprintf("CorpID NOT IN '%s' AND ApiManaged = 0", implode($this->corporations, "','")));
            foreach($corps as $k => $c) {
                if($c->Group()->ParentID == $alliance->Group()->ID) {
                    $c->Group()->delete();
                }
            }
        } else {
            $alliance = EveAlliance::get_by_id('EveAlliance', $this->allianceID);
            $corp = array_shift($this->corporations);
            if(!$c = EveCorp::get_one('EveCorp', sprintf("CorpID = '%d'", $corp))) {
                $c = new EveCorp();
                $c->CorpID = $corp;
                $c->EveAllianceID = $this->allianceID;
                $c->write();
            }

            if($this->debug) printf(" -- %s [%s]\n", $c->CorpName, $c->Ticker);

            if(!$g = Group::get_one('Group', sprintf("ID = '%s' AND ParentID = %d", $c->GroupID, $alliance->Group()->ID))) {
                $g = new Group();
                $g->ParentID = $alliance->Group()->ID;
                $g->Title    = $c->CorpName;
                $g->Code     = $c->Ticker;
                $g->Ticker   = $c->Ticker;
                $g->write();

            }

            //find or create a director group subgroup for this corp
            if(!$dg = Group::get_one('Group', sprintf("Code = 'directors' AND ParentID = '%d'", $g->ID))) {
                $dg = new Group();
                $dg->ParentID = $g->ID;
                $dg->Title    = sprintf('Directors [%s]', $c->Ticker);
                $dg->Code     = 'directors';
                $dg->Ticker   = $c->Ticker;
                $dg->write();
            }

            if($c->GroupID != $g->ID) {
                $c->GroupID = $g->ID;
                $c->write();
            }
        }

		$this->currentStep++;

        if(!count($this->alliances) && !count($this->corporations)) {

    		if($this->repeat) {
    	    	$job = new EveAllianceUpdateJob();
    			if(!$this->debug) singleton('QueuedJobService')->queueJob($job, date('Y-m-d H:i:s', time() + $this->repeat));
        	}

    		$this->isComplete = true;
        }
	}
}
