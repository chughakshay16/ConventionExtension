<?php
/**
 *
 * @todo see line 237
 * @todo complete mustValidateInputs()
 * @author chughakshay16
 *
 */
class ApiConferenceEventEdit extends ApiBase
{
	public function __construct( $main, $action )
	{
		parent::__construct( $main, $action );
	}
	public function execute()
	{
			
			
		$params = $this->extractRequestParams();
		$resultApi = $this->getResult();
		$request = $this->getRequest();
		$user = $this->getUser();
		if ( !$user->isLoggedIn() )
		{
			$this->dieUsageMsg( array( 'mustbeloggedin', 'Wiki' ) );
		}
		
		$groups = $user->getGroups();
		if ( !in_array( 'sysop', $groups ) )
		{
			$this->dieUsageMsg( array( 'badaccess-groups' ) );
		}
		
		$sessionData = $request->getSessionData( 'conference' );
		if ( !$sessionData )
		{
			$this->dieUsage( 'No conference details were found in the session object for this user', 'noconfinsession' );
		}
		if ( !$params['starttimeto']
				&& !$params['endtimeto'] && !$params['dayto'] && 
				!$params['topicto'] && !$params['groupto'] && !$params['locationto'] ) {
			
			$this->dieUsage( 'Atleast one of the new params must be set in the request', 'atleastparam' );
			
		} else {
				
			$starttimeto = $params['starttimeto'] ? $params['starttimeto'] : $params['starttime'];
			$endtimeto = $params['endtimeto'] ? $params['endtimeto'] : $params['endtime'];
			$groupto = $params['groupto'] ? $params['groupto'] : $params['group'];
			$topicto = $params['topicto'] ? $params['topicto'] : $params['topic'];
			$dayto = $params['dayto'] ? $params['dayto'] : $params['day'];
			$dayto = str_replace( '/', '', $dayto );
			$errors = $this->mustValidateInputs( $params['starttime'], $params['endtime'], $params['day'], $params['topic'], $params['group'] );
			if ( count( $errors ) )
			{
				//depending on the error
				//$this->dieUsageMsg(array('spamdetected',put the parameter due to which error was thrown))
			}
			//$conferenceSessionArray = $request->getSessionData('conference');
			
			//here we dont need to check if the location is modified or not (that case will eventually be checked in performEdit() function)
			//$params['locationto] may be null or '', so do perform the check that location is not null in performEdit() function
			// and if it is then dont change the location value
			$conferenceId = $sessionData['id'];
			$conferenceTitle = $sessionData['title'];
			if ( $params['locationto'] )
			{
				$locationText = $conferenceTitle . '/locations/' . $params['locationto'];
				$titleLocation = Title::newFromText( $locationText );
				if ( !$titleLocation )
				{
				
					$this->dieUsageMsg( array( 'invalidtitle', $params['locationto'] ) );
				
				} elseif ( !$titleLocation->exists() ) {
				
					$this->dieUsageMsg( array( '', $params['locationto'] ) );
				} else {
					$locationId = $titleLocation->getArticleID();
					$location = EventLocation::loadFromId( $locationId );
				}
			}
			//modify the day value
			$day = str_replace( '/', '', $params['day'] );
			$oldText = $conferenceTitle . '/events/' . $params['topic'] . '-' . $day . '-' . $params['starttime'] . '-' . $params['endtime'] . '-' . $params['group'];
			$newText = $conferenceTitle . '/events/' . $topicto . '-' . $dayto . '-' . $starttimeto . '-' . $endtimeto . '-'.$groupto;
			$newTitle = Title::newFromText( $newText );
			$oldTitle = Title::newFromText( $oldText );
			$oldTalkPage = $oldTitle->getTalkPage();
			$newTalkPage = $newTitle->getTalkPage();
			if ( !$oldTitle )
			{
				$this->dieUsageMsg( array( 'invalidtitle', 'Old title created with the params passed' ) );
					
			} elseif ( !$oldTitle->exists() ) {
					
				$this->dieUsageMsg( array( 'nocreate-missing' ) );
					
			} else {
				$eventId = $oldTitle->getArticleID();
				$oldLocationId = ConferenceEventUtils::getLocationId( $eventId );
				$oldLocation = EventLocation::loadFromId( $oldLocationId );
			}
			
			if ( $oldTalkPage->exists() || $newTalkPage->exists() ) {
				//debug this error
				//and do something about it
			} elseif ( !$newTitle ) {
					
				$this->dieUsageMsg( array( 'invalidtitle', 'New title created with the params passed' ) );
					
			} elseif ( $newTitle->exists() ) {
				if ( $oldText === $newText )
				{
					// this implies that all the new property values sent are same as the old values,
					// so now check for the change in location
					if ( !$locationId || $oldLocationId == $locationId )
					{
							
						//no need to perform an edit , just send back the msg stating no edit operation was performed
						$result['msg'] = 'The event details passed were same as before, so no details have been modified';
						$result['done'] = true;
						$result['noedit'] = true;
						$resultApi->addValue( null, $this->getModuleName(), $result );
						return ;
							
					} else {
						$onlyLocationChanged = true;
						$result = ConferenceEvent::performEdit( $conferenceId, $location, $params['starttimeto'], $params['endtimeto'], 
								$dayto, $params['topicto'], $params['groupto'] );
						if ( $result['done'] )
						{
							/* modify the template */
							$dateArray = CommonUtils::parseDate( $dayto );
							$name = $conferenceTitle . '/'. $dateArray['month'] . ' ' . $dateArray['date'] . ', ' . $dateArray['year'];
							$schedule = ConferenceSchedule::loadFromName( $name );
							$oldEvent = new ConferenceEvent( null, null, $oldLocation, $params['starttime'], $params['endtime'], 
									$day, $params['topic'], $params['group'] );
							$newEvent = new ConferenceEvent( null, null, $location, $starttimeto, $endtimeto, $dayto, $topicto, $groupto );
							$schedule->editEvent( $oldEvent, $newEvent );
						}
						$resultApi->addValue( null, $this->getModuleName(), $result );
						return ;
					}
				} else {
						
					$this->dieUsageMsg( array( 'createonly-exists' ) );
						
				}	
					 
			}

			
			# move operation
			$createRedirect  = false;
			$reason = 'The admin is editing the details of the event';
			$retval = $oldTitle->moveTo( $newTitle, true, $reason, $createRedirect );
			if ( $retval !== true )
			{
				$this->dieUsageMsg( reset( $retval ) );
			}
			
			
			# actual edit
			$result = ConferenceEvent::performEdit( $conferenceId, $location, $params['starttimeto'], $params['endtimeto'], 
				$dayto, $params['topicto'], $params['groupto'] );
			if ( $result['done'] )
			{
				/* modify the template */
				$oldEvent = new ConferenceEvent( null, null, $oldLocation, $params['starttime'], $params['endtime'], $day,
						$params['topic'], $params['group'] );
				if ( !isset( $locationId ) )
				{
					$location = $oldLocation;
				}
				$newEvent = new ConferenceEvent( null, null, $location, $starttimeto, $endtimeto, $dayto, $topicto, $groupto );
				if ( $day == $dayto )
				{
					$dateArray = CommonUtils::parseDate( $dayto );
					$name = $conferenceTitle . '/' . $dateArray['month'] . ' ' . $dateArray['date'] . ', ' . $dateArray['year'];
					$schedule = ConferenceSchedule::loadFromName( $name );
					$schedule->editEvent( $oldEvent, $newEvent );
				} else {
					$dateArrayOld = CommonUtils::parseDate( $day );
					$dateArrayNew = CommonUtils::parseDate( $dayto );
					$nameOld = $conferenceTitle . '/' . $dateArrayOld['month'] . ' ' . $dateArrayOld['date'] . ', ' . $dateArrayOld['year'];
					$nameNew = $conferenceTitle . '/' . $dateArrayNew['month'] . ' ' . $dateArrayNew['date'] . ', ' . $dateArrayNew['year'];
					$scheduleOld = ConferenceSchedule::loadFromName( $nameOld );
					$scheduleNew = ConferenceSchedule::loadFromName( $nameNew );
					$scheduleOld->deleteEvent( $oldEvent );
					$scheduleNew->addEvent( $newEvent );
				}
			}
			$resultApi->addValue( null, $this->getModuleName(), $result );
				
		}
	}
	private function mustValidateInputs( $startTime, $endTime, $day, $topic, $group )
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
				'starttime'		=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true),
				'endtime'		=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true),
				'topic'			=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true),
				'group'			=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true),
				'day'			=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true),
				'starttimeto'	=>null,
				'endtimeto'		=>null,
				'topicto'		=>null,
				'groupto'		=>null,
				'dayto'			=>null,
				'locationto'	=>null );
	}
	public function getParamDescription()
	{
		return array(
				'starttime'		=>'Starting time of the event',
				'endtime'		=>'Ending time of the event',
				'topic'			=>'Topic of the event',
				'group'			=>'Group that will be attending this event',
				'day'			=>'Day on which this event will happen',
				'starttimeto'	=>'New starting time of the event',
				'endtimeto'		=>'New ending time of the event',
				'topicto'		=>'New topic for the event',
				'groupto'		=>'New group for the event',
				'dayto'			=>'New day for the event',
				'locationto'	=>'New room no for the event'
		);
	}
	public function getDescription()
	{
		return 'Delete Event Details';
	}
	public function getPossibleErrors()
	{
		$user = $this->getUser();
		return array_merge( parent::getPossibleErrors(), array(
				array( 'mustbeloggedin', 'conference' ),
				array( 'badaccess-groups' ),
				/*array('invaliduser',$user->getName()),*/
				array( 'invalidtitle', 'Old title created with params passed' ),
				array( 'invalidtitle', 'New title created with params passed' ),
				array( 'createonly-exists' ),
				array( 'nocreate-missing' ),
				array( 'code' => 'atleastparam', 'info' => 'Atleast one of the params should be passed in the request' )
		) );
	}
	public function getExamples()
	{

	}
	public function getVersion()
	{
		return __CLASS__ . ': $Id$';
	}

}