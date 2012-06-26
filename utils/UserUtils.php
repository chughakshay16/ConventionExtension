<?php
class UserUtils
{
	public static function isUser($userId)
	{
		$dbr=wfGetDB(DB_SLAVE);
		$res=$dbr->select('user',
		'*',
		array('user_id'=>$userId),
		__METHOD__,
		array());
		return $dbr->numRows($res)?true:false;
	}
	public static function isAccount($userId)
	{
		$dbr=wfGetDB(DB_SLAVE);
		$res=$dbr->select('page_props',
		'*',
		array('pp_propname'=>'account-user','pp_value'=>$userId),
		__METHOD__,
		array());
		return $dbr->numRows($res)?true:false;
	}
	public static function isSpeaker($userId)
	{
		$dbr=wfGetDB(DB_SLAVE);
		$res=$dbr->select('page_props',
		'*',
		array('pp_propname'=>'speaker-user','pp_value'=>$userId),
		__METHOD__,
		array());
		return $dbr->numRows($res)?true:false;
	}
	public static function isApplicant($userId)
	{
		$dbr=wfGetDB(DB_SLAVE);
		$res=$dbr->select('page_props',
		'*',
		array('pp_propname'=>'applicant-user','pp_value'=>$userId),
		__METHOD__,
		array());
		return $dbr->numRows($res)?true:false;
	}
	public static function isOrganizer($userId)
	{
		$dbr=wfGetDB(DB_SLAVE);
		$res=$dbr->select('page_props',
		'*',
		array('pp_propname'=>'organizer-user','pp_value'=>$userId),
		__METHOD__,
		array());
		return $dbr->numRows($res)?true:false;
	}
	public static function getUsername($uid)
	{
		$dbr=wfGetDB(DB_SLAVE);
		$resultRow=$dbr->selectRow("user",
		'user_name',
		array('user_id'=>$uid),
		__METHOD__,
		array());
		return $resultRow->user_name?$resultRow->user_name:null;
	}
	
}