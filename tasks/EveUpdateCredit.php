<?php

class QueueEveUpdateCreditJobTask extends BuildTask
{
    protected $description = 'Queue the Credit Update from Wallet Task';

    public function run($request) {
        $job = new EveUpdateCreditJob();
        singleton('QueuedJobService')->queueJob($job);
    }
}


class EveUpdateCreditJob extends AbstractQueuedJob
{
	public function __construct()
    {
        $this->repeat = 1200;
	}

    private $credit_providers = array();

	public function getTitle()
    {
		return "Scheduled update of Eve credit from wallet API deposits";
	}

    public function getJobType()
    {
        return QueuedJob::QUEUED;
    }

    public function setup()
    {
       $providers = EveCreditProvider::get('EveCreditProvider', 'Active = 1');
       foreach($providers as $p) {
            $this->credit_providers[] = $p->ID;
       }
       $this->totalSteps = count($this->credit_providers);
    }

	public function process()
    {
        $providerID = array_shift($this->credit_providers);
        $provider = EveCreditProvider::get_by_id('EveCreditProvider', $providerID);

        if($provider && $provider->hasWalletAccess()) {
            $transactions = $provider->APITransactions();
            $transactions->sort('RefID', 'DESC');
            foreach($transactions as $t) {
               if(!EveCreditRecord::get_one('EveCreditRecord', sprintf("RefID = '%d'", $t->RefID))) {
                    $ecr = new EveCreditRecord();
                    $ecr->Amount                = $t->Amount;
                    $ecr->RefID                 = $t->RefID;
                    $ecr->Date                  = $t->Date;
                    $ecr->CharacterID           = $t->CharacterID;
                    $ecr->EveCreditProviderID   = $provider->ID;
                    $ecr->write();
                } /* else {
                    break;
                } */
            }
        }

		$this->currentStep++;

        if(!count($this->credit_providers)) {

    		if($this->repeat) {
    	    	$job = new EveUpdateCreditJob();
    			singleton('QueuedJobService')->queueJob($job, date('Y-m-d H:i:s', time() + $this->repeat));
        	}

    		$this->isComplete = true;
        }
	}
}
