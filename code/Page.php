
<?php
class Page extends SiteTree {
	public static $has_one = array(
	);
    function ShowInMainMenu()
    {
        return true;
    }

    public static $db = array(
        'OpenInNewWindow' => 'Boolean'
    );

    public static $defaults = array(
        'OpenInNewWindow' => 'false'
    );

    public function getCMSFields()
    {
        $f = parent::getCMSFields();
        $fields = $f->findOrMakeTab('Root.Behaviour');
        $fields->insertAfter(new CheckBoxField('OpenInNewWindow', 'Open in new window?', $this->OpenInNewWindow), 'ShowInMenus');
        return $f;
    }
}
class Page_Controller extends ContentController {

	/**
	 * An array of actions that can be accessed via a request. Each array element should be an action name, and the
	 * permissions or conditions required to allow the user to access it.
	 *
	 * <code>
	 * array (
	 *     'action', // anyone can access this action
	 *     'action' => true, // same as above
	 *     'action' => 'ADMIN', // you must have ADMIN permissions to access this action
	 *     'action' => '->checkAction' // you can only access this action if $this->checkAction() returns true
	 * );
	 * </code>
	 *
	 * @var array
	 */
	public static $allowed_actions = array (
	);

	public function init() {
		parent::init();

		// Note: you should use SS template require tags inside your templates
		// instead of putting Requirements calls here.  However these are
		// included so that our older themes still work
		Requirements::themedCSS('layout');
		Requirements::themedCSS('typography');
		Requirements::themedCSS('form');
	}

    public function results($data, $form){
        $data = $_REQUEST;

        $query = Convert::raw2sql(htmlspecialchars($data['Search'], ENT_QUOTES,'UTF-8'));
        //print_r($data['Search']);


        $pages = DataObject::get("SiteTree", sprintf("MATCH (Title,Content) AGAINST ('%s' IN BOOLEAN MODE)", $query));
        $searchresults = new DataObjectSet();
        $searchresults->merge($pages);

        if($m = Member::currentUser() && $m->inGroup('rioters')) {
            $faq = DataObject::get("FAQ", sprintf("MATCH (Title,Content) AGAINST ('%s' IN BOOLEAN MODE)", $query));
            $searchresults->merge($faq);
        }

        if($searchresults){
            $data['Results'] = $searchresults;
        } else {
            $data['Results'] = '';
        }
        $data['Title'] = 'Search Results';

        return $this->customise($data)->renderWith(array('Page_results','Page'));
    }

    function IsRioter()
    {
        if($m = Member::currentUser()) {
            if($m->inGroup('rioters')) return true;
        }
        return false;
    }

    function NextTimer()
    {
       if(!$this->IsRioter()) return false;
       if($t = EvePosTimer::get_one('EvePosTimer', 'TimerEnds > NOW()')) return $t->TimerEndsTimeStamp();
    }
}
