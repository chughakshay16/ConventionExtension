<?php
class ConferenceAuthor
{
	/**
	 * 
	 * The page_id of the parent author
	 * @var Int
	 */
	private $mAuthorId;
	/**
	 * 
	 * An array containing the ids of confernces for which this author has submitted a proposal
	 * @var Array
	 */
	private $mConferenceIds;
	/**
	 * 
	 * user_id for the parent author
	 * @var Int
	 */
	private $mUserId;
	/**
	 * 
	 * Country which author belongs to
	 * @var String
	 */
	private $mCountry;
	/**
	 * Author's country
	 * @var String
	 */
	private $mAffiliation;
	/**
	 * 
	 * Author's blog url
	 * @var unknown_type
	 */
	private $mBlogUrl;
	/**
	 * 
	 * An array containing the proposals made by this author(it includes the proposals from all the conferences)
	 * @var Array
	 */
	private $mSubmissions;
	/**
	 * 
	 * Enter description here ...
	 * @param Int $aid
	 * @param Array $cids
	 * @param Int $uid
	 * @param String $country
	 * @param String $affiliation
	 * @param String $url
	 * @param Array $submissions
	 */
	public function __construct($aid=null, $cids=array(), $uid, $country , $affiliation, $url,$submissions=null){
		$this->mAffiliation=$affiliation;
		$this->mAuthorId=$aid;
		$this->mBlogUrl=$url;
		$this->mConferenceIds=$cids;
		$this->mUserId=$uid;
		$this->mSubmissions=$submissions;

	}
		/**
	 * @param Int $cid page_id of the conference page
	 * @param Int $uid user_id for the speaker
	 * @param String $country
	 * @param String $affiliation
	 * @param String $url
	 * @param Object(AuthorSubmission) $submission - this object only contains the info passed with the form, other author-id 
	 * and ids are set in this function itself
	 * @return ConferenceAuthor
	 * This is a bit different from how its implemented in ConferenceAccount
	 * So in cases where parent, or parent and child both are present this is the function which we would call
	 * For example , if we are adding a new submission for the already created parent and child authors we are gonna call this function with 
	 * the appropriate ConferenceSubmission object
	 * This function takes care of all the various possible scenarios that may occur in the creation of a ConferenceAuthor object
	 * 1. When the parent author is not present
	 * 2. When the parent author exists but child author absent
	 * 3. When both parent and child authors exist
	 */
	public static function createFromScratch($cid, $uid, $country , $affiliation, $url,$submission=null)
	{
		$newParent=!(ConferenceAuthorUtils::hasParentAuthor($uid));
		$confTitle=ConferenceUtils::getTitle($cid);
		$userName=UserUtils::getUsername($uid);
		$titleParent='/authors/'.$userName;
		$properties=array();
		$titleObj=Title::newFromText($title);
		$pageObj=WikiPage::factory($titleObj);
		if($newParent)
		{
			
			$text=Xml::element('author',array('country'=>$country,'affiliation'=>$affiliation,'blogUrl'=>$url,
			'cvext-author-user'=>$uid));
			$status=$pageObj->doEdit($text, 'new parent author added',EDIT_NEW);
			if($status->value['revision'])
			{
				$revision=$status->value['revision'];
				$id=$revision->getPage();
			
			} else {
			//do something here
			}
		} else {
			if($pageObj->exists())
			{
				$id=$pageObj->getId();
			}
		}
		$newChild=!(ConferenceAuthorUtils::hasChildAuthor($id, $cid));
		$titleChild=$confTitle.'/authors/'.$username;
		$titleChildObj=Title::newFromText($titleChild);
		$pageChildObj=WikiPage::factory($titleChildObj);
		if($newChild)
		{
			$childText=Xml::element('author-sub',array('cvext-author-parent'=>$id,'cvext-author-conf'=>$cid));
			$statusChild=$pageChildObj->doEdit($childText,'new sub author added',EDIT_NEW);
			if($statusChild->value['revision'])
			{
				$revisionChild=$statusChild->value['revision'];
				$idChild=$revisionChild->getPage();
				
				
			} else {
				//do something here
			}
		} else {
				if($pageChildObj->exists())
				{
					$id=$pageChildObj->getId();
				}
			
			}
			$submission=AuthorSubmission::createFromScratch($idChild, $submission->getTitle(), $submission->getType(), 
			$submission->getAbstract(), $submission->getTrack(), $submission->getLength(), $submission->getSlidesInfo(), 
			$submission->getSlotReq());
			if($newParent)
			{
				$properties[]=array('id'=>$id,'prop'=>'cvext-author-user','value'=>$uid);
				if($newChild)
				{
					$properties[]=array('id'=>$childId,'prop'=>'cvext-author-parent','value'=>$id);
					$properties[]=array('id'=>$childId,'prop'=>'cvext-author-conf','value'=>$cid);
				}
				
			}
			$dbw=wfGetDB(DB_MASTER);
			foreach($properties as $value)
			{
				$dbw->insert('page_props',array('pp_page'=>$value['id'],'pp_propname'=>$value['prop'],'pp_value'=>$value['value']));
			}
			
			$submissions=array();
			$submissions[]=$submission;
			return new self($id,$cid, $uid, $country, $affiliation, $url,$submissions);
		
	}
	/**
	 * @param Int $authorId (this is the page_id of the parent author)
	 * @return ConferenceAuthor
	 * This function loads the ConferenceAuthor object from the database
	 */
	public static function loadFromId($authorId)
	{
		$article=Article::newFromID($authorId);
		$text=$article->fetchContent();
		preg_match_all("/<author country=\"(.*)\" affiliation=\"(.*)\" blogUrl=\"(.*)\" cvext-author-user=\"(.*)\" \/>/"
		,$text,$matches);
		$dbr=wfGetDB(DB_SLAVE);
		/*$dbr->select('page_props',
		array('pp_propertyname','pp_ value'),
		array('pp_page'=>$speakerId),
		__METHOD__,
		array());
		foreach($res as $row)
		{
			if($row->pp_propertyname=='parent')
			$cid=$row->pp_value;
			else if($row->pp_value=='user')
			$uid=$row->pp_value;
			else {}
		}*/
		//get all the sub authors
		$resSub=$dbr->select('page_props',
		'*',
		array('pp_value'=>$authorId,'pp_propname'=>'cvext-author-parent'),
		__METHOD__,
		array());
		$subIds=array();
		foreach ($resSub as $row)
		{
			$subIds[]=$row->pp_page;
		}
		$resConf=$dbr->select('page_props',
		'*',
		array('pp_page IN ('.implode(',', $subIds).')','pp_propname'=>'cvext-author-conf'),
		__METHOD__);
		$conferenceIds=array();
		foreach ($resConf as $row)
		{
			$conferenceIds[]=$row->pp_value;
		}
		$res=$dbr->select('page_props',
		array('pp_page'),
		array('pp_value IN ('.implode(',',$subIds).')','pp_propertyname'=>'cvext-submission-author'),
		__METHOD__,
		array());
		$submissions=array();
		foreach($res as $row)
		{
			$submissions[]=AuthorSubmission::loadFromId($row->pp_page);	
		}
		return new self($authorId,$conferenceIds, $matches[4][0], $matches[1][0], $matches[2][0], $matches[3][0],$submissions);
	}
/**
	 * 
	 * 
	 * @param String $input - text contained within the tag
	 * @param Array $args - an array of tag attributes
	 * @param Parser $parser
	 * @param PPFrame $frame
	 */
	public static function render($input, array $args, Parser $parser, PPFrame $frame)
	{

		$dbw=wfGetDB(DB_MASTER);
		$authorId=$parser->getTitle()->getArticleId();
				$dbw->insert('page_props',array('pp_page'=>$authorId,'pp_propname'=>'cvext-author-user','pp_value'=>$args['cvext-author-user']));
		return '';
	}
	/**
	 * 
	 * @param String $input - text contained within the tag
	 * @param Array $args - an array of tag attributes
	 * @param Parser $parser
	 * @param PPFrame $frame
	 */
	public static function renderSub($input, array $args, Parser $parser, PPFrame $frame)
	{
		$dbw=wfGetDB(DB_MASTER);
		$authorId=$parser->getTitle()->getArticleId();
		$properties=array(array('id'=>$authorId,'prop'=>'cvext-author-conf','value'=>$args['cvext-author-conf']),array('id'=>$authorId,'prop'=>'cvext-author-parent','value'=>$args['cvext-author-parent']));
		foreach ($properties as $property)
		{
			$dbw->insert('page_props',array('pp_page'=>$property['id'],'pp_propname'=>$property['prop'],'pp_value'=>$property['value']));
		}
		return '';
	}
	/**
	 * 
	 * Returns the country value
	 */
	public function getCountry()
	{
		return $this->mCountry;
	}
	/**
	 * 
	 * Sets the country value
	 * @param String $county
	 */
	public function setCounry($county)
	{
		$this->mCountry=$country;
	}
	/**
	 * 
	 * Returns the affiliation value
	 */
	public function getAffiliation()
	{
		return $this->mAffiliation;
	}
	/**
	 * 
	 * Sets the affiliation value
	 * @param String $aff
	 */
	public function setAffiliation($aff)
	{
		$this->mAffiliation=$aff;
	}
	/**
	 * 
	 * Returns the url of the blog
	 */
	public function getBlogUrl()
	{
		return $this->mBlogUrl;
	}
	/**
	 * 
	 * Sets the blog url
	 * @param String $url
	 */
	public function setBlogUrl($url)
	{
		$this->mBlogUrl=$url;
	}
	/**
	 * 
	 * Returns the parent author id
	 */
	public function getAuthorId()
	{
		return $this->mAuthorId;
	}
	/**
	 * 
	 * Sets the parent author id
	 * @param Int $id
	 */
	public function setAuthorId($id)
	{
		$this->mAuthorId=$id;
	}
	/**
	 * 
	 * Returns an array of conference ids
	 */
	public function getConferenceIds()
	{
		return $this->mConferenceIds;
	}
	/**
	 * 
	 * Sets the value of the conference Ids
	 * @param Array $id
	 */
	public function setconferenceIds($id)
	{
		$this->mConferenceIds=$id;
	}
	/**
	 * 
	 * Returns the user_id for the author object
	 */
	public function getUserId()
	{
		return $this->mUserId;
	}
	/**
	 * 
	 * Sets the user_id for the author object
	 * @param unknown_type $id
	 */
	public function setUserId($id)
	{
		$this->mUserId=$id;
	}
	/**
	 * 
	 * Returns an array of submissions made by the author
	 */
	public function getSubmissions()
	{
		return $this->mSubmissions;
	}
	/**
	 * 
	 * Sets an array of submissions
	 * @param unknown_type $submissions
	 */
	public function setSubmissions($submissions)
	{
		$this->mSubmissions=$submissions;
	}

}