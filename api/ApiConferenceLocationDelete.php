<?php
/**
 *
 * @todo see line 208
 * @todo complete mustValidateInputs()
 * @author chughakshay16
 *
 */
class ApiConferenceLocationDelete extends ApiBase
{
	public function __construct( $main, $action )
	{
		parent::__construct( $main, $action );
	}
	public function execute()
	{
		//in all the api classes we never take it into consideration that for an admin the conference session data may have gotten destroyed
		/**
		 * @todo take care of this above scenario
		 */
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
		$roomNo = $params['roomno'];
		// we are performing this check even though its gonna be done again in performDelete() function, 
		//just so that we dont have to go through much more before we get into this situation
		$conferenceId = $sessionData['id'];
		$conferenceTitle = $sessionData['title'];

		$titleText = $conferenceTitle . '/locations/' . $roomNo;
		$title = Title::newFromText( $titleText );
		if ( !$title )
		{
				
			$this->dieUsageMsg( array( 'invalidtitle', $params['roomno'] ) );
				
		} elseif ( !$title->exists() ) {
				
			$this->dieUsageMsg( array( 'cannotdelete', 'this location' ) );
		}

		//now all the checks have been made
		//do the actual delete
		$result = EventLocation::performDelete( $conferenceId, $roomNo );
		if ( $result['done'] )
		{
			/* modify the template */
			$conferenceTag = ConferenceUtils::loadFromConferenceTag( $conferenceId );
			$days = CommonUtils::getAllConferenceDays( $conferenceTag['startDate'], $conferenceTag['endDate']);
			foreach ( $days as $day )
			{
				$name = $conferenceTitle . '/' . $day;
				$schedule = ConferenceSchedule::loadFromName( $name );
				$dummyLocation = new EventLocation( $roomNo, null, null );
				$schedule->deleteLocation( $dummyLocation );
			}
		}
		$resultApi = $this->getResult();
		$resultApi->addValue( null, $this->getModuleName(), $result );

	}
	private function mustValidateInputs($description , $url)
	{
		return array();
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
				'roomno'=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true)
		);
	}
	public function getParamDescription()
	{
		return array(
				'roomno'=>'Room no of the location'
		);
	}
	public function getDescription()
	{
		return 'Delete Location Details';
	}
	public function getPossibleErrors()
	{
		$user = $this->getUser();
		return array_merge( parent::getPossibleErrors(), array(
				array( 'mustbeloggedin', 'Wiki' ),
				/*array('invaliduser',$user->getName()),*/
				array( 'badaccess-groups' ),
				array( 'missingparam', 'roomno' ),
				array( 'invalidtitle', 'roomno' ),
				array( 'cannot delete', 'this location' )
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