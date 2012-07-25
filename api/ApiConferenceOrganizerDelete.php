<?php
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
		$username = $params['username'];
		$category = $params['category'];
		$post = $params['post'];
		$conferenceId=$sessionData['id'];
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
			$result=ConferenceOrganizer::performDelete($conferenceId, $username , $category, $post);
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
				'username'=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true),
				'category'=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true),
				'post'=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true)
		);
	}
	public function getParamDescription()
	{
		return array(
				'username'=>'Username  of the organizer',
				'category'=>'Category of the organizer',
				'post'=>'Post of the organizer'
		);
	}
	public function getDescription()
	{
		return 'Delete Organizer Details';
	}
	public function getPossibleErrors()
	{
		return array_merge(parent::getPossibleErrors(), array(
				array('mustbeloggedin', 'conference'),
				array('badaccess-groups'),
				array('nosuchuser', 'username'),
				array('invaliduser', 'username'),
				array('missingparam', 'username'),
				array('cannotdelete', 'this organizer')
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