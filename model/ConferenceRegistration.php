<?php
class ConferenceRegistration 
{
	private $mId,$mAccountId,$mType,$mdietaryRestr,$mOtherDietOpts,$mOtherOpts,$mBadgeInfo,$mTransaction, $mEvents;
	
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
	 * @param Int $mAccountId
	 * @param String $mType
	 * @param String $mdietaryRestr
	 * @param String $mOtherDietOpts
	 * @param String $mOtherOpts
	 * @param String $mBadgeInfo
	 * @param Object(ConferenceTransaction) $mTransaction
	 * @param Object(array of ConferenceEvent objects) $mEvents
	 */
	public static function createFromScratch($mAccountId,$mType,$mdietaryRestr,$mOtherDietOpts,$mOtherOpts,$mBadgeInfo,
	$mTransaction=null, $mEvents=null)
	{
		$titleObj=Title::newFromText($title);
		$pageObj=WikiPage::factory($titleObj);
		$text=Xml::element('registration',array('regType'=>$mType,'dietaryRestr'=>$mdietaryRestr,'otherDietOpts'=>$mOtherDietOpts,
		'otherOpts'=>$mOtherOpts,'badge'=>$mBadgeInfo,'cvext-registration-account'=>$mAccountId));
		$status=$page->doEdit($text, 'new registration added',EDIT_NEW);	
		if($status['revision'])
		$revision=$status['revision'];
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
			$text=Xml::element('registration-event',array('cvext-registration-parent'=>$id,'cvext-registration-event'=>$event->getEventId()));
			$status=$page->doEdit($text, 'new registration-event added',EDIT_NEW);	
			if($status['revision'])
			$revision=$status['revision'];
			$subId=$revision->getPage();
			$properties=array('cvext-registration-parent'=>$id,'cvext-registration-event'=>$event->getEventId());
			foreach($properties as $name=>$value)
			{
				$dbw->insert('page_props', array('pp_page'=>$subId,'pp_propname'=>$name,'pp_value'=>$value));
			}
		}
	}
	/**
	 * @param Int $registrationId
	 * @return ConferenceRegistration
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
	public static function render($input, array $args, Parser $parser, PPFrame $frame)
	{
		wfGetDB(DB_MASTER)->insert('page_props',array('pp_page'=>$parser->getTitle()->getArticleId()
		,'pp_propname'=>'cvext-registration-account','pp_value'=>$args['cvext-registration-account']));
		return '';
	}
	public static function renderSub($input, array $args, Parser $parser, PPFrame $frame)
	{
		$dbw=wfGetDB(DB_MASTER);
		$id=$parser->getTitle()->getArticleId();
		$properties=array(array('id'=>$id,'prop'=>'cvext-registration-parent','value'=>$args['cvext-registration-parent']),array('id'=>$id,'prop'=>'cvext-registration-event','value'=>$args['cvex-registration-event']));
		foreach ($properties as $property)
		{
			$dbw->insert('page_props',array('pp_page'=>$property['id']
		,'pp_propname'=>$property['prop'],'pp_value'=>$property['value']));
		}
		return '';
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