<?php
/**
 * 
 * @todo complete mustValidateInputs()
 * @author chughakshay16
 *
 */
class ApiConferencePageEdit extends ApiBase
{
	public function __construct($main, $action)
	{
		parent::__construct($main,$action);
	}
	public function execute()
	{
		$params = $this->extractRequestParams();
		$request = $this->getRequest();
		$user = $this->getUser();
		
		if(session_id()=='')
		{
			
			$this->dieUsageMsg(array('mustbeloggedin','conference'));
			
		} elseif (!$request->getSessionData('conference')){
			
			$this->dieUsageMsg(array('badaccess-groups'));
			
		} elseif ($user->getId()==0){
			
			$this->dieUsageMsg(array('invaliduser',$user->getName()));
			
		} elseif (!isset($params['type'])){
			
			$this->dieUsageMsg(array('missingparam',$params['type']));
			
		} elseif (!isset($params['typeto'])){
			
			$this->dieUsageMsg(array('missingparam',$params['typeto']));
			
		}
		$conferenceSessionArray = $request->getSessionData('conference');
		$oldText = $conferenceSessionArray['title'].'/pages/'.$params['type'];
		$newText = $conferenceSessionArray['title'].'/pages/'.$params['typeto'];
		$oldTitle = Title::newFromText($oldText);
		$newTitle = Title::newFromText($newText);
		$oldTalkPage = $oldTitle->getTalkPage();
		$newTalkPage = $newTitle->getTalkPage();
		if(!$oldTitle)
		{
			
			$this->dieUsageMsg(array('invalidtitle',$params['type']));
			
		} elseif (!$newTitle){
			
			$this->dieUsageMsg(array('invalidtitle',$params['typeto']));
			
		} elseif (!$oldTitle->exists()){
			
			$this->dieUsageMsg(array('nocreate-missing'));
			
		} elseif ($newTitle->exists()){
			
			$this->dieUsageMsg(array(array('createonly-exists')));
			
		} elseif ($oldTalkPage && $oldTalkPage->exists()){
			
			//debug
			//and do something about it
			
		} elseif ($newTalkPage && $newTalkPage->exists()){
			
			//debug 
			//and do something about it
			
		}
		$createRedirect = false;
		$reason = 'The admin is editing the details of the conference page';
		$retval = $oldTitle->moveTo( $newTitle, true, $reason, $createRedirect );
		if ( $retval !== true ) 
		{
			$this->dieUsageMsg( reset( $retval ) );
		}
		$result=ConferencePage::performEdit($conferenceSessionArray['id'],$params['type']);
		$resultApi = $this->getResult();
		$resultApi->addValue(null, $this->getModuleName(), $result);
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
		'type'=>null,
		'typeto'=>null
		);	
	}
	public function getParamDescription()
	{
		return array(
		'type'=>'Type of the conference page',
		'typeto'=>'New type for the conference page'
		);
	}
	public function getDescription()
	{
		return 'Edit Conference Details';
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


class ApiConferencePageDelete extends ApiBase
{
	public function __construct($main , $action)
	{
		parent::__construct($main, $action);
	}
	public function execute()
	{
		$params=$this->extractRequestParams();
		$request=$this->getRequest();
		if(session_id()=='')
		{
			$this->dieUsageMsg(array('mustbeloggedin','conference'));
		} else {
			if(!$request->getSessionData('conference'))
			{
				$this->dieUsageMsg(array('badaccess-groups'));
			}
		}
		$user = $this->getUser();
		if($user->getId()==0)
		{
			
			$this->dieUsageMsg(array('invaliduser', $user->getName()));
			
		}
		if(isset($params['type']))
		{
			$conferenceSessionArray=$request->getSessionData('conference');
			$conferenceId=$conferenceSessionArray['id'];
			$text = $conferenceSessionArray['title'].'/pages/'.$params['type'];
			$title=Title::newFromText($text);
			if(!$title)
			{
				
				$this->dieUsageMsg( array( 'invalidtitle', $params['title'] ) );
				
			} elseif (!$title->exists()){
				
				$this->dieUsageMsg(array('cannotdelete','this page'));
				
			}	
		} else {
			
			$this->dieUsageMsg(array('missingparam',$params['type']));
			
		}
		$result=ConferencePage::performDelete($conferenceId,$type);
		$resultApi = $this->getResult();
		$resultApi->addValue(null, $this->getModuleName(), $result);
		
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
		'type'=>null
		);	
	}
	public function getParamDescription()
	{
		return array(
		'type'=>'Type of the conference page'
		);
	}
	public function getDescription()
	{
		return 'Delete Conference Page';
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


class ApiConferencePageAdd extends ApiBase
{
	public function __construct($main , $action)
	{
		parent::__construct($main, $action);
	}
	public function execute()
	{
		$params = $this->extractRequestParams();
		/**
		 * these are the checks that we need to pass before we actually create a new page
		 * 1. admin should be logged in 
		 * 2. conference data should be present in the session(this is an important 
		 * step because this step distinugishes the admin from a regular user). As conference data is only stored 
		 * for the admin user once he/she loads up the Special Dashboard page.
		 * 3. check for the validity of title
		 * 4. check if the title already exists
		 * 5. check for the validity of the user
		 * 
		 */
		if(session_id()=='')
		{
			
			$this->dieUsageMsg(array('mustbeloggedin', 'conference'));
			
		}
		if(!isset($params['type']))
		{
			
			$this->dieUsageMsg(array('missingparam',$params['type']));
			
		} else {
			
			$type = $params['type'];
			$default = $params['default'];
			
		}
		
		$request = $this->getRequest();
		if(!isset($request->getSessionData('conference')))
		{
			
			$this->dieUsageMsg(array('badaccess-groups'));
			
		}
		
		$conferenceSessionArray = $request->getSessionData('conference');
		$conferenceId = $conferenceSessionArray['id'];
		
		//now the user validity check
		//although its not that necessary
		$user = $this->getUser();
		if($user->getId()==0)
		{
			
			$this->dieUsageMsg(array('invaliduser' , $user->getName()));
			
		}
		
		//this is just checking the validity of the title passed along
		//sad part is that createFromScratch() never checks for the existence of title in the database so we will have to check that here only
		$confTitle = $conferenceSessionArray['title'];
		$titleText=$confTitle.'/pages/'.$type;
		$title = Title::newFromText($titleText);
		if(!$title)
		{
			
			$this->dieUsageMsg(array('invalidtitle',$params['type']));
			
		} elseif ($title->exists()){
			
			$this->dieUsageMsg(array('createonly-exists',$params['type']));
			
		}
		
		//now we are ready to create the page
		//but before doing that check if the page is already one of the existing types 
		//, if it is not then default content will not be added even if the default is passed as true
		if($default)
		{
			$isTypePreLoaded = $this->isPreLoaded($type);
			
			if(!$isTypePreLoaded)
			{
				
				$default = false;
				
			}
			
			$page = ConferencePage::createFromScratch($conferenceId, $type,$default);
			$resultApi = $this->getResult();
			if($page && $page->getId())
			{
				
				$result['done']=true;
				$result['id']=$page->getId();
				$resultApi->addValue(null, $this->getModuleName(), $result);
				
			} else {
				$result['done']=false;
				$resultApi->addValue(null, $this->getModuleName(), $result);
			}
		}
		
		
		
	}
	private function isPreLoaded($type)
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
		'type'=>null,
		'default'=>false
		);	
	}
	public function getParamDescription()
	{
		return array(
		'type'=>'Type of the conference page',
		'default'=>'Boolean for deciding whether to add default content or not'
		);
	}
	public function getDescription()
	{
		return 'Add a conference page';
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