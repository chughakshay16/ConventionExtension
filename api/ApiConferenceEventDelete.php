<?php
/**
 *
 * @todo complete mustValidateInputs()
 * @author chughakshay16
 *
 */
class ApiConferenceEventDelete extends ApiBase
{
	public function __construct($main, $action)
	{
		parent::__construct($main, $action);
	}
	public function execute()
	{
		// in this case all the parameters must be passed through the client
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
		$startTime = $params['starttime'];
		$endTime = $params['endtime'];
		$day = $params['day'];
		$topic = $params['topic'];
		$group = $params['group'];
		$day = str_replace('/','',$day);
		//now check for the validity of location and event titles
		$conferenceId = $sessionData['id'];
		$conferenceTitle = $sessionData['title'];
		$errors = $this->mustValidateInputs($startTime, $endTime , $day, $topic, $group);
		if(count($errors))
		{
				
			//depending on the error
			//$this->dieUsageMsg(array('spamdetected',put the parameter due to which error was thrown))
				
		}

		$eventTitleText = $conferenceTitle.'/events/'.$topic.'-'.$day.'-'.$startTime.'-'.$endTime.'-'.$group;
		$eventTitle = Title::newFromText($eventTitleText);
		if(!$eventTitle)
		{
				
			$this->dieUsageMsg(array('invalidtitle','Title created with the params passed'));
				
		} elseif (!$eventTitle->exists()){
				
			$this->dieUsageMsg(array('cannotdelete','this event'));
				
		}


		$result = ConferenceEvent::performDelete($conferenceId, $startTime, $endTime, $day, $topic, $group);
		$resultApi = $this->getResult();
		$resultApi->addValue(null, $this->getModuleName(), $result);
	}
	private function mustValidateInputs($startTime, $endTime, $day, $topic, $group)
	{
		//no need to perform null checks as none of the values passed will be null
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
				'starttime'=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true),
				'endtime'=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true),
				'topic'=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true),
				'group'=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true),
				'day'=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true)
		);
	}
	public function getParamDescription()
	{
		return array(
				'starttime'=>'Starting time of the event',
				'endtime'=>'Ending time of the event',
				'topic'=>'Topic of the event',
				'group'=>'Group that will be attending this event',
				'day'=>'Day on which this event will happen'
		);
	}
	public function getDescription()
	{
		return 'Delete Event Details';
	}
	public function getPossibleErrors()
	{
		$user = $this->getUser();
		return array_merge(parent::getPossibleErrors(), array(
				array('mustbeloggedin', 'conference'),
				array('badaccess-groups'),
				array('invaliduser', $user->getName()),
				array('invalidtitle', 'Title created with passed params'),
				array('cannotdelete','this event'),
				array('missingparam','starttime'),
				array('missingparam','endtime'),
				array('missingparam','topic'),
				array('missingparam','group'),
				array('missingparam','day'),
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