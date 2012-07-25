<?php
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
		$params=$this->extractRequestParams();
		$user = $this->getUser();
		$request=$this->getRequest();

		//still need to decide on what messages you should choose while throwing these errors
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

		$addedUser=User::newFromName($username,true);
		if($addedUser->getId()==0)
		{
				
			$this->dieUsageMsg(array('nosuchuser',$params['username']));
				
		} elseif($addedUser===false)
		{
				
			$this->dieUsageMsg(array('invaliduser',$params['username']));
				
		}
		$errors=$this->mustValidateInputs($category, $post);
		if(count($errors))
		{
			//depending on the error
			//$this->dieUsageMsg(array('spamdetected',put the parameter due to which error was thrown))
		}
		$conferenceId=$sessionData['id'];
		$catpost=array(array('category'=>$category,'post'=>$post));
		$organizer=ConferenceOrganizer::createFromScratch($conferenceId, $addedUser->getId(), $catpost);
		$resultApi = $this->getResult();
		if($organizer && $organizer->getOrganizerId())
		{
			//$orgurl = Title::makeTitle(NS_MAIN, $conferenceId.'/organizers/'.$user->getName())->getFullURL();
			$result['done']=true;
			$result['msg'] = 'The organizer was successfully added';
			$result['id']=$organizer->getOrganizerId();
			$result['username'] = $addedUser->getName();
			$result['category'] = $category;
			$result['post'] = $post;
			$result['userpage'] = $user->getUserPage()->getFullURL();
			$class = '';
			if(!$user->getUserPage()->exists())
			{
				$class = 'new';
			}
			$result['userpageclass'] = $class;
			$resultApi->addValue(null, $this->getModuleName(), $result);
		} else {
			$result['done']=false;
			$result['msg'] = 'The organizer could not be added. Try again.';
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
		return array_merge(parent::getPossibleErrors(), array(
				array('mustbeloggedin','conference'),
				array('badaccess-groups'),
				array('missingparam','username'),
				array('missingparam','category'),
				array('missingparam','post'),
				array('nosuchuser','username'),
				array('invaliduser','username'),
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