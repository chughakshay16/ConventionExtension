<?php
class ConferenceAccountUtils 
{
	/**
	 * Checks is this user_id already has a parent account
	 * always use this function before calling ConferenceAccount::createFromScratch()
	 * because createFromScratch() doesnt keep a check if its there or not
	 * @param unknown_type $userId
	 */
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
	/**
	 * 
	 * Fetches username for the given sub-account page_id 
	 * User --> Account(parent) --> Account(sub)(for every conference there would be one parent account and one sub account per user)
	 * @param Int $subAccountId
	 * @todo check if sql statement works !!
	 */
	public static function getUsernameFromSubAccount($subAccountId)
	{
		$dbr=wfGetDB(DB_SLAVE);
		$resultRow=$dbr->selectRow('page_props E',
		'E.pp_value',
		array('E.pp_page'=>$subAccountId,'E.pp_propname'=>'account-user','F.pp_propname'=>'account-parent'),
		__METHOD__,
		array(),
		array('page_props F'=> array('INNER JOIN','F.pp_value=E.pp_page')));
		return $resultRow->E.pp_value;
	}
	/**
	 * 
	 * Get a conference title for the conference that a sub-account page points to
	 * @param Int $subAccountId
	 */
	public static function getConferenceTitleFromSubAccount($subAccountId)
	{
		//this is the sub author id	
		$dbr=wfGetDB(DB_SLAVE);
		$resultRow=$dbr->select('page_props',
		'*',
		array('pp_page'=>$subAccountId,'pp_propname'=>'account-conf'),
		__METHOD__,
		array(),
		array('page'=> array('INNER JOIN','pp_value=page_id')));
		return $resultRow->page_title;
		
	}
	
}