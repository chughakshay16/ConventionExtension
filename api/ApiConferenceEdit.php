<?php
/**
 * 
 * This class performs an edit operation with the conference details.
 * The important thing to note here is that the parameters which are sent in this case are the ones which need to be edited, 
 * they are not used to identify a page in the database. 
 * So it means that performEdit() has to check that only the changed values are edited in the page content.
 * There are three possible scenarios which are covered here :
 * 1. only title is changed
 * 2. title and other content is changed
 * 3. only other details except title are changed
 * @todo big process involving the title change
 * @todo complete mustValidateInputs()
 * @author chughakshay16
 *
 */
class ApiConferenceEdit extends ApiBase
{
	public function __construct($main,$action)
	{
		
		parent::__construct($main, $action);
		
	}
	public function execute()
	{
		$params = $this->extractRequestParams();
		$title = Title::newFromText( $params['title'] );
		if ( !$title )
		{
			$this->dieUsageMsg( array( 'invalidtitle', $params['title'] ) );
				
		} elseif ( !$title->exists() ) {
				
			$this->dieUsageMsg( array( 'nocreate-missing' ) );
				
		}
		if ( $params['titleto'] )
		{
				
			$isTitleChange = true;
			$titleTo = Title::newFromText( $params['titleto'] );
			if ( !$titleTo )
			{
					
				$this->dieUsageMsg( array( 'invalidtitle', $params['titleto'] ) );
					
			} elseif ( $titleTo->exists() ) {
					
				$this->dieUsageMsg( array( 'createonly-exists' ) );
					
			}
		}	
		$request = $this->getRequest();
		
		//still need to decide on what messages you should choose while throwing these errors
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
		if ( !isset( $params['venue'] ) && !isset( $params['description'] ) && !isset( $params['capacity'] ) && 
				!isset( $params['startdate'] ) && !isset( $params['enddate'] ) && !$isTitleChange ) {
			
			$this->dieUsage( 'Atleast one of the params should be passed in the request', 'atleastparam' );
			
		} else {
			
			$sessionData = $request->getSessionData( 'conference' );
			if ( !isset($sessionData) || $sessionData['title'] != $title->getDBkey() )
			{
				
				//if session details dont match or the session doesnt exist, we will have to re-populate the session with the new details
				$conferenceSessionArray['id'] = $title->getArticleID();
				$conferenceSessionArray['title'] = $title->getDBkey();
				$request->setSessionData( 'conference', $conferenceSessionArray );
				
			}
			
		}
		
		
		$conferenceId = $sessionData['id'];
		//get all the param values
		$venue = $params['venue'];
		$description = $params['description'];
		$capacity = $params['capacity'];
		$startDate = $params['startdate'];
		$endDate = $params['enddate'];
		$titleTo = $params['titleto'];
		$errors = $this->mustValidateInputs($description,$capacity,$startDate,$endDate,$venue);
		if ( count( $errors ) )
		{
			
			//depending on the error
			//$this->dieUsageMsg(array('spamdetected',put the parameter due to which error was thrown))
			//after writing this part add those errors to getPossibleErrors() function
			
		}
		if ( $isTitleChange ) //there is a title change, this is a big process
		{
			//just make a move operation with $createRedirect = false;see how its implemented in other api functions of this extension
			//change getPossibleErrors()
			$conferenceSessionArray = array( 'id' => $conferenceId, 'title' => $titleTo );
			$request->setSessionData( 'conference', $conferenceSessionArray );
			$title = $titleTo;/* always pass title text (rightmost part) */
			
		} 
		//remember to check for the title change in performEdit()
		$startDate = str_replace( '/', '', $startDate );
		$endDate = str_replace( '/', '', $endDate );
		$result = Conference::performEdit( $conferenceId, $title, $venue, $description, $capacity, $startDate, $endDate );
		$resultApi = $this->getResult();
		if( $result['done'] )
		{
			$result['title'] = $title->getText(); 
			$result['venue'] = $venue;
			$result['capacity'] = $capacity;
			$result['description'] = $description;
			$result['startdate'] = $startDate;
			$result['enddate'] = $endDate;
		}
		$resultApi->addValue( null, $this->getModuleName(), $result );

	}
	private function mustValidateInputs($description,$capacity,$startDate,$endDate,$venue)
	{
		//this function returns an array of errors
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
		'title'			=> array(
		ApiBase::PARAM_TYPE		=> 'string',
		ApiBase::PARAM_REQUIRED	=> true),
		'titleto'		=> null,
		'capacity'		=> array(
		ApiBase::PARAM_TYPE 	=> 'integer',
		ApiBase::PARAM_MIN		=> 0),
		'venue'			=> null,
		'startdate'		=> null,
		'enddate'		=> null,
		'description'	=> null
		);	
	}
	public function getParamDescription()
	{
		return array(
		'title'			=> 'Title of the conference to edit',
		'titleto'		=> 'New title for the conference',
		'capacity'		=> 'The capacity of the conference',
		'venue'			=> 'The venue for the conference',
		'startdate'		=> 'Start Date for the conference',
		'enddate'		=> 'End Date for the conference',
		'description'	=> 'Description for the conference'
		);
	}
	public function getDescription()
	{
		return 'Edit Conference Details';
	}
	public function getPossibleErrors()
	{	
		return array_merge( parent::getPossibleErrors(), array(
		array( 'invalidtitle', 'title' ),
		array( 'invalidtitle', 'titleto' ),
		array( 'nocreate-missing' ),
		array( 'createonly-exists' ),
		array( 'mustbeloggedin', 'conference' ),
		array( 'badaccess-groups' ),
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