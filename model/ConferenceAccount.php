<?php
class ConferenceAccount
{
	/**
	 * 
	 * Array containing the conference ids associated with this account object
	 * @var Array
	 */
	private $mConferenceIds;
	/**
	 * 
	 * page_id for this account object 
	 * @var Int
	 */
	private $mAccountId;
	/**
	 * 
	 * user_id of the user to which this account object belongs to
	 * @var Int
	 */
	private $mUserId;
	/**
	 * 
	 * Gender of the account holder
	 * @var String
	 */
	private $mGender;
	/**
	 * 
	 * First Name of the account holder
	 * @var String
	 */
	private $mFirstName;
	/**
	 * 
	 * Last Name of the account holder
	 * @var String
	 */
	private $mLastName;
	/**
	 * 
	 * ConferencePassportInfo object associated with this account 
	 * @var Object
	 */
	private $mPassportInfo;
	/**
	 * 
	 * It stores all the registrations made by the account holder(registrations from all the conferences)
	 * @var Array
	 */
	private $mRegistrations;
	/**
	 * 
	 * Constructor function
	 * generally called from ConferenceAccount::createFromScratch() or ConferenceAccount::loadFromId()
	 * @param Int $mAccountId
	 * @param Array $mConferenceIds
	 * @param Int $mUserId
	 * @param String $mGender
	 * @param String $mFirstName
	 * @param String $mLastName
	 * @param Object $mPassportInfo
	 * @param Array $mRegistrations
	 */
	public function __construct($mAccountId=null,$mConferenceIds=array(),$mUserId,$mGender, $mFirstName, $mLastName,$mPassportInfo=null, 
	$mRegistrations=null){
		$this->mConferenceIds=$mConferenceIds;
		$this->mAccountId=$mAccountId;
		$this->mUserId=$mUserId;
		$this->mGender=$mGender;
		$this->mFirstName=$mFirstName;
		$this->mLastName=$mLastName;
		$this->mPassportInfo=$mPassportInfo;
		$this->mRegistrations=$mRegistrations;

	}
	/**
	 * 
	 * This function creates a ConferenceAccount object with an empty registrations array.
	 * @param Int $mUserId
	 * @param String $mGender
	 * @param String $mFirstName
	 * @param String $mLastName
	 * @param Object $mPassportInfo - this object contains all the passport details except the page_id for the 
	 * passport page, which is created during this function and is set up with the passed passport info object
	 */
	public static function createFromScratch($mUserId,$mGender, $mFirstName, $mLastName,$mPassportInfo)
	{
		$username=UserUtils::getUsername($mUserId);
		$accountTitle='accounts/'.$username;
		$title=Title::newFromText($accountTitle);
		$page=WikiPage::factory($title);
		$accountText=Xml::element('account',array('gender'=>$mGender,'firstName'=>$mFirstName,'lastName'=>$mLastName,
			'cvext-account-user'=>$mUserId));
		$status=$page->doEdit($accountText,'creating new account for the user '.$username,EDIT_NEW);
		if($status->value['revision'])
		{
			$accountId=$status->value['revision']->getPage();
		} else {
			//throw some error
		}
		$dbw=wfGetDB(DB_MASTER);
		$dbw->insert('page_props',array('pp_page'=>$accountId,'pp_propname'=>'cvext-account-user','pp_value'=>$mUserId));
		$mPassportInfo=ConferencePassportInfo::createFromScratch($mPassportInfo->getPassportNo(), $accountId,
		$mPassportInfo->getIssuedBy(), $mPassportInfo->getValidUntil(), $mPassportInfo->getPlace(),
		$mPassportInfo->getDOB(), $mPassportInfo->getCountry());
		return new self($accountId,array(), $mUserId, $mGender, $mFirstName, $mLastName,$mPassportInfo);
		
		
	}
	/**
	 * @param Int $accountId
	 * @return ConferenceAccount
	 * sub-account:registration 1:1 mapping
	 */
	public static function loadFromId($accountId)
	{
		$article=Article::newFromID($accountId);
		$text=$article->fetchContent();
		preg_match_all("/<account gender=\"(.*)\" firstName=\"(.*)\" lastName=\"(.*)\" cvext-account-user=\"(.*)\" \/>/",$text,$matches);
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
		$registrations=array();
		//we are following an assumption that only one sub-account per conference
		foreach ($resultSub as $row)
		{
			$subAccountIds[]=$row->pp_page;
		}
		if(!empty($subAccountIds))
		{
			$conferenceResult=$dbr->select('page_props',
			'*',
			array('pp_page IN ('.implode(',',$subAccountIds).')','pp_propname'=>'cvext-account-conf'),
			__METHOD__);
			foreach ($conferenceResult as $row)
			{
				$conferenceIds[]=array('sub-account'=>$row->pp_page,'conf'=>$row->pp_value);
			}
			$res=$dbr->select('page_props',
			array('pp_page','pp_value'),
			array('pp_value IN ('.implode(',',$subAccountIds).')','pp_propname'=>'cvext-registration-account'),
			__METHOD__,
			array());
			
			foreach ($res as $row)
			{
				foreach ($conferenceIds as $combo)
				{
					if($row->pp_value==$combo['sub-account'])
					$conf = $row->pp_value;
				}
				$registrations[]=array('conf'=>$conf,'registration'=>ConferenceRegistration::loadFromId($row->pp_page));

			}
		}
		
		
		
		return new self($accountId,$conferenceIds,$matches[4][0],$matches[1][0], $matches[2][0], $matches[3][0],$passportInfo, $registrations);


	}
	/**
	 * 
	 * This function is used for adding registration to the ConferenceAccount object.
	 * This function works on a pre-loaded ConferenceAccount object, so it should 
	 * always be called on an object created from ConferenceAccount::createFromScratch() or 
	 * ConferenceAccount::loadFromId()
	 * @param Object $registration
	 * @param Int $confId
	 * Note : do keep in mind that the registration object passed should only contain events that are part of the same conference 
	 */
	public function pushRegistration($registration=null,$confId)
	{
		if( is_null($registration) )
		{
			return '';
		}
		$conferenceTitle=ConferenceUtils::getTitle($confId);
		$username=UserUtils::getUsername($this->getUserId());
		$titleText=$conferenceTitle.'/accounts/'.$username;
		$title=Title::newFromText($titleText);
		$page=WikiPage::factory($title);
		$properties=array();
		$newChild=!(in_array($confId,$this->mConferenceIds));
		if($newChild)
		{
			$text=Xml::element('account-sub',array('cvext-account-parent'=>$this->getAccountId(),'cvext-account-conf'=>$confId));
			$status=$page->doEdit($text,'creating new sub account',EDIT_NEW);
			if($status->value['revision'])
			{
				$id=$status->value['revision']->getPage();
				$properties[]=array('id'=>$id,'prop'=>'cvext-account-parent','value'=>$this->getAccountId());
				$properties[]=array('id'=>$id,'prop'=>'cvext-account-conf','value'=>$confId);
				foreach ($properties as $value)
				{
					$dbw->insert('page_props',array('pp_page'=>$value['id'],'pp_propname'=>$value['prop'],'pp_value'=>$value['value']));
				}
			} else {
				//do something here
			}
			
		} else {
			if($page->exists())
			{
				$id=$page->getId();
			}
		}
			$registration=ConferenceRegistration::createFromScratch($id, $registration->getType(),
			$registration->getDietaryRestr(), $registration->getOtherDietOpts(), $registration->getOtherOpts(),
			$registration->getBadgeInfo(), $registration->getTransaction(), $registration->getEvents());
			$this->addConferenceId($confId);
			$this->addRegistration($registration);
	}
	/**
	 * 
	 * updates the account info
	 * @param Int $uid
	 * @param String $firstName
	 * @param String $lastName
	 * @param String $gender
	 */
	public static function performAccountEdit($uid,$firstName,$lastName,$gender)
	{
		$username=UserUtils::getUsername($uid);
		$titleText='accounts/'.$username;
		$title=Title::newFromText($titleText);
		$page=WikiPage::factory($title);
		$result=array();
		if($page->exists())
		{
			$id=$page->getId();
			$article=Article::newFromID($id);
			$content=$article->fetchContent();
			preg_match_all("/<account gender=\"(.*)\" firstName=\"(.*)\" lastName=\"(.*)\" cvext-account-user=\"(.*)\" \/>/",$content,$matches);
			//we will never be changing the cvext-account-user property
			if(!$gender)
			{
				$gender = $matches[1][0];
			}
			if(!$firstName)
			{
				$firstName = $matches[2][0];
			}
			if(!$lastName)
			{
				$lastName = $matches[3][0];
			}
			
			$newTag = Xml::element('account',array('gender'=>$gender,'firstName'=>$firstName,'lastName'=>$lastName,
			'cvext-account-user'=>$matches[4][0]));
			
			$content = preg_replace("/<account gender=\".*\" firstName=\".*\" lastName=\".*\" cvext-account-user=\".*\" \/>/", $newTag, 
			$content);
			
			$status=$page->doEdit($content,'The account info has been modified',EDIT_UPDATE);
			if($status->value['revision'])
			{
				$result['done']=true;
				$result['msg']="The account has been successfully modified";
				$result['flag']=Conference::SUCCESS_CODE;	
			} else {
				$result['done']=false;
				$result['msg']="The account could not be modified";
				$result['flag']=Conference::ERROR_EDIT;
			}
		} else {
			$result['done']=false;
			$result['msg']="The account with the following details was not found in the database";
			$result['flag']=Conference::ERROR_MISSING;
		}
		return $result;
	}
	/**
	 * deletes all the info associated with a parent account
	 * this action can be carried out by the admin only
	 * @param Int $uid
	 */
	public static function performAccountDelete($uid)
	{
		//step 1. find the parent account
		//step 2. find all the sub-accounts
		//step 3. find all the corresponding registrations and passport info
		//step 4. delete all registrations
		//step 5. delete sub-accounts
		//step 6. delete passport info
		//step 7. delete parent account
		$username=UserUtils::getUsername($uid);
		$titleText='/accounts/'.$username;
		$title=Title::newFromText($titleText);
		$page=WikiPage::factory($title);
		$subAccountIds=array();
		$result=array();
		if($page->exists())
		{
			$id=$page->getId();
			//now get all the sub-accounts
			$dbr=wfGetDB(DB_SLAVE);
			$result=$dbr->select('page_props',
			'pp_page',
			array('pp_propname'=>'cvext-account-parent','pp_value'=>$id),
			__METHOD__);
			if($dbr->numRows($result))
			{
				foreach ($result as $row)
				{
						$subAccountIds[]=$row->pp_page;
				}
				//now get all the registrations
				$resultReg=$dbr->select('page_props',
				'pp_page',
				array('pp_value IN ('.implode(',',$subAccountIds).')','pp_propname'=>'cvext-registration-account'),
				__METHOD__);
				//first delete all the registrations
				foreach ($resultReg as $row)
				{
					$tempPage=WikiPage::newFromID($row->pp_page);
					$tempStatus=$tempPage->doDeleteArticle('registration is deleted as the sub-account was deleted',Revision::DELETED_TEXT);
					if($tempStatus!==true)
					{
						$result['done']=false;
						$result['cause']="registration delete fail";
						$result['flag']=Conference::ERROR_DELETE;
						return $result;
					} 
				}
				// now its time to delete all the sub-accounts
				foreach ($subAccountIds as $subId)
				{
					$tempAccPage=WikiPage::newFromID($subId);
					$tempAccStatus=$tempAccPage->doDeleteArticle("sub-account is deleted as the parent account was deleted",Revision::DELETED_TEXT);
					if($tempAccStatus!==true)
					{
						$result['done']=false;
						$result['cause']="sub-account delete fail";
						$result['flag']=Conference::ERROR_DELETE;
						return $result;
					}
				}
			}
			$pagePassport=WikiPage::factory(Title::newFromText("passports/".$username));
			$statusPass=$pagePassport->doDeleteArticle("passport info is deleted as parent account was deleted");
			if($statusPass!==true)
			{
				$result['done']=false;
				$result['cause']="passport info delete fail";
				$result['flag']=Conference::ERROR_DELETE;
				return $result;
			}
			$statusAccount=$page->doDeleteArticle("parent account is deleted by the admin",Revsion::DELETED_TEXT);
			if($statusAccount!==true)
			{
				$result['done']=false;
				$result['cause']='parent account delete fail';
				$result['flag']=Conference::ERROR_DELETE;
				return $result;
			}
				
		} else {
			$result['done']=false;
			$result['cause']='parent account not found in the database';
			$result['flag']=Conference::ERROR_MISSING;
			return $result;
		}
		$result['done']=true;
		$result['cause']='';
		$result['flag']=Conference::SUCCESS_CODE;
		return $result;
	
	}
	/**
	 * 
	 * updates the passport info of a user
	 * @param Int $uid
	 * @param ConferencePassortInfo(this object may not be having the id value set) $passport
	 */
	public static function performPassportUpdate($uid,$passport)
	{
		$username=UserUtils::getUsername($uid);
		$titleText='passports/'.$username;
		$title=Title::newFromText($titleText);
		$page=WikiPage::factory($title);
		$result=array();
		if($page->exists())
		{
			$id=$page->getId();
			$article=Article::newFromID($id);
			$content=$article->fetchContent();
			//modify the content
			//here we wont be modifying any of the passport page_props (as there is no need to)
			$status=$page->doEdit($content,'modifying passport info of the user with username '.$username,EDIT_UPDATE);
			if($status->value['revision'])
			{
				$result['done']=true;
				$result['msg']="The passport info has been successfully updated";
				$result['flag']=Conference::SUCCESS_CODE;
			} else {
				$result['done']=false;
				$result['msg']="The passport info could not be updated";
				$result['flag']=Conference::ERROR_EDIT;
			
			}
		} else {
			$result['done']=false;
			$result['msg']="The passport with the given username doesnt exist in the database";
			$result['flag']=Conference::ERROR_MISSING;
		}
		return $result;
		
	}
	/**
	 * 
	 * for adding conference id to the $mConferenceIds array
	 * @param Int $confId
	 */
	public function addConferenceId($confId)
	{
		$this->mConferenceIds[]=$confId;
	}
	/**
	 * 
	 * for adding registration object to the $mRegistrations array
	 * @param Object $registration
	 */
	public function addRegistration($registration)
	{
		$this->mRegistrations[]=$registration;
	}
	/**
	 * 
	 * parserFirstCallInit() hook function
	 * It re-populates the page_props table with the properties of parent account wiki page
	 * @param String $input
	 * @param Array $args
	 * @param Parser object $parser
	 * @param PPFrame object $frame
	 */
	public static function render($input, array $args, Parser $parser, PPFrame $frame)
	{
		
		//now we have to re-set these values into page_props table
		$dbw=wfGetDB(DB_MASTER);
		$title=$parser->getTitle();
		$accountId=$title->getArticleId();
		if($accountId!=0)
		{
			$dbw->insert('page_props',array('pp_page'=>$accountId,'pp_propname'=>'cvext-account-user','pp_value'=>$args['cvext-account-user']));
		}
		//$page_props=array('account-conf'=>$conferenceId,'account-user'=>$userId);
		
		return '';
	}
	/**
	 * 
	 * Re-populates the page_props table with properties of child account wiki page
	 * @param String $input
	 * @param Array $args
	 * @param Parser object $parser
	 * @param PPFrame object $frame
	 */
	public static function renderSub($input, array $args, Parser $parser, PPFrame $frame)
	{
		$dbw=wfGetDB(DB_MASTER);
		$title=$parser->getTitle();
		$accountSubId=$title->getArticleId();
		if($accountSubId!=0)
		{
			foreach($args as $attribute=>$value)
			{
				$dbw->insert('page_props',array('pp_page'=>$accountSubId,'pp_propname'=>$attribute,'pp_value'=>$value));
			}
		}
		return '';
		
	}
	/*public static function createFromScratch($mConferenceId,$mUserId,$mGender, $mFirstName, $mLastName,$mPassportInfo,$mRegistration=null)
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
			if($statusParent->value['revision'])
			{
				$revision=$statusParent->value['revision'];
				$id=$revision->getPage();
			}
			else
			{
			//decide on what to do
			}
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
		if($statusChild->value['revision'])
		{
			$childId=$statusChild->value['revision']->getPage();
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
			$properties=array(array('id'=>$childId,'prop'=>'cvext-account-conf','value'=>$mConferenceId),array('id'=>$childId,'prop'=>					'cvext-account-parent','value'=>$id));
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
		else
		{
		//do something here
		}
	}*/
	/**
	 * 
	 * getter function
	 */
	public function getGender()
	{
		return $this->mGender;
	}
	/**
	 * 
	 * setter function
	 * @param String $gender
	 */
	public function setGender($gender)
	{
		$this->mGender=$gender;
	}
	/**
	 * 
	 * getter function
	 */
	public function getFirstName()
	{
		return $this->mFirstName;
	}
	/**
	 * 
	 * setter function
	 * @param String $name
	 */
	public function setFirstName($name)
	{
		$this->mFirstName=$name;
	}
	/**
	 * 
	 * getter function
	 */
	public function getLastName()
	{
		return $this->mLastName;
	}
	/**
	 * 
	 * setter function
	 * @param String $name
	 */
	public function setLastName($name)
	{
		$this->mLastName=$name;
	}
	/**
	 * 
	 * getter function
	 */
	public function getPassportInfo()
	{
		return $this->mPassportInfo;
		
	}
	/**
	 * 
	 * setter function
	 * @param ConferencePassportInfo object $info
	 */
	public function setPassportInfo($info)
	{
		$this->mPassportInfo=$info;
	}
	/**
	 * 
	 * getter function
	 */
	public function getRegistrations()
	{
		return $this->mRegistrations;
	
	}
	/**
	 * 
	 * setter function
	 * @param Array of ConferenceRegistration objects $registrations
	 */
	public function setRegistrations($registrations)
	{
		$this->mRegistrations=$registrations;
	}
	/**
	 * 
	 * getter function
	 */
	public function getConferenceId()
	{
		return $this->mConferenceId;
	}
	/**
	 * 
	 * setter function
	 * @param Int $id
	 */
	public function setConferenceId($id)
	{
		$this->mConferenceId=$id;
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
	public function getUserId()
	{
		return $this->mUserId;
	}
	/**
	 * 
	 * setter function
	 * @param Int $id
	 */
	public function setUserId($id)
	{
		$this->mUserId=$id;
	}
	
}