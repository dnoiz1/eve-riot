<?php
class DustMercToken extends DataObject
{
    static $db = array(
        'Token'     => 'Varchar(8)',
        'Used'      => 'Boolean',
        'Expires'   => 'SS_DateTime'
    );

    static $has_one = array(
        'Member' => 'Member',
        'DustMercTokenProvider' => 'DustMercTokenProvider'
    );
}
