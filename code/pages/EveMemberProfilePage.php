<?php
class EveProfilePage extends MemberProfilePage implements PermissionProvider {
    function Children()
    {

        if(!Member::currentUser()) {
            return new DataObjectSet();
        }

        return parent::Children();
    }
}
class EveProfilePage_Controller extends MemberProfilePage_Controller {
    /**
     * Allows users to edit their profile if they are in at least one of the
     * groups this page is restricted to, and editing isn't disabled.
     *
     * If editing is disabled, but the current user can add users, then they
     * are redirected to the add user page.
     *
     * @return array
     */
    protected function indexProfile() {
        if(!$this->AllowProfileEditing) {
            if($this->AllowAdding && Permission::check('CREATE_OTHER_USERS')) {
                return $this->redirect($this->Link('add'));
            }

            return Security::permissionFailure($this, _t(
                'MemberProfiles.CANNOTEDIT',
                'You cannot edit your profile via this page.'
            ));
        }

        $member = Member::currentUser();

        foreach($this->Groups() as $group) {
            if(!$member->inGroup($group)) {
                return Security::permissionFailure($this);
            }
        }

        $form = $this->ProfileForm();
        $form->loadDataFrom($member);

        if($password = $form->Fields()->fieldByName('Password')) {
            $password->setCanBeEmpty(false);
            $password->setValue(null);
            $password->setCanBeEmpty(true);
        }

        $chars = array();
        if($keys = $member->ApiKeys()) {
            foreach($keys as $k) {
                foreach($k->Characters() as $c) {
                    $chars[$c['name']] = $c['name'];
                }
            }
        }
        if(count($chars) > 0) {
            $form->Fields()->replaceField('Nickname', new DropDownField('Nickname', 'Pilot', $chars, $member->Nickname()));
        }

        return array (
            'Title' => $this->obj('ProfileTitle'),
            'Content' => $this->obj('ProfileContent'),
            'Form'  => $form
        );
    }

    function RegisterForm()
    {
        $form = parent::RegisterForm();
        /*
        $form->Fields()->push(new DropDownField('Reason', 'Why are you Registering?',
            array(0 => 'I\'m a Rioter', 1 => 'I want to join Riot', 2 => 'I\'m a Guest')
        ));
        */
        return $form;
    }

    public function register($data, Form $form) {
        if($member = $this->addMember($form)) {
            if(!$this->RequireApproval && $this->EmailType != 'Validation' && !$this->AllowAdding) {
                $member->logIn();
            }

            if ($this->RegistrationRedirect) {
                if ($this->PostRegistrationTargetID) {
                    $this->redirect($this->PostRegistrationTarget()->Link());
                    return;
                }

                if ($sessionTarget = Session::get('MemberProfile.REDIRECT')) {
                    Session::clear('MemberProfile.REDIRECT');
                    if (Director::is_site_url($sessionTarget)) {
                        $this->redirect($sessionTarget);
                        return;
                    }
                }
            }

            return $this->redirect($this->Link('api-keys'));
            //return $this->redirect($this->Link('afterregistration'));
        } else {
            return $this->redirectBack();
        }
    }

}
