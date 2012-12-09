<?php

class QueueScrapePosTimersJobTask extends BuildTask
{
    protected $description = 'Queue the Scrape Pos Timers Job';

    public function run($request) {
        $job = new ScrapePosTimersJob();
        singleton('QueuedJobService')->queueJob($job);
    }
}


class ScrapePosTimersJob extends AbstractQueuedJob
{
	public function __construct()
    {
        $this->repeat = 3600;
	}

    private $timers = array();

	public function getTitle()
    {
		return "Scheduled import of POS Timers";
	}

    public function getJobType()
    {
        return QueuedJob::IMMEDIATE;
    }

    public function setup()
    {
        $c = curl_init("http://map.pleaseignore.com/timers/timer.pl");
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        $html = curl_exec($c);
        curl_close($c);

        // this is why i hate test.
        // they make me do shit like this, its not even nearly valid html.

        $fuck_you_test = '|<tr style="background-color: #(.*?);">(.*?)<td>(.*?)</td><td><a(.*?)a> (.*?)</td><td>(.*?)</td><td>(.*?)</td><td>(.*?) <td>(.*?)</td><td>(.*?)</td><sc(.*?)<td>(.*?)<td>(.*?)</td><td>(.*?)</td><td>(.*?)</td>|ims';
        preg_match_all($fuck_you_test, $html, $m);

        for($z=0;$z<count($m);$z++) {
            for($x=0;$x<count($m[$z]);$x++) {
                $this->timers[$x][$z] = $m[$z][$x];
            }
        }

        $this->totalSteps = count($this->timers);
    }

	public function process()
    {
        $item = array_shift($this->timers);

        if($system = mapSolarSystems::get_one('mapSolarSystems', sprintf("solarSystemName = '%s'", Convert::raw2sql(trim($item[5]))))) {
            $dt = strtotime($item[13]);
            $p = $item[6];
            $m = $item[7];

            $friendly = ($item[1] == 'c88') ? 'No' : 'Yes';

            if(strpos(strtolower($item[8]), 'tower') !== false) {
                $type = 'Tower';
            } elseif(strpos(strtolower($item[8]), 'poco') !== false) {
                $type = 'PoCo';
            } else {
                $type = $item[8];
            }

            $owner = trim($item[14]);

            switch($item[10]) {
                case 'Shield':
                    $timer = 'Shield';
                    break;
                case 'Armor':
                    $timer =  'Final';
                    break;
                default:
                    $timer = 'None';
            }

//            $posTimer = EvePosTimer::get_one('EvePosTimer', sprintf("TimerEnds > NOW() AND TargetSolarSystem = %s AND Planet = %d AND Moon %d AND Timer = %s", $system->solarSystemID, (int)$p, (int)$m, $timer));
            $posTimer = EvePosTimer::get_one('EvePosTimer', sprintf("TimerEnds = FROM_UNIXTIME(%d) AND TargetSolarSystem = %d", $dt, $system->solarSystemID));

            if(!$posTimer) {
                $pt = new EvePosTimer();
                $pt->TimerEnds = $dt;
                $pt->TargetSolarSystem = $system->solarSystemID;
                $pt->Planet = $p;
                $pt->Moon = $m;
                $pt->Friendly = $friendly;
                $pt->Defended = 'N/A';
                $pt->Timer = $timer;
                $pt->Owner = $owner;
                $pt->Type = $type;
                $pt->write();
            }
        }

		$this->currentStep++;

        if(!count($this->timers)) {

    		if($this->repeat) {
    	    	$job = new ScrapePosTimersJob();
    			singleton('QueuedJobService')->queueJob($job, date('Y-m-d H:i:s', time() + $this->repeat));
        	}

    		$this->isComplete = true;
        }
	}
}
