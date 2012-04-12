<?php
class ConferenceAuthor
{
	private $mAuthorId,$mConferenceId,$mUserId,$mCountry,$mAffiliation,$mBlogUrl,$mSubmissions;

	public function __construct($aid=null, $cid, $uid, $country , $affiliation, $url,$submissions=null){
		$this->mAffiliation=$affiliation;
		$this->mAuthorId=$aid;
		$this->mBlogUrl=$url;
		$this->mConferenceId=$cid;
		$this->mUserId=$uid;
		$this->mSubmissions=$submissions;

	}
	public function getCountry()
	{
		return $this->mCountry;
	}
	public function setCounry($county)
	{
		$this->mCountry=$country;
	}
	public function getAffiliation()
	{
		return $this->mAffiliation;
	}
	public function setAffiliation($aff)
	{
		$this->mAffiliation=$aff;
	}
	public function getBlogUrl()
	{
		return $this->mBlogUrl;
	}
	public function setBlogUrl($url)
	{
		$this->mBlogUrl=$url;
	}
	/**
	 * @param Int $cid page_id of the conference page
	 * @param Int $uid user_id for the speaker
	 * @param String $country
	 * @param String $affiliation
	 * @param String $url
	 * @param Object(AuthorSubmission) $submission
	 * @return ConferenceAuthor
	 */
	public static function createFromScratch($cid, $uid, $country , $affiliation, $url,$submission=null)
	{
		$titleObj=Title::newFromText($title);
		$pageObj=WikiPage::factory($titleObj);
		$text=Xml::element('speaker',array('country'=>$country,'affiliation'=>$affiliation,'blogUrl'=>$url,'speaker-conf'=>$cid,
		'speaker-user'=>$uid));
		$status=$page->doEdit($text, 'new submission added',EDIT_NEW);
		if($status['revision'])
		$revision=$status['revision'];
		$id=$revision->getPage();
		$submission=AuthorSubmission::createFromScratch($id, $submission->getTitle(), $submission->getType(), 
		$submission->getAbstract(), $submission->getTrack(), $submission->getLength(), $submission->getSlidesInfo(), 
		$submission->getSlotReq);
		$properties=array('speaker-conf'=>$cid,'speaker-user'=>$uid);
		$dbw=wfGetDB(DB_MASTER);
		foreach($properties as $name=>$value)
		{
			$dbw->insert('page_props',array('pp_page'=>$id,'pp_propertyname'=>$name,'pp_value'=>$value));
		}
		$submissions=array();
		$submissions[]=$submission;
		return new self($id,$cid, $uid, $country, $affiliation, $url,$submissions);
	}
	/**
	 * @param Int $speakerId page_id of the speaker page
	 * @return ConferenceAuthor
	 */
	public static function loadFromId($speakerId)
	{
		$article=Article::newFromID($speakerId);
		$text=$article->fetchContent();
		preg_match_all("/<speaker country=\"(.*)\" affiliation=\"(.*)\" blogUrl=\"(.*)\" speaker-conf=\"(.*)\" speaker-user=\"(.*)\" \/>/"
		,$text,$matches);
		/*$dbr=wfGetDB(DB_SLAVE);
		$dbr->select('page_props',
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
		$dbr->select('page_props',
		array('pp_page'),
		array('pp_propertyname'=>'submission-author','pp_value'=>$speakerId),
		__METHOD__,
		array());
		$submissions=array();
		foreach($res as $row)
		{
			$submissions[]=AuthorSubmission::loadFromId($row->pp_page);	
		}
		return new self($matches[4][0], $matches[5][0], $matches[1][0], $matches[2][0], $matches[3][0],$submissions);
	}
	public function getAuthorId()
	{
		return $this->mAuthorId;
	}
	public function setAuthorId($id)
	{
		$this->mAuthorId=$id;
	}
	public function getConferenceId()
	{
		return $this->mConferenceId;
	}
	public function setconferenceId($id)
	{
		$this->mConferenceId=$id;
	}
	public function getUserId()
	{
		return $this->mUserId;
	}
	public function setUserId($id)
	{
		$this->mUserId=$id;
	}
	public function getSubmissions()
	{
		return $this->mSubmissions;
	}
	public function setSubmissions($submissions)
	{
		$this->mSubmissions=$submissions;
	}

}