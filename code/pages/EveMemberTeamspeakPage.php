<?php
class EveMemberTeamspeakPage extends Page {
    private static $db = array(
        'Hostname'  => 'Varchar(255)',
        'Port'      => 'Int',
        'Username'  => 'Varchar(255)',
        'Password'  => 'Varchar(255)',
        'TeamspeakGroupID'   => 'Int',
    );

    static $defaults = array(
        'Port' => '10011'
    );

    function getCMSFields()
    {
        $f = parent::getCMSFields();
        $f->findOrMakeTab('Root.Teamspeak', 'Teamspeak');
        $f->addFieldsToTab('Root.Teamspeak', new FieldList(
            new TextField('Hostname', 'Hostname'),
            new NumericField('Port', 'Port'),
            new Textfield('Username', 'ServerQuery User'),
            new PasswordField('Password', 'ServerQuery Password'),
            new NumericField('TeamspeakGroupID', 'Teamspeak Group ID')
        ));
        return $f;
    }
}

class EveMemberTeamspeakPage_controller extends Page_controller
{
    private $socket;
    private function ServerQueryEscape($text)
    {
        $text = str_replace("\t", '\t', $text);
        $text = str_replace("\v", '\v', $text);
        $text = str_replace("\r", '\r', $text);
        $text = str_replace("\n", '\n', $text);
        $text = str_replace("\f", '\f', $text);
        $text = str_replace(' ', '\s', $text);
        $text = str_replace('|', '\p', $text);
        $text = str_replace('/', '\/', $text);
        return $text;
    }

    private function ServerQueryUnescape($text)
    {
        $escapedChars = array("\t", "\v", "\r", "\n", "\f", "\s", "\p", "\/");
        $unEscapedChars = array('', '', '', '', '', ' ', '|', '/');
        $text = str_replace($escapedChars, $unEscapedChars, $text);
        return $text;
    }

    private function ServerQueryCommand($cmd, $params = false)
    {
        if(!$this->socket || !$cmd) return false;

        if(is_array($params)) {
            $param = '';
            foreach($params as $k => $v) {
                $param .= sprintf(" %s=%s", $this->ServerQueryEscape($k), $this->ServerQueryEscape($v));
            }
        } else {
            $param = ' '.$params;
        }

        fwrite($this->socket, sprintf("%s%s\n\r", $cmd, $param));
        $resp = '';
        while($d = fgets($this->socket, 4096)) {
            $resp .= $d;
            if(strpos($d, "error id") !== false && strpos($d, 'msg=') !== false) break;
        }

        $ret = array();
        foreach(explode("\n", $resp) as $v) {
            if(strlen($v) > 0) {
                $ret[] = str_replace("\r", '', $v);// = $this->ServerQueryUnescape($v);
                if(strpos($v, "error id") !== false) {
                    if(preg_match("/id=(\d+) msg=(.*?)$/ims", $v, $match) !== false) {
                        if($match[1] != 0) {
                            throw new Exception(sprintf("Error: %d ServerQuery::%s", $match[1], $this->ServerQueryUnescape($match[2])));
                        }
                    }
                }
            }
        }

        return $ret;
    }

    function TeamspeakForm()
    {
        $m = Member::currentUser();
        if(!$m) return Form::create();


        $f = FieldList::create(
            ReadOnlyField::create('sv', 'Server Address', 'ts.evetroll.com')->addExtraClass('strong'),
            ReadOnlyField::create('fn', 'Connect to Teamspeak with your name as', $m->TaggedName())->addExtraClass('strong')
        );
        if($m->TeamSpeakIdentity) {
            $f->push(ReadOnlyField::create('', 'Current Teamspeak Identity', $m->TeamSpeakIdentity)->addExtraClass('strong'));
        }

        $form = BootstrapForm::create($this, 'TeamspeakForm', $f,
            FieldList::create(
                FormAction::create('TeamspeakUpdate', 'Click here when you are connected')
                    ->addExtraClass('btn-primary pull-right')
            )
        )->addWell();

        if(($msg = Session::get('Eve.Profile.Teamspeak.Updated')) && Session::get('Eve.Profile.Teamspeak.Updated') != 1) {
            $form->setMessage(sprintf("Error: %s", $msg), 'alert-error');
            Session::clear('Eve.Profile.Teamspeak.Updated');
        } elseif(Session::get('Eve.Profile.Teamspeak.Updated')) {
            $form->setMessage('Teamspeak Roles and Identity Updated Successfully', 'alert-success');
            Session::clear('Eve.Profile.Teamspeak.Updated');
        }

        return $form;
    }

    function TeamspeakUpdate($data, $form)
    {
        $m = Member::CurrentUser();
        if(!$m) $this->redirectBack();

        try {
            $this->socket = fsockopen($this->Hostname, $this->Port, $errorno, $errorstr, 3);
            if(!$this->socket) throw new Exception(sprintf('Error: %d ServerQuery::%s', $errorno, $errorstr));

            $use = $this->ServerQueryCommand('use 1');

            $loggedin = $this->ServerQueryCommand("login", array(
               'client_login_name' => $this->Username,
               'client_login_password' => $this->Password
            ));

            $client_list = $this->ServerQueryCommand("clientlist");
            $client_list = explode("|", $client_list[0]);

            $clients = array();
            foreach($client_list as $i => $cl) {
                foreach(explode(" ", $cl) as $c) {
                    list($k, $v) = explode("=", $c);
                    $clients[$i][$k] = $this->ServerQueryUnescape($v);
                }
            }

            $client = false;
            foreach($clients as $c) {
                if(strtolower($c['client_nickname']) == strtolower($m->TaggedName())) {
                    // this is the droid we are looking for
                    $client = $c;
                }
            }

            if(!$client) throw new Exception('Make sure you are connected to TeamSpeak with the correct name');

            $client_info = $this->ServerQueryCommand('clientinfo', array(
                'clid' => $client['clid']
            ));

            $client = array();
            foreach(explode(" ", $client_info[0]) as $ci) {
                @list($k, $v) = preg_split('/=([^ ]+)/', $ci, -1, PREG_SPLIT_DELIM_CAPTURE);
                $client[$k] = $this->ServerQueryUnescape($v);
            }

            //about time, this is what we wanted.
            $identity = $client['client_unique_identifier'];
            $client_db_id = $client['client_database_id'];
            $client_server_groups = explode(",", $client['client_servergroups']);


            $server_groups = $this->ServerQueryCommand('servergrouplist');
            $server_groups = explode("|", $server_groups[0]);

            $sgroups = array();
            foreach($server_groups as $i => $sg) {
                foreach(explode(" ", $sg) as $g) {
                    list($k, $v) = explode("=", $g);
                    $sgroups[$i][$k] = $this->ServerQueryUnescape($v);
                }
            }

            $client_groups_required = array();

            foreach($sgroups as $si => $server_group) {
               if($server_group['sgid'] == $this->TeamspeakGroupID) {
                    $client_groups_required[] = $server_group['sgid'];
                    $server_group_clients = $this->ServerQueryCommand('servergroupclientlist', array('sgid' => $server_group['sgid']));
                    $server_group_clients = $server_group_clients[0];
                    if(strpos($server_group_clients, "|") !== false) {
                        $server_group_clients = explode("|", $server_group_clients);
                    } else {
                        $server_group_clients = array($server_group_clients);
                    }
                    $server_group_client_db_ids = array();
                    foreach($server_group_clients as $sgc) {
                        list($k, $cbid) = explode("=", $sgc);
                        $server_group_client_db_ids[] = $cbid;
                    }
                    if(!in_array($client_db_id, $server_group_client_db_ids)) {
                        $this->ServerQueryCommand('servergroupaddclient', array(
                            'sgid'   =>  $server_group['sgid'],
                            'cldbid' =>  $client_db_id
                        ));
                    }
                }
            }

            foreach(array_diff($client_server_groups, $client_groups_required) as $csg) {
                //remove user from group
                if(in_array($csg, array(6, 181, 123, 159, 160, 161, 162, 163, 118, 189))) continue; //skip admins
                $this->ServerQueryCommand('servergroupdelclient', array(
                    'sgid'      => $csg,
                    'cldbid'    => $client_db_id
                ));
            }

            Session::set('Eve.Profile.Teamspeak.Updated', true);

            fclose($this->socket);
        } catch(Exception $e) {
            Session::set('Eve.Profile.Teamspeak.Updated', $e->getMessage());
        }

        if(isset($identity)) {
            $m->TeamSpeakIdentity = $identity;
        } else {
            $m->TeamSpeakIdentity = false;
        }
        $m->write();

        return $this->redirectBack();
    }
}
