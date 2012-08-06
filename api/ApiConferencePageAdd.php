<?php
class ApiConferencePageAdd extends ApiBase
{
	public function __construct( $main , $action )
	{
		parent::__construct( $main, $action );
	}
	public function execute()
	{
		$params = $this->extractRequestParams();
		/**
		 * these are the checks that we need to pass before we actually create a new page
		 * 1. admin should be logged in
		 * 2. conference data should be present in the session(this is an important
		 * step because this step distinugishes the admin from a regular user). As conference data is only stored
		 * for the admin user once he/she loads up the Special Dashboard page.
		 * 3. check for the validity of title
		 * 4. check if the title already exists
		 * 5. check for the validity of the user
		 *
		*/
		$user = $this->getUser();
		$request = $this->getRequest(); 
		/**
		 * 1. user should be logged in
		 * 2. user should be in the group 'sysop'
		 * 3. user should have conference data in the session
		 */
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
		$type = $params['pagetype'];
		$default = $params['defaultcontent'];
		$conferenceId = $sessionData['id'];
		//this is just checking the validity of the title passed along
		//sad part is that createFromScratch() never checks for the existence of title in the database so we will have to check that here only
		$confTitle = $sessionData['title'];
		$titleText = $confTitle . '/pages/' . $type;
		$title = Title::newFromText( $titleText );
		if ( !$title )
		{
				
			$this->dieUsageMsg( array( 'invalidtitle', $params['pagetype'] ) );
				
		} elseif ( $title->exists() ) {
				
			$this->dieUsageMsg( array( 'createonly-exists', $params['pagetype'] ) );
				
		}

		//now we are ready to create the page
		//but before doing that check if the page is already one of the existing types
		//, if it is not then default content will not be added even if the default is passed as true
		if ( $default )
		{
			$isTypePreLoaded = $this->isPreLoaded( $type );
				
			if ( !$isTypePreLoaded )
			{

				$default = false;

			}
				
			$page = ConferencePage::createFromScratch( $conferenceId, $type, $default );
			$resultApi = $this->getResult();
			if ( $page && $page->getId() )
			{

				$result['done'] = true;
				$result['id'] = $page->getId();
				$result['pagetype'] = $page->getType();
				$result['pageurl'] = Title::makeTitle(NS_MAIN,$confTitle.'/pages/'.$page->getType())->getFullURL();
				$result['msg'] = 'The conference page was successfully created';

			} else {
				$result['done'] = false;
				$result['msg'] = 'Some internal error occurred . Try to create again .';
			}
			$resultApi->addValue( null, $this->getModuleName(), $result );
		}



	}
	private function isPreLoaded( $type )
	{
		return true;
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
				'defaultcontent'=>array(
						ApiBase::PARAM_TYPE=>'boolean',
						ApiBase::PARAM_DFLT=>false)
		);
	}
	public function getParamDescription()
	{
		return array(
				'pagetype'		=>'Type of the conference page',
				'defaultcontent'=>'Boolean for deciding whether to add default content or not'
		);
	}
	public function getDescription()
	{
		return 'Add a conference page';
	}
	public function getPossibleErrors()
	{
		return array_merge( parent::getPossibleErrors(), array(
				array( 'mustbeloggedin', 'Wiki' ),
				array( 'badaccess-groups' ),
				/*array('invaliduser', $user->getName()),*/
				array( 'missingparam', 'type' ),
				array( 'invalidtitle', 'type' ),
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