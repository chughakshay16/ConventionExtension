<?php
class ConferenceAuthor
{
	private $mAuthorId,$mConferenceIds,$mUserId,$mCountry,$mAffiliation,$mBlogUrl,$mSubmissions;

	public function __construct($aid=null, $cids=array(), $uid, $country , $affiliation, $url,$submissions=null){
		$this->mAffiliation=$affiliation;
		$this->mAuthorId=$aid;
		$this->mBlogUrl=$url;
		$this->mConferenceIds=$cids;
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
		$titleChildObj=Title::newFromText($titleChild);
		$pageChildObj=WikiPage::factory($titleChildObj);
		$text=Xml::element('author',array('country'=>$country,'affiliation'=>$affiliation,'blogUrl'=>$url,
		'cvext-author-user'=>$uid));
		$status=$page->doEdit($text, 'new parent author added',EDIT_NEW);
		if($status->value['revision'])
		$revision=$status->value['revision'];
		$id=$revision->getPage();
		$childText=Xml::element('author-sub',array('cvext-author-parent'=>$id,'cvext-author-conf'=>$cid));
		$statusChild=$pageChildObj->doEdit($childText,'new sub author added',EDIT_NEW);
		$revisionChild=$statusChild->value['revision'];
		$idChild=$revisionChild->getPage();
		$submission=AuthorSubmission::createFromScratch($idChild, $submission->getTitle(), $submission->getType(), 
		$submission->getAbstract(), $submission->getTrack(), $submission->getLength(), $submission->getSlidesInfo(), 
		$submission->getSlotReq());
		$properties=array(array('id'=>$id,'prop'=>'cvext-author-user','value'=>$uid),array('id'=>$childId,'prop'=>'cvext-author-parent','value'=>$id),array('id'=>$childId,'prop'=>'cvext-author-conf','value'=>$cid));
		$dbw=wfGetDB(DB_MASTER);
		foreach($properties as $value)
		{
			$dbw->insert('page_props',array('pp_page'=>$value['id'],'pp_propertyname'=>$value['prop'],'pp_value'=>$value['value']));
		}
		$submissions=array();
		$submissions[]=$submission;
		return new self($id,$cid, $uid, $country, $affiliation, $url,$submissions);
	}
	/**
	 * @param Int $speakerId page_id of the speaker page
	 * @return ConferenceAuthor
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
	public static function render($input, array $args, Parser $parser, PPFrame $frame)
	{

		$dbw=wfGetDB(DB_MASTER);
		$authorId=$parser->getTitle()->getArticleId();
				$dbw->insert('page_props',array('pp_page'=>$authorId,'pp_propname'=>'cvext-author-user','pp_value'=>$args['cvext-author-user']));
		return '';
	}
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
	public function getAuthorId()
	{
		return $this->mAuthorId;
	}
	public function setAuthorId($id)
	{
		$this->mAuthorId=$id;
	}
	public function getConferenceIds()
	{
		return $this->mConferenceIds;
	}
	public function setconferenceIds($id)
	{
		$this->mConferenceIds=$id;
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