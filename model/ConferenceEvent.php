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
	public static function createFromScratch($mConferenceId,$mLocation,$mStartTime,$mEndTime,$mDay,$mTopic,$mGroup)
	{
			$title=Title::newFromText();
			$page=WikiPage::factory($title);
			$text=Xml::element('event',array('event-conf'=>$mConferenceId,'location'=>$mLocation->getLocationId(),'startTime'=>$mStartTime,'endTime'=>$mEndTime,'day'=>$mDay,'topic'=>$mTopic,'group'=>$mGroup));
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
	public static function loadFromId($eventId)
	{
		$article=Article::newFromID($eventId);
		$text=$article->fetchContent();
		/**
		 * parse content
		 */
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
		return new self($parent,$eventId,$location,$mStartTime,$mEndTime,$mDay,$mTopic,$mGroup);
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
}
