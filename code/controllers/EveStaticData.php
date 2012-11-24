<?php

/* a static route and controller for exposing static data in json format */


class EveStaticData extends Page_Controller
{
    static $URLSegment = 'eveStaticData';

    public function index()
    {
        return $this->httpError(404);
    }

    /*
     * not really happy with the output format, but its for the autosuggest.. so
     */

    public function solarSystems($request)
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
}
