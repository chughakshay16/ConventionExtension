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
			$titleText=$confTitle.'/events/'.$mTopic.'-'.$mDay.'-'.$mStartTime.'-'.$mEndTime.'-'.$mGroup;
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
	 * updates the event page in the database
	 * @param Int $cid
	 * @param EventLocation object $mLocation
	 * @param String $mStartTime
	 * @param String $mEndTime
	 * @param String $mDay
	 * @param String $mTopic
	 * @param String $mGroup
	 */
	public static function performEdit($cid,$mLocation,$mStartTime,$mEndTime,$mDay,$mTopic,$mGroup)
	{
		$confTitle=ConferenceUtils::getTitle($cid);
		$titleText=$confTitle.'/events/'.$mTopic.'-'.$mDay.'-'.$mStartTime.'-'.$mEndTime.'-'.$mGroup;
		$title=Title::newFromText($titleText);
		$page=WikiPage::factory($title);
		if($page->exists())
		{
			$id=$page->getId();
			$article=Article::newFromID($id);
			$content=$article->fetchContent();
			//modify the content
			//even the location pointer can be modified
			//while fetching values from the content , we will see if $mLocation->getLocationId() matches with the location id stored 
			//in the content , so if its the same then page_properties table wont be modified otherwise page_props table would have
			//to be modified as well
			$status=$page->doEdit($content,"Event has been modified",EDIT_UPDATE);
			if($status->value['revision'])
			{
				if($isPagePropChanged)
				{
					$dbw=wfGetDB(DB_MASTER);
					$res=$dbw->update('page_props',
					array('pp_location'=>$mLocation->getLocationId()),
					array('pp_page'=>$id,'pp_propname'=>'cvext-event-location'),
					__METHOD__);
					if($res)
					{
						$result['done']=true;
						$result['msg']='The event was successfully updated';	
					} else {
						$result['done']=false;
						$result['msg']='The properties were not updated properly';
					}
						
				} else
				{
					$result['done']=true;
					$result['msg']='The event was successfully updated';
				}
			} else {
				$result['done']=false;
				$result['msg']='The event could not be successfully updated';
			}
			
		} else {
			$result['done']=false;
			$result['msg']='The event with these details wasnt found in the database';
		}
		return $result;
	}
	/**
	 * 
	 * deletes an event and its linked properties
	 * wont delete if any registration is pointing towards this event
	 * @param Int $cid
	 * @param String $mStartTime
	 * @param String $mEndTime
	 * @param String $mDay
	 * @param String $mTopic
	 * @param String $mGroup
	 * @return $result
	 * $result['done'] - true/false ~ success/failure
	 * $result['msg'] - success or failure message
	 */
	public static function performDelete($cid,$mStartTime,$mEndTime,$mDay,$mTopic,$mGroup)
	{
		$confTitle=ConferenceUtils::getTitle($cid);
		$titleText=$confTitle.'/events/'.$mTopic.'-'.$mDay.'-'.$mStartTime.'-'.$mEndTime.'-'.$mGroup;
		$title=Title::newFromText($titleText);
		$page=WikiPage::factory($title);
		$result=array();
		if($page->exists())
		{
			//do a check to see if none of the registrations are associated with this event
			$id=$page->getId();
			$dbr=wfGetDB(DB_SLAVE);
			$res=$dbr->select('page_props',
			'pp_page',
			array('pp_propname'=>'cvext-registration-event','pp_value'=>$id),
			__METHOD__);
			if($dbr->numRows($res)>0)
			{
				$result['done']=false;
				$result['msg']="event cant be deleted as it is associated with a registration";
			} else {
				$status=$page->doDeleteArticle("event is deleted by the admin",Revision::DELETED_TEXT);
				if($status===true)
				{
					$result['done']=true;
					$result['msg']="Event was successfully deleted";
				} else {
					$result['done']=false;
					$result['msg']="The event couldnt be deleted";
				}	
			}
			
		} else {
			$result['done']=false;
			$result['msg']="no event was found with such details in this conference";
		}
		return $result;
	}
	/**
	 * 
	 * Parser Hook function
	 * @param String $input
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
		}
		$id=$parser->getTitle()->getArticleId();
		if($id!=0)
		{
			$dbw=wfGetDB(DB_MASTER);
			foreach ($ids as $name=>$value)
			{
				$dbw->insert('page_props',array('pp_page'=>$id,'ppp_propname'=>$name,'pp_value'=>$value));
			}
		}
		return '';
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
