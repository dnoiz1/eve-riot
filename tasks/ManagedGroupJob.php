<?php

class QueueEveManagedGroupJobTask extends BuildTask
{
    protected $description = 'Queue the Managed Group Job';

    public function run($request) {
        $job = new EveManagedGroupJob();
        singleton('QueuedJobService')->queueJob($job);
    }
}


class EveManagedGroupJob extends AbstractQueuedJob
{
	public function __construct()
    {
        $this->repeat = 3600;
	}

    private $managed_groups = array();

	public function getTitle()
    {
		return "Scheduled Updated of Managed Group Membership";
	}

    public function getJobType()
    {
        return QueuedJob::IMMEDIATE;
    }

    public function setup()
    {
        $this->managed_groups = EveManagedGroup::get()->getIDList();
        $this->totalSteps = count($this->managed_groups);
    }

	public function process()
    {

        $group_id = array_shift($this->managed_groups);
        $managed_group = EveManagedGroup::get()->byID($group_id);
        if($managed_group) {
            $managed_group->UpdateGroupMembers();
        }

		$this->currentStep++;

        if(!count($this->managed_groups)) {

    		if($this->repeat) {
    	    	$job = new EveManagedGroupJob();
    			singleton('QueuedJobService')->queueJob($job, date('Y-m-d H:i:s', time() + $this->repeat));
        	}

    		$this->isComplete = true;
        }
	}
}
