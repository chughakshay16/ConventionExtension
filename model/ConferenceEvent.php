<?php
class ConferenceEvent
{
	private $mConferenceId,$mEventId,$mLocation,$mStartTime,$mEndTime,$mDay,$mTopic,$mGroup;
	
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
			$title=Title::newFromText();
			$page=WikiPage::factory($title);
			$text=Xml::element('event',array('event-conf'=>$mConferenceId,'location'=>$mLocation->getLocationId(),
			'startTime'=>$mStartTime,'endTime'=>$mEndTime,'day'=>$mDay,'topic'=>$mTopic,'group'=>$mGroup));
			$status=$page->doEdit($text, 'new event added',EDIT_NEW);
			if($status['revision'])
			{
				$revision=$status['revision'];
				$eventId=$revision->getPage();
			}
			$dbw=wfGetDB(DB_MASTER);
			$properties=array('event-conf'=>$mConferenceId,'location'=>$mLocation->getLocationId());
			foreach($properties as $name=>$value)
			{
				$dbw->insert('page_props',array('pp_page'=>$eventId,'pp_propertyname'=>$name,'pp_value'=>$value),__METHOD__,array());
			}
			return new self($mConferenceId,$eventId,$mLocation,$mStartTime,$mEndTime,$mDay,$mTopic,$mGroup);
	}
	/**
	 * @param Int $eventId page_id of the event page
	 * @return ConferenceEvent
	 */
	public static function loadFromId($eventId)
	{
		$article=Article::newFromID($eventId);
		$text=$article->fetchContent();
		preg_match_all("/<event event-conf=\"(.*)\" location=\"(.*)\" startTime=\"(.*)\" endTime=\"(.*)\" 
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
	public static function render($input, array $args, Parser $parser, PPFrame $frame)
	{
		$ids=array();
		foreach ($args as $attribute=>$value)
		{
			if($attribute=='event-conf')
			{
				$ids['event-conf']=$value;
			}
			if($attribute=='location')
			{
				$ids['location']=$value;
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
	public function getConferenceId()
	{
		return $this->mConferenceId;
	}
	public function setConferenceId($id)
	{
		$this->mConferenceId=$id;
	}
	public function getEventId()
	{
		return $this->mEventId;
	}
	public function setEventId($id)
	{
		$this->mEventId=$id;
	}
	public function getLocationId()
	{
		return $this->mLocationId;
	}
	public function setLocationId()
	{
		$this->mLocationId=$id;
	}
	public function getStartTime()
	{
		return $this->mStartTime;
	}
	public function setStartTime($time)
	{
		$this->mStartTime=$time;
	}
	public function getEndTime()
	{
		return $this->mEndTime;
	}
	public function setEndTime($time)
	{
		$this->mEndTime=$time;
	}
	public function getDay()
	{
		return $this->mDay;
	}
	public function setDay($day)
	{
		$this->mDay=$day;
	}
	public function getTopic()
	{
		return $this->mTopic;
	}
	public function setTopic($topic)
	{
		$this->mTopic=$topic;
	}
	public function getGroup()
	{
		return $this->mGroup;
	}
	public function setGroup($group)
	{
		$this->mGroup=$group;
	}
	
	
}
