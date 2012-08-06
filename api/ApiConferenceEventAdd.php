<?php
/**
 *
 * @todo
 * 1. complete mustValidateInputs()
 * 2. before creating a new event check if the slots are available or not
 * 3. make error messages more clear
 * @author chughakshay16
 *
 */
class ApiConferenceEventAdd extends ApiBase
{
	public function __construct( $main, $action )
	{
		parent::__construct( $main, $action );
	}
	public function execute()
	{
		// in this case all the parameters must be passed through the client
		$params = $this->extractRequestParams();
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

		$roomNo = $params['location'];
		$startTime = $params['starttime'];
		$endTime = $params['endtime'];
		$day = $params['day'];
		$topic = $params['topic'];
		$group = $params['group'];
		if ($day)
		{
			$day = str_replace( '/', '', $day );
		}	
			
		//now check for the validity of location and event titles
		$conferenceId = $sessionData['id'];
		$conferenceTitle = $sessionData['title'];

		$locationTitleText = $conferenceTitle . '/locations/' . $roomNo;
		$locationTitle = Title::newFromText($locationTitleText);
		if ( !$locationTitle )
		{
				
			$this->dieUsageMsg( array( 'invalidtitle', $roomNo ) );
				
		} elseif ( !$locationTitle->exists() ) {
				
			$this->dieUsageMsg( array( 'nocreate-missing' ) );
				
		}

		$errors = $this->mustValidateInputs( $startTime, $endTime , $day, $topic, $group );
		if ( count( $errors ) )
		{
				
			//depending on the error
			//$this->dieUsageMsg(array('spamdetected',put the parameter due to which error was thrown))
				
		}

		$eventTitleText = $conferenceTitle . '/events/' . $topic . '-' . $day . '-' . $startTime . '-' . $endTime . '-' . $group;
		$eventTitle = Title::newFromText( $eventTitleText );
		if ( !$eventTitle )
		{
				
			$this->dieUsageMsg( array( 'invalidtitle', 'Title created with passed parameters' ) );
				
		} elseif ( $eventTitle->exists() ) {
				
			$this->dieUsageMsg( array( 'createonly-exists' ) );
				
		}


		$locationId = $locationTitle->getArticleID();
		$location = EventLocation::loadFromId( $locationId );
		/* @todo before creating the event check if the slot is available for the location provided */
		$event = ConferenceEvent::createFromScratch( $conferenceId, $location, $startTime, $endTime, $day, $topic, $group );
		$resultApi = $this->getResult();
		if($event && $event->getEventId())
		{
			/* modify the template */
			$dateArray = CommonUtils::parseDate( $day );
			$name = $conferenceTitle . '/' . $dateArray['month'] . ' ' . $dateArray['date'] . ', ' . $dateArray['year'];
			$schedule = ConferenceSchedule::loadFromName( $name );
			$schedule->addEvent( $event );
			
			$result['done'] = true;
			$result['id'] = $event->getEventId();
			$result['starttime'] = $event->getStartTime(); /* mmddyyyy*/
			$result['endtime'] = $event->getEndTime();
			$result['group'] = $event->getGroup();
			$result['day'] = $event->getDay();
			$result['topic'] = $event->getTopic();
			$result['location'] = $location->getRoomNo();
			$result['locationurl']= $locationTitle->getFullURL();
			$result['eventurl'] = $eventTitle->getFullURL();
			$result['msg'] = 'The event has been successfully created';
			$resultApi->addValue( null, $this->getModuleName(), $result );
		} else {
			$result['done'] = false;
			$result['msg'] = 'The event could not be added . Try again';
			$resultApi->addValue( null, $this->getModuleName(), $result );
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
				'topic'		=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true),
				'group'		=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true),
				'starttime'	=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true),
				'endtime'	=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true),
				'day'		=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true),
				'location'	=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true)
				
		);
	}
	public function getParamDescription()
	{
		return array(
				'location'		=> 'Room no of the location',
				'starttime'		=> 'Starting time for the event',
				'endtime'		=> 'Ending time for the event',
				'day'			=> 'Day on which event is held',
				'topic'			=> 'Topic of the conference',
				'group'			=> 'Group of people for whom this event is held'
		);
	}
	public function getDescription()
	{
		return 'Add Event Details';
	}
	public function getPossibleErrors()
	{
		$user = $this->getUser();
		return array_merge( parent::getPossibleErrors(), array(
				array( 'mustbeloggedin', 'Wiki'),
				/*array('invaliduser', $user->getName()),*/
				array( 'badaccess-groups' ),
				array( 'missingparam', 'roomno' ),
				array( 'missingparam', 'starttime' ),
				array( 'missingparam', 'endtime' ),
				array( 'missingparam', 'day' ),
				array( 'missingparam', 'topic' ),
				array( 'missingparam', 'group' ),
				array( 'invalidtitle', 'roomno' ),
				array( 'invalidtitle', 'Title created with passed parameters' ),
				array( 'createonly-exists' ),
				array( 'nocreate-missing' )
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