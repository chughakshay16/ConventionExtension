<?php
class ApiConferencePageDelete extends ApiBase
{
	public function __construct($main , $action)
	{
		parent::__construct($main, $action);
	}
	public function execute()
	{
		$params=$this->extractRequestParams();
		$request=$this->getRequest();
		$user = $this->getUser();
		if( !$user->isLoggedIn() )
		{
			$this->dieUsageMsg(array('mustbeloggedin','Wiki'));
		}
		
		$groups = $user->getGroups();
		if( !in_array('sysop',$groups))
		{
			$this->dieUsageMsg(array('badaccess-groups'));
		}
		
		$sessionData = $request->getSessionData('conference');
		if( !$sessionData )
		{
			$this->dieUsage('No conference details were found in the session object for this user','noconfinsession');
		}
		$conferenceId=$sessionData['id'];
		$text = $sessionData['title'].'/pages/'.$params['pagetype'];
		$title=Title::newFromText($text);
		if(!$title)
		{

			$this->dieUsageMsg( array( 'invalidtitle', $params['pagetype'] ) );

		} elseif (!$title->exists()){

			$this->dieUsageMsg(array('cannotdelete','this page'));

		}
		$type = $params['pagetype'];
		$result=ConferencePage::performDelete($conferenceId,$type);
		$resultApi = $this->getResult();
		$resultApi->addValue(null, $this->getModuleName(), $result);

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
				'pagetype'=>array(
						ApiBase::PARAM_TYPE=>'string',
						ApiBase::PARAM_REQUIRED=>true)
		);
	}
	public function getParamDescription()
	{
		return array(
				'pagetype'=>'Type of the conference page'
		);
	}
	public function getDescription()
	{
		return 'Delete Conference Page';
	}
	public function getPossibleErrors()
	{

	}
	public function getExamples()
	{

	}
	public function getVersion()
	{
		return __CLASS__ . ': $Id$';
	}
}