<?php
class ApiConferenceAuthorDelete extends ApiBase
{
	public function __construct( $main, $action )
	{
		parent::__construct( $main, $action );
	}
	public function execute()
	{
		#only an author can delete itself
		$user = $this->getUser();
		if ( !$user->isLoggedIn() )
		{
				
			$this->dieUsageMsg( array( 'mustbeloggedin', 'Wiki' ) );
				
		}
		#now go for author checks
		$isAuthor = UserUtils::isSpeaker( $user->getId() );
		if ( $isAuthor )
		{
				
			$result = ConferenceAuthor::performAuthorDelete( $user->getId() );
			$resultApi = $this->getResult();
			$resultApi->addValue( null, $this->getModuleName(), $result );
				
		} else {
				
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
		return array();
	}
	public function getParamDescription()
	{
		return array();
	}
	public function getDescription()
	{
		return 'Delete Author Details';
	}
	public function getPossibleErrors()
	{
		$user = $this->getUser();
		return array_merge( parent::getPossibleErrors(), array(
				array( 'mustbeloggedin', 'Wiki' ),
				array( 'invaliduser', $user->getName() ),
				array( 'badaccess-groups' )
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