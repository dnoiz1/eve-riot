<?php
/*
 * rly wanted to avoid this but ok.
 */

class invGroups extends DataObject
{
    static $db = array(
        'groupID' => 'Int',
        'categoryID' => 'Int',
        'groupName' => 'Varchar(100)',
        'description' => 'Text',
        'iconID' => 'Int',
        'useBasePrice' => 'Int',
        'allowManufacture' => 'Int',
        'allowRecycler' => 'Int',
        'anchored' => 'Int',
        'anchoreable' => 'Int',
        'fittableNonSingleton' => 'Int',
        'published' => 'Int'
    );

    public $invTypes = false;

    function invTypes()
    {
        if($this->invtypes) return $this->invtypes;
        $this->invtypes = invTypes::get('invTypes', sprintf("`groupID` = '%d' and `published` = '1'", $this->groupID));
        return $this->invtypes;
    }
}
