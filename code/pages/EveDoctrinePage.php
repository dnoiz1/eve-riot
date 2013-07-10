<?php

class EveDoctrinePage extends Page
{
    public $doctrine;
    public $doctrineship;

    function Doctrines()
    {
        return EveDoctrine::get()->Filter(array('Hidden' => 0, 'EveDoctrinePageID' => $this->ID));
    }

    function Doctrine()
    {
        return $this->doctrine;
    }

    function DoctrineShip()
    {
        return $this->doctrineship;
    }

    function Children()
    {
        return $this->Doctrines();
    }
}

class EveDoctrinePage_controller extends Page_controller
{

    function handleAction($request, $action)
    {
        if($a = $request->param('Action')) {
            //nasty :(
            $sql = "REPLACE(
                      REPLACE (
                            REPLACE(LOWER(TRIM(`Title`)), ' ', '-'),
                        '/', '-'),
                    '.', '-') = '%s' AND Hidden = 0";

            $this->doctrine = EveDoctrine::get_one('EveDoctrine', sprintf($sql, Convert::raw2sql($a)));
            if(!$this->doctrine) return $this->httpError(404);

            if($id = $request->param('ID')) {
                $this->doctrineship = $this->doctrine->EveDoctrineShip(sprintf("EveDoctrineShip.ID  = '%d'", Convert::raw2sql($id)));
                if(!$this->doctrineship->Count()) return $this->httpError(404);
                $this->doctrineship = $this->doctrineship->First();

                return $this->renderWith(array('EveDoctrinePage_doctrineship', 'Page'), $this->doctrineship);

            }

            return $this->renderWith(array('EveDoctrinePage_doctrine', 'Page'), array(
                'Title' => $this->doctrine->Title,
                'Content' => $this->doctrine->Description
            ));
        }

        return parent::handleAction($request, $action);
    }

    function Doctrine()
    {
        return $this->doctrine;
    }

    function Fitting()
    {
        return $this->doctrineship->Fitting();
    }
}
