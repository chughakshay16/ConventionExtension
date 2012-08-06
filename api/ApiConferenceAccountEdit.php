<?php
/**
 *
 * @todo complete mustValidateInputs()
 * @author chughakshay16
 *
 */
class ApiConferenceAccountEdit extends ApiBase
{
	public function __construct( $main, $action )
	{
		parent::__construct( $main, $action );
	}
	public function execute()
	{
		$params = $this->extractRequestParams();
		$request = $this->getRequest();
		$user = $this->getUser();
		//still need to decide on what messages you should choose while throwing these errors
		//there is no need for conference data for modifying the account fields
		/*if(session_id()=='')
		{
				
			$this->dieUsageMsg(array('mustbeloggedin','conference'));
				
		}
		if($user->getId()==0)
		{
				
			$this->dieUsageMsg(array('invaliduser',$user->getName()));
				
		}*/
		if ( !$user->isLoggedIn() )
		{
		
			$this->dieUsageMsg( array( 'mustbeloggedin', 'Wiki' ) );
		
		}
		if ( !isset( $params['gender'] ) && !isset( $params['firstname'] ) && !isset( $params['lastname'] ) )
		{
				
			$this->dieUsage( 'Atleast one of the params should be passed in the request', 'atleastparam' );
				
		} else {
				
			$gender = $params['gender'];
			$firstname = $params['firstname'];
			$lastname = $params['lastname'];

		}


		$isAccount = UserUtils::isAccount($user->getId());
		if ( !$isAccount )
		{
				
			$this->dieUsageMsg( array( 'badaccess-groups' ) );
				
		} else {
			$errors = $this->mustValidateInputs( $gender, $firstname, $lastname );
			if( count( $errors ) )
			{
				//depending on the error
				//$this->dieUsageMsg(array('spamdetected',put the parameter due to which error occurred))
			}
			$result = ConferenceAccount::performAccountEdit( $user->getId(), $firstName, $lastName, $gender );
			$resultApi = $this->getResult();
			$resultApi->addValue( null, $this->getModuleName(), $result );
		}
	}
	public function mustValidateInputs( $gender, $firstname, $lastname )
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
				'gender'	=>null,
				'firstname'	=>null,
				'lastname'	=>null
		);
	}
	public function getParamDescription()
	{
		return array(
				'gender'	=> 'Gender of the account holder',
				'firstname'	=> 'First Name of the account holder',
				'lastname'	=> 'Last Name of the account holder'
		);
	}
	public function getDescription()
	{
		return 'Edit Account Details';
	}
	public function getPossibleErrors()
	{
		$user = $this->getUser();
		return array_merge(parent::getPossibleErrors(),array(
				array('mustbeloggedin','Wiki'),
				/*array('invaliduser',$user->getName()),*/
				array('code'=>'atleastparam','info'=>'Atleast one of the params should be passed in the request'),
				array('badaccess-groups')));
	}
	public function getExamples()
	{

	}
	public function getVersion()
	{
		return __CLASS__ . ': $Id$';
	}
}