<?php
class EveApiPage extends Page
{
}

class EveApiPage_controller extends Page_controller
{
    private static $allowed_actions = array(
        'APIForm',
        'delete'
    );

    function init()
    {
        Requirements::CustomScript(<<<JS
            jQuery('#showApiForm').click(function() {
            div = '#APIFormWrapper';
            if (jQuery(div).is(":hidden")) {
                jQuery(this).slideUp("slow", function(d) {
                    jQuery(d).slideDown("slow");
                }(div));
            }});
JS
        );

        return parent::init();
    }

    function ApiKeys()
    {
        return DataObject::Get('EveApi', sprintf("MemberID = '%s'", Member::CurrentUser()->ID));
    }

    function APIForm()
    {
        $f = new FieldList(
            new NumericField('KeyID'),
            new TextField('vCode')
        );

        $a = new FieldList(
            new FormAction('addAPI', 'Submit')
        );

        $v = new RequiredFields('KeyID', 'vCode');

        return new Form($this, 'APIForm', $f, $a, $v);
    }

    function addAPI($data, $form)
    {
        $api = new EveApi();
        $form->saveInto($api);
        $api->setField('MemberID', Member::currentUser()->ID);

        $api->write();
        $this->redirectBack();
    }

    function delete($params)
    {
        $id = $params->param('ID');

        $k = DataObject::get_one('EveApi', sprintf("MemberID = '%d' AND ID = '%d'", Member::CurrentUser()->ID, $id));
        if($k) $k->delete();
        $this->RedirectBack();
    }

    function updateNow()
    {
        $m = Member::CurrentUser();
        if($m) $m->UpdateGroupsFromAPI();
        $this->redirectBack();
    }

    function Groups()
    {
/*
        $groups = array('Rioters', 'Officers', 'Directors');
        $gs = array();

        $m = Member::CurrentUser();

        foreach($groups as $g) {
            if($m->inGroup($g)) $gs[] = array('Title' => $g);
        }

        return new DataObjectset($gs);
*/
        if($m = Member::CurrentUser()) {
            $exclude = EveAlliance::get()->exclude('Standing',  array('2.5', '5', '10'));
            $exclude = ($exclude->count() > 0) ? $exclude->Map('GroupID')->Keys() : array();
            return $m->Groups()->exclude('ID', $exclude)->exclude('ParentID', $exclude);
        }
        return false;

    }
}
