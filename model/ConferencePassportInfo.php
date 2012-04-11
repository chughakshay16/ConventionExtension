<?php
class ConferencePassportInfo
{
	private $mId,$mPassportNo,$mAccountId,$mIssuedBy,$mValidUntil,$mPlace,$mDob,$mCountry;
	
	public function __construct($id=null,$pno,$aid,$iby,$vu,$pl,$dob,$ctry)
	{
		$this->mPassportNo=$pno;
		$this->mIssuedBy=$iby;
		$this->mValidUntil=$vu;
		$this->mPlace=$pl;
		$this->mDob=$dob;
		$this->mCountry=$ctry;
		$this->mAccountId=$aid;
		$this->mId=$id;
	}
	public static function createFromScratch($pno,$aid,$iby,$vu,$pl,$dob,$ctry)
	{
		$titleObj=Title::newFromText($title);
		$pageObj=WikiPage::factory($titleObj);
		$text=Xml::element('passport',array('number'=>$pno,'validUntil'=>$vu,'place'=>$pl,'dob'=>$dob,'country'=>$ctry,'issuedBy'=>$iby,'passport-account'=>$aid));
		$status=$page->doEdit($text, 'new passport added',EDIT_NEW);	
		if($status['revision'])
		$revision=$status['revision'];
		$id=$revision->getPage();
		$dbw=wfGetDB(DB_MASTER);
		$properties=array('passport-account'=>$aid);
		foreach($properties as $name=>$value)
		{
			$dbw->insert('page_props',array('pp_page'=>$id,'pp_propertyname'=>$name,'pp_value'=>$value));
		}
		if($dbw->affectedRows())
		return new self($id,$pno, $aid, $iby, $vu, $pl, $dob, $ctry);
		
	}
	public static function loadFromId($passportId)
	{
		$article=Article::newFromID($organizerId);
		$text=$article->fetchContent();
		/**
		 * parse content
		 */
		/*$dbr=wfGetDB(DB_SLAVE);
		$row=$dbr->selectRow('page_props',
		array('pp_value'),
		array('pp_page'=>$passportId,'pp_propertyname'=>'parent'),
		__METHOD__,
		array());*/
		return new self($passportId,$pno,$aid, $iby, $vu, $pl, $dob, $ctry);
	}
	public function getId()
	{
		return $this->mId;
	}
}