<?php
class ConferenceRegistration 
{
	/**
	 * 
	 * page_id of the conference registration wiki page
	 * @var Int
	 */
	private $mId;
	/**
	 * 
	 * page_id of the associated sub-account wiki page
	 * @var Int
	 */
	private $mAccountId;
	/**
	 * 
	 * The type of registration ( for eg. student type registration)
	 * @var String
	 */
	private $mType;
	/**
	 * 
	 * @var String
	 */
	private $mdietaryRestr;
	/**
	 * 
	 * @var String
	 */
	private $mOtherDietOpts;
	/**
	 * 
	 * @var String
	 */
	private $mOtherOpts;
	/**
	 * 
	 * Info regarding the badge type
	 * @var String
	 */
	private $mBadgeInfo;
	/**
	 * 
	 * Transaction object
	 * @var String
	 * @todo Transaction Handling
	 */
	private $mTransaction;
	/**
	 * 
	 * Array of ConferenceEvent objects associated with this registration
	 * @var Array
	 */
	private $mEvents;
	/**
	 * 
	 * Constructor function
	 * generally called from createFromScratch() and loadFromId() functions
	 * @param unknown_type $mId
	 * @param unknown_type $mAccountId
	 * @param unknown_type $mType
	 * @param unknown_type $mdietaryRestr
	 * @param unknown_type $mOtherDietOpts
	 * @param unknown_type $mOtherOpts
	 * @param unknown_type $mBadgeInfo
	 * @param unknown_type $mTransaction
	 * @param unknown_type $mEvents
	 */
	public function __construct($mId=null,$mAccountId,$mType,$mdietaryRestr,$mOtherDietOpts,$mOtherOpts,$mBadgeInfo,$mTransaction=null, 
	$mEvents=null){
		$this->mId=$mId;
		$this->mAccountId=$mAccountId;
		$this->mType=$mType;
		$this->mdietaryRestr=$mdietaryRestr;
		$this->mOtherDietOpts=$mOtherDietOpts;
		$this->mOtherOpts=$mOtherOpts;
		$this->mBadgeInfo=$mBadgeInfo;
		$this->mTransaction=$mTransaction;
		$this->mEvents=$mEvents;
	}
	/**
	 * 
	 * function called from ConferenceAccount->pushRegistration()
	 * @param Int $mAccountId - page_id of the sub-account wiki page
	 * @param String_type $mType
	 * @param String $mdietaryRestr
	 * @param String $mOtherDietOpts
	 * @param String $mOtherOpts
	 * @param String $mBadgeInfo
	 * @param Object $mTransaction
	 * @param Array $mEvents - these are pre-loaded event objects ( they contain all of the properties initialized from the database)
	 * Not maintaining a check for is_null($mEvents) because every registration is submitted by the user along with the events 
	 * so it wont be null in any case
	 * We would have to specially create event objects and then pass them along createFromScratch() in order for them to be not null here
	 */
	public static function createFromScratch($mAccountId,$mType,$mdietaryRestr,$mOtherDietOpts,$mOtherOpts,$mBadgeInfo,
	$mTransaction=null, $mEvents=null){	
		$username=ConferenceAccountUtils::getUsernameFromSubAccount($mAccountId);
		$confTitle=ConferenceAccountUtils::getConferenceTitleFromSubAccount($mAccountId);
		$titleText=$confTitle.'registrations/'.$username;
		$title=Title::newFromText($titleText);
		$page=WikiPage::factory($title);
		$text=$text=Xml::element('registration',array('regType'=>$mType,'dietaryRestr'=>$mdietaryRestr,'otherDietOpts'=>$mOtherDietOpts,
		'otherOpts'=>$mOtherOpts,'badge'=>$mBadgeInfo,'cvext-registration-account'=>$mAccountId));
		$status=$page->doEdit($text,'new registration added',EDIT_NEW);
		if($status->value['revision'])
		{
			$id=$status->value['revision']->getPage();
			$i=1;
			$dbw=wfGetDB(DB_MASTER);
			foreach ($mEvents as $event)
			{
				$titleEventText = $confTitle.'/'.$username.'/registration-event('.$i.')';
				$titleEvent=Title::newFromText($titleEventText);
				$eventPage=WikiPage::factory($titleEvent);
				$eventText=Xml::element('registration-event',array('cvext-registration-parent'=>$id,
				'cvext-registration-event'=>$event->getEventId()));
				$status=$eventPage->doEdit($eventText,'new registration event added',EDIT_NEW);
				if($status->value['revision'])
				{
					$subRegId=$status->value['revision']->getPage();
					$properties=array(array('id'=>$subRegId,'prop'=>'cvext-registration-event','value'=>$event->getEventId()),
					array('id'=>$subRegId,'prop'=>'cvext-registration-parent','value'=>$id));
					foreach ($properties as $property)
					{
						$dbw->insert('page_props', array('pp_page'=>$property['id'],'pp_propname'=>$property['prop'],
						'pp_value'=>$property['value']));
					}
				} else {
					//do something here
				}
				$i++;
			}
			$dbw->insert('page_props', array('pp_page'=>$id,'pp_propname'=>'cvext-registration-account','pp_value'=>$mAccountId));
			return new self($id,$mAccountId,$mdietaryRestr,$mOtherDietOpts,$mOtherOpts,$mBadgeInfo,$mTransaction,$mEvents);
			
		} else {
			//do something here
			
		}																						
	}
	/**
	 * 
	 * loads the ConferenceRegistration object from the database
	 * @param Int $registrationId
	 */
	public static function loadFromId($registrationId)
	{
		//$registrationId is the id of the parent registration page
		$article=Article::newFromID($registrationId);
		$text=$article->fetchContent();
		preg_match_all("/<registration regType=\"(.*)\" dietaryRestr=\"(.*)\" otherDietOpts=\"(.*)\" otherOpts=\"(.*)\" 
		badge=\"(.*)\" cvext-registration-account=\"(.*)\" \/>/", $text, $matches);
		// fetching children for parent registration page
		$dbr=wfGetDB(DB_SLAVE);
		$res=$dbr->select('page_props',
		array('pp_page'),
		array('pp_propname'=>'cvext-registration-parent','pp_value'=>$registrationId),
		__METHOD__,
		array());
		$events=array();
		foreach ($res as $row)
		{
			$eventRow=$dbr->selectRow('page_props',
			array('pp_value'),
			array('pp_page'=>$row->pp_page,'pp_propname'=>'cvext-registration-event'),
			__METHOD__,
			array());
			$events[]=ConferenceEvent::loadFromId($eventRow->pp_value);
		}
		/**
		 * code for fetching details from transactions table
		 */
		return new self($registrationId,$matches[6][0],$matches[1][0],$matches[2][0],$matches[3][0],$matches[4][0],
		$matches[5][0],$mTransaction, $events);
		
	}
	/**
	 * Parser hook function
	 * @param String $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 */
	public static function render($input, array $args, Parser $parser, PPFrame $frame)
	{
		wfGetDB(DB_MASTER)->insert('page_props',array('pp_page'=>$parser->getTitle()->getArticleId()
		,'pp_propname'=>'cvext-registration-account','pp_value'=>$args['cvext-registration-account']));
		return '';
	}
	/**
	 * Parser hook function
	 * @param String $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 */
	public static function renderSub($input, array $args, Parser $parser, PPFrame $frame)
	{
		$dbw=wfGetDB(DB_MASTER);
		$id=$parser->getTitle()->getArticleId();
		$properties=array(array('id'=>$id,'prop'=>'cvext-registration-parent','value'=>$args['cvext-registration-parent'])
		,array('id'=>$id,'prop'=>'cvext-registration-event','value'=>$args['cvex-registration-event']));
		foreach ($properties as $property)
		{
			$dbw->insert('page_props',array('pp_page'=>$property['id']
		,'pp_propname'=>$property['prop'],'pp_value'=>$property['value']));
		}
		return '';
	}
	/*public static function createFromScratch($mAccountId,$mType,$mdietaryRestr,$mOtherDietOpts,$mOtherOpts,$mBadgeInfo,
	$mTransaction=null, $mEvents=null)
	{
		$titleObj=Title::newFromText($title);
		$pageObj=WikiPage::factory($titleObj);
		$text=Xml::element('registration',array('regType'=>$mType,'dietaryRestr'=>$mdietaryRestr,'otherDietOpts'=>$mOtherDietOpts,
		'otherOpts'=>$mOtherOpts,'badge'=>$mBadgeInfo,'cvext-registration-account'=>$mAccountId));
		$status=$page->doEdit($text, 'new registration added',EDIT_NEW);	
		if($status->value['revision'])
		{
			$revision=$status->value['revision'];
			$id=$revision->getPage();
			$dbw=wfGetDB(DB_MASTER);
			$properties=array('cvext-registration-account'=>$mAccountId);
			foreach ($properties as $name=>$value)
			{
				$dbw->insert('page_props',array('pp_page'=>$id,'pp_propname'=>$name,'pp_value'=>$value));
			}
			foreach($mEvents as $event)
			{
				$titleObj=Title::newFromText($title);
				$pageObj=WikiPage::factory($titleObj);
				$text=Xml::element('registration-event',array('cvext-registration-parent'=>$id
				,'cvext-registration-event'=>$event->getEventId()));
				$status=$page->doEdit($text, 'new registration-event added',EDIT_NEW);	
				if($status->value['revision'])
				{
					$revision=$status->value['revision'];
					$subId=$revision->getPage();
					$properties=array('cvext-registration-parent'=>$id,'cvext-registration-event'=>$event->getEventId());
					foreach($properties as $name=>$value)
					{
						$dbw->insert('page_props', array('pp_page'=>$subId,'pp_propname'=>$name,'pp_value'=>$value));
					}
				}
				else
				{
				//do something here
				}
			}
			return new self($id,$mAccountId,$mdietaryRestr,$mOtherDietOpts,$mOtherOpts,$mBadgeInfo,$mTransaction,$mEvents);
		}
		else
		{
		//do something here
		}
		
	}*/
	public function getDietaryRestr()
	{
		return $this->mdietaryRestr;
	}
	public function setDietaryRestr($restr)
	{
		$this->mdietaryRestr=$restr;
	}
	public function getOtherDietOpts(){
		return $this->mOtherDietOpts;
	}
	public function setOtherDietOpts($opts)
	{
		$this->mOtherDietOpts=$opts;
	}
	public function getOtherOpts()
	{
		return $this->mOtherOpts;
	}
	public function setOtherOpts($opts)
	{
		$this->mOtherOpts=$opts;
	}
	public function getBadgeInfo()
	{
		return $this->mBadgeInfo;
	}
	public function setBadgeInfo($info)
	{
		$this->mBadgeInfo=$info;
	}
	public function getId()
	{
		return $this->mId;
	}
	public function setId($id)
	{
		$this->mId=$id;
	}
	public function getAccountId()
	{
		return $this->mAccountId;
	}
	public function setAccountId($id)
	{
		$this->mAccountId=$id;
	}
	public function getType()
	{
		return $this->mType;
	}
	public function setType($type)
	{
		$this->mType=$type;
	}
		
}