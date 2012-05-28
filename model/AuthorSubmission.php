<?php
class AuthorSubmission
{
	/**
	 * 
	 * page_id of the submission wiki page
	 * @var Int
	 */
	private $mSubmissionId;
	/**
	 * 
	 * page_id of the sub-author wiki page
	 * @var Int
	 */
	private $mAuthorId;
	/**
	 * 
	 * title of the submission
	 * @var String
	 */
	private $mTitle;
	/**
	 * 
	 * Type of submission
	 * @var String
	 */
	private $mType;
	/**
	 * 
	 * Abstract for the submission
	 * @var String
	 */
	private $mAbstract;
	/**
	 * 
	 * Track under which this submission can be categorized
	 * @var String
	 */
	private $mTrack;
	/**
	 * 
	 * Length of the presentation(it will be in minutes, storing it as a String)
	 * @var String
	 */
	private $mLength;
	/**
	 * 
	 * Some extra slides info for the submission
	 * @var String
	 */
	private $mSlidesInfo;
	/**
	 * 
	 * Slot which is requested by the author
	 * @var unknown_type
	 * @todo still need to decide how are slots created for the conference
	 */
	private $mSlotRequest;
	/**
	 * 
	 * @param  Int $id
	 * @param  Int  $aid - page_id of the sub-author wiki page
	 * @param String $title
	 * @param String $type
	 * @param String $abstract
	 * @param String $track
	 * @param Int    $length
	 * @param String $slidesInfo
	 * @param String $slotReq
	 */
	public function __construct($id, $aid, $title,$type,$abstract, $track, $length, $slidesInfo, $slotReq)
	{
		$this->mSubmissionId=$id;
		$this->mAuthorId=$aid;
		$this->mTitle=$title;
		$this->mType=$type;
		$this->mAbstract=$abstract;
		$this->mTrack=$track;
		$this->mLength=$length;
		$this->mSlidesInfo=$slidesInfo;
		$this->mSlotRequest=$slotReq;
	}
		/**
	 * @param $aid Int page_id of the sub-author page
 	 * @param $title String title of the submission
 	 * @param $type String type of the submission(presentation, seminar...)
	 * @param $abstract String
	 * @param $track String the track to which this track belongs to
	 * @param $length Int length of the presentation
	 * @param $slidesInfo String extra slides info
	 * @param $slotReq String request for specific slot
	 * @return New AuthorSubmission object
	 */
	public static function createFromScratch($aid, $title,$type,$abstract, $track, $length, $slidesInfo, $slotReq)
	{
		$conferenceTitle=ConferenceAuthorUtils::getConferenceTitleFromSubAuthor($aid);
		$username=ConferenceAuhorUtils::getUsernameFromSubAuthor($aid);
		$titleSub=$conferenceTitle.'/authors/'.$username.'/submissions/'.$title;
		$titleObj=Title::newFromText($titleSub);
		$pageObj=WikiPage::factory($titleObj);
		$text=Xml::element('submission',array('title'=>$title,'submissionType'=>$type,'abstract'=>$abstract,
		'track'=>$track,'length'=>$length,'slidesInfo'=>$slidesInfo,'slotReq'=>$slotReq,'cvext-submission-author'=>$aid));
		$status=$page->doEdit($text, 'new submission added',EDIT_NEW);	
		if($status->value['revision'])
		{
			$revision=$status->value['revision'];
			$submissionId=$revision->getPage();
			$dbw=wfGetDB(DB_MASTER);
			$properties=array('cvext-submission-author'=>$aid);
			foreach($properties as $name=>$value)
			{
				$dbw->insert('page_props',array('pp_page'=>$submissionId,'pp_propname'=>$name,'pp_value'=>$value),__METHOD__,array());
			}
		
			return new self($submissionId, $aid, $title,$type,$abstract, $track, $length, $slidesInfo, $slotReq);
		}
		else
		{
			//it means revision could not be created
			//need to decide on what should be done
			//return new self(null,$aid,$title,$type,$abstract,$track,$length,$slidesInfo,$slotReq);
		}
	}
	/**
	 * @param Int $submissionId - page_id of the submission page
	 * @return AuthorSubmission
	 */
	public static function loadFromId($submissionId)
	{
		$article=Article::newFromID($submissionId);
		$text=$article->fetchContent();
		preg_match_all("/<submission title=\"(.*)\" submissionType=\"(.*)\" abstract=\"(.*)\" track=\"(.*)\" 
		length=\"(.*)\" slidesInfo=\"(.*)\" slotReq=\"(.*)\" cvext-submission-author=\"(.*)\" \/>/",$text,$matches);
		/*wfProfileIn(__METHOD__.'-db');
		$dbr=wfGetDB(DB_SLAVE);
		$res = $dbr->selectRow( 'page_props',
		array('pp_propertyname','pp_value'),
		array( 'pp_page' => $submissionId,'pp_propertyname'=>'parent'),
		__METHOD__,
		array()
		);
		wfProfileOut(__METHOD__.'-db');*/
		return new self($submissionId, $matches[8][], $matches[1][0], $matches[2][0], $matches[3][0], $matches[4][0], 
		$matches[5][0], $matches[6][0], $slotReq[7][0]);
	}
	/**
	 * 
	 * Parser Hook function
	 * @param unknown_type $input
	 * @param unknown_type $args
	 * @param unknown_type $parser
	 * @param unknown_type $frame
	 */
	public static function render($input, array $args, Parser $parser, PPFrame $frame)
	{
		//extract all the relevant info and store it in the page_props
		$submissionId=$parser->getTitle()->getArticleId();
		if($submissionId!=0)
		{
			$dbw=wfGetDB(DB_MASTER);
			$dbw->insert('page_props',array('pp_page'=>$submissionId,'pp_propname'=>'cvext-submission-author'
			,'pp_value'=>$args['cvext-submission-author']));
		}
		return '';
	}
	/**
	 * 
	 * getter function
	 */
	public function getId()
	{
		return $this->mSubmissionId;
	}
	/**
	 * 
	 * setter function
	 * @param Int $id
	 */
	public function setId($id)
	{
		$this->mSubmissionId=$id;
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
	public function getType()
	{
		return $this->mType;
		
	}
	/**
	 * 
	 * setter function
	 * @param String $type
	 */
	public function setType($type)
	{
		$this->mType=$type;
	}
	/**
	 * 
	 * getter function
	 */
	public function getAbstract()
	{
		return $this->mAbstract;
	}
	/**
	 * 
	 * getter function
	 */
	public function getTrack()
	{
		return $this->mTrack;
	}
	/**
	 * 
	 * getter function
	 */
	public function getLength()
	{
		return $this->mLength;
	}
	/**
	 * 
	 * setter function
	 * @param String $abstract
	 */
	public function setAbstract($abstract)
	{
		$this->mAbstract=$abstract;
	}
	/**
	 * 
	 * setter function
	 * @param String $track
	 */
	public function setTrack($track)
	{
		$this->mTrack=$track;
	}
	/**
	 * 
	 * setter function
	 * @param String(actually it will be a number) $length
	 */
	public function setLength($length)
	{
		$this->mLength=$length;
	}
}