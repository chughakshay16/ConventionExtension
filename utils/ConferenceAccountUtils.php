<?php
class ConferenceAccountUtils 
{
	//always use this function before calling ConferenceAccount::createFromScratch()
	//because createFromScratch() doesnt keep a check if its there or not
	public static function hasParentAccount($userId)
	{
		$dbr=wfGetDB(DB_SLAVE);
		//if at all the parent account is present there will only be one row
		$result=$dbr->select('page_props',
		'*',
		array('pp_propname'=>'cvext-account-user','pp_value'=>$userId),
		__METHOD__,
		array());
		return $dbr->numRows($result)?true:false;
	}
	
}