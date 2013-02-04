<?php

class EveCharacterPage extends Page
{
}

class EveCharacterPage_controller extends Page_controller
{
    public $char = false;

    public function handleAction($request)
    {
        Requirements::CustomScript(<<<JS
            jQuery('.controls #HideUntrained').change(function(){
               if(this.checked) {
                    jQuery('.not-trained').hide();
                } else {
                    jQuery('.not-trained').show();
                }
            });
            jQuery(function(){ jQuery('.controls #HideUntrained').change(); })

            jQuery('#character-selector').change(function(){
                window.location = jQuery(this).val();
            });
JS
        );

        $charid = $request->param('Action'); // ?: Session::get('CharacterID');

        $m = Member::currentUser();

        if(!$m->ApiKeys()) return $this->redirect('/profile/api-keys/');

        if(!$charid) $charid = $m->CharacterID;

//        if(!$charid || !$m->Character($charid) && !($m->inGroup('directors') || $m->inGroup('administrators'))) {
        if(!$m->Character($charid) && !Permission::check('EVE_CHAR_SHEET')) {
            try {
                $this->char = new EveCharacter($m->CharacterID);
            } catch(Exception $e) {}
        } else {
            try {
                $this->char = new EveCharacter($charid);
            } catch(Exception $e) {}
        }
        if(!$this->char) return $this->httpError(404);

        //var_dump($this->char);

        $this->Title = sprintf("%s: %s", $this->Title, $this->Character()->Name());

        return $this;
    }

    public function CharacterSelector()
    {
        $m = Member::currentUser();
        if(!$m) return false;

        $chars = array();
        $characters = $m->Characters();

        if($characters) foreach($characters as $c) {
            $chars[$this->Link($c['characterID'])] = $c['name'];
        }

        return new DropDownField('character-selector', 'Pilot', $chars, $this->Link($this->Character()->ID()));
    }

    public function SetCharacter()
    {
    }

    public function Character()
    {
        return $this->char;
    }
}
