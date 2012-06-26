<?php
/**
 * 
 * @todo complete mustValidateInputs()
 * @author chughakshay16
 *
 */
class ApiConferenceAuthorEdit extends ApiBase
{
	public function __construct($main , $action)
	{
		parent::__construct($main, $action);
	}
	public function execute()
	{
		$params = $this->extractRequestParams();
		if(session_id=='')
		{
			
			$this->dieUsageMsg(array('mustbeloggedin','conference'));
			
		}
		if(!isset($params['country']) && !isset($params['affiliation']) && !isset($params['url']))
		{
			
			$this->dieUsage('Atleast one of the params must be passed in the request', 'atleastparam');
			
		} else {
			
			$country = $params['country'];
			$affiliation = $params['affiliation'];
			$url = $params['url'];
			
		}
		$errors= $this->mustValidateInputs($country , $affiliation , $url);
		if(count($errors))
		{
			//depending on the error
						//$this->dieUsageMsg(array('spamdetected',put the parameter due to which error occurred))
						//change getPossibleErrors()
		} else {
			$user=$this->getUser();
			$user = User::newFromName($user->getName());
			if($user->getId()==0)
			{
				
				$this->dieUsageMsg(array('nosuchuser',$user->getName()));
				
			}  elseif ($user===false){
				
				$this->dieUsageMsg(array('invaliduser',$user->getName()));
				
			}
			
			
			$isAuthor=UserUtils::isSpeaker($user->getId());
			if($isAuthor)
			{
				$result = ConferenceAuthor::performAuthorEdit($user->getId(), $country, $affiliation, $url);
				$resultApi = $this->getResult();
				$resultApi->addValue(null, $this->getModuleName(), $result);
			} else {
					
				$this->dieUsageMsg(array('badaccess-groups'));
			}
			
		}
	}
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $gender
	 * @param unknown_type $firstname
	 * @param unknown_type $lastname
	 * @todo complete this function
	 */
	public function mustValidateInputs($gender, $firstname , $lastname)
	{
		// dont throw any error for null values	
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
		'country'=>null,
		'affiliation'=>null,
		'url'=>null
		);	
	}
	public function getParamDescription()
	{
		return array(
		'country'=>'Country that the author lives in',
		'affiliation'=>'Affiliation of the author',
		'url'=>'Url of the author\'\s personal blog'
		);
	}
	public function getDescription()
	{
		return 'Edit Author Details';
	}
	public function getPossibleErrors()
	{	
		$user = $this->getUser();
		return array_merge(parent::getPossibleErrors(), array(
		array('mustbeloggedin','conference'),
		array('nosuchuser',$user->getName()),
		array('invaliduser',$user->getName()),
		array('badaccess-groups'),
		array('code'=>'atleastparam','info'=>'Atleast one of the params should be passed in the request')));
	}
	public function getExamples()
	{
	
	}
	public function getVersion()
	{
		return __CLASS__ . ': $Id$';
	}
}


class ApiConferenceAuthorDelete extends ApiBase
{
	public function __construct($main, $action)
	{
		parent::__contruct($main, $action);
	}
	public function execute()
	{
		//only an author can delete itself
		$pars=$this->extractRequestParams();
		if(session_id=='')
		{
			
			$this->dieUsageMsg(array('mustbeloggedin','conference'));
			
		}
		
		$user=$this->getUser();
		if($user->getId==0)
		{
			
			$this->dieUsageMsg(array('invaliduser',$user->getName()));
			
		}
		//no all the user checks are complete, now go for author checks
		$isAuthor=UserUtils::isSpeaker($user->getId());
		if($isAuthor)
		{
			
			$result=ConferenceAuthor::performAuthorDelete($user->getId());
			$resultApi = $this->getResult();
			$resultApi->addValue(null, $this->getModuleName(), $result);
			
		} else { 
			
			$this->dieUsageMsg(array('badaccess-groups'));
			
		}
		
		
		
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
		return array();	
	}
	public function getParamDescription()
	{
		return array();
	}
	public function getDescription()
	{
		return 'Delete Author Details';
	}
	public function getPossibleErrors()
	{	
		$user = $this->getUser();
		return array_merge(parent::getPossibleErrors(), array(
		array('mustbeloggedin','conference'),
		array('invaliduser',$user->getName()),
		array('badaccess-groups')
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


class ApiAuthorSubmissionDelete extends ApiBase
{
	//how do we store conference details in the session for the author
	public function __construct($main, $action)
	{
		parent::__construct($main, $action);
	}
	public function execute()
	{
		$params= $this->extractRequestParams();
		//here both the parameters are necessary
		//here $params['title'] just signifies just the core title of the submission and not the 'confTitle/submissions/title'
		if(session_id()=='')
		{
			$this->dieUsageMsg(array('mustbeloggedin', 'conference'));
		}
		//we have already added the 'required' condition for these parameters in getAllowedParams()
		/*if(isset($params['title']) && isset($params['conference']))
		{
			
			$title = $params['title'];
			$conferenceTitle = $params['conference'];
			
		} elseif (!isset($params['title'])){
			
			$this->dieUsageMsg(array('missingparam',$params['title']));
			
		} elseif (!isset($params['conference'])){
			
			$this->dieUsageMsg(array('missingparam',$params['conference']));
		}*/
		$title = $params['title'];
		$conferenceTitle = $params['conference'];
		
		//now we need the user-id
		$user=$this->getUser();
		if($user->getId()==0)
		{
			
			$this->dieUsageMsg('invaliduser',$user->getName());
			
		}
		
		//now we have the valid user, now check if we have a valid author as well
		$isAuthor=UserUtils::isSpeaker($user->getId());
		if($isAuthor)
		{
			//now check for the validity of title passed
			$text = $conferenceTitle.'/authors/'.$user->getName();
			$titleObj=Title::newFromText($text);
			$titleConfObj = Title::newFromText($conferenceTitle);
			$conferenceId = ConferenceUtils::getConferenceId($title);
			
			if(!$titleObj)
			{
				
				$this->dieUsageMsg(array('invalidtitle',$params['title']));
				
			} elseif (!$titleConfObj){
				
				$this->dieUsageMsg(array('invalidtitle',$params['conference']));
				
			} elseif (!$conferenceId){
				
				$this->dieUsageMsg(array('invalidtitle',$params['conference']));
				
			} elseif (!$titleObj->exists()){
				
				$this->dieUsageMsg(array('cannotdelete', 'this submission'));
			}
			
			//till this point we have validated conference, title and the user, so now we can go ahead and perform the delete
			$result=ConferenceAuthor::performSubmissionDelete($user->getId(), $conferenceId, $title);
			$resultApi = $this->getResult();
			$resultApi->addValue(null, $this->getModuleName(), $result);
		} else {
			
			$this->dieUsageMsg(array('badaccess-groups'));
			
		}
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
		'title'=>array(
		ApiBase::PARAM_TYPE=>'string',
		ApiBase::PARAM_REQUIRED=>true),
		'conference'=>array(
		ApiBase::PARAM_TYPE=>'string',
		ApiBase::PARAM_REQUIRED=>true)
		);	
	}
	public function getParamDescription()
	{
		return array(
		'title'=>'Title of the submission',
		'conference'=>'Title of the conference this submission belongs to'
		);
	}
	public function getDescription()
	{
		return 'Delete Submission Details';
	}
	public function getPossibleErrors()
	{	
		$user = $this->getUser();
		return array_merge(parent::getPossibleErrors(), array(
		array('mustbeloggedin','conference'),
		array('missingparam','title'),
		array('missingparam','conference'),
		array('invaliduser',$user->getName()),
		array('invalidtitle','title'),
		array('invalidtitle','conference'),
		array('cannotdelete','this submission'),
		array('badaccess-groups')
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

/**
 * 
 * @todo See line 455
 * @author chughakshay16
 *
 */
class ApiAuthorSubmissionEdit extends ApiBase
{
	public function __construct($main, $action)
	{
		parent::__construct($main, $action);
	}
	public function execute()
	{
		$params=$this->extractRequestParams();
		
		if(session_id()=='')
		{
			$this->dieUsageMsg(array('mustbeloggedin','conference'));
		}	
		/**
		 * these are all the checks that we need to go through before we make an actual edit
		 * 1. if its a valid user
		 * 2. if its a valid author
		 * 3. if its a valid conference
		 * 4. validate all the inputs
		 * 5. if its a valid title
		 */
		
		$user=$this->getUser();
		if($user->getId()==0)
		{
			
			$this->dieUsageMsg(array('invaliduser',$user->getName()));
			
		}
		
		$isAuthor=UserUtils::isSpeaker($user->getId());
		if(!isAuthor)
		{
			
			$this->dieUsageMsg(array('badaccess-groups'));
			
		}
		//we have already added the 'required' condition for these parameters in getAllowedParams()
		/*if(isset($params['title']) && isset($params['conference']))
		{
			
			$title=$params['title'];
			$conferenceTitle=$params['conference'];	
			
		} elseif (!$isset($params['title'])){
			
			$this->dieUsageMsg(array('missingparam',$params['title']));
			
		} elseif (!isset($params['conference'])){
			
			$this->dieUsageMsg(array('missingparam',$params['conference']));
		}*/
		$title = $params['title'];
		$conferenceTitle = $params['conference'];
		
		if(!isset($params['titleto']) && !isset($params['abstract']) && !isset($params['type']) 
		&& !isset($params['track']) && !isset($params['length']) && !isset($params['slidesinfo']))
		{
			
			$this->dieUsage('Atleast one of the params should be passed in the request','atleastparam');
			
		} else {
			
			$titleTo=$params['titleto'];
			$abstract= $params['abstract'];
			$type = $params['type'];
			$track = $params['track'];
			$length = $params['length'];
			$slidesInfo = $params['slidesinfo'];
			$slotReq = $params['slotreq'];
			//Note : normally in other api functions I perform a check on the whole title rather than just the rightmost part which we were performing here
			//so just check out to see which one is better
			$username = $user->getName();
			$text = $conferenceTitle.'/authors/'.$username.'/submissions/'.$title;
			$titleObj=Title::newFromText($text);
			$titleConfObj = Title::newFromText($conferenceTitle);
			$conferenceId = ConferenceUtils::getConferenceId($title);
			
			if(!$titleObj)
			{
				
				$this->dieUsageMsg(array('invalidtitle',$params['title']));
				
			} elseif (!$titleConfObj){
				
				$this->dieUsageMsg(array('invalidtitle',$params['conference']));
				
			} elseif (!$conferenceId){
				
				$this->dieUsageMsg(array('invalidtitle',$params['conference']));
				
			} elseif (!$titleObj->exists()){

				$this->dieUsageMsg('nocreate-missing');
				
			} elseif (!$titleConfObj->exists()){
				
				$this->dieUsageMsg('nocreate-missing');
				
			}
			if($titleTo)
			{
				//its a big change
				//also keep a check for the validity of titleTo
				//also keep a check for other inputs
				//just like title we have $titleTo which is just the rightmost part of the whole title (refer to the above Note)
				$text = $conferenceTitle.'/authors/'.$username.'/submissions/'.$titleTo;
				$titleNew = Title::newFromText($text);
				//$toText = Title::newFromText($titleTo);
				if(!titleNew)
				{
					$this->dieUsageMsg(array('invalidtitle', $titleTo));
					
				} elseif ($newTitle->exists()){
					
					$this->dieUsageMsg(array('createonly-exists'));
					
				}
				
				//now title validation is done
				$errors = $this->mustValidateInputs($track,$type,$abstract,$length,$slidesInfo);
				if(count($errors))
				{
					//depending on the error
					//$this->dieUsageMsg(array('spamdetected',put the parameter due to which error was thrown))
				}
				$oldTalkPage = $titleObj->getTalkPage();
				$newTalkPage = $titleNew->getTalkPage();
				if($oldTalkPage->exists() || $newTalkPage->exists())
				{
					//debug this error
					//and do something about it
				}
				$createRedirect = false;
				$reason = 'The author is editing the title and other details of the submission';
				$retval = $titleObj->moveTo( $titleNew, true, $reason, $createRedirect );
				if ( $retval !== true ) {
					
					$this->dieUsageMsg( reset( $retval ) );
					// I dont know how to account for this error in getPossibleErrors()
					
				}	
				
				//now perform the edit on the page with the new title
				$moveResult = ConferenceAuthor::performSubmissionEdit($conferenceId, $user->getId(), $titleto, $type, $abstract, $track, $length, $slidesInfo, $slotReq);
				$resultApi = $this->getResult();
				$resultApi->addValue(null, $this->getModuleName(), $moveResult);
			} else {
				//at this point we need to validate other inputs
				
				$errors = $this->mustValidateInputs($track,$type,$abstract,$length,$slidesInfo);
				if(count($errors))
				{
					
					//depending on the error
					//$this->dieUsageMsg(array('spamdetected',put the parameter due to which error was thrown))
					
				} else {
					
					$result = ConferenceAuthor::performSubmissionEdit($conferenceId, $user->getId(), $title, $type, 
					$abstract, $track, $length, $slidesInfo, $slotReq);
					$resultApi = $this->getResult();
					$resultApi->addValue(null, $this->getModuleName(), $result);
					
				}
			}
		}
		
		//continue with fetching parameters
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
		'title'=>array(
		ApiBase::PARAM_TYPE=>'string',
		ApiBase::PARAM_REQUIRED=>true),
		'conference'=>array(
		ApiBase::PARAM_TYPE=>'string',
		ApiBase::PARAM_REQUIRED=>true),
		'titleto'=>null,
		'type'=>null,
		'abstract'=>null,
		'track'=>null,
		'length'=>array(
		ApiBase::PARAM_TYPE=>'integer',
		ApiBase::PARAM_DFLT=>0,
		ApiBase::PARAM_MIN=>0),
		'slidesInfo'=>null,
		'slotreq'=>null
		);	
	}
	public function getParamDescription()
	{
		return array(
		'title'=>'Title of the current submission',
		'conference'=>'Title of the conference this submission belongs to',
		'titleto'=>'New title for the submission',
		'type'=>'Type of the submission',
		'abstract'=>'Abstract of the submission',
		'track'=>'Track this submission belongs to',
		'length'=>'Length of the presentation',
		'slidesInfo'=>'Slides info for the submission',
		'slotreq'=>'Slot request for the proposal'
		);
	}
	public function getDescription()
	{
		return 'Edit Submission Details';
	}
	public function getPossibleErrors()
	{	
		$user= $this->getUser();
		return array_merge(parent::getPossibleErrors(), array(
		array('mustbeloggedin','conference'),
		array('invaliduser',$user->getName()),
		array('badaccess-groups'),
		array('missingparam','title'),
		array('missingtitle','conference'),
		array('code'=>'atleastparam','info'=>'Atleast one of the params should be passed in the request'),
		array('invalidtitle','title'),
		array('invalidtitle','conference'),
		array('nocreate-missing'),
		array('invalidtitle','titleto'),
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