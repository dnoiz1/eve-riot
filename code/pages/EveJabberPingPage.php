<?php
class EveJabberPingPage extends Page
{
    static $db = array(
        'JabberHost' => 'Varchar(255)',
        'JabberRPCHost' => 'Varchar(255)',
        'JabberRPCPort' => 'Int'
    );

    static $defaults = array(
        'JabberRPCHost' => 'localhost',
        'JabberRPCPort' => '4650'
    );

    function getCMSFields()
    {
        $f = parent::getCMSFields();
        $f->findOrMakeTab('Root.Content.Jabber', 'Jabber Settings');
        $f->addFieldsToTab('Root.Content.Jabber', new FieldSet(
            new TextField('JabberHost', 'Jabber Host (for JID)'),
            new TextField('JabberRPCHost', 'Jabber XML-RPC Host'),
            new NumericField('JabberRPCPort', 'Jabber XML-RPC Port')
        ));
        return $f;
    }
}

class EveJabberPingPage_controller extends Page_controller
{
    function JabberXMLRPC($method, $params = array())
    {
        $request = xmlrpc_encode_request($method, $params);
        $ch = curl_init();

        $headers = array(
            "Content-Type: text/xml",
            "User-Agent: Unclaimed Ping"
        );

        curl_setopt($ch, CURLOPT_URL, sprintf("http://%s:%d/RPC2", $this->JabberRPCHost, $this->JabberRPCPort));
        curl_setopt($ch, CURLOPT_PORT, $this->JabberRPCPort);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);

        $response = xmlrpc_decode_request(curl_exec($ch), $method);
        curl_close($ch);

        return $response;
    }

    function JabberPingForm()
    {

        $m = Member::currentUser();
        if(!$m) return;

/*
        if(Permission::check('ADMIN')) {
            $dst = Group::get('Group')->Map();
        }  else {
*/
            $dst = array();
            foreach($m->Groups() as $g) {
                if($g->hasPerm('JABBER') && $g->ApiManaged) {
                    // i am a bad person
                    if($g->ID != 41) {
                        if($g->Code != 'directors') {
                            $dst[$g->ID] = $g->Title;
                        }
                        if($g->Code == 'directors' && !in_array('Everyone', $dst)) {
                            $dst[0] = 'Everyone';
                            if($dg = Group::get_one('Group', "Code = 'directors' AND ParentID = 41")) {
                                $dst[$dg->ID] = $dg->Title;
                            }
                        }
                    }
                }
            }
            ksort($dst);
//        }

        $template = "Fleet Name:\n".
                    "FC:\n".
                    "Comms:\n".
                    "Ship Types: dont forget to link to doctrine\n".
                    "FormUp:\n".
                    "When:\n".
                    "Duration:\n".
                    "Reimbursable:\n".
                    "Why:";


        $f = new FieldSet(
            new DropDownField('Destination', 'Group to Ping', $dst),
            new TextAreaField('JabberMessage', 'Message', 10, 5, $template)
        );

        $a = new FieldSet(
            new FormAction('JabberPing', 'Send')
        );

        $v = new RequiredFields('JabberMessage');

        $form = new Form($this, 'JabberPingForm', $f, $a, $v);


        if(Session::get('Eve.JabberPing.Sent') && Session::get('Eve.JabberPing.Sent') != 1) {
            $form->setMessage(sprintf("Error: %s", Session::get('Eve.JabberPing.Sent')), 'bad');
        } elseif(Session::get('Eve.JabberPing.Sent')) {
            $form->setMessage('Message Sent', 'good');
        }
        Session::clear('Eve.JabberPing.Sent');

        return $form;

    }

    function JabberPing($data, $form)
    {
        $m = Member::currentUser();

        $data['Destination'] = (int)$data['Destination'];
        //check data
        $send_global = false;

        $director_groups = Group::get('Group', sprintf("Code = 'directors' AND ParentID = 41"))->Map('ID', 'ID');
        $member_groups = $m->Groups()->Map('ID', 'ID');

        if(Permission::check('ADMIN') || count(array_intersect($member_groups, $director_groups)) > 0) {
            $send_global = true;
        }

        if($data['Destination'] == 0 &&  Permission::check('JABBER') && $send_global) {
            $this->JabberSendPing($data['JabberMessage']);
            Session::set('Eve.JabberPing.Sent', true);
        } elseif($m->inGroup($data['Destination'], true) && Permission::check('JABBER') ||  Permission::check('ADMIN')) {
            $this->JabberSendPing($data['JabberMessage'], $data['Destination']);
            Session::set('Eve.JabberPing.Sent', true);
        } else {
            Session::set('Eve.JabberPing.Sent', 'You are not allowed to broadcast to that group');
        }

        return $this->redirectBack();
    }

    function JabberSendPing($message, $target = 0)
    {
        $m = Member::CurrentUser();

        $target = (int)$target;

        $message = str_replace("\r", '', "\n" . $message);

        if($target == 0) {
            $message = sprintf("%s\n\n>> Sent to All Online Users by %s [%s] at %s EVE time <<", $message, $m->Nickname, $m->Ticker(), date("d/m/Y H:i:s"));

            $params = array(
                'from' => 'trollcast@localhost',
                'to' => sprintf("%s/announce/all-hosts/online", $this->JabberHost),
                'body' => $message,
            );

            $this->JabberXMLRPC('send_message_chat', $params);
        } else {
            if($group = Group::get_by_id('Group', $target)) {

                $online_users = array();
                $connected_users = $this->JabberXMLRPC('connected_users');
                foreach($connected_users as $cu) {
                    foreach($cu as $session) {
                        foreach($session as $s) {
                            preg_match("/^(.*?)\//", $s, $s);
                            if(!in_array($s[1], $online_users)) {
                                array_push($online_users, $s[1]);
                            }
                        }
                    }
                }

                $group_members =  $group->Members()->Map('ID', 'JabberUser');

                foreach($group_members as $k => $u) {
                    $group_members[$k] = sprintf("%s@%s", $u, $this->JabberHost);
                }

                $online_group_members = array_intersect($online_users, $group_members);

                $message = sprintf("%s\n\n>> Sent to %s by %s [%s] at %s EVE time <<", $message, $group->Title, $m->Nickname, $m->Ticker(), date("d/m/Y H:i:s"));
                foreach($online_group_members as $ogm) {

                    $params = array(
                        'from' => $this->JabberHost,
                        'to'   => $ogm,
                        'body' => $message,
                    );

                    $this->JabberXMLRPC('send_message_chat', $params);
                }

            }
        }
    }
}
