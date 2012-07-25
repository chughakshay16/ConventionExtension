<?php
/**
 *
 * @todo complete mustValidateInputs()
 * @author chughakshay16
 *
 */
class ApiPassportEdit extends ApiBase
{
	public function __construct($main, $action)
	{
		parent::__construct($main, $action);
	}
	public function execute()
	{
		$params = $this->extractRequestParams();
		$user = $this->getUser();
		if(session_id()=='')
		{
				
			$this->dieUsageMsg('mustbeloggedin','conference');
				
		} elseif ($user->getId()==0){
				
			$this->dieUsageMsg(array('invaliduser',$user->getName()));
				
		} elseif (!isset($params['pno']) && !isset($params['iby']) && !isset($params['vu']) && !isset($params['pl'])
				&& !isset($params['dob']) && !isset($params['ctry'])){
				
			$this->dieUsage('Atleast one of the params should be passed in the request','atleastparam');
				
		}


		$errors = $this->mustValidateInputs($params['pno'],$params['iby'],$params['vu'],$params['pl'],$params['dob'],$params['ctry']);
		if(count($errors))
		{
			//depending on the error
			//$this->dieUsageMsg(array('spamdetected',put the parameter due to which error occurred))
			//change getPossibleErrors()
		}

		$passport = new ConferencePassportInfo($params['pno'], null, $params['iby'], $params['vu'], $params['pl'], $params['dob'], $params['ctry']);
		$result = ConferenceAccount::performPassportUpdate($user->getId(), $passport);
		$resultApi = $this->getResult();
		$resultApi->addValue(null, $this->getModuleName(), $result);
	}
	public function mustValidateInputs($pno, $iby, $vu, $pl , $db, $ctry)
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
				'pno'=>null,
				'iby'=>null,
				'vu'=>null,
				'pl'=>null,
				'dob'=>null,
				'ctry'=>null
		);
	}
	public function getParamDescription()
	{
		return array(
				'pno'=>'Passport No',
				'iby'=>'Who issued this passport',
				'vu'=>'Date until which this passport is valid',
				'pl'=>'Place where this passport was issued',
				'dob'=>'Date of Birth of the passport holder',
				'ctry'=>'Country where the passport holder belongs to'
		);
	}
	public function getDescription()
	{
		return 'Edit Passport Details';
	}
	public function getPossibleErrors()
	{
		$user = $this->getUser();
		return array_merge(parent::getPossibleErrors(), array(
				array('mustbeloggedin','conference'),
				array('invaliduser',$user->getName()),
				array('code'=>'atleastparam', 'Atleast one of the params should be passed in the request'),
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