<?php

/*  not sure if this is really the right way to do global permissions,
 *  seems a bit dumb
 */

class EvePermissions extends SiteTree implements PermissionProvider
{
    public function providePermissions()
    {
        return array(
            'EVE_DOCTRINE' => array(
                'name'  => 'Manage Doctrines',
                'category' => 'Eve Permissions'
            ),
            'EVE_TIMERS' => array(
                'name'  => 'Manage Timers',
                'category' => 'Eve Permissions'
            ),
            'EVE_CHAR_SHEET' => array(
                'name'  => 'View Pilot Character Sheets',
                'category' => 'Eve Permissions'
            ),
            'EVE_ROSTER' => array(
                'name'  => 'View Complete Roster',
                'category' => 'Eve Permissions'
            ),
            'EVE_LOGISTICS_CONFIGURE' => array(
                'name'  => 'Edit Logistics Configuration',
                'category' => 'Eve Permissions'
            ),
            'EVE_LOGISTICS_ADMIN' => array(
                'name'  => 'Manage Logistics Orders',
                'category' => 'Eve Permissions'
            ),
            'EVE_BLACKLIST_ADMIN' => array(
                'name'  => 'Manage Blacklist',
                'category' => 'Eve Permissions'
            ),
            'JABBER' => array(
                'name'  =>  'Allow Jabber Access',
                'category' => 'External Services'
            ),
            'TEAMSPEAK' => array(
                'name'  =>  'Allow Teamspeak Access',
                'category' => 'External Services'
            )
        );
    }
}
