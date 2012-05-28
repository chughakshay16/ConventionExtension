<?php
class ConferencePassportInfo
{
	/**
	 * 
	 * page_id of the passport wiki page
	 * @var Int
	 */
	private $mId;
	/**
	 * 
	 * passport no.
	 * @var String (it would actually be a number but we will store it as a string)
	 */
	private $mPassportNo;
	/**
	 * 
	 * page_id of the parent account page
	 * @var Int
	 */
	private $mAccountId;
	/**
	 * 
	 * Date when this passport was issued
	 * @var String (in date format ddMMYYYY)
	 */
	private $mIssuedBy;
	/**
	 * 
	 * Uptil what date will it be valid
	 * @var String (in date format ddMMYYYY)
	 */
	private $mValidUntil;
	/**
	 * 
	 * Place where this was issued
	 * @var String
	 */
	private $mPlace;
	/**
	 * 
	 * Date of Birth
	 * @var String (in date format ddMMYYYY)
	 */
	private $mDob;
	/**
	 * 
	 * Country where this passport was issued
	 * @var String(3 digit country code)
	 */
	private $mCountry;
	/**
	 * 
	 * Constructor function
	 * @param Int $id
	 * @param String $pno
	 * @param Int $aid
	 * @param String(date format) $iby
	 * @param String(date format) $vu
	 * @param String $pl
	 * @param String(date format) $dob
	 * @param String $ctry
	 */
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
		'cvext-passport-account'=>$aid));
		$status=$page->doEdit($text, 'new passport added',EDIT_NEW);	
		if($status->value['revision'])
		{
			$revision=$status->value['revision'];
			$id=$revision->getPage();
			$dbw=wfGetDB(DB_MASTER);
			$properties=array('cvext-passport-account'=>$aid);
			foreach($properties as $name=>$value)
			{
				$dbw->insert('page_props',array('pp_page'=>$id,'pp_propertyname'=>$name,'pp_value'=>$value));
			}
			if($dbw->affectedRows())
				return new self($id,$pno, $aid, $iby, $vu, $pl, $dob, $ctry);
			else 
				return null;
		}
		else
		{
		//do something here
		}
		
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
		cvext-passport-account=\"(.*)\" \/>/",$text,$matches);
		/*$dbr=wfGetDB(DB_SLAVE);
		$row=$dbr->selectRow('page_props',
		array('pp_value'),
		array('pp_page'=>$passportId,'pp_propertyname'=>'parent'),
		__METHOD__,
		array());*/
		return new self($passportId,$matches[1][0],$matches[7][0], $matches[6][0], $matches[2][0], $matches[3][0], $matches[4][0], 
		$matches[5][0]);
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
		$id=$parser->getTitle()->getArticleId();
		if($id!=0)
		{
			wfGetDB(DB_MASTER)->insert('page_props', array('pp_page'=>$id,'pp_propname'=>'cvext-passport-account','pp_value'=>$args['cvext-passport-account']));
		}
		return '';
	}
	/**
	 * 
	 * getter function
	 */
	public function getId()
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
	public function getAccountId()
	{
		return $this->mAccountId;
	}
	/**
	 * 
	 * setter function
	 * @param Int $id
	 */
	public function setAccountId($id)
	{
		$this->mAccountId=$id;
	}
	/**
	 * 
	 * getter function
	 */
	public function getPassportNo()
	{
		return $this->mPassportNo;
	}
	/**
	 * 
	 * setter function
	 * @param String $no
	 */
	public function setPasssportNo($no)
	{
		$this->mPassportNo=$no;
	}
	/**
	 * 
	 * getter function
	 */
	public function getIssuedBy()
	{
		return $this->mIssuedBy;
	}
	/**
	 * 
	 * setter function
	 * @param String $issued
	 */
	public function setIssuedBy($issued)
	{
		$this->mIssuedBy=$issued;
	}
	/**
	 * 
	 * getter function
	 */
	public function getValidUntil()
	{
		return $this->mValidUntil;
	}
	/**
	 * 
	 * setter function
	 * @param String $valid
	 */
	public function setValidUntil($valid)
	{
		$this->mValidUntil=$valid;
	}
	/**
	 * 
	 * getter function
	 */
	public function getPlace()
	{
		return $this->mPlace;
	}
	/**
	 * 
	 * setter function
	 * @param String $place
	 */
	public function setPlace($place)
	{
		$this->mPlace=$place;
	}
	/**
	 * 
	 * getter function
	 */
	public function getDOB()
	{
		return $this->mDob;
	}
	/**
	 * 
	 * setter function
	 * @param String $dob
	 */
	public function setDOB($dob)
	{
		$this->mDob=$dob;
	}
	/**
	 * 
	 * getter function
	 */
	public function getCountry()
	{
		return $this->mCountry;
	}
	/**
	 * 
	 * setter function
	 * @param String $country
	 */
	public function setCountry($country)
	{
		$this->mCountry=$country;
	}
}