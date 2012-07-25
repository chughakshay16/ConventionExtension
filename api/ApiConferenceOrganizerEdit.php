<?php
/**
 *
 * @todo complete mustValidateInputs()
 * @author chughakshay16
 *This module is implemented slightly differently in terms of the parameters passed,
 *here category and post values passed are the values to be updated and not the original values
 *Add the settings for the request parameters in the getAllowedParams() and remove the condition blocks from execute() method
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
		$resultApi = $this->getResult();
		$user = $this->getUser();
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
		if(isset($params['username']))
		{
				
			$username=$params['username'];
				
			//now check if one of the values category and post are passed if not throw the error
			//Note : here '' or NULL means the same i.e dont change their value
			//here we wont
			/*if(!isset($params['category']))
			 {
			$this->dieUsageMsg(array('missingparam',$params['category']));
			} elseif (!isset($params['post'])) {
			$this->dieUsageMsg(array('missingparam',$params['post']));
			} else {
			$category = $params['category'];
			$post = $params['post'];
			}*/
			if($params['category'] && $params['post'])
			{
				$category = $params['category'];
				$post = $params['post'];
				$categoryto = $params['categoryto'] ? $params['categoryto'] : $params['category'];
				$postto = $params['postto'] ? $params['postto'] : $params['post'];
			} else {
				//throw an error
				if(!$params['category'])
				{
					$this->dieUsageMsg(array('missingparam',$params['category']));
				} else {
					$this->dieUsageMsg(array('missingparam',$params['post']));
				}
			}

		} else {
				
			$this->dieUsageMsg(array('missingparam',$params['username']));
				
		}

		//before we start to edit the page, make sure that a new (cat,post) is passed along
		if($category == $categoryto && $postto == $post)
		{
			$result['msg'] = 'No edit needed. Details passed were same as before';
			$result['done'] = true;
			$result['noedit'] = true;
			$resultApi->addValue(null, $this->getModuleName(), $result);
			return ;
		}
		$conferenceId=$sessionData['id'];

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
				
			$catpostOld=array(array('category'=>$category,'post'=>$post));
			$catpostNew = array(array('category'=>$categoryto,'post'=>$postto));
			$result=ConferenceOrganizer::performEdit($conferenceId, $username, $catpostNew, $catpostOld);
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
				'post'=>null,
				'categoryto'=>null,
				'postto'=>null
		);
	}
	public function getParamDescription()
	{
		return array(
				'username'=>'Username  of the organizer',
				'category'=>'Category which the organizer belongs to',
				'post'=>'The role which has been assigned to the organizer',
				'categoryto'=>'New category for the organizer',
				'postto'=>'New post for the organizer'
		);
	}
	public function getDescription()
	{
		return 'Edit Organizer Details';
	}
	public function getPossibleErrors()
	{
		$user = $this->getUser();
		return array_merge(parent::getPossibleErrors(), array(
				array('mustbeloggedin','conference'),
				array('badaccess-groups'),
				array('missingparam','category'),
				array('missingparam','post'),
				array('missingparam','username'),
				array('nosuchuser', 'username'),
				array('invaliduser', 'username'),
				array('nocreate-missing')
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