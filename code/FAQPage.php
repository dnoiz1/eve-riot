<?php

class FAQ extends DataObject {
    static $db = array(
        'Title' => 'Varchar(255)',
        'Content' => 'HTMLText',
        'Hidden' => 'Boolean'
    );
    public static $indexes = array(
        "fulltext (Title, Content)"
    );

    function Link()
    {
        return '/riot-101';
    }
}

class FAQPage extends Page {
    static $has_many = array(
        'FAQ' => 'FAQ'
    );

    function getCMSFields()
    {
        $f = parent::getCMSFields();
        return $f;
    }
}

class FAQPage_controller extends Page_controller {
    function Questions() {
        return DataObject::get('FAQ');
    }
}
