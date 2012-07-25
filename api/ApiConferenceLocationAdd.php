<?php
class ApiConferenceLocationAdd extends ApiBase
{
	public function __construct($main , $action)
	{
		parent::__construct($main, $action);
	}
	public function execute()
	{
		$params = $this->extractRequestParams();
		$request = $this->getRequest();
		$user = $this->getUser();
		if( !$user->isLoggedIn() )
		{
			$this->dieUsageMsg(array('mustbeloggedin','Wiki'));
		}
		
		$groups = $user->getGroups();
		if( !in_array('sysop',$groups))
		{
			$this->dieUsageMsg(array('badaccess-groups'));
		}
		
		$sessionData = $request->getSessionData('conference');
		if( !$sessionData )
		{
			$this->dieUsage('No conference details were found in the session object for this user','noconfinsession');
		}		
		$roomNo = $params['roomno'];
		$description = $params['description'];
		$url = $params['url'];
		//now we will check the validity of title
		$conferenceId = $sessionData['id'];
		$conferenceTitle = $sessionData['title'];

		$titleText = $conferenceTitle.'/locations/'.$roomNo;
		$title = Title::newFromText($titleText);
		if (!$title)
		{
				
			$this->dieUsageMsg(array('invalidtitle',$params['roomno']));
				
		} elseif ($title->exists()) {
				
			$this->dieUsageMsg(array('createonly-exists'));
				
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
			$result['roomno'] = $roomNo;
			$result['description'] = $description;
			$result['url'] = $url;
			$locUrl = Title::makeTitle(NS_MAIN,$title->getDBkey())->getFullURL();
			$result['locurl'] = $locUrl;
			$result['msg'] = 'The location was successfully added';
			$resultApi->addValue(null, $this->getModuleName(), $result);
				
		} else {
				
			$result['done']=false;
			$result['msg'] = 'The location could not be added . Try again .';
			$resultApi->addValue(null, $this->getModuleName(), $result);
				
		}
	}
	private function mustValidateInputs($description , $url)
	{
		//dont throw errors if values are null
		return array();
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
				'roomno'=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true),
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
		$user = $this->getUser();
		return array_merge(parent::getPossibleErrors(), array(
				array('mustbeloggedin','conference'),
				array('invaliduser',$user->getName()),
				array('badaccess-groups'),
				array('missingparam','roomno'),
				array('invalidtitle','roomno'),
				array('createonly-exists')
		));
	}
	public function getExamples()
	{

	}
	public function getVersion()
	{
		return __CLASS__ . ': $Id$';
	}
}