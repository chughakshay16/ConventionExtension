<?php
class ConferenceAccount
{
	private $mConferenceId,$mAccountId,$mUserId,$mGender, $mFirstName, $mLastName,$mPassportInfo, $mRegistrations;
	
	public function __construct($mAccountId=null,$mConferenceId,$mUserId,$mGender, $mFirstName, $mLastName,$mPassportInfo=null, $mRegistrations=null){
		$this->mConferenceId=$mConferenceId;
		$this->mAccountId=$mAccountId;
		$this->mUserId=$mUserId;
		$this->mGender=$mGender;
		$this->mFirstName=$mFirstName;
		$this->mLastName=$mLastName;
		$this->mPassportInfo=$mPassportInfo;
		$this->mRegistrations=$mRegistrations;

	}
	public static function createFromScratch($mConferenceId,$mUserId,$mGender, $mFirstName, $mLastName,$mPassportInfo,$mRegistration=null)
	{
		$titleObj=Title::newFromText($title);
		$pageObj=WikiPage::factory($titleObj);
		$text=Xml::element('account',array('gender'=>$mGender,'firstName'=>$mFirstName,'lastName'=>$mLastName,'account-conf'=>$conferenceId,'account-user'=>$mUserId));
		$status=$page->doEdit($text, 'new account added',EDIT_NEW);	
		if($status['revision'])
		$revision=$status['revision'];
		$id=$revision->getPage();
		$mPassportInfo=ConferencePassportInfo::createFromScratch($mPassportInfo->getPassportNo(), $id, 
		$passportInfo->getIssuedBy(), $mPassportInfo->getValidUntil(), $mPassportInfo->getPlace(), 
		$mPassportInfo->getDOB(), $mPassportInfo->getCountry());
		$registrations=array();
		if(!is_null($mRegistration))
		{
		
			$mRegistration=ConferenceRegistration::createFromScratch($id, $mRegistration->getType(), 
			$mRegistration->getDietaryRestr(), $mRegistration->getOtherDietOpts(), $mRegistration->getOtherOpts(),
			$mRegistration->getBadgeInfo(), $mRegistration->getTransaction(), $mRegistration->getEvents());
			$registrations[]=$mRegistration;
		}
		$dbw=wfGetDB(DB_MASTER);
		$properties=array('account-conf'=>$conferenceId,'account-user'=>$mUserId);
		foreach ($properties as $name=>$value)
		{
			$dbw->insert('page_props',array('pp_page'=>$id,'pp_propname'=>$name,'pp_value'=>$value));
		}
		return new self($id,$mConferenceId, $mUserId, $mGender, $mFirstName, $mLastName,$mPassportInfo,$registrations);	
	}
	public static function loadFromId($accountId)
	{
		$article=Article::newFromID($accountId);
		$text=$article->fetchContent();
		/**
		 * parse the text
		 * it will contain $mConferenceId, $mUserId, $mGender, $mFirstName, $mLastName
		 */
		$dbr=wfGetDB(DB_SLAVE);
		$row=$dbr->selectRow('page_props',
		array('pp_page'),
		array('pp_propname'=>'passport-account','pp_value'=>$accountId),
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
		$res=$dbr->select('page_props',
		array('pp_page'),
		array('pp_propname'=>'registration-account','pp_value'=>$accountId),
		__METHOD__,
		array());
		$registrations=array();
		foreach ($res as $row)
		{
				$registrations[]=ConferenceRegistration::loadFromId($row->pp_page);
			
		}
		return new self($accountId,$mConferenceId,$mUserId,$mGender, $mFirstName, $mLastName,$passportInfo, $registrations);
		
		
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