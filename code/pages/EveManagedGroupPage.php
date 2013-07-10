<?php

class EveManagedGroupsPage extends Page
{
    public function Children()
    {
        // groups that can be managed
        if($m = Member::CurrentUser()) {
            if(Permission::check('ADMIN')) {
                return EveManagedGroup::get();
            }
            return EveManagedGroup::get()->filter('ManagersID', $m->Groups()->getIDList());
        }
        return parent::Children();
    }
}

class EveManagedGroupsPage_controller extends Page_controller
{
    //static $allowed_actions = array('manage');
    static $url_handlers = array(
        'manage/$ID' => 'manage'
    );

    public $Group;

    function init()
    {
        Requirements::CSS('eacc/thirdparty/datatables/datatables.css');
        Requirements::CustomCSS(<<<CSS
            .table td { vertical-align: middle !important; }
CSS
        );
        Requirements::JavaScript('eacc/thirdparty/datatables/jquery.dataTables.min.js');


        if($this->Children()) {
            $left_grid  = 4;
            $right_grid = 5;
        } else {
            $left_grid  = 6;
            $right_grid = 6;
        }

        Requirements::CustomScript(<<<JS
            $(function(){
                $('#ToggleCompletedApplications').bind('click', function(){
                    var e  = $(this),
                        ca = $('#CompletedApplications');
                    if(ca.is(':visible')) {
                        e.text('See Previous Applications');
                    } else {
                        e.text('Hide Previous Applications');
                    }
                    $('#CompletedApplications').slideToggle();
                });

                $('.data-table').each(function(i,e){
                    if($(e).find('tr td').length > 1) {
                        $(e).dataTable({
                            "sDom": "<'row'<'span{$left_grid}'><'span{$right_grid}'f>r>t<'row'<'span{$left_grid}'i><'span{$right_grid}'p>>",
                            "sWrapper": "dataTables_wrapper form-inline",
                            "aaSorting": []
                        });
                    }
                });

            });
JS
        );

        return parent::init();
    }

    function manage(SS_HTTPRequest $request)
    {
        $id = $request->param('ID');

        $group = EveManagedGroup::get()->byID($id);

        if(!$group) {
            return $this->httpError(404);
        }

        if(!$group->canManage()) {
            return $this->httpError(403);
        }

        $this->Group = $group;

        $this->Title = sprintf("Manage Group: %s", $group->Title);

        return $this->renderWith('Page', array(
            'Layout' => $this->renderWith('EveManagedGroupsPage_manage'),
//            'ManageForm' => $this->ManageForm() 
        ));
    }

    function AvailableGroups()
    {
        if($m = Member::CurrentUser()) {
            $member_groups = $m->Groups()->getIDList();
            $pending_applications = $this->PendingApplications();

            $results = EveManagedGroup::get()
                    ->filter(array(
                        'GroupID:not'   => $member_groups,
                        'ID:not'        => $pending_applications->column('EveManagedGroupID')
                    ))
                    ->exclude('MinStanding:LessThan', $m->Standing());
            return $results;
        }
    }

    function PendingApplications()
    {
        if($m = Member::CurrentUser()) {
            return EveGroupApplication::get()
                    ->filter('MemberID', $m->ID)
                    ->exclude('Completed',  true);
        }
    }

    function CompletedApplications()
    {
        if($m = Member::CurrentUser()) {
            return EveGroupApplication::get()
                ->filter('MemberID', $m->ID)
                ->exclude('Completed', false)
                ->sort('ID', 'DESC');

        }
    }

    function ApplicationForm()
    {

        Requirements::customScript(<<<JS
            $(function(){
                $('.group-application').bind('click', function(){
                    var e = $(this);
                    $('#BootstrapForm_ApplicationForm_EveManagedGroupID').val(e.attr('data-group-id'));
                    $('#BootstrapForm_ApplicationForm_Title').html(e.attr('data-group-title'));
                    $('#BootstrapForm_ApplicationForm_Description').html(e.attr('data-group-description'));

                    // open the form
                    $('#ApplicationFormModal').modal();
                });
            });
JS
        );

        return BootstrapForm::create(
            $this,
            'ApplicationForm',
            FieldList::create(
                HiddenField::create('EveManagedGroupID'),
                ReadOnlyField::create('Title', 'You are applying to:'),
                ReadOnlyField::create('Description', 'Description:'),
                TextAreaField::create('Message', 'Application Notes')
            ),
            FieldList::create(
                FormAction::create('apply', 'Apply')
            )
        );

    }

    function apply($data, $form)
    {
        if(!array_key_exists('EveManagedGroupID', $data)
            || !array_key_exists('Message', $data)) {
                return $this->httpError(400);
        }

        if($m = Member::CurrentUser()) {
           $group = EveManagedGroup::get()->filter('RequiresApproval', true)
                        ->exclude(array(
                            'MinStanding:LessThan' => $m->Standing(),
                        ))->byID($data['EveManagedGroupID']);

           if($group) {
                $outstanding_applications = EveGroupApplication::get()->filter(array(
                    'EveManagedGroupID' => $group->ID,
                    'MemberID'          => $m->ID,
                    'Completed'         => false
                ));

                if($outstanding_applications->Count() == 0 && !$m->inGroup($group->GroupID)) {
                    // looks good
                    $application = EveGroupApplication::create();
                    $application->MemberID = $m->ID;
                    $application->EveManagedGroupID = $group->ID;
                    $application->Message = $data['Message'];
                    $application->write();

                    $this->setMessage('success', sprintf('You have applied to join the group "%s"', $group->Title), 'Sweet!');
                    if(Director::is_ajax()) return 'Success';
                }
            }
        }

        if(Director::is_ajax()) {
            return $this->httpError(403);
        }
        return $this->redirectBack();
    }

    function JoinPartForm()
    {
        /* should probably move into bootstrap_forms
         * module as a form action
         */
        Requirements::JavaScript('eacc/thirdparty/bootbox.min.js');

        Requirements::customScript(<<<JS
            $(function(){
                $('.group-join-part').bind('click', function(){

                    var e       = $(this),
                        action  = e.attr('data-group-action'),
                        group   = e.attr('data-group-title');

                    $('#Form_JoinPartForm_Action').val(action);
                    $('#Form_JoinPartForm_EveManagedGroupID').val(e.attr('data-group-id'));

                    if(action == 'part') {
                        action = 'leave';
                    } else if(action == 'withdraw') {
                        action = 'withdraw your application from';
                    }

                    bootbox.confirm("You are about to " + action + ' the group "' + group + '"', function(result){
                        if(result) $('#Form_JoinPartForm').submit();
                    });

                });
            });
JS
        );

        return Form::create(
            $this,
            'JoinPartForm',
            FieldList::create(
                HiddenField::create('Action'),
                HiddenField::create('EveManagedGroupID')
            ),
            FieldList::create(
                FormAction::create('joinpart', 'JoinPart')
            )
        )->addExtraClass('hidden');
    }

    function joinpart($data, $form)
    {
        if(!array_key_exists('EveManagedGroupID', $data)
            || !array_key_exists('Action', $data)) {
                return $this->httpError(400);
        }

        if($m = Member::CurrentUser()) {
            switch($data['Action']) {
                case 'withdraw':
                    $group = $this->Withdraw($data['EveManagedGroupID'], $m);
                    if($group) {
                        $this->setMessage('success', sprintf('You have withdrawn your application from "%s"', $group), 'Oh.');
                    }
                    break;
                case 'join':
                    $group = $this->Join($data['EveManagedGroupID'], $m);
                    if($group) {
                        $this->setMessage('success', sprintf('You have joined the group "%s"', $group), 'Awesome!');
                    }
                    break;
                case 'part':
                    $group = $this->Part($data['EveManagedGroupID'], $m);
                    if($group) {
                        $this->setMessage('success', sprintf('You have left the group "%s"', $group), 'Awww.');
                    }
                    break;
            }
        }

        if(!$group) {
            $this->setMessage('error', "Can't see shit captain!", 'Deep Space System Report:');
        }
        return $this->redirectBack();
    }

    function Withdraw($application_id, $member) {
        $application = EveGroupApplication::get()->filter(array(
            'MemberID' => $member->ID,
            'Completed' => false
        ))->byID($application_id);

        if($application) {
            $title = $application->EveManagedGroup()->Title;
            $application->Deny();
            return $title;
        }
    }

    function Join($managed_group_id, $member)
    {
        $managed_group = EveManagedGroup::get()->filter(array(
                'GroupID:not'   => $member->Groups()->getIDList(),
                'RequiresApproval' => false
            ))->exclude('MinStanding:LessThan', $member->Standing())
            ->byID($managed_group_id);

        /* probably need something here about users
         * being banned from certain groups or something
         */

        if($managed_group) {
            return $managed_group->addMember($member);
        }
    }

    function Part($managed_group_id, $member)
    {
        $managed_group = EveManagedGroup::get()->byID($managed_group_id);
        if($managed_group) {
            return $managed_group->removeMember($member);
        }
    }

    /* should be something more global, multiple message, move later */

    function Message()
    {
        $message = Session::get(sprintf("%s.%d.%s", $this->ClassName, $this->ID, "Message"));
        Session::clear(sprintf("%s.%d.%s", $this->ClassName, $this->ID, "Message"));
        if($message) {
            return ArrayData::create($message);
        }
    }

    function setMessage($style, $message, $title)
    {
        Session::set(sprintf("%s.%d.%s", $this->ClassName, $this->ID, "Message"), array(
            'Style'   => $style,
            'Message' => $message,
            'Title'   => $title
        ));
        Session::save();
    }

    function ManageForm()
    {
        Requirements::JavaScript('eacc/thirdparty/bootbox.min.js');
        Requirements::CustomScript(<<<JS
            $(function(){
                $('.group-approve-deny').bind('click', function(){

                    var e       = $(this),
                        action  = e.attr('data-group-action'),
                        group   = e.attr('data-group-title'),
                        user    = e.attr('data-member-name');

                    $('#Form_ManageForm_Action').val(action);
                    $('#Form_ManageForm_EveManagedGroupID').val(e.attr('data-group-id'));
                    $('#Form_ManageForm_MemberID').val(e.attr('data-member-id'));

                    msg = '';

                    if(action == 'approve') {
                        msg = 'approve ' + user + ' to join "' + group + '"';
                    } else if(action == 'deny') {
                        msg = 'deny ' + user + ' from joining "' + group + '"';
                    } else if(action == 'kick') {
                        msg = 'kick ' + user + ' from "' + group + '"';
                    }

                    bootbox.confirm("You are about to " + msg, function(result){
                        if(result) $('#Form_ManageForm').submit();
                    });
                });
            });
JS
        );

        return Form::create(
            $this,
            'ManageForm',
            FieldList::create(
                HiddenField::create('EveManagedGroupID'),
                HiddenField::create('MemberID'),
                HiddenField::create('Action')
            ),
            FieldList::create(
                FormAction::create('managegroup', 'ManageGroup')
            )
        )->addExtraClass('hidden');
    }

    function managegroup($data, $form)
    {
        $required = array('EveManagedGroupID', 'MemberID', 'Action');

        foreach($required as $r) {
            if(!array_key_exists($r, $data)) return $this->httpError(400);
        }

        $success = false;

        if($m = Member::CurrentUser()) {
            $managed_group = EveManagedGroup::get()->byID($data['EveManagedGroupID']);
            $member = Member::get()->byID($data['MemberID']);

            if($managed_group && $member) {

                if($managed_group->canManage()) {

                    switch($data['Action']) {
                        case 'kick':
                            if($managed_group->removeMember($member)) {
                                $success = true;
                                $this->setMessage('success', sprintf('%s has been kicked from "%s"', $member->FirstName, $managed_group->Title), "Punt!");
                            }
                            break;
                        case 'approve':
                        case 'deny':
                            $application = EveGroupApplication::get()->filter(array(
                                'EveManagedGroupID'    => $managed_group->ID,
                                'MemberID'             => $member->ID,
                                'Completed'            => false
                            ));

                            if($application->Count() == 1) {
                                $success = true;

                                if($data['Action'] == 'approve') {
                                    $application->First()->Approve();
                                    $this->setMessage('success', sprintf('%s has been added to "%s"', $member->FirstName, $managed_group->Title), "Alright!");
                                } else {
                                    $application->First()->Deny();
                                    $this->setMessage('success', sprintf('%s has been denied entry to "%s"', $member->FirstName, $managed_group->Title), "Not Today Pal!");
                                }
                            }
                            break;
                    }
                }
            }
        }
        if(!$success) {
            $this->setMessage('error', "woah.. woah.. slow down kiddo.", "Woah, woah woah.");
        }

        return $this->redirectBack();
    }
}
