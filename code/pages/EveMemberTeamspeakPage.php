<?php
class EveMemberTeamspeakPage extends Page {
    static $db = array(
        'Hostname'  => 'Varchar(255)',
        'Port'      => 'Int',
        'Username'  => 'Varchar(255)',
        'Password'  => 'Varchar(255)'
    );

    static $defaults = array(
        'Port' => '10011'
    );

    function getCMSFields()
    {
        $f = parent::getCMSFields();
        $f->findOrMakeTab('Root.Teamspeak', 'Teamspeak');
        $f->addFieldsToTab('Root.Teamspeak', new FieldSet(
            new TextField('Hostname', 'Hostname'),
            new NumericField('Port', 'Port'),
            new Textfield('Username', 'ServerQuery User'),
            new PasswordField('Password', 'ServerQuery Password')
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
                    preg_match("/id=(\d+) msg=(.*?)$/ims", $match);
                    if($match[1] != 0) {
                        throw new Exception(sprintf("Error: %d ServerQuery::%s", $match[1], $this->ServerQueryUnescape($match[2])));
                    }
                }
            }
        }

        return $ret;
    }

    function TeamspeakForm()
    {
        $m = Member::currentUser();
        if(!$m) return new Form();

        $f = new FieldSet(
            //new CheckBoxField('TeamspeakAutoConnect', 'Auto Connect to Teamspeak', $m->getField('TeamspeakAutoConnect'))
        );

        if(!Session::get('Eve.Profile.Teamspeak.Check')) {
            $a = new FieldSet(
                new FormAction('TeamspeakCheck', 'click here when you are connected')
            );
        } else {
            $a = new FieldSet(
                new FormAction('TeamspeakSave', 'Save')
            );
        }

        $form = new Form($this, 'TeamspeakForm', $f, $a);

        if(Session::get('Eve.Profile.Teamspeak.Updated')) {
            $form->setMessage('Teamspeak Login Saved', 'good');
            Session::clear('Eve.Profile.Teamspeak.Updated');
        }

        return $form;
    }

    function TeamspeakCheck($data, $form)
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
                if(strtolower($c['client_nickname']) == strtolower($m->Nickname)) {
                    // this is the droid we are looking for
                    $client = $c;
                }
            }

            if(!$client) throw new Exception('ServerQuery::NoClientFound');

            $client_info = $this->ServerQueryCommand('clientinfo', array(
                'clid' => $client['clid']
            ));

            $client = array();
            foreach(explode(" ", $client_info[0]) as $ci) {
                list($k, $v) = preg_split('/=([^ ]+)/', $ci, -1, PREG_SPLIT_DELIM_CAPTURE);
                $client[$k] = $this->ServerQueryUnescape($v);
            }

            //about time, this is what we wanted.
            $identity = $client['client_unique_identifier'];
            $client_db_id = $client['client_database_id'];
            //var_dump($client);

            // find out which groups have 'jabber access'
            $groups = array();
            foreach($m->Groups() as $g) {
                if($g->hasPerm('JABBER') && $g->Ticker) {
                    $groups[] = $g;
                }
            }

            $server_groups = $this->ServerQueryCommand('servergrouplist');
            $server_groups = explode("|", $server_groups[0]);

            $sgroups = array();
            foreach($server_groups as $i => $sg) {
                foreach(explode(" ", $sg) as $g) {
                    list($k, $v) = explode("=", $g);
                    $sgroups[$i][$k] = $this->ServerQueryUnescape($v);
                }
            }

            foreach($sgroups as $si => $server_group) {
                foreach($groups as $group) {
                    if($server_group['name'] == $group->Ticker) {
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
            }
            //var_dump($sgroups);

            fclose($this->socket);
        } catch(Exception $e) {
            return;
        }

    }
    function TeamspeakSave($data, $form)
    {
        $m = Member::currentUser();
        if(!$m) return Director::redirectBack();

        //$m->setField('TeamspeakAutoConnect', $data['TeamspeakAutoConnect']);

        $m->write();

        Session::set('Eve.Profile.Teamspeak.Updated', true);

        Director::redirectBack();
    }

}
