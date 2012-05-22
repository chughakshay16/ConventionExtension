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
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $subAccountId
	 * @todo inner join on the same table
	 * details mentioned in the notebook
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