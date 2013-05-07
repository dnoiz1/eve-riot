<?php

/* a static route and controller for exposing static data in json format */


class EveStaticData extends Page_Controller
{
    //static $URLSegment = 'eveStaticData';

    private $allowed_actions = array(
        'solarSystems'
    );

    public static $url_handlers = array(
        'solarSystems/$ID' => 'solarSystems'
    );

    static function packagedSizes($hullClass)
    {
        $map = array(
            'AssaultShip'               => 2500,
            'AuditLogSecureContainer'   => 1000,
            'Battlecruiser'             => 15000,
            'Battleship'                => 50000,
            'BlackOps'                  => 50000,
            'CapitalIndustrialShip'     => 1000000,
            'Capsule'                   => 500,
            'CargoContainer'            => 1000,
            'Carrier'                   => 1000000,
            'CombatReconShip'           => 10000,
            'CommandShip'               => 15000,
            'CovertOps'                 => 2500,
            'Cruiser'                   => 10000,
            'Destroyer'                 => 5000,
            'Dreadnought'               => 1000000,
            'ElectronicAttackShips'     => 2500,
            'EliteBattleship'           => 50000,
            'Exhumer'                   => 3750,
            'ForceReconShip'            => 10000,
            'FreightContainer'          => 1000,
            'Freighter'                 => 1000000,
            'Frigate'                   => 2500,
            'HeavyAssaultShip'          => 10000,
            'HeavyInterdictors'         => 10000,
            'Industrial'                => 20000,
            'IndustrialCommandShip'     => 500000,
            'Interceptor'               => 2500,
            'Interdictor'               => 5000,
            'JumpFreighter'             => 1000000,
            'Logistics'                 => 10000,
            'Marauders'                 => 50000,
            'MiningBarge'               => 3750,
            'MissionContainer'          => 1000,
            'Rookieship'                => 2500,
            'SecureCargoContainer'      => 1000,
            'Shuttle'                   => 500,
            'StealthBomber'             => 2500,
            'StrategicCruiser'          => 5000,
            'Supercarrier'              => 1000000,
            'Titan'                     => 10000000,
            'TransportShip'             => 20000
        );
        return (array_key_exists($hullClass, $map)) ? $map[$hullClass] : 0;
    }

    public function __construct()
    {
        /* auth here prob */

        return parent::__construct();
    }

    public function index()
    {
        return $this->httpError(404);
    }

    /*
     * not really happy with the output format, but its for the autosuggest.. so
     */

    public function solarSystems(SS_HTTPRequest $request)
    {
        $name = $request->param('ID');
        //if(!$name) return $this->httpError(404);

        $results = mapSolarSystems::get('mapSolarSystems', sprintf("solarSystemName LIKE '%s%%'", Convert::raw2sql($name)), '', '', 6);

        $json = array('results' => array());

        if($results) foreach($results as $r) {
            $json['results'][] = array(
                'id' => $r->solarSystemID,
                'value' => $r->solarSystemName,
                'info'  => $r->Region()->regionName
            );
        }
        return Convert::array2json($json);
    }
/*
    public function invTypes($request)
    {
        $name = $request->param('ID');

//        $results = invTypes::get('invTypes', sprintf("`invTypes`.`typeName` LIKE %%%s%% AND `invTypes`.`published` = '1'", Convert::raw2sql($name)), '', '', 16);
        $results = invTypes::get('invTypes', sprintf("typeName LIKE '%%%s%%' AND published = 1", Convert::raw2sql($name)), 'typeName ASC', '', 8);

        $json = array('results' => array());

        if($results) foreach($results as $r) {
            //var_dump($r);
            $json['results'][] = array(
                'id'    => $r->typeID,
                'value' => $r->typeName,
                'info'  => $r->Group()->groupName
            );
        }

        return Convert::array2json($json);
    }
*/
}
