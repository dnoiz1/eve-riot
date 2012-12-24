<?php

class EveDoctrinePage extends Page
{
    public $doctrine;
    public $doctrineship;

    function Doctrines()
    {
        return EveDoctrine::get('EveDoctrine', "Hidden = 0");
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
    function handleAction($request)
    {
        if($a = $request->param('Action')) {
            //nasty :(
            $sql = "REPLACE(
                      REPLACE (
                            REPLACE(LOWER(TRIM(`Title`)), ' ', '-'),
                        '/', '-'),
                    '.', '-') = '%s' AND Hidden = 0";
            $this->doctrine = EveDoctrine::get_one('EveDoctrine', sprintf($sql, Convert::raw2sql($a)));
            //$this->doctrine = EveDoctrine::get_by_id(Convert);
            if(!$this->doctrine) return $this->httpError(404);

            if($id = $request->param('ID')) {
                /*
                $this->doctrineship = EveDoctrineShip::get_one('EveDoctrineShip',
                    sprintf("`ID` = '%d' AND `EveDoctrineID` = '%d'",
                    Convert::raw2sql($id), Convert::raw2sql($this->doctrine->ID))
                );
                */

                // not very nice, but many to many's are messy to messy
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

        return parent::handleAction($request);
    }

    function Breadcrumbs()
    {
        $b = parent::Breadcrumbs();
        if($this->doctrine) {
            $bs = explode(SiteTree::$breadcrumbs_delimiter, $b);
            $nb = count($bs);
            $bs[$nb-1] = sprintf('<a href="%s">%s</a>', $this->Link(), $bs[$nb-1]);
            $bs[$nb] = $this->doctrine->Title;
            $b = implode(SiteTree::$breadcrumbs_delimiter, $bs);
        }
        return $b;
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
