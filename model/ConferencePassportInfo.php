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
	/**
	 * @param String $pno passport number
	 * @param Int $aid page_id of the account page
	 * @param String $iby issued by
	 * @param String(date) $vu valid until
	 * @param String $pl place
	 * @param String(date) $dob date of birth
	 * @param String $ctry country
	 * @return ConferencePassportInfo
	 */
	public static function createFromScratch($pno,$aid,$iby,$vu,$pl,$dob,$ctry)
	{
		$titleObj=Title::newFromText($title);
		$pageObj=WikiPage::factory($titleObj);
		$text=Xml::element('passport',array('number'=>$pno,'validUntil'=>$vu,'place'=>$pl,'dob'=>$dob,'country'=>$ctry,'issuedBy'=>$iby,
		'passport-account'=>$aid));
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
	/**
	 * @param Int $passportId page_id of the passport info page
	 * @return ConferencePassportInfo
	 */
	public static function loadFromId($passportId)
	{
		$article=Article::newFromID($organizerId);
		$text=$article->fetchContent();
		preg_match_all("/<passport number=\"(.*)\" validUntil=\"(.*)\" place=\"(.*)\" dob=\"(.*)\" country=\"(.*)\" issuedBy=\"(.*)\" 
		passport-account=\"(.*)\" \/>/",$text,$matches);
		/*$dbr=wfGetDB(DB_SLAVE);
		$row=$dbr->selectRow('page_props',
		array('pp_value'),
		array('pp_page'=>$passportId,'pp_propertyname'=>'parent'),
		__METHOD__,
		array());*/
		return new self($passportId,$matches[1][0],$matches[7][0], $matches[6][0], $matches[2][0], $matches[3][0], $matches[4][0], 
		$matches[5][0]);
	}
	public static function render($input, array $args, Parser $parser, PPFrame $frame)
	{
		wfGetDB(DB_MASTER)->insert('page_props', array('pp_page'=>$parser->getTitle()->getArticleId(),'pp_propname'=>'passport-account','pp_value'=>$args['passport-account']));
		return '';
	}
	public function getId()
	{
		return $this->mId;
	}
	public function setId($id)
	{
		$this->mId=$id;
	}
	public function getAccountId()
	{
		return $this->mAccountId;
	}
	public function setAccountId($id)
	{
		$this->mAccountId=$id;
	}
	public function getPassportNo()
	{
		return $this->mPassportNo;
	}
	public function setPasssportNo($no)
	{
		$this->mPassportNo=$no;
	}
	public function getIssuedBy()
	{
		return $this->mIssuedBy;
	}
	public function setIssuedBy($issued)
	{
		$this->mIssuedBy=$issued;
	}
	public function getValidUntil()
	{
		return $this->mValidUntil;
	}
	public function setValidUntil($valid)
	{
		$this->mValidUntil=$valid;
	}
	public function getPlace()
	{
		return $this->mPlace;
	}
	public function setPlace($place)
	{
		$this->mPlace=$place;
	}
	public function getDOB()
	{
		return $this->mDob;
	}
	public function setDOB($dob)
	{
		$this->mDob=$dob;
	}
	public function getCountry()
	{
		return $this->mCountry;
	}
	public function setCountry($country)
	{
		$this->mCountry=$country;
	}
}