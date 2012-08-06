<?php
/**
 *
 * @todo complete mustValidateInputs()
 * @author chughakshay16
 *
 */
class ApiConferencePageEdit extends ApiBase
{
	public function __construct( $main, $action )
	{
		parent::__construct( $main,$action );
	}
	public function execute()
	{

		$params = $this->extractRequestParams();
		$request = $this->getRequest();
		$user = $this->getUser();
		$resultApi = $this->getResult();
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
		$oldText = $sessionData['title'] . '/pages/' . $params['pagetype'];
		$newText = $sessionData['title'] . '/pages/' . $params['pagetypeto'];
		$oldTitle = Title::newFromText( $oldText );
		$newTitle = Title::newFromText( $newText );
		$oldTalkPage = $oldTitle->getTalkPage();
		$newTalkPage = $newTitle->getTalkPage();
		# if pagetype and pagetypeto are same dont do anything, just send back what was given
		if ( $params['pagetype'] === $params['pagetypeto'] )
		{
			$result['done'] = true;
			$result['pagetype'] = $params['pagetype'];
			$result['pagetypeto'] = $params['pagetypeto'];
			$result['urlto'] = $newTitle->getFullURL();
			$result['msg'] = 'The details were successfully edited';
			$resultApi->addValue( null, $this->getModuleName(), $result );
			return ;
		}
		
		if ( !$oldTitle )
		{
				
			$this->dieUsageMsg( array( 'invalidtitle', $params['pagetype'] ) );
				
		} elseif ( !$newTitle ) {
				
			$this->dieUsageMsg( array( 'invalidtitle', $params['pagetypeto'] ) );
				
		} elseif ( !$oldTitle->exists() ) {
				
			$this->dieUsageMsg( array( 'nocreate-missing' ) );
				
		} elseif ( $newTitle->exists() ) {
				
			$this->dieUsageMsg( array( array( 'createonly-exists' ) ) );
				
		} elseif ( $oldTalkPage && $oldTalkPage->exists() ) {
				
			//debug
			//and do something about it
				
		} elseif ( $newTalkPage && $newTalkPage->exists() ) {
				
			//debug
			//and do something about it
				
		}
		$createRedirect = false;
		$reason = 'The admin is editing the details of the conference page';
		$retval = $oldTitle->moveTo( $newTitle, true, $reason, $createRedirect );
		if ( $retval !== true )
		{
			$this->dieUsageMsg( reset( $retval ) );
		}
		$result = ConferencePage::performEdit( $sessionData['id'], $params['pagetypeto'], $params['pagetype'] );
		$resultApi->addValue( null, $this->getModuleName(), $result );
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
				'pagetype'		=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true),
				'pagetypeto'	=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true)
		);
	}
	public function getParamDescription()
	{
		return array(
				'pagetype'		=> 'Type of the conference page',
				'pagetypeto'	=> 'New type for the conference page'
		);
	}
	public function getDescription()
	{
		return 'Edit Conference Details';
	}
	public function getPossibleErrors()
	{
		return array_merge( parent::getPossibleErrors(), array(
				array( 'mustbeloggedin', 'Wiki' ),
				array( 'badaccess-groups' ),
				/*array('invaliduser', $user->getName()),*/
				array( 'missingparam', 'type' ),
				array( 'missingparam', 'typeto' ),
				array( 'invalidtitle', 'type' ),
				array( 'invalidtitle', 'typeto' ),
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