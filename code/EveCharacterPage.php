<?php

class EveCharacterPage extends Page
{
}

class EveCharacterPage_controller extends Page_controller
{
    public $char;

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

        if(!$m->Character($charid) || !($m->inGroup('directors') || $m->inGroup('administrators'))) {
            $this->char = new EveCharacter($m->CharacterID);
        } else {
            $char = new EveCharacter($charid);
            if(!$char) $this->httpError(404);
            $this->char = $char;
        }

        $this->Title = sprintf("%s: %s", $this->Title, $this->Character()->Name());

        return $this;
    }

    public function CharacterSelector()
    {
        $m = Member::currentUser();
        if(!$m) return false;

        $chars = array();

        foreach($m->Characters() as $c) {
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
