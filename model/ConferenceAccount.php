<?php
class ConferenceAccount
{
	private $mConferenceIds,$mAccountId,$mUserId,$mGender, $mFirstName, $mLastName,$mPassportInfo, $mRegistrations;

	public function __construct($mAccountId=null,$mConferenceIds=array(),$mUserId,$mGender, $mFirstName, $mLastName,$mPassportInfo=null, 
	$mRegistrations=null,$mChildren=null){
		$this->mConferenceIds=$mConferenceIds;
		$this->mAccountId=$mAccountId;
		$this->mUserId=$mUserId;
		$this->mGender=$mGender;
		$this->mFirstName=$mFirstName;
		$this->mLastName=$mLastName;
		$this->mPassportInfo=$mPassportInfo;
		$this->mRegistrations=$mRegistrations;

	}
	public function getGender()
	{
		return $this->mGender;
	}
	public function setGender($gender)
	{
		$this->mGender=$gender;
	}
	public function getFirstName()
	{
		return $this->mFirstName;
	}
	public function setFirstName($name)
	{
		$this->mFirstName=$name;
	}
	public function getLastName()
	{
		return $this->mLastName;
	}
	public function setLastName($name)
	{
		$this->mLastName=$name;
	}
	public function getPassportInfo()
	{
		return $this->mPassportInfo;
		
	}
	public function setPassportInfo($info)
	{
		$this->mPassportInfo=$info;
	}
	public function getRegistrations()
	{
		return $this->mRegistrations;
	
	}
	public function setRegistrations($registrations)
	{
		$this->mRegistrations=$registrations;
	}
	/**
	 * @param Int $mConferenceId
	 * @param Int $mUserId
	 * @param String$mGender
	 * @param String $mFirstName
	 * @param String $mLastName
	 * @param Object(ConferencePassportInfo) $mPassportInfo
	 * @param Object(ConferenceRegistration) $mRegistration
	 * @return ConferenceAccount
	 */
	public static function createFromScratch($mConferenceId,$mUserId,$mGender, $mFirstName, $mLastName,$mPassportInfo,$mRegistration=null)
	{
		//this is revised approach for storing account objects
		$newParent=ConferenceAccount::hasParentAccount($mUserId);
		if($newParent)
		{
			//create a new title
			$titleParent='';
		}
		else
		{
			//use an old one
			$titleParent='';
		}
		$titleParentObj=Title::newFromText($titleParent);
		$pageParentObj=WikiPage::factory($titleParentObj);
		if($newParent)
		{
		$parentText=Xml::element('account',array('gender'=>$mGender,'firstName'=>$mFirstName,'lastName'=>$mLastName,
		'cvext-account-user'=>$mUserId));
		$statusParent=$pageParentObj->doEdit($parentText, 'new parent account added',EDIT_NEW);
		if($statusParent['revision'])
		$revision=$statusParent['revision'];
		$id=$revision->getPage();
		}
		else
		{
			if($pageParentObj->exists())
			{
				$id=$pageParentObj->getId();
			}
		}
		$titleChildObj=Title::newFromText($titleChild);
		$pageChildObj=WikiPage::factory($titleChildObj);
		$childText=Xml::element('account-sub',array('cvext-account-parent'=>$id,'cvext-account-conf'=>$mConferenceId));
		$statusChild=$pageChildObj->doEdit($childText,'new sub account added',EDIT_NEW);
		$childId=$statusChild['revision']->getPage();
		//passport-info will be linked to the parent account page
		$mPassportInfo=ConferencePassportInfo::createFromScratch($mPassportInfo->getPassportNo(), $id,
		$passportInfo->getIssuedBy(), $mPassportInfo->getValidUntil(), $mPassportInfo->getPlace(),
		$mPassportInfo->getDOB(), $mPassportInfo->getCountry());
		$registrations=array();
		if(!is_null($mRegistration))
		{
			//we maintain a relationship between registration and account-sub page
			$mRegistration=ConferenceRegistration::createFromScratch($childId, $mRegistration->getType(),
			$mRegistration->getDietaryRestr(), $mRegistration->getOtherDietOpts(), $mRegistration->getOtherOpts(),
			$mRegistration->getBadgeInfo(), $mRegistration->getTransaction(), $mRegistration->getEvents());
			$registrations[]=$mRegistration;
		}
		$dbw=wfGetDB(DB_MASTER);
		$properties=array(array('id'=>$childId,'prop'=>'cvext-account-conf','value'=>$mConferenceId),array('id'=>$childId,'prop'=>'cvext-account-parent','value'=>$id));
		if($newParent)
		{
			$properties[]=array('id'=>$id,'prop'=>'cvext-account-user','value'=>$mUserId);
		}
		foreach ($properties as $value)
		{
			$dbw->insert('page_props',array('pp_page'=>$value['id'],'pp_propname'=>$value['prop'],'pp_value'=>$value['value']));
		}
		return new self($id,array($mConferenceId), $mUserId, $mGender, $mFirstName, $mLastName,$mPassportInfo,$registrations);
	}
	/**
	 * @param Int $accountId
	 * @return ConferenceAccount
	 */
	public static function loadFromId($accountId)
	{
		$article=Article::newFromID($accountId);
		$text=$article->fetchContent();
		preg_match_all("/<account gender=\"(.*)\" firstName=\"(.*)\" lastName=\"(.*)\" cvext-account-user=\"(.*)\" \/>/",
		$text,$matches);
		$dbr=wfGetDB(DB_SLAVE);
		$row=$dbr->selectRow('page_props',
		array('pp_page'),
		array('pp_propname'=>'cvext-passport-account','pp_value'=>$accountId),
		__METHOD__,
		array());
		/*$ids=array();
		 foreach($res as $row)
		 {
			$ids[]=$row->pp_page;
			}
			$passportRow=$dbr->selectRow('page_props',
			array('pp_page'),
			array('pp_page'=>$ids,'pp_value'=>'passport'));*/
		$passportInfo=ConferencePassportInfo::loadFromId($row->pp_page);
		$resultSub=$dbr->select('page_props',
		'*',
		array('pp_propname'=>'cvext-account-parent','pp_value'=>$accountId),
		__METHOD__,
		array());
		$subAccountIds=array();
		$conferenceIds=array();
		foreach ($resultSub as $row)
		{
			$subAccountIds[]=$row->pp_page;
		}
		$conferenceResult=$dbr->select('page_props',
		'*',
		array('pp_page IN ('.implode(',',$subAccountIds).')','pp_propname'=>'cvext-account-conf'),
		__METHOD__);
		foreach ($conferenceResult as $row)
		{
			$conferenceIds[]=$row->pp_value;
		}
		$res=$dbr->select('page_props',
		array('pp_page'),
		array('pp_value IN ('.implode(',',$subAccountIds).')','pp_propname'=>'cvext-registration-account'),
		__METHOD__,
		array());
		$registrations=array();
		foreach ($res as $row)
		{
			$registrations[]=ConferenceRegistration::loadFromId($row->pp_page);

		}
		
		return new self($accountId,$conferenceIds,$matches[4][0],$matches[1][0], $matches[2][0], $matches[3][0],$passportInfo, $registrations);


	}
	public static function render($input, array $args, Parser $parser, PPFrame $frame)
	{
		
		//now we have to re-set these values into page_props table
		$dbw=wfGetDB(DB_MASTER);
		$title=$parser->getTitle();
		$accountId=$title->getArticleId();
		//$page_props=array('account-conf'=>$conferenceId,'account-user'=>$userId);
		$dbw->insert('page_props',array('pp_page'=>$accountId,'pp_propname'=>'cvext-account-user','pp_value'=>$args['cvext-account-user']));
		return '';
	}
	public static function renderSub($input, array $args, Parser $parser, PPFrame $frame)
	{
		$dbw=wfGetDB(DB_MASTER);
		$title=$parser->getTitle();
		$accountSubId=$title->getArticleId();
		foreach($args as $attribute=>$value)
		{
			$dbw->insert('page_props',array('pp_page'=>$accountSubId,'pp_propname'=>$attribute,'pp_value'=>$value));
		}
	}
	
	public function getConferenceId()
	{
		$this->mConferenceId;
	}
	public function setConferenceId($id)
	{
		$this->mConferenceId=$id;
	}
	public function getAccountId()
	{
		$this->mAccountId;
	}
	public function setAccountId($id)
	{
		$this->mAccountId=$id;
	}
	public function getUserId()
	{
		return $this->mUserId;
	}
	public function setUserId($id)
	{
		$this->mUserId=$id;
	}

}