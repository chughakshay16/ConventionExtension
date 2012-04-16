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
	private $mPages;

	public function __construct($mId=null,$mTitle,$mDescription,$mStartDate,$mEndDate,$mVenue,$mCapacity,$mAuthors=null
	,$mEvents=null,$mApplicants=null,$mOrganizers=null,$mAccounts=null,$pages=null)
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
		$this->mPages=$pages;


	}
	/**
	 * Enter description here ...
	 * @param String $title title of the conference
	 * @param String $venue venue for the conference
	 * @param String $capacity total number of attendees allowed for this conference
	 * @param String $startDate - starting date
	 * @param String $endDate - ending date
	 * @param String $description - short description for this conference
	 * @return Conference
	 */
	public static function createFromScratch($title,$venue,$capacity,$startDate,$endDate,$description)
	{
		$titleObj=Title::newFromText($title);
		$page=WikiPage::factory($titleObj);
		$text=Xml::element('conference',array('title'=>$title,'venue'=>$venue,'capacity'=>$capacity
		,'startDate'=>$startDate,'endDate'=>$endDate,'description'=>$description,'type'=>'conference'));
		$status=$page->doEdit($text, 'new conference added',EDIT_NEW);
		if($status['revision'])
		$revision=$status['revision'];
		$id=$revision->getPage();
		$dbw=wfGetDB(DB_MASTER);
		$dbw->insert('page_props',array('pp_page'=>$id,'pp_propname'=>'type','pp_value'=>'conference'));
		return new self($id,$title,$description,$startDate,$endDate,$venue,$capacity);
	}
	/**
	 * @param Int $conferenceId - page_id of the conference page
	 * @return Conference
	 */
	public static function loadFromId($conferenceId)
	{
		$article=Article::newFromID($conferenceId);
		$text=$article->fetchContent();
		preg_match_all("/<conference title=\"(.*)\" venue=\"(.*)\" capacity=\"(.*)\" startDate=\"(.*)\"
		endDate=\"(.*)\" description=\"(.*)\" type=\"(.*)\" \/>/",$text,$matches);
		// now get the information on speakers
		$dbr=wfGetDB(DB_SLAVE);
		//collect all the pages pointing to $conferenceId as their parent conference
		$res=$dbr->select('page_props',
		array('pp_page','pp_propname'),
		array('pp_value'=>$conferenceId),
		__METHOD__,
		array());
		$accounts=array();
		$pages=array();
		$organizers=array();
		$applicants=array();
		$events=array();
		$authors=array();
		//now depending on the property name initialise
		foreach($res as $row)
		{
			if($res->pp_propname=='account-conf')
			{
				$accounts[]=ConferenceAccount::loadFromId($res->pp_page);
			}
			else if($res->pp_propname=='page-conf')
			{
				$pages[]=ConferencePage::loadFromId($res->pp_page);
			}
			else if($res->pp_propname=='speaker-conf')
			{
				$authors[]=ConferenceAuthor::loadFromId($res->pp_page);
			}
			else if($res->pp_propname=='event-conf')
			{
				$events[]=ConferenceEvent::loadFromId($res->pp_page);
			}
			else if($res->pp_propname=='organizer-conf')
			{
				$organizers[]=ConferenceOrganizer::loadFromId($res->pp_page);
			}
			else 
			{
				$applicants[]=ConferenceApplicant::loadFromId($res->pp_page);
			}
			
		}
		/*$res=$dbr->select('page_props',
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
		}*/
		return new self($conferenceId,$matches[1][0],$matches[6][0],$matches[4][0],$matches[5][0]
		,$matches[2][0],$matches[3][0],$speakers,$events,$applicants,$organizers,$accounts,$pages);

	}
	public static function render($input, array $args, Parser $parser, PPFrame $frame)
	{
		$conferenceType=$args['type'];
		$conferenceId=$parser->getTitle()->getArticleId();
		$dbw=wfGetDB(DB_MASTER);
		$dbw->insert('page_props',array('pp_page'=>$conferenceId,'pp_propname'=>'type','pp_value'=>'conference'));
		return '';
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
	public function getPages()
	{
		return $this->mPages;
	}
	public function setPages($pages)
	{
		$this->mPages=$pages;
	}
}