<?php
/**
 * 
 * @todo see line 74
 * @todo complete mustValidateInputs()
 * @author chughakshay16
 * Concept :
 * Even if a move operation is performed on a location wiki page (due to change in room no.) nothing would 
 * have to be changed for an event wiki page( as it will still point to the correct location page -- in move operation page_id is not changed)
 *
 */
class ApiConferenceLocationEdit extends ApiBase
{
	public function __construct( $main, $action )
	{
		parent::__construct( $main, $action );
	}
	public function execute()
	{
		//if there is a change in roomNo it will change the title as well
		$params = $this->extractRequestParams();
		//roomNo is the param which should be sent no matter what
		
		$request = $this->getRequest();
		$user = $this->getUser();
		if ( !$user->isLoggedIn() )
		{
			$this->dieUsageMsg( array( 'mustbeloggedin', 'Wiki' ) );
		}
		
		$groups = $user->getGroups();
		if ( !in_array( 'sysop', $groups))
		{
			$this->dieUsageMsg( array( 'badaccess-groups' ) );
		}
		
		$sessionData = $request->getSessionData( 'conference' );
		if ( !$sessionData )
		{
			$this->dieUsage( 'No conference details were found in the session object for this user', 'noconfinsession' );
		}
		if ( !isset( $params['description'] ) && !isset( $params['url'] ) && !isset( $params['roomnoto'] ) ){
			
			$this->dieUsage( 'Atleast params except roomno should be passed in the request', 'atleastparam' );
			
		} else {
			
			$roomNo = $params['roomno'];
			$description = $params['description'];
			$url = $params['url'];
			$roomNoTo = $params['roomnoto'];
			
		}
		
		
		//we dont need to check the validity of location title , or do we ?
		$conferenceId = $sessionData['id'];
		$conferenceTitle =$sessionData['title'];
		$titleText = $conferenceTitle . '/locations/' . $roomNo;
		$title = Title::newFromText( $titleText );
		if ( !$title )
		{
			
			$this->dieUsageMsg( array( 'invalidtitle', $params['roomno'] ) );
			
		} elseif ( !$title->exists() ) {
			
			$this->dieUsageMsg( array( 'nocreate-missing' ) );
			
		} else {
			$errors = $this->mustValidate( $params['roomno'] );
			if( count( $errors ) )
			{
				$this->dieUsageMsg( array( 'invalidtitle', $params['roomno'] ) );
			}
		}
		
		$errors = $this->mustValidateInputs( $description, $url );
		if ( count( $errors ) )
		{
			
			//depending on the error
					//$this->dieUsageMsg(array('spamdetected',put the parameter due to which error was thrown))
			
		} else {
			if ( $roomNoTo != $roomNo && $roomNoTo )
			{
				
				//its a big change need to think about it
				//now we need to check the validity of roomnoto 
				$toTitleText = $conferenceTitle . '/locations/' . $roomNoTo;
				$titleTo = Title::newFromText( $toTitleText );
				if ( !$titleTo )
				{
					$this->dieUsageMsg( array( 'invalidtitle', $params['roomnoto'] ) );
					
				} elseif ( $titleTo->exists() ) {
					
					$this->dieUsageMsg( array( 'createonly-exists' ) );
					
				}
				$errors = $this->mustValidate( $roomNoTo );//this step could totally be skipped by just passing the default type in getAllowedParams()
				if ( count( $errors ) )
				{
					
					//depending on the error
					//$this->dieUsageMsg(array('spamdetected',put the parameter due to which error was thrown))
					
				}
				//here starts the logic of move operation
				//we can perform a lot of checks here if we want
				//such as 
				//1. its not a title that belongs to File namespace
				//2. this old title should not have a sub-page attached
				//3. this old title should not have a talk page associated with it
				//so lets perform these checks before we dive into the real move operation
				// in our case we dont need to check for the namespace as it will always be MAIN namespace only since we never passed any prefix 
				//in the text or a namespace as a separate parameter
				$oldTalkPage = $title->getTalkPage();
				$newTalkPage = $titleTo->getTalkPage();
				//have a small doubt here that is it possible to have a talk page and not a user page
				if ( $newTalkPage->exists() || $oldTalkPage->exists() )
				{
					//debug this error
					//and do something about it
				}
				// I somehow feel that there is no need to check for the sub-pages as we will never let an admin create one, and even if someone else
				//creates one it shouldnt affect the normal working of this conference
				$createRedirect = false;
				// One very important thing :- user(or admin) in our case should have the 'suppressredirect' right set to true 
				//for this move operation to make sense in our case
				$reason = 'The admin is editing the roomNo of the location';
				$retval = $title->moveTo( $titleTo, true, $reason, $createRedirect );
				if ( $retval !== true ) {
					
					$this->dieUsageMsg( reset( $retval ) );
				}
				/* modify the template */
				$conferenceTag = ConferenceUtils::loadFromConferenceTag( $conferenceId );
				$days = CommonUtils::getAllConferenceDays( $conferenceTag['startDate'], $conferenceTag['endDate']);
				foreach ( $days as $day )
				{
					$name = $conferenceTitle . '/' . $day;
					$schedule = ConferenceSchedule::loadFromName( $name );
					$schedule->editLocation(new EventLocation( $roomNo, null, null ), new EventLocation( $roomNoTo , null, null ) );
				}
				$roomNo = $roomNoTo;
				
			} 
			$result = EventLocation::performEdit( $conferenceId, $roomNo, $description, $url );
			$resultApi = $this->getResult();
			$resultApi->addValue( null, $this->getModuleName(), $result );
		}
	}
	private function mustValidateInputs( $description , $url )
	{
		return array();
	}
	private function mustValidate( $roomno )
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
		'roomno'		=>array(
			ApiBase::PARAM_TYPE=>'string',
			ApiBase::PARAM_REQUIRED=>true),
		'roomnoto'		=>null,
		'description'	=>null,
		'url'			=>null
		);	
	}
	public function getParamDescription()
	{
		return array(
		'roomno'		=> 'Room no of the location',
		'roomnoto'		=> 'New room no of the location',
		'description'	=> 'Description of the location',
		'url'			=> 'Url which points to the image of the location'
		);
	}
	public function getDescription()
	{
		return 'Edit Location Details';
	}
	public function getPossibleErrors()
	{	
		$user = $this->getUser();
		return array_merge(parent::getPossibleErrors(), array(
		array( 'mustbeloggedin', 'conference' ),
		/*array('invaliduser',$user->getName()),*/
		array( 'badaccess-groups' ),
		array( 'missingparam', 'roomno' ),
		array( 'code' => 'atleastparam', 'info' => 'Atleast params except roomno should be passed in the request' ),
		array( 'invalidtitle', 'roomno' ),
		array( 'invalidtitle', 'roomnoto' ),
		array( 'createonly-exists' ),
		array( 'nocreate-missing' ),
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
