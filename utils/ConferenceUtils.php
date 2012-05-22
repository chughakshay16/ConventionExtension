<?php
class ConferenceUtils
{
	public static function isConference($conferenceId)
	{
		$dbr=wfGetDB(DB_SLAVE);
		$res=$dbr->select('page_props',
		'*',
		array('pp_propname'=>'type','pp_value'=>$conferenceId),
		__METHOD__,
		array());
		return $dbr->numRows($res)?true:false;
	}
	public static function getConferenceId($title)
	{
		$dbr=wfGetDB(DB_SLAVE);
		$row=$dbr->selectRow('page',
		array('page_id'),
		array('page_title'=>$title),
		__METHOD__,
		array());
		return $row->page_id;
	}
	public static function getNamespace($conferenceId)
	{
		$dbr=wfGetDB(DB_SLAVE);
		$row=$dbr->selectRow('page',
		array('page_namespace'),
		array('page_id'=>$conferenceId),
		__METHOD__,
		array());
		return $row->page_namespace;
	}
	public static function getTitle($conferenceId)
	{
		$dbr=wfGetDB(DB_SLAVE);
		$row=$dbr->selectRow('page',
		array('page_title'),
		array('page_id'=>$conferenceId),
		__METHOD__,
		array());
		return $row->page_title;
	}	
	
}