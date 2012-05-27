<?php
class Conference
{
	/**
	 * 
	 * page_id of the conference wiki page that this object represents
	 * @var Int
	 */
	private $mId;
	/**
	 * 
	 * Title of the conference(in our case its also the title of conference wiki page that exists in page table)
	 * @var String
	 */
	private $mTitle;
	/**
	 * 
	 * Description for the conference
	 * @var String
	 */
	private $mDescription;
	/**
	 * 
	 * Starting date of the conference
	 * @var String ( eg. 12062007)
	 */
	private $mStartDate;
	/**
	 * 
	 * Ending date of the conference
	 * @var String (eg. 12062007)
	 */
	private $mEndDate;
	/**
	 * 
	 * Venue for the conference
	 * @var String
	 */
	private $mVenue;
	/**
	 * 
	 * Capacity of the conference
	 * @var String(actually will be a number)
	 */
	private $mCapacity;
	/**
	 * 
	 * Array of ConferenceAuthor objects for this conference
	 * @var Array
	 */
	private $mAuthors;
	/**
	 * 
	 * Array of ConferenceEvent objects for this conference
	 * @var Array
	 */
	private $mEvents;
	/**
	 * 
	 * Array of ConferenceApplicant objects for this conference
	 * @var Array
	 */
	private $mApplicants;
	/**
	 * 
	 * Array of ConferenceOrganizer objects for this conference
	 * @var Array
	 */
	private $mOrganizers;
	/**
	 * 
	 * Array of ConferenceAccount objects for this conference
	 * @var Array
	 */
	private $mAccounts;
	/**
	 * 
	 * Array of ConferencePage objects for this conference
	 * @var Array
	 */
	private $mPages;
	/**
	 * 
	 * constructor function (generally called from other functions)
	 * @param Int $mId
	 * @param String $mTitle
	 * @param String $mDescription
	 * @param String $mStartDate
	 * @param String $mEndDate
	 * @param String $mVenue
	 * @param String $mCapacity
	 * @param Array $mAuthors
	 * @param Array $mEvents
	 * @param Array $mApplicants
	 * @param Array $mOrganizers
	 * @param Array $mAccounts
	 * @param Array $pages
	 */
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
	 * Creates a Conference object and also pushes the data into the database
	 * It just returns the object initialized with properties passed through this function and also the conferenceId
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
		//in case of conference wiki page (page_title would be set as conference title only)
		$titleObj=Title::newFromText($title);
		$page=WikiPage::factory($titleObj);
		$text=Xml::element('conference',array('title'=>$title,'venue'=>$venue,'capacity'=>$capacity
		,'startDate'=>$startDate,'endDate'=>$endDate,'description'=>$description,'cvext-type'=>'conference'));
		$status=$page->doEdit($text, 'new conference added',EDIT_NEW);
		if($status->value['revision'])
		{
		$revision=$status->value['revision'];
		$id=$revision->getPage();
		$dbw=wfGetDB(DB_MASTER);
		$dbw->insert('page_props',array('pp_page'=>$id,'pp_propname'=>'cvext-type','pp_value'=>'conference'));
		return new self($id,$title,$description,$startDate,$endDate,$venue,$capacity);
		}
		else
		{
			//what should be done
		}
	}
	/**
	 * loads the whole conference object from the database
	 * it loads all the other relevant objects and sets them as properties of the Conference object
	 * @param Int $conferenceId - page_id of the conference page
	 * @return Conference
	 */
	public static function loadFromId($conferenceId)
	{
		$article=Article::newFromID($conferenceId);
		$text=$article->fetchContent();
		preg_match_all("/<conference title=\"(.*)\" venue=\"(.*)\" capacity=\"(.*)\" startDate=\"(.*)\"
		endDate=\"(.*)\" description=\"(.*)\" cvext-type=\"(.*)\" \/>/",$text,$matches);
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
			if($res->pp_propname=='cvext-account-conf')
			{
				$accounts[]=ConferenceAccount::loadFromId($res->pp_page);
			}
			else if($res->pp_propname=='cvext-page-conf')
			{
				$pages[]=ConferencePage::loadFromId($res->pp_page);
			}
			else if($res->pp_propname=='cvext-author-conf')
			{
				$authors[]=ConferenceAuthor::loadFromId($res->pp_page);
			}
			else if($res->pp_propname=='cvext-event-conf')
			{
				$events[]=ConferenceEvent::loadFromId($res->pp_page);
			}
			else if($res->pp_propname=='cvext-organizer-conf')
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
	/**
	 * 
	 * edits the conference details
	 * called via ApiConferenceEdit class
	 * @param Int $cid
	 * @param String $title
	 * @param String  $venue
	 * @param String $description
	 * @param String $capacity
	 * @param String $startDate
	 * @param String $endDate
	 * @param String $mDescription
	 */
	public static function performEdit($cid,$title,$venue,$description,$capacity,$startDate,$endDate,$mDescription)
	{
		$confTitle=ConferenceUtils::getTitle($cid);
		$titleText='conferences/'.$confTitle;
		$title=Title::newFromText($titleText);
		$page=WikiPage::factor($title);
		$result=array();
		if($page->exists())
		{
			$id=$page->getId();
			$article=Article::newFromID($id);
			$content=$article->fetchContent();
			//modify the content
			$status=$page->doEdit($content,"conference details modified by the admin",EDIT_UPDATE);	
			if($status->value['revision'])
			{
				$result['done']=true;
				$result['msg']="conference details are successfully saved";	
			} else {
				$result['done']=false;
				$result['msg']="conference details couldnt be saved";
			}
		} else {
			$result['done']=false;
			$result['msg']="No conference exists with this title in the database";
		}
		return $result;
	}
	/**
	 * 
	 * Parser Hook function
	 * @param String $input
	 * @param Array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 */
	public static function render($input, array $args, Parser $parser, PPFrame $frame)
	{
		//$conferenceType=$args['type'];
		$conferenceId=$parser->getTitle()->getArticleId();
		$dbw=wfGetDB(DB_MASTER);
		$dbw->insert('page_props',array('pp_page'=>$conferenceId,'pp_propname'=>'cvext-type','pp_value'=>'conference'));
		return '';
	}
	/**
	 * 
	 * getter function
	 */
	public  function getId()
	{
		return $this->mId;
	}
	/**
	 * 
	 * setter function
	 * @param Int $id
	 */
	public function setId($id)
	{
		$this->mId=$id;
	}
	/**
	 * 
	 * getter function
	 */
	public function getAuthors()
	{
		return $this->mAuthors;
	}
	/**
	 * 
	 * setter function
	 * @param Array $authors
	 */
	public function setAuthors($authors)
	{
		$this->mAuthors=$authors;
	}
	/**
	 * 
	 * getter function
	 */
	public function getOrganizers()
	{
		return $this->mOrganizers;
	}
	/**
	 * 
	 * setter function
	 * @param Array $organizers
	 */
	public function setOrganizers($organizers)
	{
		$this->mOrganizers=$organizers;
	}
	/**
	 * 
	 * getter function
	 */
	public function getAccounts()
	{
		return $this->mAccounts;
	}
	/**
	 * 
	 * setter function
	 * @param Array $accounts
	 */
	public function setAccounts($accounts)
	{
		$this->mAccounts=$accounts;
	}
	/**
	 * 
	 * getter function
	 */
	public function getApplicants()
	{
		return $this->mApplicants;
	}
	/**
	 * 
	 * setter function
	 * @param Array $applicants
	 */
	public function setApplicants($applicants)
	{
		$this->mApplicants=$applicants;
	}
	/**
	 * 
	 * getter function
	 */
	public function getEvents()
	{
		return $this->mEvents;
	}
	/**
	 * 
	 * setter function
	 * @param Array $events
	 */
	public function setEvents($events)
	{
		$this->mEvents=$events;
	}
	/**
	 * 
	 * getter function
	 */
	public function getTitle()
	{
		return $this->mTitle;
	}
	/**
	 * 
	 * setter function
	 * @param String $title
	 */
	public function setTitle($title)
	{
		$this->mTitle=$title;
	}
	/**
	 * 
	 * getter function
	 */
	public function getCapacity()
	{
		return $this->mCapacity;
	}
	/**
	 * 
	 * setter function
	 * @param String $capacity
	 */
	public function setCapacity($capacity)
	{
		$this->mCapacity=$capacity;
	}
	/**
	 * 
	 * getter function
	 */
	public function getDescription()
	{
		return $this->mDescription;
	}
	/**
	 * 
	 * setter function
	 * @param String $description
	 */
	public function setDescription($description)
	{
		$this->mDescription=$description;
	}
	/**
	 * 
	 * getter function
	 */
	public function getStartDate()
	{
		return $this->mStartDate;
	}
	/**
	 * 
	 * setter function
	 * @param String $startDate
	 */
	public function setStartDate($startDate)
	{
		$this->mStartDate=$startDate;
	}
	/**
	 * 
	 * getter function
	 */
	public function getEndDate()
	{
		return $this->mEndDate;
	}
	/**
	 * 
	 * setter function
	 * @param String $endDate
	 */
	public function setEndDate($endDate)
	{
		$this->mEndDate=$endDate;
	}
	/**
	 * 
	 * getter function
	 */
	public function getVenue()
	{
		return $this->mEvents;
	}
	/**
	 * 
	 * setter function
	 * @param String $venue
	 */
	public function setVenue($venue)
	{
		$this->mVenue=$venue;
	}
	/**
	 * 
	 * getter function
	 */
	public function getPages()
	{
		return $this->mPages;
	}
	/**
	 * 
	 * setter function
	 * @param Array $pages
	 */
	public function setPages($pages)
	{
		$this->mPages=$pages;
	}
}