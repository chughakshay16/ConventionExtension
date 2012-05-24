<?php
class ConferenceEvent
{
	/**
	 * 
	 * page_id of the conference wiki page
	 * @var Int
	 */
	private $mConferenceId;
	/**
	 * 
	 * page_id of the event page(which this object represents)
	 * @var Int
	 */
	private $mEventId;
	/**
	 * 
	 * EventLocation object for this event
	 * @var Object
	 */
	private $mLocation;
	/**
	 * 
	 * Starting time(stored as XXXX for ex. 0070)
	 * @var String
	 */
	private $mStartTime;
	/**
	 * 
	 * Ending time(stored as XXXX for ex. 0070)
	 * @var String
	 */
	private $mEndTime;
	/**
	 * 
	 * Day of the event (stored as ddMMyyyy for ex. 23112007)
	 * @var String
	 */
	private $mDay;
	/**
	 * 
	 * Topic for the event
	 * @var String
	 */
	private $mTopic;
	/**
	 * 
	 * Group for which this event was organized
	 * @var String
	 */
	private $mGroup;
	/**
	 * 
	 * Constructor function
	 * @param Int $mConferenceId
	 * @param Int $mEventId
	 * @param Object $mLocation
	 * @param String $mStartTime
	 * @param String $mEndTime
	 * @param String $mDay
	 * @param String $mTopic
	 * @param String $mGroup
	 */
	public function __construct($mConferenceId,$mEventId=null,$mLocation=null,$mStartTime,$mEndTime,$mDay,$mTopic,$mGroup)
	{
		$this->mConferenceId=$mConferenceId;
		$this->mEventId=$mEventId;
		$this->mLocation=$mLocation;
		$this->mStartTime=$mStartTime;
		$this->mEndTime=$mEndTime;
		$this->mDay=$mDay;
		$this->mTopic=$mTopic;
		$this->mGroup=$mGroup;
		
	}
	/**
	 * @param Int $mConferenceId
	 * @param Object(EventLocation) $mLocation
	 * @param String $mStartTime
	 * @param String $mEndTime
	 * @param String $mDay
	 * @param String $mTopic
	 * @param String $mGroup
	 * @return ConferenceEvent
	 */
	public static function createFromScratch($mConferenceId,$mLocation,$mStartTime,$mEndTime,$mDay,$mTopic,$mGroup)
	{
			$confTitle=ConferenceUtils::getTitle($mConferenceId);
			$titleText=$confTitle.'/events/'.$mTopic.'-'.$mDay.'-'.$mStartTime.'-'.$mEndTime;
			$title=Title::newFromText($titleText);
			$page=WikiPage::factory($title);
			$text=Xml::element('event',array('cvext-event-conf'=>$mConferenceId,'cvext-event-location'=>$mLocation->getLocationId(),
			'startTime'=>$mStartTime,'endTime'=>$mEndTime,'day'=>$mDay,'topic'=>$mTopic,'group'=>$mGroup));
			$status=$page->doEdit($text, 'new event added',EDIT_NEW);
			if($status->value['revision'])
			{
				$revision=$status->value['revision'];
				$eventId=$revision->getPage();
				$dbw=wfGetDB(DB_MASTER);
				$properties=array('cvext-event-conf'=>$mConferenceId,'cvext-event-location'=>$mLocation->getLocationId());
				foreach($properties as $name=>$value)
				{
					$dbw->insert('page_props',array('pp_page'=>$eventId,'pp_propertyname'=>$name,'pp_value'=>$value),__METHOD__,array());
				}
				return new self($mConferenceId,$eventId,$mLocation,$mStartTime,$mEndTime,$mDay,$mTopic,$mGroup);
			}
			else
			{
			//do something here
			}
	}
	/**
	 * @param Int $eventId page_id of the event page
	 * @return ConferenceEvent
	 */
	public static function loadFromId($eventId)
	{
		$article=Article::newFromID($eventId);
		$text=$article->fetchContent();
		preg_match_all("/<event cvext-event-conf=\"(.*)\" cvext-event-location=\"(.*)\" startTime=\"(.*)\" endTime=\"(.*)\" 
		day=\"(.*)\" topic=\"(.*)\" group=\"(.*)\" \/>/",$text,$matches);
		/*wfProfileIn(__METHOD__.'-db');
		$dbr=wfGetDB(DB_SLAVE);
		$res = $dbr->select( 'page_props',
		array('pp_propertyname','pp_value'),
		array( 'pp_page' => $eventId),
		__METHOD__,
		array()
		);
		wfProfileOut(__METHOD__.'-db');
		foreach($res as $value)
		{
			if($value->pp_propertyname=='parent')
			$parent=$value->pp_value;
			else 
			$location=EventLocation::loadFromId($value->pp_value);
		}*/
		$location=EventLocation::loadFromId($matches[2][0]);
		return new self($matches[1][0],$eventId,$location,$matches[3][0],$matches[4][0],$matches[5][0],$matches[6][0],$matches[7][0]);
	}
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 */
	public static function render($input, array $args, Parser $parser, PPFrame $frame)
	{
		$ids=array();
		foreach ($args as $attribute=>$value)
		{
			if($attribute=='cvext-event-conf')
			{
				$ids['cvext-event-conf']=$value;
			}
			if($attribute=='cvext-event-location')
			{
				$ids['cvext-event-location']=$value;
			}
			$id=$parser->getTitle()->getArticleId();
			$dbw=wfGetDB(DB_MASTER);
			foreach ($ids as $name=>$value)
			{
				$dbw->insert('page_props',array('pp_page'=>$id,'ppp_propname'=>$name,'pp_value'=>$value));
			}
			return '';
		}
	}
	/**
	 * 
	 * getter function
	 */
	public function getConferenceId()
	{
		return $this->mConferenceId;
	}
	/**
	 * 
	 * setter function
	 * @param Int $id
	 */
	public function setConferenceId($id)
	{
		$this->mConferenceId=$id;
	}
	/**
	 * 
	 * getter function
	 */
	public function getEventId()
	{
		return $this->mEventId;
	}
	/**
	 * 
	 * setter function
	 * @param Int $id
	 */
	public function setEventId($id)
	{
		$this->mEventId=$id;
	}
	/**
	 * 
	 * getter function
	 */
	public function getLocationId()
	{
		return $this->mLocationId;
	}
	/**
	 * 
	 * setter function
	 * @param Int $id
	 */
	public function setLocationId($id)
	{
		$this->mLocationId=$id;
	}
	/**
	 * 
	 * getter function
	 */
	public function getStartTime()
	{
		return $this->mStartTime;
	}
	/**
	 * 
	 * setter function
	 * @param String $time
	 */
	public function setStartTime($time)
	{
		$this->mStartTime=$time;
	}
	/**
	 * 
	 * getter function
	 */
	public function getEndTime()
	{
		return $this->mEndTime;
	}
	/**
	 * 
	 * setter function
	 * @param String $time
	 */
	public function setEndTime($time)
	{
		$this->mEndTime=$time;
	}
	/**
	 * 
	 * getter function
	 */
	public function getDay()
	{
		return $this->mDay;
	}
	/**
	 * 
	 * setter function
	 * @param String $day
	 */
	public function setDay($day)
	{
		$this->mDay=$day;
	}
	/**
	 * 
	 * getter function
	 */
	public function getTopic()
	{
		return $this->mTopic;
	}
	/**
	 * 
	 * setter function
	 * @param String $topic
	 */
	public function setTopic($topic)
	{
		$this->mTopic=$topic;
	}
	/**
	 * 
	 * getter function
	 */
	public function getGroup()
	{
		return $this->mGroup;
	}
	/**
	 * 
	 * setter function
	 * @param String $group
	 */
	public function setGroup($group)
	{
		$this->mGroup=$group;
	}
	
	
}
