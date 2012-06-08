<?php
/**
 * 
 * @todo complete mustValidateInputs()
 * @author chughakshay16
 *
 */
class ApiConferenceOrganizerEdit extends ApiBase
{
	public function __construct($main, $action)
	{
		parent::__construct($main, $action);
	}
	public function execute()
	{
		$params=$this->extractRequestParams();
		$request=$this->getRequest();
		
		//still need to decide on what messages you should choose while throwing these errors
		if(session_id()=='')
		{
			
			$this->dieUsageMsg(array('mustbeloggedin','conference'));
			
		} else {
			
			if(!$request->getSessionData('conference'))
			{
				$this->dieUsageMsg(array('badaccess-groups'));
			}
		}
		if(isset($params['username']))
		{
			
			$username=$params['username'];
			
			//now check if one of the values category and post are passed if not throw the error
			if(!isset($params['category']) && !isset($params['post']))
			{
				
				$this->dieUsageMsg(array('missingparam','Atleast category or post'));
				
			} else {
				
				$category=$params['category'];
				$post=$params['post'];
				
			}
			
		} else {
			
			$this->dieUsageMsg(array('missingparam',$params['username']));
			
		}
		
		$conferenceSessionArray=$request->getSessionData('conference');
		$conferenceId=$conferenceSessionArray['id'];
		
		$editedUser=User::newFromName($username,true);
		if($editedUser->getId()==0)
		{
			
			$this->dieUsageMsg(array('nosuchuser',$params['username']));	
			
		} elseif ($editedUser===false){
			
			$this->dieUsageMsg(array('invaliduser',$params['username']));
			
		}
		
		$isOrganizer=ConferenceOrganizerUtils::isOrganizerFromConference($editedUser->getId(), $conferenceId);
		if(!isOrganizer)
		{
			$this->dieUsageMsg(array('nocreate-missing'));
		}
		
		$errors=$this->mustValidateInputs($category,$post);
		if(count($errors))
		{
			
			//depending on the error
					//$this->dieUsageMsg(array('spamdetected',put the parameter due to which error was thrown))
			
		} else {
			
			$catpost=array(array('cat'=>$category,'post'=>$post));
			$result=ConferenceOrganizer::performEdit($conferenceId, $username, $catpost);
			$resultApi = $this->getResult();
			$resultApi->addValue(null, $this->getModuleName(), $result);
		}
		
		
	}
	public function mustValidateInputs($category, $post)
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
		'username'=>null,
		'category'=>null,
		'post'=>null
		);	
	}
	public function getParamDescription()
	{
		return array(
		'username'=>'Username  of the organizer',
		'category'=>'Category which the organizer belongs to',
		'post'=>'The role which has been assigned to the organizer'
		);
	}
	public function getDescription()
	{
		return 'Edit Organizer Details';
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


class ApiConferenceOrganizerDelete extends ApiBase
{
	public function __construct($main, $action)
	{
		parent::__construct($main, $action);
	}
	public function execute()
	{
	$params=$this->extractRequestParams();
		$request=$this->getRequest();
		
		//still need to decide on what messages you should choose while throwing these errors
		if(session_id()=='')
		{
			
			$this->dieUsageMsg(array('mustbeloggedin','conference'));
			
		} else {
			if(!$request->getSessionData('conference'))
			{
				
				$this->dieUsageMsg(array('badaccess-groups'));
				
			}
		}
		if(isset($params['username']))
		{
			
			$username=$params['username'];
			
		} else {
			
			$this->dieUsageMsg(array('missingparam',$params['username']));
			
		}
		
		$conferenceSessionArray=$request->getSessionData('conference');
		$conferenceId=$conferenceSessionArray['id'];
		$deletedUser=User::newFromName($username,true);
		if($deletedUser->getId()==0)
		{
			
			$this->dieUsageMsg('nosuchuser',$params['username']);
			
		} elseif ($deletedUser===false)
		{
			
			$this->dieUsageMsg('invaliduser',$params['username']);
			
		}
		
		$isOrganizer=ConferenceOrganizerUtils::isOrganizerFromConference($deletedUser->getId(), $conferenceId);
		if(!isOrganizer)
		{
			$this->dieUsageMsg(array('cannotdelete','this organizer '));
		} else {
			$result=ConferenceOrganizer::performDelete($conferenceId, $username);
			$resultApi = $this->getResult();
			$resultApi->addValue(null, $this->getModuleName(), $result);
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
		'username'=>null
		);	
	}
	public function getParamDescription()
	{
		return array(
		'username'=>'Username  of the organizer'
		);
	}
	public function getDescription()
	{
		return 'Delete Organizer Details';
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
 * @todo complete mustValidateInputs()
 * @author chughakshay16
 *
 */
class ApiConferenceOrganizerAdd extends ApiBase
{
	public function __construct($main, $action)
	{
		parent::__construct($main, $action);
	}
	public function execute()
	{
		$params=$this->extractRequestParameters();
		//here we should have all the three parameters set from our client
		$request=$this->getRequest();
		
		//still need to decide on what messages you should choose while throwing these errors
		if(session_id()=='')
		{
			
			$this->dieUsageMsg(array('mustbeloggedin','conference'));
			
		} else {
			if(!$request->getSessionData('conference'))
			{
				
				$this->dieUsageMsg(array('badaccess-groups'));
				
			}
		}
		
		
		if(isset($params['username']))
		{
			
			$username=$params['username'];
			
		} else {
			
			$this->dieUsageMsg(array('missingparam',$params['username']));
			
		}
		
		if(isset($params['category']))
		{
			
			$category=$params['category'];
			
		} else {
			
			$this->dieUsageMsg(array('missingparam',$params['category']));
			
		}
		
		if(isset($params['post']))
		{
			
			$post=$params['post'];
			
		} else {
			
			$this->dieUsageMsg(array('missingparam',$params['post']));
			
		}
		
		$addedUser=User::newFromName($username,true);
		if($addedUser->getId()==0)
		{
			
			$this->dieUsageMsg('nosuchuser',$params['username']);
			
		} elseif($addedUser===false)
		{
			
			$this->dieUsageMsg('invaliduser',$params['username']);
			
		}
		$errors=$this->mustValidateInputs($category, $post);
		if(count($errors))
		{
			//depending on the error
					//$this->dieUsageMsg(array('spamdetected',put the parameter due to which error was thrown))
		}
		$conferenceSessionArray=$request->getSessionData('conference');
		$conferenceId=$conferenceSessionArray['id'];
		$isOrganizer=ConferenceOrganizerUtils::isOrganizerFromConference($addedUser->getId(), $conferenceId);
		if($isOrganizer)
		{
			$this->dieUsageMsg(array('createonly-exists'));
		} else {
			$catpost=array(array('cat'=>$category,'post'=>$post));
			$organizer=ConferenceOrganizer::createFromScratch($conferenceId, $addedUser->getId(), $catpost);
			$resultApi = $this->getResult();
			if($organizer && $organizer->getOrganizerId())
			{
				$result['done']=true;
				$result['id']=$organizer->getOrganizerId();
				$resultApi->addValue(null, $this->getModuleName(), $result);
			} else {
				$result['done']=false;
				$resultApi->addValue(null, $this->getModuleName(), $result);
			}
		}
		
		
	}
	public function mustValidateInputs($category, $post)
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
		'username'=>null,
		'category'=>null,
		'post'=>null
		);	
	}
	public function getParamDescription()
	{
		return array(
		'username'=>'Username  of the organizer',
		'category'=>'Category which the organizer belongs to',
		'post'=>'The role which has been assigned to the organizer'
		);
	}
	public function getDescription()
	{
		return 'Add Organizer Details';
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