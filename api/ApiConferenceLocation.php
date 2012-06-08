<?php
/**
 * 
 * @todo see line 123
 * @todo complete mustValidateInputs()
 * @author chughakshay16
 *
 */
class ApiConferenceLocationEdit extends ApiBase
{
	public function __construct($main, $action)
	{
		parent::__construct($main, $action);
	}
	public function execute()
	{
		//if there is a change in roomNo it will change the title as well
		$params = $this->extractRequestParams();
		//roomNo is the param which should be sent no matter what
		
		$request = $this->getRequest();
		$user = $this->getUser();
		
		if(session_id()==0)
		{
			
			$this->dieUsageMsg(array('mustbeloggedin', 'conference'));
			
		} elseif (!$request->getSessionData('conference')){
			
			$this->dieUsageMsg(array('badaccess-groups'));
			
		} elseif ($user->getId()==0){
			
			$this->dieUsageMsg(array('invaliduser', $user->getName()));
			
		}
		
		
		if(!isset($params['roomno']))
		{
			
			$this->dieUsageMsg(array('missingparam',$params['roomno']));
			
		} elseif (!isset($params['description']) && !isset($params['url']) && !isset($params['roomnoto'])){
			
			$this->dieUsageMsg(array('missingparam','Atleast description, url or roomnoto'));
			
		} else {
			
			$roomNo = $params['roomno'];
			$description = $params['description'];
			$url = $params['url'];
			$roomNoTo = $params['roomnoto'];
			
		}
		
		
		//we dont need to checkt the validity of location title , or do we ?
		$conferenceSessionArray = $request->getSessionData('conference');
		$conferenceId = $conferenceSessionArray['id'];
		$conferenceTitle =$conferenceSessionArray['title'];
		
		$titleText = $conferenceTitle.'/locations/'.$roomNo;
		$title = Title::newFromText($titleText);
		if(!$title)
		{
			
			$this->dieUsageMsg(array('invalidtitle',$params['roomno']));
			
		} elseif (!$title->exists()){
			
			$this->dieUsageMsg(array('nocreate-missing',$params['roomno']));
			
		}
		
		$errors = $this->mustValidateInputs($description, $url);
		if(count($errors))
		{
			
			//depending on the error
					//$this->dieUsageMsg(array('spamdetected',put the parameter due to which error was thrown))
			
		} else {
			if ($roomNoTo)
			{
				
				//its a big change need to think about it
				//now we need to check the validity of roomnoto 
				$toTitleText = $conferenceTitle.'/locations/'.$roomNoTo;
				$titleTo = Title::newFromText($toTitleText);
				if(!$titleTo)
				{
					$this->dieUsageMsg(array('invalidtitle',$params['roomnoto']));
					
				} elseif ($titleTo->exists()){
					
					$this->dieUsageMsg(array('createonly-exists'));
					
				}
				$errors = $this->mustValidate($roomNoTo);//this step could totally be skipped by just passing the default type in getAllowedParams()
				if(count($errors))
				{
					
					//depending on the error
					//$this->dieUsageMsg(array('spamdetected',put the parameter due to which error was thrown))
					
				}
				//here starts the logic of move operation
				//we can perform a lot of checks here if we want
				//such as 
				//1. its not a title that belongs to File namespace
				//2. this old title should not have a sub-page attached
				//3. this old title should not have a talk page associated with it
				//so lets perform these checks before we dive into the real move operation
				// in our case we dont need to check for the namespace as it will always be MAIN namespace only since we never passed any prefix 
				//in the text or a namespace as a separate parameter
				$oldTalkPage = $title->getTalkPage();
				$newTalkPage = $titleTo->getTalkPage();
				//have a small doubt here that is it possible to have a talk page and not a user page
				if($newTalkPage->exists() || $oldTalkPage->exists())
				{
					//debug this error
					//and do something about it
				}
				// I somehow feel that there is no need to check for the sub-pages as we will never let an admin create one, and even if someone else
				//creates one it shouldnt affect the normal working of this conference
				$createRedirect = false;
				// One very important thing :- user(or admin) in our case should have the 'suppressredirect' right set to true 
				//for this move operation to make sense in our case
				$reason = 'The admin is editing the roomNo of the location';
				$retval = $title->moveTo( $titleTo, true, $reason, $createRedirect );
				if ( $retval !== true ) {
					
					$this->dieUsageMsg( reset( $retval ) );
				}
				$roomNo = $roomNoTo;
				
			} 
			$result=EventLocation::performEdit($conferenceId, $roomNo, $description, $url);
			$resultApi = $this->getResult();
			$resultApi->addValue(null, $this->getModuleName(), $result);
		}
	}
	private function mustValidateInputs($description , $url)
	{
	
	}
	public function isWriteMode()
	{
		return true;
	}
	public function mustBePosted()
	{
		return true;
	}
	public function getAllowedParams()
	{
		return array(
		'roomno'=>null,
		'roomnoto'=>null,
		'description'=>null,
		'url'=>null
		);	
	}
	public function getParamDescription()
	{
		return array(
		'roomno'=>'Room no of the location',
		'description'=>'Description of the location',
		'url'=>'Url which points to the image of the location'
		);
	}
	public function getDescription()
	{
		return 'Edit Location Details';
	}
	public function getPossibleErrors()
	{	
	
	}
	public function getExamples()
	{
	
	}
	public function getVersion()
	{
		return __CLASS__ . ': $Id$';
	}
}

/**
 * 
 * @todo see line 208
 * @todo complete mustValidateInputs()
 * @author chughakshay16
 *
 */
class ApiConferenceLocationDelete extends ApiBase
{
	public function __construct($main, $action)
	{
		parent::__construct($main, $action);
	}
	public function execute()
	{
		//in all the api classes we never take it into consideration that for an admin the conference session data may have gotten destroyed
		/**
		 * @todo take care of this above scenario
		 */
		$params = $this->extractRequestParams();
		$request = $this->getRequest();
		$user = $this->getUser();
		
		
		if(session_id()=='')
		{
			
			$this->dieUsageMsg(array('mustbeloggedin','conference'));
				
		} elseif (!$request->getSessionData('conference')){
			
			$this->dieUsageMsg(array('badaccess-groups'));
			
		} elseif ($user->getId()==0){
			
			$this->dieUsageMsg(array('invaliduser', $user->getName()));
			
		} elseif (!isset($params['roomno'])){
			
			$this->dieUsageMsg(array('missingparam',$params['roomno']));
			
		} else {
			
			$roomNo = $params['roomno'];
			
		}
		
		// we are performing this check even if its gonna be done again in performDelete() function
		$conferenceSessionArray = $request->getSessionData('conference');
		$conferenceId = $conferenceSessionArray['id'];
		$conferenceTitle = $conferenceSessionArray['title'];
		
		$titleText = $conferenceTitle.'/locations/'.$roomNo;
		$title = Title::newFromText($titleText);
		if(!$title)
		{
			
			$this->dieUsageMsg(array('invalidtitle',$params['roomno']));
			
		} elseif (!$title->exists()){
			
			$this->dieUsageMsg(array('cannotdelete'));
		}
		
		//now all the checks have been made
		//do the actual delete
		$result = EventLocation::performDelete($conferenceId, $roomNo);
		$resultApi = $this->getResult();
		$resultApi->addValue(null, $this->getModuleName(), $result);
		
	}
	private function mustValidateInputs($description , $url)
	{
	
	}
	public function isWriteMode()
	{
		return true;
	}
	public function mustBePosted()
	{
		return true;
	}
	public function getAllowedParams()
	{
		return array(
		'roomno'=>null
		);	
	}
	public function getParamDescription()
	{
		return array(
		'roomno'=>'Room no of the location'
		);
	}
	public function getDescription()
	{
		return 'Delete Location Details';
	}
	public function getPossibleErrors()
	{	
	
	}
	public function getExamples()
	{
	
	}
	public function getVersion()
	{
		return __CLASS__ . ': $Id$';
	}
	
}


class ApiConferenceLocationAdd extends ApiBase
{
	public function __construct($main , $action)
	{
		parent::__construct($main, $action);
	}
	public function execute()
	{
		$params = $this->executeRequestParams();
		$request = $this->getRequest();
		$user = $this->getUser();
		
		
		if(session_id()=='')
		{
			
			$this->dieUsageMsg(array('mustbeloggedin','conference'));
			
		} elseif (!$request->getSessionData('conference')){
			
			$this->dieUsageMsg(array('badaccess-groups'));
			
		} elseif (!isset($params['roomno'])){
			
			$this->dieUsageMsg(array('missingparam',$params['roomno']));
			
		} elseif ($user->getId()==0){
			
			$this->dieUsageMsg(array('invaliduser', $user->getName()));
			
		} else {
			
			$roomNo = $params['roomno'];
			$description = $params['description'];
			$url = $params['url'];
			
		}
		
		
		//now we will check the validity of title
		$conferenceSessionArray = $request->getSessionData('conference');
		$conferenceId = $conferenceSessionArray['id'];
		$conferenceTitle = $conferenceSessionArray['title'];
		
		$titleText = $conferenceTitle.'/locations/'.$roomNo;
		$title = Title::newFromText($titleText);
		if (!$title)
		{
			
			$this->dieUsageMsg(array('invalidtitle',$params['roomno']));
			
		} elseif ($title->exists()) {
			
			$this->dieUsageMsg(array('createonly-exists',$params['roomno']));
			
		}
		
		
		//now we have checked the validity of roomno
		//go and validate the other inputs if at all they are passed
		$errors = $this->mustValidateInputs($description, $url);
		if(count($errors))
		{
			//depending on the error
					//$this->dieUsageMsg(array('spamdetected',put the parameter due to which error was thrown))
		}
		$location = EventLocation::createFromScratch($conferenceId, $roomNo, $description, $url);
		//now we need to check if the creation process went well
		$resultApi = $this->getResult();
		if($location && $location->getLocationId())
		{
			
			$result['done']=true;
			$result['id']=$location->getLocationId();
			$resultApi->addValue(null, $this->getModuleName(), $result);
			
		} else {
			
			$result['done']=false;
			$resultApi->addValue(null, $this->getModuleName(), $result);
			
		}
	}
	private function mustValidateInputs($description , $url)
	{
		//dont throw errors if values are null
	}
	public function isWriteMode()
	{
		return true;
	}
	public function mustBePosted()
	{
		return true;
	}
	public function getAllowedParams()
	{
		return array(
		'roomno'=>null,
		'description'=>null,
		'url'=>null
		);	
	}
	public function getParamDescription()
	{
		return array(
		'roomno'=>'Room no of the location',
		'description'=>'Description of the location',
		'url'=>'Url for the image of the location'
		);
	}
	public function getDescription()
	{
		return 'Add Location Details';
	}
	public function getPossibleErrors()
	{	
	
	}
	public function getExamples()
	{
	
	}
	public function getVersion()
	{
		return __CLASS__ . ': $Id$';
	}
}