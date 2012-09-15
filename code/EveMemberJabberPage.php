<?php

class EveMemberJabberPage extends Page
{
}

class EveMemberJabberPage_controller extends Page_controller
{

    function JabberForm()
    {
        $m = Member::currentUser();
        if(!$m) return new Form();

        $f = new FieldSet(
            new TextField('JabberUser', 'Jabber Login', $m->getField('JabberUser')),
            new PasswordField('JabberPasswd', 'Jabber Password', $m->getField('JabberPasswd'))
        );

        $a = new FieldSet(
            new FormAction('JabberSave', 'Submit'),
            new FormAction('JabberDelete', 'Delete')
        );

        $v = new RequiredFields('JabberUser', 'JabberPasswd');

        $form = new Form($this, 'JabberForm', $f, $a, $v);

        if(Session::get('Eve.Profile.Jabber.Updated')) {
            $form->setMessage('Jabber Login Saved', 'good');
            Session::clear('Eve.Profile.Jabber.Updated');
        }

        return $form;
    }

    function JabberSave($data, $form)
    {
        $m = Member::currentUser();
        if(!$m) return Director::redirectBack();

        $m->setField('JabberUser', $data['JabberUser']);
        $m->setField('JabberPasswd', $data['JabberPasswd']);

        $m->write();

        Session::set('Eve.Profile.Jabber.Updated', true);

        Director::redirectBack();
    }

    function JabberDelete()
    {
        $m = Member::currentUser();
        if(!$m) return Director::redirectBack();

        $m->setField('JabberUser', '');
        $m->setField('JabberPasswd', '');

        $m->write();

        Session::set('Eve.Profile.Jabber.Updated', true);
        Director::redirectBack();

    }
}


