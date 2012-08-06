<?php
/**
 *
 * @todo complete mustValidateInputs()
 * @author chughakshay16
 *
 */
class ApiConferenceAuthorEdit extends ApiBase
{
	public function __construct( $main , $action )
	{
		parent::__construct( $main, $action );
	}
	public function execute()
	{
		$params = $this->extractRequestParams();
		$user = $this->getUser();
		# checks for the validity of the user as well
		if ( !$user->isLoggedIn() )
		{
				
			$this->dieUsageMsg( array( 'mustbeloggedin', 'Wiki' ) );
				
		}
		
		if ( !isset( $params['country'] ) && !isset( $params['affiliation'] ) && !isset( $params['url'] ) )
		{
				
			$this->dieUsage( 'Atleast one of the params must be passed in the request', 'atleastparam' );
				
		} else {
				
			$country = $params['country'];
			$affiliation = $params['affiliation'];
			$url = $params['url'];
				
		}
		
		# validate other inputs
		$errors = $this->mustValidateInputs( $country , $affiliation , $url );
		if ( count( $errors ) )
		{
			
			//depending on the error
			//$this->dieUsageMsg(array('spamdetected',put the parameter due to which error occurred))
			//change getPossibleErrors()
			
		} else {
				
			$isAuthor = UserUtils::isSpeaker( $user->getId() );
			if ( $isAuthor )
			{
				# make an actual edit
				$result = ConferenceAuthor::performAuthorEdit( $user->getName(), $country, $affiliation, $url );
				$resultApi = $this->getResult();
				$resultApi->addValue( null, $this->getModuleName(), $result );
				
			} else {
					
				$this->dieUsageMsg( array( 'badaccess-groups' ) );
			}
				
		}
	}
	/**
	 *
	 * @param String $gender
	 * @param String $firstname
	 * @param String $lastname
	 * @todo complete this function
	 */
	public function mustValidateInputs( $gender, $firstname , $lastname )
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
				'country'		=> null,
				'affiliation'	=> null,
				'url'			=> null
		);
	}
	public function getParamDescription()
	{
		return array(
				'country'		=> 'Country that the author lives in',
				'affiliation'	=> 'Affiliation of the author',
				'url'			=> 'Url of the author\'\s personal blog'
		);
	}
	public function getDescription()
	{
		return 'Edit Author Details';
	}
	public function getPossibleErrors()
	{
		$user = $this->getUser();
		return array_merge( parent::getPossibleErrors(), array(
				array( 'mustbeloggedin', 'conference' ),
				array( 'nosuchuser', $user->getName() ),
				array( 'invaliduser', $user->getName() ),
				array( 'badaccess-groups' ),
				array( 'code' => 'atleastparam', 'info' => 'Atleast one of the params should be passed in the request' ) ) );
	}
	public function getExamples()
	{

	}
	public function getVersion()
	{
		return __CLASS__ . ': $Id$';
	}
}