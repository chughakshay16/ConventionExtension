<?php
/**
 * Notes :
 * 1. if using this module internally do remember to pass 'conference' and 'title' value without underscores
 * @author User:Chughakshay16
 *
 */
class ApiAuthorSubmissionDelete extends ApiBase
{
	//how do we store conference details in the session for the author ? Answer : We don't !!
	public function __construct( $main, $action )
	{
		parent::__construct( $main, $action );
	}
	public function execute()
	{
		$params= $this->extractRequestParams();
		# here $params['title'] signifies just the core title of the submission and not 'confTitle/submissions/title'
		$user = $this->getUser();
		
		if ( !$user->isLoggedIn() )
		{
			$this->dieUsageMsg( array( 'mustbeloggedin', 'Wiki' ) );
		}
		$title = $params['title'];
		$conferenceTitle = $params['conference'];
		
		# now we have a valid user, check if we have a valid author as well
		$isAuthor = UserUtils::isSpeaker( $user->getId() );
		if ( $isAuthor )
		{
			# now check for the validity of title passed and conference title
			$text = $conferenceTitle . '/authors/' . $user->getName() . '/submissions/' . $title;
			$titleObj = Title::newFromText( $text );
			$titleConfObj = Title::newFromText( $conferenceTitle );
			$conferenceId = ConferenceUtils::getConferenceId( $titleConfObj->getDBkey() );
				
			if ( !$titleObj )
			{

				$this->dieUsageMsg( array( 'invalidtitle', $params['title'] ) );

			} elseif ( !$titleConfObj ) {

				$this->dieUsageMsg( array( 'invalidtitle', $params['conference'] ) );

			} elseif ( !$conferenceId ) {

				$this->dieUsageMsg( array( 'invalidtitle', $params['conference'] ) );

			} elseif ( !$titleObj->exists() ) {

				$this->dieUsageMsg( array( 'cannotdelete', 'this submission' ) );
			}
				
			
			# till this point we have validated conference, title and the user, so now we can go ahead and perform the delete
			$result = ConferenceAuthor::performSubmissionDelete( $titleObj );
			$resultApi = $this->getResult();
			$resultApi->addValue( null, $this->getModuleName(), $result );
			
			
		} else {
			# not a valid author	
			$this->dieUsageMsg( array( 'badaccess-groups' ) );
				
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
				'title'			=>array(
						ApiBase::PARAM_TYPE		=> 'string',
						ApiBase::PARAM_REQUIRED	=> true),
				'conference'	=>array(
						ApiBase::PARAM_TYPE		=> 'string',
						ApiBase::PARAM_REQUIRED	=> true)
		);
	}
	public function getParamDescription()
	{
		return array(
				'title'		=> 'Title of the submission',
				'conference'=> 'Title of the conference this submission belongs to'
		);
	}
	public function getDescription()
	{
		return 'Delete Submission Details';
	}
	public function getPossibleErrors()
	{
		$user = $this->getUser();
		return array_merge( parent::getPossibleErrors(), array(
				array( 'mustbeloggedin', 'Wiki' ),
				array( 'missingparam', 'title' ),
				array( 'missingparam', 'conference' ),
				/*array( 'invaliduser', $user->getName() ),*/
				array( 'invalidtitle', 'title' ),
				array( 'invalidtitle', 'conference' ),
				array( 'cannotdelete', 'this submission' ),
				array( 'badaccess-groups' )
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