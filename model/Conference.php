<?php
class Conference
{
	private $mId;
	private $mTitle;
	private $mDescription;
	private $mStartDate;
	private $mEndDate;
	private $mVenue;
	private $mCapacity;
	private $mAuthors;
	private $mEvents;
	private $mApplicants;
	private $mOrganizers;
	private $mAccounts;
	
	public function __construct($mId=null,$mTitle,$mDescription,$mStartDate,$mEndDate,$mVenue,$mCapacity,$mAuthors=null,$mEvents=null,$mApplicants=null,$mOrganizers=null,$mAccounts=null)
	{
			//this is the ideal way of initializing the conference
			$this->mId=$mId;
			$this->mTitle=$mTitle;
			$this->mDescription=$mDescription;
			$this->mStartDate=$mStartDate;
			$this->mEndDate=$mEndDate;
			$this->mVenue=$mVenue;
			$this->mCapacity=$mCapacity;
			$this->mAuthors=$mAuthors;
			$this->mEvents=$mEvents;
			$this->mApplicants=$mApplicants;
			$this->mOrganizers=$mOrganizers;
			$this->mAccounts=$mAccounts;
		
		
	}
	public static function createFromScratch($title,$venue,$capacity,$startDate,$endDate,$description)
	{
		$titleObj=Title::newFromText($title);
		$page=WikiPage::factory($titleObj);
		$text=Xml::element('conference',array('title'=>$title,'venue'=>$venue,'capacity'=>$capacity,'startDate'=>$startDate,'endDate'=>$endDate,'description'=>$description));
		$status=$page->doEdit($text, 'new conference added',EDIT_NEW);
		if($status['revision'])
		$revision=$status['revision'];
		$id=$revision->getPage();
		return new self($id,$title,$description,$startDate,$endDate,$venue,$capacity);
	}
	public static function loadFromId($conferenceId)
	{
		$article=Article::newFromID($conferenceId);
		$text=$article->fetchContent();
		/**
		 * parse the text , $title, $venue, $capacity, $startDate, $endDate, $description
		 */
		// now get the information on speakers
		$dbr=wfGetDB(DB_SLAVE);
		$res=$dbr->select('page_props',
		array('pp_page'),
		array('pp_propname'=>'speaker-conf','pp_value'=>$conferenceId),
		__METHOD__,
		array());
		$speakers=array();
		foreach ($res as $row)
		{
			$speakers[]=ConferenceAuthor::loadFromId($row->pp_page);
		}
		//get all the organizers
		$orgres=$dbr->select('page_props',
		array('pp_page'),
		array('pp_propname'=>'organizer-conf','pp_value'=>$conferenceId),
		__METHOD__,
		array());
		$organizers=array();
		foreach ($orgres as $row)
		{
			$organizers[]=ConferenceOrganizer::loadFromId($row->pp_page);
		}
		//get all accounts 
		$accres=$dbr->select('page_props',
		array('pp_page'),
		array('pp_propname'=>'account-conf','pp_value'=>$conferenceId),
		__METHOD__,
		array());
		$accounts=array();
		foreach ($accres as $row)
		{
			$accounts[]=ConferenceAccount::loadFromId($row->pp_page);
		}
		$evtres=$dbr->select('page_props',
		array('pp_page'),
		array('pp_propname'=>'event-conf','pp_value'=>$conferenceId),
		__METHOD__,
		array());
		$events=array();
		foreach ($evtres as $row)
		{
			$events[]=ConferenceEvent::loadFromId($row->pp_page);
		}
		$appres=$dbr->select('page_props',
		array('pp_page'),
		array('pp_propname'=>'applicant-conf','pp_value'=>$conferenceId),
		__METHOD__,
		array());
		$applicants=array();
		foreach ($appres as $row)
		{
			$applicants[]=ConferenceApplicant::loadFromId($row->pp_page);
		}
		return new self($conferenceId,$mTitle,$mDescription,$mStartDate,$mEndDate,$mVenue,$mCapacity,$speakers,$events,$applicants,$organizers,$accounts);
		
	}
	private function getAuthors($conferenceId)
	{
	
	}
	private function getApplicants($conferenceId)
	{
	
	}
	private function getEvents($conferenceId)
	{
	
	}
	private function getOrganizers($conferenceId)
	{
	
	}
	private function getAccounts($conferenceId)
	{
	
	}
	public  function getId()
	{
		return $this->mId;
	}
	public function setId($id)
	{
		$this->mId=$id;
	}
	public function getAuthors()
	{
		return $this->mAuthors;
	}
	public function setAuthors($authors)
	{
		$this->mAuthors=$authors;
	}
	public function getOrganizers()
	{
		return $this->mOrganizers;
	}
	public function setOrganizers($organizers)
	{
		$this->mOrganizers=$organizers;
	}
	public function getAccounts()
	{
		return $this->mAccounts;
	}
	public function setAccounts($accounts)
	{
		$this->mAccounts=$accounts;
	}
	public function getApplicants()
	{
		return $this->mApplicants;
	}
	public function setApplicants($applicants)
	{
		$this->mApplicants=$applicants;
	}
	public function getEvents()
	{
		return $this->mEvents;
	}
	public function setEvents($events)
	{
		$this->mEvents=$events;
	}
	public function getTitle()
	{
		return $this->mTitle;
	}
	public function setTitle($title)
	{
		$this->mTitle=$title;
	}
	public function getCapacity()
	{
		return $this->mCapacity;
	}
	public function setCapacity($capacity)
	{
		$this->mCapacity=$capacity;
	}
	public function getDescription()
	{
		return $this->mDescription;
	}
	public function setDescription($description)
	{
		$this->mDescription=$description;
	}
	public function getStartDate()
	{
		return $this->mStartDate;
	}
	public function setStartDate($startDate)
	{
		$this->mStartDate=$startDate;
	}
	public function getEndDate()
	{
		return $this->mEndDate;
	}
	public function setEndDate($endDate)
	{
		$this->mEndDate=$endDate;
	}
	public function getVenue()
	{
		return $this->mEvents;
	}
	public function setVenue($venue)
	{
		$this->mVenue=$venue;
	}
}