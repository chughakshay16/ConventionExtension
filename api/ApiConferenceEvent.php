<?php
/**
 * 
 * @todo complete mustValidateInputs()
 * @author chughakshay16
 *
 */
class ApiConferenceEventAdd extends ApiBase
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
		
		
		if(session_id()=='')
		{
			
			$this->dieUsageMsg(array('mustbeloggedin','conference'));
			
		} elseif (!$request->getSessionData('conference')){
			
			$this->dieUsageMsg(array('badaccess-groups'));
			
		} elseif ($user->getId()==0){
			
			$this->dieUsageMsg(array('invaliduser',$user->getName()));
			
		} /*elseif (!isset($params['location'])){
			
			$this->dieUsageMsg(array('missingparam',$params['location']));
			
		} elseif (!isset($params['starttime'])){
			
			$this->dieUsageMsg(array('missingparam', $params['starttime']));
			
		} elseif (!isset($params['endtime'])){
			
			$this->dieUsageMsg(array('missingparam',$params['endtime']));
			
		} elseif (!isset($params['day'])){
			
			$this->dieUsageMsg(array('missingparam',$params['day']));
			
		} elseif (!isset($params['topic'])){
			
			$this->dieUsageMsg(array('missingparam',$params['topic']));
			
		} elseif (!isset($params['group'])){
			
			$this->dieUsageMsg(array('missingparam',$params['group']));
			
		} else {
			*/
			$roomNo = $params['location'];
			$startTime = $params['starttime'];
			$endTime = $params['endtime'];
			$day = $params['day'];
			$topic = $params['topic'];
			$group = $params['group'];
			
		/*} */
		
		
		//now check for the validity of location and event titles
		$conferenceSessionArray = $request->getSessionData('conference');
		$conferenceId = $conferenceSessionArray['id'];
		$conferenceTitle = $conferenceSessionArray['title'];
		
		$locationTitleText = $conferenceTitle.'/locations/'.$roomNo;
		$locationTitle = Title::newFromText($locationTitleText);
		if(!$locationTitle)
		{
			
			$this->dieUsageMsg(array('invalidtitle',$roomNo));
			
		} elseif (!$locationTitle->exists()) {
			
			$this->dieUsageMsg(array('nocreate-missing'));
			
		}
		
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
			
			$this->dieUsageMsg(array('invalidtitle','Title created with passed parameters'));
			
		} elseif ($eventTitle->exists()){
			
			$this->dieUsageMsg(array('createonly-exists'));
			
		}
		
		
		$locationId = $locationTitle->getArticleID();
		$location = EventLocation::loadFromId($locationId);
		
		$event = ConferenceEvent::createFromScratch($conferenceId, $location, $startTime, $endTime, $day, $topic, $group);
		$resultApi = $this->getResult();
		if($event && $event->getEventId())
		{
			$result['done']=true;
			$result['id']=$event->getEventId();
			$resultApi->addValue(null, $this->getModuleName(), $result);
		} else {
			$result['done']=false;
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
		'location'=>array(
		ApiBase::PARAM_TYPE=>'string',
		ApiBase::PARAM_REQUIRED=>true),
		'starttime'=>array(
		ApiBase::PARAM_TYPE=>'string',
		ApiBase::PARAM_REQUIRED=>true),
		'endtime'=>array(
		ApiBase::PARAM_TYPE=>'string',
		ApiBase::PARAM_REQUIRED=>true),
		'day'=>array(
		ApiBase::PARAM_TYPE=>'string',
		ApiBase::PARAM_REQUIRED=>true),
		'topic'=>array(
		ApiBase::PARAM_TYPE=>'string',
		ApiBase::PARAM_REQUIRED=>true),
		'group'=>array(
		ApiBase::PARAM_TYPE=>'string',
		ApiBase::PARAM_REQUIRED=>true)
		);	
	}
	public function getParamDescription()
	{
		return array(
		'location'=>'Room no of the location',
		'starttime'=>'Starting time for the event',
		'endtime'=>'Ending time for the event',
		'day'=>'Day on which event is held',
		'topic'=>'topic of the conference',
		'group'=>'Group of people for whom this event is held'
		);
	}
	public function getDescription()
	{
		return 'Add Event Details';
	}
	public function getPossibleErrors()
	{	
		$user = $this->getUser();
		return array_merge(parent::getPossibleErrors(), array(
		array('mustbeloggedin','conference'),
		array('invaliduser', $user->getName()),
		array('badaccess-groups'),
		array('missingparam','roomno'),
		array('missingparam','starttime'),
		array('missingparam','endtime'),
		array('missingparam','day'),
		array('missingparam','topic'),
		array('missingparam','group'),
		array('invalidtitle','roomno'),
		array('invalidtitle','Title created with passed parameters'),
		array('createonly-exists'),
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

/**
 * 
 * @todo see line 237
 * @todo complete mustValidateInputs()
 * @author chughakshay16
 *
 */
class ApiConferenceEventEdit extends ApiBase
{
	public function __construct($main, $action)
	{
		parent::__construct($main, $action);
	}
	public function execute()
	{
		$params = $this->extractRequestParams();

		if(session_id()=='')
		{
			$this->dieUsageMsg(array('mustbeloggedin', 'conference'));
		}
		
		$request = $this->getRequest();
		$user = $this->getUser();
		if($request->getSessionData('conference'))
		{
			
			$this->dieUsageMsg(array('badaccess-groups'));
			
		} elseif ($user->getId()==0){
			
			$this->dieUsageMsg(array('invaliduser',$user->getName()));
			
		} 
		if (!isset($params['starttimeto']) 
			&& !isset($params['endtimeto']) && !isset($params['dayto']) && !isset($params['topicto']) && !isset($params['groupto']) ){
				$this->dieUsage('Atleast one of the new params should be passed in the request','atleastparam');
		} else {
			
			$starttimeto = $params['starttimeto'] ? $params['starttimetoo'] : $params['starttime'];
			$endtimeto = $params['endtimeto'] ? $params['endtimeto'] : $params['endtime'];
			$groupto = $params['groupto'] ? $params['groupto'] : $params['group'];
			$topicto = $params['topicto'] ? $params['topicto'] : $params['topic'];
			$dayto = $params['dayto'] ? $params['dayto'] : $params['day'];
			$errors = $this->mustValidateInputs($params['starttime'],$params['endtime'], $params['day'], $params['topic'], $params['group']);
			if(count($errors))
			{
				//depending on the error
					//$this->dieUsageMsg(array('spamdetected',put the parameter due to which error was thrown))
			}
			$conferenceSessionArray = $request->getSessionData('conference');
			$conferenceId = $conferenceSessionArray['id'];
			$conferenceTitle = $conferenceSessionArray['title'];
				$oldText = $conferenceTitle.'/events/'.$params['topic'].'-'.$params['day'].'-'.$params['starttime'].'-'.$params['endtime'].'-'.$params['group'];
				$newText = $conferenceTitle.'/events/'.$topicto.'-'.$dayto.'-'.$starttimeto.'-'.$endtimeto.'-'.$groupto;
				$newTitle = Title::newFromText($newText);
				$oldTitle = Title::newFromText($oldText);
				$oldTalkPage = $oldTitle->getTalkPage();
				$newTalkPage = $newTitle->getTalkPage();
				if(!$oldTitle)
				{
					$this->dieUsageMsg(array('invalidtitle', 'Old title created with the params passed'));
					
				} elseif ($newTitle->exists()){
			
					$this->dieUsageMsg(array('createonly-exists'));
					
				} elseif (!$oldTitle->exists()){
					
					$this->dieUsageMsg(array('nocreate-missing'));	
							
				} elseif ($oldTalkPage->exists() || $newTalkPage->exists()){
					//debug this error 
					//and do something about it
				} elseif (!$newTitle){
					
					$this->dieUsageMsg(array('invalidtitle','New title created with the params passed'));
					
				}
			$createRedirect  = false;
			$reason = 'The admin is editing the details of the event';
			$retval = $oldTitle->moveTo( $newTitle, true, $reason, $createRedirect );
			if ( $retval !== true ) 
			{
				$this->dieUsageMsg( reset( $retval ) );
			}
			//here we dont need to check if the location is modified or not (that case will eventually be checked in performEdit() function)
			//$params['locationto] may be null or '', so do perform the check that location is not null in performEdit() function
			// and if it is then dont change the location value
			$location = null;
			$locationText = $conferenceTitle.'/locations/'.$params['locationto'];
			$titleLocation = Title::newFromText($locationText);
			if($titleLocation && $titleLocation->exists())
			{
				$locationId = $titleLocation->getArticleID();
				$location = EventLocation::loadFromId($locationId);
			}
			$result=ConferenceEvent::performEdit($conferenceId, $location, $params['starttimeto'], $params['endtimeto'], $params['dayto'], $params['topicto'], $params['groupto']);
			$resultApi = $this->getResult();
			$resultApi->addValue(null, $this->getModuleName(), $result);
			
		} 
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
		ApiBase::PARAM_REQUIRED=>true),
		'starttimeto'=>null,
		'endtimeto'=>null,
		'topicto'=>null,
		'groupto'=>null,
		'dayto'=>null,
		'locationto'=>null);	
	}
	public function getParamDescription()
	{
		return array(
		'starttime'=>'Starting time of the event',
		'endtime'=>'Ending time of the event',
		'topic'=>'Topic of the event',
		'group'=>'Group that will be attending this event',
		'day'=>'Day on which this event will happen',
		'starttimeto'=>'New starting time of the event',
		'endtimeto'=>'New ending time of the event',
		'topicto'=>'New topic for the event',
		'groupto'=>'New group for the event',
		'dayto'=>'New day for the event',
		'locationto'=>'New room no for the event'
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
		array('invaliduser',$user->getName()),
		array('invalidtitle','Old title created with params passed'),
		array('invalidtitle','New title created with params passed'),
		array('createonly-exists'),
		array('nocreate-missing'),
		array('code'=>'atleastparam','info'=>'Atleast one of the params should be passed in the request')
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
 * @todo complete mustValidateInputs()
 * @author chughakshay16
 *
 */
class ApiConferenceEventDelete extends ApiBase
{
	public function __construct($main, $action)
	{
	
	}
	public function execute()
	{
		// in this case all the parameters must be passed through the client
		$params = $this->extractRequestParams();
		$request = $this->getRequest();
		$user = $this->getUser();
		
		
		if(session_id()=='')
		{
			
			$this->dieUsageMsg(array('mustbeloggedin', 'conference'));
			
		} elseif (!$request->getSessionData('conference')){
			
			$this->dieUsageMsg(array('badaccess-groups'));
			
		} elseif ($user->getId()==0){
			
			$this->dieUsageMsg(array('invaliduser', $user->getName()));
			
		} /*elseif (!isset($params['starttime'])){
			
			$this->dieUsageMsg(array('missingparam', $params['starttime']));
			
		} elseif (!isset($params['endtime'])){
			
			$this->dieUsageMsg(array('missingparam',$params['endtime']));
			
		} elseif (!isset($params['day'])){
			
			$this->dieUsageMsg(array('missingparam',$params['day']));
			
		} elseif (!isset($params['topic'])){
			
			$this->dieUsageMsg(array('missingparam',$params['topic']));
			
		} elseif (!isset($params['group'])){
			
			$this->dieUsageMsg(array('missingparam',$params['group']));
			
		} */else {
			
			$startTime = $params['starttime'];
			$endTime = $params['endtime'];
			$day = $params['day'];
			$topic = $params['topic'];
			$group = $params['group'];
			
		}
		
		
		//now check for the validity of location and event titles
		$conferenceSessionArray = $request->getSessionData('conference');
		$conferenceId = $conferenceSessionArray['id'];
		$conferenceTitle = $conferenceSessionArray['title'];
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
